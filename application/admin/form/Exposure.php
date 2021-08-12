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
class Exposure
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
                    'view_key'      => 'exposure_name',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'exposure_name',
                        'where_type'        => 'like',
                    ],
                ],

                [
                    'label'         => '网关',
                    'view_type'     => 'field',
                    'view_key'      => 'exposure_gateway',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'exposure_gateway',
                        'where_type'        => 'like',
                    ],
                ],

                [
                    'label'         => '信誉积分',
                    'view_type'     => 'field',
                    'view_key'      => 'integral',
                    'search_config' => [
                        'form_type'         => 'section',
                    ],
                ],


                [
                    'label'         => '被投诉者id',
                    'view_type'     => 'field',
                    'view_key'      => 'tg_uid',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'tg_uid',
                    ],
                ],

                [
                    'label'         => '被投诉者账号',
                    'view_type'     => 'field',
                    'view_key'      => 'tg_user',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'tg_user',
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
                    'view_data_key' => 'name',
                    'view_data'     => lang('common_exposure_reason_list'),
                    'is_form_su'    => 1,
                    'align'         => 'center',
                    'search_config' => [
                        'form_type'         => 'select',
                        'where_type'        => 'in',
                        'data'              => lang('common_exposure_reason_list'),
                        'data_key'          => 'value',
                        'data_name'         => 'name',
                        'is_multiple'       => 0,
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
                    'view_key'      => 'exposure/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
        ];
    }


}
?>
