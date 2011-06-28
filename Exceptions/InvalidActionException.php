<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Exceptions;

/**
 * Raised when an Controller action either doesn't exist or is not publicly
 * accessable.
 *
 * @see \ORM\Controller\BaseController
 */
class InvalidActionException extends \BadMethodCallException {
}