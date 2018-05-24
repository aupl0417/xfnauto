<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\Backend;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Controller;
use think\Db;
class Organization extends Admin
{

    /**
     * 门店列表
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $org   = model('Organization')->getOrganizationByOrgId($this->orgId, 'orgLevel');
        $where = ['ao.status' => ['in', [1, 2, 3]]];
        $whereOr = [];
        if(!$this->isAdmin){
            $where['ao.orgId'] = $this->orgId;
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $where['ao.shortName'] = ['like', '%' . $keywords . '%'];
        }

        $data = model('Organization')->getOrgList($where, $whereOr, $page, $rows, 'ao.orgId desc');
        $this->apiReturn(200, $data);
    }

    public function create(){
        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'Organization');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $this->data['orgCode']     = getRandomString(6);
        $this->data['create_date'] = date('Y-m-d H:i:s');
        $this->data['status']      = 1;
        $this->data['orgLevel']    = 2;
        $this->data['orgtype']     = $this->data['orgType'];
        $this->data['provinceName']= isset($this->data['province']) ? htmlspecialchars(trim($this->data['province'])) : '';
        $this->data['cityName']    = isset($this->data['city']) ? htmlspecialchars(trim($this->data['city'])) : '';
        $this->data['areaName']    = isset($this->data['area']) ? htmlspecialchars(trim($this->data['area'])) : '';
        unset($this->data['orgType'], $this->data['province'], $this->data['city'], $this->data['area']);
        $result = Db::name('system_organization')->insert($this->data);
        !$result && $this->apiReturn(201, '', '添加失败');
        $this->apiReturn(200, '', '添加成功');
    }

    public function edit(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        unset($this->data['sessionId'], $this->data['id']);
        $result = $this->validate($this->data, 'Organization');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

//        if(Db::name('system_organization')->where(['orgId' => ['neq', $id], 'shortName' => $this->data['shortName']])->count()){
//            $this->apiReturn(201, '', '该门店名称已存在');
//        }
        $this->data['orgtype']     = $this->data['orgType'];
        $this->data['provinceName']= isset($this->data['province']) ? htmlspecialchars(trim($this->data['province'])) : '';
        $this->data['cityName']    = isset($this->data['city']) ? htmlspecialchars(trim($this->data['city'])) : '';
        $this->data['areaName']    = isset($this->data['area']) ? htmlspecialchars(trim($this->data['area'])) : '';
        unset($this->data['orgType'], $this->data['province'], $this->data['city'], $this->data['area']);
        $result = Db::name('system_organization')->where(['orgId' => $id])->update($this->data);
        $result === false && $this->apiReturn(201, '', '编辑失败');
        $this->apiReturn(200, '', '编辑成功');
    }

    public function getOrg(){
        $where = ['status' => 1];
        if(!$this->isAdmin){
            $where['orgId'] = $this->orgId;
        }
        $data = model('Organization')->getOrgAll($where, 'orgId,shortName as orgName');
        $this->apiReturn(200, $data);
    }

    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        unset($this->data['sessionId'], $this->data['id']);

        $field = 'orgId as id,parentId,orgCode,shortName,provinceId,cityId,areaId,address,orgtype as orgType,orgLevel,remark,status,longitude,latitude,imageurl,bankAccount,bankName,
                 openingBranch,nameOfAccount,telephone,provinceName,cityName,areaName,introduce,create_date as createDate';
        $data = model('Organization')->getOrganizationByOrgId($id, $field);
        $this->apiReturn(200, $data);
    }

    public function remove(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');
        $orgId = $this->data['id'] + 0;

        if($orgId == $this->orgId){
            $this->apiReturn(201, '', '不能对自身进行此操作');
        }

        $org = model('Organization')->getOrganizationByOrgId($orgId, 'status');
        if(!$org){
            $this->apiReturn(201, '', '门店不存在');
        }
        if($org['status'] == 1){
            $status = 2;
            $msg    = '禁用';
        }else{
            $status = 1;
            $msg    = '启用';
        }

        $result = Db::name('system_organization')->where(['orgId' => $orgId])->update(['status' => $status]);
        $result === false && $this->apiReturn(201, '', $msg . '失败');
        $this->apiReturn(200, ['status' => $status], $msg . '成功');
    }

}