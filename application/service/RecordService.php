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

/**
 * 记账机器人服务层
 * Class RecordService
 * @package app\service
 */
class RecordService
{

    protected $events = [
        'new_chat_members', 'forward_from', 'reply_to_message'
    ];


    protected function convertMethod($str)
    {
        $arr = explode('-', $str);
        for ($i = 1; $i < count($arr); $i++) {
            $arr[$i] = ($i != 1) ? ucfirst($arr[$i]) : $arr[$i];
        }
        $str = implode('', $arr);
        return $str;
    }

    /**
     * 发生的事件
     * @param $pushMsg
     */
    public function hanleEvent($pushMsg)
    {
        foreach ($this->events as $event) {
            if (isset($pushMsg[$event])) {
                call_user_func([$this, $this->convertMethod($event)], $pushMsg);
                break;
            }
        }
    }


    /**
     * 转发
     */
    protected function forwardFrom()
    {
        //todo

    }

    protected function newChatMembers()
    {
        //todo

    }

    protected function replyToMessage()
    {
        //todo
    }
    protected function isNeedToHandle($pushMessage)
    {

        if (($pushMessage['date'] + 20) < time()) {
            return ;
        }
    }










}

?>
