<?php
/**
 * Created by PhpStorm.
 * User: oray
 * Date: 2019/4/28
 * Time: 15:33
 */

namespace App\Http\Controllers\AdminApi;


use App\Models\AdminUser as AdminUserModel;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class AdminApiController extends Controller
{
    public function json($code=200, $msg='', $data=array())
    {
        if (request()->isRefreshToken) {
            $userId = request()->userId;
            // 刷新token
            $adminUser = AdminUserModel::where('admin_user_id', $userId)->first();
            // 请求验证token
            $startTime  = time();
            $ExpireTime = time()+7200;
            $userId     = $adminUser->admin_user_id;
            $userName   = $adminUser->username;
            $accessToken = \App\Http\Common\Tools::generateJwt($startTime, $ExpireTime, $userId, $userName);
            $data['access_token'] = $accessToken;
            $oldAccessToken  = request()->header('access-token');
            Cache::store('redis')->set($oldAccessToken,true, 20);
        }
        $json = array(
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        );
        return json_encode($json, true);
    }
}
