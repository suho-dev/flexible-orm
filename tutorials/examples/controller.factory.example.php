<?php
// Get the request variables and controller name
define( 'DEFAULT_CONTROLLER', 'namespace' );
$request        = new Request( $_GET, $_POST, $_COOKIES );
$controllerName = $request->get->request ?: DEFAULT_CONTROLLER;

// Register the location of your controllers
$register       = new ControllerRegister;
$register->registerNamespace( '\MyProject\Controllers' );

// Create the factory
$factory        = new ControllerFactory( $register );

/*
 * Get the controller from the factory
 * 
 * Also passes arguments to the controller constructor.
 */
$controller     = $factory->get($controllerName, array(
    $request, new SmartyTemplate
));

echo $controller->performAction();
