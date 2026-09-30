<?php
/**
 * Controlador de Suspensión de Cheques y Chequeras
 */

/**
 * Controlador de Suspensión de Cheques y Chequeras
 */
class ChequesController extends AppController {

	 /**
         *
         * @var array Modelos que usa el controlador 
         */
	var $uses = array('Usuario','Cheque','Bitacora','TransaccionJuridicoDetalle','TransaccionPendiente','TransaccionBeneficiario');

        /**
         *
         * @var array Componentes que utiliza este controlador
         */
	var $components = array('Activo', 'Session');

        /**
         *
         * @var array Ayudantes utilizados en las vistas de este controlador
         */
	var $helpers = array('Activo','Html','Session','Form','Text');

        /**
         * Accion que permite seleccionar el tipo de suspensión a realizar, suspensión de cheque, chequera o rango de cheque
         */
	function index(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(22)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');
		
		$cuentas = $this->Activo->buscarCuentasCorriente();

		$this->set(compact('cuentas'));

		if(isset($this->data['Cheque'])){
			$this->Cheque->set($this->data);

			if($this->Cheque->validates(array('fieldList'=>array('cuenta','tipo')))){
				$this->Session->write('cheques.index',$this->data);
				
				// Validación anti fraude
				$cuentas = $this->Activo->buscarCuentas();

				if(
					!in_array($this->data['Cheque']['cuenta'], array_keys($cuentas))
				){
					$this->redirect('/consultas/integral');
				}
				//  Fin validación anti fraude		

				switch($this->data['Cheque']['tipo']){
					case 'c':
						$this->redirect('cheque');
						break;
					case 'h':
						$this->redirect('chequera');
						break;
					case 'r':
						$this->redirect('rango_cheque');
						break;
				}
			}
		}
	}

        /**
         * Acción que permite especificar los datos de la suspensión de un cheque
         */
	function cheque(){
		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');

		$motivos = $this->Cheque->buscar_motivos();

		$this->set(compact('motivos'));

		if(isset($this->data['Cheque'])){
			$this->Cheque->set($this->data);

			if($this->Cheque->validates(array('fieldList'=>array('motivo','tipo_motivo','cheque')))){
				$estado = $this->Session->read('cheques.index');
				$estado['Cheque']['motivo']=$this->data['Cheque']['motivo'];
				$estado['Cheque']['tipo_motivo']=$this->data['Cheque']['tipo_motivo'];
				$estado['Cheque']['cheque']=$this->data['Cheque']['cheque'];

				$this->Session->write('cheques.index',$estado);

				$this->redirect('cheque_confirmacion');
			}
		} else if($this->Session->check('cheques.index')){
			$this->data = $this->Session->read('cheques.index');
		}
	}

        /**
         * Acción de confirmación de la suspensión de un cheque
         */
	function cheque_confirmacion(){
		if($this->Session->check('cheques.index')){	
			$this->data = $this->Session->read('cheques.index');
		} else {
			$this->redirect('index');
		}

		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');

		$motivos = $this->Cheque->buscar_motivos();
		
		$this->set(compact('motivos'));
	}

