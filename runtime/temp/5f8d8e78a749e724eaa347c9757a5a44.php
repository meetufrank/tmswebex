<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:65:"D:\www\apiadmin\public/../application/wiki\view\index\detail.html";i:1525774918;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo config('apiAdmin.APP_NAME'); ?> - 在线接口列表</title>
    <link href="https://cdn.bootcss.com/semantic-ui/2.2.11/semantic.css" rel="stylesheet">
    <script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdn.bootcss.com/semantic-ui/2.2.11/components/tab.min.js"></script>
    <link rel="stylesheet" href="/static/jsonFormater/jsonFormater.css">
    <script type="text/javascript" src="/static/jsonFormater/jsonFormater.js"></script>
</head>
<body>
<br />
<div class="ui container">
    <div class="ui segment">
        <div class="ui items">
            <div class="item">
                <div class="image">
                    <img src="<?php echo !empty($groupInfo['image'])?$groupInfo['image'] : '/static/defaultImg.jpg'; ?>">
                </div>
                <div class="content">
                    <span class="header"><?php echo $groupInfo['name']; ?></span>
                    <div class="description">
                        <p><?php echo $groupInfo['description']; ?></p>
                    </div>
                    <div class="extra">
                        额外的细节
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; ?>
    <div class="ui teal big message" id="msg">接口访问地址：<?php echo $http_type; ?><?php echo $_SERVER['HTTP_HOST']; ?>/api/<?php echo $hash; ?></div>
    <div class="ui grid">
        <div class="four wide column">
            <div class="ui vertical menu" style="width: 100%;overflow: auto;">
                <?php if(is_array($apiList) || $apiList instanceof \think\Collection || $apiList instanceof \think\Paginator): $i = 0; $__LIST__ = $apiList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <a class="<?php if($hash == $vo['hash']): ?>active<?php endif; ?> teal item" href="<?php echo url('/wiki/detail/'.$groupHash.'/'.$vo["hash"]); ?>">
                    <?php echo substr($vo['hash'], 0, 11); ?>...（<?php echo mb_substr($vo['info'], 0, 3); ?>...）
                    <div class="ui <?php if($hash == $vo['hash']): ?>teal<?php endif; ?> label"> &gt; </div>
                </a>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        </div>
        <div class="twelve wide column">
            <div class="ui floating message" id="detail" style="overflow: auto;">
                <h2 class='ui header'>接口唯一标识：<a target="_blank" href="<?php echo $http_type; ?><?php echo $_SERVER['HTTP_HOST']; ?>/api/<?php echo $hash; ?>"><?php echo $hash; ?></a><?php if($detail['isTest'] == 1): ?>（<?php echo $detail['apiClass']; ?>）<?php endif; ?></h2><br />
                <div class="ui raised segment">
                    <span class="ui red ribbon large label">接口说明</span>
                    <?php if($detail['status'] == 0): ?>
                        <span class='ui red label large'><i class="usb icon"></i>禁用</span>
                        <?php else: if($detail['isTest'] == 1): ?>
                            <span class='ui teal label large'><i class="usb icon"></i>测试</span>
                            <?php else: ?>
                            <span class='ui green label large'><i class="usb icon"></i>启用</span>
                        <?php endif; endif; ?>
                    <span class="ui teal label large"><i class="certificate icon"></i><?php echo config('apiAdmin.APP_VERSION'); ?></span>
                    <span class="ui blue large label"><i class="chrome icon"></i>
                    <?php switch($detail['method']): case "1": ?>POST<?php break; case "2": ?>GET<?php break; default: ?>不限
                    <?php endswitch; ?>
                    </span>
                    <div class="ui message">
                        <p><?php echo $detail['info']; ?></p>
                    </div>
                </div>
                <div class="ui pointing large blue three item menu">
                    <a class="item active" data-tab="first">请求参数</a>
                    <a class="item" data-tab="second">返回参数</a>
                    <a class="item" data-tab="third">返回示例</a>
                </div>
                <div class="ui tab segment active" data-tab="first">
                    <h3>公共请求参数</h3>
                    <table class="ui orange celled striped table" >
                        <thead>
                        <tr><th>参数名字</th><th>类型</th><th width="70">是否必须</th><th>默认值</th><th width="30%">其他</th><th>说明</th></tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>version</td>
                            <td>String</td>
                            <td><span class="ui green label">必填</span></td>
                            <td><?php echo config('apiAdmin.APP_VERSION'); ?></td>
                            <td></td>
                            <td>API版本号【请在Header头里面传递】</td>
                        </tr>
                        <tr>
                            <td>access-token</td>
                            <td>String</td>
                            <td><?php echo $detail['accessToken']==1?'<span class="ui green label">必填</span>':'<span class="ui red label">勿填</span>'; ?></td>
                            <td></td>
                            <td></td>
                            <td>APP认证秘钥【请在Header头里面传递】</td>
                        </tr>
                        <tr>
                            <td>user-token</td>
                            <td>String</td>
                            <td><?php echo $detail['needLogin']==1?'<span class="ui green label">必填</span>':'<span class="ui red label">勿填</span>'; ?></td>
                            <td></td>
                            <td></td>
                            <td>用户认证秘钥【请在Header头里面传递】</td>
                        </tr>
                        </tbody>
                    </table>
                    <h3>请求参数</h3>
                    <table class="ui red celled striped table" >
                        <thead>
                        <tr><th>参数名字</th><th>类型</th><th width="70">是否必须</th><th>默认值</th><th>其他</th><th>说明</th></tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($request) || $request instanceof \think\Collection || $request instanceof \think\Paginator): $i = 0; $__LIST__ = $request;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['fieldName']; ?></td>
                                <td><?php echo $dataType[$vo['dataType']]; ?></td>
                                <td><?php echo $vo['isMust']==1?'<span class="ui green label">必填</span>':'<span class="ui teal label">可选</span>'; ?></td>
                                <td><?php echo $vo['default']; ?></td>
                                <td><?php echo $vo['range']; ?></td>
                                <td><?php echo $vo['info']; ?></td>
                            </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="ui tab segment" data-tab="second">
                    <h3>公共返回参数</h3>
                    <table class="ui olive celled striped table" >
                        <thead>
                        <tr><th>返回字段</th><th>类型</th><th>说明</th></tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>code</td>
                            <td>Integer</td>
                            <td>返回码，详情请参阅<a href="<?php echo url('/errorCode'); ?>">错误码说明</a></td>
                        </tr>
                        <tr>
                            <td>msg</td>
                            <td>String</td>
                            <td>错误描述，当请求成功时可能为空</td>
                        </tr>
                        <tr>
                            <td>debug</td>
                            <td>String</td>
                            <td>调试字段，如果没有调试信息会没有此字段</td>
                        </tr>
                        </tbody>
                    </table>
                    <h3>返回参数</h3>
                    <table class="ui green celled striped table" >
                        <thead>
                        <tr><th>返回字段</th><th>类型</th><th>说明</th></tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($response) || $response instanceof \think\Collection || $response instanceof \think\Paginator): $i = 0; $__LIST__ = $response;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['showName']; ?></td>
                                <td><?php echo $dataType[$vo['dataType']]; ?></td>
                                <td><?php echo $vo['info']; ?></td>
                            </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="ui tab segment" data-tab="third">
                    <pre id="json" style='font-family: Arial;'></pre>
                </div>
                <div class="ui blue message">
                    <strong>温馨提示：</strong> 此接口参数列表根据后台代码自动生成，如有疑问请咨询后端开发
                </div>
                <p>&copy; Powered  By <a href="http://www.apiadmin.org/" target="_blank"><?php echo config('apiAdmin.APP_NAME'); ?> <?php echo config('apiAdmin.APP_VERSION'); ?></a> <p>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    $('.pointing.menu .item').tab();
    $(document).ready(function () {
        var s = function () {
            var options = {
                dom: '#json',
                isCollapsible: true,
                quoteKeys: true,
                tabSize: 2,
                imgCollapsed: "/static/jsonFormater/Collapsed.gif",
                imgExpanded: "/static/jsonFormater/Expanded.gif"
            };
            window.jf = new JsonFormater(options);
            jf.doFormat(<?php echo $detail["returnStr"]; ?>);
        }();
    });
    $('.ui .vertical').css('max-height', $('#detail').outerHeight(true));
</script>
</html>
