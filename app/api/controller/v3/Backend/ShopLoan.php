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
class ShopLoan extends Admin
{

    /**
     * 垫资列表
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = ['sa_isDel' => 0];

        if(isset($this->data['state']) && $this->data['state'] != ''){
            $state = $this->data['state'] + 0;
            $where['sa_state'] = $state;
        }

        if(isset($this->data['orgName']) && !empty($this->data['orgName'])){
            $orgName = htmlspecialchars(trim($this->data['orgName']));
            $where['sa_orgName'] = ['like', '%' . $orgName . '%'];
        }

        if(isset($this->data['orderId']) && !empty($this->data['orderId'])){
            $orderId = htmlspecialchars(trim($this->data['orderId']));
            $where['sa_orderId'] = ['like', '%' . $orderId . '%'];
        }

        $data = model('ShopLoanApply')->getDataByPage($where, $page, $rows);
        $this->apiReturn(200, $data);
    }

    /**
     * 审核详情
     * */
    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        $data = model('ShopLoanApply')->getShopLoanApplyByIdAll($id);
        if($data){
            $field = $this->getField('shop_info', '', false, '', true);
            $data['info'] = model('ShopInfo')->getShopInfoByUserId($data['userId'], $field);
        }

        $this->apiReturn(200, $data);
    }

    /**
     * 审核
     * */
    public function verify(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');
        (!isset($this->data['state']) || empty($this->data['state'])) && $this->apiReturn(201, '', '参数非法');

        $id     = $this->data['id'] + 0;
        $state  = $this->data['state'] + 0;
        $reason = '';
        if($state == 1){
            (!isset($this->data['reason']) || empty($this->data['reason'])) && $this->apiReturn(201, '', '参数非法');

            $reason = htmlspecialchars(trim($this->data['reason']));
        }

        $info = model('ShopInfo')->getById($id, 'sa_state');
        !$info && $this->apiReturn(201, '', '数据不存在');
        $info['state'] != 0 && $this->apiReturn(201, '', '订单已处理');

        $data = [
            'sa_state' => $state,
            'reason'   => $reason
        ];

        $result = Db::name('shop_loan_apply')->where(['sa_id' => $id])->update($data);
        $result === false && $this->apiReturn(201, '', '操作失败');
        $this->apiReturn(200, '', '操作成功');
    }

    public function setRate(){
        $cacheKey = md5('set_loan_rate');
        (!isset($this->data['rate']) || empty($this->data['rate'])) && $this->apiReturn(201, '', '费率不能为空');

        !is_numeric($this->data['rate']) && $this->apiReturn(201, '', '参数非法');

        $rate = floatval($this->data['rate']);
        cache($cacheKey, $rate);
        $this->apiReturn(200, ['rate' => cache($cacheKey)]);
    }




}