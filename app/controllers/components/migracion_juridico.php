<?php
/**
 * Componente para el manejo de las operaciones del proceso de migración de 
 * usuarios jurídicos.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de las operaciones del proceso de migración de usuarios jurídicos.
 */
class MigracionJuridicoComponent extends Object{
	
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
	 * Mapeo de transacciones ib anterior a ib actual
	 */
	private $mapeo_transacciones = array(
		10=>array(
			// Consulta de cuentas
			0=>array(
				'transaccion_id'=>3,
				'instrumentos'=>array('cuentas')
			),
		),
		12=>array(
			// Consulta de tdc
			0=>array(
				'transaccion_id'=>4,
				'instrumentos'=>array('tdcs')
			),
		),
		20=>array(
			// Afiliación de transferencias
			0=>array(
				'transaccion_id'=>13
			),
			// Transferencias mismo titular mismo banco
			1=>array(
				'transaccion_id'=>100021,
				'instrumentos'=>array('cuentas')
			),
			// Transferencias terceros mismo banco
			2=>array(
				'transaccion_id'=>100022,
				'instrumentos'=>array('cuentas')
			),
			// Transferencias mismo titular otros bancos
			3=>array(
				'transaccion_id'=>100023,
				'instrumentos'=>array('cuentas')
			),
			// Transferencias terceros otros bancos
			4=>array(
				'transaccion_id'=>100024,
				'instrumentos'=>array('cuentas')
			),
		),
		30=>array(
			// Suspensión de cheques
			2=>array(
				'transaccion_id'=>22
			),
		),
		40=>array(
			// Pago tdc mismo titular mismo banco
			2=>array(
				'transaccion_id'=>100025,
				'instrumentos'=>array('cuentas','tdcs')
			),
			// Pago tdc mismo titular otros bancos
			3=>array(
				'transaccion_id'=>100027,
				'instrumentos'=>array('cuentas')
			),
			// Pago nómina
			6=>array(
				'transaccion_id'=>array(39,100016)
			),
			// Pago proveedores
			7=>array(
				'transaccion_id'=>array(40,100017)
			),
			// Seguimiento de gestión
			8=>array(
				'transaccion_id'=>43
			),
		),
		41=>array(
			// Afiliación nómina
			1=>array(
				'transaccion_id'=>41
			),
			// Afiliación proveedores
			2=>array(
				'transaccion_id'=>42
			),
		),
		70=>array(
			// Inversiones (Deposito a plazo)
			1=>array(
				'transaccion_id'=>6,
				'instrumentos'=>array('inversiones')
			)
		)
	);
	
	
	/**
	 * Método para realizar la conexión a la base de datos
	 * 
	 * @access private
	 * @return boolean
	 */
	private function conectar(){
		
		$this->link = @mssql_connect(
			Configure::read('jm.db.host'),
			desencriptar(Configure::read('jm.db.user')),
			desencriptar(Configure::read('jm.db.pwd'))
		);
		
		if($this->link){
			@mssql_select_db(
				Configure::read('jm.db.name'),
				$this->link
			);
			
			return true;
		}else{
                    
                    $this->log('Error al conectar con servidor de base de datos '.Configure::read('jm.db.host'), LOG_ERROR);
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
	 * Método para validar la existencia del usuario en el Internet Banking.
	 * 
	 * @param string $rif Identificador de la empresa.
	 * @param string $login Usuario utilizado para el acceso del Internet Banking.
	 * @return boolean
	 */
	function validarUsuario($rif, $login){
		
		if(!$this->Session->check('login.juridico')) return false;
		
		if(empty($rif) || empty($login)) return false;
			
		if(!$this->conectar()) return false;
		
		$respuesta = false;
		
		//$this->log('Ejecucion Validate - ' . $rif . ' - ' . $login, LOG_DEBUG);
		
		$result = @mssql_query(
			"SELECT * FROM Validate('".stripslashes($rif)."','".stripslashes($login)."')", 
			$this->link
		);
		
		if($result===FALSE) {
			$this->log('Error al tratar de validar el usuario '.$rif, LOG_ERROR);
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
	
	
	/**
	 * Autentica un usuario jurídico migrado.
	 * 
	 * @param string $rif Identificador de la empresa.
	 * @param string $login Usuario utilizado para el acceso del Internet Banking.
	 * @param string $pass Clave de acceso del Internet Banking.
	 * @param array $legacy Arreglo con datos para la migración.
	 * @return array|boolean
	 */
	function autenticarUsuario($rif, $login, $pass, $legacy){
		
		if(!$this->Session->check('login.juridico')) return false;
		
		if(empty($rif) || empty($login) || empty($pass)) return false;
			
		if(!$this->conectar()) return false;
		
		$respuesta = false;
		
		// Encripta la clave del usuario
		$encriptado = $this->Activo->invoca(
			'SHA1Encrypt',
			array(
				'req'=>array(
					'modificado'=> true,
					'texto'=> strtoupper(trim($pass)),
				)
			)
		);
		
		if(!isset($encriptado['SHA1EncryptReturn']['encriptado']) || empty($encriptado['SHA1EncryptReturn']['encriptado'])) return false;
		
		//$this->log('Ejecucion Autenticate - ' . $rif . ' - ' . $login . ' - ' . $encriptado['SHA1EncryptReturn']['encriptado'], LOG_DEBUG);
		
		$result = @mssql_query(
			"SELECT * FROM Autenticate('".stripslashes($rif)."','".stripslashes($login)."','".$encriptado['SHA1EncryptReturn']['encriptado']."')", 
			$this->link
		);
		
		if($result===FALSE) {
			$this->log('Error al tratar de autenticar el usuario '.$rif . ' - ' . $login . ' - ' . $encriptado['SHA1EncryptReturn']['encriptado'], LOG_ERROR);
		} elseif(@mssql_num_rows($result)){
			
			// Consulta los datos basicos del usuario
			$datos_basicos = $this->Activo->invoca(
				'NIBPF3010',
				array(
				'req'=>array(
					'NACIONALIDAD'=>substr($rif,0,1),
					'NROIDENTIFICACION'=>str_pad(substr($rif,1),12,'0',STR_PAD_LEFT),
					'NROPARAM'=>'1'
					)
				)
			);

			// Consulta los productos del usuario
			$instrumentos = array();
			
			if(isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])){
				$res = $this->Activo->invoca(
					'SUMDSJV',
					array(
						'req'=>array(
							'SUMACN'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],
							'SUMFIR'=>'X'
						)
					)
				);
				
				if($res){
					$instrumentos = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
					normaliza($instrumentos);
				}
			}
			
			$cuentas = array();
			$tdcs = array();
			$creditos = array();
			$inversiones = array();
			$instrumentos_agrupados = array();
			
			if(isset($instrumentos)){
				foreach($instrumentos as $i){
					// cuentas
					if(in_array($i['SUMTYP'], array('SAV','DDA','NOW')) ){
						$instrumentos_agrupados['cuentas'] []= trim($i['SUMACN']);
					} else if($i['SUMTYP']=='CC&'){
						$instrumentos_agrupados['tdcs'] []= trim($i['SUMACN']);
					} else if($i['SUMTYP']=='LNS'){
						$instrumentos_agrupados['creditos'] []= trim($i['SUMACN']);
					} else if($i['SUMTYP']=='CDS'){
						$instrumentos_agrupados['inversiones'] []= trim($i['SUMACN']);
					}
				}
			}
			
			// Genera arreglo con transacciones permisadas
			
			$transacciones = array();
			
			// Recorre el resultado obtenido
			
			while ($dataTemp = @mssql_fetch_assoc($result)){
				
				$dataTemp = array_map('trim', $dataTemp);
			
				$perfil = 0;
				
				// Obtiene el perfil
				switch ($dataTemp['PERFIL']){
					case 'OPERADOR':
						$perfil = 3;
						break;
					case 'SUPERVISOR':
						$perfil = 4;
						break;
					default:
						$perfil = 5;
						break;
				}

				$legacy['Usuario']['tdd_validada'] = true;
				$legacy['Usuario']['nombre'] = $dataTemp['FIRSTNAME'].' '.$dataTemp['LASTNAME'];
				$legacy['Usuario']['perfil'] = $perfil;
				$legacy['Usuario']['cargo'] = $dataTemp['TITLE'];
				$legacy['Usuario']['emailPri'] = $dataTemp['EMAIL'];
				$legacy['Usuario']['usuario_creado'] = true;
				
				// Verificar si la transacción es de cabecera
				
				if($dataTemp['CMID']==0){
					// Extraer el nivel de aprobaciones que es lo que nos interesa
					switch($dataTemp['MMID']){
						case 20: // Transferencias
							$apv_transferencias = $dataTemp['APPLEVEL'];
							break;
						case 30: // Cheques
							$apv_cheques = $dataTemp['APPLEVEL'];
							break;
						case 40: // Pagos
							$apv_pagos = $dataTemp['APPLEVEL'];
					}
				}
				
				// Busca transacción en mapeo
				
				if(isset($this->mapeo_transacciones[$dataTemp['MMID']][$dataTemp['CMID']])){
					$mapeada = $this->mapeo_transacciones[$dataTemp['MMID']][$dataTemp['CMID']];
				} else {
					// Si no tenemos mapeada la transacción, continuamos iterando
					continue;
				}
				
				// Permisos para cuentas
				
				if(isset($mapeada['instrumentos']) && in_array('cuentas', $mapeada['instrumentos'])){
					// Construye arreglo con las cuentas seleccionadas en la banca anterior para el usuario
					// en cuestión
					
					$cuentas_seleccionadas = array();
					
					for($i=1; $i<=10; $i++){
						$cuenta= $dataTemp['EXACC'. str_pad($i, 2, '0', STR_PAD_LEFT)];
						
						if(!empty($cuenta) && $cuenta>0){
							// invoca al servicio que completa el numero
							$res = $this->Activo->completaCuenta($cuenta);

							// Verifica si trae la cuenta
							if( $res && !isset($res['EDL890101Return']['error']) ){
								$cuentas_seleccionadas[]=str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT);
							}
						}
					}
					
					// Genera arreglo con cuentas permisadas según el campo INCEXC
					
					$cuentas_finales = array();
					
					switch($dataTemp['INCEXC']){
						case 'N': // Todas las cuentas
							break;
						case 'I': // Sólo las cuentas seleccionadas
							$instrumentos_agrupados['cuentas'] = $cuentas_seleccionadas;
							break;
						case 'E': // Todas las cuentas excepto las seleccionadas para el usuario
							$instrumentos_agrupados['cuentas'] = array_diff($instrumentos_agrupados['cuentas'], $cuentas_seleccionadas);
					}
				}
				
				if(isset($mapeada['instrumentos'])){
					foreach($mapeada['instrumentos'] as $tipo_ins){
						if(isset($instrumentos_agrupados[$tipo_ins])){
							foreach($instrumentos_agrupados[$tipo_ins] as $instrumento){
								if(is_array($mapeada['transaccion_id'])){
									foreach($mapeada['transaccion_id'] as $i){
										
										// Evitar que repita la cuenta para una misma transaccion
										if(count($transacciones) && isset($transacciones[$i]['cuenta']) && in_array($instrumento, $transacciones[$i]['cuenta'])) continue;
										
										$transacciones[$i][]= array(
											'cuenta' => encriptar($instrumento),
											'limite' => number_format($dataTemp['LMTAMNT'],2,'.',''),
											'aprobacion'=>null
										);
									}
								} else {
									
									// Evitar que repita la cuenta para una misma transaccion
									if(count($transacciones) && isset($transacciones[$i]['cuenta']) && in_array($instrumento, $transacciones[$mapeada['transaccion_id']]['cuenta'])) continue;
										
									$transacciones[$mapeada['transaccion_id']][]= array(
										'cuenta' => encriptar($instrumento),
										'limite' => number_format($dataTemp['LMTAMNT'],2,'.',''),
										'aprobacion'=>null
									);
								}
							}
						}
					}
				} elseif(is_array($mapeada['transaccion_id'])){
					foreach($mapeada['transaccion_id'] as $i){
						$transacciones[$i][]= array(
							'limite' => number_format($dataTemp['LMTAMNT'],2,'.',''),
							'aprobacion'=>null
						);
					}
				} else {
					$transacciones[$mapeada['transaccion_id']][]= array(
						'limite' => number_format($dataTemp['LMTAMNT'],2,'.',''),
						'aprobacion'=>null
					);
				}
			}
			
			// Agregar permisos de consulta de créditos, si los tiene
			
			if(isset($instrumentos_agrupados['creditos'])){
				foreach($instrumentos_agrupados['creditos'] as $instrumento){
					$transacciones[5][]= array(
						'cuenta' => encriptar($instrumento),
						'limite' => null,
						'aprobacion'=>null
					);
				}
			}
			
			// Asignar niveles de aprobación a las transacciones detectadas anteriormente
			
			if(!isset($apv_transferencias)){
				$this->log("No se encontro nivel de aprobación de transferencia para $rif $login usando 1 por defecto", LOG_ERR);
				$apv_transferencias = 2;
			}
			
			if(!isset($apv_pagos)){
				$this->log("No se encontro nivel de aprobación de pagos para $rif $login usando 1 por defecto", LOG_ERR);
				$apv_pagos = 2;
			}
			
			if(!isset($apv_cheques)){
				$this->log("No se encontro nivel de aprobación de cheques para $rif $login usando 1 por defecto", LOG_ERR);
				$apv_cheques = 2;
			}
			
			// Transferencias mismo titular mismo banco
			
			if(isset($transacciones[100021])){
				foreach($transacciones[100021] as &$i){
					$i['aprobacion'] = $apv_transferencias-1;
				}
			}			
			
			// Transferencias terceros mismo banco
			
			if(isset($transacciones[100022])){
				foreach($transacciones[100022] as &$i){
					$i['aprobacion'] = $apv_transferencias-1;
				}
			}
			
			// Transferencias mismo titular otros bancos
			
			if(isset($transacciones[100023])){
				foreach($transacciones[100023] as &$i){
					$i['aprobacion'] = $apv_transferencias-1;
				}
			}
			
			// Transferencias mismo titular otros bancos
			
			if(isset($transacciones[100024])){
				foreach($transacciones[100024] as &$i){
					$i['aprobacion'] = $apv_transferencias-1;
				}
			}
			
			// Pagos tdc mismo titular mismo banco
			
			if(isset($transacciones[100025])){
				foreach($transacciones[100025] as &$i){
					$i['aprobacion'] = $apv_pagos-1;
				}
			}			
			
			// Pagos tdc mismo titular otros bancos
			
			if(isset($transacciones[100027])){
				foreach($transacciones[100027] as &$i){
					$i['aprobacion'] = $apv_pagos-1;
				}
			}
			
			// Suspensión de cheques
			
			if(isset($transacciones[22])){
				foreach($transacciones[22] as &$i){
					$i['aprobacion'] = $apv_cheques-1;
				}
			}
			
			// Pagos de nómina
			
			if(isset($transacciones[39])){
				foreach($transacciones[39] as &$i){
					$i['aprobacion'] = $apv_pagos-1;
				}
			}			
			
			if(isset($transacciones[100016])){
				foreach($transacciones[100016] as &$i){
					$i['aprobacion'] = $apv_pagos-1;
				}
			}			
			
			// Pagos proveedores
			
			if(isset($transacciones[40])){
				foreach($transacciones[40] as &$i){
					$i['aprobacion'] = $apv_pagos-1;
				}
			}
			
			if(isset($transacciones[100017])){
				foreach($transacciones[100017] as &$i){
					$i['aprobacion'] = $apv_pagos-1;
				}
			}
			
			// Activar la opción de aprobaciones pendientes según el tipo de usuario
			// si el mismo tiene permiso de hacer transferencias o pagos
			
			if(
				isset($transacciones[100021])  ||
				isset($transacciones[100022])  ||
				isset($transacciones[100023])  ||
				isset($transacciones[100024])  ||
				isset($transacciones[100025])  ||
				isset($transacciones[100026])  ||
				isset($transacciones[100027])  ||
				isset($transacciones[100028])  ||
				isset($transacciones[39]) ||
				isset($transacciones[100016]) ||
				isset($transacciones[40]) ||
				isset($transacciones[100017])
			){
				// Aprobadores y Operadores/Aprobadores
				if($perfil==4 || $perfil==5){
					// Aprobar
					$transacciones[54][]= array('limite' => null,'aprobacion'=>null);
					
					// Rechazar
					$transacciones[55][]= array('limite' => null,'aprobacion'=>null);
				}
				
				// Operadores y Operadores/Aprobadores
				
				if($perfil==3 || $perfil==5){
					// Modificar
					$transacciones[56][]= array('limite' => null,'aprobacion'=>null);
					
					// Eliminar
					$transacciones[57][]= array('limite' => null,'aprobacion'=>null);
				}
			}
			

			$legacy['transacciones_asignadas'] = $transacciones;
			
			$respuesta = $legacy;
			
		}
		
		$this->desconectar();
		
		return $respuesta;
	}

	
	/**
	 * Busca los beneficiarios de la empresa y los almacena en la base de datos.
	 * 
	 * @param string $tipo_rif Tipo de RIF de la empresa
	 * @param string $nro_rif Número de RIF de la empresa
	 * @return boolean
	 */
	function guardaBeneficiarios($tipo_rif, $nro_rif){
		
		// Limite de tiempo
		set_time_limit(0);
		
		
		if(!$this->Session->check('login.juridico')) return false;
		
		if(empty($tipo_rif) || empty($nro_rif)) return false;
			
		if(!$this->conectar()) return false;
				
		// Modelo de afiliacion
		$this->AfiliacionCash = ClassRegistry::init('AfiliacionCash');
			
		// Verifica si ya existen afiliados
		$encontrado = $this->AfiliacionCash->find(
			'count',
			array(
				'conditions'=> array(
					'tipo_rif' => $tipo_rif,
					'rif' => encriptar($nro_rif),
				)
			)
		);
			
		if($encontrado)	return true;
		
		$respuesta = false;
				
		//$this->log('Ejecucion Datos_Benef - ' . $tipo_rif . $nro_rif, LOG_DEBUG);
		
		$result = @mssql_query(
			"SELECT * FROM Datos_Benef('".stripslashes($tipo_rif.$nro_rif)."')", 
			$this->link
		);
		
		if($result===FALSE) {
			$this->log('Error al tratar de buscar los beneficiarios de la empresa '.$tipo_rif.$nro_rif, LOG_ERROR);
		}
		else {
			
			$respuesta= true;
			
			$afiliados = array();
			
			// Verifica si encontro un registro
			if (@mssql_num_rows($result)){
				
				// Recorre el resultado obtenido y almacena en base de datos
				while ($row = @mssql_fetch_assoc($result)){
					$row = array_map('trim', $row);
					
					// verifica si se repite el afiliado en un mismo tipo de pago
					
					$flag_continuar = true;
					
					if(count($afiliados)){
						foreach ($afiliados as $a){
							$temp_tipo = $row['TYPE']==1 ? 'n' : 'p';
							if( 
								$a['tipo']==$temp_tipo && 
								$a['tipo_cedula']==substr($row['BENE_ADD1'], 0, 1) && 
								$a['cedula']==encriptar(ltrim(substr($row['BENE_ADD1'],1),0))
							){
								$flag_continuar = false;
								break;
							}
						}
					}
					
					if(!$flag_continuar) continue;
					
					// fin verificacion
					
					$afiliados[] = array(
						'tipo_carga'=>'a',
						'tipo' => $row['TYPE']==1 ? 'n' : 'p',
						'tipo_rif' => $tipo_rif,
						'rif' => encriptar($nro_rif),
						'nombre' => encriptar($row['BENE_NAME']),
						'tipo_cedula' => substr($row['BENE_ADD1'], 0, 1),
						'cedula' => encriptar(ltrim(substr($row['BENE_ADD1'],1),0)),
						'numero_cuenta' => encriptar($row['CREDITACC']),
						'email' => encriptar($row['BENE_EMAIL']),
						'celular' => encriptar(str_pad($row['BENE_CELLPHONE'], 11, "0", STR_PAD_LEFT)),
					);
				}
				
				if( !$this->AfiliacionCash->saveAll($afiliados, array('validate' => false)) ){
					$this->log('No se logro guardar los beneficiarios ' . $tipo_rif.$nro_rif, LOG_ERROR);
					$this->log($this->AfiliacionCash->getDataSource()->error, LOG_ERROR);
					// $respuesta = false;
				}
			}
		}
		
		$this->desconectar();
		
		return $respuesta;
	}
	
	
	/**
	 * busca y guarda el historial de las transacciones de la empresa
	 * 
	 * @param string $tipo_rif Tipo de RIF de la empresa
	 * @param string $nro_rif Número de RIF de la empresa
	 * @param string $ibuser Usuario que identifica en el IB
	 * @param string $idUsuario ID del registro del usuario
	 * @return boolean
	 */
	function guardaHistorial($tipo_rif, $nro_rif, $ibuser, $idUsuario){
		
		// Limite de tiempo
		set_time_limit(0);
		
		
		if(!$this->Session->check('login.juridico')) return false;
		
		if(empty($tipo_rif) || empty($nro_rif) || empty($ibuser) || empty($idUsuario)) return false;
			
		if(!$this->conectar()) return false;
		
		$respuesta = false;
		
		//$this->log('Ejecucion Hist_Transf - ' . $tipo_rif . $nro_rif . ' - ' . $ibuser . ' - ' . $idUsuario, LOG_DEBUG);
		
		$result = @mssql_query(
			"SELECT * FROM Hist_Transf('".stripslashes($tipo_rif.$nro_rif)."','".date('d/m/Y')."')",
			$this->link
		);
		
		if($result===FALSE) {
			$this->log('Error al tratar de obtener el historico de la empresa '.$tipo_rif.$nro_rif, LOG_ERROR);
		}
		else {
			
			$respuesta = true;
				
			// Verifica si encontro un registro
			if (@mssql_num_rows($result)){
				
				// Modelo de bitacora
				$this->Bitacora = ClassRegistry::init('Bitacora');
				
				$bitacora = array();
				
				// Recorre el resultado obtenido y almacena en base de datos
				while ($row = @mssql_fetch_assoc($result)){
					
					$row = array_map('trim', $row);
					
					// verifica que exista los datos importantes
					// y que fueron realizadas por el usuario
					if(
						empty($row['DEBITACC']) ||
						empty($row['CREDITACC']) ||
						strtolower($row['OUSER']) != desencriptar($ibuser)
					){
						continue;
					}
					
					// Cuenta a debitar
					$cuenta_completada = false;
					if( strlen($row['DEBITACC'])==10 ){
						
						// invoca al servicio que completa el numero
						$res = $this->Activo->completaCuenta($row['DEBITACC']);

						// Verifica si trae la cuenta
						if( $res && !isset($res['EDL890101Return']['error']) ){
							$cuenta_debitar = encriptar(str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT)); 
							$cuenta_completada = true;
						}
					}
					if(!$cuenta_completada){
						$cuenta_debitar = encriptar($row['DEBITACC']);
					}
					
					
					// Cuenta a creditar
					$cuenta_completada = false;
					if( strlen($row['CREDITACC'])==10 ){
						
						// invoca al servicio que completa el numero
						$res = $this->Activo->completaCuenta($row['CREDITACC']);

						// Verifica si trae la cuenta
						if( $res && !isset($res['EDL890101Return']['error']) ){
							$cuenta_creditar = encriptar(str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT)); 
							$cuenta_completada = true;
						}
					}
					if(!$cuenta_completada){
						$cuenta_creditar = encriptar($row['CREDITACC']);
					}
					
					
					// Tipo de transaccion
					if(!empty($row['BENE_MEMO1'])){
						switch (substr($row['BENE_MEMO1'], 0, 4)){
							case '0220':
							case '0227':
								$transaccion = 8;
								break;
							default:
								$transaccion = 7;
								break;
						}
					}
					else{
						$transaccion = 7;
					}
					
					
					// Arreglo con la data del historial
					$bitacora[] = array(
						'usuario_id'	=> $idUsuario,
						'transaccion_id'=> $transaccion,
						'fecha_hora'	=> preg_replace('/^(\d{1,2})\/(\d{1,2})\/(\d{2})$/i','20$3-$2-$1',$row['ODATE']),
						'cuenta_origen'	=> $cuenta_debitar,
						'cuenta_destino'=> $cuenta_creditar,
						'monto'			=> $row['AMOUNT'],
						'descripcion'	=> (!empty($row['BENE_MEMO2'])) ? encriptar($row['BENE_MEMO2']) : NULL,
						'ip'			=> (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? encriptar($_SERVER['HTTP_X_FORWARDED_FOR']) : encriptar($_SERVER['REMOTE_ADDR']),
						'user_agent'	=> encriptar($_SERVER['HTTP_USER_AGENT']),
					);
					
				}
				
				if(count($bitacora)){
					if( !$this->Bitacora->saveAll($bitacora, array('validate' => false)) ){
						$this->log('No se logro guardar las transacciones del historico ' . $tipo_rif.$nro_rif, LOG_ERROR);
						$this->log($this->Bitacora->getDataSource()->error, LOG_ERROR);
						// $respuesta = false;
					}
				}
				
			}
		}
		
