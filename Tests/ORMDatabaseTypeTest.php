<?php
/**
 * @file
 * @author jarrod.swift@suho.com.au
 */
namespace ORM\Tests;

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
        $cars;
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
        $foo->save();
    }
    
    public function testCreateAndFind() {
        $class = $this->carClass;
        
        $foo = new $class();
        $foo->brand     = "BMW";
        $foo->colour    = "Orange";
        $foo->save();
        
        $savedCar = $class::Find( $foo->id() );
        
        $this->assertEquals( $foo->brand, $savedCar->brand );
    }
    
}
