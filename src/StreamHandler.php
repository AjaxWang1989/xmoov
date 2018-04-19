<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午2:41
 */

namespace Zoran\Xmoov;


interface StreamHandler
{
    public  function encrypt($file);

    public  function decrypt($fileHandle, $bufferSize);
}