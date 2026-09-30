<?php
/**
 * Controlador para mostrar las cuentas y sus detalles
 * 
 * @author Adverweb <http://www.adverweb.com>
 */

/**
 * Controlador para mostrar las cuentas y sus detalles
 * 
 */
class CuentasController extends AppController {
	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array('BusquedaCuenta','ExportarCuenta','DetalleProducto','Bitacora','TransaccionPendiente','Afiliacion');
	
	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */	
	var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');

	/**
	 * Acción que despliega el listado de cuentas del usuario
	**/
	function index(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(3)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		// para el regreso al index o el consolidado
		$this->Session->write('usuarios.volver_index', TRUE);
		
		$usuario = $this->Session->read('usuarios.datos');
		
		// Registra la consulta del producto
		$this->Bitacora->registraBitacora($usuario['id'], 3, null, null, null, null, "Consulta Listado de Cuentas");

		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$tipo_documento = $usuario['tipo_rif'];
			$documento = desencriptar($usuario['rif']);
		}
		else{
			$tipo_documento = $usuario['tipo_cedula'];
			$documento = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
		
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		
		$res = '';
		
		// Verifica si hay una respuesta
		if(isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])){
			$res = $this->Activo->invoca(
				'SUMDSJV',
				array(
					'req'=>array(
						'SUMACN'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],
						'SUMFIR'=>'X'
					)
				)
			);
		}
		
		$cuentas = array();
                $cuentas_moneda_extranjera = array();
                
		$totales = array(
			'SUMBAL'=>0,
			'SUMAVB'=>0
		);
                $totales_moneda_extranjera = array(
			'SUMBAL'=>0,
			'SUMAVB'=>0
		);

		if($res){
			$x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
			
			normaliza($x);
			
			foreach($x as $i){
				if(($i['SUMTYP']=='SAV' || $i['SUMTYP']=='DDA'  || $i['SUMTYP']=='NOW' || $i['SUMTYP']=='MMK') && ($i['SUMCCY']=='VEB')){
					
					// si es un usuario juridico verifica si tiene permiso en la cuenta
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 3, trim($i['SUMACN'])) ) continue;
					}
					
					$i['ALIAS'] = _a($usuario['id'], $i['SUMACN']);
					$cuentas[] = $i;
					$totales['SUMBAL'] += $i['SUMBAL'];
					$totales['SUMAVB'] += $i['SUMAVB'];
				}
                                else if($i['SUMCCY']!='VEB'){
					
					// si es un usuario juridico verifica si tiene permiso en la cuenta
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 3, trim($i['SUMACN'])) ) continue;
					}
					
                                        $i['ALIAS'] = _a($usuario['id'], $i['SUMACN']);
					$cuentas_moneda_extranjera[] = $i;
					$totales_moneda_extranjera['SUMBAL'] += $i['SUMBAL'];
					$totales_moneda_extranjera['SUMAVB'] += $i['SUMAVB'];
				}
			}
		}

		$this->set(compact('cuentas','totales','cuentas_moneda_extranjera','totales_moneda_extranjera'));

		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.cuentas');
	}

	/**
	 * Acción que despliega el detalle de la cuenta
	 * @param string $volver Indica si está regresando de la búsqueda
	**/
	function detalle($volver=''){
                
                set_time_limit(0);
                
		// para el regreso al index o el consolidado
		if( $this->Session->check('usuarios.volver_index') ){
			$ruta_volver = '/cuentas/';
		}
		else {
			$ruta_volver = '/consultas/integral';
		}
		$this->set('ruta_regreso', $ruta_volver);
		// para el regreso al index o el consolidado
		
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_cuenta')))){
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');
		
		// Registra la consulta del producto
		$this->Bitacora->registraBitacora($usuario['id'], 3, null, null, null, null, "Consulta Cuenta " . $this->data['DetalleProducto']['nro_cuenta']);

		$cuentas = $this->Activo->buscarCuentasConsulta();
		
		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
		
		$parametros_consulta = array(
			'req'=>array(
				'RSTACC'=>substr($this->data['DetalleProducto']['nro_cuenta'] ,10),
				'RSTCAF'=>'1'
			)
		);		

		$this->Session->write('cuentas.ultima_consulta',$parametros_consulta);

		$res = $this->Activo->invoca(
			'STMRDSJV',
			$parametros_consulta
		);
                                
                //Verifica el tipo de moneda

		$moneda = (trim($res['STMRDSJVReturn']['STMBDSJV']['STBCCY'])) == 'VEB' ? 'BS.' : $res['STMRDSJVReturn']['STMBDSJV']['STBCCY'];

		$totales = array(
			'ABONOS'=>0,
			'CREDITOS'=>0
		);

		$arrData = array();

		//		
		// Verifica que el webservice traiga registros
		if(!empty($res['STMRDSJVReturn']['STMSDSJV']['STMSDSJV'])){
			$arrData = $res['STMRDSJVReturn']['STMSDSJV']['STMSDSJV'];
			normaliza($arrData);
			$arrData = array_reverse($arrData);
		}
		
		// Totales
		if(count($arrData)){
			
			$contador = 0;
			foreach($arrData as $i){
				if($i['STMDCC']=='5'){
					$totales['ABONOS']+=$i['STMAMT'];
				} elseif($i['STMDCC']=='0'){
					$totales['CREDITOS']+=$i['STMAMT'];
				}

				// Verifica si es un dispositivo movil
				if(esMovil()){
					$contador++;
					$temp_arr[] = $i;
					if($contador>=Configure::read('cantidad.listado.movil.cuentas')) break;
				}
			}
			
			// Verifica si es un dispositivo movil
			if(esMovil()) $arrData = $temp_arr;
		}

		// Empieza código para paginar

		$total_registros = count($arrData);

		$offset = 0;
		
		if(!empty($volver)) $this->params['form']['offset'] = intval($this->Session->read('cuentas.offset'));

		if(
			isset($this->params['form']['offset']) &&
			preg_match('/^\d+$/',$this->params['form']['offset'])
		){
			$offset = intval($this->params['form']['offset']);			
		}

		$arrData = array_slice($arrData,
			$offset,
			Configure::read('Tamano.paginacion'),
			true
		);
		
		$this->Session->write('cuentas.offset', $offset);

		// Fin código para paginar

		$flag_buscar=false;
		
		$this->Session->delete('cuentas.flagBusqueda');
		
		$periodo = false;

		$alias = _a($usuario['id'], $this->data['DetalleProducto']['nro_cuenta']);
		
		$this->set(compact('res','cuentas','totales','total_registros','arrData','ruta_regreso','flag_buscar', 'alias','periodo', 'moneda'));

		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.cuentas');
	}

	/**
	 * Acción que despliega el detalle del movimiento
	**/
	function movimiento(){
		
                // setlocale(LC_ALL,'es_VE.utf8'); PARA LINUX
                setlocale(LC_ALL,'es_VE.UTF-8'); // PARA LINUX
                
		$flag_busqueda = $this->Session->check('cuentas.flagBusqueda');
                
		// en caso de venir de la busqueda avanzada
		if(isset($this->data['BusquedaCuenta'])){
			$this->data['DetalleProducto']= $this->data['BusquedaCuenta'];
			$flag_buscar = true;
		}
		else if($this->Session->check('cuentas.busqueda')){
			$data_busqueda = $this->Session->read('cuentas.busqueda');
			unset($data_busqueda['DetalleProducto']);
			$this->Session->write('cuentas.busqueda', $data_busqueda);
		}

		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);
			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_cuenta','referencia','tipo')))){
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$cuenta = substr($this->data['DetalleProducto']['nro_cuenta'] ,10);
		$referencia = str_pad($this->data['DetalleProducto']['referencia'],9,'0',STR_PAD_LEFT);	
		$monto = $this->data['DetalleProducto']['monto'];                
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		$identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
		
		$res = $this->Activo->invoca(
			'TINQDSJV2',
			array(
				'req'=>array(
					'TINUSR'=>$identificacion,
					'TINDACC'=>$cuenta,
					'TINTYP'=>'X',
					'TINATY'=>'I',
					'TINNUM'=>$referencia,
                                        'TINAMT'=>formatea_monto_toWs($monto)
				)
			)
		);

		//Para la consulta en transacciones pendientes, manejo de datos del lote
		$conditions = array(
			'TransaccionPendiente.tipo_rif' => $usuario['tipo_rif'],
			'TransaccionPendiente.rif' => $usuario['rif'],
			'TransaccionPendiente.lote' => $referencia,
		);
		
		$datosTransaccion = $this->TransaccionPendiente->find(
				'all',
				array(
					'fields'=>array('lote','fecha_hora_valor','descripcion'),
					'conditions'=>$conditions,
				)
			);
		
		
		if($res){
			$movimiento = $res['TINQDSJV2Return']['TINQDSJV2'];
			
			if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
				$movimiento['TINDACC'] = '';
			} else {			
				$completa = $this->Activo->completaCuenta($movimiento['TINDACC']);
				$movimiento['TINDACC'] = str_pad($completa['EDL890101Return']['EDL890101']['e01ACC020'], 20, '0', STR_PAD_LEFT);
			}
			
			if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){
				$movimiento['TINCACC'] = '';
			} else {			
				$completa = $this->Activo->completaCuenta($movimiento['TINCACC']);
				$movimiento['TINCACC'] = str_pad($completa['EDL890101Return']['EDL890101']['e01ACC020'], 20, '0', STR_PAD_LEFT);
			}
			
			$movimiento['CTA_DESTINO'] = '';
			$movimiento['CTA_ORIGEN'] = '';
			$movimiento['NOMBRE_ORIGEN'] = '';
			$movimiento['NOMBRE_DESTINO'] = '';
			$formato_identificacion= '';
			
			switch($this->data['DetalleProducto']['tipo']){
                                case 'AE':					
					$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
					$movimiento['NOMBRE_DESTINO'] = $movimiento['TINOC1'];
					$movimiento['TINBC3'] = intval($movimiento['TINBC3']);
                                        $movimiento['CTA_DESTINO'] = $movimiento['TINDACC'];
                                        $movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
                                        $movimiento['TINBC3'] = $usuario['tipo_cedula'].'-'.desencriptar($usuario['cedula']);
					break;
				case 'CI':					
					$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
					$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
					$movimiento['TINBC3'] = intval($movimiento['TINBC3']);
					if(empty($movimiento['TINBC4']) || $movimiento['TINBC4']=='0'){
						$movimiento['CTA_DESTINO'] = '';
					} else {
						$movimiento['CTA_DESTINO'] = $movimiento['TINBC4'];
					}
					
					if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
						$movimiento['CTA_ORIGEN'] = '';
					} else {
						$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
					}
					break;
				case 'WC':					
					if($movimiento['TINTYP']=='O'){
						$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINBC2'];
						$movimiento['NOMBRE_DESTINO'] = $movimiento['TINOC1'];
						$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));	
					
						if(empty($movimiento['TINBC1']) || $movimiento['TINBC1']=='0'){
							$movimiento['CTA_ORIGEN']='';
						} else {
							$movimiento['CTA_ORIGEN'] = str_pad($movimiento['TINBC1'], 20, '0', STR_PAD_LEFT);
						}
						
						if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){
							$movimiento['CTA_DESTINO'] = '';
						} else {					
							$movimiento['CTA_DESTINO'] = $movimiento['TINCACC'];
						}			
					} else {
						$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
						$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
						$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];

						if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
							$movimiento['CTA_ORIGEN'] = '';
						} else {
							$movimiento['CTA_ORIGEN'] = str_pad($movimiento['TINDACC'], 20, '0', STR_PAD_LEFT);
						}
						
						if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){
							$movimiento['CTA_DESTINO'] = '';
						} else {					
							$movimiento['CTA_DESTINO'] = $movimiento['TINCACC'];
						}							
					}
										
					if(0==strlen(trim($movimiento['TINBB1']))){
						$movimiento['TINBB1'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
					}
					
					if(strlen(trim($movimiento['TINBC3']))<3){
						$movimiento['TINBC3'] = $formato_identificacion;
					}
					
					if(empty($movimiento['NOMBRE_DESTINO'])){
						$movimiento['NOMBRE_DESTINO'] = desencriptar($usuario['nombre']);
					}
					
					break;
				case 'WD':
                                case 'CW':    
					if($movimiento['TINTYP']=='O'){
						$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
						$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
						$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
						
						if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
							$movimiento['CTA_ORIGEN']='';
						} else {
							$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
						}
						
						if(empty($movimiento['TINBC1']) || $movimiento['TINBC1']=='0'){
							$movimiento['CTA_DESTINO'] = '';
						} else {					
							$movimiento['CTA_DESTINO'] = $movimiento['TINBC1'];
						}			
					} else {
						$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
						$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
						$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
						
						if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
							$movimiento['CTA_ORIGEN'] = '';
						} else {					
							$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
						}	
						
						if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){  

						$movimiento['CTA_DESTINO'] =  $movimiento['TINBC4']; 
							 
						} else {
							 
							$movimiento['CTA_DESTINO'] = $movimiento['TINCACC']; 
						}
										
						if(0==strlen(trim($movimiento['TINBB1']))){
							$movimiento['TINBB1'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
						}
						
						if(empty($movimiento['NOMBRE_DESTINO'])){
							$movimiento['NOMBRE_DESTINO'] = desencriptar($usuario['nombre']);
						}						
					}
					break;
				case 'PS':
                                case 'DS':    
					$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
					$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
					$movimiento['TINBC3'] = intval($movimiento['TINBC3']);
					if(empty($movimiento['TINBC4']) || $movimiento['TINBC4']=='0'){
						$movimiento['CTA_DESTINO'] = '';
					} else {
						$movimiento['CTA_DESTINO'] = $movimiento['TINBC4'];
					}
					
					if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
						$movimiento['CTA_ORIGEN'] = '';
					} else {
						$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
					}
					break;

				case 'WN':
					if($movimiento['TINTYP']=='X'){
						$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
						$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
						//$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
						
						if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
							$movimiento['CTA_ORIGEN']='';
						} else {
							$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
						}
						
						if(empty($movimiento['TINBC1']) || $movimiento['TINBC1']=='0'){
							$movimiento['CTA_DESTINO'] = '';
						} else {					
							$movimiento['CTA_DESTINO'] = $movimiento['TINBC1'];
						}			
					} else {
						//$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
						$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
						$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
						
						if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
							$movimiento['CTA_ORIGEN'] = '';
						} else {					
							$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
						}	
						
						if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){
							$movimiento['CTA_DESTINO'] = '';
						} else {
							$movimiento['CTA_DESTINO'] = str_pad($movimiento['TINCACC'], 20, '0', STR_PAD_LEFT);
						}
										
						if(0==strlen(trim($movimiento['TINBB1']))){
							$movimiento['TINBB1'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
						}
						
						if(empty($movimiento['NOMBRE_DESTINO'])){
							$movimiento['NOMBRE_DESTINO'] = desencriptar($usuario['nombre']);
						}						
					}
					break;
				case 'PI':
                                        $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                                        if($usuario['tipo_rif']){
                                                $movimiento['NOMBRE_ORIGEN'] = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                                        } else {
                                                $movimiento['NOMBRE_ORIGEN'] = desencriptar($usuario['nombre']);
			}
                                        $movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
                                        $movimiento['CTA_DESTINO'] = $movimiento['TINIB1'];
                                        switch (substr($movimiento['CTA_DESTINO'],0,1)){
                                            case '1':
                                                    $movimiento['CTA_DESTINO'] = 'V'.substr($movimiento['CTA_DESTINO'],1);
                                                    break;
                                            case '2':
                                                    $movimiento['CTA_DESTINO'] = 'E'.substr($movimiento['CTA_DESTINO'],1);
                                                    break;
                                            case '3':
                                                    $movimiento['CTA_DESTINO'] = 'J'.substr($movimiento['CTA_DESTINO'],1);
                                                    break;
                                            case '4':
                                                    $movimiento['CTA_DESTINO'] = 'P'.substr($movimiento['CTA_DESTINO'],1);
                                                    break;
                                            case '5':
                                                    $movimiento['CTA_DESTINO'] = 'G'.substr($movimiento['CTA_DESTINO'],1);
                                                    break;
                                        }
                                        $movimiento['AUTORIZACION'] = trim($movimiento['TINIB2']);
                                        $movimiento['ADUANA'] = trim(substr($movimiento['TINOB4'],9));
                                        $movimiento['EXPEDIENTE'] = $movimiento['TINOC3'];
                                        $movimiento['NOMBREADUANA'] = $movimiento['TINBB5'].$movimiento['TINBBI2'];
                                        if($movimiento['ADUANA'] == '000000' || $movimiento['ADUANA'] == ''){
                                            $movimiento['AUTORIZACION'] = substr($movimiento['AUTORIZACION'],0,6);
                                        }
                                        $movimiento['FORMA'] = substr($movimiento['TINIB3'],10);
                                        $movimiento['IMPUESTO'] = $movimiento['TINOC4'];
                                        $movimiento['NOMBREIMPUESTO'] = $movimiento['TINBBI3'].$movimiento['TINBBI4'];
                                        $movimiento['PLANILLA'] = substr($movimiento['TINIB4'],5);
                                        if($usuario['tipo_rif']){
                                            $usuario_rif = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                                            $usuario_rif = substr($usuario_rif,0,9);
                                        }else{
                                            $usuario_rif = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                                        }
                                        $movimiento['DESCRIPCION'] = "Pago Impuestos Nacionales/Aduanales";
                                        if($usuario_rif == substr($movimiento['CTA_DESTINO'],0,9)){
                                            $movimiento['DESCRIPCION'] = $movimiento['DESCRIPCION'] . " - Mismo Titular";
                                        }else{
                                            $movimiento['DESCRIPCION'] = $movimiento['DESCRIPCION'] . " - Terceros";
                                        }
                                        break;                                   
			}
			
			// verifica si el detalle viene vacio
			if(isset($flag_buscar) && $movimiento['TINID1']==0 && $movimiento['TINID2']==0 && $movimiento['TINID3']==0){
				$this->data['DetalleProducto']['error'] = true;
				$this->Session->write('cuentas.busqueda', $this->data);
				$this->redirect('buscar/v');
			} else {
				// busca el tipo de la referencia en caso de una busqueda avanzada
				if(isset($flag_buscar)){
					
					$res = $this->Activo->invoca(
						'STMRDSJV',
						array(
							'req'=>array(

								'RSTACC'=>substr($this->data['DetalleProducto']['nro_cuenta'] ,10),
								'RSTCAF'=>'A',
								'RSTFAM'=>'00',
								'RSTFCK'=>'00',
								'RSTFD1'=>$movimiento['TINID1'],
								'RSTFD2'=>$movimiento['TINID2'],
								'RSTFD3'=>$movimiento['TINID3'],
								'RSTTAM'=>'00',
								'RSTTCK'=>'00',
								'RSTTD1'=>$movimiento['TINID1'],
								'RSTTD2'=>$movimiento['TINID2'],
								'RSTTD3'=>$movimiento['TINID3']
							)
						)
					);

					$arrData = array();

					// Verifica que el webservice traiga registros
					if($res){
					
						if(isset($res['STMRDSJVReturn']['STMSDSJV']['STMSDSJV'])){
							$arrData = $res['STMRDSJVReturn']['STMSDSJV']['STMSDSJV'];
							normaliza($arrData);
						} else {
							$arrData = array();
						}
					
						foreach($arrData as $i){
							if($this->data['DetalleProducto']['referencia'] == $i['STMCKN']){
                                                            if ($i['STMNA9'] == '05305'){
                                                                $this->data['DetalleProducto']['tipo'] = 'PI';
                                                            }else{
								$this->data['DetalleProducto']['tipo']= trim($i['STMCDE']);
                                                            }
								break;
							}
						}
					} else {
						$this->data['DetalleProducto']['tipo']='';
					}
				}
			}
		} else {
			$movimiento = array(
				'TINNUM'=>'',
				'TINID1'=>'',
				'TINID2'=>'',
				'TINID3'=>'',
				'TINAMT'=>'',
				'TINOBI1'=>'',
				'TINSID'=>'',
				'TINOC1'=>'',
				'TINBB1'=>'',
				'TINRID'=>'',
				'TINBC2'=>'',
				'TINBC1'=>'',
				'TINOBI2'=>'',
				'TINIMD'=>'',
				'CTA_DESTINO'=>'',
				'TINTYP'=>'',
			);
			
			$error = true;
			$this->data['DetalleProducto']['tipo']='';
		}
                if($movimiento['TINTYP']=='O' || $this->data['DetalleProducto']['tipo']=='PI'){
			$referencia = str_pad($this->data['DetalleProducto']['referencia'],9,'0',STR_PAD_LEFT);
		}else{
			$referencia = $this->data['DetalleProducto']['referencia'];
		}
                $movimiento['REFERENCIA'] = $referencia;

		$detalleLogs = $this->Bitacora->find(
			'first',
			array(
				'fields'=>array('descripcion','cuenta_destino','cuenta_origen','cedula','fecha_hora'),
				'conditions'=>array(
					'usuario_id'=>$usuario['id'],
					'referencia'=>encriptar($referencia)
				)
			),false
		);

		if($this->data['DetalleProducto']['tipo'] == 'PS' || $this->data['DetalleProducto']['tipo'] =='DS'){
			$usuario = $this->Session->read('usuarios.datos');
			
			$nombre_servicio = ereg_replace("[^a-zA-Z .]", "", $movimiento['TINOBI1']);
			$numero_servicio = ereg_replace("[^0-9]", "", $movimiento['TINOBI1']);
	
			$data_beneficiario = $this->Afiliacion->find(
				'first',
				array(
					'fields'=>array('nombre_beneficiario','tipo_afiliacion'),
					'conditions'=>array(
						'usuario_id'=>$usuario['id'],
						'numero_cuenta'=>encriptar($numero_servicio)
					)
				),false
			);
			
			if($data_beneficiario['Afiliacion']['tipo_afiliacion']== 's'){
				$descripcion = 'PAGO SERVICIO';
			}elseif($data_beneficiario['Afiliacion']['tipo_afiliacion'] == 'r' || $this->data['DetalleProducto']['tipo'] =='DS'){
				$descripcion = 'RECARGA SERVICIO';
			}else{
				$descripcion = '';
			}
		}
		$this->set(compact('cuentas','movimiento', 'flag_buscar', 'flag_busqueda','detalleLogs','buscarServicio','nombre_servicio','numero_servicio','data_beneficiario','descripcion','datosTransaccion','error'));

		$this->layout = 'interna';
		
		if( isset($this->data['DetalleProducto']['flagHT']) ){
			$this->set('seccion_actual','consultas.historico.transacciones');
		}
		else{
			$this->set('seccion_actual','consultas.cuentas');
		}
	}

	/**
	 * Acción que genera el detalle del movimiento en formato PDF
	**/
	function descargar_movimiento(){
		
                set_time_limit(0);
                if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_cuenta','referencia','tipo')))){
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$cuenta = substr($this->data['DetalleProducto']['nro_cuenta'] ,10);
		$referencia = str_pad($this->data['DetalleProducto']['referencia'],9,'0',STR_PAD_LEFT);	
                $monto = $this->data['DetalleProducto']['monto'];
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		$identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
		
		$res = $this->Activo->invoca(
			'TINQDSJV2',
			array(
				'req'=>array(
					'TINUSR'=>$identificacion,
					'TINDACC'=>$cuenta,
					'TINTYP'=>'X',
					'TINATY'=>'I',
					'TINNUM'=>$referencia,
                                        'TINAMT'=>formatea_monto_toWs($monto)
				)
			)
		);

		$movimiento = $res['TINQDSJV2Return']['TINQDSJV2'];
		
		//Para la consulta en transacciones pendientes, manejo de datos del lote
		$conditions = array(
			'TransaccionPendiente.tipo_rif' => $usuario['tipo_rif'],
			'TransaccionPendiente.rif' => $usuario['rif'],
			'TransaccionPendiente.lote' => $referencia,
		);
		
		$datosTransaccion = $this->TransaccionPendiente->find(
				'all',
				array(
					'fields'=>array('lote','fecha_hora_valor','descripcion'),
					'conditions'=>$conditions,
				)
			);
		
			
		if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
			$movimiento['TINDACC'] = '';
		} else {			
			$completa = $this->Activo->completaCuenta($movimiento['TINDACC']);
			$movimiento['TINDACC'] = str_pad($completa['EDL890101Return']['EDL890101']['e01ACC020'], 20, '0', STR_PAD_LEFT);
		}
		
		if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){
			$movimiento['TINCACC'] = '';
		} else {			
			$completa = $this->Activo->completaCuenta($movimiento['TINCACC']);
			$movimiento['TINCACC'] = str_pad($completa['EDL890101Return']['EDL890101']['e01ACC020'], 20, '0', STR_PAD_LEFT);
		}
		
		$movimiento['CTA_DESTINO'] = '';
		$movimiento['CTA_ORIGEN'] = '';
		$movimiento['NOMBRE_ORIGEN'] = '';
		$movimiento['NOMBRE_DESTINO'] = '';
		$formato_identificacion = '';
		
		switch($this->data['DetalleProducto']['tipo']){
                        case 'AE':					
                                $movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
                                $movimiento['NOMBRE_DESTINO'] = $movimiento['TINOC1'];
                                $movimiento['TINBC3'] = intval($movimiento['TINBC3']);
                                $movimiento['CTA_DESTINO'] = $movimiento['TINDACC'];
                                $movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
                                $movimiento['TINBC3'] = $usuario['tipo_cedula'].'-'.desencriptar($usuario['cedula']);
                                break;
			case 'CI':				
				$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
				$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
				$movimiento['TINBC3'] = intval($movimiento['TINBC3']);
				
				if(empty($movimiento['TINBC4']) || $movimiento['TINBC4']=='0'){
					$movimiento['CTA_DESTINO'] = '';
				} else {
					$movimiento['CTA_DESTINO'] = $movimiento['TINBC4'];
				}
				
				if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
					$movimiento['CTA_ORIGEN'] = '';
				} else {
					$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
				}
				break;
			case 'WC':	
				if($movimiento['TINTYP']=='O'){
					$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINBC2'];
					$movimiento['NOMBRE_DESTINO'] = $movimiento['TINOC1'];
					$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));	
					
					if(empty($movimiento['TINBC1']) || $movimiento['TINBC1']=='0'){
						$movimiento['CTA_ORIGEN']='';
					} else {
						$movimiento['CTA_ORIGEN'] = str_pad($movimiento['TINBC1'], 20, '0', STR_PAD_LEFT);
					}
					
					if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){
						$movimiento['CTA_DESTINO'] = '';
					} else {					
						$movimiento['CTA_DESTINO'] = $movimiento['TINCACC'];
					}			
				} else {
					$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
					$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
					$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];

					if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
						$movimiento['CTA_ORIGEN'] = '';
					} else {
						$movimiento['CTA_ORIGEN'] = str_pad($movimiento['TINDACC'], 20, '0', STR_PAD_LEFT);
					}
					
					if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){
						$movimiento['CTA_DESTINO'] = '';
					} else {					
						$movimiento['CTA_DESTINO'] = $movimiento['TINCACC'];
					}							
				}

				if(0==strlen(trim($movimiento['TINBB1']))){
					$movimiento['TINBB1'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
				}
				
				if(strlen(trim($movimiento['TINBC3']))<3){
					$movimiento['TINBC3'] = $formato_identificacion;
				}
					
				if(empty($movimiento['NOMBRE_DESTINO'])){
					$movimiento['NOMBRE_DESTINO'] = desencriptar($usuario['nombre']);
				}	
				
				break;
			case 'WD':
                        case 'CW':
				if($movimiento['TINTYP']=='O'){
					$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
					$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
					$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
					
					if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
						$movimiento['CTA_ORIGEN']='';
					} else {
						$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
					}
					
					if(empty($movimiento['TINBC1']) || $movimiento['TINBC1']=='0'){
						$movimiento['CTA_DESTINO'] = '';
					} else {					
						$movimiento['CTA_DESTINO'] = $movimiento['TINBC1'];
					}			
				} else {
					$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
					$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
					$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
					
					if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
						$movimiento['CTA_ORIGEN'] = '';
					} else {					
						$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
					}	
					
					if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){
						$movimiento['CTA_DESTINO'] = $movimiento['TINBC4']; 
					} else {
							$movimiento['CTA_DESTINO'] =  $movimiento['TINBC4']; 
						//$movimiento['CTA_DESTINO'] = str_pad($movimiento['TINCACC'], 20, '0', STR_PAD_LEFT);
					}

					if(0==strlen(trim($movimiento['TINBB1']))){
						$movimiento['TINBB1'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
					}
					
					if(empty($movimiento['NOMBRE_DESTINO'])){
						$movimiento['NOMBRE_DESTINO'] = desencriptar($usuario['nombre']);
					}						
				}
				break;
			case 'PS':
                        case 'DS':    
                                    $movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
                                    $movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
                                    $movimiento['TINBC3'] = intval($movimiento['TINBC3']);
                                    if(empty($movimiento['TINBC4']) || $movimiento['TINBC4']=='0'){
                                            $movimiento['CTA_DESTINO'] = '';
                                    } else {
                                            $movimiento['CTA_DESTINO'] = $movimiento['TINBC4'];
                                    }

                                    if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
                                            $movimiento['CTA_ORIGEN'] = '';
                                    } else {
                                            $movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
                                    }
                                    break;		
		
			case 'WN':
				if($movimiento['TINTYP']=='X'){
					$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
					$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
					//$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
					
					if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
						$movimiento['CTA_ORIGEN']='';
					} else {
						$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
					}
					
					if(empty($movimiento['TINBC1']) || $movimiento['TINBC1']=='0'){
						$movimiento['CTA_DESTINO'] = '';
					} else {					
						$movimiento['CTA_DESTINO'] = $movimiento['TINBC1'];
					}			
				} else {
					$movimiento['TINBC3'] = substr($movimiento['TINBC3'],0,1).'-'.intval(substr($movimiento['TINBC3'],1));
					$movimiento['NOMBRE_ORIGEN'] = $movimiento['TINOC1'];
					$movimiento['NOMBRE_DESTINO'] = $movimiento['TINBC2'];
					
					if(empty($movimiento['TINDACC']) || $movimiento['TINDACC']=='0'){
						$movimiento['CTA_ORIGEN'] = '';
					} else {					
						$movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
					}	
					
					if(empty($movimiento['TINCACC']) || $movimiento['TINCACC']=='0'){
						$movimiento['CTA_DESTINO'] = '';
					} else {
						$movimiento['CTA_DESTINO'] = str_pad($movimiento['TINCACC'], 20, '0', STR_PAD_LEFT);
					}

					if(0==strlen(trim($movimiento['TINBB1']))){
						$movimiento['TINBB1'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
					}
					
					if(empty($movimiento['NOMBRE_DESTINO'])){
						$movimiento['NOMBRE_DESTINO'] = desencriptar($usuario['nombre']);
					}						
				}
				break;
                        case 'PI':
                                $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                                if($usuario['tipo_rif']){
                                        $movimiento['NOMBRE_ORIGEN'] = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                                } else {
                                        $movimiento['NOMBRE_ORIGEN'] = desencriptar($usuario['nombre']);
		}
                                $movimiento['CTA_ORIGEN'] = $movimiento['TINDACC'];
                                $movimiento['CTA_DESTINO'] = $movimiento['TINIB1'];
                                switch (substr($movimiento['CTA_DESTINO'],0,1)){
                                    case '1':
                                            $movimiento['CTA_DESTINO'] = 'V'.substr($movimiento['CTA_DESTINO'],1);
                                            break;
                                    case '2':
                                            $movimiento['CTA_DESTINO'] = 'E'.substr($movimiento['CTA_DESTINO'],1);
                                            break;
                                    case '3':
                                            $movimiento['CTA_DESTINO'] = 'J'.substr($movimiento['CTA_DESTINO'],1);
                                            break;
                                    case '4':
                                            $movimiento['CTA_DESTINO'] = 'P'.substr($movimiento['CTA_DESTINO'],1);
                                            break;
                                    case '5':
                                            $movimiento['CTA_DESTINO'] = 'G'.substr($movimiento['CTA_DESTINO'],1);
                                            break;
                                }
                                $movimiento['AUTORIZACION'] = trim($movimiento['TINIB2']);
                                $movimiento['ADUANA'] = trim(substr($movimiento['TINOB4'],9));
                                $movimiento['EXPEDIENTE'] = $movimiento['TINOC3'];
                                $movimiento['NOMBREADUANA'] = $movimiento['TINBB5'].$movimiento['TINBBI2'];
                                if($movimiento['ADUANA'] == '000000' || $movimiento['ADUANA'] == ''){
                                    $movimiento['AUTORIZACION'] = substr($movimiento['AUTORIZACION'],0,6);
                                }
                                $movimiento['FORMA'] = substr($movimiento['TINIB3'],10);
                                $movimiento['IMPUESTO'] = $movimiento['TINOC4'];
                                $movimiento['NOMBREIMPUESTO'] = $movimiento['TINBBI3'].$movimiento['TINBBI4'];
                                $movimiento['PLANILLA'] = substr($movimiento['TINIB4'],5);
                                if($usuario['tipo_rif']){
                                    $usuario_rif = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                                    $usuario_rif = substr($usuario_rif,0,9);
                                }else{
                                    $usuario_rif = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                                }
                                $movimiento['DESCRIPCION'] = "Pago Impuestos Nacionales/Aduanales";
                                if($usuario_rif == substr($movimiento['CTA_DESTINO'],0,9)){
                                    $movimiento['DESCRIPCION'] = $movimiento['DESCRIPCION'] . " - Mismo Titular";
                                }else{
                                    $movimiento['DESCRIPCION'] = $movimiento['DESCRIPCION'] . " - Terceros";
                                }
                                break;                                   
                }
		
		if($movimiento['TINTYP']=='O' || $this->data['DetalleProducto']['tipo']=='PI'){
			$referencia = str_pad($this->data['DetalleProducto']['referencia'],9,'0',STR_PAD_LEFT);
		}else{
			$referencia = $this->data['DetalleProducto']['referencia'];
		}

                $movimiento['REFERENCIA'] = $referencia;
                
		$detalleLogs = $this->Bitacora->find(
			'first',
			array(
				'fields'=>array('descripcion','cuenta_destino','cuenta_origen','cedula','fecha_hora'),
				'conditions'=>array(
					'usuario_id'=>$usuario['id'],
					'referencia'=>encriptar($referencia)
				)
			),false
		);

		if($this->data['DetalleProducto']['tipo'] == 'PS' || $this->data['DetalleProducto']['tipo'] == 'DS'){
			$nombre_servicio = ereg_replace("[^a-zA-Z .]", "", $movimiento['TINOBI1']);
			$numero_servicio = ereg_replace("[^0-9]", "", $movimiento['TINOBI1']);

			$data_beneficiario = $this->Afiliacion->find(
				'first',
				array(
					'fields'=>array('nombre_beneficiario','tipo_afiliacion'),
					'conditions'=>array(
						'usuario_id'=>$usuario['id'],
						'numero_cuenta'=>encriptar($numero_servicio)
					)
				),false
			);
		
			if($data_beneficiario['Afiliacion']['tipo_afiliacion']== 's'){
				$descripcion = 'PAGO SERVICIO';
			}elseif($data_beneficiario['Afiliacion']['tipo_afiliacion'] == 'r' || $this->data['DetalleProducto']['tipo'] =='DS'){
				$descripcion = 'RECARGA SERVICIO';
			}else{
				$descripcion = '';
			}
		}
		$this->set(compact('movimiento','usuario','detalleLogs','datosTransaccion','buscarServicio','nombre_servicio','numero_servicio','data_beneficiario','descripcion'));

	}

	/**
	 * Acción para buscar movimientos de una cuenta
	 * @param string $volver Indica si está regresado del detalle
	**/
	function buscar($volver=''){
		$this->layout = 'interna';
		$this->set('seccion_actual','consultas.cuentas');
		if($this->Session->check('cuentas.busqueda') && !empty($volver)){
			$this->data = $this->Session->read('cuentas.busqueda');
			$this->Session->delete('cuentas.busqueda');
		}

		if(!empty($this->data['DetalleProducto']['nro_cuenta'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_cuenta')))){
				$this->redirect('index');
			}

			$this->data['BusquedaCuenta']['nro_cuenta'] = $this->data['DetalleProducto']['nro_cuenta'];
		} elseif(!empty($this->data['BusquedaCuenta'])){
			$this->BusquedaCuenta->set($this->data);
			$this->data['DetalleProducto'] = $this->data['BusquedaCuenta'];

			if($this->BusquedaCuenta->validates()){
				// WS

				// Usuario
				$usuario = $this->Session->read('usuarios.datos');
				$referencia ='';
				$flagReferencia = '0';
				// Verifica si ocurre un error

				$cuentas = $this->Activo->buscarCuentasConsulta();
				$totales = array( 'ABONOS'=>0, 'CREDITOS'=>0 );
				$arrData = array();
				$arreglo = array();
				$arrDataRef = array();
				
				// Tipo de consulta
				$tipo_consulta = 'A';
				
				if($this->data['BusquedaCuenta']['tipoBusq']=='t'){
					switch ($this->data['BusquedaCuenta']['periodo']){

						case 'o':
							// Desde
							$dd = $this->data['BusquedaCuenta']['fechaDesde']['day'];
							$md = $this->data['BusquedaCuenta']['fechaDesde']['month'];
							$ad = $this->data['BusquedaCuenta']['fechaDesde']['year'];
							$ad = substr($ad, 2);

							// Hasta
							$dh = $this->data['BusquedaCuenta']['fechaHasta']['day'];
							$mh = $this->data['BusquedaCuenta']['fechaHasta']['month'];
							$ah = $this->data['BusquedaCuenta']['fechaHasta']['year'];
							$ah = substr($ah, 2);
							break;

						case 'n':
							if (
								empty($this->data['BusquedaCuenta']['montoDesde']) && 
								empty($this->data['BusquedaCuenta']['montoHasta']) 
							){
								$tipo_consulta = '3';

								// Desde
								$dd = '00';
								$md = '00';
								$ad = '00';

								// Hasta
								$dh = '00';
								$mh = '00';
								$ah = '00';
							}
							else{
								// Desde
								$dd = '01';
								$md = date('m',strtotime('-1 month'));
								$ad = date('y',strtotime('-1 month'));

								// Hasta
								$dh = date('t',strtotime('-1 month'));
								$mh = date('m',strtotime('-1 month'));
								$ah = date('y',strtotime('-1 month'));
							}
							break;
						case 'u':
							$t1 = strtotime('-1 year');

							// Desde
							$dd = date("d", $t1);
							$md = date("m", $t1);
							$ad = date("y", $t1);

							// Hasta
							$dh = date("d");
							$mh = date("m");
							$ah = date("y");
							break;

						case 'a':
							if (empty($this->data['BusquedaCuenta']['montoDesde']) && empty($this->data['BusquedaCuenta']['montoHasta']) ) $tipo_consulta = '';
						default:
							// Desde
							$dd = '01';
							$md = date('m');
							$ad = date('y');

							// Hasta
							$dh = date('d');
							$mh = date('m');
							$ah = date('y');
							break;

					}

				} else {
					$referencia = str_pad($this->data['BusquedaCuenta']['referencia'],9, 0, STR_PAD_LEFT);
					$flagReferencia = '1';
					$dd = '00';
					$md = '00';
					$ad = '00';
					$dh = '00';
					$mh = '00';
					$ah = '00';
				}
				
				$montodesde = (empty($this->data['BusquedaCuenta']['montoDesde'])) ? '00' : formatea_monto_toWs($this->data['BusquedaCuenta']['montoDesde']);
				$montohasta = (empty($this->data['BusquedaCuenta']['montoHasta'])) ? '00' : formatea_monto_toWs($this->data['BusquedaCuenta']['montoHasta']);
				
				
				/** Para identificar si es un usuario juridico **/
				if($usuario['tipo_rif']){
					$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
				}
				else{
					$identificacion = desencriptar($usuario['cedula']);
				}
				/** Para identificar si es un usuario juridico **/
				/**
				 * Bloque de validación de la cuenta
				 */
				$accToSearch = $this->data['BusquedaCuenta']['nro_cuenta'];

				// Sanitizar el input para evitar inyección de código
				$input_sanitized = htmlspecialchars($accToSearch, ENT_QUOTES, 'UTF-8');

				// Verificar si el dato de entrada existe como clave en el array
				if (array_key_exists($input_sanitized, $cuentas)) {
					$accToSearch = substr($accToSearch ,10);
				} else {
					$accToSearch = '';
					$this->redirect('index');
				}		
				$parametros_consulta = array(
					'req'=>array(

						'RSTACC'=>substr($this->data['BusquedaCuenta']['nro_cuenta'] ,10),
						'RSTCAF'=>$tipo_consulta,
						'RSTFAM'=>$montodesde,
						'RSTFCK'=>'00',
						'RSTFD1'=>$dd,
						'RSTFD2'=>$md,
						'RSTFD3'=>$ad,
						'RSTTAM'=>$montohasta,
						'RSTTCK'=>'00',
						'RSTTD1'=>$dh,
						'RSTTD2'=>$mh,
						'RSTTD3'=>$ah,
						'RSTFBL'=>$referencia,
						'RSTFRC'=>$flagReferencia
					)
				);
				
				$this->Session->write('cuentas.ultima_consulta',$parametros_consulta);
				$this->Session->write('cuentas.busqueda',$this->data);

				// Invocacion del webservices para el detalle de las cuentas
				$res = $this->Activo->invoca(
					'STMRDSJV',
					$parametros_consulta
				);
				
				if($res){

					$tMoneda = trim($res['STMRDSJVReturn']['STMBDSJV']['STBCCY']);
					$moneda = ($tMoneda) =='VEB' ? 'BS.' : $tMoneda; 
                                    
                                        //Verifica el tipo de moneda
                                        // if($res['STMRDSJVReturn']['STMBDSJV']['STBCCY']=='VEB'){
                                        //     $moneda = 'Bs.';
                                        // }else if ($res['STMRDSJVReturn']['STMBDSJV']['STBCCY']=='USD'){
                                        //     $moneda = '$';
                                        // }else{
                                        //     $moneda = 'Bs.';
                                        // }
                                        
					// Verifica que el webservice traiga registros
					if( isset($res['STMRDSJVReturn']['STMSDSJV']['STMSDSJV'])){

						$arrData = $res['STMRDSJVReturn']['STMSDSJV']['STMSDSJV'];

						normaliza($arrData);

							foreach($arrData as $i){

								if($i['STMDCC']=='5'){
									$totales['ABONOS']+=$i['STMAMT'];
								} elseif($i['STMDCC']=='0'){
									$totales['CREDITOS']+=$i['STMAMT'];
								}
								
								$arreglo[] = $i;

							}

							$arrData = $arreglo;
						
					}
				}

				// Inidica a donde va regresar
				$ruta_regreso = '/cuentas/buscar';

				// Indica que esta desde el buscador
				$flag_buscar = true;
				$this->Session->write('cuentas.flagBusqueda', $flag_buscar);
				
				
				$periodo = ( (isset($this->data['DetalleProducto']['periodo']) && ($this->data['DetalleProducto']['periodo'] == 'u' || $this->data['DetalleProducto']['periodo'] == 'o')) || $this->data['DetalleProducto']['tipoBusq'] == 'f');
				if($periodo){
					$flagSaldo = true;
				}

				// Empieza código para paginar

				$total_registros = count($arrData);

				$offset = 0;
				
				if(!empty($volver)) $this->params['form']['offset'] = intval($this->Session->read('cuentas.offset'));
				
				if(
					isset($this->params['form']['offset']) &&
					preg_match('/^\d+$/',$this->params['form']['offset'])
				){
					$offset = intval($this->params['form']['offset']);			
				}

				$arrData = array_slice(array_reverse($arrData),
					$offset,
					Configure::read('Tamano.paginacion'),
					true
				);
				
				
				$this->Session->write('cuentas.offset', $offset);
				
				// Fin código para paginar

				$alias = _a($usuario['id'], $this->data['BusquedaCuenta']['nro_cuenta']);
				
				$this->set(compact('res','cuentas','totales', 'arrData', 'total_registros', 'ruta_regreso', 'flag_buscar','alias','periodo', 'flagSaldo','moneda'));

				$this->render('detalle');
			}
		} else {
			$this->redirect('index');
		}
	}

	/**
	 * Acción que despliega el saldo diferido y los movimientos correspondientes
	 * de la cuenta
	**/
	function diferido(){
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_cuenta')))){
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$cuenta = substr($this->data['DetalleProducto']['nro_cuenta'] ,10);

		$cuentas = $this->Activo->buscarCuentasConsulta();
	
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		$identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
		
		$res = $this->Activo->invoca(
			'EDD0395DS',
			array(
				'req'=>array(
					'e01UNCACC'=>$cuenta,
					'h01USERID'=>$identificacion

				)
			)
		);
		
		$diferido = array();
		
		if($res){
			$x = $res['EDD0395DSReturn']['EDD0395DS']['EDD0395DS'];

			$x = array_reverse($x);
			
			normaliza($x);

			foreach($x as $i){
				if(0==strlen(trim($i['e01UNCFLG']))){				
					$diferido[] = $i;
				}
			}
		}

		$this->set(compact('cuenta','diferido','cuentas'));

		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.cuentas');	
	}

	/**
	 * Acción que despliega el saldo retenido y los movimientos correspondientes
	 * de la cuenta
	**/
	function retenido(){
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_cuenta')))){
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$cuenta = substr($this->data['DetalleProducto']['nro_cuenta'] ,10);
		
		$cuentas = $this->Activo->buscarCuentasConsulta();
	
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		$identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
		
		$res = $this->Activo->invoca(
			'EDD0395DS',
			array(
				'req'=>array(
					'e01UNCACC'=>$cuenta,
					'h01USERID'=>$identificacion
				)
			)
		);

		$retenido = array();
		
		if($res){

			if(isset($res['EDD0395DSReturn']['EDD0395DS']['EDD0395DS'])){
			$x = $res['EDD0395DSReturn']['EDD0395DS']['EDD0395DS'];

			$x = array_reverse($x);
			
			normaliza($x);
			
			foreach($x as $i){
				if($i['e01UNCFLG'] =='H' || $i['e01UNCFLG'] == 'C'){				
					$retenido[] = $i;
				}
			}
		}
		}

		$this->set(compact('cuenta','retenido','cuentas'));

		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.cuentas');	
	}

	/**
	 * Acción para consultar movimientos históricos de las cuentas
	**/
	function historial(){
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_cuenta')))){
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		/**
		 * Bloque de validación de la cuenta
		*/
		$cuentas = $this->Activo->buscarCuentasConsulta();
		$accToSearch = $this->data['DetalleProducto']['nro_cuenta'];

		// Sanitizar el input para evitar inyección de código
		$input_sanitized = htmlspecialchars($accToSearch, ENT_QUOTES, 'UTF-8');

		// Verificar si el dato de entrada existe como clave en el array
	        if (array_key_exists($input_sanitized, $cuentas)) {
			$accToSearch = substr($accToSearch ,10);
		} else {
			$accToSearch = '';
			$this->redirect('index');
		}
			
		if(isset($this->data['DetalleProducto']['data_exportar']) && $this->data['DetalleProducto']['data_exportar']=='V' ){
		
			$this->Session->write('usuarios.cuenta.historico', $this->data);
			
			$this->redirect('exportar_historico');
			
			exit();
		}
			
		$opciones = $this->meses_estado_cuenta($this->data['DetalleProducto']['nro_cuenta']);

		$this->set(compact('opciones'));

		$usuario = $this->Session->read('usuarios.datos');

		if(isset($this->data['DetalleProducto']['fecha_edo_cuenta'])){
			$t1 = intval($this->data['DetalleProducto']['fecha_edo_cuenta']);
			
			/** Para identificar si es un usuario juridico **/
			if($usuario['tipo_rif']){
				$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
			}
			else{
				$identificacion = desencriptar($usuario['cedula']);
			}
			/** Para identificar si es un usuario juridico **/

			$res = $this->Activo->invoca(
				'IIN091801',
				array(
					'req'=>array(

						'i01ACMACC'=>$accToSearch,
						'h01OPECOD'=>'0001',
						'i01STADT1'=>date('d',$t1),
						'i01STADT2'=>date('m',$t1),
						'i01STADT3'=>date('y',$t1)
					)
				)
			);

			$totales = array(
				'i01CREDIT'=>0,
				'i01DEBITS'=>0
			);

			$arrData = array();

			if($res){
				$arrData = $res['IIN091801Return']['IIN091801']['IIN091801'];
				
				normaliza($arrData);

				foreach($arrData as $i){
					$totales['i01CREDIT']+=$i['i01CREDIT'];
					$totales['i01DEBITS']+=$i['i01DEBITS'];
				}
			} else {
				$arrData=array(array(
					'i01ACMCUN'=>'',
					'i01CUSNA1'=>'',
					'i01STADT1'=>'',
					'i01STADT2'=>'',
					'i01STADT3'=>'',
					'i01CUSNA2'=>'',
					'i01CUSNA3'=>'',
					'i01CUSNA4'=>'',
					'i01BEGBAL'=>'',
					'i01ACMACC'=>$accToSearch,
				));
			}
			
			$nro_cuenta = $accToSearch;
			
			$this->set(compact('arrData','totales','usuario','nro_cuenta'));

			$this->layout='nada';

			$this->render( ($this->data['DetalleProducto']['data_exportar']=='M') ? 'edo_cuenta_mt940' : 'edo_cuenta');

		} else {
			
			$cuentas = $this->Activo->buscarCuentasConsulta();

			$this->set('cuentas', $cuentas);
			
			$this->layout = 'interna';

			$this->set('seccion_actual','consultas.cuentas');
		}
	}

	/**
	 * Función utilitaria que genera el listado de meses de estados de cuenta
	 * disponibles para una cuenta
	 * @param string $cuenta Número de cuenta
	 * return array Arreglo asociativo donde la clave es la fecha en milisegundos y el valor
	 * el nombre del mes seguido del año
	**/
	function meses_estado_cuenta($cuenta){

		$usuario = $this->Session->read('usuarios.datos');

		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/

		$res = $this->Activo->invoca(
			'IIN091801',
			array(
				'req'=>array(

					'i01ACMACC'=>substr(trim($cuenta),10),
					'h01OPECOD'=>'0000'
				)
			)
		);	

		$meses = array();
		$x= array();
		
		if($res){ 
	
			
			if(isset($res['IIN091801Return']['IIN091801']['IIN091801'])){
				// Verifica si trae un solo registro o varios
				
				$x = $res['IIN091801Return']['IIN091801']['IIN091801'];
				
				normaliza($x);
				
				$x = array_slice(array_reverse($x),0,11);
			}
		

			foreach($x as $i){
				$t = mktime(0,0,0,$i['i01STADT2'],$i['i01STADT1'],$i['i01STADT3']);
				setlocale(LC_ALL,'es_VE.UTF-8');
				$meses[$t] = ucwords(strftimeAW('%B %Y', $t));
			}
		} else {
			$meses = array(
				''=>_m('error.fatal')
			);
		}

		if(empty($meses)){
			$meses = array(
				''=>'No hay registros que desplegar'
			);
		}

		if( $this->RequestHandler->isAjax()){
			$this->layout = 'ajax';
			$this->set(compact('meses'));
		} else {
			return $meses;
		}
	}

	/**
	 * Acción para exportar movimientos de cuenta
	**/
	function exportar(){
		if(!$this->Session->check('cuentas.ultima_consulta')) {
			$this->redirect('index');
		}

		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.cuentas');	

		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_cuenta')))){
				$this->redirect('index');
			}

			$this->data['ExportarCuenta']['nro_cuenta'] = $this->data['DetalleProducto']['nro_cuenta'];

		} else if(!empty($this->data['ExportarCuenta'])){
			$this->ExportarCuenta->set($this->data);
			
			$fields = array(
				'campos',
				'formato',
				'configuraciones',
				'divisionCampos'
			);
			
			if(0<count(array_intersect(array('m','s'),$this->data['ExportarCuenta']['campos']))){
				$fields[]='formatoMonto';
			}
			
			if(in_array('f',$this->data['ExportarCuenta']['campos'])){
				$fields[]='formatoFecha';
			}
			
			if(in_array('fp',$this->data['ExportarCuenta']['campos'])){
				$fields[]='formatoFecha';
			}

			if($this->ExportarCuenta->validates(array('fieldList'=>$fields))){
				$parametros_consulta = $this->Session->read('cuentas.ultima_consulta');
				$referencia = $this->Session->read('cuentas.busqueda_referencia');

				$res = $this->Activo->invoca(
					'STMRDSJV',
					$parametros_consulta
				);

				$arrData = array();
				$arreglo = array();
				
				if(isset($res['STMRDSJVReturn']['STMSDSJV']['STMSDSJV'])){
					$arrData = $res['STMRDSJVReturn']['STMSDSJV']['STMSDSJV'];
					
					normaliza($arrData);
				}

				$totales = array('ABONOS'=>0,'CREDITOS'=>0);

				// Totales
				if(count($arrData)){
					
					foreach($arrData as $i){
						
						if( !empty($referencia) && (stristr($referencia, $i['STMCKN']) === FALSE) ) continue;
						
						if($i['STMDCC']=='5'){
							$totales['ABONOS']+=$i['STMAMT'];
						} elseif($i['STMDCC']=='0'){
							$totales['CREDITOS']+=$i['STMAMT'];
						}
						
						if(!empty($referencia)) $arreglo[] = $i;
					}
					
					if(!empty($referencia)) $arrData = $arreglo;
				}

				$movimientos = array();

				if(is_array($this->data['ExportarCuenta']['campos'])){
					for($i = 0; $i < count($arrData); $i++){
						for($c=0;$c<count($this->data['ExportarCuenta']['campos']);$c++){
							$campo = $this->data['ExportarCuenta']['campos'][$c];

							switch($campo){
								case 's':
									$movimientos[$i][$c] = formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],$arrData[$i]['STMEBL']);
									
									if($this->data['ExportarCuenta']['formato']=='e') $movimientos[$i][$c] .= ' ';
									else $movimientos[$i][$c] .= '    ';
									
									break;
								case 'm':
									$movimientos[$i][$c] = formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],$arrData[$i]['STMAMT']);
									
									if($this->data['ExportarCuenta']['formato']=='e') $movimientos[$i][$c] .= ' ';
									else $movimientos[$i][$c] .= '    ';
									
									break;
								case 'c':
									$movimientos[$i][$c] = $arrData[$i]['STMCDE'];

									if($this->data['ExportarCuenta']['formato']=='e') $movimientos[$i][$c] .= ' ';									
									
									break;
								case 'f':
									$movimientos[$i][$c] = formato_fecha_exportar($this->data['ExportarCuenta']['formatoFecha'],$arrData[$i]['STMBD1'],$arrData[$i]['STMBD2'],$arrData[$i]['STMBD3']);
									break;
								case 'fp':
									$movimientos[$i][$c] = formato_fecha_exportar($this->data['ExportarCuenta']['formatoFecha'],trim($arrData[$i]['STMNA8']),trim($arrData[$i]['STMNA7']),trim($arrData[$i]['STMNA6']));
									break;	
								case 'r':
									$movimientos[$i][$c] = strval($arrData[$i]['STMCKN']);

									if($this->data['ExportarCuenta']['formato']=='e') $movimientos[$i][$c] .= ' ';
									else $movimientos[$i][$c] .= '    ';
									
									break;
								case 'd':
									$movimientos[$i][$c] = trim($arrData[$i]['STMNAR']);
									break;
								case 't':
									if($arrData[$i]['STMDCC']=='0'){
										$movimientos[$i][$c] = 'DEBITO';
									} elseif($arrData[$i]['STMDCC']=='5'){
										$movimientos[$i][$c] = 'CREDITO';
									}
									break;
							}
						}
					}
				}

				$this->set(compact('movimientos'));

				switch($this->data['ExportarCuenta']['formato']){
					case 't':
						$this->layout = 'nada';
						$this->render('exportar_txt');
						break;
					case 'e':
						$this->render('exportar_excel');						
						break;
					case 's':
						break;
				}
			}
		} else {
			$this->redirect('index');
		}
	}

	/**
	 * Acción para exportar movimientos históricos
	**/
	function exportar_historico(){
		
		if(!$this->Session->check('usuarios.cuenta.historico')){
			$this->redirect('index');
		}
				
		$tempdata = $this->Session->read('usuarios.cuenta.historico');
		
		if( isset($this->data['ExportarCuenta']) ){
			$this->data = array_merge($tempdata, $this->data);
		}
		else{
			$this->data = $tempdata;
		}
		
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_cuenta')))){
				$this->redirect('index');
			}
		} 

		$this->data['ExportarCuenta']['nro_cuenta'] = $this->data['DetalleProducto']['nro_cuenta'];

		$usuario = $this->Session->read('usuarios.datos');
				
		if(isset($this->data['ExportarCuenta']['formato'])){
		
			$t1 = intval($this->data['DetalleProducto']['fecha_edo_cuenta']);

			/** Para identificar si es un usuario juridico **/
			if($usuario['tipo_rif']){
				$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
			}
			else{
				$identificacion = desencriptar($usuario['cedula']);
			}
			/** Para identificar si es un usuario juridico **/

			$res = $this->Activo->invoca(
				'IIN091801',
				array(
					'req'=>array(

						'i01ACMACC'=>substr($this->data['DetalleProducto']['nro_cuenta'] ,10),
						'h01OPECOD'=>'0001',
						'i01STADT1'=>date('d',$t1),
						'i01STADT2'=>date('m',$t1),
						'i01STADT3'=>date('y',$t1)
					)
				)
			);

			$totales = array(
				'i01CREDIT'=>0,
				'i01DEBITS'=>0
			);

			$arrData = array();

			if($res){
				$arrData = $res['IIN091801Return']['IIN091801']['IIN091801'];
				
				normaliza($arrData);

				foreach($arrData as $i){
					$totales['i01CREDIT']+=$i['i01CREDIT'];
					$totales['i01DEBITS']+=$i['i01DEBITS'];
				}
			} else {
				$arrData=array(array(
					'i01ACMCUN'=>'',
					'i01CUSNA1'=>'',
					'i01STADT1'=>'',
					'i01STADT2'=>'',
					'i01STADT3'=>'',
					'i01CUSNA2'=>'',
					'i01CUSNA3'=>'',
					'i01CUSNA4'=>'',
					'i01BEGBAL'=>'',
					'i01ACMACC'=>'',
				));
			}
			
			$this->set(compact('arrData','totales','usuario','ruta_regreso'));

			switch($this->data['ExportarCuenta']['formato']){
					case 't':
						$this->layout = 'nada';
						$this->render('edo_cuenta_txt');
						break;
					case 'e':
						$this->render('edo_cuenta_xls');						
						break;
					case 's':
						break;
				}

		} else {
			
			$cuentas = $this->Activo->buscarCuentasConsulta();

			$this->set('cuentas', $cuentas);
			
			$this->layout = 'interna';

			$this->set('seccion_actual','consultas.cuentas');
		}
	}


	function consolidado_digital(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(101110)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		// para el regreso al index o el consolidado
		$this->Session->write('usuarios.volver_index', TRUE);
		
		$usuario = $this->Session->read('usuarios.datos');

       $validacion_otp = $this->Session->read('seguridad.validar_otp');
        if (
                (
                $validacion_otp == null ||
                !isset($validacion_otp['validado']) ||
                !$validacion_otp['validado']
                )
        ) {

            $url_volver = '/consultas/integral';
            $pasos = array();
            

            $this->Session->write(
                'seguridad.validar_otp', array(
                'url_aceptar' => '/cuentas/consolidado_digital',
                'url_volver' => $url_volver,
                'layout' => 'interna',
                'titulo' => 'Consolidado Dígital',
                'pasos' => $pasos,
                'paso_actual' => 0,
                'seccion_actual' => 'consolidado.digital'
                    )
            );
            $this->redirect('/seguridad/genera_otp');
        }



		// Registra la consulta del producto
		$this->Bitacora->registraBitacora($usuario['id'], 3, null, null, null, null, "Consulta Listado de Cuentas 20 Dígitos");

		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$tipo_documento = $usuario['tipo_rif'];
			$documento = desencriptar($usuario['rif']);
		}
		else{
			$tipo_documento = $usuario['tipo_cedula'];
			$documento = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
		
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		
		$res = '';
		
		// Verifica si hay una respuesta
		if(isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])){
			$res = $this->Activo->invoca(
				'SUMDSJV',
				array(
					'req'=>array(
						'SUMACN'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],
						'SUMFIR'=>'X'
					)
				)
			);
		}
		
		$cuentas = array();
                $cuentas_moneda_extranjera = array();
                
		$totales = array(
			'SUMBAL'=>0,
			'SUMAVB'=>0
		);
                $totales_moneda_extranjera = array(
			'SUMBAL'=>0,
			'SUMAVB'=>0
		);

		if($res){
			$x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
			
			normaliza($x);
			
			foreach($x as $i){

				if($i['SUMAST']=='A'){
					if(($i['SUMTYP']=='SAV' || $i['SUMTYP']=='DDA'  || $i['SUMTYP']=='NOW' || $i['SUMTYP']=='MMK') && ($i['SUMCCY']=='VEB')){
				
						// si es un usuario juridico verifica si tiene permiso en la cuenta
						if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 3, trim($i['SUMACN'])) ) continue;
						}
					
						$i['ALIAS'] = _a($usuario['id'], $i['SUMACN']);
						$cuentas[] = $i;
						//$totales['SUMBAL'] += $i['SUMBAL'];
						//$totales['SUMAVB'] += $i['SUMAVB'];
					}
                	else if($i['SUMCCY']!='VEB'){
					
						// si es un usuario juridico verifica si tiene permiso en la cuenta
						if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
							if( !permisoCuenta($usuario['id'], 3, trim($i['SUMACN'])) ) continue;
						}
					    $i['ALIAS'] = _a($usuario['id'], $i['SUMACN']);
						$cuentas_moneda_extranjera[] = $i;
						//$totales_moneda_extranjera['SUMBAL'] += $i['SUMBAL'];
						//$totales_moneda_extranjera['SUMAVB'] += $i['SUMAVB'];
					}
				}	

			}
		}






		$this->set(compact('cuentas','totales','cuentas_moneda_extranjera','totales_moneda_extranjera'));

		$this->layout = 'interna';




		$this->set('seccion_actual','consolidado.digital');
	}






}
?>