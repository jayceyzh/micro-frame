<?php
namespace mpf\db;
/**
 * DbRecord
 * 支持数据库的插入、更新、删除，简化sql语句。
 * DbRecord::table()
 * DbRecord::andWhere($str, $params)
 * DbRecord::setColumn($column,$value)
 * DbRecord::insert($debug = false)
 * DbRecord::update($debug = false)
 * DbRecord::delete($debug = false)
 */
class DbRecord{
      
    protected $db;
    protected $table;
    protected $columns = [];
    protected $columnValues = [];
    protected $columnPlaceholders = [];
    protected $conditions = [];
    protected $conditionParams = [];
     
    /**
     * DbRecord::__construct()
     * 
     * @param $db
     * @return
     */
    public function __construct($db){
        if( $db instanceof \PDO || $db instanceof \mpf\db\Db ){
            
        }else{
            throw new \Exception('数据库连接类不正确！数据库连接类请传PDO或者\mpf\db\Db');
        }
        $this->db = $db;
    }
    static function init($db){
        return new self($db);
    }
    /**
     * DbRecord::table()
     * 设置表名
     * @param string $tableName
     * @return
     */
    public function table($tableName){
        $this->table = $tableName;
    }
    /**
     * DbRecord::andWhere()
     * 增加条件语句
     * @param string $str 条件
     * @param mixed $params 参数
     * @return
     */
    public function andWhere($str, $params)
    {
        $this->conditions[] = $str;
        if (is_array($params)) {
            $this->conditionParams = array_merge($this->conditionParams,$params);
        } else {
            $this->conditionParams[] = $params;
        }
        return $this;
    }
    /**
     * DbRecord::setColumn()
     * 设置字段
     * @param string $column 字段
     * @param mixed $value 值
     * @return
     */
    public function setColumn($column,$value,$placeholder = '?'){
        $this->columns[] = '`' . $column . '`';
        if( $placeholder == '?' ){
            $this->columnValues[] = $value;
        }
        $this->columnPlaceholders[] = $placeholder;
    }
    /**
     * DbRecord::__set()
     * 设置字段
     * @param string $column 字段
     * @param mixed $value 值
     * @return 
     */
    public function __set($column,$value){
        $this->columns[] = '`' . $column . '`';
        $this->columnValues[] = $value;
        $this->columnPlaceholders[] = '?';
    }
    /**
     * DbRecord::insert()
     * 插入记录
     * @param bool $debug 是否调试
     * @return 返回插入的id
     */
    public function insert($debug = false){
        if( empty($this->table) ){
            throw new DbException("表名不能为空！");
        }
        if( empty($this->columns) ){
            throw new DbException("字段不能为空！");
        }
        $sql = 'insert into %s(%s) values(%s)';
        $sql = sprintf($sql,$this->table,implode(',',$this->columns),implode(',',$this->columnPlaceholders));
        if( $debug ){
            echo $sql . PHP_EOL;
            var_export($this->columnValues);
        }
        $sth = $this->db->prepare($sql);
        $sth->execute($this->columnValues);
        return $this->db->lastInsertId();
    }
    /**
     * DbRecord::update()
     * 更新记录，必须要加入条件才能调用该方法
     * @param bool $debug 是否调试
     * @return 返回受影响的行数
     */
    public function update($debug = false){
         if( empty($this->table) ){
            throw new DbException("表名不能为空！");
        }
        if( empty($this->columns) ){
            throw new DbException("字段不能为空！");
        }
        if( empty($this->conditions) ){
            throw new DbException("更新语句必须加上条件！");
        }
        $columns = [];
        foreach( $this->columns as $key=>$column ){
            $columns[] = $column . '=' . $this->columnPlaceholders[$key];
        }
        $sql = 'update %s set %s where %s';
        $sql = sprintf($sql,$this->table,implode(',',$columns),implode(' and ',$this->conditions));
        $params = array_merge($this->columnValues,$this->conditionParams);
        if( $debug ){
            echo $sql . PHP_EOL;
            var_export($params);
        }
        $sth = $this->db->prepare($sql);
        $sth->execute($params);
        return $sth->rowCount();
    }
     /**
     * DbRecord::delete()
     * 删除记录，必须要加入条件才能调用该方法
     * @param bool $debug 是否调试
     * @return 返回受影响的行数
     */
    public function delete($debug = false){
        if( empty($this->table) ){
            throw new DbException("表名不能为空！");
        }
        if( empty($this->conditions) ){
            throw new DbException("删除语句必须加上条件！");
        }
        $sql = 'delete from %s where %s';
        $sql = sprintf($sql,$this->table,implode(' and ',$this->conditions) );
        if( $debug ){
            echo $sql . PHP_EOL;
            var_export($this->conditionParams);
        }
        $sth = $this->db->prepare($sql);
        $sth->execute($this->conditionParams);
        return $sth->rowCount();
    }
}