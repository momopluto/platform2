<?php
/**
 * Client公共函数
 *
 */


// 比较本月销售额(降序)，用于所有餐厅排序
function compare_month_sales($x, $y){
	if($x['month_sales'] == $y['month_sales']){//
		return 0;
	}elseif($x['month_sales'] > $y['month_sales']){
		return -1;
	}else{
		return 1;
	}
}


// 比较上个月销售额(降序)，用于所有餐厅排序
function compare_last_month_sales($x, $y){
	if($x['last_month_sales'] == $y['last_month_sales']){//
		return 0;
	}elseif($x['last_month_sales'] > $y['last_month_sales']){
		return -1;
	}else{
		return 1;
	}
}

/**
 * 订餐页面所需要的餐厅的信息，组装
 * 判断当前时间餐厅状态以及月销售量
 * @param  Array $an_rst 某一餐厅
 * @return Array         组装过后的餐厅信息
 */
function rstInfo_combine($an_rst){
	// 判断是否营业时间
    $n_time = date('H:i');

    if(strtotime($n_time) < strtotime($an_rst['time_1_open'])){
        $open_status = "0";
    }elseif(strtotime($n_time) <= strtotime($an_rst['time_1_close'])){
        $open_status = "1";
    }else{
        $open_status = "14";
    }

    if ($an_rst['time_2_open'] !== '' && $an_rst['time_2_close'] !== '') {
        $has_2_time = true;
        if(strtotime($n_time) < strtotime($an_rst['time_2_open'])){
            if($open_status == "14"){
                $open_status = "12";
            }
        }elseif(strtotime($n_time) <= strtotime($an_rst['time_2_close'])){
            $open_status = "2";
        }else{
            $open_status = "24";
        }
    }

    if ($an_rst['time_3_open'] !== '' && $an_rst['time_3_close'] !== '') {
        $has_2_time = true;
        if(strtotime($n_time) < strtotime($an_rst['time_3_open'])){
            if($open_status == "24"){
                $open_status = "23";
            }
        }elseif(strtotime($n_time) <= strtotime($an_rst['time_3_close'])){
            $open_status = "3";
        }else{
            $open_status = "4";
        }
    }

    $an_rst['open_status'] = $open_status;

    $menuModel = M('menu');
    $month_sales = $menuModel->where(array('r_ID'=>$an_rst['r_ID']))->sum('month_sales');//本月销售量
    $last_month_sales = $menuModel->where(array('r_ID'=>$an_rst['r_ID']))->sum('last_month_sales');//上月销售量

    $an_rst['month_sales'] = $month_sales ? $month_sales : 0;
    $an_rst['last_month_sales'] = $last_month_sales ? $last_month_sales : 0;

    return $an_rst;
}

/**
 * 将餐厅分为"营业"和"非营业"2类
 * @param  Array $rsts        所有餐厅
 * @param  Array &$open_rsts  营业餐厅，引用类型
 * @param  Array &$close_rsts 非营业餐厅，引用类型
 */
function classify_open_n_close_rsts($rsts, &$open_rsts, &$close_rsts){

	foreach ($rsts as $an_rst) {

	    $an_rst = rstInfo_combine($an_rst);// 订餐页面所需要的餐厅的信息，组装
	    
	    $key = $an_rst['r_ID'];// 键为餐厅ID

	    if($an_rst['isOpen'] == "1"){//主观，营业
	        // echo $an_rst['open_status']."status！";die;
	        if(intval($an_rst['open_status']) % 10 == 4){//已过餐厅今天的所有营业时间
	            // echo $an_rst['r_ID']."打烊了啊！";die;
	            $close_rsts[$key] = $an_rst;
	        }else{
	            if($an_rst['is_bookable']){
	                $open_rsts[$key] = $an_rst;
	            }else{
	                if($an_rst['open_status'] == "1" || $an_rst['open_status'] == "2" || $an_rst['open_status'] == "3"){
	                    $open_rsts[$key] = $an_rst;
	                }else{
	                    $close_rsts[$key] = $an_rst;
	                }
	            }
	        } 
	    }else{//主观，其它，非营业
	        $close_rsts[$key] = $an_rst;
	    }    
	}
}


/**
 * 按营业额排序"营业&非营业餐厅"
 * @param  Array &$open_rsts  营业餐厅，引用类型
 * @param  Array &$close_rsts 非营业餐厅，引用类型
 */
