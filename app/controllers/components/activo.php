<?php
/**
 * Componente que hace interfaz con el gateway, con el cual se invocan
 * los servicios primarios del banco
 *
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Importación de la librería del manejo de los webservices.
 */
App::Import('Vendor', 'nusoap/nusoap');


/**
 * Maneja los métodos generales del sistema.
 */
class ActivoComponent extends Object
{

    /**
     * Controlador
     *
     * @var string
     */
    var $controller;

    /**
     * Bancos
     *
     * @var array
     */
    private static $bancos;

    /**
     * Bancos
     *
     * @var array
     */
    private static $bancos2;

    /**
     * Productos del usuario
     *
     * @var array
     */
    var $instrumentos;

    /**
     * Objeto de conexión del webservice
     *
     * @var object
     */
    var $client;
    
    /**
     * Objeto de conexión del webservice
     *
     * @var object
     */
    var $clientp2p;
    
    /**
     * Objeto de conexión del webservice
     *
     * @var object
     */
    var $clientSimf;

    /**
     * Número de error de una respuesta
     *
     * @var int
     */
    var $error;

    /**
     * Descripción del error de una respuesta
     *
     * @var string
     */
    var $descripcion_error;

    /**
     * Componentes del cake utilizados
     *
     * @var array
     */

      /**
     * Objeto de conexión del webservice
     *
     * @var object
     */
    
    
    var $components = array('Session', 'Email', 'Sms', 'Compensacion');


    /**
     * Método invocado inicialmente por el CakePHP
     *
     * @param string $controller Controlador que incluye al componente
     * @param array $settings Configuración básica del componente
     */
    function initialize(&$controller, $settings = array())
    {
        $this->controller = $controller;
    }


    /**
     * Método general para realizar la invocación a una operación del webservice.
     *
     * @param string $servicio Nombre del servicio en el gateway
     * @param array $parametros Parámetros del servicio
     * @return array|boolean Falso si hubo error. Arreglo con la respuesta si no
     *
     */

    function invoca($servicio, $parametros, $token2, $response_timeout = null)
    {

        App::import('Component', 'Session');
        App::import('Component', 'Activo');

        $activo = new ActivoComponent();    
        $session = new SessionComponent();

        $token = $session->read('token.token');

        if ($response_timeout == null) {
            $response_timeout = 60;
        }
        if (!isset($this->client)) {
            $this->client = new nusoap_client(Configure::read('Gateway.pn.url'), 'wsdl');
            $this->client->timeout = 45;
        }

        $this->client->setCredentials($token,$token,'jwt');
        $this->client->response_timeout = $response_timeout;

        $res = $this->client->call(
            $servicio,
            $parametros
        );

        $this->log('Resultado: ', LOG_DEBUG);
        $this->log($res, LOG_DEBUG);

        if ($this->client->fault) {

            $this->log('Error invocando ' . $servicio, LOG_ERR);
            $this->log('Parámetros:', LOG_ERR);
            $this->log($parametros, LOG_ERR);
            $this->log('Error técnico:', LOG_ERR);
            $this->log($res, LOG_ERR);



            if (isset($this->controller)) {
                $this->controller->set('error_fatal', _m('error.fatal'));
            }

            $this->error = -2;
            return false;
            
        } else {
            $err = $this->client->getError();

            if ($err) {
                $this->log('Error invocando ' . $servicio, LOG_ERR);
                $this->log('Parámetros:', LOG_ERR);
                $this->log($parametros, LOG_ERR);
                $this->log('Error técnico:', LOG_ERR);
                $this->log($err, LOG_ERR);

                if (isset($this->controller)) {
                    $this->controller->set('error_fatal', _m('error.fatal'));
                }

                $this->error = -2;
                return false;
            }
        }

        return $res;
    }

    /*Agregado para Subasta en IB*/

    function invocaSub($servicio, $parametros, $token2 = null, $response_timeout = null)
    {

        App::import('Component', 'Session');
        App::import('Component', 'Activo');

        $activo = new ActivoComponent();    
        $session = new SessionComponent();

        //$token = $session->read('token.token');

        if ($response_timeout == null) {
            $response_timeout = 60;
        }

        $soapClientSub = new nusoap_client(Configure::read('Subasta'), 'wsdl');
        $soapClientSub->timeout = 45;
        // if (!isset($this->client)) {
            //$this->client = new nusoap_client(Configure::read('Subasta'), 'wsdl');
            // $this->client->timeout = 45;
        // }

        // print_r(Configure::read('Subasta'));

        //$soapClientSub->setCredentials($token,$token,'jwt');
        $soapClientSub->response_timeout = $response_timeout;

        $res = $soapClientSub->call(
            $servicio,
            $parametros
        );

        // print_r($soapClientSub->request);
        // print_r($soapClientSub->response);
        // print_r($soapClientSub->getDebug());

        $this->log('Resultado: ', LOG_DEBUG);
        $this->log($res, LOG_DEBUG);

        if ($soapClientSub->fault) {

            $this->log('Error invocando ' . $servicio, LOG_ERR);
            $this->log('Parámetros:', LOG_ERR);
            $this->log($parametros, LOG_ERR);
            $this->log('Error técnico:', LOG_ERR);
            $this->log($res, LOG_ERR);



            if (isset($this->controller)) {
                $this->controller->set('error_fatal', _m('error.fatal'));
            }

            $this->error = -2;
            return false;
            
        } else {
            $err = $soapClientSub->getError();

            if ($err) {
                $this->log('Error invocando ' . $servicio, LOG_ERR);
                $this->log('Parámetros:', LOG_ERR);
                $this->log($parametros, LOG_ERR);
                $this->log('Error técnico:', LOG_ERR);
                $this->log($err, LOG_ERR);

                if (isset($this->controller)) {
                    $this->controller->set('error_fatal', _m('error.fatal'));
                }

                $this->error = -2;
                return false;
            }
        }

        return $res;
    }


        function invocaContacless($servicio, $parametros, $token2, $response_timeout = null)
    {
        
        App::import('Component', 'Session');
        App::import('Component', 'Activo');

        $activo = new ActivoComponent();    
        $session = new SessionComponent();

        $token = $session->read('token.token');

        if ($response_timeout == null) {
            $response_timeout = 60;
        }
        if (!isset($this->client)) {
            $this->client = new nusoap_client(Configure::read('Gateway.Contactless.url'), 'wsdl');
            $this->client->timeout = 45;
        }

        $this->client->setCredentials($token,$token,'jwt');
        $this->client->response_timeout = $response_timeout;

        $res = $this->client->call(
            $servicio,
            $parametros
        );

        $this->log('Resultado: ', LOG_DEBUG);
        $this->log($res, LOG_DEBUG);

        if ($this->client->fault) {

            $this->log('Error invocando ' . $servicio, LOG_ERR);
            $this->log('Parámetros:', LOG_ERR);
            $this->log($parametros, LOG_ERR);
            $this->log('Error técnico:', LOG_ERR);
            $this->log($res, LOG_ERR);



            if (isset($this->controller)) {
                $this->controller->set('error_fatal', _m('error.fatal'));
            }

            $this->error = -2;
            return false;
            
        } else {
            $err = $this->client->getError();

            if ($err) {
                $this->log('Error invocando ' . $servicio, LOG_ERR);
                $this->log('Parámetros:', LOG_ERR);
                $this->log($parametros, LOG_ERR);
                $this->log('Error técnico:', LOG_ERR);
                $this->log($err, LOG_ERR);

                if (isset($this->controller)) {
                    $this->controller->set('error_fatal', _m('error.fatal'));
                }

                $this->error = -2;
                return false;
            }
        }

        return $res;
    }

    function invocaMovilnet($parametros, $response_timeout = null)
    {
        if ($response_timeout == null) {
            $response_timeout = 60;
        }
        if (!isset($this->clientmovilnet)) {
            $this->clientmovilnet = new nusoap_client(Configure::read('Gateway.movilnet.url'), 'wsdl');
            $this->clientmovilnet->timeout = 45;
            $this->clientmovilnet->wsdl = null;
        }
        $this->clientmovilnet->response_timeout = $response_timeout;
        $this->clientmovilnet->trace = 1;

        $this->log('Invocando IN801301', LOG_DEBUG);
        $this->log($parametros, LOG_DEBUG);
        $this->log(Configure::read('Gateway.movilnet.url'), LOG_DEBUG);
    
        $res = $this->clientmovilnet->call(
            'IN801301',
            $parametros
        );

        $this->log('Resultado: ', LOG_DEBUG);
        $this->log($res, LOG_DEBUG);
		
        if ($this->clientmovilnet->fault) {
            $this->log('Error SOAP FAULT invocando IN801301', LOG_ERR);
            $this->log('Parámetros:', LOG_ERR);
            $this->log($parametros, LOG_ERR);
            $this->log('Error técnico (fault):', LOG_ERR);
            $this->log($res, LOG_ERR);
            $this->log('GetError():', LOG_ERR);
            $this->log($this->clientmovilnet->getError(), LOG_ERR);
            
            if (isset($this->controller)) {
                $this->controller->set('error_fatal', _m('error.fatal'));
            }
            $this->error = -2;
			
            return false;
            
        } else {
            $err = $this->clientmovilnet->getError();
            if ($err) {
                $this->log('Error invocando IN801301', LOG_ERR);
                $this->log('Parámetros:', LOG_ERR);
                $this->log($parametros, LOG_ERR);
                $this->log('Error técnico:', LOG_ERR);
                $this->log($err, LOG_ERR);
                
                if (isset($this->controller)) {
                    $this->controller->set('error_fatal', _m('error.fatal'));
                }
                $this->error = -2;
                return false;
            }
        }

        return $res;
    }

    function invocaCorpoelect($servicio, $parametros, $response_timeout = null)
    {
        if ($response_timeout == null) {
            $response_timeout = 60;
        }
        if (!isset($this->clientCorpoelect)) {

            $this->clientCorpoelect = new nusoap_client(Configure::read('Gateway.Corpoelec.url'), 'wsdl');
            $this->clientCorpoelect->timeout = 45;
            // print_r($this->clientCorpoelect);die("111");
        }
        $this->clientCorpoelect->response_timeout = $response_timeout;

        $this->log('Invocando ' . $servicio, LOG_DEBUG);
        $this->log($parametros, LOG_DEBUG);
        $this->log(Configure::read('Gateway.Corpoelec.url'), LOG_DEBUG);

        $res = $this->clientCorpoelect->call(
            $servicio,
            $parametros
        );

        $this->log('Resultado: ', LOG_DEBUG);
        $this->log($res, LOG_DEBUG);

        if ($this->clientCorpoelect->fault) {
            $this->log('Error invocando ' . $servicio, LOG_ERR);
            $this->log('Parámetros:', LOG_ERR);
            $this->log($parametros, LOG_ERR);
            $this->log('Error técnico:', LOG_ERR);
            $this->log($res, LOG_ERR);

            if (isset($this->controller)) {
                $this->controller->set('error_fatal', _m('error.fatal'));
            }

            $this->error = -2;
            return false;
            
        } else {
            $err = $this->clientCorpoelect->getError();

            if ($err) {
                $this->log('Error invocando ' . $servicio, LOG_ERR);
                $this->log('Parámetros:', LOG_ERR);
                $this->log($parametros, LOG_ERR);
                $this->log('Error técnico:', LOG_ERR);
                $this->log($err, LOG_ERR);

                if (isset($this->controller)) {
                    $this->controller->set('error_fatal', _m('error.fatal'));
                }

                $this->error = -2;
                return false;
            }
        }

        return $res;
    }
     
    
    /**
     * Método general para realizar la invocación a una operación del webservice.
     *
     * @param string $servicio Nombre del servicio en Pago Activo
     * @param array $parametros Parámetros del servicio
     * @return array|boolean Falso si hubo error. Arreglo con la respuesta si no
     *
     */
    function invocaPagoActivo($servicio, $parametros, $response_timeout = null)
    {
        if ($response_timeout == null) {
            $response_timeout = 60;
        }
                    
        switch($servicio){
            case 'afiliacion_p2p':
                $url = Configure::read('PagoActivo.url.afiliacion');
                break;
            case 'modificacion_basica_p2p':
                $url = Configure::read('PagoActivo.url.modificacion');
                break;
            case 'desafiliar_p2p':
                $url = Configure::read('PagoActivo.url.desafiliacion');
                break;
            case 'consulta_limite_p2p':
                $url = Configure::read('PagoActivo.url.consulta_limites');
                break;
             case 'consulta_basica_p2p':
                $url = Configure::read('PagoActivo.url.consulta_basica');
                break;           
            case 'consulta_saldo_p2p':
                $url = Configure::read('PagoActivo.url.consulta_saldo');
                break;
            case 'directorio_p2p':
                $url = Configure::read('PagoActivo.url.directorio');
                break;
            case 'consulta_bancos_p2p':
                $url = Configure::read('PagoActivo.url.consulta_bancos');
                break;
            case 'pago_p2p':
                $url = Configure::read('PagoActivo.url.pago');
                break;                                                   
        }

        $this->clientp2p = new nusoap_client($url, 'wsdl');
        $this->clientp2p->timeout = 45;
                
        $this->clientp2p->response_timeout = $response_timeout;        

        $this->log('Invocando ' . $servicio, LOG_DEBUG);
        $this->log($parametros, LOG_DEBUG);

        $res = $this->clientp2p->call(
            $servicio,
            $parametros
        );
        
        $this->log('Resultado: ', LOG_DEBUG);
        $this->log($res, LOG_DEBUG);

        if ($this->clientp2p->fault) {
            $this->log('Error invocando ' . $servicio, LOG_ERR);
            $this->log('Parámetros:', LOG_ERR);
            $this->log($parametros, LOG_ERR);
            $this->log('Error técnico:', LOG_ERR);
            $this->log($res, LOG_ERR);

            if (isset($this->controller)) {
                $this->controller->set('error_fatal', _m('error.fatal'));
            }

            $this->error = -2;
            return false;
        } else {
            $err = $this->clientp2p->getError();

            if ($err) {
                $this->log('Error invocando ' . $servicio, LOG_ERR);
                $this->log('Parámetros:', LOG_ERR);
                $this->log($parametros, LOG_ERR);
                $this->log('Error técnico:', LOG_ERR);
                $this->log($err, LOG_ERR);

                if (isset($this->controller)) {
                    $this->controller->set('error_fatal', _m('error.fatal'));
                }

                $this->error = -2;
                return false;
            }
        }

        return $res;
    }
    
    /**
     * Método general para realizar la invocación a una operación del webservice.
     *
     * @param string $servicio Nombre del servicio en el gateway
     * @param array $parametros Parámetros del servicio
     * @return array|boolean Falso si hubo error. Arreglo con la respuesta si no
     *
     */

    function invocaSIMF($servicio, $parametros, $response_timeout = null)
    {
        if ($response_timeout == null) {
            $response_timeout = 60;
        }
        if (!isset($this->clientSimf)) {
            $this->clientSimf = new nusoap_client(Configure::read('Gateway.simf.url'), 'wsdl');
            $this->clientSimf->timeout = 45;
        }
        $this->clientSimf->response_timeout = $response_timeout;

        $this->log('Invocando ' . $servicio, LOG_DEBUG);
        $this->log($parametros, LOG_DEBUG);
        $this->log(Configure::read('Gateway.simf.url'), LOG_DEBUG);

        $res = $this->clientSimf->call(
            $servicio,
            $parametros
        );

        $this->log('Resultado: ', LOG_DEBUG);
        $this->log($res, LOG_DEBUG);

        if ($this->clientSimf->fault) {
            $this->log('Error invocando ' . $servicio, LOG_ERR);
            $this->log('Parámetros:', LOG_ERR);
            $this->log($parametros, LOG_ERR);
            $this->log('Error técnico:', LOG_ERR);
            $this->log($res, LOG_ERR);

            if (isset($this->controller)) {
                $this->controller->set('error_fatal', _m('error.fatal'));
            }

            $this->error = -2;
            return false;
            
        } else {
            $err = $this->clientSimf->getError();

            if ($err) {
                $this->log('Error invocando ' . $servicio, LOG_ERR);
                $this->log('Parámetros:', LOG_ERR);
                $this->log($parametros, LOG_ERR);
                $this->log('Error técnico:', LOG_ERR);
                $this->log($err, LOG_ERR);

                if (isset($this->controller)) {
                    $this->controller->set('error_fatal', _m('error.fatal'));
                }

                $this->error = -2;
                return false;
            }
        }

        return $res;
    }

