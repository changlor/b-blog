<?php
namespace app\models;
class Category extends Base
{
    private $table = 'category';

    public function __construct()
    {
        parent::__construct();
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
     * @param [array] $where --The WHERE clause to filter records.
     * @return [boolean] --True of False if the target data has been founded.
     */
    public function has($where)
    {
        $res = parent::has($this->table, $where);
        return $res && !is_numeric($res);
    }

    /**
     * @param [array] $input --包括一个参数
     * @param [int] $categoryId --需要更新版本号的分类id
     * @return [boolean] --返回更新成功与否的布尔值
     */
    public function updateCategoryVersion($input)
    {
        //获取文章分类id
        $categoryId = $input['categoryId'];
        //--如果不存在文章分类则返回错误信息
        if (!$this->has(['id' => $categoryId])) {
            return [false, '未找到分类OoO'];
        }
        //获取需要生成新版本号的当前分类下所有文章信息
        //--如果是默认分类，则说明此时是更新主页文章的版本号
        $res = $categoryId == DEFAULT_CATEGORY_ID
        ? $this->model->Article->selectArticles()
        : $this->model->Category->selectCategoryArticles($categoryId);
        //--取出文章信息
        $articles = $res[2];
        //通过摘要算法生成版本号
        $version = createVersion(json_encode($articles));
        //更新分类版本号
        $this->update(['version' => $version], ['id' => $categoryId]);
        //所有输入值都是经过校验的，所以默认是成功的，失败只能说校验环节出错
        return [true, '更新成功OoO'];
    }

    /**
     * @param [array] $input --包括三个参数
     * @param (required)[int] $categoryId 需要获取分类下的文章的分类id
     * @param [string]        $version    当前分类的版本号
     * @param [string]        $sort       返回的文章的排序
     * @return [array] 返回当前分类下的所有文章或更新后的所有文章或版本号信息
     */
    public function selectCategoryArticles($input)
    {
        //获取文章分类id
        $categoryId = $input['categoryId'];
        //--如果不存在文章分类则返回错误信息
        if (!$this->has(['id' => $categoryId])) {
            return [false, '未找到分类OoO'];
        }
        //获取文章分类的版本号
        $version = $input['version'];
        //--如果携带正确的版本号，则说明客户端需要获取更新版本后的文章
        if (strlen($version) == 40) {
            return $this->selectUpdatedCategoryArticles([
                'categoryId' => $categoryId,
                'version' => $version,
            ]);
        }
        //获取文章的排序方式
        $sort = isset($input['sort']) ? $input['sort'] : 'DESC';
        //--只要不是设置的升序，就是默认的降序
        if ($sort != 'ASC') {
            $sort = 'DESC';
        }
        //只要存在文章分类id，就认为一定获取成功，调用article模型封装的curd方法，获得文章数据
        $articles = $this->model->Article->select([
            'category_id' => $categoryId,
            'ORDER' => ['created_at' => $sort],
        ]);
        return [true, '', $articles];
    }

    /**
     * @param [array] $input --包括两个参数
     * @param (required)[int]     $categoryId  需要获取分类下文章的分类id
     * @param (required)[string]  $version     当前分类的旧版本号
     * @return [array] 返回已更新后的所有文章或版本号信息
     */
    public function selectUpdatedCategoryArticles($input)
    {
        //获取文章分类id
        $categoryId = $input['categoryId'];
        //--如果不存在文章分类则返回错误信息
        if (!$this->has(['id' => $categoryId])) {
            return [false, '未找到分类OoO'];
        }
        //获取文章分类旧版本号
        $version = $input['version'];
        //--如果版本号格式不对，直接返回错误
        if (strlen($version) != 40) {
            return [false, '版本号格式错误OoO'];
        }
        //--如果存在当前分类id对应的旧版本号，说明没有更新，返回信息
        if ($this->has([
            'AND' => ['version' => $version, 'id' => $categoryId]
        ])) {
            return [true, '已经是最新版本OoO', ['version' => $version]];
        }
        //只要原分类的版本号变了，就认为分类更新过了
        return $this->selectCategoryArticles(['categoryId' => $categoryId]);
    }
}
