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

class CustomerOrder extends Model
{

    protected $table = 'customer_order';

    public function getOrderById($id, $field = '*'){
        if(empty($id) || !is_numeric($id)){
            return false;
        }

        return Db::name($this->table)->field($field)->where(['customer_order_id' => $id])->find();
    }

    /*
     * 单月各状态的订单统计
     * */
    public function orderCount($condition = '', $userId, $orgId){
        $startTime = date('Y-m-01');
        $endTime   = date('Y-m-t 23:59:59');
        $where['create_date']    = ['between', [$startTime, $endTime]];
        $where['system_user_id'] = $userId;
        $where['org_id']         = $orgId;
        $obj = Db::name($this->table)->where($where);
        $cond = array();
        if($condition){
            if(!is_array($condition)){
                $condition = explode(',', $condition);
                $cond['customer_order_state'] = ['in', $condition];
                $obj = $obj->where($cond);
            }elseif($condition[0] == 'customer_order_state'){
                $obj = $obj->where($condition[0], $condition[1], $condition[2], $condition[3]);
            }else{
                $cond['customer_order_state'] = $condition;//支持['in/between', []]形式
                $obj = $obj->where($cond);
            }
        }

        return $obj->count();
    }

    /*
     * 订单各费用统计
     * */
    public function orderFeeCount($type, $userId, $orgId){
        $startTime = date('Y-m-01');
        $endTime   = date('Y-m-t 23:59:59');
        $where['create_date']    = ['between', [$startTime, $endTime]];
        $where['system_user_id'] = $userId;
        $where['org_id']         = $orgId;

        switch ($type){
            case 'insurance':
                $total = Db::name($this->table)->where($where)->sum('insurance_priace');
                break;
            case 'mortgage':
                $total = Db::name($this->table)->where($where)->sum('mortgage_priace');
                break;
            case 'boutique':
                $total = Db::name($this->table)->where($where)->sum('boutique_priace');
                break;
            case 'license':
                $total = Db::name($this->table)->where($where)->sum('license_plate_priace');
        }
        return $total;
    }

    public function getOrderList($where = ''){
        $field = 'customer_order_id as id,customer_order_code as orderId,customer_order_state as orderState,cars_name as carName';
        $data  = Db::name('customer_order')->where($where)
            ->field($field)
            ->select();

        return $data;
    }
}