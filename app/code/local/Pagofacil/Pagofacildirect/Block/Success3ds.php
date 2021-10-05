<?php

	class Pagofacil_Pagofacildirect_Block_Success3ds extends Mage_Core_Block_Template
	{
			protected function _construct(){
				parent::_construct();
				$this->setTemplate('pagofacildirect/success3ds.phtml');
			}

			public function Responde()
			{        
				$resp = $this->getRequest()->getPost();
				$resp = $resp['response'];
				$objPF = new PagoFacil_Descifrado_Descifrar();
				
				$key = Mage::helper('pagofacildirect/Data')->keyEncrypted();
				
				$txtDesencripted = $objPF->desencriptar($resp, $key);
				$jsonDes = json_decode($txtDesencripted, true);
				
				return $jsonDes['autorizado'];
				
			}
		}