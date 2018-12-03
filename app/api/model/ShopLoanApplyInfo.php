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
        $field = getField($this->table, '', false, '', true);
        $data  = Db::name($this->table)->where(['sai_saId' => $saId, 'sai_state' => 0, 'sai_isDel' =>  0])->field($field)->select();
        if($data){
            foreach($data as &$value){
                $value['carImage']  = $value['carImage'] ?: 'http://opii7iyzy.bkt.clouddn.com/1530496843751';
                if(strpos($value['carImage'], 'defult.jpg') !== false){
                    $value['carImage'] = 'http://opii7iyzy.bkt.clouddn.com/1530496843751';
                }
                $value['createTime'] = date('Y-m-d H:i:s', $value['createTime']);
            }
        }
        return $data;
    }

    public function getDataBySaId($saId){
        $field = getField($this->table, '', false, '', true);
        return Db::name($this->table)->where(['sai_saId' => $saId, 'sai_isDel' =>  0])->field($field)->order('sai_carId desc')->select();
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
                $value['fee']        = round($value['fee'], 2);
                $value['carImage']   = $value['carImage'] ?: 'http://opii7iyzy.bkt.clouddn.com/1530496843751';
                if(strpos($value['carImage'], 'defult.jpg') !== false){
                    $value['carImage'] = 'http://opii7iyzy.bkt.clouddn.com/1530496843751';
                }
            }
        }
        return $data ?: [];
    }

}