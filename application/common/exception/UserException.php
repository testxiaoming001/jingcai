<?php


namespace app\common\exception;

/**
 * 游戏充值异常类
 * Class GameRechargeException
 * @package app\common\exception
 */
class UserException extends BaseException
{
    public $code = 200;
    public $msg = 'user does not exist.';
    public $errorCode = 400000;
}