	/**
	 * Acción para el resultado de la suspensión de un cheque
	 */
	function cheque_resultado(){
		if($this->Session->check('cheques.index')){	
			$this->data = $this->Session->read('cheques.index');
			$motivos = $this->Cheque->buscar_motivos();

			$this->Session->delete('cheques.index');
		} else {
			$this->redirect('index');
		}

		if(isset($this->data['Cheque']['cheque'])){
			$this->data['Cheque']['chqInicial'] = $this->data['Cheque']['cheque'];
			$this->data['Cheque']['chqFinal'] = '';
			$flagCheques = false;
		}else{
			$this->data['Cheque']['chqInicial'] = $this->data['Cheque']['chqInicial'];
			$this->data['Cheque']['chqFinal'] = $this->data['Cheque']['chqFinal'];
			$flagCheques = true;
		}
		
		if(empty($this->data['Cheque']['motivo'])) { 
			$motivo = $motivos[$this->data['Cheque']['tipo_motivo']];
		} else {
			$motivo = $this->data['Cheque']['motivo'];
		}

		$usuario = $this->Session->read('usuarios.datos');
		
		$error = 0;
		$pendiente = false;
		$referencia = null;

		// si es un master unico o natural ejecuta la operacion
		if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id']==2){
		
			$error = -1;
			
			$res = $this->Activo->invoca(
				'STOPDSJV',
				array(
					'req'=>array(
						'STOSTPACC'=> substr($this->data['Cheque']['cuenta'],10),
						'STOSTPFCK'=> $this->data['Cheque']['chqInicial'],
						'STOSTPRMK'=> $motivo,
						'STOSTPTCK'=> $this->data['Cheque']['chqFinal'],
						'STOSTPFLG'=> 'S'
					)
				)
			);
			
			if(isset($res['STOPDSJVReturn']['error']['ERRNUM'])){
				$error = intval($res['STOPDSJVReturn']['error']['ERRNUM']);
				$error = ($error == 0) ? $error : intval($res['STOPDSJVReturn']['error']['ERRNUM']);
				$descripcion = $res['STOPDSJVReturn']['error']['ERNU01'] . ' - ' . $res['STOPDSJVReturn']['error']['ERDS01'];
				
				$this->log($descripcion . ' - ID: ' . $usuario['id'], LOG_ERR);	
			}
			elseif(isset($res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'])){
				$arrData = isset($res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV']) ? $res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'] : array();
				
				normaliza($arrData);
				foreach($arrData as $i){
					$referencia = $i['STOSTPNRE'];
				}
				$error = 0;
			}
			
			$log_descripcion = 'Suspensión de cheque ' . $this->data['Cheque']['chqInicial'];
			$log_descripcion .= (!empty($this->data['Cheque']['chqFinal'])) ? ' - ' . $this->data['Cheque']['chqFinal'] : ' ';
				
			$this->Bitacora->registraBitacora(
				$usuario['id'],
				22,
				$this->data['Cheque']['cuenta'],
				NULL,
				NULL,
				$referencia,
				$log_descripcion,
				($error != 0)
			);

		}
		else if(!empty($usuario['rol_juridico_id']) && ($usuario['rol_juridico_id']==3 || $usuario['rol_juridico_id']==5)){ 
			// Si es un usuario operador u operador/aprobador monta la operacion como pendiente

			// Verifica la cantidad de aprobaciones
			$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener($usuario['id'], 22);
			if(!$transaccion_detalle) {
				$error = -1;
				$this->log( 'Usuario juridico no autorizado - ID: ' . $usuario['id'], LOG_ERR);	
			}
			else{
				
				// verifica si es una suspension por rango de cheque o un cheque individual
				if($flagCheques){
					$cheque = $this->data['Cheque']['chqInicial'].'|'.$this->data['Cheque']['chqFinal'];
					$tipo = 'csr';
				}
				else{
					$cheque = $this->data['Cheque']['chqInicial'];
					$tipo = 'csi';
				}

				$res = $this->TransaccionPendiente->save(
					array(
						'TransaccionPendiente'=>array(
							'usuario_id'=>$usuario['id'],
							'tipo'=>$tipo,
							'estatus'=>'pa',
							'fecha_hora_registro'=>date('Y-m-d H:i:s'),
							'aprobaciones_requeridas'=>$transaccion_detalle['aprobaciones'],
							'tipo_rif'=>$usuario['tipo_rif'],
							'rif'=>$usuario['rif'],
							'cuenta_origen'=>encriptar($this->data['Cheque']['cuenta']),
							'cuenta_destino'=>encriptar($cheque),
							'descripcion'=> encriptar($motivo),
							'monto'=> 0
						)
					),
					false
				);

				if($res){
					$error = 0;
					$referencia = $this->TransaccionPendiente->id;
				} else {
					$error = -1;
				}

				$pendiente = true;
			}
		}
		else{
			$error = -1;
		}
		
		$this->set(compact('error','motivos','referencia','flagCheques','pendiente'));	
			
		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');		

		$this->render('resultado');
	}
	
