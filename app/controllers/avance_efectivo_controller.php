<?php

/**
 * Class AvanceEfectivoController
 *
 * Controlador que contiene las funcionalidades para los avance de efectivo y avance de extracredito.
 *
 * @author Miguel Mendez <mammendez@bancoactivo.com>
 */
class AvanceEfectivoController extends AppController
{
    var $name = 'AvanceEfectivo';
    /**
     * @var array Modelos que usa el controlador
     */
    var $uses = array(
        'AvanceEfectivo',
        'Usuario',
        'Bitacora',
        'Afiliacion',
        'AcumuladoTransaccion',
        'TransaccionPendiente',
        'TransaccionJuridicoDetalle',
        'Parametro',
    );

    /**
     *
     * @var array Componentes que utiliza este controlador
     */
    var $components = array('Activo', 'Session', 'Email', 'RequestHandler');

    /**
     * @var array Ayudantes utilizados en las vistas de este controlador
     */
    var $helpers = array('Activo', 'Html', 'Session', 'Form', 'Paginator', 'Text', 'Javascript');


    /**
     * Funcion para responder con JSON
     *
     * @param array $response Data para ser enviada en la respuesta
     *
     * @return void
     */
    public function enviaJson($response)
    {        
        // Setea la data para la vista
        $this->set('response', $response);
        $this->layout = '';
        // Renderiza la vista  
        $this->render(null, null, VIEWS . 'elements' . DS . 'json.ctp');
    }

    /**
     * Muestra el formulario para realizar el avance de efectivo. Si se esta enviando el formulario, se procesa y se
     * muestra el paso siguiente que es el de confirmación.
     *
     */
    function avance_extra()
    {
        $transaccion = 100039;
        $form_errores = array();

        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion($transaccion)) {
            $this->redirect('/consultas/integral/');
        }

        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

        // Validamos la tarjeta de coordenadas si no ha sido validada antes en esta sesi?n
        if ($validacion_tdc == null || !isset($validacion_tdc['validado']) || !$validacion_tdc['validado']) {
            $this->Session->write(
                'seguridad.validar_tdc',
                array(
                    'url_aceptar' => '/avance_efectivo/avance_extra',
                    'layout' => 'interna',
                    'titulo' => 'ExtraCrédito Efectivo',
                    'pasos' => array(),
                    'paso_actual' => 0,
                    'seccion_actual' => 'creditos_linea.avance.extracredito'
                )
            );

            $this->redirect('/seguridad/validar_tdc');

        }

        $this->layout = 'interna';
        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');

        $this->set('seccion_actual', 'creditos_linea.avance.extracredito');

        // Buscamos los parametros del m?dulo
        $params = $this->AvanceEfectivo->getParametros();

        //Validamos si esta enviando el formulario
        if (!empty($this->data['AvanceEfectivo'])) {
            //var_dump($this->data['AvanceEfectivo']);die();
            $this->AvanceEfectivo->tarjetas = $this->Session->read('tarjetas.avance');
            $this->AvanceEfectivo->set($this->data);

            if ($this->AvanceEfectivo->validates()) {
                if ($this->AvanceEfectivo->alMenosUnaTarjeta()) {
                    $this->Session->write('avance.extra.confirmacion.data',
                        $this->AvanceEfectivo->getDataConfirmacion($usuario));
                    $this->redirect('avance_extra_confirmacion');
                }
            }
        }


        // Buscamos los instrumentos que califican para este producto
        // $extra true para que nos traiga los de Extracredito Efectivo
        $extra = true;
        $instrumentos = $this->Activo->buscarInstrumentosAvance($extra);

        $cuentas = $instrumentos['cuentas'];

        if (empty($cuentas)) {
            array_push($form_errores, _m('productos.no.califican', true));
        }

        // Porcentaje maximo que puede solicitar de su linea paralela
        $porc_max = (double)$params['avance.extra.porc.max'];

