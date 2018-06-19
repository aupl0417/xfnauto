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


class ConsumerOrderPay extends Validate{

    protected $rule = [
        'orderId'                 => 'require|number',
        'amount'                  => 'require|number|egt:0|checkAmount',
        'payType'                 => 'require|number|in:1,2',
        'voucher'                 => 'url'
    ];

    protected $message = [
        'orderId.require'         => '订单ID非法',
        'orderId.number'          => '订单ID非法',
        'amount.require'          => '支付金额不能为空',
        'amount.number'           => '支付金额非法',
        'amount.egt'              => '支付金额格式非法',
        'amount.checkAmount'      => '已付清定金或尾款',
        'payType.require'         => '请选择支付方式',
        'payType.number'          => '支付方式非法',
        'payType.in'              => '支付方式非法',
        'voucher.url'             => '支付凭证地址非法'
    ];

    public function checkAmount($value){
        if($value === 0){
            return false;
        }
        return true;
    }
}