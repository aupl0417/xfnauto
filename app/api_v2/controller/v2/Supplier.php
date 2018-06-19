<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api_v2\controller\v2;

use app\api_v2\model\CustomerOrder;
use think\Controller;
use think\Db;
class Supplier extends Home
{

    /**
     * 供应商下拉列表
     * */
    public function index(){
        $data  = Db::name('car_supplier')->where(['org_id' => $this->orgId, 'is_delete' => 0])->field('supplier_id as id,supplier_name as supplierName')->select();
        $this->apiReturn(200, $data ?: []);
    }

}