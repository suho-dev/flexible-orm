<?php
/**
 * @file
 * @author jarrod.swift@suho.com.au
 */
namespace FlexibleORMTests;

/**
 * Description of ORMDatabaseTypeTest
 *
 */
abstract class ORMDatabaseTypeTest extends ORMTest {
    protected $carClass         = '/ORM/Mock/Car';
    protected $databaseConfig   = 'database';

    protected function setUp() {
    }
    
    protected function tearDown() {
    }
    
    /**
     * Make sure we got the correct database
     */
    public function testDatabaseConfig() {
        $class = $this->carClass;
        
        $this->assertEquals( 
            $this->databaseConfig, 
            $class::DatabaseConfigName()
        );
    }
    
    public function testCreateNew() {
        $class = $this->carClass;
        
        $foo = new $class();
        $foo->brand     = "BMW";
        $foo->colour    = "Orange";
        
        $this->assertTrue( $foo->save() );
    }
    
    public function testCreateAndFind() {
        $class = $this->carClass;
        
        $foo = new $class();
        $foo->brand     = "BMW";
        $foo->colour    = "Orange";
        $this->assertTrue( $foo->save() );
        
        echo "Looking for {$foo->id()}\n";
        $savedCar = $class::Find( $foo->id() );
        
        $this->assertEquals( $foo->brand, $savedCar->brand );
    }
    
    public function testUpdate() {
        $class = $this->carClass;
        
        $car = $class::Find();
        
        if( !$car ) {
            $car = new $class();
            $car->brand     = "BMW";
            $car->colour    = "Orange";
            $car->save();
        }
        
        $car->colour = 'red';
        
        $this->assertTrue( $car->save() );
    }
    
}
