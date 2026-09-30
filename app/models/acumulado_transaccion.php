<?php
/**
 * Modelo para el manejo de los acumulados diarios de las transacciones.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los acumulados diarios de las transacciones.
 * 
 */
class AcumuladoTransaccion extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'acumulado_transaccion';
	
	/**
	 * Indica las tablas con las que tiene una relacion foranea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'Usuario' => array( 
			'className' => 'Usuario',
			'foreignKey' => 'usuario_id'
		)
	);
	
	/**
	 * Busca la informacion de los acumulados de una EMPRESA
	 * 
	 * @param string $tipo_rif
	 * @param string $rif
	 * @param string $fecha
	 * @return array
	 */
	function buscaRIF($tipo_rif, $rif, $fecha=null) {
		if(empty($fecha)) $fecha = date('Y-m-d');
		
		// Busca la sumatoria de los acumulados de los usuarios de la empresa

		$acumulados = $this->find(
			'first',
			array(
				'fields'=>array('sum(acumulado_internas_terceros) as acumulado_internas_terceros,'
                                    . 'sum(acumulado_externas) as acumulado_externas,'
                                    . 'sum(acumulado_internas_terceros_nomina) as acumulado_internas_terceros_n,'
                                    . 'sum(acumulado_internas_terceros_nomina_usd) as acumulado_it_n_usd,'
                                    . 'sum(acumulado_internas_terceros_nomina_eur) as acumulado_it_n_eur,'
                                    . 'sum(acumulado_externas_nomina) as acumulado_externas_n,'
                                    . 'sum(acumulado_internas_terceros_proveedores) as acumulado_internas_terceros_p,'
                                    . 'sum(acumulado_internas_terceros_proveedores_usd) as acumulado_it_p_usd,'
                                    . 'sum(acumulado_internas_terceros_proveedores_eur) as acumulado_it_p_eur,'
                                    . 'sum(acumulado_externas_proveedores) as acumulado_externas_p,'
                                    . 'sum(acumulado_internas_terceros_usd) as acumulado_internas_terceros_usd,'
                                    . 'sum(acumulado_internas_terceros_eur) as acumulado_internas_terceros_eur'), // pner moneda extranjera
				'conditions' => array (
					'Usuario.tipo_rif' => $tipo_rif,
					'Usuario.rif' => $rif,
					'fecha' => $fecha
				)
			),
			false
		);
		
		if(!isset($acumulados[0]['acumulado_internas_terceros'])){
			$acumulados[0]['acumulado_internas_terceros'] = 0;
		}
                
                if(!isset($acumulados[0]['acumulado_internas_terceros_usd'])){
			$acumulados[0]['acumulado_internas_terceros_usd'] = 0;
		}
                
                if(!isset($acumulados[0]['acumulado_internas_terceros_eur'])){
			$acumulados[0]['acumulado_internas_terceros_eur'] = 0;
		}
		
		if(!isset($acumulados[0]['acumulado_externas'])){
			$acumulados[0]['acumulado_externas'] = 0;
		}
                
        if(!isset($acumulados[0]['acumulado_internas_terceros_n'])){
			$acumulados[0]['acumulado_internas_terceros_n'] = 0;

		}
		if(!isset($acumulados[0]['acumulado_it_n_usd'])){
			$acumulados[0]['acumulado_it_n_usd'] ;
		}

		if(!isset($acumulados[0]['acumulado_it_n_eur'])){
			$acumulados[0]['acumulado_it_n_eur'] = 0;
		}
                
                if(!isset($acumulados[0]['acumulado_externas_n'])){
			$acumulados[0]['acumulado_externas_n'] = 0;
		}
                
                if(!isset($acumulados[0]['acumulado_internas_terceros_p'])){
			$acumulados[0]['acumulado_internas_terceros_p'] = 0;
		}

		if(!isset($acumulados[0]['acumulado_it_p_usd'])){
			$acumulados[0]['acumulado_it_p_usd'] = 0;
		}

		if(!isset($acumulados[0]['acumulado_it_p_eur'])){
			$acumulados[0]['acumulado_it_p_eur'] = 0;
		}
                
                if(!isset($acumulados[0]['acumulado_externas_p'])){
			$acumulados[0]['acumulado_externas_p'] = 0;
		}
		
		$acumulados = array(
			'AcumuladoTransaccion'=>array(
			'acumulado_internas_terceros'=>$acumulados[0]['acumulado_internas_terceros'],
            'acumulado_internas_terceros_usd'=>$acumulados[0]['acumulado_internas_terceros_usd'],
            'acumulado_internas_terceros_eur'=>$acumulados[0]['acumulado_internas_terceros_eur'],
			'acumulado_externas'=>$acumulados[0]['acumulado_externas'],
            'acumulado_internas_terceros_nomina'=>$acumulados[0]['acumulado_internas_terceros_n'],
            'acumulado_it_nomina_usd'=> $acumulados[0]['acumulado_it_n_usd'],
            'acumulado_it_nomina_eur'=> $acumulados[0]['acumulado_it_n_eur'],
			'acumulado_externas_nomina'=>$acumulados[0]['acumulado_externas_n'],
            'acumulado_internas_terceros_proveedores'=>$acumulados[0]['acumulado_internas_terceros_p'],
            'acumulado_it_proveedores_usd'=>$acumulados[0]['acumulado_it_p_usd'],
            'acumulado_it_proveedores_eur'=>$acumulados[0]['acumulado_it_p_eur'],
			'acumulado_externas_proveedores'=>$acumulados[0]['acumulado_externas_p']
			)
		);
		
		return $acumulados;
	}
	
	/**
	 * Busca la informacion de los acumulados de un usuario en especifico.
	 * 
	 * @param int $id_usuario
	 * @param date $fecha
	 * @return array
	 */
	function buscaInfo($id_usuario,$fecha=null) {
		if(empty($fecha)) $fecha = date('Y-m-d');
		
		// Busca la info de los acumulados
		$acumulados = $this->find(
			'first',
			array(
				'conditions' => array (
					'usuario_id' => $id_usuario,
					'fecha' => $fecha
				)
			),
			false
		);
		
		if(!$acumulados){
			
			// Elimina los registros con un mes de antiguedad respecto a la fecha del dia
                        $fecha_query = strtotime('-30 day',strtotime(date('Y-m-d')));
                        $fecha_query = date ('Y-m-d' , $fecha_query);
			$this->deleteAll( array( 'usuario_id' => $id_usuario, 'fecha < ' => $fecha_query ) );
			
			$acumulados = array(
				'AcumuladoTransaccion' => array(
					'usuario_id' => $id_usuario,
					'fecha' => $fecha
				)
			);
			
			// crea un nuevo registro con acumulados en 0 (ceros)
			$this->save(
				$acumulados,
				false
			);
			
			$this->read();
			
			$acumulados = $this->data;
			
		}
		
		return $acumulados;
	}
	
	
	/**
	 * Actualiza el acumulado del dia segun la transaccion.
	 * 
	 * @param int $id_usuario
	 * @param string $tipo_transaccion it:internas terceros, e:externas
	 * @param string $monto
	 * @param date $fecha
	 */
	function guardaAcumulado($id_usuario, $tipo_transaccion, $monto,$fecha=null){

		
		
		// busca los acumulados en la BD
		$acumulados = $this->buscaInfo($id_usuario,$fecha);
		
		$data['AcumuladoTransaccion'] = array(
                    'id' => $acumulados['AcumuladoTransaccion']['id']
		);
		
		$fmonto = floatval(formatea_monto_toWs($monto));
		
		// Asigna el monto de acuerdo al tipo de transaccion
		switch ($tipo_transaccion){
			case 'it':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_internas_terceros'];
				$data['AcumuladoTransaccion']['acumulado_internas_terceros'] = number_format($monto,2,'.','');
				break;
			case 'e':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_externas'];
				$data['AcumuladoTransaccion']['acumulado_externas'] = number_format($monto,2,'.','');
				break;
                case 'itn':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'];
				$data['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'] = number_format($monto,2,'.','');
				break;
			    case 'en':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_externas_nomina'];
				$data['AcumuladoTransaccion']['acumulado_externas_nomina'] = number_format($monto,2,'.','');
				break;
                 case 'itp':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'];
				$data['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'] = number_format($monto,2,'.','');
				break;
			    case 'ep':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_externas_proveedores'];
				$data['AcumuladoTransaccion']['acumulado_externas_proveedores'] = number_format($monto,2,'.','');
				break;  
			    case 'itmeusd':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_internas_terceros_usd'];
				$data['AcumuladoTransaccion']['acumulado_internas_terceros_usd'] = number_format($monto,2,'.','');
				break;
                        case 'itmeeur':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_internas_terceros_eur'];
				$data['AcumuladoTransaccion']['acumulado_internas_terceros_eur'] = number_format($monto,2,'.','');
				break;
				case 'itnusd':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_internas_terceros_nomina_usd'];
				$data['AcumuladoTransaccion']['acumulado_internas_terceros_nomina_usd'] = number_format($monto,2,'.','');
				break;
			    case 'itneur':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_internas_terceros_nomina_eur'];
				$data['AcumuladoTransaccion']['acumulado_internas_terceros_nomina_eur'] = number_format($monto,2,'.','');
				break;
				case 'itpusd':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores_usd'];
				$data['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores_usd'] = number_format($monto,2,'.','');
				break;
                case 'itpeur':
				$monto = $fmonto + $acumulados['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores_eur'];
				$data['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores_eur'] = number_format($monto,2,'.','');
				break;    

		}

		
		
		// actualiza el registro
		$this->save($data,false);
		
		// Sino se elimina el id se actualiza el registro
		unset($this->id);
		
	}
	
}
?>