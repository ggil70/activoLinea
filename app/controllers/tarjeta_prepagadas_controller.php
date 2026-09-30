 <?php
/**
 * Controlador para las operaciones de tarjeta divisa TDD.
 * 
 * @author Gheylert Gil Bco Activo
 */
/**
 * Operaciones de tarjeta divisa tdd.
 * 
 */
class TarjetaPrepagadasController extends AppController {	 
    
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
            'TransaccionMesaCambio',
            'PrefijosTelefonicos',
            'SolicitudTarjetasPrepagadas',
            'TcpAgencias',
            'TcpCantidadRecarga',
            'Afiliacion',
            'GtpTarjetaVirtual'
	);

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session', 'Email', 'MesaCambio','Credicard', 'RequestHandler');

	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */
	var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');


   /**
     * Metodo inicial para realizar solicitud de la tarjeta fisica
     * 
     * @param string $volver Indica si esta regresando de la confirmacion
     */

    function presolicitud($volver=null){   
        //valida el modulo a ejecutarse
        if(!validarTransaccion(101090)) $this->redirect('/consultas/integral');
        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagadas.solicitud');

 
        if(!empty($this->data['Presolicitud'])){


            if($this->data['Presolicitud']['tipo_tarjeta'] == 'f'){
                $this->redirect('presolicitud_fisica');        
            }else{
                //Verificar que posea cuenta Bs Activa
                $cuentasNAC = $this->Activo->buscarCuentasSaldos();
                //Validar y el cliente posee Cta BS Activa y de firma indivisdual o indistinta
                if(count($cuentasNAC)==0){
                    $swValidar = 10;
                    $ruta = 'no_presolicitud/'.$swValidar;
                    $this->redirect($ruta);
                }

                $this->redirect('presolicitud_digital');
            }
        }
        $this->Session->delete('tarjeta_prepagadas');
        $this->Session->delete('arrgResultado');

        $usuario = $this->Session->read('usuarios.datos'); 
        $tipo_documento = $usuario['tipo_cedula']; 
        $cedulaOrig =  $usuario['cedula'];



        $nro_documento = desencriptar($usuario['cedula']); 
        $cedulaCliente = str_pad($nro_documento, 12, "0", STR_PAD_LEFT);
        $datos_basicos = $this->datosBasicosUsuarios($tipo_documento, $cedulaCliente);


        $this->Session->write('tarjeta_prepagadas.datos_basicos',$datos_basicos); 

        //validar mayor de edad
        $edadCliente = $this->edadCliente($datos_basicos['NIBPF3010Return']['NIBPF3010']['FECHANAC'], 'no_presolicitud'); 

        //Verificar que posea cta en dolares activa   
        $valCtaDol = $this->valCtaDolares();

        //Validar y el cliente posee Cta BS Activa y de firma individual o indistinta
        $swValidar = 0;
        $cuentasBS = $this->Activo->buscarCuentasSaldos();
        if(count($cuentasBS)==0){
              $swValidar = 10;
              $ruta = 'no_presolicitud/'.$swValidar;
              $this->redirect($ruta);              
        }

        $tipoTarjeta = 'F';    //Tarjeta Fisica. 

  

        $swFisica = $this->poseeTarjeta($cedulaCliente, $cedulaOrig, $tipo_documento, $tipoTarjeta, $modulo, $pantallaError, $nroTransaccion);  


        $tipoTarjeta = 'D';    //Tarjeta Digital / Virtual. 
        $swVirtual = $this->poseeTarjeta($cedulaCliente, $cedulaOrig, $tipo_documento, $tipoTarjeta); 

        
        $swTarjetaFaltante = 0;

        if($swFisica == 1 && $swVirtual == 1){
               $swValidar = 4;
               $ruta = 'no_presolicitud/'.$swValidar;
               $this->redirect($ruta);             
        }else{
             
            if($swFisica == 0 && $swVirtual == 0){
                //obtener la informacion de las dos tarjetas
                //$agencias = $this->TcpAgencias->listar_agencias();
                $swTarjetaFaltante = 3;
            }else{
                if($swFisica == 0){
                    //obtener la informacion de la fisica
                    $swTarjetaFaltante = 1;
                    $agencias = $this->TcpAgencias->listar_agencias();
                }else{
                    $swTarjetaFaltante = 2;
                    //obtener la informacion de la virtual
                }
            }    
        }    

        //Verificar tipo de productos no solicitado
        $swValidar = 0;
        $this->set(compact(swTarjetaFaltante));
    }

     
    /**********************************************************
    ******** Solicitud de la tarjeta de modo fisico  **********
    ***********************************************************/ 
    function presolicitud_fisica(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101090)) $this->redirect('/consultas/integral');

        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.solicitud');

        if(!empty($this->data['PresolicitudFisica'])){
            $cuentaSelBS = $this->data['PresolicitudFisica']['cuentaBS'];
            $datos_basicos = $this->Session->read('tarjeta_prepagadas.datos_basicos');

            $this->Session->write('tarjeta_prepagadas.ctaDebito',$cuentaBS);
            // tipoTarjeta = f  (solicitud tarjeta FISICA)
            $this->Session->write('tarjeta_prepagadas.tipoTarjeta','f');

            //validar saldo del cliente.
            //Nota: la comision vendrá parametrizada del AS400 y no de canales.
            $comision = $this->Session->read('tarjeta_prepagadas.comisionBS');

            //Validar si el cliente posee saldo en la cta dolares para cubrir la
            //comision 
            $identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];

            if($comision > 0){
                $saldo = $this->saldoCuenta($identificacion, $cuentaSelBS);
                $tipoMoneda = 1;   //1 = Bolivares
            
                //Validar si posee saldo para pagar la comision 
                $this->valSaldoComision($saldo, $comision, $tipoMoneda); 
                //FIN Validar si el cliente posee saldo en la cta dolares para cubrir la comision 
            }    

            $agencia = $this->data['PresolicitudFisica']['agencia'];
            $this->Session->write('tarjeta_prepagadas.c_agencia',$agencia);
            $this->Session->write('tarjeta_prepagadas.c_cuentaBS',$cuentaSelBS);
            $this->redirect('solicitud_confirmacion');
        }

        $this->Session->delete('tarjeta_prepagadas');
        $this->Session->delete('arrgResultado');

        $usuario = $this->Session->read('usuarios.datos');
        $tipo_documento = $usuario['tipo_cedula']; 
        $cedulaOrig =  $usuario['cedula'];
        $nro_documento = desencriptar($usuario['cedula']); 
        $cedulaCliente = str_pad($nro_documento, 12, "0", STR_PAD_LEFT);
        $tipoTarjeta = 'F';    //Tarjeta Fisica.

        //Obtener Consulta basica de los clientes 
        $datos_basicos = $this->datosBasicosUsuarios($tipo_documento, $cedulaCliente);

        //validar mayor edad
        $fechaNac = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['FECHANAC']);
        $valEdadNac = $this->edadCliente($fechaNac,'no_presolicitud');         

        //validar si posee cuenta en dolares
        $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();
        $cuentaME = $this->listaCuentaME($cuentasValidar,'no_presolicitud');

        //validar si poseen tarjeta previamente solicitada  
        $modulo = "PRESOLICITUD";
        $pantallaError = "no_presolicitud";
        $nroTransaccion = "101090";
        //$RespuestaPoseeTarjeta = $this->poseeTarjeta($cedulaCliente, $cedulaOrig, $tipo_documento, $tipoTarjeta, $modulo, $pantallaError, $nroTransaccion);

        //fin de validar tarjeta

        //Validar y el cliente posee Cta BS Activa y de firma individual o indistinta
        $swValidar = 0;
        $cuentasBS = $this->Activo->buscarCuentasSaldos();
        if(count($cuentasBS)==0){
              $swValidar = 10;
              $ruta = 'no_presolicitud/'.$swValidar;
              $this->redirect($ruta);              
        }

       //Solicitud comisión al As400  
        $resComision = $this->Activo->invoca(
            'IN206001',
            array(
                'req'=>array(
                    'IN2060COD'=>'ETPP' 
                )   
            )
        );

        if($resComision == false){
            $swValidar = 2;
            $ruta = 'error_critico/'.$swValidar . '/PRESOLICITUD/101090';
            $this->redirect($ruta);            
        }

        $comisionTCP = $resComision['IN206001Return']['IN206001']['IN2060COM'];
        $tasaCambio = $resComision['IN206001Return']['IN206001']['IN2060EXR'];

        $comisionBS = $comisionTCP * $tasaCambio; 
        $comisionBS = round($comisionBS,2);
        $etiqComisionBS = $this->comisionFormato($comisionBS);
        ////FIN de comision
             
        //crear una tabla de agencias  y crear un modelo y exportarla a la variable agencias
        $agencias = $this->TcpAgencias->listar_agencias();

        $this->Session->write('tarjeta_prepagadas.comisionTCP',$comisionTCP);
        $this->Session->write('tarjeta_prepagadas.comisionBS',$comisionBS);
        $this->Session->write('tarjeta_prepagadas.etiqComisionBS',$etiqComisionBS);
        $this->Session->write('tarjeta_prepagadas.listaCuentaBS',$cuentasBS);
        $this->Session->write('tarjeta_prepagadas.agencias',$agencias);
        $this->Session->write('tarjeta_prepagadas.datos_basicos',$datos_basicos);

        $this->set(compact('usuario','datos_basicos','agencias','cuentasBS','etiqComisionBS'));
    } 
    /**************************************************************************
      Fin de presolicitud fisica 
    ***************************************************************************/ 

    /*confirmacion Tarjeta Prepagadas 
     * Metodo confirmar datos solicitud tarjeta prepagadas
     * @param string $volver Indica si esta regresando de la confirmacion
     *///
    function solicitud_confirmacion($volver=null){  
         $this->layout = 'interna';
         $this->set('seccion_actual','prepagas.solicitud');

        //Configurar datos a mostrar
        $usuario = $this->Session->read('usuarios.datos');
        $tipo_documento = $usuario['tipo_cedula']; 
        $nro_documento = desencriptar($usuario['cedula']); 
        //Tipo documento + cedula
        $v_cedula = $tipo_documento . ".-" . str_pad($nro_documento, 8, "0", STR_PAD_LEFT);

        //Obtener Datos basico del cliente
        $datosBas = $this->Session->read('tarjeta_prepagadas.datos_basicos'); 
        $this->obtenerDatosSolicitud($datosBas);

        //datos usuario
        $v_nombre = $this->Session->read('tarjeta_prepagadas.c_nombre');
        $v_celular = $this->Session->read('tarjeta_prepagadas.c_telCel');

        //Agencia donde retirara el plastico
        $c_agencia = $this->Session->read('tarjeta_prepagadas.c_agencia');
        $a_agencia =  $this->Session->read('tarjeta_prepagadas.agencias');
        $v_agencia = $a_agencia[$c_agencia];

        //Cuenta de moned Bolivares
        $c_cuentaBS = $this->Session->read('tarjeta_prepagadas.c_cuentaBS');
        $v_cuentaBS = substr($c_cuentaBS,0,4) . "****". substr($c_cuentaBS,strlen($c_cuentaBS)-4,4);
        $etiqComisionTP = $this->Session->read('tarjeta_prepagadas.etiqComisionBS');
        $this->set(compact('v_cedula','v_nombre','v_celular','v_agencia','v_cuentaBS','etiqComisionTP'));
    }

    /**
     * Metodo Resultado datos solicitud tarjeta prepagadas
     * 
     * @param string $volver Indica si esta regresando de la confirmacion
     *///
    
    function solicitud_resultado($volver=null){  
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.solicitud');
        $usuario = $this->Session->read('usuarios.datos');



        //Validar codigo otp
        $validacion_otp = $this->Session->read('seguridad.validar_otp');
        if (
                (
                $validacion_otp == null ||
                !isset($validacion_otp['validado']) ||
                !$validacion_otp['validado']
                )
        ) {
            $url_volver = '/tarjeta_prepagadas/presolicitud';
            $pasos = array(
                            'Seleccionar Tipo de Tarjeta',
                            'Validar Informaci&oacute;n Banco',
                            'Confirmaci&oacute;n',
                            'C&oacute;digo de Autorizaci&oacute;n',
                            'Resultado de Operaci&oacute;n'
                        );
            $this->Session->write(
                'seguridad.validar_otp', array(
                'url_aceptar' => '/tarjeta_prepagadas/solicitud_resultado',
                'url_volver' => $url_volver,
                'layout' => 'interna',
                'titulo' => 'Solicitud tarjeta Prepago CASH',
                'pasos' => $pasos,
                'paso_actual' => 3,
                'seccion_actual' => 'prepagadas.solicitud'
                    )
            );
            $this->redirect('/seguridad/genera_otp');
        }
        
        
        //FIN validar codigo OTP 

        if($this->Session->check('tarjeta_prepagadas.c_cuentaBS')){
            //primero descontar la comisión al cliente y esperar la información
           //positiva del cliente.
            $comisionTCP = $this->Session->read('tarjeta_prepagadas.comisionTCP');
            if($comisionTCP > 0.00){
                $cuentaBS = $this->Session->read('tarjeta_prepagadas.c_cuentaBS');
                $cuentaBS = substr($cuentaBS,10,10);
                $comisionTCP = $this->Session->read('tarjeta_prepagadas.comisionTCP');
                $comisionBS = $this->Session->read('tarjeta_prepagadas.comisionBS');
                $comisionLOG = str_replace('.',',',$comisionBS);

                //como el cliente no tiene nro tarjeta se agrega 00000
                //para guardar en el in206101
                $tarjeta = '0000000000000000';
        
                $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

               $datosBasicos = $this->Session->read('tarjeta_prepagadas.datos_basicos');
               $nroCliente = $datosBasicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'];


               $resPagoComicion = $this->Activo->invoca(
                    'IN206101',
                    array(
                        'req'=>array(
                            'IN2061CAN'=>'04',
                            'IN2061CCY'=>'USD',
                            'IN2061CDB'=>$cuentaBS,
                            'IN2061COD'=>'ETPP',
                            'IN2061COM'=>$comisionTCP,
                            'IN2061DIP'=>$ip,
                            'IN2061MRE'=>0,
                            'IN2061CKN'=>0,
                            'IN2061REF'=>0,
                            'IN2061TDC'=>$tarjeta,
                            'IN2061UPD'=>'N',
                            'IN2061XXX'=>$nroCliente,
                            'IN2061ERR' => 0,
                            'IN2061MSE' => ''
                        )   
                    )
                );   
            
                if($resPagoComicion){ 
                    if($resPagoComicion['IN206101Return']['IN206101']['IN2061ERR']==0){
                        $procesar = 0;
                    }else{
                        $swValidar = 6;
                        $ruta = 'no_presolicitud/'.$swValidar;
                        $this->redirect($ruta);                  
                    }
                }else{
                    $swValidar = 2;
                    $ruta = 'error_critico/'.$swValidar  . '/RECARGA AS400/101090';
                    $this->redirect($ruta); 
                }
            }        

            ///////////inicicio guardar solicitud
            //generar información del cliente para guaradarla en la BD AEL
            $tipoProducto = 'F';
            $this->guardarDatosSolicitud($tipoProducto, $usuario); 
            $codiEstatus = $this->Session->read('tarjeta_prepagadas.codigoEstatus');

            if($codiEstatus > 3){
                $this->SolicitudTarjetasPrepagadas->modificarEstatus($nro_documento);
            }  
            ///-------------//fin guardar solicitud

            //guardar log
            $descripcion = "Emisión de Tarjeta Prepago Cash";
            $this->logBitacora('101090',$cuentaME, $tarjeta, $comisionLOG,NULL,$descripcion,NULL);
            //fIN DE GUARDAR LOG

            $tipo_documento = $usuario['tipo_cedula']; 
            $nro_documento = desencriptar($usuario['cedula']); 
            //Tipo documento + cedula
            $v_cedula = $tipo_documento . ".-" . str_pad($nro_documento, 8, "0", STR_PAD_LEFT);

            //nombre cliente
            $datos_basicos = $this->Session->read('tarjeta_prepagadas.datos_basicos');  
            $v_nombre = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'])." " .trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO']).", " .trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'])." " .trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE']);
           
            //codigo + numero telefono celular
            $v_celular = trim($this->Session->read('tarjeta_prepagadas.c_codTelCel'))."-".$this->Session->read('tarjeta_prepagadas.c_telCel');

            //Agencia donde retirara el plastico
            $c_agencia = $this->Session->read('tarjeta_prepagadas.c_agencia');
            $a_agencia =  $this->Session->read('tarjeta_prepagadas.agencias');
            $v_agencia = $a_agencia[$c_agencia];

            $c_cuentaBS = $this->Session->read('tarjeta_prepagadas.c_cuentaBS');
            $v_cuentaBS = substr($c_cuentaBS,0,4) . "****". substr($c_cuentaBS,strlen($c_cuentaBS)-4,4);

            //$ctaDed = substr($v_cuentaME,0,12);
            //$v_cuentaME = substr($v_cuentaME,0,12);    
            $this->Session->write('respoeteTCP.v_cuentaME',$v_cuentaBS);  

            //elimina la sessión de los datos de tarjeta prepagadas. 
            $arregloResultado = array(
                'v_cedula'=>$v_cedula,
                'v_nombre'=>$v_nombre,
                'v_celular'=>$v_celular, 
                'v_agencia'=>$v_agencia,
                'v_agencia'=>$v_agencia,
                'v_cuentaBS'=>$v_cuentaBS,
            );
            $this->Session->write('arrgResultado',$arregloResultado);
            $ruta = 'solicitud_resultado_vta';
            $this->Session->delete('tarjeta_prepagadas'); 
            $this->redirect($ruta);            
        } else {
            $this->redirect('presolicitud_fisica');
        }        
    }  

    /////////////////////////////////////////////////////////////
    // solicitud Resultado Vta para mostrar resultados         //
    ///////////////////////////////////////////////////////////// 
    function solicitud_resultado_vta($arrgRecarga){
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagadas.solicitud'); 
        $arreglo = $this->Session->read('arrgResultado');
        $v_cedula = $arreglo['v_cedula'];
        $v_nombre = $arreglo['v_nombre'];
        $v_celular = $arreglo['v_celular'];
        $v_agencia = $arreglo['v_agencia'];
        $v_cuentaBS = $arreglo['v_cuentaBS'];
        $this->set(compact('v_cedula','v_nombre','v_celular','v_agencia','v_cuentaBS'));        
    }
    /////////////////////////////////////////////////////////////
    // FIN solicitud Resultado Vta para mostrar resultados     //
    ///////////////////////////////////////////////////////////// 
    
    //////////////////////////////////////////////////////////////////////////////
    // Fin solicitud tarjeta fisica                                             //
    //////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////
    // Solicitud de tarjeta Virtual                          //
    ////////////////////////////////////////////////////////////////
    
    function presolicitud_digital(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101090)) $this->redirect('/consultas/integral');

        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagadas.solicitud');

        if(!empty($this->data['PresolicitudDigital'])){

            $cuentaSelBS = $this->data['PresolicitudDigital']['cuentaBS'];
            $datos_basicos = $this->Session->read('tarjeta_prepagadas.datos_basicos');
            $this->Session->write('tarjeta_prepagadas.ctaDebito',$cuentaBS);
            
            // tipoTarjeta = d  (solicitud tarjeta VirtuAL)
            $this->Session->write('tarjeta_prepagadas.tipoTarjeta','d');

            //validar saldo del cliente.
            //Nota: la comision vendrá parametrizada del AS400 y no de canales.

            $comision = $this->Session->read('tarjeta_prepagadas.comisionBS');

            //Validar si el cliente posee saldo en la cta dolares para cubrir la
            //comision 
            $identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];

            if($comision > 0){
                $saldo = $this->saldoCuenta($identificacion, $cuentaSelBS);
                $tipoMoneda = 1;   //1 = Bolivares
                $this->valSaldoComision($saldo, $comision, $tipoMoneda); 
                //FIN Validar si el cliente posee saldo en la cta bolivares para cubrir la comision 
            }    
            $this->Session->write('tarjeta_prepagadas.c_cuentaBS',$cuentaSelBS);
            $this->redirect('solicitud_virtual_confirmacion');
        }

        $this->Session->delete('tarjeta_prepagadas');
        $this->Session->delete('respoeteTCP');

        $usuario = $this->Session->read('usuarios.datos');

        //$login = desencriptar($usuario['login']);
        $tipo_documento = $usuario['tipo_cedula']; 
        $cedulaOrig =  $usuario['cedula'];

        $nro_documento = desencriptar($usuario['cedula']); 
        $cedulaCliente = str_pad($nro_documento, 12, "0", STR_PAD_LEFT);
        $tipoTarjeta = 'D';    //Tarjeta Virtual.

        //Obtener Consulta basica de los clientes 
        $datos_basicos = $this->datosBasicosUsuarios($tipo_documento, $cedulaCliente);

        //validar mayor edad
        $fechaNac = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['FECHANAC']);
        $edadNac = $this->edadCliente($fechaNac,'no_presolicitud');         

        //validar si posee cuenta en dolares
        $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();
        $cuentaME = $this->listaCuentaME($cuentasValidar,'no_presolicitud');


        //validar si poseen tarjeta previamente solicitada  
        $modulo = "PRESOLICITUD";
        $pantallaError = "no_presolicitud";
        $nroTransaccion = "101090";
 
        //$RespuestaPoseeTarjeta = $this->poseeTarjeta($cedulaCliente, $cedulaOrig, $tipo_documento, $tipoTarjeta, $modulo, $pantallaError, $nroTransaccion); 





        //fin de validar tarjeta

        //Validar y el cliente posee Cta BS Activa y de firma individual o indistinta

        $swValidar = 0;
        $cuentasBS = $this->Activo->buscarCuentasSaldos();

        if(count($cuentasBS)==0){
              $swValidar = 10;
              $ruta = 'no_presolicitud/'.$swValidar;
              $this->redirect($ruta);              
        }

       //Solicitud comisión al As400  

        $resComision = $this->Activo->invoca(
            'IN206001',
            array(
                'req'=>array(
                    'IN2060COD'=>'ETPV' 
                )   
            )
        );

        if($resComision == false){
            $swValidar = 2;
            $ruta = 'error_critico/'.$swValidar . '/PRESOLICITUD/101090';
            $this->redirect($ruta);            
        }

        $comisionTCP = $resComision['IN206001Return']['IN206001']['IN2060COM'];
        $tasaCambio = $resComision['IN206001Return']['IN206001']['IN2060EXR'];


        $comisionBS = $comisionTCP * $tasaCambio; 
        $comisionBS = round($comisionBS,2);
        $etiqComisionBS = $this->comisionFormato($comisionBS);

        ////FIN de comision
             
        $this->Session->write('tarjeta_prepagadas.comisionTCP',$comisionTCP);
        $this->Session->write('tarjeta_prepagadas.comisionBS',$comisionBS);
        $this->Session->write('tarjeta_prepagadas.etiqComisionBS',$etiqComisionBS);
        $this->Session->write('tarjeta_prepagadas.listaCuentaBS',$cuentasBS);
        $this->Session->write('tarjeta_prepagadas.datos_basicos',$datos_basicos);

        $this->set(compact('usuario','datos_basicos','cuentasBS','etiqComisionBS','idCuentaBS'));
    }

  
    //confirmacion Tarjeta Prepagadas virtual
    function solicitud_virtual_confirmacion($volver=null){  

         $this->layout = 'interna';
         $this->set('seccion_actual','prepagadas.solicitud');

        //Configurar datos a mostrar
        $usuario = $this->Session->read('usuarios.datos');
        $tipo_documento = $usuario['tipo_cedula']; 
        $nro_documento = desencriptar($usuario['cedula']); 
        //Tipo documento + cedula
        $v_cedula = $tipo_documento . ".-" . str_pad($nro_documento, 8, "0", STR_PAD_LEFT);

        //Configurar Datos
        //nombre cliente
        $datosBas = $this->Session->read('tarjeta_prepagadas.datos_basicos'); 

        $this->obtenerDatosSolicitud($datosBas);

        $v_cuentaBS = $this->Session->read('tarjeta_prepagadas.c_cuentaBS');
        $etiqComisionBS =  $this->Session->read('tarjeta_prepagadas_virtual.etiqComisionBS');


        //datos usuario

        $v_nombre = $this->Session->read('tarjeta_prepagadas.c_nombre');
        $v_celular = $this->Session->read('tarjeta_prepagadas.c_telCel');

        $v_cuentaBS = substr($v_cuentaBS,0,4) . '****' . substr($v_cuentaBS,strlen($v_cuentaBS)-4,4);

    $this->set(compact('v_cedula','v_nombre','v_celular','v_cuentaBS','etiqComisionBS'));

    }




    function solicitud_virtual_resultado($volver=null){  
         $this->layout = 'interna';
         $this->set('seccion_actual','prepagadas.solicitud');

        $usuario = $this->Session->read('usuarios.datos');


        
       $validacion_otp = $this->Session->read('seguridad.validar_otp');
        if (
                (
                $validacion_otp == null ||
                !isset($validacion_otp['validado']) ||
                !$validacion_otp['validado']
                )
        ) {

            $url_volver = '/tarjeta_prepagadas/presolicitud';
            $pasos = array(
                            'Seleccionar Tipo de Tarjeta',
                            'Validar Informaci&oacute;n Banco',
                            'Confirmaci&oacute;n',
                            'C&oacute;digo de Autorizaci&oacute;n',
                            'Resultado de Operaci&oacute;n'
                        );
            

            $this->Session->write(
                'seguridad.validar_otp', array(
                'url_aceptar' => '/tarjeta_prepagadas/solicitud_resultado',
                'url_volver' => $url_volver,
                'layout' => 'interna',
                'titulo' => 'Solicitud tarjeta Prepago CASH',
                'pasos' => $pasos,
                'paso_actual' => 3,
                'seccion_actual' => 'prepagadas.solicitud'
                    )
            );
            $this->redirect('/seguridad/genera_otp');
        }
        
    

        if($this->Session->check('tarjeta_prepagadas.c_cuentaBS')){  

            //primero descontar la comisión al cliente y esperar la información
            //positiva del cliente.

            $cuentaBS = $this->Session->read('tarjeta_prepagadas.c_cuentaBS');
            $cuentaBS = substr($cuentaBS,10,10);
            $comisionTCP = $this->Session->read('tarjeta_prepagadas.comisionTCP');
            $comisionBS = $this->Session->read('tarjeta_prepagadas.comisionBS');
            $comisionLOG = str_replace('.',',',$comisionBS);
            $tarjeta = '0000000000000000';

            $datosBasicos = $this->Session->read('tarjeta_prepagadas.datos_basicos');
            $nroCliente = $datosBasicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'];



            if($comisionTCP > 0.00){
                 $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                 

                $resPagoComicion = $this->Activo->invoca(
                    'IN206101',
                    array(
                        'req'=>array(
                            'IN2061CAN'=>'04',
                            'IN2061CCY'=>'USD',
                            'IN2061CDB'=>$cuentaBS,
                            'IN2061COD'=>'ETPV',
                            'IN2061COM'=>$comisionTCP,
                            'IN2061DIP'=>$ip,
                            'IN2061MRE'=>0,
                            'IN2061CKN'=>0,
                            'IN2061REF'=>0,
                            'IN2061TDC'=>$tarjeta,
                            'IN2061UPD'=>'N',
                            'IN2061XXX'=>$nroCliente,
                            'IN2061ERR' => 0,
                            'IN2061MSE' => ''
                        )
                    )
                );


                if($resPagoComicion){ 
                    if($resPagoComicion['IN206101Return']['IN206101']['IN2061ERR']==0){
                        $procesar = 0;
                    }else{
                        $swValidar = 6;
                        $ruta = 'no_presolicitud/'.$swValidar;
                        $this->redirect($ruta);                  
                    }
                   
                }else{

                    $swValidar = 2;
                    $ruta = 'error_critico/'.$swValidar  . '/RECARGA AS400/101090';
                    $this->redirect($ruta); 
                }
                
            }  //fin validacion comision



///////////inicicio guardar solicitud
            //generar información del cliente para guaradarla en la BD AEL
            $tipoProducto = 'D';
            $this->guardarDatosSolicitud($tipoProducto, $usuario); 

            $codiEstatus = $this->Session->read('tarjeta_prepagadas.codigoEstatus');

            if($codiEstatus > 3){
                $this->SolicitudTarjetasPrepagadas->modificarEstatus($nro_documento);
            }  
///-------------//fin guardar solicitud

            //guardar log

            $descripcion = "Emisión de Tarjeta Prepago Virtual Cash";
            $this->logBitacora('101090',$cuentaBS, $tarjeta, $comisionLOG,NULL,$descripcion,NULL);
            //fIN DE GUARDAR LOG

            $tipo_documento = $usuario['tipo_cedula']; 
            $nro_documento = desencriptar($usuario['cedula']); 
            //Tipo documento + cedula
            $v_cedula = $tipo_documento . ".-" . str_pad($nro_documento, 8, "0", STR_PAD_LEFT);

            //nombre cliente
            $datos_basicos = $this->Session->read('tarjeta_prepagadas.datos_basicos');  

            $v_nombre = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'])." " .trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO']).", " .trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'])." " .trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE']);
           
            //codigo + numero telefono celular
            $v_celular = trim($this->Session->read('tarjeta_prepagadas.c_codTelCel'))."-".$this->Session->read('tarjeta_prepagadas.c_telCel');
          
            $v_cuentaBS = substr($v_cuentaBS,0,4) . '****' . substr($v_cuentaBS,strlen($v_cuentaBS)-4,4);

            $v_email = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];  

            $v_direccion = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['DIRECCION01'].  " " . $datos_basicos['NIBPF3010Return']['NIBPF3010']['DIRECCION02']. " " . $datos_basicos['NIBPF3010Return']['NIBPF3010']['DIRECCION03']. " " . $datos_basicos['NIBPF3010Return']['NIBPF3010']['DIRECCION04']);
      
            //elimina la sessión de los datos de tarjeta prepagadas. 


            $arregloResultado = array(
                'v_cedula'=>$v_cedula,
                'v_nombre'=>$v_nombre,
                'v_celular'=>$v_celular, 
                'v_email'=>$v_email,
                'v_direccion'=>$v_direccion
            );

            $this->Session->write('arrgResultado',$arregloResultado);
            $this->Session->write('respoeteTCP.v_cuentaME',$v_cuentaBS);

            $ruta = 'solicitud_resultado_virtual_vta';
            $this->Session->delete('tarjeta_prepagadas');  
            $this->redirect($ruta);            
            
        } else {
           $this->redirect('presolicitud');
        }        
    }  


