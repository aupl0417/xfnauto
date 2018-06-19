<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v3\Backend;

use think\Controller;
use think\Db;
use think\Exception;
use think\Request;

class Login extends Base
{

    /**
     * 用户登录
     * @return json
     * */
    public function index(){
        $result = $this->validate($this->data, 'Login');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $user = model('SystemUser')->getUserByPhone($this->data['phoneNumber']);
        !$user && $this->apiReturn(201, '', '用户不存在');

        if(strtoupper(md5($this->data['password'])) != $user['password']){
            $this->apiReturn(201, '', '密码错误');
        }

        session_id(md5(serialize($this->data) . microtime(true)));
        $result = Db::name('system_user')->where(['usersId' => $user['usersId']])->update(['sessionId' => session_id()]);
        $result === false && $this->apiReturn(201, '', '登录失败');

        $menus = [];
        $role  = model('RoleUser')->getRoleByUserId($user['usersId']);
        $roleName = '';
        if($role){
            $roleIds = array_column($role, 'roleId');
            $roleName= model('Role')->getRoleAll(['roleId' => ['in', $roleIds], 'isDelete' => 0]);
            $roleName= $roleName ? implode(',', array_column($roleName, 'roleName')) : '';
            $menus   = model('RoleAccess')->getRoleAccessByRoleIds($roleIds);
            $menus   = $menus ? array_filter(array_column($menus, 'access_ids')) : [];
            $menus   = $menus ? explode(',', implode(',', $menus)) : [];
            $menus   = array_unique($menus);
        }

        $org = model('Organization')->getOrganizationByOrgId($user['orgId'], 'orgtype as orgType,orgLevel');

        $data = [
            'menus' => $menus,
            'orgLevel' => $org ? $org['orgLevel'] : '',
            'orgType'  => $org ? $org['orgType'] : '',
            'realName' => $user['realName'],
            'roleId'   => $user['roleIds'],
            'roleName' => $roleName,
            'sessionId'=> session_id(),
            'userName' => $user['userName'] ?: ''
        ];

        $this->apiReturn(200, $data, '登录成功');
    }
    

}