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
class TcpZonaspostales extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'tcp_zonaspostales';
        
        /**
	 * Listar los prefijos telefonicos.
	 * 	 	 
	 * @return array
	 */
	function listar_zonaPostal(){	


		$zonaPostal =  $this->find(
			'all', array('order' => 'cod_estado ASC')
		);

		


        $arrayZonaPostal = array(); 
        $arraySub = array();
        $sw = 0;


        $codigo = "";
		foreach ($zonaPostal as $key) {
            if(trim($codigo) != trim($key['TcpZonaspostales']['cod_estado'])){
            	if($sw == 0){
                    $sw = 1; 
                    $codigo = trim($key['TcpZonaspostales']['cod_estado']);
                    

            	}else{
                    $arrayZonaPostal[$codigo] = $arraySub;
                    $arraySub = array(); 
                    $codigo = trim($key['TcpZonaspostales']['cod_estado']); 
            	}
            } 

            $arraySub[$key['TcpZonaspostales']['cod_zona_postal']] = $key['TcpZonaspostales']['des_zona_postal'];
            if($codigo == 25){
            	 $arrayZonaPostal[$codigo] = $arraySub;
            }
		}


		return $arrayZonaPostal;
	}


    function listar_zonaPostal_edo($estado){               
        $zonaPostal =  $this->find(
            'all', array(
                'conditions'=>array(
                    "TcpZonaspostales.cod_estado"=>$estado),
                'order' => 'cod_estado ASC')
        );

        $arrayZonaPostal = array(); 
        foreach ($zonaPostal as $key) {
            $arrayZonaPostal[$key['TcpZonaspostales']['cod_zona_postal']] = $key['TcpZonaspostales']['des_zona_postal'];
        }
        return $arrayZonaPostal;
    }



    
}

?>
