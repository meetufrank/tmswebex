<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:65:"E:\www\apiadmin\public/../application/index\view\index\login.html";i:1544783691;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>webex登陆</title>
    <link rel="stylesheet" href="/static/webex/bootstrap-3.3.7-dist/bootstrap-3.3.7-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/webex/css/login.css">
    <link rel="stylesheet" href="/static/webex/css/jquery.slider.css">
    
    <!--基础样式-->
    <style>
        .ui-slider-wrap{
            background: #eee !important;
            margin: 0 auto !important;
        }
        .ui-slider-bg{
            background: #09bb07 !important;
        }
        .ui-slider-text{
            color: #fff !important;
        }
        /*图动滑块验证*/
        .ui-slider-wrap{
            width: 250px !important;
        }
    </style>
</head>
<body>
    <div class="form">
        <!--头部logo-->
        <div class="logo">
            <img src="/static/webex/img/logo.png" class="img-responsive">
        </div>
        <!--登陆验证-->
        <div class="input">
            <!--站点名称-->
            <div class="com">
                <i class="	glyphicon glyphicon-globe"></i>
                <input type="text" placeholder="请输入站点名称" class="write" id="write" data-foolish-msg="请输入站点名称" value="<?php echo $re_siteName; ?>">
                <label>.webex.com.cn</label>
            </div>
            <!--用户名-->
            <div class="user">
                <i class="glyphicon glyphicon-user"></i>
                <input type="text" placeholder="请输入账号" class="username" id="name" data-foolish-msg="请输入您的账号" value="<?php echo $re_webExId; ?>">
            </div>
            <!--密码-->
            <div class="pwd">
                <i class="	glyphicon glyphicon-lock"></i>
                <input type="password" placeholder="请输入密码" class="userpwd" id="pwd" data-foolish-msg="请输入您的密码" value="<?php echo $re_password; ?>">
                <i class="	glyphicon glyphicon-eye-open"></i>
                <i class=" glyphicon glyphicon-eye-close"></i>
            </div>
        </div>
        <!--滑块验证-->
        <div class="sliderBox">
            <div id="slider2" class="slider"  data-foolish-msg="请拖动滑块验证">
                
                <!--<div id="slider_bg"></div>
                <span id="label"><i class="rightRow"></i></span>
                <span id="labelTip">拖动滑块验证</span>-->
            </div>
            <div id="hkform" style="display:none;"></div>
        </div>
        <!--记住账号密码-->
        <div class="remmber">
            <input type="checkbox"  id="remember"  >
            <label>记住账号密码</label>
        </div>
        <!--登录按钮-->
        <div class="land">
            <button class=" matter-button" type="submit">登陆</button>
        </div>
    </div>
    <!--底部logo-->
    <div class="footer">
        <img src="/static/webex/img/footer.png" class="img-responsive">
    </div>
    <script src="/static/webex/js/jquery.min.js"></script>
    <script src="/static/webex/js/jquery-1.12.1.min.js"></script>
    <script src="/static/webex/js/jquery.slider.min.js"></script>
    <script src="/static/webex/js/login.js"></script>
    <script src="/static/webex/js/huakuai.js"></script>
    <script src="/static/webex/layer.mobile-v2.0/layer.mobile-v2.0/layer_mobile/layer.js"></script>
    <!-- 验证 -->
    <script>
       
            function checkUserName(username){
                if ((/^[\u4E00-\u9FA5A-Za-z]+$/.test(username))) {
                    return true;
                }else{
                    return false;
                }
            }
            function checkPwd(str) {
                if(str.length==0){
                    return false;
                }else{
                    return true;
                }
//                if ((/^[0-9a-zA-Z!@#$^]{6,18}$/.test(str))) { return true; } else { return false; }
            }

         
            function checkSlider(str){
                
                if(str == 'true'){ return true; } else { return false; }
             }
           
            $('.matter-button').on('click', function(){
                var requestData = {
                    matterconter:$('.write').val(),
                    username:$('.username').val(),
                    userpwd:$('.userpwd').val(),
                    remember:$('#remember').val(),
                    hkform:$('#hkform').text()

                }
                var valid = true;
                if(valid && requestData.matterconter.length <=0 ){ valid=false; var tips=$ ( ".write").attr( "data-foolish-msg"); $( ".write").focus(); }
                if(valid && !checkUserName(requestData.username) ){ valid=false; var tips=$ ( ".username").attr( "data-foolish-msg"); $( ".username").focus(); }
                if(valid && !checkPwd(requestData.userpwd)) { valid=false; var tips=$ ( ".userpwd").attr( "data-foolish-msg"); $( ".userpwd").focus(); }
                if(valid && !checkSlider(requestData.hkform)){ valid=false; var tips=$ ( ".slider").attr( "data-foolish-msg");}


                // if(valid && !checkMobile(requestData.userpwd)){ var tips=$ ( ".userpwd").attr( "data-foolish-msg"); $( ".userpwd").focus(); valid=false; }

                //问题
                var UserName = requestData.username;		//账号
                var UserPwd = requestData.userpwd;		//密码
                var Matterconter = requestData.matterconter;		//站点
                var remember = requestData.remember;
                if(valid){
                    $.ajax({
                        type:"POST",
                        url:"<?php echo url('/webex_postlogin'); ?>",
                        dataType: "json",
                        data:{"webExId":UserName,"password":UserPwd,"siteName":Matterconter,"remember":remember},
                        success: function(data){
                            layer.open({
                                    content: data.msg
                                    ,skin: 'msg'
                                    ,time: 2 //2秒后自动关闭
                                });
                            if(data.code == -7){
                                
                                window.location.href="<?php echo url('/webex_login'); ?>";
                            }else if(data.code == 1){
                                window.location.href="<?php echo url('/webex_list'); ?>";
                            }
                        }
                    });
                }else{

                    layer.open({
                        content: tips
                        ,skin: 'msg'
                        ,time: 2 //2秒后自动关闭
                        // 弹出层高度 下边距
                        ,style:'margin-bottom:200px'
                    });
                }
            });
    
    </script>
</body>
</html>