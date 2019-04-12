<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:63:"E:\www\apiadmin\public/../application/index\view\index\add.html";i:1544779603;}*/ ?>
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
    <link rel="stylesheet" href="/static/webex/css/ments.css">
    <!--插件css样式-->
    <link rel="stylesheet" href="/static/webex/css/rolldate.css">

</head>
<body>
    <div class="ments">
        <form name="myform">
            <!--会议名称-->
            <div class="shuru">
                <input type="text" placeholder="会议名称" class="mingcheng" name="mingcheng"  data-foolish-msg="请输入会议名称">
                <i class="glyphicon glyphicon-user"></i>
            </div>
            <!--开始日期-->
            <div class="shuru">
                <input type="text" id="demo1" placeholder="开始日期" readonly name="date">
                <i class="glyphicon glyphicon-time"></i>
            </div>
            <!--开始时间-->
            <div class="shuru">
                <input type="text" id="demo2" placeholder="开始时间" readonly name="shijian" value="">
                <i class="glyphicon glyphicon-time"></i>
            </div>
            <!--持续时间-->
            <div class="shuru">
                <input type="text" id="demo3" placeholder="持续时间" readonly name="long" value="">
                <i class="glyphicon glyphicon-time"></i>
            </div>
            <!--提交按钮-->
            <input type="button" class="tijiao" value="提交">
        </form>
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
        $('.tijiao').click(function(){
            
            
            
           
            // 会议名称
           if(myform.mingcheng.value.length < 1){
               layer.open({
                   content: '请输入会议名称'
                   ,skin: 'msg'
                   ,time: 2 //2秒后自动关闭
                   // 弹出层高度 下边距
                   ,style:'margin-bottom:200px'
               });
               $('.mingcheng').focus();
               return false;
           }else  if(myform.date.value == ""){
                layer.open({
                    content: '请选择开始日期'
                    ,skin: 'msg'
                    ,time: 2 //2秒后自动关闭
                    // 弹出层高度 下边距
                    ,style:'margin-bottom:200px'
                });
                return false;
            }else  if(myform.shijian.value == ""){
                layer.open({
                    content: '请选择开始时间'
                    ,skin: 'msg'
                    ,time: 2 //2秒后自动关闭
                    // 弹出层高度 下边距
                    ,style:'margin-bottom:200px'
                });
                return false;
            }else if(myform.long.value == ""){
                layer.open({
                    content: '请选择持续时间'
                    ,skin: 'msg'
                    ,time: 2 //2秒后自动关闭
                    // 弹出层高度 下边距
                    ,style:'margin-bottom:200px'
                });
                return false;
            }
            if(!validTime(myform.date.value,myform.shijian.value)){
                        return false;
                    }
            
            var meetingStart=myform.date.value+ ''+myform.shijian.value;  //开始时间
            
            var arr=myform.long.value.split(":");  //分割小时和分钟
            
            var meetduration=parseInt(parseInt(arr[0])*60+parseInt(arr[1]));  //持续时间
            
            var meetingName=myform.mingcheng.value;
            $(".al").css('display','block');return;
            $.ajax({
                        type:"POST",
                        url:"/webex_create",
                        dataType: "json",
                        data:{"meetingStart":key,"meetduration":meetduration,"meetingName":meetingName},
                        success: function(data){
                            if(data.code == -1){
                            layer.open({
                                    content: data.msg
                                    ,skin: 'msg'
                                    ,time: 2 //2秒后自动关闭
                                });
                            }
                            if(data.code == -7){
                                
                                window.location.href="<?php echo url('/webex_login'); ?>";
                            }
                        }
            });
        });
    </script>
    <script>
        
        //判断时间选择的合法性
       function validTime(date,day){
           
                    var d = new Date(),   
                    d1 = new Date(date+' '+day);
                    
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
                 var  d2 = new Date(d.getFullYear()+'/'+(d.getMonth()+1)+'/'+d.getDate()+' '+now_hour+':'+selecmin);//如果非'YYYY-MM-DD'格式，需要另做调整 
                 if(d1 < d2){
                    layer.open({
                    content: '请选择五分钟之后的时间'
                    ,skin: 'msg'
                    ,time: 2 //2秒后自动关闭
                    // 弹出层高度 下边距
                    ,style:'margin-bottom:200px'
                     });
                    
                    return false;
                }else{
                    return true;
                }
       }
        // 选择日期 年 + 月 + 日
        new rolldate.Date({
            el:'#demo1',
            format:'YYYY-MM-DD',
            theme:'blue',
            beginYear:new Date().getFullYear(),   //今天的年份
            endYear:new Date(new Date().setDate(new Date().getDate()+7)).getFullYear(),   //7天后的年份
            beginMonth:new Date().getMonth()+1,   //今天的月份
            endMonth:new Date(new Date().setDate(new Date().getDate()+7)).getMonth()+1,   //七天后的月份
            confirmBefore:function(el,date){
               
                var d = new Date(),
                    d1 = new Date(date.replace(/\-/g, "\/")),
                    d2 = new Date(d.getFullYear()+'/'+(d.getMonth()+1)+'/'+d.getDate());//如果非'YYYY-MM-DD'格式，需要另做调整

                if(d1 < d2){
                    layer.open({
                    content: '不能小于当前的日期'
                    ,skin: 'msg'
                    ,time: 2 //2秒后自动关闭
                    // 弹出层高度 下边距
                    ,style:'margin-bottom:200px'
                     });
//                    layer.alert('不能小于当前的日期');
                    return false;
                }
            }
        });
        // 开始时间 时 + 分
        new rolldate.Date({
            el:'#demo2',
            format:'hh:mm',
            theme:'blue',
            minStep:5,
            tapBefore: function(el) {
                
                if(myform.date.value == ""){
                     layer.open({
                        content: '请先选择日期'
                        ,skin: 'msg'
                        ,time: 2 //2秒后自动关闭
                        // 弹出层高度 下边距
                        ,style:'margin-bottom:200px'
                         });
                    return false;
                }else{
                    
                
                    //获取当前的分钟数
                    var d=new Date();
                    var now_min=d.getMinutes();
                    var now_hour=d.getHours();
                    var num=now_min%5;  //余数
                    
                    if(num<3){ 
                        var selecmin=now_min-num+5;   //选中分钟数
                       
                     }else{
                        var selecmin=now_min-num+10;   //选中分钟数
                     }
                     if(selecmin>=60){      //超过60分钟以上则下一个小时
                            this.config.selectHour=now_hour+1;
                            this.config.selectMin=selecmin-60; 
                        }else{
                            this.config.selectMin=selecmin;
                     }
                    
                    
                }
            },
            confirmBefore:function(el,date){
                
                    if(!validTime(myform.date.value,date)){
                        return false;
                    }
               
            }
            // tapBefore: function(el) {
            //     console.log('插件开始触发'); // 触发插件之前得事件  判断时间大于三分钟 小于三分钟  // el 绑定dom元素   return false 可以阻止插件运行
            //     var myDate = new Date();  // 标准时间
            //     var mytime=myDate.toLocaleTimeString();     //获取当前时间
            //     // alert(Date.now()+5*60*1000);   alert一下当前时间的下一个五分钟
            //     if(mytime > (Date.now()+3*60*1000)){   // 判断 如果当前得时间大于三分钟
            //         // alert('当前时间超过了三分钟');    //  如果超过了三分钟 就显示下下一个五分钟 把时 + 分插入到 插件的时 + 分上
            //     }else{
            //         // alert('当前时间没超过三分钟');  //   如果没超过 就显示下一个五分钟 在把时间分钟更改
            //     }
            // },
            // confirmBefore: function(el, date) {  // 确定按钮触发事件
            //     alert("你选中的开始时间为" + date);  // 选中插件上的时间 小时 + 分钟数
            //     console.log('确定按钮触发');  // 确定按钮 还是时间问题 大于三分钟 小于三分钟 如果不好强制改变时间值  // el 为要选中的元素 date 为选中日期
            //     // 点击确定如果时间不对 强制把判断后的时间赋值给input
            //     if(date < Date.now()){    // 如果选中的时间小于当前的时间
            //         // 如果选择的时间小于准确的时间 点击确定之后 根据当前时间的时间差自动选择下一个正确的预约时间
            //         // return document.getElementById("demo2").value = (Date.now()+5*60*1000);
            //         return false;
            //     }
            // }
        });
        // 持续时间 时 + 分
        new rolldate.Date({
            el:'#demo3',
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


        // 点击按钮出现加载按钮
        // $('.tijiao').click(function(){
        //     $('.al').css('display','block');
        // });
    </script>
</body>
</html>