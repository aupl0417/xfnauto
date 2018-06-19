<?php

/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:47
 */
namespace app\api_v2\model;

use think\Db;
use think\Model;

class Menu extends Model
{

    protected $table = 'system_menu';
    protected $formatTree = array();

    public function getMenuById($menuId){
        if(!$menuId || !is_numeric($menuId)){
            return false;
        }
        $menu = Db::name($this->table)->where(['menuId' => $menuId])->field('menuId as id,parentId,seq,iconUrl,menuName,src,levelNum')->find();
        if(!$menu){
            return false;
        }
        return $menu;
    }

    public function getMenuBySrc($url){
        if(!$url || !is_string($url)){
            return false;
        }
        $menu = Db::name($this->table)->where(['src' => $url, 'isDelete' => 0])->field('menuId as id,parentId,seq,iconUrl,menuName,src,levelNum')->find();
        if(!$menu){
            return false;
        }
        return $menu;
    }

    public function getMenuAll($where, $field = '*', $order = 'menuId asc'){
        $data = Db::name($this->table)->where($where)->field($field)->order($order)->select();
        return $data;
    }

    public function arrangeMenu($menu, &$data, $pid = 0, $level = 0){
        $data = self::getParentMenu($menu);
//        dump($menu);die;
        foreach($menu as $key => $value){
            foreach($data as $k => $val){
                if($value['parentId'] == $val['id']){
                    $data[$k]['children'][] = $value;
                }
            }
        }

        return $data;
    }

    public function getParentMenu($menu){
        $data = array();
        foreach($menu as $key => $value){
            if($value['parentId'] == 0){
                $data[] = $value;
            }
        }
        return $data;
    }

    public function getMenuTree($ids = '', $parentId = 0){

        $field = 'menuId as id,parentId,menuName as name,src';
        $where = ["parentId" => $parentId, 'isDelete' => 0];
        if(!empty($ids)){
            $where['menuId'] = ['in', $ids];
        }

        $menus = $this->where($where)->field($field)->order(["menuId" => "ASC"])->select();
        if($menus){
            foreach ($menus as $key => $menu) {
                $children = $this->getMenuTree($ids, $menu['id']);
                if(!empty($children)) {
                    $menus[$key]['children'] = $children;
                }
            }
        }

        return $menus;
    }

    private function _toFormatTree($list, $level=0, $title = 'name') {
        foreach($list as $key=>$val){
            $val[$title]  = $level== 0 ? $val[$title] . '　' :  $val[$title] . '';
            if(!array_key_exists('children', $val)){
                array_push($this->formatTree, $val);
            }else{
                $tmp_ary = $val['children'];
                unset($val['children']);
                array_push($this->formatTree, $val);
                $this->_toFormatTree($tmp_ary, $level + 1, $title); //进行下一层递归
            }
        }

        return;
    }

    private function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] = &$list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }else{
                        $tree[] = &$list[$key];
                    }
                }
            }
        }

        return $tree;
    }

    public function getTree($list){
        $list   = $this->list_to_tree($list, 'id', 'parentId', 'children', 0);
        return $list;
    }



}