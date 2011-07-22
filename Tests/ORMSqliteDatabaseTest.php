<?php
/**
 * Tests for ORM_Model class
 * @file
 * @author jarrod.swift
 * @todo Fix the autoloader
 */
namespace ORM\Tests;
use \ORM\Utilities\Configuration;

require_once 'ORMTest.php';

// Delete existing SQLite file
$dsn = Configuration::sqliteDB('dsn');
$sqlDBLocation = str_replace("sqlite:", "", $dsn);

if (file_exists($sqlDBLocation)) {
    unlink($sqlDBLocation);
}

// Create sqlite database
$db = new \PDO($dsn);
$db->exec('CREATE TABLE cars(id integer primary key asc, brand varchar(255), colour varchar(32));');
$db = null;

/**
 * Test class for ORM_Model using multiple databases
 */
class ORMSqliteDatabaseTest extends ORMDatabaseTypeTest {
    protected $carClass         = '\ORM\Tests\Mock\AlternateCarSqlite';
    protected $databaseConfig   = 'sqliteDB';

}