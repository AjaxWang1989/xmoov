<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午12:57
 */

namespace Zoran\Xmoov\Stream;


class XmoovStreamLog
{
    private $config = null;
    public function log($eventType = null, $event = null, $conf = null)
    {
        if ($conf){
            $this->config = $conf;
        }
        $eventStamp    = '[' . date('m.d.Y, H:i:s') . ', ' . $this->getIP() . ']';
        $eventProvider = defined('XS_PROVIDER') ? 'server: (' . XS_PROVIDER . ')' : '';
        $eventFile     = isset($this->config['file']) ? 'file: (' . $this->config['file'] . ')' : '';
        $showEvent     = false;
        $notifyEvent   = false;
        $logEvent      = false;
        $eventString  = '';
        switch ($eventType) {
            case 'error' :
                switch ($event) {
                    case 'file_open' :
                        $eventString = ('Could not open');
                        break;
                    case 'file_empty' :
                        $eventString = ('A file was not set');
                        break;
                    case 'file_path' :
                        $eventString = ('Server path does not exist (' . $this->config['file_path'] . ')');
                        break;
                    case '404' :
                        $eventString = ('File not found');
                        break;
                    case 'security' :
                        $eventString = ('Security error! A possible hack was attempted');
                        break;
                    case 'file_type' :
                        $eventString = ('Mime type not allowed');
                        break;
                    case 'time_limit' :
                        $eventString = ('Could not set time limit');
                        break;
                    case 'magic_quotes' :
                        $eventString = ('Could not set magic_quotes_runtime');
                        break;
                    default :
                        $eventString = ('unknown error');
                        break;
                }

                if (isset($this->config['show_errors']))
                    $showEvent = $this->doOutput($this->config['show_errors'], $event);
                if (isset($this->config['log_errors']))
                    $logEvent = $this->doOutput($this->config['log_errors'], $event);
                if (isset($this->config['notify_email']) && isset ($this->config['notify_errors']))
                    $notifyEvent = $this->doOutput($this->config['notify_errors'], $event);
                break;
            case 'activity' :
                if (isset($this->config['log_activity']))
                    $logEvent = $this->doOutput($this->config['log_activity'], $event);
                $eventString = $event;
                break;
        }

        $eventOutput = $eventStamp . ' xmoovStream ' . strtoupper($eventType) . ': [' . $eventString . '] '
            . $eventFile . ' ' . $eventProvider;

        if ($showEvent)
            $this->displayEvent($eventOutput);

        if ($notifyEvent)
            $this->notifyEvent($eventType, $eventOutput);

        if ($logEvent)
            $this->logEvent($eventType, $eventOutput);
    }

    # check configuration for output conditions
    protected function doOutput($conf, $event)
    {
        if (is_bool($conf))
            return $conf;
        if (is_array($conf) && in_array($event, $conf))
            return true;
        if (!is_array($conf) && (strtolower((string)$conf) == 'all' || $conf == $event))
            return true;
        return false;
    }

    # output event to the browser
    protected function displayEvent($output)
    {
        echo $output;
    }

    # send email notification
    protected function notifyEvent($subject, $message)
    {
        if ($this->config['notify_email']){
            mail($this->config['notify_email'], ' xmoovStream ' . $subject, $message,
                'From: noreply@' . $_SERVER['HTTP_HOST'] . '\r\n' .
                'Reply-To: noreply@' . $_SERVER['HTTP_HOST'] . "\r\n" . 'X-Mailer: PHP/' . phpversion());
        }
    }

    # output event to log file
    protected function logEvent($eventType, $output)
    {
        if ($logfile = $this->logFile ($eventType) && is_writable($this->config['logs'])) {
            if (function_exists('file_put_contents')) {
                file_put_contents($logfile, $output."\n", FILE_APPEND);
            } else {
                $f = @fopen($logfile, 'ab');
                if ($f) {
                    $bytes = fwrite($f, $output."\n");
                    fclose($f);
                    return $bytes;
                }
            }
        }
        return false;
    }

    # get current logfile or create it if it doesn't exist
    protected function logFile($eventType)
    {
        if (!is_writable($this->config['logs']))
            return false;
        $logfile = $this->config['logs'] . '/' . date('m-d-Y') . '.' . $eventType . '.log';
        if (!file_exists($logfile)) {
            if ($this->createLogfile($logfile)) {
                return $logfile;
            }
            return false;
        }
        return $logfile;
    }

    # try to create logfile
    protected function createLogfile($file)
    {
        $fileHandle = fopen($file, 'w');
        if ($fileHandle) {
            fclose($fileHandle);
            return true;
        }
        return false;
    }

    # get client ip
    public function getIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}