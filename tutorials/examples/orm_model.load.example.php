<?php
// Create a new person with partial details
$person = new Person(array(
    'name' => 'jarrod',
    'id' => 1
));

// This will be null
echo $person->age;

// Load the existing details of Person[1] (note: the primary key value must be set)
$person->load();

// Will output the stored age value for Person with id 1
echo $person->age; 