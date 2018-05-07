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

class SystemUser extends Model
{

    protected $table = 'system_user';

    /*
     * 通过分销员ID来获取分销员数据
     * */
    public function getUserBySessionId($id, $field = '*'){
        return Db::name($this->table)->field($field)->where(['sessionId' => $id])->find();
    }


    
}