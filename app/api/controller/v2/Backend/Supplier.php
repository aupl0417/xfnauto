<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-05-31
 * Time: 11:46
 */

namespace app\api\controller\v2\Backend;

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

    /**
     * 供应商添加或编辑
     * */
    public function create(){
        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'Supplier');
        $result !== true && $this->apiReturn(201, '', $result);

        $data = [
            'supplier_name' => $this->data['supplierName'],
            'phone_number'  => $this->data['phoneNumber'],
            'remarks'       => (isset($this->data['remark']) && !empty($this->data['remark'])) ? htmlspecialchars(trim($this->data['remark'])) : '',
        ];

        if(isset($this->data['id']) && !empty($this->data['id'])){
            $id = $this->data['id'] + 0;
            unset($this->data['id']);
            $result = Db::name('car_supplier')->where(['supplier_id' => $id])->update($data);
            $result === false && $this->apiReturn(201, '', '编辑失败');
        }else{
            $data['org_id']        = $this->orgId;
            $data['org_name']      = $this->user['orgName'];
            $data['creater_date']  = date('Y-m-d H:i:s');
            $result = Db::name('car_supplier')->insert($data);
            !$result && $this->apiReturn(201, '', '添加失败');
        }

        $this->apiReturn(200, '', '提交成功');
    }

    /**
     * 删除供应商
     * */
    public function remove(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        $result = Db::name('car_supplier')->where(['supplier_id' => $id])->update(['is_delete' => 1]);
        $result === false && $this->apiReturn(201, '', '删除失败');
        $this->apiReturn(200, '', '删除成功');
    }
    

}