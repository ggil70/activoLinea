<?php
/**
 * Clase base de los controladores de la aplicaci�n
 */
 
/**
 * Clase base de los controladores de la aplicaci�n
 */
class AppController extends Controller {
	
	/**
	 * Componentes utilizados.
	 * 
	 * @var array 
	 */
	//var $components = array('Activo','Session', 'RequestHandler','DebugKit.Toolbar');
	var $components = array('Activo','Session', 'RequestHandler','Ipinfodb');
	
	
	/**
	 * Helpers utilizados.
	 * 
	 * @var array
	 */
	var $helpers = array('Session', 'Html', 'Form','Activo');
	
        /**
         * Controlador
         *
         * @var string
         */
        var $controller;
	
        /**
         * M�todo invocado inicialmente por el CakePHP
         *
         * @param string $controller Controlador que incluye al componente
         * @param array $settings Configuraci�n b�sica del componente
         */
        function initialize(&$controller, $settings = array())
        {
            $this->controller = $controller;
        }
    
	/**
	 * M�todo que se ejecuta antes de la acci�n invocada por el usuario
	 * 
	 */

	 
	function bancoActivoPagoEnLineaTdcMensajes($res)
	{

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

	 function ConsultaDeSaldoTdcSoap($cod_cedula = null, $cedula = null)
	 {
	 
		 // cedula
		 $usuario = $this->Session->read('usuarios.datos');
		// $num_cedula = '00' . desencriptar($usuario['cedula']);
		 $num_cedula = str_pad(desencriptar($usuario['cedula']), 12, '0', STR_PAD_LEFT);
		//  $num_cedula = '00' . desencriptar($usuario['cedula']);
		// $num_cedula = desencriptar($usuario['cedula']);


		// $longitud = strlen($num_cedula);

		// if ($longitud === 7) {
		// 	$num_cedula = '000' . desencriptar($usuario['cedula']);
		// } else if ($longitud === 8) {
		// 	$num_cedula = '00' . desencriptar($usuario['cedula']);
		// }
		// //  $num_cedula = '0016528522';
		 
		 $cod_cedula = $usuario['tipo_cedula'];
	 
		 // 0016528522 varias TDC ARRAY
		 // 0017272272 una sola TDC OBJECT
	 
		 $soapData = $this->bancoActivoPagoEnLineaTdc();
	 
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
	 
			 $this->log('Error t�cnico:', LOG_ERR);
			 $this->log($res, LOG_ERR);
	 
			 if (isset($this->controller)) {
				 $this->controller->set('error_fatal', _m('error.fatal'));
			 }
			 return 1;
		 } else {
	 
			 $err = $this->client->getError();
			 if ($err) {
	 
				 // $this->log('Error t�cnico:',LOG_ERR);
				 // $this->log($err,LOG_ERR);
				 // if(isset($this->controller)){
				 // 	$this->controller->set('error_fatal',_m('error.fatal'));					
				 return 1;
			 }
		 }
	 
		 // $soapResponse = $this->bancoActivoPagoEnLineaTdcMensajes($res);
	 
		 foreach ($res as $r) {
	 
			 $this->Session->write('consulta_TDC.soapResponse', $r);
	 
			 return $r;
		 };
	 }

