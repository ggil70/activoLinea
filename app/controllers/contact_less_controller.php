<?php
/**
 * Controlador para gestionar las operaciones del servicio Contactless
 * (Consulta de estatus, activación, desactivación y modificación de cuenta asociada).
 * * Basado en la arquitectura CakePHP 1.x.
 */
class ContactLessController extends AppController {
    // =========================================================================
    // 1. CONSTANTES Y CONFIGURACIÓN DEL CONTROLADOR
    // =========================================================================

    /**
     * @var int TRX_CONSULTA Código de transacción para la consulta de estatus.
     * @var int TRX_EJECUCION Código de transacción para la ejecución de cambios (activar/inactivar/modificar).
     * @var int TRX_PERMISO  Código de transacción usado para la validación de acceso a la funcionalidad.
     */
    const TRX_CONSULTA = 102103;
    const TRX_EJECUCION = 102104;
    const TRX_PERMISO = 102104; 
    
    // Acciones soportadas por el servicio SOAP
    const ACCION_CONSULTA = 'C';
    const ACCION_ACTIVAR = 'A';
    const ACCION_INACTIVAR = 'I';
    const ACCION_MODIFICAR = 'M';

    // Estatus de error interno para bitácora y logs
    const ESTATUS_ERROR_COMM = 'E_COMM'; // Error de comunicación con el servicio
    const ESTATUS_ERROR_STRUCT = 'E_STRUCT'; // Error en la estructura de la respuesta del servicio

    /**
     * Modelos a utilizar en este controlador.
     * @var array
     */
    var $uses = array('Usuario', 'Parametro', 'Bitacora', 'Afiliacion');

    /**
     * Componentes de CakePHP y personalizados a utilizar.
     * Activo: Para invocar servicios externos (como obtener tarjetas).
     * ContactLess: Componente personalizado para la lógica SOAP.
     * @var array
     */
    var $components = array('Activo', 'Session', 'Email', 'ContactLess', 'RequestHandler');

    /**
     * Helpers (Auxiliares de vista) a utilizar.
     * @var array
     */
    var $helpers = array('Html', 'Session', 'Form', 'Paginator');

    // =========================================================================
    // 2. FUNCIONES DE FLUJO PRINCIPAL (ORDEN DE EJECUCIÓN)
    // =========================================================================

