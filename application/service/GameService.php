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
namespace app\service;

use app\common\enum\GameRecharge;
use app\common\enum\User;
use app\common\exception\GameRechargeException;
use app\common\exception\UserException;
use app\common\model\EwmPayCode;
use app\plugins\wallet\service\RechargeService;
use app\plugins\wallet\service\WalletService;
use think\Collection;
use think\Db;
use app\service\ResourcesService;
use think\facade\Config;
use think\facade\Log;
use TronTool\Credential;
use TronTool\TronApi;
use TronTool\TronKit;

/**
 * 游戏服务
 * Class ChannelService
 * @package app\service
 */
class GameService
{
    const BEGIN_ORDERNO = '001';
    const END_OREDRNO = '120';
    const SIZE = 10;
    //3分钟后封盘
    const FENPAN_DELAY = 3;
    //4分钟后开奖
    const KJ_DELAY = 4;
    //封盘key
    const FENPAK_KEY_PRFIX = 'game:fenpan:';
    //开奖key
    const KJ_KEY_PRFIX = 'game:kaijiang:';

    //充值订单取消key
    const RECHARGE_ORDER_CANCLE__KEY_PRFIX = 'game:recharge_cancle:';


    //提现订单确认key
    const WITHDRAW_ORDER_CONFIRM__KEY_PRFIX = 'game:withdraw_confirm:';

    const OPNE = 1;
    const  UNSETTLEMENT = 0;
    const  SETTLEMENT = 1;
    const GAGE_RATE = 1;

    const USDT_AMOUNT_FLOAT_TOP = 1;
    const USDT_AMOUNT_FLOAT_LEVEL = 0;

    const USDT_RECHARGE_PRICE_NUMS_LIMIT = 20;

    const UNPAY = 0;
    const PAYED = 1;
    const CLOSED = 2;


    const ONCE_BUILD = 1000;

    const KJRPCURL = "http://api.20api.com/test?token=E992043A815307B7&t=jnd28&limit=5&p=json";
//    const KJRPCURL = "https://www.wxh5cdn.com/token/701b5628fb1711ebbb0675e401a4642c/code/jnd28/rows/5.json";

    /*
     *
     * Notes:同步开奖数据
     * User: Administrator
     * DateTime: 2021/8/11 0011 21:48
     */
    public static function syncKJdata()
    {
        $pullDefaultKai = 2746503;
        $waitKjGame = Db::name('Game')->where([
            'is_open' => 0,
        ])->order('id', 'desc')->find();
        if($waitKjGame)
        {
            $waitKjGameNo = $waitKjGame ? $waitKjGame['game_no'] : $pullDefaultKai;
            $responce = file_get_contents(self::KJRPCURL);
            echo "采集到加拿大28开奖结果返回{$responce}\r\n";
            $responce = json_decode($responce, true);
            $lastOpenGame = $responce['data'][0];
            if ($lastOpenGame['expect'] == $waitKjGameNo) {
                //三方开奖了
                $update['open_codes'] = explode(',', $lastOpenGame['opencode']);
                $update['open_at'] = $lastOpenGame['opentime'];
                try {
                    //平台开奖
                    self::kaijiang($waitKjGame['id'], $update);
                } catch (\Exception $exception) {
                    echo "加拿大28开奖结算发生异常{$exception->getMessage()}\r\n";
                }
            }
        }
    }


    /*
     * Notes:是否在游戏维护期间
     * User: Administrator
     * DateTime: 2021/8/11 0011 0:27
     * @param $currentStamps  当前的时间戳
     */
    protected static function isInStopTime($currentStamps)
    {
        $stBegin = strtotime(date('Y-m-d 19:00:00', $currentStamps));
        $stStop = strtotime(date('Y-m-d 20:00:00', $currentStamps));
        if ($currentStamps > $stBegin && $currentStamps < $stStop) {
            return true;
        }
        return false;
    }

    //生成系统游戏的开奖时间
    const GAME_OPEN_AT_BEGIN = '2021-08-10 00:02:30';
    const GAME_START_NUMBER = 2745732;
    const GAME_OPEN_ATS_LIMIT = 10;

