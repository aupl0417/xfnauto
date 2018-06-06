<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api\validate;
use think\Db;
use think\Validate;


class Login extends Validate{

    protected $rule = [
        'phoneNumber'        => 'require|checkPhone',
        'password'           => 'require',
        'to'                 => 'url'
    ];

    protected $message = [
        'phoneNumber.require'    => '请输入手机号码',
        'phoneNumber.checkPhone' => '手机号码格式非法',
        'password.require'       => '密码不能为空',
        'to.url'                 => '地址非法',
    ];

    public function checkPhone($phone){
        return true;
        if(!checkPhone($phone)) {
            return false;
        }
        return true;
    }
}