//solicitud Resultado Vta
    function solicitud_resultado_virtual_vta(){
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagadas.solicitud');        
        $arreglo = $this->Session->read('arrgResultado');
        $v_cedula = $arreglo['v_cedula'];
        $v_nombre = $arreglo['v_nombre'];
        $v_celular = $arreglo['v_celular'];
        $v_email = $arreglo['v_email'];
        $v_direccion = $arreglo['v_direccion'];
        $this->set(compact('v_cedula','v_nombre','v_celular','v_email','v_direccion'));        
    }


///////////////////////////////////////////////////////////////////////////////////
// Fin solicitud tarjeta virtual
///////////////////////////////////////////////////////////////////////////////////    

///////////////////////////////////////////////////////////////////
//Pantalla Manejo de errores
///////////////////////////////////////////////////////////////////    
/******************************************************************
  function no_presolicitud      
  Permite desplegar la pantalla de errores ocurridos durante 
  la solicitud de las tarjetas
******************************************************************/
    function no_presolicitud($swValidar,$fecha = null){
        $this->layout = 'interna';
        $proceso = 'PRESOLICITUD';
        $servicio = 'AS400';
        $idTransaccion = 101090;
        $mensaje = "";
        switch ($swValidar) {
          case 1:
              $mensaje = _m('tcp.sin.cta.usd');
              break;
          case 3:
              //Sin saldo en dolares
              $mensaje = _m('tcp.sin.saldo');
              break; 
          case 4:         
              $mensaje = _m('tcp.tarjeta.solicitada'); 
              $servicio = 'BASE DATOS';
              break; 
          case 5:         
              $mensaje = _m('tcp.tarjeta.noSolicitada'); 
              break; 
          case 6:         
              $mensaje = _m('tcp.no.saldo.comision'); 
              break; 
          case 7:     
              $mensaje = "No se pudo ejecutar su solicitud, Intente mas tarde"; 
              break; 
          case 8:     
              $mensaje = "No se pudo ejecutar su solicitud, Intente mas tarde"; 
              break; 

          case 12:     
              $mensaje = _m('tcp.menor.edad'); 
              break; 

          case 20:     
              //Sin saldo en bolivares
              $mensaje = _m('tcp.sin.saldo.bs');
              break; 
        }         

        //LLenar Log de canales con el error presentado
        $descripcion = $mensaje; 
        $descripcionLog = $mensaje . "/" . $proceso . "/" . $servicio; 
        $this->logBitacora($idTransaccion,NULL, NULL,NULL,NULL,$descripcionLog,NULL);
        $this->set(compact('descripcion'));
    }
/***************************************************************
Fin funcion no_presolicitud
***************************************************************/

//////////////////////////////////////////////////////////////////////////////////////
// Fin solicitud tarjeta Fisica y virtual virtual
////////////////////////////////////////////////////////////////////////////////////// 