    /*
     *
     * Notes:获取游戏开奖时间
     * User: Administrator
     * DateTime: 2021/8/11 0011 0:53
     */
    public static function buildGameOpenAts()
    {
        $prevGame = self::prevGame();
        $maxOpenAt = $prevGame['open_at'] ? $prevGame['open_at'] : self::GAME_OPEN_AT_BEGIN;
        $maxGameNo = $prevGame['game_no'] ? $prevGame['game_no'] : self::GAME_START_NUMBER;
        $opens = [];
        $rTime = 210;         //每隔三分半开一次
        for ($i = 0; $i <= 500; $i++) {
            $at = date('Y-m-d H:i:s', strtotime($maxOpenAt) + $i * $rTime);
            if (!self::isInStopTime(strtotime($at))) { //这段时间是正常游戏时间
//                $atTimeStamps = strtotime($at);
//                if ($atTimeStamps > strtotime(date('Y-m-d 20:00:00', $atTimeStamps))) {
//                    $at =date('Y-m-d 20:01:00', $atTimeStamps);
//                }
                array_push($opens, [
                    'open_at' => $at,
                    'game_no' => $maxGameNo++
                ]);
            }
        }


        dd($opens);

    }


    /*
     *生成系统游戏数据先部对接第三方
     * Notes:
     * User: Administrator
     * DateTime: 2021/8/10 0010 23:11
     */
    public static function buildGameData()
    {
        $openAts = self::buildGameOpenAts();
        dd($openAts);


        $openTime = 1628524950; // 2021-08-10 00:02:30
        $limit = 5;
        //构造数据
        $gameData = [];
        for ($i = 0; $i <= $limit - 1; $i++) {
            $openTimeStamps = $openTime + $i * $rTime;
            if (!self::isInStopTime($openTimeStamps)) {
                //没有在当前cp停盘时间  --->系统生成
                dd($startGameNO);

                $gameData[] = [
                    'game_no' => $i,
                    'open_at' => date('Y-m-d H:i:s', $openTimeStamps)
                ];
            }
        }
        dd($gameData);
    }

    protected static function subscribeHander($dirve = 'redis')
    {
        return \think\facade\Cache::connect(['type' => $dirve])->handler();
    }


    /**
     * 上一期游戏
     * @return mixed
     */
    protected static function prevGame()
    {
        return Db::name('Game')->order(['open_at' => 'desc'])->find();
    }

    protected static function parseGameNo($gameNo = 0)
    {
        return str_pad($gameNo, 3, '0', STR_PAD_LEFT);
    }


    protected static function createOpenCodes()
    {
        $numbers = [1, 2, 3, 4, 5, 6];
        $codes = [];
        for ($i = 0; $i <= 2; $i++) {
            $codes[$i] = $numbers[array_rand($numbers)];
        }
        //大小
        $size = array_sum($codes) > SELF::SIZE ? '大' : "小";
        return compact('codes', 'size');
    }


    /*
     *
     * Notes:投递到封盘的队列中
     * User: Administrator
     * DateTime: 2021/8/11 0011 23:47
     * @param $gameId  游戏id
     * @param int $ttl  key过期时间
     * @return mixed
     */
    protected static function pushFenpanQueue($gameId, $ttl = 0)
    {

        $ttlTime = self::FENPAN_DELAY * 60;
        $key = self::FENPAK_KEY_PRFIX . $gameId;
        $ttlTime = $ttl ? $ttl : $ttlTime;
        return self::subscribeHander()->setex($key, $ttlTime, 1);
    }


    /**
     * 投递到订单取消队列
     * @param $rechargeId
     * @return mixed
     */
    protected static function pushRechargeCancleQueue($rechargeId)
    {
        $ttlTime = self::getGameConfig('recharge_cancle_minute')['param_value'] * 60;
        $key = self::RECHARGE_ORDER_CANCLE__KEY_PRFIX . $rechargeId;
        return self::subscribeHander()->setex($key, $ttlTime, 1);
    }


    protected static function pushWithdrawConfirmQueue($rechargeId)
    {
        $ttlTime = 120;
        $key = self::WITHDRAW_ORDER_CONFIRM__KEY_PRFIX . $rechargeId;
        return self::subscribeHander()->setex($key, $ttlTime, 1);
    }


    protected static function pushKaijiangQueue($gameId)
    {
        $ttlTime = self::KJ_DELAY * 60;
        $key = self::KJ_KEY_PRFIX . $gameId;
        return self::subscribeHander()->setex($key, $ttlTime, 1);
    }


    /**
     * 生成游戏记录
     */
    public static function createGame()
    {
        $prevGame = self::prevGame();
        if (intval($prevGame['game_no']) <= self::END_OREDRNO) {
            $gameCodeParams = self::createOpenCodes();
            $gameNo = empty($prevGame) ? self::BEGIN_ORDERNO : self::parseGameNo((intval($prevGame['game_no']) + 1));
            $data['game_no'] = $gameNo;
            $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s', time());
            $data['open_codes'] = implode('|', $gameCodeParams['codes']);
            $data['open_size'] = $gameCodeParams['size'];
            $gameId = Db::name('game')->insertGetId($data);
            if (false == $gameId) {
                throw  new \Exception("新增游戏结果出错{$gameId}", 100001);
            }
            //投放到封盘队列钟
            self::pushFenpanQueue($gameId);
            self::pushKaijiangQueue($gameId);
            //给当前群组推送游戏即将开始
            $tgBotService = new TelegramBotService();

            $text = "🎉🎉🎉竞猜游戏期数:【{$gameNo}】即将开始,恭祝各位好运！！！";
            $tgBotService->sedDefaultGroupMessage($text);

        }
    }


