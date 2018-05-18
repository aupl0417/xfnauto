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


class SystemUser extends Validate{

    protected $rule = [
        'phoneNumber'           => 'require|checkPhone|unique:system_user',
        'orgId'                 => 'require|number',
        'realName'              => 'require',
        'roleIds'               => 'require',
        'parentIds'             => 'require',
        'sex'                   => 'require|in:0,1',
        'birthday'              => 'checkTime',
        'cardNo'                => 'checkIdCard',
        'entryTime'             => 'checkTime',
        'basePay'               => 'checkNumber',
        'headPortrait'          => 'checkUrl',
    ];

    protected $message = [
        'phoneNumber.require'        => '手机号不能为空',
        'phoneNumber.checkPhone'     => '手机号格式非法',
        'phoneNumber.unique'         => '手机号已存在',
        'orgId.require'              => '组织ID非法',
        'orgId.number'               => '组织ID非法',
        'roleIds.require'            => '请选择角色',
        'parentIds.require'          => '请选择上级主管',
        'agentGender.require'        => '请选择性别',
        'sex.in'                     => '性别格式非法',
        'birthday.checkTime'         => '生日格式非法',
        'cardNo.checkIdCard'         => '身份证号码格式非法',
        'entryTime.checkTime'        => '入职时间格式非法',
        'basePay.checkNumber'        => '基本工资格式非法',
        'headPortrait.checkUrl'      => '头像地址格式非法',
    ];

    public function checkPhone($phone){
        if(!checkPhone($phone)) {
            return false;
        }
        return true;
    }

    public function checkUrl($value){
        if(isset($_REQUEST['headPortrait']) && !empty($_REQUEST['headPortrait'])){
            $headPortrait = htmlspecialchars(trim($_REQUEST['headPortrait']));
            if(!filter_var($headPortrait, FILTER_VALIDATE_URL)) {
                return false;
            }
            return true;
        }
    }

    public function checkTime($value){
        if(!checkDateIsValid($value)){
            return false;
        }
        return true;
    }

    public function checkIdCard($value){
        return true;
    }

    public function checkNumber($value){
        if(!is_numeric($value)){
            return '基本工资必须是数字';
        }
        if($value < 0){
            return '基本工资不能小于0';
        }
        return true;
    }
}