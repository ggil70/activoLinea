<?php
/**
 * Controlador para el Pago de  Servicios
 */
 
class PagosServiciosController extends AppController {

    /**
    *
    * @var array Modelos que usa el controlador 
    */
	var $uses = array(
		'PagoServicios',
		'Usuario',
		'Bitacora',
		'Afiliacion',
		'AcumuladoTransaccion',
		'TransaccionPendiente',
		'Parametro',
                'TransaccionJuridicoDetalle',                       	
                'PrefijosTelefonicos'                       
	);

    /**
    *
    * @var array Componentes que utiliza este controlador
    */
	var $components = array('Activo', 'Session', 'Email', 'Sms');
    
    /**
    *
    * @var array Ayudantes utilizados en las vistas de este controlador
    */
	
        var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');

	/*** Inicio Pago Servicios Postpago ***/
	
         /**
         *Acci&oacute;n inicial para el pago de servicos
         * @param String $volver Variable que indica que viene de la confirmaci&oacute;n
         */
	function pago_servicios($volver=null){
            
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(100014)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */

            $usuario = $this->Session->read('usuarios.datos');								

            $this->layout = 'interna';

            $this->set('seccion_actual','pagos.servicios');

            $cuentas = $this->Activo->buscarCuentasSaldos();				

            if(!empty($this->data['PagoServicios'])){

                    //
                    // Para usuario jurídicos operadores, obtener su configuraci&oacute;n para este tipo de transacci&oacute;n
                    if(
                            $usuario['rol_juridico_id'] == 3 ||
                            $usuario['rol_juridico_id'] == 5
                    ){
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                    $usuario['id'],
                                    100014,
                                    $this->data['PagoServicios']['cuentaDebitar']
                            );

                            if(!$transaccion_detalle) {
                                    $this->PagoServicios->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                            }
                    }

                    if(empty($this->PagoServicios->validationErrors)){				

                            if(isset($transaccion_detalle)){
                                    $this->data['PagoServicios']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                                    if(is_numeric($transaccion_detalle['limite'])){
                                            $this->data['PagoServicios']['monto_limite_usuario']=formatea_monto_fromWs($transaccion_detalle['limite']);
                                    } else {
                                            $this->data['PagoServicios']['monto_limite_usuario']='';
                                    }
                            }

                            $this->PagoServicios->set($this->data);													

                            if($this->PagoServicios->validates( array( 'fieldList'=>array( 'cuentaDebitar', 'tipoDestinatario') ) )){														

                                $this->Session->write('pago_servicios', $this->data);
                                  //Comentado por solicitud en el proyecto movistar
//                                    if($this->data['PagoServicios']['tipoDestinatario'] == 'a'){
                                    $this->redirect('afiliados');
//                                    } else {				
//                                        $this->redirect('noafiliados');
//                                    }
                            }	
                    }

            }
            //fin										

