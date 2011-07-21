<?php
/**
 * Tests for ORM_Model class
 * @file
 * @author jarrod.swift
 * @todo Fix the autoloader
 */
namespace ORM\Tests;
use \ORM\Tests\Mock\AlternateCarSqlite, \ORM\Utilities\Configuration;

require_once 'ORMTest.php';

// Delete existing SQLite fle
$dsn = Configuration::alternateCarSqlite('dsn');
$sqlDBLocation = str_replace("sqlite:", "", $dsn);

if (file_exists($sqlDBLocation)) {
    unlink($sqlDBLocation);
}

/**
 * Test class for ORM_Model using multiple databases
 */
class ORMSqliteDatabaseTest extends ORMTest {
    /*
     * Create a sqlite database to connect to.
     */
    protected function setUp() {
        $dsn = Configuration::alternateCarSqlite('dsn');
       
        // Create sqlite database
        $db = new \PDO($dsn);
        $db->exec('CREATE TABLE cars(id integer primary key asc, brand varchar(255), colour varchar(32));');
        $db = null;
    }
    
    /*
     * Make sure we got the correct database
     */
    public function testDatabaseConfig() {
        $this->assertEquals('alternateCarSqlite', AlternateCarSqlite::DatabaseConfigName());
    }

    /*
     * Make sure we got the correct database
     */
    public function testCreateNew() {
        $foo = new AlternateCarSqlite();
        $foo->brand = "BMW";
        $foo->brand = "Orange";   // This should really spit an error, as I doubt BMW make an orange car!!
        $foo->save();
    }
}