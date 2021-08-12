<?php


namespace app\common\exception;

/**
 * 游戏充值异常类
 * Class GameRechargeException
 * @package app\common\exception
 */
class GameRechargeException extends BaseException
{
    public $code = 200;
    public $msg = 'order_no does not exist.';
    public $errorCode = 200000;
}