<?php
/** 
*  Webhook para notificaciones de pagos.
*
*  @author waldix (waldix86@gmail.com)
*/

class Pagofacil_Pagofacildirect_WebhookController extends Mage_Core_Controller_Front_Action{

    public function indexAction(){
  		$params = $this->getRequest()->getParams();

  		$body = @file_get_contents('php://input');
  	    $event_json = json_decode($body);
  	    $_id = $event_json->{'customer_order'};
  	    $_type = $event_json->{'status'};	    

          $config = Mage::getModel('pagofacildirect/CashCP');        
  		$_order = Mage::getModel('sales/order')->loadByIncrementId($_id);

  		switch ($_type) {    
  			case 1:
  			    $status = $config->getConfigData('order_status_in_process');
  			    $message = 'The user has not completed the payment process yet.';
  			    $_order->addStatusToHistory($status, $message);
  			    break;
  		    case 2:
  			    $status = $config->getConfigData('order_status_in_process');
  			    $message = 'The user has not completed the payment process yet.';
  			    $_order->addStatusToHistory($status, $message);
  			    break;
  			case 3:         
  				$status = $config->getConfigData('order_status_cancelled');
  			    $message = 'The user has not completed the payment and the order was cancelled.';
  				$_order->cancel();
  				break;
  			case 4:
  			    $createinvoice = Mage::getModel('pagofacildirect/CashCP')->getConfigData('auto_create_inovice');
  			    if ($createinvoice == 1){     
  					if(!$_order->hasInvoices()){
  					    $invoice = $_order->prepareInvoice();   
  					    $invoice->register()->pay();
  					    Mage::getModel('core/resource_transaction')
  					    ->addObject($invoice)
  					    ->addObject($invoice->getOrder())
  					    ->save();					    
  					    
  					    $message = 'Payment '.$invoice->getIncrementId().' was created. ComproPago automatically confirmed payment for this order.';
  					    $status = $config->getConfigData('order_status_approved');
  					    $_order->addStatusToHistory($status,$message,true);
  					    $invoice->sendEmail(true, $message);
  					}
  			    } else {				
  					$message = 'ComproPago automatically confirmed payment for this order.';
  					$status = $config->getConfigData('order_status_approved');
  					$_order->addStatusToHistory($status,$message,true);				
  			    }
  			    break;
  			default:
  			    $status = $config->getConfigData('order_status_in_process');
  			    $message = "";    
  			    $_order->addStatusToHistory($status, $message);
  		}
			
  		$_order->save();		    

    }

    public function threedSecurePfAction(){

  		$this->loadLayout();

      $this->renderLayout();

    }

    public function getOnepage()
    {

        return Mage::getSingleton('checkout/type_onepage');

    }

}
/*
 * Clase para el desencriptado del post 3dSecure Banorte
 *
 */
class PagoFacil_Descifrado_Descifrar
{
   
    public static function desencriptar($encodedInitialData, $key)

    {
        $encodedInitialData =  base64_decode($encodedInitialData);
        $cypher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        if (mcrypt_generic_init($cypher, $key, $key) != -1)
        {
            $decrypted = mdecrypt_generic($cypher, $encodedInitialData);
            mcrypt_generic_deinit($cypher);
            mcrypt_module_close($cypher);
            return self::pkcs5_unpad($decrypted);
        }
        return "";
    }
    private static function pkcs5_pad ($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    private static function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
    }
    /**
     * This class uses by default hex2bin function, but it is only available since 5.4, because the current php version
     * is 5.2 this function was created with the purpose to replace the default function.
     *
     * @param $hex_string
     *
     * @return string
     */
    private static function hexToBin($hex_string)
    {
        $pos = 0;
        $result = '';
        while ($pos < strlen($hex_string)) {
            if (strpos(" \t\n\r", $hex_string{$pos}) !== FALSE) {
                $pos++;
            } else {
                $code = hexdec(substr($hex_string, $pos, 2));
                $pos = $pos + 2;
                $result .= chr($code);
            }
        }
        return $result;
    }

}
