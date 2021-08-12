<?php


namespace app\service;


use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use think\Db;
use think\facade\Log;

class TelegramBotService
{

    private $token = '';
    private $chat_id = '';


    /**
     * 初始化
     */
    public function __construct()
    {
        //读取配置
        $common_site_telegram_bot_token = Db::name('Config')->where(['only_tag' => 'common_site_telegram_bot_token'])->find();
        $common_site_telegram_bot_chat_id = Db::name('Config')->where(['only_tag' => 'common_site_telegram_bot_chat_id'])->find();
        $this->token = $common_site_telegram_bot_token['value'];
        $this->chat_id = $common_site_telegram_bot_chat_id['value'];
    }

    public function test()
    {
//        $bot_api_key  = $this->token;
//        $bot_username = 'MessageAssistantBot';
////        $hook_url     = 'http://47.94.33.7:4455/';
//        $hook_url     = 'https://www.baidu.com';
//
//
//            // Create Telegram API object
//
////            $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
//            $telegram = new Telegram($bot_api_key, $bot_username);
//
//            // Set webhook
//            $result = $telegram->setWebhook($hook_url);
//            var_dump($result);
//            if ($result->isOk()) {
//                echo $result->getDescription();
//            }
////        } catch (TelegramException $e) {
////            // log telegram errors
////            // echo $e->getMessage();
////        }
//        return ;
    }

    /**
     * 消息回复
     */
    public function reply($text, $chat_id = '')
    {
        $goods_id = 0;
        //是否有这个分类
        $cate = Db::name('GoodsCategory')
            ->alias('gc')
            ->where('gc.name', 'like', '%' . $text . '%')
            ->find();
        if ($cate) {
            //随机获取一个商品
            $join = Db::name('GoodsCategoryJoin')->where(['category_id' => $cate['id']])->orderRaw("RAND()")->select();
            if ($join) {
                $goods_id = $join[0]['goods_id'];
            }
        }

        if (!$goods_id) {
            //模糊查询商品标题
            $goods = Db::name('Goods')->where('title', 'like', '%' . $text . '%')->orderRaw("RAND()")->select();
            if ($goods) {
                $goods_id = $goods[0]['id'];
            }
        }
        if (!$goods_id) {

            if (!$chat_id) {
                $chat_id = $this->chat_id;
            }
            $this->sendGroupMessage($chat_id, '对不起，找不到' . $text . '相关的商品');
        } else {
            $this->publishGoodsMessage($goods_id, $chat_id);
        }

        return $goods_id;
    }


    /**
     * 发送商品
     */
    public function publishGoodsMessage($goods_id, $chat_id = '')
    {

        $goods = Db::name('Goods')->where(['id' => $goods_id])->find();
        if (!$goods) {
            return false;
        }

        //商品的担保方
        $str = '';
        $assureStyles = GoodsService::getGoodsAssureStyles($goods_id);
        if ($assureStyles) {
            foreach ($assureStyles as $k => $v) {
                $str .= $v['assure_style_name'];
                if (end($assureStyles) != $v) {
                    $str .= "|";
                }
            }
        } else {
            $str = '不受担保';
        }

        //商品分类
        $is_top_one = false;
        $cates = GoodsService::getGoodsCateGory($goods_id);
        $cates_ids = GoodsService::getGoodsCateGoryIds($goods_id);
        if ($cates_ids) {
            $data = Db::name('GoodsCategory')
                ->field('c1.name as c1_name,c2.name as c2_name,c3.name as c3_name')
                ->alias('c1')
                ->join('GoodsCategory c2', 'c1.pid=c2.id')
                ->join('GoodsCategory c3', 'c2.pid=c3.id')
                ->where('c1.id', 'in', $cates_ids)
                ->where(['c3.id' => '1'])
                ->select();
            if ($data) {
                $is_top_one = true;
            }
        }
        $cates_text = implode(' | ', $cates);
        $is_one_day_text = $goods['is_one_day'] ? "是" : "否";


        //供需类型
        if ($goods['goods_type'] == '1') {
            $goods_type_text = '需求';
        } else {
            $goods_type_text = '供应';
        }
        //读取配置
        $common_site_telegram_bot_message_remarks = Db::name('Config')->where(['only_tag' => 'common_site_telegram_bot_message_remarks'])->find();


        $text = $goods['title'] . "
" . $goods['goods_type_info'] . "
序列号： 20040" . $goods['id'] . "
联系人： @" . $goods['user_telegram'] . "
担保方： " . $str . "
分    类： " . $cates_text . "
保证金： " . $goods['deposit'] . "
信誉度： " . str_repeat('☆', $goods['goods_reliability']) . "
";

        if ($is_top_one) {
            $text .= "费    率： " . $goods['goods_rate'] . "%
日    量： " . $goods['day_order_count'] . " 
24小时：" . $is_one_day_text . "
限    额： " . $goods['limit_moneys'] . "  
";
        }
        $text .= "供    需： " . $goods_type_text . "
发布者： " . getPublishName($goods['uid']) . "
时    间： " . date('Y年m月d号 H时', ($goods['add_time'])) . "
" . $common_site_telegram_bot_message_remarks['value'];

        if (!$chat_id) {
            $chat_id = $this->chat_id;
        }

        return $this->sendGroupMessage($chat_id, $text);
    }


