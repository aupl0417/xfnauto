<?php

/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:47
 */
namespace app\api\model;

use think\Db;
use think\Model;

class ConsumerOrder extends Model
{

    protected $table = 'consumer_order';

    public function getOrderById($id, $field = '*'){
        if(empty($id) || !is_numeric($id)){
            return false;
        }

        return Db::name($this->table)->field($field)->where(['id' => $id])->find();
    }

    public function getOrderList($where = ''){

        $field = 'co.id as id,co.order_code as orderId,co.state as orderState,oi.cars_name as carName,oi.color_name as colorName,oi.interior_name as interiorName,oi.state as orderInfoState,oi.car_num as carNum';
        $data  = Db::name('consumer_order co')->where($where)
               ->field($field)->join('consumer_order_info oi', 'co.id=oi.order_id', 'left')
               ->select();
        if($data){
            $stateArr = [
                '1' => '初始',
                '5' => '待收定金',
                '10' => '待配车',
                '15' => '待验车',
                '20' => '待协商',
                '25' => '待收尾款',
                '30' => '待出库',
                '35' => '待上传票证',
                '40' => '完成',
            ];
            foreach($data as $key => &$value){
                $value['orderStateName'] = $stateArr[$value['orderState']];
            }
        }

        return $data;
    }

    /*
     * 单月各状态的订单统计
     * */
    public function orderCount($condition = '', $userId, $orgId){
        $startTime = date('Y-m-01');
        $endTime   = date('Y-m-t 23:59:59');
        $where['create_time']    = ['between', [$startTime, $endTime]];
        $where['creator_id']     = $userId;
        $where['org_id']         = $orgId;
        $obj = Db::name($this->table)->where($where);
        $cond = array();
        if($condition){
            if(!is_array($condition)){
                $condition = explode(',', $condition);
                $cond['state'] = ['in', $condition];
            }else{
                $cond['state'] = $condition;//支持['in/between', []]形式
            }
            $obj = $obj->where($cond);
        }

        return $obj->count();
    }

    /*
     * 订单各费用统计
     * */
    public function orderFeeCount($type, $userId, $orgId){
        $startTime = date('Y-m-01');
        $endTime   = date('Y-m-t 23:59:59');
        $where['create_time']    = ['between', [$startTime, $endTime]];
        $where['creator_id']     = $userId;
        $where['org_id']         = $orgId;
        $obj = $total = Db::name($this->table)->where($where)->join('consumer_order_info', 'id=order_id', 'left');
        switch ($type){
            case 'traffic':
                $total = $obj->sum('traffic_compulsory_insurance_price');
                break;
            case 'commercial':
                $total = $obj->sum('commercial_insurance_price');
                break;
        }

        return $total;
    }

    /*
     * 订单各费用统计
     * */
    public function orderFeeList($type, $userId, $orgId){
        $startTime = date('Y-m-01', strtotime('-1 month'));
        $endTime   = date('Y-m-t 23:59:59');
        $where['co.create_time']    = ['between', [$startTime, $endTime]];
        $where['co.creator_id']     = $userId;
        $where['co.org_id']         = $orgId;
        $field = 'co.id as id,co.order_code as orderId,co.state as orderState,oi.cars_name as carName,oi.color_name as colorName,oi.interior_name as interiorName,oi.state as orderInfoState,oi.car_num as carNum';
        switch ($type){
            case 'traffic':
                $where['oi.traffic_compulsory_insurance_price'] = ['>', 0];
                break;
            case 'commercial':
                $where['oi.commercial_insurance_price'] = ['>', 0];
                break;
        }

        return Db::name('consumer_order co')->where($where)->field($field)->join('consumer_order_info oi', 'co.id=oi.order_id', 'left')->order('co.id desc')->select();
    }
}