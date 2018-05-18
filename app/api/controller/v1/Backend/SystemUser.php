<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\Backend;

use think\Controller;
use think\Db;
use think\Exception;

class SystemUser extends Admin
{

    /**
     * 系统用户列表
     * */
    public function index(){

    }

    /**
     * 添加系统用户
     * */
    public function create(){
        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'SystemUser');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $orgInfo = Db::name('system_organization')->where(['orgId' => $this->data['orgId']])->field('shortName,orgCode')->find();

        Db::startTrans();
        try{

            $time = date('Y-m-d H:i:s');
            $data = [
                'phoneNumber'   => $this->data['phoneNumber'],
                'orgId'         => $this->data['orgId'],
                'realName'      => $this->data['realName'],
                'roleIds'       => $this->data['roleIds'],
                'parentIds'     => $this->data['parentIds'],
                'agentGender'   => $this->data['sex'] + 0,
                'birthday'      => isset($this->data['birthday'])     ? htmlspecialchars(trim($this->data['birthday'])) : '',
                'cardNo'        => isset($this->data['cardNo'])       ? htmlspecialchars(trim($this->data['cardNo'])) : '',
                'entryTime'     => isset($this->data['entryTime'])    ? htmlspecialchars(trim($this->data['entryTime'])) : '',
                'basePay'       => isset($this->data['basePay'])      ? floatval($this->data['basePay']) : 0,
                'headPortrait'  => isset($this->data['headPortrait']) ? htmlspecialchars(trim($this->data['headPortrait'])) : '',
                'password'      => md5('123456'),
                'createTime'    => $time,
                'updateTime'    => $time,
                'orgName'       => trim($orgInfo['shortName']),
                'orgCode'       => $orgInfo['orgCode'],
                'status'        => 1
            ];

            $result = Db::name('system_user')->insert($data);
            if(!$result){
                throw new Exception('添加系统用户失败');
            }
            $userId = Db::name('system_user')->getLastInsID();

            $roleArr = explode(',', $data['roleIds']);
            $role    = array();
            foreach($roleArr as $key => $value){
                $role[$key]['userId'] = $userId;
                $role[$key]['roleId'] = $value;
            }

            $result = Db::name('system_user_role')->insertAll($role);
            if(!$result){
                throw new Exception('添加到用户角色表失败');
            }
            Db::commit();
            $this->apiReturn(200, '', '添加成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '添加失败');
        }
    }

}