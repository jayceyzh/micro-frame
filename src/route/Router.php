<?php

namespace mpf\route;

/**
 * Router
 * 通过get参数r（也就是$_GET['r']）作为路由解析的参数。
 * 通过url（也就是$_SERVER['DOCUMENT_URI']）作为路由解析的参数。
 * 通过命令行的参数（也就是命令行的$argv）作为路由解析的参数。
 * 根据参数获取要加载的controller文件、要实例化的controller类、要调用的action方法
 * Router::__construct($dir,$namespace,$type='http_get_param_r')
 * Router::getRoute()
 * Router::getRouteByKey($key)
 * Router::addErrorRoute($class,$action)
 */
class Router{
    
    //controller命名空间前缀
    protected $namespace;
    //controller文件目录
    protected $dir;
    //参数的类型 http_get_param_r | urlRewrite | cli
    protected $type;
    //路由的数组
    protected $routes;
    //错误的路由
    protected $ErrorRoute;
    //当前路由
    public $route;
    
    /**
     * Router::__construct()
     * 
     * @param string $dir controller文件目录
     * @param string $namespace controller文件目录
     * @param string $type 参数的类型 http_get_param_r | urlRewrite | cli
     * @return
     */
    public function __construct($dir,$namespace,$type='http_get_param_r'){
        $this->dir = $dir;
        $this->namespace = $namespace;
        $this->type = $type;
    }
    /**
     * Router::getRoute()
     * 获取路由
     * @param string $key 路由的key
     * @param string $dir controller文件目录 
     * @param string $namespace controller命名空间前缀
     * @return
     */
    public function getRoute($key=null,$dir=null,$namespace=null){
        if( $dir == null ){
            $dir = $this->dir;
        }
        if( $namespace == null ){
            $namespace = $this->namespace;
        }
        if( $this->type == "http_get_param_r" ){
            $route = $this->httpGetParamR();
        }elseif( $this->type == 'cli' ){
            $route = $this->cli();
        }elseif( $this->type == 'urlRewrite' ){
            $route = $this->urlRewrite();
        }else{
            throw new \Exception("router type $this->type. is not exist!");
        }
        if( count($route) == 0 ){
            $route = ['index','index'];
        }elseif( count($route) == 1 ){
            if( $route[0] == "" ){
                $route = ['index','index'];
            }else{
                $route = [$route[0],'index'];
            }
        }elseif( count($route) == 2 ){
            if($route[1] == ""){
                $route[1] = 'index';
            }
            $route = [$route[0],$route[1]];
        }elseif( count($route) > 2 ){
            $route = [$route[0] . '\\' . $route[1],$route[2]];
        }
        $file = $this->dir . '/' . $route[0] . '.php';
        $class = $this->namespace . '\\' . $route[0];
        $class = preg_replace("/[\/\\\]/",'\\',$class);
        $file = preg_replace("/[\/\\\]/",DIRECTORY_SEPARATOR,$file);
        $this->route = $route;
        $Route = new Route($file,$class,$route[1]);
        try{
            if( $key == null ){
                $this->routes[] = $Route;
            }else{
                $this->routes[$key] = $Route;
            }
            return $Route;
        }catch(\Exception $e){
            if( !$this->ErrorRoute && (!defined('APP_DEBUG') || APP_DEBUG == false) ){
                return $this->ErrorRoute;
            }else{
                throw $e;
            }
        }
    }
    /**
     * Router::getRouteByKey()
     * 通过key获取路由
     * @param string $key key
     * @return
     */
    public function getRouteByKey($key){
        if( isset($this->routes[$key]) ){
            return $this->routes[$key];
        }
    }
    /**
     * Router::addErrorRoute()
     * 添加错误的路由
     * @param string $class 类
     * @param string $action 动作
     * @return
     */
    public function addErrorRoute($class,$action){
        $file = $this->dir . '/' . $class . '.php';
        $this->ErrorRoute = new Route($file,$class,$action);
        return $this->ErrorRoute;
    }
    /**
     * Router::httpGetParamR()
     * 通过get参数r（也就是$_GET['r']）作为路由解析的参数。
     * @return
     */
    protected function httpGetParamR(){
        if( isset($_GET['r']) ){
            if( substr($_GET['r'],0,1) == '/' ){
                throw new \Exception('first char can\'t is /');
            }
            return explode('/',$_GET['r']);
        }else{
            return [];
        }
    }
    /**
     * Router::cli()
     * 通过命令行的参数（也就是命令行的$argv）作为路由解析的参数。
     * @return
     */
    protected function cli(){
        global $argv;
        if( PHP_SAPI != 'cli' ){
            throw new \Exception('router type cli must in php cli!');
        }
        return array_slice($argv,1);
    }
    /**
     * Router::urlRewrite()
     * 通过url（也就是$_SERVER['DOCUMENT_URI']）++++++++++++++作为路由解析的参数。
     * @return
     */
    protected function urlRewrite(){        
        if( isset($_SERVER['REQUEST_URI']) ){
            if( strpos($_SERVER['REQUEST_URI'],$_SERVER['SCRIPT_NAME']) === 0 ){
                $uri = substr($_SERVER['REQUEST_URI'],strlen($_SERVER['SCRIPT_NAME']));
                $uri = substr($uri,1);
            }else{
                $uri = substr($_SERVER['REQUEST_URI'],1);
            }
            if( strpos($uri,'?') !== false ){
                $uri = substr($uri,0,strpos($uri,'?'));
            }
            $route = explode('/', $uri);

        }elseif( isset($_SERVER['DOCUMENT_URI']) ){
            
            $uri = substr($_SERVER['DOCUMENT_URI'],1);
            if( strpos($uri,'?') !== false ){
                $uri = substr($uri,0,strpos($uri,'?'));
            }
            $route = explode('/', $uri);
        }else{
            throw new \Exception('$_SERVER["DOCUMENT_URI"] or $_SERVER["REQUEST_URI"] is not exist!');
        }
        return $route;
    }
}