<?php
/**
 * Controlador para el Pago de Saren
 */
 
class PagosCorpoelecController extends AppController {

    /**
    *
    * @var array Modelos que usa el controlador 
    */
    var $uses = array(
        'PagoServicios',
        'Usuario',
        'Bitacora',
        'PagoCorpoelec',
        'TransaccionPendiente',
        'TransaccionJuridicoDetalle'
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

    /*** Inicio Pago Corpoelec ***/
    
     /**
     *Acción inicial para el pago de corpoelec
     * @param String $volver Variable que indica que viene de la confirmaciï¿½n
     */
    
    function datos($volver=null){
    
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101102)) $this->redirect('/consultas/integral');


        $usuario = $this->Session->read('usuarios.datos');

        $this->layout = 'interna';
        $this->set('seccion_actual','pagos.corpoelec');
        
        if(!empty($this->data['PagoCorpoelec'])){

            $this->Session->delete('recarga_corpoelec');

            if($this->data['PagoCorpoelec']['servicio'] == 1){
                $tIdent = $this->data['PagoCorpoelec']['tipoCedula'];
                $ident  = $this->data['PagoCorpoelec']['nroCedula'];
                $servicioCus = 'customer';
                $servicioCon = 'contracts-debts';
                $dataRest = array("tipoIdent" => $tIdent,"ident" => $ident);

                $res_customer = $this->Activo->invocaConsultaCorpoelec($servicioCus,$dataRest,"GET");
                $res_customer = json_decode(json_encode($res_customer),true);

                $res_contracts = $this->Activo->invocaConsultaCorpoelec($servicioCon,$dataRest,"GET");
                $res_contracts = json_decode(json_encode($res_contracts),true);

                if (empty($res_customer) || empty($res_contracts)) {
                    $error = "0000";
                    $this->redirect(
                        array(
                            'controller'=>'/pagos_corpoelec/pago_corpoelec_fallida',
                            'action'=>$error
                        )
                    );
                }elseif($res_customer['status'] != '200'){
                    $error = $res_customer['status'];
                    $this->redirect(
                        array(
                            'controller'=>'/pagos_corpoelec/pago_corpoelec_fallida',
                            'action'=>$error
                        )
                    );
                }else{
                    $beneficiario = $res_customer['data']['names']." ".$res_customer['data']['last_name'];

                    $contratos=array();
                    for ($i=0; $i < count($res_contracts['data']); $i++) {
                        $debit = array();

                        if(!empty($res_contracts['data'][$i]["debts"])){
                            $debit =$res_contracts['data'][$i]["debts"];
                        }

                        $aux=(object)array(
                            "contract_account_code" => $res_contracts['data'][$i]["contract_account_code"],
                            "fare_group" => $res_contracts['data'][$i]["fare_group"],
                            "debts" => $debit,
                        );
                        $contratos[] =json_encode($aux);
                    }
                    $this->Session->write('pago.corpoelec',$this->data['PagoCorpoelec']);
                    $this->Session->write('pago.corpoelec.beneficiario',$beneficiario);
                    $this->Session->write('pago.corpoelec.contratos',$contratos);
                }
            }elseif($this->data['PagoCorpoelec']['servicio'] == 2){
                $Contrado =$this->data['PagoCorpoelec']['nroContrato'];
                if (strlen($ident) < 18) {
                    $Contrado = str_pad($Contrado, 18, '0', STR_PAD_LEFT);
                }
                $dataRest= array(
                    "service" =>"corpoelect",
                    "Tipo_de_transaccion" => "T7",
                    "Codigo_Banco" => "0171",
                    "Canal_de_pago" => "6",
                    "Codigo_de_busqueda" => $Contrado
                );
                $operation = $this->Activo->invocaGatewayNexus(
                    "corpoelect",
                    json_encode($dataRest),
                    "POST"
                );

                $response = json_decode(json_encode($operation),true);

                if ($response["Respuesta"]=="0000") {
                    $this->Session->write('pago.corpoelec',$this->data['PagoCorpoelec']);
                    $this->Session->write('pago.corpoelec.response',$response);
                } else {
                    switch($response["Respuesta"]){
                        case "0013":
                            $error = "Banco no activo para la modalidad de pago";
                        break;
                        case "0020":
                            $error = "No se pudo asignar importe";
                        break;
                        case "0022":
                            $error = "Error en BAPI de consulta GET_BALANCE";
                        break;
                        case "0033":
                            $error = "Faltan parámetros para el reverso";
                        break;
                        case "0047":
                            $error = "Time Out ";
                        break;
                        case "0052":
                            $error = "Error aplicando pago de factura";
                        break;
                        case "0111":
                            $error = "Indicar interlocutor.";
                        break;
                    }
                    $this->redirect(
                        array(
                            'controller'=>'/pagos_corpoelec/pago_corpoelec_fallida',
                            'action'=>$error
                        )
                    );
                }
            }
            $fechaC = date("d-m-Y H:i:s");
            
            $this->Session->write('pago.corpoelec.fecha',$fechaC);
            $this->redirect('/pagos_corpoelec/pago');
        }

