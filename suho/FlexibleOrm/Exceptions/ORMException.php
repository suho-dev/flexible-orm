<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * Custom flexible-orm exceptions
 */
namespace Suho\FlexibleOrm\Exceptions;

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