    /**
     * 游戏封盘
     * @param $gameId
     */
    public static function fenpan($gameId)
    {
        $game = Db::name('Game')->where(['id' => $gameId])->find();
        if ($game) {
            $tgBotService = new TelegramBotService();
            $text = "竞猜游戏期数:【{$game['game_no']}】已封盘,暂停投注！！！";
            $tgBotService->sedDefaultGroupMessage($text);

        }
    }

    /**
     * 游戏投注记录
     * @param $gameId
     */
    public static function bets($gameId)
    {
        return Db::name('GameBet')
            ->where(['game_id' => $gameId])
            ->select();
    }


    public static function getGameRechargeInfoById($rechargeId)
    {
        $fields = ['a.id as recharge_id', 'order_no', 'user_telegram', 'user_telegram_id', 'a.status as status'];
        return Db::name('GameRecharge')->alias('a')
            ->leftJoin('User b', 'a.user_id=b.id')
            ->where(['a.id' => $rechargeId])
            ->field($fields)
            ->find();
    }


    /**
     * 游戏充值订单超时取消
     * @param $gameId
     * @return array|\PDOStatement|string|Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function recharge_cancle($rechargeId)
    {
        $recharge = self::getGameRechargeInfoById($rechargeId);
        if ($recharge && $recharge['status'] == GameRecharge::UN_PAY) {
            $res = Db::name('GameRecharge')
                ->where([
                    'id' => $rechargeId,
                    'status' => GameRecharge::UN_PAY
                ])->update(['desciption' => '订单超时未支付自动关闭', 'status' => GameRecharge::CLOSED, 'updated_at' => date('Y-m-d H:i:s')]);
            if ($res !== false) {
                //给当前用户推送订单关闭通知
                $tgBotService = new TelegramBotService();
                $text = "@{$recharge['user_telegram']}您的充值单号【{$recharge['order_no']}】超时未支付，系统已关闭！！！";
                $tgBotService->sedDefaultGroupMessage($text);
                return;
            }
            Log::error("订单关闭异常:【params:recharge_id-->{$rechargeId}】");
        }
    }


    /**
     * 后台提现
     * @param $rechargeId
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function withdraw_confirm($rechargeId)
    {
        $TelegramBotService = new TelegramBotService();
        $withdraw = Db::name('GameWithdraw')->where('id', $rechargeId)->find();
        if ($withdraw) {

            $net = Config::get('tronscan_network', 'testNet');
            $tronNet = TronApi::$net();
            $chainsTran = $tronNet->getTransactionInfoById($withdraw['block_chain_transid']);
            Log::info("链上交易信息查询:【params:withdraw_id-->{$rechargeId}】,链上查询结果" . json_encode($chainsTran));
            $isSuccess = isset($chainsTran->result) && $chainsTran->result == 'FAILED' ? 2 : 1;
            $afterUpdate['status'] = $isSuccess;
            $afterUpdate['updated_at'] = $afterUpdate['sys_pay_at'] = date('Y-m-d H:i:s', time());
            $afterUpdate['desciption'] = $chainsTran->receipt->result;
            $afterUpdate['block_chain_pay_at'] = date('Y-m-d H:i:s', $chainsTran->blockTimeStamp / 1000);
            Db::name('GameWithdraw')->where('id', $rechargeId)->update($afterUpdate);
            $user = Db::name('User')->where('id', $withdraw['user_id'])->find();
            //冻结资金减少
            WalletService::UserWalletMoneyUpdate($user['id'], $withdraw['amount'], 0, 'frozen_money', 2, "提现成功减少冻结金额");
            switch ($isSuccess) {
                case 1:
                    //链上转账成功
                    $sed = "@{$user['user_telegram']}您提现的单号：{$withdraw['order_no']},金额：{$withdraw['usdt_amount']}U系统已转款,请查询您的钱包交易记录,提现账单查询请输入指令: withdraw:单号";
                    break;
                case 2:
                    //链上转账失败
                    Log::error("提现订单自动确认异常:【params:withdraw_id-->{$rechargeId}】,链上查询结果" . json_encode($chainsTran));
                    $sed = "@{$user['user_telegram']}您提现的单号：{$withdraw['order_no']},金额：{$withdraw['usdt_amount']}U系统在线处理失败,提现账单查询请输入指令: withdraw:单号";
                    //增加可用资金
                    WalletService::UserWalletMoneyUpdate($user['id'], $withdraw['amount'], 1, 'normal_money', 2, "提现失败资金返回");
                    break;
                default:
                    break;
            }
            $TelegramBotService->sedDefaultGroupMessage($sed);
        }
    }


    /**
     * 解散统计
     * @param $gameId
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function calulate($gameId)
    {
        $fields = ['b.id as user_id', 'user_telegram', 'COALESCE(cast(sum(bet_amount) AS decimal(15,2)),0) as total_bet_amount', 'username', 'COALESCE(cast(sum(settlement_amount) AS decimal(15,2)),0) as total_settlement_amount'];
        $data = Db::name('GameBet')->alias('a')
            ->leftJoin('User b', 'a.user_id=b.id')
            ->group('a.user_id')
            ->where(['game_id' => $gameId, 'is_settlement' => self::SETTLEMENT])
            ->field($fields)
            ->select();
        return $data;
    }


    public static function calulateTmplates($cals)
    {
        $itemsStr = [];
        foreach ($cals as $k => $cal) {
            $user_telegram = $cal['user_telegram'] ? $cal['user_telegram'] : $cal['username'];
            $amountStr = $cal['total_settlement_amount'] > 0 ? '+' . $cal['total_settlement_amount'] : $cal['total_settlement_amount'];
            $jsAmount = bcsub($cal['total_settlement_amount'], $cal['total_bet_amount'], 2);
            $itemsStr[] = ($k + 1) . ".{$user_telegram} {$jsAmount}";
        }
        return $itemsStr;
    }


    /**
     * 根据下注参数解析出赔率
     * @param $size
     */
    public static function resolveGameRateByBetSize($size)
    {
        $where['description'] = $size;
        return Db::name('GameConfig')->where($where)->find();
    }


