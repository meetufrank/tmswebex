<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:69:"D:\www\apiadmin\public/../application/wiki\view\index\error_code.html";i:1525774918;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo config('apiAdmin.APP_NAME'); ?> - 错误码说明</title>
    <link href="https://cdn.bootcss.com/semantic-ui/2.2.11/semantic.css" rel="stylesheet">
</head>
<body>
<br />
<div class="ui text container" style="max-width: none !important;">
    <div class="ui floating message">
        <h1 class="ui header"><?php echo config('apiAdmin.APP_NAME'); ?> - 错误码说明</h1>
        <a href="<?php echo url('/wiki/index'); ?>">
            <button class="ui green button" style="margin-top: 15px">返回接口文档</button>
        </a>
        <table class="ui red celled striped table">
            <thead>
            <tr>
                <th>#</th><th>英文标识</th><th>错误码</th><th>中文说明</th>
            </tr>
            </thead>
            <tbody>
            <?php if(is_array($codeArr) || $codeArr instanceof \think\Collection || $codeArr instanceof \think\Paginator): $i = 0; $__LIST__ = $codeArr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <tr>
                    <td>
                        <?php echo $i; ?>
                    </td>
                    <td>
                        <?php echo $key; ?>
                    </td>
                    <td>
                        <b><?php echo $vo; ?></b>
                    </td>
                    <td>
                        <?php echo $errorInfo[$vo]; ?>
                    </td>
                </tr>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            </tbody>
        </table>
        <p>&copy; Powered  By <a href="http://www.apiadmin.org/" target="_blank"><?php echo config('apiAdmin.APP_NAME'); ?> <?php echo config('apiAdmin.APP_VERSION'); ?></a> <p>
    </div>
</div>
</body>
</html>
