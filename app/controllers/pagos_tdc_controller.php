<?php

/**
 * Controlador para el Pago de Tarjetas de Crédito
 */
class PagosTdcController extends AppController
{
	/**
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array(
		'Usuario',
		'Pagostdc',
		'Bitacora',
		'Afiliacion',
		'AcumuladoTransaccion',
		'TransaccionPendiente',
		'TransaccionJuridicoDetalle'
	);

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session', 'Email', 'Compensacion', 'Credicard');

	/**
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */
	var $helpers = array('Activo', 'Html', 'Session', 'Form', 'Paginator', 'Text', 'Javascript');


	/*** Inicio TDC Propias ***/


	// function ConsultaDeSaldoTdcSoap($cod_cedula = null, $cedula = null)
	// {

	// 	// cedula
	// 	$usuario = $this->Session->read('usuarios.datos');
	// 	$num_cedula = str_pad(desencriptar($usuario['cedula']), 10, '0', STR_PAD_LEFT);

	// 	$cod_cedula = $usuario['tipo_cedula'];

	// 	// 0016528522 varias TDC ARRAY
	// 	// 0017272272 una sola TDC OBJECT

	// 	$soapData = $this->bancoActivoPagoEnLineaTdc();

	// 	$req = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.ws.hub.credicard.com.ve/">
	// 			    <soapenv:Header/>
	// 				<soapenv:Body>
	// 					<ser:ConsultaSaldoenLineaTDC>
	// 						<!--Optional:-->
	// 						<ConsultaSaldoenLineaTDCRequest>
	// 							<!--Optional:-->
	// 							<cedulath>' . $num_cedula . '</cedulath>
	// 							<!--Optional:-->
	// 							<nacionalidadth>' . $cod_cedula . '</nacionalidadth>
	// 						</ConsultaSaldoenLineaTDCRequest>
	// 					</ser:ConsultaSaldoenLineaTDC>
	// 				</soapenv:Body>
	// 			</soapenv:Envelope>';
	// 	if (!isset($this->client)) {
	// 		$this->client = new nusoap_client(Configure::read('Credicard_Consultar_TDC.url'));
	// 		$this->client->timeout = 300;
	// 		$this->client->response_timeout = 300;
	// 	}

	// 	//$username = "bactivo";
	// 	$username = Configure::read('CredicardActivacionTDC.user');
	// 	$password = Configure::read('CredicardActivacionTDC.pwd');
	// 	$authtype = "basic";

	// 	$this->client->setCredentials($username, $password, $authtype);
	// 	$this->client->operation = "ConsultaSaldoenLineaTDC";
	// 	$this->client->soap_defencoding = 'UTF-8';
	// 	$this->client->decode_utf8 = false;

	// 	$res = $this->client->send($req);

	// 	if ($this->client->fault) {

	// 		$this->log('Error técnico:', LOG_ERR);
	// 		$this->log($res, LOG_ERR);

	// 		if (isset($this->controller)) {
	// 			$this->controller->set('error_fatal', _m('error.fatal'));
	// 		}
	// 		return 1;
	// 	} else {

	// 		$err = $this->client->getError();
	// 		if ($err) {

	// 			// $this->log('Error técnico:',LOG_ERR);
	// 			// $this->log($err,LOG_ERR);
	// 			// if(isset($this->controller)){
	// 			// 	$this->controller->set('error_fatal',_m('error.fatal'));					
	// 			return 1;
	// 		}
	// 	}

	// 	// $soapResponse = $this->bancoActivoPagoEnLineaTdcMensajes($res);

	// 	foreach ($res as $r) {

	// 		$this->Session->write('consulta_TDC.soapResponse', $r);

	// 		return $r;
	// 	};
	// }



	/**
	 * Acción para realizar el pago de las tarjetas de Crédito propias 
	 * @param String $volver Variable que indica si viene de la confirmaci?n
	 */
	function propias($volver = null)
	{

		$tdcSaldoSoap = $this->Credicard->consultaDeSaldoTdcSoap(2);
		
		$tdcConsultaMonto = json_encode($tdcSaldoSoap['saldocont']);
		$tdcConsultaTarjeta = trim($tdcSaldoSoap['tarjeta']);
		$tdcConsultaTarjetaEncrip = $tdcSaldoSoap['cuenta2'];
		$tdcConsultaTarjetaCompleta = array(
			$tdcConsultaTarjeta => $tdcConsultaTarjetaEncrip
		);
		
		/* Para validar el acceso al modulo */
		if (!validarTransaccion(100025)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */

		$this->Session->delete('usuarios.descarga.pdf');

		$usuario = $this->Session->read('usuarios.datos');

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.propias');

		$cuentas = $this->Activo->buscarCuentasSaldos();

		$datatdcSaldoSoap = $tdcSaldoSoap;
		
		// Iteracion para mostrar tarjetas en el select
		if (isset($datatdcSaldoSoap['tarjeta']) == true) {

			if($datatdcSaldoSoap['estatustrj'] !== 2 || $datatdcSaldoSoap['estatustrj'] !== 1){
				
				$soapTarjeta = array(trim($datatdcSaldoSoap['tarjeta']) => $datatdcSaldoSoap['cuenta2']);
				$tarjetas = $soapTarjeta;
			}

		} else {
			$tarjetas = array();

			$mensajes = array(
			    "3" => 'Tarjeta con sobrelimite',
			    "5" => 'Tarjeta perdida',
			    "6" => 'Tarjeta robada',
			    "8" => 'Tarjeta en observación',
			    "9" => 'Tarjeta restringida',
			    "10" => 'Tarjeta sin activar',
			    "90" => 'Tarjeta cancelada',
			);

			foreach ($datatdcSaldoSoap as $clave => $tdc) {
				
 				$codeEstatustrj = $tdc['estatustrj'];
				if($codeEstatustrj == '1' || $codeEstatustrj == '2'){

			    if (isset($mensajes[$codeEstatustrj])) {
			        $tarjetas[trim($tdc['tarjeta'])] = $mensajes[$codeEstatustrj] . ': ' . trim($tdc['cuenta2']);
			    } else {
			        $tarjetas[trim($tdc['tarjeta'])] = trim($tdc['cuenta2']);
			    }
				}
			   

			}
		}
		// END Iteracion para mostrar tarjetas en el select

		//Elimina del combo box las tarjetas corporativas en el caso de naturales
		if (empty($usuario['tipo_rif'])) {

			$tarjetas_permitidas = array_flip($tarjetas);

			foreach ($tarjetas_permitidas as $u) {
				$datosProducto = array('NROTDC' => $u, 'NROPARAM' => 4);
				$datos = $this->Activo->validarProducto($datosProducto);
				if (trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1']) == 'TCCH') {
					unset($tarjetas[$u]);
				}
			}
		}

		//Busca las tarjetas corporativas padre para los clientes juridicos
		$tarjetas_corporativas = $this->Activo->buscarTarjetasEmpCorp('TCC');
		$tarjetas_asociadas = array();

		//Incluye las tarjetas corporativas asociadas al combo box de tarjetas (solo juridicos)
		if (!empty($tarjetas_corporativas) && !empty($usuario['tipo_rif'])) {

			$tarjetas_corporativas = array_flip($tarjetas_corporativas);
			//Busca las tarjetas asociadas corporativas
			foreach ($tarjetas_corporativas as $x) {
				$tarjetas_asociadas = $this->Activo->buscarTarjetasEmpCorpAsociadas('C', $x);
				$tarjetas = $tarjetas + $tarjetas_asociadas;
			}
		}

		$soapServicioPago = $this->Credicard->bancoActivoPagoEnLineaTdc(); 

		$this->set(compact('cuentas', 'tarjetas', 'soapServicioPago', 'tdcSaldoSoap' . 'tdcConsultaMonto'));

		if (!empty($this->data['Pagostdc'])) {

			$usuario = $this->Session->read('usuarios.datos');

			// Para usuario jurídicos operadores, obtener su configuración para este tipo de transAcción
			if (
				$usuario['rol_juridico_id'] == 3 ||
				$usuario['rol_juridico_id'] == 5
			) {
				$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					100025,
					$this->data['Pagostdc']['cuentaDebitar']
				);

				if (!$transaccion_detalle) {
					$this->Pagostdc->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
				}

				$transaccion_detalle_tdc = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					array(4, 100025),
					$this->data['Pagostdc']['numeroTDC']
				);

				if (!$transaccion_detalle_tdc) {
					$this->Pagostdc->validationErrors['numeroTDC'] = _m('usuario.no.autorizado');
				}
			}

			if (empty($this->Pagostdc->validationErrors)) {

				if (isset($transaccion_detalle) && isset($transaccion_detalle_tdc)) {
					$this->data['Pagostdc']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];

					if (
						is_numeric($transaccion_detalle['limite']) &&
						is_numeric($transaccion_detalle_tdc['limite'])
					) {
						$limite_cuenta = formatea_monto_fromWs($transaccion_detalle['limite']);
						$limite_tdc = formatea_monto_fromWs($transaccion_detalle_tdc['limite']);
						$this->data['Pagostdc']['monto_limite_usuario'] = min($limite_cuenta, $limite_tdc);
					} elseif (is_numeric($transaccion_detalle['limite'])) {
						$this->data['Pagostdc']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
					} elseif (is_numeric($transaccion_detalle_tdc['limite'])) {
						$this->data['Pagostdc']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle_tdc['limite']);
					} else {
						$this->data['Pagostdc']['monto_limite_usuario'] = '';
					}
				}

				$this->Pagostdc->set($this->data);

