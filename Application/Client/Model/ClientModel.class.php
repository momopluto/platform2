<?php
namespace Client\Model;
use Think\Model;

/**
 * 客户模型
 * 
 */
class ClientModel extends Model {

	protected $tableName = 'client';// 数据表名
    protected $fields    = array('client_ID','phone','name','reg_time');// 字段信息
    protected $pk        = 'client_ID';// 主键

    // 自动验证
    protected $_validate = array(
        array('phone','','此手机号已注册！',self::EXISTS_VALIDATE,'unique',self::MODEL_INSERT),
        array('name','require','称呼不能为空！'), //默认情况下用正则进行验证
    );

    // 自动完成
    protected $_auto = array (
        array('reg_time','getDatetime',self::MODEL_INSERT,'function') , // 新增的时候把调用getDatetime方法记录时间
    );

}