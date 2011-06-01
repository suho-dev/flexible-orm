<?php
namespace ORM;
/*! @page validation Model Validation
 *
 * \section validation_intro Introduction
 * The ORM contains a basic framework for validating models. Models are validated
 * before the object is saved.
 * 
 * \section validation_rules Rules
 * First you need to set the rules for a valid object. There are 4 things the model 
 * needs for validation:
 *   -# Override the \c valid() method in your model class
 *   -# Check each rule you need
 *   -# Call \c validationError() when a rule fails
 *   -# Return \c TRUE on success or \c FAIL when not valid
 * 
 * \include orm_model.validation.php
 * 
 * \section validation_check Checking and Using Errors
 * Model validation occurs when you attempt to save an object. You can check whether
 * the save was successful (which it won't be if there are errors) by testing the output
 * of save(). The example below shows a very simple error checking process.
 * 
 * \include orm_model.check_validation.php
 * 
 * \note Save may fail for reasons other than validation, such as database configuration
 *       problems.
 */