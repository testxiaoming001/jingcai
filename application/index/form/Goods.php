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
namespace app\index\form;

use app\index\controller\Common;
use think\Db;
use app\service\GoodsService;
use app\service\RegionService;
use app\service\BrandService;

/**
 * 商品动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-05-16
 * @desc    description
 */
class Goods
{
    // 基础条件
    public $condition_base = [
        ['is_delete_time', '=', 0],
    ];

    /**
     * 入口
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-05-16
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Run($params = [])
    {

        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'status_field'  => 'is_shelves',
                'search_url'    => MyUrl('admin/goods/index'),
                'is_delete'     => 0,
                'delete_url'    => MyUrl('admin/goods/delete'),
                'delete_key'    => 'ids',
                'detail_title'  => '基础信息',
            ],
            // 表单配置
            'form' => [
                [
                    'label'         => '商品序列号',
                    'view_type'     => 'module',
                    'view_key'      => 'goods/module/series_number',
                    'width'         => 105,
                ],
                [
                    'label'         => '商品信息',
                    'view_type'     => 'module',
                    'view_key'      => 'goods/module/info',
                    'grid_size'     => 'lg',
                ],
                [
                    'label'         => '商品分类',
                    'view_type'     => 'field',
                    'view_key'      => 'category_text',
                ],
//                [
//                    'label'         => '审核状态',
//                    'view_type'     => 'field',
//                    'view_key'      => 'check_status',
//                ],

                [
                    'label'         => '审核状态',
                    'view_type'     => 'field',
                    'view_key'      => 'check_status',
                    'view_data_key' => 'name',
                    'view_data'     => lang('common_goods_check_status'),
                ],


                [
                    'label'         => '商品可靠度',
                    'view_type'     => 'field',
                    'view_key'      => 'goods_reliability',
                ],
                [
                    'label'         => '供需类型',
                    'view_type'     => 'field',
                    'view_key'      => 'goods_type',
                    'view_data_key' => 'name',
                    'view_data'     => lang('common_goods_type_list'),
                ],
                [
                    'label'         => '同台地区',
                    'view_type'     => 'field',
                    'view_key'      => 'place_origin_name',
                ],
                [
                    'label'         => '访问次数',
                    'view_type'     => 'field',
                    'view_key'      => 'access_count',
                ],
                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                ],
//                [
//                    'label'         => '更新时间',
//                    'view_type'     => 'field',
//                    'view_key'      => 'upd_time',
//                ],
                [
                    'label'         => '操作',
                    'view_type'     => 'operate',
                    'view_key'      => 'goods/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
        ];
    }

    /**
     * 商品分类条件处理
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2020-06-03
     * @desc    description
     * @param   [array]           $value    [条件值]
     * @param   [array]           $params   [输入参数]
     */
    public function WhereValueGoodsCategory($value, $params = [])
    {
        if(!empty($value))
        {
            // 是否为数组
            if(!is_array($value))
            {
                $value = [$value];
            }

            // 获取分类下的所有分类 id
            $cids = GoodsService::GoodsCategoryItemsIds($value, 1);

            // 获取商品 id
            $ids = Db::name('GoodsCategoryJoin')->where(['category_id'=>$cids])->column('goods_id');

            // 避免空条件造成无效的错觉
            return empty($ids) ? [0] : $ids;
        }
        return $value;
    }
}
?>
