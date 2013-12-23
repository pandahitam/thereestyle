<?php

/*
    Plugin Name : Prestashop MyShortCart Payment Gateway
    Plugin URI  : http://myshortcart.com
    Description : MyShortCart Payment Gateway for Prestashop 1.5.3
    Version     : 1.1
    Author      : hafizh.hazmi
    Author URI  : http://nprojectmedia.com
*/


if (!defined('_PS_VERSION_'))
    exit;

class myshortcart extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();

    public $mscstoreid;
    public $mschash;
    public $mscprefix;

    public function __construct()
    {
        $this->name = 'myshortcart';
        $this->tab = 'payments_gateways';
        $this->version = '1.1';
        $this->author = 'MyShortCart';

        //$this->ps_versions_compliancy = array('min' => '1.5.2', 'max' => '1.5.3');
        $config = Configuration::getMultiple(array('MSC_STOREID', 'MSC_SHARE_KEY', 'MSC_PREFIX'));

        if (isset($config['MSC_STOREID']))
            $this->mscstoreid = $config['MSC_STOREID'];
        if (isset($config['MSC_SHARE_KEY']))
            $this->mschash = $config['MSC_SHARE_KEY'];
        if (isset($config['MSC_PREFIX']))
            $this->mscprefix = $config['MSC_PREFIX'];

        parent::__construct();

        $this->displayName = $this->l('MyShortCart');
        $this->description = $this->l('Accept payments by MyShortCart.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        if (!isset($this->mscurl) || !isset($this->mscstoreid) || !isset($this->mschash) || !isset($this->mscprefix))
                $this->warning = $this->l('store-id, share key, prefix invoice must be configured in order to use this module correctly.');
    }

    public function install()
    {
        parent::install();
        $this->createMyshortcartTable();
        $this->registerHook('Payment');  // using displayPayment
        $this->registerHook('PaymentReturn'); // using displayPayment

    }

    public function uninstall()
    {
        Configuration::deleteByName('MSC_STOREID');
        Configuration::deleteByName('MSC_SHARE_KEY');
        Configuration::deleteByName('MSC_PREFIX');
        parent::uninstall();
        Db::getInstance()->Execute("DROP TABLE `"._DB_PREFIX_."myshortcart`");
        parent::uninstall();
    }

    function createMyShortCartTable()
    {
        $db = Db::getInstance();
        $query = "CREATE TABLE `"._DB_PREFIX_."myshortcart` (
            `msc_id` int(11) NOT NULL auto_increment,
            `start_time` datetime NOT NULL default '0000-00-00 00:00:00',
            `finish_time` datetime NOT NULL default '0000-00-00 00:00:00',
            `status` varchar(50) NOT NULL default '',
            `amount` int(20) NOT NULL default '0',
            `statuscode` varchar(4) NOT NULL default '',
            `trxdate` datetime NOT NULL default '0000-00-00 00:00:00',
            `paytype` varchar(20) NOT NULL default '',
            `extrainfo` varchar(250) NOT NULL default '',
            `msc_order_id` varchar(125) NOT NULL default '0',
            `session_id` varchar(50) NOT NULL default '',
            `void_status` varchar(24) NOT NULL default '',
            `void_reason` varchar(24) NOT NULL default '',
            `void_date` varchar(24) NOT NULL default '',
            PRIMARY KEY  (`msc_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";

        $db->Execute($query);

    }

    private function _postValidation()
    {
        // configuration
        if (Tools::isSubmit('btnSubmit'))
        {
            if (!Tools::getValue('mscstoreid'))
                $this->_postErrors[] = $this->l('Store-ID is required.');
            elseif (!Tools::getValue('mschash'))
                $this->_postErrors[] = $this->l('Share-key is required.');
            elseif (!Tools::getValue('mscprefix'))
                $this->_postErrors[] = $this->l('Prefix invoice is required.');
        }
    }

    private function _postProcess()
    {
        // configuration
        if (Tools::isSubmit('btnSubmit'))
        {
            Configuration::updateValue('MSC_STOREID', Tools::getValue('mscstoreid'));
            Configuration::updateValue('MSC_SHARE_KEY', Tools::getValue('mschash'));
            Configuration::updateValue('MSC_PREFIX', Tools::getValue('mscprefix'));
        }
        $this->_html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
    }

    private function _displayForm()
    {
        // form configuration
        $this->_html .=
        '<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
             <fieldset>
                <legend><img src="../img/admin/contact.gif" />'.$this->l('Configuration details').'</legend>
                <table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
                    <tr>
                        <td colspan="2">'.$this->l('MyShortCart Module Configuration').'.<br /><br /></td>
                    </tr>
                    <tr>
                        <td width="130" style="vertical-align: top;">'.$this->l('Store-Id').'</td>
                        <td style="padding-bottom:15px;">
                            <input type="text" name="mscstoreid" value="'.Tools::safeOutput(Tools::getValue('MSC_STOREID', Configuration::get('MSC_STOREID'))).'" style="width: 300px;" />
                        </td>
                    </tr>
                    <tr>
                        <td width="130" style="vertical-align: top;">'.$this->l('Shared Key').'</td>
                        <td style="padding-bottom:15px;">
                            <input type="text" name="mschash" value="'.Tools::safeOutput(Tools::getValue('MSC_SHARE_KEY', Configuration::get('MSC_SHARE_KEY'))).'" style="width: 300px;" />
                        </td>
                    </tr>
                    <tr>
                        <td width="130" style="vertical-align: top;">'.$this->l('Prefix Invoice').'</td>
                        <td style="padding-bottom:15px;">
                            <input type="text" name="mscprefix" value="'.Tools::safeOutput(Tools::getValue('MSC_PREFIX', Configuration::get('MSC_PREFIX'))).'" style="width: 300px;" />
                        </td>
                    </tr>
                    <tr>
                        <td width="130" style="vertical-align: top;"></td>
                        <td style="padding-bottom:15px;"><a href=\'https://apps.myshortcart.com/registration/sign-up/\'><strong>klik disini</strong></a> untuk mendapatkan akun MyShortCart.
                        </td>
                    </tr>
                    <tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
                    <tr>
                        <td width="340" style="vertical-align: top;"></td>
                        <td style="padding-bottom:15px;"></td>
                    </tr>
                    <tr>
                        <td width="340" style="vertical-align: top;">'.$this->l('VERIFY URL').'</td>
                        <td style="padding-bottom:15px;">'.Tools::getHttpHost(false, true).__PS_BASE_URI__ .'modules/myshortcart/verify.php</td>
                    </tr>
                    <tr>
                        <td width="340" style="vertical-align: top;">'.$this->l('NOTIFY URL').'</td>
                        <td style="padding-bottom:15px;">'.Tools::getHttpHost(false, true).__PS_BASE_URI__ .'modules/myshortcart/notify.php</td>
                    </tr>
                    <tr>
                        <td width="340" style="vertical-align: top;">'.$this->l('REDIRECT URL').'</td>
                        <td style="padding-bottom:15px;">'.Tools::getHttpHost(false, true).__PS_BASE_URI__ .'modules/myshortcart/redirect.php</td>
                    </tr>
                    <tr>
                        <td width="340" style="vertical-align: top;">'.$this->l('CANCEL URL').'</td>
                        <td style="padding-bottom:15px;">'.Tools::getHttpHost(false, true).__PS_BASE_URI__ .'modules/myshortcart/cancel.php</td>
                    </tr>
                </table>
            </fieldset>
        </form>';
    }

    /*private function _displayMyShortCart()
    {

    }*/

    public function getContent()
    {
        $this->_html = '<h2>'.$this->displayName.'</h2>';

        if (Tools::isSubmit('btnSubmit'))
        {
            $this->_postValidation();
            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors as $err)
                    $this->_html .= '<div class="alert error">'. $err .'</div>';
        }
        else
            $this->_html .= '<br />';

        $this->_displayForm();

        return $this->_html;
    }

    public function execPayment($cart)
    {
        if (!$this->active)
            return;

        $basket='';
        global $cookie,$smarty;

        $myshortcart = new myshortcart();
        $cart = new Cart(intval($cookie->id_cart));
        $address = new Address(intval($cart->id_address_invoice));
        $country = new Country(intval($address->id_country));
        $state = NULL;
        if ($address->id_state)
            $state = new State(intval($address->id_state));
        $customer = new Customer(intval($cart->id_customer));
        $currency_order = new Currency(intval($cart->id_currency));
        $products = $cart->getProducts();
        
        $summarydetail = $cart->getSummaryDetails();
       
        
//        $shipping = new Shipping(intval($cart->id_shipping));
////        $shippings = $cart->getShipping();
//        print $shipping;
        $i = 0;
        $basket = '';
        
        // Edit Moh. Machfudh
        foreach($products as $product)
        {
//            $i = $i + 1;
//            if($i > 1)
//            {
//                $separator = ';';
//            }
//            else
//            {
//                $separator = '';
//            }
            
            $price_wt = number_format($product['price_wt'],2,'.','');
            $total_wt = number_format($product['total_wt'],2,'.','');

            $basket .= $product['name'] . ',' ;
            $basket .= $price_wt . ',' ;
            $basket .= $product['cart_quantity'] . ',';
            $basket .= $total_wt .';' ;
            
        }
        
        if ( $summarydetail['total__discounts'] > 0) { 
            $basket .= 'Total Discount ,';
            $basket .=  number_format($summarydetail['total__discounts'],2,'.','') . ',';
            $basket .=  '1 ,';
            $basket .=  number_format($summarydetail['total__discounts'],2,'.',''). ';';
        }
        
//        if ( $shippings > 0) {   
            $basket .= 'Shipping Cost ,';
            $basket .=  number_format($summarydetail['total_shipping'],2,'.','') . ',';
            $basket .=  '1 ,';
            $basket .=  number_format($summarydetail['total_shipping'],2,'.','');
//        }

        $total = number_format(floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', '')),2,'.','');

        $order = new Order($myshortcart->currentOrder);
        $hash_pass = Configuration::get('MSC_SHARE_KEY'); // fix this variable
        $invoice_id = Configuration::get('MSC_PREFIX') . intval($cart->id);
        $words = sha1($total . $hash_pass . $invoice_id);
        $msc_url = "https://apps.myshortcart.com/payment/request-payment/";

        $myshortcart->writeMyShortCartOrder($invoice_id, $total);

        $smarty->assign(array(
                'basket' => $basket,
                'msc_storeid' => Configuration::get('MSC_STOREID'),
                'msc_url' => $msc_url,
                'msc_pass' => Configuration::get('MSC_PASS'),
                'msc_amount' => $total,
                'msc_invoice' => $invoice_id,
                'msc_words' => $words,
                'c_name' => $address->firstname . ' ' . $address->lastname,
                'c_email' => $customer->email,
                'c_phone' => $address->phone,
                'c_mphone' => $address->phone_mobile,
                'c_address' => $address->address1 . ' ' . $address->address2,
                'c_zipcode' => $address->postcode,
                'url' => Tools::getHttpHost(false, true).__PS_BASE_URI__
        ));

        //return $this->display(__FILE__, 'payment_execution.tpl');
    }


    function writeMyShortCartOrder($id_order, $amount)
    {
        $date_now = date('Y-m-d H:i:s');
        
        $db = Db::getInstance();
        //$check_duplicate = $db->Execute('SELECT `msc_id` FROM `ps_myshortcart` WHERE msc_order_id="'.$id_order.'" AND status="Requested"');
        
        //if ($check_duplicate) {
        //    $update_duplicate = $db->Execute('UPDATE `ps_myshortcart` SET amount="'.$amount.'" WHERE msc_order_id="'.$id_order.'" AND status="Requested"');
        //}
        //else {
            $result = $db->Execute('INSERT INTO `ps_myshortcart` (`msc_order_id`, `start_time`,`status`,`amount`) VALUES ("'.$id_order.'","'.$date_now.'","Requested","'.$amount.'")');
        //}

        return;
    }

    function hookPayment($params)
    {
        if (!$this->active)
            return;

        global $smarty;
        $smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }


    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return;

        global $smarty;

        $state = $params['objOrder']->getCurrentState();

        if ($state == _PS_OS_OUTOFSTOCK_ or $state == _PS_OS_PAYMENT_)
            $smarty->assign(array(
                'total_to_pay'  => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false, false),
                'status'        => 'ok',
                'id_order'      => $params['objOrder']->id
            ));
        else
            $smarty->assign('status', 'failed');

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookPaymentSuccess()
    {
        if (!$this->active)
            return;

        return $this->display(__FILE__, 'success.tpl');
    }

    public function hookPaymentFailed()
    {
        if (!$this->active)
            return;

        return $this->display(__FILE__, 'failed.tpl');
    }
}

?>