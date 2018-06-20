<?php

/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:47
 */
namespace app\api_v2\model;

use think\Db;
use think\Model;

class ShopUser extends Model
{

    protected $table = 'shop_user';

    /*
     * 通过分销员ID来获取分销员数据
     * */
    public function getUserBySessionId($id, $field = '*'){
        return Db::name($this->table)->field($field)->where(['session_id' => $id])->find();
    }

    public function getUserByIdAll($userId, $field = '*'){
        $where = [
            'shop_user_id' => $userId,
        ];

        $join = [
            ['shop_info', 'si_userId=shop_user_id', 'left'],
            ['shop_loan', 's_userId=shop_user_id', 'left'],
        ];

        return Db::name('shop_user')->where($where)->field($field)->join($join)->find();
    }
    
    

}