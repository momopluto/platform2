<?php

	define('APP_DEBUG', true);

	// // 绑定Client模块到当前入口文件
	// define('BIND_MODULE','Client');
	// define('BUILD_CONTROLLER_LIST','Index,User,Order');
	// define('BUILD_MODEL_LIST','User');

    define('APP_PATH', './Application/');
    define('THINK_PATH', './ThinkPHP/');

    define('DOMAIN_URL', "http://momopluto.xicp.net");//服务器域名
    // define('DOMAIN_URL', "http://scauhometown.sinaapp.com");//服务器域名
    
    // define('DOMAIN_URL', "http://127.0.0.1:8080");//服务器域名

    define('PUBLIC_URL', '/platform2/Application/Public');//Public公共文件夹路径
    
    define('ADMIN_SRC', '/platform2/Application/Admin/Source');//Admin资源文件夹路径
    define('HOME_SRC', '/platform2/Application/Home/Source');//Home资源文件夹路径
    define('CLIENT_SRC', '/platform2/Application/Client/Source');//CLient资源文件夹路径

    define('ADMIN_TITLE', '订餐平台后台');
    define('HOME_TITLE', '餐厅管理系统');
    define('PLTF_NAME', '堂-订餐');

    require THINK_PATH.'ThinkPHP.php'

?>