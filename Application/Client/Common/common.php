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
 * 得到格式化后(简单)的餐厅营业状态
 * @param  Array $an_rst 餐厅信息
 * @return int           餐厅状态：-1，餐厅不营业；0，非餐厅营业时间，但可预订；1，餐厅营业
 */
function get_format_open_status($an_rst){

	$open_status = get_open_status($an_rst);

	if($an_rst['isOpen'] == "0"){//主观，休息

	    $status = -1;
	}else{//主观，营业
	    if(intval($an_rst['open_status']) % 10 == 4){//已过餐厅今天的所有营业时间
	        
	        $status = -1;
	    }else{
	        if($an_rst['is_bookable']){

	            $status = 0;// 非营业时间，但可预订
	        }else{
	            if($an_rst['open_status'] == "1" || $an_rst['open_status'] == "2" || $an_rst['open_status'] == "3"){
	                $status = 1;
	            }else{
	            	// 非营业时间，且不可预订
	                $status = -1;
	            }
	        }
	    } 
	}

	return $status;
}

/**
 * 得到餐厅营业状态
 * @param  Array $an_rst 餐厅信息
 * @return int          餐厅状态
 */
function get_open_status($an_rst){

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

    return $open_status;
}

/**
 * 订餐页面所需要的餐厅的信息，组装
 * 判断当前时间餐厅状态以及月销售量
 * @param  Array $an_rst 某一餐厅
 * @return Array         组装过后的餐厅信息
 */
function rstInfo_combine($an_rst){

	// 得到餐厅营业状态
    $an_rst['open_status'] = get_open_status($an_rst);

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
	            $close_rsts[/*$key*/] = $an_rst;
	        }else{
	            if($an_rst['is_bookable']){
	                $open_rsts[/*$key*/] = $an_rst;
	            }else{
	                if($an_rst['open_status'] == "1" || $an_rst['open_status'] == "2" || $an_rst['open_status'] == "3"){
	                    $open_rsts[/*$key*/] = $an_rst;
	                }else{
	                    $close_rsts[/*$key*/] = $an_rst;
	                }
	            }
	        } 
	    }else{//主观，其它，非营业
	        $close_rsts[/*$key*/] = $an_rst;
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
	    usort($open_rsts, 'compare_month_sales');//降序
	    usort($close_rsts, 'compare_month_sales');//降序
	}else{
	    //本月第1天，以上月销售为排序标准
	    usort($open_rsts, 'compare_last_month_sales');//降序
	    usort($close_rsts, 'compare_last_month_sales');//降序
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


/**
 * 检验订单信息数组，确保需要用到的"键"都存在
 * @param  array $order 订单信息数组
 * @return bool         是否合法
 */
function check_order_array_key_exists($order){

	// 需要检查的key
	$ORDER_KEY = array('r_ID','total','item','c_name','c_address','c_phone','note','deliverTime','cTime');
	// p($ORDER_KEY);

	foreach ($ORDER_KEY as $key) {
		
		if (!array_key_exists($key, $order)){

			return false;
		}
	}

	return true;
}

/**
 * 判断该手机号是否已存在于client表中
 * @param string $phone 手机号
 * @return 成功返回一条记录;失败返回false
 */
function is_exists_phone($phone){

	$model = M('client');

	$map['phone'] = $phone;
	return $model->where($map)->find();
}

/**
 * 获取该用户的所有送餐地址
 * @param string $client_ID 用户ID
 * @param bool $option 选择是否返回完整数据
 * @return 成功返回地址数组;失败返回NULL
 */
function get_client_address($client_ID, $option=true){

	$model = M('client_address');
	$map['client_ID'] = $client_ID;

	if ($option) {
		$one = $model->where($map)->select();// 一条记录中的完整数据
	}else {
		$one = $model->where($map)->field('address_ID,address')->select();// 去掉了记录中的client_ID
	}
	
	return $one;
}

/**
 * 不论该手机号是否注册，得到对应用户的ID
 * @param  Array $order 订餐信息数组(含手机号)
 * @return 成功返回client_ID;否则返回false
 */
function get_client_ID($order){

	// 主要用到的是$order['c_phone'],$order['c_address'],$order['c_name']
	
	// 通过$order['c_phone']查找client表中是否有该用户
	// 		有，则返回client_ID，返回
	// 		没有，则使用订单中的送餐信息"为用户注册"，得到client_ID，返回

	$one = is_exists_phone($order['c_phone']);

	if ($one) {
		// 已注册
		session('CLIENT_ID', $one['client_ID']);// PC端查询订单需要用到client_ID
		return $one['client_ID'];
	}else{
		//未注册
		//替用户注册得到client_ID

		// 写入表client
		$reg_data['phone'] = $order['c_phone'];
		$reg_data['name'] = $order['c_name'];
		$reg_data['reg_time'] = date('Y-m-d H:i:s');

		// p($reg_data);
		$client_model = M('client');
		$client_ID = $client_model->add($reg_data);
		// p($client_model);

		if ($client_ID) {
			// 写入表client_address
			
			$addr_data['address'] = $order['c_address'];
			$addr_data['client_ID'] = $client_ID;

			$addr_model = M('client_address');
			if (!$addr_model->add($addr_data)) {
				// 未成功插入地址
				// 此处不需要事务。因为可以仅为用户注册，而没有绑定地址
				return false;
			}
			
			session('CLIENT_ID', $client_ID);// PC端查询订单需要用到client_ID
			return $client_ID;
		}
	}
}


/**
 * 获取客户1个月内的历史订单
 * @param  int $client_ID 客户ID
 * @return Array          历史订单
 */
function get_his_orders($client_ID){

    $map['client_ID'] = $client_ID;
    $model = D('OrderView');

    $today = date('Y-m-d');//今日
    $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳
    $last_month_days = getLastMonth_StartAndEnd($today);//上月第1日和最后1日，数组时间戳

    // 上月底到本月底的订单
    $t_brief = $model->where($map)->where("DATE_FORMAT(cTime,'%Y-%m-%d') between '"
        .date('Y-m-d',$last_month_days[1])."' and '"
        .date('Y-m-d',$month_days[1])."'")->order('cTime desc')->field('guid,cTime,r_ID,logo_url,r_name,total,status,reason')->select();

    // p($model);

    if($t_brief){
    // 存在订单数据

        // 转换时间显示格式
        foreach ($t_brief as $one) {

            $one['cTime'] = date('m.d H:i', strtotime($one['cTime']));

            $data[] = $one;
            // p($one);die;
        }
        // p($data);die;

    }else {

        $data['errcode'] = '46010';
        $data['errmsg'] = '一个月内没有下过单';
    }

    return $data;
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