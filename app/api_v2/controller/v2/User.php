<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api_v2\controller\v2;

use app\api_v2\model\CustomerOrder;
use think\Controller;
use think\Db;
class User extends Home
{

    /**
     * 用户资料添加或修改
     * */
    public function create(){
        $thumb = '';
        if(isset($this->data['thumb']) && !empty($this->data['thumb'])){
            $thumb = htmlspecialchars($this->data['thumb']);
            unset($this->data['thumb']);
        }
        if(!isset($this->data['headPortrait']) || empty($this->data['headPortrait'])){
            $this->data['headPortrait'] = $thumb;
        }

        if(isset($this->data['customerUsersId']) && !empty($this->data['customerUsersId'])){
            $userId = $this->data['customerUsersId'] + 0;
            unset($this->data['sessionId'], $this->data['customerUsersId']);

            $result = Db::name('customer_customerusers')->where(['customerUsersId' => $userId])->update($this->data);
            $result === false && $this->apiReturn(201, '', '保存失败');
        }else{
            $result = Db::name('customer_customerusers')->insert($this->data);
            !$result && $this->apiReturn(201, '', '添加失败');
        }
        $this->apiReturn(200, '', '保存成功');
    }

}