    /**
     * 玩大是否命中
     * @param $openCodes
     */
    public static function play_size_big_rate($openCodes)
    {
        return array_sum($openCodes) > self::SIZE ? true : false;
    }


    /**
     * 玩顺子是否命中
     * @param $openCodes
     */
    public static function play_size_small_rate($openCodes)
    {
        return array_sum($openCodes) <= self::SIZE ? true : false;
    }

    public static function playWaysBasic($vars)
    {
        $res = '';
        if ($vars[0] == $vars[1] && $vars[0] == $vars[2] && $vars[1] == $vars[2]) {
            $res = '豹子';
        } else {
            $dz = '';
            if ($vars[0] == $vars[1]) {
                $dz++;
            }
            if ($vars[0] == $vars[2]) {
                $dz++;
            }
            if ($vars[1] == $vars[2]) {
                $dz++;
            }
            if ($dz == 1) {
                $res = '对子';
            } else {
                $bb = 0;
                if (abs($vars[0] - $vars[1]) == 1) {
                    $bb++;
                }
                if (abs($vars[0] - $vars[2]) == 1) {
                    $bb++;
                }
                if (abs($vars[1] - $vars[2]) == 1) {
                    $bb++;
                }
                if ($bb == 0) {
                    $res = '杂六';
                }
                if ($bb == 1) {
                    $res = '半顺';
                }
                if ($bb == 2) {
                    $res = '顺子';
                }
            }
        }
        return $res;
    }


    public static function play_shunzi_rate($vars)
    {
        return self::playWaysBasic($vars) == '顺子' ? true : false;
    }


    public static function play_duizi_rate($vars)
    {
        return self::playWaysBasic($vars) == '对子' ? true : false;
    }


    public static function play_baozi_rate($vars)
    {
        return self::playWaysBasic($vars) == '豹子' ? true : false;
    }


    //解析每种玩法是否命中
    public static function parsePayWaysSout($openCodes)
    {
        $playWays = Db::name('GameConfig')->where('param_name', 'like', '%play_%')->select();
        $souts = array_map(function ($item) use ($openCodes) {
            $isSout = call_user_func([GameService::class, $item['param_name']], $openCodes);
            $row['play_way_cn'] = $item['description'];
            $row['play_way_en'] = $item['param_name'];
            $row['is_sout'] = $isSout;
            return $row;
        }, $playWays);
        return $souts;
    }


