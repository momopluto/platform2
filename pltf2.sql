/*
Navicat MySQL Data Transfer

Source Server         : mysql
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : pltf2

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2015-07-17 15:32:41
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `admin`
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `admin_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(255) NOT NULL COMMENT '管理员姓名',
  `password` char(32) NOT NULL,
  `res_time` datetime NOT NULL COMMENT '注册时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户状态(是否启用该管理员，0为否，1为启用)',
  PRIMARY KEY (`admin_ID`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=301 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('200', 'admin', '21232f297a57a5a743894a0e4a801fc3', '2015-05-06 21:02:04', '2015-05-06 21:02:07', '1');
INSERT INTO `admin` VALUES ('300', 'admin2', 'c84258e9c39059a89ab77d846ddab909', '2015-05-06 21:03:07', '2015-05-06 21:03:10', '1');

-- ----------------------------
-- Table structure for `client`
-- ----------------------------
DROP TABLE IF EXISTS `client`;
CREATE TABLE `client` (
  `client_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '客户编号',
  `phone` char(32) NOT NULL COMMENT '手机号',
  `name` varchar(45) NOT NULL COMMENT '客户姓名',
  `reg_time` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`client_ID`),
  UNIQUE KEY `phone_UNIQUE` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=10008 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of client
-- ----------------------------
INSERT INTO `client` VALUES ('10001', '13570432706', '沈同学', '2015-05-06 21:04:16');
INSERT INTO `client` VALUES ('10003', '18826666666', '刘恩尖尖尖', '2015-07-14 12:25:12');
INSERT INTO `client` VALUES ('10007', '18827777777', '刘恩32111', '2015-07-16 20:00:40');

-- ----------------------------
-- Table structure for `client_address`
-- ----------------------------
DROP TABLE IF EXISTS `client_address`;
CREATE TABLE `client_address` (
  `address_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '地址流水号',
  `address` varchar(255) NOT NULL COMMENT '详细地址',
  `client_ID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`address_ID`),
  KEY `client_ID` (`client_ID`),
  CONSTRAINT `client_address_ibfk_1` FOREIGN KEY (`client_ID`) REFERENCES `client` (`client_ID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=570 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of client_address
-- ----------------------------
INSERT INTO `client_address` VALUES ('558', '华山23栋', '10001');
INSERT INTO `client_address` VALUES ('559', '华山8栋', '10001');
INSERT INTO `client_address` VALUES ('561', '华山22栋', '10001');
INSERT INTO `client_address` VALUES ('562', '华山55', '10001');
INSERT INTO `client_address` VALUES ('563', '华山36', '10001');
INSERT INTO `client_address` VALUES ('565', '17栋205', '10003');
INSERT INTO `client_address` VALUES ('569', '17.205 55555555', '10007');

-- ----------------------------
-- Table structure for `home_user`
-- ----------------------------
DROP TABLE IF EXISTS `home_user`;
CREATE TABLE `home_user` (
  `home_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商家ID',
  `admin_ID` int(10) unsigned NOT NULL,
  `username` varchar(255) NOT NULL COMMENT '商家名',
  `password` char(32) NOT NULL COMMENT '密码',
  `reg_time` datetime NOT NULL COMMENT '注册时间',
  `update_time` datetime NOT NULL COMMENT '更新时间(修改密码时更新)',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态(是否启用，0为未启用，1为启用)',
  PRIMARY KEY (`home_ID`),
  KEY `fk_home_user_admin_idx` (`admin_ID`),
  CONSTRAINT `home_user_ibfk_1` FOREIGN KEY (`admin_ID`) REFERENCES `admin` (`admin_ID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=790 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of home_user
-- ----------------------------
INSERT INTO `home_user` VALUES ('788', '200', 'homeuser1', 'cc344d2ad568a2bad4ade1986a32dd66', '2015-05-06 21:05:52', '2015-07-16 12:26:43', '1');
INSERT INTO `home_user` VALUES ('789', '300', 'homeuser2', 'f13a37aed93610a07a8436e9f2fe77b5', '2015-05-06 21:06:20', '2015-05-06 21:06:22', '1');

-- ----------------------------
-- Table structure for `log`
-- ----------------------------
DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `log_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志流水号',
  `oper_CATE` int(2) NOT NULL,
  `oper_ID` int(10) NOT NULL,
  `event` text NOT NULL COMMENT '事件（Json)',
  `cTime` datetime NOT NULL COMMENT '日志产生时间',
  PRIMARY KEY (`log_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of log
-- ----------------------------

-- ----------------------------
-- Table structure for `menu`
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `menu_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单编号',
  `r_ID` int(10) unsigned NOT NULL,
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '所属分类(pid=0为类，pid=xx为id=xx的类下面的菜式)',
  `name` varchar(255) NOT NULL COMMENT '菜单名',
  `price` decimal(6,0) unsigned NOT NULL COMMENT '价格（对于类别来说，是该类别下的菜式数量）',
  `desc` tinytext COMMENT '描述',
  `stock` int(10) unsigned NOT NULL DEFAULT '10000' COMMENT '库存',
  `tag` smallint(4) unsigned zerofill NOT NULL COMMENT '标签[新菜、招牌、配菜、辣]，全选中为1111，默认全未选中0000',
  `sort` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序号',
  `month_sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '月售',
  `last_month_sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上月售',
  PRIMARY KEY (`menu_ID`),
  KEY `r_ID` (`r_ID`),
  CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`r_ID`) REFERENCES `restaurant` (`r_ID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11144 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES ('11101', '30000', '0', '正餐', '7', 'ok', '10000', '0000', '1', '0', '0');
INSERT INTO `menu` VALUES ('11102', '30000', '0', '团购优惠套餐', '0', 'what the hell', '10000', '0000', '2', '0', '0');
INSERT INTO `menu` VALUES ('11103', '30000', '0', '饮料', '3', '冰爽一夏', '10000', '0000', '3', '0', '0');
INSERT INTO `menu` VALUES ('11104', '30000', '0', '历史记录', '1', null, '10000', '0000', '0', '2', '5');
INSERT INTO `menu` VALUES ('11105', '30000', '11101', '小黄闷鸡米饭（学生价）', '12', '111', '9999', '0012', '1', '1', '0');
INSERT INTO `menu` VALUES ('11106', '30000', '11101', '辣小黄闷鸡米饭（学生价）', '12', '222', '9999', '0013', '2', '1', '0');
INSERT INTO `menu` VALUES ('11107', '30000', '11101', '小黄闷鸡米饭', '15', null, '9998', '0012', '3', '2', '0');
INSERT INTO `menu` VALUES ('11108', '30000', '11101', '辣小黄闷鸡米饭', '15', null, '9999', '0013', '4', '1', '0');
INSERT INTO `menu` VALUES ('11109', '30000', '11101', '大黄闷鸡米饭', '20', null, '9999', '0012', '5', '1', '0');
INSERT INTO `menu` VALUES ('11110', '30000', '11101', '辣大黄闷鸡米饭', '20', null, '9999', '0013', '6', '1', '0');
INSERT INTO `menu` VALUES ('11111', '30000', '11101', '加白饭一盒', '2', null, '9998', '0005', '7', '2', '0');
INSERT INTO `menu` VALUES ('11112', '30000', '11103', '柠檬茶', '3', null, '9999', '0000', '1', '1', '0');
INSERT INTO `menu` VALUES ('11113', '30000', '11103', '冬瓜茶', '2', null, '10000', '0008', '2', '0', '0');
INSERT INTO `menu` VALUES ('11114', '30000', '11103', '任意一款黄焖鸡米饭+1元送冬瓜茶', '1', null, '9999', '0000', '3', '1', '0');
INSERT INTO `menu` VALUES ('11121', '30001', '0', '正餐', '7', 'ok', '10000', '0000', '1', '0', '0');
INSERT INTO `menu` VALUES ('11122', '30001', '0', '团购优惠套餐', '0', 'what', '10000', '0000', '2', '0', '0');
INSERT INTO `menu` VALUES ('11123', '30001', '0', '饮料', '3', '冰爽一夏', '10000', '0000', '3', '0', '0');
INSERT INTO `menu` VALUES ('11124', '30001', '0', '历史记录', '1', null, '10000', '0000', '0', '0', '0');
INSERT INTO `menu` VALUES ('11125', '30001', '11121', '小黄闷鸡米饭（学生价）', '12', '111', '9998', '0012', '1', '2', '0');
INSERT INTO `menu` VALUES ('11126', '30001', '11121', '辣小黄闷鸡米饭（学生价）', '12', '222', '10000', '0013', '2', '0', '0');
INSERT INTO `menu` VALUES ('11127', '30001', '11121', '小黄闷鸡米饭', '15', '2333333', '9998', '0012', '3', '12', '22');
INSERT INTO `menu` VALUES ('11128', '30001', '11121', '辣小黄闷鸡米饭', '15', null, '9998', '0013', '4', '2', '0');
INSERT INTO `menu` VALUES ('11129', '30001', '11121', '大黄闷鸡米饭', '20', null, '10000', '0012', '5', '0', '0');
INSERT INTO `menu` VALUES ('11130', '30001', '11121', '辣大黄闷鸡米饭', '20', null, '9999', '0013', '6', '1', '0');
INSERT INTO `menu` VALUES ('11131', '30001', '11123', '加白饭一盒', '2', null, '9999', '0005', '7', '1', '0');
INSERT INTO `menu` VALUES ('11132', '30001', '11123', '柠檬茶', '3', null, '10000', '0000', '1', '0', '0');
INSERT INTO `menu` VALUES ('11133', '30001', '11123', '冬瓜茶', '2', null, '10000', '0008', '2', '0', '0');
INSERT INTO `menu` VALUES ('11137', '30000', '0', 'test_cate', '1', '0.0', '10000', '0000', '99', '0', '0');
INSERT INTO `menu` VALUES ('11141', '30000', '11137', 'test', '2', '', '9999', '0007', '1', '0', '0');
INSERT INTO `menu` VALUES ('11142', '30005', '0', 'test', '1', '0.0', '10000', '0000', '1', '0', '0');
INSERT INTO `menu` VALUES ('11143', '30005', '11142', '饭饭饭', '5', '', '9998', '0004', '1', '2', '0');

-- ----------------------------
-- Table structure for `orderitem`
-- ----------------------------
DROP TABLE IF EXISTS `orderitem`;
CREATE TABLE `orderitem` (
  `order_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(20) NOT NULL COMMENT '订单号',
  `r_ID` int(10) unsigned NOT NULL COMMENT '餐厅编号',
  `client_ID` int(10) unsigned NOT NULL,
  `name` varchar(45) NOT NULL COMMENT '客户姓名',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `phone` varchar(30) NOT NULL COMMENT '手机号',
  `total` int(20) unsigned NOT NULL COMMENT '订单总额',
  `order_info` text COMMENT '订单信息(JSON)',
  `cTime` datetime NOT NULL COMMENT '下单时间',
  `rTime` datetime DEFAULT NULL COMMENT '响应时间',
  `today_sort` int(10) DEFAULT NULL COMMENT '今日第x份',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订餐状态（0为餐厅未确认，1为餐厅已确认，2为订单完成，3为订单取消，4为催单，5为响应催单）',
  `reason` text COMMENT '备注',
  PRIMARY KEY (`order_ID`),
  UNIQUE KEY `guid` (`guid`),
  KEY `fk_orderitem_restaurant_idx` (`r_ID`),
  KEY `fk_orderitem_client_idx` (`client_ID`),
  CONSTRAINT `orderitem_ibfk_2` FOREIGN KEY (`r_ID`) REFERENCES `restaurant` (`r_ID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `orderitem_ibfk_1` FOREIGN KEY (`client_ID`) REFERENCES `client` (`client_ID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10070 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of orderitem
-- ----------------------------
INSERT INTO `orderitem` VALUES ('10057', '3254300001437026219', '30000', '10003', '刘恩32111', '17.205 55555555', '18826666666', '12', '{\"r_ID\":\"30000\",\"total\":\"12\",\"item\":[{\"entity_id\":\"11106\",\"name\":\"辣小黄闷鸡米饭（学生价）\",\"price\":\"12\",\"count\":\"1\",\"total\":\"12\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 55555555\",\"c_phone\":\"18826666666\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 13:56:58\",\"isWeChat\":\"false\"}', '2015-07-16 13:56:58', '2015-07-16 19:10:57', null, '3', '不在配送范围');
INSERT INTO `orderitem` VALUES ('10058', '6027300011437045132', '30001', '10003', '刘恩32111', '17.205 55555555', '18826666666', '27', '{\"r_ID\":\"30001\",\"total\":\"27\",\"item\":[{\"entity_id\":\"11126\",\"name\":\"辣小黄闷鸡米饭（学生价）\",\"price\":\"12\",\"count\":\"1\",\"total\":\"12\"},{\"entity_id\":\"11127\",\"name\":\"小黄闷鸡米饭\",\"price\":\"15\",\"count\":\"1\",\"total\":\"15\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 55555555\",\"c_phone\":\"18826666666\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 19:12:12\",\"isWeChat\":\"false\"}', '2015-07-16 19:12:12', '2015-07-16 19:13:58', null, '3', 'null');
INSERT INTO `orderitem` VALUES ('10059', '1877300011437045394', '30001', '10003', '刘恩32111', '17.205 55555555', '18826666666', '15', '{\"r_ID\":\"30001\",\"total\":\"15\",\"item\":[{\"entity_id\":\"11128\",\"name\":\"辣小黄闷鸡米饭\",\"price\":\"15\",\"count\":\"1\",\"total\":\"15\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 55555555\",\"c_phone\":\"18826666666\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 19:16:34\",\"isWeChat\":\"false\"}', '2015-07-16 19:16:34', '2015-07-16 19:16:55', null, '3', '餐厅太忙');
INSERT INTO `orderitem` VALUES ('10060', '4247300001437045451', '30000', '10003', '刘恩32111', '17.205 55555555', '18826666666', '24', '{\"r_ID\":\"30000\",\"total\":\"24\",\"item\":[{\"entity_id\":\"11106\",\"name\":\"辣小黄闷鸡米饭（学生价）\",\"price\":\"12\",\"count\":\"2\",\"total\":\"24\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 55555555\",\"c_phone\":\"18826666666\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 19:17:31\",\"isWeChat\":\"false\"}', '2015-07-16 19:17:31', '2015-07-16 19:17:58', null, '3', '美食已售完');
INSERT INTO `orderitem` VALUES ('10061', '2778300001437045847', '30000', '10003', '刘恩32111', '17.205 55555555', '18826666666', '15', '{\"r_ID\":\"30000\",\"total\":\"15\",\"item\":[{\"entity_id\":\"11107\",\"name\":\"小黄闷鸡米饭\",\"price\":\"15\",\"count\":\"1\",\"total\":\"15\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 55555555\",\"c_phone\":\"18826666666\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 19:24:07\",\"isWeChat\":\"false\"}', '2015-07-16 19:24:07', null, null, '0', null);
INSERT INTO `orderitem` VALUES ('10062', '6568300011437047566', '30001', '10001', 'sum', '华山23栋', '13570432706', '19', '{\"cTime\":\"2015-07-16 19:52:44\",\"c_address\":\"华山23栋\",\"c_name\":\"sum\",\"c_phone\":\"13570432706\",\"deliverTime\":\"尽快送出\",\"item\":[{\"name\":\"饮料\",\"entity_id\":11123,\"count\":2,\"price\":3,\"total\":6},{\"name\":\"历史记录\",\"entity_id\":11124,\"count\":1,\"price\":1,\"total\":1},{\"name\":\"小黄闷鸡米饭（学生价）\",\"entity_id\":11125,\"count\":1,\"price\":12,\"total\":12}],\"note\":\"无\",\"r_ID\":30001,\"total\":19}', '2015-07-16 19:52:44', null, null, '0', null);
INSERT INTO `orderitem` VALUES ('10063', '1992300011437047608', '30001', '10001', 'sum', '华山23栋', '13570432706', '19', '{\"cTime\":\"2015-07-16 19:53:24\",\"c_address\":\"华山23栋\",\"c_name\":\"sum\",\"c_phone\":\"13570432706\",\"deliverTime\":\"尽快送出\",\"item\":[{\"name\":\"饮料\",\"entity_id\":11123,\"count\":2,\"price\":3,\"total\":6},{\"name\":\"历史记录\",\"entity_id\":11124,\"count\":1,\"price\":1,\"total\":1},{\"name\":\"小黄闷鸡米饭（学生价）\",\"entity_id\":11125,\"count\":1,\"price\":12,\"total\":12}],\"note\":\"无\",\"r_ID\":30001,\"total\":19}', '2015-07-16 19:53:24', null, null, '0', null);
INSERT INTO `orderitem` VALUES ('10064', '6592300001437047715', '30000', '10003', '刘恩32111', '17.205 55555555', '18826666666', '35', '{\"r_ID\":\"30000\",\"total\":\"35\",\"item\":[{\"entity_id\":\"11107\",\"name\":\"小黄闷鸡米饭\",\"price\":\"15\",\"count\":\"1\",\"total\":\"15\"},{\"entity_id\":\"11109\",\"name\":\"大黄闷鸡米饭\",\"price\":\"20\",\"count\":\"1\",\"total\":\"20\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 55555555\",\"c_phone\":\"18826666666\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 19:55:15\",\"isWeChat\":\"false\"}', '2015-07-16 19:55:15', null, null, '0', null);
INSERT INTO `orderitem` VALUES ('10065', '5957300001437047723', '30000', '10003', '刘恩32111', '17.205 55555555', '18826666666', '22', '{\"r_ID\":\"30000\",\"total\":\"22\",\"item\":[{\"entity_id\":\"11110\",\"name\":\"辣大黄闷鸡米饭\",\"price\":\"20\",\"count\":\"1\",\"total\":\"20\"},{\"entity_id\":\"11111\",\"name\":\"加白饭一盒\",\"price\":\"2\",\"count\":\"1\",\"total\":\"2\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 55555555\",\"c_phone\":\"18826666666\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 19:55:23\",\"isWeChat\":\"false\"}', '2015-07-16 19:55:23', null, null, '0', null);
INSERT INTO `orderitem` VALUES ('10066', '6060300001437048040', '30000', '10007', '刘恩32111', '17.205 55555555', '18827777777', '20', '{\"r_ID\":\"30000\",\"total\":\"20\",\"item\":[{\"entity_id\":\"11110\",\"name\":\"辣大黄闷鸡米饭\",\"price\":\"20\",\"count\":\"1\",\"total\":\"20\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 55555555\",\"c_phone\":\"18827777777\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 20:00:40\",\"isWeChat\":\"false\"}', '2015-07-16 20:00:40', null, null, '0', null);
INSERT INTO `orderitem` VALUES ('10067', '2083300001437048075', '30000', '10007', '刘恩32111', '17.205 55555555', '18827777777', '20', '{\"r_ID\":\"30000\",\"total\":\"20\",\"item\":[{\"entity_id\":\"11110\",\"name\":\"辣大黄闷鸡米饭\",\"price\":\"20\",\"count\":\"1\",\"total\":\"20\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 55555555\",\"c_phone\":\"18827777777\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 20:01:15\",\"isWeChat\":\"false\"}', '2015-07-16 20:01:15', null, null, '0', null);
INSERT INTO `orderitem` VALUES ('10068', '1983300001437050261', '30000', '10001', 'sum', '华山23栋', '13570432706', '14', '{\"cTime\":\"2015-07-16 20:37:50\",\"c_address\":\"华山23栋\",\"c_name\":\"sum\",\"c_phone\":\"13570432706\",\"deliverTime\":\"尽快送出\",\"item\":[{\"name\":\"正餐\",\"entity_id\":11101,\"count\":2,\"price\":7,\"total\":14}],\"note\":\"无\",\"r_ID\":30000,\"total\":14}', '2015-07-16 20:37:50', null, null, '0', null);
INSERT INTO `orderitem` VALUES ('10069', '2931300001437050836', '30000', '10003', '刘恩32111', '17.205 66666', '18826666666', '15', '{\"r_ID\":\"30000\",\"total\":\"15\",\"item\":[{\"entity_id\":\"11107\",\"name\":\"小黄闷鸡米饭\",\"price\":\"15\",\"count\":\"1\",\"total\":\"15\"}],\"c_name\":\"刘恩32111\",\"c_address\":\"17.205 66666\",\"c_phone\":\"18826666666\",\"note\":\"\",\"deliverTime\":\"尽快送出\",\"cTime\":\"2015-07-16 20:47:16\",\"isWeChat\":\"false\"}', '2015-07-16 20:47:16', null, null, '0', null);

-- ----------------------------
-- Table structure for `restaurant`
-- ----------------------------
DROP TABLE IF EXISTS `restaurant`;
CREATE TABLE `restaurant` (
  `r_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'rid = 10086 + uid',
  `home_ID` int(10) unsigned NOT NULL,
  `logo_url` varchar(255) NOT NULL DEFAULT 'http://momopluto.xicp.net/platform/Application/Uploads/rst_logo/default_rst_logo.jpg' COMMENT 'logo的url(JPG、PNG格式，大图360*200，小图200*200，默认为default_rst_logo.jpg)',
  `name` varchar(255) DEFAULT NULL COMMENT '餐厅名',
  `address` varchar(255) DEFAULT NULL COMMENT '餐厅地址',
  `desc` tinytext COMMENT '餐厅简介',
  `phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `promotion_info` text COMMENT '餐厅公告信息（促销信息）',
  `deliver_desc` text COMMENT '起送说明',
  `agent_fee` tinyint(4) unsigned DEFAULT '0' COMMENT '起送价(目前只简单的统一设置9.24）',
  `isOpen` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否营业(0为不营业，1为营业，默认为1)',
  `is_bookable` tinyint(1) unsigned DEFAULT '1' COMMENT '是否接受预定(0否，1是，默认1)',
  `time_1_open` time DEFAULT NULL,
  `time_1_close` time DEFAULT NULL,
  `time_2_open` time DEFAULT NULL,
  `time_2_close` time DEFAULT NULL,
  `time_3_open` time DEFAULT NULL,
  `time_3_close` time DEFAULT NULL,
  `warning_tone` tinyint(1) unsigned DEFAULT '0' COMMENT '提示音(值为提示音编号,默认为0)',
  PRIMARY KEY (`r_ID`),
  KEY `r_ID` (`r_ID`),
  KEY `home_ID` (`home_ID`),
  CONSTRAINT `restaurant_ibfk_1` FOREIGN KEY (`home_ID`) REFERENCES `home_user` (`home_ID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30006 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of restaurant
-- ----------------------------
INSERT INTO `restaurant` VALUES ('30000', '788', 'http://momopluto.xicp.net/platform2/Application/Uploads/rst_logo/5ecc613150de01b7e6824594426f24f4.png', '黄小吉', '华农西门长福路ONG创意园', '健康最重要，小吉马上到！每天专注于一道菜！', '15876502162', '黄小吉吉吉吉吉吉吉', '华农的同学们可以填写短号哦，华工和跃进的同学们尽量几个宿舍一起订，满6份，送可乐大炮一瓶，拍下才有哦。谢谢！662162（华农短号）或 15876502162', '10', '1', '1', '11:00:00', '14:00:00', '16:00:00', '18:00:00', '19:00:00', '23:59:00', '0');
INSERT INTO `restaurant` VALUES ('30001', '788', 'http://momopluto.xicp.net/platform2/Application/Uploads/rst_logo/451fbb024d0794ffcda2258170740a1e.jpg', '黄小吉233', '华农西门长福路ONG创意园', '健康最重要，小吉马上到！每天专注于一道菜！', '15876502162', '黄小吉吉吉吉吉吉吉', '华农的同学们可以填写短号哦，华工和跃进的同学们尽量几个宿舍一起订，满6份，送可乐大炮一瓶，拍下才有哦。谢谢！662162（华农短号）或 15876502162', '10', '1', '1', '11:00:00', '14:00:00', '16:00:00', '18:00:00', '19:00:00', '23:59:00', '1');
INSERT INTO `restaurant` VALUES ('30005', '789', 'http://momopluto.xicp.net/platform2/Application/Uploads/rst_logo/default_rst_logo.jpg', '尖尖尖', 'test', 'test', '18826481053', 'test', 'test', '0', '1', '1', '11:00:00', '13:00:00', '15:00:00', '17:00:00', '18:00:00', '22:00:00', '1');

-- ----------------------------
-- Table structure for `service`
-- ----------------------------
DROP TABLE IF EXISTS `service`;
CREATE TABLE `service` (
  `service_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '服务流水号',
  `r_ID` int(10) unsigned NOT NULL,
  `sTime` datetime NOT NULL COMMENT '操作时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '服务状态',
  PRIMARY KEY (`service_ID`),
  KEY `fk_service_restaurant_idx` (`r_ID`),
  CONSTRAINT `service_ibfk_1` FOREIGN KEY (`r_ID`) REFERENCES `restaurant` (`r_ID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of service
-- ----------------------------
INSERT INTO `service` VALUES ('101', '30000', '2015-05-06 21:33:01', '1');
INSERT INTO `service` VALUES ('102', '30001', '2015-05-06 21:33:10', '1');
INSERT INTO `service` VALUES ('103', '30005', '2015-05-06 21:33:19', '1');
