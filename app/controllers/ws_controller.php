<?php
/**
 * Controlador usado como webservice para el borrado de cache de los banners.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */

/**
 * Webservice para el borrado de cache de los banners.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */
class WsController extends Controller{
	
	
	/**
	 * Modelos que usa el controlador 
	 * 
	 * @var array 
	 */
	var $uses = array(
		'Sello',
		'Usuario',
	);

	
	/**
	 * Método para eliminar el cache de los banners mostrados en el internet banking.
	 * 
	 */
	function borrar_cache(){
		$form = $this->params['form'];
		
		if(
			isset($form['usuario'], $form['clave']) &&
			$form['usuario'] == Configure::read('ib.ws.user') &&
			$form['clave'] == Configure::read('ib.ws.pwd')
		) {
			$archivos = glob(CACHE.DS.'banner_*');
			
			foreach($archivos as $i){
				unlink($i);
			}
			
			$this->set('res','ok');
		} else {
			$this->set('res','nok');
		}
		
		$this->layout='ajax';
	}

	/**
	 * Busca la imagen de autentificacion por el ID de registro
	 * 
	 */
	function buscar_imagen(){
		$result = '';
		
		// verifica la data
		if(
			count($this->params['pass']) && 
			isset($this->params['pass'][0]) && 
			$this->params['pass'][0]
		){
			$id = intval($this->params['pass'][0]);
			
			$result = $this->Sello->find(
				'first',
				array(
					'conditions' => array(
						'Sello.id'=>$id
					)
				)
			);
			
		}
		
		$this->set(compact('result'));
		
		$this->layout = 'nada';
		
	}
}

?>