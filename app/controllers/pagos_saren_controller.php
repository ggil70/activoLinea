<?php
/**
 * Controlador para el Pago de Saren
 */
 
class PagosSarenController extends AppController {

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
        'PrefijosTelefonicos',
        'PagoSaren'                       
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
         *Acción inicial para el pago de servicos
         * @param String $volver Variable que indica que viene de la confirmación
         */
	function pago($volver=null){
            
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(102098)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */

            $usuario = $this->Session->read('usuarios.datos');                      
            $this->layout = 'interna';
            $this->set('seccion_actual','pagos.saren');
            
            if(!empty($this->data['PagoSaren'])){
                $this->Session->write('pago.saren',$this->data['PagoSaren']);
                $nro_planilla = $this->Session->read('pago.saren.nro_planilla');
                $ctaDebitar = $this->Session->read('pago.saren.cuentaDebitar');
                $this->redirect('/pagos_saren/confirmar_pago');
            }

            $cuentas = $this->Activo->buscarCuentasSaldos();
            $this->set(compact('cuentas','error','monto'));
		
	}

	
	 /**
         * Acción para realizar Pago Servicios Postpago con Clientes Afiliados
         */
	function confirmar_pago(){

        /* Para validar el acceso al modulo */
        if(!validarTransaccion(102098)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */ 

        $usuario = $this->Session->read('usuarios.datos'); 
        $this->layout = 'interna';

        if ($this->Session->check('pago.saren')){
            $datos = $this->Session->read('pago.saren');
            $dataRest = array();

            //configuración llamada api saren
            $dataRest['nro_planilla'] = $datos['nro_planilla'];
            $token = Configure::read('Saren.token');


            
            $response = $this->Activo->invocaSaren(Configure::read('Saren.url').'/buscarplanilla',"POST",$dataRest,$token);

           




            if($response!='error'){
                $resp_all = get_object_vars($response);            
            } else {
                //si se genera un error de comunicación con el sarem
                $code = '1100';
                $message = _m('saren.falla.comunicacion');
                $this->registrarBitacora($usuario['id'], $message);
                $this->redirect(array('controller'=>'/pagos_saren/pago_saren_fallida',  'action' => $code));
            }

            //Validar la respuesta del sarem
            if ($resp_all['code'] == 1000) {
                $dataRest = get_object_vars($resp_all['data']);
                if($dataRest['monto']==0){
                    $code = '1100';
                    $message = _m('saren.falla.comunicacion');
                    $this->registrarBitacora($usuario['id'], $message);
                    $this->redirect(array('controller'=>'/pagos_saren/pago_saren_fallida',  'action' => $code));

                } 

                $this->Session->write('pago.saren.monto',$dataRest['monto']);
                $this->Session->write('pago.saren.acto',utf8_decode($dataRest['acto']));
                $this->Session->write('pago.saren.oficina',$dataRest['oficina']);
                $this->Session->write('pago.saren.fecha',$dataRest['fecha_emision']);
                $fecha = $dataRest['fecha_emision'];
                

                $fechaFormateada = substr($fecha,8,2) . "-" . substr($fecha,5,2) . "-" . substr($fecha,0,4);
                $datos = $this->Session->read('pago.saren');

                $this->set(compact('datos','cuentas','error','fechaFormateada'));   
            }else{
                $code = $resp_all['code'];
                $message = $resp_all['message'];
                $this->registrarBitacora($usuario['id'], $message);
            
                $this->Session->write('pago.saren.code',$resp_all['code']);
                $this->Session->write('pago.saren.mensaje',$resp_all['message']);
                $this->redirect(array('controller'=>'/pagos_saren/pago_saren_fallida',  'action' => $code));
            }
        }else{
            //La sesion esta vencida
            $this->redirect('pago'); 
        }
	}
    //fin de confirmación_pago


    function resultado_pago($volver=null){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(102098)) $this->redirect('/consultas/integral');  

        $this->layout = 'interna';
        //validar estado de la variable de session 
        if(!$this->Session->check('pago.saren')){
            //La sesion esta vencida
            $this->redirect('pago');
        }    

        $datos = $this->Session->read('pago.saren');
        $usuario = $this->Session->read('usuarios.datos');

        //Consulta basica de los clientes          
        $datos_basicos = $this->Activo->invoca(
            'NIBPF3010',
                array(
                    'req'=>array(
                        'NACIONALIDAD'=>$usuario['tipo_cedula'],
                        'NROIDENTIFICACION'=>str_pad(desencriptar($usuario['cedula']),12,'0',STR_PAD_LEFT),
                        'NROPARAM'=>'1'
                    )
                )
        ); 


        ///validar codigo otp
        $validacion_otp = $this->Session->read('seguridad.validar_otp');
        if (
            (
                $validacion_otp == null ||
                !isset($validacion_otp['validado']) ||
                !$validacion_otp['validado']
            )
           ) 
        {
                $url_volver = '/pagos_saren/pago';
                $pasos = array(
                    'Validar Informaci&oacute;n Banco',
                    'Confirmaci&oacute;n',
                    'C&oacute;digo de Autorizaci&oacute;n',
                    'Resultado de Operaci&oacute;n'
                );
                
                $this->Session->write(
                    'seguridad.validar_otp', 
                    array(
                        'url_aceptar' => '/pagos_saren/resultado_pago',
                        'url_volver' => $url_volver,
                        'layout' => 'interna',
                        'titulo' => 'Pagos Saren',
                        'pasos' => $pasos,
                        'paso_actual' => 2,
                        'seccion_actual' => 'pagos.saren',
                    )
                );
                $this->redirect('/seguridad/genera_otp');
        }

        
        //fin validar código OTP.

        //Realizar pago en as400
        //Solicitud comisión al As400  
        $cuentaD = substr($datos['cuentaDebitar'],10,10);  

        $nombreB = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE']);
        $nombreB = substr($nombreB,0,15);
        $nombreB = $this->formatearCadena($nombreB);

        $apellidoB = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO']);
        $apellidoB = substr($apellidoB,0,15);
        $apellidoB = $this->formatearCadena($apellidoB);

        $acto = substr($datos['acto'],0,60);
        $acto = $this->formatearCadena($acto);

        $nroPlanilla = substr($datos['nro_planilla'],0,11);

        $tipoCedula = substr($usuario['tipo_cedula'],0,1);
        $cedula = desencriptar($usuario['cedula']);
        $cedula = substr($cedula,0,9);

        $oficina = substr($datos['oficina'],0,60);
        $oficina = $this->formatearCadena($oficina);

        $fechaP = trim($datos['fecha']);
        $fechaP =  substr($fechaP,0,12);


        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']; 

        $loginUsuario = desencriptar($usuario['login']);
        $loginUsuario = substr($loginUsuario,0,12);
        $loginUsuario = $this->formatearCadena($loginUsuario);
        $loginUsuario = strtoupper($loginUsuario);


        $resPagoSaren = $this->Activo->invoca(
            'IN800101',
                array(
                    'req'=>array(
                        'IN8001ACC'=>$cuentaD,
                        'IN8001CCY'=>'VEB',
                        'IN8001PUB'=>$nroPlanilla,
                        'IN8001MTO'=>$datos['monto'],
                        'IN8001ACT'=>$acto,
                        'IN8001NAC'=>$tipoCedula,
                        'IN8001CED'=>$cedula,
                        'IN8001NOM'=>$nombreB,
                        'IN8001APE'=>$apellidoB,
                        'IN8001OFI'=>$oficina,
                        'IN8001FEM'=>$fechaP,
                        'IN8001CAN'=>'04',
                        'IN8001IRV'=>'N',
                        'IN8001DIP'=>$ip,
                        'IN8001USR'=>$loginUsuario,
                )   
            )    
        );


        if($resPagoSaren == false){
            //Sin comunicación con el as400
            $this->redirect('/consultas/integral');
        }

        //validar mensaje
        if($resPagoSaren['IN800101Return']['IN800101']['IN8001ERR'] != '0'){
            $code = $resPagoSaren['IN800101Return']['IN800101']['IN8001ERR'];
            if($code == '9923'){
                $message = _m('saren.saldo.insuficiente');
            }else{
                $message = _m('error.fatal') . " [" . $code . "]";
            }      
            $this->registrarBitacora($usuario['id'], $message);
            $this->redirect(array('controller'=>'/pagos_saren/pago_saren_fallida',  'action' => $code));
        }else{
            $referenciaBABU = $resPagoSaren['IN800101Return']['IN800101']['IN8001CKN'];
            $dataRest['codigo_sucursal'] = _p('saren.cod.sucursal');
            $dataRest['nro_planilla'] = $datos['nro_planilla'];
            $dataRest['nro_referencia'] =$referenciaBABU;
            $dataRest['monto'] = $datos['monto'];
            $dataRest['fecha_pago'] = date('Y-m-d H:i:s');
            $token = Configure::read('Saren.token');
            $response = $this->Activo->invocaSaren(Configure::read('Saren.url').'/pagarplanilla',"POST",$dataRest,$token);


            if($response!='error'){
                $resp_all = get_object_vars($response);            
            } else {
                //si se genera un error de comunicación con el saren
                //Reversar en as400.
                $resReversoSaren = $this->Activo->invoca(
                    'IN800101',
                        array(
                            'req'=>array(
                                'IN8001ACC'=>$cuentaD,
                                'IN8001CCY'=>'VEB',
                                'IN8001PUB'=>$nroPlanilla,
                                'IN8001MTO'=>$datos['monto'],
                                'IN8001ACT'=>$acto,
                                'IN8001NAC'=>$tipoCedula,
                                'IN8001CED'=>$cedula,
                                'IN8001NOM'=>$nombreB,
                                'IN8001APE'=>$apellidoB,
                                'IN8001OFI'=>$oficina,
                                'IN8001FEM'=>$fechaP,
                                'IN8001CAN'=>'04',
                                'IN8001IRV'=>'Y',
                                'IN8001DIP'=>$ip,
                                'IN8001USR'=>$loginUsuario,
                                'IN8001CKN'=>$referenciaBABU
                            )   
                        )    
                );                    
                $code = '1100';
                $message = _m('saren.falla.comunicacion');
                $this->registrarBitacora($usuario['id'], $message);
                $this->redirect(array('controller'=>'/pagos_saren/pago_saren_fallida',  'action' => $code));
            }    

            if ($resp_all['code'] == 1000) {
                //llamar vista de resultado
                //La operación fue realizada exitosamente

                $fecha = $datos['fecha'];

                $fechaFormateada = substr($fecha,8,2) . "-" . substr($fecha,5,2) . "-" . substr($fecha,0,4);
                $error = '';

                $fechaPago = $dataRest['fecha_pago'];
                $fechaPago = substr($fechaPago,8,2) . "-" . substr($fechaPago,5,2) . "-" . substr($fechaPago,0,4);
                        
                $referencia = $referenciaBABU;
                $this->set(compact('datos','cuentas','error','fechaFormateada','referencia','fechaPago'));
            }else{
                //Reversar en as400.
                $resReversoSaren = $this->Activo->invoca(
                    'IN800101',
                        array(
                            'req'=>array(
                                'IN8001ACC'=>$cuentaD,
                                'IN8001CCY'=>'VEB',
                                'IN8001PUB'=>$nroPlanilla,
                                'IN8001MTO'=>$datos['monto'],
                                'IN8001ACT'=>$acto,
                                'IN8001NAC'=>$tipoCedula,
                                'IN8001CED'=>$cedula,
                                'IN8001NOM'=>$nombreB,
                                'IN8001APE'=>$apellidoB,
                                'IN8001OFI'=>$oficina,
                                'IN8001FEM'=>$fechaP,
                                'IN8001CAN'=>'04',
                                'IN8001IRV'=>'Y',
                                'IN8001DIP'=>$ip,
                                'IN8001USR'=>$loginUsuario,
                                'IN8001CKN'=>$referenciaBABU
                            )   
                        )    
                );                    

                $code = $resp_all['code'];
                $message = $resp_all['message'];
                $this->registrarBitacora($usuario['id'], $message);
                $this->redirect(array('controller'=>'/pagos_saren/pago_saren_fallida',  'action' => $code));
            }
        }    
    }









//////////////////////////////////////////////////////////
//         Fin Detalle del pago                         //  
//////////////////////////////////////////////////////////    



////////////////////////////////////////////////////////
// Funciones asociadas, soportes de los procesos      //
////////////////////////////////////////////////////////   



//pantalla emisión de errores    
    function pago_saren_fallida($code){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(102098)) {
            $this->redirect('/consultas/integral');
        }
        $this->layout = 'interna';
        $this->set('usuarios',$usuario);
        $usuario = $this->Session->read('usuarios.datos');
        $error = '';
        switch ($code) {
                case '1004':
                    $error = _m('error.fatal') . ' [SAREN][1004]';
                    break;
                
                case '1001':
                    $error = _m('saren.planilla.pagada');
                    break;

                case '1002':
                    $error = _m('saren.planilla.no.existe');
                    break;

                case '1003':
                    $error = _m('saren.nro.referencia.invalida');
                    break;

                case '1006':
                    $error = _m('saren.planilla.exenta');
                    break;

                case '1007':
                    $error = _m('saren.formato.invalido');
                    break;

                case '1008':
                    $error = _m('saren.planilla.vencida');
                    break;

                case '1009':
                    $error = _m('saren.metodo.no.permitido.token');
                    break;

                case '1010':
                    $error = _m('saren.sucursal.invalida');
                    break;

                case '1100':
                    $error = _m('saren.falla.comunicacion');
                    break;
                case '0500':
                    $error = _m('error.fatal') . " [SAREN][0500]";
                    break;  

                case '1501':
                    $error = _m('error.fatal') . " [SAREN][1501]";
                    break; 

                case '9923':
                    $error = _m('saren.saldo.insuficiente');
                    break;  


                case '41':
                    $error = "Nro de cuenta no existe";
                    break;  
            }