       /**
     * Método general para realizar la invocación a una operación del webservice rest.
     *
     * @param string $url Url del Servicio
     * @param string $request Tipo de Consulta (POST, GET)
     * @param array $parametros Parámetros del servicio
     * @return array|boolean Falso si hubo error. Arreglo con la respuesta si no
     *
     */
    function invocaSaren($url, $request, $parametros, $token='')
    {

            $curl = curl_init();
            
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $request,
                CURLOPT_POSTFIELDS => json_encode($parametros),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "token: " . $token
                ),
            ));

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($curl);

            $arregloResp = json_decode($response);
            $resp_all = get_object_vars($arregloResp);  

            if(isset($resp_all['code'])){
                curl_close($curl);
                return $arregloResp;
            }else{
                curl_close($curl);
                return 'error';
            }  
    }

    /**
     * Método general para realizar la invocación a una operación del webservice rest.
     *
     * @param string $url Url del Servicio
     * @param string $request Tipo de Consulta (POST, GET)
     * @param array $parametros Parámetros del servicio
     * @return array|boolean Falso si hubo error. Arreglo con la respuesta si no
     *
     */
    function invocaCurl($url, $request, $parametros, $token='')
    {
            
           $curl = curl_init();

                      
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $request,
                CURLOPT_POSTFIELDS => json_encode($parametros),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: " . $token
                ),
            ));

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($curl);


            curl_close($curl);
            
            return json_decode($response);
    }

    /**
     * Método general para realizar la invocación a una operación del webservice rest.
     *
     * @param string $url Url del Servicio
     * @param string $request Tipo de Consulta (POST, GET)
     * @param array $parametros Parámetros del servicio
     * @return array|boolean Falso si hubo error. Arreglo con la respuesta si no
     *
     */
    function invocaToken($url, $request, $parametros, $token='')
    {
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => $request,
              CURLOPT_POSTFIELDS => json_encode($parametros),
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return json_decode($response);
    }
    
            
    /**
     * Retorna un arreglo asociativo con el código como clave
     * y el nombre del banco como valor.
     *
     * @return array Arreglo con los bancos
     */
    function buscarBancos($socket=NULL)
    {
        
            if (!isset(self::$bancos)) {
                if(!isset($socket)){
                    $res = $this->invoca(
                        'BNKSRDSJV',
                        array(
                            'req' => array(
                                'BNKPVI' => 'F'
                            )
                        )
                    );
                }else{
                    $res = $this->invoca(
                        'BNKSRDSJV',
                        array(
                            'req' => array(
                                'BNKPVI' => 'T'
                            )
                        )
                    );  
                }

                if (isset($res['BNKSRDSJVReturn']['BNKSRDSJV']['BNKSRDSJV'])) {
                    foreach ($res['BNKSRDSJVReturn']['BNKSRDSJV']['BNKSRDSJV'] as $i) {
                        self::$bancos[substr($i['BNKID'], -4)] = $i['BNKNME'];
                    }
                }
            }

        return self::$bancos;
    }


    /**
     * Retorna un arreglo con los datos de los bancos.
     *
     * @return array Arreglo de bancos donde la clave es el código del banco y el valor
     * es un arreglo con todas las propiedades del mismo
     */
    function buscarBancos2()
    {
        if (!isset(self::$bancos2)) {
            $res = $this->invoca(
                'BNKSRDSJV',
                array(
                    'req' => array(
                        'BNKPVI' => 'F'
                    )
                )
            );

            if (isset($res['BNKSRDSJVReturn']['BNKSRDSJV']['BNKSRDSJV'])) {
                foreach ($res['BNKSRDSJVReturn']['BNKSRDSJV']['BNKSRDSJV'] as $i) {
                    self::$bancos2[substr($i['BNKID'], -4)] = $i;
                }
            }
        }

        return self::$bancos2;
    }

    /**
     * Dado el un número de tarjeta de crédito existente, se busca el detalle de la tdc en el as400.
     *
     * @param $num Número de TDC
     * @return array Arreglo con los detalles de la TDC
     * @throws Exception
     */
    public function buscaDetalleTdc($num){

        // Validamos que esten enviando el numero de tdc
        if(is_null($num) || trim($num) == "") throw new Exception("El número de TDC es obligatorio");

        // Solo trabajamos con dos marcas, todas las tarjetas que comienzan con 4 son VISA, con 5 son MASTERCARD. Si esto
        // llega a cambiar hay que ajustar.
        $marca = array('4'=>'VISA', '5'=>'MASTERCARD');
        $tarj = array();

        $res = $this->invoca(
            'CARDSJV',
            array(
                'req'=>array(
                    'CARNUM'=> $num
                )
            )
        );

        if(isset($res)){
            if(isset($res['CARDSJVReturn']['CARDSJV']['CARAAD'])) {
                $t = $res['CARDSJVReturn']['CARDSJV'];

                $tarj['numero'] = (trim($t['CARNUM']));
                $tarj['saldoDisponible'] = (floatval($t['CARAVC']));
                $tarj['moneda'] = (trim($t['CARCCY']));
                $tarj['descripcion'] = (trim($t['CARNIK']));
                $tarj['estatus'] = (trim($t['CARCFA']));
                $tarj['fechaProximoPago'] = $t['CARED1'].'/'.$t['CARED2'].'/'.$t['CARED3'];
                $tarj['fechaUltimoPago'] = $t['CARLP1'].'/'.$t['CARLP2'].'/'.$t['CARLP3'];
                $tarj['fechaVencimientoPago'] = $t['CARDU1'].'/'.$t['CARDU2'].'/'.$t['CARDU3'];
                $tarj['limiteCredito'] = (floatval($t['CARCRL']));
                $tarj['pagoMinimo'] = (floatval($t['CARPMA']));
                $tarj['marca'] = $marca[substr($num,0,1)];
                $tarj['saldoActual'] = ($t['CARBAL']);
                $tarj['fechaUltimaFacturacion'] = $t['CARLB1'].'/'.$t['CARLB2'].'/'.$t['CARLB3'];
            }
        }
        return $tarj;
    }

    /**
     * Dado un número de tdc y un número de extrafinanciamiento, busca en el detalle de dicho extrafinanciamiento, debido a
     * que los números de extrafinanciamientos no son únicos, se debe realizar la busqueda junto con el número de tdc.
     *
     * @param $tarjeta Número de tdc
     * @param $numCred Número de extrafinanciamiento
     * @return array Arreglo con los movimientos cargados en ese extrafinanciamiento
     * @throws Exception
     */
    public function detalleExtrafinanciamientos($tarjeta, $numCred){

        // Validamos que esten enviando el numero de tdc
        if(is_null($tarjeta) || trim($tarjeta) == "") throw new Exception("El número de TDC es obligatorio");

        // Validamos que esten enviando el numero de extrafinanciamiento
        if(is_null($numCred) || trim($numCred) == "") throw new Exception("El número de extrafinanciamiento es obligatorio");


        $res = $this->invoca(
            'obtieneMovimientosExtraCredito',
            array(
                'req' => array(
                    'numeroTarjeta' => $tarjeta,
                    'numeroCredito' => $numCred
                )
            )
        );

        $movimientos = array();

        if($res){
            if (isset($res['MovimientosExtraCreditoReturn']['movimientos']['movimientos']['concepto'])) {
                $movimientos[] = $res['MovimientosExtraCreditoReturn']['movimientos']['movimientos'];
            }
            else{
                $movimientos = $res['MovimientosExtraCreditoReturn']['movimientos']['movimientos'];
            }
        }

        if(count($movimientos) > 0){
            return $movimientos;
        }else{
            throw new Exception('El extracredito no posee movimientos', -1);
        }
    }

    /**
     * Dado un númerod de TDC, consulta la información sobre los extrafinanciamientos activos en la linea paralela.
     *
     * @param $tarjeta Núnmero de TDC.
     * @return array Arreglo con los extrafinanciamientos activos.
     * @throws Exception Si la tdc no posee extrafinanciamientos se lanza una exception con código -1.
     */
    public function consolidadoExtrafinanciamientos($tarjeta)
    {
        // Validamos que esten enviando el numero de tdc
        if(is_null($tarjeta) || trim($tarjeta) == "") throw new Exception("El número de TDC es obligatorio");

        $res = $this->invoca(
            'obtieneExtracreditosInfo',
            array(
                'req' => array(
                    'numeroTarjeta' => $tarjeta
                )
            )
        );

        $extraCreditos = array();

        if(isset($res['ExtracreditoInfoReturn']['extInfo']['extInfo'])){
            if (isset($res['ExtracreditoInfoReturn']['extInfo']['extInfo']['codigoComercio'])) {
                $extraCreditos[] = $res['ExtracreditoInfoReturn']['extInfo']['extInfo'];
            }
            else{
                $extraCreditos = $res['ExtracreditoInfoReturn']['extInfo']['extInfo'];
            }
        }



        if (count($extraCreditos) > 0) {
            $respuesta['saldo_actual'] = 0;
            foreach ($extraCreditos as $e) {
                $respuesta['saldo_actual'] += $e['saldoPendiente'];
                $respuesta['detalles'][] = $e;
            }
            return $respuesta;
        } else {
            throw new Exception('La tarjeta no posee Extrafinanciamientos', -1);
        }
    }

    /**
     * Realiza un avance de efectivo.
     *
     * Dado los datos de entrada, realiza un avance de efectivo en la tdc seleccionada. El avance se realiza a través
     * del webservice en el Gateway para este proposito.
     *
     * @param $num_tarjeta Número de TDC a la cual se le realiza el cargo.
     * @param $cuenta_abonar Si el avance es satisfactorio se realiza el abono en esta cuenta.
     * @param $monto Monto del avance de efectivo
     * @return array Arreglo con el resultado del avance de efectivo.
     */
    function realizaAvanceEfectivo($num_tarjeta, $cuenta_abonar, $monto)
    {
        $usuario = $this->Session->read('usuarios.datos');
        $avance = array();
        if (isset($num_tarjeta) && isset($cuenta_abonar) && isset($monto) &&
            $num_tarjeta != '' && $cuenta_abonar != '' && $monto != ''
        ) {
            $res = $this->invoca(
                'avanceEfectivo',
                array(
                    'req' => array(
                        'numTarjeta' => $num_tarjeta,
                        'monto' => $monto,
                        'cuentaAbono' => $cuenta_abonar,
                        'numCliente' => $usuario['nrocliente'],
                        'emailCliente' => desencriptar($usuario['email_principal']),
                        'nombreCliente' => desencriptar($usuario['nombre'])
                    )
                )
            );

            if (isset($res['AdelantoEfectivoReturn']['codigoRespuesta'])) {
                $avance['tipo_mensaje'] = $res['AdelantoEfectivoReturn']['tipoMensaje'];
                $avance['num_tarjeta'] = $res['AdelantoEfectivoReturn']['numTarjeta'];
                $avance['cuenta_abonar'] = $res['AdelantoEfectivoReturn']['cuentaAbono'];
                $avance['cod_autorizacion'] = $res['AdelantoEfectivoReturn']['codigoAutorizacion'];
                $avance['cod_respuesta'] = $res['AdelantoEfectivoReturn']['codigoRespuesta'];
                $avance['respuesta'] = $res['AdelantoEfectivoReturn']['respuesta'];
                $avance['monto'] = $res['AdelantoEfectivoReturn']['monto'];
                $avance['referencia'] = $res['AdelantoEfectivoReturn']['numReferencia'];
            }
        }
        return $avance;
    }


    /**
     * Realiza un avance de ExtraCredito efectivo
     *
     * Dado los datos de entrada, realiza un avance de ExtraCredito en linea paralela de la tdc seleccionada.
     * El avance se realiza a través del webservice en el Gateway para este proposito.
     *
     * @param $num_tarjeta Número de TDC donde se realiza el cargo.
     * @param $cuenta_abonar  Si el avance es satisfactorio se realiza el abono en esta cuenta.
     * @param $monto Monto del avance de efectivo
     * @param $plazo Plazo de financiamiento del extracredito.
     * @return array Arreglo con el resultado del Avance de extracredito
     */
    function realizaAvanceExtraCredito($num_tarjeta, $cuenta_abonar, $monto, $plazo)
    {
        $usuario = $this->Session->read('usuarios.datos');
        $avance = array();
        if (isset($num_tarjeta) && isset($cuenta_abonar) && isset($monto) &&
            $num_tarjeta != '' && $cuenta_abonar != '' && $monto != '' && $plazo != ''
        ) {
            $res = $this->invoca(
                'avanceExtraCredito',
                array(
                    'req' => array(
                        'numTarjeta' => $num_tarjeta,
                        'monto' => $monto,
                        'cuentaAbono' => substr($cuenta_abonar, -10),
                        'dataExtraFinanciamiento' => $plazo,
                        'numCliente' => $usuario['nrocliente'],
                        'emailCliente' => desencriptar($usuario['email_principal']),
                        'nombreCliente' => desencriptar($usuario['nombre'])
                    )
                ), 60);

            if (isset($res['AdelantoEfectivoReturn']['codigoRespuesta'])) {
                $avance['tipo_mensaje'] = $res['AdelantoEfectivoReturn']['tipoMensaje'];
                $avance['num_tarjeta'] = $res['AdelantoEfectivoReturn']['numTarjeta'];
                $avance['cuenta_abonar'] = $res['AdelantoEfectivoReturn']['cuentaAbono'];
                $avance['cod_autorizacion'] = $res['AdelantoEfectivoReturn']['codigoAutorizacion'];
                $avance['cod_respuesta'] = $res['AdelantoEfectivoReturn']['codigoRespuesta'];
                $avance['respuesta'] = $res['AdelantoEfectivoReturn']['respuesta'];
                $avance['monto'] = $res['AdelantoEfectivoReturn']['monto'];
                $avance['referencia'] = $res['AdelantoEfectivoReturn']['numReferencia'];
                $avance['primer_pago'] = $res['AdelantoEfectivoReturn']['dataExtraFinanciamiento'];
            }
        }

        return $avance;
    }


    /**
     * Realiza la busqueda del saldo disponible en la tarjeta de credito para realizar ExtraCredito Efectivo
     *
     * @return array Arreglo con los detalles de la tarjeta de credito
     *
     *
     */

    function consultaExtraCredito($num_tarjeta, $monto)
    {
        $usuario = $this->Session->read('usuarios.datos');

        $saldo = array(
            'tipo_mensaje' => '',
            'num_tarjeta' => '',
            'cod_autorizacion' => '',
            'cod_respuesta' => '',
            'monto' => $monto,
            'referencia' => ''
        );
        if (isset($num_tarjeta) && $num_tarjeta != '') {
            $res = $this->invoca(
                'consultaSaldoExtraCredito',
                array(
                    'req' => array(
                        'numTarjeta' => $num_tarjeta,
                        'monto' => $monto,
                        'numCliente' => $usuario['nrocliente']
                    )
                ), 60);

            if (isset($res['AdelantoEfectivoReturn']['codigoRespuesta'])) {
                $saldo['tipo_mensaje'] = $res['AdelantoEfectivoReturn']['tipoMensaje'];
                $saldo['num_tarjeta'] = $res['AdelantoEfectivoReturn']['numTarjeta'];
                $saldo['cod_autorizacion'] = $res['AdelantoEfectivoReturn']['codigoAutorizacion'];
                $saldo['cod_respuesta'] = $res['AdelantoEfectivoReturn']['codigoRespuesta'];
                $saldo['monto'] = (double)$res['AdelantoEfectivoReturn']['montoConsultaSaldo'];
                $saldo['referencia'] = $res['AdelantoEfectivoReturn']['numReferencia'];


            }
        }
        return $saldo;
    }


    /**
     * Realiza la busqueda del saldo disponible en la tarjeta de credito para realizar avances de efectivo
     *
     * @return array Arreglo con los detalles de la tarjeta de credito
     *
     *
     */
    function buscarSaldoAvance($num_tarjeta)
    {
        $saldo = array(
            'tipo_mensaje' => '',
            'num_tarjeta' => '',
            'cod_autorizacion' => '',
            'cod_respuesta' => '',
            'monto' => 0,
            'referencia' => ''
        );
        $usuario = $this->Session->read('usuarios.datos');
        if (isset($num_tarjeta) && $num_tarjeta != '') {
            $res = $this->invoca(
                'consultaSaldoAvance',
                array(
                    'req' => array(
                        'numTarjeta' => $num_tarjeta,
                        'numCliente' => $usuario['nrocliente']
                    )
                )
            );
            if (isset($res['AdelantoEfectivoReturn']['codigoRespuesta'])) {
                $saldo['tipo_mensaje'] = $res['AdelantoEfectivoReturn']['tipoMensaje'];
                $saldo['num_tarjeta'] = $res['AdelantoEfectivoReturn']['numTarjeta'];
                $saldo['cod_autorizacion'] = $res['AdelantoEfectivoReturn']['codigoAutorizacion'];
                $saldo['cod_respuesta'] = $res['AdelantoEfectivoReturn']['codigoRespuesta'];
                $saldo['monto'] = (double)$res['AdelantoEfectivoReturn']['montoConsultaSaldo'];
                $saldo['referencia'] = $res['AdelantoEfectivoReturn']['numReferencia'];


            }
        }

        return $saldo;
    }

    /**
     * Realiza la búsqueda de los productos del usuario que califican para avance de efectivo
     *  y extracredito, genera un arreglo para ser utilizado en los combos.
     *
     * @param $extra boolean, si true es busqueda para extracredito
     * @return array Arreglo con las cuentas donde la clave es el número de cuenta y el valor es el número de cuenta enmascarado
     *
     */
    function buscarInstrumentosAvance($extra = false)
    {
        $cuentas = array();
        $tarjetas = array();

        // Numero de emisor de las tarjetas garantizadas
        /** Los siguientes emisores se estan incorporando para solventar una incidencia, pudiendo ser eliminados
         * de la lista luego de solventada la incidencia. 4744971722  4744961725 5210411715 5301471712
         * fecha 21/07/2015
         * @author Miguel Mendez
         *
         */
        $garantizadas = array('5301470412', '4744970422', '4744971722', '4744961725', '5210411715', '5301471712');


        $status_validos = array('01', '02', '03'); // 01 Activo, 02 Nueva, 03, Sin movimiento. Niniguna tiene bloqueos

        $usuario = $this->Session->read('usuarios.datos');

        /** Para identificar si es un usuario juridico **/
        if ($usuario['tipo_rif']) {
            $tipo_documento = $usuario['tipo_rif'];
            $documento = desencriptar($usuario['rif']);
        } else {
            $tipo_documento = $usuario['tipo_cedula'];
            $documento = desencriptar($usuario['cedula']);
        }
        $datos_basicos = $this->Session->read('usuarios.nibpf3010');
        
        if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
            $res = $this->invoca(
                'SUMDSJV',
                array(
                    'req' => array(
                        'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                        'SUMFIR' => 'X'
                    )
                )
            );
            if (isset($res)) {
                //Buscamos las cuentas
                foreach ($res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'] as $producto) {
                    // SI son cuentas de ahorro o corriente
                    if (isset($producto['SUMTYP'])) {
                        if ($producto['SUMTYP'] == 'DDA' || $producto['SUMTYP'] == 'SAV' || $producto['SUMTYP'] == 'NOW' || $producto['SUMTYP'] == 'MMK') {
                            // Si no es IVSS
                            if (trim($producto['SUMUSR']) != 'IVSS' && trim($producto['SUMCCY']) == 'VEB') {
                                // Si la cuenta esta activa
                                if (trim($producto['SUMAST']) == 'A') {
                                    $cuentas[$producto['SUMACN']] = enmascara(trim($producto['SUMACN']));
                                }
                            }
                        }
                    }
                }
                reset($res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV']);

                //Buscamos la tarjetas
                foreach ($res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'] as $producto) {
                    // SI son tarjetas de cr?dito
                    if (isset($producto['SUMTYP'])) {
                        if (trim($producto['SUMTYP']) == 'CC&') {
                            // Buscamos el datalle de la tarjeta
                            $num_tarjeta = trim($producto['SUMACN']);

                            // para extracredito no tomamos las tarjetas garantizadas
                            if ($extra && in_array(substr($num_tarjeta, 0, 10), $garantizadas)) {
                                    

                                break;
                            }

                            $detalle_tarjeta = $this->invoca(
                                'NIBPF3010',
                                array(
                                    'req' => array(
                                        'NROTDC' => $num_tarjeta,
                                        'NROPARAM' => '4'
                                    )
                                )
                            );
                            
                            if (isset($detalle_tarjeta)) {
                                $t = $detalle_tarjeta['NIBPF3010Return']['NIBPF3010'];
                                // Seleccionamos si la tarjeta esta activa y es individual
                                if (in_array($t['STSTDC'],
                                        $status_validos) && ($t['DISPONIBLE1'] == 'TCIN' || $t['DISPONIBLE1'] == 'P')
                                ) {
                                    $numero = $t['NROTDC'];
                                    settype($numero, 'string');
                                    $tarjeta = array($numero => enmascaraTDC($t['NROTDC']));
                                    array_push($tarjetas, $tarjeta);
                                }
                            }
                        }
                    }
                }

            }
        }
        return array('cuentas' => $cuentas, 'tarjetas' => $tarjetas);

    }

    /**
     * Realiza la búsqueda de los productos del usuario y genera un arreglo para
     * ser utilizado en los combos.
     *
     * @param array $tipos Arreglo de códigos de tipo de instrumentos
     * @param boolean $consulta_cuenta Indica si trae sólo las cuentas activas
     * @param array $consulta_empcorp Indica si la consulta es para el detalle de una corporativa o empresarial (Tarjetas Asociadas)
     * @return array Arreglo con las cuentas donde la clave es el número de cuenta
     * y el valor es el número de cuenta enmascarado
     *
     */
    function buscarInstrumentos($tipos, $consulta_cuenta = false, $consulta_empcorp = null, $tipo = null)
    {

        $usuario = $this->Session->read('usuarios.datos');        
        
        /** Para identificar si es un usuario juridico **/
        if ($usuario['tipo_rif']) {
            $tipo_documento = $usuario['tipo_rif'];
            $documento = desencriptar($usuario['rif']);
        } else {
            $tipo_documento = $usuario['tipo_cedula'];
            $documento = desencriptar($usuario['cedula']);
        }

        $datos_basicos = $this->Session->read('usuarios.nibpf3010');

        if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
            if (empty($consulta_empcorp)) {
                $res = $this->invoca(
                    'SUMDSJV',
                    array(
                        'req' => array(
                            'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                            'SUMFIR' => 'X'
                        )
                    )
                );
            } else {
                $res = $this->invoca(
                    'SUMDSJV',
                    array(
                        'req' => array(
                            'SUMACN' => trim($consulta_empcorp),
                            'SUMFIR' => $tipo
                        )
                    )
                );
            }
        }

        $this->instrumentos = array();

        if (isset($res) && !empty($res['SUMDSJVReturn']['SUMDSJV'])) {
            $this->instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

            normaliza($this->instrumentos);
        } else {
            $this->instrumentos = array();
        }

        $deseados = array();

        foreach ($this->instrumentos as $i) {
                
            if (in_array($i['SUMTYP'], $tipos)) {                
                // si es un usuario juridico verifica si tiene permiso en la cuenta
                // verifica si viene de las acciones de resultado
                if ($usuario['tipo_rif'] && $usuario['rol_juridico_id'] > 2 && !preg_match('/resultado/i',
                        $this->controller->params['action'])
                ) {

                    // si viene del controlador del cash omite la verificacion
                    if ($this->controller->params['controller'] != 'cash') {

                        // se indica la transaccion de acuerdo al producto
                        switch ($i['SUMTYP']) {
                            case 'SAV':
                            case 'DDA':
                            case 'NOW':
                            case 'MMK':    
                                $transaccion = 3;

                                // verifica si es del controlador de transferencia
                                if ($this->controller->params['controller'] == 'transferencias') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 100021;
                                            break;
                                        case 'terceros':
                                            $transaccion = 100022;
                                            break;
                                        case 'otros':
                                            $transaccion = 100023;
                                            break;
                                        case 'terceros_otros':
                                            $transaccion = 100024;
                                            break;
                                    }

                                } // verifica si es del controlador de pagos
                                elseif ($this->controller->params['controller'] == 'pagos_tdc') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 100025;
                                            break;
                                        case 'terceros':
                                            $transaccion = 100026;
                                            break;
                                        case 'otros':
                                            $transaccion = 100027;
                                            break;
                                        case 'terceros_otros':
                                            $transaccion = 100028;
                                            break;
                                    }
                                }
                                // verifica si es del controlador de pago activo
                                elseif ($this->controller->params['controller'] == 'pago_activo') {
                                                                            
                                    // verifica si es afiliacion o pagos                                    
                                    switch ($this->controller->params['action']) {
                                        case 'afiliacion_crear':
                                            $transaccion = 100064;
                                            break;
                                        case 'pagos_noafiliada':
                                            $transaccion = 100065;
                                            break;                                        
                                    }
                                }
                                break;

                            case 'CC&':
                            case 'TCC':
                            case 'TCE':
                            case 'TCO':
                            case 'TEM':
                                $transaccion = 4;
                                break;

                            case 'CDS':
                                $transaccion = 6;
                                break;

                            case 'LNS':
                                $transaccion = 5;
                                break;
                        }

                        if (!permisoCuenta($usuario['id'], $transaccion, trim($i['SUMACN']))) {
                            continue;
                        }

                    }
                }
                
                // Omite las cuentas que sean inactivas
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && $i['SUMAST'] != 'A' && !$consulta_cuenta) {
                    continue;
                }
                               
                // Omite las cuentas de moneda extranjera (pago activo)
                if ($this->controller->params['controller'] == 'pago_activo' && $i['SUMCCY'] != 'VEB') {
                    continue;
                }
                
                if ($i['SUMTYP'] == 'LNS') {
                    $deseados[trim($i['SUMACN'])] = $i['SUMACN'];
                } else {
                    $deseados[trim($i['SUMACN'])] = enmascara($i['SUMACN']);
                }
            }
        }

        // Si no encontro una cuenta
        if (count($deseados) == 0) {
            $deseados[''] = 'Estimado Cliente, no posee productos activos';
        }

        return $deseados;
    }


