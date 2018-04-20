<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>　
// +----------------------------------------------------------------------

namespace Zoran\Xmoov;


use Symfony\Component\Process\Process;

class FlvStreamHandle implements StreamHandler
{
    const CHUNK_LENGTH = 1024;

    const ENCRYPT_LEVEL = 1760;

    const ENCRYPT_ASCII_OFFSET = 6;

    const ENCRYPT_ASCII_MODE = 16;

    public  function encode($file, $encrypt = true)
    {
        $flvTool2 = xmoovPath('/tools/flvtool2/flvtool2') ;
        $process  = new Process("{$flvTool2} -U {$file}");
        $process->run();
        if(!$encrypt){
            return ;
        }
        $filename      = basename($file);
        $origin          = $file;
        $originJM        = dirname($file) . '\\' . $filename . 'jm';
        $jmFileHandle = @fopen($originJM, "w");

        if ($fileHandle = fopen($origin, 'rb')) {
            while (true) {
                $startPosition = ftell($fileHandle);
                $currentChunkContent          = fread($fileHandle, self::CHUNK_LENGTH);
                $endPosition = ftell($fileHandle);
                if (!$currentChunkContent) {
                    break;
                } else {
                    $encryptLevel = self::ENCRYPT_LEVEL;
                    $encryptPosition   = $startPosition + ($encryptLevel - ($startPosition % $encryptLevel) - 1);
                    while ($encryptPosition < $endPosition) {
                        $encryptHexStr = bin2hex($currentChunkContent[$encryptPosition - $startPosition]);
                        $encryptedStr   = "";
                        $count = strlen($encryptHexStr);
                        for ($index = 0; $index < $count; $index++) {
                            $hex = hexdec($encryptHexStr[$index]);
                            $hex = $hex + self::ENCRYPT_ASCII_OFFSET;
                            $hex = $hex % self::ENCRYPT_ASCII_MODE;
                            $encryptedStr .= dechex($hex);
                        }
                        $currentChunkContent[$encryptPosition - $startPosition] = pack("H*", $encryptedStr);
                        $encryptPosition += ($encryptLevel);
                    }
                    fwrite($jmFileHandle, $currentChunkContent);
                }
            }

        }
        fclose($fileHandle);
        fclose($jmFileHandle);
        unlink($origin);
        rename(dirname($file) . '\\' . $filename . 'jm', $origin);
    }

    public  function decrypt($fileHandle, $bufferSize)
    {
        $encryptLevel = self::ENCRYPT_LEVEL;
        $startPosition = ftell($fileHandle);
        $currentChunkContent = fread($fileHandle, $bufferSize);
        $endPosition = ftell($fileHandle);
        $encryptPosition = $startPosition + ($encryptLevel - ($startPosition % $encryptLevel) - 1);
        while($encryptPosition < $endPosition){
            $encryptHexStr = bin2hex($currentChunkContent[$encryptPosition - $startPosition]);
            $encryptedStr = '';
            $count = strlen($encryptHexStr);
            for($index = 0; $index < $count; $index ++){
                $hex = hexdec($encryptHexStr[$index]);
                $hex = $hex - self::ENCRYPT_ASCII_OFFSET;
                if($hex < 0)
                    $hex = $hex + self::ENCRYPT_ASCII_MODE;
                $encryptedStr .= dechex($hex);
            }
            $currentChunkContent[$encryptPosition-$startPosition] = pack("H*", $encryptedStr);
            $encryptPosition += ($encryptLevel);
        }

        return $currentChunkContent;
    }
}