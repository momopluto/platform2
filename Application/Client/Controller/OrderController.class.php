<?php
namespace Client\Controller;
use Think\Controller;
class OrderController extends ClientController {


    /**
     * 查询历史订单
     * 分PC和移动端处理
     */
    function orders(){

        echo "orders can work!";

        if (I('get.srcid') == null) {
            
            // pc
            // 直接用session中的标识
            $data = $this->get_his_orders(session('CLIENT_ID'));

            $this->assign('data',$data);
            $this->display('order');
        }else {
            if (I('get.srcid') == '10086') {
                
                // 根据post过来的client_ID
                $data = $this->get_his_orders(I('post.client_ID'));

                $JSON['data'] = $data;
                echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
            }else {

                // do nothing
                return;
            }
        }
    }

    
    // 查询订单
    function myOrder(){

//假定给出cookie('phone')或者cookie('openid')
// cookie('openid', 'o55gotzkfpEcJoQGXBtIKoSrairQ');

        // cookie('pltf_openid', null);
        // cookie('openid',null);
        // cookie('c_info',null);
        // echo I('get.user_id');
        // p(cookie());die;
        // echo I('get.user_id');die;

        // $isWechat = is_weixin();
        
        if (!is_weixin()) {//非微信浏览器

            get_zx_userid('');// 调用卓效获取用户user_id接口，仅为显示请在微信中打开
        }

        // 如果存在get.user_id，存入cookie，重定向1次去掉user_id
        if ($user_id = I('get.user_id')) {
            // echo "111";die;
            cookie('openid', $user_id);
            cookie('token', "gh_34b3b2e6ed7f");// *****************************此处的token最好统一定义

            $model = M('orderman','admin_');
            // 准备好用户的手机、地址信息
            $map['openid'] = cookie('openid');
            $c_info = $model->where($map)->field('name, phone, address')->find();
            if (!is_null($c_info)) {

                cookie('c_info', json_encode($c_info));
            }

            redirect(U("Client/Order/myOrder"));
        }

        // 不存在get.user_id，也不存在cookie('openid')，出错！重新获取用户user_id
        if (is_null(cookie('openid'))) {

            // echo "222";die;

            $jump_url = U("Client/Order/myOrder");
            $jump_url = "http://".$_SERVER['HTTP_HOST'].$jump_url;// 加上域名

            get_zx_userid($jump_url);// 调用卓效获取用户user_id接口
        }
        
        // 如果不存在get.user_id，但存在cookie('openid')，则已成功处理掉get.user_id，继续下一步进入选择餐厅界面即可
        // echo "333";die;

        // 即只要存在cookie('openid')，能够标识用户，才满足一切操作的前提

// p(cookie());
// die;

        $has_cookie = true;
        if(cookie('pltf_phone')){
        // 有phone
            $map['phone'] = cookie('pltf_phone');
        }else{

            if(cookie('openid')){
            // 有openid
                $whe['openid'] = cookie('openid');
                // 在orderman中通过openid对应phone
                $map = M('orderman','admin_')->where($whe)->field('openid')->find();

                if(is_null($map)){
                // 微信上使用，用openid过滤
                // 确保openid存在
                
                    $has_cookie = false;
                }
                
            }else{
            // 没有phone 也没有openid
                $has_cookie = false;
            }

        }

        // p($map);die;


        
        // 得到了phone后，开始查数据
        if($has_cookie){

            $o_model = M('orderitem',' ');
            $r_model = M('resturant','home_');

            // $t_brief = $o_model->where($map)->order('cTime desc')->field('guid,cTime,rid,total,status,reason')->select();

            $today = date('Y-m-d');//今日
            $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳
            $last_month_days = getLastMonth_StartAndEnd($today);//上月第1日和最后1日，数组时间戳

            // 上月底到本月底的订单
            $t_brief = $o_model->where($map)->where("cTime between '".$last_month_days[1]."' and '".$month_days[1]."'")->order('cTime desc')->field('guid,cTime,rid,total,status,reason')->select();

            if(!is_null($t_brief)){
            // 存在订单数据
                $rsts = $r_model->getField('rid,logo_url,rst_name');

                foreach ($t_brief as $one) {
                    $one['logo_url'] = $rsts[$one['rid']]['logo_url'];
                    $one['rst_name'] = $rsts[$one['rid']]['rst_name'];
                    $one['cTime'] = date('n.d H:i', $one['cTime']);

                    unset($one['rid']);

                    $data[] = $one;
                    // p($one);die;
                }
                // p($data);die;
                $this->assign('data',$data);
            }
        }

        $this->display('order');
    }

