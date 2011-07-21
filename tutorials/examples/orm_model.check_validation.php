<?php
$car            = new Car();
$car->doors     = 3;
$car->colour    = 'red';

if ( $car->save() ) {
    // Car is valid
    
} else {
    // Car is NOT valid
    foreach ( $car->errorMessages() as $property => $message ) { 
        echo "Error with the $property field - $message \n";
    }
}