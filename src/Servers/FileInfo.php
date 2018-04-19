<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午3:52
 */

namespace Zoran\Xmoov\Servers;


class FileInfo extends StreamServer
{
    public function init()
    {
        return [
            'show_errors' => false
        ];
    }
}