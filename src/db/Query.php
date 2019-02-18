<?php

namespace mpf\db;

use mpf\db\Db;

/**
 * Query
 * 封装数据库查询的sql语句构造类。
 * 
 * Query::__construct($db)
 * Query::init($db)
 * Query::select($column = '*')
 * Query::selectCount($column = "*")
 * Query::from($tableName)
 * Query::join($str)
 * Query::leftJoin($tableName, $str = '')
 * Query::innerJoin($tableName, $str = '')
 * Query::rightJoin($tableName, $str = '')
 * Query::groupby($column)
 * Query::group($str)
 * Query::orderby($str)
 * Query::order($str)
 * Query::limit($count)
 * Query::offset($count)
 * Query::andWhere($str, $params)
 * Query::like($column, $param)
 * Query::orLikes($conditions)
 * Query::getSql()
 * Query::getCountSql()
 * Query::getParams()
 * Query::one()
 * Query::all()
 * Query::count()
 * Query::column()
 * 
 * eg:
 * 
 * $Query = Query::init()->select("name")
 *              ->from("user")
 *              ->leftJoin("user_goods","uid=ug_id")
 *              ->andWhere("uid=?",1)
 *              ->andWhere("uid=? and uname=?",[1,"uid"])
 *              ->like("uname","ab")
 *              ->orlikes(["uid"=>1,"uname"=>"ab"])
 *              ->limit(1)
 *              ->offset(1);
 * 
 * $record = $Query->one();
 * $records = $Query->all();
 * $count = $Query->count();
 * 
 * $users = Query->init()->select("*")->from("user")->all();
 * 
 */
class Query
{

    protected $db;

    protected $tableName = '';

    protected $column = '*';

    protected $countColumn = '*';

    protected $from = '';

    protected $joins = [];

    protected $conditions = [];

    protected $params = [];

    protected $group = '';

    protected $order = '';

    protected $limit = 100;

    protected $offset = 0;

