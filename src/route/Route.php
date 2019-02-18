<?php
namespace mpf\route;
/**
 * Route
 * Router::__construct($file,$class,$action)
 * Router::run()
 */
class Route{
    //文件
    protected $file;
    //controller类
    protected $class;
    //动作
    protected $action;
    //类的实例
    protected $controller;
    
    /**
     * Route::__construct()
     * 
     * @param string $file 文件
     * @param string $class controller类
     * @param string $action 动作
     * @return
     */
    public function __construct($file,$class,$action){
        $this->file = $file;
        $this->class = $class;
        $this->action = $action;
    }
    /**
     * Route::run()
     * 
     * @return
     */
    public function run(){
        require_once $this->file;
        $this->controller = new $this->class;
        call_user_func(array($this->controller,$this->action . 'Action'));
    }
}