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
namespace app\admin\form;

use think\Db;
use app\service\WarehouseService;
use app\service\RegionService;

/**
 * 曝光动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-07-07
 * @desc    description
 */
class Exposurepay
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
     * @date    2020-06-16
     * @desc    description
     * @param   [array]           $params [输入参数]
     */
    public function Run($params = [])
    {
        return [
            // 基础配置
            'base' => [
                'key_field'     => 'id',
                'status_field'  => 'is_enable',
                'is_search'     => 1,
                'search_url'    => MyUrl('admin/exposure/index'),
                'is_delete'     => 1,
                'delete_url'    => MyUrl('admin/exposure/delete'),
                'delete_key'    => 'ids',
                'detail_title'  => '基础信息',
            ],
            // 表单配置
            'form' => [
                [
                    'view_type'         => 'checkbox',
                    'is_checked'        => 0,
                    'checked_text'      => '反选',
                    'not_checked_text'  => '全选',
                    'align'             => 'center',
                    'width'             => 80,
                ],

                [
                    'label'         => '名称',
                    'view_type'     => 'field',
                    'view_key'      => 'exposurepay_name',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'exposurepay_name',
                        'where_type'        => 'like',
                    ],
                ],

                [
                    'label'         => '网关',
                    'view_type'     => 'field',
                    'view_key'      => 'exposurepay_gateway',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'exposurepay_gateway',
                        'where_type'        => 'like',
                    ],
                ],





                [
                    'label'         => '投诉者id',
                    'view_type'     => 'field',
                    'view_key'      => 'complaint_uid',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'complaint_uid',
                    ],
                ],

                [
                    'label'         => '投诉者账号',
                    'view_type'     => 'field',
                    'view_key'      => 'complaint_username',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'complaint_username',
                        'where_type'        => 'like',
                    ],
                ],


                [
                    'label'         => '投诉原因',
                    'view_type'     => 'field',
                    'view_key'      => 'complaint_reason',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'complaint_reason',
                        'where_type'        => 'like',
                    ],
                ],



                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'add_time',
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => '更新时间',
                    'view_type'     => 'field',
                    'view_key'      => 'upd_time',
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],
                [
                    'label'         => '操作',
                    'view_type'     => 'operate',
                    'view_key'      => 'exposurepay/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
        ];
    }


}
?>
