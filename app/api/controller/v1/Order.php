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

    /**
     * 资源订单
     * @return json
     * */
    public function consumerList(){
        if(isset($this->data['keyword'])&& !empty($this->data['keyword'])){
            $keyword= htmlspecialchars(trim($this->data['keyword']));
            $field = !preg_match('/^DG\d+/', $keyword) ? 'cars_name' : 'order_code';
            $where[$field] = ['like', '%' . $keyword . '%'];
        }
        $where = [
            'co.state'   => ['not in', [-1, 37]],
            'co.is_del'  => 0
        ];

        if(isset($this->data['state'])&& !empty($this->data['state'])){
            $where['co.state'] = $this->data['state'] + 0;
        }

        if(!$this->isRole){
            $where['co.creator_id'] = $this->userId;
            $userIds = model('SystemUser')->getUserByOrgId($this->orgId, 'usersId');
            if($userIds){
                $userIds = array_column($userIds, 'usersId');
                $where['co.creator_id'] = ['in', $userIds];
            }
        }
        $where['co.create_time']    = ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]];
//        $where['co.org_id']         = $this->orgId;

        $data = model('ConsumerOrder')->getOrderList($where);
        $this->apiReturn(200, $data);
    }

    /**
     * 用户订单列表
     * @return json
     * */
    public function customerList(){
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

        if(!$this->isRole){
            $where['system_user_id'] = $this->userId;
        }
        $where['create_date']    = ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]];
        $where['org_id']         = $this->orgId;
        $where['is_delete']      = 0;

        $data = model('CustomerOrder')->getOrderList($where);
        $this->apiReturn(200, $data);
    }

    /**
     * 费用统计
     * @return json
     * */
    public function statistics(){
        $type = isset($this->data['type']) && !empty($this->data['type']) ? htmlspecialchars(trim($this->data['type'])) : 'customer';
        $mode = isset($this->data['mode']) && !empty($this->data['mode']) ? htmlspecialchars(trim($this->data['mode'])) : 'insurance';
        !in_array($type, ['customer', 'consumer']) && $this->apiReturn(201, '', '类型非法');

        if($type == 'customer'){
            !in_array($mode, ['insurance', 'mortgage', 'boutique', 'license']) && $this->apiReturn(201, '', '统计类型非法');
            $model = 'CustomerOrder';
        }else{
            !in_array($mode, ['traffic', 'commercial']) && $this->apiReturn(201, '', '统计类型非法');
            $model = 'ConsumerOrder';
        }
        $data = model($model)->orderFeeList($mode, $this->userId, $this->orgId, $this->isRole);
//        echo model($model)->getLastSql();die;
        $this->apiReturn(200, $data);
    }

}