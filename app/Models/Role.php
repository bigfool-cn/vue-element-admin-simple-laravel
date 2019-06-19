<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * 数据表主键
     * @var string
     */
    protected $primaryKey = 'role_id';

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'role';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = ['role_name','router_ids','button_ids','desc','update_time','create_time'];

    /**
     * 关闭自动更新时间字段
     * @var bool
     */
    public $timestamps = false;

    /**
     * 创建角色
     * @param $data
     * @return mixed
     */
    public function createRole($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        $model = self::create($data);
        return $model->role_id;
    }

    /**
     * 更新角色
     * @param int $roleId
     * @param array $data
     * @return int|string
     */
    public function updateRole($roleId,$data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        $model = self::where('role_id',$roleId)->update($data);
        return $model;
    }

    /**
     * 删除角色
     * @param array $condition
     * @return int
     */
    public function deleteRole($condition)
    {
        $model = self::where($condition)->delete();
        return $model;
    }

    /**
     * 获取单条角色
     * @param array $condition
     * @param array $fields
     */
    public function getRole($condition=array(), $fields=array('*'))
    {
        $model = self::where($condition)->select($fields)->first();
        if ($model) {
            $model->router_ids = json_decode($model->router_ids, true);
            $model->button_ids = json_decode($model->button_ids, true);
        }

        return $model;
    }

    /**
     * 获取角色分页
     * @param int $page 页码
     * @param int $row 数量
     * @param $condition
     * @return array
     */
    public function getRolePage($page=1, $row=20, $condition='1=1')
    {
        $paginate = self::whereRaw($condition)->orderBy('create_time', 'DESC')->paginate($row);
        $roles = $paginate->all();
        $pages = array(
            'current_page' => (int) $paginate->currentPage(),
            'last_page'    => (int) $paginate->lastPage(),
            'per_page'     => (int) $paginate->perPage(),
            'total'        => (int) $paginate->total(),
        );
        $data = array(
            'pages' => $pages,
            'roles' => $roles
        );
        return $data;
    }

    /**
     * 获取角色权限
     * @param array $roleIds
     * @param array $buttonIds
     * @return array
     */
    public function getRoleAuths($routerIds, $buttonIds)
    {
        // 去重
        $routerIds = array_unique($routerIds);
        $buttonIds = array_unique($buttonIds);
        // 路由权限
        $adminRouterModel = new AdminRouter();
        $condition = array('admin_router_id'=>$routerIds);
        $routers = $adminRouterModel->getAdminRouterTable($condition);
        // 按钮权限
        $systemButtomModel = new SystemButton();
        $condition = array('button_id'=>$buttonIds);
        $fields = array('key');
        $buttons = $systemButtomModel->getSystemButtonAll($condition, $fields);

        return array('routers'=>$routers, 'buttons'=>array_column($buttons,'key'));
    }
}
