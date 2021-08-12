<?php


namespace app\message\controller;

use app\service\RecordService;
use app\service\TgBotService;
use think\Controller;
use think\Db;
use think\Log;


/**
 * 订单警告机器人
 * Class RecordBelongs
 * @package app\message\controller
 */
class OrderWarning extends Controller
{


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
        $url = $this->request->domain() . '/index.php/message/OrderWarning/notify';
        $result = $this->tgBotService->setBotToken($this->orderWarningRebotToken)->setWebHookUrl($url);
        var_dump($result);
    }


    /**
     * 消息回调入口
     */
    public function notify()
    {
        $json = file_get_contents("php://input");
        file_put_contents("./order_warning_bot.log", "订单警告机器人回调数据" . $json, FILE_APPEND);

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
            $this->tgBotService->setBotToken($this->orderWarningRebotToken)->sendMessage($pushMsg['chat']['id'], '机器人已授权订单警告,连续15笔订单未支付机器人将自动通知出来!!!');
            return;
        }
    }


}
