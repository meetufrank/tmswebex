<?php
/**
 * Api路由
 */
use think\Route;

Route::miss('api/Index/index');
$afterBehavior = ['\app\api\behavior\ApiAuth', '\app\api\behavior\ApiPermission', '\app\api\behavior\RequestFilter'];
Route::rule('api/5b14e69e846af','api/Index/index', 'GET', ['after_behavior' => $afterBehavior]);Route::rule('api/5b14eff803992','api/BuildToken/getAccessToken', 'GET', ['after_behavior' => $afterBehavior]);