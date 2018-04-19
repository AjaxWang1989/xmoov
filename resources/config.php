<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 下午12:04
 */

return [
    'token_key' => '4b0a7',
    'token_expires' => 3600,
    'logs' => '',
    'file' => null,
    'output_file_name' => null,
    'file_path' => XS_DIR.'../resources/',
    'use_error_handling' => true,//http://localhost/tts/video/video/&video=1.flv
    'use_activity_handling' => true,
    'log_errors' => [
        'file_open', 'file_empty',
        'file_path', '404',
        'security', 'file_type',
        'time_limit', 'magic_quotes'
    ],
    'show_errors' => [
        'file_open', 'file_empty',
        'file_path', '404',
        'security', 'file_type'
    ],
    'notify_errors' => [
        'file_open', 'file_empty',
        'file_path', '404',
        'security', 'file_type'
    ],
    'notify_email' => null,
    'log_activity' => [
        'flv_random_access', 'file_access',
        'download_complete', 'partial_download_complete',
        'user_abort'
    ],
    'use_http_range' => 1,
    'force_download' => 0,
    'burst_size' => 0,
    'buffer_size' => 500,
    'throttle' => 0,
    'mime_types' => [
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        'f4v' => 'video/mp4',
        'f4p' => 'video/mp4',
        'mp4' => 'video/mp4',
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