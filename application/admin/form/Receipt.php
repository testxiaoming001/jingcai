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


/**
 * 文章动态表格
 * @author  Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2020-06-16
 * @desc    description
 */
class Receipt
{
    // 基础条件
    public $condition_base = [];

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
                'key_field'     => 'receipt_id',
                'status_field'  => 'receipt_status',
                'is_search'     => 1,
                'search_url'    => MyUrl('admin/receipt/index'),
                'is_delete'     => 1,
                'delete_url'    => MyUrl('admin/receipt/delete'),
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
                    'label'         => '账户名称',
                    'view_type'     => 'field',
                    'view_key'      => 'receipt_name',
                    'grid_size'     => 'sm',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'receipt_name',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '收款地址',
                    'view_type'     => 'field',
                    'view_key'      => 'receipt_address',
                    'grid_size'     => 'sm',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'receipt_address',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '账户类型',
                    'view_type'     => 'field',
                    'view_key'      => 'receipt_type',
                    'grid_size'     => 'sm',
                    'search_config' => [
                        'form_type'         => 'input',
                        'form_name'         => 'receipt_type',
                        'where_type'        => 'like',
                    ],
                ],
                [
                    'label'         => '是否启用',
                    'view_type'     => 'status',
                    'view_key'      => 'receipt_status',
                    'post_url'      => MyUrl('admin/receipt/statusupdate'),
                    'is_form_su'    => 1,
                    'align'         => 'center',
                    'search_config' => [
                        'form_type'         => 'select',
                        'where_type'        => 'in',
                        'data'              => lang('common_is_enable_list'),
                        'data_key'          => 'receipt_id',
                        'data_name'         => 'name',
                        'is_multiple'       => 1,
                    ],
                ],


                [
                    'label'         => '创建时间',
                    'view_type'     => 'field',
                    'view_key'      => 'createtime',
                    'search_config' => [
                        'form_type'         => 'datetime',
                    ],
                ],

                [
                    'label'         => '操作',
                    'view_type'     => 'operate',
                    'view_key'      => 'receipt/module/operate',
                    'align'         => 'center',
                    'fixed'         => 'right',
                ],
            ],
        ];
    }

}
?>