    /*
     *
     * Notes: 游戏开奖
     * User: Administrator
     * DateTime: 2021/8/11 0011 22:31
     * @param $gameId  游戏id
     * @param $kjParam 开奖参数
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function kaijiang($gameId, $kjParam = [])
    {
        try {
            $date = date('Y-m-d H:i:s');
            Db::startTrans();
            $game = Db::name('Game')->where(['id' => $gameId])->lock(true)->find();
            if ($game['is_open'] == self::OPNE) {
                throw  new \Exception("当期已开奖！！！", 10003);
            }

            $gRes = Db::name('Game')->where(['id' => $gameId])->update([
                'open_at' => $kjParam['open_at'],
                'sync_at' => $date,
                'updated_at' => $date,
                'open_codes' => implode('|', $kjParam['open_codes']),
                'is_open' => SELF::OPNE
            ]);

            if ($gRes === false) {
                throw  new \Exception("游戏开奖结算失败！！！", 10003);
            }
            //解析每种玩法是否命中
            $souts = self::parsePayWaysSout($kjParam['open_codes']);
            $soutedItemNames = Collection::make($souts)->where('is_sout', '=', 1)->column('play_way_cn');
            $soutedItemNames = implode(',', $soutedItemNames);
            //本期投注记录
            $bets = self::bets($gameId);
            if ($bets) {
                foreach ($bets as $k => $bet) {
                    if ($bet['is_settlement'] == self::UNSETTLEMENT) {
                        //更新投注结果
                        $rate = self::resolveGameRateByBetSize($bet['bet_size']);
                        if (empty($rate)) {
                            throw new \Exception("为注单ID【{$bet['id']}结算失败，无法解析赔率】");
                        }
                        $profit = sprintf("%.2f", $rate['param_value'] * $bet['bet_amount']);
                        $playWayResult = Collection::make($souts)->where('play_way_cn', '=', $bet['bet_size'])->all();
                        $settlementAmount = ($playWayResult[0]['is_sout']) ? $profit : 0;
                        Db::name('GameBet')->where([
                            'id' => $bet['id'],
                            'is_settlement' => SELF::UNSETTLEMENT
                        ])->update(
                            [
                                'settlement_amount' => $settlementAmount,
                                'is_settlement' => self::SETTLEMENT,
                                'settlement_at' => $kjParam['open_at']
                            ]);
                        //资金结算
                        if (bccomp($settlementAmount, 0) == 1) {
                            $message = "投注ID【{$bet['id']}】中将返利{$profit}龙珠";
                            WalletService::UserWalletMoneyUpdate($bet['user_id'], $profit, 1, 'normal_money', 3, $message);
                        }
                    }
                }
            }
            //写入下一期到数据库
            $nextGameNo = $game['game_no']+1;
            $insert['game_no'] = $nextGameNo;
            $insert['created_at'] = $date;
            $insert['updated_at'] = $date;
            $nextGameId = Db::name('Game')->insertGetId($insert);
            //拉取到开奖结果开奖时间和系统时间的时间差
            $ttl = self::FENPAN_DELAY * 60 - (strtotime($date) - strtotime($kjParam['open_at']));
            $infenpansecond = 40;//提前40s封盘
            $ttl = strtotime($kjParam['open_at'])+210-$infenpansecond-time();
            //提前40s封盘
            self::pushFenpanQueue($nextGameId,$ttl);

            Db::commit();

            //推送系统已结算通知
            $tgBotService = new TelegramBotService();

            $openCodes = implode(' ', $kjParam['open_codes']);

            //结算统计
            $basicTemplates = "🎉🎉🎉\r\n竞猜游戏期数:【{$game['game_no']}】\r\n开奖号码:【{$openCodes}】\r\n详情:【{$soutedItemNames}】";
            if ($bets) {
                $callulateTmplates = self::calulateTmplates(self::calulate($gameId));
                $basicTemplates .= "中奖名单：\r\n" . implode("\r\n", $callulateTmplates);
            }
            $tgBotService->sedDefaultGroupMessage($basicTemplates);

            //推送下一期
            $text = "🎉🎉🎉竞猜游戏期数:【{$nextGameNo}】即将开始,恭祝各位好运！！！";
            $tgBotService->sedDefaultGroupMessage($text);

        } catch (\Exception $exception) {
            Db::rollback();
            echo $exception->getMessage();
            $text = "竞猜结算失败:【params:" . json_encode($game) . "detail:" . $exception->getMessage() . "】";
            Log::error($text);
        }
    }

    /**
     * 游戏检测
     * @param $betParam
     */
    public static function checkGame($betGameNo)
    {
        $game = Db::name('Game')->where(['game_no' => $betGameNo])->find();
        if (empty($game)) {
            throw new \Exception("期数不存在");
        }
        if ($game['is_open']) {
            throw new \Exception("本期数已开奖了！！！");
        }
        //是否处于封盘
        if (empty(self::subscribeHander()->get(self::FENPAK_KEY_PRFIX . $game['id']))) {
            throw new \Exception("本期截止投注！！！");
        }
        if (time() > strtotime($game['created_at']) + self::FENPAN_DELAY * 60) {
            throw new \Exception("本期投注时间截止！！！");
        }
        return $game;
    }


