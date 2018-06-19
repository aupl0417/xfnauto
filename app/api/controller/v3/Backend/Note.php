<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v3\Backend;

use think\Controller;
use think\Db;
use think\Exception;
use think\Request;

class Note extends Admin
{

    /**
     * 留言首页
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $field = 'n_id as id,n_username as username,n_phone as phone,n_email as email,n_content as content,n_createTime as createTime';
        $data  = Db::name('note')->field($field)->order('n_id desc')->page($page, $rows)->select();
        $count = Db::name('note')->count();
        if($data){
            foreach($data as $key => &$value){
                $value['createTime'] = date('Y-m-d H:i:s', $value['createTime']);
            }
        }
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

    /**
     * 删除留言
     * @param id integer 留言ID
     * @return json
     * */
    public function remove(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', 'ID非法');
        $id = $this->data['id'] + 0;

        $result = Db::name('note')->where(['n_id' => $id])->update(['n_isDelete' => 1]);
        $result === false && $this->apiReturn(201, '', '删除失败');
        $this->apiReturn(200, '', '删除成功');
    }
    
    

}