	/**
	 * Acción para la suspensión de una chequera
	 */
	function chequera(){
	
		if(!$this->Session->check('cheques.index')){
			$this->redirect('index');
		}
	
		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');

		$motivos = $this->Cheque->buscar_motivos();

		$this->set(compact('motivos'));

		if(isset($this->data['Cheque'])){
			$this->Cheque->set($this->data);

			if($this->Cheque->validates(array('fieldList'=>array('chequeras','motivo','tipo_motivo')))){
				$estado = $this->Session->read('cheques.index');
				$estado['Cheque']['motivo']=$this->data['Cheque']['motivo'];
				$estado['Cheque']['tipo_motivo']=$this->data['Cheque']['tipo_motivo'];
				$estado['Cheque']['chequeras']=$this->data['Cheque']['chequeras'];

				$this->Session->write('cheques.index',$estado);

				$this->redirect('chequera_confirmacion');
			}
		}
		else{
			$this->data = $this->Session->read('cheques.index');
		}
		
		//INTEGRACIÓN
		
		$usuario = $this->Session->read('usuarios.datos');
		
		$cuenta	= substr($this->data['Cheque']['cuenta'] ,10);

		$arrData = array();
		
		
		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
	
		$res = $this->Activo->invoca(
			'EDL890001',
			array(
				'req'=>array(
					'e01USERID'=>str_pad($identificacion,10,'0', STR_PAD_LEFT),
					'e01CHMACC'=>str_pad($cuenta,12,'0', STR_PAD_LEFT)
				)
			)
		);
		
		$disChe = array();

		if($res){	
			if(!empty($res['EDL890001Return']['EDL890001']['EDL890001'])){
				$arrData = $res['EDL890001Return']['EDL890001']['EDL890001'];
				normaliza($arrData);
			}

			foreach($arrData as $i){
				$primerCheque = $i['e01CHMICK'];
				$ultimoCheque = $i['e01CHMFCK'];
				$cantidadCheques = $i['e01CHMNTC'];
				$numeroChequera = $i['e01CHMNCB'];
				$cantCheqDispChequera = $i['e01CHMFCK'] - $i['e01CHMICK'] + 1;	
				
				for($j=1; $j <= $cantCheqDispChequera; $j++){
					$e = 'e01CHMS'.str_pad($j,2,'0',STR_PAD_LEFT);
					$v = 'e01CHMN'.str_pad($j,2,'0',STR_PAD_LEFT);
			
					if($i[$e] == 'D'){	
						$disChe[]= $i[$v];	
					}
				}
			}
		}

		$this->set(compact('res','arrData','primerCheque','ultimoCheque','disChe','cantidadCheques'));
		
	}

	/**
	 * Acción de confirmación de la suspensión de una chequera
	 */
	function chequera_confirmacion(){
		if($this->Session->check('cheques.index')){
			$this->data = $this->Session->read('cheques.index');
		} else {
			$this->redirect('index');
		}

		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');

		$motivos = $this->Cheque->buscar_motivos();

		$this->set(compact('motivos'));
	}

