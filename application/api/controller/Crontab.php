<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\service\CrontabService;
use app\service\GameService;
use app\service\TaskService;
use app\service\TgBotService;
use FG\Utility\BigInteger;
use think\Collection;
use think\Db;
use think\Exception;
use think\facade\Config;
use think\facade\Log;
use TronTool\TronApi;

/**
 * 定时任务
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-08-18T17:19:33+0800
 */
class Crontab extends Common
{
    /**
     * 订单关闭
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-18T17:19:33+0800
     */
    public function OrderClose()
    {
        $ret = CrontabService::OrderClose();
        return 'sucs:' . $ret['data']['sucs'] . ', fail:' . $ret['data']['fail'];
    }

    /**
     * 订单收货
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-18T17:19:33+0800
     */
    public function OrderSuccess()
    {
        $ret = CrontabService::OrderSuccess();
        return 'sucs:' . $ret['data']['sucs'] . ', fail:' . $ret['data']['fail'];
    }

    /**
     * 支付日志订单关闭
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-18T17:19:33+0800
     */
    public function PayLogOrderClose()
    {
        $ret = CrontabService::PayLogOrderClose();
        return 'count:' . $ret['data'];
    }


    public function captureUsdtRate()
    {

        $captureUrl = "https://otc-api-hk.eiijo.cn/v1/data/trade-market?coinId=2&currency=1&tradeType=sell&currPage=1&payMethod=0&country=37&blockType=block&online=1&range=0&amount=";
        try {
            $result = json_decode(httpRequest($captureUrl), true);
            if ($result['code'] == 200) {
                $rate = $result['data'][0]['price'];

            }
            throw new Exception('采集usdt最新汇率失败');
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            Log::info($exception->getMessage());
        }
        dd($result);
    }


    /**
     * 每隔五分钟推送一次广告
     * @return string
     */
    public function pushTgBanner()
    {
        $ret = CrontabService::pushTgBanner();
        return 'count:' . $ret['data'];
    }


    /**
     * 获取需要的交易记录
     * @param $trans
     */
    protected function getAviableTrans($trans)
    {
        $transCollections = Collection::make($trans);
        $payUrl = config("shopxo.task_pay_usdt")['url'];
        $targetTrans = [];
        $transCollections->filter(function ($item) use ($payUrl, &$targetTrans) {
            if ($item['to'] == $payUrl && $item['type'] == 'Transfer') {
                $row['usdt_amount'] = $item['value'];
                $row['deal_time'] = $item['block_timestamp'];
                $targetTrans[] = $row;
            }
        });
        return $targetTrans;
    }


    public function autoCloseTask()
    {
        TaskService::autoCloseTask();

    }


    /**
     *自动回调task订单
     */
    public function setTaskAutoSuccess()
    {
        //查询所有待支付的task订单
        $tasks = TaskService::TaskList([
            'pay_status' => 0,
            'is_delete' => 0
        ]);
        $tasks = $tasks['data'];
        if ($tasks) {
            //如果有未支付的才拉 频繁拉 封ip
            $limit = 50;
            $usdt = config("shopxo.task_pay_usdt");
            $rpcUrl = "https://api.trongrid.io/v1/accounts/{$usdt['url']}/transactions/{$usdt['link_type']}?limit={$limit}";
            $trans = json_decode(httpRequest($rpcUrl), true);
            if (empty($trans) || !isset($trans['data']) || empty($trans['data'])) {
                return;
            }
            $tokenDealTrans = $this->getAviableTrans($trans['data']);

            foreach ($tasks as $k => $task) {
                foreach ($tokenDealTrans as $tokenDealTran) {
                    if ($task['usdt_amount'] * 1000000 == $tokenDealTran['usdt_amount'] || $tokenDealTran['deal_time'] > strtotime($task['created_at']) || $tokenDealTran['deal_time'] < strtotime($task['closed_at'])) {
                        $params['task_id'] = $task['id'];
                        TaskService::setTaskSuccess($task['id']);
                        break;
                    }
                }

            }
        }
    }


