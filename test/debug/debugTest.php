<?php
require_once dirname(__DIR__) . "/mpfLoad.php";
class QueryTest extends PHPUnit_Framework_TestCase{
    
    public $debug;
    
	public function setup(){
        $this->debug = new \mpf\debug\Debug();    
    }
    
    public function testLog(){
        !defined('APP_DEBUG') && define('APP_DEBUG',true);
        $this->debug->log('123',0);
        $this->debug->log('123',1);
        $this->debug->log('123',2);
    }
    
    public function testSafeEcho(){
        \mpf\debug\Debug::safeEcho('safe echo!');
    }
    
    public function testDump(){
        
        \mpf\debug\Debug::dump(['abc'=>'123','ab'=>2]);
        \mpf\debug\Debug::dump_format_json(['abc'=>'123','ab'=>2]);
        \mpf\debug\Debug::var_export(['abc'=>'123','ab'=>2]);
        
    }
    public function renderData(){
        $data = ['abc'=>'123','ab'=>2];
        $str = \mpf\debug\Debug::renderData($data,\mpf\debug\Debug::FORMAT_JSON);
        $this->assert(json_decode($str,true),$data);
        $str = \mpf\debug\Debug::renderData($data,\mpf\debug\Debug::FORMAT_STRING);
        $this->assert(json_decode($str,true),$data);
        $str = \mpf\debug\Debug::renderData($data,\mpf\debug\Debug::FORMAT_VAR_EXPORT);
    }
}