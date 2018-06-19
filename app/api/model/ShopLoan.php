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

class ShopLoan extends Model
{

    protected $table = 'shop_loan';

    public function getLoanById($id, $field = '*', $shopId = ''){
        $where = ['s_id' => $id];
        if($shopId){
            $where['s_shopId'] = $shopId;
        }
        return Db::name($this->table)->field($field)->where($where)->find();
    }


    public function getLoanByUserId($userId, $field = '*', $shopId = ''){
        $where = ['s_userId' => $userId];
        if($shopId){
            $where['s_shopId'] = $shopId;
        }
        return Db::name($this->table)->field($field)->where($where)->find();
    }


    
    

}