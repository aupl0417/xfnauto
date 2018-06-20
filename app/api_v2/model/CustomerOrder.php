<?php

/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:47
 */
namespace app\api_v2\model;

use think\Db;
use think\Model;

class CustomerOrder extends Model
{

    protected $table = 'customer_order';

    public function getOrderById($id, $field = '*'){
        if(empty($id) || !is_numeric($id)){
            return false;
        }

        return Db::name($this->table)->field($field)->where(['customer_order_id' => $id, 'is_delete' => 0])->find();
    }

    /*
     * 单月各状态的订单统计
     * */
    public function orderCount($condition = '', $userId, $orgId, $isRole = false){
        $where['system_user_id'] = ['in', $userId];
        $where['org_id']         = ['in', $orgId];
        $where['create_date']    = ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]];
        $where['is_delete']      = 0;

        $obj = Db::name($this->table)->where($where);
        if($condition){
            $cond = array();
            if($condition == 6){
                $condition = ['in', [7, 9, 11]];
            }elseif($condition == 12){
                $condition = ['in', [13, 15, 17]];
            }
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
    public function orderFeeCount($type, $userId, $orgId, $isRole = false){
        $where['system_user_id'] = ['in', $userId];
        $where['org_id']         = ['in', $orgId];
        $where['create_date']    = ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]];
        $where['is_delete']      = 0;

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
    public function orderFeeList($type, $userId, $orgId, $isRole = false, $page = 1, $pageSize = 10){
        $where['system_user_id'] = ['in', $userId];
        $where['create_date']    = ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]];
        $where['org_id']         = ['in', $orgId];
        $where['is_delete']      = 0;
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

        return Db::name($this->table)->where($where)->field($field)->page($page, $pageSize)->order('create_date desc')->select();
    }

    public function getOrderList($where = '', $page = 1, $pageSize = 10){
        $field = 'customer_order_id as id,customer_order_code as orderId,customer_order_state as orderState,cars_name as carName,create_date as createTime';
        $data  = Db::name('customer_order')->where($where)->field($field)->page($page, $pageSize)->order('create_date desc')->select();
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

    public function getReturnVisitCount($userId, $orgId, $isRole = false){
        $where = [
            'create_date'          => ['between', [date('Y-m-d', strtotime('-7 day')), date('Y-m-d H:i:s')]],
            'customer_order_state' => 17,
            'org_id'               => ['in', $orgId],
            'is_delete'            => 0,
            'system_user_id'       => ['in', $userId]
        ];

        return Db::name($this->table)->where($where)->count();
    }

    public function getReturnVisitList($where = '', $page = 1, $pageSize = 10){
//        $field = 'customer_order_id as id,customer_name as customerName,customer_phone_number as customerPhone,system_user_name as sellerName,cars_name as carName,create_date as createTime';
        $field = 'customer_users_org_id as id,customer_users_name as username,phone_number as phone,co.create_date as createTime,co.org_id as orgId,time_of_appointment_date as timeOfAppointmentDate,co.system_user_name as systemUsername,cars_name as carName,expect_way_id as expectWay,intensity';
        $join  = [
            ['customer_customerorg org', 'org.customer_users_org_id=co.customer_id', 'left'],
        ];
        $data  = Db::name('customer_order co')->where($where)->field($field)->join($join)->page($page, $pageSize)->order('co.create_date desc')->select();
        return $data;
    }

    public function getOrderListAll($where = '', $page = 1, $pageSize = 10){
        $field = 'customer_order_id as id,customer_order_code as orderId,customer_order_state as orderState,customer_name as customerName,customer_phone_number as phoneNumber,cars_name as carName,system_user_name as systemUserName,create_date as createTime';
        $data  = Db::name('customer_order')->where($where)->field($field)->page($page, $pageSize)->order('create_date desc')->select();
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

        return ['list' => $data, 'total' => Db::name('customer_order')->where($where)->count(), 'page' => $page, 'rows' => $pageSize];
    }


    public function getOrderDetailByOrderId($orderId, $field = '*'){
        if(!$orderId || !is_numeric($orderId)){
            return false;
        }

        $where = ['co.customer_order_id' => $orderId, 'co.is_delete' => 0];
        $join  = [
            ['stock_car sc', 'sc.stock_car_id=co.stock_car_id', 'left'],
            ['system_organization org', 'org.orgId=co.org_id', 'left'],
            ['customer_customerusers cu', 'cu.customerUsersId=co.customer_id', 'left'],
            ['car_cars ca', 'ca.carId=co.cars_id', 'left'],
        ];
        $data  = Db::name($this->table . ' co')->where($where)->field($field)->join($join)->find();
        if($data){
            $data['amountMoney']       = getMoneyNumberString($data['amount']);
            $data['depositPriceMoney'] = getMoneyNumberString($data['depositPrice']);
            $data['totalAmount']       = $data['amount'] - $data['loan'];
            $data['payAmount']         = $data['totalAmount'] - Db::name('customer_order_in_pay')->where(['customer_order_id' => $orderId, 'order_in_pay_state' => 1])->sum('amount');
        }
        return $data;
    }
}