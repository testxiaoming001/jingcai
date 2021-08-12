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
use app\service\UserService;


/**
 * tg 广告服务层
 * Class TgAdService
 * @package app\service
 */
class TgAdService
{


    /**
     * 指定时间可以推送的广告
     * @param $time
     *
     */
    public static function getAblePushAdsAtime($time)
    {
        return Db::name('TgAd')
            ->where('is_able',1)
            ->where('push_time','exp','REGEXP \''."{$time}".'\'')
            ->select();
    }

    /**
     * @param array $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function adsList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : trim($params['order_by']);
        $data = Db::name('TgAd')->field($field)->where($where)->order($order_by)->select();
        return DataReturn('处理成功', 0, $data);
    }


    /**
     * @param array $params
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function TgAdSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'ad_content',
                'error_msg'         => '内容不能为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'push_time',
                'error_msg'         => '推送不能为空',
            ],

        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 操作数据
        $data = [
            'ad_content'              => $params['ad_content'],
            'push_time'              => $params['push_time'],
            'is_able'              => input('is_able',0),
        ];


        Db::startTrans();

        // 添加/更新数据
        if(empty($params['id']))
        {
            $data['add_time'] = date('Y-m-d H:i:s');
            if(Db::name('TgAd')->insertGetId($data) > 0)
            {
                Db::commit();
                return DataReturn('新增成功', 0);
            } else {
                Db::rollback();
                return DataReturn('新增失败');
            }
        } else {

            if(Db::name('TgAd')->where(['id'=>intval($params['id'])])->update($data))
            {
                Db::commit();
                return DataReturn('更新成功', 0);
            } else {
                Db::rollback();
                return DataReturn('更新失败');
            }
        }
    }


    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function TgAdStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作id有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => '状态有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据更新
        if(Db::name('TgAd')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state'])]))
        {
            return DataReturn('操作成功');
        }
        return DataReturn('操作失败', -100);
    }

    /**
     * 删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function TgAdDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn('id有误', -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 启动事务
        Db::startTrans();

        // 删除操作
        if(Db::name('TgAd')->where(['id'=>$params['ids']])->delete())
        {


            // 提交事务
            Db::commit();
            return DataReturn('删除成功');
        }

        Db::rollback();
        return DataReturn('删除失败', -100);
    }

}





?>