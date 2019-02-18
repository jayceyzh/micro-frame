<?php

namespace mpf\core;
/**
 * ErrorHandler
 * 捕获错误。
 * 打印错误。（define("APP_DEBUG",true)开启打印错误，define("APP_DEBUG",false)关闭打印错误）
 * 记录错误日志。
 * ErrorHandler::registerLog()
 * ErrorHandler::register()
 * ErrorHandler::unregister()
 * ErrorHandler::renderException()
 */

class ErrorHandler
{
    //callable $logCallable 记录日志的方法
    protected $logCallable;
    
    /**
     * ErrorHandler registerLog
     * 注册记录日志的方法
     * @throw 错误日志记录的方法已经定义
     * @param callable $logCallable 记录日志的回调方法
     * @return
     */
    public function registerLog(callable $logCallable){
        if( $this->logCallable != null ){
            try{
                throw new \Exception("ErrorHandler registerLog has set");
            }catch( \Exception $e ){
                $this->renderException($e);
            }
        }  
        $this->logCallable = $logCallable;
    }
    /**
     * ErrorHandler::register()
     * 关闭PHP默认显示错误信息，添加自定义捕获的异常和错误的方法
     * @return
     */
    public function register(){
        ini_set('display_errors', false);
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'fatalErrorShutDown']);
    }

    /**
     * ErrorHandler::unregister()
     * 恢复以前的错误处理函数和异常处理函数
     * @return
     */
    public function unregister(){
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * ErrorHandler::handleException()
     * 异常捕获方法
     * @param Exception $exception 异常
     * @return
     */
    public function handleException($exception){
        if (PHP_SAPI !== 'cli') {
            http_response_code(500);
        }
        $this->renderException($exception);
    }

    /**
     * ErrorHandler::handleError()
     * 捕捉错误的方法
     * @param int $code 错误代码
     * @param string $message 错误消息
     * @param string $file 错误的文件
     * @param int $line 错误在那一行
     * @param array $traces 错误追踪
     * @return
     */
    public function handleError($code, $message, $file, $line, $traces){
        if (error_reporting() & $code ) {
            try{
                throw new \ErrorException($message, $code, $code, $file, $line);
            }catch( \ErrorException $e ){
                $this->renderException($e);
            }
        }
    }

    /**
     * ErrorHandler::fatalErrorShutDown()
     * 捕获致命错误的方法
     * @return
     * 
     */
    public function fatalErrorShutDown(){
        $error = error_get_last();
        if ( $error != null ) {
            $exception = new \ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
            $this->renderException($exception);
        }
    }

    /**
     * ErrorHandler::renderException()
     * 渲染的异常
     * @param Exception $exception 异常
     * @return
     */
    public function renderException($exception)
    {

        $msg = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();

        if (defined('APP_TRACE') && APP_TRACE == true) {
            $traceStr = $exception->getTraceAsString();
            $traceStr = sprintf("<pre>%s</pre>", $traceStr);
        } else {
            $traceStr = "";
        }
        
        if (method_exists($exception, 'getName')) {
            $name = $exception->getName(); 
        }elseif ( $exception instanceof \ErrorException ) {
            $name = $this->getName($exception);
        }elseif ($exception instanceof \Exception){
            $name = get_class($exception);
        } else {
            $name = 'unknown exception';
        }
        if( !empty($traceStr) ){
            $traceStr = PHP_EOL .$traceStr;
        }
        $htmlStr = sprintf('<div style="font-size:16px;color:black;font-family:Consolas;"><b style="font-size:18px">%s:</b>%s ' . PHP_EOL . 'in file <b>%s</b> on line <b>%s</b>%s</div>', $name, $msg, $file,$line, $traceStr);

        $str = strip_tags($htmlStr);
        if( $this->logCallable != null ){
            call_user_func($this->logCallable,$str);
        }
        if( defined("APP_DEBUG") && APP_DEBUG == true ){
            if (PHP_SAPI == 'cli' ) {
                echo $str . PHP_EOL;
            } else {
                echo $htmlStr;
            }
        }
    }

    /**
     * ErrorHandler::getName()
     * 获取错误级别对应的名称
     * @param mixed $exception 异常
     * @return string 名称
     */
    public function getName($exception)
    {
        static $names = [
            E_COMPILE_ERROR => 'PHP Compile Error', 
            E_COMPILE_WARNING => 'PHP Compile Warning', 
            E_CORE_ERROR => 'PHP Core Error', 
            E_CORE_WARNING =>'PHP Core Warning', 
            E_DEPRECATED => 'PHP Deprecated Warning', 
            E_ERROR => 'PHP Fatal Error', 
            E_NOTICE => 'PHP Notice', 
            E_PARSE => 'PHP Parse Error',
            E_RECOVERABLE_ERROR => 'PHP Recoverable Error', 
            E_STRICT => 'PHP Strict Warning', 
            E_USER_DEPRECATED => 'PHP User Deprecated Warning', 
            E_USER_ERROR =>'PHP User Error', 
            E_USER_NOTICE => 'PHP User Notice', 
            E_USER_WARNING => 'PHP User Warning', 
            E_WARNING => 'PHP Warning'
        ];

        return isset($names[$exception->getCode()]) ? $names[$exception->getCode()] : 'Error';
    }

}
