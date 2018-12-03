<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-05-31
 * Time: 11:46
 */

namespace app\api\controller\v3\Backend;

use think\Controller;
use think\Db;
use think\Exception;

class ShopLoanVerify extends Admin
{

    /**
     * 垫资资格审核列表
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = ['si_state' => 1];
        if(isset($this->data['state']) && $this->data['state'] != ''){
            $state = $this->data['state'] + 0;
            $where['s_state'] = $state;
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $where['si_shopName'] = ['like', '%' . $keywords . '%'];
        }

        $data = model('ShopLoan')->getShopLoanListForPage($where, $page, $rows);
        $this->apiReturn(200, $data);
    }

    /**
     * 审核详情
     * */
    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        $data = model('ShopLoan')->getShopLoanByIdAll($id);
        $this->apiReturn(200, $data);
    }

    /**
     * 审核
     * */
    public function verify(){
        (!isset($this->data['id'])    || empty($this->data['id']))    && $this->apiReturn(201, '', '参数非法');
        (!isset($this->data['state']) || empty($this->data['state']) || !in_array($this->data['state'], [1, 2])) && $this->apiReturn(201, '', '状态参数非法');

        $id     = $this->data['id'] + 0;
        $state  = $this->data['state'] + 0;

        $reason    = '';
        $materials = '';
        if($state == 2){
            (!isset($this->data['reason']) || empty($this->data['reason'])) && $this->apiReturn(201, '', '请输入拒绝原因');
            $reason = htmlspecialchars(trim($this->data['reason']));
        }else{
            (!isset($this->data['materials']) || empty($this->data['materials'])) && $this->apiReturn(201, '', '请上传店铺认证资料');
            $materials = htmlspecialchars(trim($this->data['materials']));
            $materialsArr = explode(',', $materials);
            foreach($materialsArr as $value){
                !filter_var($value, FILTER_VALIDATE_URL) && $this->apiReturn(201, '', '店铺认证资料地址非法');
            }
        }

        $info = model('ShopLoan')->getShopLoanByIdAll($id);
        !$info && $this->apiReturn(201, '', '数据不存在');
//        $info['state'] == 1 && $this->apiReturn(201, '', '订单已处理，审核已通过');
//        $info['state'] == 2 && $this->apiReturn(201, '', '订单已处理，审核已拒绝');

        $data = [
            's_state'            => $state,
            's_reason'           => $reason,
            's_materials'        => $materials,
            's_system_user_id'   => $this->userId,
            's_system_user_name' => $this->user['realName'],
            's_updateTime'       => time()
        ];

        $result = Db::name('shop_loan')->where(['s_id' => $id])->update($data);
        $result === false && $this->apiReturn(201, '', '操作失败');
        $this->apiReturn(200, '', '操作成功');
    }




}