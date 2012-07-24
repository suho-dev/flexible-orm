<?php
namespace ORM;
/*! @page advanced_features Advanced Features
 *
 * \section advanced_intro Introduction
 * This section will include some more advanced examples
 * 
 * \section advanced_assoc Associations
 * It is possible to create a model that has two or more of the same object associated with it.
 * 
 * For example, a table named \c companies may have two associated user_ids that refer to a table 'users':
 *  - \c companies.owner_id
 *  - \c companies.business_manager_id
 * 
 * The normal method of \subpage intro_step3 "defining foreign key relationships" can still be used, but it needs a little
 * more work. All that is required is a second Model class definition that points to the same table.
 * 
 * \include orm_model.advanced-foreign-keys.example.php
 * 
 * \section advanced_models Special Model Types
 * \subsection advanced_models_cached Cached Models
 * \subsection advanced_models_SDB AmazonSDB Models
 */