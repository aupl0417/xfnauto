<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-06-04
 * Time: 15:03
 */

namespace app\api\controller\v1\Backend;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Controller;
use think\Db;
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

}