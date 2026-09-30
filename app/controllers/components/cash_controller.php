<?php
/**
 * Controlador para todas las operaciones de cash management 
 */

/**
 * Controlador para todas las operaciones de cash management 
 */
class CashController extends AppController{
	/**
	 *
	 * @var array Modelos que usa este controlador 
	 */
	var $uses = array(
		'TransaccionPendiente',
		'TransaccionBeneficiario', 
		'TransaccionJuridicoDetalle',
		'DetallePagoProveedor',
		'Bitacora',
		'PagoCash',
		'AfiliacionCash',
		'AprobacionTransaccion',
		'AcumuladoTransaccion',
                'Empresa',
                'UsuarioCanal',
                'LimitesVip'
	);
	
	/**
	 *
	 * @var array Componentes que usa este controlador
	 */
	var $components = array('Activo', 'Session', 'Email', 'Sms', 'Compensacion');
	
	/**
	 *
	 * @var array Ayudantes que usan las vistas de este controlador
	 */
	var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');
        
        /**
	 * Acción que muestra un listado con los últimos pagos de nómina y proveedores
	 * realizados por la empresa
	*/
	function seguimiento_gestion_filtros(){
            
		// Valida el permiso de la transaccion
		if( !validarTransaccion('43') ) $this->redirect('/consultas/integral');
		
                $this->layout = 'interna';			
		$this->set('seccion_actual','cash.seguimiento_gestion');
		$usuario = $this->Session->read('usuarios.datos');	
										
		$tipos = array(
                    'n'=>'Pago de Nómina',
                    'p'=>'Pago a Proveedores'                    
		);
                
                $estatus = array(
                    'Pendiente por Procesar'=>'Pendiente por Procesar',
                    'Procesado'=>'Procesado',
                    'Procesado Parcialmente'=>'Procesado Parcialmente',
                    'Procesado con Rechazos'=>'Procesado con Rechazos'
		);
                
                if(!empty($this->data['TransaccionPendiente'])){			
                    
                    $this->TransaccionPendiente->set($this->data);                    
                    $this->Session->write('cash.seguimiento', $this->data);
                    $this->redirect('seguimiento_gestion');
                    		
		}
                	
		$this->set(compact('tipos','estatus'));		

	}
        
	/**
	 * Acción que muestra un listado con los últimos pagos de nómina y proveedores
	 * realizados por la empresa
	*/
	function seguimiento_gestion(){
            
		// Valida el permiso de la transaccion
		if( !validarTransaccion('43') ) $this->redirect('/consultas/integral');
		
		$usuario = $this->Session->read('usuarios.datos');	
		if($this->Session->check('cash.seguimiento')){
                    $data = $this->Session->read('cash.seguimiento');
		} else {
                    $this->redirect('seguimiento_gestion_filtros');
		}
                
                $data = $data['TransaccionPendiente'];
                
                // Condiciones para la busqueda de transacciones guardadas
                
                 
                if(!empty($data['lote'])){
                    $conditions['TransaccionPendiente.lote'] = $data['lote'];                                      
                }else{
                    
                    if(!empty($data['operacion'])){
                        if($data['operacion']=='n'){
                           $conditions['TransaccionPendiente.tipo'] = array('nxa','nxm','nxo'); 
                        }else{
                            $conditions['TransaccionPendiente.tipo'] = array('vxa','vxm','vxo');
                        }                     
                    }else{
                        $conditions['TransaccionPendiente.tipo'] = array('vxa','vxm','vxo','nxa','nxm','nxo');
                    }
                    
                    $conditions['TransaccionPendiente.fecha_hora_registro >=']=formatea_fecha_toMSSQL(
                        $data['fechaDesde']['day'],
                        $data['fechaDesde']['month'],
                        $data['fechaDesde']['year']
                    ).' 00:00:00.000';

                    $conditions['TransaccionPendiente.fecha_hora_registro <=']=formatea_fecha_toMSSQL(
                            $data['fechaHasta']['day'],
                            $data['fechaHasta']['month'],
                            $data['fechaHasta']['year']
                    ).' 23:59:59.000';
                }
                                                
                $conditions['TransaccionPendiente.rif'] = $usuario['rif'];
                $conditions['TransaccionPendiente.tipo_rif'] = $usuario['tipo_rif'];
                $conditions['TransaccionPendiente.estatus not '] = array('g','pa','el');
                				
		$listado = $this->TransaccionPendiente->find(
                    'all',
                    array(
                        'conditions'=>$conditions,
                        'order'=>'TransaccionPendiente.fecha_hora_registro desc',
                    )
		);

		$tipo_lote = array();
                
		if($listado){
                    
			foreach($listado as &$i){
                            // Completar información con el AS400
                            $this->Activo->seguimientoGestionCabecera($i);
			}
						
			$tipo_lote['n'] = array();
			$tipo_lote['v'] = array();
			
			// Agrupa las transacciones por tipo
			foreach($listado as $m => $d){
                            if(!empty($data['estatus'])){                                
                                if($data['estatus']==$d['TransaccionPendiente']['descripcion_estatus']){
                                    $tipo_lote[$d['TransaccionPendiente']['tipo'][0]] []= $d;
                                }                                
                            }else{
                                $tipo_lote[$d['TransaccionPendiente']['tipo'][0]] []= $d;
                            }                            
			}
			
			// si no contiene data el tipo de transaccion se elimina
			foreach ($tipo_lote as $k => $v) if(!count($tipo_lote[$k])) unset($tipo_lote[$k]);
		}
		
		$this->layout = 'interna';
	
		$this->set(compact('listado','tipo_lote'));
		$this->set('seccion_actual','cash.seguimiento_gestion');

	}
	
	/**
	 * Acción que muestra el detalle de un pago y permite descargar el mismo
	 * en formato PDF y Excel
	 *
	*/
	function seguimiento_gestion_detalle(){
		
                // Valida el permiso de la transaccion
		if( !validarTransaccion('43') ) $this->redirect('/consultas/integral');
		
		$usuario = $this->Session->read('usuarios.datos');	
		
		if(!isset($this->data['TransaccionPendiente'])){
			$this->redirect('seguimiento_gestion');
		}
		
		if(isset($this->data['TransaccionPendiente']['formato'])){
			$formato = $this->data['TransaccionPendiente']['formato'];
		}
		
		$data = $this->TransaccionPendiente->findById($this->data['TransaccionPendiente']['id']);
		
		// Completar información con el AS400
		$this->Activo->seguimientoGestion($data);
		
		$this->data = $data;

		if(
			$this->data['TransaccionPendiente']['rif'] != $usuario['rif'] ||
			$this->data['TransaccionPendiente']['tipo_rif'] != $usuario['tipo_rif']
		){
		
			$this->redirect('seguimiento_gestion');
		}
		
		if(isset($formato)){
			$this->layout = 'nada';
			
			// Verifica si es un pago masivo de proveedores
			if($this->data['TransaccionPendiente']['tipo_carga']=='a' && $this->data['TransaccionPendiente']['tipo'][0]=='v'){
				if(count($this->data['Beneficiarios'])){
					for($i=0; $i<count($this->data['Beneficiarios']); $i++){
						
						// Verifica si la instruccion de pago tiene detalle
						$detalle = $this->DetallePagoProveedor->find(
							'all',
							array(
								'conditions'=> array(
									'beneficiario_id'=> $this->data['Beneficiarios'][$i]['id']
								)
							)
						);
						
						if($detalle) $this->data['Beneficiarios'][$i]['detalle'] = $detalle;
					}
				}
			}
			
			if($formato=='txt'){
				$this->render('seguimiento_gestion_descarga_txt');
			} else {
				$this->render('seguimiento_gestion_descarga_xls');
			}
		} else {
			$bancos = $this->Activo->buscarBancos();
			$this->set(compact('bancos','tipo_lote'));
			$this->layout = 'interna';
			$this->set('seccion_actual','cash.seguimiento_gestion');
		}
	}
	
	/**
	 * Acción para mostrar el listado de pagos de nómina o proveedores
	 * @param string $tipo Indica el tipo de los pagos a mostrar. 'n' para nómina,
	 * 'p' para proveedores
	 */
	function pagos($tipo=null){
		if( empty($tipo) || !in_array($tipo, array('n','p')) ) $this->redirect('/consultas/integral');
		
		// Valida el permiso de la transaccion
		if(
			($tipo=='n' && (!validarTransaccion(39,100016) || verificaAfiliados('n'))) ||
			($tipo=='p' && (!validarTransaccion(40,100017) || verificaAfiliados('p')))
		) $this->redirect('/consultas/integral');
		
		$usuario = $this->Session->read('usuarios.datos');
		
		$this->Session->delete('cash.pagos.descarga');
		$this->Session->delete('cash.pagos.operacion');
		$this->Session->delete('cash.pagos.data');
		
		if(!empty($this->data['PagoCash']) && !isset($this->data['PagoCash']['paginando'] ) ){
			$this->PagoCash->set($this->data);
			
			if($this->PagoCash->validates(array('fieldList'=>array('accion','seleccion')))){
	
				switch($this->data['accion']){
					case 'crear':
						$this->Session->write('cash.pagos.operacion', 
							array(
								'motivo'=>'crear',
								'tipo'=>$tipo,
								'url_volver_modificar'=>'pagos/'.$tipo
							)
						);
						$this->redirect('pago_modificar');
						break;
					
					case 'detalle':
						$estado = $this->PagoCash->buscar($this->data['PagoCash']['id'],$usuario['id']);

						if($estado){
							$this->Session->write('cash.pagos.operacion', 
								array(
									'tipo'=>$tipo,
									'url_volver'=>'pagos/'.$tipo
								)
							);
							$this->Session->write('cash.pagos.data', $estado);
							$this->redirect('pago_detalle');
						}
						break;
					
					case 'eliminar':
						$this->Session->write('cash.pagos.operacion', 
							array(
								'tipo'=>$tipo,
								'url_volver'=>'pagos/'.$tipo,
								'seleccion'=>$this->data['seleccion']
							)
						);
						$this->redirect('pago_eliminar');
						break;
				}
			}
		}
		
		// Condiciones para la busqueda de transacciones guardadas
		$conditions = array(
			'TransaccionPendiente.usuario_id' => $usuario['id'],
			'TransaccionPendiente.estatus' => 'g',
		);
		
		if($tipo=='n'){
			$conditions['TransaccionPendiente.tipo'] = array('nxa','nxm','nxo');
		}
		else{
			$conditions['TransaccionPendiente.tipo'] = array('vxa','vxm','vxo');
		}
		
		// Paginacion
		$total_registros = $this->TransaccionPendiente->find(
			'count',
			array( 'conditions'=>$conditions )
		);	
		
		
		$offset = 0;

		if(
			isset($this->data['PagoCash']['offset']) &&
			preg_match('/^\d+$/',$this->data['PagoCash']['offset'])
		){
			$offset = intval($this->data['PagoCash']['offset']);			
		}
		
		$listado = array();
		
		if($total_registros){
			$listado = $this->TransaccionPendiente->find(
				'all',
				array(
					'fields'=>array('id','lote','cuenta_origen','fecha_hora_valor','descripcion','monto','UsuarioCreador.login'),
					'conditions'=>$conditions,
					'order'=>'TransaccionPendiente.id desc',
					'offset'=>$offset,
					'limit'=>10
				)
			);
		}
		
		$this->layout = 'interna';
		
		switch($tipo){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos Nómina');
				$this->set('encabezado_vista','pagos de nómina');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores');
				$this->set('encabezado_vista','pagos de proveedores');
				break;
		}
		
		if($this->Session->check('cash.pago.exito')){
			$pago_guardado = $this->Session->read('cash.pago.exito');
			$this->set('pago_guardado',$pago_guardado);
			$this->Session->delete('cash.pago.exito');
		}
		
		$this->set(compact('listado','tipo','total_registros'));
	}
	
