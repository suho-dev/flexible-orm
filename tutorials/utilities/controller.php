<?php
/**
 * Package for implementing the controller part of the Model-View-Controller pattern
 * 
 * See the \ref controller_tutorial "Controller Tutorial"
 */
namespace ORM\Controller;
use ORM\Interfaces\Template;

/*! @page controller_tutorial Controller Tutorial
 * 
 * \section ctrl_intro Introduction
 * The Controller package allows flexible-orm to be used as an almost complete 
 * Model-View-Controller framework. It provides the Model and Controller parts
 * plus the groundwork for View components.
 * 
 * The important classes and interfaces for the Controller package are:
 * - BaseController
 *     - Abstract class for controllers to extend.
 * - Request
 *     - Represents the request values GET, POST and cookies.
 * - Template
 *     - Interface for defining classes to handle output (i.e. the View component
 *       of MVC.
 * - SmartyTemplate
 *     - An implementation of the Template interface using the Smarty templating
 *       system.
 * 
 * \n\n
 * \section ctrl_basic Basic Concepts
 * A controller class defines \e actions (as public methods) and assigns resulting
 * values to a \e template (the view layer). All public methods are callable as
 * actions. All public properties are assigned to the template.
 * 
 * The basic process is:
 * -# Get the request variables and create a Request object
 * -# Create and setup a Template object (such as SmartyTemplate)
 * -# Create a new controller object from a class that extends BaseController
 *     - Pass the controller object the Request and Template objects
 * -# Call performAction() on the controller object
 * -# Do something with the output (usually just \c echo it out).
 * 
 * 
 * \subsection ctrl_layout Layouts
 * Usually you would want most templates on a site to contain a lot of identical
 * code (navigation, layout, etc). To avoid having to write this into every template
 * you simply create a template file named 'layout' in your route template folder.
 * This template will be called for every action unless the property \c _useLayout
 * is set to \c FALSE .
 * 
 * The layout will get all the assigned variables from the action, plus a special
 * variable named \c $action_content which is the output of the template.
 * 
 * <b>Basic Controller Class</b>
 * @code
 * class MyController extends BaseController {
 *      public $variable = 'value';
 *      public $id;
 *      private $date;
 * 
 *      // The only action available in MyController
 *      public function view() {
 *          $this->date = time();
 *          $this->id = $this->_request->get->id;
 *      }
 * 
 *      private function create() {
 *          // Not callable directly as it is private
 *      }
 * }
 * @endcode
 * 
 * <b><i>A basic template</i></b>\n
 * For this example, there is no layout template.
 * 
 * \include controller.template.example.tpl
 * 
 * 
 * <b><i>Using the MyController class</i></b>
 * @code
 * // Create the request object
 * // - assume $_GET['action'] == 'view' and $_GET['id'] == 10
 * $request = new Request( $_GET, $_POST, $_COOKIES );
 * 
 * // Create the controller using Smarty templating and all the defaults
 * $controller = new MyController( $request, new SmartyTemplate );
 * 
 * // Echo the output of the template
 * echo $controller->performAction();
 * @endcode
 * 
 * <b><i>Output</i></b>
 * 
 * \include controller.output.example.html
 * 
 * \n\n
 * \section ctrl_options Overriding The Defaults
 * By default each action will attempt to load a template 'controllerName/actionName'.
 * You can change this in the action by setting the \c $_templateName variable
 * to the template you wish to load.
 * 
 * 
 * \n\n
 * \section ctrl_template Implementing Your Own Template Class
 * If you don't want to use Smarty you can simply implement your own class that
 * implements the Template interface.
 * 
 * The following class is an example implementation of a JSON-based template class:
 * 
 * \include controller.json.template.php
 * 
 * \n
 * To use this class:
 * @code
 * $request     = new Request( $_GET, $_POST );
 * $controller  = new MyController( $request, new JsonTemplate );
 * 
 * // Output the JSON encoded data (all public properties of MyController after
 * // the 'test' method is executed.
 * echo $controller->performAction( 'test' );
 * @endcode
 */