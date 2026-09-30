<?php
/**
 * Modelo para el manejo de las empresas que participarán en el proceso de selección para auditorías externas.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los empresas auditoras de IB.
 * 
 */
class EmpresasAuditoras extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'empresas_auditoras';
	
	/**
	* Acceso a la base de datos de Autidores Externos, necesario para que consulte en esta base de datos.
	*/
	public $useDbConfig ='auditores_externos_conexion';
	
}

?>