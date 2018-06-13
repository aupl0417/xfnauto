<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/12 18:42
 * Time: 13:39
 */
namespace app\api\validate;
use think\Db;
use think\Validate;


class Stock extends Validate{

    protected $rule = [
        'name'                => 'require',
//        'orgId'               => 'require|number',
    ];

    protected $message = [
        'name.require'        => '请输入仓库名称',
//        'orgId.require'       => '请选择门店',
//        'orgId.number'        => '门店参数非法',
    ];

}