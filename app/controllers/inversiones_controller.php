<?php
/**
 * Controlador para las inversiones 
 */
 
/**
 * Controlador para las inversiones 
 */
class InversionesController extends AppController{
    
	/**
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array('Usuario','DetalleProducto','Bitacora');

        
	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session');

        
    /**
	 * Acción para el listado de inversiones de un cliente
	 */    
	function index(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(6)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		// para el regreso al index o el consolidado
		$this->Session->write('usuarios.volver_index', TRUE);
		
		$this->layout = 'interna';

		$this->set('seccion_actual','consultas.inversiones');

		$usuario = $this->Session->read('usuarios.datos');
		
		// Registra la consulta del producto
		$this->Bitacora->registraBitacora($usuario['id'], 6, null, null, null, null, "Consulta Listado de Inversiones");
			
		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$tipo_documento = $usuario['tipo_rif'];
			$nro_documento = desencriptar($usuario['rif']);
			$documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$tipo_documento = $usuario['tipo_cedula'];
			$nro_documento = desencriptar($usuario['cedula']);
			$documento_identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/

		$datos_basicos = $this->Session->read('usuarios.nibpf3010');

		if(isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])){
			$res = $this->Activo->invoca(
				'SUMDSJV',array( 
					'req'=>array(
						'SUMACN'=>trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']),
						'SUMFIR'=>'X'
					)
				)
			);
		}
		
		$inversiones = array();
		
		$totales = array(
			'SUMBAL'=>0,
			'SUMAVB'=>0
		);

		if(isset($res) && !empty($res)){
			$x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
			normaliza($x);
			foreach ($x AS $i){
				if($i['SUMTYP']=='CDS' || $i['SUMTYP']=='TDS'){
					
					// si es un usuario juridico verifica si tiene permiso en la cuenta
					if($usuario['tipo_rif'] && $usuario['rol_juridico_id']>2){
						if( !permisoCuenta($usuario['id'], 6, trim($i['SUMACN'])) ) continue;
					}
					
					$i['ALIAS'] = _a($usuario['id'], $i['SUMACN']);
					
					// Busca la fecha de vencimiento consultando el detalle de la inversion
					$res2 = $this->Activo->invoca(
						'CDSDSJV',
						array(
							'req'=>array(

								'CDSDEACCY'=>$i['SUMCCY'],
								'CDSDEAACC'=>trim($i['SUMACN'])
							)
						)
					);

					// Asigna los datos al registro de la inversion
					$mov = $res2['CDSDSJVReturn']['CDSDSJS'];
					
					$i = array_merge($i, array(
						'CDSMATDTE1'=>$mov['CDSMATDTE1'],
						'CDSMATDTE2'=>$mov['CDSMATDTE2'],
						'CDSMATDTE3'=>$mov['CDSMATDTE3']
					));

					// Asigna al arreglo de inversiones y calcula los totales
					$inversiones[] = array_map("trim", $i);
					$totales['SUMBAL'] += $i['SUMBAL'];
					$totales['SUMAVB'] += $i['SUMAVB'];
				}				
			}
		}

		$this->set(compact('inversiones','totales'));
	}

	/**
	 * acción para mostrar el detalle de una Inversión seleecionada
	 */
	function detalle(){
		
		// para el regreso al index o el consolidado
		if( $this->Session->check('usuarios.volver_index') ){
			$ruta_volver = '/inversiones/';
		}
		else {
			$ruta_volver = '/consultas/integral';
		}
		$this->set('ruta_volver', $ruta_volver);
		// para el regreso al index o el consolidado
		
		if(isset($this->data['DetalleProducto'])){
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if(!$this->DetalleProducto->validates(array('fieldList'=>array('nro_inversion')))){
				$this->redirect('index');
			}

			$this->layout = 'interna';
			$this->set('seccion_actual','consultas.inversiones');

			$usuario = $this->Session->read('usuarios.datos');

			$moneda = "";

			// Registra la consulta del producto
			$this->Bitacora->registraBitacora($usuario['id'], 6, null, null, null, null, "Consulta Inversión " . $this->data['DetalleProducto']['nro_inversion']);
			
			/** Para identificar si es un usuario juridico **/
			if($usuario['tipo_rif']){
				$tipo_documento = $usuario['tipo_rif'];
				$nro_documento = desencriptar($usuario['rif']);
				$documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
			}
			else{
				$tipo_documento = $usuario['tipo_cedula'];
				$nro_documento = desencriptar($usuario['cedula']);
				$documento_identificacion = desencriptar($usuario['cedula']);
			}
			/** Para identificar si es un usuario juridico **/

			$datos_basicos = $this->Activo->invoca(
				'NIBPF3010',
				array(
					'req'=>array(
						'NACIONALIDAD'=>$tipo_documento,
						'NROIDENTIFICACION'=>str_pad($nro_documento,12,'0',STR_PAD_LEFT),
						'NROPARAM'=>'1'
					)
				)
			);

			$identificacion = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];

			$res = $this->Activo->invoca(
				'SUMDSJV',array( 
					'req'=>array(
						'SUMACN'=>$identificacion,
						'SUMFIR'=>'X'
					)
				)
			);

			// Verifica si hubo resultado y si es una inversion
			$inversiones = array();
			if($res){
				$x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
				
				foreach ($x as $i){
					if($i['SUMTYP']=='CDS' || $i['SUMTYP']=='TDS'){
						$inversiones[trim($i['SUMACN'])] = $i['SUMACN'];

						// Verifica si es la misma inversion que se quiere ver el detalle
						if ($this->data['DetalleProducto']['nro_inversion']==trim($i['SUMACN'])){
							$moneda = $i['SUMCCY'];
							$alias = _a($usuario['id'], $i['SUMACN']);
						}
					}
				}
			}

			// Busca el detalle de la inversion
			$res2 = $this->Activo->invoca(
				'CDSDSJV',
				array(
					'req'=>array(

						'CDSDEACCY'=>$moneda,
						'CDSDEAACC'=>$this->data['DetalleProducto']['nro_inversion']
					)
				)
			);
			
			$detalle = array();
			$estatus = "";
			$totalInteresPorPagar = array();

			if($res2){
				$detalle = array_map("trim", $res2['CDSDSJVReturn']['CDSDSJS']);
				if($res2['CDSDSJVReturn']['CDSDSJS']['CDSSTATUS'] != 'C'){
					$estatus = 'Activo';				
				}else if($res2['CDSDSJVReturn']['CDSDSJS']['CDSSTATUS'] == 'C'){
					$estatus = 'Cancelado';
				}
				$totalInteresPorPagar = abs($res2['CDSDSJVReturn']['CDSDSJS']['CDSLSYIPD'] - $res2['CDSDSJVReturn']['CDSDSJS']['CDSDEAIPY']);

			}

			$this->set(compact('detalle', 'inversiones', 'alias','estatus','totalInteresPorPagar'));
		} else {
			$this->redirect('index');
		}
		
	}

}

?>