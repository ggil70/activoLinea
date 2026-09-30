<?php
/**
 * Controlador para todo lo referente a solicitudes y anulaciones
 * de tarjetas de coordenadas
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Controlador para solicitudes y anulaciones de tarjetas de coordenadas
 * 
 */
class SolicitudesController extends AppController {
	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array('Usuario', 'SolicitudTdc', 'Bitacora', 'UsuarioCanal', 'SolicitudCuentaCash');
	
	
	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */		
	var $components = array('Idg','Activo', 'Session','Sms', 'Email');
	
	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */		
	var $helpers = array('Activo','Html','Session','Form','Text');
	
	/**
	 * Acción para solicitar una nueva tarjeta de coordenadas
	**/
	function solicitar_tarjeta_coordenada(){
		if($this->Session->check('usuarios.datos')){
			/* Para validar el acceso al modulo */
			if(!validarTransaccion(20)) $this->redirect('/consultas/integral');
			/* Para validar el acceso al modulo */
						
			$usuario = $this->Session->read('usuarios.datos');
			
                        if( !empty($usuario['tipo_rif']) && ($usuario['rol_juridico_id']==1 || $usuario['rol_juridico_id']>2)) { // PARA JURIDICO MASTER CON DELEGACION								                               

                            $data_session = array(
                                'url_aceptar'=>'/consultas/integral',
                                'url_volver'=>'/consultas/integral',
                                'layout'=>'interna',
                                'titulo'=>'Solicitar Tarjeta de Coordenadas',
                                'pasos'=> array(
                                        'Solicitud',
                                        'Preguntas de Desafío',
                                        'Resultado de Operaci&oacute;n'					
                                ),
                                'paso_solicitud'=>0,
                                'paso_desafio'=>1,
                                'paso_resultado'=>2,
                                'paso_actual'=>0,
                                'seccion_actual'=>'solicitar.tarjeta.coordenada',
                            );
                        }elseif( !empty($usuario['tipo_rif']) && $usuario['rol_juridico_id']==2) { // PARA JURIDICO MASTER UNICO        
                            
                            $data_session = array(
				'url_aceptar'=>'/consultas/integral',
				'url_volver'=>'/consultas/integral',
				'layout'=>'interna',
				'titulo'=>'Solicitar Tarjeta de Coordenadas',
				'pasos'=> array(
					'Solicitud',
                                        'Código de Autorización',
					'Preguntas de Desaf&iacute;o',
					'Resultado de Operaci&oacute;n'					
				),
				'paso_solicitud'=>0,
				'paso_codigo'=>1,
				'paso_desafio'=>2,
                                'paso_resultado'=>3,
                                'paso_actual'=>0,
				'seccion_actual'=>'solicitar.tarjeta.coordenada',
                            );
                            
                        } else { // PARA NATURAL
                                
                            $data_session = array(
				'url_aceptar'=>'/consultas/integral',
				'url_volver'=>'/consultas/integral',
				'layout'=>'interna',
				'titulo'=>'Solicitar Tarjeta de Coordenadas',
				'pasos'=> array(
					'Solicitud',
                                        'Código de Autorización',
					'Preguntas de Autenticaci&oacute;n',
					'Resultado de Operaci&oacute;n'					
				),
				'paso_solicitud'=>0,
				'paso_codigo'=>1,
				'paso_autenticacion'=>2,
                                'paso_resultado'=>3,
                                'paso_actual'=>0,
				'seccion_actual'=>'solicitar.tarjeta.coordenada',
                            );
                        }                        			
			
			$this->Session->write('solicitudes.solicitar_tarjeta_coordenada',$data_session);
			
			// Verifica si es un usuario natural
			if(empty($usuario['tipo_rif'])){
				$usuario_login = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
				$validar_preguntas_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
                                $validacion_otp = $this->Session->read('seguridad.validar_otp');
                                $this->Session->write('seguridad.validar_preguntas_desafio',array('validado'=>true));
                                $validar_preguntas_desafio = $this->Session->read('seguridad.validar_preguntas_desafio');
                        } elseif($usuario['rol_juridico_id']==2) { // Master Unico
                                $usuario_login = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
				$validar_preguntas_desafio = $this->Session->read('seguridad.validar_preguntas_desafio');                                
                                $validacion_otp = $this->Session->read('seguridad.validar_otp');                                
                                $this->Session->write('seguridad.validar_preguntas_autenticacion',array('validado'=>true));
                                $validar_preguntas_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
                                
			} else { // Juridico Master
				$usuario_login = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
				$validar_preguntas_desafio = $this->Session->read('seguridad.validar_preguntas_desafio');                                
                                $this->Session->write('seguridad.validar_otp',array('validado'=>true));
                                $this->Session->write('seguridad.validar_preguntas_autenticacion',array('validado'=>true));
                                $validar_preguntas_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
                                $validacion_otp = $this->Session->read('seguridad.validar_otp');
			}
			
		} else if($this->Session->check('usuarios.registro')){
			$estado = $this->Session->read('usuarios.registro');
		
			if(empty($estado['Usuario']['tipoRif'])){
				$usuario_login = $estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'];
				$validar_preguntas_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
                                $validar_preguntas_desafio = $this->Session->read('seguridad.validar_preguntas_desafio');
                                $validacion_otp = $this->Session->read('seguridad.validar_otp');
			} else {
				$usuario_login = $estado['Usuario']['tipoRif'].$estado['Usuario']['nroRif'].$estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'].$estado['Usuario']['nomUsuario'];
				$validar_preguntas_autenticacion = $this->Session->read('seguridad.validar_preguntas_desafio');
                                $validar_preguntas_desafio = $this->Session->read('seguridad.validar_preguntas_desafio');
                                $validacion_otp = $this->Session->read('seguridad.validar_otp');
			}
			
			$usuario_id = $estado['Usuario']['id'];
			$celular = $estado['Usuario']['codigoCelular'].$estado['Usuario']['nroCelular'];
			
			$data_session = $this->Session->read('solicitudes.solicitar_tarjeta_coordenada');
		} else {
			$this->redirect('/');
		}
		
		$res = $this->Idg->obtenerTdc($usuario_login);
		
		if($res && isset($res['item'])){
			$this->set('ya_tiene_tdc',true);
		} else {
			$this->set('ya_tiene_tdc',false);

			if(isset($this->data['SolicitudTdc']) || $this->Session->check('solicitudes.solicitar_tarjeta_coordenada_confirmada')){
				$this->Session->write('solicitudes.solicitar_tarjeta_coordenada_confirmada',true);
				
				if(($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])){
                                        $this->Session->write(
                                            'seguridad.validar_otp',
                                            array(
                                                'url_aceptar'=>'/solicitudes/solicitar_tarjeta_coordenada',
                                                'url_volver' => '/solicitudes/solicitar_tarjeta_coordenada',
                                                'layout'=>$data_session['layout'],
                                                'titulo'=>$data_session['titulo'],
                                                'pasos'=> $data_session['pasos'],
                                                'paso_actual'=>$data_session['paso_codigo'],
                                                'seccion_actual'=>$data_session['seccion_actual'],
                                            )
                                        );

                                        $this->redirect('/seguridad/genera_otp');
                                }elseif(
					$validar_preguntas_autenticacion == null ||
					!isset($validar_preguntas_autenticacion['validado']) ||
					!$validar_preguntas_autenticacion['validado']
				){
					// PARA NATURALES REALIZA PREGUNTAS DE AUTENTICACION					
					
                                        // Colocar data para el manejador de preguntas de seguridad
                                        $this->Session->write(
                                                'seguridad.validar_preguntas_autenticacion',
                                                array(
                                                    'url_aceptar'=>'/solicitudes/solicitar_tarjeta_coordenada',
                                                    'url_volver'=>'/solicitudes/solicitar_tarjeta_coordenada',
                                                    'layout'=>$data_session['layout'],
                                                    'titulo'=>$data_session['titulo'],
                                                    'cantidad_preguntas_autenticacion'=>4,
                                                    'pasos'=> $data_session['pasos'],
                                                    'paso_actual'=>$data_session['paso_autenticacion'],
                                                    'seccion_actual'=>$data_session['seccion_actual'],
                                                )
                                        );
                                        // Redireccionar a las preguntas de seguridad
                                        $this->redirect('/seguridad/validar_preguntas_autenticacion');									
                                        
                                }elseif(
					$validar_preguntas_desafio == null ||
					!isset($validar_preguntas_desafio['validado']) ||
					!$validar_preguntas_desafio['validado']
				){					
					// PARA JURIDICOS REALIZA PREGUNTAS DE DESAFIO
					
                                        // Colocar data para el manejador de preguntas de desafio
                                        $this->Session->write(
                                                'seguridad.validar_preguntas_desafio',
                                                array(
                                                    'url_aceptar'=>'/solicitudes/solicitar_tarjeta_coordenada',
                                                    'url_volver'=>'/solicitudes/solicitar_tarjeta_coordenada',
                                                    'layout'=>$data_session['layout'],
                                                    'titulo'=>$data_session['titulo'],
                                                    'cantidad_preguntas_desafio'=>3,
                                                    'usuario'=>$usuario,
                                                    'pasos'=> $data_session['pasos'],
                                                    'paso_actual'=>$data_session['paso_desafio'],
                                                    'seccion_actual'=>$data_session['seccion_actual'],
                                                )
                                        );
                                        // Redireccionar a las preguntas de desafio
                                        $this->redirect('/seguridad/validar_preguntas_desafio');
                                 
                                }else{
                                        
                                        $this->Session->write(
                                                'solicitudes.solicitar_tarjeta_coordenada',
                                                array(
                                                    'url_aceptar'=>$data_session['url_aceptar'],
                                                    'url_volver'=>$data_session['url_volver'],
                                                    'layout'=>$data_session['layout'],
                                                    'titulo'=>$data_session['titulo'],                                                    
                                                    'pasos'=> $data_session['pasos'],
                                                    'paso_actual'=>$data_session['paso_resultado'],
                                                    'seccion_actual'=>$data_session['seccion_actual'],
                                                )
                                        );                                       
					$this->redirect('solicitar_tc_resultado');
				}
			}
		}
		
