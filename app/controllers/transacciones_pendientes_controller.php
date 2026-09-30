<?php
/**
 * Controlador para las operaciones en la pizarra de transacciones pendientes 
 * de los usuarios juridicos.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Operaciones en la pizarra de transacciones pendientes de los usuarios juridicos.
 * 
 */
class TransaccionesPendientesController extends AppController {
    
    /**
     *
     * @var array Modelos que usa el controlador 
     */
    var $uses = array(
        'Usuario',
        'Bitacora',
        'TransaccionPendiente',
        'TransaccionJuridicoDetalle',
        'TransaccionBeneficiario',
        'AprobacionTransaccion',
        'AcumuladoTransaccion',
        'PagoCash',
        'LimitesVip',
        'TransaccionMesaCambio',
        'TransaccionIntervencion'
    );

    /**
     *
     * @var array Componentes que utiliza este controlador
     */
    var $components = array('Activo', 'Session', 'Email','Sms', 'Compensacion', 'MesaCambio', 'Intervencion','Simpletv', 'Credicard');

    /**
     *
     * @var array Ayudantes utilizados en las vistas de este controlador
     */
    var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');

    
    /**
     * Método que lista las transacciones pendientes de la empresa.
     * 
     * @param string $volver Indica si está regresando de la confirmación
     */
    function index($volver=null){
        if(!validarTransaccion(36)) $this->redirect('/consultas/integral');

        $usuario = $this->Session->read('usuarios.datos');
        
        $this->Session->delete('transacciones_pendientes.accion_multiple');
        
        $this->Session->delete('transacciones_pendientes.transacciones_ejecutadas');
        
        // registra los datos del usuario en bitacora
        $this->Bitacora->registraBitacora($usuario['id'], 36);
        
        if(isset($this->data['accion'])){
            $this->Session->write(
                'transacciones_pendientes.index',
                $this->data
            );
            
            switch($this->data['accion']){
                case 'aprobar':
                case 'rechazar':
                case 'eliminar':
                    $this->Session->write(
                        'transacciones_pendientes.accion_multiple',
                        array(
                            'seleccion' => $this->data['seleccion'],
                            'accion'=>$this->data['accion'],
                            'ruta_regreso'=>'index/v',)
                    );
                    
                    $this->redirect('accion_multiple');
                    break;                  
            }
        } else if(!empty($volver) && $this->Session->check('transacciones_pendientes.index')){
            $estado = $this->Session->read('transacciones_pendientes.index');
            
            $this->data = $estado;
        }
        
        $this->layout = 'interna';

        $this->set('seccion_actual','aprobaciones.resumen');
        
        $pendientes = $this->TransaccionPendiente->obtenerAprobacionesPendientes($usuario);
        
        // Se declara el arreglo con un orden especifico
        $agrupadas = array();
        $agrupadas['t'] = array();
        $agrupadas['p'] = array();
        $agrupadas['c'] = array();
        $agrupadas['n'] = array();
        $agrupadas['v'] = array();
        $agrupadas['i'] = array();
        $agrupadas['s'] = array();//Pago se servicios Postpago
        $agrupadas['r'] = array();//Recarga servicios Prepago
        $agrupadas['e'] = array();//Divisas
        $agrupadas['f'] = array();//Intervencion Cambiaria
        $agrupadas['m'] = array(); // Nomina Moneda Extranjera
        $agrupadas['x'] = array(); // Proveedores Moneda Extranjera
        
        foreach($pendientes as $k => $i){
            $agrupadas[$i['TransaccionPendiente']['tipo'][0]] []= $i;
        }
        
        // si no contiene data el tipo de transaccion se elimina
        foreach ($agrupadas as $k => $v) if(!count($agrupadas[$k])) unset($agrupadas[$k]);
        
        $this->set(compact('agrupadas', 'pendientes','usuario'));
    }
    
    
    /**
     * Método para el detalle del pago pendiente
     * 
     * @param string $volver Indica si está regresando de la confirmación
     */
    function detalle($volver=null){
        if(!validarTransaccion(36)) $this->redirect('/consultas/integral');
        
        $usuario = $this->Session->read('usuarios.datos');
        
        $this->layout = 'interna';

        $this->set('seccion_actual','aprobaciones.resumen');
        
        // Si el usuario está regresando, leer la data de sesión
        
        if(!empty($volver) && $this->Session->check('transacciones_pendientes.detalle')){
            $this->data = $this->Session->read('transacciones_pendientes.detalle');
        }

        if(isset($this->data['TransaccionPendiente']['id'])){
        
            $transaccion = $this->TransaccionPendiente->find(
                'first',
                array(
                    'conditions'=>array(
                        'TransaccionPendiente.tipo_rif'=>$usuario['tipo_rif'],
                        'TransaccionPendiente.rif'=>$usuario['rif'],
                        'TransaccionPendiente.id'=>$this->data['TransaccionPendiente']['id']
                    )
                )
            );
            
            if(!$transaccion){
                // La transacción ya no existe, redirigir
                $this->redirect('index');
            }
            
            if(
                $usuario['rol_juridico_id']==3 &&
                $usuario['id'] != $transaccion['TransaccionPendiente']['usuario_id']
            ){
                // Los operadores sólo pueden trabajar con sus propias transacciones
                
                $this->redirect('index');
            }
            
            if($transaccion['TransaccionPendiente']['estatus'] != 'pa' && $transaccion['TransaccionPendiente']['estatus'] != 'ra'){
                // Sólo mostrar transacciones pendientes
                
                $this->redirect('index');
            }
            
            $this->TransaccionPendiente->analizaPermisos($usuario, $transaccion);
            $permiso_aprobacion = false;
            
            if(isset($transaccion['permiso_aprobacion'])) {
                $permiso_aprobacion = $transaccion['permiso_aprobacion'];
            }
            
            if(isset($this->data['accion'])){
                // El usuario pulsó un boton en la página de detalle
                
                // Guardar el id en sesión en caso de que el usuario pulse volver
                $this->Session->write(
                    'transacciones_pendientes.detalle',
                    array(
                        'TransaccionPendiente'=>array(
                            'id'=>$this->data['TransaccionPendiente']['id']
                        )
                    )
                );

                // Identificar el botón presionado
                switch($this->data['accion']){
                    case 'aprobar':
                    case 'rechazar':
                        if(!$transaccion['permiso_aprobacion']) $this->redirect('index');
                    case 'eliminar':
                        $estado = array(
                            'seleccion' => array($this->data['TransaccionPendiente']['id']),
                            'accion'=>$this->data['accion'],
                            'ruta_regreso'=>'detalle/v',
                        );
                
                        $this->Session->write(
                            'transacciones_pendientes.accion_multiple',
                            $estado
                        );
                        
                        $this->redirect('accion_multiple');
                        
                        break;
                    case 'modificar':
                        if(in_array($transaccion['TransaccionPendiente']['tipo'][0],array('n','v'))){
                            // Las modificaciones de pago de nómina y proveedores las redirigimos a su módulo correspondiente                       
                            
                           $map = array('n' => 'n', 'v' => 'p', 'm' => 'm', 'x'=>'x');
                            
                            $this->Session->write('cash.pagos.operacion', 
                                array(
                                    'tipo'=>$map[$transaccion['TransaccionPendiente']['tipo'][0]],
                                    'url_volver_modificar'=>'/transacciones_pendientes/detalle/v',
                                    'modificar_detalle'=>true
                                )
                            );
                            
                            $dataTemp = $this->PagoCash->buscar($transaccion['TransaccionPendiente']['id'],$usuario['id']);

                            $this->Session->write('cash.pagos.data', $dataTemp);
                            
                            $this->redirect('/cash/pago_modificar');
                        } else {
                            // Las modificaciones de transferencias y pagos de tdc las manejamos por aquí mismo
                            
                            $this->Session->write(
                                'transacciones_pendientes.modificar',
                                array(
                                    'TransaccionPendiente'=>array(
                                        'id'=>$this->data['TransaccionPendiente']['id']
                                    )
                                )
                            );
                            
                            $this->redirect('modificar');
                        }
                        
                        break;
                }
            }
                
            
            // Buscar el banco destino
            
            $bancos = $this->Activo->buscarBancos();
           
            if($transaccion['TransaccionPendiente']['tipo'][2]=='o'){

                switch($transaccion['TransaccionPendiente']['tipo'][0]){
                    case 'r':
                        $id_banco_destino = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),0,4);
                        $transaccion['TransaccionPendiente']['banco'] = $bancos[$id_banco_destino]; 
                    break;
                    case 's':
                        $id_banco_destino = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),0,4);
                        $transaccion['TransaccionPendiente']['banco'] = $bancos[$id_banco_destino]; 
                    break;
                    case 't':                        
                        if(strlen(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])) < 19){
                            $id_banco_destino = $transaccion['TransaccionPendiente']['banco'];
                            $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                        }else{
                        $id_banco_destino = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 0, 4);
                         $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                                    }  
                         $transaccion['TransaccionPendiente']['banco'] = $bancos[$id_banco_destino]; 
                    break;
                    case 'p':
                        $tdc = str_pad(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),20,'0',STR_PAD_RIGHT);
                        $valorTarjeta = array('TNROTARJ' => $tdc);
                        $datos_tdc = $this->Activo->validarBIN($valorTarjeta);
                        
                        if(!isset($datos['NIB090501Return']['error'])){
                            $id_banco_destino = trim(substr($datos_tdc['NIB090501Return']['NIB090501']['TBANCO'],6));
                            $transaccion['TransaccionPendiente']['banco'] = $datos_tdc['NIB090501Return']['NIB090501']['TDESBANCO'];
                        }
                    break;              
                }
            }
            
            $this->set(compact('transaccion','bancos','permiso_aprobacion','usuario'));
            
        } else {
            $this->redirect('/consultas/integral');
        }
    }

    /**
     * Método para la selección de operaciones multiples.
     * 
     */
    function accion_multiple(){

        if(!validarTransaccion(array(54,55,57))) $this->redirect('/consultas/integral'); 


        if(!$this->Session->check('transacciones_pendientes.accion_multiple')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('transacciones_pendientes.accion_multiple');


        
        $usuario = $this->Session->read('usuarios.datos');
        
        
        /** * **/
        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');
        if( $estado['accion']!='eliminar' && (!isset($validacion_tdc['validado']) || !$validacion_tdc['validado']) ){
            $estado['pasos'] = array(
                'Consulta de Aprobaciones',
                'Confirmaci&oacute;n',
                'Tarjeta de Coordenadas',
                'Resultado de la Operaci&oacute;n'
            );
            $estado['paso_final'] = 3;
        }
        else{
            $estado['pasos'] = array(
                'Consulta de Aprobaciones',
                'Confirmaci&oacute;n',
                'Resultado de la Operaci&oacute;n'
            );
            $estado['paso_final'] = 2;
        }
        $this->Session->write('transacciones_pendientes.accion_multiple', $estado);
        /** * **/
        
        
        if(isset($this->data['accion']) && $this->data['accion']=='aceptar'){
            // El usuario presionó aceptar
      
                    
            $estado['confirmada'] = true;
            
            $this->Session->write('transacciones_pendientes.accion_multiple',$estado);
            
            $validacion_tdc = $this->Session->read('seguridad.validar_tdc');
            
            if(
                $estado['accion']!='eliminar' &&
                (
                    $validacion_tdc == null ||
                    !isset($validacion_tdc['validado']) ||
                    !$validacion_tdc['validado']
                )
            ){
                $this->Session->write(
                    'seguridad.validar_tdc',
                    array(
                        'url_aceptar'=>'/transacciones_pendientes/accion_multiple_ejecutar',
                        'layout'=>'interna',
                        'titulo'=>'Aprobaciones Pendientes - '.ucwords($estado['accion']),
                        'pasos'=> $estado['pasos'],
                        'paso_actual'=>2,
                        'seccion_actual'=>'aprobaciones.resumen'
                    )
                );

                $this->redirect('/seguridad/validar_tdc');
               
            } else {
                 $this->redirect('accion_multiple_ejecutar');
                
            }
        }




        $transacciones = $this->TransaccionPendiente->find(
            'all',
            array(
                'conditions'=>array(
                    'TransaccionPendiente.tipo_rif'=>$usuario['tipo_rif'],
                    'TransaccionPendiente.rif'=>$usuario['rif'],
                    'TransaccionPendiente.id'=>$estado['seleccion']
                ),
                'order'=>'TransaccionPendiente.fecha_hora_valor asc'
            )
        );

        
        $cuentas = $this->Activo->buscarInstrumentos2(array('SAV','DDA','NOW'));

        $bancos = $this->Activo->buscarBancos();

        $cuentas_map = array();
        
        foreach($cuentas as $i){
            $cuentas_map[$i['SUMACN']] = $i['SUMAVB'];
        }
        
        $agrupadas = array();
            
        // Traer las operaciones pendientes de bd para evitar fraudes por javascript/firebug
        
        $pendientes_bd = $this->TransaccionPendiente->obtenerAprobacionesPendientes($usuario);

        // Iterar sobre todos los registros seleccionados
        
        $todas = array();
        $tipo_operacion ='';






        foreach($transacciones as &$i){
            // Buscar el banco destino
            $encontrada = false;
            
            foreach($pendientes_bd as $t){
                if($i['TransaccionPendiente']['id']==$t['TransaccionPendiente']['id']){
                    $encontrada = $t;
                    break;
                }
            }
            
            // Si no la encuentra es porque el usuario la metió manulamente por javascript o firebug
            if(!$encontrada) continue;
            
            $permitir = true;
            
            switch($estado['accion']){
                case 'rechazar':
                    // Validar permiso para rechazar
                    if(!validarTransaccion(55)) $permitir=false;
                    
                    // validar que el usuario creador no sea el usuario actual
                    elseif($i['TransaccionPendiente']['usuario_id']==$usuario['id']) $permitir=false;
                    break;
                case 'aprobar':
                    // Validar permiso para aprobar
                    if(!validarTransaccion(54)) $permitir=false;
                    
                    // validar que el usuario tenga permiso de aprobación sobre la transacción
                    elseif(
                        isset($encontrada['permiso_aprobacion']) &&
                        !$encontrada['permiso_aprobacion']
                    ) $permitir=false;
                    
                    
                    // validar que el usuario creador no sea el usuario actual
                    elseif($i['TransaccionPendiente']['usuario_id']==$usuario['id']) $permitir=false;
                
                    break;
                case 'eliminar':
                    // Validar permiso para eliminar
                    if(!validarTransaccion(57)) continue;
                    
                    // validar que el usuario creador sea el usaurio actual
                    elseif($i['TransaccionPendiente']['usuario_id']!=$usuario['id']) $permitir=false;
                    
                    // si ya tiene aprobacionse no se puede eliminar
                    elseif(count($i['AprobacionesRealizadas'])>0) $permitir=false;                  
                    
                    break;
            }
            $tipo_operacion = $i['TransaccionPendiente']['tipo'];


            //cuenta destino simple TV
            if($tipo_operacion == 'rsv'){
                $cuentaD = desencriptar($i['TransaccionPendiente']['cuenta_destino']);
                $arrayCuentaD = explode('/',$cuentaD);
                $ctaDEncriptada = encriptar($arrayCuentaD[0]);
                $i['TransaccionPendiente']['cuenta_destino'] = $ctaDEncriptada;

            }    

            if(!$permitir) continue;
                        
           $bancos = $this->Activo->buscarBancos();
            
            if($tipo_operacion[2]=='o'){
      
                switch($tipo_operacion[0]){
                    case 'r':
                        $id_banco_destino = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),0,4);
                        $transaccion['TransaccionPendiente']['banco'] = $bancos[$id_banco_destino]; 
                    break;
                    case 's':
                        $id_banco_destino = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),0,4);
                        $transaccion['TransaccionPendiente']['banco'] = $bancos[$id_banco_destino]; 
                    break;
                    case 't':                        
                        if(strlen(desencriptar($i['TransaccionPendiente']['cuenta_destino'])) < 19){
                            $id_banco_destino = $i['TransaccionPendiente']['banco'];
                            $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                        }else{
                        $id_banco_destino = substr(desencriptar($i['TransaccionPendiente']['cuenta_destino']), 0, 4);
                         $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                                    }  
                         $i['TransaccionPendiente']['banco'] = $bancos[$id_banco_destino];

                      break;
                    case 'p':
                        $tdc = str_pad(desencriptar($i['TransaccionPendiente']['cuenta_destino']),20,'0',STR_PAD_RIGHT);
                        $valorTarjeta = array('TNROTARJ' => $tdc);
                        $datos_tdc = $this->Activo->validarBIN($valorTarjeta);
                        
                        if(!isset($datos['NIB090501Return']['error'])){
                            $id_banco_destino = trim(substr($datos_tdc['NIB090501Return']['NIB090501']['TBANCO'],6));
                            $i['TransaccionPendiente']['banco'] = $datos_tdc['NIB090501Return']['NIB090501']['TDESBANCO'];
                        }
                    break;              
                }
            }
 
            
            $agrupadas[$i['TransaccionPendiente']['cuenta_origen']][$i['TransaccionPendiente']['tipo'][0]] []= $i;
            $todas []= $i;
        }

        if(empty($todas)) $this->redirect('index');

        $this->set(compact('agrupadas','estado','cuentas_map','todas','bancos','tipo_operacion'));
        
        $this->layout = 'interna';

        $this->set('seccion_actual','aprobaciones.resumen');
    }

    /**
     * Método para le ejecución de la acción de las selección múltiple.
     */
    function accion_multiple_ejecutar(){
   
        if(!validarTransaccion(array(54,55,57))) $this->redirect('/consultas/integral');
    
        if(!$this->Session->check('transacciones_pendientes.accion_multiple')) $this->redirect('/consultas/integral');

        $estado = $this->Session->read('transacciones_pendientes.accion_multiple');     
        $usuario = $this->Session->read('usuarios.datos');      
        $bancos = $this->Activo->buscarBancos();
        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $hubo_error = false;
        
        if(isset($estado['confirmada']) && $estado['confirmada']){
            $res = true;
            $bancos2 = $this->Activo->buscarBancos2();
            // Traer las operaciones pendientes de bd para evitar fraudes por javascript/firebug
            
            $pendientes_bd = $this->TransaccionPendiente->obtenerAprobacionesPendientes($usuario);
            
            // Buscar usuario master de la empresa
            
            $usuario_master = $this->UsuarioCanal->find(
                'first',
                array(
                    'conditions'=>array(
                        'Usuario.tipo_rif'=>$usuario['tipo_rif'],
                        'Usuario.rif'=>$usuario['rif'],
                        'Usuario.rol_juridico_id'=>1,
                        'usuarioCanal.canal_id'=>Configure::read('canal.ib')
                    )
                )
            );
            
            // Buscar los límites diarios
            $limite_externas = null;
            $limite_internas_terceros = null;                        
            $limite_externas_nomina = null;
            $limite_internas_terceros_nomina = null;                        
            $limite_externas_proveedores = null;
            $limite_internas_terceros_proveedores = null;
            $limite_internas_terceros_proveedores_usd = null;
            $limite_internas_terceros_proveedores_eur = null;
            $limite_internas_terceros_nomina_usd = null;
            $limite_internas_terceros_nomina_eur = null;

            if($usuario_master){
                // Si hay master registrado, tomar los límites configurados por él              
                $limite_externas = $usuario_master['UsuarioCanal']['limite_externas'];
                $limite_internas_terceros = $usuario_master['UsuarioCanal']['limite_internas_terceros'];
                $limite_internas_terceros_usd = $usuario_master['UsuarioCanal']['limite_internas_terceros_usd'];
                $limite_internas_terceros_eur = $usuario_master['UsuarioCanal']['limite_internas_terceros_eur'];
                $limite_externas_nomina = $usuario_master['UsuarioCanal']['limite_externas_nomina'];
                $limite_internas_terceros_nomina = $usuario_master['UsuarioCanal']['limite_internas_terceros_nomina'];
                $limite_externas_proveedores = $usuario_master['UsuarioCanal']['limite_externas_proveedores'];
                $limite_internas_terceros_proveedores = $usuario_master['UsuarioCanal']['limite_internas_terceros_proveedores'];
                $limite_vip_master = $usuario_master['UsuarioCanal']['limite_vip'];
                $limite_internas_terceros_nomina_usd = $usuario_master['UsuarioCanal']['limite_interna_terceros_nomina_usd'];
                $limite_internas_terceros_nomina_eur = $usuario_master['UsuarioCanal']['limite_interna_terceros_nomina_eur'];
                $limite_internas_terceros_proveedores_usd = $usuario_master['UsuarioCanal']['limite_interna_terceros_proveedores_usd'];
                $limite_internas_terceros_proveedores_eur = $usuario_master['UsuarioCanal']['limite_interna_terceros_proveedores_eur'];
            }
            
            //Si continua sin límites configurados toma
            // el límite de la configuración del banco                     
            if(empty($limite_externas)){
                $limite_externas = _p('limite_externas_J');
            }
            
            if(empty($limite_internas_terceros)){
                $limite_internas_terceros = _p('limite_internas_terceros_J');
            }
                        
            if(empty($limite_internas_terceros_usd)){
                $limite_internas_terceros_usd = _p('limite_internas_terceros_usd_j');
            }
                        
            if(empty($limite_internas_terceros_eur)){
                $limite_internas_terceros_eur = _p('limite_internas_terceros_eur_j');
            }
                        
            if($usuario_master && is_numeric($limite_vip_master)){
                // Tomamos el límite vip
                $limite_vip = $this->LimitesVip->buscarLimiteById($limite_vip_master);                                          
                $limite_internas_terceros_nomina = $limite_vip['limite_internas_terceros_nomina'];
                $limite_externas_nomina = $limite_vip['limite_externas_nomina'];  
                $limite_internas_terceros_proveedores = $limite_vip['limite_internas_terceros_proveedores'];
                $limite_externas_proveedores = $limite_vip['limite_externas_proveedores'];  
            }else{
                if(empty($limite_externas_nomina)){
                    $limite_externas_nomina = _p('limite_externas_nomina');
                }

                if(empty($limite_internas_terceros_nomina)){
                    $limite_internas_terceros_nomina = _p('limite_internas_terceros_nomina');
                }

                if(empty($limite_externas_proveedores)){
                    $limite_externas_proveedores = _p('limite_externas_proveedores');
                }

                if(empty($limite_internas_terceros_proveedores)){
                    $limite_internas_terceros_proveedores = _p('limite_internas_terceros_proveedores');
                }
                 if (empty($limite_internas_terceros_nomina_usd)) {
                    $limite_internas_terceros_nomina_usd = _p('limite_interna_terceros_nomina_usd');
                }

                if (empty($limite_internas_terceros_nomina_eur)) {
                    $limite_internas_terceros_nomina_eur = _p('limite_interna_terceros_nomina_eur');
                }
                 if (empty($limite_internas_terceros_proveedores_usd)) {
                    $limite_internas_terceros_proveedores_usd = _p('limite_interna_terceros_proveedores_usd');
                }

                if (empty($limite_internas_terceros_proveedores_eur)) {
                    $limite_internas_terceros_proveedores_eur = _p('limite_interna_terceros_proveedores_eur');
                }

            } 
                                                                    
            // Arreglo de resultados
            
            $transacciones = array();

            // Iterar sobre todos los registros seleccionados
            
            foreach($estado['seleccion'] as $i){
                // Comenzar una transacción de base de datos
                $ds = $this->TransaccionPendiente->getDataSource();
                
                $ds->begin($this->TransaccionPendiente);
            
                // Buscar la transacción en la lista de pendientes
                
                $encontrada = false;
                
                foreach($pendientes_bd as $t){
                    if($t['TransaccionPendiente']['id']==$i){
                        $encontrada = $t;
                        break;
                    }
                }
                
                // Si no la encuentra es porque el usuario la metió manualamente por javascript o firebug
                
                if(!$encontrada) $this->redirect('accion_multiple');
                
                // Variables para manejo de errores
                
                $error = 0;
                $descripcion = '';
            
                // Buscar la transacción y validar que el usuario pueda
                // ejercer acción sobre ella
                $transaccion = $this->TransaccionPendiente->find(
                    'first',
                    array(
                        'conditions'=>array(
                            'TransaccionPendiente.id'=>$i
                        )
                    )
                );

                // Buscar el banco destino
                
                if($transaccion['TransaccionPendiente']['tipo'][2]=='o'){
                    $banco_destino = '';
                
                    switch($transaccion['TransaccionPendiente']['tipo'][0]){
                        case 't':
                         if(strlen(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])) < 19){
                            $id_banco_destino = $transaccion['TransaccionPendiente']['banco'];
                            $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                        }else{
                        $id_banco_destino = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 0, 4);
                         $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                                    }  
                         $transaccion['TransaccionPendiente']['banco'] = $bancos[$id_banco_destino];    

                        break;
                        case 'p':
                            $tdc = str_pad(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),20,'0',STR_PAD_RIGHT);
                            $valorTarjeta = array('TNROTARJ' => $tdc);
                            $datos_tdc = $this->Activo->validarBIN($valorTarjeta);
                            
                            if(!isset($datos['NIB090501Return']['error'])){
                                $id_banco_destino = trim(substr($datos_tdc['NIB090501Return']['NIB090501']['TBANCO'],6));
                                $transaccion['TransaccionPendiente']['banco'] = $datos_tdc['NIB090501Return']['NIB090501']['TDESBANCO'];
                            }
                        break;
                    }
                }


                
                // Validar que la operación sea de la empresa del mismo usuario
                
                if(
                    $transaccion['TransaccionPendiente']['rif'] != $usuario['rif'] ||
                    $transaccion['TransaccionPendiente']['tipo_rif'] != $usuario['tipo_rif']
                ){
                    continue;
                }

                $this->TransaccionPendiente->id = $i;

                    if($transaccion['TransaccionPendiente']['tipo'][0] == 'i'){//Pago Seniat

                        $descripcionTP = desencriptar($transaccion['TransaccionPendiente']['descripcion']);
                        $detalle = explode('?', $descripcionTP);
                        $transaccion['TransaccionPendiente']['forma'] = $detalle[1];
                        $transaccion['TransaccionPendiente']['planilla'] = $detalle[2];
                        $transaccion['TransaccionPendiente']['periodo'] = $detalle[3];
                        $transaccion['TransaccionPendiente']['impuesto'] = $detalle[4]." - ".$detalle[5];;
                        $transaccion['TransaccionPendiente']['aduana'] = $detalle[6]." - ".$detalle[7];;
                        $transaccion['TransaccionPendiente']['expediente'] = $detalle[8];
                        $transaccion['TransaccionPendiente']['porcion'] = $detalle[9];
                        $transaccion['TransaccionPendiente']['vencimiento'] = $detalle[10];
                    }

                    //Pago Corpoelec
                    if ($transaccion['TransaccionPendiente']['tipo'] == 'spc') {
                        $descripcionCorp = desencriptar($transaccion['TransaccionPendiente']['descripcion']);
                        $detalle = explode('?', $descripcionCorp);                         
                    }                               

                switch($estado['accion']){
                    case 'rechazar':
                        if(!validarTransaccion(55)) $this->redirect('/transacciones_pendientes/');
                        
                        // Rechazar las transacciones seleccionadas
                        
                        if($transaccion['TransaccionPendiente']['usuario_id']==$usuario['id']) $this->redirect('accion_multiple');
                        
                        $res = $this->TransaccionPendiente->saveField('estatus','ra');
                        
                        $this->AprobacionTransaccion->deleteAll(
                            array(
                                'transaccion_id' => $transaccion['TransaccionPendiente']['id']
                            )
                            ,false
                        );
                        
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            55, 
                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                            ($transaccion['TransaccionPendiente']['tipo'][0]!='c') ? formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']) : null,
                            ( in_array($transaccion['TransaccionPendiente']['tipo'][0], array('n','v')) ) ? $transaccion['TransaccionPendiente']['lote'] :  $transaccion['TransaccionPendiente']['id'],
                            null,
                            $res ? 0 : 1
                        );

                        if(!$res){
                            $error = -1;
                            $descripcion = _m('error.fatal');
                            $this->log($this->TransaccionPendiente->getDataSource()->error,LOG_ERR);
                        }
                        
                        break;
                    case 'eliminar':
                        if(!validarTransaccion(57)) $this->redirect('/transacciones_pendientes/');
                        
                        // Eliminar las transacciones seleccionadas
                        
                        if($transaccion['TransaccionPendiente']['usuario_id']!=$usuario['id']) $this->redirect('accion_multiple');
                        
                        if(count($transaccion['AprobacionesRealizadas'])>0) $this->redirect('accion_multiple');
                        
                        $res = $this->TransaccionPendiente->saveField('estatus','el');
                        
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            57, 
                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                            ($transaccion['TransaccionPendiente']['tipo'][0]!='c') ? formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']) : null,
                            ( in_array($transaccion['TransaccionPendiente']['tipo'][0], array('n','v')) ) ? $transaccion['TransaccionPendiente']['lote'] :  $transaccion['TransaccionPendiente']['id'],
                            null,
                            $res ? 0 : 1
                        );
                        
                        if(!$res){
                            $error = -1;
                            $descripcion = _m('error.fatal');
                            $this->log($this->TransaccionPendiente->getDataSource()->error,LOG_ERR);
                        }
                        
                        break;
                    case 'aprobar':
                        if(!validarTransaccion(54)) $this->redirect('/transacciones_pendientes/');
                        
                        if(
                            isset($encontrada['permiso_aprobacion']) &&
                            !$encontrada['permiso_aprobacion']
                        ) $this->redirect('accion_multiple');
                        
                        // Aprobar las transacciones seleccionadas                      
                        
                        if($transaccion['TransaccionPendiente']['usuario_id']==$usuario['id']) $this->redirect('accion_multiple');
                        
                        $res = $this->TransaccionPendiente->saveField('estatus','pa');

                        // Averiguar si el usuario ya aprobó la transacción con anterioridad
                        $cantidad = $this->AprobacionTransaccion->find(
                            'count',
                            array(
                                'conditions'=>array(
                                    'AprobacionTransaccion.transaccion_id'=>$i,
                                    'AprobacionTransaccion.usuario_id'=>$usuario['id']
                                )
                            )
                        );
                            
                        // Ya aprobó, por lo tanto ignorar
                        if($cantidad > 0) continue;
                        
                        // Si no la ha aprobado, generar el registro
                        $this->AprobacionTransaccion->create();                     
                        
                        $res = $this->AprobacionTransaccion->save(
                            array(
                                'AprobacionTransaccion'=>array(
                                    'transaccion_id'=>$i,
                                    'usuario_id'=>$usuario['id'],
                                    'fecha_hora'=>date('Y-m-d H:i:s')
                                )
                            ),
                            false
                        );
                        
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            54, 
                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                            ($transaccion['TransaccionPendiente']['tipo'][0]!='c') ? formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']) : null,
                            ( in_array($transaccion['TransaccionPendiente']['tipo'][0], array('n','v')) ) ? $transaccion['TransaccionPendiente']['lote'] :  $transaccion['TransaccionPendiente']['id'],
                            null,
                            $res ? 0 : 1
                        );
                        
                        if(!$res) {
                             $error = -1;
                            $descripcion = _m('error.fatal');
                            $this->log($this->AprobacionTransaccion->getDataSource()->error,LOG_ERR);
                            break;
                        }
                        
                        // Revisar la cantidad de aprobaciones a ver si ya se puede ejecutar
                        

                        $aprobaciones_actuales = $this->AprobacionTransaccion->find(
                            'count',
                            array(
                                'conditions'=>array(
                                    'AprobacionTransaccion.transaccion_id'=>$i
                                )
                            )
                        );
                        
                        if($aprobaciones_actuales >= $transaccion['TransaccionPendiente']['aprobaciones_requeridas']){

                           // La transacción alcanzó la cantidad de aprobaciones necesarias
                            // para ser ejecutada
                            
                            
                            // CAMBIO 28/05/2013 Incidencias reportadas, cambio de estatus de EJECUTADA a EN PROCESO
                            
                            /* * /
                            if($transaccion['TransaccionPendiente']['tipo'][2]=='m'){
                                // Las transacciones al mismo banco se ejecutan
                                // inmediatamente
                                $estatus = 'ej';
                            } else {
                                // Las transacciones a otros bancos o mixtas quedan en proceso
                                $estatus = 'ep';
                            }
                            /* */
                            if($transaccion['TransaccionPendiente']['tipo'][0]=='c' || $transaccion['TransaccionPendiente']['tipo'][0]=='r' || $transaccion['TransaccionPendiente']['tipo'][0]=='s'){
                                $estatus = 'ej';
                            }
                            else{
                                $estatus = 'ep';
                            }
                            
                            $this->TransaccionPendiente->id = $i;
                            
                            $res = $this->TransaccionPendiente->saveField(
                                'estatus',
                                $estatus,
                                false
                            );
                            if(!$res) {
                                $error = -1;
                                $descripcion = _m('error.fatal');
                                $this->log($this->TransaccionPendiente->getDataSource()->error,LOG_ERR);
                                break;
                            }
                            
                            // Preparar data para los servicios del gateway                            
                            $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                            $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
                            $cuentaDebitar = trim(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']) ,10));
                            
                            // Buscar el monto acumulado diario POR EMPRESA (OJO)
                            // MGGY Se toma en cuenta el horario de franja de compensacion en caso de que la fehca valor sea el mismo dia                                                        
                            $fecha_acumulado = $this->Activo->consultaFechaCompensacion();
                            $t = strtotime($transaccion['TransaccionPendiente']['fecha_hora_valor']);
                            $temp_fecha = date('Y-m-d',$t);

                            if(in_array($transaccion['TransaccionPendiente']['tipo'][0], array('n','v'))){
                     
                                //Para las internas utiliza la fecha valor del lote 
                                $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $temp_fecha);

                                //Si el lote contiene operaciones de otros bancos y la fecha valor es del mismo dia utiliza la fecha segun la franja de compensacion     
                                if(date('Y-m-d')==$temp_fecha){
                                    $fecha_externa = $fecha_acumulado;
                                }else{
                                    $fecha_externa = $temp_fecha;
                                }
                                $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha_externa);
                                                                                                                                                                                    
                            }elseif($transaccion['TransaccionPendiente']['tipo'][0]!='c'){    
                                $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif']);
                                $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'],$fecha_acumulado);
                            }
                            
                            // Ejecutar la transacción                                                        
                            switch($transaccion['TransaccionPendiente']['tipo']){
                                //Pago y Recarga de Servicios Movilnet
                                case 'stm':
                                case 'scn':
                                case 'rcn': //Recarga Servicios Movilnet
                                    if($transaccion['TransaccionPendiente']['tipo'][0]=='s'){
                                        if($limite_internas_terceros){
                                            $monto_limite = $limite_internas_terceros - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros'];
                                            if($transaccion['TransaccionPendiente']['monto'] > $monto_limite){
                                                $error = -4;
                                                $descripcion = _m('monto.excedido','de la transacción');
                                                break;
                                            }
                                        }
                                    }
                                    
                                    /** Para identificar si es un usuario juridico **/
                                    if($usuario['tipo_rif']){
                                        $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                                    }
                                    else{
                                        $documento_identificacion = desencriptar($usuario['cedula']);
                                    }
                                    
                                    //Buscar las fecha facturacion y fecha proceso
                                    $resPago = '';
                                    $tipo_servicio = $transaccion['TransaccionPendiente']['tipo_servicio'];
                                    $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                                    $datos = $transaccion['TransaccionPendiente'];

                                    if($transaccion['TransaccionPendiente']['tipo_servicio']== 'rtm'){    
                                        $producto = '61';
                                        $ttr = '00';
                                    }else{
                                        $producto = '63';
                                        $ttr = '02';
                                    }

                                    $parametro = array(
                                        'IN8013ACC' => substr(desencriptar($datos['cuenta_origen']), 10),
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
                                        'IN8013MTO' => $datos['monto'],
                                        'IN8013NAU' => '',
                                        'IN8013NCT' => desencriptar($datos['cuenta_destino']),
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
                                    $dtaTransaccion = $res['IN801301Return']['IN801301'];
                                    if (isset($dtaTransaccion['IN8013ERR']) && $dtaTransaccion['IN8013ERR'] == 0) {
                                        if (isset($dtaTransaccion['IN8013CKN'])) {
                                            $referencia = $dtaTransaccion['IN8013CKN'];
                                        }

                                        if($transaccion['TransaccionPendiente']['tipo_servicio'] == 'stm'){
                                            $dataRest = array(
                                                "operadora"         => "movilnet_pospago",
                                                "producto"          => 63,
                                                "numero_contrato"   => desencriptar($datos['cuenta_destino'])
                                            );
                                            $res = $this->Activo->invocaGatewayNexus(
                                                'payall/movilnet/consultaSaldo',
                                                json_encode($dataRest),
                                                'POST'
                                            );

                                            if(is_object($res)){
                                                if($res->codigo_respuesta = "00"){
                                                    $factura = $res->factura;
                                                    $dataRest = array(
                                                        'operadora' => 'movilnet_pospago',
                                                        'producto' => $producto,
                                                        'numero' => trim(desencriptar($datos['cuenta_destino']))."/".trim($factura),
                                                        'monto' => $datos['monto']
                                                    );
                                                }
                                            }
                                        }else{
                                            $dataRest = array(
                                                'operadora' => 'movilnet',
                                                'producto' => $producto,
                                                'numero' => desencriptar($datos['cuenta_destino']),
                                                'monto' => $datos['monto']
                                            );
                                        }
                                        
                                        $operation = $this->Activo->invocaGatewayNexus(
                                            'payall/movilnet/recargar',
                                            json_encode($dataRest),
                                            'POST'
                                        );
                                        
                                        if ( is_object($operation) && isset($operation->success) && ($operation->success == true || $operation->success === 'true') && $operation->codigo_aprobacion != "000000") {
                                            $opData = (array) $operation;

                                            $codigo_aprobacion = isset($opData['codigo_aprobacion']) ? trim(str_replace(array("\r", "\n", "\t"), '', $opData['codigo_aprobacion'])) : '';
                                            $secuencia         = isset($opData['secuencia'])         ? trim(str_replace(array("\r", "\n", "\t"), '', $opData['secuencia']))         : '';
                                            $uuid              = isset($opData['uuid'])              ? trim(str_replace(array("\r", "\n", "\t"), '', $opData['uuid']))              : '';

                                            $confirmacion = $codigo_aprobacion;

                                            $dtaTransaccion['IN8013NAU'] = $codigo_aprobacion; 
                                            
                                            $dtaTransaccion['IN8013NTR'] = $secuencia;
                                            
                                            $dtaTransaccion['IN8013UUI'] = $uuid;

                                            if(empty($dtaTransaccion['IN8013UUI']) && empty($dtaTransaccion['IN8013NTR']) && empty($dtaTransaccion['IN8013NAU'])){
                                                $dtaTransaccion['IN8013IRV'] = 'Y';
                                                if($transaccion['TransaccionPendiente']['tipo_servicio']== 'rtm'){  
                                                    $dtaTransaccion['IN8013TTR'] = '01';
                                                }else{
                                                    $dtaTransaccion['IN8013TTR'] = '03';
                                                }
                                                $res = $this->Activo->invocaMovilnet(array('req' => $dtaTransaccion));
                                            }else{
                                                $res = $this->Activo->invocaMovilnet(array('req' => $dtaTransaccion));
                                            }

                                        } else {
                                            $dtaTransaccion['IN8013IRV'] = 'Y';
                                            $uuid = isset($opData['uuid']) ? trim(str_replace(array("\r", "\n", "\t"), '', $opData['uuid'])) : '';
                                            $dtaTransaccion['IN8013UUI'] = $uuid;
                                            if($transaccion['TransaccionPendiente']['tipo_servicio']== 'rtm'){  
                                                $dtaTransaccion['IN8013TTR'] = '01';
                                            }else{
                                                $dtaTransaccion['IN8013TTR'] = '03';
                                            }
                                            $res = $this->Activo->invocaMovilnet(array('req' => $dtaTransaccion));
                                        }
                                    }

                                    $error = -1;
                                    $descripcion = "Error inesperado, transacci�n no disponible.";                                 
                                    $error_afiliacion = false;

                                    if (isset($res['IN801301Return']['IN801301']['IN8013ERR']) && $res['IN801301Return']['IN801301']['IN8013ERR'] == 0) {
                                        $error = intval($res['IN801301Return']['IN801301']['IN8013ERR']);
                                        $descripcion = $res['IN801301Return']['IN801301']['IN8013ERR'] . ' - ' . $res['IN801301Return']['IN801301']['IN8013DSC'];
                                    }else{
                                        if (isset($res['IN801301Return']['IN801301']['IN8013ERR'])) {
                                            $error = intval($res['IN801301Return']['IN801301']['IN8013ERR']);
                                            $descripcion = $res['IN801301Return']['IN801301']['IN8013ERR'] . ' - ' . $res['IN801301Return']['IN801301']['IN8013DSC'];
                                        }
                                    }

                                    if (isset($operation) && is_object($operation) && $operation->codigo_aprobacion == "000000") {
                                        $error = -1;
                                        $descripcion = $operation->codigo_respuesta . ' - ' . $operation->mensaje;
                                    }else{
                                       $descripcion = $operation->codigo_respuesta . ' - ' . $operation->mensaje;
                                    }

                                    
                                    if($error == 0 ){
                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));
                                        
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            $descripcion,
                                            0
                                        );

                                        $res = $this->TransaccionPendiente->saveField(
                                            'estatus',
                                            'ej',
                                            false
                                        );
                                        
                                        $this->data['RecargaServicios']=array(
                                            'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            'cuentaCreditar'=>null,
                                            'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                            'banco'=>$bancos['0171'],
                                            'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA'=>$referencia,
                                            'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA_OPERADOR'=>$codigo_aprobacion,
                                            'numeroContrato'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])
                                        );

                                        // Email Orden
                                        // Email Options
                                        $this->Email->smtpOptions = Configure::read('email.smtp');
                                        $this->Email->delivery = 'smtp';
                                        $this->Email->from = Configure::read('email.from');
                                        $this->Email->to = desencriptar($usuario['email_principal']);
                                        $this->Email->subject = 'Notificacion Recarga de Servicios';
                                        $this->Email->sendAs = 'html';

                                        // Informacion del correo
                                        $this->Email->template  = 'recargaservicio_ordenante';
                                        $tipo_accion = 'Recarga de Servicio';
                                        switch($transaccion['TransaccionPendiente']['tipo']){
                                            case 'scn':
                                                $tipo_afiliacion = "Pago de servicio de Movilnet Postpago";
                                                break;
                                            case 'rcn':
                                                $tipo_afiliacion = "Recarga de servicio Movilnet";
                                                break;
                                        }
                                        $nombreBeneficiario = $res1['IN0984012Return']['IN098401']['IN984DS1'];
                                        
                                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion','nombreBeneficiario'));

                                        // Envio del correo
                                        $this->Email->send();
                                    
                                    }else{
                                        $this->log($transaccion['TransaccionPendiente']['descripcion'], LOG_ERR);
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015,
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            $transaccion['TransaccionPendiente']['monto'],
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }
                                
                                    break; 
                                //Corpoelect Juridico
                                case 'spc':
                                    $idRegistro = $transaccion['TransaccionPendiente']['id'];
                                    $nroTransaccion = 101102;
                                    $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                                    $transaccionPendienteDetalle = desencriptar($transaccion['TransaccionPendiente']['descripcion']);
                                    $servicio = end($detalle);
                                    //echo "<br> ======================================== <br>";
                                    //var_dump($transaccionPendienteDetalle);
                                    //echo "<br> ======================================== <br>";
                                    //var_dump($servicio);
                                    //echo "<br> ======================================== <br>";
                                    //die();
                                    if($servicio == 1){
                                        $stringData = 
                                        $datos['cuentaDebitar']."?". //IN8003ACC
                                        'VEB'."?". //IN8003CCY
                                        $datos['debts']."?". //IN8003MTO
                                        $datos['contrato']."?".//IN8003CON
                                        $datos['tipoCedula'] . $datos['nroCedula']."?". //IN8003NAC
                                        $datos['grupo']."?". //IN8003GRU
                                        $this->formatearCadena($datos['desc'])."?". //IN8003CPT
                                        desencriptar($usuario['login'])."?".//IN8003USR
                                        $datos['servicio']; 
                                        $arrayData = array(
                                            'IN8003ACC'=> substr($detalle[0],10),
                                            'IN8003CCY'=> $detalle[1],
                                            'IN8003MTO'=> $detalle[2],
                                            'IN8003CON'=> $detalle[3],
                                            'IN8003NAC'=> $detalle[4],
                                            'IN8003GRU'=> $detalle[5],
                                            'IN8003CPT'=> $detalle[6],
                                            'IN8003CAN'=> '04',
                                            'IN8003DIP'=> $ip,
                                            'IN8003USR'=> $detalle[7],
                                            'IN8003IRV'=> 'N',                        
                                        );
            
                                        $res = $this->Activo->invoca('IN800301',array('req'=>$arrayData));
                                        if (empty($res)) {
                                            $error = '0001';
                                            $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error));       
                                        }else{
                                            if($res['IN800301Return']['IN800301']['IN8003ERR'] != 0){
                                                $error = $res['IN800301Return']['IN800301']['IN8003ERR'];
                                                $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error));
                                            }else{
                                                $id =explode(" - ",$detalle[8]);
                                                $debt_id = $id[0];
                                                $amount = $detalle[2];
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
                                                    $cta_debito = enmascara($detalle[0]); 
                                                    $nro_contrato = $detalle[3];
                                                    $monto = $detalle[2];
                                                    $fecha_proceso = date('Y-m-d');
                                                    $concepto = 'PAGO CORPOELEC';
                                                    $referencia = $operation_code;
                                                    $descripcion = $concepto . "-" . $nro_contrato;

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
                                                                    'IN8003ACC'=> substr($detalle[0],10),
                                                                    'IN8003CCY'=> $detalle[1],
                                                                    'IN8003MTO'=> $detalle[2],
                                                                    'IN8003CON'=> $detalle[3],
                                                                    'IN8003NAC'=> $detalle[4],
                                                                    'IN8003GRU'=> $detalle[5],
                                                                    'IN8003CPT'=> $detalle[6],
                                                                    'IN8003CAN'=> '04',
                                                                    'IN8003DIP'=> $ip,
                                                                    'IN8003USR'=> $detalle[7],
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
                                        }
                                    }else if ($servicio == 2){
                                        $cuenta = substr($detalle[0],10);
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
                                    
                                        $arrayData = array(
                                            'IN8008ACC'=>$cuenta,
                                            'IN8008CAN'=>'04',
                                            'IN8008CCY'=>$detalle[1],
                                            'IN8008CKN'=>'0',
                                            'IN8008CON'=>$detalle[2],
                                            'IN8008CPA'=>'6',
                                            'IN8008CUN'=>$detalle[3],
                                            'IN8008D01'=>$this->payAmount(floatval($detalle[4])),
                                            'IN8008D02'=>$this->payAmount(floatval($detalle[5])),
                                            'IN8008D03'=>$this->payAmount(floatval($detalle[6])),
                                            'IN8008DIP'=>$ip,
                                            'IN8008DSC'=>'Pago Corpoelec',
                                            'IN8008ERR'=>'0',
                                            'IN8008FEC'=>date('Ymd'),
                                            'IN8008HOR'=>date('his'),
                                            'IN8008ICO'=>$detalle[7],
                                            'IN8008INS'=>$TipInst,
                                            'IN8008IRV'=>'N',
                                            'IN8008MTO'=>$this->payAmount(floatval($detalle[8])),
                                            'IN8008TMV'=>$detalle[9],
                                            'IN8008USR'=>$detalle[10],
                                        );
                                        
                                        $res = $this->Activo->invocaCorpoelect('IN800801',array('req'=>$arrayData));
                                    
                                        if (empty($res)) {
                                            $error = '0001';
                                            $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error));       
                                        }else{

                                            $operation_code = $res['IN800801Return']['IN800801']['IN8008CKN'];

                                            $contrato =$res['IN800801Return']['IN800801']['IN8008CON'];

                                            $contrato = str_pad(trim($contrato),18,'0',STR_PAD_LEFT);

                                            $keyPago = strtr($this->data['PagoCorpoelec']['deudaPagar'], " ", "_");
                                            
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

                                            $decimales = substr($res['IN800801Return']['IN800801']['IN8008MTO'], -2);
                                            $entero = substr($res['IN800801Return']['IN800801']['IN8008MTO'], 0, -2);
                                            $numeroFormateado = number_format($entero, 0, '', '');
                                            $montoFinal = $numeroFormateado . $decimales;
                                            $montoFinalImpr = $numeroFormateado .".".$decimales;
                                            

                                            $dataRest= array(
                                                "service" => "corpoelect",
                                                "Tipo_de_transaccion" => "T2",
                                                "Codigo_Banco" => "0171",
                                                "Canal_de_pago" => "6",
                                                "Codigo_de_busqueda" => $contrato,
                                                "Fecha_Recaudacion" => $res['IN800801Return']['IN800801']['IN8008FEC'],
                                                "Hora_Recaudacion" => str_pad($res['IN800801Return']['IN800801']['IN8008HOR'], 6 , '0' , STR_PAD_LEFT),
                                                "Respuesta" => "0000",
                                                "Tipo_de_Instrumento" => $TipInst,
                                                "Referencia_instrumento" => "000000000000000000",
                                                "Tipo_movimiento" => str_pad(
                                                    $res['IN800801Return']['IN800801']['IN8008TMV'],
                                                    2,
                                                    '0',
                                                    STR_PAD_LEFT
                                                ),
                                                "Indicador_cobro" => $res['IN800801Return']['IN800801']['IN8008ICO'],
                                                "Monto_Recaudado" => str_pad(
                                                    $montoFinal,
                                                    15,
                                                    '0',
                                                    STR_PAD_LEFT
                                                ),
                                                "Referencia_1" => "000000000000000000",
                                                "Referencia_2" => "000000000000000000",
                                                "Referencia_3" => "00000000000000"
                                            );
                                            
                                            $operation = $this->Activo->invocaGatewayNexus(
                                                "corpoelect",
                                                json_encode($dataRest),
                                                "POST"
                                            );
                                            $response = json_decode(json_encode($operation),true);
                                            
                                            $mOpe = $this->payAmount(floatval($detalle[8])); 

                                            if ($response["Respuesta"]=="0000"){
                                                $this->Email->smtpOptions = Configure::read('email.smtp');
                                                $this->Email->delivery = 'smtp';
                                                $this->Email->from = Configure::read('email.from');
                                                $this->Email->to = desencriptar($usuario['email_principal']);
                                                $this->Email->subject = 'Notificacion de Pago Corpoelec';
                                                $this->Email->sendAs = 'html';
                                                $this->Email->template  = 'pago_corpoelec';

                                                $tipo_operacion = 'Pago Corpoelec';
                                                $cta_debito = enmascara($detalle[0]); 
                                                $nro_contrato = $contrato;
                                                $monto = $montoFinalImpr;
                                                $fecha_proceso = $res['IN800801Return']['IN800801']['IN8008FEC'];
                                                $concepto = 'PAGO CORPOELEC';
                                                $referencia = $operation_code;
                                                $descripcion = $concepto . "-" . $nro_contrato;
                                                
                                                $this->set(compact('tipo_operacion','cta_debito','nro_contrato','monto','fecha_proceso','concepto','referencia','usuario'));

                                                $this->Bitacora->registraBitacora(
                                                    $usuario['id'],
                                                    101102,
                                                    $cta_debito, 
                                                    NULL, 
                                                    str_replace('.', ',', $mOpe),
                                                    $referencia,
                                                    $descripcion,
                                                    0
                                                );
                                                
                                                $this->Email->send();
                                            }else{
                                                $arrayData['IN8008IRV']='Y';
                                                $arrayData['IN8008CKN']=$res['IN800801Return']['IN800801']['IN8008CKN'];
                                                
                                                $res = $this->Activo->invocaCorpoelect('IN800801',array('req'=>$arrayData));
                                               
                                                if ($res['IN800801Return']['IN800801']['IN8008ERR'] == 0) {
                                                    
                                                    // $error = $res['IN8003ERR'];
                                                    $error = $response["Respuesta"];
                                                    $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error)); 
                                                }else{
                                                    // $error = $res['IN8003ERR'];
                                                    $error = $response["Respuesta"];
                                                    $this->redirect(array('controller'=>'/pagos_corpoelec/pago_corpoelec_fallida','action'=>$error));
                                                }

                                                $error = 0001;

                                                $this->Bitacora->registraBitacora(
                                                    $usuario['id'], 
                                                    101102,
                                                    NULL, 
                                                    encriptar($datos['cuentaDebitar']), 
                                                    str_replace('.', ',', $mOpe),
                                                    $res['IN800801Return']['IN800801']['IN8008CKN'],
                                                    encriptar($datos['desc']),
                                                    0
                                                );
                                            }
                                        }
                                    }
                                break;
                                case 'rsv':
                                    //Recarga SIMPLE TV 
                                    //id de la tranferencia
                                    $idRegistro = $transaccion['TransaccionPendiente']['id'];  


                                   $nroTransaccion = 102100;
                                    $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);                                  
                                    if($usuario['tipo_rif']){
                                        $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                                        $login = desencriptar($usuario['login']);
                                    }
                                    else{
                                        $documento_identificacion = desencriptar($usuario['cedula']);
                                        $login = desencriptar($usuario['login']);
                                    }

                                    $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']; 


                                    $contratoCustom = desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']);  
                                    $arrayContrato = explode('/', $contratoCustom);
                                    $nroContrato = $arrayContrato[0];
                                    
                                    $custonID = $arrayContrato[1];
                                    $transaccion['TransaccionPendiente']['cuenta_destino'] = encriptar($nroContrato);
                                

                                    //Realizar el debito As400 
                                    $montoRec = $transaccion['TransaccionPendiente']['monto'];
                                    $montoRec = str_replace(',','.',$montoRec);
                                    $transaccion['TransaccionPendiente']['monto'] = $montoRec;
                                    $ctaDebitar = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']), 10); 

                             
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


                                    $swAS400 = 0;
                                    if(!$res){
                                        $swValidar = 1;
                                        $swAS400 = 1;
                                        $ruta = 'no_recarga/'.$swValidar;
                                        $this->redirect($ruta);                  
                                    }else{
                                        $error = $res['IN800201Return']['IN800201']['IN8002ERR']; 
                                        //si existe un error desde el as400, lo registra en bitacora y lo redirecciona
                                        //a una pantalla de error.     
                                        if($error > 0){
                                            $swAS400 = 1;
                                            //registrar log
                                            //INDICAR EL ERROR y EESCRIBIRLO
                                            $descripcion = $res['IN800201Return']['IN800201']['IN8002MSE'];
                                            //INDICAR EL ERROR y ESCRIBIRLO  
                                            $this->set(compact('descripcion'));
                                            $this->log('Transacciones Pendiente / Recarga Simple TV - ' . $descripcion . ' - ' . $res, LOG_ERR);
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
                                        //obtener Custom Id del cliente simple TV
                                        //esta referencia viene del 400 y se completa con cero a la derecha

                                     
                                        $referenciaOrig = $res['IN800201Return']['IN800201']['IN8002CKN'];
                                        $referencia = str_pad($referenciaOrig,8,'0',STR_PAD_LEFT);
                                        $transaccion['TransaccionPendiente']['id'] = $referencia; 

                                        //$this->data['RecargaSimpletv']['REFERENCIA'] = $referencia;
                                        $montoRec = $transaccion['TransaccionPendiente']['monto']; 

                                        $userSTV = Configure::read('Simple_TV.user');
                                        $passwordSTV = Configure::read('Simple_TV.pwd');
                                        $ledgerAccount = Configure::read('Simple_TV.ledgerAccount'); 
                                        $urlPaid = Configure::read('Simple_TV.urlPaid'); 

                                        


                                        $arrayPaid = array(
                                                'customerId'=>$custonID,
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
                                            if($res->error == 0){
                                                
                                                $diaProgramacionActual = $res->daysProgramming; 
                                                $fechaDeconexion = $res->dateDeconexion;  
                                                $referenciaOperadora = $res->transactionId;
                                                $infSimple = trim($referenciaOperadora) . ";" . trim($diaProgramacionActual) .";". trim($fechaDeconexion);
                                                $transaccion['TransaccionPendiente']['inf_simpletv'] = $infSimple;

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
                                                $this->log('Transacciones Pendiente / Recarga Simple TV - ' . $descripcion . ' - ' . $res, LOG_ERR);
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

                                                $ruta = 'no_recarga/'.$errorSimple . '/' . $mensajeVta;
                                                $this->redirect($ruta);
                                            }
                                        } 
                                        //fin pago de simple tv 

                                    }    

                                    
                                    $descripcion = "Recarga Simpletv exitosa";

                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));

                                    $this->data['RecargaServicios'] = array(
                                        'cuentaDebitar' => desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                        'numeroContrato' => $nroContrato,
                                        'nombreBeneficiario' => desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                        'banco' => $bancos['0171'],
                                        'monto' => formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                        'CONCEPTO' => desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                        'REFERENCIA' => $referenciaOrig,
                                        'REFERENCIA_OPERADOR' => $referenciaOperadora,
                                        'descripcion' => desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                        'diaProgramacionActual'=>$diaProgramacionActual,
                                        'fechaDeconexion'=>$fechaDeconexion
                                        );


                                        // Email Orden
                                        // Email Opciones
                                        $this->Email->smtpOptions = Configure::read('email.smtp');
                                        $this->Email->delivery = 'smtp';
                                        $this->Email->from = Configure::read('email.from');
                                        $this->Email->to = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];
                                        $this->Email->subject = 'Notificacion Recarga de Simpletv';
                                        $this->Email->sendAs = 'html';

                                        // Informacion del correo
                                        $this->Email->template = 'recargaservicio_ordenante';
                                        $tipo_accion = 'Recarga de Servicio';
                                        $tipo_afiliacion = 'Recarga Simpletv';
                                        $nombreBeneficiario = desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']);
                                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));
                                        // Envio del correo
                                        $this->Email->send();

                                       //Modificar informacion simple
                                    break;  

                                case 'ssi': 
                                     //Identificar el tipo de pago                                   
                                    $this->data['PagoServicios']['operacion'] = 'Pago de servicio Inter';
                                    
                                    $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                                    $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'], 10, '0', STR_PAD_LEFT);
                                    $tipo_servicio = $transaccion['TransaccionPendiente']['tipo_servicio'];

                                    /** Para identificar si es un usuario juridico **/
                                    if ($usuario['tipo_rif']) {
                                        $documento_identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
                                    } else {
                                        $documento_identificacion = $usuario['tipo_cedula'] . str_pad(desencriptar($usuario['cedula']), 9, '0', STR_PAD_LEFT);
                                    }
                                    /*Para identificar si es un usuario jurídico*/

                                        $modalidad = 40;
                                               $data = array(
                                                'req'=>array(
                                                    'compania'=>2,
                                                    'nrodocumento'=>$identificacion,
                                                    'nrocontrato'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),                                              
                                                    'servicio'=>2,
                                                    'canal'=>'04',                                                      
                                                    'ope'=>2, 
                                                    'cuentadebito'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20),10,'0',STR_PAD_LEFT),
                                                    'monto'=>$transaccion['TransaccionPendiente']['monto'],
                                                    'ip'=>$ip,
                                                    'reverso'=>'N',
                                                    'refreverso'=>'000000000',

                                                )
                                        );
                                 
                                    $resPago = '';
                                    $resPago = $this->Activo->invoca(
                                        'Celupagos',
                                        $data
                                    );
                                    
                                    $error = -1;
                                    $error_afiliacion = false;

                                    $referencia = str_pad($resPago['CelupagosReturn']['celupagos']['celupagos']['referenciab'], 9, '0', STR_PAD_LEFT);
                                    $confirmacion = str_pad($resPago['CelupagosReturn']['celupagos']['celupagos']['reference'], 9, '0', STR_PAD_LEFT);

                                    if (isset($resPago['CelupagosReturn'])) {
                                        $error = 0;
                                        if ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 1) {
                                            $error = 0;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 204) {
                                            $error = 13;
                                        }elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 2) {
                                            $error = 0;
                                        }
                                        elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3) {
                                            $error = 0;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 89) {
                                            $error = 208;
                                        }
                                        elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3001) {
                                            $error = 208;
                                        } 
                                        elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3003) {
                                            $error = 208;
                                        }  else {
                                            $error = 208;
                                        }
                                    }

                                    if ($error == 0) {

                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));

                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015,
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($descripcion),
                                            0
                                        );

                                        $this->data['RecargaServicios'] = array(
                                            'cuentaDebitar' => desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            'numeroContrato' => desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            'nombreBeneficiario' => desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                            'banco' => $bancos['0171'],
                                            'monto' => formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            'CONCEPTO' => desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA' => $referencia,
                                            'REFERENCIA_OPERADOR' => $confirmacion,
                                            'descripcion' => desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                        );

                                        if (_p('notificacion.digitel') == '1' || _p('notificacion.digitel') == '3') {
                                            // Email Orden
                                            // Email Opciones
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];
                                            $this->Email->subject = 'Notificacion Recarga de Servicios';
                                            $this->Email->sendAs = 'html';

                                            // Informacion del correo
                                            $this->Email->template = 'recargaservicio_ordenante';
                                            $tipo_accion = 'Recarga de Servicio';
                                            $tipo_afiliacion = 'Recarga Digitel';
                                            $nombreBeneficiario = desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']);

                                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                                            // Envio del correo
                                            $this->Email->send();
                                        }

                                        if (_p('notificacion.digitel') == '2' || _p('notificacion.digitel') == '3') {
                                            // Enviar por sms la notificación de que realizó la recarga al ordenante        
                                            $this->Sms->enviar(
                                                array(
                                                    'numcel' => $datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR'],
                                                    'canal1' => '59',
                                                    'fectim' => 'Digitel',
                                                    'cheque' => substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 2),
                                                    'cuenta' => substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']), 16, 4),
                                                    'monto' => $transaccion['TransaccionPendiente']['monto'],
                                                    'fecha' => date('Ymd'),
                                                    'hora' => date('His'),
                                                    'refnum' => substr($referencia, 5, 4),
                                                    'xrnde38' => 'IB'
                                                )
                                            );
                                        }
                                    } else {
                                        $this->log($transaccion['TransaccionPendiente']['descripcion'] . ' - ' . $resPago, LOG_ERR);
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015,
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            $transaccion['TransaccionPendiente']['monto'],
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }

                                    break;                              
                                case 'rsi':
  
                                    //Identificar el tipo de pago                                   
                                    $this->data['PagoServicios']['operacion'] = 'Recarga Inter';

                                    $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                                    $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'], 10, '0', STR_PAD_LEFT);
                                    $tipo_servicio = $transaccion['TransaccionPendiente']['tipo_servicio'];

                                    /** Para identificar si es un usuario juridico **/
                                    if ($usuario['tipo_rif']) {
                                        $documento_identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
                                    } else {
                                        $documento_identificacion = $usuario['tipo_cedula'] . str_pad(desencriptar($usuario['cedula']), 9, '0', STR_PAD_LEFT);
                                    }
                                    /*Para identificar si es un usuario jurídico*/

                                        $modalidad = 40;
                                               $data = array(
                                                'req'=>array(
                                                    'compania'=>2,
                                                    'nrodocumento'=>$identificacion,
                                                    'nrocontrato'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),                                              
                                                    'servicio'=>1,
                                                    'canal'=>'04',                                                      
                                                    'ope'=>2, 
                                                    'cuentadebito'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20),10,'0',STR_PAD_LEFT),
                                                    'monto'=>$transaccion['TransaccionPendiente']['monto'],
                                                    'ip'=>$ip,
                                                    'reverso'=>'N',
                                                    'refreverso'=>'000000000',
                                                    
                                                )
                                    );
                                 
                                    $resPago = '';
                                    $resPago = $this->Activo->invoca(
                                        'Celupagos',
                                        $data
                                    );
                                           
                                    $error = -1;
                                    $error_afiliacion = false;

                                    $referencia = str_pad($resPago['CelupagosReturn']['celupagos']['celupagos']['referenciab'], 9, '0', STR_PAD_LEFT);
                                    $confirmacion = str_pad($resPago['CelupagosReturn']['celupagos']['celupagos']['reference'], 9, '0', STR_PAD_LEFT);

                                    if (isset($resPago['CelupagosReturn'])) {
                                        $error = 0;
                                        if ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 1) {
                                            $error = 00;
                                        }
                                        elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 2) {
                                            $error = 00;
                                        }
                                         elseif($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3) {
                                            $error = 00;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 204) {
                                            $error = 8;
                                        } 
                                        elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3001) {
                                            $error = 208;
                                        }elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3003) {
                                            $error = 208;
                                        }elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 89) {
                                            $error = 208;
                                        } else {
                                            $error = 208;
                                        }
                                  
                                    }

                                    if ($error == 0) {

                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));

                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015,
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($descripcion),
                                            0
                                        );

                                        $this->data['RecargaServicios'] = array(
                                            'cuentaDebitar' => desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            'numeroContrato' => desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            'nombreBeneficiario' => desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                            'banco' => $bancos['0171'],
                                            'monto' => formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            'CONCEPTO' => desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA' => $referencia,
                                            'REFERENCIA_OPERADOR' => $confirmacion,
                                            'descripcion' => desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                        );

                                        if (_p('notificacion.digitel') == '1' || _p('notificacion.digitel') == '3') {
                                            // Email Orden
                                            // Email Opciones
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];
                                            $this->Email->subject = 'Notificacion Recarga de Servicios';
                                            $this->Email->sendAs = 'html';

                                            // Informacion del correo
                                            $this->Email->template = 'recargaservicio_ordenante';
                                            $tipo_accion = 'Recarga de Servicio';
                                            $tipo_afiliacion = 'Recarga Digitel';
                                            $nombreBeneficiario = desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']);

                                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                                            // Envio del correo
                                            $this->Email->send();
                                        }

                                        
                                    } else {
                                        $this->log($transaccion['TransaccionPendiente']['descripcion'] . ' - ' . $resPago, LOG_ERR);
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015,
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            $transaccion['TransaccionPendiente']['monto'],
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }

                                    break;                          
                                //Recarga de Servicios Digitel Prepago
                                case 'rcd':  
                                   //Identificar el tipo de pago                                   
                                    $this->data['PagoServicios']['operacion'] = 'Recarga Digitel';
                                                                        
                                    $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                                    $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
                                    $tipo_servicio = $transaccion['TransaccionPendiente']['tipo_servicio'];

                                    /** Para identificar si es un usuario juridico **/
                                    if($usuario['tipo_rif']){
                                        $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                                    } else {
                                        $documento_identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                                    }
                                    /*Para identificar si es un usuario jurídico*/
                                    
                                    if($tipo_servicio == 'rtm'){
                                            $modalidad = 40;
                                            $data = array(
                                                        'req'=>array(
                                                        'compania'=>1,
                                                        'nrodocumento'=>$identificacion,
                                                        'telefono'=>'58'.substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),1),                                              
                                                        'servicio'=>1,
                                                        'canal'=>'04',                                                      
                                                        'ope'=>2, 
                                                        'cuentadebito'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20),10,'0',STR_PAD_LEFT),
                                                        'monto'=>$transaccion['TransaccionPendiente']['monto'],
                                                        'ip'=>$ip,
                                                        'reverso'=>'N',
                                                        'refreverso'=>'000000000'

                                                        )
                                            );
                                    }elseif($tipo_servicio == 'rtf'){
                                            $modalidad = 41;
                                            $data = array(
                                                        'req'=>array(
                                                        'compania'=>1,
                                                        'nrodocumento'=>$identificacion,
                                                        'telefono'=>'58'.substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),1),                                                        
                                                        'servicio'=>1,
                                                        'canal'=>'04',                                                      
                                                        'ope'=>2, 
                                                        'cuentadebito'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20),10,'0',STR_PAD_LEFT),
                                                        'monto'=>$transaccion['TransaccionPendiente']['monto'],
                                                        'ip'=>$ip,
                                                        'reverso'=>'N',
                                                        'refreverso'=>'000000000'

                                                        )
                                            );
                                    }elseif($tipo_servicio == 'rim'){
                                            $modalidad = 40;
                                            $data = array(
                                                        'req'=>array(
                                                        'compania'=>1,
                                                        'nrodocumento'=>$identificacion,
                                                        'telefono'=>'58'.substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),1),                                                
                                                        'servicio'=>1,
                                                        'canal'=>'04',                                                      
                                                        'ope'=>2, 
                                                        'cuentadebito'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20),10,'0',STR_PAD_LEFT),
                                                        'monto'=>$transaccion['TransaccionPendiente']['monto'],
                                                        'ip'=>$ip,
                                                        'reverso'=>'N',
                                                        'refreverso'=>'000000000'

                                                        )
                                            );
                                   }

                                    $resPago = '';
                                    $resPago = $this->Activo->invoca(
                                            'Celupagos',
                                            $data
                                    );  

                                    $error = -1;
                                    $error_afiliacion = false;                                  

                                    $referencia = str_pad($resPago['CelupagosReturn']['celupagos']['celupagos']['referenciab'], 9, '0', STR_PAD_LEFT);
                                    $confirmacion = str_pad($resPago['CelupagosReturn']['celupagos']['celupagos']['reference'], 9, '0', STR_PAD_LEFT);

                                    if(isset($resPago['CelupagosReturn'])){
                                        $error = 0;
                                        if($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode']=='00'){
                                            $error = 00;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] ==05) {
                                            $error = 05;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] ==07) {
                                            $error = 07;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] ==13) {
                                            $error = 13;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 19) {
                                            $error = 05;
                                        }elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 21) {
                                            $error = 208;
                                        }elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 22) {
                                            $error = 05;
                                        
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 89) {
                                            $error = 208;
                                        }else{
                                        $error = 208;
                                        }
                                    }     
                                       
                                    if($error == 0 ){

                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));
                                        
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($descripcion),
                                            0
                                        );
                                        
                                        $this->data['RecargaServicios']=array(
                                            'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            'numeroContrato'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                            'banco'=>$bancos['0171'],
                                            'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA'=>$referencia,
                                            'REFERENCIA_OPERADOR'=>$confirmacion,    
                                            'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                        );

                                        if(_p('notificacion.digitel')=='1' || _p('notificacion.digitel')=='3'){
                                            // Email Orden
                                            // Email Opciones
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];
                                            $this->Email->subject = 'Notificacion Recarga de Servicios';
                                            $this->Email->sendAs = 'html';

                                            // Informacion del correo
                                            $this->Email->template = 'recargaservicio_ordenante';
                                            $tipo_accion = 'Recarga de Servicio';
                                            $tipo_afiliacion = 'Recarga Digitel';
                                            $nombreBeneficiario = desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']);

                                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion','nombreBeneficiario'));

                                            // Envio del correo
                                            $this->Email->send();
                                        }                                       

                                        if(_p('notificacion.digitel')=='2' || _p('notificacion.digitel')=='3'){
                                            // Enviar por sms la notificación de que realizó la recarga al ordenante        
                                            $this->Sms->enviar(
                                                    array(
                                                            'numcel'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR'],
                                                            'canal1'=>'59',
                                                            'fectim'=>'Digitel',
                                                            'cheque'=>substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),2),
                                                            'cuenta'=>substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),16,4),
                                                            'monto'=>$transaccion['TransaccionPendiente']['monto'],
                                                            'fecha'=>date('Ymd'),
                                                            'hora'=>date('His'),
                                                            'refnum'=>substr($referencia,5,4),
                                                            'xrnde38'=>'IB'
                                                    )
                                            );
                                        }                                                                                
                                    }else{
                                        $this->log($transaccion['TransaccionPendiente']['descripcion'] . ' - ' . $resPago, LOG_ERR);
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015,
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            $transaccion['TransaccionPendiente']['monto'],
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }                                
                                    break;
                                //Recarga de Servicios Digitel Postpago
                                case 'scd': 

                                    //Identificar el tipo de pago                                   
                                    $this->data['PagoServicios']['operacion'] = 'Pago de servicio Digitel';
                                                                        
                                    $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                                    $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);
                                    $tipo_servicio = $transaccion['TransaccionPendiente']['tipo_servicio'];

                                    /** Para identificar si es un usuario juridico **/
                                    if($usuario['tipo_rif']){
                                        $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                                    } else {
                                        $documento_identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                                    }
                                    /*Para identificar si es un usuario jurídico*/
                                    
                                    if($tipo_servicio == 'stm'){
                                            $modalidad = 40;
                                            $data = array(
                                                     'req'=>array(
                                                     'compania'=>1,
                                                     'nrodocumento'=>$identificacion,
                                                     'telefono'=>'58'.substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),1),                                              
                                                     'servicio'=>2,
                                                     'canal'=>'04',                                                      
                                                     'ope'=>2, 
                                                     'cuentadebito'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20), 12, '0', STR_PAD_LEFT),
                                                     'monto'=>$transaccion['TransaccionPendiente']['monto'],
                                                     'ip'=>$ip,
                                                     'referenciaD'=> str_pad($transaccion['TransaccionPendiente']['lote'],10,'0',STR_PAD_LEFT),
                                                     'reverso'=>'N',
                                                     'refreverso'=>'000000000'
                                                        )
                                            );




                                    }elseif($tipo_servicio == 'stf'){
                                            $modalidad = 41;
                                            $data = array(
                                                  'req'=>array(
                                                      'compania'=>1,
                                                      'nrodocumento'=>$identificacion,
                                                      'telefono' => '58' .substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 1),
                                                      'servicio'=>2,
                                                      'canal'=>'04',                                                      
                                                      'ope'=>2, 
                                                      'cuentadebito'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20),12, '0', STR_PAD_LEFT),
                                                      'monto'=>$transaccion['TransaccionPendiente']['monto'],
                                                      'ip'=>$ip,
                                                      'referenciaD'=> str_pad($transaccion['TransaccionPendiente']['lote'],10,'0',STR_PAD_LEFT),
                                                      'reverso'=>'N',
                                                    'refreverso'=>'000000000'

                                                           )
                                              );


                                    }elseif($tipo_servicio == 'sim'){
                                            $modalidad = 40;
                                            $data = array(
                                                'req'=>array(
                                                    'compania'=>1,
                                                    'nrodocumento'=>$identificacion,
                                                    'telefono'=>'58'.substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),1),                                              
                                                    'servicio'=>2,
                                                    'canal'=>'04',                                                      
                                                    'ope'=>2, 
                                                    'cuentadebito'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20), 12, '0', STR_PAD_LEFT),
                                                    'monto'=>$transaccion['TransaccionPendiente']['monto'],

                                                    'ip'=>$ip,
                                                    'referenciaD'=> str_pad($transaccion['TransaccionPendiente']['lote'],10,'0',STR_PAD_LEFT),
                                                    'reverso'=>'N',
                                                    'refreverso'=>'000000000'
                                                ),
                                        );
                                    }  

                                    $resPago = '';
                                    $resPago = $this->Activo->invoca(
                                            'Celupagos',
                                            $data
                                    ); 


                                    $error = -1;
                                    $error_afiliacion = false;                                  
                                    $referencia = str_pad($resPago['CelupagosReturn']['celupagos']['celupagos']['referenciab'], 9, '0', STR_PAD_LEFT);
                                    $confirmacion = str_pad($resPago['CelupagosReturn']['celupagos']['celupagos']['reference'], 9, '0', STR_PAD_LEFT);
                                                                        
                                    if(isset($resPago['CelupagosReturn'])){
                                          $error = 0;
                                        if ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0000') {
                                            $error = 00;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0001') {
                                            $error = 208;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0005') {
                                            $error = 8;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0002') {
                                            $error = 208;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-OM0003') {
                                            $error = 208;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 'PFDT-O13724-EM0001') {
                                            $error = 208;
                                        } elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == '89') {
                                            $error = 208;
                                        } 
                                        elseif ($resPago['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == '204') {
                                            $error = 208;
                                        } else {
                                            $error = 208;
                                        }
                                    }
                                    if($error == 0 ){
                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));
                                        
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($descripcion),
                                            0
                                        );
                                        
                                        $this->data['RecargaServicios']=array(
                                            'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            'numeroContrato'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                            'banco'=>$bancos['0171'],
                                            'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA'=>$referencia,
                                            'REFERENCIA_OPERADOR'=>$confirmacion,    
                                            'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                        );

                                        if(_p('notificacion.digitel')=='1' || _p('notificacion.digitel')=='3'){
                                            // Email Orden
                                            // Email Opciones
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];
                                            $this->Email->subject = 'Notificacion Recarga de Servicios';
                                            $this->Email->sendAs = 'html';

                                            // Informacion del correo
                                            $this->Email->template = 'recargaservicio_ordenante';
                                            $tipo_accion = 'Recarga de Servicio';
                                            $tipo_afiliacion = 'Recarga Digitel';
                                            $nombreBeneficiario = desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']);

                                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion','nombreBeneficiario'));

                                            // Envio del correo
                                            $this->Email->send();
                                        }                                       

                                        if(_p('notificacion.digitel')=='2' || _p('notificacion.digitel')=='3'){
                                            // Enviar por sms la notificación de que realizó la recarga al ordenante        
                                            $this->Sms->enviar(
                                                array(
                                                    'numcel'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR'],
                                                    'canal1'=>'59',
                                                    'fectim'=>'Digitel',
                                                    'cheque'=>substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),2),
                                                    'cuenta'=>substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),16,4),
                                                    'monto'=>$transaccion['TransaccionPendiente']['monto'],
                                                    'fecha'=>date('Ymd'),
                                                    'hora'=>date('His'),
                                                    'refnum'=>substr($referencia,5,4),
                                                    'xrnde38'=>'IB'
                                                )
                                            );
                                        }                                                                                
                                    }else{
                                        $this->log($transaccion['TransaccionPendiente']['descripcion'] . ' - ' . $resPago, LOG_ERR);
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];


                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015,
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            $transaccion['TransaccionPendiente']['monto'],
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }
                                
                                    break;
                                //Recarga de Servicios Movistar Prepago
                                case 'rcm':                                 
                                    // Verificar el límite acumulado de la transacción

                                    // Validar el límite sólo si el mismo es diferente de null
                                    // ya que en ese caso se considera sin límites
                                    if($limite_internas_terceros){
                                        $monto_limite = $limite_internas_terceros - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros'];

                                        if($transaccion['TransaccionPendiente']['monto'] > $monto_limite){
                                            $error = -4;
                                            $descripcion = _m('monto.excedido','de la transacción');
                                            break;
                                        }
                                    }
                                    
                                    //Identificar el tipo de pago                                   
                                    $this->data['PagoServicios']['operacion'] = 'Movistar';
                                    $tipo_servicio = $transaccion['TransaccionPendiente']['tipo_servicio'];
                                    $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                                    $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);

                                    /** Para identificar si es un usuario juridico **/
                                    if($usuario['tipo_rif']){
                                        $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                                    }
                                    else{
                                        $documento_identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                                    }
                                    /*Para identificar si es un usuario jurídico*/
                                    
                                    if($tipo_servicio == 'rtm' || $tipo_servicio == 'rim'){
                                            $modalidad = 70;                                                                                
                                    }elseif($tipo_servicio == 'rtf'){
                                            $modalidad = 71;                                                                               
                                    }elseif($tipo_servicio == 'rtv'){
                                            $modalidad = 72;                                                                                
                                    }             

                                    $resPago = '';
                                    $resPago = $this->Activo->invoca(
                                            'IN098801',
                                            array(
                                                'req'=>array(
                                                    'IN988IDUS'=>$documento_identificacion,
                                                    'IN988NCLT'=>$identificacion,
                                                    'IN988DS2'=>str_pad(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),40,'0',STR_PAD_LEFT),
                                                    'IN988TYPE'=>11,
                                                    'IN988CHAN'=>4,                                                                                                  
                                                    'IN988CSER'=>$modalidad,
                                                    'IN988TSER'=>1, 
                                                    'IN988FACC'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20),12,'0',STR_PAD_LEFT),
                                                    'IN988AMNT'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                    'IN988IPCLT'=>$ip
                                                )
                                            )
                                    );                                                                                                          

                                    $error = -1;
                                    $error_afiliacion = false;                                  

                                    if(isset($resPago['IN098801Return'])){
                                        $error = 0;
                                        if(isset($resPago['IN098801Return']['error'])){
                                            if(!empty($resPago['IN098801Return']['error']['ERDR01'])){
                                                $error = intval($resPago['IN098801Return']['error']['ERDR01']);
                                            }else{
                                                $error = intval($resPago['IN098801Return']['error']['ERNU01']);
                                            }
                                            $descripcion = 'Movistar ' .$resPago['IN098801Return']['error']['ERDR01'] . '-' . 'MYPS ' . $resPago['IN098801Return']['error']['ERNU01'] . ' - ' . $resPago['IN098801Return']['error']['ERDS01'];                                                                            
                                        }                                                                                                                                               
                                    }
                                                                        
                                    if($error == 0 ){
                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));
                                        
                                        $referencia = str_pad($resPago['IN098801Return']['IN098801']['IN988CTRA'],9,'0',STR_PAD_LEFT);
                                        $confirmacion = str_pad($resPago['IN098801Return']['IN098801']['IN988CTRA'],9,'0',STR_PAD_LEFT);
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($descripcion),
                                            0
                                        );
                                        
                                        $this->data['RecargaServicios']=array(
                                            'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            'numeroContrato'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                            'banco'=>$bancos['0171'],
                                            'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA'=>$referencia,
                                            'REFERENCIA_OPERADOR'=>$confirmacion,    
                                            'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                        );                                                                               

                                        if(_p('notificacion.movistar')=='1' || _p('notificacion.movistar')=='3'){                                
                                            // Email Orden
                                            // Email Opciones
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];
                                            $this->Email->subject = 'Notificacion Recarga de Servicios';
                                            $this->Email->sendAs = 'html';

                                            // Informacion del correo
                                            $this->Email->template = 'recargaservicio_ordenante';
                                            $tipo_accion = 'Recarga de Servicio';
                                            $tipo_afiliacion = 'Movistar';
                                            $nombreBeneficiario = desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']);                                                                                   

                                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion','nombreBeneficiario'));
                                            // Envio del correo
                                            $this->Email->send();
                                        }                            

                                        if(_p('notificacion.movistar')=='2' || _p('notificacion.movistar')=='3'){
                                            // Enviar por sms la notificación de que realizó la recarga al ordenante        
                                            $this->Sms->enviar(array(
                                                'numcel'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR'],
                                                'canal1'=>'59',
                                                'fectim'=>'Movistar',
                                                'cheque'=>substr(desencriptar($this->data['TransaccionPendiente']['cuenta_destino']),2),
                                                'cuenta'=>substr(desencriptar($this->data['TransaccionPendiente']['cuenta_origen']),16,4),
                                                'monto'=>$this->data['TransaccionPendiente']['monto'],
                                                'fecha'=>date('Ymd'),
                                                'hora'=>date('His'),
                                                'refnum'=>substr($referencia,5,4),
                                                'xrnde38'=>'IB'
                                            ));
                                        }                                                                                                                                                                                                                                              
                                    
                                    }else{
                                        $this->log($transaccion['TransaccionPendiente']['descripcion'] . ' - ' . $res, LOG_ERR);
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015,
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            $transaccion['TransaccionPendiente']['monto'],
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }                               
                                    break;                                                                        
                                //Servicios Movistar PostPago
                                case 'scm':                                 
                                    // Verificar el límite acumulado de la transacción

                                    // Validar el límite sólo si el mismo es diferente de null
                                    // ya que en ese caso se considera sin límites
                                    if($limite_internas_terceros){
                                        $monto_limite = $limite_internas_terceros - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros'];

                                        if($transaccion['TransaccionPendiente']['monto'] > $monto_limite){
                                            $error = -4;
                                            $descripcion = _m('monto.excedido','de la transacción');
                                            break;
                                        }
                                    }
                                    
                                    //Identificar el tipo de pago                                   
                                    $this->data['PagoServicios']['operacion'] = 'Movistar';
                                                                        
                                    $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                                    $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],10,'0',STR_PAD_LEFT);                                                                        
                                    $this->data['PagoServicios']['operacion'] = 'Movistar';
                                    $tipo_servicio = $transaccion['TransaccionPendiente']['tipo_servicio'];


                                    /** Para identificar si es un usuario juridico **/
                                    if($usuario['tipo_rif']){
                                        $documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
                                    }
                                    else{
                                        $documento_identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                                    }
                                    /*Para identificar si es un usuario jurídico*/
                                    
                                    if($tipo_servicio == 'rtm' || $tipo_servicio == 'rim'){
                                            $modalidad = 70;                                                                                
                                    }elseif($tipo_servicio == 'rtf'){
                                            $modalidad = 71;                                                                               
                                    }elseif($tipo_servicio == 'rtv'){
                                            $modalidad = 72;                                                                                
                                    }             

                                    $resPago = '';
                                    $resPago = $this->Activo->invoca(
                                            'IN098801',
                                            array(
                                                'req'=>array(
                                                    'IN988IDUS'=>$documento_identificacion,
                                                    'IN988NCLT'=>$identificacion,
                                                    'IN988DS2'=>str_pad(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),40,'0',STR_PAD_LEFT),
                                                    'IN988TYPE'=>11,
                                                    'IN988CHAN'=>4,                                                                                                  
                                                    'IN988CSER'=>$modalidad,
                                                    'IN988TSER'=>2, 
                                                    'IN988FACC'=>str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10,20),12,'0',STR_PAD_LEFT),
                                                    'IN988AMNT'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                    'IN988IPCLT'=>$ip

                                                )
                                            )
                                    );                                                                                                          

                                    $error = -1;
                                    $error_afiliacion = false;                                  

                                    if(isset($resPago['IN098801Return'])){
                                        $error = 0;
                                        if(isset($resPago['IN098801Return']['error'])){
                                            if(!empty($resPago['IN098801Return']['error']['ERDR01'])){
                                                $error = intval($resPago['IN098801Return']['error']['ERDR01']);
                                            }else{
                                                $error = intval($resPago['IN098801Return']['error']['ERNU01']);
                                            }
                                            $descripcion = 'Movistar ' .$resPago['IN098801Return']['error']['ERDR01'] . '-' . 'MYPS ' . $resPago['IN098801Return']['error']['ERNU01'] . ' - ' . $resPago['IN098801Return']['error']['ERDS01'];                                                                            
                                        }                                                                                                                                               
                                    }
                                                                        
                                    if($error == 0 ){
                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));
                                        
                                        $referencia = str_pad($resPago['IN098801Return']['IN098801']['IN988CTRA'],9,'0',STR_PAD_LEFT);
                                        $confirmacion = str_pad($resPago['IN098801Return']['IN098801']['IN988CTRA'],9,'0',STR_PAD_LEFT);
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($descripcion),
                                            0
                                        );
                                        
                                        $this->data['RecargaServicios']=array(
                                            'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            'numeroContrato'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                            'banco'=>$bancos['0171'],
                                            'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA'=>$referencia,
                                            'REFERENCIA_OPERADOR'=>$confirmacion,    
                                            'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                        );                                                                               

                                        if(_p('notificacion.movistar')=='1' || _p('notificacion.movistar')=='3'){                                
                                            // Email Orden
                                            // Email Opciones
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3'];
                                            $this->Email->subject = 'Notificacion Recarga de Servicios';
                                            $this->Email->sendAs = 'html';

                                            // Informacion del correo
                                            $this->Email->template = 'recargaservicio_ordenante';
                                            $tipo_accion = 'Recarga de Servicio';
                                            $tipo_afiliacion = 'Movistar';
                                            $nombreBeneficiario = desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']);                                                                                   

                                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion','nombreBeneficiario'));
                                            // Envio del correo
                                            $this->Email->send();
                                        }                            

                                        if(_p('notificacion.movistar')=='2' || _p('notificacion.movistar')=='3'){
                                            // Enviar por sms la notificación de que realizó la recarga al ordenante        
                                            $this->Sms->enviar(array(
                                                'numcel'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR'],
                                                'canal1'=>'59',
                                                'fectim'=>'Movistar',
                                                'cheque'=>substr(desencriptar($this->data['TransaccionPendiente']['cuenta_destino']),2),
                                                'cuenta'=>substr(desencriptar($this->data['TransaccionPendiente']['cuenta_origen']),16,4),
                                                'monto'=>$this->data['TransaccionPendiente']['monto'],
                                                'fecha'=>date('Ymd'),
                                                'hora'=>date('His'),
                                                'refnum'=>substr($referencia,5,4),
                                                'xrnde38'=>'IB'
                                            ));
                                        }                                                                                                                                                                                                                                              
                                    
                                    }else{
                                        $this->log($transaccion['TransaccionPendiente']['descripcion'] . ' - ' . $res, LOG_ERR);
                                        $descripcion = $transaccion['TransaccionPendiente']['descripcion'];
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            100015,
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            $transaccion['TransaccionPendiente']['monto'],
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }                               
                                    break;        
                                case 'tpm': //Transferencia propia mismo banco
                                case 'ttm': //Transferencia tercero mismo banco
                                
                                    // Para transacciones propias mismo banco no se valida límite acumulado diario
                                    
                                    if($transaccion['TransaccionPendiente']['tipo'][1]=='t'){
                                        // Verificar el límite acumulado de la transacción

                                        // Validar el límite sólo si el mismo es diferente de null
                                        // ya que en ese caso se considera sin límites
                                        if($limite_internas_terceros){
                                            $monto_limite = $limite_internas_terceros - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros'];
                                            
                                            if($transaccion['TransaccionPendiente']['monto'] > $monto_limite){
                                                $error = -4;
                                                $descripcion = _m('monto.excedido','de la transacción');
                                                break;
                                            }
                                        }
                                    }
                                                                        
                                    if ($transaccion['TransaccionPendiente']['tipo']=='tpm'){
                                        $tipoOperacion='p';
                                    }else{
                                        $tipoOperacion='t';
                                    }
                                                                        
                                    $res = $this->Activo->invoca(
                                        'INTRFDSJV',
                                        array(
                                            'req'=>array(
                                                'canal'=>Configure::read('canal.ib'),
                                                'INTUSR'=>$identificacion,
                                                'INTFRMACC'=>$cuentaDebitar,
                                                'INTTOACC'=>trim(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']) ,10)),//para
                                                'INTTRFAMT'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                'INTTYPE'=>'0',
                                                'INTDESC'=>trim(desencriptar($transaccion['TransaccionPendiente']['descripcion'])),
                                                'operacion'=>$tipoOperacion,
                                                'INTUSER' => Configure::read('as400.user'),
                                                'INTPASWD' => Configure::read('as400.pwd')
                                            )
                                        )
                                    );

                                    $referencia = $res['INTRFDSJVReturn']['error']['ERDF01'];
                                    
                                    $error = -1;
                                    $descripcion = _m('error.fatal');
                                    $error_afiliacion = false;

                                    if(isset($res['INTRFDSJVReturn'])){
                                        $error = intval($res['INTRFDSJVReturn']['error']['ERRNUM']);
                                        $error = ($error == 0) ? $error : intval($res['INTRFDSJVReturn']['error']['ERNU01']);
                                        $descripcion = $res['INTRFDSJVReturn']['error']['ERNU01'] . ' - ' . $res['INTRFDSJVReturn']['error']['ERDS01'];
                                    }
                                    
                                    if($error == 0 ){
                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));
                                        
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            ($transaccion['TransaccionPendiente']['tipo']=='tpm') ? 100021 : 100022, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            0
                                        );
                                        
                                        if($transaccion['TransaccionPendiente']['tipo']=='ttm'){
                                            // Enviar notificaciones por correo                                         
                                            
                                            $this->data['Transferencia']=array(
                                                'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                                'cuentaCreditar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                                'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                                'banco'=>$bancos['0171'],
                                                'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                                'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                                'REFERENCIA'=>$referencia,
                                                'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                            );
                                            
                                            // Email Ordenante
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = desencriptar($usuario['email_principal']);
                                            $this->Email->subject = 'Notificacion Transferencia';
                                            $this->Email->sendAs = 'html';
                                            $this->Email->template = 'transferencia_ordenante';
                                            $tipo_accion= 'Transferencia';
                                            
                                            switch($transaccion['TransaccionPendiente']['tipo']){
                                                case 'tpm':
                                                    $tipo_afiliacion = 'Propias Mismo Bancos';
                                                    break;
                                                case 'ttm':
                                                    $tipo_afiliacion = 'Terceros Mismo Bancos';
                                                    break;                                              
                                            }
                                            
                                            $this->set(compact('usuario','tipo_accion','tipo_afiliacion','error'));
                                            $this->Email->send();
                                            
                                            
                                            // Email Beneficiario
                                            $this->Email->reset();
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = $transaccion['TransaccionPendiente']['email_beneficiario'];
                                            $this->Email->subject = 'Notificacion Transferencia';
                                            $this->Email->sendAs = 'html';
                                            $this->Email->template = 'transferencia_beneficiario';
                                            $this->set(compact('usuario'));
                                            $this->Email->send();
                                        }
                                    } else {
                                        $this->log('Transferencias Terceros - '. $descripcion, LOG_ERR);
                                        
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            ($transaccion['TransaccionPendiente']['tipo']=='tpm') ? 100021 : 100022, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }
                                
                                    break;
                                case 'tpo': //Transferencia propia otro banco
                                case 'tto': //Transferencia tercero otro banco
                                    // Verificar el límite acumulado de la transacción
                                    
                                    // Validar el límite sólo si el mismo es diferente de null
                                    // ya que en ese caso se considera sin límites


                                    if($limite_externas){                                   
                                        $monto_limite = $limite_externas - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas'];
                                        
                                        if($transaccion['TransaccionPendiente']['monto'] > $monto_limite){
                                            $error = -4;
                                            $descripcion = _m('monto.excedido','de la transacción');
                                            break;
                                        }
                                    }
                                
                                    if($transaccion['TransaccionPendiente']['tipo'][1]=='t'){
                                        $INEDTP1 = '0227-Terceros Otros Bancos';
                                                                                $tipoOperacion='t';
                                    } else {
                                        $INEDTP1 = '0220-MISMO TITULAR OTROS BANCOS';
                                                                                $tipoOperacion='p';
                                    }

                                    

                                    if(strlen(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])) < 19){
                                        $id_banco_destino = $transaccion['TransaccionPendiente']['banco'];
                                    }else{
                                        $id_banco_destino = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 0, 4);
                                    }            

                                  
                                                               /** Para identificar si es un usuario juridico * */
                                    if ($usuario['tipo_rif']) {
                                        $identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
                                        $identificacionSIMF = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
                                        $tipoSchmeNm = 'SRIF';
                                    } else {
                                        $identificacion = desencriptar($usuario['cedula']);
                                        $identificacionSIMF = $usuario['tipo_cedula'] . desencriptar($usuario['cedula']);
                                        if($usuario['tipo_cedula']=='P'){
                                            $tipoSchmeNm = 'SPAS';
                                        }else{
                                            $tipoSchmeNm = 'SCID';
                                        }
                                    }


                            
                                    if($transaccion['TransaccionPendiente']['tipo_id_beneficiario']=='P'){
                                        $tipoSchmeNmBenef = 'SPAS';
                                    }elseif($transaccion['TransaccionPendiente']['tipo_id_beneficiario']=='V' || $transaccion['TransaccionPendiente']['tipo_id_beneficiario']=='E'){
                                        $tipoSchmeNmBenef = 'SCID';
                                    }else{
                                        $tipoSchmeNmBenef = 'SRIF';
                                    }
                            
                                    if(strlen(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])) < 19 ){
                                        $credito = array(
                                            'id'=>trim(ltrim(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),'0')), 
                                            'tp'=>'CELE'
                                        );
                                    }else{
                                        $credito = array(
                                            'id'=>trim(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])), 
                                            'tp'=>'CNTA'
                                        );
                                    }
                            
                                    $res =$this->Activo->invocaSIMF(
                                        'CstmrCdtTrfInitn',
                                        array(
                                            'CstmrCdtTrfInitn'=>array(
                                                'user'=>Configure::read('as400.user'),
                                                'password'=>Configure::read('as400.pwd'),
                                                'canal'=>'4',
                                                'id'=>$transaccion['TransaccionPendiente']['usuario_id'],                               
                                                'tipo'=>'1',
                                                'ip'=>Configure::read('ip.client'),
                                                'grpHdr'=>array(
                                                    'ctrlSum'=>array(
                                                        'amt'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']), 
                                                        'ccy'=>'VES'
                                                    ),
                                                    'lclInstrm'=>'040',
                                                    'nbOfTxs'=>1,
                                                ),
                                                'pmtInf'=>array(
                                                    'amount'=>array(
                                                      'amt'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']), 
                                                        'ccy'=>'VES'
                                                    ),
                                                    'cdtr'=>array(
                                                        'id'=>$transaccion['TransaccionPendiente']['tipo_id_beneficiario'].desencriptar($transaccion['TransaccionPendiente']['id_beneficiario']), 
                                                        'nm'=>trim(desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario'])),
                                                        'schmeNm'=>$tipoSchmeNmBenef
                                                    ),
                                                    'cdtrAcct'=>$credito,
                                                    'cdtrAgt'=>$transaccion['TransaccionPendiente']['banco_destino'],
                                                    'dbtr'=>array(
                                                        'id'=>$identificacionSIMF,
                                                        'nm'=>desencriptar($usuario['nombre']),
                                                        'schmeNm'=>$tipoSchmeNm
                                                    ),
                                                    'dbtrAcct'=>array(
                                                        'id'=>trim(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen'])), 
                                                        'tp'=>'CNTA'
                                                    ),
                                                    'dbtrAgt'=>'0171',
                                                    'regId'=>1,
                                                    'rmtInf'=>trim(desencriptar($transaccion['TransaccionPendiente']['descripcion'])),
                                                    'purp'=>220
                                                )

                                            )
                                        )
                                    );    

                                    $error = -1;
                                    $descripcion = "Error inesperado, transacción no disponible.";                                        

                                    if (isset($res['return']['codRespuesta']) && $res['return']['codRespuesta']!='00') {
                                        $error = -1;                        
                                        $descripcion = $res['return']['codRespuesta'] . ' - ' . $res['return']['mensaje'];
                                    }elseif(!$res){
                                         $error = -1;
                                    $descripcion = "Error inesperado, transacción no disponible.";
                                       
                                    }else{
                                         $error = 0;
                                        $referencia = $res['return']['referencia'];

                                    }
                            
                                    $estatus = 'EN PROCESO';
                                    $this->set('pendienteSimf', true);
                                                                                                              
                                    if($error == 0){
                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'e', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),$fecha_acumulado);
                                        
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            ($transaccion['TransaccionPendiente']['tipo']=='tpo') ? 100023 : 100024, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            0
                                        );
                                        
                                        // Enviar notificaciones por correo                                         
                                        
                                        $this->data['Transferencia']=array(
                                            'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            'cuentaCreditar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                            'banco'=>$banco_destino,
                                            'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA'=>$referencia,
                                            'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                        );
                                        
                                        // Email Ordenante
                                        $this->Email->smtpOptions = Configure::read('email.smtp');
                                        $this->Email->delivery = 'smtp';
                                        $this->Email->from = Configure::read('email.from');
                                        $this->Email->to = desencriptar($usuario['email_principal']);
                                        $this->Email->subject = 'Notificacion Transferencia';
                                        $this->Email->sendAs = 'html';
                                        $this->Email->template = 'transferencia_ordenante';
                                        $tipo_accion= 'Transferencia';
                                        
                                        switch($transaccion['TransaccionPendiente']['tipo']){
                                            case 'tpo':
                                                $tipo_afiliacion = 'Propias Otros Bancos';
                                                break;
                                            case 'tto':
                                                $tipo_afiliacion = 'Terceros Otros Bancos';
                                                break;
                                        }
                                        
                                        $this->set(compact('usuario','tipo_accion','tipo_afiliacion','error'));
                                        $this->Email->send();
                                        
                                        
                                        // Email Beneficiario
                                        $this->Email->reset();
                                        $this->Email->smtpOptions = Configure::read('email.smtp');
                                        $this->Email->delivery  = 'smtp';
                                        $this->Email->from      = Configure::read('email.from');
                                        $this->Email->to        = $transaccion['TransaccionPendiente']['email_beneficiario'];
                                        $this->Email->subject   = 'Notificacion Transferencia';
                                        $this->Email->sendAs    = 'html';
                                        $this->Email->template  = 'transferencia_beneficiario';
                                        $this->set(compact('usuario'));
                                        $this->Email->send();
                                    } else {
                                        
                                        $this->log('Transferencias Mismo Titulas Otros Bancos - '. $descripcion, LOG_ERR);
                                        
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            ($transaccion['TransaccionPendiente']['tipo']=='tpo') ? 100023 : 100024, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }
                                
                                    break;                                                                                                                                                                                                                
                                case 'dpm': //Transferencia propia mismo banco Moneda Extranjera
                                case 'dtm': //Transferencia tercero mismo banco Moneda Extranjera
                                
                                    // Para transacciones propias mismo banco no se valida límite acumulado diario
                                    
                                    $datosDproducto = array('NROCUENTA' => str_pad($cuentaDebitar,12,'0',STR_PAD_LEFT), 'NROPARAM' => 2);   
                                    $datosD = $this->Activo->validarProducto($datosDproducto);                                                                                
                                    // Capturar la moneda 
                                    $moneda = $datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'];
                                    $estado['moneda'] = $moneda;
                                                                        
                                    if($transaccion['TransaccionPendiente']['tipo'][1]=='t'){
                                        // Verificar el límite acumulado de la transacción
                                        // Validar el límite sólo si el mismo es diferente de null
                                        // ya que en ese caso se considera sin límites                                                                                
                                                                                
                                        if($moneda=='USD'){
                                            if($limite_internas_terceros_usd){
                                                $monto_limite = $limite_internas_terceros_usd - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_usd'];

                                                if($transaccion['TransaccionPendiente']['monto'] > $monto_limite){
                                                    $error = -4;
                                                    $descripcion = _m('monto.excedido','de la transacción');
                                                    break;
                                                }
                                            }
                                        }else{
                                            if($limite_internas_terceros_eur){
                                                $monto_limite = $limite_internas_terceros_eur - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_eur'];

                                                if($transaccion['TransaccionPendiente']['monto'] > $monto_limite){
                                                    $error = -4;
                                                    $descripcion = _m('monto.excedido','de la transacción');
                                                    break;
                                                }
                                            }    
                                        }                                       
                                    }
                                                                        
                                    if ($transaccion['TransaccionPendiente']['tipo']=='dpm'){
                                        $tipoOperacion='p';
                                    }else{
                                        $tipoOperacion='t';
                                    }                                                                                                           

                                    $res = $this->Activo->invoca(
                                        'INTRFDSJV6',
                                        array(
                                            'req'=>array(
                                                'INTUSR6'=>$identificacion,
                                                'INTFRMACC6'=>$cuentaDebitar,//de
                                                'INTTOACC6'=>trim(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']) ,10)),//para
                                                'INTTRFAMT6'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                'INTTYPE6'=>'0',
                                                'INTCANAL6'=>'06',
                                                'INTCCY6'=>$moneda, 
                                                'INTDESC6'=>trim(desencriptar($transaccion['TransaccionPendiente']['descripcion'])),
                                                'canal'=>Configure::read('canal.ib'),
                                                'operacion'=>'t',
                                                'INTDIP6' => Configure::read('ip.client')
                                            )
                                        )
                                    );
                                    $referencia = $res['INTRFDSJV6Return']['error']['ERDF01'];
                                    
                                    $error = -1;
                                    $descripcion = "Error inesperado, transaccion no disponible.";

                                    $error_afiliacion = false;

                                      if(isset($res['INTRFDSJV6Return'])){
                                        $error = intval($res['INTRFDSJV6Return']['error']['ERRNUM']);
                                        $error = ($error == 0) ? $error : intval($res['INTRFDSJV6Return']['error']['ERNU01']);
                                        $descripcion = $res['INTRFDSJV6Return']['error']['ERNU01'] . ' - ' . $res['INTRFDSJV6Return']['error']['ERDS01'];
                                      }

                                    
                                    if($error == 0 ){
                                                                            
                                        if($moneda=='USD'){
                                            $acumME = 'itmeusd';
                                        }else{
                                            $acumME = 'itmeeur';
                                        }
                                        $transaccion['TransaccionPendiente']['moneda'] = $moneda;
                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], $acumME, formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));
                                        
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            ($transaccion['TransaccionPendiente']['tipo']=='dpm') ? 101070 : 101071, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            0
                                        );
                                        
                                        if($transaccion['TransaccionPendiente']['tipo']=='dtm'){
                                            // Enviar notificaciones por correo                                         
                                            
                                            $this->data['Transferencia']=array(
                                                'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                                'cuentaCreditar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                                'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                                'banco'=>$bancos['0171'],
                                                'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                                'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                                'REFERENCIA'=>$referencia,
                                                'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                            );
                                            
                                            // Email Ordenante
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = desencriptar($usuario['email_principal']);
                                            $this->Email->subject = 'Notificacion Transferencia';
                                            $this->Email->sendAs = 'html';
                                            $this->Email->template = 'transferencia_ordenante';
                                            $tipo_accion= 'Transferencia';
                                            
                                            switch($transaccion['TransaccionPendiente']['tipo']){
                                                case 'dpm':
                                                        $tipo_afiliacion = 'Propias Mismo Bancos';
                                                        break;
                                                case 'dtm':
                                                        $tipo_afiliacion = 'Terceros Mismo Bancos';
                                                        break;
                                                    
                                            }
                                            
                                            $this->set(compact('usuario','tipo_accion','tipo_afiliacion','error'));
                                            $this->Email->send();
                                                                                        
                                            // Email Beneficiario
                                            $this->Email->reset();
                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery = 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = $transaccion['TransaccionPendiente']['email_beneficiario'];
                                            $this->Email->subject = 'Notificacion Transferencia';
                                            $this->Email->sendAs = 'html';
                                            $this->Email->template = 'transferencia_beneficiario';
                                            $this->set(compact('usuario'));
                                            $this->Email->send();
                                        }
                                    } else {
                                        $this->log('Transferencias Terceros - '. $descripcion, LOG_ERR);
                                        
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            ($transaccion['TransaccionPendiente']['tipo']=='dpm') ? 101070 : 101071, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            NULL,
                                            $descripcion,
                                            1
                                        );
                                    }
                                
                                    break;                                                                        
                                case 'ppm': //Pago tarjeta propia mismo banco
                                case 'ptm': //Pago tarjeta tercero mismo banco
                                
                                    // Para transacciones propias mismo banco no se valida límite acumulado diario
                                    
                                    if($transaccion['TransaccionPendiente']['tipo'][1]=='t'){
                                        // Verificar el límite acumulado de la transacción
                                        // Validar el límite sólo si el mismo es diferente de null
                                        // ya que en ese caso se considera sin límites
                                        if($limite_internas_terceros){                                      
                                            $monto_limite = $limite_internas_terceros - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros'];
                                            
                                            if($transaccion['TransaccionPendiente']['monto'] > $monto_limite){
                                                $error = -4;
                                                $descripcion = _m('monto.excedido','de la transacción');
                                                break;
                                            }
                                        }
                                    }    

                                                                    
                                    if ($transaccion['TransaccionPendiente']['tipo']=='ppm'){
                                        $tipoOperacion='p';
                                    }else{
                                        $tipoOperacion='t';
                                    }

                                    $cuentaDebitar = trim(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']) ,10));


                                    $montoOperacion = $transaccion['TransaccionPendiente']['monto'];  

                                    $res = $this->Activo->invoca(
                                        'IN098102',
                                        array(
                                            'req'=>array(
                                                'IN982USR' => $identificacion,
                                                'IN982CRNUM'=> desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                                'IN982DEBAC'=> $cuentaDebitar,
                                                'IN982PAYAM'=>$montoOperacion,
                                                'IN982CCY' => _p('moneda'),
                                                'IN982DTE1'=> date('d'),
                                                'IN982DTE2'=> date('m'),
                                                'IN982DTE3'=> date('y'),
                                                'operacion'=> $tipoOperacion,
                                                'IN982IPACT' => Configure::read('ip.client'),
                                                'IN982ENDFL' => 'N',
                                                'canal' => Configure::read('canal.ib')
                                            )
                                        )
                                    );




                                    $error = -1;
                                    $descripcion = _m('error.fatal');
                                    $referencia = $res['IN098102Return']['error']['ERDF01'];
                                    
                                    if(isset($res['IN098102Return']['error'])){
                                        $error = intval($res['IN098102Return']['error']['ERNU01']);
                                        $descripcion = $res['IN098102Return']['error']['ERNU01'] . ' - ' . $res['IN098102Return']['error']['ERDS01'];
                                    }


                                    
                                    if($error==0){
                                        $anioTransaccion = date("Y");
                                        $codBCV = '860';
                                        $diaTransaccion = date("d");
                                        $mesTransaccion = date("m"); 
                                        $numTransaccion = rand(1, 999999999); 
                                        $numeroTDC = desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'] );


                                        //Se formateo con la coma(,) como separador de decimales, ya que en pago tdc envian los montos con coma y la transaforman en el xml del archivo credicard  

                                        
                                        if(strpos($montoOperacion, '.') === false){
                                            $montoTCD = trim($montoOperacion).",00";
                                        }else{
                                            $montoTCD = str_replace(".",",",$montoOperacion);
                                        }    

                                        $soapRes = $this->Credicard->PagoEnLineaTdcSoap($anioTransaccion, $codBCV, $diaTransaccion, $mesTransaccion, $montoTCD, $numTransaccion, $numeroTDC);

                                         if (trim($soapRes['IN982ENDFL']) == 'Y') {

                                            $res = $this->Activo->invoca(
                                                'IN098102',
                                                array(
                                                    'req' => array(
                                                        'IN982USR' => $identificacion,
                                                        'IN982CRNUM'=> desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                                        'IN982DEBAC' => $cuentaDebitar,
                                                        'IN982PAYAM' => $montoOperacion,
                                                        'IN982CCY' => _p('moneda'),
                                                        'IN982DTE1' => date('d'),
                                                        'IN982DTE2' => date('m'),
                                                        'IN982DTE3' => date('y'),
                                                        'IN982IPACT' => Configure::read('ip.client'),
                                                        'IN982ENDFL' => 'Y',
                                                        'operacion' => $tipoOperacion,
                                                        'canal' => Configure::read('canal.ib')
                                                    )
                                                )
                                            );
                                                
                                            //construir el mansaje de error devuelto por el WS credicard
                                            $lugar = stripos($soapRes['mensaje'], "TRANSACCION");
                                            if($lugar > 0){
                                                $error = "9998";
                                                $mensaje = $soapRes['mensaje'];
                                                $descripcion = substr($mensaje,0,$lugar-1);
                                            }else{    
                                                $error = "9999";
                                                $descripcion = $soapRes['mensaje'];
                                            }    

                                        }else{  

                                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']));                                            
                                            
                                            $this->Bitacora->registraBitacora(
                                                $usuario['id'],
                                                ($transaccion['TransaccionPendiente']['tipo']=='ppm') ? 100025 : 100026, 
                                                desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                                desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                                formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                                $referencia,
                                                desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                                0
                                            );
                                        
                                            if($transaccion['TransaccionPendiente']['tipo']=='ptm'){
                                                $this->data['Pagostdc']=array(
                                                  'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                                  'numeroTDC'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                                  'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                                  'banco'=>$bancos['0171'],
                                                  'emailBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['email_beneficiario']),
                                                  'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                                  'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                                  'REFERENCIA'=>$referencia,
                                                  'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                                );
                                        
                                                // Email ordenante
                                                $this->Email->smtpOptions = Configure::read('email.smtp');
                                                $this->Email->delivery  = 'smtp';
                                                $this->Email->from = Configure::read('email.from');
                                                $this->Email->to = desencriptar($usuario['email_principal']);
                                                $this->Email->subject = 'Notificacion Pago Tarjeta de Credito';
                                                $this->Email->sendAs = 'html';
                                                $this->Email->template = 'pagotdc_ordenante';
                                            
                                                $tipo_accion= 'Pago TDC';
                                            
                                                switch($transaccion['TransaccionPendiente']['tipo']){
                                                    case 'ppm':
                                                        $tipo_afiliacion = 'Propias Mismo Bancos';
                                                        break;
                                                    case 'ptm':
                                                        $tipo_afiliacion = 'Terceros Mismo Bancos';
                                                        break;
                                                }
                                            
                                                $this->set(compact('usuario','tipo_accion','tipo_afiliacion','error'));
                                                $this->set(compact('usuario'));
                                                $this->Email->send();
                                            
                                            
                                                // Email Beneficiario
                                                $this->Email->reset();
                                                $this->Email->smtpOptions = Configure::read('email.smtp');
                                                $this->Email->delivery = 'smtp';
                                                $this->Email->from = Configure::read('email.from');
                                                $this->Email->to = $transaccion['TransaccionPendiente']['email_beneficiario'];
                                                $this->Email->subject = 'Notificacion Pago';
                                                $this->Email->sendAs = 'html';
                                                $this->Email->template = 'pagotdc_beneficiario';
                                                $this->set(compact('usuario'));
                                                $this->Email->send();
                                            } 

                                        } //fin de soap  
                                
                                    } else {

                                            
                                        $this->log('Pago TDC Mismo Banco - ' . $descripcion, LOG_ERR);
                                            
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            ($transaccion['TransaccionPendiente']['tipo']=='ppm') ? 100025 : 100026, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            $descripcion,
                                            1
                                        );
                                    }
                                    break;
                                case 'ppo': //Pago tarjeta propia otro banco
                                case 'pto': //Pago tarjeta tercero otro banco
                                    // Verificar el límite acumulado de la transacción
                                    // Validar el límite sólo si el mismo es diferente de null
                                    // ya que en ese caso se considera sin límites
                                    if($limite_externas){                                       
                                        $monto_limite = $limite_externas - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas'];
                                        
                                        if($transaccion['TransaccionPendiente']['monto'] > $monto_limite){
                                            $error = -4;
                                            $descripcion = _m('monto.excedido','de la transacción');
                                            break;
                                        }
                                    }
                                    
                                    // Para buscar el banco
                                    $tdc = str_pad(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),20,'0',STR_PAD_RIGHT);
                                    $valorTarjeta = array('TNROTARJ' => $tdc);
                                    $datos_tdc = $this->Activo->validarBIN($valorTarjeta);

                                    
                                    if(isset($res['NIB090501Return']['error'])){
                                        $error = intval($res['NIB090501Return']['error']['ERNU01']);
                                        $descripcion = $res['NIB090501Return']['error']['ERNU01'] . ' - ' . $res['IN098102Return']['error']['ERDS01'];
                                        $this->log('Pago TDC Otros Bancos - ' . $descripcion, LOG_ERR);
                                        break;
                                    } else {
                                        $id_banco_destino = trim(substr($datos_tdc['NIB090501Return']['NIB090501']['TBANCO'],6));
                                        $banco_destino = $datos_tdc['NIB090501Return']['NIB090501']['TDESBANCO'];
                                        $id_banco_destino = str_pad($id_banco_destino,9,'0',STR_PAD_LEFT);
                                    }
                                    
                                    if ($transaccion['TransaccionPendiente']['tipo']=='ppo'){
                                        $tipoOperacion='p';
                                    }else{
                                        $tipoOperacion='t';
                                    }
                                                                        
                                    $res = $this->Activo->invoca(
                                        'INEXTDSJV',
                                        array(
                                            'req'=>array(
                                                'canal'=>Configure::read('canal.ib'),
                                                'INEUSR'=> $identificacion,
                                                'INEFRMACC'=>$cuentaDebitar,
                                                'INEBNFACC'=>trim(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])),
                                                'INEAMT'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                'INEBNFAD1'=>substr(trim(desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario'])),0,30),
                                                'INEBNFAD2'=>$transaccion['TransaccionPendiente']['tipo_id_beneficiario'].str_pad(desencriptar($transaccion['TransaccionPendiente']['id_beneficiario']),9,'0',STR_PAD_LEFT),
                                                'INEBNKAD3'=>_p('moneda'),
                                                'INECCY'=>_p('moneda'),
                                                'INEDTP1'=>'0225-TARJETA DE CREDITO OTRO BANCO',
                                                'INEDTP2'=>trim(desencriptar($transaccion['TransaccionPendiente']['descripcion'])),
                                                'INEDTP3'=>trim(desencriptar($transaccion['TransaccionPendiente']['descripcion'])),
                                                'INEBNKSN'=>substr($banco_destino,0,20),
                                                'INEINTAD4'=>_p('moneda'),
                                                'INEVDT1'=>date('d'),
                                                'INEVDT2'=>date('m'),
                                                'INEVDT3'=>date('y'),
                                                'INEXPMTV'=>_p('via_pago_internet'),
                                                'INEBNKID'=>$id_banco_destino,
                                                'INEBNKAD1'=>substr($banco_destino,0,20),
                                                'INEBNKAD4'=>_p('moneda'),
                                                'INECKN'=>'0',
                                                'INESFMT'=>'0',
                                                'operacion'=>$tipoOperacion
                                            )
                                        )
                                    );
                                    
                                    $error = -1;
                                    $descripcion = _m('error.fatal');
                                    $error_afiliacion = false;
                                    $referencia = $res['INEXTDSJVReturn']['error']['ERDF01'];
                                    
                                    if(isset($res['INEXTDSJVReturn'])){
                                        $error = intval($res['INEXTDSJVReturn']['error']['ERRNUM']);
                                        $error = ($error == 0) ? $error : intval($res['INEXTDSJVReturn']['error']['ERNU01']);
                                        $descripcion = $res['INEXTDSJVReturn']['error']['ERNU01'] . ' - ' . $res['INEXTDSJVReturn']['error']['ERDS01'];
                                    }

                                    if($error == 0){
                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'e', formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),$fecha_acumulado);
                                        
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            ($transaccion['TransaccionPendiente']['tipo']=='ppo') ? 100027 : 100028, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            0
                                        );
                                        
                                        $this->data['Pagostdc']=array(
                                            'cuentaDebitar'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            'numeroTDC'=>desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
                                            'nombreBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),
                                            'banco'=>$banco_destino,
                                            'emailBeneficiario'=>desencriptar($transaccion['TransaccionPendiente']['email_beneficiario']),
                                            'monto'=>formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            'CONCEPTO'=>desencriptar($transaccion['TransaccionPendiente']['descripcion']),
                                            'REFERENCIA'=>$referencia,
                                            'descripcion'=>desencriptar($transaccion['TransaccionPendiente']['descripcion'])
                                        );
                        
                                        // Email ordenante
                                        $this->Email->smtpOptions = Configure::read('email.smtp');
                                        $this->Email->delivery = 'smtp';
                                        $this->Email->from = Configure::read('email.from');
                                        $this->Email->to = desencriptar($usuario['email_principal']);
                                        $this->Email->subject = 'Notificacion Pago Tarjeta de Credito';
                                        $this->Email->sendAs = 'html';
                                        $this->Email->template = 'pagotdc_ordenante';
                                        
                                        $tipo_accion= 'Pago TDC';
                                        
                                        switch($transaccion['TransaccionPendiente']['tipo']){
                                            case 'ppo':
                                                $tipo_afiliacion = 'Propias Otros Bancos';
                                                break;
                                            case 'pto':
                                                $tipo_afiliacion = 'Terceros Otros Bancos';
                                                break;
                                        }
                                        
                                        $this->set(compact('usuario','tipo_accion','tipo_afiliacion','error'));
                                        $this->Email->send();
        
                                        if($transaccion['TransaccionPendiente']['tipo'] == 'ppo'){
                                            $tipoEmail = desencriptar($usuario['email_principal']);
                                        }elseif($transaccion['TransaccionPendiente']['tipo'] == 'pto'){
                                            $tipoEmail = $this->data['Pagostdc']['emailBeneficiario'];
                                        }
                                        
                                        // Email Beneficiario
                                        $this->Email->reset();
                                        $this->Email->smtpOptions = Configure::read('email.smtp');
                                        $this->Email->delivery  = 'smtp';
                                        $this->Email->from = Configure::read('email.from');
                                        $this->Email->to = $tipoEmail;
                                        $this->Email->subject = 'Notificacion Pago';
                                        $this->Email->sendAs = 'html';
                                        $this->Email->template = 'pagotdc_beneficiario';
                                        $this->set(compact('usuario'));
                                        $this->Email->send();

                                    }else{
                                        $this->log('Pago TDC Terceros Otros Bancos- '. $descripcion, LOG_ERR);
                                        
                                        $this->Bitacora->registraBitacora(
                                            $usuario['id'],
                                            ($transaccion['TransaccionPendiente']['tipo']=='ppo') ? 100027 : 100028, 
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
                                            $referencia,
                                            $descripcion,
                                            1
                                        );
                                    }
            
                                    break;
                                    
                                case 'nxm':
                                case 'vxm':
                                case 'nxo':
                                case 'vxo':
                                case 'nxa':
                                case 'vxa':
                                case 'mxm':
                                case 'xxm':
                                    
                                    if($transaccion['TransaccionPendiente']['tipo'][0]=='n'){
                                        $tipo_pago_cash = 'n';
                                    }elseif($transaccion['TransaccionPendiente']['tipo'][0]=='v'){
                                        $tipo_pago_cash = 'p';
                                    }
                                    if($transaccion['TransaccionPendiente']['tipo'][0]=='m'){
                                        $tipo_pago_cash = 'n';
                                    }elseif($transaccion['TransaccionPendiente']['tipo'][0]=='x'){
                                        $tipo_pago_cash = 'p';
                                    }
                                    
                                    $bdata = $this->TransaccionBeneficiario->obtenerListaDetalle($transaccion['TransaccionPendiente']['id']);

                                    $monto_interna = 0;
                                    $monto_externa = 0;

                                    foreach($bdata as &$b){
                                        // Verifica si es una cuenta interna o externa
                                        if(substr(desencriptar($b['AfiliacionCash']['numero_cuenta']),0,4)=='0171'){
                                            $monto_interna += formatea_monto_toWs($b['AfiliacionCash']['monto']);
                                        }
                                        else{
                                            $monto_externa += formatea_monto_toWs($b['AfiliacionCash']['monto']);
                                        }
                                    }

                                    $productoDvalidar = substr(desencriptar($b['AfiliacionCash']['numero_cuenta']), -10);
                                    $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar, 12, '0', STR_PAD_LEFT), 'NROPARAM' => 2);
                                    $datosD = $this->Activo->validarProducto($datosDproducto);
                                    $errorD = intval($datosD['NIBPF3010Return']['error']['ERNU01']);                                    
                                    
                                    // Verificar los límites acumulados de la transacción
                                    // validar los límites sólo si son diferentes de null
                                    // ya que en ese caso se considera ilimitado
                                    $tiene_limite_interno = false;
                                    $tiene_limite_externo = false;
                                                                                                          
                                    if($transaccion['TransaccionPendiente']['tipo'][0]=='n'){
                                        if($limite_internas_terceros_nomina && $monto_interna>0){
                                            $monto_limite_interno = $limite_internas_terceros_nomina - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'];
                                            $tiene_limite_interno = true;
                                        }

                                        if($limite_externas_nomina && $monto_externa>0){
                                            $monto_limite_externo = $limite_externas_nomina - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_nomina'];
                                            $tiene_limite_externo = true;
                                        } 
                                    }elseif($transaccion['TransaccionPendiente']['tipo'][0]=='v'){

                                        if($limite_internas_terceros_proveedores && $monto_interna>0){
                                            $monto_limite_interno = $limite_internas_terceros_proveedores - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'];
                                            $tiene_limite_interno = true;
                                        }

                                        if($limite_externas_proveedores && $monto_externa>0){
                                            $monto_limite_externo = $limite_externas_proveedores - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_proveedores'];
                                            $tiene_limite_externo = true;
                                        } 
                                    }elseif($transaccion['TransaccionPendiente']['tipo'][0]=='m'){

                                        $limite_total_interno_usd = $usuario['limite_internas_terceros_nomina_usd'] ? $usuario['limite_internas_terceros_nomina_usd'] : _p('limite_interna_terceros_nomina_usd');

                                        $limite_total_interno_eur = $usuario['limite_internas_terceros_nomina_eur'] ? $usuario['limite_internas_terceros_nomina_eur'] : _p('limite_interna_terceros_nomina_eur');   

                                        if ($limite_total_interno_usd) { 

                                            $monto_limite_interno_usd = $limite_total_interno_usd - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_nomina_usd'];
                                        }

                                        if ($limite_total_interno_eur) {
                                            $monto_limite_interno_eur = $limite_total_interno_eur - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_nomina_eur'];
                                        }
                                                                                                                              
                                    }elseif($transaccion['TransaccionPendiente']['tipo'][0]=='x'){
                                        $limite_total_interno_usd = $usuario['limite_internas_terceros_proveedores_usd'] ? $usuario['limite_internas_terceros_proveedores_usd'] : _p('limite_interna_terceros_proveedores_usd');
                                        $limite_total_interno_eur = $usuario['limite_internas_terceros_proveedores_eur'] ? $usuario['limite_internas_terceros_proveedores_eur'] : _p('limite_interna_terceros_proveedores_eur');

                                        if ($limite_total_interno_usd) {
                                            $monto_limite_interno_usd = $limite_total_interno_usd - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_proveedores_usd'];
                                        }

                                        if ($limite_total_interno_eur) {
                                            $monto_limite_interno_eur = $limite_total_interno_eur - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_proveedores_eur'];
                                        }
                                    }

                                    if(
                                        $tiene_limite_interno &&
                                        $tiene_limite_externo &&
                                        $monto_interna > $monto_limite_interno &&
                                        $monto_externa > $monto_limite_externo
                                    ){

                                        $error = -4;
                                        $descripcion = _m('monto.excedido.ambos','de la transaccion');
                                        break;
                                    }elseif(
                                        $tiene_limite_externo &&
                                        $tiene_limite_interno &&
                                        $monto_interna > $monto_limite_interno
                                    ){

                                        $error = -4;
                                        $descripcion = _m('monto.excedido.activo','de la transaccion');
                                        break;
                                    }elseif(
                                        $tiene_limite_externo &&
                                        $monto_externa > $monto_limite_externo
                                    ){
                                        $error = -4;
                                        $descripcion = _m('monto.excedido.otros','de la transaccion');
                                        break;
                                    }elseif($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD'){
                                        
                                        if ($monto_interna > $limite_total_interno_usd) {
                                            $error = -4;
                                            $descripcion = _m('monto.limite.config', 'de la transaccion');
                                            break;

                                        }elseif($monto_interna > $monto_limite_interno_usd){

                                        $error = -4;

                                            $descripcion = _m('monto.excedido.activo', 'de la transaccion');
                                            break;
                                        } 
                       
                                    }elseif ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {
                   
                                        if ($monto_interna > $limite_total_interno_eur) {
                                            $error = -4;
                                            $descripcion = _m('monto.limite.config', 'de la transaccion');
                                            break;

                                       }elseif($monto_interna > $monto_limite_interno_eur){
                                            $error = -4;
                                            $descripcion = _m('monto.excedido.activo', 'de la transaccion');
                                            break;
                                        } 
                                    }
                                                                        
                                    $t = strtotime($transaccion['TransaccionPendiente']['fecha_hora_valor']);
                                    
                                    $fecha_valor['day'] = date('d',$t);
                                    $fecha_valor['month'] = date('m',$t);
                                    $fecha_valor['year'] = date('Y',$t);
                                    
                                    $dataPago = array(
                                        'lote'=> $transaccion['TransaccionPendiente']['lote'], 
                                        'tipo'=> $tipo_pago_cash, 
                                        'cuentaDebitar'=> desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']), 
                                        'monto_total'=> str_replace(",",".",$transaccion['TransaccionPendiente']['monto']),
                                        'fecha_valor'=> $fecha_valor,
                                    );
                                    
                                    if(!$this->Activo->ejecutarPagoCash($dataPago, $bdata)){
                                        $error = $this->Activo->error;
                                        $descripcion = $this->Activo->descripcion_error;
                                    } else {
                                        if(
                                            $dataPago['fecha_valor']['day']==date('d') &&
                                            $dataPago['fecha_valor']['month']==date('m') &&
                                            $dataPago['fecha_valor']['year']==date('Y')
                                        ){
                                            // si el pago es hoy, revisar pago por pago para sumar sólo los montos aplicados en el acumulado
                                            
                                            $this->Activo->seguimientoGestion($transaccion);
                                            
                                            foreach($transaccion['Beneficiarios'] as $i){
                                                if(isset($i['rechazado'])){
                                                    // Restar montos rechazados del acumulado
                                                    
                                                    if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
                                                        $monto_interna -= floatval($i['monto']);
                                                    } else {
                                                        $monto_externa -= floatval($i['monto']);
                                                    }
                                    
                                                    // Marcar en la base de datos el reverso, para no hacerlo dos veces
                                                    
                                                    $this->TransaccionBeneficiario->id = $i['id'];
                                                    
                                                    $this->TransaccionBeneficiario->saveField('reversada',1);
                                                }
                                            }
                                        }
                                        if($transaccion['TransaccionPendiente']['tipo'][0]=='n'){
                                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itn', formatea_monto_fromWs($monto_interna), $temp_fecha);
                                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'en', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                        }elseif($transaccion['TransaccionPendiente']['tipo'][0]=='v'){
                                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itp', formatea_monto_fromWs($monto_interna), $temp_fecha);
                                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'ep', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                        }elseif($transaccion['TransaccionPendiente']['tipo'][0]=='m'){
                                            if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD') {
                                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itnusd', formatea_monto_fromWs($monto_interna), $temp_fecha); 
                                            }elseif ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {
                                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itneur', formatea_monto_fromWs($monto_interna), $temp_fecha);
                                            }
                                        }elseif($transaccion['TransaccionPendiente']['tipo'][0]=='x'){
                                            if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD') {                                                       
                                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itpusd', formatea_monto_fromWs($monto_interna), $temp_fecha);       
                                            }elseif ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {
                                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itpeur', formatea_monto_fromWs($monto_interna), $temp_fecha);
                                            }
                                        }
                                    }
                                    
                                    break; 
                                    
                                case 'csr': //Suspensión de rango de cheques
                                
                                    list($primer_cheque,$segundo_cheque) = @explode('|', desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']));
                                    
                                    $res = $this->Activo->invoca(
                                        'STOPDSJV',
                                        array(
                                            'req'=>array(
                                                'STOSTPACC'=> substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10),
                                                'STOSTPFCK'=> trim($primer_cheque),
                                                'STOSTPRMK'=> trim(desencriptar($transaccion['TransaccionPendiente']['descripcion'])),
                                                'STOSTPTCK'=> trim($segundo_cheque),
                                                'STOSTPFLG'=> 'S'
                                            )
                                        )
                                    );
                                    
                                    $error = 0;
                                    $referencia = null;
                                                                        
                                    if(isset($res['STOPDSJVReturn']['error']['ERRNUM'])){
                                        $error = intval($res['STOPDSJVReturn']['error']['ERRNUM']);
                                        $error = ($error == 0) ? $error : intval($res['STOPDSJVReturn']['error']['ERNU01']);
                                        $descripcion = $res['STOPDSJVReturn']['error']['ERNU01'] . ' - ' . trim($res['STOPDSJVReturn']['error']['ERDS01']);
                                        
                                        $this->log('Suspension de cheques/chequera - '.$descripcion . ' - ID: ' . $usuario['id'], LOG_ERR); 
                                    }
                                    else if(isset($res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'])){
                                        $arrData = $res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'];
                                        normaliza($arrData);
                                        $last_info = count($arrData)-1;
                                        $referencia = $arrData[$last_info]['STOSTPNRE'];
                                    }
                                    else{
                                        $error = -1;
                                        $this->log('Suspension de cheques/chequera - Error inesperado - ID: ' . $usuario['id'], LOG_ERR);   
                                    }
                                    
                                    $log_descripcion = TransaccionPendiente::$tipo_transaccion[$transaccion['TransaccionPendiente']['tipo']].' '.strval($primer_cheque);
                                    if($segundo_cheque) $log_descripcion.= ' - '. strval($segundo_cheque);
                                    
                                    $this->Bitacora->registraBitacora(
                                        $usuario['id'],
                                        22,
                                        $transaccion['TransaccionPendiente']['cuenta_origen'],
                                        NULL,
                                        NULL,
                                        $referencia,
                                        $log_descripcion,
                                        ($error != 0 )
                                    );
                                    
                                    break;
                                
                                case 'csx': //Suspensión de una chequera
                                
                                    $bdata = $this->TransaccionBeneficiario->obtenerListaDetalle($transaccion['TransaccionPendiente']['id']);
                                    
                                    foreach($bdata as $tb){
                                        list($primer_cheque,$segundo_cheque) = @explode('|', desencriptar($tb['AfiliacionCash']['numero_cuenta']));

                                        $res = $this->Activo->invoca(
                                            'STOPDSJV',
                                            array(
                                                'req'=>array(
                                                    'STOSTPACC'=> substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10),
                                                    'STOSTPFCK'=> trim($primer_cheque),
                                                    'STOSTPRMK'=> trim(desencriptar($transaccion['TransaccionPendiente']['descripcion'])),
                                                    'STOSTPTCK'=> trim($segundo_cheque),
                                                    'STOSTPFLG'=> 'S'
                                                )
                                            )
                                        );
                                    }
                                    $error = 0;
                                    $referencia = null;

                                    //$descripcion = _m('error.fatal');
                                    
                                    if(isset($res['STOPDSJVReturn']['error']['ERRNUM'])){
                                        $error = intval($res['STOPDSJVReturn']['error']['ERRNUM']);
                                        $error = ($error == 0) ? $error : intval($res['STOPDSJVReturn']['error']['ERNU01']);
                                        $descripcion = $res['STOPDSJVReturn']['error']['ERNU01'] . ' - ' . trim($res['STOPDSJVReturn']['error']['ERDS01']);
                                        
                                        $this->log('Suspension de cheques/chequera - '.$descripcion . ' - ID: ' . $usuario['id'], LOG_ERR); 
                                    }
                                    else if(isset($res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'])){
                                        $arrData = $res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'];
                                        normaliza($arrData);
                                        $last_info = count($arrData)-1;
                                        $referencia = $arrData[$last_info]['STOSTPNRE'];
                                    }
                                    else{
                                        $error = -1;
                                        $this->log('Suspension de cheques/chequera - Error inesperado - ID: ' . $usuario['id'], LOG_ERR);   
                                    }
                                    
                                    $log_descripcion = TransaccionPendiente::$tipo_transaccion[$transaccion['TransaccionPendiente']['tipo']].' '.strval($primer_cheque);
                                    if($segundo_cheque) $log_descripcion.= ' - '. strval($segundo_cheque);
                                    
                                    $this->Bitacora->registraBitacora(
                                        $usuario['id'],
                                        22,
                                        $transaccion['TransaccionPendiente']['cuenta_origen'],
                                        NULL,
                                        NULL,
                                        $referencia,
                                        $log_descripcion,
                                        ($error != 0 )
                                    );
                                    
                                    break;
                                
                                case 'csi': //Suspensión de cheque individual
                                
                                    $cheques = @explode(' ', desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']));
                                    foreach($cheques as $ch){
                                        $res = $this->Activo->invoca(
                                            'STOPDSJV',
                                            array(
                                                'req'=>array(
                                                    'STOSTPACC'=> substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10),
                                                    'STOSTPFCK'=> trim($ch),
                                                    'STOSTPRMK'=> trim(desencriptar($transaccion['TransaccionPendiente']['descripcion'])),
                                                    'STOSTPTCK'=> '',
                                                    'STOSTPFLG'=> 'S'
                                                )
                                            )
                                        );
                                    }
                                    
                                    $error = 0;
                                    $referencia = null;
                                    
                                    if(isset($res['STOPDSJVReturn']['error']['ERRNUM'])){
                                        $error = intval($res['STOPDSJVReturn']['error']['ERRNUM']);
                                        $error = ($error == 0) ? $error : intval($res['STOPDSJVReturn']['error']['ERNU01']);
                                        $descripcion = $res['STOPDSJVReturn']['error']['ERNU01'] . ' - ' . trim($res['STOPDSJVReturn']['error']['ERDS01']);
                                        
                                        $this->log('Suspension de cheques/chequera - '.$descripcion . ' - ID: ' . $usuario['id'], LOG_ERR); 
                                    }
                                    else if(isset($res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'])){
                                        $arrData = $res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'];
                                        normaliza($arrData);
                                        $last_info = count($arrData)-1;
                                        $referencia = $arrData[$last_info]['STOSTPNRE'];
                                    }
                                    else{
                                        $error = -1;
                                        $this->log('Suspension de cheques/chequera - Error inesperado - ID: ' . $usuario['id'], LOG_ERR);   
                                    }
                                    
                                    $log_descripcion = TransaccionPendiente::$tipo_transaccion[$transaccion['TransaccionPendiente']['tipo']].' '.desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']);
                                    
                                    $this->Bitacora->registraBitacora(
                                        $usuario['id'],
                                        22,
                                        $transaccion['TransaccionPendiente']['cuenta_origen'],
                                        NULL,
                                        NULL,
                                        $referencia,
                                        $log_descripcion,
                                        ($error != 0 )
                                    );
                                    
                                    break;
                                case 'ip ': //Pago Seniat propios
                                case 'it ': //Pago seniat terceros
                                    
                                    // Verificar el límite acumulado de la transacción

                                    //Se buscan los límites para la transaccion
                                    $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                        $usuario['id'],
                                        ($transaccion['TransaccionPendiente']['tipo']=='ip ') ? 100036 : 100037);

                                    $limite= "";
                                    if(isset($transaccion_detalle)){
                                        if(is_numeric($transaccion_detalle['limite'])){
                                            $limite=$transaccion_detalle['limite'];
                                        }
                                    }   

                                    $monto_mayor_limite = false;
                                    if ($limite != "" && $transaccion['TransaccionPendiente']['monto']>$limite){
                                        $monto_mayor_limite = true;
                                    }

                                    $this->log($transaccion['TransaccionPendiente']['monto'],LOG_ERR);
                                                                        
                                    if(!$monto_mayor_limite){

                                        set_time_limit(180);

                                        $res = $this->Activo->invoca(
                                                'IN097002',
                                                array(
                                                        'req'=>array(
                                                                'IN9702IDUS'=> $identificacion,
                                                                'IN9702BVDM'=> date('m'),
                                                                'IN9702BVDD'=> date('d'),
                                                                'IN9702BVDY'=> date('y'),
                                                                'IN9702FACC'=> substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),10),
                                                                'IN9702IDCO'=> $detalle[0],
                                                                'IN9702RIFP'=> $this->Activo->cambiar_tipo_rif($usuario['tipo_rif'].desencriptar($usuario['rif'])),
                                                                'IN9702CFOR'=> $detalle[1],
                                                                'IN9702NPLA'=> $detalle[2],
                                                                'IN9702RIFC'=> $this->Activo->cambiar_tipo_rif(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])),
                                                                'IN9702PLIQ'=> substr($detalle[3],6).substr($detalle[3],3,2).substr($detalle[3],0,2),
                                                                'IN9702MTOT'=> formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                                'IN9702EFEC'=> formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                                'IN9702CIMN'=> $detalle[4],
                                                                'IN9702CONC'=> $detalle[5],
                                                                'IN9702CADU'=> $detalle[6],
                                                                'IN9702NADU'=> $detalle[7],
                                                                'IN9702NEXP'=> $detalle[8],
                                                                'IN9702NPOR'=> $detalle[9],
                                                                'IN9702FVCT'=> substr($detalle[10],6).substr($detalle[10],3,2).substr($detalle[10],0,2)
                                                         )  
                                                ),180
                                        );

                                        if($transaccion['TransaccionPendiente']['tipo'][1] == 'p'){
                                            $descripcionError = "Pago Impuestos Nacionales/Aduanales - Mismo Titular";
                                        }else{
                                            $descripcionError = "Pago Impuestos Nacionales/Aduanales - Terceros";
                                        }
                                        $referencia = "";

                                        if(isset($res['IN097002Return']['error'])){

                                            $error = trim($res['IN097002Return']['error']['ERRNUM']);

                                            if($error=='0' || $error==''){
                                                    $this->TransaccionPendiente->id = $i;

                                                    $res = $this->TransaccionPendiente->saveField(
                                                            'estatus',
                                                            'ej',
                                                            false
                                                    );

                                                    $referencia = $res['IN097002Return']['error']['ERDF01'];
                                                    $autorizacion = trim($res['IN097002Return']['error']['ERDS01']);

                                                    if (strlen($autorizacion) > 6){
                                                        $transaccion['TransaccionPendiente']['autorizacion'] = substr($autorizacion,0,6).'-'.substr($autorizacion,6);
                                                    }else{
                                                        $transaccion['TransaccionPendiente']['autorizacion'] = $autorizacion;
                                                    }
                                                    $transaccion['TransaccionPendiente']['concepto'] = $descripcionError;
                                                    $transaccion['TransaccionPendiente']['estatus'] = 'PROCESADO';
                                                    $transaccion['TransaccionPendiente']['referencia'] = $referencia;
                                                    $transaccion['TransaccionPendiente']['cuentaDebitar'] = desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']);
                                                    $transaccion['TransaccionPendiente']['rif'] = desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']);

                                                    // Email Orden
                                                    // Email Options
                                                    $this->Email->smtpOptions = Configure::read('email.smtp');
                                                    $this->Email->delivery  = 'smtp';
                                                    $this->Email->from = Configure::read('email.from');
                                                    $this->Email->to = desencriptar($usuario['email_principal']);
                                                    $this->Email->subject = 'Notificacion Pago de Impuesto';
                                                    $this->Email->sendAs = 'html';

                                                    // Informacion del correo
                                                    $this->Email->template  = 'pagoseniat_ordenante';
                                                    $this->set(compact('usuario', 'descripcion'));
                                                    $this->set('data', $transaccion['TransaccionPendiente']);

                                                    // Envio del correo
                                                    $this->Email->send();

                                                    if(trim($transaccion['TransaccionPendiente']['tipo'])=='it'){
                                                    // Enviar por sms la notificación de que realizó el pago al ordenante       
                                                        $this->Sms->enviar(
                                                            array(
                                                                'numcel'=>desencriptar($usuario['celular']),
                                                                'canal1'=>22,
                                                                'xrnde38'=>'IB',
                                                                'fectim'=>'INTERNET BANKING',                                                            
                                                                'cuenta'=>substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),16,4),
                                                                'monto'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                                'fecha'=>date('Ymd'),
                                                                'hora'=>date('His'),
                                                                'refnum'=>substr($referencia,5,4)
                                                            )
                                                        );
                                                    } 

                                                    $this->Bitacora->registraBitacora(
                                                            $usuario['id'], 
                                                            ($transaccion['TransaccionPendiente']['tipo']=='ip ') ? 100036 : 100037, 
                                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']), 
                                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                                            $transaccion['TransaccionPendiente']['monto'], 
                                                            $transaccion['TransaccionPendiente']['referencia'],
                                                            $descripcionError,
                                                            0,
                                                            $autorizacion);

                                                    $this->log('Transaccion Pendiente Pago SENIAT - ' . $descripcionError . " - " . $referencia . " - " . $autorizacion, LOG_ERR);
                                                    // Genera la data para la descarda de pdf
                                                    $this->Session->write('seniat.descarga.pdf', $transaccion['TransaccionPendiente']);
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

                                                    $this->log('Transaccion Pendiente Pago SENIAT - ' . $descripcionError, LOG_ERR);
                                                    $this->Bitacora->registraBitacora(
                                                            $usuario['id'], 
                                                            ($transaccion['TransaccionPendiente']['tipo']=='ip ') ? 100036 : 100037, 
                                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']), 
                                                            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                                            $transaccion['TransaccionPendiente']['monto'],
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
                                                    ($transaccion['TransaccionPendiente']['tipo']=='ip ') ? 100036 : 100037, 
                                                    desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']), 
                                                    desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
                                                    $transaccion['TransaccionPendiente']['monto'],
                                                    $referencia, 
                                                    "Error en la ejecución del servicio IN097002", 
                                                    1);

                                            $this->set(compact('error', 'descripcionError','codigoError'));
                                        }

                                    }else{
                                        $error = '2000';
                                        $codigoError = "";
                                        $this->set(compact('error', 'codigoError'));
                                    }
                                    break;
                                
                                case 'exc': //Mesa Cambio Compra
                                case 'exv': //Mesa Cambio 

                                    $valores = explode('-',$transaccion['TransaccionPendiente']['email_beneficiario']);
                                    $comision=$valores[0];
                                    $montoDivisa=$valores[1];
                                    $tasa=$valores[2];
                                    $producto=$valores[3];
                                    $total=$valores[4];
                                    $origen=$valores[5];
                                    $motivo=$valores[6];
                                    $tasa4Digitos=$valores[7];
                                    $parametro=$valores[8];

                                    
                                    $cuentaDebitar = str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']) ,10), 20, "0", STR_PAD_LEFT);
                                    $cuentaCreditar = str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']) ,10), 20, "0", STR_PAD_LEFT);
                                    $cuentaDebitarA = desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']);
                                    $cuentaCreditarA = desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']);

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

                                    if($transaccion['TransaccionPendiente']['tipo']=='exc'){


                                        $montoRV=str_replace('.', '', $montoDivisa);
                   
                                            //Mesa
                                            if(round($montoRV) > round($parametro)){

                                            $exchange_cod=$valores[9];
                                            $exchange_type_cod=$valores[10];
                                            $exchange_operation_cod=$valores[11];
                                            $currency_cod=$valores[12];
                                            $product_target=$valores[13];
                                            $product_source=$valores[14];
                                            $currency_target_cod=$valores[15];

                                            $trans=1;


                                        //Servicio de token
                                                         
                                                $dataToken = array();
                                                $dataToken["username"] = Configure::read('Api.user');
                                                $dataToken["password"] = Configure::read('Api.password');                        
                                                $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                                                $tokenApp = json_decode(json_encode($tokenApp), true);                        
                                                $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];                
                                               
                                                $dataRest = array();
                                                

                                                $dataRest["exchange_cod"] =  $exchange_cod;
                                                $dataRest["exchange_type_cod"] = $exchange_type_cod;
                                                $dataRest["exchange_operation_cod"] =  $exchange_operation_cod;
                                                $dataRest["currency_cod"] = $currency_target_cod;
                                                $dataRest["ip"] =  Configure::read('ip.client');
                                                $dataRest["canal"] =  4;
                                                $dataRest["tipo"] = '';
                                                $dataRest["customer_type_cod"] = $tipo;
                                                $dataRest["amount_order"] =formatea_monto_toWs($montoDivisa);
                                                $dataRest["product_target"] =$product_target;
                                                $dataRest["product_source"] =$product_source;               
                                                $dataRest["operation_type"] = 'VB';
                                                $dataRest["process_type"] = '01';
                                                $dataRest["account_source"] = $cuentaDebitarA;
                                                $dataRest["account_target"] = $cuentaCreditarA;
                                                $dataRest["identification_type"] =$tipoIdentificacion;
                                                $dataRest["identification"] = $identificacion;
                                                $dataRest["amount_source"] =formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']);
                                                $dataRest["rate_client"] =formatea_monto_toWs($tasa);       
                                                $dataRest["amount_target"] = formatea_monto_toWs($montoDivisa);
                                                $dataRest["amount_commission"] =formatea_monto_toWs($comision);
                                                $dataRest["funds_source"] =$origen;
                                                $dataRest["funds_destination"] =$motivo;

                                                                                                              
                                             $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeTableNew',"POST",$dataRest, $token);



                                             $response = (array) $response;
                                             $responseError=(array) $response['data'];
                                             $responseError=(array) $response['data'][0];
                                             $referencia=(array) $responseError['INVCREFE'];                 
                                             $responseError=(array) $responseError['error'];



                                          if(!isset($responseError['ERRNUM'])){

                        
                                            $referencia=$referencia[0];
                                            
                                             
                                            $dataRest = array();

                                                $dataRest["exchange_cod"] =  $exchange_cod;
                                                $dataRest["exchange_type_cod"] = $exchange_type_cod;
                                                $dataRest["exchange_operation_cod"] =  $exchange_operation_cod;
                                                $dataRest["currency_cod"] = $currency_target_cod;
                                                $dataRest["ip"] =  Configure::read('ip.client');
                                                $dataRest["canal"] =  4;
                                                $dataRest["tipo"] = '';
                                                $dataRest["customer_type_cod"] = $tipo;
                                                $dataRest["amount_order"] =formatea_monto_toWs($montoDivisa);
                                                $dataRest["product_target"] =$product_target;
                                                $dataRest["product_source"] =$product_source;                                                
                                                $dataRest["operation_type"] = 'PC';
                                                $dataRest["process_type"] = '01';
                                                $dataRest["account_source"] = $cuentaDebitarA;
                                                $dataRest["account_target"] = $cuentaCreditarA;
                                                $dataRest["identification_type"] =$tipoIdentificacion;
                                                $dataRest["identification"] = $identificacion;
                                                $dataRest["amount_source"] =formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']);
                                                $dataRest["rate_client"] =formatea_monto_toWs($tasa);
                                                $dataRest["amount_target"] = formatea_monto_toWs($montoDivisa);
                                                $dataRest["amount_commission"] =formatea_monto_toWs($comision);
                                                $dataRest["funds_source"] =$origen;
                                                $dataRest["funds_destination"] =$motivo;
                                                $dataRest["reference_number"] =$referencia;
                                           

                                                                    
                                                                                                                        
                                             $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeTableNew',"POST",$dataRest, $token);
                                        //Bloqueo y validacion*/

                                         

                                                     $response = (array) $response;
                                                     $responseError=(array) $response['data'];
                                                     $responseError=(array) $response['data'][0];
                                                     $referencia=(array) $responseError['INVCREFE'];
                                                     $montoAdjudicado=(array) $responseError['INVCMTOD'];
                                                     $transaccion['TransaccionPendiente']['monto_divisaAdjudicado']=$montoAdjudicado[0];
                                                     $montoTotal=(array) $responseError['INVCMTOO'];
                                                     $montoComision=(array) $responseError['INVCMTOC'];
                                                     $total=$montoTotal[0];
                                                     $transaccion['TransaccionPendiente']['resultado']=1;
                                                     $transaccion['TransaccionPendiente']['total']=$total;
                                                      $transaccion['TransaccionPendiente']['monto']=$total;
                                                     $responseError=(array) $responseError['error'];
                                                     $referencia=$referencia[0];
                                                     $transaccion['TransaccionPendiente']['lote']=$referencia;
                                                     $transaccion['TransaccionPendiente']['date']=date('d/m/Y');

                                                   

                                                        

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
                                                    $total,
                                                    $referencia,
                                                    NULL,
                                                    0
                                                );                                                                                             
                                                                                            
                                                 $save = new TransaccionMesaCambio();

                                                
                                               $guardar = $save->save(
                                                    array(
                                                        'TransaccionMesaCambio'=>array(
                                                        'usuario_id'=>$usuario['id'],
                                                        'tipo'=>'com',
                                                        'estatus'=>'ej',
                                                        'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                                        'fecha_hora_valor'=>date('Y-m-d H:i:s'),
                                                        'monto_divisa'=>formatea_monto_toWs($montoDivisa),
                                                        'tasa'=> formatea_monto_toWs($tasa),
                                                        'comision'=> formatea_monto_toWs($comision),
                                                        'monto'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                        'total'=>formatea_monto_toWs($total),
                                                        'moneda'=>$transaccion['TransaccionPendiente']['moneda'],                                   
                                                        'tipo_rif'=>$usuario['tipo_rif'],
                                                        'rif'=>$usuario['rif'],
                                                        'cuenta_origen'=>$transaccion['TransaccionPendiente']['cuenta_origen'],
                                                        'cuenta_destino'=>$transaccion['TransaccionPendiente']['cuenta_destino'],
                                                        'referencia' => $referencia,
                                                        'monto_adjudicado'=> $montoAdjudicado[0],
                                                        'lote'=>$referencia,
                                                        )
                                                    ),
                                                    false
                                                );
                                                
                                                $res = $this->TransaccionPendiente->saveField(
                                                    'estatus',
                                                    'ej',
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
                                                $transaccion['TransaccionPendiente']['monto'],
                                                NULL,
                                                $descripcion,       
                                                1
                                            );
                                            
                                        
                                        }

                                        }else{

                                            $product_target=$valores[9];
                                            $product_source=$valores[10];
                                            $tasa4Digitos=$valores[11];
                                            
                                           
                                        
                                            $trans=2;
                      
                                           //Servicio de token
                                                $dataToken = array();
                                                $dataToken["username"] = Configure::read('Api.user');
                                                $dataToken["password"] = Configure::read('Api.password');                        
                                                $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                                                $tokenApp = json_decode(json_encode($tokenApp), true);                        
                                                $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];                  
                                           
                                            $dataRest = array();

                                            $dataRest["product_target"] =$product_target;
                                            $dataRest["product_source"] = $product_source;
                                            $dataRest["canal"] = 4;
                                            $dataRest["operation_type"] = "VB";
                                            $dataRest["process_type"] = '01';
                                            $dataRest["account_source"] = $cuentaDebitarA;
                                            $dataRest["account_target"] = $cuentaCreditarA;
                                            $dataRest["identification_type"] =$tipoIdentificacion;
                                            $dataRest["identification"] = $identificacion;
                                            $dataRest["amount_source"] =formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']);
                                            $dataRest["amount_target"] = formatea_monto_toWs($montoDivisa);
                                            $dataRest["amount_commission"] =formatea_monto_toWs($comision);
                                            $dataRest["rate_client"] =$tasa4Digitos;
                                            $dataRest["funds_source"] =$origen;
                                            $dataRest["funds_destination"] =$motivo;
                                            $dataRest["intervention_date_ini"] = date('ymd');
                                            $dataRest["intervention_date_fin"] = date('ymd');  
                                            $dataRest["intervention_date_val"] = date('ymd'); 
                                            $dataRest["date_BCV"] = date('ymd'); 
                                            $dataRest["userEmail"] = desencriptar($usuario['email_principal']);
                                            $dataRest["userPhone"] =desencriptar($usuario['celular']);
                                            $dataRest["userName"] = desencriptar($usuario['nombre']);


                                                                          
                                            $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeMenudeo',"POST",$dataRest, $token);



                                             $response = (array) $response;
                                             $responseError=(array) $response['data'];
                                             $responseError=(array) $response['data'][0];
                                             $referencia=(array) $responseError['NIBMREFE'];
                                             $transaccion['TransaccionPendiente']['lote']=$referencia[0];
                                             $transaccion['TransaccionPendiente']['date']=date('d/m/Y');                 
                                             $responseError=(array) $responseError['error'];

                                             if(!isset($responseError['ERRNUM'])){

                        
                                                 $referencia=$referencia[0];
                                                 $transaccion['TransaccionPendiente']['lote']=$referencia;
                                                 $transaccion['TransaccionPendiente']['date']=date('d/m/Y');
                                                 
                                                                     
                                                    $error = 0;

                                                    $this->Bitacora->registraBitacora(
                                                    $usuario['id'],
                                                    101074,
                                                    $cuentaDebitarA,
                                                    $cuentaCreditarA,
                                                    $total,
                                                    $referencia,
                                                    NULL,
                                                    0
                                                );                                                                                             
                                                
                                                $save = new TransaccionMesaCambio();
                                                
                                                $guardar = $save->save(
                                                    array(
                                                        'TransaccionMesaCambio'=>array(
                                                        'usuario_id'=>$usuario['id'],
                                                        'tipo'=>'com',
                                                        'estatus'=>'ej',
                                                        'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                                        'fecha_hora_valor'=>date('Y-m-d H:i:s'),
                                                        'monto_divisa'=>formatea_monto_toWs($montoDivisa),
                                                        'tasa'=> formatea_monto_toWs($tasa),
                                                        'comision'=> formatea_monto_toWs($comision),
                                                        'monto'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                        'total'=>formatea_monto_toWs($total),
                                                        'moneda'=>$transaccion['TransaccionPendiente']['moneda'],                                   
                                                        'tipo_rif'=>$usuario['tipo_rif'],
                                                        'rif'=>$usuario['rif'],
                                                        'cuenta_origen'=>$transaccion['TransaccionPendiente']['cuenta_origen'],
                                                        'cuenta_destino'=>$transaccion['TransaccionPendiente']['cuenta_destino'],
                                                        'referencia' => $referencia,
                                                        'lote'=>$referencia,   
                                                        )
                                                    ),
                                                    false
                                                );
                                                
                                                $res = $this->TransaccionPendiente->saveField(
                                                    'estatus',
                                                    'ej',
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
                                                $transaccion['TransaccionPendiente']['monto'],
                                                NULL,
                                                $descripcion,       
                                                1
                                            );
                                            
                                         
                                        }
                                                        
                                     } 
                            
                                    //Aqui inicia Compra de Mesa                                         
                                                
                                        }else{
                                            //aqui inicia Venta

                                                                                 
                                             $montoRV=str_replace('.', '', $montoDivisa);

                                            //Mesa
                                            if(round($montoRV) > round($parametro)){

                                            
                                            $exchange_cod=$valores[9];
                                            $exchange_type_cod=$valores[10];
                                            $exchange_operation_cod=$valores[11];
                                            $currency_cod=$valores[12];
                                            $product_target=$valores[13];
                                            $product_source=$valores[14];
                                            $currency_target_cod=$valores[15];
                                            $name_product=$valores[15];

                                            $trans=1;
                      
                                              //Servicio de token
                                                $dataToken = array();
                                                $dataToken["username"] = Configure::read('Api.user');
                                                $dataToken["password"] = Configure::read('Api.password');                        
                                                $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                                                $tokenApp = json_decode(json_encode($tokenApp), true);                        
                                                $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];                       
                                               
                                                $dataRest = array();

                                                $dataRest["exchange_cod"] =  $exchange_cod;
                                                $dataRest["exchange_type_cod"] = $exchange_type_cod;
                                                $dataRest["exchange_operation_cod"] =  $exchange_operation_cod;
                                                $dataRest["currency_cod"] = $currency_target_cod;
                                                $dataRest["ip"] =  Configure::read('ip.client');
                                                $dataRest["canal"] =  4;
                                                $dataRest["tipo"] = '';
                                                $dataRest["customer_type_cod"] = $tipo;
                                                $dataRest["amount_order"] =formatea_monto_toWs($montoDivisa);
                                                 $dataRest["product_target"] =$product_target;
                                                $dataRest["product_source"] =$product_source;
                                                
                                                $dataRest["operation_type"] = 'VB';
                                                $dataRest["process_type"] = '02';
                                                $dataRest["account_source"] = $cuentaDebitarA;
                                                $dataRest["account_target"] = $cuentaCreditarA;
                                                $dataRest["identification_type"] =$tipoIdentificacion;
                                                $dataRest["identification"] = $identificacion;
                                                $dataRest["amount_source"] =formatea_monto_toWs($montoDivisa);
                                                $dataRest["rate_client"] =formatea_monto_toWs($tasa);
                                                
                                                $dataRest["amount_target"] = formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']);
                                                $dataRest["amount_commission"] =formatea_monto_toWs( $comision);
                                                $dataRest["funds_source"] =$origen;
                                                $dataRest["funds_destination"] =$motivo;


                        
                                                                          
                                              $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeTableNew',"POST",$dataRest, $token);



                                             $response = (array) $response;
                                             $responseError=(array) $response['data'];
                                             $responseError=(array) $response['data'][0];
                                             $referencia=(array) $responseError['INVCREFE'];                 
                                             $responseError=(array) $responseError['error'];

                                          if(!isset($responseError['ERRNUM'])){

                        
                                            $referencia=$referencia[0];
                                            $transaccion['TransaccionPendiente']['lote']=$referencia;
                                            $transaccion['TransaccionPendiente']['date']=date('d/m/Y');
                                           
                                            
                                             
                                            $dataRest = array();

                                                $dataRest["exchange_cod"] =  $exchange_cod;
                                                $dataRest["exchange_type_cod"] = $exchange_type_cod;
                                                $dataRest["exchange_operation_cod"] =  $exchange_operation_cod;
                                                $dataRest["currency_cod"] = $currency_target_cod;
                                                $dataRest["ip"] =  Configure::read('ip.client');
                                                $dataRest["canal"] =  4;
                                                $dataRest["tipo"] = '';
                                                $dataRest["customer_type_cod"] = $tipo;
                                                $dataRest["amount_order"] =formatea_monto_toWs($montoDivisa);
                                                $dataRest["product_target"] =$product_target;
                                                $dataRest["product_source"] =$product_source;                                                
                                                $dataRest["operation_type"] = 'PC';
                                                $dataRest["process_type"] = '02';
                                                $dataRest["account_source"] = $cuentaDebitarA;
                                                $dataRest["account_target"] = $cuentaCreditarA;
                                                $dataRest["identification_type"] =$tipoIdentificacion;
                                                $dataRest["identification"] = $identificacion;
                                                $dataRest["amount_source"] =formatea_monto_toWs($montoDivisa);
                                                $dataRest["rate_client"] =formatea_monto_toWs($tasa);
                                                $dataRest["amount_target"] = formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']);
                                                $dataRest["amount_commission"] =formatea_monto_toWs($comision);
                                                $dataRest["funds_source"] =$origen;
                                                $dataRest["funds_destination"] =$motivo;
                                                $dataRest["reference_number"] =$referencia;
                                           

                                                                    
                                                                                                                        
                                           $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeTableNew',"POST",$dataRest, $token);
                                        //Bloqueo y validacion*/

                                         

                                                     $error = "-1";
                                                     $descripcion = "Error inesperado, transaccion no disponible.";
                                                     $response = (array) $response;
                                                     $responseError=(array) $response['data'];
                                                     $responseError=(array) $response['data'][0];
                                                     $referencia=(array) $responseError['INVCREFE'];
                                                     $montoAdjudicado=(array) $responseError['INVCMTOO'];
                                                     $transaccion['TransaccionPendiente']['monto_divisaAdjudicado']=$montoAdjudicado[0];
                                                     $montoTotal=(array) $responseError['INVCMTOD'];
                                                     $montoComision=(array) $responseError['INVCMTOC'];
                                                     $total=$montoTotal[0]-$montoComision[0];
                                                     $transaccion['TransaccionPendiente']['resultado']=1;
                                                     $transaccion['TransaccionPendiente']['total']=$total;
                                                     $transaccion['TransaccionPendiente']['monto']=$total;
                                                     $responseError=(array) $responseError['error'];
                                                     $referencia=$referencia[0];
                                                     $transaccion['TransaccionPendiente']['lote']=$referencia;
                                                     $transaccion['TransaccionPendiente']['date']=date('d/m/Y');
                                                        

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
                                                    $total,
                                                    $referencia,
                                                    NULL,
                                                    0
                                                );                                                                                             
                                                
                                                 $save = new TransaccionMesaCambio();
                                                   $this->data['TransaccionPendiente']['referencia']= $referencia;

                                                
                                                $guardar = $save->save(
                                                    array(
                                                        'TransaccionMesaCambio'=>array(
                                                        'usuario_id'=>$usuario['id'],
                                                        'tipo'=>'ven',
                                                        'estatus'=>'ej',
                                                        'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                                        'fecha_hora_valor'=>date('Y-m-d H:i:s'),
                                                        'monto_divisa'=>formatea_monto_toWs($montoDivisa),
                                                        'tasa'=> formatea_monto_toWs($tasa),
                                                        'comision'=> formatea_monto_toWs($comision),
                                                        'monto'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                        'total'=>formatea_monto_toWs($total),
                                                        'moneda'=>$transaccion['TransaccionPendiente']['moneda'],                                   
                                                        'tipo_rif'=>$usuario['tipo_rif'],
                                                        'rif'=>$usuario['rif'],
                                                        'cuenta_origen'=>$transaccion['TransaccionPendiente']['cuenta_origen'],
                                                        'cuenta_destino'=>$transaccion['TransaccionPendiente']['cuenta_destino'],
                                                        'referencia' => $referencia,
                                                        'monto_adjudicado'=> $montoAdjudicado[0],
                                                        'lote'=>$referencia
                                                        )
                                                    ),
                                                    false
                                                );
                                                
                                                $res = $this->TransaccionPendiente->saveField(
                                                    'estatus',
                                                    'ej',
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
                                                $transaccion['TransaccionPendiente']['monto'],
                                                NULL,
                                                $descripcion,       
                                                1
                                            );
                                            
                                            
                                        }

                                        }else{

                                            $product_target=$valores[9];
                                            $product_source=$valores[10];
                                            $tasa4Digitos=$valores[11];

                                            $trans=2;
                      
                          //Servicio de token
                                            $dataToken = array();
                                            $dataToken["username"] = Configure::read('Api.user');
                                            $dataToken["password"] = Configure::read('Api.password');                        
                                            $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                                            $tokenApp = json_decode(json_encode($tokenApp), true);                        
                                            $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];               
                                            $dataRest = array();
                                            $dataRest["product_target"] =$product_target;
                                            $dataRest["product_source"] = $product_source;
                                            $dataRest["canal"] = 4;
                                            $dataRest["operation_type"] = "VB";
                                            $dataRest["process_type"] = '02';
                                            $dataRest["account_source"] = $cuentaDebitarA;
                                            $dataRest["account_target"] = $cuentaCreditarA;
                                            $dataRest["identification_type"] =$tipoIdentificacion;
                                            $dataRest["identification"] = $identificacion;
                                            $dataRest["amount_source"] =formatea_monto_toWs($montoDivisa);
                                            $dataRest["amount_target"] = formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']);
                                            $dataRest["amount_commission"] =formatea_monto_toWs($comision);
                                            $dataRest["rate_client"] =$tasa4Digitos;
                                            $dataRest["funds_source"] =$origen;
                                            $dataRest["funds_destination"] =$motivo;
                                            $dataRest["intervention_date_ini"] = date('ymd');
                                            $dataRest["intervention_date_fin"] = date('ymd');  
                                            $dataRest["intervention_date_val"] = date('ymd'); 
                                            $dataRest["date_BCV"] = date('ymd');
                                            $dataRest["userEmail"] = desencriptar($usuario['email_principal']);
                                            $dataRest["userPhone"] =desencriptar($usuario['celular']);
                                            $dataRest["userName"] = desencriptar($usuario['nombre']);
                                            $response = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/dbMdC/exchangeMenudeo',"POST",$dataRest, $token);
                                            $response = (array) $response;
                                            $responseError=(array) $response['data'];
                                            $responseError=(array) $response['data'][0];
                                            $referencia=(array) $responseError['NIBMREFE'];
                                            $transaccion['TransaccionPendiente']['lote']=$referencia[0];
                                            $transaccion['TransaccionPendiente']['date']=date('d/m/Y');                
                                            $responseError=(array) $responseError['error'];
                                            if(!isset($responseError['ERRNUM'])){
                                               $referencia=$referencia[0];
                                                                                                                   
                                                    $error = 0;

                                                    $this->Bitacora->registraBitacora(
                                                    $usuario['id'],
                                                    101074,
                                                    $cuentaDebitarA,
                                                    $cuentaCreditarA,
                                                    $total,
                                                    $referencia,
                                                    NULL,
                                                    0
                                                );     
                                                $save = new TransaccionMesaCambio();
                                                  $this->data['TransaccionPendiente']['referencia']= $referencia;

                                                
                                                $guardar = $save->save(
                                                    array(
                                                        'TransaccionMesaCambio'=>array(
                                                        'usuario_id'=>$usuario['id'],
                                                        'tipo'=>'ven',
                                                        'estatus'=>'ej',
                                                        'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                                        'fecha_hora_valor'=>date('Y-m-d H:i:s'),
                                                        'monto_divisa'=>formatea_monto_toWs($montoDivisa),
                                                        'tasa'=> formatea_monto_toWs($tasa),
                                                        'comision'=> formatea_monto_toWs($comision),
                                                        'monto'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                        'total'=>formatea_monto_toWs($total),
                                                        'moneda'=>$transaccion['TransaccionPendiente']['moneda'],                                   
                                                        'tipo_rif'=>$usuario['tipo_rif'],
                                                        'rif'=>$usuario['rif'],
                                                        'cuenta_origen'=>$transaccion['TransaccionPendiente']['cuenta_origen'],
                                                        'cuenta_destino'=>$transaccion['TransaccionPendiente']['cuenta_destino'],
                                                        'referencia' => $referencia,
                                                        'lote'=>$referencia,  
                                                        )
                                                    ),
                                                    false
                                                );
                                                
                                                $res = $this->TransaccionPendiente->saveField(
                                                    'estatus',
                                                    'ej',
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
                                                $transaccion['TransaccionPendiente']['monto'],
                                                NULL,
                                                $descripcion,       
                                                1
                                            );
                                                                                     
                                        }
                                                    
                                     } 

                                    }

                                   
                                    break;
                                    
                                case 'fxc': //Intervencion Compra                                
                                    
                                    $valores = explode('-',$transaccion['TransaccionPendiente']['email_beneficiario']);
                                    $comision=$valores[0];
                                    $montoDivisa=$valores[1];
                                    $tasa=$valores[2];
                                    $producto=$valores[3];
                                    $total=$valores[4];
                                    $origen=$valores[5];
                                    $motivo=$valores[6];
                                    $exchangeCod=$valores[7];
                                    $transactionTargetCod=$valores[8];
                                    $transactionSourceCod=$valores[9];
                                    $exchangeStart=str_replace("_", "-",$valores[10]);
                                    $exchangeEnd=str_replace("_", "-",$valores[11]);
                                    $tasaCuenta=$valores[12];
                                    $exchangeMarketId=$valores[13];

                                    $cuentaDebitar = str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']) ,10), 20, "0", STR_PAD_LEFT);
                                    $cuentaCreditar = str_pad(substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']) ,10), 20, "0", STR_PAD_LEFT);
                                    $cuentaDebitarA = desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']);
                                    $cuentaCreditarA = desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']);
                                    
                                    if($transaccion['TransaccionPendiente']['tipo']=='fxc'){
                                         
                                        $date = date("Y-m-d H:i:s").'000'; 
                                        
                                        //Bloqueo y validacion
                                        $resBloqueoVal = $this->Activo->invoca(
                                            'NIBVINT01',
                                            array(
                                                'req'=>array(
                                                    'canal'=>'',
                                                    'INTCTIPO'=>'VB',
                                                    'INTCINTE'=>$exchangeCod,
                                                    'INTCCTAD'=>$cuentaCreditar,//Divisas
                                                    'INTCCTAO'=>$cuentaDebitar,//Bs
                                                    'INTCMTOO'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                    'INTCMTOC'=>formatea_monto_toWs($comision),
                                                    'INTCMTOD'=>formatea_monto_toWs($montoDivisa),
                                                    'INTCTCLI'=>$tasaCuenta,
                                                    'INTCDDTR'=>$transactionTargetCod,
                                                    'INTCDOTR'=>$transactionSourceCod,
                                                    'INTCPROC'=>'01',                                                                                              
                                                    'INTCORIF'=>$origen[1],
                                                    'INTCDESF'=>$motivo[1],
                                                    'INTCFIIN'=>$exchangeStart,
                                                    'INTCFFIN'=>$exchangeEnd
                                                )   
                                            )
                                        );
                                        
                                        if(!isset($resBloqueoVal['NIBVINT01Return']['error'])){
                        
                                            $referencia = $resBloqueoVal['NIBVINT01Return']['NIBVINT01']['INTCREFE'];
                                            
                                            $res = $this->Activo->invoca(
                                                'NIBVINT01',
                                                array(
                                                    'req'=>array(
                                                        'canal'=>'', 
                                                        'INTCTIPO'=>'PC',
                                                        'INTCINTE'=>$exchangeCod,
                                                        'INTCCTAD'=>$cuentaCreditar,//Divisas
                                                        'INTCCTAO'=>$cuentaDebitar,//Bs
                                                        'INTCMTOO'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                        'INTCMTOC'=>formatea_monto_toWs($comision),
                                                        'INTCMTOD'=>formatea_monto_toWs($montoDivisa),
                                                        'INTCTCLI'=>$tasaCuenta,
                                                        'INTCDDTR'=>$transactionTargetCod,
                                                        'INTCDOTR'=>$transactionSourceCod,
                                                        'INTCPROC'=>'01',                                                                                              
                                                        'INTCORIF'=>$origen[1],
                                                        'INTCDESF'=>$motivo[1],
                                                        'INTCFIIN'=>$exchangeStart,
                                                        'INTCFFIN'=>$exchangeEnd,
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
                                                    $transaccion['TransaccionPendiente']['monto'],
                                                    NULL,
                                                    $descripcion,       
                                                    1
                                                );                                                        
                                                
                                                $queryInsert = $this->Intervencion->save($producto,1,$cuentaDebitarA,$cuentaCreditarA,formatea_monto_toWs($total),formatea_monto_toWs($montoDivisa),formatea_monto_toWs($tasa),formatea_monto_toWs($comision),1,'RCZ',formatea_monto_toWs($montoDivisa),formatea_monto_toWs($total),$origen,$motivo,'0',substr($cuentaDebitarA ,10),$transactionTargetCod,$date,$date,$exchangeCod,$usuario['id'],$exchangeMarketId);
                                                
                                                $res = $this->TransaccionPendiente->saveField('estatus','ra',false);
                                                
                                            }else{                                           

                                                $error = 0;

                                                $this->Bitacora->registraBitacora(
                                                    $usuario['id'],
                                                    101088,
                                                    $cuentaDebitarA,
                                                    $cuentaCreditarA,
                                                    $transaccion['TransaccionPendiente']['monto'],
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
                                               
                                                $save = $this->TransaccionIntervencion->save(
                                                    array(
                                                        'TransaccionIntervencion'=>array(
                                                        'usuario_id'=>$usuario['id'],
                                                        'tipo'=>'com',
                                                        'estatus'=>'ej',
                                                        'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                                                        'monto_divisa'=>formatea_monto_toWs($montoDivisa),
                                                        'tasa'=> formatea_monto_toWs($tasa),
                                                        'comision'=> formatea_monto_toWs($comision),
                                                        'monto'=>formatea_monto_toWs($transaccion['TransaccionPendiente']['monto']),
                                                        'total'=>formatea_monto_toWs($total),
                                                        'moneda'=>$transactionTargetCod,                                   
                                                        'tipo_rif'=>$usuario['tipo_rif'],
                                                        'rif'=>$usuario['rif'],
                                                        'cuenta_origen'=>encriptar($cuentaDebitarA),
                                                        'cuenta_destino'=>encriptar($cuentaCreditarA),
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

                                                $queryInsert = $this->Intervencion->save($producto,1,$cuentaDebitarA,$cuentaCreditarA,formatea_monto_toWs($total),formatea_monto_toWs($montoDivisa),formatea_monto_toWs($tasa),formatea_monto_toWs($comision),1,'LQD',formatea_monto_toWs($montoDivisa),formatea_monto_toWs($total),$origen,$motivo,$referencia,substr($cuentaDebitarA ,10),$transactionTargetCod,$fechaValor,$fechaProceso,$exchangeCod,$usuario['id'],$exchangeMarketId);
                                                
                                                $res = $this->TransaccionPendiente->saveField(
                                                    'estatus',
                                                    'ej',
                                                    false
                                                );
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
                                                $transaccion['TransaccionPendiente']['monto'],
                                                NULL,
                                                $descripcion,       
                                                1
                                            );
                                            
                                            $queryInsert = $this->Intervencion->save($producto,1,$cuentaDebitarA,$cuentaCreditarA,formatea_monto_toWs($total),formatea_monto_toWs($montoDivisa),formatea_monto_toWs($tasa),formatea_monto_toWs($comision),1,'RCZ',formatea_monto_toWs($montoDivisa),formatea_monto_toWs($total),$origen,$motivo,'0',substr($cuentaDebitarA ,10),$transactionTargetCod,$date,$date,$exchangeCod,$usuario['id'],$exchangeMarketId);
                                            
                                            $res = $this->TransaccionPendiente->saveField('estatus','ra',false);
                                        }                 
                                    } 
                                    
                                    break;
                            } // end switch tipo

                        } // end if
                
                    break;
                } // end switch($estado['accion'])

             

               


                if($error){
                    
                    $hubo_error = true;
                    
                    $this->log('Error al ejecutar transacción pendiente '.$transaccion['TransaccionPendiente']['id'].': '.$error.' '.$descripcion,LOG_ERR);
                    
                    $ds->rollback($this->TransaccionPendiente);
                    
                    // Buscar descripción amigable a usuario
                    
                    if($transaccion['TransaccionPendiente']['tipo'][0]=='p'){
                        switch($error){
                            case 9920:
                                $descripcion = _m('deuda.tdc.menor', 'de la tarjeta');
                                break;
                            case 8412:
                                $descripcion = _m('monto.excedido', 'del pago');
                                break;
                            case 182:
                                $descripcion = _m('producto.invalido', 'el número cuenta a deditar');
                                break;
                            case 800:
                                $descripcion = _m('producto.invalido', 'el número de tarjeta a acreditar');
                                break;
                            case 8120:
                                $descripcion = _m('usuario.no.autorizado', 'el número cuenta a deditar');
                                break;
                            case 1616:
                                $descripcion = _m('cuenta.sobregirada', 'el número cuenta a deditar');
                                break;
                            case 862:
                                $descripcion = _m('cuenta.sobregirada');
                                break;                                                         
                        }
                    } else if($transaccion['TransaccionPendiente']['tipo'][0]=='t'){
                        switch($error){
                            case 2149:
                                $descripcion = _m('cuenta.erronea', 'el número cuenta a acreditar');
                                break;
                            case 182:
                                $descripcion = _m('producto.invalido', 'el número cuenta a acreditar');
                                break;
                            case 223:
                                $descripcion = _m('cuenta.inactiva', 'la cuenta');
                                break;
                            case 1912:
                                $descripcion = _m('monto.excedido', 'de la transferencia');
                                break;
                            case 1616:
                                $descripcion = _m('cuenta.sobregirada');
                                break;
                            case 862:
                                $descripcion = _m('cuenta.sobregirada');
                                break;                                                        
                        }
                    }else if($transaccion['TransaccionPendiente']['tipo'][0]=='c'){
                        switch($error){
                            case 1057:
                                $descripcion = _m('suspension.cheque.error', ($transaccion['TransaccionPendiente']['tipo'][2]=='i') ? 'el cheque' : (($transaccion['TransaccionPendiente']['tipo'][2]=='r') ? 'los cheques' : 'la chequera'));
                                break;
                            default:
                                $descripcion = _m('operacion.fallida'); 
                        }
                    }else if($transaccion['TransaccionPendiente']['tipo'][0]=='i'){
                        switch($error){
                            case 'A4':
                            case 'G4':
                            case 'A3':
                            case 'K3':
                            case '77':
                            case '91':
                            case '93':
                            case '98':
                            case '01':
                                $descripcion = _m('sin.comunicacion.seniat') . ' ' . $codigoError;
                                break;
                            case '0182':
                            case '0223':
                                $descripcion = _m('producto.invalido', 'el número cuenta a debitar');
                                break;
                            case '2003':
                            case '8028':
                            case '0608':
                                $descripcion = _m('cuenta.no.beneficiario', '');
                                break;
                            case '1616':
                                $descripcion = _m('cuenta.sobregirada', 'el número cuenta a debitar');
                                break;
                            case '2000':
                                $descripcion = _m('monto.limite.usuario','del compromiso de pago');
                                break;
                            case 'J4':
                                $descripcion = _m('rif.vencido');
                                break;
                            case 'I8':
                                $descripcion = _m('rif.no.autorizado');
                                break;
                            case 'ST1':
                                $descripcion = _m('transaccion.timeout');
                                break;
                            case 'M9':
                            case '95':
                            case '94':
                            case 'I7':
                            case 'I3':
                                $descripcion = _m('ya.pagado.seniat');
                                break;
                            default:
                        }
                        $descripcion = _m('pago.fallido') . ' ' . $codigoError; 
                    }else if($transaccion['TransaccionPendiente']['tipo']=='rcd' || $transaccion['TransaccionPendiente']['tipo']=='scd' || $transaccion['TransaccionPendiente']['tipo']=='ssi' || $transaccion['TransaccionPendiente']['tipo']=='rsi' || $transaccion['TransaccionPendiente']['tipo']=='rsv'){
                            switch($error){
                                case 203:
                                        $descripcion = _m('servicio.recarga.invalido',array('121','Digitel','203'));
                                        break;
                                case 204:
                                        $descripcion = _m('monto.recarga.invalido',array(_p('multiplo.digitel'),formatea_monto_fromWs(_p('recarga_minima_digitel')),formatea_monto_fromWs(_p('recarga_maxima_digitel')),'204'));
                                        break;
                                case 205:
                                        $descripcion = _m('gsm.invalido',array('121','Digitel','205'));
                                        break;
                                case 207:
                                        $descripcion = _m('operacion.excedido');
                                        break;
                                case 201:
                                case 202:
                                case 206:
                                case 208:    
                                        $descripcion = _m('procesar.solicitud');
                                        break;
                                case 1616:
                                        $descripcion = _m('cuenta.sobregirada');
                                        break;
                                case 862:
                                        $descripcion = _m('cuenta.sobregirada');                                    
                                        break;
                                case 05:
                                        $descripcion=_m('digitel.bloqueo');
                                        break; 
                                case 07: 
                                    $descripcion=_m('digitel.noPermitido');
                                    break;  
                                case 13: 
                                    $descripcion=_m('digitel.montoNovalido');
                                    break;  

                                case 280: 
                                    $descripcion=_m('servicio.recarga.invalido') . ' [ SIMPLE TV]';
                                    break;  
                                default:
                                    $descripcion = _m('procesar.solicitud');
                            }
                    }else if($transaccion['TransaccionPendiente']['tipo'][0]=='e' || $transaccion['TransaccionPendiente']['tipo'][0]=='f'){  
                        $descripcion;
                    } else {                                                
                        switch($error){

                            case 1049:
                                $descripcion = _m('cuenta.producto');
                                break;  
                            case 6029:
                                $descripcion = _m('cuenta.simbolo');
                                break;                                                          
                            case 1616:
                                $descripcion = _m('cuenta.sobregirada');
                                break;
                            case 862:
                                $descripcion = _m('cuenta.sobregirada');
                                break;
                            case -4:
                                $descripcion = $descripcion;
                                break;
                            case 215:
                                $descripcion = _m('lote.repetido');
                                break;                                                            
                            default:
                                $descripcion = _m('operacion.fallida');                             
                        }
                    }
                    
                    // Mostrar mensaje de error amigable al usuario
                    
                    $transaccion['descripcion_error']= $descripcion;
                } else {
                    // Si todo salió bien, hacer commit
                    $ds->commit($this->TransaccionPendiente);

                    

                    $this->TransaccionPendiente->modificarSimpletv($idRegistro, $infSimple);

                }



                
                $transacciones []= $transaccion;

                

            } // end foreach
        } else {
            $this->redirect('accion_multiple');
        }
        
        $this->Session->write('transacciones_pendientes.transacciones_ejecutadas', $transacciones);




        

        
        $this->Session->write('transacciones_pendientes.descarga', $estado);
                    
        $cuentas = $this->Activo->buscarInstrumentos2(array('SAV','DDA','NOW'));
        
        $cuentas_map = array();
        
        foreach($cuentas as $i){
            $cuentas_map[$i['SUMACN']] = $i['SUMAVB'];
        }
        
        $agrupadas = array();
        $tipo_operacion = '';

        foreach($transacciones as &$i){
            $agrupadas[$i['TransaccionPendiente']['cuenta_origen']][$i['TransaccionPendiente']['tipo'][0]] []= $i;            
            $tipo_operacion = $i['TransaccionPendiente']['tipo'];               
        }
        
        $todas = $transacciones;

        $this->set(compact('hubo_error', 'agrupadas','estado','cuentas_map', 'bancos', 'todas', 'error','tipo_operacion'));
        
        $this->layout = 'interna_impresion';

        $this->set('seccion_actual','aprobaciones.resumen');
        
        $this->Session->delete('transacciones_pendientes.accion_multiple');
    }
    
    /**
     * Método para la descarga del pdf del resultado de la acción múltiple
     */
    function accion_multiple_descargar(){
        if(!validarTransaccion(array(54,55,57))) $this->redirect('/consultas/integral');
    
        if(!$this->Session->check('transacciones_pendientes.descarga')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('transacciones_pendientes.descarga');
        
        $transacciones = $this->Session->read('transacciones_pendientes.transacciones_ejecutadas');

       
        $usuario = $this->Session->read('usuarios.datos');
        
        $bancos = $this->Activo->buscarBancos();
        
        $this->set(compact('transacciones','estado','usuario','bancos'));
    }
      
    /**
     * Método para la modificación de los datos del pago pendiente.
     * 
     * @param string $volver Indica si está regresando de la confirmación
     */
    function modificar($volver=null){
        if(!validarTransaccion(56)) $this->redirect('/consultas/integral');
    
        if(!$this->Session->check('transacciones_pendientes.modificar')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('transacciones_pendientes.modificar');
        
        $usuario = $this->Session->read('usuarios.datos');
        
        if(!isset($estado['TransaccionPendiente']['id'])) $this->redirect('index');
        
        // Buscar la transacción a modificar
        
        $transaccion = $this->TransaccionPendiente->find(
            'first',
            array(
                'conditions'=>array(
                    'TransaccionPendiente.tipo_rif'=>$usuario['tipo_rif'],
                    'TransaccionPendiente.rif'=>$usuario['rif'],
                    'TransaccionPendiente.id'=>$estado['TransaccionPendiente']['id'],
                    'TransaccionPendiente.usuario_id'=>$usuario['id']
                )
            )
        );
        
        // Si la transacción no existe redirigir al usuario
        
        if(empty($transaccion)) $this->redirect('index');
        
        if(in_array($transaccion['TransaccionPendiente']['tipo'],array('n','v'))){
            // Los pagos de nómina y proveedores se deben editar por su respectivo módulo
            $this->redirect('index');
        }
        
        $bancos = $this->Activo->buscarBancos();
        
        if(isset($this->data['TransaccionPendiente'])){
            // El usuario hizo post del formulario
            
            $this->TransaccionPendiente->set($this->data);
            
            // Cambiar las validaciones de la cuenta destino dependiendo del tipo de transaccion
            
            $campos_validacion = array(
                'cuenta_origen',
                'monto',
                'descripcion'
            );
            
            switch($transaccion['TransaccionPendiente']['tipo']){
                case 'tpm':
                    $this->TransaccionPendiente->validate['cuenta_destino']=array(
                        'nroCuenta'=> array(
                            'rule'=>'nroCuenta'
                        ),
                        'banco'=> array(
                            'rule'=>'banco'
                        ),
                        'comparacion'=>array(
                            'rule'=>'comparaCuentas'
                        ),
                        'requerido'=> array(
                            'rule'=>'notEmpty'
                        )
                    );
                    
                    $campos_validacion []= 'cuenta_destino';
                break;
                case 'ptm':
                    $this->TransaccionPendiente->validate['cuenta_destino']=array(
                        'tdc'=>array(
                            'rule'=>array('cc', array('visa', 'mc','amex','diners'),true)
                        ),
                        'requerido'=> array(
                            'rule'=>'notEmpty'
                        )
                    );
                    
                    $campos_validacion []= 'cuenta_destino';
                    
                break;
            }
            
            // Validar la data
            
            if($this->TransaccionPendiente->validates(
                array(
                    'fieldList'=>$campos_validacion
                )
            )){
                // Validar el monto de la operación con la configuración del usuario
                // Usuarios aprobadores o operadores/aprobadores que no hayan creado la transacción
                if( in_array($usuario['rol_juridico_id'], array(3,4,5)) ) {
                    $transaccion_detalle=false;
                    $transaccion_detalle_tdc=false;
                    
                    // Usuarios aprobadores o operadores/aprobadores que no hayan creado la transacción
                    switch($transaccion['TransaccionPendiente']['tipo']){
                        case 'tpm': //Transferencia propia mismo banco
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                100021,
                                $this->data['TransaccionPendiente']['cuenta_origen']
                            );
                            break;
                        case 'ttm': //Transferencia tercero mismo banco
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                100022,
                                $this->data['TransaccionPendiente']['cuenta_origen']
                            );
                            break;
                        case 'tpo': //Transferencia propia otro banco
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                100023,
                                $this->data['TransaccionPendiente']['cuenta_origen']
                            );
                            break;
                        case 'tto': //Transferencia tercero otro banco
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                100024,
                                $this->data['TransaccionPendiente']['cuenta_origen']
                            );
                            break;
                                                case 'dpm': //Transferencia propia mismo banco Moneda Extranjera
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                101070,
                                $this->data['TransaccionPendiente']['cuenta_origen']
                            );
                            break;
                        case 'dtm': //Transferencia tercero mismo banco Moneda Extranjera
                            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                101071,
                                $this->data['TransaccionPendiente']['cuenta_origen']
                            );
                            break;                                             
                        case 'ppm': //Pago tarjeta propia mismo banco
                            $transaccion_detalle_tdc = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                100025,
                                $this->data['TransaccionPendiente']['cuenta_destino']
                            );
                        case 'ptm': //Pago tarjeta tercero mismo banco
                            $transaccion_detalle_tdc = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                100026,
                                $this->data['TransaccionPendiente']['cuenta_origen']
                            );
                            break;
                        case 'ppo': //Pago tarjeta propia otro banco
                            $transaccion_detalle_tdc = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                100027,
                                $this->data['TransaccionPendiente']['cuenta_origen']
                            );
                            break;
                        case 'pto': //Pago tarjeta tercero otro banco
                            $transaccion_detalle_tdc = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                100028,
                                $this->data['TransaccionPendiente']['cuenta_origen']
                            );
                            break;
                    }

                    if(
                        $transaccion_detalle &&
                        is_numeric($transaccion_detalle['limite']) &&
                        formatea_monto_toWs($this->data['TransaccionPendiente']['monto']) > $transaccion_detalle['limite']
                    ) {
                        // el usuario no puede colocar el monto indicado
                        
                        $this->TransaccionPendiente->validationErrors['monto'] = _m('monto.limite.usuario','indicado');
                    } else if(
                        $transaccion_detalle_tdc &&
                        is_numeric($transaccion_detalle_tdc['limite']) &&
                        formatea_monto_toWs($this->data['TransaccionPendiente']['monto']) > $transaccion_detalle_tdc['limite']
                    ) {
                        // el usuario no puede colocar el monto indicado
                        
                        $this->TransaccionPendiente->validationErrors['monto'] = _m('monto.limite.usuario','indicado');
                    }
                }
                
            
                // En caso de que todo esté bien, redirigir a la confirmación
                
                if(empty($this->TransaccionPendiente->validationErrors['monto'])){
                    // Buscar el banco destino
                    
                    if($transaccion['TransaccionPendiente']['tipo'][2]=='o'){
                        
                        $banco_destino = '';
                    
                        switch($transaccion['TransaccionPendiente']['tipo'][0]){
                            case 't':
                              if(strlen(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])) < 19){
                            $id_banco_destino = $transaccion['TransaccionPendiente']['banco'];
                            $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                        }else{
                        $id_banco_destino = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 0, 4);
                         $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                                    }  
                         $transaccion['TransaccionPendiente']['banco'] = $bancos[$id_banco_destino]; 
                            break;
                            case 'p':
                                $tdc = str_pad(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),20,'0',STR_PAD_RIGHT);
                                $valorTarjeta = array('TNROTARJ' => $tdc);
                                $datos_tdc = $this->Activo->validarBIN($valorTarjeta);
                                
                                if(!isset($datos['NIB090501Return']['error'])){
                                    $id_banco_destino = trim(substr($datos_tdc['NIB090501Return']['NIB090501']['TBANCO'],6));
                                    $transaccion['TransaccionPendiente']['banco'] = $datos_tdc['NIB090501Return']['NIB090501']['TDESBANCO'];
                                }
                            break;              
                        }
                    }
                    
                    $transaccion['TransaccionPendiente']['cuenta_origen'] = encriptar($this->data['TransaccionPendiente']['cuenta_origen']);
                    
                    if(isset($this->data['TransaccionPendiente']['cuenta_destino'])){
                        $transaccion['TransaccionPendiente']['cuenta_destino'] = encriptar($this->data['TransaccionPendiente']['cuenta_destino']);
                    }
                    
                    $transaccion['TransaccionPendiente']['monto'] = formatea_monto_toWs($this->data['TransaccionPendiente']['monto']);
                    if(isset($this->data['TransaccionPendiente']['descripcion'])) $transaccion['TransaccionPendiente']['descripcion'] = encriptar($this->data['TransaccionPendiente']['descripcion']);
                    
                    $this->Session->write('transacciones_pendientes.modificar',$transaccion);
                    
                    $this->redirect('modificar_confirmar');
                }
            }
        } else {
            if(!empty($volver) && $this->Session->check('transacciones_pendientes.modificar')){
                $this->data = $this->Session->read('transacciones_pendientes.modificar');
            } else {
                $this->data = $transaccion;
            }
            
            $this->data['TransaccionPendiente']['monto'] = formatea_monto_fromWs($this->data['TransaccionPendiente']['monto']);
            $this->data['TransaccionPendiente']['descripcion'] = desencriptar($this->data['TransaccionPendiente']['descripcion']);
            $this->data['TransaccionPendiente']['cuenta_origen'] = desencriptar($this->data['TransaccionPendiente']['cuenta_origen']);
            $this->data['TransaccionPendiente']['cuenta_destino'] = desencriptar($this->data['TransaccionPendiente']['cuenta_destino']);
        }
        
        // Buscar cuentas origen/destino propias
        $this->params['form']['tipo_transaccion'] = $transaccion['TransaccionPendiente']['tipo'];
        $cuentas = $this->Activo->buscarCuentasSaldos();
        
        // Para los pagos de tarjeta traer el listado de tarjetas propias
        
        if($transaccion['TransaccionPendiente']['tipo'][0]=='p'){
            $tarjetas = $this->Activo->buscarTarjetas();
            $this->set(compact('tarjetas'));
        }

        // Buscar el banco destino
        
        if($transaccion['TransaccionPendiente']['tipo'][2]=='o'){
            $banco_destino = '';
        
            switch($transaccion['TransaccionPendiente']['tipo'][0]){
                case 't':
                    if(strlen(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])) < 19){
                            $id_banco_destino = $transaccion['TransaccionPendiente']['banco'];
                            $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                        }else{
                        $id_banco_destino = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 0, 4);
                         $transaccion['TransaccionPendiente']['banco_destino']=$id_banco_destino;
                                    }  
                         $transaccion['TransaccionPendiente']['banco'] = $bancos[$id_banco_destino]; 
                break;
                case 'p':
                    $tdc = str_pad(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),20,'0',STR_PAD_RIGHT);
                    $valorTarjeta = array('TNROTARJ' => $tdc);
                    $datos_tdc = $this->Activo->validarBIN($valorTarjeta);
                    
                    if(!isset($datos['NIB090501Return']['error'])){
                        $id_banco_destino = trim(substr($datos_tdc['NIB090501Return']['NIB090501']['TBANCO'],6));
                        $transaccion['TransaccionPendiente']['banco'] = $datos_tdc['NIB090501Return']['NIB090501']['TDESBANCO'];
                    }
                break;              
            }
        }
        
        
        $this->set(compact('transaccion','cuentas','usuario'));
        
        $this->layout = 'interna';

        $this->set('seccion_actual','aprobaciones.resumen');
        
    }
    
    /**
     * Método para la confirmación de los datos modificados del pago pendiente.
     * 
     */
    function modificar_confirmar(){
        if(!validarTransaccion(56)) $this->redirect('/consultas/integral');
    
        if(!$this->Session->check('transacciones_pendientes.modificar')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('transacciones_pendientes.modificar');
        
        $usuario = $this->Session->read('usuarios.datos');
        
        if(isset($this->data)){
            // El usuario confirmó la operación
            
            $estado['confirmada']=true;
            $this->Session->write('transacciones_pendientes.modificar',$estado);
            $this->redirect('modificar_resultado');
        }
        
        $transaccion = $estado;
        
        $this->set(compact('transaccion','usuario'));       
        
        $this->layout = 'interna';

        $this->set('seccion_actual','aprobaciones.resumen');        
    }
    
    
    /**
     * Método para el proceso de modificación del pago pendiente.
     * 
     */
    function modificar_resultado(){
        if(!validarTransaccion(56)) $this->redirect('/consultas/integral');
    
        if(!$this->Session->check('transacciones_pendientes.modificar')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('transacciones_pendientes.modificar');
        
        $usuario = $this->Session->read('usuarios.datos');
        
        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');
        
        
        if(!isset($estado['confirmada'])) $this->redirect('index');
    
        $transaccion = $estado;
        
        // Realizar la modificación en base de datos
        
        $this->TransaccionPendiente->id = $transaccion['TransaccionPendiente']['id'];
        
        $res = $this->TransaccionPendiente->save(
            array(
                'TransaccionPendiente'=>array(
                    'cuenta_origen'=>$transaccion['TransaccionPendiente']['cuenta_origen'],
                    'cuenta_destino'=>$transaccion['TransaccionPendiente']['cuenta_destino'],
                    'monto'=>$transaccion['TransaccionPendiente']['monto'],
                    'descripcion'=>$transaccion['TransaccionPendiente']['descripcion']
                )
            ),
            false
        );
        
        $error_bd = !$res;

        $this->Bitacora->registraBitacora(
            $usuario['id'],
            56, 
            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']), 
            formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),
            $transaccion['TransaccionPendiente']['id'],
            null,
            $error_bd ? 1 : 0
        );
        
        $this->set(compact('transaccion','error_bd','usuario'));        
        
        $this->layout = 'interna';

        $this->set('seccion_actual','aprobaciones.resumen');    
    }


    // Recarga SIMPLE TV
    function stv_guardar_AS400($nroDocumento, $ip, $transaccion, $login,$nroContrato){
        $montoRecarga = $transaccion['TransaccionPendiente']['monto'];

        $ctaDebitar = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']), 10); 
        $res = 0;

        $dataAs400 = array(
            'ctaDebitar'=>$ctaDebitar,
            'nroContrato'=>$nroContrato,
            'montoRec'=>$montoRecarga,
            'documento_identificacion'=>$nroDocumento,
            'tracerID'=>0,
            'referencia'=>0,
            'accion'=>'N',
            'ip'=>$ip,
            'usuario'=>$login,
        );
        $res = $this->Simpletv->as400Simple($dataAs400);
        return $res;
    }    

    function stv_reversar_AS400($nroDocumento, $ip, $transaccion, $login,$nroContrato,$referencia){
        $montoRecarga = $transaccion['TransaccionPendiente']['monto'];
        $montoRec = str_replace(',','.',$montoRecarga);        
        $ctaDebitar = substr(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']), 10); 
        $res = 0;

        $dataAs400 = array(
            'ctaDebitar'=>$ctaDebitar,
            'nroContrato'=>$nroContrato,
            'montoRec'=>$montoRec,
            'documento_identificacion'=>$nroDocumento,
            'tracerID'=>0,
            'referencia'=>$referencia,
            'accion'=>'Y',
            'ip'=>$ip,
            'usuario'=>$login,
        );
        $res = $this->Simpletv->as400Simple($dataAs400);
        return $res;
    }     


    function no_recarga($swValidar, $mensaje = null){
        $this->layout = 'interna';
        $proceso = 'TRANSACCION PENDIENTE / RECARGA SIMPLE TV';
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
        }         

        //$descripcion = $mensaje; 
        $this->set(compact('descripcion'));

    }

    function stv_bitacora($transaccion, $nroTransaccion, $usuarioId, $descripcion,$accion){
        $this->Bitacora->registraBitacora(
            $usuarioId,
            $nroTransaccion,
            desencriptar($transaccion['TransaccionPendiente']['cuenta_origen']),
            desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),
            $transaccion['TransaccionPendiente']['monto'],
            NULL,
            $descripcion,
            $accion
        );
    } 

    function payAmount($amount){
        $amount = str_replace(',', '.', $amount);
        return $amount;
    } 

}

?>