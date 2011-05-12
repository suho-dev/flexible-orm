<?php
namespace ORM\Tests;
use \ORM\Tests\Mock, \ORM\SDB\ORMModelSDB;

require_once 'ORMTest.php';


class ORMModelSDBTest extends ORMTest {
    /**
     * @var AmazonSDB $object
     */
    protected $object;

    const DOMAIN = 'cars';

    private $_testCars = array(
            '1' => array('brand' => 'Alfa Romeo', 'colour' => 'Blue',  'doors' => 4),
            '2' => array('brand' => 'Volkswagen', 'colour' => 'Black', 'doors' => 5),
            '3' => array('brand' => 'Volkswagen', 'colour' => 'Grey',  'doors' => 2),
        );

    protected function setUp(){
        $this->object = new \AmazonSDB();
        $this->object->set_response_class('\ORM\SDB\SDBResponse');
        $this->object->set_region(\AmazonSDB::REGION_APAC_SE1);

        Mock\File::CreateDomain();
        Mock\SDBCar::CreateDomain();
        $this->object->batch_put_attributes(self::DOMAIN, $this->_testCars);

    }

    protected function tearDown() {
        $sdb = \ORM\SDB\SDBStatement::GetSDBConnection();
        $sdb->delete_domain( Mock\File::TableName() );
        $sdb->delete_domain( Mock\SDBCar::TableName() );
    }

    public function testDescribe() {
        $table = Mock\SDBCar::DescribeTable();

        $this->assertEquals( 5, count($table), "Got: ".implode(', ', $table) );
        $this->assertTrue( in_array('brand', $table ) );
        $this->assertTrue( in_array('colour', $table ) );
        $this->assertTrue( in_array('doors', $table ) );
    }

    public function testReturnsSDBResponse() {
        $this->assertEquals( 'ORM\SDB\SDBResponse', get_class($this->object->list_domains() ) );
    }

    public function testFind() {
        $car = Mock\SDBCar::Find(3);

        $this->assertEquals( 'ORM\Tests\Mock\SDBCar', get_class($car) );
        $this->assertEquals( 'Volkswagen', $car->brand );
        $this->assertEquals( '3', $car->id() );
    }

    public function testFindBy() {
        $car = Mock\SDBCar::FindByBrand('Alfa Romeo');

        $this->assertEquals( 'ORM\Tests\Mock\SDBCar', get_class($car) );
        $this->assertEquals( 'Alfa Romeo', $car->brand );
        $this->assertEquals( '1', $car->id() );
    }

    public function testFindAll() {
        $cars = Mock\SDBCar::FindAll();

        $this->assertEquals( 3, count($cars) );
        $this->assertEquals( 'ORM\ModelCollection', get_class($cars) );
        $this->assertEquals( 'ORM\Tests\Mock\SDBCar', get_class($cars[1]) );
    }

    public function testFindAllBy() {
        $cars = Mock\SDBCar::FindAllByBrand('Volkswagen');

        $this->assertEquals( 'ORM\ModelCollection', get_class($cars) );
        $this->assertEquals( 2, count($cars) );

        foreach( $cars as $car ) {
            $this->assertEquals( 'Volkswagen', $car->brand );
        }
    }

    /**
     * Test the situation where one placeholder contains the name of another placeholder
     * and the short placeholder is bound first
     */
    public function testFindBySimilarPlaceholderNames() {
        $car = Mock\SDBCar::Find(array(
            'where'     => 'name = :brand OR brand = :brandname',
            'values'    => array(
                ':brand'     => 'Volkswagen',
                ':brandname' => 'Volkswagen'
            )
        ));

        $this->assertEquals( 'Volkswagen', $car->brand);
    }

