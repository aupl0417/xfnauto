<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1;

use app\api\model\CustomerOrder;
use app\api\model\ShopGoodsCarsActivity;
use think\Controller;
use think\Db;
class Login extends Base
{

    /**
     * 登录
     * */
    public function index(){
        $result = $this->validate($this->data, 'Login');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $user = model('SystemUser')->getUserByPhone($this->data['phoneNumber']);
        !$user && $this->apiReturn(201, '', '用户不存在');

        if(strtoupper(md5($this->data['password'])) != $user['password']){
            $this->apiReturn(201, '', '密码错误');
        }

        session_id(md5(serialize($this->data) . microtime(true)));
        $info = [
            'sessionId'    => session_id(),
            'loginTime'    => date('Y-m-d H:i:s')
        ];
        if(isset($this->data['nickName']) && !empty($this->data['nickName'])){
            $info['nikeName'] = htmlspecialchars(trim($this->data['nickName']));
        }
        if(isset($this->data['headPortrait']) && !empty($this->data['headPortrait'])){
            $info['headPortrait'] = htmlspecialchars(trim($this->data['headPortrait']));
        }

        $result = Db::name('system_user')->where(['usersId' => $user['usersId']])->update($info);
        $result === false && $this->apiReturn(201, '', '登录失败');

        $role  = model('RoleUser')->getRoleByUserId($user['usersId'], $user['orgId']);
        $roleName = '';
        if($role){
            $roleIds = array_unique(array_column($role, 'roleId'));
            $roleName= model('Role')->getRoleAll(['roleId' => ['in', $roleIds], 'isDelete' => 0]);
            $roleName= $roleName ? implode(',', array_column($roleName, 'roleName')) : '';
        }

        $org = model('Organization')->getOrganizationByOrgId($user['orgId'], 'orgtype as orgType,orgLevel');
        $data = [
            'headPortrait'  => (isset($this->data['headPortrait']) && !empty($this->data['headPortrait'])) ? $info['headPortrait'] : $user['headPortrait'],
            'nikeName'      => $user['nikeName'],
            'orgCode'       => $user['orgCode'],
            'orgLevel'      => $org['orgLevel'],
            'orgName'       => $user['orgName'],
            'phoneNumber'   => $user['phoneNumber'],
            'sessionId'     => session_id(),
            'userName'      => $user['userName'] ?: '',
            'roleName'      => $roleName,
            'realName'      => $user['realName'],
            'weixinQrImage' => $user['weixin_qr_image'],
        ];

        $this->apiReturn(200, $data, '登录成功');
    }



}