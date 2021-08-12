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

/**
 * 收款账户服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class ReceiptService
{
    /**
     * 获取账户列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-08-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function ReceiptList($params)
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'receipt_id desc' : trim($params['order_by']);
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        $data = Db::name('Receipt')->field($field)->where($where)->order($order_by)->limit($m, $n)->select();
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                // 时间
                if(isset($v['createtime']))
                {
                    $v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
                }
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 文章总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @param    [array]          $where [条件]
     */
    public static function ReceiptTotal($where)
    {
        return (int) Db::name('Receipt')->where($where)->count();
    }

    /**
     * 收款账户保存
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-18
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function ReceiptSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'receipt_name',
                'checked_data'      => '2,60',
                'error_msg'         => '账户名称长度 2~60 个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'receipt_address',
                'checked_data'      => '2,60',
                'error_msg'         => '收款地址长度 2~60 个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'receipt_type',
                'checked_data'      => '2,60',
                'error_msg'         => '账户类型长度 2~60 个字符',
            ],




        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据

        $data = [
            'receipt_name'                 => $params['receipt_name'],
            'receipt_address'                 => $params['receipt_address'],
            'receipt_type'                 => $params['receipt_type'],
            'receipt_status'             => isset($params['receipt_status']) ? intval($params['receipt_status']) : 0,
        ];
        if(empty($params['receipt_id']))
        {
            $data['createtime'] = time();
            if(Db::name('Receipt')->insertGetId($data) > 0)
            {
                return DataReturn('添加成功', 0);
            }
            return DataReturn('添加失败', -100);
        } else {
            if(Db::name('Receipt')->where(['receipt_id'=>intval($params['receipt_id'])])->update($data))
            {
                return DataReturn('编辑成功', 0);
            }
            return DataReturn('编辑失败', -100);
        }
    }

    /**
     * 获取分类和所有文章
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-10-19
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function ArticleCategoryListContent($params = [])
    {
        $data = Db::name('ArticleCategory')->field('id,name')->where(['is_enable'=>1])->order('id asc, sort asc')->select();
        if(!empty($data))
        {
            foreach($data as &$v)
            {
                $items = Db::name('Article')->field('id,title,title_color')->where(['article_category_id'=>$v['id'], 'is_enable'=>1])->select();
                if(!empty($items))
                {
                    foreach($items as &$vs)
                    {
                        // url
                        $vs['url'] = MyUrl('index/article/index', ['id'=>$vs['id']]);
                    }
                }
                $v['items'] = $items;
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 文章访问统计加1
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-10-15
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function ArticleAccessCountInc($params = [])
    {
        if(!empty($params['id']))
        {
            return Db::name('Article')->where(array('id'=>intval($params['id'])))->setInc('access_count');
        }
        return false;
    }

    /**
     * 文章分类
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-08-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function ArticleCategoryList($params = [])
    {
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'sort asc' : trim($params['order_by']);

        $data = Db::name('ArticleCategory')->where(['is_enable'=>1])->field($field)->order($order_by)->select();

        return DataReturn('处理成功', 0, $data);
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
    public static function ReceiptDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn('账户id有误', -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 删除操作
        if(Db::name('Receipt')->where(['receipt_id'=>$params['ids']])->delete())
        {
            return DataReturn('删除成功');
        }

        return DataReturn('删除失败', -100);
    }

    /**
     * 状态更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function ReceiptStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作id有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => '操作字段有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => '状态有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据更新
        if(Db::name('Receipt')->where(['receipt_id'=>intval($params['id'])])->update([$params['field']=>intval($params['state'])]))
        {
            return DataReturn('编辑成功');
        }
        return DataReturn('编辑失败', -100);
    }





    /**
     * 匹配一个充值地址
     */
    public static function getReceiptAddress(){

        $result = Db::name('Receipt')->orderRaw('rand()')->limit(1)->find();
        return $result ?  $result: [];
    }
}
?>
