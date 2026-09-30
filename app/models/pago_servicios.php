<?php
/**
 * Modelo utilizado por el formulario de pago de servicios
 */
 
/**
 * Validaciones para el pago de servicios
 */
class PagoServicios extends AppModel {
	
	/**
	 * @var boolean $useTable Indica a cakephp si este modelo utiliza la base de datos
	 */
	var $useTable = false;			
	
	/**
	 * Validación del monto límite
	 * @param $check El valor del campo a validar
	 * @return boolean Verdadero si el valor es correcto
	 */
	function montoLimite($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && isset($this->data['PagoServicios']['monto_limite']) && $this->data['PagoServicios']['monto_limite'] !=""){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['PagoServicios']['monto_limite']);		

		   return $monto <= $monto_maximo;
		}
		return true;
	} 
	
	/**
	 * Validación del monto según la deuda total 
	 * @param $check El valor del campo a validar
	 * @return boolean Verdadero si el valor es correcto
	 */	
	function deudatotal($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && isset($this->data['PagoServicios']['deuda_total']) && $this->data['PagoServicios']['deuda_total'] !=""){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['PagoServicios']['deuda_total']);		

		   return $monto <= $monto_maximo;
		}
		return true;
	} 
	
	/**
	 * Para validar cuando sea un monto indicado por el usuario
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
		
		if($this->data['PagoServicios']['tipo_pago']=='o'){
			return !empty($value);
		} else {
			return true;
		}
	}
	
	
	/**
	 * Validación el monto indicado
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function monto_requerido($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if($this->data['PagoServicios']['tipo_pago']!='o'){
			return !empty($value);
		} else {
			return true;
		}
	}
	
	
	/**
	 * Validación del limite total para el pago
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

		if($value!="" && $this->data['PagoServicios']['limite_total']!=""){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['PagoServicios']['limite_total']);		

		   return $monto <= $monto_maximo;
		}
		return true;
	} 
	
	
	/**
	 * Para validar si el monto es mayor a cero
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function mayorCero($check){
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           	
		
		$monto = formatea_monto_toWs($value);
		
		return $monto > 0;
	} 
	
	/**
	 * Validación del servicio
	 * @param $check El valor del campo a validar
	 * @return boolean Verdadero si el valor es correcto
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
		if($this->Afiliacion->find( 'count', array('conditions'=>(array('Afiliacion.id'=>$value,'usuario_id'=>$usuario['id']))) )) return true;
		
		return false;
		
	}

	/**
	 * @var array $validate Arreglo de validaciones a aplicar agrupadas por campo
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
			'montoLimiteDiario'=>array(
				'rule'=>'montoLimite'
			),
			'requerido'=>array(
				'rule'=>'monto_requerido'
			),
		),
		'otro_monto' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'deudatotal'=>array(
				'rule'=>'deudaTotal'
			),
			'montoLimiteDiario'=>array(
				'rule'=>'montoLimite'
			),
			'mayorCero'=>array(
				'rule'=>'mayorCero'
			),
			'requerido'=>array(
				'rule'=>'otro_monto_requerido'
			),
		),
		'numservicio' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
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
                            'message' => 'El Email  beneficiario debe tener 50 caracteres como maximo'
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
		)
	);
	
}

?>