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

class Organization extends Model
{

    protected $table = 'system_organization';

    public function getOrganizationByOrgId($orgId, $field = '*'){
        if(!$orgId || empty($orgId)){
            return false;
        }

        return Db::name($this->table)->field($field)->find();
    }

    public function getOrgList($where, $whereOr = array(), $page = 1, $rows = 50, $order = 'orgId desc'){
        $field = 'ao.orgId as id,ao.shortName as orgName,bo.shortName as parentOrgName,ao.orgLevel,ao.address,ao.status';
        $count = Db::name($this->table . ' ao')->where($where)->whereOr($whereOr)->count();
        $data  = Db::name($this->table . ' ao')->where($where)->whereOr($whereOr)->field($field)->join($this->table . ' bo', 'ao.parentId=bo.orgId')->page($page, $rows)->order($order)->select();
        if(!$data){
            return false;
        }

        return ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows];
    }

    public function getOrgAll($where, $whereOr = array(), $field = '*'){
        $data = Db::name($this->table)->where($where)->whereOr($whereOr)->field($field)->select();
        if(!$data){
            return false;
        }
        return $data;
    }
    
    

}