//JS2020

 /**
     * Realiza la búsqueda de los productos del usuario y genera un arreglo para
     * ser utilizado en los combos.
     *
     * @param array $tipos Arreglo de códigos de tipo de instrumentos
     * @param boolean $consulta_cuenta Indica si trae sólo las cuentas activas
     * @param array $consulta_empcorp Indica si la consulta es para el detalle de una corporativa o empresarial (Tarjetas Asociadas)
     * @return array Arreglo con las cuentas donde la clave es el número de cuenta
     * y el valor es el número de cuenta enmascarado
     *
     */
    function buscarInstrumentosMe($tipos, $consulta_cuenta = false, $consulta_empcorp = null, $tipo = null)
    {

        $usuario = $this->Session->read('usuarios.datos');        
        
        /** Para identificar si es un usuario juridico **/
        if ($usuario['tipo_rif']) {
            $tipo_documento = $usuario['tipo_rif'];
            $documento = desencriptar($usuario['rif']);
        } else {
            $tipo_documento = $usuario['tipo_cedula'];
            $documento = desencriptar($usuario['cedula']);
        }

        $datos_basicos = $this->Session->read('usuarios.nibpf3010');

        if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
            if (empty($consulta_empcorp)) {
                $res = $this->invoca(
                    'SUMDSJV',
                    array(
                        'req' => array(
                            'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                            'SUMFIR' => 'X'
                        )
                    )
                );
            } else {
                $res = $this->invoca(
                    'SUMDSJV',
                    array(
                        'req' => array(
                            'SUMACN' => trim($consulta_empcorp),
                            'SUMFIR' => $tipo
                        )
                    )
                );
            }
        }

        $this->instrumentos = array();

        if (isset($res) && !empty($res['SUMDSJVReturn']['SUMDSJV'])) {
            $this->instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

            normaliza($this->instrumentos);
        } else {
            $this->instrumentos = array();
        }

        $deseados = array();

        foreach ($this->instrumentos as $i) {
                
            if (in_array($i['SUMTYP'], $tipos)) {                
                // si es un usuario juridico verifica si tiene permiso en la cuenta
                // verifica si viene de las acciones de resultado
                if ($usuario['tipo_rif'] && $usuario['rol_juridico_id'] > 2 && !preg_match('/resultado/i',
                        $this->controller->params['action'])
                ) {

                    // si viene del controlador del cash omite la verificacion
                    if ($this->controller->params['controller'] != 'cash') {

                        // se indica la transaccion de acuerdo al producto
                        switch ($i['SUMTYP']) {
                            case 'SAV':
                            case 'DDA':
                            case 'NOW':
                            case 'MMK':    
                                $transaccion = 3;

                                // verifica si es del controlador de transferencia
                                if ($this->controller->params['controller'] == 'transferencias') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 101070;
                                            break;
                                        case 'terceros':
                                            $transaccion = 101071;
                                            break;
                                        case 'otros':
                                            $transaccion = 100023;
                                            break;
                                        case 'terceros_otros':
                                            $transaccion = 100024;
                                            break;
                                    }

                                } // verifica si es del controlador de pagos
                                elseif ($this->controller->params['controller'] == 'pagos_tdc') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 100025;
                                            break;
                                        case 'terceros':
                                            $transaccion = 100026;
                                            break;
                                        case 'otros':
                                            $transaccion = 100027;
                                            break;
                                        case 'terceros_otros':
                                            $transaccion = 100028;
                                            break;
                                    }
                                }
                                // verifica si es del controlador de pago activo
                                elseif ($this->controller->params['controller'] == 'pago_activo') {
                                                                            
                                    // verifica si es afiliacion o pagos                                    
                                    switch ($this->controller->params['action']) {
                                        case 'afiliacion_crear':
                                            $transaccion = 100064;
                                            break;
                                        case 'pagos_noafiliada':
                                            $transaccion = 100065;
                                            break;                                        
                                    }
                                }
                                break;

                            case 'CC&':
                            case 'TCC':
                            case 'TCE':
                            case 'TCO':
                            case 'TEM':
                                $transaccion = 4;
                                break;

                            case 'CDS':
                                $transaccion = 6;
                                break;

                            case 'LNS':
                                $transaccion = 5;
                                break;
                        }

                        if (!permisoCuenta($usuario['id'], $transaccion, trim($i['SUMACN']))) {
                            continue;
                        }

                    }
                }
                
                // Omite las cuentas que sean inactivas
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && $i['SUMAST'] != 'A' && !$consulta_cuenta) {
                    continue;
                }
                               
                // Omite las cuentas de moneda extranjera (pago activo)
                if ($this->controller->params['controller'] == 'pago_activo' && $i['SUMCCY'] != 'VEB') {
                    continue;
                }
                
                if ($i['SUMTYP'] == 'LNS') {
                    $deseados[trim($i['SUMACN'])] = $i['SUMACN'];
                } else {
                    $deseados[trim($i['SUMACN'])] = enmascara($i['SUMACN']);
                }
            }
        }

        // Si no encontro una cuenta
        if (count($deseados) == 0) {
            $deseados[''] = 'Estimado Cliente, no posee productos activos';
        }

        return $deseados;
    }

