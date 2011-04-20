<?php
namespace ORM;
/*! @page autoloader_tut Autoloader - Managing Packages
 *
 * \section autoloader_intro Introduction
 * The AutoLoader class simplifies this including of files, especially when
 * using namespaces. The basic rules for the AutoLoader are:
 *
 * - Nested namespaces are treated as subfolders, and are case-sensitive
 * - Classes are stored with the extension '.php'
 * - You can define additional autoloaders using spl_autoload
 *
 * \n
 * \subsection autoloader_packages Packages
 * The AutoLoader class uses the idea of \e packages. That is that the root namespace
 * of a class is the package name. For example, the class <code>ORM\\Utilities\\Configuration</code>
 * is in the package \e ORM whilst <code>Helpdesk\\Models\\Issue</code> is in
 * the \e Helpdesk package. The AutoLoader will then use this package to determine
 * where the class will be.
 *
 * To define the location of packages, simply add a \c [packages] group to the
 * INI file and define the package names and locations. For example, the following
 * would define where the ORM and Helpdesk packages are located.
 * @code
 * [packages]
 * ORM      = "/path/to/ORM"
 * Helpdesk = "/MyApps/helpdesk"
 * @endcode
 *
 * This defines the root folder for all classes within this namespace. If an
 * unknown package is requested or an un-namespaced class is called, then the
 * current PHP include path is searched.
 *
 * With the above INI file, a call to class \c Helpdesk\\Monitor would
 * look for the file \c /MyApps/helpdesk/Monitor.php
 *
 * \n
 * \subsection autoloader_nesting Nested Namespaces
 * Further namespaces after the root are treated as subfolders. This means that
 * in the previous example, \c Helpdesk\\Models\\Issue would look for the file
 * \c /MyApps/helpdesk/Models/Issue.php
 *
 * \n\n
 * \section utilities_nav Utilities Tutorial
 * - <b>\ref debug_tutorial "Debug - Debugging Helpers"</b>
 * - <b>\ref configuration "Configuration - Managing Settings"</b>
 * - <b>\ref orm_pdo_tutorial "ORM_PDO Extension - Database Helper"</b>
 * - <b>AutoLoader - Managing Packages</b>
 * - <b>\ref utilities_SDB "AmazonSDB Utilities"</b>
 */