		$this->desconectar();
		
		return $respuesta;
	}
	
	
	/**
	 * Transacciones pendientes por aprobar (transferencias)
	 * 
	 * @param string $tipo_rif Tipo de RIF de la empresa
	 * @param string $nro_rif Número de RIF de la empresa
	 * @param string $ibuser Usuario que identifica en el IB
	 * @param string $idUsuario ID del registro del usuario
	 * @return boolean
	 */
	function pendientesPorAprobar($tipo_rif, $nro_rif, $ibuser, $idUsuario){
		
		// Limite de tiempo
		set_time_limit(0);
		
		
		if(!$this->Session->check('login.juridico')) return false;
		
		if(empty($tipo_rif) || empty($nro_rif) || empty($ibuser) || empty($idUsuario)) return false;
			
		if(!$this->conectar()) return false;
		
		$respuesta = false;
		
		// Modelo de transacciones pendientes
		$this->TransaccionPendiente = ClassRegistry::init('TransaccionPendiente');
		
		// Verifica si existen ya transacciones pendientes en la tabla
		if($this->TransaccionPendiente->find(
			'count',
			array(
				'conditions' => array(
					'TransaccionPendiente.tipo_rif' => $tipo_rif,
					'TransaccionPendiente.rif' => encriptar($nro_rif),
					'TransaccionPendiente.tipo' => array('tpo','tto'),
				)
			)
		)){
			return true;
		}
		
		//$this->log('Ejecucion Pend_por_Aprobar - ' . $tipo_rif . $nro_rif . ' - ' . $ibuser . ' - ' . $idUsuario . ' - ' .date('d/m/Y', strtotime('-1 month')), LOG_DEBUG);
		
		$result = @mssql_query(
			"SELECT * FROM Pend_por_Aprobar('".stripslashes($tipo_rif.$nro_rif)."','".date('d/m/Y', strtotime('-1 month'))."')",
			$this->link
		);
		
		if($result===FALSE) {
			$this->log('Error al tratar de obtener las transacciones pendientes '.$tipo_rif.$nro_rif, LOG_ERROR);
		}
		else {
			
			$respuesta = true;
			
			// Verifica si encontro un registro
			if (@mssql_num_rows($result)){
				
				// Modelo para la aprobacion
				$this->AprobacionTransaccion = ClassRegistry::init('AprobacionTransaccion');
				
				// Recorre el resultado obtenido y almacena en base de datos
				while ($row = @mssql_fetch_assoc($result)){
					
					$row = array_map('trim', $row);
					
					// verifica que exista los datos importantes
					// y que fueron realizadas por el usuario
					if(
						empty($row['DEBITACC']) || 
						empty($row['CREDITACC']) || 
						empty($row['OUSER'])
					){
						continue;
					}
					
					// Tipo de transaccion
					$tipo = 'ttm';
					if(!empty($row['BENE_MEMO1'])){
						switch (substr($row['BENE_MEMO1'], 0, 4)){
							case '0220':
								$tipo = 'tpo';
								break;
							case '0227':
								$tipo = 'tto';
								break;
						}
					}
					
					
					// Cuenta a debitar
					$cuenta_completada = false;
					if( strlen($row['DEBITACC'])==10 ){
						
						// invoca al servicio que completa el numero
						$res = $this->Activo->completaCuenta($row['DEBITACC']);

						// Verifica si trae la cuenta
						if( $res && !isset($res['EDL890101Return']['error']) ){
							$cuenta_debitar = encriptar(str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT)); 
							$cuenta_completada = true;
						}
					}
					if(!$cuenta_completada){
						$cuenta_debitar = encriptar($row['DEBITACC']);
					}
					
					
					// Cuenta a creditar
					$cuenta_completada = false;
					if( strlen($row['CREDITACC'])==10 ){
						
						// invoca al servicio que completa el numero
						$res = $this->Activo->completaCuenta($row['CREDITACC']);

						// Verifica si trae la cuenta
						if( $res && !isset($res['EDL890101Return']['error']) ){
							$cuenta_creditar = encriptar(str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT)); 
							$cuenta_completada = true;
						}
					}
					if(!$cuenta_completada){
						$cuenta_creditar = encriptar($row['CREDITACC']);
					}
					
					
					// Para identificar el ID del usuario
					if(strtolower($row['OUSER']) != desencriptar($ibuser)){
						$usuario_id = $this->obtenerUsuario($tipo_rif, $nro_rif, strtolower($row['OUSER']));
					}
					else{
						$usuario_id = $idUsuario;
					}
					
					if(!$usuario_id){
						$respuesta = false;
						break;
					}
					
					$numero_aprobaciones = ($row['OUSER']==$row['VUSER']) ? 1 : 2;
					
					// Arreglo para almacenar las transacciones
					$transaccion = array(
						'TransaccionPendiente'=> array(
							'usuario_id'		=> $usuario_id,
							'tipo'				=> $tipo,
							'estatus'			=> 'pa',
							'fecha_hora_registro' => preg_replace('/^(\d{2})\/(\d{2})\/(\d{2})$/','20$3-$2-$1',$row['ODATE']),
							'monto'				=> $row['AMOUNT'],
							'aprobaciones_requeridas' => $numero_aprobaciones,
							'tipo_rif'			=> $tipo_rif,
							'rif'				=> encriptar($nro_rif),
							'cuenta_origen'		=> $cuenta_debitar,
							'cuenta_destino'	=> $cuenta_creditar,
							'descripcion'		=> encriptar($row['BENE_MEMO2']),
							'tipo_id_beneficiario' => substr($row['BENE_ADD1'], 0, 1),
							'id_beneficiario' => encriptar(ltrim(substr($row['BENE_ADD1'], 1), '0')),
							'nombre_beneficiario' => encriptar($row['BENE_NAME']),
							'email_beneficiario' => !empty($row['BENE_EMAIL']) ? encriptar($row['BENE_EMAIL']) : NULL,
						)
					);
					
					
					// Almacena la transaccion
					$res = $this->TransaccionPendiente->save($transaccion, false);
					
					if($res){
						
						// Verifica si fue validada la transaccion
						// si el OUSER es diferente al VUSER y el VUSER no esta vacio, significa que tiene una aprobacion
						if( !empty($row['VUSER']) && ($row['OUSER'] != $row['VUSER']) ){
							
							// Para identificar el ID del usuario
							if(strtolower($row['VUSER']) != desencriptar($ibuser)){
								$usuario_id = $this->obtenerUsuario($tipo_rif, $nro_rif, strtolower($row['VUSER']));
							}
							else{
								$usuario_id = $idUsuario;
							}
							
							// Almacena el primer aprobador
							$this->AprobacionTransaccion->save(
								array(
									'AprobacionTransaccion' => array(
										'transaccion_id' => $this->TransaccionPendiente->id,
										'usuario_id' => $usuario_id,
										'fecha_hora' => preg_replace('/^(\d{2})\/(\d{2})\/(\d{2})$/','20$3-$2-$1',$row['VDATE'])
									)
								),
								false
							);

							unset($this->AprobacionTransaccion->id);
							
							
							// Registro en bitacora de la aprobacion
							if(!empty($row['VDATE'])){
								$this->Bitacora = ClassRegistry::init('Bitacora');
								$this->Bitacora->save(
									array('Bitacora'=>
										array(
											'usuario_id'	=> $usuario_id,
											'transaccion_id'=> 54,
											'fecha_hora'	=> str_pad(substr($row['VDATE'], 6, 2), 4, '20', STR_PAD_LEFT) . '-' . substr($row['VDATE'], 3, 2) . '-' . substr($row['VDATE'], 0, 2),
											'monto'			=> $row['TOT_AMT'],
											'referencia'	=> encriptar($this->TransaccionPendiente->id),
											'ip'			=> (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? encriptar($_SERVER['HTTP_X_FORWARDED_FOR']) : encriptar($_SERVER['REMOTE_ADDR']),
											'user_agent'	=> encriptar($_SERVER['HTTP_USER_AGENT'])
										)
									),false
								);
								unset($this->Bitacora->id);
							}
						}
						
						unset($this->TransaccionPendiente->id);
					}
					else{
						$this->log('No se logro guardar los pagos por aprobar ' . $tipo_rif.$nro_rif, LOG_ERROR);
						$this->log($this->TransaccionPendiente->getDataSource()->error, LOG_ERROR);
						// $respuesta = false;
						break;
					}
				}
			}
		}
		
		$this->desconectar();
		
		return $respuesta;
	}
	
	
	/**
	 * Busca los beneficiarios de un pago segun la referencia.
	 * 
	 * @access private
	 * @param string $tipo_rif Tipo de RIF de la empresa
	 * @param string $nro_rif Número de RIF de la empresa
	 * @param char $tipo_pago n: Nomina, p: Proveedor
	 * @param string $referencia Referencia del pago, campo DCIBS_REF
	 * @return array
	 */
	private function beneficiariosPagos($tipo_rif, $nro_rif, $tipo_pago, $referencia){
		
		if(!$this->Session->check('login.juridico')) return false;
		
		if(empty($tipo_rif) || empty($nro_rif) || empty($tipo_pago) || empty($referencia) || !in_array($tipo_pago, array('n','p'))) return false;
			
		if(!$this->link) return false;
		
		// Verifica según el tipo la función que se va invocar
		if($tipo_pago=='n'){
			$nombre_funcion = 'Pend_Nomi_Det';
		}
		else{
			$nombre_funcion = 'Pend_provee_Det';
		}
				
		$data = array();
		
		//$this->log('Ejecucion '. $nombre_funcion . ' - ' . $tipo_rif . $nro_rif . ' - ' . $referencia, LOG_DEBUG);
		
		$result = @mssql_query(
			"SELECT * FROM ".$nombre_funcion."('".stripslashes($tipo_rif.$nro_rif)."', '".$referencia."')", 
			$this->link
		);
		
		if($result===FALSE) {
			$this->log('Error al tratar de obtener los pagos de nomina de la empresa '.$tipo_rif.$nro_rif, LOG_ERROR);
		}
		else {
			
			// Verifica si encontro un registro
			if (@mssql_num_rows($result)){
				
				while ($row = @mssql_fetch_assoc($result)){
					
					$row = array_map('trim', $row);
					
					$data[] = array(
						'TransaccionBeneficiario'=>array(
							'tipo_carga'=>'a',
							'tipo' => $tipo_pago,
							'tipo_rif' => $tipo_rif,
							'rif' => encriptar($nro_rif),
							'nombre' => encriptar($row['BENE_NAME']),
							'tipo_cedula' => substr($row['BENE_ADD1'], 0, 1),
							'cedula' => encriptar(ltrim(substr($row['BENE_ADD1'],1),0)),
							'numero_cuenta' => encriptar($row['CREDITACC']),
							'email' => encriptar($row['INT_BANK_ADD1']),
							'celular' => encriptar(str_pad($row['INT_BANK_ADD2'], 11, "0", STR_PAD_LEFT)),
							'monto' => $row['AMOUNT'],
							'referencia' => $row['BENE_NUM'],
							'concepto' => ( !empty($row['BENE_MEMO2']) ) ? encriptar($row['BENE_MEMO2']) : NULL,
						)
					);
					
				}
			}
		}
		
		return $data;
	}


	/**
	 * Para almacenar los pagos pendientes de un usuario en especifico
	 * 
	 * @param string $tipo_rif Tipo de RIF de la empresa
	 * @param string $nro_rif Número de RIF de la empresa
	 * @param string $ibuser Usuario que identifica en el IB
	 * @param string $idUsuario ID del registro del usuario
	 * @param char $tipo_pago n: Nomina, p: Proveedor
	 * @return boolean
	 */
	function pagosPendientes($tipo_rif, $nro_rif, $ibuser, $idUsuario, $tipo_pago){
		
		// Limite de tiempo
		set_time_limit(0);
		
		
		if(!$this->Session->check('login.juridico')) return false;
		
		if(empty($tipo_rif) || empty($nro_rif) || empty($ibuser) || empty($idUsuario) || empty($tipo_pago) || !in_array($tipo_pago, array('n','p'))) return false;
		
		if(!$this->conectar()) return false;
		
		$respuesta = false;
		
		// Modelo de transacciones pendientes
		$this->TransaccionPendiente = ClassRegistry::init('TransaccionPendiente');
		
		// Verifica si existen ya transacciones pendientes en la tabla
		if($this->TransaccionPendiente->find(
			'count',
			array(
				'conditions' => array(
					'TransaccionPendiente.tipo_rif' => $tipo_rif,
					'TransaccionPendiente.rif' => encriptar($nro_rif),
					'TransaccionPendiente.tipo' => $tipo_pago == 'n' ? 'nxa' : 'vxa',
				)
			)
		)){
			return true;
		}
		
		// Verifica según el tipo la función que se va invocar
		// y el tipo de pago cuando se va almacenar
		if($tipo_pago=='n'){
			$nombre_funcion = 'Pend_Nomi_Enc';
			$tipo_pago_pendiente = 'nxa';
			$tipo_titulo = 'Pagos de Nomina';
		}
		else{
			$nombre_funcion = 'Pend_provee_Enc';
			$tipo_pago_pendiente = 'vxa';
			$tipo_titulo = 'Pagos de Proveedores';
		}

		//$this->log('Ejecucion '. $nombre_funcion . ' - ' . $tipo_rif . $nro_rif . ' - ' . $ibuser . ' - ' . $idUsuario  . ' - ' . date('d', strtotime('-1 month')).'/'.date('m', strtotime('-1 month')).'/'.date('y', strtotime('-1 month')), LOG_DEBUG);
		
		$result = @mssql_query(
			"SELECT * FROM ".$nombre_funcion."('".stripslashes($tipo_rif.$nro_rif)."', '".desencriptar($ibuser)."', '".date('d', strtotime('-1 month'))."', '".date('m', strtotime('-1 month'))."', '".date('y', strtotime('-1 month'))."')",
			$this->link
		);
		
		if($result===FALSE) {
			$this->log('Error al tratar de obtener los '. $tipo_titulo .' de la empresa '.$tipo_rif.$nro_rif, LOG_ERROR);
		}
		else {
			// Devuelve verdadero en caso de encontrar o no transacciones de pago
			$respuesta = true;
			
			// Verifica si encontro un registro
			if (@mssql_num_rows($result)){
				
				// Modelo para el beneficiario y la aprobacion
				$this->TransaccionBeneficiario = ClassRegistry::init('TransaccionBeneficiario');
				$this->AprobacionTransaccion = ClassRegistry::init('AprobacionTransaccion');
				
				// Recorre el resultado obtenido y almacena en base de datos
				while ($row = @mssql_fetch_assoc($result)){
					
					$row = array_map('trim', $row);
					
					// verifica que exista los datos importantes
					// y que fueron realizadas por el usuario
					if(
						empty($row['DEBITACC']) ||
						empty($row['LOTE']) ||
						empty($row['ODATE']) ||
						empty($row['OUSER']) 
					){
						continue;
					}
					
					// Cuenta a debitar
					$cuenta_completada = false;
					if( strlen($row['DEBITACC'])==10 ){
						
						// invoca al servicio que completa el numero
						$res = $this->Activo->completaCuenta($row['DEBITACC']);

						// Verifica si trae la cuenta
						if( $res && !isset($res['EDL890101Return']['error']) ){
							$cuenta_debitar = encriptar(str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT)); 
							$cuenta_completada = true;
						}
					}
					if(!$cuenta_completada){
						$cuenta_debitar = encriptar($row['DEBITACC']);
					}
					
					// Verifica el estatus de la transaccion
					switch ($row['ACT']){
						case 'D':
							$estatus = 'el';
							break;
						
						default:
							$estatus = 'pa';
							break;
					}
					
					
					// Para identificar el ID del usuario
					if(strtolower($row['OUSER']) != desencriptar($ibuser)){
						$usuario_id = $this->obtenerUsuario($tipo_rif, $nro_rif, strtolower($row['OUSER']));
					}
					else{
						$usuario_id = $idUsuario;
					}
					
					if(!$usuario_id){
						$respuesta = false;
						break;
					}
					
					$numero_aprobaciones = ($row['OUSER']==$row['VUSER']) ? 1 : 2;
					
					$tipo_carga = ($row['NUM_OF_RECORDS']>10) ? 'a' : 'm';
					
					// Arreglo con la informacion de la transaccion
					$transaccion = array(
						'TransaccionPendiente'=> 
						array(
							'usuario_id'		=> $usuario_id,
							'tipo'				=> $tipo_pago_pendiente,
							'estatus'			=> $estatus,
							'lote'				=> $row['LOTE'],
							'fecha_hora_registro' => str_pad(substr($row['ODATE'], 6, 2), 4, '20', STR_PAD_LEFT) . '-' . substr($row['ODATE'], 3, 2) . '-' . substr($row['ODATE'], 0, 2),
							'fecha_hora_valor'	=> str_pad(substr($row['FECHA_PROCESO'], 6, 2), 4, '20', STR_PAD_LEFT) . '-' . substr($row['FECHA_PROCESO'], 3, 2) . '-' . substr($row['FECHA_PROCESO'], 0, 2),
							'monto'				=> $row['TOT_AMT'],
							'aprobaciones_requeridas' => $numero_aprobaciones,
							'tipo_rif'			=> $tipo_rif,
							'rif'				=> encriptar($nro_rif),
							'cuenta_origen'		=> $cuenta_debitar,
							'descripcion'		=> encriptar($row['DESCRIPTION']),
							'tipo_carga'		=> $tipo_carga,
						)
					);
					
					$beneficiarios = $this->beneficiariosPagos($tipo_rif, $nro_rif, $tipo_pago, $row['DCIBS_REF']);
					
					if(!count($beneficiarios)) continue;
			
					// Almacena la transaccion
					$res = $this->TransaccionPendiente->save($transaccion, false);
					
					if($res){
						// Almacena los beneficiarios
						foreach ($beneficiarios as $b){
							$b['TransaccionBeneficiario']['transaccion_id'] = $this->TransaccionPendiente->id;
							$this->TransaccionBeneficiario->save($b, false);
							unset($this->TransaccionBeneficiario->id);
						}
						
						// Verifica si fue validada la transaccion
						// si el OUSER es diferente al VUSER y el VUSER no esta vacio, significa que tiene una aprobacion
						if( !empty($row['VUSER']) && ($row['OUSER'] != $row['VUSER']) ){
							
							// Para identificar el ID del usuario
							if(strtolower($row['VUSER']) != desencriptar($ibuser)){
								$usuario_id = $this->obtenerUsuario($tipo_rif, $nro_rif, strtolower($row['VUSER']));
							}
							else{
								$usuario_id = $idUsuario;
							}

							// Almacena el primer aprobador
							$this->AprobacionTransaccion->save(
								array(
									'AprobacionTransaccion' => array(
										'transaccion_id' => $this->TransaccionPendiente->id,
										'usuario_id' => $usuario_id,
										'fecha_hora' => str_pad(substr($row['VDATE'], 6, 2), 4, '20', STR_PAD_LEFT) . '-' . substr($row['VDATE'], 3, 2) . '-' . substr($row['VDATE'], 0, 2)
									)
								),
								false
							);

							unset($this->AprobacionTransaccion->id);
							
							
							// Registro en bitacora de la aprobacion
							if(!empty($row['VDATE'])){
								$this->Bitacora = ClassRegistry::init('Bitacora');
								$this->Bitacora->save(
									array('Bitacora'=>
										array(
											'usuario_id'	=> $usuario_id,
											'transaccion_id'=> 54,
											'fecha_hora'	=> str_pad(substr($row['VDATE'], 6, 2), 4, '20', STR_PAD_LEFT) . '-' . substr($row['VDATE'], 3, 2) . '-' . substr($row['VDATE'], 0, 2),
											'monto'			=> $row['TOT_AMT'],
											'referencia'	=> encriptar($this->TransaccionPendiente->id),
											'ip'			=> (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? encriptar($_SERVER['HTTP_X_FORWARDED_FOR']) : encriptar($_SERVER['REMOTE_ADDR']),
											'user_agent'	=> encriptar($_SERVER['HTTP_USER_AGENT'])
										)
									),false
								);
								unset($this->Bitacora->id);
							}
							
						}	
							
						unset($this->TransaccionPendiente->id);
					}
					else{
						$this->log('No se logro guardar los pagos pendientes ' . $tipo_rif.$nro_rif, LOG_ERROR);
						$this->log($this->TransaccionPendiente->getDataSource()->error, LOG_ERROR);
						// $respuesta = false;
						break;
					}
				}
			}
		}
		
		$this->desconectar();
		
		return $respuesta;
	}
	
	
	/**
	 * Método que devuelve el ID del usuario. Verifica si ya existe el usuario 
	 * en la base de datos, en caso de no existir lo crea.
	 * 
	 * @access private
	 * @param string $tipo_rif Tipo de RIF de la empresa
	 * @param string $nro_rif Número de RIF de la empresa
	 * @param string $ibuser Usuario que identifica en el IB
	 * @return int
	 */
	private function obtenerUsuario($tipo_rif, $nro_rif, $ibuser){
		$usuario_id = '';
		
		// Modelo de usuario
		$this->Usuario = ClassRegistry::init('Usuario');
		
		$usuario = $this->Usuario->find(
			'first',
			array(
				'fields'=> array('id'),
				'conditions'=>array(
					'Usuario.tipo_rif'=> $tipo_rif,
					'Usuario.rif'=> encriptar($nro_rif),
					'Usuario.ibuser'=> encriptar($ibuser)
				)
			)
		);
		
		// Si existe toma el ID del registro en caso contrario guarda en BD el registro
		if($usuario){
			$usuario_id = $usuario['Usuario']['id'];
		}
		else{
			
			// Verifica si no hubo un error al momento de crear el usuario
			if(
				$this->Usuario->save( array(
					'Usuario' => array(
						'ibuser' => encriptar($ibuser),
						'tipo_rif'=> $tipo_rif,
						'rif'=> encriptar($nro_rif),
						'tipo_cedula'=> 'V',
						'cedula'=> encriptar(date('mdhis')),
						'perfil_ib_id'=> 2
					)
				) , false)
			){
				$usuario_id = $this->Usuario->id;
			}
			else{
				$this->log("Error guardando usuario en BD " . $tipo_rif.$nro_rif . " - " . $ibuser , LOG_ERROR);
				$this->log($this->Usuario->getDataSource()->error, LOG_ERROR);
			}
		}
		
		return $usuario_id;
	}
	
	
	/**
	 * Alamacena en la base de datos los usuarios de la empresa.
	 * 
	 * @param char $tipo_rif
	 * @param string $nro_rif
	 * @param string $ibuser
	 * @return boolean
	 */
	function usuariosEmpresa($tipo_rif, $nro_rif, $ibuser){
		
		// Limite de tiempo
		set_time_limit(0);
		
		if(!$this->Session->check('login.juridico')) return false;
		
		if(empty($tipo_rif) || empty($nro_rif) || empty($ibuser)) return false;
		
		if(!$this->conectar()) return false;
		
		$respuesta = false;
		
		// Modelo de usuarios
		$this->Usuario = ClassRegistry::init('Usuario');
		
		// Verifica si ya fueron creados los usuarios
		if($this->Usuario->find(
			'count',
			array(
				'conditions' => array(
					'Usuario.tipo_rif' => $tipo_rif,
					'Usuario.rif' => encriptar($nro_rif),
					'Usuario.ibuser != ? '=> $ibuser,
					'Usuario.ibuser IS NOT NULL',
				)
			)
		)){
			return true;
		}
		unset($this->Usuario->id);
		
		//$this->log('Ejecucion Autenticate_rif - ' . $tipo_rif . $nro_rif . ' - ' . $ibuser, LOG_DEBUG);
		
		$result = @mssql_query(
			"SELECT * FROM Autenticate_rif('".stripslashes($tipo_rif.$nro_rif)."')",
			$this->link
		);
		
		if($result===FALSE) {
			$this->log('Error al tratar de obtener los usuarios de la empresa '.$tipo_rif.$nro_rif, LOG_ERROR);
		}
		else {
			// Devuelve verdadero en caso de encontrar o no transacciones de pago
			$respuesta = true;
			
			// Verifica si encontro un registro
			if (@mssql_num_rows($result)){
				$num = 1;
				
				$usuarios = array();
				
				// Recorre el resultado obtenido y almacena en base de datos
				while ($row = @mssql_fetch_assoc($result)){
					
					$row = array_map('trim', $row);
					
					// si es el del usuario que se esta registrando lo omite
					if( encriptar(strtolower($row['USERID'])) == $ibuser )	continue;
					
					// perfil del usuario
					switch ($row['PERFIL']){
						case 'OPERADOR':
							$rol_perfil = 3;
							break;
						case 'SUPERVISOR':
							$rol_perfil = 4;
							break;
						default:
							$rol_perfil = 5;
							break;
					}
					
					// Arreglo con la data de los usuarios
					$usuarios[] = array(
						'ibuser' => encriptar(strtolower($row['USERID'])),
						'tipo_rif'=> $tipo_rif,
						'rif'=> encriptar($nro_rif),
						'tipo_cedula'=> 'V',
						'cedula'=> encriptar($num.$rol_perfil.date('mdhis')),
						'nombre'=> encriptar($row['FIRSTNAME'].' '.$row['LASTNAME']),
						'cargo'=> encriptar($row['TITLE']),
						'email_principal'=>encriptar($row['EMAIL']),
						'perfil_ib_id'=> 2,
						'rol_juridico_id'=> $rol_perfil,
					);
					
					$num++;
				}
				
				if(!empty($usuarios)){
					// Almacena el la BD la informacion
					if( !$this->Usuario->saveAll($usuarios, array('validate' => false)) ){
						$this->log('No se logro guardar los usuarios de la empresa ' . $tipo_rif.$nro_rif, LOG_ERROR);
						$this->log($this->Usuario->getDataSource()->error, LOG_ERROR);
						// $respuesta = false;
					}
				}
			}
		}
		
		$this->desconectar();
		
		return $respuesta;
	}
	
	
	/**
	 * Actualiza la clave del usuario en el IB anterior
	 * 
	 * @param string $rif
	 * @param string $login
	 * @param string $pass
	 * @return boolean
	 */
	function cambiarContrasena($rif, $login, $pass){
		// Limite de tiempo
		set_time_limit(0);
		
		if(!$this->Session->check('login.juridico')) return false;

		if(empty($rif) || empty($login) || empty($pass)) return false;
		
		if(!$this->conectar()) return false;
		
		// Encripta la clave del usuario
		$encriptado = $this->Activo->invoca(
			'SHA1Encrypt',
			array(
				'req'=>array(
					'modificado'=> true,
					'texto'=> strtoupper(trim($pass)),
				)
			)
		);
		
		if(!isset($encriptado['SHA1EncryptReturn']['encriptado']) || empty($encriptado['SHA1EncryptReturn']['encriptado'])) return false;
		
		$result = @mssql_query(
			"UPDATE DC_USRPROFILE SET USERPASS='".$encriptado['SHA1EncryptReturn']['encriptado']."' WHERE entityid='".$rif."' and userid='".$login."'",
			$this->link
		);
		
		if(!$result){
			$this->log('Error al cambiar contraseña legacy de usuario '.$rif.' - '.$login.' '.$login, LOG_ERROR);
		}
		
		$this->desconectar();

		return $result;
	}
}
?>