    public function __construct($db)
    {
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
     * Query::select()
     * 
     * @param string $column
     * @return Object Query
     */
    public function select($column = '*')
    {
        $this->column = $column;
        return $this;
    }

    /**
     * Query::selectCount()
     * 
     * @param string $column Query field
     * @return Object Query
     */
    public function selectCount($column = "*")
    {
        $this->countColumn = $column;
        return $this;
    }

    /**
     * Query::from()
     * 
     * @param string $tableName  Table name
     * @return Object Query
     */
    public function from($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Query::join()
     * 
     * @param mixed $str left join on aid=bid | inner join on aid=bid | right join on aid=bid
     * @return Object Query
     */
    public function join($str)
    {
        $this->joins[] = $str;
        return $this;
    }

    /**
     * Query::leftJoin()
     * 
     * @param mixed $tableName  Table name
     * @param string $str aid=bid
     * @return Object Query
     */
    public function leftJoin($tableName, $str = '')
    {
        $this->joins[] = 'left join ' . $tableName . ' on ' . $str;
        return $this;
    }

    /**
     * Query::innerJoin()
     * 
     * @param mixed $tableName Table name
     * @param string $str string aid=bid
     * @return Object Query
     */
    public function innerJoin($tableName, $str = '')
    {
        $this->joins[] = 'inner join ' . $tableName . ' on ' . $str;
        return $this;
    }

    /**
     * Query::rightJoin()
     * 
     * @param mixed $tableName Table name
     * @param string $str aid=bid
     * @return Object Query
     */
    public function rightJoin($tableName, $str = '')
    {
        $this->joins[] = 'right join ' . $tableName . ' on ' . $str;
        return $this;
    }

    /**
     * Query::groupby()
     * 
     * @param mixed $column 
     * @return Object Query
     */
    public function groupby($column)
    {
        $this->group = "group by " . $column;
        return $this;
    }

    /**
     * Query::group()
     * 
     * @param mixed $str group by time 
     * @return Object Query
     */
    public function group($str)
    {
        $this->group = $str;
        return $this;
    }

    /**
     * Query::orderby()
     * 
     * @param mixed $str time desc
     * @return Object Query
     */
    public function orderby($str)
    {
        $this->order = "order by " . $str;
        return $this;
    }

    /**
     * Query::order()
     * 
     * @param mixed $str order by time desc
     * @return Object Query
     */
    public function order($str)
    {
        $this->order = $str;
        return $this;
    }

    /**
     * Query::limit()
     * 
     * @param mixed $count  How many data query
     * @return Object Query
     */
    public function limit($count)
    {
        $this->limit = (int)$count;
        return $this;
    }

    /**
     * Query::offset()
     * 
     * @param mixed $count  Query from section
     * @return Object Query
     */
    public function offset($count)
    {
        $this->offset = (int)$count;
        return $this;
    }

    /**
     * Query::andWhere()
     * Add a condition
     * @param mixed $str  condition aid=? | aid=? or bid=? | aid=? and bid=?
     * @param mixed $params  param 1 | "ab" | [1,2]
     * @return Object Query
     */
    public function andWhere($str, $params)
    {
        $this->conditions[] = $str;
        if (is_array($params)) {
            foreach ($params as $param) {
                $this->params[] = $param;
            }
        } else {
            $this->params[] = $params;
        }
        return $this;
    }

    /**
     * Query::like()
     * Add a like condition
     * @param string $column  Field name
     * @param string $param Search content
     * @return Object Query
     */
    public function like($column, $param)
    {
        $this->conditions[] = $column . ' like concat("%",?,"%")';
        $this->params[] = $param;
        return $this;
    }

    /**
     * Query::orLikes()
     * Add one like or more like conditions and connect with or
     * @param array $conditions Conditional array ['uname'=>'ab'] | ['uname'=>'ab','desc'=>'dd']
     * @return Object Query
     */
    public function orLikes($conditions)
    {
        $likeConditions = [];
        foreach ($conditions as $key => $value) {
            $likeConditions[] = $key . ' like concat("%",?,"%")';
            $this->params[] = $value;
        }
        $this->conditions[] = '( ' . implode(' or ', $likeConditions) . ' )';
        return $this;
    }

    /**
     * Query::getSql()
     * Get SQL statement
     * @return Object Query
     */
    public function getSql()
    {
        $sql = 'select ' . $this->column . ' from ' . $this->tableName . PHP_EOL;
        if ($this->joins) {
            $sql .= implode(PHP_EOL, $this->joins) . PHP_EOL;
        }
        if ($this->conditions) {
            $sql .= 'where ' . implode(PHP_EOL . 'and ', $this->conditions) . PHP_EOL;
        }
        if ($this->order) {
            $sql .= $this->order . PHP_EOL;
        }
        if ($this->group) {
            $sql .= $this->group . PHP_EOL;
        }
        if ($this->offset) {

        }
        $sql .= ' limit ' . (int)$this->limit . ' offset ' . (int)$this->offset;
        return $sql;
    }

    /**
     * Query::getCountSql()
     * Gets the SQL statement for the number of queries
     * @return Object Query
     */
    public function getCountSql()
    {
        $sql = 'select count(' . $this->countColumn . ') from ' . $this->tableName . PHP_EOL;
        if ($this->joins) {
            $sql .= implode(PHP_EOL, $this->joins) . PHP_EOL;
        }
        if ($this->conditions) {
            $sql .= 'where ' . implode(PHP_EOL . 'and ', $this->conditions) . PHP_EOL;
        }
        if ($this->order) {
            $sql .= $this->order . PHP_EOL;
        }
        if ($this->group) {
            $sql .= $this->group . PHP_EOL;
        }
        return $sql;
    }

    /**
     * Query::getParams()
     * Acquisition parameter
     * @return Object Query
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Query::one()
     * 
     * Query a record
     * 
     * @return miexed false | [A record]
     */
    public function one()
    {
        $this->limit(1);
        $sth = $this->db->prepare($this->getSql());
        $sth->execute($this->getParams());
        return $sth->fetch();
    }

    /**
     * Query::all()
     * Query multiple record
     * @return array [[data]]
     */
    public function all()
    {
        $sth = $this->db->prepare($this->getSql());
        $sth->execute($this->getParams());
        return $sth->fetchAll();
    }

    /**
     * Query::count()
     * Returns the number of queries
     * @return int number
     */
    public function count()
    {
        $sth = $this->db->prepare($this->getCountSql());
        $sth->execute($this->getParams());
        return $sth->fetch(\PDO::FETCH_NUM)[0];
    }

    /**
     * Query::column()
     * Returns column
     * @return mixed  column
     */
    public function column()
    {
        $sth = $this->db->prepare($this->getSql());
        $sth->execute($this->getParams());
        return $sth->fetch(\PDO::FETCH_NUM)[0];
    }

}