    public function testSaveCreate() {
        $car            = new Mock\SDBCar();
        $car->brand     = 'Ford';
        $car->colour    = 'Black';
        $car->doors     = 2;
        $car->privateTest( 'changing private attribute' );

        $this->assertTrue( $car->save() );
        $this->assertNotNull( $car->id() );

        $storedCar = Mock\SDBCar::Find($car->id());
        $this->assertEquals($car->brand,    $storedCar->brand, 
                "Stored Car ($car) brand does not match created one" );
        $this->assertEquals($car->colour,   $storedCar->colour,
                "Stored Car ($car) colour does not match created one" );
        $this->assertEquals($car->doors,    $storedCar->doors,
                "Stored Car ($car) doors do not match created one" );
        $this->assertEquals($car->id(),     $storedCar->id(),
                "Stored Car ($car) id() does not match created one" );
        $this->assertNotEquals('changing private attribute', $storedCar->privateTest() );
    }

    public function testSaveUpdate() {
        $car            = new Mock\SDBCar();
        $car->brand     = 'Ford';
        $car->colour    = 'Blue';
        $car->doors     = 8;
        $this->assertTrue( $car->save(), "Failed to save: ".$car->errorMessagesString() );

        $this->assertEquals( 'Blue', Mock\SDBCar::Find($car->id())->colour );

        $car->colour = 'Red';
        $car->doors  = 6;
        $this->assertTrue( $car->save(), "Failed to save: ".$car->errorMessagesString() );

        $storedCar = Mock\SDBCar::Find($car->id());
        $this->assertEquals( $car->id(), $storedCar->id() );
        $this->assertEquals( $car->brand, $storedCar->brand ); // check the unchanged value
        $this->assertEquals( 'Red', $storedCar->colour ); // Check the updated values
        $this->assertEquals( '6', $storedCar->doors );
    }

    public function testDelete() {
        $car            = new Mock\SDBCar();
        $car->brand     = 'Ford';
        $car->colour    = 'Blue';
        $car->doors     = 8;
        $this->assertTrue( $car->save(), "Unable to save car for deletion!" );

        $car->delete();
        $this->assertFalse( Mock\SDBCar::Find($car->id()), "Found car when it should not exist" );
    }

    public function testDestroy() {
        $car            = new Mock\SDBCar();
        $car->brand     = 'Ford';
        $car->colour    = 'Blue';
        $car->doors     = 8;
        $this->assertTrue( $car->save(), "Unable to save car for deletion!" );

        $car2            = new Mock\SDBCar();
        $car2->brand     = 'Ford';
        $car2->colour    = 'Red';
        $car2->doors     = 6;
        $car2->id( $car->id() );
        $this->assertTrue( $car->save(true), "Unable to save 2nd lot of car attributes for deletion!" );

        Mock\SDBCar::Destroy( $car->id() );
        $this->assertFalse( Mock\SDBCar::Find($car->id()), "Found car when it should not exist" );
    }

    public function testUpdateChangeItemName() {
        $this->markTestIncomplete("Test not implemented");
    }

    public function testFindAllGetAll() {
        // Create all the owners
        // only do this once as it's extremely slow
//        for( $i=1; $i<=150; $i++) {
//            $owner = new Mock\SDBOwner();
//            $owner->name = "MyName".rand(1,200);
//            $owner->save();
//        }

        $allOwners = Mock\SDBOwner::FindAll();

        $this->assertGreaterThan( 149, count($allOwners) );
    }

    /**
     * 
     */
    public function testFindQuotes() {
        $owner = Mock\SDBOwner::Find(array(
            'where'  => "name = 'o\''connel' OR name LIKE :name",
            'values' => array(':name' => 'MyName%')
        ));

        $this->assertEquals( 'ORM\Tests\Mock\SDBOwner', get_class($owner),
            "Find was confused by escaped single quote in WHERE"
        );

        $owner = Mock\SDBOwner::Find(array(
            'where'  => "name = :first OR name LIKE :name",
            'values' => array(':name' => 'MyName%', ':first' => "o'connel")
        ));

        $this->assertEquals( 'ORM\Tests\Mock\SDBOwner', get_class($owner),
            "Find was confused by single quote in placeholder"
        );
    }

