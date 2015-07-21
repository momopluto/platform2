# 系统安装和使用说明

标签： platform2

---

## 1、运行环境
集成环境：WAMP/LAMP/MAMP

- Apache2.4.9(建议版本2.4以上)
- MySQL5.6.17(建议版本5.6以上)
- PHP5.5.12(建议版本5.5以上)

---

## 2、安装说明
### 一、源码配置
#### 1. 获取源码
方式一、使用git
```
git clone https://github.com/momopluto/platform2.git
```
方式二、下载源代码
```
https://github.com/momopluto/platform2/archive/master.zip

# 解压，重命名项目文件夹名为platform2
```
以上方式得到项目文件夹platform2
将此文件夹放至你的网站目录

#### 2. 添加配置文件
在项目文件夹platform2的各模块下，创建config.php文件
```
# Client模块内起作用
/Application/Client/Conf/config.php
# Home模块内起作用
/Application/Home/Conf/config.php
# Admin模块内起作用 [此模块暂未开发，2015.7.21]
/Application/Admin/Conf/config.php
```

config.php，内容如下：
```php
<?php
return array(

    // 默认引入的全局公共函数文件为/Application/Common/Common/functions.php
    // 此处引入模块内公共函数文件
    // 文件所在路径为/*模块名*/Common/common.php
    'LOAD_EXT_FILE' => 'common',

    //数据库配置
    'DB_TYPE'                =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'pltf2',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名(根据自己的实际情况填写)
    'DB_PWD'                =>  '',          // 密码(根据自己的实际情况填写)
    'DB_PORT'               =>  '3306',        // 端口
    'DB_CHARSET'         =>  'utf8',      // 数据库编码默认采用utf8
);

```
接着，
创建公共模块的config.php文件
```
# 全局模块内起作用
/Application/Common/Conf/config.php
```
config.php，内容如下：
```php
<?php
return array(
	
	'DEFAULT_MODULE' => 'Client',//默认入口为Client模块
);

/*
注：
    如果你3个模块下面的数据库配置都相同，也可以只在这个文件里增加上面的配置，
    然后在Client、Home、Admin的config.php里保留用来引用各自模块公共函数的配置：
    
    'LOAD_EXT_FILE' => 'common'
*/
```
### 二、数据库配置
#### 1. 得到pltf2.sql文件
进入目录"项目相关内容\sql文件\"，剪切pltf2.sql至非网站目录下
PS：路径中不要出现中文，这里假定目标路径为d:\pltf2.sql
#### 2.	导入数据库
```mysql
# 命令行下，登录数据库
mysql -uroot –p

>> create database pltf2;   # 创建数据库pltf2
>> use pltf2;   # 切换当前数据库为pltf2
>> source d:\pltf2.sql  # 以文件方式导入数据

```

---

## 3、使用说明
### 一、买家订餐入口：
http://127.0.0.1/platform2/index.php/Client/Restaurant/lists.html

### 二、餐厅管理入口：
http://127.0.0.1/platform2/index.php/Home/User/login.html
```
# 2个测试账号
账号1&密码：homeuser1
账号2&密码：homeuser2
```
### 三、平台管理入口：(暂未开发，2015.7.21)
http://127.0.0.1/platform2/index.php/Admin/User/login.html


---
## 附
### 1. [(买家版)安卓APP](https://github.com/momopluto/platform2/tree/master/%E9%A1%B9%E7%9B%AE%E7%9B%B8%E5%85%B3%E5%86%85%E5%AE%B9/(%E4%B9%B0%E5%AE%B6%E7%89%88)%E5%AE%89%E5%8D%93APP) 
### 2. [SQL文件说明](https://github.com/momopluto/platform2/blob/master/%E9%A1%B9%E7%9B%AE%E7%9B%B8%E5%85%B3%E5%86%85%E5%AE%B9/sql%E6%96%87%E4%BB%B6/(%E5%B8%A6%E5%88%9D%E5%A7%8B%E6%B5%8B%E8%AF%95%E6%95%B0%E6%8D%AE%E7%9A%84)SQL%E6%96%87%E4%BB%B6%E8%AF%B4%E6%98%8E.markdown)




