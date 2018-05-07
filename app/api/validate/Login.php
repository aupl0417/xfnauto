<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api\validate;
use think\Validate;


class Login extends Validate{

    protected $rule = [
        'user_login'   => 'require',
        'user_pass'    => 'require',
    ];

    protected $message = [
        'user_login'   => '请输入用户名',
        'user_pass'    => '请输入密码',
    ];

}