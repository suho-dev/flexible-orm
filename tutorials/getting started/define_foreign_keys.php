<?php
namespace ORM;
/*! @page intro_step3 Define Foreign Keys
 *
 * \section intro_step3_intro Introduction
 *
 * In the case when two or more database tables are related, the ORM can simplify
 * retrieving related objects. For example, in our tutorial tables defined in
 * \ref intro_step1 "Step 1: Define Data Structures"
 * the owners and cars tables are related (car <i>has one</i> owner).
 *
 * Currently the system only automatically deals with the <i>has one</i> relationship,
 * where the table has a foreign key of the related record in another table (e.g.
 * <i>owner_id</i>). In this example, we would be able to call \c $car->Owner to get
 * an owner object from a Car object (if it was fetched with Owner, see below).
 *
 * @code
 * $car = Car::Find( 2, 'Owner' );
 * echo get_class($car->Owner->name);
 * @endcode
 *
 * \note To find two or more objects together in this way, all models must be in the
 *      same database.
 *
 * \section intro_step3_basic Basic Model Class
 *
 * If the foreign keys are in the format [model_name]_[model_primary_key] then
 * no further configuration is required.
 *
 * In our demonstration tables defined in
 * \ref intro_step1 "Step 1: Define Data Structures"
 * the relationship between Car and Owner meets this standard (i.e. the foreign
 * key field is <i>owner_id</i>. The relationship between Car and Manufacturer
 * however does not, as they are related through the field <i>brand</i> in the cars
 * table. If the <i>brand</i> field was instead named <i>manufacturer_name</i>
 * then no further configuration would be required.
 *
 * \section intro_step3_custom Custom Foreign Key Format
 *
 * When the table format does not match the standard format (i.e. table_name
 * underscore primary_key), you have to tell the ORM_Model class what the key is.
 *
 * This is done by defining class constants. The constant name should be
 * FOREIGN_KEY_[uppercase model name].
 *
 * We can now define the model for our <i>cars</i> table. We'll call
 * the model <b>Car</b>. We only need to specify the foreign key for the Manufacturer
 * class, because the Owner class uses the foreign key "owner_id".
 *
 * @code
 * class Car extends ORM\ORM_Model {
 *      const FOREIGN_KEY_MANUFACTURER = 'brand';
 * }
 * @endcode
 *
 * \section intro_step3_using Using A Model With Foreign Keys
 * Model objects are not automatically fetched with their related models included
 * to improve performance. If you want the foreign model objects to be included,
 * you must use the findWith parameters.
 *
 * For example:
 * @code
 * $car = Car::Find( 2 );
 * echo get_class($car->Owner); // Will be null as it wasn't fetched
 *
 * $car = Car::Find( 2, 'Owner' );
 * echo get_class($car->Owner); // Will echo 'Owner'
 *
 * $car = Car::Find( 2, array('Owner', 'Manufacturer') );
 * echo get_class($car->Manufacturer); // Will echo 'Manufacturer'
 * @endcode
 *
 * If you want to create a foreign key relationship with more than one of the same records, see \ref advanced_assoc "Advanced Foreign Keys"
 * 
 * For more information see ORM_Model::Find() and ORM_Model::FindAll().
 *
 * \section intro_step3_nav Getting Started
 *
 * - <b>Step 1: \ref intro_step1 "Define Data Structures"</b>
 * - <b>Step 2: \ref intro_step2 "Define Model Classes"</b>
 * - <b>Step 3: Define Foreign Keys</b>
 * - <b>Step 4: \ref intro_step4 "Access your data!"</b>
 *
 */