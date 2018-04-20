<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午4:12
 */

namespace Zoran\Xmoov\Servers;


use Zoran\Xmoov\Stream\XmoovStreamToken;

class PrivateImageServer extends ImageServer
{
    /**
     * @var XmoovStreamToken
     * */
    protected $token = null;

    public function setToken(XmoovStreamToken $token) {
        $this->token = $token;
    }

    public function init()
    {
        if ($this->request->get('key') && $this->request->get('file')) {
            if($this->token->isValid ($this->request->get('file'), $this->request->get('key'))) {
                $file = $this->request->get('file');
            } else {
                $file = 'no_access.jpg';
            }
            return [
                'file' => $file,
                'file_path' => $this->config['file_path'] . '/protected_images/',
                'force_download' => 0,
                'burst_size' => 0,
                'throttle' => 0,
                'mime_types' => [
                    'jpe' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'jpg' => 'image/jpeg',
                    'png'  => 'image/png',
                    'svg' => 'image/svg+xml',
                    'tif' => 'image/tiff',
                    'tiff' => 'image/tiff',
                    'gif' => 'image/gif'
                ]
            ];
        }
        else
        {
            return false;
        }
    }
}