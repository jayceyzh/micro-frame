<?php
require_once dirname(__DIR__) . "/mpfLoad.php";
class ErrorHandlerTest extends PHPUnit_Framework_TestCase{
    
    public $ErrorHandler;
 	public function setup(){
        $this->ErrorHandler = new \mpf\core\ErrorHandler;
	}
    /**
     * @expectedException TypeError
     */
    public function testRegisterLogErrorParam(){
        $this->assertEquals(\mpf\core\Psr4AutoloaderClass::$isRegister,true);    
        $a = null;
        $this->ErrorHandler->registerLog($a);
    }
    
    public function testRegisterLog(){
        $a = function(){
            
        };
        $this->ErrorHandler->registerLog($a);
    }
    public function testRegister(){
        $this->ErrorHandler->register();
    }
    public function testUnRegister(){
        $this->ErrorHandler->register();
    }
    
    public function testHandleException(){
        try{
            throw new \Exception("test");
        }catch( \Exception $e ){
            $this->ErrorHandler->handleException($e);
        }
    }
    public function testHandlerError(){
        $code = 1;
        $message = "test";
        $file = "test.php";
        $line = 2;
        $traces = [];
        $this->ErrorHandler->handleError($code, $message, $file, $line, $traces);
    }
    public function testFatalErrorShutDown(){
        $this->ErrorHandler->fatalErrorShutDown();
    }
    public function testRenderException(){
        //defined("APP_DEBUG") && define("APP_DEBUG",true);
        try{
            throw new \Exception("test");
        }catch( \Exception $e){
            $this->ErrorHandler->renderException($e);
        }
    }
    public function testGetName(){
        try{
            throw new \Exception("test");
        }catch( \Exception $e ){
            $name = $this->ErrorHandler->getName($e);
            $this->assertEquals($name,'Error');    
        }
    }
    
}