            $this->set(compact('cuentas','error'));
		
	}
	
	 /**
         * Acci&oacute;n para realizar Pago Servicios Postpago con Clientes Afiliados
         */
	function afiliados(){
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(100014)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */		
		$usuario = $this->Session->read('usuarios.datos');
        $recarga = $this->Session->read('pago_servicios');                
        $montoMinimo=0;                
        $this->layout = 'interna';
        $tipo_afiliacion = 's';                                                
		$this->set('seccion_actual','pagos.servicios');
		
		// Obtener el límite de parámetros
		if(empty($usuario['rif'])){
            $limite_config = _p('limite_internas_terceros');
            $monto_limite_usuario = "";
		} else {
            $limite_config = _p('limite_internas_terceros_j');
            //Si es master unico no valida limite por usuario solo el general
            if($usuario['rol_juridico_id']!=2){
                $monto_limite_usuario = $recarga['PagoServicios']['monto_limite_usuario'];
            }else{
                $monto_limite_usuario = "";
            }
		}
		
		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/
		$monto_limite = '';
		$acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id']);
		$limite_total = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : $limite_config;
		if($limite_total){
                    $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_internas_terceros']);
		}
		$limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);
		
		if(isset($usuario['rif']) && $usuario['rol_juridico_id']!=2){
                    $monto_limite=$limite_total='';
		}
		
		$this->set(compact('monto_limite','monto_limite_usuario'));
		/** PARA EL MONTO LIMITE EN LA TRANSACCION **/	               
                
        if(_p('sms.otp.activo')=='0'){
            $validacion_otp['validado'] = true;
        }else{
            $validacion_otp = $this->Session->read('seguridad.validar_otp');
        }  
        if((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado']))
        {                                                
            if(_p('sms.otp.activo')=='0'){
                $this->data['pasos'] = array(
                    'Solicitud de Informaci&oacute;n',
                    'Confirmaci&oacute;n',                                            
                    'Resultado de Operaci&oacute;n'
                );
                $this->data['paso_final'] = 2;
            }else{
                $this->data['pasos'] = array(
                    'Solicitud de Informaci&oacute;n',
                    'Confirmaci&oacute;n',
                    'C&oacute;digo de Autorizaci&oacute;n',
                    'Resultado de Operaci&oacute;n'
                );
                $this->data['paso_final'] = 3;
            }       
        }
        else{
                $this->data['pasos'] = array(
                        'Solicitud de Informaci&oacute;n',
                        'Confirmaci&oacute;n',
                        'Resultado de Operaci&oacute;n'
                );
                $this->data['paso_final'] = 2;
        }
        
        if(empty($this->data['PagoServicios'])){			
            $this->data = $recarga;
        }elseif(!empty($this->data['PagoServicios']) && !isset($this->data['PagoServicios']['paginando'] )){
            $this->data['PagoServicios']['tipoDestinatario'] = 'a';
    
            /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
            $this->data['PagoServicios']['monto_limite'] = $monto_limite;
            $this->data['PagoServicios']['limite_total'] = $limite_total;
            $this->data['PagoServicios']['cuentaDebitar'] = $recarga['PagoServicios']['cuentaDebitar'];
                
            if(isset($recarga['PagoServicios']['monto_limite_usuario'])){
                            $this->data['PagoServicios']['monto_limite_usuario'] = $recarga['PagoServicios']['monto_limite_usuario'];
            }			
            /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
                                                                                
            // Para usuario jurídicos operadores, obtener su configuraci&oacute;n para este tipo de transacci&oacute;n
            if( $usuario['rol_juridico_id'] == 3 || $usuario['rol_juridico_id'] == 5){
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                100014,
                                $this->data['PagoServicios']['cuentaDebitar']
                            );

                            if(!$transaccion_detalle) {
                                $this->PagoServicios->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                            }
            }			                        
                        
            if(empty($this->PagoServicios->validationErrors)){
                if(isset($transaccion_detalle)){
                    $this->data['PagoServicios']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if(is_numeric($transaccion_detalle['limite'])){
                        $this->data['PagoServicios']['monto_limite_usuario']=formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['PagoServicios']['monto_limite_usuario']='';
                    }
                }
                                
                $this->PagoServicios->set($this->data);
                                    
                //Para recargas Movistar    
                if(trim($this->data['PagoServicios']['tipoOperacion'])=='st'){
                                                                
                    if($this->PagoServicios->validates( array( 'fieldList'=>array('numservicio','tipo_pago','monto','otro_monto')))){
                            
                            $modalidad = 0;
                            $error = 0;
                            $tipo_servicio = $this->data['PagoServicios']['tipoServicio'];
                            if($this->data['PagoServicios']['tipo_pago']=='o'){
                                $this->data['PagoServicios']['monto']=$this->data['PagoServicios']['otro_monto'];
                            }                                                    
                            
                            $datos_afiliacion = $this->Afiliacion->find( 'first', array('fields' => '*','conditions'=>(array('id'=>$this->data['PagoServicios']['id'],'tipo_afiliacion'=>$tipo_afiliacion))));                                                    
                            $this->data['PagoServicios']['nombreBeneficiario'] = desencriptar($datos_afiliacion['Afiliacion']['nombre_beneficiario']);
                            $this->data['PagoServicios']['numeroContrato'] = $datos_afiliacion['Afiliacion']['codigo_telefono'].desencriptar($datos_afiliacion['Afiliacion']['numero_cuenta']);
                            $this->data['PagoServicios']['afiliar'] = 'n';
                            $this->Session->write('pago_servicios', $this->data);                                                    
                            $this->Session->delete('transaccion.advertencia');

                            // En caso de ser un usuario natural o master unico validamos si ya realizo una transaccion igual durante el dia
                            if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2){
                                    if( $this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['PagoServicios'], 100015) ){

                                        $this->data['titulo']= 'Pago Servicios';
                                        $this->data['mensaje_alerta'] = 'pagotdc.alerta';
                                        $this->data['url_volver'] = '/pagos_servicios/afiliados';
                                        $this->data['url_continuar'] = '/pagos_servicios/pago_servicios_confirmacion';
                                        $this->data['seccion_actual'] = 'pagos.servicios';

                                        $this->Session->write('transaccion.advertencia', $this->data);
                                        $this->redirect('transaccion_advertencia');
                                    }
                            }
                            $this->redirect('pago_servicios_confirmacion');
                    }
                }elseif (trim($this->data['PagoServicios']['tipoOperacion'])=='sd') {
                    $modalidad = 0;
                    $error = 0;
                    $tipo_servicio = $this->data['PagoServicios']['tipoServicio'];

                    //Esta opcion se desactiva cuando la operacion es
                    //Digitel postpago
                    //if($this->data['PagoServicios']['tipo_pago']=='o'){
                    //    $this->data['PagoServicios']['monto']=$this->data['PagoServicios']['otro_monto'];
                    //} 

                    switch($this->data['PagoServicios']['tipo_pago']){
                    case 'v':
                        $this->data['PagoServicios']['monto']=$this->data['PagoServicios']['tipo_pago_v'];
                        break;

                    case 'a':
                        $this->data['PagoServicios']['monto']=$this->data['PagoServicios']['tipo_pago_a'];
                        break;

                    case 't':
                        $this->data['PagoServicios']['monto']=$this->data['PagoServicios']['tipo_pago_t'];
                        break;


                    }                                                    
                    
                    
                    $datos_afiliacion = $this->Afiliacion->find( 'first', array('fields' => '*','conditions'=>(array('id'=>$this->data['PagoServicios']['id'],'tipo_afiliacion'=>$tipo_afiliacion))));                                                    
                    $this->data['PagoServicios']['nombreBeneficiario'] = desencriptar($datos_afiliacion['Afiliacion']['nombre_beneficiario']);
                    $this->data['PagoServicios']['numeroContrato'] = $datos_afiliacion['Afiliacion']['codigo_telefono'].desencriptar($datos_afiliacion['Afiliacion']['numero_cuenta']);
                    $this->data['PagoServicios']['afiliar'] = 'n';
                    $this->Session->write('pago_servicios', $this->data);                                                    
                    $this->Session->delete('transaccion.advertencia');

                    // En caso de ser un usuario natural o master unico validamos si ya realizo una transaccion igual durante el dia
                    if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2){
                            if( $this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['PagoServicios'], 100015) ){

                                $this->data['titulo']= 'Pago Servicios';
                                $this->data['mensaje_alerta'] = 'pagotdc.alerta';
                                $this->data['url_volver'] = '/pagos_servicios/afiliados';
                                $this->data['url_continuar'] = '/pagos_servicios/pago_servicios_confirmacion';
                                $this->data['seccion_actual'] = 'pagos.servicios';

                                $this->Session->write('transaccion.advertencia', $this->data);
                                $this->redirect('transaccion_advertencia');
                            }
                    }
                    $this->redirect('pago_servicios_confirmacion');
                }elseif (trim($this->data['PagoServicios']['tipoOperacion'])=='si') {
                    $modalidad = 0;
                    $error = 0;
                    $tipo_servicio = $this->data['PagoServicios']['tipoServicio'];
                    if($this->data['PagoServicios']['tipo_pago']=='o'){
                        $this->data['PagoServicios']['monto']=$this->data['PagoServicios']['otro_monto'];
                    }                                                    
                    
                    $datos_afiliacion = $this->Afiliacion->find( 'first', array('fields' => '*','conditions'=>(array('id'=>$this->data['PagoServicios']['id'],'tipo_afiliacion'=>$tipo_afiliacion))));                                                    
                    $this->data['PagoServicios']['nombreBeneficiario'] = desencriptar($datos_afiliacion['Afiliacion']['nombre_beneficiario']);
                    $this->data['PagoServicios']['numeroContrato'] = $datos_afiliacion['Afiliacion']['codigo_telefono'].desencriptar($datos_afiliacion['Afiliacion']['numero_cuenta']);
                    $this->data['PagoServicios']['afiliar'] = 'n';
                    $this->Session->write('pago_servicios', $this->data);                                                    
                    $this->Session->delete('transaccion.advertencia');

                    // En caso de ser un usuario natural o master unico validamos si ya realizo una transaccion igual durante el dia
                    if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2){
                            if( $this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['PagoServicios'], 100015) ){

                                $this->data['titulo']= 'Pago Servicios';
                                $this->data['mensaje_alerta'] = 'pagotdc.alerta';
                                $this->data['url_volver'] = '/pagos_servicios/afiliados';
                                $this->data['url_continuar'] = '/pagos_servicios/pago_servicios_confirmacion';
                                $this->data['seccion_actual'] = 'pagos.servicios';

                                $this->Session->write('transaccion.advertencia', $this->data);
                                $this->redirect('transaccion_advertencia');
                            }
                    }
                    $this->redirect('pago_servicios_confirmacion');
                }elseif (trim($this->data['PagoServicios']['tipoOperacion'])=='sm'){
                    $modalidad = 0;
                    $error = 0;

                    $tipo_operacion = isset($this->data['PagoServicios']['tipoOperacion']) ? $this->data['PagoServicios']['tipoOperacion'] : '';
                    $tipo_servicio  = isset($this->data['PagoServicios']['tipoServicio']) ? $this->data['PagoServicios']['tipoServicio'] : '';
                    $tipo_pago      = isset($this->data['PagoServicios']['tipo_pago']) ? $this->data['PagoServicios']['tipo_pago'] : '';

                    if ($tipo_operacion === 'sm' && $tipo_servicio === 'stm') {

                        switch ($tipo_pago) {
                            case 'o':
                                $this->data['PagoServicios']['monto'] = isset($this->data['PagoServicios']['otro_monto'])
                                    ? $this->data['PagoServicios']['otro_monto']
                                    : '';
                                break;

                            case 'v':
                                $this->data['PagoServicios']['monto'] = isset($this->data['PagoServicios']['tipo_pago_v'])
                                    ? $this->data['PagoServicios']['tipo_pago_v']
                                    : '';
                                break;

                            case 'a':
                                $this->data['PagoServicios']['monto'] = isset($this->data['PagoServicios']['tipo_pago_a'])
                                    ? $this->data['PagoServicios']['tipo_pago_a']
                                    : '';
                                break;

                            case 't':
                                $this->data['PagoServicios']['monto'] = isset($this->data['PagoServicios']['tipo_pago_t'])
                                    ? $this->data['PagoServicios']['tipo_pago_t']
                                    : '';
                                break;

                            default:
                                $this->data['PagoServicios']['monto'] = '';
                                break;
                        }
                    }

                    $this->data['PagoServicios']['numeroContrato'] = isset($this->data['PagoServicios']['numservicio'])
                        ? $this->data['PagoServicios']['numservicio']
                        : '';

                    $this->data['PagoServicios']['factura'] = isset($this->data['PagoServicios']['factura'])
                        ? $this->data['PagoServicios']['factura']
                        : '';
                    
                    $this->PagoServicios->set($this->data);
                    if ($this->PagoServicios->validates(array('fieldList' => array('monto', 'descripcion')))) {
                        $this->Session->write('pago_servicios', $this->data);
                        if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2) {
                            if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['PagoServicios'], 100010)) {
                                $this->data['titulo'] = 'Pago Servicios';
                                $this->data['mensaje_alerta'] = 'pagotdc.alerta';
                                $this->data['url_volver'] = '/pagos_servicios/afiliados';
                                $this->data['url_continuar'] = '/pagos_servicios/pago_servicios_confirmacion';
                                $this->data['seccion_actual'] = 'pagos.servicios';
                                $this->Session->write('transaccion.advertencia', $this->data);
                                $this->redirect('transaccion_advertencia');
                            }
                        }
                        $this->redirect('pago_servicios_confirmacion');
                    }
            }
    
        }
        //fin
        }
                
        $servicios = $this->Afiliacion->obtenerListaAfiliados($usuario['id'],$tipo_afiliacion,array('sd','st','si','sm'));		
        
        if($servicios){
            if(count($servicios) >0 ){
                //Validar que no se incluya el servicio de 
                //  REcarga /  movilstar TV
                /////////       

            $auxServicio = $servicios;
            $servicios = array();
            $i = 0;

            foreach ($auxServicio as $a){                     
                //Validaci&oacute;n para excluir pago de movistart tv en recarga.

                if((trim($a['Afiliacion']['tipo_operacion'])=='st') && (trim($a['Afiliacion']['tipo_servicio'])=='stv')) {
                        $swTv = 1;
                }else{
                    $arreglo = array(
                    'Afiliacion'=>array(
                        'id'=> $a['Afiliacion']['id'],
                        'usuario_id'=> $a['Afiliacion']['usuario_id'], 
                        'tipo_afiliacion'=> $a['Afiliacion']['tipo_afiliacion'],
                        'tipo_operacion'=> $a['Afiliacion']['tipo_operacion'],
                        'alias'=> $a['Afiliacion']['alias'], 
                        'codigo_telefono'=> $a['Afiliacion']['codigo_telefono'],
                        'banco'=> $a['Afiliacion']['banco'], 
                        'numero_cuenta'=> $a['Afiliacion']['numero_cuenta'], 
                        'nombre_beneficiario'=> $a['Afiliacion']['nombre_beneficiario'], 
                        'tipo_cedula'=> $a['Afiliacion']['tipo_cedula'], 
                        'cedula'=> $a['Afiliacion']['cedula'], 
                        'email'=> $a['Afiliacion']['email'],                       
                        'monto_maximo'=> $a['Afiliacion']['monto_maximo'], 
                        'tipo_rif'=> $a['Afiliacion']['tipo_rif'],
                        'rif'=> $a['Afiliacion']['rif'],
                        'numero_contrato'=> $a['Afiliacion']['numero_contrato'],
                        'fecha_contrato'=> $a['Afiliacion']['fecha_contrato'],
                        'tipo_servicio'=> $a['Afiliacion']['tipo_servicio'],
                        'telefono'=> $a['Afiliacion']['telefono'],
                        'numero_cuenta_receptora'=> $a['Afiliacion']['numero_cuenta_receptora'],
                        'numero_cuenta_ahorro'=> $a['Afiliacion']['numero_cuenta_ahorro'],                      
                    )
                    );
                    array_push($servicios, $arreglo);
                    $i=$i+1;
                }    
            }   
        }    
    }



        if($servicios){
			// Empieza c&oacute;digo para paginar
			$total_registros = count($servicios);
			$offset = 0;

			if(
				isset($this->data['PagoServicios']['offset']) &&
				preg_match('/^\d+$/',$this->data['PagoServicios']['offset'])
			){
				$offset = intval($this->data['PagoServicios']['offset']);			
			}

			$resultados = array_slice(
				$servicios,
				$offset,
				5,
				true
			);
			// Fin c&oacute;digo para paginar
		}
		$this->set(compact('cuentas','tipo_operacion','servicios','error','total_registros','resultados','recarga'));	
		
	}
        
        /**
         * Acci&oacute;n para obtener informaci&oacute;n de los servicios afiliados por el cliente
             * 
         * @param string $servicio 
         * @param string $operacion
         * @param string $tipo_servicio 
         */
	function obtenerInfo($servicio, $operacion, $tipo_servicio){

        $this->layout = 'ajax';
        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $datos_basicos = $this->Session->read('usuarios.nibpf3010');
        $clienteIBS = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
        $identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];
                
		if(!empty($servicio) && $this->Session->check('usuarios.datos')){
			$usuario = $this->Session->read('usuarios.datos');
			$response = array();
	
			/** Para identificar si es un usuario juridico **/
			if($usuario['tipo_rif']){
				$documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
			}
			else{
				$documento_identificacion = desencriptar($usuario['cedula']);
			}
			/** Para identificar si es un usuario juridico **/						
			
			$servicios = $this->Afiliacion->find(
				'first',
				array(
					'fields'=>array('numero_cuenta','monto_maximo','codigo_telefono'),
					'conditions'=>array('Afiliacion.id'=>$servicio
					)
				),false
			);
			
			$res = '';
            $error = -1;
			$descripcion = "Error inesperado, transacci&oacute;n no disponible.";
                        
			if($operacion == 'scn'){//Movilnet
				
                    $res = $this->Activo->invoca(
					'IN098401',
					array(
						'req'=>array(
							'IN984USR1'=>$documento_identificacion,
							'IN984BVDD'=>date('d'),
							'IN984BVDM'=>date('m'),
							'IN984BVDY'=>date('y'),
							'IN984SER'=>$modalidad,
							'IN984CON'=>desencriptar($servicios['Afiliacion']['numero_cuenta']),
							'IN984TYPE'=>10											
						)
					)
				);
                                
                                $referencia = $res['IN098401Return']['IN098401']['IN984DS2'];
			
                                if(isset($res['IN098401Return']['error']['ERRNUM'])){
                                        $error = intval($res['IN098401Return']['error']['ERRNUM']);
                                        $error = ($error == 0) ? $error : intval($res['IN098401Return']['error']['ERNU01']);
                                        $descripcion = $res['IN098401Return']['error']['ERNU01'] . ' - ' . $res['IN098401Return']['error']['ERDS01'];

                                        $this->log('Obtener Montos de Servicios - '.$descripcion, LOG_ERR);
                                        $response = array('error'=>true);
                                }
                                else{

                                        $deuda_vencida = $res['IN098401Return']['IN098401']['IN984EXP'];

                                        $deuda_actual = $res['IN098401Return']['IN098401']['IN984AVL'] >0 ? formatea_monto_fromWs($res['IN098401Return']['IN098401']['IN984AVL']) : 'NM';

                                        $deuda_total= $res['IN098401Return']['IN098401']['IN984AMT'];

                                        $response = array(
                                                'tipo_pago_v' => formatea_monto_fromWs($deuda_vencida),
                                                'tipo_pago_a' => $deuda_actual,
                                                'tipo_pago_t' => $deuda_total,
                                                'monto_maximo' => empty($servicios['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($servicios['Afiliacion']['monto_maximo'])
                                        );

                                        $this->data['PagoServicios']['deuda_vencida']=formatea_monto_fromWs($res['IN098401Return']['IN098401']['IN984EXP']);
                                        $this->data['PagoServicios']['deuda_actual']=formatea_monto_fromWs($res['IN098401Return']['IN098401']['IN984AVL']);
                                        $this->data['PagoServicios']['deuda_total']=formatea_monto_fromWs($res['IN098401Return']['IN098401']['IN984AMT']);
                                }
                                
			}elseif($operacion == 'sm'){//Movilnet
                $dataRest = array(
                    "operadora"         => "movilnet_pospago",
                    "producto"          => 63,
                    "numero_contrato"   => desencriptar($servicios['Afiliacion']['numero_cuenta'])
                );
                $res = $this->Activo->invocaGatewayNexus(
                    'payall/movilnet/consultaSaldo',
                    json_encode($dataRest),
                    'POST'
                );

                $response = array();

                if (is_object($res)) {
                    $payload = isset($res->return) ? $res->return : $res;
                } elseif (is_array($res)) {
                    $payload = isset($res['return']) ? $res['return'] : $res;
                } else {
                    $payload = json_decode($res);
                    if (isset($payload->return)) {
                        $payload = $payload->return;
                    }
                }

                if ($payload) {
                    $codigoRespuesta = isset($payload->codigo_respuesta) ? $payload->codigo_respuesta : null;
                    $success = isset($payload->success) ? filter_var($payload->success, FILTER_VALIDATE_BOOLEAN) : false;

                    if ($success && $codigoRespuesta == '00') {
                        $response = array(
                            'deuda_vencida' => formatea_monto_fromWs($payload->deuda_vencida),
                            'deuda_vigente' => formatea_monto_fromWs($payload->deuda_vigente),
                            'deuda_total'   => formatea_monto_fromWs($payload->deuda_total),
                            'factura'       => formatea_monto_fromWs($payload->factura),
                            'monto_maximo'  => formatea_monto_fromWs(_p('recarga_maxima_movilnet_postpago')),
                            'monto_minimo'  => formatea_monto_fromWs(_p('recarga_minima_movilnet_postpago'))
                        );

                        $this->data['PagoServicios']['deuda_vencida'] = formatea_monto_fromWs($payload->deuda_vencida);
                        $this->data['PagoServicios']['deuda_actual']  = formatea_monto_fromWs($payload->deuda_vigente);
                        $this->data['PagoServicios']['deuda_total']   = formatea_monto_fromWs($payload->deuda_total);
                    } else {
                        $response = array(
                            'error' => true,
                            'descripcion' => isset($payload->mensaje_respuesta)
                                ? $payload->codigo_respuesta . '-' . $payload->mensaje_respuesta
                                : 'Respuesta invlida del servicio.'
                        );
                    }
                } else {
                    $response = array(
                        'error' => true,
                        'descripcion' => 'No se pudo interpretar la respuesta del servicio.'
                    );
                }
           
			}elseif($operacion == 'st'){//Movistar
                                
                                $modalidad = 0;                                
                                                    
                                if($tipo_servicio == 'stm' || $tipo_servicio == 'sim'){
                                        $modalidad = 70;                                                                                                    
                                }elseif($tipo_servicio == 'stf'){
                                        $modalidad = 71;                                        
                                }elseif($tipo_servicio == 'stv'){
                                        $modalidad = 72;                                        
                                }                                                                                                      
                                
                                $valorPostpago = array('IN988NCLT'=>$clienteIBS,'IN988IDUS'=>$identificacion,'IN988TYPE'=>10,'IN988TSER'=>2, 'IN988CSER'=>$modalidad,'IN988CHAN'=>'4','IN988DS2'=>str_pad(desencriptar($servicios['Afiliacion']['numero_cuenta']),40,'0',STR_PAD_LEFT),'IN988IPCLT'=>$ip);
                                
				$res = $this->Activo->invoca(
					'IN098801',
					array(
                                            'req'=>$valorPostpago
                                        )                                    
				);                                                                
			
                                if(isset($res['IN098801Return']['error']['ERRNUM'])){
                                        $error = intval($res['IN098801Return']['error']['ERRNUM']);
                                        $error = ($error == 0) ? $error : intval($res['IN098801Return']['error']['ERNU01']);
                                        $descripcion = $res['IN098801Return']['error']['ERNU01'] . ' - ' . $res['IN098801Return']['error']['ERDS01'];

                                        $this->log('Obtener Montos de Servicios - '.$descripcion, LOG_ERR);
                                        $response = array('error'=>true);
                                }
                                else{
                                        
                                        $referencia = $res['IN098801Return']['IN098801']['IN988DS2'];
                                        
                                        $deuda_vencida = $res['IN098801Return']['IN098801']['IN988MDEU'];

                                        $deuda_actual = $res['IN098801Return']['IN098801']['IN988MFAC'] >0 ? $res['IN098801Return']['IN098801']['IN988MFAC'] : 'NM';

                                        $deuda_total= $res['IN098801Return']['IN098801']['IN988MDEU'] + $res['IN098801Return']['IN098801']['IN988MFAC'];

                                        $response = array(
                                                'tipo_pago_v' => formatea_monto_fromWs($deuda_vencida),
                                                'tipo_pago_a' => formatea_monto_fromWs($deuda_actual),
                                                'tipo_pago_t' => formatea_monto_fromWs($deuda_total),
                                                'monto_maximo' => empty($servicios['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($servicios['Afiliacion']['monto_maximo'])
                                        );

                                        $this->data['PagoServicios']['deuda_vencida']=$deuda_vencida;
                                        $this->data['PagoServicios']['deuda_actual']=$deuda_actual;
                                        $this->data['PagoServicios']['deuda_total']=$deuda_total;
                                }                                                                                              
			} elseif($operacion == 'sd'){ //Digitel PostPago



                ///$valorPostpago = array('compania' => 1, 'ope' => 1, 'telefono' => '58' . substr($servicios['Afiliacion']['codigo_telefono'], 1) . desencriptar($servicios['Afiliacion']['numero_cuenta']),'canal'=>'04');

                $valorPostpago = array('compania' => 1, 'ope' => 1, 'telefono' => '58' . substr($servicios['Afiliacion']['codigo_telefono'], 1) . desencriptar($servicios['Afiliacion']['numero_cuenta']),'canal'=>'pos');



                $res = $this->Activo->invoca(
                    'Celupagos',
                    array(
                        'req' => $valorPostpago
                    )

                );

                if ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'BCRM-03013-OM0005') {



                    $response = array('noP' => true);

                    $this->log($res, LOG_ERR);

                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['total'] == 0) {

                    $response = array('noD' => true);

                    $this->log($res, LOG_ERR);
                } else {

                    $deuda_vencida = $res['CelupagosReturn']['celupagos']['celupagos']['expired'];

                    $deuda_actual = $res['CelupagosReturn']['celupagos']['celupagos']['current'];

                    $deuda_total = $res['CelupagosReturn']['celupagos']['celupagos']['total'];
                    $referenciab = $res['CelupagosReturn']['celupagos']['celupagos']['reference'];

                    //echo  $referenciab;

                    $response = array(
                        'tipo_pago_v' => formatea_monto_fromWs($deuda_vencida),
                        'tipo_pago_a' => formatea_monto_fromWs($deuda_actual),
                        'tipo_pago_t' => formatea_monto_fromWs($deuda_total),
                        'monto_maximo' => empty($servicios['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($servicios['Afiliacion']['monto_maximo']),
                        'referenciab' => $referenciab
                    );

                    $this->data['PagoServicios']['deuda_vencida'] = $deuda_vencida;
                    $this->data['PagoServicios']['deuda_actual'] = $deuda_actual;
                    $this->data['PagoServicios']['deuda_total'] = $deuda_total;
                    $this->data['PagoServicios']['referenciab'] = $referenciab;
                }
            } elseif ($operacion == 'si') { //Inter PostPago

                //$valorPostpago = array('compania' => 2, 'ope' => 1, 'nrocontrato' => desencriptar($servicios['Afiliacion']['numero_cuenta']),'canal'=>'04');

                $valorPostpago = array('compania' => 2, 'ope' => 1, 'nrocontrato' => desencriptar($servicios['Afiliacion']['numero_cuenta']),'canal'=>'pos');


                $res = $this->Activo->invoca(
                    'Celupagos',
                    array(
                        'req' => $valorPostpago
                    )
                );

                $this->log($res, LOG_ERR);
                $this->log($valorPostpago, LOG_ERR);

                if ($res['CelupagosReturn']['celupagos']['celupagos']['total'] == 0) {

                    $response = array('noD' => true);

                    $this->log($res, LOG_ERR);
                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == -5) {

                    $response = array('noSA' => true);

                    $this->log($res, LOG_ERR);
                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 1) {

                    $deuda_vencida = $res['CelupagosReturn']['celupagos']['celupagos']['expired'];

                    $deuda_actual = $res['CelupagosReturn']['celupagos']['celupagos']['current'];

                    $deuda_total = $res['CelupagosReturn']['celupagos']['celupagos']['total'];
                    $referenciab = $res['CelupagosReturn']['celupagos']['celupagos']['reference'];

                    //echo  $referenciab;

                    $response = array(
                        'tipo_pago_v' => formatea_monto_fromWs($deuda_vencida),
                        'tipo_pago_a' => formatea_monto_fromWs($deuda_actual),
                        'tipo_pago_t' => formatea_monto_fromWs($deuda_total),
                        'monto_maximo' => empty($servicios['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($servicios['Afiliacion']['monto_maximo']),
                        'referenciab' => $referenciab
                    );

                    $this->data['PagoServicios']['deuda_vencida'] = $deuda_vencida;
                    $this->data['PagoServicios']['deuda_actual'] = $deuda_actual;
                    $this->data['PagoServicios']['deuda_total'] = $deuda_total;
                    $this->data['PagoServicios']['referenciab'] = $referenciab;
                } else {

                    $response = array('error' => true);
                }
            }
            $this->set('response', $response);
        }
        $this->set('response', $response);		
  }
  
	
	/**
         *Acci&oacute;n para la confirmaci&oacute;n del pago de servicios
         */
	function pago_servicios_confirmacion(){
            
                /* Para validar el acceso al modulo */
		if(!validarTransaccion(100014)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
                
		$this->layout = 'interna';
		$this->set('seccion_actual','pagos.servicios');														
                $this->set('servicios', $this->Afiliacion->buscarTiposServicios());
                
		$usuario = $this->Session->read('usuarios.datos');               
		$this->data = $this->Session->read('pagos_servicios');		
                
		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
                    $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
                    $documento_identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
		
		if($this->Session->check('pago_servicios')){
			$this->data = $this->Session->read('pago_servicios');       
			if(trim($this->data['PagoServicios']['tipoOperacion']) == 'sm'){
                $this->data['PagoServicios']['operacion'] = 'MOVILNET';                            
			}elseif(trim($this->data['PagoServicios']['tipoOperacion']) == 'sd'){
                $this->data['PagoServicios']['operacion'] = 'Pago Digitel';					
			}elseif(trim($this->data['PagoServicios']['tipoOperacion'] == 'scn')){
                $this->data['PagoServicios']['operacion'] = 'CANTV.NET';                            
			}elseif(trim($this->data['PagoServicios']['tipoOperacion']) == 'st'){
                $this->data['PagoServicios']['operacion'] = 'Movistar';
            }elseif(trim($this->data['PagoServicios']['tipoOperacion']) == 'si'){
                $this->data['PagoServicios']['operacion'] = 'Recarga Inter';
            }
                        
			$tipo_afiliacion = 's';                        
			$error = 0;			                                                                       			
				
			$this->set(compact('error'));
			
		} else {                        
			$this->redirect('pago_servicios');
		}
	}
	
    
        /**
         * Acci&oacute;n para el resultado del pago de servicios, ejecuta el pago
         */
	function pago_servicios_resultado(){	
		
                /* Para validar el acceso al modulo */
		if(!validarTransaccion(100014)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
                                
                $this->set('seccion_actual','pagos.servicios');
                $this->set('servicios', $this->Afiliacion->buscarTiposServicios());
		$usuario = $this->Session->read('usuarios.datos');
		$estado = $this->Session->read('pago_servicios');
                $confirmacion= "";
                $referencia="";
		$modalidad = 0;
                $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                
		$this->layout = 'interna';				
                
		if($this->Session->check('pago_servicios')){			                    
			
                    $this->data = $this->Session->read('pago_servicios');
                    $tipo_servicio = $this->data['PagoServicios']['tipoServicio'];
                    $tipo_operacion = trim($this->data['PagoServicios']['tipoOperacion']);
                    $this->set('servicios', $this->Afiliacion->buscarTiposServicios());
                    $this->set('tipoOperacion', trim($this->data['PagoServicios']['tipoOperacion']));



                    
                    //Segun el tipo de operador asigno valores a las variables
                    if($tipo_operacion == 'sm'){
                            $this->data['PagoServicios']['operacion'] = 'MOVILNET';
                            $modalidad = 30;
                            $tipo_transaccion_pendiente = 'stm';
                            $ayuda='';
                            $multiplo=_p('multiplo.movilnet');;
                            $recarga_minima=_p('recarga_minima_movilnet_postpago');
                            $recarga_maxima=_p('recarga_maxima_movilnet_postpago');
                    }elseif($tipo_operacion == 'sd'){
                            $this->data['PagoServicios']['operacion'] = 'Pago Digitel';
                            $tipo_transaccion_pendiente = 'scd';
                            $ayuda='121';
                            $multiplo=_p('multiplo.digitel');
                            $recarga_minima=_p('recarga_minima_digitel');
                            $recarga_maxima=_p('recarga_maxima_digitel');
                    }elseif($tipo_operacion == 'st'){
                            $this->data['PagoServicios']['operacion'] = 'Movistar';
                            $tipo_transaccion_pendiente = 'scm';
                            $ayuda='811';
                            $multiplo=_p('multiplo.movistar');
                            $recarga_minima=_p('recarga_minima_movistar');
                            $recarga_maxima=_p('recarga_maxima_movistar');
                    }elseif($tipo_operacion == 'si'){
                            $this->data['PagoServicios']['operacion'] = 'Recarga Inter';
                            $tipo_transaccion_pendiente = 'ssi';
                            $recarga_minima=_p('recarga_minima_inter_post');
                            $recarga_maxima=_p('recarga_maxima_inter_post');
                    }

                    /** Para identificar si es un usuario juridico **/
                    if($usuario['tipo_rif']){
                        $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                    }else{
                        $documento_identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                    }
                    /** Para identificar si es un usuario juridico **/

                    $res = '';
                    
                    // Identificar tipo de usuario (natural o jurídico) para saber si proceso o no la transaccion
                    // Master único		
                    if(empty($usuario['tipo_rif']) ||$usuario['rol_juridico_id'] == 2 ){			                                  
                        
                        if(_p('sms.otp.activo')=='0' && $this->data['PagoServicios']['tipoDestinatario']=='a'){
                            $validacion_otp['validado'] = true;
                        }else{
                            $validacion_otp = $this->Session->read('seguridad.validar_otp');
                        }  

                        if(($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])){
                                if($this->data['PagoServicios']['tipoDestinatario'] == 'a'){
                                    $url_volver = '/pagos_servicios/afiliados';
                                }else{
                                    $url_volver = '/pagos_servicios/noafiliados';
                                }

                                $this->Session->write(
                                        'seguridad.validar_otp',
                                        array(
                                            'url_aceptar'=>'/pagos_servicios/pago_servicios_resultado',
                                            'url_volver' => $url_volver,
                                            'layout'=>'interna',
                                            'titulo'=>'Pago Servicios Postpago',
                                            'pasos'=> $estado['pasos'],
                                            'paso_actual'=>2,
                                            'seccion_actual'=>'pagos.servicios'
                                        )
                                );

                                $this->redirect('/seguridad/genera_otp');
                        }       
                        
                        $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                        $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
                            
                        if(trim($this->data['PagoServicios']['tipoOperacion']) == 'sm'){
                            $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                            $datos = $this->data['PagoServicios'];
                            $producto = '63';
                            $ttr = '02';

                            $parametro = array(
                                'IN8013ACC' => substr($datos['cuentaDebitar'], 10),
                                'IN8013CAN' => '04',
                                'IN8013CCY' => 'VEB',
                                'IN8013CEN' => $usuario['tipo_cedula'] . str_pad(desencriptar($usuario['cedula']), 9, '0', STR_PAD_LEFT),
                                'IN8013CKN' => 0,
                                'IN8013CUN' => $usuario['nrocliente'],
                                'IN8013DSC' => '',
                                'IN8013ERR' => 0,
                                'IN8013FEC' => date('Ymd'),
                                'IN8013HOR' => date('his'),
                                'IN8013IRV' => 'N',
                                'IN8013MTO' => formatea_monto_toWs($this->data['PagoServicios']['monto']),
                                'IN8013NAU' => '',
                                'IN8013NCT' => $datos['numeroContrato'],
                                'IN8013NIP' => $ip,
                                'IN8013UUI' => '',
                                'IN8013PRO' => $producto,
                                'IN8013TTR' => $ttr,
                                'IN8013NTR' => '',
                                'IN8013USR' => desencriptar($usuario['login'])
                            );

                            $operation = null;
                            $res = $this->Activo->invocaMovilnet(array('req' => $parametro));
                            
                            $dtaTransaccion = array();

                            $factura = $datos['factura'];

                            $factura = str_replace('.', '', $factura);
                            $factura = str_replace(',', '.', $factura);

                            $datos['factura'] = (string) intval($factura);
                            $dtaTransaccionOrig = $res['IN801301Return']['IN801301'];

                            if (isset($res['IN801301Return']['IN801301']['IN8013ERR']) && $res['IN801301Return']['IN801301']['IN8013ERR'] == 0) {
                                if (isset($res['IN801301Return']['IN801301']['IN8013CKN'])) {
                                    $referencia = $res['IN801301Return']['IN801301']['IN8013CKN'];
                                }

                                $dataRest = array(
                                    'operadora' => 'movilnet_pospago',
                                    'producto' => $producto,
                                    'numero' => trim($datos['numeroContrato']) ."/". trim($datos['factura']),
                                    'monto' => formatea_monto_toWs($this->data['PagoServicios']['monto'])
                                );
                                
                                $operation = $this->Activo->invocaGatewayNexus(
                                    'payall/movilnet/recargar',
                                    json_encode($dataRest),
                                    'POST'
                                );
                                
                                $payload = $operation;
                                if (is_object($operation)) {
                                    if (isset($operation->raw) && is_object($operation->raw) && isset($operation->raw->return)) {
                                        $payload = $operation->raw->return;
                                    } elseif (isset($operation->return)) {
                                        $payload = $operation->return;
                                    }
                                } elseif (is_array($operation)) {
                                    if (isset($operation['raw']) && is_array($operation['raw']) && isset($operation['raw']['return'])) {
                                        $payload = $operation['raw']['return'];
                                    } elseif (isset($operation['return'])) {
                                        $payload = $operation['return'];
                                    }
                                }

                                if (is_array($payload)) {
                                    $payload = (object) $payload;
                                }

                                $is_success = is_object($payload) 
                                    && isset($payload->success) 
                                    && ($payload->success === true || $payload->success === 'true') 
                                    && isset($payload->codigo_aprobacion) 
                                    && $payload->codigo_aprobacion !== '000000' 
                                    && $payload->codigo_aprobacion !== ''
                                    && isset($payload->codigo_respuesta) 
                                    && $payload->codigo_respuesta === '00';

                                if ($is_success) {
                                    $opData = (array) $payload;

                                    $codigo_aprobacion = isset($opData['codigo_aprobacion']) ? trim(str_replace(array("\r", "\n", "\t"), '', (string)$opData['codigo_aprobacion'])) : '';
                                    $secuencia         = isset($opData['secuencia'])         ? trim(str_replace(array("\r", "\n", "\t"), '', (string)$opData['secuencia']))         : '';

                                    $uuid = '';
                                    if (isset($opData['uuid']) && $opData['uuid'] !== '') {
                                        $uuid = trim(str_replace(array("\r", "\n", "\t"), '', (string)$opData['uuid']));
                                    } elseif (isset($opData['data']['uuid']) && $opData['data']['uuid'] !== '') {
                                        $uuid = trim(str_replace(array("\r", "\n", "\t"), '', (string)$opData['data']['uuid']));
                                    } elseif (isset($payload->uuid) && $payload->uuid !== '') {
                                        $uuid = trim(str_replace(array("\r", "\n", "\t"), '', (string)$payload->uuid));
                                    } elseif (isset($payload->data->uuid) && $payload->data->uuid !== '') {
                                        $uuid = trim(str_replace(array("\r", "\n", "\t"), '', (string)$payload->data->uuid));
                                    }

                                    $dtaTransaccion = array_merge($parametro, (array)$dtaTransaccionOrig);

                                    $dtaTransaccion['IN8013NAU'] = $codigo_aprobacion; 
                                    $dtaTransaccion['IN8013NTR'] = $secuencia;
                                    $dtaTransaccion['IN8013UUI'] = $uuid;
                                    $dtaTransaccion['IN8013TTR'] = '03';
                                    $dtaTransaccion['IN8013IRV'] = 'N';
                                    $confirmacion = $codigo_aprobacion;

                                    $res = $this->Activo->invocaMovilnet(array('req' => $dtaTransaccion));
                                } else {
                                    $uuid = '';
                                    if (is_object($payload) && isset($payload->uuid) && $payload->uuid !== '') {
                                        $uuid = trim(str_replace(array("\r", "\n", "\t"), '', (string)$payload->uuid));
                                    } elseif (is_object($operation) && isset($operation->uuid) && $operation->uuid !== '') {
                                        $uuid = trim(str_replace(array("\r", "\n", "\t"), '', (string)$operation->uuid));
                                    } elseif (is_array($payload) && isset($payload['uuid']) && $payload['uuid'] !== '') {
                                        $uuid = trim(str_replace(array("\r", "\n", "\t"), '', (string)$payload['uuid']));
                                    } elseif (is_array($operation) && isset($operation['uuid']) && $operation['uuid'] !== '') {
                                        $uuid = trim(str_replace(array("\r", "\n", "\t"), '', (string)$operation['uuid']));
                                    }

                                    $dtaTransaccion = array_merge($parametro, (array)$dtaTransaccionOrig);
                                    $dtaTransaccion['IN8013IRV'] = 'Y';
                                    $dtaTransaccion['IN8013TTR'] = '03';
                                    $dtaTransaccion['IN8013UUI'] = $uuid;

                                    $res = $this->Activo->invocaMovilnet(array('req' => $dtaTransaccion));
                                }
                            }
                        }elseif(trim($this->data['PagoServicios']['tipoOperacion']) == 'st'){
                                                        
                            
                            if($tipo_servicio == 'stm' || $tipo_servicio == 'sim'){
                                    $modalidad = 70;                                    
                            }elseif($tipo_servicio == 'stf'){
                                    $modalidad = 71;                                    
                            }elseif($tipo_servicio == 'stv'){
                                    $modalidad = 72;                                    
                            }                        
                                    
                            $res = $this->Activo->invoca(
                                    'IN098801',
                                        array(
                                            'req'=>array(
                                                'IN988IDUS'=>$documento_identificacion,
                                                'IN988NCLT'=>$identificacion,
                                                'IN988DS2'=>str_pad($this->data['PagoServicios']['numeroContrato'],40,'0',STR_PAD_LEFT),
                                                'IN988TYPE'=>11,
                                                'IN988CHAN'=>4,                                                      
                                                'IN988CSER'=>$modalidad,
                                                'IN988TSER'=>2, 
                                                'IN988FACC'=>str_pad(substr($this->data['PagoServicios']['cuentaDebitar'],10,20),12,'0',STR_PAD_LEFT),
                                                'IN988AMNT'=>formatea_monto_toWs($this->data['PagoServicios']['monto']),
                                                'IN988IPCLT'=>$ip
                                            )
                                        )
                            );
                                    
                            if(isset($res['IN098801Return']['IN098801'])){
                                $referencia = str_pad($res['IN098801Return']['IN098801']['IN988CTRA'],9,'0',STR_PAD_LEFT);
                                $confirmacion = str_pad($res['IN098801Return']['IN098801']['IN988CTRA'],9,'0',STR_PAD_LEFT);
                            }
                            //Recarga Digitel Nuevo esquema celupagos
                        } elseif (trim($this->data['PagoServicios']['tipoOperacion']) == 'sd') {

                            $this->data['PagoServicios']['ref'] = $this->data['PagoServicios']['referenciab'];

                            if ($tipo_servicio == 'stm') {

                                $modalidad = 40;
                                $ran = rand(6, 8);
                                $data = array(
                                    'req' => array(
                                        'compania' => 1,
                                        //'IN988IDUS'=>$documento_identificacion,
                                        'nrodocumento' => $identificacion,
                                        'telefono' => '58' . substr($this->data['PagoServicios']['numeroContrato'], 1, 3) . substr($this->data['PagoServicios']['numeroContrato'], 4),
                                        'servicio' => 2,
                                        'canal' => '04',
                                        'ope' => 2,
                                        'cuentadebito' => str_pad(substr($this->data['PagoServicios']['cuentaDebitar'], 10, 20), 12, '0', STR_PAD_LEFT),
                                        'monto' => formatea_monto_toWs($this->data['PagoServicios']['monto']),
                                        'ip' => $ip,
                                        'referenciaD' => $this->data['PagoServicios']['referenciab'],
                                        'reverso' => 'N',
                                        'refreverso' => '000000000',

                                    )
                                );





                            } elseif ($tipo_servicio == 'stf') {
                                $modalidad = 41;
                                $data = array(
                                    'req' => array(
                                        'compania' => 1,
                                        //'IN988IDUS'=>$documento_identificacion,
                                        'nrodocumento' => $identificacion,
                                        'telefono' => '58' . substr($this->data['PagoServicios']['numeroContrato'], 1, 3) . substr($this->data['PagoServicios']['numeroContrato'], 4),
                                        'servicio' => 2,
                                        'canal' => '04',
                                        'ope' => 2,
                                        'cuentadebito' => str_pad(substr($this->data['PagoServicios']['cuentaDebitar'], 10, 20), 12, '0', STR_PAD_LEFT),
                                        'monto' => formatea_monto_toWs($this->data['PagoServicios']['monto']),
                                        'ip' => $ip,

                                        'referenciaD' => $this->data['PagoServicios']['referenciab'],
                                        'reverso' => 'N',
                                        'refreverso' => '000000000',

                                    )
                                );
                            }

                            $res = $this->Activo->invoca(
                                'Celupagos',
                                $data
                            );


                            if (isset($res['CelupagosReturn']['celupagos'])) {
                                $referencia = str_pad($res['CelupagosReturn']['celupagos']['celupagos']['referenciab'], 9, '0', STR_PAD_LEFT);
                                $confirmacion = str_pad($res['CelupagosReturn']['celupagos']['celupagos']['reference'], 9, '0', STR_PAD_LEFT);
                            }
                        } elseif (trim($this->data['PagoServicios']['tipoOperacion']) == 'si') {

                            $data = array(
                                'req' => array(
                                    'compania' => 2,
                                    //'IN988IDUS'=>$documento_identificacion,
                                    'nrodocumento' => $identificacion,
                                    'nrocontrato' => $this->data['PagoServicios']['numeroContrato'],
                                    'servicio' => 2,
                                    'canal' => '04',
                                    'ope' => 2,
                                    'cuentadebito' => str_pad(substr($this->data['PagoServicios']['cuentaDebitar'], 10, 20), 12, '0', STR_PAD_LEFT),
                                    'monto' => formatea_monto_toWs($this->data['PagoServicios']['monto']),
                                    'ip' => $ip,
                                    'reverso' => 'N',
                                    'refreverso' => '000000000',

                                )
                            );

                            $res = $this->Activo->invoca(
                                'Celupagos',
                                $data
                            );

                            if (isset($res['CelupagosReturn']['celupagos'])) {
                                $referencia = str_pad($res['CelupagosReturn']['celupagos']['celupagos']['referenciab'], 9, '0', STR_PAD_LEFT);
                                $confirmacion = str_pad($res['CelupagosReturn']['celupagos']['celupagos']['reference'], 9, '0', STR_PAD_LEFT);
                            }
                        }
                        

                        if(trim($this->data['PagoServicios']['tipoOperacion']) == 'sm'){
                            //$cuenta_servicio = _p('cuenta_cantv');
                            if(intval($res['IN801301Return']['IN801301']['IN8013ERR']) != 0){
                                $error = intval($res['IN801301Return']['IN801301']['IN8013ERR']);
                                $descripcion = $res['IN801301Return']['IN801301']['IN8013ERR'] . ' - ' . $res['IN801301Return']['IN801301']['IN8013DSC'];
                            }                        
                        }elseif(trim($this->data['PagoServicios']['tipoOperacion']) == 'st'){
                            //$cuenta_servicio = _p('cuenta_movistar');
                            $operador = 'Movistar';
                            if(isset($res['IN098801Return'])){
                                $error = 0;
                                if(isset($res['IN098801Return']['error'])){
                                    if(!empty($res['IN098801Return']['error']['ERDR01'])){
                                        $error = intval($res['IN098801Return']['error']['ERDR01']);
                                    }else{
                                        $error = intval($res['IN098801Return']['error']['ERNU01']);
                                    }
                                    $descripcion = 'Movistar ' .$res['IN098801Return']['error']['ERDR01'] . '-' . 'MYPS ' . $res['IN098801Return']['error']['ERNU01'] . ' - ' . $res['IN098801Return']['error']['ERDS01'];
                                }
                            }                                                                             
                        } elseif(trim($this->data['PagoServicios']['tipoOperacion']) == 'sd'){
                            //$cuenta_servicio = _p('cuenta_digitel');
                            $operador = 'Digitel';
                            if (isset($res['CelupagosReturn'])) {
                                $error = 0;
                                if ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0000') {
                                    $error = 00;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0001') {
                                    $error = 208;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0005') {
                                    $error = 8;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0002') {
                                    $error = 208;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0003') {
                                    $error = 208;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-EM0001') {
                                    $error = 208;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == '89') {
                                    $error = 208;
                                } 
                                elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == '204') {
                                    $error = 208;
                                } else {
                                    $error = 208;
                                }
                            }
                        }elseif (trim($this->data['PagoServicios']['tipoOperacion']) == 'si') { //Inter
                            //$cuenta_servicio = _p('cuenta_digitel');
                            $operador = 'Inter';
                            if (isset($res['CelupagosReturn'])) {
                                $error = 0;

                                if ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == '2') {

                                    $error = 0;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 1) {

                                    $error = 0;
                                }
                                elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3) {

                                    $error = 0;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 204) {
                                    $error = 8;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3001) {
                                    $error = 208;
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3003) {
                                    $error = 208;
                                    
                                } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 89) {
                                    $error = 208;
                                    
                                } else {

                                    $error = 208;
                                }
                            }
                        }
                          
                           
                        if($error == 0){
                            
                            /** ACTUALIZA EL ACUMULADO DEL DIA **/
                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', $this->data['PagoServicios']['monto']);
			                /** ACTUALIZA EL ACUMULADO DEL DIA **/
                                        
                            $descripcion='Pago Servicios '.$this->data['PagoServicios']['operacion'];
                            $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            100014, 
                            $this->data['PagoServicios']['cuentaDebitar'],
                            $this->data['PagoServicios']['numeroContrato'],
                            $this->data['PagoServicios']['monto'],
                            $referencia,
                            $descripcion,
                            0
                            );                                      
                                                        
                            $this->data['PagoServicios']['REFERENCIA'] = $referencia;
                            $this->data['PagoServicios']['REFERENCIA_OPERADOR'] = $confirmacion;
                            $this->data['PagoServicios']['CONCEPTO'] = 'PAGO SERVICIO';
                            $this->data['PagoServicios']['TIPO'] = 'Servicio' .$this->data['PagoServicios']['operacion'];
                            $this->data['PagoServicios']['ESTATUS'] = 'PROCESADO';
                            $this->data['PagoServicios']['NRO_BENEFICIARIO'] = $this->data['PagoServicios']['numeroContrato'];                        

                            // Genera la data para la descarda de pdf
                            $this->Session->write('pago.servicio.descarga.pdf', $this->data['PagoServicios']);
                                                                
                            // Email Orden
                            // Email Opciones
                            $this->Email->smtpOptions = Configure::read('email.smtp');
                            $this->Email->delivery = 'smtp';
                            $this->Email->from = Configure::read('email.from');
                            $this->Email->to = desencriptar($usuario['email_principal']);
                            $this->Email->subject = 'Notificacion Pago de Servicios';
                            $this->Email->sendAs = 'html';

                            // Informacion del correo
                            $this->Email->template = 'pagoservicio_ordenante';
                            $tipo_accion = 'Pago Servicio';
                            $tipo_afiliacion = $this->data['PagoServicios']['operacion'];
                            $nombreBeneficiario= $this->data['PagoServicios']['nombreBeneficiario'];
                            
                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion','nombreBeneficiario'));
                            
                            // Informacion del SMS
                            
                            $sms = array(
                                        'numcel'=>desencriptar($usuario['celular']),
                                        'canal1'=>'59',
                                        'fectim'=>$operador,
                                        'cheque'=>substr($this->data['PagoServicios']['numeroContrato'],1),
                                        'cuenta'=>substr($this->data['PagoServicios']['cuentaDebitar'],16,4),
                                        'monto'=>$this->data['PagoServicios']['monto'],
                                        'fecha'=>date('Ymd'),
                                        'hora'=>date('His'),
                                        'refnum'=>substr($referencia,5,4),
                                        'xrnde38'=>'IB'
                                    );                                                        
                            
                            //Movistar
                            if(trim($this->data['PagoServicios']['tipoOperacion']) == 'st'){
                                
                                if(_p('notificacion.movistar')=='1' || _p('notificacion.movistar')=='3'){                                
                                    // Envio del correo
                                    $this->Email->send();
                                }

                                if(_p('notificacion.movistar')=='2' || _p('notificacion.movistar')=='3'){
                                    // Enviar por sms la notificaci&oacute;n de que realiz&oacute; la recarga al ordenante		
                                    $this->Sms->enviar($sms);
                                }
                            } elseif(trim($this->data['PagoServicios']['tipoOperacion']) == 'si'){
                                
                                if(_p('notificacion.movistar')=='1' || _p('notificacion.movistar')=='3'){                                
                                    // Envio del correo
                                    $this->Email->send();
                                }
                                                            

                               /* if(_p('notificacion.movistar')=='2' || _p('notificacion.movistar')=='3'){
                                    // Enviar por sms la notificaci&oacute;n de que realiz&oacute; la recarga al ordenante        
                                    $this->Sms->enviar($sms);
                                }*/

                            } elseif(trim($this->data['PagoServicios']['tipoOperacion']) == 'sd'){
                                
                                if(_p('notificacion.digitel')=='1' || _p('notificacion.digitel')=='3'){                                
                                    // Envio del correo
                                    $this->Email->send();
                                }
                            }                                                       

                        }else{

                           
                                switch($error){
                                    case 203:
                                    case 209:
                                    case 8267:
                                    case 8275:
                                    case 8293:    
                                            $descripcion = _m('servicio.recarga.invalido',array($ayuda,$operador,'203'));
                                            break;
                                    case 204:
                                    case 8273:    
                                            $descripcion = _m('monto.recarga.invalido',array($multiplo,formatea_monto_fromWs($recarga_minima),formatea_monto_fromWs($recarga_maxima)),'204');
                                            break;
                                    case 205:
                                    case 8271:    
                                            $descripcion = _m('gsm.invalido',array('GSM',$ayuda,$operador,'205'));
                                            break;
                                    case 207:
                                    case 8268:
                                    case 8269:    
                                            $descripcion = _m('servicio.recarga.invalido',array($ayuda,$operador,'207'));
                                            break;
                                    case 201:
                                    case 8279:
                                    case 202:
                                    case 206:
                                    case 8208:    
                                    case 208:
                                    case 8277:    
                                    case 9996:
                                    case 9920:
                                    case 1393:    
                                            $descripcion = _m('procesar.solicitud');
                                            break;
                                    case 1616:
                                            $descripcion = _m('cuenta.sobregirada');
                                            break;
                                    case 862:
                                            $descripcion = _m('cuenta.sobregirada');                    
                                            break;
                                    case 14: $descripcion=_m('digitel.noPermitido');
                                    break;
                                    case 5:
                                            $descripcion=_m('digitel.bloqueo');
                                            break; 
                                    case 7: 
                                             $descripcion=_m('digitel.noPermitido');
                                    break;  
                                    case 13:
                                    case 8: 
                                        $descripcion=_m('digitel.montoNovalido');

                                         break;
                                    case 9:
                                         $descripcion=_m('digitel.lineanoPagable');
                                    break;
                                    
                                    default:

                                            $descripcion = _m('procesar.solicitud');
                                                                                        
                                }
                                $this->set(compact('descripcion'));
                                $this->log('Pago de Servicios '.$this->data['PagoServicios']['operacion'].' - '. $descripcion . ' - ' . $res, LOG_ERR);
                                $this->Bitacora->registraBitacora(
                                    $usuario['id'],
                                    100014,
                                    $this->data['PagoServicios']['cuentaDebitar'],
                                    $this->data['PagoServicios']['numeroContrato'],
                                    $this->data['PagoServicios']['monto'],
                                    NULL,
                                    $descripcion.'-'.$error,
                                    1
                                );
                                                                
                        }
                        
                        $this->set('pendiente',false);
                        //Fin usuario natural o master unico

                    }else if(
                        $usuario['rol_juridico_id'] == 3  || // Operador
                        $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
                    ){
                        // En caso de jurídico, s&oacute;lo los operadores y operadores/aprobadores pueden
                        // generar un pago. En cuyo caso, se deja pendiente por aprobar

                        $error = 0;
                        
                        if(trim($this->data['PagoServicios']['tipoServicio'])=='stm' || trim($this->data['PagoServicios']['tipoServicio'])=='stf'){
                            $numeroContrato = substr($this->data['PagoServicios']['numeroContrato'],4);
                            $this->data['PagoServicios']['monto_maximo'] = NULL;
                        }else{
                            $numeroContrato = $this->data['PagoServicios']['numeroContrato'];
                        }
                        if(trim($this->data['PagoServicios']['tipoOperacion'])=='sd' ){


                             $res = $this->TransaccionPendiente->save(
                                array(
                                        'TransaccionPendiente'=>array(
                                                'usuario_id'=>$usuario['id'],
                                                'tipo'=>$tipo_transaccion_pendiente,
                                                'tipo_servicio'=>trim($this->data['PagoServicios']['tipoServicio']),
                                                'estatus'=>'pa',
                                                'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                                'monto'=>formatea_monto_toWs($this->data['PagoServicios']['monto']),
                                                'aprobaciones_requeridas'=>$this->data['PagoServicios']['aprobaciones_requeridas'],
                                                'tipo_rif'=>$usuario['tipo_rif'],
                                                'rif'=>$usuario['rif'],
                                                'cuenta_origen'=>encriptar($this->data['PagoServicios']['cuentaDebitar']),
                                                'cuenta_destino'=>encriptar($this->data['PagoServicios']['numeroContrato']),
                                                'descripcion'=>encriptar($this->data['PagoServicios']['operacion']),
                                                'nombre_beneficiario'=>encriptar($this->data['PagoServicios']['nombreBeneficiario']),
                                                'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                                                'id_beneficiario'=>$usuario['rif'],
                                                'email_beneficiario'=>null,
                                                'alias'=>null,
                                                'lote'=>$this->data['PagoServicios']['referenciab'],
                                        )
                                ),
                                false
                        );


                        }else{
                        
                        $res = $this->TransaccionPendiente->save(
                                array(
                                        'TransaccionPendiente'=>array(
                                                'usuario_id'=>$usuario['id'],
                                                'tipo'=>$tipo_transaccion_pendiente,
                                                'tipo_servicio'=>trim($this->data['PagoServicios']['tipoServicio']),
                                                'estatus'=>'pa',
                                                'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                                'monto'=>formatea_monto_toWs($this->data['PagoServicios']['monto']),
                                                'aprobaciones_requeridas'=>$this->data['PagoServicios']['aprobaciones_requeridas'],
                                                'tipo_rif'=>$usuario['tipo_rif'],
                                                'rif'=>$usuario['rif'],
                                                'cuenta_origen'=>encriptar($this->data['PagoServicios']['cuentaDebitar']),
                                                'cuenta_destino'=>encriptar($this->data['PagoServicios']['numeroContrato']),
                                                'descripcion'=>encriptar($this->data['PagoServicios']['operacion']),
                                                'nombre_beneficiario'=>encriptar($this->data['PagoServicios']['nombreBeneficiario']),
                                                'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                                                'id_beneficiario'=>$usuario['rif'],
                                                'email_beneficiario'=>null,
                                                'alias'=>null
                                        )
                                ),
                                false
                        );
                    }

                        if($res){
                                $error = 0;
                                $referencia = $this->TransaccionPendiente->id;
                        } else {
                                $error = -1;
                                $referencia = '';
                        }
                        
                        $error_afiliacion = false;			
                        
                        $this->set('pendiente',true);	
                    }//Fin Transacciones Pendientes



                    $this->set(compact('error','referencia','confirmacion'));
                    $this->Session->delete('pago_servicios');
		} else {
                    $this->redirect('pago_servicios');
		}
	}
	
	
        /**
         *Acci&oacute;n para la descarga del archivo pdf 
         */
	function descargar_pdf_sevicios(){
		if(!$this->Session->check('pago.servicio.descarga.pdf') && $this->Session->check('usuarios.datos')){
			$this->redirect('/consultas/integral');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$data = $this->Session->read('pago.servicio.descarga.pdf');

		$this->set(compact('data','usuario'));
		$this->render('/elements/descargar_pdf_servicios');
	}
		
	/*** Fin Pago Servicios ***/

  public function obtenerContratos($cedula, $tipo){
         $this->layout = 'ajax';
       // $valorPostpago = array('compania'=>2,'ope'=>1,'nrodocumento'=>$cedula, 'servicio'=>'2','canal'=>'04');

        $valorPostpago = array('compania'=>2,'ope'=>1,'nrodocumento'=>$cedula, 'servicio'=>'2','canal'=>'pos');


            $res = $this->Activo->invoca(
                     'Celupagos',
                     array(
                    'req'=>$valorPostpago
                    )                                    
                    ); 

          
          
                if($res==false || $res['CelupagosReturn']['celupagos']['celupagos']['errorCode']==89){

                    $response = array('error' => '999');
                }else{
                if(isset($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'])){
                
               
                if($tipo=='POSTPAGO'){
                if($res['CelupagosReturn']['celupagos']['celupagos']['errorCode']==-1){

            
                    $response[]= $res['CelupagosReturn']['celupagos']['celupagos'] ; 
                    

                }elseif ($res['CelupagosReturn']['celupagos']['celupagos']['contractType']=='POSTPAGO' &&  $res['CelupagosReturn']['celupagos']['celupagos']['errorCode']==1) {
                        $response[]=$res['CelupagosReturn']['celupagos']['celupagos'];

                            

                       } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['contractType']=='POSTPAGO' &&  $res['CelupagosReturn']['celupagos']['celupagos']['errorCode']==-2){
                        
                        $response[]=$res['CelupagosReturn']['celupagos']['celupagos'];
                        }else{
                         $response = array('error' => '991' );

                     }

                    }elseif($tipo=='PREPAGO'){



                      if($res['CelupagosReturn']['celupagos']['celupagos']['errorCode']==-1){

            
                             $response[]=$res['CelupagosReturn']['celupagos']['celupagos'];


                }elseif($res['CelupagosReturn']['celupagos']['celupagos']['contractType']=='PREPAGO' &&  $res['CelupagosReturn']['celupagos']['celupagos']['errorCode']==1){
                             $response[]=$res['CelupagosReturn']['celupagos']['celupagos'];

                     } elseif($res['CelupagosReturn']['celupagos']['celupagos']['contractType']=='PREPAGO' &&  $res['CelupagosReturn']['celupagos']['celupagos']['errorCode']==-2){
                        
                        $response[]=$res['CelupagosReturn']['celupagos']['celupagos'];

                        }else{

                              $response = array('error' => '991' );

                     }
                    }
                      
                    
                }else{
                   foreach ($res['CelupagosReturn']['celupagos']['celupagos'] as $key ) {
                    if($tipo=='POSTPAGO'){
                        if($key['contractType']=='POSTPAGO' && $key['errorCode']==1){
                           $response[]=$key; 
                        }

                }elseif($tipo=='PREPAGO'){

                    if($key['contractType']=='PREPAGO' && $key['errorCode']==1){
                  

                       $response[]=$key;   
                    }

                
                }          

                 }
                    if(count($response)==0){

                        $response = array('error' => '991' );

                    }
                

               }
           }
                 

                                    
                     
                    
            
                $this->set('response',   $response);
        }
          
      
                 
        

    
}