<?php
/**
 * Modelo para el manejo de la bitacora de los usuarios.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de la bitacora de los usuarios.
 * 
 */
class Bitacora extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'logs';

	
	/**
	 * Indica las tablas con las que tiene una relación foránea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'Usuario' => array(
			'className' => 'Usuario',
			'foreignKey' => 'usuario_id'
		),
		'Transaccion' => array( 
			'className' => 'Transaccion',
			'foreignKey' => 'transaccion_id'
		)
	);
	
	
	/**
	 * Para registrar en bitacora log
	 * 
	 * @param int $iduser
	 * @param int $idtrans
	 * @param string $ctaorigen
	 * @param string $ctadestino
	 * @param string $monto
	 * @param string $referencia
	 * @param string $descripcion
	 * @param int $errorOperacion
	 */
	function registraBitacora($iduser, $idtrans, $ctaorigen=NULL, $ctadestino=NULL, $monto=NULL, $referencia=NULL, $descripcion=NULL, $errorOperacion=0, $cedula=NULL, $tipoIdentB=NULL, $identB=NULL, $nombreB=NULL){
		
            App::import('Component', 'Session');
            $session = new SessionComponent();
            $reportaAV=1;
            
            if(esMovil()){
                    $canal = Configure::read('canal.movil');
            } else {
                    $canal = Configure::read('canal.ib');
            }		                

            if($session->read('countryAcronym')!='VE'){
                if($session->check('reportaViaje')){
                   $reportaViaje = $session->read('reportaViaje');
                    if(!$reportaViaje){
                        $reportaAV=0;
                    }
                }                 
            }

            if ($idtrans != '101102') {
            	$monto = formatea_monto_toWs($monto);
            }

            if(!$this->save(
                array('Bitacora'=>
                    array(
                            'usuario_id'=>$iduser,
                            'transaccion_id'=>$idtrans,
                            'fecha_hora'=>date('Y-m-d H:i:s'),
                            'cuenta_origen'=>encriptar($ctaorigen),
                            'cuenta_destino'=>encriptar($ctadestino),
                            'monto'=>$monto,
                            'referencia'=>encriptar($referencia),
                            'descripcion'=>encriptar($descripcion),
                            'ip'=> (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? encriptar($_SERVER['HTTP_X_FORWARDED_FOR']) : encriptar($_SERVER['REMOTE_ADDR']),
                            'user_agent'=>encriptar($_SERVER['HTTP_USER_AGENT']),
                            'error_operacion'=>$errorOperacion,
                            'canal'=>$canal,
                            'cedula'=>$cedula,
                            'codigo_pais'=>$session->read('countryCode'),
                            'tipo_identificacion_b'=>$tipoIdentB,
                            'identificacion_b'=>encriptar($identB),
                            'nombre_b'=>encriptar($nombreB),
                            'reporta_ausencia'=>$reportaAV
                    )
                ),false
            )){
                    $this->log($this->getDataSource()->error);	
            }

            // Sino se elimina el id se actualiza el registro
            unset($this->id);
	}
	
	
	/**
	 * Verifica si la información dada es igual a la de una transacción anterior ejecutada en el día
	 * 
	 * @param string $usuario_id
	 * @param array $data
	 * @param int $transaccion_id
	 * 
	 * @return boolean
	 */
	function transaccionIgualAnterior($usuario_id, $data, $transaccion_id){
		

    	// Verifica si es una transferencia/pago o recarga
        if($transaccion_id==100015){
            $campo_destino = 'numeroContrato';
        }else if($transaccion_id==100065){
            $codTelefono = $data['codigo_telefono'] * 1;  
            $telefono = '58' . trim($codTelefono) . trim($data['telefono']);
        	$cantidad = $this->find(
				'count',
				array(
					'conditions' => array(
						'usuario_id'=> $usuario_id,
						'transaccion_id'=> $transaccion_id,
						'cuenta_destino'=> encriptar($telefono),
						'monto'=> formatea_monto_toWs($data['monto']),
	                    'error_operacion'=> 0,
						'fecha_hora between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
					)
				)
			);
			return $cantidad;
        }else{
            $campo_destino = (in_array($transaccion_id, array(100021,100022,100023,100024))) ? 'cuentaCreditar' : 'numeroTDC';
        }	


        $ctaDestino = $campo_destino;

		
		// Realiza la busqueda de acuerdo a las condiciones indicadas
		$cantidad = $this->find(
			'count',
			array(
				'conditions' => array(
					'usuario_id'=> $usuario_id,
					'transaccion_id'=> $transaccion_id,
					'cuenta_destino'=> encriptar($data[$campo_destino]),
					'monto'=> formatea_monto_toWs($data['monto']),
                    'error_operacion'=> 0,
					'fecha_hora between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
				)
			)
		);				
		
		return ($cantidad>0);
		
	}

        
        /**
	 * Verifica si ha ejecutado una transaccion de alto valor durante el día (Juridico)
	 * 
	 * @param string $listado id de los usuarios asociados a la empresa	 
         * @param int $parametro parametro alto valor	 
	 * 
	 * @return $sumatoria
	 */
	function transaccionAltoValorJurudico($listado, $parametro){
												
		// Realiza la busqueda de acuerdo a las condiciones indicadas
		$sumatoria = $this->find(
			'first',
			array(
                                'fields' => array('sum(monto) AS monto'),
				'conditions' => array(
					'usuario_id' => $listado,					
					'monto >' => formatea_monto_toWs($parametro),
                                        'error_operacion' => 0,
                                        'transaccion_id <> 54',
                                        'transaccion_id' => array('100023','100024','100027','100028'),
					'fecha_hora between ? and ?' => array(date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
				)
			)
		);				
		                                
                return $sumatoria;                		
	}
}

?>
