<?php
/**
 * Modelo para el manejo del detalle de las transacciones de los usuarios 
 * jurídicos creados.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo del detalle de las transacciones de los usuarios jurídicos creados.
 * 
 */
class TransaccionJuridicoDetalle extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'transacciones_juridicos_detalle';

	
        /**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo');		        
        
	/**
	 * Indica las tablas con las que tiene una relación foránea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'TransaccionJuridico' => array( 
			'className' => 'TransaccionJuridico',
			'foreignKey' => 'transacciones_juridicos_id',
			'type'=>'RIGHT'
		)
	);
	
	
	
	/**
	 * Busca las transacciones del usuario que les fueron asignadas
	 * al momento de su creacion.
	 * 
	 * @param int $usuario
	 * @param array $transacciones_basicas
	 * @return array
	 */
	function obtenerTransacciones($usuario, $transacciones_basicas){
		$arrData = array();
		
		// Realiza la busqueda
		if( $result = $this->find(
			'all',
			array(
				'conditions'=> array( 
					'usuario_id' => $usuario,
					'transaccion_id not in ('. @implode(',', $transacciones_basicas) .')'
				)
			)
		)){	
			// Recorre el resultado
			foreach ($result AS $i){
				$x = array_merge($i['TransaccionJuridicoDetalle'],$i['TransaccionJuridico']);
				
				// Verifica si ya fue agregada la transaccion
				if(!array_key_exists($x['transaccion_id'], $arrData)) $arrData[$x['transaccion_id']] = array('menu'=>$x['transaccion_id']);
				
				// asigna el detalle si lo encuentra
				if(isset($x['cuenta'])) $arrData[$x['transaccion_id']]['cuenta'][]= desencriptar ($x['cuenta']);
				if(isset($x['aprobaciones'])) $arrData[$x['transaccion_id']]['aprobacion'] = $x['aprobaciones'];
				if(isset($x['limite'])) $arrData[$x['transaccion_id']]['limite'] = formatea_monto_fromWs($x['limite']);
			}
		}
		else{
			$this->log('Fallo transaccion/perfiles - No se encontraron transacciones del usuario ' . $usuario . ' - ' . $this->getDataSource()->error);
		}
		
		return $arrData;
	}
	
	
	/**
	 * Registra las transacciones del usuario
	 * 
	 * @param array $transacciones_basicas
	 * @param array $transacciones_asignadas
	 * @param int $usuario_id
         * @param array $tarjetas_empcorp
	 */
	function guardarTransacciones($transacciones_basicas, $transacciones_asignadas, $usuario_id, $tarjetas_empcorp){
		$consulta = false;
		
		// Guarda las transacciones basicas
		foreach ($transacciones_basicas as $b){
			$res=$this->TransaccionJuridico->save( array( 
				'TransaccionJuridico' => array(
					'usuario_id' => $usuario_id, 
					'transaccion_id' => $b
				)
			), false);
			unset($this->TransaccionJuridico->id);
		}

		// Recorre las transacciones asignadas al usuario
		foreach ($transacciones_asignadas as $x){
			if(in_array($x['menu'], array(3,4,5,6))){
				$consulta = true;
			}
			                        
			// Guardado del permiso de las transacciones
			if($this->TransaccionJuridico->save( array(
				'TransaccionJuridico'=>array(
					'usuario_id'=> $usuario_id,
					'transaccion_id'=> $x['menu']
				)
			), false)){

				// Guardado del detalle de las transacciones asignadas
				if(
					isset($x['aprobacion']) || 
					isset($x['cuenta']) ||
					isset($x['limite'])
				){

					// Detalle de las transacciones asignadas
					$detalle = array(
						'TransaccionJuridicoDetalle'=>array(
							'transacciones_juridicos_id'=> $this->TransaccionJuridico->id,
						)
					);
					if(isset($x['aprobacion']) && !empty($x['aprobacion'])) $detalle['TransaccionJuridicoDetalle']['aprobaciones'] = $x['aprobacion'];
					if(isset($x['limite'])) $detalle['TransaccionJuridicoDetalle']['limite'] = formatea_monto_toWs($x['limite']);

					// si selecciono cuentas realiza el registro por cada cuenta
					if(isset($x['cuenta'])){
                                                                                                
						foreach ($x['cuenta'] as $c) {

                                                        //Verifica si el menu es de tarjetas de credito para validar corporativa
                                                        if($x['menu']==100025 || $x['menu']==4){
                                                            //Si selecciono tarjetas corporativas busca las tarjetas asociadas de cada tarjeta padre
                                                            // y las guarda con los mismos permisos
                                                            if(!empty($tarjetas_empcorp) && array_key_exists($c,$tarjetas_empcorp)) {                                                                                                                                
                                                                foreach ($tarjetas_empcorp[$c] as $t) {
                                                                    if (!empty($t)){                                                                        
                                                                        $detalle['TransaccionJuridicoDetalle']['cuenta'] = encriptar($t);
                                                                        $this->save( $detalle, false);                                                                    
                                                                        unset($this->id);
                                                                    }                                                                                                                                        
                                                                }   
                                                                $detalle['TransaccionJuridicoDetalle']['cuenta'] = encriptar($c);
                                                                $this->save( $detalle, false);
                                                                unset($this->id);
                                                            }else{
                                                                //Si es una tarjeta comun la guarda normal    
                                                                $detalle['TransaccionJuridicoDetalle']['cuenta'] = encriptar($c);
                                                                $this->save( $detalle, false);
                                                                unset($this->id);
                                                            }                                                               
                                                        }else{
                                                                $detalle['TransaccionJuridicoDetalle']['cuenta'] = encriptar($c);
                                                                $this->save( $detalle, false);                                                                
                                                                unset($this->id);
                                                        }                                                                                                                                                                                                                                                                                                                                                                                                                                                                     
						}
					}
					elseif( isset($x['aprobacion']) || isset($x['limite']) ){
						// Guardado del detalle de las transacciones en caso de no haber seleccionado alguna cuenta
						$this->save( $detalle, false);
						unset($this->id);
					}
				}

			}
			unset($this->TransaccionJuridico->id);
		}
		if($consulta){
			$this->TransaccionJuridico->save( array( 
				'TransaccionJuridico' => array(
					'usuario_id' => $usuario_id, 
					'transaccion_id' => 25
				)
			), false);
			unset($this->TransaccionJuridico->id);
		}
	}
	
	
	/**
	 * Retorna el detalle de una transacción del usuario.
	 * 
	 * @param int $usuario_id
	 * @param int $transaccion_id
	 * @param string $cuenta
	 * @return array|boolean
	 */
	function obtener($usuario_id, $transaccion_id, $cuenta=null){
		$opciones = array(
			'TransaccionJuridico.usuario_id'=>$usuario_id,
			'TransaccionJuridico.transaccion_id'=>$transaccion_id
		);
		
		if($cuenta){
			$opciones['TransaccionJuridicoDetalle.cuenta'] = encriptar($cuenta);
		}
	
		$res = $this->find(
			'first',
			array(
				'conditions'=>$opciones
			)
		);
		
		if($res){
			// Verificar si el límite está vacío
			// En el caso de algunas transacciones esto se interpreta como
			// que se debe tomar el límite del usuario master en caso de tenerlo
			// sino el del banco
		
			if(empty($res['TransaccionJuridicoDetalle']['limite'])){
				
                                $uc = ClassRegistry::init('UsuarioCanal');                                
                                $this->LimitesVip = ClassRegistry::init('LimitesVip');
                                
				// Buscar los datos del usuario actual
				
				$usuario = $uc->find(
					'first',
					array(
						'conditions'=>array(
							'UsuarioCanal.usuario_id'=>$usuario_id
						)
					)
				);
			
				// Buscar el usuario master
				
				$master = $uc->find(
					'first',
					array(
						'conditions'=>array(
							'rol_juridico_id'=>1,
							'Usuario.rif'=>$usuario['Usuario']['rif'],
							'Usuario.tipo_rif'=>$usuario['Usuario']['tipo_rif']
						)
					)
				);
				
				// El límite depende del tipo de transacción
				
				if(in_array($transaccion_id,array(100021,100022,100025,100026,100036,100037,101072,101073))){
					// Transacciones mismo banco
					if($master && is_numeric($master['UsuarioCanal']['limite_internas_terceros'])){
						// Tomamos el límite del master
						$res['TransaccionJuridicoDetalle']['limite'] = $master['UsuarioCanal']['limite_internas_terceros'];
					} else {
						// Tomamos el limite del banco
						$res['TransaccionJuridicoDetalle']['limite'] = _p('limite_internas_terceros_J');
					}
				}else if(in_array($transaccion_id,array(39,40))){
					// Transacciones mismo banco nomina y proveedores
                                    
                                        if($transaccion_id==39){
                                            if($master && is_numeric($master['UsuarioCanal']['limite_vip'])){
                                                    // Tomamos el límite vip
                                                    $limites_vip = $this->LimitesVip->buscarLimiteById($master['UsuarioCanal']['limite_vip']);                    						
                                                    $res['TransaccionJuridicoDetalle']['limite'] = $limites_vip['limite_internas_terceros_nomina'];
                                            } elseif($master && is_numeric($master['UsuarioCanal']['limite_internas_terceros_nomina'])){
                                                    // Tomamos el límite del master
                                                    $res['TransaccionJuridicoDetalle']['limite'] = $master['UsuarioCanal']['limite_internas_terceros_nomina'];
                                            } else {
                                                    // Tomamos el limite del banco
                                                    $res['TransaccionJuridicoDetalle']['limite'] = _p('limite_internas_terceros_nomina');
                                            }
                                        }else{
                                            if($master && is_numeric($master['UsuarioCanal']['limite_vip'])){
                                                    // Tomamos el límite vip
                                                    $limites_vip = $this->LimitesVip->buscarLimiteById($master['UsuarioCanal']['limite_vip']);                    						
                                                    $res['TransaccionJuridicoDetalle']['limite'] = $limites_vip['limite_internas_terceros_proveedores'];
                                            } elseif($master && is_numeric($master['UsuarioCanal']['limite_internas_terceros_proveedores'])){
                                                    // Tomamos el límite del master
                                                    $res['TransaccionJuridicoDetalle']['limite'] = $master['UsuarioCanal']['limite_internas_terceros_proveedores'];
                                            } else {
                                                    // Tomamos el limite del banco
                                                    $res['TransaccionJuridicoDetalle']['limite'] = _p('limite_internas_terceros_proveedores');
                                            }
                                        }
					
                                        
                                } else if(in_array($transaccion_id,array(100023,100024,100027,100028))){
					// Transacciones a otros bancos
					if($master && is_numeric($master['UsuarioCanal']['limite_externas'])){
						// Tomamos el límite del master
						$res['TransaccionJuridicoDetalle']['limite'] = $master['UsuarioCanal']['limite_externas'];
					} else {
												
						// Tomamos el limite del banco
						$res['TransaccionJuridicoDetalle']['limite'] = _p('limite_externas_J');
					}
				} else if(in_array($transaccion_id,array(100016,100017))){
                                        
					// Transacciones a otros bancos nomina y proveedores
                                    
                                        if($transaccion_id==100016){
                                            if($master && is_numeric($master['UsuarioCanal']['limite_vip'])){
                                                // Tomamos el límite vip
                                                $limites_vip = $this->LimitesVip->buscarLimiteById($master['UsuarioCanal']['limite_vip']);                    						
                                                $res['TransaccionJuridicoDetalle']['limite'] = $limites_vip['limite_externas_nomina'];
                                            } elseif($master && is_numeric($master['UsuarioCanal']['limite_externas_nomina'])){
						// Tomamos el límite del master
						$res['TransaccionJuridicoDetalle']['limite'] = $master['UsuarioCanal']['limite_externas_nomina'];
                                            } else {
                                                
                                                // Tomamos el limite del banco
                                                $res['TransaccionJuridicoDetalle']['limite'] = _p('limite_externas_nomina');
                                            }
                                        }else{
                                            if($master && is_numeric($master['UsuarioCanal']['limite_vip'])){
                                                // Tomamos el límite vip
                                                $limites_vip = $this->LimitesVip->buscarLimiteById($master['UsuarioCanal']['limite_vip']);                    						
                                                $res['TransaccionJuridicoDetalle']['limite'] = $limites_vip['limite_externas_proveedores'];
                                            } elseif($master && is_numeric($master['UsuarioCanal']['limite_externas_proveedores'])){
						// Tomamos el límite del master
						$res['TransaccionJuridicoDetalle']['limite'] = $master['UsuarioCanal']['limite_externas_proveedores'];
                                            } else {
                                                
                                                // Tomamos el limite del banco
                                                $res['TransaccionJuridicoDetalle']['limite'] = _p('limite_externas_proveedores');
                                            }
                                        }					
				}
			}
		
			return $res['TransaccionJuridicoDetalle'];
		} else {
			return false;
		}
	}
}
?>