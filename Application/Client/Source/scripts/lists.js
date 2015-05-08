$(function() {
	// var loginFlag=false;//登录状态的切换
	var date = new Date();

	var nowHours = date.getHours();


	// 当不接受订单是页面的样式
	if($(".listHall").hasClass("gray")){
		$(".gray").find(".accept").css({"background":"rgb(200,200,200)","width":"80px"}).text("暂不接受订单");
	}

    // 点击某个餐厅页面的切换
	$(".hallHref").click(function(event) {
			event.preventDefault();
			$(this).parent("form").submit();
	})


	// 点击展开登录界面
	$("#menu,.login-panel").mouseover(function() {
		$(this).css("cursor", "pointer");

	}).click(function() {
		if($(".login-panel").is(":hidden")){

			$(".listHall").css("z-index","-1");
			$("body").css("overflow-y","hidden");
			$(".backDivstyle").remove();
			var $backDiv=$("<div></div>").addClass("backDivstyle");
			$(".login-panel").append($backDiv);
			$(".login-panel").fadeIn();
			// loginFlag=false;
		}
		else{
			// loginFlag=true;
			$(".listHall").css("z-index","0");
			$(".login-panel").fadeOut(0).parents("body").css("overflow-y","auto");
			
		}
	
	});




})