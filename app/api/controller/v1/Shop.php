<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class Shop extends Home
{

    /**
     * 在售车辆列表
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        $overOffShelf  = isset($this->data['overOffShelf']) && !empty($this->data['overOffShelf']) ? $this->data['overOffShelf'] + 0 : 0;

        $where = ['over_delete' => 0, 'over_off_shelf' => $overOffShelf, 'org_id' => ['in', $this->orgIds]];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = trim($this->data['keywords']);
            $where['cars_name'] = ['like', '%' . $keywords . '%'];
        }

        $field = $this->createField('shop_goods_cars');
        $data  = Db::name('shop_goods_cars')->where($where)->field($field)->order('create_date desc')->select();
        $count = Db::name('shop_goods_cars')->where($where)->count();
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

}