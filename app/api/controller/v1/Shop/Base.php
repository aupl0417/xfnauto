<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\Shop;

use think\Controller;
use think\Db;
class Base extends Controller {
    protected $data = array();
    protected $userId;
    protected $orgIds  = array();
    protected $userIds = array();
    protected $roleIds = array();
    protected $user;
    protected $isRole;
    protected $orgId;

    public function __construct(){
        $params = input('', '', 'htmlspecialchars,trim');

        (!isset($params['sessionId']) || empty($params['sessionId'])) && $this->apiReturn(201, 'SESSIONID不能为空');
        $sessionId  = trim($params['sessionId']);
        $user       = model('ShopUser')->getUserBySessionId($sessionId);
        !$user && $this->apiReturn(4002, '', '请重新登录');
        $this->userId = $user['shop_user_id'];
        $this->orgId  = $user['org_id'];
        $this->user   = $user;
        unset($params['sessionId']);
        $this->data = $params;
    }

    /**
	* 	返回数据到客户端
	*	@param $code  int		状态码
	*   @param $msg   string  状态信息
	*	@param $data  mixed	要返回的数据
	*	return json
	*/
    public function apiReturn($code, $data = null, $msg = ''){
        header('Content-Type:application/json; charset=utf-8');//返回JSON数据格式到客户端 包含状态信息

        $jsonData = array(
            'resultCode' => $code,
            'message'    => $msg ?: ($code == 200 ? '请求成功' : '请求失败'),
            'data'       => $data ? $data : null
        );

        exit(json_encode($jsonData));
    }

}