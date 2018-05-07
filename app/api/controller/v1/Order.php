<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class Order extends Home
{

    /*
     * 资源订单
     * */
    public function consumerList(){
        (!isset($this->data['orgId']) || empty($this->data['orgId'])) && $this->apiReturn(201, '', '组织ID不能为空');

        $orgId  = $this->data['orgId'] + 0;
        if(isset($this->data['keyword'])&& !empty($this->data['keyword'])){
            $keyword= htmlspecialchars(trim($this->data['keyword']));
            $field = !preg_match('/^DG\d+/', $keyword) ? 'cars_name' : 'order_code';
            $where[$field] = ['like', '%' . $keyword . '%'];
        }

        if(isset($this->data['state'])&& !empty($this->data['state'])){
            $where['co.state'] = $this->data['state'] + 0;
        }

        $where['co.create_time']    = ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]];
        $where['co.creator_id']     = $this->userId;
        $where['co.org_id']         = $orgId;

        $data = model('ConsumerOrder')->getOrderList($where);
        $this->apiReturn(200, $data);
    }

    public function customerList(){
        (!isset($this->data['orgId']) || empty($this->data['orgId'])) && $this->apiReturn(201, '', '组织ID不能为空');

        $orgId  = $this->data['orgId'] + 0;
        if(isset($this->data['keyword'])&& !empty($this->data['keyword'])){
            $keyword= htmlspecialchars(trim($this->data['keyword']));
            $field = !preg_match('/^DD\d+/', $keyword) ? 'cars_name' : 'customer_order_code';
            $where[$field] = ['like', '%' . $keyword . '%'];
        }

        if(isset($this->data['state'])&& !empty($this->data['state'])){
            $state = $this->data['state'] + 0;
            $where['customer_order_state'] = $state;
            if($state == 6){
                $where['customer_order_state'] = ['in', [7, 9, 11, 13]];
            }
        }

        $where['create_date']    = ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]];
        $where['system_user_id'] = $this->userId;
        $where['org_id']         = $orgId;

        $data = model('CustomerOrder')->getOrderList($where);
        $this->apiReturn(200, $data);

    }

}