        $tarjetas = array();
        foreach ($instrumentos['tarjetas'] as $t) {
            // Bandera para asegurarnos que obtuvimos el saldo de todas las tarjetas
            $err = false;

            foreach ($t as $k => $v) {
                
                //Validar el bin si es de empleado y agregar valores por tarjeta 
                $pb = explode(',', $params['avance.extra.emp.bin']);
                $plazostdc = $params['avance.extra.plazos'];
                $tasatdc = $params['avance.extra.tasa.vig'];
                $comisiontdc = $params['avance.extra.comision'];
                $BinEmp = array();
                $BinEmp = array_combine($pb, $pb); 
                foreach($pb as $bn){
                    if ($bn == substr($k, 0,10)){
                       $plazostdc = $params['avance.extra.emp.plazos'];
                       $tasatdc = $params['avance.extra.emp.tasa.vig'];
                       $comisiontdc = $params['avance.extra.emp.comision'];
                    }
   
                    
                }
                //(string)$k
                //-------
                
                $alias = _a($usuario['id'], $k);
                //Se comenta la busqueda de saldo ya que no se usa en este momento
                //Sin embargo se utilizara en un futuro
                //$saldo = $this->Activo->buscarSaldoExtraCredito((string)$k);

                /************Forzamos los valores por ahora *****************/
                $saldo['cod_respuesta'] = '00';
                $saldo['monto'] = 1000;
                /************************  Fin  ******************************/

                if ($saldo['cod_respuesta'] == '00') // Fue exitosa la busqueda de saldo
                {
                    // Calculamos el porcentaje maximo que puede usar
                    //if($porc_max >= 1 && $porc_max <= 100){
                    //  $saldo['monto'] = ($saldo['monto'] * $porc_max) / 100;
                    //}
                    if ($saldo['monto'] > 0) {
                        array_push($tarjetas,
                            array(
                                'alias' => $alias,
                                'numero' => (string)$k,
                                'msk' => $v,
                                'saldo' => $saldo['monto'],
                                'error' => $this->AvanceEfectivo->getErrorTarjetas($k),
                                'plazoex' => $plazostdc ,
                                'tasatdc' => $tasatdc ,
                                'comisiontdc' => $comisiontdc 
                            )
                        );
                    }
                } else {
                    $err = true;
                    // Registra en bitacora el error
                    $this->Bitacora->registraBitacora(
                        $usuario['id'],
                        $transaccion,
                        $v,
                        null,
                        null,
                        null,
                        $saldo['cod_respuesta'] . ' Error al obtener el saldo de la tarjeta de cr&eacute;dito',
                        1
                    );
                }
            }

            //Si tuvimos error en obtener el saldo de alguna tarjeta vaciamos el array
            if ($err) {
                $tarjetas = array();
                array_push($form_errores, _m('error.consulta.saldo', true));
            }

        }

