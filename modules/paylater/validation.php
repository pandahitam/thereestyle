<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/paylater.php');

$paylater = new Paylater();

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$paylater->active)
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
$authorized = false;
foreach (Module::getPaymentModules() as $module)
	if ($module['name'] == 'paylater')
	{
		$authorized = true;
		break;
	}
if (!$authorized)
	die(Tools::displayError('This payment method is not available.'));
	
$customer = new Customer((int)$cart->id_customer);

if (!Validate::isLoadedObject($customer))
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

$currency = new Currency($cookie->id_currency);
$total = (float)$cart->getOrderTotal(true, Cart::BOTH);

$mailVars = array();

$paylater->validateOrder($cart->id, 15, $total, $paylater->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
// $paylater->sendEmailConfirmation();
echo '1';
// $order = new Order($paylater->currentOrder);
echo '2';
Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$paylater->id.'&id_order='.$paylater->currentOrder.'&key='.$customer->secure_key);
echo '3';