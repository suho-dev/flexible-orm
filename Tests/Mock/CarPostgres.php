<?php
/**
 * @file
 * @author Jarrod Swift <jarrod.swift@sustainabilityhouse.com.au>
 */
/**
 * Mock object classes for testing
 */
namespace FlexibleORMTests\Mock;
use ORM\ORM_Model;



/**
 * Description of AlternateCarSqlite
 *
 * A simple Model using a separate sqlite database
 */
class CarPostgres extends ORM_Model {
    const DATABASE  = 'postgresDB';
    const TABLE     = 'cars';
}
