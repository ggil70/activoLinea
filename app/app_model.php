<?php
/**
 * Clase base de los modelos de la aplicacion
 */
 
/**
 * Clase base de los modelos de la aplicacion
 */
class AppModel extends Model {
	
	/**
	 * 
	 * @var boolean Indica si los queries se guardan en cache por request
	 */
	var $cacheQueries = false;
	
	/**
	 *
	 * @var array Funcionalidades adicionales a los modelos
	 */
	var $actsAs = array('Containable');
	
	
	/**
	 * Método para validar si una cadena es alfanumerica
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function alfanumerico($check) {
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}

		return preg_match('/^[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ ]*$/', $value);
	}
	
	
	/**
	 * Método para validar si una cadena es alfanumerica
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function alfabetico($check) {
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}

		return preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ ]*$/', $value);
	}
	
	
	/**
	 * Método para verificar si una cadena es numerica
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function numerico($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)){
			return true;
		} else if(is_float($value)){
			return $value>0;
		} elseif(is_string($value)) {
			if(preg_match('/^-?(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d+)?$/', $value)){
				$monto = formatea_monto_toWs($value);
				
				return $monto>0;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 * Método para verificar si una cadena es numerica
	 * el monto debe tener decimales
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function montoEstricto($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)){
			return true;
		} else if(is_float($value)){
			return $value>0;
		} elseif(is_string($value)) {
			if(preg_match('/^-?(?:\d+|\d{1,3}(?:\.\d{3})+),\d{2}$/', $value)){
				$monto = formatea_monto_toWs($value);
				return $monto>0;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 * Método para validar el formato de un número de celular.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function celular($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}				
		return preg_match('/^\d{7}$/', $value);
	}
	
	
	/**
	 * Método para verificar si existe una secuencia en una cadena.
	 * 
	 * @param string $needle
	 * @return boolean
	 */
	function hasSequence($needle){
		$secuencias = array(
			'01234567890',
			'abcdefghijklmnñopqrstuvwxyz',
			'09876543210',
			'zyxwvutsrqpoñnmlkjihgfedcba'
		);
		
		$len = 4;
		
		$needle = strtolower($needle);

		for($j=0;$j<count($secuencias);$j++){
			for($i=0;$i<strlen($secuencias[$j])-$len;$i++){
			   $x = substr($secuencias[$j],$i,$len);
			   
			   if(false!==strpos($needle,$x)){
				   return true;
			   }
			}
		}
		
		return false;
	}
	
	
	/**
	 * Método para validar el formato de una clave
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function contrasena($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)) return true;
		
		$repeticiones = '/^(.)\1{3,}/';
		$permitidos = '/^[a-zA-Z0-9 \!\"\#\$\%\&\/\(\)\=\?\¡]*$/';
		$numeros = '/[0-9]+/';
		$letras = '/[a-zA-Z]+/';
		
		$resultado = !$this->hasSequence($value);
		$resultado &= !preg_match($repeticiones, $value);
		$resultado &= preg_match($permitidos, $value);
		$resultado &= preg_match($numeros, $value);
		$resultado &= preg_match($letras, $value);
		
		return $resultado;
	}	
	
	
	/**
	 * Método para validar la clave contra los datos del usuario
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function contrasena2($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)) return true;
		
		App::import('Component', 'Session');
		
		$session = new SessionComponent();
		
		$keywords = array();
		
		if($session->check('usuarios.datos')){
			$datos = $session->read('usuarios.datos');
			$tipo_cedula = $datos['tipo_cedula'];
			$login = desencriptar($datos['login']);
			$cedula = desencriptar($datos['cedula']);
			
			// EN CASO DE QUE SEA JURIDICO
			if(!empty($datos['tipo_rif'])){
				$tipo_cedula = $datos['tipo_rif'];
				$cedula = desencriptar($datos['rif']);
				
				// datos basicos del juridico
				$keywords[] = desencriptar($datos['cedula']);
				$keywords[] = substr(desencriptar($datos['celular']), 4);
				$keywords = array_merge($keywords, explode(' ',desencriptar($datos['nombre'])));
			}
			
		} else if($session->check('usuarios.registro')){
			$datos = $session->read('usuarios.registro');
			
			if($session->check('login.juridico')){
				$tipo_cedula = $this->data['Usuario']['tipoRif'];
				$cedula = $this->data['Usuario']['nroRif'];
				
				// datos del juridico
				if(isset($datos['Usuario']['nroCedulaRif'])) $keywords[] = $datos['Usuario']['nroCedulaRif'];
				if(isset($datos['Usuario']['nroCelular'])) $keywords[] = $datos['Usuario']['nroCelular'];
				if(isset($this->data['Usuario']['nombre'])) $keywords = array_merge($keywords, explode(' ',$this->data['Usuario']['nombre']));
				
			} else {
				$tipo_cedula = $datos['Usuario']['tipoCedulaRif'];
				$cedula = $datos['Usuario']['nroCedulaRif'];
			}
			
			if(isset($this->data['Usuario']['nomUsuario'])){
				$login = $this->data['Usuario']['nomUsuario'];
			} else {
				$login = $datos['Usuario']['nomUsuario'];
			}
		} else if($session->check('seguridad.olvido')){
			$olvido = $session->read('seguridad.olvido');
			
			$tipo_cedula = $olvido['datasource']['tipo_cedula'];
			$cedula = desencriptar($olvido['datasource']['cedula']);
			$login = desencriptar($olvido['datasource']['login']);
			
			// EN CASO DE QUE SEA JURIDICO
			if(!empty($olvido['datasource']['tipo_rif'])){
				$tipo_cedula = $olvido['datasource']['tipo_rif'];
				$cedula = desencriptar($olvido['datasource']['rif']);
				
				// datos del juridico
				$keywords[] = desencriptar($olvido['datasource']['cedula']);
				$keywords[] = substr(desencriptar($olvido['datasource']['celular']), 4);
				$keywords = array_merge($keywords, explode(' ', desencriptar($olvido['datasource']['nombre'])));
				
			}
		}
		
		$resultado = true;
		
		if(isset($cedula)){
			
			$keywords[] = $login;
			$keywords[] = $cedula;
			$keywords[] = 'login';
			$keywords[] = 'usuario';
			$keywords[] = 'activo';
			$keywords[] = 'banco';
			$keywords[] = 'bancoactivo';
		
			App::import('Component', 'Activo');
			
			$activo = new ActivoComponent();
			
			$datos_basicos = $activo->invoca(
				'NIBPF3010',
				array(
					'req'=>array(
						'NACIONALIDAD'=>$tipo_cedula,
						'NROIDENTIFICACION'=>str_pad($cedula,12,'0',STR_PAD_LEFT),
						'NROPARAM'=>'1'
					)
				)
			);
			
			if($datos_basicos){
				$keywords = array_merge(
					$keywords,
					array(
						$datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'],
						$datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE'],
						$datos_basicos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'],
						$datos_basicos['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO'],
						$datos_basicos['NIBPF3010Return']['NIBPF3010']['FECHANAC'],
						substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['FECHANAC'],0,4).substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['FECHANAC'],6),
						$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR'],
						substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR'],4),
						$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROHAB'],
						substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROHAB'],4),
						$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROOFICINA'],
						substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROOFICINA'],4),
						$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROFAX'],
						substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['NROFAX'],4)
					)
				);
				
				if(
					!empty($datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD']) &&
					strlen($datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD'])>=4
				){
					$keywords []= $datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD'];
					$keywords []= substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD'],0,4);
					$keywords []= substr($datos_basicos['NIBPF3010Return']['NIBPF3010']['TDD'],-4);
				}
				
				$sumdsjv = $activo->invoca(
					'SUMDSJV',
					array(
						'req'=>array(
							'SUMACN'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],
							'SUMFIR'=>'X'
						)
					)
				);
				
				if($sumdsjv){
					if(isset($sumdsjv['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'])){
						$x = $sumdsjv['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
						normaliza($x);

						if(isset($x) && count($x)){
							foreach($x as $i){
								if(in_array($i['SUMTYP'],array('SAV','DDA','NOW'))){
									$keywords []= substr($i['SUMACN'],-10);
								}
							}
						}
					}
				}
			}
			
			$keywords = array_map('trim',array_map('strtolower',$keywords));
			
			$x = strtolower($value);
			
			foreach($keywords as $k){
				if(strlen($k)>0 && strpos($x,$k)!== false){
					$resultado = false;
					break;
				}
			}			
		}

		return $resultado;
	}
	
	
	/**
	 * Método para validar el login del usuario si realiza el proceso de migración.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function usuario_legacy($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(empty($value)) return true;
		
		$permitidos = '/^[a-zA-Z0-9\!\"\#\$\%\&\/\(\)\=\?\¡]*$/';

		$resultado = preg_match($permitidos, $value);
		
		return $resultado;
	}
	
	
	/**
	 * Verifica:
	 * - La longitud maxima del valor de la cedula de acuerdo al tipo
	 * - Si es alfanumerico o numerico de acuerdo al tipo
	 *
	 * @param string $tipo
	 * @param string $valor
	 * @return boolean
	 */
	function checkMaxCI($tipo, $valor){
		App::import('Component', 'Session');
		$session = new SessionComponent();
		$tipo = strtoupper($tipo);
		$arrayMaxTipos = array("V" => 9, "E" => 9, "P" => 15, "J" => 9, "G" => 9);
		$arrayMinTipos = array("V" => 6, "E" => 5, "P" => 3, "J" => 9, "G" => 9);
		$booleanType = false;
		
		if($tipo=='P'){
			$booleanType = preg_match('/^\w+$/i', $valor);
		} else {
			$booleanType = preg_match('/^\d+$/', $valor);
		}
	
		return $booleanType && !(strlen($valor)>$arrayMaxTipos[$tipo]) && !(strlen($valor)<$arrayMinTipos[$tipo]);
	}
	
	
	/**
	 * Método para la validación del banco
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function banco($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		App::import('Component', 'Activo');
		
		$activo = new ActivoComponent();
		
		$bancos = $activo->buscarBancos();
		
		return in_array(substr($value,0,4), array_keys($bancos));		
	}
	
	
	/**
	 * Método para la validación de otros bancos
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function otro_banco($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		$prefijo = substr($value,0,4);
		
		if($prefijo=='0171') {
			return false;
		} else {
			App::import('Component', 'Activo');
			
			$activo = new ActivoComponent();
			
			$bancos = $activo->buscarBancos();
			
			return in_array($prefijo, array_keys($bancos));
		}		
	}
	
	
	/**
	 * Método que realiza la validación del número de cuenta
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function nroCuenta($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(!preg_match('/^\d{20}$/i',$value)){
			return false;
		} else {
		
			App::import('Component', 'Activo');

			$activo = new ActivoComponent();
		
			$bancos = $activo->buscarBancos();
			
			if(!array_key_exists(substr($value,0,4),$bancos)){
				return false;
			} else {
				// Validar dígito verificador
				
				$banco = substr($value, 0, 4);
				$oficina = substr($value, 4, 4);
				$ultimos10 = substr($value, -10);
				$digitos = substr($value,8,2);

				$aux1 = substr(strrev(str_pad($banco.$oficina.'0',15,'0',STR_PAD_LEFT)),-14);

				$aux2 = substr(strrev(str_pad($oficina.$ultimos10.'0',15,'0',STR_PAD_LEFT)),-14);

				$factorm = array(2,3,4,5,6,7,2,3,4,5,6,7,2,3);
				
				$digito1 = $digito2 = 0;

				for($i=0;$i<strlen($aux1);$i++){
					$digito1 += $aux1[$i]*$factorm[$i];
					$digito2 += $aux2[$i]*$factorm[$i];
				}

				$digito1 = (11 - ($digito1 % 11)) % 10;
				$digito2 = (11 - ($digito2 % 11)) % 10;

				return $digitos==$digito1.$digito2;
			}
		}
	}
	
	
	/**
	 * Método que realiza la validación del número de cuenta contra el servicio 
	 * de bancoactivo.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function nroCuenta2($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/',$value)){
			App::import('Component', 'Session');
			App::import('Component', 'Activo');

			$activo = new ActivoComponent();	
			$session = new SessionComponent();
			
			$usuario = $session->read('usuarios.datos');		

			$productoValidar = substr($value,-10);
			$valorCuenta= array(
				'NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 
				'NROPARAM' => 5,
				'NACIONALIDAD'=>trim($this->data[$this->name]['tipo_cedula']),
				'NROIDENTIFICACION'=>str_pad(substr(trim($this->data[$this->name]['cedula']),0,12),12,'0',STR_PAD_LEFT)
			);

			$datos = $activo->validarProducto($valorCuenta);



			if($datos){
				$error = (isset($datos['NIBPF3010Return']['error']['ERNU01'])) ? intval($datos['NIBPF3010Return']['error']['ERNU01']) : 0;
								
				if($error == 41){ // Verifica si es valido el numero de cuenta
					$this->validationErrors['numero_cuenta'] = 'nroCuenta';
				} 
				else if($error == 608){ // Indica que la cedula no es correcta
					$this->validationErrors['cedula'] = 'longitud';
				} 
				else if($error == 9950){ // Verifica si el beneficiario es titular de la cuenta
					$this->validationErrors['cedula'] = 'noBeneficiario';
				} 
				else if($error == 0){
					if(empty($usuario['tipo_cedula'])){
						$tipo_id = $usuario['tipo_cedula'];
						$nro_id = desencriptar($usuario['cedula']);
					} else {
						$tipo_id = $usuario['tipo_rif'];
						$nro_id = desencriptar($usuario['rif']);								
					}
					
					$idibs = strtolower(preg_replace('/^(.)0*(\d+)\s*$/i','$1$2',$datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']));
					
						
					if($idibs==strtolower($tipo_id.ltrim($nro_id,'0'))){
						$this->validationErrors['numero_cuenta'] = 'productoPropio';
					} else if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='A' && $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='T'){
						
						if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
							$this->validationErrors['numero_cuenta'] = 'cuentaControlada';
						} else {
							$this->validationErrors['numero_cuenta'] = 'cuentaInactiva';
						}
					} else {
						// Completar el tamaño de la cuenta
						if(10==strlen($this->data[$this->name]['numero_cuenta'])){
							$res = $activo->completaCuenta($this->data[$this->name]['numero_cuenta']);
							
							if($res){
								$this->data[$this->name]['numero_cuenta'] = str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT);
							} else {
								return false;
							}
						}
						
					}
					
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return $this->nroCuenta($check);
		}
		
		return true;
	}
	
	
	/**
	 * Valida contra el monto máximo de la afiliación
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function montoMaximo($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if(
			$value!="" &&
			isset($this->data[$this->name]['monto_maximo']) &&
			$this->data[$this->name]['monto_maximo']!=""
		){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data[$this->name]['monto_maximo']);		

		   return $monto <= $monto_maximo;
		}
		
		return true;
	}  
	
	
	/**
	 * Valida contra el monto acumulado
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function montoLimite($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if(
			$value!="" &&
			isset($this->data[$this->name]['monto_limite']) &&
			$this->data[$this->name]['monto_limite']!=""
		){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data[$this->name]['monto_limite']);		

		   return $monto <= $monto_maximo;
		}
		return true;
	} 
	
	
	/**
	 * Valida contra el monto límite configurado para el usuario jurídico
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function montoLimiteUsuario($check){
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if(
			$value!="" &&
			isset($this->data[$this->name]['monto_limite_usuario']) &&
			$this->data[$this->name]['monto_limite_usuario']!=""
		){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data[$this->name]['monto_limite_usuario']);		

		   return $monto <= $monto_maximo;
		}
		return true;
	}
	
	
	/**
	 * Si está afiliando, valida que el monto no sea superior al máximo
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function limiteTotal($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if(
			$value!="" &&
			isset($this->data[$this->name]['limite_total']) &&
			$this->data[$this->name]['limite_total']!=""
		){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data[$this->name]['limite_total']);		

		   return $monto <= $monto_maximo;
		}
		return true;
	}
    
	/**
	 * Si es una operacion de alto valor, valida que el monto de la transaccion mas la sumatoria
         * de operaciones alto valor hechas durante el dia no sea superior al máximo diario del cliente
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function limiteAltoValor($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if(
                    $value!="" &&
                    isset($this->data[$this->name]['limite_total']) &&
                    $this->data[$this->name]['limite_total']!="" &&
                    isset($this->data[$this->name]['alto_valor']) &&
                    isset($this->data[$this->name]['parametro_altoValor']) &&
                    $this->data[$this->name]['parametro_altoValor']!=""
		){
		   $monto = formatea_monto_toWs($value);
                   $parametro_altoValor = formatea_monto_toWs($this->data[$this->name]['parametro_altoValor']);
                   $monto_altoValor = $this->data[$this->name]['alto_valor'];
		   $monto_maximo = formatea_monto_toWs($this->data[$this->name]['limite_total']);                   
                   $monto_sumatoria = formatea_monto_toWs($monto + $monto_altoValor);
                   
                   if($monto >= $parametro_altoValor){
                       return $monto_sumatoria <= $monto_maximo;                           
                   }else{
                       return true;
                   }		   
		}
                
		return true;
	}
        
        //Funcion para validar los caracteres especiales         
        function uploadFile($check) {
 
            $uploadData = array_shift($check);
 
            /*if ( $uploadData['size'] == 0 || $uploadData['error'] !== 0) 
            {
              return false;
            }*/
            $nombreArchivo = (explode('.', $uploadData['name']));                        
            
