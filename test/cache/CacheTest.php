<?php

require_once dirname(__DIR__) . "/mpfLoad.php";

class MemCacheTest extends \PHPUnit_Framework_TestCase{
    
    private $cache;
    
    public function setup(){
        $this->cache = new \mpf\cache\MemCache('',[['127.0.0.1',11211]],'test_','','',[]);
    }
    
    public function testBuildKey(){
        $key = '1234';
        $key = $this->cache->buildKey($key);
        $this->assertEquals($key,'test_1234');
        $key = 'abcdefgkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkk';
        $len = strlen($key);
        $newKey = $this->cache->buildKey($key);
        $this->assertEquals(37,strlen($newKey));
    }
    
    public function testGet(){
        $this->cache->set('abc2',1,0);
        $result = $this->cache->get('abc2');
        $this->assertEquals($result,1);
    }
    
    public function testSet(){
        $this->cache->set('abc2',1,0);
        $result = $this->cache->get('abc2');
        $this->assertEquals($result,1);
        $this->cache->set('abc1',2,1);
        sleep(1.1);
        $result = $this->cache->get('abc1');
        $this->assertEquals($result,false);
    }
    
    public function testMget(){
        $this->cache->set('test1',1,0);
        $this->cache->set('test2',1,0);
        $result = $this->cache->mget(['test1','test2']);
    }
    
    public function testMset(){
        $items = [
            'key1'=>1,
            'key2'=>2
        ];
        $keys = ['key1','key2'];
        $this->cache->mset($items,0);
        $result = $this->cache->mget($keys);
        $this->assertEquals($result,$items);
        
        $this->cache->mset($items,1);
        sleep(1.1);
        $result = $this->cache->get($keys);
        $this->assertEquals($result,false);
    }
    
    public function testAdd(){
        $this->cache->delete('abc2');
        $this->cache->add('abc2',1,0);
        $result = $this->cache->get('abc2');
        $this->assertEquals($result,1);
        $this->cache->delete('abc2');
        $this->cache->add('abc2',2,1);
        sleep(1.1);
        $result = $this->cache->get('abc2');
        $this->assertEquals($result,false);
        
        $this->cache->add('abc2',1,0);
        $result = $this->cache->add('abc2',1);
        $this->assertEquals($result,false);
    }
    
    public function testMadd(){
        $items = [
            'key1'=>1,
            'key2'=>2
        ];
        $keys = ['key1','key2'];
        $this->cache->madd($items,0);
        $result = $this->cache->mget($keys);
        $this->assertEquals($result,$items);
        
        $this->cache->delete('key1');
        $this->cache->delete('key2');
        
        $this->cache->mset($items,1);
        sleep(1.1);
        $result = $this->cache->get($keys);
        $this->assertEquals($result,false);
    }
    
    public function testDelete(){
        $this->cache->set('abc',1);
        $this->cache->delete('abc');
        $result = $this->cache->get('abc');
        $this->assertEquals($result,false);
    }
    
    public function testFlush(){
        $this->cache->set('abc',1);
        $this->cache->set('abc1',1);
        $this->cache->flush();
        $result = $this->cache->get('abc');
        $this->assertEquals($result,false);
        $result = $this->cache->get('abc1');
        $this->assertEquals($result,false);
    }
    
}