    // 订单详情
    function detail(){
        
        if(!I('get.id')){

            $this->error('无效订单号');
        }
        $map['guid'] = I('get.id');



        // $o_model = M('orderitem',' ');
        $order = M('orderitem',' ')->where($map)->find();
        if(!is_null($order)){
            // p($order);die;

            $r_model = M('resturant','home_');
            $whe['rid'] = $order['rid'];
            $rstinfo = $r_model->where($whe)->field('logo_url,rst_name,rst_phone')->find();

            $brief['rid'] = 10086 * $order['rid'];//此处rid加密
            $brief['logo_url'] = $rstinfo['logo_url'];
            $brief['rst_name'] = $rstinfo['rst_name'];
            $brief['cTime'] = date('Y-n-d H:i', $order['cTime']);
            $brief['total'] = $order['total'];
            $brief['status'] = $order['status'];
            $brief['reason'] = $order['reason'];
            // p($rstinfo);die;
            
            $data['rst_phone'] = $rstinfo['rst_phone'];
            $data['brief'] = $brief;

            $o_info = json_decode($order['order_info'],true);
            $i_count = 0;
            foreach ($o_info['item'] as $an_item) {
                $i_count += $an_item['count'];
            }
            $data['count'] = $i_count;
            $data['total'] = $order['total'];
            $data['items'] = $o_info['item'];
            $data['name'] = $order['name'];
            $data['phone'] = $order['phone'];
            $data['address'] = $order['address'];
            $data['guid'] = $order['guid'];
            $data['note'] = $o_info['note'];
            // p($data);die;
            
            $this->assign('data', $data);
        }
        
    	$this->display();
    }



    /*array格式订单信息
        (
            [total] => 50
            [item] => Array
                (
                    [0] => Array
                        (
                            [name] => \u5c0f\u83dc00
                            [price] => 10
                            [count] => 2
                            [total] => 20
                        )

                    [1] => Array
                        (
                            [name] => \u5c0f\u83dc11
                            [price] => 10
                            [count] => 3
                            [total] => 30
                        )

                )

            [note] => testtttttttttttttttttt
        )
     */
    /*json格式订单信息
        {
            "total": "50",
            "item": [{
                "name": "\u5c0f\u83dc00",
                "price": "10",
                "count": "2",
                "total": "20"
            }, {
                "name": "\u5c0f\u83dc11",
                "price": "10",
                "count": "3",
                "total": "30"
            }],
            "note": "testtttttttttttttttttt"
        }

        {
             "total": 14,
             "items": [{
                 "entity_id": 5472819,
                 "name": "\u5c0f\u9ec4\u95f7\u9e21\u7c73\u996d\uff08\u5b66\u751f\u4ef7\uff09",
                 "price": 12,
                 "parent_entity_id": 0,
                 "group_id": 1,
                 "quantity": 2,
                 "entity_category_id": 1
             }, {
                 "entity_id": 4858126,
                 "name": "\u67e0\u6aac\u8336",
                 "price": 3,
                 "parent_entity_id": 0,
                 "group_id": 1,
                 "quantity": 1,
                 "entity_category_id": 1
             }, {
                 "entity_id": 1331,
                 "name": "\u7acb\u51cf\u4f18\u60e0",
                 "price": -13,
                 "parent_entity_id": 0,
                 "group_id": 0,
                 "quantity": 1,
                 "entity_category_id": 12
             }],
             "isWaimai": true,
             "isTangchi": false,
             "isBook": true,
             "deliverTime": "12\u70b930\u5206",
             "restaurantNumber": 20,
             "createdAt": "2014-09-29 10:34:02",
             "table": "",
             "people": "",
             "phone": "18826485288",
             "address": "\u534e\u5c7114\u680b503",
             "description": "",
             "isFollower": false,
             "isOnlinePayment": true,
             "invoice": "",
             "isTimeEnsure": false,
             "timeEnsureText": ""
         };
    */


