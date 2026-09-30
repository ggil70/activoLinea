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
class TcpCantidadRecarga extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'tcp_cantidad_recarga';
	


	function guardarRecarga($data){
		$this->save($data);
		return true;

	}	

function modificarRecarga($idUsuario,$cantidad_recarga_dia, $cantidad_recarga_mes){
    
    $actualizaRecarga = '';
    $actualizaRecarga =  $this->updateAll(
                            array(
                                'cantidad_recarga_dia' => "'".$cantidad_recarga_dia."'",
                                'cantidad_recarga_mes' => "'".$cantidad_recarga_mes."'",
                            ),
                            array(
                                'id_cliente' => $idUsuario
                            )
                         );
    return $actualizaRecarga;
  } 


function modificarDiaRecarga($idUsuario,$fechaDia){
    
    $actualizaRecarga = '';
    $actualizaRecarga =  $this->updateAll(
                            array(
                                'cantidad_recarga_dia' => "'0'",
                                'dia_recarga' => "'".$fechaDia."'",
                            ),
                            array(
                                'id_cliente' => $idUsuario
                            )
                         );
    return $actualizaRecarga;
  } 

function modificarMesRecarga($idUsuario,$fechaMes, $fechaDia){
    
    $actualizaRecarga = '';
    $actualizaRecarga =  $this->updateAll(
                            array(
                                'cantidad_recarga_dia' => "'0'",
                                'dia_recarga' => "'".$fechaDia."'",
                                'cantidad_recarga_mes' => "'0'",
                                'mes_recarga' => "'".$fechaMes."'",
                            ),
                            array(
                                'id_cliente' => $idUsuario
                            )
                         );
    return $actualizaRecarga;
  } 
 
             
function consultarNroRecarga($idCliente){
    $recarga =  $this->find(
        'all', array(
		'fields'=>array('cantidad_recarga_dia','dia_recarga','cantidad_recarga_mes','mes_recarga'),
            'conditions'=>array(
                "TcpCantidadRecarga.id_cliente"=>$idCliente)
    )
    );



    if($recarga){
    	$arrayRecarga = array(); 
    	foreach ($recarga as $key) {
            	$arrayRecarga['cantidad_recarga_dia'] = $key['TcpCantidadRecarga']['cantidad_recarga_dia'];
              $arrayRecarga['dia_recarga'] = $key['TcpCantidadRecarga']['dia_recarga'];
              $arrayRecarga['cantidad_recarga_mes'] = $key['TcpCantidadRecarga']['cantidad_recarga_mes'];
              $arrayRecarga['mes_recarga'] = $key['TcpCantidadRecarga']['mes_recarga'];
    	}
	      return $arrayRecarga;
    }else{
	      return false;
    }	
}

	
} //fin del modelo

?>