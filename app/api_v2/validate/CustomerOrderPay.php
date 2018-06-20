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


class CustomerOrderPay extends Validate{

    protected $rule = [
        'customerOrderId'        => 'require|number',
        'amount'                 => 'require|number|egt:0',
        'payMethod'              => 'require|number|in:5,6,14',
        'voucher'                => 'url'
    ];

    protected $message = [
        'customerOrderId.require' => '订单ID非法',
        'customerOrderId.number'  => '订单ID非法',
        'amount.require'          => '支付金额不能为空',
        'amount.number'           => '支付金额非法',
        'amount.egt'              => '支付金额必须为正数',
        'amount.checkAmount'      => '已付清定金或尾款',
        'payMethod.require'       => '请选择支付方式',
        'payMethod.number'        => '支付方式非法',
        'payMethod.in'            => '支付方式非法',
        'voucher.url'             => '支付凭证地址非法'
    ];

    public function checkAmount($value){
        if($value === 0){
            return false;
        }
        return true;
    }
}