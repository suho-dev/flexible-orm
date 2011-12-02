<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller;
use ORM\Exceptions\ControllerDoesNotExistException;
use \ArrayObject;

/**
 * Description of ControllerRegistry
 *
 * @author jarrodswift
 */
class ControllerRegistry extends ArrayObject {
    
    public function addNamespace( $namespace, $prefix = null ) {
        $this[$prefix] = $namespace;
    }
}