    /**
     * 最终下单
     * 分PC和移动端处理
     */
    function done(){
        
        if (I('get.srcid') == null) {// ***********************PC端的提交请求

            if(IS_POST){

                $json_order = cookie('pltf2_order_cookie');
                if(!$json_order){

                    // 没有订单信息的cookie
                    $this->error('Something Wrong！', U('Client/Restaurant/lists'));
                }

                $order = json_decode($json_order, true);

                $data = $this->handle_order($order);

                if ($data['result']) {// 成功写入数据库

                    // 清除session和cookie
                    session('pltf2_curRst_info', null);
                    cookie('pltf2_curRst_info', null);

                    
                    // 以下2句代码须同时使用，且顺序不能调换
                    cookie('pltf_order_cookie', null);// 删thinkphp中的cookie
                    setcookie("pltf_order_cookie", "", time()-1);// 真正从浏览器中删除

                    $this->display();
                }else {

                    $this->error('下单未能完成，请稍后再试！');
                }

            }else {

                redirect(U('Client/Restaurant/lists'));
                return;
            }

        }else {// *****************************非PC端，如：移动端
            if (I('get.srcid') == '10086') {// 且srcid是指定的值
                
                $json_order = I('post.order');
                $order = json_decode($json_order, true);

                $data = $this->handle_order($order);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }else {

                redirect(U('Client/Restaurant/lists'));
                return;
            }
        }
    }


    // 送餐信息
    function delivery(){


        if(IS_POST){

            if (!session('?pltf2_curRst_info')) {
        //检错*********************************************
                $this->error('Something Wrong！', U('Client/Restaurant/lists'));
            }

            // 获取餐厅rid，再次验证餐厅状态
            $rst = session('pltf2_curRst_info');
            $rst = $this->update_curRstInfo($rst['r_ID']);// 更新餐厅信息

            $s_times = cut_send_times($rst);
            $this->assign('s_times', $s_times);
       
            $this->display();
            
        }else{

            redirect(U('Client/Restaurant/lists'));
        }
    }


    // 购物车
    function cart(){

        if(IS_POST){
            
            if(is_null(cookie('pltf2_order_cookie'))){
        //检错*********************************************
                // echo "111";die;
                $this->success('美食篮空空如也，快去挑选餐厅选餐吧！', U('Client/Restaurant/lists'), 3);
            }else{

                // p(cookie());die;

                $rst = session('pltf2_curRst_info');
                $rst = $this->update_curRstInfo($rst['r_ID']);// 更新餐厅信息

                // echo "222";die;
                $this->display();
            }

        }else{
            redirect(U('Client/Restaurant/lists'));
        }
    }


    // 对应餐厅的菜单
    function menu(){

        if (IS_POST) {

            // p(I('post.'));die;
            if(I('post.r_ID') == ""){
        //检错*********************************************
                $this->error('Something Wrong！', U('Client/Restaurant/lists'));
            }

            $r_ID = I('post.r_ID') / 10086;//简单加密的解密
            $rst = $this->update_curRstInfo($r_ID);// 获取餐厅信息，写入session和cookie

            $map['r_ID'] = $r_ID;
            $data = M('menu')->where($map)->select();

            // 如果是app来的访问，返回json
            if (I('get.srcid') == '10086') {
                
                $JSON['data'] = $data;
                // $JSON['data']['menus'] = $data;
                // $JSON['data']['rst'] = $rst;

                echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
                return;
            }

            $this->assign('data', $data);//菜单列表
            $this->assign('rst', $rst);//餐厅信息

            $this->display();
        }else{

            if(!session('?pltf2_curRst_info')){
        //检错*********************************************
                redirect(U('Client/Restaurant/lists'));
            }
            // p(session('pltf2_curRst_info'));die;
            // $rst = json_decode(cookie('pltf2_curRst_info'),true);

            $rst = session('pltf2_curRst_info');
            $rst = $this->update_curRstInfo($rst['r_ID']);// 更新餐厅信息

            $map['r_ID'] = $rst['r_ID'];
            $data = M('menu')->where($map)->select();

            // 如果是app来的访问，返回json
            // if (I('get.srcid') == '10086') {
                
            //     $JSON['data'] = $data;
            //     // $JSON['data']['menus'] = $data;
            //     // $JSON['data']['rst'] = $rst;

            //     echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
            //     return;
            // }
            
            $this->assign('data', $data);//菜单列表
            $this->assign('rst', $rst);//餐厅信息

            $this->display();
        }
    }

