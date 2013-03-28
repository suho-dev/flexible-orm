<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * Mock object classes for testing
 */
namespace FlexibleORMTests\Mock;
use Suho\FlexibleOrm\ORM_Model;

/**
 * Description of AlternateCar
 *
 * A simple Model using a separate database
 */
class AlternateCar extends ORM_Model {
    const DATABASE  = 'secondDatabase';
    const TABLE     = 'cars';
}
