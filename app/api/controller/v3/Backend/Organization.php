<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v3\Backend;

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
        unset($this->data['sessionId'], $this->data['id']);
        $result = $this->validate($this->data, 'Organization');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $data = [
            'shortName'     => $this->data['shortName'],
            'orgtype'       => $this->data['orgType'],
            'telephone'     => $this->data['telephone'],
            'address'       => $this->data['address'],
            'longitude'     => isset($this->data['longitude']) && !empty($this->data['longitude']) ? $this->data['longitude'] : 0,
            'latitude'      => isset($this->data['latitude']) && !empty($this->data['latitude']) ? $this->data['latitude'] : 0,
            'provinceId'    => $this->data['provinceId'],
            'cityId'        => $this->data['cityId'],
            'areaId'        => $this->data['areaId'],
            'introduce'     => $this->data['introduce'],
            'bankAccount'   => $this->data['bankAccount'],
            'nameOfAccount' => $this->data['nameOfAccount'],
            'bankName'      => $this->data['bankName'],
            'openingBranch' => $this->data['openingBranch'],
            'imageurl'      => $this->data['imageurl'],
            'orgCode'       => getRandomString(6),
            'create_date'   => date('Y-m-d H:i:s'),
            'status'        => 1,
            'orgLevel'      => 3,
            'provinceName'  => isset($this->data['province']) ? htmlspecialchars(trim($this->data['province'])) : '',
            'cityName'      => isset($this->data['city']) ? htmlspecialchars(trim($this->data['city'])) : '',
            'areaName'      => isset($this->data['area']) ? htmlspecialchars(trim($this->data['area'])) : '',
        ];

        $result = Db::name('system_organization')->insert($data);
        unset($this->data, $data);
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

        $data = [
            'shortName'     => $this->data['shortName'],
            'orgtype'       => $this->data['orgType'],
            'telephone'     => $this->data['telephone'],
            'address'       => $this->data['address'],
            'longitude'     => isset($this->data['longitude']) && !empty($this->data['longitude']) ? $this->data['longitude'] : 0,
            'latitude'      => isset($this->data['latitude']) && !empty($this->data['latitude']) ? $this->data['latitude'] : 0,
            'provinceId'    => $this->data['provinceId'],
            'cityId'        => $this->data['cityId'],
            'areaId'        => $this->data['areaId'],
            'introduce'     => $this->data['introduce'],
            'bankAccount'   => $this->data['bankAccount'],
            'nameOfAccount' => $this->data['nameOfAccount'],
            'bankName'      => $this->data['bankName'],
            'openingBranch' => $this->data['openingBranch'],
            'imageurl'      => $this->data['imageurl'],
            'provinceName'  => isset($this->data['province']) ? htmlspecialchars(trim($this->data['province'])) : '',
            'cityName'      => isset($this->data['city']) ? htmlspecialchars(trim($this->data['city'])) : '',
            'areaName'      => isset($this->data['area']) ? htmlspecialchars(trim($this->data['area'])) : '',
        ];

//        if(Db::name('system_organization')->where(['orgId' => ['neq', $id], 'shortName' => $this->data['shortName']])->count()){
//            $this->apiReturn(201, '', '该门店名称已存在');
//        }

        $result = Db::name('system_organization')->where(['orgId' => $id])->update($data);
        unset($this->data, $data);
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