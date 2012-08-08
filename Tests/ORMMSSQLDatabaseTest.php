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
class ORMMSSQLDatabaseTest extends ORMDatabaseTypeTest {
    protected $carClass         = '\FlexibleORMTests\Mock\CarMSSql';
    protected $databaseConfig   = 'mssqlDB';
}