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

class RoleUser extends Model
{

    protected $table = 'system_role_user';

    public function getRoleByUserId($userId){
        if(!$userId || !is_numeric($userId)){
            return false;
        }
        $role = Db::name($this->table)->where(['userId' => $userId])->field('userRoleId as id,userId,roleId')->select();
        if(!$role){
            return false;
        }
        return $role;
    }
    
    

}