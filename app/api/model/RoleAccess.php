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

class RoleAccess extends Model
{

    protected $table = 'system_role_access';

    /**
     * 通过角色ID来获取权限
     * @return boolean / array
     * */
    public function getRoleAccessByRoleId($roleId){
        if(!$roleId || !is_numeric($roleId)){
            return false;
        }
        $role = Db::name($this->table)->where(['role_id' => $roleId])->field('id,role_id,access_ids')->find();
        if(!$role){
            return false;
        }
        return $role;
    }

    /**
     * 通过角色id集来获取权限
     * @param $roleIds string/array 角色id集
     * @return boolean/array
     * */
    public function getRoleAccessByRoleIds($roleIds = ''){
        $where = ['is_delete' => 0];
        if($roleIds){
            if(is_string($roleIds)){
                $roleIds = explode(',', $roleIds);
            }
            $where['role_id'] = ['in', $roleIds];
        }

        $data = Db::name($this->table)->field('id,role_id,access_ids')->where($where)->select();
        if(!$data){
            return false;
        }
        return $data;
    }
    
    

}