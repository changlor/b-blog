<?php
namespace app\controllers;
class Comment extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createComment()
    {
        $res = $this->model->Comment->insertComment([
            'articleId' => $this->post['postId'],
            'nickname' => $this->post['nickname'],
            'email' => $this->post['email'],
            'website' => $this->post['website'],
            'content' => $this->post['content'],
        ]);
        
        $this->response($res);
    }

    public function getArticleComments($articleId)
    {
        $res = $this->model->Comment->selectArticleComments([
            'articleId' => $articleId,
            'currentPage' => $this->get['currentPage'],
            'pageSize' => $this->get['pageSize'],
        ]);
        
        $this->response($res);
    }
}
