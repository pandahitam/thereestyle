<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/paylateremail.php');

$paylaterEmail = new PaylaterEmail();
$orderId = $_POST['id_order'];
$paylaterEmail->send($orderId);