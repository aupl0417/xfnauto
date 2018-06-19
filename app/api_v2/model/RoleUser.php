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

class RoleUser extends Model
{

    protected $table = 'system_role_user';

    public function getRoleByUserId($userId, $orgId){
        if(!$userId || !is_numeric($userId)){
            return false;
        }

        $join = [
            ['system_role sr', 'ur.roleId=sr.roleId', 'left']
        ];

        $role = Db::name($this->table . ' ur')->where(['ur.userId' => $userId, 'sr.orgId' => $orgId])->join($join)->field('userRoleId as id,userId,ur.roleId')->select();
        if(!$role){
            return false;
        }
        return $role;
    }

    public function getByUserId($userId){
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