    /**
     * 拉取远程订单警告推送的tg消息
     */
    public function pullplatzfSendMessage()
    {
        $remoteUrl = "http://www.yingqianpay.com/index/tg_message/orderWarning";
        $result = httpRequest($remoteUrl);
        Log::info("拉取到支付系统待播放Tg消息业务类型【订单报警】参数返回" . $result);
        $result = json_decode($result, true);
        try {
            if (true) {
                //发送消息到订单机器人群组
                $groupId = Db::name('Config')->where(['only_tag' => 'tg_order_warning_rebot_in_chat'])->value('value');
                $token = Db::name('Config')->where(['only_tag' => 'tg_order_warning_robot_token'])->value('value');
                $tgBotService = new TgBotService();
                $result = $tgBotService->setBotToken($token)->sendMessage($groupId, $result['send_message']);
                dd($result);
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            Log::info("拉取到支付系统待播放Tg消息业务类型【订单报警】shopxo处理异常" . $exception->getMessage());
        }
    }


    /**
     * Notes:
     * User: Administrator
     * DateTime: 2021/8/10 0010 23:06
     */
    public function createGame()
    {
        try {
            //游戏是否启动
            if (config('shopxo.game_start')) {
           //   GameService::createGame();
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }


    /**
     * 自动充值
     */
    public function autoRecharge()
    {
        try {
            $rechareOrderCancleTime = GameService::getGameConfig('recharge_cancle_minute')['param_value'];
            //查询最近所有的未支付的订单/id升序
            $recharges = Db::name('GameRecharge')
                ->where([
                    'status' => GameService::UNPAY,
                ])
                ->where('created_at', '>=', date('Y-m-d H:i:s', time() - $rechareOrderCancleTime * 60))
                ->order('created_at', 'desc')->select();

            if ($recharges) {
                $address = GameService::getGameConfig('usdt_trc20_address')['param_value'];
                //链上的交易信息
                $net = Config::get('tronscan_network', 'testNet');
                $tronNet = TronApi::$net();
                $chainsTrans = $tronNet->gettrc20transactions(
                    [
                        'only_to' => true,
                        'limit=>200',
                        'min_timestamp' => getMillisecond() - $rechareOrderCancleTime * 60 * 1000
                    ], $address);

                if ($chainsTrans && !empty($chainsTrans->data)) {
                    $chainsTrans = $chainsTrans->data;
                    foreach ($recharges as $recharge) {
                        //判断然后标记为充值成功
                        foreach ($chainsTrans as $chainsTran) {
                            //当前波场usdt合约采用的位数
                            $decimals = $chainsTran->token_info->decimals;
                            $decimalNumber = str_pad(1, $decimals + 1, 0);
                            $chainsTranUsdt = bcdiv($chainsTran->value, $decimalNumber, 3);
                            if (bccomp($chainsTranUsdt, $recharge['usdt_amount']) == 0 && strtotime($recharge['created_at']) * 1000 < $chainsTran->block_timestamp) {
                                Log::info("自动充值:回调充值本地信息" . json_encode($recharge) . '匹配链上信息' . json_encode($chainsTran));
                                $update['updated_at'] = $update['sys_pay_at'] = date('Y-m-d H:i:s', time());
                                $update['status'] = GameService::PAYED;
                                $update['block_chain_transid'] = $chainsTran->transaction_id;
                                $update['from_usdt_address'] = $chainsTran->from;
                                $update['desciption'] = "匹配链上{$chainsTran->transaction_id}完成" . '自动充值';
                                $update['block_chain_pay_at'] = date('Y-m-d H:i:s', $chainsTran->block_timestamp / 1000);
                                GameService::setRechargeSuccess($update, $recharge['id']);
                                break;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }


    /*
     *
     * Notes:同步游戏数据
     * User: Administrator
     * DateTime: 2021/8/10 0010 23:10
     */
    public function buildGameData()
    {
        try {
            GameService::buildGameData();
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }


}

?>
