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

use app\service\RegionService;
use think\Db;
use think\facade\Hook;
use app\service\GoodsService;
use app\service\GoodsCommentsService;
use app\service\GoodsBrowseService;
use app\service\GoodsFavorService;
use app\service\SeoService;


class Tools extends Common
{

    /**
     * 批量设置level层级关系
     *
     */
    public function batchSetGoodsCategorylevel()
    {
        $data = Db::name('GoodsCategory')->select();
        $data = getCategory($data,0,1);
        foreach ( $data as $item) {
            Db::name('GoodsCategory')->where('id','=',$item['id'])
                ->setField('level',$item['level']);
        }
    }
}
?>
