<?php
/**
 * Modelo para el manejo de la información de los limites vip.
 * 
 * @author EMSYS
 */


/**
 * Manejo de la información de los limites vip.
 * 
 */
class LimitesVip extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'limites_vip';                        
        
        /*
	 * Método para la consultar un limite por el id
	 * 	 
         * @param array|string $usuario_id
	 * @return array
	 */        
        function buscarLimiteById($id){
                         
            $condiciones = array(                
                "id" => $id
            );
                       
            $parametros = array(
                'conditions' => $condiciones
            );
            
            $res = $this->find('all', $parametros);
            
            return $res[0]['LimitesVip'];
        }                
}

?>