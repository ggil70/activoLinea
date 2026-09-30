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
class RecargaServiciosController extends AppController
{

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
    var $components = array('Activo', 'Session', 'Email', 'Sms');

    /**
     *
     * @var array Ayudantes utilizados en las vistas de este controlador
     */
    var $helpers = array('Activo', 'Html', 'Session', 'Form', 'Paginator', 'Text', 'Javascript');

    /*** Inicio Recarga Prepago ***/


  function limiteDigitelMovistar()
{
    /** PARA EL MONTO LIMITE EN LA TRANSACCION **/

    // NUEVOS PARAMETROS DIGITEL Y MOVISTAR

    // Declaraci&oacute;n de arrays asociativos
    $montos_digitel = array();
    for ($i = 1; $i <= 9; $i++) {
        $monto = _p("monto_digitel_$i");
        
        // Validaci&oacute;n y cast a float (o int si son enteros puros)
        if (empty($monto) && $monto !== '0') {
            $monto = 0; // Valor por defecto si estÃ¡ vacÃ­o o nulo
        }

        // Aplicar formato a dos decimales
        $monto_formateado = number_format($monto, 2, ',', ''); // Ej: 10 -> "10,00"

        // **IMPORTANTE: Usar el monto formateado como clave y valor**
        $montos_digitel[$monto_formateado] = $monto_formateado;
    }

    // Declaraci&oacute;n de arrays asociativos
    $montos_movistar = array();
    for ($i = 9; $i <= 16; $i++) {
        $monto = _p("monto_movistar_$i");

        // Validaci&oacute;n y cast a float
        if (empty($monto) && $monto !== '0') {
            $monto = 0;
        }
        // Aplicar formato a dos decimales
        $monto_formateado = number_format($monto, 2, ',', '');

        // **IMPORTANTE: Usar el monto formateado como clave y valor**
        $montos_movistar[$monto_formateado] = $monto_formateado;
    }

    // NUEVOS PARAMETROS MOVILNET

    $montos_movilnet = array();

    for ($i = 1; $i <= 8; $i++) {
        $monto = _p("montos_movilnet_$i");

        $monto_limpio = trim((string)$monto);

        $monto_limpio = str_replace('.', '', $monto_limpio);
        $monto_limpio = str_replace(',', '.', $monto_limpio);

        if (empty($monto_limpio) && $monto_limpio !== '0') {
            $monto_limpio = '0';
        }

        $monto_formateado = number_format((float)$monto_limpio, 2, ',', '');
        
        $montos_movilnet[$monto_formateado] = $monto_formateado;
    }

    // Obtener mÃƒÂ­nimo y mÃƒÂ¡ximo
    $min_mov = _p("recarga_minima_movilnet");
    $max_mov = _p("recarga_maxima_movilnet");
    $minimo_movilnet_php = !empty($min_mov) ? $min_mov : '0,00';
    $maximo_movilnet_php = !empty($max_mov) ? $max_mov : '0,00';

    // El resto de tu l&oacute;gica para mínimo y máximo está bien
    $minimo_digitel_php = !empty($montos_digitel) ? reset($montos_digitel) : '0,00';
    $maximo_digitel_php = !empty($montos_digitel) ? end($montos_digitel) : '0,00';

    $minimo_movistar_php = !empty($montos_movistar) ? reset($montos_movistar) : '0,00';
    $maximo_movistar_php = !empty($montos_movistar) ? end($montos_movistar) : '0,00';

    return array(
        'montos_digitel'      => $montos_digitel,
        'montos_movistar'     => $montos_movistar,
        'montos_movilnet'     => $montos_movilnet,
        'minimo_digitel_php'  => $minimo_digitel_php,
        'maximo_digitel_php'  => $maximo_digitel_php,
        'minimo_movistar_php' => $minimo_movistar_php,
        'maximo_movistar_php' => $maximo_movistar_php,
        'minimo_movilnet_php' => $minimo_movilnet_php,
        'maximo_movilnet_php' => $maximo_movilnet_php,
    );
}

    /**
     *Acci&oacute;n inicial para el pago de servicos
     */
    function recarga_servicios()
    {
        /* Para validar el acceso al modulo */
        if (!validarTransaccion(100015))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $usuario = $this->Session->read('usuarios.datos');
        $this->layout = 'interna';
        $this->set('seccion_actual', 'recarga.servicios');
        $cuentas = $this->Activo->buscarCuentasSaldos();

        if (!empty($this->data['RecargaServicios'])) {

            //
            // Para usuario jurídicos operadores, obtener su configuraci&oacute;n para este tipo de transacci&oacute;n
            if (
                $usuario['rol_juridico_id'] == 3 ||
                $usuario['rol_juridico_id'] == 5
            ) {
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                    $usuario['id'],
                    100015,
                    $this->data['RecargaServicios']['cuentaDebitar']
                );




                if (!$transaccion_detalle) {
                    $this->RecargaServicios->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                }
            }

            if (empty($this->RecargaServicios->validationErrors)) {

                if (isset($transaccion_detalle)) {
                    $this->data['RecargaServicios']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if (is_numeric($transaccion_detalle['limite'])) {
                        $this->data['RecargaServicios']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['RecargaServicios']['monto_limite_usuario'] = '';
                    }
                }

                $this->RecargaServicios->set($this->data);

                if ($this->RecargaServicios->validates(array('fieldList' => array('cuentaDebitar', 'tipoDestinatario')))) {

                    $this->Session->write('recarga_servicios', $this->data);
                    //Comentado por solicitud en el proyecto movistar
//                                    if($this->data['RecargaServicios']['tipoDestinatario'] == 'a'){
                    $this->redirect('afiliados');
                    //                                    } else {              
//                                        $this->redirect('noafiliados');
//                                    }
                }
            }

        }
        //fin                                       

