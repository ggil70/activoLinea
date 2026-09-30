<?php
/**
 * Modelo para el manejo de los afiliados al directorio pago activo.
 * 
 * @author EMSYS
 */


/**
 * Manejo de los afiliados en el directorio pago activo.
 * 
 */
class Directorio extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = false;
			
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
		return $this->checkMaxCI($this->data['Directorio']['tipoCedulaRif'], $value);
	}						
		
	/**
	 * Método para la validación del límite en el monto para una afiliacion.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function montoLimite($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && $this->data['Directorio']['monto_limite']!=""){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['Directorio']['monto_limite']);		

		   return $monto <= $monto_maximo;
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
		'id' => array(
			'numero'=> array(
				'rule'=>'numeric'
			),		
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'accion' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('crear','editar','eliminar'))
			),
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
		
                'tipoCliente' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('0','1'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
            
		'tipoCedulaRif' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('V','E','J','P','G'))
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
		
		'alias' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			)
		),
		
		'tipo_afiliacion' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'tipo_operacion' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
                
                'tipo_servicio' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
            
                'monto_maximo' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
		)				
	);			               
	    
}
?>