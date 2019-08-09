<?php

use think\Route;

Route::miss('Index/mcu_meeting/login');
// webex 别名路由到 index/Index 控制器

//Route::rule('webex_login','index/index/login');
//Route::rule('webex_postlogin','index/index/postLogin');
//Route::rule('webex_list','index/index/webex_list');
//Route::rule('webex_info','index/index/webex_content');
//Route::rule('webex_delete','index/index/delete');
//Route::rule('webex_create','index/index/webex_add');
//Route::rule('webex_begin','index/index/begin');



Route::rule('webex_login','index/mcu_meeting/login');
Route::rule('meeting_postlogin','index/mcu_meeting/postLogin');
Route::rule('meeting_list','index/mcu_meeting/webex_list');
Route::rule('meeting_info','index/mcu_meeting/webex_content');
Route::rule('meeting_delete','index/mcu_meeting/delete');
Route::rule('meeting_create','index/mcu_meeting/webex_add');
Route::rule('meeting_begin','index/mcu_meeting/begin');

//测试链接
//Route::alias('ceshi','index/Ceshi/ceshi');

