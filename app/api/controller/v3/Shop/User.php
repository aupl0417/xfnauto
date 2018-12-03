<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v3\Shop;

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
        $user['realName']      = $user['realName'] ?: '';
        $user['loanStateName'] = is_null($user['loanState']) ? '去认证' : ($user['loanState'] == 0 ? '审核中' : ($user['loanState'] == 1 ? '已认证' : '未通过'));
        $user['shopStateName'] = is_null($user['shopState']) ? '去认证' : ($user['shopState'] == 0 ? '审核中' : ($user['shopState'] == 1 ? '已认证' : '未通过'));
        $user['loanState']     = !is_null($user['loanState']) ? $user['loanState'] : -1;
        $user['shopState']     = !is_null($user['shopState']) ? $user['shopState'] : -1;
        $user['rate']          = model('ShopLoan')->getRate();
        $this->apiReturn(200, $user);
    }

    public function shopInfo(){
        $field = $this->getField('shop_info', 'si_operatorId,updateTime,si_shopId', false, '', true);
        $data = Db::name('shop_info')->where(['si_userId' => $this->userId])->field($field)->order('si_id desc')->find();
        !$data && $this->apiReturn(200);
        $data['idCard']  = explode(',', $data['idCard']);
        $data['license'] = explode(',', $data['license']);
        $data['image']   = explode(',', $data['image']);
        $state = ['认证中', '已通过', '已拒绝'];
        $data['stateName']    = $state[$data['state']];
        $data['reason']       = $data['reason'] ?: '';
        $data['idCardPicOn']  = $data['idCardPicOn'] ?: '';
        $data['idCardPicOff'] = $data['idCardPicOff'] ?: '';
        $data['createTime']   = date('Y-m-d H:i:s', $data['createTime']);
        $this->apiReturn(200, $data);
    }

    public function loanInfo(){
        $field = $this->getField('shop_info', 'si_operatorId,updateTime,si_shopId', false, '', true);
        $info  = Db::name('shop_info')->where(['si_userId' => $this->userId])->field($field)->order('si_id desc')->find();
        !$info && $this->apiReturn(200);

        $field = $this->getField('shop_loan', 's_system_user_id,s_system_user_name,s_updateTime', false, '', true);
        $data  = Db::name('shop_loan')->where(['s_userId' => $this->userId])->field($field)->order('s_id desc')->find();
        !$data && $this->apiReturn(200);
        $data['materials']  = $data['materials']  ? explode(',', $data['materials']) : [];
        $data['createTime'] = $data['createTime'] ? date('Y-m-d H:i:s', $data['createTime']) : '';
        $this->apiReturn(200, $data);
    }

}