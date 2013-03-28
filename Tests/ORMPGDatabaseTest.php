<?php
/**
 * Tests for ORM_Model class
 * @file
 * @author jarrod.swift
 * @todo Fix the autoloader
 */
namespace FlexibleORMTests;

use Suho\FlexibleOrm\Utilities\Configuration;
use PDO;

require_once 'ORMTest.php';

$conf = Configuration::postgresDB();
$db = new PDO( "pgsql:dbname={$conf->name};host=localhost", $conf->user, $conf->pass );
$db->exec('EMPTY TABLE cars IF EXISTS;');
$db = null;


/**
 * Test class for ORM_Model using multiple databases
 */
class ORMPGDatabaseTest extends ORMDatabaseTypeTest {
    protected $carClass         = '\FlexibleORMTests\Mock\CarPostgres';
    protected $databaseConfig   = 'postgresDB';
}