	/**
	 * Acción para el resultado de la suspensión de una chequera
	 */
	function chequera_resultado(){
		if($this->Session->check('cheques.index')){	
			$this->data = $this->Session->read('cheques.index');
			$motivos = $this->Cheque->buscar_motivos();

			$this->Session->delete('cheques.index');
		} else {
			$this->redirect('index');
		}
		
		if(empty($this->data['Cheque']['motivo'])) { 
			$motivo = $motivos[$this->data['Cheque']['tipo_motivo']];
		} else {
			$motivo = $this->data['Cheque']['motivo'];
		}

		$usuario = $this->Session->read('usuarios.datos');
		$cuenta	= substr($this->data['Cheque']['cuenta'] ,10);
		$arrData = array();

		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
		
		
		// busca las chequeras del usuario y de la cuenta
		$res = $this->Activo->invoca(
			'EDL890001',
			array(
				'req'=>array(
					'e01USERID'=>str_pad($identificacion,10,'0', STR_PAD_LEFT),
					'e01CHMACC'=>str_pad($cuenta,12,'0', STR_PAD_LEFT)
				)
			)
		);
		
		$error = 0;
		
		$pendiente = false;
		
		// Busca la cantidad de aprobaciones del usuario juridico
		if(!empty($usuario['rol_juridico_id']) && $usuario['rol_juridico_id']>2){

			$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener($usuario['id'], 22);
			if(!$transaccion_detalle) {
				$res = '';
				$this->log( 'Usuario juridico no autorizado - ID: ' . $usuario['id'], LOG_ERR);	
			}
		}
		
		if($res){
			if(isset($res['EDL890001Return']['EDL890001']['EDL890001'])){
				$arrData = $res['EDL890001Return']['EDL890001']['EDL890001'];
				normaliza($arrData);
				
				if(!empty($usuario['rol_juridico_id']) && ($usuario['rol_juridico_id']==3 || $usuario['rol_juridico_id']==5)){ 
					// Si es un usuario operador u operador/aprobador monta la operacion como pendiente

					$res = $this->TransaccionPendiente->save(
						array(
							'TransaccionPendiente'=>array(
								'usuario_id'=>$usuario['id'],
								'tipo'=>'csx',
								'estatus'=>'pa',
								'fecha_hora_registro'=>date('Y-m-d H:i:s'),
								'aprobaciones_requeridas'=>$transaccion_detalle['aprobaciones'],
								'tipo_rif'=>$usuario['tipo_rif'],
								'rif'=>$usuario['rif'],
								'cuenta_origen'=>encriptar($this->data['Cheque']['cuenta']),
								'descripcion'=> encriptar($motivo),
								'monto'=> 0
							)
						),
						false
					);

					if($res){
						$error = 0;
					} else {
						$error = -1;
					}

					$pendiente = true;
				}
				
				$cadenaChequera = '';
				
				if($error == 0){
					// Recorre el arreglo y verifica que sea una de las chequeras seleccionadas
					foreach($arrData as $i){	
						if(in_array($i['e01CHMICK'],$this->data['Cheque']['chequeras'])){
							$referencia = null;
							$error = -1;
							
							// si es un master unico o natural ejecuta la operacion
							if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id']==2){

								$res2 = $this->Activo->invoca(
									'STOPDSJV',
									array(
										'req'=>array(
											'STOSTPACC'=> $cuenta,
											'STOSTPFCK'=> $i['e01CHMICK'],
											'STOSTPRMK'=> $motivo,
											'STOSTPTCK'=> $i['e01CHMFCK'],
											'STOSTPFLG'=> 'S'
										)
									)
								);

								if(isset($res2['STOPDSJVReturn']['error']['ERRNUM'])){
									$error = $res2['STOPDSJVReturn']['error']['ERRNUM'];
									$error = ($error == 0) ? $error : intval($res2['STOPDSJVReturn']['error']['ERRNUM']);
									$descripcion = $res2['STOPDSJVReturn']['error']['ERNU01'] . ' - ' . $res2['STOPDSJVReturn']['error']['ERDS01'];
									
									$this->log($descripcion . ' - ID: ' . $usuario['id'], LOG_ERR);	
								}
								else if($res2){
									$arrData2 = isset($res2['STOPDSJVReturn']['STOPDSJV']['STOPDSJV']) ? $res2['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'] : array();

									normaliza($arrData2);
									foreach($arrData2 as $j){
										$referencia = $j['STOSTPNRE'];
									}
									$error = 0;
								}

								$this->Bitacora->registraBitacora(
									$usuario['id'],
									22,
									$this->data['Cheque']['cuenta'],
									NULL,
									NULL,
									$referencia,
									'Suspensión de chequera con cheque inicial ' . str_pad($i['e01CHMICK'], 6, 0, STR_PAD_LEFT),
									($error!=0)
								);
							}
							else if(!empty($usuario['rol_juridico_id']) && ($usuario['rol_juridico_id']==3 || $usuario['rol_juridico_id']==5)){ 
								// Si es un usuario operador u operador/aprobador
								
								$cadenaChequera.= str_pad($i['e01CHMICK'], 6, 0, STR_PAD_LEFT).' ';
								
								$res = $this->TransaccionBeneficiario->save(
									array(
										'TransaccionBeneficiario'=>array(
											'transaccion_id'=>$this->TransaccionPendiente->id,
											'tipo'=>'n',
											'tipo_rif'=>$usuario['tipo_rif'],
											'rif'=>$usuario['rif'],
											'nombre'=>'',
											'tipo_cedula'=>'',
											'cedula'=>'',
											'email'=>'',
											'celular'=>'',
											'monto'=>0,
											'numero_cuenta'=>encriptar(str_pad($i['e01CHMICK'], 6, 0, STR_PAD_LEFT).'|'.str_pad($i['e01CHMFCK'], 6, 0, STR_PAD_LEFT))
										)
									),
									false
								);
								unset($this->TransaccionBeneficiario->id);
								$error = 0;
							}
							else{
								$error = -1;
								break;
							}
						}
						
					} // fin foreach
				}
				
				if(!empty($cadenaChequera)){
					$this->TransaccionPendiente->saveField('cuenta_destino',encriptar($cadenaChequera));
					$referencia = $this->TransaccionPendiente->id;
				}
			}
		}
		
