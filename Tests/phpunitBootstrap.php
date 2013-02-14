<?php

require_once __DIR__ . "/../src/vendor/autoload.php";
require_once __DIR__ . '/../src/AutoLoader.php';

$loader = new ORM\AutoLoader();
$loader->register(ORM\AutoLoader::AUTOLOAD_STYLE_FORM);
$loader->setPackageLocations(array('FlexibleORMTests' => __DIR__));
