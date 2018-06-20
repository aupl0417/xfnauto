<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-06-04
 * Time: 15:03
 */

namespace app\api\controller\v2\Backend;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Controller;
use think\Db;
use think\Exception;

class CustomerOrder extends Admin
{

    /**
     * 客户订单管理首页
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = ['is_delete' => 0];
        if(!$this->isAdmin){
            $where['org_id'] = ['in', $this->orgIds];
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $field    = checkPhone($keywords) ? 'customer_phone_number' : 'customer_order_code';
            $where[$field] = ['like', '%' . $keywords . '%'];
        }

        if(isset($this->data['state']) && !empty($this->data['state'])){
            $state = $this->data['state'] + 0;
            $where['customer_order_state'] = $state;
        }

        $data = model('CustomerOrder')->getOrderListAll($where, $page, $rows);
        $this->apiReturn(200, $data);
    }

    /**
     * 客户订单详情
     * */
    public function detail(){
        (!isset($this->data['customerOrderId']) || empty($this->data['customerOrderId'])) && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['customerOrderId'] + 0;
        $field   = $this->createField('customer_order', 'is_delete', false, 'co') . ',sc.frame_number as frameNumber,org.address as orgAddress,org.telephone as telePhone,cu.address,ca.price as guidingPrice';

        $data    = model('CustomerOrder')->getOrderDetailByOrderId($orderId, $field);
        $this->apiReturn(200, $data);
    }

    public function pay(){
        (!isset($this->data['customerOrderId']) || empty($this->data['customerOrderId'])) && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['customerOrderId'] + 0;
        $field   = 'customer_order_id as id,customer_order_state as state,customer_order_code,amount,loan,deposit_price as depositPrice,payment_way';
        $data    = Db::name('customer_order')->where(['customer_order_id' => $orderId, 'is_delete' => 0])->field($field)->find();
        !$data && $this->apiReturn(201, '', '订单不存在或已删除');
        !in_array(intval($data['state']), [1, 5, 7, 9, 11, 13, 15], true) && $this->apiReturn(201, '', '非待收订金或待收尾款状态');

        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'CustomerOrderPay');
        $result !== true && $this->apiReturn(201, '', $result);

        if($data){
            $data['totalAmount']       = $data['amount'] - $data['loan'];
            $data['payAmount']         = $data['totalAmount'] - Db::name('customer_order_in_pay')->where(['customer_order_id' => $orderId, 'order_in_pay_state' => 1])->sum('amount');
        }

        $amount = $data['state'] == 1 ? $data['depositPrice'] : $data['payAmount'];

        if($this->data['amount'] != $amount){
            $this->apiReturn(201, '', '支付金额不一致' . $amount);
        }

        try{
            Db::startTrans();
            $state = $data['state'] == 1 ? ($data['payment_way'] == 1 ? 5 : 3) : $data['state'];
            $dataInfo   = [
                'amount' => floatval($this->data['amount']),
                'create_date' => date('Y-m-d H:i:s'),
                'customer_order_id' => $orderId,
                'customer_order_code' => $data['customer_order_code'],
                'pay_method' => $this->data['payMethod'],
                'remarks'    => (isset($this->data['remarks']) && !empty($this->data['remarks'])) ? htmlspecialchars(trim($this->data['remarks'])) : '',
                'customer_order_state' => $state,
                'order_in_pay_state'   => 1,
                'pay_date' => date('Y-m-d H:i:s'),
                'order_in_pay_code' => makeOrder('PD', 4),
                'system_user_id' => $this->userId,
                'system_user_name' => $this->user['realName']
            ];

            $result = Db::name('customer_order_in_pay')->insert($dataInfo);
            if(!$result){
                throw new Exception('提交失败');
            }
            $result = Db::name('customer_order')->where(['customer_order_id' => $orderId, 'is_delete' => 0, 'customer_order_state' => 1])->update(['customer_order_state' => $state]);
            if($result === false){
                throw new Exception('更新订单状态失败');
            }
            Db::commit();
            $this->apiReturn(200, '', '操作成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '操作失败');
        }
    }

}