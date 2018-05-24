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
class Role extends Admin
{

    /**
     * 首页
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = array();
        if($this->isAdmin){
            if(isset($this->data['orgId']) && !empty($this->data['orgId'])){
                $orgId = $this->data['orgId'] + 0;
                $where['sr.orgId'] = $orgId;
            }
        }else{
            $where['sr.orgId'] = ['in', $this->orgIds];
        }

        if(isset($this->data['roleName']) && !empty($this->data['roleName'])){
            $roleName = htmlspecialchars(trim($this->data['roleName']));
            $where['sr.roleName'] = ['like', '%' . $roleName . '%'];
        }

        $data  = model('Role')->getRoleDataAll($where, $page, $rows);
        $this->apiReturn(200, ['list' => $data['data'], 'page' => $page, 'rows' => $rows, 'total' => $data['count']]);
    }

    /**
     * 添加角色
     * */
    public function create(){
        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'Role.add');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        //超级管理员和属于该组织机构的角色可添加权限
        if(!$this->checkUserIsBelongToOrg($this->userId, $this->data['orgId'])){
            $this->apiReturn(201, '', '您没权限给其它组织机构添加角色');
        }

        $roleName = htmlspecialchars(trim($this->data['roleName']));
        $orgId    = $this->data['orgId'] + 0;
        if(Db::name('system_role')->where(['orgId' => $orgId, 'roleName' => $roleName, 'isDelete' => 0])->count()){
            $this->apiReturn(201, '', '该角色已添加');
        }

        $data   = [
            'orgId'      => $this->data['orgId'],
            'roleName'   => $this->data['roleName'],
            'remark'     => isset($this->data['remark']) ? $this->data['remark'] : '',
        ];

        $result = Db::name('system_role')->insert($data);
        !$result && $this->apiReturn(201, '', '添加角色失败');
        $this->apiReturn(200, '', '添加角色成功');
    }

    public function edit(){

        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'Role.edit');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        //超级管理员和属于该组织机构的角色可添加权限
        if(!$this->checkUserIsBelongToOrg($this->userId, $this->data['orgId'])){
            $this->apiReturn(201, '', '您没权限给其它组织机构添加角色');
        }

        $roleName = htmlspecialchars(trim($this->data['roleName']));
        $orgId    = $this->data['orgId'] + 0;
        $roleId   = $this->data['id'] + 0;
        if(Db::name('system_role')->where(['orgId' => $orgId, 'roleName' => $roleName, 'isDelete' => 0, 'roleId' => ['neq', $roleId]])->count()){
            $this->apiReturn(201, '', '该角色已存在');
        }

        $data   = [
            'orgId'      => $this->data['orgId'],
            'roleName'   => $this->data['roleName'],
            'remark'     => isset($this->data['remark']) ? $this->data['remark'] : '',
        ];

        $result = Db::name('system_role')->where(['roleId' => $roleId])->update($data);
        $result === false && $this->apiReturn(201, '', '编辑角色失败');
        $this->apiReturn(200, '', '编辑角色成功');
    }

    /**
     * 删除角色
     * @param roleId integer 角色ID
     * @return json
     * */
    public function remove(){
        (!isset($this->data['roleId']) || empty($this->data['roleId'])) && $this->apiReturn(201, '', '角色ID非法');
        $roleId = $this->data['roleId'] + 0;

        if($roleId == 1){//暂时设定超级管理员的角色ID为1
            $this->apiReturn(201, '', '超级管理员不能删除');
        }

        $result = Db::name('system_role')->where(['roleId' => $roleId, 'orgId' => $this->orgId])->update(['isDelete' => 1]);
        $result === false && $this->apiReturn(201, '', '删除失败');
        $this->apiReturn(200, '', '删除成功');
    }

    public function lists(){
        $orgId = isset($this->data['orgId']) && !empty($this->data['orgId']) ? $this->data['orgId'] + 0 : 0;
        $data  = model('Role')->getRoleByOrgIdAll($orgId, 'roleId as id,roleName');
        $this->apiReturn(200, $data);
    }
    
    

}