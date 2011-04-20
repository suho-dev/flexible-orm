<?php
// Update existing record
$car = Car::Find( 3 );
$car->age++;
$car->save();

// Create new record
$car = new Car();
$car->brand = 'Toyota';
$car->age   = 3;
$car->save();

// Assuming the Car model has an "autoincrement" primary key, it should now be
// populated
echo "New car id: ", $car->id();

// It's also possible to create an item with its primary key set
// Be careful though: if the key exists already it will update the existing record
$car = new Car();
$car->id    = 100;
$car->brand = 'Ferrari';
$car->age   = 30;
$car->save();

// Alternative method of creating a model class:
$car = new Car(array(
    'brand' => 'Toyota',
    'age'   => 3,
));

$car->save();