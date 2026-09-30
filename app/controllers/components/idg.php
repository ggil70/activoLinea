<?php
/**
 * Componente que maneja los métodos para el uso del IDG.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Importación de la librería del manejo de los webservices.
 */
App::Import('Vendor', 'nusoap/nusoap');


/**
 * Maneja los métodos para el uso del IDG.
 */
class IdgComponent extends Object{
	
	/**
	 * Controlador
	 * 
	 * @var string 
	 */
	var $controller;
	
	
	/**
	 * Objeto de conexión del webservice de administración
	 * 
	 * @var object
	 */
	var $cliente_admin;
	
	
	/**
	 * Objeto de conexión del webservice de autenticación
	 * 
	 * @var object
	 */
	var $cliente_auth;
	
	
	/**
	 * Componentes del cake utilizados
	 * 
	 * @var array 
	 */
	var $components = array('Session','Sms');
	
	
	/**
	 * Mensaje de error en la invocación del webservice
	 * 
	 * @var string 
	 */
	var $mensaje_error;
	
	
	/**
	 * Código de error de la invocación del webservice
	 * 
	 * @var int 
	 */
	var $codigo_error;
	
	
	/**
	 * Código de error interno de la invocación del webservice
	 * 
	 * @var int 
	 */
	var $error_interno;
	
	
	/**
	 * Reemplaza los caracteres especiales en su equivalente XML
	 *  
	 * @param string $valor Cadena a escapar
	 * @return string Cadena escapada
	 */
	function escapa($valor){
		return str_replace(
			array("&", "<", ">", "\"", "'"),
			array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;"),
			$valor
		);
	}

	
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
	 * Método para manejar el error en caso de ocurrir en la invocación del webservice.
	 * 
	 * @param array $res Respuesta de idg
	 * @param string $cliente Objeto de conexión con el IDG
	 * @return boolean Falso si hubo error.
	 */
	function manejaError($res,$cliente){
		//$this->log('RESPUESTA', LOG_DEBUG);
		//$this->log($res, LOG_DEBUG);
		
		if($cliente->fault){
			$this->log("Error invocando idg ".$cliente->operation,LOG_ERR);
			$this->log('Error técnico:',LOG_ERR);
			$this->log($res,LOG_ERR);
			$this->mensaje_error = _m('error.fatal');
			return false;
		} if(is_array($res) && isset($res['faultcode'])){
			$this->log("Error invocando idg ".$cliente->operation,LOG_ERR);
			$this->log('Error técnico:',LOG_ERR);
			$this->log($res,LOG_ERR);
			
			$mensaje = _m('error.fatal');
			
			$detalle = array_values($res['detail']);
			
			if(is_array($res) && isset($detalle[0]['ErrorCode'])){
				$this->codigo_error = $detalle[0]['ErrorCode'];
			}elseif(is_array($res) && isset($detalle[0]['ErrorCode'])){
				$this->codigo_error = $detalle[0]['ErrorCode'];
			}
			
			if(is_array($res) && isset($detalle[0]['InternalCode'])){
				$this->error_interno = $detalle[0]['InternalCode'];
			}elseif(is_array($res) && isset($detalle[0]['InternalCode'])){
				$this->error_interno = $detalle[0]['InternalCode'];
			}			
			
			$mensaje_bd = _m('IDG_'.$this->codigo_error);
			$mensaje_bd2 = _m('IDG_'.$this->error_interno);
			
			if(!empty($mensaje_bd2)){
				$mensaje = $mensaje_bd2;
			} elseif(!empty($mensaje_bd)){
				$mensaje = $mensaje_bd;
			}elseif(is_array($res) && isset($detalle[0]['errorMessage'])){
				$mensaje = $detalle[0]['errorMessage'];
			}elseif(is_array($res) && isset($detalle[0]['errorMessage'])){
				$mensaje = $detalle[0]['errorMessage'];
			}
			
			$this->mensaje_error = $mensaje;
			return false;
		} else {
			$err = $cliente->getError();

			if($err){
				$this->log("Error invocando idg ".$cliente->operation,LOG_ERR);
				$this->log('Error técnico:',LOG_ERR);
				$this->log($err,LOG_ERR);
				$this->mensaje_error = _m('error.fatal');
				return false;
			}
		}
		
		return true;
	}
	
	
	/**
	 * Realiza la conexión con el webservice de administración.
	 * 
	 * @return object
	 */
	function getClienteAdm(){
		if(!isset($this->cliente_admin)){
			$this->cliente_admin = new nusoap_client(Configure::read('Idg.admin.url'),'wsdl');
			$this->cliente_admin->timeout = 10;
			$this->cliente_admin->response_timeout = 15;
		}	
		
		return $this->cliente_admin;
	}
	
	
	/**
	 * Realiza la conexión con el webservice de autenticación.
	 * 
	 * @return object El objeto interface de conexión con el servicio de autenticación
	 * de IDG
	 */
	function getClienteAuth(){
		if(!isset($this->cliente_auth)){
			$this->cliente_auth = new nusoap_client(Configure::read('Idg.auth.url'),'wsdl');
			$this->cliente_auth->timeout = 10;
			$this->cliente_auth->response_timeout = 15;
		}	
		
		return $this->cliente_auth;
	}
	
	
	/**
	 * Verifica el estatus de las preguntas de desafío en el IDG.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @param int $tamano Cantidad de preguntas a realizar
	 * @return array|boolean Arreglo con respuesta de IDG. Falso si hubo error
	 */
	function preAutenticarQA($login,$tamano=3){
		$this->getClienteAuth();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:authenticationV8:wsdl">
		   <soapenv:Header/>
		   <soapenv:Body>
			  <urn:getGenericChallengeCallParms>
				 <userId>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userId>
				 <parms>
					<QAChallengeSize>'.$this->escapa($tamano).'</QAChallengeSize>
					<AuthenticationType>QA</AuthenticationType>
				  </parms>
			  </urn:getGenericChallengeCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req,LOG_DEBUG);
		
