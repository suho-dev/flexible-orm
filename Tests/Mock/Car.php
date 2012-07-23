<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Mock;
use ORM\ORM_Model;
/**
 * Description of Car
 *
 * A simple Model showing a custom foreign key.
 */
class Car extends ORM_Model {
    private $_testValue = 'initial';
    protected static $_fieldAliases = array('name' => 'model');
    
    public $id;
    public $brand;
    public $colour;
    public $doors;
    public $owner_id;
    public $model;
    public $age;
    public $type;
    
    private $_findValue;
    
    /**
     * Define that the model Manufacturer is related to this model through
     * the "brand" property.
     */
    const FOREIGN_KEY_MANUFACTURER = 'brand';

    /**
     * Test the afterGet hook (set the test value to the brand)
     */
    public function afterGet() {
        $this->_testValue = $this->brand;
    }
    
    public function afterFind() {
        $this->_findValue = $this->id;
    }
    
    public function testValue() {
        return $this->_testValue;
    }
    
    public function findValue() {
        return $this->_findValue;
    }
}
