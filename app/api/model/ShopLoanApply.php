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
        '0' => '待审核',
        '1' => '拒绝',
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
        ];

        $join = [
            ['shop_loan_apply_info', 'si_userId=shop_user_id', 'left'],
            ['shop_loan', 's_userId=shop_user_id', 'left'],
        ];

        return Db::name('shop_user')->where($where)->field($field)->join($join)->find();
    }

    public function getDataByPage($where, $page = 1, $rows = 10){
        $field = 'sa_id as id,sa_orderId as orderId,sa_state as state,sa_userName as userName,sa_phone as phone,sa_orgId as orgId,sa_orgName as orgName,
                 sa_amount as amount,sa_rate as rate,sa_fee as fee,sa_period as period,sa_image as image,sa_annualIncome as annualIncome,sa_incomeImage as incomeImage,
                 sa_idCardOn as idCardOn,sa_idCardOff as idCardOff,sa_operatorName as operatorName,sa_reason as reason,sa_voucher as voucher,sa_createTime as createTime';
        $count = Db::name($this->table)->where($where)->count();
        $data  = Db::name($this->table)->where($where)->field($field)->page($page, $rows)->order('sa_id desc')->select();
        if($data){
            foreach($data as $key => &$value){
                $value['stateName'] = $this->state[$value['state']];
                $shopLoanApply = ShopLoanApply::get($value['id']);
                $shopLoanApply = $shopLoanApply->ShopLoanApplyInfo()->select();
                for($i = 0; $i < count($shopLoanApply); $i++){
                    $value['info'][] = $shopLoanApply[$i]->toArray();
                }
            }
        }
        return ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows];
    }

    public function getShopLoanApplyByIdAll($id){
        $field = 'sa_id as id,sa_orderId as orderId,sa_state as state,sa_userId as userId,sa_userName as userName,sa_phone as phone,sa_orgId as orgId,sa_orgName as orgName,
                 sa_amount as amount,sa_totalAmount as totalAmount,sa_feeTotal as feeTotal,sa_rate as rate,sa_fee as fee,sa_period as period,sa_image as image,sa_annualIncome as annualIncome,sa_incomeImage as incomeImage,
                 sa_idCardOn as idCardOn,sa_idCardOff as idCardOff,sa_operatorName as operatorName,sa_reason as reason,sa_voucher as voucher,sa_voucherPersonName as voucherPerson,sa_voucherTime as voucherTime,sa_createTime as createTime,sa_updateTime as updateTime';
        $where = ['sa_id' => $id, 'sa_type' => 1, 'sa_isDel' => 0];
        $data  = Db::name($this->table)->where($where)->field($field)->find();
        if($data){
            $createDate   = date('Y-m-d', $data['createTime']);
            $days         = (time() - strtotime($createDate)) / 3600 / 24;
            $data['days'] = $data['state'] == 4 ? $data['period'] - ceil($days) : '';
            $data['deadline']    = $data['state'] == 3 ? date('Y-m-d', $data['updateTime'] + $data['period'] * 3600 * 24) : '';
            $data['createTime']  = $createDate;
            $data['updateTime']  = $data['updateTime']  ? date('Y-m-d H:i:s', $data['updateTime']) : '';
            $data['voucherTime'] = $data['voucherTime'] ? date('Y-m-d H:i:s', $data['voucherTime']) : '';
            $data['unpayAmount'] = $data['amount'];//待还本金
            $data['unpayFee']    = $data['feeTotal'];//待还手续费
            $data['stateName']   = $this->state[$data['state']];
            $shopLoanApply = ShopLoanApply::get($data['id']);
            $shopLoanApply = $shopLoanApply->ShopLoanApplyInfo()->select();
            for($i=0; $i < count($shopLoanApply); $i++){
                $value = $shopLoanApply[$i]->toArray();
                if($value['state'] == 1){
                    $data['unpayAmount'] = $data['amount'] - $value['amount'];
                    $data['unpayFee']    = $data['feeTotal'] - $value['fee'];
                }
                $value['stateName'] = $value['state'] == 0 ? '待还款' : ($value['state'] == 1 ? '已还清' : '移交处理');
                $data['list'][] = $value;
            }
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
        return Db::name($this->table)->where(['sa_id' => $id, 'sa_isDel' => 0])->field($field)->find();
    }
    
    

}