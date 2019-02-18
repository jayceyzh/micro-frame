<?php
require_once dirname(__DIR__) . "/mpfLoad.php";
class DbTest extends PHPUnit_Framework_TestCase{
	
    public $db;
    
	public function setup(){
	   $this->db = new mpf\db\Db([
            "dbFile"=>__DIR__ . "/test.db",
            "dbtype"=>"sqlite",
            "dbcharset"=>"utf8",
        	"dbname"=>"test",
            'dbroot'=>null,
            'dbpwd'=>null
       ]);
	}

	public static function setupBeforeClass(){
		
        $db = new mpf\db\Db([
            "dbFile"=>__DIR__ . "/test.db",
            "dbtype"=>"sqlite",
            "dbcharset"=>"utf8",
        	"dbname"=>"test",
            'dbroot'=>null,
            'dbpwd'=>null
       ]);
		$db->exec("DROP TABLE IF EXISTS \"user\"");
		$db->exec(<<<EOF
CREATE TABLE "user" (
  "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  "username" text(255),
  "email" TEXT(255),
  "password" TEXT(255),
  "tel" TEXT(255)
);
EOF
);
	}
    public function testConnDb(){
        $this->db->connDb();
    }
    public function testGetConn(){
        $conn = $this->db->getConn();
        $this->assertInstanceOf('\PDO',$conn);
    }
    public function testCloseConn(){
        $this->db->connDb();
    }
	public function testExec(){
		$db = $this->db;
		$result = $db->exec("select count(*) from user where id>0");
	}
    public function testCommit(){
        $this->db->beginTransaction(); 
        $this->db->commit();
        $this->db->lastInsertId();
        $a = "sfd";
        $this->db->quote($a,\PDO::PARAM_STR);
    }
    public function testPrepare(){
        $stmt = $this->db->prepare('select * from user limit 1');
        $stmt->execute();
        $stmt->fetchAll();
    }
    public function testCallback(){
        $this->db->beginTransaction();
        $this->db->commit();
        $this->db->errorCode();
        $this->db->errorInfo();
        $this->db->exec("select 1");
        $this->db->getAttribute(\PDO::ATTR_ERRMODE);
        $this->db->getAvailableDrivers();
        $this->assertEquals( false,$this->db->inTransaction() );
        $this->assertEquals( 0, $this->db->lastInsertId() ) ;
        $this->assertEquals( '1',$this->db->query('select 1')->fetch(\PDO::FETCH_COLUMN) );
        $this->assertEquals("'Nice'",$this->db->quote('Nice'));
        $this->db->beginTransaction();
        $this->db->rollBack();
        $this->db->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);;
    }
    public function testPrepare_query(){
        $res = $this->db->prepare_query('select 1',[])->fetch(\PDO::FETCH_COLUMN);
        $this->assertEquals($res,1);
    }
    public function testStmt(){
        $res = $this->db->prepare('select 1')->exec()->fetch(\PDO::FETCH_COLUMN);
        $this->assertEquals($res,1);
        $res = $this->db->prepare('select 1')->exec()->column();
        $this->assertEquals($res,1);
    }
    public function testDbRecord(){
        $record = new \mpf\db\DbRecord($this->db);
        $record->table('user');
        $record->username = "admin";
        $record->email = "admin@qq.com";
        $record->password = '123456';
        $record->tel = '10000000000';
        $id = $record->insert();
        $this->assertEquals( $id>0,true );
        $record = new \mpf\db\DbRecord($this->db);
        $record->table('user');
        $record->password = "111111";
        $record->andWhere('id=?',$id);
        $this->assertEquals( $record->update(),1);
        $record = new \mpf\db\DbRecord($this->db);
        $record->table('user');
        $record->setColumn('password',"123456");
        $record->andWhere('id=?',$id);
        $this->assertEquals( $record->update(),1);
        $record = new \mpf\db\DbRecord($this->db);
        $record->table('user');
        $record->andWhere('id=?',$id);
        $this->assertEquals( $record->delete(),1);
    }
}