///////////////////////////////////////////////////////////////////////////////////
//Activación tarjeta
//////////////////////////////////////////////////////////////////////////////////    
    function activacion($volver=null){   

        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101091)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $usuario = $this->Session->read('usuarios.datos'); 
        if(!empty($this->data['Activacion'])){
            $codSeguridad = $this->data['Activacion']['codSeguridad'];
            $fechaVencimiento = $this->data['Activacion']['fechaVencimiento'];
            $this->Session->write('tarjeta_activacion_TP.codSeguridad',$codSeguridad);

            $this->Session->write('tarjeta_activacion_TP.fechaVencimiento',
            $this->data['Activacion']['fechaVencimiento']);
            $this->redirect('activacion_confirmacion');
        }

        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.activacion');
        $this->Session->delete('tarjeta_activacion_TP');

        $usuario = $this->Session->read('usuarios.datos'); 
        $login = desencriptar($usuario['login']);
        $tipo_documento = $usuario['tipo_cedula']; 
        $tipoCedula = $usuario['tipo_cedula']; 
        $cedulaOrig =  $usuario['cedula'];
        $nro_documento = desencriptar($usuario['cedula']); 
        $cedulaCliente = str_pad($nro_documento, 12, "0", STR_PAD_LEFT);
        
        $res = $this->Credicard->consultarCuenta($cedulaCliente); 
        $nroTarjeta = '';

        if($res == 1){
            $swValidar = 1;
            $ruta = 'error_critico/'.$swValidar  . '/ACTIVACION/WS-CONSULTACUENTA/101090';
            $this->redirect($ruta);
        }else{
            $swActivo = 0;
            $codProducto = _p('tcp_codigo_producto');
            $fechaActivo = '';

            $arreglo = $res['CuentaTarjetaClienteResponse'];
            if(isset($arreglo['codAsociado'])){
                if(trim($arreglo['codigoProducto']) == trim($codProducto)){
                    if($arreglo['codigoEstatus'] < 4){
                        if($arreglo['codigoEstatusTrj'] != '10'){
                            $swActivo = 1;
                        }else{
                            $swActivo = 2;
                            $nroTarjeta = $arreglo['tarjeta']; 
                        }                                    
                    }      
                }
            }else{
                foreach ($arreglo as $valor) {
                    $codProductoWS = $valor['codigoProducto'];
                    if(trim($codProductoWS) == trim($codProducto)){
                        if($valor['codigoEstatus'] < 4){
                            if($valor['codigoEstatusTrj'] != '10'){
                                $swActivo = 1;
                            }else{
                                $swActivo = 2;
                                $nroTarjeta = $valor['tarjeta']; 
                                break;
                            }                                    
                            
                        }    
                    } 
                }    
            }                 
        }    

        if($swActivo == 0){
            $swValidar = 1;
            $ruta = 'no_activacion/'.$swValidar;
            $this->redirect($ruta); 
        }

        if($swActivo == 1){
            $swValidar = 2;
            $ruta = 'no_activacion/'.$swValidar;
            $this->redirect($ruta); 
        }
        $this->Session->write('tarjeta_activacion_TP.cedulaCliente',$cedulaCliente);
        $this->Session->write('tarjeta_activacion_TP.nroTarjeta',$nroTarjeta);
        $this->set(compact('usuario', 'nroTarjeta','tipoCedula','cedulaCliente'));
    }



    /**
     * Metodo para realizar la confirmacion de la compra de divisas.
     */
    function activacion_confirmacion(){
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101091)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */
            $this->layout = 'interna';

            if($this->Session->check('tarjeta_activacion_TP.nroTarjeta')){
                $usuario = $this->Session->read('usuarios.datos');

                $tarjeta = $this->Session->read('tarjeta_activacion_TP.nroTarjeta');

                $fechaVencimiento = $this->Session->read('tarjeta_activacion_TP.fechaVencimiento');

                $codSeguridad = $this->Session->read('tarjeta_activacion_TP.codSeguridad');
                $this->set(compact('tarjeta','fechaVencimiento','codSeguridad')); 
            }else {
                $this->redirect('activacion');
            }   
    }


    /////////////////////////////////////////////////////////////////
    // Metodo para Activar la tarjeta y mostrar el resultado.      //
    /////////////////////////////////////////////////////////////////
    function activacion_resultado(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101091)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.activacion');
        if($this->Session->check('tarjeta_activacion_TP.nroTarjeta')){
            $usuario = $this->Session->read('usuarios.datos');
            $cedula = $this->Session->read('tarjeta_activacion_TP.cedulaCliente');

            $tarjeta = $this->Session->read('tarjeta_activacion_TP.nroTarjeta');

            $fechaVencimiento = $this->Session->read('tarjeta_activacion_TP.fechaVencimiento');
            $codSeguridad = $this->Session->read('tarjeta_activacion_TP.codSeguridad');
            $respuesta = '00';
            $res = $this->Credicard->activarTarjeta($cedula, $codSeguridad, $fechaVencimiento, $tarjeta);

            if($res == 1){
                $swValidar = 1;
                $ruta = 'error_critico/'. $swValidar . '/ACTIVACION/WS-ACTIVACION/101091';
                $this->redirect($ruta);
            }else{
                $respuesta = $res ['ActivacionTarjetaResponse']['respuesta'];
                if($respuesta == '00'){
                    $ruta = 'activacion_resultado_vta';
                    $this->redirect($ruta);
                    //$this->set(compact('tarjeta','fechaVencimiento','codSeguridad'));
                 }else{
                    $swValidar = 3;
                    $ruta = 'no_activacion/'.$swValidar.'/'.$respuesta;
                     $this->redirect($ruta); 
                }
            }    
        }else {
            $this->redirect('activacion');
        }   
    }  
    /////////////////////////////////////////////////////////////////
    // FIN Metodo para Activar la tarjeta y mostrar el resultado.  //
    /////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////
    // Activacion Resultado Vta para mostrar resultados         //
    ///////////////////////////////////////////////////////////// 
    function activacion_resultado_vta(){
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.activacion'); 
        $tarjeta = $this->Session->read('tarjeta_activacion_TP.nroTarjeta');
        $fechaVencimiento = $this->Session->read('tarjeta_activacion_TP.fechaVencimiento');
        $codSeguridad = $this->Session->read('tarjeta_activacion_TP.codSeguridad');
        $this->set(compact('tarjeta','fechaVencimiento','codSeguridad'));
        //$this->set(compact('v_cedula','v_nombre','v_celular','v_agencia','v_cuentaBS'));    
    }


    /******************************************************************
    function no_activacion      
    Permite desplegar la pantalla de errores ocurridos durante 
    la activacion de las tarjetas
    ******************************************************************/
    function no_activacion($swValidar,$respuesta=null){
           //codigo de respuesta de la Activación     
           /*00 Transacción Exitosa
            $9 Tarjeta vencida
            45 Cedula invalida
            14 Tarjeta invalida
            92 Problemas con la conexión de la caja de seguridad
            $8 Emisor no existe
            57 Tarjeta ya Activa
            80 Fecha invalida
            54 Fecha Invalida
            N7 CVC inválido 
            */

        $this->layout = 'interna';   
        $mensaje = "";
        $proceso = 'ACTIVACION';
        $idTransaccion = 101091; 

        switch ($swValidar) {
          case 1:
              $mensaje = _m('tcp.no.solicitada');
              $servicio = 'WS-CONSULTACUENTA';
              break;
          case 2:
              $mensaje = _m('tcp.activa');
              $servicio = 'WS-CONSULTACUENTA';
              break;
          case 3:
              $mensaje = _m('tcp.activa.respuesta');
              $mensRespuesta = '';
              $servicio = 'WS-ACTIVACION';
              switch ($respuesta) {
                  case '$9':
                      $mensRespuesta = '[Tarjeta vencida]';
                      break;
                  case '45':
                      $mensRespuesta = '[Cedula invalida]';
                      break;                      
                  case '14':
                      $mensRespuesta = '[Tarjeta invalida]';
                      break;  
                  case '92':
                      $mensRespuesta = '[Problemas con la conexión de la caja de seguridad]';
                      break;  
                  case '$8':
                      $mensRespuesta = '[Emisor no existe]';
                      break;
                  case '54':
                      $mensRespuesta = '[Fecha invalida]';
                      break; 
                  case '57':
                      $mensRespuesta = '[Tarjeta ya Activa]';
                      break;   
                  case '80':
                      $mensRespuesta = '[Fecha invalida]';
                      break; 
                  case 'N7':
                      $mensRespuesta = '[CVC inválido]';
                      break;  
              }
              $mensaje = $mensaje;
              break;
        }       
        $descripcion = $mensaje;  
        $descripcionLog = $mensaje . "/" . $proceso . "/" . $servicio; 
        $this->logBitacora($idTransaccion,NULL, NULL,NULL,NULL,$descripcionLog,NULL);        

        //Esta codigo esta desactivado porque no se puede crear tabla
        //en la base de datos  
        //$this->registrar_log_error($proceso, $servicio, $descripcion); 

        $this->set(compact('descripcion'));
    }

    ///////////////////////////////////////////////////////////////////////////
    //  FIN  function no_activacion                                          // 
    ///////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////////
    // Fin de Activacion                                                       // 
    /////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////////////
    // Cambio de PIn TPC                                                              //
    ////////////////////////////////////////////////////////////////////////////////////
     function cambiopin(){
         if(!validarTransaccion(101095)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */
        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.cambioPIN');    

        //Solicitud de tarjeta de coordenada
        $usuario = $this->Session->read('usuarios.datos');

        if(!empty($this->data['Cambiopin'])){
            $this->set('cambioPIN_TP',$this->data);
            $fecVencimiento = $this->data['Cambiopin']['fechaVencimiento'];
            $codSeguridad = $this->data['Cambiopin']['codSeguridad'];
            $claveNueva = $this->data['Cambiopin']['clave_nueva'];
            $this->Session->write('cambioPIN_TP.fecVencimiento',$fecVencimiento);
            $this->Session->write('cambioPIN_TP.codSeguridad',$codSeguridad);
            $this->Session->write('cambioPIN_TP.claveNueva',$claveNueva);
            $this->redirect('cambiopin_confirmacion');
        }
            
        $usuario = $this->Session->read('usuarios.datos');
        $this->Session->delete('cambioPIN_TP');
        $tipo_documento = $usuario['tipo_cedula']; 
        $cedulaOrig =  $usuario['cedula'];    
        $cedulaCliente = desencriptar($usuario['cedula']);
        $cedulaCliente = str_pad($cedulaCliente, 12, "0", STR_PAD_LEFT);

        $res = $this->Credicard->consultarCuenta($cedulaCliente);

        if($res == 1){
            $swValidar = 1;
            $ruta = 'error_critico/' .$swValidar  . '/CAMBIO PIN/WS-CONSULTACUENTA/101095';
            $this->redirect($ruta);
         }else{
            $swActivo = 0;
            $codProducto = _p('tcp_codigo_producto');
            $fechaActivo = '';

            $arreglo = $res['CuentaTarjetaClienteResponse'];

            if(isset($arreglo['codAsociado'])){
                if(trim($arreglo['codigoProducto']) == trim($codProducto)){
                    if($arreglo['codigoEstatus'] < 4){
                        if($arreglo['codigoEstatusTrj'] == '1'){
                            $swActivo = 2;  
                            $nroTarjeta = $arreglo['tarjeta']; 
                            $nroCuenta = $arreglo['cuenta'];
                            $estatusCliente = $arreglo['descripcionEstatus'];
                        }else{
                            $swActivo = 1;
                            $descripcionStatus = $valor['descripcionEstatusTrj'];
                        }
                    }             
                }
            }else{
                foreach ($arreglo as $valor) {
                    $codProductoWS = $valor['codigoProducto'];
                     
                    if(trim($codProductoWS) == trim($codProducto)){
                            if($valor['codigoEstatus'] < 4){
                                if($valor['codigoEstatusTrj'] == '1'){
                                    $swActivo = 2;  
                                    $nroTarjeta = $valor['tarjeta']; 
                                    $nroCuenta = $valor['cuenta'];
                                    $estatusCliente = $valor['descripcionEstatus'];
                                    break;                     
                                }else{
                                    $swActivo = 1;
                                    $descripcionStatus = $valor['descripcionEstatusTrj'];
                                }   
                                
                            }else{
                                $swActivo = 1;  
                                $descripcionStatus = $valor['descripcionEstatus']; 
                            }    
                    }
                }        
            }  
        }                   

        if($swActivo == 0){
            $swValidar = 1;
            $ruta = 'no_cambiopin/'.$swValidar;
            $this->redirect($ruta);  
        }

        if($swActivo == 1){
            $swValidar = 2;
            $ruta = 'no_cambiopin/'.$swValidar . '/'. $descripcionStatus;
            $this->redirect($ruta);         
        }

        $this->Session->write('cambioPIN_TP.nroTarjeta',$nroTarjeta);
        $this->Session->write('cambioPIN_TP.nroCuenta',$nroCuenta);
        $this->Session->write('cambioPIN_TP.estatusCliente',$estatusCliente);



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
                                'url_aceptar'=>'/tarjeta_prepagadas/cambiopin',
                                'layout'=>'interna',
                                'titulo'=>'Generaci&oacute;n de Clave / Tarjeta Prepago CASH',
                                'pasos'=> array(),
                                'paso_actual'=>0,
                                'seccion_actual'=>'seccion_actual','prepagas.cambioPIN'
                        )
                );

                $this->redirect('/seguridad/validar_tdc');
        }


        $nroTarjeta = substr($nroTarjeta,0,4) . "****" . substr($nroTarjeta,12,4);
        $this->set(compact('nroTarjeta')); 
    }


    /////////////////////////////////////////////////////////////////////////
    // Cambio de pin confirmacion                                          //
    /////////////////////////////////////////////////////////////////////////
    function cambiopin_confirmacion(){

    if(!validarTransaccion(101095)) $this->redirect('/consultas/integral');
    /* Para validar el acceso al modulo */
    $this->layout = 'interna';
    $this->set('seccion_actual','prepagas.cambioPIN');
    $usuario = $this->Session->read('usuarios.datos');
    $tarjeta = $this->Session->read('cambioPIN_TP.nroTarjeta');
    $fechaVencimiento = $this->Session->read('cambioPIN_TP.fecVencimiento');
    $codSeguridad = $this->Session->read('cambioPIN_TP.codSeguridad');
    $tarjeta = substr($tarjeta,0,4) . "****" . substr($tarjeta,12,4);
    $this->set(compact('tarjeta','fechaVencimiento','codSeguridad')); 
    }   
    /////////////////////////////////////////////////////////////////////////
    // Fin cambio de pin confirmacion                                      //
    /////////////////////////////////////////////////////////////////////////



    /////////////////////////////////////////////////////////////////////////
    // cambio de pin resultado                                             //
    /////////////////////////////////////////////////////////////////////////
    function cambiopin_resultado(){
    if(!validarTransaccion(101095)) $this->redirect('/consultas/integral');

    $this->layout = 'interna';

    $usuario = $this->Session->read('usuarios.datos');

    $cedulaOrig =  $usuario['cedula'];
    $nro_documento = desencriptar($usuario['cedula']); 
    $cedulaCliente = str_pad($nro_documento, 12, "0", STR_PAD_LEFT);    

    $tarjeta = $this->Session->read('cambioPIN_TP.nroTarjeta');
    $fecVencimiento = $this->Session->read('cambioPIN_TP.fecVencimiento');
    $codSeguridad = $this->Session->read('cambioPIN_TP.codSeguridad');

    $claveNueva = $this->Session->read('cambioPIN_TP.claveNueva');

    $res = $this->Credicard->cambioPIN($cedulaCliente, $tarjeta, $fecVencimiento, $codSeguridad, $claveNueva);

    if($res == 1){
        $swValidar = 3;
        $ruta = 'no_cambiopin/'.$swValidar;
        $this->redirect($ruta); 

    }else{
        if($res['GestionPinPrepagoResponse']['codigoresp']!='00'){  

            $mensaje = $res['GestionPinPrepagoResponse']['motivo'] . " [ " . $res['GestionPinPrepagoResponse']['respuesta'] . " ]";

            $swValidar = 4;
            $ruta = 'no_cambiopin/'.$swValidar . '/' . $mensaje;
            $this->redirect($ruta); 
        }else{

            //enviar correo

            $this->Email->smtpOptions = Configure::read('email.smtp');
            $this->Email->delivery = 'smtp';
            $this->Email->from = Configure::read('email.from');
            $this->Email->to = desencriptar($usuario['email_principal']);
            $this->Email->subject = 'Notificacion de cambio de Pin de Tarjeta Prepago Cash';
            $this->Email->sendAs = 'html';

            // Informacion del correo
            $this->Email->template = 'tcp_cambio_pin';
            $nombreBeneficiario = desencriptar($usuario['nombre']);
             
            $this->set(compact('usuario',' nombreBeneficiario'));

            // Envio del correo
            $this->Email->send();

            //fin de enviar correo  

            //llamar vista de resultado
            $ruta = 'cambiopin_resultado_vta';
            $this->redirect($ruta);
        } 

    }

    } 
    /////////////////////////////////////////////////////////////////////////
    // FIN cambio de pin resultado                                             //
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////
    // Cambio Pin Resultado Vta para mostrar resultados         //
    ///////////////////////////////////////////////////////////// 
    function cambiopin_resultado_vta(){
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.cambioPIN'); 
        $tarjeta = $this->Session->read('cambioPIN_TP.nroTarjeta');
        $fecVencimiento = $this->Session->read('cambioPIN_TP.fecVencimiento');
        $codSeguridad = $this->Session->read('cambioPIN_TP.codSeguridad');
        $tarjeta = substr($tarjeta,0,4) . "****" . substr($tarjeta,12,4);
        $this->set(compact('tarjeta','fecVencimiento','codSeguridad'));
        //$this->set(compact('v_cedula','v_nombre','v_celular','v_agencia','v_cuentaBS'));    
    }



    ////////////////////////////////////////////////////////////////////////
    //  function no_cambiopin                                             //
    //  Permite desplegar la pantalla de errores ocurridos durante        //  
    //  el cambio de PIN(Clave)  de la tarjetas                           //
    ////////////////////////////////////////////////////////////////////////
   function no_cambiopin($swValidar, $respuesta=null){
        $this->layout = 'interna';
        $mensaje = "";
        $proceso = 'CAMBIO PIN';
        $servicio = 'WS-CAMBIO PIN';
        $idTransaccion = 101095;
        switch ($swValidar) {
          case 1:
              $mensaje = _m('tcp.no.solicitada.saldo') . " para Solicitar Cambio de PIN";
              break;  
          case 2:
              $mensaje = _m('tcp.no.pin');
              $mensaje = $mensaje . " [" . $respuesta . "]";
              break;                
          case 3:
              $mensaje = _m('tcp.no.cambiopin');
              $mensaje = $mensaje;
              break;                
          case 4:
             $mensaje = "Estimado Cliente, " . $respuesta;
              break;
        }      
        $descripcion =  $mensaje;  
        //codigo desactivado problema con creacion BD en AEL
        //$this->registrar_log_error($proceso, $servicio, $descripcion); 
        $descripcionLog = $mensaje . "/" . $proceso . "/" . $servicio; 
        $this->logBitacora($idTransaccion,NULL, NULL,NULL,NULL,$descripcionLog,NULL);
        $this->set(compact('descripcion'));
    }
    ///////////////////////////////////////////////////////////////
    //  FIN  function no_cambiopin                               //
    ///////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////////////
    // FIN Cambio de PIn TPC                                                          //
    ////////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////////
    // Funcion consulta de saldo                                                  //
    ////////////////////////////////////////////////////////////////////////////////        

    function consulta_saldo($volver=null){   
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101093)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */

            $this->Session->delete('usuarios.descarga.pdf');
            $this->layout = 'interna';
            $this->set('seccion_actual','prepagas.consultas');





            $tipo_documento = $usuario['tipo_cedula']; 
            $cedulaOrig =  $usuario['cedula'];    
            
            $cedulaCliente = desencriptar($usuario['cedula']);  

            $res = $this->Credicard->consultarCuenta($cedulaCliente);

            $nroTarjeta = '';
            $descripcionStatus = "";
            $nroCuenta = '';
            $estatusCliente = '';
   
            if($res == 1){
                $swValidar = 1;
                $ruta = 'error_critico/'. $swValidar . '/CONSULTA SALDO/WS-CONSULTACUENTA/101092';
                $this->redirect($ruta);
            }else{

                $swActivo = 0;
                $fechaActivo = '';
                $arreglo = $res['CuentaTarjetaClienteResponse'];
                $tipoConsulta = 0;  //1 = fisica, 2 = virtual, 3 = ambas
                
                
                $arregloCtaTarjetas = $this->valConsultaTarjetas($arreglo, 'no_consulta'); 
            }

            $this->Session->delete('consulta_TP');  
            $tipoConsulta = $arregloCtaTarjetas['tipoConsulta']; 


            if($tipoConsulta < 3){
                $this->Session->write('consulta_TP.nroCuenta', $arregloCtaTarjetas['arregloCtaTarjetas'][0]['nroCuenta']);
                $this->Session->write('consulta_TP.nroTarjeta', $arregloCtaTarjetas['arregloCtaTarjetas'][0]['nroTarjeta']);
                $this->Session->write('consulta_TP.estatus', $arregloCtaTarjetas['arregloCtaTarjetas'][0]['estatusCliente']);            
                $this->Session->write('consulta_TP.tipoConsulta', $tipoConsulta);
            }else{
                $arregloCtas = $arregloCtaTarjetas['arregloCtaTarjetas'];

                foreach ($arregloCtas as $valor) {
                    $this->Session->write('consulta_TP.tipoConsulta', $tipoConsulta);
                    $codProducto = _p('tcp_codigo_producto');
                    
                    if($codProducto == $valor['codProducto']){
                        $this->Session->write('consulta_TP.nroCuenta', $valor['nroCuenta']);
                        $this->Session->write('consulta_TP.nroTarjeta', $valor['nroTarjeta']);
                        $this->Session->write('consulta_TP.estatus', $valor['estatusCliente']);        
                    }else{
                        $this->Session->write('consulta_TP.nroCuenta2', $valor['nroCuenta']);
                        $this->Session->write('consulta_TP.nroTarjeta2', $valor['nroTarjeta']);
                        $this->Session->write('consulta_TP.estatus2', $valor['estatusCliente']);
                    }
                }    
             }


             switch($tipoConsulta){
                 case 1:
                    //$ruta = 'seleccionar_consulta_saldo';  
                    $ruta = 'seleccionar_consulta_saldo';
                    break;
                 case 2:
                    $ruta = 'consulta_saldotcp_virtual';                 
                    break;
                 case 3:
                    $ruta = 'seleccionar_consulta_saldo';                   
                    break;
             }  
             $this->redirect($ruta);



    }


    function seleccionar_consulta_saldo(){
        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.consultas');

        if(!empty($this->data['SeleccionarConsultaSaldo'])){
            if($this->data['SeleccionarConsultaSaldo']['tipo_tarjeta'] == 'f'){
                $this->redirect('consulta_saldotcp');        
            }else{
                $this->redirect('consulta_saldotcp_virtual');
            }
        }

        $usuario = $this->Session->read('usuarios.datos');

        $tipoDocumento = $usuario['tipo_cedula'];
        $nroDocumento = desencriptar($usuario['cedula']);


        //Consulta basica del Beneficiario         
        $datos_beneficiario = $this->Activo->invoca(
            'NIBPF3010',
            array(
                'req'=>array(
                    'NACIONALIDAD'=>$tipoDocumento,
                    'NROIDENTIFICACION'=>str_pad($nroDocumento,12,'0',STR_PAD_LEFT),
                    'NROPARAM'=>'1'
                )
            )
        );


        $pNombre = $datos_beneficiario['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'];
        $sNombre = $datos_beneficiario['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE'];
        $pApellido = $datos_beneficiario['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'];        
        $sApellido = $datos_beneficiario['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO'];        

        $nombreCliente = trim($pNombre);
        if(!empty($sNombre)){
            $sNombre = trim($sNombre);
            $nombreCliente = $nombreCliente . "&nbsp;&nbsp;" . substr($sNombre,0,1); 
        }

        $nombreCliente = $nombreCliente . "&nbsp;&nbsp;" .trim($pApellido);

        if(!empty($sApellido)){
            $sApellido = trim($sApellido);
            $nombreCliente = $nombreCliente . "&nbsp;&nbsp;" . substr($sApellido,0,1); 
        }


        $tipo_documento = $usuario['tipo_cedula']; 
        $cedulaOrig =  $usuario['cedula'];    
        $cedulaCliente = desencriptar($usuario['cedula']);
        $res = $this->Credicard->consultarCuenta($cedulaCliente);

        $nroTarjeta = '';
        $descripcionStatus = "";
        $nroCuenta = '';
        $estatusCliente = '';

        if($res == 1){
            $swValidar = 1;
            $ruta = 'error_critico/' .$swValidar . '/CONSULTACUENTA/WS-CONSULTACUENTA/101093';
            $this->redirect($ruta);
        }else{
            $swActivo = 0;
            $codProducto = _p('tcp_codigo_producto');

            $fechaActivo = '';

            $arreglo = $res['CuentaTarjetaClienteResponse'];



            $arregloCtaTarjetas = $this->valConsultaTarjetas($arreglo, 'no_consulta');

            $arrgTarjetas =  $arregloCtaTarjetas['arregloCtaTarjetas'];

            //Crear arreglos para mostrar los numeros de tarjetas en la vista  
            $tarjetas = array();
            $swFisico = 0;
            $swVirtual = 0;

            foreach ($arrgTarjetas as $registro) {
                $tipoProducto = "Virtual";
                if($codProducto == $registro['codProducto']){
                    $swFisico = 1;
                    $nroCuentaF = $registro['nroCuenta'];
                    $nroTarjetaF = $registro['nroTarjeta'];
                    $estatusTarjetaF = $registro['estatusCliente'];
                    $desTarjetaF = substr($registro['nroTarjeta'],0,4) . '****' . substr($registro['nroTarjeta'],12,4); 
                }else{
                    $swVirtual = 1;
                    $nroCuentaV = $registro['nroCuenta'];
                    $nroTarjetaV = $registro['nroTarjeta'];
                    $estatusTarjetaV = $registro['estatusCliente'];
                    $desTarjetaV = substr($registro['nroTarjeta'],0,4) . '****' . substr($registro['nroTarjeta'],12,4); 
                }
            }

            $this->Session->write('consulta_TP.nombreCliente',$nombreCliente);
            $this->Session->write('consulta_TP.nroCuentaF',$nroCuentaF);

            $this->Session->write('consulta_TP.nroTarjetaF',$nroTarjetaF);
            $this->Session->write('consulta_TP.estatusTarjetaF',$estatusTarjetaF);          
            $this->Session->write('consulta_TP.desTarjetaF',$desTarjetaF);

            $this->Session->write('consulta_TP.nroCuentaV',$nroCuentaV); 
            $this->Session->write('consulta_TP.nroTarjetaV',$nroTarjetaV);
            $this->Session->write('consulta_TP.estatusTarjetaV',$estatusTarjetaV);
            $this->Session->write('consulta_TP.desTarjetaV',$desTarjetaV);
        }   


        $this->set(compact('swFisico','swVirtual', 'desTarjetaF','desTarjetaV'));
    } 

    //**********************************************************************
    //Consulta de saldo virtual
    //////////////////////////////////////////////////////////////////////

    function consulta_saldotcp_virtual(){  
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101093)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */
            $this->Session->delete('usuarios.descarga.pdf');
            $this->layout = 'interna';
            $this->set('seccion_actual','prepagas.consultas');

       //validacion Codigo OTP
        $validacion_otp = $this->Session->read('seguridad.validar_otp');
        if (
                (
                $validacion_otp == null ||
                !isset($validacion_otp['validado']) ||
                !$validacion_otp['validado']
                )
        ) {

            $url_volver = '/tarjeta_prepagadas/seleccionar_consulta_saldo';
            $pasos = array(
                            'C&oacute;digo de Autorizaci&oacute;n',
                        );
             
            $this->Session->write(
                'seguridad.validar_otp', array(
                'url_aceptar' => '/tarjeta_prepagadas/consulta_saldotcp_virtual',
                'url_volver' => $url_volver,
                'layout' => 'interna',
                'titulo' => 'Consulta de saldo  tarjeta Prepago CASH',
                'pasos' => $pasos,
                'paso_actual' => 1,
                'seccion_actual' => 'prepagas.consultas'
                    )
            );
            $this->redirect('/seguridad/genera_otp');
        }
        
    

            $nroCuenta = $this->Session->read('consulta_TP.nroCuentaV');
            $statusCuenta = $this->Session->read('consulta_TP.estatusTarjetaV'); 
            $nroTarjeta = $this->Session->read('consulta_TP.nroTarjetaV');

            $nombreCliente = $this->Session->read('consulta_TP.nombreCliente'); 


            //$this->Session->write('consulta_TP.nroCuenta', $nroCuenta);        

            $res = $this->Credicard->consultarSaldo($nroCuenta);

            if(empty($res['PrepagoSaldoResumenResponse']['codigoEstatus'])){
                $swValidar = 1;
                $ruta = 'error_critico/'.$swValidar . '/CONSULTA SALDO/WS-CONSULTASALDO/101093';
                $this->redirect($ruta);
            }
            
            if($res == 1){
                $swValidar = 1;
                $ruta = 'error_critico/' .$swValidar . '/CONSULTA SALDO/WS-CONSULTASALDO/101093';
                $this->redirect($ruta);
            } 

           $saldoCuentaP = $res['PrepagoSaldoResumenResponse']['saldoCuenta'];
           $saldoCuenta = str_replace('.',',',$saldoCuentaP);

           $saldoDiferidoP = $res['PrepagoSaldoResumenResponse']['saldoDiferido'];
           $saldoDiferido = str_replace('.',',',$saldoDiferidoP);
           $saldoDisponible = $saldoCuentaP - $saldoDiferidoP;

           $saldoDisponible = str_replace('.',',',$saldoDisponible);

           $arraySdoDisp = explode(',', $saldoDisponible);

           $tamSdoDisp = strlen($arraySdoDisp[1]);
           if($tamSdoDisp == 2){
                $saldoDisponible = $arraySdoDisp[0] . "," . $arraySdoDisp[1]; 
           }else{
               if($tamSdoDisp == 0){
                  $saldoDisponible = $arraySdoDisp[0] . ",00";
                }else{
                   $saldoDisponible = $arraySdoDisp[0] . "," . $arraySdoDisp[1] . "0";
                }  
           } 

           $arryActivacion = $this->GtpTarjetaVirtual->obtenerTarjeta($nroTarjeta);

           $fechaVencimiento = desencriptar($arryActivacion['GtpTarjetaVirtual']['fecha_vencimiento']);

           $fechaVencimiento = substr($fechaVencimiento,0,2) . "/" . substr($fechaVencimiento,2,2);

           $cvv = desencriptar($arryActivacion['GtpTarjetaVirtual']['cvv']);

           $this->set(compact('nroTarjeta', 'statusCuenta','saldoCuenta', 'saldoDiferido','saldoDisponible', 'fechaVencimiento', 'cvv', 'nombreCliente'));
     }


    
    function consulta_saldotcp(){  
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101093)) $this->redirect('/consultas/integral');

            /* Para validar el acceso al modulo */
            $this->Session->delete('usuarios.descarga.pdf');
            $this->layout = 'interna';
            $this->set('seccion_actual','prepagas.consultas');

            $usuario = $this->Session->read('usuarios.datos');

            $nroCuenta = $this->Session->read('consulta_TP.nroCuentaF');  
            //$this->Session->write('consulta_TP.nroCuenta', $nroCuenta);

            $nroTarjeta = $this->Session->read('consulta_TP.nroTarjetaF');
            $statusCuenta = $this->Session->read('consulta_TP.estatusTarjetaF'); 





            $res = $this->Credicard->consultarSaldo($nroCuenta);

            if(empty($res['PrepagoSaldoResumenResponse']['codigoEstatus'])){
                $swValidar = 1;
                $ruta = 'error_critico/'.$swValidar . '/CONSULTA SALDO/WS-CONSULTASALDO/101093';
                $this->redirect($ruta);
            }
            
            if($res == 1){
                $swValidar = 1;
                $ruta = 'error_critico/' .$swValidar . '/CONSULTA SALDO/WS-CONSULTASALDO/101093';
                $this->redirect($ruta);
            } 

           $saldoCuentaP = $res['PrepagoSaldoResumenResponse']['saldoCuenta'];
           $saldoCuenta = str_replace('.',',',$saldoCuentaP);

           $saldoDiferidoP = $res['PrepagoSaldoResumenResponse']['saldoDiferido'];
           $saldoDiferido = str_replace('.',',',$saldoDiferidoP);
           $saldoDisponible = $saldoCuentaP - $saldoDiferidoP;

           $saldoDisponible = str_replace('.',',',$saldoDisponible);

           $arraySdoDisp = explode(',', $saldoDisponible);

           $tamSdoDisp = strlen($arraySdoDisp[1]);
           if($tamSdoDisp == 2){
                $saldoDisponible = $arraySdoDisp[0] . "," . $arraySdoDisp[1]; 
           }else{
               if($tamSdoDisp == 0){
                  $saldoDisponible = $arraySdoDisp[0] . ",00";
                }else{
                   $saldoDisponible = $arraySdoDisp[0] . "," . $arraySdoDisp[1] . "0";
                }  
           } 


           $this->set(compact('nroTarjeta', 'statusCuenta','saldoCuenta', 'saldoDiferido','saldoDisponible'));
     }


    ///////////////////////////////////////////////////////////////////
    //  function no_consulta      
    //  Permite desplegar la pantalla de errores ocurridos durante 
    //  la la consulta de la tarjetas
    ///////////////////////////////////////////////////////////////////
    function no_consulta($swValidar,$respuesta=null){
        $this->layout = 'interna';
        $mensaje = "";
        $proceso = 'CONSULTA SALDO';
        $servicio = 'WS-CONSULTACUENTA';
        $idTransaccion = 101093;
        switch ($swValidar) {
          case 1:
              $mensaje = _m('tcp.no.solicitada.saldo') . " para Consultar Saldo";
              break;  
          case 2:
              $mensaje = _m('tcp.no.consulta');
              $mensaje = $mensaje . " [" . $respuesta . "]";
              break;  
          case 3:
              $mensaje = _m('tcp.error.consulta');
              $servicio = 'WS-CONSULTASALDO';
              break;                
        }      

        $descripcion =  $mensaje;  
        $descripcionLog = $mensaje . "/" . $proceso . "/" . $servicio; 
        $this->logBitacora($idTransaccion,NULL, NULL,NULL,NULL,$descripcionLog,NULL);
       // Codigo desactivado motivo probllema en lacreacion tabla en la bd          
       // $this->registrar_log_error($proceso, $servicio, $descripcion);
        $this->set(compact('descripcion'));
    }

    ///////////////////////////////////////////////////////
    // FIN  function no_consulta                         // 
    ///////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////
    //Fin Consulta de saldo                                                     //
    //////////////////////////////////////////////////////////////////////////////    


     /////////////////////////////////////////////////////////////////
     // Movimiento de tarjeta prepagada cash
     /////////////////////////////////////////////////////////////////

    function movimientos(){
    
     if(!validarTransaccion(101094)) $this->redirect('/consultas/integral');
    /* Para validar el acceso al modulo */
    $this->Session->delete('usuarios.descarga.pdf');
    $this->layout = 'interna';
    $this->set('seccion_actual','prepagas.movimientos');


    $usuario = $this->Session->read('usuarios.datos');
    $tipo_documento = $usuario['tipo_cedula']; 
    $cedulaOrig =  $usuario['cedula'];    
    $cedulaCliente = desencriptar($usuario['cedula']);
    $res = $this->Credicard->consultarCuenta($cedulaCliente);

    $nroTarjeta = '';
    $descripcionStatus = "";
    $nroCuenta = '';
    $estatusCliente = '';

    if($res == 1){
        $swValidar = 1;
        $ruta = 'error_critico/' .$swValidar . '/MOVIMIENTO/WS-CONSULTACUENTA/101094';
        $this->redirect($ruta);
     }else{
        $swActivo = 0;
        $codProducto = _p('tcp_codigo_producto');
        $fechaActivo = '';

        $arreglo = $res['CuentaTarjetaClienteResponse'];

        $arregloCtaTarjetas = $this->valConsultaTarjetas($arreglo, 'no_movimiento');

        $arrgTarjetas =  $arregloCtaTarjetas['arregloCtaTarjetas'];

        //Crear arreglos para mostrar los numeros de tarjetas en la vista  
        $tarjetas = array();
        $tipoFisica = "";

        foreach ($arrgTarjetas as $registro) {
                $tipoProducto = "Virtual";
                if($codProducto == $registro['codProducto']){
                    $tipoProducto = "Fisica";
                }

                $letraProducto = substr($tipoProducto,0,1); 
                $nroCuenta = trim($registro['nroTarjeta']) . $letraProducto;

                $tarjetas[$nroCuenta] = enmascara($registro['nroTarjeta']) . ' - '. $tipoProducto;
                $tipoDigital = substr($registro['nroTarjeta'],12,4);

        }
    }   





     $this->Session->write('movimiento_TP.nroCuenta',$nroCuenta);
     $this->Session->write('movimiento_TP.estatusCliente',$estatusCliente);
     $this->Session->write('movimiento_TP.tarjetas',$tarjetas);
     $this->Session->write('movimiento_TP.tipoTarjeta',$tipoDigital);
     $this->Session->write('movimiento_TP.ctaTarjetas',$arrgTarjetas);

     //obtener arreglo de mes
     $arrayMeses = $this->SolicitudTarjetasPrepagadas->meses();
     $mes = '';
     $anio = '';
     $this->set(compact('mes', 'anio','arrayMeses', 'tarjetas', 'tipoProducto'));     
    }//fin movimientos


   ///////////////////////////////////////////////////////////////////
   // Realiza la consulta en web service de movimiento y activa la  //
   // vista donde se ven los movimientos                            // 
   /////////////////////////////////////////////////////////////////// 
    function mostrar_movimiento(){
        if(!validarTransaccion(101094)) $this->redirect('/consultas/integral');
            $this->Session->delete('usuarios.descarga.pdf');
            $this->layout = 'interna';
            $this->set('seccion_actual','prepagas.movimientos');
     
         if(!empty($this->data['Movimientos'])){
            $mes = $this->data['Movimientos']['meses'];

            if($mes < 10){
               $mes = "0".$mes;            
            }

            $anio = $this->data['Movimientos']['anio'];
            
            $nroCuenta = $this->data['Movimientos']['tarjeta'];
            $letraTipo = substr($nroCuenta,16,1);
            $nroCuenta = substr($nroCuenta,0,16);

            if($letraTipo == 'F'){
                $tipoTarjeta = 'FISICA';    
            }else{
                $tipoTarjeta = 'VIRTUAL';    
            } 

            //obtener estatus de la cuenta
            $arregloCta  = $this->Session->read('movimiento_TP.ctaTarjetas');

            $estatusCuenta = '';

            foreach ($arregloCta as $cuenta) {
                if(trim($nroCuenta) == trim($cuenta['nroTarjeta'])){
                   $estatusCuenta = $cuenta['estatusCliente']; 
                   break;
                }
            }


            $arrayMovimientos = $this->Credicard->movimientoSaldo($anio, $mes, $nroCuenta);

            if($arrayMovimientos == 1){
                $swValidar = 5;
                $ruta = 'error_critico/'.$swValidar  . '/MOVIMIENTO/WS-MOVIMIENTO/101094';
                $this->redirect($ruta);
            } 
                

            /*$tipoT = "FISICA";
            $tipoTarjeta == substr($nroCuenta,12,4);
            if($tipoTarjeta == substr($nroCuenta,12,4)){
                $tipoT = "VIRTUAL";
            }*/

            $nroCuenta = substr($nroCuenta,0,4)."****".substr($nroCuenta,12,4) . " [". $tipoTarjeta . "]";

            $arrayData = array('ctaDebito'=>$nroCuenta);

             $this->Session->write('usuarios.descarga.pdf',$arrayData);


            $usuario = $this->Session->read('usuarios.datos');
            $tipo_documento = $usuario['tipo_cedula']; 
            $cedulaOrig =  $usuario['cedula'];    
            $cedulaCliente = desencriptar($usuario['cedula']);  
            $this->set(compact('arrayMovimientos', 'nroCuenta', 'estatusCuenta'));
         }
    }
    ///////////////////////////////////////////////////////////////////
    // FIN mostrar Movimientos                                       //
    /////////////////////////////////////////////////////////////////// 

   /////////////////////////////////////////////////////////////////////
   // function no_consulta                                            //
   // Permite desplegar la pantalla de errores ocurridos durante      // 
   //la consulta de movimientos de la tarjetas                        //
   /////////////////////////////////////////////////////////////////////
    function no_movimiento($swValidar, $respuesta=null){
        $this->layout = 'interna';
        $mensaje = "";
        $proceso = 'MOVIMIENTO';
        $servicio = 'WS-MOVIMIENTO';
        $idTransaccion = 101094;
        switch ($swValidar) {
          case 1:
              $mensaje = _m('tcp.no.solicitada.saldo') . " para Consultar Movimientos";
              break;  
          case 2:
              $mensaje = _m('tcp.no.movimiento');
              $mensaje = $mensaje . " [" . $respuesta . "]";
              break;  
          case 3:
              $mensaje = _m('tcp.error.movimiento');
              break;                
        }      
        $descripcion =  $mensaje;
        //codigo desactivado problema con creacion BD en AEL
        //$this->registrar_log_error($proceso, $servicio, $descripcion);  
        $descripcionLog = $mensaje . "/" . $proceso . "/" . $servicio; 
        $this->logBitacora($idTransaccion,NULL, NULL,NULL,NULL,$descripcionLog,NULL);
        $this->set(compact('descripcion'));
    }
    //////////////////////////////////////////////////////////////
    // FIN  function no_movimiento                              // 
    ////////////////////////////////////////////////////////////// 

    ///////////////////////////////////////////////////////////////////////
    // FIN Movimiento de tarjeta prepagada cash                         //
    //////////////////////////////////////////////////////////////////////


    ///////////////////////////////////////////////////////////////////
    // Candado TPC                                                   //
    // Abrir y cerrar candado                                        // 
    ///////////////////////////////////////////////////////////////////
    //-------------- CANDADO VIRTUAL -----------------// 

    function candadov(){ 

        if(!validarTransaccion(102097)) $this->redirect('/consultas/integral');  

        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual', 'prepagas.candadov');

        if(!empty($this->data['Candadov'])){
            $this->redirect('candado_confirmacion');
        }

        $usuario = $this->Session->read('usuarios.datos');
        $tipo_documento = $usuario['tipo_cedula']; 
        $cedulaOrig =  $usuario['cedula'];    
        $cedulaCliente = desencriptar($usuario['cedula']);
        $codProducto = _p('tcp_codigo_producto');

        $res = $this->Credicard->consultarCuenta($cedulaCliente); 

        if($res == 1){
            $swValidar = 1;
            $ruta = 'error_critico/' .$swValidar . '/CANDADO/WS-CONSULTACUENTA/101094';
            $this->redirect($ruta);
         }else{
            $swActivo = 0;
            $arreglo = $res['CuentaTarjetaClienteResponse'];
            $arregloCtaTarjetas = $this->valConsultaTarjetas($arreglo, 'no_candado');

            $arrgTarjetas =  $arregloCtaTarjetas['arregloCtaTarjetas'];
            //Crear arreglos para mostrar los numeros de tarjetas en la vista  
            $tarjetas = array();
            foreach ($arrgTarjetas as $registro) {
                $tipoProducto = "Virtual";
                if($codProducto == $registro['codProducto']){
                    $tipoProducto = "Fisica";
                }

                $tarjetas[$registro['nroTarjeta']] = enmascara($registro['nroTarjeta']) . ' - '.$tipoProducto;
            }
        }    

         $this->Session->write('tarjeta_candadov_TP.tarjetas',$tarjetas);
         //obtener arreglo de mes
         $this->set(compact('tarjetas'));
    }    


    function mostrar_candadov(){
        if(!validarTransaccion(102097)) $this->redirect('/consultas/integral');  

        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual', 'prepagas.candadov');
        $usuario = $this->Session->read('usuarios.datos');
        $nro_documento = desencriptar($usuario['cedula']); 

        if(!empty($this->data['Candadov'])){
            $nroTarjeta = $this->data['Candadov']['tarjeta'];
            $cedulaClienteVirtual = $nro_documento;  
            $cedulaCliente = str_pad($nro_documento, 12, '0', STR_PAD_LEFT);
            $estatusCandado = 0;
            $desEstatusCandado = '';
            $estatusCandadoNew = 0;
            $desEstatusCandadoNew = '';
            $estatusCuenta = '';

            $res = $this->Credicard->consultaCandado($cedulaClienteVirtual);



            if($res == 1){
                 $swValidar = 1;
                 $ruta = 'error_critico/'.$swValidar  . '/ACTIVACION/WS-CONSULTA_CANDADO_VIRTUAL/102097';
                 $this->redirect($ruta);
            }else{
                if(isset($res['ConsultaCandadoServiceResponse']['candado'])){
                    if($res['ConsultaCandadoServiceResponse']['candado'] < 2){
                        $nroTarjeta = $res['ConsultaCandadoServiceResponse']['tarjeta'];
                        $estatusCandado = $res['ConsultaCandadoServiceResponse']['candado'];
                    }else{
                        $swValidar = 1;
                        $ruta = 'no_candado/'.$swValidar;
                        $this->redirect($ruta);
                    }    
                }else{
                    $arrg = $res['ConsultaCandadoServiceResponse'];
                    foreach ($arrg as $arrgCandado) {
                        if($arrgCandado['tarjeta']==$nroTarjeta){
                            if($arrgCandado['candado'] < 2){
                                $nroTarjeta = $arrgCandado['tarjeta'];
                                $estatusCandado = $arrgCandado['candado'];
                            }else{
                                $swValidar = 1;
                                $ruta = 'no_candado/'.$swValidar;
                                $this->redirect($ruta);                                
                            }    
                        }  
                    }
                }    
            }    
        }    
        if($estatusCandado == 0){
            $desEstatusCandado = 'CERRADO';   
            $estatusCandadoNew = 1;
            $desEstatusCandadoNew = 'ABIERTO';
        }else{
            $desEstatusCandado = 'ABIERTO';            
            $estatusCandadoNew = 0;
            $desEstatusCandadoNew = 'CERRADO';
        } 

        $this->Session->write('tarjeta_candadov_TP.cedulaCliente',$cedulaCliente); 
        $this->Session->write('tarjeta_candadov_TP.cedulaClienteV',$cedulaClienteVirtual);
        $this->Session->write('tarjeta_candadov_TP.nroTarjeta',$nroTarjeta);
        $this->Session->write('tarjeta_candadov_TP.estadoCandado',$estatusCandado);
        $this->Session->write('tarjeta_candadov_TP.desEstadoCandado',$desEstatusCandado);
        $this->Session->write('tarjeta_candadov_TP.estadoCandado',$estatusCandado);
        $this->Session->write('tarjeta_candadov_TP.desEstadoCandado',$desEstatusCandado);
        $this->Session->write('tarjeta_candadov_TP.estadoCandadoNew',$estatusCandadoNew);
        $this->Session->write('tarjeta_candadov_TP.desEstadoCandadoNew',$desEstatusCandadoNew);
        $this->set(compact('nroTarjeta', 'estatusCandado', 'desEstatusCandado'));        
        $this->redirect('candado_confirmacion');
    }    

    //---------- Fin candado Virtual  ------------------//


    /**
     * Metodo para realizar la confirmacion de activación desactivacion candado virtual
     */
    function candado_confirmacion(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(102097)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */
        $this->layout = 'interna';
        $this->set('seccion_actual', 'prepagas.candadov'); 

        if($this->Session->check('tarjeta_candadov_TP.nroTarjeta')){
            $usuario = $this->Session->read('usuarios.datos');
            $nroTarjeta = $this->Session->read('tarjeta_candadov_TP.nroTarjeta');
            $desEdoActual = $this->Session->read('tarjeta_candadov_TP.desEstadoCandado');
            $desEdoNew = $this->Session->read('tarjeta_candadov_TP.desEstadoCandadoNew');
            $this->set(compact('nroTarjeta','desEdoActual','desEdoNew')); 
        }else {
            $this->redirect('candadov');
        }   
    }


    /**
     * Metodo para Candado Virtual Activar o sdesactivar.
     */
    function candado_resultado(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(102097)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */
        $idTransaccion = 102097;
        $this->layout = 'interna';
        $this->set('seccion_actual', 'prepagas.candadov');

        if(!$this->Session->check('tarjeta_candadov_TP.nroTarjeta')){
            $this->redirect('candadov');
        }   

        $validacion_otp = $this->Session->read('seguridad.validar_otp');
        if (
                (
                $validacion_otp == null ||
                !isset($validacion_otp['validado']) ||
                !$validacion_otp['validado']
                )
        ) {

            $url_volver = '/tarjeta_prepagadas/candadov';
            $pasos = array(
                            'Validar Informaci&oacute;n Banco',
                            'Confirmaci&oacute;n',
                            'C&oacute;digo de Autorizaci&oacute;n',
                            'Resultado de Operaci&oacute;n'
                        );

            $this->Session->write(
                'seguridad.validar_otp', array(
                'url_aceptar' => '/tarjeta_prepagadas/candado_resultado',
                'url_volver' => $url_volver,
                'layout' => 'interna',
                'titulo' => 'Candado Virtual tarjeta Prepago CASH',
                'pasos' => $pasos,
                'paso_actual' => 2,
                'seccion_actual' => 'prepagadas.candadov'
                    )
            );
            $this->redirect('/seguridad/genera_otp');
        }
        
       

        $nroTarjeta = $this->Session->read('tarjeta_candadov_TP.nroTarjeta');
        $estatusCandadoNew = $this->Session->read('tarjeta_candadov_TP.estadoCandadoNew');
        $estatusCandado = $this->Session->read('tarjeta_candadov_TP.estadoCandado');
        $res = $this->Credicard->actualizarCandado($nroTarjeta, $estatusCandado);

        if($res == 1){
            $swValidar = 1;
            $ruta = 'error_critico/'. $swValidar . '/CANDADO VIRTUAL/WS-CANDADO/102097';
            $this->redirect($ruta);
        }else{
            $respuesta = $res ['ActivacionDesactivacionCandadoPrepagoServiceResponse']['codigoresp'];
            if($respuesta == '0'){
                $nroTarjeta = $this->Session->read('tarjeta_candadov_TP.nroTarjeta');
                $desEdoNew = $this->Session->read('tarjeta_candadov_TP.desEstadoCandadoNew');
                
                //log transaccional
                $descripcion = 'Candado Virtual ' . $desEdoNew . " Satisfactoriamente";
                $cedula = $this->Session->read('tarjeta_candadov_TP.cedulaClienteV');


                //Generar registro de Bitacora
                $usuario = $this->Session->read('usuarios.datos');
                $usuario_id = $usuario['usuario_id'];
                $ctaOrigen = NULL;
                $ctaDestino = $nroTarjeta;
                $monto = NULL;
                $referencia = NULL;

                $this->Bitacora->registraBitacora(
                    $usuario_id, 
                    $idTransaccion,
                    $ctaOrigen, 
                    $ctaDestino, 
                    $monto,
                    $referencia,
                    $descripcion,
                    0,
                    $cedula
                );

                //$this->logBitacora('102097',$nroTarjeta, NULL,NULL,NULL,$descripcion,0);
                //fin log
                
                $ruta = 'candado_resultado_vta';
                $this->redirect($ruta);    
                

                //$this->set(compact('nroTarjeta','desEdoNew'));                 
            }else{
                $swValidar = 3;
                $respuesta = $res ['ActivacionDesactivacionCandadoPrepagoServiceResponse']['mensaje'];
                $ruta = 'no_candado/'.$swValidar.'/'.$respuesta;
                $this->redirect($ruta); 
            }
        }    
    }    

    function candado_resultado_vta(){
       $this->layout = 'interna';
       $this->set('seccion_actual', 'prepagas.candadov');

       $nroTarjeta = $this->Session->read('tarjeta_candadov_TP.nroTarjeta');
       $desEdoNew = $this->Session->read('tarjeta_candadov_TP.desEstadoCandadoNew');
       $this->set(compact('nroTarjeta','desEdoNew')); 

    }


    /******************************************************************
      function no_candado    
      Permite desplegar la pantalla de errores ocurridos durante 
      la activacion del candado de la tarjetas
    ******************************************************************/
    function no_candado($swValidar,$respuesta=null){
        $this->layout = 'interna';   
        $mensaje = "";
        $proceso = 'CONSULTA CANDADO VIRTUAL';
        $servicio = 'WS-CANDADO VIRTUAL';
        $idTransaccion = 102097;
        switch ($swValidar) {
          case 1:
              $mensaje = _m('candado.tpc.tarjeta.invalida');
              break;
          case 2:
              //dos tarjetas activas
              $mensaje = _m('tcp.nocandado') . " [ " . $respuesta . " ]";
              break;
          case 3: 
              $mensaje = _m('tcp.nocandado') . " [ " . $respuesta . " ]";
        }
        $descripcion = $mensaje;  

        //log transaccional
        $nroTarjeta = $this->Session->read('tarjeta_candadov_TP.nroTarjeta');
        $this->logBitacora('102097',$nroTarjeta, NULL,NULL,NULL,$descripcion,NULL);
        //fin log

        $usuario = $this->Session->read('usuarios.datos');
        $cedula = $usuario['cedula'];

        $usuario_id = $usuario['usuario_id'];
        $idTransaccion = 102097;
        $ctaOrigen = NULL;
        $ctaDestino = NULL;
        $monto = NULL;
        $referencia = NULL;
        $descripcionLog = $mensaje . "/" . $proceso . "/" . $servicio; 
        $this->logBitacora($idTransaccion,NULL, NULL,NULL,NULL,$descripcionLog,NULL);
        $this->Bitacora->registraBitacora(
            $usuario_id, 
            $idTransaccion,
            $ctaOrigen, 
            $ctaDestino, 
            $monto,
            $referencia,
            $descripcion,
            0,
            $cedula
        );
        //codigo desactivado problema con creacion BD en AEL
        //$this->registrar_log_error($proceso, $servicio, $descripcion); 
        $this->set(compact('descripcion'));
    }

    /******************************************************************
      FIN  function no_candado
    ******************************************************************/


    ///////////////////////////////////////////////////////////////////
    // Recargas Propias                                              //  
    //recarga mismo titular
    //Recarga tarjeta prepagada
    ////////////////////////////////////////////////////////////////     
    function recarga($volver=null){   
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101092)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        if(!empty($this->data['Recarga'])){
            $this->set('recarga_TP',$this->data);
            $comision = $this->data['Recarga']['totalComision'];
            if($comision == '0,00'){
                // Si la conision es cero quiere decir que no se realizo en cambio con javascript
                // Porque esta utilizando como navegador explorer y activo en linea no trabajando
                //con este navegador
                $swValidar = 9;
                $descripcionStatus = _m('tcp.no.java');
                $ruta = 'no_recarga/'.$swValidar. '/' .$descripcionStatus;
                $this->redirect($ruta);                     
            } 

            $nroTarjeta = $this->data['Recarga']['tarjeta'];  
            $nroTarjeta = substr($nroTarjeta,0,16);
            $usuario = $this->Session->read('usuarios.datos');
            $cedulaCliente = desencriptar($usuario['cedula']);  
             if($this->ValTipoTarjeta($nroTarjeta, $cedulaCliente) == 'F'){
                 $this->Session->write('recarga_TP.comisionRecarga',$this->Session->read('recarga_TP.comisionRecargaF')); 
                 $this->Session->write('recarga_TP.etiqComisionRecarga',$this->Session->read('recarga_TP.etiqComisionRecargaF'));                 
                 $tipoTarjeta = 'RTPP';
             }else{
                 $this->Session->write('recarga_TP.comisionRecarga',$this->Session->read('recarga_TP.comisionRecargaD')); 
                 $this->Session->write('recarga_TP.etiqComisionRecarga',$this->Session->read('recarga_TP.etiqComisionRecargaD'));                 
                 $tipoTarjeta = 'RTPV';
             }


            $this->Session->write('recarga_TP.comisionRecargaF',$comisionTCPF); 
            $this->Session->write('recarga_TP.etiqComisionRecargaF',$etiqComisionF);  



            $ctaDebito = $this->data['Recarga']['cuentaCash']; 
            $totalRecarga = $this->data['Recarga']['totalRecarga'];

            //Validar saldo para realizar la recarga 
            $this->valSaldoRecarga($ctaDebito, $totalRecarga, 'RP');
            $this->Session->write('recarga_TP.tipoTarjeta', $tipoTarjeta);
            $this->Session->write('recarga_TP.cuentaRecarga', $nroTarjeta);
            $this->Session->write('recarga_TP.cuentaCash', $this->data['Recarga']['cuentaCash']);
            $this->Session->write('recarga_TP.montoRecarga', $this->data['Recarga']['montoRecarga']);
            $this->Session->write('recarga_TP.comision', $comision);
            $this->Session->write('recarga_TP.montoTotal', $totalRecarga);
            $this->Session->write('recarga_TP.recarga', $this->data);
            $this->redirect('recarga_confirmacion');
        }

        $this->Session->delete('recarga_TP');
        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.recargas');
        $this->Session->delete('recarga_TP.recarga');




        $usuario = $this->Session->read('usuarios.datos');

        //validar Nro de recargas diarias y mensuales  
        $res = $this->TcpCantidadRecarga->consultarNroRecarga($usuario['usuario_id']);  
         
        if($res){
           $diaActual = date("j");
           $mesActual = date("n");
           $diaRecarga = $res['dia_recarga'];
           $mesRecarga = $res['mes_recarga'];
           $cantDia = $res['cantidad_recarga_dia'];
           $cantMes = $res['cantidad_recarga_mes'];
           $cantDiaRecarga = _p('tcp_recarga_dia');
           $cantMesRecarga = _p('tcp_recarga_mes');
            if($mesActual == $mesRecarga){
                if($diaActual == $diaRecarga){
                    if($cantMes == $cantMesRecarga){
                        $swValidar = 7;
                        $descripcionStatus = $cantMesRecarga;
                        $ruta = 'no_recarga/'.$swValidar. '/' .$descripcionStatus;
                        $this->redirect($ruta); 
                    }else{
                        if($cantDia == $cantDiaRecarga){
                            $swValidar = 6;
                            $descripcionStatus = $cantDiaRecarga;
                            $ruta = 'no_recarga/'.$swValidar. '/' .$descripcionStatus;
                            $this->redirect($ruta); 
                        }
                    } 
                }else{
                    if($cantMes == $cantMesRecarga){
                        $swValidar = 7;
                        $descripcionStatus = $cantMesRecarga;
                        $ruta = 'no_recarga/'.$swValidar. '/' .$descripcionStatus;
                        $this->redirect($ruta);
                    }else{
                        $res = $this->TcpCantidadRecarga->modificarDiaRecarga($usuario['usuario_id'], $diaActual);
                    }
                }      
            }else{
                $res = $this->TcpCantidadRecarga->modificarMesRecarga($usuario['usuario_id'], $mesActual, $diaActual);
           } 
        }


        //fin validar nro de recargas por dia o por mes

        $tipo_documento = $usuario['tipo_cedula']; 
        $cedulaOrig =  $usuario['cedula'];    
        $cedulaCliente = desencriptar($usuario['cedula']); 
        $nro_documento = $cedulaCliente;

        //consulta Cliente en WS credicard 
        $nro_documento = str_pad($nro_documento, 12, "0", STR_PAD_LEFT);
        $datos_basicos = $this->datosBasicosUsuarios($tipo_documento, $nro_documento);

        $res = $this->Credicard->consultarCuenta($cedulaCliente); 

         


        $nroTarjeta = '';
        $ctaTarjeta = '';
        $descripcionStatus = "";

        if($res == 1){
            $swValidar = 1;
            $ruta = 'error_critico/'.$swValidar . '/RECARGA PROPIA/WS-CONSULTACUENTA/101092';
            $this->redirect($ruta);
        }else{

            $swActivo = 0;
            $codProducto = _p('tcp_codigo_producto');
            $fechaActivo = '';
            $arreglo = $res['CuentaTarjetaClienteResponse'];

            $arregloCtaTarjetas = $this->valConsultaTarjetas($arreglo, 'no_recarga');


            $arrgTarjetas =  $arregloCtaTarjetas['arregloCtaTarjetas'];

            //Crear arreglos para mostrar los numeros de tarjetas en la vista  
            $tarjeta = array();
            foreach ($arrgTarjetas as $registro) {
                $tipoProducto = "Virtual";
                $tProducto = "D";
                if($codProducto == $registro['codProducto']){
                    $tipoProducto = "Fisica";
                    $tProducto = "F";
                }
                $tarjeta[trim($registro['nroTarjeta']).$tProducto] = enmascara($registro['nroTarjeta']) . ' - '.$tipoProducto;
            }
        } 

        $tipoCedula = $usuario['tipo_cedula']; 
        $cedulaCliente = desencriptar($usuario['cedula']); 
        $cedulaCliente = str_pad($cedulaCliente, 12, "0", STR_PAD_LEFT); 

        $this->Session->write('recarga_TP.cedulaCliente',$cedulaCliente); 
        $this->Session->write('recarga_TP.nroTarjeta',$nroTarjeta);
        $usuario = $this->Session->read('usuarios.datos');
        $login = desencriptar($usuario['login']);

        //obtener cuenta divisa asociada TDD para mostrarla pasando al servicio el nro de cedula
 
        $swValidar = 0;
        $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();
           
        $swCta = 0;  
        if(count($cuentasValidar) > 0){
          $cuentaME = array();
          foreach ($cuentasValidar as $key => $value) {
                $valores = explode('-', $value);
                $moneda = trim($valores[2]);
                $tipoCta = trim($valores[1]);                
                if($moneda=='USD' && ($tipoCta=='DEPN' || $tipoCta=='CDN1')){
                    $swCta = 1;
                    $item = substr($valores[0],0,4) .'****'.substr($valores[0],-4) . " - " . $valores[2] . " - " . $valores[3]; 
                    $cuentaME[$valores[0]] = $item;  
                }    
          }   
        }  

        if($swCta == 0){
            $swValidar = 3;
            $ruta = 'no_recarga/'.$swValidar;
            $this->redirect($ruta);
        }   

        $login = desencriptar($usuario['login']);



        //Capturar la comision de AS400 Fisica 
        /*******************************************
        /* Para validar el acceso al modulo */
        /*****************************************/
        $resComision = $this->Activo->invoca(
            'IN206001',
            array(
                'req'=>array(
                    'IN2060COD'=> 'RTPP'
                )   
            )
        );
        
        if($resComision == false){
            $swValidar = 2;
            $ruta = 'error_critico/'.$swValidar . '/RECARGA MISMO TITULAR/101093';
            $this->redirect($ruta);            
        }
        $comisionTCPF = $resComision['IN206001Return']['IN206001']['IN2060COM'];
        $etiqComisionF = '';
        $arrayCom = explode('.', $comisionTCPF);
        if(isset($arrayCom[1])){
            if(strlen($arrayCom[1])==1){
                $etiqComisionF = $arrayCom[0] . "," . $arrayCom[1] . "0";
            }else{
                $etiqComisionF = $arrayCom[0] . "," . $arrayCom[1];    
            }
        }else{
            $etiqComisionF = $arrayCom[0] . ",00"; 
        } 


        //Capturar la comision de AS400 Virtual
        /*******************************************
        /* Para validar el acceso al modulo */
        /*****************************************/
        $resComisionD = $this->Activo->invoca(
            'IN206001',
            array(
                'req'=>array(
                    'IN2060COD'=> 'RTPV'
                )   
            )
        );

        if($resComisionD == false){
            $swValidar = 2;
            $ruta = 'error_critico/'.$swValidar . '/RECARGA MISMO TITULAR/101093';
            $this->redirect($ruta);            
        }
        $comisionTCPD = $resComisionD['IN206001Return']['IN206001']['IN2060COM'];
        $etiqComisionD = '';
        $arrayCom = explode('.', $comisionTCPD);
        if(isset($arrayCom[1])){
            if(strlen($arrayCom[1])==1){
                $etiqComisionD = $arrayCom[0] . "," . $arrayCom[1] . "0";
            }else{
                $etiqComisionD = $arrayCom[0] . "," . $arrayCom[1];    
            }
        }else{
            $etiqComisionD = $arrayCom[0] . ",00"; 
        } 





        $this->Session->write('recarga_TP.comisionRecargaF',$comisionTCPF); 
        $this->Session->write('recarga_TP.etiqComisionRecargaF',$etiqComisionF);    

        $this->Session->write('recarga_TP.comisionRecargaD',$comisionTCPD); 
        $this->Session->write('recarga_TP.etiqComisionRecargaD',$etiqComisionD);  

        $this->Session->write('recarga_TP.datosBasicos',$datos_basicos);  

        $recargaMaxima = _p('tcp_recarga_max'); 
        $recargaMinima = _p('tcp_recarga_min'); 
        $this->set(compact('usuario', 'tarjeta', 'cuentaME', 'comisionTCPF','etiqComisionF','recargaMaxima','recargaMinima', 'comisionTCPD','etiqComisionD'));
    }
    //fin recarga mismo titular

   /**
     * Metodo para realizar la confirmacion de la compra de divisas.
     */
    function recarga_confirmacion(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101092)) $this->redirect('/consultas/integral');
                 
            /* Para validar el acceso al modulo */
            $this->layout = 'interna';
            $this->set('seccion_actual','prepagas.recargas');
            
            if($this->Session->check('recarga_TP')){
                $usuario = $this->Session->read('usuarios.datos');
                $tarjeta = $this->Session->read('recarga_TP.cuentaRecarga');
                $debito = $this->Session->read('recarga_TP.cuentaCash');
                $comision = $this->Session->read('recarga_TP.comision');
                $montoTotal = $this->Session->read('recarga_TP.montoTotal');
                $montoRecarga = $this->Session->read('recarga_TP.montoRecarga');
                $this->data = $this->Session->read('recarga_TP.recarga');  
                $etiqComision = $this->Session->read('recarga_TP.etiqComisionRecarga');
                $this->set(compact('tarjeta','montoRecarga','debito','comision','montoTotal','etiqComision')); 
            }else{
                $this->redirect('recarga');
            }    
    }


    /**
     * Metodo para realizar la confirmacion de la compra de divisas.
     */
    function recarga_resultado(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101092)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        if($this->Session->read('recarga_TP.swResultado')){
            $this->redirect('/consultas/integral');
        }            

        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.recargas');

        if($this->Session->check('recarga_TP.recarga')){ 
            $this->data = $this->Session->read('recarga_TP.recarga');
            $usuario = $this->Session->read('usuarios.datos');

            //$nroCliente = $datosBasicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'];


            $datosCliente =  $this->Session->read('recarga_TP.datosBasicos');

            $nroCliente =  $datosCliente['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'];

            $tarjeta = trim($this->Session->read('recarga_TP.cuentaRecarga')); 

            $montoRecarga = $this->Session->read('recarga_TP.montoRecarga');
            $monto =  str_replace(".", "",$montoRecarga); 
            $montoRec =  str_replace(",", ".",$monto); 

            $debito = $this->Session->read('recarga_TP.cuentaCash');
            $comision = $this->Session->read('recarga_TP.comision');
            $montoTotal = $this->Session->read('recarga_TP.montoTotal');
            $montoRecarga = $this->Session->read('recarga_TP.montoRecarga');
            $totalRecarga = $this->Session->read('recarga_TP.montoTotal');

            $totalRecarga = str_replace('.','',$totalRecarga);
            $totalRecarga = str_replace(',','.',$totalRecarga);

            $cedula =  $this->Session->read('recarga_TP.cedulaCliente');

            $tipoTarjeta = $this->Session->read('recarga_TP.tipoTarjeta');

            $gestion = "RC";
            $motivo = "010"; 

            $cuentaDeb = $this->Session->read('recarga_TP.cuentaCash');
            $cuentaDeb = substr($cuentaDeb,10,10);

            $comision = $this->Session->read('recarga_TP.comision');


            //validar tipo de tarjeta
           

            $comision = str_replace(',','.',$comision);
            $montoRecarga = str_replace(',','.',$montoRecarga);
            $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];   

           $swCta = 0;  

           $swValidar = 0;
           $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();
           $saldo = 0;

           if(count($cuentasValidar) > 0){
              $cuentaME = array();
              foreach ($cuentasValidar as $key => $value) {
                    $valores = explode('-', $value);
                    $moneda = trim($valores[2]);
                    $tipoCta = trim($valores[1]);                
                    if($moneda=='USD' && ($tipoCta=='DEPN' || $tipoCta=='CDN1')){
                        if($debito == $valores[0]){
                            $saldo = $valores[3];
                        }
                        $swCta = 1;
                    }    
              }   
           } 

            $this->valSaldoRecarga($debito, $totRecarga, 'RT');

               $resPagoComicion = $this->Activo->invoca(
                    'IN206101',
                    array(
                        'req'=>array(
                            'IN2061CAN'=>'04',
                            'IN2061CCY'=>'USD',
                            'IN2061CDB'=>$cuentaDeb,
                            'IN2061COD'=>$tipoTarjeta,
                            'IN2061COM'=>$comision,
                            'IN2061DIP'=>$ip,
                            'IN2061MRE'=>$montoRecarga,
                            'IN2061CKN'=>0,
                            'IN2061REF'=>0,
                            'IN2061TDC'=>$tarjeta,
                            'IN2061UPD'=>'N',
                            'IN2061XXX'=>$nroCliente, 
                            'IN2061ERR' => 0,
                            'IN2061MSE' => ''
                        )   
                    )
                ); 


    
                if($resPagoComicion){
                    if($resPagoComicion['IN206101Return']['IN206101']['IN2061ERR'] == '0'){

                        $referenciaCKN =  $resPagoComicion['IN206101Return']['IN206101']['IN2061CKN'];

                        $cedula = trim($cedula);
                        $tarjeta = trim($tarjeta);
                        $montoRec = trim($montoRec);
                        $gestion = trim($gestion);
                        $motivo = trim($motivo);

                        $res = $this->Credicard->recargaTarjeta($cedula, $tarjeta, $montoRec, $gestion, $motivo);

                        $error = 0;
                        $descripcion = '';

                        if($res == 1){
                            $swValidar = 1;
                            $ruta = 'error_critico/'.$swValidar . '/RECARGA PROPIA/WS-RECARGA/101092';
                            $this->redirect($ruta);
                        }    
                        if(isset($res['RecargaSaldoResponse']['respuesta'])){
                            if($res['RecargaSaldoResponse']['respuesta']!='00'){
                                $error = 1;
                                $descripcion = _m('tcp.error.recarga') . " [". $res['RecargaSaldoResponse']['descripcion'] ." ". $res['RecargaSaldoResponse']['respuesta'] . "]";
                                $swValidar = 8;
                                $ruta = 'no_recarga/'.$swValidar. '/' .$descripcion;
                                $this->redirect($ruta);                         
                            }else{
                                //mover referencia de credicard al As400
                                $referencia = $res['RecargaSaldoResponse']['autorizacion'];

                                $resPagoRespuesta = $this->Activo->invoca(
                                    'IN206101',
                                    array(
                                        'req'=>array(
                                            'IN2061CAN'=>'04',
                                            'IN2061CCY'=>'USD',
                                            'IN2061CDB'=>$cuentaDeb,
                                            'IN2061COD'=>$tipoTarjeta,
                                            'IN2061COM'=>$comision,
                                            'IN2061DIP'=>$ip,
                                            'IN2061MRE'=>$montoRecarga,
                                            'IN2061UPD'=>'U',
                                            'IN2061XXX'=>$nroCliente, 
                                            'IN2061REF'=>$referencia,
                                            'IN2061CKN'=>$referenciaCKN,
                                            'IN2061TDC'=>$tarjeta

                                        )   
                                    )
                                );


                                //Guardar nro recarga del dia y mes

                                $res = $this->TcpCantidadRecarga->consultarNroRecarga($usuario['usuario_id']);  
                                $dia = 1;
                                $mes = 1;

                                if($res){
                                    $dia = $res['cantidad_recarga_dia'] + 1;
                                    $mes = $res['cantidad_recarga_mes'] + 1;
                                    $resp =  $this->TcpCantidadRecarga->modificarRecarga($usuario['usuario_id'], $dia, $mes);   

                                    $this->set(compact('tarjeta','montoRecarga','debito','comision','montoTotal','error','descripcion'));
                           

                                }else{
                                    $data = array(
                                        'id_cliente'=>$usuario['usuario_id'],
                                        'cedula_cliente'=>$usuario['cedula'],
                                        'cantidad_recarga_dia'=>$dia,
                                        'dia_recarga'=>date('j'),
                                        'cantidad_recarga_mes'=>$mes,
                                        'mes_recarga'=>date('n'),
                                    );

                                    $resp =  $this->TcpCantidadRecarga->guardarRecarga($data);
                                }
                      
                                $etiqComision = $this->Session->read('recarga_TP.etiqComisionRecarga');

                                $descripcion = "Recarga Mismo Titular en Activo";
                                $this->logBitacora('101093',$debito, $tarjeta, $monto,NULL,$descripcion,NULL);

                                $this->Session->write('recarga_TP.swResultado',1);

                                
                                $this->Session->write('recarga_TP.error',$error); 
                                $this->Session->write('recarga_TP.descripcion',$descripcion);
                                $this->Session->write('recarga_TP.etiqComision',$etiqComision);
                                 
                                $this->Session->write('recarga_TP.referenciaCKN',$referenciaCKN); 

                                

                                $ruta = 'recarga_resultado_vta';
                                $this->redirect($ruta);
                                

                                //$this->set(compact('tarjeta','montoRecarga','debito','comision','montoTotal','error','descripcion','etiqComision'));
                            }
                        }else{
                            $swValidar = 1;
                            $ruta = 'error_critico/'.$swValidar . '/RECARGA PROPIA/WS-RECARGA/101092';
                            $this->redirect($ruta);
                        } 


                    }else{
                            $swValidar = 2;
                            $ruta = 'error_critico/'.$swValidar  . '/RECARGA AS400/101092';
                            $this->redirect($ruta);
                    }    
                }else{                      
                    $swValidar = 2;
                    $ruta = 'error_critico/'.$swValidar  . '/RECARGA AS400/101092';
                    $this->redirect($ruta);
                }
        }
        else{
            $this->redirect('recarga');            
        }
    }    

    function recarga_resultado_vta(){

        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.recargas');
        $tarjeta = trim($this->Session->read('recarga_TP.cuentaRecarga'));
        $montoRecarga = $this->Session->read('recarga_TP.montoRecarga');
        $debito =  $this->Session->read('recarga_TP.debito');
        $comision = $this->Session->read('recarga_TP.comision');
        $montoTotal = $this->Session->read('recarga_TP.montoTotal');
        $error = $this->Session->read('recarga_TP.error');
        $descripcion = $this->Session->read('recarga_TP.descripcion');
        $etiqComision = $this->Session->read('recarga_TP.etiqComision');
        $referencia = $this->Session->read('recarga_TP.referenciaCKN');
        $this->set(compact('tarjeta','montoRecarga','debito','comision','montoTotal','error','descripcion','etiqComision','referencia'));
    }

