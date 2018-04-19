<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午12:40
 */

namespace Zoran\Xmoov\Stream;


use Zoran\Xmoov\StreamHandler;

class XmoovStream
{
    const CHUNK_BUFFER_LENGTH = 1024;

    # xmoovStream version

    private $version = '0.8.4b';

    # minimum php version

    private $minPhpVersion = '5.5';

    /**
     * @var StreamHandler
     * */
    private $streamHandler = null;

    # file to stream

    private $file = null;

    # modified date

    private $fileModified = 0;

    # parial file download

    private $partialDownload = 0;

    # http range request

    private $httpRangeDownload = 0;

    # file name to be sent to the client

    private $fileName = null;

    # configuration array from server.inc file

    private $config = [];

    # mime type

    private $mime = null;

    # buffer size

    private $bufferSize = 8;

    # start seek position

    private $seekStart = 0;

    # end seek position

    private $seekEnd = -1;

    # original file size

    private $fileSize = 0;

    # human readable original file size

    private $humanFileSize = 0;

    # output file size

    private $contentLength = 0;

    # event handler class

    /**
     * @var XmoovStreamLog
     * */
    private $xsLog = 0;

    # magic_quotes_runtime setting memory

    private $mqrM = 0;


    public function __construct(StreamHandler $handler = null, array $defaults = null, array $config = null)
    {
        $this->streamHandler = $handler;
        if ($defaults) {
            $this->setConfig($defaults);
        }
        if ($config){
            $this->setConfig($config);
        }
    }


