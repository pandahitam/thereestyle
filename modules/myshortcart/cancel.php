<?php
    include(dirname(__FILE__).'/../../config/config.inc.php');
    include(dirname(__FILE__).'/../../header.php');
    include(dirname(__FILE__).'/myshortcart.php');

    $myshortcart = new myshortcart();

    echo $myshortcart->hookPaymentFailed();

    include(dirname(__FILE__).'/../../footer.php');
?>
