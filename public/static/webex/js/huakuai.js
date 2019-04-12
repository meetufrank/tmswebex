$(function() {
	/*var gObj = {};
	gObj.flag = false;
	var slider = new SliderUnlock("#slider", {
		successLabelTip: "验证通过"
	}, function() {
		$(document).off("mouseup")
		$('#label .rightRow').css({
			background:'url(../img/php.fw.png) no-repeat 0 -180px',
			width: '24px',
			height: '17px',
			left: '10px',
			top: '12px'
		});
		$("#labelTip").css({
			color:	'#4FE299'
		})
		gObj.flag = true;
	});
	slider.init();*/
	
	$("#slider2").slider({
		width: 250, // width
		height: 40, // height
		sliderBg: "#222730", // 滑块背景颜色
		color: "#333", // 文字颜色
		fontSize: 14, // 文字大小
		bgColor: "rgba(78,226,153,0.40)", // 背景颜色
		textMsg: "拖动滑块验证", // 提示文字
		successMsg: "验证通过", // 验证成功提示文字
		successColor: "rgb(79, 226, 153)", // 滑块验证成功提示文字颜色
		time: 400, // 返回时间
		callback: function(result) { // 回调函数，true(成功),false(失败)
			$("#hkform").text(result);
		}
	});
	$('.ui-slider-btn').append('<i class="rightRow"></i>');
})