    /**
     * 生成游戏唯一订单号
     * @return string
     */
    protected static function makeOrderNo()
    {
        //生成24位唯一订单号码，格式：YYYY-MMDD-HHII-SS-NNNN,NNNN-CC，其中：YYYY=年份，MM=月份，DD=日期，HH=24格式小时，II=分，SS=秒，NNNNNNNN=随机数，CC=检查码

        @date_default_timezone_set("PRC");
        //订购日期
        $order_date = date('Y-m-d');

        $order_id_main = date('YmdHis') . rand(10000000, 99999999);

        //订单号码主体长度

        $order_id_len = strlen($order_id_main);

        $order_id_sum = 0;

        for ($i = 0; $i < $order_id_len; $i++) {

            $order_id_sum += (int)(substr($order_id_main, $i, 1));

        }
        return $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
    }


    public static function getGameConfig($paramName = '')
    {
        if ($paramName) {
            return Db::name('GameConfig')->where(['param_name' => $paramName])->find();
        }
        return Db::name('GameConfig')->select();
    }


    public static function getAvaibleMoneys($money)
    {
        $data = [];
        $limit = SELF::USDT_RECHARGE_PRICE_NUMS_LIMIT;
        $moneyStart = $money + $limit * 0.005 / 5;
        for ($i = 0; $i <= $limit; $i++) {
            if ($moneyStart + $i * 0.001 != $money) {
                $data[] = sprintf("%.3f", floatval($moneyStart + $i * 0.001));
            }
        }
        return $data;
    }


    /**
     * 30分钟内正在支付中的金额
     */
    public static function rechergeOrderUserAmounts()
    {
        $rechareOrderCancleTime = self::getGameConfig('recharge_cancle_minute')['param_value'];

        $map['status'] = GameRecharge::UN_PAY;
        return Db::name('GameRecharge')
            ->where($map)
            ->where('created_at', '>', date('Y-m-d H:i:s', time() - $rechareOrderCancleTime * 60))
            ->column('usdt_amount');
    }


    /**
     * 获取支付的usdt金额
     * @param $money
     */
    public static function getPayUsdtAmount($money)
    {
        $moneys = [];
        $limit = SELF::USDT_RECHARGE_PRICE_NUMS_LIMIT;
        $moneyStart = $money + $limit * 0.005 / 5;
        for ($i = 0; $i <= $limit; $i++) {
            if ($moneyStart + $i * 0.001 != $money) {
                $moneys[] = sprintf("%.3f", floatval($moneyStart + $i * 0.001));
            }
        }
        $onRechargeMoneys = self::rechergeOrderUserAmounts();
        //过滤usdtamount
        $getAvaibleMoneys = Collection::make($moneys)->diff($onRechargeMoneys)->all();
        return @$getAvaibleMoneys[array_rand($getAvaibleMoneys)];
    }


