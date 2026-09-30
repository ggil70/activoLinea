<?php
/**
 * Componente que encapsula y maneja la comunicación con el Web Service SOAP de Contactless
 * de Credicard para la consulta, modificación e inactivación de las cuentas asociadas a una tarjeta.
 *
 * @author Andrés S. (Refactorizado por IA)
 */

/**
 * Importación obligatoria de la librería de manejo de los webservices (nusoap).
 * Esta ruta debe ser accesible desde la estructura de vendors de CakePHP.
 */
App::Import('Vendor', 'nusoap/nusoap');

class ContactLessComponent extends Object {

    /**
     * Instancia del controlador que carga este componente.
     * @var object
     */
    var $controller;
    
    /**
     * Objeto de conexión de nusoap_client para interactuar con el Web Service.
     * @var object
     */
    var $client;
    
    /**
     * Componentes de CakePHP que se pueden requerir dentro de este componente.
     * @var array
     */
    var $components = array('Session');

    
    /**
     * Método invocado por el CakePHP al inicializar el componente.
     * Guarda una referencia al controlador principal para su uso posterior.
     *
     * @param object $controller Referencia al controlador actual.
     * @param array $settings Configuraciones opcionales pasadas al componente.
     */
    function initialize(&$controller, $settings = array()) {
        $this->controller = $controller;
    }   


    /**
     * @access public
     * Llama al servicio SOAP 'CLAFILI01' para Consultar ('C'), Modificar ('M') o Inactivar ('I')
     * la cuenta asociada al servicio Contactless de una tarjeta de débito específica.
     *
     * @param string $clafiacc Código de acción a ejecutar: 'C', 'M' o 'I'.
     * @param string $claficus ID de cliente o código de usuario (CLAFICUS).
     * @param string $clafitar Número de la tarjeta de débito (CLAFITAR).
     * @param string $claficcl Número de cuenta Contact Less activo/a cambiar. (Solo para M/I)
     * @param string $claficpp Número de cuenta Principal (a activar/desactivar). (Solo para M/I)
     * @param string $claficsc Número de cuenta Secundaria (a la que se cambia si es M/I). (Solo para M/I)
     * @return mixed Devuelve el array de la respuesta SOAP en caso de éxito, o el valor '1' si ocurre un error fatal.
     */
    function contactLessSoap($clafiacc, $claficus, $clafitar, $claficcl = '', $claficpp = '', $claficsc = ''){

        // Namespaces utilizados para el request (ajustados a los ejemplos proporcionados).
        $ser_namespace = "http://servicios.gateway.activo.com"; 
        $bean_namespace = "http://beans.gateway.activo.com";    
        
        // 1. Construcción del Request XML SOAP.
        // Los campos de cuenta se poblarán según los parámetros, si no se reciben se enviarán vacíos (necesario para 'C').

        // Uso de variables para evitar confusiones y mejorar legibilidad:
        $_claficcl = !empty($claficcl) ? $claficcl : '';
        $_claficpp = !empty($claficpp) ? $claficpp : '';
        $_claficsc = !empty($claficsc) ? $claficsc : '';

        $req =
            "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" 
                             xmlns:ser=\"$ser_namespace\" 
                             xmlns:bean=\"$bean_namespace\">
                <soapenv:Header/>
                <soapenv:Body>
                    <ser:CLAFILI01>
                        <ser:req>
                            <bean:canal></bean:canal>
                            <bean:CLAFIACC>$clafiacc</bean:CLAFIACC>
                            <bean:CLAFICCL>$_claficcl</bean:CLAFICCL>
                            <bean:CLAFICPP>$_claficpp</bean:CLAFICPP>
                            <bean:CLAFICSC>$_claficsc</bean:CLAFICSC>
                            <bean:CLAFICUS>$claficus</bean:CLAFICUS>
                            <bean:CLAFIFEC></bean:CLAFIFEC>
                            <bean:CLAFIFLG></bean:CLAFIFLG>
                            <bean:CLAFISTS></bean:CLAFISTS>
                            <bean:CLAFITAR>$clafitar</bean:CLAFITAR>
                            <bean:CLAFIUSR></bean:CLAFIUSR>
                        </ser:req>
                    </ser:CLAFILI01>
                </soapenv:Body>
            </soapenv:Envelope>";


        // 2. Inicialización de la Conexión con nusoap_client (Lazy Loading).
        // Se mantiene la lógica de inicialización.
        if(!isset($this->client)){
            // Obtener la URL del Web Service de Contactless desde la configuración (Configure::read).
            $this->client = new nusoap_client(Configure::read('Gateway.Contactless.url'));
            // Configuración de tiempos de espera para una operación de larga duración (en segundos).
            $this->client->timeout = 300;
            $this->client->response_timeout = 300;
        }   

        // 3. Envío de la Solicitud SOAP.
        $this->client->operation = "CLAFILI01";
        // Se envía el Request XML y se almacena la respuesta.
        $res = $this->client->send($req);
        // die(json_encode($res));
        // 4. Bloque de Manejo de Errores de Conexión (nusoap_client).
        $err_client = $this->client->getError();
        if($err_client){
            $this->log('Error de Cliente (nusoap) en CLAFILI01: ' . $err_client, LOG_ERR);
            if(isset($this->controller)){
                // Asignar mensaje de error genérico para la vista (asumiendo _m() es una función de internacionalización/mensajes)
                $this->controller->set('error_fatal', _m('error.fatal')); 
            }
            return 1; // Retornar 1 (código de error)
        }
        
        // 5. Bloque de Manejo de Errores del Servidor SOAP (Fault).
        if($this->client->fault){
            $this->log('Error técnico (SOAP Fault) en CLAFILI01:', LOG_ERR);
            $this->log($res, LOG_ERR);
            if(isset($this->controller)){
                $this->controller->set('error_fatal',_m('error.fatal'));
            }
            return 1; // Retornar 1 (código de error)
        } else {
            // 6. Manejo de Errores de nusoap que no causaron un fault (ej: error en la respuesta).
            $err = $this->client->getError();
            if($err){
                $this->log('Error de Cliente (nusoap) después del envío en CLAFILI01: ' . $err, LOG_ERR);
                return 1; // Retornar 1 (código de error)
            }
        }

        // 7. Retorno Exitoso.
        return $res;
    }
}
?>