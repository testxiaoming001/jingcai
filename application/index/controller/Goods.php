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
namespace app\index\controller;

use app\service\BrandService;
use app\service\RegionService;
use think\Collection;
use think\Db;
use think\facade\Hook;
use app\service\GoodsService;
use app\service\GoodsCommentsService;
use app\service\GoodsBrowseService;
use app\service\GoodsFavorService;
use app\service\SeoService;

/**
 * 商品详情
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Goods extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 详情
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-12-02T23:42:49+0800
     */
    public function Index()
    {

        $goods_id = isset($this->data_request['id']) ? $this->data_request['id'] : 0;
        $params   = [
            'where' => [
                'id' => $goods_id,
                'is_delete_time' => 0,
            ],
            'is_photo' => true,
            'is_spec' => true,
        ];
        $ret      = GoodsService::GoodsList($params);
        if (empty($ret['data'][0]) || $ret['data'][0]['is_delete_time'] != 0) {
            $this->assign('msg', '资源不存在或已被删除');
            return $this->fetch('/public/tips_error');
        } else {
            // 商品信息
            $goods = $ret['data'][0];

            //顶级分类
            $catesIds = array_keys(GoodsService::getGoodsCateGory($goods['id']));
            $topCate = GoodsService::getTopParentBychildsId(end($catesIds));
            $goods['top_cate_id'] = $topCate['id'];

            // 当前登录用户是否已收藏
            $ret_favor         = GoodsFavorService::IsUserGoodsFavor(['goods_id' => $goods_id, 'user' => $this->user]);
            $goods['is_favor'] = ($ret_favor['code'] == 0) ? $ret_favor['data'] : 0;

            // 商品评价总数
            $goods['comments_count'] = GoodsCommentsService::GoodsCommentsTotal(['goods_id' => $goods_id, 'is_show' => 1]);

            // 商品收藏总数
            $goods['favor_count'] = GoodsFavorService::GoodsFavorTotal(['goods_id' => $goods_id]);

            //供需类型
            $goods['goods_type_name']  = $goods['goods_type'] ? lang('common_goods_type_list')[$goods['goods_type']]['name'] : '';
            $goods['goods_type_color'] = $goods['goods_type'] ? lang('common_goods_type_list')[$goods['goods_type']]['color'] : '';

            //担保方式
            $assure_list  = Db::name('GoodsAssureStyle')->where(['goods_id' => $goods_id])->select();
            $assure_array = [];
            foreach ($assure_list as $k => $v) {
                $str = lang('common_assure_style_list')[$v['assure_style_id']]['name'];
                if (lang('common_assure_style_list')[$v['assure_style_id']]['tg']) {
                    $str .= "<a target=\"_blank\" href='https://t.me/" . lang('common_assure_style_list')[$v['assure_style_id']]['tg'] . " '>（@" . lang('common_assure_style_list')[$v['assure_style_id']]['tg'] . "）</a>";
                }
                $assure_array[] = $str;
            }
            $goods['assure_type_name'] = implode(',', $assure_array);
//            var_dump($goods['assure_type_name']);die();
            //同台地区
            $goods['place_origin_name'] = $goods['place_area'];

            // 钩子
            $this->PluginsHook($goods_id, $goods);

            // 商品数据
            $this->assign('goods', $goods);

            // seo
            $seo_title = empty($goods['seo_title']) ? $goods['title'] : $goods['seo_title'];
            $this->assign('home_seo_site_title', SeoService::BrowserSeoTitle($seo_title, 2));
            if (!empty($goods['seo_keywords'])) {
                $this->assign('home_seo_site_keywords', $goods['seo_keywords']);
            }
            if (!empty($goods['seo_desc'])) {
                $this->assign('home_seo_site_description', $goods['seo_desc']);
            }

            // 二维码
            $qrcode     = GoodsService::GoodsQrcode($goods_id, $goods['add_time']);
            $qrcode_url = ($qrcode['code'] == 0 && isset($qrcode['data']['url'])) ? $qrcode['data']['url'] : '';
            $this->assign('qrcode_url', $qrcode_url);

            // 商品评分
            $goods_score = GoodsCommentsService::GoodsCommentsScore($goods_id);
            $this->assign('goods_score', $goods_score['data']);

            // 商品访问统计
            GoodsService::GoodsAccessCountInc(['goods_id' => $goods_id]);

            // 用户商品浏览
            GoodsBrowseService::GoodsBrowseSave(['goods_id' => $goods_id, 'user' => $this->user]);

            // 左侧商品 看了又看

            //获取商品类型
            $category  = Db::name('GoodsCategoryJoin')->where(['goods_id' => $goods_id])->select();
            $goods_ids = [];
            if ($category) {
                $category_ids = array_column($category, 'category_id');
                if ($category_ids) {
                    $goodsList = Db::name('GoodsCategoryJoin')->group('goods_id')->where('category_id', 'in', $category_ids)->select();
                    if ($goodsList) {
                        $goods_ids = array_column($goodsList, 'goods_id');
                        $goods_ids = array_diff($goods_ids, [$goods_id]);
                    }
                }
            }


            $params = [
                'where' => [
                    'is_delete_time' => 0,
                    'is_shelves' => 1
                ],
                'order_by' => 'access_count desc',
                'field' => 'id,title,title_color,price,images,deposit,user_telegram,goods_type',
                'n' => 10,
            ];


            if ($goods_ids) {
                $params['where']['id'] = [implode(',', $goods_ids)];
            }
            $right_goods = GoodsService::GoodsList($params, true);
            $this->assign('left_goods', $right_goods['data']);

            // 详情tab商品 猜你喜欢
            $params     = [
                'where' => [
                    'is_delete_time' => 0,
                    'is_shelves' => 1,
                    'is_home_recommended' => 1,
                ],
                'order_by' => 'sales_count desc',
                'field' => 'id,title,title_color,price,images',
                'n' => 16,
            ];
            $like_goods = GoodsService::GoodsList($params);
            $this->assign('detail_like_goods', $like_goods['data']);

            // 站点类型 - 展示型模式操作名称
            $this->assign('common_is_exhibition_mode_btn_text', MyC('common_is_exhibition_mode_btn_text', '立即咨询', true));

            // 是否商品详情页展示相册
            $this->assign('common_is_goods_detail_show_photo', MyC('common_is_goods_detail_show_photo', 0, true));

            // 商品销售模式
            $ret = GoodsService::GoodsSalesModelType($goods_id, $goods['site_type']);
            $this->assign('common_site_type', $ret['data']);

            // 商品类型是否一致
            $ret = GoodsService::IsGoodsSiteTypeConsistent($goods_id, $goods['site_type']);
            $this->assign('is_goods_site_type_consistent', ($ret['code'] == 0) ? 1 : 0);

            return $this->fetch();
        }
    }

    /**
     * 钩子处理
     * @param   [int]             $goods_id [商品id]
     * @param   [array]           $params   [输入参数]
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     */
    private function PluginsHook($goods_id, &$goods)
    {
        // 商品页面相册内部钩子
        $hook_name = 'plugins_view_goods_detail_photo_within';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面相册底部钩子
        $hook_name = 'plugins_view_goods_detail_photo_bottom';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面基础信息顶部钩子
        $hook_name = 'plugins_view_goods_detail_base_top';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面基础信息面板底部钩子
        $hook_name = 'plugins_view_goods_detail_panel_bottom';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面基础信息面板底部钩子
        $hook_name = 'plugins_view_goods_detail_base_bottom';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面tabs顶部钩子
        $hook_name = 'plugins_view_goods_detail_tabs_top';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面tabs顶部钩子
        $hook_name = 'plugins_view_goods_detail_tabs_bottom';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面左侧顶部钩子
        $hook_name = 'plugins_view_goods_detail_left_top';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面基础信息标题里面钩子
        $hook_name = 'plugins_view_goods_detail_title';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面基础信息面板售价顶部钩子
        $hook_name = 'plugins_view_goods_detail_panel_price_top';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));

        // 商品页面基础信息购买小导航里面钩子
        $hook_name = 'plugins_view_goods_detail_base_buy_nav_min_inside';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => false,
                'goods_id' => $goods_id,
                'goods' => &$goods,
            ]));
    }

    /**
     * 商品收藏
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-13
     * @desc    description
     */
    public function Favor()
    {
        // 是否ajax请求
        if (!IS_AJAX) {
            return $this->error('非法访问');
        }

        // 是否登录
        $this->IsLogin();

        // 开始处理
        $params         = input('post.');
        $params['user'] = $this->user;
        return GoodsFavorService::GoodsFavorCancel($params);
    }

    /**
     * 商品规格类型
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-14
     * @desc    description
     */
    public function SpecType()
    {
        // 是否ajax请求
        if (!IS_AJAX) {
            return $this->error('非法访问');
        }

        // 开始处理
        $params = input('post.');
        return GoodsService::GoodsSpecType($params);
    }

    /**
     * 商品规格信息
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-14
     * @desc    description
     */
    public function SpecDetail()
    {
        // 是否ajax请求
        if (!IS_AJAX) {
            return $this->error('非法访问');
        }

        // 开始处理
        $params = input('post.');
        return GoodsService::GoodsSpecDetail($params);
    }

    /**
     * 商品评论
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-05-13T21:47:41+0800
     */
    public function Comments()
    {
        // 是否ajax请求
        if (!IS_AJAX) {
            return $this->error('非法访问');
        }

        // 参数
        $params = input();
        if (empty($params['goods_id'])) {
            return DataReturn('参数有误', -1);
        }

        // 分页
        $number = 10;
        $page   = max(1, isset($params['page']) ? intval($params['page']) : 1);

        // 条件
        $where = [
            'goods_id' => $params['goods_id'],
            'is_show' => 1,
        ];

        // 获取总数
        $total      = GoodsCommentsService::GoodsCommentsTotal($where);
        $page_total = ceil($total / $number);
        $start      = intval(($page - 1) * $number);

        // 获取列表
        $data_params = array(
            'm' => $start,
            'n' => $number,
            'where' => $where,
            'is_public' => 1,
        );
        $data        = GoodsCommentsService::GoodsCommentsList($data_params);

        // 返回数据
        $result = [
            'number' => $number,
            'total' => $total,
            'page_total' => $page_total,
            'data' => $this->fetch(null, ['data' => $data['data']]),
        ];
        return DataReturn('请求成功', 0, $result);
    }


    /**
     * 添加商品
     */

    public function publish()
    {
        // 参数
        $params = $this->data_request;

        // 商品信息
        $data = [];
        if (!empty($params['id'])) {
            // 条件
            $where = [
                ['is_delete_time', '=', 0],
                ['id', '=', intval($params['id'])],
            ];

            // 获取数据
            $data_params = [
                'where' => $where,
                'm' => 0,
                'n' => 1,
                'is_photo' => 1,
                'is_content_app' => 1,
                'is_category' => 1,
            ];
            $ret         = GoodsService::GoodsList($data_params);
            if (empty($ret['data'][0])) {
                return $this->error('商品信息不存在', MyUrl('admin/goods/index'));
            }
            $data = $ret['data'][0];
            if ($data) {
                //获取担保信息
                $assure_style         = Db::name('GoodsAssureStyle')->where(['goods_id' => $data['id']])->select();
                $data['assure_style'] = array_column($assure_style, 'assure_style_id');
            }
            // 获取商品编辑规格
            $specifications = GoodsService::GoodsEditSpecifications($ret['data'][0]['id']);
            $this->assign('specifications', $specifications);
        }

        // 地区信息
        $this->assign('region_province_list', RegionService::RegionItems(['pid' => 0]));

        // 商品分类
        $this->assign('goods_category_list', GoodsService::GoodsCategoryAll());

        // 品牌分类
        $this->assign('brand_list', BrandService::CategoryBrand());

        // 规格扩展数据
        $goods_spec_extends = GoodsService::GoodsSpecificationsExtends($params);
        $this->assign('goods_specifications_extends', $goods_spec_extends['data']);

        // 站点类型
        $this->assign('common_site_type_list', lang('common_site_type_list'));

        //担保方式
        $this->assign('assure_list', lang('common_assure_style_list'));

        //商品类型
        $this->assign('goods_type_list', lang('common_goods_type_list'));

        // 当前系统设置的站点类型
        $this->assign('common_site_type', MyC('common_site_type', 0, true));

        // 是否拷贝
        $this->assign('is_copy', (isset($params['is_copy']) && $params['is_copy'] == 1) ? 1 : 0);

        // 商品编辑页面钩子
        $hook_name = 'plugins_view_admin_goods_save';
        $this->assign($hook_name . '_data', Hook::listen($hook_name,
            [
                'hook_name' => $hook_name,
                'is_backend' => true,
                'goods_id' => isset($params['id']) ? $params['id'] : 0,
                'data' => &$data,
                'params' => &$params,
            ]));

        // 编辑器文件存放地址
        $this->assign('editor_path_type', 'goods');

        // 数据
        unset($params['id'], $params['is_copy']);
        $this->assign('data', $data);
        $this->assign('params', $params);
        return $this->fetch();
    }


    /**
     * [Save 商品添加/编辑]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-14T21:37:02+0800
     */
    public function Save()
    {
        // 是否ajax
        if (!IS_AJAX) {
            return $this->error('非法访问');
        }

        // 开始操作
        $params         = $this->data_post;
        $params['user'] = $this->user;
        //同步用户等级到产品等级
        syncUserReliabilityToGoodsReliability($this->user['id']);
        return GoodsService::GoodsSave($params);

    }


    /**
     * 我的产品
     */
    public function myGoods()
    {
        $where['uid'] = $this->user['id'];
        // 总数
        $total = GoodsService::GoodsTotal($where);
        // 分页
        $page_params = [
            'number' => $this->page_size,
            'total' => $total,
            'where' => $this->data_request,
            'page' => $this->page,
            'url' => MyUrl('index/goods/myGoods'),
        ];
        $page        = new \base\Page($page_params);

        // 获取数据列表
        $data_params = [
            'where' => $where,
            'm' => $page->GetPageStarNumber(),
            'n' => $this->page_size,
            'is_category' => 1,
        ];
        $ret         = GoodsService::GoodsList($data_params);

        // 基础参数赋值
        $this->assign('params', $this->data_request);
        $this->assign('page_html', $page->GetPageHtml());
        $this->assign('data_list', $ret['data']);
        return $this->fetch();
    }


    /**
     * [Delete 商品删除]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Delete()
    {
        // 是否ajax
        if (!IS_AJAX) {
            return $this->error('非法访问');
        }

        // 开始操作
        $params    = $this->data_post;
        $user      = $this->user;
        $deleteIds = explode(',', $params['ids']);;
        foreach ($deleteIds as $goodId) {
            $goods = GoodsService::getGoodsInfoByid($goodId);
            if ($user['id'] != $goods['uid']) {
                return $this->error('非法访问');
            }
        }
        $params['admin'] = $this->user;
        return GoodsService::GoodsDelete($params);
    }

}

?>