//js2020


    /**
     * Realiza la búqueda de los productos de un usuario y retorna un arreglo
     * con la información.
     *
     * @param array $tipos Arreglo de códigos de tipo de instrumentos
     * @param boolean $consulta_cuenta Indica si trae sólo las cuentas activas
     * @return array Arreglo con las cuentas donde la clave es el número de cuenta
     * @return string Arreglo de cuentas donde la clave es el número de cuenta y
     * el valor es un arreglo con los datos de la cuenta
     */
    function buscarInstrumentos2($tipos, $consulta_cuenta = false)
    {
        $usuario = $this->Session->read('usuarios.datos');
        if (!isset($this->instrumentos)) {

            /** Para identificar si es un usuario juridico **/
            if ($usuario['tipo_rif']) {
                $tipo_documento = $usuario['tipo_rif'];
                $documento = desencriptar($usuario['rif']);
            } else {
                $tipo_documento = $usuario['tipo_cedula'];
                $documento = desencriptar($usuario['cedula']);
            }

            $datos_basicos = $this->Session->read('usuarios.nibpf3010');

            if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
                $res = $this->invoca(
                    'SUMDSJV',
                    array(
                        'req' => array(
                            'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                            'SUMFIR' => 'X'
                        )
                    )
                );
            }

            $this->instrumentos = array();

            if (isset($res) && !empty($res)) {
                $this->instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

                normaliza($this->instrumentos);
            } else {
                $this->instrumentos = array();
            }
        }

        $deseados = array();

        foreach ($this->instrumentos as $i) {
            if (in_array($i['SUMTYP'], $tipos)) {

                // si es un usuario juridico verifica si tiene permiso en la cuenta
                if ($usuario['tipo_rif'] && $usuario['rol_juridico_id'] > 2) {

                    // si viene del controlador del cash omite la verificacion
                    if ($this->controller->params['controller'] != 'cash' && $this->controller->params['controller'] != 'transacciones_pendientes') {

                        // se indica la transaccion de acuerdo al producto
                        switch ($i['SUMTYP']) {
                            case 'SAV':
                            case 'DDA':
                            case 'NOW':
                            case 'MMK':    
                                $transaccion = 3;

                                // verifica si es del controlador de transferencia
                                if ($this->controller->params['controller'] == 'transferencias') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 100021;
                                            break;
                                        case 'terceros':
                                            $transaccion = 100022;
                                            break;
                                        case 'otros':
                                            $transaccion = 100023;
                                            break;
                                        case 'terceros_otros':
                                            $transaccion = 100024;
                                            break;
                                    }

                                } // verifica si es del controlador de transferencias moneda extranjera
                                elseif ($this->controller->params['controller'] == 'transferencias_me') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 101070;
                                            break;
                                        case 'terceros':
                                            $transaccion = 101071;
                                            break;                                        
                                    }
                                } // verifica si es del controlador de pagos
                                elseif ($this->controller->params['controller'] == 'pagos_tdc') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 100025;
                                            break;
                                        case 'terceros':
                                            $transaccion = 100026;
                                            break;
                                        case 'otros':
                                            $transaccion = 100027;
                                            break;
                                        case 'terceros_otros':
                                            $transaccion = 100028;
                                            break;
                                    }

                                }// verifica si es del controlador de pago activo
                                elseif ($this->controller->params['controller'] == 'pago_activo') {

                                    // verifica si es afiliacion o pagos                                    
                                    switch ($this->controller->params['action']) {
                                        case 'afiliacion_crear':
                                            $transaccion = 100064;
                                            break;
                                        case 'pagos_noafiliada':
                                            $transaccion = 100065;
                                            break;                                        
                                    }
                                }
                                break;

                            case 'CC&':
                                $transaccion = 4;
                                break;

                            case 'CDS':
                                $transaccion = 6;
                                break;

                            case 'LNS':
                                $transaccion = 5;
                                break;
                        }

                        if (!permisoCuenta($usuario['id'], $transaccion, trim($i['SUMACN']))) {
                            continue;
                        }
                    }
                }

                // Omite las cuentas que sean inactivas
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && $i['SUMAST'] != 'A' && !$consulta_cuenta) {
                    continue;
                }                

                $deseados[trim($i['SUMACN'])] = $i;
            }
        }

        // Si no encontro una cuenta
        if (count($deseados) == 0) {
            $deseados[''] = 'Estimado Cliente, no posee cuentas activas';
        }

        return $deseados;
    }


    /**
     * Realiza la búqueda de los productos de un usuario deacuerdo a los tipos indicados.
     *
     * @param char $nacionalidad
     * @param string $identificacion
     * @param array $tipos
     * @return array
     */
    function buscarInstrumentos3($nacionalidad, $identificacion, $tipos)
    {

        // Consulta los datos basicos del usuario
        $datos_basicos = $this->Session->read('usuarios.nibpf3010');

        // Consulta los productos del usuario
        $instrumentos = array();

        if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
            $res = $this->invoca(
                'SUMDSJV',
                array(
                    'req' => array(
                        'SUMACN' => $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],
                        'SUMFIR' => 'X'
                    )
                )
            );

            if ($res) {
                $instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
                normaliza($instrumentos);
            }
        }

        $cuentas = array();

        foreach ($instrumentos as $i) {
            // cuentas
            if (in_array($i['SUMTYP'], $tipos)) {
                $cuentas[trim($i['SUMACN'])] = $i;
            }
        }

        return $cuentas;
    }


    /**
     * Realiza la búsqueda de las cuentas.
     *
     * @return array Arreglo con las cuentas corriente y de ahorro
     */
    function buscarCuentas()
    {
        return $this->buscarInstrumentos(array('SAV', 'DDA', 'NOW', 'MMK'));
    }


    function buscarCuentasMe()
    {
        return $this->buscarInstrumentosMe(array('SAV', 'DDA', 'NOW', 'MMK'));
    }


    /**
     * Realiza la búsqueda de las cuentas activas.
     *
     * @return array Arreglo con las cuentas corriente y de ahorro activas
     */
    function buscarCuentasConsulta()
    {
        return $this->buscarInstrumentos(array('SAV', 'DDA', 'NOW', 'MMK'), true);
    }


    /**
     * Realiza la búsqueda de las cuentas corrientes.
     *
     * @return array Arreglo con las cuentas corrientes activas
     */
    function buscarCuentasCorriente()
    {
        return $this->buscarInstrumentos(array('DDA', 'NOW', 'MMK'));
    }
    
    /**
     * Realiza la búsqueda de las cuentas de ahorro activas
     *
     * @return array Arreglo con las cuentas de ahorro activas
     */
    function buscarCuentasAhorro()
    {
        return $this->buscarInstrumentos(array('SAV'));
    }
    
    /**
     * Realiza la búsqueda de las cuentas donde el cliente sea el titular y esten activas.
     *
     * @return array Arreglo con las cuentas corriente y de ahorro activas donde el cliente sea el titular y esten activas
     */
    function buscarCuentasTitular()
    {
        return $this->buscarInstrumentos(array('SAV', 'DDA', 'NOW', 'MMK'), true, true);
    }

    /**
     * Realiza la búsqueda de los créditos.
     *
     * @return array Arreglo con los créditos
     */
    function buscarCreditos()
    {
        return $this->buscarInstrumentos(array('LNS'));
    }


    /**
     * Realiza la búsqueda de las tarjetas de crédito.
     *
     * @return array Arreglo con las tarjetas de crédito comunes y empresariales
     */
    function buscarTarjetas()
    {
        return $this->buscarInstrumentos(array('CC&', 'TCE'));
    }

    /**
     * Realiza la búsqueda de las tarjetas de crédito empresariales o corporativas que posee un cliente.
     *
     * @return array Arreglo con las tarjetas de crédito
     */
    function buscarTarjetasEmpCorp($tipo)
    {
        return $this->buscarInstrumentos(array($tipo));
    }

    /**
     * Realiza la búsqueda de las tarjetas de crédito asociadas a una tarjeta corporativa.
     *
     * @return array Arreglo con las tarjetas de crédito asociadas
     */
    function buscarTarjetasEmpCorpAsociadas($tipo, $parametros)
    {
        return $this->buscarInstrumentos(array('TCO'), false, $parametros, $tipo);
    }

    /**
     * Realiza la búsqueda de las cuentas incluyendo el saldo.
     *
     * @return array Arreglo con las tarjetas de crédito. Donde la clave
     * es el número de cuenta y el valor es el número de cuenta enmascarado
     * y el saldo disponible de la misma
     */
    function buscarCuentasSaldos()
    {
        return $this->buscarInstrumentosSaldos(array('SAV', 'DDA', 'NOW', 'MMK'));
    }

    /**
     * Realiza la búsqueda de las cuentas moneda extranjera incluyendo el saldo.
     *
     * @return array Arreglo con las tarjetas de crédito. Donde la clave
     * es el número de cuenta y el valor es el número de cuenta enmascarado
     * y el saldo disponible de la misma
     */
    function buscarCuentasSaldosMe()
    {
        return $this->buscarInstrumentosSaldosMe(array('SAV', 'DDA', 'NOW', 'MMK'));
    }
    
    /**
     * Realiza la búsqueda de las cuentas moneda extranjera incluyendo el saldo, tipo y moneda.
     *
     * @return array Arreglo con las tarjetas de crédito. Donde la clave
     * es el número de cuenta y el valor es el número de cuenta enmascarado
     * y el saldo disponible de la misma
     */
    function buscarCuentasSaldosMeTipo()
    {
        return $this->buscarInstrumentosSaldosMe(array('SAV', 'DDA', 'NOW', 'MMK'),true);
    }

    /**Realiza busqueda de cuentas dolares electronica para subas**/
    function buscarCuentasSoloCDPN()
    {
        $usuario = $this->Session->read('usuarios.datos');
        $datos_basicos = $this->Session->read('usuarios.nibpf3010');
        
        // 1. Invocación al WebService (reutilizando la lógica de la función original)
        $res = $this->invoca(
            'SUMDSJV',
            array(
                'req' => array(
                    'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                    'SUMFIR' => 'X'
                )
            )
        );

        $instrumentos = array();
        if (isset($res) && !empty($res)) {
            $instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
            normaliza($instrumentos);
        }

        $deseados = array();

        // 2. Filtrado Estricto para CDPN
        foreach ($instrumentos as $i) {
            
            // FILTRO CLAVE: Solo si el producto (SUMUSR) es exactamente CDPN
            if (trim($i['SUMUSR']) !== 'CDPN') {
                continue;
            }

            // Omite si no está activa
            if ($i['SUMAST'] != 'A') {
                continue;
            }

            // Filtro de seguridad para usuarios naturales (Firmas conjuntas)
            if (empty($usuario['tipo_rif']) && trim($i['SUMFIR']) == '2') {
                continue;
            }

            // Formateo de salida igual al original para mantener compatibilidad con la vista
            $monto_formateado = formatea_monto_fromWs($i['SUMAVB']);
            $deseados[$i['SUMACN']] = enmascara($i['SUMACN']) . ' - ' . $i['SUMCCY'] . ' - ' . $monto_formateado;
        }

        // Si no hay resultados
        if (empty($deseados)) {
            $deseados[''] = 'No posee cuentas CDPN activas';
        }

        return $deseados;
    }

    /**
     * Búsqueda los productos del usuario incluyendo el saldo.
     *
     * @param array $tipos Arreglo de los tipos de cuenta a consultar
     * @return array Arreglo con los instrumentos y sus respectivos saldos
     */
    function buscarInstrumentosSaldos($tipos)
    {
        $usuario = $this->Session->read('usuarios.datos');
        if (!isset($this->instrumentos)) {

            /** Para identificar si es un usuario juridico **/
            if ($usuario['tipo_rif']) {
                $tipo_documento = $usuario['tipo_rif'];
                $documento = desencriptar($usuario['rif']);
            } else {


                
                $tipo_documento = $usuario['tipo_cedula'];
                $documento = desencriptar($usuario['cedula']);
            }

            $datos_basicos = $this->Session->read('usuarios.nibpf3010');

            $identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];
            $pensionado = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'];
            $tarjeta_debito = $datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD'];

            if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
                $res = $this->invoca(
                    'SUMDSJV',
                    array(
                        'req' => array(
                            'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                            'SUMFIR' => 'X'
                        )
                    )
                );
            }

            $this->instrumentos = array();

            if (isset($res) && !empty($res)) {
                $this->instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
                normaliza($this->instrumentos);
            } else {
                $this->instrumentos = array();
            }
        }

        $deseados = array();
        $arreglo = array();

        foreach ($this->instrumentos as $i) {
            if (in_array($i['SUMTYP'], $tipos)) {

                // si es un usuario juridico verifica si tiene permiso en la cuenta
                if ($usuario['tipo_rif'] && $usuario['rol_juridico_id'] > 2) {

                    // si viene del controlador del cash omite la verificacion
                    if ($this->controller->params['controller'] != 'cash') {

                        // se indica la transaccion de acuerdo al producto
                        switch ($i['SUMTYP']) {
                            case 'SAV':
                            case 'DDA':
                            case 'NOW':
                            case 'MMK':    
                                $transaccion = 3;
                                // verifica si es del controlador de transferencia
                                if ($this->controller->params['controller'] == 'transferencias') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 100021;
                                            break;
                                        case 'terceros':
                                            $transaccion = 100022;
                                            break;
                                        case 'otros':
                                            $transaccion = 100023;
                                            break;
                                        case 'terceros_otros':
                                            $transaccion = 100024;
                                            break;
                                    }

                                } // verifica si es del controlador de pagos
                                elseif ($this->controller->params['controller'] == 'pagos_tdc') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 100025;
                                            break;
                                        case 'terceros':
                                            $transaccion = 100026;
                                            break;
                                        case 'otros':
                                            $transaccion = 100027;
                                            break;
                                        case 'terceros_otros':
                                            $transaccion = 100028;
                                            break;
                                    }

                                } // verifica si es del controlador de pagos de impuestos seniat
                                elseif ($this->controller->params['controller'] == 'pagos_seniat') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->Session->read('usuarios.tipo.seniat')) {
                                        case 'p':
                                            $transaccion = 100036;
                                            break;
                                        case 't':
                                            $transaccion = 100037;
                                            break;
                                    }

                                }
                                // verifica si esta modificando una transaccion pendiente
                                if ($this->controller->params['controller'] == 'transacciones_pendientes' && $this->controller->params['form']['tipo_transaccion']) {

                                    // verifica si es transferencias o pagos / mismo banco u otros bancos
                                    switch ($this->controller->params['form']['tipo_transaccion']) {
                                        case 'tpm':
                                            $transaccion = 100021;
                                            break;
                                        case 'ttm':
                                            $transaccion = 100022;
                                            break;
                                        case 'tpo':
                                            $transaccion = 100023;
                                            break;
                                        case 'tto':
                                            $transaccion = 100024;
                                            break;
                                        case 'ppm':
                                            $transaccion = 100025;
                                            break;
                                        case 'ptm':
                                            $transaccion = 100026;
                                            break;
                                        case 'ppo':
                                            $transaccion = 100027;
                                            break;
                                        case 'pto':
                                            $transaccion = 100028;
                                            break;
                                    }
                                }
                                // verifica si es del controlador de pago activo
                                elseif ($this->controller->params['controller'] == 'pago_activo') {

                                    // verifica si es afiliacion o pagos                                    
                                    switch ($this->controller->params['action']) {
                                        case 'afiliacion_crear':
                                        case 'detalle':    
                                            $transaccion = 100064;
                                            break;
                                        case 'pagos_noafiliada':
                                            $transaccion = 100065;
                                            break;                                        
                                    }
                                }
                                break;

                            case 'CC&':
                                $transaccion = 4;
                                break;

                            case 'CDS':
                                $transaccion = 6;
                                break;

                            case 'LNS':
                                $transaccion = 5;
                                break;
                        }

                        if (!permisoCuenta($usuario['id'], $transaccion, trim($i['SUMACN']))) {
                            continue;
                        }
                    }
                }

                // Omite las cuentas que sean inactivas
                if ($i['SUMAST'] != 'A') {
                    continue;
                }

                // Omite las cuentas de moneda extranjera
                if ($i['SUMCCY'] != 'VEB') {
                    continue;
                }

                // Omite las cuentas para pago de colegios
                if (trim($i['SUMUSR']) == 'CCLC') {
                    continue;
                }

                // APLICA SOLO PARA USUARIOS NATURALES
                if (empty($usuario['tipo_rif'])) {

                    // Omite las cuentas de pensionados que no tengan tarjeta de debito
                    /*if (trim($i['SUMUSR']) == 'IVSS' && trim($pensionado) == 'P' && trim($tarjeta_debito) == '') {
                        continue;
                    }*/

                    // Omite las cuentas de firmas conjuntas (1 = Individual 2 = Conjuntas 3 = Indistintas)
                    if (trim($i['SUMFIR']) == '2') {
                        continue;
                    }
                }

                $arreglo[$i['SUMACN']] = enmascara($i['SUMACN']);
                $arreglo[$i['SUMAVB']] = formatea_monto_fromWs($i['SUMAVB']);
                $deseados[$i['SUMACN']] = $arreglo[$i['SUMACN']] . ' - Bs. ' . $arreglo[$i['SUMAVB']];
            }
        }

        // Si no encontro una cuenta
        if (count($deseados) == 0) {
            $deseados[''] = 'Estimado Cliente, no posee cuentas activas';
        }

        return $deseados;
    }

