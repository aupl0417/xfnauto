<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class Article extends Home
{

    /**
     * 首页
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        $field = 'a_id as id,a_title as title,a_publishedTime as publishedTime,a_icon as icon';
        $where = [
            'a_deleteTime'    => ['eq', 0],
            'a_publishedTime' => ['neq', '']
        ];
        $data  = Db::name('article_post')->where($where)->field($field)->page($page, $rows)->order('a_publishedTime desc')->select();
        if($data){
            foreach($data as &$value){
                $value['publishedTime'] = date('Y-m-d H:i', $value['publishedTime']);
            }
        }
        $this->apiReturn(200, $data);
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

        $time   = time();
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
            'a_publishedTime' => $time,
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

        $time = time();
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
        $this->apiReturn(200, $data);
    }

    /**
     * 今日回访列表
     * @return json
     * */
    public function remove(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '文章ID非法');
        $id = $this->data['id'] + 0;

        $result = Db::name('article_post')->where(['a_id' => $id])->update(['a_deleteTime' => time()]);
        $result === false && $this->apiReturn(201, '', '删除失败');
        $this->apiReturn(200, '', '删除成功');
    }


}