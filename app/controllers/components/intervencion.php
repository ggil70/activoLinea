<?php
/**
 * Componente para el manejo de las operaciones de intervencion.
 * 
 * @author Emsys
 */


/**
 * Manejo de las operaciones del proceso de intervencion.
 */
class IntervencionComponent extends Object{
	
	/**
	 * Componentes del cake utilizados
	 * 
	 * @var array Arreglo con los componentes
	 */
	var $components = array('Session', 'Activo');
	
	
	/**
	 * Enlace de conexión a la base de datos
	 * 
	 * @access private
	 * @var resource ResourceID
	 */
	private $link;			
	
	
	/**
	 * Método para realizar la conexión a la base de datos
	 * 
	 * @access private
	 * @return boolean
	 */
	private function conectar(){
		
            $this->link = @mssql_connect(
                Configure::read('exc.db.host'),
                desencriptar(Configure::read('exc.db.user')),
                desencriptar(Configure::read('exc.db.pwd'))
            );

            if($this->link){
                @mssql_select_db(
                    Configure::read('exc.db.name'),
                    $this->link
                );

                return true;
            }else{
                $this->log('Error al conectar con servidor de base de datos '.Configure::read('exc.db.host'), LOG_ERROR);
                return false;
            }
				
	}
	
	
	/**
	 * Método para realizar el cierre de la conexión a la base de datos.
	 * 
	 * @access private
	 */
	private function desconectar(){
            if($this->link){
                @mssql_close($this->link);
            }
	}

        
	/**
	 * Método para validar si el producto aplica para intervencion.
	 * 
	 * @param string $producto Tipo de Producto.	 
	 * @return array
	 */
	function validarProducto($producto,$tipo,$origen,$destino){				
		                        
            if(!$this->conectar()) return false;

            $respuesta = false;
            
            $result = @mssql_query(
                "SELECT ope.fee_percentage,mark.rate,mark.available,prod.name_product,pro.transaction_source_cod,pro.transaction_target_cod,mark.exchange_cod,mark.exchange_start,mark.exchange_end,mark.exchange_type_cod,mark.exchange_market_id FROM rel_ch_exchange_pro AS pro INNER JOIN tb_products AS prod ON pro.product_source_cod = prod.product_cod INNER JOIN rel_exchange_type_ope AS ope ON pro.exchange_type_id = ope.exchange_type_id AND pro.exchange_operation_id = ope.exchange_operation_id INNER JOIN exchange_market AS mark ON pro.exchange_operation_id = mark.exchange_operation_id AND pro.exchange_type_cod = mark.exchange_type_cod INNER JOIN tb_exchange AS exch ON mark.exchange_id = exch.exchange_id WHERE product_source_cod = '".$producto."' and pro.parameter_id = '2' and pro.status_cod = 'A' and mark.currency_source_cod = '".$origen."' and mark.currency_target_cod = '".$destino."' and exch.status_cod = 'A' and mark.status_cod = 'A' and pro.exchange_type_cod in ('IVE','IVT')", 
                $this->link
            );

            if($result===FALSE) {
                $this->log('Error al tratar de validar el producto '.$producto, LOG_ERROR);
            }else{
                // Verifica si encontro un registro
                if (@mssql_num_rows($result)){ 

                    $dataTemp = @mssql_fetch_assoc($result);

                    $this->desconectar();

                    return array_map('trim', $dataTemp);
                }
            }

            $this->desconectar();

            return $respuesta;
	}
	
        /**
	 * Método para consultar los parametros segun el tipo.
	 * 
	 * @param string $tipo Tipo de Parametros.	 
	 * @return array
	 */
	function consultaParametros($tipo){				
		                        
            if(!$this->conectar()) return false;

            $respuesta = false;
            
            $result = @mssql_query(
                "SELECT parameter_id, parameter_cod, parameter_description FROM tb_parameters AS p WHERE parameter_id = '".$tipo."'", 
                $this->link
            );

            if($result===FALSE) {
                $this->log('Error al tratar de consultar los parametros '.$tipo, LOG_ERROR);
            }else{
                // Verifica si encontro un registro
                if (@mssql_num_rows($result)){ 
                    
                    $datos = array();
                    
                    while($valores = @mssql_fetch_assoc($result)){       
                        $datos[$valores['parameter_id'].'-'.$valores['parameter_cod']] = $valores['parameter_description'];                        
                    }                    

                    $this->desconectar();

                    return $datos;
                }
            }

            $this->desconectar();

            return $respuesta;
	}
        