    /**
     * [Paso 1] Pantalla inicial de consulta.
     * * Obtiene las tarjetas del usuario, consulta su estatus Contactless en el
     * servicio y prepara los datos (tarjetas y cuentas) para los dropdowns de la vista.
     */
    function consulta() {
        // 1. Verificar Permiso: Redirige si el usuario no tiene permiso para esta transacción.
        if (!$this->_checkPermiso()) return;

        // 2. Obtener Datos: Carga ID, tipo de documento y número de documento del usuario de la sesión.
        $datos_usuario = $this->_obtenerDatosUsuario();
        $id_usuario = $datos_usuario['id_usuario'];
        $tipo_documento = $datos_usuario['tipo_documento'];
        $documento = $datos_usuario['documento'];
        
        // 3. Consulta de Tarjetas: Llama al servicio Activo para obtener las tarjetas de débito.
        $getTarjeta = $this->getTarjeta($tipo_documento, $documento);
        
        // 4. Inicialización de Variables de Vista: Nombres RESTAURADOS para compatibilidad con el CTP.
        $cuentas_dropdown = array();
        $tarjetasConEstatus = array();
        $tarjetas_dropdown = array();
        $primerEstatus = 'Sin tarjetas disponibles';
        $res = array();
        $cuentaPrincipalAsociada = '';
        $cuentaAsociadaDisplay = 'No hay cuentas para esta tarjeta';

        // Mapeo interno: Claves SOAP para el tipo de cuenta y su descripción.
        $cuentas_claves = array('CLAFICCL'=>'Contactless Activa', 'CLAFICPP'=>'Principal', 'CLAFICSC'=>'Secundaria');

        // 5. Procesamiento de Tarjetas: Consulta el estatus Contactless de cada tarjeta.
        foreach ($getTarjeta as $numeroTarjeta) {
            $moneda = ' - VES';
            
            // Llama al servicio ContactLess SOAP con la acción 'C' (Consulta).
            $res = $this->ContactLess->contactLessSoap(self::ACCION_CONSULTA, $id_usuario, $numeroTarjeta);
            
            // Extracción y mapeo del estatus y cuentas
            $estatus_raw = 'E'; // Estatus inicial por defecto (Error)
            $data_return = isset($res['CLAFILI01Return']['CLAFILI01']) ? $res['CLAFILI01Return']['CLAFILI01'] : null;
            
            if (isset($data_return['CLAFISTS'])) {
                $estatus_raw = $data_return['CLAFISTS'];
            }

            // Itera sobre las posibles cuentas asociadas a la tarjeta
            foreach ($cuentas_claves as $soap_key => $sufijo) {
                if (isset($data_return[$soap_key])) {
                    $cuenta_raw = trim($data_return[$soap_key]);
                    
                    // Solo si la cuenta existe y no es '0'
                    if (!empty($cuenta_raw) && $cuenta_raw !== '0') {
                        $cuenta_enmascarada = $this->_enmascararCuenta($cuenta_raw);
                        $cuenta_con_sufijo = $cuenta_enmascarada . ' (' . $sufijo . ')';
                        
                        // Añade la cuenta al dropdown si no está ya presente (por si una cuenta tiene múltiples roles)
                        if (!array_key_exists($cuenta_raw, $cuentas_dropdown)) {
                            $cuentas_dropdown[$cuenta_raw] = $cuenta_con_sufijo;
                        }
                        // Identifica la cuenta principal activa (CLAFICCL)
                        if ($soap_key === 'CLAFICCL') {
                            $cuentaPrincipalAsociada = $cuenta_raw;
                            $cuentaAsociadaDisplay = $cuenta_con_sufijo;
                        }
                    }
                }
            }

            // Mapea el estatus crudo a un texto legible y lo guarda.
            $estatus_mapeado = $this->mapEstatus($estatus_raw);
            $tarjetasConEstatus[$numeroTarjeta] = $estatus_mapeado;
            
            // Prepara el texto enmascarado para el dropdown de tarjetas.
            $tarjetas_dropdown[$numeroTarjeta] = $this->_enmascararTarjeta($numeroTarjeta, false) . $moneda;
            
            // Guarda el estatus de la primera tarjeta para mostrarlo por defecto.
            if ($primerEstatus == 'Sin tarjetas disponibles') {
                $primerEstatus = $estatus_mapeado;
            }
        }

        // 6. Manejo de Cuentas Vacías: Si no hay cuentas, se establece un mensaje por defecto.
        if (empty($cuentas_dropdown)) {
            $cuentas_dropdown[''] = 'No hay cuentas para esta tarjeta';
        }

        // 7. Almacenamiento en Sesión: Guarda datos clave del cliente para futuras transacciones.
        $this->_writeContactlessState(array(
            'cedulaCliente' => $documento,
            'tipoDocumentoCliente' => $tipo_documento,
            'getTarjeta' => $getTarjeta,
            'cuentaPrincipal' => $cuentaPrincipalAsociada
        ));

        // 8. Registro de Bitácora: Log de la consulta realizada.
        $log_data = isset($res['error']) ? $res['error'] : (isset($res['CLAFILI01Return']) ? $res['CLAFILI01Return'] : 'N/A');
        $this->Bitacora->registraBitacora($datos_usuario['usuario']['id'], self::TRX_CONSULTA, NULL, NULL, NULL, NULL, 'Consulta Contactless Soap: ' . json_encode($log_data), 0);

        // 9. Configuración de Vista: Se define la sección y el layout.
        $this->set('seccion_actual', 'contact.less');
        $this->layout = 'interna';
        
        // 10. Envío de Variables a la Vista (.ctp). Los nombres DEBEN ser exactos.
        $this->set(compact('documento', 'tipo_documento'));
        $this->set('statueResponse', $primerEstatus);
        $this->set('tarjetas_dropdown', $tarjetas_dropdown);
        $this->set('cuentas_dropdown', $cuentas_dropdown);
        $this->set('cuentaPrincipalAsociada', $cuentaPrincipalAsociada);
        $this->set('cuentaAsociadaDisplay', $cuentaAsociadaDisplay);
        $this->set('tarjetasConEstatus', $tarjetasConEstatus);
    }

