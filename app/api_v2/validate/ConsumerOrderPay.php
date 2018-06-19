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


class ConsumerOrderPay extends Validate{

    protected $rule = [
        'orderId'                 => 'require|number',
        'amount'                  => 'require|number|gt:0',
        'payType'                 => 'require|number|in:1,2',
        'voucher'                 => 'url'
    ];

    protected $message = [
        'orderId.require'         => '订单ID非法',
        'orderId.number'          => '订单ID非法',
        'amount.require'          => '支付金额不能为空',
        'amount.number'           => '支付金额非法',
        'amount.gt'               => '支付金额必须为正数',
        'payType.require'         => '请选择支付方式',
        'payType.number'          => '支付方式非法',
        'payType.in'              => '支付方式非法',
        'voucher.url'             => '支付凭证地址非法'
    ];
}