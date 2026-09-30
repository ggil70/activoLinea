<?php
/**
 * Controlador para las operaciones de intervencion.
 * 
 * @author Emsys
 */


/**
 * Operaciones de intervencion.
 * 
 */
class IntervencionController extends AppController {	        
        
	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array(            
            'Usuario',            
            'Parametro',
            'Bitacora',            
            'TransaccionPendiente',
            'TransaccionJuridicoDetalle',
            'TransaccionIntervencion'            
	);

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session', 'Email', 'Intervencion');

	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */
	var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');

	
	/**
	 * Metodo inicial para realizar compra de divisas. Busca la 
	 * informacion de las cuentas.
	 * 
	 * @param string $volver Indica si esta regresando de la confirmacion
	 */
	function compra($volver=null){	
           
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101088)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */
            
            $this->Session->delete('usuarios.descarga.pdf');
            $this->layout = 'interna';
            $this->set('seccion_actual','comprar.divisas.int');
            $sw_cuentaDebitar = 0;
            $errorDivisa = 0;
            $error = 1;
            
            if(!empty($this->data['Intervencion'])){
                
                $monto = str_replace('.', '', $this->data['Intervencion']['monto_divisa']);
                $arrayMonto = explode(',', $monto);
                if($arrayMonto[0]=='0'){
                   $this->TransaccionIntervencion->validationErrors['monto_divisa'] = 'sin_cero';
                   $errorDivisa=1;
                }
        		
                $usuario = $this->Session->read('usuarios.datos');	
		 // Para usuario jurodicos operadores, obtener su configuracion para este tipo de
                 // transaccion

		if(
                    $usuario['rol_juridico_id'] == 3 ||
                    $usuario['rol_juridico_id'] == 5
		){
                    $sw_cuentaDebitar = 1; 
                    $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                        $usuario['id'],
                        101088,
                        $this->data['Intervencion']['cuentaDebitar']
                    );
                    
                    if(!$transaccion_detalle) {
                        $sw_cuentaDebitar = 1;
                        $this->TransaccionIntervencion->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                    }
	        }

