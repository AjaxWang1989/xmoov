<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午3:24
 */

namespace Zoran\Xmoov\Servers;


class AudioServer extends StreamServer
{
    public function init()
    {
        if ($this->request->get('file')) {
            $config = [
                'file' => $this->request->get('file'),
                'file_path' => $this->config['file_path'] . '/audio/',
                'force_download' => 0,
                'burst_size' => 120,
                'throttle' => 84,
                'mime_types' => [
                    'mp3' => 'audio/mpeg'
                ]

            ];
            return $config;
        }
        return false;
    }
}