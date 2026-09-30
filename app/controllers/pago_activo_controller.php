<?php

/**
 * Controlador de afiliaciones, pagos y consultas Pago Activo
 */
class PagoActivoController extends AppController {

/**
 *
 * @var array Modelos que usa el controlador 
 */
    var $uses = array('Usuario', 'Afiliacion', 'Bitacora', 'PrefijosTelefonicos', 'Canal', 'PagoActivo', 'Parametro', 'Directorio', 'DetalleProducto', 'BusquedaCuenta', 'Modulo', 'Transaccion', 'ExportarCuenta', 'LbLog', 'LbSeguridad');

    /**
     *
     * @var array Componentes que utiliza este controlador
     */
    var $components = array('Activo', 'Session', 'Email', 'Sms');

    /**
     *
     * @var array Ayudantes utilizados en las vistas de este controlador
     */
    var $helpers = array('Activo', 'Html', 'Session', 'Form', 'Text');

    /**
     * Accion que se encarga de mostrar el listado de afiliaciones del usuario
     * @param string $volver Variable que indica si la pagina se carga
     * debido a un regreso
     */
    function listado_pago_activo($volver = null) {

        /* Para validar el acceso al modulo */
        if (!validarTransaccion(array(100064)))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');
        $usuario = $this->Session->read('usuarios.datos');

        if (
                (
                $validacion_tdc == null ||
                !isset($validacion_tdc['validado']) ||
                !$validacion_tdc['validado']
                )
        ) {
            $this->Session->write(
                'seguridad.validar_tdc', array(
                    'url_aceptar' => '/pago_activo/listado_pago_activo',
                    'layout' => 'interna',
                    'titulo' => 'Afiliaciones',
                    'pasos' => array(),
                    'paso_actual' => 0,
                    'seccion_actual' => 'pago_activo.afiliacion'
                )
            );

            $this->redirect('/seguridad/validar_tdc');
        }

        if ((!empty($this->data['Afiliacion']) && !isset($this->data['Afiliacion']['paginando'])) || !empty($this->data)) {

            $this->Afiliacion->set($this->data);

            if ($this->Afiliacion->validates(array('fieldList' => array('accion')))) {

                $this->Session->write('pago_activo.listado_pago_activo', $this->data);

                switch ($this->data['accion']) {
                    case 'crear':
                        $this->Session->delete('pago_activo.afiliacion_crear');
                        $this->redirect('afiliacion_crear');
                        break;

                    case 'editar':
                        $afiliacion = $this->Afiliacion->buscarAfiliacionesPagoActivo($this->data['Afiliacion']['id']);
                        $afiliacion['Afiliacion']['monto_maximo'] = empty($afiliacion['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($afiliacion['Afiliacion']['monto_maximo']);
                        $afiliacion['Afiliacion']['numero_contrato'] = empty($afiliacion['Afiliacion']['numero_contrato']) ? '' : desencriptar($afiliacion['Afiliacion']['numero_contrato']);
                        $afiliacion['Afiliacion'] = desencriptarCampos($afiliacion['Afiliacion'], array('numero_cuenta', 'numero_cuenta_ahorro', 'numero_cuenta_receptora', 'telefono', 'email'));
                        $afiliacion['Afiliacion']['tipo_operacion'] = trim($afiliacion['Afiliacion']['tipo_operacion']);
                        $afiliacion['Afiliacion']['tipo_servicio'] = trim($afiliacion['Afiliacion']['tipo_servicio']);
                        $afiliacion['Afiliacion']['codigo_telefono'] = trim($afiliacion['Afiliacion']['codigo_telefono']);
                        $afiliacion['Afiliacion']['numero_contrato'] = trim($afiliacion['Afiliacion']['numero_contrato']);
                        $afiliacion['Afiliacion']['accion'] = 'Modificación';
                        $afiliacion['Afiliacion']['cuenta_actual'] = $afiliacion['Afiliacion']['numero_cuenta_receptora'];
                        $afiliacion['Afiliacion']['telefono_actual'] = trim($afiliacion['Afiliacion']['codigo_telefono']).filter_var($afiliacion['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT);
                        $this->Session->write('pago_activo.afiliacion_crear', $afiliacion);
                        $this->redirect('afiliacion_crear/e');
                        break;

                    case 'eliminar':
                        $afiliacion = $this->Afiliacion->buscarAfiliacionesPagoActivo($this->data['Afiliacion']['id']);
                        $afiliacion['Afiliacion']['monto_maximo'] = empty($afiliacion['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($afiliacion['Afiliacion']['monto_maximo']);
                        $afiliacion['Afiliacion']['numero_contrato'] = empty($afiliacion['Afiliacion']['numero_contrato']) ? '' : desencriptar($afiliacion['Afiliacion']['numero_contrato']);
                        $afiliacion['Afiliacion'] = desencriptarCampos($afiliacion['Afiliacion'], array('numero_cuenta', 'numero_cuenta_ahorro', 'numero_cuenta_receptora', 'telefono', 'email'));
                        $afiliacion['Afiliacion']['tipo_operacion'] = trim($afiliacion['Afiliacion']['tipo_operacion']);
                        $afiliacion['Afiliacion']['tipo_servicio'] = trim($afiliacion['Afiliacion']['tipo_servicio']);
                        $afiliacion['Afiliacion']['codigo_telefono'] = trim($afiliacion['Afiliacion']['codigo_telefono']);
                        $afiliacion['Afiliacion']['accion'] = 'Eliminacion';

                        $this->Session->write('pago_activo.afiliacion_eliminar', $afiliacion);
                        $this->redirect('afiliacion_eliminar_confirmacion');
                        break;
                }
            }
        } else if (isset($volver) && $this->Session->check('pago_activo.listado_pago_activo')) {
            $this->data = $this->Session->read('pago_activo.listado_pago_activo');
        }

        if ($usuario['tipo_rif']) { // PARA JURIDICO
            if($usuario['rol_juridico_id'] == '2'){
                $conditions = array(
                    'tipo_rif' => $usuario['tipo_rif'],
                    'rif' => $usuario['rif'],
                    'usuario_id' => $usuario['id']
                );
            }else{
                $conditions = array(
                    'tipo_rif' => $usuario['tipo_rif'],
                    'rif' => $usuario['rif']
                );
            }
            
            $listCanales = array("5","7");


          
        } else { // PARA NATURAL
            $conditions = array(
                'Afiliacion.usuario_id' => $usuario['id']
            );
            $listCanales = array("5");


        }

        $conditions = array_merge($conditions, array('Afiliacion.tipo_afiliacion' => 'm'));

        $total_registros = $this->Afiliacion->find(
            'count', array('conditions' => $conditions)
        );

        $offset = 0;

        if (
            isset($this->data['Afiliacion']['offset']) &&
            preg_match('/^\d+$/', $this->data['Afiliacion']['offset'])
        ) {
            $offset = intval($this->data['Afiliacion']['offset']);
        }

        $listado = $this->Afiliacion->find(
            'all', array(
                'conditions' => $conditions,
                'order' => 'Afiliacion.id desc',
                'offset' => $offset,
                'limit' => Configure::read('Tamano.paginacion')
            )
        );
        $this->Session->write('pago_activo.listado_afiliaciones', $listado);
        $canal = $this->Canal->canal_pago_activo($listCanales);
        if($usuario['perfil_ib_id'] == '2'){
            $canal['5'] = 'P2C';
        }else{
            $canal['5'] = 'P2P';
        }
        
        if(count($canal)==$total_registros && !$usuario['tipo_rif']){
            $superado = 1;
        }else{
            $superado = 0;
        }
                
        $this->layout = 'interna';
        $this->set('seccion_actual', 'pago_activo.afiliacion');
        $this->set(compact('listado', 'canal', 'total_registros', 'superado'));
    }



    /**
     * Accion para crear o modificar una afiliacion
     * @param string Indica si se esta regresando de la confirmacion
     */
    function afiliacion_crear($volver = null) {

        /* Para validar el acceso al modulo */
        if (!validarTransaccion(array(100064)))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

        if (
                (
                $validacion_tdc == null ||
                !isset($validacion_tdc['validado']) ||
                !$validacion_tdc['validado']
                )
        ) {
            $this->Session->write(
                'seguridad.validar_tdc', array(
                    'url_aceptar' => '/pago_activo/listado_pago_activo',
                    'layout' => 'interna',
                    'titulo' => 'Afiliaciones',
                    'pasos' => array(),
                    'paso_actual' => 0,
                    'seccion_actual' => 'pago_activo.afiliacion'
                )
            );

            $this->redirect('/seguridad/validar_tdc');
        }

        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $usuario = $this->Session->read('usuarios.datos');
        $subtipos = $this->Afiliacion->buscarSubTipos('m');
        $cuentas = $this->Activo->buscarCuentas();
        $cuentasCorrientes = $this->Activo->buscarCuentasCorriente();
        $cuentasAhorro = $this->Activo->buscarCuentasAhorro();        
        $afiliaciones = $this->Session->read('pago_activo.listado_afiliaciones');
        $tipoCliente = 'V0';

        if ($usuario['perfil_ib_id'] == '2') {
            
            $listCanales = array("5","7");
            $canales = $this->Canal->canal_pago_activo($listCanales);
           $canales['5']='P2C';
            $tipoCliente = 'J0';
            $prefijos_telefonicos = $this->PrefijosTelefonicos->listar_prefijos_pago_movil();

            if ($usuario['rol_juridico_id'] > '2') {
                $buscarUsuario = $this->Usuario->find(
                        'first', array(
                    'fields' => array('email_principal', 'celular'),
                    'conditions' => array('tipo_rif' => $usuario['tipo_rif'], 'rif' => $usuario['rif'], 'rol_juridico_id' => 1)
                        )
                );

                $usuario['email_principal'] = $buscarUsuario['Usuario']['email_principal'];
                $usuario['celular'] = $buscarUsuario['Usuario']['celular'];
            }
        } else {            
            $listCanales = array("5");
            $canales['5']='P2P';
            $prefijos_telefonicos = $this->PrefijosTelefonicos->listar_prefijos_movil();
        }

        $parametros_limites = array(
            'ip' => $ip,
            'usuario' => desencriptar($usuario['login']),
            'cedula' => 'V0'
        );

        $res_limites = $this->Activo->invocaPagoActivo(
            'consulta_limite_p2p', $parametros_limites
        );

        if (!isset($res_limites['cadena']) || $res_limites['cadena']['code'] != "200") {
            $limite_pago_activo = '0';
        } else {
            if ($tipoCliente == 'V0') {
                $limite_pago_activo = ltrim($res_limites['cadena']['AREA_LIMITE_OPERACION_P2P'], '0');
            } else {
                $limite_pago_activo = ltrim($res_limites['cadena']['AREA_LIMITE_OPERACION_P2C'], '0');
            }

            $limite_pago_activo = substr($limite_pago_activo, 0, -2) . ',' . substr($limite_pago_activo, -2);
        }
                                       
        $data = $this->Session->read('pago_activo.afiliacion_crear');


        $prefijos = array();
        foreach ($prefijos_telefonicos as $prefijo) {
            $prefijos[$prefijo['PrefijosTelefonicos']['codigo_telefono']] = $prefijo['PrefijosTelefonicos']['codigo_telefono'];
        }

        if (empty($this->data['Afiliacion'])) {
            if (isset($volver) && $this->Session->check('pago_activo.afiliacion_crear')) {
                $this->data = $this->Session->read('pago_activo.afiliacion_crear');
            }
        } else {
            $this->data['Afiliacion']['tipo_afiliacion']= 'm';
            $this->data['Afiliacion']['tipo_operacion'] = 'a';
            $this->data['Afiliacion']['numero_contrato'] = $data['Afiliacion']['numero_contrato'];
            $this->data['Afiliacion']['telefono_actual'] = $data['Afiliacion']['telefono_actual'];
            $this->data['Afiliacion']['cuenta_actual'] = $data['Afiliacion']['cuenta_actual'];

             
            if(isset($this->data['Afiliacion']['codigo_telefono'])){

             
                if($this->Afiliacion->checkAfiliacionServicioP2P($usuario['id'],$this->data['Afiliacion']['telefono'],$this->data['Afiliacion']['codigo_telefono'],$this->data['Afiliacion']['tipo_servicio']) && ($this->data['Afiliacion']['cuenta_actual'] == $this->data['Afiliacion']['numero_cuenta_receptora']) ){

                       $this->Afiliacion->validationErrors['telefono'] = 'producto_nodisponible';

                }else{

                    $this->Afiliacion->set($this->data);

                    if ($this->Afiliacion->validates(
                            array(
                                'fieldList' => array(
                                    'tipo_afiliacion',
                                    'tipo_operacion',
                                    'tipo_servicio',
                                    'email',
                                    'monto_maximo',

                                )
                            )
                    )) {
                        $this->data['Afiliacion']['telefono'] = filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT);
                        $this->Session->write('pago_activo.afiliacion_crear', $this->data);
                        $this->redirect('afiliacion_crear_confirmacion');
                    }

                }
            }
               
        }

        $this->layout = 'interna';
        $this->set('usuario', $usuario);
        $this->set('afiliaciones', $afiliaciones);
        $this->set('subtipos', $subtipos);
        $this->set('seccion_actual', 'pago_activo.afiliacion');
        $this->set('canales', $canales);
        $this->set('prefijos_telefonicos', $prefijos);
        $this->set('cuentas', $cuentas);
        $this->set('cuentasCorrientes', $cuentasCorrientes);
        $this->set('cuentasAhorro', $cuentasAhorro);
        $this->set('limite_pago_activo', $limite_pago_activo);
    }

    /**
     * Accion de confirmacion de creacion/modificacion de la afiliacion 
     */
    function afiliacion_crear_confirmacion() {

        $usuario = $this->Session->read('usuarios.datos');

        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

        if (
                (
                $validacion_tdc == null ||
                !isset($validacion_tdc['validado']) ||
                !$validacion_tdc['validado']
                )
        ) {
            $this->Session->write(
                'seguridad.validar_tdc', array(
                    'url_aceptar' => '/pago_activo/listado_pago_activo',
                    'layout' => 'interna',
                    'titulo' => 'Afiliaciones',
                    'pasos' => array(),
                    'paso_actual' => 0,
                    'seccion_actual' => 'pago_activo.afiliacion'
                )
            );

            $this->redirect('/seguridad/validar_tdc');
        }
        
        if ($this->Session->check('pago_activo.afiliacion_crear')) {
            
            if ($usuario['tipo_rif']) { // PARA JURIDICO            
                $listCanales = array("5","7");
            } else { // PARA NATURAL            
                $listCanales = array("5");
            }
            $canal=$this->Canal->canal_pago_activo($listCanales);
            if($usuario['perfil_ib_id'] == '2'){

                    $canal['5'] = 'P2C';
                    }else{
              $canal['5'] = 'P2P';

                    }
            $this->layout = 'interna';                      
            $usuario = $this->Session->read('usuarios.datos');
            $this->set('tipos', $this->Afiliacion->buscarTipos());
            $this->set('subtipos', $this->Afiliacion->buscarSubTipos());
            $this->set('canal', $canal);
            $this->set('juridico', $usuario['tipo_rif']);
            $this->set('seccion_actual', 'pago_activo.afiliacion');                       
            
            $this->data = $this->Session->read('pago_activo.afiliacion_crear');
        } else {
            $this->redirect('listado_pago_activo');
        }
    }

    /**
     * Accion para el resultado de la creacion/modificacion de la afiliacion 
     * Modificar Funcion Para agregar C2P
     */
    function afiliacion_crear_resultado() {


        $parametros='';
        $usuario = $this->Session->read('usuarios.datos');
        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $res = array();
        $descripcion = '';
        $conditions = array();
        $codigoafiliado='';
        $telefonoC2P_P2C= false;








        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');


        if (
                (
                $validacion_tdc == null ||
                !isset($validacion_tdc['validado']) ||
                !$validacion_tdc['validado']
                )
        ) {
            $this->Session->write(
                'seguridad.validar_tdc', array(
                    'url_aceptar' => '/pago_activo/listado_pago_activo',
                    'layout' => 'interna',
                    'titulo' => 'Afiliaciones',
                    'pasos' => array(),
                    'paso_actual' => 0,
                    'seccion_actual' => 'pago_activo.afiliacion'
                )
            );

            $this->redirect('/seguridad/validar_tdc');
        }

        if ($this->Session->check('pago_activo.afiliacion_crear')) {

            $this->layout = 'interna';

            $this->set('seccion_actual', 'pago_activo.afiliacion');
            $this->data = $this->Session->read('pago_activo.afiliacion_crear');             
            $this->data['Afiliacion']['nombre_beneficiario'] = $usuario['nombre'];
            $usuario = $this->Session->read('usuarios.datos');
            $datosbasicos=$this->Session->read('usuarios.nibpf3010');

            $estados = array('01' =>'DC' ,
                             '02' =>'AM' ,
                             '03' =>'AN',
                             '04' =>'AP',
                             '05' =>'AR',
                             '06' =>'BA',
                             '07' =>'BO',
                             '08' =>'CB',
                             '09' =>'CO',
                             '10' =>'DA',
                             '11' =>'FA',
                             '12' =>'GU',
                             '13' =>'LA',
                             '14' =>'ME',
                             '15' =>'MI',
                             '16' =>'MO',
                             '17' =>'NE',
                             '18' =>'PO',
                             '19' =>'SU',
                             '20' =>'TA',
                             '21' =>'TR',
                             '22' =>'YA',
                             '23' =>'ZU',
                             '24' =>'VA',
                             
            );
                      
            
            $ciudad=substr($datosbasicos['NIBPF3010Return']['NIBPF3010']['DIRECCION04'],5);
            $estado=$datosbasicos['NIBPF3010Return']['NIBPF3010']['ESTADO'];
            $direccion=$datosbasicos['NIBPF3010Return']['NIBPF3010']['DIRECCION02'].$datosbasicos['NIBPF3010Return']['NIBPF3010']['DIRECCION03'].$ciudad;

              
            $this->Session->delete('pago_activo.afiliacion_crear');
            $tipoCliente = 'V0';
            $identificacion = desencriptar($usuario['cedula']);
            $tipoIdentificacion = $usuario['tipo_cedula'];
            $this->data['Afiliacion']['tipo_cedula'] = $usuario['tipo_cedula'];
            $this->data['Afiliacion']['cedula'] = $usuario['cedula'];
            
            if (empty($this->data['Afiliacion']['numero_cuenta'])) {
                $cuentaCorriente = '0';
            } else {
                $cuentaCorriente = $this->data['Afiliacion']['numero_cuenta'];                
            }

            if (empty($this->data['Afiliacion']['numero_cuenta_ahorro'])) {
                $cuentaAhorro = '0';
            } else {
                $cuentaAhorro = $this->data['Afiliacion']['numero_cuenta_ahorro'];
            }

            if (isset($this->data['Afiliacion']['numero_cuenta_receptora']) && !empty($this->data['Afiliacion']['numero_cuenta_receptora'])) {
                $cuentaReceptora = $this->data['Afiliacion']['numero_cuenta_receptora'];
                $statusReceptor = 'S';
            } else {
                $statusReceptor = 'N';
                $cuentaReceptora = 0;
            }
                       
            if ($usuario['perfil_ib_id'] == '1') {
                if($cuentaCorriente!='0'){
                    $cuentaPrincipal = "CTE";
                }else{
                    $cuentaPrincipal = "AHO";
                }
                $listCanales = array("5");
            }else{
                $listCanales = array("5","7");
                $cuentaPrincipal = "CTE";
            }
                        
            $this->set('juridico', $usuario['tipo_rif']);

            $transaccion = 100064;

            $this->Afiliacion->set($this->data);

            $flag_nuevo = !isset($this->Afiliacion->id) || $this->Afiliacion->id == '';
            
            $parametros_limites = array(
                'ip' => $ip,
                'usuario' => desencriptar($usuario['login']),
                'cedula' => 'V0'
            );

            $res_limites = $this->Activo->invocaPagoActivo(
                'consulta_limite_p2p', $parametros_limites
            );

           

            $this->data['Error'] = false;

            if ($res_limites['cadena']['code'] != "200") {
                $this->data['Error'] = true;
                $descripcion = $res_limites['cadena']['descripcion'];
            }                                          
                        
            // Si es juridico
            if ($usuario['perfil_ib_id'] == '2') {
                
                $this->data['Afiliacion']['tipo_rif'] = $usuario['tipo_rif'];
                $this->data['Afiliacion']['rif'] = $usuario['rif'];
                $this->data['Afiliacion']['numero_cuenta'] = '0';
                $identificacion = sprintf('%09d',desencriptar($usuario['rif']));
                $tipoIdentificacion = $usuario['tipo_rif'];
                $tipoCliente = 'J0';
                $statusp1 = 'N';

                if ($usuario['rol_juridico_id'] > '2') {
                    $buscarUsuario = $this->Usuario->find(
                        'first', array(
                        'fields' => array('id', 'login', 'email_principal', 'celular'),
                        'conditions' => array('tipo_rif' => $usuario['tipo_rif'], 'rif' => $usuario['rif'], 'rol_juridico_id' => 1)
                            )
                    );

                    $usuarioId = $buscarUsuario['Usuario']['id'];
                    $usuarioLogin = $buscarUsuario['Usuario']['login'];
                    $correo = $buscarUsuario['Usuario']['email_principal'];
                    $telefono = $buscarUsuario['Usuario']['celular'];
                    $conditions = array(
                        'tipo_rif' => $usuario['tipo_rif'],
                        'rif' => $usuario['rif']
                    );                    
                } else {
                    $conditions = array(
                        'tipo_rif' => $usuario['tipo_rif'],
                        'rif' => $usuario['rif'],
                        'usuario_id' => $usuario['id']
                    );
                    $usuarioId = $usuario['id'];
                    $usuarioLogin = $usuario['login'];
                    $correo = $usuario['email_principal'];
                    $telefono = $usuario['celular'];
                }
                                
                if ($this->data['Afiliacion']['tipo_servicio'] == '7'){           
                    
                    if (!empty($flag_nuevo)) {
                        $afiliacionC2P = $this->Activo->invoca(
                            'IN208001', array(
                                'req' => array(
                                    'IN2080ACC' => '00'.substr($cuentaReceptora, 10),
                                    'IN2080IDN' => str_pad($tipoIdentificacion.$identificacion, 15, ' ', STR_PAD_RIGHT),
                                    'IN2080OPC' => 'A',
                                    'IN2080USE' => desencriptar($usuarioLogin),
                                    'IN2080COD' => '000000000',
                                    'IN2080ERR' => '0000',
                                    'IN2080FAF' => '00000000',
                                )
                            )
                        );
                    }else{
                        $afiliacionC2P = $this->Activo->invoca(
                            'IN208001', array(
                                'req' => array(
                                    'IN2080ACC' => '00'.substr($cuentaReceptora, 10),
                                    'IN2080IDN' => str_pad($tipoIdentificacion.$identificacion, 15, ' ', STR_PAD_RIGHT),
                                    'IN2080OPC' => 'M',
                                    'IN2080USE' => desencriptar($usuarioLogin),
                                    'IN2080COD' => $this->data['Afiliacion']['numero_contrato'],
                                    'IN2080ERR' => '0000',
                                    'IN2080FAF' => '00000000',
                                )
                            )
                        );
                    }



                                                                                                             
                    if (isset($afiliacionC2P['IN208001Return']['IN208001'])) {    

                         $codigoafiliado=$afiliacionC2P['IN208001Return']['IN208001']['IN2080COD']; 


                        if($afiliacionC2P['IN208001Return']['IN208001']['IN2080ERR']=='8002' || $afiliacionC2P['IN208001Return']['IN208001']['IN2080ERR']=='648'){                            
                            $this->data['Afiliacion']['numero_contrato'] = $afiliacionC2P['IN208001Return']['IN208001']['IN2080COD'];                                                        
                        }else{                            
                            $this->data['Error'] = true;
                            $error = $afiliacionC2P['IN208001Return']['IN208001']['IN2080ERR'];
                            $descripcion = $afiliacionC2P['IN208001Return']['IN208001']['IN2080MSE'];                            
                        }                       
                    }else{
                        $this->data['Error'] = true;
                        $error = 1;
                        $descripcion = 'Estimado Cliente, no se pudo realizar la operación por favor intente de nuevamente.'; 
                    }
                }
                
            } else {

                $statusp1 = 'S';
                $usuarioId = $usuario['id'];
                $usuarioLogin = $usuario['login'];
                $correo = $usuario['email_principal'];
                $telefono = $usuario['celular'];
                $conditions = array(
                    'Afiliacion.usuario_id' => $usuario['id']
                );
            }                        
            
            $conditions = array_merge($conditions, array('Afiliacion.tipo_afiliacion' => 'm'));  
            
            if (!$this->data['Error']) {                
                 
                if ($flag_nuevo) {
                    
                    if ($this->data['Afiliacion']['tipo_servicio'] == '7'){

                        if($this->Afiliacion->checkAfiliacionServicioP2P($usuario['id'], filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT),$this->data['Afiliacion']['codigo_telefono'],5)){
                       
                            if($this->Afiliacion->checkAfiliacionServicioC2P($usuario['id'])==false){

                                $metodoWS = 'modificacion_basica_p2p';      
                            }else{
                        
                                $metodoWS = 'afiliacion_p2p';    
                            }
                        }else{

                            $metodoWS = 'afiliacion_p2p'; 
                        }
                    }else{

                        $idp2c = $this->Afiliacion->find(
                            'first', array('conditions' => 
                                array('Afiliacion.usuario_id' => $usuario['id'], 'Afiliacion.tipo_afiliacion' => 'm', 'Afiliacion.tipo_servicio' => '5')));

                        if ($idp2c['Afiliacion']['id']) {
                            $this->data['Error'] = true;
                            $descripcion = _m('pa.afiliacion.existe');
                        } 

                        $idc2p = $this->Afiliacion->find(
                            'first', array('conditions' => 
                                array('Afiliacion.usuario_id' => $usuario['id'], 
                                    'Afiliacion.tipo_afiliacion' => 'm', 
                                    'Afiliacion.codigo_telefono'=>$this->data['Afiliacion']['codigo_telefono'],
                                    'Afiliacion.telefono'=>encriptar(filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT)),
                                    'Afiliacion.tipo_servicio' => '7')));

                        if (!$idc2p['Afiliacion']['id']) {
                            $telefonoC2P_P2C = true;  
                        }
                        
                        $metodoWS = 'afiliacion_p2p';
                    }

                    $buscar = $this->UsuarioCanal->set(
                        $this->UsuarioCanal->find(
                                'first', array(
                            'conditions' => array('usuario_id' => $usuarioId, 'canal_id' => Configure::read('canal.pa'))
                                )
                        )
                    );

                    if (!$buscar) {
                        
                        $usuario_canal = array(
                            'UsuarioCanal' => array(
                                'usuario_id' => $usuarioId,
                                'canal_id' => Configure::read('canal.pa'),
                                'estatus_registro' => 'A',
                                'estatus_acceso' => 'A',
                                'limite_internas_terceros' => NULL,
                                'limite_externas' => NULL,
                                'inicio_sesion' => 0,
                                'fecha_creacion' => date('Y-m-d H:i:s'),
                            )
                        );
                    } else {
                        $this->UsuarioCanal->set('estatus_registro', 'A');
                        $this->UsuarioCanal->set('estatus_acceso', 'A');
                        $this->UsuarioCanal->set('inicio_sesion', 0);
       
                        $this->UsuarioCanal->set('fecha_creacion', date('Y-m-d H:i:s'));
                    }
                } else {
          
                    $buscar = $this->UsuarioCanal->set(
                        $this->UsuarioCanal->find(
                            'first', array(
                            'conditions' => array('usuario_id' => $usuarioId, 'canal_id' => Configure::read('canal.pa'))
                            )
                        )
                    );

                    if ($this->data['Afiliacion']['telefono_actual'] != $this->data['Afiliacion']['codigo_telefono']. filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT)){

                        $total_registros_telefono = $this->Afiliacion->find(
                            'count', array('conditions' => 
                                array('Afiliacion.usuario_id' => $usuario['id'], 
                                    'Afiliacion.tipo_afiliacion' => 'm', 
                                    'Afiliacion.codigo_telefono'=>substr($this->data['Afiliacion']['telefono_actual'], 0, 4),
                                    'Afiliacion.telefono'=>encriptar(substr($this->data['Afiliacion']['telefono_actual'], 4, 11))
                                    )));

                        if ($total_registros_telefono <= 1) {

                            $parametros = array(
                                'ip' => $ip,
                                'usuario' => desencriptar($usuario['login']),
                                'cedula' => $tipoIdentificacion . $identificacion,
                                'telefono' => '58' . substr($this->data['Afiliacion']['telefono_actual'], 1)    
                            );

                            $res = $this->Activo->invocaPagoActivo(
                                'desafiliar_p2p', $parametros
                            );        
                        }
                        if ($this->data['Afiliacion']['tipo_servicio'] == '5') {
                            $idc2p = $this->Afiliacion->find(
                            'first', array('conditions' => 
                                array('Afiliacion.usuario_id' => $usuario['id'], 
                                    'Afiliacion.tipo_afiliacion' => 'm', 
                                    'Afiliacion.codigo_telefono'=>$this->data['Afiliacion']['codigo_telefono'],
                                    'Afiliacion.telefono'=>encriptar(filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT)),
                                    'Afiliacion.tipo_servicio' => '7')));

                            if ($idc2p['Afiliacion']['id']) {
                                $metodoWS = 'modificacion_basica_p2p'; 
                                $codigoafiliado =desencriptar($idc2p['Afiliacion']['numero_contrato']);
                            } else {
                                $telefonoC2P_P2C = true;
                                $metodoWS = 'afiliacion_p2p';
                            }

                        } else {
                            $telefonoC2P_P2C = true;
                            $metodoWS = 'afiliacion_p2p';
                        }
                         
                    } else {

                        if ($this->data['Afiliacion']['cuenta_actual'] != $cuentaReceptora){

                            $idc2p = $this->Afiliacion->find(
                            'first', array('conditions' => 
                                array('Afiliacion.usuario_id' => $usuario['id'], 
                                    'Afiliacion.tipo_afiliacion' => 'm', 
                                    'Afiliacion.codigo_telefono'=>$this->data['Afiliacion']['codigo_telefono'],
                                    'Afiliacion.telefono'=>encriptar(filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT)),
                                    'Afiliacion.tipo_servicio' => '7')));

                            $parametros = array(
                                'ip' => $ip,
                                'usuario' => desencriptar($usuario['login']),
                                'cedula' => $tipoIdentificacion . $identificacion,
                                'telefono' => '58' . substr($this->data['Afiliacion']['codigo_telefono'], 1) . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT)    
                            );

                            $res = $this->Activo->invocaPagoActivo(
                                'desafiliar_p2p', $parametros
                            );  

                            if ($idc2p['Afiliacion']['id']) {
                                $codigoafiliado =desencriptar($idc2p['Afiliacion']['numero_contrato']);
                            } 
                            $metodoWS = 'afiliacion_p2p';
                            $telefonoC2P_P2C = true;       

                         } else {
                            $metodoWS = 'modificacion_basica_p2p';
                         }   
                    }           
                }                                                                                                                                              

                $total_registros = $this->Afiliacion->find(
                    'count', array('conditions' => $conditions)
                );   
                                          

                    if($codigoafiliado==NULL){
                            $codigoafiliado=NULL;
                    }
                   
                  

                
                if($total_registros==0 || $metodoWS == 'modificacion_basica_p2p' || $codigoafiliado!=NULL || $telefonoC2P_P2C){
                     
                    
                    $parametros = array(
                        'ip' => $ip,
                        'usuario' => desencriptar($usuarioLogin),
                        'cedula' => $tipoIdentificacion . $identificacion,
                        'telefono1' => '58' . substr($this->data['Afiliacion']['codigo_telefono'], 1) . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT),                        
                        'cuentaPrincipal' => $cuentaPrincipal,
                        'cuentaCorriente' => $cuentaCorriente,
                        'cuentaAhorro' => $cuentaAhorro,                        
                        'cuentaReceptora' => $cuentaReceptora,                        
                        'statusPagador' => $statusp1,                                                
                        'limiteOperacionP2P' => $res_limites['cadena']['AREA_LIMITE_OPERACION_P2P'],
                        'montoPagDiaP2P' => $res_limites['cadena']['AREA_MONTO_PAG_DIA_P2P'],   
                        'limiteOperacionP2C' => $res_limites['cadena']['AREA_LIMITE_OPERACION_P2C'],
                        'montoPagDiaP2C' => $res_limites['cadena']['AREA_MONTO_PAG_DIA_P2C'],   
                        'limiteOperacionSMS' => $res_limites['cadena']['AREA_LIMITE_OPERACION_SMS'],
                        'montoPagDiaSMS' => $res_limites['cadena']['AREA_MONTO_PAG_DIA_SMS'],
                        'limiteOperacionC2P' => $res_limites['cadena']['AREA_LIMITE_OPERACION_C2P'],
                        'montoPagDiaC2P' => $res_limites['cadena']['AREA_MONTO_PAG_DIA_C2P'],                       
                        'categoriaComercio' => 7012,
                        'codigoAfiliado' => $codigoafiliado,                                             
                        'emailJuridico' => desencriptar($correo),
                        'ciudad'=> $ciudad,
                        'direccion'=> $direccion,
                        'estado'=>$estados[$estado], 
                        'pais'=>'VE',
                        
                    );
                    
                    $res = $this->Activo->invocaPagoActivo(
                        $metodoWS,
                        $parametros
                    );
                    
                    $this->data['Error'] = false;

                    if($metodoWS == 'afiliacion_p2p'){



                        if($res['cadena']['code'] != "0000"){

                            $this->data['Error'] = true;

                            $error = $res['cadena']['code'];

                            switch($error){
                                case 'E011':



                             $descripcion = _m('pa.afiliacion.existe');
                                   break;
                                case 'E026':
                                case 'E027':
                                    $descripcion = _m('pa.telefono.yaregistrado');
                                    break;
                                default:
                                    $descripcion = _m('pa.tipocuenta.errado');
                                    break;
                            }
                        }

                    }else{

                        if($res['cadena']['code'] != "200"){

                            $this->data['Error'] = true;

                            $error = $res['cadena']['code'];

                            switch($error){
                                case 'E011':


                                    $descripcion = _m('pa.afiliacion.existe');
                                    break;
                                case 'E026':
                                case 'E027':
                                    $descripcion = _m('pa.telefono.yaregistrado');
                                    break;
                                default:
                                    $descripcion = _m('pa.tipocuenta.errado');
                                    break;
                            }
                        }
                    }                     
                }else{
                       
                       if($this->Afiliacion->checkAfiliacionServicioC2P($usuario['id'])==false){
                            $this->data['Error'] = true;
                            $descripcion = _m('pa.afiliacion.existe');     
                       
                        }else{
                        
                            
                            if ($flag_nuevo) {
                                $this->data['Afiliacion']['accion'] = 'Afiliacion';
                                $codigo_sms = '68';
                            } else {
                                $this->data['Afiliacion']['accion'] = 'Modificacion';
                                $codigo_sms = '69';
                            }

                            // Email Options
                            $this->Email->smtpOptions = Configure::read('email.smtp');
                            $this->Email->delivery = 'smtp';
                            $this->Email->from = Configure::read('email.from');
                            $this->Email->to = desencriptar($correo);
                            $this->Email->subject = 'Notificacion Afiliacion Pago Activo';
                            $this->Email->sendAs = 'html';

                            // Informacion del correo
                            $this->Email->template = 'afiliacion_pago_activo';
                            $tipo_afiliacion = 'Pago Activo';
                            $tipo_accion = $this->data['Afiliacion']['accion'] . ' Pago Activo';
                            $numero_celular = enmascaraCel($this->data['Afiliacion']['codigo_telefono'] . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT));
                            $email = $this->data['Afiliacion']['email'];
                            $cuentaPCorriente = (!empty($this->data['Afiliacion']['numero_cuenta'])) ? enmascara($this->data['Afiliacion']['numero_cuenta']) . '  ' : '';
                            $cuentaPAhorro = (!empty($this->data['Afiliacion']['numero_cuenta_ahorro'])) ? enmascara($this->data['Afiliacion']['numero_cuenta_ahorro']) : '';
                            $cuentaPagadora = $cuentaPCorriente . $cuentaPAhorro;
                            if (isset($this->data['Afiliacion']['numero_cuenta_receptora'])) {
                                $cuentaReceptora = enmascara($this->data['Afiliacion']['numero_cuenta_receptora']);
                                $cuentaReceptoraLog = $this->data['Afiliacion']['numero_cuenta_receptora'];
                            } else {
                                $cuentaReceptora = "";
                                $cuentaReceptoraLog = "";
                            }
                            if (isset($this->data['Afiliacion']['numero_contrato'])) {
                                $numeroAfiliacion = $this->data['Afiliacion']['numero_contrato'];                        
                            } else {
                                $numeroAfiliacion = "";                        
                            }
                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'numero_celular', 'email', 'cuentaPagadora', 'cuentaPagadoraAhorro', 'cuentaReceptora', 'numeroAfiliacion'));

