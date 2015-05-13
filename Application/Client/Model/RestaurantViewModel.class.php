<?php
namespace Client\Model;
use Think\Model\ViewModel;

/**
 * 餐厅信息模型
 * 
 */
class RestaurantViewModel extends ViewModel {

	public $viewFields = array(
        
        'restaurant'=>array('r_ID','home_ID','logo_url','name','address','desc'=>'descr'/*desc为关键字*/,'phone','promotion_info','deliver_desc','agent_fee','isOpen','is_bookable',
            'time_1_open','time_1_close','time_2_open','time_2_close','time_3_open','time_3_close','warning_tone','_table'=>"restaurant"),
        'home_user'=>array('admin_ID','status' => 'userStatus', '_on'=>'restaurant.home_ID=home_user.home_ID'),

        'service'=>array('service_ID','status' => 'serviceStatus', '_on'=>'restaurant.r_ID=service.r_ID'),
    );

/*
        (
            [r_ID] => 30000
            [home_ID] => 788
            [logo_url] => http://momopluto.xicp.net/platform/Application/Uploads/rst_logo/default_rst_logo.jpg
            [name] => 黄小吉
            [address] => 华农西门长福路ONG创意园
            [descr] => 健康最重要，小吉马上到！每天专注于一道菜！
            [phone] => 15876502162
            [promotion_info] => 黄小吉吉吉吉吉吉吉
            [deliver_desc] => 华农的同学们可以填写短号哦，华工和跃进的同学们尽量几个宿舍一起订，满6份，送可乐大炮一瓶，拍下才有哦。谢谢！662162（华农短号）或 15876502162
            [agent_fee] => 10
            [isOpen] => 1
            [is_bookable] => 1
            [time_1_open] => 11:00:00
            [time_1_close] => 14:00:00
            [time_2_open] => 16:00:00
            [time_2_close] => 18:00:00
            [time_3_open] => 19:00:00
            [time_3_close] => 23:59:00
            [warning_tone] => 0
            [admin_ID] => 200
            [userStatus] => 1
            [service_ID] => 101
            [serviceStatus] => 1
        )
*/
}