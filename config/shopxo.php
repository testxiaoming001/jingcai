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

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 开发模式
    'is_develop' => false,

    // 默认编码
    'default_charset' => 'utf-8',

    // 缓存key列表
    // 公共系统配置信息key
    'cache_common_my_config_key' => 'cache_common_my_config_data',

    // 前台顶部导航，后端菜单更新则删除缓存
    'cache_common_home_nav_header_key' => 'cache_common_home_nav_header_data',

    // 前台顶部导航
    'cache_common_home_nav_footer_key' => 'cache_common_home_nav_footer_data',

    // 商品大分类缓存
    'cache_goods_category_key' => 'cache_goods_category_key_data',

    // 应用数据缓存
    'cache_plugins_data_key' => 'cache_plugins_data_key_data_',

    // 用户登录左侧数据
    'cache_user_login_left_key' => 'cache_user_login_left_data',

    // 用户密码找回左侧数据
    'cache_user_forgetpwd_left_key' => 'cache_user_forgetpwd_left_data',

    // 配置信息一条缓存 拼接唯一标记 [ only_tag ]
    'cache_config_row_key' => 'cache_config_row_data_',

    // 用户缓存信息
    'cache_user_info' => 'cache_user_info_',

    // 首页楼层缓存信息
    'cache_goods_floor_list_key' => 'cache_goods_floor_list_data',

    // 轮播缓存信息
    'cache_banner_list_key' => 'cache_banner_list_data_',

    // 导航缓存信息
    'cache_navigation_key' => 'cache_navigation_data_',

    // 附件host、最后不要带/斜杠结尾, 数据库图片地址以/static/...开头
    'attachment_host' => defined('__MY_PUBLIC_URL__') ? substr(__MY_PUBLIC_URL__, 0, -1) : '',

    // 应用商店地址
    'store_url' => 'https://store.shopxo.net/',
    'store_payment_url' => 'https://store.shopxo.net/payment.html',
    'store_theme_url' => 'https://store.shopxo.net/theme.html',

    // 开启U带域名
    'url_domain_deploy' => true,

    // 支付业务类型,支付插件根据业务类型自动生成支付入口文件
    'payment_business_type_all' => [
        ['name' => 'Order', 'desc' => '订单'],
    ],

    // 不删除的支付方式
    'payment_cannot_deleted_list' => [
        'DeliveryPayment',
        'CashPayment',
    ],

    // 线下支付方式
    'under_line_list' => ['CashPayment', 'DeliveryPayment'],

    // 小程序平台
    'mini_app_type_list' => ['weixin', 'alipay', 'baidu', 'toutiao', 'qq'],

    // 坐标需要转换的平台
    'coordinate_transformation' => ['alipay', 'weixin', 'toutiao', 'baidu'],

    // 价格符号
    'price_symbol' => '￥',

    // 验证码最大验证次数,防止暴力破解
    'security_prevent_violence_max' => 6,


    // 动态表格可加入钩子组
    'module_form_hook_group' => ['admin', 'index', 'api'],
    //水印文本
    'water_text' => '97danbao',
    'ip_register_time' => 2,
    'regitser_send_usdt' => 10,
    'speak_send_usdt' => 100,

    'is_open_pay_game' => 1,
    //tg相关配置
    'new_member_add' => [
        'text' => '欢迎交流,群规如下
 1:禁止发广告
 2：禁止发黄图
 3:禁止发链接特别是群组信息',
        'inline_keyboard' => [
            [
                ['text' => '骗子曝光台', 'callback_data' => 'someString', 'url' => 'http://www.baidu.com'],
            ],
            [
                ['text' => '供应信息', 'callback_data' => 'someString'],
                ['text' => '需求信息', 'callback_data' => 'someStrinxxxxxxxxxxxxxg']
            ],
            [
                ['text' => '常见问题-解决办法', 'callback_data' => 'someString'],
            ],
        ]
    ],
    //任务自动关闭时间
    'task_close_min' => 30,

    'task_pay_usdt' => [
        'url' => 'TEBjtGY2DCx9Qwv1hmg64ZKLPgLTadkoh5',
        'link_type' => "trc20"
    ],
    //邀请人派发佣金
    'user_invite_commission' => 50,
    //被邀请人派发佣金
    'user_invited_commission' => 100,
    'game_start' => true,
    'tronscan_network' => 'testNet', //mainNet 主网络  testNet测试网路
    'contract_decimals' => 18,
    'contract_address'=>'TVYVKhhmN4Kem6iM8vZF4GVzpUjVieyRQi'

];
?>