    /**
     * [Paso 2] Recibe los datos del formulario de consulta y determina la acción.
     * * Se ejecuta cuando el usuario presiona 'Activar', 'Inactivar' o 'Modificar'
     * en la pantalla de consulta. Almacena la acción en sesión y redirige a la confirmación.
     */
    function cambiarEstatus() {
        // 1. Verificar Permiso.
        if (!$this->_checkPermiso()) return;
        
        // 2. Validación de datos recibidos por POST.
        if (empty($this->data)) {
            $this->Session->setFlash('Acción no válida. Se requiere enviar datos de tarjeta.', 'alerta');
            $this->redirect(array('action' => 'consulta'));
            return;
        }

        $numeroTarjeta = $this->data['GetTarjetaSelect']['numTarjeta'];
        $numeroCuenta = isset($this->data['GetTarjetaSelect']['numCuenta']) ? $this->data['GetTarjetaSelect']['numCuenta'] : null;
        
        $accion = '';
        $mensaje_accion = '';

        // 3. Determinar la acción solicitada por el usuario.
        if (isset($this->data['GetTarjetaSelect']['activar'])) {
            $accion = self::ACCION_ACTIVAR;
            $mensaje_accion = 'Activaci&oacute;n';
        } elseif (isset($this->data['GetTarjetaSelect']['inactivar'])) {
            $accion = self::ACCION_INACTIVAR;
            $mensaje_accion = 'Inactivaci&oacute;n';
        } elseif (isset($this->data['GetTarjetaSelect']['modificar'])) {
            $accion = self::ACCION_MODIFICAR;
            $mensaje_accion = 'Modificaci&oacute;n de Cuenta';
        } else {
            $this->Session->setFlash('Acción de cambio de estatus no especificada.', 'alerta');
            $this->redirect(array('action' => 'consulta'));
            return;
        }

        // 4. Validación de la Cuenta (necesaria para cualquier acción).
        if (empty($numeroCuenta)) {
            $this->Session->setFlash('Debe seleccionar una cuenta válida para continuar con la operación.', 'alerta');
            $this->redirect(array('action' => 'consulta'));
            return;
        }

        // 5. Almacenamiento en Sesión: Guarda los detalles de la acción para el siguiente paso (Confirmación).
        $this->_writeContactlessState(array(
            'accion' => $accion,
            'mensaje_accion' => $mensaje_accion,
            'numeroTarjeta' => $numeroTarjeta,
            'numeroCuenta' => $numeroCuenta
        ));
        
        // 6. Redirección al paso de Confirmación.
        $this->redirect(array('action' => 'consulta_confirmacion'));
    }

    /**
     * [Paso 3] Pantalla de Confirmación.
     * * Muestra al usuario la acción que está a punto de realizar y los datos
     * de la tarjeta/cuenta enmascarados para su verificación.
     */
    function consulta_confirmacion() {
        // 1. Verificar Permiso.
        if (!$this->_checkPermiso()) return;
        
        // 2. Configuración de Vista.
        $this->set('seccion_actual', 'contact.less');
        $this->layout = 'interna';
        
        // 3. Recuperar datos de la acción desde la sesión.
        $numeroTarjeta = $this->_readContactlessState('numeroTarjeta');
        $mensaje_accion = $this->_readContactlessState('mensaje_accion');
        $numeroCuenta = $this->_readContactlessState('numeroCuenta');

        // 4. Procesamiento y Envío a la Vista.
        if ($numeroTarjeta && $mensaje_accion && $numeroCuenta) {
            $tarjeta_enmascarada = $this->_enmascararTarjeta($numeroTarjeta, false);
            $cuenta_enmascarada = $this->_enmascararCuenta($numeroCuenta);
            
            // Envía variables a la vista para el resumen de confirmación.
            $this->set(compact('tarjeta_enmascarada', 'mensaje_accion', 'cuenta_enmascarada', 'numeroCuenta'));
        } else {
            // Error si faltan datos en la sesión.
            $this->Session->setFlash('La acción a confirmar no fue encontrada.', 'alerta');
            $this->redirect('consulta');
        }
    }