    /**
     * XmoovStream object initialization function
     * @param boolean $start
     * @return boolean
     * */
    public function init($start = false)
    {
        # initialize logging class
        if (class_exists('XmoovStreamLog')
            && (isset($this->config['use_error_handling']) || isset($this->config['use_activity_handling']))
            && ($this->config['use_error_handling'] || $this->config['use_activity_handling'])
        ) {
            $this->xsLog = new XmoovStreamLog();
        }
        # start download
        if (isset($this->config['file'])) {
            if ($this->setFile($this->config['file'])) {
                if ($start){
                    $this->download();
                }
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * parse configuration arguments
     * @param string|array|null $args
     * */
    public function setConfig($args = null)
    {
        # make sure arguments have been defined
        if (isset($args)) {
            $config = null;
            # check if the arguments are an array
            if (is_array($args)) {
                $config = $args;
            } else {
                # assume arguments as a string and parse into an array
                parse_str($args, $config);
            }
            # final check if the arguments are an array,if so parse into the configuration
            if (is_array($config)){
                $this->config = array_merge($this->config, $config);
            }
        }
    }


    # http range

    protected function httpRange()
    {
        global $HTTP_SERVER_VARS;
        if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
            if (isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
                $seekRange = substr($HTTP_SERVER_VARS['HTTP_RANGE'], strlen('bytes='));
            } else {
                $seekRange = substr($_SERVER['HTTP_RANGE'], strlen('bytes='));
            }
            $range = explode('-', $seekRange);
            if ($range[0] > 0) {
                $this->seekStart = intval($range[0]);
            }
            if ($range[1] > 0) {
                $this->seekEnd = intval($range[1]);
            } else {
                $this->seekEnd = -1;
            }
            $this->partialDownload = true;
            $this->httpRangeDownload = true;
        }
    }


    # header output

    protected function header()
    {
        header('Pragma: public');
        # force file download dialog
        if (isset($this->config['force_download']) && $this->config['force_download']) {
            if (ini_get('zlib.output_compression')){
                ini_set('zlib.output_compression', 'Off');
            }
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/download');
            header("Content-type: $this->mime");
            header("Content-Disposition: attachment; file_name=$this->fileName;");
        } else {
            header('Cache-Control: public');
            header("Content-type: $this->mime");
            header("Content-Disposition: inline; file_name=$this->fileName;");
        }
        header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T', $this->fileModified));
        header("Content-Transfer-Encoding: binary\n");
        if ($this->httpRangeDownload) {
            header('HTTP/1.0 206 Partial Content');
            header('Status: 206 Partial Content');
            header('Accept-Ranges: bytes');
            header("Content-Range: bytes $this->seekStart-$this->seekEnd/$this->fileSize");
        }
        header("Content-Length: $this->contentLength");
    }


    # begin download
    public function download()
    {
        # turn off php error reporting
        error_reporting(0);
        if (isset($this->config['position']))
            $this->seekStart = intval($this->config['position']);
        if (isset($this->config['buffer_size']))
            $this->bufferSize = intval($this->config['buffer_size']) * self::CHUNK_BUFFER_LENGTH;

        #try to set time limit to 0 for large file support
        if (!$this->timeLimit()){
            $this->log('error', 'time_limit');
        }

        # keep binary data safe
        if (!$this->setMagicQuotes()){
            $this->log('error', 'magic_quotes');
        }

        # check http range header
        if (isset($this->config['use_http_range']) && $this->config['use_http_range']) {
            $this->httpRange();
            # check seek positions
            if ($this->seekEnd < $this->seekStart){
                $this->seekEnd = $this->fileSize - 1;
            }
        }

        # set content length
        $this->contentLength = $this->seekEnd - $this->seekStart + 1;
        # open the file
        if (!$fileHandle = fopen($this->file, 'rb')) {
            $this->log('error', 'file_open');
            $this->disconnect();
        }

        # output the header
        $this->header();
        # seek if we have to
        if ($this->seekStart > 0) {
            $this->partialDownload = true;
            fseek($fileHandle, $this->seekStart);
            # print flv header if we have to
            if ($this->mime === 'video/x-flv') {
                echo 'FLV',
                pack('C', 1),
                pack('C', 1),
                pack('N', 9),
                pack('N', 9);

                $this->log('activity', 'flv_random_access');
            }
        } else {
            $this->log('activity', 'file_access');
        }

        # get ready for take off
        $speed = 0;
        $bytesSent = 0;
        $chunk = 1;
        $throttle = isset($this->config['throttle']) ? $this->config['throttle'] : false;
        $burst = isset($this->config['burst_size']) ? $this->config['burst_size'] * self::CHUNK_BUFFER_LENGTH : 0;

        # output file
        while (!(connection_aborted() || connection_status() == 1) && $bytesSent < $this->contentLength) {
            #st buffer size after the first burst has been sent
            if ($bytesSent >= $burst){
                $speed = $throttle;
            }
            # make sure we don't read past the total file size
            if ($bytesSent + $this->bufferSize > $this->contentLength){
                $this->bufferSize = $this->contentLength - $bytesSent;
            }
            # send data
            if($this->streamHandler){
                echo $this->streamHandler->decrypt($fileHandle, $this->bufferSize);
            }else{
                echo fread($fileHandle, $this->bufferSize);
            }

            $bytesSent += $this->bufferSize;
            # clean up
            flush();
            ob_flush();
            #throttle
            if ($speed && ($bytesSent - $burst > $speed * $chunk * self::CHUNK_BUFFER_LENGTH)) {
                //sleep(1);
                $chunk++;
            }
        }
        fclose($fileHandle);
        if ($bytesSent == $this->fileSize) {
            $this->log('activity', 'download_complete');
        } else {
            if ($bytesSent == $this->contentLength) {
                $this->log('activity', 'partial_download_complete');
            } else {
                $this->log('activity', 'user_abort');
            }
        }
        $this->disconnect();
    }


    # exit script

    public function disconnect()
    {
        $this->setMagicQuotes(true);
        exit();
    }


    # set file and check itegrity
    public function setFile($file)
    {
        # make sure the file was set
        if (isset($file) && $file != '') {
            if ($file != $this->config['file']) {
                $this->config['file'] = $file;
            }
        } else {
            $this->log('error', 'file_empty');
            return false;
        }
        # make sure we are not being hacked
        /*
        if(eregi('(^\.\.?)',$file) || eregi(basename($_SERVER['PHP_SELF']),$file) || eregi('.php',$file)){
            $this->log('error','security');
            return false;
        }
        */

        # make sure server path is a directory
        if (isset($this->config['file_path']) && is_dir($this->config['file_path'])) {
            $file = $this->config['file_path'] . $file;
        } else {
            $this->log('error', 'file_path');
            return false;
        }

        # make sure the file exists
        if (!file_exists($file)) {
            $this->log('error', '404');
            return false;
        }

        # make sure file type is allowed
        $this->mime = $this->getMimeType($file);
        if (!$this->mime) {
            $this->log('error', 'file_type');
            return false;
        }

        # make sure the file is ok and finally set the file privateiables after all this paranoia
        if (is_readable($file) && is_file($file)) {
            $this->fileName = isset($this->config['output_file_name']) ? $this->config['output_file_name'] :
                basename($file);
            if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')){
                $this->fileName = preg_replace('/\./', '%2e', $this->fileName,
                    substr_count($this->fileName, '.') - 1);
            }
            $this->fileModified = filemtime($file);
            $this->fileSize = filesize($file);
            $this->humanFileSize = $this->convertHumanFileSize($this->fileSize);
            $this->file = $file;
            return true;
        }
        return false;
    }


    # mime

    public function getMimeType($file = null)
    {
        if(!$file) {
            if (isset($this->config['file'])) {
                $file = $this->config['file'];
            } else {
                return false;
            }
        }
        $ext=strtolower(eregi_replace("^(.*)\.","",$file));
        return(isset($this->config['mime_types'][$ext])) ? $this->config['mime_types'][$ext] : false;
    }


    # pass events to event handler
    protected function log($event_type = null, $event = null)
    {
        if ($this->xsLog) {
            $this->xsLog->log($event_type, $event, $this->config);
        }

    }


    # convert file size

    protected function convertHumanFileSize($size)
    {
        /** @var integer $i */
        $i = floor(log($size, self::CHUNK_BUFFER_LENGTH));
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        return $size ? round($size / pow(self::CHUNK_BUFFER_LENGTH, $i),
                2) . $sizes[$i] : '0 Bytes';
    }


    public function getFileSize($human = false)
    {
        return $human ? $this->convertHumanFileSize($this->fileSize) : $this->fileSize;
    }


    # try to set script time limit to 0

    protected function timeLimit()
    {
        if (ini_get('safe_mode')) {
            return false;
        }
        set_time_limit(0);
        return true;
    }


    # try to set magic quotes runtime

    public function setMagicQuotes($reset = false)
    {
        if ($this->mqrM) {
            set_magic_quotes_runtime($this->mqrM);
            return true;
        }

        if (function_exists('get_magic_quotes_runtime')) {
            $this->mqrM = get_magic_quotes_runtime();
            return set_magic_quotes_runtime(0);
        }
        return false;
    }
}