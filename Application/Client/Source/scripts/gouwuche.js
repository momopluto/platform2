$(function() {



	// list页面右边的单价的列表对象
	var $BtnItemPrice = $(".btnPrice .price");

	// clickArray用于存储一种饭菜点了多少份
	var clickArray = new Array($BtnItemPrice.length);
	var index = 0; // 全局变量、用于clickArray数组下标

	var btnRemove = true; //用于标记是否需要清空购物车
	var agent_fee; //起送价，全局变量

	var order_list; // 全局变量，用于存放取得的订单信息cookie数组
	var order_cookie_name = "pltf2_order_cookie"; //对应的cookie名
	var curRst_info; // 全局变量，用于存放取得的当前餐厅信息cookie		数组
	var curRst_cookie_name = "pltf2_curRst_info"; //对应的cookie名

	// 初始化没有点餐
	for (var i = 0; i < clickArray.length; i++) {
		clickArray[i] = 0;
	}



	$(document).ready(function() {
		// 页面加载完毕后，即初始化前端数据**************************************************************
		if ($.cookie(curRst_cookie_name) != null) {
			// alert($.cookie(curRst_cookie_name));
			curRst_info = JSON.parse($.cookie(curRst_cookie_name)); //初始化curRst_info
			// console.log(curRst_info);
			// alert("55 " + curRst_info);

			if (curRst_info != null) {
				// alert(JSON.stringify(curRst_info) + "curRst_info不空");

				agent_fee = parseInt(curRst_info.agent_fee); //即为起送价

				rst_status_judge(); //判断餐厅状态
				order_cookie_judge(); //判断是否已有选单cookie
			}
		}
	});


	// 餐厅状态判断，根据状态，相应展示
	function rst_status_judge() {


		if (curRst_info.isOpen == "1") { //主观，营业

			if (parseInt(curRst_info.open_status) % 10 == 4) { //已过今天最晚营业时间，休息
				alert("已打烊");
				$(".price").after("<p class='rest'>已打烊</p>").attr("disabled", "disabled").css({
					"fontSize": "14px",
					"color": "#555",
					"background": "rgb(255,255,255)"
				});
				$(".number").attr("disabled", "disabled");
			} else {
				if (curRst_info.is_bookable == "1") { //可预订
					// alert("可预订");
					$("#restState").css("display", "none");
					$(".menu").css("margin-top","45px");
					$(".price").removeAttr("disabled").css({
						"fontSize": "14px",
						"color": "rgb(255,255,255)",
						"background": "rgb(49,153,232)"
					});
					$(".number").removeAttr("disabled");
				} else { //不可预订

					if (curRst_info.open_status == "1" || curRst_info.open_status == "2" || curRst_info.open_status == "3") { //营业时间
						// alert("不可预订 营业时间");
						$("#restState").css("display", "none");
						$(".menu").css("margin-top","45px");
						$(".price").removeAttr("disabled").css({
							"fontSize": "14px",
							"color": "rgb(255,255,255)",
							"background": "rgb(49,153,232)"
						});
						$(".number").removeAttr("disabled");
					} else { //非营业时间
						alert("不可预订 非营业时间");
						$(".price").after("<p class='rest'>休息中</p>").attr("disabled", "disabled").css({
							"fontSize": "14px",
							"color": "#555",
							"background": "rgb(255,255,255)"
						});
						$(".number").attr("disabled", "disabled");
					}
				}
			}
		} else { //主观，暂停营业
			alert("暂停营业");
			$(".price").after("<p class='rest'>暂停营业</p>").attr("disabled", "disabled").css({
				"fontSize": "14px",
				"color": "#555",
				"background": "rgb(255,255,255)"
			});
			$(".number").attr("disabled", "disabled");
		}
	}

	// 判断是否有选单的cookie，初始化order_list
	function order_cookie_judge() {
		// $.cookie(order_cookie_name,menu_Orderinfo,{expires:-1});
		if ($.cookie(order_cookie_name) != null) {
			// alert($.cookie(order_cookie_name));
			// menu_Orderinfo = $.cookie(order_cookie_name);//保存cookie至变量menu_Orderinfo

			// json转化数组样式
			order_list = JSON.parse($.cookie(order_cookie_name));

			if (order_list != null) {

				// alert(order_list + "order_list不空")
				// 回复每一项的具体点餐数
				for (var i = 0; i < order_list.item.length; i++) {
					var name = order_list.item[i].name;
					var price = order_list.item[i].price;

					var count = parseInt(order_list.item[i].count);
					var total = order_list.item[i].total;
					var $menuListItem = $(".menuListItem");
					for (var j = 0; j < $menuListItem.length; j++) {
						var menuName = $menuListItem.eq(j).find(".menuName").text();
						var $BtnPrice = $menuListItem.eq(j).find(".price");
						if (name == menuName) {
							clickArray[j] = count;

							if (order_list.r_ID == curRst_info.r_ID) {

								var button = document.createElement("p");

								var p = document.createElement("p");
								p.className = "number";
								$BtnPrice.before(p);


								$BtnPrice.parent().find(".btnSub").css("display", "block");
								$BtnPrice.parent().find(".number").text(clickArray[j]);
							}
							$clone = $("#demoClone").clone(true); //进行一次深克隆
							$clone.find(".ItemName").text(name);
							$clone.find(".show_count").val(clickArray[j]);

							$clone.find(".ItemPrice").text("￥" + price);
							$(".listUl").append($clone);
							$clone.slideDown();
							Total(clickArray, j);
						}
					}
				}
			}
		}
	}


	//获得菜单名和id数组列表
	var $orderNameArray = $(".menuName");
	var orderNameArray = new Array($orderNameArray.length);
	var orderIDArray = new Array($orderNameArray.length);
	var orderPriceArray = new Array($BtnItemPrice.length);
	for (var k = 0; k < orderNameArray.length; k++) {
		orderNameArray[k] = $orderNameArray.eq(k).text();
		orderIDArray[k] = $orderNameArray.eq(k).siblings(".menuNameforID").val();

	}

	// 获得菜单单价的数组列表
	for (var m = 0; m < $BtnItemPrice.length; m++) {
		orderPriceArray[m] = $BtnItemPrice.eq(m).text().slice(1);
		// clickArray[m]=$BtnItemPrice.eq(m).siblings(".number").text();
	}

	// 点击价钱的时候出现数量
	$BtnItemPrice.mouseover(function() {
		$(this).css("cursor", "default");
	}).click(function() {
		if (order_list != null && curRst_info != null) {

			if (order_list.r_ID == curRst_info.r_ID) {
				index = $BtnItemPrice.index(this);

				if (clickArray[index] < 1) {

					clickArray[index] = clickArray[index] + 1;
					var button = document.createElement("p");

					var p = document.createElement("p");
					p.className = "number";
					$(this).before(p);

					//p.before(button);
					$(this).parent().find(".btnSub").css("display", "block");
					$(this).parent().find(".number").text(clickArray[index]);


					Total(clickArray, index);
				} else {
					clickArray[index] = clickArray[index] + 1;

					$(this).parent().find(".number").text(clickArray[index]);

					// 显示隐藏购物车的数量
					$(".ItemName:contains('" + orderNameArray[index] + "')").siblings().find(".show_count").val(clickArray[index]);
					Total(clickArray, index);
				}
			} else {
				if (order_list != null && order_list.r_ID != curRst_info.r_ID) {

					clearCart(); //清空美食篮子
				}

			}
		} else {
			index = $BtnItemPrice.index(this);

			if (clickArray[index] < 1) {

				clickArray[index] = clickArray[index] + 1;
				var button = document.createElement("p");

				var p = document.createElement("p");
				p.className = "number";
				$(this).before(p);

				//p.before(button);
				$(this).parent().find(".btnSub").css("display", "block");
				$(this).parent().find(".number").text(clickArray[index]);


				Total(clickArray, index);
			} else {
				clickArray[index] = clickArray[index] + 1;

				$(this).parent().find(".number").text(clickArray[index]);

				$(".ItemName:contains('" + orderNameArray[index] + "')").siblings().find(".show_count").val(clickArray[index]);
				Total(clickArray, index);
			}

		}

	})

	//清空美食篮子
	function clearCart() {

		var deleteOrNot = confirm("购物车内有其它餐厅的美食\n是否清空美食篮子中的所有美食");

		if (deleteOrNot == true) {
			$(".gouwucheItem:gt(0)").remove();

			for (var n = 0; n < clickArray.length; n++) {
				clickArray[n] = 0;
			}

			$.cookie(order_cookie_name, null, {
				expires: -1
			});
			order_list = null;


			Total(clickArray, index);
		}
	}

	// 订单信息写入cookie
	function setCookie(jsonArray) {

		// alert("setCookie");

		if (jsonArray != null && jsonArray.total != "0") {

			if (order_list != null) {
				// alert("当前餐厅pltf2_curRst_info ＝ " + curRst_info.r_ID);
				// alert("原餐厅r_ID ＝ " + order_list.r_ID);

				if (order_list.r_ID != curRst_info.r_ID) {
					jsonArray["r_ID"] = order_list.r_ID;
				}
			}

			var menu_Orderinfo = JSON.stringify(jsonArray);
			$.cookie(order_cookie_name, menu_Orderinfo);
			// alert("成功写入cookie");

			// alert("数据是："+menu_Orderinfo);

		} else {
			$.cookie(order_cookie_name, null, {
				expires: -1
			});
			// alert("不符的数据，删除cookie");
		}
	}


	//点击“-”的时候数量的变化（btnSub）
	$(".btnSub").mouseover(function() {
		$(this).css("cursor", "default");
	}).click(function() {
		index = $(".btnSub").index(this);

		var text = orderNameArray[index];

		if (clickArray[index] > 1) {
			clickArray[index] --;

			$(this).parent().find(".number").text(clickArray[index]);

			var text = orderNameArray[index];

			$(".ItemName:contains('" + text + "')").siblings().find(".show_count").val(clickArray[index]);


			Total(clickArray, index);
		} else {
			$(this).css("display", "none");
			$(this).siblings(".number").remove();
			clickArray[index] = 0;
			$(".ItemName:contains('" + text + "')").parent().remove();

			Total(clickArray, index);
		}
	})


	//点击去结算列
	$("#formSubmit").click(function(event) {

		event.preventDefault();

		if (order_list != null && order_list.r_ID != curRst_info.r_ID) {

			clearCart(); //清空美食篮子

		} else {

			var total_price = parseInt($(".total_price").text().slice(1));

			if (total_price >= agent_fee) {

				$("#myForm").submit();

			} else {
				event.preventDefault();
			}
		}
	})



	function Total(clickArray, index) {
	
		var jsonArray = {
			"r_ID": curRst_info.r_ID, //""curRst_info.r_ID
			"total": "",
			"item": new Array(),
			"note": ""
		};
		
		var account = 0;
		var number=0;

		for (var i = 0,j=0; i < clickArray.length; i++) {
			if (clickArray[i] > 0) {

				jsonArray["item"][j] = {
					"entity_id": orderIDArray[i],
					"name": orderNameArray[i],
					"price": orderPriceArray[i] + "",
					"count": clickArray[i] + "",
					"total": parseFloat(orderPriceArray[i])*parseInt(clickArray[i]) + ""
				};

				account = account + parseFloat(orderPriceArray[i])*parseInt(clickArray[i]);
				number=number+parseInt(clickArray[i]);
				j++;

			}
		}	
		
		if (account >= agent_fee) {
			$(".jiesuan").css("display", "block");
			$(".shortcComing").css("display", "none");
		} else {

			$(".jiesuan").css("display", "none");
			$(".shortcComing").css("display", "block");
			var shortcComing = agent_fee - account;
			$(".shortcComing span").text(shortcComing);
		}

		jsonArray["total"] = account + "";

		jsonArray["note"] = $("#beizhu").val();
		account = "￥" + account;
		$(".account_menu").text(number);
		$(".total_price").text(account);

		setCookie(jsonArray);
	}

})