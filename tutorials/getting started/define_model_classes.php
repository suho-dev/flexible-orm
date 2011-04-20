<?php
namespace ORM;
/*! @page intro_step2 Define Model Classes
 *
 * \section intro_step2_basic Basic Model Class
 * The simplest model class definition (i.e. when the default conditions are met)
 * is simply extending the ORM_Model class.
 *
 * Assuming you had the tables defined in \ref intro_step1 "Step 1: Define Data Structures"
 * you could define the model for \e Owner as:
 *
 * @code
 * class Owner extends \ORM\ORM_Model {
 *      // any custom code could be added to enhance the model
 * }
 * @endcode
 *
 * This would then allow you to fetch and create database objects in a simple
 * Object-Oriented fashion:
 *
 * @code
 * $jarrod       = new Owner();
 * $jarrod->name = "Jarrod";
 * $jarrod->age  = 31;
 * $jarrod->save();
 *
 * $allTheOldOwners = Owner::FindAllByAge( '70', '>' );
 * $allTheOldOwners->delete(); // bit harsh maybe
 * @endcode
 *
 * For more details on accessing and modifying your data, see \ref intro_step4 "Access your data!"
 *
 * \section intro_step2_custom Custom Table Format
 * When the table format does not match the standard format (i.e. table name plural
 * of model name and primary key "id"), you have to tell the ORM_Model class
 * what those values are.
 *
 * This is done by defining class constants. The two most important constants are:
 * - TABLE<br/>
 *   <i>Defines the table name name</i>
 * - PRIMARY_KEY<br/>
 *   <i>Defines the primary key (default: id)</i>
 *
 * \note You can use both, either or neither of these constants when defining a class.
 * Currently only tables with a single primary key are supported.
 *
 * We can now define the model for our <i>car_manufacturers</i> table. We'll call
 * the model <b>Manufacturer</b> for the purpose of demonstration:
 *
 * @code
 * class Manufacturer extends \ORM\ORM_Model {
 *      const TABLE         = 'car_manufacturers';
 *      const PRIMARY_KEY   = 'name';
 * }
 * @endcode
 *
 * \section intro_step2_advanced Multiple Databases
 * Most of the time a system will only use a single database, but in the case
 * where a system must use multiple databases, you can still use ORM2.0.
 *
 * The connection details for each database must be loaded into the Configuration.
 * The following \ref configuration_inifiles "INI File" contains the default
 * database group (which is used when no database configuration is specified) and
 * an alternative database named \c secondDatabase.
 *
 * @code
 * [database] #The default database
 * name = "test_db"
 * user = "test"
 * pass = "password"
 *
 * [secondDatabase]
 * name = "test2_db"
 * user = "test2"
 * pass = "none"
 * @endcode
 *
 * To create a prepared statement on an alternative database, simply call
 * <code>PDOFactory::Get( $sql, 'secondDatabase' )</code>.
 *
 * To get a model class to use a separate database, simple define the \c DATABASE
 * constant to match the config group name. For example:
 *
 * @code
 * class ExternalJobs extends \ORM\ORM_Model {
 *      const DATABASE = 'secondDatabase';
 * }
 * @endcode
 *
 * You can then access this model from a different database in the same way you
 * would normally.
 *
 * \note If you are using $findWith (eg in ORM_Model::Find()) then all the requested
 *      models must be stored in the same database.
 * 
 *
 * \section intro_step2_advanced More Model Options
 * There are other options for model classes, such as caching and using the AmazonSDB
 * service. For more information see \ref advanced_models "Advanced Features: Special Model Types".
 *
 * \n\n
 * \section intro_step2_nav Getting Started
 *
 * - <b>Step 1: \ref intro_step1 "Define Data Structures"</b>
 * - <b>Step 2: Define Model Classes</b>
 * - <b>Step 3: \ref intro_step3 "Define Foreign Keys"</b>
 * - <b>Step 4: \ref intro_step4 "Access your data!"</b>
 *
 */