        // Validamos que tenga cuentas y tarjetas que califiquen
        if (empty($tarjetas) || empty($cuentas)) {
            // No tiene productos que califiquen
            $cuentas = array();
            $tarjetas = array();
            array_push($form_errores, _m('productos.no.califican', true));
        }
        array_merge($form_errores, $this->AvanceEfectivo->form_errores);
        $this->Session->write('tarjetas.avance', $tarjetas);
        $this->set(compact('cuentas', 'tarjetas', 'params', 'form_errores'));

    }

    /**
     * Confirmación de los datos suministrados en la pantalla anterior, Avance de efectivo.
     *
     */
    function avance_extra_confirmacion()
    {
        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion(100039) || $this->referer() != '/avance_efectivo/avance_extra') {
            $this->redirect('/consultas/integral/');
        }

        $this->layout = 'interna';
        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');
        $this->set('seccion_actual', 'creditos_linea.avance.extracredito');

        $this->data = $this->Session->read('avance.extra.confirmacion.data');

        if (!isset($this->data) || $this->referer() != '/avance_efectivo/avance_extra') {
            $this->redirect('avance_extra');
        }
        $this->AvanceEfectivo->set($this->data);

        $this->Session->write('confirmacion.avance.extra', $this->data);
        $this->set('data_confirmacion', $this->data);

    }

    /**
     * Accion que ejecuta el Extracredito
     *
     */
    function avance_extra_resultado()
    {
        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion(100039) || $this->referer() != '/avance_efectivo/avance_extra_confirmacion') {
            $this->redirect('/consultas/integral/');
        }

        $this->layout = 'interna';
        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');
        $this->set('seccion_actual', 'creditos_linea.avance.extracredito');

        $this->data = $this->Session->read('confirmacion.avance.extra');

        foreach ($this->data['tarjetas'] as $t) {
            // Registra en bitacora la aceptaci?n de contratos
            $this->Bitacora->registraBitacora(
                $usuario['id'], 100039, $t['numero'], $this->data['cuenta_abono']['numero'],
                formatea_monto_fromWs($t['monto_solicitado']), null,
                "Usuario acepta el contrato y condiciones del ExtraCredito Efectivo", 0
            );
        }

        if (!isset($this->data) || $this->referer() != '/avance_efectivo/avance_extra_confirmacion') {
            $this->redirect('avance_extra');
        }
        $this->set('data_confirmacion', $this->data);
    }

    /**
     * Accion que genera el contrato de ExtraCredito efectivo
     *
     */
    function contrato_extra()
    {
        // Validamos que el usuario tenga acceso al modulo
        if (!validarTransaccion(100039) || $this->referer() != '/avance_efectivo/avance_extra_confirmacion') {
            $this->redirect('/consultas/integral/');
        }
        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->read('avance.extra.confirmacion.data');

        if (!isset($this->data)) {
            $this->redirect('avance_extra');
        }
        foreach ($this->data['tarjetas'] as $t) {
            if ($t['msk'] == $this->params['url']['tarj']) {
                $contrato_data['num_tarjeta'] = $t['numero'];
                $contrato_data['cuenta_abono'] = $this->data['cuenta_abono']['numero'];
                $contrato_data['monto'] = $t['monto_solicitado'];
                $contrato_data['plazo'] = $t['plazo'];
                $contrato_data['comision_porc'] = round((floatval($t['comision_flat'] * 100)) / floatval($t['monto_solicitado']));
                $this->set('contrato_data', $contrato_data);

            }
        }


    }


    /**
     * Accion que ejecuta el Avance de Efectivo
     * Llamado desde avance_resultado via AJAX
     */
    function realiza_avance_extra()
    {

        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion(100039)) {
            $this->redirect('/consultas/integral/');
        }

        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');
        $data_confirmacion = $this->Session->read('confirmacion.avance.extra');
        $this->set('seccion_actual', 'creditos_linea.avance.extracredito');

        if ($this->RequestHandler->isAjax()) {
            // Creamos la estructura de la data por defecto
            $data = array(
                't_cod_respuesta' => 'NC',
                't_resultado' => "Ocurri&oacute; un error, por favor intente mas tarde",
                't_referencia' => "",
                't_primer_pago' => 0
            );

            $response = array('success' => true);

            $consulta = $this->Activo->consultaExtraCredito($this->data['t_numero'], $this->data['t_monto_sol']);
            if (isset($consulta['cod_respuesta'])) {
                // La respuesta es positiva, tiene saldo disponible
                if ($consulta['cod_respuesta'] === '00') {
                    // Llamamos al servicio que realiza el avance

                    $res = $this->Activo->realizaAvanceExtraCredito($this->data['t_numero'],
                        $this->data['t_cuenta_abonar'], $this->data['t_monto_sol'], $this->data['t_plazo']);
                    if (!isset($res['cod_respuesta'])) {
                        // Registra en bitacora
                        $this->Bitacora->registraBitacora(
                            $usuario['id'], 100039, $this->data['t_numero'], $this->data['t_cuenta_abonar'],
                            formatea_monto_fromWs($this->data['t_monto_sol']), null,
                            "OCURRIO UN ERROR LLAMANDO AL SERVICIO", 1
                        );
                        $response['success'] = false;
                    }
                    // La compra fue aprobada
                    if (isset($res['cod_respuesta']) && $res['cod_respuesta'] == '00') {
                        $data['t_cod_respuesta'] = '00';
                        $data['t_resultado'] = 'Operaci&oacute;n realizada satisfactoriamente';
                        $data['t_referencia'] = $res['referencia'];
                        // Buscamos el monto de primer pago que calculamos en la primera pantalla
                        // Una vez obtengamos el monto desde el servicio de credicard podemos utilizarlo aqui
                        foreach ($data_confirmacion['tarjetas'] as $t_conf) {
                            if ($t_conf['numero'] == $this->data['t_numero']) {
                                $data['t_primer_pago'] = (double)$t_conf['primer_pago'];
                            }
                        }

                        // Registramos en Bitacora la compra
                        $this->Bitacora->registraBitacora(
                            $usuario['id'], 100039, $res['num_tarjeta'], $this->data['t_cuenta_abonar'],
                            formatea_monto_fromWs($this->data['t_monto_sol']), $res['referencia'],
                            "Abono ExtraCredito Efectivo", 0
                        );
                    } else {
                        $cod = (isset($res['cod_respuesta']) && $res['cod_respuesta'] != null && $res['cod_respuesta'] != '') ? $res['cod_respuesta'] : '07';
                        $data['t_cod_respuesta'] = $cod;
                        $data['t_resultado'] = _m('extra.no.rechazo', $cod, true);
                        $data['t_referencia'] = " ";
                        $data['t_primer_pago'] = 0;
                        // Registramos en Bitacora el rechazo de la compra
                        $this->Bitacora->registraBitacora(
                            $usuario['id'], 100039, $res['num_tarjeta'], $this->data['t_cuenta_abonar'],
                            formatea_monto_fromWs($this->data['t_monto_sol']), null,
                            "EXTRACREDITO - TRANSACCION NO APROBADA", 0
                        );
                    }
                    // No posee saldo suficiente	
                } elseif ($consulta['cod_respuesta'] === 'E') {
                    $data['t_cod_respuesta'] = 'E';
                    $data['t_resultado'] = _m('extra.supera.monto', true);
                    $data['t_referencia'] = " ";
                    $data['t_primer_pago'] = 0;
                    // Registra en bitacora
                    $this->Bitacora->registraBitacora(
                        $usuario['id'], 100039, $this->data['t_numero'], $this->data['t_cuenta_abonar'],
                        formatea_monto_fromWs($this->data['t_monto_sol']), null,
                        "EXTRACREDITO - CLIENTE NO POSEE SALDO SUFICIENTE", 0
                    );
                } else {
                    $data['t_cod_respuesta'] = $consulta['cod_respuesta'];
                    $data['t_resultado'] = _m('extra.no.rechazo', $consulta['cod_respuesta'], true);
                    $data['t_referencia'] = " ";
                    $data['t_primer_pago'] = 0;
                    // Registra en bitacora
                    $this->Bitacora->registraBitacora(
                        $usuario['id'], 100039, $this->data['t_numero'], $this->data['t_cuenta_abonar'],
                        formatea_monto_fromWs($this->data['t_monto_sol']), null,
                        "EXTRACREDITO - TRANSACCION NO APROBADA", 0
                    );
                }
                // No hubo respuesta en la consulta
            } else {
                // Registra en bitacora
                $this->Bitacora->registraBitacora(
                    $usuario['id'], 100039, $this->data['t_numero'], $this->data['t_cuenta_abonar'],
                    formatea_monto_fromWs($this->data['t_monto_sol']), null,
                    "EXTRACREDITO - OCURRIO UN ERROR LLAMANDO AL SERVICIO", 1
                );
                $response['success'] = false;
            }
            $this->set('data', $data);
            $this->enviaJson($response);
        } else {
            $this->redirect('/consultas/integral/');
        }
    }


    /**
     * Accion que muestra el formulario inicial y luego lo valida y procesa
     *
     */

    function avance()
    {
        $transaccion = 100038;
        $form_errores = array();

        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion($transaccion)) {
            $this->redirect('/consultas/integral/');
        }

        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

        // Validamos la tarjeta de coordenadas si no ha sido validada antes en esta sesi?n
        if ($validacion_tdc == null || !isset($validacion_tdc['validado']) || !$validacion_tdc['validado']) {
            $this->Session->write(
                'seguridad.validar_tdc',
                array(
                    'url_aceptar' => '/avance_efectivo/avance',
                    'layout' => 'interna',
                    'titulo' => 'Avance Efectivo',
                    'pasos' => array(),
                    'paso_actual' => 0,
                    'seccion_actual' => 'creditos_linea.avance'
                )
            );

            $this->redirect('/seguridad/validar_tdc');

        }

        $this->layout = 'interna';
        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');


        $this->set('seccion_actual', 'creditos_linea.avance');

        // Buscamos los parametros del m?dulo
        $params = $this->AvanceEfectivo->getParametros();

        //Validamos si esta enviando el formulario
        if (!empty($this->data['AvanceEfectivo'])) {
            $this->AvanceEfectivo->tarjetas = $this->Session->read('tarjetas.avance');
            $this->AvanceEfectivo->set($this->data);

            if ($this->AvanceEfectivo->validates()) {
                if ($this->AvanceEfectivo->alMenosUnaTarjeta()) {
                    $this->Session->write('avance.confirmacion.data',
                        $this->AvanceEfectivo->getDataConfirmacion($usuario));
                    $this->redirect('avance_confirmacion');
                }
            }
        }


        // Buscamos los instrumentos que califican para este producto
        $instrumentos = $this->Activo->buscarInstrumentosAvance();
        $cuentas = $instrumentos['cuentas'];

        if (empty($cuentas)) {
            array_push($form_errores, 'No posee cuentas que califiquen para este producto');
        }

        // Porcentaje maximo que puede solicitar de su linea rotativa
        $porc_max = (double)$params['avance.porc.max'];

        $tarjetas = array();
        foreach ($instrumentos['tarjetas'] as $t) {
            // Bandera para asegurarnos que obtuvimos el saldo de todas las tarjetas
            $err = false;

            foreach ($t as $k => $v) {
                $alias = _a($usuario['id'], $k);
                //$saldo = $this->Activo->buscarSaldoAvance((string)$k);

                /************Forzamos los valores por ahora *****************/
                $saldo['cod_respuesta'] = '00';
                $saldo['monto'] = 1;
                /************************  Fin  ******************************/


                if ($saldo['cod_respuesta'] == '00') // Fue exitosa la busqueda de saldo
                {
                    // Calculamos el porcentaje maximo que puede usar
                    if ($porc_max >= 1 && $porc_max <= 100) {
                        $saldo['monto'] = ($saldo['monto'] * $porc_max) / 100;
                    }
                    if ($saldo['monto'] > 0) {
                        array_push($tarjetas,
                            array(
                                'alias' => $alias,
                                'numero' => (string)$k,
                                'msk' => $v,
                                'saldo' => $saldo['monto'],
                                'error' => $this->AvanceEfectivo->getErrorTarjetas($k)
                            )
                        );
                    }
                } else {
                    $err = true;
                    // Registra en bitacora el error
                    $this->Bitacora->registraBitacora(
                        $usuario['id'],
                        $transaccion,
                        $v,
                        null,
                        null,
                        null,
                        $saldo['cod_respuesta'] . ' Error al obtener el saldo de la tarjeta de credito',
                        1
                    );
                }
            }
            //Si tuvimos error en obtener el saldo de alguna tarjeta vaciamos el array
            if ($err) {
                $tarjetas = array();
                array_push($form_errores, _m('error.consulta.saldo', true));
            }

        }

        // Validamos que tenga cuentas y tarjetas que califiquen
        if (empty($tarjetas) || empty($cuentas)) {
            // No tiene productos que califiquen
            $cuentas = array();
            $tarjetas = array();
            array_push($form_errores, 'Lo sentimos mucho, no se puede realizar el Avance de Efectivo');
        }
        array_merge($form_errores, $this->AvanceEfectivo->form_errores);
        $this->Session->write('tarjetas.avance', $tarjetas);
        $this->set(compact('cuentas', 'tarjetas', 'params', 'form_errores'));

    }

    /**
     * Accion de confiramci?n de Avance de Efectivo
     *
     */
    function avance_confirmacion()
    {
        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion(100038)) {
            $this->redirect('/consultas/integral/');
        }

        $this->layout = 'interna';
        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');
        $this->set('seccion_actual', 'creditos_linea.avance');

        $this->data = $this->Session->read('avance.confirmacion.data');

        if (!isset($this->data) || $this->referer() != '/avance_efectivo/avance') {
            $this->redirect('avance');
        }
        $this->AvanceEfectivo->set($this->data);

        $this->Session->write('confirmacion.avance', $this->data);
        $this->set('data_confirmacion', $this->data);

    }


    /**
     * Accion que ejecuta el Avance de Efectivo
     *
     */
    function avance_resultado()
    {
        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion(100038)) {
            $this->redirect('/consultas/integral/');
        }

        $this->layout = 'interna';
        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');
        $this->set('seccion_actual', 'creditos_linea.avance');

        $this->data = $this->Session->read('confirmacion.avance');

        /*if(!isset($this->data) || $this->referer()!='/avance_efectivo/avance_confirmacion'){
            $this->redirect('avance');
        }*/
        $this->set('data_confirmacion', $this->data);
    }

    /**
     * Accion que ejecuta el Avance de Efectivo
     * Llamado desde avance_resultado via AJAX
     */
    function realiza_avance()
    {
        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion(100038)) {
            $this->redirect('/consultas/integral/');
        }

        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');
        $this->set('seccion_actual', 'creditos_linea.avance');

        if ($this->RequestHandler->isAjax()) {
            $response = array('success' => false);
            // Llamamos al servicio que realiza el avance
            $res = $this->Activo->realizaAvanceEfectivo($this->data['t_numero'],
                str_pad(substr($this->data['t_cuenta_abonar'], -10), 12, "0", STR_PAD_LEFT),
                $this->data['t_monto_sol']);

            // Creamos la estructura de la data por defecto
            $data = array(
                't_cod_respuesta' => '08',
                't_resultado' => "Ocurrio un error, intente mas tarde",
                't_referencia' => ""
            );

            if (isset($res['cod_respuesta'])) {
                if ($res['cod_respuesta'] == '00') {
                    $response['success'] = true;
                    $data['t_resultado'] = "Operaci&oacute;n realizada satisfactoriamente";
                    $data['t_monto_sol'] = $res['monto'];
                } else {
                    $response['success'] = true;
                    $data['t_resultado'] = _m('extra.no.rechazo', $res['cod_respuesta'], true);
                }
                $data['t_cod_respuesta'] = $res['cod_respuesta'];
                $data['t_referencia'] = $res['referencia'];

                // Registra en bitacora
                $err = $res['cod_respuesta'] == '00' ? 0 : 1;
                $desc = $res['cod_respuesta'] == '00' ? "AVANCE EFEC. " . $res['num_tarjeta'] : "AVANCE EFECTIVO - " . $res['respuesta'];
                $this->Bitacora->registraBitacora(
                    $usuario['id'],
                    100038,
                    $res['num_tarjeta'],
                    $this->data['t_cuenta_abonar'],
                    formatea_monto_fromWs($res['monto']),
                    $res['referencia'],
                    $desc,
                    $err
                );
            } else {
                $response['success'] = null;

                // Registra en bitacora
                $this->Bitacora->registraBitacora(
                    $usuario['id'],
                    100038,
                    $this->data['t_numero'],
                    $this->data['t_cuenta_abonar'],
                    $this->data['t_monto_sol'],
                    null,
                    "AVANCE EFECTIVO - OCURRIO UN ERROR LLAMANDO AL SERVICIO",
                    1
                );
            }


            $this->set('data', $data);
            $this->enviaJson($response);
        } else {
            $this->redirect('/consultas/integral/');
        }

    }

    /**
     * Genera un archivo pdf basado en el resultado exitoso del avance de efectivo.
     */
    function genera_pdf_avance()
    {
        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion(100038)) {
            $this->redirect('/consultas/integral/');
        }

        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');
        $this->set('seccion_actual', 'creditos_linea.avance');
        $data_confirmacion = $this->Session->read('confirmacion.avance');
        $data_post = $this->data;
        if (!isset($data_post) || $this->referer() != '/avance_efectivo/avance_resultado') {
            $this->redirect('/consultas/integral/');
        }
        $this->set(compact('data_confirmacion', 'data_post'));

    }

    /**
     * Genera un archivo pdf basado en el resultado exitoso del avance de extracredito.
     */
    function genera_pdf_extracredito()
    {
        // Validamos que el ususrio tenga acceso al modulo
        if (!validarTransaccion(100039)) {
            $this->redirect('/consultas/integral/');
        }

        // se leen los datos del usuario		
        $usuario = $this->Session->read('usuarios.datos');
        $this->set('seccion_actual', 'creditos_linea.avance.extracredito');
        $data_confirmacion = $this->Session->read('confirmacion.avance.extra');
        $data_post = $this->data;
        if (!isset($data_post) || $this->referer() != '/avance_efectivo/avance_extra_resultado') {
            $this->redirect('/consultas/integral/');
        }

        $this->set(compact('data_confirmacion', 'data_post'));

    }
}