    public function testCreateComma() {
        $owner = new Mock\SDBOwner();
        $owner->name = "This is, a silly";

        $this->assertTrue( $owner->save() );

        $fetched = Mock\SDBOwner::Find( $owner->id() );
        $this->assertEquals( $owner->name, $fetched->name );
    }

    public function testUpdateComma() {
        $owner = Mock\SDBOwner::Find();
        $owner->name = "This is, a silly";

        $this->assertTrue( $owner->save() );

        $fetched = Mock\SDBOwner::Find( $owner->id() );
        $this->assertEquals( $owner->name, $fetched->name );
    }

    public function testCreateComplex() {
        $owner = new Mock\SDBOwner();
        $owner->name = "This i', s',\na =  silly";

        $this->assertTrue( $owner->save() );

        $fetched = Mock\SDBOwner::Find( $owner->id() );
        $this->assertEquals( $owner->name, $fetched->name );
    }

    public function testUpdateComplex() {
        $owner = Mock\SDBOwner::Find();
        $owner->name = "This \ni's', 'a silly\\\\',";

        $this->assertTrue( $owner->save(), "Unable to save owner: {$owner->errorMessagesString()}" );

        $fetched = Mock\SDBOwner::Find( $owner->id() );
        $this->assertEquals( $owner->name, $fetched->name );
    }

    public function testEscapeCreate() {
        $owner = new Mock\SDBOwner();
        $owner->name = "Th''is i's a \'silly";

        $this->assertTrue( $owner->save(), "Unable to save owner: {$owner->errorMessagesString()}" );

        $fetched = Mock\SDBOwner::Find( $owner->id() );
        $this->assertEquals( $owner->name, $fetched->name );
    }

    public function testEscapeUpdate() {
        $owner = Mock\SDBOwner::Find();
        $owner->name = "This i's a \'silly";

        $this->assertTrue( $owner->save(), "Unable to save owner: {$owner->errorMessagesString()}" );

        $fetched = Mock\SDBOwner::Find( $owner->id() );
        $this->assertEquals( $owner->name, $fetched->name );
    }

    public function testCreateWithID() {
        $owner = new Mock\SDBOwner();
        $owner->name = 'MyNewOwner';
        $id = rand(1, 999999999). 'myID'.rand(1,100);
        $owner->id($id);

        $this->assertTrue( $owner->save(true), "Failed saving: ".$owner->errorMessagesString() );

        $stored = Mock\SDBOwner::Find($id);
        $this->assertEquals( $owner->name, $stored->name );
    }

    public function testFindWith() {
        $this->_setupForeignKeysTest();
        
        $carWithOwner = Mock\SDBCar::FindByBrand( 'Volkswagen', '\ORM\Tests\Mock\SDBOwner');

        $this->assertEquals( 'MyCarsOwner', $carWithOwner->SDBOwner->name );
    }

    public function testFindAllWith() {
        $this->_setupForeignKeysTest();
        $carsWithOwners = Mock\SDBCar::FindAll(array(), '\ORM\Tests\Mock\SDBOwner');

        foreach($carsWithOwners as $car ) {
            $this->assertEquals( 'MyCarsOwner', $car->SDBOwner->name );
        }
    }

    private function _setupForeignKeysTest() {
        $owner = new Mock\SDBOwner();
        $owner->name = 'MyCarsOwner';
        $id = rand(1, 999999999). 'myID'.rand(1,100);
        $owner->id($id);

        $this->assertTrue( $owner->save(true), "Failed saving: ".$owner->errorMessagesString() );
        $cars = Mock\SDBCar::FindAll();
        $cars->each(function($car)use($id){
            $car->owner_id = $id;
        });
        
        $cars->save();
    }
}
?>