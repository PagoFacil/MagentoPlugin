<?php

	class Pagofacil_Pagofacildirect_Block_Success3ds extends Mage_Core_Block_Template
	{
			protected function _construct(){
				parent::_construct();
				$this->setTemplate('pagofacildirect/success3ds.phtml');
			}

			public function saveOrder($data)
			{
				//$result = array();
				//$this->getOnepage()->getQuote()->save();
				/**
				 * change order status to 'Completed'
				 **/
		
				$order = Mage::getModel('sales/order')->loadByIncrementId($data['idPedido']);
				$order->setData('state', "processing");
				$order->setStatus("processing");
				$history = $order->addStatusHistoryComment('Cambio de estado por codigo.', false);
				$history->setIsCustomerNotified(false);
				$order->save();

			}
		

			public function Responde()
			{        
				$resp = $this->getRequest()->getPost();
				$resp = $resp['response'];
				$objPF = new PagoFacil_Descifrado_Descifrar();
				
				$key = Mage::helper('pagofacildirect/Data')->keyEncrypted();
				
				$txtDesencripted = $objPF->desencriptar($resp, $key);
				$jsonDes = json_decode($txtDesencripted, true);

				if ($jsonDes['autorizado']>0)
				{
					$data = array(
					'method'         =>     "pagofacildirect",
					'nombre'         =>     $jsonDes['data']['nombre'],
					'apellidos'      =>     $jsonDes['data']['apellidos'],
					'numero_tarjeta' =>     $jsonDes['data']['numeroTarjeta'],
					'cvt'            =>     $jsonDes['data']['cvt'],
					'mes_expiracion' =>     $jsonDes['data']['mesExpiracion'],
					'anyo_expiracion'=>     $jsonDes['data']['anyoExpiracion'],
					'telefono'       =>     $jsonDes['data']['telefono'],
					'celular'        =>     $jsonDes['data']['celular'],
					'calley_numero'  =>     $jsonDes['data']['calleyNumero'],
					'colonia'        =>     $jsonDes['data']['colonia'],
					'municipio'      =>     $jsonDes['data']['municipio'],
					'estado'         =>     $jsonDes['data']['estado'],
					'pais'           =>     $jsonDes['data']['pais'],
					'cp'             =>     $jsonDes['data']['cp'],
					'email'          =>     $jsonDes['data']['email'],
					'idPedido'       =>     $jsonDes['data']['idPedido']
					);
					$this->saveOrder($data);					
				}
				return $jsonDes['autorizado'];
		}
	}