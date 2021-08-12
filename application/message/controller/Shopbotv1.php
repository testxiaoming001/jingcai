<?php


namespace app\message\controller;

use app\plugins\wallet\service\WalletService;
use app\service\MessageBotService;
use app\service\TelegramBotService;
use app\service\TgBotService;
use app\service\UserService;
use think\Cache;
use think\cache\driver\Redis;
use think\Controller;
use think\Db;
use think\Log;
use think\Request;


class Shopbotv1 extends Controller
{





    /**
     * ShopBotV1机器人token
     */
    const SHOPBOT_V1_TOKEN = "1859216842:AAFliGFUr4x3F2q1yGq6TMZce1IwR2NNfag";


    /**
     * 设置回调地址
     */
    public function setWebHookUrl(TgBotService $tgBotService)
    {
        $url = $this->request->domain() . '/index.php/message/Shopbotv1/notify';
        $result = $tgBotService->setBotToken(self::SHOPBOT_V1_TOKEN)->setWebHookUrl($url);
        var_dump($result);
    }


    /*
     * 测试新成员入群
     */
    public function testNewMember()
    {

        $json = '{"update_id":135468915,
"message":{"message_id":324,"from":{"id":1458266103,"is_bot":false,"first_name":"jianyun","last_name":"li"},"chat":{"id":-1001366870194,"title":"test","type":"supergroup"},"date":1614616984,"new_chat_participant":{"id":1494186771,"is_bot":false,"first_name":"meisha","last_name":"qiao","language_code":"zh-hans"},"new_chat_member":{"id":1494186771,"is_bot":false,"first_name":"meisha","last_name":"qiao","language_code":"zh-hans"},"new_chat_members":[{"id":1494186771,"is_bot":false,"first_name":"meisha","last_name":"qiao","language_code":"zh-hans"}]}}';
        $data = json_decode($json, true);
        $message = $data['message'];

        if (array_key_exists('new_chat_members', $message) && $message['new_chat_members']) {
            MessageBotService::hookNewMemberEevent($message);
        }


        dd($data);
    }


    /**
     * 回调
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notify()
    {
        $json = file_get_contents("php://input");
        file_put_contents('./shopBotv1.log', $json, FILE_APPEND);
        $data = json_decode($json, true);
        //有可能是发送消息 有可能是编辑消息
        $pushMsg = isset($data['message']) ? $data['message'] : $data['edited_message'];
        //超时不候
        if (($pushMsg['date'] + 20) < time()) {
            return;
        }
        //新用户入群事件
        if (array_key_exists('new_chat_members', $pushMsg) && $pushMsg['new_chat_members'] && $pushMsg['new_chat_participant']) {
            MessageBotService::hookNewMemberEeventV2($pushMsg, self::SHOPBOT_V1_TOKEN);
            return;
        }
    }


}
