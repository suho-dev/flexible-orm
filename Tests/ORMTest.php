<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * PHPUnit tests for flexible-orm
 */
namespace FlexibleORMTests;

use ORM\AutoLoader;
use ORM\Utilities\Cache\APCCache;
use ORM\Utilities\Configuration;
use PHPUnit_Framework_TestCase;

error_reporting(E_ALL);

require_once __DIR__ . '/../src/AutoLoader.php';
require_once 'AWSSDKforPHP/sdk.class.php';

$loader = new AutoLoader();
$loader->register(AutoLoader::AUTOLOAD_STYLE_FORM);
$loader->setPackageLocations(array('FlexibleORMTests' => __DIR__));

Configuration::Load(__DIR__.'/test.ini');
Configuration::SetCacheClass('\ORM\Utilities\Cache\APCCache');

if ( function_exists('apc_clear_cache') ) {
    $cache = new APCCache();
    $cache->flush();
}

/**
 * Description of ORMTestClass
 *
 */
class ORMTest extends PHPUnit_Framework_TestCase {
    public function testTruth() {
        $this->assertTrue( 1 == 1 );
    }
}
