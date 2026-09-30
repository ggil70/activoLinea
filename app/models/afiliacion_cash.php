<?php
/**
 * Modelo para el manejo de los afiliados para usuarios juridicos.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los afiliados para los usuarios juridicos.
 * 
 */
class AfiliacionCash extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'afiliaciones_juridicos';
	
	
	/**
	 * Método para la validación de la longitud de la cédula.
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
		
		if(
			$this->data['AfiliacionCash']['tipo']=='n' &&
			strtoupper($this->data['AfiliacionCash']['tipo_cedula'])=='V' &&
			strlen($this->data['AfiliacionCash']['cedula'])>8
		){
			// En el caso de afiliaciones de nómina, no permitir rif personal
			return false;
		} else {
			return $this->checkMaxCI($this->data['AfiliacionCash']['tipo_cedula'], $value);
		}
	}
	
	
	/**
	 * Método para la validación del RIF.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function validaRif($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		App::import('Component', 'Activo');

		$activo = new ActivoComponent();
		
		return	$activo->validarRIF(
					$this->data[$this->name]['tipo_cedula'],
					$value,
                                        'ac'
				);
	}
	
	
	
	/**
	 * Método para la validación del nombre cuando la afiliación es de nómina
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function nombreNomina($check){
		if($this->data['AfiliacionCash']['tipo']=='n' || $this->data['AfiliacionCash']['tipo']=='m' ){
                    return $this->alfabetico($check);
		} else {
			return true;
		}
	}
	
	/**
	 * Método para la validación del nombre cuando la afiliación es de proveedores
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function nombreProveedor($check){
		if($this->data['AfiliacionCash']['tipo']=='p' || $this->data['AfiliacionCash']['tipo']=='x' ){
			return $this->alfanumerico($check);
		} else {
			return true;
		}
	}
	
	/**
	 * Método para validar si el numero de cuenta se repite.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function validarUnicidadCuenta($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		// Para pago de proveedores de banco activo
		// Se pueden repetir los nro de cuentra entre distintos afiliados
		// La validación nroCuenta2 rebota la cuenta si el beneficiario no es tituloar o cotitular
	
		if(
			$this->data[$this->name]['tipo']=='p' || $this->data[$this->name]['tipo']=='x'  &&
			substr($value,0,4)=='0171'
		){
			return true;
		} else {
			// En cualquier otro caso no debe dejar repetir la cuenta
			
			$condiciones = array(
				'tipo_rif'=>$this->data['AfiliacionCash']['tipo_rif'],
				'rif'=>encriptar($this->data['AfiliacionCash']['rif']),
				'numero_cuenta'=>encriptar($value),
				'tipo'=>$this->data['AfiliacionCash']['tipo']
			);
			
			if(isset($this->data['AfiliacionCash']['id']) && !empty($this->data['AfiliacionCash']['id'])){
				$condiciones["id <>"] = $this->data['AfiliacionCash']['id'];
			}
			
			return 0 == $this->find(
				'count',
				array(
					'conditions'=>$condiciones
				)
			);
		}
	}
	
	/**
	 * Verifica si la cuenta de un beneficiario ya existe.
	 * 
	 * @param array $check
	 * @return boolean
	 */
	function validarUnicidadBeneficiario($check){
		$condiciones = array(
			'tipo_rif'=>$this->data['AfiliacionCash']['tipo_rif'],
			'rif'=>encriptar($this->data['AfiliacionCash']['rif']),
			'tipo_cedula'=>$this->data['AfiliacionCash']['tipo_cedula'],
			'cedula'=>encriptar($this->data['AfiliacionCash']['cedula']),
			'tipo'=>$this->data['AfiliacionCash']['tipo']
		);
			
		if(isset($this->data['AfiliacionCash']['id']) && !empty($this->data['AfiliacionCash']['id'])){
			$condiciones["id <>"] = $this->data['AfiliacionCash']['id'];
		}

		return 0 == $this->find(
			'count',
			array(
				'conditions'=>$condiciones
			)
		);
	}
	
	/**
	 * Verifica el tipo de identificacion de un beneficiario
	 * 
	 * @param array $check
	 * @return boolean
	 */
	function validarTipoIdentificacion($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
            if(isset($this->data[$this->name]['tipo'])){
                switch($this->data[$this->name]['tipo']){
		case 'n':
                    return in_array($value, array('V','E','P'));
		case 'p':
                    return in_array($value, array('V','E','J','P','G'));  
 		case 'm':
                    return in_array($value, array('V','E','P'));
		case 'x':
                    return in_array($value, array('V','E','J','P','G'));    
		}
		} else {
			return in_array($value, array('V','E','J','P','G'));
		}
	}
	
	/**
	 * Valida la referencia que no este duplicada
	 * 
	 * @param string $check	 
	 * @return array
	 */
	function validaNumReferencia($check){
		
            $this->TransaccionBeneficiario = ClassRegistry::init('TransaccionBeneficiario');
            
            if(is_array($check)){
                    $value = array_values($check);
                    $value = $value[0];
            } else {
                    $value = $check;
            }
                
            $result = $this->TransaccionBeneficiario->find(
                'first',
                array(
                        'joins' => array(
                            array(
                                'table' => 'transacciones_pendientes',
                                'alias' => 'TP',
                                'type' => 'INNER',
                                'conditions' => array(
                                    'TP.id = TransaccionBeneficiario.transaccion_id'
                                )
                            )
                        ),
                        'fields'=>array('referencia'),
                        'conditions'=>array('TransaccionBeneficiario.tipo_rif'=>$this->data[$this->name]['tipo_rif'], 'TransaccionBeneficiario.rif'=>encriptar($this->data[$this->name]['rif']), 'TransaccionBeneficiario.referencia'=>encriptar($value), 'TP.fecha_hora_valor'=> date('Y-m-d').' 00:00:00.000')
                )
            );
            
            if($result){
                return false;
            }

            return true;
	}
        
	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'numero_cuenta' => array(
			'nroCuenta'=> array(
				'rule'=>'nroCuenta2'
			),
			'numero'=> array(
				'rule'=>'numeric'
			),		
			'requerido'=> array(
				'rule'=>'notEmpty'
			),
			'unicidad'=>array(
				'rule'=>'validarUnicidadCuenta'
			)
		),		
		'nombre' => array(
			'alfabetico'=>array(
				'rule'=>'nombreNomina'
			),		
			'alfanumerico'=>array(
				'rule'=>'nombreProveedor'
			),		
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'tipo_cedula' => array(
			'valoresPermitidos'=>array(
				'rule'=>'validarTipoIdentificacion'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'cedula' => array(
			'longitud'=>array(
				'rule'=>'validaLongitud'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'rif'=>array(
				'rule'=>'validaRif'
			),
			'unicidad'=>array(
				'rule'=>'validarUnicidadBeneficiario'
			)
		),
		
		'email' => array(
			'correo'=>array(
				'rule'=>'email'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
                         'longitud'=>array(
                            'rule'=>array('maxLength','50'),
                            'message' => 'El Email debe tener 50 caracteres como maximo'
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
		'accion' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('A','D'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'monto' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
		),
		'concepto' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',30)
			)
		),
		'referencia' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			),
			'numero'=> array(
				'rule'=>'numeric'
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',9)
			),
			'unicidad'=>array(
				'rule'=>'validaNumReferencia'
			)
		),
		'archivo' => array(
			'extension'=>array(
				'rule' => array( 'extension', array('txt') ),
				'required'=>true
			),
                         'upload-file' => array(
                                'rule' => array('uploadFile'),
                                'message' => 'Estimado Cliente, el nombre del archivo no debe contener caracteres especiales, acentos ni espacios en blanco'
                        )
		),
		
	);
	

	/**
	 * Lista los afiliados de la empresa segun el tipo indicado.
	 * 
	 * @param array $usuario
	 * @param string $tipo
	 * @return array
	 */
	function listar($usuario, $tipo){
		
		$res = $this->find(
			'all',
			array(
				'conditions'=>array(
					'tipo'=>$tipo,
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif']
				)
			)
		);
		
		return $res;
	}
        
     /**
	 * Lista los afiliados moneda extranjera de la empresa segun el tipo indicado.
	 * 
	 * @param array $usuario
	 * @param string $tipo
	 * @return array
	 */
	function listar_me($usuario, $tipo){
		
            $res = $this->find(
                'all',
                   array(
                    'conditions'=>array(
                        'tipo'=>$tipo,
                       	'tipo_rif'=>$usuario['tipo_rif'],
                   		'rif'=>$usuario['rif']
           		))
			);
		
            return $res;
	}
    } 
 ?>