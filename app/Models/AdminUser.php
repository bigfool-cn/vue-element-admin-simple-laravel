<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    /**
     * 数据表主键
     * @var string
     */
    protected $primaryKey = 'admin_user_id';

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'admin_user';

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = ['password'];

    /**
    * 可以被批量赋值的属性.
    *
    * @var array
    */
    protected $fillable = ['username','password','avatar','is_active','update_time','create_time','login_time'];

    /**
     * 关闭自动更新时间字段
     * @var bool
     */
    public $timestamps = false;

    /**
     * 处理头像返回绝对路径
     * @param $value
     * @return string
     */
    public function getAvatarAttribute($value)
    {
        return env('APP_URL') . $value;
    }

    /**
     * 新增用户信息
     * @param array $data
     * @return mixed
     */
    public function createAdminUser($data=array())
    {
        $data['password']    = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['create_time'] = date('Y-m-d H:i:s');
        $model = self::create($data);
        return $model->admin_user_id;
    }

    /**
     * 更新用户信息
     * @param $adminUserId
     * @param $data
     * @return int|string
     */
    public function updateAdminUser($adminUserId, $data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        $res = self::where('admin_user_id', $adminUserId)->update($data);
        return $res;
    }


    /**
     * 根据id获取用户信息
     * @param $adminUserId
     * @return mixed
     */
    public function getAdminUserById($adminUserId)
    {
        $data = self::with('roles')->find($adminUserId)->makeVisible('password');
        return $data;
    }

    /**
     * 获取单条用户信息
     * @param $condition
     * @param array $hidden
     * @return array
     */
    public function getAdminUser($condition=array())
    {
        $data = self::where($condition)->with('roles')->first()->makeVisible('password');
        return $data;
    }

    /**
     * 获取多条用户信息
     * @param array $condition
     * @return mixed
     */
    public function getAdminUsers($condition=array())
    {
        $data = self::where($condition)->with('roles')->all();
        return $data;
    }

    /**
     * 获取用户信息分页
     * @param int $page
     * @param int $row
     * @return array
     */
    public function getAdminUserPage($page=1, $row=20, $condition='1=1')
    {
        $paginate = self::whereRaw($condition)->with('roles')->orderBy('create_time', 'DESC')
            ->paginate($row);
        $admin_users = $paginate->all();
        $pages = array(
            'current_page' => (int) $paginate->currentPage(),
            'last_page'    => (int) $paginate->lastPage(),
            'per_page'     => (int) $paginate->perPage(),
            'total'        => (int) $paginate->total(),
        );
        $data = array(
            'pages'       => $pages,
            'admin_users' => $admin_users
        );
        return $data;
    }

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role','admin_user_role','admin_user_id','role_id');
    }
}
