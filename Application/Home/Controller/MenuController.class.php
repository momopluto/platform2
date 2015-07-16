<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 餐厅菜单
 * 
 */
class MenuController extends HomeController {

    function _initialize() {
        parent::_initialize ();

        // $this->model = M ('menu', $rid."_")->order('pid, sort');
    }

    // 菜单列表
    function lists(){

        $whe['home_ID'] = session('HOME_ID');
        $r_IDs = M('restaurant')->where($whe)->getField('r_ID,name');

        // p($r_IDs);die;

        foreach ($r_IDs as $r_ID => $name) {

            $model = M('menu');
            $map['r_ID'] = $r_ID;

            $map['pid'] = 0;
            //pid=0的类别信息数组$menu，按sort排序，其中的price为该类别下的菜式数量
            $menu = $model->where($map)->order('pid, sort')->getField('menu_ID,pid,r_ID,name,price,desc,stock,tag,sort,month_sales,last_month_sales');

            // 将子菜单加入到菜单类别
            foreach ($menu as $key => $value2) {
                $map['pid'] = $key;

                $sub_menu = $model->where($map)->order('sort')->getField('menu_ID,pid,r_ID,name,price,desc,stock,tag,sort,month_sales,last_month_sales');//->select();
                $menu[$key]['sub_menu'] = $sub_menu;
            }

            $data[$r_ID]['name'] = $name;
            $data[$r_ID]['menu'] = $menu;
        }
        

        // p($data);die;

        $this->assign('data', $data);

    	$this->display();
    }

    // 新增菜单
    function add_menu(){
        // p(I('post.'));
        // die;
        //菜名必须
        //价格非负，可为0
        //序号不填，默认为0        

        if(IS_POST){
            $r_ID = I('post.rid');
            if (!is_rID_valid($r_ID)) {

                $this->error('餐厅ID不合法！');
                return;
            }

            if(I('post.new_menu') != '' && I('post.pid') != ''){//菜单名不空 && pid不空

                $data['r_ID'] = $r_ID;
                $data['pid'] = I('post.pid');
                $data['name'] = I('post.new_menu');
                if(I('post.price') == ''){
                    $data['price'] = 0;
                }elseif(I('post.price') >= 0){
                    $data['price'] = I('post.price');
                }else{
                    $this->error('价格为负？');
                    return;
                }

                $data['desc'] = I('post.description');

                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                // 标签判断
                $tagArr[] = I('post.is_new');
                $tagArr[] = I('post.is_featured');
                $tagArr[] = I('post.is_gum');
                $tagArr[] = I('post.is_spicy');

                $tag = 0;
                foreach ($tagArr as $value) {
                    $tag = $tag << 1;
                    if ($value == 'on') {
                        $tag += 1;
                    }
                }
                // echo $tag;
                $data['tag'] = $tag;

                // p($data);die;

                $model = M('menu');
                $model->startTrans();

                if($model->add($data)){
                    $map['r_ID'] = $r_ID;
                    $map['menu_ID'] = $data['pid'];
                    // 所属类别的菜单数+1
                    if ($model->where($map)->setInc('price') === false) {
                        $model->rollback();
                        $this->error('新增菜单失败！');
                    }else{
                        $model->commit();
                        $this->success('新增菜单成功！');
                    }
                }else{
                    $this->error($this->model->getError());
                }            
            }else{
                $this->error('菜单名不能为空！');
            }
        }else{
            redirect(U('Home/Menu/lists'));
        }
    }    

    // 删除菜单
    function del_menu(){

        // p(I('get.'));
        // die;
        $r_ID = I('get.rid');
        if (!is_rID_valid($r_ID)) {

            $this->error('餐厅ID不合法！');
            return;
        }

        if(I('get.id') != '' && I('get.pid') != ''){

            $model = M('menu');
            $map['r_ID'] = $r_ID;
            $map['menu_ID'] = I('get.id');

            $model->startTrans();// 开启事务

            if(!$model->where($map)->delete()){// 返回0或者false都直接算删除失败

                $model->rollback();
                $this->error('删除菜单失败！');
            }else{

                $map['menu_ID'] = I('get.pid');
                // 所属类别的菜单数-1
                if (!$model->where($map)->setDec('price')) {// 所属类别一定存在，所以返回0或者false都算失败
                    
                    $model->rollback();
                    $this->error('更新菜单所属类别失败！');
                }else{

                    $model->commit();
                    $this->success('删除菜单成功！');
                }
            }
        }else{
            redirect(U('Home/Menu/lists'));
        }
    }

    // 菜单库存清零
    function stockclear(){
        // P(I('get.'));die;

        $r_ID = I('get.rid');
        if (!is_rID_valid($r_ID)) {

            $this->error('餐厅ID不合法！');
            return;
        }

        if(I('get.id') != ''){
            $map['menu_ID'] = I('get.id');
            $map['r_ID'] = $r_ID;
            $model = M('menu');
            $model->where($map)->setField('stock', 0);
        }

        redirect(U('Home/Menu/lists'));
    }

