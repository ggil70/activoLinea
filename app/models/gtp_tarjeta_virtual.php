<?php
/**
 * Modelo para el manejo de Registro Activación Tarjeta Virtual.
 * 
 * @author GAGC
 */


/**
 * Manejo de la información de Usuario.
 * 
 */
class GtpTarjetaVirtual extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'gtp_tarjetasvirtuales';
        
        /**
	 * Listar Tarjetas Activas.
	 * 	 	 
	 * @return array
	 */
	
	function validar_usuario($pcero){

/*        $options['joins'] = array(
    		array('table' => 'gtp_tipo_usuario',
        		'alias' => 'GtpTipoUsuario',
        		'type' => 'inner',
        		'conditions' => array(
            		'GtpUsuario.tipo_usuario_id = GtpTipoUsuario.id'
        		)
        	)	
    	);
    	$options['conditions'] = array(
    		'cod_usuario'=>$pcero)
    	;

    	$options['fields'] = array(
    		'GtpUsuario.id','GtpUsuario.cod_usuario','GtpUsuario.nob_usuario','GtpUsuario.tipo_usuario_id','GtpTipoUsuario.nob_tipo_usuario');

		$usuario =  $this->find(
			'first', $options 
		);
		return $usuario;
*/		
	}

    function obtenerInformacion($cliente_id){
    	$options['conditions'] = array(
    		'cliente_id'=>$cliente_id,
    		'activo'=>1
    	);

		$tarjeta =  $this->find(
			'first', $options 
		);
		return $tarjeta;
    }


	function guardar_activacion_virtual($data){
		$this->save($data);
			return true;
	}	

    function modificarActivo($id){
        $resp = $this->updateAll(
            array(
               'activo' => 0,
                ),
            array(
                'id_cliente' => $id,
            )
        );

        return $resp;
    } 

    function listadoFecha($usuario, $fechaDesde, $fechaHasta){
        $options['joins'] = array(
    		array('table' => 'gtp_cliente_virtuales',
        		'alias' => 'GtpClienteVirtual',
        		'type' => 'inner',
        		'conditions' => array(
            		'GtpTarjetaVirtual.cliente_id = GtpClienteVirtual.id'
        		)
        	),

    		array('table' => 'gtp_usuario',
        		'alias' => 'GtpUsuario',
        		'type' => 'inner',
        		'conditions' => array(
            		'GtpTarjetaVirtual.usuario_id = GtpUsuario.id'
        		)
        	)
    	);   
    	$options['fields'] = array(
    		   'GtpClienteVirtual.nac_cliente', 'GtpClienteVirtual.nob_cliente','GtpClienteVirtual.ced_cliente','GtpTarjetaVirtual.numero_tarjeta','GtpTarjetaVirtual.fecha_registro','GtpTarjetaVirtual.hora_registro', 'GtpUsuario.nob_usuario','GtpUsuario.cod_usuario');

        if($usuario == 0){
            $options['conditions'] = array("fecha_registro BETWEEN '" . $fechaDesde . "' and '" . $fechaHasta ."'");
        }else{
        	$options['conditions'] = array("fecha_registro BETWEEN '" . $fechaDesde . "' and '" . $fechaHasta ."' and usuario_id = " . $usuario);
        } 



		$tarjeta =  $this->find(
			'all', $options 
		);
		return $tarjeta;
    } 

		

    function obtenerTarjeta($tarjeta){
    	$nroTarjeta = encriptar($tarjeta);
    	$options['conditions'] = array(
    		'numero_tarjeta'=>$nroTarjeta
    	);

		$tarjeta =  $this->find(
			'first', $options 
		);
		return $tarjeta;
    }




    
}

?>
