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
namespace app\service;

use think\Db;
use app\service\MessageService;
use app\service\UserService;

/**
 * 积分服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class IntegralService
{
    /**
     * [UserIntegralLogAdd 用户积分日志添加]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-05-18T16:51:12+0800
     * @param    [int]                   $user_id           [用户id]
     * @param    [int]                   $original_integral [原始积分]
     * @param    [int]                   $operation_integral[操作积分]
     * @param    [string]                $msg               [操作原因]
     * @param    [int]                   $type              [操作类型（0减少, 1增加）]
     * @param    [int]                   $operation_id      [操作人员id]
     * @return   [boolean]                                  [成功true, 失败false]
     */
    public static function UserIntegralLogAdd($user_id, $original_integral, $operation_integral, $msg = '', $type = 0, $operation_id = 0)
    {
        $data = array(
            'user_id'               => intval($user_id),
            'original_integral'     => intval($original_integral),
            'operation_integral'    => intval($operation_integral),
            'msg'                   => $msg,
            'type'                  => intval($type),
            'operation_id'          => intval($operation_id),
            'add_time'              => time(),
        );
        $data['new_integral'] = ($data['type'] == 1) ? $data['original_integral']+$data['operation_integral'] : $data['original_integral']-$data['operation_integral'];
        $log_id = Db::name('UserIntegralLog')->insertGetId($data);
        if($log_id > 0)
        {
            $type_msg = lang('common_integral_log_type_list')[$type]['name'];
            $detail = $msg.'积分'.$type_msg.$operation_integral;
            MessageService::MessageAdd($user_id, '积分变动', $detail, '积分', $log_id);
            return true;
        }
        return false;
    }

    /**
     * 列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function IntegralLogList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $field = '*';
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];

        // 获取数据列表
        $data = Db::name('UserIntegralLog')->where($where)->field($field)->limit($m, $n)->order($order_by)->select();
        if(!empty($data))
        {
            $integral_log_type_list = lang('common_integral_log_type_list');
            foreach($data as &$v)
            {
                // 用户信息
                if(isset($v['user_id']))
                {
                    if(isset($params['is_public']) && $params['is_public'] == 0)
                    {
                        $v['user'] = UserService::GetUserViewInfo($v['user_id']);
                    }
                }

                // 操作类型
                $v['type_text'] = $integral_log_type_list[$v['type']]['name'];

                // 时间
                $v['add_time_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['add_time_date'] = date('Y-m-d', $v['add_time']);
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function IntegralLogTotal($where = [])
    {
        return (int) Db::name('UserIntegralLog')->where($where)->count();
    }

    /**
     * 前端积分列表条件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UserIntegralLogListWhere($params = [])
    {
        // 条件初始化
        $where = [];

        // 用户id
        if(!empty($params['user']))
        {
            $where[] = ['user_id', '=', $params['user']['id']];
        }

        if(!empty($params['keywords']))
        {
            $where[] = ['msg', 'like', '%'.$params['keywords'] . '%'];
        }

        // 是否更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            if(isset($params['type']) && $params['type'] > -1)
            {
                $where[] = ['type', '=', intval($params['type'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['add_time', '<', strtotime($params['time_end'])];
            }
        }

        return $where;
    }

    /**
     * 订单商品积分赠送
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function OrderGoodsIntegralGiving($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order_id',
                'error_msg'         => '订单id有误',
            ]
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 订单
        $order = Db::name('Order')->field('id,user_id,status')->find(intval($params['order_id']));
        if(empty($order))
        {
            return DataReturn('订单不存在或已删除，终止操作', 0);
        }
        if(!in_array($order['status'], [4]))
        {
            return DataReturn('当前订单状态不允许操作，未完成', 0);
        }

        // 获取用户信息
        $user = Db::name('User')->field('id')->find($order['user_id']);
        if(empty($user))
        {
            return DataReturn('用户不存在或已删除，终止操作', 0);
        }

        // 获取订单商品
        $order_detail = Db::name('OrderDetail')->where(['order_id'=>$params['order_id']])->field('goods_id,total_price')->select();
        if(!empty($order_detail))
        {
            // 获取赠送积分的商品
            $goods_give = Db::name('Goods')->where(['id'=>array_column($order_detail, 'goods_id')])->column('give_integral', 'id');

            // 循环发放
            foreach($order_detail as $dv)
            {
                if(array_key_exists($dv['goods_id'], $goods_give))
                {
                    $give_rate = $goods_give[$dv['goods_id']];
                    if($give_rate > 0 && $give_rate <= 100)
                    {
                        // 实际赠送积分
                        $give_integral = intval(($give_rate/100)*$dv['total_price']);
                        if($give_integral >= 1)
                        {
                            // 用户积分添加
                            $user_integral = Db::name('User')->where(['id'=>$user['id']])->value('integral');
                            if(!Db::name('User')->where(['id'=>$user['id']])->setInc('integral', $give_integral))
                            {
                                return DataReturn('用户积分赠送失败['.$params['order_id'].'-'.$goods_id.']', -10);
                            }

                            // 积分日志
                            self::UserIntegralLogAdd($user['id'], $user_integral, $give_integral, '订单商品完成赠送', 1);
                        }
                    }
                }
            }
            return DataReturn('操作成功', 0);
        }
        return DataReturn('没有需要操作的数据', 0);
    }

    /**
     * 订单商品积分释放
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-28
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function OrderGoodsIntegralRollback($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order_id',
                'error_msg'         => '订单id有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order_detail_id',
                'error_msg'         => '订单详情id有误',
            ]
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 订单是否存在完成状态（订单赠送积分的条件是完成赠送）
        $order_status_history = Db::name('OrderStatusHistory')->where(['order_id'=>intval($params['order_id'])])->column('new_status');
        if(empty($order_status_history) || !in_array(4, $order_status_history))
        {
            return DataReturn('订单状态有误或未存在完成状态，终止操作', 0);
        }

        // 订单详情
        $order_detail = Db::name('OrderDetail')->field('id,user_id,order_id,goods_id,total_price,refund_price')->find(intval($params['order_detail_id']));
        if(empty($order_detail))
        {
            return DataReturn('订单详情不存在或已删除，终止操作', 0);
        }

        // 获取用户信息
        $user = Db::name('User')->field('id,integral')->find($order_detail['user_id']);
        if(empty($user))
        {
            return DataReturn('用户不存在或已删除，终止操作', 0);
        }

        // 获取商品相关信息
        $give_rate = Db::name('Goods')->where(['id'=>$order_detail['goods_id']])->value('give_integral');
        if($give_rate > 0 && $give_rate <= 100)
        {
            $give_integral = intval(($give_rate/100)*$order_detail['refund_price']);
            if($give_integral >= 1)
            {
                // 用户积分添加
                if(!Db::name('User')->where(['id'=>$user['id']])->setDec('integral', $give_integral))
                {
                    return DataReturn('用户积分释放失败['.$order_detail['order_id'].'-'.$order_detail['goods_id'].']', -10);
                }

                // 积分日志
                self::UserIntegralLogAdd($user['id'], $user['integral'], $give_integral, '订单商品发生售后收回', 0);
            }
        }
    }

    /**
     * 用户积分
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-01
     * @desc    description
     * @param   [int]          $user_id [用户 id]
     */
    public static function UserIntegral($user_id)
    {
        $data = Db::name('User')->where(['id'=>$user_id])->field('integral,locking_integral')->find();
        return DataReturn('success', 0, $data);
    }
}
?>