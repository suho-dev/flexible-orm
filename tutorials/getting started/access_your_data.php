<?php
namespace ORM;
/*! @page intro_step4 Access Your Data!
 *
 * Page Index:
 * - \ref intro_step4_find "Finding & Manipulating A Single Object"
 *      - \ref intro_step4_find_one "Find()"
 *      - \ref intro_step4_find_save "Save()"
 *      - \ref intro_step4_find_delete "Delete()"
 * - \ref intro_step4_findall "Finding & Manipulating Many Objects"
 *      - \ref intro_step4_find_all "FindAll()"
 *      - \ref intro_step4_find_allby "FindAllBy()"
 *      - \ref intro_step4_find_all_save "save()"
 * - \ref intro_step4_moreinfo "For More Information"
 *
 * \n\n
 *
 * \section intro_step4_find Finding & Manipulating A Single Object
 * There are a number of ways to find a single object from the database. You can
 * search by id (ORM_Model::Find()), a simple comparison (ORM_Model::FindBy()) or
 * a more complex query (using ORM_Model::Find() with an options array).
 *
 * \n\n
 * \subsection intro_step4_options Options Array
 *
 * - <i>where</i><br >
 *          A string for the \c WHERE statement, optionally with PDO placeholders.
 *          If placeholders are present, the \e values array must also
 *          exist.
 *
 * - <i>values</i><br >
 *          Array of values to be passed to PDOStatement->execute(). If "?" placeholders
 *          have been used, this is simply an array of those paramenters in order.
 *          If the named placeholders have been used (e.g. :colour), then the array
 *          should be an associative array where the keys are the placeholders.
 *
 * - <i>order</i><br >
 *          The SQL \c ORDER \c BY value.
 * 
 * - <i>limit</i><br >
 *          (Only for findAll statements). Limit the number of results to the specified
 *          maximum.
 * 
 * - <i>offset</i><br >
 *          (Only for findAll statements). Offset rows to skip (used with \e limit 
 *          to create pagination.
 *
 * \n\n
 * \subsection intro_step4_find_one Find
 * \copydetails ORM_Model::Find()
 * \n\n
 *
 * \subsection intro_step4_find_save Save
 * \copydetails ORM_Model::save()
 * \n\n
 * 
 * \subsection intro_step4_find_delete Delete
 * \copydetails ORM_Model::delete()
 *
 * \section intro_step4_findall Finding & Manipulating Many Objects
 * The same options exist for finding a number of objects as for finding a single
 * one. All the find function names are named starting with \em FindAll.
 * 
 * @code
 * // Increment the age off all cars by 1
 * $cars = Car::FindAll();
 * $cars->each(function($car){
 *      $car->age++;
 * });
 *
 * $cars->save();
 * @endcode
 * \n\n
 * \subsection intro_step4_find_all FindAll()
 * \copydetails ORM_Model::FindAll()
 * \n\n
 *
 * \subsection intro_step4_find_allby FindAllBy()
 * \copydetails ORM_Model::FindAllBy()
 * \n\n
 *
 * \subsection intro_step4_find_all_save save()
 * \copydetails ModelCollection::save()
 * \n\n
 *
 * \section intro_step4_moreinfo For More Information
 * @see Single objects: ORM_Model::Find(), ORM_Model::FindBy(), ORM_Model::save() \n
 *      Multiple objects: ORM_Model::FindAll(), ORM_Model::FindAllBy(), ModelCollection
 *
 *
 * \section intro_step4_nav Getting Started
 *
 * - <b>Step 1: \ref intro_step1 "Define Data Structures"</b>
 * - <b>Step 2: \ref intro_step2 "Define Model Classes"</b>
 * - <b>Step 3: \ref intro_step3 "Define Foreign Keys"</b>
 * - <b>Step 4: Access your data!</b>
 *
 */