<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 餐厅管理
 *
 */
class ShopmanageController extends HomeController{
	
	protected $model;
	// 初始化操作
	function _initialize() {
        parent::_initialize ();

        // $this->model = D('restaurant');
	}

    // home新增餐厅
    //      restaurant中增加一条记录，logo_url为default，isOpen为开
    //      service中增加一条记录



    // home关闭餐厅
    //      restaurant设置isOpen状态为关



    // home编辑餐厅
    //      内嵌的标签页，每个标签页一个餐厅
    function set(){

        /*
        
        测试样例：
        1.仅有r_ID, home_ID, logo_url, isOpen的餐厅初始化信息（不使用自动验证
        2.完整的餐厅信息

        以上情况，还各自分"上传新logo"和"不上传新logo"2种情况

         */

        // 查看餐厅信息
        // 首先，通过home_ID得到其麾下管理的r_ID
        // 外循环(有几个r_ID
        //   得到每个r_ID的餐厅信息
        
        // 更新餐厅信息
        // 得到r_ID
        //   1.上传餐厅logo(如果没有新上传logo，使用数据库内原有的;
        //   2.组装data数组，更新数据表

        // session(null);
        if (IS_POST) {
             // p(I('post.'));
             // p(I('get.'));

             $r_ID = I('get.id');

             if (!is_rID_valid($r_ID)) {

                 $this->error('餐厅ID不合法！');
                 return;
             }

             $data = I('post.');
             $data['r_ID'] = $r_ID;
        /*======================================上传餐厅logo begin==================================*/
            // p($_FILES);die;
            $logo_url = null;
            if($_FILES['photo']['error'] == 4){
                // $logo_url = null;
            }else{
                $config = array(//图片上传配置
                    'maxSize'    =>    3145728,    
                    'rootPath'   =>    './Application/Uploads',
                    'savePath'   =>    '/rst_logo/',    
                    'saveName'   =>    md5($r_ID),   //md5加密用户的uid作为logo名
                    'exts'       =>    array('jpg', 'png', 'jpeg'),    
                    'autoSub'    =>    false,   //子目录，关闭    
                    // 'subName'    =>    array('date','Ymd'),
                    'replace'    =>    true,    //允许同名文件覆盖
                );
                $upload = new \Think\Upload($config);// 实例化上传类  
                $info   =   $upload->uploadOne($_FILES['photo']);

                if(!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                }else{// 上传成功
                    $logo_url = DOMAIN_URL."/platform2/Application/Uploads/rst_logo/".$info['savename'];
                }
            }
        /*======================================上传餐厅logo end==================================*/

        // logo_url不为空，则需要更新数据表
        if ($logo_url) {
            $data['logo_url'] = $logo_url;
        }

        if($data['is_bookable'] === 'on'){
            $data['is_bookable'] = 1;
        }
        else{
            $data['is_bookable'] = 0;
        }

        // p($data);

        $model = D('Restaurant');
        if($model->create($data, 2) && $model->save($data)){

            // echo "成功";
            $this->success('餐厅信息更新成功！');
        }else{

            // echo $model->getError();
            // p($model);
            $this->error($model->getError());
        }

        }else {

            // $home_ID = session('HOME_ID');
            $whe['home_ID'] = session('HOME_ID');

            $model = M('restaurant');
            $rsts = $model->where($whe)->select();

            // p($rsts);die;

            $this->assign('rsts', $rsts);
            $this->display();
        }
    }
}

?>