    /**
     * 游戏充值
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function recharge($data)
    {
        Db::startTrans();
        $TelegramBotService = new TelegramBotService();
        try {
            $fromUsertgId = $data['from']['id'];
            $user = Db::name('User')->where(['user_telegram_id' => $fromUsertgId])->find();
            if (empty($user)) {
                throw  new UserException([
                    'code' => User::NOT_EXISTS,
                    'msg' => '用户不存在,请先注册！！！'
                ]);
            }
            $amount = sprintf("%.2f", trim(str_replace("充值", '', $data['text'])));
            $rechargeMin = self::getGameConfig('recharge_min')['param_value'];
            if (bccomp($amount, $rechargeMin) == -1) {
                throw  new GameRechargeException([
                    'code' => GameRecharge::ILLEGAL_MIN_AMOUNT,
                    'msg' => "最低充值{$rechargeMin}龙珠！！！"
                ]);
            }
            $usdtAmount = bcdiv($amount, self::getGameConfig('usdt#longz')['param_value'], 2);
            //保留一位后面两位随机生成
            $usdtAmount = self::getPayUsdtAmount($usdtAmount);

            if (empty($usdtAmount)) {
                throw  new GameRechargeException([
                    'code' => GameRecharge::ILLEGAL_AMOUNT,
                    'message' => '当前金额不匹配,请稍后重试！！！'
                ]);
            }
            $usdt_trc20_address = self::getGameConfig('usdt_trc20_address')['param_value'];
            $recharge['order_no'] = self::makeOrderNo();
            $recharge['user_id'] = $user['id'];
            $recharge['amount'] = $amount;
            $recharge['usdt_amount'] = $usdtAmount;
            $recharge['to_usdt_address'] = $usdt_trc20_address;
            $recharge['created_at'] = $recharge['updated_at'] = date('Y-m-d H:i:s');

            $rid = Db::name('GameRecharge')->insertGetId($recharge);
            if (empty($rid)) {
                throw  new GameRechargeException([
                    'code' => GameRecharge::ILLEGAL_AMOUNT,
                    'message' => '当前金额不匹配,请稍后重试！！！'
                ]);
            }
            Db::commit();
            //投递到订单取消队列
            self::pushRechargeCancleQueue($rid);

            //给当前用户推送充值提示信息
            $TelegramBotService = new TelegramBotService();
            $recharge_cancle_minute = self::getGameConfig('recharge_cancle_minute')['param_value'];
            $sed = "付款提示：\r\n1:群置顶地址 {$usdt_trc20_address}\r\n2:传送金额 {$usdtAmount}u\r\n3 请严格按照小数点转账否则无法到账,请在{$recharge_cancle_minute}分钟内完成付款逾期订单自动取消\r\n4.请使用快速通道，无法到账或者延期到账的后果自负";
            $TelegramBotService->sendGroupMessage($data['chat']['id'], $sed, ['reply_to_message_id' => $data['message_id']]);
        } catch (\Exception $exception) {
            Db::rollback();;
            Log::error("充值异常【params:" . json_encode($data) . 'detail' . $exception->msg);
            $TelegramBotService->sendGroupMessage($data['chat']['id'], $exception->msg, ['reply_to_message_id' => $data['message_id']]);
        }
    }


    /*
     * 自动充值
     * @params $data 修改参数
     * @params $rechargeId 充值id
     */
    public static function setRechargeSuccess($data, $rechargeId)
    {
        Db::startTrans();
        $TelegramBotService = new TelegramBotService();
        try {
            $recharge = Db::name('GameRecharge')->where(['id' => $rechargeId])->lock(true)->find();
            if ($recharge['status'] == self::UNPAY) {
                //修改订单数据
                Db::name('GameRecharge')->where(['id' => $rechargeId])->update($data);

                //用户上分
                $msg = "游戏上分到账{$recharge['amount']}";

                WalletService::UserWalletMoneyUpdate($recharge['user_id'], $recharge['amount'], 1, 'normal_money', 1, $msg);

            }
            Db::commit();
            $user = Db::name('User')->where('id', $recharge['user_id'])->find();
            $TelegramBotService = new TelegramBotService();
            $sed = "@{$user['user_telegram']}您充值的{$recharge['amount']}龙珠已到账,请查询余额确认到账额度,充值账单查询请输入指令: recharge:单号";
            $TelegramBotService->sedDefaultGroupMessage($sed);
        } catch (\Exception $exception) {
            Db::rollback();;
            Log::error("充值回调异常【params:" . json_encode($data));
            $TelegramBotService->sendGroupMessage($data['chat']['id'], $exception->msg, ['reply_to_message_id' => $data['message_id']]);
        }
    }


