<?php
/**
 * Tests for ModelCollection class
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests;
use \ORM\Tests\Mock, \ORM\PDOFactory, \ORM\DEBUG;

require_once 'ORMTest.php';

PDOFactory::GetFactory()->startProfiling();


/**
 * Test class for ORM_Model
 */
class ModelCollectionTest extends ORMTest {

    /**
     * @var ModelCollection $object
     */
    protected $object;

    protected function setUp() {
        $this->object = Mock\Car::FindAll();
    }

    protected function tearDown() {
        $fords = Mock\Car::FindAllByBrand('Ford');
        $fords->delete();
    }

    public function testSave() {
        $startingAge = $this->object[3]->age;
        $this->object[3]->age = ++$startingAge;

        $this->object[] = new Mock\Car(array(
            'brand'     => 'Ford',
            'colour'    => 'Black',
            'owner_id'  => 3,
            'doors'     => 2,
            'age'       => 100
        ));

        $ids = $this->object->save();

        $toyota = Mock\Car::Find(4);
        $this->assertEquals( $startingAge, $toyota->age );
        $this->assertEquals( 5, count($ids) );
        $this->assertEquals( $ids[4], Mock\Car::FindByBrand('Ford')->id() );
    }

    public function testDelete() {
        $ford = new Mock\Car(array(
            'brand'     => 'Ford',
            'colour'    => 'Black',
            'owner_id'  => 3,
            'doors'     => 2,
            'age'       => 100
        ));

        $ford->save();

        $fords = Mock\Car::FindAllByBrand('Ford');
        // Check the ford exists
        $this->assertEquals( 1, count($fords) );

        $fords->delete();

        $fords = Mock\Car::FindAllByBrand('Ford');
        $this->assertEquals( 0, count($fords) );
    }

    public function testSelect() {
        $blackCars = $this->object->select('colour', 'black');
        $this->assertEquals( 'ORM\ModelCollection', get_class( $blackCars ) );
        $this->assertEquals( 2, count($blackCars) );
        $this->assertFalse( $blackCars->detect(function($car){
            $car->colour != 'black';
        }));

        $notVolkswagens = $this->object->select(function($car){
            return $car->brand != 'Volkswagen';
        });

        $this->assertEquals( 2, count($notVolkswagens) );
        $this->assertFalse( $blackCars->detect(function($car){
            $car->colour == 'Volkswagen';
        }));
    }
}
?>