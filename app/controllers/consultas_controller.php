<?php
/**
 *Controlador para la consulta de productos, histórico de conexiones, transacciones 
 */
 
/**
 *Controlador para la consulta de productos, histórico de conexiones, transacciones 
 */
class ConsultasController extends AppController {

    /**
    *
    * @var array Modelos que usa el controlador 
    */
	var $uses = array(
		'Bitacora',
		'Usuario',
		'ExportarCuenta',
		'Modulo',
		'Transaccion',
		'HistoricoTransacciones',
		'Mensajes',
		'Canal',
		'RolJuridico',
                'Banners'
	);

    /**
    *
    * @var array Componentes que utiliza este controlador
    */
	var $components = array('Activo', 'Session', 'RequestHandler', 'Credicard');

    /**
    *
    * @var array Ayudantes utilizados en las vistas de este controlador
    */
	var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');
    

    /**
     * Acción para la consulta del consolidado de productos que posee un cliente:
     * cuentas, tarjetas, créditos, inversiones
     */
	function integral(){
            
		if(!validarTransaccion(25)) $this->redirect('/consultas/');
		
		// para eliminar la sesion que indica el regreso al index o el consolidado
		$this->Session->delete('usuarios.volver_index');
		// se leen los datos del usuario		
		$usuario = $this->Session->read('usuarios.datos');	

        // registra los datos del usuario en bitacora
		$this->Bitacora->registraBitacora($usuario['id'], 25);
        // esta clase se ejecuta desde el archivo "activo.php" por nomenclaturas de CakePHP
		
		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$tipo_documento = $usuario['tipo_rif'];
			$documento = desencriptar($usuario['rif']);
			$documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}else{
			$tipo_documento = $usuario['tipo_cedula'];
			$documento = desencriptar($usuario['cedula']);
			$documento_identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
		
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		$res = '';
		$identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];
		if($identificacion){
			$res = $this->Activo->invoca(
				'SUMDSJV',
				array(
					'req'=>array(
						'SUMACN'=>$identificacion,
						'SUMFIR'=>'X'
					)
				)
			);
		}



		// Llamada de funcion de consulta de credicard
		$consultaSalsoTdc = $this->Credicard->consultaDeSaldoTdcSoap(1);
		$consultaSalsoTdc = $consultaSalsoTdc['ConsultSaldoenLineaTDCResponse'];

        //Busco el valor del petro en Bs y $
        $petro_bs = _p('monto.petro.bs');
        $petro_usd = _p('monto.petro.usd');

		$cuentas = array();
        $cuentas_moneda_extranjera = array();
		$tarjetas = array();
		$inversiones = array();
		$creditos = array();

		$totales = array(
			'SUMBAL'=>0,
			'SUMAVB'=>0
		);
                
        $totales_moneda_extranjera = array(
			'SUMBAL'=>0,
			'SUMAVB'=>0
		);

		$totales_inversiones = array(
			'SUMBAL'=>0,
			'SUMAVB'=>0
		);

		$totales_creditos = array(
			'LNSPOF'=>0,
			'e01DEAMPP'=>0,
			'LNSPRI'=>0
		);

		$totales_uvcc = array(
			'SUMSDBBSR3'=>0,
			'SUMCBSR3'=>0,
			'SUMCBSR3_PETRO'=>0,
			'SUMCBS3'=>0,
			'SUMCOBS3' => 0
		);

		
		$lineasCredito= array();
		if($res){
			$x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
			normaliza($x);
			
            // Verifica que el webservice traiga registros
			if(isset($res['SUMDSJVReturn']['ILN006001']['ILN006001'])){
                // Verifica si trae un solo registro o varios
                $lineasCredito = $res['SUMDSJVReturn']['ILN006001']['ILN006001'];
                normaliza($lineasCredito);
			}
			
			foreach($x as $i){
				$i['ALIAS'] = _a($usuario['id'], $i['SUMACN']);
					
				if( validarTransaccion(3) && ($i['SUMTYP']=='SAV' || $i['SUMTYP']=='DDA' || $i['SUMTYP']=='NOW' || $i['SUMTYP']=='MMK') && ($i['SUMCCY']=='VEB')){
					
					// si es un usuario juridico verifica si tiene permiso en la cuenta
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 3, trim($i['SUMACN'])) ) continue;
					}
					