        $this->set(compact('error'));
    }

//registrar errores en la bitacora
    function registrarBitacora($usuario, $message){            
        $this->Bitacora->registraBitacora(
            $usuario['id'],
            102098,
            null,
            null,
            null,
            null,
            $code . " - " . $message,
            0    
        );
    }



function formatearCadena($cadena) {
    $cadena = trim($cadena);
    $cadena = strtoupper($cadena);
    $cadena = str_replace('á', 'A', $cadena);
    $cadena = str_replace('é', 'E', $cadena);
    $cadena = str_replace('í', 'I', $cadena);
    $cadena = str_replace('ó', 'O', $cadena);
    $cadena = str_replace('ú', 'U', $cadena);
    $cadena = str_replace('Á', 'A', $cadena);
    $cadena = str_replace('É', 'E', $cadena);
    $cadena = str_replace('Í', 'I', $cadena);
    $cadena = str_replace('Ó', 'O', $cadena);
    $cadena = str_replace('Ú', 'U', $cadena);
    
    $tam = strlen($cadena);
    $i = 0;
    $cadenaAux = '';
    $alfabeto = "ABCDEFGHIJKLMNÑOPQRSTUVWXYZ";

    while ($i < $tam){
        $letra = substr($cadena,$i,1);
        if($letra != " "){
            $letra = strtoupper($letra);
            $res = strpos($alfabeto, $letra);
            if($res === false){
               $a = 0;
            }else{  
                $cadenaAux = $cadenaAux . $letra;
            }
        }else{
            $cadenaAux = $cadenaAux . $letra;
        }     
        $i=$i+1; 
    }  
    return $cadenaAux;
  } 




        	
 
}