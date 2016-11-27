<?php
namespace app\controllers;
use Kotori\Core\Controller;

class Base extends Controller
{
    protected $uid;
    protected $header;
    protected $get;
    protected $post;
    protected $put;
    public function __construct()
    {
        parent::__construct();
        $this->header();
        $this->get();
        $this->post();
        $this->put();
        $this->decryptToken();
    }

    public function response($res)
    {
        if (!isset($res[0])) {
            $res[0] = false;
        }
        if (!isset($res[1])) {
            $res[1] = '未知错误';
        }
        if (!isset($res[2])) {
            $res[2] = [];
        }
        $this->response->throwJson([
            'success' => $res[0],
            'msg' => $res[1],
            'data' => $res[2]
        ]);
    }

    public function header()
    {
        $version = $_SERVER['HTTP_VERSION'];
        $this->header['version'] = $version;
    }

    public function get()
    {
        $this->get = $this->request->input('get.');
    }

    public function post()
    {
        $this->post = json_decode(file_get_contents('php://input'), true);
    }

    public function put()
    {
        $this->put = json_decode(file_get_contents('php://input'), true);
    }

    public function decryptToken()
    {
        $authToken = strtoupper('HTTP_Auth_Token');
        $authToken = $_SERVER[$authToken];
        $this->uid = authToken($authToken, 'DECODE');
    }
}