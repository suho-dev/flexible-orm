<?php
/**
 * The base namespace for flexible-orm
 * 
 * See \ref getting_started "the getting started guide"
 */
namespace ORM;
/*! @mainpage Introduction to flexible-orm
 * 
 * \section intro_sec Introduction
 *
 * Simple PHP 5.3 based ORM allowing cloud-based models and traditional database
 * models to coexist. Download the source and log bugs at our 
 * <a href="http://code.google.com/p/flexible-orm/">google code site</a>.
 *
 * It aims to simplify the relationship between the database and the application
 * by presenting database rows in an Object-Oriented fashion.
 *
 * The ORM package also includes some general application utilities, like the
 * Configuration class for storing application options.
 *
 * \subsection definitions Definitions
 * The ORM package fulfils the <b>Model</b> part of the <b>Model-View-Controller</b>
 * design pattern. As such, classes that define a single ORM entity (e.g. they
 * extend ORM_Model) are described as <i>models</i>.
 *
 * Each instance of a model refers to exactly one row in a database and each
 * column a single property. For example if you have the database table (<i>users</i>):
 * <table>
 *   <tr><th>id*</th><th>name</th>   <th>email</th></tr>
 *   <tr><td>1</td>  <td>Jarrod</td> <td>jarrod@suho.com.au</td></tr>
 *   <tr><td>2</td>  <td>Steve</td>  <td>steve@suho.com.au</td></tr>
 *   <tr><td>3</td>  <td>Jime</td>   <td>jim@suho.com.au</td></tr>
 * </table>
 *
 * This would be represented by 3 instances of the model <i>User</i>, each of
 * which would have the properties ->id, ->name and ->email.
 *
 * \n\n
 * \section instalation Initial Setup
 *
 * To start using the ORM, you will need to create an INI file that includes database
 * details and the location of your packages. If you only have one database, you
 * only need to define the \c [database] group in the INI file.
 *
 * Then you simply require the \e AutoLoader.php file and load your config.
 *
 * <b>INI File</b>
 * @code
 * [packages]
 * ORM      = "/path/to/ORM"
 * MyApp    = "/path/to/MyApp"
 *
 * [database]
 * name     = "test"
 * user     = "root"
 * pass     = ""
 * @endcode
 *
 * Then all we need to do is include the ORM in your PHP file and load your settings:
 * @code
 * require '/path/to/ORM/AutoLoader.php';
 * \ORM\Utilities\Configuration::Load('test.ini');
 * @endcode
 *
 * \note For more information on how the AutoLoader finds classes, see
 *      \ref autoloader_tut "AutoLoader - Managing Packages".
 *
 * \n\n
 * \section getting_started Getting Started
 *
 * First complete the initial setup, then go on to the following steps:
 *
 * - <b>Step 1: \subpage intro_step1 "Define Data Structures"</b>
 * - <b>Step 2: \subpage intro_step2 "Define Model Classes"</b>
 * - <b>Step 3: \subpage intro_step3 "Define Foreign Keys"</b>
 * - <b>Step 4: \subpage intro_step4 "Access your data!"</b>
 *
 * \section using_the_documentation Using This Documentation
 * This documentation comes in two parts, the \ref getting_started "Getting Started"
 * tutorial and the API docs.
 *
 * - ORM Key API Classes
 *      - ORM_Model is the basis for the entire ORM
 *      - ModelCollection simplifies handling groups of objects
 *      - ORM_Core and ORM_Interface allows you to define an alternative model class
 *      that behaves in the same way as ORM_Model (for instance for a non-database
 *      backed model).
 * - \ref utilities_tutorial "Utilities"
 *      - Configuration in a simple class for centralising option storage
 *      - PDOFactory handles database prepared statements
 *      - ORM_PDOStatement expands on the standard PDOStatement class with some
 *      convenience methods.
 * - Tests
 *      - ORMTest is the parent class of all unit tests and includes information
 *      about running and viewing.
 */