<?php
namespace ORM\Controller;

require_once '../ORMTest.php';
/**
 * Description of ControllerRegisterTest
 *
 * @author jarrodswift
 */
class ControllerRegisterTest extends \ORM\Tests\ORMTest {
    /**
     * @var ControllerRegister $register
     */
    protected $register;
    
    public function setUp() {
        $this->register = new ControllerRegister;
    }
    
    public function testRegisterNamespace() {
        $registeredNamespaces = $this->register->registerNamespace(
            '\ORM\Tests\Mock\Controller'
        );
        
        $this->assertEquals(array('\ORM\Tests\Mock\Controller'), $registeredNamespaces);
        
        $registeredNamespaces = $this->register->registerNamespace(
            '\ORM\Tests\Mock\AlternateController'
        );
        
        $this->assertEquals(array(
            '\ORM\Tests\Mock\Controller',
            '\ORM\Tests\Mock\AlternateController'
            ), $registeredNamespaces);
    }
    
    public function testRegisterController() {
        $registered = $this->register->registerController(
            'special', '\ORM\Tests\Mock\Controller\Cars'
        );
        
        $this->assertEquals(array('special'=>'\ORM\Tests\Mock\Controller\Cars'), $registered);
    }
    
    public function testGetClassName() {
        $this->register->registerController(
            'special', '\ORM\Tests\Mock\Controller\Cars'
        );
        
        $this->assertEquals(
            '\ORM\Tests\Mock\Controller\Cars',
            $this->register->getClassName('special')
        );
    }
    
    public function testGetNamespacedName() {
        $this->register->registerNamespace(
            '\ORM\Tests\Mock\Controller'
        );
        
        $this->assertEquals(
            '\ORM\Tests\Mock\Controller\Cars',
            $this->register->getClassName('cars')
        );
    }
    
    public function testGetUnknown() {
        $this->register->registerNamespace(
            '\ORM\Tests\Mock\Controller'
        );
        
        $this->assertFalse(
            $this->register->getClassName('owners')
        );
    }
    
    public function testGetNotAController() {
        $this->register->registerNamespace(
            '\ORM\Tests\Mock\Controller'
        );
        
        $this->assertFalse(
            $this->register->getClassName('test')
        );
    }
}
