<?php

namespace mpf\cache;

/**
 * Cache
 * 通过抽象类的方式提供缓存要用到的方法，定义需要实现的方法。
 * 缓存类继承ArrayAccess，来方便使用。
 * 
 * ****以下是对外提供的方法****
 * public Cache::buildKey($key)
 * public Cache::get($key)
 * public Cache::exists($key)
 * public Cache::mget($keys)
 * public Cache::set($key, $value, $duration = 0)
 * public Cache::mset($items, $duration = 0)
 * public set($key, $value, $duration = 0)
 * public add($key, $value, $duration = 0)
 * public madd($items, $duration = 0)
 * public delete($key)
 * public function flush()
 * 
 * ****以下是必须实现的方法****
 * abstract getValue($key)
 * abstract deleteValue($key)
 * abstract addValue($key, $value, $duration)
 * 
 * ****可选择实现;比如有跟高的效率可以实现一下；比如说add和set的差异****
 * protected function setValue($key, $value, $duration)
 * protected function flushValues()
 * protected function getValues($keys)
 * protected function setValues($items, $duration)
 * protected function addValues($items, $duration)
 */
abstract class Cache implements \ArrayAccess
{
    //前缀
    public $keyPrefix;

    /**
     * Cache::buildKey()
     * 生成缓存的key
     * @param mixed $key 
     * @return string
     */
    public function buildKey($key)
    {
        if (is_string($key)) {
            $key = strlen($key) <= 32 ? $key : md5($key);
        } else {
            $key = md5(json_encode($key));
        }
        return $this->keyPrefix . $key;
    }

    /**
     * Cache::get()
     * 获取缓存数据
     * @param mixed $key
     * @return
     */
    public function get($key)
    {
        $key = $this->buildKey($key);
        return $this->getValue($key);
    }

    /**
     * Cache::exists()
     * 缓存数据的数据是否存在
     * @param mixed $key
     * @return
     */
    public function exists($key)
    {
        $key = $this->buildKey($key);
        $value = $this->getValue($key);
        return $value !== false;
    }

    /**
     * Cache::mget()
     * 批量缓存数据
     * @param mixed $keys
     * @return
     */
    public function mget($keys)
    {
        $keyMap = [];
        foreach ($keys as $key) {
            $keyMap[$key] = $this->buildKey($key);
        }
        $values = $this->getValues(array_values($keyMap));
        $results = [];
        foreach ($keyMap as $key => $newKey) {
            $results[$key] = false;
            if (isset($values[$newKey])) {
                $results[$key] = $values[$newKey];
            }
        }
        return $results;
    }

    /**
     * Cache::set()
     * 缓存数据
     * @param mixed $key
     * @param mixed $value
     * @param integer $duration
     * @return boolean
     */
    public function set($key, $value, $duration = 0)
    {
        $key = $this->buildKey($key);
        return $this->setValue($key, $value, $duration);
    }

    /**
     * Cache::mset()
     * 批量缓存数据
     * @param mixed $items
     * @param integer $duration
     * @return boolean
     */
    public function mset($items, $duration = 0)
    {
        $data = [];
        foreach ($items as $key => $item) {
            $key = $this->buildKey($key);
            $data[$key] = $item;
        }
        return $this->setValues($data, $duration);
    }

    /**
     * Cache::add()
     * 增加缓存数据
     * @param mixed $key 
     * @param mixed $value data
     * @param integer $duration expire time
     * @return mixed
     */
    public function add($key, $value, $duration = 0)
    {
        $key = $this->buildKey($key);
        return $this->addValue($key, $value, $duration);
    }

    /**
     * Cache::madd()
     * 批量增加缓存数据
     * @param mixed $items [[key,value]]
     * @param integer $duration
     * @return boolean
     */
    public function madd($items, $duration = 0)
    {
        $data = [];
        foreach ($items as $key => $item) {
            $key = $this->buildKey($key);
            $data[$key] = $item;
        }
        return $this->addValues($data, $duration);
    }

    /**
     * Cache::delete()
     * 删除缓存数据
     * @param mixed $key
     * @return boolean
     */
    public function delete($key)
    {
        $key = $this->buildKey($key);
        return $this->deleteValue($key);
    }

    /**
     * Cache::flush()
     * 删除所有缓存
     * @return
     */
    public function flush()
    {
        $this->flushValues();
    }
    
    /**
     * Cache::addValue()
     * 
     * @param string $key
     * @param mixed $value
     * @param mixed $duration expire time
     * @return
     */
    abstract protected function addValue($key, $value, $duration);
    /**
     * Cache::getValue()
     * 
     * @param string $key
     * @return
     */
    abstract protected function getValue($key);

    

    /**
     * Cache::deleteValue()
     * 
     * @param string $key
     * @return
     */
    abstract protected function deleteValue($key);
    
    /**
     * Cache::addValue()
     * 
     * @param string $key
     * @param mixed $value
     * @param mixed $duration expire time
     * @return
     */
    protected function setValue($key, $value, $duration){
        $this->addValue($key, $value, $duration);
    }
    /**
     * Cache::flushValues()
     * 
     * @return
     */
    protected function flushValues(){
        throw new \Exception("没有实现这个方法！");
    }

    /**
     * Cache::getValues()
     * 
     * @param string $keys
     * @return
     */
    protected function getValues($keys){
        $values = [];
        foreach( $keys as $key ){
            $values[$key] = $this->getValue($key);
        }
        return $values;
    }

    /**
     * Cache::setValues()
     * 
     * @param array $items [[key,value]]
     * @param int $duration expire time
     * @return
     */
    protected function setValues($items, $duration){
        foreach( $items as $key=>$item ){
            $this->addValue($key,$item,$duration);
        }
    }

    /**
     * Cache::addValues()
     * 
     * @param array $items [[key,value]]
     * @param int $duration expire time
     * @return
     */
    protected function addValues($items, $duration){
        foreach( $items as $key=>$item ){
            $this->addValue($key,$item,$duration);
        }
    }

    /**
     * Cache::offsetExists()
     * 
     * @param string $key
     * @return
     */
    public function offsetExists($key)
    {
        return $this->exists($key);
    }

    /**
     * Cache::offsetGet()
     * 
     * @param string $key
     * @return
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Cache::offsetSet()
     * 
     * @param string $key
     * @param mixed $value
     * @return
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Cache::offsetUnset()
     * 
     * @param string $key
     * @return
     */
    public function offsetUnset($key)
    {
        $this->delete($key);
    }

}
