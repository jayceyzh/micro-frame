<?php

namespace mpf\core;

/**
 * Psr4AutoloaderClass
 * psr4规范自动加载
 * Psr4AutoloaderClass::register()
 * Psr4AutoloaderClass::addNamespace($prefix, $base_dir)
 * Psr4AutoloaderClass::loadClass($class)
 */
class Psr4AutoloaderClass{
    /**
     * @var array key是命名空间前缀和值的关联数组是命名空间中类的基本目录数组。
     */
    static $prefixes = array();
    /**
     * @boolean $isRegister 是否已注册
     */
    static $isRegister = false;
    /**
     * Psr4AutoloaderClass:register()
     * 调用sql_autoload_register
     * @return void
     */
    static function register(){
        if( self::$isRegister == false ){
            spl_autoload_register('\mpf\core\Psr4AutoloaderClass::loadClass');
            self::$isRegister = true;
        }
    }

    /**
     * Psr4AutoloaderClass::addNamespace($prefix,$base_dir)
     * 为命名空间前缀添加基础目录。
     * @param string $prefix 命名空间前缀
     * @param string $base_dir 类文件的基本目录
     * @return void
     */
    static function addNamespace($prefix, $base_dir){
        $prefix = trim($prefix, '\\') . '\\';
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';
        self::$prefixes[$prefix] = str_replace( '/',DIRECTORY_SEPARATOR,$base_dir );
    }

    /**
     * Psr4AutoloaderClass::loadClass($class)
     * 为给定类名加载类文件。
     *
     * @param string $class 类名称
     * @return mixed 成功的映射文件名；或失败时，返回false.
     * 
     */
    static function loadClass($class){
        
        $matchPrefixes = [];
        foreach( self::$prefixes as $key=>$prefix ){
            if( stripos($class,$key) === 0 ){
                $matchPrefixes[] = $key;
            }
        }
        if( $matchPrefixes == [] ){
            return false;
        }
        arsort($matchPrefixes);
        $path = str_replace($matchPrefixes[0],self::$prefixes[ $matchPrefixes[0] ],$class);
        $path = str_replace("\\",DIRECTORY_SEPARATOR,$path);
        $path = str_replace("/",DIRECTORY_SEPARATOR,$path);
        $path .= '.php';
        if( is_file($path) ){
            require_once $path;
        }else{
            return false;
        }
    }

}
