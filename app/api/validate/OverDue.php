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


class OverDue extends Validate{

    protected $rule = [
        'orderId'        => 'require|number',
        'downpayment'    => 'require|number|gt:0',
        'rate'           => 'require|number|gt:0',
        'period'         => 'require|number|gt:0',
        'voucher'        => 'require|url'
    ];

    protected $message = [
        'orderId.require'        => '垫资单ID不能为空',
        'orderId.number'         => '垫资单ID非法',
        'downpayment.require'    => '首付比例不能为空',
        'downpayment.number'     => '首付比例格式非法',
        'downpayment.gt'         => '首付比例格式非法',
        'rate.require'           => '手续费率不能为空',
        'rate.number'            => '手续费率格式非法',
        'rate.gt'                => '手续费率格式非法',
        'period.require'         => '延长天数不能为空',
        'period.number'          => '延长天数格式非法',
        'period.gt'              => '延长天数格式非法',
        'voucher.require'        => '请上传还款凭证',
        'voucher.url'            => '还款凭证非法',
    ];

    protected $scene = [
        'add'   =>  ['orderId','downpayment', 'rate', 'period'],
    ];
}