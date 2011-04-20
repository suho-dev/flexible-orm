<?php
/**
 * Tests for ORM_Model class
 * @file
 * @author jarrod.swift
 * @todo Fix the autoloader
 */
namespace ORM\Tests;
use \ORM\Tests\Mock, \ORM\PDOFactory, \ORM\DEBUG;

require_once 'ORMTest.php';
PDOFactory::GetFactory()->startProfiling();


/**
 * Test class for ORM_Model using multiple databases
 */
class ORMSecondDatabaseTest extends ORMTest {

    public function testDatabaseConfig() {
        $this->assertEquals('secondDatabase', Mock\AlternateCar::DatabaseConfigName() );
    }

    public function testFind() {
        $car = Mock\AlternateCar::Find(1);

        $this->assertEquals(
            'ORM\\Tests\\Mock\\AlternateCar',
            get_class( $car )
        );

        $this->assertEquals(
            1,
            $car->id
        );

        $this->assertEquals(
            'Ferrari',
            $car->brand
        );
    }


    public function testFindOptions() {
        $brand  = 'Ferrari';
        $car    = Mock\AlternateCar::Find(array(
            'where' => 'doors > ? AND brand NOT LIKE ?',
            'order' => 'colour DESC',
            'values' => array( 3, $brand )
        ));

        $this->assertEquals(
            'ORM\\Tests\\Mock\\AlternateCar',
            get_class( $car )
        );

        $this->assertNotEquals(
            $brand,
            $car->brand
        );

        $this->assertTrue(
            $car->doors > 3
        );
    }

    public function testFindBy() {
        $car = Mock\AlternateCar::FindByBrand('Ferrari');

        $this->assertEquals(
            'ORM\\Tests\\Mock\\AlternateCar',
            get_class( $car )
        );

        $this->assertEquals(
            'Ferrari',
            $car->brand
        );
    }

    public function testFindAllBy() {
        $cars = Mock\AlternateCar::FindAllByBrand('Volkswagen');

        $this->assertEquals(
            'ORM\\ModelCollection',
            get_class( $cars )
        );

        $this->assertEquals(
            2,
            count( $cars )
        );

        foreach( $cars as $car ) {
            $this->assertEquals(
                'ORM\\Tests\\Mock\\AlternateCar',
                get_class( $car )
            );

            $this->assertEquals(
                'Volkswagen',
                $car->brand
            );
        }
    }
    public function testFindAll() {
        $cars = Mock\AlternateCar::FindAll(array(
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

        foreach( $cars as $car ) {
            $this->assertEquals(
                'ORM\\Tests\\Mock\\AlternateCar',
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
}
?>