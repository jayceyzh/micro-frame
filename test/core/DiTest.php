<?php

namespace test;

use \mpf\core\Di;

require_once dirname(__DIR__) . "/mpfLoad.php";

class DispatcherTest extends \PHPUnit_Framework_TestCase{
    
    private $Di;
    
    public function setup(){
        $this->Di = new Di;            
    }
    
    public function testSet(){
        $this->Di->register('example1','\test\di_service_example');
        $service = $this->Di->get('example1');
        $this->assertInstanceOf('\test\di_service_example',$service);
        $service->shared = true;
        $service1 = $this->Di->get('example1');
        $this->assertEquals($service1->shared,false);
    }
    
    public function testSetShared(){
        $this->Di->register('example1','\test\di_service_example',true);
        $service = $this->Di->get('example1');
        $this->assertInstanceOf('\test\di_service_example',$service);
        $service->shared = true;
        $service1 = $this->Di->get('example1');
        $this->assertEquals($service1->shared,true);
    }
    
    public function testGet(){
        
        $this->Di->register('example1','\test\di_service_example');
        $service = $this->Di->get('example1');
        $this->assertInstanceOf('\test\di_service_example',$service);
        
        $this->Di->register('example2',function($param1,$param2){
            return new \test\di_service_example($param1,$param2);
        });
        
        $service2 = $this->Di->get('example2',[1,2]);
        $this->assertEquals($service2->plus(),3);
    }
    
    public function testHas(){
        $this->Di->register('example1','\test\di_service_example');
        $this->assertEquals($this->Di->has('example1'),true);
        
        $this->Di->register('example2','\test\di_service_example',true);
        $this->assertEquals($this->Di->has('example2'),true);
    }
    
    public function testRemove(){
        $this->Di->register('example1','\test\di_service_example');
        $this->assertEquals($this->Di->has('example1'),true);
        $this->Di->remove('example1');
        $this->assertEquals($this->Di->has('example1'),false);
    }
    
    public function testArrayAccessDi(){
        $this->Di->register('example1','\test\di_service_example');
        $this->assertInstanceOf('\test\di_service_example',$this->Di['example1']);
    }
    
    public function testObjectAccessDi(){
        $this->Di->register('example1','\test\di_service_example');
        $this->assertInstanceOf('\test\di_service_example',$this->Di->example1);
    }
    
    public function testBetchRegister(){
        $dis = [
            'example1'=>['class'=>'\test\di_service_example','shared'=>false]
        ];
        $this->Di->betchRegister($dis);
        $this->assertInstanceOf('\test\di_service_example',$this->Di['example1']);
    }
    
}

class di_service_example{
    
    public $shared = false;
    
    public $param1;
    
    public $param2;
    
    public function __construct($param1 = 0,$param2 = 0){
        $this->param1 = $param1;
        $this->param2 = $param2;    
    }
    
    public function plus(){
        return $this->param1+$this->param2;
    }
}
