<?php
/**
 * Controlador que maneja los temas referentes a:
 *  Cambio de clae
 *  Cambio de imagen antiphishing
 *  Cambio de preguntas de autenticación
 *  Cambio de montos límites
 *  Validación de tarjeta de coordenadas
 *  Validación de preguntas de autenticación
 *  Validación de preguntas de desafío
 *  
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Controlador para solicitudes y anulaciones de tarjetas de coordenadas
 * 
 */
class SeguridadController extends AppController{
	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array(
		'Usuario',
		'CambioClave',
		'Olvido',
		'Tdc',
		'Bitacora',
		'UsuarioCanal',
        'Pais',
        'NotificacionAusencia',
        'CambioPin'
	);

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */			
	var $components = array('Idg','Activo', 'Session', 'Email','Sms');

	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */			
	var $helpers = array('Activo','Html','Session','Form','Text');

	/**
	 * Acción que maneja el formulario de cambio de clave a la banca en línea
	**/
	function cambiar_clave(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(15)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		if(
			$validacion_tdc == null ||
			!isset($validacion_tdc['validado']) ||
			!$validacion_tdc['validado']
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/seguridad/cambiar_clave',
					'layout'=>'interna',
					'titulo'=>'Cambiar Clave Acceso Activo en Línea',
					'pasos'=> array(),
					'paso_actual'=>0,
					'seccion_actual'=>'cambio.clave'
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}

		if(isset($this->data['CambioClave'])){
			$this->CambioClave->set($this->data);
			 
			if($this->CambioClave->validates()){			
				$usuario = $this->Session->read('usuarios.datos');

				if(empty($usuario['tipo_rif'])){
					$login_idg = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
				} else {
					$login_idg = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
				}
					
				$this->Idg->preAutenticar(
					$login_idg
				);
				
				if(isset($this->Idg->codigo_error)){
					$usuario = $this->Session->read('usuarios.datos');
					
					switch($this->Idg->codigo_error){
						case 'USER_LOCKED':
						case 'AUTH_FAILED_USER_LOCKED':
							$this->Bitacora->registraBitacora(
								$usuario['id'],
								100001
							);
							
							$this->UsuarioCanal->cambioEstatus(
								'acceso',
								'BC',
								$usuario['id']
							);
							
							$this->Session->destroy();
							
							if( !empty($usuario['tipo_rif']) ){ // PARA JURIDICO
								
								$this->Session->write('login.juridico', 'J');
								
								$estado = array(
									'titulo' => 'Usuario Bloqueado',
									'paso_desafio'=>0,
									'paso_clave'=>1,
									'pasos'=>array(
										'Preguntas de Desafío',
										'Cambio de Clave'
									),
									'tipo'=>'clave',
									'cedula.validada'=>true,
									'datasource'=>$usuario,
									'mensaje'=>'usuario.bloqueo.clave'
								);
							}
							else { // PARA NATURAL
								$estado = array(
									'titulo' => 'Usuario Bloqueado',
									'paso_autenticacion'=>0,
									'paso_desafio'=>1,
									'paso_clave'=>2,
									'pasos'=>array(
										'Preguntas de Autenticación',
										'Preguntas de Desafío',
										'Cambio de Clave'
									),
									'tipo'=>'clave',
									'cedula.validada'=>true,
									'datasource'=>$usuario,
									'mensaje'=>'usuario.bloqueo.clave'
								);
							}
							
							$this->Session->write('seguridad.olvido', $estado);
							$this->redirect('/seguridad/olvido_clave');	
					}
				}
				
				if(Configure::read('desarrollo')){
					$res = true;				
				} else {
					$res = $this->Idg->autenticar(
						$login_idg,
						$this->data['CambioClave']['clave_actual']
					);
				}
				
				if(
					$res ||
					(
						isset($this->Idg->codigo_error) && 
						$this->Idg->codigo_error=='PASSWORD_CHANGE_REQUIRED'
					)
				){
					$res = $this->Idg->cambiarContrasena(
						$login_idg,
						$this->data['CambioClave']['clave_nueva'],
						$this->data['CambioClave']['tiempoCaduca']					
					);
					
					if($res){
						if($this->Session->check('usuarios.clave.vencida')){
							$this->Session->delete('usuarios.clave.vencida');
							$this->Session->delete('seguridad.validar_tdc');
						}
						
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							15
						);					
						
						$this->redirect('cambiar_clave_resultado');	
					} else {
						$this->CambioClave->validationErrors['clave_nueva'] = $this->Idg->mensaje_error;	
					}
				} else {
					switch($this->Idg->codigo_error){
						case 'USER_LOCKED':
						case 'AUTH_FAILED_USER_LOCKED':
							$this->Bitacora->registraBitacora(
								$usuario['id'],
								100001
							);
							
							$this->UsuarioCanal->cambioEstatus(
								'acceso',
								'BC',
								$usuario['id']
							);
									
							$this->Session->destroy();
							
							
							if( !empty($usuario['tipo_rif']) ){ // PARA JURIDICO
								
								$this->Session->write('login.juridico', 'J');
								
								$estado = array(
									'titulo' => 'Usuario Bloqueado',
									'paso_desafio'=>0,
									'paso_clave'=>1,
									'pasos'=>array(
										'Preguntas de Desafío',
										'Cambio de Clave'
									),
									'tipo'=>'clave',
									'cedula.validada'=>true,
									'datasource'=>$usuario,
									'mensaje'=>'usuario.bloqueo.clave'
								);
							}
							else { // PARA NATURAL
								$estado = array(
									'titulo' => 'Usuario Bloqueado',
									'paso_autenticacion'=>0,
									'paso_desafio'=>1,
									'paso_clave'=>2,
									'pasos'=>array(
										'Preguntas de Autenticación',
										'Preguntas de Desafío',
										'Cambio de Clave'
									),
									'tipo'=>'clave',
									'cedula.validada'=>true,
									'datasource'=>$usuario,
									'mensaje'=>'usuario.bloqueo.clave'
								);
							}
							
							$this->Session->write('seguridad.olvido', $estado);
							$this->redirect('/seguridad/olvido_clave');
						default:
							$this->CambioClave->validationErrors['clave_actual']=$this->Idg->mensaje_error;
							$this->log('error validando contraseña del usuario '.$usuario['login'],LOG_ERR);
                                                        
                                                        // Registra el log
							$this->Bitacora->registraBitacora($usuario['id'],100063,"","","","","Cambio de Contraseña");
							break;
					}
				}
			}
		}
		
		$this->data['CambioClave']['clave_nueva'] = '';
		$this->data['CambioClave']['clave_confirmacion'] = '';

		$this->layout = 'interna';

