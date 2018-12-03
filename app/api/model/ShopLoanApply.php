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

class ShopLoanApply extends Model
{

    protected $table = 'shop_loan_apply';
    protected $state = [
        '-1' => '取消',
        '0' => '审核中',
        '1' => '已拒绝',
        '2' => '待放款',
        '3' => '已放款',
        '4' => '请还款',
        '5' => '已逾期',
        '6' => '移交处理',
        '7' => '已还清'
    ];


    public function getUserLoanApplyByIdAll($id, $field = '*'){
        $where = [
            'sa_id' => $id,
            'sa_state' => ['neq', -1]
        ];

        $join = [
            ['shop_loan_apply_info', 'si_userId=shop_user_id', 'left'],
            ['shop_loan', 's_userId=shop_user_id', 'left'],
        ];

        return Db::name('shop_user')->where($where)->field($field)->join($join)->find();
    }

    public function getDataByPage($where, $page = 1, $rows = 10){
        $ignoreFields = 'sa_isDel,sa_payVoucher,sa_payVoucherTime,sa_operatorId,sa_operatorName,sa_orgCode,sa_voucherPersonId,sa_voucherPersonName';
        $field = getField($this->table, $ignoreFields, false, '', true);
        $count = Db::name($this->table)->where($where)->count();
        $data  = Db::name($this->table)->where($where)->field($field)->page($page, $rows)->order('sa_id desc')->select();
        if($data){
            foreach($data as $key => &$value){
                $value['stateName'] = $this->state[$value['state']];
                $overDueDayCount = Db::name('shop_loan_apply_overdue')->where(['sao_orderId' => $value['id'], 'sao_state' => 1])->sum('sao_period');
                $days = ceil((strtotime(date('Y-m-d', $value['voucherTime'])) + ($value['period'] + $overDueDayCount) * 24 * 3600 - strtotime(date('Y-m-d', time()))) / 24 / 3600);
                $value['days']        = $value['voucherTime'] ? ($value['state'] != 5 ? ($days > 0 ? $days: 0) : 0) : 0;
                $value['overdueDays'] = $overDueDayCount;
                $value['period']      += $overDueDayCount;
                $value['voucherTime'] = $value['voucherTime'] ? date('Y-m-d H:i:s', $value['voucherTime']) : '';
                $shopLoanApply        = ShopLoanApply::get($value['id']);
                $shopLoanApply        = $shopLoanApply->ShopLoanApplyInfo()->select();
                $value['unpayAmount'] = $value['amount'];//待还本金总额
                $value['unpayFee']    = $value['feeTotal'];//待还手续费总额
                $value['unpayAmountTotal']    = $value['amount'] + $value['feeTotal'];//待还总额（待还本金总额 + 待还手续费总额）
                $value['createTime']  = date('Y-m-d', $value['createTime']);
                $value['updateTime']  = $value['updateTime'] ? date('Y-m-d H:i:s', $value['updateTime']) : '';
                $value['image']       = $value['image'] ?: '';
                $value['reason']      = $value['reason'] ?: '';
                $value['voucher']     = $value['voucher'] ?: '';
                $cars = [];
                $value['info'] = [];
                $counts = [];
                $paidFee = 0;
                $paidAmount = 0;
                for($i = 0; $i < count($shopLoanApply); $i++){
                    $info = $shopLoanApply[$i]->toArray();
                    $info['carImage'] = $info['carImage'] ?: 'http://opii7iyzy.bkt.clouddn.com/1530496843751';
                    if(strpos($info['carImage'], 'defult.jpg') !== false){
                        $info['carImage'] = 'http://opii7iyzy.bkt.clouddn.com/1530496843751';
                    }
                    if($info['state'] == 1){
                        $paidAmount += $info['amount'];
                        $paidFee    += $info['fee'];
                    }

                    $infoString = md5($info['carId'] . $info['colorId']);
                    if(!in_array($infoString, $cars)){
                        $cars[] = $infoString;
                        $value['info'][$infoString] = $info;
                        $counts[$infoString] = 1;
                    }else{
                        $counts[$infoString] ++;
                    }
                }

                $value['unpayAmount'] = round($value['amount'] - $paidAmount, 2);
                $value['unpayFee']    = round($value['feeTotal'] - $paidFee, 2);
                $value['unpayAmountTotal']    = $value['unpayAmount'] + $value['unpayFee'];
                foreach($value['info'] as $k => &$val){
                    $val['number'] = $counts[$k];
                    $val['fee']    = $val['fee'] * $val['number'];
                    $val['amount'] = $val['amount'] * $val['number'];
                }

                unset($counts, $cars);
                $value['info'] = array_values($value['info']);
            }
        }
        return ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows];
    }

    public function getShopLoanApplyByIdAll($id){
        $field = 'sa_id as id,sa_orderId as orderId,sa_state as state,sa_userId as userId,sa_userName as userName,sa_phone as phone,sa_orgId as orgId,sa_orgName as orgName,
                 sa_amount as amount,sa_totalAmount as totalAmount,sa_feeTotal as feeTotal,sa_rate as rate,sa_fee as fee,sa_period as period,sa_image as image,
                 sa_operatorName as operatorName,sa_reason as reason,sa_voucher as voucher,sa_voucherPersonName as voucherPerson,sa_voucherTime as voucherTime,sa_createTime as createTime,sa_updateTime as updateTime,s_materials as materials';
        $where = ['sa_id' => $id, 'sa_type' => 1, 'sa_isDel' => 0, 's_state' => 1];
        $data  = Db::name($this->table)->where($where)->field($field)->join('shop_loan', 's_userId=sa_userId', 'left')->find();
        if($data){
            $createDate   = date('Y-m-d', $data['createTime']);
            $data['createTime']  = $createDate;
            $data['updateTime']  = $data['updateTime']  ? date('Y-m-d H:i:s', $data['updateTime']) : '';
            $data['unpayAmount'] = $data['amount'];//待还本金
            $data['unpayFee']    = $data['feeTotal'];//待还手续费
            $data['stateName']   = $this->state[$data['state']];
            $data['voucherRate'] = $data['rate'];
            $data['materials']   = $data['materials'] ? explode(',', $data['materials']) : [];
            $data['voucherPeriod'] = $data['period'];
            $data['payRecords']  = Db::name('shop_loan_pay_records')->where(['spr_orderId' => $id])->field('spr_id as id,spr_orderId as orderId,spr_infoIds as infoIds,spr_voucher as voucher,spr_createTime as createTime')->select();
            if($data['payRecords']){
                foreach($data['payRecords'] as &$record){
                    $record['createTime'] = date('Y-m-d H:i:s', $record['createTime']);
                }
            }
            $data['list']        = model('ShopLoanApplyInfo')->getDataBySaId($data['id']);
            if($data['list']){
                $paidFee    = 0;
                $paidAmount = 0;
                foreach($data['list'] as &$value){
                    if($value['state'] == 1){
                        $paidAmount += $value['amount'];
                        $paidFee    += $value['fee'];
                    }
                    $value['fee']       = round($value['fee'], 2);
                    $value['carImage']  = $value['carImage'] ?: 'http://opii7iyzy.bkt.clouddn.com/1530496843751';
                    if(strpos($value['carImage'], 'defult.jpg') !== false){
                        $value['carImage'] = 'http://opii7iyzy.bkt.clouddn.com/1530496843751';
                    }
                    $value['stateName'] = $value['state'] == 0 ? '待还款' : ($value['state'] == 1 ? '已还清' : '已移交处理');
                }
                $data['unpayAmount'] = round($data['amount'] - $paidAmount, 2);
                $data['unpayFee']    = round($data['feeTotal'] - $paidFee, 2);
            }
            $data['unpayAmount'] = $data['state'] != 7 ? $data['unpayAmount'] : 0;//待还本金
            $data['unpayFee']    = $data['state'] != 7 ? $data['unpayFee'] : 0;//待还手续费
            $overDueField    = 'sao_id as id,sao_downpayment as downpayment,sao_downpaymentFee as downpaymentFee,sao_rate as rate,sao_fee as fee,sao_period as period,sao_voucher as voucher,sao_createTime as createTime,sao_state as state,sao_updateTime as updateTime,sao_beginTime as beginTime';
            $data['overDue'] = Db::name('shop_loan_apply_overdue')->where(['sao_orderId' => $id])->field($overDueField)->select();
            $overDueDayCount = 0;
            $data['currentOverDueRate'] = 0;
            if($data['overDue']){
                $nextRate = $data['voucherRate'];
                $voucherDate  = date('Y-m-d', $data['voucherTime']);
                $loanDeadLine = strtotime($voucherDate) + $data['period'] * 24 * 3600;//垫资截止日期
                $nextTime     = $loanDeadLine;
                foreach($data['overDue'] as &$due){
                    $beginTime    = $due['beginTime'];//延期开始时间
                    $deadLine     = $beginTime + $due['period'] * 24 * 3600;//延期截止时间
                    $overDueDays  = ceil((time() - strtotime(date('Y-m-d', $due['beginTime']))) / 24 / 3600);
                    $overDueDays = $due['state'] == 1 ? ($overDueDays > 0 ? $overDueDays : 0) : 0;
                    $due['createTime'] = $due['createTime'] ? date('Y-m-d H:i:s', $due['createTime']) : '';
                    $due['updateTime'] = $due['updateTime'] ? date('Y-m-d H:i:s', $due['updateTime']) : '';
                    $due['totalFee']   = ceil($data['amount'] * $due['rate'] * $overDueDays) / 100;
                    $due['stateName']  = $due['state'] == 0 ? '未付款' : '已付款';
                    if($due['state'] == 1){
                        $overDueDayCount += $due['period'];
                    }
                    if(time() > $beginTime && time() < $deadLine){
                        $due['isActive'] = '已生效';
                        $data['rate']    = $due['rate'];
                    }elseif(time() > $nextTime && time() < $beginTime){//如果是处于两个延期之间，则用前一个延期的费率
                        $data['rate']    = $nextRate;
                    }
                    $nextRate = $due['rate'];
                    $nextTime = $deadLine;
                }

                if(time() > $deadLine){//延期过期后，当前费率仍为最后一次延期的费率
                    $data['rate']    = $due['rate'];
                }
            }
            $data['period']      += $overDueDayCount;
            $data['deadline']    = $data['voucherTime'] ? date('Y-m-d', strtotime(date('Y-m-d', $data['voucherTime'])) + ($data['period'] - 1) * 24 * 3600) : '';
            $data['days']        = $data['voucherTime'] ? ($data['state'] != 5 ? ceil((strtotime(date('Y-m-d', $data['voucherTime'])) + ($data['period']) * 24 * 3600 - strtotime(date('Y-m-d', time()))) / 24 / 3600) : 0) : 0;
            $data['voucherTime'] = $data['voucherTime'] ? date('Y-m-d H:i:s', $data['voucherTime']) : '';
        }

        return $data;
    }

    public function ShopLoanApplyInfo(){
        $field = 'sai_id as id,sai_orderId as orderId,sai_carId as carId,sai_carName as carName,sai_colorId as colorId,sai_colorName as colorName,
                  sai_guidancePrice as guidancePrice,sai_price as price,sai_downPayments as downPayments,sai_amount as amount,sai_number as number,
                  sai_state as state,sai_voucher as voucher,sai_createTime as createTime,sai_fee as fee,sai_carImage as carImage';
        return $this->hasMany('shop_loan_apply_info', 'sai_orderId', 'sa_orderId')->field($field);
    }

    public function getById($id, $field = '*'){
        return Db::name($this->table)->where(['sa_id' => $id, 'sa_isDel' => 0, 'sa_state' => ['neq', -1]])->field($field)->find();
    }

    public function getPayRecords($orderId){
        if(!$orderId || !is_numeric($orderId)){
            return false;
        }
        $data = Db::name('shop_loan_pay_records')->where(['spr_orderId' => $orderId])->join('shop_loan_apply', 'sa_id=spr_orderId', 'left')->field('spr_id as id,spr_orderId as orderId,spr_amount as amount,spr_infoIds as infoIds,spr_period as period,spr_voucher as voucher,spr_createTime as createTime,sa_voucherTime as voucherTime')->select();
        if($data){
            foreach($data as &$record){
                $record['loanDays'] = ceil((strtotime(date('Y-m-d, 23:59:59', $record['createTime'])) - strtotime(date('Y-m-d', $record['voucherTime']))) / 24 / 3600);
                $record['createTime'] = date('Y-m-d H:i:s', $record['createTime']);
                $record['cars'] = model('ShopLoanApplyInfo')->getDataByIds($record['infoIds']);
            }
        }
        return $data;
    }

    public function getDataForNonePage($where){
        $ignoreFields = 'sa_isDel,sa_payVoucher,sa_payVoucherTime,sa_operatorId,sa_operatorName,sa_orgCode,sa_voucherPersonId,sa_voucherPersonName';
        $field = getField($this->table, $ignoreFields, false, '', true);
        $data  = Db::name($this->table)->where($where)->field($field)->order('sa_id desc')->select();
        if($data){
            foreach($data as $key => &$value){
                $value['stateName'] = $this->state[$value['state']];
                $shopLoanApply = ShopLoanApply::get($value['id']);
                $shopLoanApply = $shopLoanApply->ShopLoanApplyInfo()->select();
                $value['unpayAmount'] = $value['amount'];//待还本金
                $value['unpayFee']    = $value['feeTotal'];//待还手续费
                $value['createTime']  = date('Y-m-d', $value['createTime']);
                $value['updateTime']  = $value['updateTime'] ? date('Y-m-d H:i:s', $value['updateTime']) : '';
                $value['image']       = $value['image'] ?: '';
                $value['reason']      = $value['reason'] ?: '';
                $value['voucher']     = $value['voucher'] ?: '';
                $cars = [];
                $value['info'] = [];
                $counts = [];
                for($i = 0; $i < count($shopLoanApply); $i++){
                    $info = $shopLoanApply[$i]->toArray();
                    if($info['state'] == 1){
                        $value['unpayAmount'] = $value['amount'] - $info['amount'];
                        $value['unpayFee']    = $value['feeTotal'] - $info['fee'];
                    }
                    $infoString = md5($info['carId'] . $info['colorId']);
                    if(!in_array($infoString, $cars)){
                        $cars[] = $infoString;
                        $value['info'][$infoString] = $info;
                        $counts[$infoString] = 1;
                    }else{
                        $counts[$infoString] ++;
                    }
                }

                foreach($value['info'] as $k => &$val){
                    $val['number'] = $counts[$k];
                }

                unset($counts, $cars);
                $value['info'] = array_values($value['info']);
            }
        }
        return $data;
    }
    
    

}