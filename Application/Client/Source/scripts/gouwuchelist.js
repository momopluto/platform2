$(function() {


	var curRst_info; // 全局变量，用于存放取得的当前餐厅信息cookie数组
	var curRst_cookie_name = "pltf2_curRst_info"; //对应的cookie名

	var order_cookie_name = "pltf2_order_cookie"; //订单信息cookie名

	$(document).ready(function() {
		// 页面加载完毕后，即初始化前端数据**************************************************************
		if ($.cookie(curRst_cookie_name)) {
			// alert($.cookie(curRst_cookie_name));
			curRst_info = JSON.parse($.cookie(curRst_cookie_name)); //初始化curRst_info
			// alert(curRst_info.isOpen);
			// alert(curRst_info.tostring());
			// console.log(curRst_info);
			// console.log(JSON.parse($.cookie(order_cookie_name)));

			if (curRst_info != null) {
				// alert(curRst_info + "curRst_info不空");
				rst_status_judge(); //判断餐厅状态
				// order_cookie_judge();//判断是否已有选单cookie
			}
		}
	});


	// 餐厅状态判断，根据状态，相应展示
	function rst_status_judge() {

		if (curRst_info.isOpen == "1") { //主观，营业

			if (parseInt(curRst_info.open_status) % 10 == 4) { //已过今天最晚营业时间，休息
				alert("该餐厅已打烊");
				$(".add_sub").attr("disabled", "true");
				// $(".show_count").attr("disabled", "true");
				$("#formSubmit2").css("background", "rgb(141,213,153)");
			} else {
				if (curRst_info.is_bookable == "1") { //可预订
					// alert("可预订");
					$(".add_sub").removeAttr("disabled");
					// $(".show_count").removeAttr("disabled");
					$("#formSubmit2").css("background", "rgb(76,218,100)");
				} else { //不可预订

					if (curRst_info.open_status == "1" || curRst_info.open_status == "2" || curRst_info.open_status == "3") { //营业时间
						// alert("不可预订 营业时间");
						$(".add_sub").removeAttr("disabled");
						// $(".show_count").removeAttr("disabled");
						$("#formSubmit2").css("background", "rgb(76,218,100)");
					} else { //非营业时间
						alert("目前非该餐厅营业时间");
						$(".add_sub").attr("disabled", "true");
						// $(".show_count").attr("disabled", "true");
						$("#formSubmit2").css("background", "rgb(141,213,153)");
					}
				}
			}
		} else { //主观，暂停营业
			alert("餐厅暂停营业");
			$(".add_sub").attr("disabled", "true");
			// $(".show_count").attr("disabled", "true");
			$("#formSubmit2").css("background", "rgb(141,213,153)");
		}
	}


	// 获得页面各种数据，数量、单价、总量
	var $gouwucheItem = $(".gouwucheItem");
	var orderCountArray = new Array($gouwucheItem.length);
	var orderNameArray = new Array($gouwucheItem.length);
	var orderIDArray = new Array($gouwucheItem.length);
	var orderPriceArray = new Array($gouwucheItem.length);
	for (var k = 0; k < $gouwucheItem.length; k++) {
		orderCountArray[k] = $gouwucheItem.eq(k).find(".inputCount").text();
		orderNameArray[k] = $gouwucheItem.eq(k).find(".ItemName").text();
		orderIDArray[k] = $gouwucheItem.eq(k).find(".NameforID").val();
		orderPriceArray[k] = $gouwucheItem.eq(k).find(".inputPrice").text();


	}
	// console.log(orderCountArray);
	// console.log(orderNameArray);
	// console.log(orderIDArray);
	// console.log(orderPriceArray);



	//点击购物车列表项切换可见性
	$(".gouwucheItem").click(function(event) {

		var $deleteAndchange = $(this).find(".deleteAndchange");
		if (event.target.className != "add_sub add" && event.target.className != "add_sub sub") {
			if ($deleteAndchange.is(":visible")) {
				$deleteAndchange.slideUp("fast");
			} else {
				$deleteAndchange.slideDown("fast");
			}


		} else {

		}


	})

	// 点击减一份菜
	$(".sub").click(function() {
		var $inputCount = $(this).parents(".gouwucheItem").find(".inputCount");
		var index = $(".sub").index(this);
		// console.log(index);

		// var number = parseInt($(this).siblings(".show_count").val());
		// var number = parseInt($inputCount.text());
		// if (number > 1) {
		// 	number--;
		// 	// $(this).siblings(".show_count").val(number);
		// 	$inputCount.text(number);
		// } else if (number == 1) { //1份-即删除该菜
		// 	$(this).parents(".gouwucheItem").remove();
		// }
		if (orderCountArray[index] > 1) {
			orderCountArray[index] --;
			$inputCount.text(orderCountArray[index]);
		} else if (orderCountArray[index] == 1) {
			$(this).parents(".gouwucheItem").remove();
			orderCountArray.splice(index, 1);
			orderNameArray.splice(index, 1);
			orderIDArray.splice(index, 1);
			orderPriceArray.splice(index, 1);
		}
		// var $deleteAndchange=$(this).find(".deleteAndchange");
		//  $deleteAndchange.show();
		//  alert("kjf");

		total();
	})


	// 点击加一份菜
	$(".add").click(function() {
		var $inputCount = $(this).parents(".gouwucheItem").find(".inputCount");
		var index = $(".add").index(this);
		orderCountArray[index] ++;
		$inputCount.text(orderCountArray[index]);
		// var number = parseInt($inputCount.text());
		// number++;

		// var $deleteAndchange=$(this).find(".deleteAndchange");

		// 	 $deleteAndchange.show();

		// $(this).siblings(".show_count").val(number);
		total();
	})


	//点击删除按钮
	$(".deleteBtn").click(function() {
		var index = $(".deleteBtn").index(this);

		$(this).parents(".gouwucheItem").remove();
		orderCountArray.splice(index, 1);
		orderNameArray.splice(index, 1);
		orderIDArray.splice(index, 1);
		orderPriceArray.splice(index, 1);
		total();
	})



	function showEmpty() {

		// 获得当前窗口的大小
		var pageHeigth = window.innerHeight;
		var pageWidth = window.innerWidth;
		if (typeof pageHeigth != "number") {
			if (document.compatMode == "CSS1Compat") {
				pageHeigth = document.documentElement.clientHeight;
				pageWidth = document.documentElement.clientWidth;
			} else {
				pageHeigth = document.body.clientHeight;
				pageWidth = document.body.clientWidth;
			}
		

		}

	

        // console.log(pageHeigth);
		$(".empty").height(pageHeigth);
		// console.log($(".empty").height());
		$(".empty").fadeIn();

		$(".totalAllMenu").css("display", "none");
		$("#formSubmit2").css("display", "none");

		if ($.cookie(order_cookie_name)) {

			// console.log(order_cookie_name);
			// 删除cookie
			$.cookie(order_cookie_name, null, {
				expires: -1
			});
		}
	}


	function total() {

		var menulist = $(".gouwucheItem");

		// 当购物车为空时显示图片并隐藏”确认美食“列
		if (menulist.length == 0) {
			showEmpty();
		} else {
			var number = 0;
			var totalPrice = 0;
			// alert("chelist  134" + curRst_info.r_ID);
			var jsonArray = {
				"r_ID": curRst_info.r_ID,
				"total": "",
				"item": new Array(),
				"note": ""
			};
			for (var i = 0; i < menulist.length; i++) {
				var listItem = menulist.eq(i);
				// var nameItem = listItem.find(".ItemName").text();
				// var priceItem = listItem.find(".ItemPrice span").text();
				// var ItemPrice = parseFloat(listItem.find(".inputPrice").text());
				// var nameItemforID=listItem.find(".NameforID").val();

				// alert(listItem.find(".NameforID").val());
				// var show_count = parseInt(listItem.find(".inputCount").text());

				jsonArray["item"][i] = {
					"entity_id": orderIDArray[i] + "",
					'name': orderNameArray[i],
					'price': parseFloat(orderPriceArray[i]) + "",
					'count': parseInt(orderCountArray[i]) + "",
					'total': (parseInt(orderCountArray[i]) * parseFloat(orderPriceArray[i])) + ""
				};

				number += parseInt(orderCountArray[i]);
				totalPrice += (parseInt(orderCountArray[i]) * parseFloat(orderPriceArray[i]));
			}
			jsonArray["total"] = totalPrice + "";
			$("#account").text(number);
			$("#total").text(totalPrice);
			if (number == 0) {
				$(".totalAllMenu").css("display", "none");
			}
			var jsonString = JSON.stringify(jsonArray);

			// 把数组传到hidden中
			// $("#postData").val(jsonString);
			// alert(jsonString);
			$.cookie("pltf_order_cookie", jsonString); //设置cookie 

			if (totalPrice < parseInt(curRst_info.agent_fee)) {
				var balance = parseInt(curRst_info.agent_fee) - totalPrice;
				$("#formSubmit2").text("还差 " + balance + "元起送").css("background", "rgb(141,213,153)");

			} else {
				$("#formSubmit2").text("确认美食").removeAttr("disabled").css("background", "rgb(76,218,100)");
			}

			$(".empty").fadeOut();
			$("#formSubmit2").css("display", "block");
		}
	}


	$("#formSubmit2").click(function() {

		event.preventDefault();

		if (parseInt($("#total").text()) >= parseInt(curRst_info.agent_fee)) {

			order_submit_judge(); //提交订单

		}
	})

	// 判断餐厅状态是否可提交订单
	function order_submit_judge() {

		if (curRst_info.isOpen == "1") { //主观，营业

			if (parseInt(curRst_info.open_status) % 10 == 4) { //已过今天最晚营业时间，休息
				alert("该餐厅已打烊");
			} else {
				if (curRst_info.is_bookable == "1") { //可预订
					// alert("可预订");
					$("#myForm2").submit();
				} else { //不可预订

					if (curRst_info.open_status == "1" || curRst_info.open_status == "2" || curRst_info.open_status == "3") { //营业时间
						// alert("不可预订 营业时间");
						$("#myForm2").submit();
					} else { //非营业时间
						alert("目前非该餐厅营业时间");
					}
				}
			}
		} else { //主观，暂停营业
			alert("餐厅暂停营业");
		}
	}

})