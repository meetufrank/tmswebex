<?php
/**
 * Api路由
 */
use think\Route;

//Route::miss('api/Index/index');
$afterBehavior = ['\app\api\behavior\ApiAuth', '\app\api\behavior\ApiPermission', '\app\api\behavior\RequestFilter'];
Route::rule('api/5b19f05dcc1e6','api/Webex/GetUser', 'POST', ['after_behavior' => $afterBehavior]);Route::rule('api/5b19f193cc234','api/BuildToken/getAccessToken', 'POST', ['after_behavior' => $afterBehavior]);Route::rule('api/5b1a190c62cb9','api/Webex/tmsCreatMeet', 'POST', ['after_behavior' => $afterBehavior]);Route::rule('api/5b3f39163b90a','api/Webex/getSessionTicket', 'POST', ['after_behavior' => $afterBehavior]);Route::rule('api/5b432334b00c9','api/Webex/WebexLogin', 'POST', ['after_behavior' => $afterBehavior]);Route::rule('api/5b4423d585225','api/Webex/GetClientID', 'POST', ['after_behavior' => $afterBehavior]);Route::rule('api/5b47103ab2ae5','api/Webex/tmsGetMeetingList', 'POST', ['after_behavior' => $afterBehavior]);Route::rule('api/5b4d716111a59','api/Webex/tmsGetMeetingInfo', 'POST', ['after_behavior' => $afterBehavior]);Route::rule('api/5b4edb4f60ff3','api/Webex/deleteMeeting', 'POST', ['after_behavior' => $afterBehavior]);Route::rule('api/5b56cf04251a3','api/Ceshi/ceshi', 'GET', ['after_behavior' => $afterBehavior]);

Route::rule('old_webex','api/index/index');