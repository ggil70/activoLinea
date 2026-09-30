<?php
/**
 * Modelo para el manejo de la información del usuario.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de la información del usuario.
 * 
 */
class Usuario extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'usuarios';
	
	/**
	 * Montos límites del usuario.
	 * 
	 * @var array
	 */
	var $montos_limites = array();

	
	/**
	 * Estatus registro.
	 * 
	 * @var array 
	 */
	var $estatusRegistro = array('DD'=>'Suspendido', 'ST'=>'Suspendido Temporal', 'B'=>'Bloqueado','I1'=>'Inactivo','A'=>'Activo');
	
	
	/**
	 * Obtiene los límites máximos del usuario.
	 * 
	 * @return array
	 */
	function obtenerLimitesMaximos(){
		App::import('Component', 'Session');
		$session = new SessionComponent();
		$sufijo = $session->check('login.juridico') ? '_J' : '';
		
		$limites_maximos['max_it_trans'] = _p('limite_internas_terceros'.$sufijo);
		
		if(!empty($limites_maximos['max_it_trans'])){
			$limites_maximos['max_it_trans']= formatea_monto_fromWs($limites_maximos['max_it_trans']);
		}
		
		$limites_maximos['max_e_trans']	= _p('limite_externas'.$sufijo);
		
		if(!empty($limites_maximos['max_e_trans'])){
			$limites_maximos['max_e_trans']	= formatea_monto_fromWs($limites_maximos['max_e_trans']);
		}
		                                                                               
                $limites_maximos['max_it_n'] = _p('limite_internas_terceros_nomina');
		
		if(!empty($limites_maximos['max_it_n'])){
			$limites_maximos['max_it_n'] = formatea_monto_fromWs($limites_maximos['max_it_n']);
		}
		
		$limites_maximos['max_e_n'] = _p('limite_externas_nomina');
		
		if(!empty($limites_maximos['max_e_n'])){
			$limites_maximos['max_e_n'] = formatea_monto_fromWs($limites_maximos['max_e_n']);
		}
                                
                $limites_maximos['max_it_p'] = _p('limite_internas_terceros_proveedores');
		
		if(!empty($limites_maximos['max_it_p'])){
			$limites_maximos['max_it_p']= formatea_monto_fromWs($limites_maximos['max_it_p']);
		}
		
		$limites_maximos['max_e_p'] = _p('limite_externas_proveedores');
		
		if(!empty($limites_maximos['max_e_p'])){
			$limites_maximos['max_e_p'] = formatea_monto_fromWs($limites_maximos['max_e_p']);
		}
                
		return $limites_maximos;
	}
		
	
	/**
	 * Método para comparar el valor ingresado en la casilla de aceptacion de contrato
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function compara($check) {
	
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if( strcmp(strtoupper($value), 'ACEPTO') == 0){
			return true;
		}
		else{
			return false;
		}
	}
	
	
	/**
	 * Método para verificar las clave nueva y confirmada.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function confirmacionIgualQue($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		return $value == $this->data['Usuario']['clave'];
	}	
	
	
	/**
	 * Método que verifica el ID de la imagen seleccionada en la confirmación.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function idIgualQue($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		return $value == $this->data['Usuario']['idImagen'];
	}
	
	
	/**
	 * Método que verifica que no este vacía la pregunta que indica el usuario.
	 * 
	 * @param array|string $check
	 * @param string $preg
	 * @return boolean
	 */
	function validar_otra_pregunta($check,$preg){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if($this->data['Usuario'][$preg]=='otra'){
			if(empty($value)){
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
	
	
	/**
	 * Método par validar que las respuestas no se repitan.
	 * 
	 * @param array|string $check
	 * @param array $otros
	 * @return boolean
	 */
	function noigual($check, $otros){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)){
			return true;
		}if($value=='otra'){
			return true;
		} else {
			foreach($otros as $o){
				if($this->data['Usuario'][$o] == $value){
					return false;
				}
			}
			
			return true;
		}
	}
	
	
	/**
	 * Método para validar que el monto indicado por el usuario no sea superior
	 * al monto límite.
	 * 
	 * @param array|string $check
	 * @param string $key
	 * @return boolean
	 */
	function monto_limite($check, $key){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)) return true;
		
		if(
			isset($this->montos_limites[$key]) &&
			!empty($this->montos_limites[$key])
		){
			if(!preg_match('/[0-9,\.]/', $this->montos_limites[$key])){
				// Sin límites
				return true;
			} else {
				$monto = formatea_monto_toWs($value);
				return $monto <= formatea_monto_toWs($this->montos_limites[$key]);
			}
		}
		
		return true;
	}
	
	
	/**
	 * Método para validar la logintud del número de documento de identidad.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function validaLongitud($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		return $this->checkMaxCI($this->data['Usuario']['tipoCedulaRif'], $value);
	}
	
	
	/**
	 * Método para validar la longitud del RIF.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function validaLongitudRif($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		return $this->checkMaxCI($this->data['Usuario']['tipoRif'], $value);
	}
	
	
	/**
	 * Método para validar que no se encuentren vacío el sello creado por el usuario.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function notEmptySellos($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		return (trim($value)!='' || trim($this->data['Usuario']['sello2'])!='' || trim($this->data['Usuario']['sello3'])!='');
	}
	
	
	/**
	 * Método para validar el formato del login del usuario.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function nombre_usuario($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)) return true;
		
		$repeticiones = '/^(.)\1+/';
		$permitidos = '/^[a-zA-Z][a-zA-Z0-9]*$/';
		$primerCaracter = '/^[a-zA-Z]/';
		$letras = '/[a-zA-Z]+/';
		$numeros = '/[0-9]+/';

		$resultado = !$this->hasSequence($value);
		$resultado &= !preg_match($repeticiones, $value);
		$resultado &= preg_match($permitidos, $value);
		$resultado &= preg_match($primerCaracter, $value);
		$resultado &= preg_match($letras, $value);
		$resultado &= preg_match($numeros, $value);

		return $resultado;
	}
	
	
	/**
	 * Método para validar las restricciones en el login del usuario.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function nombre_usuario2($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)) return true;
		
		$keywords = array();
		
		if(
			isset($this->data['Usuario']['nroRif']) &&
			isset($this->data['Usuario']['tipoRif'])		
		){
			$tipoId = $this->data['Usuario']['tipoRif'];
			$nroId = $this->data['Usuario']['nroRif'];
			
			if(isset($this->data['Usuario']['nroCedulaRif'])) $keywords[]= $this->data['Usuario']['nroCedulaRif'];
			
		} else if(
			isset($this->data['Usuario']['nroCedulaRif']) &&
			isset($this->data['Usuario']['tipoCedulaRif'])
		){
			$tipoId = $this->data['Usuario']['tipoCedulaRif'];
			$nroId = $this->data['Usuario']['nroCedulaRif'];
		}
		
		if(
			isset($tipoId) &&
			isset($nroId)
		){
			App::import('Component', 'Activo');
			
			$activo = new ActivoComponent();
			
			$datos_basicos = $activo->invoca(
				'NIBPF3010',
				array(
					'req'=>array(
						'NACIONALIDAD'=>$tipoId,
						'NROIDENTIFICACION'=>str_pad($nroId,12,'0',STR_PAD_LEFT),
						'NROPARAM'=>'1'
					)
				)
			);
			
			if(isset($datos_basicos['NIBPF3010Return']['NIBPF3010'])){
				
				$keywords[]= $nroId;
				
				if(strlen($datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD'])>=6){
					$keywords []= substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD'],0,6);
					$keywords []= substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD'],-6);
				}
				
				$keywords = array_map('trim',array_map('strtolower',$keywords));
				
				$x = strtolower($value);
				
				foreach($keywords as $k){
					if(
						!empty($k) &&
						false !== strpos($x,$k)
					){
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	
	/**
	 * Método para validar que no se repita el login del usuario.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function login_unico($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)) return true;
		
		if(!empty($this->data['Usuario']['tipoRif'])){
			$condiciones = array(
				'Usuario.tipo_rif'=>$this->data['Usuario']['tipoRif'],
				'Usuario.rif'=>encriptar($this->data['Usuario']['nroRif']),
				'Usuario.login'=>encriptar(strtolower($value))
			);
		} else {
			$condiciones = array(
				'Usuario.login'=>encriptar(strtolower($value)),
				'Usuario.tipo_rif is null'
			);
		}
	
		$res = $this->find(
			'count',
			array(
				'conditions'=>$condiciones
			)
		);
		
		return $res == 0;
	}
	
	/**
	 * Método para verificar si el usuario ya acepto el nuevo contrato
	 * @param int $idUsuario
	 * @return boolean
	 */
	function validar_nuevo_contrato($idUsuario){
		
            $res = $this->find(
                    'first',
                    array(
                            'fields'=>array('nvo_contrato'),
                            'conditions'=>array('Usuario.id'=>$idUsuario),
                    )
            );
            
            return !$res['Usuario']['nvo_contrato'];
	}

	/**
	 * Método para actualizar cuando el usuario acepta el nuevo contrato
	 * @param int $idUsuario
	 * @return boolean
	 */
	function actualizar_nuevo_contrato($idUsuario){
		
            // actualiza el registro
            $res = $this->save(
                array(
                    'Usuario' => array(
                                'nvo_contrato' => 1,
                                'id' => $idUsuario
                            )
                    ), false
            );

            return $res;
         }
        
	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'login' => array(
			'minLength'=>array(
				'rule'=>array('minLength',1)
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',12),
			),
			'usuario'=>array(
				'rule'=>'usuario_legacy'
			),	
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'contrasena' => array(
			'minLength'=>array(
				'rule'=>array('minLength',8),
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',12),
			),
			'contrasena'=>array(
				'rule'=>'contrasena',
			),	
			'requerido'=>array(
				'rule'=>'notEmpty',
			)
		),
		'contrasena2' => array('rule'=>'contrasena2'),
		
		'decision' => array(
			'decision' => array(
				'rule'=>'compara',

			),
			'requerido'=> array(
				'rule'=>'notEmpty',

			)
		),
		'contrasenaL' => array(
			'minLength'=>array(
				'rule'=>array('minLength',1),
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',12),
			),
			'contrasena'=>array(
				'rule'=>'contrasena',
			),	
			'requerido'=>array(
				'rule'=>'notEmpty',
			)
		),
		
		'tipoRif' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('J','G','V','E')),

			),
			'requerido'=>array(
				'rule'=>'notEmpty',

			)
		),
		
		'nroRif' => array(
			'longitud'=>array(
				'rule'=>'validaLongitudRif'
			),
			'requerido'=>array(
				'rule'=>'notEmpty',
			)
		),
		
		'tipoCedulaRif' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('V','E','P')),

			),
			'requerido'=>array(
				'rule'=>'notEmpty',

			)
		),
		
		'nroCedulaRif' => array(
			'longitud'=>array(
				'rule'=>'validaLongitud'
			),
			'requerido'=>array(
				'rule'=>'notEmpty',

			)
		),
		
		'nroTarjeta' => array(
			'nroTarjeta'=> array(
				'rule'=>'/^\d{6}$/i',
			),
			'requerido'=> array(
				'rule'=>'notEmpty',
			),
		),
		
		'valor' => array(
			'valor'=> array(
				'rule'=>'numeric',

			),
			'requerido'=> array(
				'rule'=>'notEmpty',

			),
		),
		
		'opcion' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('s','n')),

			),
			'requerido'=>array(
				'rule'=>'notEmpty',

			)
		),
		
		'nomUsuario' => array(
			'minLength'=>array(
				'rule'=>array('minLength',8)
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',12),
			),	
			'login_unico'=>array(
				'rule'=>'login_unico'
			),
			'nombre_usuario2'=>array(
				'rule'=>'nombre_usuario2'
			),
			'nombre_usuario'=>array(
				'rule'=>'nombre_usuario'
			),		
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'emailPri' => array(
			'correo'=>array(
				'rule'=>'email',
			),
			'requerido'=>array(
				'rule'=>'notEmpty',
			),
                        'longitud'=>array(
                            'rule'=>array('maxLength','50'),
                            'message' => 'El Email Principal debe tener máximo 50'
                        )                     
                    
		),
		
		'emailSec' => array(
			'correo'=>array(
				'rule'=>'email',
				'allowEmpty'=>true

			),
                      'longitud'=>array(
                            'rule'=>array('maxLength','50'),
                            'message' => 'El Email Secundario debe tener máximo 50'
                        )
		),
		
		'nroCelular' => array(
			'valor'=> array(
				'rule'=>'celular',

			),
			'requerido'=> array(
				'rule'=>'notEmpty',

			),
		),
		
		'codigoCelular' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('0414','0424','0416','0426','0412','0422')),

			),
			'requerido'=>array(
				'rule'=>'notEmpty',

			)
		),
		
		'clave' => array(
			'minLength'=>array(
				'rule'=>array('minLength',8)
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',12)
			),
			'contrasena'=>array(
				'rule'=>'contrasena'
			),		
			'contrasena2'=>array(
				'rule'=>'contrasena2',
			),	
			'requerido'=>array(
				'rule'=>'notEmpty'
			)		
		),
		
		'clave_confirmacion' => array(
			'confirmacionIgualQue'=>array(
				'rule'=>'confirmacionIgualQue'

			),
			'requerido'=>array(
				'rule'=>'notEmpty'

			)		
		),
		
		'tiempoCaduca'=> array(
			'requerido'=>array(
				'rule'=>'notEmpty'

			),
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('30','60','90','180'))
			),		
		),
		
		'idImagen'=> array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			)		
		),
		
		'idConfirm' => array(
			'idIgualQue'=>array(
				'rule'=>'idIgualQue'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)			
		),
		
		'sello1'=> array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			),		
			'requerido'=>array(
				'rule'=>'notEmptySellos'
			)
		),
		
		'sello2'=> array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			)
		),
		
		'sello3'=> array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			)
		),
		
		'preg1'=> array(
			'requerido'=>array(
				'rule'=>'notEmpty',
			)
		),
		'preg2'=> array(
			'noigual'=>array(
				'rule'=>array('noigual',array('preg1'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty',

			)
		),
		'preg3'=> array(
			'noigual'=>array(
				'rule'=>array('noigual',array('preg1','preg2'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty',

			)
		),
		
		'pregUser1'=> array(
			'minLength'=>array(
				'rule'=>array('minLength',5),
				'allowEmpty'=>true
				
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',30),
				'allowEmpty'=>true
			),
			'validar_otra_pregunta'=>array(
				'rule'=>array('validar_otra_pregunta','preg1')
			)
		),
		'pregUser2'=> array(
			'minLength'=>array(
				'rule'=>array('minLength',5),
				'allowEmpty'=>true
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',30),
				'allowEmpty'=>true
			),
			'validar_otra_pregunta'=>array(
				'rule'=>array('validar_otra_pregunta','preg2')
			),
			'noigual'=>array(
				'rule'=>array('noigual',array('pregUser1'))
			)
		),
		'pregUser3'=> array(
			'minLength'=>array(
				'rule'=>array('minLength',5),
				'allowEmpty'=>true
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',30),
				'allowEmpty'=>true
			),
			'validar_otra_pregunta'=>array(
				'rule'=>array('validar_otra_pregunta','preg3')
			),
			'noigual'=>array(
				'rule'=>array('noigual',array('pregUser1','pregUser2'))
			)
		),
		
		'resp1'=> array(
			'maxLength'=>array(
				'rule'=>array('maxLength',30)
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			)
		),
		'resp2'=> array(
			'maxLength'=>array(
				'rule'=>array('maxLength',30)
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'noigual'=>array(
				'rule'=>array('noigual',array('resp1'))
			),
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			)
		),
		'resp3'=> array(
			'maxLength'=>array(
				'rule'=>array('maxLength',30)
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'noigual'=>array(
				'rule'=>array('noigual',array('resp1','resp2'))
			),
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			)
		),		
		'nombre'=> array(
			'maxLength'=>array(
				'rule'=>array('maxLength',50)
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)			
		),
		'cargo'=> array(
			'maxLength'=>array(
				'rule'=>array('maxLength',30)
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)			
		),
		'tiempoCaduca'=> array(
			'maxLength'=>array(
				'rule'=>array('maxLength',8)
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)			
		),
		'perfil'=> array(
			'maxLength'=>array(
				'rule'=>array('maxLength',12)
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)			
		),
		'i_transaccion' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'monto_limite'=>array(
				'rule'=>array('monto_limite','max_i_trans')
			)
		),
		'it_transaccion' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'monto_limite'=>array(
				'rule'=>array('monto_limite','max_it_trans')
			)
		),
		'e_transaccion' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'monto_limite'=>array(
				'rule'=>array('monto_limite','max_e_trans')
			)
		),
		'et_transaccion' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'monto_limite'=>array(
				'rule'=>array('monto_limite','max_et_trans')
			)
		),                
                'it_transaccion_n' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'monto_limite'=>array(
				'rule'=>array('monto_limite','max_it_n')
			)
		),
		'e_transaccion_n' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'monto_limite'=>array(
				'rule'=>array('monto_limite','max_e_n')
			)
		),                        
                'it_transaccion_p' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'monto_limite'=>array(
				'rule'=>array('monto_limite','max_it_p')
			)
		),
		'e_transaccion_p' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'monto_limite'=>array(
				'rule'=>array('monto_limite','max_e_p')
			)
		),                                    
		'co_estado' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('A','P','BC')),
			),
			'requerido'=>array(
				'rule'=>'notEmpty',
			)
		),
	);
        
        /**
	 * Método para actualizar cuando el usuario se afilia o desafilia de movil activo
	 * @param int $idUsuario
         * @param int $afiliado 
	 * @return boolean
	 */
	function afiliacion_movil($idUsuario,$afiliado){
		
            // actualiza el registro
            $res = $this->save(
                array(
                    'Usuario' => array(
                                'afiliacion_movil' => $afiliado,
                                'id' => $idUsuario
                            )
                    ), false
            );

            return $res;
        }
        
}
?>