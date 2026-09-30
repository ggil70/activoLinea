<?php

/**
 * Controlador para las operaciones de consulta de tarjeta
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Consultas de tarjetas de cr?dito
 * 
 */
class TarjetasController extends AppController
{
	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array(
		'Usuario',
		'DetalleProducto',
		'ExportarTarjeta',
		'BusquedaCuenta',
		'EstadoCuentaTdc',
		'Bitacora'
	);

	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */
	var $helpers = array('Activo', 'Html', 'Session', 'Form', 'Paginator', 'Text', 'Javascript');

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session', 'RequestHandler', 'Credicard');

	/**
	 * Acci?n que lista las tarjetas de cr?dito del usuario.
	 */
	function index()
	{
		/* Para validar el acceso al modulo */
		if (!validarTransaccion(4)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */

		// para el regreso al index o el consolidado
		$this->Session->write('usuarios.volver_index', TRUE);

		$usuario = $this->Session->read('usuarios.datos');

		/** Para identificar si es un usuario juridico **/
      $tipoCliente = 0;  //Delimita si es natural = 0 / juridico = 1
		if (!empty($usuario['tipo_rif'])){
         $tipoCliente = 1;
         $tipoTarjeta = 'TCC';
			$tipo_documento = $usuario['tipo_rif'];
			$nro_documento = desencriptar($usuario['rif']);
			$documento_identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
		} else {
			$tipoTarjeta = 'CC&';
			$tipo_documento = $usuario['tipo_cedula'];
			$nro_documento = desencriptar($usuario['cedula']);
			$documento_identificacion = desencriptar($usuario['cedula']);
		}

		/** Para identificar si es un usuario juridico **/

		// Registra la consulta del producto
		$this->Bitacora->registraBitacora($usuario['id'], 4, null, null, null, null, "Consulta Listado de Tarjetas");

		// Retorna los datos b�sicos del usuario
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');

		// Almacena un string vacio.
		$res = '';

		// Verificacion de la consulta de los datos del servicio
		// Esta condicion valida si $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'] existe, en este caso es una cedula.
		
		if (isset($datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'])) {
			$res = $this->Activo->invoca(
				'SUMDSJV',
				array(
					'req' => array(
						'SUMACN' => $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],
						'SUMFIR' => 'X'
					)
				)
			);
 		   /*Al final retornamos un objeto que retorna los servicios o numero de tarjeta que pertenecen al cliente.*/
		}

      $documento = $datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'];

 		// Esta variable almacena un array vacio
		$tarjetas = array();


		// Esta variable almacena un array, el cual asigna a "saldo_actual", "credito_disponible" y "pago_minimo" un total de 0
		$totales = array(
			'saldo_actual' => 0,
			'credito_disponible' => 0,
			'pago_minimo' => 0
		);

		// Llamada de funcion de consulta de credicard
		$consultaSalsoTdc = $this->Credicard->consultaDeSaldoTdcSoap(1);
      $consultaSalsoTdc = $consultaSalsoTdc['ConsultSaldoenLineaTDCResponse'];

      if(isset($consultaSalsoTdc['cedula'])){  
         //un solo tarjeta
         if($consultaSalsoTdc['estatustrj'] <= 2){
         	$resp = $this->llenarConsultaTarjeta($tarjetas, $consultaSalsoTdc);
         	if($resp != 0){
         		$tarjetas = $resp;
            	$totales = $this->llenarConsultaTotales($totales, $consultaSalsoTdc);
         	}
         }	
      }else{
      	//Varias tarjetas
      	foreach ($consultaSalsoTdc as $key => $consultaTdc){  
      		if($consultaTdc['estatustrj'] <= 2){
      			$resp = $this->llenarConsultaTarjeta($tarjetas, $consultaTdc);
	         	if($resp != 0){
	         		$tarjetas = $resp;
	         		$totales = $this->llenarConsultaTotales($totales, $consultaTdc);  
	         	}
	         }	
      	}	
      }


		$this->Session->write('consultTdc.consultaTdc', $tarjetas);
		$this->Session->write('consultTdc.tarjetasTdc', $tarjetas);
		$this->set(compact('tarjetas', 'totales'));
		$this->layout = 'interna';
		$this->set('seccion_actual', 'consultas.tarjetas');
	}

	/**
	 * Acci?n que muestra el detalle de la tarjeta de cr?dito
	 */


	function llenarConsultaTotales($totales_tarjetas, $consultaTdc){
	   $pagMinimo = "";
	   $sdoUVC = $consultaTdc['saldeuduvc'];
	   $sdoBS = 0;
   	   $creditoBS = 0;

	   $tipoUVC = $consultaTdc['tipoctauvc'];
	   $limUVC= $consultaTdc['limcreduvc'];

       $sdoBS = $consultaTdc['saldocont'];

	   if($sdoUVC == null){
	   	$sdoUVC = 0;
	   }

	   if($limUVC == null){
	   	$limUVC = 0;
	   }	

	   $creditoBS = $consultaTdc['disponible'];
	   $pagMinimo = $consultaTdc['pagomin'];
	   $totales_tarjetas['saldo_actual'] += $sdoBS;
	   $totales_tarjetas['saldo_petro'] += $sdoBS;
	   $totales_tarjetas['saldo_actualUVC'] += $sdoUVC;
	   $totales_tarjetas['credito_disponible'] += $creditoBS;
	   $totales_tarjetas['pago_minimo'] += $pagMinimo;   
	   $totales_tarjetas['credito_disponible_petro'] += $creditoBS;	
	   return $totales_tarjetas;
	}


    function llenarConsultaTarjeta($tarjetas, $consultaTdc){
	    $fecPago = "";
	    $pagMinimo = "";
	    $tipoUVC = null;
	    $sdoUVC = 0;
	    $sdoBS = 0;
	    $creditoBS = 0;
	    $limUVC = 0;
	    $limBS = 0;    

        if($consultaTdc['estatustrj'] == '1' || $consultaTdc['estatustrj'] == '2'){
		      $fecPago = $consultaTdc['fepago'];
            $pagMinimo = $consultaTdc['pagomin'];
            $tipoUVC = $consultaTdc['tipoctauvc'];
            $limBS = $consultaTdc['limcredito'];
				$nroTarjeta = $consultaTdc['cuenta'];
            $sdoUVC =  $consultaTdc['saldeuduvc'];
            $limUVC = $consultaTdc['limcreduvcc'];				

            $sdoBS = $consultaTdc['saldocont'];
            $creditoBS = $consultaTdc['disponible'];

           	if($sdoUVC == null){
           		$sdoUVC = '0.00';
           	}	
           	if($limUVC == null){
           		$limUVC = '0.00';
          	}	

            $saldoPetro = $sdoBS / $petro_bs;

			$i['saldo_actual'] = $sdoBS;
			$i['nroTarjeta'] = $nroTarjeta;
			$i['saldo_petro'] = $saldoPetro;
			$i['saldo_actual_uvc'] = $sdoUVC;
            $i['tipoTarjetaUVC'] = $tipoUVC; 
			$i['tipo_tarjeta_uvc'] = $tipoUVC;
			$i['pago_minimo'] = $pagMinimo;
			$i['creditoBS'] = $creditoBS;
			$i['nroTarjeta'] = $consultaTdc['cuenta'];
   			$i['limiteBS'] = $limBS;
   			$i['limiteUVC'] = $limUVC; 
				$i['franquicia'] = $consultaTdc['franquicia'];

         	if($fecPago != null){
	         	$i['dia_pago'] = substr($fecPago,0,2);
	   		   	$i['mes_pago'] = substr($fecPago,3,2);
	   		   	$anio = substr($fecPago,6,2) * 1;
	   		   	if($anio > 0){
   		      		$i['anio_pago'] = "20" . substr($fecPago,6,2); 		
   		      	}else{
   		      		$i['anio_pago'] = "0000";
   		      	}	
		   	}else{
   			   	$i['dia_pago'] = "";
   	        	$i['mes_pago'] = "";
			    	$i['anio_pago'] = ""; 		
	   		}   
	   		$i['fecPago'] = $fecPago;	
            //Tipo Tarjeta  Empresarial, normal
            //configurar cuando pasen los bines de credicard miriam
            $tipoTarjeta = "CC&";
            //configurar cuando pasen los bines de credicard miriam

            $i['SUMTYP'] = $tipoTarjeta;
				$i['ALIAS'] = 'Alias no asignado';
				$tarjetas[] = $i;
				return $tarjetas;            
        }else{
         	return 0;
        }	
    }


	function detalle()
	{
		// para el regreso al index o el consolidado

		if ($this->Session->check('usuarios.volver_index')) {
			// Para el regreso al detalle de tarjetas corporativas o comunes
			// Aqui validamos el tipo de TDC y segun el tipo agregamos a la varible $ruta_volver una direccion.

			if ($this->data['DetalleProducto']['tipo'] == 'TCC') {
				$ruta_volver = '/tarjetas/detalle_emp_corp';
				$this->Session->write('usuarios.volver_corporativa_pp', TRUE);
			} else {
				$ruta_volver = '/tarjetas/';
			}
		} else {
			// Para el regreso al detalle de tarjetas corporativas o comunes
			if ($this->data['DetalleProducto']['tipo'] == 'TCC') {
				$ruta_volver = '/tarjetas/detalle_emp_corp';
				$this->Session->write('usuarios.volver_corporativa_pp', TRUE);
			} else {
				$ruta_volver = '/consultas/integral';
			}
		}

		/*Al final asignamos el front detalle.ctp la variable $ruta_volver
		 que contiene el path segun lo que regrese la  condicion */
		 // para el regreso al index o el consolidado                                
		$this->set('ruta_regreso', $ruta_volver);
		/* Esta condicion valida si existe "DetalleProducto" este dato contiene un 
		objeto con nro_tarjeta, nro_tarjeta_padre y tipo (ejemplo CC&) */
		if (isset($this->data['DetalleProducto'])) {
			// Aqui asignamos al objeto anteriormente mencionado  un padre llamado "DetalleProducto"
			$this->DetalleProducto->set($this->data['DetalleProducto']);
			//Esta condicion valida si el parametro nro_tarjeta dentro del objeto existe, de no ser asi redirecciona al index
			if (!$this->DetalleProducto->validates(array('fieldList' => array('nro_tarjeta')))) {
				$this->redirect('index');
			}
		} else {
			//Si ninguna de las condiciones anteriores se cumple redirecciona al index
			$this->redirect('index');
		}

		// Esta variable retorna toda la data del usuario loggeado
		$usuario = $this->Session->read('usuarios.datos');

		// Retorna la tarjeta que se selecciono
		$tarjeta = trim($this->data['DetalleProducto']['nro_tarjeta']);
		// Retorna la tarjeta padre que se selecciono
		$tarjeta_padre = trim($this->data['DetalleProducto']['nro_tarjeta_padre']);

		// Retorna el tipo de tarjeta que se selecciono, ejemplo CC&, TCC... etc.
		$tipo = $this->data['DetalleProducto']['tipo'];

		// En esta condicion validamos si la tarjeta es de tipo TCC, de ser asi
		//if ($tipo == 'TCC') {
			//$tipo_tarjeta = 'C';
			//$tarjetas = $this->Activo->buscarTarjetasEmpCorpAsociadas($tipo_tarjeta,$tarjeta_padre);
		//}else{
			//$tarjetas = $this->Session->read('consultTdc.consultaTdc');
		//}

		$tarjetas = $this->Session->read('consultTdc.consultaTdc');

		// Registra la consulta del producto
		$this->Bitacora->registraBitacora($usuario['id'], 4, null, null, null, null, "Consulta Tarjeta " . $tarjeta);

		/** Para identificar si es un usuario juridico **/
		if ($usuario['tipo_rif']) {
			$identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
		} else {
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
      //$tarjetaVarias = $this->Session->read('consultTdc.tdcAS400');
      $arr = $tarjetas;

      foreach($arr as $arreglo){
			$nroTarjeta = $arreglo['nroTarjeta'];
			$soapTarjeta[$nroTarjeta] = enmascara($nroTarjeta);
		}	

      $tarjetaVarias = $soapTarjeta;      


      $this->Session->write('detalleTarjeta.tarjetaVarias', $tarjetaVarias);  


      foreach($arr as $arreglo){
      	if($tarjeta == $arreglo['nroTarjeta']){
             $arrConsultas = $arreglo;
             break;
      	}
      }
		if (isset($arrConsultas)) {
			$consultaTdc = $arrConsultas;

			$totalesTDC = array(
				'ABONOSTDC' => 0,
				'CREDITOSTDC' => 0
			);

			$arrData = array();
			$arreglo = array();
			$flag_varios = FALSE;

			$moviendotosTdc = $this->Credicard->consultaMovimientoSoap($tarjeta);

			// Verifica que el webservice traiga registros
			if (isset($moviendotosTdc) && $moviendotosTdc !== "" ) {

				for ($i = 0; $i < count($moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse']); $i++) {
					if (trim($moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['tipotxn']) !== 'C') {
						$totalesTDC['ABONOSTDC'] += $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['mtotxn'];
						$totalesTDC['CREDITOSTDC'] += '0,00';
						$arreglo[] = $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i];

						// Verifica si es un dispositivo movil
						if (esMovil() && $i >= (Configure::read('cantidad.listado.movil.tarjetas') - 1)) break;
					} else {
						$totalesTDC['ABONOSTDC'] += '0,00';
						$totalesTDC['CREDITOSTDC'] += $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['mtotxn'];
						$arreglo[] = $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i];

						// Verifica si es un dispositivo movil
						if (esMovil() && $i >= (Configure::read('cantidad.listado.movil.tarjetas') - 1)) break;
					}
				}

				$arrData = $arreglo;
			}

			// Empieza c?digo para paginar
			$total_registros = count($arrData);

			$offset = 0;

			if (
				isset($this->params['form']['offset']) &&
				preg_match('/^\d+$/', $this->params['form']['offset'])
			) {

				$offset = intval($this->params['form']['offset']);
			}

			$arrData = array_slice(
				$arrData,
				$offset,
				Configure::read('Tamano.paginacion'),
				true
			);

			// Fin c?digo para paginar
			$flag_buscar = false;
			$alias = _a($usuario['id'], $this->data['DetalleProducto']['nro_tarjeta']);

			$this->set(compact('detalle_tarjeta', 'tarjetas', 'movimientoTDC', 'totalesTDC', 'arrData', 'res', 'ruta_regreso', 'flag_buscar', 'total_registros', 'alias', 'abonos', 'consultaTdc', 'moviendotosTdc','tarjetaVarias'));

			$this->layout = 'interna';
			$this->set('seccion_actual', 'consultas.tarjetas');
			$this->Session->delete('tarjetas.ultima_consulta');
		}

		foreach($arrConsultas as $arr){
			if(trim($arr['tarjeta']) == $tarjeta){
				$consultaTdc = $arr;
				$arrData = array();
				$arreglo = array();
				$flag_varios = FALSE;
	
				$moviendotosTdc = $this->ConsultaMovimientoSoap($tarjeta);
				
				// Verifica que el webservice traiga registros
				if (isset($moviendotosTdc) && $moviendotosTdc !== "") {
	
					for ($i = 0; $i < count($moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse']); $i++) {
						if (trim($moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['tipotxn']) !== 'C') {
							$totalesTDC['ABONOSTDC'] += $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['mtotxn'];
							$totalesTDC['CREDITOSTDC'] += '0,00';
							$arreglo[] = $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i];
	
							// Verifica si es un dispositivo movil
							if (esMovil() && $i >= (Configure::read('cantidad.listado.movil.tarjetas') - 1)) break;
						} else {
							$totalesTDC['ABONOSTDC'] += '0,00';
							$totalesTDC['CREDITOSTDC'] += $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['mtotxn'];
							$arreglo[] = $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i];
	
							// Verifica si es un dispositivo movil
							if (esMovil() && $i >= (Configure::read('cantidad.listado.movil.tarjetas') - 1)) break;
						}
					}
	
					$arrData = $arreglo;
				}

			
				// Empieza c?digo para paginar
				$total_registros = count($arrData);
	
				$offset = 0;
	
				if (
					isset($this->params['form']['offset']) &&
					preg_match('/^\d+$/', $this->params['form']['offset'])
				) {
	
					$offset = intval($this->params['form']['offset']);
				}
	
				$arrData = array_slice(
					$arrData,
					$offset,
					Configure::read('Tamano.paginacion'),
					true
				);
	
				// Fin c?digo para paginar
	
				$flag_buscar = false;
	
				$alias = _a($usuario['id'], $this->data['DetalleProducto']['nro_tarjeta']);
	
				$moviendotosTdc = $this->ConsultaMovimientoSoap($tarjeta);

				$this->set(compact('detalle_tarjeta', 'tarjetas', 'movimientoTDC', 'totalesTDC', 'arrData', 'res', 'ruta_regreso', 'flag_buscar', 'total_registros', 'alias', 'abonos', 'consultaTdc', 'moviendotosTdc','tarjetaVarias'));
	
				$this->layout = 'interna';
				$this->set('seccion_actual', 'consultas.tarjetas');
				$this->Session->delete('tarjetas.ultima_consulta');
			}
		}



		

		// END Llamada del servicio TDC
	}

