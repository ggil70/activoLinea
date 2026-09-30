<?php
/**
 * Componente que maneja las respuestas de api activo pay
 * 
 * @author Andrés S.
 */


/**
 * Importación de la librería del manejo de los webservices.
 */
App::Import('Vendor', 'nusoap/nusoap');

class ActivoPayComponent extends Object {

	/**
	 * Controlador
	 * 
	 * @var string 
	 */
	var $controller;
	
	/**
	 * Objeto de conexión del webservice
	 * 
	 * @var object
	 */
	var $client;
	
	/**
	 * Componentes del cake utilizados
	 * 
	 * @var array 
	 */
	var $components = array('Session');

	
	/**
	 * Método invocado inicialmente por el CakePHP
	 * 
	 * @param string $controller
	 * @param array $settings
	 */
	function initialize(&$controller, $settings = array()) {
		$this->controller = $controller;
	}	


/**
 * Función para interactuar con la API de Activo Pay.
 * * @param string $nro_documento Número de documento a consultar.
 * @param string $tipo_documento Tipo de documento (e.g., 'DNI').
 * @return array|string Devuelve la respuesta o un array de error.
 */
function activoPayApi($nro_documento, $tipo_documento)
{
    // 1. Cargar la librería HttpSocket (común en CakePHP)
    // Asegúrate de que esta línea esté correcta para tu versión de CakePHP
    App::import('Core', 'HttpSocket');
    $HttpSocket = new HttpSocket();

    // 2. Definir la URL de la API
    // Asume que Configure::read('Activo_Pay_Api.url') funciona correctamente.
    $url = Configure::read('Activo_Pay_Api.url');

    // 3. Preparar el cuerpo de la solicitud (array que será codificado a JSON)
    $data = array(
        'nro_documento' => $nro_documento,
        'tipo_documento' => $tipo_documento
    );

    // 4. Configurar los encabezados (Headers)
    $request_config = array(
        'header' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        )
    );

    // 5. Ejecutar la solicitud POST y obtener la respuesta JSON (como string)
    $response_json = $HttpSocket->post(
        $url,
        json_encode($data),
        $request_config
    );

    // 6. Decodificar la cadena JSON a un array asociativo de PHP
    $response_array = json_decode($response_json, true);

    // --- 7. Manejar y devolver la respuesta ---

    // Verificar si la decodificación fue exitosa y si la clave 'error' existe
    if ($response_array === null) {
        // La respuesta no fue un JSON válido o hubo un error de conexión
        return array('error' => 'Error: Respuesta no válida o vacía de la API.');
    }

    if (!isset($response_array['error'])) {
        // La estructura de la respuesta es inesperada (falta la clave 'error')
        return array('error' => 'Error: La respuesta de la API tiene una estructura inesperada.');
    }
    
    // Obtener el código de error para simplificar las comparaciones
    $errorCode = $response_array['error']; 
    
    // NOTA: Usamos comparaciones con STRING ('0000', '1000') porque 
    // la API los devuelve como strings en el JSON.

    if ($errorCode === '0000') {
        // Éxito: Código 0000
        return  $response_array;
    } elseif ($errorCode === '1000') {
        // Error específico 1000
        // Retornamos el mensaje original que tenías planeado.
        return  $response_array;
    } else {
        // Cualquier otro código de error
        return array('error' => 'Error de la API con código: ' . $errorCode);
    }
}

// Nota: La variable 'Configure' y la función 'App::import' son específicas 
// de tu framework (probablemente CakePHP) y se asume que están definidas.
}