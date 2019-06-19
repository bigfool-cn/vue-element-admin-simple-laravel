<?php
/**
 * Created by PhpStorm.
 * User: JS_chen
 * Date: 2019/5/19
 * Time: 14:52
 */

namespace App\Validates;


class AdminUserValidate extends BaseValidate
{

    protected $rule = array(
        'admin_user_id' => 'required|integer',
        'username'      => 'required|regex:/^[0-9a-zA-z]{1,}$/|min:3|unique:admin_user,username',
        'password'      => 'required|min:6|confirmed',
        'old_password'  => 'required|min:6',
        'is_active'     => 'required|boolean',
        'roles'         => 'required',
        'avatar'        => 'required|mimes:jpeg,jpg,png,gif|max:2048'
    );

    protected $message = array(
        'admin_user_id.required' => '用户ID不能为空',
        'admin_user_id.integer'  => '用户ID不是整数',
        'username.required'      => '用户名不能为空',
        'username.regex'         => '用户名由数字或字母组成',
        'username.min'           => '用户名长度不能小于3',
        'username.unique'        => '用户名已存在',
        'password.required'      => '密码不能为空',
        'password.confirmed'     => '两次密码输入不一致',
        'password.min'           => '密码长度不能小于6',
        'old_password.required'  => '旧密码不正确',
        'old_password.min'       => '旧密码不正确',
        'is_active.required'     => '激活状态不能为空',
        'is_active.boolean'      => '激活状态值不合法',
        'roles.required'         => '角色不能为空',
        'avatar.required'        => '头像不能为空',
        'avatar.mimes'           => '头像不是[jpeg,jpg,png,gif]类型',
        'avatar.max'             => '头像大小超过2M'
    );

    protected $scene = array(
        'create'          => array('username','password','is_active', 'roles'),
        'update_password' => array('admin_user_id','password'),
        'update_user_pwd' => array('admin_user_id','password','old_password'),
        'update_active'   => array('admin_user_id','is_active'),
        'update_role'     => array('admin_user_id','roles'),
        'update_avatar'   => array('admin_user_id','avatar')
    );
}
