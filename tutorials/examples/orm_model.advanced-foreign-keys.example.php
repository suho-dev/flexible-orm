<?php
/*
 * Define the base database model
 */
class User extends ORM\ORM_Model {
    /*
     * Force this class (and all child classes) to use the users table 
     */
    const TABLE = 'users';
    
    public $name;
}

/*
 * Extend the User model for the Company Owner relationship 
 */
class Owner extends User {
}

/*
 * Extend the User mode for the Company Business Manager relationship
 */
class BusinessManager extends User {
}

/*
 * Then the definition of the Company model class 
 */
class Company extends ORM\ORM_Model {
    const FOREIGN_KEY_BUSINESSMANAGER   = 'business_manager_id';
    
    public $business_manager_id;
    public $owner_id;
    public $trading_name;
}


// This would allow you to call:
$company = Company::Find(1, array('Owner', 'BusinessManager'));

echo $company->Owner->name;
echo $company->BusinessManager->name;