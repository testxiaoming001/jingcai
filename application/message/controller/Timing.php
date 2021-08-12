<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaohei
 * Date: 2020/12/24
 * Time: 0:19
 */

namespace app\message\controller;


use app\plugins\wallet\service\WalletService;
use app\service\TelegramBotService;
use app\service\TgAdService;
use think\Controller;
use think\Db;
use think\facade\Log;

class Timing extends Controller
{
    /**
     * 定时发送曝光支付列表
     */
    public function exposurepayList()
    {
        $result = Db::name('Exposurepay')
            ->whereTime('add_time', '>', strtotime('-30 day'))
            ->where(['is_delete_time' => '0'])
            ->order('add_time', 'desc')
            ->select();


        $str = '近30日被曝光的支付';

        foreach ($result as $k => $v) {
            $str .= '
' . $v['exposurepay_name'] . ' ' . $v['exposurepay_gateway'] . ' ' . $v['complaint_reason'] . ' ' . date('Y-m-d', $v['add_time']);
        }


        $TelegramBotService = new TelegramBotService();
        $TelegramBotService->sedDefaultGroupMessage($str);
    }


    /**
     * 定时推送tg广告内容
     */
    public function sendAd(TelegramBotService $telegramBotService)
    {
        $date = date('H:i');
        //此时间段可以推送的广告
        $ads = TgAdService::getAblePushAdsAtime($date);
        if ($ads) {
            $contents = implode(' ', array_column($ads, 'ad_content'));
            $telegramBotService->sedDefaultGroupMessage($contents);
        }
    }


    /**
     * 每五分钟开启支付小游戏
     */
    public function sendGameExposurepay(TelegramBotService $telegramBotService)
    {
        //是否开启游戏
        $isOpen = config('shopxo.is_open_pay_game');
        if (!$isOpen) {
            return;
        }

        $limit = 4;;
        $result = Db::name('Exposurepay')
            ->where(['is_delete_time' => '0'])
            ->order('add_time', 'desc')
            ->limit(4)
            ->select();
        $pays   = '';
        array_walk($result, function ($v, $k) use (&$pays) {
            $pays .= '
 ' . ($k + 1) . '.' . $v['exposurepay_name'];
        });
        $sendText = "防骗小游戏竞猜开始 ： 下列 {$limit}个支付中请猜出最黑的支付是谁{$pays}
请输入你认为本期最黑的支付:格式（XXX支付 100），猜对会获取4倍奖励！";

        $logText = "【" . date('Y-m-d H:i:s') . "】 支付小游戏开始了,游戏内容{$sendText}\n";
        //记录本期
        file_put_contents('./game.log', "{$logText}\n", FILE_APPEND);
        Log::info($logText);
        //记录开奖参数
        $currentTime                     = time();
        $paysId                          = array_column($result, 'id');
        $currentGameParam['pay_game_id'] = cache('pay_game_id', 1);
        $currentGameParam['start_time']  = date('Y-m-d H:i');
        $currentGameParam['stop_time']   = date('Y-m-d H:i', $currentTime + 180);
        $currentGameParam['open_time']   = date('Y-m-d H:i', $currentTime + 240);
        $currentGameParam['open_result'] = $paysId[array_rand($paysId)];
        $currentGameParam['game_detail'] = json_encode($result);
        $currentGameParam['joinPersons'] = [];
        $logText                         = "【" . date('Y-m-d H:i:s') . "】 本轮开奖游戏结果{$currentGameParam['open_result']}\n";
        //记录本期开奖结果
        file_put_contents('./game.log', "{$logText}\n", FILE_APPEND);
        Log::info($logText);
        //保存本地game 300s后自动失效
        cache('pay_game_params', $currentGameParam, 300);
        //推送消息入群
        $telegramBotService->sedDefaultGroupMessage($sendText);
    }

    /**
     * 支付曝光游戏游戏open&stop
     * @param TelegramBotService $telegramBotService
     */
    public function playGameExposurepay(TelegramBotService $telegramBotService)
    {
        $payGameParam = cache('pay_game_params');
        //有游戏玩
        $currentTime = date('Y-m-d H:i');
        try {
            if ($payGameParam && $payGameParam['stop_time'] == $currentTime) {
                //停止游戏
                $this->stopPayGame($payGameParam);
                return;
            }
            if ($payGameParam && $payGameParam['open_time'] == $currentTime) {
                //游戏开奖
                $this->openPayGame($payGameParam);
                return;
            }
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
        }
    }


    /**
     * 停止游戏
     */
    protected function stopPayGame()
    {
        $telegramBotService = new TelegramBotService();
        //发送游戏暂定消息
        $sendText = "支付小游戏本轮竞猜已停止";
        $logText  = "【" . date('Y-m-d H:i:s') . "】 支付小游戏本轮竞猜已停止\n";
        file_put_contents('./game.log', "{$logText}\n", FILE_APPEND);
        Log::info($logText);
        $telegramBotService->sedDefaultGroupMessage($sendText);
    }


    /**
     * 开奖
     * @param $payGameParam
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function openPayGame($payGameParam)
    {
        $telegramBotService = new TelegramBotService();
        $walletService      = new WalletService();
        //开奖结果
        $openResult = Db::name('Exposurepay')->where('id', $payGameParam['open_result'])
            ->find();

        //本轮开奖  ['user_id'=>1,'play_result'=>2,'number'=>100,'user_name'=>'shopxo'];
        $joinPersons = $payGameParam['joinPersons'];
        //赛出中奖和未中奖名单
        $winPersons = [];
        if ($joinPersons) {
            foreach ($joinPersons as $person) {
                //结算
                $isWin = ($person['play_result'] == $payGameParam['open_result']) ? 1 : 0;
                if ($isWin) {
                    array_push($winPersons, $person);
                    $person['number'] = $person['number'] * 4;
                }
                $walletService->UserWalletMoneyUpdate($person['user_id'], $person['number'], 'normal_money',
                    $isWin, '支付竞猜游戏');

            }
        }
        //给中奖人推送消息
        if ($winPersons) {
            $winSendItem = [];
            array_walk($result, function ($v, $k) use (&$winSendItem) {
                $winSendItem[] = $v['user_name'] . ' +' . $v['number'] * 4;
            });
        }
        $winSendItem = $winPersons ? '恭喜下列玩家:' . $winSendItem : '';
        $sendText    = "本期最黑支付是 {$openResult['exposurepay_name']}，理由 ：{$openResult['complaint_reason']} " . $winSendItem;
        $logText     = "【" . date('Y-m-d H:i:s') . "】 {$sendText}\n";
        file_put_contents('./game.log', "{$logText}\n", FILE_APPEND);
        Log::info($logText);
        $telegramBotService->sedDefaultGroupMessage($sendText);
        //夸奖结束本轮结束
        cache('pay_game_params', null);
    }


    /**
     * 推送介绍机器人到群或者channel
     */
    public function introduceBoot()
    {
        $telegramBotService = new TelegramBotService();
        $option['parse_mode'] = 'MarkdownV2';
        $sendText = 'xcxzczczxczx';
        $telegramBotService->sedDefaultGroupMessage($sendText,$option);
    }

}
