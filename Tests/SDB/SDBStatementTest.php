<?php
/**
 * Tests specific to AmazonSDB packages (SDB)
 */
namespace FlexibleORMTests\SDB;

use ORM\SDB\SDBFactory;
use ORM\SDB\SDBStatement;
use FlexibleORMTests\Mock;
use FlexibleORMTests\ORMTest;

set_include_path(get_include_path(). PATH_SEPARATOR . __DIR__.'/..');
require_once 'ORMTest.php';

$sdb = SDBStatement::GetSDBConnection();
$sdb->delete_domain( Mock\SDBOwner::TableName() );

//$sdb->create_domain( '123_mustbeescaped' );
//$sdb->put_attributes( 
//    '123_mustbeescaped', 
//    'test', 
//    array('note' => 'This domain name should be escaped with backticks'));

Mock\SDBCar::CreateDomain();
Mock\SDBOwner::CreateDomain();
        
$owners = range( 1, 12 );
foreach( $owners as $owner ) {
    $owner = new Mock\SDBOwner(array(
        'name' => 'Jarrod '.$owner
    ));

    $owner->save();
}


/**
 * Test failures and exceptions for SDBStatement
 *
 * Most of the features are already tested in ORMModelSDBTest
 */
class SDBStatementTest extends ORMTest {
    const DOMAIN = 'SDBStatementTest';

    public function testInjectionInsert() {
        $query = new SDBStatement("INSERT INTO cars (brand, colour, doors) VALUES ( ?, ?, ? )");
        $query->bindValues(array('\'?\' ? \'red\'', '4', "\'3"));
        
        
        $this->assertEquals( array(
            'brand'     => '\'?\' ? \'red\'',
            'colour'    => '4',
            'doors'     => "\'3"
        ), $query->attributes() );
    }
    
    public function testInjectionUpdate() {
        $query = new SDBStatement("UPDATE cars SET manufacturer = ? WHERE itemName() = ?");
        $query->bindValues(array('\'?\' ? \'red\'', '4'));
        
        $this->assertEquals(array(
            'manufacturer'     => '\'?\' ? \'red\''
        ), $query->attributes());
    }
    
    public function testInjectionSelect() {
        $query = new SDBStatement("SELECT * FROM cars WHERE doors > ? AND brand = ?");
        $query->bindValues(array( "'4'", '\'?\' ? \'red\''));
        
        $this->assertEquals("SELECT * FROM cars WHERE doors > '''4''' AND brand = '''?'' ? ''red'''", (string)$query);
    }

    public function testNotAllBound() {
        $this->markTestIncomplete();
    }

    public function testBindParam() {
        $this->markTestIncomplete();
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
        $this->markTestIncomplete();
    }

    public function testTooManyBound() {
        $this->markTestIncomplete();
    }

    public function testDeleteRange() {
        $this->markTestIncomplete();
    }
    
    public function testFetchArray() {
        $query = SDBFactory::Get("SELECT * FROM owners LIMIT 110");
        $query->execute();
        $result = $query->fetch(SDBStatement::FETCH_ARRAY);
        
        $this->assertTrue(is_array($result));
        $this->assertEquals( 1, count($result) );
    }
    
    public function testFetchAssoc() {
        $query = SDBFactory::Get("SELECT * FROM owners LIMIT 10");
        $query->execute();
        $result = $query->fetch(SDBStatement::FETCH_ASSOC);
        
        $this->assertTrue(is_array($result));
        $this->assertEquals( 1, count($result) );
        $keys = array_keys($result);
        $this->assertEquals( 'name', $keys[0] );
    }
    
    public function testFetchBoth() {
        $query = SDBFactory::Get("SELECT * FROM owners LIMIT 10");
        $query->execute();
        $result = $query->fetch();
        
        $this->assertTrue(is_array($result));
        $this->assertEquals( 2, count($result) );
        $keys = array_keys($result);
        $this->assertTrue( array_key_exists('name', $result) );
        $this->assertEquals( $result[0], $result['name'] );
    }
    
    public function testFetchMultiple() {
        $query = SDBFactory::Get("SELECT name FROM owners LIMIT 10");
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
        $query = SDBFactory::Get("SELECT name FROM owners LIMIT 10");
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
        $query = SDBFactory::Get("SELECT name FROM owners LIMIT 5");
        $query->execute();
        $count      = 0;
        $results    = array();
        
        while( $results[] = $query->fetch(SDBStatement::FETCH_ARRAY) ) {
            $count++;
        }
        
        $this->assertEquals(5, $count);
        
        array_pop($results);
        
        $query->execute();
        $fetchAllResult = $query->fetchAll(SDBStatement::FETCH_ARRAY );
        $this->assertEquals( $results, $fetchAllResult);
        
    }
    
    public function testTableEscaping() {
        $query = SDBFactory::Get("SELECT * FROM `123_mustbeescaped` LIMIT 1");
        $this->assertTrue( $query->execute(), 'Query failed' );
    }
}