/******************************************************************
  function no_recarga      
  Permite desplegar la pantalla de errores ocurridos durante 
  la recarga de las tarjetas mismo cliente
******************************************************************/

    function no_recarga($swValidar, $respuesta=null){
        $this->layout = 'interna';
        $mensaje = "";
        $proceso = 'RECARGA PROPIA';
        $servicio = 'AS400';
        $idTransaccion = 101092;
        $swBitacora = 0;
        switch ($swValidar) {
          case 1:
              $mensaje = _m('tcp.nosolicitada.recarga');
              $servicio = 'WS-CONSULTACUENTA';
              break;  
          case 2:
              $mensaje = _m('tcp.norecarga');
              $mensaje = $mensaje . " [" . $respuesta . "]";
              $servicio = 'WS-CONSULTACUENTA';
              break;  
          case 3:
              $mensaje = _m('tcp.sin.cta.usd');
              break;  
           case 4:
              $mensaje = _m('tcp.no.saldo.comision');
              $swBitacora = 1;
              break;
           case 6:
              $mensaje = _m('tcp.rec.nro.dia',$respuesta);
              $servicio = 'BD-CANALES';
              break;
           case 7:
              $mensaje = _m('tcp.rec.nro.mes',$respuesta);
              $servicio = 'BD-CANALES';
              break;
           case 8:
              $mensaje = $respuesta;
              $swBitacora = 1;
              $servicio = 'WS-RECARGA';
              break;    
           case 9:
              $mensaje = $respuesta;
              $servicio = 'NAVEGADOR';
              break;   
           case 10:
              $mensaje = _m('tcp.saldo.recarga');
              break; 
        }      

        $descripcion =  $mensaje; 

        //este codigo esta desactivado hasta que arreglen el problema de crear
        //tablas en la BAse de datos
        //$this->registrar_log_error($proceso, $servicio, $descripcion); 

        $descripcionLog = $mensaje . "/" . $proceso . "/" . $servicio; 
        $this->logBitacora($idTransaccion,NULL, NULL,NULL,NULL,$descripcionLog,NULL);  

        //log transaccional
/*        if($swBitacora == 1){
            $this->logBitacora('101092',NULL, NULL,NULL,NULL,$descripcion,NULL);
        }         
*/        
        $this->set(compact('descripcion'));

    }
    ///////////////////////////////////////////////////////////////////
    // FIN Recargas Propias                                         //  
    ///////////////////////////////////////////////////////////////////   


    ///////////////////////////////////////////////////////////////////
    // Recargas Tercero                                             //  
    //////////////////////////////////////////////////////////////////   
    ///////////////////////////////////////////////////////////////////
    // Recargas Tercero                                             //  
    //////////////////////////////////////////////////////////////////   

    //Recarga tercero / tarjeta prepagada
    function recarga_tercero($volver=null){   
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(102099)) $this->redirect('/consultas/integral');
   
        /* Para validar el acceso al modulo */
        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        //$this->set('seccion_actual', 'prepagas.recargasT');
        $this->set('seccion_actual', 'prepagas.recargaterctpc');
        

        if(!empty($this->data['Recarga'])){
            $this->set('recargas_terceroTP');
            $this->Session->write('recargas_tercerosTP.ctaCashDebitar', $this->data['Recarga']['cuentaDebitar']);
            $this->Session->write('recargas_tercerosTP.tipoDestinatario', $this->data['Recarga']['tipoDestinatario']);

            if ($this->data['Recarga']['tipoDestinatario'] == 'a') {
                $this->redirect('recargatercero');
            } else {
                $this->redirect('recargatercnoafiliado');
            }
        }

        $this->Session->delete('recargas_tercerosTP');

        //Obtener datos basicos del usuario
        $usuario = $this->Session->read('usuarios.datos'); 
        $cedulaOrig =  $usuario['cedula'];
        $nro_documento = desencriptar($usuario['cedula']); 
        $tipo_documento = $usuario['tipo_cedula'];
        $cedulaCliente = str_pad($nro_documento, 12, "0", STR_PAD_LEFT);
        $datos_basicos = $this->datosBasicosUsuarios($tipo_documento, $cedulaCliente);
        $this->Session->write('recargas_tercerosTP.datosBasicos', $datos_basicos);
        //FIN Obtener datos basicos del usuario  
        






        
        $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();
        $swCta = 0;  
        if(count($cuentasValidar) > 0){
            $cuentaME = array();
            foreach ($cuentasValidar as $key => $value) {
                $valores = explode('-', $value);
                $moneda = trim($valores[2]);
                $tipoCta = trim($valores[1]);                
                if(empty($valores[3])){
                    $disponible = $valores[4];
                }else{
                    $disponible = $valores[3];
                }
                if($moneda=='USD' && ($tipoCta=='DEPN' || $tipoCta=='CDN1')){
                    $swCta = 1;
                    $disponible = formatea_monto_fromWs($disponible);
                    $item = substr($valores[0],0,4) .'****'.substr($valores[0],-4) . " - " . $valores[2] . " - " . $disponible; 
                    $cuentaME[$valores[0]] = $item;  
                }    
            }   
        }   
        if($swCta == 0){
            $swValidar = 3;
            $ruta = $ruta = 'no_recarga/'.$swValidar;
            $this->redirect($ruta);
        }
        $this->set(compact('cuentaME'));
    }




    function recargatercero(){
       if(!validarTransaccion(102099)) $this->redirect('/consultas/integral');

        $usuario = $this->Session->read('usuarios.datos'); 
        $this->layout = 'interna';
        $this->set('seccion_actual', 'prepagas.recargaterctpc');


        if(!empty($this->data['Recarga'])){
            $comision = $this->data['Recarga']['totalComision'];
            if($comision == '0,00'){
                // Si la conision es cero quiere decir que no se realizo en cambio con javascript
                // Porque esta utilizando como navegador explorer y activo en linea no trabajando
                //con este navegador
                $swValidar = 9;
                $descripcionStatus = _m('tcp.no.java');
                $ruta = 'no_recarga/'.$swValidar. '/' .$descripcionStatus;
                $this->redirect($ruta);                     
            }

            $this->Session->read('recargas_tercerosTP.ctaCashDebitar', $this->data['Recarga']['cuentaDebitar']);

             
            //validar saldo  
            $ctaDebito = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');
            $totalRecarga = $this->data['Recarga']['totalRecarga'];

            //Validar saldo para realizar la recarga 
            $this->valSaldoRecarga($ctaDebito, $totalRecarga, 'RT');

            $cedula = $this->data['Recarga']['cedula'];

            //Formatear nro de tarjeta para obtenet el nro de la trajeta y el tipo tarjeta (F=fisica, D=virtual) 
            $numTarjeta = $this->data['Recarga']['tarjetatpc'];
            $tipoTarjeta = substr($this->data['Recarga']['tarjetatpc'],16,1);
            $numTarjeta = substr($this->data['Recarga']['tarjetatpc'],0,16);



            $emailB = $this->data['Recarga']['email'];
            $arrayCedula = explode('-',$cedula);
            $cedBeneficiario = $arrayCedula[1];
            $nombreBeneficiario = $this->data['Recarga']['nombre'];
            $nroDocumento = str_pad($cedBeneficiario,12,'0',STR_PAD_LEFT);  

            if($tipoTarjeta == 'F'){
                $tipoTarjeta = 'RTPT';
                $this->Session->write('recargas_tercerosTP.etiqComisionRecarga', $this->Session->read('recarga_TP.etiqComisionRecargaF'));
            }else{
                $tipoTarjeta = 'RTPV';
                $this->Session->write('recargas_tercerosTP.etiqComisionRecarga', $this->Session->read('recarga_TP.etiqComisionRecargaD'));
            }


            $datoTarjeta = $this->validarClienteCredicard($nroDocumento,$numTarjeta,2,101097);
            //Fin Verificar que la cedula del Beneficiario posea tarjeta Prepaga cash 

            //obtiene en numero de la tarjeta del beneficiario 
            $arrayDatoTarjeta = explode('/', $datoTarjeta);
            $nroTarjeta = $arrayDatoTarjeta[0];
            $nroCuenta = $arrayDatoTarjeta[1];





            $this->Session->write('recargas_tercerosTP.tipoT',$tipoTarjeta);

            $this->Session->write('recargas_tercerosTP.tarjetaB',$nroTarjeta );
            $this->Session->write('recargas_tercerosTP.nroCuentaB', $nroCuenta);
                                      
            $this->Session->write('recargas_tercerosTP.tipoOperacion', '1');
            $this->Session->write('recargas_tercerosTP.cuentaRecarga', $nroTarjeta);

            $this->Session->write('recargas_tercerosTP.email', $this->data['Recarga']['email']);


            $this->Session->write('recargas_tercerosTP.montoRecarga', $this->data['Recarga']['montoRecarga']);

            $this->Session->write('recargas_tercerosTP.totalComision', $comision);

            $this->Session->write('recargas_tercerosTP.totalRecarga', $totalRecarga);

                                   
            $this->Session->write('recargas_tercerosTP.cedulaTercero', $cedBeneficiario);

            $this->Session->write('recargas_tercerosTP.cedulaTerceroNac', $cedula);

            $this->Session->write('recargas_tercerosTP.nombreBeneficiario', $nombreBeneficiario);
            $this->Session->write('recargas_tercerosTP.afiliar', 'n');            
            $this->redirect('recargatertpc_confirmacion');
        }   




        $cuentaME =  $this->Session->read('recargas_tercerosTP.ctaCashDebitar');

        //llenar listado de Beneficiario
        $afiliados = $this->Afiliacion->obtenerListaAfiliados($usuario['id'], 'h', 'ht');


        //Capturar la comision de AS400 Fisica 
        /*******************************************
        /* Para validar el acceso al modulo */
        /*****************************************/
        $resComision = $this->Activo->invoca(
            'IN206001',
            array(
                'req'=>array(
                    'IN2060COD'=> 'RTPT'
                )   
            )
        );
        
        if($resComision == false){
            $swValidar = 2;
            $ruta = 'error_critico/'.$swValidar . '/RECARGA TERCERO/101093';
            $this->redirect($ruta);            
        }



        $comisionTCPF = $resComision['IN206001Return']['IN206001']['IN2060COM'];
        $etiqComisionF = '';
        $arrayCom = explode('.', $comisionTCPF);
        if(isset($arrayCom[1])){
            if(strlen($arrayCom[1])==1){
                $etiqComisionF = $arrayCom[0] . "," . $arrayCom[1] . "0";
            }else{
                $etiqComisionF = $arrayCom[0] . "," . $arrayCom[1];    
            }
        }else{
            $etiqComisionF = $arrayCom[0] . ",00"; 
        } 


        //Capturar la comision de AS400 Virtual
        /*******************************************
        /* Para validar el acceso al modulo */
        /*****************************************/
        $resComisionD = $this->Activo->invoca(
            'IN206001',
            array(
                'req'=>array(
                    'IN2060COD'=> 'RTPV'
                )   
            )
        );

        if($resComisionD == false){
            $swValidar = 2;
            $ruta = 'error_critico/'.$swValidar . '/RECARGA MISMO TITULAR/101093';
            $this->redirect($ruta);            
        }
        $comisionTCPD = $resComisionD['IN206001Return']['IN206001']['IN2060COM'];
        $etiqComisionD = '';
        $arrayCom = explode('.', $comisionTCPD);
        if(isset($arrayCom[1])){
            if(strlen($arrayCom[1])==1){
                $etiqComisionD = $arrayCom[0] . "," . $arrayCom[1] . "0";
            }else{
                $etiqComisionD = $arrayCom[0] . "," . $arrayCom[1];    
            }
        }else{
            $etiqComisionD = $arrayCom[0] . ",00"; 
        } 

        $this->Session->write('recarga_TP.comisionRecargaF',$comisionTCPF); 
        $this->Session->write('recarga_TP.etiqComisionRecargaF',$etiqComisionF);    

        $this->Session->write('recarga_TP.comisionRecargaD',$comisionTCPD); 
        $this->Session->write('recarga_TP.etiqComisionRecargaD',$etiqComisionD);    




        $recargaMinima = _p('tcp_recarga_min'); 
        $recargaMaxima = _p('tcp_recarga_max'); 
        $arrayRecargas = $this->Session->read('recargas_tercerosTP');
        $cuentaDebitar = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');
        $this->set(compact('afiliados', 'tipo','recargaMinima','comisionTCPF','etiqComisionF','cuentaME','recargaMaxima','comisionTCPD','etiqComisionD'));
        $this->layout = 'interna';
    }

    function recargatertpc_confirmacion(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(102099)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */

            $this->layout = 'interna';
            $this->set('seccion_actual', 'prepagas.recargaterctpc');
           
            if($this->Session->check('recargas_tercerosTP')){

                $usuario = $this->Session->read('usuarios.datos');
                $tarjeta = $this->Session->read('recargas_tercerosTP.tarjetaB');


                $cedulaClienteB = $this->Session->read('recargas_tercerosTP.cedulaTerceroNac');
                $nombreB = $this->Session->read('recargas_tercerosTP.nombreBeneficiario'); 

                $debito = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');
          

                $comision = $this->Session->read('recargas_tercerosTP.totalComision');

                $montoTotal = $this->Session->read('recargas_tercerosTP.totalRecarga');

                $montoRecarga = $this->Session->read('recargas_tercerosTP.montoRecarga');

                $email = $this->Session->read('recargas_tercerosTP.email');

                $etiqComision = $this->Session->read('recargas_tercerosTP.etiqComisionRecarga');

                $this->set(compact('montoRecarga','debito','comision','montoTotal','etiqComision','cedulaClienteB','nombreB', 'tarjeta','email')); 
            }else{
                $this->redirect('recargatertpc');
            }    
    }


     //***********************************************************
     //  REsultado de los diferentes Recargas de tercero
     //***********************************************************
    function recargatertpc_resultado(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(102099)) $this->redirect('/consultas/integral');

        /* Para validar el acceso al modulo */
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.recargaterctpc');

       //******************************************************//
       // Validar codigo otp                                   //
       //******************************************************//
      $validacion_otp = $this->Session->read('seguridad.validar_otp');
        if (
                (
                $validacion_otp == null ||
                !isset($validacion_otp['validado']) ||
                !$validacion_otp['validado']
                )
        ) {

            $url_volver = '/tarjeta_prepagadas/recargatertpc_confirmacion';
            $pasos = array(
                            'Solicitar informaci&oacute;n beneficiario',
                            'Recargar monto',
                            'Confirmaci&oacute;n',
                            'C&oacute;digo de Autorizaci&oacute;n',
                            'Resultado de Operaci&oacute;n'
                        );
            

            $this->Session->write(
                'seguridad.validar_otp', array(
                'url_aceptar' => '/tarjeta_prepagadas/recargatertpc_resultado',
                'url_volver' => $url_volver,
                'layout' => 'interna',
                'titulo' => 'Recarga Tercero / Tarjeta Prepago CASH',
                'pasos' => $pasos,
                'paso_actual' => 3,
                'seccion_actual' => 'prepagas.recargaterctpc'
                    )
            );
            $this->redirect('/seguridad/genera_otp');
        }

        
        

       //******************************************************//
       // Fin Validar codigo otp                               //
       //******************************************************//

        if($this->Session->check('recargas_tercerosTP')){ 
            $usuario = $this->Session->read('usuarios.datos');
            $montoRecarga = $this->Session->read('recargas_tercerosTP.montoRecarga');
            $monto =  str_replace(".", "",$montoRecarga); 
            $montoRec =  str_replace(",", ".",$monto);  

            $tipoDocumento = $this->Session->read('recargas_tercerosTP.tipoCedulaB');
            $documentoT =  $this->Session->read('recargas_tercerosTP.cedulaTercero');
            $nroDocumento = str_pad($documentoT,12,'0',STR_PAD_LEFT);
            $nroTarjeta = trim($this->Session->read('recargas_tercerosTP.tarjetaB')); 


            $datosBasicos = $this->Session->read('recargas_tercerosTP.datosBasicos');

            $nroCliente = $datosBasicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'];
            
            $ctaTarjeta = trim($this->Session->read('recargas_tercerosTP.nroCuentaB')); 
            $nombreB = $this->Session->read('recargas_tercerosTP.nombreBeneficiario');
            $emailB = $this->Session->read('recargas_tercerosTP.email');

            $tipoT = $this->Session->read('recargas_tercerosTP.tipoT');

            $tipoOperacion = $this->Session->read('recargas_tercerosTP.tipoOperacion');
            //$ctaTarjeta = $this->Session->read('recargaTerc_TP.nroCuenta');
            $debito = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');

            $montoRecarga = $this->Session->read('recargas_tercerosTP.montoRecarga');
            $comision = $this->Session->read('recargas_tercerosTP.totalComision');
            $totalRecarga = $this->Session->read('recargas_tercerosTP.totalRecarga');

            $totRecarga = $totalRecarga;

            $totalRecarga = str_replace('.','',$totalRecarga);
            $totalRecarga = str_replace(',','.',$totalRecarga);
            $cedulaClienteB = $this->Session->read('recargas_tercerosTP.cedulaTercero');

            $cedulaClienteBNac = $this->Session->read('recargas_tercerosTP.cedulaTerceroNac');

            $descripcionPago = 'RECARGA TERC. ACTIVO TPC ' . enmascara($nroTarjeta);
 
            //$arrayCedCteB = explode('-', $cedulaClienteB);
            $cedCteB = $cedulaClienteB;

            $gestion = "RC";
            $motivo = "010"; 
            $cuentaDeb = substr($debito,10,10);
            $comision = str_replace(',','.',$comision);
            $montoRecarga = str_replace(',','.',$montoRecarga);
            $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];   
            $swCta = 0;  
            $swValidar = 0;
            $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();
            $etiqComision = $this->Session->read('recargas_tercerosTP.etiqComisionRecarga');

            //Validar saldo para realizar la recarga 

            $this->valSaldoRecarga($debito, $totRecarga, 'RT');


            
            $resPagoComicion = $this->Activo->invoca(
                'IN206101',
                array(
                    'req'=>array(
                        'IN2061CAN'=>'04',
                        'IN2061CCY'=>'USD',
                        'IN2061CDB'=>$cuentaDeb,
                        'IN2061COD'=>$tipoT,
                        'IN2061COM'=>$comision,
                        'IN2061DIP'=>$ip,
                        'IN2061MRE'=>$montoRecarga,
                        'IN2061UPD'=>'N',
                        'IN2061XXX'=>$nroCliente,
                        'IN2061CKN'=>0,
                        'IN2061REF'=>0,
                        'IN2061TDC'=>$nroTarjeta,
                        'IN2061ERR' => 0,
                        'IN2061MSE' => ''                        
                    )   
                )
            );

            if($resPagoComicion){
                if($resPagoComicion['IN206101Return']['IN206101']['IN2061ERR'] == '0'){
                    $referenciaCKN =  $resPagoComicion['IN206101Return']['IN206101']['IN2061CKN'];                    

                    $res = $this->Credicard->recargaTarjeta($cedCteB, $nroTarjeta, $montoRec, $gestion, $motivo);
                        $error = 0;
                        $descripcion = '';

                    if($res == 1){
                        $swValidar = 1;
                        $ruta = 'error_critico/'. $swValidar  . '/RECARGA TERCERO/WS-RECARGA/102099';
                        $this->redirect($ruta);
                    }
                        
                    if(isset($res['RecargaSaldoResponse']['respuesta'])){
                        if($res['RecargaSaldoResponse']['respuesta']!='00'){
                            $error = 1;
                            $descripcion = _m('tcp.error.recarga') . " [". $res['RecargaSaldoResponse']['descripcion'] ." ". $res['RecargaSaldoResponse']['respuesta'] . "]";
                                $swValidar = 8;
                                $ruta = 'no_recargatercero/'.$swValidar. '/' .$descripcion;
                                $this->redirect($ruta);                         
                        }else{
                                //mover referencia de credicard al As400
                                $referencia = $res['RecargaSaldoResponse']['autorizacion'];

                                $resPagoRespuesta = $this->Activo->invoca(
                                    'IN206101',
                                    array(
                                        'req'=>array(
                                            'IN2061CAN'=>'04',
                                            'IN2061CCY'=>'USD',
                                            'IN2061CDB'=>$cuentaDeb,
                                            'IN2061COD'=>$tipoT,
                                            'IN2061COM'=>$comision,
                                            'IN2061DIP'=>$ip,
                                            'IN2061MRE'=>$montoRecarga,
                                            'IN2061UPD'=>'U',
                                            'IN2061XXX'=>$nroCliente,
                                            'IN2061REF'=>$referencia,
                                            'IN2061CKN'=>$referenciaCKN,
                                            'IN2061REF'=>0,
                                            'IN2061TDC'=>$nroTarjeta,
                                            'IN2061ERR' => 0,
                                            'IN2061MSE' => ''                                            
                                        )   
                                    )
                                );
                        }

                    }else{
                        $swValidar = 1;
                        $ruta = 'error_critico/'. $swValidar  . '/RECARGA TERCERO/WS-RECARGA/102099';
                        $this->redirect($ruta);
                    }
                }else{
                    $swValidar = 2;
                    $ruta = 'error_critico/'.$swValidar  . '/RECARGA AS400/102099';
                    $this->redirect($ruta);
                }
            }else{
                $swValidar = 2;
                $ruta = 'error_critico/'.$swValidar  . '/RECARGA AS400/102099';
                $this->redirect($ruta);
                
            }

                //Preguntar si es no afiliado, afiliar 
                $afiliar = 'n';
                
                if($this->Session->read('recargas_tercerosTP.afiliar')){    
                    if($this->Session->read('recargas_tercerosTP.afiliar')=='s'){

                        $afiliar = 's';
                        $aliasAfiliar = $this->Session->read('recargas_tercerosTP.afiliarAlias');


                        $tipoDocumentoAfiliar = $this->Session->read('recargas_tercerosTP.tipoCedulaB');

                        $nroDocumentoAfiliar = $this->Session->read('recargas_tercerosTP.cedulaTercero');

                        $nroTarjeta = $this->Session->read('recargas_tercerosTP.tarjetaAfiliacion');





                        //Afiliar Beneficiario
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                                        $usuario['id'], 'h', 'ht', $aliasAfiliar, $nroTarjeta, $nombreB, $tipoDocumentoAfiliar, $nroDocumentoAfiliar, $emailB, null, null, null, null
                        );


                        if (!$error_afiliacion) {
                            $tipo_afiliacion = 'h';
                            $tipo_accion = 'ht';
                            $nombreBeneficiario = $nombreB;
                            // Email Options
                            $this->Email->smtpOptions = Configure::read('email.smtp');
                            $this->Email->delivery = 'smtp';
                            $this->Email->from = Configure::read('email.from');
                            $this->Email->to = desencriptar($usuario['email_principal']);
                            $this->Email->subject = 'Notificacion Afiliacion';
                            $this->Email->sendAs = 'html';

                            // Informacion del correo
                            $this->Email->template = 'afiliacion_producto';

                            if($tipoT == 'RTPV'){
                                $tipo_accion = 'Recarga Tarjeta Prepago Virtual';
                            }else{
                                $tipo_accion = 'Recarga Tarjeta Prepago Cash';
                            }


                            $tipo_afiliacion = 'Terceros en Activo';
                            $nombreBeneficiario = $nombreB;
                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario','referencia'));

                            // Envio del correo
                            $this->Email->send();

                        }
                    }
                }

            //Enviar correo al emisor.
                // Email Options
                //correo receptor
                $correoTO = desencriptar($usuario['email_principal']);
                //suject del correo
                $correoSubject = 'Notificacion Recarga';
                //nombre de la plantilla
                $correoTemplate = 'tpc_tf_terc_ordenante';
             
                //variables datos del correo
                $cuentaD = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');
                $cuentaOrigen = enmascara($cuentaD);
                $nro_tarjeta = enmascara($nroTarjeta);
                $monto_recarga = $this->Session->read('recargas_tercerosTP.montoRecarga');
                $concepto = "RECARGA TPC " .enmascara($nroTarjeta);

                if($tipoT == 'RTPV'){
                    $tipo_accion = 'Recarga Tarjeta Prepago Virtual';
                }else{
                    $tipo_accion = 'Recarga Tarjeta Prepago Cash';
                }


                 

                $dataMail=array(
                    'usuario'=>$usuario,
                    'correoTO'=>$correoTO,
                    'correoSubject'=>$correoSubject,
                    'correoTemplate'=>$correoTemplate,
                    'tipo_accion'=>$tipo_accion,
                    'tipo_afiliacion'=>'Terceros en Activo',
                    'nombreBeneficiario'=>$nombreB,
                    'cuentaOrigen'=>$cuentaOrigen,
                    'nro_tarjeta'=>$nro_tarjeta,
                    'monto_recarga'=>$monto_recarga,
                    'concepto'=>$concepto,
                    //'referencia'=>$referencia
                );

                //generar correo
                $this->generarCorreo($dataMail);
            //Fin Enviar correo al emisor.


            //Enviar correo al Beneficiario.
                // Email Options
                
                $correoTO = $emailB;
                //suject del correo
                $correoSubject = 'Notificacion Recarga';
                //nombre de la plantilla
                $correoTemplate = 'tpc_tf_terc_beneficiario';

                //variables datos del correo
                $dataMail['correoTO'] = $correoTO;
                $dataMail['correoSuject'] = $correoSuject;
                $dataMail['correoTemplate'] = $correoTemplate;

                //generar correo
                $this->generarCorreo($dataMail);
            //Fin Enviar correo al beneficiario.    

            //Generar logBitacora     


            $descripcion = "Recarga de tercero Satisfactoria";
            $this->logBitacora('102099',$debito, $nroTarjeta,$monto,NULL,$descripcion,NULL);

            $tarjeta = $nroTarjeta;
            $email = $emailB;

            //arreglo para pdf
            $dataPDF = array(
                'descripcion'=>'RECARGA TERC. ACTIVO TPC',
                'ctaDebito'=>$debito,
                'nombreT'=>$usuario['nombre'],
                'nroTarjetaB'=>enmascara($nroTarjeta),
                'cedulaB'=>$cedulaClienteBNac,
                'nombreB'=>$nombreB,
                'descripcionPago'=>$descripcionPago,
                'estatus'=>'PROCESADO',
                'mtoRecarga'=>$montoRecarga,
                //'referencia'=>$referencia
            );  
            $this->Session->write('recargas_tercerosTP.dataPDF',$dataPDF);           

            $afiliar = "";

            if($this->Session->check('recargas_tercerosTP.afiliarAlias')){
                $afiliar = $this->Session->read('recargas_tercerosTP.afiliarAlias');                
            }  

            $this->Session->write('recargas_tercerosTP.tarjeta',$tarjeta);
            $this->Session->write('recargas_tercerosTP.montoRecarga',$montoRecarga);
            $this->Session->write('recargas_tercerosTP.debito',$debito);
            $this->Session->write('recargas_tercerosTP.comision',$comision);
            $this->Session->write('recargas_tercerosTP.montoTotal',$montoTotal);
            $this->Session->write('recargas_tercerosTP.etiqComision',$etiqComision);
            $this->Session->write('recargas_tercerosTP.cedulaClienteBNac',$cedulaClienteBNac);
            $this->Session->write('recargas_tercerosTP.nombreB',$nombreB);
            $this->Session->write('recargas_tercerosTP.error',$error);
            $this->Session->write('recargas_tercerosTP.descripcion',$descripcion);
            $this->Session->write('recargas_tercerosTP.tipoOperacion',$tipoOperacion);
            $this->Session->write('recargas_tercerosTP.afiliar',$afiliar);
            $this->Session->write('recargas_tercerosTP.totalRecarga',$totalRecarga);
            $this->Session->write('recargas_tercerosTP.email',$email);

            $this->Session->write('recargas_tercerosTP.referenciaCKN',$referenciaCKN);

            $ruta = 'recargatertpc_resultado_vta';
            $this->redirect($ruta);
            
        }  //fin validación variable session
        else{
            $this->redirect('recargaterctpc');            
        }
    }    


    function recargatertpc_resultado_vta(){

        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.recargaterctpc');

        $tarjeta = trim($this->Session->read('recargas_tercerosTP.tarjeta'));
        $montoRecarga = $this->Session->read('recargas_tercerosTP.montoRecarga');
        $debito =  $this->Session->read('recargas_tercerosTP.debito');
        $comision = $this->Session->read('recargas_tercerosTP.comision');
        $montoTotal = $this->Session->read('recargas_tercerosTP.montoTotal');
        $etiqComision = $this->Session->write('recargas_tercerosTP.etiqComision');
        $cedulaClienteBNac = $this->Session->read('recargas_tercerosTP.cedulaClienteBNac');
        $nombreB = $this->Session->read('recargas_tercerosTP.nombreB');
        $error = $this->Session->read('recargas_tercerosTP.error');
        $descripcion = $this->Session->read('recargas_tercerosTP.descripcion');
        $tipoOperacion = $this->Session->read('recargas_tercerosTP.tipoOperacion');
        $afiliar = $this->Session->read('recargas_tercerosTP.afiliar');        
        $totalRecarga = $this->Session->read('recargas_tercerosTP.totalRecarga');
        $email = $this->Session->read('recargas_tercerosTP.email'); 

        $referencia = $this->Session->read('recargas_tercerosTP.referenciaCKN');

        $this->set(compact('tarjeta','montoRecarga','debito','comision','montoTotal','etiqComision','cedulaClienteBNac','nombreB','error','descripcion', 'tipoOperacion', 'afiliar', 'totalRecarga','email','referencia'));
    }    



    function recargatercnoafiliado(){
        if(!validarTransaccion(102099)) $this->redirect('/consultas/integral');  
        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';
        $this->set('seccion_actual', 'prepagas.recargaterctpc');

        $swValidarAlias = 0;
        
        //comienzo validar formulario 
        if(!empty($this->data['Recargatercna'])){
            $comision = $this->data['Recargatercna']['totalComision'];
            
            if($comision == '0,00'){
                // Si la conision es cero quiere decir que no se realizo en cambio con javascript
                // Porque esta utilizando como navegador explorer y activo en linea no trabajando
                //con este navegador
                $swValidar = 9;
                $descripcionStatus = _m('tcp.no.java');
                $ruta = 'no_recarga/'.$swValidar. '/' .$descripcionStatus;
                $this->redirect($ruta);                     
            } 


            $tarjeta = $this->data['Recargatercna']['tarjeta'];

            $tarjetaAfiliado = $tarjeta;
            $tarjeta = substr($tarjeta,0,16);


            $usuarioT = $this->Session->read('usuarios.datos');
            $cedula = $this->data['Recargatercna']['nroCedula'];

            $swErrorCedula = 0;  
            $swValidarCedula = 0;
            $errorCedula = '';

            //validar saldo  
            $ctaDebito = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');
            $totalRecarga = $this->data['Recargatercna']['totalRecarga'];
            //Validar saldo para realizar la recarga 
            $this->valSaldoRecarga($ctaDebito, $totalRecarga, 'RT');
            //Fin Validar saldo para realizar la recarga

            $tipoDocumento = $this->data['Recargatercna']['tipoCedula'];
            $cedulaNac = trim($tipoDocumento) . "-" . trim($cedula);
            //$ctaDebito = $this->data['Recargatercna'][''];
            $documentoTitular= desencriptar($usuarioT['cedula']);
           
            $emailB = $this->data['Recargatercna']['email'];

            $usuario = $this->Session->read('usuarios.datos');

            $cedulaCliente = desencriptar($usuario['cedula']);  

            if($this->ValTipoTarjeta($tarjeta, $cedula) == 'F'){
                 $this->Session->write('recargas_tercerosTP.comisionRecarga',$this->Session->read('recargas_tercerosTP.comisionRecargaF')); 
                 $this->Session->write('recargas_tercerosTP.etiqComisionRecarga',$this->Session->read('recargas_tercerosTP.etiqComisionRecargaF'));                 
                 $tipoTarjeta = 'RTPT';
            }else{
                 $this->Session->write('recargas_tercerosTP.comisionRecarga',$this->Session->read('recargas_tercerosTP.comisionRecargaD')); 
                 $this->Session->write('recargas_tercerosTP.etiqComisionRecarga',$this->Session->read('recargas_tercerosTP.etiqComisionRecargaD'));                 
                 $tipoTarjeta = 'RTPV';
             }            

            $this->Session->write('recargas_tercerosTP.tipoT', $tipoTarjeta);


            //Verifica que la cedula del beneficiario no sea la misma del titular.   
            if($cedula == $documentoTitular){
                $swErrorCedula = 1;  
                $swValidarCedula = 1;
                $errorCedula = _m('tcp.rec_ter_ced_igual');
            }
            //Fin validación cedula del beneficiario no sea la misma del titular 
            //validar cedula en el core
            if($swValidarCedula == 0){ 
               $nroDocumento = str_pad($cedula,12,'0',STR_PAD_LEFT);    
            }   


            //Consulta basica del Beneficiario         
            $datos_beneficiario = $this->Activo->invoca(
                'NIBPF3010',
                array(
                    'req'=>array(
                        'NACIONALIDAD'=>$tipoDocumento,
                        'NROIDENTIFICACION'=>str_pad($nroDocumento,12,'0',STR_PAD_LEFT),
                        'NROPARAM'=>'1'
                    )
                )
            ); 

            //fin validacion cedula en el core

               //Verificar que la cedula del Beneficiario posea tarjeta Prepaga cash


               $swErrorAlias = 0;                 
               $swValidarAlias = 0;
               $nombreB="";

               if($this->data['Recargatercna']['afiliar'] == 's'){

                    $this->Session->write('recargas_tercerosTP.afiliar', 's');
                    $this->Session->write('recargas_tercerosTP.afiliarAlias',$this->data['Recargatercna']['alias']);
                    $this->Session->write('recargas_tercerosTP.tarjetaAfiliacion',$tarjetaAfiliado);
                    


                    //VAlidar que el alias no se encuentre repetido 
                    if($this->Afiliacion->checkAlias($usuarioT['id'],$this->data['Recargatercna']['alias'])){
                       $swErrorAlias = 1;
                       $errorAlias = _m('alias.yaregistrado');
                       $comisionTCP = $this->Session->read('recargas_tercerosTP.comisionRecarga');
                       $etiqComision = $this->Session->read('recargas_tercerosTP.etiqComisionRecarga');
                       $cuentaME = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');
                       $nacionalidad = $tipoDocumento;
                       $nroCedula = $cedula; 
                       $montoRecarga = $this->data['Recargatercna']['montoRecarga'];
                       $totalComision = $this->data['Recargatercna']['totalComision'];
                       $totalRecarga = $this->data['Recargatercna']['totalRecarga'];
                       $alias = $this->data['Recargatercna']['alias'];
                       $email = $this->data['Recargatercna']['email'];
                       $afiliar = 's';
                       $swValidarAlias = 1;
                       $this->set(compact('comisionTCP', 'etiqComision', 'cuentaME', 'nacionalidad', 'nroCedula', 'totalComision','swErrorAlias','errorAlias', 'alias', 'afiliar','montoRecarga','totalRecarga','email'));
                    }
                    
                    if($swValidarCedula == 0) {

                        if($this->Afiliacion->checkCedulaTarjeta($usuarioT['id'],$cedula,'h',$tarjeta)){
                               $swErrorCedula = 3;  
                               $swValidarCedula = 1;
                               $errorCedula = _m('afi.tpc.existente');
                        }
                    }


                    $apellido = trim($datosBas['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO']);
                    if(empty($apellido)){
                        $nombreB = $this->data['Recargatercna']['nombre'];
                    }else{  
                        $nombre = trim($datosBas['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'])." " .trim($datosBas['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO'])." " .trim($datosBas['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'])." " .trim($datosBas['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE']);
                    }    

                    $this->data['Recargatercna']['nombre'] = $nombreB; 


                    //if($this->Afiliacion->checkCedula($usuarioT['id'],$cedula,'h')){

                
               }else{
                    $afiliar = 'n';
                   $this->Session->write('recargas_tercerosTP.afiliar', 'n');
               }





                if($swValidarCedula == 1){
                   $comisionTCP = $this->Session->read('recargas_tercerosTP.comisionRecarga');
                   $etiqComision = $this->Session->read('recargas_tercerosTP.etiqComisionRecarga');


                   



                   $cuentaME = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');
                   $nacionalidad = $tipoDocumento;
                   $nroCedula = $cedula; 
                   $montoRecarga = $this->data['Recargatercna']['montoRecarga'];
                   $totalComision = $this->data['Recargatercna']['totalComision'];
                   $totalRecarga = $this->data['Recargatercna']['totalRecarga'];

                   $alias = $this->data['Recargatercna']['alias'];
                   $email = $this->data['Recargatercna']['email'];
                   $nombreB = $this->data['Recargatercna']['nombre'];


                   

                   $this->set(compact('comisionTCP', 'etiqComision', 'cuentaME', 'nacionalidad', 'nroCedula', 'totalComision','swErrorCedula','errorCedula', 'alias', 'afiliar','montoRecarga','totalRecarga','email','nombreB'));
                } 






   

               //Si el alias es correcto sigue el flujo del proceso
               if($swValidarAlias == 0 && $swValidarCedula == 0){


                    if(empty($nombreB)){
                        $nombreBeneficiario = $this->nombreCliente($datos_beneficiario, 1);
                        if(empty($nombreBeneficiario)){
                            $nombreBeneficiario = "RECARGA A TERCERO";
                        }
                    }else{
                        $nombreBeneficiario = $nombreB;
                    } 


                


                   $datoTarjeta = $this->validarClienteCredicard($nroDocumento,$tarjeta, 2,102099);
                  

                   //obtiene en numero de la tarjeta del beneficiario 
                   $arrayDatoTarjeta = explode('/', $datoTarjeta);
                 

                   $nroTarjeta = $arrayDatoTarjeta[0];
                   $nroCuenta = $arrayDatoTarjeta[1];
                   //Fin obtiene en numero de la tarjeta del beneficiario

              
                   //Obtiene el nombre del usuario 1 = Nombres, apellidos
    
                   $correoB = $emailB;

                   $this->Session->write('recargas_tercerosTP.tipoOperacion', '2');  
                   $this->Session->write('recargas_tercerosTP.nombreBeneficiario', $nombreBeneficiario);

                   $this->Session->write('recargas_tercerosTP.email',$correoB); 

                   $this->Session->write('recargas_tercerosTP.correoB', $correoB);
                   $this->Session->write('recargas_tercerosTP.tipoCedulaB',$tipoDocumento);
                   $this->Session->write('recargas_tercerosTP.cedulaTercero',$cedula);
                   $this->Session->write('recargas_tercerosTP.cedulaTerceroNac',$cedulaNac);

                   $this->Session->write('recargas_tercerosTP.tarjetaB',$nroTarjeta);


                   $this->Session->write('recargas_tercerosTP.nroCuentaB',$nroCuenta);

                   
                   //Obtener datos Recarga
                   $totalComision = $this->data['Recargatercna']['totalComision'];
                   $this->Session->write('recargas_tercerosTP.montoRecarga', $this->data['Recargatercna']['montoRecarga']);
                   $this->Session->write('recargas_tercerosTP.totalComision', $this->data['Recargatercna']['totalComision']);
                   $this->Session->write('recargas_tercerosTP.totalRecarga', $this->data['Recargatercna']['totalRecarga']);
                   $this->redirect('recargatercnoafiliadoconfirmacion');
               }  //Fin  el alias es correcto sogue el flujo del proceso      
        }  //fin validación del formulario

        if($swValidarAlias == 0 && $swValidarCedula == 0){ 

            

        //Capturar la comision de AS400 Fisica 
        /*******************************************
        /* Para validar el acceso al modulo */
        /*****************************************/
        $resComision = $this->Activo->invoca(
            'IN206001',
            array(
                'req'=>array(
                    'IN2060COD'=> 'RTPT'
                )   
            )
        );
        
        if($resComision == false){
            $swValidar = 2;
            $ruta = 'error_critico/'.$swValidar . '/RECARGA MISMO TITULAR/101093';
            $this->redirect($ruta);            
        }
        $comisionTCPF = $resComision['IN206001Return']['IN206001']['IN2060COM'];
        $etiqComisionF = '';
        $arrayCom = explode('.', $comisionTCPF);
        if(isset($arrayCom[1])){
            if(strlen($arrayCom[1])==1){
                $etiqComisionF = $arrayCom[0] . "," . $arrayCom[1] . "0";
            }else{
                $etiqComisionF = $arrayCom[0] . "," . $arrayCom[1];    
            }
        }else{
            $etiqComisionF = $arrayCom[0] . ",00"; 
        } 


        //Capturar la comision de AS400 Virtual
        /*******************************************
        /* Para validar el acceso al modulo */
        /*****************************************/
        $resComisionD = $this->Activo->invoca(
            'IN206001',
            array(
                'req'=>array(
                    'IN2060COD'=> 'RTPV'
                )   
            )
        );

        if($resComisionD == false){
            $swValidar = 2;
            $ruta = 'error_critico/'.$swValidar . '/RECARGA MISMO TITULAR/101093';
            $this->redirect($ruta);            
        }
        $comisionTCPD = $resComisionD['IN206001Return']['IN206001']['IN2060COM'];
        $etiqComisionD = '';
        $arrayCom = explode('.', $comisionTCPD);
        if(isset($arrayCom[1])){
            if(strlen($arrayCom[1])==1){
                $etiqComisionD = $arrayCom[0] . "," . $arrayCom[1] . "0";
            }else{
                $etiqComisionD = $arrayCom[0] . "," . $arrayCom[1];    
            }
        }else{
            $etiqComisionD = $arrayCom[0] . ",00"; 
        } 


        $this->Session->write('recargas_tercerosTP.comisionRecargaF',$comisionTCPF); 
        $this->Session->write('recargas_tercerosTP.etiqComisionRecargaF',$etiqComisionF);    

        $this->Session->write('recargas_tercerosTP.comisionRecargaD',$comisionTCPD); 
        $this->Session->write('recargas_tercerosTP.etiqComisionRecargaD',$etiqComisionD);         

        $cuentaME = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');

        $nacionalidad = '';
        $nroCedula = '';
        $totalComision = '';
        $totalRecarga = '';
        $montoRecarga = '';
        $afiliar = 'n';
        $alias = '';
        $swErrorAlias = 0;
        $errorAlias = '';

            $this->set(compact('comisionTCPF', 'etiqComisionF', 'cuentaME', 'nacionalidad', 'nroCedula', 'totalComision','swErrorAlias','errorAlias', 'alias', 'afiliar','montoRecarga','totalRecarga','comisionTCPD', 'etiqComisionD'));
        }
    }  


    function recargatercnoafiliadoconfirmacion(){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(102099)) $this->redirect('/consultas/integral');
                 
            /* Para validar el acceso al modulo */
            $this->layout = 'interna';
            $this->set('seccion_actual', 'prepagas.recargaterctpc');

            if($this->Session->check('recargas_tercerosTP')){
                $usuario = $this->Session->read('usuarios.datos');
                $tipoCedulaB = $this->Session->read('recargas_tercerosTP.tipoCedulaB');
                $cedulaClienteB = $tipoCedulaB . "-" . $this->Session->read('recargas_tercerosTP.cedulaTercero');
                $nombreB = $this->Session->read('recargas_tercerosTP.nombreBeneficiario'); 
                $debito = $this->Session->read('recargas_tercerosTP.ctaCashDebitar');
                $correoB = $this->Session->read('recargas_tercerosTP.correoB');

                $tarjetaB = $this->Session->read('recargas_tercerosTP.tarjetaB');

                $aliasB = '';
                if($this->Session->read('recargas_tercerosTP.afiliar')=='s'){
                    $aliasB = $this->Session->read('recargas_tercerosTP.afiliarAlias');
                }
                $montoRecarga = $this->Session->read('recargas_tercerosTP.montoRecarga');
                $comision = $this->Session->read('recargas_tercerosTP.totalComision');
                $montoTotal = $this->Session->read('recargas_tercerosTP.totalRecarga');


                $etiqComision = $this->Session->read('recargas_tercerosTP.etiqComisionRecarga');



                $this->set(compact('montoRecarga','debito','comision','montoTotal','etiqComision','cedulaClienteB','nombreB', 'aliasB','correoB','tarjetaB','nombreB' ));
            }else{
                $this->redirect('recargatertpc');
            }    
    }


    ///////////////////////////////////////////////////////////////////
    // FIN Recargas Tercero                                             //  
    //////////////////////////////////////////////////////////////////   




    /////////////////////////////////////////////////////////////////////////////
    // Funciones enericas                                                      //
    ////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////// 
    //Registrar Bitacora de la transaccion de las operaciones realizadas //
    ///////////////////////////////////////////////////////////////////////  
    function logBitacora($idTransaccion, $ctaOrigen=null, $ctaDestino=null, $monto=null, $referencia=null, $descripcion=null, $cedula=null){
        $usuario = $this->Session->read('usuarios.datos');
        $usuario_id = $usuario['id'];
        $this->Bitacora->registraBitacora(
            $usuario_id, 
            $idTransaccion,
            $ctaOrigen, 
            $ctaDestino, 
            $monto,
            $referencia,
            $descripcion,
            $cedula
        );
    } 
    //////////////////////////////////////////////////////////////////////
    //FIN Registrar Bitacora de la transaccio                           //
    ///////////////////////////////////////// //////////////////////////// 

    ////////////////////////////////////////////////////////////////////// 
    //obtener datos basicos del trabajador AS400.                       //
    //////////////////////////////////////////////////////////////////////
    function datosBasicosUsuarios($tipoDocumento, $nroDocumento){
        $datos_beneficiario = $this->Activo->invoca(
            'NIBPF3010',
            array(
                'req'=>array(
                    'NACIONALIDAD'=>$tipoDocumento,
                    'NROIDENTIFICACION'=>str_pad($nroDocumento,12,'0',STR_PAD_LEFT),
                    'NROPARAM'=>'1'
                )
            )
        ); 


        if($datos_beneficiario == false){
            $swValidar = 2;
            $ruta = 'error_critico/'.$swValidar . '/PRESOLICITUD/101090';
            $this->redirect($ruta);            
        } 
        return $datos_beneficiario;      
    }

    /////////////////////////////////////////////////////////////////////// 
    //Fin obtener datos basicos del trabajador AS400.                    //
    ///////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////// 
    //valida la edad del cliente si es mayor de edad.                   //
    //////////////////////////////////////////////////////////////////////
    function edadCliente($fechaNac, $procesoError){
        $fechaNac = trim($fechaNac);
        
        $diaN = substr($fechaNac,0,2);
        $mesN = substr($fechaNac,2,2);
        $anioN = substr($fechaNac,4,4);

        $fechaNac =  $dia . "-" . $mes . "-" . $anio;   

        $anioA = intval(date('Y'));
        $mesA  = intval(date('m'));
        $diaA  = intval(date('d'));

        $rMes  = 0;
        $rYear = 0;
        if ($diaA > $diaN) {
            $rMes = 1;
        }

        if ($mesA > $mesN) {
            $rYear = 1;

        } elseif ($mesA == $mesN) {
            if ($rMes == 1) {
                $rYear = 1;
            }
        }

        if ($diaA == $diaN and $mesA == $mesN) {
            $rYear = 1;
        }
        $edad = $anioA - $anioN + $rYear - 1;     

        if($edad < 18 ){
            if($edadNac==false){
                $swValidar = 12;
                $ruta = $procesoError . '/'.$swValidar;
                $this->redirect($ruta);              
            } 
        }else{
           return true;
        } 
    }          
    //////////////////////////////////////////////////////////////////// 
    //Fin valida la edad del cliente si es mayor de edad.             //
    ////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////// 
    //Determina si el clientes tiene cuenta moneda extranjera activa //
    ///////////////////////////////////////////////////////////////////
    function valCtaDolares(){
        $cuentasME = $this->Activo->buscarCuentasSaldosMeTipo();
        $swCuentaME = 0;
        if(count($cuentasME) > 0){
            foreach ($cuentasME as $key => $value) {
                $valores = explode('-', $value);
                $moneda = trim($valores[2]);
                $tipoCta = trim($valores[1]);                 
                if($moneda=='USD' && ($tipoCta=='DEPN' || $tipoCta=='CDN1')){
                    $valor = count($valores); 
                    $swCuentaME = 2;
                    if($valor < 5){  
                        $swCuentaME = 1;
                    }                    
                }
            }
        }else{
            $swCuentaME = 1;
        } 

        if($swCuentaME == 1){
              $swValidar = 1;
              $ruta = 'no_presolicitud/'.$swValidar;
              $this->redirect($ruta);
        }
        return true;
    }
    /////////////////////////////////////////////////////////////////// 
    //Fin validar clientes tiene cuenta moneda extranjera activa     //
    ///////////////////////////////////////////////////////////////////

    ///////////////////////////////////////////////////////////////////
    //Valida si posee cuentas en dolares y devuelve una lista con    //
    //las cuentas                                                    //
    ///////////////////////////////////////////////////////////////////
    function listaCuentaME($arrgCtas, $procesoError){
        $swCta = 0;  
        if(count($arrgCtas) > 0){
            $cuentaME = array();
            foreach ($arrgCtas as $key => $value) {
                $valores = explode('-', $value);
                $moneda = trim($valores[2]);
                $tipoCta = trim($valores[1]);
                if($moneda=='USD' && ($tipoCta=='DEPN' || $tipoCta=='CDN1')){
                    $swCta = 1;
                    $item = substr($valores[0],0,4) .'****'.substr($valores[0],-4) . " - " . $valores[2] . " - " . $valores[3]; 
                        $cuentaME[$valores[0]] = $item;  
                }   
            }   

            if(count($cuentaME) > 0){
                return $cuentaME;
            }          
        }else{
             $swValidar = 1;
        }
         
        if($swValidar > 0 || $swCta == 0){
            $swValidar = 1;
            $ruta = trim($procesoError) . '/'.$swValidar;
            $this->redirect($ruta);
        }
    }
    //////////////////////////////////////////////////////////////////
    //Fin Valida si posee cuentas en dolares y devuelve una lista   // 
    // con las cuentas                                              //
    //////////////////////////////////////////////////////////////////

    ///////////////////////////////////////////////////////////////////
    //Verifica si posee tarjeta con las condiciones de emision       //
    //aplica solo para el metodo de solicitud tanto fisica y virtual //
    ///////////////////////////////////////////////////////////////////
    function poseeTarjeta($cedulaCliente, $cedulaOrig, $tipoDocumento, $tipoTarjeta, $modulo, $pantallaError, $nroTransaccion){

        $res = $this->Credicard->consultarCuenta($cedulaCliente); 
        

        $codiEstatus = 0;
        $codTarjeta = 0;

        if($res == 1){
            $swValidar = 1;
            $ruta = 'error_critico/' . $swValidar . '/' . $modulo . '/WS-CONSULTACUENTA/' . $nroTransaccion;
            $this->redirect($ruta);
        }else{
            $swActivo = 0;
            if($tipoTarjeta == 'F'){
                $codProducto = _p('tcp_codigo_producto');
            }else{
                $codProducto = _p('tpc_codigo_producto_virtual');
            }  

            $fechaActivo = '';
            $arreglo = $res['CuentaTarjetaClienteResponse'];

            if(isset($arreglo['codAsociado'])){
                if(trim($arreglo['codigoProducto']) == trim($codProducto)){
                    //$codTarjeta = $arreglo['codigoEstatus'];
                    if($arreglo['codigoEstatus'] < 4){
                         $swActivo = 1;
                         $fechaActivo = $arreglo['fechaRegistroCuenta'];
                    }else{
                            $this->SolicitudTarjetasPrepagadas->modificarEstatusCancelada($cedulaOrig);
                    }      
                }


            }else{
                foreach ($arreglo as $valor) {
                    $codProductoWS = $valor['codigoProducto'];
                    if(trim($codProductoWS) == trim($codProducto)){
                        if($valor['codigoEstatus'] < 4){
                            $swActivo = 1;
                            $fechaActivo = $valor['fechaRegistroCuenta'];
                             break;       
                        }else{
                               $this->SolicitudTarjetasPrepagadas->modificarEstatusCancelada($cedulaOrig);  
                        } 
                    } 
                }
            }    
        } 
       

         //Validar si existe la tarjeta en la base de datos con estatus < 4     
         if($swActivo == 1){
               /*$fecha = $fechaActivo;
               $fecha = trim(substr($fecha,6,2)) . '-' . trim(substr($fecha,4,2)) . '-'  . trim(substr($fecha,0,4));
               $swValidar = 4;
               $ruta = $pantallaError . '/'. $swValidar .'/'.$fecha;
               $this->redirect($ruta);
               */
               return 1;
         }

        if($codiEstatus == 0 && $codTarjeta == 0){ 

 

            $arrayConsulta = $this->SolicitudTarjetasPrepagadas->consultarSolicitud($tipoDocumento,$cedulaOrig, $tipoTarjeta);

               if($arrayConsulta){  
                /********** PENDIENTE CON el status de la tarjeta se procede a determinar el mensaje si fue solicitada, ya aprobada o rechazada */



                if($arrayConsulta['estatus'] == 1){
                    /*$fecha = $arrayConsulta['fecha_creacion'];
                    $fecha = substr($fecha,8,2) . "-" . substr($fecha,5,2) . "-" . substr($fecha,0,4);
                    $swValidar = 4;
                    $ruta =  $pantallaError . '/'.$swValidar.'/'.$fecha;
                    $this->redirect($ruta);
                    */
                    return 1;
                }else{
                    if($codiEstatus==0){

                        if($arrayConsulta['estatus'] < 4){
                            /*$fecha = $arrayConsulta['fecha_creacion'];
                            $fecha = substr($fecha,8,2) . "-" . substr($fecha,5,2) . "-" . substr($fecha,0,4);
                            $swValidar = 4;
                            $ruta = $pantallaError . '/'.$swValidar.'/'.$fecha;
                            $this->redirect($ruta);
                            */
                            return 1;
                        }    
                    }    
                }    
             }else{
                return 0;
            } 
        } 
        return true;
    } 
    ///////////////////////////////////////////////////////////////////
    //FIN Verifica si posee tarjeta con las condiciones de emision   //
    //aplica solo para el metodo de solicitud tanto fisica y virtual //
    ///////////////////////////////////////////////////////////////////

    ///////////////////////////////////////////////////////////////////
    //  comisionFormato                                              // 
    //  Permite formatear el monto de la comision en el formato 0,00 //
    //  para mostrarlo en las vistas donde se desplegue.             //  
    ///////////////////////////////////////////////////////////////////
    function comisionFormato($comisionTCP){
        $etiqComision = '';
        $arrayCom = explode('.', $comisionTCP);
        if(isset($arrayCom[1])){
            if(strlen($arrayCom[1])==1){
                $etiqComision = $arrayCom[0] . "," . $arrayCom[1] . "0";
            }else{
                $etiqComision = $arrayCom[0] . "," . $arrayCom[1];    
            }
        }else{
            $etiqComision = $arrayCom[0] . ",00"; 
        }
        return $etiqComision;
    }
    ///////////////////////////////////////////////////////////////////
    //  FIN comisionFormato                                          // 
    //  Permite formatear el monto de la comision en el formato 0,00 //
    //  para mostrarlo en las vistas donde se desplegue.             //  
    ///////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    //Obtener y devuelve el saldo de una cuenta especifica en BS se   //
    //recibe la identificacion del cliente y la cuenta a consultar    //
    ////////////////////////////////////////////////////////////////////
    function saldoCuenta($identificacion, $cuentaSelBS){
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

        $saldo = 0.00;
        $arrayCta = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
        foreach ($arrayCta as $key => $value) {
            if($value['SUMACN'] == $cuentaSelBS){
                $saldo = $value['SUMAVB'];  
                break;  
            }
        }    

        return $saldo;
    }
    //////////////////////////////////////////////////////////////
    //FIN SALDO CUENTA BS                                       //
    //////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////
    //Valida el saldo con respecto al monto de comision o recarga //
    // de Tarjeta Prepagada                                       //
    ////////////////////////////////////////////////////////////////    
    function valSaldoComision($saldo, $comision, $tipoMoneda){
        if($comision > $saldo){
            if($tipoMoneda == 1){
                $swValidar = 20;
                $ruta = 'no_presolicitud/'.$swValidar;
                $this->redirect($ruta);  
            }else{
                $swValidar = 3;
                $ruta = 'no_presolicitud/'.$swValidar;
                $this->redirect($ruta);  
            }    
        }
    }    
    ////////////////////////////////////////////////////////////////
    //FIN Valida el saldo con respecto al monto de comision o     //
    //Recarga de Tarjeta Prepagada                                //
    //////////////////////////////////////////////////////////////// 

    ///////////////////////////////////////////////////////////////
    // Obtener datos cliente para envio a credicard Solicitud    //
    // Tarjeta Fisica o vitual                                   //
    /////////////////////////////////////////////////////////////// 
    function obtenerDatosSolicitud($datosBas){
        //ubicacion geografica (Miranda / Mun: Chacao / Ciudad: Chacao / Zonz:ZONA INDUST LOS DOS CERRITOS
        $c_estado = 15;
        $c_municipio = 7;
        $c_ciudad = 20;
        $c_zonaPostal = 1201;

        //direccion/
        $dire01 = $datosBas['NIBPF3010Return']['NIBPF3010']['DIRECCION01'];  
        $dire02 = $datosBas['NIBPF3010Return']['NIBPF3010']['DIRECCION02']; 
        $dire03 = $datosBas['NIBPF3010Return']['NIBPF3010']['DIRECCION03']; 
        $dire04 = $datosBas['NIBPF3010Return']['NIBPF3010']['DIRECCION04'];
        $direccion = trim($dire01) . "  " . trim($dire02) . "  " . trim($dire03) . "  " . trim($dire04);

        $direccion = substr($direccion,0,55);

        $c_direccion1 = $direccion;
        $c_direccion2 = $direccion;

        //tel Habitacion:
        $telHab = $datosBas['NIBPF3010Return']['NIBPF3010']['NROHAB'];
        if(empty($telHab)){
            $c_codTelHab = '0212';
            $c_telHab = '1234567';
        }else{
            $c_codTelHab = substr($telHab,0,4);
            $c_telHab = substr($telHab,4,7);
        }

        //tel Oficina:
        $telOfic = $datosBas['NIBPF3010Return']['NIBPF3010']['NROOFICINA'];
        if(empty($telOfic)){
            $c_codTelOfic = '0212';
            $c_telOfic = '1234567';
        }else{
            $c_codTelOfic = substr($telOfic,0,4);
            $c_telOfic = substr($telOfic,4,7);
        }  

        //tel Celular:
        $telCel = $datosBas['NIBPF3010Return']['NIBPF3010']['NROCELULAR'];
        if(empty($telCel)){
            $c_codTelCel = '0416';
            $c_telCel = '1234567';
        }else{
            $c_codTelCel = substr($telCel,0,4);
            $c_telCel = substr($telCel,4,7);
        }

        //nombre cliente 
        $v_nombre = trim($datosBas['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'])." " .trim($datosBas['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO']).", " .trim($datosBas['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'])." " .trim($datosBas['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE']);

        //codigo + numero telefono celular
        $v_celular = trim( $c_codTelCel)."-".trim($c_telCel); 

        $this->Session->write('tarjeta_prepagadas.c_estado',$c_estado);
        $this->Session->write('tarjeta_prepagadas.c_municipio',$c_municipio);
        $this->Session->write('tarjeta_prepagadas.c_ciudad',$c_ciudad);
        $this->Session->write('tarjeta_prepagadas.c_zonaPostal',$c_zonaPostal);
        $this->Session->write('tarjeta_prepagadas.c_direccion1',$c_direccion1);
        $this->Session->write('tarjeta_prepagadas.c_direccion2',$c_direccion2);   
        $this->Session->write('tarjeta_prepagadas.c_codTelHab',$c_codTelHab);
        $this->Session->write('tarjeta_prepagadas.c_telHab',$c_telHab); 
        $this->Session->write('tarjeta_prepagadas.c_codTelOfic',$c_codTelOfic);
        $this->Session->write('tarjeta_prepagadas.c_telocic',$c_telOfic); 
        $this->Session->write('tarjeta_prepagadas.c_codTelCel',$c_codTelCel);
        $this->Session->write('tarjeta_prepagadas.c_telCel',$c_telCel); 
        $this->Session->write('tarjeta_prepagadas.c_nombre',$v_nombre);   
    }    
    ///////////////////////////////////////////////////////////////
    // FIN Obtener datos cliente para envio a credicard          //
    // Solicitud Tarjeta Fisica o vitual                         //
    /////////////////////////////////////////////////////////////// 

    ///////////////////////////////////////////////////////////////
    // Guarda en la tabla SolicitudTarjetasPrepagadas la         //  
    // informacion que se envia a credicard para la emision      //
    ///////////////////////////////////////////////////////////////
    function guardarDatosSolicitud($tipoProducto, $usuario){
        
        $datos_basicos = $this->Session->read('tarjeta_prepagadas.datos_basicos');

        $tipoRegistro = "2";
        $codEmpresa = _p('tcp_codigo_empresa');

        $tipo_documento = $usuario['tipo_cedula']; 
        $nro_documento =  $usuario['cedula'];

        $pNombre = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE']);
        $pNombre = encriptar($pNombre); 
        $sNombre = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE']);
        if(empty($sNombre)){
            $sNombre = ".";
        }             
        $sNombre = encriptar($sNombre);
        $pApellido = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO']);
        $pApellido = encriptar($pApellido);
        $sApellido = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO']);
        if(!empty($sApellido)){
            $sApellido = encriptar($sApellido);
        } 
        

        $fNacimiento = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['FECHANAC']);
        $fNacimiento = substr($fNacimiento,4,4) . "-" . substr($fNacimiento,2,2) . "-" . substr($fNacimiento,0,2);
        $sexo = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['SEXO']);
        $edoCivil = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['ESTADOCIVIL']);
        $edoC = "";
        switch ($edoCivil) {
            case 1:
                $edoC = 'C';
                break;
            case 2:
                $edoC = 'C';
                break;
            case 3:
                $edoC = 'D';
                break;
            case 4:
                $edoC = 'S';
                break;
            case 5:
                $edoC = 'V';
                break;
        }
        $tipoPersona = 'N';
        $correo = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3']);
        $correo =  encriptar($correo);
        $lugarNacimiento = "CARACAS";  //UBICAR EN AS400
        $clienteVIP = "N";

        $codTelefResidencia = $this->Session->read('tarjeta_prepagadas.c_codTelHab');
        $TelefResidencia = $this->Session->read('tarjeta_prepagadas.c_telHab');
        $TelefResidencia = encriptar($TelefResidencia);

        $codOfic = $this->Session->read('tarjeta_prepagadas.c_codTelOfic');
        $TelefOfic = $this->Session->read('tarjeta_prepagadas.c_telofic');
        $TelefOfic = encriptar($TelefOfic);


        $direc1 = $this->Session->read('tarjeta_prepagadas.c_direccion1');
        $direc1 = trim($direc1);
        $direc1 = encriptar($direc1);
        $direc1 = trim($direc1);
        $direc2 = $this->Session->read('tarjeta_prepagadas.c_direccion2');
        $direc2 = trim($direc2);
        $direc2 = encriptar($direc2); 

        $parroquia = "000000";
        $municipio = $this->Session->read('tarjeta_prepagadas.c_municipio');
        $ciudad = $this->Session->read('tarjeta_prepagadas.c_ciudad');
        $estado = $this->Session->read('tarjeta_prepagadas.c_estado');
        $zonaP = $this->Session->read('tarjeta_prepagadas.c_zonaPostal');
        //$courier = "0000000022";  //Establecer estainformación en producto
        $courier = _p('tcp.courier');
        $courier = str_pad($courier, 10, "0", STR_PAD_LEFT);


        //$dirEntrega = "0000000008";  //Establecer estainformación en producto
        $dirEntrega = _p('tcp.dir.entrega');  //Establecer dirección entrega producto
        $dirEntrega = str_pad($dirEntrega, 10, "0", STR_PAD_LEFT);             

        $titularidad = "T";
        $usuarioRegistro = 'ACTIVO';
        $codCelular = $this->Session->read('tarjeta_prepagadas.c_codTelCel');
        $nroCelular = $this->Session->read('tarjeta_prepagadas.c_telCel');
        $nroCelular = encriptar($nroCelular);  //cambiar cuando incremente campo tel-celular en BD

        $pNacimiento = 862;  //por defecto
        $nacionalidad = 157;    //por defecto
        $nAcademico = 0;
        $profesion = 1;
        $aEconomica = 111;
        $fecMilitar = date('Y-m-d');
        $otroProducto = 'N';
        $cualesProducto = 'NINGUNO';
        $tranfExterior = 'N';
        $pTransferencia = 0;
        $fechaCreacion = date('Y-m-d');

        //Agencia donde retirara el plastico
        $c_agencia = $this->Session->read('tarjeta_prepagadas.c_agencia');
        $a_agencia =  $this->Session->read('tarjeta_prepagadas.agencias');

        $v_agencia = $a_agencia[$c_agencia];


        
        //el campo tipo de producto maneja los valores
        //F = fisica
        //D = Virtual              

        $data = array('tipo_registro'=>$tipoRegistro, 
                      'codigo_empresa'=>$codEmpresa,
                      'tipo_cedula'=>$tipo_documento,
                      'cedula'=>$nro_documento,
                      'primer_nombre' =>$pNombre,
                      'segundo_nombre' =>$sNombre,
                      'primer_apellido'=>$pApellido,
                      'segundo_apellido'=>$sApellido,
                      'fecha_nacimiento'=>$fNacimiento,
                      'sexo'=>$sexo,
                      'estado_civil'=>$edoC,
                      'tipo_persona'=>$tipoPersona,
                      'correo_electronico'=>$correo,
                      'lugar_nacimiento'=>$lugarNacimiento,
                      'cliente_vip'=>$clienteVIP,
                      'codigo_residencia'=>$codTelefResidencia,
                      'telf_residencia'=>$TelefResidencia,
                      'codigo_oficina'=>$codOfic,
                      'telf_oficina'=>$TelefOfic,
                      'direccion1'=>$direc1,
                      'direccion2'=>$direc2,
                      'parroquia'=>$parroquia,
                      'municipio'=> $municipio,
                      'ciudad'=>$ciudad,
                      'estado'=>$estado,
                      'zona_postal'=>$zonaP,
                      'currier'=>$courier,
                      'direccion_entrega'=>$dirEntrega,
                      'titularidad'=>$titularidad,
                      'usuario_registro'=>$usuarioRegistro,
                      'cod_celular' => $codCelular,
                      'tel_celular'=>$nroCelular,
                      'pais_nacimiento'=>$pNacimiento,
                      'nacionalidad'=>$nacionalidad,
                      'nivel_academico'=>$nAcademico,
                      'profesion'=>$profesion,
                      'act_economica'=>$aEconomica,
                      'fec_militar'=>$fecMilitar,
                      'otro_producto'=>$otroProducto,
                      'cuales_producto'=>$cualesProducto,
                      'transf_exterior'=>$tranfExterior,
                     'pais_transferencia'=> $pTransferencia,
                     'fecha_creacion'=>$fechaCreacion,
                     'estatus'=>1,
                     'nob_agencia'=>$v_agencia,
                     'tipo_producto'=>$tipoProducto
                    );

            $res = $this->SolicitudTarjetasPrepagadas->guardar_solicitud($data,$nro_documento,$v_agencia, $tipo_documento, $nro_documento);
    }
    ///////////////////////////////////////////////////////////////
    // FIN Guarda en la tabla SolicitudTarjetasPrepagadas la     //  
    // informacion que se envia a credicard para la emision      //
    ///////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////
    // Acción para al exportación del archivo pdf en solicitud  //
    // de la tarjeta fisica o Virtual                           //  
    //////////////////////////////////////////////////////////////
    function descargarTCP_pdf($tipo){
        if(!$this->Session->check('usuarios.datos')){
            $this->redirect('/consultas/integral');
        }
        //Cuenta de moned extranjera
        $c_cuentabs = $this->Session->read('respoeteTCP.v_cuentaME');
        $usuario = $this->Session->read('usuarios.datos');
        $data = $this->Session->read('usuarios.descarga.pdf');
        $this->set(compact('data','usuario','tipo'));
        $this->render('/elements/descargarTCP_pdf');
    }
    //////////////////////////////////////////////////////////////
    // FIN Acción para al exportación del archivo pdf en        //
    // solicitud de la tarjeta fisica o Virtual                 //  
    //////////////////////////////////////////////////////////////

    ///////////////////////////////////////////////////////////// 
    // Imprime contrato al solicitar la tarjeta Prepagada      //
    /////////////////////////////////////////////////////////////
 
    function contrato_TCP(){
        $usuario = $this->Session->read('usuarios.datos');
        //$this->set('contratoTCP',$contratoTCP);
    }
    ///////////////////////////////////////////////////////////// 
    // FIN Imprime contrato al solicitar la tarjeta Prepagada  //
    /////////////////////////////////////////////////////////////

    ///////////////////////////////////////////////////////////// 
    // Arreglo con cuentas de credicard validas                //
    /////////////////////////////////////////////////////////////
    function valConsultaTarjetas($arreglo, $titPantallaError){
        $codProducto = _p('tcp_codigo_producto');
        $codProductoVirtual = _p('tpc_codigo_producto_virtual');
        $fechaActivo = '';

        $tipoConsulta = 0;  //1 = fisica, 2 = Virtual, 3 = ambas
        $arregloCtaTarjetas = array();
        $i = 0;
        $swActivo = 0;


        if(isset($arreglo['codAsociado'])){

        if(trim($arreglo['codigoProducto']) == trim($codProducto) || trim($arreglo['codigoProducto']) == trim($codProductoVirtual)){

                if($arreglo['codigoEstatus'] < 4){

                    
                    if($arreglo['codigoEstatusTrj'] == '1'){
                        //verificar tipo de producto
                        if(trim($arreglo['codigoProducto']) == trim($codProducto)){
                            $tipoConsulta = 1;
                        }else{     
                            $tipoConsulta = 2;
                        }  
                        $arregloCtaTarjetas[0] = array(
                            'nroTarjeta'=>$arreglo['tarjeta'],
                            'nroCuenta'=>$arreglo['cuenta'],
                            'estatusCliente'=>$arreglo['descripcionEstatus'],
                            'codProducto'=>$arreglo['codigoProducto'],
                        );        
                        $i++;
                        $swActivo = 2;
                    }else{
                        $swActivo = 1;
                        $descripcionStatus = $arreglo['descripcionEstatusTrj'];
                    }
                }             
            }
        }else{
            foreach ($arreglo as $valor) {
                $codProductoWS = $valor['codigoProducto'];
                if(trim($codProductoWS) == trim($codProducto) || trim($codProductoWS) == trim($codProductoVirtual)){
                    if($valor['codigoEstatus'] < 4){
                        if($valor['codigoEstatusTrj'] == '1'){
                            //verificar tipo de producto

                            if($tipoConsulta == 0){
                                if(trim($valor['codigoProducto']) == trim($codProducto)){
                                    $tipoConsulta = 1; //fisica
                                }else{     
                                    $tipoConsulta = 2;   //Virtual
                                }
                            }else{
                                $tipoConsulta = 3;  //posee ambos productos
                            }    

                            $swActivo = 2;  

                            $arregloCtaTarjetas[$i] = array(
                                'nroTarjeta'=>$valor['tarjeta'],
                                'nroCuenta'=>$valor['cuenta'],
                                'estatusCliente'=>$valor['descripcionEstatus'],
                                'codProducto'=>$valor['codigoProducto'],
                            );  
                            $i++;
                        }else{
                            if($swActivo != 2){
                                 $swActivo = 1;                                        
                                 $descripcionStatus = $valor['descripcionEstatusTrj'];
                            }     
                        }   
                    }else{
                        if($swActivo != 2){                                    
                            $swActivo = 1;  
                            $descripcionStatus = $valor['descripcionEstatus']; 
                        }    
                    }    
                }
            }        
        }


        if($swActivo == 0){
            $swValidar = 1;
            $ruta = $titPantallaError.'/'.$swValidar;
            $this->redirect($ruta); 
        }

        if($swActivo == 1){
            $swValidar = 2;
            $ruta = $titPantallaError.'/'.$swValidar. '/' .$descripcionStatus;
            $this->redirect($ruta); 
        }  

        $arregloTotal = array(
           'tipoConsulta'=>$tipoConsulta,
           'arregloCtaTarjetas'=>$arregloCtaTarjetas,
        );

        return $arregloTotal;

    }
    ///////////////////////////////////////////////////////////// 
    // FIN Arreglo con cuentas de credicard validas            //
    /////////////////////////////////////////////////////////////


    /********************************************************************************
      function error_critico
      Permite desplegar la pantalla de errores ocurridos en cualquieras de los procesos
      tarjeta prepagada y cuando son errores de web services o Gateway As400
    **********************************************************************************/     
    function error_critico($swValidar, $proceso, $servicio, $idTransaccion){
        $this->layout = 'interna';
        $mensaje = _m('error.fatal');

        switch($swValidar){
        case 1:
            $descripcion = $mensaje . " [CC]";  
            break;  
        case 2:
            $descripcion = $mensaje . " [AS|400]";      
            break;  
  
        }

        //LLenar Log de canales con el error presentado
        $this->logBitacora($idTransaccion,NULL, NULL,NULL,NULL,$descripcion,NULL);
        $this->set(compact('descripcion'));
    }


    //Validar si el cliente posee saldo para realizar la recarga     
    function valSaldoRecarga($ctaDebito, $totalRecarga,$tipoRecarga){
        $valTotal = str_replace('.','',$totalRecarga);
        $valTotal = str_replace(',','.',$valTotal);
        $cuentasValidar = $this->Activo->buscarCuentasSaldosMeTipo();
        
        $saldo = 0;
        if(count($cuentasValidar) > 0){
          $cuentaME = array();
          foreach ($cuentasValidar as $key => $value) {
                $valores = explode('-', $value);
                $moneda = trim($valores[2]);
                $tipoCta = trim($valores[1]);                
                if($moneda=='USD' && ($tipoCta=='DEPN' || $tipoCta=='CDN1')){
                    if($ctaDebito == $valores[0]){
                        if(empty($valores[3])){
                            $saldo = $valores[4];                                 
                        }else{
                            $saldo = $valores[3];
                        }    
                    }
                    $swCta = 1;
                }    
          }   
        } 

        if($saldo < $valTotal){
            if($tipoRecarga == 'RT'){
                //El usuario no posee saldo para realizar la operación
                $swValidar = 13;
                $ruta = 'no_recargatercero/'.$swValidar;
                $this->redirect($ruta);
            }

            if($tipoRecarga == 'RP'){
                //El usuario no posee saldo para realizar la operación
                $swValidar = 10;
                $ruta = 'no_recarga/'.$swValidar;
                $this->redirect($ruta);
            }
            


        }    

    }        
    //Validar si el cliente posee saldo para realizar la recarga     


