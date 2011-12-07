<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;
require_once '../ORMTest.php';

/**
 */
class SDBFactoryTest  extends \ORM\Tests\ORMTest {
    public function setUp() {
        \ORM\Tests\Mock\SDBCar::CreateDomain();
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
        
        $query      = 'SELECT * FROM cars WHERE itemName() = "'.$id.'"';
        $statement  = SDBFactory::Get($query);
        $result     = $statement->fetch(SDBStatement::FETCH_ASSOC);
        
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