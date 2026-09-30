<?php
/**
 * Controlador para desplegar los créditos y sus detalles
 */

/**
 * Controlador para desplegar los créditos y sus detalles
 */
class CreditosController extends AppController {
    
    /**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array('Usuario','DetalleProducto','Bitacora');
    
    
    /**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */
	var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');

    
    /**
     *Acción para listar los créditos disponibles del cliente 
     */
	function index(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(5)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		// para el regreso al index o el consolidado
		$this->Session->write('usuarios.volver_index', TRUE);
		
		$usuario = $this->Session->read('usuarios.datos');

		// Registra la consulta del producto
		$this->Bitacora->registraBitacora($usuario['id'], 5, null, null, null, null, "Consulta Listado Créditos");
		
		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$tipo_documento = $usuario['tipo_rif'];
			$documento = desencriptar($usuario['rif']);
			$documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$tipo_documento = $usuario['tipo_cedula'];
			$documento = desencriptar($usuario['cedula']);
			$documento_identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
		
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		
		if(isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])){
			$res = $this->Activo->invoca(
				'SUMDSJV',
				array(
					'req'=>array(
						'SUMACN'=>trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
						'SUMFIR'=>'X'
					)
				)
			);
		}

		$creditos = array();

		$totales = array(
			'LNSPOF'=>0,
			'e01DEAMPP'=>0,
			'LNSPRI'=>0
		);

		if(isset($res) && !empty($res)){
			$x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
			normaliza($x);

			foreach($x as $i){
				if($i['SUMTYP']=='LNS'){
					
					// si es un usuario juridico verifica si tiene permiso en la cuenta
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 5, trim($i['SUMACN'])) ) continue;
					}
					
					$i['ALIAS'] = _a($usuario['id'], $i['SUMACN']);
					
					$res = $this->Activo->invoca(
						'LNSDSJV',
						array(
							'req'=>array(
								'LNSACC'=>trim($i['SUMACN']),

							)
						)
					);

					$x = $res['LNSDSJVReturn']['LNSDSJV'];

					$y = array_merge($i,$x);
					
					$res = $this->Activo->invoca(
						'EDL098001',
						array(
							'req'=>array(
								'e01DEAACC'=>trim($i['SUMACN']),
								'h01USERID'=>$documento_identificacion
							)
						)
					);
					
					if($res && isset($res['EDL098001Return']['EDL098001'])){
						$detalle = $res['EDL098001Return']['EDL098001'];
						$y = array_merge($y, $detalle);
					}					
					
					$creditos[] = $y;
					$totales['LNSPOF'] += $y['LNSPOF'];
					$totales['e01DEAMPP'] += $y['e01DEAMPP'];
					$totales['LNSPRI'] += $y['LNSPRI'];
				}
			}
		}

		$this->set(compact('creditos','totales'));	

		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.creditos');	
	}

    
    /**
     *Acción para la consulta de movimientos del crédito 
     */
	function movimientos(){
		
		// para el regreso al index o el consolidado
		if( $this->Session->check('usuarios.volver_index') ){
			$ruta_volver = '/creditos/';
		}
		else {
			$ruta_volver = '/consultas/integral';
		}
		$this->set('ruta_volver', $ruta_volver);
		// para el regreso al index o el consolidado
		
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_credito')))){
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');
		
		// Registra la consulta del producto
		$this->Bitacora->registraBitacora($usuario['id'], 5, null, null, null, null, "Consulta Crédito " . $this->data['DetalleProducto']['nro_credito']);

		$creditos = $this->Activo->buscarCreditos();
		
		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
			
		$res = $this->Activo->invoca(
			'EDL098001',
			array(
				'req'=>array(
					'e01DEAACC'=>trim($this->data['DetalleProducto']['nro_credito']),
					'h01USERID'=>$identificacion,
					'e01MATUR1' => date('m'), // mes hasta
					'e01MATUR3' => date('y')+1, // año hasta
				)
			)
		);
		
		$pagos = array();
		$detalle = array();
		$condicion = array();

		if($res){
			if(isset($res['EDL098001Return']['EDL090002']['EDL090002'])){
				$temp_pagos = $res['EDL098001Return']['EDL090002']['EDL090002'];
				normaliza($temp_pagos);
				
				$last_key = 0;
				$cantidad = count($temp_pagos);
				for($i = 0; $i < $cantidad; $i++){
					
					// Verifica si es una cuota vencida
					if($temp_pagos[$i]['e02DLPSTS']==' ' && $temp_pagos[$i]['e02DLPVEN'] > 0 ){
						$pagos[]= $temp_pagos[$i];
						$last_key = $i+1;
					}
					
					// En caso de no encontrar ningun vencido
					if(!$last_key){
						if( trim($temp_pagos[$i]['e02DLPDT1'])==0 ){
							$last_key = $i;
						}
					}
				}
				
				// El proximo a pagar
				if( $last_key < $cantidad ) $pagos[] = $temp_pagos[$last_key];
			}
			$detalle = $res['EDL098001Return']['EDL098001'];
			$condicion = $res['EDL098001Return']['EDL098001']['e01DEADLC'];
			
			switch($condicion){
				case 1:
					$estado = 'Vigente';
				break;
				case 2:
					$estado = 'Vencido';
				break;
				case 3:
					$estado = 'Castigado';
				break;
				case 4:
					$estado = 'Castigado no Informado';
				break;	
				case 5:
					$estado = 'Acelerado';
				break;
			}
		}
		
		$total_registros = count($pagos);
			
		if($total_registros){

			// Empieza código para paginar	
		
			$offset = 0;

			if(
				isset($this->params['form']['offset']) &&
				preg_match('/^\d+$/',$this->params['form']['offset'])
			){
				$offset = intval($this->params['form']['offset']);			
			}

			$pagos = array_slice(
				$pagos,
				$offset,
				Configure::read('Tamano.paginacion'),
				true
			);

			// Fin código para paginar
			
		}

		//Para cabecera UVCC / UVCP
		
		if (!empty($usuario['rif'])){
			$sumusr3 = desencriptar($usuario['rif']);
			if (strlen($sumusr3)==8) $sumusr3='0'.$sumusr3;
			if (strlen($sumusr3)==7) $sumusr3='00'.$sumusr3;
			if (strlen($sumusr3)==6) $sumusr3='000'.$sumusr3;
			$sumusr3 =  $usuario['tipo_rif'].$sumusr3;
		}else{
			$sumusr3 = desencriptar($usuario['cedula']);
			if (strlen($sumusr3)==8) $sumusr3='0'.$sumusr3;
			if (strlen($sumusr3)==7) $sumusr3='00'.$sumusr3;
			if (strlen($sumusr3)==6) $sumusr3='000'.$sumusr3;
			$sumusr3 = $usuario['tipo_cedula'].$sumusr3;
			
		}
		
		$res_cab_uvcc = $this->Activo->invoca(
			'SUMDSJV3',
			array(
				'req'=>array(
					'SUMUSR3'=>trim($sumusr3)
				)
			)
		);
		
		$cab_uvcc = array();
		
		if ($res_cab_uvcc && isset($res_cab_uvcc['SUMDSJV3Return']['SUMDSJV3']['SUMDSJV3'])){
				$cab_uvcc= $res_cab_uvcc['SUMDSJV3Return']['SUMDSJV3']['SUMDSJV3'];
			}
		
		//Para detalle de creido UVCC / UVCP
		$res_det_uvcc = $this->Activo->invoca(
			'SUMDSJV4',
			array(
				'req'=>array(
					'SUMACC4'=>trim($this->data['DetalleProducto']['nro_credito']),
				)
			)
		);
		$det_uvcc = array();
		
		if ($res_det_uvcc && isset($res_det_uvcc['SUMDSJV4Return']['SUMDSJV4']['SUMDSJV4'])){
				$det_uvcc= $res_det_uvcc['SUMDSJV4Return']['SUMDSJV4']['SUMDSJV4'];
			}

		$this->set(compact('creditos','detalle','pagos','total_registros','estado','cab_uvcc','det_uvcc'));	
		
		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.creditos');
		
		// Se guarda en sesion las fechas de apertura, vencimiento y proximo pago para la busqueda avanzada
		$this->Session->write('usuarios.creditos.fechas', array(
			'e01OPEND1' => $detalle['e01OPEND1'], // mes desde
			'e01OPEND3' => $detalle['e01OPEND3'], // año desde
			'e01MATUR1' => $detalle['e01MATUR1'], // mes hasta
			'e01MATUR3' => $detalle['e01MATUR3'], // año hasta
			'e01DEAMPP1' => $detalle['e01DEAMPP1'], // mes proximo pago
			'e01DEAMPP3' => $detalle['e01DEAMPP3'], // año proximo pago
		));
		
	}

    
    /**
     *Acción para consultar las cuotas pagadas de un crédito 
     */
	function cuotas_pagadas(){
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_credito')))){
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');

		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
			
		$res = $this->Activo->invoca(
			'EDL098001',
			array(
				'req'=>array(
					'e01DEAACC'=>trim($this->data['DetalleProducto']['nro_credito']),
					'h01USERID'=>$identificacion,
					'e01MATUR1' => date('m'), // mes hasta
					'e01MATUR3' => date('y'), // año hasta
				)
			)
		);
		
		$pagos = array();

		if($res){
			$x = $res['EDL098001Return']['EDL090002']['EDL090002'];
			normaliza($x);
			
			foreach($x as $i){
				if(trim($i['e02DLPSTS'])=='P'){
					$pagos[]=$i;
				}
			}
			
			$detalle = $res['EDL098001Return']['EDL098001'];
		}

		$total_registros = count($pagos);
		
		if($total_registros){
		
			// Empieza código para paginar
			
			$offset = 0;

			if(
				isset($this->params['form']['offset']) &&
				preg_match('/^\d+$/',$this->params['form']['offset'])
			){
				$offset = intval($this->params['form']['offset']);			
			}

			$pagos = array_slice(
				$pagos,
				$offset,
				Configure::read('Tamano.paginacion'),
				true
			);

			// Fin código para paginar
			
		}

		$this->set(compact('creditos','detalle','pagos','total_registros'));	

		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.creditos');	
	}

    
    /**
     *Acción para consultar las cuotas vencidas de un crédito 
     */
	function cuotas_vencidas(){
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_credito')))){
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');

		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
			
		$res = $this->Activo->invoca(
			'EDL098001',
			array(
				'req'=>array(
					'e01DEAACC'=>trim($this->data['DetalleProducto']['nro_credito']),
					'h01USERID'=>$identificacion,
					'e01MATUR1' => date('m'), // mes hasta
					'e01MATUR3' => date('y'), // año hasta
				)
			)
		);
		
		$pagos = array();

		if($res){
			$x = $res['EDL098001Return']['EDL090002']['EDL090002'];
			normaliza($x);
			
			foreach($x as $i){
				if($i['e02DLPSTS']==' ' && $i['e02DLPVEN'] > 0 ){
					$pagos[]=$i;
				}
			}
			
			$detalle = $res['EDL098001Return']['EDL098001'];
		}

		$total_registros = count($pagos);
		
		if($total_registros){
		
			// Empieza código para paginar
			
			$offset = 0;

			if(
				isset($this->params['form']['offset']) &&
				preg_match('/^\d+$/',$this->params['form']['offset'])
			){
				$offset = intval($this->params['form']['offset']);			
			}

			$pagos = array_slice(
				$pagos,
				$offset,
				Configure::read('Tamano.paginacion'),
				true
			);

			// Fin código para paginar
			
		}	

		$this->set(compact('creditos','detalle','pagos','total_registros'));	

		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.creditos');	
	}

    
    /**
     *Acción para exportar el estado del credito 
     */
	function exportar_estado(){
		if(isset($this->data['DetalleProducto'])){
			
			$this->layout = 'none';
			
			$usuario = $this->Session->read('usuarios.datos');
			
			$fechas = $this->Session->read('usuarios.creditos.fechas');
			
			/** Para identificar si es un usuario juridico **/
			if($usuario['tipo_rif']){
				$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
			}
			else{
				$identificacion = desencriptar($usuario['cedula']);
			}
			/** Para identificar si es un usuario juridico **/
			
			$res = $this->Activo->invoca(
				'EDL098001', array( 
					'req' => array(
						'e01DEAACC' => trim($this->data['DetalleProducto']['nro_credito']),
						'h01USERID' => $identificacion,
						'e01MATUR1' => $fechas['e01DEAMPP1'], // mes hasta
						'e01MATUR3' => $fechas['e01DEAMPP3'], // año hasta
					)
				)
			);

			$pagos = array();
			
			$detalle = array();
			
			if($res){
				
				$pagos = $res['EDL098001Return']['EDL090002']['EDL090002'];
				
				normaliza($pagos);
				
				$detalle = $res['EDL098001Return']['EDL098001'];
				
				$condicion = $res['EDL098001Return']['EDL098001']['e01DEADLC'];
			
				switch($condicion){
					case 1:
						$estado = 'Vigente';
					break;
					case 2:
						$estado = 'Vencido';
					break;
					case 3:
						$estado = 'Castigado';
					break;
					case 4:
						$estado = 'Castigado no Informado';
					break;	
					case 5:
						$estado = 'Acelerado';
					break;
				}
			
				$this->set(compact('detalle','pagos','usuario','estado'));
			}
			else{
				$this->redirect('index');
			}
		}
		else{
			$this->redirect('index');
		}
	}

    
    /**
     *Acción para el estado de cuenta del crédito 
     */
	function edo_cuenta(){
		
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_credito')))){
				$this->redirect('index');
			}
		}
		else {
			$this->redirect('index');
		}
		
		$this->layout = 'interna';
		
		$this->set('seccion_actual','consultas.creditos');
		
		// Para limitar los meses del credito en caso de ser menor a un año
		
		$fechas = $this->Session->read('usuarios.creditos.fechas');
		
		$inicio = strtotime( $fechas['e01OPEND1'] . '/11/' . $fechas['e01OPEND3']);
		
		$fin = strtotime( $fechas['e01MATUR1'] . '/11/' . $fechas['e01MATUR3']);
		
		$opciones = array();
		
		$t = strtotime(date('m').'/10/'.date('Y'));
		
		if($inicio<=$t){
			
			$t = strtotime(date('m').'/10/'.date('Y').' -1 month');

			if($inicio > $t) $t = $inicio + 2678400;

			if($fin < time()) $t = $fin;
			
			for($i=0;$i<11;$i++){

				if( $t <= $inicio ) break;

				setlocale(LC_ALL,'es_VE.UTF-8'); //PARA LINUX
				//setlocale(LC_ALL,'esn'); //PARA WINDOWS
				$opciones[$t] = ucwords(strftimeAW('%B %Y', $t));

				$t = $t - 2678400;
			}
		}
		
		$this->set(compact('opciones'));
		
		// Para limitar los meses del credito en caso de ser menor a un año
	}
	
    /**
     *Acción para el historial del crédito 
     */
	function historial(){
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_credito')))){
				$this->redirect('index');
			}
		}
		else {
			$this->redirect('index');
		}
		
		// Para mostrar los años del credito, como vienen dos digitos se le concatena los dos primeros
		$fechas = $this->Session->read('usuarios.creditos.fechas');
		$anios = array(
			'20'.str_pad($fechas['e01OPEND3'], 2, '0', STR_PAD_LEFT),
			date('Y')
		);
		$this->set(compact('anios'));
		// Para mostrar los años del credito, como vienen dos digitos se le concatena los dos primeros
		
		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.creditos');
		
		if(isset($this->data['DetalleProducto']['periodo'])){
			
			$usuario = $this->Session->read('usuarios.datos');
			
			/** Para identificar si es un usuario juridico **/
			if($usuario['tipo_rif']){
				$identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
			}
			else{
				$identificacion = desencriptar($usuario['cedula']);
			}
			/** Para identificar si es un usuario juridico **/

			$arrayParams = array(
				'e01DEAACC' =>trim($this->data['DetalleProducto']['nro_credito']),
				'h01USERID' =>$identificacion,
				'e01OPEND1' => '', // mes desde
				'e01OPEND3' => '', // año desde
				'e01MATUR1' => '', // mes hasta
				'e01MATUR3' => '', // año hasta
			);
			
			switch ($this->data['DetalleProducto']['periodo']){
				case 'a': // mes actual
					$arrayParams['e01OPEND1'] = date('m');
					$arrayParams['e01OPEND3'] = date('y');
					$arrayParams['e01MATUR1'] = date('m');
					$arrayParams['e01MATUR3'] = date('y');
					break;
				case 'n': // mes anterior
					$arrayParams['e01OPEND1'] = date('m', strtotime('-1 month'));
					$arrayParams['e01OPEND3'] = date('y', strtotime('-1 month'));
					$arrayParams['e01MATUR1'] = date('m', strtotime('-1 month'));
					$arrayParams['e01MATUR3'] = date('y', strtotime('-1 month'));
					break;
				case 'c': // año actual
					$arrayParams['e01OPEND1'] = '01';
					$arrayParams['e01OPEND3'] = date('y');
					$arrayParams['e01MATUR1'] = date('m');
					$arrayParams['e01MATUR3'] = date('y');
					break;
				case 'r': // año anterior
					$arrayParams['e01OPEND1'] = '01';
					$arrayParams['e01OPEND3'] = date('y', strtotime('-1 year'));
					$arrayParams['e01MATUR1'] = '12';
					$arrayParams['e01MATUR3'] = date('y', strtotime('-1 year'));
					break;
				case 'o': // periodo
					$arrayParams['e01OPEND1'] = $this->data['DetalleProducto']['fechaDesde']['month'];
					$arrayParams['e01OPEND3'] = substr($this->data['DetalleProducto']['fechaDesde']['year'], 2);
					$arrayParams['e01MATUR1'] = $this->data['DetalleProducto']['fechaHasta']['month'];
					$arrayParams['e01MATUR3'] = substr($this->data['DetalleProducto']['fechaHasta']['year'], 2);
					break;
			}
			
			$res = $this->Activo->invoca( 'EDL098001', array( 'req' => $arrayParams ) );

			$pagos = array();
			$detalle = array();
			
			if($res){
				if(isset($res['EDL098001Return']['EDL090002']['EDL090002'])){
					$pagos = $res['EDL098001Return']['EDL090002']['EDL090002'];
					normaliza($pagos);
				}

				$detalle = $res['EDL098001Return']['EDL098001'];
				$condicion = $res['EDL098001Return']['EDL098001']['e01DEADLC'];
			
				switch($condicion){
					case 1:
						$estado = 'Vigente';
					break;
					case 2:
						$estado = 'Vencido';
					break;
					case 3:
						$estado = 'Castigado';
					break;
					case 4:
						$estado = 'Castigado no Informado';
					break;	
					case 5:
						$estado = 'Acelerado';
					break;
				}
				
			}
			
			$total_registros = count($pagos);
			
			if($total_registros){
				
				// Empieza código para paginar

				$offset = 0;

				if(
					isset($this->params['form']['offset']) &&
					preg_match('/^\d+$/',$this->params['form']['offset'])
				){
					$offset = intval($this->params['form']['offset']);			
				}

				$pagos = array_slice(
					$pagos,
					$offset,
					Configure::read('Tamano.paginacion'),
					true
				);

				// Fin código para paginar
			}
			
			// Inidica a donde va regresar
			$ruta_volver = '/creditos/historial';

			// Indica que proviene de una busqueda
			$flag_busqueda = true;
			
			$this->set(compact('detalle','pagos','total_registros','ruta_volver','flag_busqueda','estado'));
			
			$this->render('movimientos');
		}
	}	
}
?>