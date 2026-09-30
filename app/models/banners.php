<?php
/**
 * Modelo para el manejo de los banners del sistema.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los banners del sistema.
 * 
 */
class Banners extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'banners';
	
	
	/**
	 * Busqueda de los banners según la sección indicada.
	 * 
	 * @param int $seccion_id
	 * @return array
	 */
	function porSeccion($seccion_id=1) {
		App::import('Component', 'Session');
		
		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');
		
		$key = 'seccion_'.$seccion_id;
		
		$key .= $juridico ? '_j' : '_n';

		$tipo_portal = $juridico ? 'J' : 'N';

		if (($banners = Cache::read($key,'banners')) === false) {
			
			$banners = $this->find(
				'all',
				array(
					'fields' => array(
						'Banners.id',
						'Banners.archivo_1',
						'Banners.url',
						'Banners.seccion_id'
					),
					'conditions' => array(
						'Banners.estatus' => 'A',
						'Banners.tipo_portal' => array('A', $tipo_portal),
						'Banners.seccion_id' => $seccion_id,
						'? BETWEEN Banners.fecha_inicio AND Banners.fecha_fin' => date('Y-m-d')
					)
				)
			);
			
			Cache::write($key,$banners,'banners');
		}
		
		return $banners;
	}
}
?>