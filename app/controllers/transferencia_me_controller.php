<?php
/**
 * Controlador para las operaciones de transferencias electrï¿½nicas.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Operaciones de transferencias electrï¿½nicas.
 * 
 */
class transferenciaMeController extends AppController {	        
        
	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array(
		'Afiliacion',
		'Usuario',
		'Transferencia',
		'Parametro',
		'Bitacora',
		'AcumuladoTransaccion',
		'TransaccionPendiente',
		'TransaccionJuridicoDetalle'
	);

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session', 'Email', 'Compensacion');

	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */
	var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');

	
	/**
	 * Metodo inicial para realizar transferencias a cuentas propias. Busca la 
	 * informacion de las cuentas.
	 * 
	 * @param string $volver Indica si esta regresando de la confirmacion
	 */
	function propias($volver=null){		
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101070)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */
		$this->Session->delete('usuarios.descarga.pdf');
		$this->layout = 'interna';
		$this->set('seccion_actual','transfme.propias');
            if(!empty($this->data['Transferencia'])){
		$usuario = $this->Session->read('usuarios.datos');	
		// Para usuario jurodicos operadores, obtener su configuracion para este tipo de transaccion
		if(
                    $usuario['rol_juridico_id'] == 3 ||
                    $usuario['rol_juridico_id'] == 5
		){
                    $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                    $usuario['id'],
                    101070,
                    $this->data['Transferencia']['cuentaDebitar']
                    );
				
                    if(!$transaccion_detalle) {
                        $this->Transferencia->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                    }
		}
			