//js 08/2020
 /**
     * Búsqueda los productos moneda extranjera del usuario incluyendo el saldo.
     *Jsulbaran
     * @param array $tipos Arreglo de los tipos de cuenta a consultar
     * @return array Arreglo con los instrumentos y sus respectivos saldos
     */
    function buscarInstrumentosSaldosMe($tipos,$consulta=false)
    {
        $usuario = $this->Session->read('usuarios.datos');
        if (!isset($this->instrumentos)) {

            /** Para identificar si es un usuario juridico **/
            if ($usuario['tipo_rif']) {
                $tipo_documento = $usuario['tipo_rif'];
                $documento = desencriptar($usuario['rif']);
            } else {
                $tipo_documento = $usuario['tipo_cedula'];
                $documento = desencriptar($usuario['cedula']);
            }

            $datos_basicos = $this->Session->read('usuarios.nibpf3010');

            $identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];
            $pensionado = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'];
            $tarjeta_debito = $datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD'];

            if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
                $res = $this->invoca(
                    'SUMDSJV', //estoy aqui JS2020
                    array(
                        'req' => array(
                            'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                            'SUMFIR' => 'X'
                        )
                    )
                );
            }

            $this->instrumentos = array();

            if (isset($res) && !empty($res)) {
                $this->instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

                normaliza($this->instrumentos);
            } else {
                $this->instrumentos = array();
            }
        }
        // print_r($this->instrumentos);die();
        $deseados = array();
        $arreglo = array();

        foreach ($this->instrumentos as $i) {
            if (in_array($i['SUMTYP'], $tipos)) {

                // si es un usuario juridico verifica si tiene permiso en la cuenta
                if ($usuario['tipo_rif'] && $usuario['rol_juridico_id'] > 2) {

                    // si viene del controlador del cash omite la verificacion
                    if ($this->controller->params['controller'] != 'cash' && trim($this->controller->params['controller']) != 'tarjeta_prepagadas') {

                        // se indica la transaccion de acuerdo al producto
                        switch ($i['SUMTYP']) {
                            case 'SAV':
                            case 'DDA':
                            case 'NOW':
                            case 'MMK':    
                                $transaccion = 3;
                                // verifica si es del controlador de transferencia moneda extranjera
                                if ($this->controller->params['controller'] == 'transferenciasMe') {

                                    // verifica si es mismo banco u otros bancos
                                    switch ($this->controller->params['action']) {
                                        case 'propias':
                                            $transaccion = 101071;
                                            break;
                                        case 'terceros':
                                            $transaccion = 101071;
                                            break;                                        
                                    }
                                }

                                // verifica si esta modificando una transaccion pendiente
                                if ($this->controller->params['controller'] == 'transacciones_pendientes' && $this->controller->params['form']['tipo_transaccion']) {

                                    // verifica si es transferencias o pagos / mismo banco u otros bancos
                                    switch ($this->controller->params['form']['tipo_transaccion']) {
                                        case 'tpm':
                                            $transaccion = 101070;
                                            break;
                                        case 'ttm':
                                            $transaccion = 101071;
                                            break;                                        
                                    }
                                }                               
                                break;

                            case 'CC&':
                                $transaccion = 4;
                                break;

                            case 'CDS':
                                $transaccion = 6;
                                break;

                            case 'LNS':
                                $transaccion = 5;
                                break;
                        }

                        if (!permisoCuenta($usuario['id'], $transaccion, trim($i['SUMACN']))) {
                            continue;
                        }
                    }
                }

                // Omite las cuentas que sean inactivas
                if ($i['SUMAST'] != 'A') {
                    continue;
                }

                // Omite las cuentas de moneda nacional
                if ($i['SUMCCY'] == 'VEB' ) {
                    continue;
                }
                  
                //OMITE cuentas 
                if (trim($i['SUMUSR'])=='DPJN' || trim($i['SUMUSR'])=='CPND' || trim($i['SUMUSR'])=='CPJD' || trim($i['SUMUSR'])=='CPJE' ||  trim($i['SUMUSR'])=='CPNE'  || trim($i['SUMUSR'])=='DCPN' || trim($i['SUMUSR'])=='DPJE' || trim($i['SUMUSR'])=='DPJD' ) {
                    continue;
                }
                    
                // Omite las cuentas para pago de colegios
                if (trim($i['SUMUSR']) == 'CCLC') {
                    continue;
                }

                // APLICA SOLO PARA USUARIOS NATURALES
                if (empty($usuario['tipo_rif'])) {

                    // Omite las cuentas de pensionados que no tengan tarjeta de debito
                    if (trim($i['SUMUSR']) == 'IVSS' && trim($pensionado) == 'P' && trim($tarjeta_debito) == '') {
                        continue;
                    }

                    
                }

                // Omite las cuentas de firmas conjuntas (1 = Individual 2 = Conjuntas 3 = Indistintas)
                    if (trim($i['SUMFIR']) == '2') {
                        continue;
                    }

                $simbolo = $i['SUMCCY'];             

                $arreglo[$i['SUMACN']] = enmascara($i['SUMACN']);
                $arreglo[$i['SUMAVB']] = formatea_monto_fromWs($i['SUMAVB']);
                if($consulta){
                    $deseados[$i['SUMACN']] = $i['SUMACN'].'-'.$i['SUMUSR'].'-'.$i['SUMCCY'].'-'.$i['SUMAVB'].'-'.$i['SUMDSC'];
                }else{
                    $deseados[$i['SUMACN']] = $arreglo[$i['SUMACN']] . ' - '. $simbolo.' - ' . $arreglo[$i['SUMAVB']];
                }
            }
        }

        // Si no encontro una cuenta
        if (count($deseados) == 0) {
            $deseados[''] = 'Estimado Cliente, no posee cuentas activas';
        }

        return $deseados;
    }


