<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:65:"E:\www\apiadmin\public/../application/index\view\index\begin.html";i:1544783343;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>WebEx</title>
    <link rel="icon" type="image/x-icon" href="/static/webex/img/logo.png" />
    <link rel="stylesheet" href="/static/webex/bootstrap-3.3.7-dist/bootstrap-3.3.7-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/webex/css/begin.css">
    <link rel="stylesheet" href="/static/webex/css/rolldate.css">
    <style>

    </style>
</head>
<body>
    <div class="begin">
        <div class="content">
            <!--开始时间-->
            <div class="start">
                <span>开始时间</span>
                <span id="nowtime">2018-12-3 16:30</span>
            </div>
            <!--持续时间输入框-->
            <div class="shuru">
                <form name="myform">
                    <input type="text" id="demo4" placeholder="持续时间" readonly name="long" data-foolish-msg="请输入持续时间">
                    <i class="glyphicon glyphicon-time"></i>
                </form>

            </div>
            <!--中间内容-->
            <!--提交按钮-->
            <button class="tijiao" type="submit">提交</button>
        </div>
    </div>
 <!--loading 加载-->
    <div class="al" style="display: none;">
        <div class="load">
            <img src="/static/webex/img/loading.gif">
            <p>Loading...</p>
        </div>
        <div class="outer"></div>
    </div>
    <script src="/static/webex/js/jquery.min.js"></script>
    <script src="/static/webex/js/rolldate.js"></script>
    <script src="/static/webex/layer.mobile-v2.0/layer.mobile-v2.0/layer_mobile/layer.js"></script>
     <script>
         function getdate(){
              var d = new Date(); 
                    
            var now_min=d.getMinutes();
            var now_hour=d.getHours();
            var num=now_min%5;  //余数

            if(num<3){ 
                var selecmin=now_min-num+5;   //选中分钟数

             }else{
                var selecmin=now_min-num+10;   //选中分钟数
             }
             if(selecmin>=60){      //超过60分钟以上则下一个小时
                    now_hour=now_hour+1;
                    selecmin=selecmin-60; 
                }
            var  meetingStart = d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate()+' '+now_hour+':'+selecmin;//如果非'YYYY-MM-DD'格式，需要另做调整
            
            return meetingStart;
         }
         
         $(function(){
             var time=getdate();
             $("#nowtime").text(time);
             
         });
        $('.tijiao').click(function(){
            
            
            
           if(myform.long.value == ""){
                layer.open({
                    content: '请选择持续时间'
                    ,skin: 'msg'
                    ,time: 2 //2秒后自动关闭
                    // 弹出层高度 下边距
                    ,style:'margin-bottom:200px'
                });
                return false;
            }
            
            
            var meetingStart=getdate(); //开始时间
            
            var arr=myform.long.value.split(":");  //分割小时和分钟
            
            var meetduration=parseInt(parseInt(arr[0])*60+parseInt(arr[1]));  //持续时间
             
          
            $(".al").css('display','block');
            $(".tijiao").attr('disabled',true);  
            $.ajax({
                        type:"POST",
                        url:"/webex_create",
                        dataType: "json",
                        data:{"meetingStart":meetingStart,"meetduration":meetduration},
                        success: function(data){
                            $(".al").css('display','none');
                            $(".tijiao").attr('disabled',false);
                            if(data.code == -1){
                            layer.open({
                                    content: data.msg
                                    ,skin: 'msg'
                                    ,time: 2 //2秒后自动关闭
                                });
                                return false;
                            }
                            if(data.code == -7){
                                
                                window.location.href="<?php echo url('/webex_login'); ?>";
                            }
                            if(data.code == 1){
                                if(data.data.meetingkey){
                                    window.location.href="/webex_info/meetid/"+data.data.meetingkey;
                                }else{
                                    window.location.href="<?php echo url('/webex_list'); ?>";
                                }
                                 
                            }
                            
                        },
                        error:function(){
                            layer.open({
                                    content: '服务器连接错误'
                                    ,skin: 'msg'
                                    ,time: 2 //2秒后自动关闭
                                });
                                $(".al").css('display','none');
                            $(".tijiao").attr('disabled',false);
                            
                             
                        }
            });
        });
    </script>
    <script>
         // 持续时间 时 + 分
        new rolldate.Date({
            el:'#demo4',
            format:'hh:mm',
            beginHours:1,
            endHours:23,
            beginMin:00,
            endMin:45,
            theme:'blue',
            minStep:15,
            tapBefore: function(el) {
                if(myform.long.value != ""){    //有值加载选中的值
                    var longv=myform.long.value;
                    var arr=longv.split(":");  //分割小时和分钟
                    
                    
                     this.config.selectHour=arr[0];
                     this.config.selectMin=arr[1];
                }else{  //没值默认
                  this.config.selectHour=1;  
                }
                 
            }
        });
    </script>
</body>
</html>