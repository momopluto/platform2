<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 订单管理
 * 
 */
class OrderitemController extends HomeController {

    function _initialize() {
        parent::_initialize ();
        
        // $this->model = M ('orderitem', ' ');
    }

    // 订单列表
    function lists(){

        if(I('get.date')){// 过滤日期
            $the_day = I('get.date');                
        }else{//无，则默认为今天
            $the_day = date('Y-m-d');
        }

        $whe['home_ID'] = session('HOME_ID');
        $r_IDs = M('restaurant')->where($whe)->getField('r_ID,name');

        $model = M('orderitem');
        // $map['status'] = array('neq', 0);// 已处理的订单
        foreach ($r_IDs as $r_ID => $name) {

            $map['r_ID'] = array('eq',$r_ID);
            $orders = $model->where($map)->where("DATE_FORMAT(cTime,'%Y-%m-%d')='" . $the_day . "'")->select();

            // p($model);
            // p($orders);
            // die;

            $total_1 = 0;// 有效订单的，总金额
            $count_1 = 0;// 有效订单的，总数
            foreach ($orders as $key => $value) {

                // 去除，"未处理"的新订单
                // 为什么不在过滤的时候去除？因为要得到一家餐厅所有订单的顺序，所以采用unset的方式
                // 这样之后，键key就是该订单当天的顺序
                if ($value['status'] == 0) {
                    unset($orders[$key]);
                    continue;
                }

                // 顺便统计有效订单的相关数据
                if($value['status'] != 3){
                    $total_1 += $value['total'];
                    $count_1 ++;
                }
                
                $orders[$key]['order_info'] = json_decode($orders[$key]['order_info'], true);

                $ph['phone'] = $orders[$key]['phone'];
                $ph_count = $model->where($ph)->count();
                // echo $ph_count;

                // 判断该手机用户是否为新用户
                // 不准确，如果新用户连续下2单以上，则就不是新用户了
                if ($ph_count == 1) {
                    $orders[$key]['isNewer'] = true;
                }else{
                    $orders[$key]['isNewer'] = false;
                }
            }

            $count_all = count($orders);//订单总数

            $data[$r_ID]['name'] = $name;
            $data[$r_ID]['count_all'] = $count_all;//订单总数(有效&无效)
            $data[$r_ID]['total_1'] = $total_1;// 有效订单的，总金额
            $data[$r_ID]['count_1'] = $count_1;// 有效订单的，总数
            $data[$r_ID]['orders'] = $orders;
        }

        // foreach ($data as $key => $one_data) {
        //     foreach ($one_data['orders'] as $one_order) {
        //         echo "<pre/>";
        //         p($one_order);
        //     }
        // }

        // die;
        // p($data);die;

        $this->assign('the_day', $the_day);
        $this->assign('data', $data);

        $this->display();
    }
   

    // 确认订单
    function confirm(){
        // p(I('get.'));die;

        $r_ID = I('get.rid');
        if (!is_rID_valid($r_ID)) {

            $this->error('餐厅ID不合法！');
            return;
        }

        $guid = I('get.guid');
        if ($guid == '') {
            $this->error('订单号不合法！');
            return;
        }

        $order_map['r_ID'] = $r_ID;
        $order_map['guid'] = $guid;

        $model = M('orderitem');
        $an_order = $model->where($order_map)->find();
        
        // p($an_order);
        // die;
        
        if($an_order['rTime'] == ''  && $an_order['status'] == 0){// 未确认订单

            $update['rTime'] = getDatetime();
            $update['status'] = 1;
        }else{

            $this->error('该订单已被处理！请勿重复操作！');
            return;
        }

//***************************对应菜单销量、库存更新***************************
        $order_info = json_decode($an_order['order_info'],true);
        // p($order_info);
        // die;
        
        $menu_map['r_ID'] = $r_ID;
        $m_model = M('menu');

        // 检查用户的选单数据合法性
        foreach ($order_info['item'] as $an_menu) {

            $menu_map['menu_ID'] = $an_menu['entity_id'];
            $menu_map['name'] = $an_menu['name'];

            if ($m_model->where($menu_map)->count() == 0) {
                
                $this->error("此订单非法！请联系顾客取消订单！");// --".$menu_map['menu_ID'].$menu_map['name']."--
                return;
            }
        }

        // echo "订单合法~~~~~~";
        // die;

        $m_model->startTrans();// menu表，开启事务
        // 至此则认为选单数据合法，可以更新menu
        foreach ($order_info['item'] as $an_menu) {

            $menu_map['menu_ID'] = $an_menu['entity_id'];
            $menu_map['name'] = $an_menu['name'];

            // 更新对应菜式的销量 和 库存
            if (!$m_model->where($menu_map)->setInc('month_sales',$an_menu['count'])
             || !$m_model->where($menu_map)->setDec('stock',$an_menu['count'])) {
                
                $m_model->rollback();
                $this->error('确认订单失败！菜单数据更新错误！');
                return;
            }
        }
//***************************对应菜单销量、库存更新***************************

        $model->startTrans();// orderitem表，开启事务
        if (!$model->where($order_map)->setField($update)) {

            $model->rollback();
            $m_model->rollback();

            $this->error('确认订单失败！订单状态更新错误！');
            return;
        }else{
            $model->commit();
            $m_model->commit();

            $this->success ( '订单确认成功！',U("Home/Orderitem/newOrders"),1);
        }  
    }


