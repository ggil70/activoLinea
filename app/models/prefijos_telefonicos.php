<?php
/**
 * Modelo para el manejo de la información de los prefijos telefonicos.
 * 
 * @author MGGY
 */


/**
 * Manejo de la información de los canales.
 * 
 */
class PrefijosTelefonicos extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'prefijos_telefonicos';
        
        /**
	 * Listar los prefijos telefonicos.
	 * 	 	 
	 * @return array
	 */
	function listar_prefijos(){				
				
		return $this->find(
			'all',
                        array('order' => 'codigo_telefono ASC')
		);
	}
	
        /**
	 * Listar los prefijos telefonicos movil.
	 * 	 	 
	 * @return array
	 */
	function listar_prefijos_movil(){				
		            
		return $this->find(
			'all',
                        array(
                            'fields' => array (
                                    'PrefijosTelefonicos.codigo_telefono'                                            
                                ),
                            'conditions'=>array(
                                   "PrefijosTelefonicos.es_movil"=>1
                                )                            
                        ),
                        array('order' => 'PrefijosTelefonicos.codigo_telefono ASC')
		);
	}
        
        /**
	 * Listar los prefijos telefonicos pago movil.
	 * 	 	 
	 * @return array
	 */
	function listar_prefijos_pago_movil(){				
				
            return $this->find(
                'all',
                array(
                    'fields' => array (
                        'PrefijosTelefonicos.codigo_telefono'                                            
                    )                   
                ),
                array(
                    'order' => 'codigo_telefono ASC',
                    'group' => 'es_movil DESC'
                )
            );
        }
    



function listar_prefijos_movil_actual(){
		            
		$prefijoT = $this->find(
			'all',
                        array(
                            'fields' => array (
                                    'PrefijosTelefonicos.codigo_telefono'                                            
                                ),
                            'conditions'=>array(
                                   "PrefijosTelefonicos.es_movil"=>1
                                )                            
                        ),
                        array('order' => 'PrefijosTelefonicos.codigo_telefono ASC')
		);

        $arrayPrefijoT = array(); 
		foreach ($prefijoT as $key) {
            $arrayPrefijoT[$key['PrefijosTelefonicos']['codigo_telefono']] = $key['PrefijosTelefonicos']['codigo_telefono'];

		}

		return $arrayPrefijoT;		
}		

}


?>
