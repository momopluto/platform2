<?php
namespace Client\Model;
use Think\Model\ViewModel;

/**
 * 订单信息模型
 * 
 */
class OrderViewModel extends ViewModel {

	public $viewFields = array(
        
        'orderitem'=>array('order_ID','guid','r_ID','client_ID','name'=>'c_name','address'=>'c_address','phone'=>'c_phone',
            'total','order_info','cTime','rTime','status','reason','_table'=>"orderitem"),
        'restaurant'=>array('logo_url','name'=>'r_name','phone'=>'r_phone', '_on'=>'restaurant.r_ID=orderitem.r_ID'),
    );

/*
 Array
(
    [order_ID] => 10001
    [guid] => 1803104561418490416
    [r_ID] => 30000
    [client_ID] => 10000
    [c_name] => 刘恩坚
    [c_address] => 华山17栋
    [c_phone] => 18826481053
    [total] => 24
    [order_info] => {"r_ID":"30000","total":"24","item":[{"entity_id":"5","name":"小黄闷鸡米饭（学生价）","price":"12","count":"2","total":"24"}],"note":"","c_name":"刘恩坚","c_address":"华山17栋","c_phone":"18826481053","deliverTime":"尽快送出","cTime":"2015-06-26 21:58:38"}
    [cTime] => 2015-06-26 21:58:38
    [rTime] => 
    [status] => 1
    [reason] => 
    [logo_url] => http://momopluto.xicp.net/platform/Application/Uploads/rst_logo/default_rst_logo.jpg
    [r_name] => 黄小吉
    [r_phone] => 15876502162
)
*/
}