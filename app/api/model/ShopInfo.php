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

    public function getShopInfoForPage($where, $page = 1, $rows = 10){
        $field = getField($this->table, '', false, '', true);
        $count = Db::name($this->table)->where($where)->count();
        $data  = Db::name($this->table)->where($where)->field($field)->page($page, $rows)->order('si_id desc,si_state asc')->select();
        $type  = ['1' => '4S店', '2' => '资源公司', '3' => '汽贸公司'];
        $state = ['认证中', '已通过', '已拒绝'];
        foreach($data as $key => &$value){
            $value['createTime'] = date('Y-m-d H:i:s', $value['createTime']);
            $value['type']       = $type[$value['type']];
            $value['stateName']  = $state[$value['state']];
        }

        return ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows];
    }

    public function findOne($where){
        if(!$where){
            return false;
        }
        $field = getField($this->table, 'si_operatorId,updateTime', false, '', true);
        $data = Db::name($this->table)->field($field)->where($where)->find();
        if(!$data){
            return false;
        }
        $data['idCard'] = explode(',', $data['idCard']);
        $data['license'] = explode(',', $data['license']);
        $data['image']   = explode(',', $data['image']);
        $state = ['认证中', '已通过', '已拒绝'];
        $data['stateName']  = $state[$data['state']];
        $data['reason']     = $data['reason'] ?: '';
        return $data;
    }

    public function getShopInfoByOrgId($orgId, $field = '*'){
        $where = ['si_shopId' => $orgId];
        return Db::name($this->table)->where($where)->field($field)->find();
    }


    
    

}