function sortBy_sales(&$open_rsts, &$close_rsts){

	$today = date('Y-m-d');//今日
	$month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳

	if (strtotime($today) != $month_days[0]) {
	    //不是每月第1天，以本月售为排序标准
	    uasort($open_rsts, 'compare_month_sales');//降序
	    uasort($close_rsts, 'compare_month_sales');//降序
	}else{
	    //本月第1天，以上月销售为排序标准
	    uasort($open_rsts, 'compare_last_month_sales');//降序
	    uasort($close_rsts, 'compare_last_month_sales');//降序
	}
}


// 将open - close之间的时间，以10分钟为间隔分割
function cut_cut($open, $close, $on){

	$times = array();

	$strOpen = strtotime($open);
	$strClose = strtotime($close);

	if($on){//餐厅正在营业

		if(($strClose  - $strOpen) / 60 > 40){

			$strOpen = $strOpen + (600 - $strOpen % 600);//向上取整为10的倍数

			for ($i=2; $i <= ($strClose  - $strOpen) / 600; $i++) { 

				array_push($times, date('H:i',($strOpen + 600 * $i)));

			}
		}

	}else{//餐厅尚未营业

		for ($i=0; $i <= ($strClose  - $strOpen) / 600; $i++) { 

			array_push($times, date('H:i',($strOpen + 600 * $i)));

		}
	}

	// p($times);die;
	return $times;
}

// 划分送餐时间，返回字符串数组
function cut_send_times($an_rst){

	$s_times = array();

	if(intval($an_rst['open_status']) % 10 != 4){//未到休息时间
		
		if($an_rst['open_status'] == "0"){

			$s_times = cut_cut($an_rst['time_1_open'], $an_rst['time_1_close'], 0);

		}elseif($an_rst['open_status'] == "1"){

			$s_times = cut_cut(date('H:i'), $an_rst['time_1_close'], 1);

		}elseif($an_rst['open_status'] == "12"){

			$s_times = cut_cut($an_rst['time_2_open'], $an_rst['time_2_close'], 0);

		}elseif($an_rst['open_status'] == "2"){

			$s_times = cut_cut(date('H:i'), $an_rst['time_2_close'], 1);

		}elseif($an_rst['open_status'] == "23"){

			$s_times = cut_cut($an_rst['time_3_open'], $an_rst['time_3_close'], 0);

		}elseif($an_rst['open_status'] == "3"){

			$s_times = cut_cut(date('H:i'), $an_rst['time_3_close'], 1);

		}

	}

	return $s_times;
}

// 不论注册与否，得到用户的ID
function get_client_ID($order){

	// 主要用到的是$order['c_phone'],$order['c_address'],$order['c_name']
	
	// 通过$order['c_phone']查找client表中是否有该用户
	// 		有，则"比较当前数据和client数据库数据，如不一致，更新数据库数据"，得到client_ID，返回
	// 		没有，则使用订单中的送餐信息"为用户注册"，得到client_ID，返回
}

// 获取卓效团队的接口得到的user_id
function get_zx_userid($jump_url){

	$aid = "608f5652accc7314abd682e8dedfba86";
    // $jump_url = "http://192.168.1.103:8080/platform/index.php/Client/Restaurant/lists.html";
    
    $zx_url = "http://wx.joshell.com/" . $aid . "/jwc/open-oauth?redirect_uri=" . $jump_url;

    redirect($zx_url);
}

// 判断网页是否在微信浏览器中打开
function is_weixin(){

	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {

		return true;
	}

	return false;

	// $user_agent = $_SERVER['HTTP_USER_AGENT'];
	// if (strpos($user_agent, 'MicroMessenger') === false) {
	//     // 非微信浏览器禁止浏览
	//     echo "HTTP/1.1 401 Unauthorized";
	// } else {
	//     // 微信浏览器，允许访问
	//     echo "MicroMessenger";
	//     // 获取版本号
	//     preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
	//     echo '<br>Version:'.$matches[2];
	// }

// 	下面分别是 Android, WinPhone, iPhone 的 HTTP_USER_AGENT 信息。
// 1 "HTTP_USER_AGENT": "Mozilla/5.0 (Linux; U; Android 4.1; zh-cn; Galaxy Nexus Build/Wind-Galaxy Nexus-V1.2) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 MicroMessenger/5.0.1.352",
// 2 "HTTP_USER_AGENT": "Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; Nokia 920T)",
// 3 "HTTP_USER_AGENT": "Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Mobile/10B329 MicroMessenger/5.0.1",
}


?>