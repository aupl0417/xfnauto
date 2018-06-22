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

class ShopLoanApplyInfo extends Model
{

    protected $table = 'shop_loan_apply_info';

    
    public function getCountById($id){
        $where = [
            'sai_isDel'   => 0,
            'sai_orderId' => $id
        ];
        return Db::name($this->table)->where($where)->count();
    }

    public function getUnpaidDataBySaId($saId){
        $field = 'sai_id as id,sai_orderId as orderId,sai_carId as carId,sai_carName as carName,sai_colorId as colorId,sai_colorName as colorName,
                  sai_guidancePrice as guidancePrice,sai_price as price,sai_downPayments as downPayments,sai_amount as amount,sai_number as number,
                  sai_state as state,sai_voucher as voucher,sai_createTime as createTime,sai_fee as fee,sai_carImage as carImage';
        $field = getField($this->table, '', false, '', true);
        return Db::name($this->table)->where(['sai_saId' => $saId, 'sai_state' => 0, 'sai_isDel' =>  0])->field($field)->select();
    }

    public function getDataBySaId($saId){
        $field = 'sai_id as id,sai_orderId as orderId,sai_carId as carId,sai_carName as carName,sai_colorId as colorId,sai_colorName as colorName,
                  sai_guidancePrice as guidancePrice,sai_price as price,sai_downPayments as downPayments,sai_amount as amount,sai_number as number,
                  sai_state as state,sai_voucher as voucher,sai_createTime as createTime,sai_fee as fee,sai_carImage as carImage';
        $field = getField($this->table, '', false, '', true);
        return Db::name($this->table)->where(['sai_saId' => $saId, 'sai_isDel' =>  0])->field($field)->select();
    }

    public function getDataByIds($ids){
        if(!$ids){
            return false;
        }
        $field = getField($this->table, 'sai_isDel,sai_voucher', false, '', true);
        $data = Db::name($this->table)->where(['sai_id' => ['in', $ids], 'sai_isDel' =>  0])->field($field)->select();
        if($data){
            foreach($data as &$value){
                $value['createTime'] = date('Y-m-d H:i:s', $value['createTime']);
            }
        }
        return $data ?: [];
    }

}