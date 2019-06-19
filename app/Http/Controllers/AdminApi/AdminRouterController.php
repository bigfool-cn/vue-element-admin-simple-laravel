<?php
/**
 * Created by PhpStorm.
 * User: JS_chen
 * Date: 2019/6/14
 * Time: 22:52
 */

namespace App\Http\Controllers\AdminApi;


use App\Models\AdminRouter;
use App\Validates\AdminRouterValidate;
use Illuminate\Http\Request;

class AdminRouterController extends AdminApiController
{

    /**
     * 后台路由验证器
     */
    private $_adminRouterValidate = null;

    /**
     * 后台路由模型
     */
    private $_adminRouterModel = null;

    public function __construct()
    {
        $this->_adminRouterValidate = new AdminRouterValidate();
        $this->_adminRouterModel    = new AdminRouter();
    }

    /**
     * 后台路由新增
     * @return string
     */
    public function createAdminRouter(Request $request)
    {
        $post = $request->all();
        $param = $post['param'];
        $param = json_decode($param,true);
        if (!$param) {
            return $this->json(40000,'路由配置项不是json格式');
        }
        $validate = $this->_adminRouterValidate->check($post);
        if (!$validate) {
            try {
                $adminRouter = $this->_adminRouterModel->getAdminRouter($post);
                if ($adminRouter) {
                    return $this->json(40000,'该路由已经存在');
                }
                $adminRouterId = $this->_adminRouterModel->createAdminRouter($post);
                if ($adminRouterId) {
                    $data = $this->_adminRouterModel->getAdminRouterTree();
                    $data = array(
                        'routers_tree'    => $data['routers_tree'],
                        'admin_router_id' => $adminRouterId
                    );
                    return $this->json(20000,'新增动态路由成功',$data);
                } else {
                    return $this->json(50000,'新增动态路由失败');
                }
            } catch (\Exception $e) {
                return $this->json(50000, $e->getMessage());
            }

        } else {
            return $this->json(40000, $validate);
        }
    }

    /**
     * 后台路由修改
     * @return string
     */
    public function updateAdminRouter(Request $request)
    {
        $post  = $request->all();
        $param = $post['param'];
        $param = json_decode($param,true);
        $adminRouterId = (int) $post['admin_router_id'];
        if (!$param) {
            return $this->json(40000,'路由配置项不是json格式');
        }
        if (!is_int($adminRouterId) && $adminRouterId < 0) {
            return $this->json(40000,'ID参数错误');
        }
        $validate = $this->_adminRouterValidate->check($post);
        if (!$validate) {
            try {
                $adminRouter = $this->_adminRouterModel->getAdminRouter($post);
                if ($adminRouter && $adminRouter->admin_router_id !== $adminRouterId) {
                    return $this->json(40000,'该路由已经存在');
                }
                $res = $this->_adminRouterModel->updateAdminRouter($adminRouterId, $post);
                if ($res) {
                    $data = $this->_adminRouterModel->getAdminRouterTree();
                    return $this->json(20000,'修改动态路由成功',$data);
                } else {
                    return $this->json(50000,'修改动态路由失败');
                }
            } catch (\Exception $e) {
                return $this->json(50000, '修改动态路由失败');
            }

        } else {
            return $this->json(40000, $validate);
        }
    }

    /**
     * 后台路由删除
     * @return false|string
     */
    public function deleteAdminRouter(Request $request)
    {
        $post = $request->all();
        isset($post['admin_router_id']) ?  $adminRouterId=$post['admin_router_id'] : $adminRouterId=0;
        if (empty($adminRouterId) && !is_int($adminRouterId) && $adminRouterId < 0) {
            return $this->json(40000,'参数ID错误');
        }
        try {
            $res = $this->_adminRouterModel->deleteAdminRouter($adminRouterId);
            if ($res) {
                $data = $this->_adminRouterModel->getAdminRouterTree();
                return $this->json(20000, '删除动态路由成功', $data);
            } else {
                return $this->json(50000, '删除动态路由失败');
            }
        } catch (\Exception $e) {
            return $this->json(50000,'删除动态路由失败');
        }
    }

    /**
     * 后台路由排序
     * @return false|string
     */
    public function updateAdminRouterSort(Request $request)
    {
        $sort = $request->input('sort','');
        try {
            foreach ($sort as $key=>$value) {
                $this->_adminRouterModel->updateAdminRouter((int)$value, array('sort'=>$key));
            }
            return $this->json(20000, '排序成功');
        } catch (\Exception $e) {
            return $this->json(50000,'排序失败');
        }

    }

    /**
     * 获取路由树
     * @return false|string
     */
    public function getAdminRouterTree()
    {
        $data = $this->_adminRouterModel->getAdminRouterTree();
        return $this->json(20000,'获取成功',$data);
    }

    /**
     * 获取单条路由
     * @param int $id
     * @return false|string
     */
    public function getAdminRouter(Request $request)
    {
        $id = $request->input('id',0);
        if (!is_int($id) && $id <= 0) {
            return $this->json(40000,'ID参数错误');
        }
        $data  = $this->_adminRouterModel->getAdminRouter(array('admin_router_id'=>$id));
        return $this->json(20000,'获取成功',$data);
    }

}
