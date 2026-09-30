<?php
/**
 * Modelo para el manejo de la información de los usuarios creados por un 
 * jurídico master.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de la información de los usuarios creados por un jurídico master.
 * 
 */
class Empresa extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = false;
	

	
	/**
	 * Indica las tablas con las que se trabajaran en el modelo.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'Usuario' => array( 
			'className' => 'Usuario',
			'foreignKey' => 'usuario_id'
		),
		'UsuarioCanal' => array( 
			'className' => 'UsuarioCanal',
			'foreignKey' => 'usuario_id'
		)
	);

	
	/**
	 * Estatus de registro.
	 * 
	 * @var array
	 */
	var $estatusRegistro = array('DD'=>'Suspendido', 'ST'=>'Suspendido Temporal', 'B'=>'Bloqueado','I1'=>'Inactivo','A'=>'Activo');
	
	
	/**
	 * Estatus de acceso
	 * 
	 * @var array
	 */
	var $estatus = array('A'=>'Activo', 'P'=>'Pendiente', 'I1'=>'Eliminado', 'BC'=>'Bloqueado', 'BD'=>'Bloqueado', 'I'=>'Inactivo', ''=>'PENDIENTE POR REGISTRO', 'DS'=>'Bloqueado por doble sesión');
	
	
	/**
	 * Genera una clave aleatoria
	 * 
	 * @return string
	 */
	function generaClave(){

		// Caracteres a utilizar para generar la clave
		$caracteres = str_shuffle('abcdefghijkmnpqrstuvwxyz');
		
		// Sino cumple con las condiciones de la contraseña la vuelve a generar
		do{
			// Concatena los caracteres al azar
			$cadena= '';
			for($i = 0; $i < 7; $i++){
				$key = rand(0,strlen($caracteres)-1);
				$cadena .= $caracteres[$key];
			}
			$cadena .= rand(2,9);
		}while(!$this->contrasena($cadena));
		
        return $cadena;
    }
	
	
	/**
	 * Listado de usuarios jurídicos
	 * 
	 * @param string $tipoRif
	 * @param string $rif
	 * @param int $offset
	 * @return array
	 */
	public function listadoUsuariosJuridicos($tipoRif, $rif, $offset){

		$listado = $this->Usuario->find(
			'all',
			array(
				'fields'=>array(
					'Usuario.id','Usuario.login','Usuario.nombre','Usuario.fecha_hora_creacion','Usuario.fecha_hora_modificacion','Usuario.ibuser',
					'rj.nombre','rj.id',
					'usuarioCanal.estatus_registro','usuarioCanal.estatus_acceso'
				),
				'joins' => array(
					array(
						'table' => 'roles_juridicos',
						'alias' => 'rj',
						'type' => 'LEFT',
						'conditions' => array(
							'rj.id = Usuario.rol_juridico_id'
						)
					),
					array(
						'table' => 'usuarios_canales',
						'alias' => 'usuarioCanal',
						'type' => 'LEFT',
						'conditions' => array(
							'usuarioCanal.usuario_id = Usuario.id'
						)
					)
				),
				'conditions'=>array(
					'Usuario.tipo_rif' => $tipoRif,
					'Usuario.rif' => $rif,
					'or'=>array(
						'and'=>array(
								'usuarioCanal.estatus_acceso is null',
								'usuario.rol_juridico_id not in (1,2)' // para no mostrar master/masterunico eliminado
						),
						'usuarioCanal.estatus_acceso <>'=>'I1'
					)
				),
				'order'=>'Usuario.id asc',
				'offset'=>$offset,
				'limit'=>Configure::read('Tamano.paginacion')
			)
		);
		return $listado;
	}
	
	
	/**
	 * Retorna los datos de un usuario perteneciente a la empresa.
	 * 
	 * @param int $id
	 * @param varchar $tipo_rif
	 * @param varchar $rif
	 * @return array
	 */
	public function buscarUsuario($id, $tipo_rif, $rif){

		// busqueda del usuario
		$result = $this->Usuario->find(
			'first',
			array(
				'fields'=>array(
					'id', 'nombre', 'tipo_cedula AS tipoCedulaRif', 'cedula AS nroCedulaRif',
					'login AS nomUsuario', 'email_principal AS emailPri', 'celular', 
					'rol_juridico_id AS perfil', 'cargo', 'ibuser', 'UsuarioCanal.estatus_acceso AS co_estado'
				),
				'joins' => array(
					array(
						'table'=>'usuarios_canales',
						'alias'=>'UsuarioCanal',
						'type' =>'LEFT',
						'conditions' => array(
							'UsuarioCanal.usuario_id = Usuario.id'
						)
					)
				),
				'conditions'=>array(
					'Usuario.id'=> $id,
					'Usuario.tipo_rif'=> $tipo_rif,
					'Usuario.rif'=> $rif,
				)
			)
		);	
		
		if($result){
			$result = array_merge($result['Usuario'],$result[0]);
			$usuario['Usuario'] = desencriptarCampos($result, array('nombre','celular','nroCedulaRif', 'nomUsuario', 'emailPri', 'cargo'));
			$usuario['Usuario']['codigoCelular'] = substr($usuario['Usuario']['celular'], 0, 4);
			$usuario['Usuario']['nroCelular'] = substr($usuario['Usuario']['celular'], 4);
		}
		
		return $usuario;
	}
	
	
	/**
	 * Elimina un usuario de la empresa
	 * 
	 * @param int $id
	 * @param string $tipo_rif
	 * @param string $rif
	 * @return array
	 */
	public function eliminaUsuario($id, $tipo_rif, $rif){
		
		$actualizaUsuario = '';
		
		if( $actualizaUsuario = $this->UsuarioCanal->updateAll(
			array(
				'estatus_acceso' => "'I1'"
			),
			array(
				'usuario_id' => $id,
				'canal_id' => Configure::read('canal.ib')
			)
		)){
			$this->Usuario->updateAll(
				array(
					'fecha_hora_modificacion'=>"'".date('Y-m-d H:i:s')."'"
				),
				array(
					'Usuario.id' => $id,
					'Usuario.tipo_rif'=> $tipo_rif,
					'Usuario.rif'=> $rif,
				)
			);
		}
				
		return $actualizaUsuario;
	}
	
	
	/**
	 * Cambia el estatus de acceso un usuario de la empresa
	 * 
	 * @param int $id
	 * @param string $estatus
	 * @param string $tipo_rif
	 * @param string $rif
	 * @return array
	 */
	public function cambioEstatusUsuario($id, $estatus, $tipo_rif, $rif){
		
		$actualizaUsuario = '';

		if($actualizaUsuario = $this->UsuarioCanal->updateAll(
			array(
				'estatus_acceso' => "'".$estatus."'"
			),
			array(
				'usuario_id' => $id,
				'canal_id' => Configure::read('canal.ib')
			)
		)){
			$this->Usuario->updateAll(
				array(
					'fecha_hora_modificacion'=>"'".date('Y-m-d H:i:s')."'"
				),
				array(
					'Usuario.id' => $id,
					'Usuario.tipo_rif'=> $tipo_rif,
					'Usuario.rif'=> $rif,
				)
			);
		}	
		return $actualizaUsuario;
	}
}
?>