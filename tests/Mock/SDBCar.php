<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Mock;
/**
 * Description of SDBCar
 *
 */
class SDBCar extends \ORM\SDB\ORMModelSDB {
    const TABLE = 'cars';
    const FOREIGN_KEY_SDBOWNER = 'owner_id';
    
    public $brand;
    public $colour = 'black';
    public $doors;
    public $owner_id;

    private $_privateTest = 'should not be saved';

    public function privateTest( $value = null) {
        if ( !is_null($value) ) {
            $this->_privateTest = $value;
        }

        return $this->_privateTest;
    }
}
?>
