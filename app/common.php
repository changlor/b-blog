<?php
function fadeTime($fT)
{
    $cT = time();
    $dY = date('Y', $cT) - date('Y', $fT);
    $time = $dY > 0 ? date('m 月 d 日 Y 年', $cT) : date('m 月 d 日', $fT);
    return $time;
}

function createVersion($code)
{
    $code = preg_replace_callback(
        '/(\\\\+)u([0-9a-z]{4})/i',
        function ($matches) {
            return $matches[1] == '\\'
            ? '\\u' . strtoupper($matches[2])
            : $matches[1] . 'u' . $matches[2];
        },
        $code
    );
    $code = preg_replace('/\\\\\//i', '/', $code);

    return sha1($code);
}

function authToken($code = '', $operation = false, $expire = 36000)
{
    if ($operation == 'DECODE') {
        $id = authCode($code, 'DECODE', $expire);
        //成功解密返回id，否则返回false
        return is_numeric($id) ? $id : false;
    }
    //返回token
    return authCode($code, 'ENCODE', $expire);
}

function authCode($string, $operation, $expiry = 0)
{
    $key = 'oppaiishonour';
    $ckey_length = 4;
    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

function hashString($string, $salt = null)
{
    /** 生成随机字符串 */
    $salt = empty($salt) ? randString(9) : $salt;
    $length = strlen($string);
    $hash = '';
    $last = ord($string[$length - 1]);
    $pos = 0;
    /** 判断扰码长度 */
    if (strlen($salt) != 9) {
        /** 如果不是9直接返回 */
        return;
    }
    while ($pos < $length) {
        $asc = ord($string[$pos]);
        $last = ($last * ord($salt[($last % $asc) % 9]) + $asc) % 95 + 32;
        $hash .= chr($last);
        $pos++;
    }
    $result['hash'] = '$T$' . $salt . md5($hash);
    $result['salt'] = $salt;
    return empty($salt) ? $result : $result['hash'];
}

function randString($length, $specialChars = false)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    if ($specialChars) {
        $chars .= '!@#$%^&*()';
    }
    $result = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[rand(0, $max)];
    }
    return $result;
}
