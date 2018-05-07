<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api\validate;
use think\Validate;


class AddUser extends Validate{

    protected $rule = [
        'b_username'           => 'require',
        'b_phone'              => 'require|unique:user|number|checkPhone',
        'b_wechat'             => 'require|unique:user',
        'b_email'              => 'require|unique:user|checkEmail',
        'b_industry'           => 'require',
        'b_address'            => 'require',
        'b_channelId'          => 'require',
        'b_carStyleId'         => 'require',
        'b_buyerCarStyle'      => 'requireIf:b_carStyleId,1',
        'b_standard'           => 'require',
        'b_information'        => 'require',
        'b_purpose'            => 'require',
        'b_buyerCarStyleId'    => 'require',
        'b_purchaseTime'       => 'require',
        'b_background'         => 'require',
        'b_institution'        => 'require',
        'b_downpaymentsRate'   => 'require',
        'b_periods'            => 'require',
        'b_maritalStatus'      => 'require',
        'b_isNative'           => 'require',
        'b_hasEstate'          => 'require',
        'b_estateAddr'         => 'require',
        'b_workAddr'           => 'require',
        'b_assets'             => 'require',
        'b_workSituation'      => 'require',
        'b_position'           => 'checkPosition',
        'b_incomeSituation'    => 'require',
        'b_bankCredit'         => 'require',
        'b_creditRemark'       => 'require',
        'b_register'           => 'require',
        'b_carUser'            => 'require',
        'b_registerAddr'       => 'require',
        'b_qualification'      => 'require',
        'b_downpayments'       => 'require',
        'b_monthlySupply'      => 'require',
        'b_hasDrivingLicense'  => 'require',
        'b_licenseHandling'    => 'requireIf:hasDrivingLicense,2',
    ];

    protected $message = [
        'b_username.require'      => '请输入姓名',
        'b_phone.require'         => '请输入手机号码',
        'b_phone.unique'          => '该手机号码已存在',
        'b_phone.number'          => '该手机号码必须是数字串',
        'b_phone.mobile'          => '该手机格式非法',
        'b_wechat.require'        => '请输入微信号',
        'b_wechat.unique'         => '该微信号已存在',
        'b_email.require'         => '请输入邮箱地址',
        'b_email.unique'          => '邮箱格式非法',
        'b_email.email'           => '邮箱格式非法',
        'b_industry.require'      => '请输入从事行业',
        'b_address.require'       => '请输入居住地址',
        'b_channelId.require'     => '请选择渠道',
        'b_carStyleId.require'    => '请选择车型',
        'b_buyerCarStyle'         => '请输入所有车型',
        'b_standard.require'      => '请选择购车标准',
        'b_information.require'      => '请选择对汽车的了解情况',
        'b_purpose.require'          => '请选择车辆用途',
        'b_buyerCarStyleId.require'  => '请选择现在驾驶车型',
        'b_purchaseTime.require'     => '请选择预计购车时间',
        'b_background.require'       => '请选择陪同人员的背景资料',
        'b_institution.require'      => '请选择按揭金融机构',
        'b_downpaymentsRate.require' => '请选择首付比例',
        'b_periods.require'          => '请选择按揭分期期数',
        'b_maritalStatus.require'    => '请选择身份证婚姻状态',
        'b_isNative.require'         => '请选择家人是否居住本地',
        'b_hasEstate.require'        => '请选择是否有房产',
        'b_estateAddr.require'       => '请填写房产所在地',
        'b_workAddr.require'         => '请填写顾客所在地',
        'b_assets.require'           => '请选择其它资产',
        'b_workSituation.require'    => '请选择工作情况',
        'b_position'                 => '请填写职位或单位',
        'b_incomeSituation.require'  => '请选择收入或负债情况',
        'b_bankCredit.require'       => '请选择银行信用',
        'b_creditRemark.require'     => '请填写银行信用备注',
        'b_register.require'         => '请选择车辆登记人/上牌人',
        'b_carUser.require'          => '请选择车辆使用人',
        'b_registerAddr.require'     => '请选择车辆使用人',
        'b_qualification.require'    => '请选择身份证所在地购车资格',
        'b_downpayments.require'     => '请选择首付金额',
        'b_monthlySupply.require'    => '请选择月供金额',
        'b_hasDrivingLicense.require'=> '请选择是否有驾驶证',
        'b_licenseHandling'          => '请选择是否有驾驶证',
    ];

    protected $scene = [
        'create' => ['b_username', 'b_phone'],
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
    function checkPosition($value, $rule, $data){
        dump($_REQUEST);die;
        //!isset($_POST['position']) || empty($_POST['position'])
        if(in_array(intval($_REQUEST['workSituation']), [1, 2]) && (empty($position) || !isset($position))) {
            return '请填写职位或单位';
        }
        return true;
    }

}