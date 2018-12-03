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

    public function getLoanByShopId($shopId, $field = '*'){
        $where = ['s_shopId' => $shopId];
        return Db::name($this->table)->where($where)->field($field)->find();
    }


    public function getLoanByUserId($userId, $field = '*', $shopId = ''){
        $where = ['s_userId' => $userId];
        if($shopId){
            $where['s_shopId'] = $shopId;
        }
        return Db::name($this->table)->field($field)->where($where)->find();
    }

    public function getShopLoanListForPage($where, $page = 1, $rows = 10){
        $field = getField($this->table, 's_system_user_name,s_system_user_id', false, $alias = '', true) . ',si_shopName as shopName,si_type as type,phone_number as phone';
        $join  = [
            ['shop_info', 'si_id=s_shopId', 'left'],
            ['shop_user', 's_userId=shop_user_id', 'left'],
        ];
        $count = Db::name($this->table)->where($where)->join($join)->count();
        $data  = Db::name($this->table)->where($where)->join($join)->field($field)->order('s_id desc,s_state asc')->select();
        if($data){
            $type  = ['1' => '4S店', '2' => '资源公司', '3' => '汽贸公司'];
            foreach($data as $key => &$value){
                $value['createTime'] = $value['createTime'] ? date('Y-m-d H:i:s', $value['createTime']) : '';
                $value['updateTime'] = $value['updateTime'] ? date('Y-m-d H:i:s', $value['updateTime']) : '';
                $value['stateName']  = $value['state'] == 0 ? '认证中' : ($value['state'] == 1 ? '已通过' : '已拒绝');
                $value['materials']  = $value['materials'] ? explode(',', $value['materials']) : [];
                $value['type']       = $type[$value['type']];
            }
        }
        return ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows];
    }

    public function getShopLoanByIdAll($id){
        $field  = getField($this->table, 's_system_user_name,s_system_user_id', false, $alias = '', true);
        $field .= ',si_shopName as shopName,si_type as type,si_address as address,si_describes as describes,si_corporation as corporation,si_phone as phone,si_idCard as idCard,si_license as license,si_image as image';
        $where = ['s_id' => $id, 'si_state' => 1];
        $data  = Db::name($this->table)->where($where)->join('shop_info', 'si_userId=s_userId', 'left')->field($field)->find();
        if(!$data){
            return false;
        }
        $type  = ['1' => '4S店', '2' => '资源公司', '3' => '汽贸公司'];
        $data['createTime'] = date('Y-m-d', $data['createTime']);
        $data['updateTime'] = $data['updateTime'] ? date('Y-m-d H:i:s', $data['updateTime']) : '';
        $data['type']       = $type[$data['type']];
        $data['stateName']  = $data['state'] == 0 ? '认证中' : ($data['state'] == 1 ? '已通过' : '已拒绝');
        $data['idCard']     = $data['idCard'] ? explode(',', $data['idCard']) : [];
        $data['license']    = $data['license'] ? explode(',', $data['license']) : [];
        $data['image']      = $data['image'] ? explode(',', $data['image']) : [];
        $data['materials']  = $data['materials'] ? explode(',', $data['materials']) : [];
        return $data;
    }

    public function getRate(){
        $cacheKey = md5('set_loan_rate');
        if(!$data = cache($cacheKey)){
            $info = Db::name('dictionary')->where(['d_typeid' => 1, 'd_key' => 0])->field('d_value as value')->find();
            $data = $info['value'];
            cache($cacheKey, $data, 5);
        }
        return $data;
    }


    
    

}