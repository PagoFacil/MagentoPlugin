<?php

/**
 * Clase helper para verificar informacion en el modelo standard.php 
 * para el 3d Secure de Banorte
 * @author Miguel Gonzalez <mike@pagofacil.net> @miguelgoma
 **/
class Pagofacil_Pagofacildirect_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function __construct()
    {
        $this->tdsecure = new Pagofacil_Pagofacildirect_Model_Standard;
    }

    /**
     * Verifica si se tiene configurada la opción 3dSecure en el admin de magento.
     * return 1 ó 0
     **/
    public function verificaTDSecureConfig()
    {
        return $this->tdsecure->tDSecureConfig();
    }

    /**
     * Consulta la información adicional al formulario para el envío a 3D Secure Banorte
     * return Array
     **/
    public function datosAdicionalesTreeD()
    {
        return $this->tdsecure->addDataTreeDSecure();
    }

    /**
     * Verifica la llave de cifrado configurada en Admin para la desencriptacion de datos
     * return String
     **/
    public function keyEncrypted()
    {
        return $this->tdsecure->encryptedKey();
    }

}