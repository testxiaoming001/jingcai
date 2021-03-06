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

/**
 * 管理员服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class AdminService
{
    /**
     * 管理员列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AdminList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $order_by = empty($params['order_by']) ? 'id desc' : trim($params['order_by']);

        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        // 获取管理员列表
        $data = Db::name('Admin')->where($where)->field($field)->order($order_by)->limit($m, $n)->select();
        if(!empty($data))
        {
            // 获取当前用户角色名称
            $roles =  Db::name('Role')->where('id', 'in', array_column($data, 'role_id'))->column('name', 'id');

            // 数据处理
            $common_gender_list = lang('common_gender_list');
            $common_admin_status_list = lang('common_admin_status_list');
            foreach($data as &$v)
            {
                // 所在角色组
                $v['role_name'] = isset($roles[$v['role_id']]) ? $roles[$v['role_id']] : '';

                // 性别
                $v['gender_text'] = isset($common_gender_list[$v['gender']]) ? $common_gender_list[$v['gender']]['name'] : '';

                // 状态
                $v['status_text'] = isset($common_admin_status_list[$v['status']]) ? $common_admin_status_list[$v['status']]['name'] : '';

                // 时间
                $v['login_time'] = empty($v['login_time']) ? '' : date('Y-m-d H:i:s', $v['login_time']);
                $v['add_time'] = empty($v['add_time']) ? '' : date('Y-m-d H:i:s', $v['add_time']);
                $v['upd_time'] = empty($v['upd_time']) ? '' : date('Y-m-d H:i:s', $v['upd_time']);
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 管理员总数
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @param    [array]          $where [条件]
     */
    public static function AdminTotal($where)
    {
        return (int) Db::name('Admin')->where($where)->count();
    }

    /**
     * 角色列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @param    [array]          $params [输入参数]
     */
    public static function RoleList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $data = Db::name('Role')->field($field)->where($where)->select();
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 管理员保存
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AdminSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'admin',
                'error_msg'         => '用户信息有误',
            ],
            [
                'checked_type'      => 'fun',
                'key_name'          => 'mobile',
                'checked_data'      => 'CheckMobile',
                'is_checked'        => 1,
                'error_msg'         => '手机号码格式错误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'gender',
                'checked_data'      => [0,1,2],
                'error_msg'         => '性别值范围不正确',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'status',
                'checked_data'      => array_column(lang('common_admin_status_list'), 'value'),
                'error_msg'         => '状态值范围不正确',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        return empty($params['id']) ? self::AdminInsert($params) : self::AdminUpdate($params);
    }

    /**
     * 管理员添加
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AdminInsert($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'username',
                'error_msg'         => '用户名不能为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user_telegram_id',
                'error_msg'         => 'telegram账号id不能为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'user_telegram',
                'error_msg'         => 'telegram账号不能为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'login_pwd',
                'error_msg'         => '密码不能为空',
            ],
            [
                'checked_type'      => 'fun',
                'key_name'          => 'username',
                'checked_data'      => 'CheckUserName',
                'error_msg'         => '用户名格式 5~18 个字符（可以是字母数字下划线）',
            ],
            [
                'checked_type'      => 'unique',
                'key_name'          => 'username',
                'checked_data'      => 'Admin',
                'error_msg'         => '用户名已存在',
            ],
            [
                'checked_type'      => 'fun',
                'key_name'          => 'login_pwd',
                'checked_data'      => 'CheckLoginPwd',
                'error_msg'         => '密码格式 6~18 个字符',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'role_id',
                'error_msg'         => '角色组有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 添加账号
        $salt = GetNumberCode(6);
        $data = [
            'username'      => $params['username'],
            'user_telegram_id'      => $params['user_telegram_id'],
            'user_telegram'      => $params['user_telegram'],
            'login_salt'    => $salt,
            'login_pwd'     => LoginPwdEncryption($params['login_pwd'], $salt),
            'mobile'        => isset($params['mobile']) ? $params['mobile'] : '',
            'gender'        => intval($params['gender']),
            'status'        => intval($params['status']),
            'role_id'       => intval($params['role_id']),
            'add_time'      => time(),
        ];

        // 添加
        if(Db::name('Admin')->insert($data) > 0)
        {
            //清理缓存
            cache('botAdminList',null);
            return DataReturn('新增成功', 0);
        }
        return DataReturn('新增失败', -100);
    }

    /**
     * 管理员更新
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AdminUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'fun',
                'key_name'          => 'login_pwd',
                'checked_data'      => 'CheckLoginPwd',
                'is_checked'        => 1,
                'error_msg'         => '密码格式 6~18 个字符',
            ],
        ];
        if($params['id'] != $params['admin']['id'])
        {
            $p[] = [
                'checked_type'      => 'empty',
                'key_name'          => 'role_id',
                'error_msg'         => '角色组有误',
            ];
        }
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 是否非法修改超管
        if($params['id'] == 1 && $params['id'] != $params['admin']['id'])
        {
            return DataReturn('非法操作', -1);
        }

        // 数据
        $data = [
            'mobile'        => isset($params['mobile']) ? $params['mobile'] : '',
            'gender'        => intval($params['gender']),
            'status'        => intval($params['status']),
            'upd_time'      => time(),
            'user_telegram_id'      => $params['user_telegram_id'],
            'user_telegram'      => $params['user_telegram'],
        ];

        // 密码
        if(!empty($params['login_pwd']))
        {
            $data['login_salt'] = GetNumberCode(6);
            $data['login_pwd'] = LoginPwdEncryption($params['login_pwd'], $data['login_salt']);
        }
        // 不能修改自身所属角色组
        if($params['id'] != $params['admin']['id'])
        {
            $data['role_id'] = intval($params['role_id']);
        }

        // 更新
        if(Db::name('Admin')->where(['id'=>intval($params['id'])])->update($data))
        {
            // 自己修改密码则重新登录
            if(!empty($params['login_pwd']) && $params['id'] == $params['admin']['id'])
            {
                session_destroy();
            }
            cache('botAdminList',null);
            return DataReturn('编辑成功', 0);
        }
        return DataReturn('编辑失败或数据未改变', -100);
    }

    /**
     * 管理员删除
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @param    [array]          $params [输入参数]
     */
    public static function AdminDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn('管理员id有误', -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 是否包含删除超级管理员
        if(in_array(1, $params['ids']))
        {
            return DataReturn('超级管理员不可删除', -1);
        }

        // 删除操作
        if(Db::name('Admin')->where(['id'=>$params['ids']])->delete())
        {
            return DataReturn('删除成功');
        }
        return DataReturn('删除失败', -100);
    }


    /**
     * 验证码校验
     * @param $params
     * @param $verify_params
     * @param int $status
     * @return array
     */
    private static function IsImaVerify($params, $verify_params)
    {
        if(empty($params['verify']))
        {
            return DataReturn('图片验证码为空', -10);
        }
        $verify = new \base\Verify($verify_params);
        if(!$verify->CheckExpire())
        {
            return DataReturn('验证码已过期', -11);
        }
        if(!$verify->CheckCorrect($params['verify']))
        {
            return DataReturn('验证码错误', -12);
        }
        return DataReturn('操作成功', 0, $verify);
    }


    /**
     * 管理员登录
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-10T22:16:29+0800
     * @param    [array]          $params [输入参数]
     */
    public static function Login($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'username',
                'error_msg'         => '用户名不能为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'login_pwd',
                'error_msg'         => '密码不能为空',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'verify',
                'error_msg'         => '验证码不能为空',
            ],
            [
                'checked_type'      => 'fun',
                'key_name'          => 'username',
                'checked_data'      => 'CheckUserName',
                'error_msg'         => '用户名格式 5~18 个字符（可以是字母数字下划线）',
            ],
            [
                'checked_type'      => 'fun',
                'key_name'          => 'login_pwd',
                'checked_data'      => 'CheckLoginPwd',
                'error_msg'         => '密码格式 6~18 个字符',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        //图片验证码校验
        $verify_params = [
            'key_prefix' => 'login',
            'expire_time' => MyC('common_verify_expire_time'),
        ];
        $verify = self::IsImaVerify($params, $verify_params);
        if($verify['code'] != 0)
        {
            return $verify;
        }


        // 获取管理员
        $admin = Db::name('Admin')->field('id,username,login_pwd,login_salt,mobile,login_total,role_id')->where(['username'=>$params['username'], 'status'=>0])->find();
        if(empty($admin))
        {
            return DataReturn('账户异常', -2);
        }

        // 密码校验
        $login_pwd = LoginPwdEncryption($params['login_pwd'], $admin['login_salt']);
        if($login_pwd != $admin['login_pwd'])
        {
            return DataReturn('密码错误', -3);
        }

        // 校验成功
        // session存储
        session('admin', $admin);

        // 返回数据,更新数据库
        if(session('admin') != null)
        {
            $login_salt = GetNumberCode(6);
            $data = array(
                    'login_salt'    =>  $login_salt,
                    'login_pwd'     =>  LoginPwdEncryption($params['login_pwd'], $login_salt),
                    'login_total'   =>  $admin['login_total']+1,
                    'login_time'    =>  time(),
                );
            if(Db::name('Admin')->where(['id'=>$admin['id']])->update($data))
            {
                // 清空权限缓存数据
                cache(config('cache_admin_left_menu_key').$admin['id'], null);
                cache(config('cache_admin_power_key').$admin['id'], null);

                return DataReturn('登录成功');
            }
        }

        // 失败
        session('admin', null);
        return DataReturn('登录失败，请稍后再试！', -100);
    }
}
?>
