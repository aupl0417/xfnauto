<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api_v2\controller\v3\Frontend;

use app\api\model\CustomerOrder;
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
        $field = 'a_id as id,a_title as title,a_publishedTime as publishedTime,a_icon as icon';
        $where = [
            'a_state'         => ['eq', 1],
            'a_type'          => ['eq', 4],
            'a_publishedTime' => ['neq', '']
        ];
        $data  = Db::name('article_post')->where($where)->field($field)->page($page, $rows)->order('a_publishedTime desc')->select();
        $this->apiReturn(200, $data);
    }


    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '文章ID非法');
        $id = $this->data['id'] + 0;

        $field = 'a_id as id,a_title as title,a_content as content,a_source as source,a_publishedTime as publishedTime,a_excerpt as excerpt,a_videoUrl as videoUrl,a_icon as icon,realName,a_like as likeCount';
        $data = Db::name('article_post')->where(['a_id' => $id, 'a_state' => ['eq', 1]])
              ->field($field)
              ->join('system_user', 'a_uid=usersId', 'left')
              ->find();

        !$data&& $this->apiReturn(201, '', '文章不存在');
        $data['content'] = htmlspecialchars_decode($data['content']);
        $this->apiReturn(200, $data);
    }


}