		$this->set(compact('error','motivos','referencia','pendiente'));	
			
		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');		

		$this->render('resultado_chequera');
	}
	
	
	/**
	 * Acción para el resultado de la suspensión de cheques de una chequera sin suspender la chequera completa
	 */
	function cheques_resultado(){
		if($this->Session->check('cheques.index')){	
			$this->data = $this->Session->read('cheques.index');
			$motivos = $this->Cheque->buscar_motivos();

			$this->Session->delete('cheques.index');
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$arregloCheque = array();
	
		if(isset($this->data['Cheque']['cheques']) && is_array($this->data['Cheque']['cheques'])){
			$arregloCheque = $this->data['Cheque']['cheques'];
		}

		if(empty($this->data['Cheque']['motivo'])) { 
			$motivo = $motivos[$this->data['Cheque']['tipo_motivo']];
		} else {
			$motivo = $this->data['Cheque']['motivo'];
		}

		if($arregloCheque >= 0){
			
			$error = 0;
			$pendiente = false;
			$referencia = null;
					
			// Busca la cantidad de aprobaciones del usuario juridico
			if(!empty($usuario['rol_juridico_id']) && $usuario['rol_juridico_id']>2){
				
				$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener($usuario['id'], 22);
				if(!$transaccion_detalle) {
					$error = -1;
					$this->log( 'Usuario juridico no autorizado - ID: ' . $usuario['id'], LOG_ERR);	
				}
			}
			
			if(!empty($usuario['rol_juridico_id']) && ($usuario['rol_juridico_id']==3 || $usuario['rol_juridico_id']==5)){ 
					// Si es un usuario operador u operador/aprobador monta la operacion como pendiente

					$res = $this->TransaccionPendiente->save(
						array(
							'TransaccionPendiente'=>array(
								'usuario_id'=>$usuario['id'],
								'tipo'=>'csi',
								'estatus'=>'pa',
								'fecha_hora_registro'=>date('Y-m-d H:i:s'),
								'aprobaciones_requeridas'=>$transaccion_detalle['aprobaciones'],
								'tipo_rif'=>$usuario['tipo_rif'],
								'rif'=>$usuario['rif'],
								'cuenta_origen'=>encriptar($this->data['Cheque']['cuenta']),
								'descripcion'=> encriptar($motivo),
								'monto'=> 0
							)
						),
						false
					);

					if($res){
						$error = 0;
					} else {
						$error = -1;
					}

					$pendiente = true;
				}
				
			$cadenaCheque = '';
			
			if($error==0){
				
				// Recorre el arreglo de cheques y verifica si es usuario natural o
				// juridico operador, operador/aprobador
				foreach($arregloCheque as $cheque){	
					$referencia = null;
					$error = 0;
					// si es un master unico o natural ejecuta la operacion
					if(empty($usuario['rol_juridico_id']) || $usuario['rol_juridico_id']==2){

						$error = -1;
					
						//suspensión de cheques
						$res = $this->Activo->invoca(
							'STOPDSJV',
							array(
								'req'=>array(

									'STOSTPACC'=> substr($this->data['Cheque']['cuenta'],10),
									'STOSTPFCK'=> $cheque,
									'STOSTPRMK'=> $motivo,
									'STOSTPTCK'=> '',
									'STOSTPFLG'=> 'S'
								)
							)
						);

						if(isset($res['STOPDSJVReturn']['error']['ERRNUM'])){
							$error = intval($res['STOPDSJVReturn']['error']['ERRNUM']);
							$error = ($error == 0) ? $error : intval($res['STOPDSJVReturn']['error']['ERRNUM']);
							$descripcion = $res['STOPDSJVReturn']['error']['ERNU01'] . ' - ' . $res['STOPDSJVReturn']['error']['ERDS01'];

							$this->log($descripcion . ' - ID: ' . $usuario['id'], LOG_ERR);	
						}
						elseif(isset($res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'])){
							$arrData = isset($res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV']) ? $res['STOPDSJVReturn']['STOPDSJV']['STOPDSJV'] : array();

							normaliza($arrData);
							foreach($arrData as $i){
								$referencia = $i['STOSTPNRE'];
							}
							$error = 0;
						}

						$this->Bitacora->registraBitacora(
							$usuario['id'],
							22,
							$this->data['Cheque']['cuenta'],
							NULL,
							NULL,
							$referencia,
							'Suspensión de cheque ' . strval($cheque),
							($error != 0 )
						);

					}
					else if(!empty($usuario['rol_juridico_id']) && ($usuario['rol_juridico_id']==3 || $usuario['rol_juridico_id']==5)){ 
						// Si es un usuario operador u operador/aprobador 
						
						$cadenaCheque.= str_pad($cheque, 6, 0, STR_PAD_LEFT).' ';
					}
					else{
						$error = -1;
						break;
					}
				}// fin del for
			}//fin del if
			
			if(!empty($cadenaCheque)){
					$this->TransaccionPendiente->saveField('cuenta_destino',encriptar($cadenaCheque));
					$referencia = $this->TransaccionPendiente->id;
				}
			
		}
		
		$this->set(compact('error','referencia','motivos','pendiente'));
			
		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');		

		$this->render('resultado_cheque');
	}
	
	
	/**
         * Acción para la suspensión de cheques dentro de una chequera, es decir, no suspender la chequera completa sino los cheques que se deseen
         * @param String Indica si se está regresando de la confirmación
         */
	function cheques($volver=NULL){
		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');

		$motivos = $this->Cheque->buscar_motivos();

		$this->set(compact('motivos'));

		if(isset($this->data['Cheque']['tipo_motivo'])){
			$this->Cheque->set($this->data);

			if($this->Cheque->validates(array('fieldList'=>array('motivo','tipo_motivo')))){
				$estado = $this->Session->read('cheques.index');
				$estado['Cheque']['motivo']=$this->data['Cheque']['motivo'];
				$estado['Cheque']['tipo_motivo']=$this->data['Cheque']['tipo_motivo'];
				$estado['Cheque']['cheques']=$this->data['Cheque']['cheques'];

				$this->Session->write('cheques.index',$estado);

				$this->redirect('cheques_confirmacion');
			}
		} else if($this->Session->check('cheques.index')){
			$estado = $this->Session->read('cheques.index');
			if(isset($this->data['Cheque']['chequera'])){
				$estado['Cheque']['chequera'] = $this->data['Cheque']['chequera'];
				$this->data = $estado;
				$this->Session->write('cheques.index',$estado);
			} else {
				$this->data = $estado;
			}

			//Comienzo Integración
			$usuario = $this->Session->read('usuarios.datos');
			$ciUsuario = desencriptar($usuario['cedula']);
			$cuenta	= substr($estado['Cheque']['cuenta'] ,10);
			$arrData = array();

			$res = $this->Activo->invoca(
			'EDL890001',
				array(
					'req'=>array(
						'e01USERID'=>str_pad($ciUsuario,10,'0', STR_PAD_LEFT),
						'e01CHMACC'=>str_pad($cuenta,12,'0', STR_PAD_LEFT)
					)
				)
			);
			$disChe = array();	
			$chequera = array();
			
			if($res){
				if(!empty($res['EDL890001Return']['EDL890001']['EDL890001'])){
					$arrData = $res['EDL890001Return']['EDL890001']['EDL890001'];
					normaliza($arrData);				
				}
				
				foreach($arrData as $i){
					$primerCheque = $i['e01CHMICK'];
					$cantCheqDispChequera = $i['e01CHMFCK'] - $i['e01CHMICK'] + 1;

					if(intval($this->data['Cheque']['chequera']) != $primerCheque) continue;
					for($j=1; $j <= $cantCheqDispChequera; $j++){
						$e = 'e01CHMS'.str_pad($j,2,'0',STR_PAD_LEFT);
						$v = 'e01CHMN'.str_pad($j,2,'0',STR_PAD_LEFT);
				
						if($i[$e] == 'D'){	
							$disChe[]= $i[$v];	
						}
					}
					$chequera = $i;
					break;
				}
			}
			
			$this->set(compact('chequera','disChe'));
			
			if(!isset($volver)){
				$this->data['Cheque']['tipo_motivo']='';
				$this->data['Cheque']['motivo']='';
				$this->data['Cheque']['cheques']=array();
			}
		}
	}

        /**
         * Acción de la confirmación de la suspensión de cheques de una chequera sin suspender la chequera completa
         */
	function cheques_confirmacion(){
		if($this->Session->check('cheques.index')){
			$this->data = $this->Session->read('cheques.index');
		} else {
			$this->redirect('index');
		}

		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');

		$motivos = $this->Cheque->buscar_motivos();

		$this->set(compact('motivos'));
	}

        /**
         * Acción para la suspensión de un rango de cheques
         */
	function rango_cheque(){
		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');

		$motivos = $this->Cheque->buscar_motivos();

		$this->set(compact('motivos'));

		if(isset($this->data['Cheque'])){
			$this->Cheque->set($this->data);

			if($this->Cheque->validates(array('fieldList'=>array('chqInicial','chqFinal','motivo','tipo_motivo')))){
				$estado = $this->Session->read('cheques.index');
				$estado['Cheque']['motivo']=$this->data['Cheque']['motivo'];
				$estado['Cheque']['tipo_motivo']=$this->data['Cheque']['tipo_motivo'];
				$estado['Cheque']['chqInicial']=$this->data['Cheque']['chqInicial'];
				$estado['Cheque']['chqFinal']=$this->data['Cheque']['chqFinal'];

				$this->Session->write('cheques.index',$estado);

				$this->redirect('rango_cheque_confirmacion');
			}
		} else if($this->Session->check('cheques.index')){
			$this->data = $this->Session->read('cheques.index');
		}	
	}

        /**
         * Acción de la confiemación de un rango de cheques
         */
	function rango_cheque_confirmacion(){
		if($this->Session->check('cheques.index')){
			$this->data = $this->Session->read('cheques.index');
		} else {
			$this->redirect('index');
		}

		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');

		$motivos = $this->Cheque->buscar_motivos();

		$this->set(compact('motivos'));
	}

        /**
         * Acción para el resultado de la suspensión de un rango de cheques 
         */
	function resultado(){
		$this->layout = 'interna';

		$this->set('seccion_actual','cheques.suspender');

		if($this->Session->check('cheques.pendiente')){
			$this->data = $this->Session->read('cheques.pendiente');
			
		} else {
			$this->redirect('suspender');
		}		
	}
}
?>