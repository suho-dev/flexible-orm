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

    public $brand;
    public $colour;
    public $doors;

    private $_privateTest = 'should not be saved';

    public function privateTest( $value = null) {
        if( !is_null($value) ) {
            $this->_privateTest = $value;
        }

        return $this->_privateTest;
    }
}
?>
