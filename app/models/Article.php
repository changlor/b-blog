<?php
namespace app\models;
class Article extends Base
{
    private $table = 'articles';
    private $columns = [
        'id',
        'title',
        'body',
        'cover',
        'profile',
        'created_at',
        'updated_at',
        'user_id',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @access public
     * @param [array] $where --The WHERE clause to filter records.
     * @return [boolean] --True of False if the target data has been founded.
     */
    public function has($where)
    {
        $res = parent::has($this->table, $where);
        return $res && !is_numeric($res);
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
     * @param [array] $updateInfo --The data that will be modified.
     * @param (optional)[array] $where --The WHERE clause to filter records.
     * @return [boolean] --Ture or false if updated success.
     */
    public function update($updateInfo, $where = [])
    {
        return parent::update($this->table, $updateInfo, $where) > 0;
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
     * @param [array] $input --包括四个参数
     * @param [string]           $title      --插入文章的标题
     * @param [int]              $categoryId --插入文章的分类id
     * @param (required)[int]    $userId     --插入文章的作者id
     * @param (required)[string] $content    --插入文章的原始正文
     * @return [int] --返回文章id
     */
    public function insertArticle($input)
    {
        //获取用户id
        $userId = $input['userId'];
        //--如果不正确则返回错误授权信息
        if (!is_numeric($userId)) {
            return [false, '授权信息错误或者已过期OoO'];
        }
        //获取文章分类id
        $categoryId = $input['categoryId'];
        //--不存在文章分类则设置为默认分类
        if (!$this->has(['category_id' => $categoryId])) {
            $categoryId = DEFAULT_CATEGORY_ID;
        }
        //获取文章标题
        $title = $input['title'];
        //获得原始正文
        $content = $input['content'];
        //--如果为空则返回不能为空的错误信息
        if (empty($content)) {
            return [false, '文章内容不能为空OoO'];
        }
        //--成功则将正文转义回来
        $content = htmlspecialchars_decode($content);
        //--以约定好的@more为断点断开正文
        $content = explode('@more', $content);
        //获取简介
        $profile = $content[0];
        //匹配封面的正则，简介中只允许存在一张图片，即封面
        $coverPattern = '/\!\[.*?\]\(([ ]+)?(.*?)(?= |\))/i';
        //获取简介中的封面，如果没有则设为空
        preg_match($coverPattern, $profile, $match) ? $cover = $match[2] : $cover = null;
        //--去掉简介中的封面链接
        $profile = preg_replace('/(\!\[.*?\]\(.*?\))/i', '', $profile);
        //--最后，简介应当是去掉首位空字符且不包含图片链接的
        $profile = trim($profile);
        //获得修改后的正文
        $body = $profile . $content[1];
        //获得其他数据
        $created_at = time();
        $updated_at = time();
        //组装文章数据
        $article = array(
            'user_id' => $userId,
            'title' => $title,
            'cover' => $cover,
            'profile' => $profile,
            'body' => $body,
            'category_id' => $categoryId,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        );
       //插入文章数据返回文章id并更新版本号
        $this->insert($article);
        //--更新文章版本号和分类版本号
        $this->updateArticleVersion(['articleId' => $articleId]);
        $this->model->Category->updateCategoryVersion(['categoryId' => $categoryId]);
        //所有输入值都是经过校验的，所以默认是成功的，失败只能说校验环节出错
        return [true, '', ['id' => $articleId]];
    }

    /**
     * @param [array] $input --包括五个参数
     * @param (required)[int]    $userId     更新文章的权限验证
     * @param (required)[int]    $articleId  更新文章的id
     * @param [int]              $categoryId 更新文章的分类id
     * @param [string]           $title      更新文章的标题
     * @param (required)[string] $profile    更新文章的简介
     * @param (required)[string] $body       更新文章的正文
     * @return [boolean] --更新成功与否
     */
    public function updateArticle($input)
    {
        //获取用户id
        $userId = $input['userId'];
        //--如果不正确则返回错误授权信息
        if (!is_numeric($userId)) {
            return [false, '授权信息错误或者已过期OoO'];
        }
        //获取文章id
        $articleId = $input['articleId'];
        //--如果不存在则返回错误信息不存在文章
        if (!$this->has(['id' => $articleId])) {
            return [false, '不存在文章OoO'];
        }
        //获取文章分类id
        $categoryId = $input['categoryId'];
        //--不存在文章分类则设置为默认分类
        if (!$this->has(['category_id' => $categoryId])) {
            $categoryId = DEFAULT_CATEGORY_ID;
        }
        //获取文章标题
        $title = $input['title'];
        //获取文章简介
        $profile = $input['profile'];
        //--如果为空返回错误信息不能为空
        if (empty($profile)) {
            return [false, '简介不能为空OoO'];
        }
        //获取文章内容
        $body = $input['body'];
        //--如果为空返回错误信息不能为空
        if (empty($body)) {
            return [false, '内容不能为空OoO'];
        }
        //获取修改文章的时间戳
        $updated_at = time();
        //组装更新信息
        $updateInfo = array(
            'title' => $title,
            'profile' => $profile,
            'body' => $body,
            'category_id' => $categoryId,
            'updated_at' => $updated_at,
        );
        //更新文章数据
        $this->update($updateInfo, ['id' => $articleId]);
        //更新文章的版本号和文章分类的版本号
        $this->updateArticleVersion(['articleId' => $articleId]);
        $this->model->Category->updateCategoryVersion(['categoryId' => $categoryId]);
        //更新主页的文章版本号
        $this->model->Category->updateCategoryVersion(['categoryId' => DEFAULT_CATEGORY_ID]);
        //所有输入值都是经过校验的，所以默认是成功的，失败只能说校验环节出错
        return [true, '更新成功啦OoO'];
    }

    /**
     * @param [array]         $input     --包括一个参数
     * @param (required)[int] $articleId --需要更新版本号的文章id
     * @return [boolean] --返回更新成功与否
     */
    public function updateArticleVersion($input)
    {
        //获取文章id
        $articleId = $input['articleId'];
        //--如果不存在则返回错误信息不存在文章
        if (!$this->has(['id' => $articleId])) {
            return [false, '不存在文章OoO'];
        }
        //获取需要生成新版本号的文章信息
        $res = $this->selectArticle(['articleId' => $articleId]);
        //--取出文章信息
        $article = $res[2];
        //通过摘要算法生成版本号
        $version = createVersion(json_encode($article));
        //更新对应文章的版本号
        $this->update(['version' => $version], ['id' => $articleId]);
        //所有输入值都是经过校验的，所以默认是成功的，失败只能说校验环节出错
        return [true, '更新成功啦OoO'];
    }

    /**
     * @param [array] $input --包括两个参数
     * @param (40)[string] $version 选取所有文章的版本号
     * @param [string]     $sort    选取所有文章的排序
     * @return [array] --返回当前数据库储存的所有文章或空数组
     */
    public function selectArticles($input)
    {
        //获取版本号
        $version = $input['version'];
        //--如果携带正确的版本号，则说明客户端需要获取最新版本的文章
        if (strlen($version) == 40) {
            return $this->selectUpdatedArticles([
                'version' => $version,
            ]);
        }
        //获取文章排序
        $sort = isset($input['sort']) ? $input['sort'] : 'DESC';
        //只要不是设置的升序，就是默认的降序
        if ($sort != 'ASC') {
            $sort = 'DESC';
        }
        //获取文章，根据返回的状态判断是否成功，返回文章或错误信息
        $articles = $this->select([
            'ORDER' => ['created_at' => $sort],
        ]);

        //--正常情况下不可能失败的
        return [true, '', $articles];
    }

    /**
     * @param [array] $input --包括两个参数
     * @param (required)[int] $articleId  选取文章的id
     * @param (40)[string]    $version    选取文章的版本号
     * @return [mixed] --选取的文章或更新后的文章或版本号信息
     */
    public function selectArticle($input)
    {
        //获取文章id
        $articleId = $input['articleId'];
        //--如果不存在则返回错误信息不存在文章
        if (!$this->has(['id' => $articleId])) {
            return [false, '不存在文章OoO'];
        }
        //获取文章版本号
        $version = isset($input['version']) ? $input['version'] : null;
        //--如果携带正确的版本号，则说明客户端需要获取更新版本后的文章
        if (strlen($version) == 40) {
            return $this->selectUpdatedArticle([
                'articleId' => $articleId,
                'version' => $version,
            ]);
        }
        $article = $this->select(['id' => $articleId]);
        //取出文章信息，只要存在文章id，就认为选取成功了
        $article = $article[0];
        return [true, '', $article];
    }

    /**
     * @param [array] $input --包括两个参数
     * @param (required)[int]        $articleId  选取文章的id
     * @param (40)(required)[string] $version    选取文章的版本号
     * @return [mixed] --更新后的文章或版本号信息
     */
    public function selectUpdatedArticle($input)
    {
        //获取文章id
        $articleId = $input['articleId'];
        //--如果不存在则返回错误信息不存在文章
        if (!$this->has(['id' => $articleId])) {
            return [false, '不存在文章OoO'];
        }
        //获取文章版本号
        $version = $input['version'];
        //--如果版本号格式不对，直接返回错误
        if (strlen($version) != 40) {
            return [false, '版本号格式错误OoO'];
        }
        //如果存在当前分类id对应的旧版本号，说明没有更新，返回信息
        if ($this->has([
            'AND' => ['version' => $version, 'id' => $articleId]
        ])) {
            return [true, '已经是最新版本OoO', ['version' => $version]];
        }
        //只要原文章的版本号变了，就认为文章更新过了
        return $this->selectArticle(['articleId' => $articleId]);
    }

    /**
     * @param [array] $input --包括一个参数
     * @param (40)(required)[string] $version 选取文章的版本号
     * @return [mixed] --更新后的文章或版本号信息
     */
    public function selectUpdatedArticles($input)
    {
        //获取文章版本号
        $version = $input['version'];
        //--如果版本号格式不对，直接返回错误
        if (strlen($version) != 40) {
            return [false, '版本号格式错误OoO'];
        }
        //全部文章生成的版本号默认放到home分类里，home的分类id为1
        if ($this->model->Category->has([
            'AND' => ['version' => $version, 'id' => DEFAULT_CATEGORY_ID]
        ])) {
            return [true, '已经是最新版本OoO', ['version' => $version]];
        }
        //只要原文章的版本号变了，就认为文章更新过了
        return $this->selectArticles();
    }
}
