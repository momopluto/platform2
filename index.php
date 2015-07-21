<?php

	define('APP_DEBUG', true);// 开启调试，上线前须关闭

	// // 绑定Client模块到当前入口文件
	// define('BIND_MODULE','Client');
	// define('BUILD_CONTROLLER_LIST','Index,User,Order');
	// define('BUILD_MODEL_LIST','User');

    define('APP_PATH', './Application/');
    define('THINK_PATH', './ThinkPHP/');
    
    define('DOMAIN_URL', "http://127.0.0.1");//服务器域名

    define('PUBLIC_URL', '/platform2/Application/Public');//Public公共文件夹路径
    
    define('ADMIN_SRC', '/platform2/Application/Admin/Source');//Admin资源文件夹路径
    define('HOME_SRC', '/platform2/Application/Home/Source');//Home资源文件夹路径
    define('CLIENT_SRC', '/platform2/Application/Client/Source');//CLient资源文件夹路径

    define('ADMIN_TITLE', '平台后台管理');
    define('HOME_TITLE', '餐厅后台管理');
    define('PLTF_NAME', '网上订餐平台-订餐');

    require THINK_PATH.'ThinkPHP.php'

?>