                if(empty($this->TransaccionIntervencion->validationErrors)){
                    if(isset($transaccion_detalle)){
                        $this->data['Intervencion']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if(is_numeric($transaccion_detalle['limite'])){
                        $this->data['Intervencion']['monto_limite_usuario']=formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['Intervencion']['monto_limite_usuario']='';
                        }
                    }
			
                    $this->TransaccionIntervencion->set($this->data);

                    if($this->TransaccionIntervencion->validates(array('fieldList'=>array('cuentaDebitar', 'cuentaDestino', 'monto')))){
                         $this->Session->write('intervencion.compra', $this->data);
                         $this->redirect('compra_confirmacion');
                    }
                }
            
            }else if(isset($volver) && $this->Session->check('intervencion.compra')){
                $this->data = $this->Session->read('intervencion.compra');
            }
            
            $cuentas = $this->Activo->buscarCuentasSaldos();
            $cta_acreditar = array();
            $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();
            $origenFondos = $this->Intervencion->consultaParametros(12);
            $motivos= $this->Intervencion->consultaParametros(13);
            $arrayInt = array();
            $intervenciones = $this->Intervencion->consultaIntervenciones();
           
            foreach ($cuentasValidar as $key => $value) {
                $valores = explode('-', $value);
                $valores = array_map('trim', $valores);
                $query = $this->Intervencion->validarProducto($valores[1],1,'VEB',$valores[2]);
                   
                if($query==true){
                    $cta_acreditar[$key.'-'.$query['fee_percentage'].'-'.$query['rate'].'-'.$query['available'].'-'.$valores[2].'-'.$valores[1].'-'.$query['transaction_source_cod'].'-'.$query['transaction_target_cod'].'-'.str_replace("-", "_", $query['exchange_cod']).'-'.date('ymd',strtotime($query['exchange_start'])).'-'.date('ymd',strtotime($query['exchange_end'])).'-'.$query['exchange_type_cod'].'-'.$query['exchange_market_id']] = '**'.substr($key,-4) . ' - ' . $query['name_product'] . ' - ' . $valores[2] . ' - ' . $valores[3];
                    $error = 0;
                }
            }
            
            $this->set(compact('cuentas','cta_acreditar','origenFondos','motivos','sw_cuentaDebitar','errorDivisa','intervenciones','error'));
	}

	
	/**
	 * Metodo para realizar la confirmacion de la compra de divisas.
	 */
	function compra_confirmacion(){

            $this->layout = 'interna';
            $usuario = $this->Session->read('usuarios.datos');
            $this->set('juridico',$usuario['tipo_rif']);
            $this->set('seccion_actual','comprar.divisas.int');	

            if($this->Session->check('intervencion.compra')){                
                $this->data = $this->Session->read('intervencion.compra');                               
            }else {
		$this->redirect('compra');
            }        
	}

	
	/**
	 * Metodo para realizar el proceso de intervencion compra.
	 */
	function compra_resultado(){
		
            $this->layout = 'interna';
                
            $this->set('seccion_actual','comprar.divisas.int');
          
            if($this->Session->check('intervencion.compra')){
                
                $this->data = $this->Session->read('intervencion.compra');
                $cuentaAcreditar = $this->data['Intervencion']['cuentaCreditar'];
                $datosCuenta = explode('-',$cuentaAcreditar);               
                $tasaCuenta = $datosCuenta[2];
                $tasaCuenta = str_replace(",", ".",$tasaCuenta);

                $this->data = $this->Session->read('intervencion.compra');
                // Validacion anti fraude
                $cuentas = $this->Activo->buscarCuentasME();	
                if(
                    !in_array($this->data['Intervencion']['cuentaDebitar'], array_keys($cuentas))
                ){
                    $this->redirect('/consultas/integral');
                }
                //  Fin validacion anti fraude
                $usuario = $this->Session->read('usuarios.datos');
                
                $moneda = $this->data['Intervencion']['moneda'];                
                $cuentaDebitar = str_pad(substr($this->data['Intervencion']['cuentaDebitar'] ,10), 20, "0", STR_PAD_LEFT);
                $cuentaCreditar = str_pad(substr($this->data['Intervencion']['cuentaDestino'] ,10), 20, "0", STR_PAD_LEFT);
                $cuentaDebitarA = $this->data['Intervencion']['cuentaDebitar'];
                $cuentaCreditarA = $this->data['Intervencion']['cuentaDestino'];
                $referencia = "";
                $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
                $origen = explode('-',$this->data['Intervencion']['origenFondos']);
                $motivo = explode('-',$this->data['Intervencion']['motivos']);
                $descripcion = "";

                
                // Identificar tipo de usuario (natural o juridico)			
                if(
                    empty($usuario['tipo_rif']) ||
                    $usuario['rol_juridico_id'] == 2 // Master Unico
                ){
                    
                    $date = date("Y-m-d H:i:s").'000';
                    
                    //Bloqueo y validacion
                    $resBloqueoVal = $this->Activo->invoca(
                        'NIBVINT01',
                        array(
                            'req'=>array(
                                'canal'=>'',
                                'INTCTIPO'=>'VB',
                                'INTCINTE'=>str_replace("_", "-",$this->data['Intervencion']['exchangeCod']),
                                'INTCCTAD'=>$cuentaCreditar,//Divisas
                                'INTCCTAO'=>$cuentaDebitar,//Bs
                                'INTCMTOO'=>formatea_monto_toWs($this->data['Intervencion']['monto']),
                                'INTCMTOC'=>formatea_monto_toWs($this->data['Intervencion']['comision']),
                                'INTCMTOD'=>formatea_monto_toWs($this->data['Intervencion']['monto_divisa']),
                                'INTCTCLI'=>$tasaCuenta,
                                'INTCDDTR'=>$this->data['Intervencion']['transactionTargetCod'],
                                'INTCDOTR'=>$this->data['Intervencion']['transactionSourceCod'],
                                'INTCPROC'=>'01',                                                                                              
                                'INTCORIF'=>$origen[1],
                                'INTCDESF'=>$motivo[1],
                                'INTCFIIN'=>$this->data['Intervencion']['exchangeStart'],
                                'INTCFFIN'=>$this->data['Intervencion']['exchangeEnd']
                            )   
                        )
                    );
                    
                    if(!isset($resBloqueoVal['NIBVINT01Return']['error'])){
                        
                        $referencia = $resBloqueoVal['NIBVINT01Return']['NIBVINT01']['INTCREFE'];
                                                
                        $res = $this->Activo->invoca(
                            'NIBVINT01',
                            array(
                                'req'=>array(
                                    'INTCTIPO'=>'PC',
                                    'INTCINTE'=>str_replace("_", "-",$this->data['Intervencion']['exchangeCod']),
                                    'INTCCTAD'=>$cuentaCreditar,//Divisas
                                    'INTCCTAO'=>$cuentaDebitar,//Bs
                                    'INTCMTOO'=>formatea_monto_toWs($this->data['Intervencion']['monto']),
                                    'INTCMTOC'=>formatea_monto_toWs($this->data['Intervencion']['comision']),
                                    'INTCMTOD'=>formatea_monto_toWs($this->data['Intervencion']['monto_divisa']),
                                    'INTCTCLI'=>$tasaCuenta,
                                    'INTCDDTR'=>$this->data['Intervencion']['transactionTargetCod'],
                                    'INTCDOTR'=>$this->data['Intervencion']['transactionSourceCod'],
                                    'INTCPROC'=>'01',                                                                                              
                                    'INTCORIF'=>$origen[1],
                                    'INTCDESF'=>$motivo[1],
                                    'INTCFIIN'=>$this->data['Intervencion']['exchangeStart'],
                                    'INTCFFIN'=>$this->data['Intervencion']['exchangeEnd'],
                                    'INTCREFE'=>$referencia                                    
                                )   
                            )
                        );                        

                        $error = "-1";
                        $descripcion = "Error inesperado, transaccion no disponible.";

                        if(isset($res['NIBVINT01Return']['error'])){

                            $error = intval($res['NIBVINT01Return']['error']['ERRNUM']);                        
                            $descripcion = $res['NIBVINT01Return']['error']['ERDS01'];

                            $this->log('Intervencion Compra - '. $error . '-' . $descripcion, LOG_ERR);				
                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                101088,
                                $cuentaDebitarA,
                                $cuentaCreditarA,
                                $this->data['Intervencion']['monto'],
                                NULL,
                                $descripcion,		
                                1
                            );                                                        
                            
                            $queryInsert = $this->Intervencion->save($this->data['Intervencion']['producto'],1,$this->data['Intervencion']['cuentaDebitar'],$this->data['Intervencion']['cuentaDestino'],formatea_monto_toWs($this->data['Intervencion']['total']),formatea_monto_toWs($this->data['Intervencion']['monto_divisa']),formatea_monto_toWs($this->data['Intervencion']['tasa']),formatea_monto_toWs($this->data['Intervencion']['comision']),1,'RCZ',formatea_monto_toWs($this->data['Intervencion']['monto_divisa']),formatea_monto_toWs($this->data['Intervencion']['total']),$origen[1],$motivo[1],$referencia,substr($this->data['Intervencion']['cuentaDebitar'] ,10),$this->data['Intervencion']['transactionTargetCod'],$date,$date,str_replace("_", "-",$this->data['Intervencion']['exchangeCod']),$usuario['id'],$this->data['Intervencion']['exchangeMarketId']);
                            
                        }else{                                           

                            $error = 0;

                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                101088,
                                $cuentaDebitarA,
                                $cuentaCreditarA,
                                $this->data['Intervencion']['total'],
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

                            $this->data['Intervencion']['REFERENCIA'] = $referencia;
                            $this->data['Intervencion']['CONCEPTO'] = 'INTERVENCION CAMBIARIA';
                            $this->data['Intervencion']['TIPO'] = 'com';
                            $this->data['Intervencion']['NRO_BENEFICIARIO'] = $this->data['Intervencion']['cuentaDestino'];
                            $this->data['Intervencion']['tipoCedulaRif'] = $usuario['tipo_cedula'];
                            $this->data['Intervencion']['nroCedulaRif'] = desencriptar($usuario['cedula']);
                            $this->data['Intervencion']['nombreBeneficiario'] = $nombre_beneficiario;
                            $this->data['Intervencion']['ESTATUS'] = 'EJECUTADO';
                            $this->data['Intervencion']['descripcion']  = ' - ';
                            $this->data['Intervencion']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
                            // Genera la data para la descarda de pdf
                            $this->Session->write('usuarios.descarga.pdf', $this->data['Intervencion']);
                            
                            $save = $this->TransaccionIntervencion->save(
                                array(
                                    'TransaccionIntervencion'=>array(
                                    'usuario_id'=>$usuario['id'],
                                    'tipo'=>'com',
                                    'estatus'=>'ej',
                                    'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                    'monto_divisa'=>formatea_monto_toWs($this->data['Intervencion']['monto_divisa']),
                                    'tasa'=> formatea_monto_toWs($this->data['Intervencion']['tasa']),
                                    'comision'=> formatea_monto_toWs($this->data['Intervencion']['comision']),
                                    'monto'=>formatea_monto_toWs($this->data['Intervencion']['monto']),
                                    'total'=>formatea_monto_toWs($this->data['Intervencion']['total']),
                                    'moneda'=>$this->data['Intervencion']['moneda'],                                   
                                    'tipo_rif'=>$usuario['tipo_rif'],
                                    'rif'=>$usuario['rif'],
                                    'cuenta_origen'=>encriptar($this->data['Intervencion']['cuentaDebitar']),
                                    'cuenta_destino'=>encriptar($this->data['Intervencion']['cuentaDestino']),
                                    'referencia' => $referencia    
                                    )
                                ),
                                false
                            );
                            
                            $fValor = substr($res['NIBVINT01Return']['NIBVINT01']['INTCFVAL'],0,2).'-'.substr($res['NIBVINT01Return']['NIBVINT01']['INTCFVAL'],2,2).'-'.substr($res['NIBVINT01Return']['NIBVINT01']['INTCFVAL'],4,2);
                            $fechaValor = date('Y-m-d',strtotime($fValor)) . ' 00:00:00';
                            $fProceso = substr($res['NIBVINT01Return']['NIBVINT01']['INTCFPRO'],0,2).'-'.substr($res['NIBVINT01Return']['NIBVINT01']['INTCFPRO'],2,2).'-'.substr($res['NIBVINT01Return']['NIBVINT01']['INTCFPRO'],4,2);
                            $hProceso = substr($res['NIBVINT01Return']['NIBVINT01']['INTCHPRO'],0,2).':'.substr($res['NIBVINT01Return']['NIBVINT01']['INTCHPRO'],2,2).':'.substr($res['NIBVINT01Return']['NIBVINT01']['INTCHPRO'],4,2);
                            $fechaProceso = date('Y-m-d',strtotime($fProceso)) . ' ' .$hProceso;
                            
                            $queryInsert = $this->Intervencion->save($this->data['Intervencion']['producto'],1,$this->data['Intervencion']['cuentaDebitar'],$this->data['Intervencion']['cuentaDestino'],formatea_monto_toWs($this->data['Intervencion']['total']),formatea_monto_toWs($this->data['Intervencion']['monto_divisa']),formatea_monto_toWs($this->data['Intervencion']['tasa']),formatea_monto_toWs($this->data['Intervencion']['comision']),1,'LQD',formatea_monto_toWs($this->data['Intervencion']['monto_divisa']),formatea_monto_toWs($this->data['Intervencion']['total']),$origen[1],$motivo[1],$referencia,substr($this->data['Intervencion']['cuentaDebitar'] ,10),$this->data['Intervencion']['transactionTargetCod'],$fechaValor,$fechaProceso,str_replace("_", "-",$this->data['Intervencion']['exchangeCod']),$usuario['id'],$this->data['Intervencion']['exchangeMarketId']);
                            
                        } 
                    
                    }else{
                        
                        $error = intval($resBloqueoVal['NIBVINT01Return']['error']['ERRNUM']);                        
                        $descripcion = $resBloqueoVal['NIBVINT01Return']['error']['ERDS01'];

                        $this->log('Intervencion Compra - '. $error . '-' . $descripcion, LOG_ERR);				
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            101088,
                            $cuentaDebitarA,
                            $cuentaCreditarA,
                            $this->data['Intervencion']['monto'],
                            NULL,
                            $descripcion,		
                            1
                        );
                         
                        $queryInsert = $this->Intervencion->save($this->data['Intervencion']['producto'],1,$this->data['Intervencion']['cuentaDebitar'],$this->data['Intervencion']['cuentaDestino'],formatea_monto_toWs($this->data['Intervencion']['total']),formatea_monto_toWs($this->data['Intervencion']['monto_divisa']),formatea_monto_toWs($this->data['Intervencion']['tasa']),formatea_monto_toWs($this->data['Intervencion']['comision']),1,'RCZ',formatea_monto_toWs($this->data['Intervencion']['monto_divisa']),formatea_monto_toWs($this->data['Intervencion']['total']),$origen[1],$motivo[1],'0',substr($this->data['Intervencion']['cuentaDebitar'] ,10),$this->data['Intervencion']['transactionTargetCod'],$date,$date,str_replace("_", "-",$this->data['Intervencion']['exchangeCod']),$usuario['id'],$this->data['Intervencion']['exchangeMarketId']);
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
                            'tipo'=>'fxc',
                            'estatus'=>'pa',
                            'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                            'monto'=>formatea_monto_toWs($this->data['Intervencion']['monto']),
                            'aprobaciones_requeridas'=>1,
                            'tipo_rif'=>$usuario['tipo_rif'],
                            'rif'=>$usuario['rif'],
                            'cuenta_origen'=>encriptar($this->data['Intervencion']['cuentaDebitar']),
                            'cuenta_destino'=>encriptar($this->data['Intervencion']['cuentaDestino']),
                            'descripcion'=> encriptar('Intervencion Compra'),
                            'nombre_beneficiario'=>$usuario['nombre'],
                            'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                            'id_beneficiario'=>$usuario['rif'],
                            'email_beneficiario'=>$this->data['Intervencion']['comision'].'-'.$this->data['Intervencion']['monto_divisa'].'-'.$this->data['Intervencion']['tasa'].'-'.$this->data['Intervencion']['producto'].'-'.$this->data['Intervencion']['total'].'-'.$origen[1].'-'.$motivo[1].'-'.$this->data['Intervencion']['exchangeCod'].'-'.$this->data['Intervencion']['transactionTargetCod'].'-'.$this->data['Intervencion']['transactionSourceCod'].'-'.str_replace("-", "_",$this->data['Intervencion']['exchangeStart']).'-'.str_replace("-", "_",$this->data['Intervencion']['exchangeEnd']).'-'.$tasaCuenta.'-'.$this->data['Intervencion']['exchangeMarketId'].'-'.$this->data['Intervencion']['tasaCompra'],
                            'moneda'=> $moneda
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
                    
                if($referencia==""){
                    $error = -1;    
                }
                $this->set(compact('error', 'referencia', 'descripcion', 'pendiente'));
                $this->Session->delete('intervencion.compra');
            } else {
		$this->redirect('compra');
            }
	}		
	
        /**
	* Acción del Listado de operaciones de intervencion
	*/
	function listado(){
            
            $usuario= $this->Session->read('usuarios.datos');

            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101089)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */            

            // registra los datos del usuario en bitacora
            $this->Bitacora->registraBitacora($usuario['id'], 101089);
                    
            $this->layout = 'interna';
            $this->set('seccion_actual','consulta.operaciones.int');                      
            
            if(empty($usuario['tipo_rif']) || $usuario['rol_juridico_id']==2){
                $total_registros = $this->TransaccionIntervencion->find('count', array(                    								
                    'conditions'=>array(
                        'TransaccionIntervencion.usuario_id' => $usuario['id'],
                        'TransaccionIntervencion.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                    ),
                    'order'=>'TransaccionIntervencion.fecha_hora_registro desc',                    
                    'limit'=>Configure::read('Tamano.paginacion')
                    )
                );
            }else{
                $total_registros = $this->TransaccionIntervencion->find('count', array(                    								
                    'conditions'=>array(
                        'TransaccionIntervencion.tipo_rif' => $usuario['tipo_rif'],
                        'TransaccionIntervencion.rif' => $usuario['rif'],
                        'TransaccionIntervencion.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                    ),
                    'order'=>'TransaccionIntervencion.fecha_hora_registro desc',                    
                    'limit'=>Configure::read('Tamano.paginacion')
                    )
                );
            }                                           

            $offset = 0;

            if(
                isset($this->data['Intervencion']['offset']) &&
                preg_match('/^\d+$/',$this->data['Intervencion']['offset'])
            ){
                $offset = intval($this->data['Intervencion']['offset']);			
            }

            $listado = array();

            if($total_registros){
                
                if(empty($usuario['tipo_rif']) || $usuario['rol_juridico_id']==2){
                    $listado = $this->TransaccionIntervencion->find(
			'all',
			array(
                            'conditions'=>array(
                                'TransaccionIntervencion.usuario_id' => $usuario['id'],
                                'TransaccionIntervencion.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                            ),
                            'order'=>'TransaccionIntervencion.fecha_hora_registro desc',
                            'offset'=>$offset,
                            'limit'=>Configure::read('Tamano.paginacion')
			)
                    );
                }else{
                    $listado = $this->TransaccionIntervencion->find(
			'all',
			array(								
                            'conditions'=>array(
                                'TransaccionIntervencion.tipo_rif' => $usuario['tipo_rif'],
                                'TransaccionIntervencion.rif' => $usuario['rif'],
                                'TransaccionIntervencion.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                            ),
                            'order'=>'TransaccionIntervencion.fecha_hora_registro desc',
                            'offset'=>$offset,
                            'limit'=>Configure::read('Tamano.paginacion')
			)
                    );
                }                
            }          

            $this->set(compact('listado', 'total_registros'));
	}
        
        /**
         * Accion que genera el contrato de Condiciones Intervencion Compra Natural
         *
         */
        function condiciones_intervencion_compra()
        {            
            
            $usuario = $this->Session->read('usuarios.datos');
            $this->data = $this->Session->check('intervencion.compra');

            if (!isset($this->data)) {
                $this->redirect('compra_confirmacion');
            }   
            
            $contrato_data['nombre'] = desencriptar($usuario['nombre']);
            
            $this->set('contrato_data', $contrato_data);
               
        }
        
        
        /**
         * Accion que genera el contrato de Condiciones Intervencion Compra Juridico
         *
         */
        function condiciones_intervencion_compra_j()
        {                        
            $usuario = $this->Session->read('usuarios.datos');
            $this->data = $this->Session->check('intervencion.compra');

            if (!isset($this->data)) {
                $this->redirect('compra_confirmacion');
            }                                                   
               
        }
               
}

?>