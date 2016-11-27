<?php
namespace app\models;
class Auth extends Base
{
    private $table = 'users';
    private $columns = ['name'];

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
     * @param [array] $input --包括一个参数
     * @param [string] $username --登陆用户名
     * @return [int] --返回用户id
     */
    public function selectId($input)
    {
        //获取用户名
        $username = $input['username'];
        //--如果不存在用户则返回错误信息
        if (!$this->has(['name' => $username])) {
            return [false, '不存在用户OoO'];
        }
        //只要存在用户名，就一定能获取到用户id
        $res = $this->select(['name' => $username], ['id']);
        //返回用户id
        return [true, '', $res[0]['id']];
    }

    /**
     * @param [array] $input --包括两个参数
     * @param [string] $username --登陆用户名
     * @param [string] $username --登陆密码
     * @return [boolean] --返回用户登录密码正确与否
     */
    public function validate($input)
    {
        //获取用户名
        $username = $input['username'];
        //--如果不存在用户则返回错误信息
        if (!$this->has(['name' => $username])) {
            return [false, '不存在用户OoO'];
        }
        //获取用户密码
        $password = $input['password'];
        //存在用户则获取用户的密码和盐
        $res = $this->select(['name' => $username], ['password_hash', 'auth_key']);
        //调用密码生成函数，比对用户输入密码是否正确
        return $res[0]['password_hash'] == hashString($password, $res[0]['auth_key'])
        ? [true, '用户密码组合正确OoO']
        : [false, ' 用户密码组合错误OoO'];
    }

    /**
     * @param [array] $input --包括两个参数
     * @param [string] $username --登陆用户名
     * @param [string] $password --登陆密码
     * @return [string] --返回授权token
     */
    public function signin($input)
    {
        //获取usrId，如果有的话
        $userId = $input['userId'];
        //如果解析出来的userId是数字的话，说明是合法id
        if (is_numeric($userId)) {
            //获取用户名并返回
            $res = $this->select(['id' => $userId]);
            //返回登陆成功授权
            return [true, '', ['username' => $res['name']]];
        }
        //获取用户名
        $username = $input['username'];
        //--如果不存在用户则返回错误信息
        if (!$this->has(['name' => $username])) {
            return [false, '不存在用户OoO'];
        }
        //获取用户密码
        $password = $input['password'];
        //调用验证函数比对用户名密码
        $res = $this->validate(['username' => $username, 'password' => $password]);
        //如果匹配则返回授权token，否则返回错误信息
        if ($res[0]) {
            $res = $this->selectId(['name' => $username]);
            //只要前面验证正确，这里是一定可以返回正确结果的
            $token = authToken($res[2]);
            return [true, '', ['token' => $token]];
        }
        return [false, '用户密码组合错误OoO'];
    }
}