//**************************************************************************// 
    //Verificar y existe el cliente en credicard y obtener el nro de la tarjeta //
    //**************************************************************************//
    function validarClienteCredicard($nroDocumento, $tarjeta, $tipoCliente=null,$idTransaccion){
        $nroDocumento = str_pad($nroDocumento,12,'0',STR_PAD_LEFT);
        $res = $this->Credicard->consultarCuenta($nroDocumento); 

        $nroTarjeta = '';
        $nroCuenta = '';
        $descripcionStatus = "";

        if($res == 1){
            $swValidar = 1;
            $ruta = 'error_critico/'.$swValidar  . '/RECARGA TERCERO/WS-CONSULTACUENTA/' . $idTransaccion;
            $this->redirect($ruta);
        }else{

            $swActivo = 0;
            $codProducto = _p('tcp_codigo_producto');
            $codProductoVirtual = _p('tpc_codigo_producto_virtual');

            $fechaActivo = '';
            $arreglo = $res['CuentaTarjetaClienteResponse'];


            if(isset($arreglo['codAsociado'])){
                if(trim($tarjeta) == trim($arreglo['tarjeta'])){
                    if(trim($arreglo['codigoProducto']) == trim($codProducto) || trim($arreglo['codigoProducto']) == $codProductoVirtual){
                        if($arreglo['codigoEstatus'] < 4){
                            if($arreglo['codigoEstatusTrj'] == '1'){
                                $swActivo = 2;  
                                $nroTarjeta = $arreglo['tarjeta']; 
                                $nroCuenta = $arreglo['cuenta']; 
                            }else{
                                $swActivo = 1;
                                $descripcionStatus =$arreglo['descripcionEstatusTrj'];

                            }
                        }             
                    }
                }    
            }else{
                foreach ($arreglo as $valor) {
                    if(trim($tarjeta) == trim($valor['tarjeta'])){
                        $codProductoWS = $valor['codigoProducto'];
                        if(trim($codProductoWS) == trim($codProducto) || trim($codProductoWS) == trim($codProductoVirtual)){
                            if($valor['codigoEstatus'] < 4){
                                if($valor['codigoEstatusTrj'] == '1'){
                                    $swActivo = 2;  
                                    $nroTarjeta = $valor['tarjeta']; 
                                    $nroCuenta = $arreglo['cuenta'];        
                                }else{
                                    $swActivo = 1;
                                    $descripcionStatus =$valor['descripcionEstatusTrj'];

                                }   
                                break;                     
                            }else{
                                $swActivo = 1;  
                                $descripcionStatus = $valor['descripcionEstatus']; 
                            }    
                        }
                    }        
                }   
            }     
        }  
        if($swActivo == 0){
            $swValidar = 1;
            $ruta = 'no_recargatercero/'.$swValidar;
            $this->redirect($ruta); 
        }
   
        if($swActivo == 1){
            $swValidar = 2;
            $ruta = 'no_recargatercero/'.$swValidar. '/' .$descripcionStatus;
            $this->redirect($ruta); 
        }

        $datoTarjeta = trim($nroTarjeta) . '/' . trim($nroCuenta);

        return $datoTarjeta;
    }
    //******************************************************************************// 
    //Fin Verificar y existe el cliente en credicard y obtener el nro de la tarjeta //
    //******************************************************************************//

