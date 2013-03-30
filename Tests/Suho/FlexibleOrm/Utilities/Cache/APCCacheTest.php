<?php
/**
 * Tests for Configuration class
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\Utilities\Cache;

use Tests\Suho\FlexibleOrm\ORMTest;

require_once  '../../ORMTest.php';

ini_set('apc.enable_cli', 'true');

if ( !function_exists('apc_clear_cache') ) {
    die("APC Not available");
}

/**
 * Test class for APCCache.
 *
 */
class APCCacheTest extends ORMTest {
    /**
     * @var APCCache $object
     */
    protected $object;

    protected function setUp() {
        $this->object = new APCCache();
    }

    protected function tearDown() {
        $this->object->flush();
    }

    function testSet() {
        $expected = "MyString";
        $this->object->set('testSet', $expected);

        $this->assertEquals($expected, $this->object->get('testSet') );
        $this->assertFalse( $this->object->get('non-existant-key') );
    }

    function testGet() {
        $this->assertFalse( $this->object->get('non-existant-key') );
    }

    function testAdd() {
        $expected = "MyString";
        $this->object->set('testAdd', $expected);

        $this->assertEquals($expected, $this->object->get('testAdd') );
    }

    function testDelete() {
        $expected = "MyStringToDelete";
        $this->object->set('testDelete', $expected);

        $this->assertEquals($expected, $this->object->get('testDelete') );

        $this->object->delete('testDelete');
        $this->assertFalse( $this->object->get('testDelete') );
    }

}