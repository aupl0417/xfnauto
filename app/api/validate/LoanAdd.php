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


class LoanAdd extends Validate{

    protected $rule = [
        'orgId'           => 'require|number',
        'period'          => 'require|number',
        'amount'          => 'require|number',
        'fee'             => 'require|number',
        'rate'            => 'require|number',
        'carsInfo'        => 'require|checkData'
    ];

    protected $message = [
        'orgId.require'       => '请选择垫资商铺',
        'orgId.number'        => '垫资商铺参数非法',
        'period.require'      => '垫资期限不能为空',
        'period.number'       => '垫资期限参数非法',
        'amount.require'      => '垫资总额不能为空',
        'amount.number'       => '垫资总额参数非法',
        'fee.require'         => '手续费不能为空',
        'fee.number'          => '手续费格式非法',
        'rate.require'        => '手续费率不能为空',
        'rate.number'         => '手续费率格式非法',
        'carsInfo.require'    => '请添加车型',
        'carsInfo.checkData'  => '添加车型数据格式非法',
    ];

    public function checkData($value){
        if(!is_array($value)){
            return false;
        }

        return true;
    }
}