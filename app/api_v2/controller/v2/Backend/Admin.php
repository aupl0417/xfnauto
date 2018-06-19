<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api_v2\controller\v2\Backend;

use think\Controller;
use think\Db;
use think\Request;
class Admin extends Base {
    protected $userId;
    protected $user;
    protected $orgId;
    protected $orgIds  = array();
    protected $userIds = array();
    protected $roleIds = array();
    protected $error;
    protected $errorCode;
    protected $isAdmin;
    protected $adminIds = array();

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        (!isset($this->data['sessionId']) || empty($this->data['sessionId'])) && $this->apiReturn(201, 'SESSIONID不能为空');
        $user           = model('SystemUser')->getUserBySessionId(trim($this->data['sessionId']));
        !$user && $this->apiReturn(4002, '', '请重新登录');
        $this->userId   = $user['usersId'];
        $this->orgId    = $user['orgId'];
        $this->user     = $user;

        $org   = model('Organization')->getOrgAll(['orgLevel' => 1, 'status' => 1], 'orgLevel');
        if($org){
            $orgIds = array_column($org, 'orgLevel');
            $users   = model('SystemUser')->getDataAll(['orgId' => ['in', $orgIds]], 'usersId');
            $this->adminIds = $users ? array_column($users, 'usersId') : [];
        }

        //获取所有下级用户
        $lowerLevel = array();
        model('SystemUser')->getAllLowerLevel($this->userId, $lowerLevel);

        $this->orgIds  = [$this->orgId];
        $this->userIds = [$this->userId];
        $this->roleIds = $user['roleIds'] ? explode(',', $user['roleIds']) : [];
        if($lowerLevel){
            $this->orgIds  = array_unique(array_merge($this->orgIds, array_column($lowerLevel, 'orgId')));//下级用户所在门店的ID
            $this->userIds = array_unique(array_merge($this->userIds, array_column($lowerLevel, 'userId')));//下级用户ID
            $this->roleIds = explode(',', trim(implode(',', array_merge($this->roleIds, array_column($lowerLevel, 'roleIds'))), ','));
            $this->roleIds = implode(',', array_unique($this->roleIds));//该账号的所有角色权限（包括下级用户的）
        }

        $this->isAdmin = true;
        if(!in_array($this->userId, $this->adminIds, true)){
            $this->isAdmin = false;
        }

        if(!$this->checkUserAuth($this->userId)){
            $this->apiReturn(201, '', $this->error);
        }
    }

    /**
     * 生成菜单中地址链接
     * @param $controller string 控制器
     * @param $action     string action
     * @return string
     * */
    public function createMenuUrl($controller = '', $action = ''){
        if(!$controller){
            $controller = request()->controller();
        }
        if(!$action){
            $action = request()->action();
        }
        $controller = explode('.', $controller);
        array_shift($controller);
        $controller = implode('/', $controller);
        return strtolower($controller . '/' . $action);
    }

    /**
     * 检查一个用户是否属于指定的组织
     * @param $userId 用户ID
     * @param $orgId  组织ID
     * @return boolean
     * */
    public function checkUserIsBelongToOrg($userId, $orgId){
        if(in_array($userId, $this->adminIds)){
            return true;
        }

        $result = model('SystemUser')->getUser($userId, $orgId, 'usersId');
        if(!$result){
            return false;
        }
        return true;
    }

    /**
     * 检查用户接口权限
     * @param $userId int  用户ID
     * @return boolean
     * */
    public function checkUserAuth($userId, $menuId = 0){
        $role = model('RoleUser')->getRoleByUserId($userId, $this->orgId);
        if(!$role){
            $this->error = '没有该功能操作权限';
            return false;
        }

        $roleIds        = array_unique(array_column($role, 'roleId'));
        $roleAccessAuth = $this->getRoleAccessAuth($roleIds);
        if(!$roleAccessAuth){
            $this->error = '您没有任何操作权限';
            return false;
        }

        if($menuId){
            $menu = model('Menu')->getMenuById($menuId);
        }else{
            $url  = $this->createMenuUrl();
            $menu = model('Menu')->getMenuBySrc($url);
        }

        if(!$menu){
            $this->error = '菜单不存在该接口';
            return false;
        }

        if(!in_array($menu['id'], $roleAccessAuth)){
            $this->error = '您没有该接口的操作权限_' . $menu['id'];
            return false;
        }
        return true;
    }

    /**
     * 通过 角色id集 获取接口权限集
     * @param $roleIds string/array
     * @return array
     * */
    public function getRoleAccessAuth($roleIds){
        if(!$roleIds){
            $this->error = '角色ID集非法';
            return false;
        }
        $roleAccess = model('RoleAccess')->getRoleAccessByRoleIds($roleIds);
        if(!$roleAccess){
            $this->error = '该角色未配置权限';
            return false;
        }
        $roleAccessAuth = array_filter(array_column($roleAccess, 'access_ids'));
        $roleAccessAuth = explode(',', implode(',', $roleAccessAuth));
        return array_unique($roleAccessAuth);
    }

}