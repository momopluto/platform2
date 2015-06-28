<?php
namespace Client\Controller;
use Think\Controller;
class UserController extends Controller {


// *******************注册接口，写数据库
// *******************登录接口，从数据库取

	/**
	 * <interface>,客户注册
	 * 需要数据：phone, name
	 * 成功，返回client_ID
	 * 失败，返回errcode=40001，注册失败:`相关的错误信息`
	 */
	function reg(){

		// echo "reg can work!";

		// phone, name
		$reg_data['phone'] = I('post.phone');
		$reg_data['name'] = I('post.name');

		// $reg_data['phone'] = '18826481052';
		// $reg_data['name'] = 'momo';

		$model = D('Client');
		if ($model->create($reg_data) && $client_ID = $model->add()) {
			
			$data['result'] = $client_ID;
		}else {

			$data['errcode'] = '40001';
			$data['errmsg'] = '注册失败:' . $model->getError();
		}

		// 如果是app来的访问，返回json
		if (I('get.srcid') == '10086') {

		    $JSON = $data;

		    echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
		    return;
		}

		$this->ajaxReturn($data, 'json');
	}

	/**
	 * <interface>,客户登录(默认调用此接口时，已经短信验证手机)
	 * 需要数据：phone
	 * 成功，返回"用户信息"
	 * 失败，返回errcode=46001，不存在的用户
	 */
	function login(){

		// echo "login can work!";

		// phone
		// 写session
		$map['phone'] = I('post.phone');
		// $map['phone'] = '18826481053';

		if (!$user = M('client')->where($map)->find()) {
			
			$data['errcode'] = '46001';
			$data['errmsg'] = '不存在的用户';
		}else {

			$data = $user;// 用户信息

			// 写session
			session('CLIENT_ID', $user['client_ID']);
			session('C_PHONE', $map['phone']);
		}

		// 如果是app来的访问，返回json
		if (I('get.srcid') == '10086') {

		    if (!$data['errcode']) {
		        
		        $JSON['data'] = $data;

		        echo json_encode($JSON, JSON_UNESCAPED_UNICODE); 
		        return;
		    }

		    echo json_encode($data, JSON_UNESCAPED_UNICODE); 
		    return;
		}

		$this->ajaxReturn($data, 'json');
	}

	/**
	 * <interface>,客户登出
	 */
	function logout(){

		session('CLIENT_ID', null);
		session('C_PHONE', null);
	}

}