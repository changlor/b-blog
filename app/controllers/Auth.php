<?php
namespace app\controllers;
class Auth extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function signin()
    {
        $res = $this->model->Auth->signin([
            'username' => $this->post['username'],
            'password' => $this->post['password'],
            'userId' => $this->uid,
        ]);

        $this->response($res);
    }
}
