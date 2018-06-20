<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api_v2\controller\v3\Frontend;

use think\Controller;
use think\Db;
use think\Request;
class Home extends Controller {
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

    public function __construct(Request $request = null){
        $params = input('', '', 'htmlspecialchars,trim');

        (!isset($params['sessionId']) || empty($params['sessionId'])) && $this->apiReturn(201, 'SESSIONID不能为空');
        $sessionId  = trim($params['sessionId']);
        $user       = model('SystemUser')->getUserBySessionId($sessionId);
        !$user && $this->apiReturn(4002, '', '请重新登录');
        $this->isRole = $this->checkRole($user['usersId']);
        $this->userId = $user['usersId'];
        $this->orgId  = $user['orgId'];
        $this->user   = $user;

        //获取所有下级用户
        $lowerLevel = array();
        model('SystemUser')->getAllLowerLevel($this->userId, $lowerLevel);

        $this->orgIds  = [$this->orgId];
        $this->userIds = [$this->userId];
        $this->roleIds = $user['roleIds'] ? explode(',', $user['roleIds']) : [];

        if($lowerLevel){
            $this->orgIds  = array_unique(array_merge($this->orgIds, array_column($lowerLevel, 'orgId')));//下级用户所在门店的ID
            $this->userIds = array_unique(array_merge($this->userIds, array_column($lowerLevel, 'userId')));//下级用户ID
            $this->roleIds = explode(',', trim(implode(',', array_merge($this->roleIds, array_column($lowerLevel, 'roleIds'))), ','));
            $this->roleIds = implode(',', array_unique($this->roleIds));//该账号的所有角色权限（包括下级用户的）
        }

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
        $userRole = Db::name('system_role_user')->where(['userId' => $userId])->field('roleId')->find();
        if(!$userRole){
            return false;
        }

        if(!in_array($userRole['roleId'], $role)){
            return false;
        }

        return true;
    }

    /**
     * 按驼峰规则动态生成表字段(只支持单表)
     * @param $table string
     * @param $ignoreFields string/array  要过滤的字段
     * @return string
     * */
    public function createField($table, $ignoreFields = '', $returnArray = false){
        if($ignoreFields && is_string($ignoreFields)){
            $ignoreFields = explode(',', $ignoreFields);
        }
        $field = Db::name($table)->getTableFields();
        $field = array_diff($field, $ignoreFields ?: []);//删除$field中与$ignoreFields中重复的元素
        $field = array_values($field);//重排key
        foreach($field as $key => $value){
            $value = explode('_', $value);
            foreach($value as $k => &$val){
                if($k == 0){
                    continue;
                }
                $val = ucfirst($val);
            }
            $fields[] = implode('', $value);
        }
        unset($value, $val);//删除
        foreach($field as $key => &$value){
            $value .= ' AS ' . $fields[$key];
        }
        unset($fields, $value, $key);
        return !$returnArray ? implode(',', $field) : $field;
    }

}