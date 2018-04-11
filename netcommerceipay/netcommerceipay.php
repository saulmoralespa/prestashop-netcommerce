<?php
if (!defined('_PS_VERSION_')) exit;
include_once(_PS_MODULE_DIR_ . 'netcommerceipay/lib/IpayOrderState.php');
class NetcommerceIpay extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();
    public $merchantId;
    public $sha_key;
    public $isTest;

    public function __construct()
    {
        $this->name = 'netcommerceipay';
        $this->displayName = 'Netcommerce Ipay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Saul Morales Pacheco';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.6.0', 'max' => '1.7');

        $config = Configuration::getMultiple(array('MERCHANTID', 'SHAKEY','ISTEST'));
        if (isset($config['MERCHANTID']))
            $this->merchantId = trim($config['MERCHANTID']);
        if (isset($config['SHAKEY']))
            $this->sha_key = trim($config['SHAKEY']);
        if (isset($config['ISTEST']))
            $this->isTest = $config['ISTEST'];

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->is_eu_compatible = 1;

        parent::__construct();

        $this->displayName = $this->l('Netcommerce Ipay');
        $this->description = $this->l('Accepts payments by Netcommerce Ipay');

        $this->confirm_uninstall = $this->l('Are you sure you want to uninstall? You will lose all your settings!');
        if (!isset($this->merchantId)  OR !isset($this->sha_key))
            $this->warning = $this->l('SHAKEY y MERCHANTID, deben estar configurados para utilizar este módulo correctamente');
        if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
            $this->warning = $this->l('No currency set for this module');

    }

    /**
     * @return bool
     */
    public function install()
    {
        IpayOrderState::setup();
        return (
            function_exists('curl_version') &&
            parent::install() &&
            in_array('curl', get_loaded_extensions()) &&
            $this->createHooks() &&
            Configuration::updateValue('MERCHANTID', '')  &&
            Configuration::updateValue('SHAKEY', '')
        );
    }


    /**
     * @return bool
     */
    private function createHooks()
    {
        $registerStatus =  $this->registerHook('paymentReturn') && $this->registerHook('updateOrderStatus');
        if (version_compare(_PS_VERSION_, '1.7.0.0 ', '<')) {
            $registerStatus &= $this->registerHook('payment');
        } else {
            $registerStatus &= $this->registerHook('paymentOptions');
        }

        return $registerStatus;
    }

    function uninstall() {
        IpayOrderState::remove();
        if (!Configuration::deleteByName('MERCHANTID') OR !Configuration::deleteByName('SHAKEY') OR !Configuration::deleteByName('ISTEST') OR !parent::uninstall())
            return false;
        return true;
    }

    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('merchantId'))
                $this->_postErrors[] = $this->l('merchantId Campo Requerido.');
            if (!Tools::getValue('sha_key'))
                $this->_postErrors[] = $this->l('sha_key Campo Requerido.');
        }
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('MERCHANTID', Tools::getValue('merchantId'));
            Configuration::updateValue('SHAKEY', Tools::getValue('sha_key'));
            Configuration::updateValue('ISTEST', Tools::getValue('isTest'));
            $this->_html.= '<div class="bootstrap"><div class="alert alert-success">'.$this->l('Cambios Aplicados Exitosamente') . '</div></div>';
        }
    }

    private function _displayForm()
    {

        global $cookie;

        $states = IpayOrderState::getOrderStates();
        $id_os_initial = Configuration::get('NETCOMMERCEIPAY_ORDERSTATE_WAITING');

        $this->_html.='<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post" class="half_form">

        <fieldset style="width: 90%; overflow: auto;display:none;">
        <div id="advanced" >
          <div style="float: left;padding:10px;">
            <table cellpadding="0" cellspacing="0" class="table">
            <thead>
              <tr>
                <th style="width: 200px;font-weight: bold;"><p style="display:inline;color:red">Advanced</p> Order States</th>
                <th>Initial State</th>
                <th>Delete On</th>
              </tr>
            </thead>
            <tbody>';

        foreach ($states as $item => $state) {
            $checked = "";
            $checkedorder = "";
            if ($state['id_order_state'] == $id_os_initial) {
                $checked = 'checked=checked';
            }

            if ($state['id_order_state']) {
                $checkedorder = 'checked=checked';
            }

            $this->_html.='.<tr style="background-color: ' . $state['color'] . ';">
            <td>' . $this->l($state['name']) . '</td>
            <td style="text-align:center"><input type="radio" name="id_os_initial" ' . $checked . ' value="' . $state['id_order_state'] . '"/></td>
            <td style="text-align:center"><input type="checkbox" name="id_os_deleteon[]" value="' . $state['id_order_state'] . '" ' . $checkedorder . ' /> </td>
            </tr>';
        }

        if(Tools::getValue('isTest', $this->isTest) == "test") {
            $checked1 = "selected";
            $checked2 = "";
        } else if(Tools::getValue('isTest', $this->isTest) == "real") {
            $checked1 ="";
            $checked2 = "selected";
        }else{
            $checked1 ="selected";
            $checked2 = "";
        }

        $this->_html.='</tbody>
        								</table>
        							</div>
        						</div>
        					</fieldset>
        					<fieldset>
        				<legend>'.utf8_encode("Configuration netcommerce iPAY").'</legend>

                <img src="../modules/netcommerceipay/logo.png"/>

                <table border="0" width="600" cellpadding="0" cellspacing="0" id="form">
        					<tr><td colspan="2">Please specify su SHA/signature key, Merchant Number supplied by NetCommerce.<br /><br /></td></tr>
                  <tr><td width="250" align="justify" style="padding-right:20px;"><b>SHA/signature key:</b><br></td><td><input type="text" name="sha_key" value="' . Tools::htmlentitiesUTF8(Tools::getValue('sha_key', $this->sha_key)) . '" style="width: 300px;" /></td></tr>
                  <tr><td width="250" >&nbsp;&nbsp;</td></tr>
                    <tr><td width="250" >&nbsp;&nbsp;</td></tr>
                            <tr><td width="250"  align="justify" style="padding-right:20px;"><b>Merchant Number</b><br></td><td><input type="text" name="merchantId" value="' . Tools::htmlentitiesUTF8(Tools::getValue('merchantId', $this->merchantId)) . '" style="width: 300px;" /></td></tr>
        				    <tr><td width="250" >&nbsp;&nbsp;</td></tr>
                            <tr><td width="250" ><b>Test mode</b><br></td>
                            <td><select name="isTest" >
                                    <option value="test" '. $checked1.'>YES</option>
                                    <option value="real" '. $checked2.'>NO</option>
                                </select>
                            </td>
                            </tr>

        				</table>
        			</fieldset>
        	<div style="clear: both;"></div>
        	<br/>
        	<center>
        		<input type="submit" name="btnSubmit" value="' . $this->l('Save Changes') . '" class="button" />
        	</center>
        	<hr />
        </form>';

    }
    public function getContent()
    {
        $this->_html = '<h2>' . $this->displayName . '</h2>';

        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
                }
            }
        } else {
            $this->_html .= '<br/>';
        }

        $this->_displayForm();
        return $this->_html;
    }

    function PaymentStatus($state,$idorder)
    {
        $this->_Acentarpago($state,$idorder);
    }

    private function _Acentarpago($response,$idorder)
    {
         if ($response == 'DECLINED')
            $state = 'NETCOMMERCEIPAY_OS_REJECTED';
         if ($response == 'APPROVED')
            $state = 'PS_OS_PAYMENT';

        $order = new Order((int)Order::getOrderByCartId((int)$idorder));
        $current_state = $order->current_state;

        if ($current_state != Configuration::get('PS_OS_PAYMENT'))
        {
            $history = new OrderHistory();
            $history->id_order = (int)$order->id;
            $history->date_add = date("Y-m-d H:i:s");
            $history->changeIdOrderState((int)Configuration::get($state), (int)$order->id);
            $history->addWithemail(false);
        }
        if ($state != 'PS_OS_PAYMENT')
        {
            foreach ($order->getProductsDetail() as $product)
                StockAvailable::updateQuantity($product['product_id'], $product['product_attribute_id'], + (int)$product['product_quantity'], $order->id_shop);
        }

    }

    function execPayment($cart) {

        if (!$this->active)
            return;
        if (!$this->checkCurrency($cart))
            return;

        global $smarty;

        $smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(true, 3),
            'this_path' => $this->_path
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }
    function hookPayment($params) {
        if (!$this->active) return;
        if (!$this->checkCurrency($params['cart'])) return;
        $this->smarty->assign(array(
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active)
            return;

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = [
            $this->getShowPayment(),
        ];

        return $payment_options;
    }

    public function getShowPayment()
    {
        $modalOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $modalOption->setCallToActionText($this->l(''))
                    ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                    ->setAdditionalInformation($this->context->smarty->fetch('module:netcommerceipay/views/templates/front/payment_onpage.tpl'))
                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/logo.png'));

        return $modalOption;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) return;

        global $smarty, $cart;

        $addressdelivery = new Address(intval($cart->id_address_delivery));
        if (version_compare(_PS_VERSION_, '1.7.0.0 ', '<')){
            $order = $params['objOrder'];
            $total = $params['total_to_pay'];
        }else{
            $order = $params['order'];
            $total = $params['order']->getOrdersTotalPaid();
        }
        $id_order = $_GET['id_order'];
        $total = number_format((float)$total, 2, '.', '');


        $currencyShop = $this->getCurrencyIso($cart);

        if ($currencyShop == "USD"){
            $numberCurrency = '840';
        }else{
            $numberCurrency = '422';
        }

        $reference =  "{$id_order}NI" .rand(10, 9000);

        $response = Context::getContext()->link->getModuleLink('netcommerceipay', 'response');
 	    $signatureHash = $total.$numberCurrency.$reference.$this->merchantId.$response.$this->sha_key;
	    $signature = hash('sha256', $signatureHash);

        $state = $order->getCurrentState();
        if ($state) {

            $smarty->assign(array(
                    'reference' => $reference,
                    'status' => 'ok',
                    'total' => $total,
                    'numbercurrency' => $numberCurrency,
                    'sha_key' => trim($this->sha_key),
                    'merchantId' => trim($this->merchantId),
                    'shakey'   => $this->sha_key,
                    'signature' => $signature,
                    'isTest' => $this->isTest,
                    'p_billing_email' => $this->context->customer->email,
                    'p_billing_name' => $this->context->customer->firstname,
                    'p_billing_lastname' => $this->context->customer->lastname,
                    'address' => $addressdelivery->address1,
                    'country' => $addressdelivery->country,
                    'city' => $addressdelivery->city,
                    'postal' => $addressdelivery->postcode,
                    'phone' => $addressdelivery->phone,
                    'restore' => Context::getContext()->link->getModuleLink('netcommerceipay', 'restore'),
                    'response' => $response
                )
            );

        } else {
            $smarty->assign('status', 'failed');
        }
        return $this->display(__FILE__, "payment_return.tpl");
    }


    public function checkCurrency($cart)
    {

        $currency_iso_code = $this->getCurrencyIso($cart);
        $currency_support = array('USD');

        if(!in_array($currency_iso_code, $currency_support))
            return false;

        $currency_order = new Currency((int)($cart->id_currency));
        $currencies_module = $this->getCurrency((int)$cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getCurrencyIso($cart)
    {
        $id_currency = $cart->id_currency;
        $currency = new CurrencyCore($id_currency);
        return $currency->iso_code;
    }

}
