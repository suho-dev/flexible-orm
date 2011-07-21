<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Mock;
use ORM\ORM_Model;
/**
 * Description of Owner
 *
 * @author jarrod.swift
 */
class SDBOwner extends \ORM\SDB\ORMModelSDB {
    const TABLE = 'owners';

    public $name;
}
?>
