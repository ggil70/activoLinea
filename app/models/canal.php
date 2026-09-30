<?php
/**
 * Modelo para el manejo de la información de los canales.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de la información de los canales.
 * 
 */
class Canal extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'canal';
        
        /**
	 * Método para buscar los canales de Pago Activo	 
	 * @return array
	 */
	function canal_pago_activo($listCanales){
		
            $res = $this->find(
                'list',
                array(
                    'fields' => array (
                            'Canal.codigo',
                            'Canal.nombre'
                        ),
                    'conditions'=>array(
                           "Canal.id"=>$listCanales
                        )                            
                )
            );
            
            return $res;
	}
	
}

?>