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
use app\service\RegionService;
use app\service\WarehouseGoodsService;

/**
 * 曝光服务层
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-07-07
 * @desc    description
 */
class ExposureService
{
    /**
     * 数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params  [输入参数]
     */
    public static function ExposureList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : trim($params['order_by']);
        $data = Db::name('Exposure')->field($field)->where($where)->order($order_by)->select();
        return DataReturn('处理成功', 0, self::DataHandle($data));
    }

    /**
     * 数据处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-18
     * @desc    description
     * @param   [array]          $data [仓库数据]
     */
    public static function DataHandle($data)
    {
        if(!empty($data))
        {
            // 字段列表
            $keys = ArrayKeys($data);



            // 循环处理数据
            foreach($data as &$v)
            {


                // 时间
                if(isset($v['add_time']))
                {
                    $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                if(isset($v['upd_time']))
                {
                    $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
                }
            }
        }
        return $data;
    }

    /**
     * 保存
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-07-07
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function ExposureSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'exposure_name',
                'error_msg'         => '名称不能为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'exposure_gateway',
                'error_msg'         => '网关不能为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'integral',
                'error_msg'         => '信誉积分不能为空',
            ],

            [
                'checked_type'      => 'empty',
                'key_name'          => 'tg_uid',
                'error_msg'         => '被投诉者id',
            ],

            [
                'checked_type'      => 'empty',
                'key_name'          => 'tg_user',
                'error_msg'         => '被投诉者账号',
            ],

            [
                'checked_type'      => 'empty',
                'key_name'          => 'complaint_uid',
                'error_msg'         => '投诉者id',
            ],

            [
                'checked_type'      => 'empty',
                'key_name'          => 'complaint_username',
                'error_msg'         => '投诉者账号',
            ],

            [
                'checked_type'      => 'empty',
                'key_name'          => 'complaint_reason',
                'error_msg'         => '投诉原因',
            ],

        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 操作数据
        $is_default = isset($params['is_default']) ? intval($params['is_default']) : 0;
        $data = [
            'exposure_name'              => $params['exposure_name'],
            'exposure_gateway'              => $params['exposure_gateway'],
            'integral'              => $params['integral'],
        ];

        Db::startTrans();



        // 添加/更新数据
        if(empty($params['id']))
        {
            $data['add_time'] = time();
            if(Db::name('Exposure')->insertGetId($data) > 0)
            {
                Db::commit();
                return DataReturn('新增成功', 0);
            } else {
                Db::rollback();
                return DataReturn('新增失败');
            }
        } else {
            $data['upd_time'] = time();
            if(Db::name('Exposure')->where(['id'=>intval($params['id'])])->update($data))
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
     * 删除
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function ExposureDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn('商品id有误', -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 启动事务
        Db::startTrans();

        // 删除操作
        if(Db::name('Exposure')->where(['id'=>$params['ids']])->update(['is_delete_time'=>time(), 'upd_time'=>time()]))
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
