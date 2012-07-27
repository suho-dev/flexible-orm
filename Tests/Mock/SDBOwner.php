<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace FlexibleORMTests\Mock;
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
