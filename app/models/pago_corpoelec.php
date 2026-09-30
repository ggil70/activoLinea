<?php
/**
 * Modelo para el manejo de Pago Activo.
 * 
 * @author Emsys
 */


/**
 * Manejo de Pago Activo.
 * 
 */
class PagoCorpoelec extends AppModel {
	
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'transacciones';
	
	
	/**
	 * Método para validar la longitud del número de documento de identificación.
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
		
		return $this->checkMaxCI($this->data['PagoActivo']['tipoCedulaRif'], $value);
	}
	
        /**
	 * Método para validar la longitud del número de documento de identificación.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function validaDestinatario($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		return $this->checkMaxCI($this->data['PagoActivo']['tipoCedulaRif'], $value);
	}
	
	/**
	 * Método para validar que las cuentas de origen y destino sean distintas.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function comparaCuentas($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}	
		
		return !( strcmp($value,$this->data['PagoActivo']['cuentaDebitar']) == 0 );
	}	        
	
	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'cuentaDebitar' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'tipoDestinatario' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('a','n'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'bancoReceptor' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'tipoCedulaRif' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('V','E','P','J','G'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'nroCedulaRif' => array(
			'longitud'=>array(
				'rule'=>'validaLongitud'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'beneficiario' => array(
			'numeric'=>array(
				'rule'=>'numeric'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'emailBeneficiario' => array(
			'correo'=>array(
				'rule'=>'email'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
                         'longitud'=>array(
                            'rule'=>array('maxLength','50'),
                            'message' => 'El Email del beneficiario debe tener 50 caracteres como maximo'
                        )
		),
		'monto' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'montoLimiteDiario'=>array(
				'rule'=>'montoLimite'
			),
			'montoLimiteUsuario'=>array(
				'rule'=>'montoLimiteUsuario'
			),
			'montoMaximo'=>array(
				'rule'=>'montoMaximo'
			)
		),
		'descripcion' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		)
	);
}

?>