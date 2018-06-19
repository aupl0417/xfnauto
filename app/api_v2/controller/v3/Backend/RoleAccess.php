<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v2\Backend;

use think\Controller;
use think\Db;
class RoleAccess extends Admin
{

    /**
     * 所有权限接口列表
     * */
    public function index(){
        if(isset($this->data['id']) && !empty($this->data['id'])){
            $roleIds = $this->data['id'] + 0;
            $data = model('RoleAccess')->getRoleAccessByRoleIds($roleIds);
            $menu = array();
            if($data){
                $authIds = implode(',', array_column($data, 'access_ids'));
                $authIds = explode(',', $authIds);
                $authIds = array_unique(array_filter($authIds));

                $where   = ['menuId' => ['in', $authIds], 'isDelete' => 0];
                $menu    = model('Menu')->getMenuAll($where, 'menuId as id,parentId,menuName as name,src');
                $menu    = model('Menu')->getTree($menu);
            }
            $this->apiReturn(200, $menu);
        }else{
            action('v1.Backend.Menu/index');
        }
    }
    
    public function addAuth(){
        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'RoleAccess');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $data = [
            'role_id' => $this->data['roleId'] + 0,
            'access_ids' => is_array($this->data['lists']) ? implode(',', $this->data['lists']) : trim($this->data['lists']),
        ];

        $count = Db::name('system_role_access')->where(['role_id' => $data['role_id']])->count();
        if(!$count){
            $result = Db::name('system_role_access')->insert($data);
            !$result && $this->apiReturn(201, '', '配置权限失败');
        }else{
            $result = Db::name('system_role_access')->where(['role_id' => $data['role_id']])->update($data);
            $result === false && $this->apiReturn(201, '', '配置权限失败');
        }
        $this->apiReturn(200, '', '配置权限成功');
    }

}