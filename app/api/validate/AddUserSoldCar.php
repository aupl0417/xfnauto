<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api\validate;
use think\Validate;


class AddUserSoldCar extends Validate{

    protected $rule = [
        'b_secondCarId'      => 'require|number',
        'b_offerType'      => 'require|number',
        'b_username'      => 'require',
        'b_phone'         => 'require|unique:user|number|checkPhone',
        'b_quotation'      => 'require|in:1,2,3',
        'b_quotationRemark'      => 'requireIf:o_quotation,1',
    ];

    protected $message = [
        'b_secondCarId.require'      => '请选择车型',
        'b_secondCarId.number'      => '车型必须是数字',
        'b_offerType.require'      => '请选择报价方式',
        'b_offerType.number'      => '报价方式必须是数字',
        'b_username.require'      => '请输入客户姓名',
        'b_phone.require'         => '请输入手机号码',
        'b_phone.unique'          => '该手机号码已存在',
        'b_phone.number'          => '该手机号码必须是数字串',
        'b_phone.checkPhone'      => '该手机格式非法',
        'b_quotation.require'      => '请选择是否有4S店/汽贸店的报价单',
        'b_quotation.in'           => '是否有4S店/汽贸店的报价单ID非法',
        'b_quotationRemark.requireIf'      => '请添加报价单',
    ];


    public function checkPhone($phone){
        if(!checkPhone($phone)) {
            return false;
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
    public function checkPosition($position){
        //!isset($_POST['position']) || empty($_POST['position'])
        if(in_array(intval($_POST['workSituation']), [1, 2])) {
            if (empty($position) || !isset($position)) {
                return '请填写职位或单位';
            }
        }
        return true;
    }

}