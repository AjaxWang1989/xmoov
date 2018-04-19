<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午3:33
 */

namespace Zoran\Xmoov\Servers;

class DownloadServer extends StreamServer
{
    public function init()
    {
        if ($this->request->get('media') && $this->request->get('file')) {
            if (eregi('protected', $this->request->get('media'))) {
                return false;
            }
            return [
                'file' => $this->request->get('file'),
                'file_path' => $this->config['file_path'] . '/' . $this->request->get('media') . '/',
                'force_download' => true,
                'burst_size' => 0,
                'throttle' => 500,
                'mime_types' => [
                    'swf' => 'application/x-shockwave-flash',
                    'flv' => 'video/x-flv',
                    'mp4' => 'video/mp4',
                    'f4v' => 'video/mp4',
                    'f4p' => 'video/mp4',
                    'asf' => 'video/x-ms-asf',
                    'asr' => 'video/x-ms-asf',
                    'asx' => 'video/x-ms-asf',
                    'avi' => 'video/x-msvideo',
                    'mpa' => 'video/mpeg',
                    'mpe' => 'video/mpeg',
                    'mpeg' => 'video/mpeg',
                    'mpg' => 'video/mpeg',
                    'mpv2' => 'video/mpeg',
                    'mov' => 'video/quicktime',
                    'movie' => 'video/x-sgi-movie',
                    'mp2' => 'video/mpeg',
                    'qt' => 'video/quicktime',
                    'mp3' => 'audio/mpeg',
                    'wav' => 'audio/x-wav',
                    'aif' => 'audio/x-aiff',
                    'aifc' => 'audio/x-aiff',
                    'aiff' => 'audio/x-aiff',
                    'jpe' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'jpg' => 'image/jpeg',
                    'png'  => 'image/png',
                    'svg' => 'image/svg+xml',
                    'tif' => 'image/tiff',
                    'tiff' => 'image/tiff',
                    'gif' => 'image/gif',
                    'txt' => 'text/plain',
                    'xml' => 'text/xml',
                    'css' => 'text/css',
                    'htm' => 'text/html',
                    'html' => 'text/html',
                    'js' => 'application/x-javascript',
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'vcf' => 'text/x-vcard',
                    'vrml' => 'x-world/x-vrml',
                    'zip'  => 'application/zip'
                ]
            ];
        } else {
            return false;
        }
    }
}