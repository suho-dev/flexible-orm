<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Exceptions;
use ORM\Controller\BaseController;

/**
 * Raised when an Controller action either doesn't exist or is not publicly
 * accessable.
 *
 * @see BaseController
 */
class InvalidActionException extends \BadMethodCallException {
}