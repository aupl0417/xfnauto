<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-05-31
 * Time: 11:46
 */

namespace app\api\controller\v3\Backend;

use think\Controller;
use think\Db;
use think\Exception;

class ShopLoan extends Admin
{

    /**
     * 垫资列表
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = ['sa_isDel' => 0];

        if(isset($this->data['state']) && $this->data['state'] != ''){
            $state = $this->data['state'] + 0;
            $where['sa_state'] = $state;
        }

        if(isset($this->data['orgName']) && !empty($this->data['orgName'])){
            $orgName = htmlspecialchars(trim($this->data['orgName']));
            $where['sa_orgName'] = ['like', '%' . $orgName . '%'];
        }

        if(isset($this->data['orderId']) && !empty($this->data['orderId'])){
            $orderId = htmlspecialchars(trim($this->data['orderId']));
            $where['sa_orderId'] = ['like', '%' . $orderId . '%'];
        }

        $data = model('ShopLoanApply')->getDataByPage($where, $page, $rows);
        $this->apiReturn(200, $data);
    }

    /**
     * 审核详情
     * */
    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        $data = model('ShopLoanApply')->getShopLoanApplyByIdAll($id);
        if($data){
            $field = $this->getField('shop_info', '', false, '', true);
            $data['info'] = model('ShopInfo')->getShopInfoByUserId($data['userId'], $field);
        }

