<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\Exceptions;
use Suho\FlexibleOrm\Controller\BaseController;

/**
 * Raised when an Controller action either doesn't exist or is not publicly
 * accessable.
 *
 * @see BaseController
 */
class InvalidActionException extends \BadMethodCallException {
}