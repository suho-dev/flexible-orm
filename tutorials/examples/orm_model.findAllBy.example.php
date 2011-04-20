<?php
// Find all red cars
$cars = Car::FindAllByColour( 'red' );

// Find all cars with more than 3 doors
$cars = Car::FindAllByDoors( 3, '>' );

// Find all cars with more than 3 doors and include the owners
$cars   = Car::FindAllByDoors( 3, '>', '\ORM\Mock\Owner' );

// We could now create an array of owners
$owners = $cars->map( 'Owner' ); 