    // 私有方法，用于获取/更新当前餐厅信息
    private function update_curRstInfo($r_ID){

        $model = D("RestaurantView");
        $map['userStatus'] = 1;// 餐厅账号开启
        $map['serviceStatus'] = 1;// 餐厅服务开启
        // 过滤得到符合展示要求的餐厅
        $map['r_ID'] = $r_ID;
        $rst = $model->where($map)->find();

        if(is_null($rst)){
    //检错*********************************************
            // 餐厅不存在，或餐厅已关闭
            $this->error('Something Wrong！', U('Client/Restaurant/lists'));
            return;
        }

        $rst = rstInfo_combine($rst);// 组装，订餐页面所需要的餐厅营业时间和销量

        session('pltf2_curRst_info', $rst);//将当前选择的餐厅信息写入session
        $json_rst = json_encode($rst, JSON_UNESCAPED_UNICODE);// unicode格式
        cookie("pltf2_curRst_info", $json_rst);//将当前选择的餐厅信息写入cookie
        // p(cookie("pltf2_curRst_info"));die;

        return $rst;
    }


// *******************接口1，查询手机号是否已注册

// 能用到以下接口，说明该用户是已经注册的
// *******************接口2，供用户更新地址
// *******************接口3，供用户删除地址
// *******************接口4，供用户增加地址


    /**
     * <interface>,判断手机号是否已注册
     * 需要数据：phone
     * 已注册，则返回"用户信息+地址信息"
     * 未注册，返回errcode=9001005，获取用户信息失败
     */
    function is_phone_exists(){

        if (!$phone = I('post.phone')) {
            $this->error('参数错误！');
        }

        // $phone = '18888888889';// test
        // $phone = '18826481053';// test

        $one = is_exists_phone($phone);

        if (!$one) {

            $data['errcode'] = '9001005';
            $data['errmsg'] = '获取用户信息失败';

            $JSON = $data;
        }else {

            $data['client_ID'] = $one['client_ID'];
            $data['name'] = $one['name'];
            $data['phone'] = $one['phone'];

            $data['addr'] = get_client_address($one['client_ID'], false);// 如果找不到，为NULL

            $JSON['data'] = $data;
        }

        // 如果是app来的访问，返回json
        if (I('get.srcid') == '10086') {

            echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
            return;
        }

        // echo json_encode($data, JSON_UNESCAPED_UNICODE);
        // p($data);die;

        $this->ajaxReturn($data, 'json');
    }

    /**
     * <interface>,更新地址
     * 需要数据：address_ID, address
     * 成功，返回影响的数据行数
     * 失败，返回errcode=9001017，更新地址信息失败
     */
    function update_addr(){

        /*
        Array
        (
            [address_ID] => 556
            [address] => 华山17栋
        )
        */
        $new_addr['address_ID'] = I('post.address_ID');
        $new_addr['address'] = I('post.address');

        $model = M('client_address');
        $res = $model->save($new_addr);

        if (!$res) {

            $data['errcode'] = '9001017';
            $data['errmsg'] = '更新地址信息失败';
        }else{

            $data['result'] = $res;
        }

        // 如果是app来的访问，返回json
        if (I('get.srcid') == '10086') {
            
            $JSON = $data;

            echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
            return;
        }

        $this->ajaxReturn($data, 'json');
    }

    /**
     * <interface>,删除地址
     * 需要数据：address_ID
     * 成功，返回影响的数据行数
     * 失败，返回errcode=9001016，删除地址信息失败
     */
    function del_addr(){

        /*
        Array
        (
            [address_ID] => 556
        )
        */
        $map['address_ID'] = I('post.address_ID');

        $model = M('client_address');
        $res = $model->where($map)->delete();

        if (!$res) {

            $data['errcode'] = '9001016';
            $data['errmsg'] = '删除地址信息失败';
        }else{

            $data['result'] = $res;
        }

        // 如果是app来的访问，返回json
        if (I('get.srcid') == '10086') {
            
            $JSON = $data;

            echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
            return;
        }

        $this->ajaxReturn($data, 'json');
    }

