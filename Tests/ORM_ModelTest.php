<?php
/**
 * Tests for ORM_Model class
 * @file
 * @author jarrod.swift
 * @todo Fix the autoloader
 */
namespace ORM;
use \ORM\Tests\Mock, \ORM\PDOFactory, \ORM\DEBUG;

require_once 'ORMTest.php';

PDOFactory::Get("TRUNCATE TABLE `cars`")->execute();

/**
 * Test class for ORM_Model
 */
class ORM_ModelTest extends Tests\ORMTest {
    public function setUp() {
        PDOFactory::Get("INSERT INTO `cars` (`id`, `brand`, `colour`, `doors`, `owner_id`, `name`, `age`, `type`) VALUES
            (1, 'Alfa Romeo', 'red', 4, 1, '156Ti', 4, 'Sedan'),
            (2, 'Volkswagen', 'black', 5, 1, NULL, 0, NULL),
            (3, 'Volkswagen', 'black', 2, 2, NULL, 0, NULL),
            (4, 'Toyota', 'White', 4, 2, NULL, 62, NULL)")->execute();
    }
    
    public function tearDown() {
        PDOFactory::Get("TRUNCATE TABLE `cars`")->execute();
    }
    
    public function testTableName() {
        $this->assertEquals(
            'cars',
            Mock\Car::TableName()
        );

        $this->assertEquals(
            'my_elephants',
            Mock\Elephant::TableName()
        );
        
        $this->assertEquals(
            'canaries',
            Mock\Canary::TableName()
        );
    }

    public function testFind() {
        $car = Mock\Car::Find(1);

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Car',
            get_class( $car )
        );

        $this->assertEquals(
            1,
            $car->id
        );

        $this->assertEquals(
            'Alfa Romeo',
            $car->brand
        );
    }

    public function testFindFalse() {
        $this->assertFalse( Mock\Car::Find(1000) );
    }

