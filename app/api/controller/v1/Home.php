<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1;

use think\Controller;
use think\Db;
use think\Request;
class Home extends Controller {
    protected $data = array();
    protected $userId;
    protected $user;
    protected $isRole;
    protected $orgId;
    protected $app  = array(
        '1' => 'D8OZLSE2NEDC0FR4XTGBKHY67UJZ8IK9', //ios
        '2' => 'DFHGKZLSE2NFDEHGFHHR4XTGBKHY67EJZ8IK9', //安卓
    );

    public function __construct(Request $request = null){
        $params = input('', '', 'htmlspecialchars,trim');
        //验证签名串是否存在或是否为空
//        (!isset($params['token']) || empty($params['token'])) && $this->apiReturn(201, '签名不能为空');
//        (!isset($params['appId']) || empty($params['appId'])) && $this->apiReturn(201, 'appId不能为空');
//        (!isset($params['deviceId']) || empty($params['deviceId'])) && $this->apiReturn(201, '设备ID不能为空');
        (!isset($params['sessionId']) || empty($params['sessionId'])) && $this->apiReturn(201, 'SESSIONID不能为空');

//        $token = $params['token'];
//        unset($params['token']);

        //验证签名
//        if(!$this->tokenValidate($params, $token)){
//            $this->apiReturn(201, $this->newSign);//暂时显示这个签名，用于测试时
//            $this->apiReturn(201, '签名错误');
//        }
        if(request()->action() != 'quotationDetail'){
            $sessionId  = trim($params['sessionId']);
            $user       = model('SystemUser')->getUserBySessionId($sessionId);
            !$user && $this->apiReturn(4002, '', '请重新登录');
        }


//        $controller = request()->controller();
//        $controller = explode('.', $controller);

        $this->isRole = $this->checkRole($user['usersId']);
        $this->userId = $user['usersId'];
        $this->orgId  = $user['orgId'];
        $this->user   = $user;
        $this->data   = $params;
    }

    public function tokenValidate($data, $token){
        if(empty($data) || !is_array($data) || !($data['appId'] > 0) || !isset($this->app[$data['appId']])){
            return false;
        }

        $secretKey = $this->app[$data['appId']];
        ksort($data);
        $queryString = $this->http_build_string($data);

        if(md5($queryString.$secretKey) != $token){
            $this->newSign = md5($queryString.$secretKey);
            return false;
        }

        return true;
    }

    /**
     * 跟系统的http_build_str()功能相同，但不用安装pecl_http扩展
     *
     * @param array $array      需要组合的数组
     * @param string $separator 连接符
     *
     * @return string               连接后的字符串
     * eg: 举例说明
     */
    function http_build_string ( $array, $separator = '&' ) {
        $string = '';
        foreach ( $array as $key => $val ) {
            $string .= "{$key}={$val}{$separator}";
        }
        //去掉最后一个连接符
        return substr( $string, 0, strlen( $string ) - strlen( $separator ) );
    }

    /*
	* 	返回数据到客户端
	*	@param $code type : int		状态码
	*   @param $info type : string  状态信息
	*	@param $data type : mixed	要返回的数据
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

    protected function ajaxReturn($data, $type='', $json_option=0) {
        if(empty($type)) $type  =   'JSON';
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[config('VAR_JSONP_HANDLER')]) ? $_GET[config('VAR_JSONP_HANDLER')] : config('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data,$json_option).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return',$data);
        }
    }

    protected function checkAuth($userId, $orgId){
        $user = model('SystemUser')->getUserById($userId, 'usersId,orgId');
        !$user && $this->apiReturn(201, '', '系统用户不存在');
        $user['orgId'] != $orgId && $this->apiReturn(201, '', '组织ID不正确');
        return true;
    }

    /**
     * 检查用户是否具有如下身份
     * 总经理 IT部 仓管 仓管主管 B端客户总监 销售经理 资源部经理 物流主管
     * @param $userId 用户ID
     * @return bool
     * */
    public function checkRole($userId){
        $role = [14,15,42,43,49,48,33,45];
        $userRole = Db::name('system_user_role')->where(['userId' => $userId])->field('roleId')->find();
        if(!$userRole){
            return false;
        }

        if(!in_array($userRole['roleId'], $role)){
            return false;
        }

        return true;
    }

}