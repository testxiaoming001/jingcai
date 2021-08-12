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
namespace app\plugins\wallet\index;

use app\plugins\wallet\service\PayService;

/**
 * 钱包 - 充值异步处理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Rechargenotify
{
    /**
     * 支付异步处理
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-03-04T14:35:38+0800
     * @param   [array]          $params [输入参数]
     */
    public function notify($params = [])
    {
        $ret = PayService::Notify($params);
        if($ret['code'] == 0)
        {
            $this->SuccessReturn();
        }
        $this->ErrorReturn();
    }

    /**
     * 成功返回
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-12
     * @desc    description
     */
    private function SuccessReturn()
    {
        // 支付插件是否自定义返回内容
        $this->ContentReturn('SuccessReturn');

        // 结束输出
        die('success');
    }

    /**
     * 失败返回
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-09-12
     * @desc    description
     */
    private function ErrorReturn()
    {
        // 支付插件是否自定义返回内容
        $this->ContentReturn('ErrorReturn');

        // 结束输出
        die('error');
    }

    /**
     * 输出支付插件自定义内容
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-01
     * @desc    description
     * @param   [string]          $action [操作方法]
     */
    private function ContentReturn($action)
    {
        $payment = 'payment\\'.PAYMENT_TYPE;
        if(class_exists($payment))
        {
            $payment_obj = new $payment();
            if(method_exists($payment_obj, $action))
            {
                die($payment_obj->$action());
            }
        }
    }
}
?>