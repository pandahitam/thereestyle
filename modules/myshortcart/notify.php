<?php
// selama tahap development, kondisi ini boleh tidak di gunakan


include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/myshortcart.php');

if ($_SERVER['REMOTE_ADDR'] != '103.10.128.11') {
    header('Location: ' . __PS_BASE_URI__);
}


if (isset($_POST['TRANSIDMERCHANT']) && isset($_POST['RESULT']) && isset($_POST['AMOUNT']))
{
    $order_number = $_POST['TRANSIDMERCHANT'];
    $purchase_amt = $_POST['AMOUNT'];
    $req_result = strtoupper($_POST['RESULT']);

    // testing only, you can remove this thinks
    echo $order_number . "\n";
    echo $purchase_amt . "\n";
    echo $req_result . "\n";


    $db = Db::getInstance();
    $result = $db->Execute('SELECT `msc_id` FROM `ps_myshortcart` WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'" AND status="Verified"');

    if($req_result == 'SUCCESS')
    {
		if($result) // edit by Moh. Machfudh 
		{ 
		    $result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="'.$req_result.'" WHERE msc_order_id="'.$order_number.'"');
			echo 'Continue';
		}else{
			$result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="FAILED - INJECTION DETECTED" WHERE msc_order_id="'.$order_number.'"');
			echo 'Stop';
		}
	//	$result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="RESULT" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
    //    echo 'Continue';
    }
    else
    {
        $result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="FAILED" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
        echo 'Stop';
    }

}
else
{
    $result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="FAILED" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
    echo 'Stop';  // edit by Moh. Machfudh
}
?>