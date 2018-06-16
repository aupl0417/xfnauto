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
        $where = ['status' => 1];
        if($orgId){
            $where['orgId'] = $orgId;
        }

        return Db::name($this->table)->where($where)->field($field)->find();
    }

    /**
     * 获取组织列表（有分页）
     * @param $where string/array
     * @param $whereOr string/array
     * @param $page int
     * @param $rows int
     * @param $order string/array
     * @return array
     * */
    public function getOrgList($where, $whereOr = '', $page = 1, $rows = 50, $order = 'orgId desc'){
        $field = 'ao.orgId as id,ao.shortName as orgName,ao.orgLevel,ao.address,ao.status,address,areaId,cityId,linkMan,provinceId,provinceName,remark';
        $count = Db::name($this->table . ' ao')->where($where)->whereOr($whereOr)->count();
        $data  = Db::name($this->table . ' ao')->where($where)->whereOr($whereOr)->field($field)->page($page, $rows)->order($order)->select();
        if(!$data){
            return false;
        }

        $status = ['运营中', '已禁用', '待审核'];
        foreach($data as $key => &$value){
            $value['orgLevel']   = $value['orgLevel'] == 2 ? '分公司' : ($value['orgLevel'] == 3 ? '门店' : '');
//            $value['statusName'] = $value['status'] ? $status[$value['status'] - 1] : '';
        }

        return ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows];
    }

    /**
     * 获取所有组织列表，不分页
     * @param $where string/array
     * @param $whereOr string/array
     * @param $field string
     * @return Array
     * */
    public function getOrgAll($where, $field = '*'){
        $data = Db::name($this->table)->where($where)->field($field)->select();
        if(!$data){
            return false;
        }
        return $data;
    }

    /**
     * 获取组织列表（有分页）
     * @param $where string/array
     * @param $whereOr string/array
     * @param $page int
     * @param $rows int
     * @param $order string/array
     * @return array
     * */
    public function getOrgData($where, $whereOr = array(), $page = 1, $rows = 50, $order = 'orgId desc', $field = ''){
        if(!$field){
            $field = 'orgId,shortName,orgLevel,address,status,areaId,cityId,linkMan,provinceId,provinceName,remark,telephone as telePhone,provinceName,cityName,areaName';
        }

        $count = Db::name($this->table)->where($where)->whereOr($whereOr)->count();
        $data  = Db::name($this->table)->where($where)->whereOr($whereOr)->field($field)->page($page, $rows)->order($order)->select();

        return ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows];
    }


    public function getAllChildOrg($orgIds, &$data){
        $field = 'orgId';
        $org  = $this->getOrgAll(['parentId' => ['in', $orgIds]], $field);
        if($org){
            $org  = array_column($org, $field);
            $data = array_merge($data, $org);
            self::getAllChildOrg($org, $data);
        }
        return $data;
    }

    /**
     * 获取所有组织列表，不分页
     * @param $where string/array
     * @param $whereOr string/array
     * @param $field string
     * @return Array
     * */
    public function getOrgs($where, $whereOr = array(), $field = '*'){
        $data = Db::name($this->table)->where($where)->whereOr($wh)->field($field)->select();
        if(!$data){
            return false;
        }
        return $data;
    }
    
    

}