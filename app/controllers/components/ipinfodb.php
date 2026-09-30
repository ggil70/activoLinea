<?php
/**
 * Componente que maneja las consultas de la geolocalizacion.
 * 
 * @author <mguglielmelli@bancoactivo.com>
 */


/**
 * Maneja las consultas de la geolocalizacion
 */
class IpinfodbComponent extends Object {
	
	private $apiKey = '2a1b1cff190b895a714a5ea05fac5ffcb8699335a3004cc45feb79286268f5e3';        	
        public $apiKey2 = '794c286b15df13c3fe01f3491a0edf65';
        public $apiKey3 = 'ed90fc552500482e14d0bd6c3712f466';
        public $apiKey4 = '271d7edafaf6238fe2740047cdd7354d';              
                        
	public function getCountry($ip)
	{
		$response = @file_get_contents('http://api.ipinfodb.com/v3/ip-country?key=' . $this->apiKey . '&ip=' . $ip . '&format=json');
		if (($json = json_decode($response, true)) === null) {
			$json['statusCode'] = 'ERROR';
			return false;
		}
		$json['statusCode'] = 'OK';
		return $json;
	}
        
	public function getCity($ip)
	{
		$response = @file_get_contents('http://api.ipinfodb.com/v3/ip-city?key=' . $this->apiKey . '&ip=' . $ip . '&format=json');
		if (($json = json_decode($response, true)) === null) {
			$json['statusCode'] = 'ERROR';
			return false;
		}
		$json['statusCode'] = 'OK';
		return $json;
	}			
}

?>