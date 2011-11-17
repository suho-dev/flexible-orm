<?php
/**
 * Tests for Configuration class
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests;
use ORM\AutoLoader;
use ORM\Utilities\Configuration;

require_once dirname(__FILE__) . '/ORMTest.php';


/**
 * Test class for Configuration.
 * 
 * @todo Rewrite this class so it works in all environments (currently only works
 *       for my environment)
 * 
 */
class AutoLoaderTest extends ORMTest {
    /**
     * @var AutoLoader $autoloader
     */
    protected $autoloader;
    
    function setUp() {
        $this->autoloader = new AutoLoader( Configuration::packages()->toArray() );
    }
    
    function testLocate() {
        $this->assertEquals(
            realpath(__DIR__.'/../Utilities/Configuration.php'),
            $this->autoloader->locate( 'ORM\Utilities\Configuration')
        );

        $this->assertEquals(
            'configuration.php',
            $this->autoloader->locate('Configuration')
        );

        $this->assertEquals(
            realpath(__DIR__.'/Mock/Owner.php'),
            $this->autoloader->locate('ORM\Tests\Mock\Owner')
        );
    }
    
    function testResetPackageLocations() {
        $this->autoloader->setPackageLocations(array(
            'Helpdesk'  => '/server/projects/helpdesk'
        ));
        
        $this->assertEquals(
            '/server/projects/helpdesk/models/user.php',
            $this->autoloader->locate('Helpdesk\Models\User')
        );
    }

    function testLocateUnknownPackage() {
        // This test reuires Zendframework to be in the PEAR path
        $this->assertEquals(
            'C:\server\xampp\php\pear/Zend/Pdf/FileParser/Font.php',
            $this->autoloader->locate('Zend\Pdf\FileParser\Font')
        );
    }

    function testLocatePackage() {
        $this->assertEquals(
            '/server/projects/controller.1.1/',
            $this->autoloader->locatePackage('\Controller\\')
        );
    }
    
    function testAddIncludePath() {
        $this->autoloader->addIncludePath('/my/test/path');
        
        $this->assertTrue( preg_match( ':/my/test/path:', get_include_path() ) > 0);
    }
}
