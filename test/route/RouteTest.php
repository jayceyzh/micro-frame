<?php

require_once dirname(__DIR__) . "/mpfLoad.php";

class RouteTest extends PHPUnit_Framework_TestCase{
    
    public $Route;
    public function setup(){
        $file = __DIR__ . '/indexController.php';
        $this->Route = new \mpf\route\Route($file,'indexController','index');
    }
    
    public function testRun(){
        ob_clean();
        ob_start();
        $this->Route->run();
        $str = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($str,'test');
    }
    
}
