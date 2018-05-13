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

    public function getUserByOrgId($orgId, $field = '*'){
        return Db::name($this->table)->where(['orgId' => $orgId])->field($field)->order('usersId desc')->select();
    }

    public function getUserGroupInfo($userId){
        if(!$userId || !is_numeric($userId)){
            return false;
        }
        $field = 'sg.org_id as orgId,ug.over_manage as over_manage';
        $where = [
            'ug.user_id' => $userId,
            'sg.over_delete' => 0,
            'grouping_name' => '销售组'
        ];
        $data = Db::name('system_user_grouping ug')
              ->where($where)->field($field)
              ->join('system_grouping sg', 'ug.grouping_id=sg.grouping_id', 'left')
              ->find();
        if(!$data){
            return false;
        }
        return $data;
    }

    public function getUserById($userId, $field = '*'){
        return Db::name($this->table)->where(['usersId' => $userId])->field($field)->find();
    }



}