<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace FlexibleORMTests\Mock;

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
        $this->_useLayout = false;
        $this->id = $this->_request->post->id;
    }
    
    public function alternateView() {
        $this->view();
        $this->_templateName = 'cars/view';
    }
    
    private function create() {
        $this->id = $this->_request->cookies->id;
    }
}
