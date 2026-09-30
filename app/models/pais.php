<?php
/**
 * Modelo para el manejo de la información de los paises.
 * 
 * @author EMSYS
 */


/**
 * Manejo de la información de los canales.
 * 
 */
class Pais extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'paises';
        
        function listar_paises(){				
		            
		return $this->find(
			'list',
                        array(
                            'fields' => array (
                                    'Pais.siglas',
                                    'Pais.nombre'
                            ), 
                            'conditions'=>array('codigo_pais !='=>"1"),
                            'order' => 'Pais.nombre ASC'
                        )                        
		);
	}
	
        function listar_paises_codigo(){				
		            
		return $this->find(
			'list',
                        array(
                            'fields' => array (
                                    'Pais.siglas',
                                    'Pais.codigo_pais'
                                ),
                            'order' => 'Pais.nombre ASC'
                        )                      
		);
	}
        
        function listar_paises_nombre(){				
		            
		return $this->find(
			'list',
                        array(
                            'fields' => array (
                                    'Pais.codigo_pais',
                                    'Pais.nombre'
                            ),
                            'order' => 'Pais.nombre ASC'
                        )
		);
	}
        
        function listar_paises_siglas(){				
		            
		return $this->find(
			'list',
                        array(
                            'fields' => array (
                                    'Pais.codigo_pais',
                                    'Pais.siglas'
                            ),
                            'order' => 'Pais.nombre ASC'                                                   
                        )
		);
	}
}

?>