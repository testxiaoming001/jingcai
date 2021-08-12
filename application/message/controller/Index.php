<?php


namespace app\message\controller;

use app\plugins\wallet\service\WalletService;
use app\service\CoinSdkService;
use app\service\GameService;
use app\service\MessageBotService;
use app\service\TelegramBotService;
use app\service\TgBotService;
use app\service\UserService;
use think\Cache;
use think\cache\driver\Redis;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Log;
use think\Request;
use TronTool\Credential;
use TronTool\TronApi;


class Index extends Controller
{


    /**
     *测试方法
     */
    public function ok()
    {

//        GameService::withdraw_confirm(2083);
//        exit;
        //$json = '{"update_id":133690810,
//"message":{"message_id":418,"from":{"id":1458266103,"is_bot":false,"first_name":"jianyun","last_name":"li"},"chat":{"id":-1001521837140,"title":"\u5546\u57ce\u5168\u5c40\u673a\u5668\u4eba","type":"supergroup"},"date":1627872462,"text":"提现 150 到 TLxhGZ8z8b9dT8yDpdjwuUhNqx6o1RszQU"}}';
        $json = '{"update_id":133691109,
"callback_query":{"id":"7918795802300019231","from":{"id":1843738323,"is_bot":false,"first_name":"\u6280\u672f","username":"jishuzhichi003","language_code":"zh-hans"},"message":{"message_id":19,"from":{"id":1924352206,"is_bot":true,"first_name":"globalshopxobot","username":"globalshopxobot"},"chat":{"id":1843738323,"first_name":"\u6280\u672f","username":"jishuzhichi003","type":"private"},"date":1628011976,"text":"\u8bf7\u9009\u62e9\u60a8\u9700\u8981\u7684\u670d\u52a1","reply_markup":{"inline_keyboard":[[{"text":"\u6295\u6ce8\u8bb0\u5f55","callback_data":"\u6295\u6ce8\u8bb0\u5f55"},{"text":"\u8d26\u5355\u67e5\u8be2","callback_data":"\u8d26\u5355\u67e5\u8be2"}]]}},"chat_instance":"7458702030834859686","data":"\u8d26\u5355\u67e5\u8be2"}}';
        $data = json_decode($json, true);
        // $pushMsg = isset($data['message']) ? $data['message'] : $data['edited_message'];
        if (array_key_exists('callback_query', $data)) {
            MessageBotService::hookCallbackEvent($data['callback_query']);
            return;
        }

    }

    /**
     * 获取消息
     */
    public function message()
    {

        try {

            $json = file_get_contents("php://input");
            file_put_contents('./test.log', $json, FILE_APPEND);
            $data = json_decode($json, true);
            //parse各种事件  消息事件&行为事件
            if (array_key_exists('callback_query', $data)) {
                MessageBotService::hookCallbackEvent($data['callback_query']);
                return;
            }


            //有可能是发送消息 有可能是编辑消息

            $pushMsg = isset($data['message']) ? $data['message'] : $data['edited_message'];
            //超时不候
//            if (($pushMsg['date'] + 20) < time()) {
//                return;
//            }

            //初始化事件
            MessageBotService::initEvent($pushMsg);


            //只要是来自于机器人的消息10s后自动删除
            if ($pushMsg['from']['is_bot']) {
                MessageBotService::HookBootMessage($pushMsg);
            }


            //新用户入群事件
            if (array_key_exists('new_chat_members', $pushMsg) && $pushMsg['new_chat_members'] && $pushMsg['new_chat_participant']) {
                MessageBotService::hookNewMemberEevent($pushMsg);
                return;
            }


            //监听用户从其他地方抓转发过来的事件
            if (array_key_exists('forward_from', $pushMsg) && $pushMsg['forward_from']) {
                MessageBotService::hookForwardEvent($pushMsg);
                return;
            }

            //监听转发消息
            if (array_key_exists('reply_to_message', $pushMsg) && $pushMsg['reply_to_message']) {
                MessageBotService::hookReplyEvent($pushMsg);
                return;
            }
            //######################发送基本文本事件处理########################
            //回调文本校验
            if (!isset($pushMsg['text']) || empty($pushMsg['text'])) {
                return;
            }


            //用户说话投诉提醒
            $MessageBotService = new MessageBotService();
            //处理非法的字符
            $MessageBotService->handleIllegalKeyworlds($pushMsg);


            $MessageBotService->complainRemind($pushMsg['chat']['id'], $pushMsg['from']['id'], $pushMsg['from']['first_name']);


            //连续三次相同进行禁言提醒
//        $times = 3;
//        MessageBotService::sendMessageWhenSpeckSameWords($pushMsg, $times);


            //处理特殊文本
            $text = $pushMsg['text'];
            if ($text == '注册') {
                $MessageBotService->botRegister($pushMsg);
                return;
            }

            //更新用户说话次数
            UserService::updateSayTimes($pushMsg['from']['id']);


            //提现单号查询
            if (strpos($text, 'withdraw') !== false && strpos($text, ':') !== false) {
                GameService::searchWithDrawOrder($pushMsg);
                return;
            }

            //签到派送龙珠
            if ($text == '签到') {
                $MessageBotService->doSign($pushMsg);
                return;
            }

            //是否促发游戏
            $regex = '/^(\d{7})\s*(大|小|顺子|对子|豹子)\s*(\d+)$/';
            if (preg_match($regex, $text, $matches)) {
                $MessageBotService->doGameBet($pushMsg, $matches);
                return;
            }


//        ① 群内广告铭感词自动删除排除特殊的tg

            //①支付曝光游戏竞猜处理
//        if (strpos($text, '支付') !== false) {
//            MessageBotService::payPayGame($pushMsg);
//            return;
//        }
            //②禁言通知  禁言1000分钟
            if (strpos($text, '禁言') !== false) {
                $limitMinus = str_replace('分钟', '', str_replace('禁言', '', $pushMsg['text']));
                $endTime = time() + (int)$limitMinus * 60;
                $telgram = new TelegramBotService();
                $telgram->strictChatMember($pushMsg['chat']['id'], $pushMsg['from']['id'], $endTime);
                return;
            }
            //③注册
            if ($text == '注册') {
                $MessageBotService->botRegister($pushMsg);
                return;
            }


            //④查余额
            if ($text == '查余额') {

                $MessageBotService->queryBalance($pushMsg);
                return;
            }
            //⑤曝光
            if (strstr($text, '我要曝光')) {
                $MessageBotService->exposurePay($pushMsg);
                return;
            }
            //⑥充值
            if (strstr($text, '充值') && strstr($text, '@')) {
                if (is_numeric(trim(mb_substr(strstr($text, "充值"), 2)))) {
                    $MessageBotService->adminToUserRecharge($pushMsg);
                }
                return;
            }

            #游戏充值
            if (strstr($text, '充值')) {
                if (is_numeric(trim(mb_substr(strstr($text, "充值"), 2)))) {
                    GameService::recharge($pushMsg);
                }
                return;
            }


            //处理提现
            $regex = '/^提现\s*(\d+)\s*到\s*(\w+)$/';
            if (preg_match($regex, $text, $matches)) {
                GameService::withdraw($pushMsg, $matches[1], $matches[2]);
                return;
            }


            //⑦找商品
            if (strstr($text, '找') && strpos($text, '找') == 0) {
                $text = mb_substr(strstr($text, '找'), 1);
                //调用消息发送
                $TelegramBotService = new TelegramBotService();
                $TelegramBotService->reply(trim($text));
                return;
            }
            //⑧查询个人信息

            if ($text == '查询信息') {
                $MessageBotService->queryUserinfo($pushMsg);
                return;
            }

            //⑨当前发送文本的用户有投诉操作
            if (cache('complain_' . $pushMsg['from']['id'])) {
                cache('trigger_callback_complain_btn_' . $pushMsg['from']['id'], null);
                MessageBotService::hookMemberComplainEvent($pushMsg);
                return;
            }

            //10 解析command 文本
            if (strstr($pushMsg['text'], '/start')) {
                $TelegramBotService = new TelegramBotService();
                $TelegramBotService->parseCommand($pushMsg, 'start');
                return;
            }
            //用户触发command中的按钮选项tg回调过来处理逻辑
            return;
            //其他文本处理todo

        } catch (\Exception $exception) {
            \think\facade\Log::error("shopxo全局商城机器人处理回调数据报错【{$exception->getMessage()}】");
            return;
        }

    }


    /**
     * tgbanner机器人回调消息通知入口
     */
    public function tgBannerMessage()
    {
        $json = file_get_contents("php://input");
//        $json = "{\"update_id\":957114321,
//\"message\":{\"message_id\":5,\"from\":{\"id\":1458266103,\"is_bot\":false,\"first_name\":\"jianyun\",\"last_name\":\"li\"},\"chat\":{\"id\":-1001306525458,\"title\":\"tg\u5e7f\u544a\u7fa4\",\"type\":\"supergroup\"},\"date\":1623490147,\"text\":\"\u6dfb\u52a0\u5e7f\u544a\"}}";


        file_put_contents('./tg_banner_bot_notify.log', $json, FILE_APPEND);
        $data = json_decode($json, true);

        //有可能是发送消息 有可能是编辑消息
        $pushMsg = isset($data['message']) ? $data['message'] : $data['edited_message'];


        //######################发送基本文本事件处理########################
        //回调文本校验
        if (!isset($pushMsg['text']) || empty($pushMsg['text'])) {
            return;
        }


        $MessageBotService = new MessageBotService();
        //处理特殊文本
        $text = $pushMsg['text'];
        if ($text == '添加广告') {
            $MessageBotService->beforeAddTgBanner($pushMsg);
            return;
        }
        $isDoAddTgBanner = cache("wait_add_tg_banner");
        if ($isDoAddTgBanner) {
            $MessageBotService->handleaddTgBanner($pushMsg);
        }


    }


    public function setUrl()
    {
        $a = new TelegramBotService();
        $request = \think\facade\Request::instance();
        $url = $request->domain() . '/index.php/message/index/message';
//        $url =  'https://www.baidu.com/';

        $b = $a->setWebHook($url);

        var_dump($b);
    }


    public function setTgBannerNotifyUrl()
    {
        $a = new TelegramBotService();
        $request = \think\facade\Request::instance();
        $url = $request->domain() . '/index.php/message/index/tgBannerMessage';

        $b = $a->setTgBannerNotifyUrl($url);
        var_dump($b);
    }


    /*********************20210701新增****************************************************/
    protected $tgBotService;

    private $orderWarningRebotToken;


    /**
     * 获取订单机器人机器人的token
     * @return mixed
     */
    protected function getOrderWarningRebotToken()
    {
        return Db::name('Config')->where(
            ['only_tag' => 'tg_order_warning_robot_token']
        )->value('value');
    }


    public function __construct($app = null, TgBotService $tgBotService)
    {
        parent::__construct($app);
        $this->orderWarningRebotToken = $this->getOrderWarningRebotToken();
        $this->tgBotService = $tgBotService;
    }


    /**
     * 设置回到通知地址
     */
    public function setWebHookUrl()
    {
        $url = $this->request->domain() . '/index.php/message/index/notify';
        $result = $this->tgBotService->setBotToken($this->orderWarningRebotToken)->setWebHookUrl($url);
        var_dump($result);
    }


    /**
     * 消息回调入口
     */
    public function notify()
    {
        $json = file_get_contents("php://input");
        \think\facade\Log::info("订单警告机器人回调数据" . $json);
        $data = json_decode($json, true);
        //历史的会话数据会推送过来
        $pushMsg = isset($data['message']) ? $data['message'] : $data['edited_message'];
        //处理文本
        $text = $pushMsg['text'];
        if (!isset($text) || empty($text)) {
            return;
        }
        if (strpos($text, '授权机器人') !== false) {
            //绑定私聊通知人以及群组
            Db::name('Config')->where(['only_tag' => 'tg_order_warning_rebot_in_chat'])->setField('value', $pushMsg['chat']['id']);
            $this->tgBotService->setBotToken($this->orderWarningRebotToken)->sendMessage($pushMsg['chat']['id'], '机器人已授权订单警告,连续十笔订单未支付机器人将自动通知出来!!!');
        }
    }


}
