<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 上午11:12
 */

namespace Zoran\Xmoov;


use Symfony\Component\Process\Process;

class FFMPEGService
{
    protected $_command = 'ffmpeg';

    public function __construct()
    {
        if (DIRECTORY_SEPARATOR === '/') {
            $this->_command = 'ffmpeg';
        } else {
            $this->_command =  xmoovPath('/tools/ffmpeg');
        }
    }

    public function execute($arg)
    {
        $command = $this->_command.' '.$arg;
        $process = new Process($command);
        $process->setTimeout(0);
        $process->run();
    }
}