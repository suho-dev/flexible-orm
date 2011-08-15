<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * PHPUnit tests for flexible-orm
 */
namespace ORM\Tests;
error_reporting(E_ALL);

require_once __DIR__ . '/../AutoLoader.php';
require_once __DIR__ . '/../../plugins/aws-sdk-1.3.7/sdk.class.php';

\ORM\Utilities\Configuration::Load(__DIR__.'/test.ini');
\ORM\Utilities\Configuration::SetCacheClass('\ORM\Utilities\Cache\APCCache');

if ( function_exists('apc_clear_cache') ) {
    $cache = new \ORM\Utilities\Cache\APCCache();
    $cache->flush();
}

/**
 * Description of ORMTestClass
 *
 */
class ORMTest extends \PHPUnit_Framework_TestCase{
    public function testTruth() {
        $this->assertTrue( 1 == 1 );
    }
}
?>
