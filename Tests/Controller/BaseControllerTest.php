<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * Tests specific to the Controller package
 */
namespace ORM\Controller;

use PHPUnit_Framework_TestCase;
use ORM\Controller\BaseController;
use ORM\Controller\Request;
use FlexibleORMTests\Mock\CarsController;
use ORM\Controller\SmartyTemplate;

/**
 * Description of ControllerTest
 * 
 * @todo Make including Smarty more flexible
 *
 */
class BaseControllerTest extends PHPUnit_Framework_TestCase {
    /**
     * @var CarsController $controller
     */
    protected $controller;
    
    /**
     *
     * @var SmartyTemplate $smarty
     */
    protected $smarty;
    
    /**
     * @var Request $request
     */
    protected $request;
    
    public function setUp() {
        $this->smarty = new SmartyTemplate();
        $this->smarty->template_dir = __DIR__ .'/data/templates';
        
        $this->request    = new Request(array('id' => 20, 'action' => 'view'), array('id' => 30), array( 'name' => 'jarrod', 'id' => 40));
        $this->controller = new CarsController( $this->request, $this->smarty );
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
    
    public function testDefaultTemplate() {
        $output = $this->controller->performAction();
        $this->assertEquals( 20, $this->smarty->getTemplateVars('id') );
        $this->assertEquals( 'view', $this->smarty->getTemplateVars('actionName') );
        $this->assertEquals( "<h1>Layout</h1><p>test output</p>", $output );
    }
    
    public function testNoLayout() {
        $output = $this->controller->performAction('index');
        $this->assertEquals( 'index', $this->smarty->getTemplateVars('actionName') );
        $this->assertEquals( 'cars', $this->smarty->getTemplateVars('controllerName') );
        $this->assertEquals( "<p>test index with no layout</p>", $output );
    }
    
    public  function testSpecifiedTemplate() {
        $output = $this->controller->performAction('alternateView');
        $this->assertEquals( 20, $this->smarty->getTemplateVars('id') );
        $this->assertEquals( "<h1>Layout</h1><p>test output</p>", $output );
    }
}