    // 设为无效
    function setInvalid(){
        // $this->ajaxreturn(I('post.'), 'json');
        // die;

        if (IS_AJAX) {
            $r_ID = I('post.rid');
            if (!is_rID_valid($r_ID)) {

                $this->error('餐厅ID不合法！');
                return;
            }

            $guid = I('post.guid');
            if ($guid == '') {
                $this->error('订单号不合法！');
                return;
            }

            $map['r_ID'] = $r_ID;
            $map['guid'] = $guid;

            $model = M('orderitem');
            $an_order = $model->where($map)->find();
            
            // p($an_order);
            // die;
            
            if($an_order['rTime'] == ''  && $an_order['status'] == 0){// 未确认订单

                $update['rTime'] = getDatetime();
                $update['status'] = 3;
                $update['reason'] = I('post.reason');
            }else{

                $this->error('该订单已被处理！请勿重复操作！');
                return;
            }

            if ($model->where($map)->setField($update)) {

                $this->success ( '订单设为无效成功！',U("Home/Orderitem/newOrders"));
            }else {
                $this->error ( '订单设为无效失败！',U("Home/Orderitem/newOrders"));
            }
            return;
        }
    }

    // 新订单查看
    function newOrders(){
        // 展示出未确认的订单
        // 订单的信息

        // echo NOW_TIME;die;
        $whe['home_ID'] = session('HOME_ID');
        $r_IDs = M('restaurant')->where($whe)->getField('r_ID,name');

        // p($r_IDs);die;

        // $r_ID = 30000;// 测试用
        $model = M('orderitem');
        $the_day = date('Y-m-d');
        // $map['status'] = 0;// 未确认
        foreach ($r_IDs as $r_ID => $name) {

            
            $map['r_ID'] = $r_ID;
            $orders = $model->where($map)->where("DATE_FORMAT(cTime,'%Y-%m-%d')='" . $the_day . "'")->order('cTime')->select();

            // p($orders);
            // p($model);
            // die;

            foreach ($orders as $key => $value) {

                // 保留"未处理"的新订单
                // 为什么不在过滤的时候去除？因为要得到一家餐厅所有订单的顺序，所以采用unset的方式
                // 这样之后，键key就是该订单当天的顺序
                if ($value['status'] != 0) {
                    unset($orders[$key]);
                    continue;
                }
                
                $orders[$key]['order_info'] = json_decode($orders[$key]['order_info'], true);

                $ph['phone'] = $orders[$key]['phone'];
                $ph_count = $model->where($ph)->count();
                // echo $ph_count;

                // 判断该手机用户是否为新用户
                // 不准确，如果新用户连续下2单以上，则就不是新用户了
                if ($ph_count == 1) {
                    $orders[$key]['isNewer'] = true;
                }else{
                    $orders[$key]['isNewer'] = false;
                }
            }

            // p($orders);
            // // // p($model);
            // die;

            $data[$r_ID]['name'] = $name;
            $data[$r_ID]['orders'] = $orders;
        }

        // foreach ($data as $key => $one_data) {
        //     foreach ($one_data['orders'] as $one_order) {
        //         echo "<pre/>";
        //         p($one_order);
        //     }
        // }

        // die;
        // p($data);die;
        

        $this->assign('data', $data);
        $this->display();
    }

    // 顾客订单记录（AJAX调用）
    function getHisOrder(){

        // $this->ajaxreturn(I('post.'), 'json');
        // die;

        if (IS_AJAX) {
            
            $r_ID = I('post.rid');
            if (!is_rID_valid($r_ID)) {

                $this->error('餐厅ID不合法！');
                return;
            }

            $phone = I('post.phone');
            if ($phone == '' || strlen($phone) != 11) {// 手机号为空，或者手机号位数不是11位
                $this->error('手机号不合法！');
                return;
            }

            $client_ID = I('post.id');
            $map['r_ID'] = $r_ID;
            $map['phone'] = $phone;
            $map['client_ID'] = $client_ID;

            $model = M('orderitem');
            // 从订单列表过来，正常情况说明一定存在相应的数据
            // 如果没有数据，说明r_ID或phone或client_ID这三者至少有1处被篡改，致使无对应数据
            if ($model->where($map)->count() == 0) {
                $this->error('参数非法！');
                return;
            }

            // 以上验证client_ID, r_ID, phone有对应数据
            
            // 获取顾客1个月内的订单记录
            $hisOrders = get_client_his_orders($map);

            $this->ajaxReturn($hisOrders,'json');
        }
    }

    //JS轮询调用的订单监视方法
    public function monitor(){

        if(IS_AJAX){

            $whe['home_ID'] = session('HOME_ID');
            $r_IDs = M('restaurant')->where($whe)->getField('r_ID,name,warning_tone');

            $the_day = date('Y-m-d');
            $map['status'] = 0;// 新订单
            $model = M('orderitem');

            foreach ($r_IDs as $r_ID => $value) {

                $map['r_ID'] = $r_ID;
                $count = $model->where($map)->where("DATE_FORMAT(cTime,'%Y-%m-%d')='" . $the_day . "'")->count();

                $data[$r_ID]['name'] = $value['name'];
                $data[$r_ID]['count'] = $count;
                $data[$r_ID]['warning_tone'] = $value['warning_tone'];
            }

            $this->ajaxreturn($data, 'json');
        }

    }
}

?>