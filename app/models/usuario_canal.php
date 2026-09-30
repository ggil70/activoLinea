<?php
/**
 * Modelo para el manejo de la información del usuario por el canal.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de la información del usuario por el canal.
 * 
 */
class UsuarioCanal extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'usuarios_canales';

	
	/**
	 * Indica las tablas con las que tiene una relación foránea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'Usuario' => array( 
			'className' => 'Usuario',
			'foreignKey' => 'usuario_id'
		)
	);
	
	
	/**
	 * Para buscar un usuario dada las condiciones necesarias.
	 * 
	 * @param array $condiciones
	 * @param int $canal
	 * @return array
	 */
	function encontrarUsuario($condiciones, $canal=''){
		
		// Se desgina el canal
		$condiciones['canal_id'] = ($canal=='') ? Configure::read('canal.ib') : $canal;
		
		$this->Usuario->bindModel(array(
			'belongsTo'=>array(
				'RolJuridico'=>array(
					'className'=>'RolJuridico',
					'foreignKey'=>'rol_juridico_id'
				)
			)
		));
		
		// Realiza la busqueda
		$result = $this->find(
			'first',
			array(
				'contain'=>array('Usuario'=>array('RolJuridico')),
				'conditions'=>$condiciones
			)
		);
		
		$this->Usuario->unbindModel(array(
			'belongsTo'=>array('RolJuridico')
		));
		
		// Hace un merge si hay resultado
		if($result){
			$arrayTemp['Usuario'] = array_merge($result['UsuarioCanal'],$result['Usuario']);
			if(isset($result['Usuario']['RolJuridico']['nombre'])) $arrayTemp['Usuario']['nombre_perfil'] = $result['Usuario']['RolJuridico']['nombre'];
			$result = $arrayTemp;
		}
		
		return $result;
	}
	
	
	/**
	 * Cambia el estatus del usuario
	 * 
	 * @param string $tipo ejem: acceso, registro, tdc
	 * @param string $valor
	 * @param int $idUser
	 * @return boolean
	 */
	function cambioEstatus($tipo, $valor, $idUser){
		if($idUser){
			if($tipo=='tdc'){
				return $this->Usuario->updateAll(
				   array(
					   'estatus_tdc' => "'".$valor."'"
				   ),
				   array(
					   'id' => $idUser
				   )
			   );
			}
			else{
				return $this->updateAll(
				   array(
					   'estatus_'.$tipo => "'".$valor."'"
				   ),
				   array(
						'usuario_id' => $idUser,
						'canal_id'=>Configure::read('canal.ib')
				   )
			   );
			}
		}
		return FALSE;
	}

	
	/**
	 * Bloqueo del registro del usuario
	 * 
	 * @param char $tipo_cedula
	 * @param string $cedula
	 * @return ID del registro del usuario
	 */
	function bloquearRegistro($tipo_cedula, $cedula){
		
		// Realiza la busqueda en la tabla usuario
		$usuario = $this->Usuario->find(
			'first',
			array(
				'conditions'=>array(
					'tipo_cedula'=>$tipo_cedula,
					'cedula'=>encriptar($cedula),
					'tipo_rif is null'
				)
			)
		);
		
		if($usuario){
			
			// Realiza la busqueda en la tabla usuario canal
			$usuario_canal = $this->find(
				'first',
				array(
					'conditions'=>array(
						'usuario_id'=>$usuario['Usuario']['id'],
						'canal_id'=>Configure::read('canal.ib')
					)
				)
			);
			
			if($usuario_canal){
				$this->id = $usuario_canal['UsuarioCanal']['id'];
			}
			
			// Actualiza o crea el registro del usuario
			$x = $this->save(
				array(
					'UsuarioCanal'=>array(
						'estatus_registro'=>'B',
						'usuario_id'=>$usuario['Usuario']['id'],
						'canal_id'=>Configure::read('canal.ib')
					)
				)
			);
			
			return $usuario['Usuario']['id'];
			
		} else {
			
			App::import('Component', 'Session');
			$session = new SessionComponent();
			$estado = $session->read('usuarios.registro');
			
			$campos = array(
				'tipo_cedula'=>$tipo_cedula,
				'cedula'=>encriptar($cedula)
			);
			
			if(isset($estado['DatosBasicos'])){
				$campos['nombre'] = encriptar(trim($estado['DatosBasicos']['PRIMERNOMBRE']).' '.trim($estado['DatosBasicos']['SEGUNDONOMBRE']).' '.trim($estado['DatosBasicos']['PRIMERAPELLIDO']).' '.trim($estado['DatosBasicos']['SEGUNDOAPELLIDO']));
				$campos['celular'] = encriptar($estado['DatosBasicos']['NROCELULAR']);
				$campos['email_principal'] = encriptar($estado['DatosBasicos']['DISPONIBLE3']);
			}
			
			// Almacena el registro del usuario
			if($this->Usuario->save(array('Usuario'=>$campos), false)){
			
				// Almacena el registro del usuario por canal
				if(!$this->save(
					array(
						'UsuarioCanal'=>array(
							'estatus_registro'=>'B',
							'usuario_id'=>$this->Usuario->id,
							'canal_id'=>Configure::read('canal.ib')
						)
					)
				)){
					// si falla borra el registro del usuario
					$this->Usuario->delete($this->Usuario->id);
					return false;
				}
			}
			else{
				$this->log($this->getDataSource()->error, LOG_ERROR);
			}
			
			return $this->Usuario->id;
		}
	}
	
	
	/**
	 * Actualiza al usuario como logueado
	 * 
	 * @param int $valor
	 * @param int $idUser
	 */
	function changeLogged($valor, $idUser){
		$this->updateAll(
			array(
				'inicio_sesion'=>$valor,
				'fecha_hora_ultima_interaccion' => "'".date('Y-m-d H:i:s')."'"
			),
			array(
				'usuario_id'=>$idUser,
				'canal_id'=>Configure::read('canal.ib')
			)
		);
	}
	
	
	/**
	 * Actualiza el último tiempo de interacción
	 * 
	 * @param array $idUser
	 * @param int $limite
	 * @return boolean
	 */
	function sessTime($idUser, $limite){
		if($fecha = $this->find(
			'first',
			array(
				'fields' => array ('fecha_hora_ultima_interaccion'),
				'conditions'=>array(
					'usuario_id' => $idUser,
					'canal_id' => Configure::read('canal.ib')
				)
			),
			false
		)){
			$last_date = strtotime($fecha['UsuarioCanal']['fecha_hora_ultima_interaccion']);
			$actual = strtotime(date("Y-m-d H:i:s"));
			$minutos = date('i',$actual-$last_date);

			// Verifica si ha transcurrido el tiempo de sesion
			if($minutos>$limite){
				return false;
			}
		}

		$this->updateAll(
			array(
				'fecha_hora_ultima_interaccion' => "'".date('Y-m-d H:i:s')."'"
			),
			array(
				'usuario_id'=>$idUser,
				'canal_id' => Configure::read('canal.ib')
			)
		);
		
		return true;
	}
	
	
	
	/**
	 * Busca los datos del usuario por medio de la cedula en el proceso de olvido
	 * 
	 * @return array
	 */
	public function userByCedula(){
		$data = $this->Usuario->data['Usuario']['Olvido'];
		$result = $this->find(
			'first',
			array(
				'conditions'=>array(
					'tipo_cedula' => $data['tipoCedulaRif'],
					'cedula' => encriptar($data['nroCedulaRif']),
                                        'perfil_ib_id'=>1,
					'canal_id' => Configure::read('canal.ib')
				)
			),
			false
		);
		
		// Hace un merge si hay resultado
		if($result){
			$arrayTemp['Usuario'] = array_merge($result['UsuarioCanal'],$result['Usuario']);
			$result = $arrayTemp;
		}
		
		return $result['Usuario'];
	}
	
	
	
	
	/**
	 * Busca los datos del usuario por medio del rif y la cedula en el proceso
	 * de olvido.
	 * 
	 * @return array
	 */
	public function userByRifCedula(){
		$data = $this->Usuario->data['Usuario']['Olvido'];
		
		// condiciones para la busqueda
		$condiciones = array(
			'tipo_rif' => $data['tipoRif'],
			'rif' => encriptar($data['nroRif']),
			'tipo_cedula' => $data['tipoCedulaRif'],
			'cedula' => encriptar($data['nroCedulaRif']),
			'canal_id' => Configure::read('canal.ib')
		);
		
		// si no es olvido de usuario toma el login como parametro de busqueda
		if($data['tipo']!='usuario'){
			$condiciones['login']= encriptar($data['loginUsuario']);
		}
		
		// Realiza la busqueda del usuario
		$result = $this->find(
			'first',
			array('conditions'=>$condiciones),
			false
		);
		
		// Hace un merge si hay resultado
		if($result){
			$arrayTemp['Usuario'] = array_merge($result['UsuarioCanal'],$result['Usuario']);
			$result = $arrayTemp;
		}
		
		return $result['Usuario'];
	}
	
}

?>