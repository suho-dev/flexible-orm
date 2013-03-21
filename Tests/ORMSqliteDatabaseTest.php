<?php
/**
 * Tests for ORM_Model class
 * @file
 * @author jarrod.swift
 * @todo Fix the autoloader
 */
namespace FlexibleORMTests;

use ORM\Utilities\Configuration;
use PDO;

/**
 * Test class for ORM_Model using multiple databases
 */
class ORMSqliteDatabaseTest extends ORMDatabaseTypeTest {
    protected $carClass                 = \FlexibleORMTests\Mock\AlternateCarSqlite::CLASS_NAME;
    protected $databaseConfig           = 'sqliteDB';
    private   $temporarySQLite3Location = '/tmp/flexible-orm-unit-tests.sqlite3';

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        $dsn = 'sqlite:'.$this->temporarySQLite3Location;

        Configuration::AddValue($this->databaseConfig, 'dsn', $dsn);

        // Delete existing SQLite file
        if (file_exists($this->temporarySQLite3Location)) {
            unlink($this->temporarySQLite3Location);
        }

        // Create sqlite database
        $db = new PDO($dsn);
        $db->exec('CREATE TABLE cars(id integer primary key asc, brand varchar(255), colour varchar(32));');
        $db = null;

        parent::__construct($name, $data, $dataName);
    }

}