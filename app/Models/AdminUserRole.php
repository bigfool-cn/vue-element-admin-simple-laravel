<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminUserRole extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'admin_user_role';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = ['admin_user_id','role_id'];

    /**
     * 关闭自动更新时间字段
     * @var bool
     */
    public $timestamps = false;

    /**
     * 保存用户角色
     * @param array $data
     */
    public function saveAdminUserRole($data)
    {
        $model = self::create($data);
        return $model;
    }

    /**
     * 删除用户角色
     * @param array $condition
     * @return int
     */
    public function deleteAdminRole($condition)
    {
        $model = self::where($condition)->delete();
        return $model;
    }
}
