<?php
use \ORM\ORM_Model;

class Elephant extends ORM_Model {
    // Use a table that is not "elephants"
    const TABLE         = 'my_elephants';

    // The primary key is not "id"
    const PRIMARY_KEY   = 'name';
}