    /*
     *
     *提现
     * @param $data  tg推送消息
     */
    public static function withdraw($data, $amount, $withdrawTrc20Address)
    {
        Db::startTrans();
        $TelegramBotService = new TelegramBotService();
        try {
            $fromUsertgId = $data['from']['id'];
            $user = Db::name('User')->where(['user_telegram_id' => $fromUsertgId])->find();
            if (empty($user)) {
                throw  new \Exception("用户不存在,请先注册！！！");
            }
            $wRes = WalletService::UserWallet($user['id']);
            if ($wRes['code'] !== 0) {
                throw  new \Exception($wRes['msg']);
            }
            $wallect = $wRes['data'];
            if (bccomp($amount, $wallect['normal_money']) == 1) {
                throw  new \Exception("您最大的提现龙珠数量为{$wallect['normal_money']}！！！");
            }
            //提币地址校验
            $net = Config::get('tronscan_network', 'testNet');
            $tronNet = TronApi::$net();

            $tRes = $tronNet->validatetrc20address($withdrawTrc20Address);
            if ($tRes->result != 1) {
                throw  new \Exception("提款地址有误");
            }
            $usdtAmount = bcdiv($amount, self::getGameConfig('usdt#longz')['param_value'], 2);
            //系统trc20usdt地址书否余额充足
            $fromKey = self::getGameConfig('usdt_trc20_privite_key')['param_value'];
            $credential = Credential::fromPrivateKey($fromKey);
            $from = $credential->address()->base58();
            $kit = new TronKit($tronNet, $credential);
            //USDT智能合约发行的地址固定死
            $contractAddress = Config::get('shopxo.contract_address');
            $inst = $kit->trc20($contractAddress);
            $balance = $inst->balanceOf($from);
//            $leftBalance = $balance-2000000;
//            if (bccomp($usdtAmount * 1000000,$leftBalance ) == 1) {
            //Log::info("竞猜提现【提现金额大宇系统钱包余额-预留2U的金额】");
//            }
            //下单
            $usdt_trc20_address = self::getGameConfig('usdt_trc20_address')['param_value'];
            $recharge['order_no'] = self::makeOrderNo();
            $recharge['user_id'] = $user['id'];
            $recharge['amount'] = $amount;
            $recharge['usdt_amount'] = $usdtAmount;
            $recharge['to_usdt_address'] = $withdrawTrc20Address;
            $recharge['from_usdt_address'] = $usdt_trc20_address;
            $recharge['created_at'] = $recharge['updated_at'] = date('Y-m-d H:i:s');
            $rid = Db::name('GameWithdraw')->insertGetId($recharge);
            if (empty($rid)) {
                throw  new \Exception("提现请求失败");
            }
            //增加冻结金额
            WalletService::UserWalletMoneyUpdate($user['id'], $amount, 1, 'frozen_money', 2, "提现增加冻结金额");
            //扣减可用余额
            WalletService::UserWalletMoneyUpdate($user['id'], $amount, 0, 'normal_money', 2, "提现扣减可用余额");
            Db::commit();
            //转账
            $decimals = Config::get('shopxo.contract_decimals');
            $chainsTranNumber = (int)str_pad(1, $decimals + 1, 0) * $recharge['usdt_amount'];

            //防止科学计数法
            $chainsTranNumber = number_format($chainsTranNumber, 0, '', '');
            $ret = $inst->transfer($withdrawTrc20Address, $chainsTranNumber);
            Db::name('GameWithdraw')->where('id', $rid)->update([
                'block_chain_transid' => $ret->tx->txID
            ]);
            //给当前用户推送充值提示信息
            $TelegramBotService = new TelegramBotService();
            $sed = "请稍等,正在为你处理！！！";
            $TelegramBotService->sendGroupMessage($data['chat']['id'], $sed, ['reply_to_message_id' => $data['message_id']]);

            //投递到提现订单确认队列中
            self::pushWithdrawConfirmQueue($rid);

        } catch (\Exception $exception) {
            Db::rollback();;
            Log::error("提现异常【params:" . json_encode($data) . 'detail' . $exception->getMessage());
            $TelegramBotService->sendGroupMessage($data['chat']['id'], $exception->getMessage(), ['reply_to_message_id' => $data['message_id']]);
        }
    }


    /**
     * 查询提现订单
     * @param $data
     * @return array|\PDOStatement|string|Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function searchWithDrawOrder($data)
    {
        $TelegramBotService = new TelegramBotService();

        try {
            $text = $data['text'];
            $orderNo = trim(explode(":", $text)['1']);
            if (!is_numeric($orderNo)) {
                throw  new \Exception("单号不合法！！！");
            }
            $fromUsertgId = $data['from']['id'];
            $user = Db::name('User')->where(['user_telegram_id' => $fromUsertgId])->find();
            if (empty($user)) {
                throw  new \Exception("用户不存在,请先注册！！！");
            }

            $order = Db::name('GameWithdraw')->where('order_no', $orderNo)->find();
            if (empty($order)) {
                throw  new \Exception("订单信息不存在！！！");
            }
            $statusText = ['处理中', '已转款', '转款失败'];
            $basicTemplates = "单号:{$order['order_no']}\r\n提现数量:{$order['amount']}龙珠\r\n等价usdt:{$order['usdt_amount']}U\r\n状态:{$statusText[$order['status']]}\r\n交易HASH:{$order['block_chain_transid']}U\r\n";
            if ($order['status'] == SELF::PAYED) {
                $basicTemplates .= '到账时间:' . $order['block_chain_pay_at'];
            }
            $TelegramBotService->sendGroupMessage($data['chat']['id'], $basicTemplates, ['reply_to_message_id' => $data['message_id']]);
        } catch (\Exception $exception) {
            $TelegramBotService->sendGroupMessage($data['chat']['id'], $exception->getMessage(), ['reply_to_message_id' => $data['message_id']]);
        }
    }


    /**
     * 获取用户投注记录
     * @param $data
     */
    public static function getUserBetsByTgId($tgId)
    {
        $user = Db::name('User')->where(['user_telegram_id' => $tgId])->find();
        $bets = Db::name('GameBet')->where([
            'user_id' => $user['id']
        ])->order(['created_at' => 'desc'])->select();
        return $bets;
    }


}

?>