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
//        'total_fee'               => 'require|number',
        'price'                   => 'require|number',
        'bareCarPrice'            => 'require|number',
        'purchase_tax'            => 'require|number',
        'license_plate_priace'    => 'require|number',
        'vehicle_vessel_tax'      => 'require|number',
        'insurance_price'         => 'require|number',
        'traffic_insurance_price' => 'require|number',
        'boutique_priace'         => 'require|number',
        'quality_assurance'       => 'require|number',
        'other'                  => 'require|number',
        'down_payment_rate'       => 'requireIf:type,2|number',
        'periods'                 => 'requireIf:type,2|number',
        'annual_rate'             => 'requireIf:type,2|number',
        'mode'                    => 'require|in:1,2',
        'change_price'            => 'require|number',
//        'monthly_supply'          => 'requireIf:type,2|number',
    ];

    protected $message = [
        'carId.require'         => '请选择车型',
        'carId.number'          => '车型ID非法',
        'type.require'          => '类型不能为空',
        'type.in'               => '类型非法',
        'total_fee.require'        => '请输入预计付费总额',
        'total_fee.number'         => '预计付费总额必须是数字',
        'price.require'        => '请输入指导价',
        'price.number'         => '指导价必须是数字',
        'bareCarPrice.require'         => '请输入裸车价',
        'bareCarPrice.number'         => '裸车价必须是数字',
        'purchase_tax.require'          => '请输入购置税',
        'purchase_tax.number'          => '购置税必须是数字',
        'license_plate_priace.require'           => '请输入上牌费用',
        'license_plate_priace.number'           => '上牌费用必须为数字',
        'vehicle_vessel_tax.require'      => '请输入车船税',
        'vehicle_vessel_tax.number'      => '车船税必须为数字',
        'insurance_price.require'      => '请输入商业保险费用',
        'insurance_price.number'      => '商业保险费用必须为数字',
        'traffic_insurance_price.require'      => '请输入交强险',
        'traffic_insurance_price.number'      => '交强险必须为数字',
        'boutique_priace.require'      => '请输入精品加装费',
        'boutique_priace.number'      => '精品加装费必须为数字',
        'quality_assurance.require'      => '请输入质保费用',
        'quality_assurance.number'      => '质保费用必须为数字',
        'other.require'                    => '请输入其它费用',
        'other.number'                     => '其它费用必须为数字',
        'down_payment_rate.requireIf'      => '请选择首付比率',
        'down_payment_rate.number'      => '首付比率必须为数字',
        'periods.requireIf'      => '请输入贷款期数',
        'periods.number'      => '贷款期数必须为数字',
        'annual_rate.requireIf'      => '请输入年利率',
        'annual_rate.number'      => '年利率必须为数字',
        'mode.require'      => '请选择金额变动模式',
        'mode.in'           => '金额变动模式非法',
        'change_price.require'      => '请输入变动金额',
        'change_price.number'           => '变动金额必须为数字',
        'monthly_supply.requireIf'      => '请输入每月还款金额',
        'monthly_supply.number'           => '每月还款金额必须为数字',
    ];



    public function checkPhone($phone){
        if(!checkPhone($phone)) {
            return false;
        }

        $count = Db::name('seller')->where(['s_phone' => $phone])->count();
        if($count){
            return '该手机号码已存在';
        }

        return true;
    }

    public function checkEmail($email){
        if(!checkEmail($email)){
            return false;
        }
        return true;
    }

    /*
     * 如果为企业主，则要求填写职位，如为非企业主，则要求填写单位
     * */
    function checkPosition($value, $rule, $data){
        dump($_REQUEST);die;
        //!isset($_POST['position']) || empty($_POST['position'])
        if(in_array(intval($_REQUEST['workSituation']), [1, 2]) && (empty($position) || !isset($position))) {
            return '请填写职位或单位';
        }
        return true;
    }

}