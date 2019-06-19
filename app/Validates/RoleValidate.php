<?php
/**
 * Created by PhpStorm.
 * User: oray
 * Date: 2019/6/14
 * Time: 14:42
 */

namespace App\Validates;


class RoleValidate extends BaseValidate
{
    protected $rule = array(
        'role_id'   => 'required|integer',
        'role_name' => 'required|max:50,unique:role,role_name',
        'desc'      => 'required'
    );

    protected $message = array(
        'role_id.required'   => 'ID不能为空',
        'role_id.integer'    => 'ID不是整数',
        'role_name.required' => '角色名称不能为空',
        'role_name.max'      => '角色名称长度不能超过50',
        'role_name.unique'   => '角色名称已存在',
        'desc.required'      => '角色描述不能为空'
    );

    protected $scene = array(
        'create' => array('role_name', 'desc'),
        'update' => array('role_id', 'desc')
    );
}