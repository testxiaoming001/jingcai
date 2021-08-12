<?php


namespace app\message\controller;

use app\service\RecordService;
use app\service\TgBotService;
use think\Controller;


/**
 * 记账机器人
 * Class RecordBelongs
 * @package app\message\controller
 */
class Record extends Controller
{
    const TECORD_BELONGS_BOT_TOKEN = '1842933045:AAHmLCO1ZmspM15mflp0l9n5eim7sz6KHsA';

    protected $tgBotService;
    protected $recordService;

    public function __construct($app = null, TgBotService $tgBotService, RecordService $recordService)
    {
        parent::__construct($app);
        $this->tgBotService = $tgBotService;
        $this->recordService = $recordService;
    }


    /**
     * 设置回到通知地址
     */
    public function setWebHookUrl()
    {
        $url = $this->request->domain() . '/index.php/message/record/notify';
        $result = $this->tgBotService->setBotToken(self::TECORD_BELONGS_BOT_TOKEN)->setWebHookUrl($url);
        dd($result);
    }


    /**
     * 消息回调入口
     */
    public function notify()
    {
        $json = file_get_contents("php://input");
        file_put_contents('./recordBotNotifyData.log', $json, FILE_APPEND);
        $data = json_decode($json, true);
        //历史的会话数据会推送过来
        $pushMsg = isset($data['message']) ? $data['message'] : $data['edited_message'];
        if ($this->recordService->isNeedToHandle($pushMsg)) {

        }

        //处理事件
        $this->recordService->hanleEvent($pushMsg);
        //处理文本
        $this->recordService->hanleMessage($pushMsg);


    }


}
