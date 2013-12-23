<?php
if (!defined('_PS_VERSION_'))
  exit;

class PaylaterEmail extends Module
{
	public function __construct()
	{
		$this->name = 'paylateremail';
		$this->tab = 'administration';
		$this->version = '0.1';
		$this->author = 'Licht';
		$this->ps_versions_compliancy = array('min'=>'1.5', 'max'=>'1.6');
		
		parent::__construct();
		
		$this->displayName = $this->l('Pay Later Email');
		$this->description =  $this->l('Send Payment Information to Customer');
	}
	
	public function install()
	{
		return (parent::install() &&
			$this->registerHook('displayAdminOrder'));
	}
	
	public function uninstall()
	{
		if(parent::install() == false)
			return false;
		return true;
	}
	
	public function hookDisplayAdminOrder()
	{
		global $smarty;
		
		$smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
			'id_order' => $_GET['id_order'],
		));
		
		return $this->display(__FILE__, 'displayForm.tpl');
	}
	
	/**
	 * mengirim email informasi pembayaran ke customer
	 * @param $orderId id dari order
	 */
	public function send($orderId)
	{
		global $cookie;
	
		//get objects
		$order = new Order($orderId);
		$invoice = new Address($order->id_address_invoice);
		$delivery = new Address($order->id_address_delivery);
		$delivery_state = $delivery->id_state ? new State($delivery->id_state) : false;
		$invoice_state = $invoice->id_state ? new State($invoice->id_state) : false;
		$customer = $order->getCustomer();
		$currency = new Currency($order->id_currency);
		$carrier = new Carrier($order->id_carrier);
		$products = $order->getProducts();
		
		//DOKU/MSC
		$msc_payment_link = Tools::getHttpHost(true).__PS_BASE_URI__.'module/myshortcart/payment?id_order='.$orderId;
		// echo $msc_payment_link;
		// die;
		
		//build email
		$subject = 'Payment Information';
		$to = $customer->email;
		
		$products_list = '';
		$virtual_product = true;
		foreach ($products as $key => $product)
		{
			$price = Product::getPriceStatic((int)$product['id_product'], false, ($product['product_attribute_id'] ? (int)$product['product_attribute_id'] : null), 6, null, false, true, $product['product_quantity'], false, (int)$order->id_customer, (int)$order->id_cart, (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
			$price_wt = Product::getPriceStatic((int)$product['id_product'], true, ($product['product_attribute_id'] ? (int)$product['product_attribute_id'] : null), 2, null, false, true, $product['product_quantity'], false, (int)$order->id_customer, (int)$order->id_cart, (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
			// $price = $product['total_price_tax_excl']/1;
			// $price_wt = $product['total_price_tax_incl']/1;

			$customization_quantity = 0;
			$customized_datas = Product::getAllCustomizedDatas((int)$order->id_cart);
			if (isset($customized_datas[$product['id_product']][$product['product_attribute_id']]))
			{
				$customization_text = '';
				foreach ($customized_datas[$product['id_product']][$product['product_attribute_id']][$order->id_address_delivery] as $customization)
				{
					if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD]))
						foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text)
							$customization_text .= $text['name'].': '.$text['value'].'<br />';

					if (isset($customization['datas'][Product::CUSTOMIZE_FILE]))
						$customization_text .= sprintf(Tools::displayError('%d image(s)'), count($customization['datas'][Product::CUSTOMIZE_FILE])).'<br />';
					$customization_text .= '---<br />';
				}
				$customization_text = rtrim($customization_text, '---<br />');

				$customization_quantity = (int)$product['customization_quantity'];
				$products_list .=
				'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
					<td style="padding: 0.6em 0.4em;width: 15%;">'.$product['reference'].'</td>
					<td style="padding: 0.6em 0.4em;width: 30%;"><strong>'.$product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '').' - '.Tools::displayError('Customized').(!empty($customization_text) ? ' - '.$customization_text : '').'</strong></td>
					<td style="padding: 0.6em 0.4em; width: 20%;">'.Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ?  Tools::ps_round($price, 2) : $price_wt, $currency, false).'</td>
					<td style="padding: 0.6em 0.4em; width: 15%;">'.$customization_quantity.'</td>
					<td style="padding: 0.6em 0.4em; width: 20%;">'.Tools::displayPrice($customization_quantity * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $price_wt), $currency, false).'</td>
				</tr>';
			}

			if (!$customization_quantity || (int)$product['product_quantity'] > $customization_quantity)
				// echo PS_TAX_EXC.'<br>';
				// echo Tools::displayPrice(Product::getTaxCalculationMethod((int)$customer->id) == PS_TAX_EXC ? Tools::ps_round($price, 2) : $price_wt, $currency).'<br>';
				$products_list .=
				'<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
					<td style="padding: 0.6em 0.4em;width: 15%;">'.$product['reference'].'</td>
					<td style="padding: 0.6em 0.4em;width: 30%;"><strong>'.$product['product_name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '').'</strong></td>
					<td style="padding: 0.6em 0.4em; width: 20%;">'.Tools::displayPrice(Product::getTaxCalculationMethod((int)$customer->id) == PS_TAX_EXC ? Tools::ps_round($price, 2) : $price_wt, $currency, false).'</td>
					<td style="padding: 0.6em 0.4em; width: 15%;">'.((int)$product['product_quantity'] - $customization_quantity).'</td>
					<td style="padding: 0.6em 0.4em; width: 20%;">'.Tools::displayPrice(((int)$product['product_quantity'] - $customization_quantity) * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $price_wt), $currency, false).'</td>
				</tr>';

			// Check if is not a virutal product for the displaying of shipping
			if (!$product['is_virtual'])
				$virtual_product &= false;

			// var_dump($product);
			// die;
		} // end foreach ($products)
		
		// echo $products_list;
		// die;
		
		$data = array(
			'{firstname}' => $customer->firstname,
			'{lastname}' => $customer->lastname,
			'{email}' => $customer->email,
			'{delivery_block_txt}' => $this->_getFormatedAddress($delivery, "\n"),
			'{invoice_block_txt}' => $this->_getFormatedAddress($invoice, "\n"),
			'{delivery_block_html}' => $this->_getFormatedAddress($delivery, '<br />', array(
				'firstname'	=> '<span style="font-weight:bold;">%s</span>',
				'lastname'	=> '<span style="font-weight:bold;">%s</span>'
			)),
			'{invoice_block_html}' => $this->_getFormatedAddress($invoice, '<br />', array(
					'firstname'	=> '<span style="font-weight:bold;">%s</span>',
					'lastname'	=> '<span style="font-weight:bold;">%s</span>'
			)),
			'{delivery_company}' => $delivery->company,
			'{delivery_firstname}' => $delivery->firstname,
			'{delivery_lastname}' => $delivery->lastname,
			'{delivery_address1}' => $delivery->address1,
			'{delivery_address2}' => $delivery->address2,
			'{delivery_city}' => $delivery->city,
			'{delivery_postal_code}' => $delivery->postcode,
			'{delivery_country}' => $delivery->country,
			'{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
			'{delivery_phone}' => ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
			'{delivery_other}' => $delivery->other,
			'{invoice_company}' => $invoice->company,
			'{invoice_vat_number}' => $invoice->vat_number,
			'{invoice_firstname}' => $invoice->firstname,
			'{invoice_lastname}' => $invoice->lastname,
			'{invoice_address2}' => $invoice->address2,
			'{invoice_address1}' => $invoice->address1,
			'{invoice_city}' => $invoice->city,
			'{invoice_postal_code}' => $invoice->postcode,
			'{invoice_country}' => $invoice->country,
			'{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
			'{invoice_phone}' => ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
			'{invoice_other}' => $invoice->other,
			'{order_name}' => $order->getUniqReference(),
			'{date}' => Tools::displayDate(date('Y-m-d H:i:s'),null , 1),
			'{carrier}' => $carrier->name,
			'{payment}' => Tools::substr($order->payment, 0, 32),
			'{products}' => $products_list,
			'{discounts}' => '', //dummy
			'{total_paid}' => Tools::displayPrice($order->total_paid, $currency, false),
			'{total_products}' => Tools::displayPrice($order->total_paid - $order->total_shipping - $order->total_wrapping + $order->total_discounts, $currency, false),
			'{total_discounts}' => Tools::displayPrice($order->total_discounts, $currency, false),
			'{total_shipping}' => Tools::displayPrice($order->total_shipping, $currency, false),
			'{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $currency, false),
			'{total_tax_paid}' => Tools::displayPrice(($order->total_products_wt - $order->total_products) + ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl), $currency, false),
			'{msc_payment_link}' => $msc_payment_link,
		);
		
		//send email
		$num = $send = Mail::Send(intval($cookie->id_lang), 'pay_later_information', 'Payment Information', $data, $to);
		
		//redirect
		echo 'email terkirim. <a href="javascript:" onclick="history.go(-1)">kembali</a>';
	}
	
	/**
	 * @param Object Address $the_address that needs to be txt formated
	 * @return String the txt formated address block
	 */
	protected function _getFormatedAddress(Address $the_address, $line_sep, $fields_style = array())
	{
		return AddressFormat::generateAddress($the_address, array('avoid' => array()), $line_sep, ' ', $fields_style);
	}
}