		$this->cliente_auth->operation = "getGenericChallenge";
		$res = $this->cliente_auth->send($req);
		
		if($this->manejaError($res,$this->cliente_auth)){
			return $res;
		} else {
			return false;
		}			
	}
	
	
	/**
	 * Verifica el estatus del usuario en el IDG.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @param string $tipo Tipo de autenticación
	 * @return array|boolean
	 */
	function preAutenticar($login,$tipo='PASSWORD'){
		$this->getClienteAuth();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:authenticationV8:wsdl">
		   <soapenv:Header/>
		   <soapenv:Body>
			  <urn:getGenericChallengeCallParms>
				 <userId>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userId>
				 <parms>
					<AuthenticationType>'.$this->escapa($tipo).'</AuthenticationType>
				  </parms>
			  </urn:getGenericChallengeCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req,LOG_DEBUG);
		
		$this->cliente_auth->operation = "getGenericChallenge";
		$res = $this->cliente_auth->send($req);
		
		if($this->manejaError($res,$this->cliente_auth)){
			return $res;
		} else {
			return false;
		}			
	}
	
	
	/**
	 * Realiza la autenticación de las preguntas de autenticación
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @param array|string $items Respuesta o arreglo con respuestas a la(s) preguntas
	 * de autenticación
	 * @param int $cantidad Cantidad de preguntas a realizar
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla.
	 */
	function autenticarQA($login, $items,$cantidad=3){
		$this->getClienteAuth();
		
		$items_str = array();
		
		if(is_array($items)){
			foreach($items as $i){
				$items_str .= '<item>'.$this->escapa($i).'</item>';		
			}
		} else {
			$items_str = '<item>'.$this->escapa($items).'</item>';
		}

		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:authenticationV8:wsdl">
		   <soapenv:Header/>
		   <soapenv:Body>
			  <urn:authenticateGenericChallengeCallParms>
				 <userId>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userId>
				 <response>
				 <response>
					'.$items_str.'
				 </response>
				 </response>
				 <parms>
					<ChallengeSize>'.$cantidad.'</ChallengeSize>
					<AuthenticationType>QA</AuthenticationType>
				</parms>
			  </urn:authenticateGenericChallengeCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';	

		//$this->log($req,LOG_DEBUG);
		
		$this->cliente_auth->operation = "authenticateGenericChallenge";
		$res = $this->cliente_auth->send($req);
		
		if($this->manejaError($res,$this->cliente_auth)){
			return $res;
		} else {
			return false;
		}	
	}
	
	
	/**
	 * Realiza la autenticación del usuario
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @param array|string $items Respuesta o arreglo con respuestas a la autenticación
	 * @param string $tipo Tipo de autenticación
	 * @return array|boolean Arreglo con respuesta de IDG. Falso si falla
	 */
	function autenticar($login, $items,$tipo='PASSWORD'){
		$this->getClienteAuth();
		
		$items_str = array();
		
		if(is_array($items)){
			foreach($items as $i){
				$items_str .= '<item>'.$this->escapa($i).'</item>';		
			}
		} else {
			$items_str = '<item>'.$this->escapa($items).'</item>';
		}

		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:authenticationV8:wsdl">
		   <soapenv:Header/>
		   <soapenv:Body>
			  <urn:authenticateGenericChallengeCallParms>
				 <userId>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userId>
				 <response>
				 <response>
					'.$items_str.'
				 </response>
				 </response>
				 <parms>
					<AuthenticationType>'.$this->escapa($tipo).'</AuthenticationType>
				</parms>
			  </urn:authenticateGenericChallengeCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';	

		//$this->log($req,LOG_DEBUG);
		
		$this->cliente_auth->operation = "authenticateGenericChallenge";
		$res = $this->cliente_auth->send($req);
		
		if($this->manejaError($res,$this->cliente_auth)){
			return $res;
		} else {
			return false;
		}	
	}
	
	
	/**
	 * Verifica si un usuario existe en el IDG.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @return boolean Verdadero si existe. Falso si no.
	 */
	function usuarioExiste($login){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		   <soapenv:Header>
			  <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		   </soapenv:Header>
		   <soapenv:Body>
			  <urn:userGetCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
				 <parms>
				 </parms>
			  </urn:userGetCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userGet";
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return true;
		} else {
			return false;
		}
	}
		
	
	/**
	 * Crea un usuario en el IDG
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function crearUsuario($login){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		   <soapenv:Header>
			  <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		   </soapenv:Header>

		   <soapenv:Body>
			  <urn:userCreateCallParms>
			 <userid>'.$this->escapa($login).'</userid>
				 <parms>
					<Group>'.Configure::read('Idg.group').'</Group>
				 </parms>
			  </urn:userCreateCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		
		$this->cliente_admin->operation = "userCreate";
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Elimina un usuario del IDG
	 * 
	 * @deprecated Se omite el proceso de borrado del usuario
	 * @param string $login Identificación del usuario en IDG
	 * @return array|boolean Arreglo con respuesta de IDG. Falso si falla
	 */
	function eliminarUsuario($login){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		   <soapenv:Header>
			  <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		   </soapenv:Header>
		   <soapenv:Body>
			  <urn:userDeleteCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
			  </urn:userDeleteCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		
		$this->cliente_admin->operation = "userDelete";
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Crea la clave de acceso del usuario
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @param string $contrasena Nueva contraseña
	 * @param int $tiempo_caduca Tiempo de caducidad de la contraseña en días
	 * @return array|boolean Arreglo con respuesta de IDG. Falso si falla
	 */
	function crearContrasena($login,$contrasena,$tiempo_caduca){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		   <soapenv:Header>
			  <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		   </soapenv:Header>
		   <soapenv:Body>
			  <urn:userPasswordCreateCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
				 <parms>
					<Password>'.$this->escapa($contrasena).'</Password>
					<AutoGenerate>false</AutoGenerate>
					<DaysToExpiry>'.$this->escapa($tiempo_caduca).'</DaysToExpiry>
					<ChangeRequired>false</ChangeRequired>
					<ClearPasswordHistory>false</ClearPasswordHistory>
					<ClearRetrievablePassword>false</ClearRetrievablePassword>
				 </parms>
			  </urn:userPasswordCreateCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userPasswordCreate";
		
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Realiza el cambio de clave de un usuario y envía un SMS al usuario.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @param string $contrasena Nueva contraseña
	 * @param int $tiempo_caduca Tiempo de caducidad de la contraseña en días
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function cambiarContrasena($login,$contrasena,$tiempo_caduca){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		<soapenv:Header>     
		 <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		</soapenv:Header>
		   <soapenv:Body>
			  <urn:userPasswordSetCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$login.'</userid>
				 <parms>
					<Password>'.$this->escapa($contrasena).'</Password>
					<AutoGenerate>false</AutoGenerate>
					<DaysToExpiry>'.$this->escapa($tiempo_caduca).'</DaysToExpiry>
					<ChangeRequired>false</ChangeRequired>
					<ClearPasswordHistory>false</ClearPasswordHistory>
					<ClearRetrievablePassword>false</ClearRetrievablePassword>
				 </parms>
			  </urn:userPasswordSetCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userPasswordSet";
		
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
		
			if($this->Session->check('usuarios.datos')){
				$usuario = $this->Session->read('usuarios.datos');
				$usuario_id = $usuario['id'];
				$celular = desencriptar($usuario['celular']);
			} else if($this->Session->check('seguridad.olvido')){
				$estado = $this->Session->read('seguridad.olvido');
				$celular = desencriptar($estado['datasource']['celular']);
				$usuario_id = $estado['datasource']['id'];
			}
				
			// Enviar por sms la notificación de que realizó el cambio de clave
		
			$this->Sms->enviar(
				array(
					'numcel'=>$celular,
					'canal1'=>'18',
					'fecha'=>date('Ymd'),
					'hora'=>date('His'),
					'refnum'=>$usuario_id.time()
				)
			);
		
			return $res;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Inicializa la clave de acceso de un usuario.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @param string $contrasena Nueva contraseña del usuario
	 * @param int $tiempo_caduca Tiempo de caducidad de la contraseña
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function inicializaContrasena($login,$contrasena,$tiempo_caduca){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		<soapenv:Header>     
		 <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		</soapenv:Header>
		   <soapenv:Body>
			  <urn:userPasswordSetCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$login.'</userid>
				 <parms>
					<Password>'.$this->escapa($contrasena).'</Password>
					<AutoGenerate>false</AutoGenerate>
					<DaysToExpiry>'.$this->escapa($tiempo_caduca).'</DaysToExpiry>
					<ChangeRequired>false</ChangeRequired>
					<ClearPasswordHistory>true</ClearPasswordHistory>
					<ClearRetrievablePassword>true</ClearRetrievablePassword>
				 </parms>
			  </urn:userPasswordSetCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userPasswordSet";
		
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Realiza el desbloqueo de un usuario del IDG.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @param string $tipo_autenticacion Tipo de autenticación a desbloquear
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function desbloquearUsuario($login, $tipo_autenticacion='PASSWORD'){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		<soapenv:Header>     
		 <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		</soapenv:Header>
		   <soapenv:Body>
			  <urn:userLockoutSetCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
				 <parms>
					<Clearlockout>true</Clearlockout>
					  <AuthenticatorLockoutIds>
						 <item>
							<Name>'.$tipo_autenticacion.'</Name>
						 </item>
					  </AuthenticatorLockoutIds>  					
				 </parms>
			  </urn:userLockoutSetCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userLockoutSet";
		
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}	
	}
	
	
	/**
	 * Realiza el desbloqueo de las preguntas de desafío de un usuario.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function desbloquearQA($login){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		<soapenv:Header>     
		 <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		</soapenv:Header>
		   <soapenv:Body>
			  <urn:userLockoutSetCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
				 <parms>
					<Clearlockout>true</Clearlockout>
					  <AuthenticatorLockoutIds>
						 <item>
							<Name>QA</Name>
						 </item>
					  </AuthenticatorLockoutIds>  					
				 </parms>
			  </urn:userLockoutSetCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userLockoutSet";
		
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}	
	}
	
	
	/**
	 * Almacena las preguntas de desafío de un usuario.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @param array $qa Arreglo con las preguntas de desafío. Donde la clave es la pregunta
	 * y el valor es la respuesta
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function guardarQA($login, $qa){
		$this->getClienteAdm();
		
		$qa_xml = '';
		
		foreach($qa as $pregunta=>$respuesta){
			$qa_xml .=
				'<item>
					<Name>'.$this->escapa($pregunta).'</Name>
					<Value>'.$this->escapa($respuesta).'</Value>
				</item>';
		}
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		   <soapenv:Header>
			  <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		   </soapenv:Header>
		   <soapenv:Body>
			  <urn:userSetCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
				 <parms>
					<QaSecrets>'.$qa_xml.'</QaSecrets>
				 </parms>
			  </urn:userSetCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userSet";
		
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}	
	}
	
	
	/**
	 * Obtiene las preguntas de desafío de un usuario.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function obtenerQA($login){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		   <soapenv:Header>
			  <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		   </soapenv:Header>
		   <soapenv:Body>
			  <urn:userGetCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
				 <parms>
					<getQASecrets>true</getQASecrets>
				 </parms>
			  </urn:userGetCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userGet";
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			$qa = array();
			
			if(!empty($res['QaSecrets']['item'])){
				foreach($res['QaSecrets']['item'] as $i){
					$qa[$i['Name']] = $i['Value'];
				}
			}
			
			return $qa;	
		} else {
			return false;
		}
	}
	
	
	/**
	 * Realiza la creación de la tarjeta de coordenadas.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function crearTdc($login){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		   <soapenv:Header>
			  <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		   </soapenv:Header>
		   <soapenv:Body>
			  <urn:userCardCreateCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
				 <parms>
				 </parms>
			  </urn:userCardCreateCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userCardCreate";
		
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}	
	}
	
	
	/**
	 * Obtiene los datos de la tarjeta de coordenadas.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function obtenerTdc($login){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		<soapenv:Header>     
		 <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		</soapenv:Header>
		   <soapenv:Body>
			  <urn:userCardGetCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
				 <filter>
					<State>
					   <item>PENDING</item>
					   <item>CURRENT</item>
					   <item>HOLD</item>
					   <item>HOLD_PENDING</item>
					</State>
				 </filter>
				 <parms>
					<getGrid>true</getGrid>
				 </parms>
			  </urn:userCardGetCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userCardGet";
		
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}	
	}
	
	
	/**
	 * Realiza la anulación de la tarjeta de coordenadas.
	 * 
	 * @param string $login Identificación del usuario en IDG
	 * @return array|boolean Arreglo con la respuesta de IDG. Falso si falla
	 */
	function anularTdc($login){
		$this->getClienteAdm();
		
		$req =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:entrust.com:ig:adminV8:wsdl">
		<soapenv:Header>     
		 <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <wsse:UsernameToken>
					<wsse:Username>'.desencriptar(Configure::read('Idg.user')).'</wsse:Username>
					<wsse:Password>'.desencriptar(Configure::read('Idg.pwd')).'</wsse:Password>
				 </wsse:UsernameToken>
			  </wsse:Security>
		</soapenv:Header>
		   <soapenv:Body>
			  <urn:userCardSetCallParms>
				 <userid>'.Configure::read('Idg.group').'/'.$this->escapa($login).'</userid>
				 <filter>
					<State>
					   <item>PENDING</item>
					   <item>CURRENT</item>
					   <item>HOLD</item>
					   <item>HOLD_PENDING</item>
					</State>
				 </filter>
				 <parms>
					<State>CANCELED</State>
				 </parms>
			  </urn:userCardSetCallParms>
		   </soapenv:Body>
		</soapenv:Envelope>';
		
		//$this->log($req, LOG_DEBUG);
		
		$this->cliente_admin->operation = "userCardSet";
		
		$res = $this->cliente_admin->send($req);
		
		if($this->manejaError($res,$this->cliente_admin)){
			return $res;
		} else {
			return false;
		}
	}
}

?>