		$this->set('seccion_actual','cambio.clave');
	}

	/**
	 * Acción que muestra el resultado del cambio de clave
	**/
        function cambiar_clave_resultado(){
                    $this->layout = 'interna';

                    $this->set('seccion_actual','cambio.clave');
        }

	/**
	 * Acción que maneja el formulario de cambio de preguntas de desafío
	**/
	function cambiar_preguntas(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(17)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		if(
			$validacion_tdc == null ||
			!isset($validacion_tdc['validado']) ||
			!$validacion_tdc['validado']
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/seguridad/cambiar_preguntas',
					'layout'=>'interna',
					'titulo'=>'Cambio Preguntas Desafío',
					'pasos'=> array(
						'Tarjeta de Coordenadas',
						'Preguntas de Desaf&iacute;o',
						'Confirmación',
						'Resultado de Operación',
					),
					'paso_actual'=>0,
					'seccion_actual'=>'cambio.preguntas'
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}
		
		$usuario = $this->Session->read('usuarios.datos');

		$this->Session->write(
			'usuarios.asignar_preguntas',
			array(
				'url_aceptar'=>'',
				'layout'=>'interna',
				'titulo'=>'Cambiar Preguntas Desafío',
				'pasos'=> array(
					'Tarjeta de Coordenadas',
					'Preguntas de Desaf&iacute;o',
					'Confirmación',
					'Resultado de Operación',
				),
				'paso_1'=>1,
				'paso_2'=>2,
				'paso_3'=>3,
				'seccion_actual'=>'cambio.preguntas'
			)
		);

		$this->redirect('/usuarios/asignar_preguntas');
	}

	/**
	 * Acción que permite cambiar la imagen de seguridad utilizada
	 * como factor de autenticación en el sistema
	**/
	function cambiar_imagen(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(18)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		if(
			$validacion_tdc == null ||
			!isset($validacion_tdc['validado']) ||
			!$validacion_tdc['validado']
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/seguridad/cambiar_imagen',
					'layout'=>'interna',
					'titulo'=>'Cambiar Imagen Antipishing',
					'pasos'=> array(
						'Tarjeta de Coordenadas',
						'Selección de Imagen'
					),
					'paso_actual'=>0,
					'seccion_actual'=>'cambio.imagen'
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}

		$this->Session->write( 'usuarios.config_sello',
			array(
				'url_aceptar'=>'',
				'layout'=>'interna',
				'titulo'=>'Cambiar Imagen Antipishing',
				'pasos'=> array(
					'Tarjeta de Coordenadas',
					'Selección de Imagen'				
				),
				'paso_actual'=>1,
				'seccion_actual'=>'cambio.imagen'
			)
		);

		$this->redirect('/usuarios/config_sello');
	}	

	/**
	 * Acción que maneja el formulario de cambio de montos límite
	**/
	function modificacion_limites(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(19)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		if(
			$validacion_tdc == null ||
			!isset($validacion_tdc['validado']) ||
			!$validacion_tdc['validado']
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/seguridad/modificacion_limites',
					'layout'=>'interna',
					'titulo'=>'Cambiar L&iacute;mites Transferencias',
					'pasos'=> array(
						'Tarjeta de Coordenadas',
						'Modificaci&oacute;n de L&iacute;mites',
						'Resultado de Operación'
					),
					'paso_actual'=>0,
					'seccion_actual'=>'modificacion.limites'
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}
		$this->Session->delete('usuarios.registro');
		$this->Session->write(
			'usuarios.limite_montos',
			array(
				'url_aceptar'=>'',
				'layout'=>'interna',
				'titulo'=>'Cambiar L&iacute;mites Transferencias',
				'pasos'=> array(
					'Tarjeta de Coordenadas',
					'Modificaci&oacute;n de L&iacute;mites',
					'Confirmación',
					'Resultado de Operaci&oacute;n',					
				),
				'paso_1'=>1,
				'paso_2'=>2,
				'paso_3'=>3,
				'seccion_actual'=>'modificacion.limites'
			)
		);

		$this->redirect('/usuarios/limite_montos');
	}

	/**
	 * Acción que muestra el resultado del cambio de montos límites
	**/
	function modificacion_limites_resultado(){
		$estado = $this->Session->read('usuarios.limite_montos');

		if(
			$estado == null ||
			!isset($estado['exito']) ||
			!$estado['exito']
		){
			$this->redirect('modificacion_limites');
		}

		$this->Session->delete('seguridad.validar_tdc');
		$this->Session->delete('usuarios.limite_montos');	
		$this->Session->delete('usuarios.registro');	

		$this->layout = 'interna';

		$this->set('seccion_actual','modificacion.limites');
	}

	/**
	 * Acción para validar la tarjeta de coordenadas
	**/
	function validar_tdc(){
		if(!$this->Session->check('seguridad.validar_tdc')){
			$this->redirect('/');
		}	else {
			$estado = $this->Session->read('seguridad.validar_tdc');
		}

		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
		if( isset($estado['seccion_actual']) ){
			$this->set('seccion_actual', $estado['seccion_actual']);
		}

		$this->set('estado',$estado);
		
		if(isset($estado['usuario'])){
			$usuario = $estado['usuario'];
		} else {
			$usuario = $this->Session->read('usuarios.datos');
		}		
		
		// Validar estatus de la tdc
		$estatus = $this->Usuario->find('first', array( 'fields'=>'estatus_tdc', 'conditions'=>array('id'=>$usuario['id'])) );
		switch($estatus['Usuario']['estatus_tdc']){
			case 'N':
			case null:
				$this->redirect('tdc_no_creada');
				break;
			case 'B':
				$this->redirect('tdc_bloqueada');
				break;
		}
		
		if(!empty($this->data['Tdc'])){
			$this->Tdc->set($this->data);

			if($this->Tdc->validates()){

				if(empty($usuario['tipo_rif'])){
					$login_idg = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
				} else {
					$login_idg = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
				}
				if(Configure::read('desarrollo')){
					$res = true;
				} else {
					$res = $this->Idg->autenticar(
						$login_idg,
						array(
							$this->data['Tdc']['coordenada1'],
							$this->data['Tdc']['coordenada2'],
						),
						'GRID'
					);
				}
				
				if($res){
					$estado['validado'] = true;
					$this->Session->write('seguridad.validar_tdc', $estado);
					$this->redirect($estado['url_aceptar']);
				} else {					
					switch($this->Idg->codigo_error){
						case 'INVALID_RESPONSE':
                                                        $this->Bitacora->registraBitacora($usuario['id'],100063, "", "", "", "", "Tarjeta de Coordenadas");
							$this->redirect('advertencia_bloqueo_tdc');
						case 'AUTH_FAILED_USER_LOCKED':
						case 'USER_LOCKED':

							$this->Bitacora->registraBitacora(
								$usuario['id'],
								100005
							);
							
							$res = $this->Idg->desbloquearUsuario($login_idg, 'GRID');
						
							$res = $this->Idg->anularTdc($login_idg);
								
							$this->UsuarioCanal->cambioEstatus(
								'tdc',
								'N', // Anulada
								$usuario['id']
							);
						
							if($this->Session->check('usuarios.datos')){
								$usuario['estatus_tdc'] = 'N';
								$this->Session->write('usuarios.datos',$usuario);
							}
							
							$estado['validado']=false;
							$this->Session->write('seguridad.validar_tdc', $estado);                                                        
							$this->redirect('tdc_bloqueada');
					}
				}
			}
		}

		if(empty($usuario['tipo_rif'])){
			$login_idg = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
		} else {
			$login_idg = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
		}
		
		$res = $this->Idg->preAutenticar(
			$login_idg,
			'GRID'
		);
		
		if($res){
			$coordenadas = $res['GridChallenge']['challenge']['item'];
			$serial = $res['GridChallenge']['cardSerialNumbers']['item'];
                        $this->Bitacora->registraBitacora($usuario['id'],100062,null,null,null,null,'Modulo: '.$estado['titulo'].' Serial N° '.$serial);
			$this->set(compact('coordenadas','serial'));
                        
		} else {
			// Validar estatus de la tdc en IDG
			
			switch($this->Idg->codigo_error){
				case 'NO_ACTIVE_CARDS':
				case 'NO_ACTIVE_CARD_AFTER_ACTIVATION':					
					$this->redirect('tdc_no_creada');
					break;
				case 'AUTH_FAILED_USER_LOCKED':
				case 'USER_LOCKED':	
					if($this->Session->check('login.juridico')){
						$res = $this->Idg->desbloquearUsuario($login_idg, 'GRID');
					
						$res = $this->Idg->anularTdc($login_idg);
						
						$this->UsuarioCanal->cambioEstatus(
							'tdc',
							'N', // Anulada
							$usuario['id']
						);
						
						$nuevo_st_tdc = 'N';
					} else {
						$nuevo_st_tdc = 'B';
					}
					
					$this->UsuarioCanal->cambioEstatus(
						'tdc',
						$nuevo_st_tdc, // Bloqueada
						$usuario['id']
					);
				
					if($this->Session->check('usuarios.datos')){
						$usuario['estatus_tdc'] = $nuevo_st_tdc;
						$this->Session->write('usuarios.datos',$usuario);
					}
					
					$this->redirect('tdc_bloqueada');
				default:
					$this->set('error.fatal',$this->Idg->mensaje_error);
                                        $this->redirect('tdc_no_creada');
					break;
			}
		}		
	}
	
	/**
	 * Acción que muestra mensaje de tarjeta de coordenada no generada
	**/
	function tdc_no_creada(){
		if(!$this->Session->check('seguridad.validar_tdc')){
			$this->redirect('/');
		}	else {
			$estado = $this->Session->read('seguridad.validar_tdc');
		}

		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
		if( isset($estado['seccion_actual']) ){
			$this->set('seccion_actual', $estado['seccion_actual']);
		}

		$this->set('estado',$estado);
	}

	/**
	 * Acción que muestra mensaje de tarjeta de coordenada bloqueada
	**/
	function tdc_bloqueada(){
		if(!$this->Session->check('seguridad.validar_tdc')){
			$this->redirect('/');
		}	else {
			$estado = $this->Session->read('seguridad.validar_tdc');
		}

		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
		if( isset($estado['seccion_actual']) ){
			$this->set('seccion_actual', $estado['seccion_actual']);
		}

		$this->set('estado',$estado);
	}
        
        /**
	 * Acción que muestra mensaje de tarjeta de coordenada bloqueada o no creada para usuarios juridicos hijos
	**/
	function tdc_master(){
		
                if(!$this->Session->read('seguridad.olvido')){
			$this->redirect('/');
		}	else {
			$estado = $this->Session->read('seguridad.olvido');
		}

		$this->layout = $estado['layout'];	

		$this->set('estado',$estado);
	}
        
	/**
	 * Acción que muestra mensaje de advertencia de bloqueo de tarjeta de coordenada
	**/
	function advertencia_bloqueo_tdc(){
		if(!$this->Session->check('seguridad.validar_tdc')){
			$this->redirect('/');
		} else {
			$estado = $this->Session->read('seguridad.validar_tdc');
		}

		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
		if( isset($estado['seccion_actual']) ){
			$this->set('seccion_actual', $estado['seccion_actual']);
		}

		$this->set('estado',$estado);
	}	

	/**
	 * Acción para validar las preguntas de autenticación
	**/
	function validar_preguntas_autenticacion(){
		if(!$this->Session->check('seguridad.validar_preguntas_autenticacion')){
			$this->redirect('/');
		} else {
			$estado = $this->Session->read('seguridad.validar_preguntas_autenticacion');
		}
		
		if($this->Session->check('usuarios.registro')){
			$registro = $this->Session->read('usuarios.registro');
			if($this->Session->check('login.juridico')){
				$tipo_cedula = $registro['Usuario']['tipoRif'];
				$cedula = $registro['Usuario']['nroRif'];
			}else{
				$tipo_cedula = $registro['Usuario']['tipoCedulaRif'];
				$cedula = $registro['Usuario']['nroCedulaRif'];
			}
			$registrando = true;
		} else if($this->Session->check('usuarios.datos')){
			$usuario = $this->Session->read('usuarios.datos');
			
			/** Para identificar si es un usuario juridico **/
			if($usuario['tipo_rif']){
				$tipo_cedula = $usuario['tipo_rif'];
				$cedula = desencriptar($usuario['rif']);
			}
			else{
				$tipo_cedula = $usuario['tipo_cedula'];
				$cedula = desencriptar($usuario['cedula']);
			}
			/** Para identificar si es un usuario juridico **/

			$idUser = $usuario['id'];
			$registrando = false;
		} else if($this->Session->check('seguridad.olvido')){
			$olvido = $this->Session->read('seguridad.olvido');
			$tipo_cedula = $olvido['datasource']['tipo_cedula'];
			$cedula = desencriptar($olvido['datasource']['cedula']);
			$idUser = $olvido['datasource']['id'];
			$registrando = false;
			
			/* EN CASO DE QUE SEA UN USUARIO JURIDICO */
			if(!empty($olvido['datasource']['tipo_rif'])){
				$tipo_cedula = $olvido['datasource']['tipo_rif'];
				$cedula = desencriptar($olvido['datasource']['rif']);
			}
		}
		
		if(!isset($estado['preguntas'])){
			$res = $this->Activo->invoca(
				'PREG0AUT',
				array(
					'req'=>array(
						'canal'=>Configure::read('canal.ib'),
						'cantidad'=>$estado['cantidad_preguntas_autenticacion'],
						'nacionalidad'=>$tipo_cedula,
						'numeroIdentificacion'=>str_pad($cedula,12,'0',STR_PAD_LEFT)
					)
				)
			);
			
			$preguntas = array();
			
			if($res && !isset($res['PREG0AUTReturn']['error']['ERRNUM'])){
				$x = $res['PREG0AUTReturn']['preguntas']['preguntas'];
				
				normaliza($x);
				foreach($x as $i){
					if(!empty($i['codigoPadre'])){
						$preguntas[$i['codigoPadre']]['Subpreguntas'][]=$i;
					} else {
						$preguntas[$i['codigo']]= $i;
					}
				}
			} else {
				$this->set('error_fatal',_m('error.fatal'));
			}
				
			$estado['preguntas'] = array_values($preguntas);

			$this->Session->write('seguridad.validar_preguntas_autenticacion', $estado);
		}
		
		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
		if( isset($estado['seccion_actual']) ){
			$this->set('seccion_actual', $estado['seccion_actual']);
		}

		$this->set('estado',$estado);

		if(!empty($this->data['RespuestasAutenticacion'])){
			$valido = false;
			
			$req = array(
				'canal' => Configure::read('canal.ib'),
				'nacionalidad' => $tipo_cedula,
				'numeroIdentificacion'=>str_pad($cedula,12,'0',STR_PAD_LEFT),
				'respuestas'=>array('item'=>array())
			);

			for($i=0;$i<count($estado['preguntas']);$i++){
				$p = $estado['preguntas'][$i];
				$r = $this->data['RespuestasAutenticacion']['respuestas'][$i];
	
				switch($p['formularioTipo']){
					case 't':
						$req['respuestas']['item'][] = array(
							'codigo'=> $p['codigo'],
							'respuesta'=> $r['valor'],
							'tipoRespuesta'=> $p['tipoRespuesta']
						);
						break;
					case 'r':
						$req['respuestas']['item'][] = array(
							'codigo'=> $p['codigo'],
							'respuesta'=> $r['opcion'],
							'tipoRespuesta'=> $p['tipoRespuesta']
						);
						
						if(!empty($p['Subpreguntas'])){
							$req['respuestas']['item'][] = array(
								'codigo'=> $p['Subpreguntas'][0]['codigo'],
								'respuesta'=> $r['valor'],
								'tipoRespuesta'=> $p['Subpreguntas'][0]['tipoRespuesta']
							);
						}
						break;
				}
			}
			
			$res = $this->Activo->invoca(
				'CHECK0PREG0AUT',
				array(
					compact('req')
				)
			);
			
			if($res && $res['CHECK0PREG0AUTReturn']['error']['ERRNUM']=='0'){
				$valido = true;
			}

			if(Configure::read('desarrollo')) $valido = true;

			$estado['validado'] = $valido;
			$this->Session->write('seguridad.validar_preguntas_autenticacion', $estado);

			if($valido){
				$this->redirect($estado['url_aceptar']);
			} else {
				if($registrando){
					
					$idUser = $this->UsuarioCanal->bloquearRegistro(
						$tipo_cedula,
						$cedula
					);
					
					if($idUser){
						$this->Bitacora->registraBitacora(
							$idUser,
							100002
						);
					}
					
				} else {
					$this->Bitacora->registraBitacora(
						$idUser,
						100002
					);				
					
					$this->UsuarioCanal->cambioEstatus(
						'acceso',
						'BA',
						$idUser
					);
				}				
				$this->redirect('bloqueo_autenticacion');
			}
		}
	}

	/**
	 * Acción para mostrar mensaje de bloqueo por responder erróneamente
	 * algunas preguntas de autenticación
	**/
	function bloqueo_autenticacion(){
		if(!$this->Session->check('seguridad.validar_preguntas_autenticacion')){
			$this->redirect('/');
		} else {
			$estado = $this->Session->read('seguridad.validar_preguntas_autenticacion');
		}

		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
		if( isset($estado['seccion_actual']) ){
			$this->set('seccion_actual', $estado['seccion_actual']);
		}

		$this->set('estado',$estado);

		if(
			!isset($estado['validado']) ||
			$estado['validado']
		){
			$this->redirect('/');
		}
	}

	/**
	 * Acción para validar las preguntas de desafío
	**/
	function validar_preguntas_desafio(){
		if(!$this->Session->check('seguridad.validar_preguntas_desafio')){
			$this->redirect('/');
		}	else {
			$estado = $this->Session->read('seguridad.validar_preguntas_desafio');
		}

		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
		if( isset($estado['seccion_actual']) ){
			$this->set('seccion_actual', $estado['seccion_actual']);
		}

		$this->set('estado',$estado);
			
		if(empty($estado['usuario']['tipo_rif'])){
			$login_idg = $estado['usuario']['tipo_cedula'].desencriptar($estado['usuario']['cedula']);
		} else {
			$login_idg = $estado['usuario']['tipo_rif'].desencriptar($estado['usuario']['rif']).$estado['usuario']['tipo_cedula'].desencriptar($estado['usuario']['cedula']).desencriptar($estado['usuario']['login']);
		}

		if(!empty($this->data['RespuestaDesafio'])){
			$respuestas = $this->data['RespuestaDesafio']['respuesta'];
			
			if(Configure::read('desarrollo')){
				$res = true;
			} else {
				$res = $this->Idg->autenticarQA(
					$login_idg,
					$respuestas,
					$estado['cantidad_preguntas_desafio']
				);
			}
			
			if($res){
				$estado['validado'] = true;
				$this->Session->write('seguridad.validar_preguntas_desafio', $estado);
				$this->redirect($estado['url_aceptar']);
			} else {
				$estado['validado']=false;
				
				switch($this->Idg->codigo_error){
					case 'INVALID_RESPONSE':
                                                $this->Bitacora->registraBitacora($estado['usuario']['id'],100063,"","","","","Preguntas de Desafío");
						$this->redirect('desafio_advertencia');
					case 'USER_LOCKED':
					case 'AUTH_FAILED_USER_LOCKED':
						$this->Bitacora->registraBitacora(
							$estado['usuario']['id'],
							100003
						);
						
						$this->UsuarioCanal->cambioEstatus(
							'acceso',
							'BD',
							$estado['usuario']['id']
						);
						
						$this->redirect('desafio_bloqueo');
				}
			}
		} else {
			$res = $this->Idg->preAutenticarQA(
				$login_idg,
				$estado['cantidad_preguntas_desafio']
			);
			
			if($res){
				$preguntas = $res['QAChallenge']['item'];
				$this->set(compact('preguntas'));
			} else {
				switch($this->Idg->codigo_error){
					case 'USER_LOCKED':
					case 'AUTH_FAILED_USER_LOCKED':
						$this->redirect('desafio_bloqueo');
						break;
					case 'QA_NOT_ENOUGH_QUESTIONS':
						$estado['validado']	= true;
						$this->Session->write('seguridad.validar_preguntas_desafio', $estado);
						$this->redirect($estado['url_aceptar']);						
						break;
					default:
						$this->set('error_fatal',_m('error.fatal'));
				}
			}
		}
	}

	/**
	 * Acción para mostrar mensaje de advertencia de bloqueo por respuesta
	 * errónea en la validación de preguntas de desafío
	**/
	function desafio_advertencia(){
		if(!$this->Session->check('seguridad.validar_preguntas_desafio')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('seguridad.validar_preguntas_desafio');

		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}
	}

	/**
	 * Acción para mostrar mensaje de bloqueo por respuesta errónea
	 * en la validación de las preguntas de desafío
	**/
	function desafio_bloqueo(){
		if(!$this->Session->check('seguridad.validar_preguntas_desafio')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('seguridad.validar_preguntas_desafio');

		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}
	}

	/**
	 * Acción para desbloquear el usuario
	**/
	function desbloqueo_usuario(){
		$this->Session->write('seguridad.olvido',array(
			'titulo'=>'Desbloqueo de usuario',
			'Olvido'=>array(
				'tipo'=>'clave'
			)
		));

		$this->redirect('olvido_cedula');
	}

	/**
	 * Acción que muestra un formulario con las opciones de olvido de usuario
	 * contraseña, imagen de seguridad y preguntas de desafío
	**/
	function olvido_tipo(){
		$this->layout = "externa";

		if(isset($this->data['Olvido'])){
			$this->Olvido->set($this->data);

			if($this->Olvido->validates(
				array(
					'fieldList'=>array(
						'tipo'
					)
				)
			)){
				$estado = $this->Session->read('seguridad.olvido');

				switch($this->data['Olvido']['tipo']){
					case 'clave':
						$estado['titulo'] = 'Olvido de clave';
						$estado['paso_cedula'] = 1;
						
						if( $this->Session->check('login.juridico') ){ // PARA JURIDICO
							$estado['paso_desafio'] = 2;                                                        
							$estado['paso_clave'] = 3;
							$estado['pasos'] = array(
								'Tipo de Olvido',
								'Autenticación del Cliente',
								'Preguntas de Desafío',
								'Cambio de Clave'
							);
						}
						else { // PARA NATURAL
                                                        $estado['paso_codigo'] = 2;
							$estado['paso_autenticacion'] = 3;
							$estado['paso_desafio'] = 4;
							$estado['paso_clave'] = 5;
							$estado['pasos'] = array(
								'Tipo de olvido',
								'Autenticación del Cliente',
                                                                'Código de Autorización',
                                                                'Preguntas de Autenticación',
								'Preguntas de Desafío',
								'Cambio de Clave'
							);
						}
						$estado['tipo'] = 'clave';
						break;
					case 'sello':
						$estado['titulo'] = 'Olvido de Imagen Antiphishing o Sello Personalizado';
						$estado['paso_actual'] = '1';
						$estado['paso_cedula'] = 1;
						
						if( $this->Session->check('login.juridico') ){ // PARA JURIDICO
							$estado['paso_desafio'] = 2;
							$estado['paso_sello'] = 3;
							$estado['pasos'] = array(
								'Tipo de Olvido',                                                                
								'Autenticación del Cliente',
								'Preguntas de Desafío',
								'Cambio de Sello'
							);
						}
						else{ // PARA NATURAL
							$estado['paso_codigo'] = 3;
                                                        $estado['paso_desafio'] = 4;
							$estado['paso_sello'] = 5;
							$estado['pasos'] = array(
								'Tipo de Olvido',
								'Autenticación del Cliente',
                                                                'Código de Autorización',
								'Preguntas de Autenticación',
								'Preguntas de Desafío',
								'Cambio de Sello'
							);
						}
						$estado['tipo'] = 'sello';
						break;
					case 'usuario':
                                                
                                                $estado['titulo'] = 'Olvido de usuario';
                                            
                                                if( $this->Session->check('login.juridico') ){ // PARA JURIDICO
							$estado['paso_actual'] = '1';
                                                        $estado['paso_cedula'] = 1;
                                                        $estado['pasos'] = array(
                                                                'Tipo de Olvido',
                                                                'Autenticación del Cliente',
                                                                'Envío de Usuario'
                                                        );
						}
						else{ // PARA NATURAL
							$estado['paso_actual'] = '1';
                                                        $estado['paso_cedula'] = 1;
                                                        $estado['paso_codigo'] = 2;
                                                        $estado['pasos'] = array(
                                                                'Tipo de Olvido',
                                                                'Autenticación del Cliente',
                                                                'Código de Autorización',
                                                                'Envío de Usuario'
                                                        );                                                        							
						}
                                                												
						$estado['tipo'] = 'usuario';
						break;
					case 'preguntas':
						$estado['titulo'] = 'Olvido de Preguntas y Respuestas de Desafío';
                                                $estado['paso_cedula'] = 1;
                                                
                                                if( $this->Session->check('login.juridico') ){ // PARA JURIDICO							
                                                        $estado['pasos'] = array(
                                                                'Tipo de Olvido',
                                                                'Autenticación del Cliente',                                                                
                                                                'Preguntas de Desafío'
                                                        );
						}
						else{ // PARA NATURAL
							$estado['paso_codigo'] = 2;
                                                        $estado['paso_autenticacion'] = 3;
                                                        $estado['paso_qa'] = 4;
                                                        $estado['usar_tdc']=false;
                                                        $estado['pasos'] = array(
                                                                'Tipo de Olvido',
                                                                'Autenticación del Cliente',
                                                                'Código de Autorización',
                                                                'Preguntas de Autenticación',
                                                                'Preguntas de Desafío'
                                                        );                                                        							
						}						                                                						
						
						$estado['tipo'] = 'preguntas';
						break;
				}

				$this->Session->write('seguridad.olvido',$estado);
				$this->redirect('olvido_cedula');
			}
		} elseif(!$this->Session->check('seguridad.olvido')){
			$titulo = 'Olvido de Usuario, Clave, Sello Personal o Preguntas y Respuestas de Desafío';

			$this->Session->write('seguridad.olvido',array(
				'titulo'=>$titulo
			));
		}

		$estado = $this->Session->read('seguridad.olvido');
		$this->set('titulo',$estado['titulo']);
	}

	/**
	 * Acción para el primer paso en el proceso de olvido. En el cual se valida
	 * la cédula del usuario
	**/
	function olvido_cedula(){
		if(!$this->Session->check('seguridad.olvido')){
			$this->redirect('/');
		}
		
		$this->layout = "externa";
		$estado = $this->Session->read('seguridad.olvido');
		$estado['paso_actual']=$estado['paso_cedula'];
		$this->set('estado',$estado);
		
		if(isset($this->data['Olvido'])){
			$this->Olvido->set($this->data);
			$this->set('titulo',$estado['titulo']);
			
			$array_validate = array(
				'tipo',
				'tipoCedulaRif',
				'nroCedulaRif'
			);
				
			// PARA JURIDICO
			if( $this->Session->check('login.juridico') ){
				$array_validate = array_merge($array_validate, array('tipoRif','nroRif','loginUsuario'));
			}
			
			if( $this->Olvido->validates( array( 'fieldList'=> $array_validate ) ) ){				
				$this->Usuario->set($this->data);
				$flag_verificacion = true;
				
				// SELECCION DEL USUARIO PARA JURIDICO O NATURAL
				if( $this->Session->check('login.juridico') ){
					$usuario = $this->UsuarioCanal->userByRifCedula();
					$flag_verificacion = ($estado['tipo'] != 'usuario');
				} else {
					$usuario = $this->UsuarioCanal->userByCedula();
					
					if($usuario){
						$tipo_documento = $usuario['tipo_cedula'];
						$nro_documento = desencriptar($usuario['cedula']);
						
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
						
						// Sustituir nombre en caso de que el cliente lo haya cambiado en la agencia

						$usuario['nombre'] = encriptar($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'].' '.$datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE'].' '.$datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'].' '.$datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO']);
						$usuario['email_principal'] = encriptar($datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3']);
						$usuario['celular'] = encriptar($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR']);
					}
				}
				
				// verifica si encontro al usuario
				if(!empty($usuario)){
					if($estado['tipo']=='preguntas' && in_array($usuario['rol_juridico_id'],array(3,4,5))){
						// Si es un jurídico y no es máster único
						// mostrar mensaje
						
						$this->Olvido->validationErrors['nroCedulaRif'] = _m('no.master');
					}
					
					
					// Verifica si es un usuario juridico no master
					if( !empty($usuario['tipo_rif']) && $usuario['rol_juridico_id']>2 ){
					
						// Verifica si el master de la empresa esta registrado y que no esta suspendido o bloqueado por seguridad
						$master = $this->UsuarioCanal->encontrarUsuario(
							array(
								'rol_juridico_id'=>1,
								'tipo_rif'=>$usuario['tipo_rif'],
								'rif'=>$usuario['rif'],
								'estatus_acceso'=>array('BS', 'ST'),
							)
						);

						// Si existe el master con las condiciones anteriores no permite la entrada al sistema
						if($master['Usuario']){
							$this->Olvido->validationErrors['nroCedulaRif'] = _m('usuario.bloqueo.acceso');
						}
					}
				
					// En caso de ser un juridico y el proceso de olvido de usuario no realiza validacion
					if(empty($this->Olvido->validationErrors['nroCedulaRif']) && $flag_verificacion){
						// Verificar el estado del registro
						switch($usuario['estatus_registro']){
							case 'P':							
								$flag_siguiente = false;
								$this->Olvido->validationErrors['nroCedulaRif'] = _m('registro.pendiente');
								break;
							case 'B':
								$flag_siguiente = false;
								$this->Olvido->validationErrors['nroCedulaRif'] = _m('autenticacion.b.2.22');
								break;
							case 'I1':
							case 'I2':
								$flag_siguiente = false;
								$this->Olvido->validationErrors['nroCedulaRif'] = _m('usuario.oficina.comercial');
								break;                                                          
						}
					}
					
					// En caso de ser un juridico y el proceso de olvido de usuario no realiza validacion
					if(empty($this->Olvido->validationErrors['nroCedulaRif']) && $flag_verificacion){
						// Verifica el estatus del usuario
						switch (strtoupper($usuario['estatus_acceso'])){
							case 'BA':
							case 'BD':
							case 'BI':
							case 'BS':
								if($usuario['perfil_ib_id']==2){ // juridico
									$this->Olvido->validationErrors['nroCedulaRif'] = _m('registro.bloqueado');
								}
								else{ // natural
									$this->Olvido->validationErrors['nroCedulaRif'] = _m('autenticacion.b.2.22');
								}
								break;
							case 'I1':
							case 'I2':
								$this->Olvido->validationErrors['nroCedulaRif'] = _m('registro.bloqueado');
								break;
							case 'BC':
								if(
									$estado['tipo']=='clave' ||
									$estado['tipo']=='preguntas'
								){
									break;
								}
							case 'P':
								$this->Olvido->validationErrors['nroCedulaRif'] = _m('usuario.bloqueo.acceso');
								break;
							case 'ST':
								$this->Olvido->validationErrors['nroCedulaRif'] = _m('suspension.temporal.usuario');
								break;
							default :
								$this->Olvido->validationErrors['nroCedulaRif'] = _m('usuario.error');
								break;
						}
					}
					
					// En caso de ser un juridico y el proceso de olvido de usuario no realiza validacion
					if(empty($this->Olvido->validationErrors['nroCedulaRif']) && $flag_verificacion){
						// Verifica el estatus del usuario en IDG
						
						if(empty($usuario['tipo_rif'])){
							$login_idg = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
						} else { 
							$login_idg = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
						}
					
						$res = $this->Idg->preAutenticar($login_idg,'QA');
					
						if($res){
							if(isset($this->Idg->codigo_error)){
								switch($this->Idg->codigo_error){
									case 'USER_LOCKED':
									case 'AUTH_FAILED_USER_LOCKED':										
										$this->UsuarioCanal->cambioEstatus(
											'acceso',
											'BD',
											$login['id']
										);
										
										$this->Olvido->validationErrors['nroCedulaRif'] = 'usuario.callcenter';
										break;
								}
							}
						}
					}
					
					if(empty($this->Olvido->validationErrors['nroCedulaRif'])){
						$estado['cedula.validada'] = true;
						
						// Datos del usuario
						$estado['datasource'] = $usuario;
						
						if($estado['tipo']=='preguntas'){
							// Preparar los pasos
								
							$estado['pasos'] = array(
								'Tipo de Olvido',
								'Autenticación del Cliente'
							);
								
							if($usuario['rol_juridico_id']==1){
								// Si es un usuario master, no hacer preguntas de autenticación
								
								$this->Session->write(
									'seguridad.validar_preguntas_autenticacion',
									array(
										'validado'=>true
									)
								);
							} else {
								// Para master único y natural si hacer preguntas de autenticación
								$estado['paso_codigo'] = count($estado['pasos']);
								$estado['pasos'][]='Código de Autorización';
                                                                $estado['paso_autenticacion'] = count($estado['pasos']);
                                                                $estado['pasos'][]='Preguntas de Autenticación';
							}
						
							if($usuario['estatus_tdc']=='A' && $usuario['rol_juridico_id']==1){
								// verificar si tiene tdc activa

								if(empty($usuario['tipo_rif'])){
									$login_idg = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
								} else {
									$login_idg = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
								}
								
								// Verificar el estado de la tdc en idg
								$res = $this->Idg->preAutenticar(
									$login_idg,
									'GRID'
								);
								
								if($res){
									// La tarjeta de coordenada existe y está activa
									// usarla entonces para la autenticación del usuario
									
									$estado['usar_tdc'] = true;
									$estado['paso_tdc'] = count($estado['pasos']);								
									$estado['pasos'][]='Tarjeta de Coordenadas';
								}
							}							                                                                                                                                                                   
                                                        
							$estado['paso_qa'] = count($estado['pasos']);
							$estado['pasos'][]='Preguntas de Desafío';
							
							if(
								$usuario['rol_juridico_id']==1 &&
								!$estado['usar_tdc']
							){
								// como el usuario jurídico no puede autenticarse
								// con tdc o preguntas de autenticación
								// tiene que descargar una planilla e ir a la agencia
								
								$estado['paso_planilla'] = count($estado['pasos']);
								$estado['pasos'][]='Descarga Planilla';
								$estado['necesita_planilla']=true;
							}
						
						} elseif($estado['tipo']=='sello'){
							
                                                        // Preparar los pasos								
							$estado['pasos'] = array(
								'Tipo de Olvido',
								'Autenticación del Cliente'
							);							                                                                                                                
                                                        
							if($usuario['rol_juridico_id']==2){
                                                                //Para master unico no solicitar preguntas de autenticacion                                                                
                                                                $estado['paso_codigo'] = count($estado['pasos']);
								$estado['pasos'][]='Código de Autorización';                                                                
                                                        } elseif(empty($usuario['rol_juridico_id'])) {
								// Para natural
								$estado['paso_codigo'] = count($estado['pasos']);
								$estado['pasos'][]='Código de Autorización';
                                                                $estado['paso_autenticacion'] = count($estado['pasos']);
                                                                $estado['pasos'][]='Preguntas de Autenticación';                                                                
							}							
                                                        $estado['paso_desafio'] = count($estado['pasos']);
                                                        $estado['pasos'][]='Preguntas de Desafío';
                                                        $estado['paso_sello'] = count($estado['pasos']);
							$estado['pasos'][]='Cambio de Sello';
                                                        
						} elseif($estado['tipo']=='clave'){
							
                                                        // Preparar los pasos								
							$estado['pasos'] = array(
								'Tipo de Olvido',
								'Autenticación del Cliente'
							);							                                                                                                                
                                                        
							if($usuario['rol_juridico_id']==2){
                                                                //Para master unico no solicitar preguntas de autenticacion                                                                
                                                                $estado['paso_codigo'] = count($estado['pasos']);
								$estado['pasos'][]='Código de Autorización';
                                                        } elseif($usuario['rol_juridico_id']>2){
                                                            
                                                            $estado['paso_tdc'] = count($estado['pasos']);								
                                                            $estado['pasos'][]='Tarjeta de Coordenadas';
                                                            
                                                            if($usuario['estatus_tdc']=='A'){
								// verificar si tiene tdc activa
								
								$login_idg = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);								
								
								// Verificar el estado de la tdc en idg
								$res = $this->Idg->preAutenticar(
									$login_idg,
									'GRID'
								);
								
								if($res){
                                                                    // La tarjeta de coordenada existe y está activa
                                                                    // usarla entonces para la autenticación del usuario

                                                                    $estado['usar_tdc'] = true;                                                                    
								}
                                                                
                                                            }else{
                                                                    $estado['titulo']= "Olvido de clave";
                                                                    $estado['layout'] = "externa";
                                                                    $this->Session->write('seguridad.olvido',$estado);
                                                                    $this->redirect('tdc_master');                                                                                                                                            
                                                            }
                                                                                                                                                                                                                                                                                                                                                                            
                                                       } elseif(empty($usuario['rol_juridico_id'])){
								// Para natural
								$estado['paso_codigo'] = count($estado['pasos']);
								$estado['pasos'][]='Código de Autorización';
                                                                $estado['paso_autenticacion'] = count($estado['pasos']);
                                                                $estado['pasos'][]='Preguntas de Autenticación';                                                                
							}
                                                        
                                                        $estado['paso_desafio'] = count($estado['pasos']);
                                                        $estado['pasos'][]='Preguntas de Desafío';
                                                        $estado['paso_clave'] = count($estado['pasos']);
							$estado['pasos'][]='Cambio de Clave';
                                                        
						} elseif($estado['tipo']=='usuario'){
							
                                                        // Preparar los pasos								
							$estado['pasos'] = array(
								'Tipo de Olvido',
								'Autenticación del Cliente'
							);							                                                                                                                
                                                        
                                                        //Si es master unico o natural solicito otp
							if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id']==2){
                                                                $estado['paso_codigo'] = count($estado['pasos']);
								$estado['pasos'][]='Código de Autorización';
                                                        }	
                                                        $estado['paso_envio'] = count($estado['pasos']);
							$estado['pasos'][]='Envío de Usuario';
						}
						
						$this->Session->write('seguridad.olvido',$estado);
						$this->redirect('olvido_' . $estado['tipo']);
					}
				} else {
					if( $this->Session->check('login.juridico') && $estado['tipo']!='usuario'){
						$this->Olvido->validationErrors['loginUsuario'] = 'usuario.no.existe';
					}
					else{
						$this->Olvido->validationErrors['nroCedulaRif'] = 'cedula.no.existe';
					}
				}
			}
		}
	}

	/**
	 * Acción para iniciar el proceso de olvido de usuario
	**/
	function olvido_usuario(){
		
		if(!$this->Session->check('seguridad.olvido')){
			$this->redirect('/');
		} else {
			$this->Session->delete('seguridad.validar_preguntas_autenticacion');
			$this->Session->delete('seguridad.validar_preguntas_desafio');

			$estado = $this->Session->read('seguridad.olvido');			                        
                                        
                        //No solicitar otp para perfil master ni para sus usuarios
                        if($estado['datasource']['rol_juridico_id']==1 || $estado['datasource']['rol_juridico_id']>2){
                            $this->Session->write(
                                            'seguridad.validar_otp',
                                            array(
                                                    'validado'=>true
                                            )
                                    );
                        }
                
			if(!isset($estado['cedula.validada'])){
				$this->redirect('olvido_cedula');
			}
                        
                        $validacion_otp = $this->Session->read('seguridad.validar_otp');

                        if(			
                                (
                                    $validacion_otp == null ||
                                    !isset($validacion_otp['validado']) ||
                                    !$validacion_otp['validado']
                                )
                        ){
                            $this->Session->write(
                                            'seguridad.validar_otp',
                                            array(
                                                    'url_aceptar'=>'olvido_usuario',
                                                    'url_volver' => '/',
                                                    'layout'=>'externa',
                                                    'titulo'=>$estado['titulo'],
                                                    'pasos'=> $estado['pasos'],
                                                    'paso_actual'=>$estado['paso_codigo']                                                   
                                            )
                            );

                            $this->redirect('genera_otp');
                        }
			
			if($this->Session->check('login.juridico')){
				
				// Busca los usuarios de la empresa con la cedula correspondiente
				$listado = $this->UsuarioCanal->find(
					'all', 
					array(
						'fields'=> array('Usuario.nombre', 'Usuario.login', 'Usuario.email_principal'),
						'conditions' => array(
							'tipo_rif'=>$estado['datasource']['tipo_rif'],
							'rif'=>$estado['datasource']['rif'],
							'tipo_cedula'=>$estado['datasource']['tipo_cedula'],
							'cedula'=>$estado['datasource']['cedula'],
							'estatus_acceso'=>'A',
						)
					)
				);
				
				// Bandera para indicar si falla algunos de los envios de correo
				$estado['usuario_enviado'] = true;
				
				// Envia los correos a cada uno de los usuarios
				foreach ($listado as $u){
					
					$temp = $u['Usuario'];
					
					// Email Options
					$this->Email->smtpOptions = Configure::read('email.smtp');
					$this->Email->delivery = 'smtp';
					$this->Email->from = Configure::read('email.from');
					$this->Email->subject = 'Notificacion de Recuperacion de Usuario';
					$this->Email->sendAs = 'html';
					$this->Email->template = 'olvido_usuario';
				
					// Informacion del correo
					$this->Email->to = desencriptar($temp['email_principal']);
					$this->set('usuario', $temp);

					// Envio del correo
					$estado['usuario_enviado'] &= $this->Email->send();
					
					$this->Email->reset();
				}
				
			}
			else{
				// Email Options
				$this->Email->smtpOptions = Configure::read('email.smtp');
				$this->Email->delivery = 'smtp';
				$this->Email->from = Configure::read('email.from');
				$this->Email->to = desencriptar($estado['datasource']['email_principal']);
				$this->Email->subject = 'Notificacion de Recuperacion de Usuario';
				$this->Email->sendAs = 'html';

				// Informacion del correo
				$this->Email->template	= 'olvido_usuario';
				$this->set('usuario', $estado['datasource']);

				// Envio del correo
				$estado['usuario_enviado'] = $this->Email->send();
			}
			
                        $estado['paso_actual'] = $estado['paso_envio'];
			$this->Session->write('seguridad.olvido',$estado);
			$this->redirect('usuario_enviado');
		}
	}

	/**
	 * Acción para mostrar mensaje de usuario enviado a la dirección de correo
	 * electrónico configurado por el usuario en el momento de su registro
	**/
	function usuario_enviado(){
		if(!$this->Session->check('seguridad.olvido')){
			$this->redirect('/');
		}

		$estado = $this->Session->read('seguridad.olvido');

		$this->Session->delete('seguridad.olvido');	

		if(!isset($estado['usuario_enviado'])){
			$this->redirect('/');
		}

		$this->layout = 'externa';
		$this->set('estado', $estado);
	}

	/**
	 * Acción para iniciar el proceso de olvido de contraseña del usuario
	**/
	function olvido_clave(){
		$this->layout = "externa";
		
		if( $this->Session->check('login.juridico') ){ // PARA JURIDICO
			$this->Session->write('seguridad.validar_preguntas_autenticacion', array('validado'=>true));
		}
                
                $estado_olvido = $this->Session->read('seguridad.olvido');
                
                //No solicitar otp para perfil master ni para sus usuarios
                if($estado_olvido['datasource']['rol_juridico_id']==1 || $estado_olvido['datasource']['rol_juridico_id']>2){
                    $this->Session->write(
                                    'seguridad.validar_otp',
                                    array(
                                            'validado'=>true
                                    )
                            );
                }
		
		$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
                $estado_desafio = $this->Session->read('seguridad.validar_preguntas_desafio');		                               
                $validacion_otp = $this->Session->read('seguridad.validar_otp');
		$estado_tdc = $this->Session->read('seguridad.validar_tdc');
                
		if(!$this->Session->check('seguridad.olvido')){
			$this->redirect('/');
		} elseif(!isset($estado_olvido['cedula.validada'])){
			$this->redirect('/');                
		} elseif($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado']){                    
                        $this->Session->write(
                                'seguridad.validar_otp',
                                array(
                                        'url_aceptar'=>'/seguridad/olvido_clave',
                                        'url_volver' => '/',
                                        'layout'=>'externa',
                                        'titulo'=>$estado_olvido['titulo'],
                                        'pasos'=> $estado_olvido['pasos'],
                                        'paso_actual'=>$estado_olvido['paso_codigo']
                                )
                        );

                        $this->redirect('genera_otp');
                } elseif(!$this->Session->check('seguridad.validar_preguntas_autenticacion')){
			// Colocar data para el manejador de preguntas de seguridad
			$this->Session->write(
				'seguridad.validar_preguntas_autenticacion',
				array(
					'url_aceptar'=>'/seguridad/olvido_clave',
					'url_volver'=>'/',
					'layout'=>'externa',
					'titulo'=>$estado_olvido['titulo'],
					'cantidad_preguntas_autenticacion'=>4,
					'pasos'=> $estado_olvido['pasos'],
					'paso_actual'=>$estado_olvido['paso_autenticacion'],
					'mensaje'=>$estado_olvido['mensaje'],
					'usuario'=>$estado_olvido['datasource']
				)
			);

			// Redireccionar a las preguntas de seguridad
			$this->redirect('validar_preguntas_autenticacion');		                  
                } elseif(
			!isset($estado_autenticacion['validado']) ||
			!$estado_autenticacion['validado']
		){
			// El usuario no completó las preguntas de autenticación
			$this->redirect('validar_preguntas_autenticacion');
                }elseif(
			isset($estado_olvido['usar_tdc']) && $estado_olvido['usar_tdc'] &&
			!$this->Session->check('seguridad.validar_tdc')
		){			

			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/seguridad/olvido_clave',
					'layout'=>'externa',
					'titulo'=>$estado_olvido['titulo'],					
					'pasos'=> $estado_olvido['pasos'],
					'paso_actual'=>$estado_olvido['paso_tdc'],					
					'usuario'=>$estado_olvido['datasource']					
				)
			);

			// Redireccionar a las preguntas de tdc
			$this->redirect('validar_tdc');
		} elseif(
			isset($estado_olvido['usar_tdc']) && $estado_olvido['usar_tdc'] &&
			(
                            !isset($estado_tdc['validado']) ||
                            (!$estado_tdc['validado'])
			)
		){
			// El usuario no completó la verificación de tdc
			$this->redirect('validar_tdc');
                        
                } elseif(!$this->Session->check('seguridad.validar_preguntas_desafio')){
			// Colocar data para el manejador de preguntas de seguridad
			$this->Session->write(
				'seguridad.validar_preguntas_desafio',
				array(
					'url_aceptar'=>'/seguridad/olvido_clave',
					'url_volver'=>'/',
					'layout'=>'externa',
					'titulo'=>$estado_olvido['titulo'],
					'cantidad_preguntas_desafio'=>3,
					'pasos'=> $estado_olvido['pasos'],
					'paso_actual'=>$estado_olvido['paso_desafio'],
					'usuario'=>$estado_olvido['datasource']
				)
			);

			// Redireccionar a las preguntas de seguridad
			$this->redirect('validar_preguntas_desafio');
		} elseif(!isset($estado_desafio['validado']) || !$estado_desafio['validado']){
			// El usuario no completó las preguntas de autenticación
			$this->redirect('validar_preguntas_desafio');		        
		} elseif(!empty($this->data['CambioClave'])){
			$this->CambioClave->set($this->data);

			if($this->CambioClave->validates(
				array(
					'fieldList'=>array(
						'clave_nueva',
						'clave_confirmacion'
					)
				)
			)){
				$valido = true;
						
				if(empty($estado_olvido['datasource']['tipo_rif'])){
					$login_idg = $estado_olvido['datasource']['tipo_cedula'].desencriptar($estado_olvido['datasource']['cedula']);
				} else { 
					$login_idg = $estado_olvido['datasource']['tipo_rif'].desencriptar($estado_olvido['datasource']['rif']).$estado_olvido['datasource']['tipo_cedula'].desencriptar($estado_olvido['datasource']['cedula']).desencriptar($estado_olvido['datasource']['login']);
				}
		
				$res = $this->Idg->desbloquearUsuario($login_idg);
				
				if($res){
					$res = $this->Idg->cambiarContrasena(
						$login_idg,
						$this->data['CambioClave']['clave_nueva'],
						30
					);
						
					if($res){
						$res = $this->UsuarioCanal->cambioEstatus('acceso', 'A', $estado_olvido['datasource']['id']);
						
						if($res){
							$this->Bitacora->registraBitacora(
								$estado_olvido['datasource']['id'],
								15
							);
							
							$estado_olvido['clave_cambiada'] = true;
							$estado_olvido['paso_actual'] = $estado_olvido['paso_clave'];
							
							$this->Session->write('seguridad.olvido',$estado_olvido);

							$this->redirect('clave_cambiada');					
						} else {
							$this->set('error.fatal',_m('error.fatal'));
						}
					} else {
						$this->CambioClave->validationErrors['clave_nueva'] = $this->Idg->mensaje_error;	
					}
				} else {
					$this->set('error.fatal',_m('error.fatal'));
				}
			}
		}
		
		$this->data['CambioClave']['clave_nueva'] = '';
		$this->data['CambioClave']['clave_confirmacion'] = '';
		
		$estado_olvido['paso_actual'] = $estado_olvido['paso_clave'];
		$this->set('estado', $estado_olvido);
	}

	/**
	 * Acción que muestra mensaje de contraseña modificada
	**/
	function clave_cambiada(){
		if(!$this->Session->check('seguridad.olvido')){
			$this->redirect('/');
		}

		// Crea una variable con los datos de sesion
		$estado = $this->Session->read('seguridad.olvido');
		
		$this->Session->delete('seguridad.olvido');

		if(!isset($estado['clave_cambiada'])){
			$this->redirect('/');
		}

		$this->layout = 'externa';
		$this->set('estado',$estado);
	}
	
	/**
	 * Acción que da inicio al proceso de olvido de preguntas de desafío
	**/
	function olvido_preguntas(){
		$this->layout = "externa";
		
		$estado_olvido = $this->Session->read('seguridad.olvido');                                
                
                //No solicitar otp para perfil master ni para sus usuarios
                if($estado_olvido['datasource']['rol_juridico_id']==1 || $estado_olvido['datasource']['rol_juridico_id']>2){
                    $this->Session->write(
                                    'seguridad.validar_otp',
                                    array(
                                            'validado'=>true
                                    )
                            );
                }
                
		$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
		$estado_tdc = $this->Session->read('seguridad.validar_tdc');
                $validacion_otp = $this->Session->read('seguridad.validar_otp');
		$estado_qa = $this->Session->read('usuarios.asignar_preguntas');
		// Para usuarios jurídicos hay que hacer un manejo especial
		// en caso de que falle las preguntas de autenticación o tdc
		
		if($estado_olvido['datasource']['tipo_rif']){
			// En caso de que haya fallado en las preguntas de autenticación
			// debe descargar planilla e ir a la agencia
			
			if(isset($estado_autenticacion['validado']) && !$estado_autenticacion['validado']){
				
                                if(!isset($estado_olvido['necesita_planilla'])){
					$estado_olvido['paso_planilla'] = count($estado_olvido['pasos']);
					$estado_olvido['pasos'][]='Descarga Planilla';
					$estado_olvido['necesita_planilla']=true;
				}
				
				$estado_olvido['usuario_bloqueado']=true;
				$estado_olvido['usar_tdc']=false; // ya no tiene sentido preguntar coordenadas
				$this->Session->write('seguridad.olvido', $estado_olvido);
			}
			
			// En caso de que haya fallado en la verificación de tdc
			// debe descargar planilla e ir a la agencia
			
			if(isset($estado_olvido['usar_tdc']) && $estado_olvido['usar_tdc'] && isset($estado_tdc['validado']) && !$estado_tdc['validado'] && !isset($estado_olvido['necesita_planilla'])){
                            
                            $estado_olvido['paso_planilla'] = count($estado_olvido['pasos']);
                            $estado_olvido['pasos'][]='Descarga Planilla';
                            $estado_olvido['necesita_planilla']=true;
                            $this->Session->write('seguridad.olvido', $estado_olvido);
			}
		}
		
		if(!$this->Session->check('seguridad.olvido')){
			$this->redirect('/');
		} elseif(!isset($estado_olvido['cedula.validada'])){
			$this->redirect('/');
                } elseif($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado']){                    
                        $this->Session->write(
                                'seguridad.validar_otp',
                                array(
                                        'url_aceptar'=>'/seguridad/olvido_preguntas',
                                        'url_volver' => '/',
                                        'layout'=>'externa',
                                        'titulo'=>$estado_olvido['titulo'],
                                        'pasos'=> $estado_olvido['pasos'],
                                        'paso_actual'=>2
                                )
                        );

                        $this->redirect('genera_otp');
                                
		} elseif(!$this->Session->check('seguridad.validar_preguntas_autenticacion')){
	
			if(isset($estado_olvido['usar_tdc']) && $estado_olvido['usar_tdc']){
				// Si el usuario tiene tdc, hacer 3 preguntas de autenticación
				$cantidad_preguntas = 3;
			} else {
				// Si el usuario no tiene tdc, hacer 6 preguntas de autenticación
				$cantidad_preguntas = 6;
			}
			
			// Colocar data para el manejador de preguntas de seguridad
			$this->Session->write(
				'seguridad.validar_preguntas_autenticacion',
				array(
					'url_aceptar'=>'/seguridad/olvido_preguntas',
					'url_volver'=>'/',
					'layout'=>'externa',
					'titulo'=>$estado_olvido['titulo'],
					'cantidad_preguntas_autenticacion'=>$cantidad_preguntas,
					'pasos'=> $estado_olvido['pasos'],
					'paso_actual'=>$estado_olvido['paso_autenticacion'],
					'usuario'=>$estado_olvido['datasource'],
					'permite_continuar'=> !empty($estado_olvido['datasource']['tipo_rif'])
				)
			);

			// Redireccionar a las preguntas de seguridad
			$this->redirect('validar_preguntas_autenticacion');
		} elseif(
			!isset($estado_autenticacion['validado']) ||
			(
				!$estado_autenticacion['validado'] && 
				!$estado_autenticacion['permite_continuar']
			)
		){
			// El usuario no completó las preguntas de autenticación
			$this->redirect('validar_preguntas_autenticacion');				
		} elseif(
			isset($estado_olvido['usar_tdc']) && $estado_olvido['usar_tdc'] &&
			!$this->Session->check('seguridad.validar_tdc')
		){
			// Colocar data para el manejador de preguntas de seguridad

			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/seguridad/olvido_preguntas',
					'layout'=>'externa',
					'titulo'=>$estado_olvido['titulo'],					
					'pasos'=> $estado_olvido['pasos'],
					'paso_actual'=>$estado_olvido['paso_tdc'],
					'seccion_actual'=>'cambio.datos',
					'usuario'=>$estado_olvido['datasource'],
					'permite_continuar'=> !empty($estado_olvido['datasource']['tipo_rif'])
				)
			);

			// Redireccionar a las preguntas de seguridad
			$this->redirect('validar_tdc');
		} elseif(
			isset($estado_olvido['usar_tdc']) && $estado_olvido['usar_tdc'] &&
			(
				!isset($estado_tdc['validado']) ||
				(
					!$estado_tdc['validado'] &&
					!$estado_tdc['permite_continuar']
				)
			)
		){
			// El usuario no completó la verificación de tdc
			$this->redirect('validar_tdc');
		} elseif(!$this->Session->check('usuarios.asignar_preguntas')){
		
			// Colocar data para el manejador asignación de qa
			
			$this->Session->write(
				'usuarios.asignar_preguntas',
				array(
					'url_aceptar'=>'/seguridad/olvido_preguntas',
					'layout'=>'externa',
					'titulo'=>$estado_olvido['titulo'],
					'pasos'=>$estado_olvido['pasos'],
					'paso_1'=>$estado_olvido['paso_qa'],
					'paso_2'=>$estado_olvido['paso_qa'],
					'paso_3'=>$estado_olvido['paso_qa'],
					'usuario'=>$estado_olvido['datasource']
				)
			);
			
			// redirigir a asignación de qa
			
			$this->redirect('/usuarios/asignar_preguntas');
		} else if(
			!isset($estado_qa['asignadas']) ||
			!$estado_qa['asignadas']
		){
			// redirigir a asignación de qa
			
			$this->redirect('/usuarios/asignar_preguntas');			
		} else if(isset($estado_olvido['necesita_planilla'])){
			// En caso de que el usuario no se haya bloqueado,
			// cambiar estatus de usuario a pendiente
			// ya que tiene que ir a la agencia
			
			if(!isset($estado_olvido['usuario_bloqueado'])){
				$this->UsuarioCanal->cambioEstatus(
					'registro',
					'P',
					$estado_olvido['datasource']['id']
				);
			}
		
			// Proceso finalizado
			// Mostrar botón de descarga de planilla
			$estado_olvido['permitir_descarga_planilla_qa'] = true;
			$this->Session->write('seguridad.olvido',$estado_olvido);
			$this->set(compact('estado_olvido'));
                        //Si estaba bloqueado por inactividad lo coloca de nuevo activo
                        if($estado_olvido['datasource']['estatus_acceso']=='IN'){
                            $this->UsuarioCanal->cambioEstatus('acceso', 'A', $estado_olvido['datasource']['id']);
                        }
                        
		} else {
			// Proceso finalizado
			// Redirigir a login
		
			$this->redirect('/');
		}
	}
	
	
	/**
	 * Acción para dercargar la planilla de preguntas de desafío
	 * 
	 */
	function descargar_planilla_qa(){
		$this->layout = "nada";
		
		$estado = $this->Session->read('seguridad.olvido');
		
		if(!$estado || !isset($estado['permitir_descarga_planilla_qa'])) $this->redirect('/');
		
		$this->set(compact('estado'));
	}

	/**
	 * Acción que da inicio al proceso de olvido de imagen de seguridad
	 */
	function olvido_sello(){
		$this->layout = "externa";
                
                //No solicitar preguntas de autenticacion para juridico
		if( $this->Session->check('login.juridico') ){ // PARA JURIDICO
                    $this->Session->write('seguridad.validar_preguntas_autenticacion', array('validado'=>true));
		}
                
                $estado_olvido = $this->Session->read('seguridad.olvido');
                
                //No solicitar otp para perfil master ni para sus usuarios
                if($estado_olvido['datasource']['rol_juridico_id']==1 || $estado_olvido['datasource']['rol_juridico_id']>2){
                    $this->Session->write(
                                    'seguridad.validar_otp',
                                    array(
                                            'validado'=>true
                                    )
                            );
                }
		
		$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
		$estado_desafio = $this->Session->read('seguridad.validar_preguntas_desafio');
                $validacion_otp = $this->Session->read('seguridad.validar_otp');
		$estado_config_sello = $this->Session->read('usuarios.config_sello');		

		// Validar que el usuario paso por las dos primeras pantallas de olvido
		if(!$this->Session->check('seguridad.olvido')){
			// Redireccionar al formulario inicial de olvido
			$this->redirect('olvido_tipo');
                        
                } elseif($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado']){                    
                        $this->Session->write(
                                'seguridad.validar_otp',
                                array(
                                        'url_aceptar'=>'/seguridad/olvido_sello',
                                        'url_volver' => '/',
                                        'layout'=>'externa',
                                        'titulo'=>$estado_olvido['titulo'],
                                        'pasos'=> $estado_olvido['pasos'],
                                        'paso_actual'=>$estado_olvido['paso_codigo']
                                )
                        );

                        $this->redirect('genera_otp');
                        
                } elseif(!$this->Session->check('seguridad.validar_preguntas_autenticacion')){
			
			// Colocar data para el manejador de preguntas de seguridad
			$this->Session->write(
				'seguridad.validar_preguntas_autenticacion',
				array(
					'url_aceptar'=>'/seguridad/olvido_sello',
					'url_volver'=>'/',
					'layout'=>'externa',
					'titulo'=>'Olvido de Sello Personal',
					'cantidad_preguntas_autenticacion'=>2,
					'pasos'=> $estado_olvido['pasos'],
					'paso_actual'=>$estado_olvido['paso_autenticacion']
				)
			);

			// Redireccionar a las preguntas de seguridad
			$this->redirect('validar_preguntas_autenticacion');		
			
		} elseif(!isset($estado_autenticacion['validado']) || !$estado_autenticacion['validado']){
			
			// El usuario no completó las preguntas de autenticación
			$this->redirect('validar_preguntas_autenticacion');				
			
		} elseif(!$this->Session->check('seguridad.validar_preguntas_desafio')){
			
			// Colocar data para el manejador de preguntas de seguridad
			$this->Session->write(
				'seguridad.validar_preguntas_desafio',
				array(
					'url_aceptar'=>'/seguridad/olvido_sello',
					'url_volver'=>'/',
					'layout'=>'externa',
					'titulo'=>'Olvido de Sello Personal',
					'cantidad_preguntas_desafio'=>2,
					'pasos'=> $estado_olvido['pasos'],
					'paso_actual'=>$estado_olvido['paso_desafio'],
					'usuario'=>$estado_olvido['datasource']
				)
			);

			// Redireccionar a las preguntas de seguridad
			$this->redirect('validar_preguntas_desafio');
			
		} elseif(!isset($estado_desafio['validado']) || !$estado_desafio['validado']){
			
				// El usuario no completó las preguntas de autenticación
				$this->redirect('validar_preguntas_desafio');
				
		}	elseif(!$this->Session->check('usuarios.config_sello')){
			
			$this->Session->write(
				'usuarios.config_sello',
				array(
					'url_aceptar'=>'/seguridad/olvido_sello',
					'url_volver'=>'/',
					'layout'=>'externa',
					'titulo'=>'Olvido de Sello Personal',
					'pasos'=> $estado_olvido['pasos'],
					'paso_actual'=>$estado_olvido['paso_sello']
				)
			);
			
			$this->Session->write(
				'usuarios.registro',
				array('Usuario'=>$estado_olvido['datasource'])
			);

			$this->redirect('/usuarios/config_sello');
		} elseif(isset($estado_config_sello['exito'])){

			$this->Session->delete('seguridad.olvido');
			$this->Session->delete('seguridad.validar_preguntas_autenticacion');
			$this->Session->delete('seguridad.validar_preguntas_desafio');
			$this->Session->delete('usuarios.config_sello');

			$this->redirect('/');
		} else {
			$this->redirect('/usuarios/config_sello');
		}		
	}

	/**
	 * Acción que da inicio al proceso de actualización de datos de contacto
	**/
	function cambiar_datos(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(16)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		if(
			$validacion_tdc == null ||
			!isset($validacion_tdc['validado']) ||
			!$validacion_tdc['validado']
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/seguridad/cambiar_datos',
					'layout'=>'interna',
					'titulo'=>'Cambio Datos Contacto',
					'pasos'=> array(
						'Tarjeta de Coordenadas',
						'Solicitud de Información',
						'Confirmación',
						'Resultado de la Operación'
					),
					'paso_actual'=>0,
					'seccion_actual'=>'cambio.datos'
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}

		if(!empty($this->data['Usuario'])){
			$this->Usuario->set($this->data['Usuario']);

			if($this->Usuario->validates(array('fieldList'=>array('emailPri','emailSec','nroCelular','codigoCelular')))){
				$estado = $this->data;
				$estado['asignado']=true;
				$this->Session->write('seguridad.cambiar_datos',$estado);
				$this->redirect('cambiar_datos_confirmacion');
			}
		}
		else{
			if($this->Session->check('seguridad.cambiar_datos')){
				$this->data = $this->Session->read('seguridad.cambiar_datos');
			}
			else{
				$usuario = $this->Session->read('usuarios.datos');
				$this->data['Usuario']['emailPri']= desencriptar($usuario['email_principal']);
				if($usuario['email_secundario']!='') $this->data['Usuario']['emailSec']= desencriptar($usuario['email_secundario']) ;
				$this->data['Usuario']['codigoCelular']= substr(desencriptar($usuario['celular']),0,4);
				$this->data['Usuario']['nroCelular']= substr(desencriptar($usuario['celular']),-7);
			}
		}

		$this->layout = 'interna';

		$this->set('seccion_actual','cambio.datos');
	}

	/**
	 * Acción para confirmar la actualización de los datos de contacto
	**/
	function cambiar_datos_confirmacion(){
		$this->layout = 'interna';

		$this->set('seccion_actual','cambio.datos');	

		$estado = $this->Session->read('seguridad.cambiar_datos');

		if(isset($this->data['Usuario'])){
			$estado['confirmado'] = true;
			$this->Session->write('seguridad.cambiar_datos',$estado);
			$this->redirect('cambiar_datos_resultado');
		} else if(isset($estado) && isset($estado['asignado']) && $estado['asignado']){
			$this->data = $estado;
		}
	}

	/**
	 * Acción para mostrar el resultado de la actualización de los datos de contacto
	**/
	function cambiar_datos_resultado(){
		$estado = $this->Session->read('seguridad.cambiar_datos');

		if(isset($estado) && isset($estado['confirmado']) && $estado['confirmado']){
			$usuario = $this->Session->read('usuarios.datos');
			$datos = $this->Session->read('seguridad.cambiar_datos');
			$datos = array_map('encriptar', $datos['Usuario']);
			$this->Session->delete('seguridad.cambiar_datos');
			
			$this->layout = 'interna';

			$numeroCelular = desencriptar($datos['codigoCelular']).desencriptar($datos['nroCelular']);
			
			$celularEncriptado = encriptar($numeroCelular);

			$this->set('seccion_actual','cambio.datos');
			
			if( $this->Usuario->save(
			   array(
				   'email_principal' => $datos['emailPri'],
				   'email_secundario' => $datos['emailSec'],
				   'celular' => $celularEncriptado,
				   'id' => $usuario['id']
			   )
			)){
			
				// Enviar correos con notificación en caso de ser necesario
				
				if(
					$usuario['email_principal'] != $datos['emailPri'] &&
					$usuario['celular'] != $celularEncriptado
				){
					$tipo_cambio = 'ambos';
					$canal_sms = '39';
				} elseif($usuario['email_principal'] != $datos['emailPri']){
					$tipo_cambio = 'email';
					$canal_sms = '37';
				} elseif($usuario['celular'] != $celularEncriptado){
					$tipo_cambio = 'celular';
					$canal_sms = '38';
				}
				
				if(isset($tipo_cambio)){
					$nombre_destino = desencriptar($usuario['nombre']);
					$this->set(compact('nombre_destino', 'tipo_cambio'));
					
					// Enviar correo a dirección nueva

					$this->Email->smtpOptions = Configure::read('email.smtp');
					$this->Email->delivery	= 'smtp';
					$this->Email->from		= Configure::read('email.from');
					$this->Email->to		= desencriptar($datos['emailPri']);
					$this->Email->subject	= 'Cambio de datos de contacto';
					$this->Email->sendAs	= 'html';
					$this->Email->template	= 'cambio_datos_contacto';
					$this->Email->send();
					
					$this->Sms->enviar(
						array(
							'numcel'=>desencriptar($usuario['celular']),
							'canal1'=>$canal_sms
						)
					);				
					
					if($tipo_cambio=='ambos' || $tipo_cambio=='email'){
						$this->Email->reset();
						$this->Email->smtpOptions = Configure::read('email.smtp');
						$this->Email->delivery	= 'smtp';
						$this->Email->from		= Configure::read('email.from');
						$this->Email->to		= desencriptar($usuario['email_principal']);
						$this->Email->subject	= 'Cambio de datos de contacto';
						$this->Email->sendAs	= 'html';
						$this->Email->template	= 'cambio_datos_contacto';
						$this->Email->send();
					}
					
					if($tipo_cambio=='ambos' || $tipo_cambio=='celular'){
						$this->Sms->enviar(
							array(
								'numcel'=>$numeroCelular,
								'canal1'=>$canal_sms
							)
						);			
					}
				}			
			
				$this->data['Error'] = FALSE;
				$usuario['email_principal'] = $datos['emailPri'];
				$usuario['email_secundario'] = $datos['emailSec'];
				$usuario['codigoCelular'] = $datos['codigoCelular'];
				$usuario['celular'] = $celularEncriptado;
				$this->Session->write('usuarios.datos', $usuario);

				/** Para identificar si es un usuario juridico **/
				if($usuario['tipo_rif']){
					$tipo_documento = $usuario['tipo_rif'];
					$identificacion = desencriptar($usuario['rif']);
				}
				else{
					$tipo_documento = $usuario['tipo_cedula'];
					$identificacion = desencriptar($usuario['cedula']);
				}
				/** Para identificar si es un usuario juridico **/
				
				$this->Activo->invoca(
					'NIBPF3030',
					array(
						'req'=>array(
							'canal'=>Configure::read('canal.ib'),
							'INTNACEM'=>$tipo_documento,
							'INTIDEEM'=>str_pad($identificacion,12,'0',STR_PAD_LEFT),
							'INTCOREM'=>desencriptar($datos['emailPri'])
						)
					)
				);
				
				$this->Activo->invoca(
					'NIBPF3040',
					array(
						'req'=>array(
							'canal'=>Configure::read('canal.ib'),
							'INTNACTC'=>$tipo_documento,
							'INTIDETC'=>str_pad($identificacion,12,'0',STR_PAD_LEFT),
							'INTNTCTC'=>$numeroCelular
						)
					)
				);
				
				//Registra en Bitácora
				$this->Bitacora->registraBitacora(
					$usuario['id'],
					16,
					NULL,
					NULL,
					NULL,
					NULL,
					NULL,
					0
				);
			} else {
				$this->data['Error'] = TRUE;
				$this->log('Error al tratar de actualizar datos del usuario. '+ $usuario['id'], LOG_ERR);
				$this->log($this->Usuario->getDataSource()->error);
				
				//Registra en Bitácora
				$this->Bitacora->registraBitacora(
					$usuario['id'],
					16,
					NULL,
					NULL,
					NULL,
					NULL,
					NULL,
					1
				);
				
			}
			
		} else {
			$this->redirect('/consultas/integral');
		}
	}
        
        /**
	 * Acción para generar un OTP
	**/
	function genera_otp(){		
                
                $estado = $this->Session->read('seguridad.validar_otp');
                
		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
		if( isset($estado['seccion_actual']) ){
			$this->set('seccion_actual', $estado['seccion_actual']);
		}
                
                $estado['validado'] = false;
		$this->Session->write('seguridad.validar_otp', $estado);
                                        
		$this->set('estado',$estado);
		
                //Validar desde que modulo se llama al servicio
                if($this->Session->check('usuarios.datos')){
                    $usuario = $this->Session->read('usuarios.datos');
                }elseif($this->Session->check('seguridad.olvido')){
                    $olvido = $this->Session->read('seguridad.olvido');
                    $usuario = $olvido['datasource'];
                }elseif($this->Session->check('usuarios.registro')){
                    $registro = $this->Session->read('usuarios.registro');
                    $usuario = $registro['Usuario'];                    
                }
		
                if(isset($usuario['tipoCedulaRif'])){
                        $tipo_cedula = $usuario['tipoCedulaRif'];
                        $cedula = $usuario['nroCedulaRif'];
                        $rif = '';
                } elseif(empty($usuario['tipo_rif'])){
			$tipo_cedula = $usuario['tipo_cedula'];
                        $cedula = desencriptar($usuario['cedula']);
                        $rif = '';
		} else {
                        $tipo_cedula = $usuario['tipo_rif'];
                        $cedula = desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
                        $rif = desencriptar($usuario['rif']);
		}
                  
                $res = $this->Activo->invoca(
                    'creaEnviaOTP',
                    array(
                            'req'=>array(
                                    'canal'=>Configure::read('canal.ib'),
                                    'tipoCedula'=>$tipo_cedula,
                                    'cedula'=>$cedula,
                                    'rif'=>$rif
                            )
                    )
                ); 
				
                if($res){
                        
                        switch($res['AutOTPReturn']['error']['ERNU01']){
                                case '1':                                        
                                case '2':
                                case '3':
                                case '4':
                                case '5':
                                case '6':
                                case '7':
                                    $this->Bitacora->registraBitacora($usuario['id'],100041, NULL, NULL, NULL, NULL, $res['AutOTPReturn']['error']['ERDS01'], 1, NULL);
                                    $this->redirect('error_otp');
                                default:
                                    $this->Bitacora->registraBitacora($usuario['id'],100041, NULL, NULL, NULL, NULL, 'Genera OTP', 0, NULL);
                        }
                                                
                }else{
                        $this->Bitacora->registraBitacora($usuario['id'],100041);
                        $this->redirect('error_otp');
                }			
						
	}
        
        
        /**
	 * Acción para validar un OTP
	**/
	function valida_otp(){		
                
                $estado = $this->Session->read('seguridad.validar_otp');
                
		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
		if( isset($estado['seccion_actual']) ){
			$this->set('seccion_actual', $estado['seccion_actual']);
		}

		$this->set('estado',$estado);
		                               
                //Validar desde que modulo se llama al servicio
                if($this->Session->check('usuarios.datos')){
                    $usuario = $this->Session->read('usuarios.datos');
                }elseif($this->Session->check('seguridad.olvido')){
                    $olvido = $this->Session->read('seguridad.olvido');
                    $usuario = $olvido['datasource'];
                }elseif($this->Session->check('usuarios.registro')){
                    $registro = $this->Session->read('usuarios.registro');
                    $usuario = $registro['Usuario'];                    
                }
		
                if(isset($usuario['tipoCedulaRif'])){
                        $tipo_cedula = $usuario['tipoCedulaRif'];
                        $cedula = $usuario['nroCedulaRif'];
                        $rif = '';
                } elseif(empty($usuario['tipo_rif'])){
			$tipo_cedula = $usuario['tipo_cedula'];
                        $cedula = desencriptar($usuario['cedula']);
                        $rif = '';
		} else {
                        $tipo_cedula = $usuario['tipo_rif'];
                        $cedula = desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
                        $rif = desencriptar($usuario['rif']);
		}                
                
                if(!empty($this->data['Otp']) && !$estado['validado']){
                    
                    $res = $this->Activo->invoca(
                                    'validaOTP',
                                    array(
                                            'req'=>array(
                                                    'canal'=>Configure::read('canal.ib'),
                                                    'tipoCedula'=>$tipo_cedula,
                                                    'cedula'=>$cedula,
                                                    'otpCod'=>$this->data['Otp']['nroOtp']
                                            )
                                    ) 
                            ); 

                    if($res){
                            
                            switch($res['AutOTPReturn']['error']['ERNU01']){
                                    case '1':                                        
                                    case '2':
                                    case '3':
                                    case '6':
                                        $estado['validado'] = false;
					$this->Session->write('seguridad.validar_otp', $estado);
                                        $this->Bitacora->registraBitacora($usuario['id'],100054, NULL, NULL, NULL, NULL, $res['AutOTPReturn']['error']['ERDS01'], 0, NULL);
                                        $this->Session->write('seguridad.resultado_otp',array('mensaje'=>_m('error.validar.otp'),'error'=>$res['AutOTPReturn']['error']['ERNU01']));

                                        $this->redirect('error_validar_otp');
                                    case '4':
                                        $estado['validado'] = false;
					$this->Session->write('seguridad.validar_otp', $estado);
                                        $this->Session->write('seguridad.resultado_otp',array('mensaje'=>_m('error.otp.vencida'),'error'=>$res['AutOTPReturn']['error']['ERNU01']));

                                        $this->redirect('error_validar_otp');
                                    default:
                                        $estado['validado'] = true;
					$this->Session->write('seguridad.validar_otp', $estado);
                                        $this->redirect($estado['url_aceptar']);
                            }                            


                    }else{                                
                            $this->Bitacora->registraBitacora($usuario['id'],100041);
                            $this->redirect('error_validar_otp');
                    }                        

                }
						
	}
        
        /**
	 * Acción para mostrar mensaje de error al generar un OTP
	 * 
	 */
	function error_otp(){				
		
		$estado = $this->Session->read('seguridad.validar_otp');
		
		$this->layout = $estado['layout'];
                
                // si es interna marca el modulo en el menu
                if( isset($estado['seccion_actual']) ){
                    $this->set('seccion_actual', $estado['seccion_actual']);
                }
                
                $this->set(compact('estado'));			
	}
        
        /**
	 * Acción para mostrar mensaje de error al validar un OTP
	 * 
	 */
	function error_validar_otp(){				
		
		$estado = $this->Session->read('seguridad.validar_otp');
		
                $resultado = $this->Session->read('seguridad.resultado_otp');
                
		$this->layout = $estado['layout'];

		// si es interna marca el modulo en el menu
                if( isset($estado['seccion_actual']) ){
                    $this->set('seccion_actual', $estado['seccion_actual']);
                }
		
                $this->set(compact('resultado','estado'));
                
	}
        
        /**
	 * Acción que muestra mensaje de advertencia de notificación de Ausencia / Viaje 
	**/
	function advertencia_notificacion_viaje(){				
            
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(array(100070))) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */    
            
            $this->layout = 'interna';

            $this->set('seccion_actual', 'notificacion.viaje');                             
                                
	}
        
        /**
	 * Acción que muestra la Ubicacion de Seguridad
	**/
	function notificacion_viaje(){		

            /* Para validar el acceso al modulo */
            if(!validarTransaccion(array(100070))) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */    
                                      
            $this->layout = 'interna';		
            $this->set('seccion_actual', 'notificacion.viaje');            
            $validacion_tdc = $this->Session->read('seguridad.validar_tdc');
            $usuario = $this->Session->read('usuarios.datos');
            $paises = $this->Pais->listar_paises_nombre();                        
            
            //Tarjeta de Coordenadas
            if(
                ($validacion_tdc == null ||
                    !isset($validacion_tdc['validado']) ||
                    !$validacion_tdc['validado']
                )
            ){
                $this->Session->write(
                    'seguridad.validar_tdc',
                    array(
                        'url_aceptar'=>'/seguridad/notificacion_viaje',
                        'layout'=>'interna',
                        'titulo'=>'Notificaci&oacute;n de Viaje/Ausencia',
                        'pasos'=> array(),
                        'paso_actual'=>0,
                        'seccion_actual'=>'notificacion.viaje'
                    )
                );

                $this->redirect('/seguridad/validar_tdc');
            }
            
            $notificacionesList = $this->NotificacionAusencia->buscarNotificacionesViajeByUsuarioId($usuario['id']);                        
            
            $this->data['NotificacionAusencia']['pais'] = "Venezuela";
            
            if(count($notificacionesList)){                                
                foreach($notificacionesList as $i){                                            
                    if($this->Activo->check_in_range($i['NotificacionAusencia']['fecha_inicio'],$i['NotificacionAusencia']['fecha_fin'],'today')){
                        $this->data['NotificacionAusencia']['pais'] = $paises[$i['NotificacionAusencia']['codigo_pais']];
                        break;
                    }                                                                        
                }
            }                             
            
            $this->Session->write('seguridad.notificacion_viaje', $this->data);                        
                        
	}
        
        /**
	 * Acción que se encarga de mostrar el listado de notificaciones de viaje que posee el cliente
	 * @param string $volver Variable que indica si la página se carga
	 * debido a un regreso
	 */
	function listado_notificacion_viaje($volver=null){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(array(100070))) $this->redirect('/consultas/integral');		

                /* Leer los Datos de Session del Usuario */
                $usuario = $this->Session->read('usuarios.datos');                                
                $paises = $this->Pais->listar_paises_nombre();
                $tiposIntrumento = $this->NotificacionAusencia->buscarTiposInstrumentos();
                
                //Tarjeta de Coordenadas
                $validacion_tdc = $this->Session->read('seguridad.validar_tdc');
                if(
                    ($validacion_tdc == null ||
                        !isset($validacion_tdc['validado']) ||
                        !$validacion_tdc['validado']
                    )
                ){
                    $this->Session->write(
                        'seguridad.validar_tdc',
                        array(
                            'url_aceptar'=>'/seguridad/notificacion_viaje',
                            'layout'=>'interna',
                            'titulo'=>'Notificaci&oacute;n de Viaje/Ausencia',
                            'pasos'=> array(),
                            'paso_actual'=>0,
                            'seccion_actual'=>'notificacion.viaje'
                        )
                    );

                    $this->redirect('/seguridad/validar_tdc');
                }
                
                if((!empty($this->data['NotificacionAusencia']) && !isset($this->data['NotificacionAusencia']['paginando'])) || !empty($this->data)){
			
                        $this->NotificacionAusencia->set($this->data);
                        
                        $paises = $this->Pais->listar_paises_siglas();
                        
			if($this->NotificacionAusencia->validates(array('fieldList'=>array('accion')))){
                                                            
				$this->Session->write('seguridad.notificacion_viaje',$this->data);

				switch($this->data['accion']){
					case 'crear':
                                            $this->Session->delete('seguridad.notificacion_crear');
                                            $this->redirect('notificacion_crear');
                                            break;
					
					case 'editar':
                                            $notificacion = $this->NotificacionAusencia->buscarNotificacionesViaje($this->data['NotificacionAusencia']['id']);
                                            $notificacion['NotificacionAusencia']['siglas'] = $paises[$notificacion['NotificacionAusencia']['codigo_pais']];
                                            $notificacion['NotificacionAusencia']['fecha_inicio'] = $notificacion['NotificacionAusencia']['fecha_inicio'];
                                            $notificacion['NotificacionAusencia']['fecha_fin'] = $notificacion['NotificacionAusencia']['fecha_fin'];
                                            $notificacion['NotificacionAusencia']['instrumento'] = $notificacion['NotificacionAusencia']['instrumento'];
                                            $notificacion['NotificacionAusencia']['accion'] ='Modificación';
                                            $this->Session->write('notificacion.notificacion_crear',$notificacion);
                                            $this->redirect('notificacion_crear/e');
                                            break;
						
					case 'eliminar':
                                            $notificacion = $this->NotificacionAusencia->buscarNotificacionesViaje($this->data['NotificacionAusencia']['id']);
                                            $notificacion['NotificacionAusencia']['siglas'] = $paises[$notificacion['NotificacionAusencia']['codigo_pais']];
                                            $notificacion['NotificacionAusencia']['fecha_inicio'] = $notificacion['NotificacionAusencia']['fecha_inicio'];
                                            $notificacion['NotificacionAusencia']['fecha_fin'] = $notificacion['NotificacionAusencia']['fecha_fin'];
                                            $notificacion['NotificacionAusencia']['instrumento'] = $notificacion['NotificacionAusencia']['instrumento'];
                                            $notificacion['NotificacionAusencia']['accion'] ='Eliminación';						

                                            $this->Session->write('notificacion.notificacion_eliminar',$notificacion);
                                            $this->redirect('notificacion_eliminar_confirmacion');
                                            break;
				}
			}
		} else if(isset($volver) && $this->Session->check('notificacion.listado_notificacion_viaje')){
                    $this->data = $this->Session->read('notificacion.listado_notificacion_viaje');
		}
		
		$conditions = array(
                        'NotificacionAusencia.usuario_id'=>$usuario['id'],
                        'NotificacionAusencia.estatus'=>1
                    );				
                		                
		$total_registros = $this->NotificacionAusencia->find(
                    'count',
                    array( 'conditions'=>$conditions )
		);	

		$offset = 0;

		if(
                    isset($this->data['NotificacionAusencia']['offset']) &&
                    preg_match('/^\d+$/',$this->data['NotificacionAusencia']['offset'])
		){
                    $offset = intval($this->data['NotificacionAusencia']['offset']);			
		}
		
		$listado = $this->NotificacionAusencia->find(
                    'all',
                    array(
                        'conditions'=>$conditions,
                        'order'=>'NotificacionAusencia.id desc',
                        'offset'=>$offset,
                        'limit'=>Configure::read('Tamano.paginacion')
                    )
		);						                
                        
                $this->Session->write('notificacion.listado_notificacion_viaje',$listado);
                                
		$this->layout = 'interna';
		$this->set('seccion_actual','notificacion.viaje');
		$this->set(compact('listado','total_registros','paises','tiposIntrumento'));
	}
        
        /**
	 * Acción para crear o modificar una notificacion
	 * @param string Indica si se está regresando de la confirmación
	 */
	function notificacion_crear($volver=null){
                
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(array(100070))) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */

            $validacion_tdc = $this->Session->read('seguridad.validar_tdc');
            $usuario = $this->Session->read('usuarios.datos');                              
                
                //Tarjeta de Coordenadas
            if(
                ($validacion_tdc == null ||
                    !isset($validacion_tdc['validado']) ||
                    !$validacion_tdc['validado']
                )
            ){
                $this->Session->write(
                    'seguridad.validar_tdc',
                    array(
                        'url_aceptar'=>'/seguridad/notificacion_viaje',
                        'layout'=>'interna',
                        'titulo'=>'Notificaci&oacute;n de Viaje/Ausencia',
                        'pasos'=> array(),
                        'paso_actual'=>0,
                        'seccion_actual'=>'notificacion.viaje'
                    )
                );

                $this->redirect('/seguridad/validar_tdc');
            }
                        
            $instrumentos = array();
            
            $instrumentos = array(
                0  => "Cuentas",
                1 => "Tarjetas de Crédito",
                2 => "Ambos"         
            ); 
                    
            $paises = $this->Pais->listar_paises();            

            if(empty($this->data['NotificacionAusencia'])){                    
                if(isset($volver) && $this->Session->check('notificacion.notificacion_crear')){
                    $this->data = $this->Session->read('notificacion.notificacion_crear');
                }
            } else {
                    $this->NotificacionAusencia->set($this->data);

                    if($this->NotificacionAusencia->validates(
                        array(
                            'fieldList'=>array(
                                'codigo_pais',
                                'fecha_inicio',
                                'fecha_fin'                               
                            )
                        )
                    )){                                                                                               
                        $this->Session->write('notificacion.notificacion_crear', $this->data);                                                        
                        $this->redirect('notificacion_crear_confirmacion');                    

                    }
            }                                

            $this->layout = 'interna';
            $this->set('seccion_actual','notificacion.viaje');
            $this->set('paises', $paises);
            $this->set('instrumentos', $instrumentos);               
        }
        
        /**
	 * Acción de confirmación de creación/modificación de la notificacion de viaje 
	 */
	function notificacion_crear_confirmacion(){
		                
            $usuario = $this->Session->read('usuarios.datos');                
            
            $tiposIntrumento = $this->NotificacionAusencia->buscarTiposInstrumentos();
            $paises = $this->Pais->listar_paises();
            
            if($this->Session->check('notificacion.notificacion_crear')){

                $this->layout = 'interna';
                
                $usuario = $this->Session->read('usuarios.datos');                
                $this->set('seccion_actual','notificacion.viaje');			
                $this->set('tiposIntrumento', $tiposIntrumento);
                $this->set('paises', $paises);                
                
                $this->data = $this->Session->read('notificacion.notificacion_crear');                

            } else {
                $this->redirect('listado_notificacion_viaje');
            }                                 
	} 
        
        /**
	 * Acción para el resultado de la creación/modificación de la notificacion de viaje
	 */
	function notificacion_crear_resultado(){
                	
		$usuario = $this->Session->read('usuarios.datos');
                $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];		                
                $tiposIntrumento = $this->NotificacionAusencia->buscarTiposInstrumentos();
                $codigosPaises = $this->Pais->listar_paises_codigo();
                $paises = $this->Pais->listar_paises_nombre();
                
                if($this->Session->check('notificacion.notificacion_crear')){	

                    $this->layout = 'interna';						

                    $this->set('seccion_actual','notificacion.viaje');				
                    $this->data = $this->Session->read('notificacion.notificacion_crear');                        			
                    $this->Session->delete('notificacion.notificacion_crear');
                    
                    $this->data['NotificacionAusencia']['ip'] = $ip;
                    $this->data['NotificacionAusencia']['usuario_id'] = $usuario['id'];
                    $this->data['NotificacionAusencia']['fecha_inicio'] = $this->data['NotificacionAusencia']['fecha_inicio']['year']."-".$this->data['NotificacionAusencia']['fecha_inicio']['month']."-".$this->data['NotificacionAusencia']['fecha_inicio']['day'];
                    $this->data['NotificacionAusencia']['fecha_fin'] = $this->data['NotificacionAusencia']['fecha_fin']['year']."-".$this->data['NotificacionAusencia']['fecha_fin']['month']."-".$this->data['NotificacionAusencia']['fecha_fin']['day'];
                    $this->data['NotificacionAusencia']['codigo_pais'] = $codigosPaises[$this->data['NotificacionAusencia']['siglas']];
                    $this->data['NotificacionAusencia']['fecha_hora'] = date('Y-m-d H:i:s');
                    $this->data['NotificacionAusencia']['estatus'] = '1';
                    $this->data['NotificacionAusencia']['instrumento'] = '0';
                    unset($this->data['NotificacionAusencia']['siglas']);                                     
                    
                    $flag_nuevo = !isset($this->NotificacionAusencia->id) || $this->NotificacionAusencia->id=='';

                    $this->data['Error'] = !$this->NotificacionAusencia->save($this->data,false);
                    
                    if(!$this->data['Error']){

                            if($flag_nuevo) {
                                $this->data['NotificacionAusencia']['accion']='Afiliación';                                					
                            } else {
                                $this->data['NotificacionAusencia']['accion']='Modificación';                                
                            }

                            // Email Options
                            $this->Email->smtpOptions = Configure::read('email.smtp');
                            $this->Email->delivery = 'smtp';
                            $this->Email->from = Configure::read('email.from');
                            $this->Email->to = desencriptar($usuario['email_principal']);
                            $this->Email->subject = 'Proceso de Notificacion/Modificacion';
                            $this->Email->sendAs = 'html';

                            // Informacion del correo
                            $this->Email->template = 'notificacion_viaje';                            
                            $tipo_accion = $this->data['NotificacionAusencia']['accion'] . ' Notificacion';							
                            $tipo_afiliacion = 'Notificacion de Seguridad';
                            $instrumento = $tiposIntrumento[$this->data['NotificacionAusencia']['instrumento']];
                            $this->set(compact('usuario','tipo_afiliacion','tipo_accion','instrumento'));

                            // Envio del correo
                            $this->Email->send();
                           
                            // Registra en bitacora
                            $this->Bitacora->registraBitacora(
                                $usuario['id'], 
                                100070,
                                NULL,
                                NULL, 
                                NULL,
                                NULL,
                                'Notificacion de Viaje '.$this->data['NotificacionAusencia']['fecha_inicio']."-".$this->data['NotificacionAusencia']['fecha_fin'],
                                0,
                                NULL,
                                $this->data['NotificacionAusencia']['codigo_pais']
                            );
                    }else{
                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                100070,
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                'Notificacion de Viaje',
                                1,
                                NULL,
                                NULL
                            );
                    }                            			                                 
                    
                    $this->set('tiposIntrumento', $tiposIntrumento);
                    $this->set('paises', $paises);
		} else {
                    $this->redirect('listado_notificacion_viaje');
		}
	}
        
        /**
	 * Acción para confirmar la eliminación de una notificacion de viaje 
	 */
	function notificacion_eliminar_confirmacion(){
            		
		$usuario = $this->Session->read('usuarios.datos');		

		if($this->Session->check('notificacion.notificacion_eliminar')){
			$this->data = $this->Session->read('notificacion.notificacion_eliminar');
			$this->layout = 'interna';		
			$this->set('seccion_actual','notificacion.viaje');
			$usuario = $this->Session->read('usuarios.datos');
			                        
			$this->set('tiposIntrumento', $this->NotificacionAusencia->buscarTiposInstrumentos());
			$this->set('paises', $this->Pais->listar_paises_nombre());                        
		} else {
			$this->redirect('listado_notificacion_viaje');
		}
	}

	/**
	 * Acción para el resultado de eliminar una notificacion de viaje 
	 */
	function notificacion_eliminar_resultado(){
            		
		$usuario = $this->Session->read('usuarios.datos');
		
		if($this->Session->check('notificacion.notificacion_eliminar')){
                    
                    $this->data = $this->Session->read('notificacion.notificacion_eliminar');			
                    $this->layout = 'interna';
                    $this->Session->delete('notificacion.notificacion_eliminar');
                    
                    $this->data['NotificacionAusencia']['estatus'] = 0;
                    unset($this->data['NotificacionAusencia']['siglas']); 
                    unset($this->data['NotificacionAusencia']['accion']);
                                        
                    $this->data['Error'] = !$this->NotificacionAusencia->save($this->data,false);

                    if(!$this->data['Error']){                            			

                            $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            100070,
                            NULL,
                            NULL, 
                            NULL,
                            NULL,
                            'Elimina Notificacion de Viaje '.$this->data['NotificacionAusencia']['fecha_inicio']."-".$this->data['NotificacionAusencia']['fecha_fin'],
                            0,
                            NULL,
                            $this->data['NotificacionAusencia']['codigo_pais']
                            
                    );
                    }else{
                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                100070,
                                NULL,
                                NULL, 
                                NULL,
                                NULL,
                                'Elimina Notificacion de Viaje',
                                1,
                                NULL,
                                NULL
                            );
                    }
			                        
			$this->set('seccion_actual','notificacion.viaje');
			$this->set('tiposIntrumento', $this->NotificacionAusencia->buscarTiposInstrumentos());
			$this->set('paises', $this->Pais->listar_paises_nombre()); 
			
		} else {
			$this->redirect('listado_notificacion_viaje');
		}
	}