                if(empty($this->Transferencia->validationErrors)){
                    if(isset($transaccion_detalle)){
                        $this->data['Transferencia']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if(is_numeric($transaccion_detalle['limite'])){
                        $this->data['Transferencia']['monto_limite_usuario']=formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['Transferencia']['monto_limite_usuario']='';
                        }
                    }
			
                    $this->Transferencia->set($this->data);

                    if($this->Transferencia->validates(array('fieldList'=>array('cuentaDebitar', 'cuentaCreditar', 'monto')))){
			$this->Session->write('transferenciaMe.propias', $this->data);
			$this->redirect('propias_confirmacion');
                    }
                }
            
            }else if(isset($volver) && $this->Session->check('transferenciaMe.propias')){
                $this->data = $this->Session->read('transferenciaMe.propias');
            }
            $cuentas = $this->Activo->buscarCuentasSaldosMe();
            $cta_acreditar = $this->Activo->buscarCuentasSaldosMe(true);
            $this->set(compact('cuentas','cta_acreditar'));
	}

	
	/**
	 * Metodo para realizar la confirmaciï¿½n de la transferencia a cuentas propias.
	 */
	function propias_confirmacion(){

            $this->layout = 'interna';

            $this->set('seccion_actual','transfme.propias');	

            if($this->Session->check('transferenciaMe.propias')){
                $this->data = $this->Session->read('transferenciaMe.propias');
            }else {
		$this->redirect('propias');
            }	
	}

	
	/**
	 * Mï¿½todo para realizar el proceso de transferencia a cuentas propias.
	 */
	function propias_resultado(){
		
            $this->layout = 'interna';
                
            $this->set('seccion_actual','transfme.propias');

            if($this->Session->check('transferenciaMe.propias')){
                
                $this->data = $this->Session->read('transferenciaMe.propias');
                // Validaciï¿½n anti fraude
		$cuentas = $this->Activo->buscarCuentasME();	
		if(
                    !in_array($this->data['Transferencia']['cuentaDebitar'], array_keys($cuentas))
		){
                    $this->redirect('/consultas/integral');
		}
		//  Fin validacion anti fraude
                $usuario = $this->Session->read('usuarios.datos');

                $productoDvalidar = substr($this->data['Transferencia']['cuentaDebitar'],-10);
                $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar,12,'0',STR_PAD_LEFT), 'NROPARAM' => 2);	
                $datosD = $this->Activo->validarProducto($datosDproducto);                
                // Capturar la moneda 
                $this->data['Transferencia']['moneda'] = $datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'];                    
                $moneda = $datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'];
                $cuentaDebitar = substr($this->data['Transferencia']['cuentaDebitar'] ,10);
                $cuentaCreditar = substr($this->data['Transferencia']['cuentaCreditar'] ,10);
                $cuentaDebitarA = $this->data['Transferencia']['cuentaDebitar'];
                $cuentaCreditarA = $this->data['Transferencia']['cuentaCreditar'];

                $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);

                // Identificar tipo de usuario (natural o juridico)			
                if(
                    empty($usuario['tipo_rif']) ||
                    $usuario['rol_juridico_id'] == 2 // Master Unico
                ){
                    // Usuario natural o juridico unico
                    $res = $this->Activo->invoca(
                        'INTRFDSJV6',
                            array(
                                'req'=>array(
                                'INTUSR6'=>$identificacion,
                                'INTFRMACC6'=>$cuentaDebitar,//de
                                'INTTOACC6'=>$cuentaCreditar,//para
                                'INTTRFAMT6'=>formatea_monto_toWs($this->data['Transferencia']['monto']),
                                'INTTYPE6'=>'0',
                                'INTCANAL6'=>'04',
                                'INTCCY6'=>$moneda,
                                'canal6'=>Configure::read('canal.ib'),
                                'operacion6'=>'p',
                                'INTDIP6' => Configure::read('ip.client')
                            )
                        )
                    );


                        $referencia = $res['INTRFDSJV6Return']['error']['ERDF01'];

                        $error = "-1";
                        $descripcion = "Error inesperado, transaccion no disponible.";
                        if(isset($res['INTRFDSJV6Return'])){
                            $error = intval($res['INTRFDSJV6Return']['error']['ERRNUM']);
                            $error = ($error == 0) ? $error : intval($res['INTRFDSJV6Return']['error']['ERNU01']);
                            $descripcion = $res['INTRFDSJV6Return']['error']['ERNU01'] . ' - ' . $res['INTRFDSJV6Return']['error']['ERDS01'];
                        }

                        if($error == 0){		
                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                101070,
                                $cuentaDebitarA,
                                $cuentaCreditarA,
                                $this->data['Transferencia']['monto'],
                                $referencia,
                                NULL,
                                0
                            );

                        $datos_basicos = $this->Session->read('usuarios.nibpf3010');			
                        if($usuario['tipo_rif']){
                            $nombre_beneficiario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                        } else {
                            $nombre_beneficiario = desencriptar($usuario['nombre']);
                        }

                            $this->data['Transferencia']['REFERENCIA'] = $referencia;
                            $this->data['Transferencia']['CONCEPTO'] = 'TRANSFERENCIA PROPIA';
                            $this->data['Transferencia']['TIPO'] = 'tp';
                            $this->data['Transferencia']['NRO_BENEFICIARIO'] = $this->data['Transferencia']['cuentaCreditar'];
                            $this->data['Transferencia']['tipoCedulaRif'] = $usuario['tipo_cedula'];
                            $this->data['Transferencia']['nroCedulaRif'] = desencriptar($usuario['cedula']);
                            $this->data['Transferencia']['nombreBeneficiario'] = $nombre_beneficiario;
                            $this->data['Transferencia']['ESTATUS'] = 'EJECUTADO';
                            $this->data['Transferencia']['descripcion']  = ' - ';
                            $this->data['Transferencia']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
                            // Genera la data para la descarda de pdf
                            $this->Session->write('usuarios.descarga.pdf', $this->data['Transferencia']);

                        } else {
                            $this->log('Transferencias Propias - '. $descripcion, LOG_ERR);				
                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                101070,
                                $cuentaDebitarA,
                                $cuentaCreditarA,
                                $this->data['Transferencia']['monto'],
                                NULL,
                                $descripcion,		1
                            );
                        }
				
                        $this->set('pendiente',false);
                } else if(
                    $usuario['rol_juridico_id'] == 3  || // Operador
                    $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
                ){
                    // En caso de juridico, solo los operadores y operadores/aprobadores pueden
                    // generar una transferencia. En cuyo caso, se deja pendiente por aprobar	
                    $error = 0;	
                    $res = $this->TransaccionPendiente->save(
                        array(
                            'TransaccionPendiente'=>array(
                            'usuario_id'=>$usuario['id'],
                            'tipo'=>'dpm',
                            'estatus'=>'pa',
                            'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                            'monto'=>formatea_monto_toWs($this->data['Transferencia']['monto']),
                            'aprobaciones_requeridas'=>$this->data['Transferencia']['aprobaciones_requeridas'],
                            'tipo_rif'=>$usuario['tipo_rif'],
                            'rif'=>$usuario['rif'],
                            'cuenta_origen'=>encriptar($this->data['Transferencia']['cuentaDebitar']),
                            'cuenta_destino'=>encriptar($this->data['Transferencia']['cuentaCreditar']),
                            'descripcion'=> encriptar(' - '),
                            'nombre_beneficiario'=>$usuario['nombre'],
                            'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                            'id_beneficiario'=>$usuario['rif'],
                            'email_beneficiario'=>$usuario['email_principal']
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

                        $this->set('pendiente',true);
                    }

                    $this->set(compact('error', 'referencia'));

                $this->Session->delete('transfme.propias');
            } else {
		$this->redirect('propias');
            }
	}
	
	/**
	 * Metodo inicial para realizar transferencias a cuentas de terceros.
	 * 
	 * @param string $volver Indica si esta regresando de la confirmacion
	 */
	function terceros($volver=null){
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101071)) $this->redirect('/consultas/integral');
                /* Para validar el acceso al modulo */
                $this->Session->delete('usuarios.descarga.pdf');
		$this->layout = 'interna';

		$this->set('seccion_actual','transfme.terceros');

		if(!empty($this->data['Transferencia'])){
                    $usuario = $this->Session->read('usuarios.datos');
			
                    // Para usuario juridicos operadores, obtener su configuracion para este tipo de transaccion
                    if(
                        $usuario['rol_juridico_id'] == 3 ||
			$usuario['rol_juridico_id'] == 5
                    ){
			$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
			$usuario['id'],
			101071,
                        $this->data['Transferencia']['cuentaDebitar']
                    );
				
                        if(!$transaccion_detalle) {
                            $this->Transferencia->validationErrors['cuentaDebitar']=_m('usuario.no.autorizado');
                        }
                    }
			
                    if(empty($this->Transferencia->validationErrors)){
			if(isset($transaccion_detalle)){
                            $this->data['Transferencia']['aprobaciones_requeridas']=$transaccion_detalle['aprobaciones'];
                            if(is_numeric($transaccion_detalle['limite'])){
                                $this->data['Transferencia']['monto_limite_usuario']=formatea_monto_fromWs($transaccion_detalle['limite']);
                            } else {
				$this->data['Transferencia']['monto_limite_usuario']='';
                            }
			}
				
			$this->Transferencia->set($this->data);
			if($this->Transferencia->validates( array( 'fieldList'=>array( 'cuentaDebitar', 'tipoDestinatario' ) ) )){
					
                            $this->Session->write('transferenciaMe.terceros', $this->data);

                            if($this->data['Transferencia']['tipoDestinatario'] == 'a'){                                                
                                $this->redirect('terceros_afiliados');
                            } else {
				$this->redirect('terceros_noafiliados');
                            }

			}
                    }
                } elseif(isset($volver) && $this->Session->check('transferencia_Me.terceros')){
			$this->data = $this->Session->read('transferencia_Me.terceros');
		}
		
		$cuentas = $this->Activo->buscarCuentasSaldosME();
		
		$this->set(compact('cuentas'));
        }    
        

	
	/**
	 * Metodo para la busqueda de los afiliados para realizar la transferencia 
	 * a cuentas de bancoactivo.
	 * 
	 */
	function terceros_afiliados(){
            
                if(!$this->Session->check('transferenciaMe.terceros')) $this->redirect('terceros');
		
		$estado = $this->Session->read('transferenciaMe.terceros');
		$usuario = $this->Session->read('usuarios.datos');		
                // Obtener el limite de parametros
                
                $productoDvalidar = substr($estado['Transferencia']['cuentaDebitar'],-10);
                $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar,12,'0',STR_PAD_LEFT), 'NROPARAM' => 2);				
		$datosD = $this->Activo->validarProducto($datosDproducto);
		
                $errorD = intval($datosD['NIBPF3010Return']['error']['ERNU01']);
                
                if(empty($usuario['rif'])){
                    if($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA']=='USD'){
			$limite_config = _p('limite_internas_terceros_usd');
                    }else{
                        $limite_config = _p('limite_internas_terceros_eur');
                    }			
		}else{
                    if($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA']=='USD'){
                        $limite_config = _p('limite_internas_terceros_usd_j');
                    }else{
                        $limite_config = _p('limite_internas_terceros_eur_j');
                    }	
		}
                
                /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
		$monto_limite = '';
		$acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id']);
		if($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA']=='USD'){
                    $limite_total = $usuario['limite_internas_terceros_usd'] ? $usuario['limite_internas_terceros_usd'] : $limite_config;
                    if($limite_total){
			$monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_internas_terceros_usd']);
                    }
		}else{
                    $limite_total = $usuario['limite_internas_terceros_eur'] ? $usuario['limite_internas_terceros_eur'] : $limite_config;
                    if($limite_total){
                        $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_internas_terceros_eur']);
                    }
                }
                $limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);
                if(isset($usuario['rif']) && $usuario['rol_juridico_id']!=2){
                    $monto_limite=$limite_total='';
                }		
                $this->set(compact('monto_limite','limite_total'));                		
		
		$this->set('seccion_actual','transfme.terceros');

		if(empty($this->data['Transferencia'])){
                    $this->data = $estado;
		}else if(!isset($this->data['Transferencia']['paginando'])) {
                    /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
                    $this->data['Transferencia']['monto_limite'] = $monto_limite;
                    $this->data['Transferencia']['limite_total'] = $limite_total;
			
                    if(isset($estado['Transferencia']['monto_limite_usuario'])){
                        $this->data['Transferencia']['monto_limite_usuario'] = $estado['Transferencia']['monto_limite_usuario'];
                    }			
                    /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
			
                    $this->Transferencia->set($this->data);

                    if($this->Transferencia->validates(
                        array(
                            'fieldList'=>array(
                            'cuentaCreditar',
                            'monto'
                            )
                        )
                    )){
                        $productoValidar = substr($this->data['Transferencia']['cuentaCreditar'],-10);
                        $datosProducto = array('NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 'NROPARAM' => 2);				
                        $datos = $this->Activo->validarProducto($datosProducto);
                        $error = intval($datos['NIBPF3010Return']['error']['ERNU01']);

                        if(empty($usuario['tipo_rif'])){
                            $tipo_id = $usuario['tipo_cedula'];
                            $nro_id = desencriptar($usuario['cedula']);
                        } else {
                            $tipo_id = $usuario['tipo_rif'];
                            $nro_id = desencriptar($usuario['rif']);
                        }
				
                        if($error != 0){
                            $this->Transferencia->validationErrors['cuentaCreditar'] = _m('valor.invalido', 'el número de cuenta  a acreditar');
                        } elseif(strtolower(trim($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']))==strtolower($tipo_id.str_pad($nro_id,9,'0',STR_PAD_LEFT))){						
                            $this->Transferencia->validationErrors['cuentaCreditar'] = _m('producto.propio');
                        } elseif($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='A' && $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='T') {
		
                            if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
                                $this->Transferencia->validationErrors['cuentaCreditar'] = _m('cuenta.controlada', 'con la cuenta');
                            }
                            else{
                                $this->Transferencia->validationErrors['cuentaCreditar'] = _m('cuenta.inactiva', 'la cuenta del beneficiario');
                            }
                            
                        }else{
                            $this->data['Transferencia']['moneda'] = $datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'];                            
                            $this->data['Transferencia']['afiliar'] = 's';// Para afiliar alias
                            if(isset($estado['Transferencia']['aprobaciones_requeridas'])) $this->data['Transferencia']['aprobaciones_requeridas'] = $estado['Transferencia']['aprobaciones_requeridas'];
					
                            if(_p('sms.otp.activo')=='0'){
                                $validacion_otp['validado'] = true;
                            }else{
                                $validacion_otp = $this->Session->read('seguridad.validar_otp');
                            }                                        
                                        
                            if((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])){                                                
                                if(_p('sms.otp.activo')=='0'){
                                    $this->data['pasos'] = array(
                                    'Solicitud de Informacion',
                                    'Confirmacion',                                            
                                    'Resultado de Operacion'
                                    );
                                    $this->data['paso_final'] = 2;
                                }else{
                                    $this->data['pasos'] = array(
                                    'Solicitud de Informacion',
                                    'Confirmacion',
                                    'Codigo de Autorizacion',
                                    'Resultado de Operacion'
                                    );
                                    $this->data['paso_final'] = 3;
                                }       
                            }else{
                                    $this->data['pasos'] = array(
                                    'Solicitud de Informacion',
                                    'Confirmacion',
                                    'Resultado de Operacion'
                                    );
                                    $this->data['paso_final'] = 2;
                                }                                                                             					
							
                                $this->Session->write('transferenciaMe.terceros', $this->data);
					
                                $this->Session->delete('transaccion.advertencia');
					
                                // En caso de ser un usuario natural o master unico
                                if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2){
                                    if( $this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Transferencia'], 101071) ){

					$this->data['titulo']= 'Transferencia Moneda Extranjera Tercero en Activo';
					$this->data['mensaje_alerta'] = 'transferencia.alerta';
					$this->data['url_volver'] = '/transferencia_me/terceros_afiliados';
					$this->data['url_continuar'] = '/transferencia_me/terceros_confirmacion';
					$this->data['seccion_actual'] = 'transfme.terceros';

					$this->Session->write('transaccion.advertencia', $this->data);
					$this->redirect('transaccion_advertencia');
                                    }
				}
					
                            $this->redirect('terceros_confirmacion');				
			}
                    }
		}
		
		$afiliados = $this->Afiliacion->obtenerListaAfiliados($usuario['id'], 'd', 't');
                
		
		if($afiliados){
                    // Empieza codigo para paginar
                    $total_registros = count($afiliados);
                    $offset = 0;

                    if(
			isset($this->data['Transferencia']['offset']) &&
                            preg_match('/^\d+$/',$this->data['Transferencia']['offset'])
			){
                            $offset = intval($this->data['Transferencia']['offset']);			
			}

			$resultados = array_slice(
                            $afiliados,
                            $offset,
                            5,
                            true
			);
		// Fin codigo para paginar
		}
		
            $this->set(compact('afiliados','resultados','total_registros'));
	
            $this->layout = 'interna';
		
	}	
	
	
	/**
	 * Metodo para el formulario de afiliacion del beneficiario de la transferencia
	 * a cuentas de bancoactivo.
	 * 
	 */
	function terceros_noafiliados(){
		
            if(!$this->Session->check('transferenciaMe.terceros')) $this->redirect('terceros');		                          
            $estado = $this->Session->read('transferenciaMe.terceros');
            $usuario = $this->Session->read('usuarios.datos');
            $productoDvalidar = substr($this->data['Transferencia']['cuentaDebitar'],-10);

            $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar,12,'0',STR_PAD_LEFT), 'NROPARAM' => 2);				
            $datosD = $this->Activo->validarProducto($datosDproducto);
            $errorD = intval($datosD['NIBPF3010Return']['error']['ERNU01']);

            // Obtener el limite de parametros
            if(empty($usuario['rif'])){
                if($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA']=='USD'){
                    $limite_config = _p('limite_internas_terceros_usd');
                }else{
                    $limite_config = _p('limite_internas_terceros_eur');
                }			
            }else{
                if($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA']=='USD'){
                    $limite_config = _p('limite_internas_terceros_usd_j');
                }else{
                    $limite_config = _p('limite_internas_terceros_eur_j');
                }	
            }

            /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
            $monto_limite = '';
            $acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id']);
            if($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA']=='USD'){
                $limite_total = $usuario['limite_internas_terceros_usd'] ? $usuario['limite_internas_terceros_usd'] : $limite_config;
                if($limite_total){
                    $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_internas_terceros_usd']);
                }
            }else{
                $limite_total = $usuario['limite_internas_terceros_eur'] ? $usuario['limite_internas_terceros_eur'] : $limite_config;
                if($limite_total){
                    $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_internas_terceros_eur']);
                }
            }
            $limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);
            if(isset($usuario['rif']) && $usuario['rol_juridico_id']!=2){
                $monto_limite=$limite_total='';
            }		
            $this->set(compact('monto_limite','limite_total'));
            /** PARA EL MONTO LIMITE EN LA TRANSACCION **/			

            $this->set('seccion_actual','transfme.terceros');

            if(empty($this->data['Transferencia'])){
                $this->data = $estado;
            } else if(!preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/',$this->data['Transferencia']['cuentaCreditar'])){
                $this->Transferencia->validationErrors['cuentaCreditar'] = _m('valor.invalido', 'el numero de cuenta a acreditar');
            } else {
                $this->data['Transferencia']['moneda'] = $datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'];
                $productoValidar = substr($this->data['Transferencia']['cuentaCreditar'],-10);

                $datosProducto = array('NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 'NROPARAM' => 2);				
                $datos = $this->Activo->validarProducto($datosProducto);
                $error = intval($datos['NIBPF3010Return']['error']['ERNU01']);

                if(empty($usuario['tipo_rif'])){
                    $tipo_id = $usuario['tipo_cedula'];
                    $nro_id = desencriptar($usuario['cedula']);
                } else {
                    $tipo_id = $usuario['tipo_rif'];
                    $nro_id = desencriptar($usuario['rif']);
                }

                if($error != 0){
                    $this->Transferencia->validationErrors['cuentaCreditar'] = _m('valor.invalido', 'el numero de cuenta a acreditar');
                }elseif($datos['NIBPF3010Return']['NIBPF3010']['MONEDA']=='VEB') {
                    $this->Transferencia->validationErrors['cuentaCreditar'] = _m('pago.nopermitido');			                              
                }elseif($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='A' && $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='T') {                     
                if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
                    $this->Transferencia->validationErrors['cuentaCreditar'] = _m('cuenta.controlada', 'con la cuenta del beneficiario');
                }else{
                    $this->Transferencia->validationErrors['cuentaCreditar'] = _m('cuenta.inactiva', 'la cuenta del beneficiario');
                }
                }elseif(strtolower(trim($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']))==strtolower($tipo_id.str_pad($nro_id,9,'0',STR_PAD_LEFT))){						
                    $this->Transferencia->validationErrors['cuentaCreditar'] = _m('producto.propio');
                }else {
                    // Completar el tamano de la cuenta
                     if(10==strlen($this->data['Transferencia']['cuentaCreditar'])){
                        $res = $this->Activo->completaCuenta($this->data['Transferencia']['cuentaCreditar']);
                        if($res){
                         $this->data['Transferencia']['cuentaCreditar'] = str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT);
                        }
                    }


                    $cedula=substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],1,9);

                    $this->data['Transferencia']['tipoCedulaRif'] = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],0,1);

                    if($this->data['Transferencia']['tipoCedulaRif'] == 'G' || $this->data['Transferencia']['tipoCedulaRif'] == 'J'){
                        $this->data['Transferencia']['nombreBeneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                    }else{
                        $this->data['Transferencia']['nombreBeneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'].' '.$datos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'];
                    }	
                    $this->data['Transferencia']['nroCedulaRif'] = $cedula;

                    /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
                    $this->data['Transferencia']['monto_limite'] = $monto_limite;

                    if(empty($usuario['rif'])) {
                        $this->data['Transferencia']['limite_total'] = $limite_total;
                    }else{
                        $this->data['Transferencia']['monto_maximo'] = $limite_total;
                    }

                    if(isset($estado['Transferencia']['monto_limite_usuario'])){
                        $this->data['Transferencia']['monto_limite_usuario'] = $estado['Transferencia']['monto_limite_usuario'];
                    }

                    /** PARA EL MONTO LIMITE EN LA TRANSACCION **/

                    $this->Transferencia->set($this->data);
                    if($this->Transferencia->validates(
                        array(
                            'fieldList'=>array(
                            'cuentaCreditar',
                            'emailBeneficiario',
                            'monto',
                            'descripcion',
                            'alias',
                            'afiliar',
                            )
                        )
                    )){

                        if($this->data['Transferencia']['afiliar'] == 's' && $this->Afiliacion->checkAfiliacion($usuario['id'],$this->data['Transferencia']['cuentaCreditar'])){
                            $this->Transferencia->validationErrors['cuentaCreditar'] = 'producto_nodisponible';
                        } else if(isset($this->data['Transferencia']['alias']) && $this->data['Transferencia']['alias'] != "" && $this->Afiliacion->checkAlias($usuario['id'],$this->data['Transferencia']['alias'])
                        ){
                            $this->Transferencia->validationErrors['alias'] = 'alias_nodisponible';
                        }else{
                            if(isset($estado['Transferencia']['aprobaciones_requeridas'])) $this->data['Transferencia']['aprobaciones_requeridas'] = $estado['Transferencia']['aprobaciones_requeridas'];
                                $validacion_otp = $this->Session->read('seguridad.validar_otp');
                            if((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado']))
                            {
                                $this->data['pasos'] = array(
                                'Solicitud de Informacion',
                                'Confirmacion',
                                'Codigo de Autorizacion',
                                'Resultado de Operacion'
                                );
                                $this->data['paso_final'] = 3;
                            }else{
                                $this->data['pasos'] = array(
                                'Solicitud de Informacion',
                                'Confirmacion',
                                'Resultado de Operacion'
                                );
                                $this->data['paso_final'] = 2;
                            } 

                            $this->Session->write('transferenciaMe.terceros', $this->data);
                            $this->Session->delete('transaccion.advertencia');
                            // En caso de ser un usuario natural o master unico
                            if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2){
                                if( $this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Transferencia'], 101071) ){
                                    $this->data['titulo']= 'Transferencia Moneda Extranjera Tercero en Activo';
                                    $this->data['mensaje_alerta'] = 'transferencia.alerta';
                                    $this->data['url_volver'] = '/transferencia/terceros_noafiliados';
                                    $this->data['url_continuar'] = '/transferencia/terceros_confirmacion';
                                    $this->data['seccion_actual'] = 'transferencia.terceros';
                                    $this->Session->write('transaccion.advertencia', $this->data);
                                    $this->redirect('transaccion_advertencia');
                                }
                            }

                            $this->redirect('terceros_confirmacion');            
                        }
                    }
                }
            }
            $this->layout = 'interna';
        }
	
	
	/**
	 * Método para la confirmación de la transferencia a cuentas de terceros.
	 * 
	 */
	function terceros_confirmacion(){
		
            if(!$this->Session->check('transferenciaMe.terceros')) $this->redirect('terceros');
		
                $estado = $this->Session->read('transferenciaMe.terceros');
                $this->layout = 'interna';
                $this->set('seccion_actual','transfme.terceros');	
                $usuario = $this->Session->read('usuarios.datos');
                $this->set('juridico',$usuario);
                $this->data = $estado;                                                                
                           
                if($this->data['Transferencia']['tipoDestinatario'] == 'a'){
			
                $afiliado = $this->Afiliacion->obtenerDataAfiliado($usuario['id'],$this->data['Transferencia']['cuentaCreditar']);
                if(!$afiliado){
                   $this->redirect('terceros');
                }                                                

                    $this->data['Transferencia']['nombreBeneficiario'] = desencriptar($afiliado['nombre_beneficiario']);
                    $this->data['Transferencia']['tipoCedulaRif'] = $afiliado['tipo_cedula'];
                    $this->data['Transferencia']['nroCedulaRif'] = desencriptar($afiliado['cedula']);
                    $this->data['Transferencia']['emailBeneficiario'] = desencriptar($afiliado['email']);
                    $this->data['Transferencia']['id'] = $afiliado['id'];
                    $this->Session->write('transferenciaMe.terceros', $this->data);
                }else{
                    $this->data['Transferencia']['id'] = null;
                    $this->Session->write('transferenciaMe.terceros', $this->data);
                }
	}
	
        /**
         * Método para el proceso de la transferencia a cuentas de terceros.
         * 
         */
        function terceros_resultado(){

            if(!$this->Session->check('transferenciaMe.terceros')) $this->redirect('terceros');

            $estado = $this->Session->read('transferenciaMe.terceros');				

            $usuario = $this->Session->read('usuarios.datos');

            $this->data = $estado;
            
            // Validación anti fraude
            $cuentas = $this->Activo->buscarCuentasMe();

            if(
                !in_array($this->data['Transferencia']['cuentaDebitar'], array_keys($cuentas))
            ){
                $this->redirect('/consultas/integral');
            }
            //  Fin validacion anti fraude		

            $this->layout = 'interna';

            $this->set('seccion_actual','transfme.terceros');

            if($this->Session->check('transferenciaMe.terceros')){
                
                $this->data = $this->Session->read('transferenciaMe.terceros');

                $usuario = $this->Session->read('usuarios.datos');
                $cuentaDebitar = substr($this->data['Transferencia']['cuentaDebitar'] ,10);
                $cuentaCreditar = substr($this->data['Transferencia']['cuentaCreditar'] ,10);
                $cuentaDebitarA = $this->data['Transferencia']['cuentaDebitar'];//para bitï¿½cora
                $descripcion= $this->data['Transferencia']['descripcion'];
                $moneda = $this->data['Transferencia']['moneda'];

                $this->set('juridico',$usuario['tipo_rif']);

                $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
                
                if(
                    empty($usuario['tipo_rif']) ||
                    $usuario['rol_juridico_id'] == 2 // Master Único
                ){							
                    if(_p('sms.otp.activo')=='0' && $this->data['Transferencia']['tipoDestinatario']=='a'){
                        $validacion_otp['validado'] = true;
                    }else{
                        $validacion_otp = $this->Session->read('seguridad.validar_otp');
                    } 
                    
                    if(($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])){
                        
                        if($this->data['Transferencia']['tipoDestinatario'] == 'a'){ 
                            $url_volver = '/transferencia_me/terceros_afiliados';
                        }else{
                            $url_volver = '/transferencia_me/terceros_noafiliados';
                        }

                        $this->Session->write(
                            'seguridad.validar_otp',
                                array(
                                    'url_aceptar'=>'/transferencia_me/terceros_resultado',
                                    'url_volver' => $url_volver,
                                    'layout'=>'interna',
                                    'titulo'=>'Transferencia Tercero Moneda Extranjera en Activo',
                                    'pasos'=> $estado['pasos'],
                                    'paso_actual'=>2,
                                    'seccion_actual'=>'transfme.terceros'
                                )
                        );

                        $this->redirect('/seguridad/genera_otp');
                    }

                    $res = $this->Activo->invoca(
                        'INTRFDSJV6',
                        array(
                            'req'=>array(
                                'INTUSR6'=>$identificacion,
                                'INTFRMACC6'=>$cuentaDebitar,//de
                                'INTTOACC6'=>$cuentaCreditar,//para
                                'INTTRFAMT6'=>formatea_monto_toWs($this->data['Transferencia']['monto']),
                                'INTTYPE6'=>'0',
                                'INTCANAL6'=>'04',
                                'INTCCY6'=>$moneda, 
                                'INTDESC6'=>$descripcion,
                                'canal'=>Configure::read('canal.ib'),
                                'operacion'=>'t',
                                'INTDIP6' => Configure::read('ip.client')
                            )
                        )
                    );
                                        
                    $referencia = $res['INTRFDSJV6Return']['error']['ERDF01'];
                    $error = -1;
                    $descripcion = "";
                    $error_afiliacion = false;
                    
                    if(isset($res['INTRFDSJV6Return'])){
                        $error = intval($res['INTRFDSJV6Return']['error']['ERRNUM']);                          
                        $error = ($error == 0) ? $error : intval($res['INTRFDSJV6Return']['error']['ERNU01']);                        
                        $descripcion = $res['INTRFDSJV6Return']['error']['ERNU01'] . ' - ' . $res['INTRFDSJV6Return']['error']['ERDS01'];
                    }

                    if($error == 0 ){
                        
                        /** ACTUALIZA EL ACUMULADO DEL DIA **/
                        if($moneda=='USD'){
                            $acumME = 'itmeusd';
                        }else{
                            $acumME = 'itmeeur';
                        }
                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], $acumME, $this->data['Transferencia']['monto']); 
                        /** ACTUALIZA EL ACUMULADO DEL DIA **/
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                                    101071, 
                            $cuentaDebitarA,
                            $this->data['Transferencia']['cuentaCreditar'], 
                            $this->data['Transferencia']['monto'],
                            $referencia,
                            $this->data['Transferencia']['descripcion'],
                            0,
                            NULL,
                            $this->data['Transferencia']['tipoCedulaRif'],
                            $this->data['Transferencia']['nroCedulaRif'],
                            $this->data['Transferencia']['nombreBeneficiario']       
                        );
                        
                        $this->data['Transferencia']['REFERENCIA'] = $referencia;
                        $this->data['Transferencia']['CONCEPTO'] = $this->data['Transferencia']['descripcion'];
                        $this->data['Transferencia']['TIPO'] = '';
                        $this->data['Transferencia']['NRO_BENEFICIARIO'] = $this->data['Transferencia']['cuentaCreditar'];
                        $this->data['Transferencia']['ESTATUS'] = 'EJECUTADO';
                        $this->data['Transferencia']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.' ;
                        $this->data['Transferencia']['MONEDA'] = $moneda;

                        // Genera la data para la descarda de pdf
                        $this->Session->write('usuarios.descarga.pdf', $this->data['Transferencia']);

                        // Email Beneficiario
                        // Email Options
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery	= 'smtp';
                        $this->Email->from		= Configure::read('email.from');
                        $this->Email->to		= desencriptar($usuario['email_principal']);
                        $this->Email->subject	= 'Notificacion Transferencia';
                        $this->Email->sendAs	= 'html';

                        // Informacion del correo
                        $this->Email->template	= 'transferencia_ordenante';
                        $tipo_accion = 'Transferencia';
                        $tipo_afiliacion = 'Terceros en Activo';
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'moneda'));

                        // Envio del correo
                        $this->Email->send();

                        // Email Options
                        $this->Email->reset();
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery	= 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = $this->data['Transferencia']['emailBeneficiario'];
                        $this->Email->subject = 'Notificacion Transferencia';
                        $this->Email->sendAs = 'html';

                        // Informacion del correo
                        $this->Email->template	= 'transferencia_beneficiario';
                        $tipo_accion = 'Transferencia';
                        $tipo_afiliacion = 'Terceros en Activo';
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'moneda'));


                        // Envio del correo
                        $this->Email->send();				
                    }else{
                        $this->log('Transferencias Terceros - '. $descripcion, LOG_ERR);
                        $this->Bitacora->registraBitacora(
                        $usuario['id'],
                                101071,
                                $cuentaDebitarA,
                                $this->data['Transferencia']['cuentaCreditar'],
                                $this->data['Transferencia']['monto'],
                                NULL,
                                $descripcion,
                                1
                        );
                    }

                    if($error != 182 && $error != 2149 && $error != 1913 && $error != -1 && $error != 223 && $error != 1912 && $error != 1616 && $this->data['Transferencia']['afiliar'] == 's'){
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 'd', 't', $this->data['Transferencia']['alias'], $this->data['Transferencia']['cuentaCreditar'],
                            $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], 
                            $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], 
                            $this->data['Transferencia']['monto_maximo'],$usuario['tipo_rif'],$usuario['rif'],
                            $this->data['Transferencia']['id']
                        );

                        if(!$error_afiliacion && $this->data['Transferencia']['tipoDestinatario']=='n'){
                            // Email Options
                            $this->Email->smtpOptions = Configure::read('email.smtp');
                            $this->Email->delivery	= 'smtp';
                            $this->Email->from = Configure::read('email.from');
                            $this->Email->to = desencriptar($usuario['email_principal']);
                            $this->Email->subject = 'Notificacion Afiliacion';
                            $this->Email->sendAs = 'html';

                            // Informacion del correo
                            $this->Email->template	= 'afiliacion_producto';
                            $tipo_accion = 'Transferencia';
                            $tipo_afiliacion = 'Terceros en Activo';
                            $nombreBeneficiario = $this->data['Transferencia']['nombreBeneficiario'];
                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                            // Envio del correo
                            $this->Email->send();
                            // Registra en bitacora
                            $this->Bitacora->registraBitacora(
                            $usuario['id'], 
                                13,
                                NULL, 
                                $this->data['Transferencia']['cuentaCreditar'], 
                                $this->data['Transferencia']['monto_maximo']
                            );                                                
                        }else{
                            $this->Bitacora->registraBitacora($usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo'], null, $descripcion, 1);
                        }		

                    }

                    $this->set('pendiente',false);
                
                } else if($usuario['rol_juridico_id'] == 3  || // Operador
                    $usuario['rol_juridico_id'] == 5 // Operador/Aprobador
                ){
                            
                    // En caso de juridico, solo los operadores y operadores/aprobadores pueden
                    // generar una transferencia. En cuyo caso, se deja pendiente por aprobar
                    $error = 0;
                    //Solicitar TC si esta afiliando la cuenta
                    if($this->data['Transferencia']['afiliar'] == 's' && $this->data['Transferencia']['tipoDestinatario']=='n'){
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
                                    'url_aceptar'=>'/transferencia_me/terceros_resultado',
                                    'layout'=>'interna',
                                    'titulo'=>'Transferencia Tercero en Activo',
                                    'pasos'=> array(),                                                                
                                    'paso_actual'=>2,
                                    'seccion_actual'=>'transfme.terceros'                                                                
                            ));

                            $this->redirect('/seguridad/validar_tdc');
                        }
                    }

                    $res = $this->TransaccionPendiente->save(
                        array(
                            'TransaccionPendiente'=>array(
                            'usuario_id'=>$usuario['id'],
                            'tipo'=>'dtm',
                            'estatus'=>'pa',
                            'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                            'monto'=>formatea_monto_toWs($this->data['Transferencia']['monto']),
                            'aprobaciones_requeridas'=>$this->data['Transferencia']['aprobaciones_requeridas'],
                            'tipo_rif'=>$usuario['tipo_rif'],
                            'rif'=>$usuario['rif'],
                            'cuenta_origen'=>encriptar($this->data['Transferencia']['cuentaDebitar']),
                            'cuenta_destino'=>encriptar($this->data['Transferencia']['cuentaCreditar']),
                            'descripcion'=>encriptar($this->data['Transferencia']['descripcion']),
                            'nombre_beneficiario'=>encriptar($this->data['Transferencia']['nombreBeneficiario']),
                            'tipo_id_beneficiario'=>$this->data['Transferencia']['tipoCedulaRif'],
                            'id_beneficiario'=>encriptar($this->data['Transferencia']['nroCedulaRif']),
                            'email_beneficiario'=>encriptar($this->data['Transferencia']['emailBeneficiario']),
                            'alias'=>isset($this->data['Transferencia']['alias'])?encriptar($this->data['Transferencia']['alias']):null
                            )),false
                    );

                    if($res){
                        $error = 0;
                        $referencia = $this->TransaccionPendiente->id;
                    } else {
                        $error = -1;
                        $referencia = '';
                    }
                        
                    if($this->data['Transferencia']['afiliar'] == 's'){                                                                        	
                        
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 'd', 't', $this->data['Transferencia']['alias'], $this->data['Transferencia']['cuentaCreditar'],
                            $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], 
                            $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], 
                            $this->data['Transferencia']['monto_maximo'],$usuario['tipo_rif'],$usuario['rif'],$this->data['Transferencia']['id']
                        );
                        
                        if(!$error_afiliacion && $this->data['Transferencia']['tipoDestinatario']=='n'){
                            // Email Options
                            $this->Email->smtpOptions = Configure::read('email.smtp');
                            $this->Email->delivery	= 'smtp';
                            $this->Email->from = Configure::read('email.from');
                            $this->Email->to = desencriptar($usuario['email_principal']);
                            $this->Email->subject = 'Notificacion Afiliacion';
                            $this->Email->sendAs = 'html';

                            // Informacion del correo
                            $this->Email->template	= 'afiliacion_producto';
                            $tipo_accion = 'Transferencia';
                            $tipo_afiliacion = 'Terceros en Activo';
                            $nombreBeneficiario = $this->data['Transferencia']['nombreBeneficiario'];
                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                            // Envio del correo
                            $this->Email->send();

                            // Registra en bitacora
                            $this->Bitacora->registraBitacora(
                                $usuario['id'], 
                                13,
                                NULL, 
                                $this->data['Transferencia']['cuentaCreditar'], 
                                $this->data['Transferencia']['monto_maximo']
                            );
                        }else{
                            $this->Bitacora->registraBitacora($usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo'], null, $descripcion, 1);
                        }
                                                                                                
                    } else {
                        $error_afiliacion = false;
                    }

                    $this->set('pendiente',true);
                }

                $this->set(compact('error','error_afiliacion','referencia'));
                //$this->Session->delete('transferencia_me.terceros');
                
            } else {
                $this->redirect('terceros');
            }
        }
				
        /**
         * Acción para mostrar advertencia de operación realizada
         * 
         */
        function transaccion_advertencia(){

                if(!$this->Session->check('transaccion.advertencia')) $this->redirect('/consultas/integral');

                $this->data = $this->Session->read('transaccion.advertencia');

                $this->layout = 'interna';

                $this->set('seccion_actual', $this->data['seccion_actual']);

                $this->render('/elements/transaccion_advertencia');
        }
	
}

?>