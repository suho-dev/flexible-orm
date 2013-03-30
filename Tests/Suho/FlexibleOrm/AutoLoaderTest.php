<?php
/**
 * Tests for Configuration class
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm;

use Mock_Zend_TestClass;
use Tests\Suho\FlexibleOrm\ORMTest;

require_once dirname(__FILE__) . '/ORMTest.php';

/**
 * Test class for Configuration.
 * 
 */
class AutoLoaderTest extends ORMTest {
    /**
     * @var AutoLoader $autoloader
     */
    protected $autoloader;
    
    private $defaultPackageLocations;
    
    function setUp() {
        $this->defaultPackageLocations = array(
            'Suho\FlexibleOrm' => $this->pathToFlexibleOrm,
            'Suho\Roborater'   => '/sites/roborater',
            'Mock'             => "$this->pathToTestRoot/Mock",
        );
        
        $this->autoloader = new AutoLoader($this->defaultPackageLocations);
    }
    
    function testLocate() {
        $this->assertEquals(
            realpath("$this->pathToFlexibleOrm/Utilities/Configuration.php"),
            $this->autoloader->locate( __NAMESPACE__.'\Utilities\Configuration')
        );

        $this->assertEquals(
            'configuration.php',
            $this->autoloader->locate('Configuration')
        );

        $this->assertEquals(
            "$this->pathToTestRoot/Mock/Owner.php",
            $this->autoloader->locate('Mock\Owner')
        );
    }
    
    function testResetPackageLocations() {
        $expected = array(
            'Helpdesk'  => '/server/projects/helpdesk'
        );
        
        $this->autoloader->setPackageLocations($expected);
        
        $expected[str_replace("\\", "\\\\", __NAMESPACE__)] = $this->defaultPackageLocations[__NAMESPACE__];
        
        $this->assertEquals(
            $expected,
            $this->autoloader->getPackageLocations()
        );
    }

    function testLocateUnknownPackage() {
        // This test requires PHPUnit to be in the include path
        
        $this->assertStringEndsWith( 
            '/PHPUnit/Framework/Assert.php', 
            $this->autoloader->locate('PHPUnit\Framework\Assert')
        );
    }

    function testLocatePackage() {
        $this->assertEquals(
            '/sites/roborater/Simulation',
            $this->autoloader->locatePackage('Suho\Roborater\Simulation')
        );
        $this->assertEquals(
            "{$this->defaultPackageLocations['Mock']}/Zend/TestClass",
            $this->autoloader->locatePackage('Mock\Zend\TestClass')
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
     * @expectedException \Suho\FlexibleOrm\Exceptions\IncludePathDoesNotExistException
     */
    function testAddIncludePathInvalid() {
        $this->autoloader->addIncludePath('/would/be/suprising/if/this/existed');
    }
    
    /**
     * @expectedException \Suho\FlexibleOrm\Exceptions\IncludePathIsNotADirectoryException
     */
    function testAddIncludePathFile() {
        $this->autoloader->addIncludePath( __FILE__ );
    }
 
    function testLoadZend() {
        $this->assertTrue( 
            $this->autoloader->loadZend('Mock_Zend_TestClass'),
            'loadZend unable to locate Mock_Zend_TestClass'
        );
        
        $testObject = new Mock_Zend_TestClass;
        $this->assertTrue( $testObject->loaded );
    }
    
    function loadZendNotAvailable() {
        $this->assertFalse( 
            $this->autoloader->loadZend('Mock_Zend_AnotherTestClass'),
            'loadZend was able to locate Mock_Zend_AnotherTestClass, which should not exist'
        );
    }
    
    /**
     * Get an array of included paths
     */
    private function _getIncludePaths() {
        return explode( PATH_SEPARATOR, get_include_path() );
    }
}