    /**
     * [Paso 4] Ejecuta la Acción de Cambio de Estatus (Activación/Inactivación/Modificación).
     * * Valida el OTP, llama al servicio SOAP de ejecución, procesa la respuesta
     * y registra la transacción en la bitácora.
     */
    function ejecutarAccion() {
        // 1. Verificar Permiso.
        if (!$this->_checkPermiso()) return;

        // 2. Recuperar datos de la acción desde la sesión.
        $accion = $this->_readContactlessState('accion');
        $numeroTarjeta = $this->_readContactlessState('numeroTarjeta');
        $numeroCuenta = $this->_readContactlessState('numeroCuenta');
        $mensaje_accion_log = $this->_readContactlessState('mensaje_accion');
        
        $datos_usuario = $this->_obtenerDatosUsuario();
        $id_usuario = $datos_usuario['id_usuario'];

        // 3. Validación de datos de sesión.
        if (empty($accion) || empty($numeroTarjeta) || empty($numeroCuenta)) {
            $this->Session->setFlash('Error: Datos de acción incompletos.', 'alerta');
            $this->redirect(array('action' => 'consulta'));
            return;
        }

        // 4. Validación OTP: Verifica si el usuario ha pasado por el componente de seguridad OTP.
        $validacion_otp = $this->Session->read('seguridad.validar_otp');
        if (!isset($validacion_otp['validado']) || !$validacion_otp['validado']) {
            // Si no está validado, configura la sesión para redirigir a la generación de OTP.
            $this->Session->write('seguridad.validar_otp', array(
                'url_aceptar' => '/contact_less/ejecutarAccion', // URL a la que regresa al validar.
                'url_volver' => '/contact_less/consulta',
                'layout' => 'interna',
                'titulo' => $mensaje_accion_log . ' Servicio Contact Less',
                'pasos' => array('Solicitud', 'Confirmacion', 'Autorizacion', 'Resultado'),
                'paso_actual' => 3,
                'seccion_actual' => 'contact.less'
            ));
            $this->redirect('/seguridad/genera_otp');
            return;
        }
        // Si el OTP fue validado, se elimina la bandera de la sesión.
        $this->Session->delete('seguridad.validar_otp');

        // 5. Ejecución del Servicio: Llama al servicio SOAP con la acción y los datos.
        $res = $this->ContactLess->contactLessSoap($accion, $id_usuario, $numeroTarjeta, $numeroCuenta);
        
        // 6. Procesar Respuesta: Normaliza el resultado del servicio para el usuario.
        $resultado = $this->_procesarRespuesta($res, $accion, $numeroTarjeta, $numeroCuenta, $mensaje_accion_log, $datos_usuario);

        // 7. Registro de Bitácora: Log de la transacción de ejecución.
        $this->Bitacora->registraBitacora(
            $datos_usuario['usuario']['id'], 
            self::TRX_EJECUCION, 
            NULL, NULL, NULL, NULL, 
            'Ejecución Contactless (' . $mensaje_accion_log . ') Tarjeta: ' . $numeroTarjeta . ' Cuenta: ' . $numeroCuenta . ' - Resultado RAW: ' . $resultado['estatus_final_raw'], 
            $resultado['error_flag']
        );

        // 8. Almacenar Resultado y Limpiar Sesión.
        $this->Session->write('contact_less.resultado', $resultado);
        $this->Session->delete('contact_less.accion');
        $this->Session->delete('contact_less.mensaje_accion');
        $this->Session->delete('contact_less.numeroTarjeta');
        $this->Session->delete('contact_less.numeroCuenta');
        
        // 9. Redirección a la pantalla de Resultado.
        $this->redirect(array('action' => 'consulta_resultado'));
    }

    /**
     * [Paso 5] Pantalla de Resultado.
     * * Muestra el resumen del resultado de la ejecución al usuario (éxito o error).
     */
    function consulta_resultado() {
        // 1. Verificar Permiso.
        if (!$this->_checkPermiso()) return;
        
        // 2. Configuración de Vista.
        $this->layout = 'interna';
        $this->set('seccion_actual', 'contact.less');
        
        // 3. Recuperar el resultado de la sesión.
        $resultado = $this->Session->read('contact_less.resultado');
        
        // 4. Mostrar o Redirigir.
        if (!empty($resultado)) {
            $this->set('resultado', $resultado);
            $this->Session->delete('contact_less.resultado');
        } else {
            // Si no hay resultado en sesión, es una entrada inválida.
            $this->Session->setFlash('No se encontró el resultado de la operación.', 'alerta');
            $this->redirect('consulta');
        }
    }


    // =========================================================================
    // 3. MÉTODOS AUXILIARES Y PRIVADOS
    // =========================================================================

