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


class EditStock extends Validate{

    protected $rule = [
        'unit_price'            => 'require|number|checkFee',
        'freight'               => 'require|number|checkFee',
        'othersFee'             => 'require|number|checkFee',
    ];

    protected $message = [
        'unit_price.require'    => '请输入采购单价',
        'unit_price.number'     => '采购单价必须为数字',
        'freight.require'       => '请输入每车辆车的运费',
        'freight.number'        => '运费必须为数字',
        'othersFee.require'     => '请输入其它费用',
        'othersFee.number'      => '其它费用必须为数字',
    ];

    public function checkFee($value){
        if(!is_numeric($value)) {
            return false;
        }

        if($value < 0){
            return '不能输入小于0的数据';
        }

        return true;
    }

}