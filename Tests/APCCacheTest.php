<?php
/**
 * Tests for Configuration class
 * @file
 * @author jarrod.swift
 */
namespace FlexibleORMTests;

use ORM\Utilities\Cache\APCCache;
use PHPUnit_Framework_TestCase;

/**
 * Test class for APCCache.
 *
 */
class APCCacheTest extends PHPUnit_Framework_TestCase {
    /**
     * @var APCCache $object
     */
    protected $object;

    protected function setUp() {
        $this->object = new APCCache();
        // Unfortunately we can 'set' this ini since it's a PHP_INI_SYSTEM configuration.
        // echo "apc.enable_cli = 1" | sudo tee /etc/php5/conf.d/90-enable-apc-for-cli.ini
        $this->assertEquals(1, ini_get('apc.enable_cli'));
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