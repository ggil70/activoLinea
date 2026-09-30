<?php
    /**
    * Modelo para el manejo de la relación de las transacciones con los perfiles.
    * 
    * @author Adverweb <http://www.adverweb.com>
    */


    /**
    * Manejo de la relación de las transacciones con los perfiles.
    * 
    */
    class TransaccionPerfil extends AppModel{	        
        
	/**
	* Nombre de la tabla en base de datos.
	* 
	* @var string
	*/
	var $useTable = 'transacciones_perfiles_ib';
	

	/**
	* Indica las tablas con las que tiene una relación foránea.
	* 
	* @var array 
	*/
	var $belongsTo = array(
            'PerfilesIb' => array( 
		'className' => 'PerfilesIb',
		'foreignKey' => 'perfil_ib_id'
            )
	);
	
	
	/**
	* Obtiene las transacciones de un perfil.
	* 
	* @param int $perfil
	* @param int $usuario_id
	* @return array
	*/
	public function obtenerTransPerfil($perfil, $usuario_id) {
            $arrData = array();
                                
            if( $result = $this->find(
		'all',
		array(
                    'fields'=>'transaccion_id',
                    'joins' => array(
                        array(
                            'table' => 'transacciones',
                            'alias' => 'T',
                            'type' => 'INNER',
                            'conditions' => array(
				'T.id = TransaccionPerfil.transaccion_id'
                            )
			),
			array(
                            'table' => 'modulos',
                            'alias' => 'M',
                            'type' => 'INNER',
                            'conditions' => array(
                                'M.id = T.modulo_id'
                            )
			)
                    ),
			'conditions'=> array(
                            'TransaccionPerfil.perfil_ib_id' => $perfil,
                            'PerfilesIb.estatus' => 'a',
                            'M.canal_id' => Configure::read('canal.ib'),
					
                            'AND' => array(
				array(
				'OR' => array(
                                        array('TransaccionPerfil.perfil_piloto IS NULL'),
                                            array('TransaccionPerfil.perfil_piloto IN (SELECT perfil_piloto FROM usuarios_perfil_piloto WHERE usuario_id = '.$usuario_id.')')
                                    )
				)
                            )
					
			)
		)
            )){
		foreach ($result AS $i){
                $arrData[] = $i['TransaccionPerfil']['transaccion_id'];
		}
            }
            else{
                $this->log('Fallo transaccion/perfiles - No se encontraron transacciones del perfil ' . $perfil . ' - ' . $this->getDataSource()->error);
            }
		
            return $arrData;
	}
	
	
	
	/**
	 * Devuelve los modulos y las transacciones con su informacion
	 * 
	 * @param array $usuario
	 * @param int $rol_perfil
	 * @return array
	 */
	public function moduloTransacciones($usuario, $rol_perfil) {
		
            $activo = ClassRegistry::init('ActivoComponent');		
		
	    // Consulta los datos basicos del usuario
            App::import('Component', 'Session');
		
            $session = new SessionComponent();
            $datos_basicos = $session->read('usuarios.nibpf3010');

            // Consulta los productos del usuario
            $instrumentos = array();
            if(isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])){
		$res = $activo->invoca(
                        'SUMDSJV',
			array(
                            'req'=>array(
                                'SUMACN'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],
				'SUMFIR'=>'X'
                            )
                        )
		);
			
                    if($res){
                        $instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
			normaliza($instrumentos);
                    }
            }
		
            // en caso de ocurrir un error en los servicios no continua
            if(!count($instrumentos)) return array();
		
		$cuentas  =  array();
		$fcuentas = array();
		$consulta = array();
		$tarjetas = array();
		$inversiones = array();
		$creditos = array();
		
		foreach($instrumentos as $i){

                    if($i['SUMCCY']=='VEB'){
                        //cuentas                        
                        if( in_array($i['SUMTYP'], array('SAV','DDA','NOW','MMK')) ){
                            $cuentas[trim($i['SUMACN'])] = enmascara($i['SUMACN']);         
                        }

                        // tarjetas
                        elseif( $i['SUMTYP']=='CC&' || $i['SUMTYP']=='TCC' || $i['SUMTYP']=='TCE'){
                                $tarjetas[trim($i['SUMACN'])] = enmascara($i['SUMACN']);
                        }
                        // inversiones
                        elseif( $i['SUMTYP']=='CDS' || $i['SUMTYP']=='TDS'){
                                $inversiones[trim($i['SUMACN'])] = enmascara($i['SUMACN']);
                        }
                        // creditos
                        elseif($i['SUMTYP']=='LNS'){
                                $creditos[trim($i['SUMACN'])] = enmascara($i['SUMACN']);
                        }
				        
                    }else {
			//cuentas moneda extranjera                      
			if( in_array($i['SUMTYP'], array('SAV','DDA','NOW','MMK')) ){
                            $fcuentas[trim($i['SUMACN'])] = enmascara($i['SUMACN']); 
                        }
                    }
            
		
                    if( in_array($i['SUMTYP'], array('SAV','DDA','NOW','MMK')) ){
                      $consultas[trim($i['SUMACN'])] = enmascara($i['SUMACN']); 
                    }			 
		}

		
		$modulos = array();
		$modulos_aprobaciones = Configure::read('cuadro_juridico.modulos_aprobaciones');
		
		$transacciones_limites = Configure::read('cuadro_juridico.transacciones.limites');
		
		$transacciones_cuentas = Configure::read('cuadro_juridico.transacciones.cuentas');

		$transacciones_cuentasme = Configure::read('cuadro_juridico.transacciones.cuentasme');

		$transacciones_consultas = Configure::read('cuadro_juridico.transacciones.consultas');

		$transacciones_tarjetas = Configure::read('cuadro_juridico.transacciones.tarjetas');
		
		$transacciones_inversiones = Configure::read('cuadro_juridico.transacciones.inversiones');
		
		$transacciones_creditos = Configure::read('cuadro_juridico.transacciones.creditos');
		
		$transacciones_no_permitidas = Configure::read('cuadro_juridico.transacciones.no_permitidas');
		
		$transaciones_denegadas_rol = Configure::read('cuadro_juridico.transacciones.denegadas_rol');
		
		// Busca las transacciones del perfil y del canal
		if($result = $this->find(
                    'all',
                    array(
			'fields'=>array('Modulo.id', 'Modulo.nombre', 'Transaccion.id', 'Transaccion.nombre'),
                            'joins' => array(
				array(
                                    'table' => 'transacciones',
                                    'alias' => 'Transaccion',
                                    'type' => 'INNER',
                                    'conditions' => array(
					'Transaccion.id = TransaccionPerfil.transaccion_id'
                                    )
				),
				array(
                                    'table' => 'modulos',
                                    'alias' => 'Modulo',
                                    'type' => 'INNER',
                                    'conditions' => array(
                                        'Modulo.id = Transaccion.modulo_id'
                                    )
				)
                            ),
                            'conditions'=> array(
                                'TransaccionPerfil.perfil_ib_id' => $usuario['perfil_ib_id'],
                                'PerfilesIb.estatus' => 'a',
                                'Modulo.canal_id' => Configure::read('canal.ib'),
                                'transaccion_id NOT ' => array_merge($transacciones_no_permitidas, Configure::read('transacciones_basicas.rol.default'), $transaciones_denegadas_rol[$rol_perfil]),
					
                                'AND' => array(
                                    array(
                                        'OR' => array(
                                            array('TransaccionPerfil.perfil_piloto IS NULL'),
                                            array('TransaccionPerfil.perfil_piloto IN (SELECT perfil_piloto FROM usuarios_perfil_piloto WHERE usuario_id = '.$usuario['id'].')')
                                        )
                                    )
                                )
					
                            ),
                                'order'=> array('Modulo.posicion'=>'ASC', 'Transaccion.posicion'=>'ASC')
			)
                    )
                ){
			
                // Guarda los modulos
                foreach ($result AS $m){
				
                        // Verifica si ya existe
                        if(!array_key_exists($m['Modulo']['id'], $modulos)){
                            $modulos[$m['Modulo']['id']]['menu'] = $m['Modulo']['nombre'];
                        }
				
                    // Guarda las transacciones
                    foreach ($result AS $t){
					
                        // Verifica que el modulo sea el mismo
                        if($m['Modulo']['id']==$t['Modulo']['id']){
						
                        // Verifica si ya existe
                        if(!isset($modulos[$m['Modulo']['id']]['transaccion']) || !array_key_exists($t['Transaccion']['id'], $modulos[$m['Modulo']['id']]['transaccion'])){
						
                            $muestra = false;
							
                            if(   
                                in_array($t['Transaccion']['id'], $transacciones_cuentas) && count($cuentas) || in_array($t['Transaccion']['id'], $transacciones_cuentasme) && count($fcuentas) || in_array($t['Transaccion']['id'], $transacciones_consultas) && count($consultas) || 
                                in_array($t['Transaccion']['id'], $transacciones_creditos) && count($creditos) ||
                                in_array($t['Transaccion']['id'], $transacciones_inversiones) && count($inversiones) ||
                                in_array($t['Transaccion']['id'], $transacciones_tarjetas) && count($tarjetas) ||
                                !in_array($t['Transaccion']['id'], array_merge($transacciones_cuentas,$transacciones_cuentasme,$transacciones_consultas, $transacciones_creditos, $transacciones_inversiones, $transacciones_tarjetas))){
                                $muestra = true;
                            }
							
                            //Elimina la transaccion de Pago de Impuestos SENIAT a Terceros de los usuarios tipo Gobierno
                            if(!empty($usuario['tipo_rif']) && $usuario['tipo_rif'] == 'G' && $t['Transaccion']['id'] == 100037){
                                $muestra = false;
                            }     
                            if(!$muestra) continue;
						
                                $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['nombre'] = $t['Transaccion']['nombre'];
                                $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['limite'] = in_array($t['Transaccion']['id'], $transacciones_limites);
                                $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['aprobacion'] = in_array($m['Modulo']['id'], $modulos_aprobaciones);
							
                                $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'] = array();

                                if($t['Transaccion']['id']== 3){
                                    $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'] = $consultas + $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'];								
                                }else if($t['Transaccion']['id']== 101074 || $t['Transaccion']['id']== 101075){
                                   $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'] = $consultas + $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'];
				}else if(in_array($t['Transaccion']['id'], $transacciones_cuentas) ){
                                   $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'] = $cuentas + $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'];
				}else{
                                    if(in_array($t['Transaccion']['id'], $transacciones_cuentasme) ){
					$modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'] = $fcuentas + $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'];
                                    }
				}

                                if( in_array($t['Transaccion']['id'], $transacciones_creditos) ){
                                    $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'] = $creditos + $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'];                                                                
				}
				if( in_array($t['Transaccion']['id'], $transacciones_tarjetas) ){
                                    $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'] = $tarjetas + $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'];
				}
				if( in_array($t['Transaccion']['id'], $transacciones_inversiones) ){
                                    $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'] = $inversiones + $modulos[$m['Modulo']['id']]['transaccion'][$t['Transaccion']['id']]['cuentas'];
				}
                            }
                        }
                    }
		}
            }
            else{
                $this->log('Fallo transaccion/perfiles - No se encontraron transacciones del perfil de juridico - ' . $this->getDataSource()->error);
            }
            return $modulos;
	}
    }
?>