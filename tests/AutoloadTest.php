<?php
/**
 * Tests for Configuration class
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests;
use \ORM\AutoLoader;

require_once dirname(__FILE__) . '/ORMTest.php';

/**
 * Test class for Configuration.
 *
 */
class AutoLoaderTest extends ORMTest {
    function testLocate() {
        $this->assertEquals(
            '/server/projects/orm.2.0/Utilities/Configuration.php',
            AutoLoader::Get()->locate( 'ORM\Utilities\Configuration')
        );

        $this->assertEquals(
            'configuration.php',
            AutoLoader::Get()->locate('Configuration')
        );

        $this->assertEquals(
            '/server/projects/orm.2.0/Tests/Mock/Owner.php',
            AutoLoader::Get()->locate('ORM\Tests\Mock\Owner')
        );
    }

    function testLocateUnknownPackage() {
        // Slashes will work either way in Windows, but must be / for *nix servers
        $this->assertEquals(
            'C:\server\xampp\php\PEAR/Zend/Pdf/FileParser/Font.php',
            AutoLoader::Get()->locate('Zend\Pdf\FileParser\Font')
        );
    }

    function testLocatePackage() {
        $this->assertEquals(
            '/server/projects/controller.1.1/',
            AutoLoader::Get()->locatePackage('\Controller\\')
        );
    }
}
?>
