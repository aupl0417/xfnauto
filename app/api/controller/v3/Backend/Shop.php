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
class Shop extends Admin
{

    /**
     * 店铺认证列表
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = [];
        if(isset($this->data['state']) && $this->data['state'] != ''){
            $state = $this->data['state'] + 0;
            $where['si_state'] = $state;
        }

        if(isset($this->data['orgName']) && !empty($this->data['orgName'])){
            $orgName = htmlspecialchars(trim($this->data['orgName']));
            $where['si_shopName'] = ['like', '%' . $orgName . '%'];
        }
        
        $data = model('ShopInfo')->getShopInfoForPage($where, $page, $rows);
        $this->apiReturn(200, $data);
    }

    /**
     * 审核详情
     * */
    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        $data = model('ShopInfo')->findOne(['si_id' => $id]);
        $this->apiReturn(200, $data ?: []);
    }

    /**
     * 审核
     * */
    public function verify(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');
        (!isset($this->data['state']) || empty($this->data['state'])) && $this->apiReturn(201, '', '参数非法');

        $id    = $this->data['id'] + 0;
        $state = $this->data['state'] + 0;

        $reason = '';
        if($state == 2){
            (!isset($this->data['reason']) || empty($this->data['reason'])) && $this->apiReturn(201, '', '请输入拒绝原因');
            $reason = htmlspecialchars(trim($this->data['reason']));
        }

        $data = model('ShopInfo')->findOne(['si_id' => $id]);
        !$data && $this->apiReturn(201, '', '数据不存在');
        $data['state'] == 1 && $this->apiReturn(201, '', '审核已经通过，不能重复操作');
        $data['state'] == 2 && $this->apiReturn(201, '', '已拒绝该店铺通过审核');

        $data = [
            'si_operatorId' => $this->userId,
            'si_updateTime' => time(),
            'si_reason'     => $reason,
            'si_state'      => $state
        ];

        $result = Db::name('shop_info')->where(['si_id' => $id])->update($data);
        $result === false && $this->apiReturn(201, '', '操作失败');
        $this->apiReturn(200);
    }


}