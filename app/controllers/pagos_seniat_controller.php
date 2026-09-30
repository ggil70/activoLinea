<?php
/**
 * Controlador para el Pago de Impuestos Nacionales y Aduanales
 */
 
/**
 * Controlador para el Pago de Impuestos Nacionales y Aduanales
 */
class PagosSeniatController extends AppController {
	/**
	* @var array Modelos que usa el controlador 
	*/
	var $uses = array('Usuario', 'Bitacora', 'Afiliacion','TransaccionJuridicoDetalle','TransaccionPendiente');

	/**
	* @var array Componentes que utiliza este controlador
	*/
	var $components = array('Activo', 'Session', 'Email','Sms');

	/**
	* @var array Ayudantes utilizados en las vistas de este controlador
	*/
	var $helpers = array('Activo', 'Html', 'Session', 'Form', 'Paginator', 'Text', 'Javascript');
	
	/**
	* Acción para listar las afiliaciones de PAgo de Impuestos Nacionales/Aduanales del cliente 
	*/
	function terceros_listado(){
		
		//Para validar el acceso al modulo
		if(!validarTransaccion(100037)) $this->redirect('/consultas/integral');
			
		$this->Session->delete('usuarios.descarga.pdf');
		
		$this->layout = 'interna';
                
                $this->set('seccion_actual','pagos.seniat.terceros');
		$this->set('descripcion', "Terceros");
                
                if(empty($this->data['Afiliacion'])){
                    
                    $usuario = $this->Session->read('usuarios.datos');
                    $afiliaciones = $this->Afiliacion->listarxTipoOperacion($usuario['id'], 'i', 't');
                    $total_registros = sizeof($afiliaciones);
                    $this->set(compact('afiliaciones','total_registros'));
                 }else{
                    
                    $this->Session->write('pagos_seniat.pago', $this->data);
                    $this->redirect('compromisos/t');
                }
	}

