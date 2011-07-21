<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Controller;
use \ORM\Controller\Request;

set_include_path(get_include_path(). PATH_SEPARATOR . __DIR__.'/..');
require_once 'ORMTest.php';


/**
 * Description of RequestTest
 *
 */
class RequestTest extends \ORM\Tests\ORMTest {
    /**
     * @var Request $object
     */
    protected $object;
    
    private $get  = array( 'id' => 123, 'name' => 'jarrod' );
    private $post = array( 'name' => 'steve', 'age' => '1notvalid');
    
    public function setUp() {
        $this->object = new Request( $this->get, $this->post );
    }
    
    public function testGetAsProperty() {
        $this->assertEquals(
            $this->get['id'], $this->object->get->id
        );
    }
    
    public function testGetAsMethod() {
        $this->assertEquals(
            $this->get['name'], $this->object->get->name()
        );
    }
    
    public function testPostAsProperty() {
        $this->assertEquals(
            $this->post['name'], $this->object->post->name
        );
    }
    
    public function testPostAsMethod() {
        $this->assertEquals(
            $this->post['name'], $this->object->post->name()
        );
    }
    
    public function testRequestGet() {
        $this->assertEquals(
            $this->post['name'], $this->object->name()
        );
        $this->assertEquals(
            $this->post['name'], $this->object->name
        );
    }
    
    public function testRules() {
        $this->assertNull( $this->object->post->age(null, 'ctype_digit') );
        $this->assertEquals( 0, $this->object->post->age(0, 'ctype_digit') );
        $this->assertEquals( $this->post['age'], $this->object->post->age() );
        $this->assertEquals( 0, $this->object->age(0, 'ctype_digit') );
        
        $this->assertEquals( $this->post['age'], $this->object->post->age(10), '/^\d/' );
    }
    
    public function testDefault() {
        $this->assertEquals(
            25, $this->object->get->idontexist(25)
        );
        
        $this->assertEquals(
            $this->get['id'], $this->object->get->id(25)
        );
    }
}