	/**
	 * Acci?n que muestra el detalle de la tarjeta de cr?dito corporativa
	 */
	function detalle_Emp_Corp()
	{

		// Para el regreso al index o el consolidado
		if ($this->Session->check('usuarios.volver_index')) {
			$ruta_volver = '/tarjetas/';
		} else {
			$ruta_volver = '/consultas/integral';
		}
		$this->set('ruta_regreso', $ruta_volver);

		if (isset($this->data['DetalleProducto'])) {
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if (!$this->DetalleProducto->validates(array('fieldList' => array('nro_tarjeta')))) {
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$tipo = $this->data['DetalleProducto']['tipo'];

		if (isset($this->data['DetalleProducto']['nro_tarjeta_padre'])) {
			$tarjeta = $this->data['DetalleProducto']['nro_tarjeta_padre'];
		} else {
			$tarjeta = $this->data['DetalleProducto']['nro_tarjeta'];
		}

		$tipo_tarjeta = 'C';

		//Busca las tarjetas corporativas
		$tarjetas = $this->Activo->buscarTarjetasEmpCorp($tipo);

		//Busca las tarjetas asociadas corporativas
		$tarjetas_asociadas = $this->Activo->buscarTarjetasEmpCorpAsociadas($tipo_tarjeta, $tarjeta);
		$total_registros = count($tarjetas_asociadas);

		// Registra la consulta del producto
		$this->Bitacora->registraBitacora($usuario['id'], 4, null, null, null, null, "Consulta Tarjeta " . $tarjeta);

		/** Para identificar si es un usuario juridico **/
		if ($usuario['tipo_rif']) {
			$identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
		} else {
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/

		//Consulta el detalle de la tarjeta principal		
		$res = $this->Activo->invoca(
			'SUMDSJV',
			array(
				'req' => array(
					'SUMACN' => trim($identificacion),
					'SUMFIR' => 'X'
				)
			)
		);

		if (isset($res) && !empty($res)) {
			$x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
			normaliza($x);

			foreach ($x as $i) {
				if (trim($i['SUMACN']) == trim($tarjeta)) {
					$i['ALIAS'] = _a($usuario['id'], $i['SUMACN']);
					$i['fecha_pago'] = explode("/", $i['SUMNNM']);
					$i['dia_pago'] = $i['fecha_pago'][0];
					$i['mes_pago'] = $i['fecha_pago'][1];
					$i['anio_pago'] = $i['fecha_pago'][2];
					$detalle_tarjeta = $i;
				}
			}
		}

		//Consulta el detalle de las tarjetas corporativas asociadas
		$res_asociadas = $this->Activo->invoca(
			'SUMDSJV',
			array(
				'req' => array(
					'SUMACN' => $tarjeta,
					'SUMFIR' => 'C'
				)
			)
		);

		if (isset($res_asociadas) && !empty($res_asociadas)) {
			$y = $res_asociadas['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
			normaliza($y);
		}

		//Calcula los pagos pendientes
		$abonos = $this->Activo->calcularTotalPagosPendientes(
			$identificacion,
			$tarjeta
		);

		//Consulta el detalle de cada tarjeta corporativa asociada
		$arrData = array();

		if (isset($tarjetas_asociadas)) {

			$i = 0;
			$tarjetas_asociadas = array_flip($tarjetas_asociadas);

			foreach ($tarjetas_asociadas as $t) {

				$respuesta = $this->Activo->invoca(
					'CARDSJV',
					array(
						'req' => array(
							'CARNUM' => trim($t)
						)
					)
				);

				if ($respuesta) {
					$i++;
					$arrData[$i] = $respuesta['CARDSJVReturn']['CARDSJV'];
					foreach ($y as $j) {
						if (trim($j['SUMACN']) == trim($t)) {
							$arrData[$i]['NOMBRE'] = $j['SUMDSC'];
						}
					}
				} else {
					$arrData = array(
						'CARCRL' => '',
						'CARAVC' => '',
						'CARBAL' => '',
						'CARED1' => '',
						'CARLB1' => '',
						'CARPMA' => '',
						'CARED2' => '',
						'CARLB2' => '',
						'CARLSB' => '',
						'CARED3' => '',
						'CARLB3' => '',
					);
				}
			}
		}

		$flag_varios = FALSE;

		//		// Empieza c?digo para paginar		

		$offset = 0;

		if (
			isset($this->params['form']['offset']) &&
			preg_match('/^\d+$/', $this->params['form']['offset'])
		) {
			$offset = intval($this->params['form']['offset']);
		}

		$arrData = array_slice(
			$arrData,
			$offset,
			Configure::read('Tamano.paginacion'),
			true
		);
		//		// Fin c?digo para paginar

		$flag_buscar = false;

		$this->set(compact('tarjeta', 'detalle_tarjeta', 'tarjetas', 'tarjetas_asociadas', 'ruta_regreso', 'flag_buscar', 'total_registros', 'abonos', 'arrData', 'tipo'));

		$this->layout = 'interna';

		$this->set('seccion_actual', 'consultas.tarjetas');

		$this->Session->delete('tarjetas.ultima_consulta');
	}

	/**
	 * Acci?n que maneja el formulario de b?squeda de movimientos
	 * de las tarjetas
	 * @param string $volver Indica si esta regresando
	 */
	function buscar($volver = '')
	{
		$this->layout = 'interna';
		$this->set('seccion_actual', 'consultas.tarjetas');

		if ($this->Session->check('tarjetas.busqueda') && !empty($volver)) {
			$this->data = $this->Session->read('tarjetas.busqueda');
			$this->Session->delete('tarjetas.busqueda');
		}

		if (!empty($this->data['BusquedaCuenta'])) {
			$this->BusquedaCuenta->set($this->data);
			$this->data['DetalleProducto']['nro_tarjeta'] = $this->data['BusquedaCuenta']['nro_tarjeta'];
			$this->data['DetalleProducto']['nro_tarjeta_padre'] = $this->data['BusquedaCuenta']['nro_tarjeta_padre'];
			$this->data['DetalleProducto']['tipo'] = $this->data['BusquedaCuenta']['tipo'];


			$tarjeta = $this->data['BusquedaCuenta']['nro_tarjeta'];

			$usuario = $this->Session->read('usuarios.datos');
			$tarjetas = $this->Session->read('detalleTarjeta.tarjetaVarias');
			$arr = $this->Session->read('consultTdc.tarjetasTdc');
			$tarjetaVarias = $tarjetas;

         $$consultaTdc = array(); 

         //datos Tarjeta 
	      foreach($arr as $arreglo){
	      	if($tarjeta == $arreglo['nroTarjeta']){
	             $arrConsultas = $arreglo;
	             break;
	      	}
	      }

			if(count($arrConsultas) > 0){
				$consultaTdc = $arrConsultas;
			}	


         $mes = $this->data['BusquedaCuenta']['meses'];  
         $anio = $this->data['BusquedaCuenta']['anio']; 
         $moviendotosTdc = $this->Credicard->consultaMovimientoSoap($tarjeta, $mes, $anio);

			$this->Session->write('tarjetas.ultima_consulta', $parametros_consulta);
			$this->Session->write('tarjetas.busqueda', $this->data);

			$arrData = array();
			$arreglo = array();
			$flag_varios = FALSE;

			
			// Verifica que el webservice traiga registros
			if (isset($moviendotosTdc) && $moviendotosTdc !== "" ) {

				for ($i = 0; $i < count($moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse']); $i++) {
					if (trim($moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['tipotxn']) !== 'C') {
						$totalesTDC['ABONOSTDC'] += $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['mtotxn'];
						$totalesTDC['CREDITOSTDC'] += '0,00';
						$arreglo[] = $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i];

						// Verifica si es un dispositivo movil
						if (esMovil() && $i >= (Configure::read('cantidad.listado.movil.tarjetas') - 1)) break;
					} else {
						$totalesTDC['ABONOSTDC'] += '0,00';
						$totalesTDC['CREDITOSTDC'] += $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['mtotxn'];
						$arreglo[] = $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i];

						// Verifica si es un dispositivo movil
						if (esMovil() && $i >= (Configure::read('cantidad.listado.movil.tarjetas') - 1)) break;
					}
				}
				$arrData = $arreglo;
			}

			// Empieza c?digo para paginar
			$total_registros = count($arrData);

			$offset = 0;

			if (
				isset($this->params['form']['offset']) &&
				preg_match('/^\d+$/', $this->params['form']['offset'])
			) {

				$offset = intval($this->params['form']['offset']);
			}

			$arrData = array_slice(
				$arrData,
				$offset,
				Configure::read('Tamano.paginacion'),
				true
			);

			// Fin c?digo para paginar
			$flag_buscar = false;
			$alias = _a($usuario['id'], $this->data['DetalleProducto']['nro_tarjeta']);


				$this->set(compact('totalesTDC', 'arrData', 'total_registros', 'ruta_regreso', 'lag_buscar', 'alias', 'abonos', 'moviendotosTdc', 'consultaTdc','tarjetaVarias'));
				$this->render('detalle');
			//}
		} else if (isset($this->data['DetalleProducto'])) {
			$this->data['BusquedaCuenta']['nro_tarjeta'] = $this->data['DetalleProducto']['nro_tarjeta'];
			$this->data['BusquedaCuenta']['nro_tarjeta_padre'] = $this->data['DetalleProducto']['nro_tarjeta_padre'];
			$this->data['BusquedaCuenta']['tipo'] = $this->data['DetalleProducto']['tipo'];
		}

		$t2 = strtotime('-1 month');

		setlocale(LC_ALL, 'es_VE.UTF-8');

     	$meses = array(); 
      $meses[1] = 'ENERO';
      $meses[2] = 'FEBRERO';
      $meses[3] = 'MARZO';
      $meses[4] = 'ABRIL';
      $meses[5] = 'MAYO';
      $meses[6] = 'JUNIO';
      $meses[7] = 'JULIO';
      $meses[8] = 'AGOSTO';      	
      $meses[9] = 'SEPTIEMBRE';
      $meses[10] = 'OCTUBRE';
      $meses[11] = 'NOVIEMBRE';
      $meses[12] = 'DICIEMBRE';

      $anios = date('Y'); 


		$this->set(compact('meses','anios'));
	}

	/**
	 * Acci?n utilizada para mostrar los pagos retenido y/o pendientes
	 **/
	function pagos_pendientes()
	{

		// Para el regreso al detalle de tarjetas corporativas o comunes
		if ($this->data['DetalleProducto']['tipo'] == 'TCC' && (!$this->Session->check('usuarios.volver_corporativa_pp'))) {
			$ruta_volver = '/tarjetas/detalle_emp_corp';
		} else {
			$ruta_volver = '/tarjetas/detalle';
		}
		$this->set('ruta_regreso', $ruta_volver);
		// Fin

		if (isset($this->data['DetalleProducto'])) {
			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if (!$this->DetalleProducto->validates(array('fieldList' => array('nro_cuenta')))) {
				$this->redirect('index');
			}
		} else {
			$this->redirect('index');
		}

		$usuario = $this->Session->read('usuarios.datos');
		$tarjeta = trim(substr($this->data['DetalleProducto']['nro_tarjeta'], 0, 16));
		$tarjeta_padre = trim($this->data['DetalleProducto']['nro_tarjeta_padre']);
		$tipo = $this->data['DetalleProducto']['tipo'];

		//Verifica si es una corporativa y segun eso realiza la busqueda de tarjetas
		if ($tipo == 'TCC') {

			$tipo_tarjeta = 'C';

			if (trim($tarjeta) == trim($tarjeta_padre)) {
				//Busca las tarjetas corporativas segun el tipo
				$tarjetas = $this->Activo->buscarTarjetasEmpCorp($tipo);
			} else {
				//Busca las tarjetas asociadas corporativas segun el tipo
				$tarjetas = $this->Activo->buscarTarjetasEmpCorpAsociadas($tipo_tarjeta, $tarjeta_padre);
			}
		} else {
			$tarjetas = $this->Session->read('detalleTarjeta.tarjetaVarias');
		}

		/** Para identificar si es un usuario juridico **/
		if ($usuario['tipo_rif']) {
			$identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
		} else {
			$identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/

		$res = $this->Activo->invoca(
			'CCARDTRJV',
			array(
				'req' => array(
					'CCATRNUM' => $tarjeta,
					'CCATROPC' => 'TR'
				)
			)
		);

		$totalesAbono = array(
			'ABONOSTDC' => 0
		);

		$pagos_pendientes = array();

		if ($res && isset($res['CCARDTRJVReturn']['CCARDTRJV']['CCARDTRJV'])) {
			$x = $res['CCARDTRJVReturn']['CCARDTRJV']['CCARDTRJV'];
			normaliza($x);

			foreach ($x as $i) {
				if (trim($i['h01FLGWK3'] == 'X') && trim($i['CCATRAMCR'] != 0.00)) {
					$pagos_pendientes[] = $i;
					$totalesAbono['ABONOSTDC'] += $i['CCATRAMCR'];
				}
			}
		}

		$this->set(compact('tarjeta', 'tarjetas', 'pagos_pendientes', 'totalesAbono', 'ruta_regreso'));
		$this->layout = 'interna';
		$this->set('seccion_actual', 'consultas.tarjetas');
	}

	/**
	 * Acci?n para descargar el estado de cuenta hist?rico de la tarjeta
	 **/
	function estado_tarjeta()
	{

		// Para el regreso al detalle de tarjetas corporativas o comunes
		if ($this->data['DetalleProducto']['tipo'] == 'TCC' && (!$this->Session->check('usuarios.volver_corporativa_pp'))) {
			$ruta_volver = '/tarjetas/detalle_emp_corp';
		} else {
			$ruta_volver = '/tarjetas/detalle';
		}
		$this->set('ruta_regreso', $ruta_volver);
		// Fin

		$this->layout = 'interna';




		



		if (!empty($this->data['EstadoCuentaTdc'])) {
			$this->EstadoCuentaTdc->set($this->data);
			$usuario = $this->Session->read('usuarios.datos');
			$datos_cliente = $this->Session->read('usuarios.nibpf3010');


			if ($this->EstadoCuentaTdc->validates()) {

				$consulta_tarjeta = $this->Activo->invoca(
					'IN098101',
					array(
						'req' => array(
							'IN981USR' => str_pad($datos_cliente['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'], 10, 0, STR_PAD_LEFT)
						)
					)
				);

				$referencial = '';
				if ($consulta_tarjeta && isset($consulta_tarjeta['IN098101Return']['IN098101']['IN098101'])) {
					$arrData = $consulta_tarjeta['IN098101Return']['IN098101']['IN098101'];
					normaliza($arrData);
					foreach ($arrData as $tarjeta) {
						if ($this->data['EstadoCuentaTdc']['nro_tarjeta'] == trim($tarjeta['IN981CRNUM'])) {
							$referencial = trim($tarjeta['IN981NICKN']);
							break;
						}
					}
				}

				if ($referencial) {
					$res = $this->Credicard->buscarEstadoCuenta(
						$referencial,
						$this->data['EstadoCuentaTdc']['mes']
					);

					if ($res) {

						if (isset($res['return']['documento'])) {
							$data = base64_decode($res['return']['documento']);

							header('Content-Type: application/x-download');
							header('Content-Length: ' . strlen($data));
							header('Content-Disposition: attachment; filename="' . desencriptar($usuario['cedula']) . '-' . date('ymdhis') . '.pdf"');
							header('Cache-Control: private, max-age=0, must-revalidate');
							header('Pragma: public');

							echo $data;
							exit;
						} else {

							$this->EstadoCuentaTdc->validationErrors['mes'] = _m('edo.cuenta.no.existe');
							$this->log('Error conexion credicard - ' . $referencial, LOG_ERR);
						}
					}
				} else {
					$this->EstadoCuentaTdc->validationErrors['mes'] = _m('edo.cuenta.no.existe');
					$this->log('Error en consulta servicio IN098101 - ' . $this->data['EstadoCuentaTdc']['nro_tarjeta'], LOG_ERR);
				}
			}
		} else if (isset($this->data['DetalleProducto'])) {
			$this->data['EstadoCuentaTdc']['nro_tarjeta'] = $this->data['DetalleProducto']['nro_tarjeta'];
			$this->data['EstadoCuentaTdc']['nro_tarjeta_padre'] = $this->data['DetalleProducto']['nro_tarjeta_padre'];
			$this->data['EstadoCuentaTdc']['tipo'] = $this->data['DetalleProducto']['tipo'];
		} else {
			$this->redirect('index');
		}

		$tarjeta_padre = trim($this->data['EstadoCuentaTdc']['nro_tarjeta_padre']);
		$tipo = $this->data['EstadoCuentaTdc']['tipo'];

		//Si es una tarjeta corporativa busca las tarjetas asociadas
		if ($tipo == 'TCC') {

			$tipo_tarjeta = 'C';

			if (trim($this->data['EstadoCuentaTdc']['nro_tarjeta']) == trim($tarjeta_padre)) {
				//Busca las tarjetas corporativas
				$tarjetas = $this->Activo->buscarTarjetasEmpCorp($tipo);
			} else {
				//Busca las tarjetas asociadas corporativas
				$tarjetas = $this->Activo->buscarTarjetasEmpCorpAsociadas($tipo_tarjeta, $tarjeta_padre);
			}
		} else {
			$tarjetas = $this->Activo->buscarTarjetas();
		}

		$meses = array();

		$t1 = strtotime(date('Y-m-1'));

		for ($i = 1; $i < 13; $i++) {
			$t = strtotime("-$i months", $t1);
			setlocale(LC_ALL, 'es_VE.UTF-8');
			$meses[strval($t)] = ucwords(strftimeAW('%B %Y', $t));
		}

		$this->set(compact('meses', 'tarjetas'));
	}

	/**
	 * Acci?n que exporta los movimientos de la tarjeta
	 **/
	function exportar()
	{

		$this->layout = 'interna';
		$this->set('seccion_actual', 'consultas.tarjetas');

		if (isset($this->data['DetalleProducto'])) {
            

			$this->DetalleProducto->set($this->data['DetalleProducto']);

			if (!$this->DetalleProducto->validates(array('fieldList' => array('nro_cuenta')))) {
				$this->redirect('index');
			}

			$this->data['ExportarTarjeta']['nro_tarjeta'] = $this->data['DetalleProducto']['nro_tarjeta'];
			$this->data['ExportarTarjeta']['nro_tarjeta_padre'] = $this->data['DetalleProducto']['nro_tarjeta_padre'];
			$this->data['ExportarTarjeta']['tipo'] = $this->data['DetalleProducto']['tipo'];

		} else {


			if (!empty($this->data['ExportarTarjeta'])) {

				$this->ExportarTarjeta->set($this->data);

				if ($this->ExportarTarjeta->validates()) {

					$totalesTDC['ABONOSTDC'] = 0;

					$totalesTDC['CREDITOSTDC'] = 0;

					$arrData = array();

					$arreglo = array();

					$usuario = $this->Session->read('usuarios.datos');

               


					if ($this->Session->check('tarjetas.ultima_consulta')) {
						$parametros_consulta = $this->Session->read('tarjetas.ultima_consulta');
					} else {
						/** Para identificar si es un usuario juridico **/
						if ($usuario['tipo_rif']) {
							$identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']);
						} else {
							$identificacion = desencriptar($usuario['cedula']);
						}
						/** Para identificar si es un usuario juridico **/

						 $parametros_consulta = array(
							'req' => array(
								'CCATRNUM' => $this->data['ExportarTarjeta']['nro_tarjeta'],
								'CCATROPC' => 'TR'
							)
						   );
						   
					}

					$moviendotosTdc = $this->Credicard->ConsultaMovimientoSoap($this->data['ExportarTarjeta']['nro_tarjeta']); 

					if (isset($moviendotosTdc) && $moviendotosTdc !== "" ) {
						/*$arrData = $parametros_consulta['CCARDTRJVReturn']['CCARDTRJV']['CCARDTRJV'];
						normaliza($arrData);
						$arrData = array_reverse($arrData);
						*/
						//Totales montos TDC

                  $arrData = $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse']; 


						for ($i = 0; $i < count($moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse']); $i++){ 


								$totalesTDC['CREDITOSTDC'] += '0,00';
								//$arreglo[] = $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i];

								// Verifica si es un dispositivo movil
								if (esMovil() && $i >= (Configure::read('cantidad.listado.movil.tarjetas') - 1)) break;
						}		
					} else {
								$totalesTDC['ABONOSTDC'] += '0,00';
								$totalesTDC['CREDITOSTDC'] += $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i]['mtotxn'];
								//$arreglo[] = $moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'][$i];

								// Verifica si es un dispositivo movil
								if (esMovil() && $i >= (Configure::read('cantidad.listado.movil.tarjetas') - 1)) break;
					}	


					$res = true;

					if ($res) {
						// Verifica que el webservice traiga registros
						//if (isset($res['CCARDTRJVReturn']['CCARDTRJV']['CCARDTRJV'])) {
						//	$arrData = $res['CCARDTRJVReturn']['CCARDTRJV']['CCARDTRJV'];
							//normaliza($arrData);
							//Totales montos TDC

							for ($i = 0; $i < count($arrData); $i++) {
                        $credito = 0;
                        $abono = 0; 

                        
								if (trim($arrData[$i]['tipotxn']) == 'C') {
                            $totalesTDC['CREDITOSTDC'] += $arrData[$i]['mtotxn'];  
                            $credito =  $arrData[$i]['mtotxn'];
								}else{
									 $abono =  $arrData[$i]['mtotxn'];
									 $totalesTDC['ABONOSTDC'] += $arrData[$i]['mtotxn'];  
								}	


								for ($c = 0; $c < count($this->data['ExportarTarjeta']['campos']); $c++) {
									$campo = $this->data['ExportarTarjeta']['campos'][$c];

									switch ($campo) {
										case 'f':

											$arreglo[$i][$campo] = formato_fecha_exportar($this->data['ExportarTarjeta']['formatoFecha'], $dia, $mes, $anio);
												break;

											case 'r':

												$arreglo[$i][$campo] = strval(trim($arrData[$i]['nroref']));

												if ($this->data['ExportarTarjeta']['formato'] == 't') $arreglo[$i][$campo] .= '    ';
												else $arreglo[$i][$campo] .= ' ';

												break;

											case 'd':
												$arreglo[$i][$campo] = trim($arrData[$i]['conceptotxn']);
												break;

											case 'c':
												if ($this->data['ExportarTarjeta']['formato'] != 't') {
													$arreglo[$i][$campo] = formato_monto_exportar($this->data['ExportarTarjeta']['formatoMonto'], $credito) . ' ';
												} else {

													$arreglo[$i][$campo] = formato_monto_exportar($this->data['ExportarTarjeta']['formatoMonto'], $credito) . '    ';

												}
												break;

											case 'a':
												if ($this->data['ExportarTarjeta']['formato'] != 't') {
													$arreglo[$i][$campo] = formato_monto_exportar($this->data['ExportarTarjeta']['formatoMonto'], $abono) . ' ';
												} else {
													$arreglo[$i][$campo] = formato_monto_exportar($this->data['ExportarTarjeta']['formatoMonto'], $abono) . '    ';
												}
												break;
										}
									}
								//}
							//}
						}
					}



					$this->layout = 'nada';

					$this->set(compact('arreglo', 'totalesTDC'));

					if ($this->data['ExportarTarjeta']['formato'] == 't') {
						$this->render('exportar_txt');
					} else {
						$this->render('exportar_excel');
					}
				}
			}




		}
	}


   function tipoTarjeta($nroTarjeta, $documento){
		$res = '';

		// Verificacion de la consulta de los datos del servicio
		$res = $this->Activo->invoca(
			'SUMDSJV',array( 
				'req'=>array(
					'SUMACN'=>$documento,
					'SUMFIR'=>'X'
				)
			)
		);

		$x = array();
		if($res){
			$x = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
		} 

     /* Borrar al tener la data correcta.*/
		$x[] = array(
				'SUMTYP' => 'CC&',
				'SUMACN' => '4744990122025856'
		);
		

		$x[] = array(
				'SUMTYP' => 'TCC',
				'SUMACN' => '5466290112013605'
		);

		/* Fin Borrar al tener la data correcta.*/



		$tipoTarjeta = "";

		foreach($x as $i){
          if(trim($i['SUMACN']) == trim($nroTarjeta)){
              $tipoTarjeta = $i['SUMTYP'];          	   
              break;
          }
		}  
		return $tipoTarjeta;
   }



}