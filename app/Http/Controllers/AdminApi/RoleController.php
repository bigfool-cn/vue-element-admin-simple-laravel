<?php
/**
 * Created by PhpStorm.
 * User: JS_chen
 * Date: 2019/6/14
 * Time: 23:03
 */

namespace App\Http\Controllers\AdminApi;


use App\Models\Role;
use App\Validates\RoleValidate;
use Illuminate\Http\Request;

class RoleController extends AdminApiController
{
    /**
     * 角色模型
     * @var null
     */
    private $_roleModel = null;

    /**
     * 角色验证器
     * @var null
     */
    private $_roleValidate = null;

    public function __construct()
    {
        $this->_roleModel    = new Role();
        $this->_roleValidate = new RoleValidate();
    }

    /**
     * 新增角色
     * @return false|string
     */
    public function createRole(Request $request)
    {
        $post = $request->all();
        $validate = $this->_roleValidate->check($post, 'create');
        if (!$validate) {
            $data = array(
                'role_name'  => $post['role_name'],
                'desc'       => $post['desc'],
                'router_ids' => json_encode($post['routers']),
                'button_ids' => json_encode($post['buttons']),
            );
            try {
                $res = $this->_roleModel->createRole($data);
                if ($res) {
                    return $this->json(20000,'保存角色成功');
                } else {
                    return $this->json(50000,'保存角色失败');
                }
            } catch (\Exception $e) {
                return $this->json(50000,'保存角色失败');
            }
        } else {
            return $this->json(40000, $validate);
        }
    }

    /**
     * 修改角色
     * @return false|string
     */
    public function updateRole(Request $request)
    {
        $post = $request->all();
        $validate = $this->_roleValidate->check($post, 'update');
        if (!$validate) {
            $data = array(
                'role_name'  => $post['role_name'],
                'desc'       => $post['desc'],
                'router_ids' => json_encode($post['routers']),
                'button_ids' => json_encode($post['buttons']),
            );
            try {
                $res = $this->_roleModel->updateRole($post['role_id'], $data);
                if ($res) {
                    return $this->json(20000,'保存角色成功');
                } else {
                    return $this->json(50000,'保存角色失败');
                }
            } catch (\Exception $e) {
                return $this->json(50000,'保存角色失败');
            }
        } else {
            return $this->json(40000, $validate);
        }
    }

    /**
     * 删除角色
     * @return false|string
     */
    public function deleteRole(Request $request)
    {
        $post   = $request->all();
        $roleId = $post['role_id'];
        if (empty($roleId) && !is_int($roleId) && $roleId < 0) {
            return $this->json(40000,'参数ID错误');
        }
        try {
            $condition = array('role_id'=>$roleId);
            $res = $this->_roleModel->deleteRole($condition);
            if ($res) {
                $data = $this->_roleModel->getRolePage();
                return $this->json(20000, '删除角色成功', $data);
            } else {
                return $this->json(50000, '删除角色失败');
            }
        } catch (\Exception $e) {
            return $this->json(50000,'删除角色失败');
        }
    }

    /**
     * 获取单条角色
     * @param int $role_id
     * @return string
     */
    public function getRole(Request $request)
    {
        $role_id = $request->input('role_id',0);
        $condition = array('role_id'=> $role_id);
        $fields = array('role_id', 'role_name', 'desc', 'router_ids', 'button_ids');
        $data = $this->_roleModel->getRole($condition, $fields);
        return $this->json(20000,'获取成功',$data);
    }

    /**
     * 获取角色列表
     * @return false|string
     */
    public function getRoleList(Request $request)
    {
        $params = $request->all();
        $page   = 1;
        $row    = 20;
        $condition  = '1=1';
        $this->_makeCondition($params,$condition,$page, $row);
        $data = $this->_roleModel->getRolePage($page, $row, $condition);
        return $this->json(20000,'获取成功',$data);
    }

    /**
     * 列表查询条件
     * @param $params
     * @param $where
     * @param $page
     * @param $row
     */
    private function _makeCondition($params, &$condition, &$page, &$row)
    {
        if (empty($params)) {
            return;
        }

        // 页码
        if (isset($params['page']) && $params['page'] > 0 && is_int((int)$params['page'])) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        // 数量
        if (isset($params['row']) && $params['row'] > 0 && is_int((int)$params['row']) && $params['row'] <= 100) {
            $row = $params['row'];
        } else {
            $row = 20;
        }

        // 角色名称
        if (isset($params['role_name']) && !empty($params['role_name'])) {
            $condition .= " AND role_name LIKE '%{$params['role_name']}%'";
        }

        // 时间
        if (isset($params['date']) && strtotime($params['date'][0])) {

            $condition && $condition .= ' AND ';
            $condition .= "create_time >= '{$params['date'][0]}'";
        }
        if (isset($params['date']) && strtotime($params['date'][1])) {
            $condition && $condition .= ' AND ';
            $endTime = date('Y-m-d', strtotime ("+1 day", strtotime($params['date'][1])));
            $condition .= "create_time <= '$endTime'";
        }
    }
}
