<?php
require(dirname(__FILE__).'/../../../config/config.inc.php');
require(dirname(__FILE__).'/controllers/ViewAllPostController.php');

$controller = new ViewAllPostController();
$controller->run();