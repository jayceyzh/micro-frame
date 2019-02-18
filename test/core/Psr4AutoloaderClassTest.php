<?php
require_once dirname(__DIR__) . "/mpfLoad.php";
class Psr4AutoloaderClassTest extends PHPUnit_Framework_TestCase{
    
 	public function setup(){
		\mpf\core\Psr4AutoloaderClass::register();
	}
    
    public function testRegister(){
        $this->assertEquals(\mpf\core\Psr4AutoloaderClass::$isRegister,true);    
    }
    
    public function testAddNamespace(){
        \mpf\core\Psr4AutoloaderClass::addNamespace('\ClassExample\Example1',__DIR__ . '/');
        $this->assertEquals(isset(\mpf\core\Psr4AutoloaderClass::$prefixes['ClassExample\Example1\\']),true);
    }
    
    public function testLoadClass(){
		//var_dump(__DIR__);return;
        \mpf\core\Psr4AutoloaderClass::addNamespace('\ClassExample\Example1',__DIR__ . '/ClassExample/Example1');
        \mpf\core\Psr4AutoloaderClass::addNamespace('\ClassExample\Example2',__DIR__ . '/ClassExample/Example2');
        $A = new \ClassExample\Example1\A;
        $B = new \ClassExample\Example1\B;
        
        $Test = new \ClassExample\Example1\Test\Test1;
        
        $A1 = new \ClassExample\Example2\A;
        $B1 = new \ClassExample\Example2\B;
    }
    
}
