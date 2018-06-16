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
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = array();

        if(isset($this->data['realName']) && !empty($this->data['realName'])){
            $realName = htmlspecialchars(trim($this->data['realName']));
            $where['realName'] = ['like', '%' . $realName . '%'];
        }

        if(isset($this->data['phone']) && !empty($this->data['phone'])){
            $phone = htmlspecialchars(trim($this->data['phone']));
            $where['phoneNumber'] = ['like', '%' . $phone . '%'];
        }

        if(isset($this->data['orgId']) && !empty($this->data['orgId'])){
            $orgId = $this->data['orgId'] + 0;
            if($this->isAdmin){
                $where['orgId'] = $orgId;
            }else{
                $where['orgId'] = $this->orgId;
            }
        }else{
            if(!$this->isAdmin){
                $where['orgId'] = $this->orgId;
            }

        }

        $field = 'usersId as id,realName,phoneNumber as phone,parentIds,roleIds,orgName,status,isEnable,orgId';
        $data  = model('SystemUser')->getSystemUserList($where, $field, $page, $rows);
        if($data){
            foreach($data['list'] as $key => &$value){
                $higherUps = model('SystemUser')->getDataAll(['usersId' => ['in', $value['parentIds']]], 'realName');
                $value['higherUps'] = $higherUps ? implode(',', array_column($higherUps, 'realName')) : '';
                $roleIds = model('RoleUser')->getRoleByUserId($value['id'], $value['orgId']);
                if($roleIds){
                    $roleIds = array_unique(array_column($roleIds, 'roleId'));
                }
                $roles = model('Role')->getRoleAll(['roleId' => ['in', $roleIds], 'orgId' => $value['orgId'], 'isDelete' => 0], 'roleName');
                $value['roles'] = $roles ? implode(',', array_column($roles, 'roleName')) : '';
            }
        }

        $this->apiReturn(200, $data);
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

        Db::startTrans();
        try{
            $orgInfo  = model('Organization')->getOrganizationByOrgId($this->data['orgId'], 'shortName,orgCode');
            if(!$orgInfo){
                throw new Exception('门店不存在');
            }
            $time = date('Y-m-d H:i:s');
            $data = [
                'phoneNumber'   => $this->data['phoneNumber'],
                'orgId'         => $this->data['orgId'],
                'realName'      => $this->data['realName'],
                'roleIds'       => $this->data['roleIds'],
                'parentIds'     => $this->data['parentIds'],
                'agentGender'   => $this->data['sex'] + 0,
                'birthday'      => isset($this->data['birthday']) && !empty($this->data['birthday'])     ? htmlspecialchars(trim($this->data['birthday'])) : null,
                'cardNo'        => isset($this->data['cardNo'])   && !empty($this->data['cardNo'])       ? htmlspecialchars(trim($this->data['cardNo'])) : '',
                'entryTime'     => isset($this->data['entryTime'])&& !empty($this->data['entryTime'])    ? htmlspecialchars(trim($this->data['entryTime'])) : null,
                'basePay'       => isset($this->data['basePay'])  && !empty($this->data['basePay'])      ? floatval($this->data['basePay']) : 0,
                'headPortrait'  => isset($this->data['headPortrait']) ? htmlspecialchars(trim($this->data['headPortrait'])) : '',
                'password'      => strtoupper(md5('1')),
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

            $result = Db::name('system_role_user')->insertAll($role);
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

    /**
     * 编辑系统用户
     * */
    public function edit(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $userId = $this->data['id'] + 0;
        unset($this->data['sessionId'], $this->data['id']);
        $result = $this->validate($this->data, 'SystemUser.edit');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $orgInfo  = model('Organization')->getOrganizationByOrgId($this->data['orgId'], 'shortName,orgCode');
        if(!$orgInfo){
            $this->apiReturn(201, '', '门店不存在');
        }
        $userInfo = model('SystemUser')->getUserById($userId, 'usersId,parentIds,roleIds');
        if(!$userInfo){
            $this->apiReturn(201, '', '用户不存在');
        }

        Db::startTrans();
        try{

            $time = date('Y-m-d H:i:s');
            $data = [
                'orgId'         => $this->data['orgId'],
                'realName'      => $this->data['realName'],
                'roleIds'       => $this->data['roleIds'],
                'parentIds'     => $this->data['parentIds'],
                'agentGender'   => $this->data['sex'] + 0,
                'updateTime'    => $time,
                'orgName'       => trim($orgInfo['shortName']),
                'orgCode'       => $orgInfo['orgCode'],
            ];

            if(isset($this->data['birthday']) && !empty($this->data['birthday'])){
                $data['birthday'] = htmlspecialchars(trim($this->data['birthday']));
            }

            if(isset($this->data['cardNo']) && !empty($this->data['cardNo'])){
                $data['cardNo'] = htmlspecialchars(trim($this->data['cardNo']));
            }

            if(isset($this->data['entryTime']) && !empty($this->data['entryTime'])){
                $data['entryTime'] = htmlspecialchars(trim($this->data['entryTime']));
            }

            if(isset($this->data['basePay']) && !empty($this->data['basePay'])){
                $data['basePay'] = floatval($this->data['basePay']);
            }

            if(isset($this->data['headPortrait']) && !empty($this->data['headPortrait'])){
                $data['headPortrait'] = htmlspecialchars(trim($this->data['headPortrait']));
            }

            $result = Db::name('system_user')->where(['usersId' => $userId])->update($data);
            if($result === false){
                throw new Exception('编辑系统用户失败');
            }

            $newRole = explode(',', $data['roleIds']);
            $oldRole = model('RoleUser')->getByUserId($userId);
            $oldRole = $oldRole ? array_column($oldRole, 'roleId') : [];
            //如果角色换了，就更新
            if($oldRole != $newRole){
                $roleIntersect = array_intersect($oldRole, $newRole);
                if($roleIntersect){//如果有交集，则oldRole中去掉交集，并删除oldRole中剩余的数据，newRole中也去掉交集，并插入剩余的数据
                    $oldRole = array_diff($oldRole, $roleIntersect);
                    if($oldRole){
                        $result = Db::name('system_role_user')->where(['userId' => $userId, 'roleId' => ['in', $oldRole]])->delete();
                        if(!$result){
                            throw new Exception('删除用户角色失败');
                        }
                    }
                    $newRole = array_diff($newRole, $roleIntersect);
                }
                $role    = array();
                foreach($newRole as $key => $value){
                    $role[$key]['userId'] = $userId;
                    $role[$key]['roleId'] = $value;
                }

                if($role){
                    $result = Db::name('system_role_user')->insertAll($role);
                    if(!$result){
                        throw new Exception('添加到用户角色表失败');
                    }
                }
            }

            Db::commit();
            $this->apiReturn(200, '', '编辑成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '编辑失败');
        }
    }

    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $userId = $this->data['id'] + 0;
        $field  = 'usersId as id,headPortrait,realName,phoneNumber as phone,orgId,orgName,status,isEnable,agentGender as gender,birthday,cardNo,entryTime,basePay,parentIds,roleIds';
        $data   = model('SystemUser')->getUserById($userId, $field);
        !$data  && $this->apiReturn(201, '', '用户信息不存在');
        $data['birthday']   = checkTimeIsValid($data['birthday']) ? date('Y-m-d', strtotime($data['birthday'])) : '';
        $data['entryTime']  = checkTimeIsValid($data['entryTime']) ? date('Y-m-d', strtotime($data['entryTime'])) : '';
        $data['parentUser'] = Db::name('system_user')->where(['usersId' => ['in', $data['parentIds']]])->field('usersId as id,realName')->select();
        $data['roles']      = Db::name('system_role')->where(['roleId' => ['in', $data['roleIds']], 'orgId' => $data['orgId'], 'isDelete' => 0])->field('roleId as id,roleName')->select();

        unset($data['parentIds'], $data['roleIds']);
        $this->apiReturn(200, $data);
    }

    /**
     * 获取上级列表
     * */
    public function higherUps(){
        $orgId = isset($this->data['orgId']) && !empty($this->data['orgId']) ? $this->data['orgId'] + 0 : 0;
        $id    = isset($this->data['id']) && !empty($this->data['id']) ? $this->data['id'] + 0 : 0;
        $data = model('SystemUser')->getUserByOrgId($orgId, 'usersId as id,realName');
        $userIds = [$id];
        if($id){
            $lowerLevel = [];
            model('SystemUser')->getAllLowerLevel($id, $lowerLevel);
            $userIds = array_merge($userIds, array_column($lowerLevel, 'userId'));
        }

        if($data){
            foreach($data as $key => &$value){
                if(in_array($value['id'], $userIds)){
                    unset($data[$key]);
                }else{
                    continue;
                }
            }
            $data = array_values($data);
        }
        $this->apiReturn(200, $data);
    }

    /**
     * 禁用或启用
     * */
    public function remove(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');
        $userId = $this->data['id'] + 0;

        if($userId == $this->userId){
            $this->apiReturn(201, '', '不能对自身进行此操作');
        }

        $where = ['usersId' => $userId];
        $user = Db::name('system_user')->where($where)->field('isEnable')->find();
        !$user && $this->apiReturn(201, '', '用户不存在');
        if($user['isEnable'] == 0){//已禁用
            $isEnable = 1;//启用
            $msg      = '启用';
        }else{
            $isEnable = 0;
            $msg      = '禁用';
        }

        $result = Db::name('system_user')->where($where)->update(['isEnable' => $isEnable]);
        $result === false && $this->apiReturn(201, '', $msg . '失败');
        $this->apiReturn(200, ['isEnable' => $isEnable], $msg . '成功');
    }

    public function userInfo(){

        $data = [
            'headPortrait' => $this->user['headPortrait'],
            'phoneNumber'  => $this->user['phoneNumber'],
            'orgName'      => $this->user['orgName'],
            'realName'     => $this->user['realName'],
        ];
        $roleIds = model('RoleUser')->getRoleByUserId($this->userId, $this->orgId);
        $role = '';
        if($roleIds){
            $roleIds = array_unique(array_column($roleIds, 'roleId'));
            $role = model('Role')->getRoleAll(['isDelete' => 0, 'roleId' => ['in', $roleIds], 'orgId' => $this->orgId]);
            $role = implode(',', array_column($role, 'roleName'));
        }
        $data['roles'] = $role;
        $this->apiReturn(200, $data);
    }

}