<?php
/**
 * Modelo para el manejo de la información de las ciudades.
 * 
 * @author MGGY
 */


/**
 * Manejo de la información de los canales.
 * 
 */
class TcpCiudades extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'tcp_ciudades';
        
        /**
	 * Listar los prefijos telefonicos.
	 * 	 	 
	 * @return array
	 */
	function listar_ciudades(){				
		$ciudades =  $this->find(
			'all', array('order' => 'cod_estado ASC')
		);


        $arrayCiudades = array(); 
        $arraySub = array();
        $sw = 0;


        $codigo = "";

		foreach ($ciudades as $key) {
			
            if(trim($codigo) != trim($key['TcpCiudades']['cod_estado'])){
            	if($sw == 0){
                    $sw = 1; 
                    $codigo = trim($key['TcpCiudades']['cod_estado']);
            	}else{
            		
                    $arrayCiudades[$codigo] = $arraySub;
                    $arraySub = array(); 
                    $codigo = trim($key['TcpCiudades']['cod_estado']); 
            	}
            } 

            $arraySub[$key['TcpCiudades']['cod_ciudad']] = $key['TcpCiudades']['des_ciudad'];
            if($codigo == 25){
            	 $arrayCiudades[$codigo] = $arraySub;
            }
		}
		return $arrayCiudades;
	}


    function listar_ciudades_edo($estado){               
        $ciudades =  $this->find(
            'all', array(
                'conditions'=>array(
                    "TcpCiudades.cod_estado"=>$estado),
                'order' => 'cod_estado ASC')
        );

        $arrayCiudades = array(); 
        foreach ($ciudades as $key) {
            $arrayCiudades[$key['TcpCiudades']['cod_ciudad']] = $key['TcpCiudades']['des_ciudad'];
        }
        return $arrayCiudades;
    }

    
}

?>
