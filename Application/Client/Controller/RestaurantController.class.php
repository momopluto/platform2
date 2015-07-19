<?php
namespace Client\Controller;
use Think\Controller;
class RestaurantController extends ClientController {

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


        // 如果是app来的访问，返回json
        if (I('get.srcid') == '10086') {
            
            $JSON['data']['open_rsts'] = $open_rsts;
            $JSON['data']['close_rsts'] = $close_rsts;

            echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
            return;
        }
        
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

            // 如果是app来的访问，返回json
            if (I('get.srcid') == '10086') {
                
                $JSON['data']['rst'] = $data;

                echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
                return;
            }

            $this->assign('data', $data);
        }

        $this->display();
    }


// *******************提供查询餐厅状态接口

    /**
     * <interface>,获取餐厅营业状态
     * 需要数据：r_ID
     * 成功，返回餐厅营业状态
     * 失败，返回"错误代码+错误提示"
     */
    function getStatus(){

        $r_ID = I('post.r_ID');
        // $r_ID = 30000;
        $an_rst = M('restaurant')->find($r_ID);
        // p($an_rst);die;

        if (!$an_rst) {
            
            $data['errcode'] = '46002';
            $data['errmsg'] = '不存在的餐厅';

        }else {

            $data['result'] = get_format_open_status($an_rst);
        }
        // p($data);die;

        // 46001，不存在的用户
        // 46002，不存在的餐厅
        // 46003，不存在的菜单
        // 46004，不存在的订单

        // 如果是app来的访问，返回json
        if (I('get.srcid') == '10086') {
            
            $JSON = $data;

            echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
            return;
        }

        $this->ajaxReturn($data, 'json');
    }
}

?>