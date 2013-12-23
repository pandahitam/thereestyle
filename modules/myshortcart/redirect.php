<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/myshortcart.php');
require_once(dirname(__FILE__).'/../../classes/order/Order.php');
require_once(dirname(__FILE__).'/../../classes/order/OrderHistory.php');

//if ($_SERVER['REMOTE_ADDR'] != '103.10.128.11'){
//    header('Location: ' . __PS_BASE_URI__);
//}

$myshortcart = new myshortcart();

if (isset($_POST['TRANSIDMERCHANT']) && isset($_POST['STATUSCODE']) && isset($_POST['RESULT']) && isset($_POST['AMOUNT']) && isset($_POST['PTYPE']) && isset($_POST['TRANSDATE']))
{
    $order_number = $_POST['TRANSIDMERCHANT'];
    $purchase_amt = $_POST['AMOUNT'];
    $req_result = strtoupper($_POST['RESULT']);
    $statuscode = $_POST['STATUSCODE'];
    $transdate = $_POST['TRANSDATE'];
    $paytype = $_POST['PTYPE'];

    $date_now = date('Y-m-d H:i:s');

    $db = Db::getInstance(); // Edit by Moh. Machfudh
    $result = $db->Execute('SELECT `msc_id` FROM `ps_myshortcart` WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'" AND status="RESULT"');

    if($statuscode=='00')
    {
		if($result){
        $result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="'.$req_result.'", finish_time="'.$date_now.'", trxdate="'.$transdate.'", statuscode="'.$statuscode.'", paytype="'.$paytype.'" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');

        echo 'Continue';
        echo $myshortcart->hookPaymentSuccess();
		}
    }
    else
    {
        $result_update = $db->Execute('UPDATE `ps_myshortcart` SET status="'.$req_result.'", finish_time="'.$date_now.'", statuscode="'.$statuscode.'", trxdate="'.$transdate.'", paytype="'.$ptype.'" WHERE msc_order_id="'.$order_number.'" AND amount="'.$purchase_amt.'"');
        //$extra_result = $db->Execute('INSERT INTO `ps_myshortcart` (`statuscode`, `trxdate`,`paytype`) VALUES ("'.$statuscode.'","'.$transdate.'","'.$ptype.'")');

        echo 'Stop';
        echo $myshortcart->hookPaymentFailed();
    }

}
else {
    //$extra_result = $db->Execute('INSERT INTO `ps_myshortcart` (`statuscode`, `trxdate`,`paytype`) VALUES ("'.$statuscode.'","'.$transdate.'","'.$ptype.'")');

    echo 'Stop';
    echo $myshortcart->hookPaymentFailed();
}

include(dirname(__FILE__).'/../../footer.php');
?>