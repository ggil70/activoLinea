<?php
/**
 * Controlador para la Recarga de Servicios Prepago
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Operaciones de recarga de servicios prepago.
 * 
 */
class RecargaSimpletvController extends AppController {

        /**
        *
        * @var array Modelos que usa el controlador 
        */
	var $uses = array(
		'RecargaServicios',
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
	var $components = array('Activo', 'Session', 'Email','Sms','Simpletv');
    
        /**
        *
        * @var array Ayudantes utilizados en las vistas de este controlador
        */
	var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');

	/*** Inicio Recarga Prepago ***/
	
        /**
        *Acción inicial para el pago de servicos
        */
	function recarga_simpletv(){


				
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(102100)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */

		$usuario = $this->Session->read('usuarios.datos');									

        $this->Session->delete('recarga_simpletv');


		$this->layout = 'interna';
		$this->set('seccion_actual','recarga.simpletv');	
		$cuentas = $this->Activo->buscarCuentasSaldos();	




		if(!empty($this->data['RecargaSimpletv'])){

  		    //
			// Para usuario jurídicos operadores, obtener su configuración para este tipo de transacción

            if(!empty($usuario['rol_juridico_id'])){
			    if($usuario['rol_juridico_id'] == 3 || 
                $usuario['rol_juridico_id'] == 5
			    ){

                    $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                    $usuario['id'],
                    102100,
                    $this->data['RecargaSimpletv']['cuentaDebitar']
                    );


                    if(!$transaccion_detalle) {
                        $this->RecargaSimpletv->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                    }


			    }
            }    



            if(empty($this->RecargaSimpletv->validationErrors)){               

                                
                if(isset($transaccion_detalle)){
                    $this->data['RecargaSimpletv']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if(is_numeric($transaccion_detalle['limite'])){
                        $this->data['RecargaSimpletv']['monto_limite_usuario']=formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['RecargaSimpletv']['monto_limite_usuario']='';
                    }
                }



                $this->RecargaServicios->set($this->data);                                                  
                    
                if($this->RecargaServicios->validates( array( 'fieldList'=>array( 'cuentaDebitar', 'tipoDestinatario') ) )){                                                        

                    $this->Session->write('recarga_simpletv', $this->data);

                    $this->redirect('afiliados');
                }
	    	
		}
               //fin	
        }

      									
		$this->set(compact('cuentas','error'));	

}
        
         /**
         * Acción para realizar Recarga Prepago con Clientes Afiliados
         */

