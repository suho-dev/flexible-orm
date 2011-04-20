<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Mock;
use ORM\ORM_Model;
/**
 * Description of Car
 *
 * A simple Model showing a custom foreign key.
 */
class Car extends ORM_Model {
    /**
     * Define that the model Manufacturer is related to this model through
     * the "brand" property.
     */
    const FOREIGN_KEY_MANUFACTURER = 'brand';
}
?>