				if ($this->Pagostdc->validates(array('fieldList' => array('cuentaDebitar', 'numeroTDC', 'otro_monto', 'tipo_pago', 'monto', 'anioTransaccion', 'codBCV', 'diaTransaccion', 'mesTransaccion', 'numTransaccion')))) {
					if ($this->data['Pagostdc']['tipo_pago'] == 'o') {
						$this->data['Pagostdc']['monto'] = $this->data['Pagostdc']['otro_monto'];
					}

					$this->data['pasos'] = array(
						'Solicitud de Informaci&oacute;n',
						'Confirmaci&oacute;n',
						'Resultado de Operaci&oacute;n'
					);

					$this->Session->write('pagos_tdc.propias', $this->data);

					$this->Session->delete('transaccion.advertencia');

					// En caso de ser un usuario natural o master unico
					if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
						if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Pagostdc'], 100025)) {

							$this->data['titulo'] = 'Pagos Tarjeta de Crédito Mismo Titular en Activo';
							$this->data['mensaje_alerta'] = 'pagotdc.alerta';
							$this->data['url_volver'] = '/pagos_tdc/propias';
							$this->data['url_continuar'] = '/pagos_tdc/propias_confirmacion';
							$this->data['seccion_actual'] = 'pagos.tdc.propias';

							$this->Session->write('transaccion.advertencia', $this->data);
							$this->redirect('transaccion_advertencia');
						}
					}

					$this->redirect('propias_confirmacion');
				}
			}
		} else if (isset($volver) && $this->Session->check('pagos_tdc.propias')) {
			$this->data = $this->Session->read('pagos_tdc.propias');
		}
	}

	/**
	 * Acción para obtener la información de la tarjeta de Crédito seleccionada por el cliente
	 * @param String $tarjeta Número de tarjeta seleccionada por el cliente
	 */
	function obtenerInfo($tarjeta) 
	{
	    $tdcSaldoSoap = $this->Credicard->consultaDeSaldoTdcSoap(2);

	    if($tdcSaldoSoap['saldocont']){
			$tdcConsultaMonto = $tdcSaldoSoap['saldocont'];
			$tdcConsultaPagoMin = $tdcSaldoSoap['pagomin'];  

	        $tdcConsultaMonto = str_replace(".",",",$tdcConsultaMonto);
	        $tdcConsultaPagoMin = str_replace(".",",",$tdcConsultaPagoMin);
	         

	        if($tdcSaldoSoap['saldocont'] > 0.00){
				$response = array(
					'tipo_pago_m' => $tdcConsultaPagoMin,
					'tipo_pago_t' => $tdcConsultaMonto,
					'estatustrj' => 1
				); 
			}else{
				$response = array(
					'tipo_pago_m' => $tdcConsultaPagoMin,
					'tipo_pago_t' => $tdcConsultaMonto,
					'estatustrj' => 3
				);	



			}
		}else{
	        foreach ($tdcSaldoSoap as $data) {
	        	$tdcConsultaMonto = str_replace(".",",",$data['saldocont']);
	        	$tdcConsultaPagoMin = str_replace(".",",",$data['pagomin']);        	
	        	if($data['estatustrj'] < 3){
	        		$tarj = trim($data['tarjeta']);
	        		if(trim($data['tarjeta']) == trim($tarjeta)){


	        			if($data['saldocont'] > 0.00){
							$response = array(
								'tipo_pago_m' => $tdcConsultaPagoMin,
								'tipo_pago_t' => $tdcConsultaMonto,
								'estatustrj' => 1
							);        				
							break;
	        			}else{
							$response = array(
								'tipo_pago_m' => $tdcConsultaPagoMin,
								'tipo_pago_t' => $tdcConsultaMonto,
								'estatustrj' => 3
							);
							break;        				
	        			}
	        		}
	        	}
	        }    
	    }    	
		$this->set('response', $response);	
	}



	/**
	 * Acción de confirmaci?n para el pago de tarjetas de Créditos propias
	 */
	function propias_confirmacion()
	{

		
		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.propias');

		if ($this->Session->check('pagos_tdc.propias')) {
			$this->data = $this->Session->read('pagos_tdc.propias');
		} else {
			$this->redirect('propias');
		}
	}

	/**
	 * Acción para el resultado del pago de tarjetas de Crédito propias, ejecuta el pago.
	 */
	function propias_resultado()
	{


		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.propias');

		if ($this->Session->check('pagos_tdc.propias') && $this->Session->read('usuarios.datos')) {

			$usuario = $this->Session->read('usuarios.datos');
			$this->data = $this->Session->read('pagos_tdc.propias');
			$this->Session->delete('pagos_tdc.propias');

			// Validaci?n anti fraude
			$cuentas = $this->Activo->buscarCuentas();

			if (
				!in_array($this->data['Pagostdc']['cuentaDebitar'], array_keys($cuentas))
			) {
				$this->redirect('/consultas/integral');
			}
			//  Fin validaci?n anti fraude				

			$datos_basicos = $this->Session->read('usuarios.nibpf3010');
			$identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'], 10, '0', STR_PAD_LEFT);

			if (
				empty($usuario['tipo_rif']) ||
				$usuario['rol_juridico_id'] == 2 ||// Master ?nico
				$usuario['rol_juridico_id'] == 6
			) {

				$anioTransaccion = $this->data['Pagostdc']['anioTransaccion'];
				$codBCV = $this->data['Pagostdc']['codBCV'];
				$diaTransaccion = $this->data['Pagostdc']['diaTransaccion'];
				$mesTransaccion = $this->data['Pagostdc']['mesTransaccion'];
				$monto = $this->data['Pagostdc']['monto'];
				$numTransaccion = $this->data['Pagostdc']['numTransaccion'];
				$cuentaDebitar = $this->data['Pagostdc']['cuentaDebitar'];
				$numeroTDC = $this->data['Pagostdc']['numeroTDC'];

                //configurar IP
                $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];  
                if(strpos($ip, ',') != false){
	                $arrayIP = explode(",", $ip);
	                $ip = $arrayIP[0];
                }
				$ip = substr($ip,0,16);
				//fin configurar IP

                //configuracion log llamada AS400
                $mtoTDC = formatea_monto_toWs($monto);
                $inf_logs_tdc = "\n" . date("Y/m/d H:i:s") . " -- PAGO = TERCERO MISMO BANCO -- USUARIO = " . $identificacion . " -- IP = " . $ip . " -- CANAL = " . Configure::read('canal.ib') . "\n";

                $inf_logs_tdc.="TARJETA = " . $numeroTDC . " -- CTA DEBITO = " . substr($this->data['Pagostdc']['cuentaDebitar'], 10) . " -- MONTO = " . $mtoTDC;
                //Fin configuracion log llamada AS400
 
				$res = $this->Activo->invoca(
					'IN098102',
					array(
						'req' => array(
							'IN982USR' => $identificacion,
							'IN982CRNUM' => $numeroTDC,
							'IN982DEBAC' => substr($this->data['Pagostdc']['cuentaDebitar'], 10),
							'IN982PAYAM' => formatea_monto_toWs($monto),
							'IN982CCY' => _p('moneda'),
							'IN982DTE1' => date('d'),
							'IN982DTE2' => date('m'),
							'IN982DTE3' => date('y'),
							'IN982IPACT' => $ip,
							'IN982ENDFL' => 'N',
							'operacion' => 'p',
							'canal' => Configure::read('canal.ib')
						)
					)
				);

                if(!$res){
					$inf_logs_tdc.= "\nAS400 -- RESULTADO =  ERROR AL INVOCAR EL SERVICIO";
					$inf_logs_tdc.= "\n FIN OPERACION";
					$this->log_tdc($inf_logs_tdc);                	
                	$this->redirect('/consultas/integral');
                }				


				// en caso de que la cuenta este en 0
				if (isset($res['IN098102Return']['error'])) {
					$error = intval($res['IN098102Return']['error']['ERNU01'] );
					$descripcion = $res['IN098102Return']['error']['ERNU01'] . ' - ' . $res['IN098102Return']['error']['ERDS01'];
					if(!empty($error)){
						$inf_logs_tdc.= "\nAS400 -- RESULTADO =  ERROR (" .$error . "  " .  $descripcion . ")";
						$inf_logs_tdc.= "\n FIN OPERACION";
						$this->log_tdc($inf_logs_tdc);
					}					
					$this->set(compact('error', 'referencia', 'movimiento', 'soapRes'));
				}
				// end:en caso de que la cuenta este en 0

				if (trim($res['IN098102Return']['error']['ERNU01']) == null && $res['IN098102Return']['error']['ERDF01'] > 0) {
					$inf_logs_tdc.= "\n AS400 -- RESULTADO =  00 DEBITO EXITOSO\n";
					
					$soapRes = $this->Credicard->PagoEnLineaTdcSoap($anioTransaccion, $codBCV, $diaTransaccion, $mesTransaccion, $monto, $numTransaccion, $numeroTDC);

					if ($soapRes['IN982ENDFL'] === 'Y') {

						$inf_logs_tdc.= "\n CREDICARD -- ERROR = " . $soapRes['mensaje'] . " --USUARIO = " . $identificacion . " -- IP = " . $ip . " -- CANAL = " . Configure::read('canal.ib') . "\n";

						$res = $this->Activo->invoca(
							'IN098102',
							array(
								'req' => array(
									'IN982USR' => $identificacion,
									'IN982CRNUM' => $numeroTDC,
									'IN982DEBAC' => substr($this->data['Pagostdc']['cuentaDebitar'], 10),
									'IN982PAYAM' => formatea_monto_toWs($monto),
									'IN982CCY' => _p('moneda'),
									'IN982DTE1' => date('d'),
									'IN982DTE2' => date('m'),
									'IN982DTE3' => date('y'),
									'IN982IPACT' => Configure::read('ip.client'),
									'IN982ENDFL' => $soapRes['IN982ENDFL'],
									'operacion' => 'p',
									'canal' => Configure::read('canal.ib')
								)
							)
						);
                        if($res){
                            $inf_logs_tdc.= "\n AS400 -- RESULTADO =  00 REVERSO EXITOSO";
                        } else {
                            $inf_logs_tdc.= "\n AS400 -- RESULTADO =  ERROR REVERSO";
                        }	
                  		$inf_logs_tdc.= "\n FIN OPERACION";	
                        $this->log_tdc($inf_logs_tdc);

					} else {

						$error = 0;
						$descripcion = "";
						$referencia = $res['IN098102Return']['error']['ERDF01'];
						if (isset($res['IN098102Return']['error'])) {
							$error = intval($res['IN098102Return']['error']['ERNU01']);
							$descripcion = $res['IN098102Return']['error']['ERNU01'] . ' - ' . $res['IN098102Return']['error']['ERDS01'];
						}
						if ($error == 0) {
							$inf_logs_tdc.= "\n CREDICARD -- RESULTADO = 00 -- OPERACION EXITOSA -- USUARIO = " . $identificacion . " -- IP = " . $ip . " -- CANAL = " . Configure::read('canal.ib');
							$inf_logs_tdc.= "\n FIN OPERACION";	
							$this->log_tdc($inf_logs_tdc);							

							
							$this->Bitacora->registraBitacora(
								$usuario['id'],
								100025,
								$this->data['Pagostdc']['cuentaDebitar'],
								$this->data['Pagostdc']['numeroTDC'],
								$this->data['Pagostdc']['monto'],
								$referencia,
								$this->data['Pagostdc']['descripcion'],
								0
							);

							$this->data['Pagostdc']['REFERENCIA'] = $referencia;
							$this->data['Pagostdc']['CONCEPTO'] = 'PAGO TDC ' . enmascaraTDC($this->data['Pagostdc']['numeroTDC']);
							$this->data['Pagostdc']['TIPO'] = 'tdc';
							$this->data['Pagostdc']['NRO_BENEFICIARIO'] = $this->data['Pagostdc']['numeroTDC'];
							$this->data['Pagostdc']['tipoCedulaRif'] = $usuario['tipo_cedula'];
							$this->data['Pagostdc']['nroCedulaRif'] = desencriptar($usuario['cedula']);
							$this->data['Pagostdc']['nombreBeneficiario'] = desencriptar($usuario['nombre']);
							$this->data['Pagostdc']['ESTATUS'] = 'PROCESADO';
							$this->data['Pagostdc']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL C.A.';

							// Genera la data para la descarda de pdf
							$this->Session->write('usuarios.descarga.pdf', $this->data['Pagostdc']);
						} else {
							$this->log('Pago TDC Mismo Titular - ' . $descripcion, LOG_ERR);
							$this->Bitacora->registraBitacora(
								$usuario['id'],
								100025,
								$this->data['Pagostdc']['cuentaDebitar'],
								$this->data['Pagostdc']['numeroTDC'],
								$this->data['Pagostdc']['monto'],
								$referencia,
								$descripcion,
								1
							);
						}

						$this->set('pendiente', false);
					}
				}
			} else if (
				$usuario['rol_juridico_id'] == 3  || // Operador
				$usuario['rol_juridico_id'] == 5     // Operador/Aprobador
			) {
				// En caso de jur?dico, s?lo los operadores y operadores/aprobadores pueden
				// generar una pago. En cuyo caso, se deja pendiente por aprobar

				$error = 0;

				$res = $this->TransaccionPendiente->save(
					array(
						'TransaccionPendiente' => array(
							'usuario_id' => $usuario['id'],
							'tipo' => 'ppm',
							'estatus' => 'pa',
							'fecha_hora_registro' => date('Y-m-d H:i:s'),
							'monto' => formatea_monto_toWs($this->data['Pagostdc']['monto']),
							'aprobaciones_requeridas' => $this->data['Pagostdc']['aprobaciones_requeridas'],
							'tipo_rif' => $usuario['tipo_rif'],
							'rif' => $usuario['rif'],
							'cuenta_origen' => encriptar($this->data['Pagostdc']['cuentaDebitar']),
							'cuenta_destino' => encriptar($this->data['Pagostdc']['numeroTDC']),
							'descripcion' => encriptar($this->data['Pagostdc']['descripcion']),
							'nombre_beneficiario' => encriptar($datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB']),
							'tipo_id_beneficiario' => $usuario['tipo_rif'],
							'id_beneficiario' => $usuario['rif'],
							'email_beneficiario' => $usuario['email_principal']
						)
					),
					false
				);

				if ($res) {
					$error = 0;
					$referencia = $this->TransaccionPendiente->id;
				} else {
					$error = -1;
					$referencia = '';
				}

				$this->set('pendiente', true);
			}

			$this->set(compact('error', 'referencia', 'movimiento', 'soapRes'));
		} else {
			$this->redirect('propias');
		}
	}

	/*** Fin TDC Propias ***/



	/*** Inicio TDC Terceros ***/

	/**
	 * Acción para realizar el pago de las tarjetas de Crédito de terceros 
	 * @param String $volver Variable que indica si viene de la confirmaci?n
	 */
	function terceros($volver = null)
	{

		/* Para validar el acceso al modulo */
		if (!validarTransaccion(100026)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */

		$this->Session->delete('usuarios.descarga.pdf');

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.terceros');

		$cuentas = $this->Activo->buscarCuentasSaldos();

		$this->set(compact('cuentas'));

		if (!empty($this->data['Pagostdc'])) {
			$usuario = $this->Session->read('usuarios.datos');

			// Para usuario jurídicos operadores, obtener su configuración para este tipo de transAcción
			if (
				$usuario['rol_juridico_id'] == 3 ||
				$usuario['rol_juridico_id'] == 5
			) {
				$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					100026,
					$this->data['Pagostdc']['cuentaDebitar']
				);

				if (!$transaccion_detalle) {
					$this->Pagostdc->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
				}
			}

			if (empty($this->Pagostdc->validationErrors)) {
				if (isset($transaccion_detalle)) {
					$this->data['Pagostdc']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
					if (is_numeric($transaccion_detalle['limite'])) {
						$this->data['Pagostdc']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
					} else {
						$this->data['Pagostdc']['monto_limite_usuario'] = '';
					}
				}

				$this->Pagostdc->set($this->data);

				if ($this->Pagostdc->validates(array('fieldList' => array('cuentaDebitar', 'tipoDestinatario')))) {

					$this->Session->write('pagos_tdc.terceros', $this->data);

					if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {
						$this->redirect('terceros_afiliados');
					} else {
						$this->redirect('terceros_noafiliados');
					}
				}
			}
		} elseif (isset($volver) && $this->Session->check('pagos_tdc.terceros')) {
			$this->data = $this->Session->read('pagos_tdc.terceros');
		}
	}

	/**
	 * Acción para realizar el pago de tarjetas de Crédito terceros afiliados
	 */
	function terceros_afiliados()
	{

		$tdcSaldoSoap = $this->Credicard->consultaDeSaldoTdcSoap(2);

		$tdcConsultaMonto = json_encode($tdcSaldoSoap['saldocont']);
		$tdcConsultaTarjeta = trim($tdcSaldoSoap['tarjeta']);
		$tdcConsultaTarjetaEncrip = $tdcSaldoSoap['cuenta2'];
		$tdcConsultaTarjetaCompleta = array(
			$tdcConsultaTarjeta => $tdcConsultaTarjetaEncrip
		);

		if (!$this->Session->check('pagos_tdc.terceros')) $this->redirect('terceros');

		$estado = $this->Session->read('pagos_tdc.terceros');

		$this->layout = 'interna';

		$usuario = $this->Session->read('usuarios.datos');

		// Obtener el l?mite de par?metros
		if (empty($usuario['rif'])) {
			$limite_config = _p('limite_internas_terceros');
		} else {
			$limite_config = _p('limite_internas_terceros_j');
		}

		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
		$monto_limite = '';
		$acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id']);
		$limite_total = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : $limite_config;
		if ($limite_total) {
			$monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_internas_terceros']);
		}
		$limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);
		if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
			$monto_limite = $limite_total = '';
		}
		$this->set(compact('monto_limite'));
		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

		$this->set('seccion_actual', 'pagos.tdc.terceros');

		if (empty($this->data['Pagostdc'])) {
			$this->data = $estado;
		} else if (!isset($this->data['Pagostdc']['paginando'])) { // realiza la verificacion si no viene de la paginacion	

			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
			$this->data['Pagostdc']['monto_limite'] = $monto_limite;
			$this->data['Pagostdc']['limite_total'] = $limite_total;

			if (isset($estado['Pagostdc']['monto_limite_usuario'])) {
				$this->data['Pagostdc']['monto_limite_usuario'] = $estado['Pagostdc']['monto_limite_usuario'];
			}
			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

			$this->Pagostdc->set($this->data);

			if ($this->Pagostdc->validates(
				array(
					'fieldList' => array(
						'numeroTDC',
						'monto',
						'descripcion'
					)
				)
			)) {

				$datosProducto = array('NROTDC' => $this->data['Pagostdc']['numeroTDC'], 'NROPARAM' => 4);
				$datos = $this->Activo->validarProducto($datosProducto);
				$error = intval($datos['NIBPF3010Return']['error']['ERNU01']);

				if ($error != 0) {
					$this->Pagostdc->validationErrors['numeroTDC'] = _m('valor.invalido', 'el Número de tarjeta a acreditar');
				} elseif ($datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '01' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '02' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '03' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '06' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '08') {
					$this->Pagostdc->validationErrors['numeroTDC'] = _m('cuenta.inactiva', 'la tarjeta');
				} else {
					$this->data['Pagostdc']['afiliar'] = 's';

					if (isset($estado['Pagostdc']['aprobaciones_requeridas'])) $this->data['Pagostdc']['aprobaciones_requeridas'] = $estado['Pagostdc']['aprobaciones_requeridas'];

					if (_p('sms.otp.activo') == '0') {
						$validacion_otp['validado'] = true;
					} else {
						$validacion_otp = $this->Session->read('seguridad.validar_otp');
					}

					if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
						if (_p('sms.otp.activo') == '0') {
							$this->data['pasos'] = array(
								'Solicitud de Informaci&oacute;n',
								'Confirmaci&oacute;n',
								'Resultado de Operaci&oacute;n'
							);
							$this->data['paso_final'] = 2;
						} else {
							$this->data['pasos'] = array(
								'Solicitud de Informaci&oacute;n',
								'Confirmaci&oacute;n',
								'C&oacute;digo de Autorizaci&oacute;n',
								'Resultado de Operaci&oacute;n'
							);
							$this->data['paso_final'] = 3;
						}
					} else {
						$this->data['pasos'] = array(
							'Solicitud de Informaci&oacute;n',
							'Confirmaci&oacute;n',
							'Resultado de Operaci&oacute;n'
						);
						$this->data['paso_final'] = 2;
					}

					$this->Session->write('pagos_tdc.terceros', $this->data);

					$this->Session->delete('transaccion.advertencia');

					// En caso de ser un usuario natural o master unico
					if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
						if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Pagostdc'], 100026)) {

							$this->data['titulo'] = 'Pagos Tarjeta de Crédito Tercero en Activo';
							$this->data['mensaje_alerta'] = 'pagotdc.alerta';
							$this->data['url_volver'] = '/pagos_tdc/terceros_afiliados';
							$this->data['url_continuar'] = '/pagos_tdc/terceros_confirmacion';
							$this->data['seccion_actual'] = 'pagos.tdc.terceros';

							$this->Session->write('transaccion.advertencia', $this->data);
							$this->redirect('transaccion_advertencia');
						}
					}

					$this->redirect('terceros_confirmacion');
				}
			}
		}

		$afiliados = $this->Afiliacion->obtenerListaAfiliados($usuario['id'], 'p', 't');

		if ($afiliados) {
			// Empieza c?digo para paginar
			$total_registros = count($afiliados);
			$offset = 0;

			if (
				isset($this->data['Pagostdc']['offset']) &&
				preg_match('/^\d+$/', $this->data['Pagostdc']['offset'])
			) {
				$offset = intval($this->data['Pagostdc']['offset']);
			}

			$resultados = array_slice(
				$afiliados,
				$offset,
				5,
				true
			);
			// Fin c?digo para paginar
		}

		$soapServicioPago = $this->Credicard->bancoActivoPagoEnLineaTdc();

		$this->set(compact('afiliados', 'resultados', 'total_registros', 'soapServicioPago'));
	}

	/**
	 * Acción para realizar el pago de tarjetas de Crédito terceros no afiliados
	 */
	function terceros_noafiliados()
	{
		$tdcSaldoSoap = $this->Credicard->consultaDeSaldoTdcSoap(2);

		$tdcConsultaMonto = json_encode($tdcSaldoSoap['saldocont']);
		$tdcConsultaTarjeta = trim($tdcSaldoSoap['tarjeta']);
		$tdcConsultaTarjetaEncrip = $tdcSaldoSoap['cuenta2'];
		$tdcConsultaTarjetaCompleta = array(
			$tdcConsultaTarjeta => $tdcConsultaTarjetaEncrip
		);

		if (!$this->Session->check('pagos_tdc.terceros')) $this->redirect('terceros');

		$estado = $this->Session->read('pagos_tdc.terceros');

		$this->layout = 'interna';
		$usuario = $this->Session->read('usuarios.datos');

		// Obtener el l?mite de par?metros
		if (empty($usuario['rif'])) {
			$limite_config = _p('limite_internas_terceros');
		} else {
			$limite_config = _p('limite_internas_terceros_j');
		}

		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
		$monto_limite = '';
		$acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id']);
		$limite_total = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : $limite_config;
		if ($limite_total) {
			$monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_internas_terceros']);
		}
		$limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);
		if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
			$monto_limite = $limite_total = '';
		}

		$soapServicioPago = $this->Credicard->bancoActivoPagoEnLineaTdc();

		// INICIO: DEBUGG
		// FINAL: DEBUGG

		$this->set(compact('monto_limite', 'limite_total', 'soapServicioPago'));
		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

		$this->set('seccion_actual', 'pagos.tdc.terceros');

		if (empty($this->data['Pagostdc'])) {
			$this->data = $estado;
		} else {

			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
			$this->data['Pagostdc']['monto_limite'] = $monto_limite;

			if (empty($usuario['rif'])) {
				$this->data['Pagostdc']['limite_total'] = $limite_total;
			} else {
				$this->data['Pagostdc']['monto_maximo'] = $limite_total;
			}

			if (isset($estado['Pagostdc']['monto_limite_usuario'])) {
				$this->data['Pagostdc']['monto_limite_usuario'] = $estado['Pagostdc']['monto_limite_usuario'];
			}
			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

			$this->Pagostdc->set($this->data);

			if ($this->Pagostdc->validates(
				array(
					'fieldList' => array(
						'numeroTDC',
						'emailBeneficiario',
						'monto',
						'descripcion',
						'alias',
						'afiliar',
						'monto_maximo',
						'anioTransaccion',
						'codBCV',
						'diaTransaccion',
						'mesTransaccion',
						'numTransaccion',
						'nombreBeneficiario',
						'tipoCedulaRif',
						'nroCedulaRif'
					)
				)
			)) {
				if ($this->data['Pagostdc']['afiliar'] == 's' && $this->Afiliacion->checkAfiliacion($usuario['id'], $this->data['Pagostdc']['numeroTDC'])) {
					$this->Pagostdc->validationErrors['numeroTDC'] = 'producto_nodisponible';
				} else if (isset($this->data['Pagostdc']['alias']) && $this->data['Pagostdc']['alias'] != "" && $this->Afiliacion->checkAlias($usuario['id'], $this->data['Pagostdc']['alias'])) {
					$this->Pagostdc->validationErrors['alias'] = 'alias_nodisponible';
				} else {
					$datosProducto = array('NROTDC' => $this->data['Pagostdc']['numeroTDC'], 'NROPARAM' => 4);
					$datos = $this->Activo->validarProducto($datosProducto);
					$error = $datos['NIBPF3010Return']['error']['ERNU01'];

					if ($error != 0) {
						$this->Pagostdc->validationErrors['numeroTDC'] = _m('valor.invalido', 'el Número de tarjeta');
					} elseif (empty($usuario['rif']) && (trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1']) == 'TCEP' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1']) == 'TCEH' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1']) == 'TCCP' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1']) == 'TCCH')) {
						$this->Pagostdc->validationErrors['numeroTDC'] = _m('pago.nopermitido');
					} elseif (!empty($usuario['rif']) && (trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1']) == 'TCEH' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1']) == 'TCCP')) {
						$this->Pagostdc->validationErrors['numeroTDC'] = _m('pago.nopermitido');
					} elseif ($datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '01' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '02' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '03' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '06' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC'] != '08') {
						$this->Pagostdc->validationErrors['numeroTDC'] = _m('cuenta.inactiva', 'la tarjeta');
					} else {
						$cedula = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'], 1, 9);

						$this->data['Pagostdc']['tipoCedulaRif'] = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'], 0, 1);

						if ($this->data['Pagostdc']['tipoCedulaRif'] == 'G' || $this->data['Pagostdc']['tipoCedulaRif'] == 'J') {
							// $this->data['Pagostdc']['nombreBeneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
							$this->data['Pagostdc']['nombreBeneficiario'];
						} else {
							// $this->data['Pagostdc']['nombreBeneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'] . ' ' . $datos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'];
							$this->data['Pagostdc']['nombreBeneficiario'];
						}

						// $this->data['Pagostdc']['nroCedulaRif'] = $cedula;
						$this->data['Pagostdc']['nroCedulaRif'];
						$this->data['Pagostdc']['banco'] = 'BANCO ACTIVO';
						if (isset($estado['Pagostdc']['aprobaciones_requeridas'])) $this->data['Pagostdc']['aprobaciones_requeridas'] = $estado['Pagostdc']['aprobaciones_requeridas'];

						$validacion_otp = $this->Session->read('seguridad.validar_otp');
						if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
							$this->data['pasos'] = array(
								'Solicitud de Informaci&oacute;n',
								'Confirmaci&oacute;n',
								'C&oacute;digo de Autorizaci&oacute;n', 
								'Resultado de Operaci&oacute;n'
							);
							$this->data['paso_final'] = 3;
						} else {
							$this->data['pasos'] = array(
								'Solicitud de Informaci&oacute;n',
								'Confirmaci&oacute;n',
								'Resultado de Operaci&oacute;n'
							);
							$this->data['paso_final'] = 2;
						}

						$this->Session->write('pagos_tdc.terceros', $this->data);

						$this->Session->delete('transaccion.advertencia');

						// En caso de ser un usuario natural o master unico
						if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
							if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Pagostdc'], 100026)) {
								$this->data['titulo'] = 'Pagos Tarjeta de Crédito Tercero en Activo';
								$this->data['mensaje_alerta'] = 'pagotdc.alerta';
								$this->data['url_volver'] = '/pagos_tdc/terceros_noafiliados';
								$this->data['url_continuar'] = '/pagos_tdc/terceros_confirmacion';
								$this->data['seccion_actual'] = 'pagos.tdc.terceros';

								$this->Session->write('transaccion.advertencia', $this->data);
								$this->redirect('transaccion_advertencia');
							}
						}
						$this->redirect('terceros_confirmacion');
					}
				}
			}
		}
	}

	/**
	 * Acción de confirmaci?n para el pago de tarjetas de Créditos de terceros
	 */
	function terceros_confirmacion()
	{

		if (!$this->Session->check('pagos_tdc.terceros')) $this->redirect('terceros');

		$estado = $this->Session->read('pagos_tdc.terceros');

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.terceros');

		$usuario = $this->Session->read('usuarios.datos');

		$this->set('juridico', $usuario);

		$this->data = $estado;

		if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {

			// busca la informacion del afiliado
			$afiliado = $this->Afiliacion->obtenerDataAfiliado($usuario['id'], $this->data['Pagostdc']['numeroTDC']);
			if (!$afiliado) {
				$this->redirect('terceros');
			}

			$this->data['Pagostdc']['nombreBeneficiario'] = desencriptar($afiliado['nombre_beneficiario']);
			$this->data['Pagostdc']['tipoCedulaRif'] = $afiliado['tipo_cedula'];
			$this->data['Pagostdc']['nroCedulaRif'] = desencriptar($afiliado['cedula']);
			$this->data['Pagostdc']['emailBeneficiario'] = desencriptar($afiliado['email']);
			$this->data['Pagostdc']['banco'] = 'BANCO ACTIVO';
			$this->data['Pagostdc']['id'] = $afiliado['id'];
			$this->Session->write('pagos_tdc.terceros', $this->data);
		} else {
		
			$this->data['Pagostdc']['nombreBeneficiario'];
			$this->data['Pagostdc']['tipoCedulaRif'];
			$this->data['Pagostdc']['nroCedulaRif'];
			$this->data['Pagostdc']['id'] = null;
			$this->Session->write('pagos_tdc.terceros', $this->data);
		}
	}

	/**
	 * Acción para el resultado del pago de tarjetas de Crédito de terceros, ejecuta el pago.
	 */
	function terceros_resultado()
	{

		if (!$this->Session->check('pagos_tdc.terceros')) $this->redirect('terceros');

		$estado = $this->Session->read('pagos_tdc.terceros');

		$usuario = $this->Session->read('usuarios.datos');

		$this->data = $estado;

		// Validaci?n anti fraude
		$cuentas = $this->Activo->buscarCuentas();

		if (
			!in_array($this->data['Pagostdc']['cuentaDebitar'], array_keys($cuentas))
		) {
			$this->redirect('/consultas/integral');
		}
		//  Fin validaci?n anti fraude								

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.terceros');

		if ($this->Session->check('pagos_tdc.terceros')) {


			$usuario = $this->Session->read('usuarios.datos');
			$this->data = $this->Session->read('pagos_tdc.terceros');

			$this->set('juridico', $usuario['tipo_rif']);

			$datos_basicos = $this->Session->read('usuarios.nibpf3010');
			$identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'], 10, '0', STR_PAD_LEFT);

			if (
				empty($usuario['tipo_rif']) ||
				$usuario['rol_juridico_id'] == 2 ||// Master ?nico
				$usuario['rol_juridico_id'] == 6
			) {
				if (empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2) {

                    if (_p('sms.otp.activo') == '0' && $this->data['Pagostdc']['tipoDestinatario'] == 'a') {
                        $validacion_otp['validado'] = true;
                    } else {
                        $validacion_otp = $this->Session->read('seguridad.validar_otp');
                    }

                    if (
                            (
                            $validacion_otp == null ||
                            !isset($validacion_otp['validado']) ||
                            !$validacion_otp['validado']
                            )
                    ) {
                        if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {
                            $url_volver = '/pagos_tdc/terceros_afiliados';
                        } else {
                            $url_volver = '/pagos_tdc/terceros_noafiliados';
                        }

                        $this->Session->write(
                                'seguridad.validar_otp', array(
                                    'url_aceptar' => '/pagos_tdc/terceros_resultado',
                                    'url_volver' => $url_volver,
                                    'layout' => 'interna',
                                    'titulo' => 'Pagos Tarjeta de Cr&eacutedito Tercero en Activo',
                                    'pasos' => $estado['pasos'],
                                    'paso_actual' => 2,
                                    'seccion_actual' => 'pagos.tdc.terceros'
                                )
                        );

                        $this->redirect('/seguridad/genera_otp');
                    }
                    
                    
                }else{

                    $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

                    if(
                        (
                            $validacion_tdc == null ||
                            !isset($validacion_tdc['validado']) ||
                            !$validacion_tdc['validado']
                        )
                    ){
                        if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {
                            $url_volver = '/pagos_tdc/terceros_afiliados';
                        } else {
                            $url_volver = '/pagos_tdc/terceros_noafiliados';
                        }

                        $this->Session->write(
                                'seguridad.validar_tdc',
                                array(
                                    'url_aceptar' => '/pagos_tdc/terceros_resultado',
                                    'url_volver' => $url_volver,
                                    'layout' => 'interna',
                                    'titulo' => 'Pagos Tarjeta de Cr&eacutedito Tercero en Activo',
                                    'pasos' => $estado['pasos'],
                                    'paso_actual' => 2,
                                    'seccion_actual' => 'pagos.tdc.terceros'
                                )
                        );

                                $this->redirect('/seguridad/validar_tdc');
                    }



                }

				$anioTransaccion = $this->data['Pagostdc']['anioTransaccion'];

				$codBCV = $this->data['Pagostdc']['codBCV'];
				$diaTransaccion = $this->data['Pagostdc']['diaTransaccion'];
				$mesTransaccion = $this->data['Pagostdc']['mesTransaccion'];
				$monto = $this->data['Pagostdc']['monto'];
				$numTransaccion = $this->data['Pagostdc']['numTransaccion'];
				$cuentaDebitar = $this->data['Pagostdc']['cuentaDebitar'];
				$numeroTDC = $this->data['Pagostdc']['numeroTDC'];

                //configurar IP
                $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];  

                if(strpos($ip, ',') != false){
	                $arrayIP = explode(",", $ip);
	                $ip = $arrayIP[0];
                }
				$ip = substr($ip,0,16);
				//fin configurar IP

                //configuracion log llamada AS400
                $mtoTDC = formatea_monto_toWs($monto);
                $inf_logs_tdc = "\n" . date("Y/m/d H:i:s") . " -- PAGO = TERCERO MISMO BANCO -- USUARIO = " . $identificacion . " -- IP = " . $ip . " -- CANAL = " . Configure::read('canal.ib') . "\n";

                $inf_logs_tdc.="TARJETA = " . $numeroTDC . " -- CTA DEBITO = " . substr($this->data['Pagostdc']['cuentaDebitar'], 10) . " -- MONTO = " . $mtoTDC;
                //Fin configuracion log llamada AS400

				$res = $this->Activo->invoca(
					'IN098102',
					array(
						'req' => array(
							'IN982USR' => $identificacion,
							'IN982CRNUM' => $numeroTDC,
							'IN982DEBAC' => substr($this->data['Pagostdc']['cuentaDebitar'], 10),
							'IN982PAYAM' => formatea_monto_toWs($monto),
							'IN982CCY' => _p('moneda'),
							'IN982DTE1' => date('d'),
							'IN982DTE2' => date('m'),
							'IN982DTE3' => date('y'),
							'IN982IPACT' => $ip,
							'IN982ENDFL' => 'N',
							'operacion' => 't',
							'canal' => Configure::read('canal.ib')
						)
					)
				);


                if(!$res){

					$inf_logs_tdc.= "\nAS400 -- RESULTADO =  ERROR AL INVOCAR EL SERVICIO";
					$inf_logs_tdc.= "\n FIN OPERACION";
					$this->log_tdc($inf_logs_tdc);

                	$this->redirect('/consultas/integral');
                }

				if (isset($res['IN098102Return']['error'])) {
					$error = intval($res['IN098102Return']['error']['ERNU01']);
					$descripcion = $res['IN098102Return']['error']['ERNU01'] . ' - ' . $res['IN098102Return']['error']['ERDS01'];
					if(!empty($error)){
						$inf_logs_tdc.= "\nAS400 -- RESULTADO =  ERROR (" .$error . "  " .  $descripcion . ")";
						$inf_logs_tdc.= "\n FIN OPERACION";
						$this->log_tdc($inf_logs_tdc);
					}
					$this->set(compact('error', 'referencia', 'movimiento', 'soapRes'));
				}

				if (trim($res['IN098102Return']['error']['ERNU01']) == null && $res['IN098102Return']['error']['ERDF01'] > 0) {
				
				    $inf_logs_tdc.= "\n AS400 -- RESULTADO =  00 DEBITO EXITOSO\n";

					$soapRes = $this->Credicard->PagoEnLineaTdcSoap($anioTransaccion, $codBCV, $diaTransaccion, $mesTransaccion, $monto, $numTransaccion, $numeroTDC);
				
					if ($soapRes['IN982ENDFL'] === 'Y') {

						$inf_logs_tdc.= "\n CREDICARD -- ERROR = " . $soapRes['mensaje'] . " --USUARIO = " . $identificacion . " -- IP = " . $ip . " -- CANAL = " . Configure::read('canal.ib') . "\n";

						$res = $this->Activo->invoca(
							'IN098102',
							array(
								'req' => array(
									'IN982USR' => $identificacion,
									'IN982CRNUM' => $numeroTDC,
									'IN982DEBAC' => substr($this->data['Pagostdc']['cuentaDebitar'], 10),
									'IN982PAYAM' => formatea_monto_toWs($monto),
									'IN982CCY' => _p('moneda'),
									'IN982DTE1' => date('d'),
									'IN982DTE2' => date('m'),
									'IN982DTE3' => date('y'),
									'IN982IPACT' => Configure::read('ip.client'),
									'IN982ENDFL' => $soapRes['IN982ENDFL'],
									'operacion' => 'p',
									'canal' => Configure::read('canal.ib')
								)
							)
						);


                        if($res){
                            $inf_logs_tdc.= "\n AS400 -- RESULTADO =  00 REVERSO EXITOSO";
                        } else {
                            $inf_logs_tdc.= "\n AS400 -- RESULTADO =  ERROR REVERSO";
                        }

                        $inf_logs_tdc.= "\n FIN OPERACION";	
                        $this->log_tdc($inf_logs_tdc);

					} else {
						$error = 0;
						$descripcion = "";
						$error_afiliacion = false;
						$referencia = $res['IN098102Return']['error']['ERDF01'];
						if (isset($res['IN098102Return']['error'])) {
							$error = intval($res['IN098102Return']['error']['ERNU01']);
							$descripcion = $res['IN098102Return']['error']['ERNU01'] . ' - ' . $res['IN098102Return']['error']['ERDS01'];
						}

						if ($error == 0) {
							$inf_logs_tdc.= "\n CREDICARD -- RESULTADO = 00 -- OPERACION EXITOSA -- USUARIO = " . $identificacion . " -- IP = " . $ip . " -- CANAL = " . Configure::read('canal.ib');
							$inf_logs_tdc.= "\n FIN OPERACION";	
							$this->log_tdc($inf_logs_tdc);							


							/** ACTUALIZA EL ACUMULADO DEL DIA **/
							$this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', $this->data['Pagostdc']['monto']);
							/** ACTUALIZA EL ACUMULADO DEL DIA **/

							$this->Bitacora->registraBitacora(
								$usuario['id'],
								100026,
								$this->data['Pagostdc']['cuentaDebitar'],
								$this->data['Pagostdc']['numeroTDC'],
								$this->data['Pagostdc']['monto'],
								$referencia,
								$this->data['Pagostdc']['descripcion'],
								0,
								NULL,
								$this->data['Pagostdc']['tipoCedulaRif'],
								$this->data['Pagostdc']['nroCedulaRif'],
								$this->data['Pagostdc']['nombreBeneficiario']

							);

							$this->data['Pagostdc']['REFERENCIA'] = $referencia;
							$this->data['Pagostdc']['CONCEPTO'] = 'PAGO TDC ' . enmascaraTDC($this->data['Pagostdc']['numeroTDC']);
							$this->data['Pagostdc']['TIPO'] = 'tdc';
							$this->data['Pagostdc']['NRO_BENEFICIARIO'] = $this->data['Pagostdc']['numeroTDC'];
							$this->data['Pagostdc']['ESTATUS'] = 'PROCESADO';
							$this->data['Pagostdc']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL C.A.';

							// Genera la data para la descarda de pdf
							$this->Session->write('usuarios.descarga.pdf', $this->data['Pagostdc']);

							// Email Orden
							// Email Options
							$this->Email->smtpOptions = Configure::read('email.smtp');
							$this->Email->delivery	= 'smtp';
							$this->Email->from = Configure::read('email.from');
							$this->Email->to = desencriptar($usuario['email_principal']);
							$this->Email->subject = 'Notificacion Pago Tarjeta de Credito';
							$this->Email->sendAs = 'html';

							// Informacion del correo
							$this->Email->template	= 'pagotdc_ordenante';
							$tipo_accion = 'Pago Tarjeta de Credito';
							$tipo_afiliacion = 'Tercero en Activo';
							$nombreBeneficiario = $this->data['Pagostdc']['nombreBeneficiario'];
							$this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

							// Envio del correo
							$this->Email->send();

							// Email Beneficiario
							// Email Options
							$this->Email->reset();
							$this->Email->smtpOptions = Configure::read('email.smtp');
							$this->Email->delivery	= 'smtp';
							$this->Email->from = Configure::read('email.from');
							$this->Email->to = $this->data['Pagostdc']['emailBeneficiario'];
							$this->Email->subject = 'Notificacion Pago';
							$this->Email->sendAs = 'html';

							// Informacion del correo
							$this->Email->template	= 'pagotdc_beneficiario';
							$this->set(compact('usuario'));

							// Envio del correo
							$this->Email->send();
						} else {
							$this->log('Pago TDC Mismo Titular - ' . $descripcion, LOG_ERR);
							$this->Bitacora->registraBitacora(
								$usuario['id'],
								100026,
								$this->data['Pagostdc']['cuentaDebitar'],
								$this->data['Pagostdc']['numeroTDC'],
								$this->data['Pagostdc']['monto'],
								$referencia,
								$descripcion,
								1
							);
						}

						// Si la TDC no existe realiza la afiliacion
						if ($error != 800 && $error != 182 && $error != 8120 && $error != 1616 && $error != 9920 && $error != 8412 && $this->data['Pagostdc']['afiliar'] == 's') {
							$error_afiliacion = !$this->Afiliacion->afiliar(
								$usuario['id'],
								'p',
								't',
								$this->data['Pagostdc']['alias'],
								$this->data['Pagostdc']['numeroTDC'],
								$this->data['Pagostdc']['nombreBeneficiario'],
								$this->data['Pagostdc']['tipoCedulaRif'],
								$this->data['Pagostdc']['nroCedulaRif'],
								$this->data['Pagostdc']['emailBeneficiario'],
								$this->data['Pagostdc']['monto_maximo'],
								$usuario['tipo_rif'],
								$usuario['rif'],
								$this->data['Pagostdc']['id']
							);

							if (!$error_afiliacion && $this->data['Pagostdc']['tipoDestinatario'] == 'n') {

								// Registra en bitacora
								$this->Bitacora->registraBitacora(
									$usuario['id'],
									14,
									NULL,
									$this->data['Pagostdc']['numeroTDC'],
									$this->data['Pagostdc']['monto_maximo']
								);

								// Email Options
								$this->Email->reset();
								$this->Email->smtpOptions = Configure::read('email.smtp');
								$this->Email->delivery	= 'smtp';
								$this->Email->from = Configure::read('email.from');
								$this->Email->to = desencriptar($usuario['email_principal']);
								$this->Email->subject = 'Notificacion Afiliacion';
								$this->Email->sendAs = 'html';

								// Informacion del correo
								$this->Email->template	= 'afiliacion_producto';
								$tipo_accion = 'Pago Tarjeta de Credito';
								$tipo_afiliacion = 'Tercero en Activo';
								$nombreBeneficiario = $this->data['Pagostdc']['nombreBeneficiario'];
								$this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

								// Envio del correo
								$this->Email->send();
							} else {
								$this->Bitacora->registraBitacora(
									$usuario['id'],
									14,
									NULL,
									$this->data['Pagostdc']['numeroTDC'],
									$this->data['Pagostdc']['monto_maximo'],
									null,
									$descripcion,
									1
								);
							}
						}

						$this->set('pendiente', false);
					}
				}
			} else if (
				$usuario['rol_juridico_id'] == 3  || // Operador
				$usuario['rol_juridico_id'] == 5     // Operador/Aprobador
			) {
				// En caso de jur?dico, s?lo los operadores y operadores/aprobadores pueden
				// generar una pago. En cuyo caso, se deja pendiente por aprobar

				$error = 0;

				//Solicitar TC si esta afiliando la cuenta
				if ($this->data['Pagostdc']['afiliar'] == 's' && $this->data['Pagostdc']['tipoDestinatario'] == 'n') {

					$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

					if (
						(
							$validacion_tdc == null ||
							!isset($validacion_tdc['validado']) ||
							!$validacion_tdc['validado']
						)
					) {
						$this->Session->write(
							'seguridad.validar_tdc',
							array(
								'url_aceptar' => '/pagos_tdc/terceros_resultado',
								'layout' => 'interna',
								'titulo' => 'Pago Tarjeta Crédito Tercero en Activo',
								'pasos' => array(),
								'paso_actual' => 2,
								'seccion_actual' => 'pagos.tdc.terceros'
							)
						);

						$this->redirect('/seguridad/validar_tdc');
					}
				}

				$res = $this->TransaccionPendiente->save(
					array(
						'TransaccionPendiente' => array(
							'usuario_id' => $usuario['id'],
							'tipo' => 'ptm',
							'estatus' => 'pa',
							'fecha_hora_registro' => date('Y-m-d H:i:s'),
							'monto' => formatea_monto_toWs($this->data['Pagostdc']['monto']),
							'aprobaciones_requeridas' => $this->data['Pagostdc']['aprobaciones_requeridas'],
							'tipo_rif' => $usuario['tipo_rif'],
							'rif' => $usuario['rif'],
							'cuenta_origen' => encriptar($this->data['Pagostdc']['cuentaDebitar']),
							'cuenta_destino' => encriptar($this->data['Pagostdc']['numeroTDC']),
							'descripcion' => encriptar($this->data['Pagostdc']['descripcion']),
							'nombre_beneficiario' => encriptar($this->data['Pagostdc']['nombreBeneficiario']),
							'tipo_id_beneficiario' => $this->data['Pagostdc']['tipoCedulaRif'],
							'id_beneficiario' => encriptar($this->data['Pagostdc']['nroCedulaRif']),
							'email_beneficiario' => encriptar($this->data['Pagostdc']['emailBeneficiario']),
							'alias' => isset($this->data['Pagostdc']['alias']) ? encriptar($this->data['Pagostdc']['alias']) : null
						)
					),
					false
				);

				if ($res) {
					$error = 0;
					$referencia = $this->TransaccionPendiente->id;
				} else {
					$error = -1;
					$referencia = '';
				}

				$this->set('pendiente', true);

				if ($this->data['Pagostdc']['afiliar'] == 's') {
					$error_afiliacion = !$this->Afiliacion->afiliar(
						$usuario['id'],
						'p',
						't',
						$this->data['Pagostdc']['alias'],
						$this->data['Pagostdc']['numeroTDC'],
						$this->data['Pagostdc']['nombreBeneficiario'],
						$this->data['Pagostdc']['tipoCedulaRif'],
						$this->data['Pagostdc']['nroCedulaRif'],
						$this->data['Pagostdc']['emailBeneficiario'],
						$this->data['Pagostdc']['monto_maximo'],
						$usuario['tipo_rif'],
						$usuario['rif'],
						$this->data['Pagostdc']['id']
					);

					if (!$error_afiliacion && $this->data['Pagostdc']['tipoDestinatario'] == 'n') {

						// Registra en bitacora
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo']
						);

						// Email Options
						$this->Email->reset();
						$this->Email->smtpOptions = Configure::read('email.smtp');
						$this->Email->delivery	= 'smtp';
						$this->Email->from = Configure::read('email.from');
						$this->Email->to = desencriptar($usuario['email_principal']);
						$this->Email->subject = 'Notificacion Afiliacion';
						$this->Email->sendAs = 'html';

						// Informacion del correo
						$this->Email->template	= 'afiliacion_producto';
						$tipo_accion = 'Pago Tarjeta de Credito';
						$tipo_afiliacion = 'Tercero en Activo';
						$nombreBeneficiario = $this->data['Pagostdc']['nombreBeneficiario'];
						$this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

						// Envio del correo
						$this->Email->send();
					} else {
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo'],
							null,
							$descripcion,
							1
						);
					}
				} else {
					$error_afiliacion = false;
				}
			}

			$this->set(compact('error', 'error_afiliacion', 'referencia', 'soapRes'));
			$this->Session->delete('pagos_tdc.terceros');
		} else {
			$this->redirect('terceros');
		}
	}

	/*** Fin TDC Terceros ***/



	/*** Inicio TDC Otros Bancos propias ***/

	/**
	 * Acción para realizar el pago de las tarjetas de Crédito mismo titular otros bancos 
	 * @param string $volver Variable que indica si viene de la confirmaci?n
	 */
	function otros($volver = null)
	{

		/* Para validar el acceso al modulo */
		if (!validarTransaccion(100027)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */

		$this->Session->delete('usuarios.descarga.pdf');

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.otros');

		if (!empty($this->data['Pagostdc'])) {
			$usuario = $this->Session->read('usuarios.datos');

			// Para usuario jurídicos operadores, obtener su configuración para este tipo de transAcción
			if (
				$usuario['rol_juridico_id'] == 3 ||
				$usuario['rol_juridico_id'] == 5
			) {
				$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					100027,
					$this->data['Pagostdc']['cuentaDebitar']
				);

				if (!$transaccion_detalle) {
					$this->Pagostdc->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
				}
			}

			if (empty($this->Pagostdc->validationErrors)) {
				if (isset($transaccion_detalle)) {
					$this->data['Pagostdc']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
					if (is_numeric($transaccion_detalle['limite'])) {
						$this->data['Pagostdc']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
					} else {
						$this->data['Pagostdc']['monto_limite_usuario'] = '';
					}
				}
				$this->Pagostdc->set($this->data);

				if ($this->Pagostdc->validates(
					array(
						'fieldList' => array(
							'cuentaDebitar',
							'tipoDestinatario'
						)
					)
				)) {

					$this->Session->write('pagos_tdc.otros', $this->data);

					if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {
						$this->redirect('otros_afiliada');
					} else {
						$this->redirect('otros_noafiliada');
					}
				}
			}
		} elseif (isset($volver) && $this->Session->check('pagos_tdc.otros')) {
			$this->data = $this->Session->read('pagos_tdc.otros');
		}
		$cuentas = $this->Activo->buscarCuentasSaldos();

		$this->set(compact('cuentas'));
	}

	/**
	 * Acción para realizar el pago de tarjetas de Crédito afiliadas mismo titular otros bancos 
	 */
	function otros_afiliada()
	{

		if (!$this->Session->check('pagos_tdc.otros')) $this->redirect('otros');

		$estado = $this->Session->read('pagos_tdc.otros');

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.otros');

		$usuario = $this->Session->read('usuarios.datos');

		// Obtener el l?mite de par?metros
		if (empty($usuario['rif'])) {
			$limite_config = _p('limite_externas');
		} else {
			$limite_config = _p('limite_externas_j');
		}

		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
		$monto_limite = '';
		$fecha_acumulado = $this->Activo->consultaFechaCompensacion();
		$acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id'], $fecha_acumulado);
		$limite_total = $usuario['limite_externas'] ? $usuario['limite_externas'] : $limite_config;
		if ($limite_total) {
			$monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_externas']);
		}
		$limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);
		if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
			$monto_limite = $limite_total = '';
		}
		$this->set(compact('monto_limite'));
		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

		if (empty($this->data['Pagostdc'])) {
			$this->data = $estado;
		} else if (!isset($this->data['Pagostdc']['paginando'])) {

			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
			$this->data['Pagostdc']['monto_limite'] = $monto_limite;
			$this->data['Pagostdc']['limite_total'] = $limite_total;

			if (isset($estado['Pagostdc']['monto_limite_usuario'])) {
				$this->data['Pagostdc']['monto_limite_usuario'] = $estado['Pagostdc']['monto_limite_usuario'];
			}
			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

			$this->Pagostdc->set($this->data);

			if ($this->Pagostdc->validates(
				array(
					'fieldList' => array(
						'numeroTDC',
						'monto',
						'descripcion'
					)
				)
			)) {
				$this->data['Pagostdc']['afiliar'] = 's';
				if (isset($estado['Pagostdc']['aprobaciones_requeridas'])) $this->data['Pagostdc']['aprobaciones_requeridas'] = $estado['Pagostdc']['aprobaciones_requeridas'];

				if (_p('sms.otp.activo') == '0') {
					$validacion_otp['validado'] = true;
				} else {
					$validacion_otp = $this->Session->read('seguridad.validar_otp');
				}

				if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
					if (_p('sms.otp.activo') == '0') {
						$this->data['pasos'] = array( 
							'Solicitud de Informaci&oacute;n',
							'Confirmaci&oacute;n',
							'Resultado de Operaci&oacute;n'
						);
						$this->data['paso_final'] = 2;
					} else {
						$this->data['pasos'] = array(
							'Solicitud de Informaci&oacute;n',
							'Confirmaci&oacute;n',
							'C&oacute;digo de Autorizaci&oacute;n',
							'Resultado de Operaci&oacute;n'
						);
						$this->data['paso_final'] = 3;
					}
				} else {

					$this->data['pasos'] = array(
						'Solicitud de Informaci&oacute;n',
						'Confirmaci&oacute;n',
						'Resultado de Operaci&oacute;n'
					);
					$this->data['paso_final'] = 2;
				}
				$this->data['Pagostdc']['fecha_acumulado'] = $fecha_acumulado;
				$this->Session->write('pagos_tdc.otros', $this->data);

				$this->Session->delete('transaccion.advertencia');

				// En caso de ser un usuario natural o master unico
				if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
					if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Pagostdc'], 100027)) {

						$this->data['titulo'] = 'Pago Tarjeta Crédito Mismo Titular Otro Banco';
						$this->data['mensaje_alerta'] = 'pagotdc.alerta';
						$this->data['url_volver'] = '/pagos_tdc/otros_afiliada';
						$this->data['url_continuar'] = '/pagos_tdc/otros_confirmacion';
						$this->data['seccion_actual'] = 'pagos.tdc.otros';

						$this->Session->write('transaccion.advertencia', $this->data);
						$this->redirect('transaccion_advertencia');
					}
				}

				$this->redirect('otros_confirmacion');
			}
		}

		$afiliados_temp = $this->Afiliacion->obtenerListaAfiliados($usuario['id'], 'p', 'ob');
		$afiliados = array();

		if ($afiliados_temp) {

			// Para buscar el banco de los afiliados
			foreach ($afiliados_temp as $a) {
				$tdc = str_pad(desencriptar($a['Afiliacion']['numero_cuenta']), 20, '0', STR_PAD_RIGHT);
				$valorTarjeta = array('TNROTARJ' => $tdc);
				$datos = $this->Activo->validarBIN($valorTarjeta);
				$a['Afiliacion']['banco'] = '';
				if (!isset($datos['NIB090501Return']['error'])) {
					$a['Afiliacion']['banco'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
				}

				$afiliados[] = $a;
			}
			// Para buscar el banco de los afiliados

			// Empieza c?digo para paginar
			$total_registros = count($afiliados);
			$offset = 0;

			if (
				isset($this->data['Pagostdc']['offset']) &&
				preg_match('/^\d+$/', $this->data['Pagostdc']['offset'])
			) {
				$offset = intval($this->data['Pagostdc']['offset']);
			}

			$resultados = array_slice(
				$afiliados,
				$offset,
				5,
				true
			);
			// Fin c?digo para paginar
		}

		$this->set(compact('afiliados', 'resultados', 'total_registros'));
	}

	/**
	 * Acción para realizar el pago de tarjetas de Crédito no afiliadas mismo titular otros bancos
	 */
	function otros_noafiliada()
	{

		if (!$this->Session->check('pagos_tdc.otros')) $this->redirect('otros');

		$estado = $this->Session->read('pagos_tdc.otros');

		$this->layout = 'interna';

		$usuario = $this->Session->read('usuarios.datos');

		// Obtener el l?mite de par?metros
		if (empty($usuario['rif'])) {
			$limite_config = _p('limite_externas');
		} else {
			$limite_config = _p('limite_externas_j');
		}

		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
		$monto_limite = '';
		$fecha_acumulado = $this->Activo->consultaFechaCompensacion();
		$acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id'], $fecha_acumulado);
		$limite_total = $usuario['limite_externas'] ? $usuario['limite_externas'] : $limite_config;

		if ($limite_total) {
			$monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_externas']);
		}
		$limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);
		if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
			$monto_limite = $limite_total = '';
		}
		$this->set(compact('monto_limite', 'limite_total'));
		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

		$this->set('seccion_actual', 'pagos.tdc.otros');

		if (empty($this->data['Pagostdc'])) {
			$this->data = $estado;
		} else {

			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
			$this->data['Pagostdc']['monto_limite'] = $monto_limite;

			if (empty($usuario['rif'])) {
				$this->data['Pagostdc']['limite_total'] = $limite_total;
			} else {
				$this->data['Pagostdc']['monto_maximo'] = $limite_total;
			}

			if (isset($estado['Pagostdc']['monto_limite_usuario'])) {
				$this->data['Pagostdc']['monto_limite_usuario'] = $estado['Pagostdc']['monto_limite_usuario'];
			}
			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

			$this->Pagostdc->set($this->data);

			if ($this->Pagostdc->validates(
				array(
					'fieldList' => array(
						'numeroTDC',
						'monto',
						'descripcion',
						'alias',
						'afiliar',
						'monto_maximo'
					)
				)
			)) {
				$tdc = str_pad($this->data['Pagostdc']['numeroTDC'], 20, '0', STR_PAD_RIGHT);
				$datosProducto = array('TNROTARJ' => $tdc);

				if ($this->data['Pagostdc']['afiliar'] == 's' && $this->Afiliacion->checkAfiliacion($usuario['id'], $this->data['Pagostdc']['numeroTDC'])) {
					$this->Pagostdc->validationErrors['numeroTDC'] = 'producto_nodisponible';
				} else if (isset($this->data['Pagostdc']['alias']) && $this->data['Pagostdc']['alias'] != "" && $this->Afiliacion->checkAlias($usuario['id'], $this->data['Pagostdc']['alias'])) {
					$this->Pagostdc->validationErrors['alias'] = 'alias_nodisponible';
				} else {
					$datos = $this->Activo->validarBIN($datosProducto);
					$error = intval($datos['NIB090501Return']['error']['ERNU01']);

					if ($error == 0) {

						/** Para identificar si es un usuario juridico **/
						if ($usuario['tipo_rif']) {
							$this->data['Pagostdc']['tipoCedulaRif'] = $usuario['tipo_rif'];
							$this->data['Pagostdc']['nroCedulaRif'] = desencriptar($usuario['rif']);
							$datos_basicos = $this->Session->read('usuarios.nibpf3010');
							$this->data['Pagostdc']['nombreBeneficiario'] = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
						} else {
							$this->data['Pagostdc']['tipoCedulaRif'] = $usuario['tipo_cedula'];
							$this->data['Pagostdc']['nroCedulaRif'] = desencriptar($usuario['cedula']);
							$this->data['Pagostdc']['nombreBeneficiario'] = desencriptar($usuario['nombre']);
						}
						/** Para identificar si es un usuario juridico **/

						$this->data['Pagostdc']['banco'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
						$this->data['Pagostdc']['numeroBanco'] = trim(substr($datos['NIB090501Return']['NIB090501']['TBANCO'], 3));
						if (isset($estado['Pagostdc']['aprobaciones_requeridas'])) $this->data['Pagostdc']['aprobaciones_requeridas'] = $estado['Pagostdc']['aprobaciones_requeridas'];

						$validacion_otp = $this->Session->read('seguridad.validar_otp');
						if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
							$this->data['pasos'] = array(
								'Solicitud de informaci&oacute;n',
								'Confirmaci&oacute;n',
								'C&oacute;digo de Autorizaci&oacute;n',
								'Resultado de Operaci&oacute;n'
							);
							$this->data['paso_final'] = 3;
						} else {

							$this->data['pasos'] = array(
								'Solicitud de informaci&oacute;n',
								'Confirmaci&oacute;n',
								'Resultado de Operaci&oacute;n'
							);
							$this->data['paso_final'] = 2;
						}
						$this->data['Pagostdc']['fecha_acumulado'] = $fecha_acumulado;
						$this->Session->write('pagos_tdc.otros', $this->data);

						$this->Session->delete('transaccion.advertencia');

						// En caso de ser un usuario natural o master unico
						if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
							if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Pagostdc'], 100027)) {

								$this->data['titulo'] = 'Pago Tarjeta Crédito Mismo Titular Otro Banco';
								$this->data['mensaje_alerta'] = 'pagotdc.alerta';
								$this->data['url_volver'] = '/pagos_tdc/otros_noafiliada';
								$this->data['url_continuar'] = '/pagos_tdc/otros_confirmacion';
								$this->data['seccion_actual'] = 'pagos.tdc.otros';

								$this->Session->write('transaccion.advertencia', $this->data);
								$this->redirect('transaccion_advertencia');
							}
						}

						$this->redirect('otros_confirmacion');
					} else if ($error == 9986) {
						$this->Pagostdc->validationErrors['numeroTDC'] = _m('numero.bin');
					}
				}
			}
		}
	}

	/**
	 * Acción de confirmaci?n para el pago de tarjetas de Créditos mismo titular otros bancos
	 */
	function otros_confirmacion()
	{

		if (!$this->Session->check('pagos_tdc.otros')) $this->redirect('otros');

		$estado = $this->Session->read('pagos_tdc.otros');

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.otros');

		$this->data = $estado;

		$usuario = $this->Session->read('usuarios.datos');

		$this->set('juridico', $usuario);

		// Si viene de afiliados realiza la busqueda de los datos...
		if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {

			$afiliado = $this->Afiliacion->obtenerDataAfiliado($usuario['id'], $this->data['Pagostdc']['numeroTDC']);

			if (!$afiliado) {
				$this->redirect('otros');
			}

			$this->data['Pagostdc']['nombreBeneficiario'] = desencriptar($afiliado['nombre_beneficiario']);
			$this->data['Pagostdc']['tipoCedulaRif'] = $afiliado['tipo_cedula'];
			$this->data['Pagostdc']['nroCedulaRif'] = desencriptar($afiliado['cedula']);
			$this->data['Pagostdc']['emailBeneficiario'] = desencriptar($afiliado['email']);
			$this->data['Pagostdc']['id'] = $afiliado['id'];

			// Para buscar el banco
			$tdc = str_pad($this->data['Pagostdc']['numeroTDC'], 20, '0', STR_PAD_RIGHT);
			$valorTarjeta = array('TNROTARJ' => $tdc);
			$datos = $this->Activo->validarBIN($valorTarjeta);
			$this->data['Pagostdc']['banco'] = '';
			if (!isset($datos['NIB090501Return']['error'])) {
				$this->data['Pagostdc']['numeroBanco'] = trim(substr($datos['NIB090501Return']['NIB090501']['TBANCO'], 3));
				$this->data['Pagostdc']['banco'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
			}
			// Para buscar el banco

			$this->Session->write('pagos_tdc.otros', $this->data);
		} else {
			$this->data['Pagostdc']['id'] = null;
			$this->Session->write('pagos_tdc.otros', $this->data);
		}
	}

	/**
	 * Acción para el resultado del pago de tarjetas de Crédito mismo titular otros bancos, ejecuta el pago.
	 */
	function otros_resultado()
	{

		if (!$this->Session->check('pagos_tdc.otros')) $this->redirect('otros');

		$estado = $this->Session->read('pagos_tdc.otros');

		$usuario = $this->Session->read('usuarios.datos');

		$this->data = $estado;

		// Validaci?n anti fraude
		$cuentas = $this->Activo->buscarCuentas();

		if (
			!in_array($this->data['Pagostdc']['cuentaDebitar'], array_keys($cuentas))
		) {
			$this->redirect('/consultas/integral');
		}
		//  Fin validaci?n anti fraude				

		if ($this->Session->check('pagos_tdc.otros')) {

			$this->layout = 'interna';

			$this->set('seccion_actual', 'pagos.tdc.otros');

			$this->data = $this->Session->read('pagos_tdc.otros');

			$usuario = $this->Session->read('usuarios.datos');

			$this->set('juridico', $usuario['tipo_rif']);

			$y = substr($this->data['Pagostdc']['numeroBanco'], 3);
			$banco = str_pad($y, 9, '0', STR_PAD_LEFT);

			$cedula = $this->data['Pagostdc']['nroCedulaRif'];
			if (strlen($cedula) < 9) {
				$cedula = str_pad($cedula, 9, '0', STR_PAD_LEFT);
			}

			/** Para identificar si es un usuario juridico **/
			if ($usuario['tipo_rif']) {
				$identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
			} else {
				$identificacion = desencriptar($usuario['cedula']);
			}

			/** Para identificar si es un usuario juridico **/

			if (
				empty($usuario['tipo_rif']) ||
				$usuario['rol_juridico_id'] == 2 ||// Master ?nico
				$usuario['rol_juridico_id'] == 6
			) {

				if (empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2) {

                    if (_p('sms.otp.activo') == '0' && $this->data['Pagostdc']['tipoDestinatario'] == 'a') {
                        $validacion_otp['validado'] = true;
                    } else {
                        $validacion_otp = $this->Session->read('seguridad.validar_otp');
                    }

                    if (
                            (
                            $validacion_otp == null ||
                            !isset($validacion_otp['validado']) ||
                            !$validacion_otp['validado']
                            )
                    ) {
                        if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {
                            $url_volver = '/pagos_tdc/otros_afiliada';
                        } else {
                            $url_volver = '/pagos_tdc/otros_noafiliada';
                        }

                        $this->Session->write(
                                'seguridad.validar_otp', array(
                                    'url_aceptar' => '/pagos_tdc/otros_resultado',
                                    'url_volver' => $url_volver,
                                    'layout' => 'interna',
                                    'titulo' => 'Pagos Tarjeta de Crédito Mismo Titular Otro Banco',
                                    'pasos' => $estado['pasos'],
                                    'paso_actual' => 2,
                                    'seccion_actual' => 'pagos.tdc.otros'
                                )
                        );

                        $this->redirect('/seguridad/genera_otp');
                    }
                }else{

                    $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

                    if(
                        (
                            $validacion_tdc == null ||
                            !isset($validacion_tdc['validado']) ||
                            !$validacion_tdc['validado']
                        )
                    ){
                        if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {
                            $url_volver = '/pagos_tdc/otros_afiliada';
                        } else {
                            $url_volver = '/pagos_tdc/otros_noafiliada';
                        }

                        $this->Session->write(
                                'seguridad.validar_tdc',
                                array(
                                    'url_aceptar' => '/pagos_tdc/otros_resultado',
                                    'url_volver' => $url_volver,
                                    'layout' => 'interna',
                                    'titulo' => 'Pagos Tarjeta de Crédito Mismo Titular Otro Banco',
                                    'pasos' => $estado['pasos'],
                                    'paso_actual' => 2,
                                    'seccion_actual' => 'pagos.tdc.otros'
                                )
                        );

                                $this->redirect('/seguridad/validar_tdc');
                    }
                }


                $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];  

                if(strpos($ip, ',') != false){
	                $arrayIP = explode(",", $ip);
	                $ip = $arrayIP[0];
                }
				$ip = substr($ip,0,16);


				$res = $this->Activo->invoca(
					'INEXTDSJV',
					array(
						'req' => array(

							'INEFRMACC' => trim(substr($this->data['Pagostdc']['cuentaDebitar'], 10)),
							'INEBNFACC' => trim($this->data['Pagostdc']['numeroTDC']),
							'INEAMT' => formatea_monto_toWs($this->data['Pagostdc']['monto']),
							'INEBNFAD1' => substr(trim($this->data['Pagostdc']['nombreBeneficiario']), 0, 30),
							'INEBNFAD2' => $this->data['Pagostdc']['tipoCedulaRif'] . $cedula,
							'INEBNKAD3' => _p('moneda'),
							'INECCY' => _p('moneda'),
							'INEDTP1' => '0225-TARJETA DE CREDITO OTRO BANCO',
							'INEDTP2' => trim($this->data['Pagostdc']['descripcion']),
							'INEDTP3' => trim($this->data['Pagostdc']['descripcion']),
							'INEBNKSN' => substr($this->data['Pagostdc']['banco'], 0, 20),
							'INEINTAD4' => _p('moneda'),
							'INEVDT1' => date('d'),
							'INEVDT2' => date('m'),
							'INEVDT3' => date('y'),
							'INEXPMTV' => _p('via_pago_internet'),
							'INEBNKID' => $banco,
							'INEBNKAD1' => substr($this->data['Pagostdc']['banco'], 0, 20),
							/*'INEBNKAD2'=>'Caracas', //trim($dataBanco['BNKCTY']),
							'INEBNKAD3'=>'Caracas DC',  //trim($dataBanco['BNKCTY']).' '.trim($dataBanco['BNKSTA']),*/
							'INEBNKAD4' => _p('moneda'),
							'INECKN' => '0',
							'INESFMT' => '0',
							'INEDIP' => $ip,
							'operacion' => 'p',
							'canal' => Configure::read('canal.ib')
						)
					)
				);

                if(!$res){
                	$this->redirect('/consultas/integral');
                }


				$error = -1;
				$descripcion = "Error inesperado, transAcción no disponible.";
				$error_afiliacion = false;
				$referencia = $res['INEXTDSJVReturn']['error']['ERDF01'];

				if (isset($res['INEXTDSJVReturn'])) {
					$error = intval($res['INEXTDSJVReturn']['error']['ERRNUM']);
					$error = ($error == 0) ? $error : intval($res['INEXTDSJVReturn']['error']['ERNU01']);
					$descripcion = $res['INEXTDSJVReturn']['error']['ERNU01'] . ' - ' . $res['INEXTDSJVReturn']['error']['ERDS01'];
				}

				if ($error == 0) {

					/** ACTUALIZA EL ACUMULADO DEL DIA SEGUN LA FRANJA DE COMPENSACION**/
					$this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'e', $this->data['Pagostdc']['monto'], $this->data['Pagostdc']['fecha_acumulado']);
					/** ACTUALIZA EL ACUMULADO DEL DIA SEGUN LA FRANJA DE COMPENSACION**/

					$this->Bitacora->registraBitacora(
						$usuario['id'],
						100027,
						$this->data['Pagostdc']['cuentaDebitar'],
						$this->data['Pagostdc']['numeroTDC'],
						$this->data['Pagostdc']['monto'],
						$referencia,
						$this->data['Pagostdc']['descripcion'],
						0
					);

					$this->data['Pagostdc']['REFERENCIA'] = $referencia;
					$this->data['Pagostdc']['CONCEPTO'] = 'PAGO TDC ' . enmascaraTDC($this->data['Pagostdc']['numeroTDC']);
					$this->data['Pagostdc']['TIPO'] = 'tdc';
					$this->data['Pagostdc']['NRO_BENEFICIARIO'] = $this->data['Pagostdc']['numeroTDC'];
					$this->data['Pagostdc']['ESTATUS'] = 'EN PROCESO';

					// Genera la data para la descarda de pdf
					$this->Session->write('usuarios.descarga.pdf', $this->data['Pagostdc']);

					// Email Orden
					// Email Options
					$this->Email->smtpOptions = Configure::read('email.smtp');
					$this->Email->delivery	= 'smtp';
					$this->Email->from		= Configure::read('email.from');
					$this->Email->to		= desencriptar($usuario['email_principal']);
					$this->Email->subject	= 'Notificacion Pago Tarjeta de Credito';
					$this->Email->sendAs	= 'html';

					// Informacion del correo
					$this->Email->template	= 'pagotdc_ordenante';
					$tipo_accion = 'Pago Tarjeta de Credito';
					$tipo_afiliacion = 'Mismo Titular Otros Bancos';
					$this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion'));

					// Envio del correo
					$this->Email->send();
				} else {
					$this->log('Pago TDC Terceros Otros Bancos- ' . $descripcion, LOG_ERR);
					$this->Bitacora->registraBitacora(
						$usuario['id'],
						100027,
						$this->data['Pagostdc']['cuentaDebitar'],
						$this->data['Pagostdc']['numeroTDC'],
						$this->data['Pagostdc']['monto'],
						$referencia,
						$descripcion,
						1
					);
				}

				if ($error != 182 && $error != 2149 && $error != 1913 && $error != 862 && $error != 1912 && $error != -1 && $this->data['Pagostdc']['afiliar'] == 's') {

					$error_afiliacion = !$this->Afiliacion->afiliar(
						$usuario['id'],
						'p',
						'ob',
						$this->data['Pagostdc']['alias'],
						$this->data['Pagostdc']['numeroTDC'],
						$this->data['Pagostdc']['nombreBeneficiario'],
						$this->data['Pagostdc']['tipoCedulaRif'],
						$this->data['Pagostdc']['nroCedulaRif'],
						desencriptar($usuario['email_principal']),
						$this->data['Pagostdc']['monto_maximo'],
						$usuario['tipo_rif'],
						$usuario['rif'],
						$this->data['Pagostdc']['id']
					);

					if (!$error_afiliacion && $this->data['Pagostdc']['tipoDestinatario'] == 'n') {

						// Registra en bitacora
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo']
						);

						// Email Options
						$this->Email->reset();
						$this->Email->smtpOptions = Configure::read('email.smtp');
						$this->Email->delivery	= 'smtp';
						$this->Email->from = Configure::read('email.from');
						$this->Email->to = desencriptar($usuario['email_principal']);
						$this->Email->subject = 'Notificacion Afiliacion';
						$this->Email->sendAs = 'html';

						// Informacion del correo
						$this->Email->template	= 'afiliacion_producto';
						$tipo_accion = 'Pago Tarjeta de Credito';
						$tipo_afiliacion = 'Mismo Titular Otros Bancos';
						$nombreBeneficiario = $this->data['Pagostdc']['nombreBeneficiario'];
						$this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

						// Envio del correo
						$this->Email->send();
					} else {
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo'],
							NULL,
							$descripcion,
							1
						);
					}
				}

				$this->set('pendiente', false);
			} else if (
				$usuario['rol_juridico_id'] == 3  || // Operador
				$usuario['rol_juridico_id'] == 5     // Operador/Aprobador
			) {
				// En caso de jur?dico, s?lo los operadores y operadores/aprobadores pueden
				// generar una pago. En cuyo caso, se deja pendiente por aprobar

				$error = 0;

				//Solicitar TC si esta afiliando la cuenta
				if ($this->data['Pagostdc']['afiliar'] == 's' && $this->data['Pagostdc']['tipoDestinatario'] == 'n') {

					$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

					if (
						(
							$validacion_tdc == null ||
							!isset($validacion_tdc['validado']) ||
							!$validacion_tdc['validado']
						)
					) {
						$this->Session->write(
							'seguridad.validar_tdc',
							array(
								'url_aceptar' => '/pagos_tdc/otros_resultado',
								'layout' => 'interna',
								'titulo' => 'Pago Tarjeta Crédito Mismo Titular Otro Banco',
								'pasos' => array(),
								'paso_actual' => 2,
								'seccion_actual' => 'pagos.tdc.otros'
							)
						);

						$this->redirect('/seguridad/validar_tdc');
					}
				}

				$res = $this->TransaccionPendiente->save(
					array(
						'TransaccionPendiente' => array(
							'usuario_id' => $usuario['id'],
							'tipo' => 'ppo',
							'estatus' => 'pa',
							'fecha_hora_registro' => date('Y-m-d H:i:s'),
							'monto' => formatea_monto_toWs($this->data['Pagostdc']['monto']),
							'aprobaciones_requeridas' => $this->data['Pagostdc']['aprobaciones_requeridas'],
							'tipo_rif' => $usuario['tipo_rif'],
							'rif' => $usuario['rif'],
							'cuenta_origen' => encriptar($this->data['Pagostdc']['cuentaDebitar']),
							'cuenta_destino' => encriptar($this->data['Pagostdc']['numeroTDC']),
							'descripcion' => encriptar($this->data['Pagostdc']['descripcion']),
							'nombre_beneficiario' => encriptar($this->data['Pagostdc']['nombreBeneficiario']),
							'tipo_id_beneficiario' => $this->data['Pagostdc']['tipoCedulaRif'],
							'id_beneficiario' => encriptar($this->data['Pagostdc']['nroCedulaRif']),
							'email_beneficiario' => $usuario['email_principal'],
							'alias' => isset($this->data['Pagostdc']['alias']) ? encriptar($this->data['Pagostdc']['alias']) : null
						)
					),
					false
				);

				if ($res) {
					$error = 0;
					$referencia = $this->TransaccionPendiente->id;
				} else {
					$error = -1;
					$referencia = '';
				}

				$this->set('pendiente', true);

				if ($this->data['Pagostdc']['afiliar'] == 's') {
					$error_afiliacion = !$this->Afiliacion->afiliar(
						$usuario['id'],
						'p',
						'ob',
						$this->data['Pagostdc']['alias'],
						$this->data['Pagostdc']['numeroTDC'],
						$this->data['Pagostdc']['nombreBeneficiario'],
						$this->data['Pagostdc']['tipoCedulaRif'],
						$this->data['Pagostdc']['nroCedulaRif'],
						desencriptar($usuario['email_principal']),
						$this->data['Pagostdc']['monto_maximo'],
						$usuario['tipo_rif'],
						$usuario['rif'],
						$this->data['Pagostdc']['id']
					);

					if (!$error_afiliacion && $this->data['Pagostdc']['tipoDestinatario'] == 'n') {

						// Registra en bitacora
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo']
						);

						// Email Options
						$this->Email->reset();
						$this->Email->smtpOptions = Configure::read('email.smtp');
						$this->Email->delivery	= 'smtp';
						$this->Email->from		= Configure::read('email.from');
						$this->Email->to		= desencriptar($usuario['email_principal']);
						$this->Email->subject	= 'Notificacion Afiliacion';
						$this->Email->sendAs	= 'html';

						// Informacion del correo
						$this->Email->template	= 'afiliacion_producto';
						$tipo_accion = 'Pago Tarjeta de Credito';
						$tipo_afiliacion = 'Mismo Titular Otros Bancos';
						$nombreBeneficiario = $this->data['Pagostdc']['nombreBeneficiario'];
						$this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

						// Envio del correo
						$this->Email->send();
					} else {
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo'],
							NULL,
							$descripcion,
							1
						);
					}
				} else {
					$error_afiliacion = false;
				}
			}

			$this->set(compact('error', 'error_afiliacion', 'referencia'));
			$this->Session->delete('pagos_tdc.otros');
		} else {
			$this->redirect('otros');
		}
	}

	/*** Fin TDC Otros Bancos propias ***/


	/*** Inicio TDC Otros Bancos terceros ***/

	/**
	 * Acción para realizar el pago de las tarjetas de Crédito terceros otros bancos 
	 * @param string $volver Variable que indica si viene de la confirmaci?n
	 */
	function terceros_otros($volver = null)
	{

		/* Para validar el acceso al modulo */
		if (!validarTransaccion(100028)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */

		$this->Session->delete('usuarios.descarga.pdf');

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.terceros.otros');

		if (!empty($this->data['Pagostdc'])) {
			$usuario = $this->Session->read('usuarios.datos');

			// Para usuario jurídicos operadores, obtener su configuración para este tipo de transAcción
			if (
				$usuario['rol_juridico_id'] == 3 ||
				$usuario['rol_juridico_id'] == 5
			) {
				$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					100028,
					$this->data['Pagostdc']['cuentaDebitar']
				);

				if (!$transaccion_detalle) {
					$this->Pagostdc->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
				}
			}

			if (empty($this->Pagostdc->validationErrors)) {
				if (isset($transaccion_detalle)) {
					$this->data['Pagostdc']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
					if (is_numeric($transaccion_detalle['limite'])) {
						$this->data['Pagostdc']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
					} else {
						$this->data['Pagostdc']['monto_limite_usuario'] = '';
					}
				}

				$this->Pagostdc->set($this->data);

				if ($this->Pagostdc->validates(
					array(
						'fieldList' => array(
							'cuentaDebitar',
							'tipoDestinatario'
						)
					)
				)) {

					$this->Session->write('pagos_tdc.terceros_otros', $this->data);

					if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {
						$this->redirect('terceros_otros_afiliada');
					} else {
						$this->redirect('terceros_otros_noafiliada');
					}
				}
			}
		} elseif (isset($volver) && $this->Session->check('pagos_tdc.terceros_otros')) {
			$this->data = $this->Session->read('pagos_tdc.terceros_otros');
		}
		$cuentas = $this->Activo->buscarCuentasSaldos();

		$this->set(compact('cuentas'));
	}

	/**
	 * Acción para realizar el pago de tarjetas de Crédito afiliados terceros otros bancos
	 */
	function terceros_otros_afiliada()
	{
		if (!$this->Session->check('pagos_tdc.terceros_otros')) $this->redirect('terceros_otros');

		$estado = $this->Session->read('pagos_tdc.terceros_otros');

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.terceros.otros');

		$usuario = $this->Session->read('usuarios.datos');

		// Obtener el l?mite de par?metros
		if (empty($usuario['rif'])) {
			$limite_config = _p('limite_externas');
		} else {
			$limite_config = _p('limite_externas_j');
		}

		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
		$monto_limite = '';
		$fecha_acumulado = $this->Activo->consultaFechaCompensacion();
		$acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id'], $fecha_acumulado);
		$limite_total = $usuario['limite_externas'] ? $usuario['limite_externas'] : $limite_config;

		if ($limite_total) {
			$monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_externas']);
		}
		$limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);
		if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
			$monto_limite = $limite_total = '';
		}
		$this->set(compact('monto_limite'));
		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

		if (empty($this->data['Pagostdc'])) {
			$this->data = $estado;
		} else if (!isset($this->data['Pagostdc']['paginando'])) {

			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
			$this->data['Pagostdc']['monto_limite'] = $monto_limite;
			$this->data['Pagostdc']['limite_total'] = $limite_total;

			if (isset($estado['Pagostdc']['monto_limite_usuario'])) {
				$this->data['Pagostdc']['monto_limite_usuario'] = $estado['Pagostdc']['monto_limite_usuario'];
			}
			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

			$this->Pagostdc->set($this->data);

			if ($this->Pagostdc->validates(
				array(
					'fieldList' => array(
						'numeroTDC',
						'monto',
						'descripcion'
					)
				)
			)) {
				$this->data['Pagostdc']['afiliar'] = 's';
				if (isset($estado['Pagostdc']['aprobaciones_requeridas'])) $this->data['Pagostdc']['aprobaciones_requeridas'] = $estado['Pagostdc']['aprobaciones_requeridas'];

				if (_p('sms.otp.activo') == '0') {
					$validacion_otp['validado'] = true;
				} else {
					$validacion_otp = $this->Session->read('seguridad.validar_otp');
				}

				if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
					if (_p('sms.otp.activo') == '0') {
						$this->data['pasos'] = array(
							'Solicitud de Informaci&oacute;n',
							'Confirmaci&oacute;n',
							'Resultado de Operaci&oacute;n'
						);
						$this->data['paso_final'] = 2;
					} else {
						$this->data['pasos'] = array(
							'Solicitud de Informaci&oacute;n',
							'Confirmaci&oacute;n',
							'C&oacute;digo de Autorizaci&oacute;n',
							'Resultado de Operaci&oacute;n'
						);
						$this->data['paso_final'] = 3;
					}
				} else {
					$this->data['pasos'] = array(
						'Solicitud de Informaci&oacute;n',
						'Confirmaci&oacute;n',
						'Resultado de Operaci&oacute;n'
					);
					$this->data['paso_final'] = 2;
				}
				$this->data['Pagostdc']['fecha_acumulado'] = $fecha_acumulado;
				$this->Session->write('pagos_tdc.terceros_otros', $this->data);

				$this->Session->delete('transaccion.advertencia');

				// En caso de ser un usuario natural o master unico
				if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
					if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Pagostdc'], 100028)) {

						$this->data['titulo'] = 'Pago Tarjeta Crédito Tercero en Otro Banco';
						$this->data['mensaje_alerta'] = 'pagotdc.alerta';
						$this->data['url_volver'] = '/pagos_tdc/terceros_otros_afiliada';
						$this->data['url_continuar'] = '/pagos_tdc/terceros_otros_confirmacion';
						$this->data['seccion_actual'] = 'pagos.tdc.terceros.otros';

						$this->Session->write('transaccion.advertencia', $this->data);
						$this->redirect('transaccion_advertencia');
					}
				}

				$this->redirect('terceros_otros_confirmacion');
			}
		}

		$afiliados_temp = $this->Afiliacion->obtenerListaAfiliados($usuario['id'], 'p', 'tob');
		$afiliados = array();
		if ($afiliados_temp) {

			// Para buscar el banco de los afiliados
			foreach ($afiliados_temp as $a) {
				$tdc = str_pad(desencriptar($a['Afiliacion']['numero_cuenta']), 20, '0', STR_PAD_RIGHT);
				$valorTarjeta = array('TNROTARJ' => $tdc);
				$datos = $this->Activo->validarBIN($valorTarjeta);
				$a['Afiliacion']['banco'] = '';
				if (!isset($datos['NIB090501Return']['error'])) {
					$a['Afiliacion']['banco'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
				}

				$afiliados[] = $a;
			}
			// Para buscar el banco de los afiliados


			// Empieza c?digo para paginar
			$total_registros = count($afiliados);
			$offset = 0;

			if (
				isset($this->data['Pagostdc']['offset']) &&
				preg_match('/^\d+$/', $this->data['Pagostdc']['offset'])
			) {
				$offset = intval($this->data['Pagostdc']['offset']);
			}

			$resultados = array_slice(
				$afiliados,
				$offset,
				5,
				true
			);
			// Fin c?digo para paginar
		}

		$this->set(compact('afiliados', 'resultados', 'total_registros'));
	}

	/**
	 * Acción para realizar el pago de tarjetas de Crédito no afiliados terceros otros bancos
	 */
	function terceros_otros_noafiliada()
	{
		if (!$this->Session->check('pagos_tdc.terceros_otros')) $this->redirect('terceros_otros');

		$estado = $this->Session->read('pagos_tdc.terceros_otros');

		$this->layout = 'interna';
		$usuario = $this->Session->read('usuarios.datos');

		// Obtener el l?mite de par?metros
		if (empty($usuario['rif'])) {
			$limite_config = _p('limite_externas');
		} else {
			$limite_config = _p('limite_externas_j');
		}

		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
		$monto_limite = '';
		$fecha_acumulado = $this->Activo->consultaFechaCompensacion();
		$acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id'], $fecha_acumulado);
		$limite_total = $usuario['limite_externas'] ? $usuario['limite_externas'] : $limite_config;

		if ($limite_total) {
			$monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_externas']);
		}
		$limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);
		if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
			$monto_limite = $limite_total = '';
		}
		$this->set(compact('monto_limite', 'limite_total'));
		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

		if (empty($this->data['Pagostdc'])) {
			if ($this->Session->check('pagos_tdc.terceros_otros')) {
				$this->data = $this->Session->read('pagos_tdc.terceros_otros');
			} else {
				$this->redirect('terceros_otros');
			}
		} else {

			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
			$this->data['Pagostdc']['monto_limite'] = $monto_limite;

			if (empty($usuario['rif'])) {
				$this->data['Pagostdc']['limite_total'] = $limite_total;
			} else {
				$this->data['Pagostdc']['monto_maximo'] = $limite_total;
			}

			if (isset($estado['Pagostdc']['monto_limite_usuario'])) {
				$this->data['Pagostdc']['monto_limite_usuario'] = $estado['Pagostdc']['monto_limite_usuario'];
			}
			/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

			$this->Pagostdc->set($this->data);

			if ($this->Pagostdc->validates(
				array(
					'fieldList' => array(
						'numeroTDC',
						'nombreBeneficiario',
						'tipoCedulaRif',
						'nroCedulaRif',
						'emailBeneficiario',
						'monto',
						'descripcion',
						'alias',
						'afiliar',
						'monto_maximo',
					)
				)
			)) {
				$tdc = str_pad($this->data['Pagostdc']['numeroTDC'], 20, '0', STR_PAD_RIGHT);
				$datosProducto = array('TNROTARJ' => $tdc);

				if ($this->data['Pagostdc']['afiliar'] == 's' && $this->Afiliacion->checkAfiliacion($usuario['id'], $this->data['Pagostdc']['numeroTDC'])) {
					$this->Pagostdc->validationErrors['numeroTDC'] = 'producto_nodisponible';
				} else if (isset($this->data['Pagostdc']['alias']) && $this->data['Pagostdc']['alias'] != "" && $this->Afiliacion->checkAlias($usuario['id'], $this->data['Pagostdc']['alias'])) {
					$this->Pagostdc->validationErrors['alias'] = 'alias_nodisponible';
				} else if (!$this->Activo->validarRIF($this->data['Pagostdc']['tipoCedulaRif'], $this->data['Pagostdc']['nroCedulaRif'], 'p')) {
					$this->Pagostdc->validationErrors['nroCedulaRif'] = _m('valor.invalido', 'el Número de identificaci?n');
				} else {
					$datos = $this->Activo->validarBIN($datosProducto);
					$error = intval($datos['NIB090501Return']['error']['ERNU01']);

					if ($error == 0) {
						$this->data['Pagostdc']['banco'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
						$this->data['Pagostdc']['numeroBanco'] = trim(substr($datos['NIB090501Return']['NIB090501']['TBANCO'], 3));
						if (isset($estado['Pagostdc']['aprobaciones_requeridas'])) $this->data['Pagostdc']['aprobaciones_requeridas'] = $estado['Pagostdc']['aprobaciones_requeridas'];

						$validacion_otp = $this->Session->read('seguridad.validar_otp');
						if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
							$this->data['pasos'] = array(
								'Solicitud de Informaci&oacute;n',
								'Confirmaci&oacute;n',
								'C&oacute;igo de Autorizaci&oacute;',
								'Resultado de Operaci&oacute;n'
							);
							$this->data['paso_final'] = 3;
						} else {
							$this->data['pasos'] = array(
								'Solicitud de Informaci&oacute;n',
								'Confirmaci&oacute;n',
								'Resultado de Operaci&oacute;n'
							);
							$this->data['paso_final'] = 2;
						}
						$this->data['Pagostdc']['fecha_acumulado'] = $fecha_acumulado;
						$this->Session->write('pagos_tdc.terceros_otros', $this->data);

						$this->Session->delete('transaccion.advertencia');

						// En caso de ser un usuario natural o master unico
						if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
							if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Pagostdc'], 100028)) {

								$this->data['titulo'] = 'Pago Tarjeta Crédito Tercero en Otro Banco';
								$this->data['mensaje_alerta'] = 'pagotdc.alerta';
								$this->data['url_volver'] = '/pagos_tdc/terceros_otros_noafiliada';
								$this->data['url_continuar'] = '/pagos_tdc/terceros_otros_confirmacion';
								$this->data['seccion_actual'] = 'pagos.tdc.terceros.otros';

								$this->Session->write('transaccion.advertencia', $this->data);
								$this->redirect('transaccion_advertencia');
							}
						}

						$this->redirect('terceros_otros_confirmacion');
					} else if ($error == 9986) {
						$this->Pagostdc->validationErrors['numeroTDC'] = _m('numero.bin');
					}
				}
			}
		}

		$this->set('seccion_actual', 'pagos.tdc.terceros.otros');
	}

	/**
	 * Acción de confirmaci?n para el pago de tarjetas de Créditos terceros otros bancos
	 */
	function terceros_otros_confirmacion()
	{
		if (!$this->Session->check('pagos_tdc.terceros_otros')) $this->redirect('terceros_otros');

		$estado = $this->Session->read('pagos_tdc.terceros_otros');

		$this->layout = 'interna';

		$this->set('seccion_actual', 'pagos.tdc.terceros.otros');

		$this->data = $estado;

		$usuario = $this->Session->read('usuarios.datos');

		$this->set('juridico', $usuario);

		// Si viene de afiliados realiza la busqueda de los datos...
		if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {

			$afiliado = $this->Afiliacion->obtenerDataAfiliado($usuario['id'], $this->data['Pagostdc']['numeroTDC']);

			if (!$afiliado) {
				$this->redirect('terceros_otros');
			}

			$this->data['Pagostdc']['nombreBeneficiario'] = desencriptar($afiliado['nombre_beneficiario']);
			$this->data['Pagostdc']['tipoCedulaRif'] = $afiliado['tipo_cedula'];
			$this->data['Pagostdc']['nroCedulaRif'] = desencriptar($afiliado['cedula']);
			$this->data['Pagostdc']['emailBeneficiario'] = desencriptar($afiliado['email']);
			$this->data['Pagostdc']['id'] = $afiliado['id'];

			// Para buscar el banco
			$tdc = str_pad($this->data['Pagostdc']['numeroTDC'], 20, '0', STR_PAD_RIGHT);
			$valorTarjeta = array('TNROTARJ' => $tdc);
			$datos = $this->Activo->validarBIN($valorTarjeta);
			$this->data['Pagostdc']['banco'] = '';
			if (!isset($datos['NIB090501Return']['error'])) {
				$this->data['Pagostdc']['numeroBanco'] = trim(substr($datos['NIB090501Return']['NIB090501']['TBANCO'], 3));
				$this->data['Pagostdc']['banco'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
			}
			// Para buscar el banco

			$this->Session->write('pagos_tdc.terceros_otros', $this->data);
		} else {
			$this->data['Pagostdc']['id'] = null;
			$this->Session->write('pagos_tdc.terceros_otros', $this->data);
		}
	}


	/**
	 * Acción para el resultado del pago de tarjetas de Crédito terceros otros bancos, ejecuta el pago.
	 */
	function terceros_otros_resultado()
	{

		if (!$this->Session->check('pagos_tdc.terceros_otros')) $this->redirect('terceros_otros');

		$estado = $this->Session->read('pagos_tdc.terceros_otros');

		$usuario = $this->Session->read('usuarios.datos');

		$this->data = $estado;

		// Validaci?n anti fraude
		$cuentas = $this->Activo->buscarCuentas();

		if (
			!in_array($this->data['Pagostdc']['cuentaDebitar'], array_keys($cuentas))
		) {
			$this->redirect('/consultas/integral');
		}
		//  Fin validaci?n anti fraude							

		if ($this->Session->check('pagos_tdc.terceros_otros')) {

			$this->layout = 'interna';

			$this->set('seccion_actual', 'pagos.tdc.terceros.otros');
			$this->data = $this->Session->read('pagos_tdc.terceros_otros');

			$usuario = $this->Session->read('usuarios.datos');
			$y = substr($this->data['Pagostdc']['numeroBanco'], 3);
			$banco = str_pad($y, 9, '0', STR_PAD_LEFT);

			$this->set('juridico', $usuario['tipo_rif']);

			$cedula = $this->data['Pagostdc']['nroCedulaRif'];
			if (strlen($cedula) < 9) {
				$cedula = str_pad($cedula, 9, '0', STR_PAD_LEFT);
			}

			/** Para identificar si es un usuario juridico **/
			if ($usuario['tipo_rif']) {
				$identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
			} else {
				$identificacion = desencriptar($usuario['cedula']);
			}
			/** Para identificar si es un usuario juridico **/

			if (
				empty($usuario['tipo_rif']) ||
				$usuario['rol_juridico_id'] == 2 ||// Master ?nico
				$usuario['rol_juridico_id'] == 6
			) {

                if (empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2) {

                    if (_p('sms.otp.activo') == '0' && $this->data['Pagostdc']['tipoDestinatario'] == 'a') {
                        $validacion_otp['validado'] = true;
                    } else {
                        $validacion_otp = $this->Session->read('seguridad.validar_otp');
                    }

                    if (
                            (
                            $validacion_otp == null ||
                            !isset($validacion_otp['validado']) ||
                            !$validacion_otp['validado']
                            )
                    ) {
                        if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {
                            $url_volver = '/pagos_tdc/terceros_otros_afiliada';
                        } else {
                            $url_volver = '/pagos_tdc/terceros_otros_noafiliada';
                        }

                        $this->Session->write(
                                'seguridad.validar_otp', array(
                                    'url_aceptar' => '/pagos_tdc/terceros_otros_resultado',
                                    'url_volver' => $url_volver,
                                    'layout' => 'interna',
                                    'titulo' => 'Pago Tarjeta Crédito Tercero en Otro Banco',
                                    'pasos' => $estado['pasos'],
                                    'paso_actual' => 2,
                                    'seccion_actual' => 'pagos.tdc.terceros.otros'
                                )
                        );

                        $this->redirect('/seguridad/genera_otp');
                    }
                }else{

                    $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

                    if(
                        (
                            $validacion_tdc == null ||
                            !isset($validacion_tdc['validado']) ||
                            !$validacion_tdc['validado']
                        )
                    ){
                        if ($this->data['Pagostdc']['tipoDestinatario'] == 'a') {
                            $url_volver = '/pagos_tdc/terceros_otros_afiliada';
                        } else {
                            $url_volver = '/pagos_tdc/terceros_otros_noafiliada';
                        }


                        $this->Session->write(
                                'seguridad.validar_tdc',
                                array(
                                    'url_aceptar' => '/pagos_tdc/terceros_otros_resultado',
                                    'url_volver' => $url_volver,
                                    'layout' => 'interna',
                                    'titulo' => 'Pago Tarjeta Crédito Tercero en Otro Banco',
                                    'pasos' => $estado['pasos'],
                                    'paso_actual' => 2,
                                    'seccion_actual' => 'pagos.tdc.terceros.otros'
                                )
                        );

                                $this->redirect('/seguridad/validar_tdc');
                    }
                }

                if(strpos($ip, ',') != false){
	                $arrayIP = explode(",", $ip);
	                $ip = $arrayIP[0];
                }
				$ip = substr($ip,0,16);


				$res = $this->Activo->invoca(
					'INEXTDSJV',
					array(
						'req' => array(

							'INEFRMACC' => trim(substr($this->data['Pagostdc']['cuentaDebitar'], 10)),
							'INEBNFACC' => trim($this->data['Pagostdc']['numeroTDC']),
							'INEAMT' => formatea_monto_toWs($this->data['Pagostdc']['monto']),
							'INEBNFAD1' => substr(trim($this->data['Pagostdc']['nombreBeneficiario']), 0, 30),
							'INEBNFAD2' => $this->data['Pagostdc']['tipoCedulaRif'] . $cedula,
							'INEBNKAD3' => _p('moneda'),
							'INECCY' => _p('moneda'),
							'INEDTP1' => '0225-TARJETA DE CREDITO OTRO BANCO',
							'INEDTP2' => trim($this->data['Pagostdc']['descripcion']),
							'INEDTP3' => trim($this->data['Pagostdc']['descripcion']),
							'INEBNKSN' => substr($this->data['Pagostdc']['banco'], 0, 20),
							'INEINTAD4' => _p('moneda'),
							'INEVDT1' => date('d'),
							'INEVDT2' => date('m'),
							'INEVDT3' => date('y'),
							'INEXPMTV' => _p('via_pago_internet'),
							'INEBNKID' => $banco,
							'INEBNKAD1' => substr($this->data['Pagostdc']['banco'], 0, 20),
							/*'INEBNKAD2'=>'Caracas', //trim($dataBanco['BNKCTY']),
							'INEBNKAD3'=>'Caracas DC',  //trim($dataBanco['BNKCTY']).' '.trim($dataBanco['BNKSTA']),*/
							'INEBNKAD4' => _p('moneda'),
							'INECKN' => '0',
							'INESFMT' => '0',
							'INEDIP' => $ip,
							'operacion' => 't',
							'canal' => Configure::read('canal.ib')
						)
					)
				);

                if(!$res){
                	$this->redirect('/consultas/integral');
                }

				$error = -1;
				$descripcion = "Error inesperado, transAcción no disponible.";
				$error_afiliacion = false;
				$referencia = $res['INEXTDSJVReturn']['error']['ERDF01'];

				if (isset($res['INEXTDSJVReturn'])) {
					$error = intval($res['INEXTDSJVReturn']['error']['ERRNUM']);
					$error = ($error == 0) ? $error : intval($res['INEXTDSJVReturn']['error']['ERNU01']);
					$descripcion = $res['INEXTDSJVReturn']['error']['ERNU01'] . ' - ' . $res['INEXTDSJVReturn']['error']['ERDS01'];
				}

				if ($error == 0) {

					/** ACTUALIZA EL ACUMULADO DEL DIA SEGUN LA FRANJA DE COMPENSACION**/
					$this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'e', $this->data['Pagostdc']['monto'], $this->data['Pagostdc']['fecha_acumulado']);
					/** ACTUALIZA EL ACUMULADO DEL DIA SEGUN LA FRANJA DE COMPENSACION**/

					$this->Bitacora->registraBitacora(
						$usuario['id'],
						100028,
						$this->data['Pagostdc']['cuentaDebitar'],
						$this->data['Pagostdc']['numeroTDC'],
						$this->data['Pagostdc']['monto'],
						$referencia,
						$this->data['Pagostdc']['descripcion'],
						0,
						NULL,
						$this->data['Pagostdc']['tipoCedulaRif'],
						$this->data['Pagostdc']['nroCedulaRif'],
						$this->data['Pagostdc']['nombreBeneficiario']

					);

					$this->data['Pagostdc']['REFERENCIA'] = $referencia;
					$this->data['Pagostdc']['CONCEPTO'] = 'PAGO TDC ' . enmascaraTDC($this->data['Pagostdc']['numeroTDC']);
					$this->data['Pagostdc']['TIPO'] = 'tdc';
					$this->data['Pagostdc']['NRO_BENEFICIARIO'] = $this->data['Pagostdc']['numeroTDC'];
					$this->data['Pagostdc']['ESTATUS'] = 'EN PROCESO';

					// Genera la data para la descarda de pdf
					$this->Session->write('usuarios.descarga.pdf', $this->data['Pagostdc']);


					// Email Orden
					// Email Options
					$this->Email->smtpOptions = Configure::read('email.smtp');
					$this->Email->delivery	= 'smtp';
					$this->Email->from = Configure::read('email.from');
					$this->Email->to = desencriptar($usuario['email_principal']);
					$this->Email->subject = 'Notificacion Pago Tarjeta de Credito';
					$this->Email->sendAs = 'html';

					// Informacion del correo
					$this->Email->template	= 'pagotdc_ordenante';
					$tipo_accion = 'Pago Tarjeta de Credito';
					$tipo_afiliacion = 'Tercero Otros Bancos';
					$nombreBeneficiario = $this->data['Pagostdc']['nombreBeneficiario'];
					$this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

					// Envio del correo
					$this->Email->send();


					// Email Beneficiario
					// Email Options
					$this->Email->reset();
					$this->Email->smtpOptions = Configure::read('email.smtp');
					$this->Email->delivery	= 'smtp';
					$this->Email->from = Configure::read('email.from');
					$this->Email->to = $this->data['Pagostdc']['emailBeneficiario'];
					$this->Email->subject = 'Notificacion Pago';
					$this->Email->sendAs = 'html';

					// Informacion del correo
					$this->Email->template	= 'pagotdc_beneficiario';
					$this->set(compact('usuario'));

					// Envio del correo
					$this->Email->send();
				} else {
					$this->log('Pago TDC Terceros Otros Bancos- ' . $descripcion, LOG_ERR);
					$this->Bitacora->registraBitacora(
						$usuario['id'],
						100028,
						$this->data['Pagostdc']['cuentaDebitar'],
						$this->data['Pagostdc']['numeroTDC'],
						$this->data['Pagostdc']['monto'],
						$referencia,
						$descripcion,
						1
					);
				}

				if ($error != 182 && $error != 2149 && $error != 1913 && $error != 862 && $error != 9972 && $error != 1912 && $error != -1 && $this->data['Pagostdc']['afiliar'] == 's') {

					$error_afiliacion = !$this->Afiliacion->afiliar(
						$usuario['id'],
						'p',
						'tob',
						$this->data['Pagostdc']['alias'],
						$this->data['Pagostdc']['numeroTDC'],
						$this->data['Pagostdc']['nombreBeneficiario'],
						$this->data['Pagostdc']['tipoCedulaRif'],
						$this->data['Pagostdc']['nroCedulaRif'],
						$this->data['Pagostdc']['emailBeneficiario'],
						$this->data['Pagostdc']['monto_maximo'],
						$usuario['tipo_rif'],
						$usuario['rif'],
						$this->data['Pagostdc']['id']
					);

					if (!$error_afiliacion && $this->data['Pagostdc']['tipoDestinatario'] == 'n') {

						// Registra en bitacora
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo']
						);

						// Email Options
						$this->Email->reset();
						$this->Email->smtpOptions = Configure::read('email.smtp');
						$this->Email->delivery	= 'smtp';
						$this->Email->from = Configure::read('email.from');
						$this->Email->to = desencriptar($usuario['email_principal']);
						$this->Email->subject = 'Notificacion Afiliacion';
						$this->Email->sendAs = 'html';

						// Informacion del correo
						$this->Email->template	= 'afiliacion_producto';
						$tipo_accion = 'Pago Tarjeta de Credito';
						$tipo_afiliacion = 'Tercero Otros Bancos';
						$nombreBeneficiario = $this->data['Pagostdc']['nombreBeneficiario'];
						$this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

						// Envio del correo
						$this->Email->send();
					} else {
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo'],
							NULL,
							$descripcion,
							1
						);
					}
				}

				$this->set('pendiente', false);
			} else if (
				$usuario['rol_juridico_id'] == 3  || // Operador
				$usuario['rol_juridico_id'] == 5     // Operador/Aprobador
			) {
				// En caso de jur?dico, s?lo los operadores y operadores/aprobadores pueden
				// generar una pago. En cuyo caso, se deja pendiente por aprobar

				$error = 0;

				//Solicitar TC si esta afiliando la cuenta
				if ($this->data['Pagostdc']['afiliar'] == 's' && $this->data['Pagostdc']['tipoDestinatario'] == 'n') {

					$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

					if (
						(
							$validacion_tdc == null ||
							!isset($validacion_tdc['validado']) ||
							!$validacion_tdc['validado']
						)
					) {
						$this->Session->write(
							'seguridad.validar_tdc',
							array(
								'url_aceptar' => '/pagos_tdc/terceros_otros_resultado',
								'layout' => 'interna',
								'titulo' => 'Pago Tarjeta Crédito Tercero en Otro Banco',
								'pasos' => array(),
								'paso_actual' => 2,
								'seccion_actual' => 'pagos.tdc.terceros.otros'
							)
						);

						$this->redirect('/seguridad/validar_tdc');
					}
				}

				$res = $this->TransaccionPendiente->save(
					array(
						'TransaccionPendiente' => array(
							'usuario_id' => $usuario['id'],
							'tipo' => 'pto',
							'estatus' => 'pa',
							'fecha_hora_registro' => date('Y-m-d H:i:s'),
							'monto' => formatea_monto_toWs($this->data['Pagostdc']['monto']),
							'aprobaciones_requeridas' => $this->data['Pagostdc']['aprobaciones_requeridas'],
							'tipo_rif' => $usuario['tipo_rif'],
							'rif' => $usuario['rif'],
							'cuenta_origen' => encriptar($this->data['Pagostdc']['cuentaDebitar']),
							'cuenta_destino' => encriptar($this->data['Pagostdc']['numeroTDC']),
							'descripcion' => encriptar($this->data['Pagostdc']['descripcion']),
							'nombre_beneficiario' => encriptar($this->data['Pagostdc']['nombreBeneficiario']),
							'tipo_id_beneficiario' => $this->data['Pagostdc']['tipoCedulaRif'],
							'id_beneficiario' => encriptar($this->data['Pagostdc']['nroCedulaRif']),
							'email_beneficiario' => encriptar($this->data['Pagostdc']['emailBeneficiario']),
							'alias' => isset($this->data['Pagostdc']['alias']) ? encriptar($this->data['Pagostdc']['alias']) : null
						)
					),
					false
				);

				if ($res) {
					$error = 0;
					$referencia = $this->TransaccionPendiente->id;
				} else {
					$error = -1;
					$referencia = '';
				}

				$this->set('pendiente', true);

				if ($this->data['Pagostdc']['afiliar'] == 's') {
					$error_afiliacion = !$this->Afiliacion->afiliar(
						$usuario['id'],
						'p',
						'tob',
						$this->data['Pagostdc']['alias'],
						$this->data['Pagostdc']['numeroTDC'],
						$this->data['Pagostdc']['nombreBeneficiario'],
						$this->data['Pagostdc']['tipoCedulaRif'],
						$this->data['Pagostdc']['nroCedulaRif'],
						$this->data['Pagostdc']['emailBeneficiario'],
						$this->data['Pagostdc']['monto_maximo'],
						$usuario['tipo_rif'],
						$usuario['rif'],
						$this->data['Pagostdc']['id']
					);

					if (!$error_afiliacion && $this->data['Pagostdc']['tipoDestinatario'] == 'n') {

						// Registra en bitacora
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo']
						);

						// Email Options
						$this->Email->reset();
						$this->Email->smtpOptions = Configure::read('email.smtp');
						$this->Email->delivery	= 'smtp';
						$this->Email->from		= Configure::read('email.from');
						$this->Email->to		= desencriptar($usuario['email_principal']);
						$this->Email->subject	= 'Notificacion Afiliacion';
						$this->Email->sendAs	= 'html';

						// Informacion del correo
						$this->Email->template	= 'afiliacion_producto';
						$tipo_accion = 'Pago Tarjeta de Credito';
						$tipo_afiliacion = 'Tercero Otros Bancos';
						$nombreBeneficiario = $this->data['Pagostdc']['nombreBeneficiario'];
						$this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

						// Envio del correo
						$this->Email->send();
					} else {
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							14,
							NULL,
							$this->data['Pagostdc']['numeroTDC'],
							$this->data['Pagostdc']['monto_maximo'],
							null,
							$descripcion,
							1
						);
					}
				} else {
					$error_afiliacion = false;
				}
			}

			$this->set(compact('error', 'error_afiliacion', 'referencia'));
			$this->Session->delete('pagos_tdc.terceros_otros');
		} else {
			$this->redirect('terceros_otros');
		}
	}
	/*** Fin TDC Otros Bancos terceros ***/



	/**
	 * Acción para mostrar advertencia de operaci?n realizada
	 * 
	 */
	function transaccion_advertencia()
	{

		if (!$this->Session->check('transaccion.advertencia')) $this->redirect('/consultas/integral');

		$this->data = $this->Session->read('transaccion.advertencia');

		$this->layout = 'interna';

		$this->set('seccion_actual', $this->data['seccion_actual']);

		$this->render('/elements/transaccion_advertencia');
	}

	function log_tdc($cadena){

    	$mes = date("m-Y");

    	//Para windows
        //$rutaArchivoTXT = TMP . 'logs\logs_tdc' . DS . 'tdc_' . $mes . '.txt';

    	//Para Linux
        $rutaArchivoTXT = TMP . 'logs/logs_tdc' . DS . 'tdc_' . $mes . '.txt';


        $linea = "\n---------------------------------------------------------------------------";
        file_put_contents($rutaArchivoTXT, $linea, FILE_APPEND | LOCK_EX);
        file_put_contents($rutaArchivoTXT, $cadena, FILE_APPEND | LOCK_EX);
        file_put_contents($rutaArchivoTXT, $linea, FILE_APPEND | LOCK_EX);
    }



}
