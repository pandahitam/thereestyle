<?php
require(dirname(__FILE__).'/../../../config/config.inc.php');
require(dirname(__FILE__).'/controllers/DetailsController.php');

$controller = new DetailsController();
$controller->run();