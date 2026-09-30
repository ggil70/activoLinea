<?php
/**
 * Controlador del menú móvil
 */

/**
 * Controlador del menú móvil
 */
class MenuMovilController extends AppController {
	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array('Usuario','Afiliacion', 'Bitacora');

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session', 'Email');

	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */
	var $helpers = array('Activo','Html','Session','Form','Text');
	
	
	/**
	 * Acción principal del controlador
	 */
	function index(){
		$this->layout='interna';
		
	}
} ?>