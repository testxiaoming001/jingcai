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
use app\service\UserService;
use app\service\GoodsService;

/**
 * tg服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Tg
{
    /**
     * tg投诉
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-10-09
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function complaint($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'tg_username',
                'error_msg'         => 'TG用户名不可为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'score',
                'error_msg'         => '请填写信誉分',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'reason',
                'error_msg'         => '请填写投诉原因',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'talk_logs',
                'error_msg'         => '请填写聊天凭证',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        $params['reason']  =htmlspecialchars($params['reason']);
        $params['talk_logs']  =htmlspecialchars($params['talk_logs']);
        $params['create_time'] = time();
        // 评分
        if(intval($params['score']) < 0 || intval($params['score']) >100)
        {
            return DataReturn('信誉分有误,请输入0-100', -1);
        }
        // 处理数据
        try{
            Db::name('tgComplaint')->insert($params);
            Db::startTrans();
        }catch(\Exception $exception)
        {
            Db::rollback();
            return DataReturn('投诉失败', -101);
        }
        return DataReturn('投诉成功', 0);
    }


    /**
     * tg投诉列表
     * @param $where
     * @param $limit
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function  complaints($where,$limit=10)
    {
        return Db::name('tgComplaint')->where($where)->paginate($limit, false, [
            'query' => input()]);
    }
}
?>