//js fin 08/2020


    /**
     * Realiza el cálculo de los pagos realizados en una tarjeta.
     *
     * @param string $cedula Identificación del usuario
     * @param string $tarjeta Número de tarjeta
     * @return float Monto total de los pagos pendientes de la tarjeta
     */
    function calcularTotalPagosPendientes($cedula, $tarjeta)
    {
        $res = $this->invoca(
            'CCARDTRJV',
            array(
                'req' => array(
                    'CCATRNUM' => $tarjeta,
                    'CCATROPC' => 'TR'
                )
            )
        );

        $totalesAbono = 0;

        if ($res && isset($res['CCARDTRJVReturn']['CCARDTRJV']['CCARDTRJV'])) {
            $x = $res['CCARDTRJVReturn']['CCARDTRJV']['CCARDTRJV'];
            normaliza($x);

            foreach ($x as $i) {
                if (trim($i['h01FLGWK3'] == 'X')) {
                    $totalesAbono += $i['CCATRAMCR'];
                }
            }
        }

        return $totalesAbono;
    }

    /**
     * Realiza la validación de un RIF.
     *
     * @param string $tipo Tipo de documento de identidad
     * @param string $numero Número de documento de identidad
     * @return boolean Verdadero si el número de documento es válido
     */
    function validarRIF($tipo, $numero, $tipoAfiliacion)
    {

        if ($tipoAfiliacion != 'i') {
            if ($tipo == 'P' || $tipo == 'p' || $tipo == 'E' || $tipo == 'e') {
                return true;
            }
            if (($tipo == 'V' || $tipo == 'v') && strlen($numero) < 9) {
                return true;
            }
        }
        $digito = substr($numero, -1);

        $rif = substr($numero, 0, -1);

        $res = $this->invoca(
            'INVALRIF',
            array(
                'req' => array(
                    'RNRORIF' => str_pad($rif, 8, '0', STR_PAD_LEFT),
                    'RDIGITO' => $digito,
                    'RSTATUS' => $tipo
                )
            )
        );

        return !isset($res['INVALRIFReturn']['error']);
    }


    /**
     * Realiza la validación de una tarjeta de crédito.
     *
     * @param string $datosProducto Arreglo con los datos de la tarjeta
     * @return array Respuesta del servicio NIB090501
     */
    function validarBIN($datosProducto)
    {

        return $res = $this->invoca(
            'NIB090501',
            array(
                'req' => $datosProducto
            )
        );

        //return !isset($res['NIB090501Return']['error']);
    }


    /**
     * Realiza la validacón de un producto del usuario.
     *
     * @param array $datosProducto Arreglo con la petición para el servicio NIBPF3010
     * @return array Respuesta del servicio NIBPF3010
     */
    function validarProducto($datosProducto)
    {

        return $this->invoca(
            'NIBPF3010',
            array(
                'req' => $datosProducto
            )
        );

        return !isset($res['NIBPF3010Return']['error']);
    }


    /**
     * Completa el numero de cuenta bancoactivo.
     *
     * @param string $nro_cuenta Número de cuenta a consultar
     * @return array Respuesta del servicio EDL890101
     */
    function completaCuenta($nro_cuenta)
    {

        if (strlen($nro_cuenta) != 10) {
            return null;
        }

        return $this->invoca(
            'EDL890101',
            array(
                'req' => array(
                    'e01ACC010' => $nro_cuenta
                )
            )
        );
    }             
    
    
    /**
     * Rellena la información de cabecera seguimiento de gestión a una transacción pendiente
     *
     * @param array $transaccion_pendiente La transacción pendiente en cuestión, tal cual
     * como la devuelve cakephp
     */
    function seguimientoGestionCabecera(&$transaccion_pendiente)
    {
        if (isset($transaccion_pendiente['TransaccionPendiente'])) {
            $aux = &$transaccion_pendiente['TransaccionPendiente'];
        } else {
            $aux = &$transaccion_pendiente;
        }

        $res = $this->invoca(
            'IBPINFDAT',
            array(
                'req' => array(
                    'IBPUSR' => $aux['tipo_rif'] . desencriptar($aux['rif']),
                    'IBPPID' => $aux['lote']
                )
            )
        );        

        if (isset($res['IBPINFDATReturn']['IBPINFDAT'])) {
            
            $x = $res['IBPINFDATReturn']['IBPINFDAT'];            
            
            $aux['monto_procesado'] = $x['IBPMTE'];  
            $aux['monto_pendiente'] = $x['IBPMTP'];
            $aux['monto_rechazos'] = $x['IBPMTR'];
            $aux['cantidad_registros'] = $x['IBPNUM'];            
            
            if($x['IBPSTS']=='R'){
               $aux['descripcion_estatus'] = 'Rechazado';
            }elseif(trim($x['IBPSTS'])==''){
               $aux['descripcion_estatus'] = 'Pendiente por Procesar';
            }elseif ($x['IBPMTR'] != 0.00) {                
               $aux['descripcion_estatus'] = 'Procesado con Rechazos';                            
            }elseif ($x['IBPMTP'] != 0.00) {
               $aux['descripcion_estatus'] = 'Procesado Parcialmente';
            }else{
               $aux['descripcion_estatus'] = 'Procesado'; 
            }
        }

        if (!isset($aux['descripcion_estatus'])) {

            $aux['descripcion_estatus'] = TransaccionPendiente::$estatus_transaccion[$aux['estatus']];

            // Si esta rechazada por un usuario aprobador busca en el log el nombre del usuario que realizo la accion
            if ($aux['estatus'] == 'ra') {

                $this->Usuario = ClassRegistry::init('Usuario');

                $usuario = $this->Usuario->find(
                    'first',
                    array(
                        'fields' => array('nombre'),
                        'conditions' => array(
                            'tipo_rif' => $aux['tipo_rif'],
                            'rif' => $aux['rif'],
                            "id = (select usuario_id from logs where referencia = '" . encriptar($aux['id']) . "' and transaccion_id=55)"
                        )
                    )
                );

                if ($usuario) {
                    $aux['descripcion_estatus'] .= ' ' . desencriptar($usuario['Usuario']['nombre']);
                }
            }

        }
        
    }
    
    
    /**
     * Rellena la información de seguimiento de gestión a una transacción pendiente
     *
     * @param array $transaccion_pendiente La transacción pendiente en cuestión, tal cual
     * como la devuelve cakephp
     */
    function seguimientoGestion(&$transaccion_pendiente)
    {
        if (isset($transaccion_pendiente['TransaccionPendiente'])) {
            $aux = &$transaccion_pendiente['TransaccionPendiente'];
        } else {
            $aux = &$transaccion_pendiente;
        }

        $res = $this->invoca(
            'INPYRLSJV2',
            array(
                'req' => array(
                    'PYRUSR' => $aux['tipo_rif'] . desencriptar($aux['rif']),
                    'PYRPID' => $aux['lote'],
                    'PYROPC' => '04'
                )
            )
        );

        $monto_procesado = 0;

        if (isset($res['INPYRLSJV2Return']['INPYRLSJV']['INPYRLSJV'])) {
            $x = $res['INPYRLSJV2Return']['INPYRLSJV']['INPYRLSJV'];
            normaliza($x);

            $i = 0;
            $contador_rechazados = 0;
            $contador_aplicados = 0;
            $contador_pendientes = 0;
            $monto_procesado = 0;
            $monto_rechazado = 0;
            $monto_pendiente = 0;
            //Variables para registros banco activo
            $contador_rechazados_p = 0;
            $contador_aplicados_p = 0;
            $contador_pendientes_p = 0;
            $contador_pt = 0;
            $monto_procesado_p = 0;
            $monto_rechazado_p = 0;
            $monto_pendiente_p = 0;
            $monto_pt = 0;
            //Variables para registros otros bancos
            $contador_rechazados_ob = 0;
            $contador_aplicados_ob = 0;
            $contador_pendientes_ob = 0;
            $contador_obt = 0;
            $monto_procesado_ob = 0;
            $monto_rechazado_ob = 0;
            $monto_pendiente_ob = 0;
            $monto_obt = 0;
            $motivoError = "";
            
            $procesada = true;
            $con_error = false;

            foreach ($x as $j) {
                $sumar_monto = false;

                $resp = "En Proceso";
                $resp_aux = '';

                if (trim($j['PYRTYP']) == "C") {
                    if ($j['PYRSTS'] == "P") {
                        $resp = "Aplicado";  
                        $motivoError = $j['PYRERD'];                
                        $monto_procesado += $j['PYRAMT'];
                        $con_error = false;
                        $contador_aplicados++;
                        if (trim($j['PYRIBKID']) == "0171") {
                            $monto_procesado_p += $j['PYRAMT'];
                            $contador_aplicados_p++;
                            $monto_pt += $j['PYRAMT'];
                            $contador_pt++;
                        }else{
                            $monto_procesado_ob += $j['PYRAMT'];
                            $contador_aplicados_ob++;
                            $monto_obt += $j['PYRAMT'];
                            $contador_obt++;
                        }
                    } elseif ($j['PYRSTS'] == "R") {
                        $resp = "Rechazado";
                        $motivoError = $j['PYRERD'];
                        $monto_rechazado += $j['PYRAMT'];
                        $resp_aux = (strlen(trim($j['PYRERD'])) > 3) ? trim($j['PYRERD']) : ((strlen(trim($j['PYRERD'])) == 3) ? trim($j['PYRERD']) . ' ' . _m('EAN_' . trim($j['PYRERD'])) : $resp);
                        $con_error = true;
                        $contador_rechazados++;
                        if (trim($j['PYRIBKID']) == "0171") {
                            $monto_rechazado_p += $j['PYRAMT'];
                            $contador_rechazados_p++;
                            $monto_pt += $j['PYRAMT'];
                            $contador_pt++;
                        }else{
                            $monto_rechazado_ob += $j['PYRAMT'];
                            $contador_rechazados_ob++;
                            $monto_obt += $j['PYRAMT'];
                            $contador_obt++;
                        }
                    } else {
                        $procesada = false;
                        $motivoError = $j['PYRERD'];
                        $monto_pendiente += $j['PYRAMT'];
                        $contador_pendientes++;
                        if (trim($j['PYRIBKID']) == "0171") {
                            $monto_pendiente_p += $j['PYRAMT'];
                            $contador_pendientes_p++;
                            $monto_pt += $j['PYRAMT'];
                            $contador_pt++;
                        }else{
                            $monto_pendiente_ob += $j['PYRAMT'];
                            $contador_pendientes_ob++;
                            $monto_obt += $j['PYRAMT'];
                            $contador_obt++;
                        }
                    }

                } else {
                    if (trim($j['PYRTYP']) == "A") {
                        if ($j['PYRSTS'] == "P" && strlen(trim($j['PYRERD'])) == 0) {
                            $resp = "En Proceso";
                            $motivoError = $j['PYRERD'];
                            $monto_pendiente += $j['PYRAMT'];
                            $procesada = false;
                            $con_error = false;
                            $contador_pendientes++;
                            if (trim($j['PYRIBKID']) == "0171") {
                                $monto_pendiente_p += $j['PYRAMT'];
                                $contador_pendientes_p++;
                                $monto_pt += $j['PYRAMT'];
                                $contador_pt++;
                            }else{
                                $monto_pendiente_ob += $j['PYRAMT'];
                                $contador_pendientes_ob++;
                                $monto_obt += $j['PYRAMT'];
                                $contador_obt++;
                            }
                        } elseif (($j['PYRSTS'] == "P" || $j['PYRSTS'] == "R") && strlen(trim($j['PYRERD'])) != 0) {
                            $resp = "Rechazado";
                            $motivoError = $j['PYRERD'];
                            $monto_rechazado += $j['PYRAMT'];
                            $resp_aux = (strlen(trim($j['PYRERD'])) > 3) ? trim($j['PYRERD']) : (strlen(trim($j['PYRERD'])) == 3) ? trim($j['PYRERD']) . ' ' . _m('EAN_' . trim($j['PYRERD'])) : $resp;
                            $con_error = true;
                            $contador_rechazados++;
                            if (trim($j['PYRIBKID']) == "0171") {
                                $monto_rechazado_p += $j['PYRAMT'];
                                $contador_rechazados_p++;
                                $monto_pt += $j['PYRAMT'];
                                $contador_pt++;
                            }else{
                                $monto_rechazado_ob += $j['PYRAMT'];
                                $contador_rechazados_ob++;
                                $monto_obt += $j['PYRAMT'];
                                $contador_obt++;
                            }
                        } elseif ($j['PYRSTS'] == "T") {
                            $resp = "Aplicado"; 
                            $motivoError = $j['PYRERD'];                          
                            $monto_procesado += $j['PYRAMT'];
                            $con_error = false;
                            $contador_aplicados++;
                            if (trim($j['PYRIBKID']) == "0171") {
                                $monto_procesado_p += $j['PYRAMT'];
                                $contador_aplicados_p++;
                                $monto_pt += $j['PYRAMT'];
                                $contador_pt++;
                            }else{
                                $monto_procesado_ob += $j['PYRAMT'];
                                $contador_aplicados_ob++;
                                $monto_obt += $j['PYRAMT'];
                                $contador_obt++;
                            }
                        } elseif ($j['PYRSTS'] == "X") {
                            $resp = "Rechazado";
                            $motivoError = $j['PYRERD'];
                            $monto_rechazado += $j['PYRAMT'];
                            $resp_aux = (strlen(trim($j['PYRERD'])) > 3) ? trim($j['PYRERD']) : (strlen(trim($j['PYRERD'])) == 3) ? trim($j['PYRERD']) . ' ' . _m('EAN_' . trim($j['PYRERD'])) : $resp;
                            $con_error = true;
                            $contador_rechazados++;
                            if (trim($j['PYRIBKID']) == "0171") {
                                $monto_rechazado_p += $j['PYRAMT'];
                                $contador_rechazados_p++;
                                $monto_pt += $j['PYRAMT'];
                                $contador_pt++;
                            }else{
                                $monto_rechazado_ob += $j['PYRAMT'];
                                $contador_rechazados_ob++;
                                $monto_obt += $j['PYRAMT'];
                                $contador_obt++;
                            }
                        } else {
                            $procesada = false;
                            $motivoError = $j['PYRERD'];
                            $monto_pendiente += $j['PYRAMT'];
                            $contador_pendientes++;
                            if (trim($j['PYRIBKID']) == "0171") {
                                $monto_pendiente_p += $j['PYRAMT'];
                                $contador_pendientes_p++;
                                $monto_pt += $j['PYRAMT'];
                                $contador_pt++;
                            }else{
                                $monto_pendiente_ob += $j['PYRAMT'];
                                $contador_pendientes_ob++;
                                $monto_obt += $j['PYRAMT'];
                                $contador_obt++;
                            }
                        }

                    }
                }                
                                
                
                if ($con_error) {
                    $transaccion_pendiente['Beneficiarios'][$i]['rechazado'] = true;
                }

                $transaccion_pendiente['Beneficiarios'][$i]['resultado'] = $resp;
                $transaccion_pendiente['Beneficiarios'][$i]['respuestaWS'] = $resp_aux;
                $transaccion_pendiente['Beneficiarios'][$i]['motivo_error'] = $motivoError;

                $i++;
            }
            
            $aux['cantidad_aplicados'] = $contador_aplicados;
            $aux['cantidad_pendientes'] = $contador_pendientes;
            $aux['cantidad_rechazados'] = $contador_rechazados;
            $aux['monto_aplicados'] = $monto_procesado;
            $aux['monto_pendientes'] = $monto_pendiente;
            $aux['monto_rechazados'] = $monto_rechazado;
            
            $aux['cantidad_aplicados_p'] = $contador_aplicados_p;
            $aux['cantidad_pendientes_p'] = $contador_pendientes_p;
            $aux['cantidad_rechazados_p'] = $contador_rechazados_p;
            $aux['cantidad_pt'] = $contador_pt;
            $aux['monto_aplicados_p'] = $monto_procesado_p;
            $aux['monto_pendientes_p'] = $monto_pendiente_p;
            $aux['monto_rechazados_p'] = $monto_rechazado_p;
            $aux['monto_pt'] = $monto_pt;
                        
            $aux['cantidad_aplicados_ob'] = $contador_aplicados_ob;
            $aux['cantidad_pendientes_ob'] = $contador_pendientes_ob;
            $aux['cantidad_rechazados_ob'] = $contador_rechazados_ob;
            $aux['cantidad_obt'] = $contador_obt;
            $aux['monto_aplicados_ob'] = $monto_procesado_ob;
            $aux['monto_pendientes_ob'] = $monto_pendiente_ob;
            $aux['monto_rechazados_ob'] = $monto_rechazado_ob;
            $aux['monto_obt'] = $monto_obt;
            
            
                
            if ($contador_rechazados>0) {
                if ($contador_rechazados == $i) {
                    $aux['descripcion_estatus'] = 'Rechazado';
                } else {
                    $aux['descripcion_estatus'] = 'Procesado con Rechazos';
                }
            }elseif($contador_aplicados==0){  
                $aux['descripcion_estatus'] = 'Pendiente por Procesar';
            } else if($contador_pendientes>0) {
                $aux['descripcion_estatus'] = 'Procesado Parcialmente';
            } else {
                $aux['descripcion_estatus'] = 'Procesado';
            }           
        }
        
        if (!isset($aux['descripcion_estatus'])) {

            $aux['descripcion_estatus'] = TransaccionPendiente::$estatus_transaccion[$aux['estatus']];

            // Si esta rechazada por un usuario aprobador busca en el log el nombre del usuario que realizo la accion
            if ($aux['estatus'] == 'ra') {

                $this->Usuario = ClassRegistry::init('Usuario');

                $usuario = $this->Usuario->find(
                    'first',
                    array(
                        'fields' => array('nombre'),
                        'conditions' => array(
                            'tipo_rif' => $aux['tipo_rif'],
                            'rif' => $aux['rif'],
                            "id = (select usuario_id from logs where referencia = '" . encriptar($aux['id']) . "' and transaccion_id=55)"
                        )
                    )
                );

                if ($usuario) {
                    $aux['descripcion_estatus'] .= ' ' . desencriptar($usuario['Usuario']['nombre']);
                }
            }

        }

        $aux['monto_procesado'] = $monto_procesado;
    }


    /**
     * Ejecuta un pago de nomina o proveedores
     *
     * @param array $dataPago lote, tipo, cuentaDebitar, monto_total, fecha_valor=>day,month,year
     * @param array $beneficiarios Arreglo con los datos de los beneficiarios
     * @return boolean Verdadero si se ejecutó correctamente
     */
    function ejecutarPagoCash($dataPago, $beneficiarios)
    {
        if (!$this->Session->check('usuarios.datos') || !count($beneficiarios)) {
            return false;
        }
        
        // setlocale(LC_ALL,'es_VE.utf8'); PARA LINUX
        setlocale(LC_ALL, 'es_VE.UTF-8'); // PARA LINUX

        $usuario = $this->Session->read('usuarios.datos');
        
        
        if($dataPago['tipo'] =='m'){
            $dataPago['tipo'] ='n';
        }

        if($dataPago['tipo'] =='x'){
            $dataPago['tipo'] ='p';
        }

        if($dataPago['tipo'] =='mxm'){
            $dataPago['tipo'] ='n';
        }
        if($dataPago['tipo'] =='xxm'){
            $dataPago['tipo'] ='p';
        } 

        if($dataPago['tipo'] =='nxm'){
            $dataPago['tipo'] ='n';
        }
        if($dataPago['tipo'] =='xxm'){
            $dataPago['tipo'] ='p';
        }

        if($dataPago['tipo'] =='vxm'){
            $dataPago['tipo'] ='p';
        }

        // Verifica la empresa y el monto del pago
        $verifica = $this->invoca(
            'INPYRLSJV',
            array(
                'req' => array(
                    'PYRUSR' => $usuario['tipo_rif'] . desencriptar($usuario['rif']),
                    'PYRPID' => ltrim($dataPago['lote'], "0"),
                    'PYRACC' => substr($dataPago['cuentaDebitar'], -10),
                    'PYRAMT' => $dataPago['monto_total'],
                    'PYRNUM' => count($beneficiarios),
                    'PYRVDD' => str_pad($dataPago['fecha_valor']['day'], 2, '0', STR_PAD_LEFT),
                    'PYRVDM' => str_pad($dataPago['fecha_valor']['month'], 2, '0', STR_PAD_LEFT),
                    'PYRVDY' => substr($dataPago['fecha_valor']['year'], 2),
                    'PYRMEM1' => $dataPago['tipo'] == 'n' ? 'PAGO NOMINA ' . ltrim($dataPago['lote'], "0") : 'ORDEN DE PAGO ' . ltrim($dataPago['lote'], "0"),
                    'PYROPC' => '00'
                )
            )
        );

        if (isset($verifica['INPYRLSJVReturn']['error']['ERRNUM'])) {
            $this->log('Error verificando pago LOTE: ' . ltrim($dataPago['lote'], "0") . ' - (' . $verifica['INPYRLSJVReturn']['error']['ERNU01'] . ') ' . $verifica['INPYRLSJVReturn']['error']['ERDS01'], LOG_ERR);
            $this->error = $verifica['INPYRLSJVReturn']['error']['ERNU01'];
            $this->descripcion_error = $verifica['INPYRLSJVReturn']['error']['ERDS01'];
            return false;
        }

        // Busca la info de los bancos
        $bancos = $this->buscarBancos2();
        $i = 0;
        // Beneficiarios del pago
        foreach ($beneficiarios as $x) {
            $b = desencriptarCampos($x['AfiliacionCash'],
                array('nombre', 'cedula', 'numero_cuenta', 'email', 'celular'));

              $moneda=$b['moneda'];

            

            $banco_bene = $bancos[substr($b['numero_cuenta'], 0, 4)];

            $verifica = $this->invoca(
                'INPYRLSJV',
                array(
                    'req' => array(
                        'PYRUSR' => $usuario['tipo_rif'] . desencriptar($usuario['rif']),
                        'PYRPID' => ltrim($dataPago['lote'], "0"),
                        'PYROPC' => '01',
                        'PYRTYP' => substr($b['numero_cuenta'], 0, 4) == '0171' ? 'C' : 'A',
                        'PYRNUM' => ++$i,
                        'PYRBACC' => $b['numero_cuenta'],
                        'PYRBBNK' => str_pad(substr($b['numero_cuenta'], 0, 4), 9, 0, STR_PAD_LEFT),
                        'PYRBBKN' => substr($banco_bene['BNKNME'], 0, 30),
                        'PYRIBKID' => substr($b['numero_cuenta'], 0, 4),
                        'PYRIBKNM' => substr($banco_bene['BNKNME'], 0, 30),
                        'PYRIBKAD' => substr($banco_bene['BNKAD1'], 0, 30),
                        'PYRIBKA2' => substr($banco_bene['BNKCTY'], 0, 30),
                        'PYRBNAM' => substr($b['nombre'], 0, 30),
                        'PYRBBKA2' => '',
                        'PYRAMT' => formatea_monto_toWs($b['monto']),
                        'PYRCCY' => 'VEB',
                        'PYRMEM1' => (substr($b['numero_cuenta'], 0, 4) == '0171') ? '' : '0227-Terceros Otros Bancos',
                        'PYRMEM2' => $dataPago['tipo'] == 'n' ? 'PAGO NOMINA ' . ltrim($dataPago['lote'], "0") : 'ORDEN DE PAGO ' . ltrim($dataPago['lote'], "0"),
                        'PYRBAD1' => $b['tipo_cedula'] . str_pad($b['cedula'], 9, 0, STR_PAD_LEFT),
                    )
                )
            );

            if (isset($verifica['INPYRLSJVReturn']['error']['ERRNUM'])) {
                $this->log('Error verificando pago LOTE: ' . ltrim($dataPago['lote'], "0") . ' - (' . $verifica['INPYRLSJVReturn']['error']['ERNU01'] . ') ' . $verifica['INPYRLSJVReturn']['error']['ERDS01'], LOG_ERR);
                $this->error = $verifica['INPYRLSJVReturn']['error']['ERNU01'];
                $this->descripcion_error = $verifica['INPYRLSJVReturn']['error']['ERDS01'];
                return false;
            }
        }

        // Ejecucion del pago
        $ejecucion = $this->invoca(
            'INPYRLSJV',
            array(
                'req' => array(
                    'PYRUSR' => $usuario['tipo_rif'] . desencriptar($usuario['rif']),
                    'PYRPID' => ltrim($dataPago['lote'], "0"),
                    'PYROPC' => '02',
                    'PYRTYP' => strtoupper($dataPago['tipo']),
                    'PYRMEM1' => $dataPago['tipo'] == 'n' ? 'PAGO NOMINA ' . ltrim($dataPago['lote'], "0") : 'ORDEN DE PAGO ' . ltrim($dataPago['lote'], "0"),
                    'PYRMEM2' => ltrim($dataPago['lote'], "0"),
                    'PYRACC' => substr($dataPago['cuentaDebitar'], -10),
                    'PYRAMT' => $dataPago['monto_total'],
                    'PYRNUM' => count($beneficiarios),
                    'PYRVDD' => str_pad($dataPago['fecha_valor']['day'], 2, '0', STR_PAD_LEFT),
                    'PYRVDM' => str_pad($dataPago['fecha_valor']['month'], 2, '0', STR_PAD_LEFT),
                    'PYRVDY' => substr($dataPago['fecha_valor']['year'], 2)
                )
            )
        );


        if (isset($ejecucion['INPYRLSJVReturn']['error']['ERRNUM'])) {
            $this->log('Error verificando pago LOTE: ' . ltrim($dataPago['lote'], "0") . ' - (' . $ejecucion['INPYRLSJVReturn']['error']['ERNU01'] . ') ' . $ejecucion['INPYRLSJVReturn']['error']['ERDS01'], LOG_ERR);
            $this->error = $ejecucion['INPYRLSJVReturn']['error']['ERNU01'];
            $this->descripcion_error = $ejecucion['INPYRLSJVReturn']['error']['ERDS01'];
            return false;
        } else {
            // Si todo sale bien, enviar correos de notificación al master

            if ($usuario['rol_juridico_id'] == 2) {
                $correo_master = desencriptar($usuario['email_principal']);
                $celular_master = desencriptar($usuario['celular']);
            } else {
                $this->Usuario = ClassRegistry::init('Usuario');

                $datos_master = $this->Usuario->find(
                    'first',
                    array(
                        'fields' => array('email_principal', 'celular'),
                        'conditions' => array(
                            'tipo_rif' => $usuario['tipo_rif'],
                            'rif' => $usuario['rif'],
                            'rol_juridico_id' => 1
                        )
                    )
                );

                if ($datos_master) {
                    $correo_master = desencriptar($datos_master['Usuario']['email_principal']);
                    $celular_master = desencriptar($datos_master['Usuario']['celular']);
                }
            }

            if (isset($correo_master)) {
                $this->Email->reset();
                $this->Email->smtpOptions = Configure::read('email.smtp');
                $this->Email->delivery = 'smtp';
                $this->Email->from = Configure::read('email.from');
                $this->Email->to = $correo_master;

                if ($dataPago['tipo'] == 'p') {
                    $this->Email->subject = 'Notificación Orden de Pago';
                } else {
                    $this->Email->subject = 'Notificación de Pago de Nómina';
                }

                $this->Email->sendAs = 'html';
                $this->Email->template = 'pago_cash_master';

                $this->controller->set(compact('usuario', 'dataPago', 'moneda'));
                $res = $this->Email->send();
            }

            if ($dataPago['tipo'] == 'p') {
                $codigo_sms = 46;
            } else {
                $codigo_sms = 45;
            }

            // Enviar por sms la notificación
            if (isset($celular_master)) {
                $this->Sms->enviar(
                    array(
                        'numcel' => $celular_master,
                        'canal1' => $codigo_sms,
                        'tipcre' => 'IB',
                        'refnum' => ltrim($dataPago['lote'], "0"),
                        'cuenta' => substr($dataPago['cuentaDebitar'], -6),
                        'monto' => formatea_monto_toWs($dataPago['monto_total'])
                    )
                );
            }

            if ($dataPago['tipo'] == 'p') {
                $this->DetallePagoProveedor = ClassRegistry::init('DetallePagoProveedor');

                // Si es un pago a proveedores, enviar correo a cada uno
                foreach ($beneficiarios as $beneficiario) {
                    if (isset($beneficiario['AfiliacionCash']['detalle'])) {
                        $detalles = $beneficiario['AfiliacionCash']['detalle'];
                    } else {
                        $detalles = $this->DetallePagoProveedor->find(
                            'all',
                            array(
                                'conditions' => array(
                                    'DetallePagoProveedor.beneficiario_id' => $beneficiario['AfiliacionCash']['id']
                                )
                            )
                        );
                    }

                    $this->Email->reset();
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = desencriptar($beneficiario['AfiliacionCash']['email']);
                    $this->Email->subject = 'Notificación de Pago';
                    $this->Email->sendAs = 'html';
                    $this->Email->template = 'pago_cash_proveedor';

                    $this->controller->set(compact('usuario', 'dataPago', 'beneficiario', 'detalles'));
                    $res = $this->Email->send();
                }
            }
        }

        return true;
    }

    /**************PAGO EXPRESS START********************/

    /**
     * Ejecuta un pago de nomina o proveedores
     *
     * @param array $dataPago lote, tipo, cuentaDebitar, monto_total, fecha_valor=>day,month,year
     * @param array $beneficiarios Arreglo con los datos de los beneficiarios
     * @return boolean Verdadero si se ejecutó correctamente
     */
    function ejecutarPagoCashExpress($dataPago, $beneficiarios)
    {
        if (!$this->Session->check('usuarios.datos') || !count($beneficiarios)) {
            return false;
        }

        // setlocale(LC_ALL,'es_VE.utf8'); PARA LINUX
        setlocale(LC_ALL, 'es_VE.UTF-8'); // PARA LINUX

        $usuario = $this->Session->read('usuarios.datos');
        
        
        if($dataPago['tipo'] =='m'){
            $dataPago['tipo'] ='n';
        }

        if($dataPago['tipo'] =='x'){
            $dataPago['tipo'] ='p';
        }

        if($dataPago['tipo'] =='p'){
            $dataPago['tipo'] ='p';
        }

        if($dataPago['tipo'] =='mxm'){
            $dataPago['tipo'] ='n';
        }
        if($dataPago['tipo'] =='xxm'){
            $dataPago['tipo'] ='p';
        } 

        if($dataPago['tipo'] =='nxm'){
            $dataPago['tipo'] ='n';
        }
        if($dataPago['tipo'] =='xxm'){
            $dataPago['tipo'] ='p';
        }

        if($dataPago['tipo'] =='vxm'){
            $dataPago['tipo'] ='p';
        }

        // Verifica la empresa y el monto del pago
        $verifica = $this->invoca(
            'INPYRLSJV',
            array(
                'req' => array(
                    'PYRUSR' => $usuario['tipo_rif'] . desencriptar($usuario['rif']),
                    'PYRPID' => ltrim($dataPago['lote'], "0"),
                    'PYRACC' => substr($dataPago['cuentaDebitar'], -10),
                    'PYRAMT' => $dataPago['monto_total'],
                    'PYRNUM' => count($beneficiarios),
                    'PYRVDD' => str_pad($dataPago['fecha_valor']['day'], 2, '0', STR_PAD_LEFT),
                    'PYRVDM' => str_pad($dataPago['fecha_valor']['month'], 2, '0', STR_PAD_LEFT),
                    'PYRVDY' => substr($dataPago['fecha_valor']['year'], 2),
                    'PYRMEM1' => $dataPago['tipo'] == 'n' ? 'PAGO NOMINA ' . ltrim($dataPago['lote'], "0") : 'ORDEN DE PAGO ' . ltrim($dataPago['lote'], "0"),
                    'PYROPC' => '00'
                )
            )
        );

        if (isset($verifica['INPYRLSJVReturn']['error']['ERRNUM'])) {
            $this->log('Error verificando pago LOTE: ' . ltrim($dataPago['lote'], "0") . ' - (' . $verifica['INPYRLSJVReturn']['error']['ERNU01'] . ') ' . $verifica['INPYRLSJVReturn']['error']['ERDS01'], LOG_ERR);
            $this->error = $verifica['INPYRLSJVReturn']['error']['ERNU01'];
            $this->descripcion_error = $verifica['INPYRLSJVReturn']['error']['ERDS01'];
            return false;
        }

        // Busca la info de los bancos
        $bancos = $this->buscarBancos2();
        $i = 0;
        
        // Beneficiarios del pago
        foreach ($beneficiarios as $x) {   

            $banco_bene = $bancos[substr($x['numero_cuenta'], 0, 4)];

            $verifica = $this->invoca(
                'INPYRLSJV',
                array(
                    'req' => array(
                        'PYRUSR' => $usuario['tipo_rif'] . desencriptar($usuario['rif']),
                        'PYRPID' => ltrim($dataPago['lote'], "0"),
                        'PYROPC' => '01',
                        'PYRTYP' => substr($x['numero_cuenta'], 0, 4) == '0171' ? 'C' : 'A',
                        'PYRNUM' => ++$i,
                        'PYRBACC' => $x['numero_cuenta'],
                        'PYRBBNK' => str_pad(substr($x['numero_cuenta'], 0, 4), 9, 0, STR_PAD_LEFT),
                        'PYRBBKN' => substr($banco_bene['BNKNME'], 0, 30),
                        'PYRIBKID' => substr($x['numero_cuenta'], 0, 4),
                        'PYRIBKNM' => substr($banco_bene['BNKNME'], 0, 30),
                        'PYRIBKAD' => substr($banco_bene['BNKAD1'], 0, 30),
                        'PYRIBKA2' => substr($banco_bene['BNKCTY'], 0, 30),
                        'PYRBNAM' => substr($x['nombre'], 0, 30),
                        'PYRBBKA2' => '',
                        'PYRAMT' => formatea_monto_toWs($x['monto']),
                        'PYRCCY' => 'VEB',
                        'PYRMEM1' => (substr($x['numero_cuenta'], 0, 4) == '0171') ? '' : '0227-Terceros Otros Bancos',
                        'PYRMEM2' => $dataPago['tipo'] == 'n' ? 'PAGO NOMINA ' . ltrim($dataPago['lote'], "0") : 'ORDEN DE PAGO ' . ltrim($dataPago['lote'], "0"),
                        'PYRBAD1' => $x['tipo_cedula'] . str_pad($x['cedula'], 9, 0, STR_PAD_LEFT),
                    )
                )
            );

            if (isset($verifica['INPYRLSJVReturn']['error']['ERRNUM'])) {
                $this->log('Error verificando pago LOTE: ' . ltrim($dataPago['lote'], "0") . ' - (' . $verifica['INPYRLSJVReturn']['error']['ERNU01'] . ') ' . $verifica['INPYRLSJVReturn']['error']['ERDS01'], LOG_ERR);
                $this->error = $verifica['INPYRLSJVReturn']['error']['ERNU01'];
                $this->descripcion_error = $verifica['INPYRLSJVReturn']['error']['ERDS01'];
                return false;
            }
        }

        // Ejecucion del pago
        $ejecucion = $this->invoca(
            'INPYRLSJV',
            array(
                'req' => array(
                    'PYRUSR' => $usuario['tipo_rif'] . desencriptar($usuario['rif']),
                    'PYRPID' => ltrim($dataPago['lote'], "0"),
                    'PYROPC' => '02',
                    'PYRTYP' => strtoupper($dataPago['tipo']),
                    'PYRMEM1' => $dataPago['tipo'] == 'n' ? 'PAGO NOMINA ' . ltrim($dataPago['lote'], "0") : 'ORDEN DE PAGO ' . ltrim($dataPago['lote'], "0"),
                    'PYRMEM2' => ltrim($dataPago['lote'], "0"),
                    'PYRACC' => substr($dataPago['cuentaDebitar'], -10),
                    'PYRAMT' => $dataPago['monto_total'],
                    'PYRNUM' => count($beneficiarios),
                    'PYRVDD' => str_pad($dataPago['fecha_valor']['day'], 2, '0', STR_PAD_LEFT),
                    'PYRVDM' => str_pad($dataPago['fecha_valor']['month'], 2, '0', STR_PAD_LEFT),
                    'PYRVDY' => substr($dataPago['fecha_valor']['year'], 2)
                )
            )
        );

        if (isset($ejecucion['INPYRLSJVReturn']['error']['ERRNUM'])) {
            $this->log('Error verificando pago LOTE: ' . ltrim($dataPago['lote'], "0") . ' - (' . $ejecucion['INPYRLSJVReturn']['error']['ERNU01'] . ') ' . $ejecucion['INPYRLSJVReturn']['error']['ERDS01'], LOG_ERR);
            $this->error = $ejecucion['INPYRLSJVReturn']['error']['ERNU01'];
            $this->descripcion_error = $ejecucion['INPYRLSJVReturn']['error']['ERDS01'];
            return false;
        } else {
            // Si todo sale bien, enviar correos de notificación al master

            if ($usuario['rol_juridico_id'] == 2) {
                $correo_master = desencriptar($usuario['email_principal']);
                $celular_master = desencriptar($usuario['celular']);
            } else {
                $this->Usuario = ClassRegistry::init('Usuario');

                $datos_master = $this->Usuario->find(
                    'first',
                    array(
                        'fields' => array('email_principal', 'celular'),
                        'conditions' => array(
                            'tipo_rif' => $usuario['tipo_rif'],
                            'rif' => $usuario['rif'],
                            'rol_juridico_id' => 1
                        )
                    )
                );

                if ($datos_master) {
                    $correo_master = desencriptar($datos_master['Usuario']['email_principal']);
                    $celular_master = desencriptar($datos_master['Usuario']['celular']);
                }
            }

            if (isset($correo_master)) {
                $this->Email->reset();
                $this->Email->smtpOptions = Configure::read('email.smtp');
                $this->Email->delivery = 'smtp';
                $this->Email->from = Configure::read('email.from');
                $this->Email->to = $correo_master;

                if ($dataPago['tipo'] == 'p') {
                    $this->Email->subject = 'Notificación Orden de Pago';
                } else {
                    $this->Email->subject = 'Notificación de Pago de Nómina';
                }

                $this->Email->sendAs = 'html';
                $this->Email->template = 'pago_cash_master_express';

                $this->controller->set(compact('usuario', 'dataPago', 'moneda'));
                $res = $this->Email->send();
            }

            if ($dataPago['tipo'] == 'p') {
                $codigo_sms = 46;
            } else {
                $codigo_sms = 45;
            }

            // Enviar por sms la notificación
            if (isset($celular_master)) {
                $this->Sms->enviar(
                    array(
                        'numcel' => $celular_master,
                        'canal1' => $codigo_sms,
                        'tipcre' => 'IB',
                        'refnum' => ltrim($dataPago['lote'], "0"),
                        'cuenta' => substr($dataPago['cuentaDebitar'], -6),
                        'monto' => formatea_monto_toWs($dataPago['monto_total'])
                    )
                );
            }

            if ($dataPago['tipo'] == 'p') {
                $this->DetallePagoProveedor = ClassRegistry::init('DetallePagoProveedor');

                // Si es un pago a proveedores, enviar correo a cada uno
                foreach ($beneficiarios as $beneficiario) {
                    if (isset($beneficiario['concepto'])) {
                        $detalles = $beneficiario['concepto'];
                    } else {
                        $detalles = $this->DetallePagoProveedor->find(
                            'all',
                            array(
                                'conditions' => array(
                                    'DetallePagoProveedor.beneficiario_id' => $beneficiario['AfiliacionCash']['id']
                                )
                            )
                        );
                    }

                    $this->Email->reset();
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = $beneficiario['email'];
                    $this->Email->subject = 'Notificación de Pago';
                    $this->Email->sendAs = 'html';
                    $this->Email->template = 'pago_cash_proveedor_express';

                    $this->controller->set(compact('usuario', 'dataPago', 'beneficiario', 'detalles'));
                    $res = $this->Email->send();
                }
            }
        }

        return true;
    }

    /**************PAGO EXPRESS END********************/


    /**
     * Busca los sellos existentes y selecciona los primeros 12
     *
     * @return array
     */
    function buscarSellos()
    {

        $array_imagenes = array();

        $res = $this->invoca(
            'LISTADOSELLOS',
            array(
                'req' => array(
                    'canal' => Configure::read('canal.ib')
                )
            )
        );

        if ($res && !isset($res['LISTADOSELLOSReturn']['error']['ERRNUM'])) {
            if (count($res['LISTADOSELLOSReturn']['sellos']['sellos'])) {
                for ($i = 0; $i < 12; $i++) {
                    $array_imagenes[] = $res['LISTADOSELLOSReturn']['sellos']['sellos'][$i];
                }
            }
        } else {
            $descripcion = ($res && isset($res['LISTADOSELLOSReturn']['error']['ERRNUM'])) ? ' - ' . $res['LISTADOSELLOSReturn']['error']['ERNU01'] . ' ' . $res['LISTADOSELLOSReturn']['error']['ERDS01'] : '';
            $this->log('Error en la solicitud de sellos ' . $descripcion, LOG_ERR);
        }

        shuffle($array_imagenes);
        return $array_imagenes;
    }


    /**
     * Busca el limite para transacciones externas de un usuario Master en IB  anterior.
     *
     * @param varchar $tipo_rif Tipo de rif de la empresa
     * @param type $rif Número de rif de la empresa
     * @return boolean|string
     */
    function limiteMaster($tipo_rif, $rif)
    {

        // Busca el limite de otros bancos del usuario master
        $limite_master = $this->invoca(
            'PWDDSJV',
            array(
                'req' => array(
                    'PWDLGT' => 4,
                    'PWDCMP' => $tipo_rif . $rif
                )
            )
        );

        // Verifica si se ejecuto sin error y si el limite es mayor al del banco
        if (
            isset($limite_master['PWDDSJVReturn']['PWDDSJV']['PWDMAX']) &&
            $limite_master['PWDDSJVReturn']['PWDDSJV']['PWDMAX'] > _p('limite_externas_J')
        ) {
            return $limite_master['PWDDSJVReturn']['PWDDSJV']['PWDMAX'];
        }

        return false;
    }


    /**
     * Función que verifica si un usuario votó en el proceso de selección de la empresa auditora
     */

    function verificarVoto($id_cliente_IBS, $identificacion, $loginUsuario)
    {

        return $res = $this->invoca(
            'NIB2090PF',
            array(
                'req' => array(
                    'AUDCUN' => str_pad($id_cliente_IBS, 9, '0', STR_PAD_LEFT),
                    'AUDIDN' => $identificacion,
                    'AUDOPC' => '0001',
                    'AUDUSR' => $loginUsuario
                )
            )
        );
    }


    /**
     * Función que verifica si un usuario votó en el proceso de selección de la empresa auditora
     */

    function actualizarVoto($id_cliente_IBS, $identificacion, $loginUsuario, $nombreUsuario, $empresa, $id_empresa)
    {

        return $res = $this->invoca(
            'NIB2090PF',
            array(
                'req' => array(
                    'AUDCUN' => str_pad($id_cliente_IBS, 9, '0', STR_PAD_LEFT),
                    'AUDIDN' => $identificacion,
                    'AUDOPC' => '0002',
                    'AUDUSR' => $loginUsuario,
                    'AUDEMP' => $id_empresa,
                    'AUDNAM' => $nombreUsuario,
                    'AUDNEM' => $empresa,
                    'AUDCAN' => 'IB',
                    'AUDIPE' => (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']
                )
            )
        );
    }

    /**
     * Realiza la búsqueda de los productos utilizados en PAGO ACTIVO
     * y genera un arreglo para ser utilizado en los combos.
     *
     * @param array $tipos Arreglo de códigos de tipo de instrumentos
     *
     */
    function buscarInstrumentosPagoActivo($tipos)
    {

        $usuario = $this->Session->read('usuarios.datos');

        $tipo_documento = $usuario['tipo_cedula'];
        $documento = desencriptar($usuario['cedula']);

        $datos_basicos = $this->Session->read('usuarios.nibpf3010');

        if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
            $res = $this->invoca(
                'SUMDSJV',
                array(
                    'req' => array(
                        'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                        'SUMFIR' => 'X'
                    )
                )
            );
        }

        $this->instrumentos = array();

        if (isset($res) && !empty($res)) {
            $this->instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

            normaliza($this->instrumentos);
        } else {
            $this->instrumentos = array();
        }

        $deseados = array();

        foreach ($this->instrumentos as $i) {

            if (in_array($i['SUMTYP'], $tipos)) {

                // Omite las cuentas que sean inactivas
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && $i['SUMAST'] != 'A') {
                    continue;
                }

                // Omite las cuentas donde no sea el titular (1 = Individual 2 = Conjuntas 3 = Indistintas)
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && ($i['SUMFIR'] == '2' || $i['SUMFIR'] == '3')
                ) {
                    continue;
                }

                // Omite las tarjetas que no esten activas
                if (in_array($i['SUMTYP'],
                        array('CC&')) && ($i['SUMFIR'] != '1' && $i['SUMFIR'] != '2' && $i['SUMFIR'] != '3')
                ) {
                    continue;
                }

                // Omite las cuentas de pensionados
                if (trim($i['SUMUSR']) == 'IVSS') {
                    continue;
                }
                
                // Omite las cuentas para pago de colegios
                if (trim($i['SUMUSR']) == 'CCLC') {
                    continue;
                }
                
                // Omite las cuentas de moneda extranjera
                if ($i['SUMCCY'] != '') {
                    continue;
                }

                $deseados[trim($i['SUMACN'])] = enmascara($i['SUMACN']);
            }
        }

        // Si no encontro una cuenta
        if (count($deseados) == 0) {
            $deseados[''] = 'Estimado Cliente, no posee productos activos';
        }

        return $deseados;
    }

    /*
     * Realiza la búsqueda del tipo de cuenta del cliente segun el nro de cuenta
     *
     * @param string $identEBS identificacion del usuario en EIBS
         * @param array $nroCuenta numeros de cuenta/tarjeta a consultar
         *
     */
    function buscarTipoCuenta($identEBS, $nroCuenta)
    {
        $res = $this->invoca(
            'SUMDSJV',
            array(
                'req' => array(
                    'SUMACN' => $identEBS,
                    'SUMFIR' => 'X'
                )
            )
        );
        
        $x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
        normaliza($x);
        $deseado = array();

        foreach ($x as $list) {
            if (in_array(trim($list['SUMACN']), $nroCuenta)) {
                if (substr($list['SUMACN'], 0, 4) == '0171') {
                    $deseado[trim($list['SUMACN'])] = trim($list['SUMNNM']);
                    $deseado['SUMTYP'] = trim($list['SUMTYP']);
                } else {
                    $deseado[trim($list['SUMACN'])] = trim($list['SUMDSC']);
                    $deseado['SUMTYP'] = trim($list['SUMTYP']);
                }
            }
        }

        return $deseado;
    }

    /**
     * Realiza la validación de un RIF.
     *
     * @param string $tipo Tipo de documento de identidad
     * @param string $numero Número de documento de identidad
     * @return boolean Verdadero si el número de documento es válido
     */
    function generaRIF($tipo, $numero)
    {

        $res = $this->invoca(
            'IN097101',
            array(
                'req' => array(
                    'RNRORIF' => str_pad($numero, 8, "0", STR_PAD_LEFT),
                    'RSTATUS' => $tipo
                )
            )
        );

        if (isset($res['IN097101Return']['INVALRIF'])) {
            $rif = $tipo . str_pad($numero, 8, "0", STR_PAD_LEFT) . $res['IN097101Return']['INVALRIF']['RDIGITO'];
        } else {
            $rif = -1;
        }

        return $rif;
    }

    function cambiar_tipo_rif($rif)
    {

        switch (substr($rif, 0, 1)) {
            case 'V':
                $rif = '1' . substr($rif, 1);
                break;
            case 'E':
                $rif = '2' . substr($rif, 1);
                break;
            case 'J':
                $rif = '3' . substr($rif, 1);
                break;
            case 'P':
                $rif = '4' . substr($rif, 1);
                break;
            case 'G':
                $rif = '5' . substr($rif, 1);
                break;
        }
        return $rif;
    }
    
    /**
     * Consulta la proxima fecha de compensacion.
     *       
     * @return date Proxima fecha compensacion
     */
    function consultaFechaCompensacion() {
        
        $now = getdate();
        $hora_actual = strtotime($now['hours'].":".$now['minutes'].":".$now['seconds']);
        if($this->Compensacion->consultaCierreFranja()){
            $hora_compensacion = $this->Compensacion->consultaCierreFranja();
            $hora_compensacion = strtotime($hora_compensacion['HORA']);
        }else{
            $hora_compensacion = strtotime("00:00:00");
        }

        if($hora_actual >= $hora_compensacion){                    
            $fecha = strtotime('+1 day',strtotime(date('Y-m-d')));
            $nuevafecha = date ('Y-m-d' , $fecha);
            $fecha_ano = substr($nuevafecha, 2, 2);
            $fecha_mes = substr($nuevafecha, 5, 2);
            $fecha_dia = substr($nuevafecha, 8, 2);

            do{
                $res = $this->invoca(
                                'validaFechaFeriados',
                                array(
                                        'req'=>array(
                                            'dia'=>$fecha_dia,
                                            'mes'=>$fecha_mes,
                                            'ano'=>$fecha_ano,
                                        )
                                )
                ); 

                if ($res['ConsultaFeriadosReturn']['existe']=='true'){
                    $fecha = strtotime('+1 day',$fecha);
                    $nuevafecha = date ('Y-m-d' , $fecha);
                    $fecha_ano = substr($nuevafecha, 2, 2);
                    $fecha_mes = substr($nuevafecha, 5, 2);
                    $fecha_dia = substr($nuevafecha, 8, 2);
                    $existe=1;
                }else{
                    $existe=0;
                }
            }while($existe==1);

            $fecha = $nuevafecha;

        }else{
            $fecha = strtotime(date('Y-m-d'));
            $nuevafecha = date ('Y-m-d' , $fecha);
            $fecha_ano = substr($nuevafecha, 2, 2);
            $fecha_mes = substr($nuevafecha, 5, 2);
            $fecha_dia = substr($nuevafecha, 8, 2);
            
            do{
                $res = $this->invoca(
                                'validaFechaFeriados',
                                array(
                                        'req'=>array(
                                            'dia'=>$fecha_dia,
                                            'mes'=>$fecha_mes,
                                            'ano'=>$fecha_ano,
                                        )
                                )
                ); 

                if ($res['ConsultaFeriadosReturn']['existe']=='true'){
                    $fecha = strtotime('+1 day',$fecha);
                    $nuevafecha = date ('Y-m-d' , $fecha);
                    $fecha_ano = substr($nuevafecha, 2, 2);
                    $fecha_mes = substr($nuevafecha, 5, 2);
                    $fecha_dia = substr($nuevafecha, 8, 2);
                    $existe=1;
                }else{
                    $existe=0;
                }
            }while($existe==1);

            $fecha = $nuevafecha;
        }
        
        return $fecha;
    }
    
    /**
     * Funcion para validar que la identificacion del beneficiario no sea igual a la del cliente logeado
     * para transferencias terceros otros bancos
     * 
     * @param array|string $tipoIdentificacion
     * @param array|string $identificacion
     * @param array|string $tipoOperacion 
     * @return boolean
     */
    function validarIdentificacion($tipoIdentificacion,$identificacion,$tipoOperacion) {           

        $usuario = $this->Session->read('usuarios.datos');

        if(($tipoOperacion=='tob') && (!isset($usuario['rif']))){                

            $tipo_id = $usuario['tipo_cedula'];
            $nro_id = desencriptar($usuario['cedula']);

            if (($tipo_id==$tipoIdentificacion) && ($nro_id==$identificacion)){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }            

    }

    function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
                }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
    
    /**
     * Consulta las transacciones que poseen condicionamiento IP.
     * @transaccionId      
     * @return array transacciones
     */
    function consultaTransaccionesIP($transaccionId) {
        
        $usuario = $this->Session->read('usuarios.datos');
        $mostrar = false;
        
        // Modelo de TransaccionesPerfilesIp
	$this->TransaccionesPerfilesIp = ClassRegistry::init('TransaccionesPerfilesIp');
                
	$transacciones = $this->TransaccionesPerfilesIp->buscarTransaccionesIP($usuario['perfil_ib_id']);

	// Si es un arreglo
	if(is_array($transacciones)){            
            // Verifica si al menos uno de los "id" se encuentra en las transacciones condicionadas
            foreach ($transacciones as $id){                
                if($id['TransaccionesPerfilesIp']['transaccion_id']==$transaccionId){
                    $mostrar = true;
                }
            }            
	}
        
        return $mostrar;
    }
    
    /**
     * Verifica que una fecha esté dentro del rango de fechas establecidas
     * @param $start_date fecha de inicio
     * @param $end_date fecha final
     * @param $evaluame fecha a comparar
     * @return true si esta en el rango, false si no lo está
     */
    function check_in_range($start_date, $end_date, $evaluame) {

        $start_ts = strtotime($start_date);

        $end_ts = strtotime($end_date);

        $user_ts = strtotime($evaluame);

        return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));

    }
    
    /**
     * Devuelve TRUE si la dirección IPv4 dada pertenece a la subred indicada, FALSE si no
     *
     * @param string $str_ip Dirección IP en formato '127.0.0.1'
     * @param string $str_rango Red y máscara en formato '127.0.0.0/8', '127.0.0.0/255.0.0.0' o '127.0.0.1'
     * @return bool
     *
     * @version v2011-08-30
    */ 
    function ip_pertenece_a_red($str_ip, $str_rango){     
        // Extraemos la máscara     
        list($str_red, $str_mascara) = array_pad(explode('/', $str_rango), 2, NULL);     
        if( is_null($str_mascara) ){         
            // No se especifica máscara: el rango es una única IP         
            $mascara = 0xFFFFFFFF;                     
        }elseif( (int)$str_mascara==$str_mascara ){         
            // La máscara es un entero: es un número de bits         
            $mascara = 0xFFFFFFFF << (32 - (int)$str_mascara);                 
        }else{         
            // La máscara está en formato x.x.x.x         
            $mascara = ip2long($str_mascara);            
        }     
        
        $ip = ip2long($str_ip);     
        $red = ip2long($str_red);     
        $inf = $red & $mascara;     
        $sup = $red | (~$mascara & 0xFFFFFFFF);    
        return $ip>=$inf && $ip<=$sup;                       
    }

    /**
     * Realiza la búsqueda de los productos utilizados en SALDO ACTIVO PLUS
     * y genera un arreglo para ser utilizado en los combos.
     *
     * @param array $tipos Arreglo de códigos de tipo de instrumentos
     *
     */
    function buscarInstrumentosSaldoActivo($tiposCtas)
    {

        $usuario = $this->Session->read('usuarios.datos');

        $tipo_documento = $usuario['tipo_cedula'];
        $documento = desencriptar($usuario['cedula']);

        $datos_basicos = $this->Session->read('usuarios.nibpf3010');

        if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
            $res = $this->invoca(
                'SUMDSJV',
                array(
                    'req' => array(
                        'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                        'SUMFIR' => 'X'
                    )
                )
            );
        }

        $this->instrumentos = array();

        if (isset($res) && !empty($res)) {
            $this->instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

            normaliza($this->instrumentos);
        } else {
            $this->instrumentos = array();
        }

        $deseados = array();

        foreach ($this->instrumentos as $i) {
           
             if ((in_array($i['SUMCCY'], array('USD')) || $i['SUMTYP'] == 'CC&') && 
                (in_array(trim($i['SUMUSR']), array('DEPN','ECPN','','DEPJ','ECPJ','CDN1')))) {
            
            if (in_array($i['SUMTYP'], $tiposCtas)) {

                // Omite las cuentas que sean inactivas
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && $i['SUMAST'] != 'A') {
                    continue;
                }

                // Omite las cuentas donde no sea el titular (1 = Individual 2 = Conjuntas 3 = Indistintas)
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && ($i['SUMFIR'] == '2')
                ) {
                    continue;
                }

                // Omite las tarjetas que no esten activas
                if (in_array($i['SUMTYP'],
                        array('CC&')) && ($i['SUMFIR'] != '1' && $i['SUMFIR'] != '2' && $i['SUMFIR'] != '3')
                ) {
                    continue;
                }

                // Omite las cuentas de pensionados
                if (trim($i['SUMUSR']) == 'IVSS' ) {
                    continue;
                }
                
                // Omite las cuentas para pago de colegios
                if (trim($i['SUMUSR']) == 'CCLC') {
                    continue;
                }

                if (substr($i['SUMACN'], 0, 1) == '0') {
                $deseados[trim($i['SUMACN'])] = enmascara(substr($i['SUMACN'], 10, 10));
                }else{
                $deseados[trim($i['SUMACN'])] = enmascara(substr($i['SUMACN'], 0, 16));
                }

            }}
        }

        // Si no encontro una cuenta
        if (count($deseados) == 0) {
            $deseados[''] = 'Estimado Cliente, no posee productos activos';
        }

        return $deseados;
    }

    /**
     * Realiza la búsqueda de los productos utilizados en SALDO ACTIVO PLUS
     * y genera un arreglo para ser utilizado en los combos.
     *
     * @param array $tipos Arreglo de códigos de tipo de instrumentos
     *
     */
    function buscarInstrumentosSaldoActivoU($tiposCtas)
    {

        $usuario = $this->Session->read('usuarios.datos');

        $tipo_documento = $usuario['tipo_cedula'];
        $documento = desencriptar($usuario['cedula']);

        $datos_basicos = $this->Session->read('usuarios.nibpf3010');

        if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
            $res = $this->invoca(
                'SUMDSJV',
                array(
                    'req' => array(
                        'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                        'SUMFIR' => 'X'
                    )
                )
            );
        }
        

        $this->instrumentos = array();

        if (isset($res) && !empty($res)) {
            $this->instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

            normaliza($this->instrumentos);
        } else {
            $this->instrumentos = array();
        }

        $deseados = array();

        foreach ($this->instrumentos as $i) {

            if ((in_array($i['SUMCCY'],array('VEB')))&&
                (in_array(trim($i['SUMUSR']), array('CENA','CEPN','EXCE','MO01','NE01','NO01','CAPN','IN01','FU01','SN01','CEPJ','CAPJ','MO02','CCDO','IP01','IP02','IN02','CCD1','CCD2','CRN1','CCNA','NACE','NA01')))) {

            if (in_array($i['SUMTYP'], $tiposCtas)) {

                // Omite las cuentas que sean inactivas
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && $i['SUMAST'] != 'A') {
                    continue;
                }

                // Omite las cuentas donde no sea el titular (1 = Individual 2 = Conjuntas 3 = Indistintas)
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && ($i['SUMFIR'] == '2')
                ) {
                    continue;
                }

                // Omite las tarjetas que no esten activas
                if (in_array($i['SUMTYP'],
                        array('CC&')) && ($i['SUMFIR'] != '1' && $i['SUMFIR'] != '2' && $i['SUMFIR'] != '3')
                ) {
                    continue;
                }

                // Omite las cuentas de pensionados
                if (trim($i['SUMUSR']) == 'IVSS') {
                    continue;
                }
                
                // Omite las cuentas para pago de colegios
                if (trim($i['SUMUSR']) == 'CCLC') {
                    continue;
                }

                $deseados[trim($i['SUMACN'])] = enmascara($i['SUMACN']);

            }}
        }
    
        // Si no encontro una cuenta
        if (count($deseados) == 0) {
            
            $deseados[''] = 'Estimado Cliente, no posee productos activos';
        }

        return $deseados;
    }

    /**
     * Realiza la búsqueda de los productos utilizados en SALDO ACTIVO PLUS
     * y genera un arreglo para ser utilizado en los combos.
     *
     * @param array $tipos Arreglo de códigos de tipo de instrumentos
     *
     */
    function buscarInstrumentosSaldoActivoC($tiposCtas)
    {

        $usuario = $this->Session->read('usuarios.datos');

        $tipo_documento = $usuario['tipo_cedula'];
        $documento = desencriptar($usuario['cedula']);

        $datos_basicos = $this->Session->read('usuarios.nibpf3010');

        if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
            $res = $this->invoca(
                'SUMDSJV',
                array(
                    'req' => array(
                        'SUMACN' => trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
                        'SUMFIR' => 'X'
                    )
                )
            );
        }

        $this->instrumentos = array();

        if (isset($res) && !empty($res)) {
            $this->instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

            normaliza($this->instrumentos);
        } else {
            $this->instrumentos = array();
        }

        $deseados = array();

        foreach ($this->instrumentos as $i) {

            if ((in_array($i['SUMCCY'],array('VEB')))&&
                (in_array(trim($i['SUMUSR']), array('CENA','CEPN','EXCE','MO01','NE01','NO01','CAPN','IN01','FU01','SN01','IVSS','CCDO','CAPJ','IN02','IP01','IP02','CCD1','CCD2','CRN1','CCNA','NACE','NA01')))) {

            if (in_array($i['SUMTYP'], $tiposCtas)) {

                // Omite las cuentas que sean inactivas
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && $i['SUMAST'] != 'A') {
                    continue;
                }

                // Omite las cuentas donde no sea el titular (1 = Individual 2 = Conjuntas 3 = Indistintas)
                if (in_array($i['SUMTYP'], array('SAV', 'DDA', 'NOW', 'MMK')) && ($i['SUMFIR'] == '2')
                ) {
                    continue;
                }

                // Omite las tarjetas que no esten activas
                if (in_array($i['SUMTYP'],
                        array('CC&')) && ($i['SUMFIR'] != '1' && $i['SUMFIR'] != '2' && $i['SUMFIR'] != '3')
                ) {
                    continue;
                }
                
                // Omite las cuentas para pago de colegios
                if (trim($i['SUMUSR']) == 'CCLC') {
                    continue;
                }

                $deseados[trim($i['SUMACN'])] = enmascara($i['SUMACN']);

            }}
        }
    
        // Si no encontro una cuenta
        if (count($deseados) == 0) {
            
            $deseados[''] = 'Estimado Cliente, no posee productos activos';
        }

        return $deseados;
    }

    function invocaConsultaCorpoelec($servicio, $parametros, $metodo, $response_timeout = null)
    {

        if ($response_timeout == null) {
            $response_timeout = 60;
        }

        $tIdent = $parametros['tipoIdent'];
        $ident = $parametros['ident'];

        $url = Configure::read('PagoCorpoelec.url.servicio').$servicio.'?document_type='.$tIdent.'&document_number='.$ident;
        $token = Configure::read('Corpoelec.token');

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => 'ISO-8859-1',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $metodo,
          CURLOPT_HTTPHEADER => array(
            "token: " . $token
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);

    }

    function invocaPagoCorpoelec($servicio, $parametros, $metodo, $response_timeout = null)
    {

        if ($response_timeout == null) {
            $response_timeout = 60;
        }

        $url = Configure::read('PagoCorpoelec.url.servicio').$servicio;
        $token = Configure::read('Corpoelec.token');
        $parametros = json_encode($parametros);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $metodo,
          CURLOPT_POSTFIELDS => $parametros,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'token: ' . $token
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);


        $curl = curl_init();
    }

    /**
     * Método general para realizar la invocación a una operación del webservice.
     *
     * @param string $servicio Nombre del servicio en Pago Activo
     * @param array $parametros Parámetros del servicio
     * @return array|boolean Falso si hubo error. Arreglo con la respuesta si no
     *
     */
    function invocaPagoA($servicio, $parametros, $response_timeout = null)
    {

        if ($response_timeout == null) {
            $response_timeout = 60;
        }
                    
        $url = Configure::read('PagoMovil.url.servicio') . $servicio;
        $parametros = json_encode($parametros);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$parametros,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);

    }

    function invocaGatewayNexus($servicio, $parametros, $metodo, $response_timeout = null)
    {

        if ($response_timeout == null) {
            $response_timeout = 60;
        }

        $url = Configure::read('GatewayNexus.url').$servicio;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'UTF-8',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $metodo,
            CURLOPT_POSTFIELDS =>$parametros,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        // print_r($response);die();
        curl_close($curl);
        return json_decode($response);
    }

}

?>
