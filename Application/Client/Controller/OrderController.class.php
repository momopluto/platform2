<?php
namespace Client\Controller;
use Think\Controller;
class OrderController extends ClientController {



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


    // 下单成功与否
    function done(){
        // echo NOW_TIME;die;
        if(IS_POST){

            // p(cookie('pltf2_curRst_info'));
            // p(cookie('pltf_order_cookie'));die;
            // p(`());die;
            // p(cookie());die;

            $json_order = cookie('pltf_order_cookie');
            if(!$json_order){
        //检错*********************************************
                $this->error('Something Wrong！', U('Client/Restaurant/lists'));
            }



            $order = json_decode($json_order, true);
            // p($order);die;

            $rid = $order['rid'];
            $model = M('today', $rid.'_');

            $data['rid'] = $rid;
            if($model->create($data) && $today_sort = $model->add($data)){
                //$guid订单号，$today_sort今天第xx份订单
                $guid = strval(1800 + $today_sort).strval($rid).strval(NOW_TIME);//19位
                // echo "guid = ".$guid."<br/>today_sort = " . $today_sort."<br/>";

                $temp['rid'] = $rid;
                $temp['guid'] = $guid;
                $temp['today_sort'] = $today_sort;


                // 此处的token和openid该从cookie中取
                $temp['token'] = cookie('token');// "gh_34b3b2e6ed7f";
                $temp['openid'] = cookie('openid');// "o55gotzkfpEcJoQGXBtIKoSrairQ";



                $temp['name'] = $order['c_name'];
                $temp['address'] = $order['c_address'];
                $temp['phone'] = $order['c_phone'];
                $temp['total'] = $order['total'];
                $temp['order_info'] = $json_order;
                $temp['cTime'] = $order['cTime'];

                // p($temp);die;

                $_model = M('orderitem', ' ');
                if($id = $_model->add($temp)){
                    // echo '$id = '.$id;//die;

                    // 清除session和cookie
                    session('pltf2_curRst_info', null);
                    cookie('pltf2_curRst_info', null);

                    
                    // 以下2句代码须同时使用，且顺序不能调换
                    cookie('pltf_order_cookie', null);// 删thinkphp中的cookie
                    setcookie("pltf_order_cookie", "", time()-1);// 真正从浏览器中删除
                    
                    // p(session());p(cookie());die;

                // 判断该用户cookie('openid')是否存在于orderman表中
                    // 在，则更新这个用户id下的手机、地址信息cookie，写入数据库；
                    // 不在，则新增入数据库;



                    if (is_weixin()) {//微信浏览器is_weixin()

                        $man_model = M('orderman','admin_');
                        if ($uuu = $man_model->where(array('openid'=>cookie('openid')))->find()) {
                            // 存在该手机号
                            // 更新这个用户id下的地址信息cookie，写入数据库；

                            
                            $update['name'] = $temp['name'];
                            $update['phone'] = $temp['phone'];
                            $update['address'] = $temp['address'];


                            $c_info = json_decode(cookie('c_info'),true);

                            // echo "--".$update['name']."--<br/>";
                            // echo "--".$c_info['name']."--<br/>";

                            if (array_diff($update, $c_info) || array_diff($c_info, $update)) {
                                
                                // 数组内容有不同，更新
                                // p($update);die;

                                cookie('c_info', json_encode($update));
                                
                                $u_id = $uuu['id'];
                                $man_model->where("id = $u_id")->setField($update);
                            }

                        }else{// 新手机号

                            // token、openid、name、phone、address、cTime
                            
                            $update['name'] = $temp['name'];
                            $update['phone'] = $temp['phone'];
                            $update['address'] = $temp['address'];

                            $update['token'] = $temp['token'];
                            $update['openid'] = $temp['openid'];
                            $update['cTime'] = NOW_TIME;

                            // $man_model->create($update) && 
                            $man_model->add($update);
                        }
                    }
                    // 微信浏览器的话没必要，写了cookie也会被清掉，况且进入微信浏览器的时候会从数据库里取phone的cookie

                    // cookie('pltf_phone') = $temp['phone'];// 留下身份唯一标识

                    $this->display();
                }else{
                    $this->error($_model->getError());
                }
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
            $rst = $this->update_curRstInfo($rst['rid']);// 更新餐厅信息

/*  更新餐厅信息
            $rid = $rst['rid'];

            // 重新访问数据库获取信息
            $rst = M('resturant','home_')->where("rid = $rid")->field('rid,logo_url,rst_name,isOpen,rst_is_bookable,rst_agent_fee,
                stime_1_open,stime_1_close,stime_2_open,stime_2_close,stime_3_open,stime_3_close')->find();
            
    
            if (is_null($rst)) {//空则跳转
    
                $this->error('Something Wrong！', U('Client/Restaurant/lists'));
            }

            // if (!$rst['isOpen']) {// isOpen＝0餐厅休息，无法完成下单操作
                
            //     $this->error('餐厅休息！无法下单！', U('Client/Restaurant/lists'));
            // }


            $rst = rstInfo_combine($rst);
            session('pltf2_curRst_info', $rst);//更新当前餐厅信息，写入session

            $rst['logo_url'] = urlencode($rst['logo_url']);//处理logo_url链接
            $json_rst = json_encode($rst);
            // p($rst);die;
            // p($json_rst);die;
            cookie("pltf2_curRst_info", urldecode($json_rst));//更新当前餐厅信息，写入cookie
*/
            // cookie(null,'pltf_'); // 清空指定前缀的所有cookie值

            $s_times = cut_send_times($rst);
            $this->assign('s_times', $s_times);


    // 测试用******************************************************
            // session('pltf_openid', 'o55gotzkfpEcJoQGXBtIKoSrairQ');
            // 根据缓存的session从数据内取name, phone, address信息
            // 完全可以在缓存session时一并将该用户的name, phone, address缓存，则不需要这一步
// ********************************************************************************************
            // if(session('?pltf_openid')){
            //     $map['openid'] = session('pltf_openid');
            //     $c_info = M('orderman', 'admin_')->where($map)->field('name, phone, address')->find();
            //     if(!is_null($c_info)){

            //         $this->assign('c_info', $c_info);
            //         // p($c_info);die;
            //     }
            // }

            
            $this->display();
            
        }else{

            redirect(U('Client/Restaurant/lists'));
        }

    }


    // 购物车
    function cart(){
/*
        if (!is_null(cookie('pltf_order_cookie'))) {
            if(IS_POST){
                $this->display();
            }else{
                $this->error('请在餐厅内下单哦！', 'Client/Restaurant/lists');
            }
        }else{//没有cookie
            if(IS_POST){
                $this->error('美食篮是空的～您还没选餐哦！', 'Client/Restaurant/lists');
            }else{
                $this->error('Something Wrong！', 'Client/Restaurant/lists');
            }
        }
*/
        // p(cookie(''));die;

        if(IS_POST){
            
            if(is_null(cookie('pltf_order_cookie'))){
        //检错*********************************************
                // echo "111";die;
                $this->success('美食篮空空如也，快去挑选餐厅选餐吧！', U('Client/Restaurant/lists'), 3);
            }else{

                // p(cookie());die;

                $rst = session('pltf2_curRst_info');
                $rst = $this->update_curRstInfo($rst['rid']);// 更新餐厅信息

                // echo "222";die;
                $this->display();
            }
                        
        }else{
            redirect(U('Client/Restaurant/lists'));
        }

    }


    // 对应餐厅的菜单
    function menu(){
        
        // cookie('pltf_order_cookie',null);
        // cookie('pltf2_curRst_info',null);
        // p(cookie(''));die;

        // $r_ID = 10456;//伪造数据，测试

        if (IS_POST) {


            // p(I('post.'));die;
            if(I('post.r_ID') == ""){
        //检错*********************************************
                $this->error('Something Wrong！', U('Client/Restaurant/lists'));
            }


            $r_ID = I('post.r_ID') / 10086;//简单加密的解密
            $rst = $this->update_curRstInfo($r_ID);// 获取餐厅信息，写入session和cookie

/*  获取餐厅信息
            // 得到r_ID餐厅信息
            $rst = M('resturant','home_')->where("r_ID = $r_ID")->field('r_ID,logo_url,rst_name,isOpen,rst_is_bookable,rst_agent_fee,
                stime_1_open,stime_1_close,stime_2_open,stime_2_close,stime_3_open,stime_3_close')->find();


            if(is_null($rst)){

                $this->error('Something Wrong！', U('Client/Restaurant/lists'));   
            }


            $rst = rstInfo_combine($rst);// 订餐页面所需要的餐厅的信息，组装

            session('pltf2_curRst_info', $rst);//将当前选择的餐厅信息写入session

            $rst['logo_url'] = urlencode($rst['logo_url']);//处理logo_url链接
            $json_rst = json_encode($rst);
            // p($rst);
            // p($json_rst);die;
            cookie("pltf2_curRst_info", urldecode($json_rst));//将当前选择的餐厅信息写入cookie

            // p(session('pltf2_curRst_info'));die;
*/

            // $data = M('menu',$r_ID.'_')->select();

            $map['r_ID'] = $r_ID;
            $data = M('menu')->where($map)->select();

            // p($data);die;


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

}