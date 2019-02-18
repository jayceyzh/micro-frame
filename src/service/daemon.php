<?php
namespace mpf\service;
/**
 * daemon
 * 守护进程
 * daemon::__construct($osUser='www',$logFile='/tmp/mpf_service_daemon.log',$pidFile='/tmp/mpf_daemon.pid')
 * daemon::registerService($execFile,$args = [])
 * daemon::run()
 */
class daemon{
    protected $osUser;
    protected $logFile;
    protected $services = [];
    protected $pidFile;
    /**
     * daemon::__construct()
     * 
     * @param string $osUser 系统用户
     * @param string $logFile 日志文件
     * @param string $pidFile pid文件
     * @return
     */
    public function __construct($osUser='www',$logFile='/tmp/mpf_service_daemon.log',$pidFile='/tmp/mpf_daemon.pid'){
        ini_set('display_errors', true);
        if( PHP_SAPI != 'cli' ){
            throw new \Exception('只能运行在cli模式下面！');
        }
        if(!extension_loaded('pcntl')){
            throw new \Exception('没有安装pcntl扩展！');
        }
        if( !is_dir(dirname($logFile)) ){
            throw new \Exception(dirname($logFile) . '目录不存在！');
        } 
        if( !is_dir(dirname($pidFile)) ){
            throw new \Exception(dirname($pidFile) . '目录不存在！');
        }
        $this->osUser = $osUser;
        $this->logFile = $logFile;
        $this->pidFile = $pidFile;
    }
    /**
     * daemon::registerService()
     * 注册服务
     * @param string $execFile 可执行文件
     * @param mixed $args 参数
     * @return
     */
    public function registerService($execFile,$args = []){
        if( !is_executable($execFile) ){
            throw new \Exception($execFile . "不是一个可执行文件！");
        }
        $this->services[] = [
            'execFile'=>$execFile,
            'args'=>$args,
            'pid'=>null
        ];
    }
    /**
     * daemon::checkServices()
     * 检测服务是否启动，没有启动则启动
     * @return
     */
    protected function checkServices(){
        foreach( $this->services as $key=>$service ){
            $pid = $service['pid'];
            if( empty($pid) ||  !posix_kill($pid,0) ){
                $pid = pcntl_fork();
                if( $pid == 0 ){
                    $this->setUserAndGroup();
                    pcntl_exec($service['execFile'],$service['args']);
                    exit;
                }else{
                    if( posix_kill($pid,0) ){
                        $str = "启动服务成功:" . $service['execFile'] . ' ' . implode(' ',$service['args'])  . ';pid=' . $pid . PHP_EOL;
                    }else{
                        $str = "启动服务失败:" . $service['execFile'] . ' ' . implode(' ',$service['args'])  . ';pid=' . $pid . PHP_EOL;
                    }
                    $this->log($str);
                    $this->services[$key]['pid'] = $pid;
                }
            }
        }
    }
    /**
     * daemon::run()
     * 开始运行
     * @return
     */
    public function run(){
        $pid = @file_get_contents( $this->pidFile );
        if( !empty($pid) && posix_kill($pid,0) ){
            if (!function_exists('posix_isatty') || posix_isatty(STDOUT)) {
                echo "程序已启动..." . PHP_EOL;
            }
            exit;
        }
        $this->processDaemon();
        $this->register_SIG();
        $this->checkServices();
        file_put_contents( $this->pidFile,posix_getpid() );
        $this->log('程序启动...');
        sleep(2);
        while(1){
            if( time() % 10 == 0 ){
                ob_start();
                $this->checkServices();
                $msg = ob_get_contents();
                ob_end_clean();
                if (!function_exists('posix_isatty') || posix_isatty(STDOUT)) {
                    echo $msg;
                }
            }
            pcntl_signal_dispatch();
            sleep(1);
        }
    }
    /**
     * daemon::processDaemon()
     * 守护进程
     * @return
     */
    protected function processDaemon(){
        fclose(STDIN);
        $pid = pcntl_fork();
        if( $pid > 0 ){
            exit(0); 
        }
        posix_setsid();
    }
    /**
     * daemon::setUserAndGroup()
     * 设置进程的用户和用户组
     * @return
     */
    protected function setUserAndGroup(){
        
        $user_info = posix_getpwnam($this->osUser);
        if (!$user_info) {
            $this->log("Warning: User {$this->osUser} not exsits");
            return;
        }
        $uid = $user_info['uid'];
        $gid = $user_info['gid'];
    
        if ($uid != posix_getuid() || $gid != posix_getgid()) {
            if (!posix_setgid($gid) || !posix_initgroups($user_info['name'], $gid) || !posix_setuid($uid)) {
                $this->log("Warning: change gid or uid fail.");
            }
        }
    }
    
    /**
     * daemon::register_SIG()
     * 注册信号
     * @return
     */
    protected function register_SIG(){
        pcntl_signal(SIGCHLD, function($signo){
            while( 1 ){
                $pid = pcntl_wait($status,WNOHANG);
                if( $pid == -1 || $pid == 0 ){
                    return;
                }
            }
        });
        $obj = $this;
        pcntl_signal(SIGTERM, function($signo) use($obj){
            foreach( $obj->services as $service ){
                posix_kill($service['pid'],SIGKILL);
                for( $i=0;$i<3000;$i++ ){
                    $pid = pcntl_wait($status,WNOHANG);
                    if( $pid == -1 || $pid == 0 ){
                        break;
                    }
                    usleep(1000);
                }
            }
            @unlink($this->pidFile);
            $this->log("程序退出...");
            exit;
        });
    }
    
    /**
     * daemon::log()
     * 记录日志
     * @param string $str
     * @return
     */
    protected function log($str){
        $str = date("Y-m-d H:i:s") . ' ' . trim($str) . PHP_EOL;
        file_put_contents($this->logFile,$str,FILE_APPEND);
    }
    
}