            if (ctype_alnum($nombreArchivo[0])){
                return true;
            }else{
                return false;
            }
        }
	        
        /**
	 * Funcion para validar que la identificacion del beneficiario no sea igual a la del cliente logeado
	 * para transferencias terceros otros bancos
         * 
	 * @param array|string $tipoIdentificacion
         * @param array|string $identificacion 
	 * @return boolean
	 */
        function checkDestinatario($tipoIdentificacion,$identificacion) {
 
            $session = new SessionComponent();
			
            $usuario = $session->read('usuarios.datos');                        
            
            if($usuario['perfil_ib_id']==1){
                
                $tipo_id = $usuario['tipo_cedula'];
                $nro_id = desencriptar($usuario['cedula']);
                
                if (($tipo_id==$tipoIdentificacion) && ($nro_id==$identificacion)){
                    return false;
                }else{
                    return true;
                }
                
            }else{
                
                $tipo_id = $usuario['tipo_rif'];
                $nro_id = desencriptar($usuario['rif']);
                if (($tipo_id==$tipoIdentificacion) && ($nro_id==$identificacion)){
                    return false;
                }else{
                    return true;
                }
                
            }
            
            
        }


// Cambios cuenta express
	function nroCuentaExpress($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if(preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/',$value)){
			App::import('Component', 'Session');
			App::import('Component', 'Activo');

			$activo = new ActivoComponent();	
			$session = new SessionComponent();
			
			$usuario = $session->read('usuarios.datos');		

			$productoValidar = substr($value,-10);
			$valorCuenta= array(
				'NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 
				'NROPARAM' => 5,
				'NACIONALIDAD'=>trim($this->data[$this->name]['tipo_cedula']),
				'NROIDENTIFICACION'=>str_pad(substr(trim($this->data[$this->name]['cedula']),0,12),12,'0',STR_PAD_LEFT)
			);
			
			$datos = $activo->validarProducto($valorCuenta);

			if($datos){
				$error = (isset($datos['NIBPF3010Return']['error']['ERNU01'])) ? intval($datos['NIBPF3010Return']['error']['ERNU01']) : 0;
								
				if($error == 41){ // Verifica si es valido el numero de cuenta
					$this->validationErrors['numero_cuenta'] = 'nroCuenta';
				} 
				else if($error == 608){ // Indica que la cedula no es correcta
					$this->validationErrors['cedula'] = 'longitud';
				} 
				else if($error == 9950){ // Verifica si el beneficiario es titular de la cuenta
					$this->validationErrors['cedula'] = 'noBeneficiario';
				} 
				else if($error == 0){
					if(empty($usuario['tipo_cedula'])){
						$tipo_id = $usuario['tipo_cedula'];
						$nro_id = desencriptar($usuario['cedula']);
					} else {
						$tipo_id = $usuario['tipo_rif'];
						$nro_id = desencriptar($usuario['rif']);								
					}
					
					$idibs = strtolower(preg_replace('/^(.)0*(\d+)\s*$/i','$1$2',$datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']));
					
						
					if($idibs==strtolower($tipo_id.ltrim($nro_id,'0'))){
						$this->validationErrors['numero_cuenta'] = 'productoPropio';
					} else if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='A' && $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='T'){
						
						if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
							$this->validationErrors['numero_cuenta'] = 'cuentaControlada';
						} else {
							$this->validationErrors['numero_cuenta'] = 'cuentaInactiva';
						}
					} else {
						// Completar el tamaño de la cuenta
						if(10==strlen($this->data[$this->name]['numero_cuenta'])){
							$res = $activo->completaCuenta($this->data[$this->name]['numero_cuenta']);
							
							if($res){
								$this->data[$this->name]['numero_cuenta'] = str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT);
							} else {
								return false;
							}
						}
						
					}
					
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return $this->nroCuenta($check);
		}
		
		return true;
	}

    






}
?>
