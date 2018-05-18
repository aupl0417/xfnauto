<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\Backend;

use think\Controller;
use think\Db;
class ConsumerOrder extends Admin
{

    /**
     * 首页
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;


        $where = [
            'co.state'   => ['not in', [-1, 37]],
            'co.is_del'  => 0
        ];

        if(isset($this->data['keywords'])&& !empty($this->data['keywords'])){
            $keywords= htmlspecialchars(trim($this->data['keywords']));
            $orderUser    = Db::name('consumer_order_user')->where(['user_name' => ['like', '%' . $keywords . '%']])->field('order_id')->select();
            $orderUserIds = array();
            if($orderUser){
                $orderUserIds = array_column($orderUser, 'order_id');
            }
            $join = [
                ['consumer_order_car oc', 'oc.stock_car_id=sc.stock_car_id', 'left'],
                ['consumer_order_info oi', 'oi.id=oc.info_id', 'left'],
            ];
            $stockCar = Db::name('stock_car sc')->where(['frame_number' => ['like', '%' . $keywords . '%']])->join($join)->field('oi.order_id')->select();
            $stockCarIds = array();
            if($stockCar){
                $stockCarIds = array_column($stockCar, 'order_id');

            }
            $orderIds = array_merge($orderUserIds, $stockCarIds);
            $where['co.id'] = ['in', $orderIds];
        }

        if(isset($this->data['orgName'])&& !empty($this->data['orgName'])){
            $orgName = htmlspecialchars(trim($this->data['orgName']));
            $where['shortName'] = ['like', '%' . $orgName . '%'];
        }

//        $where['co.creator_id'] = $this->userId;

        $startTime = isset($this->data['startDate']) && !empty($this->data['startDate']) ? $this->data['startDate'] : '';
        $endTime   = isset($this->data['endDate'])   && !empty($this->data['endDate'])   ? $this->data['endDate'] : '';
        if($startTime && !$endTime){
            $where['co.create_time'] = ['egt', $startTime];
        }elseif(!$startTime && $endTime){
            $where['co.create_time'] = ['elt', $endTime];
        }else{
            $now = date('Y-m-d H:i:s');
            if($startTime == $endTime && $endTime <= $now){
                $where['co.create_time'] = ['egt', $startTime];
            }elseif($startTime == $endTime && $endTime >= $now){
                $where['co.create_time'] = ['elt', $startTime];
            }else{
                if($startTime > $endTime){
                    $where['co.create_time'] = ['between', [$endTime, $startTime]];
                }else{
                    $where['co.create_time'] = ['between', [$startTime, $endTime]];
                }
            }
        }

        $data = model('ConsumerOrder')->getOrderListAll($where, $page, $rows);
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => count($data)]);
    }

    /**
     * 资源订单详情
     * */
    public function consumerDetail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['id'] + 0;
        $data    = model('ConsumerOrder')->getOrderDetailByOrderId($orderId);
        $this->apiReturn(200, $data);
    }

}