<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/19
 * Time: 上午11:59
 */

define("XS_DIR", dirname(__FILE__));

if(!function_exists('xmoovPath')){
    function xmoovPath($path = ""){
        return dirname(dirname(__FILE__)).$path;
    }
}