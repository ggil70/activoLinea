<?php

/**
 * Controlador para las operaciones de transferencias electrónicas.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */

/**
 * Operaciones de transferencias electrónicas.
 * 
 */
class TransferenciasController extends AppController {

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
        'TransaccionJuridicoDetalle',
        'TransaccionSimf',
        'PrefijosTelefonicos',
        'LbLog',
        'LbSeguridad'
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
    var $helpers = array('Activo', 'Html', 'Session', 'Form', 'Paginator', 'Text', 'Javascript');

    /**
     * Método inicial para realizar transferencias a cuentas propias. Busca la 
     * información de las cuentas.
     * 
     * @param string $volver Indica si está regresando de la confirmación
     */
    function propias($volver = null) {
        /* Para validar el acceso al modulo */
        if (!validarTransaccion(100021))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $this->Session->delete('usuarios.descarga.pdf');

        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.propias');

        if (!empty($this->data['Transferencia'])) {
            $usuario = $this->Session->read('usuarios.datos');

            // Para usuario jurídicos operadores, obtener su configuración para este tipo de transacción
            if (
                    $usuario['rol_juridico_id'] == 3 ||
                    $usuario['rol_juridico_id'] == 5
            ) {
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                        $usuario['id'], 100021, $this->data['Transferencia']['cuentaDebitar']
                );

                if (!$transaccion_detalle) {
                    $this->Transferencia->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                }
            }

            if (empty($this->Transferencia->validationErrors)) {
                if (isset($transaccion_detalle)) {
                    $this->data['Transferencia']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if (is_numeric($transaccion_detalle['limite'])) {
                        $this->data['Transferencia']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['Transferencia']['monto_limite_usuario'] = '';
                    }
                }

                $this->Transferencia->set($this->data);

                if ($this->Transferencia->validates(array('fieldList' => array('cuentaDebitar', 'cuentaCreditar', 'monto')))) {
                    $this->Session->write('transferencias.propias', $this->data);
                    $this->redirect('propias_confirmacion');
                }
            }
        } else if (isset($volver) && $this->Session->check('transferencias.propias')) {
            $this->data = $this->Session->read('transferencias.propias');
        }


        $cuentas = $this->Activo->buscarCuentasSaldos();
        $cta_acreditar = $this->Activo->buscarCuentasSaldos(true);

