<?php

namespace mpf\cache;

use \mpf\cache\Cache;

/**
 * MemCache
 * 提供memcached的缓存封装类。
 */
class MemCache extends Cache
{

    private $mem;

    /**
     * MemCache::__construct()
     * 
     * @param string $persistent_id  presistent ID
     * @param array $servers   [[$host,$port,$weight],[$host1,$port,$weight]]
     * @param string $keyPrefix   cache key prefix
     * @param string $user  username of memcache use sasl authentication
     * @param string $password  password of memcache use sasl authentication
     * @param string $options  memcached options
     * @return
     */
    public function __construct($persistent_id, $servers, $keyPrefix = "memPrefix_", $user = "", $password = "", $options = "") 
    {
        $this->keyPrefix = $keyPrefix;
        if (! extension_loaded("Memcached")) {
            throw new \ErrorException('extension Memcached is not Loaded!');
        }
        $this->mem = new \Memcached($persistent_id);
        if ($persistent_id != "" && count($this->mem->getServerList()) != 0) {
            
        } else {
            
            //$this->mem->setOptions($options);
            $this->mem->setOption(\Memcached::OPT_COMPRESSION,false);
            $this->mem->setOption(\Memcached::OPT_BINARY_PROTOCOL,true);
            $this->mem->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
            $result = $this->mem->addServers($servers);
            if ($result != true) {
                throw new \ErrorException('Memcached::addServers failed!');
            }
            if ($user != "" && $password != "") {
                $this->mem->setSaslAuthData($user, $password);
            }
            /*
            if ($this->mem->getResultCode() != \Memcached::RES_SUCCESS) {
                throw new \ErrorException($this->mem->getResultMessage());
            }
            */
        }

    }

    /**
     * MemCache::getMem()
     * get memcached instance
     * @return Memcached
     */
    public function getMem()
    {
        return $this->mem;
    }

    /**
     * MemCache::getValue()
     * get memcache data
     * @param string $key
     * @return mixed  cache data
     */
    protected function getValue($key)
    {
        return $this->mem->get($key);
    }

    /**
     * MemCache::setValue()
     * set memcache data
     * @param string $key
     * @param mixed $value
     * @param int $duration  expire time,0 is not expire;
     * @return boolean
     */
    protected function setValue($key, $value, $duration)
    {
        return $this->mem->set($key, $value, $duration);

    }

    /**
     * MemCache::addValue()
     * add a memcache value
     * @param string $key
     * @param mixed $value
     * @param int $duration expire time,0 is not expire;
     * @return boolean
     */
    protected function addValue($key, $value, $duration)
    {
        return $this->mem->add($key, $value, $duration);
    }

    /**
     * MemCache::deleteValue()
     * delete memcache value
     * @param string $key
     * @return boolean
     */
    protected function deleteValue($key)
    {
        return $this->mem->delete($key);
    }

    /**
     * MemCache::flushValues()
     * delete all cache
     * @return
     */
    protected function flushValues()
    {
        return $this->mem->flush();
    }

    /**
     * MemCache::getValues()
     * batch get cache
     * @param array $keys [$key1,$key2,$key3]
     * @return array [$key1=>$value1,$key2=>$value2,$key3=>$value3]
     */
    protected function getValues($keys)
    {
        /*
        return $this->mem->getMulti($keys, NULL, \Memcached::GET_PRESERVE_ORDER);
        */
        
        return $this->mem->getMulti($keys, NULL);
    }

    /**
     * MemCache::setValues()
     * batch set values
     * @param array $items  [$key1=>$value1,$key2=>$value2,$key3=>$value3]
     * @param int $duration expire time,0 is not expire;
     * @return boolean
     */
    protected function setValues($items, $duration)
    {
        return $this->mem->setMulti($items, $duration);
    }

    /**
     * MemCache::addValues()
     * 
     * @param array $items  [$key1=>$value1,$key2=>$value2,$key3=>$value3]
     * @param int $duration expire time,0 is not expire;
     * @return true
     */
    protected function addValues($items, $duration)
    {
        foreach ($items as $key => $item) {
            $this->mem->add($key, $item, $duration);
        }
        return true;
    }
    
    public function __destruct(){
        $this->mem = null;
    }

}
