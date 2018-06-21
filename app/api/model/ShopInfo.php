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

class ShopInfo extends Model
{

    protected $table = 'shop_info';

    public function getShopInfoById($id, $field = '*', $shopId = ''){
        $where = ['si_id' => $id];
        if($shopId){
            $where['si_shopId'] = $shopId;
        }
        return Db::name($this->table)->field($field)->where($where)->find();
    }


    public function getShopInfoByUserId($userId, $field = '*', $shopId = ''){
        $where = ['si_userId' => $userId];
        if($shopId){
            $where['si_shopId'] = $shopId;
        }
        
        return Db::name($this->table)->field($field)->where($where)->find();
    }


    
    

}