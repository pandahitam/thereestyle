<?php
require(dirname(__FILE__).'/../../../config/config.inc.php');
require(dirname(__FILE__).'/controllers/BlogTagsController.php');

$controller = new BlogTagsController();
$controller->run();