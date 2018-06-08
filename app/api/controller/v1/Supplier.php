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
class Supplier extends Home
{


    /**
     * 物流单列表
     * */
    public function index(){
        (!isset($this->data['orgId']) || empty($this->data['orgId'])) && $this->apiReturn(201, '', '参数非法');

        $orgId = $this->data['orgId'] + 0;
        $data  = Db::name('car_supplier')->where(['org_id' => $orgId, 'is_delete' => 0])->field('supplier_id as id,supplier_name as supplierName')->select();
        $this->apiReturn(200, $data ?: []);
    }

}