	function afiliados(){
        /* Para validar el acceso al modulo */
		if(!validarTransaccion(102100)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */								

		$usuario = $this->Session->read('usuarios.datos');

        $recarga = $this->Session->read('recarga_simpletv');


        $montoMinimo=0;
        $tipo_afiliacion = 'r';                                                
        $this->layout = 'interna';

		$this->set('seccion_actual','recarga.simpletv');
		
        //validar otp            
        if(_p('sms.otp.activo')=='0'){
            $validacion_otp['validado'] = true;
        }else{
            $validacion_otp = $this->Session->read('seguridad.validar_otp');
        }  

 
                
        if((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado']))
        {        


            if(_p('sms.otp.activo')=='0'){
                $this->data['pasos'] = array(
                    'Solicitud de Información',
                    'Confirmación',                                            
                    'Resultado de Operación'
                );
                $this->data['paso_final'] = 2;
            }else{
                $this->data['pasos'] = array(
                    'Solicitud de Información',
                    'Confirmación',
                    'Código de Autorización',
                    'Resultado de Operación'
                );
                $this->data['paso_final'] = 3;
            }       
        }
        else{
            $this->data['pasos'] = array(
                'Solicitud de Información',
                'Confirmación',
                'Resultado de Operación'
            );
            $this->data['paso_final'] = 2;
        }

        $montoAfiliadoVta = '';
        $tipoPago = false;


        if(empty($this->data['RecargaSimpletv'])){          
            $this->data = $recarga;
        }elseif(!empty($this->data['RecargaSimpletv']) && !isset($this->data['RecargaSimpletv']['paginando'] )){


            //vALIDAR MONTO MAXIMO DE LA AFILIACION CON RESPECTO A MONTO DE LA RECARGA  
            if($this->data['RecargaSimpletv']['tipo_pago']=='t'){
                 
                $mtoAux = str_replace('.',',',$this->data['RecargaSimpletv']['renta']);
                $this->data['RecargaSimpletv']['montoFinal'] = $mtoAux;
            }else{
                $this->data['RecargaSimpletv']['montoFinal'] = $this->data['RecargaSimpletv']['monto']; 
                $tipoPago = true;

            }            
           
            $mtoAfiliacion = $this->data['RecargaSimpletv']['montoafiliacion']; 
            $montoAfiliadoVta = $mtoAfiliacion;
            $mtoAfiliacion = str_replace(',','.', $mtoAfiliacion);






            $swValidaMtoAfiliacion = 0;  
            $errorMtoAfiliado = ''; 
            if(!empty($mtoAfiliacion)){
                $mtoRecarga = $this->data['RecargaSimpletv']['montoFinal'];
                $mtoRecarga = str_replace('.','', $mtoRecarga);
                $mtoRecarga = str_replace(',','.', $mtoRecarga);


                if($mtoRecarga > $mtoAfiliacion){
                    $swValidaMtoAfiliacion = 1;        
                    $errorMtoAfiliado = _m('monto.maximo',$mtoRecarga) . ' ( ' . $mtoAfiliacion . ' )';

                }             
            }

            if($swValidaMtoAfiliacion ==0){

                $this->data['RecargaSimpletv']['tipoDestinatario'] = 'a';
                $this->data['RecargaServicios']['cuentaDebitar'] = $recarga['RecargaSimpletv']['cuentaDebitar'];
                $this->data['RecargaServicios']['numeroContrato'] = $recarga['RecargaSimpletv']['numservicio'];
                $this->data['RecargaSimpletv']['operacion'] = 'RECARGA SIMPLETV';

                if($usuario['rol_juridico_id'] == 3 || $usuario['rol_juridico_id'] == 5
                ){

                    $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                      $usuario['id'],102100,$this->data['RecargaSimpletv']['cuentaDebitar']
                    );


                    if(!$transaccion_detalle) {
                        $this->RecargaSimpletv->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                    }
                }  

                if(empty($this->RecargaSimpletv->validationErrors)){

                    if(isset($transaccion_detalle)){
                        $this->data['RecargaSimpletv']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                        if(is_numeric($transaccion_detalle['limite'])){
                            $this->data['RecargaSimpletv']['monto_limite_usuario']=formatea_monto_fromWs($transaccion_detalle['limite']);
                        } else {
                            $this->data['RecargaSimpletv']['monto_limite_usuario']='';
                        }
                    }



                    $montoMinimo=formatea_monto_fromWs(_p('recarga_minima_simple_tv'));
                    $montoMaximo=formatea_monto_fromWs(_p('recarga_maxima_simple_tv'));
                    $modalidad = 0;      


       
                    $datos_afiliacion = $this->Afiliacion->find( 'first', array('fields' => '*','conditions'=>(array('id'=>$this->data['RecargaSimpletv']['id'],'tipo_afiliacion'=>$tipo_afiliacion))));

                    $this->data['RecargaSimpletv']['nombreBeneficiario'] = desencriptar($datos_afiliacion['Afiliacion']['nombre_beneficiario']);

                    $this->data['RecargaSimpletv']['numeroContrato'] = $this->data['RecargaSimpletv']['numservicio'];

                    $this->Session->write('recarga_simpletv',$this->data);
                     
                    // En caso de ser un usuario natural o master unico
                    if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2){
                        if( $this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['RecargaSimpletv'], 102100) ){
                                $this->data['titulo']= 'Recarga Simpletv';
                                $this->data['mensaje_alerta'] = 'pagotdc.alerta';
                                $this->data['url_volver'] = '/recarga_simpletv/afiliados';
                                $this->data['url_continuar'] = '/recarga_simpletv/recarga_simpletv_confirmacion';
                                $this->data['seccion_actual'] = 'recarga.simpletv';
                                $this->Session->write('transaccion.advertencia', $this->data);
                                $this->redirect('transaccion_advertencia');
                        }
                    }
                    $this->redirect('recarga_simpletv_confirmacion');
                }    
            }
        }


                
    $servicios = $this->Afiliacion->obtenerListaAfiliados($usuario['id'],$tipo_afiliacion,array('sv'));

    if($servicios){
        if(count($servicios) >0 ){
            //Validar que no se incluya el servicio de 
            //  REcarga /  movilstar TV
            /////////       

            $auxServicio = $servicios;
            $servicios = array();
            
            $i = 0;

            foreach ($auxServicio as $a){                     
                //Validación para excluir pago de movistart tv en recarga.

                if((trim($a['Afiliacion']['tipo_operacion'])=='st') && (trim($a['Afiliacion']['tipo_servicio'])=='rtv')) {
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



        //Fin Validación para excluir pago de movistart tv en recarga.



        //Generar el listado de beneficiario s Simple TV
        if($servicios){
            $total_registros = count($servicios);

            // Empieza código para paginar
            $offset = 0;

            if(
                            isset($this->data['RecargaSimpletv']['offset']) &&
                            preg_match('/^\d+$/',$this->data['RecargaSimpletv']['offset'])
            ){
                            $offset = intval($this->data['RecargaSimpletv']['offset']);         
            }

            $resultados = array_slice(
                            $servicios,
                            $offset,
                            5,
                            true
            );
            // Fin código para paginar
            
        }


       $cuentas = $recarga['RecargaSimpletv']['cuentaDebitar']; 
       $this->set(compact('cuentas','tipo_operacion','servicios','error','total_registros','resultados','recarga','montoMinimo','montoMaximo','errorMtoAfiliado','tipoPago'));   
        
    }

	
        /**
        * Acción para obtener información de los servicios afiliados por el cliente        
        * @param string $servicio 
        * @param string $operacion 
        */
	function obtenerInfo($contrato){
		$this->layout = 'ajax';

        if(isset($contrato)){
            $nroContrato = str_pad($contrato, 12, '0', STR_PAD_LEFT);

            $userSTV = Configure::read('Simple_TV.user');
            $passwordSTV = Configure::read('Simple_TV.pwd');
            $arrayContrato = array(
                'nroContrato'=>$nroContrato,
                'userSTV'=>$userSTV,
                'passwordSTV'=>$passwordSTV,
                //'urlCustomer'=>$urlCustomer
            );  

                $res = $this->Simpletv->invocaCurlStv('validateUser', $arrayContrato);



                

                if($res){ 
                    if($res->error > 0){

                        $mensajeError = $this->Simpletv->mensajeErrorSTV_VTA($res->codeError);
                        $mensajeError = $this->formatearCadena($mensajeError);

                        $response = array(
                           'error'=>1,
                           'codeError'=>$res->codeError,
                           'mensajeError'=>$mensajeError
                        );
                        $this->set('response', $response);
                    }else{

                       //identificación del cliente en simple TV, esto proviene de la validación del usuario 
                       $customerId = $res->CustomerId;

                       $this->Session->write('recarga.simpletv.customerId',$customerId);
                       $urlBalance = Configure::read('Simple_TV.urlBalance');

                        $arrayBalance = array(
                            'customerId'=>$customerId,
                            'userSTV'=>$userSTV,
                            'passwordSTV'=>$passwordSTV,
                        );  
                         
                        $res = $this->Simpletv->invocaCurlStv('getBalance', $arrayBalance);

                        if($res){
                            if($res->error == '0'){
                                $response = array(
                                    'error'=>$res->error,
                                    'saldo'=>$res->balance,
                                    'dias'=>$res->daysProgramming,
                                    'renta'=>$res->amountPrepaid
                                );
                            }else{
                                $mensajeError = $this->Simpletv->mensajeErrorSTV_VTA($res->codeError);
                                $mensajeError = $this->formatearCadena($mensajeError);

                                $response = array(
                                    'error'=>$res->error,
                                    'codeError'=>$res->codeError,
                                    'mensajeError'=>$mensajeError
                                );
                            }
                            $this->set('response', $response);  
                        }else{
                            $codeError = 'SC-LV-0002';
                            $mensajeError = $this->Simpletv->mensajeErrorSTV_VTA($res->codeError);
                            $mensajeError = $this->formatearCadena($mensajeError);
                            $response = array(
                               'error'=>1,
                               'codeError'=>$codeError,
                               'mensajeError'=>$mensajeError
                            );
                            $this->set('response', $response);
                        } 

                    }
                }else{
                    $codeError = 'SC-LV-0001';
                    $mensajeError = $mensajeError = $this->Simpletv->mensajeErrorSTV_VTA($codeError);
                    $mensajeError = $this->formatearCadena($mensajeError);

                    

                    $response = array(
                       'error'=>1,
                       'codeError'=>$codeError,
                       'mensajeError'=>$mensajeError
                    );
                    $this->set('response', $response);

                }              
        }else{

            $codeError = 'SC-LV-0006';
            $mensajeError = $this->Simpletv->mensajeErrorSTV_VTA($res->codeError);
            $mensajeError = $this->formatearCadena($mensajeError);

            $response = array(
               'error'=>1,
               'codeError'=>$codeError,
               'mensajeError'=>$mensajeError
            );
            $this->set('response', $response);  
        } 

	}	
	
	/**
	* Acción para obtener el monto de un servicio	
	* @param string $servicio
	*/
	function obtenerMonto($servicio){
		$this->layout = 'ajax';
		$usuario = $this->Session->read('usuarios.datos');
		$response = array();
		
		if(!empty($servicio)){
		
			$afiliacion = $this->Afiliacion->find(
					'first',
					array(
						'conditions'=>array(
							'usuario_id'=>$usuario['id'],
							'id'=>$servicio
						)
					)
				);
				
			$response = array(
				'monto_maximo' => formatea_monto_fromWs($afiliacion['Afiliacion']['monto_maximo'])
			);
			
			$this->set('response', $response);		
		}
	}
	
	/**
         *Acción para la confirmación del pago de servicios
        **/
	function recarga_simpletv_confirmacion(){
	
		$this->layout = 'interna';
		$this->set('seccion_actual','recarga.simpletv');	
		//$this->set('servicios', $this->Afiliacion->buscarTiposServicios());                
		$usuario = $this->Session->read('usuarios.datos');               
		$this->data = $this->Session->read('recarga_simpletv');

		$modalidad = 0;
                
		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
            $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
            $documento_identificacion = desencriptar($usuario['cedula']);
		}
		/** fin Para identificar si es un usuario juridico **/
		
		if($this->Session->check('recarga_simpletv')){
			$this->data = $this->Session->read('recarga_simpletv');
			$this->set(compact('error'));
			
		} else {                        
			$this->redirect('recarga_simpletv');
		}

	}
	
    
        /**
        * Acción para el resultado del pago de servicios, ejecuta el pago
        */
	function recarga_simpletv_resultado(){
		
        /* Para validar el acceso al modulo */
		if(!validarTransaccion(102100)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
                
        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                $this->set('seccion_actual','recarga.simpletv');
		
        $usuario = $this->Session->read('usuarios.datos');
		$estado = $this->Session->read('recarga_simpletv');
                $confirmacion= "";
                $referencia="";
		$modalidad = 0;                                

		$this->layout = 'interna';				
                
		if($this->Session->check('recarga_simpletv')){

            $this->data = $this->Session->read('recarga_simpletv');



            $nroContrato = $this->data['RecargaSimpletv']['contrato'];

            $tipo_servicio = $this->data['RecargaSimpletv']['tipoServicio'];
            $tipo_operacion = trim($this->data['RecargaSimpletv']['tipoOperacion']);
            $ctaDebitar =  $this->data['RecargaSimpletv']['cuentaDebitar'];


            $this->data['RecargaSimpletv']['operacion'] = 'Recarga Simpletv';
            $tipo_transaccion_pendiente = 'rsv';
            $recarga_minima=_p('recarga_minima_simple_tv');
            $recarga_maxima=_p('recarga_maxima_simple_tv');
            $customerId = $this->Session->read('recarga.simpletv.customerId');

            $montoRecarga = $this->data['RecargaSimpletv']['montoFinal'];
            $descripcion = $this->data['RecargaSimpletv']['descripcion'];

           


            $monto =  str_replace(".", "",$montoRecarga); 
            $montoRec =  str_replace(",", ".",$monto);

           /** Para identificar si es un usuario juridico **/
            if($usuario['tipo_rif']){
                $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
            }else{
                $documento_identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
            }
            /** Fin Para identificar si es un usuario juridico **/

            $res = '';
            // Identificar tipo de usuario (natural o jurídico)	

            if(empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2){  // Master único	

                

                if(_p('sms.otp.activo')=='0' && $this->data['RecargaSimpletv']['tipoDestinatario']=='a'){
                    $validacion_otp['validado'] = true;
                }else{
                    $validacion_otp = $this->Session->read('seguridad.validar_otp');
                }  
                if(			
                    (
                        $validacion_otp == null ||
                        !isset($validacion_otp['validado']) ||
                        !$validacion_otp['validado']
                    )
                ){
                    $url_volver = '/recarga_simpletv/afiliados';
                    $this->Session->write('seguridad.validar_otp',
                            array(
                                'url_aceptar'=>'/recarga_simpletv/recarga_simpletv_resultado',
                                'url_volver' => $url_volver,
                                'layout'=>'interna',
                                'titulo'=>'Recarga Simpletv Prepago',
                                'pasos'=> $estado['pasos'],
                                'paso_actual'=>2,
                                'seccion_actual'=>'recarga.simpletv'
                            )
                    );
                    $this->redirect('/seguridad/genera_otp');
                }
                

                $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],9,'0',STR_PAD_LEFT);

                /* guardar en el 400   */

                //$ctaDebitar = str_pad($this->data['RecargaSimpletv']['cuentaDebitar'],12,'0',STR_PAD_LEFT);
                $ctaDebitar = substr($this->data['RecargaSimpletv']['cuentaDebitar'],10,10);

                
                //Realizar el debito 
                $dataAs400 = array(
                    'ctaDebitar'=>$ctaDebitar,
                    'nroContrato'=>$nroContrato,
                    'montoRec'=>$montoRec,
                    'documento_identificacion'=>$documento_identificacion,
                    'tracerID'=>0,
                    'referencia'=>0,
                    'accion'=>'N',
                    'ip'=>$ip,
                    'usuario'=>desencriptar($usuario['login']),
                );

                $res = $this->Simpletv->as400Simple($dataAs400);



                if(!$res){
                    $swValidar = 1;
                    $ruta = 'no_recarga/'.$swValidar;
                    $this->redirect($ruta);                  
                }else{
                     
                    $swAS400 = 0;    
                    $error = $res['IN800201Return']['IN800201']['IN8002ERR'];    
                    //si existe un error desde el as400, lo registra en bitacora y lo redirecciona
                    //a una pantalla de error.     
                    if($error > 0){
                        //registrar log
                        //INDICAR EL ERROR y EESCRIBIRLO
                        $descripcion = $res['IN800201Return']['IN800201']['IN8002MSE'];
                        //INDICAR EL ERROR y ESCRIBIRLO  
                        $this->set(compact('descripcion'));
                        $this->log('Recarga Simple TV'.$this->data['RecargaSimpletv']['operacion'].' - '. $descripcion . ' - ' . $res, LOG_ERR);
                        $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                102100,
                                $this->data['RecargaSimpletv']['cuentaDebitar'],
                                $nroContrato,
                                $this->data['RecargaSimpletv']['monto'],
                                NULL,
                                $descripcion,
                                1
                        );
                        $swValidar = 2;

                        $ruta = 'no_recarga/'.$swValidar.'/'.$descripcion;
                        $this->redirect($ruta);                        
                    }    
                }

                if($swAS400==0){ 

                    ////proceder a actualizar el Web service de SimpleTV
                    //esta referencia viene del 400 y se completa con cero a la derecha
                    $referenciaOrig = $res['IN800201Return']['IN800201']['IN8002CKN'];
                    $referencia = str_pad($referenciaOrig,8,'0',STR_PAD_LEFT);


                    $this->data['RecargaSimpletv']['REFERENCIA'] = $referencia;

                    $userSTV = Configure::read('Simple_TV.user');
                    $passwordSTV = Configure::read('Simple_TV.pwd');
                    $ledgerAccount = Configure::read('Simple_TV.ledgerAccount'); 
                    $urlPaid = Configure::read('Simple_TV.urlPaid'); 


                    $arrayPaid = array('customerId'=>$customerId,
                                       'monto'=>$montoRec,
                                       'referencia'=>$referencia,
                                       'userSTV'=>$userSTV,
                                       'passwordSTV'=>$passwordSTV,
                                       'ledgerAccount'=>$ledgerAccount
                    );

                    $referenciaOperadora = '';
                    $diaProgramacionActual = '';
                    $res = $this->Simpletv->invocaCurlStv('paidStv', $arrayPaid);

                    $errorSimple = 0;
                    $swTimeOut = 0;


              
                    if($res){
                        if($res->error == '0'){
                            $referenciaOperadora = $res->transactionId;
                            $diaProgramacionActual = $res->daysProgramming; 
                            $fechaDeconexion = $res->dateDeconexion; 
                        }else{
                            //validar que tipo de error regresa, si es time out 
                            if($res->codeError == 'MI-0083' || $res->codeError == 'TM-9999'){
                                $errorSimple = 0;
                                $swTimeOut = 1;
                                $codeError = $res->codeError;
                                $referenciaOperadora = _p('stv.ref.operadora.default');
                            } else{
                                $errorWS = $res->error;
                                $codiErrorWs = $res->codeError;
                                $mensajeErrorWs = $res->mensajeError;
                                $mensajeErrorVtaWs = $res->mensajeErrorVta;
                                $mensajeSTV = $this->Simpletv->mensajeErrorSTV_STV($res->codeError); 
                                $mensajeVta = $this->Simpletv->mensajeErrorSTV_VTA($res->codeError);
                                $errorSimple=4;     
                            }
                        }

                  
                        if($errorSimple==0){
                             //Guardar codifo tracerID en el debito efectuado.
                             $dataAs400['tracerID'] = $referenciaOperadora;
                             $dataAs400['accion'] = 'U';
                             $dataAs400['referencia'] = $referenciaOrig;
                             $res = $this->Simpletv->as400Simple($dataAs400);

                        }
                    }else{
                        $mensajeSTV = '';
                        $errorSimple=3;  
                    } 

                     
                    if($errorSimple > 0){
                        //Reverso del AS400
                        $dataAs400['tracerID'] = 0;
                        $dataAs400['accion'] = 'Y';
                        $dataAs400['referencia'] = $referenciaOrig;
                        $res = $this->Simpletv->as400Simple($dataAs400);                        

                        //Quitar cuando este el reverso
                        if(!$res){
                            $swValidar = 1;
                            $ruta = 'no_recarga/'.$swValidar;
                            $this->redirect($ruta);                            
                        }else{
                            $descripcion =  $mensajeSTV;    
                            $this->log('Recarga Simple TV'.$this->data['RecargaSimpletv']['operacion'].' - '. $descripcion . ' - ' . $res, LOG_ERR);
                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                102100,
                                $this->data['RecargaSimpletv']['cuentaDebitar'],
                                $nroContrato,
                                $this->data['RecargaSimpletv']['montoFinal'],
                                NULL,
                                $descripcion,
                                1
                            );                            

                            $ruta = 'no_recarga/'.$errorSimple . '/' . $mensajeVta;
                            $this->redirect($ruta);
                        }
                    } 

                   
                    if($errorSimple == 0){  
                        ///////// Modificar el transaccion ID en el as400

                        if($swTimeOut == 0){
                            $descripcion='Servicio Recarga Simpletv';
                        }else{
                            $descripcion='Servicio Recarga Simpletv codigo ( '. $codeError . ' )';
                        }    


                        $this->Bitacora->registraBitacora(
                        $usuario['id'],
                        102100, 
                        $this->data['RecargaSimpletv']['cuentaDebitar'],
                        $nroContrato,
                        $this->data['RecargaSimpletv']['monto'],
                        $referencia,
                        $descripcion,
                        0
                        );

                        /*referencia de simple TV de ser afirmativo la operacion web service*/

                        $this->data['RecargaSimpletv']['REFERENCIA'] = $referencia;
                        $this->data['RecargaSimpletv']['REFERENCIA_OPERADOR'] = $referenciaOperadora;

                        $this->data['RecargaSimpletv']['DIAPROGRAMACION'] = $diaProgramacionActual;
                        $this->data['RecargaSimpletv']['FECHADECONEXION'] = $fechaDeconexion;

                        $this->data['RecargaSimpletv']['CONCEPTO'] = 'RECARGA SIMPLE TV';
                        $this->data['RecargaSimpletv']['TIPO'] = 'Servicio' .$this->data['RecargaSimpletv']['operacion'];
                        $this->data['RecargaSimpletv']['ESTATUS'] = 'PROCESADO';
                        $this->data['RecargaSimpletv']['MONTO'] = $this->data['RecargaSimpletv']['montoFinal'];


                        //en estos momento esta en blanco requeiro ver el nro de contrato
                        //eliminar esta linre adespues validar nro de contrato
                        $this->data['RecargaSimpletv']['NRO_BENEFICIARIO'] = $contrato;                        

                        // Genera la data para la descarda de pdf
                        $this->Session->write('recarga.simpletv.descarga.pdf', $this->data['RecargaSimpletv']);                                                                                
                            
                        // Email Orden
                        // Email Opciones
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery = 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = desencriptar($usuario['email_principal']);
                        $this->Email->subject = 'Notificacion Recarga de Simpletv';
                        $this->Email->sendAs = 'html';


                        // Informacion del correo
                        $this->Email->template = 'recargasimpletv_ordenante';
                        $tipo_accion = 'Recarga Simpletv';
                        $tipo_afiliacion = $this->data['RecargaSimpletv']['operacion'];
                        $nombreBeneficiario= $this->data['RecargaSimpletv']['nombreBeneficiario'];
                    
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion','nombreBeneficiario',' referenciaOperadora'));

                        // Informacion del SMS
                        $sms = array(
                         'numcel'=>desencriptar($usuario['celular']),
                         'canal1'=>'59',
                         'fectim'=>$operador,
                         'cheque'=>$contrato,
                         'cuenta'=>substr($this->data['RecargaSimpletv']['cuentaDebitar'],16,4),
                         'monto'=>$this->data['RecargaSimpletv']['montoFinal'],
                         'diaProgramacion'=>$this->data['RecargaSimpletv']['DIAPROGRAMACION'],
                         'fechaDesconexion'=>$this->data['RecargaSimpletv']['FECHADECONEXION'],


                         'fecha'=>date('Ymd'),
                         'hora'=>date('His'),
                         'refnum'=>substr($referencia,5,4),
                         'xrnde38'=>'IB'
                        );

                        $this->Email->send();

                    }   
                }

                $this->set('pendiente',false);
                //Fin usuario natural o master unico
            } // finif(empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2){  // Master único
            else if(
                        $usuario['rol_juridico_id'] == 3  || // Operador
                        $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
                ){



               // En caso de jurídico, sólo los operadores y operadores/aprobadores pueden
                // generar un pago. En cuyo caso, se deja pendiente por aprobar
                $error = 0;
                $numeroContrato = $this->data['RecargaSimpletv']['numeroContrato'];


               $res = $this->TransaccionPendiente->save(
                    array(
                        'TransaccionPendiente'=>array(
                        'usuario_id'=>$usuario['id'],
                        'tipo'=>$tipo_transaccion_pendiente,
                        'tipo_servicio'=>trim($this->data['RecargaSimpletv']['tipoServicio']),
                        'estatus'=>'pa',
                        'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                        'monto'=>formatea_monto_toWs($this->data['RecargaSimpletv']['montoFinal']),
                        'aprobaciones_requeridas'=>$this->data['RecargaSimpletv']['aprobaciones_requeridas'],
                        'tipo_rif'=>$usuario['tipo_rif'],
                        'rif'=>$usuario['rif'],
                        'cuenta_origen'=>encriptar($this->data['RecargaSimpletv']['cuentaDebitar']),
                        'cuenta_destino'=>encriptar($this->data['RecargaSimpletv']['numeroContrato'] . '/' . $customerId),
                        //'descripcion'=>encriptar($this->data['RecargaSimpletv']['operacion']),
                        'descripcion'=>encriptar($descripcion),
                        'nombre_beneficiario'=>encriptar($this->data['RecargaSimpletv']['nombreBeneficiario']),
                        'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                        'id_beneficiario'=>$usuario['rif'],
                        'email_beneficiario'=>null,
                        'alias'=>null
                                )
                        ),
                        false
                );


                $pendiente = false; 
                if($res){
                    $error = 0;
                    $referencia = $this->TransaccionPendiente->id;
                    $pendiente = true;

                } else {
                    $error = -1;
                    $referencia = '';
                }
                        
                $this->data['RecargaSimpletv']['REFERENCIA'] = $referencia;

                $error_afiliacion = false;			
                $this->set('pendiente',true);	

            }//Fin Transacciones Pendientes
                    
            $this->set(compact('error','referencia','confirmacion','pendiente'));
            $this->Session->delete('recarga_simpletv');

		} else {   //if($this->Session->check('recarga_servicios')){                              
            $this->redirect('recarga_simpletv');
		}		
	}
		   
