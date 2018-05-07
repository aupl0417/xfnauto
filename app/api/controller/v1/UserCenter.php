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
        (!isset($this->data['orgId']) || empty($this->data['orgId'])) && $this->apiReturn(201, '', '组织ID不能为空');

        $orgId  = $this->data['orgId'] + 0;

//        $user = Db::name('system_user')->where(['usersId' => $userId, 'isEnable' => 1, 'orgId' => $orgId])->count();
//        !$user && $this->apiReturn(201, '', '系统用户不存在');

        $data = array();
        $data['intensityCount'] = Db::name('customer_customerorg')->where(['system_user_id' => $this->userId, 'intensity' => 1])->count();
        $customermodel = model('CustomerOrder');
        $data['customer'] = [
            'userCount'    => Db::name('customer_customerorg')->where('time_of_appointment_date', ['>=', date('Y-m-01')], ['<=', date('Y-m-t 23:59:59')], 'and')->where(['system_user_id' => $this->userId])->count(),
            'total'        => $customermodel->orderCount('', $this->userId, $orgId),
            'unpayDeposit' => $customermodel->orderCount(1, $this->userId, $orgId),
            'bankAudit'    => $customermodel->orderCount(3, $this->userId, $orgId),
            'undelivery'   => $customermodel->orderCount(5, $this->userId, $orgId),
            'others'       => $customermodel->orderCount(['customer_order_state', ['>',5], ['<',15], 'and'], $this->userId, $orgId),
            'unfinished'   => $customermodel->orderCount(15, $this->userId, $orgId),
            'finished'     => $customermodel->orderCount(17, $this->userId, $orgId),
            'insurance'    => $customermodel->orderFeeCount('insurance', $this->userId, $orgId),
            'mortgage'     => $customermodel->orderFeeCount('mortgage', $this->userId, $orgId),
            'boutique'     => $customermodel->orderFeeCount('boutique', $this->userId, $orgId),
            'license'      => $customermodel->orderFeeCount('license', $this->userId, $orgId),
        ];

        $consumermodel = model('ConsumerOrder');
        $data['consumer'] = [
            'placeOrder'        => $consumermodel->orderCount(1, $this->userId, $orgId),
            'total'             => $consumermodel->orderCount('', $this->userId, $orgId),
            'unpayDeposit'      => $consumermodel->orderCount(3, $this->userId, $orgId),
            'unpayfinalpayment' => $consumermodel->orderCount(25, $this->userId, $orgId),
            'carMatch'          => $consumermodel->orderCount(10, $this->userId, $orgId),
            'carCheck'          => $consumermodel->orderCount(15, $this->userId, $orgId),
            'outStock'          => $consumermodel->orderCount(30, $this->userId, $orgId),
            'uploadTicket'      => $consumermodel->orderCount(35, $this->userId, $orgId),
            'commercial'        => $consumermodel->orderCount('commercial', $this->userId, $orgId),
            'traffic'           => $consumermodel->orderCount('traffic', $this->userId, $orgId),
        ];

        $data['commission'] = [
            'returnVisit' => Db::name('customer_order')->where(['create_date' => ['between', [date('Y-m-d', strtotime('-6 day')), date('Y-m-d H:i:s')]], 'customer_order_state' => 17, 'org_id' => $orgId, 'system_user_id' => $this->userId])->count(),
            'appointment' => Db::name('customer_customerorg')->where(['time_of_appointment' => ['between', [date('Y-m-d'), date('Y-m-d 23:59:59')]], 'org_id' => $orgId, 'system_user_id' => $this->userId])->count(),
        ];

        $this->apiReturn(200, $data);
    }

    public function customers(){
        (!isset($this->data['orgId']) || empty($this->data['orgId'])) && $this->apiReturn(201, '', '组织ID不能为空');

        $where = ['system_user_id' => $this->userId, 'intensity' => 1];
        if(isset($this->data['keyword']) || empty($this->data['keyword'])){
            $keyword = htmlspecialchars(trim($this->data['keyword']));
            $fields  = !checkPhone($keyword) ? 'customer_users_name' : 'phone_number';
            $where[$fields] = ['like', '%' . $keyword . '%'];
        }

        $field = 'customer_users_org_id as id,customer_users_name as username,phone_number as phone,create_date as createTime,system_user_name as systemUsername,carName,expect_way_name as expectWay';

        $data = Db::name('customer_customerorg')->where($where)->join('car_cars', 'intention_car_id=carId')->field($field)->select();
        !$data && $this->apiReturn(201, '', '暂无记录');
        $this->apiReturn(200, $data);
    }

}