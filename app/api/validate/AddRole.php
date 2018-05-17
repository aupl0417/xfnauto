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


class AddRole extends Validate{

    protected $rule = [
        'orgId'                => 'require|number',
        'roleName'             => 'require',
//        'remark'               => 'require',
    ];

    protected $message = [
        'orgId.require'         => '请选择所属机构',
        'orgId.number'          => '所属机构ID非法',
        'roleName.require'       => '请输入角色名称'
    ];
}