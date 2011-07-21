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


/**
 * Test class for ORM_Model using multiple databases
 */
class ORMSqliteDatabaseTest extends ORMTest {

    /*
     * Create a sqlite database to connect to.
     */
    protected function setUp() {
        Configuration::Clear();
        Configuration::Load('test.ini');
        $dsn = Configuration::alternateCarSqlite('dsn');
        $sqliteDBFileLocation = str_replace("sqlite:", "", $dsn);

        // Remove the file if it exists
        if (file_exists($sqliteDBFileLocation)) {
            unlink($sqliteDBFileLocation);
        }

        // Create sqlite database
        $db = new \PDO($dsn);
        $db->exec('CREATE TABLE cars(id integer primary key asc, brand varchar(255), colour varchar(32));');
        // This is the documented way of closing a PDO!!! (there is no ->close() function).
        $db = null;
        $this->assertEquals(file_exists($sqliteDBFileLocation), true);
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