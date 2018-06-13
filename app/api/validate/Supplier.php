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


class Supplier extends Validate{

    protected $rule = [
        'supplierName'              => 'require',
        'phoneNumber'               => 'require|number',
    ];

    protected $message = [
        'supplierName.require'      => '请输入供应商名称',
        'phoneNumber.require'       => '请输入手机号码',
        'phoneNumber.number'        => '手机号码格式非法',
    ];

}