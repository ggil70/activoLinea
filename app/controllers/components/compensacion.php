<?php
/**
 * Componente que maneja las consultas con la bd de compensacion.
 * 
 * @author <mguglielmelli@bancoactivo.com>
 */


/**
 * Maneja las consultas a la bd de compensacion
 */
class CompensacionComponent extends Object {
	
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
                    Configure::read('comp.db.host'),
                    desencriptar(Configure::read('comp.db.user')),
                    desencriptar(Configure::read('comp.db.pwd'))
		);
		
		if($this->link){
			@mssql_select_db(
                            Configure::read('comp.db.name'),
                            $this->link
			);
			
			return true;
		}else{
                    
                    $this->log('Error al conectar con servidor de base de datos '.Configure::read('comp.db.host'), LOG_ERROR);
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
	 * Método para consultar el horario de cierre de franja de compensacion
	 * 
	 */
	function consultaCierreFranja(){
							
		if(!$this->conectar()) return false;
		
		$respuesta = false;				
		
		$result = @mssql_query(
                    "SELECT TX_CONST AS HORA FROM CONST_PCCOMP WHERE CO_CONST='TIME_HASTA'", 
                    $this->link
		);
		
		if($result===FALSE) {
			$this->log('Error al tratar de consultar el parametro de cierre de franja ', LOG_ERROR);
		}
		else {
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
			
}

?>