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

    /*
     * 订单各费用统计列表
     * */
    public function orderFeeList($type, $userId, $orgId){
        $startTime = date('Y-m-01', strtotime('-1 month'));
        $endTime   = date('Y-m-t 23:59:59');
        $where['create_date']    = ['between', [$startTime, $endTime]];
        $where['system_user_id'] = $userId;
        $where['org_id']         = $orgId;
        $field = 'customer_order_id as id,customer_order_code as orderId,customer_order_state as orderState,cars_name as carName,create_date as createTime';
        switch ($type){
            case 'insurance':
                $where['insurance_priace'] = ['>', 0];
                break;
            case 'mortgage':
                $where['mortgage_priace'] = ['>', 0];
                break;
            case 'boutique':
                $where['boutique_priace'] = ['>', 0];
                break;
            case 'license':
                $where['license_plate_priace'] = ['>', 0];

        }

        return Db::name($this->table)->where($where)->field($field)->order('customer_order_id desc')->select();
    }

    public function getOrderList($where = ''){
        $field = 'customer_order_id as id,customer_order_code as orderId,customer_order_state as orderState,cars_name as carName,create_date as createTime';
        $data  = Db::name('customer_order')->where($where)->field($field)->order('customer_order_id desc')->select();
        if($data){
            $stateArr = [
                '0' => '初始',
                '1' => '待收定金',
                '3' => '等待银行审核',
                '4' => '银行审核不通过',
                '5' => '等待车辆出库',
                '7' => '等待加装精品',
                '9' => '等待上牌',
                '11' => '等待贴膜',
                '13' => '等待交车',
                '15' => '人车已合照',
                '17' => '已完款,交车放行',
                '19' => '已回访',
            ];
            foreach($data as $key => &$value){
                $value['orderStateName'] = $stateArr[$value['orderState']];
            }
        }
        
        return $data;
    }
}