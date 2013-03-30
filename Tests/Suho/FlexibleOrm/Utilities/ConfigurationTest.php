<?php
/**
 * Tests for Configuration class
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\Utilities;

use Tests\Suho\FlexibleOrm\ORMTest;

require_once '../ORMTest.php';

/**
 * Test class for Configuration.
 * 
 */
class ConfigurationTest extends ORMTest {

    /**
     * Clear and reload the Configuration details before each test
     */
    protected function setUp() {
        Configuration::Clear();
        Configuration::Load("$this->pathToTestRoot/test.ini");
    }

    public function testValueNull() {
        $this->assertNull( Configuration::Value('non-existant', 'test') );
    }

    public function testValueFalse() {
        $this->assertFalse( Configuration::Value('boolean', 'test') );
    }

    public function testValue() {
        $this->assertEquals( 'value', Configuration::Value('property', 'test') );
    }

    public function testRemove() {
        $this->assertEquals( 'value', Configuration::Value('property', 'test') );
        Configuration::Remove('property', 'test');
        $this->assertNull( Configuration::Value('property', 'test') );
    }

    public function testCall() {
        $this->assertEquals( 'test_user', Configuration::database('user') );
    }

    public function testCallOO() {
        $this->assertInstanceOf( __NAMESPACE__.'\ConfigurationGroup', Configuration::test() );
        $this->assertEquals('value', Configuration::test()->property);
    }

    public function testCallOONull() {
        $this->assertNull( Configuration::test()->non_existant );
    }
}