	/**
	 * Acción para indicar el detalle del pago de nómina o proveedores 
	 */
	function pago_detalle(){
		if(!$this->Session->check('cash.pagos.operacion') && !$this->Session->check('cash.pagos.data')){
			$this->redirect('/consultas/integral');
		}
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$dataTemp = $this->Session->read('cash.pagos.data');

		if(!empty($this->data['accion'])){
			switch($this->data['accion']){
				case 'eliminar':
					$this->Session->write('cash.pagos.operacion', 
						array(
							'tipo'=>$estado['tipo'],
							'url_volver'=>'pago_detalle',
							'seleccion'=>$dataTemp['PagoCash']['id']
						)
					);
					
					$this->redirect('pago_eliminar');
					break;
				
				case 'procesar':
					if($this->PagoCash->validarFecha(array('fecha_valor'=>$dataTemp['PagoCash']['fecha_valor']))){
						$this->Session->write('cash.pagos.operacion', 
							array(
								'tipo'=>$estado['tipo'],
								'url_volver_procesar'=>'pago_detalle',
								'procesar_detalle'=>true
							)
						);
						
						$this->redirect('pago_procesar_confirmar');
					}
					
					break;
				
				case 'modificar':
					$this->Session->write('cash.pagos.operacion', 
						array(
							'tipo'=>$estado['tipo'],
							'url_volver_modificar'=>'pago_detalle',
							'modificar_detalle'=>true
						)
					);
					
					$this->Session->write('cash.pagos.data', $dataTemp);					
					
					$this->redirect('pago_modificar');
					break;
			}
		}
		
		$bancos = $this->Activo->buscarBancos();
		
		$this->data = $dataTemp;
		
		$this->set(compact('estado','usuario','bancos'));
			
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos Nómina');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores');
				break;
		}
		
	}
	
	/**
	 * Acción para modificar o crear pagos manualmente
	 * @param string $volver Indica si viene de la confirmación
	 */
	function pago_modificar($volver=null){
		if(!$this->Session->check('cash.pagos.operacion')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');                
		$usuario = $this->Session->read('usuarios.datos');
	               
		// Contar el número de aprobaciones de la transacción
		
		$aprobaciones = 0;
		
		$temp = $this->Session->read('cash.pagos.data');
		
		$beneficiarios = $this->AfiliacionCash->listar($usuario, $estado['tipo']);
		
		foreach($beneficiarios as &$i){
			// Validar el estatus del nro de cuenta
			// Siempre y cuando sea cuenta banco activo
			if(preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/',desencriptar($i['AfiliacionCash']['numero_cuenta']))){
			
				$productoValidar = substr(desencriptar($i['AfiliacionCash']['numero_cuenta']),-10);
				$valorCuenta= array(
					'NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 
					'NROPARAM' => 5,
					'NACIONALIDAD'=>trim($i['AfiliacionCash']['tipo_cedula']),
					'NROIDENTIFICACION'=>str_pad(trim(desencriptar($i['AfiliacionCash']['cedula'])),12,'0',STR_PAD_LEFT)
				);
				
				$datos = $this->Activo->validarProducto($valorCuenta);				
				
				$error = (isset($datos['NIBPF3010Return']['error']['ERNU01'])) ? intval($datos['NIBPF3010Return']['error']['ERNU01']) : 0;
				
				if($error!=0 || !in_array($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'],array('A','T'))){
				
					if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
						$i['AfiliacionCash']['controlada'] = true;
					} else {
						$i['AfiliacionCash']['inactiva'] = true;
					}
				}
			}
		}
		
		// Ordenar arreglo
		
		usort($beneficiarios,'ordernar_afiliacioncash_por_id');

		if(isset($estado['modificar_detalle'])){
			$aprobaciones = $this->AprobacionTransaccion->find(
				'count',
				array(
					'conditions'=>array(
						'AprobacionTransaccion.transaccion_id'=>$temp['PagoCash']['id']
					)
				)
			);
		}
		
		if( empty($this->data['PagoCash']) ){
			if(isset($volver) || isset($estado['modificar_detalle'])){
				$this->data = $this->Session->read('cash.pagos.data');
				
				// Indexar beneficiarios por id de afiliación
				$bs = array();
				
				foreach($beneficiarios as $xd){
					foreach($this->data['Beneficiarios'] as $j){
						if(
							$xd['AfiliacionCash']['tipo_cedula'] == $j['tipo_cedula'] &&
							desencriptar($xd['AfiliacionCash']['cedula']) == desencriptar($j['cedula'])
						){
							$j['monto'] = formatea_monto_fromWs($j['monto']);
							$bs[$xd['AfiliacionCash']['id']] = $j;
							break;
						}
					}
				}
				
				if(isset($volver) && count($bs)) $estado['modificar_detalle'] = true;
				
				$this->data['Beneficiarios']=$bs;
			} else {
				// Numero de lote
				do{
                                    $lote = rand(111111111, 999999999);
				}while(!$this->TransaccionPendiente->checkLote($lote));
				$this->data['PagoCash']['lote'] = $lote;
			}
		} else {
			$this->data['PagoCash']['cantidad_aprobaciones'] = 0;
			// Calculo del monto total del lado del servidor
			$this->data['PagoCash']['monto_total'] = 0;
			$this->data['PagoCash']['monto_total_it'] = 0;
			$this->data['PagoCash']['monto_total_e'] = 0;
			
			if(isset($this->data['Beneficiarios'])){
				// Recorre los beneficiarios
				foreach ($beneficiarios AS $b){
					// verifica que sea el seleccionado
					if(array_key_exists($b['AfiliacionCash']['id'], $this->data['Beneficiarios'])){
						$monto = $this->data['Beneficiarios'][$b['AfiliacionCash']['id']]['monto'];
						
						$this->data['PagoCash']['monto_total'] += formatea_monto_toWs($monto);
						
						if(substr(desencriptar($b['AfiliacionCash']['numero_cuenta']),0,4)=='0171'){
							$this->data['PagoCash']['monto_total_it'] += formatea_monto_toWs($monto);
						} else {
							$this->data['PagoCash']['monto_total_e'] += formatea_monto_toWs($monto);
						}
					}
				}
			}
			
			$this->data['PagoCash']['monto_total'] = formatea_monto_fromWs($this->data['PagoCash']['monto_total']);
			$this->data['PagoCash']['monto_total_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_total_it']);
			$this->data['PagoCash']['monto_total_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_total_e']);
			
			// Limite del usuario
			if($usuario['rol_juridico_id']>2){
				$transaccion_detalle_it = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					$estado['tipo']=='n' ? 39 : 40
				);
				
				$transaccion_detalle_e = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					$estado['tipo']=='n' ? 100016 : 100017
				);
				
				if($transaccion_detalle_it) {
					$this->data['PagoCash']['monto_limite_usuario_it'] = $transaccion_detalle_it['limite'];
				} else {
					$this->data['PagoCash']['monto_limite_usuario_it'] = '0,00'; // Denegar permiso a través de los limites
				}
				
				if($transaccion_detalle_e) {
					$this->data['PagoCash']['monto_limite_usuario_e'] = $transaccion_detalle_e['limite'];
				} else {
					$this->data['PagoCash']['monto_limite_usuario_e'] = '0,00'; // Denegar permiso a través de los limites
				}
			} else {
                            
                            if($estado['tipo']=='n'){
                                $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
				$this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
                            }else{
                                $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');
				$this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
                            }
				
			}
			
			if($this->data['PagoCash']['monto_limite_usuario_it']){
				$this->data['PagoCash']['monto_limite_usuario_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_it']);
			} else {
				$this->data['PagoCash']['monto_limite_usuario_it'] = ''; // Ilimitado
			}
			
			if($this->data['PagoCash']['monto_limite_usuario_e']){
				$this->data['PagoCash']['monto_limite_usuario_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_e']);
			} else {
				$this->data['PagoCash']['monto_limite_usuario_e'] = ''; // Ilimitado
			}
			
			// Validacion de los campos
			$this->PagoCash->set($this->data);
			
			if ($this->PagoCash->validates(array('fieldList' => array(
				'cuentaDebitar',
				'descripcion',
				'lote',
				'fecha_valor',
				'monto_total'
			)))){
				$this->data['PagoCash']['id'] = '';
				
				if(isset($estado['modificar_detalle'])){
					$this->data['PagoCash']['id'] = $temp['PagoCash']['id'];
					
					
					if($aprobaciones > 0){	
						// Si la transacción ya tiene aprobaciones, no modificar el nro de lote
						
						$this->data['PagoCash']['lote'] = ltrim($temp['PagoCash']['lote'], "0");
					}
				}
				
				if(!isset($this->data['Beneficiarios']) || !count($this->data['Beneficiarios'])){
					$this->PagoCash->validationErrors['beneficiario'] = _m('uno.seleccion.requerida','de los beneficiarios');
				} else if(!$this->TransaccionPendiente->checkLote($this->data['PagoCash']['lote'],$this->data['PagoCash']['id'])){
					$this->PagoCash->validationErrors['lote'] = _m('lote.repetido');
				} else {
					if(count ($this->data['Beneficiarios'])<=10){
						
						// guardado en la sesion
						$estado['url_volver_procesar'] = 'pago_modificar/v';
						$this->Session->write('cash.pagos.operacion', $estado);
						
						if(isset($temp['PagoCash'])) $this->data['PagoCash'] = array_merge($temp['PagoCash'],$this->data['PagoCash']);
						
						$bs = array();	
						
						// Recorre los beneficiarios
						foreach ($beneficiarios AS $b){
							
							// verifica que sea el seleccionado
							if(array_key_exists($b['AfiliacionCash']['id'], $this->data['Beneficiarios'])){
								if( 
									isset($this->data['Beneficiarios'][$b['AfiliacionCash']['id']]['seleccionado']) &&
									!isset($b['AfiliacionCash']['inactiva']) &&
									!isset($b['AfiliacionCash']['controlada'])
								){
									$tempData = $b['AfiliacionCash'];
									$tempData['monto'] = formatea_monto_toWs($this->data['Beneficiarios'][$b['AfiliacionCash']['id']]['monto']);
									unset($tempData['id']);
									$bs []= $tempData;
								}
							}
						}
						
						$this->data['Beneficiarios'] = $bs;
						
						$this->Session->write('cash.pagos.data', $this->data);
						
						// Si elige la opcion de guardar no muestra confirmacion ni resultado                                                
						if($this->data['accion']=='guardar'){
							$this->redirect('pago_guardar_advertencia');
						}
						else if($this->data['accion']=='modificar'){
							$this->redirect('pago_modificar_confirmar');
						}
						else{
							$this->redirect('pago_procesar_confirmar');
						}
					} else {
						$this->PagoCash->validationErrors['beneficiario'] =_m('lote.limite.diez');
					}
				}
			}
		}
		
		$this->data['PagoCash']['cantidad_aprobaciones'] = $aprobaciones;		
		$cuentas = $this->Activo->buscarCuentasSaldos();
		
		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('estado','usuario','cuentas','beneficiarios', 'bancos'));
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				if(isset($this->data['PagoCash']['id']) && !isset($this->data['accion'])){
					$this->set('titulo','Pagos Nómina - Modificar');
					$this->set('instruccion','A continuaci&oacute;n proceda a modificar los datos del pago');
				} else {
					$this->set('titulo','Pagos Nómina - Carga Manual');
					$this->set('instruccion','A continuaci&oacute;n proceda a ingresar los datos del pago');
				}
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				if(isset($this->data['PagoCash']['id']) && !isset($this->data['accion'])){
					$this->set('titulo','Pagos Proveedores - Modificar');
					$this->set('instruccion','A continuaci&oacute;n proceda a modificar los datos del pago');
				} else {
					$this->set('titulo','Pagos Proveedores - Carga Manual');
					$this->set('instruccion','A continuaci&oacute;n proceda a ingresar los datos del pago');
				}
				break;
		}
	}
	
        /**
	 * Acción para mostrar un mensaje de advertencia antes de guardar un pago manual 
	 */
	function pago_guardar_advertencia(){
		
                $estado = $this->Session->read('cash.pagos.operacion');                
                $usuario = $this->Session->read('usuarios.datos');
                $this->data = $this->Session->read('cash.pagos.data');		
                $this->data['accion']='guardar';                
                
                $this->set(compact('estado','usuario'));

		$this->layout = 'interna';
                
                switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');				
				$this->set('titulo','Pagos Nómina - Carga Manual');				
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores - Carga Manual');				
				break;
		}	
	}
        
	/**
	 * Acción para confirmar la modificación o creación de un pago manual 
	 */
	function pago_modificar_confirmar(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$this->data = $this->Session->read('cash.pagos.data');
		
		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('estado','usuario','bancos'));

		$this->layout = 'interna';

		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				if(isset($this->data['PagoCash']['id'])){
					$this->set('titulo','Pagos Nómina - Modificar');
				} else {
					$this->set('titulo','Pagos Nómina - Carga Manual');
				}
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				if(isset($this->data['PagoCash']['id'])){
					$this->set('titulo','Pagos Proveedores - Modificar');
				} else {
					$this->set('titulo','Pagos Proveedores - Carga Manual');
				}
				break;
		}
	}
	
	/**
	 * Acción para indicar el resultado de la modificación o creación del pago 
	 */
	function pago_modificar_resultado(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$this->data = $this->Session->read('cash.pagos.data');
		
		$exito = false;
		
		// Validación anti fraude
		$cuentas = $this->Activo->buscarCuentas();
		
		if(
			!in_array($this->data['PagoCash']['cuentaDebitar'], array_keys($cuentas))
		){
			$this->redirect('/consultas/integral');
		}
		//  Fin validación anti fraude
		
		if(isset($this->data['PagoCash']['id'])) $this->TransaccionPendiente->id = $this->data['PagoCash']['id'];
		
		$ds = $this->TransaccionPendiente->getDataSource();

		$ds->begin($this->TransaccionPendiente);
		
		// Buscar la cantidad de aprobaciones requeridas para la transacción
		// Todos los tipos de pago cash tienen la misma cantidad de aprobaciones
		// debido a que en la configuración de usuarios el combo es para los cuatro tipo de pagos
		// buscamos uno por uno y nos quedamos con el que exista, sin importar cual es debido a la
		// premisa anterior
		$transaccion_detalle = false;
		
		foreach(array(39,100016,40,100017) as $i){
			$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
				$usuario['id'],
				$i
			);
			
			if($transaccion_detalle) break;
		}
		
		$nro_aprobaciones = 3; // Nunca debería quedar en este valor
		
		if( isset($transaccion_detalle['aprobaciones']) ){
			$nro_aprobaciones = $transaccion_detalle['aprobaciones'];
		}
			
		$internas = false;
		$externas = false;
		
		foreach($this->data['Beneficiarios'] as &$i){			
			// Verifica si es una cuenta interna o externa
			if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
				$internas = true;
			}
			else{
				$externas = true;
			}
		}
		
		// tipo de pago deacuerdo a las transacciones
		if($internas && $externas){
			$tipo_pago = ($estado['tipo']=='n') ? 'nxa' : 'vxa';
		}
		else if($internas){
			$tipo_pago = ($estado['tipo']=='n') ? 'nxm' : 'vxm';
		}
		else{
			$tipo_pago = ($estado['tipo']=='n') ? 'nxo' : 'vxo';
		}
		// tipo de pago deacuerdo a las transacciones
		
		$res = $this->TransaccionPendiente->save(
			array(
				'TransaccionPendiente'=>array(
					'usuario_id'=>$usuario['id'],
					'tipo'=> $tipo_pago,
					'estatus'=>($this->data['accion']=='guardar' || $this->data['accion']=='modificar') ? 'g' : 'pa',
					'lote'=>ltrim($this->data['PagoCash']['lote'], "0"),
					'fecha_hora_registro'=>date('Y-m-d H:i:s'),
					'fecha_hora_valor'=>formatea_fecha_toMSSQL($this->data['PagoCash']['fecha_valor']['day'], $this->data['PagoCash']['fecha_valor']['month'], $this->data['PagoCash']['fecha_valor']['year']),
					'monto'=>formatea_monto_toWs($this->data['PagoCash']['monto_total']),
					'aprobaciones_requeridas'=>$nro_aprobaciones,
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif'],
					'cuenta_origen'=>encriptar($this->data['PagoCash']['cuentaDebitar']),
					'descripcion'=>encriptar($this->data['PagoCash']['descripcion']),
				)
			),
			false
		);
		
		if($res){
			$ds->commit($this->TransaccionPendiente);
			
			// Elimina los beneficiarios anteriores
			$this->TransaccionBeneficiario->deleteAll(
				array(
					'transaccion_id' => $this->TransaccionPendiente->id
				),
				false
			);
			
			foreach($this->data['Beneficiarios'] as &$i){
				$i['transaccion_id'] = $this->TransaccionPendiente->id;
			}
			
			$res = $this->TransaccionBeneficiario->saveAll(
				$this->data['Beneficiarios'],
				array('validate'=>false)
			);
			
			if($res){
				// Para el PDF
				$pdf = $this->data;
				$pdf['tipo'] = $estado['tipo'];
				$this->Session->write('cash.pagos.descarga', $pdf);
				
				$exito = true;
			} else {
				$exito = false;
				$this->log($ds->error, LOG_ERROR);
			}
		}
		else{
			$ds->rollback($this->TransaccionPendiente);
			$this->log($ds->error, LOG_ERROR);
		}
		
		$this->Session->delete('cash.pagos.data');
		$this->Session->delete('cash.pagos.operacion');
		
		if($this->data['accion']=='guardar'){
			
			if($exito){
				$descripcion = 'Guardado lote nro.: '.$this->data['PagoCash']['lote'];

				if(
					$tipo_pago[2]=='a' ||
					$tipo_pago[2]=='m'
				){
					$this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0]=='n'?39:40, NULL, NULL, NULL, NULL, $descripcion);
				} elseif($tipo_pago[2]=='o'){
					$this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0]=='n'?100016:100017, NULL, NULL, NULL, NULL, $descripcion);
				}
			}
			
			$this->Session->write('cash.pago.exito',$exito);
			$this->redirect($estado['url_volver_modificar']);
		}
		else{
			
			if($exito){
				$descripcion = 'Modificación lote nro.: '.$this->data['PagoCash']['lote'];

				if($tipo_pago[2]=='a' || $tipo_pago[2]=='m'){
					$this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0]=='n'?39:40, NULL, NULL, NULL, NULL, $descripcion);
				} elseif($tipo_pago[2]=='o') {
					$this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0]=='n'?100016:100017, NULL, NULL, NULL, NULL, $descripcion);
				}
			}
			
			$bancos = $this->Activo->buscarBancos();

			$this->set(compact('estado','usuario','bancos', 'exito'));
			
			$this->layout = 'interna';
			
			switch($estado['tipo']){
				case 'n':
					$this->set('seccion_actual','cash.pagos.nomina');
					if(isset($this->data['PagoCash']['id'])){
						$this->set('titulo','Pagos Nómina - Modificar');
					} else {
						$this->set('titulo','Pagos Nómina - Carga Manual');
					}
					break;
				case 'p':
					$this->set('seccion_actual','cash.pagos.proveedores');
					if(isset($this->data['PagoCash']['id'])){
						$this->set('titulo','Pagos Proveedores - Modificar');
					} else {
						$this->set('titulo','Pagos Proveedores - Carga Manual');
					}
					break;
			}
		}
		
	}
	
	/**
	 * Acción para eliminar pagos de nómina o proveedores 
	 */
	function pago_eliminar(){
		if(!$this->Session->check('cash.pagos.operacion')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		
		// listado
		$pagos = $this->PagoCash->listar($estado['seleccion'],$usuario['id']);
		
		$this->layout = 'interna';		
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos Nómina - Eliminar');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores - Eliminar');
				break;
		}
		
		$this->set(compact('pagos','estado','usuario'));
	}
	
	/**
	 * Acción para indicar el resultado de la eliminación del pago 
	 */
	function pago_eliminar_resultado(){
		if(!$this->Session->check('cash.pagos.operacion')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$exito = false;
		
		// listado
		$pagos = $this->PagoCash->listar($estado['seleccion'],$usuario['id']);
		
		// Elimina los pagos seleccionados
		if( $this->TransaccionPendiente->deleteAll(
			array(
				'id' => $estado['seleccion'],
				'usuario_id'=>$usuario['id'],
			), false)
		){
			// Para el PDF
			$estado['pagos'] = $pagos;
			$this->Session->write('cash.pagos.descarga', $estado);
			
			$exito = true;
		}
		
		$this->layout = 'interna';		

		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos Nómina');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores');
				break;
		}
		
		$this->set(compact('pagos','estado','usuario','exito'));
		
		$this->Session->delete('cash.pagos.operacion');
	}
	
	/**
	 * Acción para confirmar el procesamiento del pago
	 */
	function pago_procesar_confirmar(){
            
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
                		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$this->data = $this->Session->read('cash.pagos.data');				
		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('estado','usuario','beneficiarios','bancos'));
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos Nómina - Procesar');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores - Procesar');
				break;
		}
	}
	
	/**
	 * Acción para indicar el resultado del procesamiento del pago 
	 */
	function pago_procesar_resultado(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$this->data = $this->Session->read('cash.pagos.data');
		                
                $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		// Validar la fecha como última instancia
		
		if(!$this->PagoCash->validarFecha(array('fecha_valor'=>$this->data['PagoCash']['fecha_valor']))) $this->redirect('pago_detalle');
		
		if(
			$usuario['rol_juridico_id'] == 2 
			&&
			(
				$validacion_tdc == null ||
				!isset($validacion_tdc['validado']) ||
				!$validacion_tdc['validado']
			)
		){
			switch($estado['tipo']){
				case 'n':
					$titulo_modulo = 'Pagos Nómina - Procesar';
					$seccion_actual = 'cash.pagos.nomina';
					break;
				case 'p':
					$titulo_modulo = 'Pagos Proveedores - Procesar';
					$seccion_actual = 'cash.pagos.proveedores';
					break;
			}
		
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/cash/pago_procesar_resultado',
					'layout'=>'interna',
					'titulo'=>$titulo_modulo,
					'pasos'=> array(
						'Solicitud de Información',
						'Confirmación',
						'Tarjeta de Coordenadas',
						'Resultado Operación',
					),
					'paso_actual'=>2,
					'seccion_actual'=>$seccion_actual
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}
		
		// Validación anti fraude
		$cuentas = $this->Activo->buscarCuentas();
		
		if(
			!in_array($this->data['PagoCash']['cuentaDebitar'], array_keys($cuentas))
		){
			$this->redirect('/consultas/integral');
		}
		//  Fin validación anti fraude
		
		$exito = false;			// Bandera de no error en bd
		$error_pago = true;		// Bandera de error en as400
		$descripcion_error='';	// Descripción error as400	
		$tipo_pago = '';

		// Comenzar transacción en bd
		
		$ds = $this->TransaccionPendiente->getDataSource();

		$ds->begin($this->TransaccionPendiente);	
		
		if(isset($estado['procesar_detalle'])){
			if(isset($this->data['PagoCash']['id'])){
				$this->TransaccionPendiente->id = $this->data['PagoCash']['id'];
				
				if( $this->TransaccionPendiente->save( array( 
					'TransaccionPendiente'=>array(
						'estatus'=>$usuario['rol_juridico_id']==2 ? 'ep' : 'pa',
					))) 
				){
					$exito = true;
				}
			}
		} else {
			// Buscar la cantidad de aprobaciones requeridas para la transacción
			// Todos los tipos de pago cash tienen la misma cantidad de aprobaciones
			// debido a que en la configuración de usuarios el combo es para los cuatro tipo de pagos
			// buscamos uno por uno y nos quedamos con el que exista, sin importar cual es debido a la
			// premisa anterior
			$transaccion_detalle = false;
			
			foreach(array(39,100016,40,100017) as $i){
				$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					$i
				);
				
				if($transaccion_detalle) break;
			}
			
			$nro_aprobaciones = 3; // Nunca debería quedar en este valor
			
			if( isset($transaccion_detalle['aprobaciones']) ){
				$nro_aprobaciones = $transaccion_detalle['aprobaciones'];
			}
			
			if(isset($this->data['PagoCash']['id'])) $this->TransaccionPendiente->id = $this->data['PagoCash']['id'];			

			$res = $this->TransaccionPendiente->save(
				array(
					'TransaccionPendiente'=>array(
						'usuario_id'=>$usuario['id'],
						'tipo'=> ($estado['tipo']=='n') ? 'nxa' : 'vxa',
						'estatus'=> $usuario['rol_juridico_id']==2 ? 'ep' : 'pa',
						'lote'=>ltrim($this->data['PagoCash']['lote'], "0"),
						'fecha_hora_registro'=>date('Y-m-d H:i:s'),
						'fecha_hora_valor'=>formatea_fecha_toMSSQL($this->data['PagoCash']['fecha_valor']['day'], $this->data['PagoCash']['fecha_valor']['month'], $this->data['PagoCash']['fecha_valor']['year']),
						'monto'=>formatea_monto_toWs($this->data['PagoCash']['monto_total']),
						'aprobaciones_requeridas'=>$nro_aprobaciones,
						'tipo_rif'=>$usuario['tipo_rif'],
						'rif'=>$usuario['rif'],
						'cuenta_origen'=>encriptar($this->data['PagoCash']['cuentaDebitar']),
						'descripcion'=>encriptar($this->data['PagoCash']['descripcion']),
					)
				),
				false
			);

			if($res){

				// Elimina los beneficiarios anteriores
				$res = $this->TransaccionBeneficiario->deleteAll(
					array(
						'transaccion_id' => $this->TransaccionPendiente->id
					),
					false
				);
				
				$internas = false;
				$externas = false;

				foreach($this->data['Beneficiarios'] as &$i){
					$i['transaccion_id'] = $this->TransaccionPendiente->id;

					// Verifica si es una cuenta interna o externa
					if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
						$internas = true;
					}
					else{
						$externas = true;
					}
				}

				// tipo de pago deacuerdo a las transacciones
				if($internas && $externas){
					$tipo_pago = ($estado['tipo']=='n') ? 'nxa' : 'vxa';
				}
				else if($internas){
					$tipo_pago = ($estado['tipo']=='n') ? 'nxm' : 'vxm';
				}
				else{
					$tipo_pago = ($estado['tipo']=='n') ? 'nxo' : 'vxo';
				}
				$this->TransaccionPendiente->saveField('tipo',$tipo_pago);
				// tipo de pago deacuerdo a las transacciones

				if($res){
					$res = $this->TransaccionBeneficiario->saveAll(
						$this->data['Beneficiarios'],
						array('validate'=>false)
					);
					
					if($res){
						$exito = true;
					} else {
						$exito = false;
						$this->log($ds->error, LOG_ERROR);
					}
				} else {
					$this->log($ds->error, LOG_ERROR);
				}
			} else {
				$this->log($ds->error, LOG_ERROR);
			}
		}
		
		// Ejecuta el servicio si es un master unico
		if($exito){
			if($usuario['rol_juridico_id']==2){
				$dataPago = array(
					'lote'=> ltrim($this->data['PagoCash']['lote'], "0"), 
					'tipo'=> $estado['tipo'], 
					'cuentaDebitar'=> $this->data['PagoCash']['cuentaDebitar'], 
					'monto_total'=> $this->data['PagoCash']['monto_total'], 
					'fecha_valor'=> $this->data['PagoCash']['fecha_valor'],
				);

				$bdata = $this->TransaccionBeneficiario->obtenerListaDetalle($this->TransaccionPendiente->id);
				
				$monto_interna = 0;
				$monto_externa = 0;								
				
				/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
				
				// Buscar el monto acumulado diario POR EMPRESA
				$fecha = $this->data['PagoCash']['fecha_valor']['year']. '-' . str_pad($this->data['PagoCash']['fecha_valor']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($this->data['PagoCash']['fecha_valor']['day'], 2, '0', STR_PAD_LEFT);
				
                                // MGGY Se toma en cuenta el horario de franja de compensacion en caso de que la fehca valor sea el mismo dia                                                        
                                $fecha_acumulado = $this->Activo->consultaFechaCompensacion();                                
                                
                                foreach($bdata as &$b){
					// Verifica si es una cuenta interna o externa
					if(substr(desencriptar($b['AfiliacionCash']['numero_cuenta']),0,4)=='0171'){
						$monto_interna += formatea_monto_toWs($b['AfiliacionCash']['monto']);
					}else{
						$monto_externa += formatea_monto_toWs($b['AfiliacionCash']['monto']);
					}
				}
                                
                                $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha);
                                //Si el lote contiene operaciones de otros bancos y la fecha valor es del mismo dia utiliza la fecha segun la franja de compensacion     
                                if(date('Y-m-d')==$fecha){
                                    $fecha_externa = $fecha_acumulado;
                                }else{
                                    $fecha_externa = $fecha;
                                }
                                $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha_externa);
                                
                                if($estado['tipo']=='n'){
                                    
                                    if(is_numeric($usuario['limite_vip'])){
                                        // Tomamos el límite vip
                                        $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                                        $limite_total_interno = $limite_vip['limite_internas_terceros_nomina'];
                                        $limite_total_externo = $limite_vip['limite_externas_nomina'];                                        
                                    }else{
                                        $limite_total_interno = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                                        $limite_total_externo = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
                                    } 
                                                                        
                                    if($limite_total_interno){
                                        $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'];
                                    }
                                    
                                    if($limite_total_externo){
                                        $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_nomina'];
                                    }
                                }else{
                                    
                                    if(is_numeric($usuario['limite_vip'])){
                                        // Tomamos el límite vip
                                        $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                                        $limite_total_interno = $limite_vip['limite_internas_terceros_proveedores'];
                                        $limite_total_externo = $limite_vip['limite_externas_proveedores'];                                        
                                    }else{
                                        $limite_total_interno = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');
                                        $limite_total_externo = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
                                    } 
                                                                        
                                    if($limite_total_interno){
                                        $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'];
                                    }
                                    
                                    if($limite_total_externo){
                                        $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_proveedores'];
                                    }
                                }				
				/** PARA EL MONTO LIMITE EN LA TRANSACCION **/


				// Verificar los límites acumulados de la transacción
				// 
				if(
					isset($monto_limite_interno) &&
					isset($monto_limite_externo) &&
					$monto_interna > $monto_limite_interno &&
					$monto_externa > $monto_limite_externo
				){
					$descripcion_error = _m('monto.excedido.ambos','de la transacción');
				} else if(
					isset($monto_limite_interno) &&
					$monto_interna > $monto_limite_interno
				){
					$descripcion_error = _m('monto.excedido.activo','de la transacción');
				} else if(
					isset($monto_limite_externo) &&
					$monto_externa > $monto_limite_externo
				){
					$descripcion_error = _m('monto.excedido.otros','de la transacción');
				} else{

					if($this->Activo->ejecutarPagoCash($dataPago, $bdata)){
						// el pago se ejecutó bien
						$error_pago = false;
						
						if(
							$dataPago['fecha_valor']['day']==date('d') &&
							$dataPago['fecha_valor']['month']==date('m') &&
							$dataPago['fecha_valor']['year']==date('Y')
						){
							// si el pago es hoy, revisar pago por pago para sumar sólo los montos aplicados en el acumulado
							
							$tx = $this->TransaccionPendiente->findById($this->TransaccionPendiente->id);
							
							$this->Activo->seguimientoGestion($tx);
							
							foreach($tx['Beneficiarios'] as $i){
								if(isset($i['rechazado'])){
									// Restar montos rechazados del acumulado
									
									if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
										$monto_interna -= floatval($i['monto']);
									} else {
										$monto_externa -= floatval($i['monto']);
									}
									
									// Marcar en la base de datos el reverso, para no hacerlo dos veces
									
									$this->TransaccionBeneficiario->id = $i['id'];
									
									$this->TransaccionBeneficiario->saveField('reversada',1);
								}
							}
						}
						
                                                if($estado['tipo']=='n'){
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itn', formatea_monto_fromWs($monto_interna), $fecha);
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'en', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                                }else{
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itp', formatea_monto_fromWs($monto_interna), $fecha);
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'ep', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                                }						
										
					} else {
						switch($this->Activo->error){
							case 1616:
								$descripcion_error = _m('cuenta.sobregirada');
								break;
							case 862:
								$descripcion_error =  _m('cuenta.sobregirada');
								break;
                                                        case 215:
								$descripcion_error =  _m('lote.repetido');
								break;    
							default:
								$descripcion_error = _m('operacion.fallida');								
						}
					}
				}
			} else {
				$error_pago = false;
			}
		}
		
		if($exito && !$error_pago){
			// Hacer commit en bd
			$ds->commit($this->TransaccionPendiente);
			
			// Registrar en bitácora
			switch ($tipo_pago){
				case 'nxm':
				case 'vxm':
					$transaccion = ($estado['tipo']=='n' ? 39 : 40);
					break;

				case 'nxo':
				case 'vxo':
					$transaccion = ($estado['tipo']=='n' ? 100016 : 100017);
					break;

				default:
					$transaccion = ($estado['tipo']=='n' ? 100018 : 100019);
					break;
			}
			$descripcion = 'A procesar lote nro.: ' . $this->data['PagoCash']['lote'];
			$this->Bitacora->registraBitacora($usuario['id'], $transaccion , NULL, NULL, NULL, NULL, $descripcion);
		} else {
			// Algo falló, hacer rollback en bd
			$ds->rollback($this->TransaccionPendiente);
		}
		
		// Para el PDF
		$pdf = $this->data;
		$pdf['tipo'] = $estado['tipo'];
		if(isset($estado['procesar_detalle'])) $pdf['procesar_detalle'] = $estado['procesar_detalle'];
		$this->Session->write('cash.pagos.descarga', $pdf);

		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('estado','usuario','beneficiarios', 'bancos', 'exito', 'error_pago','descripcion_error'));
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos Nómina - Procesar');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores - Procesar');
				break;
		}
		
		$this->Session->delete('cash.pagos.data');
		$this->Session->delete('cash.pagos.operacion');
	}
	
	/**
	 * Acción para hacer carga masiva de pagos de nómina o proveedores
	 * @param string $tipo Indica el tipo de beneficiario. Nómina: 'n',
	 * Proveedores: 'p'
	 */
	function pago_masivo($tipo=null){
		if( empty($tipo) || !in_array($tipo, array('n','p')) ) $this->redirect('/consultas/integral');
		
		// Valida el permiso de la transaccion
		if( 
			(!validarTransaccion(array(39,100016)) && $tipo=='n') ||
			(!validarTransaccion(array(40,100017)) && $tipo=='p') ) {
			$this->redirect('/consultas/integral');
		}
		
		// setlocale(LC_ALL,'es_VE.utf8'); PARA LINUX
                setlocale(LC_ALL,'es_VE.UTF-8'); // PARA LINUX
		
		set_time_limit(0);
		
		$usuario = $this->Session->read('usuarios.datos');
		                           
		if(!empty($this->data['PagoCash'])){
			
			$this->PagoCash->set($this->data);
			
			if ($this->PagoCash->validates(array('fieldList' => array(
				'cuentaDebitar',
				'archivo',
			)))){
				$error_data = array();
                                $referencias = array();
				$header_leido = false;
				$header_encontrado = false;
				$cantidad_pagos = 0;
				$this->data['Beneficiarios'] = array();
				$monto_acumulado = 0;
				$monto_acumulado_it = 0;
				$monto_acumulado_e = 0;
				$alerta = false;
				
				$internas = false;
				$externas = false;
				
				$archivo = @file($this->data['PagoCash']['archivo']['tmp_name']);
				$lineas = count($archivo);

				if(count($error_data)==0)
				for($i=0; $i < $lineas; $i++){
					
					// Actualiza el tiempo de la session en la base de datos
					$this->UsuarioCanal->sessTime($usuario['id'], _p('tiempo_session'));
					
					$temp = @explode(';', trim($archivo[$i]));
					$fecha_valor = '';

					if($temp[0]=='H' && !$header_leido){
						$header_encontrado = true;
						if(count($temp)!=6){
							$error_data[] = array(
								'registro'=>$i,
								'identificacion'=>'',
								'error'=>'Archivo inválido'
								
							);
							break;
						}
						
						// Data del pago
						$this->data['PagoCash']['lote'] = ltrim($temp[4], "0");
						$this->data['PagoCash']['descripcion'] = $temp[3];
						$this->data['PagoCash']['monto_total'] = $temp[2];
						$this->data['PagoCash']['cantidad_registros'] = $temp[1];
					
						// Fecha valor
						if( strlen(trim($temp[5]))==8 ){
							$this->data['PagoCash']['fecha_valor']['day'] = substr($temp[5],0,2);
							$this->data['PagoCash']['fecha_valor']['month'] = substr($temp[5],2,2);
							$this->data['PagoCash']['fecha_valor']['year'] = substr($temp[5],4);
						} else {
							$error_data[] = array(
								'registro'=>$i+1,
								'identificacion'=>'',
								'error'=>'Estructura del registro incompleto - '.trim($archivo[$i])
								
							);
							
							break;
						}						
						
						// Limite del usuario
						if($usuario['rol_juridico_id']>2){
							$transaccion_detalle_it = $this->TransaccionJuridicoDetalle->obtener(
								$usuario['id'],
								$tipo=='n' ? 39 : 40
							);
							
							$transaccion_detalle_e = $this->TransaccionJuridicoDetalle->obtener(
								$usuario['id'],
								$tipo=='n' ? 100016 : 100017
							);
							
							if($transaccion_detalle_it) {
								$this->data['PagoCash']['monto_limite_usuario_it'] = $transaccion_detalle_it['limite'];
							} else {
								$this->data['PagoCash']['monto_limite_usuario_it'] = '0,00'; // Denegar permiso a través de los limites
							}
							
							if($transaccion_detalle_e) {
								$this->data['PagoCash']['monto_limite_usuario_e'] = $transaccion_detalle_e['limite'];
							} else {
								$this->data['PagoCash']['monto_limite_usuario_e'] = '0,00'; // Denegar permiso a través de los limites
							}
							
							$this->data['PagoCash']['cantidad_aprobaciones'] = $transaccion_detalle_it['aprobaciones'];
						} else {
                                                    if($tipo=='n'){
                                                        if(is_numeric($usuario['limite_vip'])){
                                                            // Tomamos el límite vip
                                                            $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $limite_vip['limite_internas_terceros_nomina'];
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $limite_vip['limite_externas_nomina'];
                                                        }else{
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
                                                        } 
                                                        
                                                    }else{
                                                        if(is_numeric($usuario['limite_vip'])){
                                                            // Tomamos el límite vip
                                                            $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $limite_vip['limite_internas_terceros_proveedores'];
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $limite_vip['limite_externas_proveedores'];
                                                        }else{
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
                                                        }                                                        
                                                    }
							
							$this->data['PagoCash']['cantidad_aprobaciones'] = 0;
						}
						
						if($this->data['PagoCash']['monto_limite_usuario_it']){
							$this->data['PagoCash']['monto_limite_usuario_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_it']);
						} else {
							$this->data['PagoCash']['monto_limite_usuario_it'] = ''; // Ilimitado
						}
						
						if($this->data['PagoCash']['monto_limite_usuario_e']){
							$this->data['PagoCash']['monto_limite_usuario_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_e']);
						} else {
							$this->data['PagoCash']['monto_limite_usuario_e'] = ''; // Ilimitado
						}	
						
						
						// Verifica los datos del lote
						$this->PagoCash->set($this->data);
						
						// no validar el total ahora, ya que necesitamos calcular los montos
						// mismo banco y otro banco por separado
						
						$this->PagoCash->validate['monto_total']=array(
							'numerico'=>array(
								'rule'=>'montoEstricto'
							),
							'requerido'=>array(
								'rule'=>'notEmpty'
							),
						);

						if (!$this->PagoCash->validates(array('fieldList' => array(
							'descripcion',
							'lote',
							'monto_total',
							'cantidad_registros',
							'fecha_valor',
						)))){
							
							if(isset($this->PagoCash->validationErrors['descripcion'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Descripción inválida- '.trim($archivo[$i])
									
								);
							}
							
							if(isset($this->PagoCash->validationErrors['lote'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Lote inválido- ' . trim($archivo[$i])
									
								);
							}
							
							if(isset($this->PagoCash->validationErrors['cantidad_registros'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Cantidad de registros inválida- ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->PagoCash->validationErrors['fecha_valor'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Fecha inválida - ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->PagoCash->validationErrors['monto_total'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Monto inválido - ' . trim($archivo[$i])
								);
							}
							
							break;
						}
						
						// verifica el numero de lote
						if(!$this->TransaccionPendiente->checkLote($this->data['PagoCash']['lote'])){
							$error_data[] = array(
								'registro'=>$i+1,
								'identificacion'=>'',
								'error'=>'Número de lote duplicado - ' . trim($archivo[$i])
							);
							
							break;
						}
						
						$header_leido = true;
					} else if($temp[0]=='P') {
						if(count($temp)!=6){
							$error_data[] = array(
								'registro'=>$i+1,
								'identificacion'=>'',
								'error'=>'Estructura del registro incompleto - ' . trim($archivo[$i])
							);
							
							continue;
						}
						
						$this->data['AfiliacionCash']['tipo_rif'] = $usuario['tipo_rif'];
						$this->data['AfiliacionCash']['rif'] = desencriptar($usuario['rif']);
						$this->data['AfiliacionCash']['tipo_cedula'] = substr($temp[1], 0, 1);
						$this->data['AfiliacionCash']['cedula'] = in_array($this->data['AfiliacionCash']['tipo_cedula'],array('J','G')) ? substr($temp[1], 1) : ltrim(substr($temp[1], 1),'0');
						$this->data['AfiliacionCash']['monto'] = $temp[2];
						$this->data['AfiliacionCash']['concepto'] = $temp[3];
						$this->data['AfiliacionCash']['referencia'] = $temp[4];
						$this->data['AfiliacionCash']['tipo'] = $tipo;
						
                                                $referencias[$i] = $temp[4];
                                                
						$identificacion = $this->data['AfiliacionCash']['tipo_cedula'].$this->data['AfiliacionCash']['cedula'];
						
						// Verifica los datos del lote
						$this->AfiliacionCash->set($this->data);
						
						$this->AfiliacionCash->validate['cedula'] = array(
							'longitud'=>array(
								'rule'=>'validaLongitud'
							),
							'requerido'=>array(
								'rule'=>'notEmpty'
							),
							'rif'=>array(
								'rule'=>'validaRif'
							)
						);
						
						// Buscar en la base de datos
						
						$beneficiario = $this->AfiliacionCash->find(
							'first',
							array(
								'conditions'=>array(
									'AfiliacionCash.tipo_rif' => $usuario['tipo_rif'],
									'AfiliacionCash.rif' => $usuario['rif'],
									'AfiliacionCash.tipo_cedula' => $this->data['AfiliacionCash']['tipo_cedula'],
									'AfiliacionCash.cedula' => encriptar($this->data['AfiliacionCash']['cedula']),
									'AfiliacionCash.tipo' => $tipo									
								)
							)
						);						
						
						// Cambiar la validación del monto para que falle si el monto no tiene decimales
						
						$this->AfiliacionCash->validate['monto'] = array(
							'numerico'=>array(
								'rule'=>'montoEstricto'
							),
							'requerido'=>array(
								'rule'=>'notEmpty'
							)
						);
						
						if (!$this->AfiliacionCash->validates(array('fieldList' => array(
							'tipo_cedula',
							'cedula',
							'monto',
							'concepto',
							'referencia',
						)))){
							if(isset($this->AfiliacionCash->validationErrors['tipo_cedula'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'RIF o Cédula inválida - ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->AfiliacionCash->validationErrors['cedula'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'RIF o Cédula inválida - ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->AfiliacionCash->validationErrors['monto'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'Monto de transacción inválida- ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->AfiliacionCash->validationErrors['concepto'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'Instrucción Inválida Concepto del Pago - ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->AfiliacionCash->validationErrors['referencia'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'Instrucción Inválida o Referencia Duplicada - ' . trim($archivo[$i])
								);
							}
							
							// A pesar del error sumar los totales si es posible
							
							$cantidad_pagos++;
							
							if(!isset($this->AfiliacionCash->validationErrors['monto'])){
								// Sumar el monto al acumulado sólo si está bien
								$monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);
								$monto_acumulado += $monto_tx;
								
								if($beneficiario){
									if(substr(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']),0,4)=='0171'){
										$monto_acumulado_it += $monto_tx;
									} else {
										$monto_acumulado_e += $monto_tx;
									}
								}
							}

							continue;
						}
						
						$cantidad_pagos++;
						$monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);
						$monto_acumulado += $monto_tx;
						
						if($beneficiario){
							
							
							// Validar el estatus del nro de cuenta
							// Siempre y cuando sea cuenta banco activo
							if(preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/',desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']))){
								$monto_acumulado_it += $monto_tx;
								
								$productoValidar = substr(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']),-10);
								
								$valorCuenta= array(
									'NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 
									'NROPARAM' => 5,
									'NACIONALIDAD'=>trim($beneficiario['AfiliacionCash']['tipo_cedula']),
									'NROIDENTIFICACION'=>str_pad(trim(desencriptar($beneficiario['AfiliacionCash']['cedula'])),12,'0',STR_PAD_LEFT)
								);
								
								$datos = $this->Activo->validarProducto($valorCuenta);
								
								$error = (isset($datos['NIBPF3010Return']['error']['ERNU01'])) ? intval($datos['NIBPF3010Return']['error']['ERNU01']) : 0;
								
								$internas = true;
								
							}else{
								$monto_acumulado_e += $monto_tx;
								
								$error = 0;
								$datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] = 'A';
								$externas = true;
							}
							
							if($error!=0 || !in_array($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'],array('A','T'))){
								if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
									$mensaje_error = _m('cuenta.controlada', 'con la cuenta del beneficiario');
								} else {
									$mensaje_error = _m('cuenta.inactiva', 'la cuenta del beneficiario');
								}
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>$mensaje_error .' - '.desencriptar($beneficiario['AfiliacionCash']['numero_cuenta'])
								);								
							} else {
								$beneficiario['AfiliacionCash']['monto'] = $this->data['AfiliacionCash']['monto'];
								$beneficiario['AfiliacionCash']['concepto'] = encriptar($this->data['AfiliacionCash']['concepto']);
								$beneficiario['AfiliacionCash']['referencia'] = encriptar($this->data['AfiliacionCash']['referencia']);
							}
						} else {
							$error_data[] = array(
								'registro'=>$i+1,
								'identificacion'=>$identificacion,
								'error'=>'Empresa/Cliente no esta afiliado/no fue encontrado - ' . trim($archivo[$i])
							);
						}
						
						// Verifica si es para proveedor
						if($tipo=='p'){
							$j=$i+1;
							$monto_detalle = 0;
							$tiene_detalle = false;
							
							// realiza una busqueda en las siguientes posiciones para verificar si tiene detalle
							do{
								$temp2 = @explode(';', trim($archivo[$j]));
								if($temp2[0]=='D'){
									$tiene_detalle = true;
									
									// Verificacion de la data
									if(count($temp2)!=4){
										$error_data[] = array(
											'registro'=>$j+1,
											'identificacion'=>$identificacion,
											'error'=>'Estructura del registro incompleto - ' . trim($archivo[$j])
										);
									} else {
										$this->data['DetallePagoProveedor']['tipo_detalle'] = $temp2[1];
										$this->data['DetallePagoProveedor']['referencia'] = $temp2[2];
										$this->data['DetallePagoProveedor']['monto'] = $temp2[3];

										$this->DetallePagoProveedor->set($this->data);
										if (!$this->DetallePagoProveedor->validates(array('fieldList' => array(
											'monto',
											'tipo_detalle',
											'referencia',
										)))){

											if(isset($this->DetallePagoProveedor->validationErrors['monto'])){
												$error_data[] = array(
													'registro'=>$j+1,
													'identificacion'=>$identificacion,
													'error'=>'Monto de transacción invalida- ' . trim($archivo[$j])
												);
											}

											if(isset($this->DetallePagoProveedor->validationErrors['tipo_detalle'])){
												$error_data[] = array(
													'registro'=>$j+1,
													'identificacion'=>$identificacion,
													'error'=>'Instrucción Invalida tipo detalle - ' . trim($archivo[$j])
												);
											}

											if(isset($this->DetallePagoProveedor->validationErrors['referencia'])){
												$error_data[] = array(
													'registro'=>$j+1,
													'identificacion'=>$identificacion,
													'error'=>'Instrucción Invalida o Duplicada referencia - ' . trim($archivo[$j])
												);
											}
										} else {
											// Sumatoria de los montos del detalle
											$monto_detalle += formatea_monto_toWs($this->data['DetallePagoProveedor']['monto']);
											
											// formateo de la data
											$detalle['DetallePagoProveedor'] = $this->data['DetallePagoProveedor'];
											$detalle['DetallePagoProveedor']['monto'] = formatea_monto_toWs($this->data['DetallePagoProveedor']['monto']);
											$detalle['DetallePagoProveedor']['referencia'] = encriptar($this->data['DetallePagoProveedor']['referencia']);
											
											// Asignacion del detalle al proveedor
											$beneficiario['AfiliacionCash']['detalle'][] = $detalle;
										}
									}
									$j++;
								}
							}while($temp2[0]=='D');
							
							// Verifica monto acumulado del detalle
							if( $tiene_detalle && formatea_monto_toWs($this->data['AfiliacionCash']['monto'])!=formatea_monto_toWs($monto_detalle)){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Monto detalle no cuadra con sumatoria'
								);
							}
							
						}// FIN de verifica si es para proveedor
						
							
						$this->data['Beneficiarios'][]=$beneficiario;
							
						unset($this->data['AfiliacionCash']);
					}                                                                                
					
				}
				
                                if(count($referencias) > count(array_unique($referencias))){
                                    $error_data[] = array(
                                        'registro'=>1,
                                        'identificacion'=>'',
                                        'error'=>'Existen Referencias Duplicadas en el archivo'
                                    );
                                }
                                        
				// Verifica monto acumulado
				if($header_leido){
					if(formatea_monto_toWs($this->data['PagoCash']['monto_total']) != formatea_monto_toWs($monto_acumulado)){
						$error_data[] = array(
							'registro'=>1,
							'identificacion'=>'',
							'error'=>'Monto total no cuadra con sumatoria'
						);
					}
				}

				// Ahora sí validar monto total con respecto a limites
				
				$this->PagoCash->data['PagoCash']['monto_total_it'] = $monto_acumulado_it;
				$this->PagoCash->data['PagoCash']['monto_total_e'] = $monto_acumulado_e;
				
				$this->PagoCash->validate['monto_total']=array(
					'limiteMontoTotal'=>array(
						'rule'=>'limiteMontoTotal'
					)
				);
				
				$this->PagoCash->validationErrors=array();
				
				if (!$this->PagoCash->validates(array('fieldList' => array(
					'monto_total'
				)))){
					if($this->PagoCash->validationErrors['monto_total']=='montoLimiteAmbos'){
						$mensaje_error = _m('monto.limite.usuario','de la transacción en activo y otros bancos');
					} elseif($this->PagoCash->validationErrors['monto_total']=='montoLimiteIt'){
						$mensaje_error = _m('monto.limite.usuario','de la transacción en activo');
					} elseif($this->PagoCash->validationErrors['monto_total']=='montoLimiteE'){
						$mensaje_error = _m('monto.limite.usuario','de la transacción a otros bancos');
					} elseif($this->PagoCash->validationErrors['monto_total']=='nopermitidaE'){
						if($tipo=='n'){
							$mensaje_error = _m('op.nopermitida','de Pagos de Nómina Otros Bancos');
						} else {
							$mensaje_error = _m('op.nopermitida','de Pagos de Proveedores Otros Bancos');
						}
					} elseif($this->PagoCash->validationErrors['monto_total']=='nopermitidaIt'){
						if($tipo=='n'){
							$mensaje_error = _m('op.nopermitida','de Pagos de Nómina en Activo');
						} else {
							$mensaje_error = _m('op.nopermitida','de Pagos de Proveedores en Activo');
						}
					}
					
					if(isset($mensaje_error)){
						$error_data[] = array(
							'registro'=>1,
							'identificacion'=>'',
							'error'=>$mensaje_error
						);
					}					
				}
				
				// verifica cantidad de pagos
				if($header_leido){
					if(!$cantidad_pagos || ($cantidad_pagos != $this->data['PagoCash']['cantidad_registros']) ){
						$error_data[] = array(
							'registro'=>1,
							'identificacion'=>'',
							'error'=>'Total transacciones no cuadra con sumatoria'
						);
					}
				}
				
				// Si es un pago proveedores verifica el monto total con el del ultimo lote
				if($header_leido){
					$result = $this->TransaccionPendiente->find(
						'first',
						array(
							'fields'=>array('monto'),
							'conditions'=>array(
								'TransaccionPendiente.tipo_rif'=>$usuario['tipo_rif'],
								'TransaccionPendiente.rif'=>$usuario['rif'],
								'TransaccionPendiente.tipo'=>$tipo=='p'?array('vxa','vxm','vxo'):array('nxa','nxm','nxo'),
							),
							'order'=>array('TransaccionPendiente.id DESC'),
							'limit' => 1,
						)
					);
					
					if($result && isset($this->data['PagoCash']['monto_total']) && $result['TransaccionPendiente']['monto']==formatea_monto_toWs($this->data['PagoCash']['monto_total'])){
						$alerta = true;
					}
				}
				
				//Si no se envía un archivo que contenga la cabecera, es un archivo inválido
				if(!$header_encontrado){
					$error_data[] = array(
						'registro'=>1,
						'identificacion'=>'',
						'error'=>'Archivo inválido'
					);
				}
				
				// verifica si hubo errores
				if(count($error_data)){
					
					// Ordenar arreglo
					usort($error_data,'ordernar_errores_por_linea');
		
					$estado['tipo'] = $tipo;
					$estado['errores'] = $error_data;
					$estado['archivo'] = $this->data['PagoCash']['archivo'];
					$this->Session->write('cash.pagos.data', $estado);
					$this->redirect('pago_masivo_error');
				} else {
					//Formateo de monto para la presentación	
					if(isset($this->data['PagoCash']['monto_total'])) $this->data['PagoCash']['monto_total'] = formatea_monto_fromWs( formatea_monto_toWs($this->data['PagoCash']['monto_total']));
					
					// tipo de pago deacuerdo a las transacciones
					if($internas && $externas){
						$this->data['tipo_pago'] = ($tipo=='n') ? 'nxa' : 'vxa';
					}
					else if($internas){
						$this->data['tipo_pago'] = ($tipo=='n') ? 'nxm' : 'vxm';
					}
					else{
						$this->data['tipo_pago'] = ($tipo=='n') ? 'nxo' : 'vxo';
					}
					
					$this->data['tipo'] = $tipo;
					$this->Session->write('cash.pagos.data', $this->data);
					
					if($alerta){
						$this->redirect('pago_masivo_alerta');
					}
					else{
						$this->redirect('pago_masivo_confirmar');
					}
				}
			}
		}
		
		$cuentas = $this->Activo->buscarCuentasSaldos();
		
		$this->set(compact('tipo','cuentas'));
		
		$this->Session->delete('cash.pagos.data');
		
		$this->layout = 'interna';
		
		switch($tipo){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina.masiva');
				$this->set('titulo','Pagos Nómina - Carga Masiva');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores.masiva');
				$this->set('titulo','Pagos Proveedores - Carga Masiva');
				break;
		}
	}
	
	/**
	 * Acción para mostrar alerta de pago duplicado en caso de que el monto
	 * sea igual al monto del último pago de proveedores
	 */
	function pago_masivo_alerta(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
				
		$this->layout = 'interna';
		
		$this->set('seccion_actual','cash.pagos.proveedores.masiva');
		
		$this->set('titulo','Pagos Proveedores - Carga Masiva');
	}
	
	/**
	 * Acción para confirmarr el pago masivo 
	 */
	function pago_masivo_confirmar(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.data');
		
		$usuario = $this->Session->read('usuarios.datos');		                
                
		$this->set(compact('estado','usuario'));
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina.masiva');
				$this->set('titulo','Pagos Nómina - Carga Masiva');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores.masiva');
				$this->set('titulo','Pagos Proveedores - Carga Masiva');
				break;
		}
	}
	
	/**
	 * Acción para el resultado del pago masivo
	 */
	function pago_masivo_resultado(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		set_time_limit(0);
		
		$estado = $this->Session->read('cash.pagos.data');
		
		$usuario = $this->Session->read('usuarios.datos');
		                
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');		                                                                
                
		if(
			$usuario['rol_juridico_id'] == 2 
			&&
			(
				$validacion_tdc == null ||
				!isset($validacion_tdc['validado']) ||
				!$validacion_tdc['validado']
			)
		){
			switch($estado['tipo']){
				case 'n':
					$titulo_modulo = 'Pagos Nómina - Carga Masiva';
					$seccion_actual = 'cash.pagos.nomina.masiva';
					break;
				case 'p':
					$titulo_modulo = 'Pagos Proveedores - Carga Masiva';
					$seccion_actual = 'cash.pagos.proveedores.masiva';
					break;
			}
		
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/cash/pago_masivo_resultado',
					'layout'=>'interna',
					'titulo'=>$titulo_modulo,
					'pasos'=> array(
						'Selección Archivo',
						'Confirmación',
						'Tarjeta de Coordenadas',
						'Resultado Operación',
					),
					'paso_actual'=>2,
					'seccion_actual'=>$seccion_actual
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}
		
		// Validación anti fraude
		$cuentas = $this->Activo->buscarCuentas();
		
		if(
			!in_array($estado['PagoCash']['cuentaDebitar'], array_keys($cuentas))
		){
			$this->redirect('/consultas/integral');
		}
		//  Fin validación anti fraude
		
		$exito = false;
			
		$descripcion_error = _m('operacion.fallida');
		
		// Guarda la transaccion en la BD
		$res = $this->TransaccionPendiente->save(
			array(
				'TransaccionPendiente'=>array(
					'usuario_id'=>$usuario['id'],
					'tipo'=> $estado['tipo_pago'],
					'estatus'=> $usuario['rol_juridico_id']==2 ? 'ep' : 'pa',
					'lote'=>ltrim($estado['PagoCash']['lote'], "0"),
					'fecha_hora_registro'=>date('Y-m-d H:i:s'),
					'fecha_hora_valor'=>formatea_fecha_toMSSQL($estado['PagoCash']['fecha_valor']['day'], $estado['PagoCash']['fecha_valor']['month'], $estado['PagoCash']['fecha_valor']['year']),
					'monto'=>formatea_monto_toWs($estado['PagoCash']['monto_total']),
					'aprobaciones_requeridas'=>$estado['PagoCash']['cantidad_aprobaciones'],
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif'],
					'cuenta_origen'=>encriptar($estado['PagoCash']['cuentaDebitar']),
					'descripcion'=>encriptar($estado['PagoCash']['descripcion']),
					'tipo_carga'=>'a',
				)
			),
			false
		);
		
		if($res){
			
			$exito = true;
			
			// Recorre los beneficiarios y los almacena en la BD
			foreach ($estado['Beneficiarios'] as $x){
				$b = $x['AfiliacionCash'];
				$b['monto'] = formatea_monto_toWs($b['monto']);
				$b['transaccion_id'] = $this->TransaccionPendiente->id;
				
				unset($b['id']);

				$this->TransaccionBeneficiario->save( array('TransaccionBeneficiario'=>$b),false);
				
				// Si posee detalle es un pago de proveedores
				// almacena en BD el detalle del pago
				if(isset($b['detalle'])){
					foreach ($b['detalle'] as $d){
						$d['DetallePagoProveedor']['beneficiario_id'] = $this->TransaccionBeneficiario->id;
						$this->DetallePagoProveedor->save($d,false);
						unset($this->DetallePagoProveedor->id);
					}
				}
				
				unset($this->TransaccionBeneficiario->id);
			}

			// Verifica si es un usuario master unico para procesar el pago
			if($usuario['rol_juridico_id']==2){
				
				$monto_interna = 0;
				$monto_externa = 0;												
				
				/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

				// Buscar el monto acumulado diario POR EMPRESA
				$fecha = $estado['PagoCash']['fecha_valor']['year']. '-' . str_pad($estado['PagoCash']['fecha_valor']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($estado['PagoCash']['fecha_valor']['day'], 2, '0', STR_PAD_LEFT);
                                
                                // MGGY Se toma en cuenta el horario de franja de compensacion en caso de que la fehca valor sea el mismo dia
                                $fecha_acumulado = $this->Activo->consultaFechaCompensacion();                                
                                
                                switch ($estado['tipo_pago']){
					case 'nxm':
					case 'vxm':
						$monto_interna = formatea_monto_toWs($estado['PagoCash']['monto_total']);
						$acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha);
                                                break;
					
					case 'nxo':
					case 'vxo':
						$monto_externa = formatea_monto_toWs($estado['PagoCash']['monto_total']);                                                                                                                                              
                                                
                                                //Si el lote contiene operaciones de otros bancos y la fecha valor es del mismo dia utiliza la fecha segun la franja de compensacion     
                                                if(date('Y-m-d')==$fecha){
                                                    $fecha_externa = $fecha_acumulado;                                                    
                                                }else{
                                                    $fecha_externa = $fecha;
                                                }
                                                $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha_externa);
						break;
					
					default:
						// recorre los beneficiarios
						foreach ($estado['Beneficiarios'] as $x){
							// Verifica si es una cuenta interna o externa
							if(substr(desencriptar($x['AfiliacionCash']['numero_cuenta']),0,4)=='0171'){
								$monto_interna += formatea_monto_toWs($x['AfiliacionCash']['monto']);
							}
							else{
								$monto_externa += formatea_monto_toWs($x['AfiliacionCash']['monto']);
							}
						}
                                                $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha);
                                                //Si el lote contiene operaciones de otros bancos y la fecha valor es del mismo dia utiliza la fecha segun la franja de compensacion     
                                                if(date('Y-m-d')==$fecha){
                                                    $fecha_externa = $fecha_acumulado;                                                    
                                                }else{
                                                    $fecha_externa = $fecha;
                                                }
                                                $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha_externa);
						break;
				}                                                                
                                
                                if($estado['tipo']=='n'){
                                    
                                    if(is_numeric($usuario['limite_vip'])){
                                        // Tomamos el límite vip
                                        $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                                        $limite_total_interno = $limite_vip['limite_internas_terceros_nomina'];
                                        $limite_total_externo = $limite_vip['limite_externas_nomina'];                                        
                                    }else{
                                        $limite_total_interno = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                                        $limite_total_externo = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
                                    }                                                                                                                                                
                                     
                                    if($limite_total_interno){
                                        $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'];
                                    }
                                    
                                    if($limite_total_externo){
                                        $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_nomina'];
                                    }
                                }else{
                                    
                                    if(is_numeric($usuario['limite_vip'])){
                                        // Tomamos el límite vip
                                        $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                                        $limite_total_interno = $limite_vip['limite_internas_terceros_proveedores'];
                                        $limite_total_externo = $limite_vip['limite_externas_proveedores'];                                        
                                    }else{
                                        $limite_total_interno = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');
                                        $limite_total_externo = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
                                    } 
                                                                        
                                    if($limite_total_interno){
                                        $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'];
                                    }
                                    
                                    if($limite_total_externo){
                                        $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_proveedores'];
                                    }
                                }                                                                                                                                				
				/** PARA EL MONTO LIMITE EN LA TRANSACCION **/                                
                                
				// Verificar los límites acumulados de la transacción
				if(
					isset($monto_limite_interno) &&
					isset($monto_limite_externo) &&
					$monto_interna > $monto_limite_interno &&
					$monto_externa > $monto_limite_externo
				){
					$descripcion_error = _m('monto.excedido.ambos','de la transacción');
					$exito = false;
				}
				else if(
					isset($monto_limite_interno) &&
					$monto_interna > $monto_limite_interno
				){
					$descripcion_error = _m('monto.excedido.activo','de la transacción');
					$exito = false;
				}
				else if(
					isset($monto_limite_externo) &&
					$monto_externa > $monto_limite_externo
				){
					$descripcion_error = _m('monto.excedido.otros','de la transacción');
					$exito = false;
				}                                
				else{
					$dataPago = array(
						'lote'=> ltrim($estado['PagoCash']['lote'], "0"), 
						'tipo'=> $estado['tipo'], 
						'cuentaDebitar'=> $estado['PagoCash']['cuentaDebitar'], 
						'monto_total'=> $estado['PagoCash']['monto_total'], 
						'fecha_valor'=> $estado['PagoCash']['fecha_valor'],
					);

					if( !$this->Activo->ejecutarPagoCash($dataPago, $estado['Beneficiarios']) ){
						$exito = false;

						switch($this->Activo->error){
							case 1616:
								$descripcion_error = _m('cuenta.sobregirada');
								break;
							case 862:
								$descripcion_error =  _m('cuenta.sobregirada');
								break;
                                                        case 215:
								$descripcion_error =  _m('lote.repetido');
								break;    
							default:
								$descripcion_error = _m('operacion.fallida');		
						}
					}
					else{
						// el pago se ejecutó bien
						if(
							$dataPago['fecha_valor']['day']==date('d') &&
							$dataPago['fecha_valor']['month']==date('m') &&
							$dataPago['fecha_valor']['year']==date('Y')
						){
							// si el pago es hoy, revisar pago por pago para sumar sólo los montos aplicados en el acumulado

							$tx = $this->TransaccionPendiente->findById($this->TransaccionPendiente->id);

							$this->Activo->seguimientoGestion($tx);

							foreach($tx['Beneficiarios'] as $i){
								if(isset($i['rechazado'])){
									// Restar montos rechazados del acumulado
									if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
										$monto_interna -= floatval($i['monto']);
									} else {
										$monto_externa -= floatval($i['monto']);
									}

									// Marcar en la base de datos el reverso, para no hacerlo dos veces
									$this->TransaccionBeneficiario->id = $i['id'];
									$this->TransaccionBeneficiario->saveField('reversada',1);
								}
							}
						}
						
						// Guarda acumulado
                                                if($estado['tipo']=='n'){
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itn', formatea_monto_fromWs($monto_interna), $fecha);
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'en', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                                }else{
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itp', formatea_monto_fromWs($monto_interna), $fecha);
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'ep', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                                }
						
					}
					
				}
				
				
				if(!$exito){
					// Elimina la transaccion
					$this->TransaccionPendiente->deleteAll(array('id'=>$this->TransaccionPendiente->id), false);
					
					$this->log('Pago masivo - '.$descripcion_error.' - ID: '.$usuario['id'], LOG_ERROR);
				}
			}
		}
		else{
			$this->log($this->TransaccionPendiente->getDataSource()->error, LOG_ERROR);
		}
		
		// para la descarga del pdf
		if($exito){
			unset($estado['Beneficiarios']);
			$this->Session->write('cash.pagos.descarga',$estado);
			
			switch ($estado['tipo_pago']){
				case 'nxm':
				case 'vxm':
					$transaccion = ($estado['tipo']=='n' ? 39 : 40);
					break;

				case 'nxo':
				case 'vxo':
					$transaccion = ($estado['tipo']=='n' ? 100016 : 100017);
					break;

				default:
					$transaccion = ($estado['tipo']=='n' ? 100018 : 100019);
					break;
			}
			
			$descripcion = 'Carga masiva procesar lote nro.: ' . ltrim($estado['PagoCash']['lote'], "0");
			$this->Bitacora->registraBitacora($usuario['id'], $transaccion , NULL, NULL, NULL, NULL, $descripcion);
		}
		
		$this->set(compact('estado','usuario', 'exito','descripcion_error'));
		
		//$this->Session->delete('cash.pagos.data');
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina.masiva');
				$this->set('titulo','Pagos Nómina - Carga Masiva');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores.masiva');
				$this->set('titulo','Pagos Proveedores - Carga Masiva');
				break;
		}
	}

	/**
	 * Acción para mostrar los errores ocurridos en el proceso
	 * de pago masivo 
	 */
	function pago_masivo_error(){
		//if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.data');
		
		$this->set(compact('estado'));
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina.masiva');
				$this->set('titulo','Pagos Nómina - Carga Masiva');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores.masiva');
				$this->set('titulo','Pagos Proveedores - Carga Masiva');
				break;
		}
	}
	
	/**
	 * Acción para descargar en PDF el comprobante del pago masivo 
	 */
	function descarga_comprobante_masivo(){
		if(!$this->Session->check('cash.pagos.descarga')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.descarga');
		$usuario = $this->Session->read('usuarios.datos');
		
		$this->set(compact('estado','usuario'));
		
		$this->layout='nada';
	}
	
	/**
	 * Acción para descargar en PDF los errores ocurridos en el pago masivo
	 */
	function descarga_error_masivo(){
		
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.data');
		
		$usuario = $this->Session->read('usuarios.datos');
		
		$this->set(compact('estado','usuario'));
		
		$this->layout = 'nada';
	}
	
	/**
	 * Acción para descargar en PDF el comprobante del pago 
	 */
	function descarga_comprobante_pago(){
		if(!$this->Session->check('cash.pagos.descarga')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.descarga');
		$usuario = $this->Session->read('usuarios.datos');
		
		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('estado','usuario','bancos'));
		
		$this->layout='nada';
	}


	/**
	 * Acción para descargar en PDF el comprobante de la eliminación
	 */
	function descarga_comprobante_eliminacion(){
		if(!$this->Session->check('cash.data_usuario')) $this->redirect('manejo_usuarios/n');
		
		$this->data = $this->Session->read('cash.data_usuario');
		$usuario = $this->Session->read('usuarios.datos');
		
		$this->set('accion','ELIMINACIÓN');
		
		// Busqueda del nombre del banco
		$bancos = $this->Activo->buscarBancos();
		$this->data['AfiliacionCash']['banco'] =  $bancos[substr($this->data['AfiliacionCash']['numero_cuenta'],0,4)];
		
		$this->set(compact('usuario'));
		
		$this->layout='nada';
		
		$this->render('descarga_comprobante_afiliacion');
	}
	
	
	/**
	 * Acción para descargar en PDF el comprobante de la eliminación de pagos Nómina/Proveedores
	 */
	function descarga_comprobante_eliminacion_pagos(){
		if(!$this->Session->check('cash.pagos.descarga')) $this->redirect('/pagos/n');
		
		$pagos = $this->Session->read('cash.pagos.descarga');
		$usuario = $this->Session->read('usuarios.datos');

		$this->set('accion','ELIMINACIÓN');
		
		$this->set(compact('usuario','pagos'));
		
		$this->layout='nada';
		
		$this->render('descarga_comprobante_eliminacion_pagos');
	}
	

	/**
	 * Acción que muestra el listado con los beneficiarios afiliados a los
	 * procesos de pago de nómina o proveedores
	 * @param string $tipo Indica si se trabaja sobre beneficiarios de tipo
	 * proveedores: 'p' o nómina 'n'
	 */
	function manejo_usuarios($tipo=null){
            
		if(!empty($tipo)){
			if(!in_array($tipo,array('n','p'))) $this->redirect('/consultas/integral');
			
			if($tipo=='n' && !validarTransaccion(41)) {
				$this->redirect('/consultas/integral');
			} else if($tipo=='p' && !validarTransaccion(42)) {
				$this->redirect('/consultas/integral');
			}
			
			$this->Session->write(
				'cash.pagos',
				array(
					'tipo'=>$tipo
				)
			);
		} else if($this->Session->check('cash.pagos')){
			$estado = $this->Session->read('cash.pagos');
			$tipo = $estado['tipo'];
		} else {
			$this->redirect('/consultas/integral');
		}
		
		$this->Session->delete('cash.data_usuario');
						
		$usuario = $this->Session->read('usuarios.datos');
		
		$condiciones = array(
			'tipo'=>$tipo,
			'tipo_rif'=>$usuario['tipo_rif'],
			'rif'=>$usuario['rif']
		);		

		$total_registros = $this->AfiliacionCash->find(
			'count',
			array( 'conditions'=>$condiciones )
		);	
                
                switch($tipo){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo','Afiliaciones - Nómina');
                                $seccion_actual = 'cash.afiliaciones.nomina';                                
                                $titulo = 'Afiliaciones - Nómina';
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo','Afiliaciones - Proveedores');
                                $seccion_actual = 'cash.afiliaciones.proveedores';
                                $titulo= 'Afiliaciones - Proveedores';
				break;
		}
                
                //Valida el factor de autenticacion TC
                $validacion_tdc = $this->Session->read('seguridad.validar_tdc');		                                
                
		if(			
			(
                            $validacion_tdc == null ||
                            !isset($validacion_tdc['validado']) ||
                            !$validacion_tdc['validado']
			)
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/cash/manejo_usuarios',
					'layout'=>'interna',
					'titulo'=>$titulo,
					'pasos'=> array(),
					'paso_actual'=>0,
					'seccion_actual'=>$seccion_actual
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}
                //Fin TC
                
		if(isset($this->data['accion'])){
			switch($this->data['accion']){
				case 'crear_usuarios':
					if($total_registros < 10) {
						$this->Session->write(
							'cash.manejo_usuarios_modificar',
							array(
								'motivo'=>'crear',
								'tipo'=>$tipo,
								'url_volver'=>'manejo_usuarios/'.$tipo
							)
						);
						
						$this->redirect('modificar_usuarios');
					}
					break;
				case 'modificar_usuarios':
					$this->Session->write(
						'cash.manejo_usuarios_modificar',
						array(
							'motivo'=>'modificar',
							'tipo'=>$tipo,
							'url_volver'=>'manejo_usuarios/'.$tipo
						)
					);
					
					$afiliacion = $this->AfiliacionCash->findById($this->data['seleccion'][0]);
					
					if(
						$afiliacion &&
						$afiliacion['AfiliacionCash']['tipo_rif']==$usuario['tipo_rif'] &&
						$afiliacion['AfiliacionCash']['rif']==$usuario['rif']
					){
					
						$afiliacion['AfiliacionCash']['nombre'] = desencriptar($afiliacion['AfiliacionCash']['nombre']);
						$afiliacion['AfiliacionCash']['cedula'] = desencriptar($afiliacion['AfiliacionCash']['cedula']);
						$afiliacion['AfiliacionCash']['numero_cuenta'] = desencriptar($afiliacion['AfiliacionCash']['numero_cuenta']);
						$afiliacion['AfiliacionCash']['email'] = desencriptar($afiliacion['AfiliacionCash']['email']);
						$afiliacion['AfiliacionCash']['celular'] = desencriptar($afiliacion['AfiliacionCash']['celular']);
					
						$this->Session->write(
							'cash.data_usuario',
							$afiliacion
						);
						
						$this->redirect('modificar_usuarios');
					}
					
					break;
				case 'eliminar_usuarios':
					$this->Session->write(
						'cash.manejo_usuarios',
						array(
							'tipo'=>$tipo,
							'url_volver'=>'manejo_usuarios/'.$tipo,
							'seleccion'=>$this->data['seleccion']
						)
					);
					
					$this->redirect('eliminar_usuarios');
					break;
			}
		}

		$offset = 0;

		if(
			isset($this->data['AfiliacionCash']['offset']) &&
			preg_match('/^\d+$/',$this->data['AfiliacionCash']['offset'])
		){
			$offset = intval($this->data['AfiliacionCash']['offset']);			
		}
		$listado = $this->AfiliacionCash->find(
			'all',
			array(
				'conditions'=>$condiciones
			)
		);
		
		// Ordenar arreglo
		
		usort($listado,'ordernar_afiliacioncash_por_id');
		
		// Paginar arreglo
		
		$listado = array_slice($listado, $offset, Configure::read('Tamano.paginacion'));
		
		$bancos = $this->Activo->buscarBancos();
			
		$this->layout = 'interna';
		
		$this->set(compact('listado','bancos','tipo','total_registros'));
				
	}
	
	/**
	 * Acción para eliminar manualmente beneficiarios de pagos de nómina y proveedores
	 */	
	function eliminar_usuarios(){
		if(!$this->Session->check('cash.manejo_usuarios')){
			$this->redirect('pagos/n');
		}
		
		$estado = $this->Session->read('cash.manejo_usuarios');
			
		if($estado['tipo']=='n' && !validarTransaccion(41)) {
			$this->redirect('/consultas/integral');
		} else if($estado['tipo']=='p' && !validarTransaccion(42)) {
			$this->redirect('/consultas/integral');
		}

		if(isset($this->data['PagoCash']['confirmado'])){
			$estado['confirmado']=true;
			$this->Session->write('cash.manejo_usuarios',$estado);
			$this->redirect('eliminar_usuarios_resultado');			
		}
		
		$usuario = $this->Session->read('usuarios.datos');
		
		$afiliaciones = $this->AfiliacionCash->find(
			'all',
			array(
				'conditions'=>array(
					'id'=>$estado['seleccion'],
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif']
				)
			)
		);
		
		$this->layout = 'interna';		

		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('afiliaciones','bancos','estado'));
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo','Eliminar Beneficiario Nómina');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo','Eliminar Proveedores');
				break;
		}
	}

	/**
	 * Acción para indicar el resultado de la eliminación manual del
	 * beneficiario de pago de nómina o proveedor 
	 */
	function eliminar_usuarios_resultado(){
		if(!$this->Session->check('cash.manejo_usuarios')) {
			$this->redirect('manejo_usuarios');
		}
		
		$estado = $this->Session->read('cash.manejo_usuarios');
			
		if($estado['tipo']=='n' && !validarTransaccion(41)) {
			$this->redirect('/consultas/integral');
		} else if($estado['tipo']=='p' && !validarTransaccion(42)) {
			$this->redirect('/consultas/integral');
		}
		
		if(
			!isset($estado['confirmado'])
		){
			$this->redirect('manejo_usuarios');
		}
		
		$usuario = $this->Session->read('usuarios.datos');		
		
		$afiliaciones = $this->AfiliacionCash->find(
			'all',
			array(
				'conditions'=>array(
					'id'=>$estado['seleccion'],
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif']
				)
			)
		);
		
		$data = $afiliaciones[0];
		
		$data['AfiliacionCash']['nombre'] = desencriptar($data['AfiliacionCash']['nombre']);
		$data['AfiliacionCash']['numero_cuenta'] = desencriptar($data['AfiliacionCash']['numero_cuenta']);
		$data['AfiliacionCash']['cedula'] = desencriptar($data['AfiliacionCash']['cedula']);
		$data['AfiliacionCash']['email'] = desencriptar($data['AfiliacionCash']['email']);
		$data['AfiliacionCash']['celular'] = desencriptar($data['AfiliacionCash']['celular']);
		
		$this->Session->write('cash.data_usuario',$data);
		
		$res = $this->AfiliacionCash->deleteAll(
			array(
				'id'=>$estado['seleccion'],
				'tipo_rif'=>$usuario['tipo_rif'],
				'rif'=>$usuario['rif']
			)
		);
		
		$res = true;
		if(!$res){
			$this->set('error_fatal',_m('error.fatal'));
		}
		
		$this->layout = 'interna';		

		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('afiliaciones','bancos','estado'));		
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo','Eliminar Beneficiario Nómina');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo','Eliminar Proveedores');
				break;
		}
		
		$this->Session->delete('cash.manejo_usuarios');	
	}
	
	/**
	 * Acción para crear o modificar beneficiarios de pagos de nómina o
	 * proveedores
	 * @param string $volver Indica si se está regresando de la confirmación
	 */
	function modificar_usuarios($volver=null){
		if(!$this->Session->check('cash.manejo_usuarios_modificar')){
			$this->redirect('pagos/n');
		}
		
		$estado = $this->Session->read('cash.manejo_usuarios_modificar');
			
		if($estado['tipo']=='n' && !validarTransaccion(41)) {
			$this->redirect('/consultas/integral');
		} else if($estado['tipo']=='p' && !validarTransaccion(42)) {
			$this->redirect('/consultas/integral');
		}
			
		$usuario = $this->Session->read('usuarios.datos');
		
		if(isset($this->data['AfiliacionCash']['editando'])){
			// El usuario hizo POST del formulario
			
			if($this->Session->check('cash.data_usuario')){
				// Conservar id del registro a modificar
				// para verificar unicidad en validaciones del modelo
				$temp = $this->Session->read('cash.data_usuario');
				$this->data['AfiliacionCash']['id'] = $temp['AfiliacionCash']['id'];
			}
			
			// Colocar rif para validar en el modelo la unicidad
			$this->data['AfiliacionCash']['tipo_rif'] = $usuario['tipo_rif'];
			$this->data['AfiliacionCash']['rif'] = desencriptar($usuario['rif']);
			
			$this->AfiliacionCash->set($this->data);
			
			if($this->AfiliacionCash->validates(array(
				'fieldList'=>array(
					'numero_cuenta',
					'nombre',
					'tipo_cedula',
					'cedula',
					'email',
					'nroCelular',
					'codigoCelular',
					'nroCelular',
					'monto'
				)
			))){
				$this->data['AfiliacionCash']['tipo']=$estado['tipo'];
				$this->data['AfiliacionCash']['tipo_rif']=$usuario['tipo_rif'];
				$this->data['AfiliacionCash']['rif']=$usuario['rif'];
				$this->data['AfiliacionCash']['celular'] = $this->data['AfiliacionCash']['codigoCelular'].$this->data['AfiliacionCash']['nroCelular'];
				$this->data['motivo'] = $estado['motivo'];
				$this->Session->write('cash.data_usuario',$this->data);
				$this->redirect('modificar_usuarios_confirmar');
			}
		} else if(isset($volver) && $this->Session->check('cash.data_usuario')){
			// Regresando de la confirmación
			$this->data = $this->Session->read('cash.data_usuario');

		} else if($estado['motivo']=='modificar' && $this->Session->check('cash.data_usuario')){
			// Modificando  
			
			$this->data = $this->Session->read('cash.data_usuario');
			$this->data['AfiliacionCash']['codigoCelular'] = substr($this->data['AfiliacionCash']['celular'],0,4);
			$this->data['AfiliacionCash']['nroCelular'] = substr($this->data['AfiliacionCash']['celular'],-7);

		} else if($estado['motivo']=='crear'){
			$condiciones = array(
				'tipo'=>$estado['tipo'],
				'tipo_rif'=>$usuario['tipo_rif'],
				'rif'=>$usuario['rif']
			);		

			$total_registros = $this->AfiliacionCash->find(
				'count',
				array( 'conditions'=>$condiciones )
			);			
			
			if($total_registros >= 10){
				$this->Session->delete('cash.manejo_usuarios_modificar');
				$this->redirect('manejo_usuarios');
			}
			
			$this->data = array(
				'AfiliacionCash'=>array(
					'tipo'=>$estado['tipo']
				)
			);
		} else {
			// Usuario ingreso por url
			$this->redirect('manejo_usuarios/n');
		}
		
		if(
			isset($this->data['AfiliacionCash']['codigoCelular']) &&
			isset($this->data['AfiliacionCash']['nroCelular'])
		){
			$this->data['AfiliacionCash']['celular']=$this->data['AfiliacionCash']['codigoCelular'].$this->data['AfiliacionCash']['nroCelular'];
		}
		
		$bancos = $this->Activo->buscarBancos();

		$this->set(compact('bancos','estado'));
		$this->layout = 'interna';
		
		if($estado['motivo']=='modificar'){
			$titulo = 'Modificar';
			$titulo2 = 'A continuación proceda a modificar los datos del beneficiario';
		} else {
			$titulo = 'Crear';
			$titulo2 = 'A continuación proceda a crear los datos del beneficiario';
		}
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo',$titulo.' Beneficiario Nómina');
				$this->set('titulo2',$titulo2);
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo',$titulo.' Proveedores');
				$this->set('titulo2',$titulo2);
				break;
		}		
	}
	
	/**
	 * Acción para confirmar la modificación o creación del beneficiario 
	 */
	function modificar_usuarios_confirmar(){
		if(!$this->Session->check('cash.manejo_usuarios_modificar')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.manejo_usuarios_modificar');
			
		if($estado['tipo']=='n' && !validarTransaccion(41)) {
			$this->redirect('/consultas/integral');
		} else if($estado['tipo']=='p' && !validarTransaccion(42)) {
			$this->redirect('/consultas/integral');
		}
		
		$bancos = $this->Activo->buscarBancos();
		$data =  $this->Session->read('cash.data_usuario');
		
		$data['AfiliacionCash']['banco'] =  $bancos[substr($data['AfiliacionCash']['numero_cuenta'],0,4)];
		
		if(isset($this->data['AfiliacionCash']['confirmado'])){
		
			$this->AfiliacionCash->id = $data['AfiliacionCash']['id'];
		
			// Verifica si ya existe 10 afiliados
			$usuario = $this->Session->read('usuarios.datos');
			$total_registros = $this->AfiliacionCash->find(
				'count',
				array( 'conditions'=>array(
						'tipo'=>$estado['tipo'],
						'tipo_rif'=>$usuario['tipo_rif'],
						'rif'=>$usuario['rif']
					)
				)
			);
			
			if($total_registros >= 10 && $estado['motivo']!='modificar'){
				$estado['confirmado']=true;
				$estado['cantidad_superada']=true;
				$this->Session->write('cash.manejo_usuarios_modificar',$estado);
				$this->redirect('modificar_usuarios_resultado');
			}
			else{

				$res = $this->AfiliacionCash->save(
					array(
						'AfiliacionCash'=>array(
							'tipo_carga'=>'m',
							'tipo' => $data['AfiliacionCash']['tipo'],
							'tipo_rif' => $data['AfiliacionCash']['tipo_rif'],
							'rif' => $data['AfiliacionCash']['rif'],
							'nombre' => encriptar($data['AfiliacionCash']['nombre']),
							'tipo_cedula' => $data['AfiliacionCash']['tipo_cedula'],
							'cedula' => encriptar($data['AfiliacionCash']['cedula']),
							'numero_cuenta' => encriptar($data['AfiliacionCash']['numero_cuenta']),
							'email' => encriptar($data['AfiliacionCash']['email']),
							'celular' => encriptar($data['AfiliacionCash']['celular']),
						)
					),
					false
				);

				if($res){

					$usuario = $this->Session->read('usuarios.datos');
					$transaccion = ($estado['tipo']=='n' ? 41 : 42);
					$descripcion = 'Afiliación ';
					$descripcion.= ($estado['tipo']=='n' ? 'nomina' : 'proveedor');
                                        
					if($estado['tipo']=='n'){
                                            // Enviar por sms la notificación
                                            if( isset($usuario['celular']) ){                                    
                                                    $this->Sms->enviar(
                                                            array(
                                                                    'numcel'=>desencriptar($usuario['celular']),
                                                                    'canal1'=>50
                                                            )
                                                    );
                                            }
                                        }else{
                                            // Enviar por sms la notificación
                                            if( isset($usuario['celular']) ){                                    
                                                    $this->Sms->enviar(
                                                            array(
                                                                    'numcel'=>desencriptar($usuario['celular']),
                                                                    'canal1'=>51
                                                            )
                                                    );
                                            }
                                        }
                                        
                                        $this->Bitacora->registraBitacora($usuario['id'], $transaccion , NULL, NULL, NULL, NULL, $descripcion);
					$estado['confirmado']=true;
					$this->Session->write('cash.manejo_usuarios_modificar',$estado);
					$this->redirect('modificar_usuarios_resultado');		
				} else {
					$this->set('error_fatal',_m('error.fatal'));
				}
			}
		}
		
		$this->data = $data;
		
		$this->data['AfiliacionCash']['banco'] =  $bancos[substr($this->data['AfiliacionCash']['numero_cuenta'],0,4)];
		$this->data['AfiliacionCash']['celular'] = $data['AfiliacionCash']['codigoCelular'].$data['AfiliacionCash']['nroCelular'];
		
		$this->set(compact('bancos'));
		$this->layout = 'interna';
		
		if($estado['motivo']=='modificar'){
			$titulo = 'Modificar';
		} else {
			$titulo = 'Crear';
		}
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo',$titulo.' Beneficiario Nómina');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo',$titulo.' Proveedores');
				break;
		}	
	}
	
	/**
	 * Acción para indicar el resultado de la modificación o creación del
	 * beneficiario 
	 */
	function modificar_usuarios_resultado(){
		if(!$this->Session->check('cash.manejo_usuarios_modificar')) $this->redirect('manejo_usuarios/n');
		
		$estado = $this->Session->read('cash.manejo_usuarios_modificar');
			
		if($estado['tipo']=='n' && !validarTransaccion(41)) {
			$this->redirect('/consultas/integral');
		} else if($estado['tipo']=='p' && !validarTransaccion(42)) {
			$this->redirect('/consultas/integral');
		}
		
		if(isset($estado['cantidad_superada'])){
			$this->set(compact('estado'));
		}
		else{
			$bancos = $this->Activo->buscarBancos();

			$data =  $this->Session->read('cash.data_usuario');
			$this->data = $data;

			$this->data['AfiliacionCash']['banco'] =  $bancos[substr($this->data['AfiliacionCash']['numero_cuenta'],0,4)];

			if(!isset($estado['confirmado'])){
				$this->redirect('pagos/n');
			}

			$this->set(compact('bancos'));
		}
		
		$this->layout = 'interna';

		
		if($estado['motivo']=='modificar'){
			$titulo = 'Modificar';
		} else {
			$titulo = 'Crear';
		}
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo',$titulo.' Beneficiario Nómina');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo',$titulo.' Proveedores');
				break;
		}
		
		$this->Session->delete('cash.manejo_usuarios_modificar');	
	}
	
	/**
	 * Acción para descargar en PDF el comprobante de la afiliación
	 */
	function descarga_comprobante_afiliacion(){
		if(!$this->Session->check('cash.data_usuario')) $this->redirect('manejo_usuarios/n');
		
		$this->data = $this->Session->read('cash.data_usuario');
		$usuario = $this->Session->read('usuarios.datos');
		
		if($this->data['motivo']=='modificar'){
			$this->set('accion','MODIFICACIÓN');
		} else {
			$this->set('accion','AFILIACIÓN');
		}
		
		// Busqueda del nombre del banco
		$bancos = $this->Activo->buscarBancos();
		$this->data['AfiliacionCash']['banco'] =  $bancos[substr($this->data['AfiliacionCash']['numero_cuenta'],0,4)];
		
		$this->set(compact('usuario'));
		
		$this->layout='nada';
		
	}
	
	/**
	 * Accion para cargar masivamente beneficiarios de pago de nómina y
	 * proveedores
	 * @param string $tipo Indica si se está trabajando sobre proveedores: 'p'
	 * o nómina 'n'
	 */
	function carga_masiva_beneficiarios($tipo=null){                                
                
		if(!empty($tipo)){
			if(!in_array($tipo,array('n','p'))) $this->redirect('/consultas/integral');
			
			if($tipo=='n' && !validarTransaccion(41)) {
				$this->redirect('/consultas/integral');
			} else if($tipo=='p' && !validarTransaccion(42)) {
				$this->redirect('/consultas/integral');
			}
			
			$this->Session->write(
				'cash.pagos',
				array(
					'tipo'=>$tipo
				)
			);
		} else if($this->Session->check('cash.pagos')){
			$datos = $this->Session->read('cash.pagos');
			$tipo = $datos['tipo'];
		} else { 
			$this->redirect('/consultas/integral');
		}
		
                switch($tipo){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina.masiva');
				$this->set('titulo','Afiliaciones - Nómina');
                                $seccion_actual = 'cash.afiliaciones.nomina.masiva';                                
                                $titulo = 'Afiliaciones - Nómina';
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores.masiva');
				$this->set('titulo','Afiliaciones - Proveedores');
                                $seccion_actual = 'cash.afiliaciones.proveedores.masiva';                                
                                $titulo = 'Afiliaciones - Proveedores';
				break;
		}
                
                //Valida el factor de autenticacion TC
                $validacion_tdc = $this->Session->read('seguridad.validar_tdc');		                                
                
		if(			
			(
                            $validacion_tdc == null ||
                            !isset($validacion_tdc['validado']) ||
                            !$validacion_tdc['validado']
			)
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/cash/carga_masiva_beneficiarios',
					'layout'=>'interna',
					'titulo'=>$titulo,
					'pasos'=> array(),
					'paso_actual'=>0,
					'seccion_actual'=>$seccion_actual
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}
                //Fin TC
                
		$data=$this->data['CargaMasivaBeneficiariosCash']['archivo']['tmp_name'];
		$nombreArchivo = $this->data['CargaMasivaBeneficiariosCash']['archivo']['name'];
		
		$usuario = $this->Session->read('usuarios.datos');
		
		if(!empty($data)){
			$error_archivo=false;
			
			// Verifica la extension del archivo
			$this->AfiliacionCash->set($this->data['CargaMasivaBeneficiariosCash']);
			
			if ($this->AfiliacionCash->validates(array('fieldList' => array(
				'archivo',
			)))){
				$fp = fopen($data, "r");
				$errores=array();
				$registros=array();
				$kcuentas = array();
				$kcedulas = array();
				$linea=0;

				while (!feof($fp)){
					
					// Actualiza el tiempo de la session en la base de datos
					$this->UsuarioCanal->sessTime($usuario['id'], _p('tiempo_session'));
					
					$linea++;
					$contenido_linea = trim(fgets($fp));
					
					if(!strlen($contenido_linea)) continue;

					$data = explode(";", $contenido_linea);
					
					if(count($data) != 7){
						$errores[] = array(
							'registro'=>$linea,
							'identificacion'=>'',
							'nombre'=>'',
							'error'=>'Estructura del registro incompleto'
						);
						
						continue;
					}

					$tipoCedula = strtoupper(substr($data[0], 0, 1));
					$cedula = trim(substr($data[0], 1));
					$nombre = trim($data[1]);
					$cuenta =  $data[2];
					$banco =   trim($data[3]);
					$email =    $data[4];
					$telefono = $data[5];
					$accion =  strtoupper(trim($data[6]));
					$valido= true;
			
					if(in_array($tipoCedula ,array('V','P','E'))){
						$cedula = ltrim($cedula,0);	
					}
					
					$data = array(
						'AfiliacionCash'=>array(
							'tipo_rif'=>$usuario['tipo_rif'],
							'rif'=>desencriptar($usuario['rif']),
							'tipo_cedula'=>$tipoCedula,
							'cedula'=>$cedula,
							'nombre'=>$nombre,
							'numero_cuenta'=>$cuenta,
							'banco'=>$banco,
							'email'=>$email,
							'nroCelular'=>substr($telefono,4), 
							'codigoCelular'=>substr($telefono,0,4),
							'accion'=>$accion,
							'tipo'=>$tipo,
							'tipo_carga' => 'a'
						)
					);
					
					// buscar la afiliacion en bd
					
					$afiliacion_existente = $this->AfiliacionCash->find(
						'first',
						array(
							'conditions'=>array(
								'tipo_rif'=>$usuario['tipo_rif'],
								'rif'=>$usuario['rif'],
								'tipo_cedula'=>$data['AfiliacionCash']['tipo_cedula'],
								'cedula'=>encriptar($data['AfiliacionCash']['cedula']),
								'tipo'=>$tipo,
							)
						)
					);
					
					if(!$afiliacion_existente){
						// Si no encontró por cédula, buscar por nro de cuenta
						
						$afiliacion_existente = $this->AfiliacionCash->find(
							'first',
							array(
								'conditions'=>array(
									'tipo_rif'=>$usuario['tipo_rif'],
									'rif'=>$usuario['rif'],
									'numero_cuenta'=>encriptar($data['AfiliacionCash']['numero_cuenta']),
									'tipo'=>$tipo,
								)
							)
						);
					}
						
					// preparar arreglo de error
					
					$error = array(
						'registro'=>$linea,
						'identificacion'=> $tipoCedula.$cedula,
						'nombre'=>$nombre,
					);	
					
					if($afiliacion_existente){
						// colocar id para validaciones de unicidad
						$data['AfiliacionCash']['id'] = $afiliacion_existente['AfiliacionCash']['id'];
					}
					
					$this->AfiliacionCash->set($data);		
					
					if(!$this->AfiliacionCash->validates()){
						// Validar cédula
						
						if(isset($this->AfiliacionCash->validationErrors['tipo_cedula'])){
							$error['error']='Tipo de Identificación Inválido - '.$tipoCedula.$cedula;
							$errores[] = $error;
						} else if(isset($this->AfiliacionCash->validationErrors['cedula'])) {
							switch($this->AfiliacionCash->validationErrors['cedula']){
								case 'noBeneficiario':
									$error['error'] = _m('cuenta.no.beneficiario');
									break;
								case 'unicidad':
									$error['error'] = _m('beneficiario.ya.afiliado');
									break;
								default:
									$error['error']='RIF o Cédula invalida - '.$tipoCedula.$cedula;	
							}
							$errores[] = $error;
						}
					
						if($accion != 'D'){
							// Si no es instrucción de eliminación, validar todos los datos
							
							if(isset($this->AfiliacionCash->validationErrors['nombre'])){
								$error['error']='Nombre o dirección del beneficiario en blanco/invalido - '.$nombre;
								$errores[] = $error;
							}
							
							if(isset($this->AfiliacionCash->validationErrors['numero_cuenta'])){
								
								switch($this->AfiliacionCash->validationErrors['numero_cuenta']){
									case 'unicidad':
										$error['error']=_m('producto.nodisponible').' - '.$cuenta;
										break;
									case 'cuentaControlada':
									case 'cuentaInactiva':
										$error['error']=_m('cuenta.inactiva', 'la cuenta del beneficiario').' - '.$cuenta;
										break;
									case 'productoPropio':
										$error['error']=_m('producto.propio').' - '.$cuenta;
										break;
									default:
										$error['error']='Cuenta del Beneficiario/deudor invalida o no existe - '.$cuenta;
								}
								
								$errores[] = $error;
							}
							
							if(isset($this->AfiliacionCash->validationErrors['email'])){
								$error['error']='Instrucción Invalida - Email- '.$email;
								$errores[] = $error;
							}
							
							if(isset($this->AfiliacionCash->validationErrors['nroCelular'])){
								$error['error']='Instrucción Invalida - Celular - '.$telefono;
								$errores[] = $error;
							}
						}
					}
					
					
					if($accion=='D' && !$afiliacion_existente){
						$error['error'] = 'No se puede eliminar este beneficiario, ya que no existe';
						$errores[] = $error;
					} else if(array_key_exists($tipoCedula.$cedula, $kcedulas)){
						$error['error'] = 'Registro duplicado - '.$tipoCedula.$cedula;	
						$errores[] = $error;
					} else if(
						array_key_exists($cuenta, $kcuentas) &&
						!($tipo=='p' && substr($cuenta,0,4)=='0171')
					){	
						// Para pago de proveedores en cuentas de banco activo
						// se pueden repetir los números de cuenta para distintos afiliados
						// siempre y cuando sean cotitulares de dicha cuenta
						// En cualquier otro caso, indicar el error de duplicidad
						$error['error'] = 'Registro duplicado - '.$cuenta;	
						$errores[] = $error;
					} else if(empty($this->AfiliacionCash->validationErrors)) {
						$data['AfiliacionCash']['cedula'] = encriptar($data['AfiliacionCash']['cedula']);
						$data['AfiliacionCash']['nombre'] = encriptar($data['AfiliacionCash']['nombre']);
						$data['AfiliacionCash']['numero_cuenta'] = encriptar($data['AfiliacionCash']['numero_cuenta']);
						$data['AfiliacionCash']['banco'] = encriptar($data['AfiliacionCash']['banco']);
						$data['AfiliacionCash']['email'] = encriptar($data['AfiliacionCash']['email']);
						$data['AfiliacionCash']['nroCelular'] = encriptar($data['AfiliacionCash']['nroCelular']);
						$data['AfiliacionCash']['codigoCelular'] = encriptar($data['AfiliacionCash']['codigoCelular']);
						$registros[]=$data;
					}
					
					$kcuentas[$cuenta]=true;
					
					$kcedulas[$tipoCedula.$cedula]=true;
				}

				if(!count($errores)){
					$estado['archivo'] = $this->data['CargaMasivaBeneficiariosCash']['archivo']['name'];
					$estado['registros'] = $registros;
					$this->Session->write('afiliacion.masiva', $estado);
					$this->redirect('carga_masiva_beneficiarios_confirmacion');	
				} else {
					$manejoErrores['tipo']= $tipo;
					$manejoErrores['errores'] = $errores;
					$manejoErrores['archivo'] = $this->data['CargaMasivaBeneficiariosCash']['archivo']['name'];
					$this->Session->write('cash.afiliaciones_errores',$manejoErrores);
					$this->redirect('carga_masiva_beneficiarios_error');				
				}
			}
			
			if(isset($this->AfiliacionCash->validationErrors['archivo'])){
				$error_archivo=true;
                                $mensaje_error = $this->AfiliacionCash->validationErrors['archivo'];
			}
		}
		
		$this->set(compact('tipo','error_archivo','mensaje_error'));	
		$this->layout = 'interna';
				
	}

	/**
	 * Acción para confirmación de la carga masiva de beneficiarios para
	 * pagos de nómina y proveedores
	 */
	function carga_masiva_beneficiarios_confirmacion(){
		if(!$this->Session->check('afiliacion.masiva')) $this->redirect('pagos');
		if(!$this->Session->check('cash.pagos')) $this->redirect('pagos');
		
		$estado = $this->Session->read('afiliacion.masiva');
		$usuario = $this->Session->read('usuarios.datos');
		$tipo = $this->Session->read('cash.pagos');

		$this->set(compact('tipo','estado'));	
		$this->layout = 'interna';
		
		switch($tipo['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina.masiva');
				$this->set('titulo','Afiliaciones - Nómina');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores.masiva');
				$this->set('titulo','Afiliaciones - Proveedores');
				break;
		}	
	}
	
	/**
	 * Acción para indicar el resultado de la carga masiva de beneficiarios
	 * para pagos de nómina y proveedores
	 */
	function carga_masiva_beneficiarios_resultado(){
		if(!$this->Session->check('afiliacion.masiva')) $this->redirect('pagos');
		if(!$this->Session->check('cash.pagos')) $this->redirect('pagos');
		
		$data=$this->Session->read('afiliacion.masiva');
		$usuario = $this->Session->read('usuarios.datos');
		$tipo = $this->Session->read('cash.pagos');
		
		$band=true;
		$afiliados=array();
		$errores=array();
		$agregados = 0;
		$modificados = 0;
		$eliminados = 0;
		$registros=array();
		$bandera = true;
		$noEliminados = array();
		$noAgregados = array();
		$noModificados = array();
		
		$totalregistros = count($data);
		
		$ds = $this->AfiliacionCash->getDataSource();
		$i = 1;
		
		foreach($data['registros'] as $datos){
			if($datos['AfiliacionCash']['accion'] == 'A'){
				if(isset($datos['AfiliacionCash']['id'])) {
					$afiliados[]=$datos;
					$modificados++;				
				} else {
					$afiliados[]=$datos;
					$agregados++;
				}
			} else {
				$afiliados[]=$datos;
				$eliminados ++;
			}
			
			$i++;
		}

		//verifica si hay errores 
		if(count($errores)){
			$manejoErrores['tipo']= $tipo;
			$manejoErrores['errores'] = $errores;
			$manejoErrores['archivo'] = $data['archivo'];
			$this->Session->write('cash.afiliaciones_errores',$manejoErrores);
			$this->redirect('carga_masiva_beneficiarios_error');
		}	
		
		foreach($afiliados as &$datos){
			if($datos['AfiliacionCash']['accion'] == 'D'){
				$datos['AfiliacionCash']['descripcion_accion']='Eliminar';
					
				if(!$this->AfiliacionCash->deleteAll(
					array(
						'tipo'=>$tipo['tipo'],
						'tipo_rif'=>$usuario['tipo_rif'],
						'rif'=>$usuario['rif'],
						'cedula'=>$datos['AfiliacionCash']['cedula'],
						'tipo_cedula'=>$datos['AfiliacionCash']['tipo_cedula']
					),
					false
				)){
					$bandera = false;

					$this->log('Error de bd al eliminar afiliado '.$usuario['tipo_rif'].desencriptar($usuario['rif']), LOG_ERROR);
					$this->log($this->getDataSource()->error, LOG_ERROR);
				}else{
					$bandera = true;
				}
			}elseif($datos['AfiliacionCash']['accion'] == 'A'){
				$c = desencriptar($datos['AfiliacionCash']['codigoCelular']);
				$n = desencriptar($datos['AfiliacionCash']['nroCelular']);
				$celular = $c.$n;
				
				$this->AfiliacionCash->create();
				
				if(isset($datos['AfiliacionCash']['id'])) {
					$datos['AfiliacionCash']['descripcion_accion']='Modificar';
					$this->AfiliacionCash->id = $datos['AfiliacionCash']['id'];
				} else {
					$datos['AfiliacionCash']['descripcion_accion']='Agregar';
				}
				
				$res = $this->AfiliacionCash->save(
					 array(
						'AfiliacionCash'=>
							array(
								'tipo' => $datos['AfiliacionCash']['tipo'],
								'nombre' => $datos['AfiliacionCash']['nombre'],
								'tipo_cedula' => $datos['AfiliacionCash']['tipo_cedula'],
								'cedula' => $datos['AfiliacionCash']['cedula'],
								'numero_cuenta' => $datos['AfiliacionCash']['numero_cuenta'],
								'email' => $datos['AfiliacionCash']['email'],
								'celular' => encriptar($celular),
								'tipo_carga'=>$datos['AfiliacionCash']['tipo_carga'],
								'tipo_rif'=>$usuario['tipo_rif'],
								'rif'=>$usuario['rif'],
							)
					 ),
					 false
				);
				
				if($res){
					$bandera=true;	
					$registros[]=$res;
				} else {
					$bandera = false;
					$this->log('Error de bd al guardar afiliado '.$usuario['tipo_rif'].desencriptar($usuario['rif']), LOG_ERROR);
					$this->log($this->AfiliacionCash->getDataSource()->error, LOG_ERROR);
				}
			}	
		}
			
		$resultado['registros'] = $afiliados;
		$resultado['agregados'] = $agregados;
		$resultado['eliminados'] = $eliminados;
		$resultado['modificados'] = $modificados;
		$resultado['tipo'] = $tipo['tipo'];
		$resultado['archivo'] = $data['archivo'];
		$resultado['cantidad_registros'] = count($data['registros']);
		
		$this->Session->write('descarga.masiva.pdf', $resultado);	
		
		$cantidad = $this->AfiliacionCash->find(
			'count',
			array(
				'conditions'=>array(
					'tipo'=>$tipo['tipo'],
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif']
				)
			)
		);
		
		if($cantidad > 10){
			// Si hay mas de 10 afiliados, se desactiva la opción de pago manual
			// y por lo tanto hay que eliminar los pagos guardados
			
			$tipos = array(
				'n'=>array('nxm','nxo','nxa'),
				'p'=>array('vxm','vxo','vxa')
			);
			
			$res = $this->TransaccionPendiente->deleteAll(
				array(
					'TransaccionPendiente.tipo_rif' => $usuario['tipo_rif'],
					'TransaccionPendiente.rif' => $usuario['rif'],
					'TransaccionPendiente.estatus' => 'g',
					'TransaccionPendiente.tipo' => $tipos[$tipo['tipo']]					
				),
				false
			);
			
			if($res){
				$bandera = true;
			} else {
				$bandera = false;
			}
		}

		if($bandera){
			$transaccion = ($tipo=='n' ? 41 : 42);
			$descripcion = 'Carga masiva afiliación ';
			$descripcion.= ($tipo=='n' ? 'nomina' : 'proveedor');
			$this->Bitacora->registraBitacora($usuario['id'], $transaccion , NULL, NULL, NULL, NULL, $descripcion);
			$ds->commit($this->AfiliacionCash);
		} else {
			$ds->rollback($this->AfiliacionCash);
		}
		
		$bancos = $this->Activo->buscarBancos();
		$this->set(compact('bandera','resultado','bancos','afiliados','agregados','eliminados','modificados','resultado','tipo','data'));
		$this->Session->delete('afiliacion.masiva');
		$this->layout = 'interna';	
			
		switch($tipo['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina.masiva');
				$this->set('titulo','Afiliaciones - Nómina');
                                if($bandera){
                                    // Enviar por sms la notificación
                                    if( isset($usuario['celular']) ){                                    
                                            $this->Sms->enviar(
                                                    array(
                                                            'numcel'=>desencriptar($usuario['celular']),
                                                            'canal1'=>50
                                                    )
                                            );
                                    }
                                }
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores.masiva');
				$this->set('titulo','Afiliaciones - Proveedores');
                                if($bandera){
                                    // Enviar por sms la notificación
                                    if( isset($usuario['celular']) ){                                    
                                            $this->Sms->enviar(
                                                    array(
                                                            'numcel'=>desencriptar($usuario['celular']),
                                                            'canal1'=>51
                                                    )
                                            );
                                    }
                                }
				break;
		}
	}
	
	/**
	 * Acción para descargar el resultado de la carga masiva de beneficiarios
	 * en PDF 
	 */
	function descarga_carga_masiva_beneficiarios(){
		if(!$this->Session->check('descarga.masiva.pdf')) $this->redirect('/consultas/integral');
		$bancos = $this->Activo->buscarBancos();
		$this->set(compact('bancos'));
		$cargaMasiva=$this->Session->read('descarga.masiva.pdf');
		$usuario = $this->Session->read('usuarios.datos');
		$this->set(compact('usuario','cargaMasiva'));
		$this->render('/cash/descargarCargaMasiva_pdf');
	}
	
	/**
	 * Acción para indicar los errores en la carga masiva de beneficiarios
	 * para pagos de nómina y proveedores
	 */
	function carga_masiva_beneficiarios_error(){
		if(!$this->Session->check('cash.pagos')) $this->redirect('/consultas/integral');
		if(!$this->Session->check('cash.afiliaciones_errores')) $this->redirect('/consultas/integral');

		$usuario = $this->Session->read('usuarios.datos');
		$tipo = $this->Session->read('cash.pagos');
		$manejoErrores = $this->Session->read('cash.afiliaciones_errores');
		$this->set(compact('tipo','manejoErrores'));
		$this->layout = 'interna';	
			
		switch($tipo['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina.masiva');
				$this->set('titulo','Afiliaciones - Nómina');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores.masiva');
				$this->set('titulo','Afiliaciones - Proveedores');
				break;
		}
	}
	
	/**
	 * Acción para confirmación de los errores ocurridos en la carga masiva
	 * de beneficiarios de pagos de nómina y proveedores 
	 */
	function carga_masiva_beneficiarios_error_confir(){
		if(!$this->Session->check('afiliacion.masiva')) $this->redirect('/consultas/integral');
		if(!$this->Session->check('cash.pagos')) $this->redirect('/consultas/integral');
		if(!$this->Session->check('cash.afiliaciones_errores')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('afiliacion.masiva');
		$usuario = $this->Session->read('usuarios.datos');
		$tipo = $this->Session->read('cash.pagos');
		$errores =  $this->Session->read('cash.afiliaciones_errores');
		
		$this->set(compact('tipo','errores'));	
		$this->layout = 'interna';
		
		switch($tipo['tipo']){
				case 'n':
					$this->set('seccion_actual','cash.afiliaciones.nomina.masiva');
					$this->set('titulo','Afiliaciones - Nómina');
					break;
				case 'p':
					$this->set('seccion_actual','cash.afiliaciones.proveedores.masiva');
					$this->set('titulo','Afiliaciones - Proveedores');
					break;
			}	
	}
	
	/**
	 * Acción para descargar en PDF los errores ocurridos en la carga
	 * masiva de pagos
	 */
	function descarga_carga_masiva_error(){
		if(!$this->Session->check('cash.afiliaciones_errores')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.afiliaciones_errores');

		$usuario = $this->Session->read('usuarios.datos');
		
		$this->set(compact('estado','usuario'));
		
		$this->layout = 'nada';
	}
	
}

/**
 * Función para ordenar por campo identificación
 * 
 * @param array $a
 * @param array $b
 * @return int
 */
function ordernar_afiliacioncash_por_id($a,$b){
	if($a['AfiliacionCash']['tipo_cedula'] == $b['AfiliacionCash']['tipo_cedula']){
		// Si el tipo de identificación es el mismo
		// comparar el número
		
		$id_a = desencriptar($a['AfiliacionCash']['cedula']);
		$id_b = desencriptar($b['AfiliacionCash']['cedula']);
		
		if($a['AfiliacionCash']['tipo_cedula']=='P'){
			// Si es pasaporte, puede tener letras, por lo tanto la comparación es textual
			return strcmp($id_a,$id_b);
		} else {
			// Comparación numérica
			
			$id_a_i = intval(trim($id_a));
			$id_b_i = intval(trim($id_b));
			
			if($id_a_i==$id_b_i){
				return 0;
			} else if($id_a_i<$id_b_i){
				return -1;
			} else {
				return 1;
			}
		}
	} else {
		return strcmp($a['AfiliacionCash']['tipo_cedula'],$b['AfiliacionCash']['tipo_cedula']);
	}
}

/**
 * Función para ordenar el listado de errores
 * 
 * @param array $a
 * @param array $b
 * @return int
 */
function ordernar_errores_por_linea($a, $b){
	return strcmp($a['registro'], $b['registro']);
}