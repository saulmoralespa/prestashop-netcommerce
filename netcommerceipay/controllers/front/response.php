<?php
class NetcommerceIpayResponseModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $this->context = Context::getContext();

        if (!isset($_POST['RespVal']))
            die('No params received');

	$txtMerchNum = $_POST['txtMerchNum'];
	$txtIndex = $_POST['txtIndex'];
	$txtAmount = $_POST['txtAmount'];
	$txtCurrency = $_POST['txtCurrency'];
	$txtNumAut = $_POST['txtNumAut'];
	$RespVal = $_POST['RespVal'];
	$RespMsg = $_POST['RespMsg'];
	$NCSignature = $_POST['signature'];

	$netcommerceipay = new NetcommerceIpay;
	$sha_key = $netcommerceipay->sha_key;

        $delimit = explode('NI', $txtIndex);
        $order_id = $delimit[0];

	$sha256_value = $txtMerchNum.$txtIndex.$txtAmount.$txtCurrency.$txtNumAut.$RespVal.$RespMsg.$sha_key;
	$signature = hash('sha256', $sha256_value);

	$message = "";

	if($NCSignature == $signature){
        switch ($RespVal) {
            case '1':
                $netcommerceipay->PaymentStatus('APPROVED', $order_id);
                $message = "Your Transaction was successful" . PHP_EOL . "Order ID: " . $txtIndex . PHP_EOL . "Authorization no.: ".$txtNumAut  . PHP_EOL . "Amount: ".$txtAmount  . PHP_EOL;
                break;
            case '0':
                $netcommerceipay->PaymentStatus('DECLINED',$order_id);
                $message = "Unfortunately your transaction was refused" . PHP_EOL . "Order ID: " . $txtIndex  . PHP_EOL . "Amount: ".$txtAmount  . PHP_EOL;
                break;
        }
    }else{
        $message = "Security violation was detected!" . PHP_EOL . "Order ID: " . $txtIndex . PHP_EOL;
    }

        Context::getContext()->smarty->assign(
            array(
                'message' => $message
            )
        );

        $this->setTemplate('response.tpl');
    }
}
?>