/******************************************************************
  function no_recargatercero      
  Permite desplegar la pantalla de errores ocurridos durante 
  la recraga de tercero de la tarjetas
******************************************************************/
function no_recargatercero($swValidar, $respuesta=null){
        $this->layout = 'interna';
        $mensaje = "";
        $proceso = 'RECARGA DE TERCERO';
        $servicio = 'WS-RECARGA';  
        $idTransaccion = '102099';    
        $swBitacora = 0;
        switch ($swValidar) {
          case 1:
              $mensaje = _m('tcp.nosolicitada.recarga.beneficiario');
              break;  
          case 2:
              $mensaje = _m('tcp.norecarga');
              $mensaje = $mensaje . " [" . $respuesta . "]";
              break;  
          case 3:
              $mensaje = _m('tcp.sin.cta.usd');
              break;  
           case 4:
              $mensaje = _m('tcp.no.saldo.comision');
              $swBitacora = 1;
              break;
           case 6:
              $mensaje = _m('tcp.rec.nro.dia',$respuesta);
              break;
           case 7:
              $mensaje = _m('tcp.rec.nro.mes',$respuesta);
              break;
           case 8:
              $mensaje = $respuesta;
              $swBitacora = 1; 
              break;    
           case 9:
              $mensaje = $respuesta;
              break;   
           case 10:
              $mensaje = _m('tcp.rec_ter_ced_igual'); 
              break;   
           case 11:
              $mensaje = _m('tcp.rec_ter_ben_no_existe') . " [ " . $respuesta . "]"; 
              break;   
           case 12:
              $mensaje = _m('tcp.rec_ter_ben_no_tarjeta'); 
              break;   
           case 13:
              $mensaje = _m('tcp.saldo.recarga');
              $swBitacora = 1;
              break;   
        }      

        $descripcion =  $mensaje;  
        $descripcionLog = $mensaje . "/" . $proceso . "/" . $servicio; 
        $this->logBitacora($idTransaccion,NULL, NULL,NULL,NULL,$descripcionLog,NULL);

        //log transaccional
/*        if($swBitacora == 1){
            $this->logBitacora('101097',NULL, NULL,NULL,NULL,$descripcion,NULL);
        }    
        //fin log        
*/
        $this->set(compact('descripcion'));
    }

    /******************************************************************
        FIN  function no_recarga tercero
    ******************************************************************/     

    /******************************************************************
        Obtiene el nombre del cliente
    ******************************************************************/     

    function nombreCliente($datos_beneficiario, $formato = null){
        //formato 1 = nommbre, apellido    2 = apellido, nombre

        $pNombre = trim($datos_beneficiario['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE']);
        $sNombre = trim($datos_beneficiario['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE']);
        $pApellido = trim($datos_beneficiario['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO']);
        $sApellido = trim($datos_beneficiario['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO']);

        if(empty($pApellido)){
            return '';
        }

        if($formato = 1){
            if(empty($sNombre)){
                $nombreCompleto = $pNombre . ", " . $pApellido . " " . $sApellido;
            }else{
                $nombreCompleto = $pNombre . " " . $sNombre . ", " . $pApellido . " " . $sApellido; 
            }
            return trim($nombreCompleto);
        }else{
            if(empty($sApellido)){
                $nombreCompleto = $pApellido . ", " . $pNombre . " " . $sNombre;
            }else{
                $nombreCompleto = $pApellido . " " . $sApellido . ", " . $pNombre . " " . $sNombre; 
            }
            return trim($nombreCompleto);
        }
    }

    /******************************************************************
        FIN Obtiene el nombre del cliente
    ******************************************************************/     



    //*********************************************************************// 
    //   listarTarjeta Ajax                                                //
    //*********************************************************************//

    function listarTarjeta($cedula){
        $this->layout = 'ajax';
        //consulta Cliente en WS credicard  
        $cedulaCliente = str_pad($cedula, 12, "0", STR_PAD_LEFT);    
        $res = $this->Credicard->consultarCuenta($cedulaCliente); 
        $nroTarjeta = '';
        $ctaTarjeta = '';
        $descripcionStatus = "";
        $tarjeta = array();

        $error = 0;
        $fisica = "";
        $virtual = "";
        $desError =  "";
        $swFisica = 0;
        $swVirtual = 0;

        if($res == 1){
            $error = 1;
            $desError =  _m('error.fatal') . "[CC]";
        }else{
            $swActivo = 0;
            $codProducto = _p('tcp_codigo_producto');
            $fechaActivo = '';
            $arreglo = $res['CuentaTarjetaClienteResponse'];
            $arregloCtaTarjetas = $this->valConsultaTarjetas($arreglo, 'no_recarga');

            $arrgTarjetas =  $arregloCtaTarjetas['arregloCtaTarjetas'];

            //Crear arreglos para mostrar los numeros de tarjetas en la vista  
            $tarjeta = array();
            foreach ($arrgTarjetas as $registro) {
                if($codProducto == $registro['codProducto']){
                    $fisica = trim($registro['nroTarjeta']) . "F";
                    $swFisica = 1;
                }else{
                    $virtual = trim($registro['nroTarjeta'])."D";
                    $swVirtual = 1;
                }
            }
        } 
         
        $response = array(
            'error'=>$error,
            'swFisica'=>$swFisica,
            'swVirtual'=>$swVirtual,
            'descricionError'=>$desError,
            'fisica'=>$fisica,
            'virtual'=>$virtual
        );
        $this->set('response', $response); 

    }






    function generarCorreo($dataMail){
        $this->Email->smtpOptions = Configure::read('email.smtp');
        $this->Email->delivery = 'smtp';
        $this->Email->from = Configure::read('email.from');
        $this->Email->to = $dataMail['correoTO'];
        $this->Email->subject = $dataMail['correoSubject'];
        $this->Email->sendAs = 'html';
        // Informacion del correo
        $this->Email->template = $dataMail['correoTemplate'];
        $this->set(compact('dataMail'));
        // Envio del correo
        $this->Email->send();
    }

    function descargar_tercero_pdf(){
        //$usuario = $this->Session->read('usuarios.datos');
        $data = $this->Session->read('recargas_tercerosTP.dataPDF');

        $this->set(compact('data'));
        $this->render('/elements/descargar_tercero_pdf');
    }



    function ValTipoTarjeta($tarjeta, $cedula){

        $cedula = str_pad($cedula,12,'0',STR_PAD_LEFT);



        $res = $this->Credicard->consultarCuenta($cedula);
        $codProductoVirtual = _p('tpc_codigo_producto_virtual');
        $tipoProducto = "";

        $swActivo = 0;
        $codProducto = _p('tcp_codigo_producto');
        $codProductoVirtual = _p('tpc_codigo_producto_virtual');

        $arreglo = $res['CuentaTarjetaClienteResponse'];

        if(isset($arreglo['codAsociado'])){
            if(trim($tarjeta) == trim($arreglo['tarjeta'])){
                if(trim($arreglo['codigoProducto']) == trim($codProducto) || trim($arreglo['codigoProducto']) == $codProductoVirtual){
                    if($arreglo['codigoEstatus'] < 4){
                        if($arreglo['codigoEstatusTrj'] == '1'){
                            if(trim($arreglo['codigoProducto']) == trim($codProducto)){
                                $tipoProducto = 'F';
                            }else{
                                $tipoProducto = 'D';
                            }    
                            $swActivo = 2;  
                        }else{
                            $swActivo = 1;
                        }
                    }             
                }
            }    
        }else{
            foreach ($arreglo as $valor) {
                if(trim($tarjeta) == trim($valor['tarjeta'])){
                    $codProductoWS = $valor['codigoProducto'];
                    if(trim($codProductoWS) == trim($codProducto) || trim($codProductoWS) == trim($codProductoVirtual)){
                        if($valor['codigoEstatus'] < 4){
                            if($valor['codigoEstatusTrj'] == '1'){
                                $swActivo = 2;  
                                if(trim($valor['codigoProducto']) == trim($codProducto)){
                                    $tipoProducto = 'F';
                                }else{
                                    $tipoProducto = 'D';
                                
                                }    

                            }else{
                                $swActivo = 1;
                            }   
                            break;                     
                        }else{
                            $swActivo = 1;  
                            $descripcionStatus = $valor['descripcionEstatus']; 
                        }    
                    }
                }        
            }   
        } 
        if($swActivo == 2){    
            return $tipoProducto;
        }    
    }




//**************************************************************
}//fin de la clase principal



?>