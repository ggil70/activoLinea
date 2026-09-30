<?php
/**
 * Clase dummy para utilizar cake sin base de datos
**/
class DboMyDboSource extends DboSource
{
	/**
	 * Método dummy para simular conexión con base de datos
	 */
	function connect()
	{
		$this->connected = true;
		return $this->connected;
	}
	
	/**
	 * Método dummy para simular desconexión con base de datos
	 */
	function disconnect()
	{
		$this->connected = false;
		return !$this->connected;
	}
}

?>