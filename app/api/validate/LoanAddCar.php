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


class LoanAddCar extends Validate{

    protected $rule = [
        'carId'         => 'require|number',
        'colorId'       => 'require|number',
        'guidancePrice' => 'require|number',
        'price'         => 'require|number',
        'downPayments'  => 'require|number',
        'number'        => 'require|number',
        'amount'        => 'require|number',
    ];

    protected $message = [
        'carId.require'         => '请选择车型',
        'carId.number'          => '车型参数非法',
        'colorId.require'       => '请选择颜色',
        'colorId.number'        => '颜色参数非法',
        'guidancePrice.require' => '官方指导价不能为空',
        'guidancePrice.number'  => '官方指导价参数非法',
        'price.require'         => '实际购车额不能为空',
        'price.number'          => '实际购车额格式非法',
        'downPayments.require'  => '首付比率不能为空',
        'downPayments.number'   => '首付比率格式非法',
        'number.require'        => '垫资数量不能为空',
        'number.number'         => '垫资数量参数非法',
        'amount.require'        => '垫资总额不能为空',
        'amount.number'         => '垫资总额格式非法',
    ];
}