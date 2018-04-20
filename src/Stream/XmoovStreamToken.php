<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午3:07
 */

namespace Zoran\Xmoov\Stream;


use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class XmoovStreamToken
{
    private $tokens = [];

    protected $secret = null;

    protected $expires = null;
    /**
     * @var Request
     * */
    protected $request = null;

    public function __construct($secret, $expires, $request)
    {
        $this->secret = $secret;
        $this->expires = $expires;
        $this->request = $request;
    }

    public function getKey($path)
    {
        return md5($path . $this->secret);
    }

    public function getToken($path)
    {
        $key = $this->getKey($path);
        if (isset($this->tokens[$key])) {
            return $this->tokens[$key];
        } else {
            return $this->request->cookies->get($key);
        }
    }

    public function isValid($path, $token)
    {
        if ($token == $this->getToken($path)) {
            return true;
        }
        return false;
    }

    public function setToken($path)
    {
        $key   = $this->getKey($path);
        $token = md5(uniqid(rand(), 1));
        setcookie($key, $token, time() + $this->expires, '/', false);
        $this->tokens[$key] = $token;
        return $token;
    }
}