<?php
/**
 * Modelo para el formulario de recarga de servicios
 * 
 */


/**
 * Validaciones para el formulario de recarga de servicios
 */
class RecargaServicios extends AppModel {
	
	/**
	 * Tabla de la base de datos a utilizar
	 * 
	 * @var string
	 */
	var $useTable = false;
	
	/**
	 * Para la validación del límite total para la recarga
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function limiteTotal($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && $this->data['Pagostdc']['limite_total']!=""){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['Pagostdc']['limite_total']);		

		   return $monto <= $monto_maximo;
		}
		return true;
	} 
	
	/**
	 * Validación para el monto de la recarga minima
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function recargaMinima($check){
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && !empty($this->data['RecargaServicios']['monto'])){
		   $monto = floatval(formatea_monto_toWs($value));
		   $tipoOperacion = isset($this->data['RecargaServicios']['tipoOperacion']) ? trim($this->data['RecargaServicios']['tipoOperacion']) : '';
		   if($tipoOperacion == 'sd'){
                       $monto_minimo = _p('recarga_minima_digitel');
                   }elseif($tipoOperacion == 'st'){
                       $monto_minimo = _p('recarga_minima_movistar');
                   }elseif($tipoOperacion == 'si'){
                       $monto_minimo = _p('recarga_minima_inter_pre');
                   }elseif($tipoOperacion == 'sm' || $tipoOperacion == 'sc' || $tipoOperacion == 'scn'){
                       $monto_minimo = _p('recarga_minima_movilnet');
                   }else{
                       $monto_minimo = _p('recarga_minima');
                   }		

		   $monto_minimo = floatval(formatea_monto_toWs($monto_minimo));
		   return $monto >= $monto_minimo;
		}
		return true;

	}
        
        /**
	 * Validación para el monto de la recarga minima
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function recargaMaxima($check){
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && !empty($this->data['RecargaServicios']['monto'])){
		   $monto = floatval(formatea_monto_toWs($value));
		   $tipoOperacion = isset($this->data['RecargaServicios']['tipoOperacion']) ? trim($this->data['RecargaServicios']['tipoOperacion']) : '';
		   if($tipoOperacion == 'sd'){
                       $monto_maximo = _p('recarga_maxima_digitel');
                   }elseif($tipoOperacion == 'st'){
                       $monto_maximo = _p('recarga_maxima_movistar');
                   }elseif($tipoOperacion == 'si'){
                       $monto_maximo = _p('recarga_maxima_inter_pre');
                   }elseif($tipoOperacion == 'sm' || $tipoOperacion == 'sc' || $tipoOperacion == 'scn'){
                       $monto_maximo = _p('recarga_maxima_movilnet');
                   }else{
                       $monto_maximo = _p('recarga_maxima');
                   }		

		   $monto_maximo = floatval(formatea_monto_toWs($monto_maximo));
		   return $monto <= $monto_maximo;
		}
		return true;

	}
	
	/**
	 * Validación de la deuda total de la recarga
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

		if($value!="" && isset($this->data['RecargaServicios']['deuda_total']) && $this->data['RecargaServicios']['deuda_total'] !=""){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['RecargaServicios']['deuda_total']);		

		   return $monto <= $monto_maximo;
		}
		return true;
	} 
	
	
	/**
	 * Verifica si es un día feriado
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function validarServicio($check){
	
		App::import('Component', 'Session');

		$session = new SessionComponent();
		
		$usuario = $session->read('usuarios.datos');
	
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}       
	
		// verifica que sea un dia feriado
		$this->Afiliacion = ClassRegistry::init('Afiliacion');
		if($this->Afiliacion->find( 'count', array('conditions'=>(array('id'=>$value,'usuario_id'=>$usuario['id']))) )) return true;
		
		return false;
		
	}
	
	/**
	 * Verificación para el monto límite del pago
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

		if($value!="" && $this->data['RecargaServicios']['monto_limite']!=""){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['RecargaServicios']['monto_limite']);		

		   return $monto <= $monto_maximo;
		}
		return true;

	}  
	
	
	/**
	 * Verificación del monto maximo de la recarga
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function montoMaximo($check){
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && !empty($this->data['RecargaServicios']['monto_maximo'])){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['RecargaServicios']['monto_maximo']);		

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
		'cuentaDebitar' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'tipoOperacion' => array(		
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'tipo_pago' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('o','t','a','v'))
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
			'montoMaximo'=>array(
				'rule'=>'montoMaximo'
			),
			'monto_limite'=>array(
				'rule'=>'montoLimite'
			),
			'recargaMinima'=>array(
				'rule'=>'recargaMinima'
			),
                        'recargaMaxima'=>array(
				'rule'=>'recargaMaxima'
			)                  
		),
		'numservicio' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'servicio'=>array(
				'rule'=>'validarServicio'
			),
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
		'tipoDestinatario' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('a','n'))
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
			),
                        'recargaMinima'=>array(
				'rule'=>'recargaMinima'
			),
                        'recargaMaxima'=>array(
				'rule'=>'recargaMaxima'
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
		)
	);
	
}

?>