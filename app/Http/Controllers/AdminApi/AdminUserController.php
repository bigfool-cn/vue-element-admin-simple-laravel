<?php
/**
 * Created by PhpStorm.
 * User: oray
 * Date: 2019/6/14
 * Time: 14:58
 */

namespace App\Http\Controllers\AdminApi;


use App\Models\AdminUserRole;
use App\Models\Role;
use App\Validates\AdminUserValidate;
use App\Validates\LoginValidate;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminUserController extends AdminApiController
{

    /**
     * 登录验证器
     * @var null
     */
    private $_loginValidate = null;

    /**
     * 后台用户信息验证器
     * @var null
     */
    private $_adminUserValidate = null;

    /**
     * 后台用户模型
     * @var null
     */
    private $_adminUserModel = null;

    /**
     * 用户角色模型
     * @var null
     */
    private $_adminUserRoleModel = null;

    public function __construct()
    {
        $this->_loginValidate      = new LoginValidate();
        $this->_adminUserValidate  = new AdminUserValidate();
        $this->_adminUserModel     = new AdminUser();
        $this->_adminUserRoleModel = new AdminUserRole();
    }

    /**
     * 后台登录
     * @return string
     */
    public function login(Request $request)
    {
        $form = $request->all();
        $validate = $this->_loginValidate->check($form);
        if (!$validate) {
            $adminUser = $this->_adminUserModel->getAdminUser(array('username'=>$form['username']));
            if (empty($adminUser)) {
                return $this->json(40003,'用户不存在');
            }
            if (!$adminUser['is_active']) {
                return $this->json(40003,'用户未激活');
            }
            if (!password_verify($form['password'], $adminUser['password'])) {
                return $this->json(40003,'密码错误');
            }
            // 请求验证token
            $startTime  = time();
            $ExpireTime = time()+7200;
            $userId     = $adminUser['admin_user_id'];
            $userName   = $adminUser['username'];
            $access_token = \App\Http\Common\Tools::generateJwt($startTime, $ExpireTime, $userId, $userName);
            $data = array(
                'access_token' => $access_token
            );
            // 记录登录时间
            $this->_adminUserModel->updateAdminUser($userId, array('login_time'=>date('Y-m-d H:i:s')));
            return $this->json(20000,'登录成功', $data);
        } else {
            return $this->json(40004, $validate);
        }
    }

    /**
     * 获取信息
     * @return string
     */
    public function info(Request $request)
    {
        $userId = $request->userId;
        $adminUser = $this->_adminUserModel->getAdminUserById($userId);
        if (!$adminUser) {
            return $this->json(40000,'用户不存在');
        }

        // 获取用户角色权限
        $roles = $routers = $buttons = array();
        $this->_getAdminUserRoleAuth($adminUser['roles'],$roles,$routers,$buttons);

        $data = array(
            'user_id'  => $userId,
            'name'     => $adminUser->username,
            'avatar'   => $adminUser->avatar,
            'roles'    => $roles,
            'routers'  => $routers,
            'buttons'  => $buttons
        );
        return $this->json(20000,'ok', $data);
    }

    /**
     * 新增管理员
     */
    public function createAdminUser(Request $request)
    {
        $post = $request->all();
        $validate = $this->_adminUserValidate->check($post,'create');
        if (!$validate) {
            try {
                $data = array(
                    'username' => $post['username'],
                    'password' => $post['password']
                );
                DB::beginTransaction();
                $adminUserId = $this->_adminUserModel->createAdminUser($data);
                // 添加用户角色
                $roles = $post['roles'];
                $adminUserRoles = array();
                foreach ($roles as $key=>$role) {
                    $adminUserRoles[] = array('admin_user_id'=>$adminUserId,'role_id'=>$role);
                }
                $res = DB::table('admin_user_role')->insert($adminUserRoles);
                if ($adminUserId && $res) {
                    DB::commit();
                    return $this->json(20000, '新增管理员成功');
                } else {
                    DB::rollback();
                    return $this->json(50000, '新增管理员失败');
                }
            } catch (\Exception $e) {
                DB::rollback();
                return $this->json(50000, '新增管理员失败');
            }
        } else {
            return $this->json(40003, $validate);
        }
    }

    /**
     * 更新用户角色
     * @return string
     */
    public function updateAdminUserRole(Request $request)
    {
        $post = $request->all();
        $validate = $this->_adminUserValidate->check($post,'update_role');
        if (!$validate) {
            $adminUserId = $post['admin_user_id'];
            if ($adminUserId == 1) {
                return $this->json(40003, '超级管理员角色不可更改');
            }
            $data = array();
            foreach ($post['roles'] as $key=>$role) {
                $data[] = array('admin_user_id'=>$adminUserId,'role_id'=>$role);
            }
            try {
                $adminUser = $this->_adminUserModel->getAdminUserById($adminUserId);
                if (!$adminUser) {
                    return $this->json(40000,'用户不存在');
                }
                DB::beginTransaction();
                $delRes = $this->_adminUserRoleModel->deleteAdminRole(array('admin_user_id'=>$adminUserId));
                $saveRes = DB::table('admin_user_role')->insert($data);
                if ($delRes && $saveRes) {
                    DB::commit();
                    return $this->json(20000, '分配角色成功');
                } else {
                    DB::rollback();
                    return $this->json(50000, '分配角色失败');
                }
            } catch (\Exception $e) {
                DB::rollback();
                return $this->json(50000, $e->getMessage());
            }
        } else {
            return $this->json(40003, $validate);
        }
    }

    /**
     * 获取用户角色权限
     * @param $rolesModel
     * @param $roles
     * @param $routers
     * @param $buttons
     */
    private function _getAdminUserRoleAuth($rolesModel, &$roles, &$routers, &$buttons)
    {
        $routerIds = $buttonIds = array();
        foreach ($rolesModel as $key=>$value) {
            $routerIds = array_merge($routerIds, json_decode($value['router_ids'],true));
            $buttonIds = array_merge($buttonIds, json_decode($value['button_ids'],true));
            array_push($roles, $value['role_name']);
        }
        // 获取权限
        $roleModel = new Role();
        $auths     = $roleModel->getRoleAuths($routerIds, $buttonIds);
        $routers   = $auths['routers'];
        $buttons   = $auths['buttons'];

    }

    /**
     * 更新管理员激活状态
     * @return false|string
     */
    public function updateAdminUserActive(Request $request)
    {
        $post = $request->all();
        $validate = $this->_adminUserValidate->check($post, 'update_active');
        if (!$validate) {
            $data = array('is_active'=>!$post['is_active']);
            try {
                if ($post['admin_user_id'] == 1) {
                    return $this->json(40003, '超级管理员激活状态不可更改');
                }
                $res = $this->_adminUserModel->updateAdminUser($post['admin_user_id'], $data);
                if ($res) {
                    return $this->json(20000, '更新激活状态成功');
                } else {
                    return $this->json(50000, '更新激活状态失败');
                }
            } catch (\Exception $e) {
                return $this->json(50000, '更新激活状态失败');
            }

        } else {
            return $this->json(40003, $validate);
        }
    }

    /**
     * 修改密码
     * @return false|string
     */
    public function updateAdminUserPassword(Request $request)
    {
        $post = $request->all();
        $validate = $this->_adminUserValidate->check($post, 'update_password');
        if (!$validate) {
            try {
                $data = array('password'=>password_hash($post['password'], PASSWORD_BCRYPT));
                $res = $this->_adminUserModel->updateAdminUser($post['admin_user_id'], $data);
                if ($res) {
                    return $this->json(20000, '修改密码成功');
                } else {
                    return $this->json(50000, '修改密码失败');
                }
            } catch (\Exception $e) {
                return $this->json(5000, '修改密码失败');
            }
        } else {
            return $this->json(40003, $validate);
        }
    }

    /**
     * 用户修改密码
     * @return false|string
     */
    public function updateUserPassword(Request $request)
    {
        $post = $request->all();
        $validate = $this->_adminUserValidate->check($post, 'update_user_pwd');
        if (!$validate) {
            try {
                $condition = array(
                    'admin_user_id' => $post['admin_user_id']
                );
                $adminUser = $this->_adminUserModel->getAdminUser($condition);
                if (!$adminUser) {
                    return $this->json(40003, '用户不存在');
                }
                if (!password_verify($post['old_password'], $adminUser['password'])) {
                    return $this->json(40003,'旧密码不正确');
                }
                $data = array('password'=>password_hash($post['password'], PASSWORD_BCRYPT));
                $res = $this->_adminUserModel->updateAdminUser($post['admin_user_id'], $data);
                if ($res) {
                    return $this->json(20000, '修改密码成功');
                } else {
                    return $this->json(50000, '修改密码失败');
                }
            } catch (\Exception $e) {
                return $this->json(5000, '修改密码失败');
            }
        } else {
            return $this->json(40003, $validate);
        }
    }


    /**
     * 用户上传头像
     * @return string
     */
    public function uploadAvatar(Request $request)
    {
        $file = $request->file('file');
        $adminUserId = $request->input('user_id',0);
        $post = array('admin_user_id'=>$adminUserId,'avatar'=>$file);
        $valdate = $this->_adminUserValidate->check($post,'update_avatar');
        if (!$valdate) {
            if (!$adminUserId) {
                return $this->json(40000,'ID不存在');
            }
            $adminUser = $this->_adminUserModel->getAdminUserById($adminUserId);
            if (!$adminUser) {
                return $this->json(40000,'用户不存在或已删除');
            }
            @unlink(env('APP_DIR') . str_replace(env('APP_URL'),'', $adminUser->avatar));
            $avatardDir = 'user-avatar';
            $fileName = md5(time().$adminUserId) . '.' . $file->extension();
            $info = $file->storeAs($avatardDir,$fileName);
            //3、保存上传文件（获取临时文件的路径）
            Storage::disk($avatardDir) ->put($fileName,file_get_contents($request->file('file')->path()));
            if (!$info) {
                return $this->json(50000,'上传失败，'.$file->getError());
            }
            $avatar = '/' . $avatardDir . '/' . $fileName;
            try {
                $res = $this->_adminUserModel->updateAdminUser($adminUserId,array('avatar'=>$avatar));
                if ($res) {
                    $data = array(
                        'avatar' => env('APP_URL') . $avatar
                    );
                    return $this->json(20000,'上传成功', $data);
                } else {
                    return $this->json(50000,'上传失败');
                }
            } catch (\Exception $e) {
                return $this->json(50000,'上传失败');
            }
        } else {
            return $this->json(40000,'上传失败' . $valdate);
        }

    }

    /**
     * 获取管理员列表
     * @return string
     */
    public function getAdminUserList(Request $request)
    {
        $params = $request->all();
        $page   = 1;
        $row    = 20;
        $condition  = '1=1';
        $this->_makeCondition($params, $condition, $page, $row);
        $data = $this->_adminUserModel->getAdminUserPage($page, $row, $condition);
        return $this->json(20000,'ok', $data);
    }

    /**
     * 列表查询条件
     * @param $params
     * @param $condition
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

        // 用户名称
        if (isset($params['username']) && !empty($params['username'])) {
            $condition .= " AND username LIKE '%{$params['username']}%'";
        }

        // 激活状态
        if (isset($params['is_active']) && $params['is_active'] !== '' && in_array($params['is_active'], [0, 1])) {
            $condition && $condition .= ' AND ';
            $condition .= "is_active = {$params['is_active']}";
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
