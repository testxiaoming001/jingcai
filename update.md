
### 定时任务
0 */1 * * * curl -sS --connect-timeout 10 -m 3600 'https://域名/index.php/message/timing/exposurepayList'
* * * * * curl  -sS --connect-timeout 10 -m 3600  http://doc.shopxo.com/message/timing/sendAd

*/5 * * * * curl  -sS --connect-timeout 10 -m 3600    https://www.97danbao.com/api/crontab/pushTgBanner

#黑支付游戏竞猜
*/5 * * * * curl  -sS --connect-timeout 10 -m 3600  http://doc.shopxo.com/message/timing/sendGameExposurepay
#黑支付游戏竞猜停止和开奖
*  * * * * curl  -sS --connect-timeout 10 -m 3600  http://doc.shopxo.com/message/timing/playGameExposurepay
