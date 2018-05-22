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

        $where   = ['ao.orgId'    => ['in', $this->orgIds]];
        $whereOr = ['ao.parentId' => ['in', $this->orgIds]];

        $data = model('Organization')->getOrgList($where, $whereOr, $page, $rows, 'ao.orgLevel asc,ao.orgId desc');
        $this->apiReturn(200, $data);
    }

    public function edit(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;

        
    }

    public function getOrg(){
        $data = model('Organization')->getOrgAll(['parentId' => $this->orgId, 'status' => 1], ['orgId' => $this->orgId], 'orgId,shortName as orgName');
        $this->apiReturn(200, $data);
    }

}