    // 编辑菜单
    function edit_menu(){

        if(IS_POST){
            // p(I('post.'));
            // die;

            $r_ID = I('post.rid');
            if (!is_rID_valid($r_ID)) {

                $this->error('餐厅ID不合法！');
                return;
            }

            if (I('post.id') == '') {
                $this->error('菜单ID不能为空！');
                return;
            }

            if(I('post.name') != ''){//菜单名不空

                $data['name'] = I('post.name');
                if(I('post.price') == ''){
                    $data['price'] = 0;
                }elseif(I('post.price') >= 0){
                    $data['price'] = I('post.price');
                }else{
                    $this->error('价格为负？');
                }
                $data['desc'] = I('post.description');
                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }
                if(I('post.stock') == ''){
                    $data['stock'] = 10000;
                }else{
                    $data['stock'] = I('post.stock');
                }

                // 标签判断
                $tagArr[] = I('post.is_new');
                $tagArr[] = I('post.is_featured');
                $tagArr[] = I('post.is_gum');
                $tagArr[] = I('post.is_spicy');

                $tag = 0;
                foreach ($tagArr as $value) {
                    $tag = $tag << 1;
                    if ($value == 'on') {
                        $tag += 1;
                    }
                }
                // echo $tag;
                $data['tag'] = $tag;

                // p($data);die;

                $map['r_ID'] = $r_ID;
                $map['menu_ID'] = I('post.id');
                $model = M('menu');
                if($model->where($map)->save($data)){

                    redirect(U('Home/Menu/lists'));
                }else{
                    $this->error('更新菜单信息失败！');
                }            
            }else{
                $this->error('菜单名不能为空！');
            }
        }else{
            redirect(U('Home/Menu/lists'));
        }
    }

/*--------------------------------------------------------------*/

    // 新增分类
    function add_cate(){
/*
 Array
(
    [rid] => 30000
    [sort] => 排序号
    [new_cate] => 分类名
    [description] => 分类描述
)
*/
        // p(I('post.'));die;
        // 餐厅ID必须
        // 菜名必须
        // 序号，默认为0 
        // 分类描述，可不填

        if(IS_POST){
            $r_ID = I('post.rid');
            if (!is_rID_valid($r_ID)) {

                $this->error('餐厅ID不合法！');
                return;
            }

            $new_cate = I('post.new_cate');
            if($new_cate != ''){//类别名不空
                $data['r_ID'] = $r_ID;
                $data['pid'] = 0;
                $data['name'] = $new_cate;
                $data['price'] = 0;
                $data['desc'] = I('post.description');
                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                // p($data);
                // die;

                $model = M('menu');
                if($model->add($data)){

                    $this->success('新增分类成功！');
                }else{
                    $this->error($model->getError());
                }            
            }else{
                $this->error('分类名不能为空！');
            }
        }else{
            redirect(U('Home/Menu/lists'));
        }
    }

    // 删除分类
    function del_cate(){
        $r_ID = I('get.rid');
        if (!is_rID_valid($r_ID)) {

            $this->error('餐厅ID不合法！');
            return;
        }

        if(I('get.id') != ''){
            $map1['menu_ID'] = I('get.id');
            $model = M('menu');
            $model->startTrans();// 开启事务
            $map1['r_ID'] = $r_ID;
            if(!$model->where($map1)->delete()){// 返回值为0或者false都直接算删除失败
                
                $model->rollback();// 回滚事务
                $this->error('删除分类失败！');
            }else{

                $map2['r_ID'] = $r_ID;
                $map2['pid'] = $map1['menu_ID'];
                //删除该分类下的所有菜单
                if ($model->where($map2)->delete() === false) {// 返回值为false才算失败，0说明该分类下没有菜单

                    $model->rollback();// 回滚事务
                    $this->error('删除分类下所有菜单失败！');
                }else {
                    $model->commit();// 提交事务
                    $this->success('删除分类成功！');
                }
            }
        }else{
            redirect(U('Home/Menu/lists'));
        }
    }

    // 分类库存清零
    function setStockEmpty(){
        $r_ID = I('get.rid');
        if (!is_rID_valid($r_ID)) {

            $this->error('餐厅ID不合法！');
            return;
        }

        if(I('get.pid') != ''){
            $map['pid'] = I('get.pid');
            $model = M('menu');
            $model->where($map)->setField('stock', 0);
        }

        redirect(U('Home/Menu/lists'));
    }

    // 分类库存置满
    function setStockFull(){
        $r_ID = I('get.rid');
        if (!is_rID_valid($r_ID)) {

            $this->error('餐厅ID不合法！');
            return;
        }

        if(I('get.pid') != ''){
            $map['pid'] = I('get.pid');
            $model = M('menu');
            $model->where($map)->setField('stock', 10000);
        }

        redirect(U('Home/Menu/lists'));
    }

    // 编辑分类
    function edit_cate(){

        if(IS_POST){

            $r_ID = I('post.rid');
            if (!is_rID_valid($r_ID)) {

                $this->error('餐厅ID不合法！');
                return;
            }

            if(I('post.name') != '' && I('post.id') != ''){//类别名不空 && 菜单id不为空

                $data['name'] = I('post.name');
                $data['desc'] = I('post.description');
                if(I('post.sort') == ''){
                    $data['sort'] = 0;
                }else{
                    $data['sort'] = I('post.sort');
                }

                $map['r_ID'] = $r_ID;
                $map['menu_ID'] = I('post.id');
                $model = M('menu');
                if(!$model->where($map)->setField($data)){
                    
                    $this->error('更新分类信息失败');
                }else{

                    redirect(U('Home/Menu/lists'));
                }            
            }else{
                $this->error('类别名不能为空！');
            }
        }else{
            redirect(U('Home/Menu/lists'));
        }
    }
}

?>