        $error = null;
        $this->set(compact('cuentas', 'error'));
    }

    /**
     * Acci&oacute;n para realizar Recarga Prepago con Clientes Afiliados
     */
    function afiliados()
    {
        /* Para validar el acceso al modulo */
        if (!validarTransaccion(100015))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $usuario = $this->Session->read('usuarios.datos');
        $recarga = $this->Session->read('recarga_servicios');

        $montoMinimo = 0;
        $montoMaximo = 0;
        $error = null;
        $tipo_operacion = null;
        $total_registros = 0;
        $resultados = array();
        $tipo_afiliacion = 'r';
        

        $this->set('seccion_actual', 'recarga.servicios');

        // Obtener el límite de parámetros
        if (empty($usuario['rif'])) {
            $limite_config = _p('limite_internas_terceros');
            $monto_limite_usuario = "";
        } else {
            $limite_config = _p('limite_internas_terceros_j');
            //Si es master unico no valida limite por usuario solo el general
            if ($usuario['rol_juridico_id'] != 2) {
                $monto_limite_usuario = $recarga['RecargaServicios']['monto_limite_usuario'];
            } else {
                $monto_limite_usuario = "";
            }
        }

        // Nueva funcion de limites
        $limiteDigMov = $this->limiteDigitelMovistar();
        
        // Nueva funcion de limites

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

        $this->set(compact('monto_limite', 'monto_limite_usuario', 'limiteDigMov'));
        /** PARA EL MONTO LIMITE EN LA TRANSACCION **/

        if (_p('sms.otp.activo') == '0') {
            $validacion_otp['validado'] = true;
        } else {
            $validacion_otp = $this->Session->read('seguridad.validar_otp');
        }

        if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
            if (_p('sms.otp.activo') == '0') {
                $this->data['pasos'] = array(
                    'Solicitud de Informaci&oacute;n',
                    'Confirmaci&oacute;n',
                    'Resultado de Operaci&oacute;n'
                );
                $this->data['paso_final'] = 2;
            } else {
                $this->data['pasos'] = array(
                    'Solicitud de Informaci&oacute;n',
                    'Confirmaci&oacute;n',
                    'C&oacute;digo de Autorizaci&oacute;n',
                    'Resultado de Operaci&oacute;n'
                );
                $this->data['paso_final'] = 3;
            }
        } else {
            $this->data['pasos'] = array(
                'Solicitud de Informaci&oacute;n',
                'Confirmaci&oacute;n',
                'Resultado de Operaci&oacute;n'
            );
            $this->data['paso_final'] = 2;
        }
        
        if (empty($this->data['RecargaServicios'])) {
            $this->data = $recarga;

        } elseif (!empty($this->data['RecargaServicios']) && !isset($this->data['RecargaServicios']['paginando'])) {
           
            $this->data['RecargaServicios']['tipoDestinatario'] = 'a';
            /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
            $this->data['RecargaServicios']['monto_limite'] = $monto_limite;
            $this->data['RecargaServicios']['limite_total'] = $limite_total;
            $this->data['RecargaServicios']['cuentaDebitar'] = $recarga['RecargaServicios']['cuentaDebitar'];

            if (isset($recarga['RecargaServicios']['monto_limite_usuario'])) {
                $this->data['RecargaServicios']['monto_limite_usuario'] = $recarga['RecargaServicios']['monto_limite_usuario'];
            }

            /** PARA EL MONTO LIMITE EN LA TRANSACCION **/

            // Para usuario jur?dicos operadores, obtener su configuraci&oacute;n para este tipo de transacci&oacute;n
            if (
                $usuario['rol_juridico_id'] == 3 ||
                $usuario['rol_juridico_id'] == 5
            ) {
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                    $usuario['id'],
                    100015,
                    $this->data['RecargaServicios']['cuentaDebitar']
                );

                if (!$transaccion_detalle) {
                    $this->RecargaServicios->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                }
            }

            if (empty($this->RecargaServicios->validationErrors)) {
                
                if (isset($transaccion_detalle)) {
                    $this->data['RecargaServicios']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if (is_numeric($transaccion_detalle['limite'])) {
                        $this->data['RecargaServicios']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);

                    } else {
                        $this->data['RecargaServicios']['monto_limite_usuario'] = '';
                    }
                }     
                
                //Para recargas Movilnet                                        
                if (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sm') {
                    $id_afiliacion = isset($this->data['RecargaServicios']['id']) ? $this->data['RecargaServicios']['id'] : $this->data['RecargaServicios']['numservicio'];
                    $this->obtenerInfo($id_afiliacion, trim($this->data['RecargaServicios']['tipoOperacion']));
                    $montoMinimo = formatea_monto_fromWs($limiteDigMov['minimo_movilnet_php']);
                    $montoMaximo = formatea_monto_fromWs($limiteDigMov['maximo_movilnet_php']);

                    $datos_afiliacion = $this->Afiliacion->find('first', array('fields' => '*', 'conditions' => (array('id' => $this->data['RecargaServicios']['id'], 'tipo_afiliacion' => $tipo_afiliacion))));
                    if (!empty($datos_afiliacion)) {
                        $this->data['RecargaServicios']['nombreBeneficiario'] = desencriptar($datos_afiliacion['Afiliacion']['nombre_beneficiario']);
                        $this->data['RecargaServicios']['numeroContrato'] = $datos_afiliacion['Afiliacion']['codigo_telefono'] . desencriptar($datos_afiliacion['Afiliacion']['numero_cuenta']);
                        $this->data['RecargaServicios']['tipoServicio'] = $datos_afiliacion['Afiliacion']['tipo_servicio'];
                    } else {
                        $this->data['RecargaServicios']['numeroContrato'] = $this->data['RecargaServicios']['numservicio'];
                    }

                    $montoMovt = isset($this->data['RecargaServicios']['montoFormMovt']) ? trim($this->data['RecargaServicios']['montoFormMovt']) : '';
                    $descripcionMovt = isset($this->data['RecargaServicios']['descripcion']) ? trim($this->data['RecargaServicios']['descripcion']) : '';
                    $this->data['RecargaServicios']['monto'] = $montoMovt;
                    $this->data['RecargaServicios']['descripcion'] = $descripcionMovt;
                    
                    $this->RecargaServicios->set($this->data);

                    if (!empty($montoMovt) && !empty($descripcionMovt) && $this->RecargaServicios->validates(array('fieldList' => array('monto', 'descripcion')))) {
                        $this->data['RecargaServicios']['afiliar'] = 'n';
                        $this->Session->write('recarga_servicios', $this->data);

                        $this->Session->delete('transaccion.advertencia');

                        // En caso de ser un usuario natural o master unico
                        if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
                            if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['RecargaServicios'], 100015)) {

                                $this->data['titulo'] = 'Recarga Servicios';
                                $this->data['mensaje_alerta'] = 'pagotdc.alerta';
                                $this->data['url_volver'] = '/recarga_servicios/afiliados';
                                $this->data['url_continuar'] = '/recarga_servicios/recarga_servicios_confirmacion';
                                $this->data['seccion_actual'] = 'recarga.servicios';

                                $this->Session->write('transaccion.advertencia', $this->data);
                                $this->redirect('transaccion_advertencia');
                            }
                        }

                        $this->redirect('recarga_servicios_confirmacion');
                    } else {
                        if (empty($montoMovt)) {
                            $this->RecargaServicios->validationErrors['montoFormMovt'] = _m('seleccion.requerida', 'el monto');
                            $this->RecargaServicios->validationErrors['monto'] = _m('seleccion.requerida', 'el monto');
                        }
                        if (empty($descripcionMovt)) {
                            $this->RecargaServicios->validationErrors['descripcion'] = _m('campo.requerido', 'la descripción');
                        }
                    }
                }
                //Para recargas Digitel  AQUI DEBUG PARA PAGOS DIGITEL  
                elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sd') {
                    $montoMinimo = formatea_monto_fromWs($limiteDigMov['minimo_digitel_php']);
                    $montoMaximo = formatea_monto_fromWs($limiteDigMov['maximo_digitel_php']);
                    if ($this->RecargaServicios->validates(array('fieldList' => array('montoFormDig')))) {
                        
                        $datos_afiliacion = $this->Afiliacion->find('first', array('fields' => '*', 'conditions' => (array('id' => $this->data['RecargaServicios']['id'], 'tipo_afiliacion' => $tipo_afiliacion))));

                        $this->data['RecargaServicios']['nombreBeneficiario'] = desencriptar($datos_afiliacion['Afiliacion']['nombre_beneficiario']);
                        $this->data['RecargaServicios']['numeroContrato'] = $datos_afiliacion['Afiliacion']['codigo_telefono'] . desencriptar($datos_afiliacion['Afiliacion']['numero_cuenta']);
                        $this->data['RecargaServicios']['afiliar'] = 'n';
                        $this->Session->write('recarga_servicios', $this->data);
                        $this->Session->delete('transaccion.advertencia');
                        // En caso de ser un usuario natural o master unico
                        if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2) {
                            if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['RecargaServicios'], 100015)) {

                                $this->data['titulo'] = 'Recarga Servicios';
                                $this->data['mensaje_alerta'] = 'pagotdc.alerta';
                                $this->data['url_volver'] = '/recarga_servicios/afiliados';
                                $this->data['url_continuar'] = '/recarga_servicios/recarga_servicios_confirmacion';
                                $this->data['seccion_actual'] = 'recarga.servicios';

                                $this->Session->write('transaccion.advertencia', $this->data);
                                $this->redirect('transaccion_advertencia');
                            }
                        }

                       

                        $this->redirect('recarga_servicios_confirmacion');
                    }
                }//Para recargas Movistar    
                elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'st') {
                  
                    $montoMinimo = formatea_monto_fromWs($limiteDigMov['minimo_movistar_php']);
                    $montoMaximo = formatea_monto_fromWs($limiteDigMov['minimo_movistar_php']);

                    if ($this->RecargaServicios->validates(array('fieldList' => array('monto')))) {

                        $modalidad = 0;
                        $tipo_servicio = $this->data['RecargaServicios']['tipoServicio'];

                        $datos_afiliacion = $this->Afiliacion->find('first', array('fields' => '*', 'conditions' => (array('id' => $this->data['RecargaServicios']['id'], 'tipo_afiliacion' => $tipo_afiliacion))));
                        $this->data['RecargaServicios']['nombreBeneficiario'] = desencriptar($datos_afiliacion['Afiliacion']['nombre_beneficiario']);
                        $this->data['RecargaServicios']['numeroContrato'] = $datos_afiliacion['Afiliacion']['codigo_telefono'] . desencriptar($datos_afiliacion['Afiliacion']['numero_cuenta']);
                        $this->data['RecargaServicios']['afiliar'] = 'n';
                        $this->Session->write('recarga_servicios', $this->data);
                        $this->Session->delete('transaccion.advertencia');

                        // En caso de ser un usuario natural o master unico
                        if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2) {
                            if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['RecargaServicios'], 100015)) {

                                $this->data['titulo'] = 'Recarga Servicios';
                                $this->data['mensaje_alerta'] = 'pagotdc.alerta';
                                $this->data['url_volver'] = '/recarga_servicios/afiliados';
                                $this->data['url_continuar'] = '/recarga_servicios/recarga_servicios_confirmacion';
                                $this->data['seccion_actual'] = 'recarga.servicios';

                                $this->Session->write('transaccion.advertencia', $this->data);
                                $this->redirect('transaccion_advertencia');
                            }
                        }
                        $this->redirect('recarga_servicios_confirmacion');
                    }
                } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'si') {



                    $montoMinimo = formatea_monto_fromWs(_p('recarga_minima_inter_pre'));
                    $montoMaximo = formatea_monto_fromWs(_p('recarga_maxima_inter_pre'));



                    if ($this->RecargaServicios->validates(array('fieldList' => array('monto')))) {

                        $modalidad = 0;
                        $tipo_servicio = $this->data['RecargaServicios']['tipoServicio'];

                        $datos_afiliacion = $this->Afiliacion->find('first', array('fields' => '*', 'conditions' => (array('id' => $this->data['RecargaServicios']['id'], 'tipo_afiliacion' => $tipo_afiliacion))));
                        $this->data['RecargaServicios']['nombreBeneficiario'] = desencriptar($datos_afiliacion['Afiliacion']['nombre_beneficiario']);
                        $this->data['RecargaServicios']['numeroContrato'] = $datos_afiliacion['Afiliacion']['codigo_telefono'] . desencriptar($datos_afiliacion['Afiliacion']['numero_cuenta']);
                        $this->data['RecargaServicios']['afiliar'] = 'n';
                        $this->Session->write('recarga_servicios', $this->data);
                        $this->Session->delete('transaccion.advertencia');

                        // En caso de ser un usuario natural o master unico
                        if (empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id'] == 2) {
                            if ($this->Bitacora->transaccionIgualAnterior($usuario['id'], $this->data['RecargaServicios'], 100015)) {

                                $this->data['titulo'] = 'Recarga Servicios';
                                $this->data['mensaje_alerta'] = 'pagotdc.alerta';
                                $this->data['url_volver'] = '/recarga_servicios/afiliados';
                                $this->data['url_continuar'] = '/recarga_servicios/recarga_servicios_confirmacion';
                                $this->data['seccion_actual'] = 'recarga.servicios';

                                $this->Session->write('transaccion.advertencia', $this->data);
                                $this->redirect('transaccion_advertencia');
                            }
                        }
                        $this->redirect('recarga_servicios_confirmacion');
                    }
                }
            }

        }
        //fin

        $servicios = $this->Afiliacion->obtenerListaAfiliados($usuario['id'], $tipo_afiliacion, array('sd', 'st', 'si','sm'));
        if ($servicios) {
            if (count($servicios) > 0) {
                //Validar que no se incluya el servicio de 
                //  REcarga /  movilstar TV
                /////////       

                $auxServicio = $servicios;
                $servicios = array();

                $i = 0;
                foreach ($auxServicio as $a) {
                    //Validaci&oacute;n para excluir pago de movistart tv en recarga.

                    if ((trim($a['Afiliacion']['tipo_operacion']) == 'st') && (trim($a['Afiliacion']['tipo_servicio']) == 'rtv')) {
                        $swTv = 1;
                    } else {
                        $arreglo = array(
                            'Afiliacion' => array(
                                'id' => $a['Afiliacion']['id'],
                                'usuario_id' => $a['Afiliacion']['usuario_id'],
                                'tipo_afiliacion' => $a['Afiliacion']['tipo_afiliacion'],
                                'tipo_operacion' => $a['Afiliacion']['tipo_operacion'],
                                'alias' => $a['Afiliacion']['alias'],
                                'codigo_telefono' => $a['Afiliacion']['codigo_telefono'],
                                'banco' => $a['Afiliacion']['banco'],
                                'numero_cuenta' => $a['Afiliacion']['numero_cuenta'],
                                'nombre_beneficiario' => $a['Afiliacion']['nombre_beneficiario'],
                                'tipo_cedula' => $a['Afiliacion']['tipo_cedula'],
                                'cedula' => $a['Afiliacion']['cedula'],
                                'email' => $a['Afiliacion']['email'],
                                'monto_maximo' => $a['Afiliacion']['monto_maximo'],
                                'tipo_rif' => $a['Afiliacion']['tipo_rif'],
                                'rif' => $a['Afiliacion']['rif'],
                                'numero_contrato' => $a['Afiliacion']['numero_contrato'],
                                'fecha_contrato' => $a['Afiliacion']['fecha_contrato'],
                                'tipo_servicio' => $a['Afiliacion']['tipo_servicio'],
                                'telefono' => $a['Afiliacion']['telefono'],
                                'numero_cuenta_receptora' => $a['Afiliacion']['numero_cuenta_receptora'],
                                'numero_cuenta_ahorro' => $a['Afiliacion']['numero_cuenta_ahorro'],
                            )
                        );
                        array_push($servicios, $arreglo);
                        $i = $i + 1;
                    }
                }
            }
        }

        //Fin Validaci&oacute;n para excluir pago de movistart tv en recarga.




        if ($servicios) {

            $total_registros = count($servicios);


            // Empieza c&oacute;digo para paginar
            $offset = 0;

            if (isset($this->data['RecargaServicios']['offset']) && preg_match('/^\d+$/', $this->data['RecargaServicios']['offset'])) {
                $offset = intval($this->data['RecargaServicios']['offset']);
            }

            $resultados = array_slice($servicios, $offset, 5, true);
            
        }
        $this->layout = 'interna';
        $this->set(compact('cuentas', 'tipo_operacion', 'servicios', 'error', 'total_registros', 'resultados', 'recarga', 'montoMinimo', 'montoMaximo', 'limiteDigMov'));

    }

    /**
     * Acci&oacute;n para realizar Recarga Prepago con Clientes Afiliados
     */
    function noafiliados()
    {

        /* Para validar el acceso al modulo */
        if (!validarTransaccion(100015))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $this->layout = 'interna';
        $usuario = $this->Session->read('usuarios.datos');
        $recarga = $this->Session->read('recarga_servicios');
        $tipo_operacion = $this->Afiliacion->buscarSubTipos('r');
        $prefijos_telefonicos = $this->PrefijosTelefonicos->listar_prefijos();

        $this->set('seccion_actual', 'recarga.servicios');

        // Obtener el límite de parámetros
        if (empty($usuario['rif'])) {
            $limite_config = _p('limite_internas_terceros');
            $monto_limite_usuario = "";
        } else {
            $limite_config = _p('limite_internas_terceros_j');
            //Si es master unico no valida limite por usuario solo el general
            if ($usuario['rol_juridico_id'] != 2) {
                $monto_limite_usuario = $recarga['RecargaServicios']['monto_limite_usuario'];
            } else {
                $monto_limite_usuario = "";
            }
        }

        /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
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
        $this->set(compact('monto_limite', 'limite_total', 'monto_limite_usuario'));

        /** PARA EL MONTO LIMITE EN LA TRANSACCION **/

        // para los limites

        $tipo_afiliacion = 'r';

        $validacion_otp = $this->Session->read('seguridad.validar_otp');
        if ((empty($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5))) && ($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
            $this->data['pasos'] = array(
                'Solicitud de Informaci&oacute;n',
                'Confirmaci&oacute;n',
                'C&oacute;digo de Autorizaci&oacute;n',
                'Resultado de Operaci&oacute;n'
            );
            $this->data['paso_final'] = 3;
        } else {
            $this->data['pasos'] = array(
                'Solicitud de Informaci&oacute;n',
                'Confirmaci&oacute;n',
                'Resultado de Operaci&oacute;n'
            );
            $this->data['paso_final'] = 2;
        }

        if (empty($this->data['RecargaServicios'])) {
            $this->data = $recarga;
        } else {

            /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
            $this->data['RecargaServicios']['monto_limite'] = $monto_limite;
            $this->data['RecargaServicios']['limite_total'] = $limite_total;
            $this->data['RecargaServicios']['cuentaDebitar'] = $recarga['RecargaServicios']['cuentaDebitar'];
            $this->data['RecargaServicios']['tipoDestinatario'] = 'na';

            /** PARA EL MONTO LIMITE EN LA TRANSACCION **/

            // Para usuario jurídicos operadores, obtener su configuraci&oacute;n para este tipo de transacci&oacute;n
            if (
                $usuario['rol_juridico_id'] == 3 ||
                $usuario['rol_juridico_id'] == 5
            ) {
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                    $usuario['id'],
                    100015,
                    $this->data['RecargaServicios']['cuentaDebitar']
                );

                if (!$transaccion_detalle) {
                    $this->RecargaServicios->validationErrors['cuentaDebitar'] = _m('usuario.no.autorizado');
                }
            }

            if (empty($this->RecargaServicios->validationErrors)) {

                if (isset($transaccion_detalle)) {
                    $this->data['RecargaServicios']['aprobaciones_requeridas'] = $transaccion_detalle['aprobaciones'];
                    if (is_numeric($transaccion_detalle['limite'])) {
                        $this->data['RecargaServicios']['monto_limite_usuario'] = formatea_monto_fromWs($transaccion_detalle['limite']);
                    } else {
                        $this->data['RecargaServicios']['monto_limite_usuario'] = '';
                    }
                }

                $this->RecargaServicios->set($this->data);

                //Para recargas Movilnet                                        
                if (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sm' || trim($this->data['RecargaServicios']['tipoOperacion']) == 'sc' || trim($this->data['RecargaServicios']['tipoOperacion']) == 'scn') {

                    $this->obtenerInfo($this->data['RecargaServicios']['numServicio'], trim($this->data['RecargaServicios']['tipoOperacion']));
                    $this->data['RecargaServicios']['numeroContrato'] = $this->data['RecargaServicios']['numservicio'];
                    $this->data['RecargaServicios']['monto'] = $this->data['RecargaServicios']['montoFormMovt'];
                    $this->RecargaServicios->set($this->data);

                    if ($this->RecargaServicios->validates(array('fieldList' => array('numServicio', 'monto', 'monto_maximo')))) {

                        if (!empty($this->data['RecargaServicios']['tipo_operacion'])) {

                            $valorPrepago = array('IN984USR1' => $usuario['cedula'], 'IN984ARE' => substr(desencriptar($this->data['RecargaServicios']['numservicio']), 0, 4), 'IN984TLF' => substr(desencriptar($this->data['RecargaServicios']['numservicio']), 4), 'IN984TYPE' => 10, 'IN984SER' => $modalidad);

                            $datosPrepago = $this->Activo->validarPrepago(trim($this->data['RecargaServicios']['tipoOperacion']), $valorPrepago);
                            $error = $datosPrepago['IN098401Return']['error']['ERNU01'];

                            if ($error != 0) {
                                $this->RecargaServicios->validationErrors['numservicio'] = _m('valor.invalido', 'el número de Teléfono');
                            }
                        }

                        $this->Session->write('recarga_servicios', $this->data);
                        $this->redirect('recarga_servicios_confirmacion');
                    }
                    //Para recargas Digitel    
                } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sd') {
                        
                    if ($this->RecargaServicios->validates(array('fieldList' => array('monto')))) {

                        $this->data['RecargaServicios']['numeroContrato'] = $this->data['RecargaServicios']['codigo_telefono'] . $this->data['RecargaServicios']['numservicio'];
                        $this->Session->write('recarga_servicios', $this->data);
                        $this->redirect('recarga_servicios_confirmacion');
                    }
                }
            }

        }

        if (!empty($usuario['rif'])) {
            $tipo = $usuario['tipo_rif'];
            $documento = $usuario['rif'];
        }

        $this->set(compact('tipo_operacion', 'error', 'recarga', 'prefijos_telefonicos'));

    }

    /**
     * Acci&oacute;n para obtener informaci&oacute;n de los servicios afiliados por el cliente        
     * @param string $servicio 
     * @param string $operacion 
     */
    function obtenerInfo($servicio, $operacion)
    {
        $this->layout = 'ajax';

        if (!empty($servicio) && $this->Session->check('usuarios.datos')) {
            $usuario = $this->Session->read('usuarios.datos');
            $response = array();

            /** Para identificar si es un usuario juridico **/
            if ($usuario['tipo_rif']) {
                $documento_identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
            } else {
                $documento_identificacion = desencriptar($usuario['cedula']);
            }
            /** Para identificar si es un usuario juridico **/

            if ($operacion == 'sc') {
                $modalidad = 10;
            } elseif ($operacion == 'sm') {
                $modalidad = 30;
            } elseif ($operacion == 'scn') {
                $modalidad = 20;
            }

            $servicios = $this->Afiliacion->find(
                'first',
                array(
                    'fields' => array('numero_cuenta'),
                    'conditions' => array(
                        'Afiliacion.id' => $servicio
                    )
                ),
                false
            );

            $res = '';
            if ($operacion == 'scn') {
                $res = $this->Activo->invoca(
                    'IN098401',
                    array(
                        'req' => array(
                            'IN984USR1' => $documento_identificacion,
                            'IN984BVDD' => date('d'),
                            'IN984BVDM' => date('m'),
                            'IN984BVDY' => date('y'),
                            'IN984SER' => $modalidad,
                            'IN984CON' => desencriptar($servicios['Afiliacion']['numero_cuenta']),
                            'IN984TYPE' => 10,
                            'IN984ABA' => 'X'
                        )
                    )
                );
            } else {
                $res = $this->Activo->invoca(
                    'IN098401',
                    array(
                        'req' => array(
                            'IN984USR1' => $documento_identificacion,
                            'IN984BVDD' => date('d'),
                            'IN984BVDM' => date('m'),
                            'IN984BVDY' => date('y'),
                            'IN984SER' => $modalidad,
                            'IN984ARE' => substr(desencriptar($servicios['Afiliacion']['numero_cuenta']), 0, 4),
                            'IN984TLF' => substr(desencriptar($servicios['Afiliacion']['numero_cuenta']), 4),
                            'IN984TYPE' => 10,
                            'IN984ABA' => 'X'
                        )
                    )
                );
            }

            if (!empty($res['IN098801Return']['error'])) {
                $error = intval($res['IN098801Return']['error']['ERRNUM']);
                $error = ($error == 0) ? $error : intval($res['IN098801Return']['error']['ERNU01']);
                $descripcion = $res['IN098801Return']['error']['ERNU01'] . ' - ' . $res['IN098801Return']['error']['ERDS01'];

                if ($error == 4656 || $error == 4668 || $error == 4684) {
                    $errorMovistar = _m('servicio.noexiste', null, true);
                } else if ($error == 4689 || $error == 4690 || $error == 4691 || $error == '0999' || $error == '0080') {
                    $errorMovistar = _m('servicio.nocomunicacion', null, true);
                } else if ($error == 4614 || $error == 'V006' || $error == 'V007' || $error == 'V008') {
                    $errorMovistar = _m('servicio.invalido', null, true);
                } else if ($error == 4613 || $error == 4603 || $error == 4615 || $error == 4630 || $error == 4655 || $error == 4658 || $error == '46N7') {
                    $errorMovistar = _m('servicio.valor.invalido', null, true);
                } else if ($error == '46P4' || $error == '46P9') {
                    $errorMovistar = _m('operacion.excedido', null, true);
                } else {
                    $errorMovistar = _m('error.fatal', null, true);
                }

                $response = array(
                    'error_servicio' => $errorMovistar
                );
                $this->log('Consulta de Servicios Movistar - ' . $descripcion, LOG_ERR);
            } elseif (!empty($res['IN098801Return']['IN098801'])) {
                $response = array(
                    'tipo_pago_v' => formatea_monto_fromWs($res['IN098401Return']['IN098401']['IN984EXP']),
                    'tipo_pago_a' => formatea_monto_fromWs($res['IN098401Return']['IN098401']['IN984AVL']),
                    'tipo_pago_t' => formatea_monto_fromWs($res['IN098401Return']['IN098401']['IN984AMT'])
                );
            } else {
                $response = array(
                    'error_servicio' => -1
                );
                $this->log('Consulta de Servicios Movistar - Servicio IN098801 sin respuesta', LOG_ERR);
            }

            $this->set('response', $response);
        }

    }

    /**
     * Acci&oacute;n para obtener el monto de un servicio  
     * @param string $servicio
     */
    function obtenerMonto($servicio)
    {

        $this->layout = 'ajax';
        $usuario = $this->Session->read('usuarios.datos');
        $response = array();

        if (!empty($servicio)) {

            $afiliacion = $this->Afiliacion->find(
                'first',
                array(
                    'conditions' => array(
                        'usuario_id' => $usuario['id'],
                        'id' => $servicio
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
     *Acci&oacute;n para la confirmaci&oacute;n del pago de servicios
     **/
    function recarga_servicios_confirmacion()
    {

        $this->layout = 'interna';

        $this->set('seccion_actual', 'recarga.servicios');
        $this->set('servicios', $this->Afiliacion->buscarTiposServicios());
        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->read('recarga_servicios');
        $modalidad = 0;

        /** Para identificar si es un usuario juridico **/
        if ($usuario['tipo_rif']) {
            $documento_identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
        } else {
            $documento_identificacion = desencriptar($usuario['cedula']);
        }
        /** Para identificar si es un usuario juridico **/

        if ($this->Session->check('recarga_servicios')) {
            $this->data = $this->Session->read('recarga_servicios');

            if (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sc') {
                $this->data['RecargaServicios']['operacion'] = 'CANTV';
                $modalidad = 10;
            } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sm') {
                $this->data['RecargaServicios']['operacion'] = 'MOVILNET';
                $modalidad = 30;
            } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sd') {
                $this->data['RecargaServicios']['operacion'] = 'Recarga Digitel';
            } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'scn') {
                $this->data['RecargaServicios']['operacion'] = 'CANTV.NET';
                $modalidad = 20;
            } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'st') {
                $this->data['RecargaServicios']['operacion'] = 'Movistar';
                
            } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'si') {
                $this->data['RecargaServicios']['operacion'] = 'Recarga Inter';
            }

            $tipo_afiliacion = 'r';
            $error = 0;

            $this->set(compact('error'));

        } else {
            $this->redirect('recarga_servicios');
        }

    }


    /**
     * Acci&oacute;n para el resultado del pago de servicios, ejecuta el pago
     */
    function recarga_servicios_resultado()
    {

        /* Para validar el acceso al modulo */
        if (!validarTransaccion(100015))
            $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $this->set('seccion_actual', 'recarga.servicios');
        $usuario = $this->Session->read('usuarios.datos');
        $estado = $this->Session->read('recarga_servicios');
        $confirmacion = "";
        $referencia = "";
        $modalidad = 0;
        $limiteDigMov = $this->limiteDigitelMovistar();






        $this->layout = 'interna';

        if ($this->Session->check('recarga_servicios')) {

            $this->data = $this->Session->read('recarga_servicios');
            
            $tipo_servicio = $this->data['RecargaServicios']['tipoServicio'];
            $tipo_operacion = trim($this->data['RecargaServicios']['tipoOperacion']);
            $this->set('servicios', $this->Afiliacion->buscarTiposServicios());
            $this->set('tipoOperacion', trim($this->data['RecargaServicios']['tipoOperacion']));
            $tipo_transaccion_pendiente = '';
            
            if ($tipo_operacion == 'sc') {
                $this->data['RecargaServicios']['operacion'] = 'CANTV';
                $modalidad = 10;
                $tipo_transaccion_pendiente = 'rcn';
                $ayuda = '';
                $multiplo = '';
                $recarga_minima = '';
                $recarga_maxima = '';
            } elseif ($tipo_operacion == 'sm') {
                $this->data['RecargaServicios']['operacion'] = 'MOVILNET';
                $modalidad = 30;
                $tipo_transaccion_pendiente = 'rcn';
                $ayuda = '';
                $multiplo = '';
                $recarga_minima = $limiteDigMov['minimo_movilnet_php'];
                $recarga_maxima = $limiteDigMov['maximo_movilnet_php'];
            } elseif ($tipo_operacion == 'scn') {
                $this->data['RecargaServicios']['operacion'] = 'CANTV.NET';
                $modalidad = 20;
                $tipo_transaccion_pendiente = 'rcn';
                $ayuda = '';
                $multiplo = '';
                $recarga_minima = '';
                $recarga_maxima = '';
            } elseif ($tipo_operacion == 'sd') {
                $this->data['RecargaServicios']['operacion'] = 'Recarga Digitel';
                $tipo_transaccion_pendiente = 'rcd';
                $ayuda = '121';
                // $multiplo = _p('multiplo.digitel');
                $recarga_minima = $limiteDigMov['minimo_digitel_php'];
                $recarga_maxima = $limiteDigMov['maximo_digitel_php'];
            }elseif ($tipo_operacion == 'st') {
                $this->data['RecargaServicios']['operacion'] = 'Movistar';
                $tipo_transaccion_pendiente = 'rcm';
                $ayuda = '811';
                // $multiplo = _p('multiplo.movistar');
                $recarga_minima = $limiteDigMov['minimo_movistar_php'];
                $recarga_maxima = $limiteDigMov['maximo_movistar_php'];
            } elseif ($tipo_operacion == 'si') {
                $this->data['RecargaServicios']['operacion'] = 'Recarga Inter';
                $tipo_transaccion_pendiente = 'rsi';
                $recarga_minima = _p('recarga_minima_inter_pre');
                $recarga_maxima = _p('recarga_maxima_inter_pre');
            }

            /** Para identificar si es un usuario juridico **/
            if ($usuario['tipo_rif']) {
                $documento_identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
            } else {
                $documento_identificacion = $usuario['tipo_cedula'] . str_pad(desencriptar($usuario['cedula']), 9, '0', STR_PAD_LEFT);
            }
            /** Para identificar si es un usuario juridico **/

            $res = '';

            
            // Identificar tipo de usuario (natural o jurídico)         
            if (empty($usuario['tipo_rif']) || $usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {

                if (_p('sms.otp.activo') == '0' && $this->data['RecargaServicios']['tipoDestinatario'] == 'a') {
                    $validacion_otp['validado'] = true;
                } else {
                    $validacion_otp = $this->Session->read('seguridad.validar_otp');
                }

                /*if (($validacion_otp == null || !isset($validacion_otp['validado']) || !$validacion_otp['validado'])) {
                    if ($this->data['RecargaServicios']['tipoDestinatario'] == 'a') {
                        $url_volver = '/recarga_servicios/afiliados';
                    } else {
                        $url_volver = '/recarga_servicios/noafiliados';
                    }

                    $this->Session->write(
                        'seguridad.validar_otp',
                        array(
                            'url_aceptar' => '/recarga_servicios/recarga_servicios_resultado',
                            'url_volver' => $url_volver,
                            'layout' => 'interna',
                            'titulo' => 'Recarga Servicios Prepago',
                            'pasos' => $estado['pasos'],
                            'paso_actual' => 2,
                            'seccion_actual' => 'recarga.servicios'
                        )
                    );

                    $this->redirect('/seguridad/genera_otp');
                }*/
                

                $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                $identificacion = str_pad($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'], 9, '0', STR_PAD_LEFT);

                if (trim($this->data['RecargaServicios']['tipoOperacion']) == 'scn') {
                    $res = $this->Activo->invoca(
                        'IN0984012',
                        array(
                            'req' => array(
                                'IN984USR1' => $documento_identificacion,
                                'IN984SER' => $modalidad,
                                'IN984CON' => $this->data['RecargaServicios']['numeroContrato'],
                                'IN984TYPE' => 11,
                                'IN984FACC' => str_pad(substr($this->data['RecargaServicios']['cuentaDebitar'], 10, 20), 12, '0', STR_PAD_LEFT),
                                'IN984AMNT' => formatea_monto_toWs($this->data['RecargaServicios']['monto']),
                                'IN984DAT' => $this->data['RecargaServicios']['fechaFacturacion'],
                                'IN984PER' => $this->data['RecargaServicios']['fechaPeriodo']
                            )
                        )
                    );
                    if (isset($res['IN0984012Return'])) {
                        $referencia = $res['IN0984012Return']['IN098401']['IN984DS2'];
                    }
                } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sm') {
                    $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                    $datos = $this->data['RecargaServicios'];
                    $producto = '61';
                    $ttr = '00';

                    $montoMovt = !empty($datos['montoFormMovt']) ? $datos['montoFormMovt'] : (isset($datos['monto']) ? $datos['monto'] : '');
                    $this->data['RecargaServicios']['montoFormMovt'] = $montoMovt;
                    $this->data['RecargaServicios']['monto'] = $montoMovt;

                    if (!isset($this->data['RecargaServicios']['operacion']) ||  empty($this->data['RecargaServicios']['operacion'])) {
                        $this->data['RecargaServicios']['operacion'] = 'MOVILNET';
                    }

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
                        'IN8013MTO' => formatea_monto_toWs($montoMovt),
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

                    $dtaTransaccionOrig = array();
                    if (is_array($res) && isset($res['IN801301Return']['IN801301'])) {
                        $dtaTransaccionOrig = $res['IN801301Return']['IN801301'];
                    }

                    if (is_array($res) && isset($res['IN801301Return']['IN801301']['IN8013ERR']) && $res['IN801301Return']['IN801301']['IN8013ERR'] == 0) {
                        if (isset($res['IN801301Return']['IN801301']['IN8013CKN'])) {
                            $referencia = $res['IN801301Return']['IN801301']['IN8013CKN'];
                        }
                        

                        $dataRest = array(
                            'operadora' => 'movilnet',
                            'producto' => $producto,
                            'numero' => $datos['numeroContrato'],
                            'monto' => formatea_monto_toWs($montoMovt)
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

                            $confirmacion = $codigo_aprobacion;
                            $dtaTransaccion = array_merge($parametro, (array)$dtaTransaccionOrig);

                            $dtaTransaccion['IN8013NAU'] = $codigo_aprobacion;
                            $dtaTransaccion['IN8013NTR'] = $secuencia;
                            $dtaTransaccion['IN8013UUI'] = $uuid;
                            $dtaTransaccion['IN8013TTR'] = '01';
                            $dtaTransaccion['IN8013IRV'] = 'N';
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
                            $dtaTransaccion['IN8013TTR'] = '01';
                            $dtaTransaccion['IN8013UUI'] = $uuid;

                            $res = $this->Activo->invocaMovilnet(array('req' => $dtaTransaccion));
                        }
                    }
                }
                elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sd') {
                    if ($tipo_servicio == 'rtm') {

                        $modalidad = 40;
                        $data = array(
                            'req' => array(
                                'compania' => 1,
                                'nrodocumento' => $identificacion,
                                'telefono' => '58' . substr($this->data['RecargaServicios']['numeroContrato'], 1, 3) . substr($this->data['RecargaServicios']['numeroContrato'], 4),
                                'servicio' => 1,
                                'canal' => '04',
                                'ope' => 2,
                                'cuentadebito' => str_pad(substr($this->data['RecargaServicios']['cuentaDebitar'], 10, 20), 10, '0', STR_PAD_LEFT),
                                'monto' => formatea_monto_toWs($this->data['RecargaServicios']['montoFormDig']),
                                'ip' => $ip,
                                'reverso' => 'N',
                                'refreverso' => '000000000',
                            )
                        );
                   
                    } elseif ($tipo_servicio == 'rtf') {

                        $modalidad = 41;
                        $data = array(
                            'req' => array(
                                'compania' => 1,
                                'nrodocumento' => $identificacion,
                                'telefono' => '58' . substr($this->data['RecargaServicios']['numeroContrato'], 1, 3) . substr($this->data['RecargaServicios']['numeroContrato'], 4),
                                'servicio' => 1,
                                'canal' => '04',
                                'ope' => 2,
                                'cuentadebito' => str_pad(substr($this->data['RecargaServicios']['cuentaDebitar'], 10, 20), 10, '0', STR_PAD_LEFT),
                                'monto' => formatea_monto_toWs($this->data['RecargaServicios']['montoFormDig']),
                                'ip' => $ip,
                                'reverso' => 'N',
                                'refreverso' => '000000000',


                            )
                        );

                    } elseif ($tipo_servicio == 'rim') {
                        $modalidad = 40;
                        $data = array(
                            'req' => array(
                                'compania' => 1,
                                'nrodocumento' => $identificacion,
                                'telefono' => '58' . substr($this->data['RecargaServicios']['numeroContrato'], 1, 3) . substr($this->data['RecargaServicios']['numeroContrato'], 4),
                                'servicio' => 1,
                                'canal' => '04',
                                'ope' => 2,
                                'cuentadebito' => str_pad(substr($this->data['RecargaServicios']['cuentaDebitar'], 10, 20), 10, '0', STR_PAD_LEFT),
                                'monto' => formatea_monto_toWs($this->data['RecargaServicios']['montoFormDig']),
                                'ip' => $ip,
                                'reverso' => 'N',
                                'refreverso' => '000000000',



                            )
                        );
                    } elseif ($tipo_servicio == 'rtv') {

                        $modalidad = 42;
                        $data = array(
                            'req' => array(
                                'compania' => 1,
                                'nrodocumento' => $identificacion,
                                'telefono' => '58' . substr($this->data['RecargaServicios']['numeroContrato'], 1, 3) . substr($this->data['RecargaServicios']['numeroContrato'], 4),
                                'servicio' => 1,
                                'canal' => '04',
                                'ope' => 2,
                                'cuentadebito' => str_pad(substr($this->data['RecargaServicios']['cuentaDebitar'], 10, 20), 10, '0', STR_PAD_LEFT),
                                'monto' => formatea_monto_toWs($this->data['RecargaServicios']['montoFormDig']),
                                'ip' => $ip,
                                'reverso' => 'N',
                                'refreverso' => '000000000',
                            )
                        );
                    }
                       
                    $res = $this->Activo->invocaCelupagos($data);

                    if (isset($res['CelupagosReturn']['celupagos']['celupagos']) && $res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == '000') {
                        $referencia = str_pad($res['CelupagosReturn']['celupagos']['celupagos']['referenciab'], 9, '0', STR_PAD_LEFT);
                        $confirmacion = str_pad($res['CelupagosReturn']['celupagos']['celupagos']['reference'], 9, '0', STR_PAD_LEFT);
                    }
                } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'st') {

                    if ($tipo_servicio == 'rtm' || $tipo_servicio == 'rim') {
                        $modalidad = 70;
                    } elseif ($tipo_servicio == 'rtf') {
                        $modalidad = 71;
                    } elseif ($tipo_servicio == 'rtv') {
                        $modalidad = 72;
                    }

                    // --- L&oacute;gica para determinar el monto a enviar ---
                    $monto_a_enviar = ''; // Inicializar la variable

                    if (isset($this->data['RecargaServicios']['operacion']) && $this->data['RecargaServicios']['operacion'] == 'Movistar') {
                        // Si la operaci&oacute;n es Movistar, usa montoFormMov
                        $monto_a_enviar = $this->data['RecargaServicios']['montoFormMov'];
                    } else {
                        // En cualquier otro caso, usa el monto general
                        $monto_a_enviar = $this->data['RecargaServicios']['monto'];
                    }
                    // --- Fin de la l&oacute;gica del monto ---

                    $res = $this->Activo->invoca(
                        'IN098801',
                        array(
                            'req' => array(
                                'IN988IDUS' => $documento_identificacion,
                                'IN988NCLT' => $identificacion,
                                'IN988DS2' => str_pad($this->data['RecargaServicios']['numeroContrato'], 40, '0', STR_PAD_LEFT),
                                'IN988TYPE' => 11,
                                'IN988CHAN' => 4,
                                'IN988CSER' => $modalidad,
                                'IN988TSER' => 1,
                                'IN988FACC' => str_pad(substr($this->data['RecargaServicios']['cuentaDebitar'], 10, 20), 12, '0', STR_PAD_LEFT),
                                'IN988AMNT' => formatea_monto_toWs($monto_a_enviar),
                                'IN988IPCLT' => $ip
                            )
                        )
                    );

                    if (isset($res['IN098801Return']['IN098801'])) {
                        $referencia = str_pad($res['IN098801Return']['IN098801']['IN988CTRA'], 9, '0', STR_PAD_LEFT);
                        $confirmacion = str_pad($res['IN098801Return']['IN098801']['IN988CTRA'], 9, '0', STR_PAD_LEFT);
                    }
                } //Recarga Inter 
                elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'si') {


                    $data = array(
                        'req' => array(
                            'compania' => 2,
                            //'IN988IDUS'=>$documento_identificacion,
                            'nrodocumento' => $identificacion,
                            'nrocontrato' => $this->data['RecargaServicios']['numeroContrato'],
                            'servicio' => 1,
                            'canal' => '04',
                            'ope' => 2,
                            'cuentadebito' => str_pad(substr($this->data['RecargaServicios']['cuentaDebitar'], 10, 20), 10, '0', STR_PAD_LEFT),
                            'monto' => formatea_monto_toWs($this->data['RecargaServicios']['monto']),
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

                $error = -1;

                if (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sc' || trim($this->data['RecargaServicios']['tipoOperacion']) == 'scn') {
                    $cuenta_servicio = _p('cuenta_cantv');
                    if (isset($res['IN0984012Return']['error']['ERRNUM'])) {
                        $error = intval($res['IN0984012Return']['error']['ERRNUM']);
                        $error = ($error == 0) ? $error : intval($res['IN0984012Return']['error']['ERNU01']);
                        $descripcion = $res['IN0984012Return']['error']['ERNU01'] . ' - ' . $res['IN0984012Return']['error']['ERDS01'];
                    }

                } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'st') {
                    $cuenta_servicio = _p('cuenta_movistar');
                    $operador = 'Movistar';
                    if (isset($res['IN098801Return'])) {
                        $error = 0;
                        if (isset($res['IN098801Return']['error'])) {
                            if (!empty($res['IN098801Return']['error']['ERDR01'])) {
                                $error = intval($res['IN098801Return']['error']['ERDR01']);
                            } else {
                                $error = intval($res['IN098801Return']['error']['ERNU01']);
                            }
                            $descripcion = 'Movistar ' . $res['IN098801Return']['error']['ERDR01'] . '-' . 'MYPS ' . $res['IN098801Return']['error']['ERNU01'] . ' - ' . $res['IN098801Return']['error']['ERDS01'];
                        }
                    }
                } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sd') {
                    $cuenta_servicio = _p('cuenta_digitel');
                    $operador = 'Digitel';
                    
                    if (isset($res['CelupagosReturn'])) {

                        $error = 0;
                        if ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 00) {
                            $error = 00;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 05) {
                            $error = 05;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 07) {
                            $error = 07;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 13) {
                            $error = 13;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 19) {
                            $error = 208;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 21) {
                            $error = 208;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 204) {
                            $error = 204;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 89) {
                            $error = 208;
                        } else {
                            $error = 208;
                        }
                    }
                } elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'si') {
                    //$cuenta_servicio = _p('cuenta_digitel');

                    $operador = 'Inter';
                    if (isset($res['CelupagosReturn'])) {
                        $error = 0;
                        if ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 2) {
                            $error = 0;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 1) {
                            $error = 0;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3) {
                            $error = 0;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3001) {
                            $error = 208;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 3003) {
                            $error = 208;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 204) {
                            $error = 13;
                        } elseif ($res['CelupagosReturn']['celupagos']['celupagos']['errorCode'] == 89) {
                            $error = 208;
                        } else {
                            $error = 208;
                        }
                    }
                }elseif (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sm'){
                    $operador = 'Movilnet';
                    if (is_array($res) && isset($res['IN801301Return']['IN801301']['IN8013ERR']) && $res['IN801301Return']['IN801301']['IN8013ERR'] == 0) {
                        $error = 0;
                    } else {
                        $error = (is_array($res) && isset($res['IN801301Return']['IN801301']['IN8013ERR'])) ? intval($res['IN801301Return']['IN801301']['IN8013ERR']) : -1;
                    }
                }

                if ($error == 0) {

                    /** ACTUALIZA EL ACUMULADO DEL DIA **/
                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'it', $this->data['RecargaServicios']['monto']);
                    /** ACTUALIZA EL ACUMULADO DEL DIA **/

                    $descripcion = 'Servicio Recarga ' . $this->data['RecargaServicios']['operacion'];
                    $this->Bitacora->registraBitacora(
                        $usuario['id'],
                        100015,
                        $this->data['RecargaServicios']['cuentaDebitar'],
                        $this->data['RecargaServicios']['numeroContrato'],
                        $this->data['RecargaServicios']['monto'],
                        $referencia,
                        $descripcion,
                        0
                    );

                    $this->data['RecargaServicios']['REFERENCIA'] = $referencia;
                    $this->data['RecargaServicios']['REFERENCIA_OPERADOR'] = $confirmacion;
                    $this->data['RecargaServicios']['CONCEPTO'] = 'RECARGA SERVICIO';
                    $this->data['RecargaServicios']['TIPO'] = 'Servicio' . $this->data['RecargaServicios']['operacion'];
                    $this->data['RecargaServicios']['ESTATUS'] = 'PROCESADO';
                    $this->data['RecargaServicios']['NRO_BENEFICIARIO'] = $this->data['RecargaServicios']['numeroContrato'];

                    // Genera la data para la descarda de pdf
                    $this->Session->write('recarga.servicio.descarga.pdf', $this->data['RecargaServicios']);

                    // Email Orden
                    // Email Opciones
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = desencriptar($usuario['email_principal']);
                    $this->Email->subject = 'Notificacion Recarga de Servicios';
                    $this->Email->sendAs = 'html';

                    // Informacion del correo
                    $this->Email->template = 'recargaservicio_ordenante';
                    $tipo_accion = 'Recarga de Servicios';
                    $tipo_afiliacion = $this->data['RecargaServicios']['operacion'];
                    $nombreBeneficiario = isset($this->data['RecargaServicios']['nombreBeneficiario']) ? $this->data['RecargaServicios']['nombreBeneficiario'] : '';

                    $this->set(compact('usuario', 'tipo_afiliacion', 'tipo_accion', 'nombreBeneficiario'));

                    // Informacion del SMS

                    $sms = array(
                        'numcel' => desencriptar($usuario['celular']),
                        'canal1' => '59',
                        'fectim' => $operador,
                        'cheque' => substr($this->data['RecargaServicios']['numeroContrato'], 1),
                        'cuenta' => substr($this->data['RecargaServicios']['cuentaDebitar'], 16, 4),
                        'monto' => $this->data['RecargaServicios']['monto'],
                        'fecha' => date('Ymd'),
                        'hora' => date('His'),
                        'refnum' => substr($referencia, 5, 4),
                        'xrnde38' => 'IB'
                    );
                    //Digitel
                    if (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sd') {

                        if (_p('notificacion.digitel') == '1' || _p('notificacion.digitel') == '3') {
                            // Envio del correo
                            $this->Email->send();
                        }

                        if (_p('notificacion.digitel') == '2' || _p('notificacion.digitel') == '3') {
                            // Enviar por sms la notificaci&oacute;n de que realiz&oacute; la recarga al ordenante        
                            $this->Sms->enviar($sms);
                        }

                    }
                    //Digitel
                    if (trim($this->data['RecargaServicios']['tipoOperacion']) == 'si') {

                        if (_p('notificacion.digitel') == '1' || _p('notificacion.digitel') == '3') {
                            // Envio del correo
                            $this->Email->send();
                        }

                        /* if(_p('notificacion.digitel')=='2' || _p('notificacion.digitel')=='3'){
                             // Enviar por sms la notificaci&oacute;n de que realiz&oacute; la recarga al ordenante        
                             $this->Sms->enviar($sms);
                         }*/

                    }

                    //Movistar
                    if (trim($this->data['RecargaServicios']['tipoOperacion']) == 'st') {

                        if (_p('notificacion.movistar') == '1' || _p('notificacion.movistar') == '3') {
                            // Envio del correo
                            $this->Email->send();
                        }

                        if (_p('notificacion.movistar') == '2' || _p('notificacion.movistar') == '3') {
                            // Enviar por sms la notificaci&oacute;n de que realiz&oacute; la recarga al ordenante        
                            $this->Sms->enviar($sms);
                        }

                    }

                } else {



                    switch ($error) {
                        case 203:
                        case 209:
                        case 8267:
                        case 8275:
                        case 8293:
                            $descripcion = _m('servicio.recarga.invalido', array($ayuda, $operador, '203'));
                            break;
                        case 204:
                        case 8273:
                            $descripcion = _m('monto.recarga.invalido', array($multiplo, formatea_monto_fromWs($recarga_minima), formatea_monto_fromWs($recarga_maxima)), '204');
                            break;
                        case 205:
                        case 8271:
                            $descripcion = _m('gsm.invalido', array('GSM', $ayuda, $operador, '205'));
                            break;
                        case 207:
                        case 8268:
                        case 8269:
                            $descripcion = _m('servicio.recarga.invalido', array($ayuda, $operador, '207'));
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

                        case 14:
                            $descripcion = _m('digitel.noPermitido');
                            break;
                        case 05:

                            $descripcion = _m('digitel.bloqueo');
                            break;
                        case 07:
                            $descripcion = _m('digitel.noPermitido');
                            break;
                        case 13:
                            $descripcion = _m('digitel.montoNovalido');
                            break;
                        case 2:
                            $descripcion = _m('cuenta.sobregirada');
                            break;
                        default:
                            $descripcion = _m('procesar.solicitud');
                    }
                    $this->set(compact('descripcion'));
                    $this->log('Recarga de Servicios ' . $this->data['RecargaServicios']['operacion'] . ' - ' . $descripcion . ' - ' . (is_array($res) ? json_encode($res) : $res), LOG_ERR);
                    $this->Bitacora->registraBitacora(
                        $usuario['id'],
                        100015,
                        $this->data['RecargaServicios']['cuentaDebitar'],
                        $this->data['RecargaServicios']['numeroContrato'],
                        $this->data['RecargaServicios']['monto'],
                        NULL,
                        $descripcion . '-' . $error,
                        1
                    );

                }

                $this->set('pendiente', false);
                //Fin usuario natural o master unico

            } else if ( $usuario['rol_juridico_id'] == 3 || $usuario['rol_juridico_id'] == 5) {
                // En caso de jurídico, s&oacute;lo los operadores y operadores/aprobadores pueden
                // generar un pago. En cuyo caso, se deja pendiente por aprobar

                $error = 0;
                if (trim($this->data['RecargaServicios']['tipoServicio']) == 'rtm' || trim($this->data['RecargaServicios']['tipoServicio']) == 'rtf') {
                    if (trim($this->data['RecargaServicios']['tipoOperacion']) == 'sm' && trim($this->data['RecargaServicios']['tipoServicio']) == 'rtm') {
                        $datos = $this->data['RecargaServicios'];
                        $numeroContrato = $datos['numeroContrato'];
                        $monto = formatea_monto_toWs($datos['montoFormMovt']);
                    }else{
                        $numeroContrato = substr($this->data['RecargaServicios']['numeroContrato'], 4);
                        $this->data['RecargaServicios']['monto_maximo'] = NULL;
                        $monto = formatea_monto_toWs($this->data['RecargaServicios']['monto']);
                    }
                } else {
                    $numeroContrato = $this->data['RecargaServicios']['numeroContrato'];
                    $monto = formatea_monto_toWs($this->data['RecargaServicios']['monto']);
                }

                $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                    
        

                $res = $this->TransaccionPendiente->save(
                    array(
                        'TransaccionPendiente' => array(
                            'usuario_id' => $usuario['id'],
                            'tipo' => $tipo_transaccion_pendiente,
                            'tipo_servicio' => trim($this->data['RecargaServicios']['tipoServicio']),
                            'estatus' => 'pa',
                            'fecha_hora_registro' => date('Y-m-d H:i:s'),
                            'monto' => $monto,
                            'aprobaciones_requeridas' => $this->data['RecargaServicios']['aprobaciones_requeridas'],
                            'tipo_rif' => $usuario['tipo_rif'],
                            'rif' => $usuario['rif'],
                            'cuenta_origen' => encriptar($this->data['RecargaServicios']['cuentaDebitar']),
                            'cuenta_destino' => encriptar($numeroContrato),
                            'descripcion' => encriptar($this->data['RecargaServicios']['operacion']),
                            'nombre_beneficiario' => encriptar($this->data['RecargaServicios']['nombreBeneficiario']),
                            'tipo_id_beneficiario' => $usuario['tipo_rif'],
                            'id_beneficiario' => $usuario['rif'],
                            'email_beneficiario' => null,
                            'alias' => null
                        )
                    ),
                    false
                );
                
                if ($res) {
                    $error = 0;
                    $referencia = $this->TransaccionPendiente->id;
                } else {
                    $error = -1;
                    $referencia = '';
                }

                $error_afiliacion = false;

                $this->set('pendiente', true);
            }//Fin Transacciones Pendientes

            $this->set(compact('error', 'referencia', 'confirmacion'));
            $this->Session->delete('recarga_servicios');
        } else {
            $this->redirect('recarga_servicios');
        }
    }

    /**
     *Acci&oacute;n para la descarga del archivo pdf 
     */
    function descargar_pdf_sevicios()
    {
        if (!$this->Session->check('recarga.servicio.descarga.pdf') && $this->Session->check('usuarios.datos')) {
            $this->redirect('/consultas/integral');
        }

        $usuario = $this->Session->read('usuarios.datos');
        $data = $this->Session->read('recarga.servicio.descarga.pdf');

        $this->set(compact('data', 'usuario'));
        $this->render('/elements/descargar_pdf_servicios');
    }

    /**
     * Acci&oacute;n para mostrar advertencia de operaci&oacute;n realizada   
     */
    function transaccion_advertencia()
    {

        if (!$this->Session->check('transaccion.advertencia'))
            $this->redirect('/consultas/integral');

        $this->data = $this->Session->read('transaccion.advertencia');

        $this->layout = 'interna';

        $this->set('seccion_actual', $this->data['seccion_actual']);

        $this->render('/elements/transaccion_advertencia');
    }

    /*** Fin Pago Servicios ***/
}