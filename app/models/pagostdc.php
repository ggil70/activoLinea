<?php
/**
 * Modelo para el manejo de los pagos de tarjetas de crédito.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los pagos de tarjetas de crédito.
 * 
 */
class Pagostdc extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = false;
	
	
	/**
	 * Método para validar la longitud de la cédula del beneficiario.
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
		return $this->checkMaxCI($this->data['Pagostdc']['tipoCedulaRif'], $value);
	}
	
	
	/**
	 * Método para verificar el monto para el pago de tarjetas de crédito bancoactivo
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function deudatotal($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && $this->data['Pagostdc']['tipo_pago_t'] != ""){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['Pagostdc']['tipo_pago_t']);		

		   return $monto <= $monto_maximo;
		}
		
		return true;
	}
	
        /**
	 * Método para validar que la identificacion del cliente y destino sean distintas.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function comparaIdentificacion(){			
		
            return $this->checkDestinatario($this->data['Pagostdc']['tipoCedulaRif'], $this->data['Pagostdc']['nroCedulaRif']);
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
		'numeroTDC' => array(
			'longitud' => array(
				'rule'=>'/^\d{14,16}$/i'
			),
			'numeric'=> array(
				'rule'=>'numeric'
			),		
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'tipo_pago' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('t','c','m','o'))
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
			'montoLimiteDiario'=>array(
				'rule'=>'montoLimite'
			),
			'montoMaximo'=>array(
				'rule'=>'montoMaximo'
			),
			'montoLimiteUsuario'=>array(
				'rule'=>'montoLimiteUsuario'
			),
                        'limiteAltoValor'=>array(
				'rule'=>'limiteAltoValor'
			)
		),
		'otro_monto' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'requerido'=>array(
				'rule'=>'otro_monto_requerido'
			),
			'deudatotal'=>array(
				'rule'=>'deudatotal'
			),
			'montoLimiteUsuario'=>array(
				'rule'=>'montoLimiteUsuario'
			),
		),
		'tipoDestinatario' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('a','n'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'banco' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'numeroBanco' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'nombreBeneficiario' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			),		
			'requerido'=>array(
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
				'rule'=>'notEmpty',
			),
                        'destinatario'=>array(
				'rule'=>'comparaIdentificacion'
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
		'monto_maximo' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'montoMaximoLimite'=>array(
				'rule'=>'limiteTotal'
			)
		),
		'descripcion' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'alias' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			)
		),
		'afiliar' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('s','n'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		)
	);
	
	
	/**
	 * Método para validar el monto en el pago de tarjetas de crédito bancoactivo.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function otro_monto_requerido($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if($this->data['Pagostdc']['tipo_pago']=='o'){
			return !empty($value);
		} else {
			return true;
		}
	}
}

?>