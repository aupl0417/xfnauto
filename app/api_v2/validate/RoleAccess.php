<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api_v2\validate;
use think\Db;
use think\Validate;


class RoleAccess extends Validate{

    protected $rule = [
        'roleId'                => 'require|number',
        'lists'                 => 'require',
    ];

    protected $message = [
        'roleId.require'        => '角色ID不能为空',
        'roleId.number'         => '角色ID非法',
        'lists.require'         => '请选择权限',
    ];
}