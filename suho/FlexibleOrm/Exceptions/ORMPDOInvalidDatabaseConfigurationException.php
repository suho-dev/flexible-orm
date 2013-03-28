<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\Exceptions;

/**
 * Thrown when attempt to connect to a database with invalid connection details
 * or none at all.
 *
 */
class ORMPDOInvalidDatabaseConfigurationException extends ORMPDOException {
}