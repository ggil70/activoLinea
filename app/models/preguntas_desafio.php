<?php
/**
 * Modelo para el manejo de las preguntas de desafío.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de las preguntas de desafío.
 * 
 */
class PreguntasDesafio extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'preguntas_desafio';
	
	
	/**
	 * Busca y retorna las preguntas de desafío.
	 * 
	 * @return array
	 */
	function buscarTodas(){
		return array_merge(
			$this->find(
				'list',
				array(
					'fields'=>'id,pregunta'
				)
			),
			array('otra'=>'Otra')
		);
	}
}

?>