<?php


namespace app\common\command;


use app\service\ChannelService;
use think\cache\driver\Redis;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Log;

/**
 * 订阅者频道
 * Class Subscribe
 * @package app\common\command
 */
class Subscribe extends Command
{

    /**
     * 订阅者驱动
     * @var object|null
     */
    protected $driver;

    protected function configure()
    {
        $this->setName('subscribe')->setDescription('subscribe the channels by redis');
    }

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->driver = Cache::connect(['type' => 'redis'])->handler();
    }

    /**
     * 执行订阅
     * @param Input $input
     * @param Output $output
     * @return int|void|null
     */
    protected function execute(Input $input, Output $output)
    {

        //避免在默认的配置下，1分钟后终端了与redis服务器的链接
        ini_set('default_socket_timeout', -1);
        //订阅
        $channelService = app(ChannelService::class);
        $this->driver->psubscribe(array_keys($channelService->chanelFunMap), function ($redis, $pattern, $chan, $msg) use ($channelService) {
            $fun = $channelService->chanelFunMap[$chan];
            //回调到channelservice中去处理
            echo "正在处理channel:{$chan}},调用方法{$fun},处理参数{$msg}";
            try {
                call_user_func([$channelService, $fun], $msg);
            } catch (\Exception $exception) {
                Log::error("订阅频道{$chan}处理数据{$msg}失败,原因：【{$exception->getMessage()}】");
            }
        });

        $output->writeln('redis后端已订阅频道');
    }

}