        $this->Session->delete('pago');
        $this->Session->delete('recarga_corpoelec');
        
        // Para usuario jurÃ­dicos operadores, obtener su configuraciÃ³n para este tipo de transacciÃ³n
        if ($usuario['rol_juridico_id'] == 3 || $usuario['rol_juridico_id'] == 5 ){
            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                $usuario['id'], 101102, $this->data['Transferencia']['numero_cuenta']
            );

            if(isset($transaccion_detalle)){
                $this->Session->write('pago.transaccionDetalle.aprobaciones',$transaccion_detalle['aprobaciones']);
                if(is_numeric($transaccion_detalle['limite'])){
                    $this->Session->write('pago.transaccionDetalle.monto_limite_usuario',formatea_monto_fromWs($transaccion_detalle['limite']));
                } else {
                    $this->Session->write('pago.transaccionDetalle.monto_limite_usuario','');
                }
            }

            if (!$transaccion_detalle) {
                $this->Transferencia->validationErrors['numero_cuenta'] = _m('usuario.no.autorizado');
            }
        }

        $cuentas = $this->Activo->buscarCuentasSaldos();
        $this->set(compact('cuentas','error','monto'));
    }

    function pago(){

        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101102)) $this->redirect('/consultas/integral');

        //Variable utilizada para que no se repita el pago al presionar F5
        $this->Session->read('recarga_corpoelec.swPago',0);        

        $usuario = $this->Session->read('usuarios.datos');
        $datos = $this->Session->read('pago.corpoelec');
        $debts = $datos['debts'];

        $this->facturas = array();
        $arreglo = array();
        $deseado = array();
        $contratos=array();
        $grupo=array();
        $deudas=array();
        
        foreach ($datos['contratos'] as $key => $value) {
            $aux=json_decode($value);
            $contratos[] = $aux->contract_account_code;
            $grupo[] = $aux->fare_group;
            if (isset($aux->debts) && !empty($aux->debts)) {
                $this->facturas[$aux->contract_account_code] = $aux->debts;
                normaliza($this->facturas);
            }
        }

        foreach ($this->facturas as $value) {
            $deseado[] =array_values($value);
        }
        
        if($datos['servicio'] == 1){
            if(!empty($this->data['PagoCorpoelec']['factura'])){
                $factura=explode(" - ",$this->data['PagoCorpoelec']['factura']);
                
                $datos["debts"]=substr($factura[1], 4);

                $resultado = null;

                // Buscar en el array
                foreach ($deseado as $facturaArray) {
                    foreach ($facturaArray as $value) {
                        if ($value->id ===  $factura[0]) {
                            $resultado = $value;
                            break 2; // Salir de ambos bucles
                        }
                    }
                }
                
                $this->Session->write('pago.corpoelec.contrato',$contratos[$this->data['PagoCorpoelec']['contrato']]);
                $this->Session->write('pago.corpoelec.grupo',$grupo[$this->data['PagoCorpoelec']['contrato']]);
                $this->Session->write('pago.corpoelec.fecha',date("d-m-Y H:i:s"));
                $this->Session->write('pago.corpoelec.debts',substr($factura[1], 4));
                $this->Session->write('pago.corpoelec.id',$this->data['PagoCorpoelec']['factura']);
                $this->Session->write('pago.corpoelec.desc',utf8_decode($factura[2]));

                $this->redirect('/pagos_corpoelec/confirmar_pago');
            }
        }elseif($datos['servicio'] == 2){

            $validaM = ltrim($datos['response']['Deuda_Total'],'0');
            
            if ($validaM == 0 || empty($validaM)) {
                $error = '200';
                $this->redirect(
                    array(
                        'controller'=>'/pagos_corpoelec/pago_corpoelec_fallida',
                        'action'=>$error
                    )
                );
            }

            if(!empty($this->data['PagoCorpoelec']['deudaPagar'])){
                $keyPago = strtr($this->data['PagoCorpoelec']['deudaPagar'], " ", "_");
                $ico='';
                switch($keyPago){
                    case 'Deuda_Vigente':
                    $ico ='A';
                    break;
                    case 'Deuda_Vencida':
                    $ico ='V';
                    break;
                    case 'Deuda_Total':
                    $ico ='T';
                    break;
                }
                //validar tipo de servicio a pagar
                $tipoS = $this->data['PagoCorpoelec']['tipoServicio'];

                $tp = 0;
                $aux = explode(" ", $this->data['PagoCorpoelec']['deudaPagar']);
                switch($tipoS){
                    case 'Energia':
                    $keyPago=$aux[0]."_Energia_".$aux[1];
                    $tp="01";
                    break;
                    case 'Aseo+Relleno':
                    $keyPago=$aux[0]."_Aseo+Relleno_".$aux[1];
                    $tp="06";
                    break;
                    case 'Total':
                    $tp="17";
                    break;
                }

                $this->Session->write('pago.corpoelec.ico',$ico);
                $this->Session->write('pago.corpoelec.tp',$tp);
                $this->Session->write('pago.corpoelec.debts',$datos['response'][$keyPago]);
                $this->Session->write('pago.corpoelec.fecha',date("d-m-Y H:i:s"));
                $this->redirect('/pagos_corpoelec/confirmar_pago');
            }
        }
        

        $this->layout = 'interna';
        $this->set(compact('datos','deseado','contratos','grupo'));
        
    }

    function confirmar_pago(){

        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101102)) $this->redirect('/consultas/integral'); 

        $usuario = $this->Session->read('usuarios.datos'); 
        $datosPago = $this->Session->read('pago.corpoelec'); 
        
        if ($this->Session->check('pago.corpoelec')){
            $datos = $this->Session->read('pago.corpoelec');
        }
        
        $this->layout = 'interna';
        $this->set(compact('datos','deseado'));  
    }

    function formatoMoneda($numero){
        $decimales = substr($numero, -2);
        $entero = substr($numero, 0, -2);
        $numeroFormateado = number_format($entero, 0, '', '.');
        $resultadoFinal = $numeroFormateado . '.' . $decimales;
        return $resultadoFinal;
    }

    function resultado_pago($volver=null){

        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101102)) $this->redirect('/consultas/integral');

    //validar que no se repita el pago al presionar f5    
    $swPago =  $this->Session->read('recarga_corpoelec.swPago');    

    if($swPago == 1){
        $ruta = 'resultado_pago_vta';
        $this->redirect($ruta); 

    }else{          

        $usuario = $this->Session->read('usuarios.datos');
        $datos = $this->Session->read('pago.corpoelec');
        $transaccionDetalle = $this->Session->read('pago.transaccionDetalle');
        $monto="";
        switch($datos['ico']){
            case 'A':
            $monto =$datos['response']['Deuda_Vigente'];
            break;
            case 'V':
            $monto =$datos['response']['Deuda_Vencida'];
            break;
            case 'T':
            $monto =$datos['response']['Deuda_Total'];
            break;
        }

        $dVigente = "";
        $dVencida = "";
        $dTotal = "";
        
        switch($datos['tp']){
            case "01":
                $dVigente = $datos['response']['Deuda_Energia_Vigente'];
                $dVencida = $datos['response']['Deuda_Energia_Vencida'];
                $dTotal = $datos['response']['Deuda_Energia_Total'];
            break;
            case "06":
                $dVigente = $datos['response']['Deuda_Aseo+Relleno_Vigente'];
                $dVencida = $datos['response']['Deuda_Aseo+Relleno_Vencida'];
                $dTotal = $datos['response']['Deuda_Aseo+Relleno_Total'];
            break;
            case "17":
                $dVigente = $datos['response']['Deuda_Vigente'];
                $dVencida = $datos['response']['Deuda_Vencida'];
                $dTotal = $datos['response']['Deuda_Total'];
            break;
        }

        if($usuario['rol_juridico_id'] == 3 || $usuario['rol_juridico_id'] == 5 ){
            $mtoCorp = $datos['debts'];

            $stringData = 
                $datos['cuentaDebitar']."?". //IN8008ACC
                'VEB'."?". //IN8008CCY
                $datos['nroContrato']."?". //IN8008CON
                $usuario['nrocliente']."?".//IN8008CUN
                substr($dVigente, 0,13) . "." .substr($dVigente, -2)."?". //IN8008D01
                substr($dVencida, 0,13) . "." .substr($dVencida, -2)."?". //IN8008D02
                substr($dTotal, 0,13) . "." .substr($dTotal, -2)."?". //IN8008D03
                $datos['ico']."?". //IN8008ICO
                substr($mtoCorp, 0,13) . "." .substr($mtoCorp, -2)."?". //IN8008MTO
                $datos['tp']."?". //IN8008TMV
                desencriptar($usuario['login']); //IN8008USR
            
            $error = 0;
            $monto = ltrim(substr($mtoCorp, 0,13)).".".substr($mtoCorp, -2);
            $tipo_transaccion_pendiente = 'spc';
            $aprobaciones =$transaccionDetalle['aprobaciones'];
            if(empty($aprobaciones) || is_null($aprobaciones)){
                $aprobaciones=1;
            }

            $DataTransaccionPendiente=array(
                'TransaccionPendiente'=>array(
                    'usuario_id'=>$usuario['id'],
                    'tipo'=>$tipo_transaccion_pendiente,
                    'tipo_servicio'=>'pcj',
                    'estatus'=>'pa',
                    'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                    'monto'=>$monto,
                    'aprobaciones_requeridas'=>$aprobaciones,
                    'tipo_rif'=>$usuario['tipo_rif'],
                    'rif'=>$usuario['rif'],
                    'cuenta_origen'=>encriptar($datos['cuentaDebitar']),
                    'cuenta_destino'=>encriptar($datos['nroContrato']),
                    'descripcion'=>encriptar($stringData),
                    'nombre_beneficiario'=>encriptar($this->data['PagoServicios']['nombreBeneficiario']),
                    'tipo_id_beneficiario'=>$usuario['tipo_rif'],
                    'id_beneficiario'=>$usuario['rif'],
                    'email_beneficiario'=>$usuario['email_principal'],
                    'lote'=>$this->data['PagoServicios']['referenciab'],
                )
            );
            
            $res = $this->TransaccionPendiente->save($DataTransaccionPendiente,false);
            
            if($res){
                $error = 0;
                $referencia = $this->TransaccionPendiente->id;
            } else {
                $error = -1;
                $referencia = '';
            }
            $tipo_operacion = 'Pago Corpoelec';
            $cta_debito = enmascara($datos['cuentaDebitar']); 
            $nro_contrato = $datos['nroContrato'];
            $monto = ltrim(substr($datos['debts'],0,13),"0").'.'.substr($datos['debts'], -2);
            $monto = $monto;
            $fecha_proceso = $datos['fecha'];
            $concepto = 'PAGO CORPOELEC';
            $descripcion = $concepto . "-" . $nro_contrato;


            $this->Session->write('recarga_corpoelec.tipo_operacion_vt',$tipo_operacion);
            $this->Session->write('recarga_corpoelec.cta_debito_vt',$cta_debito);
            $this->Session->write('recarga_corpoelec.nro_contrato_vt',$nro_contrato);
            $this->Session->write('recarga_corpoelec.monto_vt',$monto);
            $this->Session->write('recarga_corpoelec.fecha_proceso_vt',$fecha_proceso);
            $this->Session->write('recarga_corpoelec.concepto_vt',$concepto);
            $this->Session->write('recarga_corpoelec.referencia_vt',$referencia);
            $this->Session->write('recarga_corpoelec.usuario_vt',$usuario);
            $this->Session->write('recarga_corpoelec.swPago',1);

            $this->set('pendiente',true);
            $this->layout = 'interna';
            $this->set(compact('tipo_operacion','cta_debito','nro_contrato','monto','fecha_proceso','concepto','usuario','referencia'));
        }elseif (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2){
            $validacion_otp = $this->Session->read('seguridad.validar_otp');
            if (($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) 
            {
                $url_volver = '/pagos_corpoelec/confirmar_pago';
                $pasos = array(
                    'Solicitud de Información',
                    'Solicitud de Factura',
                    'Confirmación',
                    'Código de Autorización',
                    'Resultado Operación'
                );
                
                $this->Session->write(
                    'seguridad.validar_otp', 
                    array(
                        'url_aceptar' => '/pagos_corpoelec/resultado_pago',
                        'url_volver' => $url_volver,
                        'layout' => 'interna',
                        'titulo' => 'Pago Corpoelec',
                        'pasos' => $pasos,
                        'paso_actual' => 3,
                        'seccion_actual' => 'pagos.corpoelec',
                    )
                );
                $this->redirect('/seguridad/genera_otp');
            }
            $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $cuenta = substr($datos['cuentaDebitar'],10);
            $tipCuenta = substr($cuenta,0,1);
            $TipInst='';
            switch ($tipCuenta) {
                case '2':
                    $TipInst='A';
                    break;
                case '4':
                case '6':
                    $TipInst='C';
                    break;
            }

            $mtoCorp = $datos['debts'];

            if($datos['servicio'] == 1){
                $arrayData = array(
                    'IN8003ACC'=> substr($datos['cuentaDebitar'],10),
                    'IN8003CCY'=> 'VEB',
                    'IN8003MTO'=> $datos['debts'],
                    'IN8003CON'=> $datos['contrato'],
                    'IN8003NAC'=> $datos['tipoCedula'] . $datos['nroCedula'],
                    'IN8003GRU'=> $datos['grupo'],
                    'IN8003CPT'=> $this->formatearCadena($datos['desc']),
                    'IN8003CAN'=> '04',
                    'IN8003DIP'=> $ip,
                    'IN8003USR'=> desencriptar($usuario['login']),
                    'IN8003IRV'=> 'N',                        
                );
        
                $res = $this->Activo->invoca('IN800301',array('req'=>$arrayData));

            }elseif ($datos['servicio'] == 2) {
                $arrayData = array(
                    'IN8008ACC'=>substr($datos['cuentaDebitar'],10),
                    'IN8008CAN'=>'04',
                    'IN8008CCY'=>'VEB',
                    'IN8008CKN'=>0,
                    'IN8008CON'=>$datos['nroContrato'],
                    'IN8008CPA'=>'6',
                    'IN8008CUN'=>$usuario['nrocliente'],
                    'IN8008D01'=>substr($dVigente, 0,13) . "." .substr($dVigente, -2),
                    'IN8008D02'=>substr($dVencida, 0,13) . "." .substr($dVencida, -2),
                    'IN8008D03'=>substr($dTotal, 0,13) . "." .substr($dTotal, -2),
                    'IN8008DIP'=>$ip,
                    'IN8008DSC'=>'',
                    'IN8008ERR'=>0,
                    'IN8008FEC'=>date('Ymd'),
                    'IN8008HOR'=>date('his'),
                    'IN8008ICO'=>$datos['ico'],
                    'IN8008INS'=>$TipInst,
                    'IN8008IRV'=>'N',
                    'IN8008MTO'=>substr($mtoCorp, 0,13) . "." .substr($mtoCorp, -2),
                    'IN8008TMV'=>$datos['tp'],
                    'IN8008USR'=>desencriptar($usuario['login'])
                );
        
                $res = $this->Activo->invocaCorpoelect('IN800801',array('req'=>$arrayData));
            }

            if (empty($res)) {
               
                $error = '0001';
                $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error));       
            }else{
                if($datos['servicio'] == 1){
                    if($res['IN800301Return']['IN800301']['IN8003ERR'] != 0){
                        $error = $res['IN800301Return']['IN800301']['IN8003ERR'];
                        $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error));
                    }else{
                        $id =explode(" - ",$datos['id']);
                        $debt_id = $id[0];
                        $amount = $datos['debts'];
                        $operation_code = $res['IN800301Return']['IN800301']['IN8003CKN'];

                        $servicioCus = "contracts-debts";
                        $dataRest = array("debt_id" => $debt_id,"amount" => $amount,"operation_code" => $operation_code);
                        $res_customer = $this->Activo->invocaPagoCorpoelec($servicioCus,$dataRest,"POST");
                        $res_customer = json_decode(json_encode($res_customer),true);
                        if (!empty($res_customer) || $res_customer['status'] == '200') { 
                            $this->Email->smtpOptions = Configure::read('email.smtp');
                            $this->Email->delivery = 'smtp';
                            $this->Email->from = Configure::read('email.from');
                            $this->Email->to = desencriptar($usuario['email_principal']);
                            $this->Email->subject = 'Notificaci&oacute;n de Pago Corpoelec';
                            $this->Email->sendAs = 'html';
                            $this->Email->template  = 'pago_corpoelec';

                            $tipo_operacion = 'Pago Corpoelec';
                            $cta_debito = enmascara($datos['cuentaDebitar']); 
                            $nro_contrato = $datos['contrato'];
                            $monto = $datos['debts'];
                            $fecha_proceso = $datos['fecha'];
                            $concepto = 'PAGO CORPOELEC';
                            $referencia = $operation_code;
                            $descripcion = $concepto . "-" . $nro_contrato;

                            $this->Session->write('recarga_corpoelec.tipo_operacion_vt',$tipo_operacion);
                            $this->Session->write('recarga_corpoelec.cta_debito_vt',$cta_debito);
                            $this->Session->write('recarga_corpoelec.nro_contrato_vt',$nro_contrato);
                            $this->Session->write('recarga_corpoelec.monto_vt',$monto);
                            $this->Session->write('recarga_corpoelec.fecha_proceso_vt',$fecha_proceso);
                            $this->Session->write('recarga_corpoelec.concepto_vt',$concepto);
                            $this->Session->write('recarga_corpoelec.referencia_vt',$referencia);
                            $this->Session->write('recarga_corpoelec.usuario_vt',$usuario);    
                            $this->Session->write('recarga_corpoelec.swPago',1); 




                            $this->set(compact('tipo_operacion','cta_debito','nro_contrato','monto','fecha_proceso','concepto','referencia','usuario'));
                            
                            // Envio del correo
                            $this->Email->send();

                            $this->Bitacora->registraBitacora(
                                $usuario['id'], 
                                101102,
                                $cta_debito, 
                                NULL, 
                                $monto,
                                $referencia,
                                $descripcion,
                                0
                            );
                            $this->layout = 'interna';
                        }else{

                            $res = $this->Activo->invoca(
                                'IN800301',
                                    array(
                                        'req'=>array(
                                            'IN8003ACC'=> substr($datos['cuentaDebitar'],10),
                                            'IN8003CCY'=> 'VEB',
                                            'IN8003MTO'=> $datos['debts'],
                                            'IN8003CON'=> $datos['contrato'],
                                            'IN8003NAC'=> $datos['tipoCedula'] . $datos['nroCedula'],
                                            'IN8003GRU'=> $datos['grupo'],
                                            'IN8003CPT'=> $this->formatearCadena($datos['desc']),
                                            'IN8003CAN'=> '04',
                                            'IN8003DIP'=> $ip,
                                            'IN8003USR'=> desencriptar($usuario['login']),
                                            'IN8003IRV'=> 'Y',
                                            'IN8003CKN'=> $res['IN800301Return']['IN800301']['IN8003CKN'],
                                        )
                                    )
                                );

                            if ($res['IN8003ERR'] == 0) {
                                $error = $res['IN8003ERR'];
                                $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error)); 
                            }else{
                                $error = '0000';
                                $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error));
                            }

                            $error = 0001;

                            $this->Bitacora->registraBitacora(
                                $usuario['id'], 
                                101102,
                                NULL, 
                                encriptar($datos['cuentaDebitar']), 
                                $datos['debts'],
                                $res['IN800301Return']['IN800301']['IN8003CKN'],
                                encriptar($datos['desc']),
                                0
                            );
                        }
                    }
                }elseif($datos['servicio'] == 2){
                    if($res['IN800801Return']['IN800801']['IN8008ERR'] != 0){
                        $error = $res['IN800801Return']['IN800801']['IN8008ERR'];
                        $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error));
                    }else{
                        $id =explode(" - ",$datos['id']);
                        $debt_id = $id[0];
                        $amount = $datos['debts'];
                        $operation_code = $res['IN800801Return']['IN800801']['IN8008CKN'];
                        $contrado = str_pad($res['IN800801Return']['IN800801']['IN8008CON'],18,'0',STR_PAD_LEFT);
                        $tipCuenta = substr($res['IN800801Return']['IN800801']['IN8008ACC'],0,1);
                        $TipInst='';
                        switch ($tipCuenta) {
                            case '2':
                                $TipInst='A';
                                break;
                            case '4':
                            case '6':
                                $TipInst='C';
                                break;
                        }
                        $dataRest= array(
                            "service"                 => "corpoelect",
                            "Tipo_de_transaccion"     => "T2",
                            "Codigo_Banco"            => "0171",
                            "Canal_de_pago"           => "6",
                            "Codigo_de_busqueda"      => str_pad($datos['nroContrato'],18,'0',STR_PAD_LEFT),
                            "Fecha_Recaudacion"       => $res['IN800801Return']['IN800801']['IN8008FEC'],
                            "Hora_Recaudacion"        => str_pad($res['IN800801Return']['IN800801']['IN8008HOR'], 6 , '0' , STR_PAD_LEFT),
                            "Respuesta"               => "0000",
                            "Tipo_de_Instrumento"     => $TipInst,
                            "Referencia_instrumento"  => "000000000000000000",
                            "Tipo_movimiento"         => str_pad($datos['tp'],2,'0',STR_PAD_LEFT),
                            "Indicador_cobro"         => $res['IN800801Return']['IN800801']['IN8008ICO'],
                            "Monto_Recaudado"         => $datos['debts'],
                            "Referencia_1"            => "000000000000000000",
                            "Referencia_2"            => "000000000000000000",
                            "Referencia_3"            => "00000000000000"
                        );
                        //print_r($dataRest);
                        $operation = $this->Activo->invocaGatewayNexus(
                            "corpoelect",
                            json_encode($dataRest),
                            "POST"
                        );
                        $response = json_decode(json_encode($operation),true);
                        //print_r($response);
                        //die();
                        if ($response["Respuesta"]=="0000"){
                            $this->Email->smtpOptions = Configure::read('email.smtp');
                            $this->Email->delivery = 'smtp';
                            $this->Email->from = Configure::read('email.from');
                            $this->Email->to = desencriptar($usuario['email_principal']);
                            $this->Email->subject = 'Notificación de Pago Corpoelec';
                            $this->Email->sendAs = 'html';
                            $this->Email->template  = 'pago_corpoelec';

                            $tipo_operacion = 'Pago Corpoelec';
                            $cta_debito = enmascara($datos['cuentaDebitar']); 
                            $nro_contrato = ltrim($contrado,'0');
                            $monto = $this->FormatoMoneda($datos['debts']);
                            $fecha_proceso = $datos['fecha'];
                            $concepto = 'PAGO CORPOELEC';
                            $referencia = str_pad(trim($operation_code),8,'0',STR_PAD_LEFT);
                            $descripcion = $concepto . "-" . $nro_contrato;


                            $this->Session->write('recarga_corpoelec.tipo_operacion_vt',$tipo_operacion);
                            $this->Session->write('recarga_corpoelec.cta_debito_vt',$cta_debito);
                            $this->Session->write('recarga_corpoelec.nro_contrato_vt',$nro_contrato);
                            $this->Session->write('recarga_corpoelec.monto_vt',$monto);
                            $this->Session->write('recarga_corpoelec.fecha_proceso_vt',$fecha_proceso);
                            $this->Session->write('recarga_corpoelec.concepto_vt',$concepto);
                            $this->Session->write('recarga_corpoelec.referencia_vt',$referencia);
                            $this->Session->write('recarga_corpoelec.usuario_vt',$usuario);  
                            $this->Session->write('recarga_corpoelec.swPago',1); 



                            $this->set(compact('tipo_operacion','cta_debito','nro_contrato','monto','fecha_proceso','concepto','referencia','usuario'));
                            
                            // Envio del correo
                            $this->Email->send();
                            $mOpe = ltrim(substr($mtoCorp, 0,13),'0') . "," .substr($mtoCorp, -2);

                            $this->Bitacora->registraBitacora(
                                $usuario['id'],
                                101102,
                                $cta_debito, 
                                NULL, 
                                $mOpe,
                                $referencia,
                                $descripcion,
                                0
                            );
                            $this->layout = 'interna';
                        }else{
                            $arrayData['IN8008IRV']='Y';
                            $arrayData['IN8008CKN']=$res['IN800801Return']['IN800801']['IN8008CKN'];
                            $res = $this->Activo->invocaCorpoelect('IN800801',array('req'=>$arrayData));

                            if ($res['IN800801Return']['IN800801']['IN8008ERR'] == 0) {
                                $error = $res['IN8003ERR'];
                                $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error)); 
                            }else{
                                $error = '0000';
                                $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error));
                            }

                            $error = 0001;

                            $this->Bitacora->registraBitacora(
                                $usuario['id'], 
                                101102,
                                NULL, 
                                encriptar($datos['cuentaDebitar']), 
                                $mOpe,
                                $res['IN800801Return']['IN800801']['IN8008CKN'],
                                encriptar($datos['desc']),
                                0
                            );
                        }
                    }
                }
            }

        }   
    } //fin del control de swPAgo            
    }

    function pago_corpoelec_fallida($error){
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101102)) {
            $this->redirect('/consultas/integral');
        }

        $usuario = $this->Session->read('usuarios.datos');
        $descripcion = "";

        switch ($error) {
            case "0013":
                $error = "Banco no activo para la modalidad de pago";
            break;
            case "0020":
                $error = "No se pudo asignar importe";
            break;
            case "0022":
                $error = "Error en BAPI de consulta GET_BALANCE";
            break;
            case "0033":
                $error = "Faltan parámetros para el reverso";
            break;
            case "0047":
                $error = "Time Out ";
            break;
            case "0052":
                $error = "Error aplicando pago de factura";
            break;
            case "0111":
                $error = "Indicar interlocutor.";
            break;
            case '0000':
                $descripcion = _m('servicio.nocomunicacion') . "[SC]";
                break;
            case '0001':
                $descripcion = _m('servicio.nocomunicacion');
                break;
            case '200':
                $descripcion = "Estimado cliente, usted no posee deuda actualmente";
                break;
            case '404':
                $descripcion = _m('error.fatal') . "(Registro no encontrado)";
                break;
            case '3000':
                $descripcion = "Falta Cuenta Débito";
                break;
            case '0041':
                $descripcion = "Número de Cuenta no existe";
                break;
            case '9923':
                $descripcion = "Fondos No Disponibles";
                break;
            case '0223':
                $descripcion = "Estatus de la Cuenta no es Activo";
                break;
            case '9441':
                $descripcion = "Moneda Enviada Invalida";
                break;
            case '6120':
                $descripcion = "Código de Canal Invalido";
                break;
            case '2020':
                $descripcion = "Falta Dirección Ip";
                break;
            case '1751':
                $descripcion = "Falta Numero Contrato";
                break;
            case '2002':
                $descripcion = "Falta Monto a Debitar";
                break;
            case '9923':
                $descripcion = "Fondos No Disponibles";
                break;
            case '2003':
                $descripcion = "Falta Referencia BABU (SI ES UN REVERSO)";
                break;
            case '2010':
                $descripcion = "Transacción Original no Existe";
                break;
            case '2020':
                $descripcion = "Transacción ya Reversada";
                break;
            case '0100':
                $descripcion = _m('operacion.fallida') . " [C-{$error}]";
                break;
            default:
                $descripcion = _m('operacion.fallida');
                break;
        }
        $this->layout = 'interna';
        $this->set(compact('descripcion'));
    }

    function formatearCadena($cadena) {
        $cadena = trim($cadena);
        $cadena = strtoupper($cadena);
        $cadena = str_replace('á', 'A', $cadena);
        $cadena = str_replace('é', 'E', $cadena);
        $cadena = str_replace('í', 'I', $cadena);
        $cadena = str_replace('ó', 'O', $cadena);
        $cadena = str_replace('ú', 'U', $cadena);
        $cadena = str_replace('n', 'N', $cadena);
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


    function resultado_pago_vta(){
        $this->layout = 'interna';
        $this->set('seccion_actual','prepagas.recargas');
        $tipo_operacion = trim($this->Session->read('recarga_corpoelec.tipo_operacion_vt'));
        $cta_debito = trim($this->Session->read('recarga_corpoelec.cta_debito_vt'));
        $nro_contrato = trim($this->Session->read('recarga_corpoelec.nro_contrato_vt'));
        $monto = trim($this->Session->read('recarga_corpoelec.monto_vt'));
        $fecha_proceso = trim($this->Session->read('recarga_corpoelec.fecha_proceso_vt'));
        $concepto = trim($this->Session->read('recarga_corpoelec.concepto_vt'));
        $referencia = trim($this->Session->read('recarga_corpoelec.referencia_vt'));
        $usuario = trim($this->Session->read('recarga_corpoelec.usuario_vt'));
        $this->set(compact('tipo_operacion','cta_debito','nro_contrato','monto','fecha_proceso','concepto','referencia','usuario'));
    }


}
