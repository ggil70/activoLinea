<?php
/**
 * Clase ayudante para las vistas con métodos utilitarios comunes para todas
 */
 
/**
 * Clase ayudante para las vistas con métodos utilitarios comunes para todas
 */
class ActivoHelper extends AppHelper {
	/**
	 * @var array $helpers Arreglo con listado de ayudantes que utiliza este ayudante
	 */
	var $helpers = array('Session','Html');
	
	/**
	 * Método utilizado para enmascarar un número de cuenta o tarjeta de crédito
	 * @param string $nro Número de cuenta o tarjeta
	 * @return string Número de cuenta enmascarado
	 */
	function enmascara($nro){
		return substr($nro,0,4).'******'.substr($nro,strlen($nro)-4,4);
	}	
	
	/**
	 * Método que indica si la tarjeta de coordenadas ya fue validada
	 * @return boolean Verdadero si la tarjeta de coordenadas ya fue
	 * autenticada exitosamente en la sesión
	 */ 
	function tdc_validada(){
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');
		
		if(
			$validacion_tdc == null ||
			!isset($validacion_tdc['validado']) ||
			!$validacion_tdc['validado']
		){
			return false;
		} else {
			return true;
		}
	}
        
        /**
	 * Método que indica si el codigo otp ya fue validado
	 * @return boolean Verdadero si el otp ya fue
	 * validado exitosamente en la sesión
	 */ 
	function otp_validado(){
		$validacion_otp = $this->Session->read('seguridad.validar_otp');
		
		if(
			$validacion_otp == null ||
			!isset($validacion_otp['validado']) ||
			!$validacion_otp['validado']
		){
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Obtiene el tipo de tarjeta dado su número
	 * @param string $nro_tarjeta Número de tarjeta de crédito
	 * @return string El nombre del tipo de tarjeta de crédito
	 */
	function getTipoTarjeta($nro_tarjeta) {
		$tipo = '';
		
		switch (substr($nro_tarjeta, 0, 1)){
			case 4:
				$tipo = 'Visa';
				break;
			
			case 5:
				$tipo = 'Master';
				break;
		}
		
		return $tipo;
	}
	
	/**
	 * Genera html de los banners
	 * @param array $banners Arreglo de banners para generar html
	 * @param array $params Arreglo de parámetros extra para los banners.
	 * width: indica el ancho máximo. url: indica el url de destino al hacer clic
	 * sobre el banner
	 */
	function banners($banners=array(),$params=null){
		$res = '';
		
		if (count($banners) ){
			$i = 0;
			
			foreach ($banners as $b) {
				$img_attrs = array(
					'src'=>Router::url('/usuarios/obtener_banner/') . $b['Banners']['seccion_id'] . '/' . $b['Banners']['id']
				);
				
				if(isset($params['width'])) {
					$img_attrs['width'] = $params['width'];
				}
				
				$img_tag = $this->Html->tag(
					'img',
					null,
					$img_attrs
				);
				
				if($i++ > 0) echo '<div class="espacio_vertical_10"></div>';
				
				if(!empty($b['Banners']['url'])){
					$a_tag = $this->Html->tag(
						'a',
						$img_tag,
						array(
							'target'=>'_blank',
							'href'=>$b['Banners']['url']
						)
					);
					
					$res .= $a_tag;
				} else {
					$res .= $img_tag;
				}
			}
		}
		
		return $res;
	}
}