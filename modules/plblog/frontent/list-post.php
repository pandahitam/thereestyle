<?php
require(dirname(__FILE__).'/../../../config/config.inc.php');
require(dirname(__FILE__).'/controllers/ListPostController.php');

$controller = new ListPostController();
$controller->run();