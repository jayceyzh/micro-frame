<?php
namespace mpf\db;
/**
 * Stmt
 * PDO支持多种数据库的操作，也就是说PDO已经封装的很好了，就对PDO和PDOStatement进行一个简单的封装。
 * 增加数据库操作的异常处理。
 * 
 * 重新PDOStatement的方法
 * Stmt::execute
 * 
 * 重载PDOStatement的方法
 * Stmt::bindColumn
 * Stmt::bindParam
 * Stmt::bindValue
 * Stmt::closeCursor
 * Stmt::columnCount
 * Stmt::debugDumpParams
 * Stmt::errorCode
 * Stmt::errorInfo
 * Stmt::fetch
 * Stmt::fetchAll
 * Stmt::fetchColumn
 * Stmt::fetchObject
 * Stmt::getAttribute
 * Stmt::getColumnMeta
 * Stmt::nextRowset
 * Stmt::rowCount
 * Stmt::setAttribute
 * Stmt::setFetchMode
 */
class Stmt{
    /**
     * PDOStatement
     */
    protected $stmt;
    /**
     * Stmt::__construct()
     * 
     * @param PDOStatement $stmt
     * @return
     */
    public function __construct($stmt){
        $this->stmt = $stmt;
    }
    /**
     * Stmt::exec()
     * 
     * @param mixed $input_parameters 参照PDOStatement的$input_parameters参数
     * @return
     */
    public function exec($input_parameters = []){
        try{
            if( $input_parameters == [] ){
                $res = $this->stmt->execute();
            }else{
                $res = $this->stmt->execute($input_parameters);
            }
        }catch( \Exception $e ){
            throw $e;
        }
        return $this;
    }
    public function column(){
        return $this->stmt->fetch(\PDO::FETCH_COLUMN);
    }
    /**
     * Stmt::__call()
     * 重载PDOStatement的方法
     * @param string $name 方法名称
     * @param mixed $args 方法的参数
     * @return
     */
    public function __call($name,$args){
        try{
            return call_user_func_array(array($this->stmt,$name),$args);
        }catch( \Exception $e ){
            throw $e;
        }
    }
    /**
     * Stmt::__debugInfo()
     * 打印Stmt类
     * @return
     */
    public function __debugInfo(){
        return [
            'stmt'=>$this->$stmt
        ];
    }
    
}
