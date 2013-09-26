<?php
if (!defined('_PS_VERSION_'))
  exit;
  
include_once (PS_ADMIN_DIR . '/../../classes/Mail.php');

class PayLater extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'paylater';
		$this->tab = 'payments_gateways';
		$this->version = '0.1';
		$this->author = 'Chandra S.';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min'=>'1.5', 'max'=>'1.6');
		
		parent::__construct();
		
		$this->displayName = $this->l('Pay Later');		
		$this->description = $this->l('Pay later after stock confirmation.');		
	}
	
	public function install()
	{
		// if(Shop::isFeatureActive())
			// Shop::setContext(Shop::CONTEXT_ALL);
			
		return parent::install() &&
			$this->registerHook('displayPayment') &&
			$this->registerHook('paymentReturn');
	}
	
	public function uninstall()
	{
		return parent::uninstall();
	}
	
	public function hookDisplayPayment($params)
	{
		if(!$this->active)
			return;
			
		global $smarty;
		
		$smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		
		return $this->display(__FILE__, 'payment.tpl');
	}
	
	public function execPayment($cart)
	{
		if (!$this->active)
			return ;

		global $cookie, $smarty;

		$smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->getCurrency((int)$cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		return $this->display(__FILE__, 'payment_execution.tpl');
	}
	
	/**
	 * mengirim email ke customer setelah customer submit order
	 */
	/* public function sendEmailConfirmation()
	{
		echo 'send email';
		$configuration = Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_MAIL_METHOD', 'PS_MAIL_SERVER', 'PS_MAIL_USER', 'PS_MAIL_PASSWD', 'PS_SHOP_NAME'));
		var_dump($configuration);
		$num = Mail::Send('id', 'template', 'subject', '', explode(self::__MA_MAIL_DELIMITOR__, 'chandra@licht-soft.com'), NULL, $configuration['PS_SHOP_EMAIL'], $configuration['PS_SHOP_NAME'], NULL, NULL, dirname(__FILE__).'/mails/');
		printf("Sent %d messages\n", $num);
		die;
	
		if (!$this->_merchant_order OR empty($this->_merchant_mails))
			return;

		Getting differents vars
        $id_lang = (( ! isset($cookie)) OR ( ! is_object($cookie)))
                 ? intval(Configuration::get('PS_LANG_DEFAULT'))
                 : intval($cookie->id_lang);
	 	$currency = $params['currency'];
		$configuration = Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_MAIL_METHOD', 'PS_MAIL_SERVER', 'PS_MAIL_USER', 'PS_MAIL_PASSWD', 'PS_SHOP_NAME'));
		$order = $params['order'];
		$customer = $params['customer'];
		$delivery = new Address(intval($order->id_address_delivery));
		$invoice = new Address(intval($order->id_address_invoice));
		$order_date_text = Tools::displayDate($order->date_add, intval($id_lang));
		$order_date_text = Tools::displayDate($order->date_add, intval($id_lang), 1);
		$carrier = new Carrier(intval($order->id_carrier));
		$message = $order->getFirstMessage();
		if (!$message OR empty($message))
			$message = $this->l('No message');

		$itemsTable = '';
		foreach ($params['cart']->getProducts() AS $key => $product)
		{
			$unit_price = Product::getPriceStatic($product['id_product'], (bool)(Product::getTaxCalculationMethod() == PS_TAX_INC), $product['id_product_attribute'], 2, NULL, false, true, $product['cart_quantity']);
			$price = Product::getPriceStatic($product['id_product'], (bool)(Product::getTaxCalculationMethod() == PS_TAX_INC), $product['id_product_attribute'], 6, NULL, false, true, $product['cart_quantity']);
			$itemsTable .=
				'<tr style="background-color:'.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
					<td style="padding:0.6em 0.4em;">'.$product['reference'].'</td>
					<td style="padding:0.6em 0.4em;"><strong>'.$product['name'].(isset($product['attributes_small']) ? ' '.$product['attributes_small'] : '').'</strong></td>
					<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice($unit_price, $currency, false, false).'</td>
					<td style="padding:0.6em 0.4em; text-align:center;">'.intval($product['cart_quantity']).'</td>
					<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice(($price * $product['cart_quantity']), $currency, false, false).'</td>
				</tr>';
		}
		foreach ($params['cart']->getDiscounts() AS $discount)
		{
			$itemsTable .=
			'<tr style="background-color:#EBECEE;">
					<td colspan="4" style="padding:0.6em 0.4em; text-align:right;">'.$this->l('Voucher code:').' '.$discount['name'].'</td>
					<td style="padding:0.6em 0.4em; text-align:right;">-'.Tools::displayPrice($discount['value_real'], $currency, false, false).'</td>
			</tr>';
		}
		if ($delivery->id_state)
			$delivery_state = new State(intval($delivery->id_state));
		if ($invoice->id_state)
			$invoice_state = new State(intval($invoice->id_state));

		Filling-in vars for email
		$template = 'new_order';
		$subject = $this->l('New order');
		$templateVars = array(
			'{firstname}' => $customer->firstname,
			'{lastname}' => $customer->lastname,
			'{email}' => $customer->email,
			'{delivery_company}' => $delivery->company,
			'{delivery_firstname}' => $delivery->firstname,
			'{delivery_lastname}' => $delivery->lastname,
			'{delivery_address1}' => $delivery->address1,
			'{delivery_address2}' => $delivery->address2,
			'{delivery_city}' => $delivery->city,
			'{delivery_postal_code}' => $delivery->postcode,
			'{delivery_country}' => $delivery->country,
			'{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
			'{delivery_phone}' => $delivery->phone,
			'{delivery_phone_mobile}' => $delivery->phone_mobile,
			'{delivery_other}' => $delivery->other,
			'{invoice_company}' => $invoice->company,
			'{invoice_firstname}' => $invoice->firstname,
			'{invoice_lastname}' => $invoice->lastname,
			'{invoice_address2}' => $invoice->address2,
			'{invoice_address1}' => $invoice->address1,
			'{invoice_city}' => $invoice->city,
			'{invoice_postal_code}' => $invoice->postcode,
			'{invoice_country}' => $invoice->country,
			'{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
			'{invoice_phone}' => $invoice->phone,
            '{invoice_phone_mobile}' => $invoice->phone_mobile,
			'{invoice_other}' => $invoice->other,
			'{order_name}' => sprintf("%06d", $order->id),
			'{shop_name}' => Configuration::get('PS_SHOP_NAME'),
			'{date}' => $order_date_text,
			'{carrier}' => (($carrier->name == '0') ? Configuration::get('PS_SHOP_NAME') : $carrier->name),
			'{payment}' => $order->payment,
			'{items}' => $itemsTable,
			'{total_paid}' => Tools::displayPrice($order->total_paid, $currency),
			'{total_products}' => Tools::displayPrice($order->getTotalProductsWithTaxes(), $currency),
			a minus ('-') is displayed before the discounts, as they are positive
            '{total_discounts}' => '-'.Tools::displayPrice($order->total_discounts, $currency),
			'{total_shipping}' => Tools::displayPrice($order->total_shipping, $currency),
			'{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $currency),
			'{currency}' => $currency->sign,
			'{message}' => $message,
		);
		$iso = Language::getIsoById($id_lang);
		if (file_exists(dirname(__FILE__).'/mails/'.$iso.'/'.$template.'.txt') AND file_exists(dirname(__FILE__).'/mails/'.$iso.'/'.$template.'.html'))
			Mail::Send($id_lang, $template, $subject, $templateVars, explode(self::__MA_MAIL_DELIMITOR__, $this->_merchant_mails), NULL, $configuration['PS_SHOP_EMAIL'], $configuration['PS_SHOP_NAME'], NULL, NULL, dirname(__FILE__).'/mails/');
	} */
}