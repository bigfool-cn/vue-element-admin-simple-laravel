<?php

namespace App\Models;

use App\Http\Common\Tools;
use Illuminate\Database\Eloquent\Model;

class AdminRouter extends Model
{
    /**
     * 数据表主键
     * @var string
     */
    protected $primaryKey = 'admin_router_id';

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'admin_router';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = ['title','param','parent_id','sort','update_time','create_time'];

    /**
     * 关闭自动更新时间字段
     * @var bool
     */
    public $timestamps = false;

    /**
     * 创建路由
     * @param array $data
     * @return mixed
     */
    public function createAdminRouter($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        $model = self::create($data);
        return $model->admin_router_id;
    }

    /**
     * 更新路由
     * @param int $adminRouterId
     * @param array $data
     * @return int|string
     */
    public function updateAdminRouter($adminRouterId,$data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        $model = self::where('admin_router_id',$adminRouterId)->update($data);
        return $model;
    }

    /**
     * 删除路由
     *
     * @return int
     */
    public function deleteAdminRouter($adminRouterId=0)
    {
        $model = self::where('admin_router_id', $adminRouterId)->orWhere('parent_id', $adminRouterId)->delete();
        return $model;
    }

    /**
     * 获取路由树
     * @return array
     */
    public function getAdminRouterTree()
    {
        $fileds = array('admin_router_id AS id','title AS label','parent_id');
        $model = self::orderBy('sort', 'ASC')->orderBy( 'parent_id','ASC')
            ->get($fileds)->toArray();
        $data = array();
        foreach ($model as $key=>$value) {
            $data[$value['id']] = $value;
        }
        $tree = Tools::arrayTree($data);
        $data = array(
            'routers_tree' => $tree,
        );
        return $data;
    }

    /**
     * 获取一条后台路由
     * @param array $condition
     * @param array $fields
     */
    public function getAdminRouter($condition=array(), $fields=array("*"))
    {
        $model = self::where($condition)->select($fields)->first();
        $model && $model->param = json_decode($model->param, true);
        return $model;
    }


    /**
     * 获取后台路由分页
     * @param int $page 页码
     * @param int $row 数量
     * @param string $condition
     * @return array
     */
    public function getAdminRouterPage($page=1, $row=20, $condition='1=1')
    {
        $paginate = self::whereRaw($condition)->paginate($row);
        $admin_routers = $paginate->all();
        $pages = array(
            'current_page' => $paginate->currentPage(),
            'last_page'    => $paginate->lastPage(),
            'per_page'     => $paginate->perPage(),
            'total'        => $paginate->total(),
        );
        $data = array(
            'pages'         => $pages,
            'admin_routers' => $admin_routers
        );
        return $data;
    }

    /**
     * 获取路由表结构
     * @param array $condition
     * @return array
     */
    public function getAdminRouterTable($condition=array())
    {
        $model = self::whereIn('admin_router_id',$condition['admin_router_id'])->orderBy('sort', 'ASC')
            ->orderBy('parent_id', 'ASC')->get(array('admin_router_id AS id','parent_id','param'));
        $data = array();
        foreach ($model as $key=>$value) {
            $param               = json_decode($value->param,true);
            $param['id']         = $value->id;
            $param['parent_id']  = $value->parent_id;
            $data[$value->id] = $param;
        }
        $tree = Tools::arrayTree($data);
        return $tree;
    }
}
