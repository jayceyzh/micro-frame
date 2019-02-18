<?php

namespace mpf\core;

/**
 * MapAutoloaderClass
 * 以map形式加载类文件
 */
class MapAutoloaderClass
{
    //加载的类文件的hash数组
    static $maps = [];
    //是否已注册
    static $isRegister = false;

    /**
     * MapAutoloaderClass::autoload()
     * 加载的类的回调方法
     * @param string $className 类名称
     * @return
     */
    static function autoload($className)
    {
        if ( isset(self::$maps[$className]) ) {
            $file = self::$maps[$className];
            if (is_file($file)) {
                require_once $file;
                return true;
            }
        }
        return false;
    }

    /**
     * MapAutoloaderClass::addMaps()
     * 增加类的加载
     * @param array $classes [类=>路径]
     * @return
     */
    static function addMaps($classes)
    {
        self::$maps = array_merge(self::$maps, $classes);
    }
    
    /**
     * MapAutoloaderClass::register()
     * 调用spl_autoload_register
     * @return
     */
    static function register()
    {
        if( !self::$isRegister ){   
            spl_autoload_register('self::autoload');
            self::$isRegister = true;
        }
    }
}
