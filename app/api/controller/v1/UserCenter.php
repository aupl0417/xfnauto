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

    /**
     * 首页统计
     * @return json
     * */
    public function index(){
        $data = array();
        $customerModel    = model('CustomerOrder');
        $customerOrgModel = model('CustomerOrg');
        $data['intensityCount'] = $customerOrgModel->customerCount($this->userId, $this->orgId, true, $this->isRole);
        $data['customer'] = [
            'userCount'    => $customerOrgModel->customerCount($this->userId, $this->orgId, false, $this->isRole),
            'total'        => $customerModel->orderCount('', $this->userId, $this->orgId, $this->isRole),
            'unpayDeposit' => $customerModel->orderCount(1, $this->userId, $this->orgId, $this->isRole),
            'bankAudit'    => $customerModel->orderCount(3, $this->userId, $this->orgId, $this->isRole),
            'undelivery'   => $customerModel->orderCount(5, $this->userId, $this->orgId, $this->isRole),
            'others'       => $customerModel->orderCount(6, $this->userId, $this->orgId, $this->isRole),
            'unfinished'   => $customerModel->orderCount(15, $this->userId, $this->orgId, $this->isRole),
            'finished'     => $customerModel->orderCount(17, $this->userId, $this->orgId, $this->isRole),
            'insurance'    => $customerModel->orderFeeCount('insurance', $this->userId, $this->orgId, $this->isRole),
            'mortgage'     => $customerModel->orderFeeCount('mortgage', $this->userId, $this->orgId, $this->isRole),
            'boutique'     => $customerModel->orderFeeCount('boutique', $this->userId, $this->orgId, $this->isRole),
            'license'      => $customerModel->orderFeeCount('license', $this->userId, $this->orgId, $this->isRole),
        ];

        $consumerModel = model('ConsumerOrder');
        $data['consumer'] = [
            'placeOrder'        => $consumerModel->orderCount(1, $this->userId, $this->orgId, $this->isRole),//开单
            'total'             => $consumerModel->orderCount('', $this->userId, $this->orgId, $this->isRole),//本月总订单
            'unpayDeposit'      => $consumerModel->orderCount(5, $this->userId, $this->orgId, $this->isRole),//待收定金
//            'unpayfinalpayment' => $consumerModel->orderCount(25, $this->userId, $this->orgId, $this->isRole),//待换车
            'carMatch'          => $consumerModel->orderCount(10, $this->userId, $this->orgId, $this->isRole),//待配车
            'carCheck'          => $consumerModel->orderCount(15, $this->userId, $this->orgId, $this->isRole),//待验车
//            'consulting'        => $consumerModel->orderCount(30, $this->userId, $this->orgId, $this->isRole),//待协商
            'finnalprice'       => $consumerModel->orderCount(35, $this->userId, $this->orgId, $this->isRole),//待收尾款
            'outStock'          => $consumerModel->orderCount(40, $this->userId, $this->orgId, $this->isRole),//待出库
            'tickitUploading'   => $consumerModel->orderCount(45, $this->userId, $this->orgId, $this->isRole),//待上传票证
            'commercial'        => $consumerModel->orderFeeCount('commercial', $this->userId, $this->orgId, $this->isRole),//商业险
            'traffic'           => $consumerModel->orderFeeCount('traffic', $this->userId, $this->orgId, $this->isRole),//交强险
        ];

        $data['commission'] = [
            'returnVisit' => $customerModel->getReturnVisitCount($this->userId, $this->orgId, $this->isRole),
            'appointment' => $customerOrgModel->customerAppointmentCount($this->userId, $this->orgId, $this->isRole)
        ];

        $this->apiReturn(200, $data);
    }

    public function customers(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        $type  = isset($this->data['type']) && !empty($this->data['type']) ? trim($this->data['type']) : 'all';
        !in_array($type, ['all', 'intensity', 'visit']) && $this->apiReturn(201, '', '参数非法');

        $group = model('SystemUser')->getUserGroupInfo($this->userId);
        if($group['over_manage'] == 1){
            $where['org_id']         = $group['orgId'];
        }else{
            $where['system_user_id'] = $this->userId;
        }

        if($type == 'intensity'){
            $where['intensity'] = '高';
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $fields  = !checkPhone($keywords) ? 'customer_users_name' : 'phone_number';
            $where[$fields] = ['like', '%' . $keywords . '%'];
        }

        if($type == 'visit'){
            $where['appointment_date'] = ['between', [date('Y-m-d'), date('Y-m-d 23:59:59')]];
        }else{
            $where['time_of_appointment_date'] = ['between', [date('Y-m-01'), date('Y-m-d 23:59:59')]];
        }

        $field = 'customer_users_org_id as id,customer_users_name as username,phone_number as phone,create_date as createTime,org_id as orgId,time_of_appointment_date as timeOfAppointmentDate,system_user_name as systemUsername,carName,expect_way_id as expectWay';
//        $data = Db::name('customer_customerorg')->where('time_of_appointment_date', ['>=', date('Y-m-01')], ['<=', date('Y-m-t 23:59:59')], 'and')->where($where)->join('car_cars', 'intention_car_id=carId', 'left')->field($field)->select();
        $data = Db::name('customer_customerorg')->where($where)->page($page, $rows)->join('car_cars', 'intention_car_id=carId', 'left')->field($field)->select();
        !$data && $this->apiReturn(200, '', '暂无记录');
//        echo Db::name('customer_customerorg')->getLastSql();die;
        $this->apiReturn(200, $data);
    }

    /**
     * 今日回访列表
     * @return json
     * */
    public function visit(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        $where = [
            'create_date'          => ['between', [date('Y-m-d', strtotime('-7 day')), date('Y-m-d H:i:s')]],
            'customer_order_state' => 17,
            'org_id'               => $this->orgId,
            'is_delete'            => 0,
        ];

        $group = model('SystemUser')->getUserGroupInfo($this->userId);
        if($group['over_manage'] == 1){
            $where['org_id']         = $group['orgId'];
        }else{
            $where['system_user_id'] = $this->userId;
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $fields  = !checkPhone($keywords) ? 'customer_name' : 'customer_phone_number';
            $where[$fields] = ['like', '%' . $keywords . '%'];
        }

        $data = model('CustomerOrder')->getReturnVisitList($where, $page, $rows);
        !$data && $this->apiReturn(200, '', '暂无数据');
        $this->apiReturn(200, $data);
    }

    /**
     * 报价单提交
     * @return json
     * */
    public function quotation(){
        $result = $this->validate($this->data, 'AddQuotation');
        if($result !== true){
            $this->apiReturn(201, '', $result . '_1');
        }
        
        $priceListKey = ['bareCarPrice', 'purchase_tax', 'license_plate_priace', 'vehicle_vessel_tax', 'insurance_price', 'traffic_insurance_price', 'boutique_priace', 'quality_assurance', 'other'];
        $totalFee = 0;
        $type  = $this->data['type'] + 0;
        if($type == 2){
            $priceListKey[] = 'mortgage';
        }
        foreach($this->data as $key => $value){
            if(in_array($key, $priceListKey)){
                $totalFee += $value;
            }
        }

        $total = $type == 1 ? ceil($totalFee) : ceil($totalFee * $this->data['down_payment_rate'] / 100);
        if($this->data['total_fee'] != $total){
            $this->apiReturn(201, '', '预计付费总金额不一致');
        }

        if($type == 1){
            $this->data['down_payment_rate'] = 0;
            $this->data['periods'] = 0;
            $this->data['annual_rate'] = 0;
            $this->data['monthly_supply'] = 0;
            $this->data['mortgage'] = 0;
        }

        $monthlySupply = $type == 2 ? $totalFee * (100 - $this->data['down_payment_rate']) * (100 + $this->data['annual_rate']) / $this->data['periods'] / 100 / 100 : 0;
        $monthlySupply = ceil($monthlySupply);
//        $this->apiReturn(201, '', '月供不一致'. $monthlySupply . '_' . $this->data['monthly_supply']);
        if(intval($monthlySupply) != intval($this->data['monthly_supply'])){
            $this->apiReturn(201, '', '月供不一致');
        }
        $this->data['monthly_supply'] = $monthlySupply;
        $this->data['create_user_id'] = $this->userId;
        $this->data['create_time']    = date('Y-m-d H:i:s');
        unset($this->data['sessionId']);
        $result = Db::name('consumer_car_quotation')->insert($this->data);

        !$result && $this->apiReturn(201, '', '提交数据失败');
        $this->apiReturn(200, ['id' => Db::name('consumer_car_quotation')->getLastInsID()]);
    }

    public function quotationDetail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '报价单ID非法');

        $id   = $this->data['id'] + 0;

        $data = Db::name('consumer_car_quotation')->where(['id' => $id])->find();
        !$data && $this->apiReturn(201, '', '报价单数据不存在');
        $user = model('SystemUser')->getUserById($data['create_user_id']);
        $data['user'] = ['username' => $user['realName'], 'phone' => $user['phoneNumber']];
        $data['carName'] = Db::name('car_cars')->where(['carId' => $data['carId']])->field('carName')->find()['carName'];
        $data['buycarStyle'] = $data['type'] == 1 ? '全款' : '按揭';
        unset($data['carId'], $data['create_user_id']);
        $this->apiReturn(200, $data);
    }

    /**
     * 客户管理
     * @param page int
     * @param rows int
     * @param buyCarAlready int 可选
     * @param paymentWay    int 可选
     * @param orderStates   int 可选
     * @param customerUsersSearch string 可选
     * @return json
     * */
    public function userList(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where = ['cu.org_id' => ['in', $this->orgIds], 'co.is_delete' => 0];
        if(isset($this->data['buyCarAlready']) && $this->data['buyCarAlready'] != ''){
            $buyCarAlready = $this->data['buyCarAlready'] + 0;
            $where['cus.buyCarAlready'] = $buyCarAlready;
        }

        if(isset($this->data['paymentWay']) && !empty($this->data['paymentWay']) && in_array(intval($this->data['paymentWay']), [1, 2], true)){
            $paymentWay = $this->data['paymentWay'] + 0;
//            $where['co.payment_way'] = $paymentWay;
            $where['cu.expect_way_id'] = $paymentWay;
        }

        if(isset($this->data['orderStates']) && $this->data['orderStates'] != ''){
            $orderStates = $this->data['orderStates'];
            $where['co.customer_order_state'] = $orderStates;
        }

        if(isset($this->data['customerUsersSearch']) && !empty($this->data['customerUsersSearch'])){
            $customerUsersSearch = trim($this->data['customerUsersSearch']);
            $searchField = !checkPhone($customerUsersSearch) ? 'customer_users_name' : 'phone_number';
            $where['cu.' . $searchField] = ['like', '%'. $customerUsersSearch . '%'];
        }

        $join  = [
            ['customer_order co', 'co.customer_id=cu.customer_users_Id', 'left'],
            ['customer_customerusers cus', 'cus.customerUsersId=cu.customer_users_Id', 'left'],
        ];
        $field = 'cu.customer_users_org_id as id,cu.intention_car_info as carsName,co.customer_order_id as customerOrderId,cu.customer_users_Id as customerUsersId,cu.customer_users_name as customerUsersName,cus.headPortrait,co.customer_order_state as orderState,cu.expect_way_id as paymentWay,cu.phone_number as phoneNumber,cu.system_user_name as systemUserName';
        $data  = Db::name('customer_customerorg cu')->where($where)->field($field)->join($join)->page($page, $rows)->order('cu.create_date desc')->select();
        $count = Db::name('customer_customerorg cu')->where($where)->join($join)->count();
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
            foreach($data as &$value){
                $value['orderStateName'] = $value['orderState'] ? $stateArr[$value['orderState']] : '';
            }
        }

        $this->apiReturn(200, ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows]);
    }

}