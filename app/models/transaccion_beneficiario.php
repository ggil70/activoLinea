<?php
/**
 * Modelo para el manejo de los beneficiarios de las transacciones de usuarios jurídicos.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los alias de los productos.
 * 
 */
class TransaccionBeneficiario extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'transacciones_beneficiarios';

	
	/**
	 * Indica las tablas con las que tiene una relación foránea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'TransaccionPendiente'=>array(
			'className'=>'TransaccionPendiente',
			'foreignKey'=>'transaccion_id'
		)
	);	
	
	/**
	 * Obtiene los beneficiarios de una transacción determinada.
	 * 
	 * @param int $transaccion_id
	 * @return array
	 */
	function obtenerListaDetalle($transaccion_id){
		$data = array();
		
		if($result = $this->find(
			'all',
			array(
				'conditions'=>array(
					'TransaccionBeneficiario.transaccion_id'=>$transaccion_id,
				),
			)
		)){
			foreach ($result as $b){
				$b['TransaccionBeneficiario']['monto'] = formatea_monto_fromWs($b['TransaccionBeneficiario']['monto']);
				$data[]['AfiliacionCash']= $b['TransaccionBeneficiario'];
			}
		}
		
		return $data;
	}



	function obtenerReferencia($tipoDocumento, $rif){
        $nroReferencia = 1;
        $swEncontrar = 0;
      

        while($swEncontrar <> 1){
        	$nroEncriptado = encriptar($nroReferencia);

	        $referencia =  $this->find(
	            'all', array(
					'fields'=>array('id'),
	                'conditions'=>array(
	                    "TransaccionBeneficiario.tipo_rif"=>$tipoDocumento, 
	                    "TransaccionBeneficiario.rif"=>$rif,
	                    "TransaccionBeneficiario.referencia >=" => $nroEncriptado,
	                  )
	        )
	        );
	        if($referencia){
                $nroReferencia = $nroReferencia + 1000;                
            }else{
                $swEncontrar = 1;
            } 
        }
        return $nroReferencia;
    }
}

?>