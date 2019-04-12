<?php

use think\Route;

Route::miss('Index/Index/login');
// webex 别名路由到 index/Index 控制器
Route::alias('webex','index/Index');
Route::rule('webex_login','index/index/login');
Route::rule('webex_postlogin','index/index/postLogin');
Route::rule('webex_list','index/index/webex_list');
Route::rule('webex_info','index/index/webex_content');
Route::rule('webex_delete','index/index/delete');
Route::rule('webex_create','index/index/webex_add');
Route::rule('webex_begin','index/index/begin');

