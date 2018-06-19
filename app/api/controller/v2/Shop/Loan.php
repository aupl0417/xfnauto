<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-06-11
 * Time: 9:28
 */

namespace app\api\controller\v2\Shop;

use app\api\model\CustomerOrder;
use app\api\model\ShopLoanApply;
use think\Controller;
use think\Db;
use think\Exception;

class Loan extends Base
{

    /**
     * 垫资申请列表
     * */
    public function index(){
        $page = (isset($this->data['page']) && !empty($this->data['page'])) ? $this->data['page'] + 0 : 1;
        $rows = (isset($this->data['rows']) && !empty($this->data['rows'])) ? $this->data['rows'] + 0 : 10;

        $where = ['sa_userId' => $this->userId, 'sa_isDel' => 0, 'sa_type' => 1];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            if(preg_match('/^DZ\w+/', $keywords)){
                $where['sa_orderId'] = ['like', '%' . $keywords . '%'];
            }elseif(checkTimeIsValid($keywords)){
                $where['sa_createTime'] = ['like', '%' . $keywords . '%'];
            }else{
                $orderIds = Db::name('shop_loan_apply_info')->where(['sai_carName' => ['like', '%' . $keywords . '%'], 'sai_isDel' => 0])->distinct('sai_orderId')->field('sai_orderId')->select();
                !$orderIds && $this->apiReturn(200, ['list' => [], 'page' => $page, 'rows' => $page, 'total' => 0]);
                $orderIds = array_column($orderIds, 'sai_orderId');
                $where['sa_orderId'] = ['in', $orderIds];
            }
        }

        if(isset($this->data['state']) && !empty($this->data['state'])){
            $state = $this->data['state'] + 0;
            $where['sa_state'] = $state;
        }

        $data = model('ShopLoanApply')->getDataByPage($where, $page, $rows);
        $this->apiReturn(200, $data);
    }

    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id   = $this->data['id'] + 0;
        $data = model('ShopLoanApply')->getShopLoanApplyByIdAll($id);
        if($data){
            $field = $this->getField('shop_info', '', false, '', true);
            $data['info'] = model('ShopInfo')->getShopInfoByUserId($data['userId'], $field);
        }
        $this->apiReturn(200, $data);
    }
    
    
    public function test2(){
        $shopLoanApply = ShopLoanApply::get(15);
        $shopLoanApply = $shopLoanApply->ShopLoanApplyInfo()->select();
        for($i=0;$i<count($shopLoanApply);$i++){
            $data[] = $shopLoanApply[$i]->toArray();
        }
        dump($data);
    }

    /**
     * 垫资资格申请
     * */
    public function apply(){

    }

    /**
     * 添加垫资单
     * */
    public function create(){
        $field = 'head_portrait as headPortrait,real_name as realName,phone_number as phoneNumber,s_state as loanState,si_state as shopState';
        $user  = model('ShopUser')->getUserByIdAll($this->userId, $field);
        !$user && $this->apiReturn(201, '', '用户不存在');

        (is_null($user['shopState']) || $user['shopState'] != 1) && $this->apiReturn(201, '', '店铺认证未认证');
        (is_null($user['loanState']) || $user['loanState'] != 1) && $this->apiReturn(201, '', '您没有垫资资格');

        $result = $this->validate($this->data, 'LoanAdd');
        $result !== true && $this->apiReturn(201, '', $result);
        $totalAmount = 0;
        $orderId = makeOrder('DZ', 4);
        foreach($this->data['carsInfo'] as $key => $value){
            $result = $this->validate($value, 'LoanAddCar');
            if($result !== true){
                $this->apiReturn(201, '', $result);
            }

            $amount = $value['price'] * (100 - $value['downPayments']) * $value['number'] / 100;
            $totalAmount += $amount;
            foreach($value as $k => $val){
                $info[$key]['sai_' . $k] = $value[$k];
            }
            $info[$key]['sai_carName'] = isset($this->data['carName']) ? htmlspecialchars(trim($this->data['carName'])) : Db::name('car_cars')->where(['carId' => $value['carId']])->field('carName')->find()['carName'];
            $info[$key]['sai_colorName'] = isset($this->data['colorName']) ? htmlspecialchars(trim($this->data['colorName'])) : Db::name('car_carcolour')->where(['carColourId' => $value['colorId']])->field('carColourName')->find()['carColourName'];
            $info[$key]['sai_createTime'] = time();
            $info[$key]['sai_orderId']    = $orderId;
            if($amount != $value['amount']){
                $this->apiReturn(201, '', '车型：' . $value['carName'] . '的垫资总额不一致');
            }
            ksort($info[$key]);
        }

        $orgId = $this->data['orgId'] + 0;
        $org   = model('Organization')->getOrganizationByOrgId($orgId, 'shortName,orgCode');

        if($this->data['amount'] != $totalAmount){
            $this->apiReturn(201, '', '垫资总额不一致' . $totalAmount);
        }
        $rate   = floatval($this->data['rate']);
        $fee    = $totalAmount * $rate / 100 * $this->data['period'];
        if($this->data['fee'] != $fee){
            $this->apiReturn(201, '', '手续费不一致' . $fee);
        }
        try{
            Db::startTrans();
            $data = [
                'sa_orderId' => $orderId,
                'sa_userId'  => $this->userId,
                'sa_userName' => $user['realName'],
                'sa_phone' => $user['phoneNumber'],
                'sa_type' => 1,
                'sa_orgId' => $orgId,
                'sa_orgName' => $org['shortName'],
                'sa_orgCode' => $org['orgCode'],
                'sa_amount' => $totalAmount,
                'sa_rate'   => $rate,
                'sa_fee'    => $fee,
                'sa_period' => $this->data['period'] + 0,
                'sa_createTime' => time()
            ];

            $result = Db::name('shop_loan_apply')->insert($data);
            if(!$result){
                throw new Exception('插入到垫资申请表失败');
            }

            $result = Db::name('shop_loan_apply_info')->insertAll($info);
            if(!$result){
                throw new Exception('插入到垫资申请明细表失败');
            }

            Db::commit();
            $this->apiReturn(200, '', '提交成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '提交失败' . $e->getMessage());
        }

    }

    public function test(){
//        $fp = fsockopen("smtp.163.com",25,$errno,$errstr,60);
//        if(! $fp)
//            echo '$errstr ($errno) <br> \n ';
//        else
//            echo 'ok <br> \n ';die;
        $result = sendEmail('770517692@qq.com', '这是测试内容', 'jiangjun', '测试');
        dump($result);
    }

}