        /**
	 * Método para validar si el producto aplica para intervencion.
	 * 
	 * @param string $producto Tipo de Producto.	 
	 * @return array
	 */
	function consultaIntervenciones(){				
		                        
            if(!$this->conectar()) return false;
            
            $datos = array();
            
            $result = @mssql_query(
                "SELECT mark.exchange_type_cod,currency_cod,available,rate,fee_percentage FROM exchange_market mark INNER JOIN tb_exchange exc ON mark.exchange_cod = exc.exchange_cod LEFT JOIN rel_exchange_type_ope typ ON mark.exchange_type_id = typ.exchange_type_id AND mark.exchange_operation_id = typ.exchange_operation_id WHERE exc.status_cod = 'A' AND mark.status_cod = 'I'", 
                $this->link
            );

            if($result===FALSE) {
                $this->log('Error al tratar de consultar las intervenciones', LOG_ERROR);
            }else{
                // Verifica si encontro un registro
                if (@mssql_num_rows($result)){ 
                    while($valores = @mssql_fetch_assoc($result)){       
                        array_push($datos , $valores);
                    } 
                }                                
            }

            $this->desconectar();

            return $datos;
	}
        
	/**
	 * Método para insertar una operacion de intervencion.
	 * 	 	 
	 * @return boolean
	 */
	function save($producto,$tipo,$cuentaOrigen,$cuentaDestino,$montoOrigen,$montoDestino,$tasaEfectiva,$comision,$estatusId,$estatus,$montoDivisas,$montoTotal,$origen,$motivo,$referencia,$cuenta,$targetCod,$fechaValor,$fechaProceso,$exchangeCod,$userId,$exchangeMarketId){
		                        
            if(!$this->conectar()) return false;

            $respuesta = true;				
            
            if($tipo==1){
                $result = @mssql_query(
                    "SELECT ope.fee_percentage,mark.rate,mark.available,prod.name_product,pro.transaction_source_cod,pro.transaction_target_cod,mark.exchange_cod,mark.exchange_start,mark.exchange_end,mark.exchange_type_cod,mark.exchange_market_id, pro.channel_id,exch.exchange_id,mark.modality_id,mark.currency_source_id,mark.currency_target_id,mark.exchange_operation_id,prod.bank_cod,pro.product_source_id,pro.product_target_id,mark.rate,mark.modality_cod,mark.currency_target_cod,pro.product_source_cod,pro.product_target_cod,mark.exchange_type_id FROM rel_ch_exchange_pro AS pro INNER JOIN tb_products AS prod ON pro.product_source_cod = prod.product_cod INNER JOIN rel_exchange_type_ope AS ope ON pro.exchange_type_id = ope.exchange_type_id AND pro.exchange_operation_id = ope.exchange_operation_id INNER JOIN exchange_market AS mark ON pro.exchange_operation_id = mark.exchange_operation_id AND pro.exchange_type_cod = mark.exchange_type_cod INNER JOIN tb_exchange AS exch ON mark.exchange_id = exch.exchange_id  WHERE exchange_market_id = '".$exchangeMarketId."' and product_source_cod = '".$producto."' and pro.exchange_operation_id = '".$tipo."' and pro.parameter_id = '2' and pro.status_cod = 'A'", 
                    $this->link
                );
            }else{
                $result = @mssql_query(
                    "SELECT ope.fee_percentage,mark.rate,mark.available,prod.name_product,pro.transaction_source_cod,pro.transaction_target_cod,mark.exchange_cod,mark.exchange_start,mark.exchange_end,mark.exchange_type_cod,mark.exchange_market_id, pro.channel_id,exch.exchange_id,mark.modality_id,mark.currency_source_id,mark.currency_target_id,mark.exchange_operation_id,prod.bank_cod,pro.product_source_id,pro.product_target_id,mark.rate,mark.modality_cod,mark.currency_target_cod,pro.product_source_cod,pro.product_target_cod,mark.exchange_type_id FROM rel_ch_exchange_pro AS pro INNER JOIN tb_products AS prod ON pro.product_source_cod = prod.product_cod INNER JOIN rel_exchange_type_ope AS ope ON pro.exchange_type_id = ope.exchange_type_id AND pro.exchange_operation_id = ope.exchange_operation_id INNER JOIN exchange_market AS mark ON pro.exchange_operation_id = mark.exchange_operation_id AND pro.exchange_type_cod = mark.exchange_type_cod INNER JOIN tb_exchange AS exch ON mark.exchange_id = exch.exchange_id WHERE exchange_market_id = '".$exchangeMarketId."' and product_source_cod = '".$producto."' and pro.exchange_operation_id = '".$tipo."' and pro.parameter_id = '2' and pro.status_cod = 'A'", 
                    $this->link
                );
            }
                        
            if($result===FALSE) {
                
                $this->log('Error al tratar de validar el producto '.$producto, LOG_ERROR);
                $respuesta = false;
                $this->desconectar();
                
            }else{
                // Verifica si encontro un registro
                if (@mssql_num_rows($result)){

                    $dataTemp = @mssql_fetch_assoc($result);
                    
                    $dataTemp = array_map('trim', $dataTemp);
                }
                
                $resultInsert = @mssql_query(
                    "INSERT INTO [dbo].[orders]
                        ([channel_id]
                        ,[exchange_market_id]
                        ,[exchange_id]
                        ,[modality_id]
                        ,[currency_source_id]
                        ,[currency_target_id]
                        ,[exchange_operation_id]
                        ,[value_date]
                        ,[bank_source_cod]
                        ,[product_source_id]
                        ,[account_source]
                        ,[bank_target_cod]
                        ,[product_target_id]
                        ,[account_target]
                        ,[quatity]
                        ,[price]
                        ,[order_amont_source]
                        ,[order_amont_target]
                        ,[effective_price]
                        ,[fee_currency_cod]
                        ,[fee_amont]
                        ,[transantion_cod]
                        ,[amount]
                        ,[source_funds_id]
                        ,[source_funds_cod]                        
                        ,[reason_id]
                        ,[reason_cod]
                        ,[parameter_id]
                        ,[status_order_cod]
                        ,[created_at]
                        ,[reference_cod]
                        ,[num_cta]
                        ,[currency_cod]
                        ,[process_date]
                        ,[value_date_400]
                        ,[channel_cod]
                        ,[exchange_cod]
                        ,[modality_cod]
                        ,[currency_source_cod]
                        ,[currency_target_cod]
                        ,[exchange_operation_cod]
                        ,[product_source_cod]
                        ,[product_target_cod]
                        ,[exchange_type_id]
                        ,[exchange_type_cod]
                        ,[operation_type_id]
                        ,[operation_type_cod]
                        ,[user_cod])
                    VALUES
                        (".$dataTemp['channel_id']."
                        ,".$dataTemp['exchange_market_id']."
                        ,".$dataTemp['exchange_id']."
                        ,".$dataTemp['modality_id']."
                        ,".$dataTemp['currency_source_id']."
                        ,".$dataTemp['currency_target_id']."
                        ,".$dataTemp['exchange_operation_id']."
                        ,'".date('Y-m-d H:i:s')."'
                        ,'".$dataTemp['bank_cod']."'
                        ,".$dataTemp['product_source_id']."
                        ,'".$cuentaOrigen."'
                        ,'".$dataTemp['bank_cod']."'
                        ,".$dataTemp['product_target_id']."
                        ,'".$cuentaDestino."'
                        ,'".$montoDivisas."'
                        ,'".str_replace(array(','), '.', $dataTemp['rate'])."'
                        ,'".$montoOrigen."'
                        ,'".$montoDestino."'
                        ,'".$tasaEfectiva."'
                        ,1
                        ,'".$comision."'
                        ,'".$dataTemp['transaction_source_cod']."'
                        ,'".$montoTotal."'
                        ,3
                        ,'".$origen."'
                        ,4
                        ,'".$motivo."'
                        ,".$estatusId."
                        ,'".$estatus."'
                        ,'".date('Y-m-d H:i:s')."'
                        ,'".$referencia."' 
                        ,'".$cuenta."' 
                        ,'".$dataTemp['currency_target_cod']."'
                        ,'".$fechaValor."'
                        ,'".$fechaProceso."'
                        ,2
                        ,'".$exchangeCod."'
                        ,'".$dataTemp['modality_cod']."'
                        ,'VEB'
                        ,'".$dataTemp['currency_target_cod']."'
                        ,'CPR'
                        ,'".$dataTemp['product_source_cod']."'
                        ,'".$dataTemp['product_target_cod']."'
                        ,".$dataTemp['exchange_type_id']."
                        ,'".$dataTemp['exchange_type_cod']."'
                        ,11
                        ,'VOC'
                        ,'".$userId."')",                        
                    $this->link
                );
                                  
                if($resultInsert===FALSE) {                    
                    $this->log(mssql_get_last_message(), LOG_ERROR);
                    $this->log('Error al tratar de insertar la operacion', LOG_ERROR);
                    $respuesta = false;
                }

                $this->desconectar();
            }                        

            return $respuesta;
	}
	
}
?>
