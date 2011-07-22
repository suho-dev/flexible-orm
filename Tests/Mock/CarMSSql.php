<?php
/**
 * @file
 * @author Jarrod Swift <jarrod.swift@sustainabilityhouse.com.au>
 */
/**
 * Mock object classes for testing
 */
namespace ORM\Tests\Mock;
use ORM\ORM_Model;

/**
 * Description of CarMSSql
 *
 */
class CarMSSql extends ORM_Model {
    //put your code here
    const DATABASE = 'mssqlDB';
    const TABLE     = 'cars';
}
