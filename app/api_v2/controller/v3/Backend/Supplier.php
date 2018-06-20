<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-05-31
 * Time: 11:46
 */

namespace app\api_v2\controller\v3\Backend;

use think\Controller;
use think\Db;
class Supplier extends Admin
{

    /**
     * 首页
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = array('is_delete' => 0);
        if($this->isAdmin){
            if(isset($this->data['orgId']) && !empty($this->data['orgId'])){
                $orgId = $this->data['orgId'] + 0;
                $where['org_id'] = $orgId;
            }
        }else{
            $where['org_id'] = ['in', $this->orgIds];
        }

        if(isset($this->data['orgName']) && !empty($this->data['orgName'])){
            $orgName = htmlspecialchars(trim($this->data['orgName']));
            $where['org_name'] = ['like', '%' . $orgName . '%'];
        }

        if(isset($this->data['supplierName']) && !empty($this->data['supplierName'])){
            $supplierName = htmlspecialchars(trim($this->data['supplierName']));
            $where['supplier_name'] = ['like', '%' . $supplierName . '%'];
        }

        $field = 'supplier_id as supplierId,org_id as orgId,supplier_name as supplierName,phone_number as phoneNumber,remarks as remark,org_name as orgName';
        $count = Db::name('car_supplier')->where($where)->count();
        $data  = Db::name('car_supplier')->where($where)->field($field)->page($page, $rows)->order('creater_date desc')->select();
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }
    
    

}