<?php
/**
 * Home公共函数
 *
 */

// 检验Home是否已登录
function is_login(){
    if(session('?login_flag') && session('login_flag')){
        return true;
    }
    return false;
}

/* 限制一定要设置了餐厅信息才能进行其它操作 */
function has_rstInfo($r_ID){
    $map['r_ID'] = $r_ID;
    if(M('restaurant')->where($map)->count()){
        return true;
    }
    
    return false;
}

/**
 * 判断餐厅ID是否合法
 * @param  int  $r_ID 餐厅ID
 * @return boolean
 */
function is_rID_valid($r_ID){

    if ($r_ID == '') {
        return false;
    }

    if (!session('RST_INFO')) {
        
        echo "no session!";
        die;
    }

    return array_key_exists($r_ID, session('RST_INFO'));
}

/**
 * 获取顾客1个月内的订单记录
 * @param  array $map 数据库查询条件
 * @return array      格式化后的订单集
 */
function get_client_his_orders($map){

    $model = M('orderitem');

    $today = date('Y-m-d');//今日
    $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳
    $last_month_days = getLastMonth_StartAndEnd($today);//上月第1日和最后1日，数组时间戳

    // 上月底到本月底的订单
    $temp = $model->where($map)->where("DATE_FORMAT(cTime,'%Y-%m-%d') between '"
        .date('Y-m-d',$last_month_days[1])."' and '"
        .date('Y-m-d',$month_days[1])."'")->order('cTime desc')->select();

    //组合成的数组信息array(下单时间[已过x天]，金额，订单文本信息，地址，电话)
    foreach ($temp as $key => $one_temp) {
        // p($one_temp);die;
        $one_order['phone'] = $one_temp['phone'];
        $one_order['address'] = $one_temp['address'];
        $one_order['total'] = $one_temp['total'];
    
        // xx时间前的订单
        $UNIX_time = strtotime($one_temp['cTime']);
        if($t = intval((NOW_TIME-$UNIX_time)/86400)){
            $one_order['pasttime'] = $t."天前";
        }else{
            if($t = abs(intval((NOW_TIME-$UNIX_time)/3600))){
                $one_order['pasttime'] = $t."小时前";
            }else{
                $one_order['pasttime'] = abs(intval((NOW_TIME-$UNIX_time)/60))."分钟前";
            }                    
        }

        $order_info = json_decode($one_temp['order_info'],true);
        $info = '';
        $flag = true;
        foreach ($order_info['item'] as $an_order) {
            // p($an_order);die;
            if($flag){
                $info .= $an_order['name']." ".$an_order['count']."份";
                $flag = false;
            }else{
                $info .= "<br/>".$an_order['name']." ".$an_order['count']."份";
            }
        }
        $one_order['info'] = $info;
        // echo $info;
        // p($one_order);die;
        $hisOrders[] = $one_order;
    }

    return $hisOrders;
}

/**
 * GET 请求，curl函数
 *
 * @param string $url 请求的网址
 * @return string $sContent 返回的内容
 */
function _http_get($url) {
    $oCurl = curl_init ();
    if (stripos ( $url, "https://" ) !== FALSE) {
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
    }
    curl_setopt ( $oCurl, CURLOPT_URL, $url );
    curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec ( $oCurl );
    $aStatus = curl_getinfo ( $oCurl );
    curl_close ( $oCurl );
    if (intval ( $aStatus ["http_code"] ) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

/**
 * POST 请求，curl函数
 *
 * @param string $url 请求的网址
 * @param string $strPOST  post传递的数据 
 * @return string sContent 返回的内容
 */
function _http_post($url, $strPOST) {
    $oCurl = curl_init ();
    if (stripos ( $url, "https://" ) !== FALSE) {
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
    }

    curl_setopt ( $oCurl, CURLOPT_URL, $url );
    curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $oCurl, CURLOPT_POST, true );
    curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
    $sContent = curl_exec ( $oCurl );
    $aStatus = curl_getinfo ( $oCurl );
    curl_close ( $oCurl );
    if (intval ( $aStatus ["http_code"] ) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

//获得access_token，判断有无缓存
function get_access_token($fromUsername){
    $map ['token'] = $fromUsername;

    $value = S($map ['token']);// 读取缓存
    // echo $value;
    // S($map ['token'],null);
    if(!$value){
        // echo $value."过期啦or不存在！";die;
        $info = M ( 'public' )->where ( $map )->find ();
        $url_get = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $info ['appid'] . '&secret=' . $info ['secret'];

        // $timeout = 5;
        // curl_setopt ( $ch1, CURLOPT_CONNECTTIMEOUT, $timeout );

        $accesstxt = _http_get($url_get);

        $access = json_decode ( $accesstxt, true );
        if (empty ( $access ['access_token'] )) {
            // $this->error ( '获取access_token失败,请确认AppId和Secret配置是否正确,然后再重试。' );
        }
        // 采用文件方式缓存数据300秒
        S($map ['token'],$access ['access_token'],array('type'=>'file','expire'=>7200));
        $value = S($map ['token']);// 读取缓存
    }

    return $value;
}

/**
 * 向用户发送信息（文本），服务号才能用
 *
 * @param $fromUsername 发送者token
 * @param $toUsername 接收者openid
 * @param $contentStr 发送的内容
 * @return mixed 结果代码
 */
function send_msg($fromUsername,$toUsername,$contentStr){
    //获取access_token,判断有无缓存
    $access_token = get_access_token($fromUsername);

    $contentStr = urlencode($contentStr);
    $a = array("content" => $contentStr);
    $b = array("touser" => $toUsername, "msgtype" => "text", "text" => $a);
    $strPOST = json_encode($b);
    $strPOST = urldecode($strPOST);
    $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $access_token;

    $res = _http_post($url, $strPOST);

    return json_decode ( $res, true );
}

?>