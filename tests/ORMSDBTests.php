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
}
?>