        /**
         *Acción para la descarga del archivo pdf 
        */
	function descargar_pdf_sevicios(){
		if(!$this->Session->check('recarga.simpletv.descarga.pdf') && $this->Session->check('usuarios.datos')){
			$this->redirect('/consultas/integral');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$data = $this->Session->read('recarga.simpletv.descarga.pdf');

		$this->set(compact('data','usuario'));
		$this->render('/elements/descargar_pdf_simpletv');
	}
        
        /**
	* Acción para mostrar advertencia de operación realizada	
	*/
	function transaccion_advertencia(){
		
		if(!$this->Session->check('transaccion.advertencia')) $this->redirect('/consultas/integral');
		
		$this->data = $this->Session->read('transaccion.advertencia');
		
		$this->layout = 'interna';

		$this->set('seccion_actual', $this->data['seccion_actual']);
		
		$this->render('/elements/transaccion_advertencia');
	}
		
	/*** Fin Pago Servicios ***/


    function no_recarga($swValidar, $mensaje = null){
        $this->layout = 'interna';
        $proceso = 'RECARGA SIMPLE TV';
        $servicio = 'AS400';
        switch ($swValidar) {
          case 1:
              $mensaje = _m('error.fatal');
              $descripcion = $mensaje . " [ AS400 S/C ]";          
              break;
          case 2:
              $descripcion = $mensaje . " [ AS400 ]";          
              break;       
          case 3:
              $mensaje = _m('error.fatal');
              $descripcion = $mensaje . " [ S/C LV-0003 ]";          
              break;  
          case 4:
              $descripcion = $mensaje;          
              break; 

          case 6:
              $descripcion = $mensaje;          
              break;               
        }         

        //$descripcion = $mensaje; 
        $this->set(compact('descripcion'));

    }


    function formatearCadena($cadena) {
        $cadena = trim($cadena);
        $cadena = str_replace('á', 'a', $cadena);
        $cadena = str_replace('é', 'e', $cadena);
        $cadena = str_replace('í', 'i', $cadena);
        $cadena = str_replace('ó', 'O', $cadena);
        $cadena = str_replace('ú', 'u', $cadena);
        $cadena = str_replace('ñ', 'n', $cadena);
        $cadena = str_replace('Á', 'A', $cadena);
        $cadena = str_replace('É', 'E', $cadena);
        $cadena = str_replace('Í', 'I', $cadena);
        $cadena = str_replace('Ó', 'O', $cadena);
        $cadena = str_replace('Ú', 'U', $cadena);
        $cadena = str_replace('Ñ', 'N', $cadena);
        $cadena = str_replace('?', 'ON', $cadena);    
        $cadena = str_replace('¿', '', $cadena);
        return $cadena;
    }


}