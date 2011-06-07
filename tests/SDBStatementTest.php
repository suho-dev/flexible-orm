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
    
    public function testBindAnonymous() {
        $query = new SDBStatement("SELECT * FROM cars WHERE doors > ? AND colour = ? LIMIT 10");
        
        $query->bindValues(array(1, 'black'));
        $this->assertEquals( 'SELECT * FROM cars WHERE doors > \'1\' AND colour = \'black\' LIMIT 10', (string)$query );
    }
    
    public function testComplicatedAnonymous() {
        $query = new SDBStatement("SELECT * FROM cars WHERE doors > ? AND brand = 'silly? brand?' AND colour = ? LIMIT 10");
        
        $query->bindValues(array(1, 'black'));
        $this->assertEquals( 'SELECT * FROM cars WHERE doors > \'1\' AND brand = \'silly? brand?\' AND colour = \'black\' LIMIT 10', (string)$query );
    }
    
    public function testBindInsertAnonymous() {
        $query = new SDBStatement("INSERT INTO cars (brand, colour, doors) VALUES ( ?, 'black', ? )");
        $this->assertTrue( $query->execute(array('Dodge', 2)) );
        
        $id = SDBStatement::LastInsertId();
        
        $car = Mock\SDBCar::Find( $id );
        $this->assertEquals( 'Dodge',   $car->brand );
        $this->assertEquals( 'black',   $car->colour );
        $this->assertEquals( 2,         $car->doors );
    }

    public function testHybridBind() {
        
    }

    public function testTooManyBound() {
        
    }

    public function testDeleteRange() {

    }
    
    public function testFetchArray() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT * FROM owners LIMIT 110");
        $query->execute();
        $result = $query->fetch(SDBStatement::FETCH_ARRAY);
        
        $this->assertTrue(is_array($result));
        $this->assertEquals( 1, count($result) );
    }
    
    public function testFetchAssoc() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT * FROM owners LIMIT 10");
        $query->execute();
        $result = $query->fetch(SDBStatement::FETCH_ASSOC);
        
        $this->assertTrue(is_array($result));
        $this->assertEquals( 1, count($result) );
        $keys = array_keys($result);
        $this->assertEquals( 'name', $keys[0] );
    }
    
    public function testFetchBoth() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT * FROM owners LIMIT 10");
        $query->execute();
        $result = $query->fetch();
        
        $this->assertTrue(is_array($result));
        $this->assertEquals( 2, count($result) );
        $keys = array_keys($result);
        $this->assertTrue( array_key_exists('name', $result) );
        $this->assertEquals( $result[0], $result['name'] );
    }
    
    public function testFetchMultiple() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT name FROM owners LIMIT 10");
        $query->execute();
        
        $lastName   = '';
        $count      = 0;
        
        while( $result = $query->fetch(SDBStatement::FETCH_ASSOC) ) {
            $this->assertNotEquals( $lastName, $result['name'] );
            $lastName = $result['name'];
            $count++;
        }
        
        $this->assertEquals(10, $count);
    }
    
    public function testFetchAllAssoc() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT name FROM owners LIMIT 10");
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
        
        unset($results);
    }
    
    public function testFetchAll() {
        $query = \ORM\SDB\SDBFactory::Get("SELECT name FROM owners LIMIT 10");
        $query->execute();
        $count      = 0;
        $results    = array();
        
        while( $results[] = $query->fetch(SDBStatement::FETCH_ARRAY) ) {
            $count++;
        }
        
        $this->assertEquals(10, $count);
        
        array_pop($results);
        
        $query->execute();
        $fetchAllResult = $query->fetchAll(SDBStatement::FETCH_ARRAY );
        $this->assertEquals( $results, $fetchAllResult);
        
    }

}
?>