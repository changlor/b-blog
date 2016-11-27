<?php
require 'vendor/autoload.php';
$config['APP_DEBUG'] = true;
$config['DB'] = [
    'blog' => [
        'TYPE' => 'mysql',
        'HOST' => '127.0.0.1',
        'USER' => 'root',
        'PWD' => 'changle815123',
        'NAME' => 'blog',
        'PORT' => 3306,
        'CHARSET' => 'utf8',  
    ]
];
$config['URL_MODE'] = 'PATH_INFO';
//$config['ERROR_TPL'] = 'Public/error';
$config['URL_ROUTE'] = [
    'test' => 'Article/test',
    'posts' => [
        'get' => 'Article/getArticles',
    ],
    'posts/([0-9]+)' => [
        'get' => 'Article/getCategoryArticles/$1',
    ],
    'post' => [
        'post' => 'Article/createArticle',
    ],
    'post/([0-9]+)' => [
        'get' => 'Article/getArticle/$1',
        'delete' => 'Article/deleteArticle/$1',
        'put' => 'Article/updateArticle/$1',
    ],
    'comment' => [
        'post' => 'Comment/createComment',
        'delete' => 'Comment/deleteComment',
    ],
    'comments/([0-9]+)' => [
        'get' => 'Comment/getArticleComments/$1',
    ],
    'signin' => [
        'post' => 'Auth/signin',
    ],
];
$app = new \Kotori\App($config);
$app->run();
