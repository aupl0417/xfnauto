<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\PC;

use think\Controller;
use think\Db;
class News extends Base
{

    /**
     * 资讯列表
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        $type  = isset($this->data['type']) && !empty($this->data['type']) ? $this->data['type'] + 0 : 0;

        $field = 'a_id as id,a_title as title,a_publishedTime as publishedTime,a_icon as icon,a_content as content';
        $where = [
            'a_state'         => ['eq', 1],
            'a_publishedTime' => ['neq', 0]
        ];
        if($type){
            !in_array($type, [1, 2, 3], true) && $this->apiReturn(201, '', '新闻类型非法');
            $where['a_type'] = $type;
        }else{
            $where['a_type'] = ['in', [1, 2, 3]];
        }

        $count = Db::name('article_post')->where($where)->count();
        $data  = Db::name('article_post')->where($where)->field($field)->page($page, $rows)->order('a_publishedTime desc')->select();
        if($data){
            foreach($data as &$value){
                $value['publishedTime'] = date('Y-m-d', strtotime($value['publishedTime']));
                $value['content']       = mb_substr(strip_tags(htmlspecialchars_decode($value['content'])), 0, 30, 'utf-8') . '...';
            }
        }
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '文章ID非法');
        $id = $this->data['id'] + 0;

        $where = [
            'a_id'            => $id,
            'a_state'         => ['eq', 1],
            'a_type'          => ['in', [1, 2, 3]],
            'a_publishedTime' => ['neq', '']
        ];

        $field = 'a_id as id,a_title as title,a_content as content,a_type as type,a_publishedTime as publishedTime';
        $data = Db::name('article_post')->field($field)->where($where)->find();

        !$data&& $this->apiReturn(201, '', '文章不存在');

        $type = ['公司新闻', '行业动态', '汽车商学院'];
        $data['type']    = $type[$data['type'] - 1];
        $data['publishedTime'] = date('Y-m-d', strtotime($data['publishedTime']));
        $data['content'] = str_replace(['&lt;', '&gt;', '"'], ['<', '>', '\''], htmlspecialchars_decode($data['content']));
        $where['a_id']   = ['lt', $id];
        $preId   = Db::name('article_post')->where($where)->field('a_id as id,a_title as title')->order('a_publishedTime desc')->find();
        $data['preId'] = $preId ? $preId : ['id' => $id, 'title' => $data['title']];
        $where['a_id']   = ['gt', $id];
        $nextId  = Db::name('article_post')->where($where)->field('a_id as id,a_title as title')->order('a_publishedTime asc')->find();
        $data['nextId'] = $nextId ? $nextId : ['id' => $id, 'title' => $data['title']];
        $this->apiReturn(200, $data);
    }

    public function note(){
        $result = $this->validate($this->data, 'Note');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $data = [
            'n_username'   => htmlspecialchars($this->data['username']),
            'n_email'      => $this->data['email'],
            'n_content'    => htmlspecialchars($this->data['content']),
            'n_createTime' => time(),
        ];

        $result = Db::name('note')->insert($data);
        !$result && $this->apiReturn(201, '', '留言失败');
        $this->apiReturn(200, '', '留言成功');
    }

    public function organization(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where = ['status' => 1];
        $data = model('Organization')->getOrgData($where, [], $page, $rows, 'create_date desc', 'orgId as id,shortName orgName,address,imageurl');
        if($data['list']){
            foreach($data['list'] as &$value){
                $value['imageurl'] = explode(',', $value['imageurl']);
            }
        }
        $this->apiReturn(200, $data);
    }

}