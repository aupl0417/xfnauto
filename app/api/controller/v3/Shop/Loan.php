<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-06-11
 * Time: 9:28
 */

namespace app\api\controller\v3\Shop;

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

        $where = ['sa_userId' => $this->userId, 'sa_isDel' => 0, 'sa_type' => 1, 'sa_state' => ['neq', -1]];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            if(preg_match('/^DZ\w+/', $keywords) || preg_match('/^[A-Z0-9]+/', $keywords)){
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

        if(isset($this->data['state']) && $this->data['state'] != ''){
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
     * 垫资资格申请
     * */
    public function apply(){
        $field = 's_state as loanState,si_state as shopState';
        $user  = model('ShopUser')->getUserByIdAll($this->userId, $field);
        !$user && $this->apiReturn(201, '', '用户不存在');

        (is_null($user['shopState']) || $user['shopState'] != 1) && $this->apiReturn(201, '', '店铺认证未认证');

        if(Db::name('shop_loan')->where(['s_userId' => $this->userId, 's_state' => ['neq', 2]])->count()){
            $this->apiReturn(201, '', '您已提交申请，请勿重复提交');
        }
        $data = [
            's_shopId'     => $this->orgId,
            's_userId'     => $this->userId,
            's_state'      => 0,
            's_createTime' => time()
        ];

        $result = Db::name('shop_loan')->insert($data);
        !$result && $this->apiReturn(201, '', '申请失败');
        $this->apiReturn(200, '', '申请成功');
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
        $period      = $this->data['period'] + 0;
        $orderId     = strtoupper(makeOrder('DZ', 4));
        $rate        = floatval($this->data['rate']);
        $carsInfo    = $this->data['carsInfo'];
        if(is_string($this->data['carsInfo'])){
            if(strpos($carsInfo, '&quot;') !== false){
                $carsInfo = str_replace('&quot;', '"', $carsInfo);
            }
            $carsInfo = json_decode($carsInfo, true);
        }
        !is_array($carsInfo) && $this->apiReturn(201, '', '车型数据格式非法');
        foreach($carsInfo as $key => $value){
            $result = $this->validate($value, 'LoanAddCar');
            if($result !== true){
                $this->apiReturn(201, '', $result);
            }

            $amount = ceil($value['price'] * $value['number'] * (100 - $value['downPayments'])) / 100;
            $totalAmount += $amount;
            if(strval($amount) != $value['amount']){
                $this->apiReturn(201, '', '车型：' . $value['carName'] . '的垫资总额不一致' . $amount . '--' . $value['amount']);
            }
            foreach($value as $k => $val){
                if(in_array($k, ['carId', 'carName', 'colorId', 'colorName', 'guidancePrice', 'price', 'downPayments', 'number', 'familyId'])){
                    $info[$key]['sai_' . $k] = $value[$k];
                }else{
                    continue;
                }
            }
            $info[$key]['sai_amount']     = ceil($value['price'] *  (100 - $value['downPayments'])) / 100;
            $info[$key]['sai_carName']    = isset($this->data['carName']) ? htmlspecialchars(trim($this->data['carName'])) : Db::name('car_cars')->where(['carId' => $value['carId']])->field('carName')->find()['carName'];
            $info[$key]['sai_colorName']  = isset($this->data['colorName']) ? htmlspecialchars(trim($this->data['colorName'])) : Db::name('car_carcolour')->where(['carColourId' => $value['colorId']])->field('carColourName')->find()['carColourName'];
            $info[$key]['sai_createTime'] = time();
            $info[$key]['sai_orderId']    = $orderId;
//            $info[$key]['sai_fee']        = $value['price'] * $value['number'] * (100 - $value['downPayments']) / 100 * $rate / 100 * $period;
            $info[$key]['sai_fee']        = 0;
            $info[$key]['sai_carImage']   = Db::name('car_cars')->where(['carId' => $value['carId']])->field('indexImage')->find()['indexImage'];
        }
        $totalAmount = ceil($totalAmount * 100) / 100;
        if(strval($this->data['amount']) != strval($totalAmount)){
            $this->apiReturn(201, '', '垫资总额不一致' . $totalAmount . '---' . $this->data['amount']);
        }

        $fee       = round($totalAmount * $rate / 100, 2);
        $expectFee = ceil($totalAmount * $rate * $period) / 100;
        if($this->data['fee'] != $expectFee){
            $this->apiReturn(201, '', '预计总手续费不一致' . $expectFee);
        }
        try{
            Db::startTrans();
            $data = [
                'sa_orderId'    => $orderId,
                'sa_userId'     => $this->userId,
                'sa_userName'   => $user['realName'],
                'sa_phone'      => $user['phoneNumber'],
                'sa_type'       => 1,
                'sa_orgId'      => $this->orgId,
                'sa_orgName'    => $this->user['org_name'],
//                'sa_orgCode'    => $org['orgCode'],
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
                $info[$key]['sai_saId']   = $insertId;
                $info[$key]['sai_number'] = 1;
                ksort($info[$key]);
                for($i = 0; $i < $value['sai_number'] - 1; $i ++){
                    $info[] = $info[$key];
                }
            }

            $result = Db::name('shop_loan_apply_info')->insertAll($info);
            if(!$result){
                throw new Exception('插入到垫资申请明细表失败');
            }

            Db::commit();
            $this->apiReturn(200, '', '提交成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '提交失败');
        }
    }

    public function cancel(){
        (!isset($this->data['orderId']) || empty($this->data['orderId'])) && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['orderId'] + 0;
        $data    = model('ShopLoanApply')->getById($orderId, 'sa_id as id,sa_state as state');
        !$data && $this->apiReturn(201, '', '垫资数据不存在');
        $data['state'] != 0 && $this->apiReturn(201, '', '只有待审核状态才能取消');
        $result = Db::name('shop_loan_apply')->where(['sa_id' => $orderId, 'sa_isDel' => 0, 'sa_state' => 0])->update(['sa_state' => -1]);
        $result === false && $this->apiReturn(201, '', '取消失败');
        $this->apiReturn(200, '', '取消成功');
    }

    public function carcolor(){
        (!isset($this->data['carId']) || empty($this->data['carId'])) && $this->apiReturn(201, '', '参数非法');

        $carId = $this->data['carId'] + 0;
        $carInfo = model('Car')->getCarById($carId, 'familyId');
        $data = [];
        if($carInfo){
            $data    = Db::name('car_carcolour')->where(['familyId' => $carInfo['familyId'], 'isDelete' => 0])->field('carColourId as id,carColourName as name')->select();
        }
        $this->apiReturn(200, $data);
    }

    public function send(){
        (!isset($this->data['email']) || empty($this->data['email'])) && $this->apiReturn(201, '', '请输入邮箱地址');

        $email  = htmlspecialchars(trim($this->data['email']));
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $this->apiReturn(201, '', '邮箱地址格式非法');
        }
        $attach = [
            './static/zhang.png',
//            './static/zaizhicaetificate.docx', //必须是相对地址才可以，试过七牛地址，不行
//            './static/a6e0caf7c15b14333070938bec502e52.pdf',
//            './static/GuaranteeDownload20180620.pdf'
        ];

        $body  = "您好，请点击链接打开并下载《喜蜂鸟在职证明》文件。<br>

文件地址：https://dn-mhc.qbox.me/zaizhicaetificate.docx<br>

您好，请点击链接打开并下载《喜蜂鸟签章确认函》文件。<br>

文件地址：https://dn-mhc.qbox.me/platformService/a6e0caf7c15b14333070938bec502e52.pdf<br>

您好，请点击链接打开并下载《喜蜂鸟担保函》文件。<br>\r\n

文件地址：https://dn-mhc.qbox.me/platformService/GuaranteeDownload20180620.pdf";
        $body   = '敬请关注喜蜂鸟平台';
        $result = sendMail($email, $body, '', '喜蜂鸟平台', $attach);

        $userLoan = Db::name('shop_loan')->where(['s_userId' => $this->userId])->order('s_id desc')->field('s_id as id,s_state as state')->find();
        if(!$userLoan){
            $field = 's_state as loanState,si_state as shopState';
            $user  = model('ShopUser')->getUserByIdAll($this->userId, $field);
            !$user && $this->apiReturn(201, '', '用户不存在');

            (is_null($user['shopState']) || $user['shopState'] != 1) && $this->apiReturn(201, '', '店铺认证未认证');

            $data = [
                's_shopId' => $this->orgId,
                's_userId'     => $this->userId,
                's_state'      => 0,
                's_createTime' => time()
            ];

            Db::name('shop_loan')->insert($data);
        }

        !$result && $this->apiReturn(201, '', '发送失败');
        $this->apiReturn(200, ['state' => $userLoan['state'], 'stateName' => $userLoan['state'] == 0 ? '认证中' : ($userLoan['state'] == 1 ? '已通过' : '已拒绝')], '发送成功');
    }

    public function payRecord(){
        (!isset($this->data['id'])  || empty($this->data['id']))  && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['id'] + 0;

        $order = Db::name('shop_loan_apply')->where(['sa_id' => $orderId, 'sa_userId' => $this->userId, 'sa_state' => ['egt', 3]])->count();
        !$order && $this->apiReturn(200);

        $data = model('ShopLoanApply')->getPayRecords($orderId);
        $this->apiReturn(200, $data ?: []);
    }

    public function overdueRecords(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');
        $orderId = $this->data['orderId'] + 0;

        $field = getField('shop_loan_apply_overdue', 'sao_isDelete,sao_operatorId,sao_operatorName', false, '', true);
        $data  = Db::name('shop_loan_apply_overdue')->where(['sao_orderId' => $orderId, 'sao_isDelete' => 0])->field($field)->select();
        if($data){
            foreach($data as $key => &$value){
                $value['createTime'] = $value['createTime'] ? date('Y-m-d H:i:s', $value['createTime']) : '';
                $value['updateTime'] = $value['updateTime'] ? date('Y-m-d H:i:s', $value['updateTime']) : '';
            }
        }
        $this->apiReturn(200, $data);
    }

}