        /**
	* Acción para listar los compromisos de pago del beneficiario seleccionado para el pago de Impuestos Nacionales y Aduanales 
	* @param String $volver Variable que indica si viene de la confirmación
        * @param String $tipo Variable que indica si es Mismo Titular o Terceros
	*/
	function compromisos($tipo='p',$volver=null){
			
                $error= '0';
                if($tipo == 'p'){
                    $descripcion = "Mismo Titular"; 
                    $this->set('seccion_actual','pagos.seniat.propios');
                    $transaccion = 100036;
                }else{
                     $descripcion = "Terceros"; 
                     $this->set('seccion_actual','pagos.seniat.terceros');
                     $transaccion = 100037;
                }
                
                //Para validar el acceso al modulo
                if(!validarTransaccion($transaccion)) $this->redirect('/consultas/integral');
                    
                $this->layout = 'interna';

		$this->set(compact('descripcion','tipo'));    
                
                if($this->data['PagosSeniat']['paginando'] == '' && !empty($this->data['PagosSeniat']) && (isset($this->data['PagosSeniat']['id']) && $this->data['PagosSeniat']['id'] != "") && $volver == null){

                    $this->data['PagosSeniat']['tipo'] = $tipo;
                    $this->Session->write('pagos_seniat.pago', $this->data);
                    $this->redirect('pago_confirmacion');

                }else{ 
                    $usuario = $this->Session->read('usuarios.datos'); 
                    $this->data = $this->Session->read('pagos_seniat.pago');
                    if(empty($usuario['rif'])){
                        $error = 'RP';
                        $codigoError = 'RP';
                        if($usuario['tipo_cedula'] != 'P'){
                            $rifPagador = $this->Activo->generaRIF($usuario['tipo_cedula'],intval(desencriptar($usuario['cedula'])));
                            if($rifPagador != -1){
                                $codigoError = '';
                                $error = '0';
                            }
                        }else{
                            
                        }
                        $this->set(compact('codigoError','error'));
                    }else{
                        $rifPagador = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                    }
                    if (isset($this->data['PagosSeniat']['idAfiliacion']) && $tipo == 't'){
                        $afiliacion = $this->Afiliacion->listarxTipoOperacion($usuario['id'], 'i', $tipo, $this->data['PagosSeniat']['idAfiliacion']);
                        if(isset($this->data['Afiliacion']['nombre_beneficiario'])){
                            $nombre_beneficiario = $this->data['Afiliacion']['nombre_beneficiario'];
                        }else{
                            $nombre_beneficiario = $this->data['PagosSeniat']['beneficiario'];
                        }
                    }else{
                        $afiliacion = $this->Afiliacion->listarxTipoOperacion($usuario['id'], 'i', $tipo);
                        $nombre_beneficiario = desencriptar($usuario['nombre']);
                    }
                    
                    $this->set('nombre_beneficiario',$nombre_beneficiario);
                    
                    if (isset($afiliacion['Afiliacion']['numero_cuenta']) && $error=='0'){
                        $this->data['PagosSeniat']['idAfiliacion'] = $afiliacion['Afiliacion']['id'];
                        
                        $this->Session->write('usuarios.tipo.seniat', $tipo); 
                        
                        //Se buscan las cuentas para el combo de cuenta a debitar
                        $cuentas = $this->Activo->buscarCuentasSaldos();
                        
                        $datos_basicos = $this->Session->read('usuarios.nibpf3010');

                        $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
                        
                        $this->set(compact('cuentas','rifPagador','afiliacion'));
                        
                        $this->set('total_registros',0);

                        //Se buscan los compromisos de pago del beneficiario
                        $compromisos = array();
                        $total_registros = 0;
                        
                        set_time_limit(120);
                        
                        $res = $this->Activo->invoca(
                                'IN097001',
                                array(
                                        'req'=>array(
                                                'IN9701IDUS'=> $identificacion,
                                                'IN9701BVDM'=> date('m'),
                                                'IN9701BVDD'=> date('d'),
                                                'IN9701BVDY'=> date('y'),
                                                'IN9701RIFC'=> $this->Activo->cambiar_tipo_rif(desencriptar($afiliacion['Afiliacion']['numero_cuenta'])),
                                                'IN9701RIFP'=> $this->Activo->cambiar_tipo_rif($rifPagador)
                                        )
                                ),120
                        );

                        if($res && !empty($res['IN097001Return']['IN09701']['IN09701'])){
                                
                            $compromisos = $res['IN097001Return']['IN09701']['IN09701'];
                            normaliza($compromisos);

                            $pendientes = $this->TransaccionPendiente->find(
				'all',
				array(
					'conditions'=>array(
						'TransaccionPendiente.tipo_rif'=>$usuario['tipo_rif'],
						'TransaccionPendiente.rif'=>$usuario['rif'],
                                                'TransaccionPendiente.estatus'=>array('pa','ep'),
                                                'TransaccionPendiente.tipo'=>array('ip ','it ')
					)
				)
                            );
                            $idPendientes = "";
                            foreach($pendientes as $k){
                                    $descripcionTP = desencriptar($k['TransaccionPendiente']['descripcion']);
                                    $detalle = explode('?', $descripcionTP);
                                    $idCompromiso = $detalle[0];
                                    
                                    $idPendientes = $idPendientes . "/" . $idCompromiso; 
                            }
                            
                            // Empieza código para paginar
                            $total_registros = sizeof($compromisos);

                            $offset = 0;

                            if(!empty($volver)){
                                $this->params['form']['offset'] = intval($this->Session->read('pagos.seniat.offset'));
                            }

                            if(isset($this->params['form']['offset']) && preg_match('/^\d+$/',$this->params['form']['offset'])){
                                $offset = intval($this->params['form']['offset']);			
                            }

                            $compromisos = array_slice($compromisos,
                                    $offset,
                                    Configure::read('Tamano.paginacion'),
                                    true
                            );
                        }else{

                            $descripcionError = "Compromisos SENIAT";
                            $error = trim($res['IN097001Return']['error']['ERNU01']);
                            $descripcionError =  ($res && isset($res['IN097001Return']['error']['ERRNUM'])) ? ' - '.trim($res['IN097001Return']['error']['ERNU01']).' '.$res['IN097001Return']['error']['ERDS01'] : '';
                            $this->log('Error en la solicitud de los compromisos '.$descripcion.' - error: ' . $error . ' - descripcion: ' . $descripcionError, LOG_ERR);
                            
                             //Verificar si tiene codigo de error spice o seniat
                             $codigoError = '';
                             if ($error!= ""){
                                $pos = strpos($descripcionError, '(');
                                if($pos){
                                    $codigoError = substr($descripcionError,$pos);
                                }else if($error!= "ST1"){
                                    $codigoError = '(S'.$error.')';
                                }
                             }
                             
                            $this->log('Compromisos SENIAT - ' . $descripcionError, LOG_ERR);
                            $this->Bitacora->registraBitacora(
                                    $usuario['id'], 
                                    $transaccion, 
                                    "", 
                                    $afiliacion['Afiliacion']['numero_cuenta'], 
                                    "", 
                                    "", 
                                    $descripcionError, 
                                    1);
                            
                            if($error == '8028' || $error == 'J0'){//Contribuyente sin compromisos (se coloca de esta manera para que muestre el mensaje en pantalla)
                                $error = '0';
                            }
                            
                            $this->set(compact('error', 'descripcionError', 'codigoError'));
                        }

                        $this->Session->write('pagos_seniat.pago', $this->data);

                        // Fin código para paginar

                        if (!empty($usuario['rif']) && in_array($usuario['rol_juridico_id'],array(3,5))){//cuando es juridico verifico el monto limite de la configuracion de la transaccion

                            //Se buscan los límites para la transaccion
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                                    $usuario['id'],
                                                    $transaccion 
                                                    );

                            if(isset($transaccion_detalle)){
                                    if(is_numeric($transaccion_detalle['limite'])){
                                        $limite=$transaccion_detalle['limite'];
                                    }else{
                                        $limite = -1;
                                    }
                            }else{
                                $error = 'U';
                            }   

                         }else{//cuando es natural o master unico verifico el monto limite de la configuración general
                            $limite = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : -1;
		         }

                         $this->set(compact('compromisos','total_registros','limite','idPendientes'));
                            
                    }else if($error != 'RP'){
                         $error = 'RA';
                         $codigoError = 'RA';
                    }
                    $this->set('error', $error);
               }
	}
	
	/**
	* Acción de confirmación para el pago de Impuestos Nacionales y Aduanales
	*/
	function pago_confirmacion(){
		
                $this->layout = 'interna';

		$this->data = $this->Session->read('pagos_seniat.pago');
                
                $tipo = $this->data['PagosSeniat']['tipo'];
                
                if($tipo == 'p'){
                    $descripcion = "Mismo Titular"; 
                    $this->set('seccion_actual','pagos.seniat.propios');
                }else{
                    $descripcion = "Terceros"; 
                    $this->set('seccion_actual','pagos.seniat.terceros');
                }
                
                $this->set(compact('tipo', 'descripcion'));
                
                if($this->Session->check('pagos_seniat.pago')){
			$this->data = $this->Session->read('pagos_seniat.pago');
                } else {
			$this->redirect('compromisos/'.$this->data['PagosSeniat']['tipo']);
		}
		
	}
	
        /**	 
        * Acción para el resultado del pago de Impuestos Nacionales y Aduanales, ejecuta el pago.
	*/
	function pago_resultado(){
            
                $error = '0';
                $this->layout = 'interna';

		if($this->Session->check('pagos_seniat.pago') && $this->Session->read('usuarios.datos')){
                        $this->data = $this->Session->read('pagos_seniat.pago');
			
                        if($this->data['PagosSeniat']['tipo']=='p'){
                            $this->set('seccion_actual','pagos.seniat.propios');
                            $this->set('descripcion', 'Mismo Titular');
                            $descripcion = 'Mismo Titular';
                            $transaccion = 100036;                            
                        }else{
                            $this->set('seccion_actual','pagos.seniat.terceros');
                            $this->set('descripcion', 'Terceros');
                            $descripcion = 'Terceros';
                            $transaccion = 100037;
                            $tipoTransferencia='22';
                        }
                        
                        $this->Session->write('tipo',$this->data['PagosSeniat']['tipo']);
                        

                        /*Inicio Codigo para validar TDC*/
                        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

                        $usuario = $this->Session->read('usuarios.datos');
                        
                        // Validación anti fraude
                        $cuentas = $this->Activo->buscarCuentas();

                        if(!in_array($this->data['PagosSeniat']['cuentaDebitar'], array_keys($cuentas))){
                                $this->redirect('/consultas/integral');
                        }
                        //  Fin validación anti fraude				

                        $usuario = $this->Session->read('usuarios.datos');
                        $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                        $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);

                        $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                                $usuario['id'],
                                                $transaccion 
                                                );

                        $limite = "";
                        if (!empty($usuario['rif']) && in_array($usuario['rol_juridico_id'],array(3,5))){//cuando es juridico verifico el monto limite de la configuracion de la transaccion

                            //Se buscan los límites para la transaccion
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                                    $usuario['id'],
                                                    $transaccion 
                                                    );

                            if(isset($transaccion_detalle)){
                                if(is_numeric($transaccion_detalle['limite'])){
                                    $limite=$transaccion_detalle['limite'];
                                }
                                $this->data['PagosSeniat']['aprobaciones_requeridas']=$transaccion_detalle['aprobaciones'];
                            }   
                        }else{//cuando es natural o master unico verifico el monto limite de la configuración general
                            $limite = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : "";
                        }
                        
                        $monto_mayor_limite = false;
                        if ($limite != "" && formatea_monto_toWs($this->data['PagosSeniat']['monto'])>$limite){
                            $monto_mayor_limite = true;
                        }

                        $aduana = explode(' - ', $this->data['PagosSeniat']['aduana']);
                        $impuesto = explode(' - ', $this->data['PagosSeniat']['impuesto']);

                        if(count($aduana)>1){
                            $codAduana = $aduana[0];
                            $nomAduana = $aduana[1];
                        }else{
                            $codAduana = "";
                            $nomAduana = "";
                        }
                                
                        if ($monto_mayor_limite){
                            $this->log('Pago SENIAT  - Limite de la transacción menor al monto del pago', LOG_ERR);		
                            $error = '2000';
                            $codigoError="";
                            $this->set(compact('error', 'codigoError'));
                        }else if(empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2){//persona natural y master unico

                                $this->Session->delete('pagos_seniat.pago');

                                set_time_limit(120);

                                $res = $this->Activo->invoca(
                                        'IN097002',
                                        array(
                                                'req'=>array(                                                        
                                                        'IN9702IDUS'=> $identificacion,
                                                        'IN9702BVDM'=> date('m'),
                                                        'IN9702BVDD'=> date('d'),
                                                        'IN9702BVDY'=> date('y'),
                                                        'IN9702FACC'=> substr($this->data['PagosSeniat']['cuentaDebitar'], 10),
                                                        'IN9702IDCO'=> $this->data['PagosSeniat']['id'],
                                                        'IN9702RIFP'=> $this->Activo->cambiar_tipo_rif($this->data['PagosSeniat']['rifPagador']),
                                                        'IN9702CFOR'=> $this->data['PagosSeniat']['forma'],
                                                        'IN9702NPLA'=> $this->data['PagosSeniat']['planilla'],
                                                        'IN9702RIFC'=> $this->Activo->cambiar_tipo_rif($this->data['PagosSeniat']['rif']),
                                                        'IN9702PLIQ'=> substr($this->data['PagosSeniat']['periodo'],6).substr($this->data['PagosSeniat']['periodo'],3,2).substr($this->data['PagosSeniat']['periodo'],0,2),
                                                        'IN9702MTOT'=> formatea_monto_toWs($this->data['PagosSeniat']['monto']),
                                                        'IN9702EFEC'=> formatea_monto_toWs($this->data['PagosSeniat']['monto']),
                                                        'IN9702CIMN'=> $impuesto[0],
                                                        'IN9702CONC'=> $impuesto[1],
                                                        'IN9702CADU'=> $codAduana,
                                                        'IN9702NADU'=> $nomAduana,
                                                        'IN9702NEXP'=> $this->data['PagosSeniat']['expediente'],
                                                        'IN9702NPOR'=> $this->data['PagosSeniat']['porcion'],
                                                        'IN9702FVCT'=> substr($this->data['PagosSeniat']['vencimiento'],6).substr($this->data['PagosSeniat']['vencimiento'],3,2).substr($this->data['PagosSeniat']['vencimiento'],0,2)
                                                 )	
                                        ),120
                                );

                                $descripcionError = "Pago Impuestos Nacionales/Aduanales - ".$descripcion;
                                $referencia = "";

                                if(isset($res['IN097002Return']['error'])){

                                    $error = trim($res['IN097002Return']['error']['ERRNUM']);
                                    
                                    if($error=='0' || $error==''){
                                            $referencia = $res['IN097002Return']['error']['ERDF01'];
                                            $autorizacion = trim($res['IN097002Return']['error']['ERDS01']);
                                    
                                            if (strlen($autorizacion) > 6){
                                                $this->data['PagosSeniat']['autorizacion'] = substr($autorizacion,0,6).'-'.substr($autorizacion,6);
                                            }else{
                                                $this->data['PagosSeniat']['autorizacion'] = $autorizacion;
                                            }
                                            $this->data['PagosSeniat']['concepto'] = $descripcionError;
                                            $this->data['PagosSeniat']['estatus'] = 'PROCESADO';
                                            $this->data['PagosSeniat']['referencia'] = $referencia;

                                            // Email Orden
                                            // Email Options
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = desencriptar($usuario['email_principal']);
                                            $this->Email->subject = 'Notificacion Pago de Impuesto';
                                            $this->Email->sendAs = 'html';

                                            // Informacion del correo
                                            $this->Email->template = 'pagoseniat_ordenante';
                                            $this->set(compact('usuario', 'descripcion'));
                                            $this->set('data', $this->data['PagosSeniat']);

                                            // Envio del correo
                                            $this->Email->send();
                                            
                                            if($transaccion=='100037'){
                                                // Enviar por sms la notificación de que realizó el pago al ordenante		
                                                $this->Sms->enviar(
                                                    array(
                                                        'numcel'=>desencriptar($usuario['celular']),
                                                        'canal1'=>$tipoTransferencia,
                                                        'xrnde38'=>'IB',
                                                        'fectim'=>'INTERNET BANKING',                                                            
                                                        'cuenta'=>substr($this->data['PagosSeniat']['cuentaDebitar'],16,4),
                                                        'monto'=>formatea_monto_toWs($this->data['PagosSeniat']['monto']),
                                                        'fecha'=>date('Ymd'),
                                                        'hora'=>date('His'),
                                                        'refnum'=>substr($referencia,5,4)
                                                    )
                                                );
                                            }                                            
                                            
                                            $this->Bitacora->registraBitacora(
                                                    $usuario['id'], 
                                                    $transaccion, 
                                                    $this->data['PagosSeniat']['cuentaDebitar'], 
                                                    $this->data['PagosSeniat']['rif'], 
                                                    $this->data['PagosSeniat']['monto'], 
                                                    $this->data['PagosSeniat']['referencia'],
                                                    $descripcionError,
                                                    0,
                                                    $this->data['PagosSeniat']['autorizacion']);

                                            $this->log('Pago SENIAT - ' . $descripcionError . " - " . $referencia . " - " . $autorizacion, LOG_ERR);
                                            // Genera la data para la descarda de pdf
                                            $this->Session->write('seniat.descarga.pdf', $this->data['PagosSeniat']);
                                            $this->set('error', $error);

                                    } else {
                                            $error = trim($res['IN097002Return']['error']['ERNU01']);
                                            $descripcionError = trim($res['IN097002Return']['error']['ERNU01']) . ' - ' . $res['IN097002Return']['error']['ERDS01'];

                                            //Verificar si tiene codigo de error spice o seniat
                                            $pos = strpos($descripcionError, '(');
                                            if($pos){
                                                $codigoError = substr($descripcionError,$pos);
                                            }else if($error!= "ST1"){
                                                $codigoError = '(S'.$error.')';
                                            }

                                            $this->log('Pago SENIAT - ' . $descripcionError, LOG_ERR);
                                            $this->Bitacora->registraBitacora(
                                                    $usuario['id'], 
                                                    $transaccion, 
                                                    $this->data['PagosSeniat']['cuentaDebitar'], 
                                                    $this->data['PagosSeniat']['rif'], 
                                                    $this->data['PagosSeniat']['monto'], 
                                                    $referencia, 
                                                    $descripcionError, 
                                                    1);
                                            $this->set(compact('error', 'descripcionError','codigoError'));

                                    }

                                } else {
                                    $error = '-1';
                                    $descripcionError = "";
                                    $codigoError = "";

                                    $this->log('Pago SENIAT - Error invocando IN097002', LOG_ERR);
                                    $this->Bitacora->registraBitacora(
                                            $usuario['id'], 
                                            $transaccion, 
                                            $this->data['PagosSeniat']['cuentaDebitar'], 
                                            $this->data['PagosSeniat']['rif'], 
                                            $this->data['PagosSeniat']['monto'], 
                                            $referencia, 
                                            "Error en la ejecución del servicio IN097002", 
                                            1);
                                    $this->set(compact('error', 'descripcionError','codigoError'));
                                }
                        }else if($usuario['rol_juridico_id'] == 3  || $usuario['rol_juridico_id'] == 5){//operador y operador aprobador
				// En caso de jurídico, sólo los operadores y operadores/aprobadores pueden
				// generar un pago. En cuyo caso, se deja pendiente por aprobar
				
				$error = 0;
				$descripcionTP = trim($this->data['PagosSeniat']['id']).'?'.
                                                trim($this->data['PagosSeniat']['forma']).'?'.
                                                trim($this->data['PagosSeniat']['planilla']).'?'.
                                                trim($this->data['PagosSeniat']['periodo']).'?'.
                                                trim($impuesto[0]).'?'.
                                                trim($impuesto[1]).'?'.
                                                trim($codAduana).'?'.
                                                trim($nomAduana).'?'.
                                                trim($this->data['PagosSeniat']['expediente']).'?'.
                                                trim($this->data['PagosSeniat']['porcion']).'?'.
                                                trim($this->data['PagosSeniat']['vencimiento']); 
                                
				$res = $this->TransaccionPendiente->save(
					array(
						'TransaccionPendiente'=>array(
							'usuario_id'=>$usuario['id'],
							'tipo'=>'i'.trim($this->data['PagosSeniat']['tipo']),
							'estatus'=>'pa',
							'fecha_hora_registro'=>date('Y-m-d H:i:s'),
							'monto'=>formatea_monto_toWs($this->data['PagosSeniat']['monto']),
							'aprobaciones_requeridas'=>$this->data['PagosSeniat']['aprobaciones_requeridas'],
							'tipo_rif'=>$usuario['tipo_rif'],
							'rif'=>$usuario['rif'],
							'cuenta_origen'=>encriptar($this->data['PagosSeniat']['cuentaDebitar']),
							'cuenta_destino'=>encriptar($this->data['PagosSeniat']['rif']),
							'descripcion'=> encriptar($descripcionTP),
							'nombre_beneficiario'=>encriptar($this->data['PagosSeniat']['beneficiario'])
						)
					),
					false
				);
				
				if($res){
					$error = 0;
					$referencia = $this->TransaccionPendiente->id;
				} else {
					$error = -1;
					$referencia = '';
				}
				$this->data['PagosSeniat']['referencia'] = $referencia;
				$this->set('pendiente',true);
			}
			
			$this->set(compact('error', 'referencia'));

			$this->Session->delete('pagos_seniat.pago');
                        
		} else {
                        if($this->Session->read('tipo') == 'p'){
                            $this->redirect('compromisos/p');
                        }else{
                            $this->redirect('terceros_listado');
                        }
		}
	}
	
        /**
         *Acción para la descarga del archivo pdf 
         */
	function descargar_pdf(){
            
		if(!$this->Session->check('seniat.descarga.pdf') && $this->Session->check('usuarios.datos')){
			$this->redirect('/consultas/integral');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$data = $this->Session->read('seniat.descarga.pdf');

                $this->set(compact('data','usuario'));
		$this->render('/elements/descargar_pdf_seniat');
	}
        
}