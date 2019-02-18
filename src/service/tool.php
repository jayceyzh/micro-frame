<?php
namespace mpf\service;
class tool{
    static function safeEcho($str){
        if (!function_exists('posix_isatty') || posix_isatty(STDOUT)) {
            echo $str;
        }
    }
}