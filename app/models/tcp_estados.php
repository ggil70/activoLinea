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
class TcpEstados extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'tcp_estados';
        
        /**
	 * Listar los prefijos telefonicos.
	 * 	 	 
	 * @return array
	 */
	function listar_estados(){				
		$estado =  $this->find(
			'all', array('order' => 'des_estado ASC')
		);

        $arrayEstado = array(); 
		foreach ($estado as $key) {
            $arrayEstado[$key['TcpEstados']['cod_estado']] = $key['TcpEstados']['des_estado'];

		}
		return $arrayEstado;
	}
	



    
}

?>
