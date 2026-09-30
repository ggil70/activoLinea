<?php
/**
 * Componente que maneja la información de los estados de cuenta de las tarjetas
 * de crédito.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */



/**
 * Importación de la librería del manejo de los webservices.
 */
App::Import('Vendor', 'nusoap/nusoap');
 

/**
 * Maneja la información de los estados de cuenta de las tarjetas de crédito.
 * 
 */
class CredicardComponent extends Object {
	
	/**
	 * Controlador
	 * 
	 * @var string 
	 */
	var $controller;
	
	/**
	 * Objeto de conexión del webservice
	 * 
	 * @var object
	 */
	var $client;
	
	/**
	 * Componentes del cake utilizados
	 * 
	 * @var array 
	 */
	var $components = array('Session');

	
	/**
	 * Método invocado inicialmente por el CakePHP
	 * 
	 * @param string $controller
	 * @param array $settings
	 */
	function initialize(&$controller, $settings = array()) {
		$this->controller = $controller;
	}	

	
	/**
	 * Realiza la invocación al servicio de credicard para obtener la información 
	 * de los estados de cuenta.
	 * 
	 * @param string $tdc Número de tarjeta
	 * @param string $mes Mes seleccionado
	 * @return array|boolean Falso si falla. Arreglo con la respuesta del servicio
	 * de credicard si es exitoso
	 */
	function buscarEstadoCuenta($tdc,$mes){		
		$req =
		"<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsc=\"http://WsCredicard.credicard.com.ve/\">
		   <soapenv:Header>
			  <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
				 <wsse:UsernameToken wsu:Id=\"UsernameToken-24\" xmlns:wsu=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd\">
					<wsse:Username>".Configure::read('Credicard.user')."</wsse:Username>
					<wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">".Configure::read('Credicard.pwd')."</wsse:Password>
					<wsse:Nonce>".ceil(array_sum(explode(' ',microtime()))*1000)."</wsse:Nonce>
					<wsu:Created>".date('c')."</wsu:Created>
				 </wsse:UsernameToken>
			  </wsse:Security>
		   </soapenv:Header>
		   <soapenv:Body>
			  <wsc:obtenerDocumento>
				 <arg0>".implode('-',str_split($tdc,4))."</arg0>
				 <arg1>".date('y',$mes)."</arg1>
				 <arg2>".date('m',$mes)."</arg2>
			  </wsc:obtenerDocumento>
		   </soapenv:Body>
		</soapenv:Envelope>";
		
		if(!isset($this->client)){
			$this->client = new nusoap_client(Configure::read('Credicard.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}		

		$this->client->operation = "obtenerDocumento";
		$res = $this->client->send($req);
		
		//$this->log('Resultado: ',LOG_DEBUG);
		//$this->log($res,LOG_DEBUG);
		
		if($this->client->fault){
			$this->log('Error técnico:',LOG_ERR);
			$this->log($res,LOG_ERR);
			
			if(isset($this->controller)){
				$this->controller->set('error_fatal',_m('error.fatal'));
			}
			return false;
		} else {
			$err = $this->client->getError();

			if($err){
				$this->log('Error técnico:',LOG_ERR);
				$this->log($err,LOG_ERR);
				if(isset($this->controller)){
					$this->controller->set('error_fatal',_m('error.fatal'));
				}
				return false;
			}
		}

		return $res;		
	}


//ws de tarjeta garantizada (Prepagada)
//----------------------------------------
//Permite consultar una tarjeta por la cedula del cliente
//----------------------------------------

function consultarCuenta($cedula){	
      $cedula = trim($cedula);
		$req =
		"<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ser=\"http://service.ws.hub.credicard.com.ve/\">
		   <soapenv:Header>
		   </soapenv:Header>
			<soapenv:Body>
			 <ser:consultaCuentaTarjetaCliente>
			 <!--Optional:-->
			 <CuentaTarjetaClienteRequest>
			 <!--Optional:-->
			 <cedula>$cedula</cedula>
			 </CuentaTarjetaClienteRequest>
			 </ser:consultaCuentaTarjetaCliente>
			 </soapenv:Body>
		</soapenv:Envelope>";

	if(!isset($this->client)){
			$this->client = new nusoap_client(Configure::read('Credicard_CC_TCP.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}		

			//$username = "bactivo";
		$username = Configure::read('CredicardTCP.user');
        $password = Configure::read('CredicardTCP.pwd');
		$authtype = "basic";

        $this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "consultaCuentaTarjetaCliente";
		$res = $this->client->send($req);



		if($this->client->fault){
			$this->log('Error técnico:',LOG_ERR);
			$this->log($res,LOG_ERR);
			if(isset($this->controller)){
				$this->controller->set('error_fatal',_m('error.fatal'));
			}
			return 1;
		} else {
			$err = $this->client->getError();
			if($err){
				/*$this->log('Error técnico:',LOG_ERR);
				$this->log($err,LOG_ERR);
				if(isset($this->controller)){
					$this->controller->set('error_fatal',_m('error.fatal'));
				}*/
				return 1;
			}
      }


   
    	return $res;
	
	}

//----------------------------------------
//Permite activar la tarjeta solicitada
//----------------------------------------	
function activarTarjeta($cedula, $codSeguridad, $fecha, $tarjeta){
    $cedula = trim($cedula);
    $codSeguridad = trim($codSeguridad);
    $fecha = trim($fecha);
    $tarjeta =  trim($tarjeta);
    
    $req = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ser=\"http://service.ws.hub.credicard.com.ve/\">
   <soapenv:Header/>
   <soapenv:Body>
      <ser:activacionTarjetaPrepago>
         <!--Optional:-->
         <ActivacionTarjetaRequest>
            <!--Optional:-->
            <cedula>$cedula</cedula>
            <!--Optional:-->
            <codseguridad>$codSeguridad</codseguridad>
            <!--Optional:-->
            <fecha>$fecha</fecha>
            <!--Optional:-->
            <respuesta>00</respuesta>
            <!--Optional:-->
            <tarjeta>$tarjeta</tarjeta>
         </ActivacionTarjetaRequest>
      </ser:activacionTarjetaPrepago>
   </soapenv:Body>
   </soapenv:Envelope>"; 
		if(!isset($this->client)){
			$this->client = new nusoap_client(Configure::read('Credicard_ACT_TCP.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}		

			//$username = "bactivo";
		$username = Configure::read('CredicardTCP.user');
        $password = Configure::read('CredicardTCP.pwd');
		$authtype = "basic";

        $this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "activacionTarjetaPrepago";
		$res = $this->client->send($req);

		if($this->client->fault){
			$this->log('Error técnico:',LOG_ERR);
			$this->log($res,LOG_ERR);
			if(isset($this->controller)){
				$this->controller->set('error_fatal',_m('error.fatal'));
			}
			return 1;
		} else {
			$err = $this->client->getError();
			if($err){
				/*$this->log('Error técnico:',LOG_ERR);
				$this->log($err,LOG_ERR);
				if(isset($this->controller)){
					$this->controller->set('error_fatal',_m('error.fatal'));
                */
				return 1;	
			}
		}

		return $res;		
	}

//WS recarga de saldo a tarjeta garantizada
//----------------------------------------
//Permite Recargar saldo a la tarjeta solicitada
//----------------------------------------	
function recargaTarjeta($cedula, $tarjeta, $monto, $gestion, $motivo){
    $cedula = trim($cedula);
    $tarjeta =  trim($tarjeta);
    $monto = trim($monto);
    $gestion = trim($gestion);
    $motivo = trim($motivo);

    $req = "<soapenv:Envelope
			xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\"
			xmlns:ser=\"http://service.ws.hub.credicard.com.ve/\">
 			<soapenv:Header/>
 			<soapenv:Body>
 			<ser:recargaSaldoTarjetaPrepago>
 			<!--Optional:-->
 			<RecargaSaldoRequest>
 			<!--Optional:-->
 			<autorizacion>?</autorizacion>
 			<!--Optional:-->
 			<cedula>$cedula</cedula>
 			<!--Optional:-->
 			<descripcion>?</descripcion>
 			<!--Optional:-->
 			<gestion>$gestion</gestion>
 			<!--Optional:-->
 			<monto>$monto</monto>
 			<!--Optional:-->
 			<motivo>$motivo</motivo>
 			<!--Optional:-->
 			<respuesta>?</respuesta>
 			<!--Optional:-->
 			<tarjeta>$tarjeta</tarjeta>
 			<!--Optional:-->
 			<transaccion>?</transaccion>
 			</RecargaSaldoRequest>
 			</ser:recargaSaldoTarjetaPrepago>
 			</soapenv:Body>
			</soapenv:Envelope>"; 



		if(!isset($this->client)){
			$this->client = new nusoap_client(Configure::read('Credicard_REC_TCP.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}		

			//$username = "bactivo";
		$username = Configure::read('CredicardTCP.user');
        $password = Configure::read('CredicardTCP.pwd');
		$authtype = "basic";

        $this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "recargaSaldoTarjetaPrepago";
		$res = $this->client->send($req);

      

	
		if($this->client->fault){
			$this->log('Error técnico:',LOG_ERR);
			$this->log($res,LOG_ERR);
			if(isset($this->controller)){
				$this->controller->set('error_fatal',_m('error.fatal'));
			}
			return 1;
		} else {
			$err = $this->client->getError();
			if($err){
				/*$this->log('Error técnico:',LOG_ERR);
				$this->log($err,LOG_ERR);
				if(isset($this->controller)){
					$this->controller->set('error_fatal',_m('error.fatal'));
                */

				return 1;	
			}
		}

		return $res;

	}


//ws de tarjeta garantizada (Prepagada)
//----------------------------------------
//Permite consultar una tarjeta por la cedula del cliente
//----------------------------------------

function consultarSaldo($cuenta){	
	    $cuenta = trim($cuenta);

$req =
"<soapenv:Envelope
xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\"
xmlns:ser=\"http://service.ws.hub.credicard.com.ve/\">
 <soapenv:Header/>
 <soapenv:Body>
 <ser:consultaPrepagoSaldoResumen>
 <!--Optional:-->
 <PrepagoSaldoResumenRequest>
 <!--Optional:-->
 <numCuenta>$cuenta</numCuenta>
 </PrepagoSaldoResumenRequest>
 </ser:consultaPrepagoSaldoResumen>
 </soapenv:Body>
</soapenv:Envelope>
";



	if(!isset($this->client)){
			$this->client = new nusoap_client(Configure::read('Credicard_SALDO_TCP.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}		

			//$username = "bactivo";
		$username = Configure::read('CredicardTCP.user');
        $password = Configure::read('CredicardTCP.pwd');
		$authtype = "basic";

        $this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "consultaPrepagoSaldoResumen";
		$res = $this->client->send($req);

		if($this->client->fault){
			$this->log('Error técnico:',LOG_ERR);
			$this->log($res,LOG_ERR);
			if(isset($this->controller)){
				$this->controller->set('error_fatal',_m('error.fatal'));
			}
			return 1;
		} else {
			$err = $this->client->getError();
			if($err){
				/*$this->log('Error técnico:',LOG_ERR);
				$this->log($err,LOG_ERR);
				if(isset($this->controller)){
					$this->controller->set('error_fatal',_m('error.fatal'));
                */
				return 1;	
			}
		}

      return $res;  

	}





//Ws movimiento en la cuenta
//----------------------------------------
//Permite consultar una tarjeta por la cedula del cliente
//----------------------------------------

function movimientoSaldo($anio,$mes,$nroCuenta){	
$anio = trim($anio);
$mes = trim($mes);
$nroCuenta = trim($nroCuenta);

$req =
"<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ser=\"http://service.ws.hub.credicard.com.ve/\">
   <soapenv:Header/>
   <soapenv:Body>
 <ser:consultaPrepagoMovimientos>
 <!--Optional:-->
 <PrepagoMovimientosRequest>
 <!--Optional:-->
 <ano>$anio</ano>
 <!--Optional:-->
 <mes>$mes</mes>
 <!--Optional:-->
 <numCuenta>$nroCuenta</numCuenta>
 <!--Optional:-->
 <sesionId>0</sesionId>
 <!--Optional:-->
 <tipoUsuario>P</tipoUsuario>
 <!--Optional:-->
 <usuario>WS</usuario>
 </PrepagoMovimientosRequest>
 </ser:consultaPrepagoMovimientos>
 </soapenv:Body>
</soapenv:Envelope>";


	if(!isset($this->client)){
			$this->client = new nusoap_client(Configure::read('Credicard_MOV_TCP.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}		

		$username = Configure::read('CredicardTCP.user');
        $password = Configure::read('CredicardTCP.pwd');
		$authtype = "basic";

        $this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "consultaPrepagoMovimientos";
		$res = $this->client->send($req);

	
		if($this->client->fault){
			$this->log('Error técnico:',LOG_ERR);
			$this->log($res,LOG_ERR);
			if(isset($this->controller)){
				$this->controller->set('error_fatal',_m('error.fatal'));
			}
			return 1;
		} else {
			$err = $this->client->getError();
			if($err){
				/*$this->log('Error técnico:',LOG_ERR);
				$this->log($err,LOG_ERR);
				if(isset($this->controller)){
					$this->controller->set('error_fatal',_m('error.fatal'));
                */

				return 1;	
			}
		}

     return $res;

}





//ws de CAmbio de PIN
//----------------------------------------
//Permite cambiar e PIN 
//----------------------------------------

function cambioPIN($cedulaCliente, $tarjeta, $fecVencimiento, $codSeguridad, $nuevaClave){

$cedulaCliente = trim($cedulaCliente);
$tarjeta = trim($tarjeta);
$fecVencimiento = trim($fecVencimiento);
$codSeguridad = trim($codSeguridad);
$nuevaClave = trim($nuevaClave);

$req =
"<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ser=\"http://service.ws.hub.credicard.com.ve/\">
   <soapenv:Header/>
   <soapenv:Body>
      <ser:solicitudGestionPinPrepago>
         <!--Optional:-->
         <GestionPinPrepagoRequest>
          <cedula>$cedulaCliente</cedula>
         <!--0 to 1 occurrence-->
         <codigoSeguridad>$codSeguridad</codigoSeguridad>
         <!--0 to 1 occurrence-->
         <fechaExpiracion>$fecVencimiento</fechaExpiracion>
         <!--0 to 1 occurrence-->
         <motivo>?</motivo>
         <!--0 to 1 occurrence-->
         <pinNuevo>$nuevaClave</pinNuevo>
         <!--0 to 1 occurrence-->
         <respuesta>?</respuesta>
         <!--0 to 1 occurrence-->
         <tarjeta>$tarjeta</tarjeta>
         </GestionPinPrepagoRequest>
      </ser:solicitudGestionPinPrepago>
   </soapenv:Body>
</soapenv:Envelope>";


	if(!isset($this->client)){
			$this->client = new nusoap_client(Configure::read('Credicard_PIN_TCP.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}		

			//$username = "bactivo";
		$username = Configure::read('CredicardTCP.user');
      $password = Configure::read('CredicardTCP.pwd');
		$authtype = "basic";

        $this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "cambioPinPrepago";
		$res = $this->client->send($req);


		if($this->client->fault){
			$this->log('Error técnico:',LOG_ERR);
			$this->log($res,LOG_ERR);
			if(isset($this->controller)){
				$this->controller->set('error_fatal',_m('error.fatal'));
			}
			return 1;
		} else {
			$err = $this->client->getError();
			if($err){
				/*$this->log('Error técnico:',LOG_ERR);
				$this->log($err,LOG_ERR);
				if(isset($this->controller)){
					$this->controller->set('error_fatal',_m('error.fatal'));
                */
				return 1;	
			}
		}

		return $res;
	
	}




}

?>