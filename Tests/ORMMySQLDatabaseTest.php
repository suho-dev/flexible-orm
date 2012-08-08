<?php
/**
 * Tests for ORM_Model class
 * @file
 * @author jarrod.swift
 * @todo Fix the autoloader
 */
namespace FlexibleORMTests;

require_once 'ORMTest.php';

/**
 * Test class for ORM_Model using multiple databases
 */
class ORMMySQLDatabaseTest extends ORMDatabaseTypeTest {
    protected $carClass         = '\FlexibleORMTests\Mock\Car';
    protected $databaseConfig   = 'database';
}