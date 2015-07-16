<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 前台主页
 * 
 */
class IndexController extends HomeController {

    public function _initialize() {
        parent::_initialize ();

    }
    

    // 主页
    public function index(){

        // session('RST_STATUS', null);
        // p(session());die;

        $this->display();
    }


    // 切换餐厅营业状态
    public function changeStatus(){

        // p(I('get.'));
        // die;

        $r_ID = I('get.rid');
        if (!is_rID_valid($r_ID)) {

            $this->error('餐厅ID不合法！');
            return;
        }

        $rstInfo = session('RST_INFO');


        if ($rstInfo[$r_ID]['isOpen'] == 1) {

            $update_rstInfo['isOpen'] = 0;
        }else{
            $update_rstInfo['isOpen'] = 1;
        }

        $model = M('restaurant');
        $map['r_ID'] = $r_ID;

        if ($model->where($map)->setField($update_rstInfo)) {

            $rstInfo[$r_ID]['isOpen'] = $update_rstInfo['isOpen'];
            session('RST_INFO', $rstInfo);
        }

        // p($_SERVER);die;
        redirect($_SERVER['HTTP_REFERER']);//重定向至来源网页
    }
}