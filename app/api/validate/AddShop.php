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


class AddShop extends Validate{

    protected $rule = [
        'shopname'     => 'require',
        'username'     => 'require',
        'phone'        => 'require|checkPhone',
        'province'     => 'require|number',
        'city'         => 'require|number',
        'county'       => 'require|number',
        'address'      => 'require',
        'certificate'  => 'require|url',
    ];

    protected $message = [
        'shopname.require'      => '请输入门店名称',
        'username.require'         => '请输入老板姓名',
        'phone.require'          => '请输入手机号码',
        'phone.checkPhone'          => '该手机格式非法',
        'province.require'        => '请选择省份',
        'city.require'         => '请选择市',
        'county.require'         => '请选择区县',
        'address.require'          => '请输入详细地址',
        'certificate.require'           => '请上传营业执照',
        'certificate.url'      => '营业执照地址格式非法'
    ];

    protected $scene = [
        'create' => ['b_username', 'b_phone'],
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