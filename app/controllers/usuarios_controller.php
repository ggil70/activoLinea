<?php
/**
 * Controlador que maneja todas las acciones referentes a:
 * login, registro y olvido de datos
*/

/**
 * Controlador que maneja todas las acciones referentes a:
 * login, registro y olvido de datos
*/
class UsuariosController extends AppController{
	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */	
	var $uses = array(
		'Usuario',
		'PreguntasDesafio',
		'Sello',
		'Bitacora',
		'Parametro',
		'TransaccionPerfil',
		'AliasProducto',
		'TransaccionJuridico',
		'TransaccionJuridicoDetalle',
		'PilotoUsuarios',
		'UsuarioCanal',
		'Banners',
		'RolJuridico',
		'TransaccionPendiente',
		'AuditoresExternos'
	);

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */	
	var $components = array('Idg','Activo','Session','Sms','MigracionJuridico','Email');

	/**
	 * Acción que maneja el formulario de inicio de sesión
	 * @param string $juridico Indica si se carga la extranet para usuarios jurídicos o naturales.
	 * 'N' para naturales, 'J' para jurídicos
	*/
	function login($juridico=NULL){
	
		if(!$juridico){
			// Si el tipo de persona no viene por parámetro
			// buscarlo en sesión
			if($this->Session->check('login.juridico')){
				$juridico = $this->Session->read('login.juridico');
			} else if(isset($_COOKIE['tipo_persona'])){
				// Si no está en sesión, buscarlo en cookie
				
				if(!in_array($_COOKIE['tipo_persona'], array('N','J'))){
					// Si tiene un valor no reconocido, colocarle Natural
					$juridico = 'N';
				} else {
					$juridico = $_COOKIE['tipo_persona'];
				}
			} else {
				// Si no está en cookie, colocar a natural por defecto
				$juridico = 'N';
			}
		}
		
		// Guardar la cookie con el tipo de persona
		setcookie('tipo_persona', $juridico, 0, '/');
		
		// Destruye las sesiones
		$this->Session->destroy();
		
		// Verifica si es un usuario juridico
		if($juridico=='J'){
			$this->Session->write('login.juridico', $juridico);
			$this->set(compact('juridico'));
		}
		
		if(!empty($this->data['Usuario'])){
			$this->Usuario->set($this->data);

			if ($this->Usuario->validates(array('fieldList' => array('login', 'tipoRif', 'nroRif')))) {
				
				/** Para la verificacion de los usuarios pilotos **/
				if($this->Session->check('login.juridico') && _p('piloto_jur')){
					if( !$this->PilotoUsuarios->buscar($this->data['Usuario']['nroRif'], '', 'J') ){
						$this->redirect('/');
					}
				}
				/** Para la verificacion de los usuarios pilotos **/
				
				
				// Coindiciones para el login
				$conditions_login = array(
					'Usuario.login' => encriptar(strtolower($this->data['Usuario']['login']))
				);
				
				// Verifica si es una persona juridica 
				if(isset($this->data['Usuario']['tipoRif']) && isset($this->data['Usuario']['nroRif'])){
					$conditions_login['Usuario.tipo_rif'] = $this->data['Usuario']['tipoRif'];
					$conditions_login['Usuario.rif'] = encriptar($this->data['Usuario']['nroRif']);
				} else {
					// Para que no traiga el mismo usuario pero en jurídico
					$conditions_login []= 'Usuario.tipo_rif is null';
				}
				
				// busqueda del usuario
				$login = $this->UsuarioCanal->encontrarUsuario($conditions_login);
				
				if(!isset($login['Usuario'])){
					// Si no consigue el usuario, revisar si es cliente del ib anterior
					// utilizando el servicio SUMDSJV que indicó el banco
					
					if($this->Session->check('login.juridico')){ // JURIDICO
						
						$sumdsjv = $this->Activo->invoca(
							'SUMDSJV',
							array( 'req'=>array('SUMUSR'=>$this->data['Usuario']['tipoRif'].$this->data['Usuario']['nroRif']) )
						);

						if(!$sumdsjv || isset($sumdsjv['SUMDSJVReturn']['error'])){
							// si no lo encuentra muestra mensaje de error
							$this->Usuario->validationErrors['login'] = 'usuario.notfound';
						} else {
							
							$arrLegacy = array(
								'Usuario'=>array(
									'tipoRif' => $this->data['Usuario']['tipoRif'],
									'nroRif' => $this->data['Usuario']['nroRif'],
								),
								'legacy'=>true,
							);
							
							// Verfica si se encuentra el usuario en la base de datos (operador, aprobador, operador/aprobador)
							$validaBD = $this->MigracionJuridico->validarUsuario(
								$this->data['Usuario']['tipoRif'].$this->data['Usuario']['nroRif'],
								$this->data['Usuario']['login']
							);
							
							// Asigna si el perfil si es un usuario master
							if(!$validaBD){
								
								// Verifica si es un usuario master
								$master = $this->Activo->invoca(
									'PWDDSJV',
									array(
										'req'=> array(
											'PWDUSR'=>substr($this->data['Usuario']['login'],0,10),
											'PWDLGT'=>3,
											'PWDCMP'=>$this->data['Usuario']['tipoRif'].$this->data['Usuario']['nroRif']
										)
									)
								);
								
								if(!$master || isset($master['PWDDSJVReturn']['error']) || ($master && $master['PWDDSJVReturn']['PWDDSJV']['PWDSTS']==6)){
									// si no lo encuentra muestra mensaje de error
									$this->Usuario->validationErrors['login'] = 'usuario.notfound';
								}
								else{
									$arrLegacy['Usuario']['perfil'] = 1;
									$arrLegacy['Usuario']['ibuser'] = encriptar(strtolower($this->data['Usuario']['login']));
									
									// Busca el limite de otros bancos del usuario master
									$limite_master = $this->Activo->limiteMaster($this->data['Usuario']['tipoRif'],$this->data['Usuario']['nroRif']);
									if($limite_master) $arrLegacy['Usuario']['limite_master'] = $limite_master;

								}
							}
							
							// Para la busqueda del usuario por rif
							$condicion_documento = array(
								'Usuario.rif' => encriptar( $this->data['Usuario']['nroRif'] ),
								'Usuario.tipo_rif' => $this->data['Usuario']['tipoRif'],
							);

							if(!$validaBD){
								$condicion_documento['Usuario.rol_juridico_id'] = 1;
							}
							else{

								// Indica el estatus
								switch ($validaBD['STATUS']){
									case 'INACTIVO':
										$estatus_acceso = 'I';
										break;
									case 'SUSPENDIDO':
										$this->Usuario->validationErrors['login'] = 'usuario.notfound';
										break;
									default:
										$estatus_acceso = 'A';
										break;
								}

								// Almacena para la migracion
								$arrLegacy['Usuario']['accesoIB'] = $estatus_acceso;
								$arrLegacy['Usuario']['ibuser'] = encriptar(strtolower($validaBD['USERID']));

								// Condición de busqueda para revisar si ya realizo la migracion
								$condicion_documento['Usuario.ibuser'] = $arrLegacy['Usuario']['ibuser'];
							}
						}
					}
					else{ // NATURAL
						$sumdsjv = $this->Activo->invoca(
							'SUMDSJV',
							array( 'req'=>array('SUMUSR'=>substr($this->data['Usuario']['login'],0,10)) )
						);

						if(!$sumdsjv || isset($sumdsjv['SUMDSJVReturn']['error'])){
							// si no lo encuentra muestra mensaje de error
							$this->Usuario->validationErrors['login'] = 'usuario.notfound';
						} else {
							// El usuario ya estaba registrado en el ib anterior

							// Para la busqueda del usuario por cedula
							$condicion_documento = array('Usuario.cedula' => encriptar($this->data['Usuario']['login']) );

							/** Para la verificacion de los usuarios pilotos **/
							if(_p('piloto_nat')){
								if( !$this->PilotoUsuarios->buscar($this->data['Usuario']['login'], '', 'N') ){
									$this->redirect('/');
								}
							}
							/** Para la verificacion de los usuarios pilotos **/
						}
					}
					
										
					// Verifica si es un usuario juridico no master y si no hay mensajes de error
					if( $this->Session->check('login.juridico') && !isset($condicion_documento['Usuario.rol_juridico_id']) && !isset($this->Usuario->validationErrors['login']) ){

						// Verifica si el master de la empresa esta registrado y que no esta suspendido o bloqueado por seguridad
						$master = $this->UsuarioCanal->encontrarUsuario(
							array(
								'rol_juridico_id'=>1,
								'tipo_rif'=>$this->data['Usuario']['tipoRif'],
								'rif'=>encriptar( $this->data['Usuario']['nroRif'] ),
								'estatus_acceso'=>array('BS', 'ST'),
							)
						);

						// Si existe el master con las condiciones anteriores no permite la entrada al sistema
						if($master['Usuario']){
							$this->Usuario->validationErrors['login'] = _m('usuario.bloqueo.acceso');
						}
					}
					
					
					if(!isset($this->Usuario->validationErrors['login'])){
						
						// Buscarlo por cédula en el IB actual
						$login = $this->UsuarioCanal->encontrarUsuario($condicion_documento);
						
						if(isset($login['Usuario'])){
							// El usuario ya está registrado en el IB nuevo
							
							// Verificar el estado del registro
							
							switch($login['Usuario']['estatus_registro']){
								case 'B':
									$this->Session->write('usuarios.registro',true);
									$this->redirect('registro_bloqueado');
									break;
								case 'I1':
								case 'I2':
									$this->Usuario->validationErrors['login'] = 'usuario.oficina.comercial';
									break;
								case 'P':
									$this->Usuario->validationErrors['login'] = 'registro.pendiente';
									break;
								default:
									// No podemos reconocer la cédula como login
									// ya que el usuario ya esta registrado
									$this->Usuario->validationErrors['login'] = 'usuario.notfound';
									break;
							}
							
						} else {
							
							// Verifica si es un usuario master que se realizo la eliminacion 
							// del perfil a traves del backend
							if(isset($condicion_documento['Usuario.rol_juridico_id'])){
								$login = $this->Usuario->find(
									'first',
									array(
										'conditions'=>$condicion_documento
									)
								);
								if(isset($login['Usuario'])) $this->Usuario->validationErrors['login'] = 'usuario.notfound';
							}
							
							$parametro_fecha_limite = $this->Session->check('login.juridico') ? 'fecha_limite_migrados_J' : 'fecha_limite_migrados';
							
							// El usuario no esta registrado en el ib nuevo
							// Verificar la fecha límite de registro de usuarios migrados
							if(
								time() <= strtotime(_p($parametro_fecha_limite)) 
									&& !isset($this->Usuario->validationErrors['login'])
							){
								$flag_registro= true;
								
								// En caso de que exista un master unico no puede registrarse un usuario:
								// operador, aprobador, aprobador/operador
								if($this->Session->check('login.juridico') && !isset($condicion_documento['Usuario.rol_juridico_id'])){
									
									// Verifica si ya existe un master unico
									$unico = $this->UsuarioCanal->encontrarUsuario(
										array(
											'rol_juridico_id'=>2,
											'tipo_rif'=>$this->data['Usuario']['tipoRif'],
											'rif'=>encriptar( $this->data['Usuario']['nroRif'] ),
										)
									);
									if($unico['Usuario']){
										$flag_registro= false;
										$this->Usuario->validationErrors['login'] = _m('usuario.bloqueo.acceso');
									}
								}
								
								if($flag_registro){
									// Redirigir para autenticar la contraseña vieja
									$login = array(
										'url_volver'=>'login',
										'legacy'=> $this->Session->check('login.juridico') ? $arrLegacy : true,
										'identificacion'=>$this->data['Usuario']['login']
									);

									$this->Session->write('usuarios.login', $login);
									$this->redirect('contrasena');
								}
								
							} else if( !isset($this->Usuario->validationErrors['login']) ){
								$this->redirect('registro');
							}
						}
					}
				} else {
					// Consiguió el usuario en bd
					
					$flag_siguiente = true;
					
					// Verifica si es un usuario juridico no master
					if( !empty($login['Usuario']['tipo_rif']) && $login['Usuario']['rol_juridico_id']>2 ){

						// Verifica si el master de la empresa esta registrado y que no esta suspendido o bloqueado por seguridad
						$master = $this->UsuarioCanal->encontrarUsuario(
							array(
								'rol_juridico_id'=>1,
								'tipo_rif'=>$login['Usuario']['tipo_rif'],
								'rif'=>$login['Usuario']['rif'],
								'estatus_acceso'=>array('BS', 'ST'),
							)
						);
                                                    
						// Si existe el master con las condiciones anteriores no permite la entrada al sistema
						if($master['Usuario']){
							$this->Usuario->validationErrors['login'] = _m('usuario.bloqueo.acceso');
							$flag_siguiente = FALSE;
						}
					}
					
					
					if($flag_siguiente){
						
						// Verificar el estado del registro
						switch($login['Usuario']['estatus_registro']){
//							case 'P':
//								$flag_siguiente = false;
//								$this->Usuario->validationErrors['login'] = 'registro.pendiente';
//								break;
							case 'B':
								$flag_siguiente = false;
								$this->Session->write('usuarios.registro',true);
								$this->redirect('registro_bloqueado');
								break;
							case 'I1':
							case 'I2':
								$flag_siguiente = false;
								$this->Usuario->validationErrors['login'] = 'usuario.oficina.comercial';
								break;

							// Registro incompleto
							case 'I':
								// En caso de que exista un master unico no puede registrarse un usuario:
								// operador, aprobador, aprobador/operador
								if($this->Session->check('login.juridico') && $login['Usuario']['rol_juridico_id']>2){

									// Verifica si ya existe un master unico
									$unico = $this->UsuarioCanal->encontrarUsuario(
										array(
											'rol_juridico_id'=>2,
											'tipo_rif'=>$login['Usuario']['tipo_rif'],
											'rif'=>$login['Usuario']['rif'],
										)
									);
									if($unico['Usuario']){
										$flag_siguiente = false;
										$this->Usuario->validationErrors['login'] = _m('usuario.bloqueo.acceso');
									}
								}
								break;
						}
					}

					$login = $login['Usuario'];
					
					if($flag_siguiente){
						// Verifica el estatus del usuario
						switch (strtoupper($login['estatus_acceso'])){
							case 'P': // por activar usuario juridico creado
							case 'A':
								$login['url_volver']='login';
								$this->Session->write('usuarios.login', $login);
								$this->redirect('contrasena');
								break;
							case 'BC':
							case 'DS':
								
								// Verifica si esta desde un dispositivo movil
								if(esMovil()){
									$this->Usuario->validationErrors['login'] = _m('movil.bloqueo.acceso');
									break;
								}
								
								// Verifica si es un usuario juridico no master
								if( !empty($login['tipo_rif']) && $login['rol_juridico_id']>2 ){
									$this->Usuario->validationErrors['login'] = _m('usuario.bloqueo.acceso');
								}
								else{
									if( !empty($login['tipo_rif']) ){ // PARA JURIDICO
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
											'datasource'=>$login,
											'mensaje'=>'usuario.bloqueo.clave'
										);
									}
									else { // PARA NATURAL
										$estado = array(
											'titulo' => 'Usuario Bloqueado',
											'paso_codigo'=>0,
                                                                                        'paso_autenticacion'=>1,
											'paso_desafio'=>2,
											'paso_clave'=>3,
											'pasos'=>array(
                                                                                                'Código de Autorización',
												'Preguntas de Autenticación',
												'Preguntas de Desafío',
												'Cambio de Clave'
											),
											'tipo'=>'clave',
											'cedula.validada'=>true,
											'datasource'=>$login,
											'mensaje'=>'usuario.bloqueo.clave'
										);
									}
									
									$this->Session->write('seguridad.olvido', $estado);
									$this->redirect('bloqueo_usuario');
								}
								break;
                                                        case 'IN'://Bloqueo por inactividad
								
								// Verifica si esta desde un dispositivo movil
								if(esMovil()){
									$this->Usuario->validationErrors['login'] = _m('movil.bloqueo.acceso');
									break;
								}
								
								// Verifica si es un usuario juridico no master
								if( !empty($login['tipo_rif']) && $login['rol_juridico_id']>2 ){
									$this->Usuario->validationErrors['login'] = _m('bloqueo.juridico.inactividad');
								}
								else{
									if( !empty($login['tipo_rif']) ){ // PARA JURIDICO
										$estado = array(
											'titulo' => 'Usuario Bloqueado',
											'paso_desafio'=>0,											
                                                                                        'paso_resultado'=>1,
											'pasos'=>array(
												'Preguntas de Desafío',												
                                                                                                'Resultado'
											),
											'tipo'=>'clave',
											'cedula.validada'=>true,
											'datasource'=>$login,
											'mensaje'=>'usuario.bloqueo.inactividad'
										);
									}
									else { // PARA NATURAL
										$estado = array(
											'titulo' => 'Usuario Bloqueado',
											'paso_autenticacion'=>0,
											'paso_desafio'=>1,
                                                                                        'paso_resultado'=>2,
											'pasos'=>array(
												'Preguntas de Autenticación',
												'Preguntas de Desafío',
                                                                                                'Resultado'											
											),
											'tipo'=>'clave',
											'cedula.validada'=>true,
											'datasource'=>$login,
											'mensaje'=>'usuario.bloqueo.inactividad'
										);
									}
									
									$this->Session->write('seguridad.olvido', $estado);
									$this->redirect('bloqueo_inactividad');
								}
								break;        
							case 'BA':
							case 'BD':
							case 'BI':
							case 'BS':
							case 'I':
								if( !empty($login['tipo_rif']) && $login['rol_juridico_id']>2 ){
									// operador, aprobador, operador/aprobador
									$this->Usuario->validationErrors['login'] = _m('bloqueo.juridico.creado');
								}
								elseif($login['perfil_ib_id']==2){
									// master/unico
									$this->Usuario->validationErrors['login'] = _m('master.bloqueado');
								}else{
									//natural
									$this->Usuario->validationErrors['login'] = 'usuario.callcenter';
								}
								break;
								
							case 'I1':
							case 'I2':
								// si es un usuario juridico operador/aprobador/operador-aprobador
								if( $login['perfil_ib_id']==2 && $login['rol_juridico_id']>2 ){
									$this->Usuario->validationErrors['login'] = _m('bloqueo.juridico.creado');
								}
								else{
									$this->Usuario->validationErrors['login'] = 'usuario.oficina.comercial';
								}
								break;
							
							case 'DD':
								$this->Usuario->validationErrors['login'] = _m('usuario.desafiliado');
								break;
							
							case 'ST':
								$this->Usuario->validationErrors['login'] = _m('suspension.temporal.usuario');
								break;
							
							default :
								$this->Usuario->validationErrors['login'] = 'usuario.error';
								break;
						}
						
						/** Para verificar si es un usuario juridico o natural **/
						if(!empty($login['tipo_rif'])){
							$identificacion = $login['tipo_rif'].desencriptar($login['rif']).$login['tipo_cedula'].desencriptar($login['cedula']).desencriptar($login['login']);
						} else{
							$identificacion = $login['tipo_cedula'].desencriptar($login['cedula']);
						}
						/** Para verificar si es un usuario juridico o natural **/
						
						$this->Idg->preAutenticar($identificacion);
						
						// Verificar el estado del usuario en IDG
						// Verifica si esta desde un dispositivo movil
						if(isset($this->Idg->codigo_error) 
							&& !esMovil()
						){
							switch($this->Idg->codigo_error){
								case 'USER_LOCKED':
									$this->Bitacora->registraBitacora(
										$login['id'],
										100002
									);

									/** Para verificar si es un usuario juridico no master **/
									if(!empty($login['tipo_rif']) && $login['rol_juridico_id']>2){
										$this->UsuarioCanal->cambioEstatus(
											'acceso',
											'BC',
											$login['id']
										);
										
										$this->Usuario->validationErrors['login'] = _m('usuario.bloqueo.acceso');
									}
									else{
										$this->UsuarioCanal->cambioEstatus(
											'acceso',
											'BA',
											$login['id']
										);
										
										$this->Usuario->validationErrors['login'] = 'usuario.callcenter';
									}
									break;
									
									
								case 'AUTH_FAILED_USER_LOCKED':
									// Verifica si es un usuario juridico no master
									if( !empty($login['tipo_rif']) && $login['rol_juridico_id']>2 ){
										$this->redirect('bloqueo_juridico');
									}
									else{
										if( !empty($login['tipo_rif']) ){ // PARA JURIDICO
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
												'datasource'=>$login,
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
												'datasource'=>$login,
												'mensaje'=>'usuario.bloqueo.clave'
											);
										}

										$this->Session->write('seguridad.olvido', $estado);
										$this->redirect('/seguridad/olvido_clave');
									}
									break;
							}
						}
					}
				}
			}
		}
		
		$banners = $this->Banners->porSeccion(2);
		
		$this->set(compact('banners'));
	}

	/**
	 * Acción que maneja el formulario que solicita la contraseña del usuario
	**/
	function contrasena(){
		
                $datos = $this->Session->read('usuarios.login');		               
                
                if (isset($datos['legacy']['Usuario']['tipoRif']) || isset($datos['tipo_rif'])){
                    $tipo_identificacion = 'J';
                }else{
                    $tipo_identificacion = null;
                }
                
		if( isset($datos['estatus_registro']) && $datos['estatus_registro'] == 'I' ) $datos['imagen_confirmada'] = true;

		if(empty($datos)){
			$this->redirect('login');
		} else if(!empty($this->data['Usuario'])){
			// El usuario confirmó la imágen
			$datos['imagen_confirmada'] = true;
			$this->Session->write('usuarios.login',$datos);
			
			$this->Usuario->set($this->data);
			
			if(isset($datos['legacy'])){
				// El usuario viene del ib anterior
				// Cambiar la validación de la contraseña
				
				$this->Usuario->validate['contrasenaL'] = array(
					'minLength'=>array(
						'rule'=>array('minLength',1),
					),
					'maxLength'=>array(
						'rule'=>array('maxLength',10),
					)
				);				
			}			

			if ($this->Usuario->validates(array('fieldList' => array('contrasenaL'))) ||
				!$this->Usuario->validates(array('fieldList' => array('contrasenaL')))) {
				// La contraseña tiene formato correcto
			
				if(isset($datos['legacy'])){
					// El usuario es del ib anterior
					// chequear la contraseña contra sel servicio del 400
					// en caso de que sea un usuario natural o un juridico master
					if(!$this->Session->check('login.juridico') || isset($datos['legacy']['Usuario']['perfil']) ){
					
						if(Configure::read('desarrollo')){
							$res['PWDDSJVReturn']['PWDDSJV']['PWDSTS'] = 'X';
						}
						else{
							
							if($this->Session->check('login.juridico')){ // PARA JURIDICO
								$request = array(
									'PWDUSR'=>substr($datos['identificacion'],0,10),
									'PWDPWD'=>$this->data['Usuario']['contrasenaL'],
									'PWDLGT'=>1,
									'PWDCMP'=>$datos['legacy']['Usuario']['tipoRif'].$datos['legacy']['Usuario']['nroRif']
								);
							}
							else{ // PARA NATURAL
								$request = array(
									'PWDUSR'=>substr($datos['identificacion'],0,10),
									'PWDPWD'=>$this->data['Usuario']['contrasenaL'],
									'PWDLGT'=>2
								);
							}
							
							$res = $this->Activo->invoca(
								'PWDDSJV',
								array(
									'req'=> $request
								)
							);
						}

						if($res){
							// El servicio respondió correctamente

							switch($res['PWDDSJVReturn']['PWDDSJV']['PWDSTS']){
								case '1':
								case '6':
									$this->Usuario->validationErrors['contrasenaL']=_m('LOGIN_USER_INV_USER');			
									break;
								case '2':
									$this->Usuario->validationErrors['contrasenaL']=_m('LOGIN_USER_INV_PASSWORD');			
									break;
								case '3':
									$this->Usuario->validationErrors['contrasenaL']=_m('LOGIN_USER_INACTIVE');			
									break;
								case '4':
									$this->Usuario->validationErrors['contrasenaL']=_m('LOGIN_USER_SUSP');			
									break;
								case '9':
								case '5':
									$this->Usuario->validationErrors['contrasenaL']=_m('LOGIN_USER_PENDING');			
									break;
								default:
									// La contraseña es correcta
									// Continuar con el proceso de registro

									$registro = array(
										'legacy'=>true
									);							

									// Si es juridico
									if($this->Session->check('login.juridico')){
										$this->Session->write('seguridad.validar_preguntas_autenticacion',array('validado'=>true));
										$datos['legacy']['Usuario']['tdd_validada'] = true;
										
										// Para obtener los datos del MASTER
										$datos_basicos = $this->Activo->invoca(
											'NIBPF3010',
											array(
												'req'=>array(
													'NACIONALIDAD'=>$datos['legacy']['Usuario']['tipoRif'],
													'NROIDENTIFICACION'=>str_pad($datos['legacy']['Usuario']['nroRif'],12,'0',STR_PAD_LEFT),
													'NROPARAM'=>'1'
												)
											)
										);
										
										if($datos_basicos){
											if(
												isset($datos_basicos['NIBPF3010Return']['error'])
											){
												$estado['legacy']['Usuario']['rif_valido'] = false;
												$this->Usuario->validationErrors['contrasenaL'] = _m('rif.no.existe');
											} else {
												$datos['legacy']['DatosBasicos'] = $datos_basicos['NIBPF3010Return']['NIBPF3010'];
												$datos['legacy']['Usuario']['razon'] = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
												$datos['legacy']['Usuario']['nombre'] = '';
												$datos['legacy']['Usuario']['cargo'] = '';
												$datos['legacy']['Usuario']['emailPri'] = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];
												
												$this->Session->write('usuarios.registro', $datos['legacy']);
												$this->redirect('registro');
											}
										}
									}
									else{
										$this->Session->write('usuarios.registro', $registro);
										$this->redirect('registro');
									}
									
									break;
							}
						}

					}
					else{ // PARA UN JURIDICO OPERADOR-APROBADOR-OPERADOR/APROBADOR
						                                            					    
						// Autentica a un usuario juridico en la BD
						$dataTemp = $this->MigracionJuridico->autenticarUsuario(
							$datos['legacy']['Usuario']['tipoRif'].$datos['legacy']['Usuario']['nroRif'],
							$datos['identificacion'],
							$this->data['Usuario']['contrasenaL'],
							$datos['legacy']
						);
					
						if(!$dataTemp){
							$this->Usuario->validationErrors['contrasenaL']=_m('LOGIN_USER_INV_PASSWORD');
						}
						else{							
                                                        // Valida que el RIF este activo en el maestro de clientes
                                                        $datos_basicos = $this->Activo->invoca(
                                                            'NIBPF3010',
                                                            array('req'=>array(
									'NACIONALIDAD'=>$datos['legacy']['Usuario']['tipoRif'],
									'NROIDENTIFICACION'=>str_pad($datos['legacy']['Usuario']['nroRif'],12,'0',STR_PAD_LEFT),
									'NROPARAM'=>'1'
                                                                 )
                                                            )
                                                        );
                                            
                                                        if(isset($datos_basicos['NIBPF3010Return']['error']))
                                                        {
                                                            $estado['legacy']['Usuario']['rif_valido'] = false;
                                                            $this->Usuario->validationErrors['contrasenaL'] = _m('rif.master.no.existe');
                                                        }else{        
                                                            
                                                            $this->Session->write('seguridad.validar_preguntas_autenticacion',array('validado'=>true));
                                                            $datos['legacy']['transacciones'] = Configure::read('transacciones_basicas.rol.default');
							
                                                            $this->Session->write('usuarios.registro', array_merge($datos['legacy'],$dataTemp));
                                                            $this->redirect('completar_juridico');
                                                        }
					        }   
					}
				} else {
					// El usuario es del ib nuevo
					// validar la contraseña contra el idg

					/** Para verificar si es un usuario juridico o natural **/
					if(!empty($datos['tipo_rif'])){
						$identificacion = $datos['tipo_rif'].desencriptar($datos['rif']).$datos['tipo_cedula'].desencriptar($datos['cedula']).desencriptar($datos['login']);
					} else{
						$identificacion = $datos['tipo_cedula'].desencriptar($datos['cedula']);
					}
					/** Para verificar si es un usuario juridico o natural **/
				
					
					if(Configure::read('desarrollo')){
						$res['PasswordInfo']['ExpiryDate'] = '31 January 2240';
					} else {
						$res = $this->Idg->autenticar(
							$identificacion,
							$this->data['Usuario']['contrasenaL']
						);
					}
					
					if(
						$res ||
						(
							isset($this->Idg->codigo_error) && 
							$this->Idg->codigo_error=='PASSWORD_CHANGE_REQUIRED'
						)
					){
                                                // Revisar el estatus de registro en caso de que sea Juridico y esta pendiente
                                                if($datos['estatus_registro']=='P' && !empty($datos['tipo_rif'])){
                                                    
                                                    $this->redirect('estatus_pendiente_planilla');
                                                }
                                               
						// Revisar el estatus de registro, para ver si está incompleto
						if($datos['estatus_registro']=='I'){
							
							// Verifica si es un usuario juridico o natural
							if( !empty($datos['tipo_rif']) ){
								$tipo_documento = $datos['tipo_rif'];
								$nro_documento = $datos['rif'];
							}
							else{
								$tipo_documento = $datos['tipo_cedula'];
								$nro_documento = $datos['cedula'];
							}
							$nro_doc = $tipo_documento . str_pad(desencriptar($nro_documento),9,'0',STR_PAD_LEFT);
							
							$dataRest = array("SUMUSR" => $nro_doc,"CANAL" => "4");


							$token_resp = $this->Activo->invocaToken(Configure::read('Token.url'),"POST",$dataRest);

							$resp_all = get_object_vars($token_resp);

							$token_a = $resp_all['erds01'];

							$this->Session->write('token.token',$token_a);

							$token_aut = $this->Session->read('token.token');
							
							// El registro quedó incompleto
							// Redirigir al registro para terminar el proceso
							$res2 = $this->Activo->invoca(
								'NIBPF3010',
								array(
									'req'=>array(
										'NACIONALIDAD'=>$tipo_documento,
										'NROIDENTIFICACION'=>str_pad(desencriptar($nro_documento),12,'0',STR_PAD_LEFT),
										'NROPARAM'=>'1'
									)
								),
								$token_a
							);
							
							if($res2){
								if(
									isset($res2['NIBPF3010Return']['error'])
								){
									$flag_siguiente = false;
									$this->Usuario->validationErrors['contrasenaL']=_m('usuario.login.notfound');
								} else {

									// Verifica si es un usuario juridico o natural
									if( !empty($datos['tipo_rif']) ){
										
										// En caso de ser un usuario master o master unico anula la TDC
										if($datos['rol_juridico_id']<=2) $this->Idg->anularTdc($datos['tipo_rif'].desencriptar($datos['rif']).$datos['tipo_cedula'].desencriptar($datos['cedula']));
										
										$estado = array(
											'Usuario'=>array(
												'tipoRif'=>$datos['tipo_rif'],
												'nroRif'=>desencriptar($datos['rif']),
												'tipoCedulaRif'=>$datos['tipo_cedula'],
												'nroCedulaRif'=>desencriptar($datos['cedula']),
												'id'=>$datos['id'],
												'estatus_registro'=>$datos['estatus_registro'],
												'nomUsuario'=>desencriptar($datos['login']),
												'cedula_valida'=>true,
												'tiene_tdd'=>strlen(trim($res2['NIBPF3010Return']['NIBPF3010']['TDD']))>0,
												'usuario_creado'=>true,
												'perfil'=>$datos['rol_juridico_id'],
												'registro_incompleto'=>true,
												'codigoCelular'=>substr(desencriptar($datos['celular']), 0, 4),
												'nroCelular'=>substr(desencriptar($datos['celular']), 4),
												'emailPri'=>desencriptar($datos['email_principal']),
												'cargo'=>desencriptar($datos['cargo']),
												'nombre'=>desencriptar($datos['nombre']),
												'ibuser'=>$datos['ibuser'],
											),
											'DatosBasicos'=>$res2['NIBPF3010Return']['NIBPF3010']
										);
									}
									else{
										$estado = array(
											'Usuario'=>array(
												'tipoCedulaRif'=>$datos['tipo_cedula'],
												'nroCedulaRif'=>desencriptar($datos['cedula']),
												'id'=>$datos['id'],
												'estatus_registro'=>$datos['estatus_registro'],
												'nomUsuario'=>desencriptar($datos['login']),
												'cedula_valida'=>true,
												'tiene_tdd'=>strlen(trim($res2['NIBPF3010Return']['NIBPF3010']['TDD']))>0,
												'usuario_creado'=>true,
												'registro_incompleto'=>true,
												'codigoCelular'=>substr(desencriptar($datos['celular']), 0, 4),
												'nroCelular'=>substr(desencriptar($datos['celular']), 4),
											),
											'DatosBasicos'=>$res2['NIBPF3010Return']['NIBPF3010']
										);
									}
									
									$this->Session->write('usuarios.registro',$estado);
									
									if(!empty($datos['rol_juridico_id']) && $datos['rol_juridico_id']>2){
										$this->redirect('completar_juridico');
									}
									else{
										$this->redirect('usuario_creado');
									}
								}
							}
						} else {
							
							// Verifica si es un dispositivo movil para indicar el canal
							if(esMovil()){
								$canal_id = Configure::read('canal.movil');
							} else {
								$canal_id = Configure::read('canal.ib');
							}
							
							// Busca la ultima conexion del usuario
							$bitacora = $this->Bitacora->find(
								'first',
								array(
									'conditions'=>array(
										'usuario_id' =>$datos['id'],
										'transaccion_id'=>1,
										'canal'=>$canal_id,
									),
									'fields'=>array('fecha_hora', 'ip', 'user_agent'),
									'order' => array('fecha_hora DESC')
								)
							);
							
							// Busca parametro de inicio de sesion y fecha de ultima interaccion
							$logueado = $this->UsuarioCanal->find(
								'first',
								array(
									'fields'=>array('inicio_sesion','fecha_hora_ultima_interaccion'),
									'conditions'=> array('usuario_id'=>$datos['id'],'canal_id'=>Configure::read('canal.ib'))
								)
							);
							
							// verifica si ya hay un inicio de sesion
							if(
								!Configure::read('desarrollo') &&
								$logueado['UsuarioCanal']['inicio_sesion'] == 1
							){
								
								$ip_usuario = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
								$ua_usuario = $_SERVER['HTTP_USER_AGENT'];
								
								$last_date = strtotime($logueado['UsuarioCanal']['fecha_hora_ultima_interaccion']);
								$actual = strtotime(date("Y-m-d H:i:s"));
								$tiempo = _p('tiempo_login');
								$minutos = ($actual-$last_date)/60;
								
								if( desencriptar($bitacora['Bitacora']['ip'])!=$ip_usuario && $minutos<$tiempo ){
									// se bloquea el usuario en caso de ser una ip diferente
								
									// Actualiza el campo de conexion
									$this->UsuarioCanal->changeLogged(0, $datos['id']);

									// Cambia el estatus de acceso
									$this->UsuarioCanal->cambioEstatus( 'acceso', 'DS', $datos['id']);
									
									// Registra el log
									$this->Bitacora->registraBitacora($datos['id'],45);
									
                                                                        //Envia SMS para notificar al cliente del bloqueo
                                                                        if(isset($datos['celular'])){                                                                                
                                                                                $this->Sms->enviar(
                                                                                        array(
                                                                                                'numcel'=>desencriptar($datos['celular']),
                                                                                                'canal1'=>'62',
                                                                                        )
                                                                                );
                                                                        }
									
									$this->Session->write('usuarios.doblesession',true);
									$this->redirect('session_bloqueada');
								}
								else if( desencriptar($bitacora['Bitacora']['ip'])==$ip_usuario && desencriptar($bitacora['Bitacora']['user_agent'])!=$ua_usuario && $minutos<$tiempo){
									// muestra un mensaje en caso de que la ip sea la misma, el navegador diferente y el tiempo de inactividad sea menor al tiempo de sesion
									$this->Session->write('usuarios.dobleacceso',true);
									$this->redirect('session_activa');
								}
							}
							
							if(!Configure::read('desarrollo')){
								// Actualiza los campos de conexion
								$this->UsuarioCanal->changeLogged(1, $datos['id']);
							}
							
							if(isset($res['PasswordInfo']['ExpiryDate'])){
								$expira = strtotime($res['PasswordInfo']['ExpiryDate']);
							}


							$expira=time();
							
							if(
								(
								isset($expira) &&
								$expira < time()
								)
								||
								(
									isset($this->Idg->codigo_error) && 
									$this->Idg->codigo_error=='PASSWORD_CHANGE_REQUIRED'
								)
							){
								$this->Session->write('usuarios.clave.vencida',true);
							}
							
                                                        //Sello Vencido
                                                        //<mguglielmelli@bancoactivo.com>
                                                        $fecha_vencimiento_sello = $this->Usuario->find(
                                                                'first',
                                                                array(
                                                                        'fields'=>'DATEDIFF (year,fecha_sello_id,getdate()) as tiempo',
                                                                        'conditions'=> array('id'=>$datos['id'])
                                                                     )					
                                                        );
                                                        
                                                        if($fecha_vencimiento_sello[0]['tiempo']>=1){
								$this->Session->write('usuarios.sello.vencido',true);                                                                
							}
							
                                                        // ***** Para poder agregar el numero de cliente en la variable de sesion usuarios.datos
                                                        // Agregado el 01/09/2014
                                                        // Verifica si es un usuario juridico o natural
							if( !empty($datos['tipo_rif']) ){
								$tipo_documento = $datos['tipo_rif'];
								$nro_documento = $datos['rif'];
							}
							else{
								$tipo_documento = $datos['tipo_cedula'];
								$nro_documento = $datos['cedula'];
							}
							
							$nro_doc = $tipo_documento . str_pad(desencriptar($nro_documento),9,'0',STR_PAD_LEFT);
							
							$dataRest = array("SUMUSR" => $nro_doc,"CANAL" => "4");


							$token_resp = $this->Activo->invocaToken(Configure::read('Token.url'),"POST",$dataRest);

							$resp_all = get_object_vars($token_resp);

							$token_a = $resp_all['erds01'];

							$this->Session->write('token.token',$token_a);

							$token_aut = $this->Session->read('token.token');
							
                            // Buscamos el numero de cliente y otra informacion del IBS
                            $usrIbsDatos = $this->Activo->invoca(
								'NIBPF3010',
								array(
									'req'=>array(
										'NACIONALIDAD'=>$tipo_documento,
										'NROIDENTIFICACION'=>str_pad(desencriptar($nro_documento),12,'0',STR_PAD_LEFT),
										'NROPARAM'=>'1'
									)

								),
								$token_a
							);
                                                        
                            if(isset($usrIbsDatos))
                            	$datos['nrocliente'] = $usrIbsDatos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'];
                                                        
                            // Fin de agregar el numero de cliente en la variuable de sesion usuarios.datos
                                                        
							$this->Session->write('usuarios.lastlogged', (!empty($bitacora['Bitacora']['fecha_hora'])) ? date('d/m/Y h:ia', strtotime($bitacora['Bitacora']['fecha_hora'])) : null );
							$this->Session->write('usuarios.loggedin', true);
							$this->Session->write('usuarios.datos', $datos);
							
							// Registra el log
							$this->Bitacora->registraBitacora($datos['id'],1);
							
							// Transacciones del perfil del usuario
							if( empty($datos['rol_juridico_id']) ){
								$t = $this->TransaccionPerfil->obtenerTransPerfil($datos['perfil_ib_id'], $datos['id']);
							}
							else{
								$t = $this->TransaccionJuridico->obtenerTransUsuario($datos['id']);
							}
							$this->Session->write('usuarios.transacciones', $t);
							
							/* Para validar si ve la pizarra de aprobaciones */
							if(in_array(36, $t)){
								$pendientes = $this->TransaccionPendiente->obtenerAprobacionesPendientes($datos);

								if(count($pendientes)>0){
									$this->redirect(array('controller'=>'transacciones_pendientes','action'=>'index'));
								} else {
                                                                    $usuario = $this->Session->read('usuarios.datos');
                                                                    /** Para identificar si es un usuario juridico **/
                                                                    if(isset($usuario['tipo_rif']) && isset($usuario['rif'])){
                                                                            $identificacion=$usuario['tipo_rif'].desencriptar($usuario['rif']);
                                                                            $loginUsuario = desencriptar($usuario['login']);
                                                                            $idUsuario = $usuario['id'];
                                                                    } else {
                                                                            $identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                                                                            $loginUsuario = desencriptar($usuario['login']);
                                                                            $idUsuario = $usuario['id'];
                                                                    }
                                                                    /** Para identificar si es un usuario juridico **/
                                                                    
                                                                    if((!isset($usuario['tipo_rif']) && !isset($usuario['rif'])) || (in_array($usuario['rol_juridico_id'],array(1,2)))){
                                                                        //Verificamos si el usuario debe aceptar el nuevo contrato
                                                                        if($this->Usuario->validar_nuevo_contrato($idUsuario)){
                                                                            $this->redirect('/otros_servicios/aviso_nuevo_contrato');
                                                                        }  
                                                                    }
                                                                    
                                                                    if(_p('proceso_auditoria')){
								
                                                                        $datos_cliente = $this->Session->read('usuarios.nibpf3010');
                                                                        $id_cliente_IBS = intval($datos_cliente['NIBPF3010Return']['NIBPF3010']['NROCLIENTE']);

                                                                        $cedula = desencriptar($usuario['cedula']);
                                                                        $rif = desencriptar($usuario['rif']);

                                                                        $verificarVoto = $this->Activo->verificarVoto($id_cliente_IBS, $identificacion, $loginUsuario);
                                                                        $opcion = trim($verificarVoto['NIB2090PFReturn']['NIB2090PF_2']['AUDCDE']);

                                                                        if($opcion == '03'){
                                                                                $this->redirect(array('controller'=>'auditores_externos','action'=>'auditores_externos'));
                                                                        }else{
                                                                                $this->redirect(array('controller'=>'consultas','action'=>'index'));
                                                                        }
                                                                    }else{
                                                                        $this->redirect(array('controller'=>'consultas','action'=>'index'));
                                                                        
                                                                    }//Fin auditoría externa
								}
							}
							else{
							
                                                            //verificamos si el usuario voto o no en AS400
                                                            //se verifica si está activo el proceso de votación
								
                                                            $usuario = $this->Session->read('usuarios.datos');
                                                            /** Para identificar si es un usuario juridico **/
                                                            if(isset($usuario['tipo_rif']) && isset($usuario['rif'])){
                                                                    $identificacion=$usuario['tipo_rif'].desencriptar($usuario['rif']);
                                                                    $loginUsuario = desencriptar($usuario['login']);
                                                                    $idUsuario = $usuario['id'];
                                                            } else {
                                                                    $identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                                                                    $loginUsuario = desencriptar($usuario['login']);
                                                                    $idUsuario = $usuario['id'];
                                                            }
                                                            /** Para identificar si es un usuario juridico **/
                                                            
                                                            if((!isset($usuario['tipo_rif']) && !isset($usuario['rif'])) || (in_array($usuario['rol_juridico_id'],array(1,2)))){
                                                                //validamos si el usuario debe acpetar el nuevo contrato
                                                                if($this->Usuario->validar_nuevo_contrato($idUsuario)){
                                                                    $this->redirect('/otros_servicios/aviso_nuevo_contrato');
                                                                }
                                                            }
                                                            
                                                            if(_p('proceso_auditoria')){
								
                                                                $datos_cliente = $this->Session->read('usuarios.nibpf3010');
                                                                $id_cliente_IBS = intval($datos_cliente['NIBPF3010Return']['NIBPF3010']['NROCLIENTE']);

                                                                $cedula = desencriptar($usuario['cedula']);
                                                                $rif = desencriptar($usuario['rif']);

                                                                $verificarVoto = $this->Activo->verificarVoto($id_cliente_IBS, $identificacion, $loginUsuario);
                                                                $opcion = trim($verificarVoto['NIB2090PFReturn']['NIB2090PF_2']['AUDCDE']);

                                                                if($opcion == '03'){
                                                                        $this->redirect(array('controller'=>'auditores_externos','action'=>'auditores_externos'));
                                                                }else{
                                                                        $this->redirect(array('controller'=>'consultas','action'=>'index'));
                                                                }
                                                            }else{
                                                                    
                                                                $this->redirect(array('controller'=>'consultas','action'=>'index'));
                                                                   
                                                            }//Fin auditoría externa
							}
						}
					} else {
						switch($this->Idg->codigo_error){
							case 'USER_LOCKED':
                                                                $this->Bitacora->registraBitacora(
									$datos['id'],
									100001
								);
					
								$this->UsuarioCanal->cambioEstatus(
									'acceso',
									'BC',
									$datos['id']
								);
								
								// Verifica si es un usuario juridico no master
								if( !empty($datos['tipo_rif']) && $datos['rol_juridico_id']>2 ){
									$this->redirect('bloqueo_juridico');
								}
								else{
									
									if( !empty($datos['tipo_rif']) ){ // PARA JURIDICO
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
											'datasource'=>$datos,
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
											'datasource'=>$datos,
											'mensaje'=>'usuario.bloqueo.clave'
										);
									}

									$this->Session->write('seguridad.olvido', $estado);
									$this->redirect('bloqueo_usuario');
								}
                                                                
								break;
							case 'AUTH_FAILED_USER_LOCKED':
								$this->Bitacora->registraBitacora(
									$datos['id'],
									100001
								);
					
								$this->UsuarioCanal->cambioEstatus(
									'acceso',
									'BC',
									$datos['id']
								);
								
                                                                //<mguglielmelli@bancoactivo.com>
                                                                //Envio de SMS y Correo al cliente
                                                                $tipo_cambio = 'Bloqueo de Usuario por Intentos Fallidos';                                                                                                                              
                                                                $nombre_destino = desencriptar($datos['nombre']);
                                                                $email = desencriptar($datos['email_principal']);
                                                                $celular = desencriptar($datos['celular']);                                                                

                                                                $this->set(compact('nombre_destino', 'tipo_cambio'));

                                                                // Email Options
                                                                $this->Email->smtpOptions = Configure::read('email.smtp');
                                                                $this->Email->delivery = 'smtp';
                                                                $this->Email->from = Configure::read('email.from');
                                                                $this->Email->to = $email;
                                                                $this->Email->subject = 'Bloqueo de Usuario por Intentos Fallidos';
                                                                $this->Email->sendAs = 'html';

                                                                // Informacion del correo
                                                                $this->Email->template = 'bloqueo_usuario';                                        

                                                                // Envio del correo
                                                                $this->Email->send();

                                                                $this->Sms->enviar(
                                                                    array(
                                                                        'numcel'=>$celular,
                                                                        'canal1'=>'66',
                                                                        'fectim'=>'Bloqueo de Usuario por Intentos Fallidos',
                                                                        'xrnde38'=>'IB'
                                                                    )
                                                                );
                                                                
								// Verifica si es un usuario juridico no master
								if( !empty($datos['tipo_rif']) && $datos['rol_juridico_id']>2 ){
									$this->redirect('bloqueo_juridico');
								}
								else{
									
									if( !empty($datos['tipo_rif']) ){ // PARA JURIDICO
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
											'datasource'=>$datos,
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
											'datasource'=>$datos,
											'mensaje'=>'usuario.bloqueo.clave'
										);
									}

									$this->Session->write('seguridad.olvido', $estado);
									$this->redirect('bloqueo_usuario');
								}
								break;
							default:
								$this->Usuario->validationErrors['contrasenaL']=$this->Idg->mensaje_error.'. ' . _m('mensaje.intento');
								$this->log('error validando contraseña del usuario '.$datos['login'],LOG_ERR);
								
								// Registra el log
								$this->Bitacora->registraBitacora($datos['id'],100063,"","","","","Inicio Sesion - Contraseña");
								
								break;
						}
					}
				}
			}
		}
		
		$this->data['Usuario']['contrasenaL']='';                
		$this->set('datos', $datos);
        $this->set('tipo_identificacion', $tipo_identificacion);
	}
	
	/**
	 * Acción para indicar al usuario que ha sido bloqueado por sesión múltiple
	**/
	function session_bloqueada(){
		if(!$this->Session->check('usuarios.doblesession')) $this->redirect('/');

		$this->Session->delete('usuarios');

		$this->layout = "externa";
	}
	
	/**
	 * Acción para indicar al usuario que ha sido bloqueado por sesión múltiple
	**/
	function session_activa(){
		if(!$this->Session->check('usuarios.dobleacceso')) $this->redirect('/');

		$this->Session->delete('usuarios');

		$this->layout = "externa";
	}
	
	/**
	 * Acción para finalizar sesión
	**/
	function logout(){
		$datos = $this->Session->read('usuarios.datos');
		if(!empty($datos)){
			// Actualiza los campos de conexion
			$this->UsuarioCanal->changeLogged(0, $datos['id']);
			
			// Registra el log
			$this->Bitacora->registraBitacora($datos['id'],2);
		}
		
		$this->Session->destroy();
		
		if($datos['tipo_rif']) $this->Session->write('login.juridico', 'J');
		
		$this->redirect(array('controller'=>'usuarios','action'=>'login'));
	}
	
	/**
	 * Acción para mostrar mensaje de fin de sesión
	**/
	function fin_sesion(){
		$datos = $this->Session->read('usuarios.datos');
		if(!empty($datos)){
			$this->layout = "externa";
		
			// Actualiza los campos de conexion
			$this->UsuarioCanal->changeLogged(0, $datos['id']);
			
			// Registra el log
			$this->Bitacora->registraBitacora($datos['id'],26);
		}
		
		$this->Session->destroy();
		
		if($datos['tipo_rif']) $this->Session->write('login.juridico', 'J');
	}

	/**
	 * Acción que muestra condiciones del registro
	**/
	function registro(){
		
                $this->layout = "externa";
                
                if(!$this->Session->check('usuarios.registro')) $this->redirect('/');
                
		if(!empty($this->data['Usuario'])){
			$this->Usuario->set($this->data);

			if ($this->Usuario->validates(array('fieldList' => array('decision')))) {
				if($this->Session->check('usuarios.registro')){
					$estado = $this->Session->read('usuarios.registro');
				} else {
					$estado = array();
				}
				
				$estado['Usuario']['decision'] = $this->data['Usuario']['decision'];
				$estado['Usuario']['acepto_contrato'] = true;
				$this->Session->write('usuarios.registro',$estado);
                                
				$this->redirect('registro_usuario');				
			}
		}
	}	

	/**
	 * Acción que maneja el formulario que solicita la cédula al usuario
	 * en el registro
	**/
	function autenticar_cedula(){

		$estado = $this->Session->read('usuarios.registro');				                               
                                
		if(
			isset($estado['Usuario']['tipoCedulaRif']) &&
			isset($estado['Usuario']['nroCedulaRif'])
		){
			$this->data['Usuario']['tipoCedulaRif'] = $estado['Usuario']['tipoCedulaRif'];
			$this->data['Usuario']['nroCedulaRif'] = $estado['Usuario']['nroCedulaRif'];
		}

		if(empty($this->data['Usuario'])){
			$this->data = $estado;			
		} else {
			$this->Usuario->set($this->data);			
			if($this->Session->check('login.juridico')){
				$campos = array(
					'tipoRif',
					'nroRif',
					'tipoCedulaRif',
					'nroCedulaRif'
				);
			} else {
				$campos = array(
					'tipoCedulaRif',
					'nroCedulaRif'
				);
			}

			if(
				$this->Usuario->validates(
					array(
						'fieldList' => $campos
					)
				)
			) {
				// Las validaciones para júridico no son las mismas que para naturales
				
				if($this->Session->check('login.juridico')){
					// Usuario jurídico
					
					$estado['Usuario']['tipoRif'] = $this->data['Usuario']['tipoRif'];
					$estado['Usuario']['nroRif'] = $this->data['Usuario']['nroRif'];
					$estado['Usuario']['tipoCedulaRif'] = $this->data['Usuario']['tipoCedulaRif'];
					$estado['Usuario']['nroCedulaRif'] = $this->data['Usuario']['nroCedulaRif'];
					
					/** Para la verificacion de los usuarios pilotos **/
					if(_p('piloto_jur')){
						if( !$this->PilotoUsuarios->buscar($estado['Usuario']['nroRif'],$estado['Usuario']['tipoRif'])){
							$this->redirect('/');
						}
					}
					/** Para la verificacion de los usuarios pilotos **/

					// Primero buscar la empresa
					$nro_doc = $estado['Usuario']['tipoRif'] . str_pad($estado['Usuario']['nroRif'],9,'0',STR_PAD_LEFT);
							
					$dataRest = array("SUMUSR" => $nro_doc,"CANAL" => "4");

					$token_resp = $this->Activo->invocaToken(Configure::read('Token.url'),"POST",$dataRest);

					$resp_all = get_object_vars($token_resp);

					$token_a = $resp_all['erds01'];

					$this->Session->write('token.token',$token_a);

					$token_aut = $this->Session->read('token.token');
					
					$res = $this->Activo->invoca(
						'NIBPF3010',
						array(
							'req'=>array(
								'NACIONALIDAD'=>$estado['Usuario']['tipoRif'],
								'NROIDENTIFICACION'=>str_pad($estado['Usuario']['nroRif'],12,'0',STR_PAD_LEFT),
								'NROPARAM'=>'1'
							)
						),
						$token_a
					);
						
					if($res){
						if(
							isset($res['NIBPF3010Return']['error'])
						){
							$estado['Usuario']['rif_valido'] = false;
							$this->Usuario->validationErrors['nroRif']=_m('rif.no.existe');

						} else   if((empty($res['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'])) ||  (!$this->validarEmail($res['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'])) || ($res['NIBPF3010Return']['NIBPF3010']['NROCELULAR']==0) || (strlen((int)($res['NIBPF3010Return']['NIBPF3010']['NROCELULAR']))<10)) {
							
		
							$this->Usuario->validationErrors['nroCedulaRif']=_m('dato.incompleto');
						}
						else {
							$estado['DatosBasicos']=$res['NIBPF3010Return']['NIBPF3010'];
							$estado['Usuario']['rif_valido'] = true;
							$estado['Usuario']['tiene_tdd'] = strlen(trim($estado['DatosBasicos']['TDD']))>0;
							
							// Verifica si existe un usuario master o master/unico para la empresa
							$usuario = $this->UsuarioCanal->encontrarUsuario(
								array(
									'Usuario.tipo_rif'=>$estado['Usuario']['tipoRif'],
									'Usuario.rif'=>encriptar($estado['Usuario']['nroRif']),
									'Usuario.rol_juridico_id' => array(1,2),
								)
							);
							
							// sino encontro el registro verifica el estatus
							if(!empty($usuario)){
								switch($usuario['Usuario']['estatus_registro']){
									case 'A':
									case 'I':
										$this->Usuario->validationErrors['nroCedulaRif']=_m('mater.ya.registrado');
										break;
									case 'P':
										$this->Usuario->validationErrors['nroCedulaRif']=_m('mater.ya.registrado');
										break;
									case 'B':
										$this->Session->write('usuarios.registro',$estado);
										$this->redirect('registro_bloqueado');
										break;		
									
									// desafiliacion definitiva
									case 'DD':
									
									default:
										$this->Session->write('seguridad.validar_preguntas_autenticacion',array('validado'=>true));
										$estado['Usuario']['tdd_validada'] = true;
										$this->Session->write('usuarios.registro',$estado);
										$this->redirect('registro');
										break;
								}
							} else {
								
								$eliminado = $this->Usuario->find(
									'first',
									array(
										'conditions'=> array(
											'Usuario.tipo_rif'=>$estado['Usuario']['tipoRif'],
											'Usuario.rif'=>encriptar($estado['Usuario']['nroRif']),
											'Usuario.rol_juridico_id' => array(1,2),
										)
									)
								);
								if(isset($eliminado['Usuario'])) $this->Usuario->validationErrors['login'] = 'usuario.notfound';
								
								
								// Verificar la fecha límite de registro de usuarios migrados
								if( time() <= strtotime(_p('fecha_limite_migrados_J'))
										&& !isset($this->Usuario->validationErrors['login'])
								){
									
									// Verifica si esta registrado en la banca actual
									$banca_actual = $this->Activo->invoca(
										'SUMDSJV',
										array( 'req'=>array('SUMUSR'=>$estado['Usuario']['tipoRif'].$estado['Usuario']['nroRif']) )
									);

									// En caso de estar registrado llena la sesion 
									// para el proceso de migración de la empresa.
									if($banca_actual && !isset($banca_actual['SUMDSJVReturn']['error'])){

										$estado['legacy'] = true;
										$estado['Usuario']['perfil'] = 1;
										$estado['Usuario']['razon'] = $res['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
										$estado['Usuario']['nombre'] = '';
										$estado['Usuario']['cargo'] = '';
										$estado['Usuario']['ibuser'] = encriptar($estado['Usuario']['tipoRif'].$estado['Usuario']['nroRif']);
										$estado['Usuario']['emailPri'] = $res['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];
										$estado['Usuario']['registroIB'] = 'P';

										// Busca el limite de otros bancos del usuario master
										$limite_master = $this->Activo->limiteMaster($estado['Usuario']['tipoRif'],$estado['Usuario']['nroRif']);
										if($limite_master) $estado['Usuario']['limite_master'] = $limite_master;
										
									}
								}
								
								// Los usuarios jurídicos no se autentican
								$this->Session->write('seguridad.validar_preguntas_autenticacion',array('validado'=>true));
								$estado['Usuario']['tdd_validada'] = true;
								$this->Session->write('usuarios.registro',$estado);
								$this->redirect('registro');								
							}
						}
					}
				} else {
					// El usuario es natural				
					$estado['Usuario']['tipoCedulaRif'] = $this->data['Usuario']['tipoCedulaRif'];
					$estado['Usuario']['nroCedulaRif'] = $this->data['Usuario']['nroCedulaRif'];
					$tipoId=$estado['Usuario']['tipoCedulaRif'];
					$nroId=ltrim($estado['Usuario']['nroCedulaRif'],0) ;
					
					/** Para la verificacion de los usuarios pilotos **/
					if(_p('piloto_nat')){
						if( !$this->PilotoUsuarios->buscar($estado['Usuario']['nroCedulaRif'],$estado['Usuario']['tipoCedulaRif'])){
							$this->redirect('/');
						}
					}
					/** Para la verificacion de los usuarios pilotos **/
					$nro_doc = $tipoId . str_pad($nroId,9,'0',STR_PAD_LEFT);
							
					$dataRest = array("SUMUSR" => $nro_doc,"CANAL" => "4");

					$token_resp = $this->Activo->invocaToken(Configure::read('Token.url'),"POST",$dataRest);

					$resp_all = get_object_vars($token_resp);

					$token_a = $resp_all['erds01'];

					$this->Session->write('token.token',$token_a);

					$token_aut = $this->Session->read('token.token');
					
					/** Para la busqueda del usuario en el servicio */
					$res = $this->Activo->invoca(
						'NIBPF3010',
						array(
							'req'=>array(
								'NACIONALIDAD'=>$tipoId,
								'NROIDENTIFICACION'=>str_pad($nroId,12,'0',STR_PAD_LEFT),
								'NROPARAM'=>'1'
							)
						),
						$token_a
					);
					if($res){
						
						// verifica si ocurre un error
						if( isset($res['NIBPF3010Return']['error']) ){
							$estado['Usuario']['cedula_valida'] = false;
							$this->Usuario->validationErrors['nroCedulaRif']=_m('cedula.no.existe');
						}else  if((empty($res['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'])) ||  (!$this->validarEmail($res['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'])) || ($res['NIBPF3010Return']['NIBPF3010']['NROCELULAR']==0) || (strlen((int)($res['NIBPF3010Return']['NIBPF3010']['NROCELULAR']))<10)) {
							
							
							$this->Usuario->validationErrors['nroCedulaRif']=_m('dato.incompleto');
						}
						else {
							// Verifica si ya existe el usuario en la BD
							$usuario = $this->UsuarioCanal->encontrarUsuario(
								array(
									'Usuario.tipo_cedula'=>$tipoId,
									'Usuario.cedula'=>encriptar($nroId),
									'Usuario.tipo_rif is null'
								)
							);
						
							if(!empty($usuario)){ // si lo encuentra verifica el estatus de registro		
								switch($usuario['Usuario']['estatus_registro']){
									case 'A':
									case 'I':
										$this->Usuario->validationErrors['nroCedulaRif']=_m('ya.registrado'); // se comento para muestra prototipo
										break;
									case 'B':
										$this->redirect('registro_bloqueado'); // se comento para muestra prototipo
										break;
									case 'I1':
										$this->Usuario->validationErrors['nroCedulaRif']=_m('autenticacion.b.2.22');
										break;
									case 'DD': // desafiliacion definitiva
									default: // Si esta vacio (registrado en IVR)

										$estado['DatosBasicos']=$res['NIBPF3010Return']['NIBPF3010'];
										$estado['Usuario']['cedula_valida'] = true;
										$estado['Usuario']['tiene_tdd'] = strlen(trim($estado['DatosBasicos']['TDD']))>0;
										$this->Session->write('usuarios.registro',$estado);
										$this->redirect('recordar_instrumentos');
										break;
								}
							} else {
								$estado['DatosBasicos']=$res['NIBPF3010Return']['NIBPF3010'];
								$estado['Usuario']['cedula_valida'] = true;
								$estado['Usuario']['tiene_tdd'] = strlen(trim($estado['DatosBasicos']['TDD']))>0;
								$this->Session->write('usuarios.registro',$estado);
								$this->redirect('recordar_instrumentos');
							}
						}
					}
				}
			}
		}

		$this->layout = "externa";
	}
	
	/**
	 * Acción para mostrar mensaje de registro bloqueado
	**/
	function registro_bloqueado(){
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		$estado = $this->Session->read('usuarios.registro');

		if(!$estado){
			$this->redirect('/');
		}

		$this->Session->delete('usuarios.registro');

		$this->layout = "externa";	
	}

	/**
	 * Acción que muestra mensaje de recordar instrumentos
	**/
	function recordar_instrumentos(){
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		$estado = $this->Session->read('usuarios.registro');

		if(
			!isset($estado['Usuario']['cedula_valida']) ||
			!$estado['Usuario']['cedula_valida']
		){
			$this->redirect('/');
		}

		if(empty($this->data['Usuario'])){
			$this->data = $estado;
		} else {
			$estado['Usuario']['instrumentos_recordados'] = true;

			$this->Session->write('usuarios.registro',$estado);

			if(isset($estado['legacy'])) {
				// El usuario viene del anterior ib, por lo tanto no hacer preguntas de autenticación
				$this->Session->write('seguridad.validar_preguntas_autenticacion',array('validado'=>true));

				$estado['Usuario']['tdd_validada'] = true;
				$this->Session->write('usuarios.registro',$estado);
			
				$this->redirect('registro');
			} else{
				// El usuario es nuevo, por lo tanto validar las preguntas de autenticación
				
				// Colocar data para el manejador de preguntas de seguridad
				$this->Session->write(
					'seguridad.validar_preguntas_autenticacion',
					array(
						'url_aceptar'=>'/usuarios/registro',
						'url_volver'=>'/usuarios/autenticar_cedula',
						'layout'=>'externa',
						'titulo'=>'Registro',
						'cantidad_preguntas_autenticacion'=>6,
						'pasos'=> array(							
							'Autenticación del Cliente',
                                                        'Aceptación de Contrato',
							'Registro de Usuario',
                                                        'Código de Autorización',
							'Configuración Imagen Antiphishing',
							'Configuración Preguntas Desafío',
							'Configuración de Límites'
						),
						'paso_actual'=>0
					)
				);
				
				// Redireccionar a las preguntas de seguridad
				$this->redirect('/seguridad/validar_preguntas_autenticacion');				
			}
		}

		$this->layout = "externa";
	}

	/**
	 * Acción que maneja el formulario que solicita la tarjeta de débito al usuario
	 * como factor de autenticación
	**/
	function autenticar_tarjeta(){
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		$estado = $this->Session->read('usuarios.registro');

		if(
			!isset($estado['Usuario']['instrumentos_recordados']) ||
			!$estado['Usuario']['instrumentos_recordados'] ||
			!isset($estado['Usuario']['tiene_tdd']) ||
			!$estado['Usuario']['tiene_tdd']
		){
			$this->redirect('/');
		}

		if(empty($this->data['Usuario'])){
			$this->data = $estado;
		} else {
			$this->Usuario->set($this->data);

			if ($this->Usuario->validates(array('fieldList' => array('nroTarjeta')))) {
				if(
					Configure::read('desarrollo') ||
					$this->data['Usuario']['nroTarjeta']==substr(trim($estado['DatosBasicos']['TDD']),-6)
				){
					$estado['Usuario']['tdd_validada'] = true;
					$this->Session->write('usuarios.registro',$estado);

					if(isset($estado['legacy'])){
						// El usuario viene del anterior ib, por lo tanto no hacer preguntas de autenticación
						$this->Session->write('seguridad.validar_preguntas_autenticacion',array('validado'=>true));
						$this->redirect('registro');				
					} else {
						// Colocar data para el manejador de preguntas de seguridad

						$this->Session->write(
							'seguridad.validar_preguntas_autenticacion',
							array(
								'url_aceptar'=>'/usuarios/registro',
								'url_volver'=>'/usuarios/autenticar_tarjeta',
								'layout'=>'externa',
								'titulo'=>'Registro',
								'cantidad_preguntas_autenticacion'=>6,
								'pasos'=> array(									
									'Autenticación del Cliente',
                                                                        'Aceptación de Contrato',
									'Registro de Usuario',
                                                                        'Código de Autorización',
									'Configuración Imagen Antiphishing',
									'Configuración Preguntas Desafío',
									'Configuración de Límites'
								),
								'paso_actual'=>0
							)
						);

						// Redireccionar a las preguntas de seguridad
						$this->redirect('/seguridad/validar_preguntas_autenticacion');					
					}
				} else {
					if(isset($estado['Usuario']['intento_validar_tdd'])){
						$estado['Usuario']['intento_validar_tdd'] = $estado['Usuario']['intento_validar_tdd']+1;
					} else {
						$estado['Usuario']['intento_validar_tdd'] = 1;
					}

					$estado['Usuario']['tdd_validada'] = false;

					$this->Session->write('usuarios.registro',$estado);

					if($estado['Usuario']['intento_validar_tdd']>=3){
					
						$idUser = $this->UsuarioCanal->bloquearRegistro(
							$estado['Usuario']['tipoCedulaRif'],
							$estado['Usuario']['nroCedulaRif']
						);
						
						if($idUser){
							$this->Bitacora->registraBitacora(
								$idUser,
								100007
							);				
						}
						
						$this->redirect('tdd_bloqueada');
					} else {
						$this->Usuario->validationErrors['nroTarjeta']=_m('tdd.invalida');
					}
				}
			}
		}

		$this->layout = "externa";
	}

	/**
	 * Acción que muestra mensaje de registro bloqueado por autenticación
	 * de tarjeta de débito fallida
	**/
	function tdd_bloqueada(){
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		$estado = $this->Session->read('usuarios.registro');

		if(
			!isset($estado['Usuario']['intento_validar_tdd']) ||
			$estado['Usuario']['intento_validar_tdd'] != 3
		){
			$this->redirect('/');
		}

		$this->Session->delete('usuarios.registro');

		$this->layout = "externa";	
	}
	
	/**
	 * Función invocada por AJAX que valida que el nombre de usuario
	 * seleccionado por el usuario esté disponible
	 * @param string $login 
	*/
	function verificar_usuario($login=null){
		$error = false;
		
		if(!isset($login) || empty($login)) {
			$error = true;
		}
		
		if($this->Session->check('usuarios.registro')){
			if(!$this->Session->check('seguridad.validar_preguntas_autenticacion')) {
				$error = true;
			}

			$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');

			if(
				!isset($estado_autenticacion['validado']) ||
				!$estado_autenticacion['validado']
			){
				$error = true;
			}

			if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

			$estado = $this->Session->read('usuarios.registro');

			if(
				isset($estado['Usuario']['tiene_tdd']) &&
				$estado['Usuario']['tiene_tdd'] &&
				(
					!isset($estado['Usuario']['tdd_validada']) ||
					!$estado['Usuario']['tdd_validada']
				)
			){
				$error = true;
			}
			
			if($this->Session->check('login.juridico')){
				$tipo_rif = $estado['Usuario']['tipoRif'];
				$rif = $estado['Usuario']['nroRif'];
			}
			else{
				$tipo_cedula_rif = $estado['Usuario']['tipoCedulaRif'];
				$cedula = $estado['Usuario']['nroCedulaRif'];
			}
						
		} else if($this->Session->check('usuarios.datos')) {
			$datos = $this->Session->read('usuarios.datos');
			
			if($this->Session->check('login.juridico')){
				$tipo_rif = $datos['tipo_rif'];
				$rif = desencriptar($datos['rif']);
			}
			
			$tipo_cedula_rif = $datos['tipo_cedula'];
			$cedula = desencriptar($datos['cedula']);
		} else {
			$this->redirect('/');
		}
		
		$valido = false;
		
		if(!$error){
			if($this->Session->check('login.juridico')){
				$usu=array(
					'tipoRif'=>$tipo_rif,
					'nroRif'=>$rif,
					'nomUsuario'=>$login
				);
			}else{
				$usu=array(
					'tipoCedulaRif'=>$tipo_cedula_rif,
					'nroCedulaRif'=>$cedula,
					'nomUsuario'=>$login
				);
			}
			$this->Usuario->set(array(
				'Usuario'=>$usu
			));
			
			if($this->Usuario->validates(array('fieldList'=>array('nomUsuario')))){
				$valido = true;
				$this->set('valido',true);
				$this->set('mensaje',_m('usuario.disponible'));
			} else {
				switch($this->Usuario->validationErrors['nomUsuario']){
					case 'nombre_usuario2':
						$this->set('mensaje',_m('usuario.no.permitido'));
						break;
					case 'login_unico':
						$this->set('mensaje',_m('usuario.no.disponible'));
						break;
					case 'nombre_usuario':
					default:
						$this->set('mensaje',_m('usuario.invalido'));
						break;
				}
			}
		} else {
			$this->set('mensaje',_m('error.fatal'));
		}
		
		if( $this->RequestHandler->isAjax()){
			$this->layout='ajax';
		} else if($error){
			return false;
		} else {
			return $valido;
		}
	}

	/**
	 * Acción para manejar el formulario de registro del usuario. Donde
	 * se solicitan sus datos principales
	**/
	function registro_usuario(){                                
                       
		if(!$this->Session->check('seguridad.validar_preguntas_autenticacion')) {
                    $this->redirect('/');
		}

		$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');

		if(
			!isset($estado_autenticacion['validado']) ||
			!$estado_autenticacion['validado']
		){
			$this->redirect('/');
		}

		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		$estado = $this->Session->read('usuarios.registro');
                $this->set('estado', $estado); 
                
                if(
                    !isset($estado['Usuario']['acepto_contrato']) ||
                    !$estado['Usuario']['acepto_contrato']
		){
                    $this->redirect('/');
		}                		
		
		// Si es un usuario juridico migrado
		if($this->Session->check('login.juridico') && isset($estado['legacy'])){

			// Busca el perfil
			$perfil = $this->RolJuridico->find(
				'first',
				array( 
					'field'=> 'nombre',
					'conditions'=> array('id'=>$estado['Usuario']['perfil']) 
				)
			);
			$this->set('perfil', $perfil['RolJuridico']['nombre']);
			$this->set('legacy', true);
			
			
			// verifica si ya ingreso la identificacion desde un paso anterior
			if(!isset($estado['Usuario']['tipoCedulaRif']) && !isset($estado['Usuario']['nroCedulaRif'])){
				$this->set('sin_identificacion', true);
			}
		}
		
		if(empty($this->data['Usuario'])){
			// Ver si el usuario ya se había intentado registrar quedando incompleto
                    
			if(isset($estado['Usuario']['id'])){
				// El usuario ya está creado, salta este paso y dirigir a configuración de parámetros
				$estado['Usuario']['usuario_creado']=true;

				$this->Session->write(
					'usuarios.registro',
					$estado
				);
				
				$this->redirect('usuario_creado');
			} else {
				// El usuario no está creado
				$this->data = $estado;
				
				// Colocar los valores por defecto de correo y teléfono que vienen de los
				// datos básicos del usuario
                                $this->data['Usuario']['nroCelular']=substr($estado['DatosBasicos']['NROCELULAR'],-7);
                                $this->data['Usuario']['codigoCelular']=substr($estado['DatosBasicos']['NROCELULAR'],0,4);
                                $this->data['Usuario']['emailPri']=$estado['DatosBasicos']['DISPONIBLE3'];                                        				
			}
		} else {
			if($this->Session->check('login.juridico')){
				$this->data['Usuario']['tipoRif'] = $estado['Usuario']['tipoRif'];
				$this->data['Usuario']['nroRif'] = $estado['Usuario']['nroRif'];
				
				// Si es un usuario juridico migrado
				if(isset($estado['legacy'])){
					if(!isset($estado['Usuario']['tipoCedulaRif'])) $estado['Usuario']['tipoCedulaRif'] = $this->data['Usuario']['tipoCedulaRif'];
					if(!isset($estado['Usuario']['nroCedulaRif'])) $estado['Usuario']['nroCedulaRif'] = $this->data['Usuario']['nroCedulaRif'];
					$this->data['Usuario']['perfil'] = $estado['Usuario']['perfil'];
					$this->data['Usuario']['razon'] = $estado['Usuario']['razon'];
				}
				
				if(isset($estado['Usuario']['tipoCedulaRif'])) $this->data['Usuario']['tipoCedulaRif'] = $estado['Usuario']['tipoCedulaRif'];
				if(isset($estado['Usuario']['nroCedulaRif'])) $this->data['Usuario']['nroCedulaRif'] = $estado['Usuario']['nroCedulaRif'];
			} else {
				$this->data['Usuario']['tipoCedulaRif'] = $estado['Usuario']['tipoCedulaRif'];
				$this->data['Usuario']['nroCedulaRif'] = $estado['Usuario']['nroCedulaRif'];
			}
			
			// Limpia los espacios en blanco de los correos
			$this->data['Usuario']['emailPri'] = trim($this->data['Usuario']['emailPri']);
			$this->data['Usuario']['emailSec'] = trim($this->data['Usuario']['emailSec']);			
			$this->data['Usuario']['nomUsuario'] = strtolower($this->data['Usuario']['nomUsuario']);
				
			$this->Usuario->set($this->data);
			
			$valido = true;
			
			if($this->Session->check('login.juridico')){
				if (!$this->Usuario->validates(array('fieldList' => array(
					'nombre',
					'nomUsuario',
					'emailPri',
					'emailSec',
					'nroCelular',
					'codigoCelular',
					'clave',
					'clave_confirmacion',
					'tiempoCaduca',
					'cargo',
					'tipoCedulaRif',
					'nroCedulaRif',
				)))) {
					$valido = false;
				}
			}else{
				if (!$this->Usuario->validates(array('fieldList' => array(
					'nomUsuario',
					'emailPri',
					'emailSec',
					'nroCelular',
					'codigoCelular',
					'clave',
					'clave_confirmacion',
					'tiempoCaduca'
				)))) {
					$valido = false;
				}
			}
			
			if($valido){
				$ds = $this->Usuario->getDataSource();
				
				$ds->begin($this->Usuario);	
				
				$usuario = array(
					'Usuario'=>array(
						'celular'=>encriptar($this->data['Usuario']['codigoCelular'].$this->data['Usuario']['nroCelular']),
						'login'=>encriptar($this->data['Usuario']['nomUsuario']),
						'email_principal'=>encriptar($this->data['Usuario']['emailPri']),
						'email_secundario'=>encriptar($this->data['Usuario']['emailSec']),
						'sello_id'=>NULL,
						'sello_1'=>NULL,
						'sello_2'=>NULL,
						'sello_3'=>NULL,
                        'nvo_contrato'=>1,
                        'fecha_hora_creacion'=>date('Y-m-d H:i:s'),//fecha de registro PN
					)
				);
				
				$usuario_canal = array(
					'UsuarioCanal'=>array(
						'canal_id'=>Configure::read('canal.ib'),
						'estatus_registro'=>'I',
						'estatus_acceso'=>'A',
						'limite_internas_terceros'=>NULL,
						'limite_externas'=>NULL,
                                                'fecha_creacion'=>date('Y-m-d H:i:s'),
					)
				);
				

				$usuario['Usuario']['tipo_cedula']=$estado['Usuario']['tipoCedulaRif'];
				$usuario['Usuario']['cedula']=encriptar($estado['Usuario']['nroCedulaRif']);
				
				if($this->Session->check('login.juridico')){
					$usuario['Usuario']['tipo_rif'] = $estado['Usuario']['tipoRif'];
					$usuario['Usuario']['rif'] = encriptar($estado['Usuario']['nroRif']);
					$usuario['Usuario']['nombre'] = encriptar($this->data['Usuario']['nombre']);
					$usuario['Usuario']['perfil_ib_id'] = 2;
					$usuario['Usuario']['rol_juridico_id'] = $this->data['Usuario']['perfil'];
					$usuario['Usuario']['cargo'] = encriptar($this->data['Usuario']['cargo']);
					$usuario['Usuario']['recibe_notificaciones'] = $this->data['Usuario']['recibe'];
					$usuario['Usuario']['fecha_hora_creacion'] = date('Y-m-d H:i:s');
					if(isset($estado['Usuario']['ibuser'])) $usuario['Usuario']['ibuser'] = $estado['Usuario']['ibuser'];
					
					// Verifica si es una desafiliacion definitiva
					// En caso de que lo sea actualiza los datos del usuario
					$udd = $this->UsuarioCanal->encontrarUsuario(
						array(
							'Usuario.tipo_rif'=>$usuario['Usuario']['tipo_rif'],
							'Usuario.rif'=>$usuario['Usuario']['rif'],
							'Usuario.rol_juridico_id'=>array(1,2),
							'UsuarioCanal.estatus_registro'=>'DD',							
						)
					);
					if($udd){
						$condiciones = array(
							'Usuario.tipo_rif'=>$usuario['Usuario']['tipo_rif'],
							'Usuario.rif'=>$usuario['Usuario']['rif'],
							'Usuario.id'=>$udd['Usuario']['id'],
						);
					}
					else{					
						$condiciones = array(
							'Usuario.tipo_rif'=>$usuario['Usuario']['tipo_rif'],
							'Usuario.rif'=>$usuario['Usuario']['rif'],
							'Usuario.tipo_cedula'=>$usuario['Usuario']['tipo_cedula'],
							'Usuario.cedula'=>$usuario['Usuario']['cedula'],
							'Usuario.login'=>$usuario['Usuario']['login']
						);
					}
					
				} else {
					$usuario['Usuario']['nombre'] = encriptar( implode(' ',array_map('trim',array($estado['DatosBasicos']['PRIMERNOMBRE'],$estado['DatosBasicos']['SEGUNDONOMBRE'],$estado['DatosBasicos']['PRIMERAPELLIDO'],$estado['DatosBasicos']['SEGUNDOAPELLIDO']))) );
					$usuario['Usuario']['tdd'] = encriptar($estado['DatosBasicos']['TDD']);
					
					$condiciones = array(
						'Usuario.tipo_cedula'=>$usuario['Usuario']['tipo_cedula'],
						'Usuario.cedula'=>$usuario['Usuario']['cedula'],
						'Usuario.tipo_rif is null'
					);
				}
				
				// Buscar el usuario si ya esta registrado en IVR
				// Tambien puede ser una desafiliacion definitiva
				$usuario_encontrado = $this->Usuario->find(
					'first',
					array(
						'conditions'=>$condiciones
					)
				);
				
				if($usuario_encontrado){
					$this->Usuario->id = $usuario_encontrado['Usuario']['id'];
				}
				
				// Almacena el registro del usuario
				$res = $this->Usuario->save($usuario,false);
				
				if($res){
					/** Para verificar si es un usuario juridico o natural **/
					if(!empty($usuario['Usuario']['tipo_rif'])){
						$identificacion = $usuario['Usuario']['tipo_rif'].desencriptar($usuario['Usuario']['rif']).$usuario['Usuario']['tipo_cedula'].desencriptar($usuario['Usuario']['cedula']).desencriptar($usuario['Usuario']['login']);
					} else {
						$identificacion = $estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'];
					}
					/** Para verificar si es un usuario juridico o natural **/
					
					if($this->Idg->usuarioExiste($identificacion)){
						$res_idg = true;
					} else {
						$res_idg = $this->Idg->crearUsuario($identificacion);
					}

					if($res_idg){
						$res_pwd = $this->Idg->crearContrasena(
							$identificacion,
							$this->data['Usuario']['clave'],
							$this->data['Usuario']['tiempoCaduca']
						);
						
						if(
							!$res_pwd &&
							$this->Idg->codigo_error == 'OBJECT_EXIST'
						){
							$res_pwd = $this->Idg->inicializaContrasena(
								$identificacion,
								$this->data['Usuario']['clave'],
								$this->data['Usuario']['tiempoCaduca']
							);
						}
					
						if($res_pwd){
							$ds->commit($this->Usuario);
							
							// Buscar el usuario en el canal en caso de ser una desafiliacion definitiva
							$canal_encontrado = $this->UsuarioCanal->find(
								'first',
								array(
									'conditions'=>array('usuario_id'=>$this->Usuario->id, 'canal_id'=>Configure::read('canal.ib'))
								)
							);
							
							if($canal_encontrado){
								$this->UsuarioCanal->id = $canal_encontrado['UsuarioCanal']['id'];
							}
							
							$usuario_canal['UsuarioCanal']['usuario_id']= $this->Usuario->id;
							
							// Si existe la variable para el limite la coloca en el INSERT
							if(isset($estado['Usuario']['limite_master'])) $usuario_canal['UsuarioCanal']['limite_externas'] = $estado['Usuario']['limite_master'];
							
							// Almacena el registro del usuario por canal
							if( !$this->UsuarioCanal->save($usuario_canal,false) ){
								
								// si falla y no es una desafiliacion definitiva borra el registro del usuario
								if(!$canal_encontrado){
									$this->Usuario->delete($this->Usuario->id);
								}
								
								$this->set('error_fatal',_m('error.fatal'));

								$this->log('error al crear el canal del usuario '+$identificacion,LOG_ERR);
							}
							else{ // inicio registro completo
							
								$estado['Usuario']['nomUsuario'] = $this->data['Usuario']['nomUsuario'];
								
								// Si es juridico guarda la info necesaria para los siguientes pasos
								// y almacena las transacciones segun el perfil
								if($this->Session->check('login.juridico')){
									$estado['Usuario']['nombre'] = $this->data['Usuario']['nombre'];
									$estado['Usuario']['perfil'] = $this->data['Usuario']['perfil'];
									$estado['Usuario']['cargo'] = $this->data['Usuario']['cargo'];
									
									// Transacciones basicas para los roles de juridicos
									$transacciones_roles= Configure::read('transacciones_basicas.rol');

									// Guarda las transacciones basicas
									foreach ($transacciones_roles[$this->data['Usuario']['perfil']] as $b){
										$this->TransaccionJuridico->save( array( 
											'TransaccionJuridico' => array(
												'usuario_id' => $this->Usuario->id, 
												'transaccion_id' => $b
											)
										), false);
										unset($this->TransaccionJuridico->id);
									}
								}

								$estado['Usuario']['emailPri'] = $this->data['Usuario']['emailPri'];
								$estado['Usuario']['emailSec'] = $this->data['Usuario']['emailSec'];
								$estado['Usuario']['codigoCelular'] = $this->data['Usuario']['codigoCelular'];
								$estado['Usuario']['nroCelular'] = $this->data['Usuario']['nroCelular'];
								$estado['Usuario']['clave'] = $this->data['Usuario']['clave'];
								$estado['Usuario']['clave_confirmacion'] = $this->data['Usuario']['clave_confirmacion'];
								$estado['Usuario']['tiempoCaduca'] = $this->data['Usuario']['tiempoCaduca'];
								$estado['Usuario']['usuario_creado'] = true;
								$estado['Usuario']['id'] = $this->Usuario->id;

								$this->Session->write(
									'usuarios.registro',
									$estado
								);

								if($this->Session->check('login.juridico')){
									$tipoId=$estado['Usuario']['tipoRif'];
									$nroId=$estado['Usuario']['nroRif'];
									$documento = $tipoId.$nroId;
								}else{
									$tipoId=$estado['Usuario']['tipoCedulaRif'];
									$nroId=$estado['Usuario']['nroCedulaRif'];
									$documento = $nroId;
								}

								// Crear los alias por defecto

								$res = $this->Activo->invoca(
									'SUMDSJV',
									array(
										'req'=>array(
											'SUMUSR'=>$documento
										)
									)
								);

								if(isset($res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'])){
									$instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

									normaliza($instrumentos);

									foreach($instrumentos as $i){
										$alias = trim($i['SUMNNM']);
										if(empty($alias)) continue;
										if(!in_array($i['SUMTYP'], array('SAV','DDA','NOW','CC&','LNS'))) continue;
										if(in_array($i['SUMTYP'], array('SAV','DDA','NOW')) && $i['SUMAST']!='A') continue;
										if($i['SUMTYP']=='CC&' && preg_match('/^\d{1,2}\/\d{1,2}\/\d{1,4}$/',trim($i['SUMNNM']))) continue;

										$this->AliasProducto->create();

										$res = $this->AliasProducto->save(
											array(
												'AliasProducto'=>array(
													'usuario_id'=>$estado['Usuario']['id'],
													'cuenta'=>encriptar($i['SUMACN']),
													'alias'=>encriptar($i['SUMNNM']),
												)
											),
											false
										);

										if(!$res){
											$this->log('Error al crear el alias '.$i['SUMNNM'].' de la cuenta'.$i['SUMACN'].' del usuario '.$i['SUMUSR'],LOG_ERR);
										}
									}
								}

								$this->Activo->invoca(
									'NIBPF3020',
									array(
										'req'=>array(
											'canal'=>Configure::read('canal.ib'),
											'INTNACEU'=>$tipoId,
											'INTIDEEU'=>str_pad($nroId,12,'0',STR_PAD_LEFT)
										)
									)
								);
						
								$this->redirect('usuario_creado');
							}
							// fin registro completo
							
						} else {
							switch($this->Idg->codigo_error){
								case 'INVALID_ARGUMENT':
									$this->Usuario->validationErrors['clave'] = 'contrasena';
									break;
								default:
									$this->Usuario->validationErrors['clave'] = $this->Idg->mensaje_error;	
							}
							
							$ds->rollback($this->Usuario);
							
							// Comentado porque revocaron permiso del usuario idg de eliminar usuarios
							//$this->Idg->eliminarUsuario($estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif']);
							
							$this->log('error al crear la contraseña del usuario en idg '+$identificacion,LOG_ERR);
						}
					} else {
						$ds->rollback($this->Usuario);
						$this->Usuario->validationErrors['nomUsuario'] = $this->Idg->mensaje_error;
						if($this->Session->check('login.juridico'))
							$this->log('error al crear el usuario en idg '+$estado['Usuario']['tipoRif']+$estado['Usuario']['nroRif']+$estado['Usuario']['tipoCedulaRif']+$estado['Usuario']['nroCedulaRif'],LOG_ERR);
						else
							$this->log('error al crear el usuario en idg '+$estado['Usuario']['tipoCedulaRif']+$estado['Usuario']['nroCedulaRif'],LOG_ERR);
					}
				} else {
					$ds->rollback($this->Usuario);
					$this->set('error_fatal',_m('error.fatal'));
					if($this->Session->check('login.juridico')) 
						$this->log('error al grabar el usuario'+$estado['Usuario']['tipoRif']+$estado['Usuario']['nroRif']+$estado['Usuario']['tipoCedulaRif']+$estado['Usuario']['nroCedulaRif'],LOG_ERR);
					else
						$this->log('error al grabar el usuario'+$estado['Usuario']['tipoCedulaRif']+$estado['Usuario']['nroCedulaRif'],LOG_ERR);
					$this->log($this->Usuario->getDataSource()->error);					
				}
			}
			
			$this->data['Usuario'] = array_merge($estado['Usuario'], $this->data['Usuario']);
			
			if(isset($estado['DatosBasicos'])) $this->data['DatosBasicos'] = $estado['DatosBasicos'];
		}

		$this->layout = "externa";
	}
	
	/**
	 * Acción que se ejecuta para los usuario juridicos creados por el master
	 **/
	function completar_juridico(){
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');
		$estado = $this->Session->read('usuarios.registro');
		
		// Verifica que viene del login
		if(
			!isset($estado['Usuario']['usuario_creado']) ||
			!$estado['Usuario']['usuario_creado'] || 
			!isset($estado['Usuario']['perfil']) ||
			$estado['Usuario']['perfil'] <= 2 
		){
			$this->redirect('/');
		}
		
		$pasos = array(
			'Confirmación',
			'Configuración Imagen Antiphishing',
			'Configuración Preguntas Desafío'
		);
		
		// Verifica los datos del usuario
		if(!empty($this->data['Usuario'])){
			
			$this->data['Usuario']['tipoRif']= $estado['Usuario']['tipoRif'];
			$this->data['Usuario']['nroRif']= $estado['Usuario']['nroRif'];
					
			$this->Usuario->set($this->data);
			
			$estado['Usuario'] = array_merge($estado['Usuario'],$this->data['Usuario']);
			$this->Session->write('usuarios.registro', $estado);
			
			if ($this->Usuario->validates(array('fieldList' => array(
				'nomUsuario',
				'clave',
				'clave_confirmacion',
				'tiempoCaduca',
				'nroCelular',
				'codigoCelular',
				'tipoCedulaRif',
				'nroCedulaRif',
			)))) {
				$perfiles = $this->RolJuridico->listadoRoles();
				if(!array_key_exists($estado['Usuario']['perfil'],$perfiles)) $this->redirect('login');
				// Data para la tabla usuario
				$usuario = array(
					'Usuario'=>array(
						'email_principal'=>encriptar($estado['Usuario']['emailPri']),
						'tipo_rif'=>$estado['Usuario']['tipoRif'],
						'rif'=>encriptar($estado['Usuario']['nroRif']),
						'nombre'=>encriptar($this->sanitize_input($estado['Usuario']['nombre'])),
						'perfil_ib_id'=>2,
						'rol_juridico_id'=>$estado['Usuario']['perfil'],
						'cargo'=>encriptar($estado['Usuario']['cargo']),
						'recibe_notificaciones'=>$this->data['Usuario']['recibe'],
					)
				);
				// verifica si es un usuario migrado
				if(isset($estado['legacy'])){
					$estado['Usuario']['codigoCelular'] = $this->data['Usuario']['codigoCelular'];
					$estado['Usuario']['nroCelular'] = $this->data['Usuario']['nroCelular'];
					$estado['Usuario']['tipoCedulaRif'] = $this->data['Usuario']['tipoCedulaRif'];
					$estado['Usuario']['nroCedulaRif'] = $this->data['Usuario']['nroCedulaRif'];
					$estado['Usuario']['nomUsuario'] = strtolower($this->data['Usuario']['nomUsuario']);
					
					$usuario['Usuario']['fecha_hora_creacion'] = date('Y-m-d H:i:s');
					$usuario['Usuario']['ibuser'] = $estado['Usuario']['ibuser'];
					
					$res = $this->Usuario->find(
						'count',
						array(
							'conditions'=>array(
								'Usuario.tipo_rif'=>$estado['Usuario']['tipoRif'],
								'Usuario.rif'=>encriptar($estado['Usuario']['nroRif']),
								'Usuario.tipo_cedula'=>$estado['Usuario']['tipoCedulaRif'],
								'Usuario.cedula'=>encriptar($estado['Usuario']['nroCedulaRif']),
								'Usuario.login'=>encriptar($estado['Usuario']['nomUsuario']),
							)
						)
					);
					
					if($res){
						$this->Usuario->validationErrors['nroCedulaRif'] = _m('ya.registrado');
					}
				}
				
				$usuario['Usuario']['celular'] = encriptar($estado['Usuario']['codigoCelular'].$estado['Usuario']['nroCelular']);
				$usuario['Usuario']['tipo_cedula'] = $estado['Usuario']['tipoCedulaRif'];
				$usuario['Usuario']['cedula'] = encriptar($estado['Usuario']['nroCedulaRif']);
				$usuario['Usuario']['login'] = encriptar($estado['Usuario']['nomUsuario']);
					
				// Verifica si existe un error con la cédula de los usuarios migrados
				if(!isset($this->Usuario->validationErrors['nroCedulaRif'])){
					
					$identificacion = $estado['Usuario']['tipoRif'].$estado['Usuario']['nroRif'].$estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'].$estado['Usuario']['nomUsuario'];
					
					$ds = $this->Usuario->getDataSource();

					$ds->begin($this->Usuario);	

					// Verifica si es un usuario que tiene el registro incompleto
					if( isset($estado['Usuario']['id']) ){
						$this->Usuario->id = $estado['Usuario']['id'];
					}
					
					// Verifica si fue un registro ya creado en el proceso de migracion
					if(isset($estado['legacy'])){
						$res = $this->Usuario->find(
							'first',
							array(
								'conditions'=>array(
									'Usuario.tipo_rif'=>$estado['Usuario']['tipoRif'],
									'Usuario.rif'=>encriptar($estado['Usuario']['nroRif']),
									'Usuario.ibuser'=>$estado['Usuario']['ibuser'],
								)
							)
						);
						if($res){
							$this->Usuario->id = $res['Usuario']['id'];
						}
					}

					// Almacena/Actualiza el registro del usuario
					$res = $this->Usuario->save($usuario,false);
					
					if($res){
						$estado['Usuario']['id'] = $this->Usuario->id;

						if($this->Idg->usuarioExiste($identificacion)){
							$res_idg = true;
						} else {
							$res_idg = $this->Idg->crearUsuario($identificacion);
						}

						if($res_idg){

							$res_pwd = $this->Idg->crearContrasena(
								$identificacion,
								$this->data['Usuario']['clave'],
								$this->data['Usuario']['tiempoCaduca']
							);
							
							if(
								!$res_pwd &&
								$this->Idg->codigo_error == 'OBJECT_EXIST'
							){
								$res_pwd = $this->Idg->inicializaContrasena(
									$identificacion,
									$this->data['Usuario']['clave'],
									$this->data['Usuario']['tiempoCaduca']
								);
							}

							if($res_pwd){
								$ds->commit($this->Usuario);
								$continuar = true;

								// Buscar el usuario en el canal
								$canal_encontrado = $this->UsuarioCanal->find(
									'first',
									array(
										'conditions'=>array('usuario_id'=>$this->Usuario->id, 'canal_id'=>Configure::read('canal.ib'))
									)
								);

								if($canal_encontrado){
									$this->UsuarioCanal->id = $canal_encontrado['UsuarioCanal']['id'];
								}

								$usuario_canal = array(
									'UsuarioCanal'=> array(
										'canal_id'=> Configure::read('canal.ib'),
										'estatus_registro'=> 'I',
										'estatus_acceso'=> isset($estado['legacy']) ? $estado['Usuario']['accesoIB'] : 'A',
										'usuario_id'=> $this->Usuario->id,
                                                                                'fecha_creacion'=>date('Y-m-d H:i:s'),
									)
								);

								// Almacena el registro del usuario por canal
								if( !$this->UsuarioCanal->save($usuario_canal,false) ){
									// Si es un usuario migrado crea el registro en la tabla usuario_canal
									if( isset($estado['legacy']) ){

										// Si ocurre un error elimina de la tabla usuario
										$this->Usuario->delete($this->Usuario->id);
									}

									$this->set('error_fatal',_m('error.fatal'));
									$this->log('error al crear el canal del usuario ' . $identificacion, LOG_ERR);
									$continuar = false;
								}
								else{
								
									if($this->Session->check('login.juridico')){
										$tipoId=$estado['Usuario']['tipoRif'];
										$nroId=$estado['Usuario']['nroRif'];
										$documento = $tipoId.$nroId;
									}else{
										$tipoId=$estado['Usuario']['tipoCedulaRif'];
										$nroId=$estado['Usuario']['nroCedulaRif'];
										$documento = $nroId;
									}

									// Crear los alias por defecto

									$res = $this->Activo->invoca(
										'SUMDSJV',
										array(
											'req'=>array(
												'SUMUSR'=>$documento
											)
										)
									);

									if($res){
										$instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];

										normaliza($instrumentos);

										foreach($instrumentos as $i){
                                                                                        $alias = trim($i['SUMNNM']);                                                                                        
											if(empty($alias)) continue;
											if(!in_array($i['SUMTYP'], array('SAV','DDA','NOW','CC&','LNS'))) continue;
											if(in_array($i['SUMTYP'], array('SAV','DDA','NOW')) && $i['SUMAST']!='A') continue;
											if($i['SUMTYP']=='CC&' && preg_match('/^\d{1,2}\/\d{1,2}\/\d{1,4}$/',trim($i['SUMNNM']))) continue;

											$this->AliasProducto->create();

											$res = $this->AliasProducto->save(
												array(
													'AliasProducto'=>array(
														'usuario_id'=>$estado['Usuario']['id'],
														'cuenta'=>encriptar($i['SUMACN']),
														'alias'=>encriptar($i['SUMNNM']),
													)
												),
												false
											);

											if(!$res){
												$this->log('Error al crear el alias '.$i['SUMNNM'].' de la cuenta'.$i['SUMACN'].' del usuario '.$i['SUMUSR'],LOG_ERR);
											}
										}
									}	

									// Si es un usuario migrado crea el registro en la tabla usuario_canal
									if( isset($estado['legacy']) ){

										// Guarda las transacciones basicas
										foreach ($estado['transacciones'] as $b){
											$this->TransaccionJuridico->save( array( 
												'TransaccionJuridico' => array(
													'usuario_id' => $this->Usuario->id, 
													'transaccion_id' => $b
												)
											), false);
											unset($this->TransaccionJuridico->id);
										}
										
										// verfica si tiene trasacciones en el IB antiguo
										if(isset($estado['transacciones_asignadas'])){
											
											foreach ($estado['transacciones_asignadas'] as $id=>$b){
												$this->TransaccionJuridico->save( array( 
													'TransaccionJuridico' => array(
														'usuario_id' => $this->Usuario->id, 
														'transaccion_id' => $id
													)
												), false);
												
												// almacena el detalle de las transacciones
												foreach($b as $t){
													$detalle = array(
														'TransaccionJuridicoDetalle'=>array(
															'transacciones_juridicos_id'=> $this->TransaccionJuridico->id,
															'aprobaciones'=> $t['aprobacion'],
															'limite'=> $t['limite']
														)
													);
													
													if(isset($t['cuenta'])) $detalle['TransaccionJuridicoDetalle']['cuenta'] = $t['cuenta'];
													
													$this->TransaccionJuridicoDetalle->save($detalle, false);
													
													unset($this->TransaccionJuridicoDetalle->id);
												}
												
												unset($this->TransaccionJuridico->id);
											}
										}

										$estado['Usuario']['id'] = $this->Usuario->id;
									}
								}

								// Verifica si continua con los siguientes pasos
								if($continuar){
									
										$estado['Usuario']['usuario_creado'] = true;
										$estado['pasos'] = $pasos;
										$this->Session->write('usuarios.registro', $estado);
										$this->redirect('usuario_creado');
								}
							}
							else {

								$ds->rollback($this->Usuario);

								switch($this->Idg->codigo_error){
									case 'INVALID_ARGUMENT':
										$this->Usuario->validationErrors['clave'] = 'contrasena';
										break;
									default:
										$this->Usuario->validationErrors['clave'] = $this->Idg->mensaje_error;	
								}

								$this->log('error al crear la contraseña del usuario en IDG ' . $identificacion,LOG_ERR);
							}

						}
						else {
							$ds->rollback($this->Usuario);
							$this->Usuario->validationErrors['clave'] = $this->Idg->mensaje_error;
							$this->log('error al crear el usuario en IDG ' . $identificacion, LOG_ERR);
						}

					} else {
						$ds->rollback($this->Usuario);
						$this->set('error_fatal',_m('error.fatal'));
						$this->log('error al grabar el usuario ' . $identificacion, LOG_ERR);
						$this->log($this->Usuario->getDataSource()->error);					
					}
				}
			}
		}
		
		$perfiles = $this->RolJuridico->listadoRoles();
		$paso_actual = 0;
		
		$this->set(compact('pasos','paso_actual','perfiles'));
		
		$this->data = $estado;
		$this->layout = "externa";
	}

	/**
	 * Acción para mostrar mensaje de usuario creado
	**/
	function usuario_creado(){
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		$estado = $this->Session->read('usuarios.registro');
		
		if(
			!isset($estado['Usuario']['usuario_creado']) ||
			!$estado['Usuario']['usuario_creado']
		){
			$this->redirect('/');
		}
			
		if(empty($this->data['Usuario'])){
			$this->data = $estado;
		} else {
			if($this->Session->check('login.juridico')){
				
				// Verifica si es un usuario master o master/unico
				if($estado['Usuario']['perfil']==1){
					$pasos = array(						
						'Autenticar Empresa',
                                                'Aceptación de Contrato',
						'Registro de Usuario',
						'Configuración Imagen Antiphishing',
						'Configuración Preguntas Desafío',
						'Tarjeta de Coordenadas'
					);
				} 
				elseif($estado['Usuario']['perfil']==2){
					$pasos = array(						
						'Autenticar Empresa',
                                                'Aceptación de Contrato',
						'Registro de Usuario',
						'Configuración Imagen Antiphishing',
						'Configuración Preguntas Desafío',
						'Configuración de Límites',
						'Tarjeta de Coordenadas'
					);
				}
				else{
					$pasos = $estado['pasos'];
				}
			}else{
				$pasos = array(					
					'Autenticación del Cliente',
                                        'Aceptación de Contrato',
					'Registro de Usuario',
                                        'Código de Autorización',
					'Configuración Imagen Antiphishing',
					'Configuración Preguntas Desafío',
					'Configuración de Límites'
				);
			}
			
			
			if($this->Session->check('login.juridico')){
				
				$this->Session->write(
					'usuarios.config_sello',array(
						'url_aceptar'=>'asignar_preguntas',
						'layout'=>'externa',
						'titulo'=>'Registro',
						'pasos'=>$pasos,
						'paso_actual'=> ($estado['Usuario']['perfil']>2) ? 1 : 3,
					)
				);
				
				// genera la tarjeta de coordenadas de una vez
				$this->Session->write('solicitudes.solicitar_tarjeta_coordenada_confirmada',true);
				$this->Session->write('seguridad.validar_preguntas_autenticacion', array('validado'=>true));
				$this->Session->write('seguridad.validar_preguntas_desafio', array('validado'=>true));
                                $this->Session->write('seguridad.validar_otp',array('validado'=>true));
				
				// Verifica si es un usuario master o master/unico
				if($estado['Usuario']['perfil']==1){
					$this->Session->write('usuarios.limite_montos', array('exito'=>true));
					
					$this->Session->write('usuarios.asignar_preguntas',
						array(
							'url_aceptar'=>'/solicitudes/solicitar_tarjeta_coordenada',
							'layout'=>'externa',
							'titulo'=>'Registro',
							'pasos'=>$pasos,
							'paso_1'=>4,
							'paso_2'=>4,
							'paso_3'=>4
						)
					);
					
					$this->Session->write('solicitudes.solicitar_tarjeta_coordenada',
						array(
							'url_aceptar'=>'/usuarios/resultado_juridico',
                                                        'url_volver'=>'/',
							'layout'=>'externa',
							'titulo'=>'Registro',
							'pasos'=> $pasos,
							'paso_actual'=>5,
                                                        'paso_resultado'=>5,
                                                        'seccion_actual'=>'',
						)
					);
					
				}
				else if($estado['Usuario']['perfil']==2){
					$this->Session->write('usuarios.asignar_preguntas',
						array(
							'url_aceptar'=>'limite_montos',
							'layout'=>'externa',
							'titulo'=>'Registro',
							'pasos'=>$pasos,
							'paso_1'=>4,
							'paso_2'=>4,
							'paso_3'=>4
						)
					);

					$this->Session->write('usuarios.limite_montos',
						array(
							'url_aceptar'=>'/solicitudes/solicitar_tarjeta_coordenada',
							'layout'=>'externa',
							'titulo'=>'Registro',
							'pasos'=> $pasos,
							'paso_1'=>5,
							'paso_2'=>5,
							'paso_3'=>5
						)
					);

					$this->Session->write('solicitudes.solicitar_tarjeta_coordenada',
						array(
							'url_aceptar'=>'/usuarios/resultado_juridico',
							'url_volver'=>'/usuarios/limite_montos',
							'layout'=>'externa',
							'titulo'=>'Registro',
							'pasos'=> $pasos,
							'paso_actual'=>6,
                                                        'paso_resultado'=>6,
                                                        'seccion_actual'=>'',
						)
					);
				}
				else{
					$this->Session->write('usuarios.limite_montos', array('exito'=>true));
					
					$this->Session->write('usuarios.asignar_preguntas',
						array(
							'url_aceptar'=>'/usuarios/resultado_juridico',
							'layout'=>'externa',
							'titulo'=>'Registro',
							'pasos'=>$pasos,
							'paso_1'=>2,
							'paso_2'=>2,
							'paso_3'=>2
						)
					);

				}
					
			}else{
				$this->Session->write(
                                        'seguridad.validar_otp',
                                        array(
                                                'url_aceptar'=>'/usuarios/config_sello',
                                                'url_volver' => '/',
                                                'layout'=>'externa',
                                                'titulo'=>'Registro',
                                                'pasos'=> $pasos,
                                                'paso_actual'=>3
                                        )
                                );
                                
				$this->Session->write(
					'usuarios.config_sello',array(
						'url_aceptar'=>'asignar_preguntas',
						'layout'=>'externa',
						'titulo'=>'Registro',
						'pasos'=>$pasos,
						'paso_actual'=>4
					)
				);
			
				$this->Session->write('usuarios.asignar_preguntas',
					array(
						'url_aceptar'=>'limite_montos',
						'layout'=>'externa',
						'titulo'=>'Registro',
						'pasos'=>$pasos,
						'paso_1'=>5,
						'paso_2'=>5,
						'paso_3'=>5
					)
				);
				
				$this->Session->write('usuarios.limite_montos',
					array(
						'url_aceptar'=>'resultado',
						'layout'=>'externa',
						'titulo'=>'Registro',
						'pasos'=> $pasos,
						'paso_1'=>6,
						'paso_2'=>6,
						'paso_3'=>6
					)
				);
			}
			
                        if(!$this->Session->check('login.juridico')){
                            $this->redirect('/seguridad/genera_otp');
                        }else{
			$this->redirect('config_sello');
		}
		}

		$this->layout = "externa";
	}
	

	/**
	 * Acción que maneja el formulario para configurar la imagen de seguridad
	 */
	function config_sello(){
            
		if(!$this->Session->check('usuarios.config_sello')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.config_sello');

		if($this->Session->check('usuarios.registro')){
			$estado = $this->Session->read('usuarios.registro');
			$estado['nro_transaccion']='100031';
		} else {
			$estado = $this->Session->read('usuarios.datos');
			$estado['nro_transaccion']='18';
		}

		//$estado = $this->Session->read('usuarios.registro');
		
		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}
		
		// se buscan las imagenes
		$array_docena = $this->Activo->buscarSellos();
		$this->Session->write('usuarios.sellos',$array_docena);
		$this->set('listado_sellos', $array_docena);

		if(empty($this->data['Usuario'])){
			if($this->Session->check('usuarios.registro')){
				$this->data = $estado;
			}
		} else{
			$this->Usuario->set($this->data);

			if ($this->Usuario->validates(array('fieldList' => array(
				'idImagen',
			)))) {
				$estado['Usuario']['idImagen']=$this->data['Usuario']['idImagen'];
				$this->Session->write('usuarios.registro',$estado);
				$this->redirect('config_confirm');			
			}
		}
	}

	/**
	 * Acción para confirmar la selección de la imagen de seguridad
	**/
	function config_confirm(){
            
		if(!$this->Session->check('usuarios.config_sello')) $this->redirect('/');
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.config_sello');
		
                if($this->Session->check('usuarios.registro')){
                    $estado = $this->Session->read('usuarios.registro');			
		} else {
                    $estado = $this->Session->read('usuarios.datos');			
		}                
                
		if(
			!isset($estado['Usuario']['idImagen'])
		){
			$this->redirect('/');
		}

		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}

		// se buscan las imagenes
		$lista_imagenes = $this->Session->read('usuarios.sellos');
		shuffle($lista_imagenes);
		$this->set('imagenes', $lista_imagenes);

		if(empty($this->data['Usuario'])){
			$this->data = $estado;
		} else {
			$this->Usuario->set($this->data);

			if ($this->Usuario->validates(array('fieldList' => array(
				'idConfirm',
			)))) {
				if(isset($estado['Usuario']['id'])){
					$this->Usuario->id = $estado['Usuario']['id'];
				} else if($this->Session->check('usuarios.datos')){
					$u = $this->Session->read('usuarios.datos');
					$this->Usuario->id = $u['id'];
				}
				$this->Usuario->read();
				
				$this->Usuario->set(array(
					'sello_id'=>encriptar($this->data['Usuario']['idConfirm']),
					'sello_1'=>null,
					'sello_2'=>null,
					'sello_3'=>null,
                                        'fecha_sello_id'=>date('Y-m-d H:i:s')
				));
				
				$res = $this->Usuario->save(null,false);			
				
				if($res){
					$this->Bitacora->registraBitacora(
						$this->Usuario->id,
						$estado['nro_transaccion'],
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						0
					);
					$estado['Usuario']['idConfirm'] = $this->data['Usuario']['idConfirm'];
					$data_session['exito'] = true;
					$this->Session->write('usuarios.config_sello',$data_session);
					$this->Session->write('usuarios.registro',$estado);
                                        $tipo_cambio = 'Imagen';
                                        if(isset($estado['nombre'])){
                                            $nombre_destino = desencriptar($estado['nombre']);
                                            $email = desencriptar($estado['email_principal']);
                                            $celular = desencriptar($estado['celular']);
                                        }elseif (isset($estado['Usuario']['nombre'])){
                                            $nombre_destino = desencriptar($estado['Usuario']['nombre']);
                                            $email = desencriptar($estado['Usuario']['email_principal']);
                                            $celular = desencriptar($estado['Usuario']['celular']);
                                        }else{
                                            $datos_usuario = $this->Session->read('seguridad.olvido');
                                            $nombre_destino = desencriptar($datos_usuario['datasource']['nombre']);
                                            $email = desencriptar($datos_usuario['datasource']['email_principal']);
                                            $celular = desencriptar($datos_usuario['datasource']['celular']);
                                        }
                                        
                                        $this->set(compact('nombre_destino', 'tipo_cambio'));
                                        
                                        // Email Options
                                        $this->Email->smtpOptions = Configure::read('email.smtp');
                                        $this->Email->delivery = 'smtp';
                                        $this->Email->from = Configure::read('email.from');
                                        $this->Email->to = $email;
                                        $this->Email->subject = 'Cambio de Imagen Antiphising';
                                        $this->Email->sendAs = 'html';

                                        // Informacion del correo
                                        $this->Email->template = 'cambio_datos_autenticacion';                                        

                                        // Envio del correo
                                        $this->Email->send();
                                        
                                        //Envio de SMS
                                        $this->Sms->enviar(
						array(
                                                    'numcel'=>$celular,
                                                    'canal1'=>'65',
                                                    'fectim'=>$tipo_cambio,
                                                    'xrnde38'=>'IB'
						)
					);
                                        
					$this->redirect('sello_creado');
				} else {
					$this->set('error_fatal',_m('error.fatal'));
					$this->log('error al grabar el sello del usuario'+$estado['Usuario']['id'],LOG_ERR);
					$this->log($this->Usuario->getDataSource()->error);
					
					$this->Bitacora->registraBitacora(
						$this->Usuario->id,
						$estado['nro_transaccion'],
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						1
					);
					
				}
			}
		}
	}

	/**
	 * Acción para establecer una imagen de seguridad personalizada
	**/
	function seleccion_sello(){
		if(!$this->Session->check('usuarios.config_sello')) $this->redirect('/');
	
		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.config_sello');
		$estado = $this->Session->read('usuarios.registro');
		
		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];
		
		$x = $data_session['pasos'][$data_session['paso_actual']];
		
		if($x=='Selección de imagen'){
			$data_session['pasos'][$data_session['paso_actual']]='Creación de imagen';
		}
		
		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}

		if(!empty($this->data['Usuario'])){
			$this->Usuario->set($this->data);

			if ($this->Usuario->validates(array('fieldList' => array(
				'sello1','sello2','sello3'
			)))) {
				$estado['Usuario']['sello1'] = $this->data['Usuario']['sello1'];
				$estado['Usuario']['sello2'] = $this->data['Usuario']['sello2'];
				$estado['Usuario']['sello3'] = $this->data['Usuario']['sello3'];

				$this->Session->write('usuarios.registro',$estado);
				$this->redirect('seleccion_confirmacion');
			}
		} else{
			$this->data = $estado;
		}
	}

	/**
	 * Acción para confirmar la imagen de seguridad personalizada
	**/
	function seleccion_confirmacion(){
		if(!$this->Session->check('usuarios.config_sello')) $this->redirect('/');
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.config_sello');
		$estado = $this->Session->read('usuarios.registro');

		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		if($data_session['pasos'][$data_session['paso_actual']]=='Selección de Imagen'){
			$data_session['pasos'][$data_session['paso_actual']]='Creación de Imagen';
		}

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}		

		if(empty($this->data['Usuario'])){
			$this->data = $estado;
		} else {
			if(isset($estado['Usuario']['id'])){
				$this->Usuario->id = $estado['Usuario']['id'];
			} else if($this->Session->check('usuarios.datos')){
				$u = $this->Session->read('usuarios.datos');
				$this->Usuario->id = $u['id'];
			}
			
			$this->Usuario->read();
			
			$this->Usuario->set(array(
				'sello_1'=>encriptar($estado['Usuario']['sello1']),
				'sello_2'=>encriptar($estado['Usuario']['sello2']),
				'sello_3'=>encriptar($estado['Usuario']['sello3']),
				'sello_id'=>null,
                                'fecha_sello_id'=>date('Y-m-d H:i:s')
			));
			
			$res = $this->Usuario->save(null,false);
			
			if($res){
				$this->Bitacora->registraBitacora(
					$this->Usuario->id,
					18
				);
				$data_session['exito'] = true;
				$this->Session->write('usuarios.config_sello',$data_session);		
				$this->redirect('sello_creado');				
			} else {
				$this->set('error_fatal',_m('error.fatal'));
				$this->log('Error al grabar sello personalizado del usuario'+$estado['Usuario']['id'],LOG_ERR);
				$this->log($this->Usuario->getDataSource()->error);
			}
		}
	}

	/**
	 * Acción para indicar que la imagen de seguridad ha sido creado
	**/
	function sello_creado(){
		if(!$this->Session->check('usuarios.config_sello')) $this->redirect('/');
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.config_sello');
		$estado = $this->Session->read('usuarios.registro');

		if(
			!isset($data_session['exito']) ||
			!$data_session['exito']
		){
			$this->redirect('/');
		}

		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}		

		// Se borra la variable de sesion que contiene los sellos
		$this->Session->delete('usuarios.sellos');
                if($this->Session->check('usuarios.sello.vencido')){
                    $this->Session->delete('usuarios.sello.vencido');
                    $this->Session->delete('seguridad.validar_tdc');
		}
		
		if(empty($this->data['Usuario'])){
			$this->data = $estado;
		} else {
			$this->redirect($data_session['url_aceptar']);
		}
	}

	/**
	 * Acción que maneja el formulario para generar las preguntas de
	 * desafío
	**/
	function asignar_preguntas(){
		if(!$this->Session->check('usuarios.asignar_preguntas')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.asignar_preguntas');
		
		if($this->Session->check('usuarios.registro')){
			$estado = $this->Session->read('usuarios.registro');
			$estado['nro_transaccion']='100032';
		} else {
			$estado = $this->Session->read('usuarios.datos');
			$estado['nro_transaccion']='17';
		}
                
		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		$data_session['paso_actual'] = $data_session['paso_1'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}

		$preguntas = $this->PreguntasDesafio->buscarTodas();

		$this->set('preguntas', $preguntas);
		if(empty($this->data['Usuario'])){
			if($this->Session->check('usuarios.registro')){
				$this->data = $estado;
			} else {
				if(isset($data_session['usuario'])){
					$usuario = $data_session['usuario'];
				} else {
					$usuario = $this->Session->read('usuarios.datos');
				}
				
				/** Para verificar si es un usuario juridico o natural **/
				if(!empty($usuario['tipo_rif'])){
					$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']).$usuario['tipo_cedula'].desencriptar($usuario['cedula']).desencriptar($usuario['login']);
					$estado['Usuario']['perfil'] = $usuario['rol_juridico_id'];
                                        $this->Session->write('usuarios.registro',$estado);                                        
				}
				else{
					$identificacion = $usuario['tipo_cedula'].desencriptar($usuario['cedula']);
				}
				/** Para verificar si es un usuario juridico o natural **/
				
				$qa = $this->Idg->obtenerQA($identificacion);
				
				if($qa){
					$i = 1;
					
					foreach($qa as $pregunta => $respuesta){
						if(false !== ($pos = array_search($pregunta,$preguntas))){
							$this->data['Usuario']["preg$i"] = $pos;
						} else {
							$this->data['Usuario']["preg$i"] = 'otra';
							$this->data['Usuario']["pregUser$i"] = $pregunta;
						}
						
						$this->data['Usuario']["resp$i"] = $respuesta;
						
						$i++;
					}
				}
			}
		} else {
			$this->Usuario->set($this->data);

			if ($this->Usuario->validates(array('fieldList' => array(
				'preg1',
				'preg2',
				'preg3',
				'pregUser1',
				'pregUser2',
				'pregUser3',
				'resp1',
				'resp2',
				'resp3',
			)))) {
				$estado['Usuario']['preg1'] = $this->data['Usuario']['preg1'];
				$estado['Usuario']['preg2'] = $this->data['Usuario']['preg2'];
				$estado['Usuario']['preg3'] = $this->data['Usuario']['preg3'];
				$estado['Usuario']['pregUser1'] = $this->data['Usuario']['pregUser1'];
				$estado['Usuario']['pregUser2'] = $this->data['Usuario']['pregUser2'];
				$estado['Usuario']['pregUser3'] = $this->data['Usuario']['pregUser3'];
				$estado['Usuario']['resp1'] = $this->data['Usuario']['resp1'];
				$estado['Usuario']['resp2'] = $this->data['Usuario']['resp2'];
				$estado['Usuario']['resp3'] = $this->data['Usuario']['resp3'];
			
				// Se guarda la informacion y se dirige al siguiente paso
				$data_session['asignadas'] = true;
				$this->Session->write('usuarios.asignar_preguntas',$data_session);
				$this->Session->write('usuarios.registro',$estado);
				$this->redirect('confirmar_preguntas');
			}
		}
	}

	/**
	 * Acción para confirmar las preguntas de desafío
	**/
	function confirmar_preguntas(){
            
            	if(!$this->Session->check('usuarios.asignar_preguntas')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.asignar_preguntas');		
                
                if($this->Session->check('usuarios.registro')){
                    $estado = $this->Session->read('usuarios.registro');			
		} else {
                    $estado = $this->Session->read('usuarios.datos');			
		}
                
		if(
			!isset($data_session['asignadas']) ||
			!$data_session['asignadas']
		){
			$this->redirect('/');
		}

		$data_session['paso_actual'] = $data_session['paso_2'];

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}

		if(!empty($this->data['Usuario'])){
			$preguntas = $this->PreguntasDesafio->buscarTodas();
		
			$qa = array();
			
			if($estado['Usuario']['preg1']=='otra'){
				$qa[$estado['Usuario']['pregUser1']] = $estado['Usuario']['resp1'];
			} else {
				$qa[$preguntas[$estado['Usuario']['preg1']]] = $estado['Usuario']['resp1'];
			}
			
			if($estado['Usuario']['preg2']=='otra'){
				$qa[$estado['Usuario']['pregUser2']] = $estado['Usuario']['resp2'];
			} else {
				$qa[$preguntas[$estado['Usuario']['preg2']]] = $estado['Usuario']['resp2'];
			}
			
			if($estado['Usuario']['preg3']=='otra'){
				$qa[$estado['Usuario']['pregUser3']] = $estado['Usuario']['resp3'];
			} else {
				$qa[$preguntas[$estado['Usuario']['preg3']]] = $estado['Usuario']['resp3'];
			}
			
			if(!isset($estado['Usuario']['id'])){
				if(isset($data_session['usuario'])){
					$u = $data_session['usuario'];
				} else {
					$u = $this->Session->read('usuarios.datos');
				}
				
				$this->Usuario->id = $u['id'];
				$estado['Usuario']['nomUsuario'] = desencriptar($u['login']);
				$estado['Usuario']['tipoCedulaRif'] = $u['tipo_cedula'];
				$estado['Usuario']['nroCedulaRif'] = desencriptar($u['cedula']);
				$estado['Usuario']['tipoRif'] = $u['tipo_rif'];
				$estado['Usuario']['nroRif'] = desencriptar($u['rif']);
			} else {
				$this->Usuario->id = $estado['Usuario']['id'];
			}
			
			/** Para verificar si es un usuario juridico o natural **/
			if(!empty($estado['Usuario']['tipoRif'])){
				$identificacion = $estado['Usuario']['tipoRif'].$estado['Usuario']['nroRif'].$estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'].$estado['Usuario']['nomUsuario'];
			}
			else{
				$identificacion = $estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'];
			}
			
			$res = $this->Idg->guardarQA(
				$identificacion,
				$qa
			);
			
			if($res){
				$res = $this->Idg->desbloquearQA($identificacion);
				
				if($res){
					$this->Bitacora->registraBitacora(
						$this->Usuario->id,
						$estado['nro_transaccion'],
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						0
					);
					
					$data_session['exito'] = true;
					$this->Session->write('usuarios.asignar_preguntas',$data_session);
					
                                        $tipo_cambio = 'Preguntas Desafio';
                                        if(isset($estado['nombre'])){
                                            $nombre_destino = desencriptar($estado['nombre']);
                                            $email = desencriptar($estado['email_principal']);
                                            $celular = desencriptar($estado['celular']);
                                        }elseif (isset($estado['Usuario']['nombre'])){
                                            $nombre_destino = desencriptar($estado['Usuario']['nombre']);
                                            $email = desencriptar($estado['Usuario']['email_principal']);
                                            $celular = desencriptar($estado['Usuario']['celular']);
                                        }else{
                                            $datos_usuario = $this->Session->read('seguridad.olvido');
                                            $nombre_destino = desencriptar($datos_usuario['datasource']['nombre']);
                                            $email = desencriptar($datos_usuario['datasource']['email_principal']);
                                            $celular = desencriptar($datos_usuario['datasource']['celular']);
                                        }
                                            
                                        $this->set(compact('nombre_destino', 'tipo_cambio'));
                                        
                                        // Email Options
                                        $this->Email->smtpOptions = Configure::read('email.smtp');
                                        $this->Email->delivery = 'smtp';
                                        $this->Email->from = Configure::read('email.from');
                                        $this->Email->to = $email;
                                        $this->Email->subject = 'Cambio Preguntas y Respuestas de Desafio';
                                        $this->Email->sendAs = 'html';

                                        // Informacion del correo
                                        $this->Email->template = 'cambio_datos_autenticacion';                                        

                                        // Envio del correo
                                        $this->Email->send();
                                        
                                        //Envio de SMS
                                        $this->Sms->enviar(
						array(
                                                    'numcel'=>$celular,
                                                    'canal1'=>'65',
                                                    'fectim'=>$tipo_cambio,
                                                    'xrnde38'=>'IB'
						)
					);
                                        
                                        //Si es Juridico y no es Master ni Master/Unico mando a finalizar el registro
                                        if (!empty($estado['Usuario']['tipoRif']) && isset($estado['Usuario']['perfil']) && 
                                            $estado['Usuario']['perfil']>2){
                                            
                                            $this->finalizar_registro_juridico();  
                                        }
                                        
                                        $this->redirect('preguntas_creadas');
				} else {
					$this->set('error_fatal',_m('error.fatal'));
					$this->log('error al guardar las qa del usuario en idg '+$this->Usuario->id,LOG_ERR);
					
					$this->Bitacora->registraBitacora(
						$this->Usuario->id,
						$estado['nro_transaccion'],
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						1
					);
				}
			} else {
				$this->set('error_fatal',_m('error.fatal'));
				$this->log('error al guardar las qa del usuario en idg '+$this->Usuario->id,LOG_ERR);
				
				$this->Bitacora->registraBitacora(
					$this->Usuario->id,
					$estado['nro_transaccion'],
					NULL,
					NULL,
					NULL,
					NULL,
					NULL,
					1
				);
			}
		}
		
		$this->data = $estado;

		$preguntas = $this->PreguntasDesafio->buscarTodas();

		$this->set('preguntas', $preguntas);

		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);
	}

	/**
	 * Acción para mostrar mensaje de preguntas creadas
	**/
	function preguntas_creadas(){
		if(!$this->Session->check('usuarios.asignar_preguntas')) $this->redirect('/');
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.asignar_preguntas');
		$estado = $this->Session->read('usuarios.registro');

		if(
			!isset($data_session['exito']) ||
			!$data_session['exito']
		){
			$this->redirect('/');
		}

		if(empty($this->data['Usuario'])){
			$this->data = $estado;
		} else {
			$this->redirect($data_session['url_aceptar']);
		}

		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		$data_session['paso_actual'] = $data_session['paso_3'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}                
	}

	/**
	 * Acción que maneja el formulario de configuración de montos límites
	 * @param string $volver Indica si esta regresando de la confirmación
	**/
	function limite_montos($volver=null){
		if(!$this->Session->check('usuarios.limite_montos')) $this->redirect('/');

		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.limite_montos');
		
		if($this->Session->check('usuarios.registro')){
			$estado = $this->Session->read('usuarios.registro');
			$estado['nro_transaccion']='100031';
		} else {
			$estado = $this->Session->read('usuarios.datos');
			$estado['nro_transaccion']='19';
		}
		
		$limites =  $this->Usuario->obtenerLimitesMaximos();

		


		if(!empty($this->data['Usuario'])){
			$this->Usuario->set($this->data['Usuario']);

			// Aqui se verifica si hay un monto en blanco o con valor 0 / 0,0
			// en ese caso se omite la transaccion de transferencia 
			// (leer documento funcional para mas informacion)
			
			$this->Usuario->montos_limites = $limites;
			
			if($this->Usuario->validates(array('fieldList'=>array(
				'it_transaccion',
				'e_transaccion',
				'it_transaccion_n_usd',
                'it_transaccion_n_eur',
			)))){
                            
                            if($estado['perfil_ib_id']==2){
                                if($this->Usuario->validates(array('fieldList'=>array(
                                        'it_transaccion_n',
                	'it_transaccion_n_usd',
                	'it_transaccion_n_eur',
                                        'e_transaccion_n',
                                        'it_transaccion_p',
                	'it_transaccion_p_usd',
                	'it_transaccion_p_eur',
                                        'e_transaccion_p',
                                )))){
                                    $data_session['asignado'] = true;
                                    $data_session['juridico'] = true;
                                    $this->Session->write('usuarios.limite_montos',$data_session);

                                    $estado['Usuario']['it_transaccion'] = $this->data['Usuario']['it_transaccion'];
                                    $estado['Usuario']['e_transaccion'] = $this->data['Usuario']['e_transaccion'];
                                    $estado['Usuario']['it_transaccion_n'] = $this->data['Usuario']['it_transaccion_n'];

                    $estado['Usuario']['it_transaccion_n_usd'] = $this->data['Usuario']['it_transaccion_n_usd'];
                    $estado['Usuario']['it_transaccion_n_eur'] = $this->data['Usuario']['it_transaccion_n_eur'];

                                    $estado['Usuario']['e_transaccion_n'] = $this->data['Usuario']['e_transaccion_n'];
                                    $estado['Usuario']['it_transaccion_p'] = $this->data['Usuario']['it_transaccion_p'];

                    $estado['Usuario']['it_transaccion_p_usd'] = $this->data['Usuario']['it_transaccion_p_usd'];
                    $estado['Usuario']['it_transaccion_p_eur'] = $this->data['Usuario']['it_transaccion_p_eur'];

                                    $estado['Usuario']['e_transaccion_p'] = $this->data['Usuario']['e_transaccion_p'];
                                    $this->Session->write('usuarios.registro',$estado);

                                    $this->redirect('confirmar_limites');
                                }
                            }else{
                                $data_session['asignado'] = true;
                                $data_session['juridico'] = false;
				$this->Session->write('usuarios.limite_montos',$data_session);

				$estado['Usuario']['it_transaccion'] = $this->data['Usuario']['it_transaccion'];
				$estado['Usuario']['e_transaccion'] = $this->data['Usuario']['e_transaccion'];
				$this->Session->write('usuarios.registro',$estado);

				$this->redirect('confirmar_limites');
                            }
				
			}
		} elseif($this->Session->check('usuarios.registro')){
                        $usuario = $this->Session->read('usuarios.datos');
			$this->data = $this->Session->read('usuarios.registro');

			

		} else if($this->Session->check('usuarios.datos')) {
			$usuario = $this->Session->read('usuarios.datos');
			

			// verifico los limites que voy a mostrar

			if($usuario['limite_internas_terceros']) $this->data['Usuario']['it_transaccion']=formatea_monto_fromWs($usuario['limite_internas_terceros']);
			if($usuario['limite_externas']) $this->data['Usuario']['e_transaccion']=formatea_monto_fromWs($usuario['limite_externas']);                                                
                        if($usuario['limite_internas_terceros_nomina']) $this->data['Usuario']['it_transaccion_n']=formatea_monto_fromWs($usuario['limite_internas_terceros_nomina']);
			if($usuario['limite_externas_nomina']) $this->data['Usuario']['e_transaccion_n']=formatea_monto_fromWs($usuario['limite_externas_nomina']);
                        if($usuario['limite_internas_terceros_proveedores']) $this->data['Usuario']['it_transaccion_p']=formatea_monto_fromWs($usuario['limite_internas_terceros_proveedores']);
			/*Pago Nomimna Proveedores Moneda extranjera js 2021*/
			//nomina usd
			if($usuario['limite_internas_terceros_nomina']) $this->data['Usuario']['it_transaccion_n_usd']=formatea_monto_fromWs($usuario['limite_internas_terceros_nomina']);
			//Nomina eur
			if($usuario['limite_internas_terceros_nomina_eur']) $this->data['Usuario']['it_transaccion_n_eur']=formatea_monto_fromWs($usuario['limite_internas_terceros_nomina_eur']);

			//proveedores usd
			if($usuario['limite_internas_terceros_proveedores_usd']) $this->data['Usuario']['it_transaccion_p_usd']=formatea_monto_fromWs($usuario['limite_internas_terceros_proveedores_usd']);
			//proveedores eur
			if($usuario['limite_internas_terceros_proveedores_eur']) $this->data['Usuario']['it_transaccion_p_eur']=formatea_monto_fromWs($usuario['limite_internas_terceros_proveedores_eur']);

			/////////////////////////////////////////////

			//if($usuario['limite_externas_nomina']) $this->data['Usuario']['e_transaccion_n']=formatea_monto_fromWs($usuario['limite_externas_nomina']);

            if($usuario['limite_internas_terceros_proveedores']) $this->data['Usuario']['it_transaccion_p']=formatea_monto_fromWs($usuario['limite_internas_terceros_proveedores']);
			if($usuario['limite_externas_proveedores']) $this->data['Usuario']['e_transaccion_p']=formatea_monto_fromWs($usuario['limite_externas_proveedores']);
		}
	
		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		$data_session['paso_actual'] = $data_session['paso_1'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);
		$this->set('limites', $limites);
                $this->set('usuario', $usuario);
                
		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}				
	}

	/**
	 * Acción para confirmar los montos límites
	**/
	function confirmar_limites(){
		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.limite_montos');
		$estado = $this->Session->read('usuarios.registro');

		if(!isset($data_session) || !isset($data_session['asignado']) || !$data_session['asignado']){
			$this->redirect('/');
		}

		if(isset($this->data['Usuario'])) {
			if(isset($estado['Usuario']['id'])){
				$this->Usuario->id = $estado['Usuario']['id'];
			} else if($this->Session->check('usuarios.datos')){
				$u = $this->Session->read('usuarios.datos');
				$this->Usuario->id = $u['id'];
			}
			
			// Busca la info del usuario
			$this->UsuarioCanal->set(
                            $this->UsuarioCanal->find(
                                    'first',
                                    array(
                                        'conditions' => array('usuario_id'=>$this->Usuario->id, 'canal_id'=>Configure::read('canal.ib'))
                                    )
                            )
			);
			
			if(isset($estado['Usuario']['id'])){
				if(!$this->Session->check('login.juridico')){
					$this->UsuarioCanal->set('estatus_registro','A');
				}
			}
			
			if(!empty($estado['Usuario']['it_transaccion'])){
				$this->UsuarioCanal->set(
					'limite_internas_terceros',
					formatea_monto_toWs($estado['Usuario']['it_transaccion'])
				);
			} else {
				$this->UsuarioCanal->set('limite_internas_terceros',null);
			}
			
			if(!empty($estado['Usuario']['e_transaccion'])){
				$this->UsuarioCanal->set(
					'limite_externas',
					formatea_monto_toWs($estado['Usuario']['e_transaccion'])
				);
			} else {
				$this->UsuarioCanal->set('limite_externas',null);
			}
 
                        if(isset($estado['Usuario']['it_transaccion_n']) && !empty($estado['Usuario']['it_transaccion_n'])){
				$this->UsuarioCanal->set(
					'limite_internas_terceros_nomina',
					formatea_monto_toWs($estado['Usuario']['it_transaccion_n'])
				);
			} else {
				$this->UsuarioCanal->set('limite_internas_terceros_nomina',null);
		}
            if(isset($estado['Usuario']['it_transaccion_n_usd']) && !empty($estado['Usuario']['it_transaccion_n_usd'])){
				$this->UsuarioCanal->set(
					'limite_internas_terceros_nomina_usd',
					formatea_monto_toWs($estado['Usuario']['it_transaccion_n_usd'])
				);
			} else {
				$this->UsuarioCanal->set('limite_internas_terceros_nomina_usd',null);
			}
			
            if(isset($estado['Usuario']['it_transaccion_n_eur']) && !empty($estado['Usuario']['it_transaccion_n_eur'])){
				$this->UsuarioCanal->set(
					'limite_internas_terceros_nomina_eur',
					formatea_monto_toWs($estado['Usuario']['it_transaccion_n_eur'])
				);
			} else {
				$this->UsuarioCanal->set('limite_internas_terceros_nomina_eur',null);
			}

			
			if(isset($estado['Usuario']['e_transaccion_n']) && !empty($estado['Usuario']['e_transaccion_n'])){
				$this->UsuarioCanal->set(
					'limite_externas_nomina',
					formatea_monto_toWs($estado['Usuario']['e_transaccion_n'])
				);
			} else {
				$this->UsuarioCanal->set('limite_externas_nomina',null);
			}
          
                        if(isset($estado['Usuario']['it_transaccion_p']) && !empty($estado['Usuario']['it_transaccion_p'])){
				$this->UsuarioCanal->set(
					'limite_internas_terceros_proveedores',
					formatea_monto_toWs($estado['Usuario']['it_transaccion_p'])
				);
			} else {
				$this->UsuarioCanal->set('limite_internas_terceros_proveedores',null);
			}
			
            if(isset($estado['Usuario']['it_transaccion_p_usd']) && !empty($estado['Usuario']['it_transaccion_p_usd'])){
				$this->UsuarioCanal->set(
					'limite_internas_terceros_proveedores_usd',
					formatea_monto_toWs($estado['Usuario']['it_transaccion_p_usd'])
				);
			} else {
				$this->UsuarioCanal->set('limite_internas_terceros_proveedores_usd',null);
			}

            if(isset($estado['Usuario']['it_transaccion_p_eur']) && !empty($estado['Usuario']['it_transaccion_p_eur'])){
				$this->UsuarioCanal->set(
					'limite_internas_terceros_proveedores_eur',
					formatea_monto_toWs($estado['Usuario']['it_transaccion_p_eur'])
				);
			} else {
				$this->UsuarioCanal->set('limite_internas_terceros_proveedores_eur',null);
			}
			
			if(isset($estado['Usuario']['e_transaccion_p']) && !empty($estado['Usuario']['e_transaccion_p'])){
				$this->UsuarioCanal->set(
					'limite_externas_proveedores',
					formatea_monto_toWs($estado['Usuario']['e_transaccion_p'])
				);
			} else {
				$this->UsuarioCanal->set('limite_externas_proveedores',null);
			}
                                     
			$res = $this->UsuarioCanal->save(null,false);
			
			$this->UsuarioCanal->read();
			
			if($res){
				if($this->Session->check('usuarios.datos')){
					$usuario = $this->Session->read('usuarios.datos');
					$usuario['limite_internas_terceros'] = ($estado['Usuario']['it_transaccion']) ? formatea_monto_toWs($estado['Usuario']['it_transaccion']) : '';
					$usuario['limite_externas'] = ($estado['Usuario']['e_transaccion']) ? formatea_monto_toWs($estado['Usuario']['e_transaccion']) : '';
                                        
                                        if(isset($estado['Usuario']['it_transaccion_n'])){
                                            $usuario['limite_internas_terceros_nomina'] = ($estado['Usuario']['it_transaccion_n']) ? formatea_monto_toWs($estado['Usuario']['it_transaccion_n']) : '';
                                            $usuario['limite_externas_nomina'] = ($estado['Usuario']['e_transaccion_n']) ? formatea_monto_toWs($estado['Usuario']['e_transaccion_n']) : '';
                                            $usuario['limite_internas_terceros_proveedores'] = ($estado['Usuario']['it_transaccion_p']) ? formatea_monto_toWs($estado['Usuario']['it_transaccion_p']) : '';
                                            $usuario['limite_externas_proveedores'] = ($estado['Usuario']['e_transaccion_p']) ? formatea_monto_toWs($estado['Usuario']['e_transaccion_p']) : '';                                        
                                       $usuario['limite_internas_terceros_proveedores_usd'] = ($estado['Usuario']['it_transaccion_p_usd']) ? formatea_monto_toWs($estado['Usuario']['it_transaccion_p_usd']) : '';

                        $usuario['limite_internas_terceros_proveedores_eur'] = ($estado['Usuario']['it_transaccion_p_eur']) ? formatea_monto_toWs($estado['Usuario']['it_transaccion_p_eur']) : '';
$usuario['limite_internas_terceros_nomina_usd'] = ($estado['Usuario']['it_transaccion_n_usd']) ? formatea_monto_toWs($estado['Usuario']['it_transaccion_n_usd']) : '';
                       $usuario['limite_internas_terceros_nomina_eur'] = ($estado['Usuario']['it_transaccion_n_eur']) ? formatea_monto_toWs($estado['Usuario']['it_transaccion_n_eur']) : '';

                                        }
                              
					$this->Session->write('usuarios.datos',$usuario);
				}
				//Registra en Bitácora
				$this->Bitacora->registraBitacora(
					$this->UsuarioCanal->data['Usuario']['id'],
					$estado['nro_transaccion'],
					NULL,
					NULL,
					NULL,
					NULL,
					NULL,
					0
				);
				
				$data_session['exito'] = true;
				$data_session['nro_transaccion'] = $estado['nro_transaccion'];
				$this->Session->write('usuarios.limite_montos',$data_session);
								
				if(empty($estado['Usuario']['it_transaccion'])){
					$it_transaccion = _p('limite_internas_terceros');
				} else {
					$it_transaccion = formatea_monto_toWs($estado['Usuario']['it_transaccion']);
				}
				
				if(empty($estado['Usuario']['e_transaccion'])){
					$e_transaccion = _p('limite_externas');
				} else {
					$e_transaccion = formatea_monto_toWs($estado['Usuario']['e_transaccion']);
				}
                               
                                //<mguglielmelli@bancoactivo.com>
                                //Envio de SMS y Correo al cliente
                                $tipo_cambio = 'Limite de Transferencia';
                                if(isset($estado['nombre'])){
                                    $nombre_destino = desencriptar($estado['nombre']);
                                    $email = desencriptar($estado['email_principal']);
                                    $celular = desencriptar($estado['celular']);
                                }elseif (isset($estado['Usuario']['nombre'])){
                                    $nombre_destino = desencriptar($estado['Usuario']['nombre']);
                                    $email = desencriptar($estado['Usuario']['email_principal']);
                                    $celular = desencriptar($estado['Usuario']['celular']);
                                }

                                $this->set(compact('nombre_destino', 'tipo_cambio'));

                                // Email Options
                                $this->Email->smtpOptions = Configure::read('email.smtp');
                                $this->Email->delivery = 'smtp';
                                $this->Email->from = Configure::read('email.from');
                                $this->Email->to = $email;
                                $this->Email->subject = 'Cambio de Limite de Transferencia';
                                $this->Email->sendAs = 'html';

                                // Informacion del correo
                                $this->Email->template = 'cambio_datos_autenticacion';                                        

                                // Envio del correo
                                $this->Email->send();
                                                                
                                $this->Sms->enviar(
                                    array(
                                        'numcel'=>$celular,
                                        'canal1'=>'65',
                                        'fectim'=>'Limite de Transferencia',
                                        'xrnde38'=>'IB'
                                    )
                                );                                
                                
				$this->redirect('resultado_limites');
			} else {
				$this->set('error_fatal',_m('error.fatal'));
				$this->log('error al grabar los limites del usuario'+$this->Usuario->id,LOG_ERR);
				$this->log($this->UsuarioCanal->getDataSource()->error);
				
				//Registra en Bitácora
				$this->Bitacora->registraBitacora(
					$this->UsuarioCanal->data['Usuario']['id'],
					$estado['nro_transaccion'],
					NULL,
					NULL,
					NULL,
					NULL,
					NULL,
					1
				);
				
			}
			  
		} else {
			$this->data = $estado;
		}

		// Asigna Layout del contenido
		$this->layout = $data_session['layout'];

		$data_session['paso_actual'] = $data_session['paso_2'];

		// Para manipular la informacion en la vista
		$this->set('data_session', $data_session);
		$this->set('limites', $this->Usuario->obtenerLimitesMaximos());

		// si es interna marca el modulo en el menu
		if( isset($data_session['seccion_actual']) ){
			$this->set('seccion_actual', $data_session['seccion_actual']);
		}
	}

	/**
	 * Acción para mostrar el resultado de la configuración de los
	 * montos límites
	**/
	function resultado_limites(){
		// Crea una variable con los datos de sesion
		$data_session = $this->Session->read('usuarios.limite_montos');

		if(!isset($data_session) || !isset($data_session['exito']) || !$data_session['exito']){
			$this->redirect('/');
		}

		if(isset($this->data['Usuario'])) {
			$this->redirect($data_session['url_aceptar']);
		} else {
			// Asigna Layout del contenido
			$this->layout = $data_session['layout'];

			$data_session['paso_actual'] = $data_session['paso_3'];

			// Para manipular la informacion en la vista
			$this->set('data_session', $data_session);

			// si es interna marca el modulo en el menu
			if( isset($data_session['seccion_actual']) ){
				$this->set('seccion_actual', $data_session['seccion_actual']);
			}
		}
	}

	/**
	 * Acción para mostrar el resultado del registro
	**/
	function resultado(){
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');
		if(!$this->Session->check('usuarios.config_sello')) $this->redirect('/');
		if(!$this->Session->check('usuarios.asignar_preguntas')) $this->redirect('/');
		if(!$this->Session->check('usuarios.limite_montos')) $this->redirect('/');

		$estado = $this->Session->read('usuarios.registro');
		$estado_config_sello = $this->Session->read('usuarios.config_sello');
		$estado_asignar_preguntas = $this->Session->read('usuarios.asignar_preguntas');
		$estado_limite_montos = $this->Session->read('usuarios.limite_montos');

		if(
			!isset($estado['Usuario']['usuario_creado']) ||
			!$estado['Usuario']['usuario_creado'] ||
			!isset($estado_config_sello['exito']) ||
			!$estado_config_sello['exito'] ||
			!isset($estado_asignar_preguntas['exito']) ||
			!$estado_asignar_preguntas['exito'] ||
			!isset($estado_limite_montos['exito']) ||
			!$estado_limite_montos['exito'] ||
			!empty($estado['Usuario']['tipo_rif'])
		){
			$this->redirect('/');
		}

		// Enviar por sms la notificación de que ya se realizó el registro
		if( isset($estado['Usuario']['codigoCelular']) && isset($estado['Usuario']['nroCelular']) ){
			$this->Bitacora->registraBitacora($estado['Usuario']['id'],100043);
			$this->Sms->enviar(
				array(
					'numcel'=>$estado['Usuario']['codigoCelular'].$estado['Usuario']['nroCelular'],
					'canal1'=>'30',
				)
			);
		}

		// busqueda del usuario
		$login = $this->UsuarioCanal->encontrarUsuario( array('usuario_id' => $estado['Usuario']['id']) );
		
		if($login){
			$this->UsuarioCanal->changeLogged(1, $estado['Usuario']['id']);
			$this->Session->write('usuarios.lastlogged', date('d/m/Y h:ia'));
			$this->Session->write('usuarios.loggedin', true);		
			$this->Session->write('usuarios.datos',$login['Usuario']);
			$this->Bitacora->registraBitacora($login['Usuario']['id'],1);
			
			// Transacciones del perfil del usuario
			$t = $this->TransaccionPerfil->obtenerTransPerfil($login['Usuario']['perfil_ib_id'],$login['Usuario']['id']);
			$this->Session->write('usuarios.transacciones', $t);
		}

		$this->data = $estado;
		$this->Session->delete('usuarios.registro');
		$this->Session->delete('usuarios.config_sello');
		$this->Session->delete('usuarios.asignar_preguntas');
		$this->Session->delete('usuarios.limite_montos');
		$this->Session->delete('seguridad.validar_preguntas_autenticacion');
		$this->layout = "externa";
	}

	
	/**
	 * Acción para mostrar el resultado del registro para jurídicos
	 */
	function resultado_juridico(){
                
                if(!$this->Session->check('usuarios.registro')) $this->redirect('/');
		if(!$this->Session->check('usuarios.config_sello')) $this->redirect('/');
		if(!$this->Session->check('usuarios.asignar_preguntas')) $this->redirect('/');
		if(!$this->Session->check('usuarios.limite_montos')) $this->redirect('/');

		$estado = $this->Session->read('usuarios.registro');
		$estado_config_sello = $this->Session->read('usuarios.config_sello');
		$estado_asignar_preguntas = $this->Session->read('usuarios.asignar_preguntas');
		$estado_limite_montos = $this->Session->read('usuarios.limite_montos');
                $pendientes = $this->Session->read('pendientes');
                $estatus = $estado['Usuario']['registroIB'];
                
		if(
			!isset($estado['Usuario']['usuario_creado']) ||
			!$estado['Usuario']['usuario_creado'] ||
			!isset($estado_config_sello['exito']) ||
			!$estado_config_sello['exito'] ||
			!isset($estado_asignar_preguntas['exito']) ||
			!$estado_asignar_preguntas['exito'] ||
			!isset($estado_limite_montos['exito']) ||
			!$estado_limite_montos['exito']
		){
			$this->redirect('/');
		}
		
		if($estado['Usuario']['perfil']>2){
			$this->Session->delete('usuarios.registro');
			$this->Session->delete('usuarios.config_sello');
			$this->Session->delete('usuarios.asignar_preguntas');
			$this->Session->delete('usuarios.limite_montos');
		}
		else{
                    // busqueda del usuario
                    $login = $this->UsuarioCanal->encontrarUsuario( array('usuario_id' => $estado['Usuario']['id']) );
                    if($login && $estatus=='A'){
                            $this->Session->delete('usuarios.registro');
                            $this->Session->delete('usuarios.config_sello');
                            $this->Session->delete('usuarios.asignar_preguntas');
                            $this->Session->delete('usuarios.limite_montos');

                    }
 		}
                
		$this->data = $estado;
		$this->layout = "externa";
		$this->set('data_session', $estado_config_sello);
		$this->set('pendientes', $pendientes);
		$this->set('estatus_registro', $estatus);
	}
	
	/**
	 * Acción para mostrar cuando ocurre un error en el proceso de registro
	 */
	function registro_error(){
		
		if(!$this->Session->check('usuarios.error')) $this->redirect('/');
		
		$error = $this->Session->read('usuarios.error');
		
		$this->set(compact('error'));
		
		$this->layout = "externa";
		
	}

	
	/**
	 * Acción final en el registro de un jurídico
	**/
	function resultado_juridico_fin(){
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');
		if(!$this->Session->check('usuarios.config_sello')) $this->redirect('/');
		if(!$this->Session->check('usuarios.asignar_preguntas')) $this->redirect('/');
		if(!$this->Session->check('usuarios.limite_montos')) $this->redirect('/');

		$estado = $this->Session->read('usuarios.registro');
		$estado_config_sello = $this->Session->read('usuarios.config_sello');
		$estado_asignar_preguntas = $this->Session->read('usuarios.asignar_preguntas');
		$estado_limite_montos = $this->Session->read('usuarios.limite_montos');

		if(
			!isset($estado['Usuario']['usuario_creado']) ||
			!$estado['Usuario']['usuario_creado'] ||
			!isset($estado_config_sello['exito']) ||
			!$estado_config_sello['exito'] ||
			!isset($estado_asignar_preguntas['exito']) ||
			!$estado_asignar_preguntas['exito'] ||
			!isset($estado_limite_montos['exito']) ||
			!$estado_limite_montos['exito']
		){
			$this->redirect('/');
		}

		$this->data = $estado;
		$this->layout = "externa";
		
		$this->Session->delete('usuarios.registro');
		$this->Session->delete('usuarios.config_sello');
		$this->Session->delete('usuarios.asignar_preguntas');
		$this->Session->delete('usuarios.limite_montos');
	}
	
	/**
	 * Acción para descargar la planilla de registro de los usuarios jurídicos
	**/                
	function descargar_planilla($recuperar=null){
                       
            //Verifica si el usuario esta recuperando la planilla o se esta registrando por primera vez en la nueva banca en linea
            if(!isset($recuperar) || empty($recuperar)) {
                
		if(!$this->Session->check('usuarios.registro')) $this->redirect('/');
		if(!$this->Session->check('usuarios.config_sello')) $this->redirect('/');
		if(!$this->Session->check('usuarios.asignar_preguntas')) $this->redirect('/');
		if(!$this->Session->check('usuarios.limite_montos')) $this->redirect('/');
                                
		$estado = $this->Session->read('usuarios.registro');
		$estado_config_sello = $this->Session->read('usuarios.config_sello');
		$estado_asignar_preguntas = $this->Session->read('usuarios.asignar_preguntas');
		$estado_limite_montos = $this->Session->read('usuarios.limite_montos');
		$estado['Usuario']['razon'] = $estado['DatosBasicos']['INTLNADB'];
                
		if(
			!isset($estado['Usuario']['usuario_creado']) ||
			!$estado['Usuario']['usuario_creado'] ||
			!isset($estado_config_sello['exito']) ||
			!$estado_config_sello['exito'] ||
			!isset($estado_asignar_preguntas['exito']) ||
			!$estado_asignar_preguntas['exito'] ||
			!isset($estado_limite_montos['exito']) ||
			!$estado_limite_montos['exito']
		){
			$this->redirect('/');
		}
            }else{                
                
                if(!$this->Session->check('usuarios.login')) $this->redirect('/');                
                $datos_registro = $this->Session->read('usuarios.login');
                $datos_basicos = $this->Activo->invoca(
				'NIBPF3010',
				array(
					'req'=>array(
                          		'NACIONALIDAD'=>$datos_registro['tipo_rif'],
			         	'NROIDENTIFICACION'=>str_pad(desencriptar($datos_registro['rif']),12,'0',STR_PAD_LEFT),
					'NROPARAM'=>'1'
					)
				)
			);
							        
                $estado = array();
                //Lleno el arreglo con la informacion del cliente para generar el PDF
                if($datos_basicos){
                    if(isset($datos_basicos['NIBPF3010Return']['error']))
                    {                            
                            $this->redirect('/');
                    } else {                            
                            $estado['Usuario']['razon'] = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];                            			
                    }
	        }
                $estado['Usuario']['tipoRif'] = $datos_registro['tipo_rif'];
                $estado['Usuario']['nroRif' ] = desencriptar($datos_registro['rif']);
                $estado['Usuario']['nombre'] = desencriptar($datos_registro['nombre']);
                $estado['Usuario']['tipoCedulaRif'] = $datos_registro['tipo_cedula'];
                $estado['Usuario']['nroCedulaRif'] = desencriptar($datos_registro['cedula']);
                $estado['Usuario']['cargo'] = desencriptar($datos_registro['cargo']);
                $estado['Usuario']['emailPri'] = desencriptar($datos_registro['email_principal']);                
                $estado['Usuario']['codigoCelular'] = substr(desencriptar($datos_registro['celular']),0,4);
                $estado['Usuario']['nroCelular'] = substr(desencriptar($datos_registro['celular']),5,7);
                $estado['Usuario']['perfil'] = $datos_registro['rol_juridico_id'];               
                
            }
		$this->set(compact('estado'));
		
		$this->layout = "externa";
	}

	/**
	 * Función para obtener banners
	 * @param string $seccion identificador de la sección
	 * @param string $id identificador del banner
	**/
	function obtener_banner($seccion=null,$id=null){
		
		if(empty($seccion) || empty($id)) exit;
		
		$banners = $this->Banners->porSeccion($seccion);

		foreach ($banners as $b) {
			if($b['Banners']['id'] == $id){
				$img = $b['Banners']['archivo_1'];
				break;
			}
		}
		
		if(isset($img)){
			header('Content-Type: image/gif');
			header('Content-Length: '.strlen($img));
			echo $img;
		}
		
		$this->layout='nada';
		
		exit();
	}
	
	/**
	 * Acción que se ejecuta por AJAX para indicar que el usuario
	 * reconoce la imagen de seguridad presentada en el formulario
	 * que solicita la contraseña
	**/
	function confirmar_imagen(){
		$data = $this->params['form'];
		
		if(isset($data['usuario_id'])){
			if(empty($data['sello_id'])){
				$descripcion = 'Imagen Personalizada';
			} else {
				$descripcion = 'Imagen '.desencriptar($data['sello_id']);
			}
			
			$this->Bitacora->registraBitacora(
				desencriptar($data['usuario_id']),
				44,
				null, null, null, null,
				$descripcion
			);
			
			echo 'ok';
		} else {
			echo 'nok';
		}
		
		$this->layout = 'ajax';
		
		exit;
	}
	
	/**
	 * Acción para mostrar mensaje de usuario bloqueado
	**/
	function bloqueo_usuario(){
		if(!$this->Session->check('seguridad.olvido')){
			$this->redirect('/');
		} else {
			$estado = $this->Session->read('seguridad.olvido');
		}

		$this->layout = 'externa';

		$this->set('estado',$estado);

	}
	
	/**
	 * Acción para mostrar mensaje de doble sesión al usuario
	**/
	function doble_session(){
		$datos = $this->Session->read('usuarios.datos');
		if(!empty($datos)){
			// Actualiza los campos de conexion
			$this->UsuarioCanal->changeLogged(0, $datos['id']);
			
			// Registra el log
			$this->Bitacora->registraBitacora($datos['id'],26);
		}
		
		$this->layout = 'externa';
		
		$this->Session->destroy();
		
		if($datos['tipo_rif']) $this->Session->write('login.juridico', 'J');
		
	}
	
	/**
	 * Acción que se ejecuta cuando el usuario cierra el explorador de internet sin
	 * cerrar sesión
	**/
	function closeExplorer(){
		$this->layout = 'ajax';
		
		$datos = $this->Session->read('usuarios.datos');
		
		if(!empty($datos)){
			// Actualiza los campos de conexion
			$this->UsuarioCanal->changeLogged(0, $datos['id']);
			
			// Registra el log
			$this->Bitacora->registraBitacora($datos['id'],2);
		}
		
		return null;
	}
	
	/**
	 * Acción utilitaria para resetear el contador de fin de sesión del usuario
	 */
	function ping(){
		$this->layout = 'ajax';
		
		echo 'pong';
		
		die();
		
	}
	
	/**
	 * Acción que se ejecuta cuando el usuario jurídico es bloqueado
	**/
	function bloqueo_juridico(){
		if(!$this->Session->check('usuarios.login')) $this->redirect('/');

		$this->Session->delete('usuarios');

		$this->layout = "externa";
	}
        
        /**
	 * Acción para mostrar un mensaje y descargar la planilla a los usuarios Juridicos Pendientes
	**/
	function estatus_pendiente_planilla(){
                        
            $this->layout = "externa";
	}

        /**
	* Acción que permite finalizar el registro de los usuarios Juridicos, se llama desde la acción 
         * de confirmar limites para perfil 3,4 y 5 y desde el solicitudes_controllers para los perfiles 1 y 2
	**/
        function finalizar_registro_juridico(){
                
		$estado = $this->Session->read('usuarios.registro');
		
		$pendientes = array();

                // Verifica si es un usuario migrado 
		// o un usuario con registro incompleto que NO sea master/unico
		if( 
			isset($estado['legacy']) || 
			(
				isset($this->data['Usuario']['registro_incompleto']) && 
				$estado['Usuario']['ibuser'] && 
				$estado['Usuario']['perfil'] != 2
			) 
		){
                    
                        $res = $this->MigracionJuridico->usuariosEmpresa($estado['Usuario']['tipoRif'],$estado['Usuario']['nroRif'],$estado['Usuario']['ibuser']);
                        $res &= $this->MigracionJuridico->guardaBeneficiarios($estado['Usuario']['tipoRif'],$estado['Usuario']['nroRif']);
                        $res &= $this->MigracionJuridico->guardaHistorial($estado['Usuario']['tipoRif'],$estado['Usuario']['nroRif'],$estado['Usuario']['ibuser'], $estado['Usuario']['id']);
                        $res &= $this->MigracionJuridico->pendientesPorAprobar($estado['Usuario']['tipoRif'],$estado['Usuario']['nroRif'],$estado['Usuario']['ibuser'], $estado['Usuario']['id']);
                        $res &= $this->MigracionJuridico->pagosPendientes($estado['Usuario']['tipoRif'],$estado['Usuario']['nroRif'],$estado['Usuario']['ibuser'], $estado['Usuario']['id'], 'n');
                        $res &= $this->MigracionJuridico->pagosPendientes($estado['Usuario']['tipoRif'],$estado['Usuario']['nroRif'],$estado['Usuario']['ibuser'], $estado['Usuario']['id'], 'p');

                        if( !$res ){
				
				// Si es un registro incompleto no realiza ningun cambio
				if(isset($estado['legacy'])){
				
					// Elimina el registro de la tabla usuario canal
					$this->UsuarioCanal->deleteAll(
						array(
							'usuario_id'=>$estado['Usuario']['id'],
							'canal_id'=>Configure::read('canal.ib')
						)
					);

					// Actualiza el registro de la tabla usuario
					$this->Usuario->save(
						array(
							'Usuario'=>array(								
								'recibe_notificaciones'=>NULL,
								'login'=>NULL,								
								'fecha_hora_creacion'=>NULL,								
								'id'=>$estado['Usuario']['id']
							)
						),false
					);
				}
				
				$this->Idg->anularTdc($estado['Usuario']['tipoRif'].$estado['Usuario']['nroRif'].$estado['Usuario']['tipoCedulaRif'].$estado['Usuario']['nroCedulaRif'].$estado['Usuario']['nomUsuario']);
				$error= array(
					'mensaje' => _m('error.fatal')
				);
				$this->Session->write('usuarios.error', $error);
				$this->redirect('registro_error');
			}
			else{

				// Verifica si existe el usuario Master
				$master = $this->UsuarioCanal->encontrarUsuario( 
					array(
						'Usuario.tipo_rif' => $estado['Usuario']['tipoRif'],
						'Usuario.rif' => encriptar($estado['Usuario']['nroRif']),
						'Usuario.rol_juridico_id' => 1,
					)
				);
				
                                if($estado['Usuario']['perfil']>2 && $master){
					// Email Options
					$this->Email->smtpOptions = Configure::read('email.smtp');
					$this->Email->delivery	= 'smtp';
					$this->Email->from		= Configure::read('email.from');
					$this->Email->to		= desencriptar($master['Usuario']['email_principal']);
					$this->Email->subject	= 'Notificacion de Registro';
					$this->Email->sendAs	= 'html';

					// Perfil del usuario
					$perfiles = $this->RolJuridico->listadoRoles();
					$perfil = $perfiles[$estado['Usuario']['perfil']];

					// Informacion del correo
					$this->Email->template	= 'registro_migrado';
					$this->set(compact('estado','master','perfil'));

					// Envio del correo
					$this->Email->send();
				}

			}
		}
			
		// Actualiza el estatus de registro
		if($estado['Usuario']['perfil']>2){
			$estatus = 'A';
			
                        // Verifica si es un usuario creado o si es un usuario migrado con el estatus activo
			if( !isset($estado['Usuario']['accesoIB']) || (isset($estado['Usuario']['accesoIB']) && $estado['Usuario']['accesoIB']=='A') ){
				
				// busqueda del usuario
				$login = $this->UsuarioCanal->encontrarUsuario( array('usuario_id' => $estado['Usuario']['id']) );
				if($login){
					$this->UsuarioCanal->changeLogged(1, $estado['Usuario']['id']);
					$this->Session->write('usuarios.lastlogged', date('d/m/Y h:ia'));
					$this->Session->write('usuarios.loggedin', true);		
					$this->Session->write('usuarios.datos',$login['Usuario']);
					$this->Bitacora->registraBitacora($login['Usuario']['id'],1);

					// Transacciones del perfil del usuario
					$t = $this->TransaccionJuridico->obtenerTransUsuario($estado['Usuario']['id']);
					$this->Session->write('usuarios.transacciones', $t);

					if(in_array(36, $t)){
						$pendientes = $this->TransaccionPendiente->obtenerAprobacionesPendientes($login['Usuario']);
					}
				}
			}
		}
		else{
                         // Si viene de migracion o registro incompleto, que NO sea master unico
			 if( 
				isset($estado['legacy']) || 
				(
					isset($this->data['Usuario']['registro_incompleto']) && 
					$estado['Usuario']['ibuser'] && 
					$estado['Usuario']['perfil'] != 2
				) 
			){
                                
                                $estatus = isset($estado['Usuario']['registroIB']) ? $estado['Usuario']['registroIB'] : 'A';
				
				// busqueda del usuario
				$login = $this->UsuarioCanal->encontrarUsuario( array('usuario_id' => $estado['Usuario']['id']) );
				if($login && $estatus=='A'){
					$this->UsuarioCanal->changeLogged(1, $estado['Usuario']['id']);
					$this->Session->write('usuarios.lastlogged', date('d/m/Y h:ia'));
					$this->Session->write('usuarios.loggedin', true);		
					$this->Session->write('usuarios.datos',$login['Usuario']);
					$this->Bitacora->registraBitacora($login['Usuario']['id'],1);

					// Transacciones del perfil del usuario
					$t = $this->TransaccionJuridico->obtenerTransUsuario($estado['Usuario']['id']);
					$this->Session->write('usuarios.transacciones', $t);
					
					if(in_array(36, $t)){
						$pendientes = $this->TransaccionPendiente->obtenerAprobacionesPendientes($login['Usuario']);
					}
				}
				else{
					// En caso de tener un estatus de registro pendiente elimina el indicador de migrado
					unset($estado['legacy']);
				}
			 }
			 else{
				 $estatus = 'P';
			 }
		}
                $estado['Usuario']['registroIB'] = $estatus;
                
		$this->UsuarioCanal->cambioEstatus('registro', $estatus, $estado['Usuario']['id']);
		
		$this->Session->write('usuarios.registro', $estado);
                $this->Session->write('pendientes', $pendientes);
	}
        
        /**
	 * Acción para iniciar el proceso de desbloqueo por inactividad
         * MGGY
	**/
	function bloqueo_inactividad(){
		
                $this->layout = "externa";
		$valida=false;
                
		if( $this->Session->check('login.juridico') ){ // PARA JURIDICO
                    $this->Session->write('seguridad.validar_preguntas_autenticacion', array('validado'=>true));
		}                               
               
		$estado_autenticacion = $this->Session->read('seguridad.validar_preguntas_autenticacion');
		$estado_desafio = $this->Session->read('seguridad.validar_preguntas_desafio');
		$estado_olvido = $this->Session->read('seguridad.olvido');		
                
		if(!$this->Session->check('seguridad.olvido')){
                    $this->redirect('/');
		} elseif(!isset($estado_olvido['cedula.validada'])){
                    $this->redirect('/');
		} elseif((isset($estado_autenticacion['validado']) || $estado_autenticacion['validado']) && (isset($estado_desafio['validado']) || $estado_desafio['validado'])){
                    
                    $this->UsuarioCanal->cambioEstatus('acceso', 'A', $estado_olvido['datasource']['id']);
                    $valida=true;
                    
                    // Enviar por sms la notificación de que ya se realizó el registro
                    if(isset($estado_olvido['datasource']['celular'])){			
			$this->Sms->enviar(
				array(
					'numcel'=>desencriptar($estado_olvido['datasource']['celular']),
					'canal1'=>'60',
                                        'xrnde38'=>'IB',
                                        'xrnde11'=>date('His')
				)
			);
                    }
                    
                    // Email Options
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from 	= Configure::read('email.from');
                    $this->Email->to = desencriptar($estado_olvido['datasource']['email_principal']);
                    $this->Email->subject = 'Notificacion Desbloqueo de Usuario';
                    $this->Email->sendAs = 'html';
                    
                    // Informacion del correo
                    $this->Email->template = 'desbloqueo_inactividad';
                    $this->set(compact('estado_olvido'));

                    // Envio del correo
                    $this->Email->send();
                    
                    $this->Bitacora->registraBitacora($estado_olvido['datasource']['id'],100056);
                }
                
                if(!$this->Session->check('login.juridico')){
                    // Colocar data para el manejador de preguntas de seguridad
                    $this->Session->write(
                            'seguridad.validar_preguntas_autenticacion',
                            array(
                                    'url_aceptar'=>'validar_preguntas_desafio',
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
                }                			

                // Colocar data para el manejador de preguntas de desafio
                $this->Session->write(
                        'seguridad.validar_preguntas_desafio',
                        array(
                                'url_aceptar'=>'/usuarios/bloqueo_inactividad',
                                'url_volver'=>'/',
                                'layout'=>'externa',
                                'titulo'=>$estado_olvido['titulo'],
                                'cantidad_preguntas_desafio'=>3,
                                'pasos'=> $estado_olvido['pasos'],
                                'paso_actual'=>$estado_olvido['paso_desafio'],
                                'usuario'=>$estado_olvido['datasource']
                        )
                );
									
		$this->set('estado', $estado_olvido);
                $this->set('valida', $valida);
	}
        
        /**
	 * Acción para mostrar la pagina de mantenimiento
	**/
	function mantenimiento(){
				
		$datos = $this->Session->read('usuarios.datos');
		if(!empty($datos)){
			// Actualiza los campos de conexion
			$this->UsuarioCanal->changeLogged(0, $datos['id']);						
}
		
		$this->layout = 'externa';
		
		$this->Session->destroy();
		
	}

	function validarEmail($email) {
    return (filter_var($email, FILTER_VALIDATE_EMAIL)) ? true : false;
	}

	function sanitize_input($data) {
        return filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }

        
}
?>
