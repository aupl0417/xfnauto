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
class UserCenter extends Home
{

    public function index(){
        $data = array();
        $data['intensityCount'] = Db::name('customer_customerorg')->where(['system_user_id' => $this->userId, 'intensity' => '高'])->count();
        $customermodel = model('CustomerOrder');
        $data['customer'] = [
            'userCount'    => Db::name('customer_customerorg')->where('time_of_appointment_date', ['>=', date('Y-m-01')], ['<=', date('Y-m-t 23:59:59')], 'and')->where(['system_user_id' => $this->userId])->count(),
            'total'        => $customermodel->orderCount('', $this->userId, $this->orgId),
            'unpayDeposit' => $customermodel->orderCount(1, $this->userId, $this->orgId),
            'bankAudit'    => $customermodel->orderCount(3, $this->userId, $this->orgId),
            'undelivery'   => $customermodel->orderCount(5, $this->userId, $this->orgId),
            'others'       => $customermodel->orderCount(['customer_order_state', ['>',5], ['<',15], 'and'], $this->userId, $this->orgId),
            'unfinished'   => $customermodel->orderCount(15, $this->userId, $this->orgId),
            'finished'     => $customermodel->orderCount(17, $this->userId, $this->orgId),
            'insurance'    => $customermodel->orderFeeCount('insurance', $this->userId, $this->orgId),
            'mortgage'     => $customermodel->orderFeeCount('mortgage', $this->userId, $this->orgId),
            'boutique'     => $customermodel->orderFeeCount('boutique', $this->userId, $this->orgId),
            'license'      => $customermodel->orderFeeCount('license', $this->userId, $this->orgId),
        ];

        $consumermodel = model('ConsumerOrder');
        $data['consumer'] = [
            'placeOrder'        => $consumermodel->orderCount(1, $this->userId, $this->orgId),
            'total'             => $consumermodel->orderCount('', $this->userId, $this->orgId),
            'unpayDeposit'      => $consumermodel->orderCount(3, $this->userId, $this->orgId),
            'unpayfinalpayment' => $consumermodel->orderCount(25, $this->userId, $this->orgId),
            'carMatch'          => $consumermodel->orderCount(10, $this->userId, $this->orgId),
            'carCheck'          => $consumermodel->orderCount(15, $this->userId, $this->orgId),
            'outStock'          => $consumermodel->orderCount(30, $this->userId, $this->orgId),
            'uploadTicket'      => $consumermodel->orderCount(35, $this->userId, $this->orgId),
            'commercial'        => $consumermodel->orderCount('commercial', $this->userId, $this->orgId),
            'traffic'           => $consumermodel->orderCount('traffic', $this->userId, $this->orgId),
        ];

        $data['commission'] = [
            'returnVisit' => Db::name('customer_order')->where(['create_date' => ['between', [date('Y-m-d', strtotime('-6 day')), date('Y-m-d H:i:s')]], 'customer_order_state' => 17, 'org_id' => $this->orgId, 'system_user_id' => $this->userId])->count(),
            'appointment' => Db::name('customer_customerorg')->where(['appointment_date' => ['between', [date('Y-m-d'), date('Y-m-d 23:59:59')]], 'org_id' => $this->orgId, 'system_user_id' => $this->userId])->count(),
        ];

        $this->apiReturn(200, $data);
    }

    public function customers(){
        $type  = isset($this->data['type']) && !empty($this->data['type']) ? trim($this->data['type']) : 'all';
        !in_array($type, ['all', 'intensity', 'visit']) && $this->apiReturn(201, '', '参数非法');

        $where = ['system_user_id' => $this->userId, 'org_id' => $this->orgId];
        if($type == 'intensity'){
            $where['intensity'] = '高';
        }

        if(isset($this->data['keyword']) && !empty($this->data['keyword'])){
            $keyword = htmlspecialchars(trim($this->data['keyword']));
            $fields  = !checkPhone($keyword) ? 'customer_users_name' : 'phone_number';
            $where[$fields] = ['like', '%' . $keyword . '%'];
        }

        $field = 'customer_users_org_id as id,customer_users_name as username,phone_number as phone,create_date as createTime,system_user_name as systemUsername,carName,expect_way_name as expectWay';

        if($type == 'visit'){
            $where['appointment_date'] = ['between', [date('Y-m-d'), date('Y-m-d 23:59:59')]];
        }else{
            $where['appointment_date'] = ['between', [date('Y-m-01'), date('Y-m-d 23:59:59')]];
        }

//        $data = Db::name('customer_customerorg')->where('time_of_appointment_date', ['>=', date('Y-m-01')], ['<=', date('Y-m-t 23:59:59')], 'and')->where($where)->join('car_cars', 'intention_car_id=carId', 'left')->field($field)->select();
        $data = Db::name('customer_customerorg')->where($where)->join('car_cars', 'intention_car_id=carId', 'left')->field($field)->select();
        !$data && $this->apiReturn(201, '', '暂无记录');
        $this->apiReturn(200, $data);
    }

    /*
     *
     * */
    public function visit(){
        $where = [
            'create_date'          => ['between', [date('Y-m-d', strtotime('-6 day')), date('Y-m-d H:i:s')]],
            'customer_order_state' => 17,
            'org_id'               => $this->orgId,
            'system_user_id'       => $this->userId
        ];
        $data = model('CustomerOrder')->getOrderList($where);
        !$data && $this->apiReturn(201, '', '暂无数据');
        $this->apiReturn(200, $data);
    }

    public function quotation(){
        $result = $this->validate($this->data, 'AddQuotation');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }
        
        $priceListKey = ['bareCarPrice', 'purchase_tax', 'license_plate_priace', 'vehicle_vessel_tax', 'insurance_price', 'traffic_insurance_price', 'boutique_priace', 'quality_assurance', 'other'];
        $total = 0;
        foreach($this->data as $key => $value){
            if(in_array($key, $priceListKey)){
                $total += $value;
            }
        }
        $total = $this->data['type'] == 1 ? $total : $total * (100 - $this->data['down_payment_rate']) / 100;
        if($this->data['total_fee'] != $total){
            $this->apiReturn(201, '', '预计付费总金额不一致');
        }

        if($this->data['type'] == 1){
            $this->data['down_payment_rate'] = 0;
            $this->data['periods'] = 0;
            $this->data['annual_rate'] = 0;
            $this->data['monthly_supply'] = 0;
        }

        $monthlySupply = $this->data['type'] == 2 ? $total * $this->data['annual_rate'] / $this->data['periods'] / 100 : 0;
        $this->data['monthly_supply'] = intval($monthlySupply * 100) / 100;
        $this->data['create_user_id'] = $this->userId;
        $this->data['create_time']    = date('Y-m-d H:i:s');
        unset($this->data['sessionId']);
//        $this->apiReturn(200, $this->data);
        $result = Db::name('consumer_car_quotation')->insert($this->data);
        $this->data['user'] = ['username' => $this->user['userName'], 'phone' => $this->user['phoneNumber']];
        $this->data['carName'] = Db::name('car_cars')->where(['carId' => $this->data['carId']])->field('carName')->find()['carName'];
        $this->data['buycarStyle'] = $this->data['type'] == 1 ? '全款' : '按揭';
        !$result && $this->apiReturn(201, '', '提交数据失败');
        $this->apiReturn(200, $this->data);
    }

}