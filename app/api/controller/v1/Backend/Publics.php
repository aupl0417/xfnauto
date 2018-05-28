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
use think\Request;

class Publics extends Admin
{

    /**
     * 检查JAVA接口权限
     * @return json
     * */
    public function checkJavaApiAuth(){
        (!isset($this->data['menuId']) || empty($this->data['menuId'])) && $this->apiReturn(201, '', '菜单ID非法');
        $menuId = $this->data['menuId'] + 0;
        if(!$this->checkUserAuth($this->userId, $menuId)){
            $this->apiReturn(201, '', $this->error);
        }else{
            $this->apiReturn(200);
        }
    }
    

}