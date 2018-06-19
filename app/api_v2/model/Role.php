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

class Role extends Model
{

    protected $table = 'system_role';

    public function getRoleByOrgId($orgId = 0, $page = 1, $rows){

        $where = ['isDelete' => 0];
        if($orgId !== 0){
            if(is_string($orgId)){
                $orgId = explode(',', $orgId);
            }
            $where['sr.orgId'] = ['in', $orgId];
        }

        $field = 'roleId as id,shortName as orgName,roleName,sr.remark,sr.orgId';
        $count = Db::name($this->table . ' sr')->where($where)->count();
        $role  = Db::name($this->table . ' sr')->where($where)->field($field)->page($page, $rows)->join('system_organization so', 'so.orgId=sr.orgId', 'left')->select();

        return ['data' => $role, 'count' => $count];
    }

    public function getRoleById($id){
        if(!$id || !is_null($id)){
            return false;
        }

        $field = 'roleId as id,roleName,sr.remark';
        return Db::name($this->table)->where(['roleId' => $id])->field($field)->find();
    }

    public function getRoleByOrgIdAll($orgId = 0, $field = ''){
        $where = ['isDelete' => 0];
        if($orgId !== 0){
            $where['sr.orgId'] = $orgId;
        }

        if(!$field){
            $field = 'roleId as id,roleName,sr.remark';
        }

        return Db::name($this->table . ' sr')->where($where)->field($field)->select();
    }

    public function getRoleAll($where = array(), $field = ''){
        if(!$where){
            $where = ['isDelete' => 0];
        }

        if(!$field){
            $field = 'roleId as id,roleName,remark';
        }

        return Db::name($this->table)->where($where)->field($field)->select();
    }

    public function getRoleDataAll($where = array(), $page = 1, $rows){
        if(!$where){
            $where = ['isDelete' => 0];
        }

        $field = 'roleId as id,shortName as orgName,roleName,sr.remark,sr.orgId';
        $count = Db::name($this->table . ' sr')->where($where)->count();
        $role  = Db::name($this->table . ' sr')->where($where)->field($field)->page($page, $rows)->join('system_organization so', 'so.orgId=sr.orgId', 'left')->select();

        return ['data' => $role, 'count' => $count];
    }
    
    

}