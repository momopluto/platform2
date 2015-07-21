<?php
namespace Home\Model;
use Think\Model;

/**
 * 餐厅用户模型
 *
 */
class RestaurantModel extends Model {

    protected $_validate = array(
        array('name','require','餐厅名不能为空！',self::MUST_VALIDATE),
        array('address','require','餐厅地址不能为空！',self::MUST_VALIDATE),
        array('desc','require','餐厅简介不能为空！',self::MUST_VALIDATE),
        array('phone','require','联系电话不能为空！',self::MUST_VALIDATE),
        array('promotion_info','require','餐厅公告信息不能为空！',self::MUST_VALIDATE),
        array('agent_fee','require','外加配送费不能为空！',self::MUST_VALIDATE),
        array('deliver_desc','require','起送说明不能为空！',self::MUST_VALIDATE),
        array('time_1_open','require','第一营业时间不能为空！',self::MUST_VALIDATE),
        array('time_1_close','require','第一营业时间不能为空！',self::MUST_VALIDATE),
    );
}

?>