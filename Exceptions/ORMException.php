<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Exceptions;

/**
 * Basic exception for flexible-orm
 * 
 * All other exceptions inherit from this, except where there exists a more
 * descriptive Standard PHP (SPL) exception
 *
 */
class ORMException extends \Exception {
}
?>
