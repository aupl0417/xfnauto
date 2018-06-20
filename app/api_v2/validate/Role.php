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


class Role extends Validate{

    protected $rule = [
        'id'                   => 'require|number',
        'orgId'                => 'require|number',
        'roleName'             => 'require',
//        'remark'               => 'require',
    ];

    protected $message = [
        'id.require'        => '角色ID不能为空',
        'id.number'         => '角色ID非法',
        'orgId.require'         => '请选择所属机构',
        'orgId.number'          => '所属机构ID非法',
        'roleName.require'      => '请输入角色名称'
    ];

    protected $scene = [
        'add'   =>  ['orgId','roleName'],
        'edit'  =>  ['id', 'orgId', 'roleName'],
    ];
}