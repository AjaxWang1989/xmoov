<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午3:15
 */

namespace Zoran\Xmoov\Servers;


class VideoServer extends StreamServer
{
    public function init()
    {
        if ($this->request->get('file')) {

            $position = $this->request->get('start');
            $config   = [
                'file'           => $this->request->get('file'),
                'file_path'      => $this->storage,
                'position'       => $position,
                'use_http_range' => 1,
                'force_download' => 0,
                'burst_size'     => 1,
                'throttle'       => 320,
                'mime_types'     => [
                    'flv'   => 'video/x-flv',
                    'mp4'   => 'video/mp4',
                    'f4v'   => 'video/mp4',
                    'f4p'   => 'video/mp4',
                    'asf'   => 'video/x-ms-asf',
                    'asr'   => 'video/x-ms-asf',
                    'asx'   => 'video/x-ms-asf',
                    'avi'   => 'video/x-msvideo',
                    'mpa'   => 'video/mpeg',
                    'mpe'   => 'video/mpeg',
                    'mpeg'  => 'video/mpeg',
                    'mpg'   => 'video/mpeg',
                    'mpv2'  => 'video/mpeg',
                    'mov'   => 'video/quicktime',
                    'movie' => 'video/x-sgi-movie',
                    'mp2'   => 'video/mpeg',
                    'qt'    => 'video/quicktime'
                ]
            ];
            return $config;
        }
        return false;
    }
}