    public function setWebHook($webHookUrl)
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/setwebhook';

        $data = [
            'url' => $webHookUrl,
        ];
        return json_decode(httpRequest($url, 'POST', $data), true);
    }


    public function setTgBannerNotifyUrl($webHookUrl)
    {
        $tgBannerBotToken = Db::name('Config')->where(['only_tag' => 'tgbanner_bot_token'])->find();
        $url = 'https://api.telegram.org/bot' . $tgBannerBotToken['value'] . '/setwebhook';
        $data = [
            'url' => $webHookUrl,
        ];
        return json_decode(httpRequest($url, 'POST', $data), true);
    }


    public function sedDefaultGroupMessage($text, $option = [])
    {
        return $this->sendGroupMessage($this->chat_id, $text, $option);
    }


    public function sendGroupMessage($chat_id, $text, $option = [])
    {
//        $keyboard = [
//            'inline_keyboard' => [
//                [
//                    ['text' => '骗子举报', 'callback_data' => 'someString'],
//                    ['text' => '查询骗子', 'callback_data' => 'someStrinxxxxxxxxxxxxxg']
//                ]
//            ]
//        ];
//        $encodedKeyboard = json_encode($keyboard);


        $url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
//            'parse_mode'=>'HTML',
            // 'reply_markup' => $encodedKeyboard

        ];
        $data = array_merge($data, $option);
        json_decode(httpRequest($url, 'POST', $data), true);

    }


    public function sendGroupMessageV2($tgToken, $chat_id, $text, $option = [])
    {

        $url = 'https://api.telegram.org/bot' . $tgToken . '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,

        ];
        $data = array_merge($data, $option);
        return json_decode(httpRequest($url, 'POST', $data), true);
    }


    /**
     * 群发消息
     * @param $chat_id
     * @param $text
     * @return mixed
     */
    public function sendGroupMessage2($chat_id, $text, $option = [])
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '骗子举报', 'callback_data' => 'someString'],
                    ['text' => '查询骗子', 'callback_data' => 'someStrinxxxxxxxxxxxxxg']
                ]
            ]
        ];
        $encodedKeyboard = json_encode($keyboard);


        $url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
//            'parse_mode'=>'HTML',
            'reply_markup' => $encodedKeyboard

        ];
        $data = array_merge($data, $option);
        return json_decode(httpRequest($url, 'POST', $data), true);
    }


    /**
     * 丰富发送文本
     * @param $chat_id
     * @param $text
     * @param array $option
     * @return mixed
     */
    public function sendGroupMessageWithOption($chat_id, $text, $option = [])
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
//            'parse_mode'=>'HTML',
//            'reply_markup' => $encodedKeyboard
        ];
        $data = array_merge($data, $option);
        return json_decode(httpRequest($url, 'POST', $data), true);
    }


    /**
     * 丰富发送文本
     * @param $chat_id
     * @param $text
     * @param array $option
     * @return mixed
     */
    public function sendGroupMessageWithOptionV2($chat_id, $text, $option = [], $withToken = '')
    {
        $withToken = $withToken ? $withToken : $this->token;
        $url = 'https://api.telegram.org/bot' . $withToken . '/sendMessage';

        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
//            'parse_mode'=>'HTML',
//            'reply_markup' => $encodedKeyboard
        ];
        $data = array_merge($data, $option);
        return json_decode(httpRequest($url, 'POST', $data), true);
    }


    /**
     * 用户禁言
     * @param $chat_id
     * @param $userId
     * @param $endTime
     */
    public function strictChatMember($chat_id, $userId, $endTime)
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/promoteChatMember';
        $data = [
            'chat_id' => $chat_id,
            'user_id' => $userId,
            'can_post_messages' => false,
        ];
        $result = httpRequest($url, 'POST', $data);
        Log::info("禁言用户请求返回数据{$result}");
        return $result;
    }


    /**
     * 删除消息
     * @param $chat_id
     * @param $message_id
     * @return bool|string
     */
    public function deleteMessage($message_id, $chat_id = '')
    {
        $chat_id = $chat_id ? $chat_id : $this->chat_id;
        $url = 'https://api.telegram.org/bot' . $this->token . '/deleteMessage';
        $data = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ];
        $result = json_decode(httpRequest($url, 'POST', $data), true);
        if (false == $result['ok']) {
            throw  new \Exception($result['description']);
        }

        return $result;
    }


    /**
     * 解析command 文本
     * @param $command start=>'投诉和举报command'
     */
    public function parseCommand($pushMsg, $command = 'start')
    {
        switch ($command) {
            case 'start':
                $sendText = '请选择您需要的服务';
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => '投注记录', 'callback_data' => '投注记录'],
                            ['text' => '账单查询', 'callback_data' => '账单查询']
                        ]
                    ]
                ];
                $encodedKeyboard = json_encode($keyboard);
                $option['reply_markup'] = $encodedKeyboard;
                break;
        }
        $this->sendGroupMessage($pushMsg['chat']['id'], $sendText, $option);
    }

}
