<?php
use ORM\ORM_Model;
// Definitions:
class Car extends ORM_Model {
    // Using the defaults: owner_id and manufacturer_name are the fields
    // that link in the Owner and Manufacturer classes.
}

class Owner extends ORM_Model {
    // Use the defaults
}

class Manufacturer extends ORM_Model {
    // Use the default table, but the primary key is name
    const PRIMARY_KEY = 'name';
}

// -------------
// Usage:
// Fetch the first blue car and include the Owner object
$blueCar = Car::FindByColour( 'blue', 'Owner' );

// Outputs the "name" field from the Owner record
echo "This blue car is owned by ", $blueCar->Owner->name;

// Fetch all red cars and include the Owner and Manufacturer objects
$redCars = Car::FindByColour( 'red', array('Owner', 'Manufacturer') );

// That is all the configuration required to be able to do this:
$myCar        = Car::Find( 1, array('Owner', 'Manufacturer'));
$manufacturer = $myCar->Manufacturer;
$owner        = $myCar->Owner;

