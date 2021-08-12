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

use http\Client;
use think\Db;


/**
 * telgrama 机器人sdk
 * Class TgBotService
 * @package app\service
 */
class TgBotService
{
    public $botToken;

    public function setBotToken($token)
    {
        $this->botToken = $token;
        return $this;
    }


    /**
     *设置回调地址
     * @param $webHookUrl
     * @return mixed
     */
    public function setWebHookUrl($webHookUrl)
    {
        $url = 'https://api.telegram.org/bot' . $this->botToken . '/setwebhook';
        $data = [
            'url' => $webHookUrl,
        ];
        return json_decode(httpRequest($url, 'POST', $data), true);
    }


    /**
     * 发送消息
     * @param $chat_id
     * @param $text
     * @param array $option
     * @return mixed
     */
    public function sendMessage($chat_id, $text, $option = [])
    {

        $url = 'https://api.telegram.org/bot' . $this->botToken . '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,

        ];
        $data = array_merge($data, $option);
        return json_decode(httpRequest($url, 'POST', $data), true);
    }


    /**
     * 删除消息
     * @param $chat_id
     * @param $message_id
     * @return bool|string
     */
    public function deleteMessage($message_id, $chat_id = '')
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/deleteMessage';
        $data = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ];
        return json_decode(httpRequest($url, 'POST', $data), true);
    }












}

?>
