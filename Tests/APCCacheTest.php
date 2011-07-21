<?php
/**
 * Tests for Configuration class
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests;
use \ORM\Cache, \ORM\Utilities\Cache\APCcache;

require_once dirname(__FILE__) . '/ORMTest.php';

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
        $this->object = new APCcache();
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