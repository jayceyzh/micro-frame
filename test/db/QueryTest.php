<?php
require_once dirname(__DIR__) . "/mpfLoad.php";
class QueryTest extends PHPUnit_Framework_TestCase{
	private $app;
	private $Query;
    
	public function setup(){
        $db = new mpf\db\Db([
            "dbFile"=>__DIR__ . "/test.db",
            "dbtype"=>"sqlite",
            "dbcharset"=>"utf8",
        	"dbname"=>"test",
            'dbroot'=>null,
            'dbpwd'=>null
        ]);
        $this->Query = new \mpf\db\Query($db);
	}
    
	public function testOne(){
		$result = $this->Query->select("username")->from("user")->andWhere("id>1",[])->orderby("username")->limit(1)->one();
		//$this->assertTrue( is_array($result) );
	}

	public function testAll(){
		$result = $this->Query->select("username")->from("user")->andWhere("id>1",[])->All();
		if( $result ){
			$this->assertTrue( is_array($result[0]) );
		}
	}

	public function testCount(){
		$result = $this->Query->select("username")->from("user")->andWhere("id>1",[])->count();
		//$this->assertTrue( is_int($result) );
	}
 
	public function testColumn(){
		$result = $this->Query->select("username")->from("user")->andWhere("id>1",[])->column();
	}

}
