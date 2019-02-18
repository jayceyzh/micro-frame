<?php

require_once dirname(__DIR__) . "/mpfLoad.php";

class MapAutoloaderClassTest extends PHPUnit_Framework_TestCase{
    
    public function setup(){
        \mpf\core\MapAutoloaderClass::$isRegister = false;
        \mpf\core\MapAutoloaderClass::$maps = [];
    }
    
    public function testAddmaps(){
        $maps = [
            'test1'=>'filepath1',
            'test2'=>'filepath2'
        ];
        
        \mpf\core\MapAutoloaderClass::addMaps($maps);
        
        $this->assertEquals($maps,\mpf\core\MapAutoloaderClass::$maps);
    }
    
    public function testRegister(){
        \mpf\core\MapAutoloaderClass::register();
        $this->assertEquals(\mpf\core\MapAutoloaderClass::$isRegister,true);
    }
    
    public function testAutoload(){
        \mpf\core\MapAutoloaderClass::addMaps([
            'ClassExample\test'=>__DIR__ . '/ClassExample/test.php'
        ]);
        $result = \mpf\core\MapAutoloaderClass::autoload('ClassExample\test');
        $this->assertEquals($result,true);
    }
    
}
