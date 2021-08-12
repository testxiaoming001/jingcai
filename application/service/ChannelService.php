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

use think\Db;
use app\service\ResourcesService;

/**
 * 频道服务
 * Class ChannelService
 * @package app\service
 */
class ChannelService
{

    public $chanelFunMap;

    public function __construct($chanelFunMap = [])
    {
        $this->chanelFunMap = array_merge($this->initFunMap(), $chanelFunMap);
    }

    /**
     * 初始化频道
     * @return string[]
     */
    public function initFunMap()
    {
        return [
            'task_queue' => 'task_queue',
            '__keyevent@0__:expired' => 'handleKeyExpired'//select 0
        ];
    }


    /**
     * key 过期事件
     * @param $msg
     */
    public function handleKeyExpired($msg)
    {
        $doTags = [
            'tg_message', 'vip_task', 'game'
        ];
        $msg = explode(':', $msg);
        $tag = $msg[0];
        if (!in_array($tag, $doTags)) {
            return;
        }
        //机器人发的消息
        if ($tag == 'tg_message') {
            $TelegramBotService = new TelegramBotService();
            array_shift($msg);
            list($chatId, $messageId) = $msg;
            $TelegramBotService->deleteMessage($messageId, $chatId);
            return;
        }
        if ($tag == 'game') {
            array_shift($msg);
            list($method, $gameId) = $msg;
            call_user_func([GameService::class, $method], $gameId);
            return;
        }


    }


}

?>