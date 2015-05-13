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

        // 将餐厅分为"营业"和"非营业"2类
        classify_open_n_close_rsts($rsts, $open_rsts, $close_rsts);
        
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


    /**
     * 餐厅信息
     */
    function info(){

        if (!$r_ID = I('get.r_ID')) {

            // r_ID为空
            $this->error("Something wrong！");
            return;
        }

        $map['r_ID'] = $r_ID / 10086;// 简单解密
        $data = M('restaurant')->where($map)->find();
        // p($data);die;
        if($data){
            $this->assign('data', $data);
        }

        $this->display();
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
}

?>