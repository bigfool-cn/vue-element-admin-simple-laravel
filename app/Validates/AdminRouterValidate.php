<?php
/**
 * Created by PhpStorm.
 * User: oray
 * Date: 2019/6/14
 * Time: 14:01
 */

namespace App\Validates;


class AdminRouterValidate extends BaseValidate
{
    protected $rule = [
        'parent_id'  => 'required|integer',
        'title'      => 'required|max:50',
        'param'      => 'required'
    ];

    protected $message = [
        'parent_id.required' => '根路由不能为空',
        'parent_id.integer'  => '根路由不是正书',
        'title.required'     => '路由名称不能为空',
        'title.max'          => '路由名称长度不能超过50',
        'param.required'     => '路由配置项不能为空'
    ];
}