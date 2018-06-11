<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\Shop;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class User extends Base
{
    /**
     * 账户资料
     * */
    public function profile(){
        $field = 'head_portrait as headPortrait,real_name as realName,phone_number as phoneNumber,s_state as loanState,si_state as shopState';
        $user  = model('ShopUser')->getUserByIdAll($this->userId, $field);
        !$user && $this->apiReturn(201, '', '用户不存在');
        $user['loanStateName'] = is_null($user['loanState']) ? '未认证' : ($user['loanState'] == 0 ? '审核中' : ($user['loanState'] == 1 ? '已认证' : '未通过'));
        $user['shopStateName'] = is_null($user['shopState']) ? '未认证' : ($user['shopState'] == 0 ? '审核中' : ($user['shopState'] == 1 ? '已认证' : '未通过'));
        $this->apiReturn(200, $user);
    }

}