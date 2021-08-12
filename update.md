
### 定时任务
0 */1 * * * curl -sS --connect-timeout 10 -m 3600 'https://域名/index.php/message/timing/exposurepayList'
* * * * * curl  -sS --connect-timeout 10 -m 3600  http://doc.shopxo.com/message/timing/sendAd

*/5 * * * * curl  -sS --connect-timeout 10 -m 3600    https://www.97danbao.com/api/crontab/pushTgBanner

#黑支付游戏竞猜
*/5 * * * * curl  -sS --connect-timeout 10 -m 3600  http://doc.shopxo.com/message/timing/sendGameExposurepay
#黑支付游戏竞猜停止和开奖
*  * * * * curl  -sS --connect-timeout 10 -m 3600  http://doc.shopxo.com/message/timing/playGameExposurepay





说明：
1.cd  站点目录/public/  && php socket.php index start (-d:后台守护运行 测试可不加)  这是用来抓取游戏数据的
2.init.sql 为jincai初始化sql
3.所有的jc代码写到 /application/servie/gameService.php里面的 你可以去看下 
4.jc相关的数据表前缀统一是game_
5.服务器安装redis 并修改redis __keyevent@0__:expired  设置key过期事件 
   1.找到redis.conf配置文件,可以通过命令  find / | grep redis.conf
   2.修改配置文件，找到 notify-keyspace-events，默认是被注释的，改为   notify-keyspace-events Ex
6.运行command命令竟猜事件订阅   执行命令 php think subscribe  后台守护运行
7.开奖三方网站为   https://www.b6api.com/index/lottery_type.html    我还没开通  你可以去开通  开通了 修改gameservice 里面有个地址修改下
8.数据表 g_config  里面的 usdt_trc20_privite_key  是波场钱包的 私钥文件  转账usdt需要
9.todo  其他暂时没想到啥需要交代的 









