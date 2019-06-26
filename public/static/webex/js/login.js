// 点击小眼睛更换icon
$(function(){
    $('.glyphicon-eye-close').click(function(){
        $('.glyphicon-eye-open').show();
        $('.glyphicon-eye-close').hide();
        $("#pwd").attr("type","text");
    });
    $('.glyphicon-eye-open').click(function(){
        $('.glyphicon-eye-close').css('display','block');
        $('.glyphicon-eye-open').css('display','none');
        $("#pwd").attr("type","password");
    });
});