    /**
     * Valida si el usuario tiene permiso para acceder a la funcionalidad.
     * Utiliza la función global `validarTransaccion` con el código de permiso.
     * @return bool True si tiene permiso, False y redirige si no lo tiene.
     */
    private function _checkPermiso() {
        if (!validarTransaccion(self::TRX_PERMISO)) {
            $this->redirect('/consultas/integral');
            return false;
        }
        return true;
    }

    /**
     * Lee un valor específico de la sección 'contact_less' en la sesión.
     * @param string $key La clave a leer (ej: 'numeroTarjeta').
     * @return mixed El valor almacenado o null si no existe.
     */
    private function _readContactlessState($key) {
        return $this->Session->read("contact_less.$key");
    }

    /**
     * Escribe datos en la sección 'contact_less' de la sesión, fusionando con lo existente.
     * @param array $data El arreglo de claves y valores a guardar.
     */
    private function _writeContactlessState($data) {
        $session_data = $this->Session->read('contact_less');
        $current = !empty($session_data) ? $session_data : array();
        $this->Session->write('contact_less', array_merge($current, $data));
    }

    /**
     * Obtiene los datos del usuario logueado de la sesión, incluyendo ID y documento.
     * @return array Contiene el arreglo 'usuario', 'id_usuario', 'tipo_documento', y 'documento' (desencriptado).
     */
    function _obtenerDatosUsuario() {
        $usuario = $this->Session->read('usuarios.datos');
        $tipo_documento = '';
        $documento = '';
        
        // Determina si el usuario tiene RIF o Cédula para obtener el documento correcto.
        if (isset($usuario['tipo_rif']) && $usuario['tipo_rif']) {
            $tipo_documento = $usuario['tipo_rif'];
            $documento = desencriptar($usuario['rif']);
        } else {
            $tipo_documento = $usuario['tipo_cedula'];
            $documento = desencriptar($usuario['cedula']);
        }
        return array('usuario' => $usuario, 'id_usuario' => $usuario['usuario_id'], 'tipo_documento' => $tipo_documento, 'documento' => $documento);
    }

    /**
     * Enmascara el número de tarjeta (muestra los primeros 6 y los últimos 4 dígitos).
     * @param string $numeroTarjeta Número de tarjeta completo.
     * @param bool $incluirMoneda Indica si se debe agregar el sufijo ' - VES'.
     * @return string El número de tarjeta enmascarado y formateado.
     */
    function _enmascararTarjeta($numeroTarjeta, $incluirMoneda = true) {
        $numeroTarjeta = trim($numeroTarjeta);
        if (strlen($numeroTarjeta) < 10) {
            $texto_enmascarado = $numeroTarjeta;
        } else {
            // Formato: 6 primeros dígitos, 3 asteriscos, 4 últimos dígitos.
            $texto_enmascarado = substr($numeroTarjeta, 0, 6) . '***' . substr($numeroTarjeta, -4);
        }
        $texto = 'Tarjeta Debito (' . $texto_enmascarado . ')';
        return $incluirMoneda ? $texto . ' - VES' : $texto;
    }

    /**
     * Enmascara el número de cuenta, dejando visibles el prefijo '0171' y los últimos 4 dígitos.
     * @param string $numeroCuenta Número de cuenta completo.
     * @return string El número de cuenta enmascarado y formateado.
     */
    function _enmascararCuenta($numeroCuenta) {
        $numeroCuenta = trim($numeroCuenta);
        if (strlen($numeroCuenta) < 8) return $numeroCuenta . ' - Formato inválido o incompleto';
        
        // Calcula cuántos asteriscos se deben mostrar entre el prefijo y el sufijo.
        $longitud = strlen($numeroCuenta) - 8;
        $parte_enmascarada = ($longitud > 0) ? str_repeat('*', $longitud) : '';
        
        // Se asume el prefijo '0171' (código de banco)
        return '0171' . $parte_enmascarada . substr($numeroCuenta, -4);
    }