	function beforeFilter(){
            
		if(!$this->RequestHandler->isSSL()) $this->disableCache();
                
                /** PARA VERIFICAR SI LOS SERVICIOS ESTAN ARRIBA **/
               /* if(!$this->Session->check('verifica.servicios')){
                        $datos_basicos = $this->Activo->buscarBancos(true);
                       if($datos_basicos != NULL){
                           $this->Session->write('verifica.servicios', true);
                       }else{
                           if(!$this->Session->check('verificar.mantenimiento')){
                               $this->Session->write('verificar.mantenimiento', true);
                               $this->redirect('mantenimiento');
                           }    
                       }
                   
                }*/                                                                          
                
                $usuario = $this->Session->read('usuarios.datos');
		                                           
                
				$this->Session->write('countryAcronym','VE');
                $this->Session->write('countryCode',$paises[$countryCode]);
				
                // PARA VERIFICAR SI REPORTO EL VIAJE EN CASO DE ESTAR FUERA DEL PAIS
                if($usuario){
                    
                    $this->loadModel('NotificacionAusencia');
                    $this->loadModel('Pais');
                    
                    $paises = $this->Pais->listar_paises_siglas();
                    $notificacionesList = $this->NotificacionAusencia->buscarNotificacionesViajeByUsuarioId($usuario['id']);                    
                    $countryCode = $this->Session->read('countryAcronym');
                    $reporta = false;

                    if(count($notificacionesList)){                                
                        foreach($notificacionesList as $i){                                                               
                            if($paises[$i['NotificacionAusencia']['codigo_pais']]==$countryCode && $this->Activo->check_in_range($i['NotificacionAusencia']['fecha_inicio'],$i['NotificacionAusencia']['fecha_fin'],'today')){
                               $reporta = true;
                               break;
                           }                                    
                        }
                    }
                    
                    $this->Session->write('reportaViaje',$reporta);
                }                
        
/*Fue comentada la condicion que valida si ingresa desde un movil y redirige a movilactivo 04/11/2022*/        
		/*if(esMovil()){
			// Decidir si se muestra la versi�n m�vil del portal
			// ya que no todas las pantallas est�n disponibles
			// en dicho formato
			
			//header ("Location: https://movilactivo.bancoactivo.com/");
						
			// OJO: Cada vez que se cree la versi�n m�vil de una p�gina
			// se debe agregar aqu�. De lo contrario se mostrar� la versi�n desktop
			switch($this->name){
				case 'Consultas':
					$vistas = array('integral');
					break;
				case 'Creditos':
					$vistas = array('index','movimientos');
					break;
				case 'Cuentas':
					$vistas = array('detalle','index','movimiento');
					break;
				case 'Inversiones':
					$vistas = array('detalle','index');
					break;
				case 'Tarjetas':
					$vistas = array('detalle','index');
					break;
				case 'Usuarios':
					$vistas = array(
						'contrasena',
						'login',
						'fin_sesion',
						'session_activa',
						'session_bloqueada',
						'doble_session'
					);
					break;
				case 'MenuMovil':
					$vistas=array('index');
					break;
				/*
				case 'Transferencias':
					$vistas=array(
						'otros',
						'otros_afiliada',
						'otros_confirmacion',
						'otros_resultado',
						'propias',
						'propias_confirmacion',
						'propias_resultado',
						'terceros',
						'terceros_afiliados',
						'terceros_confirmacion',
						'terceros_otros',
						'terceros_otros_afiliada',
						'terceros_otros_confirmacion',
						'terceros_otros_resultado',
						'terceros_resultado',
						'transaccion_advertencia'
					);
					break;
				
				case 'PagosTdc':
					$vistas=array(
						'propias',
						'propias_confirmacion',
						'propias_resultado',
						'transaccion_advertencia',
						'terceros',
						'terceros_afiliados',
						'terceros_confirmacion',
						'terceros_resultado',
						'terceros_otros',
						'terceros_otros_afiliada',
						'terceros_otros_confirmacion',
						'terceros_otros_resultado',
						'otros',
						'otros_afiliada',
						'otros_confirmacion',
						'otros_resultado',
					);
					break;
				*/
			/*	case 'Pages':
					$vistas = array('display');
					break;
				
				default:
					$vistas = array('x');
			}
			
			if(in_array($this->action, $vistas)){
				// En caso positivo, cambiar el tema a m�vil
				
				$this->view = "Theme";
				$this->theme = "movil";
			}
		}	*/			
		
		if(empty($usuario)){
			// Redirige si es un modulo no permitido
			$redirige = false;
			
			if(!($this instanceof UsuariosController || $this instanceof SeguridadController || $this instanceof SolicitudesController || $this instanceof PagesController || $this instanceof ValidaDocumentosController)){
				$redirige = true;
			} else if($this instanceof SeguridadController){
				$array = array('cambiar_clave', 'cambiar_datos', 'cambiar_preguntas', 'cambiar_imagen', 'modificacion_limites');
				if(in_array($this->action, $array)){
					$redirige = true;
				}
			} else if($this instanceof SolicitudesController){
				$array = array('solicitar_tarjeta_coordenada', 'solicitar_tc_resultado', 'descargar_tdc');
				
				if(!in_array($this->action, $array)){
					$redirige = true;
				}
			}
			
			if($redirige){
				$this->redirect('/');
			}
		}
		
		if(!empty($usuario)){
			$this->loadModel('Usuario');
			$this->loadModel('UsuarioCanal');
			
			if(Configure::read('desarrollo')){
				$minutos = 6000;
			} else {
				$minutos = _p('tiempo_session');
			}
			
			$estatusAcceso = $this->UsuarioCanal->find(
				'first',
				array(
					'fields'=>array('estatus_acceso'),
					'conditions'=>array(
						'usuario_id'=>$usuario['id'],
						'canal_id'=> Configure::read('canal.ib')
					)
				),false
			);
			
			// Verifica el estatus registro del usuario
			if($estatusAcceso && $estatusAcceso['UsuarioCanal']['estatus_acceso'] != 'A'){
				if(!$this->Session->check('verifica.estatus')){
					$this->Session->write('verifica.estatus', true);
					$this->redirect('/usuarios/doble_session');
				}
			}
			else{
				
				// chequea actualiza el tiempo de interaccion del usuario
				if( $this->Session->check('usuarios.loggedin') ){
					if(!$this->UsuarioCanal->sessTime($usuario['id'], $minutos)){
						$this->Session->delete('usuarios');
						$this->redirect('/');
					}
				}
		
				// chequea actualiza el tiempo de interaccion del usuario
				
				// Tiempo para el mensaje de sesion del usuario
				$tiempo_session = ($minutos - 1) * 60000;
				$this->Session->write('usuarios.timeadvsess', $tiempo_session);
				$this->Session->write('usuarios.timefinsess', $minutos * 60000);
				// Tiempo para el mensaje de sesion del usuario
			
			
				if(Configure::read('desarrollo')){
					$res = $this->Session->read('usuarios.cuentas');
				}

				// Cargar productos para deshabilitar elementos en el menu principal
				if(empty($res)){

					// Para identificar si es un usuario juridico
					if($usuario['tipo_rif']){
						$tipo_documento = $usuario['tipo_rif'];
						$nro_documento = desencriptar($usuario['rif']);
					}
					else{
						$tipo_documento = $usuario['tipo_cedula'];
						$nro_documento = desencriptar($usuario['cedula']);
					}
					
                                        $verifica_datosBasicos = $this->Session->read('usuarios.nibpf3010');
                                        
                                        if(empty($verifica_datosBasicos)){
                                            
                                            $datos_basicos = $this->Activo->invoca(
						'NIBPF3010',
						array(
							'req'=>array(
								'NACIONALIDAD'=>$tipo_documento,
								'NROIDENTIFICACION'=>str_pad($nro_documento,12,'0',STR_PAD_LEFT),
								'NROPARAM'=>'1'
							)
						)
                                            );

                                            // Sustituir nombre, correo y celular en caso de que el cliente lo haya cambiado en la agencia

                                            if(!isset($usuario['rif']) && isset($datos_basicos['NIBPF3010Return']['NIBPF3010'])){
                                                    $nuevo_nombre = encriptar($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'].' '.$datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE'].' '.$datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'].' '.$datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO']);
                                                    $nuevo_email = encriptar($datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3']);
                                                    $nuevo_celular = encriptar($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR']);

                                                    if(
                                                            $nuevo_nombre != $usuario['nombre'] ||
                                                            $usuario['email_principal'] != $nuevo_email ||
                                                            $usuario['celular'] != $nuevo_celular
                                                    ){
                                                            // Actualizar la sesi�n

                                                            $usuario['nombre'] = $nuevo_nombre;
                                                            $usuario['email_principal'] = $nuevo_email;
                                                            $usuario['celular'] = $nuevo_celular;
                                                            $this->Session->write('usuarios.datos',$usuario);

                                                            // Actualizar la bd

                                                            $this->loadModel('Afiliacion');

                                                            // Actualizar afiliaciones propias otros bancos

                                                            $ds = $this->Afiliacion->getDataSource();

                                                            $this->Afiliacion->updateAll(
                                                                    array(
                                                                            'nombre_beneficiario'=>$ds->value($nuevo_nombre),
                                                                            'email'=>$ds->value($nuevo_email),
                                                                    ),
                                                                    array(
                                                                            'usuario_id'=>$usuario['id'],
                                                                            'tipo_operacion'=>"ob"
                                                                    )
                                                            );

                                                            // Actualizar usuario

                                                            $this->Usuario->id = $usuario['id'];

                                                            $this->Usuario->save(
                                                                    array(
                                                                            'Usuario'=>array(
                                                                                    'nombre'=>$nuevo_nombre,
                                                                                    'email_principal'=>$nuevo_email,
                                                                                    'celular'=>$nuevo_celular
                                                                            )
                                                                    ),
                                                                    false
                                                            );
                                                    }
                                            }elseif(isset($usuario['rif']) && isset($datos_basicos['NIBPF3010Return']['NIBPF3010'])){

                                                    $nuevo_email = encriptar($datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3']);
                                                    $nuevo_celular = encriptar($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR']);

                                                    if(
                                                            $usuario['email_principal'] != $nuevo_email ||
                                                            $usuario['celular'] != $nuevo_celular
                                                    ){
                                                            // Actualizar la sesi�n														
                                                            $usuario['email_principal'] = $nuevo_email;
                                                            $usuario['celular'] = $nuevo_celular;
                                                            $this->Session->write('usuarios.datos',$usuario);

                                                            // Actualizar la bd

                                                            $this->loadModel('Afiliacion');

                                                            // Actualizar afiliaciones propias otros bancos

                                                            $ds = $this->Afiliacion->getDataSource();

                                                            $this->Afiliacion->updateAll(
                                                                    array(									
                                                                            'email'=>$ds->value($nuevo_email),
                                                                    ),
                                                                    array(
                                                                            'rif'=>$usuario['rif'],
                                                                            'tipo_operacion'=>"ob"
                                                                    )
                                                            );

                                                            // Actualizar usuario

                                                            $this->Usuario->id = $usuario['id'];

                                                            $this->Usuario->save(
                                                                    array(
                                                                            'Usuario'=>array(									
                                                                                    'email_principal'=>$nuevo_email,
                                                                                    'celular'=>$nuevo_celular
                                                                            )
                                                                    ),
                                                                    false
                                                            );
                                                    }
                                            }

                                            $identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];
                                            if($identificacion){
                                                    $res = $this->Activo->invoca(
                                                            'SUMDSJV',array( 
                                                                    'req'=>array(
                                                                            'SUMACN'=>$identificacion,
                                                                            'SUMFIR'=>'X'
                                                                    )
                                                            )
                                                    );

                                                    $this->Session->write('usuarios.nibpf3010',$datos_basicos);
                                                    $this->Session->write('usuarios.cuentas',$res);
                                            }
                                        }
					
				}
				
				
				$instrumentos = array();
				$consultaTdc = $this->ConsultaDeSaldoTdcSoap();
				$consultaTdcInstrumento = isset($consultaTdc['ConsultSaldoenLineaTDCResponse']);
				$consultaTdcInstrumentoObj = isset($consultaTdc);
				$res = $this->Session->read('usuarios.cuentas');
				if(isset($res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'])){
					$x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
					normaliza($x);

					if(isset($x) && count($x)){
						foreach($x as $i){
							if(isset($instrumentos[$i['SUMTYP']])){
								$instrumentos[$i['SUMTYP']]++;
							} else {
								$instrumentos[$i['SUMTYP']]=1;
							}
						}
					}
				}

				$this->set(compact('instrumentos', 'consultaTdcInstrumento', 'consultaTdcInstrumentoObj'));
				
				// Verificar si el usuario tiene clave vencida
				if(
					$this->Session->check('usuarios.clave.vencida') &&
					(
						(
							!($this instanceof SeguridadController) &&
							!($this instanceof UsuariosController)
						) ||
						!in_array($this->action, array('cambiar_clave','cambiar_clave_resultado','logout'))
					)
				){
					$this->Session->write('seguridad.validar_tdc',array('validado'=>true));
					$this->redirect('/seguridad/cambiar_clave');				
				}// Verificar si el usuario tiene el sello personalizado vencido
                                else if(
					!$this->Session->check('usuarios.clave.vencida') && $this->Session->check('usuarios.sello.vencido') &&
					(
						(
							!($this instanceof SeguridadController) &&
							!($this instanceof UsuariosController)
						) ||
						!in_array($this->action, array('cambiar_imagen','config_sello','config_confirm','sello_creado','seleccion_sello','seleccion_confirmacion','logout'))
					)
				){
					$this->Session->write('seguridad.validar_tdc',array('validado'=>true));
					$this->redirect('/seguridad/cambiar_imagen');
				}

				
				/** PARA LA BUSQUEDA DE LOS BANNERS **/
				$this->loadModel('Banners');
				$banners = $this->Banners->porSeccion(1);
				$this->set(compact('banners'));
				/** PARA LA BUSQUEDA DE LOS BANNERS **/


				/** PARA LA CANTIDAD DE NOTIFICACIONES **/
				$this->loadModel('Mensajes');
				$cantidad_mensajes = $this->Mensajes->buscaCantidad($usuario['id']);
				$this->set(compact('cantidad_mensajes'));
				/** PARA LA CANTIDAD DE NOTIFICACIONES **/
				
			}
		}
	}
        
        /**
	 * M�todo que se invoca despu�s de la l�gica de acci�n del controlador, pero antes de que se visualice la vista
	 * 
	 */
        function beforeRender() {
            
            $usuario = $this->Session->read('usuarios.datos');             
            
            /** PARA VERIFICAR LA GEOLOCALIZACION Y SI REPORTO EL VIAJE **/
            $idTransaccion = $this->Session->read('idTransaccion');
            
            if($idTransaccion!=null){                

                if($this->Activo->consultaTransaccionesIP($idTransaccion)){                    
                    
                    $this->loadModel('Pais');
                    $this->loadModel('NotificacionAusencia');                                        
                    
                    $countryCode = $this->Session->read('countryAcronym');
                    
                    if($countryCode!='VE'){

                        $notificacionesList = $this->NotificacionAusencia->buscarNotificacionesViajeByUsuarioId($usuario['id']);
                        $paises = $this->Pais->listar_paises_siglas();
                        $ingreso = false;
                        
                        if(count($notificacionesList)){                                
                            foreach($notificacionesList as $i){
                                if($countryCode=='OP'){                                    
                                    if($this->Activo->check_in_range($i['NotificacionAusencia']['fecha_inicio'],$i['NotificacionAusencia']['fecha_fin'],'today')){
                                       $ingreso = true;
                                       break;
                                    }   
                                }else{
                                    if($paises[$i['NotificacionAusencia']['codigo_pais']]==$countryCode && $this->Activo->check_in_range($i['NotificacionAusencia']['fecha_inicio'],$i['NotificacionAusencia']['fecha_fin'],'today')){
                                       $ingreso = true;
                                       break;
                                    }  
                                }                                                                 
                            }
                        }else{
                            $this->redirect('/seguridad/advertencia_notificacion_viaje');
                        }

                        if(!$ingreso){
                            $this->redirect('/seguridad/advertencia_notificacion_viaje');
                        }
                    }                        
                }                                        

            }       
        }
}
