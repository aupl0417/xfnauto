<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-06-11
 * Time: 9:28
 */

namespace app\api\controller\v3\Frontend;

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

        $where = ['sa_isDel' => 0, 'sa_type' => 1];

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

    /**
     * 添加垫资单
     * */
    public function create(){

        $orgId = $this->data['orgId'] + 0;
        $org   = model('Organization')->getOrganizationByOrgId($orgId, 'shortName,orgCode');
        
        $shopInfo = model('ShopInfo')->getShopInfoByOrgId($orgId, 'si_id,si_state');
//        dump($shopInfo);die;
        (!$shopInfo || $shopInfo['si_state'] != 1) && $this->apiReturn(201, '', '店铺认证未认证或未通过审核');

        $shopLoan = model('ShopLoan')->getLoanByShopId($orgId, 's_state');
        (!$shopLoan || $shopLoan['s_state'] != 1) && $this->apiReturn(201, '', '您没有垫资资格');

        $result = $this->validate($this->data, 'LoanAdd');
        $result !== true && $this->apiReturn(201, '', $result);
        $totalAmount = 0;
        $period      = $this->data['period'] + 0;
        $orderId     = makeOrder('DZ', 4);
        $rate        = floatval($this->data['rate']);
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
            $info[$key]['sai_carName']    = isset($this->data['carName']) ? htmlspecialchars(trim($this->data['carName'])) : Db::name('car_cars')->where(['carId' => $value['carId']])->field('carName')->find()['carName'];
            $info[$key]['sai_colorName']  = isset($this->data['colorName']) ? htmlspecialchars(trim($this->data['colorName'])) : Db::name('car_carcolour')->where(['carColourId' => $value['colorId']])->field('carColourName')->find()['carColourName'];
            $info[$key]['sai_createTime'] = time();
            $info[$key]['sai_orderId']    = $orderId;
            $info[$key]['sai_fee']        = $value['price'] * $value['number'] * (100 - $value['downPayments']) / 100 * $rate / 100 * $period;
            $info[$key]['sai_carImage']   = Db::name('car_cars')->where(['carId' => $value['carId']])->field('indexImage')->find()['indexImage'];

            if($amount != $value['amount']){
                $this->apiReturn(201, '', '车型：' . $value['carName'] . '的垫资总额不一致');
            }
        }

        if($this->data['amount'] != $totalAmount){
            $this->apiReturn(201, '', '垫资总额不一致' . $totalAmount);
        }

        $fee    = $totalAmount * $rate / 100;
        if($this->data['fee'] != $fee){
            $this->apiReturn(201, '', '手续费不一致' . $fee);
        }
        try{
            Db::startTrans();
            $data = [
                'sa_orderId'    => $orderId,
                'sa_userId'     => $this->userId,
                'sa_userName'   => $this->user['realName'],
                'sa_phone'      => $this->user['phoneNumber'],
                'sa_type'       => 1,
                'sa_platType'   => 2,//小程序
                'sa_orgId'      => $orgId,
                'sa_orgName'    => $org['shortName'],
                'sa_orgCode'    => $org['orgCode'],
                'sa_amount'     => $totalAmount,
                'sa_totalAmount'=> $totalAmount,
                'sa_rate'       => $rate,
                'sa_fee'        => $fee,
                'sa_feeTotal'   => 0,
                'sa_period'     => $period,
                'sa_createTime' => time()
            ];

            $result = Db::name('shop_loan_apply')->insert($data);
            if(!$result){
                throw new Exception('插入到垫资申请表失败');
            }

            $insertId = Db::name('shop_loan_apply')->getLastInsID();

            foreach($info as $key => $value){
                $info[$key]['sai_saId']       = $insertId;
                ksort($info[$key]);
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