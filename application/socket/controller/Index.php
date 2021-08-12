<?php

namespace app\socket\controller;

use app\service\GameService;
use think\Controller;
use Workerman\Lib\Timer;
use Workerman\Worker;


class Index extends Controller
{

    public function index()
    {
        include_once ROOT . 'extend/socketCompents/autoload.php';

        // 创建一个Worker监听2346端口，使用websocket协议通讯
        $ws_worker = new Worker("websocket://0.0.0.0:2346");
        // 启动4个进程对外提供服务
        $ws_worker->count = 4;
        // 当收到客户端发来的数据后返回hello $data给客户端
        $ws_worker->onMessage = function ($connection, $data) {
            // 向客户端发送hello $data
            $connection->send('hello ' . $data);
        };
        $ws_worker->onWorkerStart = function ($worker) {
            //在一号进程上安装定时器拉取开奖结果
            $id = $worker->id;
            if ($id == 0) {
                $worker->timerid = Timer::add(2, function () {
                    echo "正在采集加拿大28开奖结果\r\n";
                    GameService::syncKJdata();
                });
            }
        };
        $ws_worker->onWorkerStop = function ($worker) {
            // $worker->id == 0 && Timer::del($worker->timerid);
        };
        // 运行worker
        Worker::runAll();

    }
}
