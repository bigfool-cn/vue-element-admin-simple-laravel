<?php
/**
 * Created by PhpStorm.
 * User: oray
 * Date: 2019/6/14
 * Time: 14:44
 */

namespace App\Validates;


class SystemButtonValidate extends BaseValidate
{
    protected $rule = array(
        'button_id' => 'required|integer',
        'title'     => 'required|max:50',
        'key'       => 'required|unique:system_button,key|max:30',
        'is_enable' => 'required|boolean'
    );

    protected $message = array(
        'button_id.required' => 'ID不能为空',
        'button_id.integer'  => 'ID不是整数',
        'title.required'     => '按钮名称不能为空',
        'title.max'          => '按钮名称长度不能超过50',
        'key.required'       => '唯一标识不能为空',
        'key.unique'         => '唯一标识已存在',
        'key.max'            => '唯一标识长度不能超过30',
        'is_enable.required' => '是否可用不能为空',
        'is_enable.boolean'  => '是否可用值不合法'
    );

    protected $scene = array(
        'create'        => array('title', 'key', 'is_enable'),
        'update'        => array('button_id' ,'title', 'is_enable'),
        'update_enable' => array('button_id', 'is_enable')
    );
}
