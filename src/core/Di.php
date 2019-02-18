<?php

namespace mpf\core;

/**
 * Di依赖注入
 * Di::get($name,$params=array())
 * Di::has($name)
 * Di::remove($name)
 * Di::register($name, $class, $shared = false)
 * Di::removeAll()
 */
class Di implements \ArrayAccess
{
    //服务列表
    private $_bindings = array(); 
    //实例列表
    private $_instances = array();
    //单例
    static $Di;

    public function __construct(){
        if( empty($Di) ){
            self::$Di = $this;
        }
        return self::$Di;
    }
    /**
     * Di::get()
     * 获取组件的实例
     * @param string $name 组件的名称
     * @param array $params  参数 
     * @return object 组件的对象
     */
    public function get($name, $params = array())
    {
        //看组件是否已实例化，如果已实例化，返回组件的实例对象
        if (isset($this->_instances[$name])) {
            return $this->_instances[$name];
        }
        
        //检测是否存在为$name的组件
        if (! isset($this->_bindings[$name])) {
            return null;
        }

        $concrete = $this->_bindings[$name]['class']; 

        $obj = null;
        //匿名方法
        if ($concrete instanceof \Closure) {
            $obj = call_user_func_array($concrete, $params);
        }
        //字符串
        elseif (is_string($concrete)) {
            if (empty($params)) {
                $obj = new $concrete;
            } else {
                //使用反射的具有参数的类实例
                $class = new \ReflectionClass($concrete);
                $obj = $class->newInstanceArgs($params);
            }
        }
        //如果它是一个共享组件，然后写_instances列表，下次直接检索
        if ($this->_bindings[$name]['shared'] == true && $obj) {
            $this->_instances[$name] = $obj;
        }

        return $obj;
    }

    /**
     * Di::has()
     * 检测吃否存在为$name的组件
     * @param string $name 组件名称
     * @return boolean true | false
     */
    public function has($name)
    {
        return isset($this->_bindings[$name]) or isset($this->_instances[$name]);
    }

    /**
     * Di::remove()
     * 卸载组件
     * @param string $name 组件名称
     * @return boolean true | false
     */
    public function remove($name)
    {
        unset($this->_bindings[$name], $this->_instances[$name]);
    }

    /**
     * Di::register()
     * 注册组件
     * @param sring $name 组件名称
     * @param mixed $class 对象 | 字符串类名称 | 匿名方法
     * @param bool $shared 是否共享
     * @return
     */
    public function register($name, $class, $shared = false)
    {
        if (! ($class instanceof \Closure) && is_object($class)) {
            $this->_instances[$name] = $class;
        } else {
            $this->_bindings[$name] = array("class" => $class, "shared" => $shared);
        }
    }
    
    /**
     * Di::betchRegister()
     * 批量注册组件
     * @param $dis [
     *      name=>[
     *          'shared'=>boolean,
     *          'class'=>
     *      ]
     * ]
     * @return
     */
    public function betchRegister($dis){
        foreach( $dis as $name=>$di ){
            if( isset($di['shared']) && $di['shared'] == true ){
                Di::$Di->register($name,$di['class'],true);
            }else{
                Di::$Di->register($name,$di['class']);
            }
        }
    }

    /**
     * Di::offsetExists()
     * ArrayAccess接口，测试组件的存在
     * @param mixed $offset 组件名称
     * @return boolean true | false
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Di::offsetGet()
     * ArrayAccess接口，访问[$offset]组件
     * @param mixed $offset 组件名称
     * @return object 组件的实例
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Di::offsetSet()
     * ArrayAccess接口，组件的注册
     * @param mixed $offset 组件名称
     * @param mixed $value 实例对象
     * @return boolean true | false
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * Di::offsetUnset()
     * arrayaccess接口，unset的方法来卸载组件
     * @param mixed $offset
     * @return boolean true | false
     */
    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }
    
    /**
     * Di::__get()
     * 允许通过$Di->$name方式来访问
     * @param string $name 组件名称
     * @return 组件实例
     */
    public function __get($name){
        if( !$this->has($name) ){
            throw new \Exception("has not a service of \"$name\"");
        }
        return $this->get($name);
    }
    
    /**
     * Di::removeAll()
     * 移除所有的组件
     * @return
     */
    public function removeAll(){
        foreach( $this->_bindings as $name=>$bind ){
            $this->remove($name);
        }
    }
}
