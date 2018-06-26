<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v3\Shop;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class Index extends Base
{
    public function index(){

    }

    /**
     * 店铺认证
     * */
    public function verify(){
        if($this->user['user_type'] == 1){
            $this->apiReturn(201, '', '您不是商家');
        }

        if(Db::name('shop_info')->where(['si_shopName' => $this->data['shopName'], 'si_userId' => $this->userId, 'si_state' => ['neq', 2]])->count()){
            $this->apiReturn(201, '', '您已提交申请，请勿重复提交');
        }

        if(Db::name('shop_info')->where(['si_phone' => $this->data['phone'], 'si_state' => ['neq', 2]])->count()){
            $this->apiReturn(201, '', '手机号码已存在');
        }

        if(Db::name('shop_info')->where(['si_shopId' => $this->orgId, 'si_state' => ['neq', 2]])->count()){
            $this->apiReturn(201, '', '该店铺已提交过认证，请耐心等待！');
        }

        $result = $this->validate($this->data, 'Shop');
        $result !== true && $this->apiReturn(201, '', $result);

//        if($this->data['shopName'] !== $this->user['org_name']){
//            $this->apiReturn(201, '', '您输入的店铺名称有误');
//        }

        $data = [];
        foreach($this->data as $key => $value){
            $data['si_' . $key] = $value;
        }

        $data['si_createTime'] = time();
        $data['si_shopId']     = $this->user['org_id'];
        $data['si_userId']     = $this->userId;
        $result = Db::name('shop_info')->insert($data);
        !$result && $this->apiReturn(201, '', '提交失败');

        $this->apiReturn(200, '', '提交成功');
    }

    public function updatefee(){
//        $field = 'sa_id as id,sa_state as state,sa_amount as amount,sa_totalAmount as totalAmount,sa_rate as rate,sa_fee as fee,sa_feeTotal as totalFee,sa_period as period,sa_voucherTime as voucherTime';
//        $data  = Db::name('shop_loan_apply')->where(['sa_state' => ['in', [3, 4]], 'sa_isDel' => 0, 'sa_type' => 1])->field($field)->order('sa_id desc')->select();
//        dump($data);die;
//        if($data){
//            foreach($data as $key => &$value){
//
//            }
//        }
        $sql = 'SELECT sai_id as id,sai_saId as orderId,sai_price as price,sai_downPayments as downPayments,sai_amount as amount,sai_number as number,sai_fee as fee,sa_rate as rate FROM `shop_loan_apply_info` LEFT JOIN `shop_loan_apply` ON `sa_id`=`sai_saId` WHERE `sai_state` = 0 AND `sai_isDel` = 0';
        dump($data);
    }

}