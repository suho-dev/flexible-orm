<?php
namespace ORM\SDB;
/*! @page utilities_SDB AmazonSDB Utilities
 *
 * \section utilities_SDB_intro Introduction
 * Amazon SimpleDB (SDB) is a scalable non-relational simple SQL service. For more
 * information on the SDB service, see http://http://aws.amazon.com/simpledb/
 *
 * This package provides a few SDB utilities that build on the AWS-SDK for PHP
 * (http://http://aws.amazon.com/sdkforphp/). They include a response class that
 * allows you to get SDB items as an array (SDBResponse) and a pair of classes
 * that allow SDB to be used like a PDO prepared statement (SDBStatement). More
 * details of these classes is provided below.
 *
 * There is also a model class that allows models to use AmazonSDB as its datastore.
 * For more information on this see \ref advanced_models_SDB "Advanced Features: AmazonSDB Models".
 *
 * \n\n
 * \section utilities_SDB_response SDBResponse Class
 * By default the AWS SDK returns CFResponse objects, which (among other things)
 * contains an XML response with the returned data. For many uses this is a little
 * cumbersome, we really just want an array or object containing the results.
 *
 * The SDBResponse class provides this abstraction without removing any of the
 * features of CFResponse (see http://docs.amazonwebservices.com/AWSSDKforPHP/latest/index.html#i=CFResponse
 * for documentation). To use it, you must set the response class of the AmazonSDB
 * object.
 *
 * <b>Example</b>
 * \include sdb.sdbresponse.example.php
 * 
 *
 * \n\n
 * \section utilities_SDB_statement SDBStatement Class
 * The SDBStatement adds some additional SQL functionality to SDB and also presents
 * the same basic interface as ORM_PDOStatement (see the DataStatement interface).
 *
 * Together with the SDBFactory class it is used as a replacement for the PDOFactory
 * and ORM_PDOStatement classes for ORMModelSDB.
 *
 * \n\n
 * \subsection utilities_SDB_statement_limitations SQL Limitations
 *
 * Only simple SQL statements can be processed by this class, as AmazonSDB is
 * designed to be a simple, scalable datastore. This class avoids some of these
 * shortcommings, but most still exist.
 *
 * Basically the limitations are:
 * - No table (domain) joins
 * - Backticks aren't supported
 * - Subqueries have limited support
 * - All values must be in single quotes
 *
 *
 * \subsection utilities_SDB_statement_usage Usage Example
 * \include sdb.sdbstatement.example.php
 *
 *
 *
 * \n\n
 * - <b>\ref debug_tutorial "Debug - Debugging Helpers"</b>
 * - <b>\ref configuration "Configuration - Managing Settings"</b>
 * - <b>\ref orm_pdo_tutorial "ORM_PDO Extension - Database Helper"</b>
 * - <b>\ref autoloader_tut "AutoLoader - Managing Packages"</b>
 * - <b>AmazonSDB Utilities</b>
 *
 */