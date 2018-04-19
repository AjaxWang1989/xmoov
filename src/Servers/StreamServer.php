<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午3:14
 */

namespace Zoran\Xmoov\Servers;

use Symfony\Component\HttpFoundation\Request;

abstract class StreamServer
{
    protected $storage = null;

    protected $config = null;
    /**
     * @var Request
     * */
    protected $request = null;

    public function __construct(Request $request , $storage, $config)
    {
        $this->request = $request;
        $this->storage = $storage;
        $this->config = $config;
    }

    public function init(){
        return false;
    }
}