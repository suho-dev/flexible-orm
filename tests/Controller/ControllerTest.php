<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Controller;
use \ORM\Controller\BaseController, \ORM\Controller\Request, \ORM\Tests\Mock\CarsController;

require_once '../ORMTest.php';

/**
 * Description of ControllerTest
 *
 */
class ControllerTest extends \ORM\Tests\ORMTest {
    /**
     * @var CarsController $controller
     */
    protected $controller;
    
    /**
     * @var Request $request
     */
    protected $request;
    
    public function setUp() {
        $this->request    = new Request(array('id' => 20, 'action' => 'view'), array('id' => 30), array( 'name' => 'jarrod', 'id' => 40));
        $this->controller = new CarsController( $this->request );
    }
    
    public function testAction() {
        $this->controller->performAction('index');
        $this->assertEquals( 30, $this->controller->id );
    }
    
    /**
     * @expectedException \ORM\Exceptions\InvalidActionException
     */
    public function testInvalidAction() {
        $this->controller->performAction('invalid');
    }
    
    /**
     * @expectedException \ORM\Exceptions\InvalidActionException
     */
    public function testPrivateAction() {
        $this->controller->performAction('create');
    }
    
    public function testActionFromGet() {
        $this->controller->performAction();
        $this->assertEquals( 20, $this->controller->id );
    }
    
    public function testControllerName() {
        $this->assertEquals( 'cars', CarsController::ControllerName() );
    }
    
    public function testURL() {
        $this->assertEquals('/cars/view/', CarsController::URL('view') );
        $this->assertEquals('/owners/view/', CarsController::URL('view', 'owners') );
        $this->assertEquals('/cars/view/25', CarsController::URL('view', null, 25) );
        $this->assertEquals('/cars/view/25?name=jarrod', CarsController::URL('view', null, array('id' => 25, 'name' => 'jarrod')) );
    }
}

?>
