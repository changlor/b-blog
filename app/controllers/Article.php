<?php
namespace app\controllers;
class Article extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function test()
    {
        $str = 'https://twitter.com/changle/status/123';
        $pattern = '/https:\/\/(|mobile.)twitter\.com\/([0-9a-z_]+)\/status\/([0-9]{0,})/i';
        preg_match($pattern, $str, $match);
        echo'<pre>';print_r($match);
    }

    public function createArticle()
    {
        $res = $this->model->Article->insertArticle([
            'userId' => $this->uid,
            'categoryId' => $this->post['categoryId'],
            'title' => $this->post['title'],
            'content' => $this->post['content'],
        ]);

        return $this->response($res);
    }

    public function updateArticle($articleId)
    {
        $res = $this->model->Article->updateArticle([
            'userId' => $this->uid,
            'articleId' => $articleId,
            'categoryId' => $this->put['categoryId'],
            'title' => $this->put['title'],
            'profile' => $this->put['profile'],
            'body' => $this->put['body'],
        ]);

        $this->response($res);
    }

    public function getArticle($articleId)
    {
        $res = $this->model->Article->selectArticle([
            'articleId' => $articleId,
            'version' => $this->header['version'],
        ]);

        $this->response($res);
    }

    public function getCategoryArticles($categoryId)
    {
        $res = $this->model->Category->selectCategoryArticles([
            'categoryId' => $categoryId,
            'version' => $this->header['version'],
        ]);

        $this->response($res);
    }

    public function getArticles()
    {
        $res = $this->model->Article->selectArticles([
            'version' => $this->header['version'],
        ]);

        $this->response($res);
    }
}
