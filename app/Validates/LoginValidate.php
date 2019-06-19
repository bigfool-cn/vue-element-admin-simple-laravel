<?php
/**
 * Created by PhpStorm.
 * User: oray
 * Date: 2019/6/14
 * Time: 14:41
 */

namespace App\Validates;


class LoginValidate extends BaseValidate
{
    protected $rule = array(
        'username' => 'required',
        'password' => 'required|min:6',
    );

    protected $message = array(
        'username.required' => '请输入账号',
        'password.required' => '请输入密码',
        'password.min'      => '密码至少六位数'
    );
}