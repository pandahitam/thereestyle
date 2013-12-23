<?php
// selama tahap development, kondisi ini boleh tidak di gunakan


include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/myshortcart.php');

if ($_SERVER['REMOTE_ADDR'] != '103.10.128.11') {
    header('Location: ' . __PS_BASE_URI__);
}


if (isset($_POST['TRANSIDMERCHANT']) && isset($_POST['RESULT']) && isset($_POST['AMOUNT']))
// if (true)
{
    $order_number = $_POST['TRANSIDMERCHANT'];
    $purchase_amt = $_POST['AMOUNT'];
    $req_result = strtoupper($_POST['RESULT']);
    // $order_number = 'TRS_63';
    // $purchase_amt = 500000;
    // $req_result = 'SUCCESS';

    // testing only, you can remove this thinks
    echo $order_number . "\n";
    echo $purchase_amt . "\n";
    echo $req_result . "\n";


    $db = Db::getInstance();
    $result = $db->getRow('SELECT `msc_id` FROM `ps_myshortcart` WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'" AND status="Verified"');

    if($req_result == 'SUCCESS')
    {
		if($result) // edit by Moh. Machfudh & chandra.my.id
		{ 
		    // $result_update = $db->execute('UPDATE `ps_myshortcart` SET status="'.$req_result.'" WHERE msc_order_id="'.$order_number.'"');
			
			//update order status
			$cartId = substr($order_number, (strpos($order_number, '_') + 1));
			$result = $db->getRow('SELECT `id_order` FROM `'._DB_PREFIX_.'orders` WHERE id_cart="'.$cartId.'"');
			$orderId = $result['id_order'];
			$order = new Order($orderId);
			$order->setCurrentState(2);
			
			echo 'Continue';
		}else{
			$result_update = $db->execute('UPDATE `ps_myshortcart` SET status="FAILED - INJECTION DETECTED" WHERE msc_order_id="'.$order_number.'"');
			echo 'Stop';
		}
	//	$result_update = $db->execute('UPDATE `ps_myshortcart` SET status="RESULT" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
    //    echo 'Continue';
    }
    else
    {
        $result_update = $db->execute('UPDATE `ps_myshortcart` SET status="FAILED" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
        echo 'Stop';
    }

}
else
{
    $result_update = $db->execute('UPDATE `ps_myshortcart` SET status="FAILED" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
    echo 'Stop';  // edit by Moh. Machfudh
}
?>