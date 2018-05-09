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


class AddQuotation extends Validate{

    protected $rule = [
        'carId'                   => 'require|number',
        'type'                    => 'require|in:1,2',
        'total_fee'               => 'require|checkFee',
        'price'                   => 'require|checkFee',
        'bareCarPrice'            => 'require|checkFee',
        'purchase_tax'            => 'require|checkFee',
        'license_plate_priace'    => 'require|checkFee',
        'vehicle_vessel_tax'      => 'require|checkFee',
        'insurance_price'         => 'require|checkFee',
        'traffic_insurance_price' => 'require|checkFee',
        'boutique_priace'         => 'require|checkFee',
        'quality_assurance'       => 'require|checkFee',
        'other'                  => 'require|checkFee',
        'down_payment_rate'       => 'requireIf:type,2|checkFee',
        'periods'                 => 'requireIf:type,2|checkFee',
        'annual_rate'             => 'requireIf:type,2|checkFee',
        'mode'                    => 'require|in:1,2',
        'change_price'            => 'require|checkFee',
        'monthly_supply'          => 'requireIf:type,2|checkFee',
    ];

    protected $message = [
        'carId.require'         => '请选择车型',
        'carId.number'          => '车型ID非法',
        'type.require'          => '类型不能为空',
        'type.in'               => '类型非法',
        'total_fee.require'        => '请输入预计付费总额',
        'total_fee.checkFee'         => '预计付费总额必须是数字',
        'price.require'        => '请输入指导价',
        'price.checkFee'         => '指导价必须是数字',
        'bareCarPrice.require'         => '请输入裸车价',
        'bareCarPrice.checkFee'         => '裸车价必须是数字',
        'purchase_tax.require'          => '请输入购置税',
        'purchase_tax.checkFee'          => '购置税必须是数字',
        'license_plate_priace.require'           => '请输入上牌费用',
        'license_plate_priace.checkFee'           => '上牌费用必须为数字',
        'vehicle_vessel_tax.require'      => '请输入车船税',
        'vehicle_vessel_tax.checkFee'      => '车船税必须为数字',
        'insurance_price.require'      => '请输入商业保险费用',
        'insurance_price.checkFee'      => '商业保险费用必须为数字',
        'traffic_insurance_price.require'      => '请输入交强险',
        'traffic_insurance_price.checkFee'      => '交强险必须为数字',
        'boutique_priace.require'      => '请输入精品加装费',
        'boutique_priace.checkFee'      => '精品加装费必须为数字',
        'quality_assurance.require'      => '请输入质保费用',
        'quality_assurance.checkFee'      => '质保费用必须为数字',
        'other.require'                    => '请输入其它费用',
        'other.checkFee'                     => '其它费用必须为数字',
        'down_payment_rate.requireIf'      => '请选择首付比率',
        'down_payment_rate.checkFee'      => '首付比率必须为数字',
        'periods.requireIf'      => '请输入贷款期数',
        'periods.checkFee'      => '贷款期数必须为数字',
        'annual_rate.requireIf'      => '请输入年利率',
        'annual_rate.checkFee'      => '年利率必须为数字',
        'mode.require'      => '请选择金额变动模式',
        'mode.in'           => '金额变动模式非法',
        'change_price.require'      => '请输入变动金额',
        'change_price.checkFee'           => '变动金额必须为数字',
        'monthly_supply.requireIf'      => '请输入每月还款金额',
        'monthly_supply.checkFee'           => '每月还款金额必须为数字',
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