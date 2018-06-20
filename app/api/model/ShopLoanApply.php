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
                $shopLoanApply = ShopLoanApply::get($value['id']);
                $shopLoanApply = $shopLoanApply->ShopLoanApplyInfo()->select();
                for($i=0;$i<count($shopLoanApply);$i++){
                    $value['info'][] = $shopLoanApply[$i]->toArray();
                }
            }
        }
        return ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows];
    }

    public function getShopLoanApplyByIdAll($id){
        $field = 'sa_id as id,sa_orderId as orderId,sa_state as state,sa_userId as userId,sa_userName as userName,sa_phone as phone,sa_orgId as orgId,sa_orgName as orgName,
                 sa_amount as amount,sa_totalAmount as totalAmount,sa_feeTotal as feeTotal,sa_rate as rate,sa_fee as fee,sa_period as period,sa_image as image,sa_annualIncome as annualIncome,sa_incomeImage as incomeImage,
                 sa_idCardOn as idCardOn,sa_idCardOff as idCardOff,sa_operatorName as operatorName,sa_reason as reason,sa_voucher as voucher,sa_createTime as createTime';
        $where = ['sa_id' => $id, 'sa_type' => 1, 'sa_isDel' => 0];
        $data  = Db::name($this->table)->where($where)->field($field)->find();
        if($data){
            $data['createTime']  = date('Y-m-d H:i:s', $data['createTime']);
            $data['unpayAmount'] = $data['amount'];
            $shopLoanApply = ShopLoanApply::get($data['id']);
            $shopLoanApply = $shopLoanApply->ShopLoanApplyInfo()->select();
            for($i=0;$i<count($shopLoanApply);$i++){
                $data['list'][] = $shopLoanApply[$i]->toArray();
            }
        }
        return $data;
    }

    public function ShopLoanApplyInfo(){
        $field = 'sai_id as id,sai_orderId as orderId,sai_carId as carId,sai_carName as carName,sai_colorId as colorId,sai_colorName as colorName,
                  sai_guidancePrice as guidancePrice,sai_price as price,sai_downPayments as downPayments,sai_amount as amount,sai_number as number,
                  sai_state as state,sai_voucher as voucher,sai_createTime as createTime';
        return $this->hasMany('shop_loan_apply_info', 'sai_orderId', 'sa_orderId')->field($field);
    }
    
    

}