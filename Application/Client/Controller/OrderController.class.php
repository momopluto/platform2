<?php
namespace Client\Controller;
use Think\Controller;
class OrderController extends ClientController {


    /**
     * 查询历史订单
     * 分PC和移动端处理
     */
    function myOrder(){

        // echo "orders can work!";

        // p(session());
        // p(cookie());
        // die;

        if (I('get.srcid') == null) {// ***********************PC端
            
            // pc
            // 直接用session中的标识
            $data = get_his_orders(session('CLIENT_ID'));

            // p($data);die;

            if (!$data['errcode']) {

                $this->assign('data', $data);
            }

            $this->display('order');
        }else {// *****************************非PC端，如：移动端
            if (I('get.srcid') == '10086') {// 且srcid是指定的值
                
                // 根据post过来的client_ID
                $data = get_his_orders(I('post.client_ID'));

                if (!$data['errcode']) {
                    
                    $JSON['data'] = $data;

                    echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
                    return;
                }
                
                echo json_encode($data, JSON_UNESCAPED_UNICODE); 
            }else {

                // do nothing
                return;
            }
        }
    }

    // 订单详情
    function detail(){

        if (I('get.srcid') == null) {// ***********************PC端
            
            $guid = I('get.id');
            if($guid == null){

                $this->error('无效订单号！');
                return;
            }

            $data = $this->get_order_detail($guid);

            if (!$data['errcode']) {
                $data['r_ID'] = 10086 * $data['r_ID'];//此处rid加密
                $this->assign('data', $data);
            }

            $this->display();

        }else {// *****************************非PC端，如：移动端
            if (I('get.srcid') == '10086') {// 且srcid是指定的值
                
                $guid = I('post.guid') /*'1803104561418490416'*/;

                $data = $this->get_order_detail($guid);

                if (!$data['errcode']) {
                    
                    $JSON['data'] = $data;

                    echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
                    return;
                }
                
                echo json_encode($data, JSON_UNESCAPED_UNICODE); 
            }else {
                // do nothing
                return;
            }
        }
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



    // 对应餐厅的菜单
    function menu(){

        // p(cookie());die;

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

                $data = $this->handle_order($json_order);

                // p($data);die;

                if ($data['result']) {// 成功写入数据库

                    // 清除session和cookie
                    session('pltf2_curRst_info', null);
                    cookie('pltf2_curRst_info', null);

                    // 以下2句代码须同时使用，且顺序不能调换
                    cookie('pltf2_order_cookie', null);// 删thinkphp中的cookie
                    setcookie("pltf2_order_cookie", "", time()-1);// 真正从浏览器中删除

                    // PC端，用cookie保存用户的送餐信息
                    $order = json_decode($json_order, true);
                    $c_info['name'] = $order['c_name'];
                    $c_info['phone'] = $order['c_phone'];
                    $c_info['address'] = $order['c_address'];

                    cookie('C_INFO', json_encode($c_info), 36000000);

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
                // 问题确定，因为post过来的数据order字符串中的 "字符 被I方法转义为 &quot;
                // json_decode不能将其解析为json，所以直接decode结果为null
                
                $json_order = str_replace ( '&quot;', '"' , $json_order );// 将I方法转义的"字符恢复

                // var_dump($json_order);
                // die;

                $data = $this->handle_order($json_order);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }else {

                redirect(U('Client/Restaurant/lists'));
                return;
            }
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
     * @param  Array $order 订单信息数组
     * @return Array        成功，返回影响的数据行数；失败，返回errcode=40035，不合法的参数
     */
    function handle_order($json_order){

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

        // echo $json_order;
        $order = json_decode($json_order, true);

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

        // p($model);

        if (!$res) {
            
            $data['errcode'] = '40035';
            $data['errmsg'] = '不合法的参数_';
        }else {

            $data['result'] = $res;// 订单ID, order_ID
        }

        return $data;

        // $JSON = $data;
        // echo json_encode($JSON, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取订单信息详情
     * @param  init $guid 订单号
     * @return Array      成功，返回订单详情；失败，返回"错误码+错误信息"
     */
    function get_order_detail($guid){

        if ($guid == null) {
            
            $data['errcode'] = '40035';
            $data['errmsg'] = '不合法的参数';

            return $data;
        }

        $map['guid'] = $guid;
        $model = D('OrderView');

        $an_order = $model->where($map)->find();
        // p($an_order);die;

        if ($an_order) {
            
            $order_info = json_decode($an_order['order_info'],true);
            unset($an_order['order_info']);

            $an_order['note'] = $order_info['note'];
            $an_order['deliverTime'] = $order_info['deliverTime'];

            $an_order['item'] = $order_info['item'];
            $i_count = 0;
            foreach ($an_order['item'] as $an_item) {
                $i_count += $an_item['count'];
            }

            $an_order['item_count'] = '' . $i_count;
            // p($an_order);

            $data = $an_order;

        }else {

            $data['errcode'] = '46004';
            $data['errmsg'] = '不存在的订单';
        }

        return $data;
    }

}