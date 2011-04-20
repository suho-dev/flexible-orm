<?php
use \ORM\ORM_Model;

/*
 * This class uses all the defaults for ORM_Model
 *
 * - Table will be cars
 * - Primary key is id
 * - It is in the database defined under the database group in the Configuration
 */
class Car extends ORM_Model {

}

// Find a single car
$carTen = Car::Find(10);

// Find the first red car
$redCar = Car::FindByColour( 'red' );

// Find all the blue cars
$blueCars = Car::FindAllByColour( 'blue' );