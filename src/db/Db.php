<?php

namespace mpf\db;

/**
 * Db
 * PDO支持多种数据库的操作，也就是说PDO已经封装的很好了，就对PDO和PDOStatement进行一个简单的封装。
 * 增加数据库操作的异常处理。
 * 
 * 相对PDO新增方法
 * Db::connDb
 * Db::getConn
 * Db::closeConn
 * 
 * 在PDO的prepare基础上，重写prepare方法
 * Db::prepare
 * 
 * 以下方法是对PDO类的重载
 * Db::beginTransaction
 * Db::commit
 * Db::errorCode
 * Db::errorInfo
 * Db::exec
 * Db::getAttribute
 * Db::getAvailableDrivers 
 * Db::inTransaction 
 * Db::lastInsertId
 * Db::query
 * Db::quote
 * Db::rollback
 * Db::setAttribute
 */
class Db
{
    /**
     * $config = [
     *   'dbhost'=>db host,
     *   'dbroot'=>db user,
     *   'dbpwd'=>'db password,
     *   'dbtype'=>db type,
     *   'dbport'=>db port,
     *   'dbcharset'=>db charset,
     *   'dbname'=>db database name,
     *   'pdoAttribute'=>[ pdo attribute ]
     * ]
     */
    protected $config;
    /**
     * PDO $conn
     */
    protected $conn;
    /**
     * $stmts = [PDOStatements,PDOStatements]
     */
    protected $stmts = [];

    /**
     * Db::__construct()
     * 载入数据库的配置
     * @param mixed $config
     * @return
     */
    public function __construct($config)
    {
        $this->config = $config;
    }
    /**
     * Db::connDb()
     * 连接数据库
     * @return
     */
    public function connDb()
    {
        try {
            
            if( $this->config['dbtype'] == 'sqlite' ){
                $dsn = 'sqlite:' . $this->config['dbFile'];
            }else{
                $dsn = sprintf('%s:dbname=%s;host=%s;charset=%s', $this->config['dbtype'], $this->config['dbname'], $this->config['dbhost'],$this->config['dbcharset']);
            }
            //$pdoAttribute = [\PDO::MYSQL_ATTR_INIT_COMMAND => "set names " . $this->config['dbcharset']];
            
            if( !isset($this->config['pdoAttribute'][\PDO::ATTR_DEFAULT_FETCH_MODE]) ){
                $this->config['pdoAttribute'][\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;
            }
            $this->config['pdoAttribute'][\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
            foreach ($this->config['pdoAttribute'] as $key => $attr) {
                $pdoAttribute[$key] = $attr;
            }
            $this->conn = new \PDO($dsn, $this->config['dbroot'], $this->config['dbpwd'], $pdoAttribute);
            $this->stmts = [];
            return $this->conn;

        }catch (\PDOException $e) {

            throw new DbException("Database connection failed! " . $e->getMessage());

        }
    }
    /**
     * Db::prepare()
     * 执行预执行
     * @param string $sql sql语句
     * @param mixed $driver_options 参照PDO的prepare的$driver_options
     * @return PDOStatement
     */
    public function prepare($sql,$driver_options=[]){

        try{
            $stmt = $this->getConn()->prepare($sql,$driver_options);
        }catch( \Exception $e ){
            throw $e;
        }
        return new Stmt($stmt);
    }
    public function prepare_query($sql,$params){
        $sth = $this->getConn()->prepare($sql);
        $sth->execute($params);
        return $sth;
    }

    /**
     * Db::getConn()
     * 获取PDO对象
     * @return PDO
     */
    public function getConn()
    {
        if( $this->conn == null ){
            $this->connDb();
        }
        return $this->conn;
    }
    
    /**
     * Db::closeConn()
     * 关闭连接
     * @return
     */
    public function closeConn()
    {
        $this->conn = null;
    }

    /**
     * Db::error()
     * 错误处理
     * @param mixed $sql
     * @return
     */
    protected function error( $sql )
    {

        $msg = $this->conn->errorInfo();
        
        throw new DbException($sql);
        
    }
    
    /**
     * Db::__call()
     * 重载PDO的方法
     * @param string $name 方法的名称
     * @param mixed $args 方法的参数
     * @return
     */
    public function __call($name,$args){
        try{
            return call_user_func_array(array($this->getConn(),$name),$args);
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * Db::__sleep()
     * 类的序列化
     * @return
     */
    public function __sleep()
    {
        return $this->config;
    }
    
    /**
     * Db::__wakeup()
     * 类的序列化
     * @return
     */
    public function __wakeup()
    {
        $this->connDb();
    }
    /**
     * Db::__debugInfo()
     * 打印Db类
     * @return
     */
    public function __debugInfo(){
        return [
            'conn'=>$this->conn,
            'stmts'=>$this->stmts
        ];
    }
}
