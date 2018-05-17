<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\Backend;

use think\Controller;
use think\Db;
class Article extends Base
{

    /**
     * 首页
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where = [];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $where['a_title'] = ['like', '%' . $keywords . '%'];
        }

        $field = 'a_id as id,a_title as title,a_publishedTime as publishedTime,a_state as state';
        $count = Db::name('article_post')->where($where)->count();
        $data  = Db::name('article_post')->where($where)->field($field)->page($page, $rows)->order('a_publishedTime desc')->select();
        if($data){
            foreach($data as &$value){
                $value['publishedTime'] = date('Y-m-d H:i', $value['publishedTime']);
                $value['stateName']     = $value['state'] == -1 ? '禁用中' : ($value['state'] == 0 ? '未发布' : '启用中');
            }
        }

        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

    /**
     * 添加文章
     * */
    public function create(){
        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'AddArticle');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $time   = date('Y-m-d H:i:s');
        $data   = [
            'a_title'    => $this->data['title'],
            'a_content'  => $this->data['content'],
            'a_excerpt'  => $this->data['excerpt'],
            'a_videoUrl' => isset($this->data['videoUrl']) ? $this->data['videoUrl'] : '',
            'a_icon'     => isset($this->data['icon']) ? $this->data['icon'] : '',
            'a_source'   => isset($this->data['source']) && !empty($this->data['source']) ? $this->data['source'] : '喜蜂鸟学堂',
            'a_uid'      => $this->userId,
            'a_createTime'    => $time,
            'a_updateTime'    => $time,
            'a_publishedTime' => 0,
        ];

        $result = Db::name('article_post')->insert($data);
        !$result && $this->apiReturn(201, '', '添加失败');
        $this->apiReturn(200, '', '添加成功');
    }

    public function edit(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '文章ID非法');

        $id = $this->data['id'] + 0;

        unset($this->data['sessionId']);
        $result = $this->validate($this->data, 'AddArticle');
        if($result !== true){
            $this->apiReturn(201, '', $result);
        }

        $time   = date('Y-m-d H:i:s');
        $data = [
            'a_title'    => $this->data['title'],
            'a_content'  => $this->data['content'],
            'a_excerpt'  => $this->data['excerpt'],
            'a_videoUrl' => isset($this->data['videoUrl']) ? $this->data['videoUrl'] : '',
            'a_icon'     => isset($this->data['icon']) ? $this->data['icon'] : '',
            'a_source'   => isset($this->data['source']) && !empty($this->data['source']) ? $this->data['source'] : '喜蜂鸟学堂',
            'a_updateTime' => $time
        ];

        $result = Db::name('article_post')->where(['a_id' => $id])->update($data);
        $result === false && $this->apiReturn(201, '', '编辑失败');
        $this->apiReturn(200, '', '编辑成功');
    }

    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '文章ID非法');
        $id = $this->data['id'] + 0;

        $field = 'a_id as id,a_title as title,a_content as content,a_source as source,a_publishedTime as publishedTime,a_excerpt as excerpt,a_videoUrl as videoUrl,a_icon as icon,realName,a_like as likeCount';
        $data = Db::name('article_post')->where(['a_id' => $id, 'a_deleteTime' => ['eq', '']])
              ->field($field)
              ->join('system_user', 'a_uid=usersId', 'left')
              ->find();

        !$data&& $this->apiReturn(201, '', '文章不存在');
        $data['content'] = htmlspecialchars_decode($data['content']);
        $this->apiReturn(200, $data);
    }

    /**
     * 今日回访列表
     * @return json
     * */
    public function remove(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '文章ID非法');
        $id = $this->data['id'] + 0;

        $data = Db::name('article_post')->where(['a_id' => $id])->field('a_id,a_deleteTime')->find();
        !$data && $this->apiReturn(201, '', '文章不存在');

        if($data['a_deleteTime'] == 0){
            $type  = '禁用';
            $state = -1;
            $value = ['a_state' => $state, 'a_deleteTime' => date('Y-m-d H:i:s')];
        }else{
            $type = '启用';
            $state = 0;
            $value = ['a_state' => $state, 'a_deleteTime' => 0];
        }

        $result = Db::name('article_post')->where(['a_id' => $id])->update($value);
        $result === false && $this->apiReturn(201, '', $type . '失败');
        $this->apiReturn(200, ['state' => $state], $type . '成功');
    }

    public function publish(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '文章ID非法');
        $id = $this->data['id'] + 0;

        $result = Db::name('article_post')->where(['a_id' => $id])->update(['a_publishedTime' => date('Y-m-d H:i:s'), 'a_state' => 1]);
        $result === false && $this->apiReturn(201, '', '发布失败');
        $this->apiReturn(200, ['state' => 1], '发布成功');
    }


}