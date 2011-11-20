<?php
/**
 * Tests for Configuration class
 * @file
 * @author jarrod.swift
 */
namespace ORM;
use ORM\AutoLoader;
use ORM\Utilities\Configuration;

require_once dirname(__FILE__) . '/ORMTest.php';


/**
 * Test class for Configuration.
 * 
 */
class AutoLoaderTest extends Tests\ORMTest {
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

    /**
     * @todo Need to make this test not require PHPUnit to be in one specific place
     */
    function testLocateUnknownPackage() {
        // This test requires PHPUnit to be in the PEAR path
        $this->assertEquals(
            '/usr/lib/php/pear/PHPUnit/Framework/Assert.php',
            $this->autoloader->locate('PHPUnit\Framework\Assert')
        );
    }

    function testLocatePackage() {
        $this->assertEquals(
            '/server/projects/controller.1.1/',
            $this->autoloader->locatePackage('\Controller\\')
        );
    }
    
    function testAddIncludePath() {
        $includePathToAdd = realpath(__DIR__.'/../');
        $this->autoloader->addIncludePath($includePathToAdd);
        
        $this->assertTrue( in_array($includePathToAdd, $this->_getIncludePaths() ) );
        
        $includePathToAdd2 = '../somewhere/relative';
        $this->autoloader->addIncludePath($includePathToAdd2);
        
        $this->assertTrue( in_array($includePathToAdd, $this->_getIncludePaths()), "First path was removed: ".  get_include_path());
        $this->assertTrue( in_array($includePathToAdd2, $this->_getIncludePaths()), "Unable to find path: ".  get_include_path());
    }
    
    /**
     * Get an array of included paths
     */
    private function _getIncludePaths() {
        return explode( PATH_SEPARATOR, get_include_path() );
    }
    
    /**
     * Ensure that adding the path twice does not result in the inlcude_path
     * having the same path in it twice.
     * 
     * 
     */
    function testAddIncludePathTwice() {
        $includePathToAdd = __DIR__;
        $path = $this->autoloader->addIncludePath($includePathToAdd);
        
        $this->assertEquals( $path, $this->autoloader->addIncludePath($includePathToAdd) );
    }
    
    /**
     * @expectedException \ORM\Exceptions\IncludePathDoesNotExistException
     */
    function testAddIncludePathInvalid() {
        $this->autoloader->addIncludePath('/would/be/suprisingin/if/this/existed');
    }
    
    /**
     * @expectedException \ORM\Exceptions\IncludePathIsNotADirectoryException
     */
    function testAddIncludePathFile() {
        $this->autoloader->addIncludePath( __FILE__ );
    }
    
}
