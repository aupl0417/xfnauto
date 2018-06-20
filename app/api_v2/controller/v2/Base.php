<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api_v2\controller\v2;

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
    protected $app  = array(
        '1' => 'D8OZLSE2NEDC0FR4XTGBKHY67UJZ8IK9', //ios
        '2' => 'DFHGKZLSE2NFDEHGFHHR4XTGBKHY67EJZ8IK9', //安卓
    );

    public function __construct(){
        $params = input('', '', 'htmlspecialchars,trim');
        if(in_array(strtolower(request()->action()), ['contract', 'createimage'])){
            (!isset($params['sessionId']) || empty($params['sessionId'])) && $this->apiReturn(201, 'SESSIONID不能为空');
            $sessionId  = trim($params['sessionId']);
            $user       = model('SystemUser')->getUserBySessionId($sessionId);
            !$user && $this->apiReturn(4002, '', '请重新登录');
            $this->userId = $user['usersId'];
            $this->orgId  = $user['orgId'];
            $this->user   = $user;
        }
        $this->data = $params;
    }

    public function tokenValidate($data, $token){
        if(empty($data) || !is_array($data) || !($data['appId'] > 0) || !isset($this->app[$data['appId']])){
            return false;
        }

        $secretKey = $this->app[$data['appId']];
        ksort($data);
        $queryString = $this->http_build_string($data);
//        dump(md5("{$queryString}&{$secretKey}"));die;

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

}