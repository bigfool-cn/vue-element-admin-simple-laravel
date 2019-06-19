<?php
/**
 * Created by PhpStorm.
 * User: JS_chen
 * Date: 2019/6/15
 * Time: 1:19
 */

namespace App\Http\Controllers\AdminApi;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends AdminApiController
{
    public function getAuthList(Request $request)
    {
        $params = $request->all();
        $page   = 1;
        $row    = 20;
        $condition  = '1=1';
        $this->_makeCondition($params, $condition, $page, $row);
        try {
            $routerFields = 'admin_router_id AS id,title,create_time,update_time,"路由" AS type';
            $router = DB::table('admin_router')->select(DB::raw($routerFields));
            $buttonFileds = 'button_id AS id,title,create_time,update_time,"按钮" AS type';
            $button = DB::table('system_button')->where('is_enable',1)
                ->select(DB::raw($buttonFileds))->union($router);
            $auth = $button->toSql();
            $paginate = DB::table(DB::raw("({$auth}) as a"))->mergeBindings($button)->whereRaw($condition)
                ->orderBy('create_time', 'DESC')->paginate($row);
            $auths = $paginate->all();
            $pages = array(
                'current_page' => (int) $paginate->currentPage(),
                'last_page'    => (int) $paginate->lastPage(),
                'per_page'     => (int) $paginate->perPage(),
                'total'        => (int) $paginate->total(),
            );
            $data = array(
                'pages' => $pages,
                'auths' => $auths
            );
            return $this->json(20000, '获取成功', $data);
        } catch (\Exception $e) {
            return $this->json(50000, $e->getMessage());
        }
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

        // 权限名称
        if (isset($params['title']) && !empty($params['title'])) {
            $condition .= " AND title LIKE '%{$params['title']}%'";
        }

        // 权限类型
        if (isset($params['type']) && in_array($params['type'],['路由', '按钮'])) {
            $condition && $condition .= ' AND ';
            $condition .= "type = '{$params['type']}'";
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
