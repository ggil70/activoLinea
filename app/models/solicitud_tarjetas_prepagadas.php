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
class SolicitudTarjetasPrepagadas extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'solicitud_tarjetas_prepagadas';

	
	function guardar_solicitud($data,$nroDocumento, $agencia, $tipo_documento, $nro_documento){

		$this->save($data);
		$this->updateAll(
            array(
               'nob_agencia' => "'".$agencia."'",

                ),
            array(
                'cedula' => $nroDocumento
            )
        );
		return true;

	}	
             
    function modificarEstatus($nroDocumento){
        $this->updateAll(
            array(
               'estatus' => 5,
                ),
            array(
                'cedula' => $nroDocumento,
                'estatus' => '2'
            )
        );
        return true;
    }   



    function modificarEstatusCancelada($nroDocumento){
        $this->updateAll(
            array(
               'estatus' => 5,
                ),
            array(
                'cedula' => $nroDocumento,
            )
        );
        return true;
    }




    function consultarSolicitud($tipoCedula, $cedula, $tipoProducto){
        $solicitud =  $this->find(
            'all', array(
				'fields'=>array('estatus','fecha_creacion','res_nro_cuenta'),
                'conditions'=>array(
                    "SolicitudTarjetasPrepagadas.tipo_cedula"=>$tipoCedula, 
                    "SolicitudTarjetasPrepagadas.cedula"=>$cedula,
                    "SolicitudTarjetasPrepagadas.tipo_producto"=>$tipoProducto,
                    "SolicitudTarjetasPrepagadas.estatus <="=> 3,
                  )
        )
        );

        if($solicitud){
        	$arraySolicitud = array(); 
  			foreach ($solicitud as $key) {
              	$arraySolicitud['estatus'] = $key['SolicitudTarjetasPrepagadas']['estatus'];
              	$arraySolicitud['fecha_creacion'] = $key['SolicitudTarjetasPrepagadas']['fecha_creacion'];
              	$arraySolicitud['res_nro_cuenta'] = $key['SolicitudTarjetasPrepagadas']['res_nro_cuenta'];
  			}
			return $arraySolicitud;
		}else{
			return false;
		}	

    }

    function meses(){
      	$arrayMeses = array(); 
      	$arrayMeses[1] = 'ENERO';
      	$arrayMeses[2] = 'FEBRERO';
      	$arrayMeses[3] = 'MARZO';
      	$arrayMeses[4] = 'ABRIL';
      	$arrayMeses[5] = 'MAYO';
      	$arrayMeses[6] = 'JUNIO';
      	$arrayMeses[7] = 'JULIO';
        $arrayMeses[8] = 'AGOSTO';      	
      	$arrayMeses[9] = 'SEPTIEMBRE';
      	$arrayMeses[10] = 'OCTUBRE';
      	$arrayMeses[11] = 'NOVIEMBRE';
      	$arrayMeses[12] = 'DICIEMBRE';
		return $arrayMeses;
    }


	
}

?>