<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api\validate;
use think\Validate;


class CarInfoCollect extends Validate{

    protected $rule = [
        'o_cid'      => 'require|number',
        'o_uid'         => 'require|number',
        'o_changeCarId' => 'requireIf:o_type,1|number',
        'o_carcolor'    => 'require|number',
        'o_trim'         => 'require|number',
        'o_buystyle'      => 'require|number',
        'o_registerAddr'       => 'require',
        'o_deliveryTime'      => 'require|in:1,2,3,4',
        'o_choice'      => 'require|in:0,1',
        'o_boutique'      => 'require|in:1,2,3',
        'o_quotation'      => 'require|in:1,2,3',
        'o_quotationRemark'      => 'requireIf:o_quotation,1',
        'o_remark'      => 'require',
        'o_type'      => 'require|number',
        'o_sellerId'      => 'require|number'
    ];

    protected $message = [
        'o_cid.require'      => '请选择车型',
        'o_cid.number'         => '车型ID格式非法',
        'o_uid.require'         => '请添加用户信息',
        'o_uid.number'          => '用户ID非法',
        'o_changeCarId.requireIf' => '请选择客户车型',
        'o_changeCarId.number'    => '选择客户车型ID非法',
        'o_carcolor.require'      => '请选择车身颜色',
        'o_carcolor.number'        => '车身颜色ID必须为数字',
        'o_trim.require'         => '请选择内饰颜色',
        'o_trim.number'         => '内饰颜色ID必须为数字',
        'o_buystyle.require'         => '请选择购车方式',
        'o_buystyle.number'         => '购车方式ID必须为数字',
        'o_registerAddr.require'      => '请输入客户上牌地',
        'o_deliveryTime.require'      => '请选择4S店交车时间',
        'o_deliveryTime.in'      => '4S店交车时间ID非法',
        'o_choice.require'      => '请选择期货/现货',
        'o_choice.in'      => '期货/现货ID非法',
        'o_boutique.require'      => '请选择精品加装',
        'o_boutique.in'      => '精品加装ID非法',
        'o_quotation.require'      => '请选择是否有4S店/汽贸店的报价单',
        'o_quotation.in'           => '是否有4S店/汽贸店的报价单ID非法',
        'o_quotationRemark.requireIf'      => '请添加报价单',
        'o_remark.require'      => '请填写备注',
        'o_type.require'      => '类型不能为空',
        'o_type.number'      => '类型ID非法',
        'o_sellerId.require'      => '销售员ID不能为空',
        'o_sellerId.number'      => '销售员ID非法',
    ];

    protected $scene = [
        'addSeveral' => ['o_cid', 'o_quotation', 'o_quotationRemark', 'o_remark', 'o_type', 'o_sellerId'],
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