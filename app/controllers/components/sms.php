<?php
/**
 * Componente que maneja el envio de mensajes de texto vía SMS.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Maneja el envio de mensajes de texto vía SMS.
 */
class SmsComponent extends Object {
	
	
	/**
	 * Componentes del cake utilizados
	 * 
	 * @var array 
	 */
	var $components = array('Session');

	
	/**
	 * Realiza el envio del mensaje
	 * 
	 * @param array $params Arreglo con los parámetros del mensaje
	 * numcel: Número de teléfono. Por defecto el número de celular del usuario que inició sesión
	 * canal1: 
	 * cuenta: 
	 * fecha: 
	 * hora: 
	 * monto: 
	 * cheque: 
	 * priori: 
	 * xrnde11: 
	 * xrnde38: 
	 * fectim: 
	 * refnum: 
	 * tipcre: 
	 * act: 
	 * @return array
	 */
	function enviar($params){
		if(isset($params['numcel'])){
			$celular = $params['numcel'];
		} else {
			$usuario = $this->Session->read('usuarios.datos');
			$celular = desencriptar($usuario['celular']);
		}
		
		if(
			empty($celular) ||
			strlen($celular) != 11
		){
			$this->log('No se puede enviar sms, ya que no hay celular registrado',LOG_ERR);		
			return;
		}
		
		$link = mssql_connect(
			Configure::read('sms.db.host'),
			desencriptar(Configure::read('sms.db.user')),
			desencriptar(Configure::read('sms.db.pwd'))
		);
		
		mssql_select_db(
			Configure::read('sms.db.name'),
			$link
		);
		
		$stmt = mssql_init(
			Configure::read('sms.db.sp'),
			$link
		);
		
		$result = '';
		
		$params = array_merge(
			array(
				'canal1'=>' ',
				'cuenta'=>' ',
				'fecha'=>date('Ymd'),
				'hora'=>date('His'),
				'monto'=>'0',
				'cheque'=>'0',
				'priori'=>'1',
				'xrnde11'=>' ',
				'xrnde38'=>' ',
				'fectim'=>' ',
				'refnum'=>' ',
				'tipcre'=>'00000',
				'act'=>' '
			),
			$params
		);
		
		//$this->log('enviando SMS',LOG_DEBUG);
		//$this->log($params,LOG_DEBUG);

		mssql_bind($stmt,'@numcel', $celular, SQLVARCHAR, false,false,11);
		mssql_bind($stmt,'@canal1', $params['canal1'], SQLVARCHAR, false,false,4);
		mssql_bind($stmt,'@cuenta', $params['cuenta'], SQLVARCHAR, false,false,6);
		mssql_bind($stmt,'@fecha', $params['fecha'], SQLVARCHAR, false,false,10);
		mssql_bind($stmt,'@hora', $params['hora'], SQLVARCHAR, false,false,8);
		mssql_bind($stmt,'@monto', $params['monto'], SQLVARCHAR, false,false,12);
		mssql_bind($stmt,'@cheque', $params['cheque'], SQLVARCHAR, false,false,10);
		mssql_bind($stmt,'@priori', $params['priori'], SQLVARCHAR, false,false,6);
		mssql_bind($stmt,'@xrnde11', $params['xrnde11'], SQLVARCHAR, false,false,8);
		mssql_bind($stmt,'@xrnde38', $params['xrnde38'], SQLVARCHAR, false,false,8);
		mssql_bind($stmt,'@fectim', $params['fectim'], SQLVARCHAR, false,false,20);
		mssql_bind($stmt,'@refnum', $params['refnum'], SQLVARCHAR, false,false,15);
		mssql_bind($stmt,'@tipcre', $params['tipcre'], SQLVARCHAR, false,false,24);
		mssql_bind($stmt,'@act', $params['act'], SQLVARCHAR, false,false,1);
		mssql_bind($stmt,'@result', $result, SQLINT4, true,false,4);
 
		mssql_execute(
			$stmt
		);
		
		mssql_free_statement(
			$stmt
		);
		
		//$this->log('resultado envio sms: '.$result, LOG_DEBUG);
		
		if($result==0){
			$this->log('Error al enviar sms',LOG_ERR);
			$this->log(mssql_get_last_message(),LOG_ERR);
			$this->log($params,LOG_ERR);
		}
		
		mssql_close($link);
		
		return $result;
	}
}

?>