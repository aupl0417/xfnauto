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
class Stock extends Admin
{

    /**
     * 仓库列表首页
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = array('is_delete' => 0);
        if(!$this->isAdmin){
            $where['org_id'] = ['in', $this->orgIds];
        }else{
            $sql = 'SELECT orgId,shortName as orgName,orgLevel FROM system_organization WHERE  status = 1  AND (orgId = ' . $this->orgId .' OR parentId = ' . $this->orgId . ')  AND orgLevel < 3 ';
            $org = Db::name('system_organization')->query($sql);
            $where['org_id'] = ['in', array_column($org, 'orgId')];
        }

        if(isset($this->data['orgName']) && !empty($this->data['orgName'])){
            $orgName = htmlspecialchars(trim($this->data['orgName']));
            $where['org_name'] = ['like', '%' . $orgName . '%'];
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $where['warehouse_name'] = ['like', '%' . $keywords . '%'];
        }

        $field = 'warehouse_id as id,org_id as orgId,warehouse_name as warehouseName,org_name as orgName,create_date as createDate,remark';
        $count = Db::name('system_warehouse')->where($where)->count();
        $data  = Db::name('system_warehouse')->where($where)->field($field)->page($page, $rows)->order('warehouse_id desc')->select();
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }


    /**
     * 添加仓库
     * */
    public function create(){
        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'Stock');
        $result !== true && $this->apiReturn(201, '', $result);

        $org = model('Organization')->getOrganizationByOrgId($this->orgId, 'shortName,orgCode');
        !$org && $this->apiReturn(201, '', '门店不存在');

        $data = [
            'warehouse_name' => htmlspecialchars(trim($this->data['name'])),
            'org_id'         => $this->orgId,
            'org_name'       => $org['shortName'],
            'org_code'       => $org['orgCode'],
            'warehouse_type' => 1,
            'create_date'    => date('Y-m-d H:i:s'),
            'remark'         => (isset($this->data['remark']) && !empty($this->data['remark'])) ? htmlspecialchars(trim($this->data['remark'])) : ''
        ];
        $result = Db::name('system_warehouse')->insert($data);
        !$result && $this->apiReturn(201, '', '添加失败');
        $this->apiReturn(200, '', '添加成功');
    }


    /**
     * 编辑仓库
     * */
    public function edit(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        unset($this->data['sessionId'], $this->data['id']);
        $result = $this->validate($this->data, 'Stock');
        $result !== true && $this->apiReturn(201, '', $result);

        $org = model('Organization')->getOrganizationByOrgId($this->orgId, 'shortName,orgCode');
        !$org && $this->apiReturn(201, '', '门店不存在');

        $data = [
            'warehouse_name' => htmlspecialchars(trim($this->data['name'])),
            'org_id'         => $this->orgId,
            'org_name'       => $org['shortName'],
            'org_code'       => $org['orgCode'],
            'remark'         => (isset($this->data['remark']) && !empty($this->data['remark'])) ? htmlspecialchars(trim($this->data['remark'])) : ''
        ];
        $result = Db::name('system_warehouse')->where(['warehouse_id' => $id, 'is_delete' => 0])->update($data);
        $result === false && $this->apiReturn(201, '', '编辑失败');
        $this->apiReturn(200, '', '编辑成功');
    }

    /**
     * 删除仓库
     * */
    public function remove(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        $result = Db::name('system_warehouse')->where(['warehouse_id' => $id])->update(['is_delete' => 1]);
        $result === false && $this->apiReturn(201, '', '删除失败');
        $this->apiReturn(200, '', '删除成功');
    }


}