		$this->layout = $data_session['layout'];
		$this->set(compact('data_session'));

		if(isset($data_session['seccion_actual'])){
			$this->set('seccion_actual',$data_session['seccion_actual']);
		}
	}

	/**
	 * Acción para mostrar el resultado de la solicitud de la tarjeta de
	 * coordenadas
	**/
	function solicitar_tc_resultado(){
		if(!$this->Session->check('solicitudes.solicitar_tarjeta_coordenada')) $this->redirect('/');
		
		if(!$this->Session->check('usuarios.registro')){
			$usuario = $this->Session->read('usuarios.datos');
		
			if(empty($usuario['tipo_rif'])){
				$usuario_login = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
				$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
			} else {
				$usuario_login = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
				$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_desafio');
			}
			
			$usuario_id = $usuario['id'];
			$celular = desencriptar($usuario['celular']);
		} else {
			$estado = $this->Session->read('usuarios.registro');
		
			if(empty($estado['Usuario']['tipoRif'])){
				$usuario_login = $estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'];
				$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
			} else {
				$usuario_login = $estado['Usuario']['tipoRif'].$estado['Usuario']['nroRif'].$estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'].$estado['Usuario']['nomUsuario'];
				$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_desafio');
			}
			
			$usuario_id = $estado['Usuario']['id'];
			$celular = $estado['Usuario']['codigoCelular'].$estado['Usuario']['nroCelular'];
		}		
		
		$data_session = $this->Session->read('solicitudes.solicitar_tarjeta_coordenada');
		
		if(
			empty($estado_autenticacion) ||
			!isset($estado_autenticacion['validado']) ||
			!$estado_autenticacion['validado'] ||
			!$this->Session->check('solicitudes.solicitar_tarjeta_coordenada_confirmada')
		){
			// El usuario no completo las preguntas de autenticacion
			$this->redirect('solicitar_tarjeta_coordenada');
		} else {


			$res = $this->Idg->crearTdc($usuario_login);
			
			$this->Idg->desbloquearUsuario($usuario_login, 'GRID');

			if($res){
				$this->UsuarioCanal->cambioEstatus(
					'tdc',
					'A', // Activa
					$usuario_id
				);
				
				if($this->Session->check('usuarios.datos')){
					$usuario['estatus_tdc'] = 'A';
					
					$this->Session->write('usuarios.datos',$usuario);
				}
				
				$this->Bitacora->registraBitacora(
					$usuario_id,
					20
				);
				
				$res = $this->Idg->obtenerTdc($usuario_login);	
			
				if($res && isset($res['item'])){
					$tdc = array();
					
					foreach($res['item']['Grid']['cells'] as $cell){
						$tdc[$cell['row']][$cell['column']]=$cell['value'];
					}
					
					$serial = $res['item']['SerialNumber'];
					
					$this->set(compact('tdc','serial'));
					
					// Enviar por sms la notificación de que ya se realizó la generación de tdc
				
					$this->Sms->enviar(	
						array(
							'numcel'=>$celular,
							'canal1'=>'31',
							'xrnde11'=>$serial
						)
					);
					
					$this->set('tdc_creada',true);		
					$this->Session->write('solicitudes.puede_imprimir_tdc',true);
                                        
                                        //Si viene del registro, es Juridico y es Master o Master/Unico mando a finalizar el registro
                                        if($this->Session->check('usuarios.registro')){                                       
                                            $estado = $this->Session->read('usuarios.registro');
                                            if (!empty($estado['Usuario']['tipoRif']) && 
                                                $estado['Usuario']['perfil'] <= 2){
                                                $this->requestAction('/usuarios/finalizar_registro_juridico');
                                            }
                                        }
                                        
				} else {
					$this->set('error_fatal',_m('error.fatal'));					
				}
			} else {				
				$this->set('tdc_creada',false);
			}
		
			$this->Session->delete('solicitudes.solicitar_tarjeta_coordenada');
			$this->Session->delete('seguridad.validar_preguntas_autenticacion');
			$this->Session->delete('seguridad.validar_preguntas_desafio');
			$this->Session->delete('solicitudes.solicitar_tarjeta_coordenada_confirmada');

		}
		
		$this->layout = $data_session['layout'];
		
		$this->set(compact('data_session'),'estado','usuario');

		if(isset($data_session['seccion_actual'])){
			$this->set('seccion_actual',$data_session['seccion_actual']);
		}
	}

	/**
	 * Acción para solicitar la anulación de la tarjeta de coordeandas
	**/
	function anular_tc_motivo(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(21)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		$this->layout = 'interna';

		$this->set('seccion_actual','anular.tarjeta.coordenada');
		
		$usuario = $this->Session->read('usuarios.datos');
		
		// Verifica el estatus de la tarjeta
		$estatus = $this->Usuario->find('first', array( 'fields'=>'estatus_tdc', 'conditions'=>array('id'=>$usuario['id'])) );
		if($estatus['Usuario']['estatus_tdc'] == 'B'){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/solicitudes/anular_tc_motivo',
					'layout'=>'interna',
					'titulo'=>'Anular Tarjeta de Coordenadas',
					'pasos'=> array(),
					'paso_actual'=>0,
					'seccion_actual'=>'anular.tarjeta.coordenada'
				)
			);
			$this->redirect('/seguridad/tdc_bloqueada');
		}
		
		// Verifica si es un usuario juridico o natural
		if(empty($usuario['tipo_rif'])){
			$login_idg = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
		} else {
			$login_idg = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
		}
				
		$res = $this->Idg->obtenerTdc($login_idg);
		
		$motivos=$this->SolicitudTdc->obtenerMotivosAnulacion();
		
		if($res && isset($res['item'])){
			$this->set('ya_tiene_tdc',true);			                        
                        
                        $serial = $res['item']['SerialNumber'];                       
                                        
			if(!empty( $this->data['SolicitudTdc'] )){
				$this->SolicitudTdc->set($this->data);

				if ($this->SolicitudTdc->validates(array('fieldList' => array('tipo_motivo','motivo')))) {
					
					$res = $this->Idg->anularTdc($login_idg);
								
					if($res){
						$this->Bitacora->registraBitacora(
							$usuario['id'],
							100005
						);
					
						$this->UsuarioCanal->cambioEstatus(
							'tdc',
							'N', // Anulada
							$usuario['id']
						);
									
						$usuario['estatus_tdc'] = 'N';
						
						$this->Session->write('usuarios.datos',$usuario);
						
						if($this->data['SolicitudTdc']['tipo_motivo']=='o'){
							$this->Bitacora->registraBitacora(
								$usuario['id'],
								21,
								null,
								null,
								null,
								null,
								encriptar($this->data['SolicitudTdc']['motivo'])
							);
						} else {
							$this->Bitacora->registraBitacora(
								$usuario['id'],
								21,
								null,
								null,
								null,
								null,
								$motivos[$this->data['SolicitudTdc']['tipo_motivo']]
							);						
						}										                                                
                                                                                                
						$this->Session->delete('seguridad.validar_tdc');
						$this->Session->write('solicitudes.confirma_anula_tdc', 'ok');
						$this->redirect('anular_tc_resultado');
					} else {
						$this->set('error_fatal',_m('error.fatal'));
					}
				}
			}
		} else {
			$this->set('ya_tiene_tdc',false);
		}
		
		$this->set(compact('motivos'));
	}

	/**
	 * Acción para mostrar el resultado de la anulación de la tarjeta de coordenadas
	**/
	function anular_tc_resultado(){
		$this->layout = 'interna';

		$this->set('seccion_actual','anular.tarjeta.coordenada');

		if($this->Session->check('solicitudes.confirma_anula_tdc')){
			$this->Session->delete('solicitudes.confirma_anula_tdc');
		} else {
			$this->redirect('anular_tc_motivo');
		}
	}
	
	/**
	 * Acción para descargar la tarjeta de coordenadas recien generada
	 * en formato PDF
	**/
	function descargar_tdc(){

		if(!$this->Session->check('solicitudes.puede_imprimir_tdc')){
			// El usuario no completo las preguntas de autenticacion
			$this->redirect('solicitar_tarjeta_coordenada');
			
		}
	
		$this->Session->delete('solicitudes.puede_imprimir_tdc');
	
		if($this->Session->check('usuarios.registro')){
			$estado = $this->Session->read('usuarios.registro');
			
			if(empty($estado['Usuario']['tipoRif'])){
				$usuario_login = $estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'];
			} else {
				$usuario_login = $estado['Usuario']['tipoRif'].$estado['Usuario']['nroRif'].$estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'].$estado['Usuario']['nomUsuario'];
			}
			
			$usuario_id = $estado['Usuario']['id'];
			$identificacion = $estado['Usuario']['nroCedulaRif'];
		} else {
			$usuario = $this->Session->read('usuarios.datos');
			
			if(empty($usuario['tipo_rif'])){
				$usuario_login = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
			} else {
				$usuario_login = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
			}
			
			$usuario_id = $usuario['id'];
			$identificacion = desencriptar($usuario['cedula']);
		}
		
		$res = $this->Idg->obtenerTdc($usuario_login);	
			
		if($res && isset($res['item'])){
			$tdc = array();
			
			foreach($res['item']['Grid']['cells'] as $cell){
				$tdc[$cell['row']][$cell['column']]=$cell['value'];
			}
			
			$serial = $res['item']['SerialNumber'];
			
			$this->set(compact('tdc','serial','identificacion'));
		} else {
			$this->redirect('solicitar_tarjeta_coordenada');
		}
	}
        
        /**
	 * Acción que da inicio al proceso de solicitud de Afiliacion/Desafiliacion Movil Activo
         * @param string $accion Indica si se está trabajando una afiliacion (a) o una desafiliacion (d)
         * @author Marcel Guglielmelli <mguglielmelli@bancoactivo.com>
	**/
	function afiliacion_movil_activo($accion=null){		                
                
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(array(100060,100061))) {
                    $this->redirect('/consultas/integral');
                }
		/* Para validar el acceso al modulo */	
                
                $usuario = $this->Session->read('usuarios.datos');
                
                switch($accion){
			case 'a':
				$this->set('seccion_actual','movil.afiliar');
				$this->set('titulo','Movil Activo Afiliación');
                                $seccion_actual = 'movil.afiliar';                                
                                $titulo = 'Movil Activo Afiliación';
				break;
			case 'd':
				$this->set('seccion_actual','movil.desafiliar');
				$this->set('titulo','Movil Activo Desafiliación');
                                $seccion_actual = 'movil.desafiliar';                                
                                $titulo = 'Movil Activo Desafiliación';
				break;
		}
                
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		if(
			$validacion_tdc == null ||
			!isset($validacion_tdc['validado']) ||
			!$validacion_tdc['validado']
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/solicitudes/afiliacion_movil_activo/'.$accion,
					'layout'=>'interna',
					'titulo'=>$titulo,
					'pasos'=> array(
						'Tarjeta de Coordenadas',						
						'Confirmación',
						'Resultado de la Operación'
					),
					'paso_actual'=>0,
					'seccion_actual'=>$seccion_actual
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}

		$this->layout = 'interna';
                $this->set('usuario',$usuario);
                $this->set('accion',$accion);
                                
	}
        
        /**
	 * Acción que mustra el resultado del proceso de solicitud de Afiliacion/Desafiliacion Movil Activo         
         * @author Marcel Guglielmelli <mguglielmelli@bancoactivo.com>
	**/
	function afiliacion_movil_resultado($accion=null){
                
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(array(100060,100061))) {
                    $this->redirect('/consultas/integral');
                }
		/* Para validar el acceso al modulo */	
                
                $usuario = $this->Session->read('usuarios.datos');
                $res = false;
                
                switch($accion){
			case 'a':
                                $this->set('seccion_actual','movil.afiliar');
				$this->set('titulo','Movil Activo Afiliación');                                
				$res = $this->Usuario->afiliacion_movil($usuario['id'],1);
                                $transaccion = 100060;
                                $sms = 'Afiliación';
				break;
			case 'd':				
                                $this->set('seccion_actual','movil.desafiliar');
				$this->set('titulo','Movil Activo Desafiliación');
                                $res = $this->Usuario->afiliacion_movil($usuario['id'],0);
                                $transaccion = 100061;
                                $sms = 'Desafiliación';
				break;
		}                		
                
                if($res){
                    $this->Bitacora->registraBitacora(
                        $usuario['id'],
                        $transaccion,
                        null,
                        null,
                        null,
                        null,
                        'Afiliación/Desafiliación por Activo en Línea',
                        0    
                    );
                    
                    // Enviar por sms la notificación                    
                    $this->Sms->enviar(	
                            array(
                                    'numcel'=>desencriptar($usuario['celular']),
                                    'canal1'=>'63',
                                    'fectim'=>$sms
                            )
                    );
                    
                    //Enviar por email la notificación
                    // Email Options
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = desencriptar($usuario['email_principal']);
                    $this->Email->subject = 'Servicio de Movil Activo';
                    $this->Email->sendAs = 'html';                   

                    // Informacion del correo
                    $this->Email->template = 'afiliacion_movil';
                    $this->set(compact('sms','usuario'));

                    // Envio del correo
                    $this->Email->send();
                }else{
                    $this->Bitacora->registraBitacora(
                        $usuario['id'],
                        $transaccion,
                        null,
                        null,
                        null,
                        null,
                        'Afiliación/Desafiliación por Activo en Línea',
                        1    
                    );
                }                
                
                $this->set('resultado',$res);
		$this->layout = 'interna';            
                                
	}

    /**
	 * Acción que da inicio al proceso de solicitud de Cuenta Moneda Extranjera USD
       * @author Albert Soto <asoto@bancoactivo.com>
	**/
	function solicitar_cuenta_cash($accion=null){		                
               
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(101097)) {
        	$this->redirect('/consultas/integral');
        }
		/* Para validar el acceso al modulo */	
		$usuario = $this->Session->read('usuarios.datos');

		$datos_basicos = $this->Session->read('usuarios.nibpf3010');

		$identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];

		$cuenta_veb = $this->Activo->buscarInstrumentosSaldoActivoC(array('SAV', 'DDA', 'NOW', 'MMK'));

		$cuenta = $this->Activo->buscarInstrumentosSaldoActivo(array('SAV', 'DDA', 'NOW', 'MMK'));

		$accion_msj = 0;

		if (substr(implode($cuenta), 0, 1) == '8') {
			
			$accion_msj = 1;
			$this->redirect(array('controller'=>'/solicitudes/solicitar_cuenta_cash_fallida',  'action' => $accion_msj));
		}

		if (substr(implode($cuenta_veb), 0, 4) != '0171' &&
			substr(implode($cuenta), 0, 1) == '8') {
			
			$accion_msj = 2;
			$this->redirect(array('controller'=>'/solicitudes/solicitar_cuenta_cash_fallida',  'action' => $accion_msj));
		}

		if (substr(implode($cuenta_veb), 0, 4) == '0171' &&
			substr(implode($cuenta), 0, 1) == '8') {
			
			$accion_msj = 3;
			$this->redirect(array('controller'=>'/solicitudes/solicitar_cuenta_cash_fallida',  'action' => $accion_msj));
		}

		if (strlen(implode($cuenta_veb)) == 44 || 
			substr(implode($cuenta_veb), 0, 4) != '0171') {
			
			$accion_msj = 4;
			$this->redirect(array('controller'=>'/solicitudes/solicitar_cuenta_cash_fallida',  'action' => $accion_msj));
		}

		$this->layout = 'interna';
        $this->set('usuario',$usuario);
                                
	}

	function solicitar_confirmacion_cuenta_cash($accion=null){		                
    
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(101097)) {
        	$this->redirect('/consultas/integral');
        }
		/* Para validar el acceso al modulo */	

		$usuario = $this->Session->read('usuarios.datos');

		$datos_basicos = $this->Session->read('usuarios.nibpf3010');

		if(!empty($this->data['Confirmacion'])){
                $this->redirect('/solicitudes/solicitar_cuenta_cash_resultado');
            }

		$this->layout = 'interna';
        $this->set('usuario',$usuario);
        $this->set('accion',$accion);
                                
	}

		/*
	 * Muestra Contrato de Aceptacion de Servicio de Cuenta Cash
	 */
        function contrato_cuenta_cash($contrato=NULL){
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(array(101097))) $this->redirect('/consultas/integral');
            
            $usuario = $this->Session->read('usuarios.datos');

            // Datos del Usuario
            $datos_basicos = $this->Session->read('usuarios.nibpf3010');
            
            $datos = $datos_basicos['NIBPF3010Return']['NIBPF3010'];

            $nombre = $datos['INTLNADB'];
            
            if($datos['TIPOID'] == 'CIV'){                
                $datos['NACIONALIDAD'] = 'Venezolana';
            }else{    
                $datos['NACIONALIDAD'] = 'Extranjera';
            }

            if (substr($datos['IDENTIFICACIONIBS'], 0, 1) == 'V') {
            	$cedula = $datos['IDENTIFICACIONIBS'];
            }else{
            	$pasaporte = $datos['IDENTIFICACIONIBS'];
            }

            $ciudad = 'Caracas';

            $edo = 'DISTRITO CAPITAL';
 
            $this->set(compact('datos','nombre','cedula','pasaporte','usuario'));
        }
        


	/**
	 * Acción que muestra mensaje si el cliente posee Cuenta Moneda Extranjera USD
       * @author Albert Soto <asoto@bancoactivo.com>
	**/
	function solicitar_cuenta_cash_fallida($accion_msj){

		/* Para validar el acceso al modulo */
		if(!validarTransaccion(101097)) {
            $this->redirect('/consultas/integral');
        }
		
		$usuario = $this->Session->read('usuarios.datos');

        $descripcion = "";

		switch ($accion_msj) {
			case '1':
				$descripcion = _m('cuenta.cash.posee');
				break;
			case '2':
				$descripcion = _m('cuenta.cash.posee');
				break;
			case '3':
				$descripcion = _m('cuenta.cash.posee');
				break;
			case '4':
				$descripcion = _m('cuenta.cash.ctaveb');
				break;
			default:
				$descripcion = _m('operacion.fallida');
				break;
		}	

		$this->layout = 'interna';
        $this->set('usuario',$usuario);
        $this->set(compact('descripcion'));

	}

	/**
	 * Acción que muestra resultado de la apertura de Cuenta ME USD
         * @author Albert Soto <asoto@bancoactivo.com>
	**/
	function solicitar_cuenta_cash_resultado($accion=null){		                
     
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(101097)) {
  			$this->redirect('/consultas/integral');
        }

		$usuario = $this->Session->read('usuarios.datos');

		//$this->Bitacora->registraBitacora($usuario['id'], 101096);

		$usuario_login = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);

       	$validacion_otp = $this->Session->read('seguridad.validar_otp');

              
               if (($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])){

                    $this->Session->write(
                        'seguridad.validar_otp', array(
                            'url_aceptar' => '/solicitudes/solicitar_cuenta_cash_resultado',
                            'url_volver' => '/solicitudes/solicitar_cuenta_cash',
                            'layout' => 'interna',
                            'titulo' => 'Solicitar Cuenta Cash',
                            'pasos' => $estado['pasos'],
                            'paso_actual' => 2,
                            'seccion_actual' => 'solicitar.cuenta'
                        )
                    );

                    $this->redirect('/seguridad/genera_otp');
                }

                $datos_basicos = $this->Session->read('usuarios.nibpf3010');

                $identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];

                $res = $this->Activo->invoca(
						'APEDSCASHE',
						array(
							'req'=>array(
								'APEUSRE'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']
							)
						)
					);


                $aux =  $this->Activo->completaCuenta($res['APEDSCASHEReturn']['APEDSCASHE']['APEACCS']);
                $cta_usd = str_pad($aux['EDL890101Return']['EDL890101']['e01ACC020'],20,0,STR_PAD_LEFT);
                $identificacion = $res['APEDSCASHEReturn']['APEDSCASHE']['APEUSRE'];

				$this->data['Error'] = false;

                if ($res['APEDSCASHEReturn']['APEDSCASHE']['APERESP'] > 0) { 

                	$this->data['Error'] = true;
                	$cod_error = $res['APEDSCASHEReturn']['APEDSCASHE']['APERESP'];

                	switch ($cod_error) {
                		case '1':
                			//$this->redirect('/solicitudes/solicitar_cuenta_cash_fallida');
                			$descripcion = _m('cuenta.cash.posee');
                			break;
                		
                		case '2':
                			//$this->redirect('/solicitudes/solicitar_cuenta_cash_fallida');
                			$descripcion =  _m('cuenta.cash.edad');
                			break;
                	}

                }else{

                // Email Options
				$this->Email->smtpOptions = Configure::read('email.smtp');
				$this->Email->delivery = 'smtp';
				$this->Email->from = Configure::read('email.from');
				$this->Email->to = desencriptar($usuario['email_principal']);
				$this->Email->subject = 'Notificacion Apertura de Cuenta Cash';
				$this->Email->sendAs = 'html';

				// Informacion del correo
				$this->Email->template	= 'apertura_cuenta_cash';				
				$tipo_accion = 'Apertura Cuenta Cash';
				$cta_aperturada = $cta_usd;
				$descripcion = "Apertura de Cuenta Cash" . enmascara($cta_usd);	

				$this->set(compact('cta_usd','identificacion','datos_basicos','usuario','tipo_accion','cta_aperturada'));
				
				// Envio del correo
				$this->Email->send();

				$this->Bitacora->registraBitacora(
					$usuario['id'], 
					101097,
					NULL, 
					NULL, 
					NULL,
					NULL,
					$descripcion,
					0
				);
				
               }

               $this->layout = 'interna';
               $this->set('descripcion', $descripcion);
               $this->set('usuario',$usuario);
               $this->set('accion',$accion);
                                
	}
	
}

?>