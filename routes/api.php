<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['namespace' => 'AdminApi','middleware'=>'admin_api'], function () {
    // 用户信息
    Route::get('/user/info','AdminUserController@info');
    // 用户上传头像
    Route::post('/user/upload-avatar','AdminUserController@uploadAvatar');

    // 系统管理
    Route::prefix('system')->group(function () {

        // 管理员
        Route::prefix('user')->group(function () {
            Route::post('create-adminuser','AdminUserController@createAdminUser');
            Route::post('update-adminuser-active','AdminUserController@updateAdminUserActive');
            Route::post('update-adminuser-role','AdminUserController@updateAdminUserRole');
            Route::post('update-adminuser-password','AdminUserController@updateAdminUserPassword');
            Route::post('update-user-password','AdminUserController@updateUserPassword');
            Route::get('adminuser-list','AdminUserController@getAdminUserList');
        });

        // 路由管理
        Route::prefix('router')->group(function () {
            Route::get('router-tree','AdminRouterController@getAdminRouterTree');
            Route::get('get-admin-router','AdminRouterController@getAdminRouter');
            Route::post('create-router','AdminRouterController@createAdminRouter');
            Route::post('update-router','AdminRouterController@updateAdminRouter');
            Route::post('delete-router','AdminRouterController@deleteAdminRouter');
            Route::post('update-router-sort','AdminRouterController@updateAdminRouterSort');
        });

        // 按钮管理
        Route::prefix('button')->group(function () {
            Route::get('button-list','SystemButtonController@getSystemButtonList');
            Route::get('button-all','SystemButtonController@getSystemButtonAll');
            Route::post('create-button','SystemButtonController@createSystemButton');
            Route::post('update-button','SystemButtonController@updateSystemButton');
            Route::post('delete-button','SystemButtonController@deleteSystemButton');
            Route::post('update-button-enable','SystemButtonController@updateSystemButtonEnable');
        });

        // 权限列表
        Route::get('/auth-list','AuthController@getAuthList');

        // 角色管理
        Route::prefix('role')->group(function () {
            Route::get('get-role','RoleController@getRole');
            Route::get('role-list','RoleController@getRoleList');
            Route::post('create-role','RoleController@createRole');
            Route::post('update-role','RoleController@updateRole');
            Route::post('delete-role','RoleController@deleteRole');
        });
    });
});
Route::group(['namespace' => 'AdminApi'], function () {
    Route::post('/user/login','AdminUserController@login');
});
