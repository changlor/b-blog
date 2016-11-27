<?php
namespace app\models;
class Comment extends Base
{
    private $table = 'comments';
    private $columns = [
        'id',
        'nickname',
        'content',
        'website',
        'email(avatar)',
        'created_at',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @access public
     * @param [array] $article --The data that will be inserted into table.
     * @return [mixed] --The last insert id or false.
     */
    public function insert($article)
    {
        $id = parent::insert($this->table, $article);
        return $id > 0 ? $id : false;
    }

    /**
     * @access public
     * @param (optional)[array] $where --The WHERE clause to filter records.
     * @param (optional)[array] $columns --The target columns of data will be fetched.
     * @return [mixed] --Array or false or null.
     */
    public function select($where = [], $columns = [])
    {
        if (empty($columns)) {
            $columns = $this->columns;
        }
        $res = parent::select($this->table, $columns, $where);
        if (!is_array($res)) {
            return false;
        }
        return $res;
    }

    /**
     * @param [array] $input --包含五个参数
     * @param (required)[int]    $articleId --评论对应的文章id
     * @param (required)[string] $nickname  --评论者的昵称
     * @param (required)[string] $email     --评论者的邮箱
     * @param [string]           $website   --评论者的个人网站
     * @param (required)[string] $content   --评论者的评论内容
     */
    public function insertComment($input)
    {
        //获取文章id
        $articleId = $input['articleId'];
        //--调用article模型封装的方法，如果不存在articleId则返回错误信息不存在文章
        if (!$this->model->Article->has(['id' => $articleId])) {
            return [false, '不存在文章OoO'];
        }
        //获取评论者的昵称
        $nickname = $input['nickname'];
        //--昵称不能为空
        if (empty($nickname)) {
            return [false, '昵称不能为空OoO'];
        }
        //获取评论者的邮箱
        $email = $input['email'];
        //--邮箱不能为空
        if (empty($email)) {
            return [false, '邮箱不能为空OoO'];
        }
        //获取评论者的网址
        $website = $input['website'];
        //为没有加协议前缀的url匹配前缀
        if (!preg_match('/http(s)?:\/\//i', $website)) {
            $website = 'http://' . $website;
        }
        //获取评论者的评论内容
        $content = $input['content'];
        //--评论内容不能为空
        if (empty($content)) {
            return [false, '评论不能为空OoO'];
        }
        //获取其他的必要数据
        $created_at = time();
        $updated_at = time();
        //插入数据
        $comment = array(
            'article_id' => $articleId,
            'nickname' => $nickname,
            'email' => $email,
            'website' => $website,
            'content' => $content,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        );
        //插入文章数据返回评论id
        $commentId = $this->insert($comment);
        //插入后，默认成功状态下，文章的评论计数加一
        $this->model->Article->update(['comments_count[+]' => 1], ['id' => $articleId]);
        //所有输入值都是经过校验的，所以默认是成功的并且返回当前文章的所有评论，失败只能说校验环节出错
        return $this->selectArticleComments(['articleId' => $articleId]);
    }

    /**
     * @param [array] $input --包括一个参数
     * @param (required)[int] $articleId --选择对应文章下评论的文章id
     * @param [init]          $page      --指定评论的第几页数
     * @param [init]          $perPage   --指定评论每页的记录数
     * @return [array] --包含评论的数组
     */
    public function selectArticleComments($input)
    {
        //获取文章id
        $articleId = $input['articleId'];
        //--调用article模型封装的方法，如果不存在articleId则返回错误信息不存在文章
        if (!$this->model->Article->has(['id' => $articleId])) {
            return [false, '不存在文章OoO'];
        }
        //获取评论总数
        $res = $this->model->Article->select(['id' => $articleId], ['comments_count']);
        $comments_count = $res[0]['comments_count'];
        //获取评论指定页数，默认指定第一页
        $currentPage = $input['currentPage'];
        //--如果指定页数不是数字，则默认指定为第一页
        if (!is_numeric($currentPage)) {
            $currentPage = 1;
        }
        //获取评论需要的记录数
        $pageSize = $input['pageSize'];
        //--同样的，如果不是数字，则默认指定为六条
        if (!is_numeric($pageSize)) {
            $pageSize = 6;
        }
        //获取评论排序
        $sort = isset($input['sort']) ? $input['sort'] : 'DESC';
        //只要不是设置的升序，就是默认的降序
        if ($sort != 'ASC') {
            $sort = 'DESC';
        }
        //获取评论
        $comments = $this->select([
            'article_id' => $articleId,
            'LIMIT' => [($currentPage - 1) * $pageSize, $pageSize],
            'ORDER' => ['created_at' => $sort],
        ]);
        foreach ($comments as $key => $value) {
            $comments[$key]['avatar'] = md5($value['avatar']);
        }
        //所有输入值都是经过校验的，所以默认是成功的，失败只能说校验环节出错
        return [true, '', ['comments' => $comments, 'commentsCount' => $comments_count]];
    }
}
