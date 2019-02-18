<?php
namespace mpf\cache;
use \mpf\cache\Cache;
/**
 * FileCache
 * 文件缓存
 */
class FileCache extends Cache{
    
    protected $levelCharNum;
    protected $cacheDir;
    /**
     * FileCache::__construct()
     * 
     * @param string $cacheDir 缓存的目录
     * @param integer $level 缓存目录深度，默认4层
     * @return
     */
    public function __construct($cacheDir = '/tmp/mpfWebCache',$level = 4){
        if( !is_dir($cacheDir) ){
            mkdir($cacheDir);
        }
        $this->cacheDir = $cacheDir;
        if( $level > 32 ){
            $level = 32;
        }elseif( $level < 0 ){
            $level = 4;
        }
        
        $this->levelCharNum = (int)(32/$level);
    }
    /**
     * FileCache::getFile()
     * 根据缓存key来获取缓存所在文件位置
     * @param string $key 缓存的key
     * @return
     */
    public function getFile($key){
        if(strlen($key) != 32) {
            $key = md5($key);
        }
        $dirs = [];
        $start = 0;
        do{
            $dirs[] = substr($key,$start,$this->levelCharNum);
            $start += $this->levelCharNum;
        }while($start<32);
        $file = array_pop($dirs);
        $dir =  $this->cacheDir . '/' . implode('/',$dirs);
        if( !is_dir($dir) ){
            mkdir($dir,0755,true);
        }
        return $dir . '/' . $file;
    }
    /**
     * FileCache::getValue()
     * 根据缓存key来获取缓存值
     * @param string $key 缓存的key
     * @return 
     */
    protected function getValue($key){
        $file = $this->getFile($key);
        if( !is_file($file) ){
            return false;
        }
        $str = file_get_contents($file);
        $d = unserialize($str);
        if( $d[0] > time() ){
            return $d[1];
        }else{
            return false;
        }
    }

    /**
     * FileCache::addValue()
     * 添加缓存
     * @param string $key 缓存的key
     * @param mixed $value 缓存的value
     * @param int $duration 缓存的时间
     * @return
     */
    protected function addValue($key, $value, $duration){
        if( $duration == 0 ){
            $duration = 24*3600*365*30;
        }
        $file = $this->getFile($key);
        $d[0] = time() + $duration;
        $d[1] = $value;
        $str = serialize($d);
        file_put_contents($file,$str);
    }


    /**
     * FileCache::deleteValue()
     * 删除缓存
     * @param string $key 缓存的key
     * @return
     */
    protected function deleteValue($key){
        $file = $this->getFile($key);
        if( is_file($file) ){
            unlink($file);
        }
    }
  
}