<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;

use PHPUnit_Framework_TestCase;

/**
 */
class SDBFactoryTest  extends PHPUnit_Framework_TestCase {
    public function setUp() {
        \FlexibleORMTests\Mock\SDBCar::CreateDomain();
    }
    
    public function testGet() {
        $query      = 'SELECT * FROM cars';
        $statement  = SDBFactory::Get($query);
        
        $this->assertInstanceOf('\ORM\SDB\SDBStatement', $statement);
        $this->assertEquals( $query, (string)$statement );
    }

    public function testLastInsertId() {
        $query      = 'INSERT INTO cars ( colour, doors ) VALUES ( "red", "two" )';
        $statement  = SDBFactory::Get($query);
        
        $statement->execute();
        $id = SDBFactory::LastInsertId();

        $this->assertGreaterThan(1, $id);
        
        $query      = 'SELECT * FROM cars WHERE itemName() = "'.$id.'"';
        $statement  = SDBFactory::Get($query);
        $statement->setConsistentRead( true );
        $statement->execute();
        $result     = $statement->fetch(SDBStatement::FETCH_ASSOC);
        
        $this->assertFalse( $result === false, "No results fetched for '$query'" );
        $this->assertEquals( 'red', $result['colour'] );
        $this->assertEquals( 'two', $result['doors'] );
    }
    
    /**
     * The fieldNames method does nothing currently
     */
    public function testFieldNames() {
        $this->markTestIncomplete();
    }

}