        $this->set(compact('cuentas', 'cta_acreditar'));
    }

    /**
     * Método para realizar la confirmación de la transferencia a cuentas propias.
     */
    function propias_confirmacion() {

        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.propias');

        if ($this->Session->check('transferencias.propias')) {
            $this->data = $this->Session->read('transferencias.propias');
        } else {
            $this->redirect('propias');
        }
    }

    /**
     * Método para realizar el proceso de transferencia a cuentas propias.
     */
    function propias_resultado() {

        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.propias');

        if ($this->Session->check('transferencias.propias')) {
            $this->data = $this->Session->read('transferencias.propias');

            // Validación anti fraude
            $cuentas = $this->Activo->buscarCuentas();

            if (
                    !in_array($this->data['Transferencia']['cuentaDebitar'], array_keys($cuentas))
            ) {
                $this->redirect('/consultas/integral');
            }
            //  Fin validación anti fraude

            $usuario = $this->Session->read('usuarios.datos');

            $cuentaDebitar = substr($this->data['Transferencia']['cuentaDebitar'], 10);
            $cuentaCreditar = substr($this->data['Transferencia']['cuentaCreditar'], 10);
            $cuentaDebitarA = $this->data['Transferencia']['cuentaDebitar'];
            $cuentaCreditarA = $this->data['Transferencia']['cuentaCreditar'];

            $datos_basicos = $this->Session->read('usuarios.nibpf3010');
            $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'], 10, '0', STR_PAD_LEFT);


            //Validar Lista negra

                $swEncontrada = 0;  
                $tipoDocumento =  trim($usuario["tipo_cedula"]);
                $cedulaLB = desencriptar($usuario['cedula']);
                $cedulaLB = trim($cedulaLB);
                $cedulaLB = $cedulaLB * 1;
                $bancoBenef = '0171';
                $monto = formatea_monto_toWs($this->data['Transferencia']['monto']);

                $swEncontrada = $this->valListaCed($tipoDocumento, $cedulaLB, $bancoBenef, $monto);

                if($swEncontrada == 1){
                    $ruta = 'lb_transferencia';
                    $this->redirect($ruta);                    
                }
            //Fin Validar lista negra

            // Identificar tipo de usuario (natural o jurídico)

            if (
                    empty($usuario['tipo_rif']) ||
                    ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) // Master único
            ) {
                // Usuario natural o jurídico único

                $res = $this->Activo->invoca(
                        'INTRFDSJV', array(
                            'req' => array(
                                'INTUSR' => $identificacion,
                                'INTFRMACC' => $cuentaDebitar, //de
                                'INTTOACC' => $cuentaCreditar, //para
                                'INTTRFAMT' => formatea_monto_toWs($this->data['Transferencia']['monto']),
                                'INTTYPE' => '0',
                                'canal' => Configure::read('canal.ib'),
                                'operacion' => 'p',
                                'INTIPACT' => Configure::read('ip.client'),
                                'INTUSER' => Configure::read('as400.user'),
                                'INTPASWD' => Configure::read('as400.pwd')
                            )
                        )
                );

                $referencia = $res['INTRFDSJVReturn']['error']['ERDF01'];

                $error = "-1";
                $descripcion = "Error inesperado, transacción no disponible.";

                if (isset($res['INTRFDSJVReturn'])) {
                    $error = intval($res['INTRFDSJVReturn']['error']['ERRNUM']);
                    $error = ($error == 0) ? $error : intval($res['INTRFDSJVReturn']['error']['ERNU01']);
                    $descripcion = $res['INTRFDSJVReturn']['error']['ERNU01'] . ' - ' . $res['INTRFDSJVReturn']['error']['ERDS01'];
                }

                if ($error == 0) {

                    $this->Bitacora->registraBitacora(
                            $usuario['id'], 100021, $cuentaDebitarA, $cuentaCreditarA, $this->data['Transferencia']['monto'], $referencia, NULL, 0
                    );

                    $datos_basicos = $this->Session->read('usuarios.nibpf3010');

                    if ($usuario['tipo_rif']) {
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
                    $this->data['Transferencia']['descripcion'] = ' - ';
                    $this->data['Transferencia']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';
                    // Genera la data para la descarda de pdf
                    $this->Session->write('usuarios.descarga.pdf', $this->data['Transferencia']);
                } else {
                    $this->log('Transferencias Propias - ' . $descripcion, LOG_ERR);
                    $this->Bitacora->registraBitacora(
                            $usuario['id'], 100021, $cuentaDebitarA, $cuentaCreditarA, $this->data['Transferencia']['monto'], NULL, $descripcion, 1
                    );
                }

                $this->set('pendiente', false);
            } else if (
                    $usuario['rol_juridico_id'] == 3 || // Operador
                    $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
            ) {
                // En caso de jurídico, sólo los operadores y operadores/aprobadores pueden
                // generar una transferencia. En cuyo caso, se deja pendiente por aprobar

                $error = 0;

                $res = $this->TransaccionPendiente->save(
                    array(
                        'TransaccionPendiente' => array(
                            'usuario_id' => $usuario['id'],
                            'tipo' => 'tpm',
                            'estatus' => 'pa',
                            'fecha_hora_registro' => date('Y-m-d H:i:s'),
                            'monto' => formatea_monto_toWs($this->data['Transferencia']['monto']),
                            'aprobaciones_requeridas' => $this->data['Transferencia']['aprobaciones_requeridas'],
                            'tipo_rif' => $usuario['tipo_rif'],
                            'rif' => $usuario['rif'],
                            'cuenta_origen' => encriptar($this->data['Transferencia']['cuentaDebitar']),
                            'cuenta_destino' => encriptar($this->data['Transferencia']['cuentaCreditar']),
                            'descripcion' => encriptar(' - '),
                            'nombre_beneficiario' => $usuario['nombre'],
                            'tipo_id_beneficiario' => $usuario['tipo_rif'],
                            'id_beneficiario' => $usuario['rif'],
                            'email_beneficiario' => $usuario['email_principal']
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

            $this->set(compact('error', 'referencia'));

            $this->Session->delete('transferencias.propias');
        } else {
            $this->redirect('propias');
        }
    }

    /**
     * Método inicial para realizar transferencias a cuentas de terceros.
     * 
     * @param string $volver Indica si está regresando de la confirmación
     */
    function terceros($volver = null) {
        /* Para validar el acceso al modulo */
        if (!validarTransaccion(100022))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.terceros');

        if (!empty($this->data['Transferencia'])) {
            $usuario = $this->Session->read('usuarios.datos');

            // Para usuario jurídicos operadores, obtener su configuración para este tipo de transacción
            if (
                    $usuario['rol_juridico_id'] == 3 ||
                    $usuario['rol_juridico_id'] == 5
            ) {
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                        $usuario['id'], 100022, $this->data['Transferencia']['cuentaDebitar']
                );

                if (!$transaccion_detalle) {
                    $this->Transferencia->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                }
            }

            if (empty($this->Transferencia->validationErrors)) {
                if (isset($transaccion_detalle)) {
                    $this->data['Transferencia']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];

                    if (is_numeric($transaccion_detalle['limite'])) {
                        $this->data['Transferencia']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['Transferencia']['monto_limite_usuario'] = '';
                    }
                }

                $this->Transferencia->set($this->data);

                if ($this->Transferencia->validates(array('fieldList' => array('cuentaDebitar', 'tipoDestinatario')))) {

                    $this->Session->write('transferencias.terceros', $this->data);

                    if ($this->data['Transferencia']['tipoDestinatario'] == 'a') {
                        $this->redirect('terceros_afiliados');
                    } else {
                        $this->redirect('terceros_noafiliados');
                    }
                }
            }
        } elseif (isset($volver) && $this->Session->check('transferencias.terceros')) {
            $this->data = $this->Session->read('transferencias.terceros');
        }

        $cuentas = $this->Activo->buscarCuentasSaldos();

        $this->set(compact('cuentas'));
    }

    /**
     * Método para la búsqueda de los afiliados para realizar la transferencia 
     * a cuentas de bancoactivo.
     * 
     */
    function terceros_afiliados() {
        if (!$this->Session->check('transferencias.terceros'))
            $this->redirect('terceros');

        $estado = $this->Session->read('transferencias.terceros');

        $usuario = $this->Session->read('usuarios.datos');

        // Obtener el límite de parámetros
        if (empty($usuario['rif'])) {
            $limite_config = _p('limite_internas_terceros');
        } else {
            $limite_config = _p('limite_internas_terceros_j');
        }

        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $monto_limite = '';
        $acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id']);
        $limite_total = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : $limite_config;

        if ($limite_total) {
            $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_internas_terceros']);
        }
        $limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);

        if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
            $monto_limite = $limite_total = '';
        }
        $this->set(compact('monto_limite'));
        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $this->set('seccion_actual', 'transf.terceros');

        if (empty($this->data['Transferencia'])) {
            $this->data = $estado;
        } else if (!isset($this->data['Transferencia']['paginando'])) {
            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->data['Transferencia']['monto_limite'] = $monto_limite;
            $this->data['Transferencia']['limite_total'] = $limite_total;

            if (isset($estado['Transferencia']['monto_limite_usuario'])) {
                $this->data['Transferencia']['monto_limite_usuario'] = $estado['Transferencia']['monto_limite_usuario'];
            }
            $this->data['Transferencia']['alias'] = $this->sanitize_input($this->data['Transferencia']['alias']);
            $this->data['Transferencia']['descripcion'] = $this->sanitize_input($this->data['Transferencia']['descripcion']);

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->Transferencia->set($this->data);

            if ($this->Transferencia->validates(
                            array(
                                'fieldList' => array(
                                    'cuentaCreditar',
                                    'monto'
                                )
                            )
                    )) {
                $productoValidar = substr($this->data['Transferencia']['cuentaCreditar'], -10);
                $datosProducto = array('NROCUENTA' => str_pad($productoValidar, 12, '0', STR_PAD_LEFT), 'NROPARAM' => 2);
                $datos = $this->Activo->validarProducto($datosProducto);
                $error = intval($datos['NIBPF3010Return']['error']['ERNU01']);

                if (empty($usuario['tipo_rif'])) {
                    $tipo_id = $usuario['tipo_cedula'];
                    $nro_id = desencriptar($usuario['cedula']);
                } else {
                    $tipo_id = $usuario['tipo_rif'];
                    $nro_id = desencriptar($usuario['rif']);
                }

                if ($error != 0) {
                    $this->Transferencia->validationErrors['cuentaCreditar'] = _m('valor.invalido', 'el número de cuenta  a acreditar');
                } elseif (strtolower(trim($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) == strtolower($tipo_id . str_pad($nro_id, 9, '0', STR_PAD_LEFT))) {
                    $this->Transferencia->validationErrors['cuentaCreditar'] = _m('producto.propio');
                } elseif ($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] != 'A' && $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] != 'T') {

                    if ($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] == 'O') {
                        $this->Transferencia->validationErrors['cuentaCreditar'] = _m('cuenta.controlada', 'con la cuenta');
                    } else {
                        $this->Transferencia->validationErrors['cuentaCreditar'] = _m('cuenta.inactiva', 'la cuenta del beneficiario');
                    }
                } else {

                    $this->data['Transferencia']['afiliar'] = 's'; // Para afiliar alias
                    if (isset($estado['Transferencia']['aprobaciones_requeridas']))
                        $this->data['Transferencia']['aprobaciones_requeridas'] = $estado['Transferencia']['aprobaciones_requeridas'];

                    if (_p('sms.otp.activo') == '0') {
                        $validacion_otp['validado'] = true;
                    } else {
                        $validacion_otp = $this->Session->read('seguridad.validar_otp');
                    }

                    if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
                        if (_p('sms.otp.activo') == '0') {
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
                                'Código de Autorización',
                                'Resultado de Operación'
                            );
                            $this->data['paso_final'] = 3;
                        }
                    } else {
                        $this->data['pasos'] = array(
                            'Solicitud de Información',
                            'Confirmación',
                            'Resultado de Operación'
                        );
                        $this->data['paso_final'] = 2;
                    }

                    $this->Session->write('transferencias.terceros', $this->data);

                    $this->Session->delete('transaccion.advertencia');

                    // En caso de ser un usuario natural o master unico
                    if (empty($usuario['rol_juridico_id']) || ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6)) {
                        if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Transferencia'], 100022)) {

                            $this->data['titulo'] = 'Transferencia Tercero en Activo';
                            $this->data['mensaje_alerta'] = 'transferencia.alerta';
                            $this->data['url_volver'] = '/transferencias/terceros_afiliados';
                            $this->data['url_continuar'] = '/transferencias/terceros_confirmacion';
                            $this->data['seccion_actual'] = 'transf.terceros';

                            $this->Session->write('transaccion.advertencia', $this->data);
                            $this->redirect('transaccion_advertencia');
                        }
                    }

                    $this->redirect('terceros_confirmacion');
                }
            }
        }

        $afiliados = $this->Afiliacion->obtenerListaAfiliados($usuario['id'], 't', 't');


        if ($afiliados) {
            // Empieza código para paginar
            $total_registros = count($afiliados);
            $offset = 0;

            if (
                    isset($this->data['Transferencia']['offset']) &&
                    preg_match('/^\d+$/', $this->data['Transferencia']['offset'])
            ) {
                $offset = intval($this->data['Transferencia']['offset']);
            }

            $resultados = array_slice(
                    $afiliados, $offset, 5, true
            );
            // Fin código para paginar
        }

        $this->set(compact('afiliados', 'resultados', 'total_registros'));

        $this->layout = 'interna';
    }

    /**
     * Método para el formulario de afiliación del beneficiario de la transferencia
     * a cuentas de bancoactivo.
     * 
     */
    function terceros_noafiliados() {

        if (!$this->Session->check('transferencias.terceros'))
            $this->redirect('terceros');

        $estado = $this->Session->read('transferencias.terceros');

        $usuario = $this->Session->read('usuarios.datos');

        // Obtener el límite de parámetros
        if (empty($usuario['rif'])) {
            $limite_config = _p('limite_internas_terceros');
        } else {
            $limite_config = _p('limite_internas_terceros_j');
        }

        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $monto_limite = '';
        $acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id']);
        $limite_total = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : $limite_config;
        if ($limite_total) {
            $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_internas_terceros']);
        }
        $limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);

        if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
            $monto_limite = $limite_total = '';
        }
        $this->set(compact('monto_limite', 'limite_total'));
        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        // para los limites

        $this->set('seccion_actual', 'transf.terceros');

        if (empty($this->data['Transferencia'])) {
            $this->data = $estado;
        } else if (!preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/', $this->data['Transferencia']['cuentaCreditar'])) {
            $this->Transferencia->validationErrors['cuentaCreditar'] = _m('valor.invalido', 'el número de cuenta a acreditar');
        } else {
            $productoValidar = substr($this->data['Transferencia']['cuentaCreditar'], -10);

            $datosProducto = array('NROCUENTA' => str_pad($productoValidar, 12, '0', STR_PAD_LEFT), 'NROPARAM' => 2);
            $datos = $this->Activo->validarProducto($datosProducto);
            $error = intval($datos['NIBPF3010Return']['error']['ERNU01']);

            if (empty($usuario['tipo_rif'])) {
                $tipo_id = $usuario['tipo_cedula'];
                $nro_id = desencriptar($usuario['cedula']);
            } else {
                $tipo_id = $usuario['tipo_rif'];
                $nro_id = desencriptar($usuario['rif']);
            }

            if ($error != 0) {
                $this->Transferencia->validationErrors['cuentaCreditar'] = _m('valor.invalido', 'el número de cuenta a acreditar');
            } elseif ($datos['NIBPF3010Return']['NIBPF3010']['MONEDA'] != 'VEB') {
                $this->Transferencia->validationErrors['cuentaCreditar'] = _m('pago.nopermitido');
            } elseif ($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] != 'A' && $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] != 'T') {

                if ($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] == 'O') {
                    $this->Transferencia->validationErrors['cuentaCreditar'] = _m('cuenta.controlada', 'con la cuenta del beneficiario');
                } else {
                    $this->Transferencia->validationErrors['cuentaCreditar'] = _m('cuenta.inactiva', 'la cuenta del beneficiario');
                }
            } elseif (strtolower(trim($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) == strtolower($tipo_id . str_pad($nro_id, 9, '0', STR_PAD_LEFT))) {
                $this->Transferencia->validationErrors['cuentaCreditar'] = _m('producto.propio');
            } else {
                // Completar el tamaáo de la cuenta
                if (10 == strlen($this->data['Transferencia']['cuentaCreditar'])) {
                    $res = $this->Activo->completaCuenta($this->data['Transferencia']['cuentaCreditar']);

                    if ($res) {
                        $this->data['Transferencia']['cuentaCreditar'] = str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'], 20, '0', STR_PAD_LEFT);
                    }
                }

                $cedula = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'], 1, 9);

                $this->data['Transferencia']['tipoCedulaRif'] = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'], 0, 1);

                if (
                        $this->data['Transferencia']['tipoCedulaRif'] == 'G' ||
                        $this->data['Transferencia']['tipoCedulaRif'] == 'J'
                ) {
                    $this->data['Transferencia']['nombreBeneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                } else {
                    $this->data['Transferencia']['nombreBeneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'] . ' ' . $datos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'];
                }

                $this->data['Transferencia']['nroCedulaRif'] = $cedula;

                /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
                $this->data['Transferencia']['monto_limite'] = $monto_limite;

                if (empty($usuario['rif'])) {
                    $this->data['Transferencia']['limite_total'] = $limite_total;
                } else {
                    $this->data['Transferencia']['monto_maximo'] = $limite_total;
                }

                if (isset($estado['Transferencia']['monto_limite_usuario'])) {
                    $this->data['Transferencia']['monto_limite_usuario'] = $estado['Transferencia']['monto_limite_usuario'];
                }

                /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
                $this->Transferencia->set($this->data);

                if ($this->Transferencia->validates(
                                array(
                                    'fieldList' => array(
                                        'cuentaCreditar',
                                        'emailBeneficiario',
                                        'monto',
                                        'descripcion',
                                        'alias',
                                        'afiliar',
                                        'monto_maximo',
                                    )
                                )
                        )) {
                    if (
                            $this->data['Transferencia']['afiliar'] == 's' &&
                            $this->Afiliacion->checkAfiliacion($usuario['id'], $this->data['Transferencia']['cuentaCreditar'])
                    ) {
                        $this->Transferencia->validationErrors['cuentaCreditar'] = 'producto_nodisponible';
                    } else if (
                            isset($this->data['Transferencia']['alias']) &&
                            $this->data['Transferencia']['alias'] != "" && $this->Afiliacion->checkAlias($usuario['id'], $this->data['Transferencia']['alias'])
                    ) {
                        $this->Transferencia->validationErrors['alias'] = 'alias_nodisponible';
                    } else {
                        if (isset($estado['Transferencia']['aprobaciones_requeridas']))
                            $this->data['Transferencia']['aprobaciones_requeridas'] = $estado['Transferencia']['aprobaciones_requeridas'];

                        $validacion_otp = $this->Session->read('seguridad.validar_otp');

                        if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
                            $this->data['pasos'] = array(
                                'Solicitud de Información',
                                'Confirmación',
                                'Código de Autorización',
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
                        /**                         * * */
                        $this->Session->write('transferencias.terceros', $this->data);

                        $this->Session->delete('transaccion.advertencia');

                        // En caso de ser un usuario natural o master unico
                        if (empty($usuario['rol_juridico_id']) || ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6)) {
                            if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Transferencia'], 100022)) {

                                $this->data['titulo'] = 'Transferencia Tercero en Activo';
                                $this->data['mensaje_alerta'] = 'transferencia.alerta';
                                $this->data['url_volver'] = '/transferencias/terceros_noafiliados';
                                $this->data['url_continuar'] = '/transferencias/terceros_confirmacion';
                                $this->data['seccion_actual'] = 'transf.terceros';

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
    function terceros_confirmacion() {

        if (!$this->Session->check('transferencias.terceros'))
            $this->redirect('terceros');

        $estado = $this->Session->read('transferencias.terceros');

        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.terceros');

        $usuario = $this->Session->read('usuarios.datos');

        $this->set('juridico', $usuario);

        $this->data = $estado;

        if ($this->data['Transferencia']['tipoDestinatario'] == 'a') {

            $afiliado = $this->Afiliacion->obtenerDataAfiliado($usuario['id'], $this->data['Transferencia']['cuentaCreditar']);
            if (!$afiliado) {
                $this->redirect('terceros');
            }

            $this->data['Transferencia']['nombreBeneficiario'] = desencriptar($afiliado['nombre_beneficiario']);
            $this->data['Transferencia']['tipoCedulaRif'] = $afiliado['tipo_cedula'];
            $this->data['Transferencia']['nroCedulaRif'] = desencriptar($afiliado['cedula']);
            $this->data['Transferencia']['emailBeneficiario'] = desencriptar($afiliado['email']);
            $this->data['Transferencia']['id'] = $afiliado['id'];
            $this->Session->write('transferencias.terceros', $this->data);
        } else {

            $this->data['Transferencia']['id'] = null;
            $this->Session->write('transferencias.terceros', $this->data);
        }
    }

    /**
     * Método para el proceso de la transferencia a cuentas de terceros.
     * 
     */
    function terceros_resultado() {

        if (!$this->Session->check('transferencias.terceros'))
            $this->redirect('terceros');

        $estado = $this->Session->read('transferencias.terceros');

        $usuario = $this->Session->read('usuarios.datos');

        $this->data = $estado;

        // Validación anti fraude
        $cuentas = $this->Activo->buscarCuentas();

        if (
                !in_array($this->data['Transferencia']['cuentaDebitar'], array_keys($cuentas))
        ) {
            $this->redirect('/consultas/integral');
        }
        //  Fin validación anti fraude		

        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.terceros');

        if ($this->Session->check('transferencias.terceros')) {
            $this->data = $this->Session->read('transferencias.terceros');

            $usuario = $this->Session->read('usuarios.datos');
            $cuentaDebitar = substr($this->data['Transferencia']['cuentaDebitar'], 10);
            $cuentaCreditar = substr($this->data['Transferencia']['cuentaCreditar'], 10);
            $cuentaDebitarA = $this->data['Transferencia']['cuentaDebitar']; //para bitácora
            $descripcion = $this->data['Transferencia']['descripcion'];

            $this->set('juridico', $usuario['tipo_rif']);

            $datos_basicos = $this->Session->read('usuarios.nibpf3010');
            $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'], 10, '0', STR_PAD_LEFT);


            //Validar Lista negra
                $swEncontrada = 0;  
                $tipoDocumento =  trim($this->data['Transferencia']['tipoCedulaRif']);


                $cedulaLB = trim($this->data['Transferencia']['nroCedulaRif']);
                $cedulaLB = $cedulaLB * 1; 

                $bancoBenef = '0171';
                $monto = formatea_monto_toWs($this->data['Transferencia']['monto']);
      
                $swEncontrada = $this->valListaCed($tipoDocumento, $cedulaLB, $bancoBenef, $monto);
                if($swEncontrada == 1){
                    $ruta = 'lb_transferencia';
                    $this->redirect($ruta);                    
                }
            //Fin Validar lista negra


            if (
                    empty($usuario['tipo_rif']) ||
                    ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) // Master único
            ) {

                if (empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2) {

                    // $validacion_otp = $this->Session->read('seguridad.validar_otp');

                    // if (
                    //         (
                    //         $validacion_otp == null ||
                    //         !isset($validacion_otp['validado']) ||
                    //         !$validacion_otp['validado']
                    //         )
                    // ) {
                    //     if ($this->data['Transferencia']['tipoDestinatario'] == 'a') {
                    //         $url_volver = '/transferencias/terceros_afiliados';
                    //     } else {
                    //         $url_volver = '/transferencias/terceros_noafiliados';
                    //     }

                    //     $this->Session->write(
                    //             'seguridad.validar_otp', array(
                    //         'url_aceptar' => '/transferencias/terceros_resultado',
                    //         'url_volver' => $url_volver,
                    //         'layout' => 'interna',
                    //         'titulo' => 'Transferencia Tercero en Activo',
                    //         'pasos' => $estado['pasos'],
                    //         'paso_actual' => 2,
                    //         'seccion_actual' => 'transf.terceros'
                    //             )
                    //     );

                    //     $this->redirect('/seguridad/genera_otp');
                    // }
                }else{

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
                                                'url_aceptar'=>'/transferencias/terceros_resultado',
                                                'layout'=>'interna',
                                                'titulo'=>'Transferencia Tercero en Activo',
                                                'pasos'=> $estado['pasos'],
                                                'paso_actual'=>2,
                                                'seccion_actual'=>'transf.terceros'
                                        )
                                );

                                $this->redirect('/seguridad/validar_tdc');
                    }
                }






                $res = $this->Activo->invoca(
                        'INTRFDSJV', array(
                            'req' => array(
                                'INTUSR' => $identificacion,
                                'INTFRMACC' => $cuentaDebitar, //de
                                'INTTOACC' => $cuentaCreditar, //para
                                'INTTRFAMT' => formatea_monto_toWs($this->data['Transferencia']['monto']),
                                'INTTYPE' => '0',
                                'INTDESC' => $descripcion,
                                'canal' => Configure::read('canal.ib'),
                                'operacion' => 't',
                                'INTIPACT' => Configure::read('ip.client'),
                                'INTUSER' => Configure::read('as400.user'),
                                'INTPASWD' => Configure::read('as400.pwd')
                            )
                        )
                );

                $referencia = $res['INTRFDSJVReturn']['error']['ERDF01'];

                $error = -1;
                $descripcion = "";
                $error_afiliacion = false;

                if (isset($res['INTRFDSJVReturn'])) {
                    $error = intval($res['INTRFDSJVReturn']['error']['ERRNUM']);
                    $error = ($error == 0) ? $error : intval($res['INTRFDSJVReturn']['error']['ERNU01']);
                    $descripcion = $res['INTRFDSJVReturn']['error']['ERNU01'] . ' - ' . $res['INTRFDSJVReturn']['error']['ERDS01'];
                }

                if ($error == 0) {
                    /*                     * * Registrar Alias en Afiliaciones (NO ASIGNADO) ** */


                    /** ACTUALIZA EL ACUMULADO DEL DIA * */
                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', $this->data['Transferencia']['monto']);
                    /** ACTUALIZA EL ACUMULADO DEL DIA * */
                    $this->Bitacora->registraBitacora(
                            $usuario['id'], 100022, $cuentaDebitarA, $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto'], $referencia, $this->data['Transferencia']['descripcion'], 0, NULL, $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['nombreBeneficiario']
                    );

                    $this->data['Transferencia']['REFERENCIA'] = $referencia;
                    $this->data['Transferencia']['CONCEPTO'] = $this->data['Transferencia']['descripcion'];
                    $this->data['Transferencia']['TIPO'] = '';
                    $this->data['Transferencia']['NRO_BENEFICIARIO'] = $this->data['Transferencia']['cuentaCreditar'];
                    $this->data['Transferencia']['ESTATUS'] = 'EJECUTADO';
                    $this->data['Transferencia']['banco'] = 'BANCO ACTIVO BANCO UNIVERSAL, C.A.';

                    // Genera la data para la descarda de pdf
                    $this->Session->write('usuarios.descarga.pdf', $this->data['Transferencia']);

                    // Email Beneficiario
                    // Email Options
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = desencriptar($usuario['email_principal']);
                    $this->Email->subject = 'Notificacion Transferencia';
                    $this->Email->sendAs = 'html';

                    // Informacion del correo
                    $this->Email->template = 'transferencia_ordenante';
                    $tipo_accion = 'Transferencia';
                    $tipo_afiliacion = 'Terceros en Activo';
                    $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion'));

                    // Envio del correo
                    $this->Email->send();

                    // Email Options
                    $this->Email->reset();
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = $this->data['Transferencia']['emailBeneficiario'];
                    $this->Email->subject = 'Notificacion Transferencia';
                    $this->Email->sendAs = 'html';

                    // Informacion del correo
                    $this->Email->template = 'transferencia_beneficiario';
                    $tipo_accion = 'Transferencia';
                    $tipo_afiliacion = 'Terceros en Activo';
                    $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion'));


                    // Envio del correo
                    $this->Email->send();
                } else {
                    $this->log('Transferencias Terceros - ' . $descripcion, LOG_ERR);
                    $this->Bitacora->registraBitacora(
                            $usuario['id'], 100022, $cuentaDebitarA, $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto'], NULL, $descripcion, 1
                    );
                }

                if ($error != 182 && $error != 2149 && $error != 1913 && $error != -1 && $error != 223 && $error != 1912 && $error != 1616 && $this->data['Transferencia']['afiliar'] == 's') {
                    $error_afiliacion = !$this->Afiliacion->afiliar(
                                    $usuario['id'], 't', 't', $this->data['Transferencia']['alias'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id']
                    );

                    if (!$error_afiliacion && $this->data['Transferencia']['tipoDestinatario'] == 'n') {
                        // Email Options
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery = 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = desencriptar($usuario['email_principal']);
                        $this->Email->subject = 'Notificacion Afiliacion';
                        $this->Email->sendAs = 'html';

                        // Informacion del correo
                        $this->Email->template = 'afiliacion_producto';
                        $tipo_accion = 'Transferencia';
                        $tipo_afiliacion = 'Terceros en Activo';
                        $nombreBeneficiario = $this->data['Transferencia']['nombreBeneficiario'];
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                        // Envio del correo
                        $this->Email->send();

                        // Registra en bitacora
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto_maximo']
                        );
                    } else {
                        $this->Bitacora->registraBitacora($usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo'], null, $descripcion, 1);
                    }
                }

                $this->set('pendiente', false);
            } else if (
                    $usuario['rol_juridico_id'] == 3 || // Operador
                    $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
            ) {
                // En caso de jurídico, sólo los operadores y operadores/aprobadores pueden
                // generar una transferencia. En cuyo caso, se deja pendiente por aprobar

                $error = 0;

                //Solicitar TC si esta afiliando la cuenta
                if ($this->data['Transferencia']['afiliar'] == 's' && $this->data['Transferencia']['tipoDestinatario'] == 'n') {

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
                            'url_aceptar' => '/transferencias/terceros_resultado',
                            'layout' => 'interna',
                            'titulo' => 'Transferencia Tercero en Activo',
                            'pasos' => array(),
                            'paso_actual' => 2,
                            'seccion_actual' => 'transf.terceros'
                                )
                        );

                        $this->redirect('/seguridad/validar_tdc');
                    }
                }

                $res = $this->TransaccionPendiente->save(
                        array(
                    'TransaccionPendiente' => array(
                        'usuario_id' => $usuario['id'],
                        'tipo' => 'ttm',
                        'estatus' => 'pa',
                        'fecha_hora_registro' => date('Y-m-d H:i:s'),
                        'monto' => formatea_monto_toWs($this->data['Transferencia']['monto']),
                        'aprobaciones_requeridas' => $this->data['Transferencia']['aprobaciones_requeridas'],
                        'tipo_rif' => $usuario['tipo_rif'],
                        'rif' => $usuario['rif'],
                        'cuenta_origen' => encriptar($this->data['Transferencia']['cuentaDebitar']),
                        'cuenta_destino' => encriptar($this->data['Transferencia']['cuentaCreditar']),
                        'descripcion' => encriptar($this->data['Transferencia']['descripcion']),
                        'nombre_beneficiario' => encriptar($this->data['Transferencia']['nombreBeneficiario']),
                        'tipo_id_beneficiario' => $this->data['Transferencia']['tipoCedulaRif'],
                        'id_beneficiario' => encriptar($this->data['Transferencia']['nroCedulaRif']),
                        'email_beneficiario' => encriptar($this->data['Transferencia']['emailBeneficiario']),
                        'alias' => isset($this->data['Transferencia']['alias']) ? encriptar($this->data['Transferencia']['alias']) : null
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

                if ($this->data['Transferencia']['afiliar'] == 's') {

                    $error_afiliacion = !$this->Afiliacion->afiliar(
                                    $usuario['id'], 't', 't', $this->data['Transferencia']['alias'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id']
                    );

                    if (!$error_afiliacion && $this->data['Transferencia']['tipoDestinatario'] == 'n') {
                        // Email Options
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery = 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = desencriptar($usuario['email_principal']);
                        $this->Email->subject = 'Notificacion Afiliacion';
                        $this->Email->sendAs = 'html';

                        // Informacion del correo
                        $this->Email->template = 'afiliacion_producto';
                        $tipo_accion = 'Transferencia';
                        $tipo_afiliacion = 'Terceros en Activo';
                        $nombreBeneficiario = $this->data['Transferencia']['nombreBeneficiario'];
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                        // Envio del correo
                        $this->Email->send();

                        // Registra en bitacora
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto_maximo']
                        );
                    } else {
                        $this->Bitacora->registraBitacora($usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo'], null, $descripcion, 1);
                    }
                } else {
                    $error_afiliacion = false;
                }

                $this->set('pendiente', true);
            }

            $this->set(compact('error', 'error_afiliacion', 'referencia'));

            $this->Session->delete('transferencias.terceros');
        } else {
            $this->redirect('terceros');
        }
    }

    /**
     * Método inicial para realizar una transferencia a cuentas propias de 
     * otros bancos.
     * 
     * @param string $volver Indica si está regresando de la confirmación
     */
    function otros($volver = null) {

        /* Para validar el acceso al modulo */
        if (!validarTransaccion(100023))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.otros');

        if (!empty($this->data['Transferencia'])) {
            $usuario = $this->Session->read('usuarios.datos');

            // Para usuario jurídicos operadores, obtener su configuración para este tipo de transacción
            if (
                    $usuario['rol_juridico_id'] == 3 ||
                    $usuario['rol_juridico_id'] == 5
            ) {
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                        $usuario['id'], 100023, $this->data['Transferencia']['cuentaDebitar']
                );

                if (!$transaccion_detalle) {
                    $this->Transferencia->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                }
            }

            if (empty($this->Transferencia->validationErrors)) {
                if (isset($transaccion_detalle)) {
                    $this->data['Transferencia']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if (is_numeric($transaccion_detalle['limite'])) {
                        $this->data['Transferencia']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['Transferencia']['monto_limite_usuario'] = '';
                    }
                }

                $this->Transferencia->set($this->data);

                if ($this->Transferencia->validates(
                                array(
                                    'fieldList' => array(
                                        'cuentaDebitar',
                                        'tipoDestinatario'
                                    )
                        ))
                ) {
                    $this->Session->write('transferencias.otros', $this->data);

                    if ($this->data['Transferencia']['tipoDestinatario'] == 'a') {
                        $this->redirect('otros_afiliada');
                    } else {
                        $this->redirect('otros_noafiliada');
                    }
                }
            }
        } elseif (isset($volver) && $this->Session->check('transferencias.otros')) {
            $this->data = $this->Session->read('transferencias.otros');
        }

        $cuentas = $this->Activo->buscarCuentasSaldos();

        $this->set(compact('cuentas'));
    }

    /**
     * Método para la búsqueda de los afiliados para realizar la transferencia
     * a cuentas propias de otros bancos.
     * 
     */
    function otros_afiliada() {
        if (!$this->Session->check('transferencias.otros'))
            $this->redirect('otros');

        $estado = $this->Session->read('transferencias.otros');

        $usuario = $this->Session->read('usuarios.datos');

        // Obtener el límite de parámetros
        if (empty($usuario['rif'])) {
            $limite_config = _p('limite_externas');
        } else {
            $limite_config = _p('limite_externas_j');
        }
        $limite_simf =$this->Parametro->findByCodigo('alto_vSIMF');
        $limite_simf= formatea_monto_fromWs($limite_simf['Parametro']['valor']);

        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $monto_limite = '';
        $fecha_acumulado = $this->Activo->consultaFechaCompensacion();
        $acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id'], $fecha_acumulado);
        $limite_total = $usuario['limite_externas'] ? $usuario['limite_externas'] : $limite_config;

        if ($limite_total) {
            $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_externas']);
        }
        $limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);

        if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
            $monto_limite = $limite_total = '';
        }

        $this->set(compact('monto_limite', 'limite_simf'));
        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $this->set('seccion_actual', 'transf.otros');

        if (empty($this->data['Transferencia'])) {
            $this->data = $estado;
        } else if (!isset($this->data['Transferencia']['paginando'])) {

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->data['Transferencia']['monto_limite'] = $monto_limite;
            $this->data['Transferencia']['limite_total'] = $limite_total;

            if (isset($estado['Transferencia']['monto_limite_usuario'])) {
                $this->data['Transferencia']['monto_limite_usuario'] = $estado['Transferencia']['monto_limite_usuario'];
            }

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->Transferencia->set($this->data);
            
            if(strlen($this->data['Transferencia']['cuentaCreditar'])==20){
               $validaOpe = $this->Transferencia->validates(
                        array(
                            'fieldList' => array(
                                'cuentaCreditar',
                                'monto',
                                'descripcion'
                            )
                        )
                ); 
            }else{
                $validaOpe = $this->Transferencia->validates(
                        array(
                            'fieldList' => array(                                
                                'monto',
                                'descripcion'
                            )
                        )
                ); 
            }
            
            if ($validaOpe) {
                $this->data['Transferencia']['afiliar'] = 's';
                if (isset($estado['Transferencia']['aprobaciones_requeridas']))
                    $this->data['Transferencia']['aprobaciones_requeridas'] = $estado['Transferencia']['aprobaciones_requeridas'];

                if (_p('sms.otp.activo') == '0') {
                    $validacion_otp['validado'] = true;
                } else {
                    $validacion_otp = $this->Session->read('seguridad.validar_otp');
                }

                if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {

                    if (_p('sms.otp.activo') == '0') {
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
                            'Código de Autorización',
                            'Resultado de Operación'
                        );
                        $this->data['paso_final'] = 3;
                    }
                } else {

                    $this->data['pasos'] = array(
                        'Solicitud de Información',
                        'Confirmación',
                        'Resultado de Operación'
                    );
                    $this->data['paso_final'] = 2;
                }
                $this->data['Transferencia']['fecha_acumulado'] = $fecha_acumulado;
                $this->Session->write('transferencias.otros', $this->data);

                $this->Session->delete('transaccion.advertencia');

                // En caso de ser un usuario natural o master unico
                if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2) {
                    if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Transferencia'], 100023)) {

                        $this->data['titulo'] = 'Transferencia Mismo Titular Otro Banco';
                        $this->data['mensaje_alerta'] = 'transferencia.alerta';
                        $this->data['url_volver'] = '/transferencias/otros_afiliada';
                        $this->data['url_continuar'] = '/transferencias/otros_confirmacion';
                        $this->data['seccion_actual'] = 'transf.otros';

                        $this->Session->write('transaccion.advertencia', $this->data);
                        $this->redirect('transaccion_advertencia');
                    }
                }

                $this->redirect('otros_confirmacion');
            }
        }

        $bancos = $this->Activo->buscarBancos();

        $afiliados = $this->Afiliacion->obtenerListaAfiliados($usuario['id'], 't', 'ob');

       // foreach ($afiliados as &$i) {
            foreach($afiliados as $key =>  &$i){ 
            $numero = desencriptar($i['Afiliacion']['numero_cuenta']);
            if($i['Afiliacion']['banco']==''){
                $i['Afiliacion']['bancoDesc'] = $bancos[substr($numero, 0, 4)];
            }else{
                $i['Afiliacion']['bancoDesc'] = $bancos[$i['Afiliacion']['banco']];
            } 

            $id_beneficiario=    'sel'.$i['Afiliacion']['id'] ;
            $alias = desencriptar($i['Afiliacion']['alias']);
            $bancoDesc=($i['Afiliacion']['bancoDesc'])  ; 
            $nombre_beneficiario = desencriptar($i['Afiliacion']['nombre_beneficiario']);
            $numero_cuenta = desencriptar($i['Afiliacion']['numero_cuenta']);
            $afiliado_json = (object) array();
            $afiliado_json->id =$id_beneficiario; 
            $afiliado_json->alias =$alias; 
            $afiliado_json->nombre_beneficiario =$nombre_beneficiario; 
            $afiliado_json->numero_cuenta =$numero_cuenta;
            $afiliado_json->bancoDesc= $bancoDesc; 
            array_push($datos_afiliados, $afiliado_json);               
        }

        if ($afiliados) {
            // Empieza código para paginar
            $total_registros = count($afiliados);
            $offset = 0;

            if (
                    isset($this->data['Transferencia']['offset']) &&
                    preg_match('/^\d+$/', $this->data['Transferencia']['offset'])
            ) {
                $offset = intval($this->data['Transferencia']['offset']);
            }

            $resultados = array_slice(
                    $afiliados, $offset, 5, true
            );
            // Fin código para paginar
        }

        $this->set(compact('afiliados', 'resultados', 'total_registros', 'datos_afiliados'));

        $this->layout = 'interna';
    }

    /**
     * Método para el formulario de afiliación del beneficiario de la transferencia
     * a cuentas propias de otros bancos.
     * 
     */
    function otros_noafiliada() {

        if (!$this->Session->check('transferencias.otros'))
            $this->redirect('otros');

        $estado = $this->Session->read('transferencias.otros');

        $usuario = $this->Session->read('usuarios.datos');

        // Obtener el límite de parámetros
        if (empty($usuario['rif'])) {
            $limite_config = _p('limite_externas');
        } else {
            $limite_config = _p('limite_externas_j');
        }

        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $monto_limite = '';
        $fecha_acumulado = $this->Activo->consultaFechaCompensacion();
        $acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id'], $fecha_acumulado);
        $limite_total = $usuario['limite_externas'] ? $usuario['limite_externas'] : $limite_config;

       
        $limite_simf =$this->Parametro->findByCodigo('alto_vSIMF');
        $limite_simf= formatea_monto_fromWs($limite_simf['Parametro']['valor']);

        
       
        if ($limite_total) {
            $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_externas']);
        }
        $limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);

        if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
            $monto_limite = $limite_total = '';
        }
        $this->set(compact('monto_limite', 'limite_total', 'limite_simf'));
        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $this->set('seccion_actual', 'transf.otros');
        $this->set('bancos', $this->Activo->buscarBancos());

        if (empty($this->data['Transferencia'])) {
            $this->data = $estado;
        } else {

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->data['Transferencia']['monto_limite'] = $monto_limite;
            $this->data['Transferencia']['limite_simf']=$limite_simf;

            if (empty($usuario['rif'])) {
                $this->data['Transferencia']['limite_total'] = $limite_total;
            } else {
                $this->data['Transferencia']['monto_maximo'] = $limite_total;
            }

            if (isset($estado['Transferencia']['monto_limite_usuario'])) {
                $this->data['Transferencia']['monto_limite_usuario'] = $estado['Transferencia']['monto_limite_usuario'];
            }



            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->Transferencia->set($this->data);
           

            if(strlen($this->data['Transferencia']['cuentaCreditar'])==20){
               $validaOpe = $this->Transferencia->validates(
                        array(
                            'fieldList' => array(
                                'cuentaCreditar',
                                'monto',
                                'descripcion',
                                'alias',
                                'afiliar',
                                'monto_maximo'
                            )
                        )
                ); 
            }else{
                $validaOpe = $this->Transferencia->validates(
                        array(
                            'fieldList' => array(                                
                                'banco',
                                'monto',
                                'descripcion',
                                'alias',
                                'afiliar',
                                'monto_maximo'
                            )
                        )
                ); 
            }
            
            if ($validaOpe) {
                if ($this->data['Transferencia']['afiliar'] == 's' && $this->Afiliacion->checkAfiliacion($usuario['id'], $this->data['Transferencia']['cuentaCreditar'])) {
                    $this->Transferencia->validationErrors['cuentaCreditar'] = 'producto_nodisponible';
                } else if (isset($this->data['Transferencia']['alias']) && $this->data['Transferencia']['alias'] != "" && $this->Afiliacion->checkAlias($usuario['id'], $this->data['Transferencia']['alias'])) {
                    $this->Transferencia->validationErrors['alias'] = 'alias_nodisponible';
                } else {
                    /** Para identificar si es un usuario juridico * */
                    if ($usuario['tipo_rif']) {
                        $this->data['Transferencia']['tipoCedulaRif'] = $usuario['tipo_rif'];
                        $this->data['Transferencia']['nroCedulaRif'] = desencriptar($usuario['rif']);
                        $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                        $this->data['Transferencia']['nombreBeneficiario'] = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                    } else {
                        $this->data['Transferencia']['tipoCedulaRif'] = $usuario['tipo_cedula'];
                        $this->data['Transferencia']['nroCedulaRif'] = desencriptar($usuario['cedula']);
                        $this->data['Transferencia']['nombreBeneficiario'] = desencriptar($usuario['nombre']);
                    }
                    /** Para identificar si es un usuario juridico * */
                    $validacion_otp = $this->Session->read('seguridad.validar_otp');
                    if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
                        $this->data['pasos'] = array(
                            'Solicitud de Información',
                            'Confirmación',
                            'Código de Autorización',
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

                    $this->data['Transferencia']['email'] = desencriptar($usuario['email_principal']);
                    if($this->data['Transferencia']['banco']==''){
                        $this->data['Transferencia']['banco'] = substr($this->data['Transferencia']['cuentaCreditar'], 0, 4);
                    }                    
                    $this->data['Transferencia']['fecha_acumulado'] = $fecha_acumulado;
                    if (isset($estado['Transferencia']['aprobaciones_requeridas']))
                        $this->data['Transferencia']['aprobaciones_requeridas'] = $estado['Transferencia']['aprobaciones_requeridas'];

                    $this->Session->write('transferencias.otros', $this->data);

                    $this->Session->delete('transaccion.advertencia');

                    // En caso de ser un usuario natural o master unico
                    if (empty($usuario['rol_juridico_id']) || ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6)) {
                        if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Transferencia'], 100023)) {

                            $this->data['titulo'] = 'Transferencia Mismo Titular Otro Banco';
                            $this->data['mensaje_alerta'] = 'transferencia.alerta';
                            $this->data['url_volver'] = '/transferencias/otros_noafiliada';
                            $this->data['url_continuar'] = '/transferencias/otros_confirmacion';
                            $this->data['seccion_actual'] = 'transf.otros';

                            $this->Session->write('transaccion.advertencia', $this->data);
                            $this->redirect('transaccion_advertencia');
                        }
                    }

                    $this->redirect('otros_confirmacion');
                }
            }
        }

        $cuentas = $this->Activo->buscarCuentasSaldos();
        $prefijos_telefonicos = $this->PrefijosTelefonicos->listar_prefijos_movil();
        $prefijos = array();                 
        foreach ($prefijos_telefonicos as $prefijo) {                    
            $prefijos[$prefijo['PrefijosTelefonicos']['codigo_telefono']] = $prefijo['PrefijosTelefonicos']['codigo_telefono'];
        } 
        $bancos = $this->Activo->buscarBancos();
        
        $this->set(compact('cuentas','prefijos', 'bancos', 'limite_simf'));

        $this->layout = 'interna';
    }

    /**
     * Método para la confirmación de la transferencia a cuentas propias de 
     * otros bancos.
     * 
     */
    function otros_confirmacion() {

        if (!$this->Session->check('transferencias.otros'))
            $this->redirect('otros');

        $estado = $this->Session->read('transferencias.otros');                

        $this->layout = 'interna';

        $bancos = $this->Activo->buscarBancos();
        $usuario = $this->Session->read('usuarios.datos');

        $this->set('juridico', $usuario);
        $this->set('seccion_actual', 'transf.otros');
        $this->set('bancos', $bancos);

        $this->data = $estado;


        $this->data['Transferencia']['simf'] = 's';
        if($this->data['Transferencia']['simf'] == 's'){
           if (!is_null($c = $this->Parametro->findByCodigo('transferencia.otros.simf.comision'))) {
                $comision = $c['Parametro']['valor'];
            } else {
                $comision = 0.0;
            }
        }else{
            if (!is_null($c = $this->Parametro->findByCodigo('transferencia.otros.comision'))) {
                $comision = $c['Parametro']['valor'];
            } else {
                $comision = 0.0;
            }
        }


        
        // Si viene de afiliados realiza la busqueda de los datos.
        if ($this->data['Transferencia']['tipoDestinatario'] == 'a') {



            $afiliado = $this->Afiliacion->obtenerDataAfiliado($usuario['id'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['id']);

            if (!$afiliado) {
                $this->redirect('otros');
            }

            $this->data['Transferencia']['nombreBeneficiario'] = desencriptar($afiliado['nombre_beneficiario']);
            $this->data['Transferencia']['tipoCedulaRif'] = $afiliado['tipo_cedula'];
            $this->data['Transferencia']['nroCedulaRif'] = desencriptar($afiliado['cedula']);
            $this->data['Transferencia']['emailBeneficiario'] = desencriptar($afiliado['email']);
            $this->data['Transferencia']['telefono'] = $afiliado['telefono'];
            $this->data['Transferencia']['codigo_telefono'] = $afiliado['codigo_telefono'];
            $this->data['Transferencia']['afiliar'] = 'n';
            if(!empty($afiliado['banco'])){
                $this->data['Transferencia']['banco'] = $afiliado['banco'];
            }else{
                $this->data['Transferencia']['banco'] = substr($this->data['Transferencia']['cuentaCreditar'], 0, 4);

            }            
            $this->data['Transferencia']['id'] = $afiliado['id'];
            $this->data['Transferencia']['email'] = desencriptar($afiliado['email']);
            $this->data['Transferencia']['comision'] = $comision;
            $this->Session->write('transferencias.otros', $this->data);
        } else {
            $this->data['Transferencia']['id'] = null;
            $this->data['Transferencia']['comision'] = $comision;

            $this->Session->write('transferencias.otros', $this->data);
        }
    }

    /**
     * Método para el proceso de la transferencia a cuentas propias de 
     * otros bancos.
     * 
     */
    function otros_resultado() {

        if (!$this->Session->check('transferencias.otros'))
            $this->redirect('otros');

        $estado = $this->Session->read('transferencias.otros');
        $descripcion = "";
        $usuario = $this->Session->read('usuarios.datos');

        $bancos = $this->Activo->buscarBancos();
        $bancos2 = $this->Activo->buscarBancos2();


        $this->data = $estado;


        // Validación anti fraude
        $cuentas = $this->Activo->buscarCuentas();

        if (
                !in_array($this->data['Transferencia']['cuentaDebitar'], array_keys($cuentas))
        ) {
            $this->redirect('/consultas/integral');
        }
        //  Fin validación anti fraude								

        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.otros');
        $this->set('bancos', $bancos);
        $this->set('bancos2', $bancos2);

        if ($this->Session->check('transferencias.otros')) {
            $this->data = $this->Session->read('transferencias.otros');
            //inicio transferencias
            $usuario = $this->Session->read('usuarios.datos');

            $this->set('juridico', $usuario['tipo_rif']);


            
            if(trim($this->data['Transferencia']['telefono'])!=''){
                $dataBanco = $bancos2[$this->data['Transferencia']['banco']];
            }else{
                $dataBanco = $bancos2[substr($this->data['Transferencia']['cuentaCreditar'], 0, 4)];
            }                            

            $cedula = $this->data['Transferencia']['nroCedulaRif'];
            if (strlen($cedula) < 9) {

                $cedula = str_pad($cedula, 9, '0', STR_PAD_LEFT);
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


            //Validar Lista negra
                $swEncontrada = 0;  
                $tipoDocumento =  trim($this->data['Transferencia']['tipoCedulaRif']);
                $cedulaLB = trim($this->data['Transferencia']['nroCedulaRif']);
                $cedulaLB = $cedulaLB * 1; 

                $bancoBenef = $this->data['Transferencia']['banco'];
                $monto = formatea_monto_toWs($this->data['Transferencia']['monto']);
      
                $swEncontrada = $this->valListaCed($tipoDocumento, $cedulaLB, $bancoBenef, $monto);
                if($swEncontrada == 1){
                    $ruta = 'lb_transferencia';
                    $this->redirect($ruta);                    
                }
            //Fin Validar lista negra





            /** Para identificar si es un usuario juridico * */
            if (
                    empty($usuario['tipo_rif']) ||
                    ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) // Master único
            ) {

                if (empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2) {

                    // $validacion_otp = $this->Session->read('seguridad.validar_otp');

                    // if (
                    //     (
                    //     $validacion_otp == null ||
                    //     !isset($validacion_otp['validado']) ||
                    //     !$validacion_otp['validado']
                    //     )
                    // ) {
                    //     if ($this->data['Transferencia']['tipoDestinatario'] == 'a') {
                    //         $url_volver = '/transferencias/otros_afiliada';
                    //     } else {
                    //         $url_volver = '/transferencias/otros_noafiliada';
                    //     }

                    //     $this->Session->write(
                    //         'seguridad.validar_otp', array(
                    //         'url_aceptar' => '/transferencias/otros_resultado',
                    //         'url_volver' => $url_volver,
                    //         'layout' => 'interna',
                    //         'titulo' => 'Transferencia Mismo Titular Otro Banco',
                    //         'pasos' => $estado['pasos'],
                    //         'paso_actual' => 2,
                    //         'seccion_actual' => 'transf.otros'
                    //             )
                    //     );

                    //     $this->redirect('/seguridad/genera_otp');
                    // }
                }else{

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
                                                'url_aceptar'=>'/transferencias/otros_resultado',
                                                'layout'=>'interna',
                                                'titulo'=>'Transferencia Mismo Titular Otro Banco',
                                                'pasos'=> $estado['pasos'],
                                                'paso_actual'=>2,
                                                'seccion_actual'=>'transf.otros'
                                        )
                                );

                                $this->redirect('/seguridad/validar_tdc');
                    }

                }

                $this->data['Transferencia']['simf']='s';
                if($this->data['Transferencia']['tipoCedulaRif']=='P'){
                        $tipoSchmeNmBenef = 'SPAS';
                    }elseif($this->data['Transferencia']['tipoCedulaRif']=='V' || $this->data['Transferencia']['tipoCedulaRif']=='E'){
                        $tipoSchmeNmBenef = 'SCID';
                    }else{
                        $tipoSchmeNmBenef = 'SRIF';
                    }
                    
                    if(trim($this->data['Transferencia']['telefono'])!=''){
                        $credito = array(
                            'id'=>trim(ltrim($this->data['Transferencia']['codigo_telefono'],'0').$this->data['Transferencia']['telefono']), 
                            'tp'=>'CELE'
                        );
                    }else{
                        $credito = array(
                            'id'=>trim($this->data['Transferencia']['cuentaCreditar']), 
                            'tp'=>'CNTA'
                        );
                    }
                    
                    $res = $this->Activo->invocaSIMF(
                        'CstmrCdtTrfInitn',
                        array(
                            'CstmrCdtTrfInitn'=>array(
                                'user'=>Configure::read('as400.user'),
                                'password'=>Configure::read('as400.pwd'),
                                'canal'=>'4',
                                'id'=>$usuario['id'],                               
                                'tipo'=>'1',
                                'ip'=>Configure::read('ip.client'),
                                'grpHdr'=>array(
                                    'ctrlSum'=>array(
                                        'amt'=>formatea_monto_toWs($this->data['Transferencia']['monto']), 
                                        'ccy'=>'VES'
                                    ),
                                    'lclInstrm'=>'040',
                                    'nbOfTxs'=>1,
                                ),
                                'pmtInf'=>array(
                                    'amount'=>array(
                                        'amt'=>formatea_monto_toWs($this->data['Transferencia']['monto']), 
                                        'ccy'=>'VES'
                                    ),
                                    'cdtr'=>array(
                                        'id'=>$this->data['Transferencia']['tipoCedulaRif'].$this->data['Transferencia']['nroCedulaRif'], 
                                        'nm'=>trim($this->data['Transferencia']['nombreBeneficiario']),
                                        'schmeNm'=>$tipoSchmeNmBenef
                                    ),
                                    'cdtrAcct'=>$credito,
                                    'cdtrAgt'=>$this->data['Transferencia']['banco'],
                                    'dbtr'=>array(
                                        'id'=>$identificacionSIMF,
                                        'nm'=>desencriptar($usuario['nombre']),
                                        'schmeNm'=>$tipoSchmeNm
                                    ),
                                    'dbtrAcct'=>array(
                                        'id'=>trim($this->data['Transferencia']['cuentaDebitar']), 
                                        'tp'=>'CNTA'
                                    ),
                                    'dbtrAgt'=>'0171',
                                    'regId'=>1,
                                    'rmtInf'=>trim($this->data['Transferencia']['descripcion']),
                                    'purp'=>220
                                )
                            )
                        )
                    );  
                
                    $error = -1;
                    $descripcion = "Error inesperado, transacción no disponible.";                                        
                    
                    if (isset($res['return']['codRespuesta']) && $res['return']['codRespuesta']!='00') {
                        $mensaje = explode("-",$res['return']['mensaje']);
                        if ($mensaje['3'] == 4121) {
                            $error = 4121;                        
                            $descripcion = $res['return']['codRespuesta'] . ' - ' . $mensaje['4'];
                        }else{
                        $error = -1;                        
                        $descripcion = $res['return']['codRespuesta'] . ' - ' . $res['return']['mensaje'];
                        }
                    }else{
                        $error = 0;
                        $referencia = $res['return']['referencia'];
                    }
                    
                    $estatus = 'EN PROCESO';
                    $this->set('pendienteSimf', true);
                    
                              

                if ($error == 0) {

                    $banco = $this->data['Transferencia']['banco'];
                    
                    /** ACTUALIZA EL ACUMULADO DEL DIA SEGUN LA FRANJA DE COMPENSACION* */
                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'e', $this->data['Transferencia']['monto'], $this->data['Transferencia']['fecha_acumulado']);
                    /** ACTUALIZA EL ACUMULADO DEL DIA SEGUN LA FRANJA DE COMPENSACION* */
                    $this->Bitacora->registraBitacora(
                           $usuario['id'], 100023, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto'], $referencia, $this->data['Transferencia']['descripcion'], 0
                    );

                    $this->data['Transferencia']['REFERENCIA'] = $referencia;
                    $this->data['Transferencia']['CONCEPTO'] = $this->data['Transferencia']['descripcion'];
                    $this->data['Transferencia']['TIPO'] = '';
                    $this->data['Transferencia']['NRO_BENEFICIARIO'] = $credito['id'];
                    $this->data['Transferencia']['ESTATUS'] = 'EN PROCESO';
                    $this->data['Transferencia']['banco'] = $bancos[$this->data['Transferencia']['banco']];

                    // Genera la data para la descarga de pdf
                    $this->Session->write('usuarios.descarga.pdf', $this->data['Transferencia']);

                    // Email Beneficiario
                    // Email Options
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = desencriptar($usuario['email_principal']);
                    $this->Email->subject = 'Notificacion Transferencia';
                    $this->Email->sendAs = 'html';

                    // Informacion del correo
                    $this->Email->template = 'transferencia_ordenante';
                    $tipo_accion = 'Transferencia';
                    $tipo_afiliacion = 'Mismo Titular Otros Bancos';
                    $this->set(compact('usuario', 'tipo_accion', 'tipo_afiliacion'));

                    // Envio del correo
                    $this->Email->send();
                } else {

                    $this->log('Transferencias Mismo Titular Otros Bancos - ' . $descripcion, LOG_ERR);
                    $this->Bitacora->registraBitacora(
                            $usuario['id'], 100023, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto'], NULL, $descripcion, 1
                    );
                }

                if ($error != 182 && $error != 2149 && $error != 1913 && $error != -1 && $error != 223 && $error != 1912 && $error != 1616 && $error != 862 && $this->data['Transferencia']['afiliar'] == 's') {
                    
                    if(trim($this->data['Transferencia']['telefono'])!=''){

                        $dataBanco = $this->data['Transferencia']['banco'];

                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 't', 'ob', $this->data['Transferencia']['alias'], $this->data['Transferencia']['codigo_telefono'].$this->data['Transferencia']['telefono'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['email'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id'], $this->data['Transferencia']['codigo_telefono'], $this->data['Transferencia']['telefono'], $banco
                        );
                    }else{
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 't', 'ob', $this->data['Transferencia']['alias'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['email'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id']
                        );
                    }
                    
                    if (!$error_afiliacion && $this->data['Transferencia']['tipoDestinatario'] == 'n') {

                        // Registra en bitacora
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo']
                        );

                        // Email Options
                        $this->Email->reset();
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery = 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = desencriptar($usuario['email_principal']);
                        $this->Email->subject = 'Notificacion Afiliacion';
                        $this->Email->sendAs = 'html';

                        // Informacion del correo
                        $this->Email->template = 'afiliacion_producto';
                        $tipo_accion = 'Transferencia';
                        $tipo_afiliacion = 'Mismo Titular Otros Bancos';
                        $nombreBeneficiario = $this->data['Transferencia']['nombreBeneficiario'];
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                        // Envio del correo
                        $this->Email->send();
                    } else {
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo'], NULL, $descripcion, 1
                        );
                    }
                }

                $this->set('pendiente', false);
            } else if (
                    $usuario['rol_juridico_id'] == 3 || // Operador
                    $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
            ) {
                // En caso de jurídico, sólo los operadores y operadores/aprobadores pueden
                // generar una transferencia. En cuyo caso, se deja pendiente por aprobar

                $error = 0;

                //Solicitar TC si esta afiliando la cuenta
                if ($this->data['Transferencia']['afiliar'] == 's' && $this->data['Transferencia']['tipoDestinatario'] == 'n') {

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
                                    'url_aceptar' => '/transferencias/terceros_resultado',
                                    'layout' => 'interna',
                                    'titulo' => 'Transferencia Mismo Titular Otro Banco',
                                    'pasos' => array(),
                                    'paso_actual' => 2,
                                    'seccion_actual' => 'transf.otros'
                                )
                        );

                        $this->redirect('/seguridad/validar_tdc');
                    }
                }

                $id_banco=$this->data['Transferencia']['banco'];

                $this->data['Transferencia']['banco'] = $bancos[$this->data['Transferencia']['banco']];
                if(trim($this->data['Transferencia']['telefono'])!=''){
                        $credito = array(
                            'id'=>trim(ltrim($this->data['Transferencia']['codigo_telefono'],'0').$this->data['Transferencia']['telefono']), 
                            'tp'=>'CELE'
                        );
                    }else{
                        $credito = array(
                            'id'=>trim($this->data['Transferencia']['cuentaCreditar']), 
                            'tp'=>'CNTA'
                        );
                    }

                $res = $this->TransaccionPendiente->save(
                        array(
                            'TransaccionPendiente' => array(
                                'usuario_id' => $usuario['id'],
                                'tipo' => 'tpo',
                                'estatus' => 'pa',
                                'fecha_hora_registro' => date('Y-m-d H:i:s'),
                                'monto' => formatea_monto_toWs($this->data['Transferencia']['monto']),
                                'aprobaciones_requeridas' => $this->data['Transferencia']['aprobaciones_requeridas'],
                                'tipo_rif' => $usuario['tipo_rif'],
                                'rif' => $usuario['rif'],
                                'cuenta_origen' => encriptar($this->data['Transferencia']['cuentaDebitar']),
                                'cuenta_destino' => encriptar($credito['id']),
                                'descripcion' => encriptar($this->data['Transferencia']['descripcion']),
                                'nombre_beneficiario' => encriptar($this->data['Transferencia']['nombreBeneficiario']),
                                'tipo_id_beneficiario' => $this->data['Transferencia']['tipoCedulaRif'],
                                'id_beneficiario' => encriptar($this->data['Transferencia']['nroCedulaRif']),
                                'email_beneficiario' => $usuario['email_principal'],
                                'alias' => isset($this->data['Transferencia']['alias']) ? encriptar($this->data['Transferencia']['alias']) : null,
                                'banco' => isset($id_banco) ? $id_banco : null
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

                if ($this->data['Transferencia']['afiliar'] == 's') {

                    if(trim($this->data['Transferencia']['telefono'])!=''){
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 't', 'ob', $this->data['Transferencia']['alias'], $this->data['Transferencia']['codigo_telefono'].$this->data['Transferencia']['telefono'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id'], $this->data['Transferencia']['codigo_telefono'], $this->data['Transferencia']['telefono'], $id_banco
                        );
                    }else{
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 't', 'ob', $this->data['Transferencia']['alias'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id']
                        );
                    }  


                    if (!$error_afiliacion && $this->data['Transferencia']['tipoDestinatario'] == 'n') {

                        // Registra en bitacora
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo']
                        );

                        // Email Options
                        $this->Email->reset();
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery = 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = desencriptar($usuario['email_principal']);
                        $this->Email->subject = 'Notificacion Afiliacion';
                        $this->Email->sendAs = 'html';

                        // Informacion del correo
                        $this->Email->template = 'afiliacion_producto';
                        $tipo_accion = 'Transferencia';
                        $tipo_afiliacion = 'Mismo Titular Otros Bancos';
                        $nombreBeneficiario = $this->data['Transferencia']['nombreBeneficiario'];
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                        // Envio del correo
                        $this->Email->send();
                    } else {
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo'], NULL, NULL, 1
                        );
                    }
                } else {
                    $error_afiliacion = false;
                }

                $this->set('pendiente', true);
            }

            $this->set(compact('error', 'error_afiliacion', 'referencia'));

            $this->Session->delete('transferencias.otros');
        } else {
            $this->redirect('otros');
        }
    }

    /**
     * Método inicial para realizar una transferencia a cuentas de otros bancos.
     * 
     * @param string $volver Indica si está regresando de la confirmación
     */
    function terceros_otros($volver = null) {

        /* Para validar el acceso al modulo */
        if (!validarTransaccion(100024))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $this->Session->delete('usuarios.descarga.pdf');
        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.terceros.otros');

        if (!empty($this->data['Transferencia'])) {
            $usuario = $this->Session->read('usuarios.datos');

            // Para usuario jurídicos operadores, obtener su configuración para este tipo de transacción
            if (
                    $usuario['rol_juridico_id'] == 3 ||
                    $usuario['rol_juridico_id'] == 5
            ) {
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                        $usuario['id'], 100024, $this->data['Transferencia']['cuentaDebitar']
                );

                if (!$transaccion_detalle) {
                    $this->Transferencia->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                }
            }

            if (empty($this->Transferencia->validationErrors)) {
                if (isset($transaccion_detalle)) {
                    $this->data['Transferencia']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if (is_numeric($transaccion_detalle['limite'])) {
                        $this->data['Transferencia']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['Transferencia']['monto_limite_usuario'] = '';
                    }
                }

                $this->Transferencia->set($this->data);

                if ($this->Transferencia->validates(
                    array(
                        'fieldList' => array(
                            'cuentaDebitar',
                            'tipoDestinatario'
                        )
                    ))
                ) {

                    $this->Session->write('transferencias.terceros_otros', $this->data);

                    if ($this->data['Transferencia']['tipoDestinatario'] == 'a') {
                        $this->redirect('terceros_otros_afiliada');
                    } else {
                        $this->redirect('terceros_otros_noafiliada');
                    }
                }
            }
        } elseif (isset($volver) && $this->Session->check('transferencias.terceros_otros')) {
            $this->data = $this->Session->read('transferencias.terceros_otros');
        }

        $cuentas = $this->Activo->buscarCuentasSaldos();

        $this->set(compact('cuentas'));
    }

    /**
     * Método para la búsqueda de los afiliados para realizar la transferencia
     * a cuentas de otros bancos.
     * 
     */
    function terceros_otros_afiliada() {

        if (!$this->Session->check('transferencias.terceros_otros'))
            $this->redirect('terceros_otros');

        $estado = $this->Session->read('transferencias.terceros_otros');

        $usuario = $this->Session->read('usuarios.datos');

        // Obtener el límite de parámetros
        if (empty($usuario['rif'])) {
            $limite_config = _p('limite_externas');
        } else {
            $limite_config = _p('limite_externas_j');
        }
        $limite_simf =$this->Parametro->findByCodigo('alto_vSIMF');
        $limite_simf= formatea_monto_fromWs($limite_simf['Parametro']['valor']);

        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $monto_limite = '';
        $fecha_acumulado = $this->Activo->consultaFechaCompensacion();
        $acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id'], $fecha_acumulado);
        $limite_total = $usuario['limite_externas'] ? $usuario['limite_externas'] : $limite_config;

        if ($limite_total) {
            $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_externas']);
        }
        $limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);

        if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
            $monto_limite = $limite_total = '';
        }
        $this->set(compact('monto_limite', 'limite_simf'));
        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $this->set('seccion_actual', 'transf.terceros.otros');

        if (empty($this->data['Transferencia'])) {
            $this->data = $estado;
        } else if (!isset($this->data['Transferencia']['paginando'])) {

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->data['Transferencia']['monto_limite'] = $monto_limite;
            $this->data['Transferencia']['limite_total'] = $limite_total;

            if (isset($estado['Transferencia']['monto_limite_usuario'])) {
                $this->data['Transferencia']['monto_limite_usuario'] = $estado['Transferencia']['monto_limite_usuario'];
            }

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->data['Transferencia']['alias'] = $this->sanitize_input($this->data['Transferencia']['alias']);
            $this->data['Transferencia']['descripcion'] = $this->sanitize_input($this->data['Transferencia']['descripcion']);
            $this->Transferencia->set($this->data);
            
            if(strlen($this->data['Transferencia']['cuentaCreditar'])==20){
               $validaOpe = $this->Transferencia->validates(
                        array(
                            'fieldList' => array(
                                'cuentaCreditar',
                                'monto',
                                'descripcion'
                            )
                        )
                ); 
            }else{
                $validaOpe = $this->Transferencia->validates(
                        array(
                               'fieldList' => array(                                
                                'monto',
                                'descripcion'
                            )
                        )
                ); 
            }
            
            if ($validaOpe) {
                $this->data['Transferencia']['afiliar'] = 's';
                if (isset($estado['Transferencia']['aprobaciones_requeridas']))
                    $this->data['Transferencia']['aprobaciones_requeridas'] = $estado['Transferencia']['aprobaciones_requeridas'];

                if (_p('sms.otp.activo') == '0') {
                    $validacion_otp['validado'] = true;
                } else {
                    $validacion_otp = $this->Session->read('seguridad.validar_otp');
                }

                if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
                    if (_p('sms.otp.activo') == '0') {
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
                            'Código de Autorización',
                            'Resultado de Operación'
                        );
                        $this->data['paso_final'] = 3;
                    }
                } else {

                    $this->data['pasos'] = array(
                        'Solicitud de Información',
                        'Confirmación',
                        'Resultado de Operación'
                    );
                    $this->data['paso_final'] = 2;
                }
                $this->data['Transferencia']['fecha_acumulado'] = $fecha_acumulado;
                $this->Session->write('transferencias.terceros_otros', $this->data);

                $this->Session->delete('transaccion.advertencia');

                // En caso de ser un usuario natural o master unico
                if (empty($usuario['rol_juridico_id']) || ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6)) {
                    if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Transferencia'], 100024)) {

                        $this->data['titulo'] = 'Transferencia Tercero Otro Banco';
                        $this->data['mensaje_alerta'] = 'transferencia.alerta';
                        $this->data['url_volver'] = '/transferencias/terceros_otros_afiliada';
                        $this->data['url_continuar'] = '/transferencias/terceros_otros_confirmacion';
                        $this->data['seccion_actual'] = 'transf.terceros.otros';

                        $this->Session->write('transaccion.advertencia', $this->data);
                        $this->redirect('transaccion_advertencia');
                    }
                }

                $this->redirect('terceros_otros_confirmacion');
            }
        }


        $bancos = $this->Activo->buscarBancos();

        $afiliados = $this->Afiliacion->obtenerListaAfiliados($usuario['id'], 't', 'tob');
        $datos_afiliados = array();  

        $datos_afiliados = array();  
         foreach($afiliados as $key =>  &$i){ 
   //   foreach ($afiliados as &$i) {
            $numero = desencriptar($i['Afiliacion']['numero_cuenta']);
            if($i['Afiliacion']['banco']==''){
                $i['Afiliacion']['bancoDesc'] = $bancos[substr($numero, 0, 4)];
            }else{
                $i['Afiliacion']['bancoDesc'] = $bancos[$i['Afiliacion']['banco']];
            } 


            // Obetener la informacion de los faliados para filtrar rvelez 

       $id_beneficiario=    'sel'.$i['Afiliacion']['id'] ;
            $alias = desencriptar($i['Afiliacion']['alias']);
            $bancoDesc=($i['Afiliacion']['bancoDesc'])  ; 
            $nombre_beneficiario = desencriptar($i['Afiliacion']['nombre_beneficiario']);
            $numero_cuenta = desencriptar($i['Afiliacion']['numero_cuenta']);
            $afiliado_json = (object) array();
            $afiliado_json->id =$id_beneficiario; 
            $afiliado_json->alias =$alias; 
            $afiliado_json->nombre_beneficiario =$nombre_beneficiario; 
            $afiliado_json->numero_cuenta =$numero_cuenta;
            $afiliado_json->bancoDesc= $bancoDesc; 
            array_push($datos_afiliados, $afiliado_json);    

        
        }
 
        if ($afiliados) {
            // Empieza código para paginar
            $total_registros = count($afiliados);
            $offset = 0;

            if (
                    isset($this->data['Transferencia']['offset']) &&
                    preg_match('/^\d+$/', $this->data['Transferencia']['offset'])
            ) {
                $offset = intval($this->data['Transferencia']['offset']);
            }

            $resultados = array_slice(
                    $afiliados, $offset, $total_registros, true
            );
            // Fin código para paginar
        }
  
        $this->set(compact('afiliados', 'resultados', 'total_registros', 'datos_afiliados'));

        $this->layout = 'interna';
    }

    /**
     * Método para el formulario de afiliación del beneficiario de la transferencia
     * a cuentas de otros bancos.
     * 
     */
    function terceros_otros_noafiliada() {

        if (!$this->Session->check('transferencias.terceros_otros'))
            $this->redirect('terceros_otros');

        $estado = $this->Session->read('transferencias.terceros_otros');

        $usuario = $this->Session->read('usuarios.datos');

        // Obtener el límite de parámetros
        if (empty($usuario['rif'])) {
            $limite_config = _p('limite_externas');
        } else {
            $limite_config = _p('limite_externas_j');
        }

        /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
        $monto_limite = '';
        $fecha_acumulado = $this->Activo->consultaFechaCompensacion();
        $acumulado = $this->AcumuladoTransaccion->buscaInfo($usuario['id'], $fecha_acumulado);
        $limite_total = $usuario['limite_externas'] ? $usuario['limite_externas'] : $limite_config;
        $limite_simf =$this->Parametro->findByCodigo('alto_vSIMF');
        $limite_simf= formatea_monto_fromWs($limite_simf['Parametro']['valor']);

        if ($limite_total) {
            $monto_limite = formatea_monto_fromWs($limite_total - $acumulado['AcumuladoTransaccion']['acumulado_externas']);
        }
        $limite_total = empty($limite_total) ? '' : formatea_monto_fromWs($limite_total);

        if (isset($usuario['rif']) && $usuario['rol_juridico_id'] != 2) {
            $monto_limite = $limite_total = '';
        }
        
        $usuario = $this->Session->read('usuarios.datos');        

        if (empty($this->data['Transferencia'])) {
            $this->data = $estado;
        } else {

            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->data['Transferencia']['monto_limite'] = $monto_limite;

            if (empty($usuario['rif'])) {
                $this->data['Transferencia']['limite_total'] = $limite_total;
            } else {
                $this->data['Transferencia']['monto_maximo'] = $limite_total;
            }

            if (isset($estado['Transferencia']['monto_limite_usuario'])) {
                $this->data['Transferencia']['monto_limite_usuario'] = $estado['Transferencia']['monto_limite_usuario'];
            }

             $this->data['Transferencia']['limite_simf']=$limite_simf;
            /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
            $this->Transferencia->set($this->data);
            
            if($this->data['Transferencia']['cuentaCreditar']!=''){
               $validaOpe = $this->Transferencia->validates(
                        array(
                            'fieldList' => array(
                                'cuentaCreditar',
                                'nombreBeneficiario',
                                'tipoCedulaRif',
                                'nroCedulaRif',
                                'emailBeneficiario',
                                'monto',
                                'descripcion',
                                'alias',
                                'afiliar',
                                'monto_maximo',
                            )
                        )
                ); 
            }else{
                $validaOpe = $this->Transferencia->validates(
                        array(
                            'fieldList' => array(                                
                                'nombreBeneficiario',
                                'tipoCedulaRif',
                                'nroCedulaRif',
                                'emailBeneficiario',
                                'monto',
                                'descripcion',
                                'alias',
                                'afiliar',
                                'monto_maximo',
                            )
                        )
                ); 
            }
            
            if ($validaOpe) {
                if ($this->data['Transferencia']['afiliar'] == 's' && $this->Afiliacion->checkAfiliacion($usuario['id'], $this->data['Transferencia']['cuentaCreditar'])) {
                    $this->Transferencia->validationErrors['cuentaCreditar'] = 'producto_nodisponible';
                } else if (isset($this->data['Transferencia']['alias']) && $this->data['Transferencia']['alias'] != "" && $this->Afiliacion->checkAlias($usuario['id'], $this->data['Transferencia']['alias'])) {
                    $this->Transferencia->validationErrors['alias'] = 'alias_nodisponible';
                } else if (!$this->Activo->validarRIF($this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], 't')) {
                    $this->Transferencia->validationErrors['nroCedulaRif'] = _m('valor.invalido', 'el número de identificación');
                } else {
                    if($this->data['Transferencia']['banco']==''){
                        $this->data['Transferencia']['banco'] = substr($this->data['Transferencia']['cuentaCreditar'], 0, 4);
                    } 
                    if (isset($estado['Transferencia']['aprobaciones_requeridas']))
                        $this->data['Transferencia']['aprobaciones_requeridas'] = $estado['Transferencia']['aprobaciones_requeridas'];

                    $validacion_otp = $this->Session->read('seguridad.validar_otp');
                    
                    if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
                        $this->data['pasos'] = array(
                            'Solicitud de Información',
                            'Confirmación',
                            'Código de Autorización',
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
                    $this->data['Transferencia']['fecha_acumulado'] = $fecha_acumulado;
                    $this->Session->write('transferencias.terceros_otros', $this->data);

                    $this->Session->delete('transaccion.advertencia');

                    // En caso de ser un usuario natural o master unico
                    if (empty($usuario['rol_juridico_id']) || ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6)) {
                        if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['Transferencia'], 100024)) {

                            $this->data['titulo'] = 'Transferencia Tercero Otro Banco';
                            $this->data['mensaje_alerta'] = 'transferencia.alerta';
                            $this->data['url_volver'] = '/transferencias/terceros_otros_noafiliada';
                            $this->data['url_continuar'] = '/transferencias/terceros_otros_confirmacion';
                            $this->data['seccion_actual'] = 'transf.terceros.otros';

                            $this->Session->write('transaccion.advertencia', $this->data);
                            $this->redirect('transaccion_advertencia');
                        }
                    }

                    $this->redirect('terceros_otros_confirmacion');
                }
            }
        }
                
        $this->set('seccion_actual', 'transf.terceros.otros');
        $prefijos_telefonicos = $this->PrefijosTelefonicos->listar_prefijos_movil();
        $prefijos = array();                 
        foreach ($prefijos_telefonicos as $prefijo) {                    
            $prefijos[$prefijo['PrefijosTelefonicos']['codigo_telefono']] = $prefijo['PrefijosTelefonicos']['codigo_telefono'];
        } 
        $bancos = $this->Activo->buscarBancos();
        
        $this->set(compact('monto_limite', 'limite_total', 'prefijos', 'bancos', 'limite_simf'));
        
        $this->layout = 'interna';
    }

    /**
     * Método para la confirmación de la transferencia a cuentas de otros bancos.
     * 
     */
    function terceros_otros_confirmacion() {

        if (!$this->Session->check('transferencias.terceros_otros'))
            $this->redirect('terceros_otros');

        $estado = $this->Session->read('transferencias.terceros_otros');       

        $this->layout = 'interna';
        $bancos = $this->Activo->buscarBancos();

        $this->set('seccion_actual', 'transf.terceros.otros');
        $this->set('bancos', $bancos);

        $usuario = $this->Session->read('usuarios.datos');

        $this->set('juridico', $usuario);

        $this->data = $estado;
        $this->data['Transferencia']['simf'] = 's';
        
        if($this->data['Transferencia']['simf'] == 's'){
            if (!is_null($c = $this->Parametro->findByCodigo('transferencia.otros_terceros.simf.comision'))) {
                $comision = $c['Parametro']['valor'];
            } else {
                $comision = 0.0;
            }
        }else{
            if (!is_null($c = $this->Parametro->findByCodigo('transferencia.otros_terceros.comision'))) {
                $comision = $c['Parametro']['valor'];
            } else {
                $comision = 0.0;
            }
        }                              
        
        // Si viene de afiliados realiza la busqueda de los datos...
        if ($this->data['Transferencia']['tipoDestinatario'] == 'a') {

             $afiliado = $this->Afiliacion->obtenerDataAfiliado($usuario['id'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['id']);

            if (!$afiliado) {
                $this->redirect('terceros_otros');
            }
 

            $this->data['Transferencia']['nombreBeneficiario'] = desencriptar($afiliado['nombre_beneficiario']);
            $this->data['Transferencia']['tipoCedulaRif'] = $afiliado['tipo_cedula'];
            $this->data['Transferencia']['nroCedulaRif'] = desencriptar($afiliado['cedula']);
            $this->data['Transferencia']['emailBeneficiario'] = desencriptar($afiliado['email']);
            $this->data['Transferencia']['telefono'] = $afiliado['telefono'];
            $this->data['Transferencia']['codigo_telefono'] = $afiliado['codigo_telefono'];
            $this->data['Transferencia']['afiliar'] = 'n';
            if(!empty($afiliado['banco'])){
                $this->data['Transferencia']['banco'] = $afiliado['banco'];
            }else{
                $this->data['Transferencia']['banco'] = substr($this->data['Transferencia']['cuentaCreditar'], 0, 4);

            }

             
            $this->data['Transferencia']['id'] = $afiliado['id'];
            $this->data['Transferencia']['comision'] = $comision;
            $this->Session->write('transferencias.terceros_otros', $this->data);
        } else {
            $this->data['Transferencia']['comision'] = $comision;
            $this->data['Transferencia']['id'] = null;
            $this->Session->write('transferencias.terceros_otros', $this->data);
        }        
    }

    /**
     * Método para el proceso de la transferencia a cuentas de otros bancos.
     * 
     */
    function terceros_otros_resultado() {

        if (!$this->Session->check('transferencias.terceros_otros'))
            $this->redirect('terceros_otros');

        $estado = $this->Session->read('transferencias.terceros_otros');
        $descripcion = "";
        $usuario = $this->Session->read('usuarios.datos');
        $datosbasicos=$this->Session->read('usuarios.nibpf3010');

        $bancos = $this->Activo->buscarBancos();
        $bancos2 = $this->Activo->buscarBancos2();

        $this->data = $estado;

        // Validación anti fraude
        $cuentas = $this->Activo->buscarCuentas();

        if (
            !in_array($this->data['Transferencia']['cuentaDebitar'], array_keys($cuentas))
        ) {
            $this->redirect('/consultas/integral');
        }
        //  Fin validación anti fraude			

        $this->layout = 'interna';

        $this->set('seccion_actual', 'transf.terceros.otros');
        $this->set('bancos', $bancos);
        $this->set('bancos2', $bancos2);

        if ($this->Session->check('transferencias.terceros_otros')) {
            $this->data = $this->Session->read('transferencias.terceros_otros');
            $usuario = $this->Session->read('usuarios.datos');
            
            if(trim($this->data['Transferencia']['telefono'])!=''){
                $dataBanco = $bancos2[$this->data['Transferencia']['banco']];
            }else{
                $dataBanco = $bancos2[substr($this->data['Transferencia']['cuentaCreditar'], 0, 4)];
            }            

            $cedula = $this->data['Transferencia']['nroCedulaRif'];
            if (strlen($cedula) < 9) {
                $cedula = str_pad($cedula, 9, '0', STR_PAD_LEFT);
            }

            $this->set('juridico', $usuario['tipo_rif']);



            //Validar Lista negra
                $swEncontrada = 0;  
                $tipoDocumento =  trim($this->data['Transferencia']['tipoCedulaRif']);
                $cedulaLB = trim($this->data['Transferencia']['nroCedulaRif']);
                $cedulaLB = $cedulaLB * 1; 

                $bancoBenef = $this->data['Transferencia']['banco'];
                $monto = formatea_monto_toWs($this->data['Transferencia']['monto']);

      

                $swEncontrada = $this->valListaCed($tipoDocumento, $cedulaLB, $bancoBenef, $monto);
                if($swEncontrada == 1){
                    $ruta = 'lb_transferencia';
                    $this->redirect($ruta);                    
                }
            //Fin Validar lista negra




            /** Para identificar si es un usuario juridico * */
            if ($usuario['tipo_rif']) {
                $dbtrNm = $datosbasicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                $identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
                $identificacionSIMF = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
                $tipoSchmeNm = 'SRIF';
            } else {
                $dbtrNm = $datosbasicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
                $identificacion = desencriptar($usuario['cedula']);
                $identificacionSIMF = $usuario['tipo_cedula'] . desencriptar($usuario['cedula']);
                if($usuario['tipo_cedula']=='P'){
                    $tipoSchmeNm = 'SPAS';
                }else{
                    $tipoSchmeNm = 'SCID';
                }
            }
            /** Para identificar si es un usuario juridico * */
            if (
                    empty($usuario['tipo_rif']) ||
                    ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) // Master único
            ) {

                if(empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2){

                    // $validacion_otp = $this->Session->read('seguridad.validar_otp');
              
                    // if (($validacion_otp == null ||
                    //     !isset($validacion_otp['validado']) ||
                    //     !$validacion_otp['validado'])) {
                    //         if ($this->data['Transferencia']['tipoDestinatario'] == 'a') {
                    //             $url_volver = '/transferencias/terceros_otros_afiliada';
                    //         } else {
                    //             $url_volver = '/transferencias/terceros_otros_noafiliada';
                    //         }

                    //         $this->Session->write(
                    //             'seguridad.validar_otp', array(
                    //                 'url_aceptar' => '/transferencias/terceros_otros_resultado',
                    //                 'url_volver' => $url_volver,
                    //                 'layout' => 'interna',
                    //                 'titulo' => 'Transferencia Tercero Otro Banco',
                    //                 'pasos' => $estado['pasos'],
                    //                 'paso_actual' => 2,
                    //                 'seccion_actual' => 'transf.terceros.otros'
                    //             )
                    //         );

                    //         $this->redirect('/seguridad/genera_otp');
                    // }

                }else{

                    $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

                    if(($validacion_tdc == null ||
                        !isset($validacion_tdc['validado']) ||
                        !$validacion_tdc['validado'])){


                        $this->Session->write(
                                'seguridad.validar_tdc',
                                array(
                                        'url_aceptar'=>'/transferencias/terceros_otros_resultado',
                                        'layout'=>'interna',
                                        'titulo'=>'Transferencia Tercero Otro Banco',
                                        'pasos'=> $estado['pasos'],
                                        'paso_actual'=>2,
                                        'seccion_actual'=>'transf.terceros.otros'
                                )
                        );

                        $this->redirect('/seguridad/validar_tdc');
                    }

                }
                    
                
                $this->data['Transferencia']['simf'] = 's';
                if($this->data['Transferencia']['simf'] == 's'){ 
                    
                    if($this->data['Transferencia']['tipoCedulaRif']=='P'){
                        $tipoSchmeNmBenef = 'SPAS';
                    }elseif($this->data['Transferencia']['tipoCedulaRif']=='V' || $this->data['Transferencia']['tipoCedulaRif']=='E'){
                        $tipoSchmeNmBenef = 'SCID';
                    }else{
                        $tipoSchmeNmBenef = 'SRIF';
                    }
                    
                    if(trim($this->data['Transferencia']['telefono'])!=''){
                        $credito = array(
                            'id'=>trim(ltrim($this->data['Transferencia']['codigo_telefono'],'0').$this->data['Transferencia']['telefono']), 
                            'tp'=>'CELE'
                        );
                    }else{
                        $credito = array(
                            'id'=>trim($this->data['Transferencia']['cuentaCreditar']), 
                            'tp'=>'CNTA'
                        );
                    }
                    
                    $res = $this->Activo->invocaSIMF(
                        'CstmrCdtTrfInitn',
                        array(
                            'CstmrCdtTrfInitn'=>array(
                                'user'=>Configure::read('as400.user'),
                                'password'=>Configure::read('as400.pwd'),
                                'canal'=>'4',
                                'id'=>$usuario['id'],                
                                'tipo'=>'1',
                                'ip'=>Configure::read('ip.client'),
                                'grpHdr'=>array(
                                    'ctrlSum'=>array(
                                        'amt'=>formatea_monto_toWs($this->data['Transferencia']['monto']), 
                                        'ccy'=>'VES'
                                    ),
                                    'lclInstrm'=>'040',
                                    'nbOfTxs'=>1,
                                ),
                                'pmtInf'=>array(
                                    'amount'=>array(
                                        'amt'=>formatea_monto_toWs($this->data['Transferencia']['monto']), 
                                        'ccy'=>'VES'
                                    ),
                                    'cdtr'=>array(
                                        'id'=>$this->data['Transferencia']['tipoCedulaRif'].$this->data['Transferencia']['nroCedulaRif'], 
                                        'nm'=>trim($this->data['Transferencia']['nombreBeneficiario']),
                                        'schmeNm'=>$tipoSchmeNmBenef
                                    ),
                                    'cdtrAcct'=>$credito,
                                    'cdtrAgt'=>$this->data['Transferencia']['banco'],
                                    'dbtr'=>array(
                                        'id'=>$identificacionSIMF,
                                        'nm'=>$dbtrNm,
                                        'schmeNm'=>$tipoSchmeNm
                                    ),
                                    'dbtrAcct'=>array(
                                        'id'=>trim($this->data['Transferencia']['cuentaDebitar']), 
                                        'tp'=>'CNTA'
                                    ),
                                    'dbtrAgt'=>'0171',
                                    'regId'=>1,
                                    'rmtInf'=>trim($this->data['Transferencia']['descripcion']),
                                    'purp'=>220
                                )
                            )
                        )
                    );    
                                                    
                    

                    $error = -1;
                    $descripcion = "Error inesperado, transacción no disponible.";                                        

                    if (isset($res['return']['codRespuesta']) && $res['return']['codRespuesta']!='00') {
                        $mensaje = explode("-",$res['return']['mensaje']);
                        if ($mensaje['3'] == 4121) {
                            $error = 4121;                        
                            $descripcion = $res['return']['codRespuesta'] . ' - ' . $mensaje['4'];
                        }else{
                        $error = -1;                        
                        $descripcion = $res['return']['codRespuesta'] . ' - ' . $res['return']['mensaje'];
                        }
                    }elseif(!$res){
                         $error = -1;
                    $descripcion = "Error inesperado, transacción no disponible.";
                       
                    }else{
                         $error = 0;
                        $referencia = $res['return']['referencia'];

                    }
                    
                    $estatus = 'EN PROCESO';
                    $this->set('pendienteSimf', true);
                    
                }                                
                
                if ($error == 0) {
                    
                    $banco = $this->data['Transferencia']['banco'];
                    
                    /** ACTUALIZA EL ACUMULADO DEL DIA SEGUN LA FRANJA DE COMPENSACION* */
                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'e', $this->data['Transferencia']['monto'], $this->data['Transferencia']['fecha_acumulado']);
                    /** ACTUALIZA EL ACUMULADO DEL DIA SEGUN LA FRANJA DE COMPENSACION* */
                    $this->Bitacora->registraBitacora(
                            $usuario['id'], 100024, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto'], $referencia, $this->data['Transferencia']['descripcion'], 0, NULL, $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['nombreBeneficiario']
                    );


                    $this->data['Transferencia']['REFERENCIA'] = $referencia;
                    $this->data['Transferencia']['CONCEPTO'] = $this->data['Transferencia']['descripcion'];
                    $this->data['Transferencia']['TIPO'] = '';
                    $this->data['Transferencia']['NRO_BENEFICIARIO'] = $credito['id'];
                    $this->data['Transferencia']['ESTATUS'] = $estatus;
                    $this->data['Transferencia']['banco'] = $bancos[$this->data['Transferencia']['banco']];

                    // Genera la data para la descarda de pdf
                    $this->Session->write('usuarios.descarga.pdf', $this->data['Transferencia']);

                    // Email Beneficiario
                    // Email Options
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = desencriptar($usuario['email_principal']);
                    $this->Email->subject = 'Notificacion Transferencia';
                    $this->Email->sendAs = 'html';

                    // Informacion del correo
                    $this->Email->template = 'transferencia_ordenante';
                    $tipo_accion = 'Transferencia';
                    $tipo_afiliacion = 'Terceros Otros Bancos';
                    $this->set(compact('usuario', 'tipo_accion', 'tipo_afiliacion'));

                    // Envio del correo
                    $this->Email->send();

                    // Email Options
                    $this->Email->reset();
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = $this->data['Transferencia']['emailBeneficiario'];
                    $this->Email->subject = 'Notificacion Transferencia';
                    $this->Email->sendAs = 'html';

                    // Informacion del correo
                    $this->Email->template = 'transferencia_beneficiario';
                    $tipo_accion = 'Transferencia';
                    $tipo_afiliacion = 'Terceros Otros Bancos';
                    $this->set(compact('usuario', 'tipo_accion', 'tipo_afiliacion'));


                    // Envio del correo
                    $this->Email->send();
                } else {
                    $this->log('Transferencias Terceros Otros Bancos - ' . $descripcion, LOG_ERR);
                    $this->Bitacora->registraBitacora(
                            $usuario['id'], 100024, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto'], NULL, $descripcion, 1
                    );
                }

                if ($error != 182 && $error != 2149 && $error != 1913 && $error != -1 && $error != 223 && $error != 1912 && $error != 1616 && $error != 862 && $this->data['Transferencia']['afiliar'] == 's') {
                    
                    if(trim($this->data['Transferencia']['telefono'])!=''){
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 't', 'tob', $this->data['Transferencia']['alias'], $this->data['Transferencia']['codigo_telefono'].$this->data['Transferencia']['telefono'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id'], $this->data['Transferencia']['codigo_telefono'], $this->data['Transferencia']['telefono'], $banco
                        );
                    }else{
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 't', 'tob', $this->data['Transferencia']['alias'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id']
                        );
                    }                                        

                    if (!$error_afiliacion && $this->data['Transferencia']['tipoDestinatario'] == 'n') {

                        // Registra en bitacora
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto_maximo'], NULL, NULL, 0
                        );

                        // Email Options
                        $this->Email->reset();
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery = 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = desencriptar($usuario['email_principal']);
                        $this->Email->subject = 'Notificacion Afiliacion';
                        $this->Email->sendAs = 'html';

                        // Informacion del correo
                        $this->Email->template = 'afiliacion_producto';
                        $tipo_accion = 'Transferencia';
                        $tipo_afiliacion = 'Terceros Otros Bancos';
                        $nombreBeneficiario = $this->data['Transferencia']['nombreBeneficiario'];
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                        // Envio del correo
                        $this->Email->send();
                    } else {
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo'], NULL, $descripcion, 1
                        );
                    }
                }

                $this->set('pendiente', false);
            } else if (
                    $usuario['rol_juridico_id'] == 3 || // Operador
                    $usuario['rol_juridico_id'] == 5     // Operador/Aprobador
            ) {
                // En caso de jurídico, sólo los operadores y operadores/aprobadores pueden
                // generar una transferencia. En cuyo caso, se deja pendiente por aprobar

                $error = 0;


                //Solicitar TC si esta afiliando la cuenta
                if ($this->data['Transferencia']['afiliar'] == 's' && $this->data['Transferencia']['tipoDestinatario'] == 'n') {

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
                            'url_aceptar' => '/transferencias/terceros_otros_resultado',
                            'layout' => 'interna',
                            'titulo' => 'Transferencia Tercero Otro Banco',
                            'pasos' => array(),
                            'paso_actual' => 2,
                            'seccion_actual' => 'transf.terceros.otros'
                                )
                        );

                        $this->redirect('/seguridad/validar_tdc');
                    }
                }

                
                $id_banco=$this->data['Transferencia']['banco'];

                $this->data['Transferencia']['banco'] = $bancos[$this->data['Transferencia']['banco']];
                if(trim($this->data['Transferencia']['telefono'])!=''){
                        $credito = array(
                            'id'=>trim(ltrim($this->data['Transferencia']['codigo_telefono'],'0').$this->data['Transferencia']['telefono']), 
                            'tp'=>'CELE'
                        );
                    }else{
                        $credito = array(
                            'id'=>trim($this->data['Transferencia']['cuentaCreditar']), 
                            'tp'=>'CNTA'
                        );
                    }
                $res = $this->TransaccionPendiente->save(
                        array(
                            'TransaccionPendiente' => array(
                        'usuario_id' => $usuario['id'],
                        'tipo' => 'tto',
                        'estatus' => 'pa',
                        'fecha_hora_registro' => date('Y-m-d H:i:s'),
                        'monto' => formatea_monto_toWs($this->data['Transferencia']['monto']),
                        'aprobaciones_requeridas' => $this->data['Transferencia']['aprobaciones_requeridas'],
                        'tipo_rif' => $usuario['tipo_rif'],
                        'rif' => $usuario['rif'],
                        'cuenta_origen' => encriptar($this->data['Transferencia']['cuentaDebitar']),
                        'cuenta_destino' => encriptar($credito['id']),
                        'descripcion' => encriptar($this->data['Transferencia']['descripcion']),
                        'nombre_beneficiario' => encriptar($this->data['Transferencia']['nombreBeneficiario']),
                        'tipo_id_beneficiario' => $this->data['Transferencia']['tipoCedulaRif'],
                        'id_beneficiario' => encriptar($this->data['Transferencia']['nroCedulaRif']),
                        'email_beneficiario' => encriptar($this->data['Transferencia']['emailBeneficiario']),
                        'alias' => isset($this->data['Transferencia']['alias']) ? encriptar($this->data['Transferencia']['alias']) : null,
                        'banco' => isset($id_banco) ? $id_banco : null
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

                if ($this->data['Transferencia']['afiliar'] == 's') {
                     if(trim($this->data['Transferencia']['telefono'])!=''){
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 't', 'tob', $this->data['Transferencia']['alias'], $this->data['Transferencia']['codigo_telefono'].$this->data['Transferencia']['telefono'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id'], $this->data['Transferencia']['codigo_telefono'], $this->data['Transferencia']['telefono'], $id_banco
                        );
                    }else{
                        $error_afiliacion = !$this->Afiliacion->afiliar(
                            $usuario['id'], 't', 'tob', $this->data['Transferencia']['alias'], $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['nombreBeneficiario'], $this->data['Transferencia']['tipoCedulaRif'], $this->data['Transferencia']['nroCedulaRif'], $this->data['Transferencia']['emailBeneficiario'], $this->data['Transferencia']['monto_maximo'], $usuario['tipo_rif'], $usuario['rif'], $this->data['Transferencia']['id']
                        );
                    }  

                    if (!$error_afiliacion && $this->data['Transferencia']['tipoDestinatario'] == 'n') {
                        // Registra en bitacora
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaCreditar'], $this->data['Transferencia']['monto_maximo'], NULL, NULL, 0
                        );

                        // Email Options
                        $this->Email->reset();
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery = 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = desencriptar($usuario['email_principal']);
                        $this->Email->subject = 'Notificacion Afiliacion';
                        $this->Email->sendAs = 'html';

                        // Informacion del correo
                        $this->Email->template = 'afiliacion_producto';
                        $tipo_accion = 'Transferencia';
                        $tipo_afiliacion = 'Terceros Otros Bancos';
                        $nombreBeneficiario = $this->data['Transferencia']['nombreBeneficiario'];
                        $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                        // Envio del correo
                        $this->Email->send();
                    } else {
                        $this->Bitacora->registraBitacora(
                                $usuario['id'], 13, NULL, $this->data['Transferencia']['cuentaDebitar'], $this->data['Transferencia']['monto_maximo'], NULL, $descripcion, 1
                        );
                    }
                } else {
                    $error_afiliacion = false;
                }

                $this->set('pendiente', true);
            }

            $this->set(compact('error', 'error_afiliacion', 'referencia'));

            $this->Session->delete('transferencias.terceros_otros');
        } else {
            $this->redirect('terceros_otros');
        }
    }

    /**
     * Acción para mostrar advertencia de operación realizada
     * 
     */
    function transaccion_advertencia() {

        if (!$this->Session->check('transaccion.advertencia'))
            $this->redirect('/consultas/integral');

        $this->data = $this->Session->read('transaccion.advertencia');

        $this->layout = 'interna';

        $this->set('seccion_actual', $this->data['seccion_actual']);

        $this->render('/elements/transaccion_advertencia');
    }
    
    /**
    * Acción del Listado de operaciones credito inmediato
    */
    function listado_simf(){

        $usuario= $this->Session->read('usuarios.datos');

        /* Para validar el acceso al modulo */
        if(!validarTransaccion(101078)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */            

        // registra los datos del usuario en bitacora
        $this->Bitacora->registraBitacora($usuario['id'], 101078);

        $this->layout = 'interna';
        $this->set('seccion_actual','transf.simf');                      

        if(empty($usuario['tipo_rif'])){
            $total_registros = $this->TransaccionSimf->find('count', array(                    								
                'conditions'=>array(
                    'TransaccionSimf.usuario_id' => $usuario['id'],
                    'TransaccionSimf.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                ),
                'order'=>'TransaccionSimf.fecha_hora_registro desc',                    
                'limit'=>Configure::read('Tamano.paginacion')
                )
            );
        }else{
            $rifOrigen = encriptar(str_pad(desencriptar($usuario['rif']), 12, '0', STR_PAD_LEFT));            
            $total_registros = $this->TransaccionSimf->find('count', array(                    								
                'conditions'=>array(
                    'TransaccionSimf.tipo_documento' => $usuario['tipo_rif'],
                    'TransaccionSimf.documento' => $rifOrigen,
                    'TransaccionSimf.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                ),
                'order'=>'TransaccionSimf.fecha_hora_registro desc',                    
                'limit'=>Configure::read('Tamano.paginacion')
                )
            );
        }                                           

        $offset = 0;

        if(
            isset($this->data['Transferencia']['offset']) &&
            preg_match('/^\d+$/',$this->data['Transferencia']['offset'])
        ){
            $offset = intval($this->data['Transferencia']['offset']);			
        }

        $listado = array();

        if($total_registros){

            if(empty($usuario['tipo_rif'])){
                $listado = $this->TransaccionSimf->find(
                    'all',
                    array(								
                        'conditions'=>array(
                            'TransaccionSimf.usuario_id' => $usuario['id'],
                            'TransaccionSimf.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                        ),
                        'order'=>'TransaccionSimf.fecha_hora_registro desc',
                        'offset'=>$offset,
                        'limit'=>Configure::read('Tamano.paginacion')
                    )
                );
            }else{
                $rifOrigen = encriptar(str_pad(desencriptar($usuario['rif']), 12, '0', STR_PAD_LEFT));
                $listado = $this->TransaccionSimf->find(
                    'all',
                    array(								
                        'conditions'=>array(
                            'TransaccionSimf.tipo_documento' => $usuario['tipo_rif'],
                            'TransaccionSimf.documento' => $rifOrigen,
                            'TransaccionSimf.fecha_hora_registro between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
                        ),
                        'order'=>'TransaccionSimf.fecha_hora_registro desc',
                        'offset'=>$offset,
                        'limit'=>Configure::read('Tamano.paginacion')
                    )
                );
            }                
        }          

        $this->set(compact('listado', 'total_registros'));
    }

    function sanitize_input($data) {
        return filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }


    function lb_transferencia(){
        $this->layout = 'interna';
        $descripcion = _m('lb_acceso');
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
                          'operacion'=>'TR',
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
   





    
}

?>