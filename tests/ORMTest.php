<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests;
error_reporting(E_ALL);

require_once dirname(__FILE__) . '/../AutoLoader.php';
require_once '../plugins/aws-sdk-1.3.2/sdk.class.php';

\ORM\Utilities\Configuration::Load('test.ini');

if( function_exists('apc_clear_cache') ) {
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
