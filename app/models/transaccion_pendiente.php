<?php
/**
 * Modelo para el manejo de las transaciones de pagos y transferencias de los
 * usuarios jurídicos.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de las transaciones de pagos y transferencias de los usuarios jurídicos.
 * 
 */
class TransaccionPendiente extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'transacciones_pendientes';

	
	/**
	 * Tipos de transacciones.
	 * 
	 * @var array 
	 */
	public static $tipo_transaccion = array(
		'tpm'=> 'Transferencia Mismo Titular en Activo',
		'tpo'=> 'Transferencia Mismo Titular otro Banco',                
		'ttm'=> 'Transferencia Tercero en Activo',
		'tto'=> 'Transferencia Tercero en Otro Banco',
                'dpm'=> 'Transferencia Mismo Titular en Activo Moneda Extranjera',
                'dtm'=> 'Transferencia Tercero en Activo Moneda Extranjera',
		'ppm'=> 'Pago Tarjeta de Crédito Mismo Titular en Activo',
		'ppo'=> 'Pago Tarjeta de Crédito Mismo Titular Otro Banco',
		'ptm'=> 'Pago Tarjeta de Crédito Tercero en Activo',
		'pto'=> 'Pago Tarjeta de Crédito Tercero en Otro Banco',
		'nxm'=> 'Pago de Nómina en Activo',
                'mxm'=> 'Pago de Nómina en Activo Me',
		'nxo'=> 'Pago de Nómina en Otros Bancos',
		'nxa'=> 'Pago de Nómina en Activo y Otros Bancos',
		'vxm'=> 'Pago a Proveedores en Activo',
                'xxm'=> 'Pago a Proveedores en Activo Me',
		'vxo'=> 'Pago a Proveedores en Otros Bancos',
		'vxa'=> 'Pago a Proveedores en Activo y Otros Bancos',
                'ip '=> 'Pago de Impuestos Nacionales/Aduanales Mismo Titular',
                'it '=> 'Pago de Impuestos Nacionales/Aduanales Terceros',
		'csi'=> 'Suspensión de Cheque',
		'csx'=> 'Suspensión de Chequera',
		'csr'=> 'Suspensión de Rango de Cheques',
		'scn'=> 'Pago Servicio Movilnet',
		'stm'=> 'Pago Servicio Movilnet',
		'rcn'=> 'Recarga Servicio Movilnet',
                'scd'=> 'Pago Servicio Digitel',
		'rcd'=> 'Recarga Servicio Digitel',
                'scm'=> 'Pago Servicio Movistar',
		'rcm'=> 'Recarga Servicio Movistar',
		'rsi'=>'Recarga Servicio Inter',
		'ssi'=>'Recarga Servicio Inter',
                'exc'=>'Mesa Cambio Compra',
                'exv'=>'Mesa Cambio Venta',
                'fxc'=>'Intervención Compra',
        	'rsv'=>'Recarga Simpletv',
        	'spc'=>'Servicio Pago Corpoelec'
	);

	
	/**
	 * Estatus de las transacciones.
	 * 
	 * @var array
	 */
	public static $estatus_transaccion = array(
		'pa'=> 'Por aprobar',
		'ep'=> 'En proceso',
		'ae'=> 'Aprobadas por ejecutar',
		'en'=> 'Enviada',
		'ej'=> 'Ejecutada',
		'el'=> 'Eliminada',
		'ra'=> 'Rechazada usuario aprobador',
		'rb'=> 'Rechazada banco beneficiario',
		'g '=> 'Guardada',
		've'=> 'Vencido'
	);
	
	/**
	 * Indica las tablas con las que tiene una relación foránea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'UsuarioCreador'=>array(
			'className'=>'Usuario',
			'foreignKey'=>'usuario_id'
		)
	);

	
	/**
	 * Indica las tablas que se relacionan con el modelo.
	 * 
	 * @var array 
	 */
	var $hasMany = array(
		'Beneficiarios'=>array(
			'className'=>'TransaccionBeneficiario',
			'foreignKey'=>'transaccion_id'
		),
		'AprobacionesRealizadas'=>array(
			'className'=>'AprobacionTransaccion',
			'foreignKey'=>'transaccion_id'
		)			
	);
	
	
	/**
	 * Método para validar las cuentas de origen y destino.
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

		return !($value == $this->data['TransaccionPendiente']['cuenta_origen']);
	}
	
	/**
	 * Obtiene las transacciones pendientes de un usuario.
	 * 
	 * @param int $usuario
	 * @return array
	 */
	function obtenerAprobacionesPendientes($usuario){
		$this->AprobacionTransaccion = ClassRegistry::init('AprobacionTransaccion');
		
		// Condiciones para la búsqueda de transacciones
		$valores = array('pa');
		$condiciones = array(
			'TransaccionPendiente.tipo_rif'=>$usuario['tipo_rif'],
			'TransaccionPendiente.rif'=>$usuario['rif'],
			'TransaccionPendiente.estatus'=>$valores
		);
		
		// Las transacciones que se muestran dependen del tipo de usuario logueado
		
		switch($usuario['rol_juridico_id']){
			case 3:
				// Los operadores sólo pueden ver transacciones creadas por ellos mismos
				$condiciones['TransaccionPendiente.usuario_id']=$usuario['id'];
				break;
			case 4:
				// Los aprobadores sólo pueden ver transacciones que no hayan aprobado todavía ellos mismos
				// ELIMINADA ESTA CONDICION POR SOLICITUD DE VIVIANNE EL 10/4/13
				// POR SOLICITUD DE LUIS ARAUJO(CORREO 29-04-2013) SE REACTIVÓ ESTA OPCIÓN
				$condiciones['not exists(select * from aprobaciones_transacciones ap where ap.transaccion_id=TransaccionPendiente.id and usuario_id=?)']=$usuario['id'];
				
				$condiciones['or']=array(
					'TransaccionPendiente.fecha_hora_valor is null',
					'TransaccionPendiente.fecha_hora_valor >= '=>date('Y-m-d')
				);
				
				break;
			case 5:
				// Los operadores aprobadores pueden ver las que cargaron ellos mismos
				// o por otros usuarios que todavía no hayan sido aprobadas por este usuario
				// ELIMINADA ESTA CONDICION POR SOLICITUD DE VIVIANNE EL 10/4/13
				// POR SOLICITUD DE LUIS ARAUJO(CORREO 29-04-2013) SE REACTIVÓ ESTA OPCIÓN
				$condiciones['or']=array(
					'TransaccionPendiente.usuario_id'=>$usuario['id'],
					
					'not exists(select * from aprobaciones_transacciones ap where ap.transaccion_id=TransaccionPendiente.id and usuario_id=?)'=>$usuario['id']
				);
				
				// Busca las transacciones del usuario O con fecha vacia O mayor a la fecha actual
				$condiciones[]=array(
					'or'=> array(
						'TransaccionPendiente.usuario_id'=>$usuario['id'],
						'TransaccionPendiente.fecha_hora_valor is null',
						'TransaccionPendiente.fecha_hora_valor >= '=>date('Y-m-d')
					)
				);
				
				break;
		}
		
		$pendientes = $this->find(
			'all',
			array(
				'contain'=>array(
					'AprobacionesRealizadas'=>array('Usuario'),
					'UsuarioCreador',
					'Beneficiarios'
				),
				'conditions'=>$condiciones,
				'order'=>'TransaccionPendiente.fecha_hora_valor asc'
			)
		);
		
		$mapa_pendientes = array();	
		
		if($pendientes){
			foreach($pendientes as &$i){
				$this->analizaPermisos(
					$usuario,
					$i
				);
				$mapa_pendientes[$i['TransaccionPendiente']['id']] = $i;
			}
		}
		
		$x = $this->AprobacionTransaccion->find(
			'all',
			array(
				'fields'=>array(
					'AprobacionTransaccion.transaccion_id',
					'COUNT(*) as total'
				),
				'group'=>'AprobacionTransaccion.transaccion_id',
				'conditions'=>$condiciones
			)
		);
		
		foreach($x as $i){
			if(isset($mapa_pendientes[$i['AprobacionTransaccion']['transaccion_id']])){
				$mapa_pendientes[$i['AprobacionTransaccion']['transaccion_id']]['TransaccionPendiente']['aprobaciones']=$i[0]['total'];
			}
		}
		
		return $mapa_pendientes;
	}
	
	/**
	 * Analiza la transacción a ver si el usuario tiene permisos sobre ella
	 * marca banderas en el mismo arreglo de la transacción
	 * 
	 * @param array $usuario
	 * @param array $i
	 */
	function analizaPermisos($usuario, &$i){
		$this->TransaccionJuridicoDetalle = ClassRegistry::init('TransaccionJuridicoDetalle');

		// Usuarios aprobadores o operadores/aprobadores que no hayan creado la transacción
		if(
                    $usuario['rol_juridico_id']==4 ||
                    (
                        $usuario['rol_juridico_id']==5 &&
                        $i['TransaccionPendiente']['usuario_id'] != $usuario['id']
                    )
		) {
			$transaccion_detalle = false;
			$transaccion_detalle_m = false;
			$transaccion_detalle_o = false;
			
			// Usuarios aprobadores o operadores/aprobadores que no hayan creado la transacción
			switch($i['TransaccionPendiente']['tipo']){
				case 'tpm': //Transferencia propia mismo banco
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100021,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'ttm': //Transferencia tercero mismo banco
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100022,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'tpo': //Transferencia propia otro banco
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100023,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'tto': //Transferencia tercero otro banco
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100024,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'ppm': //Pago tarjeta propia mismo banco
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100025,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'ptm': //Pago tarjeta tercero mismo banco
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100026,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'ppo': //Pago tarjeta propia otro banco
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100027,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'pto': //Pago tarjeta tercero otro banco
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100028,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'nxm': //Pago de nómina banco activo
				        if($i['TransaccionPendiente'][nom_express]==1){
                                           $transa_id = '101107';  
				        }else{
				           $transa_id = '39';  	
				        }
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						$transa_id
					);
					break;
				case 'mxm': //Pago de nómina banco activo Moneda Extranjera
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						101072
					);
                                   
					break;                                    
				case 'nxo': //Pago de nómina otro banco
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100016
					);
					break;
				case 'nxa': //Pago de nómina mismo y otros bancos
				        if($i['TransaccionPendiente'][nom_express]==1){
                                           $transa_id = '101107';  
				        }else{
				           $transa_id = '39';  	
				        }				
					$transaccion_detalle_m = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						$transa_id
					);
					$transaccion_detalle_o = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100016
					);
					break;
				case 'vxm': //Pago de proveedores banco activo
				        if($i['TransaccionPendiente'][nom_express]==1){
                                           $transa_id = '101108';  
				        }else{
				           $transa_id = '40';  	
				        }
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						$transa_id
					);
					break;
				case 'xxm': //Pago de proveedores banco activo Me
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						101073
					);
					break;                                                                       
				case 'vxo': //Pago de proveedores otros bancos
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100017
					);
                    			if(!$transaccion_detalle){
						$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
							$usuario['id'],
							101107
						);
                    			}
                    			if(!$transaccion_detalle){
						$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
							$usuario['id'],
							101108
						);
                    			}



					break;
				case 'vxa': //Pago de proveedores mismo y otros banco
				        if($i['TransaccionPendiente'][nom_express]==1){
                                           $transa_id = '101108';  
				        }else{
				           $transa_id = '40';  	
				        }				
					$transaccion_detalle_m = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						$transa_id
					);
					$transaccion_detalle_o = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100017
					);

                    			if(!$transaccion_detalle){
						$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
							$usuario['id'],
							101107
						);
                    			}
                    			if(!$transaccion_detalle){
						$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
							$usuario['id'],
							101108
						);
                    			}
					
					break;
				case 'csi': //Suspensión de cheque individual
				case 'csx': //Suspensión de chequera
				case 'csr': //Suspensión de rango de cheques
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						22
					);
					if($transaccion_detalle) $transaccion_detalle['limite']= 0;
					break;
				case 'stm':
				case 'scn': //Pago Servicios MOVILNET
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100014,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'rcn': //Recaraga Servicios MOVILNET
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100015,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'ip ':
                                        $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100036,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'it ':
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100037,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;                                    
                                case 'scd': //Pago Servicios DIGITEL
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100014,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'rcd': //Recaraga Servicios DIGITEL
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100015,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
                                case 'ssi': //Recaraga Servicios Inter
                                        $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                                $usuario['id'],
                                                100014,
                                                desencriptar($i['TransaccionPendiente']['cuenta_origen'])
                                        );
                                        break;
                                case 'rsi': //Recaraga Servicios Inter
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100015,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
                                case 'scm': //Pago Servicios MOVISTAR
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100014,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
				case 'rcm': //Recaraga Servicios MOVISTAR
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						100015,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;    
					
				case 'rsv': //Recaraga Simple TV
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						102100,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;  
				case 'spc':
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						101102,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
                                case 'dpm': //Transferencia propia mismo banco Moneda extranjera
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						101070,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
					case 'dtm': //Transferencia tercero mismo banco Moneda extranjera
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						101071,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;				
                                case 'exc': //Mesa Cambio Compra
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						101074,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;
                                case 'exv': //Mesa Cambio Venta
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						101075,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;     
                                case 'fxc': //Intervencion Compra
					$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
						$usuario['id'],
						101088,
						desencriptar($i['TransaccionPendiente']['cuenta_origen'])
					);
					break;                                       
			}
			
			if(
				$transaccion_detalle_m &&
				$transaccion_detalle_o
			){
				$i['permiso_aprobacion'] = true;

				// Para transacciones mixtas hay que
				// separar totales mismo y otros bancos

				$monto_interna = 0;
				$monto_externa = 0;
				
				foreach($i['Beneficiarios'] as $b){
					// Verifica si es una cuenta interna o externa
					if(substr(desencriptar($b['numero_cuenta']),0,4)=='0171'){
						$monto_interna += $b['monto'];
					}
					else{
						$monto_externa += $b['monto'];
					}
				}
				
				if(
					is_numeric($transaccion_detalle_m['limite']) &&
					$transaccion_detalle_m['limite'] < $monto_interna
				) {
					// el usuario no puede aprobar o rechazar transacciones por este monto
					$i['permiso_aprobacion'] = false;
					$i['permiso_aprobacion_motivo']='limite';
				} elseif(
					is_numeric($transaccion_detalle_o['limite']) &&
					$transaccion_detalle_o['limite'] < $monto_externa
				) {
					// el usuario no puede aprobar o rechazar transacciones por este monto
					$i['permiso_aprobacion'] = false;
					$i['permiso_aprobacion_motivo']='limite';
				} else {
					foreach($i['AprobacionesRealizadas'] as $ar){
						if($ar['usuario_id']==$usuario['id']){
							// Si ya la aprobó, no puede otra vez
							$i['permiso_aprobacion'] = false;
							$i['permiso_aprobacion_motivo']='yaaprobo';
							break;
						}
					}
				}
			} elseif($transaccion_detalle){
				// Transacciones normales, es decir, no mixtas
			
				$i['permiso_aprobacion'] = true;
				
				if(
					is_numeric($transaccion_detalle['limite']) &&
					$transaccion_detalle['limite'] < $i['TransaccionPendiente']['monto']) {
					// el usuario no puede aprobar o rechazar transacciones por este monto
					$i['permiso_aprobacion'] = false;
					$i['permiso_aprobacion_motivo']='limite';
				} else {
					foreach($i['AprobacionesRealizadas'] as $ar){
						if($ar['usuario_id']==$usuario['id']){
							// Si ya la aprobó, no puede otra vez
							$i['permiso_aprobacion'] = false;
							$i['permiso_aprobacion_motivo']='yaaprobo';
							break;
						}
					}						
				}
			} else {
				$i['permiso_aprobacion'] = false;
				$i['permiso_aprobacion_motivo']='nopermiso';
			}
			
			// Para los pago de tdc hay que verificar también el permiso sobre la tarjeta
			
			if(
				$i['permiso_aprobacion'] &&
				$i['TransaccionPendiente']['tipo']=='ppm'
			){
				$transaccion_detalle_tdc = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					100025,
					desencriptar($i['TransaccionPendiente']['cuenta_destino']) // nro de tdc
				);
				
				if(!$transaccion_detalle_tdc){
					$i['permiso_aprobacion'] = false;
					$i['permiso_aprobacion_motivo']='nopermiso';
				} else if(
					is_numeric($transaccion_detalle_tdc['limite']) &&
					$transaccion_detalle_tdc['limite'] < $i['TransaccionPendiente']['monto']
				) {
					// el usuario no puede aprobar o rechazar transacciones por este monto
					$i['permiso_aprobacion'] = false;
					$i['permiso_aprobacion_motivo']='limite';
				}
			}
		}
	}
	
	
	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'cuenta_origen' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'cuenta_destino' => array(

		),
		'monto' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
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
	
	
	/**
	 * Verifica si esta repetido el número de lote.
	 * 
	 * @param int $lote
	 * @param int $transaccion
	 * @return boolean
	 */
	function checkLote($lote, $transaccion=''){
		
		App::import('Component', 'Session');
		
		$session = new SessionComponent();
		
		$juridico = $session->read('usuarios.datos');
		
		$conditions = array(
			'TransaccionPendiente.lote'=>$lote,
			'TransaccionPendiente.tipo_rif'=>$juridico['tipo_rif'],
			'TransaccionPendiente.rif'=>$juridico['rif'],
			"TransaccionPendiente.estatus not in ('el')"
		);
		
		if(!empty($transaccion)) $conditions['TransaccionPendiente.id <>'] = $transaccion;

		$x = $this->find( 'count', array('conditions'=>$conditions) );

		return $x==0;
	}
        
        
        /**
	 * Verifica si ha ejecutado una transaccion de alto valor durante el día
	 * 	 
	 * @param int $parametro parametro de alto valor
	 * @return int sumatoria de las transacciones de alto valor
	 */
	function transaccionesJuridicoAltoValor($parametro){
		
		App::import('Component', 'Session');
		
		$session = new SessionComponent();
		
		$juridico = $session->read('usuarios.datos');
                
		$sumatoria_nomprov = 0;
                
                $transacciones = $this->find(
			'all',
			array(
				'conditions'=>array(
                                        'TransaccionPendiente.rif ='=> $juridico['rif'],
                                        'TransaccionPendiente.estatus'=> array('ej', 'ep'),
                                        'LEFT(tipo,1)'=> array('n', 'v'),
					'fecha_hora_valor >='=> date('Y-m-d 00:00:00'),
					'fecha_hora_valor <='=> date('Y-m-d 23:59:59')
				)
			)
		);
                
                foreach($transacciones as $i){																	

			foreach($i['Beneficiarios'] as $b){
																														                                                                                    
                            if((substr(desencriptar($b['numero_cuenta']),0,4)!='0171') && ($b['monto']>=formatea_monto_toWs($parametro['MONTO'])) && (!$b['reversada'])){
                                $sumatoria_nomprov = $sumatoria_nomprov + $b['monto'];
                            }
                            				
			}			
		}
                
		return $sumatoria_nomprov;
	}


    function modificarSimpletv($id, $inf_simple){
 
        $this->updateAll(
            array(
               'inf_simpletv' => "'" . $inf_simple . "'",
                ),
            array(
                'id' => $id
            )
        );

        return true;
    } 



}
?>