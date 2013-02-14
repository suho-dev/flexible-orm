<?php
namespace ORM\Controller;

use PHPUnit_Framework_TestCase;

/**
 * Description of ControllerRegisterTest
 *
 * @author jarrodswift
 */
class ControllerRegisterTest extends PHPUnit_Framework_TestCase {
    /**
     * @var ControllerRegister $register
     */
    protected $register;
    
    public function setUp() {
        $this->register = new ControllerRegister;
    }
    
    public function testRegisterNamespace() {
        $registeredNamespaces = $this->register->registerNamespace(
            '\FlexibleORMTests\Mock\Controller'
        );
        
        $this->assertEquals(array('\FlexibleORMTests\Mock\Controller'), $registeredNamespaces);
        
        $registeredNamespaces = $this->register->registerNamespace(
            '\FlexibleORMTests\Mock\AlternateController'
        );
        
        $this->assertEquals(array(
            '\FlexibleORMTests\Mock\Controller',
            '\FlexibleORMTests\Mock\AlternateController'
            ), $registeredNamespaces);
    }
    
    public function testRegisterController() {
        $registered = $this->register->registerController(
            'special', '\FlexibleORMTests\Mock\Controller\Cars'
        );
        
        $this->assertEquals(array('special'=>'\FlexibleORMTests\Mock\Controller\Cars'), $registered);
    }
    
    public function testGetClassName() {
        $this->register->registerController(
            'special', '\FlexibleORMTests\Mock\Controller\Cars'
        );
        
        $this->assertEquals(
            '\FlexibleORMTests\Mock\Controller\Cars',
            $this->register->getClassName('special')
        );
    }
    
    public function testGetNamespacedName() {
        $this->register->registerNamespace(
            '\FlexibleORMTests\Mock\Controller'
        );
        
        $this->assertEquals(
            '\FlexibleORMTests\Mock\Controller\Cars',
            $this->register->getClassName('cars')
        );
    }
    
    public function testGetUnknown() {
        $this->register->registerNamespace(
            '\FlexibleORMTests\Mock\Controller'
        );
        
        $this->assertFalse(
            $this->register->getClassName('owners')
        );
    }
    
    public function testGetNotAController() {
        $this->register->registerNamespace(
            '\FlexibleORMTests\Mock\Controller'
        );
        
        $this->assertFalse(
            $this->register->getClassName('test')
        );
    }
}