    public function testFindWithOptions() {
        $car = Mock\Car::Find(array(
            'where' => 'brand LIKE "Alfa Romeo"'
        ), 'ORM\Tests\Mock\Owner');

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Car',
            get_class( $car )
        );

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Owner',
            get_class( $car->Owner )
        );

        $this->assertEquals(
            'Jarrod',
            $car->Owner->name
        );

        $this->assertEquals(
            '156Ti',
            $car->model
        );
    }

    public function testForeignKey() {
        $this->assertEquals('owner_id', Mock\Car::ForeignKey('ORM\Tests\Mock\Owner'));
        $this->assertEquals('brand',    Mock\Car::ForeignKey('ORM\Tests\Mock\Manufacturer'));
    }

    public function testFindWith() {
        $car = Mock\Car::Find( 1, 'ORM\Tests\Mock\Owner');

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Car',
            get_class( $car )
        );

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Owner',
            get_class( $car->Owner )
        );

        $this->assertEquals(
            'Jarrod',
            $car->Owner->name
        );

        $this->assertEquals(
            '156Ti',
            $car->model
        );
    }

    public function testFindOptions() {
        $brand  = 'Alfa Romeo';
        $car    = Mock\Car::Find(array(
            'where' => 'doors > ? AND brand NOT LIKE ?',
            'order' => 'colour DESC',
            'values' => array( 3, $brand )
        ));

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Car',
            get_class( $car )
        );

        $this->assertNotEquals(
            'Alfa Romeo',
            $car->brand
        );

        $this->assertTrue(
            $car->doors > 3
        );
    }

    public function testFindWithForeignOptions() {
        $car = Mock\Car::Find(array(
            'where' => 'Manufacturer.country = "Italy"'
        ), 'ORM\Tests\Mock\Manufacturer');
        
        $this->assertEquals(
            'ORM\\Tests\\Mock\\Manufacturer',
            get_class( $car->Manufacturer )
        );

        $this->assertEquals(
            '156Ti',
            $car->model
        );
    }

    public function testFindMultipleForeignKeys() {
        $car = Mock\Car::Find( 1, array('ORM\Tests\Mock\Owner', 'ORM\Tests\Mock\Manufacturer') );

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Manufacturer',
            get_class( $car->Manufacturer )
        );

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Car',
            get_class( $car )
        );

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Owner',
            get_class( $car->Owner )
        );

        $this->assertEquals(
            'Jarrod',
            $car->Owner->name
        );

        $this->assertEquals(
            '156Ti',
            $car->model
        );
    }

    public function testFindOptionsNamed() {
        $brand  = 'Alfa Romeo';
        $car    = Mock\Car::Find(array(
            'where' => 'doors > :doors AND brand NOT LIKE :brand',
            'order' => 'colour DESC',
            'values' => array( ':brand' => $brand, ':doors' => 3 )
        ));

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Car',
            get_class( $car )
        );

        $this->assertNotEquals(
            'Alfa Romeo',
            $car->brand
        );

        $this->assertTrue(
            $car->doors > 3
        );
    }

    public function testFindNoOptions() {
        $car    = Mock\Car::Find();

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Car',
            get_class( $car )
        );

        $this->assertEquals(
            'Alfa Romeo',
            $car->brand
        );
    }

    public function testFindAllWith() {
        $cars = Mock\Car::FindAll(array(), 'ORM\Tests\Mock\Manufacturer');

        $this->assertEquals( 'ORM\ModelCollection', get_class($cars));
        
        $this->assertEquals( 4, count($cars) );

        $this->assertEquals( 'Germany', $cars[1]->Manufacturer->country );
    }

    public function testFindBy() {
        $car = Mock\Car::FindByBrand('Alfa Romeo');

        $this->assertEquals(
            'ORM\\Tests\\Mock\\Car',
            get_class( $car )
        );

        $this->assertEquals(
            'Alfa Romeo',
            $car->brand
        );
    }

    public function testFindAllBy() {
        $cars = Mock\Car::FindAllByBrand('Volkswagen');

        $this->assertEquals(
            'ORM\\ModelCollection',
            get_class( $cars )
        );
        
        $this->assertEquals(
            2,
            count( $cars )
        );

        foreach ( $cars as $car ) {
            $this->assertEquals(
                'ORM\\Tests\\Mock\\Car',
                get_class( $car )
            );

            $this->assertEquals(
                'Volkswagen',
                $car->brand
            );
        }
    }

    public function testFindAllByNoRecords() {
        $owners = Mock\Owner::FindAllByAge(10000000, '>');

        $this->assertEquals( 0, count($owners) );
        $this->assertEquals( 'ORM\ModelCollection',get_class($owners) );
    }

    /**
     * @expectedException \ORM\Exceptions\ORMFindByInvalidFieldException
     */
    public function testFindAllByInvalid() {
        $owners = Mock\Owner::FindAllByHeight(12, '>');
    }

    /**
     * @expectedException \ORM\Exceptions\ORMFindByInvalidFieldException
     */
    public function testFindByInvalid() {
        $owners = Mock\Owner::FindByHeight(100);
    }

    public function testFindAll() {
        $cars = Mock\Car::FindAll(array(
            'where'     => 'brand NOT LIKE ? AND doors < ?',
            'values'    => array( 'Volkswagen', 5 )
        ));

        $this->assertEquals(
            'ORM\\ModelCollection',
            get_class( $cars )
        );

        $this->assertEquals(
            2,
            count( $cars )
        );

        foreach ( $cars as $car ) {
            $this->assertEquals(
                'ORM\\Tests\\Mock\\Car',
                get_class( $car )
            );

            $this->assertNotEquals(
                'Volkswagen',
                $car->brand
            );

            $this->assertTrue(
                $car->doors < 5
            );
        }
    }

    public function testFindAllByForeign() {
        $cars = Mock\Car::FindAllByDoors(5, '<', array('ORM\Tests\Mock\Owner', 'ORM\Tests\Mock\Manufacturer') );

        $this->assertEquals( 'ORM\ModelCollection', get_class($cars));

        $this->assertEquals( 3, count($cars) );

        $this->assertEquals( 'Japan', $cars[2]->Manufacturer->country );
        $this->assertEquals( 'Steve', $cars[2]->Owner->name );
    }

    public function testFindAllNoOptions() {
        $cars = Mock\Car::FindAll();
        $this->assertEquals(
            'ORM\\ModelCollection',
            get_class( $cars )
        );

        $this->assertEquals(
            4,
            count( $cars )
        );
    }

    public function testFindAllByGreaterThan() {
        $cars = Mock\Car::FindAllByDoors(4, '>=');

        $this->assertEquals(
            'ORM\\ModelCollection',
            get_class( $cars )
        );

        $this->assertEquals(
            3,
            count( $cars )
        );

        foreach ( $cars as $car ) {
            $this->assertEquals(
                'ORM\\Tests\\Mock\\Car',
                get_class( $car )
            );

            $this->assertTrue(
                $car->doors >= 4
            );
        }
    }

    public function testPrimaryKeyName() {
        $this->assertEquals('id', Mock\Car::PrimaryKeyName() );
        $this->assertEquals('name', Mock\Elephant::PrimaryKeyName() );
    }

    public function testDescribeTable() {
        $describe = Mock\Elephant::DescribeTable();

        $this->assertEquals( 2, count( $describe ) );
        $this->assertTrue( in_array('name', $describe) );
        $this->assertTrue( in_array('weight', $describe) );
    }

    public function testCreate() {
        $elephant = new Mock\Elephant();

        $elephant->name     = "Roger";
        $elephant->weight   = 1234.5;

        $this->assertTrue( $elephant->save() );
    }

    public function testCreateWithAutoIncrement() {
        $owner = new Mock\Owner();
        $owner->name = 'Fred';
        $owner->age  = rand(0,120);
        $owner->species = 'Human';
        $owner->save();

        $retrieved = Mock\Owner::Find( $owner->id() );

        $this->assertEquals( $owner->age, $retrieved->age );

    }

    public function testUpdate() {
        $elephant = new Mock\Elephant();
        $elephant->name     = "Tim";
        $elephant->weight   = 1000;

        $this->assertTrue( $elephant->save(), 'Unable to save elephant: ', $elephant->errorMessagesString() );

        $elephant = Mock\Elephant::Find( 'Tim' );
        $this->assertEquals( 1000, $elephant->weight );
        $elephant->weight = 1400;

        $this->assertTrue( $elephant->save() );

        $elephant = Mock\Elephant::Find( 'Tim' );
        $this->assertEquals( 1400, $elephant->weight );

    }
    
    public function testUpdateInvalid() {
        $elephant = new Mock\Elephant();
        $elephant->name     = "Tim";
        $elephant->weight   = 1000;

        $this->assertTrue( $elephant->save(), 'Unable to save elephant: ', $elephant->errorMessagesString() );

        $elephant = Mock\Elephant::Find( 'Tim' );
        $elephant->weight = -100;

        $this->assertFalse( $elephant->save() );

        $elephant = Mock\Elephant::Find( 'Tim' );
        $this->assertEquals( 1000, $elephant->weight, "Invalid data was saved" );
    }
    
    public function testUpdateNoChanges() {
        $elephant = new Mock\Elephant();
        $elephant->name     = "Tim";
        $elephant->weight   = 1000;

        $this->assertTrue( $elephant->save(), 'Unable to save elephant: ', $elephant->errorMessagesString() );

        $elephant = Mock\Elephant::Find( 'Tim' );
        $elephant->weight = $elephant->weight;

        $this->assertTrue( $elephant->save() );
    }

    public function testDelete() {
        $ford = new Mock\Car(array(
            'brand'     => 'Ford',
            'colour'    => 'Black',
            'owner_id'  => 3,
            'doors'     => 2,
            'age'       => 100
        ));

        $this->assertTrue($ford->valid());
        $ford->save();

        // Ensure it has been created for this to make sense as a test
        $this->assertEquals( $ford->id(), Mock\Car::Find($ford->id())->id() );

        $ford->delete();
        $this->assertFalse( Mock\Car::Find($ford->id()) );
    }

    public function testSaveCreateInvalid() {
        $elephant = new Mock\Elephant();
        $elephant->name     = "Eric";

        $this->assertFalse($elephant->valid());
        $this->assertFalse($elephant->save());
    }

    public function testSaveUpdatePartial() {
        $elephant       = new Mock\Elephant();
        $elephant->name = "me";

        $this->assertFalse($elephant->valid(), 'Item should be invalid as the object is incomplete');
        $this->assertTrue($elephant->save(), 'Item should be valid as data exists in the database');
        
    }

    public function testDestroy() {
        $ford = new Mock\Car(array(
            'brand'     => 'Ford',
            'colour'    => 'Black',
            'owner_id'  => 3,
            'doors'     => 2,
            'age'       => 100
        ));

        $ford->save();

//        $ford->save();

        // Ensure it has been created for this to make sense as a test
        $this->assertEquals( $ford->id(), Mock\Car::Find($ford->id())->id() );

        Mock\Car::Destroy( $ford->id() );
        $this->assertFalse( Mock\Car::Find($ford->id()) );
    }
    
    public function testSaveTwice() {
        $ford = new Mock\Car(array(
            'brand'     => 'Ford',
            'colour'    => 'Black',
            'owner_id'  => 3,
            'doors'     => 2,
            'age'       => 100,
            'model'     => 'xj'
        ));

        $this->assertTrue( $ford->save() );
        $id = $ford->id();
        $ford->load();
//        print_r($ford);die();
        $this->assertEquals( $ford->model, $ford->originalValue('model') );
        
        $this->assertTrue( $ford->save() );
        
        $this->assertEquals($id, $ford->id() );
    }

    /**
     * @expectedException \ORM\Exceptions\ORMPDOException
     */
    public function testUpdateException() {
        $ford = new Mock\BadModel(array(
            'id'        => 1,
            'brand'     => 'Ford',
            'colour'    => 'Black',
            'owner_id'  => 3,
            'doors'     => 2,
            'age'       => 100
        ));

        $ford->save();
    }

    public function testUpdateArray() {
        $options = array(1=>'Sedan', 2=>'Coupe');
        $type = $options[rand(1,2)];
        $car = new Mock\Car(array(
            'id'    => 1,
            'type'  => $type
        ));

        $this->assertTrue( $car->save() );

        $alfa = Mock\Car::Find(1);
        $this->assertEquals('Alfa Romeo', $alfa->brand );
        $this->assertEquals($type, $alfa->type );
    }

    /**
     * Test by ensuring the correct global variables were set and that they
     * were set in order.
     */
    public function testBeforeAfterCreate() {
        $fred = new Mock\Elephant(array('name' => 'Fred', 'weight' => 123454));

        $this->assertTrue( $fred->save() );
        $this->assertTrue( $GLOBALS['beforeSave'] );
        $this->assertTrue( $GLOBALS['beforeCreate'] );
        $this->assertTrue( $GLOBALS['afterCreate'] );
        $this->assertTrue( $GLOBALS['afterSave'] );

        $this->assertFalse( isset($GLOBALS['beforeUpdate']) );
        $this->assertFalse( isset($GLOBALS['afterUpdate']) );

        $globalVariables = array_keys($GLOBALS);
        $this->assertGreaterThan(
                array_search('beforeCreate', $globalVariables),
                array_search('afterCreate', $globalVariables)
        );
        $this->assertGreaterThan(
                array_search('beforeSave', $globalVariables),
                array_search('afterSave', $globalVariables)
        );

        $fred->delete();
    }
    
    public function testBeforeAfterUpdate() {
        $tim = Mock\Elephant::Find( 'Tim' );
        $tim->weight++;

        $this->assertTrue( $tim->save() );
        $this->assertTrue( $GLOBALS['beforeSave'] );
        $this->assertTrue( $GLOBALS['beforeUpdate'] );
        $this->assertTrue( $GLOBALS['afterUpdate'] );
        $this->assertTrue( $GLOBALS['afterSave'] );

        $this->assertFalse( isset($GLOBALS['beforeCreate']) );
        $this->assertFalse( isset($GLOBALS['afterCreate']) );

        $globalVariables = array_keys($GLOBALS);
        $this->assertGreaterThan( 
                array_search('beforeUpdate', $globalVariables),
                array_search('afterUpdate', $globalVariables)
        );
        $this->assertGreaterThan(
                array_search('beforeSave', $globalVariables),
                array_search('afterSave', $globalVariables)
        );
    }

    public function testLoad() {
        $car = new Mock\Car(array('id' => 2, 'name' => 'leo') );
        $car->load();

        $this->assertEquals( 2, $car->id );
        $this->assertEquals( "leo", $car->name );
        $this->assertEquals( "Volkswagen", $car->brand );
        $this->assertEquals( 5, $car->doors );
    }

    public function testLoadNewObject() {
        $car = new Mock\Car(array('id' => 100000, 'name' => 'leo') );
        $car->load();

        $this->assertEquals( 100000, $car->id );
        $this->assertEquals( "leo", $car->name );
        $this->assertFalse( isset($car->brand) );
    }

    public function testPossibleValues() {
        $this->assertEquals(
            array( 1=> 'Sedan', 2=> 'Coupe'),
            Mock\Car::PossibleValues('type')
        );

        $this->assertEquals(
            array(),
            Mock\Car::PossibleValues('doors')
        );
    }

    /**
     * Create a new object but don't include all fields
     */
    public function testCreateWithDefaults() {
        $owner = new Mock\Owner();
        $owner->name = 'Fred';
        $owner->age  = rand(0,120);

        $this->assertTrue( $owner->save() );
    }
    
    public function testFindAllWithLimit() {
        $owners = Mock\Owner::FindAll(array(
            'limit' => 2
        ));
        
        $this->assertEquals( 2, count($owners) );
        
        $moreOwners = Mock\Owner::FindAll(array(
            'limit'  => 2,
            'offset' => 2
        ));
        
        $this->assertNotEquals( $owners[0]->id(), $moreOwners[0]->id() );
        $this->assertGreaterThan( 1, count($moreOwners) );
    }
    
    public function testCountFindAll() {
        $carCount = Mock\Car::CountFindAll();
        
        $this->assertEquals( 4, $carCount );
    }
    
    public function testCountFindAllBy() {
        $carCount = Mock\Car::CountFindAllByColour('black');
        
        $this->assertEquals( 2, $carCount );
    }
    
    public function testAfterGet() {
        $car = Mock\Car::Find();
        $this->assertEquals( $car->brand, $car->testValue() );
        
        $newCar = new Mock\Car(array('brand' => 'ferrari'));
        $this->assertEquals( 'initial', $newCar->testValue() );
        
        $cars = Mock\Car::FindAll();
        
        foreach ( $cars as $car ) {
            $this->assertEquals( $car->brand, $car->testValue() );
        }
    }
    
    /**
     * @expectedException \ORM\Exceptions\ORMInvalidStaticMethodException
     */
    public function testInvalidStaticMethod() {
        $car = Mock\Car::Blurt();
    }
    
    public function testToString() {
        $car = Mock\Car::Find();
        $this->assertEquals( 'ORM\Tests\Mock\Car ['.$car->id().']', (string)$car );
    }
    
    public function testPropertyType() {
        $this->assertEquals(
            'int(1)',
            Mock\Car::PropertyType('doors')
        );
    }
    
    public function testPropertyTypeAlias() {
        $this->assertEquals(
            'varchar(32)',
            Mock\Car::PropertyType('model')
        );
    }
}