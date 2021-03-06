<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:64:"D:\www\apiadmin\public/../application/wiki\view\index\index.html";i:1525774918;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo config('apiAdmin.APP_NAME'); ?> - 在线接口文档</title>
    <link href="https://cdn.bootcss.com/semantic-ui/2.2.11/semantic.css" rel="stylesheet">
</head>
<body>
<br />
<div class="ui text container" style="max-width: none !important;">
    <div class="ui floating message">
        <h1 class="ui header"><?php echo config('apiAdmin.APP_NAME'); ?> - 接口文档</h1>
        <a href="<?php echo url('/wiki/errorCode'); ?>">
            <button class="ui red button" style="margin-top: 15px">错误码说明</button>
        </a>
        <a href="<?php echo url('/wiki/calculation'); ?>">
            <button class="ui orange button" style="margin-top: 15px">算法详解</button>
        </a>
        <div class="ui floating message">
            <div class="content">
                <div class="header" style="margin-bottom: 15px">接口状态说明：</div>
                <p><span class='ui teal label'>测试</span> 系统将不过滤任何字段，也不进行AccessToken的认证，但在必要的情况下会进行UserToken的认证！</p>
                <p><span class='ui blue label'>启用</span> 系统将严格过滤请求字段，并且进行全部必要认证！</p>
                <p><span class='ui red label'>禁用</span> 系统将拒绝所有请求，一般应用于危机处理！</p>
            </div>
        </div>
        <div class="ui cards four column">
            <?php if(is_array($appInfo['app_api_show']) || $appInfo['app_api_show'] instanceof \think\Collection || $appInfo['app_api_show'] instanceof \think\Paginator): $i = 0; $__LIST__ = $appInfo['app_api_show'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;
                $apiLength = count($vo);
                 if($apiLength): ?>
                <div class="card column">
                    <a class="image" href="<?php echo url('/wiki/detail/' . $key); ?>">
                        <img src="<?php echo !empty($groupInfo[$key]['image'])?$groupInfo[$key]['image'] : '/static/defaultImg.jpg'; ?>">
                    </a>
                    <div class="content">
                        <a class="header" href="<?php echo url('/wiki/detail/' . $key); ?>">
                            <?php echo $groupInfo[$key]['name']; ?>
                        </a>
                        <a class="meta" href="<?php echo url('/wiki/detail/' . $key); ?>">
                            <span class="group" style="font-size: 0.8em;"><?php echo date('Y-m-d', $groupInfo[$key]['updateTime']); ?></span>
                        </a>
                        <a class="description" style="display: block;font-size: 0.8em;" href="<?php echo url('/wiki/detail/' . $key); ?>">
                            <?php 
                            $len = mb_strlen($groupInfo[$key]['description'], 'utf8');
                            if($len > 31) {
                                echo mb_substr($groupInfo[$key]['description'], 0, 31, 'utf8') . ' ...';
                            } else {
                                echo $groupInfo[$key]['description'];
                            }
                            if ($groupInfo[$key]['hot'] >= 10000) {
                                $hot = sprintf("%.1f", $groupInfo[$key]['hot']/10000) . '万';
                            } else {
                                $hot = $groupInfo[$key]['hot'];
                            }
                             ?>
                        </a>
                    </div>
                    <div class="extra content" style="font-size: 0.9em">
                        <a class="right floated created" href="<?php echo url('/wiki/detail/' . $key); ?>">
                            <i class="fire icon"></i>
                            热度<?php echo $hot; ?>
                        </a>
                        <a class="friends" href="<?php echo url('/wiki/detail/' . $key); ?>">
                            <i class="cubes icon"></i>
                            共<?php echo $apiLength; ?>个接口
                        </a>
                    </div>
                </div>
                <?php endif; endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <p>&copy; Powered  By <a href="http://www.apiadmin.org/" target="_blank"><?php echo config('apiAdmin.APP_NAME'); ?> <?php echo config('apiAdmin.APP_VERSION'); ?></a> <p>
    </div>
</div>
</body>
</html>
