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
      $cedula = str_pad($cedula, 12, '0', STR_PAD_LEFT);

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
    $cedula = str_pad($cedula, 10, '0', STR_PAD_LEFT);
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
    $cedula = str_pad($cedula, 12, '0', STR_PAD_LEFT);
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


function consultaCandado($cedulaCliente){

$cedulaCliente = trim($cedulaCliente);

$req = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ser=\"http://service.ws.hub.credicard.com.ve/\">
	<soapenv:Header>
	</soapenv:Header>

   <soapenv:Body>
      <ser:ConsultaCandadoService>
         <!--Optional:-->
         <ConsultaCandadoServiceRequest>
            <!--Optional:-->
            <cedula>$cedulaCliente</cedula>
         </ConsultaCandadoServiceRequest>
      </ser:ConsultaCandadoService>
   </soapenv:Body>
</soapenv:Envelope>";


	if(!isset($this->client)){
			$this->client = new nusoap_client(Configure::read('Credicard_Consulta_Candado.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}		

			//$username = "bactivo";

		$username = Configure::read('CredicardTCP.user');
      	$password = Configure::read('CredicardTCP.pwd');
		$authtype = "basic";

        $this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "consultaCandadoService";
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






function actualizarCandado($nroTarjeta, $estatusNew){

$nroTarjeta = trim($nroTarjeta);
$estatusNew = trim($estatusNew);

$req = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ser=\"http://service.ws.hub.credicard.com.ve/\">
<soapenv:Header/>
<soapenv:Body>
<ser:activacionDesactivacionCandadoPrepagoService>
<!--Optional:-->
<ActivacionDesactivacionCandadoPrepagoServiceRequest>
<!--Optional:-->
<codcnd>$estatusNew</codcnd>
<!--Optional:-->
<tarjeta>$nroTarjeta</tarjeta>
</ActivacionDesactivacionCandadoPrepagoServiceRequest>
</ser:activacionDesactivacionCandadoPrepagoService>
</soapenv:Body>
</soapenv:Envelope>";


if(!isset($this->client)){
		$this->client = new nusoap_client(Configure::read('Credicard_Activar_Candado.url'));
		$this->client->timeout = 300;
		$this->client->response_timeout = 300;
	}		

		//$username = "bactivo";
	$username = Configure::read('CredicardTCP.user');
  	$password = Configure::read('CredicardTCP.pwd');
	$authtype = "basic";

    $this->client->setCredentials($username, $password, $authtype);
	$this->client->operation = "activacionDesactivacionCandadoPrepago";
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
		return $res;
	}
}	



	function consultaMovimientoSoap($cuenta, $mes=null, $anio = null)
	{

      if($mes == null){ 
      	$aniotxn = date('Y');
      	$mestxn = date('m');
         if(strlen($mestxn)==1){
         	$mestxn = '0' . $mestxn;
         }  
   	}else{
	      $aniotxn = $anio;
   	   $mestxn = $mes;
         if(strlen($mestxn)==1){
         	$mestxn = '0' . $mestxn;
         }  
   	}  

	     $req = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.ws.hub.credicard.com.ve/">
			<soapenv:Header/>
			<soapenv:Body>
			<ser:ConsultaAutorizacionesenLineaTDC>
			<!--Optional:-->
			<ConsultaAutorizacionesenLineaTDCRequest>
			<!--Optional:-->
			<aniotxn>' . $aniotxn . '</aniotxn>
			<!--Optional:-->
			<cuenta>' . $cuenta . '</cuenta>
			<!--Optional:-->
			<mestxn>' .$mestxn.'</mestxn>
			<!--Optional:-->
			<tipomov>1</tipomov>
			</ConsultaAutorizacionesenLineaTDCRequest>
			</ser:ConsultaAutorizacionesenLineaTDC>
			</soapenv:Body>
			</soapenv:Envelope>';

		if (!isset($this->client)) {                          
			$this->client = new nusoap_client(Configure::read('Credicard_Consultar_Movimientos_TDC.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}

		$username = Configure::read('CredicardActivacionTDC.user');
		$password = Configure::read('CredicardActivacionTDC.pwd');

		$authtype = "basic";
		$this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "ConsultaAutorizacionesenLineaTDC";
		$this->client->soap_defencoding = 'UTF-8';
		$this->client->decode_utf8 = false;

		$resMovimiento = $this->client->send($req);

		
		if ($this->client->fault) {

			$this->log('Error t?cnico:', LOG_ERR);
			$this->log($res, LOG_ERR);

			if (isset($this->controller)) {
				$this->controller->set('error_fatal', _m('error.fatal'));
			}
			return 1;
		} else {

			$err = $this->client->getError();
			if ($err) {

				// $this->log('Error t?cnico:',LOG_ERR);
				// $this->log($err,LOG_ERR);
				// if(isset($this->controller)){
				// 	$this->controller->set('error_fatal',_m('error.fatal'));					
				return 1;
			}
		}

		// $soapResponse = $this->bancoActivoPagoEnLineaTdcMensajes($res);
		$this->Session->write('consulta_TDC.soapResponse', $resMovimiento);
		return $resMovimiento;
		// foreach ($res as $r) {

		// 	$this->Session->write('consulta_TDC.soapResponse', $r);

		// 	return $r;
		// };
	}





	function consultaDeSaldoTdcSoap($swServicio)
	{

		// cedula
		$usuario = $this->Session->read('usuarios.datos');
		$num_cedula = '00' . desencriptar($usuario['cedula']);
		$cod_cedula = $usuario['tipo_cedula'];


		//$num_cedula = '009879879';

		//$num_cedula = '009639639';

		
      /* fin Quitar este numero de cedula */   


		$req = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.ws.hub.credicard.com.ve/">
				    <soapenv:Header/>
					<soapenv:Body>
						<ser:ConsultaSaldoenLineaTDC>
							<!--Optional:-->
							<ConsultaSaldoenLineaTDCRequest>
								<!--Optional:-->
								<cedulath>' . $num_cedula . '</cedulath>
								<!--Optional:-->
								<nacionalidadth>' . $cod_cedula . '</nacionalidadth>
							</ConsultaSaldoenLineaTDCRequest>
						</ser:ConsultaSaldoenLineaTDC>
					</soapenv:Body>
				</soapenv:Envelope>';
		if (!isset($this->client)) {
			$this->client = new nusoap_client(Configure::read('Credicard_Consultar_TDC.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}

		//$username = "bactivo";
		$username = Configure::read('CredicardActivacionTDC.user');
		$password = Configure::read('CredicardActivacionTDC.pwd');
		$authtype = "basic";

		$this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "ConsultaSaldoenLineaTDC";
		$this->client->soap_defencoding = 'UTF-8';
		$this->client->decode_utf8 = false;

		$res = $this->client->send($req);

		if ($this->client->fault) {

			$this->log('Error t?cnico:', LOG_ERR);
			$this->log($res, LOG_ERR);

			if (isset($this->controller)) {
				$this->controller->set('error_fatal', _m('error.fatal'));
			}
			return 1;
		} else {

			$err = $this->client->getError();
			if ($err) {

				// $this->log('Error t?cnico:',LOG_ERR);
				// $this->log($err,LOG_ERR);
				// if(isset($this->controller)){
				// 	$this->controller->set('error_fatal',_m('error.fatal'));					
				return 1;
			}
		}

		if($swServicio == 1){
			$this->Session->write('consulta_TDC.soapResponse', $res);
			return $res;
		}else{	
		

			if (
				isset($res['ConsultSaldoenLineaTDCResponse']) && 
				is_array($res['ConsultSaldoenLineaTDCResponse'])
			) {

				$response = $res['ConsultSaldoenLineaTDCResponse'];

				foreach($response as $r){

					if (gettype($r) === "array") {
						// varias TDC
						
							$this->Session->write('consulta_TDC.soapResponse', $res['ConsultSaldoenLineaTDCResponse']);
							 return $res['ConsultSaldoenLineaTDCResponse'];
						

					}else if(gettype($r) === "string"){
						// una sola TDC
						if($response['estatustrj'] == 2 || $response['estatustrj'] == 1){
							$this->Session->write('consulta_TDC.soapResponse', $response);
							return $response;
						}
					}
				}
		

			}
				// foreach ($res as $r) {
				// 	foreach ($resArr as $rA) {								
				// 		if($rA['estatustrj'] !== 2 || $rA['estatustrj'] !== 1){
				// 			$this->Session->write('consulta_TDC.soapResponse', $r);
				// 			return $r;
				// 		}
				// 	}

				// }
		}
	}



   function bancoActivoPagoEnLineaTdcMensajes($res, $code = null)
	{

        


		if ($code === 1) {
			$tdc_credicard_mensaje = array('IN982ENDFL' => 'Y', 'mensaje' => $res);
			// caso de rechazo

			return $tdc_credicard_mensaje;
		} else if (isset($res['faultcode'])) {

			
			$tdc_credicard_mensaje = array('IN982ENDFL' => 'Y', 'mensaje' => $res['faultstring']);

			return $tdc_credicard_mensaje;
		} else {
			if (isset($res['detail']['ExceptionFault']['detail']['detailCode'])) {

				$tdc_credicard_mensaje = array('IN982ENDFL' => 'Y', 'mensaje' => $res['detail']['ExceptionFault']['detail']['detailMessage']);

				// condicion si falta algun campo
				return $tdc_credicard_mensaje;
			} else if (isset($res['PagoenLineaTDCResponse'])) {


				// respuesta del servicio con todos los campos
				if ($res['PagoenLineaTDCResponse']['codResult'] !== "0") {

					$tdc_credicard_mensaje = array('IN982ENDFL' => 'Y', 'mensaje' => $res['PagoenLineaTDCResponse']['mensaje']);
					// caso de rechazo
					return $tdc_credicard_mensaje;
				} else if ($res['PagoenLineaTDCResponse']['codResult'] == "0") {
					$tdc_credicard_mensaje = array('IN982ENDFL' => 'N', 'mensaje' => $res['PagoenLineaTDCResponse']['mensaje']);
					// caso de rechazo
					return $tdc_credicard_mensaje;
				} else {

					$tdc_credicard_mensaje = array('IN982ENDFL' => 'N', 'mensaje' => 'Solicitud de Pago Efectuada satisfactoriamente');
					// caso exitoso
					return $tdc_credicard_mensaje;
				}
			} else {

				// casos de mensajes no registrados o nuevos
				return json_encode($res);
			}
		}
	}

	function bancoActivoPagoEnLineaTdc(
		$anioTransaccion = null,
		$codBCV = null,
		$diaTransaccion = null,
		$mesTransaccion = null,
		$monto = null,
		$numTransaccion = null,
		$cuentaDebitar = null
	) {



		$anioTransaccion = date("Y");
		$codBCV = '860';
		$diaTransaccion = date("d");
		$mesTransaccion = date("m");
		$monto = $this->data['Pagostdc']['monto'];
		$numTransaccion = rand(1, 999999999);
		$cuentaDebitar = $this->data['Pagostdc']['cuentaDebitar'];
		// cedula
		$usuario = $this->Session->read('usuarios.datos');
		$cedula = desencriptar($usuario['cedula']);
		// end cedula
		$datosUsuarioPagoCredicardTDC = array(
			'monto' => $monto,
			'cuentaDebitar' => $cuentaDebitar,
			'numeroTDC' => $this->data['Pagostdc']['numeroTDC'],
			'tipo_pago' => $this->data['Pagostdc']['tipo_pago'],
			'tipo_pago_t' => $this->data['Pagostdc']['tipo_pago_t'],
			'descripcion' => $this->data['Pagostdc']['descripcion'],
			'anioTransaccion' => $anioTransaccion,
			'codBCV' => $codBCV,
			'diaTransaccion' => $diaTransaccion,
			'mesTransaccion' => $mesTransaccion,
			'numTransaccion' => $numTransaccion,
			'cedula' => $cedula
		);



		return $datosUsuarioPagoCredicardTDC;
	}

	function PagoEnLineaTdcSoap($anioTransaccion, $codBCV, $diaTransaccion, $mesTransaccion, $monto, $numTransaccion, $numeroTDC)
	{


      

		//$soapData = $this->bancoActivoPagoEnLineaTdc();
		$monto_replace = str_replace('.', '', $monto);
		$monto_replace_two = str_replace(',', '.', $monto_replace);




		$req = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.ws.hub.credicard.com.ve/">
		<soapenv:Header/>
			<soapenv:Body>
			<ser:pagoenLineaTDC>
				<!--Optional:-->
				<PagoenLineaTDCRequest>
					<!--Optional:-->
					<anioTransaccion>' . $anioTransaccion . '</anioTransaccion>
					<!--Optional:-->
					<codBCV>' . $codBCV . '</codBCV>
					<!--Optional:-->
					<diaTransaccion>' . $diaTransaccion . '</diaTransaccion>
					<!--Optional:-->
					<mesTransaccion>' . $mesTransaccion . '</mesTransaccion>
					<!--Optional:-->
					<montoOperacion>' . $monto_replace_two . '</montoOperacion>
					<!--Optional:-->
					<numTransaccion>' . $numTransaccion . '</numTransaccion>
					<!--Optional:-->
					<tarjeta>' . $numeroTDC . '</tarjeta>
				</PagoenLineaTDCRequest>
			</ser:pagoenLineaTDC>
			</soapenv:Body>
		</soapenv:Envelope>';



		if (!isset($this->client)) {
			$this->client = new nusoap_client(Configure::read('Credicard_Pagar_TDC.url'));
			$this->client->timeout = 300;
			$this->client->response_timeout = 300;
		}

		$username = Configure::read('CredicardActivacionTDC.user');
		$password = Configure::read('CredicardActivacionTDC.pwd');
		$authtype = "basic";

		$this->client->setCredentials($username, $password, $authtype);
		$this->client->operation = "pagoenLineaTDC";
		$this->client->soap_defencoding = 'UTF-8';
		$this->client->decode_utf8 = false;
		$res = $this->client->send($req);

		if ($this->client->fault) {
			
			$this->log('Error tï¿½cnico:', LOG_ERR);
			$this->log($res, LOG_ERR);

			if (isset($this->controller)) {
				$this->controller->set('error_fatal', _m('error.fatal'));
			}
			return 1;
		} else {

			$err = $this->client->getError();

			if ($err) {

				/*$this->log('Error tï¿½cnico:',LOG_ERR);
                            $this->log($err,LOG_ERR);
                            if(isset($this->controller)){
                                $this->controller->set('error_fatal',_m('error.fatal'));
                            */
				$res = $err;

				$soapResponse = $this->bancoActivoPagoEnLineaTdcMensajes($res, 1);
				$this->Session->write('pago_TDC.soapResponse', $soapResponse);

				return $soapResponse;
			} 
		}

		$soapResponse = $this->bancoActivoPagoEnLineaTdcMensajes($res);
		
		$this->Session->write('pago_TDC.soapResponse', $soapResponse);

		return $soapResponse;
	}






	// TDC Activacion
	function activacionTdcSoap($cedula, $cvc, $fecha, $respuesta, $tarjeta){
		$cedula = trim($cedula);
   	$cedula = str_pad($cedula, 10, '0', STR_PAD_LEFT);
   	$cvc = trim($cvc);
   	$fecha = trim($fecha);
   	$respuesta = trim($respuesta);
   	$tarjeta = trim($tarjeta);

		$req = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.ws.hub.credicard.com.ve/">
                        <soapenv:Header/>
                        <soapenv:Body>
                            <ser:activacionTarjetaCredito>
                                <!--Optional:-->
                                <ActivacionTarjetaRequest>
                                    <!--Optional:-->
                                    <cedula>' . $cedula . '</cedula>
                                    <!--Optional:-->
                                    <cvc2>' . $cvc . '</cvc2>
                                    <!--Optional:-->
                                    <fecha>' . $fecha . '</fecha>
                                    <!--Optional:-->
                                    <respuesta>' . $respuesta . '</respuesta>
                                    <!--Optional:-->
                                    <tarjeta>' . $tarjeta . '</tarjeta>
                                </ActivacionTarjetaRequest>
                            </ser:activacionTarjetaCredito>
                        </soapenv:Body>
                    </soapenv:Envelope>';

            if (!isset($this->client)) {
                $this->client = new nusoap_client(Configure::read('Credicard_Activar_TDC.url'));
                $this->client->timeout = 300;
                $this->client->response_timeout = 300;
            }

            //$username = "bactivo";
            $username = Configure::read('CredicardActivacionTDC.user');
            $password = Configure::read('CredicardActivacionTDC.pwd');
            $authtype = "basic";

            $this->client->setCredentials($username, $password, $authtype);
            $this->client->operation = "pagoenLineaTDC";
            $this->client->soap_defencoding = 'UTF-8';
            $this->client->decode_utf8 = false;
            $res = $this->client->send($req);
            

            if ($this->client->fault) {
                $this->log('Error t?cnico:', LOG_ERR);
                $this->log($res, LOG_ERR);

                if (isset($this->controller)) {
                    $this->controller->set('error_fatal', _m('error.fatal'));
                }
                
                return 1;
            } else {

                $err = $this->client->getError();
                if ($err) {
                   
                    return 1;
                }
            }
            
            return $res;
	}

}

?>