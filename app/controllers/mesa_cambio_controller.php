<?php
/**
 * Controlador para las operaciones de Divisas.
 * 
 * @author Emsys
 */


/**
 * Operaciones de Divisas.
 * 
 */
class MesaCambioController extends AppController {	        
        
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
            'TransaccionMesaCambio'            
	);

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session', 'Email', 'MesaCambio');

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
            if(!validarTransaccion(101074)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */
            
            $this->Session->delete('usuarios.descarga.pdf');
            $this->layout = 'interna';
            $this->set('seccion_actual','comprar.divisas');

          

            $sw_cuentaDebitar = 0;
            $errorDivisa = 0;
            $limite_cambio =$this->Parametro->findByCodigo('ms_menudeo');

            if(!empty($this->data['MesaCambio'])){
                $monto = str_replace('.', '', $this->data['MesaCambio']['monto_divisa']);
                $arrayMonto = explode(',', $monto);
                if($arrayMonto[0]=='0'){
                   $this->TransaccionMesaCambio->validationErrors['monto_divisa'] = 'sin_cero';
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
                    101074,
                    $this->data['MesaCambio']['cuentaDebitar']
                    );
                    
                    if(!$transaccion_detalle) {
                        $sw_cuentaDebitar = 1;
                        $this->TransaccionMesaCambio->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                    }
	        }

                if(empty($this->TransaccionMesaCambio->validationErrors)){
                    if(isset($transaccion_detalle)){
                        $this->data['MesaCambio']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if(is_numeric($transaccion_detalle['limite'])){
                        $this->data['MesaCambio']['monto_limite_usuario']=formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['MesaCambio']['monto_limite_usuario']='';
                        }
                    }
			
                    $this->TransaccionMesaCambio->set($this->data);

                    if($this->TransaccionMesaCambio->validates(array('fieldList'=>array('cuentaDebitar', 'cuentaDestino', 'monto')))){
                         $this->Session->write('mesaCambio.compra', $this->data);
                         $this->redirect('compra_confirmacion');
                    }
                }
            
            }else if(isset($volver) && $this->Session->check('mesaCambio.compra')){
                $this->data = $this->Session->read('mesaCambio.compra');
            }
            $usuario = $this->Session->read('usuarios.datos');
            $cuentas = $this->Activo->buscarCuentasSaldos();
            $cta_acreditar = array();
            $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();

            $tipoDocumento=$usuario['perfil_ib_id']==1 ? $usuario['tipo_cedula'] : $usuario['tipo_rif'];
            switch ( $tipoDocumento) {
                case 'V':
                   if($usuario['perfil_ib_id']==1){
                         $origenFondosId=28;
                    }else{
                         $origenFondosId=31;
                    }                          
                    break;
                case 'J':
                   $origenFondosId=31;                
                    break;
                case 'P':
                   $origenFondosId=30;                  
                    break;
                case 'E':
                   $origenFondosId=29;                 
                    break;
                default:
                   $origenFondosId=3;
                    break;
            }
            $origenFondos = $this->MesaCambio->consultaParametros($origenFondosId);
            $motivos= $this->MesaCambio->consultaParametros(4);
            
            foreach ($cuentasValidar as $key => $value) {
                $valores = explode('-', $value);
                $valores = array_map('trim', $valores);
                $query = $this->MesaCambio->validarProducto($valores[1],1,'VEB',$valores[2]);                
                   
                if($query==true){
                    $cta_acreditar[$key.'-'.$query['fee_percentage'].'-'.$query['rate'].'-'.$query['available'].'-'.$valores[2].'-'.$valores[1].'-'.$query['name_product']] = '**'.substr($key,-4) . ' - ' . $query['name_product'] . ' - ' . $valores[2] . ' - ' . $valores[3];
                }
            }
             $limite_cambio =$this->Parametro->findByCodigo('ms_menudeo');
             $limite_cambio=$limite_cambio['Parametro']['valor'];

             $usuario = $this->Session->read('usuarios.datos');
             if($usuario['perfil_ib_id']==1){

                $tipo="NAT";
             }else{
                $tipo="JUR";
             }


            $this->set(compact('cuentas','cta_acreditar','origenFondos','motivos','sw_cuentaDebitar','errorDivisa','limite_cambio','tipo'));
	}

	
	/**
	 * Metodo para realizar la confirmacion de la compra de divisas.
	 */
	function compra_confirmacion(){

            $this->layout = 'interna';
            $usuario = $this->Session->read('usuarios.datos');
            $this->set('juridico',$usuario['tipo_rif']);
            $this->set('seccion_actual','comprar.divisas');	

            if($this->Session->check('mesaCambio.compra')){                
                $this->data = $this->Session->read('mesaCambio.compra');                
            }else {
		$this->redirect('compra');
            }	
	}

	
	/**
	 * Metodo para realizar el proceso de Divisas compra.
	 */
	function compra_resultado(){
		
            $this->layout = 'interna';
                
            $this->set('seccion_actual','comprar.divisas');
                     
            if($this->Session->check('mesaCambio.compra')){

                //Arreglar decimales de tasa cambio (Solicitud Hector casares)
                $this->data = $this->Session->read('mesaCambio.compra');
                $cuentaAcreditar = $this->data['MesaCambio']['cuentaCreditar'];
                $datosCuenta = explode('-',$cuentaAcreditar);                
                $tasaCuenta = $datosCuenta[2];
                $tasaCuenta = str_replace(",", ".",$tasaCuenta);

                $this->data = $this->Session->read('mesaCambio.compra');
                // Validacion anti fraude
                $cuentas = $this->Activo->buscarCuentasME();	
                if(
                    !in_array($this->data['MesaCambio']['cuentaDebitar'], array_keys($cuentas))
                ){
                    $this->redirect('/consultas/integral');
                }
                //  Fin validacion anti fraude
                
                $usuario = $this->Session->read('usuarios.datos');                
                $moneda = $this->data['MesaCambio']['moneda'];                
                $cuentaDebitar = str_pad(substr($this->data['MesaCambio']['cuentaDebitar'] ,10), 20, "0", STR_PAD_LEFT);
                $cuentaCreditar = str_pad(substr($this->data['MesaCambio']['cuentaDestino'] ,10), 20, "0", STR_PAD_LEFT);
                $cuentaDebitarA = $this->data['MesaCambio']['cuentaDebitar'];
                $cuentaCreditarA = $this->data['MesaCambio']['cuentaDestino'];
                $referencia = "";
                $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
                $origen = explode('-',$this->data['MesaCambio']['origenFondos']);
                $motivo = explode('-',$this->data['MesaCambio']['motivos']);
                $descripcion = "";
                $limite_cambio =$this->Parametro->findByCodigo('ms_menudeo');
                 $usuario = $this->Session->read('usuarios.datos');
                  if($usuario['perfil_ib_id']==1){
                    $tipoIdentificacion=$usuario['tipo_cedula'];
                    $identificacion=desencriptar($usuario['cedula']);
                    $tipo='NAT';
                     }else{
                    $tipoIdentificacion=$usuario['tipo_rif'];
                    $identificacion=desencriptar($usuario['rif']);
                    $tipo='JUR';
                }


                // Identificar tipo de usuario (natural o juridico)			
                if(
                    empty($usuario['tipo_rif']) ||
                    $usuario['rol_juridico_id'] == 2 // Master Unico
                ){
                    $montoRV=str_replace('.', '', $this->data['MesaCambio']['monto_divisa']);
                   
                    //Mesa
                    if(round($montoRV) > round($this->data['MesaCambio']['parametroMenudeo'])){

                        $trans=1;
                      
                          //Servicio de token
                        $dataToken = array();
                        $dataToken["username"] = Configure::read('Api.user');
                        $dataToken["password"] = Configure::read('Api.password');                        
                        $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                        $tokenApp = json_decode(json_encode($tokenApp), true);                        
                        $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];                
                       
                        $dataRest = array();

                        $dataRest["exchange_cod"] = $this->data['MesaCambio']['exchange_codMesa'];
                        $dataRest["exchange_type_cod"] = $this->data['MesaCambio']['exchange_type_codMesa'];
                        $dataRest["exchange_operation_cod"] = $this->data['MesaCambio']['exchange_operation_codMesa'];
                        $dataRest["currency_cod"] = $this->data['MesaCambio']['currency_target_cod'];
                        $dataRest["ip"] =  Configure::read('ip.client');
                        $dataRest["canal"] =  4;
                        $dataRest["tipo"] = '';
                        $dataRest["customer_type_cod"] = $tipo;
                        $dataRest["amount_order"] =formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                         $dataRest["product_target"] =$this->data['MesaCambio']['product_target_codMesa'];
                        $dataRest["product_source"] =$this->data['MesaCambio']['product_source_codMesa'];
                        
                        $dataRest["operation_type"] = 'VB';
                        $dataRest["process_type"] = '01';
                        $dataRest["account_source"] = $this->data['MesaCambio']['cuentaDebitar'];
                        $dataRest["account_target"] = $this->data['MesaCambio']['cuentaDestino'];
                        $dataRest["identification_type"] =$tipoIdentificacion;
                        $dataRest["identification"] = $identificacion;
                        $dataRest["account_target"] = $cuentaCreditar;
                        $dataRest["amount_source"] =formatea_monto_toWs($this->data['MesaCambio']['monto']);
                        $dataRest["rate_client"] =formatea_monto_toWs($this->data['MesaCambio']['tasa']);
                        
                        $dataRest["amount_target"] = formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                        $dataRest["amount_commission"] =formatea_monto_toWs($this->data['MesaCambio']['comision']);
                        $dataRest["funds_source"] =$origen[1];
                        $dataRest["funds_destination"] =$motivo[1];

                                           
                                                                          
                       $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeTableNew',"POST",$dataRest, $token);


                    //Bloqueo y validacion


                     $response = (array) $response;
                     $responseError=(array) $response['data'];
                     $responseError=(array) $response['data'][0];
                     $referencia=(array) $responseError['INVCREFE'];                 
                     $responseError=(array) $responseError['error'];

                                       
                    if(!isset($responseError['ERRNUM'])){

                        
                        $referencia=$referencia[0];
                         
                        $dataRest = array();

                        $dataRest["exchange_cod"] = $this->data['MesaCambio']['exchange_codMesa'];
                        $dataRest["exchange_type_cod"] = $this->data['MesaCambio']['exchange_type_codMesa'];
                        $dataRest["exchange_operation_cod"] = $this->data['MesaCambio']['exchange_operation_codMesa'];
                        $dataRest["currency_cod"] = $this->data['MesaCambio']['currency_target_cod'];
                        $dataRest["ip"] =  Configure::read('ip.client');
                        $dataRest["tipo"] = '';
                        $dataRest["canal"] = 4;
                        $dataRest["customer_type_cod"] = $tipo;
                        $dataRest["amount_order"] =formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                        $dataRest["product_target"] =$this->data['MesaCambio']['product_target_codMesa'];
                        $dataRest["product_source"] =$this->data['MesaCambio']['product_source_codMesa'];
                        $dataRest["process_type"] = '01';
                        $dataRest["account_source"] = $this->data['MesaCambio']['cuentaDebitar'];
                        $dataRest["account_target"] = $this->data['MesaCambio']['cuentaDestino'];
                        $dataRest["identification_type"] =$tipoIdentificacion ;
                        $dataRest["identification"] = $identificacion;
                        $dataRest["account_target"] = $cuentaCreditar;
                        $dataRest["amount_source"] =formatea_monto_toWs($this->data['MesaCambio']['monto']);
                         $dataRest["rate_client"] =formatea_monto_toWs($this->data['MesaCambio']['tasa']);
                        
                        $dataRest["amount_target"] = formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                        $dataRest["amount_commission"] =formatea_monto_toWs($this->data['MesaCambio']['comision']);

                        $dataRest["reference_number"] =$referencia;
                        $dataRest["operation_type"] = 'PC';
                        $dataRest["funds_source"] =$origen[1];
                        $dataRest["funds_destination"] =$motivo[1];

                                                
                                                                                                    
                        $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeTableNew',"POST",$dataRest, $token);
                    //Bloqueo y validacion*/
                         $error = "-1";
                         $descripcion = "Error inesperado, transaccion no disponible.";
                         $response = (array) $response;
                         $responseError=(array) $response['data'];
                         $responseError=(array) $response['data'][0];
                         $referencia=(array) $responseError['INVCREFE'];
                         $montoAdjudicado=(array) $responseError['INVCMTOD'];
                         $montoTotal=(array) $responseError['INVCMTOO'];
                         $montoComision=(array) $responseError['INVCMTOC'];
                         $this->data['MesaCambio']['comision']= $montoComision[0];
                         $total=$montoTotal[0] + $montoComision[0];
                         $responseError=(array) $responseError['error'];
                         $referencia=$referencia[0];
                         $this->data['MesaCambio']['lote']= $referencia;

                                            

                        if(isset($responseError['ERRNUM'])){

                            $error = intval($responseError['ERRNUM']);                        
                            $descripcion = $responseError['ERDS01'];

                            $this->log('Divisas Compra - '. $error . '-' . $descripcion, LOG_ERR);				
                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                101074,
                                $cuentaDebitarA,
                                $cuentaCreditarA,
                                $this->data['MesaCambio']['monto'],
                                NULL,
                                $descripcion,		
                                1
                            );                                                        
                            
                           
                            
                        }else{                                           

                                    $error = 0;

                                    $this->Bitacora->registraBitacora(
                                        $usuario['id'],
                                        101074,
                                        $cuentaDebitarA,
                                        $cuentaCreditarA,
                                        $this->data['MesaCambio']['total'],
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

                                    $this->data['MesaCambio']['REFERENCIA'] = $referencia;
                                    $this->data['MesaCambio']['CONCEPTO'] = 'DIVISA';
                                    $this->data['MesaCambio']['TIPO'] = 'com';
                                    $this->data['MesaCambio']['NRO_BENEFICIARIO'] = $this->data['MesaCambio']['cuentaDestino'];
                                    $this->data['MesaCambio']['tipoCedulaRif'] = $usuario['tipo_cedula'];
                                    $this->data['MesaCambio']['nroCedulaRif'] = desencriptar($usuario['cedula']);
                                    $this->data['MesaCambio']['nombreBeneficiario'] = $nombre_beneficiario;
                                    $this->data['MesaCambio']['ESTATUS'] = 'EJECUTADO';
                                    $this->data['MesaCambio']['descripcion']  = ' - ';
                                    $this->data['MesaCambio']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
                                    $this->data['MesaCambio']['monto_divisaAdjudicado'] = $montoAdjudicado[0];
                                     $this->data['MesaCambio']['total'] = $total;
                                    // Genera la data para la descarda de pdf
                                    $this->Session->write('usuarios.descarga.pdf', $this->data['MesaCambio']);
                                    
                                    $save = $this->TransaccionMesaCambio->save(
                                        array(
                                            'TransaccionMesaCambio'=>array(
                                            'usuario_id'=>$usuario['id'],
                                            'tipo'=>'com',
                                            'estatus'=>'ej',
                                            'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                            'monto_divisa'=>formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']),
                                            'tasa'=> formatea_monto_toWs($this->data['MesaCambio']['tasa']),
                                            'comision'=> formatea_monto_toWs($this->data['MesaCambio']['comision']),
                                            'monto'=>formatea_monto_toWs($this->data['MesaCambio']['monto']),
                                            'total'=>formatea_monto_toWs($this->data['MesaCambio']['total']),
                                            'moneda'=>$this->data['MesaCambio']['currency_target_cod'],                                   
                                            'tipo_rif'=>$usuario['tipo_rif'],
                                            'rif'=>$usuario['rif'],
                                            'cuenta_origen'=>encriptar($this->data['MesaCambio']['cuentaDebitar']),
                                            'cuenta_destino'=>encriptar($this->data['MesaCambio']['cuentaDestino']),
                                            'referencia' => $referencia,
                                            'monto_adjudicado' =>$montoAdjudicado[0]

                                            )
                                        ),
                                        false
                                    );
                                    
                                    
                            
                        } 
                    
                    }else{
                        
                        $error = intval($responseError['ERRNUM']);                        
                        $descripcion = $responseError['ERDS01'];

                        $this->log('Divisas Compra - '. $error . '-' . $descripcion, LOG_ERR);				
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            101074,
                            $cuentaDebitarA,
                            $cuentaCreditarA,
                            $this->data['MesaCambio']['monto'],
                            NULL,
                            $descripcion,		
                            1
                        );
                         
                        
                    }
                                      
                    $this->set('pendiente',false);



                     }else{

                            $datos_basicos = $this->Session->read('usuarios.nibpf3010');            
                            if($usuario['tipo_rif']){
                                $nombre_beneficiario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                            } else {
                                $nombre_beneficiario = desencriptar($usuario['nombre']);
                            }

                            
                           //Servicio de token
                        $dataToken = array();
                        $dataToken["username"] = Configure::read('Api.user');
                        $dataToken["password"] = Configure::read('Api.password');                        
                        $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                        $tokenApp = json_decode(json_encode($tokenApp), true);                        
                        $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];  

                        if ($tipoIdentificacion == 'V') {
                            $identificacion = substr($identificacion,0,8);
                        }else{
                            $identificacion;
                        }   


                       $dataRest = array();

                        $dataRest["product_target"] =$this->data['MesaCambio']['product_target_cod'];
                        $dataRest["product_source"] =$this->data['MesaCambio']['product_source_cod'];
                        $dataRest["canal"] = 4;
                        $dataRest["operation_type"] = "VB";
                        $dataRest["process_type"] = '01';
                        $dataRest["account_source"] = $this->data['MesaCambio']['cuentaDebitar'];;
                        $dataRest["account_target"] = $this->data['MesaCambio']['cuentaDestino'];;
                        $dataRest["account_target"] = $cuentaCreditar;
                        $dataRest["amount_source"] =formatea_monto_toWs($this->data['MesaCambio']['monto']);
                        $dataRest["amount_target"] = formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                        $dataRest["amount_commission"] =formatea_monto_toWs($this->data['MesaCambio']['comision']);
                        $dataRest["rate_client"] =$this->data['MesaCambio']['tasaMenudeo'];
                        $dataRest["funds_source"] =$origen[1];
                        $dataRest["funds_destination"] =$motivo[1];
                        $dataRest["identification_type"] =$tipoIdentificacion;
                        $dataRest["identification"] = $identificacion;
                        $dataRest["intervention_date_ini"] = date('ymd');
                        $dataRest["intervention_date_fin"] = date('ymd');  
                        $dataRest["intervention_date_val"] = date('ymd'); 
                        $dataRest["date_BCV"] = date('ymd');
                        $dataRest["userEmail"] = desencriptar($usuario['email_principal']);
                        $dataRest["userPhone"] =desencriptar($usuario['celular']);
                        $dataRest["userName"] = $nombre_beneficiario;
                                               
                       $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeMenudeo',"POST",$dataRest, $token);


                    //Bloqueo y validacion
                   
                     $response = (array) $response;
                     $responseError=(array) $response['data'];
                     $responseError=(array) $response['data'][0];
                     $referencia=(array) $responseError['NIBMREFE'];                 
                     $responseError=(array) $responseError['error'];


                                       
                    if(!isset($responseError['ERRNUM'])){

                        
                        $error = 0;

                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                101074,
                                $cuentaDebitarA,
                                $cuentaCreditarA,
                                $this->data['MesaCambio']['total'],
                                $referencia,
                                NULL,
                                0
                            );


                            $this->data['MesaCambio']['REFERENCIA'] = $referencia[0];
                            $this->data['MesaCambio']['CONCEPTO'] = 'Divisas';
                            $this->data['MesaCambio']['TIPO'] = 'com';
                            $this->data['MesaCambio']['NRO_BENEFICIARIO'] = $this->data['MesaCambio']['cuentaDestino'];
                            $this->data['MesaCambio']['tipoCedulaRif'] = $usuario['tipo_cedula'];
                            $this->data['MesaCambio']['nroCedulaRif'] = desencriptar($usuario['cedula']);
                            $this->data['MesaCambio']['nombreBeneficiario'] = $nombre_beneficiario;
                            $this->data['MesaCambio']['ESTATUS'] = 'EJECUTADO';
                            $this->data['MesaCambio']['descripcion']  = ' - ';
                            $this->data['MesaCambio']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
                            // Genera la data para la descarda de pdf
                            $this->Session->write('usuarios.descarga.pdf', $this->data['MesaCambio']);
                            
                            $save = $this->TransaccionMesaCambio->save(
                                array(
                                    'TransaccionMesaCambio'=>array(
                                    'usuario_id'=>$usuario['id'],
                                    'tipo'=>'com',
                                    'estatus'=>'ej',
                                    'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                    'monto_divisa'=>formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']),
                                    'tasa'=> formatea_monto_toWs($this->data['MesaCambio']['tasa']),
                                    'comision'=> formatea_monto_toWs($this->data['MesaCambio']['comision']),
                                    'monto'=>formatea_monto_toWs($this->data['MesaCambio']['monto']),
                                    'total'=>formatea_monto_toWs($this->data['MesaCambio']['total']),
                                    'moneda'=>$this->data['MesaCambio']['currency_target_cod'],                                   
                                    'tipo_rif'=>$usuario['tipo_rif'],
                                    'rif'=>$usuario['rif'],
                                    'cuenta_origen'=>encriptar($this->data['MesaCambio']['cuentaDebitar']),
                                    'cuenta_destino'=>encriptar($this->data['MesaCambio']['cuentaDestino']),
                                    'referencia' => $referencia[0]
                                    
                                    )
                                ),
                                false
                            );
                            
                           
                            
                        
                    
                    }else{
                        
                        $error = intval($responseError['ERRNUM']);                        
                        $descripcion = $responseError['ERDS01'];


                        $this->log('Divisas Compra - '. $error . '-' . $descripcion, LOG_ERR);               
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            101074,
                            $cuentaDebitarA,
                            $cuentaCreditarA,
                            $this->data['MesaCambio']['monto'],
                            NULL,
                            $descripcion,       
                            1
                        );
                         
                        
                    }
                                      
                    $this->set('pendiente',false);


                        }

                   
                } else if(
                    $usuario['rol_juridico_id'] == 3  || // Operador
                    $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
                ){
                    
                     // En caso de juridico, solo los operadores y operadores/aprobadores pueden
                    // generar una transferencia. En cuyo caso, se deja pendiente por aprobar

                     $montoRV=str_replace('.', '', $this->data['MesaCambio']['monto_divisa']);
                   
                    //Mesa

                    if(round($montoRV) > round($this->data['MesaCambio']['parametroMenudeo'])){
                       $trans=1;

                     
                    $error = 0; 
                    $res =$this->TransaccionPendiente->save(
                                            array(
                            'TransaccionPendiente'=>array(
                            'usuario_id'=>$usuario['id'],
                            'tipo'=>'exc',
                            'estatus'=>'pa',
                            'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                            'fecha_hora_valor'=>date('Y-m-d H:i:s'),
                            'monto'=>formatea_monto_toWs($this->data['MesaCambio']['monto']),
                            'aprobaciones_requeridas'=>1,
                            'tipo_rif'=>$usuario['tipo_rif'],
                            'rif'=>$usuario['rif'],
                            'cuenta_origen'=>encriptar($this->data['MesaCambio']['cuentaDebitar']),
                            'cuenta_destino'=>encriptar($cuentaCreditarA),
                            'descripcion'=> encriptar('Divisas Compra'),
                            'nombre_beneficiario'=>$usuario['nombre'],
                            'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                            'id_beneficiario'=>$usuario['rif'],
                            'email_beneficiario'=>$this->data['MesaCambio']['comision'].'-'.$this->data['MesaCambio']['monto_divisa'].'-'.$this->data['MesaCambio']['tasa'].'-'.$this->data['MesaCambio']['producto'].'-'.$this->data['MesaCambio']['total'].'-'.$origen[1].'-'.$motivo[1].'-'.$tasaCuenta.'-'.$this->data['MesaCambio']['parametroMenudeo'].'-'.$this->data['MesaCambio']['exchange_codMesa'].'-'.$this->data['MesaCambio']['exchange_type_codMesa'].'-'.$this->data['MesaCambio']['exchange_operation_codMesa'].'-'.$this->data['MesaCambio']['exchange_operation_codMesa'].'-'.$this->data['MesaCambio']['product_target_codMesa'].'-'.$this->data['MesaCambio']['product_source_codMesa'].'-'.$this->data['MesaCambio']['currency_target_cod'],
                            'moneda'=> $this->data['MesaCambio']['currency_target_cod'],
                            'nombre_producto'=> $this->data['MesaCambio']['nameProduct']
                            )
                         ),
                        false
                    );

                   

                }else{


                    $error = 0; 
                    $trans=1;    


                    $res =$this->TransaccionPendiente->save(
                                            array(
                            'TransaccionPendiente'=>array(
                            'usuario_id'=>$usuario['id'],
                            'tipo'=>'exc',
                            'estatus'=>'pa',
                            'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                            'fecha_hora_valor'=>date('Y-m-d H:i:s'),
                            'monto'=>formatea_monto_toWs($this->data['MesaCambio']['monto']),
                            'aprobaciones_requeridas'=>1,
                            'tipo_rif'=>$usuario['tipo_rif'],
                            'rif'=>$usuario['rif'],
                            'cuenta_origen'=>encriptar($this->data['MesaCambio']['cuentaDebitar']),
                            'cuenta_destino'=>encriptar($cuentaCreditarA),
                            'descripcion'=> encriptar('Divisas Compra'),
                            'nombre_beneficiario'=>$usuario['nombre'],
                            'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                            'id_beneficiario'=>$usuario['rif'],
                            'email_beneficiario'=>$this->data['MesaCambio']['comision'].'-'.$this->data['MesaCambio']['monto_divisa'].'-'.$this->data['MesaCambio']['tasa'].'-'.$this->data['MesaCambio']['producto'].'-'.$this->data['MesaCambio']['total']    .'-'.$origen[1].'-'.$motivo[1].'-'.$tasaCuenta.'-'.$this->data['MesaCambio']['parametroMenudeo'].'-'.$this->data['MesaCambio']['product_target_cod'].'-'.$this->data['MesaCambio']['product_source_cod'].'-'.$this->data['MesaCambio']['tasaMenudeo'],
                            'moneda'=> $this->data['MesaCambio']['currency_target_cod'],
                            'nombre_producto'=> $this->data['MesaCambio']['nameProduct']
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

                    $this->set('pendiente',true);
                }
                    
                if($referencia==""){
                    $error = -1;    
                }

                //Menudeo
          
                $this->set(compact('error', 'referencia', 'descripcion', 'pendiente', 'trans'));
                $this->Session->delete('mesaCambio.compra');
            } else {
		$this->redirect('compra');
            }
	}
	
	/**
	 * Metodo inicial para realizar venta de divisas. Busca la 
	 * informacion de las cuentas.
	 * 
	 * @param string $volver Indica si esta regresando de la confirmacion
	 */
	function venta($volver=null){	
            
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101075)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */
            
            $this->Session->delete('usuarios.descarga.pdf');
            $this->layout = 'interna';
            $this->set('seccion_actual','vender.divisas');

            $sw_cuentaDebitar = 0;
            $errorDivisa = 0;

            
            if(!empty($this->data['MesaCambio'])){
                   //validar que monto sea igual 
                $monto = str_replace('.', '', $this->data['MesaCambio']['monto_divisa']);
                $arrayMonto = explode(',', $monto);
                if($arrayMonto[0]=='0'){
                   $this->TransaccionMesaCambio->validationErrors['monto_divisa'] = 'sin_cero';
                   $errorDivisa=1;
                }


	           	$usuario = $this->Session->read('usuarios.datos');

             
		          // Para usuario jurodicos operadores, obtener su configuracion para este tipo de transaccion
		        if(
                    $usuario['rol_juridico_id'] == 3 ||
                    $usuario['rol_juridico_id'] == 5
		        ){
                    $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                    $usuario['id'],
                    101075,
                    $this->data['MesaCambio']['cuentaOrigen']
                    );
				

                    if(!$transaccion_detalle) {
                        $sw_cuentaDebitar = 1;
                        $this->TransaccionMesaCambio->validationErrors['cuentaOrigen'] = _m('usuario.no.autorizado');
                    }
		        }
                if(empty($this->TransaccionMesaCambio->validationErrors)){
                    if(isset($transaccion_detalle)){
                        $this->data['MesaCambio']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if(is_numeric($transaccion_detalle['limite'])){
                        $this->data['MesaCambio']['monto_limite_usuario']=formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['MesaCambio']['monto_limite_usuario']='';
                        }
                    }
			
                    $this->TransaccionMesaCambio->set($this->data);

                    if($this->TransaccionMesaCambio->validates(array('fieldList'=>array('cuentaOrigen', 'cuentaCreditar', 'monto')))){
			             $this->Session->write('mesaCambio.venta', $this->data);
			             $this->redirect('venta_confirmacion');
                    }
                }
            }else if(isset($volver) && $this->Session->check('mesaCambio.venta')){
                $this->data = $this->Session->read('mesaCambio.venta');
            }
            $usuario = $this->Session->read('usuarios.datos');
            $cuentas = array();
            $cta_acreditar = $this->Activo->buscarCuentasSaldos(true);
            $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();
            $tipoDocumento=$usuario['perfil_ib_id']==1 ? $usuario['tipo_cedula'] : $usuario['tipo_rif'];
            switch ( $tipoDocumento) {
                case 'V':
                   if($usuario['perfil_ib_id']==1){
                         $origenFondosId=23;
                    }else{
                         $origenFondosId=27;
                    }                          
                    break;
                case 'J':
                   $origenFondosId=27;                
                    break;
                case 'P':
                   $origenFondosId=26;                  
                    break;
                case 'G':
                   $origenFondosId=25;                 
                    break;
                case 'E':
                   $origenFondosId=24;                 
                break;
                default:
                   $origenFondosId=3;
                    break;
            }
            $origenFondos = $this->MesaCambio->consultaParametros($origenFondosId);
            $motivos= $this->MesaCambio->consultaParametros(4);
            
            foreach ($cuentasValidar as $key => $value) {
                $valores = explode('-', $value);
                $valores = array_map('trim', $valores);
                $query = $this->MesaCambio->validarProducto($valores[1],2,$valores[2],'VEB');                
                   
                if($query==true){                    
                    $cuentas[$key.'-'.$query['fee_percentage'].'-'.$query['rate'].'-'.$query['available'].'-'.$valores[2].'-'.$valores[1].'-'.$query['name_product']] = '**'.substr($key,-4) . ' - ' . $query['name_product'] . ' - ' . $valores[2] . ' - ' . $valores[3];
                }
            }
                                    
            $this->set(compact('cuentas','cta_acreditar','origenFondos','motivos','sw_cuentaDebitar','errorDivisa'));
	}

	
	/**
	 * Metodo para realizar la confirmacion de la venta de divisas.
	 */
	function venta_confirmacion(){



            $this->layout = 'interna';
            $usuario = $this->Session->read('usuarios.datos');
            $this->set('juridico',$usuario['tipo_rif']);
            $this->set('seccion_actual','vender.divisas');	

            if($this->Session->check('mesaCambio.venta')){                
                $this->data = $this->Session->read('mesaCambio.venta');                
            }else {
		$this->redirect('venta');
            }	
	}

	
	/**
	 * Metodo para realizar el proceso de Divisas venta.
	 */
	function venta_resultado(){

            $this->layout = 'interna';
                
            $this->set('seccion_actual','vender.divisas');

            if($this->Session->check('mesaCambio.venta')){
                $this->data = $this->Session->read('mesaCambio.venta');
                // Validacion anti fraude

                //Arreglar decimales de tasa divisa (hector)
                $this->data = $this->Session->read('mesaCambio.venta');
                $cuentaDebitar = $this->data['MesaCambio']['cuentaDebitar'];
                $datosCuenta = explode('-',$cuentaDebitar);
                $tasaCuenta = $datosCuenta[2];
                $tasaCuenta = str_replace(",", ".",$tasaCuenta);                           
                $cuentas = $this->Activo->buscarCuentasME();	
                if(
                    !in_array($this->data['MesaCambio']['cuentaOrigen'], array_keys($cuentas))
                ){
                    $this->redirect('/consultas/integral');
                }

                //  Fin validacion anti fraude
                $usuario = $this->Session->read('usuarios.datos');                
                $moneda = $this->data['MesaCambio']['moneda'];
                $cuentaDebitar = str_pad(substr($this->data['MesaCambio']['cuentaOrigen'] ,10), 20, "0", STR_PAD_LEFT);
                $cuentaCreditar = str_pad(substr($this->data['MesaCambio']['cuentaCreditar'] ,10), 20, "0", STR_PAD_LEFT);
                $cuentaDebitarA = $this->data['MesaCambio']['cuentaOrigen'];
                $cuentaCreditarA = $this->data['MesaCambio']['cuentaCreditar'];
                $referencia = "";
                $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
                $origen = explode('-',$this->data['MesaCambio']['origenFondos']);
                $motivo = explode('-',$this->data['MesaCambio']['motivos']);
                $descripcion = "";
                $limite_cambio =$this->Parametro->findByCodigo('ms_menudeo');
                 $usuario = $this->Session->read('usuarios.datos');
                  if($usuario['perfil_ib_id']==1){
                    $tipoIdentificacion=$usuario['tipo_cedula'];
                    $identificacion=desencriptar($usuario['cedula']);
                    $tipo='NAT';
                     }else{
                    $tipoIdentificacion=$usuario['tipo_rif'];
                    $identificacion=desencriptar($usuario['rif']);
                    $tipo='JUR';
                }
                

                // Identificar tipo de usuario (natural o juridico)			
                if(
                    empty($usuario['tipo_rif']) ||
                    $usuario['rol_juridico_id'] == 2 // Master Unico
                ){
                                        
                   $montoRV=str_replace('.', '', $this->data['MesaCambio']['monto_divisa']);
                   
                    //Mesa
                    if(round($montoRV) > round($this->data['MesaCambio']['parametroMenudeo'])){
                       $trans=1;
                          //Servicio de token
                     $dataToken = array();
                        $dataToken["username"] = Configure::read('Api.user');
                        $dataToken["password"] = Configure::read('Api.password');                        
                        $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                        $tokenApp = json_decode(json_encode($tokenApp), true);                        
                        $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];                         
                       
                        $dataRest = array();

                        $dataRest["exchange_cod"] = $this->data['MesaCambio']['exchange_codMesa'];
                        $dataRest["exchange_type_cod"] = $this->data['MesaCambio']['exchange_type_codMesa'];
                        $dataRest["exchange_operation_cod"] = $this->data['MesaCambio']['exchange_operation_codMesa'];
                        $dataRest["currency_cod"] = $this->data['MesaCambio']['currency_source_cod'];
                        $dataRest["ip"] =  Configure::read('ip.client');
                        $dataRest["canal"] = 4;
                        $dataRest["tipo"] = '';
                        $dataRest["customer_type_cod"] = $tipo;
                        $dataRest["amount_order"] =formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                        $dataRest["product_target"] =$this->data['MesaCambio']['product_target_codMesa'];
                        $dataRest["product_source"] =$this->data['MesaCambio']['product_source_codMesa'];
                        
                        $dataRest["operation_type"] = 'VB';
                        $dataRest["process_type"] = '02';
                        $dataRest["account_source"] = $this->data['MesaCambio']['cuentaOrigen'];
                        $dataRest["account_target"] = $this->data['MesaCambio']['cuentaDestino'];
                        $dataRest["identification_type"] =$tipoIdentificacion ;
                        $dataRest["identification"] = $identificacion;
                        $dataRest["account_target"] = $cuentaCreditar;
                        $dataRest["amount_source"] =formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                        $dataRest["amount_target"] = formatea_monto_toWs($this->data['MesaCambio']['monto']); 
                        $dataRest["amount_commission"] =formatea_monto_toWs($this->data['MesaCambio']['comision']);
                        $dataRest["rate_client"] =formatea_monto_toWs($this->data['MesaCambio']['tasa']);
                        $dataRest["funds_source"] =$origen[1];
                        $dataRest["funds_destination"] =$motivo[1];

                                                                          
                       $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeTableNew',"POST",$dataRest, $token);


                     $response = (array) $response;
                     $responseError=(array) $response['data'];
                     $responseError=(array) $response['data'][0];
                     $referencia=(array) $responseError['INVCREFE'];                 
                     $responseError=(array) $responseError['error'];

                                       
                    if(!isset($responseError['ERRNUM'])){

                        
                        $referencia=$referencia[0];
                         
                        $dataRest = array();

                        $dataRest["exchange_cod"] = $this->data['MesaCambio']['exchange_codMesa'];
                        $dataRest["exchange_type_cod"] = $this->data['MesaCambio']['exchange_type_codMesa'];
                        $dataRest["exchange_operation_cod"] = $this->data['MesaCambio']['exchange_operation_codMesa'];
                        $dataRest["currency_cod"] = $this->data['MesaCambio']['currency_source_cod'];
                        $dataRest["ip"] =  Configure::read('ip.client');
                        $dataRest["tipo"] = '';
                        $dataRest["canal"] = 4;
                        $dataRest["customer_type_cod"] = $tipo;
                        $dataRest["amount_order"] =formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                        $dataRest["product_target"] =$this->data['MesaCambio']['product_target_cod'];
                        $dataRest["product_source"] =$this->data['MesaCambio']['product_source_cod'];
                        $dataRest["process_type"] = '02';
                        $dataRest["account_source"] = $this->data['MesaCambio']['cuentaOrigen'];
                        $dataRest["account_target"] = $this->data['MesaCambio']['cuentaDestino'];
                        $dataRest["identification_type"] =$tipoIdentificacion;
                        $dataRest["identification"] = $identificacion;
                        $dataRest["account_target"] = $cuentaCreditar;
                        $dataRest["amount_source"] =formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                        $dataRest["amount_target"] = formatea_monto_toWs($this->data['MesaCambio']['monto']); 
                        $dataRest["amount_commission"] =formatea_monto_toWs($this->data['MesaCambio']['comision']);
                        $dataRest["rate_client"] =formatea_monto_toWs($this->data['MesaCambio']['tasa']);

                        $dataRest["reference_number"] =$referencia;
                        $dataRest["operation_type"] = 'PC';
                        $dataRest["funds_source"] =$origen[1];
                        $dataRest["funds_destination"] =$motivo[1];

                                                
                       $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeTableNew',"POST",$dataRest, $token);
                        $error = "-1";
                        $descripcion = "Error inesperado, transaccion no disponible.";

                         $response = (array) $response;
                         $responseError=(array) $response['data'];
                         $responseError=(array) $response['data'][0];
                         $referencia=(array) $responseError['INVCREFE'];
                         $montoAdjudicado=(array) $responseError['INVCMTOO'];
                         $montoTotal=(array) $responseError['INVCMTOD'];
                         $montoComision=(array) $responseError['INVCMTOC'];
                         $this->data['MesaCambio']['comision']= $montoComision[0];
                         $total=$montoTotal[0]-$montoComision[0];
                         $responseError=(array) $responseError['error'];
                         $referencia=$referencia[0];

                        if(isset($responseError['ERRNUM'])){

                            $error = intval($responseError['ERRNUM']);                        
                            $descripcion = $responseError['ERDS01'];

                            $this->log('Divisas Venta - '. $error . '-' . $descripcion, LOG_ERR);				
                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                101075,
                                $cuentaDebitarA,
                                $cuentaCreditarA,
                                $this->data['MesaCambio']['monto'],
                                NULL,
                                $descripcion,		
                                1
                            );
                            
                            $queryInsert = $this->MesaCambio->save($this->data['MesaCambio']['producto'],2,$this->data['MesaCambio']['cuentaOrigen'],$this->data['MesaCambio']['cuentaCreditar'],$total,formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']),formatea_monto_toWs($this->data['MesaCambio']['tasa']),formatea_monto_toWs($this->data['MesaCambio']['comision']),1,'RCZ',formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']),formatea_monto_toWs($this->data['MesaCambio']['total']),$origen[1],$motivo[1],$referencia,substr($this->data['MesaCambio']['cuentaOrigen'] ,10),$usuario['id']);
                            
                        }else{

                            $error = 0;

                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                101075,
                                $cuentaDebitarA,
                                $cuentaCreditarA,
                                $this->data['MesaCambio']['total'],
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

                            $this->data['MesaCambio']['REFERENCIA'] = $referencia;
                            $this->data['MesaCambio']['CONCEPTO'] = 'Divisas';
                            $this->data['MesaCambio']['TIPO'] = 'ven';
                            $this->data['MesaCambio']['NRO_BENEFICIARIO'] = $this->data['MesaCambio']['cuentaCreditar'];
                            $this->data['MesaCambio']['tipoCedulaRif'] = $usuario['tipo_cedula'];
                            $this->data['MesaCambio']['nroCedulaRif'] = desencriptar($usuario['cedula']);
                            $this->data['MesaCambio']['nombreBeneficiario'] = $nombre_beneficiario;
                            $this->data['MesaCambio']['ESTATUS'] = 'EJECUTADO';
                            $this->data['MesaCambio']['descripcion']  = ' - ';
                            $this->data['MesaCambio']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
                            $this->data['MesaCambio']['monto_divisaAdjudicado'] = $montoAdjudicado[0];
                            $this->data['MesaCambio']['total'] = $total;
                            // Genera la data para la descarda de pdf
                            $this->Session->write('usuarios.descarga.pdf', $this->data['MesaCambio']);
                            
                            $save = $this->TransaccionMesaCambio->save(
                                array(
                                    'TransaccionMesaCambio'=>array(
                                    'usuario_id'=>$usuario['id'],
                                    'tipo'=>'ven',
                                    'estatus'=>'ej',
                                    'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                    'monto_divisa'=>formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']),
                                    'tasa'=> formatea_monto_toWs($this->data['MesaCambio']['tasa']),
                                    'comision'=> formatea_monto_toWs($this->data['MesaCambio']['comision']),
                                    'monto'=>formatea_monto_toWs($this->data['MesaCambio']['monto']),
                                    'total'=>formatea_monto_toWs($this->data['MesaCambio']['total']),
                                    'moneda'=>$this->data['MesaCambio']['currency_source_cod'],                                   
                                    'tipo_rif'=>$usuario['tipo_rif'],
                                    'rif'=>$usuario['rif'],
                                    'cuenta_origen'=>encriptar($this->data['MesaCambio']['cuentaOrigen']),
                                    'cuenta_destino'=>encriptar($this->data['MesaCambio']['cuentaCreditar']),
                                    'referencia' => $referencia,
                                    'monto_adjudicado' =>$montoAdjudicado[0]

                                    )
                                ),
                                false
                            );
                            
                            
                        } 
                    
                    }else{
                        
                          $error = intval($responseError['ERRNUM']);                        
                        $descripcion = $responseError['ERDS01'];

                        $this->log('Divisas Venta - '. $error . '-' . $descripcion, LOG_ERR);				
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            101075,
                            $cuentaDebitarA,
                            $cuentaCreditarA,
                            $this->data['MesaCambio']['monto'],
                            NULL,
                            $descripcion,		
                            1
                        );
                          
                        
                    }
                                      
                    $this->set('pendiente',false);


                     }else{

                        $trans=2;

                        //Servicio de token
                      $dataToken = array();
                        $dataToken["username"] = Configure::read('Api.user');
                        $dataToken["password"] = Configure::read('Api.password');                        
                        $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                        $tokenApp = json_decode(json_encode($tokenApp), true);                        
                        $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];       

                        if ($tipoIdentificacion == 'V') {
                            $identificacion = substr($identificacion,0,8);
                        }else{
                            $identificacion;
                        }

                       $dataRest = array();

                        $dataRest["product_target"] =$this->data['MesaCambio']['product_target_cod'];
                        $dataRest["product_source"] =$this->data['MesaCambio']['product_source_cod'];
                        $dataRest["canal"] = 4;
                        $dataRest["operation_type"] = "VB";
                        $dataRest["process_type"] = '02';
                        $dataRest["account_source"] = $this->data['MesaCambio']['cuentaOrigen'];;
                        $dataRest["account_target"] = $this->data['MesaCambio']['cuentaDestino'];;
                        $dataRest["account_target"] = $cuentaCreditar;
                        $dataRest["amount_source"] =formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']);
                        $dataRest["amount_target"] = formatea_monto_toWs($this->data['MesaCambio']['monto']);
                        $dataRest["amount_commission"] =formatea_monto_toWs($this->data['MesaCambio']['comision']);
                        $dataRest["rate_client"] =$this->data['MesaCambio']['tasaMenudeo'];
                        $dataRest["funds_source"] =$origen[1];
                        $dataRest["funds_destination"] =$motivo[1];
                        $dataRest["identification_type"] =$tipoIdentificacion;
                        $dataRest["identification"] = $identificacion;
                        $dataRest["intervention_date_ini"] = date('ymd');
                        $dataRest["intervention_date_fin"] = date('ymd');  
                        $dataRest["intervention_date_val"] = date('ymd'); 
                        $dataRest["date_BCV"] = date('ymd'); 
                        $dataRest["userEmail"] = desencriptar($usuario['email_principal']);
                        $dataRest["userPhone"] =desencriptar($usuario['celular']);
                        $dataRest["userName"] = desencriptar($usuario['nombre']);  
                        
                   
                       
                             
                     $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeMenudeo',"POST",$dataRest, $token);
                           //Bloqueo y validacion
   
                   
                     $response = (array) $response;
                     $responseError=(array) $response['data'];
                     $responseError=(array) $response['data'][0];
                     $referencia=(array) $responseError['NIBMREFE'];                 
                     $responseError=(array) $responseError['error'];


                                       
                    if(!isset($responseError['ERRNUM'])){

                      

                        $error = 0;

                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            101075,
                            $cuentaDebitarA,
                            $cuentaCreditarA,
                            $this->data['MesaCambio']['monto'],
                            NULL,
                            $descripcion,       
                            1
                        );

                          $datos_basicos = $this->Session->read('usuarios.nibpf3010');            
                            if($usuario['tipo_rif']){
                                $nombre_beneficiario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                            } else {
                                $nombre_beneficiario = desencriptar($usuario['nombre']);
                            }

                            $this->data['MesaCambio']['REFERENCIA'] = $referencia[0];
                            $this->data['MesaCambio']['CONCEPTO'] = 'MENUDEO';
                            $this->data['MesaCambio']['TIPO'] = 'com';
                            $this->data['MesaCambio']['NRO_BENEFICIARIO'] = $this->data['MesaCambio']['cuentaDestino'];
                            $this->data['MesaCambio']['tipoCedulaRif'] = $usuario['tipo_cedula'];
                            $this->data['MesaCambio']['nroCedulaRif'] = desencriptar($usuario['cedula']);
                            $this->data['MesaCambio']['nombreBeneficiario'] = $nombre_beneficiario;
                            $this->data['MesaCambio']['ESTATUS'] = 'EJECUTADO';
                            $this->data['MesaCambio']['descripcion']  = ' - ';
                            $this->data['MesaCambio']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';

                            // Genera la data para la descarda de pdf
                            $this->Session->write('usuarios.descarga.pdf', $this->data['MesaCambio']);

                             $save = $this->TransaccionMesaCambio->save(
                                array(
                                    'TransaccionMesaCambio'=>array(
                                    'usuario_id'=>$usuario['id'],
                                    'tipo'=>'ven',
                                    'estatus'=>'ej',
                                    'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                    'monto_divisa'=>formatea_monto_toWs($this->data['MesaCambio']['monto_divisa']),
                                    'tasa'=> formatea_monto_toWs($this->data['MesaCambio']['tasa']),
                                    'comision'=> formatea_monto_toWs($this->data['MesaCambio']['comision']),
                                    'monto'=>formatea_monto_toWs($this->data['MesaCambio']['monto']),
                                    'total'=>formatea_monto_toWs($this->data['MesaCambio']['total']),
                                    'moneda'=>$this->data['MesaCambio']['currency_source_cod'],                                   
                                    'tipo_rif'=>$usuario['tipo_rif'],
                                    'rif'=>$usuario['rif'],
                                    'cuenta_origen'=>encriptar($this->data['MesaCambio']['cuentaOrigen']),
                                    'cuenta_destino'=>encriptar($this->data['MesaCambio']['cuentaCreditar']),
                                    'referencia' =>$referencia[0]                                
                                    )
                                ),
                                false
                            );
                              
                    }else{
                        
                        $error = intval($responseError['ERRNUM']);                        
                        $descripcion = $responseError['ERDS01'];

                        $this->log('Divisas venta - '. $error . '-' . $descripcion, LOG_ERR);               
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            101074,
                            $cuentaDebitarA,
                            $cuentaCreditarA,
                            $this->data['MesaCambio']['monto'],
                            NULL,
                            $descripcion,       
                            1
                        );
                         
                        
                    }

                     $this->set('pendiente',false);


                        }


                } else if(
                    $usuario['rol_juridico_id'] == 3  || // Operador
                    $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
                ){
                    
                    // En caso de juridico, solo los operadores y operadores/aprobadores pueden
                    // generar una transferencia. En cuyo caso, se deja pendiente por aprobar

                     $montoRV=str_replace('.', '', $this->data['MesaCambio']['monto_divisa']);
                   
                    //Mesa
                    if(round($montoRV) > round($this->data['MesaCambio']['parametroMenudeo'])){
                       $trans=1;

                    $error = 0;	

                    $res = $this->TransaccionPendiente->save(
                        array(
                            'TransaccionPendiente'=>array(
                            'usuario_id'=>$usuario['id'],
                            'tipo'=>'exv',
                            'estatus'=>'pa',
                            'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                            'fecha_hora_valor'=>date('Y-m-d H:i:s'),
                            'monto'=>formatea_monto_toWs($this->data['MesaCambio']['monto']),
                            'aprobaciones_requeridas'=>1,
                            'tipo_rif'=>$usuario['tipo_rif'],
                            'rif'=>$usuario['rif'],
                            'cuenta_origen'=>encriptar($this->data['MesaCambio']['cuentaOrigen']),
                            'cuenta_destino'=>encriptar($cuentaCreditarA),
                            'descripcion'=> encriptar('Divisas Venta'),
                            'nombre_beneficiario'=>$usuario['nombre'],
                            'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                            'id_beneficiario'=>$usuario['rif'],
                             'email_beneficiario'=>$this->data['MesaCambio']['comision'].'-'.$this->data['MesaCambio']['monto_divisa'].'-'.$this->data['MesaCambio']['tasa'].'-'.$this->data['MesaCambio']['producto'].'-'.$this->data['MesaCambio']['total'].'-'.$origen[1].'-'.$motivo[1].'-'.$tasaCuenta.'-'.$this->data['MesaCambio']['parametroMenudeo'].'-'.$this->data['MesaCambio']['exchange_codMesa'].'-'.$this->data['MesaCambio']['exchange_type_codMesa'].'-'.$this->data['MesaCambio']['exchange_operation_codMesa'].'-'.$this->data['MesaCambio']['exchange_operation_codMesa'].'-'.$this->data['MesaCambio']['product_target_codMesa'].'-'.$this->data['MesaCambio']['product_source_codMesa'].'-'. $this->data['MesaCambio']['currency_source_cod'],
                            'moneda'=> $this->data['MesaCambio']['currency_source_cod'],
                            'nombre_producto'=> $this->data['MesaCambio']['nameProduct']
                            )
                        ),
                        false
                    );

                }else{

                    $error = 0; 
                    $trans=1;



                    $res = $this->TransaccionPendiente->save(
                        array(
                            'TransaccionPendiente'=>array(
                            'usuario_id'=>$usuario['id'],
                            'tipo'=>'exv',
                            'estatus'=>'pa',
                            'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                            'fecha_hora_valor'=>date('Y-m-d H:i:s'),
                            'monto'=>formatea_monto_toWs($this->data['MesaCambio']['monto']),
                            'aprobaciones_requeridas'=>1,
                            'tipo_rif'=>$usuario['tipo_rif'],
                            'rif'=>$usuario['rif'],
                            'cuenta_origen'=>encriptar($this->data['MesaCambio']['cuentaOrigen']),
                            'cuenta_destino'=>encriptar($cuentaCreditarA),
                            'descripcion'=> encriptar('Divisas Venta'),
                            'nombre_beneficiario'=>$usuario['nombre'],
                            'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                            'id_beneficiario'=>$usuario['rif'],
                            'email_beneficiario'=>$this->data['MesaCambio']['comision'].'-'.$this->data['MesaCambio']['monto_divisa'].'-'.$this->data['MesaCambio']['tasa'].'-'.$this->data['MesaCambio']['producto'].'-'.$this->data['MesaCambio']['total']    .'-'.$origen[1].'-'.$motivo[1].'-'.$tasaCuenta.'-'.$this->data['MesaCambio']['parametroMenudeo'].'-'.$this->data['MesaCambio']['product_target_cod'].'-'.$this->data['MesaCambio']['product_source_cod'].'-'.$this->data['MesaCambio']['tasaMenudeo'].'-'. $this->data['MesaCambio']['nameProduct'],
                            'moneda'=> $this->data['MesaCambio']['currency_source_cod'],
                            'nombre_producto'=> $this->data['MesaCambio']['nameProduct']
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

                        $this->set('pendiente',true);
                }
                
                if($referencia==""){
                    $error = -1;    
                }
                $this->set(compact('error', 'referencia', 'descripcion', 'trans'));
                $this->Session->delete('mesaCambio.compra');
            } else {
		$this->redirect('compra');
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
	
        /**
	* Acción del Listado de operaciones de mesa cambio  
	*/
	function listado(){
            
            $usuario= $this->Session->read('usuarios.datos');

            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101076)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */            

            // registra los datos del usuario en bitacora
            $this->Bitacora->registraBitacora($usuario['id'], 101076);
                    
            $this->layout = 'interna';
            $this->set('seccion_actual','consulta.operaciones');                      
            
            if(empty($usuario['tipo_rif'])){
                $total_registros = $this->TransaccionMesaCambio->find('count', array(                    								
                    'conditions'=>array(
                        'TransaccionMesaCambio.usuario_id' => $usuario['id'],
                        'TransaccionMesaCambio.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                    ),
                    'order'=>'TransaccionMesaCambio.fecha_hora_registro desc',                    
                    'limit'=>Configure::read('Tamano.paginacion')
                    )
                );
            }else{
                $total_registros = $this->TransaccionMesaCambio->find('count', array(                    								
                    'conditions'=>array(
                        'TransaccionMesaCambio.tipo_rif' => $usuario['tipo_rif'],
                        'TransaccionMesaCambio.rif' => $usuario['rif'],
                        'TransaccionMesaCambio.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                    ),
                    'order'=>'TransaccionMesaCambio.fecha_hora_registro desc',                    
                    'limit'=>Configure::read('Tamano.paginacion')
                    )
                );
            }                                           

            $offset = 0;

            if(
                isset($this->data['MesaCambio']['offset']) &&
                preg_match('/^\d+$/',$this->data['MesaCambio']['offset'])
            ){
                $offset = intval($this->data['MesaCambio']['offset']);			
            }

            $listado = array();

            if($total_registros){
                
                if(empty($usuario['tipo_rif'])){
                    $listado = $this->TransaccionMesaCambio->find(
			'all',
			array(								
                            'conditions'=>array(
                                'TransaccionMesaCambio.usuario_id' => $usuario['id'],
                                'TransaccionMesaCambio.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                            ),
                            'order'=>'TransaccionMesaCambio.fecha_hora_registro desc',
                            'offset'=>$offset,
                            'limit'=>Configure::read('Tamano.paginacion')
			)
                    );
                }else{
                    $listado = $this->TransaccionMesaCambio->find(
			'all',
			array(								
                            'conditions'=>array(
                                'TransaccionMesaCambio.tipo_rif' => $usuario['tipo_rif'],
                                'TransaccionMesaCambio.rif' => $usuario['rif'],
                                'TransaccionMesaCambio.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                            ),
                            'order'=>'TransaccionMesaCambio.fecha_hora_registro desc',
                            'offset'=>$offset,
                            'limit'=>Configure::read('Tamano.paginacion')
			)
                    );
                }                
            }          

            $this->set(compact('listado', 'total_registros'));
	}
        
        /**
         * Accion que genera el contrato de Condiciones Divisas Compra
         *
         */
        function condiciones_mesa_cambio_compra()
        {            
            
            $usuario = $this->Session->read('usuarios.datos');
            $this->data = $this->Session->check('mesaCambio.compra');

            if (!isset($this->data)) {
                $this->redirect('compra_confirmacion');
            }   
            
            $contrato_data['nombre'] = desencriptar($usuario['nombre']);
            
            $this->set('contrato_data', $contrato_data);
               
        }
        
        
        /**
         * Accion que genera el contrato de Condiciones Divisas Compra Juridico
         *
         */
        function condiciones_mesa_cambio_compra_j()
        {                        
            $usuario = $this->Session->read('usuarios.datos');
            $this->data = $this->Session->check('mesaCambio.compra');

            if (!isset($this->data)) {
                $this->redirect('compra_confirmacion');
            }                                                   
               
        }
        
        /**
         * Accion que genera el contrato de Condiciones Divisas Venta
         *
         */
        function condiciones_mesa_cambio_venta()
        {            
            
            $usuario = $this->Session->read('usuarios.datos');
            $this->data = $this->Session->check('mesaCambio.venta');

            if (!isset($this->data)) {
                $this->redirect('venta_confirmacion');
            }   
            
            $contrato_data['nombre'] = desencriptar($usuario['nombre']);
            
            $this->set('contrato_data', $contrato_data);
               
        }
        
        
        /**
         * Accion que genera el contrato de Condiciones Divisas Venta Juridico
         *
         */
        function condiciones_mesa_cambio_venta_j()
        {                        
            $usuario = $this->Session->read('usuarios.datos');
            $this->data = $this->Session->check('mesaCambio.venta');

            if (!isset($this->data)) {
                $this->redirect('venta_confirmacion');
            }                                                   
               
        }

        public function obtener_tasas($exchange_operation_cod, $currency_cod, $product_cod, $customer_type_cod){
                          
//Servicio de token
                       $dataToken = array();
                        $dataToken["username"] = Configure::read('Api.user');
                        $dataToken["password"] = Configure::read('Api.password');                        
                        $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                        $tokenApp = json_decode(json_encode($tokenApp), true);
                        $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];
                      
                        $dataRest = array();

                        $dataRest["exchange_operation_cod"] = $exchange_operation_cod;
                        $dataRest["currency_cod"] = $currency_cod;
                        $dataRest["product_cod"] = $product_cod;
                           $usuario = $this->Session->read('usuarios.datos');
                             if($usuario['perfil_ib_id']==1){

                                $dataRest["customer_type_cod"]="NAT";
                             }else{
                                $dataRest["customer_type_cod"]="JUR";
                            }                                                                        
                        $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/getParamsExchange',"POST",$dataRest,$token);
                        $respuesta=(array) $response;
                        $respuesta=$respuesta['data'];
                        $respuesta= (array) $respuesta;
                        $this->set('response',   $respuesta);

                        

                    }
          
      
}

?>