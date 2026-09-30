<?php
/**
 * Modelo para el manejo de las solicitudes de tarjetas de coordenadas.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de las solicitudes de tarjetas de coordenadas.
 * 
 */
class LbSeguridad extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'lb_seguridad';

	

             

    function consultarCedula($tipoCedula, $cedula){
        $solicitud =  $this->find(
            'all', array(
                'conditions'=>array(
                    "LbSeguridad.tipo_documento"=>$tipoCedula, 
                    "LbSeguridad.cedula"=>$cedula,
                    "LbSeguridad.estatus"=>1,
                  )
        )
        );

        if($solicitud){
			return true;
		}else{
			return false;
		}	

    }
}

?>