//CAMBIO DE PIN INICIO 

function cambiar_pin(){

		/* Para validar el acceso al modulo */
		if(!validarTransaccion(100071)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
			$validacion_tdc = $this->Session->read('seguridad.validar_tdc');
			if(
				$validacion_tdc == null ||
				!isset($validacion_tdc['validado']) ||
				!$validacion_tdc['validado']
			){
				$this->Session->write(
					'seguridad.validar_tdc',
						array(
						'url_aceptar'=>'/seguridad/cambiar_pin',
						'layout'=>'interna',
						'titulo'=>'Cambiar Clave de Tarjeta de Débito',
						'pasos'=> array(),
						'paso_actual'=>0,
						'seccion_actual'=>'cambio.pin'
					)
				);
				$this->redirect('/seguridad/validar_tdc');
			}


		$usuario = $this->Session->read('usuarios.datos');

		if ($usuario['tipo_rif'] == '') {
            $tipo_documento = $usuario['tipo_cedula'];
            $nro_documento = str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
        }else{
            $tipo_documento = $usuario['tipo_rif'];
            $nro_documento = str_pad(desencriptar($usuario['rif']),9,'0',STR_PAD_LEFT);
        }

		$tarjetas = $this->Activo->invoca(
							'getTarjetas',
							array(
								'idCliente'=>$tipo_documento.$nro_documento,
								'canal'=>'04',
							)
						);
		
		//Validaciones de codigos de error
		if ($tarjetas==false){ 
	 		$this->redirect('cambiar_pin_error');
		}else{
			 if ($tarjetas['getTarjetasReturn']['codResp']==9999){ 
			 	$this->log('error obteniendo tarjeta '.$tarjetas['getTarjetasReturn']['codResp'],LOG_ERR);
				$this->redirect('cambiar_pin_error');
			
			 }
			  if ($tarjetas['getTarjetasReturn']['codResp']==0){ 
					/*$tarjetas = array($tarjetas['getTarjetasReturn']['tarjeta1'] =>enmascara($tarjetas['getTarjetasReturn']['tarjeta1']) ,
							$tarjetas['getTarjetasReturn']['tarjeta2']=>enmascara($tarjetas['getTarjetasReturn']['tarjeta2'])
							 );*/

					$tarjetasselect[$tarjetas['getTarjetasReturn']['tarjeta1']]=enmascara($tarjetas['getTarjetasReturn']['tarjeta1']);

						
					if(trim($tarjetas['getTarjetasReturn']['tarjeta2'])!=''){


						 $tarjetasselect[$tarjetas['getTarjetasReturn']['tarjeta2']]=enmascara($tarjetas['getTarjetasReturn']['tarjeta2']); 
			}
			}
			if($tarjetas['getTarjetasReturn']['codResp']==29){
				$this->log('Cliente no posee TDD '.$tarjetas['getTarjetasReturn']['codResp'],LOG_ERR);
				$this->redirect('cambiar_pin_notdd');

			}

		}		
		
		if(isset($this->data['CambioPin'])){

			
			$this->CambioPin->set($this->data);
					if($this->CambioPin->validates()){
				
					$cambio = $this->Activo->invoca(
							'resetPin',
							array(
								'sol'=>array(
								'idCliente'=>$tipo_documento.$nro_documento,
								'canal'=>'04',
								'numeroTarjeta'=>$this->data['CambioPin']['tarjeta'],
								'pinActual'=>encriptar('0000'),
								'pinNuevo'=>encriptar($this->data['CambioPin']['pin_nuevo']),							)
							)
							);

					
			if ($cambio==false){ 
		 				
				$this->redirect('cambiar_pin_error');
		 	}else{
				
				if($cambio['resetPinReturn']['codRespuesta']==31){

					

				$errors = 31;

				}
				if($cambio['resetPinReturn']['codRespuesta']==0){
					$canal_sms='72';
				// Enviar correo Notificación de cambio de PIN
			 		$this->Email->smtpOptions = Configure::read('email.smtp');
					$this->Email->delivery	= 'smtp';
					$this->Email->from		= Configure::read('email.from');
					$this->Email->to		= desencriptar($usuario['email_principal']);
					$this->Email->subject	= 'Notificacion de cambio de Pin de Tarjeta de Debito';
					$this->Email->sendAs	= 'html';
					$this->Email->template	= 'cambio_pin';
					$this->Email->send();

						$this->Sms->enviar(
						array(
							'numcel'=>desencriptar($usuario['celular']),
							'canal1'=>$canal_sms
						)
					);	

					$this->Bitacora->registraBitacora($usuario['id'],100071,"","","","","Cambio de Pin tarjeta de debito");
					$this->redirect('actualizar_pin');
				}

				if($cambio['resetPinReturn']['codRespuesta']==30){
					$this->log('Criptograma no existe '.'Cliente :'.$tipo_documento.$nro_documento. ' CodeResp: '.$cambio['resetPinReturn']['codRespuesta'],LOG_ERR);
					$this->redirect('cambiar_pin_error');
				}
				if($cambio['resetPinReturn']['codRespuesta']==24){
					$this->log('Inconsistencia en estatus TDD '.'Cliente :'.$tipo_documento.$nro_documento. ' CodeResp: '.$cambio['resetPinReturn']['codRespuesta'],LOG_ERR,LOG_ERR);
					$this->redirect('cambiar_pin_error');
				}
				if($cambio['resetPinReturn']['codRespuesta']==23){
					$this->log('Cedula no coincide con titular '.'Cliente :'.$tipo_documento.$nro_documento. ' CodeResp: '.$cambio['resetPinReturn']['codRespuesta'],LOG_ERR,LOG_ERR);
					$this->redirect('cambiar_pin_error');
				}
				if($cambio['resetPinReturn']['codRespuesta']==22){
					$this->log('Cedula invalida '.'Cliente :'.$tipo_documento.$nro_documento. ' CodeResp: '.$cambio['resetPinReturn']['codRespuesta'],LOG_ERR,LOG_ERR);
					$this->redirect('cambiar_pin_error');
				}
				if($cambio['resetPinReturn']['codRespuesta']==21){
					$this->log('Tarjeta no es debito '.'Cliente :'.$tipo_documento.$nro_documento. ' CodeResp: '.$cambio['resetPinReturn']['codRespuesta'],LOG_ERR,LOG_ERR);
					$this->redirect('cambiar_pin_error');
				}
				if($cambio['resetPinReturn']['codRespuesta']==27){
					$this->log('Canal no habilitado '.'Cliente :'.$tipo_documento.$nro_documento. ' CodeResp: '.$cambio['resetPinReturn']['codRespuesta'],LOG_ERR,LOG_ERR);
					$this->redirect('cambiar_pin_error');
				}
				if($cambio['resetPinReturn']['codRespuesta']==25){
					$this->log('Estatus de Tarjeta invalido '.'Cliente :'.$tipo_documento.$nro_documento. ' CodeResp: '.$cambio['resetPinReturn']['codRespuesta'],LOG_ERR,LOG_ERR);
					$this->redirect('cambiar_pin_estatus');
				}
			}

		}
	}

	$this->data['CambioClave']['clave_nueva'] = '';
	$this->data['CambioClave']['clave_confirmacion'] = '';
	$this->layout = 'interna';
	$this->set('seccion_actual','cambio.pin');
	$this->set(compact('tarjetasselect', 'errors'));
	}

	function actualizar_pin(){

		$this->redirect('cambiar_pin_resultado');	
		
	}
	function cambiar_pin_error(){

		$this->layout = 'interna';
		$this->set('seccion_actual','cambio.pin');
				
	}
		function cambiar_pin_notdd(){

		$this->layout = 'interna';
		$this->set('seccion_actual','cambio.pin');
			
	}
	
	 function cambiar_pin_resultado(){
		$this->layout = 'interna';
		$this->set('seccion_actual','cambio.pin');
    }
     function cambiar_pin_estatus(){
		$this->layout = 'interna';
		$this->set('seccion_actual','cambio.pin');
    }

	//Fin cambio de pin
}

?>