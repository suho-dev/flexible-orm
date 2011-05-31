<?php
namespace ORM\Tests;
use \ORM\Tests\Mock, \ORM\SDB\SDBStatement;

require_once 'ORMTest.php';

/**
 * Test failures and exceptions for SDBStatement
 *
 * @Todo TEST the SDBStatement class explicitly
 *
 * Most of the features are already tested in ORMModelSDBTest
 */
class SDBStatementTest extends ORMTest {
    const DOMAIN = 'SDBStatementTest';

    public function setUp() {
        Mock\SDBCar::CreateDomain();
    }

    public function testInjection() {

    }

    public function testNotAllBound() {

    }

    public function testBindParam() {

    }

    public function testHybridBind() {
        
    }

    public function testTooManyBound() {
        
    }

    public function testDeleteRange() {

    }
    
    public function testFetchArray() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT * FROM owners");
        
        $result = $query->fetch(SDBStatement::FETCH_ARRAY);
        
        $this->assertTrue(is_array($result));
        $this->assertEquals( 1, count($result) );
    }
    
    public function testFetchAssoc() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT * FROM owners");
        
        $result = $query->fetch(SDBStatement::FETCH_ASSOC);
        
        $this->assertTrue(is_array($result));
        $this->assertEquals( 1, count($result) );
        $keys = array_keys($result);
        $this->assertEquals( 'name', $keys[0] );
    }
    
    public function testFetchBoth() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT * FROM owners");
        
        $result = $query->fetch();
        
        $this->assertTrue(is_array($result));
        $this->assertEquals( 2, count($result) );
        $keys = array_keys($result);
        $this->assertTrue( array_key_exists('name', $result) );
        $this->assertEquals( $result[0], $result['name'] );
    }
    
    public function testFetchMultiple() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT name FROM owners");
        $query->execute();
        
        $lastName   = '';
        $count      = 0;
        
        while( $result = $query->fetch(SDBStatement::FETCH_ASSOC) ) {
            $this->assertNotEquals( $lastName, $result['name'] );
            $lastName = $result['name'];
            $count++;
        }
        
        $this->assertGreaterThan(10, $count);
    }
    
    public function testFetchAllAssoc() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT name FROM owners");
        $query->execute();
        $count      = 0;
        $results    = array();
        
        while( $results[] = $query->fetch(SDBStatement::FETCH_ASSOC) ) {
            $count++;
        }
        
        array_pop($results);
        
        $query->execute();
        $fetchAllResult = $query->fetchAll(SDBStatement::FETCH_ASSOC );
        $this->assertEquals( $count, count($fetchAllResult) );
        $this->assertNotEquals( $results, $fetchAllResult);
        $this->assertEquals( $results, array_values($fetchAllResult));
    }
    
    public function testFetchAll() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT name FROM owners");
        $query->execute();
        $count      = 0;
        $results    = array();
        
        while( $results[] = $query->fetch(SDBStatement::FETCH_ARRAY) ) {
            $count++;
        }
        
        array_pop($results);
        
        $query->execute();
        $fetchAllResult = $query->fetchAll(SDBStatement::FETCH_ARRAY );
        $this->assertEquals( $results, $fetchAllResult);
    }

}
?>