<?php
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