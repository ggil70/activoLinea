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
class TcpMunicipios extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'tcp_municipios';
        
        /**
	 * Listar los prefijos telefonicos.
	 * 	 	 
	 * @return array
	 */
	function listar_municipios(){				
		$municipios =  $this->find(
			'all', array('order' => 'cod_estado ASC')
		);


        $arrayMunicipio = array(); 
        $arraySub = array();
        $sw = 0;


        $codigo = "";

		foreach ($municipios as $key) {
			
            if(trim($codigo) != trim($key['TcpMunicipios']['cod_estado'])){
            	if($sw == 0){
                    $sw = 1; 
                    $codigo = trim($key['TcpMunicipios']['cod_estado']);
            	}else{
            		
                    $arrayMunicipio[$codigo] = $arraySub;
                    $arraySub = array(); 
                    $codigo = trim($key['TcpMunicipios']['cod_estado']); 
            	}
            } 

            if($codigo == 25){
            	 $arraySub[$key['TcpMunicipios']['cod_municipio']] = $key['TcpMunicipios']['des_municipio'];
            	 $arrayMunicipio[$codigo] = $arraySub;
            }

            $arraySub[$key['TcpMunicipios']['cod_municipio']] = $key['TcpMunicipios']['des_municipio'];
		}
		return $arrayMunicipio;
	}



    function listar_municipios_edo($estado){               
        $municipios =  $this->find(
            'all', array(
                'conditions'=>array(
                    "TcpMunicipios.cod_estado"=>$estado),
                'order' => 'cod_estado ASC')
        );

        $arrayMunicipio = array(); 
        foreach ($municipios as $key) {
            $arrayMunicipio[$key['TcpMunicipios']['cod_municipio']] = $key['TcpMunicipios']['des_municipio'];
        }
        return $arrayMunicipio;


    }


    
}

?>