    /**
     * Invoca el servicio para obtener las tarjetas de débito asociadas a un cliente.
     * @param string $tipo_documento Tipo de documento (ej: 'V', 'E').
     * @param string $nro_documento Número de documento.
     * @return array Arreglo con los números de tarjeta obtenidos.
     */
    function getTarjeta($tipo_documento, $nro_documento) {
        // Formateo del ID Cliente requerido por el servicio.
        $numero_limpio = ltrim((string)$nro_documento, '0');
        $numero_formateado = str_pad($numero_limpio, 9, '0', STR_PAD_LEFT);
        $idCliente_formateado = $tipo_documento . $numero_formateado;
        
        // Invoca el servicio 'getTarjetas' del componente Activo.
        $tarjetas = $this->Activo->invoca('getTarjetas', array('idCliente' => $idCliente_formateado, 'canal' => '04'));
        
        // Filtra y extrae solo el valor de la primera tarjeta encontrada.
        $tarjetas_filtradas = array();
        if (isset($tarjetas['getTarjetasReturn']['tarjeta1'])) {
            $valor = trim($tarjetas['getTarjetasReturn']['tarjeta1']);
            if (!empty($valor)) $tarjetas_filtradas['tarjeta1'] = $valor;
        }
        return $tarjetas_filtradas;
    }

    /**
     * Mapea el estatus crudo recibido del servicio SOAP (ej: 'A', 'I') a un texto legible.
     * @param string $estatus_raw El código de estatus crudo.
     * @return string La descripción del estatus para el usuario.
     */
    function mapEstatus($estatus_raw) {
        $map = array(
            self::ACCION_INACTIVAR => "Inactivo",
            self::ACCION_ACTIVAR => "Activo",
            self::ACCION_MODIFICAR => "Cuenta modificada"
        );
        $estatus_raw = trim($estatus_raw);
        return isset($map[$estatus_raw]) ? $map[$estatus_raw] : "Tarjeta no contiene servicio Contactless";
    }

    /**
     * Procesa la respuesta del servicio SOAP de ejecución, define el mensaje
     * final para el usuario y prepara el envío de correo.
     * @param array $res Respuesta del servicio SOAP.
     * @param string $accion Código de la acción solicitada (A, I, M).
     * @param string $numeroTarjeta Número de tarjeta afectado.
     * @param string $numeroCuenta Número de cuenta asociado.
     * @param string $mensaje_accion_log Descripción de la acción para logs.
     * @param array $datos_usuario Datos del usuario logueado.
     * @return array Arreglo con el resultado final formateado para la vista.
     */
    function _procesarRespuesta($res, $accion, $numeroTarjeta, $numeroCuenta, $mensaje_accion_log, $datos_usuario) {
        // Inicialización de variables de resultado
        $tarjeta_enmascarada = $this->_enmascararTarjeta($numeroTarjeta, false);
        $cuenta_enmascarada = $this->_enmascararCuenta($numeroCuenta);
        $fecha_accion = date('d/m/Y H:i:s');
        $error_flag = 1; // Por defecto es error (para bitácora)
        $estatus_final_raw = 'E_INIT';
        $estatus_texto = 'Error de Servicio';
        $descripcion_texto = 'No pudimos ejecutar su petición, por favor intente de nuevo. Si ocurre de nuevo por favor llamar a soporte.';
        $accion_verbo_correo = '';
        $asunto_correo = 'Notificacion de Estatus de Servicio Contactless';

        // 1. Manejo de Error de Comunicación
        if (isset($res['error'])) {
            $estatus_final_raw = self::ESTATUS_ERROR_COMM;
            $descripcion_texto = 'Error de Comunicación con el Servicio: ' . $res['error'];
        } 
        // 2. Manejo de Respuesta con Datos
        elseif (isset($res['CLAFILI01Return']['CLAFILI01']['CLAFIACC'])) {
            $estatus_final_raw = trim($res['CLAFILI01Return']['CLAFILI01']['CLAFIACC']);
            
            // 2a. Éxito: El estatus final es igual a la acción solicitada.

            if ($estatus_final_raw === $accion) {
                $error_flag = 0;
                if ($accion === self::ACCION_ACTIVAR) {
                    $estatus_texto = 'ACTIVADA';
                    $descripcion_texto = 'Su servicio Contactless ha sido activada satisfactoriamente.';
                    // TEXTO CORREGIDO
                    $accion_verbo_correo = 'activacion del servicio contactless de tu tarjeta de debito';
                    $asunto_correo = 'Notificacion Activacion exitosa de servicio Contactless';
                } elseif ($accion === self::ACCION_INACTIVAR) {
                    $estatus_texto = 'DESACTIVADA';
                    $descripcion_texto = 'Su servicio Contactless ha sido desactivada satisfactoriamente.';
                    // TEXTO CORREGIDO
                    $accion_verbo_correo = 'desactivacion del servicio contactless de tu Tarjeta de Debito';
                    $asunto_correo = 'Notificacion Desactivacion exitosa de servicio Contactless';
                } elseif ($accion === self::ACCION_MODIFICAR) {
                    $estatus_texto = 'CUENTA MODIFICADA';
                    $descripcion_texto = 'El cambio de cuenta Contactless se realizo satisfactoriamente.';
                    // TEXTO CORREGIDO
                    $accion_verbo_correo = 'modificacion de la cuenta asociada al servicio contactless de tu tarjeta de debito';
                    $asunto_correo = 'Notificacion Modificacion exitosa de cuenta Contactless';
                }

                $this->_enviarCorreoExito(array(
                    'usuario' => $datos_usuario['usuario'],
                    'asunto_correo' => $asunto_correo,
                    'accion_verbo_correo' => $accion_verbo_correo,
                    'tarjeta_enmascarada' => $tarjeta_enmascarada, // Se mantiene por seguridad si decides mostrarla
                    'fecha_accion' => $fecha_accion
                ));
            }
            // 2b. Fallo: El servicio retorna un estatus diferente a la acción solicitada.
            else {
                $mensaje_servicio = isset($res['CLAFILI01Return']['CLAFILI01']['CLAFIMSG']) ? $res['CLAFILI01Return']['CLAFILI01']['CLAFIMSG'] : 'Respuesta de estatus inesperada.';
                $descripcion_texto = "La {$mensaje_accion_log} falló: " . $mensaje_servicio;
                $estatus_texto = $this->mapEstatus($estatus_final_raw); // Intenta mapear el estatus de error.
            }
        } 
        // 3. Manejo de Error de Estructura
        else {
            $estatus_final_raw = self::ESTATUS_ERROR_STRUCT;
            $descripcion_texto = 'Error interno: Estructura de respuesta de servicio inesperada.';
        }

        // Retorna el arreglo de resultado final.
        return array(
            'tarjeta_enmascarada' => $tarjeta_enmascarada,
            'numero_cuenta' => $cuenta_enmascarada,
            'fecha_accion' => $fecha_accion,
            'estatus_texto' => $estatus_texto,
            'descripcion_texto' => $descripcion_texto,
            'error_flag' => $error_flag,
            'accion_solicitada' => $mensaje_accion_log,
            'estatus_final_raw' => $estatus_final_raw
        );
    }

