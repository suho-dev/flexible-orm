<?php
// Fetch a car and delete it
$car = Car::Find( 4 );
$car->delete();

// will still output the details of car[4]
var_dump($car); 

// Try to fetch it again
$car = Car::Find( 4 );

// will now output FALSE, as there is not database record
var_dump($car); 