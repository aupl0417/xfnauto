<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v2;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class Organization extends Home
{

    /**
     * 门店列表
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

//        $orgIds = [];
//        foreach($this->orgIds as $k => &$val){
//            if($val != $this->orgId){
//                $orgIds[] = $val;
//            }
//        }

        $org = model('Organization')->getOrgAll(['parentId' => ['in', $this->orgIds], 'status' => 1], 'orgId');
        if(!$org){
            $this->apiReturn(200, []);
        }
        $org = array_column($org, 'orgId');
        $where   = ['status' => 1];
        if($org){
            $where['orgId']    = ['in', $org];
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $field    = is_numeric($keywords) ? 'telephone' : 'shortName';
            $where[$field] = ['like', '%' . $keywords . '%'];
        }

        $data = model('Organization')->getOrgData($where, [], $page, $rows, 'orgLevel asc,orgId desc');
        $this->apiReturn(200, $data);
    }

    /**
     * 添加汽贸店
     * */
    public function create(){
        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'CreateOrg');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        if(Db::name('system_organization')->where(['shortName' => $this->data['shortName'], 'status' => 1])->count()){
            $this->apiReturn(201, '', '门店名称已存在');
        }

        if(Db::name('system_organization')->where(['telephone' => $this->data['telePhone'], 'status' => 1])->count()){
            $this->apiReturn(201, '', '联系电话已存在');
        }

        $orgCode = getRandomString(6);
        $data   = [
            'shortName'    => $this->data['shortName'],
            'parentId'     => $this->orgId,
            'orgCode'      => $orgCode,
            'orgCodeLevel' => 'null_' . $orgCode,
            'create_date'  => date('Y-m-d H:i:s'),
            'status'       => 1,
            'orgLevel'     => 3,
            'orgtype'      => 3,
            'linkman'      => $this->data['linkMan'],
            'telephone'    => $this->data['telePhone'],
            'address'      => $this->data['address'],
            'provinceId'   => $this->data['provinceId'] + 0,
            'cityId'       => $this->data['cityId'] + 0,
            'areaId'       => $this->data['areaId'] + 0,
            'provinceName' => $this->data['provinceName'],
            'cityName'     => $this->data['cityName'],
            'areaName'     => $this->data['areaName'],
            'remark'       => $this->data['remark'],
        ];


        $result = Db::name('system_organization')->insert($data);
        unset($this->data, $data);
        !$result && $this->apiReturn(201, '', '添加失败');
        $this->apiReturn(200, '', '添加成功');
    }

    /**
     * 编辑汽贸店
     * */
    public function edit(){
        (!isset($this->data['orgId']) || empty($this->data['orgId'])) && $this->apiReturn(201, '', '参数非法');
        $orgId = $this->data['orgId'] + 0;
        unset($this->data['sessionId'], $this->data['orgId']);
        $result = $this->validate($this->data, 'CreateOrg');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        if(Db::name('system_organization')->where(['shortName' => $this->data['shortName'], 'status' => 1, 'orgId' => ['neq', $orgId]])->count()){
            $this->apiReturn(201, '', '门店名称已存在');
        }

        if(Db::name('system_organization')->where(['telephone' => $this->data['telePhone'], 'status' => 1, 'orgId' => ['neq', $orgId]])->count()){
            $this->apiReturn(201, '', '联系电话已存在');
        }

        $data   = [
            'shortName'    => $this->data['shortName'],
            'linkman'      => $this->data['linkMan'],
            'telephone'    => $this->data['telePhone'],
            'address'      => $this->data['address'],
            'provinceId'   => $this->data['provinceId'] + 0,
            'cityId'       => $this->data['cityId'] + 0,
            'areaId'       => $this->data['areaId'] + 0,
            'provinceName' => $this->data['provinceName'],
            'cityName'     => $this->data['cityName'],
            'areaName'     => $this->data['areaName'],
            'remark'       => $this->data['remark'],
        ];

        $result = Db::name('system_organization')->where(['orgId' => $orgId])->update($data);
        unset($this->data, $data);
        $result === false && $this->apiReturn(201, '', '编辑失败');
        $this->apiReturn(200, '', '编辑成功');
    }

}