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
use think\facade\Hook;
use app\service\ResourcesService;
use think\Request;

/**
 * 文章服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class TgbannerService
{
    /**
     * 获取文章列表
     * @param   [array]          $params [输入参数]
     * @version 1.0.0
     * @date    2018-08-29
     * @desc    description
     * @author   Devil
     * @blog    http://gong.gg/
     */
    public static function TgbannerList($params)
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id asc' : trim($params['order_by']);
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        $data = Db::name('tg_banner')->field($field)->where($where)->order($order_by)->limit($m, $n)->select();
        if (!empty($data)) {
            foreach ($data as &$v) {
                // 时间
                if (isset($v['add_time'])) {
                    $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                if (isset($v['expire_time'])) {
                    $v['expire_time'] = empty($v['expire_time']) ? '' : date('Y-m-d H:i:s', $v['expire_time']);
                }
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 文章总数
     * @param    [array]          $where [条件]
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @author   Devil
     * @blog     http://gong.gg/
     */
    public static function TgbannerTotal($where)
    {
        return (int)Db::name('tg_banner')->where($where)->count();
    }

    /**
     * 文章保存
     * @param   [array]          $params [输入参数]
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @author   Devil
     * @blog    http://gong.gg/
     */
    public static function TgbannerSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type' => 'length',
                'key_name' => 'contents',
                'checked_data' => '10,105000',
                'error_msg' => '内容 10~105000 个字符',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if ($ret !== true) {
            return DataReturn($ret, -1);
        }

        // 编辑器内容
        $data = [
            'contents' => $params['contents'],
            'expire_time' => strtotime($params['expire_time'])
        ];

        if (empty($params['id'])) {
            $data['add_time'] = time();

            if (Db::name('tg_banner')->insertGetId($data) > 0) {
                return DataReturn('添加成功', 0);
            }
            return DataReturn('添加失败', -100);
        } else {
            if (Db::name('tg_banner')->where(['id' => intval($params['id'])])->update($data)) {
                return DataReturn('编辑成功', 0);
            }
            return DataReturn('编辑失败', -100);
        }
    }

    /**
     * 获取分类和所有文章
     * @param   [array]          $params [输入参数]
     * @version 1.0.0
     * @date    2018-10-19
     * @desc    description
     * @author   Devil
     * @blog    http://gong.gg/
     */
    public static function ArticleCategoryListContent($params = [])
    {
        $data = Db::name('ArticleCategory')->field('id,name')->where(['is_enable' => 1])->order('id asc, sort asc')->select();
        if (!empty($data)) {
            foreach ($data as &$v) {
                $items = Db::name('Article')->field('id,title,title_color')->where(['article_category_id' => $v['id'], 'is_enable' => 1])->select();
                if (!empty($items)) {
                    foreach ($items as &$vs) {
                        // url
                        $vs['url'] = MyUrl('index/article/index', ['id' => $vs['id']]);
                    }
                }
                $v['items'] = $items;
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 文章访问统计加1
     * @param   [array]          $params [输入参数]
     * @version 1.0.0
     * @date    2018-10-15
     * @desc    description
     * @author   Devil
     * @blog    http://gong.gg/
     */
    public static function ArticleAccessCountInc($params = [])
    {
        if (!empty($params['id'])) {
            return Db::name('Article')->where(array('id' => intval($params['id'])))->setInc('access_count');
        }
        return false;
    }

    /**
     * 文章分类
     * @param   [array]          $params [输入参数]
     * @version 1.0.0
     * @date    2018-08-29
     * @desc    description
     * @author   Devil
     * @blog    http://gong.gg/
     */
    public static function ArticleCategoryList($params = [])
    {
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'sort asc' : trim($params['order_by']);

        $data = Db::name('ArticleCategory')->where(['is_enable' => 1])->field($field)->order($order_by)->select();

        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 删除
     * @param   [array]          $params [输入参数]
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @author   Devil
     * @blog    http://gong.gg/
     */
    public static function TgbannerDelete($params = [])
    {
        // 参数是否有误
        if (empty($params['ids'])) {
            return DataReturn('商品id有误', -1);
        }
        // 是否数组
        if (!is_array($params['ids'])) {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 删除操作
        if (Db::name('tg_banner')->where(['id' => $params['ids']])->delete()) {
            return DataReturn('删除成功');
        }

        return DataReturn('删除失败', -100);
    }

    /**
     * 状态更新
     * @param    [array]          $params [输入参数]
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @author   Devil
     * @blog     http://gong.gg/
     */
    public static function ArticleStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type' => 'empty',
                'key_name' => 'id',
                'error_msg' => '操作id有误',
            ],
            [
                'checked_type' => 'empty',
                'key_name' => 'field',
                'error_msg' => '操作字段有误',
            ],
            [
                'checked_type' => 'in',
                'key_name' => 'state',
                'checked_data' => [0, 1],
                'error_msg' => '状态有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if ($ret !== true) {
            return DataReturn($ret, -1);
        }

        // 数据更新
        if (Db::name('Article')->where(['id' => intval($params['id'])])->update([$params['field'] => intval($params['state']), 'upd_time' => time()])) {
            return DataReturn('编辑成功');
        }
        return DataReturn('编辑失败', -100);
    }

    /**
     * 获取文章分类节点数据
     * @param    [array]          $params [输入参数]
     * @version  1.0.0
     * @datetime 2018-12-16T23:54:46+0800
     * @author   Devil
     * @blog     http://gong.gg/
     */
    public static function ArticleCategoryNodeSon($params = [])
    {
        // id
        $id = isset($params['id']) ? intval($params['id']) : 0;

        // 获取数据
        $field = '*';
        $data = Db::name('ArticleCategory')->field($field)->where(['pid' => $id])->order('sort asc')->select();
        if (!empty($data)) {
            foreach ($data as &$v) {
                $v['is_son'] = (Db::name('ArticleCategory')->where(['pid' => $v['id']])->count() > 0) ? 'ok' : 'no';
                $v['ajax_url'] = MyUrl('admin/articlecategory/getnodeson', array('id' => $v['id']));
                $v['delete_url'] = MyUrl('admin/articlecategory/delete');
                $v['json'] = json_encode($v);
            }
            return DataReturn('操作成功', 0, $data);
        }
        return DataReturn('没有相关数据', -100);
    }

    /**
     * 文章分类保存
     * @param    [array]          $params [输入参数]
     * @version  1.0.0
     * @datetime 2018-12-17T01:04:03+0800
     * @author   Devil
     * @blog     http://gong.gg/
     */
    public static function ArticleCategorySave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type' => 'length',
                'key_name' => 'name',
                'checked_data' => '2,16',
                'error_msg' => '名称格式 2~16 个字符',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if ($ret !== true) {
            return DataReturn($ret, -1);
        }

        // 数据
        $data = [
            'name' => $params['name'],
            'pid' => isset($params['pid']) ? intval($params['pid']) : 0,
            'is_news_cate' => isset($params['is_news_cate']) ? intval($params['is_news_cate']) : 0,
            'sort' => isset($params['sort']) ? intval($params['sort']) : 0,
            'is_enable' => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
        ];

        // 添加
        if (empty($params['id'])) {
            $data['add_time'] = time();
            if (Db::name('ArticleCategory')->insertGetId($data) > 0) {
                return DataReturn('添加成功', 0);
            }
            return DataReturn('添加失败', -100);
        } else {
            $data['upd_time'] = time();
            if (Db::name('ArticleCategory')->where(['id' => intval($params['id'])])->update($data)) {
                return DataReturn('编辑成功', 0);
            }
            return DataReturn('编辑失败', -100);
        }
    }

    /**
     * 文章分类删除
     * @param    [array]          $params [输入参数]
     * @version  1.0.0
     * @datetime 2018-12-17T02:40:29+0800
     * @author   Devil
     * @blog     http://gong.gg/
     */
    public static function ArticleCategoryDelete($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type' => 'empty',
                'key_name' => 'id',
                'error_msg' => '删除数据id有误',
            ],
            [
                'checked_type' => 'empty',
                'key_name' => 'admin',
                'error_msg' => '用户信息有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if ($ret !== true) {
            return DataReturn($ret, -1);
        }

        // 开始删除
        if (Db::name('ArticleCategory')->where(['id' => intval($params['id'])])->delete()) {
            return DataReturn('删除成功', 0);
        }
        return DataReturn('删除失败', -100);
    }


    /**
     * 获取某个文章分类下面的新闻  is_news_cate = 1
     * @param $cateId
     */
    public static function getNewsByCateId($cateId, $limit = 2)
    {
        return Db::name('Article')->where(['article_category_id' => $cateId])->paginate($limit, false, [
            'query' => input()]);
    }

    public static function getArticleCateById($id)
    {
        return Db::name('ArticleCategory')->where(['id' => $id])->find();
    }
}

?>
