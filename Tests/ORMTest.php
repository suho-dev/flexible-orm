<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * PHPUnit tests for flexible-orm
 */
namespace ORM\Tests;
use ORM\Utilities\Configuration;

error_reporting(E_ALL);
set_include_path(get_include_path() . PATH_SEPARATOR . realpath( __DIR__.'/../../plugins') );
require_once __DIR__ . '/../AutoLoader.php';
require_once 'AWSSDKforPHP/sdk.class.php';

$loader = new \ORM\AutoLoader();
$loader->register(\ORM\AutoLoader::AUTOLOAD_STYLE_FORM);

Configuration::Load(__DIR__.'/test.ini');
Configuration::SetCacheClass('\ORM\Utilities\Cache\APCCache');

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
