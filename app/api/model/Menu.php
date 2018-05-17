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

class Menu extends Model
{

    protected $table = 'system_menu';

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
        $menu = Db::name($this->table)->where(['src' => $url])->field('menuId as id,parentId,seq,iconUrl,menuName,src,levelNum')->find();
        if(!$menu){
            return false;
        }
        return $menu;
    }
    
    

}