					$cuentas[] = $i;
					$totales['SUMBAL'] += $i['SUMBAL'];
					$totales['SUMAVB'] += $i['SUMAVB'];
                                        $totales['SUMBAL_PETRO'] = $totales['SUMBAL']/$petro_bs;
                                        $totales['SUMAVB_PETRO'] = $totales['SUMAVB']/$petro_bs;
				}else if( validarTransaccion(3) && ($i['SUMCCY']!='VEB') ){
					// si es un usuario juridico verifica si tiene permiso en la cuenta
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 3, trim($i['SUMACN'])) ) continue;
					}
					
					$cuentas_moneda_extranjera[] = $i;
					$totales_moneda_extranjera['SUMBAL'] += $i['SUMBAL'];
					$totales_moneda_extranjera['SUMAVB'] += $i['SUMAVB'];
                    $totales_moneda_extranjera['SUMBAL_PETRO'] = $totales_moneda_extranjera['SUMBAL']/$petro_usd;
					$totales_moneda_extranjera['SUMAVB_PETRO'] = $totales_moneda_extranjera['SUMAVB']/$petro_usd;
				}
				else if(validarTransaccion(4) && ($i['SUMTYP']=='CC&' || $i['SUMTYP']=='TCC' || $i['SUMTYP']=='TCE')){
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 4, trim($i['SUMACN'])) ) continue;
					}					
				}
				else if(validarTransaccion(6) && ($i['SUMTYP']=='CDS' || $i['SUMTYP']=='TDS')){
					// si es un usuario juridico verifica si tiene permiso en la cuenta
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 6, trim($i['SUMACN'])) ) continue;
					}
					
					// Busca la fecha de vencimiento consultando el detalle de la inversion
					$res2 = $this->Activo->invoca(
						'CDSDSJV',
						array(
							'req'=>array(

								'CDSDEACCY'=>$i['SUMCCY'],
								'CDSDEAACC'=>trim($i['SUMACN'])
							)
						)
					);

					// Asigna los datos al registro de la inversion
					$mov = $res2['CDSDSJVReturn']['CDSDSJS'];
					$i = array_merge($i, array(
						'CDSMATDTE1'=>$mov['CDSMATDTE1'],
						'CDSMATDTE2'=>$mov['CDSMATDTE2'],
						'CDSMATDTE3'=>$mov['CDSMATDTE3']
					));

					// Asigna al arreglo de inversiones y calcula los totales
					$inversiones[] = array_map("trim", $i);
					$totales_inversiones['SUMBAL'] += $i['SUMBAL'];
					$totales_inversiones['SUMAVB'] += $i['SUMAVB'];

				} elseif (validarTransaccion(5) && $i['SUMTYP']=='LNS' ) {
					if (trim($i['SUMUSR'])!='CA01' && trim($i['SUMUSR'])!='PG01'){
						
					// si es un usuario juridico verifica si tiene permiso en la cuenta
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 5, trim($i['SUMACN'])) ) continue;
					}
					
					$res = $this->Activo->invoca(
						'LNSDSJV',
						array(
							'req'=>array(
								'LNSACC'=>trim($i['SUMACN']),

							)
						)
					);

					$x = $res['LNSDSJVReturn']['LNSDSJV'];

					$y = array_merge($i,$x);
					
					$res = $this->Activo->invoca(
						'EDL098001',
						array(
							'req'=>array(
								'e01DEAACC'=>trim($i['SUMACN']),
								'h01USERID'=>$documento_identificacion
							)
						)
					);
					
					if($res && isset($res['EDL098001Return']['EDL098001'])){
						$detalle = $res['EDL098001Return']['EDL098001'];
						$y = array_merge($y, $detalle);
					}

					$creditos[] = $y;
					$totales_creditos['LNSPOF'] += $y['LNSPOF'];
                    $totales_creditos['LNSPOF_PETRO'] = $totales_creditos['LNSPOF']/$petro_bs;
					$totales_creditos['e01DEAMPP'] += $y['e01DEAMPP'];
					$totales_creditos['LNSPRI'] += $y['LNSPRI'];
                    $totales_creditos['LNSPRI_PETRO'] = $totales_creditos['LNSPRI']/$petro_bs;
				}
				if (trim($i['SUMUSR'])=='CA01' || trim($i['SUMUSR'])=='PG01') {
					
					
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 5, trim($i['SUMACN'])) ) continue;
					}
					
					$res_uvcc = '';
					if($identificacion){
						$res_uvcc = $this->Activo->invoca(
							'SUMDSJV3',
							array(
								'req'=>array(
									'SUMUSR3'=>$identificacion,
								)
							)
						);
					}
					if ($res_uvcc && isset($res_uvcc['SUMDSJV3Return']['SUMDSJV3'])){
						// Verifica que el webservice traiga registros de montos UVCC
						$montos_uvcc= array();
						$montos_uvcc= $res_uvcc['SUMDSJV3Return']['SUMDSJV3']['SUMDSJV3'];
						$montos_uvcc+=array('SUMUSR'=>'');
				            normaliza($montos_uvcc);
						
					}
					if(isset($montos_uvcc[0]['SUMACCS3'])){
						if(trim($i['SUMACN']) == trim($montos_uvcc[0]['SUMACCS3'])){
							$montos_uvcc[0]['SUMUSR'] =  trim($i['SUMUSR']);
							
							$totales_uvcc['SUMSDBBSR3'] += $montos_uvcc[0]['SUMSDBBSR3'];
                    		$totales_uvcc['SUMCBSR3_PETRO'] += $montos_uvcc[0]['SUMCBSR3']/$petro_bs;
							$totales_uvcc['SUMCBSR3'] += $montos_uvcc[0]['SUMCBSR3'];
							$totales_uvcc['SUMCBS3'] += $montos_uvcc[0]['SUMCBS3'];
							$totales_uvcc['SUMCOBS3'] += $montos_uvcc[0]['SUMCOBS3'];
							
						}
					}
                    
				}
				}
			}
		}


        //Obtener tarjetas de crédito normal e indexada
			$totales_tarjetas = array(
				'saldo_actual' => 0,
				'saldo_petro'=>0,
				'saldo_actualUVC'=> 0,
				'credito_disponible' => 0,
				'credito_disponibleUVC' => 0,
				'pago_minimo' => 0
			);				



	      if(isset($consultaSalsoTdc['cedula'])){  
	      	   //Verifica que la cuenta este normal
               if($consultaSalsoTdc['ESTATUSTRJ'] <= 2){
			         //un solo tarjeta
			         $resp = $this->llenarConsultaTarjeta($tarjetas, $consultaSalsoTdc, $usuario);
			         if($resp != 0){
			            $tarjetas = $resp;
			            $totales_tarjetas = $this->llenarConsultaTotales($totales_tarjetas, $consultaSalsoTdc);
			         }
			    }     
	      }else{
	      	//VArias tarjetas
	      	foreach ($consultaSalsoTdc as $key => $consultaTdc){  
	      		if($consultaTdc['ESTATUSTRJ'] <= 2){
	      			$resp = $this->llenarConsultaTarjeta($tarjetas, $consultaTdc, $usuario);
			      	if($resp != 0){
			        	$tarjetas = $resp;
			        	$totales_tarjetas = $this->llenarConsultaTotales($totales_tarjetas, $consultaTdc);  
			      	}
			    }  	
	      	}	
   	   }	


   

  
	   // Envia los datos a la vista integral.ctp
	   $this->set(compact(
			'cuentas', 
			'cuentas_moneda_extranjera', 
			'totales', 
			'totales_moneda_extranjera', 
			'tarjetas', 
			'totales_tarjetas', 
			'inversiones', 
			'totales_inversiones', 
			'creditos', 
			'totales_creditos',
			'lineasCredito',
			'petro_bs',
			'petro_usd', 
			'montos_uvcc',
			'totales_uvcc', 
			'cod_uvcc'
	   ));	

		$this->Session->write('consultTdc.consultaTdc', $tarjetas);
		$this->Session->write('consultTdc.tarjetasTdc', $tarjetas);

		$this->set(compact('tarjetas', 'totales'));
		$this->layout = 'interna';
		$this->set('seccion_actual','consultas.integral');
	}


	function llenarConsultaTotales($totales_tarjetas, $consultaTdc){
	   $pagMinimo = "";
	   $sdoUVC = $consultaTdc['saldeuduvc'];
	   $sdoBS = 0;
   	   $creditoBS = 0;

   	   $limUVC = $consultaTdc['limcreduvc'];;

	   $tipoUVC = $consultaTdc['tipoctauvc'];

       $sdoBS = $consultaTdc['saldocont'];



	   if($sdoUVC == null){
		   	$sdoUVC = 0;
	   }

	   if($limUVC == null){
		   	$limUVC = 0;
	   }	

	   $creditoBS = $consultaTdc['disponible'];
	   $pagMinimo = $consultaTdc['pagomin'];
	   $totales_tarjetas['saldo_actual'] += $sdoBS;
	   $totales_tarjetas['saldo_petro'] += $sdoBS;
	   $totales_tarjetas['saldo_actualUVC'] += $sdoUVC;
	   $totales_tarjetas['credito_disponible'] += $creditoBS;
	   $totales_tarjetas['pago_minimo'] += $pagMinimo;   
	   $totales_tarjetas['credito_disponible_petro'] += $creditoBS;	
	   return $totales_tarjetas;
	}


    function llenarConsultaTarjeta($tarjetas, $consultaTdc, $usuario){
	    $fecPago = "";
	    $pagMinimo = "";
	    $tipoUVC = null;
	    $sdoUVC = 0;
	    $sdoBS = 0;
	    $creditoBS = 0;
	    $limUVC = 0;
	    $limBS = 0;    
        $swJuridico = 0; 

        $nroTarjeta = $consultaTdc['tarjeta']; 

        if($consultaTdc['estatustrj'] == '1' || $consultaTdc['estatustrj'] == '2'){
            if($usuario['rol_juridico_id'] != null){
            	//si el cliente es juridico solo puede visualizar las terjetas de la empresa
	            $binTarjeta = substr($nroTarjeta,0,6);
	            if($binTarjeta == '427172' || $binTarjeta == '547554'){
	            	$swJuridico = 0;
	            }else{
	            	return 0;
	            }
	        } 
	        //Si es un cliente natural puede visualizar cualquier tarjeta del banco
        }else{
        	return 0;
        } 




        if($swJuridico == 0){
  	        $fecPago = $consultaTdc['fepago'];
            $pagMinimo = $consultaTdc['pagomin'];
            $tipoUVC = $consultaTdc['tipoctauvc'];
            $limBS = $consultaTdc['limcredito'];
				$nroTarjeta = $consultaTdc['cuenta'];
            $sdoUVC =  $consultaTdc['saldeuduvc'];


            $limUVC = $consultaTdc['limcreduvcc'];				

            $sdoBS = $consultaTdc['saldocont'];
            $creditoBS = $consultaTdc['disponible'];

           	if($sdoUVC == null){
           		$sdoUVC = '0.00';
           	}

           	if($limUVC == null){
           		$limUVC = '0.00';
          	}


            $saldoPetro = $sdoBS / $petro_bs;

			$i['saldo_actual'] = $sdoBS;
			$i['nroTarjeta'] = $nroTarjeta;
			$i['saldo_petro'] = $saldoPetro;
			$i['saldo_actual_uvc'] = $sdoUVC;
            $i['tipoTarjetaUVC'] = $tipoUVC; 
			$i['tipo_tarjeta_uvc'] = $tipoUVC;
			$i['pago_minimo'] = $pagMinimo;
			$i['creditoBS'] = $creditoBS;
			$i['nroTarjeta'] = $consultaTdc['cuenta'];
   			$i['limiteBS'] = $limBS;
   			$i['limiteUVC'] = $limUVC; 
				$i['franquicia'] = $consultaTdc['franquicia'];

         	if($fecPago != null){
	         	$i['dia_pago'] = substr($fecPago,0,2);
	   		   	$i['mes_pago'] = substr($fecPago,3,2);
	   		   	$anio = substr($fecPago,6,2) * 1;
	   		   	if($anio > 0){
   		      		$i['anio_pago'] = "20" . substr($fecPago,6,2); 		
   		      	}else{
   		      		$i['anio_pago'] = "0000";
   		      	}	
		   	}else{
   			   	$i['dia_pago'] = "";
   	        	$i['mes_pago'] = "";
			    	$i['anio_pago'] = ""; 		
	   		}   
	   		$i['fecPago'] = $fecPago;	
            //Tipo Tarjeta  Empresarial, normal
            //configurar cuando pasen los bines de credicard miriam
            $tipoTarjeta = "CC&";
            //configurar cuando pasen los bines de credicard miriam


            $i['SUMTYP'] = $tipoTarjeta;
				$i['ALIAS'] = 'Alias no asignado';
				$tarjetas[] = $i;
				return $tarjetas;            
        }	
    }



	/**
     *Acción para el histórico de transacciones
     * @param String $volver Variable que indica que viene de la confirmación
     */
	function historico_transacciones($volver=''){
		
                // setlocale(LC_ALL,'es_VE.utf8'); PARA LINUX
                setlocale(LC_ALL,'es_VE.UTF-8'); // PARA LINUX
		
                $this->layout = 'interna';

		$this->set('seccion_actual','consultas.historico.transacciones');
		
		$transacciones = array();
				
		$usuario = $this->Session->read('usuarios.datos');

		$condiciones = array(
			'usuario_id'=>$usuario['id'],
			'canal'=>Configure::read('canal.ib'),
			'transaccion_id not in (1,2,26,44,45,46,49)',
			'Bitacora.fecha_hora >=' => date('Y-m-d 00:00:00'),
			'Bitacora.fecha_hora <=' => date('Y-m-d 23:59:59'),
			'OR'=>array(
				'error_operacion IS NULL',
				'error_operacion = 0',
			)
		);
		
		if(!empty($volver)){
			if(!$this->Session->check('consultas.transacciones_historico')){
				$this->redirect('historico_transacciones');
			}
			
			$this->data = $this->Session->read('consultas.transacciones_historico_params');
			$condiciones = $this->Session->read('consultas.transacciones_historico');		
		} else if(!empty($this->data['HistoricoTransacciones'])){
			if(!empty($this->data['HistoricoTransacciones']['perfiles'])){
				if($this->data['HistoricoTransacciones']['perfiles'] == 't'){
					$condiciones["usuario_id in (select id from usuarios where rif=? and tipo_rif=?)"]=array(
						$usuario['rif'],
						$usuario['tipo_rif']
					);
				} else {
					$condiciones["usuario_id in (select id from usuarios where rif=? and tipo_rif=? and rol_juridico_id=?)"]=array(
						$usuario['rif'],
						$usuario['tipo_rif'],
						$this->data['HistoricoTransacciones']['perfiles']
					);
				}
				
				if(isset($condiciones['usuario_id'])) unset($condiciones['usuario_id']);
			}

			if(!empty($this->data['HistoricoTransacciones']['operacion'])){
				$condiciones['Transaccion.modulo_id']=$this->data['HistoricoTransacciones']['operacion'];
			}

			if(!empty($this->data['HistoricoTransacciones']['transaccion'])){
				$condiciones['Bitacora.transaccion_id']=$this->data['HistoricoTransacciones']['transaccion'];
			}

			if(!empty($this->data['HistoricoTransacciones']['referencia'])){
				$condiciones['Bitacora.referencia']=  encriptar($this->data['HistoricoTransacciones']['referencia']);
			}

			if(!empty($this->data['HistoricoTransacciones']['monto'])){
				$condiciones['Bitacora.monto']=formatea_monto_toWs($this->data['HistoricoTransacciones']['monto']);
			}
			
			if(isset($this->data['HistoricoTransacciones']['tipo'])){
				if($this->data['HistoricoTransacciones']['tipo']=='p'){
					$condiciones['Bitacora.fecha_hora >=']=formatea_fecha_toMSSQL(
						$this->data['HistoricoTransacciones']['fechaDesde']['day'],
						$this->data['HistoricoTransacciones']['fechaDesde']['month'],
						$this->data['HistoricoTransacciones']['fechaDesde']['year']
					).' 00:00:00';

					$condiciones['Bitacora.fecha_hora <=']=formatea_fecha_toMSSQL(
						$this->data['HistoricoTransacciones']['fechaHasta']['day'],
						$this->data['HistoricoTransacciones']['fechaHasta']['month'],
						$this->data['HistoricoTransacciones']['fechaHasta']['year']
					).' 23:59:59';

				} else if($this->data['HistoricoTransacciones']['tipo']=='t'){
					if(empty($this->data['HistoricoTransacciones']['mes_transaccion'])){
						$condiciones['Bitacora.fecha_hora >='] = date('Y-m-d 00:00:00', strtotime('-1 year'));
						$condiciones['Bitacora.fecha_hora <='] = date('Y-m-d 23:59:59');						
					} else {
						$fecha_desde = date('Y-m-d 00:00:00', $this->data['HistoricoTransacciones']['mes_transaccion']);
						$condiciones['Bitacora.fecha_hora >='] = $fecha_desde;
						$condiciones['Bitacora.fecha_hora <='] = date( 'Y-m-d 23:59:59', strtotime($fecha_desde . " +1 month"));
					}
				}
			}
		} else {
			if($usuario['rol_juridico_id']==1){
				$this->data['HistoricoTransacciones']['perfiles']='1';
			}
		}
		
		$total_registros = $this->Bitacora->find(
			'count',
			array(
				'conditions'=>$condiciones
			)
		);

		$offset = 0;

		if(
			isset($this->data['HistoricoTransacciones']['offset']) &&
			preg_match('/^\d+$/',$this->data['HistoricoTransacciones']['offset'])
		){
			$offset = intval($this->data['HistoricoTransacciones']['offset']);			
		}

		$resultados = $this->Bitacora->find(
			'all',
			array(
				'conditions'=>$condiciones,
				'contain'=>array('Transaccion'=>'Modulo','Usuario'=>'login'),
				'order'=>'Bitacora.fecha_hora desc',
				'offset'=>$offset,
				'limit'=>Configure::read('Tamano.paginacion')
			)
		);
		
		$this->Session->write('consultas.transacciones_historico_params', $this->data);
		$this->Session->write('consultas.transacciones_historico',$condiciones);
			
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		$identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);;
		
		foreach($resultados as &$i){
			if( in_array($i['Bitacora']['transaccion_id'], array(8,11,100024,100023,100027,100028)) ){
				// Para las transacciones de otros bancos verificar con el servicio, para ver si hay data que mostrar al usuario
				
				$cuenta = substr(desencriptar($i['Bitacora']['cuenta_origen']),10);                                
				$referencia = str_pad(desencriptar($i['Bitacora']['referencia']),9,'0',STR_PAD_LEFT);
				$monto = formatea_monto_fromWs($i['Bitacora']['monto']);
                                
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
				
				if(
					trim($res['TINQDSJV2Return']['TINQDSJV2']['TINID1']) == '0' &&
					trim($res['TINQDSJV2Return']['TINQDSJV2']['TINID2']) == '0' &&
					trim($res['TINQDSJV2Return']['TINQDSJV2']['TINID3']) == '0'
				){
					// Ignorar
					$i['mostrar_enlace']= false;
				} else {
					$i['mostrar_enlace']= true;
				}
			} else if( $i['Bitacora']['transaccion_id']==54 ){ 
				$i['mostrar_enlace']= false;
			}else {
				$i['mostrar_enlace']= true;
			}
		}

		$this->set(compact('resultados','total_registros'));

		if( !empty($this->data['HistoricoTransacciones']['operacion']) ){
			$transacciones = $this->Transaccion->obtenerTransaccionesPorModulo($usuario, $this->data['HistoricoTransacciones']['operacion']);
		}
		
		if( !empty($usuario['tipo_rif']) ){ // PARA JURIDICO
			
			// En caso de que no sea MASTER
			if($usuario['rol_juridico_id']>1){
				$condicion_modulos = array(
					'Modulo.canal_id'=>Configure::read('canal.ib'),
					'Modulo.id in (
						SELECT transacciones.modulo_id 
						FROM transacciones
						INNER JOIN transacciones_usuarios_juridicos TJ ON TJ.transaccion_id=transacciones.id
						WHERE TJ.usuario_id='. $usuario['id'] .'
					)'
				);
			}
			else{ // SI ES UN USUARIO MASTER
				$condicion_modulos = array(
					'Modulo.canal_id'=>Configure::read('canal.ib'),
					"Modulo.id in (
						SELECT transacciones.modulo_id 
						FROM transacciones
						INNER JOIN transacciones_usuarios_juridicos TJ ON TJ.transaccion_id=transacciones.id
						WHERE TJ.usuario_id in (select id from usuarios where rif = '". $usuario['rif'] ."' and tipo_rif = '". $usuario['tipo_rif']."')
						GROUP BY transacciones.modulo_id
					)"
				);
			}
			
			$modulos = $this->Modulo->find(
				'list',
				array(
					'fields'=>array('Modulo.id','Modulo.nombre'),
					'order'=>'posicion asc',
					'conditions'=>$condicion_modulos
				)
			);
		}
		else{ // PARA NATURALES
			$modulos = $this->Modulo->find(
				'list',
				array(
					'fields'=>array('Modulo.id','Modulo.nombre'),
					'order'=>'posicion asc',
					'conditions'=>array(
						'Modulo.canal_id'=>Configure::read('canal.ib'),
						'Modulo.id in (
							SELECT transacciones.modulo_id 
							FROM transacciones
							INNER JOIN transacciones_perfiles_ib ON transacciones_perfiles_ib.transaccion_id=transacciones.id
							WHERE transacciones_perfiles_ib.perfil_ib_id='. $usuario['perfil_ib_id'] .'
							and (
								transacciones_perfiles_ib.perfil_piloto is null or 
								transacciones_perfiles_ib.perfil_piloto in (SELECT perfil_piloto FROM usuarios_perfil_piloto WHERE usuario_id = '.$usuario['id'].')
							)
						)'
					)
				)
			);
		}
		
		$meses = array();
		setlocale(LC_ALL,'es_VE.UTF-8');
		$t1 = strtotime(date('Y-m-01'));
		for($j=0;$j<13;$j++){
			$t = strtotime("-$j months",$t1);
			$meses[strval($t)] = ucwords(strftimeAW('%B %Y', $t));
		}
		
		//Listar perfiles de usuario
		$master = false;
		if(!empty($usuario['rol_juridico_id']) && $usuario['rol_juridico_id'] == 1){
			$master = true;
			$roles= array();
			$perfiles = $this->RolJuridico->find(
				'all',
				array(
					'fields'=>array('id','nombre'),
					'conditions'=>array(
						"id in (select rol_juridico_id from usuarios where rif = '". $usuario['rif'] ."' and tipo_rif = '". $usuario['tipo_rif']."' group by rol_juridico_id)"
					)
				)
			);
			
			$roles = array();
			foreach($perfiles as $nombrePerfil){
				if(!count($roles)) $roles['t'] = 'Todos los Perfiles';
				$roles[$nombrePerfil['RolJuridico']['id']] = $nombrePerfil['RolJuridico']['nombre'];
			}
		}
		
		$this->set(compact('modulos','transacciones','meses','master','roles'));
	}

	
    /**
     *Acción para obtener las transacciones de la operación que se está enviando
     * @param int $operacion Indica el id del módulo a consultar
     * @return null 
     */
	function obtener_transacciones($operacion){
		$this->layout = 'ajax';
		$usuario = $this->Session->read('usuarios.datos');
		if(!empty($operacion) && !empty($usuario)){
			$transacciones = $this->Transaccion->obtenerTransaccionesPorModulo($usuario, $operacion);	
			$this->set(compact('transacciones'));
		} 
		else{
			return null;
		}
	}

    
    /**
     *Acción para el histórico de conexiones
     */
	function historico_conexiones(){
		
		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.historico.conexiones');

		// Genera los ultimos 12 meses anteriores
		$meses = array();
		setlocale(LC_ALL,'es_VE.UTF-8');
		$t1 = strtotime(date('Y-m-1'));
		
		for($i=0;$i<12;$i++){
			$t = strtotime("-$i months",$t1);
			$meses[strval($t)] = ucwords(strftimeAW('%B %Y', $t));
		}

		$usuario = $this->Session->read('usuarios.datos');
		
		$condiciones = array(
			'usuario_id'=>$usuario['id'],
			'canal'=>Configure::read('canal.ib'),
			'Bitacora.transaccion_id'=>array(1,2,26,44,45,46)
		);
		
		if(!empty($this->data['HistoricoTransacciones']['mes'])){
			$t1 = date('Y-m-1',$this->data['HistoricoTransacciones']['mes']);
			$t2 = date('Y-m-1',strtotime('+1 months',$this->data['HistoricoTransacciones']['mes']));
			
			$condiciones['Bitacora.fecha_hora >='] = $t1;
			$condiciones['Bitacora.fecha_hora <'] = $t2;
		}
		else{
			$condiciones['Bitacora.fecha_hora >'] = date('Y-m-d H:i:s',$t);
		}
		
		$total_registros = $this->Bitacora->find(
			'count',
			array(
				'conditions'=>$condiciones
			)
		);
		
		$offset = 0;

		if(
			isset($this->params['form']['offset']) &&
			preg_match('/^\d+$/',$this->params['form']['offset'])
		){
			$offset = intval($this->params['form']['offset']);			
		}

		$resultados = $this->Bitacora->find(
			'all',
			array(
				'conditions'=>$condiciones,
				'contain'=>array('Transaccion'=>'Modulo'),
				'order'=>'Bitacora.fecha_hora desc',
				'offset'=>$offset,
				'limit'=>Configure::read('Tamano.paginacion')
			)
		);

		$this->set(compact('resultados','total_registros','meses'));
	}

    
    /**
     * Acción para la consulta de mensajes del cliente
     */
	function mensajes(){
		if( $this->Session->check('usuarios.datos') ){
			
			if(!empty($this->data['Mensajes']['chkdata'])){
				$this->Session->write('usuarios.eliminar.mensajes', $this->data['Mensajes']['chkdata']);
				$this->redirect('eliminar_mensaje');
			}
			
			$this->layout = 'interna';

			$this->set('seccion_actual','consultas.mensajes');

			$usuario = $this->Session->read('usuarios.datos');
			
			$resultado = $this->Mensajes->buscaMensajes($usuario['id']);
			
			$total_registros = 0;
			$resultados = array();
			
			if($resultado){
			
				// Empieza código para paginar

				$total_registros = count($resultado);

				$offset = 0;

				if(
					isset($this->params['form']['offset']) &&
					preg_match('/^\d+$/',$this->params['form']['offset'])
				){
					$offset = intval($this->params['form']['offset']);			
				}

				$resultados = array_slice(
					$resultado,
					$offset,
					Configure::read('Tamano.paginacion'),
					true
				);

				// Fin código para paginar
			}
				
			$this->set(compact('resultados','total_registros'));
		}
		else{
			$this->redirect('/consultas/integral');
		}
	}
	
    
    /**
     *Acción para la eliminación de mensajes 
     */
	function eliminar_mensaje(){

		if(!$this->Session->check('usuarios.eliminar.mensajes')) $this->redirect('/consultas/integral');
		
		$usuario = $this->Session->read('usuarios.datos');
		$mensajes = $this->Session->read('usuarios.eliminar.mensajes');
		
		foreach ($mensajes AS $m){
			
			$this->Mensajes->read(null, $m);

			if($this->Mensajes->data['Mensajes']['usuario_id'] == $usuario['id']){

				$this->Mensajes->set('estatus','e');

				$this->Mensajes->save();
			}
		}
		
		$this->Session->delete('usuarios.eliminar.mensajes');
		
		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.mensajes');
	}
    
    /**
     *Acción para el exportar el histórico de transacciones
     */
	function historico_exportar(){
		if(!validarTransaccion(100008)) $this->redirect('/consultas/integral');
		
		if(!$this->Session->check('consultas.transacciones_historico')){
			$this->redirect('historico_transacciones');
		}
		
		$this->layout = 'interna';
		
		$this->set('seccion_actual','consultas.historico.transacciones');
		
		if(isset($this->data['HistoricoTransacciones']['bandera'])){
			$condiciones = $this->Session->read('consultas.transacciones_historico');
				
			$x = $this->Bitacora->find(
				'all',
				array(
					'fields'=>array(
						'Bitacora.fecha_hora',
						'Bitacora.monto',
						'Bitacora.referencia',
						'Bitacora.descripcion',
                                                'Bitacora.cedula',
						'Transaccion.nombre',
						'Modulo.nombre',
						'Transaccion.modulo_id'
					),
					'joins'=>array(
						array(
							'table'=>'transacciones',
							'alias'=>'Transaccion',
							'type'=>'left',
							'conditions'=>array('Transaccion.id=Bitacora.transaccion_id')
						),
						array(
							'table'=>'modulos',
							'alias'=>'Modulo',
							'type'=>'left',
							'conditions'=>array('Modulo.id=Transaccion.modulo_id')
						),
					),
					'conditions'=>$condiciones,
					'contain'=>array(),
					'order'=>'Bitacora.fecha_hora desc'
				)
			);			

			$this->set(compact('x'));
			
			switch($this->data['HistoricoTransacciones']['formato']){
				case 'p':
					$this->render('historico_exportar_pdf');
					break;
				case 'e':
					$this->render('historico_exportar_xls');
					break;
				case 't':
					$this->layout = 'nada';
					$this->render('historico_exportar_txt');
					break;
			}
		}
		
	}
	

    /**
     *Acción para al exportación del archivo pdf 
     */
	function descargar_pdf(){
		if(!$this->Session->check('usuarios.descarga.pdf') && $this->Session->check('usuarios.datos')){
			$this->redirect('/consultas/integral');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$data = $this->Session->read('usuarios.descarga.pdf');

		$this->set(compact('data','usuario'));
		$this->render('/elements/descargar_pdf');
	}
	
	/**
	 * Acción inicial de controlador
	 * 
	 */
	function index(){
                
                $banners_index = $this->Banners->porSeccion(3);
		
		$this->set(compact('banners_index'));
		
                $this->layout = 'interna';                                
	}
                
}

?>