        $this->apiReturn(200, $data);
    }

    /**
     * 审核
     * */
    public function verify(){
        (!isset($this->data['id'])    || empty($this->data['id']))    && $this->apiReturn(201, '', '参数非法');
        (!isset($this->data['state']) || empty($this->data['state'])) && $this->apiReturn(201, '', '状态参数非法');

        $id     = $this->data['id'] + 0;
        $state  = $this->data['state'] + 0;
        $reason = '';
        if($state == 1){
            (!isset($this->data['reason']) || empty($this->data['reason'])) && $this->apiReturn(201, '', '请输入拒绝原因');

            $reason = htmlspecialchars(trim($this->data['reason']));
        }

        $info = model('ShopLoanApply')->getById($id, 'sa_state as state');
        !$info && $this->apiReturn(201, '', '数据不存在');
        $info['state'] != 0 && $this->apiReturn(201, '', '订单已处理');

        $data = [
            'sa_state'  => $state,
            'sa_reason' => $reason,
            'sa_operatorId' => $this->userId,
            'sa_operatorName' => $this->user['realName'],
            'sa_updateTime'   => time()
        ];

        $result = Db::name('shop_loan_apply')->where(['sa_id' => $id])->update($data);
        $result === false && $this->apiReturn(201, '', '操作失败');
        $this->apiReturn(200, '', '操作成功');
    }

    /**
     * 设置手续费率
     * */
    public function setRate(){
        $cacheKey = md5('set_loan_rate');
        (!isset($this->data['rate']) || empty($this->data['rate'])) && $this->apiReturn(201, '', '费率不能为空');

        !is_numeric($this->data['rate']) && $this->apiReturn(201, '', '参数非法');

        $rate = floatval($this->data['rate']);
        cache($cacheKey, $rate);
        $this->apiReturn(200, ['rate' => cache($cacheKey)]);
    }

    /**
     * 上传放款凭证
     * */
    public function loanVoucher(){
        (!isset($this->data['id'])    || empty($this->data['id']))    && $this->apiReturn(201, '', '参数非法');
        (!isset($this->data['voucher'])    || empty($this->data['voucher']))    && $this->apiReturn(201, '', '请上传放款凭证');

        $id      = $this->data['id'] + 0;
        $voucher = htmlspecialchars(trim($this->data['voucher']));
        !filter_var($voucher, FILTER_VALIDATE_URL) && $this->apiReturn(201, '', '放款凭证地址非法');

        $loanInfo  = model('ShopLoanApply')->getById($id, 'sa_state as state');
        !$loanInfo && $this->apiReturn(201, '', '该垫资数据不存在');
        $loanInfo['state'] != 2 && $this->apiReturn(201, '', '非待放款状态不能上传');

        $data = [
            'sa_state'             => 3,
            'sa_voucher'           => $voucher,
            'sa_voucherPersonId'   => $this->userId,
            'sa_voucherPersonName' => $this->user['realName'],
            'sa_voucherTime'       => time()
        ];

        $result = Db::name('shop_loan_apply')->where(['sa_id' => $id])->update($data);
        $result === false && $this->apiReturn(201, '', '上传放款凭证失败');
        $this->apiReturn(200, '', '上传放款凭证成功');
    }

    /**
     * 还款
     * */
    public function payVoucher(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');
        (!isset($this->data['voucher'])  || empty($this->data['voucher']))  && $this->apiReturn(201, '', '请上传还款凭证');
        (!isset($this->data['infoIds'])  || empty($this->data['infoIds']))  && $this->apiReturn(201, '', '请选择还款车辆');

        $orderId  = $this->data['orderId'] + 0;
        $voucher  = htmlspecialchars(trim($this->data['voucher']));
        $vouchers = explode(',', $voucher);
        foreach($vouchers as $value){
            !filter_var($value, FILTER_VALIDATE_URL) && $this->apiReturn(201, '', '还款凭证地址非法');
        }

        $applyInfo = model('ShopLoanApply')->getById($orderId, 'sa_id as id,sa_state as state,sa_orderId as orderId,sa_voucherTime as voucherTime,sa_period as period,sa_rate as rate');
        !$applyInfo && $this->apiReturn(201, '', '数据不存在');
        !in_array(intval($applyInfo['state']), [3, 4]) && $this->apiReturn(201, '', '未到还款状态');

        $infoIds       = htmlspecialchars(trim($this->data['infoIds']));
        $infoIds       = explode(',', $infoIds);
        $loanApplyInfo = Db::name('shop_loan_apply_info')->where(['sai_id' => ['in', $infoIds], 'sai_isDel' => 0, 'sai_state' => 0])->field('sai_id,sai_amount as amount,sai_downPayments as downPayments,sai_fee as fee')->select();
        try{

            Db::startTrans();
            $result = Db::name('shop_loan_apply_info')->where(['sai_id' => ['in', $infoIds], 'sai_isDel' => 0, 'sai_state' => 0])->update(['sai_state' => 1]);
            if($result === false){
                throw new Exception('更新车型垫资状态失败');
            }

            $amount   = 0;
            if($loanApplyInfo){
                foreach($loanApplyInfo as $info){
                    $amount += $info['amount'] + $info['fee'];
                }
            }
            $records = [
                'spr_orderId' => $orderId,
                'spr_infoIds' => implode(',', $infoIds),
                'spr_period'  => ceil((time() - $applyInfo['voucherTime']) / 24 / 3600),
                'spr_amount'  => $amount,
                'spr_voucher' => $voucher,
                'spr_createTime' => time()
            ];

            $result = Db::name('shop_loan_pay_records')->insert($records);
            if(!$result){
                throw new Exception('插入还款记录表失败');
            }

            $applyInfoCount = model('ShopLoanApplyInfo')->getCountById($applyInfo['orderId']);
            $data = [
                'sa_state'  => count($infoIds) == $applyInfoCount ? 7 : 4
            ];
            
            $result = Db::name('shop_loan_apply')->where(['sa_id' => $orderId])->update($data);
            if($result === false){
                throw new Exception('更新垫资表状态失败');
            }
            Db::commit();
            $this->apiReturn(200, '', '操作成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '操作失败');
        }
    }

    /**
     * 待还款列表
     * */
    public function unpayList(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['orderId'] + 0;

        $applyInfo = model('ShopLoanApply')->getById($orderId, 'sa_id as id,sa_state as state');
        !$applyInfo && $this->apiReturn(201, '', '数据不存在');
        $state = ['0' => '待审核', '1' => '拒绝',  '2 ' => '待放款',  '3' => '已放款',  '4' => '请还款',  '5' => '已逾期',  '6' => '移交处理', '7' => '已还清'];
        !in_array(intval($applyInfo['state']), [3, 4]) && $this->apiReturn(201, '', '该订单状态为' . $state[$applyInfo['state']]);

        $data    = model('ShopLoanApplyInfo')->getUnpaidDataBySaId($orderId);
        $this->apiReturn(200, $data);
    }

    /**
     * 延期操作
     * */
    public function overdue(){
        if(isset($this->data['id']) && !empty($this->data['id'])){
            $overDueId = $this->data['id'] + 0;
            $result = $this->validate($this->data, 'OverDue');
        }else{
            $result = $this->validate($this->data, 'OverDue.add');
        }

        $result !== true && $this->apiReturn(201, '', $result);

        $orderId = $this->data['orderId'] + 0;
        $applyInfo = model('ShopLoanApply')->getById($orderId, 'sa_id as id,sa_state as state,sa_amount as amount');
        !$applyInfo && $this->apiReturn(201, '', '数据不存在');
        !in_array(intval($applyInfo['state']), [4, 5], true) && $this->apiReturn(201, '', '只有在请还款和已逾期状态才能延期');

        $unpayTotal = Db::name('shop_loan_apply_info')->where(['sai_saId' => $orderId, 'sai_isDel' => 0, 'sai_state' => 0])->sum('sai_amount');

        $data = [
            'sao_orderId'        => $orderId,
            'sao_downpayment'    => floatval($this->data['downpayment']),
            'sao_downpaymentFee' => $unpayTotal * floatval($this->data['downpayment']) / 100,
            'sao_rate'           => floatval($this->data['rate']),
            'sao_period'         => floatval($this->data['period']),
            'sao_fee'            => ceil($applyInfo['amount'] * floatval($this->data['rate'])) / 100,
            'sao_state'          => 0,
            'sao_updateTime'     => time(),
            'sao_operatorId'     => $this->userId,
            'sao_operatorName'   => $this->user['realName']
        ];

        if(isset($overDueId)){
            $data['sao_voucher']     = $this->data['voucher'];
            $data['sao_updateTime']  = time();
            $data['sao_state']       = 1;
            $result = Db::name('shop_loan_apply_overdue')->where(['sao_id' => $overDueId, 'sao_orderId' => $orderId])->update($data);
            $result === false && $this->apiReturn(201, '', '延期失败');
        }else{
            if(Db::name('shop_loan_apply_overdue')->where(['sao_orderId' => $orderId])->count()){
                $this->apiReturn(201, '', '已添加过延期，不能重复添加');
            }
            $data['sao_createTime'] = time();
            $data['sao_state']      = 0;
            $result = Db::name('shop_loan_apply_overdue')->insert($data);
            !$result && $this->apiReturn(201, '', '保存成功');
        }
        $this->apiReturn(200, '', '操作成功');
    }

    /**
     * 延期详情
     * */
    public function overdueDetail(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['orderId'] + 0;
        $field   = 'sao_id as id,sao_orderId as orderId,sao_downpayment as downpayment,sao_downpaymentFee as downpaymentFee,sao_rate as rate,sao_period as period,sao_voucher as voucher';
        $data    = Db::name('shop_loan_apply_overdue')->where(['sao_orderId' => $orderId])->field($field)->find();
        $this->apiReturn(200, $data);
    }

    public function payRecord(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['orderId'] + 0;
        $data  = Db::name('shop_loan_pay_records')->where(['spr_orderId' => $orderId])->field('spr_id as id,spr_orderId as orderId,spr_infoIds as infoIds,spr_period as period,spr_voucher as voucher,spr_createTime as createTime')->select();
        if($data){
            foreach($data as &$record){
                $record['cars'] = model('ShopLoanApplyInfo')->getDataByIds($record['infoIds']);
                $record['createTime'] = date('Y-m-d H:i:s', $record['createTime']);
            }
        }
        $this->apiReturn(200, $data);
    }




}