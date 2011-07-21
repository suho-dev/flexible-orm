<?php
namespace ORM\Tests;
use \ORM\Tests\Mock, \ORM\SDB\ORMModelSDB;

require_once 'ORMTest.php';

/**
 * Description of ORMSDBTests
 *
 *
 * Additional SDB tests (as the other file was taking a long time to run)
 */
class ORMSDBTests extends ORMTest {
    public function setUp() {
        Mock\File::CreateDomain();
        Mock\SDBCar::CreateDomain();
    }

    public function tearDown() {

    }

    public function testLargeAttribute() {
        // Store some data that is way more than the 1K limit of SDB
        $file = new Mock\File();
        $file->data = file_get_contents('../ORM_Model.php');
        $file->name = 'ORM_Model.php';

        $this->assertTrue( $file->save(), "Unable to save File" );

        $storedFile = Mock\File::Find( $file->id() );
        $this->assertEquals( $file->data, $storedFile->data );
    }
    
    public function testNewLineAttribute() {
        $car = new Mock\SDBCar();
        $car->brand = 'Ford';
        $car->colour = "Blue\\nOr maybe\n red";
        
        $this->assertTrue( $car->save(), "Unable to save Car" );
        
        $storedCar = Mock\SDBCar::Find( $car->id() );
        $this->assertEquals( $car->brand,  $storedCar->brand, "Incorrect brand stored data for {$car->id()}" );
        $this->assertEquals( $car->colour, $storedCar->colour, "Incorrect colour stored data for {$car->id()}" );
    }
    
    public function testLimit() {
        $owners = Mock\SDBOwner::FindAll(array(
            'limit' => 110
        ));

        $this->assertEquals( 110, count($owners) );
    }
    
    public function testSmallLimit() {
        $owners = Mock\SDBOwner::FindAll(array(
            'limit' => 10
        ));

        $this->assertEquals( 10, count($owners) );
    }
    
    public function testOffset() {
        $owners1 = Mock\SDBOwner::FindAll(array(
            'limit'  => 10
        ));
        
        $owners1 = Mock\SDBOwner::FindAll(array(
            'limit'  => 10
        ));
        
        $owners2 = Mock\SDBOwner::FindAll(array(
            'limit'  => 10,
            'offset' => 10
        ));
        
        $owners2 = Mock\SDBOwner::FindAll(array(
            'limit'  => 10,
            'offset' => 10
        ));
        
        $owners3 = Mock\SDBOwner::FindAll(array(
            'limit'  => 10,
            'offset' => 20
        ));
        
        $owners5 = Mock\SDBOwner::FindAll(array(
            'limit'  => 10,
            'offset' => 40
        ));
        
        $ownersAll = Mock\SDBOwner::FindAll(array(
            'limit'  => 50
        ));
        
        $this->assertEquals( 10, count($owners1) );
        $this->assertEquals( 10, count($owners2) );
        $this->assertEquals( 10, count($owners3) );
        $this->assertEquals( 50, count($ownersAll) );
        
        $this->assertNotEquals( $owners1[0]->id(), $owners2[0]->id(), "Second returned collection is same as first" );
        $this->assertNotEquals( $owners2[0]->id(), $owners3[0]->id(), "Third returned collection is same as second" );
        $this->assertNotEquals( $owners1[0]->id(), $owners3[0]->id(), "Third returned collection is same as first" );
        $this->assertEquals( $ownersAll[10]->id(), $owners2[0]->id(), "Second group does not match" );
        $this->assertEquals( $ownersAll[20]->id(), $owners3[0]->id(), "Third group does not match" );
        $this->assertEquals( $ownersAll[40]->id(), $owners5[0]->id(), "Fourth group does not match (skipped offset problem)" );
    }
    
    public function testCoundFindAll() {
        $carCount = Mock\SDBCar::CountFindAll();
        $cars     = Mock\SDBCar::FindAll();
        
        $this->assertEquals(count($cars), $carCount);
    }
    
    public function testDefaultValue() {
        $car = new Mock\SDBCar;
        
        $this->assertEquals( 'black', $car->colour );
        
        $car->colour = 'red';
        
        $this->assertTrue( $car->save() );
        
        $savedCar = Mock\SDBCar::Find( $car->id() );
        $this->assertEquals( 'red', $savedCar->colour );
    }
    
}
?>