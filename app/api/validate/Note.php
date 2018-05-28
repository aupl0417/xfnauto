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


class Note extends Validate{

    protected $rule = [
        'username'              => 'require|length:1,50',
        'email'                 => 'require|email',
        'content'               => 'require|length:4,25',
    ];

    protected $message = [
        'username.require'      => '请输入用户名称',
        'username.length'       => '请输入不超过50个字符长度的用户名称',
        'email.require'         => '请输入邮箱',
        'email.email'           => '邮箱地址非法',
        'content.require'       => '请输入留言内容',
        'content.length'        => '请确保留言内容长度保持在6到255个字符之间',
    ];
}