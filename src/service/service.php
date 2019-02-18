<?php
namespace mpf\service;
abstract class service{
    protected $sleep;
    protected $stdoutFile;
    protected $filesize;
    protected $logFile;
    protected $logFilesize;
    protected $debug;
    public function __construct($stdoutFile = '',$filesize = 1000000,$sleep=2){
        $this->stdoutFile = $stdoutFile;
        $this->filesize = $filesize;
        $this->logFile = $stdoutFile;
        $this->logFilesize = $filesize;
        $this->sleep = $sleep;
    }
    public function setLogFile($file,$filesize){
        $this->logFile = $file;
        $this->logFilesize = $filesize;
    }
    public function debug(){
        $this->debug = true;
    }
    abstract protected function safeRun();
    final function run( $loop = false  ){
        do{
            ob_start();
            try{
                $this->safeRun();
            }catch( \Exception $e ){
                $this->log((string)$e);
                echo (string)$e . PHP_EOL;
            }
            $print = ob_get_contents();
            ob_end_clean();
            $this->stdout($print);
            if( $loop && !empty($this->sleep) ){
                if( $this->sleep < 1 ){
                    usleep($this->sleep*1000000);
                }
                sleep($this->sleep);
                echo 5555;
            }
        }while( $loop );
    }
    protected function stdout($print){
        if( $print === null ){
            return;
        }
        if (!function_exists('posix_isatty') || posix_isatty(STDOUT)) {
            if( $this->debug ){
                echo $print;
            }
        }
        if( is_file($this->stdoutFile) && filesize($this->stdoutFile) > $this->filesize ){
            file_put_contents($this->stdoutFile,'');
        }
        if( is_dir(dirname($this->stdoutFile)) ){
            $print = date("Y-m-d H:i:s") . PHP_EOL . $print;
            file_put_contents($this->stdoutFile,$print,FILE_APPEND);
        }
    }
    public function log($msg,$isPrint=false){
        if( is_file($this->logFile) && filesize($this->logFile) > $this->logFilesize ){
            file_put_contents($this->logFile,'');
            
        }
        if( is_dir(dirname($this->logFile)) ){
            $msg = date("Y-m-d H:i:s") . PHP_EOL . $msg . PHP_EOL;
            file_put_contents($this->logFile,$msg,FILE_APPEND);
        }
        if( $isPrint ){
            if (!function_exists('posix_isatty') || posix_isatty(STDOUT)) {
                echo $msg;
            }
        }
    }
}