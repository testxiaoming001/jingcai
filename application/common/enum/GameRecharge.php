<?php


namespace app\common\enum;


/**
 * 游戏充值相关枚举
 * Class GameRecharge
 * @package app\common\enum
 */
class GameRecharge
{

    //未支付
    const  UN_PAY = 0;

    //已支付
    const PAYED = 1;


    //已关闭
    const CLOSED = 2;


    //非法金额
    const  ILLEGAL_AMOUNT = 30000;

    //错误金额
    const  ILLEGAL_MIN_AMOUNT = 30001;


}