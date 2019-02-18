<?php
namespace mpf\service;
/**
 * systemService
 * 系统服务的类，方便写系统服务
 * systemService::__construct($executeFile,$params,$pidFile)
 * systemService::execCmd()
 */
class systemService{
    protected $executeFile;
    protected $params;
    protected $pidFile;
    /**
     * systemService::__construct()
     * 
     * @param string $executeFile 可执行文件
     * @param array $params 参数
     * @param string $pidFile pid文件
     * @return
     */
    public function __construct($executeFile,$params,$pidFile){
        ini_set('display_errors', true);
        if( PHP_SAPI != 'cli' ){
            throw new \Exception('只能运行在cli模式下面！');
        }
        global $argv;
        if( empty($argv[1]) ){
            throw new \Exception('命令行第一个参数不能为空！');
        }
        if( !in_array($argv[1],['start','stop','restart']) ){
            throw new \Exception('参数不正确！');
        }
        $this->params = $params;
        $this->executeFile = $executeFile;
        $this->pidFile = $pidFile;
    }
    /**
     * systemService::execCmd()
     * 执行cmd命令
     * @return
     */
    public function execCmd(){
        global $argv;
        if( $argv[1] == 'start' ){
            $this->start();
        }elseif( $argv[1] == 'stop' ){
            $this->stop();
        }elseif( $argv[1] == 'restart' ){
            $this->stop();
            echo '.';
            sleep(1);
            echo '.';
            sleep(1);
            echo '.' . PHP_EOL;
            $this->start();
        }
    }
    /**
     * systemService::start()
     * 启动
     * @return
     */
    protected function start(){
        if( is_file($this->pidFile) ){
            $pid = file_get_contents($this->pidFile);
            if( posix_kill($pid,0) ){
                return;
            }
        }
        pcntl_exec($this->executeFile,$this->params);
    }
    /**
     * systemService::stop()
     * 停止
     * @return
     */
    protected function stop(){
        if( is_file($this->pidFile) ){
            $pid = file_get_contents($this->pidFile);
            @unlink($this->pidFile);
            if( posix_kill($pid,0) ){
                posix_kill($pid,SIGTERM);
            }
        }else{
            echo "没有找到pid文件..." . PHP_EOL;
        }
    }
}