<?php
namespace ORM;
/*! @page configuration Configuration Class
 *
 * \section configuration_intro Introduction
 * The Configuration class is a simple container for storing and retrieving
 * settings. It is designed to be run using <i>INI Files</i> (see below).
 *
 * The class aims to present a readable Object-Oriented method of retrieving settings.
 * Options can be accessed statically like this:
 * @code
 * $username = Configuration::database()->username;
 * @endcode
 *
 *
 * \n\n
 * \subsection configuration_inifiles INI Files
 * "INI files" are the format the PHP uses for its own settings, so they seem
 * like a reasonable place to keep application specific settings also.
 *
 * \note Never store INI files in the document root! They may be visible to everyone.
 *
 * \n
 * <b>INI File - Basic</b>
 * @code
 * # This is the most basic form of ini file, it has no groups
 * name     = "Jarrod";
 * age      = 31;
 * @endcode
 *
 * The recommended format would be to have \e groups defined in the INI file,
 * allowing you to duplicate names (once for each group, obviously):
 *
 * \n
 * <b>INI File - With Groups</b>
 * @code
 * # This file is more complex, it has groups to make reading and accessing the
 * # settings easier
 * [ITManager]
 * name     = "Jarrod";
 * age      = 31;
 *
 * [CommercialManager]
 * name     = "Seb";
 * age      = 29;
 * @endcode
 *
 * \n\n
 * \subsection configuration_loading Loading Settings
 * Before you can access the settings in your application, you first must load
 * them from an INI File. You can load as many different ini files as you like.
 *
 * \note Loading multiple files will merge the options together, duplicated
 *      option names will be overriden.
 *
 * @code
 * Configuration::Load( 'mysettings.ini' );
 * Configuration::Load( 'moresettings.ini' );
 *
 * // After you've used the settings, you may wish to clear them to prevent
 * // accidental exposure
 * Configuration::Clear();
 * @endcode
 *
 * \n\n
 * \subsection configuration_loading Retrieving Settings
 * Now that you've loaded the configuration files, you can access them. Assuming
 * you have the ini file with groups mentioned above, you could access the options
 * as following:
 *
 * @code
 * $itManagerName   = Configuration::ITManager()->name;
 * $comManagerAge   = Configuration::CommercialManager()->age;
 * @endcode
 * 
 * \n\n
 * \section utilities_nav Utilities Tutorial
 *
 * - <b>\ref debug_tutorial "Debug - Debugging Helpers"</b>
 * - <b>Configuration - Managing Settings</b>
 * - <b>\ref orm_pdo_tutorial "ORM_PDO Extension - Database Helper"</b>
 * - <b>\ref autoloader_tut "AutoLoader - Managing Packages"</b>
 * - <b>\ref utilities_SDB "AmazonSDB Utilities"</b>
 */