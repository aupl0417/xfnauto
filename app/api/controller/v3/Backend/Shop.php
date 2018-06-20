<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-05-31
 * Time: 11:46
 */

namespace app\api\controller\v2\Backend;

use think\Controller;
use think\Db;
class Shop extends Admin
{

    /**
     * 店铺列表
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;


    }

    /**
     * 审核详情
     * */
    public function detail(){

    }

    /**
     * 审核
     * */
    public function verify(){

    }


}