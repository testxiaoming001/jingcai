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

use app\service\ArticleService;
use app\service\SeoService;

/**
 * tg中心
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Tg extends Common
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


    /***
     * 投诉tg view
     * @return mixed
     */
    public function doComplaint(\app\service\Tg $tgService)
    {
        if ($this->request->isPost()) {
            $params = $this->request->param('');
            return $tgService->complaint($params);
        }

        return $this->fetch();
    }

    /**
     * @return mixed|\think\response\Redirect
     */
    public function complaints(\app\service\Tg $tgServic)
    {
        $where = [];
        $tgUsename = input('tg_username', '');
        $tgUsename && $where['tg_username'] = ['like', '%' . $tgUsename . '%'];
        $data = $tgServic->complaints($where);
        $this->assign('complaints', $data);
        $this->assign('pages', $data->render());
        return $this->fetch('index');
    }

    public function complaintsList(\app\service\Tg $tgServic)
    {
        $where = [];
        $tgUsename = input('tg_username', '');
        $tgUsename && $where['tg_username'] = ['like', '%' . $tgUsename . '%'];
        $data = $tgServic->complaints($where);
        $this->assign('complaints', $data);
        $this->assign('pages', $data->render());
        return $this->fetch('content');
    }


    /**
     * 文章列表
     */
    public function news()
    {
        $cateId = input('cate_id', 0);
        $cate = ArticleService::getArticleCateById($cateId);

        if (empty($cate)) {
            $this->assign('msg', '文章分类ID有误');
            return $this->fetch('public/tips_error');
        }

        if ($cate['is_news_cate'] == false) {
            $this->assign('msg', '当前文章分类不属于新闻专题');
            return $this->fetch('public/tips_error');
        }
        $news = ArticleService::getNewsByCateId([
            'pid' => $cateId
        ]);
        $this->assign('articles', $news);
        $this->assign('pages', $news->render());
        return $this->fetch();
    }
}

?>