    /**
     * <interface>,新增地址
     * 需要数据：client_ID, address
     * 成功，返回新增地址的address_ID
     * 失败，返回errcode=9001015，新增地址信息失败
     */
    function add_addr(){

        /*
        Array
        (
            [client_ID] => 10000
            [address] => 华山17栋
        )
        */
        $new_addr['client_ID'] = I('post.client_ID');
        $new_addr['address'] = I('post.address');

        // $new_addr['client_ID'] = 10000;
        // $new_addr['address'] = 'huashan 17 in SCAU';
        
        $model = M('client_address');
        $res = $model->add($new_addr);

        if (!$res) {

            $data['errcode'] = '9001015';
            $data['errmsg'] = '新增地址信息失败';
        }else{

            $data['result'] = $res;
        }

        // 如果是app来的访问，返回json
        if (I('get.srcid') == '10086') {

            $JSON = $data;

            echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
            return;
        }

        $this->ajaxReturn($data, 'json');
    }


// *******************下单接口，验证订单，更新用户信息
// *******************查询订单接口


    /**
     * 处理订单信息
     * @param  array $order 订单信息数组
     * @return array        成功，返回影响的数据行数；失败，返回errcode=40035，不合法的参数
     */
    function handle_order($order){

        // 检验数组，确保需要用到的"键"都存在，array_key_exists(key,array)
        //      即，只能判断传过来的数据是否少了，判断不了传过来的数据是否多了
        //          而即使数据多了，也是被忽略不处理的，所以也OK
        // 然后是get_client_ID()
        // 完了组装数据写入数据库

    /*
        $order['r_ID'] = 0;
        $order['total'] = 1;
        $order['item'] = 2;
        $order['c_name'] = 3;
        $order['c_address'] = 4;
        $order['c_phone'] = 5;
        $order['note'] = 6;
        $order['deliverTime'] = 7;
        $order['cTime'] = 8;
    */

        // p($order);

        // 检验订单信息数组，确保需要用到的"键"都存在
        $valid = check_order_array_key_exists($order);

        if (!$valid) {
            
            $data['errcode'] = '40035';
            $data['errmsg'] = '不合法的参数';

            return $data;

            // $JSON = $data;

            // echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
            // return;
        }

        // echo "键是否都存在？<br/>";
        // dump($valid);
        // die;


        // ************以下为得到用户的client_ID
        // 首先，需要明确的是，当用户能够提交数据至此方法时，
        // 说明已经取得了有其手机号、地址、姓名，但这里不能确定该用户是否已注册
        // 所以要做下面的工作：
        // 检查该手机号对应的用户是否已经存在
        //      不存在，则应先为该用户(隐性)注册，得到其client_ID
        //      存在，得到其client_ID
        $client_ID = get_client_ID($order);

        // dump( $client_ID );
        // die;

        $r_ID = $order['r_ID'];
        $guid = strval(1800 + mt_rand(1, 5000)).strval($r_ID).strval(NOW_TIME);//19位
        // echo "guid = ".$guid;

        $temp['guid'] = $guid;
        $temp['r_ID'] = $r_ID;
        $temp['client_ID'] = $client_ID;

        $temp['name'] = $order['c_name'];
        $temp['address'] = $order['c_address'];
        $temp['phone'] = $order['c_phone'];
        $temp['total'] = $order['total'];
        $temp['order_info'] = $json_order;
        $temp['cTime'] = $order['cTime'];

        // p($temp);
        // die;

        $model = M('orderitem');
        $res = $model->add($temp);

        if (!$res) {
            
            $data['errcode'] = '40035';
            $data['errmsg'] = '不合法的参数_';
        }else {

            $data['result'] = $res;
        }

        return $data;

        // $JSON = $data;
        // echo json_encode($JSON, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取客户1个月内的历史订单
     * @param  int $client_ID 客户ID
     * @return array          历史订单
     */
    function get_his_orders($client_ID){

        $map['client_ID'] = $client_ID;
        $model = D('OrderView');

        $today = date('Y-m-d');//今日
        $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳
        $last_month_days = getLastMonth_StartAndEnd($today);//上月第1日和最后1日，数组时间戳

        // 上月底到本月底的订单
        $t_brief = $model->where($map)->where("cTime between '"
            .date('Y-m-d H:i:s',$last_month_days[1])."' and '"
            .date('Y-m-d H:i:s',$month_days[1])."'")->order('cTime desc')->field('guid,cTime,r_ID,total,status,reason')->select();

        if(!is_null($t_brief)){
        // 存在订单数据

            // 转换时间显示格式
            foreach ($t_brief as $one) {

                $one['cTime'] = date('n.d H:i', strtotime($one['cTime']));

                $data[] = $one;
                // p($one);die;
            }
            // p($data);die;

            return $data;
        }

        return null;
    }

}