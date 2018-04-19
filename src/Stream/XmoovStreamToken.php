<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午3:07
 */

namespace Zoran\Xmoov\Stream;


class XmoovStreamToken
{
    private $tokens = [];

    private $config = null;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getKey($file)
    {
        return md5($file . $this->config['token_key']);
    }

    public function getToken($file)
    {
        $key = $this->getKey($file);
        if (isset($this->tokens[$key])) {
            return $this->tokens[$key];
        } else {
            if (isset($_COOKIE[$key])) {
                return $_COOKIE[$key];
            }
        }
        return false;
    }

    public function isValid($file, $token)
    {
        if ($token == $this->getToken($file)) {
            return true;
        }
        return false;
    }

    public function setToken($file)
    {
        $key   = $this->getKey($file);
        $token = md5(uniqid(rand(), 1));
        setcookie($key, $token, time() + $this->config['token_expires'], '/', false);
        $this->tokens[$key] = $token;
        return $token;
    }
}