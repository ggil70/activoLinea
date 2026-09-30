<?php
/**
 * Modelo para el manejo de las solicitudes de tarjetas de coordenadas.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de las solicitudes de tarjetas de coordenadas.
 * 
 */
class TcpLogsError extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'tcp_logs_error';

	
	function guardar_logs($data){

		$this->save($data);
    
		return true;

	}	
             


	
}

?>