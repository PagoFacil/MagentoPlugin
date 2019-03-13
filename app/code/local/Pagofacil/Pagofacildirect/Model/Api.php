<?php

/**
 * API para el consumo de los servicios de PagoFacil Plugin Magento 
 * @author Miguel Gonzalez @miguelgoma <mike@pagofacil.net>
 */

class Pagofacil_Pagofacildirect_Model_Api
{

    /**
     * URL del servicio de PagoFacil en ambiente de desarrollo
     * @var string 
     */
    
    protected $_urlDemo = 'https://sandbox.pagofacil.tech/Magento/Magento/index/format/json/?method=transaccion';

    /**
     * URL del servicio de PagoFacil para verificar el cobro
     * @var string 
     */
    protected $_urlVerify = 'https://api.pagofacil.tech/Magento/Magento/querytrans/';

    /**
     * URL del servicio de PagoFacil en ambiente de produccion
     * @var string 
     */
    
    protected $_urlProd = 'https://api.pagofacil.tech/Wsrtransaccion/index/format/json?method=transaccion'

    /**
     * consume el servicio de pago de PagoFacil
     * @param string[] vector con la informacion de la peticion
     * @return mixed respuesta del consumo del servicio
     */

    /**
     * respuesta sin parsear del servicio
     * @var string
     */
    protected $_response = NULL;    
    
    public function __construct()
    {
        
    }

    public function payment($info)
    {

        $response = null;

        if (!is_array($info)) throw new Exception('parameter is not an array');

        // Determina el entorno 
        $ambiente = ($info['prod'] == '1') ? $this->_urlProd : $this->_urlDemo;

        // Lanza la transaccion
        $query     = $this->buildParams($this->infoBuild('data', $info));
        
        $consumeWS = json_decode($this->consumeWsPost($ambiente, $query),true);

        if (is_array($consumeWS)) {
            return $consumeWS['WebServices_Transacciones']['transaccion'];
        }

        $date = date('Y-m-d H:i:s');
        $nTransaccion  = $this->infoBuild('error', $info);

        $fecha = "Creado el: ".$date.' Transaccion: '. $nTransaccion['idPedido'];

        Mage::log($fecha, null, 'mylogMagento.log', true);

        //Verifica si la transaccion existe y responde
        $response = $this->verifyTransactionMagento('verify', $info);

        return $response;
    }

    /**
     * consume el servicio de pago en efectivo de PagoFacil
     * @param string[] vector con la informacion de la peticion
     * @return mixed respuesta del consumo del servicio
     * @throws Exception
     */

    public function paymentCash($info)
    {

        $response = null;        
        
        if (!is_array($info))
        {
            throw new Exception('parameter is not an array');
        }

        $info['url'] = 'https://api.pagofacil.tech/cash/charge';
        // determinar si el entorno es para pruebas
        if ($info['prod'] == '0')
        {
            $info['url'] = 'https://sandbox.pagofacil.tech/cash/charge';
        }

        // datos para la peticion del servicio
        $data = array(
            'branch_key'       => $info['branch_key'],
            'user_key'         => $info['user_key'],
            'order_id'         => $info['order_id'],
            'product'          => $info['product'],
            'amount'           => $info['amount'],
            'store_code'       => $info['storeCode'],
            'customer'         => $info['customer'],
            'email'            => $info['email']
        );

        // consumo del servicio
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $info['url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Blindly accept the certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $this->_response = curl_exec($ch);

        curl_close($ch);

        // tratamiento de la respuesta del servicio
        $response = json_decode($this->_response,true);               

        return $response;
    }

    /**
     * Envía la solicitud POST para consumir el sw de transacciones Magento
     * @param 
     * @return 
     */
    private function consumeWsPost($url, $params)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $this->_response = curl_exec($ch);
        curl_close($ch);

        return $this->_response;

    }

    /**
     * Arreglos de parametros en el ws
     * @param Array Info
     * @return Array Type
     */
    private function infoBuild($tipo, array $info )
    {
        
        // Datos para la peticion del servicio
        $data = array(
            'idServicio'        => '3',
            'idSucursal'        => $info['idSucursal'],
            'idUsuario'         => $info['idUsuario'],
            'nombre'            => $info['nombre'],
            'apellidos'         => $info['apellidos'],
            'numeroTarjeta'     => $info['numeroTarjeta'],
            'cvt'               => $info['cvt'],
            'cp'                => $info['cp'],
            'mesExpiracion'     => $info['mesExpiracion'],
            'anyoExpiracion'    => $info['anyoExpiracion'],
            'monto'             => $info['monto'],
            'email'             => $info['email'],
            'telefono'          => $info['telefono'],
            'celular'           => $info['celular'],
            'calleyNumero'      => $info['calleyNumero'],
            'colonia'           => $info['colonia'],
            'municipio'         => $info['municipio'],
            'estado'            => $info['estado'],
            'pais'              => $info['pais'],
            'idPedido'          => $info['idPedido'],
            'ip'                => $info['ipBuyer'],
            'noMail'            => $info['noMail'],
            'plan'              => $info['plan'],
            'mensualidades'     => $info['mensualidades'],
        );

        // Datos para la verificacion de una transaccion
        $verify = array(
            'idSucursal'        => $info['idSucursal'],
            'idUsuario'         => $info['idUsuario'],
            'monto'             => $info['monto'],
            'idPedido'          => $info['idPedido'],
        );

        // Datos para los logs de transaccion erronea
        $error = array(
            'idPedido'          => $info['idPedido'],
        );

        return $$tipo;

    }

    /**
     * Construye el querystring de parametros a enviar en el ws
     * @param array de datos
     * @return querystring
     */
    private function buildParams(array $data)
    {

        $query = '';
        foreach ($data as $key=>$value){
            $query .= sprintf("&data[%s]=%s", $key, urlencode($value));
        }

        return $query;

    }

    /**
     * Consume el sw de magento verificando si la transaccion existe
     * @param tipo de arreglo, array de informacion general
     * @return resultado de la consulta al sw
     */
    private function verifyTransactionMagento($type, array $info)
    {

        $query        = $this->buildParams($this->infoBuild($type, $info));
        $respVerifyWS = json_decode($this->consumeWsPost($this->_urlVerify, '?'.$query),true);

        return $respVerifyWS;

    }

    /**
     * obtiene la respuesta del servicio
     * @return string
     */
    public function getResponse()
    {
        return $this->_response;
    }

}