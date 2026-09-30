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
class LbLog extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'lb_log';


    function guardar_log($data){
        $this->save($data);
        return true;
    }





	
}

?>