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
class StockCar extends Home
{

    public function index(){

        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = [
            'is_delete' => 0,
        ];

        if(isset($this->data['frame_number']) && !empty($this->data['frame_number'])){
            $where['frame_number'] = ['like', '%' . $this->data['frame_number'] . '%'];
        }

        if(isset($this->data['cars_info']) && !empty($this->data['cars_info'])){
            $where['cars_info'] = ['like', '%' . $this->data['frame_number'] . '%'];
        }

        if(isset($this->data['cars_info']) && !empty($this->data['cars_info'])){
            $where['cars_info'] = ['like', '%' . $this->data['frame_number'] . '%'];
        }

        $field = '';
        $join  = [
            ['consumer_order co', 'co.id=sc.order_id'],
            ['system_organization so', 'so.orgId=sc.org_id']
        ];
        $data  = Db::name('stock_car sc')->where($where)->field($field)->join($join)->page($page, $rows)->order('create_date desc')->select();
        
    }

}