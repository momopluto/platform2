<?php
namespace Client\Controller;
use Think\Controller;
class RestaurantController extends ClientController {


    /**
     * 测试传递JSON数据
     */
    function testJSON(){

        $data = array();
        $data['total'] = 50;
        $data['item'][0] = array('name'=>"菜式1", 'price'=>10, 'count'=>2, 'total'=>20);
        $data['item'][1] = array('name'=>"菜式2", 'price'=>10, 'count'=>3, 'total'=>30);
        $data['note'] = "http://momopluto.xicp.net/platform/Application/Uploads/rst_logo/default_rst_logo.jpg";

        $data = json_encode($data, JSON_UNESCAPED_UNICODE);// unicode格式

        echo $data;
    }

    /**
     * 测试餐厅的视图模型
     */
    function testRestaurantViewModel(){

        $model = D("RestaurantView");
        
        $data = $model->select();
        // p($model);
        // p($data);

        $data = json_encode($data, JSON_UNESCAPED_UNICODE);// unicode格式

        echo $data;
    }

    /**
     * 测试展示餐厅信息
     */
    function lists(){

        $model = D("RestaurantView");
        $map['userStatus'] = 1;// 餐厅账号开启
        $map['serviceStatus'] = 1;// 餐厅服务开启
        // 过滤得到符合展示要求的餐厅
        $rsts = $model->where($map)->select();

        // p($rsts);

        $open_rsts = array();
        $close_rsts = array();
        foreach ($rsts as $an_rst) {

            $an_rst = rstInfo_combine($an_rst);// 订餐页面所需要的餐厅的信息，组装
            
            $key = $an_rst['r_ID'];// 键为餐厅ID

            if($an_rst['isOpen'] == "1"){//主观，营业
                // echo $an_rst['open_status']."status！";die;
                if(intval($an_rst['open_status']) % 10 == 4){//已过餐厅今天的所有营业时间
                    // echo $an_rst['rid']."打烊了啊！";die;
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
        
        // 按营业额排序"营业&非营业餐厅"
        sortBy_sales($open_rsts, $close_rsts);

        // echo "--------------------营业餐厅";
        // p($open_rsts);
        // echo "--------------------休息餐厅";
        // p($close_rsts);

        // 如果是app来的访问，返回json;否则dispaly()
        // isApp();

        $this->assign('open_rsts', $open_rsts);
        $this->assign('close_rsts', $close_rsts);

        $this->display('lists');
    }


    // 进入餐厅选择页面前，取得用户token和openid，然后写入cookie
    function checkUserValid(){

        // $dst1 = "Client/Restaurant/lists";// I('get.dstUrl');
        // $dst2 = "Client/Order/myOrder";// I('get.dstUrl');

        $dst = I('get.dstUrl');
        

        $jump_url = U($dst);

        $jump_url = "http://".$_SERVER['HTTP_HOST'].$jump_url;// 加上域名

        // echo $jump_url;
        // p($_SERVER);die;

        get_zx_userid($jump_url);// 调用卓效获取用户user_id接口，会判断是否在微信打开
    }
    

    // 所有餐厅展示，供选择
    function lists_old(){
        // echo "???";die;
        // cookie('pltf_openid', null);
        // cookie('openid',null);
        // cookie('c_info',null);
        // p(cookie());die;
        // echo I('get.user_id');die;

//PC测试用
        cookie('openid', "3440b6427c8a2d813d43f612a3180a07");
        cookie('token', "gh_34b3b2e6ed7f");// *****************************此处的token最好统一定义

        $model = M('orderman','admin_');
        // 准备好用户的手机、地址信息
        $map['openid'] = cookie('openid');
        $c_info = $model->where($map)->field('name, phone, address')->find();
        if (!is_null($c_info)) {

            cookie('c_info', json_encode($c_info));
        }


/*
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
// ****************************可通过有无cookie('c_info')判断该用户是否为新用户，但Client模块没这必要
            }

            redirect(U("Client/Restaurant/lists"));
        }

        // 不存在get.user_id，也不存在cookie('openid')，出错！重新获取用户user_id
        if (is_null(cookie('openid'))) {

            // echo "222";die;

            $jump_url = U("Client/Restaurant/lists");
            $jump_url = "http://".$_SERVER['HTTP_HOST'].$jump_url;// 加上域名

            get_zx_userid($jump_url);// 调用卓效获取用户user_id接口
        }
        
        // 如果不存在get.user_id，但存在cookie('openid')，则已成功处理掉get.user_id，继续下一步进入选择餐厅界面即可
        // echo "333";die;

        // 即只要存在cookie('openid')，能够标识用户，才满足一切操作的前提

// p(cookie());
// die;
*/

// *************************************************************旧的存用户标识方法
/*
        if(true){//微信，此状态也写入session


            // cookie('token', "gh_34b3b2e6ed7f", 36000000);
            // cookie('openid', "o55gotzkfpEcJoQGXBtIKoSrairQ", 36000000);


            if (!is_null(cookie('openid')) && !is_null(cookie('token'))) {
            //已获得用户token和openid

                if(cookie('openid') != null){// 公众号
                    // openid写入session
                    $map['openid'] = cookie('openid');
                    $c_info = $model->where($map)->field('name, phone, address')->find();
                    if (!is_null($c_info)) {

                        cookie('c_info', json_encode($c_info), 36000000);

                        // p(cookie());die;
                    }
                }
            }

            // if("得到Author.openid"){// 服务号
            //     // 得到openid，并写入session
            //     // 是否粉丝
            // }
        }
*/

        /*暂不开放PC浏览器
        else{//PC浏览器等

            // 暂不开放
            // E("无法打开该链接");

            if(cookie('pltf_phone')){
                // p(cookie());die;
                // 存在用户手机号，但不确定是否是本人，在info.html显示出来后，用户可决定是否修改送餐信息
                // info.html中要特别处理已存在送餐信息的情况
                $map['phone'] = cookie('pltf_phone');
                $c_info = $model->where($map)->field('name, phone, address')->find();
                if (!is_null($c_info)) {

                    cookie('c_info', json_encode($c_info), 36000000);

                    // p(cookie());die;
                }

            }else{
                // 什么都不做
                // 之后步骤同样通过判断session('?pltf_phone')即可
            }
        }
        */



        // 1.在admin_allrst中过滤出开启了平台服务的餐厅
        //      rid、rst_name、token
        // 2.通过以上rid信息，在home_resturant中取得餐厅的详细信息
        //      rid,logo_url,rst_name,isOpen[餐厅状态，主观人为设置是否营业，最高优先级]、
        //      rst_is_bookable,rst_agent_fee,stime_*_open,stime_*_close
        // 3.根据stime_*_open、stime_*_close判断当前是否为"自动"营业时间
        // 4.在[orderitem]/menu中统计上月售、本月售[餐厅排序依据]
        // 5.每家餐厅都整合以上信息，构成两个大的多维数组（open  &  close），以上月售、本月售降序排序
        

//***************此处用公众号原始id
        // $token = "gh_34b3b2e6ed7f";//默认


        // 开启平台服务的餐厅
        $on_rsts = M('allrst','admin_')->where("status=1")->select();
        
        $str = "";
        foreach ($on_rsts as $key => $one_rst) {
            if($key != 0){
                $str .= ",".$one_rst['rid'];
            }else{
                $str .= $one_rst['rid'];
            }
        }
        // $In['rid'] = array('in','10456,10464');
        $In['rid'] = array('in', $str);//过滤条件，rid必须in在$str所给出的范围里

        // 得到以rid为键名的多维数组
        $rsts = M('resturant','home_')->where($In)->getField('rid,logo_url,rst_name,isOpen,rst_is_bookable,rst_agent_fee,
            stime_1_open,stime_1_close,stime_2_open,stime_2_close,stime_3_open,stime_3_close');

        $open_rsts = array();
        $close_rsts = array();
        foreach ($rsts as $key => $an_rst) {

            $an_rst = rstInfo_combine($an_rst);// 订餐页面所需要的餐厅的信息，组装

            if($an_rst['isOpen'] == "1"){//主观，营业
                // echo $an_rst['open_status']."status！";die;
                if(intval($an_rst['open_status']) % 10 == 4){//已过餐厅今天的所有营业时间
                    // echo $an_rst['rid']."打烊了啊！";die;
                    $close_rsts[$key] = $an_rst;
                }else{
                    if($an_rst['rst_is_bookable']){
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

        // 营业/非营业，各自排序
        $today = date('Y-m-d');//今日
        $month_days = getMonth_StartAndEnd($today);//本月第1日和最后1日，数组时间戳

        if (strtotime($today) != $month_days[0]) {
            //不是每月第1天，以本月售为排序标准
            uasort($open_rsts, 'compare_month_sale');//降序
            uasort($close_rsts, 'compare_month_sale');//降序
        }else{
            //本月第1天，以上月销售为排序标准
            uasort($open_rsts, 'compare_last_month_sale');//降序
            uasort($close_rsts, 'compare_last_month_sale');//降序
        }

        // p($open_rsts);
        // echo "<hr/>";
        // p($close_rsts);die;
         
        $this->assign('open_rsts', $open_rsts);
        $this->assign('close_rsts', $close_rsts);

        $this->display();
    }

    // 餐厅信息
    function info(){

        if(session('?pltf_curRst_info')){

            $curRst = session('pltf_curRst_info');

            $rid = $curRst['rid'];
            // echo "$rid";
            $data = M('resturant', 'home_')->where("rid = $rid")->find();
            if($data){
                $this->assign('data',$data);
            }
        }

        $this->display();
    }

}

?>