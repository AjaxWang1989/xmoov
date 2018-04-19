<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午3:40
 */

namespace Zoran\Xmoov\Servers;


class EmbedServer extends StreamServer
{
    public function init(){
        if ($this->request->get('player') && $this->request->get('media') && $this->request->get('file')) {
            return [
                'file' => "loader.swf",
                'file_path' => $this->config['file_path'] . '/players/',
                'force_download' => 0,
                'burst_size' => 0,
                'throttle' => 0,
                'mime_types' => [
                    'swf' => 'application/x-shockwave-flash'
                ]
            ];
        }
        return false;
    }
}