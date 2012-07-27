<?php
/**
 * @file
 * @author jarrod.swift
 */
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace FlexibleORMTests\Mock;

/**
 * Description of Staff
 *
 */
class Staff extends \ORM\ORM_Core {
    public $name;
    public $age;

    private $_privateProperty       = 'private';
    protected $_protectedProperty   = 'protected';


}
?>
