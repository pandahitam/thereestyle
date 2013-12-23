<?php

// selama tahap development, kondisi ini boleh tidak di gunakan

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/myshortcart.php');

if ($_SERVER['REMOTE_ADDR'] != '103.10.128.11') {
    header('Location: ' . __PS_BASE_URI__);
}

$myshortcart = new myshortcart();

if (isset($_POST['TRANSIDMERCHANT']) && isset($_POST['STOREID']) && isset($_POST['AMOUNT']))
{
    $order_number = $_POST['TRANSIDMERCHANT'];
    $purchase_amt = $_POST['AMOUNT'];
    $storeid = $_POST['STOREID'];

    echo $order_number . "\n";
    echo $purchase_amt . "\n";
    echo $storeid . "\n";

    $db = Db::getInstance(); //edit by Moh. Machfudh
    $result = $db->Execute('SELECT `msc_id` FROM `ps_myshortcart` WHERE msc_order_id="'.$order_number.'" AND status="Requested"');

    if($result)
    {
        $result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="Verified" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
        echo 'Continue';
    }
    else
    {	
	$result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="FAILED - INJECTION DETECTED" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
        echo 'Stop';
    }
}
else
{
	$result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="FAILED - INJECTION DETECTED" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
    echo 'Stop';
}
?>