    /**
     * Envía una notificación por correo electrónico al usuario tras una ejecución exitosa.
     * @param array $datos_accion Contiene los datos necesarios para el cuerpo del correo.
     */
    function _enviarCorreoExito($datos_accion) {
        // Carga datos adicionales para el nombre del usuario (asumiendo que vienen de otro servicio/sesión).
        $datos_basicos = $this->Session->read('usuarios.nibpf3010');
        $usuario = $datos_accion['usuario'];
        $nombre_usuario = '';
        
        // Determina el nombre del usuario a partir del RIF o del nombre encriptado.
        if (isset($usuario['tipo_rif']) && $usuario['tipo_rif']) {
            $nombre_usuario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
        } else {
            $nombre_usuario = desencriptar($usuario['nombre']);
        }
 
        // Configuración del componente Email de CakePHP
        $this->Email->smtpOptions = Configure::read('email.smtp');
        $this->Email->delivery = 'smtp';
        $this->Email->from = Configure::read('email.from');
        $this->Email->to = desencriptar($usuario['email_principal']);
        $this->Email->subject = $datos_accion['asunto_correo'];
        $this->Email->sendAs = 'html';
        $this->Email->template = 'contact_less'; // Se asume una plantilla de correo llamada 'contact_less.ctp'
        
        // Envía variables a la plantilla de correo.
        $this->set(array(
            'nombre_usuario' => $nombre_usuario,
            'accion_verbo_correo' => $datos_accion['accion_verbo_correo'],
            'tarjeta_enmascarada' => $datos_accion['tarjeta_enmascarada'],
            'fecha_accion' => $datos_accion['fecha_accion'],
            'usuario' => $usuario
        ));
        
        // Intenta enviar el correo y registra el error si falla.
        if (!$this->Email->send()) {
            error_log('Error al enviar correo de Contactless: ' . $this->Email->smtpError);
        }
    }
}