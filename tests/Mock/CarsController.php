<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Mock;

/**
 * Description of CarsController
 *
 */
class CarsController extends \ORM\Controller\BaseController {
    public $id;
    
    public function view() {
        $this->id = $this->_request->get->id();
    }
    
    public function index() {
        $this->id = $this->_request->post->id;
    }
    
    private function create() {
        $this->id = $this->_request->cookies->id;
    }
}

?>