                            // Envio del correo
                            $this->Email->send();

                            //Enviar por sms la notificacion
                            if (isset($usuario['celular'])) {
                                $this->Sms->enviar(
                                    array(
                                        'numcel' => desencriptar($telefono),
                                        'canal1' => $codigo_sms
                                    )
                                );
                            }

                            $cuentaPCorrienteLog = (!empty($this->data['Afiliacion']['numero_cuenta'])) ? $this->data['Afiliacion']['numero_cuenta'] . '  ' : '';
                            $cuentaPAhorroLog = (!empty($this->data['Afiliacion']['numero_cuenta_ahorro'])) ? $this->data['Afiliacion']['numero_cuenta_ahorro'] : '';
                            $cuentaPagadoraLog = $cuentaPCorrienteLog . $cuentaPAhorroLog;

                            // Registra en bitacora
                            $this->Bitacora->registraBitacora(
                            $usuario['id'], $transaccion, $cuentaPagadoraLog, $cuentaReceptoraLog, $res_limites['cadena']['AREA_LIMITE_OPERACIONTP'], NULL, 'Celular Afiliado ' . $this->data['Afiliacion']['codigo_telefono'] . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT), 0
                            );
                         
                        }

                }               
                
                if (!$this->data['Error']){
                    
                    $this->Afiliacion->set($this->data);
                                       
                    $this->data['Error'] = !$this->Afiliacion->saveData($usuarioId);
                                        
                    if ($metodoWS == 'afiliacion_p2p') {
                        // Almacena el registro del usuario
                        if (!$buscar) {
                            $this->data['Error'] = !$this->UsuarioCanal->save($usuario_canal, false);
                        } else {
                            $this->data['Error'] = !$this->UsuarioCanal->save(null, false);
                        }
                    }

                    if (!$this->data['Error']) {

                        if ($flag_nuevo) {
                            $this->data['Afiliacion']['accion'] = 'Afiliacion';
                            $codigo_sms = '68';
                        } else {
                            $this->data['Afiliacion']['accion'] = 'Modificacion';
                            $codigo_sms = '69';
                        }

                        // Email Options
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery = 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = desencriptar($correo);
                        $this->Email->subject = 'Notificacion Afiliacion Pago Activo';
                        $this->Email->sendAs = 'html';

                        // Informacion del correo
                        $this->Email->template = 'afiliacion_pago_activo';
                        $tipo_afiliacion = 'Pago Activo';
                        $tipo_accion = $this->data['Afiliacion']['accion'] . ' Pago Activo';
                        $numero_celular = enmascaraCel($this->data['Afiliacion']['codigo_telefono'] . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT));
                        $email = $this->data['Afiliacion']['email'];
                        $cuentaPCorriente = (!empty($this->data['Afiliacion']['numero_cuenta'])) ? enmascara($this->data['Afiliacion']['numero_cuenta']) . '  ' : '';
                        $cuentaPAhorro = (!empty($this->data['Afiliacion']['numero_cuenta_ahorro'])) ? enmascara($this->data['Afiliacion']['numero_cuenta_ahorro']) : '';
                        $cuentaPagadora = $cuentaPCorriente . $cuentaPAhorro;
                        if (isset($this->data['Afiliacion']['numero_cuenta_receptora'])) {
                            $cuentaReceptora = enmascara($this->data['Afiliacion']['numero_cuenta_receptora']);
                            $cuentaReceptoraLog = $this->data['Afiliacion']['numero_cuenta_receptora'];
                        } else {
                            $cuentaReceptora = "";
                            $cuentaReceptoraLog = "";
                        }
                        if (isset($this->data['Afiliacion']['numero_contrato'])) {
                            $numeroAfiliacion = $this->data['Afiliacion']['numero_contrato'];                        
                        } else {
                            $numeroAfiliacion = "";                        
                        }
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'numero_celular', 'email', 'cuentaPagadora', 'cuentaPagadoraAhorro', 'cuentaReceptora', 'numeroAfiliacion'));

                        // Envio del correo
                        $this->Email->send();

                        //Enviar por sms la notificacion
                        if (isset($usuario['celular'])) {
                            $this->Sms->enviar(
                                array(
                                    'numcel' => desencriptar($telefono),
                                    'canal1' => $codigo_sms
                                )
                            );
                        }

                        $cuentaPCorrienteLog = (!empty($this->data['Afiliacion']['numero_cuenta'])) ? $this->data['Afiliacion']['numero_cuenta'] . '  ' : '';
                        $cuentaPAhorroLog = (!empty($this->data['Afiliacion']['numero_cuenta_ahorro'])) ? $this->data['Afiliacion']['numero_cuenta_ahorro'] : '';
                        $cuentaPagadoraLog = $cuentaPCorrienteLog . $cuentaPAhorroLog;

                        // Registra en bitacora
                        $this->Bitacora->registraBitacora(
                            $usuario['id'], $transaccion, $cuentaPagadoraLog, $cuentaReceptoraLog, $res_limites['cadena']['AREA_LIMITE_OPERACIONTP'], NULL, 'Celular Afiliado ' . $this->data['Afiliacion']['codigo_telefono'] . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT), 0
                        );
                    } else {
                        $this->Bitacora->registraBitacora(
                            $usuario['id'], $transaccion, $this->data['Afiliacion']['codigo_telefono'] . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT), NULL, $res_limites['cadena']['AREA_LIMITE_OPERACIONTP'], NULL, $descripcion, 1
                        );
                    }                           
                    //fin                                       
                }
            } else {
                $this->Bitacora->registraBitacora(
                $usuario['id'], $transaccion, $this->data['Afiliacion']['codigo_telefono'] . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT), NULL, 0, NULL, $descripcion, 1
                );
            }
            
        } else {
            $this->redirect('listado_pago_activo');
        }

                $canal=$this->Canal->canal_pago_activo($listCanales);
                if($usuario['perfil_ib_id'] == '2'){

                $canal['5'] = 'P2C';
                       
                        }else{
                  $canal['5'] = 'P2P';
              }
        
        $this->set('juridico', $usuario['tipo_rif']);
        $this->set('descripcion', $descripcion);
        $this->set('tipos', $this->Afiliacion->buscarTipos());
        $this->set('subtipos', $this->Afiliacion->buscarSubTipos());
        $this->set('canal', $canal);
    }
    

    /**
     * Accion para confirmar la eliminacion de una afiliacion 
     */
    function afiliacion_eliminar_confirmacion() {

        $usuario = $this->Session->read('usuarios.datos');

        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

        if (
                (
                $validacion_tdc == null ||
                !isset($validacion_tdc['validado']) ||
                !$validacion_tdc['validado']
                )
        ) {
            $this->Session->write(
                'seguridad.validar_tdc', array(
                    'url_aceptar' => '/pago_activo/listado_pago_activo',
                    'layout' => 'interna',
                    'titulo' => 'Afiliaciones',
                    'pasos' => array(),
                    'paso_actual' => 0,
                    'seccion_actual' => 'pago_activo.afiliacion'
                )
            );

            $this->redirect('/seguridad/validar_tdc');
        }
        
        if ($usuario['tipo_rif']) { // PARA JURIDICO            
            $listCanales = array("5","7");
        } else { // PARA NATURAL            
            $listCanales = array("5");
        }
        $canal=$this->Canal->canal_pago_activo($listCanales);
        if($usuario['perfil_ib_id'] == '2'){
            $canal['5'] = 'P2C';
        }else{
              $canal['5'] = 'P2P';
        }
        
        if ($this->Session->check('pago_activo.afiliacion_eliminar')) {
            $this->data = $this->Session->read('pago_activo.afiliacion_eliminar');
            $this->layout = 'interna';
            $this->set('juridico', $usuario['tipo_rif']);
            $this->set('tipos', $this->Afiliacion->buscarTipos());
            $this->set('subtipos', $this->Afiliacion->buscarSubTipos());
            $this->set('canal', $canal);
            $this->set('seccion_actual', 'pago_activo.afiliacion');
            $usuario = $this->Session->read('usuarios.datos');

            $this->set('juridico', $usuario['tipo_rif']);
        } else {
            $this->redirect('listado_pago_activo');
        }
    }

    /**
     * Accion para el resultado de eliminar una afiliacion 
     */
    function afiliacion_eliminar_resultado() {


        $usuario = $this->Session->read('usuarios.datos');
        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        
        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

        if (
                (
                $validacion_tdc == null ||
                !isset($validacion_tdc['validado']) ||
                !$validacion_tdc['validado']
                )
        ) {
            $this->Session->write(
                'seguridad.validar_tdc', array(
                    'url_aceptar' => '/pago_activo/listado_pago_activo',
                    'layout' => 'interna',
                    'titulo' => 'Afiliaciones',
                    'pasos' => array(),
                    'paso_actual' => 0,
                    'seccion_actual' => 'pago_activo.afiliacion'
                )
            );

            $this->redirect('/seguridad/validar_tdc');
        }
                
        if ($this->Session->check('pago_activo.afiliacion_eliminar')) {

            $this->data = $this->Session->read('pago_activo.afiliacion_eliminar');
            $this->layout = 'interna';
            //$this->Session->delete('pago_activo.afiliacion_eliminar');
            $transaccion = 100064;
            $identificacion = desencriptar($usuario['cedula']);
            $tipoIdentificacion = $usuario['tipo_cedula'];
            $this->data['Error'] = false;
            $flag = true;
            $conditions = array();
            
            // Si es juridico
            if ($usuario['tipo_rif']) {
                
                $this->data['Afiliacion']['tipo_rif'] = $usuario['tipo_rif'];
                $this->data['Afiliacion']['rif'] = $usuario['rif'];
                $identificacion = sprintf('%09d',desencriptar($usuario['rif']));
                $tipoIdentificacion = $usuario['tipo_rif'];
                
                if($usuario['rol_juridico_id'] == '2'){
                    $conditions = array(
                        'tipo_rif' => $usuario['tipo_rif'],
                        'rif' => $usuario['rif'],
                        'usuario_id' => $usuario['id']
                    );
                    $usuarioLogin = desencriptar($usuario['login']);
                    $usuarioId = $usuario['id'];
                }else{
                    $conditions = array(
                        'tipo_rif' => $usuario['tipo_rif'],
                        'rif' => $usuario['rif']
                    );
                    
                    $buscarUsuario = $this->Usuario->find(
                        'first', array(
                        'fields' => array('id', 'login', 'email_principal', 'celular'),
                        'conditions' => array('tipo_rif' => $usuario['tipo_rif'], 'rif' => $usuario['rif'], 'rol_juridico_id' => 1)
                            )
                    );
                    
                    $usuarioLogin = desencriptar($buscarUsuario['Usuario']['login']);
                    $usuarioId = $buscarUsuario['Usuario']['id'];
                }
                
                $listCanales = array("5","7");
            }else{
                $conditions = array(
                    'Afiliacion.usuario_id' => $usuario['id']
                );
                $listCanales = array("5");
                $usuarioId = $usuario['id'];
            }            

            $conditions = array_merge($conditions, array('Afiliacion.tipo_afiliacion' => 'm'));

            if ($this->data['Afiliacion']['tipo_servicio'] == '5'){                  
                                            
                $conditionsw = array_merge($conditions, array('Afiliacion.tipo_afiliacion' => 'm', 'Afiliacion.tipo_servicio' => '7', 'Afiliacion.codigo_telefono'=>$this->data['Afiliacion']['codigo_telefono'],'Afiliacion.telefono'=>encriptar(filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT)))); 

                $idc2p = $this->Afiliacion->find(
                    'first', array('conditions' => $conditionsw)
                );

                if ($idc2p['Afiliacion']['id']) {

                    $parametros = array(
                        'ip' => $ip,
                        'usuario' => desencriptar($usuario['login']),
                        'cedula' => $tipoIdentificacion . $identificacion,
                        'telefono' => '58' . substr($idc2p['Afiliacion']['codigo_telefono'], 1) . desencriptar($idc2p['Afiliacion']['telefono'])    
                    );

                    $res = $this->Activo->invocaPagoActivo(
                        'desafiliar_p2p', $parametros
                    );                                

                    if (isset($res['cadena']['cadena']['resultado']) && $res['cadena']['cadena']['resultado'] != "OK") {
                        $this->data['Error'] = true;
                        $descripcion = isset($res['cadena']['code']) ? $res['cadena']['code'] : '0' . "-" . _m('pa.tipocuenta.errado');
                    }

                    $afiliacionC2P = $this->Activo->invoca(
                        'IN208001', array(
                            'req' => array(
                                'IN2080ACC' => '00'.substr(desencriptar($idc2p['Afiliacion']['numero_cuenta_receptora']), 10),
                                'IN2080IDN' => str_pad($tipoIdentificacion.$identificacion, 15, ' ', STR_PAD_RIGHT),
                                'IN2080OPC' => 'D',
                                'IN2080USE' => $usuarioLogin,
                                'IN2080COD' => desencriptar($idc2p['Afiliacion']['numero_contrato']),
                                'IN2080ERR' => '0000',
                                'IN2080FAF' => '00000000',
                            )
                        )
                    );
                    
                    if (isset($afiliacionC2P['IN208001Return']['IN208001'])) {                        
                        if($afiliacionC2P['IN208001Return']['IN208001']['IN2080ERR']!='8001'){                            
                            $this->data['Error'] = true;
                            $error = $afiliacionC2P['IN208001Return']['IN208001']['IN2080ERR'];
                            $descripcion = $afiliacionC2P['IN208001Return']['IN208001']['IN2080MSE'];  
                        }                  
                    }
                }
            }else{

                $conditionsw = array_merge($conditions, array('Afiliacion.tipo_afiliacion' => 'm', 'Afiliacion.tipo_servicio' => '5','Afiliacion.codigo_telefono'=>$this->data['Afiliacion']['codigo_telefono'],'Afiliacion.telefono'=>encriptar(filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT))));    
            }

            $total_registros = $this->Afiliacion->find(
                'count', array('conditions' => $conditions)
            );

            $idp2p = $this->Afiliacion->find(
                'first', array('conditions' => $conditionsw)
            );

            $idp2p=$idp2p['Afiliacion']['id'];

               
            if ($this->data['Afiliacion']['tipo_servicio'] == '7'){                                
                
                $afiliacionC2P = $this->Activo->invoca(
                    'IN208001', array(
                        'req' => array(
                            'IN2080ACC' => '00'.substr($this->data['Afiliacion']['numero_cuenta_receptora'], 10),
                            'IN2080IDN' => str_pad($tipoIdentificacion.$identificacion, 15, ' ', STR_PAD_RIGHT),
                            'IN2080OPC' => 'D',
                            'IN2080USE' => $usuarioLogin,
                            'IN2080COD' => $this->data['Afiliacion']['numero_contrato'],
                            'IN2080ERR' => '0000',
                            'IN2080FAF' => '00000000',
                        )
                    )
                );
                
                if (isset($afiliacionC2P['IN208001Return']['IN208001'])) {                        
                    if($afiliacionC2P['IN208001Return']['IN208001']['IN2080ERR']!='8001'){                            
                        $this->data['Error'] = true;
                        $error = $afiliacionC2P['IN208001Return']['IN208001']['IN2080ERR'];
                        $descripcion = $afiliacionC2P['IN208001Return']['IN208001']['IN2080MSE'];  
                    }                  
                }
                                
            }
            
            if($total_registros>=1 && !$this->data['Error']){                                
                
                $parametros = array(
                    'ip' => $ip,
                    'usuario' => desencriptar($usuario['login']),
                    'cedula' => $tipoIdentificacion . $identificacion,
                    'telefono' => '58' . substr($this->data['Afiliacion']['codigo_telefono'], 1) . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT)    
                );

                $res = $this->Activo->invocaPagoActivo(
                    'desafiliar_p2p', $parametros
                );                                

                if (isset($res['cadena']['cadena']['resultado']) && $res['cadena']['cadena']['resultado'] != "OK") {
                    $this->data['Error'] = true;
                    $descripcion = isset($res['cadena']['code']) ? $res['cadena']['code'] : '0' . "-" . _m('pa.tipocuenta.errado');
                }
            }else{
                $flag = false;
            }
                                   
            if (!$this->data['Error']) {

                $this->data['Error'] = !$this->Afiliacion->delete($this->data['Afiliacion']['id']);
                
                if ($flag) {
                    $usuario_canal = array(
                        'UsuarioCanal' => array(
                            'usuario_id' => $usuarioId,
                            'canal_id' => Configure::read('canal.pa'),
                            'estatus_registro' => 'DD',
                            'estatus_acceso' => 'DD',
                            'inicio_sesion' => 0,
                        )
                    );

                    // Busca la info del usuario
                    $this->UsuarioCanal->set(
                        $this->UsuarioCanal->find(
                                'first', array(
                                'conditions' => array('usuario_id' => $usuarioId, 'canal_id' => Configure::read('canal.pa'))
                            )
                        )
                    );

                    $this->UsuarioCanal->set('estatus_registro','DD');
                    $this->UsuarioCanal->set('estatus_acceso','DD');
                    $this->UsuarioCanal->set('inicio_sesion',0);

                    $this->data['Error'] = !$this->UsuarioCanal->save(null, false);
                }
                

                if (!$this->data['Error']) {

                    // Email Options
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = desencriptar($usuario['email_principal']);
                    $this->Email->subject = 'Notificacion Eliminacion Afiliacion Pago Activo';
                    $this->Email->sendAs = 'html';

                    // Informacion del correo
                    $this->Email->template = 'afiliacion_pago_activo';
                    $tipo_afiliacion = 'Pago Activo';
                    $tipo_accion = $this->data['Afiliacion']['accion'] . ' Afiliacion Pago Activo';
                    $numero_celular = enmascaraCel($this->data['Afiliacion']['codigo_telefono'].filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT));
                    $email = $this->data['Afiliacion']['email'];
                    $cuentaPCorriente = (!empty($this->data['Afiliacion']['numero_cuenta'])) ? enmascara($this->data['Afiliacion']['numero_cuenta']) . '  ' : '';
                    $cuentaPAhorro = (!empty($this->data['Afiliacion']['numero_cuenta_ahorro'])) ? enmascara($this->data['Afiliacion']['numero_cuenta_ahorro']) : '';
                    $cuentaPagadora = $cuentaPCorriente . $cuentaPAhorro;
                    if (isset($this->data['Afiliacion']['numero_cuenta_receptora'])) {
                        $cuentaReceptora = enmascara($this->data['Afiliacion']['numero_cuenta_receptora']);
                        $cuentaReceptoraLog = $this->data['Afiliacion']['numero_cuenta_receptora'];
                    } else {
                        $cuentaReceptora = "";
                        $cuentaReceptoraLog = "";
                    }
                    
                    if (isset($this->data['Afiliacion']['numero_contrato'])) {
                        $numeroAfiliacion = $this->data['Afiliacion']['numero_contrato'];                        
                    } else {
                        $numeroAfiliacion = "";                        
                    }
                    
                    $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'numero_celular', 'email', 'cuentaPagadora', 'cuentaReceptora', 'numeroAfiliacion'));

                    // Envio del correo
                    $this->Email->send();

                    // Enviar por sms la notificacion
                    if (isset($usuario['celular'])) {
                        $this->Sms->enviar(
                            array(
                                'numcel' => desencriptar($usuario['celular']),
                                'canal1' => 70
                            )
                        );
                    }

                    $cuentaPCorrienteLog = (!empty($this->data['Afiliacion']['numero_cuenta'])) ? $this->data['Afiliacion']['numero_cuenta'] . '  ' : '';
                    $cuentaPAhorroLog = (!empty($this->data['Afiliacion']['numero_cuenta_ahorro'])) ? $this->data['Afiliacion']['numero_cuenta_ahorro'] : '';
                    $cuentaPagadoraLog = $cuentaPCorrienteLog . $cuentaPAhorroLog;

                  


                    $this->Afiliacion->delete($idp2p);

           
                    $this->Bitacora->registraBitacora(
                        $usuario['id'], $transaccion, $cuentaPagadoraLog, $cuentaReceptoraLog, $this->data['Afiliacion']['monto_maximo'], NULL, 'Celular Desafiliado ' . $this->data['Afiliacion']['codigo_telefono'] . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT), 0
                    );
                } else {

                    $this->Bitacora->registraBitacora(
                       $usuario['id'], $transaccion, $this->data['Afiliacion']['codigo_telefono'] . filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT), NULL, $this->data['Afiliacion']['monto_maximo'], 'Error al guardar en BD', NULL, 1
                    );
                }

                $this->set('juridico', $usuario['tipo_rif']);
                $this->set('seccion_actual', 'pago_activo.afiliacion');
            } else {

                $this->Bitacora->registraBitacora(
                    $usuario['id'], $transaccion, $this->data['Afiliacion']['codigo_telefono'].filter_var($this->data['Afiliacion']['telefono'], FILTER_SANITIZE_NUMBER_INT), NULL, $this->data['Afiliacion']['monto_maximo'], NULL, $descripcion, 1
                );
            }
        } else {
            $this->redirect('listado_pago_activo');
        }

        $canal=$this->Canal->canal_pago_activo($listCanales);
        if($usuario['perfil_ib_id'] == '2'){
            $canal['5'] = 'P2C';
        }else{
            $canal['5'] = 'P2P';
        }
        $this->set('juridico', $usuario['tipo_rif']);
        $this->set('tipos', $this->Afiliacion->buscarTipos());
        $this->set('subtipos', $this->Afiliacion->buscarSubTipos());
        $this->set('canal', $this->Canal->canal_pago_activo($listCanales));
    }

    /**
     * Método inicial para realizar una pago activo
     * 
     * @param string $volver Indica si esta regresando de la confirmación
     */
    function pagos($volver = null) {

        /* Para validar el acceso al modulo */
        if (!validarTransaccion(100065))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $usuario = $this->Session->read('usuarios.datos');

        $this->layout = 'interna';

        $this->set('seccion_actual', 'pago_activo.pago');

      

        if (!empty($this->data['PagoActivo'])) {

            $usuario = $this->Session->read('usuarios.datos');

            // Para usuario juridicos operadores, obtener su configuracion para este tipo de transaccion
            if (
                    $usuario['rol_juridico_id'] == 3 ||
                    $usuario['rol_juridico_id'] == 5
            ) {
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                        $usuario['id'], 100065, $this->data['PagoActivo']['cuentaDebitar']
                );

                if (!$transaccion_detalle) {
                    $this->PagoActivo->validationErrors['PagoActivo'] = _m('usuario.no.autorizado');
                }
            }

            if (empty($this->PagoActivo->validationErrors)) {
                if (isset($transaccion_detalle)) {
                    $this->data['PagoActivo']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if (is_numeric($transaccion_detalle['limite'])) {
                        $this->data['PagoActivo']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['PagoActivo']['monto_limite_usuario'] = '';
                    }
                }

                $this->PagoActivo->set($this->data);

                if ($this->PagoActivo->validates(
                                array(
                                    'fieldList' => array(
                                        'cuentaDebitar',
                                        'tipoDestinatario'
                                    )
                        ))
                ) {
                    $this->Session->write('PagoActivo.pagos', $this->data);

                    if ($this->data['PagoActivo']['tipoDestinatario'] == 'a') {
                        $this->redirect('pagos_afiliada');
                    } else {
                        $this->redirect('pagos_noafiliada');
                    }
                }
            }
        } elseif (isset($volver) && $this->Session->check('PagoActivo.pagos')) {
            $this->data = $this->Session->read('PagoActivo.pagos');
        }

        $cuentas = $this->Activo->buscarCuentasSaldos();
        $cuentasPagadoras = $this->Afiliacion->buscarCuentasAfiliadasPagoActivo($usuario['id']);
        $cuentasPagadorasD = array();

        foreach ($cuentasPagadoras as $cp) {
            if (trim($cp['Afiliacion']['numero_cuenta']) != '') {
                array_push($cuentasPagadorasD, desencriptar($cp['Afiliacion']['numero_cuenta']));
            }

            if (trim($cp['Afiliacion']['numero_cuenta_ahorro']) != '') {
                array_push($cuentasPagadorasD, desencriptar($cp['Afiliacion']['numero_cuenta_ahorro']));
            }
        }

        foreach ($cuentas as $c => $valor) {
            if (!in_array($c, $cuentasPagadorasD)) {
                unset($cuentas[$c]);
            }
        }

        $this->set(compact('cuentas'));
    }

    /**
     * Metodo para la busqueda de los afiliados para realizar el pago	 
     * 
     */
    function pagos_afiliada() {

        if (!$this->Session->check('PagoActivo.pagos'))
            $this->redirect('pagos');

        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $estado = $this->Session->read('PagoActivo.pagos');
        $usuario = $this->Session->read('usuarios.datos');
        $identificacion = desencriptar($usuario['cedula']);
        $tipoIdentificacion = $usuario['tipo_cedula'];
        $telefono = '58' . substr(desencriptar($usuario['celular']), 1);

        if ($estado['PagoActivo']['tipoPersona'] == '1') {
            $tipoCliente = 'J0';
            $tipoConsulta = '1';
        } else {
            $tipoCliente = 'V0';
            $tipoConsulta = '0';
        }

        // Si es juridico
        if ($usuario['tipo_rif']) {
            $this->data['PagoActivo']['tipo_rif'] = $usuario['tipo_rif'];
            $this->data['PagoActivo']['rif'] = $usuario['rif'];
            $identificacion = desencriptar($usuario['rif']);
            $tipoIdentificacion = $usuario['tipo_rif'];
        }

        $tipoCuenta = $this->Activo->buscarTipoCuenta($tipoIdentificacion . str_pad($identificacion, 9, '0', STR_PAD_LEFT), array($estado['PagoActivo']['cuentaDebitar']));

        //Tipo de cuenta
        if ($tipoCuenta['SUMTYP'] == 'SAV') {
            $tipoCuenta = '0002';
        } else {
            $tipoCuenta = '0001';
        }

        // Obtener el limite de parametros
        $parametros_limites = array(
            'ip' => $ip,
            'usuario' => desencriptar($usuario['login']),
            'cedula' => 'V0'
        );

        $res_limites = $this->Activo->invocaPagoActivo(
                'consulta_limite_p2p', $parametros_limites
        );


        
        if (!isset($res_limites['cadena']) || $res_limites['cadena']['code'] != "200") {
            $limite_pago_activo = '0';
        } else {
            if ($estado['PagoActivo']['tipoPersona'] == '1') {
                $limite_pago_activo = ltrim($res_limites['cadena']['AREA_LIMITE_OPERACION_P2C'], '0');
            } else {
                $limite_pago_activo = ltrim($res_limites['cadena']['AREA_LIMITE_OPERACION_P2P'], '0');
            }
            $limite_pago_activo = substr($limite_pago_activo, 0, -2) . ',' . substr($limite_pago_activo, -2);
        }

        $this->set('seccion_actual', 'pago_activo.pago');

        if (empty($this->data['PagoActivo'])) {
            $this->data = $estado;
        } else if (!isset($this->data['PagoActivo']['paginando'])) {

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->data['PagoActivo']['monto_limite'] = $limite_pago_activo;

            if (isset($estado['PagoActivo']['monto_limite_usuario'])) {
                $this->data['PagoActivo']['monto_limite_usuario'] = $estado['PagoActivo']['monto_limite_usuario'];
            }

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->PagoActivo->set($this->data);

            if ($this->PagoActivo->validates(
                            array(
                                'fieldList' => array(
                                    'cuentaCreditar',
                                    'monto',
                                    'descripcion'
                                )
                            )
                    )) {
                if (isset($estado['PagoActivo']['aprobaciones_requeridas']))
                    $this->data['PagoActivo']['aprobaciones_requeridas'] = $estado['PagoActivo']['aprobaciones_requeridas'];

/*                $validacion_otp = $this->Session->read('seguridad.validar_otp');
                if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5)))) {
                    $this->data['pasos'] = array(
                        'Solicitud de Información',
                        'Confirmación',
                        'Resultado de Operación'
                    );
                    $this->data['paso_final'] = 3;
                } else {

                    $this->data['pasos'] = array(
                        'Solicitud de Información',
                        'Confirmación',
                        'Resultado de Operación'
                    );
                    $this->data['paso_final'] = 2;
                }
*/                

                $this->Session->write('PagoActivo.pagos', $this->data);

                $this->Session->delete('transaccion.advertencia');

                // En caso de ser un usuario natural o master unico
                if (empty($usuario['rol_juridico_id']) || ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) ) {
                    $cadCuentaCreditar = trim($this->data['PagoActivo']['cuentaCreditar']);

                    $codBanco = substr($cadCuentaCreditar,0,4);
                    $codTelefono = substr($cadCuentaCreditar,6,3);
                    $telefono = substr($cadCuentaCreditar,9,7);
                    $cedula = substr($cadCuentaCreditar,16);

                    $this->data['PagoActivo']['codigo_telefono'] = $codTelefono;
                    $this->data['PagoActivo']['telefono'] = $telefono;

                    $nroRegistro = $this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['PagoActivo'], 100065);

                    if ($nroRegistro > 0) {                    
                        $this->data['titulo'] = 'Pago Activo';
                        $this->data['mensaje_alerta'] = 'transaccion.alerta';
                        $this->data['url_volver'] = '/pago_activo/pagos_afiliada';
                        $this->data['url_continuar'] = '/pago_activo/pagos_confirmacion';
                        $this->data['seccion_actual'] = 'pago_activo.pago';

                        $this->Session->write('transaccion.advertencia', $this->data);
                        $this->redirect('transaccion_advertencia');
                    }
                }

                $this->redirect('pagos_confirmacion');
            }
        }

        $this->data['Error'] = false;
        $bancos = array();
        $afiliados = array();
        $parametrosBanco = array('tipo_pagador' => $tipoConsulta);

        $resBancos = $this->Activo->invocaPagoActivo(
                'consulta_bancos_p2p', $parametrosBanco
        );

        if ($resBancos['cadena']['code'] == "200") {
            if (isset($resBancos['cadena']['banco'][0])) {
                foreach ($resBancos['cadena']['banco'] as &$i) {
                    $bancos[$i['codBanco']] = $i['nombreBanco'];
                }
            } else {
                $bancos[$resBancos['cadena']['banco']['codBanco']] = $resBancos['cadena']['banco']['nombreBanco'];
            }
        } else {
            $this->data['Error'] = true;
            $descripcion = $resBancos['cadena']['descripcion'];
        }

        $parametros = array(
            'ip' => $ip,
            'usuario' => desencriptar($usuario['login']),
            'metodo' => 'CO',
            'AREA_ORIGEN' => '01'
        );

        $res = $this->Activo->invocaPagoActivo(
                'directorio_p2p', $parametros
        );

        if ($res['cadena']['code'] == "200") {
            if (isset($res['cadena']['directorio'])) {
                $afiliados = $res['cadena']['directorio'];
                normaliza($afiliados);
                $a = 0;
                foreach ($afiliados as $afiliado) {
                    if ($afiliado['tipo_cliente'] != $estado['PagoActivo']['tipoPersona']) {
                        unset($afiliados[$a]);
                    }
                    $a++;
                }
            }
        } else {
            $this->data['Error'] = true;
            $descripcion = $res['cadena']['descripcion'];
        }

        if (count($afiliados) > 0) {
            // Empieza codigo para paginar
            $total_registros = count($afiliados);
            $offset = 0;

            if (
                    isset($this->data['PagoActivo']['offset']) &&
                    preg_match('/^\d+$/', $this->data['PagoActivo']['offset'])
            ) {
                $offset = intval($this->data['PagoActivo']['offset']);
            }

            $resultados = array_slice(
                    $afiliados, $offset, 5, true
            );
            // Fin codigo para paginar
        }

        $this->Session->write('PagoActivo.bancos', $bancos);
        $this->set(compact('afiliados', 'resultados', 'total_registros', 'bancos', 'limite_pago_activo', 'tipoConsulta'));

        $this->layout = 'interna';
    }

    /**
     * Metodo para el formulario de pago no afiliados	 
     * 
     */
    function pagos_noafiliada() {

        if (!$this->Session->check('PagoActivo.pagos'))
            $this->redirect('pagos');

        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $estado = $this->Session->read('PagoActivo.pagos');
        $usuario = $this->Session->read('usuarios.datos');
        $bancos = array();
        $canales = $this->Canal->canal_pago_activo();
        $identificacion = desencriptar($usuario['cedula']);
        $tipoIdentificacion = $usuario['tipo_cedula'];
        $telefono = '58' . substr(desencriptar($usuario['celular']), 1);

        if ($estado['PagoActivo']['tipoPersona'] == '1') {
            $tipoCliente = 'J0';
            $tipoConsulta = '1';
            $prefijos_telefonicos = $this->PrefijosTelefonicos->listar_prefijos_pago_movil();
        } else {
            $tipoCliente = 'V0';
            $tipoConsulta = '0';
            $prefijos_telefonicos = $this->PrefijosTelefonicos->listar_prefijos_movil();
        }

        // Si es juridico
        if ($usuario['tipo_rif']) {
            $this->data['PagoActivo']['tipo_rif'] = $usuario['tipo_rif'];
            $this->data['PagoActivo']['rif'] = $usuario['rif'];
            $identificacion = desencriptar($usuario['rif']);
            $tipoIdentificacion = $usuario['tipo_rif'];
        }

        $prefijos = array();
        foreach ($prefijos_telefonicos as $prefijo) {
            $prefijos[$prefijo['PrefijosTelefonicos']['codigo_telefono']] = $prefijo['PrefijosTelefonicos']['codigo_telefono'];
        }

        // Obtener el limite de parametros
        $parametros_limites = array(
            'ip' => $ip,
            'usuario' => desencriptar($usuario['login']),
            'cedula' => 'V0'
        );

        $res_limites = $this->Activo->invocaPagoActivo(
                'consulta_limite_p2p', $parametros_limites
        );
        
        if (!isset($res_limites['cadena']) || $res_limites['cadena']['code'] != "200") {
            $limite_pago_activo = '0';
        } else {
            if ($estado['PagoActivo']['tipoPersona'] == '1') {
                $limite_pago_activo = ltrim($res_limites['cadena']['AREA_LIMITE_OPERACION_P2C'], '0');
            } else {
                $limite_pago_activo = ltrim($res_limites['cadena']['AREA_LIMITE_OPERACION_P2P'], '0');
            }
            $limite_pago_activo = substr($limite_pago_activo, 0, -2) . ',' . substr($limite_pago_activo, -2);
        }
        
        $tipoCuenta = $this->Activo->buscarTipoCuenta($tipoIdentificacion . str_pad($identificacion, 9, '0', STR_PAD_LEFT), array($estado['PagoActivo']['cuentaDebitar']));

        //Tipo de cuenta
        if ($tipoCuenta['SUMTYP'] == 'SAV') {
            $tipoCuenta = '0002';
        } else {
            $tipoCuenta = '0001';
        }

        if (empty($this->data['PagoActivo'])) {
            $this->data = $estado;
        } else {

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->data['PagoActivo']['monto_limite'] = $limite_pago_activo;

            if (empty($usuario['rif'])) {
                $this->data['PagoActivo']['limite_total'] = $limite_pago_activo;
            } else {
                $this->data['PagoActivo']['monto_maximo'] = $limite_pago_activo;
            }

            if (isset($estado['PagoActivo']['monto_limite_usuario'])) {
                $this->data['PagoActivo']['monto_limite_usuario'] = $estado['PagoActivo']['monto_limite_usuario'];
            }

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->PagoActivo->set($this->data);

            if ($this->PagoActivo->validates(
                            array(
                                'fieldList' => array(
                                    'cuentaDebitar',
                                    'telefono',
                                    'bancoReceptor',
                                    'tipoCedulaRif',
                                    'nroCedulaRif',
                                    'monto',
                                    'descripcion',
                                )
                            )
                    )) {

/*                $validacion_otp = $this->Session->read('seguridad.validar_otp');
                if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
                    $this->data['pasos'] = array(
                        'Solicitud de Información',
                        'Confirmación',
                        'Resultado de Operación'
                    );
                    $this->data['paso_final'] = 3;
                } else {

                    $this->data['pasos'] = array(
                        'Solicitud de Información',
                        'Confirmación',
                        'Resultado de Operación'
                    );
                    $this->data['paso_final'] = 2;
                }
*/                

                if (isset($estado['PagoActivo']['aprobaciones_requeridas']))
                    $this->data['PagoActivo']['aprobaciones_requeridas'] = $estado['PagoActivo']['aprobaciones_requeridas'];

                $this->Session->write('PagoActivo.pagos', $this->data);

                $this->Session->delete('transaccion.advertencia');

                // En caso de ser un usuario natural o master unico
                if (empty($usuario['rol_juridico_id']) || ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6)) {
                    
                    $nroRegistro = $this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['PagoActivo'], 100065);

                    if ($nroRegistro > 0) {
                        $this->data['titulo'] = 'Pago Activo';
                        $this->data['mensaje_alerta'] = 'transaccion.alerta';
                        $this->data['url_volver'] = '/pago_activo/pagos_noafiliada';
                        $this->data['url_continuar'] = '/pago_activo/pagos_confirmacion';
                        $this->data['seccion_actual'] = 'pago_activo.pago';

                        $this->Session->write('transaccion.advertencia', $this->data);
                        $this->redirect('transaccion_advertencia');
                    }
                }

                $this->redirect('pagos_confirmacion');
            }
        }

        $parametros = array('tipo_pagador' => $tipoConsulta);

        $res = $this->Activo->invocaPagoActivo(
                'consulta_bancos_p2p', $parametros
        );

        $this->data['Error'] = false;

        if ($res['cadena']['code'] != "200") {
            $this->data['Error'] = true;
            $descripcion = $res['cadena']['descripcion'];
        }

        if (!$this->data['Error']) {
            if (isset($res['cadena']['banco'][0])) {
                foreach ($res['cadena']['banco'] as &$i) {
                    $bancos[$i['codBanco']] = $i['nombreBanco'];
                }
            } else {
                $bancos[$res['cadena']['banco']['codBanco']] = $res['cadena']['banco']['nombreBanco'];
            }
        }

        $this->Session->write('PagoActivo.bancos', $bancos);
        $this->set('seccion_actual', 'pago_activo.pago');
        $this->set('limite_pago_activo', $limite_pago_activo);
        $this->set('bancos', $bancos);
        $this->set('usuario', $usuario);
        $this->set('canales', $canales);
        $this->set('prefijos_telefonicos', $prefijos);

        $this->layout = 'interna';
    }

    /**
     * Metodo para la confirmación del pago
     * 
     */
    function pagos_confirmacion() {

        if (!$this->Session->check('PagoActivo.pagos'))
            $this->redirect('pagos');

        $estado = $this->Session->read('PagoActivo.pagos');

        $bancos = $this->Session->read('PagoActivo.bancos');

        if (!is_null($c = $this->Parametro->findByCodigo('pagoActivo.otros.comision'))) {
            $comision = $c['Parametro']['valor'];
        } else {
            $comision = 0.0;
        }

        $this->layout = 'interna';
        $usuario = $this->Session->read('usuarios.datos');

        $this->set('juridico', $usuario);
        $this->set('bancos', $bancos);
        $this->set('seccion_actual', 'pago_activo.pago');

        $this->data = $estado;
        $this->data['PagoActivo']['comision'] = $comision;

        $this->Session->write('PagoActivo.pagos', $this->data);
    }

    /**
     * Metodo para ejecutar el pago	 
     * 
     */
    function pagos_resultado() {

        if (!$this->Session->check('PagoActivo.pagos'))
            $this->redirect('pagos');

        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $estado = $this->Session->read('PagoActivo.pagos');
        $usuario = $this->Session->read('usuarios.datos');
        $bancos = $this->Session->read('PagoActivo.bancos');
        $identificacion = desencriptar($usuario['cedula']);
        $tipoIdentificacion = $usuario['tipo_cedula'];


        //Validar lista negra
                     
            $swEncontrada = 0;  

            if(isset($estado['PagoActivo']['nroCedulaRif'])){ 
                $tipoDocumento =  trim($estado['PagoActivo']['tipoCedulaRif']);
                $cedulaLB = trim($estado['PagoActivo']['nroCedulaRif']);
                $cedulaLB = $cedulaLB * 1;
            }else{
                if(isset($estado['PagoActivo']['cuentaCreditar'])){
                    $documento = trim($estado['PagoActivo']['cuentaCreditar']);
                    $subDocumento = substr($documento,16);
                    $tipoDocumento = substr($subDocumento,0,1);
                    $cedulaLB = substr($subDocumento,1);
                    $cedulaLB = $cedulaLB * 1;
                    $tipoDocumento = substr($subDocumento,0,1);

                }  
            }
                
            $bancoBenef = $estado['PagoActivo']['banco_receptor'];
            $monto = str_replace(array('.'), '', $estado['PagoActivo']['monto']);

            $swEncontrada = $this->valListaCed($tipoDocumento, $cedulaLB, $bancoBenef, $monto);

            if($swEncontrada == 1){
                $ruta = 'lb_pago';
                $this->redirect($ruta);                    
            }
        ////Fin Validar lista negra


        // Si es juridico
        if ($usuario['tipo_rif']) {
            $this->data['PagoActivo']['tipo_rif'] = $usuario['tipo_rif'];
            $this->data['PagoActivo']['rif'] = $usuario['rif'];
            $identificacion = desencriptar($usuario['rif']);
            $tipoIdentificacion = $usuario['tipo_rif'];

            if ($usuario['rol_juridico_id'] > '2') {
                $buscarUsuario = $this->Usuario->find(
                        'first', array(
                    'fields' => array('id', 'login', 'email_principal', 'celular'),
                    'conditions' => array('tipo_rif' => $usuario['tipo_rif'], 'rif' => $usuario['rif'], 'rol_juridico_id' => 1)
                        )
                );

                $usuarioId = $buscarUsuario['Usuario']['id'];
                $usuarioLogin = $buscarUsuario['Usuario']['login'];
                $correo = $buscarUsuario['Usuario']['email_principal'];
                $telefono = $buscarUsuario['Usuario']['celular'];
            }
        } else {
            $usuarioId = $usuario['id'];
            $usuarioLogin = $usuario['login'];
            $correo = $usuario['email_principal'];
            $telefono = $usuario['celular'];
        }

        $conditions = array(
            'Afiliacion.usuario_id' => $usuarioId,
            'Afiliacion.tipo_afiliacion' => 'm'
        );

        $afiliacion = $this->Afiliacion->find(
                'all', array(
            'conditions' => $conditions,
            'order' => 'Afiliacion.id desc'
                )
        );

        $telefono = '58' . substr($afiliacion[0]['Afiliacion']['codigo_telefono'], 1) . desencriptar($afiliacion[0]['Afiliacion']['telefono']);

        $this->data = $estado;

        // Validacion anti fraude
        $cuentas = $this->Activo->buscarCuentas();

        if (
                !in_array($this->data['PagoActivo']['cuentaDebitar'], array_keys($cuentas))
        ) {
            $this->redirect('/consultas/integral');
        }
        //  Fin validacion anti fraude								

        $this->layout = 'interna';

        $this->set('seccion_actual', 'pago_activo.pago');
        $this->set('bancos', $bancos);

        if ($this->Session->check('PagoActivo.pagos')) {

            $this->data = $this->Session->read('PagoActivo.pagos');
            $this->set('juridico', $usuario['tipo_rif']);
            $error = -1;

            if (
                    empty($usuario['tipo_rif']) || ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) // Master Unico
            ) { 

                /*Inclusión otp pago movil*/
/*                $validacion_otp = $this->Session->read('seguridad.validar_otp');
                 
               if (($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {

                    $pasos = array(
                        'Solicitud de Información',
                        'Confirmación',
                        'Código de Autorización',
                        'Resultado Operación'
                    );

                    $url_volver = '/pago_activo/pagos_confirmacion';
                    
                    $this->Session->write(
                        'seguridad.validar_otp', array(
                        'url_aceptar' => '/pago_activo/pagos_resultado',
                        'url_volver' => $url_volver,
                        'layout' => 'interna',
                        'titulo' => 'Pago Activo',
                        'pasos' => $pasos,
                        'paso_actual' => 2,
                        'seccion_actual' => 'pago_activo.pago'
                            )
                    );

                    $this->redirect('/seguridad/genera_otp');
                }
*/





                $tipoCuenta = $this->Activo->buscarTipoCuenta($tipoIdentificacion . str_pad($identificacion, 9, '0', STR_PAD_LEFT), array($this->data['PagoActivo']['cuentaDebitar']));

                //Tipo de cuenta
                if ($tipoCuenta['SUMTYP'] == 'SAV') {
                    $tipoCuenta = '0002';
                } else {
                    $tipoCuenta = '0001';
                }

                if (isset($estado['PagoActivo']['cuentaCreditar'])) {
                    $bancoBenef = substr($estado['PagoActivo']['cuentaCreditar'], 0, 4);
                    $identificacionBenef = substr($estado['PagoActivo']['cuentaCreditar'], 16, 1) . substr($estado['PagoActivo']['cuentaCreditar'], 17);
                    $telefonoBenef = substr($estado['PagoActivo']['cuentaCreditar'], 4, 12);
                } else {
                    $bancoBenef = $estado['PagoActivo']['banco_receptor'];
                    $identificacionBenef = $estado['PagoActivo']['tipoCedulaRif'] . $estado['PagoActivo']['nroCedulaRif'];
                    $telefonoBenef = '58' . substr($estado['PagoActivo']['codigo_telefono'], 1) . $estado['PagoActivo']['telefono'];
                }

                $parametros = array(
                    'ip' => $ip,
                    'usuario' => desencriptar($usuario['login']),
                    'cedula' => $tipoIdentificacion . $identificacion,
                    'telefono' => $telefono,
                    'tipo_cuenta' => $tipoCuenta,
                    'bco_benef' => $bancoBenef,
                    'cedula_benef' => $identificacionBenef,
                    'telefono_benef' => $telefonoBenef,
                    'email_benef' => $emailBenef,
                    'monto' => str_replace(array('.'), '', $estado['PagoActivo']['monto']),
                    'latitud' => '',
                    'longitud' => '',
                    'precision' => '',
                    'motivo' => $estado['PagoActivo']['descripcion'],
                    'canal' => '01'
                );
                
                $res = $this->Activo->invocaPagoActivo(
                        'pago_p2p', $parametros
                );


               $error = $res['cadena']['code'];

                $this->data['Error'] = false;

                if (!$res || $res['cadena']['code'] != "200") {

                    $error = $res['cadena']['code'];

                    switch ($error) {

                        case 'E002':
                        case 'E001':
                        case 'E003':
                        case 'E008':
                        case 'E009':
                        case 'E012':
                        case 'E021':
                        case '0014':
                            $descripcion = _m('pa.telefono.noregistrado');
                            break;
                        case 'E025':
                            $descripcion = _m('pa.telefono.noasociado');
                            break;
                        case 'V002':
                        case 'V010':
                        case '0022':
                            $descripcion = _m('pa.cedula.invalida');
                            break;
                        case 'V002':
                        case 'E006':
                        case 'E007':
                        case 'E020':
                        case 'V017':
                            $descripcion = _m('pa.tipocuenta.errado');
                            break;
                        case 'E013':
                        case 'E016':
                            $descripcion = _m('pa.cantidadpago.excedido');
                            break;
                        case 'E014':
                        case 'E015':
                        case 'E017':
                            $descripcion = _m('pa.monto.excedido');
                            break;
                        case 'E022':
                            $descripcion = _m('pa.telefono.nopagos');
                            break;
                        case 'E023':
                            $descripcion = _m('pa.informacion.invalida');
                            break;
                        case 'E024':
                            $descripcion = _m('pa.cliente.bloqueado');
                            break;
                        case '0005':
                            $descripcion = _m('pa.pagonegado.receptor');
                            break;
                        case '0012':
                        case '0091':
                        case '0092':
                            $descripcion = _m('pa.receptor.caido');
                            break;
                        case '0041':
                        case '0043':
                            $descripcion = _m('pa.telefono.inactivo');
                            break;
                        case '0057':
                            $descripcion = _m('pa.excede.transacciones');
                            break;
                        case '0096':
                            $descripcion = _m('pa.falla.conexion');
                            break;
                        case 'E030':
                            $descripcion = _m('pa.receptor.noactivo');
                        case '0051':
                            $descripcion = _m('cuenta.sobregirada');
                            break;
                        default:
                            $descripcion = _m('pa.tipocuenta.errado');
                            break;
                    }

                    $this->log('Pago Activo - ' . $error . ' - ' . $descripcion, LOG_ERR);

                    $this->Bitacora->registraBitacora(
                            $usuario['id'], 100065, $estado['PagoActivo']['cuentaDebitar'], $telefonoBenef, $estado['PagoActivo']['monto'], NULL, $descripcion, 1
                    );
                } else {

                    $error = 0;

                    $this->Bitacora->registraBitacora(
                            $usuario['id'], 100065, $estado['PagoActivo']['cuentaDebitar'], $telefonoBenef, $estado['PagoActivo']['monto'], $res['cadena']['nroReferencia'], $estado['PagoActivo']['descripcion'], 0
                    );

                    
                    $referencia = $res['cadena']['nroReferencia'];
                    $this->data['PagoActivo']['REFERENCIA'] = $res['cadena']['nroReferencia'];
                    $this->data['PagoActivo']['CONCEPTO'] = $estado['PagoActivo']['descripcion'];
                    $this->data['PagoActivo']['TIPO'] = 'pago_activo';
                    $this->data['PagoActivo']['NRO_BENEFICIARIO'] = $telefonoBenef;
                    $this->data['PagoActivo']['email'] = $emailBenef;
                    $this->data['PagoActivo']['ESTATUS'] = 'PROCESADA';
                    $this->data['PagoActivo']['banco'] = $bancos[$bancoBenef];
                    $this->data['PagoActivo']['monto'] = $estado['PagoActivo']['monto'];
                    $this->data['PagoActivo']['nroCedulaRif'] = $identificacionBenef;

                    // Genera la data para la descarga de pdf
                    $this->Session->write('usuarios.descarga.pdf', $this->data['PagoActivo']);

                    //Email Ordenante
                    // Email Options
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = desencriptar($usuario['email_principal']);
                    $this->Email->subject = 'Notificacion Pago Activo';
                    $this->Email->sendAs = 'html';

                    // Informacion del correo
                    $this->Email->template = 'pago_activo_ordenante';
                    $tipo_afiliacion = 'Pago Activo';
                    $tipo_accion = 'Pago Activo';

                    $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion'));

                    // Envio del correo
                    $this->Email->send();

                    //Enviar por sms la notificacion
                    if (isset($usuario['celular'])) {
                        $this->Sms->enviar(
                                array(
                                    'numcel' => desencriptar($usuario['celular']),
                                    'canal1' => '71',
                                    'monto' => $this->data['PagoActivo']['monto'],
                                    'refnum' => substr(trim($this->data['PagoActivo']['REFERENCIA']), -4)
                                )
                        );
                    }

                    if (trim($this->data['PagoActivo']['banco']) == 'BANCO ACTIVO') {

                        $datos_basicos = $this->Activo->invoca(
                                'NIBPF3010', array(
                            'req' => array(
                                'NACIONALIDAD' => substr($identificacionBenef, 0, 1),
                                'NROIDENTIFICACION' => str_pad(substr($identificacionBenef, 1), 12, '0', STR_PAD_LEFT),
                                'NROPARAM' => '1'
                            )
                                )
                        );

                        if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010'])) {

                            //Email Beneficiario
                            // Email Options
                            $this->Email->smtpOptions = Configure::read('email.smtp');
                            $this->Email->delivery = 'smtp';
                            $this->Email->from = Configure::read('email.from');
                            $this->Email->to = trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3']);
                            $this->Email->subject = 'Notificacion Pago Activo';
                            $this->Email->sendAs = 'html';

                            // Informacion del correo
                            $this->Email->template = 'pago_activo_beneficiario';
                            $tipo_afiliacion = 'Pago Activo';
                            $tipo_accion = 'Pago Activo';

                            $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion'));

                            // Envio del correo
                            $this->Email->send();
                        }
                    }
                }

                $this->set('pendiente', false);
            } else if (
                    $usuario['rol_juridico_id'] == 3 || // Operador
                    $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
            ) {
                // En caso de juridico, solo los operadores y operadores/aprobadores pueden
                // generar un pago. En cuyo caso, se deja pendiente por aprobar

                if (isset($estado['PagoActivo']['cuentaCreditar'])) {
                    $bancoBenef = substr($estado['PagoActivo']['cuentaCreditar'], 0, 4);
                    $identificacionBenef = substr($estado['PagoActivo']['cuentaCreditar'], 16, 1) . "-" . substr($estado['PagoActivo']['cuentaCreditar'], 17);
                    $telefonoBenef = substr($estado['PagoActivo']['cuentaCreditar'], 4, 12);
                    $email = "";
                } else {
                    $bancoBenef = $estado['PagoActivo']['banco_receptor'];
                    $identificacionBenef = $estado['PagoActivo']['tipoCedulaRif'] . $estado['PagoActivo']['nroCedulaRif'];
                    $telefonoBenef = '58' . substr($estado['PagoActivo']['codigo_telefono'], 1) . $estado['PagoActivo']['telefono'];
                    $email = $this->data['PagoActivo']['emailBeneficiario'];
                }

                $error = 0;

                $this->data['PagoActivo']['banco'] = $bancos[$this->data['PagoActivo']['banco']];

                $res = $this->TransaccionPendiente->save(
                        array(
                    'TransaccionPendiente' => array(
                        'usuario_id' => $usuario['id'],
                        'tipo' => 'tpo',
                        'estatus' => 'pa',
                        'fecha_hora_registro' => date('Y-m-d H:i:s'),
                        'monto' => str_replace('.', '', $this->data['PagoActivo']['monto']),
                        'aprobaciones_requeridas' => $this->data['PagoActivo']['aprobaciones_requeridas'],
                        'tipo_rif' => $usuario['tipo_rif'],
                        'rif' => $usuario['rif'],
                        'cuenta_origen' => encriptar($this->data['PagoActivo']['cuentaDebitar']),
                        'cuenta_destino' => encriptar($telefonoBenef),
                        'descripcion' => encriptar($this->data['PagoActivo']['descripcion']),
                        'nombre_beneficiario' => encriptar($identificacionBenef),
                        'tipo_id_beneficiario' => substr($identificacionBenef, 1, 1),
                        'id_beneficiario' => encriptar(substr($identificacionBenef, 2)),
                        'email_beneficiario' => $email,
                        'alias' => isset($this->data['PagoActivo']['alias']) ? encriptar($this->data['PagoActivo']['alias']) : null
                    )
                        ), false
                );

                if ($res) {
                    $error = 0;
                    $referencia = $this->TransaccionPendiente->id;
                } else {
                    $error = -1;
                    $referencia = '';
                }

                $this->set('pendiente', true);
            }


            $this->Session->write('pago_activo.error',$error);
            $this->Session->write('pago_activo.descripcion',$descripcion);
            $this->Session->write('pago_activo.error_afiliacion',$error_afiliacion);
            $this->Session->write('pago_activo.referencia',$referencia);


            $ruta = 'pagos_resultado_vta';
            $this->redirect($ruta);        

            //$this->set(compact('error', 'descripcion', 'error_afiliacion', 'referencia'));
            //$this->Session->delete('PagoActivo.pagos');
        } else {
            $this->redirect('pagos');
        }
    }


    function pagos_resultado_vta($data){
        $this->layout = 'interna';

        $this->set('seccion_actual', 'pago_activo.pago');

        $this->set('bancos', $bancos); 

        $bancos = $this->Session->read('PagoActivo.bancos');  
        $error = $this->Session->read('pago_activo.error');       
        $descripcion = $this->Session->read('pago_activo.descripcion');       
        $error_afiliacion = $this->Session->read('pago_activo.error_afiliacion');  
        $referencia = $this->Session->read('pago_activo.referencia');       
        $this->data = $this->Session->read('PagoActivo.pagos');

        $this->set(compact('error', 'descripcion', 'error_afiliacion', 'referencia','data','bancos')); 
    }
    



    /**
     * Accion que se encarga de mostrar el listado de afiliaciones del directorio Pago Activo
     * @param string $volver Variable que indica si la pagina se carga
     * debido a un regreso
     */
    function consulta_directorio($volver = null) {

        /* Para validar el acceso al modulo */
        if (!validarTransaccion(array(100069)))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

        $usuario = $this->Session->read('usuarios.datos');

        $listado = array();

       

        $parametros = array(
            'ip' => $ip,
            'usuario' => desencriptar($usuario['login']),
            'metodo' => 'CO',
            'AREA_ORIGEN' => '01'
        );

        $res = $this->Activo->invocaPagoActivo(
                'directorio_p2p', $parametros
        );

        if ($res['cadena']['code'] == "200") {
            if ($res['cadena']['cantidad'] > 0) {
                $listado = $res['cadena']['directorio'];
                normaliza($listado);
            }
        } else {
            $this->data['Error'] = true;
            $descripcion = $res['cadena']['descripcion'];
        }

        if ((!empty($this->data['Directorio']) && !isset($this->data['Directorio']['paginando'])) || (isset($this->data['accion']) && !empty($this->data['accion']))) {

            $this->Directorio->set($this->data);

            if ($this->Directorio->validates(array('fieldList' => array('accion')))) {

                $this->Session->write('Directorio.crear', $this->data);

                switch ($this->data['accion']) {
                    case 'crear':
                        $this->Session->delete('Directorio.crear');
                        $this->redirect('directorio_crear');
                        break;

                    case 'editar':

                        $seleccion = explode(";", $this->data['Directorio']['id']);
                        $afiliacion = array();
                        foreach ($listado as $v) {
                            if ((isset($v['codbanco']) && $v['codbanco'] == $seleccion[0]) && (isset($v['telefono']) && $v['telefono'] == $seleccion[1])) {
                                $afiliacion['Directorio']['nombreBeneficiario'] = trim($v['nombre']);
                                $afiliacion['Directorio']['banco_receptor'] = trim($v['codbanco']);
                                $afiliacion['Directorio']['tipoCedulaRif'] = trim($v['nacionalidad']);
                                $afiliacion['Directorio']['nroCedulaRif'] = trim($v['cedula']);
                                $afiliacion['Directorio']['codigo_telefono'] = "0" . trim(substr($v['telefono'], 2, 3));
                                $afiliacion['Directorio']['telefono'] = trim(substr($v['telefono'], 5));
                            }
                        }
                        $afiliacion['Directorio']['accion'] = 'Modificación';
                        $this->Session->write('Directorio.crear', $afiliacion);
                        $this->redirect('directorio_crear/e');
                        break;

                    case 'eliminar':

                        $seleccion = explode(";", $this->data['Directorio']['id']);
                        $afiliacion = array();
                        foreach ($listado as $v) {
                            if ((isset($v['codbanco']) && $v['codbanco'] == $seleccion[0]) && (isset($v['telefono']) && $v['telefono'] == $seleccion[1])) {
                                $afiliacion['Directorio']['nombreBeneficiario'] = trim($v['nombre']);
                                $afiliacion['Directorio']['banco_receptor'] = trim($v['codbanco']);
                                $afiliacion['Directorio']['tipoCedulaRif'] = trim($v['nacionalidad']);
                                $afiliacion['Directorio']['nroCedulaRif'] = trim($v['cedula']);
                                $afiliacion['Directorio']['codigo_telefono'] = "0" . trim(substr($v['telefono'], 2, 3));
                                $afiliacion['Directorio']['telefono'] = trim(substr($v['telefono'], 5));
                            }
                        }
                        $afiliacion['Afiliacion']['accion'] = 'Eliminación';
                        $this->Session->write('Directorio.eliminar', $afiliacion);
                        $this->redirect('directorio_eliminar_confirmacion');
                        break;
                }
            }
        } else if (isset($volver) && $this->Session->check('pago_activo.directorio')) {
            $this->data = $this->Session->read('pago_activo.directorio');
        }

        $total_registros = 0;

        if (isset($listado) && count($listado) > 0) {
            // Empieza codigo para paginar
            $total_registros = count($listado);
            $offset = 0;

            if (
                    isset($this->data['Directorio']['offset']) &&
                    preg_match('/^\d+$/', $this->data['Directorio']['offset'])
            ) {
                $offset = intval($this->data['Directorio']['offset']);
            }

            $resultados = array_slice(
                    $listado, $offset, 5, true
            );
            // Fin codigo para paginar
        }

        $this->layout = 'interna';
        $this->set('seccion_actual', 'pago_activo.directorio');
        $this->set(compact('listado', 'resultados', 'total_registros'));
    }

    /**
     * Metodo para el formulario de directorio crear 
     * 
     */
    function directorio_crear() {

        $estado = $this->Session->read('Directorio.crear');
        $usuario = $this->Session->read('usuarios.datos');

        $bancos = array();
        $bancosJ = array();
        
        if ($usuario['perfil_ib_id'] == '2') {            
            $listCanales = array("5","7");            
        } else {            
            $listCanales = array("5");            
        }
        
        $parametrosBancoN = array('tipo_pagador' => '0');

        $resBancosN = $this->Activo->invocaPagoActivo(
            'consulta_bancos_p2p', $parametrosBancoN
        );                

        if ($resBancosN['cadena']['code'] == "200" && isset($resBancosN['cadena']['cantBanco'])) {
            if (isset($resBancosN['cadena']['banco'][0])) {
                foreach ($resBancosN['cadena']['banco'] as &$i) {
                    $bancos[$i['codBanco']] = $i['nombreBanco'];
                }
            } else {
                $bancos[$resBancosN['cadena']['banco']['codBanco']] = $resBancosN['cadena']['banco']['nombreBanco'];
            }
        }

        $parametrosBancoJ = array('tipo_pagador' => '1');

        $resBancosJ = $this->Activo->invocaPagoActivo(
            'consulta_bancos_p2p', $parametrosBancoJ
        );                

        if ($resBancosJ['cadena']['code'] == "200" && isset($resBancosN['cadena']['cantBanco'])) {
            if (isset($resBancosJ['cadena']['banco'][0])) {
                foreach ($resBancosJ['cadena']['banco'] as &$i) {
                    $bancosJ[$i['codBanco']] = $i['nombreBanco'];
                }
            } else {
                $bancosJ[$resBancosJ['cadena']['banco']['codBanco']] = $resBancosJ['cadena']['banco']['nombreBanco'];
            }
        }

        $this->Session->write('Directorio.bancos', $bancos);
        $this->Session->write('Directorio.bancosJ', $bancosJ);

        $canales = $this->Canal->canal_pago_activo($listCanales);
        $prefijos_telefonicos = $this->PrefijosTelefonicos->listar_prefijos_pago_movil();
        $prefijos = array();
        foreach ($prefijos_telefonicos as $prefijo) {
            $prefijos[$prefijo['PrefijosTelefonicos']['codigo_telefono']] = $prefijo['PrefijosTelefonicos']['codigo_telefono'];
        }

        if (empty($this->data['Directorio'])) {
            $this->data = $estado;
        } else {

            $this->Directorio->set($this->data);

            if ($this->Directorio->validates(
                            array(
                                'fieldList' => array(
                                    'telefono',
                                    'bancoReceptor',
                                    'tipoCliente',
                                    'tipoCedulaRif',
                                    'nroCedulaRif',
                                    'nombreBeneficiario',
                                )
                            )
                    )) {

                if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5)))) {
                    $this->data['pasos'] = array(
                        'Solicitud de Información',
                        'Confirmación',
                        'Resultado de Operación'
                    );
                    $this->data['paso_final'] = 2;
                } else {

                    $this->data['pasos'] = array(
                        'Solicitud de Información',
                        'Confirmación',
                        'Resultado de Operación'
                    );
                    $this->data['paso_final'] = 2;
                }

                if (isset($estado['Directorio']['aprobaciones_requeridas']))
                    $this->data['Directorio']['aprobaciones_requeridas'] = $estado['Directorio']['aprobaciones_requeridas'];

                $this->Session->write('Directorio.crear', $this->data);

                $this->redirect('directorio_crear_confirmacion');
            }
        }

        $this->set('seccion_actual', 'pago_activo.directorio');
        $this->set('bancos', $bancos);
        $this->set('bancosJ', $bancosJ);
        $this->set('usuario', $usuario);
        $this->set('prefijos_telefonicos', $prefijos);

        $this->layout = 'interna';
    }

    /**
     * Accion de confirmacion de creacion/modificacion de la afiliacion directorio
     */
    function directorio_crear_confirmacion() {

        if ($this->Session->check('Directorio.crear')) {

            $this->layout = 'interna';
            $usuario = $this->Session->read('usuarios.datos');
            $this->set('seccion_actual', 'pago_activo.directorio');

            $this->data = $this->Session->read('Directorio.crear');

            if ($this->data['Directorio']['tipoCliente'] == '0') {
                $bancos = $this->Session->read('Directorio.bancos');
            } else {
                $bancos = $this->Session->read('Directorio.bancosJ');
            }

            $this->set('bancos', $bancos);
        } else {
            $this->redirect('consulta_directorio');
        }
    }

    /**
     * Metodo para ejecutar la afiliacion de un usuario	 
     * 
     */
    function directorio_crear_resultado() {

        if (!$this->Session->check('Directorio.crear'))
            $this->redirect('consulta_directorio');

        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $estado = $this->Session->read('Directorio.crear');
        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $estado;

        if ($this->data['Directorio']['tipoCliente'] == '0') {
            $bancos = $this->Session->read('Directorio.bancos');
        } else {
            $bancos = $this->Session->read('Directorio.bancosJ');
        }

        $this->layout = 'interna';

        if ($this->Session->check('Directorio.crear')) {

            $this->data = $this->Session->read('Directorio.crear');
            $this->set('juridico', $usuario['tipo_rif']);
            $error = -1;

            $parametros = array(
                'ip' => $ip,
                'usuario' => desencriptar($usuario['login']),
                'metodo' => 'IN',
                'tipo_cliente' => $estado['Directorio']['tipoCliente'],
                'nacionalidad' => $estado['Directorio']['tipoCedulaRif'],
                'cedula' => $estado['Directorio']['nroCedulaRif'],
                'codbanco' => $estado['Directorio']['banco_receptor'],
                'nombrebanco' => $bancos[$estado['Directorio']['banco_receptor']],
                'telefono' => '58' . substr($estado['Directorio']['codigo_telefono'], 1) . $estado['Directorio']['telefono'],
                'nombre' => $estado['Directorio']['nombreBeneficiario'],
                'token_autorizado' => '',
                'AREA_ORIGEN' => '01'
            );

            $res = $this->Activo->invocaPagoActivo(
                    'directorio_p2p', $parametros
            );

            $this->data['Error'] = false;

            if (!$res || $res['cadena']['code'] != "200") {

                $error = $res['cadena']['code'];
                $descripcion = $res['cadena']['descripcion'];
                $this->log('Directorio Pago Activo - ' . $descripcion, LOG_ERR);

                $this->Bitacora->registraBitacora(
                        $usuario['id'], 100069, $estado['Directorio']['tipoCedulaRif'] . $estado['Directorio']['nroCedulaRif'], '58' . substr($estado['Directorio']['codigo_telefono'], 1) . $estado['Directorio']['telefono'], NULL, NULL, $descripcion, 1
                );
            } else {

                $error = 0;

                $this->Bitacora->registraBitacora(
                        $usuario['id'], 100069, $estado['Directorio']['tipoCedulaRif'] . $estado['Directorio']['nroCedulaRif'], '58' . substr($estado['Directorio']['codigo_telefono'], 1) . $estado['Directorio']['telefono'], NULL, NULL, 'Afiliación Directorio Pago Activo', 0
                );
            }

            $this->set(compact('error', 'descripcion', 'estado'));

            $this->Session->delete('Directorio.crear');
        } else {
            $this->redirect('consulta_directorio');
        }

        $this->data = $estado;
        $this->set('seccion_actual', 'pago_activo.directorio');
        $this->set('bancos', $bancos);
    }

    /**
     * Metodo para la confirmacion de la eliminacion de una afiliacion del directorio
     * 
     */
    function directorio_eliminar_confirmacion() {

        if (!$this->Session->check('Directorio.eliminar'))
            $this->redirect('consulta_directorio');

        $this->layout = 'interna';
        $estado = $this->Session->read('Directorio.eliminar');
        $bancos = $this->Session->read('Directorio.bancos');
        $usuario = $this->Session->read('usuarios.datos');

        $this->set('bancos', $bancos);
        $this->set('seccion_actual', 'pago_activo.directorio');

        $this->data = $estado;

        $this->Session->write('Directorio.eliminar', $this->data);
    }

    /**
     * Metodo para ejecutar la eliminacion de un registro del directorio
     * 
     */
    function directorio_eliminar_resultado() {

        if (!$this->Session->check('Directorio.eliminar'))
            $this->redirect('consulta_directorio');

        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $estado = $this->Session->read('Directorio.eliminar');
        $usuario = $this->Session->read('usuarios.datos');
        $bancos = $this->Session->read('Directorio.bancos');
        $this->data = $estado;

        $this->layout = 'interna';

        if ($this->Session->check('Directorio.eliminar')) {

            $this->data = $this->Session->read('Directorio.eliminar');
            $this->set('juridico', $usuario['tipo_rif']);
            $error = -1;

            $parametros = array(
                'ip' => $ip,
                'usuario' => desencriptar($usuario['login']),
                'metodo' => 'EL',
                'nacionalidad' => $estado['Directorio']['tipoCedulaRif'],
                'cedula' => $estado['Directorio']['nroCedulaRif'],
                'codbanco' => $estado['Directorio']['banco_receptor'],
                //'nombrebanco'=>$bancos[$estado['Directorio']['banco_receptor']],
                'telefono' => '58' . substr($estado['Directorio']['codigo_telefono'], 1) . $estado['Directorio']['telefono'],
                'nombre' => $estado['Directorio']['nombreBeneficiario'],
                'token_autorizado' => '',
                'AREA_ORIGEN' => '01'
            );

            $res = $this->Activo->invocaPagoActivo(
                    'directorio_p2p', $parametros
            );

            $this->data['Error'] = false;

            if (!$res || $res['cadena']['code'] != "200") {

                $error = $res['cadena']['code'];
                $descripcion = $res['cadena']['descripcion'];
                $this->log('Directorio Pago Activo - ' . $descripcion, LOG_ERR);

                $this->Bitacora->registraBitacora(
                        $usuario['id'], 100069, $estado['Directorio']['tipoCedulaRif'] . $estado['Directorio']['nroCedulaRif'], '58' . substr($estado['Directorio']['codigo_telefono'], 1) . $estado['Directorio']['telefono'], NULL, NULL, $descripcion, 1
                );
            } else {

                $error = 0;

                $this->Bitacora->registraBitacora(
                        $usuario['id'], 100069, $estado['Directorio']['tipoCedulaRif'] . $estado['Directorio']['nroCedulaRif'], '58' . substr($estado['Directorio']['codigo_telefono'], 1) . $estado['Directorio']['telefono'], NULL, NULL, 'Afiliación Directorio Pago Activo', 0
                );
            }

            $this->set(compact('error', 'descripcion', 'estado'));

            $this->Session->delete('Directorio.eliminar');
        } else {
            $this->redirect('consulta_directorio');
        }

        $this->data = $estado;
        $this->set('seccion_actual', 'pago_activo.directorio');
        $this->set('bancos', $bancos);
    }

    /**
     * Accion que despliega el detalle de la cuenta movimientos P2P
     * @param string $volver Indica si esta regresando de la busqueda
     * */
    function detalle($volver = '') {

        /* Para validar el acceso al modulo */
        if (!validarTransaccion(array(100066)))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        set_time_limit(0);

        if ($this->Session->check('pago_activo.usuarios.volver_buscar')) {
            $ruta_volver = '/pago_activo/buscar';
        } else {
            $ruta_volver = '';
        }

        $usuario = $this->Session->read('usuarios.datos');

        // Registra la consulta del producto
        $this->Bitacora->registraBitacora($usuario['id'], 100066, null, null, null, null, "Consulta Cuenta P2P " . $this->data['DetalleProducto']['nro_cuenta']);

        if ($usuario['rol_juridico_id'] > '2') {
            $buscarUsuario = $this->Usuario->find(
                    'first', array(
                'fields' => array('id', 'login', 'email_principal', 'celular'),
                'conditions' => array('tipo_rif' => $usuario['tipo_rif'], 'rif' => $usuario['rif'], 'rol_juridico_id' => 1)
                    )
            );

            $usuarioId = $buscarUsuario['Usuario']['id'];
            $usuarioLogin = $buscarUsuario['Usuario']['login'];
            $correo = $buscarUsuario['Usuario']['email_principal'];
            $telefono = $buscarUsuario['Usuario']['celular'];
        } else {

            $usuarioId = $usuario['id'];
            $usuarioLogin = $usuario['login'];
            $correo = $usuario['email_principal'];
            $telefono = $usuario['celular'];
        }

        $cuentas = $this->Activo->buscarCuentasSaldos();
        $cuentasPR = $this->Afiliacion->buscarCuentasPRAfiliadasPagoActivo($usuarioId);
        $cuentasPRD = array();

        //Busca solo las cuentas afiliadas en P2P
        foreach ($cuentasPR as $cp) {
            if (trim($cp['Afiliacion']['numero_cuenta']) != '') {
                array_push($cuentasPRD, desencriptar($cp['Afiliacion']['numero_cuenta']));
            }

            if (trim($cp['Afiliacion']['numero_cuenta_ahorro']) != '') {
                array_push($cuentasPRD, desencriptar($cp['Afiliacion']['numero_cuenta_ahorro']));
            }

            if (trim($cp['Afiliacion']['numero_cuenta_receptora']) != '') {
                array_push($cuentasPRD, desencriptar($cp['Afiliacion']['numero_cuenta_receptora']));
            }
        }

        foreach ($cuentas as $c => $valor) {
            if (!in_array($c, $cuentasPRD)) {
                unset($cuentas[$c]);
            }
        }

        if (isset($this->data['DetalleProducto'])) {

            $this->DetalleProducto->set($this->data['DetalleProducto']);

            if (!$this->DetalleProducto->validates(array('fieldList' => array('nro_cuenta')))) {
                $this->redirect('detalle');
            }
        } else {
            $this->data['DetalleProducto']['nro_cuenta'] = $cuentasPRD[0];
        }

        /** Para identificar si es un usuario juridico * */
        if ($usuario['tipo_rif']) {
            $identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
        } else {
            $identificacion = desencriptar($usuario['cedula']);
        }
        /** Para identificar si es un usuario juridico * */
        $parametros_consulta = array(
            'req' => array(
                'canal' => Configure::read('canal.pa'),
                'RSTACC5' => substr($this->data['DetalleProducto']['nro_cuenta'], 10),
                'RSTCAF5' => '1'
            )
        );

        $this->Session->write('pago_activo.ultima_consulta', $parametros_consulta);

        $res = $this->Activo->invoca(
            'STMRDSJV5', $parametros_consulta
        );

        $totales = array(
            'ABONOS' => 0,
            'CREDITOS' => 0
        );

        $arrData = array();

        //		
        // Verifica que el webservice traiga registros
        if (!empty($res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'])) {
            $arrData = $res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'];
            normaliza($arrData);
            $arrData = array_reverse($arrData);
        }

        // Totales
        if (count($arrData)) {

            $contador = 0;

            foreach ($arrData as $i) {
                if ($i['STMDCC'] == '5') {
                    $totales['ABONOS']+=$i['STMAMT'];
                } elseif ($i['STMDCC'] == '0') {
                    $totales['CREDITOS']+=$i['STMAMT'];
                }
            }
        }

        // Empieza codigo para paginar

        $total_registros = count($arrData);

        $offset = 0;

        if (!empty($volver))
            $this->params['form']['offset'] = intval($this->Session->read('pago_activo.cuentas.offset'));

        if (
                isset($this->params['form']['offset']) &&
                preg_match('/^\d+$/', $this->params['form']['offset'])
        ) {
            $offset = intval($this->params['form']['offset']);
        }

        $arrData = array_slice($arrData, $offset, Configure::read('Tamano.paginacion'), true
        );

        $this->Session->write('pago_activo.cuentas.offset', $offset);

        // Fin codigo para paginar

        $flag_buscar = false;

        $this->Session->delete('pago_activo.cuentas.flagBusqueda');

        $periodo = false;

        $alias = _a($usuario['id'], $this->data['DetalleProducto']['nro_cuenta']);

        $this->set(compact('res', 'cuentas', 'totales', 'total_registros', 'arrData', 'flag_buscar', 'alias', 'periodo'));

        $this->layout = 'interna';

        $this->set('seccion_actual', 'pago_activo.transacciones');
    }

    /**
     * Accion para buscar movimientos P2P de una cuenta
     * @param string $volver Indica si esta regresado del detalle
     * */
    function buscar($volver = '') {

        $this->layout = 'interna';
        $this->set('seccion_actual', 'pago_activo.transacciones');

        if ($this->Session->check('pago_activo.cuentas.busqueda') && !empty($volver)) {
            $this->data = $this->Session->read('pago_activo.cuentas.busqueda');
            $this->Session->delete('pago_activo.cuentas.busqueda');
        }

        if (!empty($this->data['DetalleProducto']['nro_cuenta'])) {

            $this->DetalleProducto->set($this->data['DetalleProducto']);

            if (!$this->DetalleProducto->validates(array('fieldList' => array('nro_cuenta')))) {
                $this->redirect('detalle');
            }

            $this->data['BusquedaCuenta']['nro_cuenta'] = $this->data['DetalleProducto']['nro_cuenta'];
        } elseif (!empty($this->data['BusquedaCuenta'])) {

            $this->BusquedaCuenta->set($this->data);
            $this->data['DetalleProducto'] = $this->data['BusquedaCuenta'];

            if ($this->BusquedaCuenta->validates()) {

                // Usuario
                $usuario = $this->Session->read('usuarios.datos');
                $referencia = '';
                // Verifica si ocurre un error

                $cuentas = $this->Activo->buscarCuentasConsulta();
                $totales = array('ABONOS' => 0, 'CREDITOS' => 0);
                $arrData = array();
                $arreglo = array();
                $arrDataRef = array();

                if ($this->data['BusquedaCuenta']['tipoBusq'] == 'f') {

                    $tipo_consulta = 'R';
                    $referencia = str_pad($this->data['BusquedaCuenta']['referencia'], 9, 0, STR_PAD_LEFT);
                    $dd = '';
                    $md = '';
                    $ad = '';
                    $dh = '';
                    $mh = '';
                    $ah = '';
                    $montodesde = '';
                    $montohasta = '';
                } elseif ($this->data['BusquedaCuenta']['tipoBusq'] == 't') {

                    switch ($this->data['BusquedaCuenta']['periodo']) {

                        case 'o':
                            // Desde
                            $dd = $this->data['BusquedaCuenta']['fechaDesde']['day'];
                            $md = $this->data['BusquedaCuenta']['fechaDesde']['month'];
                            $ad = $this->data['BusquedaCuenta']['fechaDesde']['year'];
                            $ad = substr($ad, 2);

                            // Hasta
                            $dh = $this->data['BusquedaCuenta']['fechaHasta']['day'];
                            $mh = $this->data['BusquedaCuenta']['fechaHasta']['month'];
                            $ah = $this->data['BusquedaCuenta']['fechaHasta']['year'];
                            $ah = substr($ah, 2);

                            $tipo_consulta = 'F';
                            break;

                        case 'n':
                            $tipo_consulta = '3';
                            break;
                        case 'u':
                            $tipo_consulta = ' ';
                            break;

                        default:
                            $tipo_consulta = '1';
                            break;
                    }
                } elseif ($this->data['BusquedaCuenta']['tipoBusq'] == 'm') {

                    $tipo_consulta = 'M';
                    $referencia = '';
                    $dd = '';
                    $md = '';
                    $ad = '';
                    $dh = '';
                    $mh = '';
                    $ah = '';
                    $montodesde = (empty($this->data['BusquedaCuenta']['montoDesde'])) ? '00' : formatea_monto_toWs($this->data['BusquedaCuenta']['montoDesde']);
                    $montohasta = (empty($this->data['BusquedaCuenta']['montoHasta'])) ? '00' : formatea_monto_toWs($this->data['BusquedaCuenta']['montoHasta']);
                }

                /** Para identificar si es un usuario juridico * */
                if ($usuario['tipo_rif']) {
                    $identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
                } else {
                    $identificacion = desencriptar($usuario['cedula']);
                }
                /** Para identificar si es un usuario juridico * */
                $parametros_consulta = array(
                    'req' => array(
                        'RSTACC5' => substr($this->data['BusquedaCuenta']['nro_cuenta'], 10),
                        'RSTCAF5' => $tipo_consulta,
                        'RSTFAM5' => $montodesde,
                        'RSTFCK5' => '00',
                        'RSTFD15' => $dd,
                        'RSTFD25' => $md,
                        'RSTFD35' => $ad,
                        'RSTTAM5' => $montohasta,
                        'RSTTCK5' => '00',
                        'RSTTD15' => $dh,
                        'RSTTD25' => $mh,
                        'RSTTD35' => $ah,
                        'RSTFCK5' => $referencia,
                        'RSTTCK5' => $referencia
                    )
                );

                $this->Session->write('pago_activo.cuentas.ultima_consulta', $parametros_consulta);
                $this->Session->write('pago_activo.cuentas.busqueda', $this->data);

                // Invocacion del webservices para el detalle de las cuentas
                $res = $this->Activo->invoca(
                        'STMRDSJV5', $parametros_consulta
                );
               
                if ($res) {

                    // Verifica que el webservice traiga registros
                    if (isset($res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'])) {

                        $arrData = $res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'];

                        normaliza($arrData);

                        foreach ($arrData as $i) {
                            if ($i['STMDCC'] == '5') {
                                $totales['ABONOS']+=$i['STMAMT'];
                            } elseif ($i['STMDCC'] == '0') {
                                $totales['CREDITOS']+=$i['STMAMT'];
                            }

                            $arreglo[] = $i;
                        }

                        $arrData = $arreglo;
                    }
                }

                // Inidica a donde va regresar
                $ruta_regreso = '/pago_activo/buscar';

                // Indica que esta desde el buscador
                $flag_buscar = true;
                $this->Session->write('pago_activo.cuentas.flagBusqueda', $flag_buscar);

                $periodo = ((isset($this->data['DetalleProducto']['periodo']) && ($this->data['DetalleProducto']['periodo'] == 'u')));

                // Empieza codigo para paginar

                $total_registros = count($arrData);

                $offset = 0;

                if (!empty($volver))
                    $this->params['form']['offset'] = intval($this->Session->read('pago_activo.cuentas.offset'));

                if (
                        isset($this->params['form']['offset']) &&
                        preg_match('/^\d+$/', $this->params['form']['offset'])
                ) {
                    $offset = intval($this->params['form']['offset']);
                }

                $arrData = array_slice(array_reverse($arrData), $offset, Configure::read('Tamano.paginacion'), true
                );

                $this->Session->write('pago_activo.cuentas.offset', $offset);

                // Fin codigo para paginar                           

                $this->set(compact('res', 'cuentas', 'totales', 'arrData', 'total_registros', 'ruta_regreso', 'flag_buscar', 'periodo'));

                $this->render('detalle');
            }
        } else {
            $this->redirect('detalle');
        }
    }

    /**
     * Accion para el historico de transacciones P2P - C2P
     * @param String $volver
     */
    function historico_transacciones($volver = '') {

        // setlocale(LC_ALL,'es_VE.utf8'); PARA LINUX
        setlocale(LC_ALL, 'es_VE.UTF-8');

        $this->layout = 'interna';

        $this->set('seccion_actual', 'pago_activo.historico.transacciones');

        $transacciones = array();
        $condiciones = array();

        $usuario = $this->Session->read('usuarios.datos');

        //Si es juridico y tiene un perfil distinto a master unico busco el id del master
        if ($usuario['perfil_ib_id'] == 2 && $usuario['rol_juridico_id'] > 2) {
            $buscarUsuario = $this->Usuario->find(
                'first', array(
                'fields' => array('id', 'login', 'email_principal', 'celular'),
                'conditions' => array('tipo_rif' => $usuario['tipo_rif'], 'rif' => $usuario['rif'], 'rol_juridico_id' => 1)
                )
            );

            $usuarioId = $buscarUsuario['Usuario']['id'];
        } else {
            $usuarioId = $usuario['usuario_id'];
        }

        if (!empty($volver)) {

            if (!$this->Session->check('pago_activo.transacciones_historico')) {
                $this->redirect('historico_transacciones');
            }

            $this->data = $this->Session->read('pago_activo.transacciones_historico_params');

            

            $condiciones = $this->Session->read('pago_activo.transacciones_historico');
        } else if (!empty($this->data['HistoricoTransacciones'])) {

            if (!empty($this->data['HistoricoTransacciones']['perfiles'])) {

                if ($this->data['HistoricoTransacciones']['perfiles'] == 't') {
                    $condiciones["usuario_id in (select id from usuarios where rif=? and tipo_rif=?)"] = array(
                        $usuario['rif'],
                        $usuario['tipo_rif']
                    );
                } else {
                    $condiciones["usuario_id in (select id from usuarios where rif=? and tipo_rif=? and rol_juridico_id=?)"] = array(
                        $usuario['rif'],
                        $usuario['tipo_rif'],
                        $this->data['HistoricoTransacciones']['perfiles']
                    );
                }

                if (isset($condiciones['usuario_id']))
                    unset($condiciones['usuario_id']);
            }

            if (!empty($this->data['HistoricoTransacciones']['transaccion'])) {

                //Si la busqueda es de pagos se consulta directamente el servicio de AS400                        
                if ($this->data['HistoricoTransacciones']['transaccion'] == 100065) {

                    $transaccionesPagos = array();

                    $cuentasPR = $this->Afiliacion->buscarCuentasPRAfiliadasPagoActivo($usuarioId);
                    if (!empty($cuentasPR)) {
                        $cuentasPR[0]['Afiliacion'] = array_unique($cuentasPR[0]['Afiliacion']);
                    }

                    

                    if (!empty($this->data['HistoricoTransacciones']['referencia'])) {

                        $tipo_consulta = 'R';
                        $referencia = str_pad($this->data['HistoricoTransacciones']['referencia'], 9, 0, STR_PAD_LEFT);
                        $dd = '';
                        $md = '';
                        $ad = '';
                        $dh = '';
                        $mh = '';
                        $ah = '';
                        $montodesde = '';
                        $montohasta = '';
                    } elseif (!empty($this->data['HistoricoTransacciones']['monto'])) {

                        $tipo_consulta = 'M';
                        $referencia = '';
                        $dd = '';
                        $md = '';
                        $ad = '';
                        $dh = '';
                        $mh = '';
                        $ah = '';
                        $montodesde = formatea_monto_toWs($this->data['HistoricoTransacciones']['monto']);
                        $montohasta = formatea_monto_toWs($this->data['HistoricoTransacciones']['monto']);
                    } elseif ($this->data['HistoricoTransacciones']['tipo'] == 'p') {

                        // Desde
                        $dd = $this->data['HistoricoTransacciones']['fechaDesde']['day'];
                        $md = $this->data['HistoricoTransacciones']['fechaDesde']['month'];
                        $ad = $this->data['HistoricoTransacciones']['fechaDesde']['year'];
                        $ad = substr($ad, 2);

                        // Hasta
                        $dh = $this->data['HistoricoTransacciones']['fechaHasta']['day'];
                        $mh = $this->data['HistoricoTransacciones']['fechaHasta']['month'];
                        $ah = $this->data['HistoricoTransacciones']['fechaHasta']['year'];
                        $ah = substr($ah, 2);

                        $tipo_consulta = 'F';
                        $referencia = '';
                        $montodesde = '';
                        $montohasta = '';
                    } elseif ($this->data['HistoricoTransacciones']['tipo'] == 't') {

                        if (empty($this->data['HistoricoTransacciones']['mes_transaccion'])) {
                            $tipoConsulta = ' ';
                        } else {
                            $fecha_desde = date('Y-m-d 00:00:00', $this->data['HistoricoTransacciones']['mes_transaccion']);
                            $fecha_hasta = date('Y-m-d 23:59:59', strtotime($fecha_desde . " +1 month"));

                            // Desde
                            $dd = date('d', strtotime($fecha_desde));
                            $md = date('m', strtotime($fecha_desde));
                            $ad = date('Y', strtotime($fecha_desde));
                            $ad = substr($ad, 2);

                            // Hasta
                            $dh = date('d', strtotime($fecha_hasta));
                            $mh = date('m', strtotime($fecha_hasta));
                            $ah = date('Y', strtotime($fecha_hasta));
                            $ah = substr($ah, 2);

                            $tipo_consulta = 'F';
                        }

                        $referencia = '';
                        $montodesde = '';
                        $montohasta = '';
                    }

                    //Se buscan las transacciones de todas las cuentas y se organizan por referencia
                    foreach ($cuentasPR as $cp) {

                        if (isset($cp['Afiliacion']['numero_cuenta']) && trim($cp['Afiliacion']['numero_cuenta']) != '') {

                            $parametros_consulta = array(
                                'req' => array(
                                    'canal' => Configure::read('canal.pa'),
                                    'RSTACC5' => substr(desencriptar($cp['Afiliacion']['numero_cuenta']), 10),
                                    'RSTCAF5' => $tipo_consulta,
                                    'RSTFAM5' => $montodesde,
                                    'RSTFCK5' => '00',
                                    'RSTFD15' => $dd,
                                    'RSTFD25' => $md,
                                    'RSTFD35' => $ad,
                                    'RSTTAM5' => $montohasta,
                                    'RSTTCK5' => '00',
                                    'RSTTD15' => $dh,
                                    'RSTTD25' => $mh,
                                    'RSTTD35' => $ah,
                                    'RSTFCK5' => $referencia,
                                    'RSTTCK5' => $referencia
                                )
                            );

                            $res = $this->Activo->invoca(
                                    'STMRDSJV5', $parametros_consulta
                            );

                            // Verifica que el webservice traiga registros
                            if (!empty($res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'])) {
                                $arrData = $res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'];
                                normaliza($arrData);
                                $arrData = array_reverse($arrData);
                                array_push($transaccionesPagos, $arrData);
                            }
                        }

                        if (isset($cp['Afiliacion']['numero_cuenta_ahorro']) && trim($cp['Afiliacion']['numero_cuenta_ahorro']) != '') {

                            $parametros_consulta = array(
                                'req' => array(
                                    'canal' => Configure::read('canal.pa'),
                                    'RSTACC5' => substr(desencriptar($cp['Afiliacion']['numero_cuenta_ahorro']), 10),
                                    'RSTCAF5' => $tipo_consulta,
                                    'RSTFAM5' => $montodesde,
                                    'RSTFCK5' => '00',
                                    'RSTFD15' => $dd,
                                    'RSTFD25' => $md,
                                    'RSTFD35' => $ad,
                                    'RSTTAM5' => $montohasta,
                                    'RSTTCK5' => '00',
                                    'RSTTD15' => $dh,
                                    'RSTTD25' => $mh,
                                    'RSTTD35' => $ah,
                                    'RSTFCK5' => $referencia,
                                    'RSTTCK5' => $referencia
                                )
                            );

                            $res = $this->Activo->invoca(
                                    'STMRDSJV5', $parametros_consulta
                            );

                            // Verifica que el webservice traiga registros
                            if (!empty($res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'])) {
                                $arrData = $res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'];
                                normaliza($arrData);
                                $arrData = array_reverse($arrData);
                                foreach ($arrData as $j) {
                                    array_push($transaccionesPagos[0], $j);
                                }
                            }
                        }

                        if (isset($cp['Afiliacion']['numero_cuenta_receptora']) && trim($cp['Afiliacion']['numero_cuenta_receptora']) != '') {

                            $parametros_consulta = array(
                                'req' => array(
                                    'canal' => Configure::read('canal.pa'),
                                    'RSTACC5' => substr(desencriptar($cp['Afiliacion']['numero_cuenta_receptora']), 10),
                                    'RSTCAF5' => $tipo_consulta,
                                    'RSTFAM5' => $montodesde,
                                    'RSTFCK5' => '00',
                                    'RSTFD15' => $dd,
                                    'RSTFD25' => $md,
                                    'RSTFD35' => $ad,
                                    'RSTTAM5' => $montohasta,
                                    'RSTTCK5' => '00',
                                    'RSTTD15' => $dh,
                                    'RSTTD25' => $mh,
                                    'RSTTD35' => $ah,
                                    'RSTFCK5' => $referencia,
                                    'RSTTCK5' => $referencia
                                )
                            );

                            $res = $this->Activo->invoca(
                                    'STMRDSJV5', $parametros_consulta
                            );

                            // Verifica que el webservice traiga registros
                            if (!empty($res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'])) {
                                $arrData = $res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'];
                                normaliza($arrData);
                                $arrData = array_reverse($arrData);
                                foreach ($arrData as $j) {
                                    array_push($transaccionesPagos[0], $j);
                                }
                            }
                        }
                    }

                    $offset = 0;

                    if ($transaccionesPagos[0] != null) {
                        $ordenado = $this->Activo->array_orderby($transaccionesPagos[0], 'STMCKN', SORT_DESC);
                        $transaccionesPagos = $ordenado;

                        $offset = 0;

                        $total_registros = count($transaccionesPagos);

                        if (!empty($volver))
                            $this->params['form']['offset'] = intval($this->Session->read('pago_activo.historico.offset'));

                        if (
                                isset($this->data['HistoricoTransacciones']['offset']) &&
                                preg_match('/^\d+$/', $this->data['HistoricoTransacciones']['offset'])
                        ) {
                            $offset = intval($this->data['HistoricoTransacciones']['offset']);
                        }

                        $transaccionesPagos = array_slice($transaccionesPagos, $offset, Configure::read('Tamano.paginacion'), true
                        );

                        $this->Session->write('pago_activo.historico.offset', $offset);
                    } else {
                        $transaccionesPagos = array();

                        $total_registros = count($transaccionesPagos);
                    }
                } else {

                    $condiciones = array(
                        'usuario_id' => $usuario['id'],
                        'canal' => Configure::read('canal.ib'),
                        'transaccion_id' => $this->data['HistoricoTransacciones']['transaccion'],
                        'Bitacora.fecha_hora >=' => date('Y-m-d 00:00:00'),
                        'Bitacora.fecha_hora <=' => date('Y-m-d 23:59:59'),
                        'OR' => array(
                            'error_operacion IS NULL',
                            'error_operacion = 0',
                        )
                    );

                    if (!empty($this->data['HistoricoTransacciones']['referencia'])) {
                        $condiciones['Bitacora.referencia'] = encriptar($this->data['HistoricoTransacciones']['referencia']);
                    }

                    if (!empty($this->data['HistoricoTransacciones']['monto'])) {
                        $condiciones['Bitacora.monto'] = formatea_monto_toWs($this->data['HistoricoTransacciones']['monto']);
                    }

                    if (isset($this->data['HistoricoTransacciones']['tipo'])) {
                        if ($this->data['HistoricoTransacciones']['tipo'] == 'p') {
                            $condiciones['Bitacora.fecha_hora >='] = formatea_fecha_toMSSQL(
                                            $this->data['HistoricoTransacciones']['fechaDesde']['day'], $this->data['HistoricoTransacciones']['fechaDesde']['month'], $this->data['HistoricoTransacciones']['fechaDesde']['year']
                                    ) . ' 00:00:00';

                            $condiciones['Bitacora.fecha_hora <='] = formatea_fecha_toMSSQL(
                                            $this->data['HistoricoTransacciones']['fechaHasta']['day'], $this->data['HistoricoTransacciones']['fechaHasta']['month'], $this->data['HistoricoTransacciones']['fechaHasta']['year']
                                    ) . ' 23:59:59';
                        } else if ($this->data['HistoricoTransacciones']['tipo'] == 't') {
                            if (empty($this->data['HistoricoTransacciones']['mes_transaccion'])) {
                                $condiciones['Bitacora.fecha_hora >='] = date('Y-m-d 00:00:00', strtotime('-1 year'));
                                $condiciones['Bitacora.fecha_hora <='] = date('Y-m-d 23:59:59');
                            } else {
                                $fecha_desde = date('Y-m-d 00:00:00', $this->data['HistoricoTransacciones']['mes_transaccion']);
                                $condiciones['Bitacora.fecha_hora >='] = $fecha_desde;
                                $condiciones['Bitacora.fecha_hora <='] = date('Y-m-d 23:59:59', strtotime($fecha_desde . " +1 month"));
                            }
                        }
                    }

                    $total_registros = $this->Bitacora->find(
                        'count', array(
                        'conditions' => $condiciones
                        )
                    );

                    $offset = 0;

                    if (
                        isset($this->data['HistoricoTransacciones']['offset']) &&
                        preg_match('/^\d+$/', $this->data['HistoricoTransacciones']['offset'])
                    ) {
                        $offset = intval($this->data['HistoricoTransacciones']['offset']);
                    }

                    $resultados = $this->Bitacora->find(
                            'all', array(
                        'conditions' => $condiciones,
                        'contain' => array('Transaccion' => 'Modulo', 'Usuario' => 'login'),
                        'order' => 'Bitacora.fecha_hora desc',
                        'offset' => $offset,
                        'limit' => Configure::read('Tamano.paginacion')
                            )
                    );
                }
            }
        } else {
            if ($usuario['rol_juridico_id'] == 1) {
                $this->data['HistoricoTransacciones']['perfiles'] = '1';
            }
        }

        $this->Session->write('consultas.transacciones_historico_params', $this->data);
        $this->Session->write('consultas.transacciones_historico', $condiciones);

        $this->set(compact('resultados', 'total_registros', 'transaccionesPagos'));
        

        $transacciones = $this->Transaccion->obtenerTransaccionesPorModulo($usuario, 14);
        
        unset($transacciones[100067]);
        unset($transacciones[100068]);
        unset($transacciones[101076]);

        if (!empty($usuario['tipo_rif'])) { // PARA JURIDICO
            // En caso de que no sea MASTER
            if ($usuario['rol_juridico_id'] > 1) {
                $condicion_modulos = array(
                    'Modulo.canal_id' => Configure::read('canal.ib'),
                    'Modulo.id in (
                    SELECT transacciones.modulo_id 
                    FROM transacciones
                    INNER JOIN transacciones_usuarios_juridicos TJ ON TJ.transaccion_id=transacciones.id
                    WHERE TJ.usuario_id=' . $usuario['id'] . '
                )',
                    'Modulo.id' => 14
                );
            } else { // SI ES UN USUARIO MASTER
                $condicion_modulos = array(
                    'Modulo.canal_id' => Configure::read('canal.ib'),
                    "Modulo.id in (
                    SELECT transacciones.modulo_id 
                    FROM transacciones
                    INNER JOIN transacciones_usuarios_juridicos TJ ON TJ.transaccion_id=transacciones.id
                    WHERE TJ.usuario_id in (select id from usuarios where rif = '" . $usuario['rif'] . "' and tipo_rif = '" . $usuario['tipo_rif'] . "')
                    GROUP BY transacciones.modulo_id
                )",
                    'Modulo.id' => 14
                );
            }

            $modulos = $this->Modulo->find(
                'list', array(
                'fields' => array('Modulo.id', 'Modulo.nombre'),
                'order' => 'posicion asc',
                'conditions' => $condicion_modulos
                    )
            );

        } else { // PARA NATURALES
            $modulos = $this->Modulo->find(
                'list', array(
                    'fields' => array('Modulo.id', 'Modulo.nombre'),
                    'order' => 'posicion asc',
                    'conditions' => array(
                    'Modulo.canal_id' => Configure::read('canal.ib'),
                    'Modulo.id in (
                    SELECT transacciones.modulo_id 
                    FROM transacciones
                    INNER JOIN transacciones_perfiles_ib ON transacciones_perfiles_ib.transaccion_id=transacciones.id
                    WHERE transacciones_perfiles_ib.perfil_ib_id=' . $usuario['perfil_ib_id'] . '
                    and (
                    transacciones_perfiles_ib.perfil_piloto is null or 
                    transacciones_perfiles_ib.perfil_piloto in (SELECT perfil_piloto FROM usuarios_perfil_piloto WHERE usuario_id = ' . $usuario['id'] . ')
                    )                                    
                    )',
                    'Modulo.id' => 14
                )
                    )
            );
        }

        $meses = array();
        setlocale(LC_ALL, 'es_VE.UTF-8');
        $t1 = strtotime(date('Y-m-01'));
        for ($j = 0; $j < 13; $j++) {
            $t = strtotime("-$j months", $t1);
            $meses[strval($t)] = ucwords(strftimeAW('%B %Y', $t));
        }

        //Listar perfiles de usuario
        $master = false;
        if (!empty($usuario['rol_juridico_id']) && $usuario['rol_juridico_id'] == 1) {
            $master = true;
            $roles = array();
            $perfiles = $this->RolJuridico->find(
                    'all', array(
                    'fields' => array('id', 'nombre'),
                    'conditions' => array(
                    "id in (select rol_juridico_id from usuarios where rif = '" . $usuario['rif'] . "' and tipo_rif = '" . $usuario['tipo_rif'] . "' group by rol_juridico_id)"
                )
                    )
            );

            $roles = array();
            foreach ($perfiles as $nombrePerfil) {
                if (!count($roles))
                    $roles['t'] = 'Todos los Perfiles';
                $roles[$nombrePerfil['RolJuridico']['id']] = $nombrePerfil['RolJuridico']['nombre'];
            }
        }

        $this->set(compact('modulos', 'transacciones', 'master', 'roles', 'meses'));
    }

    /**
     * Accion para el historico de conexiones
     */
    function historico_conexiones() {

        $this->layout = 'interna';

        $this->set('seccion_actual', 'pago_activo.historico.conexiones');

        // Genera los ultimos 12 meses anteriores
        $meses = array();
        setlocale(LC_ALL, 'es_VE.UTF-8');
        $t1 = strtotime(date('Y-m-1'));

        for ($i = 0; $i < 12; $i++) {
            $t = strtotime("-$i months", $t1);
            $meses[strval($t)] = ucwords(strftimeAW('%B %Y', $t));
        }

        $usuario = $this->Session->read('usuarios.datos');

        $condiciones = array(
            'usuario_id' => $usuario['id'],
            'canal' => Configure::read('canal.pa'),
            'Bitacora.transaccion_id' => array(1, 2, 26, 44, 45, 46)
        );

        if (!empty($this->data['HistoricoTransacciones']['mes'])) {
            $t1 = date('Y-m-1', $this->data['HistoricoTransacciones']['mes']);
            $t2 = date('Y-m-1', strtotime('+1 months', $this->data['HistoricoTransacciones']['mes']));

            $condiciones['Bitacora.fecha_hora >='] = $t1;
            $condiciones['Bitacora.fecha_hora <'] = $t2;
        } else {
            $condiciones['Bitacora.fecha_hora >'] = date('Y-m-d H:i:s', $t);
        }

        $total_registros = $this->Bitacora->find(
            'count', array(
                'conditions' => $condiciones
            )
        );

        $offset = 0;

        if (
                isset($this->params['form']['offset']) &&
                preg_match('/^\d+$/', $this->params['form']['offset'])
        ) {
            $offset = intval($this->params['form']['offset']);
        }

        $resultados = $this->Bitacora->find(
                'all', array(
            'conditions' => $condiciones,
            'contain' => array('Transaccion' => 'Modulo'),
            'order' => 'Bitacora.fecha_hora desc',
            'offset' => $offset,
            'limit' => Configure::read('Tamano.paginacion')
                )
        );

        $this->set(compact('resultados', 'total_registros', 'meses'));
    }

    /**
     * Accion que genera el contrato de Pago Activo P2P
     *
     */
    function contrato_pago_activo() {
        // Validamos que el usuario tenga acceso al modulo
        if (!validarTransaccion(array(100064)))
            $this->redirect('/consultas/integral');

        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->check('pago_activo.afiliacion_crear');

        if (!isset($this->data)) {
            $this->redirect('afiliacion_crear_confirmacion');
        }

        $contrato_data['nombre'] = desencriptar($usuario['nombre']);

        $this->set('contrato_data', $contrato_data);
    }

    /**
     * Accion que genera el contrato de Pago Activo P2C
     *
     */
    function contrato_pago_activo_j() {
        // Validamos que el usuario tenga acceso al modulo
        if (!validarTransaccion(array(100064)))
            $this->redirect('/consultas/integral');

        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->check('pago_activo.afiliacion_crear');

        if (!isset($this->data)) {
            $this->redirect('afiliacion_crear_confirmacion');
        }
    }
    
    /**
     * Accion que genera el contrato de Pago Activo C2P
     *
     */
    function contrato_pago_activo_c2p() {
        // Validamos que el usuario tenga acceso al modulo
        if (!validarTransaccion(array(100064)))
            $this->redirect('/consultas/integral');

        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->check('pago_activo.afiliacion_crear');

        if (!isset($this->data)) {
            $this->redirect('afiliacion_crear_confirmacion');
        }
    }

    /**
     * Accion para exportar movimientos de pago activo
     * */
    function exportar() {

        $this->layout = 'interna';

        $this->set('seccion_actual', 'pago_activo.transacciones');

        if (isset($this->data['DetalleProducto'])) {
            $this->DetalleProducto->set($this->data['DetalleProducto']);

            if (!$this->DetalleProducto->validates(array('fieldList' => array('nro_cuenta')))) {
                $this->redirect('index');
            }

            $this->data['ExportarPagoActivo']['nro_cuenta'] = $this->data['DetalleProducto']['nro_cuenta'];
        } else if (!empty($this->data['ExportarPagoActivo'])) {
            $this->ExportarCuenta->set($this->data);

            $fields = array(
                'campos',
                'formato',
                'configuraciones',
                'divisionCampos'
            );

            if (0 < count(array_intersect(array('m', 's'), $this->data['ExportarPagoActivo']['campos']))) {
                $fields[] = 'formatoMonto';
            }

            if (in_array('f', $this->data['ExportarPagoActivo']['campos'])) {
                $fields[] = 'formatoFecha';
            }

            if (in_array('fp', $this->data['ExportarPagoActivo']['campos'])) {
                $fields[] = 'formatoFecha';
            }

            if ($this->ExportarCuenta->validates(array('fieldList' => $fields))) {
                $parametros_consulta = $this->Session->read('pago_activo.ultima_consulta');

                $this->Session->write('pago_activo.ultima_consulta', $parametros_consulta);

                $res = $this->Activo->invoca(
                        'STMRDSJV5', $parametros_consulta
                );

                $totales = array(
                    'ABONOS' => 0,
                    'CREDITOS' => 0
                );

                $arrData = array();

                // Verifica que el webservice traiga registros
                if (!empty($res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'])) {
                    $arrData = $res['STMRDSJV5Return']['STMSDSJV5']['STMSDSJV5'];
                    normaliza($arrData);
                    $arrData = array_reverse($arrData);
                }

                // Totales
                if (count($arrData)) {

                    $contador = 0;

                    foreach ($arrData as $i) {
                        if ($i['STMDCC'] == '5') {
                            $totales['ABONOS']+=$i['STMAMT'];
                        } elseif ($i['STMDCC'] == '0') {
                            $totales['CREDITOS']+=$i['STMAMT'];
                        }
                    }
                }

                $movimientos = array();

                if (is_array($this->data['ExportarPagoActivo']['campos'])) {
                    for ($i = 0; $i < count($arrData); $i++) {
                        for ($c = 0; $c < count($this->data['ExportarPagoActivo']['campos']); $c++) {
                            $campo = $this->data['ExportarPagoActivo']['campos'][$c];

                            switch ($campo) {
                                case 'm':
                                    $movimientos[$i][$c] = formato_monto_exportar($this->data['ExportarPagoActivo']['formatoMonto'], $arrData[$i]['STMAMT']);

                                    if ($this->data['ExportarPagoActivo']['formato'] == 'e')
                                        $movimientos[$i][$c] .= ' ';
                                    else
                                        $movimientos[$i][$c] .= '    ';

                                    break;
                                case 'f':
                                    $movimientos[$i][$c] = formato_fecha_exportar($this->data['ExportarPagoActivo']['formatoFecha'], trim($arrData[$i]['STMNA8']), trim($arrData[$i]['STMNA7']), trim($arrData[$i]['STMNA6']));
                                    break;
                                case 'r':
                                    $movimientos[$i][$c] = strval($arrData[$i]['STMCKN']);

                                    if ($this->data['ExportarPagoActivo']['formato'] == 'e')
                                        $movimientos[$i][$c] .= ' ';
                                    else
                                        $movimientos[$i][$c] .= '    ';

                                    break;
                                case 'd':
                                    $movimientos[$i][$c] = trim($arrData[$i]['STMNAR']);
                                    break;
                                case 't':
                                    if ($arrData[$i]['STMDCC'] == '0') {
                                        $movimientos[$i][$c] = 'DEBITO';
                                    } elseif ($arrData[$i]['STMDCC'] == '5') {
                                        $movimientos[$i][$c] = 'CREDITO';
                                    }
                                    break;
                            }
                        }
                    }
                }

                $this->set(compact('movimientos'));

                switch ($this->data['ExportarPagoActivo']['formato']) {
                    case 't':
                        $this->layout = 'nada';
                        $this->render('exportar_txt');
                        break;
                    case 'e':
                        $this->render('exportar_excel');
                        break;
                    case 's':
                        break;
                }
            }
        } else {
            $this->redirect('index');
        }
    }

    /**
     * Accion para el historico de transacciones P2P - C2P
     * @param String $volver
     */
    function qr($volver = '') {
        $usuario = $this->Session->read('usuarios.datos');
        $datosbasicos=$this->Session->read('usuarios.nibpf3010');

        if (!validarTransaccion(array(101104)))
            $this->redirect('/consultas/integral');

        $rutaImagen = '';  
        if(isset($this->data['qrForm'])){

            $rutaImagen = $this->data['qrForm']['codigo_qr'];
            $this->Session->write('qr.suiche',$rutaImagen);

            // Registra la creacion del qr en el log
            $this->Bitacora->registraBitacora($usuario['id'], 101104, null, null, null, null, "Creacion de Codigo QR");

        }

        if ($usuario['tipo_rif']) { // PARA JURIDICO
            if($usuario['rol_juridico_id'] == '2'){
                $conditions = array(
                    'tipo_rif' => $usuario['tipo_rif'],
                    'rif' => $usuario['rif'],
                    'usuario_id' => $usuario['id']
                );
            }else{
                $conditions = array(
                    'tipo_rif' => $usuario['tipo_rif'],
                    'rif' => $usuario['rif']
                );
            }
            
            $listCanales = array("5","7");


          
        } else { // PARA NATURAL
            $conditions = array(
                'Afiliacion.usuario_id' => $usuario['id']
            );
            $listCanales = array("5");


        }

        $conditions = array_merge($conditions, array('Afiliacion.tipo_afiliacion' => 'm'));

        $total_registros = $this->Afiliacion->find(
            'count', array('conditions' => $conditions)
        );

        $offset = 0;

        if (
            isset($this->data['Afiliacion']['offset']) &&
            preg_match('/^\d+$/', $this->data['Afiliacion']['offset'])
        ) {
            $offset = intval($this->data['Afiliacion']['offset']);
        }
        
        $listado = $this->Afiliacion->find(
            'all', array(
                'conditions' => $conditions,
                'order' => 'Afiliacion.id desc',
                'offset' => $offset,
                'limit' => Configure::read('Tamano.paginacion')
            )
        );

        if (empty($listado)) {

            $this->Bitacora->registraBitacora($usuario['id'], 101104, null, null, null, null, "Error - Creacion de Codigo QR");
            
            $error = 1;
            $this->redirect(array('controller'=>'/pago_activo/qr_fallida',  'action' => $error));
        }

        if (!empty($listado[0]['Afiliacion']['tipo_rif'])) {
            $nombre_cliente = $datosbasicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
            $tipo_doc = $listado[0]['Afiliacion']['tipo_rif'];
            $num_doc = desencriptar($listado[0]['Afiliacion']['rif']);
        }else{
            $nombre_cliente = $datosbasicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
            $tipo_doc = $listado[0]['Afiliacion']['tipo_cedula'];
            $num_doc = desencriptar($listado[0]['Afiliacion']['cedula']);
        }
        
        $cod_tlf = substr($listado[0]['Afiliacion']['codigo_telefono'],1);
        $tlf = desencriptar($listado[0]['Afiliacion']['telefono']);
		$canal = (!empty($canal)) ? $canal : "5";
        //$name = str_replace("," , "" , $nombre_cliente);
        //limpiar nombre de caracteres especiales
        $nombre_cliente = strtoupper($nombre_cliente);
        $name = trim($this->limpiarCadena($nombre_cliente));

        $id =  $tipo_doc . $num_doc;
        $phone = "58" . $cod_tlf . $tlf;

        // $this->layout = 'suiche_qr';
        $this->layout = 'interna';
        $this->set('seccion_actual', 'pago_activo.qr');
        $this->set(compact('name','id','phone','canal','total_registros','listado', 'rutaImagen'));


    }

    /**
     * Accion para desacargar pdf de QR
     * @param String $volver
     */
    function qr_pdf($qr) {
        $usuario = $this->Session->read('usuarios.datos');
        $datosbasicos=$this->Session->read('usuarios.nibpf3010');        
        $datosqr = $this->Session->read('qr.suiche');

        if (!empty($usuario['tipo_rif'])) {
            $tipo_doc = $usuario['tipo_rif'];
            $num_doc = desencriptar($usuario['rif']);
        }else{
            $tipo_doc = $usuario['tipo_cedula'];
            $num_doc = desencriptar($usuario['cedula']);
        }

        $id = $tipo_doc . $num_doc;
        $num_telefono = $datosbasicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR'];

        $this->set(compact('contrato_data', $contrato_data, $datosqr, 'datosqr', 'usuario', 'datosbasicos','num_telefono', 'id'));

    }

    /**
     * Accion para mostrar error al generar QR
     * @param String $volver
     */
    function qr_fallida($error) {
        
        if ($error == 1) {
            $descripcion = "Estimado cliente usted no se encuentra afiliado en el servicio de Pago Movil";
        }

        $this->layout = 'interna';
        $this->set(compact('descripcion'));

    }


    function lb_pago(){
        $this->layout = 'interna';
        $descripcion = _m('lb.acceso');
        $this->set(compact('descripcion'));
    }

    function valListaCed($tipo, $docu, $banco, $monto){
        $swEncontrada = 0;
        $resp = $this->LbSeguridad->consultarCedula($tipo, $docu);
        
        if($resp){
            $usuario = $this->Session->read('usuarios.datos');
            $cedCliente = $usuario['tipo_cedula'] . desencriptar($usuario['cedula']);
            $cedClienteEnc = encriptar($cedCliente);
            $cedBene = $tipo . $docu;
            $cedBeneEnc = encriptar($cedBene);  
            $fecha = date("Y-m-d H:i:s");
            $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            
            //Guardar informacion en el log
            $data = array('ced_cliente'=>$cedClienteEnc, 
                          'ced_beneficiario'=>$cedBeneEnc,
                          'fecha'=>$fecha,
                          'operacion'=>'PA',
                          'ip_cliente'=>$ip
            );
            $res = $this->LbLog->guardar_log($data);
            $swEncontrada = 1; 

            //enviar correo control
            $correos = _p('lb.correo');
            $arrgCorreo = explode(',', $correos);

            foreach($arrgCorreo as $emailLB){
                //Email Ordenante
                // Email Options
                $this->Email->smtpOptions = Configure::read('email.smtp');
                $this->Email->delivery = 'smtp';
                $this->Email->from = Configure::read('email.from');
                $this->Email->to = $emailLB;
                $this->Email->subject = 'Notificacion Lista Seguridad';
                $this->Email->sendAs = 'html';

                // Informacion del correo
                $this->Email->template = 'lb_correo_lb';
                $tipo_transaccion = 'Pago Activo';
                $tipo_accion = 'Pago Activo';
                $this->set(compact('usuario', 'tipo_transaccion', 'tipo_accion', 'cedCliente', 'cedBene', 'banco','monto','ip'));
                // Envio del correo
                $this->Email->send();
            } 
        }    
        return $swEncontrada;
    }    


function limpiarCadena($cadena) {

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
    $cadena = str_replace('Ñ', 'N', $cadena);
    $cadena = str_replace('?', '', $cadena);    
    $cadena = str_replace('¿', '', $cadena);
    $cadena = str_replace('.', '', $cadena);
    $cadena = str_replace(',', '', $cadena);
    $cadena = str_replace(';', '', $cadena);
    $cadena = str_replace('  ', ' ', $cadena);


    
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


    function transaccion_advertencia()
    {

        if (!$this->Session->check('transaccion.advertencia')) $this->redirect('/consultas/integral');

        $this->data = $this->Session->read('transaccion.advertencia');

        $this->layout = 'interna';

        $this->data['mensaje_alerta'] = 'pagotdc.alerta';

        $this->set('seccion_actual', $this->data['seccion_actual']);

        $this->render('/elements/transaccion_advertencia');
    }



}

?>
