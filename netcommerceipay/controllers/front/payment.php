<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 24/11/17
 * Time: 02:36 PM
 */
class NetcommerceIpayPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;
    public $display_column_right = false;
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $cart = $this->context->cart;
        $transid = $cart->id . "" . time();
        $addressdelivery = new Address(intval($cart->id_address_delivery));
        $addressbilling = new Address(intval($cart->id_address_invoice));
        if (!$this->module->checkCurrency($cart))
            Tools::redirect('index.php?controller=order');
        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $total,
            'iva' => $cart->getOrderTotal(true, Cart::BOTH) - $cart->getOrderTotal(false, Cart::BOTH),
            'baseDevolucionIva' => $cart->getOrderTotal(false, Cart::BOTH),
            'this_path' => $this->module->getPathUri(),
            'this_path_bw' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
        ));
        $this->setTemplate("payment.tpl");
    }
}