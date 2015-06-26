<?php
namespace Client\Model;
use Think\Model\ViewModel;

/**
 * 订单信息模型
 * 
 */
class OrderViewModel extends ViewModel {

	public $viewFields = array(
        
        'orderitem'=>array('order_ID','guid','r_ID','client_ID','name'=>'c_name','address','phone',
            'total','order_info','cTime','rTime','status','reason','_table'=>"orderitem"),
        'restaurant'=>array('logo_url','name'=>'r_name', '_on'=>'restaurant.r_ID=orderitem.r_ID'),
    );

/*

*/
}