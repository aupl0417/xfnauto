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
use think\Exception;
use think\Request;

class Menu extends Admin
{

    protected $cachekey;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->cachekey = md5('menu_0');
    }

    /**
     * 首页
     * @return json
     * */
    /**
     * 所有菜单列表
     * */
    public function index(){
        $menu = array();
//        if($this->isAdmin){
            $menuModel = model('Menu');
            if(!$menu = cache($this->cachekey)){
                $menu    = $menuModel->getMenuAll(['isDelete' => 0], 'menuId as id,parentId,menuName as name,src');
                $menu    = $menuModel->getTree($menu);
                cache($this->cachekey, $menu, 300);
            }
//        }

        $this->apiReturn(200, $menu);
    }

    /**
     * 添加菜单
     * */
    public function create(){
        unset($this->data['sessionId']);
        (!isset($this->data['menuName']) || empty($this->data['menuName'])) && $this->apiReturn(201, '', '菜单名称不能为空');
        (!isset($this->data['src']) || empty($this->data['src'])) && $this->apiReturn(201, '', '菜单URL不能为空');

//        if(!$this->isAdmin){
//            $this->apiReturn(201, '', '您不是超级管理员');
//        }

        $parentId = isset($this->data['parentId']) && !empty($this->data['parentId']) ? $this->data['parentId'] + 0 : 0;
        $menuName = htmlspecialchars(trim($this->data['menuName']));
        $src      = htmlspecialchars(trim($this->data['src']));

        if(Db::name('system_menu')->where(['src' => $src])->count()){
            $this->apiReturn(201, '', '该菜单地址已存在');
        }

        $data = [
            'parentId' => $parentId,
            'menuName' => $menuName,
            'src'      => $src,
        ];

        try{
            Db::startTrans();

            $result = Db::name('system_menu')->insert($data);
            if(!$result){
                throw new Exception('添加菜单失败');
            }

            $data['menuId'] = Db::name('system_menu')->getLastInsID();
//            $roleAccess = $this->getRoleAccessAuth($this->user['roleIds']);
//            $roleAccess[] = $data['menuId'];
//            $result = Db::name('system_role_access')->where()
            Db::commit();
            cache($this->cachekey, null);
            $this->apiReturn(200, $data, '添加菜单成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '添加菜单失败');
        }
    }

    public function edit(){
        (!isset($this->data['menuId']) || empty($this->data['menuId'])) && $this->apiReturn(201, '', '菜单ID非法');
        (!isset($this->data['menuName']) || empty($this->data['menuName'])) && $this->apiReturn(201, '', '菜单名称不能为空');
        (!isset($this->data['src']) || empty($this->data['src'])) && $this->apiReturn(201, '', '菜单URL不能为空');

//        if(!$this->isAdmin){
//            $this->apiReturn(201, '', '您不是超级管理员');
//        }

        unset($this->data['sessionId']);
        $menuId = $this->data['menuId'] + 0;
        $parentId = isset($this->data['parentId']) && !empty($this->data['parentId']) ? $this->data['parentId'] + 0 : 0;
        $menuName = htmlspecialchars(trim($this->data['menuName']));
        $src      = htmlspecialchars(trim($this->data['src']));

        unset($this->data['sessionId']);

        if(Db::name('system_menu')->where(['src' => $src, 'menuName' => $menuName, 'isDelete' => 0, 'menuId' => ['neq', $menuId]])->count()){
            $this->apiReturn(201, '', '该菜单已存在');
        }

        $data = [
            'parentId' => $parentId,
            'menuName' => $menuName,
            'src'      => $src,
        ];

        $result = Db::name('system_menu')->where(['menuId' => $menuId])->update($data);
        $result === false && $this->apiReturn(201, '', '编辑菜单失败');
        cache($this->cachekey, null);
        $this->apiReturn(200, $this->data, '编辑菜单成功');
    }

    /**
     * 删除菜单
     * @param roleId integer 角色ID
     * @return json
     * */
    public function remove(){
        (!isset($this->data['menuId']) || empty($this->data['menuId'])) && $this->apiReturn(201, '', '菜单ID非法');
        $menuId = $this->data['menuId'] + 0;

//        if(!$this->isAdmin){
//            $this->apiReturn(201, '', '您不是超级管理员');
//        }

        $menu   = Db::name('system_menu')->where(['menuId' => $menuId])->field('isDelete')->find();
        if(!$menu){
            $this->apiReturn(201, '', '该菜单不存在');
        }

        if($menu['isDelete'] == 0){
            $status = 1;//删除
            $msg    = '删除';
        }else{
            $status = 0;//恢复
            $msg    = '恢复';
        }

        $result = Db::name('system_menu')->where(['menuId' => $menuId])->update(['isDelete' => $status]);
        $result === false && $this->apiReturn(201, '', $msg . '失败');
        cache($this->cachekey, null);
        $this->apiReturn(200, '', $msg . '成功');
    }
    
    

}