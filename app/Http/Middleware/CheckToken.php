<?php

namespace App\Http\Middleware;


use Closure;
use Firebase\JWT\JWT;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Cache;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $access_token  = $request->header('access-token');
        $request->isRefreshToken = false;
        if (!$access_token) {
            $data = array('code'=>50008,'msg'=>'无效token');
            return response()->json($data);
        }
        try {
            $decode = JWT::decode($access_token, env('JWT_KEY'), array('HS256'));
            if ($decode->iss!=$decode->aud && $decode->iss!=env('APP_URL')) {
                $data = array('code'=>50008,'msg'=>'无效token');
                return response()->json($data);
            }
            $condition = array('admin_user_id'=>$decode->id);
            $adminUser = AdminUser::where($condition)->first();
            if (!$adminUser) {
                $data = array('code'=>50008,'msg'=>'无效token');
                return response()->json($data);
            }

            $time = abs(time() - $decode->exp);
            if ($time <= 60*10) {
                $request->isRefreshToken = true;
            }

            if ((time()-$decode->exp) > 60*10){
                $data = array('code'=>50014,'msg'=>'token已过期');
                return response()->json($data);
            }
            $request->userId = $decode->id;
        } catch (\Exception $e) {
            if (Cache::store('redis')->get($access_token)) {
                return $next($request);
            }
            $data = array('code'=>50008,'msg'=>'无效token');
            if ($e->getMessage() === 'Expired token') {
                $data = array('code'=>50014,'msg'=>'token已过期');
            }
            $data = array('code'=>50008,'msg'=>'无效token');
            return response()->json($data);
        }
        return $next($request);
    }
}
