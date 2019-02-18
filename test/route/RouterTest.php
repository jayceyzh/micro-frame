<?php

require_once dirname(__DIR__) . "/mpfLoad.php";

class RouterTestClassTest extends PHPUnit_Framework_TestCase{
    public $RouterTest;
    public function setup(){
        
    }
    
    public function testGetRoute1(){
        $Router = new \mpf\route\Router(__DIR__,"");
        $Route = $Router->getRoute();
        $this->assertInstanceOf('\mpf\route\Route',$Route);
    }
    
    public function testGetRoute2(){
        $Router = new \mpf\route\Router(__DIR__,"",'cli');
        $Route = $Router->getRoute();
        $this->assertInstanceOf('\mpf\route\Route',$Route);
    }
    
    public function testGetRoute3(){
        $_SERVER["DOCUMENT_URI"] = "/";
        $Router = new \mpf\route\Router(__DIR__,"",'urlRewrite');
        $Route = $Router->getRoute();
        $this->assertInstanceOf('\mpf\route\Route',$Route);
    }
    /**
     * @expectedException \Exception
     */
    public function testGetRoute4(){
        $Router = new \mpf\route\Router(__DIR__,"",'not exist type');
        $Route = $Router->getRoute();
        $this->assertInstanceOf('\mpf\route\Route',$Route);
    }

    public function testGetRoute5(){
        $Router = new \mpf\route\Router(__DIR__,"");
        $Route = $Router->getRoute();
        ob_clean();
        ob_start();
        $Route->run();
        $str = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($str,'test');
    }
    
}
