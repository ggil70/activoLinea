<?php
/**
 * Controlador para todas las operaciones de cash management 
 */

/**
 * Controlador para todas las operaciones de cash management 
 */
class CashController extends AppController{
	/**
	 *
	 * @var array Modelos que usa este controlador 
	 */
	var $uses = array(
		'TransaccionPendiente',
		'TransaccionBeneficiario', 
		'TransaccionJuridicoDetalle',
		'DetallePagoProveedor',
		'Bitacora',
		'PagoCash',
        'Parametros',
		'AfiliacionCash',
		'AprobacionTransaccion',
		'AcumuladoTransaccion',
        'Empresa',
        'UsuarioCanal',
        'LimitesVip',
        'NexpLogsError'
	);
	
	/**
	 *
	 * @var array Componentes que usa este controlador
	 */
	var $components = array('Activo', 'Session', 'Email', 'Sms', 'Compensacion');
	
	/**
	 *
	 * @var array Ayudantes que usan las vistas de este controlador
	 */
	var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');
        
        /**
     * Accin que muestra un listado con los ltimos pagos de nomina y proveedores
     * realizados por la empresa
     */
    function seguimiento_gestion_filtros() {

        // Valida el permiso de la transaccion
        if (!validarTransaccion('43'))
            $this->redirect('/consultas/integral');

        $this->layout = 'interna';
        $this->set('seccion_actual', 'cash.seguimiento_gestion');
        $usuario = $this->Session->read('usuarios.datos');

        $tipos = array(
            'n' => 'Pago de Nomina',
            'p' => 'Pago a Proveedores',
            'z' => 'Pago de Nomina Proveedores Express',
            'm' => 'Pago de Nomina Moneda Extranjera',
            'x' => 'Pago a Proveedores Moneda Extranjera'
        );

        $estatus = array(
            'Pendiente por Procesar' => 'Pendiente por Procesar',
            'Procesado' => 'Procesado',
            'Procesado Parcialmente' => 'Procesado Parcialmente',
            'Procesado con Rechazos' => 'Procesado con Rechazos'
        );

        

        if (!empty($this->data['TransaccionPendiente'])) {

            $this->TransaccionPendiente->set($this->data);
            $this->Session->write('cash.seguimiento', $this->data);
            $this->redirect('seguimiento_gestion');
        }

        $this->set(compact('tipos', 'estatus'));
    }

    /**
     * Accion que muestra un listado con los ultimos pagos de nomina y proveedores
     * realizados por la empresa
     */
    function seguimiento_gestion() {

        // Valida el permiso de la transaccion
        if (!validarTransaccion('43'))
            $this->redirect('/consultas/integral');

        $usuario = $this->Session->read('usuarios.datos');
        if ($this->Session->check('cash.seguimiento')) {
            $data = $this->Session->read('cash.seguimiento');
        } else {
            $this->redirect('seguimiento_gestion_filtros');
        }

        $data = $data['TransaccionPendiente'];


        // Condiciones para la busqueda de transacciones guardadas

        if (!empty($data['lote'])) {
            $conditions['TransaccionPendiente.lote'] = $data['lote'];
        } else {

            if (!empty($data['operacion'])) {
                if ($data['operacion'] == 'n' || $data['operacion'] == 'm' || $data['operacion'] == 'x') {

                    $conditions['TransaccionPendiente.tipo'] = array('nxa', 'nxm', 'nxo', 'mxm','xxm');

                } else {
                    $conditions['TransaccionPendiente.tipo'] = array('vxa', 'vxm', 'vxo', 'xxm','xxm');
                }
            } else {
                $conditions['TransaccionPendiente.tipo'] = array('vxa', 'vxm', 'vxo', 'nxa', 'nxm', 'nxo', 'mxm', 'xxm');
            }

            $conditions['TransaccionPendiente.fecha_hora_registro >='] = formatea_fecha_toMSSQL(
                $data['fechaDesde']['day'], $data['fechaDesde']['month'], $data['fechaDesde']['year']
                    ) . ' 00:00:00.000';

            $conditions['TransaccionPendiente.fecha_hora_registro <='] = formatea_fecha_toMSSQL(
                $data['fechaHasta']['day'], $data['fechaHasta']['month'], $data['fechaHasta']['year']
                    ) . ' 23:59:59.000';
        }

        $conditions['TransaccionPendiente.rif'] = $usuario['rif'];
        $conditions['TransaccionPendiente.tipo_rif'] = $usuario['tipo_rif'];
        $conditions['TransaccionPendiente.estatus not '] = array('g', 'pa', 'el');

        clearCache();

        $listado = $this->TransaccionPendiente->find(
            'all', array(
            'conditions' => $conditions,
            'order' => 'TransaccionPendiente.fecha_hora_registro desc',
                )
        );




        $tipo_lote = array();

        if ($listado) {

            foreach ($listado as &$i) {
                // Completar informacion con el AS400
                $this->Activo->seguimientoGestionCabecera($i);
            }

            $tipo_lote['n'] = array();
            $tipo_lote['v'] = array();
            $tipo_lote['m'] = array();
            $tipo_lote['x'] = array();

            
            foreach ($listado as $m => $d) {

                if (!empty($data['estatus'])) {
                    if ($data['estatus'] == $d['TransaccionPendiente']['descripcion_estatus']) {
                        $tipo_lote[$d['TransaccionPendiente']['tipo'][0]] [] = $d;
                    }
                } else {

                    $tipo_lote[$d['TransaccionPendiente']['tipo'][0]] [] = $d;
                }
            }

            
            // si no contiene data el tipo de transaccion se elimina
            foreach ($tipo_lote as $k => $v)
                if (!count($tipo_lote[$k]))
                    unset($tipo_lote[$k]);
        }


        $this->layout = 'interna';

        $this->set(compact('listado', 'tipo_lote'));
        $this->set('seccion_actual', 'cash.seguimiento_gestion');
    }
	 /**
     * Accion que muestra el detalle de un pago y permite descargar el mismo
     * en formato PDF y Excel
     *
     */
    function seguimiento_gestion_detalle() {

        // Valida el permiso de la transaccion
        if (!validarTransaccion('43'))
            $this->redirect('/consultas/integral');

        $usuario = $this->Session->read('usuarios.datos');

        if (!isset($this->data['TransaccionPendiente'])) {
            $this->redirect('seguimiento_gestion');
        }


        /* Se recibe la informacion del detalle de cada usuario  al pulsar el boton con el id #descarga_pdf */
        if(isset($this->data['TransaccionPendiente']['beneficiarios'])){
            if(!empty($this->data['TransaccionPendiente']['beneficiarios'])){
                $beneficiarios = explode(",", $this->data['TransaccionPendiente']['beneficiarios']);
            }
        }
      
        if (isset($this->data['TransaccionPendiente']['formato'])) {
            $formato = $this->data['TransaccionPendiente']['formato'];
        }

        $this->Session->write('error_express.id_transaccion',$this->data['TransaccionPendiente']['id']);
        $var =   $this->Session->read('error_express.id_transaccion') ;
        
        $data = $this->TransaccionPendiente->findById($this->data['TransaccionPendiente']['id']);




        $dataRechazo  = $this->NexpLogsError->find(
                            'all', array(
                                'conditions' => array(
                                    'transaccion_id' => $this->data['TransaccionPendiente']['id']
                                )
                            )
                        );


        $dataRechazoAux = $dataRechazo;


        // Completar informacion con el AS400
        
        $this->data = $this->Activo->seguimientoGestion($data);
        $dataResumen =$data;                        
        $this->data = $data;
        $this->data['TransaccionPendiente']['dataRechazo'] = $dataRechazoAux;
        
        if (
                $this->data['TransaccionPendiente']['rif'] != $usuario['rif'] ||
                $this->data['TransaccionPendiente']['tipo_rif'] != $usuario['tipo_rif']
        ) {

            $this->redirect('seguimiento_gestion');
        }

        if (isset($formato)) {
            $this->layout = 'nada';

            // Verifica si es un pago masivo de proveedores
           if($this->data['TransaccionPendiente']['tipo_carga']  == 'a' || ($this->data['TransaccionPendiente']['tipo_carga']  == 'a' && $this->data['TransaccionPendiente']['tipo'][0] == 'vxo') ){
                    if(count($beneficiarios)>0 && (count($beneficiarios)<count($this->data['Beneficiarios']))){
                        $beneficiariosFiltrados =array();

                        for ($i = 0; $i < count($beneficiarios); $i++) {
                            for ($j = 0; $j < count($this->data['Beneficiarios']); $j++) {
                                if (intval($beneficiarios[$i]) == $this->data['Beneficiarios'][$j]['id']) {
                                    $detalle = $this->DetallePagoProveedor->find(
                                        'all', array(
                                            'conditions' => array(
                                                'beneficiario_id' => intval($beneficiarios[$i])
                                            )
                                        )
                                    );
                                    if ($detalle) {
                                        $this->data['Beneficiarios'][$j]['detalle'] = $detalle; 
                                    }
                                    // Agregar al nuevo array solo los beneficiarios válidos
                                    $beneficiariosFiltrados[] = $this->data['Beneficiarios'][$j];
                                }
                            }
                        } 
                        // Reemplazar el array original por el filtrado
                        $this->data['Beneficiarios'] = $beneficiariosFiltrados;    
                                   
                    }else{
                        if (count($this->data['Beneficiarios'])) {
                            for ($i = 0; $i < count($this->data['Beneficiarios']); $i++) {
                            
                                // Verifica si la instruccion de pago tiene detalle #descarga_pdf
                                $detalle = $this->DetallePagoProveedor->find(
                                    'all', array(
                                        'conditions' => array(
                                            'beneficiario_id' => $this->data['Beneficiarios'][$i]['id']
                                        )
                                    )
                                );
        
                                if ($detalle)
                                    $this->data['Beneficiarios'][$i]['detalle'] = $detalle; 
                            
                            }
                        }
                    }
                }  
                
            if($this->data['TransaccionPendiente']['tipo_carga']  == 'm' || ($this->data['TransaccionPendiente']['tipo_carga']  == 'a' && $this->data['TransaccionPendiente']['tipo'][0] == 'vxo') ){
                /* Si count es true en $this->data['Beneficiarios']  entonces es un pago masivo de proveedores y almacena y cuenta cada uno de ellos en  #descarga_pdf  */
                if(count($beneficiarios)>0 && (count($beneficiarios)<count($this->data['Beneficiarios']))){
                    $beneficiariosFiltrados =array();

                    for ($i = 0; $i < count($beneficiarios); $i++) {
                        for ($j = 0; $j < count($this->data['Beneficiarios']); $j++) {
                            if (intval($beneficiarios[$i]) == $this->data['Beneficiarios'][$j]['id']) {
                                $detalle = $this->DetallePagoProveedor->find(
                                    'all', array(
                                        'conditions' => array(
                                            'beneficiario_id' => intval($beneficiarios[$i])
                                        )
                                    )
                                );
                                if ($detalle) {
                                    $this->data['Beneficiarios'][$j]['detalle'] = $detalle; 
                                }
                                // Agregar al nuevo array solo los beneficiarios válidos
                                $beneficiariosFiltrados[] = $this->data['Beneficiarios'][$j];
                            }
                        }
                    }

                    // Reemplazar el array original por el filtrado
                    $this->data['Beneficiarios'] = $beneficiariosFiltrados;                 
                }else{
                    
                    if (count($this->data['Beneficiarios'])) {
                        for ($i = 0; $i < count($this->data['Beneficiarios']); $i++) {
                           
                            // Verifica si la instruccion de pago tiene detalle #descarga_pdf
                            $detalle = $this->DetallePagoProveedor->find(
                                'all', array(
                                    'conditions' => array(
                                        'beneficiario_id' => $this->data['Beneficiarios'][$i]['id']
                                    )
                                )
                            );
    
                            if ($detalle)
                                $this->data['Beneficiarios'][$i]['detalle'] = $detalle; 
                         
                        }
                    }
                }
            }
            
            /* Obtener datos que seran utilizados para renderizar PDF #descarga_pdf  */
            $bancos = $this->Activo->buscarBancos(); 
            $dataPDF = ($this->data['Beneficiarios']);
            $tipoDocumento =  $usuario['tipo_rif'] ;
            $nroDocumento = desencriptar($usuario['rif']); 
            
            //Consulta basica del Beneficiario         
                $datos_origen= $this->Activo->invoca(
                    'NIBPF3010',
                    array(
                        'req'=>array(
                            'NACIONALIDAD'=>$tipoDocumento,
                            'NROIDENTIFICACION'=>str_pad($nroDocumento,12,'0',STR_PAD_LEFT),
                            'NROPARAM'=>'1'
                        )
                    )
            );  
             if ($formato == 'txt') {
                $this->render('seguimiento_gestion_descarga_txt');
            } elseif($formato == 'pdf') {
               $this->set(compact('dataResumen','dataPDF', 'bancos', 'usuario', 'datos_origen' )); 
                $this->render('seguimiento_gestion_descarga_pdf');
            }
            else{    
                $this->render('seguimiento_gestion_descarga_xls');
            }
        } else {
            $bancos = $this->Activo->buscarBancos();
            $this->set(compact('bancos', 'tipo_lote','dataRechazoAux' ));
            $this->layout = 'interna';
            $this->set('seccion_actual', 'cash.seguimiento_gestion');
        }
    }
	
	/**
	 * Accion para mostrar el listado de pagos de n/a o proveedores
	 * @param string $tipo Indica el tipo de los pagos a mostrar. 'n' para n?a,
	 * 'p' para proveedores
	 */
	function pagos($tipo=null){
		if( empty($tipo) || !in_array($tipo, array('n','p')) ) $this->redirect('/consultas/integral');
		
		// Valida el permiso de la transaccion
		if(
			($tipo=='n' && (!validarTransaccion(39,100016) || verificaAfiliados('n'))) ||
			($tipo=='p' && (!validarTransaccion(40,100017) || verificaAfiliados('p')))
		) $this->redirect('/consultas/integral');
		
		$usuario = $this->Session->read('usuarios.datos');
		
		$this->Session->delete('cash.pagos.descarga');
		$this->Session->delete('cash.pagos.operacion');
		$this->Session->delete('cash.pagos.data');
		
		if(!empty($this->data['PagoCash']) && !isset($this->data['PagoCash']['paginando'] ) ){
			$this->PagoCash->set($this->data);
			
			if($this->PagoCash->validates(array('fieldList'=>array('accion','seleccion')))){
	
				switch($this->data['accion']){
					case 'crear':
						$this->Session->write('cash.pagos.operacion', 
							array(
								'motivo'=>'crear',
								'tipo'=>$tipo,
								'url_volver_modificar'=>'pagos/'.$tipo
							)
						);
						$this->redirect('pago_modificar');
						break;
					
					case 'detalle':
						$estado = $this->PagoCash->buscar($this->data['PagoCash']['id'],$usuario['id']);

						if($estado){
							$this->Session->write('cash.pagos.operacion', 
								array(
									'tipo'=>$tipo,
									'url_volver'=>'pagos/'.$tipo
								)
							);
							$this->Session->write('cash.pagos.data', $estado);
							$this->redirect('pago_detalle');
						}
						break;
					
					case 'eliminar':
						$this->Session->write('cash.pagos.operacion', 
							array(
								'tipo'=>$tipo,
								'url_volver'=>'pagos/'.$tipo,
								'seleccion'=>$this->data['seleccion']
							)
						);
						$this->redirect('pago_eliminar');
						break;
				}
			}
		}
		
		// Condiciones para la busqueda de transacciones guardadas
		$conditions = array(
			'TransaccionPendiente.usuario_id' => $usuario['id'],
			'TransaccionPendiente.estatus' => 'g',
		);
		
		if($tipo=='n'){
			$conditions['TransaccionPendiente.tipo'] = array('nxa','nxm','nxo');
		}
		else{
			$conditions['TransaccionPendiente.tipo'] = array('vxa','vxm','vxo');
		}
		
		// Paginacion
		$total_registros = $this->TransaccionPendiente->find(
			'count',
			array( 'conditions'=>$conditions )
		);	
		
		
		$offset = 0;

		if(
			isset($this->data['PagoCash']['offset']) &&
			preg_match('/^\d+$/',$this->data['PagoCash']['offset'])
		){
			$offset = intval($this->data['PagoCash']['offset']);			
		}
		
		$listado = array();
		
		if($total_registros){
			$listado = $this->TransaccionPendiente->find(
				'all',
				array(
					'fields'=>array('id','lote','cuenta_origen','fecha_hora_valor','descripcion','monto','UsuarioCreador.login'),
					'conditions'=>$conditions,
					'order'=>'TransaccionPendiente.id desc',
					'offset'=>$offset,
					'limit'=>10
				)
			);
		}
		
		$this->layout = 'interna';
		
		switch($tipo){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos N/a');
				$this->set('encabezado_vista','pagos de n/a');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores');
				$this->set('encabezado_vista','pagos de proveedores');
				break;
		}
		
		if($this->Session->check('cash.pago.exito')){
			$pago_guardado = $this->Session->read('cash.pago.exito');
			$this->set('pago_guardado',$pago_guardado);
			$this->Session->delete('cash.pago.exito');
		}
		
		$this->set(compact('listado','tipo','total_registros'));
	}
	 //js 06/10/2020
    /**
     * Acción para mostrar el listado de pagos de nómina o proveedores Moneda Extranjera
     * @param string $tipo Indica el tipo de los pagos a mostrar. 'm' para nómina,
     * 'x' para proveedores
     */
    function pagos_me($tipo = null) {
	

        if (empty($tipo) || !in_array($tipo, array('m', 'x')))
            $this->redirect('/consultas/integral');

        // Valida el permiso de la transaccion
        if (
                ($tipo == 'm' && (!validarTransaccion(101072) || verificaAfiliados('m'))) ||
                ($tipo == 'x' && (!validarTransaccion(101073) || verificaAfiliados('x')))
        )
            $this->redirect('/consultas/integral');
           
        $usuario = $this->Session->read('usuarios.datos');

        $this->Session->delete('cash.pagos_me.descarga');
        $this->Session->delete('cash.pagos_me.operacion');
        $this->Session->delete('cash.pagos_me.data');


        if (!empty($this->data['PagoCash']) && !isset($this->data['PagoCash']['paginando'])) {
            $this->PagoCash->set($this->data);

            if ($this->PagoCash->validates(array('fieldList' => array('accion', 'seleccion')))) {


                switch ($this->data['accion']) {

                    case 'crear':

                        $this->Session->write('cash.pagos_me.operacion', array(
                                'motivo' => 'crear',
                                'tipo' => $tipo,
                                'url_volver_modificar' => 'pagos_me/' . $tipo
                            )
                        );

                        $this->redirect('pago_modificar_me');
                        break;


                    case 'detalle':
                        $estado = $this->PagoCash->buscar($this->data['PagoCash']['id'], $usuario['id']);

                        if ($estado) {
                            $this->Session->write('cash.pagos_me.operacion', array(
                                'tipo' => $tipo,
                                'url_volver' => 'pagos_me/' . $tipo
                                    )
                            );

                            $this->Session->write('cash.pagos_me.data', $estado);
                            $this->redirect('pago_detalle_me');
                        }
                        break;

                    case 'eliminar':
                        $this->Session->write('cash.pagos_me.operacion', array(
                            'tipo' => $tipo,
                            'url_volver' => 'pagos_me/' . $tipo,
                            'seleccion' => $this->data['seleccion']
                                )
                        );
                        $this->redirect('pago_eliminar_me');
                        break;
                }
            }
        }

        // Condiciones para la busqueda de transacciones guardadas
        $conditions = array(
            'TransaccionPendiente.usuario_id' => $usuario['id'],
            'TransaccionPendiente.estatus' => 'g',
        );

        if ($tipo == 'm') {

            $conditions['TransaccionPendiente.tipo'] = array('nxa', 'mxm', 'nxo');
        } else {
            $conditions['TransaccionPendiente.tipo'] = array('vxa', 'xxm', 'vxo');
        }

        // Paginacion
        $total_registros = $this->TransaccionPendiente->find(
                'count', array('conditions' => $conditions)
        );


        $offset = 0;

        if (
                isset($this->data['PagoCash']['offset']) &&
                preg_match('/^\d+$/', $this->data['PagoCash']['offset'])
        ) {
            $offset = intval($this->data['PagoCash']['offset']);
        }

        $listado = array();

        if ($total_registros) {
            $listado = $this->TransaccionPendiente->find(
                'all', array(
                'fields' => array('id', 'lote', 'cuenta_origen', 'fecha_hora_valor', 'descripcion', 'monto', 'UsuarioCreador.login'),
                'conditions' => $conditions,
                'order' => 'TransaccionPendiente.id desc',
                'offset' => $offset,
                'limit' => 10
                    )
            );
        }

        $this->layout = 'interna';

        switch ($tipo) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera');
                $this->set('encabezado_vista', 'pagos de nomina Moneda Extranjera');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_p_me');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera');
                $this->set('encabezado_vista', 'pagos de proveedores Moneda Extranjera');
                break;
        }

        if ($this->Session->check('cash.pagos_me.exito')) {
            $pago_guardado = $this->Session->read('cash.pagos_me.exito');
            $this->set('pago_guardado', $pago_guardado);
            $this->Session->delete('cash.pagos_me.exito');
        }

        $this->set(compact('listado', 'tipo', 'total_registros'));
    }

   
	
	/**
	 * Accion para indicar el detalle del pago de n/a o proveedores 
	 */
	function pago_detalle(){
		if(!$this->Session->check('cash.pagos.operacion') && !$this->Session->check('cash.pagos.data')){
			$this->redirect('/consultas/integral');
		}
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$dataTemp = $this->Session->read('cash.pagos.data');

		if(!empty($this->data['accion'])){
			switch($this->data['accion']){
				case 'eliminar':
					$this->Session->write('cash.pagos.operacion', 
						array(
							'tipo'=>$estado['tipo'],
							'url_volver'=>'pago_detalle',
							'seleccion'=>$dataTemp['PagoCash']['id']
						)
					);
					
					$this->redirect('pago_eliminar');
					break;
				
				case 'procesar':
					if($this->PagoCash->validarFecha(array('fecha_valor'=>$dataTemp['PagoCash']['fecha_valor']))){
						$this->Session->write('cash.pagos.operacion', 
							array(
								'tipo'=>$estado['tipo'],
								'url_volver_procesar'=>'pago_detalle',
								'procesar_detalle'=>true
							)
						);
						
						$this->redirect('pago_procesar_confirmar');
					}
					
					break;
				
				case 'modificar':
					$this->Session->write('cash.pagos.operacion', 
						array(
							'tipo'=>$estado['tipo'],
							'url_volver_modificar'=>'pago_detalle',
							'modificar_detalle'=>true
						)
					);
					
					$this->Session->write('cash.pagos.data', $dataTemp);					
					
					$this->redirect('pago_modificar');
					break;
			}
		}
		
		$bancos = $this->Activo->buscarBancos();
		
		$this->data = $dataTemp;
		
		$this->set(compact('estado','usuario','bancos'));
			
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos N/a');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores');
				break;
		}
		
	}
	 //--------js---------      
    //js 06/10/2020
    /**
     * Accion para indicar el detalle del pago de nomina o proveedores 
     */
    function pago_detalle_me() {
	
        if (!$this->Session->check('cash.pagos_me.operacion') && !$this->Session->check('cash.pagos.data')) {
            $this->redirect('/consultas/integral');
        }

        $estado = $this->Session->read('cash.pagos_me.operacion');
        $usuario = $this->Session->read('usuarios.datos');
        $dataTemp = $this->Session->read('cash.pagos_me.data');

        if (!empty($this->data['accion'])) {
            switch ($this->data['accion']) {
                case 'eliminar':
                    $this->Session->write('cash.pagos_me.operacion', array(
                        'tipo' => $estado['tipo'],
                        'url_volver' => 'pago_detalle_me',
                        'seleccion' => $dataTemp['PagoCash']['id']
                            )
                    );

                    $this->redirect('pago_eliminar');
                break;

                case 'procesar':
                    if ($this->PagoCash->validarFecha(array('fecha_valor' => $dataTemp['PagoCash']['fecha_valor']))) {
                        $this->Session->write('cash.pagos_me.operacion', array(
                            'tipo' => $estado['tipo'],
                            'url_volver_procesar' => 'pago_detalle_me',
                            'procesar_detalle_me' => true
                        )
                    );

                        $this->redirect('pago_procesar_confirmar_me');
                    }

                    break;

                case 'modificar':
                    $this->Session->write('cash.pagos_me.operacion', array(
                        'tipo' => $estado['tipo'],
                        'url_volver_modificar' => 'pago_detalle_me',
                        'modificar_detalle' => true
                            )
                    );

                    $this->Session->write('cash.pagos_me.data', $dataTemp);

                    $this->redirect('pago_modificar_me');
                    break;
            }
        }

        $bancos = $this->Activo->buscarBancos();

        $this->data = $dataTemp;

        $this->set(compact('estado', 'usuario', 'bancos'));

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_me.proveedores');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera');
                break;
        }
    }

    //js 06/10/2020
	
	/**
	 * Accion para modificar o crear pagos manualmente
	 * @param string $volver Indica si viene de la confirmacion	 */
	function pago_modificar($volver=null){
		if(!$this->Session->check('cash.pagos.operacion')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');                
		$usuario = $this->Session->read('usuarios.datos');
	               
		// Contar el numero de aprobaciones de la transaccion		
		$aprobaciones = 0;
		
		$temp = $this->Session->read('cash.pagos.data');
		
		$beneficiarios = $this->AfiliacionCash->listar($usuario, $estado['tipo']);
		
		foreach($beneficiarios as &$i){
			// Validar el estatus del nro de cuenta
			// Siempre y cuando sea cuenta banco activo
			if(preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/',desencriptar($i['AfiliacionCash']['numero_cuenta']))){
			
				$productoValidar = substr(desencriptar($i['AfiliacionCash']['numero_cuenta']),-10);
				$valorCuenta= array(
					'NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 
					'NROPARAM' => 5,
					'NACIONALIDAD'=>trim($i['AfiliacionCash']['tipo_cedula']),
					'NROIDENTIFICACION'=>str_pad(trim(desencriptar($i['AfiliacionCash']['cedula'])),12,'0',STR_PAD_LEFT)
				);
				
				$datos = $this->Activo->validarProducto($valorCuenta);				
				
				$error = (isset($datos['NIBPF3010Return']['error']['ERNU01'])) ? intval($datos['NIBPF3010Return']['error']['ERNU01']) : 0;
				
				if($error!=0 || !in_array($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'],array('A','T'))){
				
					if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
						$i['AfiliacionCash']['controlada'] = true;
					} else {
						$i['AfiliacionCash']['inactiva'] = true;
					}
				}
			}
		}
		
		// Ordenar arreglo
		
		usort($beneficiarios,'ordernar_afiliacioncash_por_id');

		if(isset($estado['modificar_detalle'])){
			$aprobaciones = $this->AprobacionTransaccion->find(
				'count',
				array(
					'conditions'=>array(
						'AprobacionTransaccion.transaccion_id'=>$temp['PagoCash']['id']
					)
				)
			);
		}
		
		if( empty($this->data['PagoCash']) ){
			if(isset($volver) || isset($estado['modificar_detalle'])){
				$this->data = $this->Session->read('cash.pagos.data');
				
				// Indexar beneficiarios por id de afiliacion				$bs = array();
				
				foreach($beneficiarios as $xd){
					foreach($this->data['Beneficiarios'] as $j){
						if(
							$xd['AfiliacionCash']['tipo_cedula'] == $j['tipo_cedula'] &&
							desencriptar($xd['AfiliacionCash']['cedula']) == desencriptar($j['cedula'])
						){
							$j['monto'] = formatea_monto_fromWs($j['monto']);
							$bs[$xd['AfiliacionCash']['id']] = $j;
							break;
						}
					}
				}
				
				if(isset($volver) && count($bs)) $estado['modificar_detalle'] = true;
				
				$this->data['Beneficiarios']=$bs;
			} else {
				// Numero de lote
				do{
                                    $lote = rand(111111111, 999999999);
				}while(!$this->TransaccionPendiente->checkLote($lote));
				$this->data['PagoCash']['lote'] = $lote;
			}
		} else {
			$this->data['PagoCash']['cantidad_aprobaciones'] = 0;
			// Calculo del monto total del lado del servidor
			$this->data['PagoCash']['monto_total'] = 0;
			$this->data['PagoCash']['monto_total_it'] = 0;
			$this->data['PagoCash']['monto_total_e'] = 0;
			
			if(isset($this->data['Beneficiarios'])){
				// Recorre los beneficiarios
				foreach ($beneficiarios AS $b){
					// verifica que sea el seleccionado
					if(array_key_exists($b['AfiliacionCash']['id'], $this->data['Beneficiarios'])){
						$monto = $this->data['Beneficiarios'][$b['AfiliacionCash']['id']]['monto'];
						
						$this->data['PagoCash']['monto_total'] += formatea_monto_toWs($monto);
						
						if(substr(desencriptar($b['AfiliacionCash']['numero_cuenta']),0,4)=='0171'){
							$this->data['PagoCash']['monto_total_it'] += formatea_monto_toWs($monto);
						} else {
							$this->data['PagoCash']['monto_total_e'] += formatea_monto_toWs($monto);
						}
					}
				}
			}
			
			$this->data['PagoCash']['monto_total'] = formatea_monto_fromWs($this->data['PagoCash']['monto_total']);
			$this->data['PagoCash']['monto_total_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_total_it']);
			$this->data['PagoCash']['monto_total_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_total_e']);
			
			// Limite del usuario
			if($usuario['rol_juridico_id']>2){
				$transaccion_detalle_it = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					$estado['tipo']=='n' ? 39 : 40
				);
				
				$transaccion_detalle_e = $this->TransaccionJuridicoDetalle->obtener(
					$usuario['id'],
					$estado['tipo']=='n' ? 100016 : 100017
				);
				
				if($transaccion_detalle_it) {
					$this->data['PagoCash']['monto_limite_usuario_it'] = $transaccion_detalle_it['limite'];
				} else {
					$this->data['PagoCash']['monto_limite_usuario_it'] = '0,00'; // Denegar permiso a traves de los limites
				}
				
				if($transaccion_detalle_e) {
					$this->data['PagoCash']['monto_limite_usuario_e'] = $transaccion_detalle_e['limite'];
				} else {
					$this->data['PagoCash']['monto_limite_usuario_e'] = '0,00'; // Denegar permiso a traves de los limites
				}
			} else {
                            
                            if($estado['tipo']=='n'){
                                $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
				$this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
                            }else{
                                $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');
				$this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
                            }
				
			}
			
			if($this->data['PagoCash']['monto_limite_usuario_it']){
				$this->data['PagoCash']['monto_limite_usuario_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_it']);
			} else {
				$this->data['PagoCash']['monto_limite_usuario_it'] = ''; // Ilimitado
			}
			
			if($this->data['PagoCash']['monto_limite_usuario_e']){
				$this->data['PagoCash']['monto_limite_usuario_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_e']);
			} else {
				$this->data['PagoCash']['monto_limite_usuario_e'] = ''; // Ilimitado
			}
			
			// Validacion de los campos
			$this->PagoCash->set($this->data);
			
			if ($this->PagoCash->validates(array('fieldList' => array(
				'cuentaDebitar',
				'descripcion',
				'lote',
				'fecha_valor',
				'monto_total'
			)))){
				$this->data['PagoCash']['id'] = '';
				
				if(isset($estado['modificar_detalle'])){
					$this->data['PagoCash']['id'] = $temp['PagoCash']['id'];
					
					
					if($aprobaciones > 0){	
						// Si la transacci?a tiene aprobaciones, no modificar el nro de lote
						
						$this->data['PagoCash']['lote'] = ltrim($temp['PagoCash']['lote'], "0");
					}
				}
				
				if(!isset($this->data['Beneficiarios']) || !count($this->data['Beneficiarios'])){
					$this->PagoCash->validationErrors['beneficiario'] = _m('uno.seleccion.requerida','de los beneficiarios');
				} else if(!$this->TransaccionPendiente->checkLote($this->data['PagoCash']['lote'],$this->data['PagoCash']['id'])){
					$this->PagoCash->validationErrors['lote'] = _m('lote.repetido');
				} else {
					if(count ($this->data['Beneficiarios'])<=10){
						
						// guardado en la sesion
						$estado['url_volver_procesar'] = 'pago_modificar/v';
						$this->Session->write('cash.pagos.operacion', $estado);
						
						if(isset($temp['PagoCash'])) $this->data['PagoCash'] = array_merge($temp['PagoCash'],$this->data['PagoCash']);
						
						$bs = array();	
						
						// Recorre los beneficiarios
						foreach ($beneficiarios AS $b){
							
							// verifica que sea el seleccionado
							if(array_key_exists($b['AfiliacionCash']['id'], $this->data['Beneficiarios'])){
								if( 
									isset($this->data['Beneficiarios'][$b['AfiliacionCash']['id']]['seleccionado']) &&
									!isset($b['AfiliacionCash']['inactiva']) &&
									!isset($b['AfiliacionCash']['controlada'])
								){
									$tempData = $b['AfiliacionCash'];
									$tempData['monto'] = formatea_monto_toWs($this->data['Beneficiarios'][$b['AfiliacionCash']['id']]['monto']);
									unset($tempData['id']);
									$bs []= $tempData;
								}
							}
						}
						
						$this->data['Beneficiarios'] = $bs;
						
						$this->Session->write('cash.pagos.data', $this->data);
						
						// Si elige la opcion de guardar no muestra confirmacion ni resultado                                                
						if($this->data['accion']=='guardar'){
							$this->redirect('pago_guardar_advertencia');
						}
						else if($this->data['accion']=='modificar'){
							$this->redirect('pago_modificar_confirmar');
						}
						else{
							$this->redirect('pago_procesar_confirmar');
						}
					} else {
						$this->PagoCash->validationErrors['beneficiario'] =_m('lote.limite.diez');
					}
				}
			}
		}
		
		$this->data['PagoCash']['cantidad_aprobaciones'] = $aprobaciones;		
		$cuentas = $this->Activo->buscarCuentasSaldos();
		
		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('estado','usuario','cuentas','beneficiarios', 'bancos'));
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				if(isset($this->data['PagoCash']['id']) && !isset($this->data['accion'])){
					$this->set('titulo','Pagos N?a - Modificar');
					$this->set('instruccion','A continuaci&oacute;n proceda a modificar los datos del pago');
				} else {
					$this->set('titulo','Pagos N/a - Carga Manual');
					$this->set('instruccion','A continuaci&oacute;n proceda a ingresar los datos del pago');
				}
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				if(isset($this->data['PagoCash']['id']) && !isset($this->data['accion'])){
					$this->set('titulo','Pagos Proveedores - Modificar');
					$this->set('instruccion','A continuaci&oacute;n proceda a modificar los datos del pago');
				} else {
					$this->set('titulo','Pagos Proveedores - Carga Manual');
					$this->set('instruccion','A continuaci&oacute;n proceda a ingresar los datos del pago');
				}
				break;
		}
	}
	
	// js 06/10/2020   
        /**
     * Accion para modificar o crear pagos manualmente Moneda Extranjera
     * @param string $volver Indica si viene de la confirmacion
     */
    function pago_modificar_me($volver = null) {

        if (!$this->Session->check('cash.pagos_me.operacion'))
            $this->redirect('/consultas/integral');

        $estado = $this->Session->read('cash.pagos_me.operacion');
        $usuario = $this->Session->read('usuarios.datos');

        // Contar el numero de aprobaciones de la transaccion

        $aprobaciones = 0;

        $temp = $this->Session->read('cash.pagos_me.data');


        $beneficiarios = $this->AfiliacionCash->listar_me($usuario, $estado['tipo']);

        foreach ($beneficiarios as &$i) {
            // Validar el estatus del nro de cuenta
            // Siempre y cuando sea cuenta banco activo

            if (preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/', desencriptar($i['AfiliacionCash']['numero_cuenta']))) {
                $productoValidar = substr(desencriptar($i['AfiliacionCash']['numero_cuenta']), -10);
                $valorCuenta = array(
                    'NROCUENTA' => str_pad($productoValidar, 12, '0', STR_PAD_LEFT),
                    'NROPARAM' => 5,
                    'NACIONALIDAD' => trim($i['AfiliacionCash']['tipo_cedula']),
                    'NROIDENTIFICACION' => str_pad(trim(desencriptar($i['AfiliacionCash']['cedula'])), 12, '0', STR_PAD_LEFT)
                );

                $datos = $this->Activo->validarProducto($valorCuenta);

                $error = (isset($datos['NIBPF3010Return']['error']['ERNU01'])) ? intval($datos['NIBPF3010Return']['error']['ERNU01']) : 0;

                if ($error != 0 || !in_array($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'], array('A', 'T'))) {

                    if ($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] == 'O') {
                        $i['AfiliacionCash']['controlada'] = true;
                    } else {
                        $i['AfiliacionCash']['inactiva'] = true;
                    }
                }
            }
        }

        // Ordenar arreglo
        usort($beneficiarios, 'ordernar_afiliacioncash_por_id');

        if (isset($estado['modificar_detalle'])) {
            $aprobaciones = $this->AprobacionTransaccion->find(
                'count', array(
                    'conditions' => array(
                        'AprobacionTransaccion.transaccion_id' => $temp['PagoCash']['id']
                    )
                )
            );
        }

        if (empty($this->data['PagoCash'])) {
            if (isset($volver) || isset($estado['modificar_detalle'])) {
                $this->data = $this->Session->read('cash.pagos_me.data');

                // Indexar beneficiarios por id de afiliacin
                $bs = array();

                foreach ($beneficiarios as $xd) {
                    foreach ($this->data['Beneficiarios'] as $j) {
                        if (
                                $xd['AfiliacionCash']['tipo_cedula'] == $j['tipo_cedula'] &&
                                desencriptar($xd['AfiliacionCash']['cedula']) == desencriptar($j['cedula'])
                        ) {
                            $j['monto'] = formatea_monto_fromWs($j['monto']);
                            $bs[$xd['AfiliacionCash']['id']] = $j;
                            break;
                        }
                    }
                }

                if (isset($volver) && count($bs))
                    $estado['modificar_detalle'] = true;

                $this->data['Beneficiarios'] = $bs;
            } else {
                // Numero de lote
                do {
                    $lote = rand(111111111, 999999999);
                } while (!$this->TransaccionPendiente->checkLote($lote));
                $this->data['PagoCash']['lote'] = $lote;
            }
        } else {
            $this->data['PagoCash']['cantidad_aprobaciones'] = 0;
            // Calculo del monto total del lado del servidor
            $this->data['PagoCash']['monto_total'] = 0;
            $this->data['PagoCash']['monto_total_it'] = 0;
            $this->data['PagoCash']['monto_total_e'] = 0;

            if (isset($this->data['Beneficiarios'])) {
                // Recorre los beneficiarios
                foreach ($beneficiarios AS $b) {
                    // verifica que sea el seleccionado
                    if (array_key_exists($b['AfiliacionCash']['id'], $this->data['Beneficiarios'])) {
                        $monto = $this->data['Beneficiarios'][$b['AfiliacionCash']['id']]['monto'];

                        $this->data['PagoCash']['monto_total'] += formatea_monto_toWs($monto);

                        if (substr(desencriptar($b['AfiliacionCash']['numero_cuenta']), 0, 4) == '0171') {
                            $this->data['PagoCash']['monto_total_it'] += formatea_monto_toWs($monto);
                        } else {
                            $this->data['PagoCash']['monto_total_e'] += formatea_monto_toWs($monto);
                        }
                    }
                }
            }

            $this->data['PagoCash']['monto_total'] = $this->data['PagoCash']['monto_total'];
            $this->data['PagoCash']['monto_total_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_total_it']);
            $this->data['PagoCash']['monto_total_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_total_e']);

            // Limite del usuario
            if ($usuario['rol_juridico_id'] > 2) {
                $transaccion_detalle_it = $this->TransaccionJuridicoDetalle->obtener(
                        $usuario['id'], $estado['tipo'] == 'm' ? 101072 : 101073
                );

                $transaccion_detalle_e = $this->TransaccionJuridicoDetalle->obtener(
                        $usuario['id'], $estado['tipo'] == 'm' ? 101072 : 101073
                );

                if ($transaccion_detalle_it) {
                    $this->data['PagoCash']['monto_limite_usuario_it'] = $transaccion_detalle_it['limite'];
                } else {
                    $this->data['PagoCash']['monto_limite_usuario_it'] = '0,00'; // Denegar permiso a traves de los limites
                }

                if ($transaccion_detalle_e) {
                    $this->data['PagoCash']['monto_limite_usuario_e'] = $transaccion_detalle_e['limite'];
                } else {
                    $this->data['PagoCash']['monto_limite_usuario_e'] = '0,00'; // Denegar permiso a traves de los limites
                }
            } else {

                if ($estado['tipo'] == 'm') {
                    $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_nomina_usd'] ? $usuario['limite_internas_terceros_nomina_usd'] : _p('limite_internas_terceros_nomina_usd');
                    $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_nomina_eur'] ? $usuario['limite_internas_terceros_nomina_eur'] : _p('limite_internas_terceros_nomina_eur');
                } else {
                    $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_proveedores_usd'] ? $usuario['limite_internas_terceros_proveedores_usd'] : _p('limite_internas_terceros_proveedores_usd');
                    $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_proveedores_usd'] ? $usuario['limite_internas_terceros_proveedores_usd'] : _p('limite_internas_terceros_proveedores_usd');
                }
            }

            if ($this->data['PagoCash']['monto_limite_usuario_it']) {
                $this->data['PagoCash']['monto_limite_usuario_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_it']);
            } else {
                $this->data['PagoCash']['monto_limite_usuario_it'] = ''; // Ilimitado
            }

            //if ($this->data['PagoCash']['monto_limite_usuario_e']) {
               // $this->data['PagoCash']['monto_limite_usuario_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_e']);
            //} else {
                //$this->data['PagoCash']['monto_limite_usuario_e'] = ''; // Ilimitado
            //}

            // Validacion de los campos
            $this->PagoCash->set($this->data);

            if ($this->PagoCash->validates(array('fieldList' => array(
                            'cuentaDebitar',
                            'descripcion',
                            'lote',
                            'fecha_valor',
                            'monto_total'
                )))) {
                $this->data['PagoCash']['id'] = '';

                if (isset($estado['modificar_detalle'])) {
                    $this->data['PagoCash']['id'] = $temp['PagoCash']['id'];

                    if ($aprobaciones > 0) {
                        // Si la transacci?a tiene aprobaciones, no modificar el nro de lote	
                        $this->data['PagoCash']['lote'] = ltrim($temp['PagoCash']['lote'], "0");
                    }
                }

                if (!isset($this->data['Beneficiarios']) || !count($this->data['Beneficiarios'])) {
                    $this->PagoCash->validationErrors['beneficiario'] = _m('uno.seleccion.requerida', 'de los beneficiarios');
                } else if (!$this->TransaccionPendiente->checkLote($this->data['PagoCash']['lote'], $this->data['PagoCash']['id'])) {
                    $this->PagoCash->validationErrors['lote'] = _m('lote.repetido');
                } else {
                    if (count($this->data['Beneficiarios']) <= 10) {

                        // guardado en la sesion
                        $estado['url_volver_procesar'] = 'pago_modificar_me/v';
                        $this->Session->write('cash.pagos_me.operacion', $estado);

                        if (isset($temp['PagoCash']))
                            $this->data['PagoCash'] = array_merge($temp['PagoCash'], $this->data['PagoCash']);

                        $bs = array();

                        // Recorre los beneficiarios
                        foreach ($beneficiarios AS $b) {

                            // verifica que sea el seleccionado
                            if (array_key_exists($b['AfiliacionCash']['id'], $this->data['Beneficiarios'])) {
                                if (
                                        isset($this->data['Beneficiarios'][$b['AfiliacionCash']['id']]['seleccionado']) &&
                                        !isset($b['AfiliacionCash']['inactiva']) &&
                                        !isset($b['AfiliacionCash']['controlada'])
                                ) {
                                    $tempData = $b['AfiliacionCash'];
                                    $tempData['monto'] = formatea_monto_toWs($this->data['Beneficiarios'][$b['AfiliacionCash']['id']]['monto']);
                                    unset($tempData['id']);
                                    $bs [] = $tempData;
                                }
                            }
                        }

                        $this->data['Beneficiarios'] = $bs;

                        $this->Session->write('cash.pagos_me.data', $this->data);

                        // Si elige la opcion de guardar no muestra confirmacion ni resultado                                                
                        if ($this->data['accion'] == 'guardar') {
                            $this->redirect('pago_guardar_advertencia_me');
                        } else if ($this->data['accion'] == 'modificar') {
                            $this->redirect('pago_modificar_confirmar_me');
                        } else {
                            $this->redirect('pago_procesar_confirmar_me');
                        }
                    } else {
                        $this->PagoCash->validationErrors['beneficiario'] = _m('lote.limite.diez');
                    }
                }
            }
        }

        $this->data['PagoCash']['cantidad_aprobaciones'] = $aprobaciones;
        $cuentas = $this->Activo->buscarCuentasSaldosMe();

        $bancos = $this->Activo->buscarBancos();

        $this->set(compact('estado', 'usuario', 'cuentas', 'beneficiarios', 'bancos'));

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina');
                if (isset($this->data['PagoCash']['id']) && !isset($this->data['accion'])) {
                    $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Modificar');
                    $this->set('instruccion', 'A continuaci&oacute;n proceda a modificar los datos del pago');
                } else {
                    $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Carga Manual');
                    $this->set('instruccion', 'A continuaci&oacute;n proceda a ingresar los datos del pago');
                }
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_p_me');
                if (isset($this->data['PagoCash']['id']) && !isset($this->data['accion'])) {
                    $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Modificar');
                    $this->set('instruccion', 'A continuaci&oacute;n proceda a modificar los datos del pago');
                } else {
                    $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Carga Manual');
                    $this->set('instruccion', 'A continuaci&oacute;n proceda a ingresar los datos del pago');
                }
                break;
        }
    }

    // js 06/10/2020
	
        /**
	 * Accion para mostrar un mensaje de advertencia antes de guardar un pago manual 
	 */
	function pago_guardar_advertencia(){
		
                $estado = $this->Session->read('cash.pagos.operacion');                
                $usuario = $this->Session->read('usuarios.datos');
                $this->data = $this->Session->read('cash.pagos.data');		
                $this->data['accion']='guardar';                
                
                $this->set(compact('estado','usuario'));

		$this->layout = 'interna';
                
                switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');				
				$this->set('titulo','Pagos N/a - Carga Manual');				
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores - Carga Manual');				
				break;
		}	
	}
        
	//js 06/10/2020
	/**
     * Accion para mostrar un mensaje de advertencia antes de guardar un pago manual 
     */
    function pago_guardar_advertencia_me() {

        $estado = $this->Session->read('cash.pagos_me.operacion');
        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->read('cash.pagos_me.data');
        $this->data['accion'] = 'guardar';

        $this->set(compact('estado', 'usuario'));

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Carga Manual');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_me.proveedores');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Carga Manual');
                break;
        }
    }

    // js 06/10/2020
        
	/**
	 * Accion para confirmar la modificacion creacion un pago manual 
	 */
	function pago_modificar_confirmar(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$this->data = $this->Session->read('cash.pagos.data');
		
		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('estado','usuario','bancos'));

		$this->layout = 'interna';

		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				if(isset($this->data['PagoCash']['id'])){
					$this->set('titulo','Pagos N/a - Modificar');
				} else {
					$this->set('titulo','Pagos N/a - Carga Manual');
				}
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				if(isset($this->data['PagoCash']['id'])){
					$this->set('titulo','Pagos Proveedores - Modificar');
				} else {
					$this->set('titulo','Pagos Proveedores - Carga Manual');
				}
				break;
		}
	}
	 // js 06/10/2020
    // js 06/10/2020
    /**
     * Accion para confirmar la modificacion o creacion de un pago manual Moneda Extranjera  
     */
    function pago_modificar_confirmar_me() {
        if (!$this->Session->check('cash.pagos_me.data'))
            $this->redirect('/consultas/integral');
	
        $estado = $this->Session->read('cash.pagos_me.operacion');
        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->read('cash.pagos_me.data');

        $bancos = $this->Activo->buscarBancos();

        $this->set(compact('estado', 'usuario', 'bancos'));

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina');
                if (isset($this->data['PagoCash']['id'])) {
                    $this->set('titulo', 'Pagos Nomina - Modificar Moneda Extranjera');
                } else {
                    $this->set('titulo', 'Pagos Nomina - Carga Manual Moneda Extranjera ');
                }
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_me.proveedores');
                if (isset($this->data['PagoCash']['id'])) {
                    $this->set('titulo', 'Pagos Proveedores - Modificar Moneda Extranjra ');
                } else {
                    $this->set('titulo', 'Pagos Proveedores - Carga Manual Moneda Extranjera');
                }
                break;
        }
    }

    // js 06/10/2020
	
	/**
	 * Accion para indicar el resultado de la modificacion creacion del pago 
	 */
	function pago_modificar_resultado(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$this->data = $this->Session->read('cash.pagos.data');
		
		$exito = false;
		
		// Validacion anti fraude
		$cuentas = $this->Activo->buscarCuentas();
		
		if(
			!in_array($this->data['PagoCash']['cuentaDebitar'], array_keys($cuentas))
		){
			$this->redirect('/consultas/integral');
		}
		//  Fin validaci?nti fraude
		
		if(isset($this->data['PagoCash']['id'])) $this->TransaccionPendiente->id = $this->data['PagoCash']['id'];
		
		$ds = $this->TransaccionPendiente->getDataSource();

		$ds->begin($this->TransaccionPendiente);
		
		// Buscar la cantidad de aprobaciones requeridas para la transaccion		// Todos los tipos de pago cash tienen la misma cantidad de aprobaciones
		// debido a que en la configuraci?e usuarios el combo es para los cuatro tipo de pagos
		// buscamos uno por uno y nos quedamos con el que exista, sin importar cual es debido a la
		// premisa anterior
		$transaccion_detalle = false;
		
		foreach(array(39,100016,40,100017) as $i){
			$transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
				$usuario['id'],
				$i
			);
			
			if($transaccion_detalle) break;
		}
		
		$nro_aprobaciones = 3; // Nunca debera bloquedar en este valor
		
		if( isset($transaccion_detalle['aprobaciones']) ){
			$nro_aprobaciones = $transaccion_detalle['aprobaciones'];
		}
			
		$internas = false;
		$externas = false;
		
		foreach($this->data['Beneficiarios'] as &$i){			
			// Verifica si es una cuenta interna o externa
			if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
				$internas = true;
			}
			else{
				$externas = true;
			}
		}
		
		// tipo de pago deacuerdo a las transacciones
		if($internas && $externas){
			$tipo_pago = ($estado['tipo']=='n') ? 'nxa' : 'vxa';
		}
		else if($internas){
			$tipo_pago = ($estado['tipo']=='n') ? 'nxm' : 'vxm';
		}
		else{
			$tipo_pago = ($estado['tipo']=='n') ? 'nxo' : 'vxo';
		}
		// tipo de pago deacuerdo a las transacciones
		
		$res = $this->TransaccionPendiente->save(
			array(
				'TransaccionPendiente'=>array(
					'usuario_id'=>$usuario['id'],
					'tipo'=> $tipo_pago,
					'estatus'=>($this->data['accion']=='guardar' || $this->data['accion']=='modificar') ? 'g' : 'pa',
					'lote'=>ltrim($this->data['PagoCash']['lote'], "0"),
					'fecha_hora_registro'=>date('Y-m-d H:i:s'),
					'fecha_hora_valor'=>formatea_fecha_toMSSQL($this->data['PagoCash']['fecha_valor']['day'], $this->data['PagoCash']['fecha_valor']['month'], $this->data['PagoCash']['fecha_valor']['year']),
					'monto'=>formatea_monto_toWs($this->data['PagoCash']['monto_total']),
					'aprobaciones_requeridas'=>$nro_aprobaciones,
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif'],
					'cuenta_origen'=>encriptar($this->data['PagoCash']['cuentaDebitar']),
					'descripcion'=>encriptar($this->data['PagoCash']['descripcion']),
				)
			),
			false
		);
		
		if($res){
			$ds->commit($this->TransaccionPendiente);
			
			// Elimina los beneficiarios anteriores
			$this->TransaccionBeneficiario->deleteAll(
				array(
					'transaccion_id' => $this->TransaccionPendiente->id
				),
				false
			);
			
			foreach($this->data['Beneficiarios'] as &$i){
				$i['transaccion_id'] = $this->TransaccionPendiente->id;
			}
			
			$res = $this->TransaccionBeneficiario->saveAll(
				$this->data['Beneficiarios'],
				array('validate'=>false)
			);
			
			if($res){
				// Para el PDF
				$pdf = $this->data;
				$pdf['tipo'] = $estado['tipo'];
				$this->Session->write('cash.pagos.descarga', $pdf);
				
				$exito = true;
			} else {
				$exito = false;
				$this->log($ds->error, LOG_ERROR);
			}
		}
		else{
			$ds->rollback($this->TransaccionPendiente);
			$this->log($ds->error, LOG_ERROR);
		}
		
		$this->Session->delete('cash.pagos.data');
		$this->Session->delete('cash.pagos.operacion');
		
		if($this->data['accion']=='guardar'){
			
			if($exito){
				$descripcion = 'Guardado lote nro.: '.$this->data['PagoCash']['lote'];

				if(
					$tipo_pago[2]=='a' ||
					$tipo_pago[2]=='m'
				){
					$this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0]=='n'?39:40, NULL, NULL, NULL, NULL, $descripcion);
				} elseif($tipo_pago[2]=='o'){
					$this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0]=='n'?100016:100017, NULL, NULL, NULL, NULL, $descripcion);
				}
			}
			
			$this->Session->write('cash.pago.exito',$exito);
			$this->redirect($estado['url_volver_modificar']);
		}
		else{
			
			if($exito){
				$descripcion = 'Modificacion lote nro.: '.$this->data['PagoCash']['lote'];

				if($tipo_pago[2]=='a' || $tipo_pago[2]=='m'){
					$this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0]=='n'?39:40, NULL, NULL, NULL, NULL, $descripcion);
				} elseif($tipo_pago[2]=='o') {
					$this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0]=='n'?100016:100017, NULL, NULL, NULL, NULL, $descripcion);
				}
			}
			
			$bancos = $this->Activo->buscarBancos();

			$this->set(compact('estado','usuario','bancos', 'exito'));
			
			$this->layout = 'interna';
			
			switch($estado['tipo']){
				case 'n':
					$this->set('seccion_actual','cash.pagos.nomina');
					if(isset($this->data['PagoCash']['id'])){
						$this->set('titulo','Pagos N/a - Modificar');
					} else {
						$this->set('titulo','Pagos N/a - Carga Manual');
					}
					break;
				case 'p':
					$this->set('seccion_actual','cash.pagos.proveedores');
					if(isset($this->data['PagoCash']['id'])){
						$this->set('titulo','Pagos Proveedores - Modificar');
					} else {
						$this->set('titulo','Pagos Proveedores - Carga Manual');
					}
					break;
			}
		}
		
	}
	 //js 06/10/2020
    /**
     * Accion para indicar el resultado de la modificacion o creacion del pago Moneda Extranjera 
     */
    function pago_modificar_resultado_me() {
	
        if (!$this->Session->check('cash.pagos_me.data'))
            $this->redirect('/consultas/integral');

        $estado = $this->Session->read('cash.pagos_me.operacion');
        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->read('cash.pagos_me.data');

        

        $exito = false;

        // Validacion anti fraude
        $cuentas = $this->Activo->buscarCuentas();

        if (
                !in_array($this->data['PagoCash']['cuentaDebitar'], array_keys($cuentas))
        ) {
            $this->redirect('/consultas/integral');
        }
        //  Fin validacion anti fraude

        if (isset($this->data['PagoCash']['id']))
            $this->TransaccionPendiente->id = $this->data['PagoCash']['id'];

        $ds = $this->TransaccionPendiente->getDataSource();
        
        $ds->begin($this->TransaccionPendiente);

        // Buscar la cantidad de aprobaciones requeridas para la transaccion
        // Todos los tipos de pago cash tienen la misma cantidad de aprobaciones
        // debido a que en la configuracion de usuarios el combo es para los cuatro tipo de pagos
        // buscamos uno por uno y nos quedamos con el que exista, sin importar cual es debido a la
        // premisa anterior
        $transaccion_detalle = false;

        foreach (array(39, 100016, 40, 100017, 101072, 101073) as $i) {
            $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                    $usuario['id'], $i
            );

            if ($transaccion_detalle)
                break;
        }

        $nro_aprobaciones = 3; // Nunca debera quedar en este valor

        if (isset($transaccion_detalle['aprobaciones'])) {
            $nro_aprobaciones = $transaccion_detalle['aprobaciones'];
        }

        $internas = false;
        $externas = false;

        foreach ($this->data['Beneficiarios'] as &$i) {
            // Verifica si es una cuenta interna o externa
            if (substr(desencriptar($i['numero_cuenta']), 0, 4) == '0171') {
                $internas = true;
            } else {
                $externas = true;
            }
        }
        // tipo de pago deacuerdo a las transacciones
        if ($internas && $externas) {
            $tipo_pago = ($estado['tipo'] == 'm') ? 'nxa' : 'vxa';
        } else if ($internas) {
            $tipo_pago = ($estado['tipo'] == 'm') ? 'mxm' : 'xxm';
        } else {
            $tipo_pago = ($estado['tipo'] == 'm') ? 'nxo' : 'vxo';
        }



        $productoDvalidar = substr($this->data['PagoCash']['cuentaDebitar'], -10);
        $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar, 12, '0', STR_PAD_LEFT), 'NROPARAM' => 2);
        $datosD = $this->Activo->validarProducto($datosDproducto);

        $moneda = $datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'];
        if ($moneda == 'USD') {
            $tipoMoneda = 'USD';
        } else {
            $tipoMoneda = $datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'];
        }

        // tipo de pago deacuerdo a las transacciones
        $res = $this->TransaccionPendiente->save(
            array(
                'TransaccionPendiente' => array(
                    'usuario_id' => $usuario['id'],
                    'tipo' => $tipo_pago,
                    'estatus' => ($this->data['accion'] == 'guardar' || $this->data['accion'] == 'modificar') ? 'g' : 'pa',
                    'lote' => ltrim($this->data['PagoCash']['lote'], "0" . 5),
                    'fecha_hora_registro' => date('Y-m-d H:i:s'),
                    'fecha_hora_valor' => formatea_fecha_toMSSQL($this->data['PagoCash']['fecha_valor']['day'], $this->data['PagoCash']['fecha_valor']['month'], $this->data['PagoCash']['fecha_valor']['year']),
                    'monto' => $this->data['PagoCash']['monto_total'],
                    'aprobaciones_requeridas' => $nro_aprobaciones,
                    'tipo_rif' => $usuario['tipo_rif'],
                    'rif' => $usuario['rif'],
                    'cuenta_origen' => encriptar($this->data['PagoCash']['cuentaDebitar']),
                    'descripcion' => encriptar($this->data['PagoCash']['descripcion']),
                )
            ), false
        );

       
       
        if ($res) {
            $ds->commit($this->TransaccionPendiente);
            
            // Elimina los beneficiarios anteriores
            $this->TransaccionBeneficiario->deleteAll(
                array(
                    'transaccion_id' => $this->TransaccionPendiente->id
                ), false
            );

            foreach ($this->data['Beneficiarios'] as &$i) {
                $i['transaccion_id'] = $this->TransaccionPendiente->id;
            }

            $res = $this->TransaccionBeneficiario->saveAll(
                $this->data['Beneficiarios'], array('validate' => false)
            );

            if ($res) {
                // Para el PDF
                $pdf = $this->data;
                $pdf['tipo'] = $estado['tipo'];
                $this->Session->write('cash.pagos_me.descarga', $pdf);

                $exito = true;
            } else {
                $exito = false;
                $this->log($ds->error, LOG_ERROR);
            }
        } else {
            $ds->rollback($this->TransaccionPendiente);
            $this->log($ds->error, LOG_ERROR);
        }

        $this->Session->delete('cash.pagos_me.data');
        $this->Session->delete('cash.pagos_me.operacion');

        if ($this->data['accion'] == 'guardar') {

            if ($exito) {
                $descripcion = 'Guardado lote nro.: ' . $this->data['PagoCash']['lote'];
                if (
                        $tipo_pago[2] == 'a' ||
                        $tipo_pago[2] == 'm'
                ) {
                    $this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0] == 'm' ? 101072 : 101073, NULL, NULL, NULL, NULL, $descripcion);
                } elseif ($tipo_pago[2] == 'o') {
                    $this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0] == 'm' ? 100016 : 100017, NULL, NULL, NULL, NULL, $descripcion);
                }
            }

            $this->Session->write('cash.pagos_me.exito', $exito);
            $this->redirect($estado['url_volver_modificar']);
        } else {

            if ($exito) {
                $descripcion = 'Modificacion lote nro.: ' . $this->data['PagoCash']['lote'];

                if ($tipo_pago[2] == 'a' || $tipo_pago[2] == 'm') {
                    $this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0] == 'n' ? 39 : 40, NULL, NULL, NULL, NULL, $descripcion);
                } elseif ($tipo_pago[2] == 'o') {
                    $this->Bitacora->registraBitacora($usuario['id'], $tipo_pago[0] == 'n' ? 100016 : 100017, NULL, NULL, NULL, NULL, $descripcion);
                }
            }

            $bancos = $this->Activo->buscarBancos();

            $this->set(compact('estado', 'usuario', 'bancos', 'exito'));

            $this->layout = 'interna';

            switch ($estado['tipo']) {
                case 'm':
                    $this->set('seccion_actual', 'cash.pagos_me');
                    if (isset($this->data['PagoCash']['id'])) {
                        $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Modificar');
                    } else {
                        $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Carga Manual');
                    }
                    break;
                case 'x':
                    $this->set('seccion_actual', 'cash.pagos.proveedores');
                    if (isset($this->data['PagoCash']['id'])) {
                        $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Modificar');
                    } else {
                        $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Carga Manual');
                    }
                    break;
            }
        }
    }

    //js 06/10/2020
	
	/**
	 * Accion para eliminar pagos de n/a o proveedores 
	 */
	function pago_eliminar(){
		if(!$this->Session->check('cash.pagos.operacion')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		
		// listado
		$pagos = $this->PagoCash->listar($estado['seleccion'],$usuario['id']);
		
		$this->layout = 'interna';		
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos N/a - Eliminar');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores - Eliminar');
				break;
		}
		
		$this->set(compact('pagos','estado','usuario'));
	}
	/**
     * Accion para eliminar pagos de nomina o proveedores 
     */
    function pago_eliminar_me() {
        if (!$this->Session->check('cash.pagos_me.operacion'))
            $this->redirect('/consultas/integral');
	
        $estado = $this->Session->read('cash.pagos_me.operacion');
        $usuario = $this->Session->read('usuarios.datos');

        // listado
        $pagos = $this->PagoCash->listar($estado['seleccion'], $usuario['id']);

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Eliminar');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_me.proveedores');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Eliminar');
                break;
        }

        $this->set(compact('pagos', 'estado', 'usuario'));
    }
	
	/**
	 * Accion para indicar el resultado de la eliminacion del pago 
	 */
	function pago_eliminar_resultado(){
		if(!$this->Session->check('cash.pagos.operacion')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$exito = false;
		
		// listado
		$pagos = $this->PagoCash->listar($estado['seleccion'],$usuario['id']);
		
		// Elimina los pagos seleccionados
		if( $this->TransaccionPendiente->deleteAll(
			array(
				'id' => $estado['seleccion'],
				'usuario_id'=>$usuario['id'],
			), false)
		){
			// Para el PDF
			$estado['pagos'] = $pagos;
			$this->Session->write('cash.pagos.descarga', $estado);
			
			$exito = true;
		}
		
		$this->layout = 'interna';		

		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos N/a');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores');
				break;
		}
		
		$this->set(compact('pagos','estado','usuario','exito'));
		
		$this->Session->delete('cash.pagos.operacion');
	}
	/**
     * Accion para indicar el resultado de la eliminacion del pago 
     */
    function pago_eliminar_resultado_me() {
        if (!$this->Session->check('cash.pagos_me.operacion'))
            $this->redirect('/consultas/integral');
	
        $estado = $this->Session->read('cash.pagos_me.operacion');
        $usuario = $this->Session->read('usuarios.datos');
        $exito = false;

        // listado
        $pagos = $this->PagoCash->listar($estado['seleccion'], $usuario['id']);

        // Elimina los pagos seleccionados
        if ($this->TransaccionPendiente->deleteAll(
                        array(
                    'id' => $estado['seleccion'],
                    'usuario_id' => $usuario['id'],
                        ), false)
        ) {
            // Para el PDF
            $estado['pagos'] = $pagos;
            $this->Session->write('cash.pagos_me.descarga', $estado);

            $exito = true;
        }

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina');
                $this->set('titulo', 'Pagos Nomina');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_me.proveedores');
                $this->set('titulo', 'Pagos Proveedores');
                break;
        }

        $this->set(compact('pagos', 'estado', 'usuario', 'exito'));

        $this->Session->delete('cash.pagos.operacion');
    }

	
	/**
	 * Acci?ara confirmar el procesamiento del pago
	 */
	function pago_procesar_confirmar(){
            
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
                		
		$estado = $this->Session->read('cash.pagos.operacion');
		$usuario = $this->Session->read('usuarios.datos');
		$this->data = $this->Session->read('cash.pagos.data');		
		//$parametro_altoValor = $this->Compensacion->buscarParametroAltoValor();
		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('estado','usuario','beneficiarios','bancos'));
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina');
				$this->set('titulo','Pagos N/a - Procesar');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores');
				$this->set('titulo','Pagos Proveedores - Procesar');
				break;
		}
	}
	 //js 06/10/2020
    /**
     * Accion para confirmar el procesamiento del pago Moneda Extranjera 
     */
    function pago_procesar_confirmar_me() {
	
        if (!$this->Session->check('cash.pagos_me.data'))
            $this->redirect('/consultas/integral');

        $estado = $this->Session->read('cash.pagos_me.operacion');
        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->read('cash.pagos_me.data');
        $bancos = $this->Activo->buscarBancos();

        $this->set(compact('estado', 'usuario', 'beneficiarios', 'bancos'));

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Procesar');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_p_me');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Procesar');
                break;
        }
    }

    //js 06/10/2020

	
	/**
     * Accion para indicar el resultado del procesamiento del pago 
     */
    function pago_procesar_resultado(){
        if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('cash.pagos.operacion');
        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->read('cash.pagos.data');
                        
                $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

        // Validar la fecha como ultima instancia
        
        if(!$this->PagoCash->validarFecha(array('fecha_valor'=>$this->data['PagoCash']['fecha_valor']))) $this->redirect('pago_detalle');
        
        if(
            ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) 
            &&
            (
                $validacion_tdc == null ||
                !isset($validacion_tdc['validado']) ||
                !$validacion_tdc['validado']
            )
        ){
            switch($estado['tipo']){
                case 'n':
                    $titulo_modulo = 'Pagos N/a - Procesar';
                    $seccion_actual = 'cash.pagos.nomina';
                    break;
                case 'p':
                    $titulo_modulo = 'Pagos Proveedores - Procesar';
                    $seccion_actual = 'cash.pagos.proveedores';
                    break;
            }
        
            $this->Session->write(
                'seguridad.validar_tdc',
                array(
                    'url_aceptar'=>'/cash/pago_procesar_resultado',
                    'layout'=>'interna',
                    'titulo'=>$titulo_modulo,
                    'pasos'=> array(
                        'Solicitud de Informacion',
                        'Confirmacion',
                        'Tarjeta de Coordenadas',
                        'Resultado Operacion'
                    ),
                    'paso_actual'=>2,
                    'seccion_actual'=>$seccion_actual
                )
            );

            $this->redirect('/seguridad/validar_tdc');
        }
        
        // Validaci?nti fraude
        $cuentas = $this->Activo->buscarCuentas();
        
        if(
            !in_array($this->data['PagoCash']['cuentaDebitar'], array_keys($cuentas))
        ){
            $this->redirect('/consultas/integral');
        }
        //  Fin validaci?nti fraude
        
        $exito = false;         // Bandera de no error en bd
        $error_pago = true;     // Bandera de error en as400
        $descripcion_error='';  // Descripcion error as400  
        $tipo_pago = '';

        // Comenzar transaccion bd
        
        $ds = $this->TransaccionPendiente->getDataSource();

        $ds->begin($this->TransaccionPendiente);    
        
        if(isset($estado['procesar_detalle'])){
            if(isset($this->data['PagoCash']['id'])){
                $this->TransaccionPendiente->id = $this->data['PagoCash']['id'];

                if ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
                    $status_process = 'ep';
                }else{
                    $status_process = 'pa';
                }
                
                if( $this->TransaccionPendiente->save( array( 
                    'TransaccionPendiente'=>array(
                        'estatus'=>$status_process,
                    ))) 
                ){
                    $exito = true;
                }
            }
        } else {
            // Buscar la cantidad de aprobaciones requeridas para la transaccion            
            // Todos los tipos de pago cash tienen la misma cantidad de aprobaciones
            // debido a que en la configuraci?e usuarios el combo es para los cuatro tipo de pagos
            // buscamos uno por uno y nos quedamos con el que exista, sin importar cual es debido a la
            // premisa anterior
            $transaccion_detalle = false;
            
            foreach(array(39,100016,40,100017) as $i){
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                    $usuario['id'],
                    $i
                );
                
                if($transaccion_detalle) break;
            }
            
            $nro_aprobaciones = 3; // Nunca debera quedar en este valor
            
            if( isset($transaccion_detalle['aprobaciones']) ){
                $nro_aprobaciones = $transaccion_detalle['aprobaciones'];
            }
            
            if(isset($this->data['PagoCash']['id'])) $this->TransaccionPendiente->id = $this->data['PagoCash']['id']; 

            if ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6){
                    $status_process = 'ep';
                }else{
                    $status_process = 'pa';
                }

            $res = $this->TransaccionPendiente->save(
                array(
                    'TransaccionPendiente'=>array(
                        'usuario_id'=>$usuario['id'],
                        'tipo'=> ($estado['tipo']=='n') ? 'nxa' : 'vxa',
                        'estatus'=> $status_process,
                        'lote'=>ltrim($this->data['PagoCash']['lote'], "0"),
                        'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                        'fecha_hora_valor'=>formatea_fecha_toMSSQL($this->data['PagoCash']['fecha_valor']['day'], $this->data['PagoCash']['fecha_valor']['month'], $this->data['PagoCash']['fecha_valor']['year']),
                        'monto'=>formatea_monto_toWs($this->data['PagoCash']['monto_total']),
                        'aprobaciones_requeridas'=>$nro_aprobaciones,
                        'tipo_rif'=>$usuario['tipo_rif'],
                        'rif'=>$usuario['rif'],
                        'cuenta_origen'=>encriptar($this->data['PagoCash']['cuentaDebitar']),
                        'descripcion'=>encriptar($this->data['PagoCash']['descripcion']),
                    )
                ),
                false
            );

            if($res){

                // Elimina los beneficiarios anteriores
                $res = $this->TransaccionBeneficiario->deleteAll(
                    array(
                        'transaccion_id' => $this->TransaccionPendiente->id
                    ),
                    false
                );
                
                $internas = false;
                $externas = false;

                foreach($this->data['Beneficiarios'] as &$i){
                    $i['transaccion_id'] = $this->TransaccionPendiente->id;

                    // Verifica si es una cuenta interna o externa
                    if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
                        $internas = true;
                    }
                    else{
                        $externas = true;
                    }
                }

                // tipo de pago deacuerdo a las transacciones
                if($internas && $externas){
                    $tipo_pago = ($estado['tipo']=='n') ? 'nxa' : 'vxa';
                }
                else if($internas){
                    $tipo_pago = ($estado['tipo']=='n') ? 'nxm' : 'vxm';
                }
                else{
                    $tipo_pago = ($estado['tipo']=='n') ? 'nxo' : 'vxo';
                }
                $this->TransaccionPendiente->saveField('tipo',$tipo_pago);
                // tipo de pago deacuerdo a las transacciones

                if($res){
                    $res = $this->TransaccionBeneficiario->saveAll(
                        $this->data['Beneficiarios'],
                        array('validate'=>false)
                    );
                    
                    if($res){
                        $exito = true;
                    } else {
                        $exito = false;
                        $this->log($ds->error, LOG_ERROR);
                    }
                } else {
                    $this->log($ds->error, LOG_ERROR);
                }
            } else {
                $this->log($ds->error, LOG_ERROR);
            }
        }

      
        
        // Ejecuta el servicio si es un master unico
        if($exito){
            if($usuario['rol_juridico_id']==2 || $usuario['rol_juridico_id'] == 6){
                $dataPago = array(
                    'lote'=> ltrim($this->data['PagoCash']['lote'], "0"), 
                    'tipo'=> $estado['tipo'], 
                    'cuentaDebitar'=> $this->data['PagoCash']['cuentaDebitar'], 
                    'monto_total'=> formatea_monto_toWs($this->data['PagoCash']['monto_total']), 
                    'fecha_valor'=> $this->data['PagoCash']['fecha_valor'],
                );

                $bdata = $this->TransaccionBeneficiario->obtenerListaDetalle($this->TransaccionPendiente->id);
                
                $monto_interna = 0;
                $monto_externa = 0;                             
                
                /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
                
                // Buscar el monto acumulado diario POR EMPRESA
                $fecha = $this->data['PagoCash']['fecha_valor']['year']. '-' . str_pad($this->data['PagoCash']['fecha_valor']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($this->data['PagoCash']['fecha_valor']['day'], 2, '0', STR_PAD_LEFT);
                
                                // MGGY Se toma en cuenta el horario de franja de compensacion en caso de que la fehca valor sea el mismo dia                                                        
                                $fecha_acumulado = $this->Activo->consultaFechaCompensacion(); 
                                
                                foreach($bdata as &$b){
                    // Verifica si es una cuenta interna o externa
                    if(substr(desencriptar($b['AfiliacionCash']['numero_cuenta']),0,4)=='0171'){
                        $monto_interna += formatea_monto_toWs($b['AfiliacionCash']['monto']);
                    }else{
                        $monto_externa += formatea_monto_toWs($b['AfiliacionCash']['monto']);
                    }
                }
                                
                                $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha);
                                //Si el lote contiene operaciones de otros bancos y la fecha valor es del mismo dia utiliza la fecha segun la franja de compensacion     
                                if(date('Y-m-d')==$fecha){
                                    $fecha_externa = $fecha_acumulado;
                                }else{
                                    $fecha_externa = $fecha;
                                }
                                $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha_externa);

                                $rolLimites = ($usuario['rol_juridico_id']==6) ? "1" : "2";

                                $usuario_master = $this->UsuarioCanal->find(
                                    'first',
                                    array(
                                        'conditions'=>array(
                                            'Usuario.tipo_rif'=>$usuario['tipo_rif'],
                                            'Usuario.rif'=>$usuario['rif'],
                                            'Usuario.rol_juridico_id'=>$rolLimites,
                                            'usuarioCanal.canal_id'=>Configure::read('canal.ib')
                                        )
                                    )
                                );
            
                                // Buscar los límites diarios
                                $limite_externas = null;
                                $limite_internas_terceros = null;                        
                                $limite_externas_nomina = null;
                                $limite_internas_terceros_nomina = null;                        
                                $limite_externas_proveedores = null;
                                $limite_internas_terceros_proveedores = null;
                                $limite_internas_terceros_proveedores_usd = null;
                                $limite_internas_terceros_proveedores_eur = null;
                                $limite_internas_terceros_nomina_usd = null;
                                $limite_internas_terceros_nomina_eur = null;

                                if($usuario_master){
                                    // Si hay master registrado, tomar los límites configurados por él              
                                    $limite_externas = $usuario_master['UsuarioCanal']['limite_externas'];
                                    $limite_internas_terceros = $usuario_master['UsuarioCanal']['limite_internas_terceros'];
                                    $limite_internas_terceros_usd = $usuario_master['UsuarioCanal']['limite_internas_terceros_usd'];
                                    $limite_internas_terceros_eur = $usuario_master['UsuarioCanal']['limite_internas_terceros_eur'];
                                    $limite_externas_nomina = $usuario_master['UsuarioCanal']['limite_externas_nomina'];
                                    $limite_internas_terceros_nomina = $usuario_master['UsuarioCanal']['limite_internas_terceros_nomina'];
                                    $limite_externas_proveedores = $usuario_master['UsuarioCanal']['limite_externas_proveedores'];
                                    $limite_internas_terceros_proveedores = $usuario_master['UsuarioCanal']['limite_internas_terceros_proveedores'];
                                    $limite_vip_master = $usuario_master['UsuarioCanal']['limite_vip'];
                                    $limite_internas_terceros_nomina_usd = $usuario_master['UsuarioCanal']['limite_interna_terceros_nomina_usd'];
                                    $limite_internas_terceros_nomina_eur = $usuario_master['UsuarioCanal']['limite_interna_terceros_nomina_eur'];
                                    $limite_internas_terceros_proveedores_usd = $usuario_master['UsuarioCanal']['limite_interna_terceros_proveedores_usd'];
                                    $limite_internas_terceros_proveedores_eur = $usuario_master['UsuarioCanal']['limite_interna_terceros_proveedores_eur'];
                                }
                                
                                if($estado['tipo']=='n'){
                                    
                                    if(is_numeric($usuario['limite_vip'])){
                                        // Tomamos el lote vip
                                        $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);      
                                        $limite_total_interno = $limite_vip['limite_internas_terceros_nomina'];
                                        $limite_total_externo = $limite_vip['limite_externas_nomina'];                                        
                                    }else{
                                        $limite_total_interno = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : $limite_internas_terceros_nomina ? $limite_internas_terceros_nomina : _p('limite_internas_terceros_nomina');
                                        
                                        $limite_total_externo = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : $limite_externas_nomina ? $limite_externas_nomina : _p('limite_externas_nomina');
                                    }                                                                                                                                                
                                     
                                    if($limite_total_interno){
                                        $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'];
                                    }
                                    
                                    if($limite_total_externo){
                                        $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_nomina'];
                                    }
                                }else{
                                    
                                    if(is_numeric($usuario['limite_vip'])){
                                        // Tomamos el lote vip
                                        $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);      
                                        $limite_total_interno = $limite_vip['limite_internas_terceros_proveedores'];
                                        $limite_total_externo = $limite_vip['limite_externas_proveedores'];                                        
                                    }else{
                                        $limite_total_interno = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : $limite_internas_terceros_proveedores ? $limite_internas_terceros_proveedores : _p('limite_internas_terceros_proveedores');
                                        
                                        $limite_total_externo = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : $limite_externas_proveedores ? $limite_externas_proveedores : _p('limite_externas_proveedores');
                                    } 
                                                                        
                                    if($limite_total_interno){
                                        $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'];
                                    }
                                    
                                    if($limite_total_externo){
                                        $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_proveedores'];
                                    }
                                }               
                /** PARA EL MONTO LIMITE EN LA TRANSACCION **/


                // Verificar los lotes acumulados de la transaccion // 
                if(
                    isset($monto_limite_interno) &&
                    isset($monto_limite_externo) &&
                    $monto_interna > $monto_limite_interno &&
                    $monto_externa > $monto_limite_externo
                ){
                    $descripcion_error = _m('monto.excedido.ambos','de la transaccion');
                } else if(
                    isset($monto_limite_interno) &&
                    $monto_interna > $monto_limite_interno
                ){
                    $descripcion_error = _m('monto.excedido.activo','de la transaccion');
                } else if(
                    isset($monto_limite_externo) &&
                    $monto_externa > $monto_limite_externo
                ){
                    $descripcion_error = _m('monto.excedido.otros','de la transaccion');
                } else{

                    if($this->Activo->ejecutarPagoCash($dataPago, $bdata)){
                        // el pago se ejecut?en
                        $error_pago = false;
                        
                        if(
                            $dataPago['fecha_valor']['day']==date('d') &&
                            $dataPago['fecha_valor']['month']==date('m') &&
                            $dataPago['fecha_valor']['year']==date('Y')
                        ){
                            // si el pago es hoy, revisar pago por pago para sumar si los montos aplicados en el acumulado
                            
                            $tx = $this->TransaccionPendiente->findById($this->TransaccionPendiente->id);
                            
                            $this->Activo->seguimientoGestion($tx);
                            
                            foreach($tx['Beneficiarios'] as $i){
                                if(isset($i['rechazado'])){
                                    // Restar montos rechazados del acumulado
                                    
                                    if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
                                        $monto_interna -= floatval($i['monto']);
                                    } else {
                                        $monto_externa -= floatval($i['monto']);
                                    }
                                    
                                    // Marcar en la base de datos el reverso, para no hacerlo dos veces
                                    
                                    $this->TransaccionBeneficiario->id = $i['id'];
                                    
                                    $this->TransaccionBeneficiario->saveField('reversada',1);
                                }
                            }
                        }
                        
                                                if($estado['tipo']=='n'){
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itn', formatea_monto_fromWs($monto_interna), $fecha);
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'en', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                                }else{
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itp', formatea_monto_fromWs($monto_interna), $fecha);
                                                    $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'ep', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                                }                       
                                        
                    } else {
                        switch($this->Activo->error){
                            case 1616:
                                $descripcion_error = _m('cuenta.sobregirada');
                                break;
                            case 862:
                                $descripcion_error =  _m('cuenta.sobregirada');
                                break;
                            case 215:
                                $descripcion_error =  _m('lote.repetido');
                                break;    
                            default:
                                $descripcion_error = _m('operacion.fallida');                               
                        }
                    }
                }
            } else {
                $error_pago = false;
            }
        }
        
        if($exito && !$error_pago){
            // Hacer commit en bd
            $ds->commit($this->TransaccionPendiente);
            
            // Registrar en bitᣯra
            switch ($tipo_pago){
                case 'nxm':
                case 'vxm':
                    $transaccion = ($estado['tipo']=='n' ? 39 : 40);
                    break;

                case 'nxo':
                case 'vxo':
                    $transaccion = ($estado['tipo']=='n' ? 100016 : 100017);
                    break;

                default:
                    $transaccion = ($estado['tipo']=='n' ? 100018 : 100019);
                    break;
            }
            $descripcion = 'A procesar lote nro.: ' . $this->data['PagoCash']['lote'];
            $this->Bitacora->registraBitacora($usuario['id'], $transaccion , NULL, NULL, NULL, NULL, $descripcion);
        } else {
            // Algo fall?acer rollback en bd
            $ds->rollback($this->TransaccionPendiente);
        }
        
        // Para el PDF
        $pdf = $this->data;
        $pdf['tipo'] = $estado['tipo'];
        if(isset($estado['procesar_detalle'])) $pdf['procesar_detalle'] = $estado['procesar_detalle'];
        $this->Session->write('cash.pagos.descarga', $pdf);

        $bancos = $this->Activo->buscarBancos();
        
        $this->set(compact('estado','usuario','beneficiarios', 'bancos', 'exito', 'error_pago','descripcion_error'));
        
        $this->layout = 'interna';
        
        switch($estado['tipo']){
            case 'n':
                $this->set('seccion_actual','cash.pagos.nomina');
                $this->set('titulo','Pagos N/a - Procesar');
                break;
            case 'p':
                $this->set('seccion_actual','cash.pagos.proveedores');
                $this->set('titulo','Pagos Proveedores - Procesar');
                break;
        }
        
        $this->Session->delete('cash.pagos.data');
        $this->Session->delete('cash.pagos.operacion');
    }
    
	//js 06/10/2020
    /**
     * Accion para indicar el resultado del procesamiento del pago 
     */
    function pago_procesar_resultado_me() {
	
        if (!$this->Session->check('cash.pagos_me.data'))
            $this->redirect('/consultas/integral');

        $estado = $this->Session->read('cash.pagos_me.operacion');
        $usuario = $this->Session->read('usuarios.datos');
        $this->data = $this->Session->read('cash.pagos_me.data');

       
        
        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

        // Validar la fecha como ultima instancia

        if (!$this->PagoCash->validarFecha(array('fecha_valor' => $this->data['PagoCash']['fecha_valor'])))
           $this->redirect('pago_detalle_me');
        
        if (
                ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) &&
                (
                $validacion_tdc == null ||
                !isset($validacion_tdc['validado']) ||
                !$validacion_tdc['validado']
                )
        ) {
            switch ($estado['tipo']) {
                case 'm':
                    $titulo_modulo = 'Pagos Nomina Moneda Extranjera - Procesar';
                    $seccion_actual = 'cash.pagos_me.nomina';
                    break;
                case 'x':
                    $titulo_modulo = 'Pagos Proveedores Moneda Extranjera - Procesar';
                    $seccion_actual = 'cash.pagos_me.proveedores';
                    break;
            }

            $this->Session->write(
                'seguridad.validar_tdc', array(
                'url_aceptar' => '/cash/pago_procesar_resultado_me',
                'layout' => 'interna',
                'titulo' => $titulo_modulo,
                'pasos' => array(
                    'Solicitud de Informacion',
                    'Confirmacion',
                    'Tarjeta de Coordenadas',
                    'Resultado Operacion',
                ),
                'paso_actual' => 2,
                'seccion_actual' => $seccion_actual
                )
            );

            $this->redirect('/seguridad/validar_tdc');
        }


        // Validaci?nti fraude
        $cuentas = $this->Activo->buscarCuentasme();

        if (
                !in_array($this->data['PagoCash']['cuentaDebitar'], array_keys($cuentas))
        ) {
            $this->redirect('/consultas/integral');
        }
        //  Fin validaci?nti fraude

        $exito = false;   // Bandera de no error en bd
        $error_pago = true;  // Bandera de error en as400
        $descripcion_error = ''; // Descripcion error as400	
        $tipo_pago = '';

        // Comenzar transaccion bd

        $ds = $this->TransaccionPendiente->getDataSource();
        
        
        
        $ds->begin($this->TransaccionPendiente);
        
        
        
        if (isset($estado['procesar_detalle_me'])) {
            if (isset($this->data['PagoCash']['id'])) {
                $this->TransaccionPendiente->id = $this->data['PagoCash']['id'];

                if ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
                    $status_process = 'ep';
                }else{
                    $status_process = 'pa';
                }

                if ($this->TransaccionPendiente->save(array(
                            'TransaccionPendiente' => array(
                                'estatus' => $status_process,
                    )))
                ) {
                    $exito = true;
                }
            }
        } else {
            // Buscar la cantidad de aprobaciones requeridas para la transaccion
            // Todos los tipos de pago cash tienen la misma cantidad de aprobaciones
            // debido a que en la configuracion de usuarios el combo es para los cuatro tipo de pagos
            // buscamos uno por uno y nos quedamos con el que exista, sin importar cual es debido a la
            // premisa anterior
            $transaccion_detalle = false;

            foreach (array(39, 101072, 40, 101073) as $i) {
                $transaccion_detalle = $this->TransaccionJuridicoDetalle->obtener(
                        $usuario['id'], $i
                );

                if ($transaccion_detalle)
                    break;
            }

            $nro_aprobaciones = 3; // Nunca deberia quedar en este valor

            if (isset($transaccion_detalle['aprobaciones'])) {
                $nro_aprobaciones = $transaccion_detalle['aprobaciones'];
            }

            if (isset($this->data['PagoCash']['id']))
                $this->TransaccionPendiente->id = $this->data['PagoCash']['id'];

            if ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
                $status_process = 'ep';
            }else{
                $status_process = 'pa';
            }

            $res = $this->TransaccionPendiente->save(
                    array(
                    'TransaccionPendiente' => array(
                    'usuario_id' => $usuario['id'],
                    'tipo' => ($estado['tipo'] == 'm') ? 'xxm' : 'mxm',
                    'estatus' => $status_process,
                    'lote' => ltrim($this->data['PagoCash']['lote'], "0" . 1),
                    'fecha_hora_registro' => date('Y-m-d H:i:s'),
                    'fecha_hora_valor' => formatea_fecha_toMSSQL($this->data['PagoCash']['fecha_valor']['day'], $this->data['PagoCash']['fecha_valor']['month'], $this->data['PagoCash']['fecha_valor']['year']),
                    'monto' =>$this->data['PagoCash']['monto_total'],
                    'aprobaciones_requeridas' => $nro_aprobaciones,
                    'tipo_rif' => $usuario['tipo_rif'],
                    'rif' => $usuario['rif'],
                    'cuenta_origen' => encriptar($this->data['PagoCash']['cuentaDebitar']),
                    'descripcion' => encriptar($this->data['PagoCash']['descripcion']),
                )
                    ), false
            );


            if ($res) {

                // Elimina los beneficiarios anteriores
                $res = $this->TransaccionBeneficiario->deleteAll(
                        array(
                    'transaccion_id' => $this->TransaccionPendiente->id
                        ), false
                );

                $internas = false;
                $externas = false;

                foreach ($this->data['Beneficiarios'] as &$i) {
                    $i['transaccion_id'] = $this->TransaccionPendiente->id;

                    // Verifica si es una cuenta interna o externa
                    if (substr(desencriptar($i['numero_cuenta']), 0, 4) == '0171') {
                        $internas = true;
                    } else {
                        $externas = true;
                    }
                }

                // tipo de pago deacuerdo a las transacciones
                if ($internas && $externas) {
                    $tipo_pago = ($estado['tipo'] == 'm') ? 'nxa' : 'vxa';
                } else if ($internas) {
                    $tipo_pago = ($estado['tipo'] == 'm') ? 'mxm' : 'xxm';
                } else {
                    $tipo_pago = ($estado['tipo'] == 'm') ? 'nxo' : 'vxo';
                }
                $this->TransaccionPendiente->saveField('tipo', $tipo_pago);
                // tipo de pago deacuerdo a las transacciones

                if ($res) {
                    $res = $this->TransaccionBeneficiario->saveAll(
                            $this->data['Beneficiarios'], array('validate' => false)
                    );

                    if ($res) {
                        $exito = true;
                    } else {
                        $exito = false;
                        $this->log($ds->error, LOG_ERROR);
                    }
                } else {
                    $this->log($ds->error, LOG_ERROR);
                }
            } else {
                $this->log($ds->error, LOG_ERROR);
            }
        }

//$total=mumber_format($this->data['PagoCash']['monto_total']);

       // echo $this->data['PagoCash']['monto_total'];

$total=number_format($this->data['PagoCash']['monto_total'],2,".",".");

 
      
        // Ejecuta el servicio si es un master unico
        if ($exito) {
            if ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
                $dataPago = array(
                    'lote' => ltrim($this->data['PagoCash']['lote'], "0"),
                    'tipo' => $estado['tipo'],
                    'cuentaDebitar' => $this->data['PagoCash']['cuentaDebitar'],
                    'monto_total' => $total,
                    'fecha_valor' => $this->data['PagoCash']['fecha_valor'],
                );
                

                              
                $bdata = $this->TransaccionBeneficiario->obtenerListaDetalle($this->TransaccionPendiente->id);

                $monto_interna = 0;
                $monto_externa = 0;

                /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
                // Buscar el monto acumulado diario POR EMPRESA
                $fecha = $this->data['PagoCash']['fecha_valor']['year'] . '-' . str_pad($this->data['PagoCash']['fecha_valor']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($this->data['PagoCash']['fecha_valor']['day'], 2, '0', STR_PAD_LEFT);

                // MGGY Se toma en cuenta el horario de franja de compensacion en caso de que la fehca valor sea el mismo dia                                                        
                $fecha_acumulado = $this->Activo->consultaFechaCompensacion();

                // verifica si la cuenta es interna            
                foreach ($bdata as &$b) {
                    // Verifica si es una cuenta interna o externa
                    if (substr(desencriptar($b['AfiliacionCash']['numero_cuenta']), 0, 4) == '0171') {
                          $cuentaMoneda =desencriptar($b['AfiliacionCash']['numero_cuenta']);
                        $monto_interna += formatea_monto_toWs($b['AfiliacionCash']['monto']);
                    } else {
                        $monto_externa += formatea_monto_toWs($b['AfiliacionCash']['monto']);
                    }
                }

                    $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'], $usuario['rif'], $fecha);

                if ($estado['tipo'] == 'm') {

                   

                    
                        $limite_total_interno_usd = $usuario['limite_internas_terceros_nomina_usd'] ? $usuario['limite_internas_terceros_nomina_usd'] : _p('limite_interna_terceros_nomina_usd');

                        $limite_total_interno_eur = $usuario['limite_internas_terceros_nomina_eur'] ? $usuario['limite_internas_terceros_nomina_eur'] : _p('limite_interna_terceros_nomina_eur');   


                       

                    if ($limite_total_interno_usd) { 



                     $monto_limite_interno_usd = $limite_total_interno_usd - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_nomina_usd'];

           
                    }

                    if ($limite_total_interno_eur) {
                    $monto_limite_interno_eur = $limite_total_interno_eur - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_nomina_eur'];
                    }

                } else {

                  
                        $limite_total_interno_usd = $usuario['limite_internas_terceros_proveedores_usd'] ? $usuario['limite_internas_terceros_proveedores_usd'] : _p('limite_interna_terceros_proveedores_usd');

                        $limite_total_interno_eur = $usuario['limite_internas_terceros_proveedores_eur'] ? $usuario['limite_internas_terceros_proveedores_eur'] : _p('limite_interna_terceros_proveedores_eur');

                    
                    if ($limite_total_interno_usd) {
                        $monto_limite_interno_usd = $limite_total_interno_usd - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_proveedores_usd'];


                    }


                    if ($limite_total_interno_eur) {
                        $monto_limite_interno_eur = $limite_total_interno_eur - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_proveedores_eur'];


                    }
                }

                /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
                // Verificar los limites acumulados de la transaccion 
                
                 //validamos el tipo de moneda 
                $productoDvalidar = substr($cuentaMoneda, -10);
                $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar, 12, '0', STR_PAD_LEFT), 'NROPARAM' => 2);
                $datosD = $this->Activo->validarProducto($datosDproducto);
                $errorD = intval($datosD['NIBPF3010Return']['error']['ERNU01']);

                if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD') {


                    if (
                        $monto_interna > $limite_total_interno_usd  
                    ) {

                        $descripcion_error = _m('monto.limite.config', 'de la transaccion');

                   }elseif (
                        $monto_interna > $monto_limite_interno_usd  
                    ) {
 
                        $descripcion_error = _m('monto.excedido.activo', 'de la transaccion');
                    
                    }     
                }elseif ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {
                   
                    if (
                        $monto_interna > $limite_total_interno_eur  
                    ) {

                        $descripcion_error = _m('monto.limite.config', 'de la transaccion');

                   }elseif($monto_interna > $monto_limite_interno_eur){

                    

                        $descripcion_error = _m('monto.excedido.activo', 'de la transaccion');
                    } 

                }
 
                if($descripcion_error == ''){





                    if ($this->Activo->ejecutarPagoCash($dataPago, $bdata)) {
                        // el pago se ejecuto bien
                        $error_pago = false;

                        if (
                                $dataPago['fecha_valor']['day'] == date('d') &&
                                $dataPago['fecha_valor']['month'] == date('m') &&
                                $dataPago['fecha_valor']['year'] == date('Y')
                        ) {
                            // si el pago es hoy, revisar pago por pago para sumar solo los montos aplicados en el acumulado

                            $tx = $this->TransaccionPendiente->findById($this->TransaccionPendiente->id);

                            $this->Activo->seguimientoGestion($tx);

                            foreach ($tx['Beneficiarios'] as $i) {
                                if (isset($i['rechazado'])) {
                                    // Restar montos rechazados del acumulado

                                    if (substr(desencriptar($i['numero_cuenta']), 0, 4) == '0171') {
                                        $monto_interna -= floatval($i['monto']);
                                    } else {
                                        $monto_externa -= floatval($i['monto']);
                                    }

                                    // Marcar en la base de datos el reverso, para no hacerlo dos veces

                                    $this->TransaccionBeneficiario->id = $i['id'];

                                    $this->TransaccionBeneficiario->saveField('reversada', 1);
                                }
                            }
                        }

                         if ($estado['tipo'] == 'm') {

                            if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD') {

                                                       
                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itnusd', formatea_monto_fromWs($monto_interna), $fecha); 

                            }elseif ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {

                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itneur', formatea_monto_fromWs($monto_interna), $fecha);
                            }
                            
                        } else {

                            if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD') {
                        
                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itpusd', formatea_monto_fromWs($monto_interna), $fecha);       
                            }elseif ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {

                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itpeur', formatea_monto_fromWs($monto_interna), $fecha);
                            }
                        } 
                    } else {

                        switch ($this->Activo->error) {
                            case 1616:
                                $descripcion_error = _m('cuenta.sobregirada');
                                break;
                            case 862:
                                $descripcion_error = _m('cuenta.sobregirada');
                                break;
                            case 215:
                                $descripcion_error = _m('lote.repetido');
                                break;
                            default:
                                $descripcion_error = _m('operacion.fallida');
                                break;
                        }
                    }
                }
            } else {
                $error_pago = false;
            }
        }

        if ($exito && !$error_pago) {
            // Hacer commit en bd
            $ds->commit($this->TransaccionPendiente);

            // Registrar en bitacora
            switch ($tipo_pago) {
                case 'mxm':
                case 'xxm':
                    $transaccion = ($estado['tipo'] == 'm' ? 101072 : 101073);
                    break;

                case 'nxo':
                case 'vxo':
                    $transaccion = ($estado['tipo'] == 'n' ? 100016 : 100017);
                    break;

                default:
                    $transaccion = ($estado['tipo'] == 'n' ? 100018 : 100019);
                    break;
            }
            $descripcion = 'A procesar lote nro.: ' . $this->data['PagoCash']['lote'];
            $this->Bitacora->registraBitacora($usuario['id'], $transaccion, NULL, NULL, NULL, NULL, $descripcion);
        } else {
            // Algo fall , hacer rollback en bd
            $ds->rollback($this->TransaccionPendiente);
        }

        // Para el PDF
        $pdf = $this->data;
        $pdf['tipo'] = $estado['tipo'];
        if (isset($estado['procesar_detalle']))
            $pdf['procesar_detalle'] = $estado['procesar_detalle'];
        $this->Session->write('cash.pagos.descarga', $pdf);

        $bancos = $this->Activo->buscarBancos();



        $this->set(compact('estado', 'usuario', 'beneficiarios', 'bancos', 'exito', 'error_pago', 'descripcion_error'));

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Procesar');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_p_me');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Procesar');
                break;
        }

        $this->Session->delete('cash.pagos.data');
        $this->Session->delete('cash.pagos.operacion');
    }

    // js 06/10/2020

	
	/**
	 * Accion para hacer carga masiva de pagos de n/a o proveedores
	 * @param string $tipo Indica el tipo de beneficiario. N/a: 'n',
	 * Proveedores: 'p'
	 */
	function pago_masivo($tipo=null){
		if( empty($tipo) || !in_array($tipo, array('n','p')) ) $this->redirect('/consultas/integral');
		
		// Valida el permiso de la transaccion
		if( 
			(!validarTransaccion(array(39,100016)) && $tipo=='n') ||
			(!validarTransaccion(array(40,100017)) && $tipo=='p') ) {
			$this->redirect('/consultas/integral');
		}
		
		// setlocale(LC_ALL,'es_VE.utf8'); PARA LINUX
                setlocale(LC_ALL,'es_VE.UTF-8'); // PARA LINUX
		
		set_time_limit(0);
		
		$usuario = $this->Session->read('usuarios.datos');
		                           
		if(!empty($this->data['PagoCash'])){
			
			$this->PagoCash->set($this->data);
			
			if ($this->PagoCash->validates(array('fieldList' => array(
				'cuentaDebitar',
				'archivo',
			)))){
				$error_data = array();
                                $referencias = array();
				$header_leido = false;
				$header_encontrado = false;
				$cantidad_pagos = 0;
				$this->data['Beneficiarios'] = array();
				$monto_acumulado = 0;
				$monto_acumulado_it = 0;
				$monto_acumulado_e = 0;
				$alerta = false;
				
				$internas = false;
				$externas = false;
				
				$archivo = @file($this->data['PagoCash']['archivo']['tmp_name']);
				$lineas = count($archivo);

				if(count($error_data)==0)
				for($i=0; $i < $lineas; $i++){
					
					// Actualiza el tiempo de la session en la base de datos
					$this->UsuarioCanal->sessTime($usuario['id'], _p('tiempo_session'));
					
					$temp = @explode(';', trim($archivo[$i]));
					$fecha_valor = '';

					if($temp[0]=='H' && !$header_leido){
						$header_encontrado = true;
						if(count($temp)!=6){
							$error_data[] = array(
								'registro'=>$i,
								'identificacion'=>'',
								'error'=>'Archivo invalido'
								
							);
							break;
						}
						
						// Data del pago
						$this->data['PagoCash']['lote'] = ltrim($temp[4], "0");
						$this->data['PagoCash']['descripcion'] = $temp[3];
						$this->data['PagoCash']['monto_total'] = $temp[2];
						$this->data['PagoCash']['cantidad_registros'] = $temp[1];
					
						// Fecha valor
						if( strlen(trim($temp[5]))==8 ){
							$this->data['PagoCash']['fecha_valor']['day'] = substr($temp[5],0,2);
							$this->data['PagoCash']['fecha_valor']['month'] = substr($temp[5],2,2);
							$this->data['PagoCash']['fecha_valor']['year'] = substr($temp[5],4);
						} else {
							$error_data[] = array(
								'registro'=>$i+1,
								'identificacion'=>'',
								'error'=>'Estructura del registro incompleto - '.trim($archivo[$i])
								
							);
							
							break;
						}						
						
						// Limite del usuario
						if($usuario['rol_juridico_id']>2){
							$transaccion_detalle_it = $this->TransaccionJuridicoDetalle->obtener(
								$usuario['id'],
								$tipo=='n' ? 39 : 40
							);
							
							$transaccion_detalle_e = $this->TransaccionJuridicoDetalle->obtener(
								$usuario['id'],
								$tipo=='n' ? 100016 : 100017
							);
							
							if($transaccion_detalle_it) {
								$this->data['PagoCash']['monto_limite_usuario_it'] = $transaccion_detalle_it['limite'];
							} else {
								$this->data['PagoCash']['monto_limite_usuario_it'] = '0,00'; // Denegar permiso a traves de los limites
							}
							
							if($transaccion_detalle_e) {
								$this->data['PagoCash']['monto_limite_usuario_e'] = $transaccion_detalle_e['limite'];
							} else {
								$this->data['PagoCash']['monto_limite_usuario_e'] = '0,00'; // Denegar permiso a traves de los limites
							}
							
							$this->data['PagoCash']['cantidad_aprobaciones'] = $transaccion_detalle_it['aprobaciones'];
						} else {
                                                    if($tipo=='n'){
                                                        if(is_numeric($usuario['limite_vip'])){
                                                            // Tomamos el lote vip
                                                            $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $limite_vip['limite_internas_terceros_nomina'];
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $limite_vip['limite_externas_nomina'];
                                                        }else{
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
                                                        } 
                                                        
                                                    }else{
                                                        if(is_numeric($usuario['limite_vip'])){
                                                            // Tomamos el l te vip
                                                            $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $limite_vip['limite_internas_terceros_proveedores'];
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $limite_vip['limite_externas_proveedores'];
                                                        }else{
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
                                                        }                                                        
                                                    }
							
							$this->data['PagoCash']['cantidad_aprobaciones'] = 0;
						}
						
						if($this->data['PagoCash']['monto_limite_usuario_it']){
							$this->data['PagoCash']['monto_limite_usuario_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_it']);
						} else {
							$this->data['PagoCash']['monto_limite_usuario_it'] = ''; // Ilimitado
						}
						
						if($this->data['PagoCash']['monto_limite_usuario_e']){
							$this->data['PagoCash']['monto_limite_usuario_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_e']);
						} else {
							$this->data['PagoCash']['monto_limite_usuario_e'] = ''; // Ilimitado
						}	
						
						
						// Verifica los datos del lote
						$this->PagoCash->set($this->data);
						
						// no validar el total ahora, ya que necesitamos calcular los montos
						// mismo banco y otro banco por separado
						
						$this->PagoCash->validate['monto_total']=array(
							'numerico'=>array(
								'rule'=>'montoEstricto'
							),
							'requerido'=>array(
								'rule'=>'notEmpty'
							),
						);

						if (!$this->PagoCash->validates(array('fieldList' => array(
							'descripcion',
							'lote',
							'monto_total',
							'cantidad_registros',
							'fecha_valor',
						)))){
							
							if(isset($this->PagoCash->validationErrors['descripcion'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Descripcion invalida- '.trim($archivo[$i])
									
								);
							}
							
							if(isset($this->PagoCash->validationErrors['lote'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Lote invalido- ' . trim($archivo[$i])
									
								);
							}
							
							if(isset($this->PagoCash->validationErrors['cantidad_registros'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Cantidad de registros invalida- ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->PagoCash->validationErrors['fecha_valor'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Fecha invᬩda - ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->PagoCash->validationErrors['monto_total'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Monto invalido - ' . trim($archivo[$i])
								);
							}
							
							break;
						}
						
						// verifica el numero de lote
						if(!$this->TransaccionPendiente->checkLote($this->data['PagoCash']['lote'])){
							$error_data[] = array(
								'registro'=>$i+1,
								'identificacion'=>'',
								'error'=>'Numero de lote duplicado - ' . trim($archivo[$i])
							);
							
							break;
						}
						
						$header_leido = true;
					} else if($temp[0]=='P') {
						if(count($temp)!=6){
							$error_data[] = array(
								'registro'=>$i+1,
								'identificacion'=>'',
								'error'=>'Estructura del registro incompleto - ' . trim($archivo[$i])
							);
							
							continue;
						}
						
						$this->data['AfiliacionCash']['tipo_rif'] = $usuario['tipo_rif'];
						$this->data['AfiliacionCash']['rif'] = desencriptar($usuario['rif']);
						$this->data['AfiliacionCash']['tipo_cedula'] = substr($temp[1], 0, 1);
						$this->data['AfiliacionCash']['cedula'] = in_array($this->data['AfiliacionCash']['tipo_cedula'],array('J','G')) ? substr($temp[1], 1) : ltrim(substr($temp[1], 1),'0');
						$this->data['AfiliacionCash']['monto'] = $temp[2];
						$this->data['AfiliacionCash']['concepto'] = $temp[3];
						$this->data['AfiliacionCash']['referencia'] = $temp[4];
						$this->data['AfiliacionCash']['tipo'] = $tipo;
						
                                                $referencias[$i] = $temp[4];
                                                
						$identificacion = $this->data['AfiliacionCash']['tipo_cedula'].$this->data['AfiliacionCash']['cedula'];
						
						// Verifica los datos del lote
						$this->AfiliacionCash->set($this->data);
						
						$this->AfiliacionCash->validate['cedula'] = array(
							'longitud'=>array(
								'rule'=>'validaLongitud'
							),
							'requerido'=>array(
								'rule'=>'notEmpty'
							),
							'rif'=>array(
								'rule'=>'validaRif'
							)
						);
						
						// Buscar en la base de datos
						
						$beneficiario = $this->AfiliacionCash->find(
							'first',
							array(
								'conditions'=>array(
									'AfiliacionCash.tipo_rif' => $usuario['tipo_rif'],
									'AfiliacionCash.rif' => $usuario['rif'],
									'AfiliacionCash.tipo_cedula' => $this->data['AfiliacionCash']['tipo_cedula'],
									'AfiliacionCash.cedula' => encriptar($this->data['AfiliacionCash']['cedula']),
									//'AfiliacionCash.tipo' => $tipo									
								)
							)
						);						
						
						// Cambiar la validaci?el monto para que falle si el monto no tiene decimales
						
						$this->AfiliacionCash->validate['monto'] = array(
							'numerico'=>array(
								'rule'=>'montoEstricto'
							),
							'requerido'=>array(
								'rule'=>'notEmpty'
							)
						);
						
						if (!$this->AfiliacionCash->validates(array('fieldList' => array(
							'tipo_cedula',
							'cedula',
							'monto',
							'concepto',
							'referencia',
						)))){
							if(isset($this->AfiliacionCash->validationErrors['tipo_cedula'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'RIF o Cedula invalida - ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->AfiliacionCash->validationErrors['cedula'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'RIF o Cedula invalida - ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->AfiliacionCash->validationErrors['monto'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'Monto de transaccion invalida- ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->AfiliacionCash->validationErrors['concepto'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'Instruccion invalida Concepto del Pago - ' . trim($archivo[$i])
								);
							}
							
							if(isset($this->AfiliacionCash->validationErrors['referencia'])){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>'Instruccion invalida o Referencia Duplicada - ' . trim($archivo[$i])
								);
							}
							
							// A pesar del error sumar los totales si es posible
							
							$cantidad_pagos++;
							
							if(!isset($this->AfiliacionCash->validationErrors['monto'])){
								// Sumar el monto al acumulado si esten
								$monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);
								$monto_acumulado += $monto_tx;
                                $monto_acumulado = round($monto_acumulado,2);
								
								if($beneficiario){
									if(substr(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']),0,4)=='0171'){
										$monto_acumulado_it += $monto_tx;
									} else {
										$monto_acumulado_e += $monto_tx;
									}
								}
							}

							continue;
						}
						
						$cantidad_pagos++;
						$monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);
						$monto_acumulado += $monto_tx;
                        $monto_acumulado = round($monto_acumulado,2);
						
						if($beneficiario['AfiliacionCash']['tipo']=='n' || $beneficiario['AfiliacionCash']['tipo']=='p'){
							
							
							// Validar el estatus del nro de cuenta
							// Siempre y cuando sea cuenta banco activo
							if(preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/',desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']))){
								$monto_acumulado_it += $monto_tx;
								
								$productoValidar = substr(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']),-10);
								
								if(substr(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']),10,1)==9 || substr(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']),10,1)==8){
									
									$mensaje_error = _m('nobs');

									$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>$mensaje_error .' - '.desencriptar($beneficiario['AfiliacionCash']['numero_cuenta'])
								);		
								}
								
								$valorCuenta= array(
									'NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 
									'NROPARAM' => 5,
									'NACIONALIDAD'=>trim($beneficiario['AfiliacionCash']['tipo_cedula']),
									'NROIDENTIFICACION'=>str_pad(trim(desencriptar($beneficiario['AfiliacionCash']['cedula'])),12,'0',STR_PAD_LEFT)
								);
								
								$datos = $this->Activo->validarProducto($valorCuenta);
								
								$error = (isset($datos['NIBPF3010Return']['error']['ERNU01'])) ? intval($datos['NIBPF3010Return']['error']['ERNU01']) : 0;
								
								$internas = true;
								
							}else{
								$monto_acumulado_e += $monto_tx;
								
								$error = 0;
								$datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] = 'A';
								$externas = true;
							}


                            
                            //Validar si el monto excede a alto valor cuando la nro cuenta no es banco activo
                            //validar monto mayor a alto valor lo establecido por bcv para otros bancos


                            $mtoTope = _p('cash_alto_valor');
                            $cuentaVal = trim(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']));
                            $cuentaVal = substr($cuentaVal,0,4);

                            if($cuentaVal <> '0171'){
                                $montoVal = $monto_tx;
                                $montoVal = str_replace(",",".",$montoVal);
                                if($montoVal > $mtoTope){
                                    $mtoTope = 
                                    $mensaje_error = _m('error.nomina.alto.valor') . " " . formatea_monto_fromWs($mtoTope) . " Bs.";

                                    $error_data[] = array(
                                    'registro'=>$i+1,
                                    'identificacion'=>$identificacion,
                                    'error'=>$mensaje_error
                                    );
                                }
                            }
							
							if($error!=0 || !in_array($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'],array('A','T'))){
								if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
									$mensaje_error = _m('cuenta.controlada', 'con la cuenta del beneficiario');
								} else {
									$mensaje_error = _m('cuenta.inactiva', 'la cuenta del beneficiario');
								}
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>$identificacion,
									'error'=>$mensaje_error .' - '.desencriptar($beneficiario['AfiliacionCash']['numero_cuenta'])
								);								
							} else {
								$beneficiario['AfiliacionCash']['monto'] = $this->data['AfiliacionCash']['monto'];
								$beneficiario['AfiliacionCash']['concepto'] = encriptar($this->data['AfiliacionCash']['concepto']);
								$beneficiario['AfiliacionCash']['referencia'] = encriptar($this->data['AfiliacionCash']['referencia']);
							}
						} elseif($beneficiario['AfiliacionCash']['tipo']=='m' || $beneficiario['AfiliacionCash']['tipo']=='x') {
							$error_data[] = array(
								'registro'=>$i+1,
								'identificacion'=>$identificacion,
								'error'=>'Empresa/Cliente no esta afiliado para pagos moneda nacional - ' . trim($archivo[$i])
							);
						}else{
                            $error_data[] = array(
                                'registro'=>$i+1,
                                'identificacion'=>$identificacion,
                                'error'=>'Empresa/Cliente no esta afiliado/no fue encontrado - ' . trim($archivo[$i])
                            );
                        }
						
						// Verifica si es para proveedor
						if($tipo=='p'){
							$j=$i+1;
							$monto_detalle = 0;
							$tiene_detalle = false;
							
							// realiza una busqueda en las siguientes posiciones para verificar si tiene detalle
							do{
								$temp2 = @explode(';', trim($archivo[$j]));
								if($temp2[0]=='D'){
									$tiene_detalle = true;
									
									// Verificacion de la data
									if(count($temp2)!=4){
										$error_data[] = array(
											'registro'=>$j+1,
											'identificacion'=>$identificacion,
											'error'=>'Estructura del registro incompleto - ' . trim($archivo[$j])
										);
									} else {
										$this->data['DetallePagoProveedor']['tipo_detalle'] = $temp2[1];
										$this->data['DetallePagoProveedor']['referencia'] = $temp2[2];
										$this->data['DetallePagoProveedor']['monto'] = $temp2[3];

										$this->DetallePagoProveedor->set($this->data);
										if (!$this->DetallePagoProveedor->validates(array('fieldList' => array(
											'monto',
											'tipo_detalle',
											'referencia',
										)))){

											if(isset($this->DetallePagoProveedor->validationErrors['monto'])){
												$error_data[] = array(
													'registro'=>$j+1,
													'identificacion'=>$identificacion,
													'error'=>'Monto de transaccion invalida- ' . trim($archivo[$j])
												);
											}

											if(isset($this->DetallePagoProveedor->validationErrors['tipo_detalle'])){
												$error_data[] = array(
													'registro'=>$j+1,
													'identificacion'=>$identificacion,
													'error'=>'Instruccion invalida tipo detalle - ' . trim($archivo[$j])
												);
											}

											if(isset($this->DetallePagoProveedor->validationErrors['referencia'])){
												$error_data[] = array(
													'registro'=>$j+1,
													'identificacion'=>$identificacion,
													'error'=>'Instruccion invalida o Duplicada referencia - ' . trim($archivo[$j])
												);
											}
										} else {
											// Sumatoria de los montos del detalle
											$monto_detalle += formatea_monto_toWs($this->data['DetallePagoProveedor']['monto']);
											
											// formateo de la data
											$detalle['DetallePagoProveedor'] = $this->data['DetallePagoProveedor'];
											$detalle['DetallePagoProveedor']['monto'] = formatea_monto_toWs($this->data['DetallePagoProveedor']['monto']);
											$detalle['DetallePagoProveedor']['referencia'] = encriptar($this->data['DetallePagoProveedor']['referencia']);
											
											// Asignacion del detalle al proveedor
											$beneficiario['AfiliacionCash']['detalle'][] = $detalle;
										}
									}
									$j++;
								}
							}while($temp2[0]=='D');
							
							// Verifica monto acumulado del detalle
							if( $tiene_detalle && formatea_monto_toWs($this->data['AfiliacionCash']['monto'])!=formatea_monto_toWs($monto_detalle)){
								$error_data[] = array(
									'registro'=>$i+1,
									'identificacion'=>'',
									'error'=>'Monto detalle no cuadra con sumatoria'
								);
							}
							
						}// FIN de verifica si es para proveedor
						
							
						$this->data['Beneficiarios'][]=$beneficiario;
							
						unset($this->data['AfiliacionCash']);
					}                                                                                
					
				}
				
                                if(count($referencias) > count(array_unique($referencias))){
                                    $error_data[] = array(
                                        'registro'=>1,
                                        'identificacion'=>'',
                                        'error'=>'Existen Referencias Duplicadas en el archivo'
                                    );
              

                                      }
                                        
				// Verifica monto acumulado
				if($header_leido){
                    $monto_acumulado=str_replace('.', ',', $monto_acumulado);


					if(formatea_monto_toWs($this->data['PagoCash']['monto_total']) != formatea_monto_toWs($monto_acumulado)){
						$error_data[] = array(
							'registro'=>1,
							'identificacion'=>'',
							'error'=>'Monto total no cuadra con sumatoria'
						);
					}
				}

				// Ahora si validar monto total con respecto a limites
				
				$this->PagoCash->data['PagoCash']['monto_total_it'] = $monto_acumulado_it;
				$this->PagoCash->data['PagoCash']['monto_total_e'] = $monto_acumulado_e;
				
				$this->PagoCash->validate['monto_total']=array(
					'limiteMontoTotal'=>array(
						'rule'=>'limiteMontoTotal'
					)
				);
				
				$this->PagoCash->validationErrors=array();
				
				if (!$this->PagoCash->validates(array('fieldList' => array(
					'monto_total'
				)))){
					if($this->PagoCash->validationErrors['monto_total']=='montoLimiteAmbos'){
						$mensaje_error = _m('monto.limite.usuario','de la transaccion activo y otros bancos');
					} elseif($this->PagoCash->validationErrors['monto_total']=='montoLimiteIt'){
						$mensaje_error = _m('monto.limite.usuario','de la transaccion activo');
					} elseif($this->PagoCash->validationErrors['monto_total']=='montoLimiteE'){
						$mensaje_error = _m('monto.limite.usuario','de la transaccion otros bancos');
					} elseif($this->PagoCash->validationErrors['monto_total']=='nopermitidaE'){
						if($tipo=='n'){
							$mensaje_error = _m('op.nopermitida','de Pagos de N/a Otros Bancos');
						} else {
							$mensaje_error = _m('op.nopermitida','de Pagos de Proveedores Otros Bancos');
						}
					} elseif($this->PagoCash->validationErrors['monto_total']=='nopermitidaIt'){
						if($tipo=='n'){
							$mensaje_error = _m('op.nopermitida','de Pagos de N/a en Activo');
						} else {
							$mensaje_error = _m('op.nopermitida','de Pagos de Proveedores en Activo');
						}
					}
					
					if(isset($mensaje_error)){
						$error_data[] = array(
							'registro'=>1,
							'identificacion'=>'',
							'error'=>$mensaje_error
						);
					}					
				}
				
				// verifica cantidad de pagos
				if($header_leido){
					if(!$cantidad_pagos || ($cantidad_pagos != $this->data['PagoCash']['cantidad_registros']) ){
						$error_data[] = array(
							'registro'=>1,
							'identificacion'=>'',
							'error'=>'Total transacciones no cuadra con sumatoria'
						);
					}
				}
				
				// Si es un pago proveedores verifica el monto total con el del ultimo lote
				if($header_leido){
					$result = $this->TransaccionPendiente->find(
						'first',
						array(
							'fields'=>array('monto'),
							'conditions'=>array(
								'TransaccionPendiente.tipo_rif'=>$usuario['tipo_rif'],
								'TransaccionPendiente.rif'=>$usuario['rif'],
								'TransaccionPendiente.tipo'=>$tipo=='p'?array('vxa','vxm','vxo'):array('nxa','nxm','nxo'),
							),
							'order'=>array('TransaccionPendiente.id DESC'),
							'limit' => 1,
						)
					);
					
					if($result && isset($this->data['PagoCash']['monto_total']) && $result['TransaccionPendiente']['monto']==formatea_monto_toWs($this->data['PagoCash']['monto_total'])){
						$alerta = true;
					}
				}
				
				//Si no se envian un archivo que contenga la cabecera, es un archivo invalido
				if(!$header_encontrado){
					$error_data[] = array(
						'registro'=>1,
						'identificacion'=>'',
						'error'=>'Archivo invalido'
					);
				}
				
				// verifica si hubo errores
				if(count($error_data)){
					
					// Ordenar arreglo
					usort($error_data,'ordernar_errores_por_linea');
		
					$estado['tipo'] = $tipo;
					$estado['errores'] = $error_data;
					$estado['archivo'] = $this->data['PagoCash']['archivo'];
					$this->Session->write('cash.pagos.data', $estado);
					$this->redirect('pago_masivo_error');
				} else {
					//Formateo de monto para la presentacion
					if(isset($this->data['PagoCash']['monto_total'])) $this->data['PagoCash']['monto_total'] = formatea_monto_fromWs( formatea_monto_toWs($this->data['PagoCash']['monto_total']));
					
					// tipo de pago deacuerdo a las transacciones
					if($internas && $externas){
						$this->data['tipo_pago'] = ($tipo=='n') ? 'nxa' : 'vxa';
					}
					else if($internas){
						$this->data['tipo_pago'] = ($tipo=='n') ? 'nxm' : 'vxm';
					}
					else{
						$this->data['tipo_pago'] = ($tipo=='n') ? 'nxo' : 'vxo';
					}
					
					$this->data['tipo'] = $tipo;
					$this->Session->write('cash.pagos.data', $this->data);
					
					if($alerta){
						$this->redirect('pago_masivo_alerta');
					}
					else{
						$this->redirect('pago_masivo_confirmar');
					}
				}
			}
		}

        //Eliminar variables de session 
        $this->Session->delete('cash.pagos');
		
		$cuentas = $this->Activo->buscarCuentasSaldos();
		
		$this->set(compact('tipo','cuentas'));
		
		
		$this->layout = 'interna';
		
		switch($tipo){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina.masiva');
				$this->set('titulo','Pagos N/a - Carga Masiva');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores.masiva');
				$this->set('titulo','Pagos Proveedores - Carga Masiva');
				break;
		}
	}


    /**************************START PAGO NOM/PRO EXPRESS*************************/

    /**
     * Accion para hacer carga masiva de pagos de n/a o proveedores
     * @param string $tipo Indica el tipo de beneficiario. N/a: 'n',
     * Proveedores: 'p'
     */
    function pago_masivo_express($tipo=null){

        if( empty($tipo) || !in_array($tipo, array('n','p')) ) $this->redirect('/consultas/integral');
        
        // Valida el permiso de la transaccion
        if( 
            /*(!validarTransaccion(array(39,100016)) && $tipo=='n') ||*/
            (!validarTransaccion(array(101107,101108)) && $tipo=='p') ) {
            $this->redirect('/consultas/integral');
        }
        
        setlocale(LC_ALL,'es_VE.UTF-8'); // PARA LINUX
        
        set_time_limit(0);

        $tipo='p';



        
        $usuario = $this->Session->read('usuarios.datos');
                                   
        if(!empty($this->data['PagoCash'])){
            
            $this->PagoCash->set($this->data);

            //Estas variables me permiten controlar en pago_masivo_express_resultado
            //que el proceso de subir procesr el archivo se ejecute una sola vez, cuando
            //en la vista pago_masivo_express_confirmar presionen varaias veces 
            //el boton procesar archivo cuando tarda en procesar la nomina   
            $this->Session->write('cash.pagos.swPantallaResultado', 0);
            $this->Session->write('cash.pagos.swExito', true);
            $this->Session->write('cash.pagos.descripcionError', "");
            //el boton procesar archivo cuando tarda en procesar la nomina   

            
            if ($this->PagoCash->validates(array('fieldList' => array(
                'cuentaDebitar',
                'archivo',
            )))){
                $error_data = array();
                $referencias = array();
                $header_leido = false;
                $header_encontrado = false;
                $cantidad_pagos = 0;
                $this->data['Beneficiarios'] = array();
                $monto_acumulado = 0;
                $monto_acumulado_it = 0;
                $monto_acumulado_e = 0;
                $alerta = false;
                
                $internas = false;
                $externas = false;
                
                $archivo = @file($this->data['PagoCash']['archivo']['tmp_name']);
                $lineas = count($archivo);

 
                //permitir Obtener Nro de referencia no existente del cliente
                $documentoRif = $usuario['tipo_rif'];
                $rif = $usuario['rif'];

                //$nroReferencia = $this->TransaccionBeneficiario->obtenerReferencia($documentoRif, $rif);

                $nroReferencia = 0;

                //Nuevos Nomina Express
                $nroTotalClientes = 0;
                $mtoTotalClientes = 0.00;
                $errorFatal = 0;
                $nroTotalClientesError = 0;
                $mtoTotalClientesError = 0.00;

                $arrayErrorPago = array();  
                $swArrayErrorPago = 0;



                if(count($error_data)==0)
                for($i=0; $i < $lineas; $i++){
                    
                    // Actualiza el tiempo de la session en la base de datos
                    $this->UsuarioCanal->sessTime($usuario['id'], _p('tiempo_session'));
                    
                    $temp = @explode(';', trim($archivo[$i]));
                    $fecha_valor = '';

                    if($temp[0]=='H' && !$header_leido){
                        $header_encontrado = true;
                        if(count($temp)!=6){
                            $error_data[] = array(
                                'registro'=>$i,
                                'identificacion'=>'',
                                'error'=>'Archivo invalido'
                                
                            );
                            $errorFatal = 1;
                            break;
                        }
                        
                        // Data del pago
                        $this->data['PagoCash']['lote'] = ltrim($temp[4], "0");
                        $this->data['PagoCash']['descripcion'] = $temp[3];
                        $this->data['PagoCash']['monto_total'] = $temp[2];
                        $this->data['PagoCash']['cantidad_registros'] = $temp[1];
                    
                        // Fecha valor
                        if( strlen(trim($temp[5]))==8 ){
                            $this->data['PagoCash']['fecha_valor']['day'] = substr($temp[5],0,2);
                            $this->data['PagoCash']['fecha_valor']['month'] = substr($temp[5],2,2);
                            $this->data['PagoCash']['fecha_valor']['year'] = substr($temp[5],4);
                        } else {
                            $error_data[] = array(
                                'registro'=>$i+1,
                                'identificacion'=>'',
                                'error'=>'Estructura del registro incompleto - '.trim($archivo[$i])
                                
                            );
                            $errorFatal = 1;
                            
                            break;
                        }                       
                        
                        // Limite del usuario
                        if($usuario['rol_juridico_id']>2 && $usuario['rol_juridico_id']!=6){
                            $transaccion_detalle_it = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                '101107'
                            );
                            
                            $transaccion_detalle_e = $this->TransaccionJuridicoDetalle->obtener(
                                $usuario['id'],
                                '101108'

                            );
                            
                            if($transaccion_detalle_it){
                                $this->data['PagoCash']['monto_limite_usuario_it'] = $transaccion_detalle_it['limite'];
                            }else{
                                $this->data['PagoCash']['monto_limite_usuario_it'] = '0,00'; // Denegar permiso a traves de los limites
                            }
                            
                            if($transaccion_detalle_e) {
                                $this->data['PagoCash']['monto_limite_usuario_e'] = $transaccion_detalle_e['limite'];
                            } else {
                                $this->data['PagoCash']['monto_limite_usuario_e'] = '0,00'; // Denegar permiso a traves de los limites
                            }
                            
                            $this->data['PagoCash']['cantidad_aprobaciones'] = $transaccion_detalle_it['aprobaciones'];
                        } else {
                                                    if($tipo=='n'){
                                                        if(is_numeric($usuario['limite_vip'])){
                                                            // Tomamos el lote vip
                                                            $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                                          
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $limite_vip['limite_internas_terceros_nomina'];
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $limite_vip['limite_externas_nomina'];
                                                        }else{
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
                                                        } 
                                                        
                                                    }else{
                                                        if(is_numeric($usuario['limite_vip'])){
                                                            // Tomamos el lote vip
                                                            $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                                          
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $limite_vip['limite_internas_terceros_proveedores'];
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $limite_vip['limite_externas_proveedores'];
                                                        }else{
                                                            $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');
                                                            $this->data['PagoCash']['monto_limite_usuario_e'] = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
                                                        }                                                        
                                                    }
                            
                            $this->data['PagoCash']['cantidad_aprobaciones'] = 0;
                        }
                        
                        if($this->data['PagoCash']['monto_limite_usuario_it']){
                            $this->data['PagoCash']['monto_limite_usuario_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_it']);
                        } else {
                            $this->data['PagoCash']['monto_limite_usuario_it'] = ''; // Ilimitado
                        }
                        
                        if($this->data['PagoCash']['monto_limite_usuario_e']){
                            $this->data['PagoCash']['monto_limite_usuario_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_e']);
                        } else {
                            $this->data['PagoCash']['monto_limite_usuario_e'] = ''; // Ilimitado
                        }   
                        
                        
                        // Verifica los datos del lote
                        $this->PagoCash->set($this->data);
                       
                        // no validar el total ahora, ya que necesitamos calcular los montos
                        // mismo banco y otro banco por separado
                        
                       $this->PagoCash->validate['monto_total']=array(
                            'numerico'=>array(
                                'rule'=>'montoEstricto'
                            ),
                            'requerido'=>array(
                                'rule'=>'notEmpty'
                            ),
                        );

                        if (!$this->PagoCash->validates(array('fieldList' => array(
                            'descripcion',
                            'lote',
                            'monto_total',
                            'cantidad_registros',
                            'fecha_valor',
                        )))){
                            
                            if(isset($this->PagoCash->validationErrors['descripcion'])){
                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'identificacion'=>'',
                                    'error'=>'Descripcion invalida- '.trim($archivo[$i])
                                    
                                );
                            }
                            
                            if(isset($this->PagoCash->validationErrors['lote'])){
                                $errorFatal = 1;
                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'identificacion'=>'',
                                    'error'=>'Lote invalido- ' . trim($archivo[$i])
                                    
                                );
                            }
                            
                            if(isset($this->PagoCash->validationErrors['cantidad_registros'])){
                                $errorFatal = 1;
                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'identificacion'=>'',
                                    'error'=>'Cantidad de registros invalida- ' . trim($archivo[$i])
                                );
                            }
                            
                            if(isset($this->PagoCash->validationErrors['fecha_valor'])){
                                $errorFatal = 1;
                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'identificacion'=>'',
                                    'error'=>'Fecha invalida - ' . trim($archivo[$i])
                                );
                            }
                            
                            if(isset($this->PagoCash->validationErrors['monto_total'])){
                                $errorFatal = 1;
                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'identificacion'=>'',
                                    'error'=>'Monto invalida - ' . trim($archivo[$i])
                                );
                            }
                            
                            break;
                        }
                        
                        // verifica el numero de lote
                        if(!$this->TransaccionPendiente->checkLote($this->data['PagoCash']['lote'])){
                            $errorFatal = 1;
                            $error_data[] = array(
                                'registro'=>$i+1,
                                'identificacion'=>'',
                                'error'=>'Numero de lote duplicado - ' . trim($archivo[$i])
                            );
                            
                            break;
                        }
                        
                        $header_leido = true;
                    } else if($temp[0]=='P') {
                        if(count($temp)!=10){
                            $error_data[] = array(
                                'registro'=>$i+1,
                                'identificacion'=>'',
                                'error'=>'Estructura del registro incompleto - ' . trim($archivo[$i])
                            );
                            $errorFatal = 1;
                            continue;
                        }
                        
                        $swErrorIndividual = 0;

                        
                        $mensajeErrorPago = "";


                        $nroReferencia = $nroReferencia + 1;

                        $nroRefeCompleto = str_pad($nroReferencia, 9, "0", STR_PAD_LEFT);


                        $nroReferenciaEnc = encriptar($nroRefeCompleto); 
                        

                        //DATOS PAGADOR
                        $this->data['AfiliacionCash']['tipo_rif'] = $usuario['tipo_rif'];
                        $this->data['AfiliacionCash']['rif'] = desencriptar($usuario['rif']);

                        //DATOS BENEFICIARIO
                        $this->data['AfiliacionCash']['tipo_cedula'] = substr($temp[1], 0, 1);
                        $this->data['AfiliacionCash']['cedula'] = in_array($this->data['AfiliacionCash']['tipo_cedula'],array('J','G')) ? substr($temp[1], 1) : ltrim(substr($temp[1], 1),'0');

                        $cedulaCliente = $this->data['AfiliacionCash']['cedula'];

                        $this->data['AfiliacionCash']['nombre'] = $temp[6];
                        $this->data['AfiliacionCash']['numero_cuenta'] = $temp[7];
                        
                        //se utiliza para validar el nro de cuenta que solo cumpla 
                        //con vacio, digitos y duplicidad 
                        $this->data['AfiliacionCash']['numero_cuenta_express'] = $temp[7]; 

                        $this->data['AfiliacionCash']['email'] = $temp[8];
                        $this->data['AfiliacionCash']['celular'] = $temp[9];
                        $this->data['AfiliacionCash']['monto'] = $temp[2];
                        $this->data['AfiliacionCash']['concepto'] = $temp[3];
                        $this->data['AfiliacionCash']['referencia'] = $nroReferenciaEnc;
                        $this->data['AfiliacionCash']['tipo'] = $tipo;

                        $datosPago = trim($this->data['AfiliacionCash']['cedula']) . " / " . $this->data['AfiliacionCash']['nombre'] . " / " . $this->data['AfiliacionCash']['numero_cuenta'] . " / " . $this->data['AfiliacionCash']['monto'] . " / " . $this->data['AfiliacionCash']['email'];



                        
                        $referencias[$i] = $nroReferencia;
                                                
                        $identificacion = $this->data['AfiliacionCash']['tipo_cedula'].$this->data['AfiliacionCash']['cedula'];
                        
                        // Verifica los datos del lote
                        $this->AfiliacionCash->set($this->data);
                        
                        $this->AfiliacionCash->validate['cedula'] = array(
                            'longitud'=>array(
                                'rule'=>'validaLongitud'
                            ),
                            'requerido'=>array(
                                'rule'=>'notEmpty'
                            ),
                            'rif'=>array(
                                'rule'=>'validaRif'
                            )
                        );

                        $beneficiario = $this->data['AfiliacionCash'];

                        // Cambiar la validacion del monto para que falle si el monto no tiene decimales
                        
                        $this->AfiliacionCash->validate['monto'] = array(
                            'numerico'=>array(
                                'rule'=>'montoEstricto'
                            ),
                            'requerido'=>array(
                                'rule'=>'notEmpty'
                            )
                        );
                        
                        if (!$this->AfiliacionCash->validates(array('fieldList' => array(
                            'tipo_cedula',
                            'cedula',
                            'monto',
                            'concepto',
                            'numero_cuenta',
                            'email'
                        )))){
                            $swValidarCedula = 0;
                            if(isset($this->AfiliacionCash->validationErrors['tipo_cedula'])){
                                $swValidarCedula = 1;
                                $swErrorIndividual = 1;
                                if(!empty($mensajeErrorPago)){
                                    $mensajeErrorPago = 'RIF o Cedula invalida' . $identificacion;
                                }else{
                                    $mensajeErrorPago.= ' - RIF o Cedula invalida - ' . $identificacion;
                                }  

                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'cedula'=>$cedulaCliente,
                                    'identificacion'=>$identificacion,
                                    'error'=>'RIF o Cedula invalida - ' . trim($archivo[$i])
                                );
                            }
                            
                            if($swValidarCedula ==0){
                                if(isset($this->AfiliacionCash->validationErrors['cedula'])){
                                    $swErrorIndividual = 1;
                                if(!empty($mensajeErrorPago)){
                                    $mensajeErrorPago = 'RIF o Cedula invalida - ' . $identificacion;
                                }else{
                                    $mensajeErrorPago.= ' - RIF o Cedula invalida - ' . $identificacion;
                                }  


                                    $error_data[] = array(
                                        'registro'=>$i+1,
                                        'cedula'=>$cedulaCliente,
                                        'identificacion'=>$identificacion,
                                        'error'=>'RIF o Cedula invalida - ' . trim($archivo[$i])
                                    );
                                }
                            }    
                            
                            if(isset($this->AfiliacionCash->validationErrors['monto'])){
                                $swErrorIndividual = 1;

                                if(!empty($mensajeErrorPago)){
                                    $mensajeErrorPago = 'Monto de transaccion invalida- ' . $this->data['AfiliacionCash']['monto'];
                                }else{
                                    $mensajeErrorPago.= ' - Monto de transaccion invalida- ' . $this->data['AfiliacionCash']['monto'];
                                }  


                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'cedula'=>$cedulaCliente,
                                    'identificacion'=>$identificacion,
                                    'error'=>'Monto de transaccion invalida- ' . trim($archivo[$i])
                                );
                            }
                            
                            
                           
                            if(isset($this->AfiliacionCash->validationErrors['concepto'])){
                                $swErrorIndividual = 1;
                                
                                if(!empty($mensajeErrorPago)){
                                    $mensajeErrorPago = 'Instruccion invalida Concepto del Pago - ' . $this->data['AfiliacionCash']['concepto'];
                                }else{
                                    $mensajeErrorPago.= ' - Instruccion invalida Concepto del Pago - ' . $this->data['AfiliacionCash']['concepto'];;
                                }  

                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'cedula'=>$cedulaCliente,
                                    'identificacion'=>$identificacion,
                                    'error'=>'Instruccion invalida Concepto del Pago - ' . trim($archivo[$i])
                                );
                            }
                            
                            
                            /*if(isset($this->AfiliacionCash->validationErrors['referencia_express'])){
                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'identificacion'=>$identificacion,
                                    'error'=>'Referencia invalida - ' . trim($archivo[$i])
                                );
                            }*/
                            
                            if(isset($this->AfiliacionCash->validationErrors['numero_cuenta'])){
                                $swErrorIndividual = 1;
                                if(!empty($mensajeErrorPago)){
                                    $mensajeErrorPago = 'Numero de Cuenta invalido - ' . $this->data['AfiliacionCash']['numero_cuenta'];
                                }else{
                                    $mensajeErrorPago.= ' - Numero de Cuenta invalido - ' . $this->data['AfiliacionCash']['numero_cuenta'];
                                }                                 
                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'cedula'=>$cedulaCliente,
                                    'identificacion'=>$identificacion,
                                    'error'=>'Numero de Cuenta invalido - ' . trim($archivo[$i])
                                );
                            }

                            if(isset($this->AfiliacionCash->validationErrors['email'])){
                                $swErrorIndividual = 1;
                                if(!empty($mensajeErrorPago)){
                                    $mensajeErrorPago = 'email invalido - ' . $this->data['AfiliacionCash']['email'];
                                }else{
                                    $mensajeErrorPago.= ' - email invalido - ' .  $this->data['AfiliacionCash']['email'];
                                }

                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'cedula'=>$cedulaCliente,
                                    'identificacion'=>$identificacion,
                                    'error'=>'email invalido - ' . trim($archivo[$i])
                                );
                            }

                            
                            // A pesar del error sumar los totales si es posible
                            
                            $cantidad_pagos++;
                            
                            /*if(!isset($this->AfiliacionCash->validationErrors['monto'])){
                                // Sumar el monto al acumulado s?si estᠢien
                                $swErrorIndividual = 1;
                                $monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);
                               // $monto_acumulado += $monto_tx;
                                
                               // if($beneficiario){
                               //     if(substr($beneficiario['numero_cuenta'],0,4)=='0171'){
                               //         $monto_acumulado_it += $monto_tx;
                               //     } else {
                               //         $monto_acumulado_e += $monto_tx;
                               //     }
                               // }
                            }*/
                            $nroTotalClientesError = $nroTotalClientesError + 1;                            
                            $monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);  
                            $mtoTotalClientesError = $mtoTotalClientesError + $monto_tx;

                            $cedulaError = $this->data['AfiliacionCash'][
                                'tipo_cedula'] . $this->data['AfiliacionCash']['cedula']; 


                            if($swArrayErrorPago == 0){
                                $swArrayErrorPago = 1;    
                            }
                            
                            $arrayErrorPago[] = array(
                                'cedula'=>$cedulaError,
                                'error_pago'=>$mensajeErrorPago,
                                'nombre'=>$this->data['AfiliacionCash']['nombre']
                            );

                            continue;
                        }

                        //validar campo 
                        if(strlen($this->data['AfiliacionCash']['numero_cuenta']) <> 20){
                            $swErrorIndividual = 1;
                            $nroTotalClientesError = $nroTotalClientesError + 1;                            
                            $monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);  
                            $mtoTotalClientesError = $mtoTotalClientesError + $monto_tx;

                            if(!empty($mensajeErrorPago)){
                                $mensajeErrorPago = 'Numero de Cuenta invalido';
                            }else{
                                $mensajeErrorPago.= ' - Numero de Cuenta invalido';
                            }

                            $error_data[] = array(
                                'registro'=>$i+1,
                                'cedula'=>$cedulaCliente,
                                'identificacion'=>$identificacion,
                                'error'=>'Numero de Cuenta invalido - ' . trim($archivo[$i])
                            );

                            if($swArrayErrorPago == 0){
                                $swArrayErrorPago = 1;    
                            }
                            
                            $arrayErrorPago[] = array(
                                'cedula'=>$cedulaError,
                                'error_pago'=>$mensajeErrorPago,
                                'nombre'=>$this->data['AfiliacionCash']['nombre']
                            );                            
                            continue;                            
                        }else{
                            //validar monto mayor a alto valor lo establecido por bcv para otros bancos
                            $mtoTope = _p('cash_alto_valor');
                            $cuentaVal = trim($this->data['AfiliacionCash']['numero_cuenta']);
                            $cuentaVal = substr($cuentaVal,0,4);

                            if($cuentaVal <> '0171'){
                                $montoVal = $this->data['AfiliacionCash']['monto'];
                                $montoVal = str_replace(",",".",$montoVal);
                                if($montoVal > $mtoTope){
                                    $swErrorIndividual = 1;
                                    $mensaje_error = _m('error.nomina.alto.valor') . " " . formatea_monto_fromWs($mtoTope) . " Bs.";
                                    $error_data[] = array(
                                    'registro'=>$i+1,
                                    'cedula'=>$cedulaCliente,
                                    'identificacion'=>$identificacion,
                                    'error'=>$mensaje_error);      
                                }
                            }
                        }


                        
                        $cantidad_pagos++;
                        $monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);
                        //$monto_acumulado += $monto_tx;
                        

                        
                        if($beneficiario['tipo']=='n' || $beneficiario['tipo']=='p'){
                            
                            // Validar el estatus del nro de cuenta
                            // Siempre y cuando sea cuenta banco activo
                            if(preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/',$beneficiario['numero_cuenta'])){
                                //$monto_acumulado_it += $monto_tx;
                                
                                $productoValidar = substr($beneficiario['numero_cuenta'],-10);
                                
                                if(substr($beneficiario['numero_cuenta'],10,1)==9 || substr($beneficiario['numero_cuenta'],10,1)==8){
                                    $swErrorIndividual = 1;
                                    
                                    $mensaje_error = _m('nobs');

                                    if(!empty($mensajeErrorPago)){
                                        $mensajeErrorPago = $mensaje_error .' - '.$beneficiario['numero_cuenta'];
                                    }else{
                                        $mensajeErrorPago.= ' - ' . $mensaje_error .' - '.$beneficiario['numero_cuenta'];
                                    }

                                   
                                    $error_data[] = array(
                                    'registro'=>$i+1,
                                    'cedula'=>$cedulaCliente,
                                    'identificacion'=>$identificacion,
                                    'error'=>$mensaje_error .' - '.$beneficiario['numero_cuenta']);      
                                }
                                
                                $valorCuenta= array(
                                    'NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 
                                    'NROPARAM' => 5,
                                    'NACIONALIDAD'=>trim($beneficiario['tipo_cedula']),
                                    'NROIDENTIFICACION'=>str_pad(trim($beneficiario['cedula']),12,'0',STR_PAD_LEFT)
                                );
                                
                                $datos = $this->Activo->validarProducto($valorCuenta);
                                
                                $error = (isset($datos['NIBPF3010Return']['error']['ERNU01'])) ? intval($datos['NIBPF3010Return']['error']['ERNU01']) : 0;
                                
                                $internas = true;
                                
                            }else{
                                //$monto_acumulado_e += $monto_tx;
                                
                                $error = 0;
                                $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] = 'A';
                                $externas = true;
                            }
                            
                            if($error!=0 || !in_array($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'],array('A','T'))){
                                if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
                                    $mensaje_error = _m('cuenta.controlada', 'con la cuenta del beneficiario');
                                } else {
                                    $mensaje_error = _m('cuenta.inactiva', 'la cuenta del beneficiario');
                                }
                                $swErrorIndividual = 1;

                                if(!empty($mensajeErrorPago)){
                                    $mensajeErrorPago = $mensaje_error .' - '.$beneficiario['numero_cuenta'];
                                }else{
                                    $mensajeErrorPago.= ' - ' . $mensaje_error .' - '.$beneficiario['numero_cuenta'];
                                }

                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'cedula'=>$cedulaCliente,
                                    'identificacion'=>$identificacion,
                                    'error'=>$mensaje_error .' - '.$beneficiario['numero_cuenta']);                              
                            } else {
                                $beneficiario['monto'] = $this->data['AfiliacionCash']['monto'];
                                $beneficiario['concepto'] = encriptar($this->data['AfiliacionCash']['concepto']);
                                $beneficiario['referencia'] = $this->data['AfiliacionCash']['referencia'];
                                

                                //                                $beneficiario['referencia'] = encriptar($this->data['AfiliacionCash']['referencia']);
                            }
                        } elseif($beneficiario['tipo']=='m' || $beneficiario['tipo']=='x') {
                            $swErrorIndividual = 1;

                            if(!empty($mensajeErrorPago)){
                                $mensajeErrorPago = 'Empresa/Cliente no esta afiliado para pagos moneda nacional - ' . trim($archivo[$i]);
                            }else{
                                $mensajeErrorPago.= 'Empresa/Cliente no esta afiliado para pagos moneda nacional - ' . trim($archivo[$i]);
                            }

                            $error_data[] = array(
                                'registro'=>$i+1,
                                'cedula'=>$cedulaCliente,
                                'identificacion'=>$identificacion,
                                'error'=>'Empresa/Cliente no esta afiliado para pagos moneda nacional - ' . trim($archivo[$i])
                            );
                        }

                         




                        // Verifica si es para proveedor con detalle
                        if($tipo=='p'){
                            $j=$i+1;
                            $monto_detalle = 0;
                            $tiene_detalle = false;
                            
                            // realiza una busqueda en las siguientes posiciones para verificar si tiene detalle
                            do{
                                $temp2 = @explode(';', trim($archivo[$j]));
                                if($temp2[0]=='D'){
                                    $tiene_detalle = true;
                                    
                                    // Verificacion de la data
                                    if(count($temp2)!=4){
                                        $error_data[] = array(
                                            'registro'=>$j+1,
                                            'cedula'=>$cedulaCliente,
                                            'identificacion'=>$identificacion,
                                            'error'=>'Estructura del registro incompleto - ' . trim($archivo[$j])
                                        );
                                    } else {
                                        $this->data['DetallePagoProveedor']['tipo_detalle'] = $temp2[1];
                                        $this->data['DetallePagoProveedor']['referencia'] = $temp2[2];
                                        $this->data['DetallePagoProveedor']['monto'] = $temp2[3];

                                        $this->DetallePagoProveedor->set($this->data);
                                        if (!$this->DetallePagoProveedor->validates(array('fieldList' => array(
                                            'monto',
                                            'tipo_detalle',
                                            'referencia',
                                        )))){

                                            if(isset($this->DetallePagoProveedor->validationErrors['monto'])){
                                                $swErrorIndividual = 1;
                                                $error_data[] = array(
                                                    'registro'=>$j+1,
                                                    'cedula'=>$cedulaCliente,
                                                    'identificacion'=>$identificacion,
                                                    'error'=>'Monto de transaccion invalida- ' . trim($archivo[$j])
                                                );
                                            }

                                            if(isset($this->DetallePagoProveedor->validationErrors['tipo_detalle'])){
                                                $swErrorIndividual = 1;
                                                $error_data[] = array(
                                                    'registro'=>$j+1,
                                                    'cedula'=>$cedulaCliente,
                                                    'identificacion'=>$identificacion,
                                                    'error'=>'Instruccion invalida tipo detalle - ' . trim($archivo[$j])
                                                );
                                            }

                                            if(isset($this->DetallePagoProveedor->validationErrors['referencia'])){
                                                $swErrorIndividual = 1;
                                                $error_data[] = array(
                                                    'registro'=>$j+1,
                                                    'cedula'=>$cedulaCliente,
                                                    'identificacion'=>$identificacion,
                                                    'error'=>'Instruccion invalida o Duplicada referencia - ' . trim($archivo[$j])
                                                );
                                            }
                                        } else {
                                            // Sumatoria de los montos del detalle
                                            $monto_detalle += formatea_monto_toWs($this->data['DetallePagoProveedor']['monto']);
                                            
                                            // formateo de la data
                                            $detalle['DetallePagoProveedor'] = $this->data['DetallePagoProveedor'];
                                            $detalle['DetallePagoProveedor']['monto'] = formatea_monto_toWs($this->data['DetallePagoProveedor']['monto']);
                                            $detalle['DetallePagoProveedor']['referencia'] = encriptar($this->data['DetallePagoProveedor']['referencia']);
                                            
                                            // Asignacion del detalle al proveedor
                                            $beneficiario['AfiliacionCash']['detalle'][] = $detalle;
                                        }
                                    }
                                    $j++;
                                }
                            }while($temp2[0]=='D');

                            //fin detalle cliente
                            
                            // Verifica monto acumulado del detalle
                            if( $tiene_detalle && formatea_monto_toWs($this->data['AfiliacionCash']['monto'])!=formatea_monto_toWs($monto_detalle)){
                                $error_data[] = array(
                                    'registro'=>$i+1,
                                    'cedula'=>'',
                                    'identificacion'=>'',
                                    'error'=>'Monto detalle no cuadra con sumatoria'
                                );
                            }
                            
                        }// FIN de verifica si es para proveedor
                        
                        if($swErrorIndividual == 0){
                            $nroTotalClientes = $nroTotalClientes + 1;
                            $monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);  
                            
                            if($beneficiario){
                                if(substr($beneficiario['numero_cuenta'],0,4)=='0171'){
                                    $monto_acumulado_it += $monto_tx;
                                } else {
                                    $monto_acumulado_e += $monto_tx;
                                }
                            }
                            $mtoTotalClientes = $mtoTotalClientes + $monto_tx;
                            $this->data['Beneficiarios'][]=$beneficiario;
                        }
                            
                        unset($this->data['AfiliacionCash']);


                    } 

                }  //fin lectura del archivo de texto




                        
                // Verifica monto acumulado
                if($header_leido){
                    if(strpos($mtoTotalClientes,',')){
                        $mtoTotalClientes = str_replace(",",".", $mtoTotalClientes);
                    }  


                    if(strpos($mtoTotalClientesError,',')){
                        $mtoTotalClientesError = str_replace(",",".", $mtoTotalClientesError);
                    }    


                    $monto_acumulado = $mtoTotalClientes + $mtoTotalClientesError;
                    if(strpos($monto_acumulado,',')){
                        $monto_acumulado = str_replace(",",".", $monto_acumulado);
                    }  


                    $monto_acumulado = completarDecimales($monto_acumulado);
                    $monto_acumulado_comparar = str_replace(".",",", $monto_acumulado);

                    //Monto acumulado pago aprobado 
                    $mtoTotalClientes = completarDecimales($mtoTotalClientes);
                    $mtoTotalClientes = str_replace(".",",", $mtoTotalClientes);
                    //Monto acumulado pago Rechazado
                    $mtoTotalClientesError = completarDecimales($mtoTotalClientesError);
                    $mtoTotalClientesError = str_replace(".",",", $mtoTotalClientesError);
                    $this->data['PagoCash']['mtoRegistroAprobado'] = $mtoTotalClientes;
                    $this->data['PagoCash']['nroRegistroAprobado'] = $nroTotalClientes;
                    $this->data['PagoCash']['mtoRegistroError'] = $mtoTotalClientesError;
                    $this->data['PagoCash']['nroRegistroError'] = $nroTotalClientesError;

                
                    if(formatea_monto_toWs($this->data['PagoCash']['monto_total']) != formatea_monto_toWs($monto_acumulado_comparar)){
                        $errorFatal = 1;
                        $error_data[] = array(
                            'registro'=>1,
                            'cedula'=>'',
                            'identificacion'=>'',
                            'error'=>'Monto total no cuadra con sumatoria'
                        );
                    }
                }

                // Ahora si validar monto total con respecto a limites
                
                $this->PagoCash->data['PagoCash']['monto_total_it'] = $monto_acumulado_it;
                $this->PagoCash->data['PagoCash']['monto_total_e'] = $monto_acumulado_e;
                
                $this->PagoCash->validate['monto_total']=array(
                    'limiteMontoTotal'=>array(
                        'rule'=>'limiteMontoTotal'
                    )
                );
                
                $this->PagoCash->validationErrors=array();
                
                if (!$this->PagoCash->validates(array('fieldList' => array(
                    'monto_total'
                )))){
                    $errorFatal = 1;
                    if($this->PagoCash->validationErrors['monto_total']=='montoLimiteAmbos'){
                        $mensaje_error = _m('monto.limite.usuario','de la transacci?n activo y otros bancos');
                    } elseif($this->PagoCash->validationErrors['monto_total']=='montoLimiteIt'){
                        $mensaje_error = _m('monto.limite.usuario','de la transacci?n activo');
                    } elseif($this->PagoCash->validationErrors['monto_total']=='montoLimiteE'){
                        $mensaje_error = _m('monto.limite.usuario','de la transacci? otros bancos');
                    } elseif($this->PagoCash->validationErrors['monto_total']=='nopermitidaE'){
                        if($tipo=='n'){
                            $mensaje_error = _m('op.nopermitida','de Pagos de N/a Otros Bancos');
                        } else {
                            $mensaje_error = _m('op.nopermitida','de Pagos de Proveedores Otros Bancos');
                        }
                    } elseif($this->PagoCash->validationErrors['monto_total']=='nopermitidaIt'){
                        if($tipo=='n'){
                            $mensaje_error = _m('op.nopermitida','de Pagos de N/a en Activo');
                        } else {
                            $mensaje_error = _m('op.nopermitida','de Pagos de Proveedores en Activo');
                        }
                    }
                    
                    if(isset($mensaje_error)){
                        $errorFatal = 1;
                        $error_data[] = array(
                            'registro'=>1,
                            'cedula'=>'',
                            'identificacion'=>'',
                            'error'=>$mensaje_error
                        );
                    }                   
                }
                
                // verifica cantidad de pagos
                if($header_leido){
                    $cantidad_pagos = $nroTotalClientes + $nroTotalClientesError; 



                    if(!$cantidad_pagos || ($cantidad_pagos != $this->data['PagoCash']['cantidad_registros']) ){
                        $errorFatal = 1;

                        $error_data[] = array(
                            'registro'=>1,
                            'cedula'=>'',
                            'identificacion'=>'',
                            'error'=>'Total transacciones no cuadra con sumatoria'
                        );
                    }
                }
                
                // Si es un pago proveedores verifica el monto total con el del ultimo lote
                if($header_leido){
                    $result = $this->TransaccionPendiente->find(
                        'first',
                        array(
                            'fields'=>array('monto'),
                            'conditions'=>array(
                                'TransaccionPendiente.tipo_rif'=>$usuario['tipo_rif'],
                                'TransaccionPendiente.rif'=>$usuario['rif'],
                                'TransaccionPendiente.tipo'=>$tipo=='p'?array('vxa','vxm','vxo'):array('nxa','nxm','nxo'),
                            ),
                            'order'=>array('TransaccionPendiente.id DESC'),
                            'limit' => 1,
                        )
                    );
                    
                    if($result && isset($this->data['PagoCash']['monto_total']) && $result['TransaccionPendiente']['monto']==formatea_monto_toWs($this->data['PagoCash']['monto_total'])){
                        $alerta = true;
                    }
                }
                
                //Si no se envia un archivo que contenga la cabecera, es un archivo invᬩdo
                if(!$header_encontrado){
                    $errorFatal = 1;
                    $error_data[] = array(
                        'registro'=>1,
                        'identificacion'=>'',
                        'error'=>'Archivo invalido'
                    );
                }
                
                // verifica si hubo errores
                if(count($error_data)){
                    
                    // Ordenar arreglo
                    usort($error_data,'ordernar_errores_por_linea');
        
                    $estado['tipo'] = $tipo;
                    $estado['errores'] = $error_data;
                    $estado['archivo'] = $this->data['PagoCash']['archivo'];
                    $estado['errorFatal']=$errorFatal;

                    // tipo de pago deacuerdo a las transacciones
                    if($internas && $externas){
                        $estado['tipo_pago'] = ($tipo=='n') ? 'nxa' : 'vxa';
                        $this->data['tipo_pago'] = ($tipo=='n') ? 'nxa' : 'vxa';
                    }
                    else if($internas){
                        $estado['tipo_pago'] = ($tipo=='n') ? 'nxm' : 'vxm';
                        $this->data['tipo_pago'] = ($tipo=='n') ? 'nxm' : 'vxm';
                    }
                    else{
                        $estado['tipo_pago'] = ($tipo=='n') ? 'nxo' : 'vxo';
                        $this->data['tipo_pago'] = ($tipo=='n') ? 'nxo' : 'vxo';   
                    }
                    


                    $this->Session->write('cash.pagos.error', $estado);
                    $this->Session->write('cash.pagos.errorFatal', $errorFatal);

                    $this->Session->write('cash.pagos.arrayErrorPago', $arrayErrorPago);
                    $this->Session->write('cash.pagos.swArrayErrorPago', $swArrayErrorPago);


                    

                    $this->Session->write('cash.pagos.data', $this->data);
                    $this->redirect('pago_masivo_express_error');
                } else {
                    //Formateo de monto para la presentaci?
                    if(isset($this->data['PagoCash']['monto_total'])) $this->data['PagoCash']['monto_total'] = formatea_monto_fromWs( formatea_monto_toWs($this->data['PagoCash']['monto_total']));
                    
                    // tipo de pago deacuerdo a las transacciones
                    if($internas && $externas){
                        $this->data['tipo_pago'] = ($tipo=='n') ? 'nxa' : 'vxa';
                    }
                    else if($internas){
                        $this->data['tipo_pago'] = ($tipo=='n') ? 'nxm' : 'vxm';
                    }
                    else{
                        $this->data['tipo_pago'] = ($tipo=='n') ? 'nxo' : 'vxo';
                    }
                    
                    $this->data['tipo'] = $tipo;
                    $this->Session->write('cash.pagos.data', $this->data);
                    
                    if($alerta){
                        $this->redirect('pago_masivo_express_alerta');
                    }
                    else{
                        $this->redirect('pago_masivo_express_confirmar');
                    }
                }
            }

            //Se coloca por defecto para procesar las nominas express
            //con dos aprobaciones

            $estado['tipo_pago'] = 'vxo';

        }


        $this->Session->delete('cash.pagos.data');
        $this->Session->delete('cash.pagos');
        
        $cuentas = $this->Activo->buscarCuentasSaldos();
        
        $this->set(compact('tipo','cuentas'));
        
        
        
        $this->layout = 'interna';
        
        switch($tipo){
            case 'n':
                $this->set('seccion_actual','cash.pagos.nomina.express');
                $this->set('titulo','Pagos N/a - Carga Masiva Express');
                break;
            case 'p':
                $this->set('seccion_actual','cash.pagos.proveedores.express');
                $this->set('titulo','Pagos Proveedores - Carga Masiva Express');
                break;
        }
    }

    /**
     * Acci?ara confirmarr el pago masivo 
     */
    function pago_masivo_express_confirmar(){
        if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('cash.pagos.data');
        $usuario = $this->Session->read('usuarios.datos');
        $this->set(compact('estado','usuario'));
        $this->layout = 'interna';
        switch($estado['tipo']){
            case 'n':
                $this->set('seccion_actual','cash.pagos.nomina.express');
                $this->set('titulo','Pagos Nomina - Carga Masiva Express');
                break;
            case 'p':
                $this->set('seccion_actual','cash.pagos.proveedores.express');
                $this->set('titulo','Pagos Proveedores - Carga Masiva Express');
                break;
        }
    }

        /**
     * Acci?ara el resultado del pago masivo
     */
    function pago_masivo_express_resultado(){
        if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');

        $estado = $this->Session->read('cash.pagos.data');
        $usuario = $this->Session->read('usuarios.datos');
        $descripcion_error = $this->Session->read('cash.pagos.descripcionError');
        $exito = $this->Session->read('cash.pagos.swExito');

        if($this->Session->read('cash.pagos.swPantallaResultado')==0){
            $this->Session->write('cash.pagos.swPantallaResultado',1);

        
            set_time_limit(0);

            $estado = $this->Session->read('cash.pagos.data');

            //Variable si existen errores en algunos de los pagos
            $swArrayErrorPago = $this->Session->read('cash.pagos.swArrayErrorPago');
            $arrayErrorPago = $this->Session->read('cash.pagos.arrayErrorPago');

            $usuario = $this->Session->read('usuarios.datos');
            $validacion_tdc = $this->Session->read('seguridad.validar_tdc');

            
        


            if(
                ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) 
                &&
                (
                    $validacion_tdc == null ||
                    !isset($validacion_tdc['validado']) ||
                    !$validacion_tdc['validado']
                )
            ){
                switch($estado['tipo']){
                    case 'n':
                        $titulo_modulo = 'Pagos Nomina - Carga Masiva Express';
                        $seccion_actual = 'cash.pagos.nomina.express';
                        break;
                    case 'p':
                        $titulo_modulo = 'Pagos Proveedores - Carga Masiva Express';
                        $seccion_actual = 'cash.pagos.proveedores.express';
                        break;
                }
            
                $this->Session->write(
                    'seguridad.validar_tdc',
                    array(
                        'url_aceptar'=>'/cash/pago_masivo_express_resultado',
                        'layout'=>'interna',
                        'titulo'=>$titulo_modulo,
                        'pasos'=> array(
                            'Selección Archivo',
                            'Confirmación',
                            'Tarjeta de Coordenadas',
                            'Resultado Operación'
                        ),
                        'paso_actual'=>2,
                        'seccion_actual'=>$seccion_actual
                    )
                );

                $this->redirect('/seguridad/validar_tdc');
            }
            
            // Validaci?nti fraude
            $cuentas = $this->Activo->buscarCuentas();
            
            if(
                !in_array($estado['PagoCash']['cuentaDebitar'], array_keys($cuentas))
            ){
                $this->redirect('/consultas/integral');
            }
            //  Fin validaci?nti fraude
            
            $exito = false;
                
            $descripcion_error = _m('operacion.fallida');

            $montototal=str_replace(".","",$estado['PagoCash']['mtoRegistroAprobado']); 


            $montototal=str_replace(",",".",$montototal);

            if ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
                $status_process = 'ep';
            }else{
                $status_process = 'pa';
            }

            $ppp = array(
                        'usuario_id'=>$usuario['id'],
                        'tipo'=> $estado['tipo_pago'],
                        'estatus'=> $status_process,
                        'lote'=>ltrim($estado['PagoCash']['lote'], "0"),
                        'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                        'fecha_hora_valor'=>formatea_fecha_toMSSQL($estado['PagoCash']['fecha_valor']['day'], $estado['PagoCash']['fecha_valor']['month'], $estado['PagoCash']['fecha_valor']['year']),
                        'monto'=>$montototal,
                        'aprobaciones_requeridas'=>$estado['PagoCash']['cantidad_aprobaciones'],
                        'tipo_rif'=>$usuario['tipo_rif'],
                        'rif'=>$usuario['rif'],
                        'cuenta_origen'=>encriptar($estado['PagoCash']['cuentaDebitar']),
                        'descripcion'=>encriptar($estado['PagoCash']['descripcion']),
                        'tipo_carga'=>'a',
                        'alias'=>'NEX',
                        'nom_express'=>'1'
                    );
            
            // Guarda la transaccion en la BD
            $res = $this->TransaccionPendiente->save(
                array(
                    'TransaccionPendiente'=>array(
                        'usuario_id'=>$usuario['id'],
                        'tipo'=> $estado['tipo_pago'],
                        'estatus'=> $status_process,
                        'lote'=>ltrim($estado['PagoCash']['lote'], "0"),
                        'fecha_hora_registro'=>date('Y-m-d H:i:s'),
                        'fecha_hora_valor'=>formatea_fecha_toMSSQL($estado['PagoCash']['fecha_valor']['day'], $estado['PagoCash']['fecha_valor']['month'], $estado['PagoCash']['fecha_valor']['year']),
                        'monto'=>$montototal,
                        'aprobaciones_requeridas'=>$estado['PagoCash']['cantidad_aprobaciones'],
                        'tipo_rif'=>$usuario['tipo_rif'],
                        'rif'=>$usuario['rif'],
                        'cuenta_origen'=>encriptar($estado['PagoCash']['cuentaDebitar']),
                        'descripcion'=>encriptar($estado['PagoCash']['descripcion']),
                        'tipo_carga'=>'a',
                        'alias'=>'NEX',
                        'nom_express'=>'1'
                    )
                ),
                false
            );
            

            if($res){
                
                $exito = true;

                //si existen errores en el pago
                if($swArrayErrorPago == 1){
                    $idTran = $this->TransaccionPendiente->id;


                    foreach ($arrayErrorPago as $t){
                        $res =  $this->NexpLogsError->saveAll( 
                            array('NexpLogsError'=>
                                array(
                                'cedula'=>$t['cedula'],
                                'error_pago'=>$t['error_pago'],
                                'transaccion_id'=>$idTran,
                                'nombre'=>$t['nombre']
                            )
                        ),
                            false
                        );
                    }

                }



                // Recorre los beneficiarios y los almacena en la BD
                foreach ($estado['Beneficiarios'] as $x){
     
                    $x['monto'] = formatea_monto_toWs($x['monto']);
                    $x['transaccion_id'] = $this->TransaccionPendiente->id;
                    
                    // unset($b['id']);
                    
                    $res = $this->TransaccionBeneficiario->save( 
                        array('TransaccionBeneficiario'=>
                            array(
                                'tipo_carga' => 'a',
                                'tipo'=> $x['tipo'],
                                'tipo_rif' => $x['tipo_rif'],
                                'rif' => encriptar($x['rif']),
                                'nombre' => encriptar($x['nombre']),
                                'tipo_cedula' => $x['tipo_cedula'],
                                'cedula' => encriptar($x['cedula']),
                                'numero_cuenta' => encriptar($x['numero_cuenta']),
                                'email' => encriptar($x['email']),
                                'celular' => encriptar($x['celular']),
                                'monto' => $x['monto'],
                                'referencia' => $x['referencia'],
                                'concepto' => $x['concepto'],
                                'transaccion_id' => $x['transaccion_id'],
                                'reversada' => '0',  
                            )
                        ),
                        false
                    );

                    // Si posee detalle es un pago de proveedores
                    // almacena en BD el detalle del pago
                    if(isset($b['detalle'])){
                        
                        foreach ($b['detalle'] as $d){
                            $d['DetallePagoProveedor']['beneficiario_id'] = $this->TransaccionBeneficiario->id;
                            $this->DetallePagoProveedor->save($d,false);
                            unset($this->DetallePagoProveedor->id);
                        }
                    }
                    
                    
                    unset($this->TransaccionBeneficiario->id);
                }

                // Verifica si es un usuario master unico para procesar el pago
                if($usuario['rol_juridico_id']==2 || $usuario['rol_juridico_id']==6){
                
                    $monto_interna = 0;
                    $monto_externa = 0; 

                
                    /** PARA EL MONTO LIMITE EN LA TRANSACCION **/

                    // Buscar el monto acumulado diario POR EMPRESA
                    $fecha = $estado['PagoCash']['fecha_valor']['year']. '-' . str_pad($estado['PagoCash']['fecha_valor']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($estado['PagoCash']['fecha_valor']['day'], 2, '0', STR_PAD_LEFT);
                                    
                                    // MGGY Se toma en cuenta el horario de franja de compensacion en caso de que la fehca valor sea el mismo dia
                                    $fecha_acumulado = $this->Activo->consultaFechaCompensacion();                                
                    switch ($estado['tipo_pago']){
                        case 'nxm':
                        case 'vxm':
                            $monto_interna = formatea_monto_toWs($estado['PagoCash']['monto_total']);
                            $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha);
                             break;
                        
                        case 'nxo':
                        case 'vxo':
     
                            $monto_externa = formatea_monto_toWs($estado['PagoCash']['monto_total']);
                            //Si el lote contiene operaciones de otros bancos y la fecha valor es del mismo dia utiliza la fecha segun la franja de compensacion     
                            if(date('Y-m-d')==$fecha){
                                $fecha_externa = $fecha_acumulado;
                            }else{
                                $fecha_externa = $fecha;
                            }
                            $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha_externa);
                            break;
                        
                        default:
                        
                            // recorre los beneficiarios
                            foreach ($estado['Beneficiarios'] as $x){
                                // Verifica si es una cuenta interna o externa
                                if(substr(desencriptar($x['numero_cuenta']),0,4)=='0171'){
                                    $monto_interna += formatea_monto_toWs($x['monto']);
                                }
                                else{
                                    $monto_externa += formatea_monto_toWs($x['monto']);
                                }
                            }

                            $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha);
                            //Si el lote contiene operaciones de otros bancos y la fecha valor es del mismo dia utiliza la fecha segun la franja de compensacion     
                            if(date('Y-m-d')==$fecha){
                                $fecha_externa = $fecha_acumulado;
                            }else{
                                $fecha_externa = $fecha;
                            }
                            $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha_externa);
                            break;
                    }

                    $rolLimites = ($usuario['rol_juridico_id']==6) ? "1" : "2";

                    $usuario_master = $this->UsuarioCanal->find(
                        'first',
                        array(
                            'conditions'=>array(
                                'Usuario.tipo_rif'=>$usuario['tipo_rif'],
                                'Usuario.rif'=>$usuario['rif'],
                                'Usuario.rol_juridico_id'=>$rolLimites,
                                'usuarioCanal.canal_id'=>Configure::read('canal.ib')
                            )
                        )
                    );
            
                    // Buscar los límites diarios
                    $limite_externas = null;
                    $limite_internas_terceros = null;                        
                    $limite_externas_nomina = null;
                    $limite_internas_terceros_nomina = null;                        
                    $limite_externas_proveedores = null;
                    $limite_internas_terceros_proveedores = null;
                    $limite_internas_terceros_proveedores_usd = null;
                    $limite_internas_terceros_proveedores_eur = null;
                    $limite_internas_terceros_nomina_usd = null;
                    $limite_internas_terceros_nomina_eur = null;

                    if($usuario_master){
                        // Si hay master registrado, tomar los límites configurados por él              
                        $limite_externas = $usuario_master['UsuarioCanal']['limite_externas'];
                        $limite_internas_terceros = $usuario_master['UsuarioCanal']['limite_internas_terceros'];
                        $limite_internas_terceros_usd = $usuario_master['UsuarioCanal']['limite_internas_terceros_usd'];
                        $limite_internas_terceros_eur = $usuario_master['UsuarioCanal']['limite_internas_terceros_eur'];
                        $limite_externas_nomina = $usuario_master['UsuarioCanal']['limite_externas_nomina'];
                        $limite_internas_terceros_nomina = $usuario_master['UsuarioCanal']['limite_internas_terceros_nomina'];
                        $limite_externas_proveedores = $usuario_master['UsuarioCanal']['limite_externas_proveedores'];
                        $limite_internas_terceros_proveedores = $usuario_master['UsuarioCanal']['limite_internas_terceros_proveedores'];
                        $limite_vip_master = $usuario_master['UsuarioCanal']['limite_vip'];
                        $limite_internas_terceros_nomina_usd = $usuario_master['UsuarioCanal']['limite_interna_terceros_nomina_usd'];
                        $limite_internas_terceros_nomina_eur = $usuario_master['UsuarioCanal']['limite_interna_terceros_nomina_eur'];
                        $limite_internas_terceros_proveedores_usd = $usuario_master['UsuarioCanal']['limite_interna_terceros_proveedores_usd'];
                        $limite_internas_terceros_proveedores_eur = $usuario_master['UsuarioCanal']['limite_interna_terceros_proveedores_eur'];
                    }

                                    
                                    if($estado['tipo']=='n'){
                    
                                        if(is_numeric($usuario['limite_vip'])){
                                            // Tomamos el lote vip
                                            $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);      
                                            $limite_total_interno = $limite_vip['limite_internas_terceros_nomina'];
                                            $limite_total_externo = $limite_vip['limite_externas_nomina'];                                        
                                        }else{
                                            $limite_total_interno = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : $limite_internas_terceros_nomina ? $limite_internas_terceros_nomina : _p('limite_internas_terceros_nomina');
                                            
                                            $limite_total_externo = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : $limite_externas_nomina ? $limite_externas_nomina : _p('limite_externas_nomina');
                                        }                                                                                                                                                
                                         
                                        if($limite_total_interno){
                                            $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'];
                                        }
                                        
                                        if($limite_total_externo){
                                            $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_nomina'];
                                        }
                                    }else{
                                        
                                        if(is_numeric($usuario['limite_vip'])){
                                            // Tomamos el lote vip
                                            $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);      
                                            $limite_total_interno = $limite_vip['limite_internas_terceros_proveedores'];
                                            $limite_total_externo = $limite_vip['limite_externas_proveedores'];                                        
                                        }else{
                                            $limite_total_interno = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : $limite_internas_terceros_proveedores ? $limite_internas_terceros_proveedores : _p('limite_internas_terceros_proveedores');
                                            
                                            $limite_total_externo = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : $limite_externas_proveedores ? $limite_externas_proveedores : _p('limite_externas_proveedores');
                                        } 
                                    
                                        if($limite_total_interno){
                                            $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'];
                                        }
                                        
                                        if($limite_total_externo){
                                            $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_proveedores'];
                                        }
                                    }
                                    
                    // Verificar los limites acumulados de la transaccion el limite de alto valor

                    if(
                        isset($monto_limite_interno) &&
                        isset($monto_limite_externo) &&
                        $monto_interna > $monto_limite_interno &&
                        $monto_externa > $monto_limite_externo
                    ){
                        $descripcion_error = _m('monto.excedido.ambos','de la transaccion');
                        $exito = false;
                    }
                    else if(
                        isset($monto_limite_interno) &&
                        $monto_interna > $monto_limite_interno
                    ){
                        $descripcion_error = _m('monto.excedido.activo','de la transaccion');
                        $exito = false;
                    }
                    else if(
                        isset($monto_limite_externo) &&
                        $monto_externa > $monto_limite_externo
                    ){
                        $descripcion_error = _m('monto.excedido.otros','de la transaccion');
                        $exito = false;
                    }
                     
                    else{
                        $montototal=str_replace(".","",$estado['PagoCash']['mtoRegistroAprobado']); 
                        $montototal=str_replace(",",".",$montototal);
                        $dataPago = array(
                            'lote'=> ltrim($estado['PagoCash']['lote'], "0"), 
                            'tipo'=> 'vxm', 
                            'cuentaDebitar'=> $estado['PagoCash']['cuentaDebitar'], 
                            'monto_total'=> $montototal, 
                            'fecha_valor'=> $estado['PagoCash']['fecha_valor'],
                        );

                       

                        if( !$this->Activo->ejecutarPagoCashExpress($dataPago, $estado['Beneficiarios']) ){



                            $error = $this->Activo->error;
                            $descripcion = $this->Activo->descripcion_error;

                         
                            $exito = true;

                            switch($this->Activo->error){
                                case 1616:
                                    $descripcion_error = _m('cuenta.sobregirada');
                                    break;
                                case 862:
                                    $descripcion_error =  _m('cuenta.sobregirada');
                                    break;
                                case 215:
                                    $descripcion_error =  _m('lote.repetido');
                                    break;    
                                default:
                                    $descripcion_error = _m('operacion.fallida');       
                            }
                            
                        }         else{
                            // el pago se ejecut?en
                            if(
                                $dataPago['fecha_valor']['day']==date('d') &&
                                $dataPago['fecha_valor']['month']==date('m') &&
                                $dataPago['fecha_valor']['year']==date('Y')
                            ){
                                // si el pago es hoy, revisar pago por pago para sumar si los montos aplicados en el acumulado

                                $tx = $this->TransaccionPendiente->findById($this->TransaccionPendiente->id);

                                $this->Activo->seguimientoGestion($tx);

                                foreach($tx['Beneficiarios'] as $i){
                                    if(isset($i['rechazado'])){
                                        // Restar montos rechazados del acumulado
                                        if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
                                            $monto_interna -= floatval($i['monto']);
                                        } else {
                                            $monto_externa -= floatval($i['monto']);
                                        }

                                        // Marcar en la base de datos el reverso, para no hacerlo dos veces
                                        $this->TransaccionBeneficiario->id = $i['id'];
                                        $this->TransaccionBeneficiario->saveField('reversada',1);
                                    }
                                }
                            }
                            
                            // Guarda acumulado
                                                    if($estado['tipo']=='n'){
                                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itn', formatea_monto_fromWs($monto_interna), $fecha);
                                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'en', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                                    }else{
                                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itp', formatea_monto_fromWs($monto_interna), $fecha);
                                                        $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'ep', formatea_monto_fromWs($monto_externa), $fecha_externa);
                                                    }
                            
                        }
                        
                    }
                    
                    
                    if(!$exito){
                        // Elimina la transaccion
                        $this->TransaccionPendiente->deleteAll(array('id'=>$this->TransaccionPendiente->id), false);
                        
                        $this->log('Pago masivo - '.$descripcion_error.' - ID: '.$usuario['id'], LOG_ERROR);
                    }
                }
            }
            else{
                $this->log($this->TransaccionPendiente->getDataSource()->error, LOG_ERROR);
            }
            
            // para la descarga del pdf
            if($exito){
                unset($estado['Beneficiarios']);
                $this->Session->write('cash.pagos.descarga',$estado);
                
                switch ($estado['tipo_pago']){
                    case 'nxm':
                    case 'vxm':
                        $transaccion = ($estado['tipo']=='n' ? 39 : 101107);
                        break;

                    case 'nxo':
                    case 'vxo':
                        $transaccion = ($estado['tipo']=='n' ? 100016 : 101108);
                        break;

                    default:
                        $transaccion = ($estado['tipo']=='n' ? 100018 : 100019);
                        break;
                }
                
                $descripcion = 'Carga masiva procesar lote nro.: ' . ltrim($estado['PagoCash']['lote'], "0");
                $this->Bitacora->registraBitacora($usuario['id'], $transaccion , NULL, NULL, NULL, NULL, $descripcion);
            }
            $this->Session->write('cash.pagos.descripcionError',$descripcion_error);
            $this->Session->write('cash.pagos.swExito',$exito);             
            
            $this->set(compact('estado','usuario', 'exito','descripcion_error'));
            
            $this->layout = 'interna';
            
            switch($estado['tipo']){
                case 'n':
                    $this->set('seccion_actual','cash.pagos.nomina.express');
                    $this->set('titulo','Pagos N/a - Carga Masiva Express');
                    break;
                case 'p':
                    $this->set('seccion_actual','cash.pagos.proveedores.express');
                    $this->set('titulo','Pagos Proveedores - Carga Masiva Express');
                    break;
            }
        }else{
            //fin validar swPantallaResultado para que no se repita el proceso si presionan el boton de procesar varias veces mientras se procesan nominas largas 
          
            
            $this->set(compact('estado','usuario', 'exito','descripcion_error')); 
            $this->layout = 'interna';
            
            switch($estado['tipo']){
                case 'n':
                    $this->set('seccion_actual','cash.pagos.nomina.express');
                    $this->set('titulo','Pagos N/a - Carga Masiva Express');
                    break;
                case 'p':
                    $this->set('seccion_actual','cash.pagos.proveedores.express');
                    $this->set('titulo','Pagos Proveedores - Carga Masiva Express');
                    break;
            }               

        }        
    }

    /**
     * Accionara mostrar los errores ocurridos en el proceso
     * de pago masivo 
     */
    function pago_masivo_express_error(){
        //if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('cash.pagos.data');
        
        $this->set(compact('estado'));
        
        $this->layout = 'interna';
        
        switch($estado['tipo']){
            case 'n':
                $this->set('seccion_actual','cash.pagos.nomina.express');
                $this->set('titulo','Pagos N/a - Carga Masiva Express');
                break;
            case 'p':
                $this->set('seccion_actual','cash.pagos.proveedores.express');
                $this->set('titulo','Pagos Proveedores - Carga Masiva Express');
                break;
        }
    }

    /**
     * Accionara mostrar alerta de pago duplicado en caso de que el monto
     * sea igual al monto del ultimo pago de proveedores
     */
    function pago_masivo_express_alerta(){
        if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
                
        $this->layout = 'interna';
        
        $this->set('seccion_actual','cash.pagos.proveedores.express');
        
        $this->set('titulo','Pagos Proveedores - Carga Masiva Express');
    }

    /**************************END PAGO NOM/PRO EXPRESS**************************/
	//js 06/10/2020
    /**
     * Acci?ara hacer carga masiva de pagos de nomina o proveedores Moneda Extranjera
     * @param string $tipo Indica el tipo de beneficiario. N/a: 'm',
     * Proveedores: 'x'
     */
    function pago_masivo_me($tipo = null) {

        if (empty($tipo) || !in_array($tipo, array('m', 'x')))
            $this->redirect('/consultas/integral');
	
        // Valida el permiso de la transaccion
        if (
                (!validarTransaccion(array(101072)) && $tipo == 'm') ||
                (!validarTransaccion(array(101073)) && $tipo == 'x')
        ) {
            $this->redirect('/consultas/integral');
        }

        // setlocale(LC_ALL,'es_VE.utf8'); PARA LINUX
        setlocale(LC_ALL, 'es_VE.UTF-8'); // PARA LINUX

        set_time_limit(0);

        $usuario = $this->Session->read('usuarios.datos');

        if (!empty($this->data['PagoCash'])) {

            $this->PagoCash->set($this->data);

            if ($this->PagoCash->validates(array('fieldList' => array(
                'cuentaDebitar',
                'archivo',
                )))) {
                $error_data = array();
                $referencias = array();
                $header_leido = false;
                $header_encontrado = false;
                $cantidad_pagos = 0;
                $this->data['Beneficiarios'] = array();
                $monto_acumulado = 0;
                $monto_acumulado_it = 0;
                $monto_acumulado_e = 0;
                $alerta = false;

                $internas = false;
                $externas = false;

                $archivo = @file($this->data['PagoCash']['archivo']['tmp_name']);
                $lineas = count($archivo);

                if (count($error_data) == 0)
                    for ($i = 0; $i < $lineas; $i++) {

                        // Actualiza el tiempo de la session en la base de datos
                        $this->UsuarioCanal->sessTime($usuario['id'], _p('tiempo_session'));

                        $temp = @explode(';', trim($archivo[$i]));
                        $fecha_valor = '';

                        if ($temp[0] == 'H' && !$header_leido) {
                            $header_encontrado = true;
                            if (count($temp) != 6) {
                                $error_data[] = array(
                                    'registro' => $i,
                                    'identificacion' => '',
                                    'error' => 'Archivo invalido'
                                );
                                break;
                            }

                            // Data del pago
                            $this->data['PagoCash']['lote'] = ltrim($temp[4], "0");
                            $this->data['PagoCash']['descripcion'] = $temp[3];
                            $this->data['PagoCash']['monto_total'] = $temp[2];
                            $this->data['PagoCash']['cantidad_registros'] = $temp[1];

                            // Fecha valor
                            if (strlen(trim($temp[5])) == 8) {
                                $this->data['PagoCash']['fecha_valor']['day'] = substr($temp[5], 0, 2);
                                $this->data['PagoCash']['fecha_valor']['month'] = substr($temp[5], 2, 2);
                                $this->data['PagoCash']['fecha_valor']['year'] = substr($temp[5], 4);
                            } else {
                                $error_data[] = array(
                                    'registro' => $i + 1,
                                    'identificacion' => '',
                                    'error' => 'Estructura del registro incompleto - ' . trim($archivo[$i])
                                );

                                break;
                            }

                            // Limite del usuario
                            if ($usuario['rol_juridico_id'] > 2) {
                                $transaccion_detalle_it = $this->TransaccionJuridicoDetalle->obtener(
                                        $usuario['id'], $tipo == 'm' ? 101072 : 101073
                                );

                                $transaccion_detalle_e = $this->TransaccionJuridicoDetalle->obtener(
                                        $usuario['id'], $tipo == 'm' ? 101072 : 101073
                                );
                                if ($transaccion_detalle_it) {
                                    $this->data['PagoCash']['monto_limite_usuario_it'] = $transaccion_detalle_it['limite'];
                                } else {
                                    $this->data['PagoCash']['monto_limite_usuario_it'] = '0,00'; // Denegar permiso a traves de los limites
                                }

                                if ($transaccion_detalle_e) {
                                    $this->data['PagoCash']['monto_limite_usuario_e'] = $transaccion_detalle_e['limite'];
                                } else {
                                    $this->data['PagoCash']['monto_limite_usuario_e'] = '0,00'; // Denegar permiso a traves de los limites
                                }
                                $this->data['PagoCash']['cantidad_aprobaciones'] = $transaccion_detalle_it['aprobaciones'];
                            } else {
                                if ($tipo == 'm') {
                                   
                                        $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                                       
                                    
                                } else {
                                   
                                        $this->data['PagoCash']['monto_limite_usuario_it'] = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');
                                        
                                    
                                }
                                $this->data['PagoCash']['cantidad_aprobaciones'] = 0;
                            }

                            if ($this->data['PagoCash']['monto_limite_usuario_it']) {
                                $this->data['PagoCash']['monto_limite_usuario_it'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_it']);
                            } else {
                                $this->data['PagoCash']['monto_limite_usuario_it'] = ''; // Ilimitado
                            }
                            if ($this->data['PagoCash']['monto_limite_usuario_e']) {
                                $this->data['PagoCash']['monto_limite_usuario_e'] = formatea_monto_fromWs($this->data['PagoCash']['monto_limite_usuario_e']);
                            } else {
                                $this->data['PagoCash']['monto_limite_usuario_e'] = ''; // Ilimitado
                            }


                            // Verifica los datos del lote
                            $this->PagoCash->set($this->data);

                            // no validar el total ahora, ya que necesitamos calcular los montos
                            // mismo banco y otro banco por separado

                            $this->PagoCash->validate['monto_total'] = array(
                                'numerico' => array(
                                    'rule' => 'montoEstricto'
                                ),
                                'requerido' => array(
                                    'rule' => 'notEmpty'
                                ),
                            );

                            if (!$this->PagoCash->validates(array('fieldList' => array(
                                            'descripcion',
                                            'lote',
                                            'monto_total',
                                            'cantidad_registros',
                                            'fecha_valor',
                                )))) {
                                if (isset($this->PagoCash->validationErrors['descripcion'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => '',
                                        'error' => 'Descripcion invalida- ' . trim($archivo[$i])
                                    );
                                }
                                if (isset($this->PagoCash->validationErrors['lote'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => '',
                                        'error' => 'Lote invalido- ' . trim($archivo[$i])
                                    );
                                }
                                if (isset($this->PagoCash->validationErrors['cantidad_registros'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => '',
                                        'error' => 'Cantidad de registros invalida- ' . trim($archivo[$i])
                                    );
                                }
                                if (isset($this->PagoCash->validationErrors['fecha_valor'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => '',
                                        'error' => 'Fecha invalida - ' . trim($archivo[$i])
                                    );
                                }
                                if (isset($this->PagoCash->validationErrors['monto_total'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => '',
                                        'error' => 'Monto invalido - ' . trim($archivo[$i])
                                    );
                                }

                                break;
                            }

                            // verifica el numero de lote
                            if (!$this->TransaccionPendiente->checkLote($this->data['PagoCash']['lote'])) {
                                $error_data[] = array(
                                    'registro' => $i + 1,
                                    'identificacion' => '',
                                    'error' => 'Numero de lote duplicado - ' . trim($archivo[$i])
                                );
                                break;
                            }

                            $header_leido = true;
                        } else if ($temp[0] == 'P') {
                            if (count($temp) != 6) {
                                $error_data[] = array(
                                    'registro' => $i + 1,
                                    'identificacion' => '',
                                    'error' => 'Estructura del registro incompleto - ' . trim($archivo[$i])
                                );

                                continue;
                            }

                            $this->data['AfiliacionCash']['tipo_rif'] = $usuario['tipo_rif'];
                            $this->data['AfiliacionCash']['rif'] = desencriptar($usuario['rif']);
                            $this->data['AfiliacionCash']['tipo_cedula'] = substr($temp[1], 0, 1);
                            $this->data['AfiliacionCash']['cedula'] = in_array($this->data['AfiliacionCash']['tipo_cedula'], array('J', 'G')) ? substr($temp[1], 1) : ltrim(substr($temp[1], 1), '0');
                            $this->data['AfiliacionCash']['monto'] = $temp[2];
                            $this->data['AfiliacionCash']['concepto'] = $temp[3];
                            $this->data['AfiliacionCash']['referencia'] = $temp[4];
                            $this->data['AfiliacionCash']['tipo'] = $tipo;


                            $referencias[$i] = $temp[4];

                            $identificacion = $this->data['AfiliacionCash']['tipo_cedula'] . $this->data['AfiliacionCash']['cedula'];

                            // Verifica los datos del lote
                            $this->AfiliacionCash->set($this->data);

                            $this->AfiliacionCash->validate['cedula'] = array(
                                'longitud' => array(
                                    'rule' => 'validaLongitud'
                                ),
                                'requerido' => array(
                                    'rule' => 'notEmpty'
                                ),
                                'rif' => array(
                                    'rule' => 'validaRif'
                                )
                            );

                            // Buscar en la base de datos

                            $beneficiario = $this->AfiliacionCash->find(
                                'first', array(
                                'conditions' => array(
                                    'AfiliacionCash.tipo_rif' => $usuario['tipo_rif'],
                                    'AfiliacionCash.rif' => $usuario['rif'],
                                    'AfiliacionCash.tipo_cedula' => $this->data['AfiliacionCash']['tipo_cedula'],
                                    'AfiliacionCash.cedula' => encriptar($this->data['AfiliacionCash']['cedula']),
                                   // 'AfiliacionCash.tipo' => $tipo
                                )
                                    )
                            );

                            // Cambiar la validacion del monto para que falle si el monto no tiene decimales

                            $this->AfiliacionCash->validate['monto'] = array(
                                'numerico' => array(
                                    'rule' => 'montoEstricto'
                                ),
                                'requerido' => array(
                                    'rule' => 'notEmpty'
                                )
                            );

                            if (!$this->AfiliacionCash->validates(array('fieldList' => array(
                                            'tipo_cedula',
                                            'cedula',
                                            'monto',
                                            'concepto',
                                            'referencia',
                                )))) {
                                if (isset($this->AfiliacionCash->validationErrors['tipo_cedula'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => $identificacion,
                                        'error' => 'RIF o Cedula invalida - ' . trim($archivo[$i])
                                    );
                                }

                                if (isset($this->AfiliacionCash->validationErrors['cedula'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => $identificacion,
                                        'error' => 'RIF o Cedula invalida - ' . trim($archivo[$i])
                                    );
                                }
                                if (isset($this->AfiliacionCash->validationErrors['monto'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => $identificacion,
                                        'error' => 'Monto de transaccion invalida- ' . trim($archivo[$i])
                                    );
                                }
                                if (isset($this->AfiliacionCash->validationErrors['concepto'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => $identificacion,
                                        'error' => 'Instruccion Invalida Concepto del Pago - ' . trim($archivo[$i])
                                    );
                                }

                                if (isset($this->AfiliacionCash->validationErrors['referencia'])) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => $identificacion,
                                        'error' => 'Instruccion Invalida o Referencia Duplicada - ' . trim($archivo[$i])
                                    );
                                }

                                // A pesar del error sumar los totales si es posible

                                $cantidad_pagos++;

                                if (!isset($this->AfiliacionCash->validationErrors['monto'])) {
                                    // Sumar el monto al acumulado solo si esta bien
                                    $monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);
                                    $monto_acumulado += $monto_tx;
                                    if ($beneficiario) {
                                        if (substr(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']), 0, 4) == '0171') {
                                            $monto_acumulado_it += $monto_tx;
                                        } else {
                                            $monto_acumulado_e += $monto_tx;
                                        }
                                    }
                                }

                                continue;
                            }

                            $cantidad_pagos++;
                            $monto_tx = formatea_monto_toWs($this->data['AfiliacionCash']['monto']);
                            $monto_acumulado += $monto_tx;

                    if ($beneficiario['AfiliacionCash']['tipo']=='x' || $beneficiario['AfiliacionCash']['tipo']=='m') {

                                // Validar el estatus del nro de cuenta
                                // Siempre y cuando sea cuenta banco activo
                                if (preg_match('/(?:^0171\d{16}$)|(?:^\d{10}$)/', desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']))) {
                                    $monto_acumulado_it += $monto_tx;
                                    $productoValidar = substr(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta']), -10);

                                    $valorCuenta = array(
                                        'NROCUENTA' => str_pad($productoValidar, 12, '0', STR_PAD_LEFT),
                                        'NROPARAM' => 5,
                                        'NACIONALIDAD' => trim($beneficiario['AfiliacionCash']['tipo_cedula']),
                                        'NROIDENTIFICACION' => str_pad(trim(desencriptar($beneficiario['AfiliacionCash']['cedula'])), 12, '0', STR_PAD_LEFT)
                                    );

                                    $datos = $this->Activo->validarProducto($valorCuenta);
                                    $error = (isset($datos['NIBPF3010Return']['error']['ERNU01'])) ? intval($datos['NIBPF3010Return']['error']['ERNU01']) : 0;
                                    $internas = true;
                                } else {
                                    $monto_acumulado_e += $monto_tx;
                                    $error = 0;
                                    $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] = 'A';
                                    $externas = true;
                                }
                                if ($error != 0 || !in_array($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'], array('A', 'T'))) {
                                    if ($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA'] == 'O') {
                                        $mensaje_error = _m('cuenta.controlada', 'con la cuenta del beneficiario');
                                    } else {
                                        $mensaje_error = _m('cuenta.inactiva', 'la cuenta del beneficiario');
                                    }
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => $identificacion,
                                        'error' => $mensaje_error . ' - ' . desencriptar($beneficiario['AfiliacionCash']['numero_cuenta'])
                                    );
                                } else {
                                    $beneficiario['AfiliacionCash']['monto'] = $this->data['AfiliacionCash']['monto'];
                                    $beneficiario['AfiliacionCash']['concepto'] = encriptar($this->data['AfiliacionCash']['concepto']);
                                    $beneficiario['AfiliacionCash']['referencia'] = encriptar($this->data['AfiliacionCash']['referencia']);
                                }
                            } elseif($beneficiario['AfiliacionCash']['tipo']=='n' || $beneficiario['AfiliacionCash']['tipo']=='p' ) {
                                $error_data[] = array(
                                    'registro' => $i + 1,
                                    'identificacion' => $identificacion,
                                    'error' => 'Empresa/Cliente no esta afiliado para pagos moneda extranjera - ' . trim($archivo[$i])
                                );
                            }else{
                                 $error_data[] = array(
                                    'registro' => $i + 1,
                                    'identificacion' => $identificacion,
                                    'error' => 'Empresa/Cliente no esta afiliado/no fue encontrado - ' . trim($archivo[$i])
                                );

                            }

                            // Verifica si es para proveedor
                            if ($tipo == 'x') {


                                $j = $i + 1;
                                $monto_detalle = 0;
                                $tiene_detalle = false;

                                // realiza una busqueda en las siguientes posiciones para verificar si tiene detalle
                                do {
                                    $temp2 = @explode(';', trim($archivo[$j]));
                                    if ($temp2[0] == 'D') {
                                        $tiene_detalle = true;

                                        // Verificacion de la data
                                        if (count($temp2) != 4) {
                                            $error_data[] = array(
                                                'registro' => $j + 1,
                                                'identificacion' => $identificacion,
                                                'error' => 'Estructura del registro incompleto - ' . trim($archivo[$j])
                                            );
                                        } else {
                                            $this->data['DetallePagoProveedor']['tipo_detalle'] = $temp2[1];
                                            $this->data['DetallePagoProveedor']['referencia'] = $temp2[2];
                                            $this->data['DetallePagoProveedor']['monto'] = $temp2[3];

                                            $this->DetallePagoProveedor->set($this->data);
                                            if (!$this->DetallePagoProveedor->validates(array('fieldList' => array(
                                                            'monto',
                                                            'tipo_detalle',
                                                            'referencia',
                                                )))) {

                                                if (isset($this->DetallePagoProveedor->validationErrors['monto'])) {
                                                    $error_data[] = array(
                                                        'registro' => $j + 1,
                                                        'identificacion' => $identificacion,
                                                        'error' => 'Monto de transaccion invalida- ' . trim($archivo[$j])
                                                    );
                                                }

                                                if (isset($this->DetallePagoProveedor->validationErrors['tipo_detalle'])) {
                                                    $error_data[] = array(
                                                        'registro' => $j + 1,
                                                        'identificacion' => $identificacion,
                                                        'error' => 'Instruccion Invalida tipo detalle - ' . trim($archivo[$j])
                                                    );
                                                }

                                                if (isset($this->DetallePagoProveedor->validationErrors['referencia'])) {
                                                    $error_data[] = array(
                                                        'registro' => $j + 1,
                                                        'identificacion' => $identificacion,
                                                        'error' => 'Instruccion Invalida o Duplicada referencia - ' . trim($archivo[$j])
                                                    );
                                                }
                                            } else {
                                                // Sumatoria de los montos del detalle
                                                $monto_detalle += formatea_monto_toWs($this->data['DetallePagoProveedor']['monto']);

                                                // formateo de la data
                                                $detalle['DetallePagoProveedor'] = $this->data['DetallePagoProveedor'];
                                                $detalle['DetallePagoProveedor']['monto'] = formatea_monto_toWs($this->data['DetallePagoProveedor']['monto']);
                                                $detalle['DetallePagoProveedor']['referencia'] = encriptar($this->data['DetallePagoProveedor']['referencia']);

                                                // Asignacion del detalle al proveedor
                                                $beneficiario['AfiliacionCash']['detalle'][] = $detalle;
                                            }
                                        }
                                        $j++;
                                    }
                                } while ($temp2[0] == 'D');

                                // Verifica monto acumulado del detalle
                                if ($tiene_detalle && formatea_monto_toWs($this->data['AfiliacionCash']['monto']) != formatea_monto_toWs($monto_detalle)) {
                                    $error_data[] = array(
                                        'registro' => $i + 1,
                                        'identificacion' => '',
                                        'error' => 'Monto detalle no cuadra con sumatoria'
                                    );
                                }
                            }// FIN de verifica si es para proveedor			
                            $this->data['Beneficiarios'][] = $beneficiario;
                            unset($this->data['AfiliacionCash']);
                        }
                    }

                if (count($referencias) > count(array_unique($referencias))) {
                    $error_data[] = array(
                        'registro' => 1,
                        'identificacion' => '',
                        'error' => 'Existen Referencias Duplicadas en el archivo'
                    );
                }

                // Verifica monto acumulado
                if ($header_leido) {
                    if (formatea_monto_toWs($this->data['PagoCash']['monto_total']) != formatea_monto_toWs($monto_acumulado)) {
                        $error_data[] = array(
                            'registro' => 1,
                            'identificacion' => '',
                            'error' => 'Monto total no cuadra con sumatoria'
                        );
                    }
                }

                // Ahora si validar monto total con respecto a limites

                $this->PagoCash->data['PagoCash']['monto_total_it'] = $monto_acumulado_it;
                $this->PagoCash->data['PagoCash']['monto_total_e'] = $monto_acumulado_e;

                $this->PagoCash->validate['monto_total'] = array(
                    'limiteMontoTotal' => array(
                        'rule' => 'limiteMontoTotal'
                    )
                );

                $this->PagoCash->validationErrors = array();

                if (!$this->PagoCash->validates(array('fieldList' => array(
                                'monto_total'
                    )))) {
                    if ($this->PagoCash->validationErrors['monto_total'] == 'montoLimiteAmbos') {
                        $mensaje_error = _m('monto.limite.usuario', 'de la transaccion activo y otros bancos');
                    } elseif ($this->PagoCash->validationErrors['monto_total'] == 'montoLimiteIt') {
                        $mensaje_error = _m('monto.limite.usuario', 'de la transaccion en activo');
                    } elseif ($this->PagoCash->validationErrors['monto_total'] == 'montoLimiteE') {
                        $mensaje_error = _m('monto.limite.usuario', 'de la transaccion otros bancos');
                    } elseif ($this->PagoCash->validationErrors['monto_total'] == 'nopermitidaE') {
                        if ($tipo == 'x') {
                            $mensaje_error = _m('op.nopermitida', 'de Pagos de N/a Otros Bancos Moneda Extranjera');
                        } else {
                            $mensaje_error = _m('op.nopermitida', 'de Pagos de Proveedores Otros Bancos Moneda Extranjera');
                        }
                    } elseif ($this->PagoCash->validationErrors['monto_total'] == 'nopermitidaIt') {
                        if ($tipo == 'x') {
                            $mensaje_error = _m('op.nopermitida', 'de Pagos de N/a en Activo Moneda Extranjera');
                        } else {
                            $mensaje_error = _m('op.nopermitida', 'de Pagos de Proveedores en Activo Moneda Extranjera');
                        }
                    }
                    if (isset($mensaje_error)) {
                        $error_data[] = array(
                            'registro' => 1,
                            'identificacion' => '',
                            'error' => $mensaje_error
                        );
                    }
                }

                // verifica cantidad de pagos
                if ($header_leido) {
                    if (!$cantidad_pagos || ($cantidad_pagos != $this->data['PagoCash']['cantidad_registros'])) {
                        $error_data[] = array(
                            'registro' => 1,
                            'identificacion' => '',
                            'error' => 'Total transacciones no cuadra con sumatoria'
                        );
                    }
                }

                // Si es un pago proveedores verifica el monto total con el del ultimo lote
                if ($header_leido) {
                    $result = $this->TransaccionPendiente->find(
                            'first', array(
                        'fields' => array('monto'),
                        'conditions' => array(
                            'TransaccionPendiente.tipo_rif' => $usuario['tipo_rif'],
                            'TransaccionPendiente.rif' => $usuario['rif'],
                            'TransaccionPendiente.tipo' => $tipo == 'x' ? array('vxa', 'xxm', 'vxo') : array('nxa', 'mxm', 'nxo'),
                        ),
                        'order' => array('TransaccionPendiente.id DESC'),
                        'limit' => 1,
                            )
                    );

                    if ($result && isset($this->data['PagoCash']['monto_total']) && $result['TransaccionPendiente']['monto'] == formatea_monto_toWs($this->data['PagoCash']['monto_total'])) {
                        $alerta = true;
                    }
                }

                //Si no se envia un archivo que contenga la cabecera, es un archivo invalido
                if (!$header_encontrado) {
                    $error_data[] = array(
                        'registro' => 1,
                        'identificacion' => '',
                        'error' => 'Archivo invalido'
                    );
                }

                // verifica si hubo errores
                if (count($error_data)) {
                    // Ordenar arreglo
                    usort($error_data, 'ordernar_errores_por_linea');
                    $estado['tipo'] = $tipo;
                    $estado['errores'] = $error_data;
                    $estado['archivo'] = $this->data['PagoCash']['archivo'];
                    $this->Session->write('cash.pagos.data', $estado);
                    $this->redirect('pago_masivo_error_me');
                } else {
                    //Formateo de monto para la presentacion	
                    if (isset($this->data['PagoCash']['monto_total']))
                        $this->data['PagoCash']['monto_total'] = formatea_monto_fromWs(formatea_monto_toWs($this->data['PagoCash']['monto_total']));
    	

    	            // tipo de pago deacuerdo a las transacciones
                    if ($internas && $externas) {
                        $this->data['tipo_pago'] = ($tipo == 'm') ? 'nxa' : 'vxa';
                    } else if ($internas) {
                        $this->data['tipo_pago'] = ($tipo == 'm') ? 'mxm' : 'xxm';
                    } else {

                    
                        $this->data['tipo_pago'] = ($tipo == 'm') ? 'nxo' : 'vxo';
                    }
                    $this->data['tipo'] = $tipo;
                    $this->Session->write('cash.pagos_me.nomina_masiva.data', $this->data);

                    if ($alerta) {
                        $this->redirect('pago_masivo_alerta_me');
                    } else {
                        $this->redirect('pago_masivo_confirmar_me');
                    }
                }
            }
        }

        $cuentas = $this->Activo->buscarCuentasSaldosme();

        $this->set(compact('tipo', 'cuentas'));

        $this->Session->delete('cash.pagos.data');

        $this->layout = 'interna';

        switch ($tipo) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina_masiva');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Carga Masiva');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_me.p_masiva');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Carga Masiva');
                break;
        }
    }

    //*******js**********
	
	/**
	 * Acci?ara mostrar alerta de pago duplicado en caso de que el monto
	 * sea igual al monto del ?ltimo pago de proveedores
	 */
	function pago_masivo_alerta(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
				
		$this->layout = 'interna';
		
		$this->set('seccion_actual','cash.pagos.proveedores.masiva');
		
		$this->set('titulo','Pagos Proveedores - Carga Masiva');
	}
	
	// js 15/07/2021
	/**
     * Acci? para mostrar alerta de pago duplicado en caso de que el monto
     * sea igual al monto del ultimo pago de proveedores
     */
    function pago_masivo_alerta_me() {
        
    if (!$this->Session->check('cash.pagos_me.nomina_masiva.data'))
            $this->redirect('/consultas/integral');

        $this->layout = 'interna';

        $this->set('seccion_actual', 'cash.pagos.proveedores.masiva');

        $this->set('titulo', 'Pagos Proveedores - Carga Masiva');
    }
    // js 15/072021
	
	/**
	 * Accion para confirmarr el pago masivo 
	 */
	function pago_masivo_confirmar(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.data');
		
		$usuario = $this->Session->read('usuarios.datos');
		
               // $parametro_altoValor = $this->Compensacion->buscarParametroAltoValor();
                
		$this->set(compact('estado','usuario'));
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina.masiva');
				$this->set('titulo','Pagos N/a - Carga Masiva');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores.masiva');
				$this->set('titulo','Pagos Proveedores - Carga Masiva');
				break;
		}
	}
	  //js 06/10/2020
    /**
     * Accionn para confirmarr el pago masivo 
     */
    function pago_masivo_confirmar_me() {
        if (!$this->Session->check('cash.pagos_me.nomina_masiva.data'))
            $this->redirect('/consultas/integral');
	
        $estado = $this->Session->read('cash.pagos_me.nomina_masiva.data');

        $usuario = $this->Session->read('usuarios.datos');

        $this->set(compact('estado', 'usuario'));

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina_masiva');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Carga Masiva');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos.proveedores.masiva');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Carga Masiva');
                break;
        }
    }
	/**
	 * Acci?ara el resultado del pago masivo
	 */
	function pago_masivo_resultado(){
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		set_time_limit(0);
		
		$estado = $this->Session->read('cash.pagos.data');
		
		$usuario = $this->Session->read('usuarios.datos');
		                
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');
                
		if(($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) 
			&& ($validacion_tdc == null || !isset($validacion_tdc['validado']) || !$validacion_tdc['validado']))
        {
			switch($estado['tipo']){
				case 'n':
					$titulo_modulo = 'Pagos Nomina - Carga Masiva';
					$seccion_actual = 'cash.pagos.nomina.masiva';
					break;
				case 'p':
					$titulo_modulo = 'Pagos Proveedores - Carga Masiva';
					$seccion_actual = 'cash.pagos.proveedores.masiva';
					break;
			}
		
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/cash/pago_masivo_resultado',
					'layout'=>'interna',
					'titulo'=>$titulo_modulo,
					'pasos'=> array(
						'Seleccione Archivo',
						'Confirmacion',
						'Tarjeta de Coordenadas',
						'Resultado Operacion'
					),
					'paso_actual'=>2,
					'seccion_actual'=>$seccion_actual
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}
		
		// Validaci?nti fraude
		$cuentas = $this->Activo->buscarCuentas();
		
		if(
			!in_array($estado['PagoCash']['cuentaDebitar'], array_keys($cuentas))
		){
			$this->redirect('/consultas/integral');
		}
		//  Fin validacion anti fraude
		
		$exito = false;
			
		$descripcion_error = _m('operacion.fallida');
        $montototal=str_replace(".","",$estado['PagoCash']['monto_total']); 
        $montototal=str_replace(",",".",$montototal);

        if ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
            $status_process = 'ep';
        }else{
            $status_process = 'pa';
        }
	
		// Guarda la transaccion en la BD
		$res = $this->TransaccionPendiente->save(
			array(
				'TransaccionPendiente'=>array(
					'usuario_id'=>$usuario['id'],
					'tipo'=> $estado['tipo_pago'],
					'estatus'=> $status_process,
					'lote'=>ltrim($estado['PagoCash']['lote'], "0"),
					'fecha_hora_registro'=>date('Y-m-d H:i:s'),
					'fecha_hora_valor'=>formatea_fecha_toMSSQL($estado['PagoCash']['fecha_valor']['day'], $estado['PagoCash']['fecha_valor']['month'], $estado['PagoCash']['fecha_valor']['year']),
					'monto'=>$montototal,
					'aprobaciones_requeridas'=>$estado['PagoCash']['cantidad_aprobaciones'],
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif'],
					'cuenta_origen'=>encriptar($estado['PagoCash']['cuentaDebitar']),
					'descripcion'=>encriptar($estado['PagoCash']['descripcion']),
					'tipo_carga'=>'a',
				)
			),
			false
		);
		
		if($res){
			
			$exito = true;
			
			// Recorre los beneficiarios y los almacena en la BD
			foreach ($estado['Beneficiarios'] as $x){
				$b = $x['AfiliacionCash'];
				$b['monto'] = formatea_monto_toWs($b['monto']);
				$b['transaccion_id'] = $this->TransaccionPendiente->id;
				
				unset($b['id']);

				$this->TransaccionBeneficiario->save( array('TransaccionBeneficiario'=>$b),false);
				
				// Si posee detalle es un pago de proveedores
				// almacena en BD el detalle del pago
				if(isset($b['detalle'])){
					foreach ($b['detalle'] as $d){
						$d['DetallePagoProveedor']['beneficiario_id'] = $this->TransaccionBeneficiario->id;
						$this->DetallePagoProveedor->save($d,false);
						unset($this->DetallePagoProveedor->id);
					}
				}
				
				unset($this->TransaccionBeneficiario->id);
			}

			// Verifica si es un usuario master unico para procesar el pago
			if($usuario['rol_juridico_id']==2 || $usuario['rol_juridico_id']==6){
				
				$monto_interna = 0;
				$monto_externa = 0;												
				
				/** PARA EL MONTO LIMITE EN LA TRANSACCION **/

				// Buscar el monto acumulado diario POR EMPRESA
				$fecha = $estado['PagoCash']['fecha_valor']['year']. '-' . str_pad($estado['PagoCash']['fecha_valor']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($estado['PagoCash']['fecha_valor']['day'], 2, '0', STR_PAD_LEFT);
                                
                                // MGGY Se toma en cuenta el horario de franja de compensacion en caso de que la fehca valor sea el mismo dia
                    $fecha_acumulado = $this->Activo->consultaFechaCompensacion();                                
                                
                    switch ($estado['tipo_pago']){
					case 'nxm':
					case 'vxm':
						$monto_interna = formatea_monto_toWs($estado['PagoCash']['monto_total']);
						$acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha);
                        break;
					case 'nxo':
					case 'vxo':
						$monto_externa = formatea_monto_toWs($estado['PagoCash']['monto_total']);                                                           
                        if(date('Y-m-d')==$fecha){
                            $fecha_externa = $fecha_acumulado;
                        }else{
                            $fecha_externa = $fecha;
                        }
                        $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha_externa);
						break;
					default:
						// recorre los beneficiarios
						foreach ($estado['Beneficiarios'] as $x){
							// Verifica si es una cuenta interna o externa
							if(substr(desencriptar($x['AfiliacionCash']['numero_cuenta']),0,4)=='0171'){
								$monto_interna += formatea_monto_toWs($x['AfiliacionCash']['monto']);
							}
							else{
								$monto_externa += formatea_monto_toWs($x['AfiliacionCash']['monto']);
							}
						}
                        $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha);    
                        if(date('Y-m-d')==$fecha){
                            $fecha_externa = $fecha_acumulado;
                        }else{
                            $fecha_externa = $fecha;
                        }
                        $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'],$usuario['rif'], $fecha_externa);
						break;
				}


                $rolLimites = ($usuario['rol_juridico_id']==6) ? "1" : "2";

                    $usuario_master = $this->UsuarioCanal->find(
                        'first',
                        array(
                            'conditions'=>array(
                                'Usuario.tipo_rif'=>$usuario['tipo_rif'],
                                'Usuario.rif'=>$usuario['rif'],
                                'Usuario.rol_juridico_id'=>$rolLimites,
                                'usuarioCanal.canal_id'=>Configure::read('canal.ib')
                            )
                        )
                    );
            
                    // Buscar los límites diarios
                    $limite_externas = null;
                    $limite_internas_terceros = null;                        
                    $limite_externas_nomina = null;
                    $limite_internas_terceros_nomina = null;                        
                    $limite_externas_proveedores = null;
                    $limite_internas_terceros_proveedores = null;
                    $limite_internas_terceros_proveedores_usd = null;
                    $limite_internas_terceros_proveedores_eur = null;
                    $limite_internas_terceros_nomina_usd = null;
                    $limite_internas_terceros_nomina_eur = null;

                    if($usuario_master){
                        // Si hay master registrado, tomar los límites configurados por él              
                        $limite_externas = $usuario_master['UsuarioCanal']['limite_externas'];
                        $limite_internas_terceros = $usuario_master['UsuarioCanal']['limite_internas_terceros'];
                        $limite_internas_terceros_usd = $usuario_master['UsuarioCanal']['limite_internas_terceros_usd'];
                        $limite_internas_terceros_eur = $usuario_master['UsuarioCanal']['limite_internas_terceros_eur'];
                        $limite_externas_nomina = $usuario_master['UsuarioCanal']['limite_externas_nomina'];
                        $limite_internas_terceros_nomina = $usuario_master['UsuarioCanal']['limite_internas_terceros_nomina'];
                        $limite_externas_proveedores = $usuario_master['UsuarioCanal']['limite_externas_proveedores'];
                        $limite_internas_terceros_proveedores = $usuario_master['UsuarioCanal']['limite_internas_terceros_proveedores'];
                        $limite_vip_master = $usuario_master['UsuarioCanal']['limite_vip'];
                        $limite_internas_terceros_nomina_usd = $usuario_master['UsuarioCanal']['limite_interna_terceros_nomina_usd'];
                        $limite_internas_terceros_nomina_eur = $usuario_master['UsuarioCanal']['limite_interna_terceros_nomina_eur'];
                        $limite_internas_terceros_proveedores_usd = $usuario_master['UsuarioCanal']['limite_interna_terceros_proveedores_usd'];
                        $limite_internas_terceros_proveedores_eur = $usuario_master['UsuarioCanal']['limite_interna_terceros_proveedores_eur'];
                    }  

                // print_r($usuario);echo "<br><br><br><br>";  
                // print_r($usuario_master);echo "<br><br>"; 
                // die();                                       
                                
                if($estado['tipo']=='n'){
                    
                    if(is_numeric($usuario['limite_vip'])){
                        // Tomamos el lote vip
                        $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);		
                        $limite_total_interno = $limite_vip['limite_internas_terceros_nomina'];
                        $limite_total_externo = $limite_vip['limite_externas_nomina'];                                        
                    }else{
                        $limite_total_interno = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : $limite_internas_terceros_nomina ? $limite_internas_terceros_nomina : _p('limite_internas_terceros_nomina');
                        
                        $limite_total_externo = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : $limite_externas_nomina ? $limite_externas_nomina : _p('limite_externas_nomina');
                    }                                                                                                                                                
                     
                    if($limite_total_interno){
                        $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'];
                    }
                    
                    if($limite_total_externo){
                        $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_nomina'];
                    }
                }else{
                    
                    if(is_numeric($usuario['limite_vip'])){
                        // Tomamos el lote vip
                        $limite_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);		
                        $limite_total_interno = $limite_vip['limite_internas_terceros_proveedores'];
                        $limite_total_externo = $limite_vip['limite_externas_proveedores'];                                        
                    }else{
                        $limite_total_interno = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : $limite_internas_terceros_proveedores ? $limite_internas_terceros_proveedores : _p('limite_internas_terceros_proveedores');
                        
                        $limite_total_externo = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : $limite_externas_proveedores ? $limite_externas_proveedores : _p('limite_externas_proveedores');
                    } 

                    // echo $monto_limite_externo . "<br><br>";
                    // echo $limite_total_externo . "<br><br>";
                    // echo $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_proveedores'] . "<br><br>";
                    // die();
                                                        
                    if($limite_total_interno){
                        $monto_limite_interno = $limite_total_interno - $acumulado_internas['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'];
                    }
                    
                    if($limite_total_externo){
                        $monto_limite_externo = $limite_total_externo - $acumulado_externas['AcumuladoTransaccion']['acumulado_externas_proveedores'];
                    }
                }
                                
				// Verificar los lotes acumulados de la transaccion el limite de alto valor
				if(
					isset($monto_limite_interno) &&
					isset($monto_limite_externo) &&
					$monto_interna > $monto_limite_interno &&
					$monto_externa > $monto_limite_externo
				){
					$descripcion_error = _m('monto.excedido.ambos','de la transaccion');
					$exito = false;
				}
				else if(
					isset($monto_limite_interno) &&
					$monto_interna > $monto_limite_interno
				){
					$descripcion_error = _m('monto.excedido.activo','de la transaccion');
					$exito = false;
				}
				else if(
					isset($monto_limite_externo) &&
					$monto_externa > $monto_limite_externo
				){
					$descripcion_error = _m('monto.excedido.otros','de la transaccion');
					$exito = false;
				}
                 
				else{

                    $montototal=str_replace(".","",$estado['PagoCash']['monto_total']); 
                    $montototal=str_replace(",",".",$montototal);
					$dataPago = array(
						'lote'=> ltrim($estado['PagoCash']['lote'], "0"), 
						'tipo'=> $estado['tipo'], 
						'cuentaDebitar'=> $estado['PagoCash']['cuentaDebitar'], 
						'monto_total'=> $montototal, 
						'fecha_valor'=> $estado['PagoCash']['fecha_valor'],
					);

                   

					if( !$this->Activo->ejecutarPagoCash($dataPago, $estado['Beneficiarios']) ){
						$exito = false;

						switch($this->Activo->error){
							case 1616:
								$descripcion_error = _m('cuenta.sobregirada');
								break;
							case 862:
								$descripcion_error =  _m('cuenta.sobregirada');
								break;
                                                        case 215:
								$descripcion_error =  _m('lote.repetido');
								break;    
							default:
								$descripcion_error = _m('operacion.fallida');		
						}
					}else{
						// el pago se ejecuten
						if(
							$dataPago['fecha_valor']['day']==date('d') &&
							$dataPago['fecha_valor']['month']==date('m') &&
							$dataPago['fecha_valor']['year']==date('Y')
						){
							// si el pago es hoy, revisar pago por pago para sumar si los montos aplicados en el acumulado

							$tx = $this->TransaccionPendiente->findById($this->TransaccionPendiente->id);

							$this->Activo->seguimientoGestion($tx);

							foreach($tx['Beneficiarios'] as $i){
								if(isset($i['rechazado'])){
									// Restar montos rechazados del acumulado
									if(substr(desencriptar($i['numero_cuenta']),0,4)=='0171'){
										$monto_interna -= floatval($i['monto']);
									} else {
										$monto_externa -= floatval($i['monto']);
									}

									// Marcar en la base de datos el reverso, para no hacerlo dos veces
									$this->TransaccionBeneficiario->id = $i['id'];
									$this->TransaccionBeneficiario->saveField('reversada',1);
								}
							}
						}
						
						// Guarda acumulado
                        if($estado['tipo']=='n'){
                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itn', formatea_monto_fromWs($monto_interna), $fecha);
                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'en', formatea_monto_fromWs($monto_externa), $fecha_externa);
                        }else{
                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itp', formatea_monto_fromWs($monto_interna), $fecha);
                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'ep', formatea_monto_fromWs($monto_externa), $fecha_externa);
                        }
						
					}
					
				}
				
				
				if(!$exito){
					// Elimina la transaccion
					$this->TransaccionPendiente->deleteAll(array('id'=>$this->TransaccionPendiente->id), false);
					
					$this->log('Pago masivo - '.$descripcion_error.' - ID: '.$usuario['id'], LOG_ERROR);
				}
			}
		}else{
			$this->log($this->TransaccionPendiente->getDataSource()->error, LOG_ERROR);
		}
		
		// para la descarga del pdf
		if($exito){
			unset($estado['Beneficiarios']);
			$this->Session->write('cash.pagos.descarga',$estado);
			
			switch ($estado['tipo_pago']){
				case 'nxm':
				case 'vxm':
					$transaccion = ($estado['tipo']=='n' ? 39 : 40);
					break;

				case 'nxo':
				case 'vxo':
					$transaccion = ($estado['tipo']=='n' ? 100016 : 100017);
					break;

				default:
					$transaccion = ($estado['tipo']=='n' ? 100018 : 100019);
					break;
			}
			
			$descripcion = 'Carga masiva procesar lote nro.: ' . ltrim($estado['PagoCash']['lote'], "0");
			$this->Bitacora->registraBitacora($usuario['id'], $transaccion , NULL, NULL, NULL, NULL, $descripcion);
		}
		
		$this->set(compact('estado','usuario', 'exito','descripcion_error'));
		
		//$this->Session->delete('cash.pagos.data');
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina.masiva');
				$this->set('titulo','Pagos Nomina - Carga Masiva');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores.masiva');
				$this->set('titulo','Pagos Proveedores - Carga Masiva');
				break;
		}
	}
	//js 06/10/2020
    /**
     * Acci?ara el resultado del pago masivo Moneda Extranjera
     */
    function pago_masivo_resultado_me() {
        if (!$this->Session->check('cash.pagos_me.nomina_masiva.data'))
            $this->redirect('/consultas/integral');

        set_time_limit(0);

        $estado = $this->Session->read('cash.pagos_me.nomina_masiva.data');

        $usuario = $this->Session->read('usuarios.datos');
        
        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');


        if (
                ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) &&
                (
                $validacion_tdc == null ||
                !isset($validacion_tdc['validado']) ||
                !$validacion_tdc['validado']
                )
        ) {
            switch ($estado['tipo']) {
                case 'm':
                    $titulo_modulo = 'Pagos Nomina Moneda Extranjera - Carga Masiva';
                    $seccion_actual = 'cash.pagos.nomina.masiva';
                    break;
                case 'x':
                    $titulo_modulo = 'Pagos Proveedores Moneda Extranjera - Carga Masiva';
                    $seccion_actual = 'cash.pagos.proveedores.masiva';
                    break;
            }

            $this->Session->write(
                'seguridad.validar_tdc', array(
                'url_aceptar' => '/cash/pago_masivo_resultado_me',
                'layout' => 'interna',
                'titulo' => $titulo_modulo,
                'pasos' => array(
                    'Seleccion Archivo',
                    'Confirmacion',
                    'Tarjeta de Coordenadas',
                    'Resultado Operacion',
                ),
                'paso_actual' => 2,
                'seccion_actual' => $seccion_actual
                    )
            );

            $this->redirect('/seguridad/validar_tdc');
        }
        
        // Validación anti fraude
        $cuentas = $this->Activo->buscarCuentas();


        $cuentaMoneda = $estado['PagoCash']['cuentaDebitar'];
        
        if (
                !in_array($estado['PagoCash']['cuentaDebitar'], array_keys($cuentas))
        ) {
            $this->redirect('/consultas/integral');
        }
        //  Fin validacion anti fraude

        $exito = false;

        $descripcion_error = _m('operacion.fallida');

        if ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {
            $status_process = 'ep';
        }else{
            $status_process = 'pa';
        }

        // Guarda la transaccion en la BD
        $res = $this->TransaccionPendiente->save(
            array(
                'TransaccionPendiente' => array(
                'usuario_id' => $usuario['id'],
                'tipo' => $estado['tipo_pago'],
                'estatus' => $status_process,
                'lote' => ltrim($estado['PagoCash']['lote'], "0" . 2),
                'fecha_hora_registro' => date('Y-m-d H:i:s'),
                'fecha_hora_valor' => formatea_fecha_toMSSQL($estado['PagoCash']['fecha_valor']['day'], $estado['PagoCash']['fecha_valor']['month'], $estado['PagoCash']['fecha_valor']['year']),
                'monto' =>formatea_monto_toWs($estado['PagoCash']['monto_total']),
                'aprobaciones_requeridas' => $estado['PagoCash']['cantidad_aprobaciones'],
                'tipo_rif' => $usuario['tipo_rif'],
                'rif' => $usuario['rif'],
                'cuenta_origen' => encriptar($estado['PagoCash']['cuentaDebitar']),
                'descripcion' => encriptar($estado['PagoCash']['descripcion']),
                'tipo_carga' => 'a',
            )
                ), false
        );

        if ($res) {

            $exito = true;

            // Recorre los beneficiarios y los almacena en la BD
            foreach ($estado['Beneficiarios'] as $x) {
                $b = $x['AfiliacionCash'];
                $b['monto'] = formatea_monto_toWs($b['monto']);
                $b['transaccion_id'] = $this->TransaccionPendiente->id;

                unset($b['id']);

                $this->TransaccionBeneficiario->save(array('TransaccionBeneficiario' => $b), false);

                // Si posee detalle es un pago de proveedores
                // almacena en BD el detalle del pago
                if (isset($b['detalle'])) {
                    foreach ($b['detalle'] as $d) {
                        $d['DetallePagoProveedor']['beneficiario_id'] = $this->TransaccionBeneficiario->id;
                        $this->DetallePagoProveedor->save($d, false);
                        unset($this->DetallePagoProveedor->id);
                    }
                }

                unset($this->TransaccionBeneficiario->id);
            }

            // Verifica si es un usuario master unico para procesar el pago
            if ($usuario['rol_juridico_id'] == 2 || $usuario['rol_juridico_id'] == 6) {

                $monto_interna = 0;
                $monto_externa = 0;

                /** PARA EL MONTO LIMITE EN LA TRANSACCION **/
                // Buscar el monto acumulado diario POR EMPRESA
                $fecha = $estado['PagoCash']['fecha_valor']['year'] . '-' . str_pad($estado['PagoCash']['fecha_valor']['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($estado['PagoCash']['fecha_valor']['day'], 2, '0', STR_PAD_LEFT);

                // MGGY Se toma en cuenta el horario de franja de compensacion en caso de que la fehca valor sea el mismo dia
                $fecha_acumulado = $this->Activo->consultaFechaCompensacion();

                switch ($estado['tipo_pago']) {
                    case 'mxm':
                    case 'xxm':
                        $monto_interna = formatea_monto_toWs($estado['PagoCash']['monto_total']);
                        $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'], $usuario['rif'], $fecha);
                        break;

                    case 'nxo':
                    case 'vxo':
                        $monto_externa = formatea_monto_toWs($estado['PagoCash']['monto_total']);

                        //Si el lote contiene operaciones de otros bancos y la fecha valor es del mismo dia utiliza la fecha segun la franja de compensacion     
                        if (date('Y-m-d') == $fecha) {
                            $fecha_externa = $fecha_acumulado;
                        } else {
                            $fecha_externa = $fecha;
                        }
                        $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'], $usuario['rif'], $fecha_externa);
                        break;

                    default:
                        // recorre los beneficiarios
                        foreach ($estado['Beneficiarios'] as $x) {
                            // Verifica si es una cuenta interna o externa
                            if (substr(desencriptar($x['AfiliacionCash']['numero_cuenta']), 0, 4) == '0171') {
                                $monto_interna += formatea_monto_toWs($x['AfiliacionCash']['monto']);
                            } else {
                                $monto_externa += formatea_monto_toWs($x['AfiliacionCash']['monto']);
                            }
                        }
                        $acumulado_internas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'], $usuario['rif'], $fecha);
                        //Si el lote contiene operaciones de otros bancos y la fecha valor es del mismo dia utiliza la fecha segun la franja de compensacion     
                        if (date('Y-m-d') == $fecha) {
                            $fecha_externa = $fecha_acumulado;
                        } else {
                            $fecha_externa = $fecha;
                        }
                        $acumulado_externas = $this->AcumuladoTransaccion->buscaRIF($usuario['tipo_rif'], $usuario['rif'], $fecha_externa);
                        break;
                }
                    if ($estado['tipo'] == 'm') {

                        $limite_total_interno_usd = $usuario['limite_internas_terceros_nomina_usd'] ? $usuario['limite_internas_terceros_nomina_usd'] : _p('limite_interna_terceros_nomina_usd');

                        $limite_total_interno_eur = $usuario['limite_internas_terceros_nomina_eur'] ? $usuario['limite_internas_terceros_nomina_eur'] : _p('limite_interna_terceros_nomina_eur');   


                       

                    if ($limite_total_interno_usd) { 



                     $monto_limite_interno_usd = $limite_total_interno_usd - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_nomina_usd'];

           
                    }

                    if ($limite_total_interno_eur) {
                    $monto_limite_interno_eur = $limite_total_interno_eur - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_nomina_eur'];
                    }

                } else {

                  
                        $limite_total_interno_usd = $usuario['limite_internas_terceros_proveedores_usd'] ? $usuario['limite_internas_terceros_proveedores_usd'] : _p('limite_interna_terceros_proveedores_usd');

                        $limite_total_interno_eur = $usuario['limite_internas_terceros_proveedores_eur'] ? $usuario['limite_internas_terceros_proveedores_eur'] : _p('limite_interna_terceros_proveedores_eur');

                    
                    if ($limite_total_interno_usd) {
                        $monto_limite_interno_usd = $limite_total_interno_usd - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_proveedores_usd'];


                    }


                    if ($limite_total_interno_eur) {
                        $monto_limite_interno_eur = $limite_total_interno_eur - $acumulado_internas['AcumuladoTransaccion']['acumulado_it_proveedores_eur'];


                    }
                }

                /** PARA EL MONTO LIMITE EN LA TRANSACCION * */
                // Verificar los limites acumulados de la transaccion 
                
                 //validamos el tipo de moneda 
                $productoDvalidar = substr($cuentaMoneda, -10);
                $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar, 12, '0', STR_PAD_LEFT), 'NROPARAM' => 2);
                $datosD = $this->Activo->validarProducto($datosDproducto);
                $errorD = intval($datosD['NIBPF3010Return']['error']['ERNU01']);

                if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD') {


                    if (
                        $monto_interna > $limite_total_interno_usd  
                    ) {

                        $descripcion_error = _m('monto.limite.config', 'de la transaccion');
                        $exito=false;

                   }elseif (
                        $monto_interna > $monto_limite_interno_usd  
                    ) {
 
                        $descripcion_error = _m('monto.excedido.activo', 'de la transaccion');
                        $exito=false;
                    
                    }     
                }elseif ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {
                   
                    if (
                        $monto_interna > $limite_total_interno_eur  
                    ) {

                        $descripcion_error = _m('monto.limite.config', 'de la transaccion');
                        $exito=false;

                   }elseif($monto_interna > $monto_limite_interno_eur){

                    

                        $descripcion_error = _m('monto.excedido.activo', 'de la transaccion');
                        $exito=false;
                    } 

                }


                if($exito){
                    
                    if($estado['tipo']=='m'){
                            $tipo='n';

                    }
                    if($estado['tipo']=='x'){
                            $tipo='p';

                    }

                $total=str_replace(',', '.', $estado['PagoCash']['monto_total']);
                   
                    $dataPago = array(
                        'lote' => ltrim($estado['PagoCash']['lote'], "0"),
                        'tipo' =>  $tipo,
                        'cuentaDebitar' => $estado['PagoCash']['cuentaDebitar'],
                        'monto_total' => $total,
                        'fecha_valor' => $estado['PagoCash']['fecha_valor'],
                    );

                    if (!$this->Activo->ejecutarPagoCash($dataPago, $estado['Beneficiarios'])) {
                        $exito = false;

                        switch ($this->Activo->error) {
                            case 1616:
                                $descripcion_error = _m('cuenta.sobregirada');
                                break;
                            case 862:
                                $descripcion_error = _m('cuenta.sobregirada');
                                break;
                            case 215:
                                $descripcion_error = _m('lote.repetido');
                                break;
                            default:
                                $descripcion_error = _m('operacion.fallida');
                        }
                    } else {
                        // el pago se ejecut bien
                        if (
                                $dataPago['fecha_valor']['day'] == date('d') &&
                                $dataPago['fecha_valor']['month'] == date('m') &&
                                $dataPago['fecha_valor']['year'] == date('Y')
                        ) {
                            // si el pago es hoy, revisar pago por pago para sumar solo los montos aplicados en el acumulado

                            $tx = $this->TransaccionPendiente->findById($this->TransaccionPendiente->id);

                            $this->Activo->seguimientoGestion($tx);

                            foreach ($tx['Beneficiarios'] as $i) {
                                if (isset($i['rechazado'])) {
                                    // Restar montos rechazados del acumulado
                                    if (substr(desencriptar($i['numero_cuenta']), 0, 4) == '0171') {
                                        $monto_interna -= floatval($i['monto']);
                                    } else {
                                        $monto_externa -= floatval($i['monto']);
                                    }

                                    // Marcar en la base de datos el reverso, para no hacerlo dos veces
                                    $this->TransaccionBeneficiario->id = $i['id'];
                                    $this->TransaccionBeneficiario->saveField('reversada', 1);
                                }
                            }
                        }

                        // Guarda acumulado
                        //validamos el tipo de moneda 
                        $productoDvalidar = substr($cuentaMoneda, -10);
                        $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar, 12, '0', STR_PAD_LEFT), 'NROPARAM' => 2);
                        $datosD = $this->Activo->validarProducto($datosDproducto);
                         $errorD = intval($datosD['NIBPF3010Return']['error']['ERNU01']);

                        if ($estado['tipo'] == 'm') {

                            if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD') {     
                        
                            $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itnusd', formatea_monto_fromWs($monto_interna), $fecha); 

                            }elseif ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {
                                
                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itneur', formatea_monto_fromWs($monto_interna), $fecha);
                            }
                            
                        } else {

                            if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD') {
                               
                        
                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itpusd', formatea_monto_fromWs($monto_interna), $fecha);       
                            }elseif ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {

                                $this->AcumuladoTransaccion->guardaAcumulado($usuario['id'], 'itpeur', formatea_monto_fromWs($monto_interna), $fecha);
                            }
                        }
                    }
                }


                if (!$exito) {
                    // Elimina la transaccion
                    $this->TransaccionPendiente->deleteAll(array('id' => $this->TransaccionPendiente->id), false);

                    $this->log('Pago masivo - ' . $descripcion_error . ' - ID: ' . $usuario['id'], LOG_ERROR);
                }
            }
        } else {
            $this->log($this->TransaccionPendiente->getDataSource()->error, LOG_ERROR);
        }

        // para la descarga del pdf
        if ($exito) {
            unset($estado['Beneficiarios']);
            $this->Session->write('cash.pagos.descarga', $estado);

            switch ($estado['tipo_pago']) {
                case 'nxm':
                case 'vxm':
                    $transaccion = ($estado['tipo'] == 'n' ? 39 : 40);
                    break;

                case 'nxo':
                case 'vxo':
                    $transaccion = ($estado['tipo'] == 'n' ? 100016 : 100017);
                    break;

                default:
                    $transaccion = ($estado['tipo'] == 'n' ? 100018 : 100019);
                    break;
            }

            $descripcion = 'Carga masiva procesar lote nro.: ' . ltrim($estado['PagoCash']['lote'], "0");
            $this->Bitacora->registraBitacora($usuario['id'], $transaccion, NULL, NULL, NULL, NULL, $descripcion);
        }

        $this->set(compact('estado', 'usuario', 'exito', 'descripcion_error'));

        //$this->Session->delete('cash.pagos.data');

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina.masiva');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Carga Masiva');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_me.p_masiva');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Carga Masiva');
                break;
        }
    }


    // 06/10/2020

	/**
	 * Acci?ara mostrar los errores ocurridos en el proceso
	 * de pago masivo 
	 */
	function pago_masivo_error(){
		//if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.data');
		
		$this->set(compact('estado'));
		
		$this->layout = 'interna';
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.pagos.nomina.masiva');
				$this->set('titulo','Pagos N/a - Carga Masiva');
				break;
			case 'p':
				$this->set('seccion_actual','cash.pagos.proveedores.masiva');
				$this->set('titulo','Pagos Proveedores - Carga Masiva');
				break;
		}
	}
	 // 06/10/2020
    /**
     * Accion para mostrar los errores ocurridos en el proceso
     * de pago masivo 
     */
    function pago_masivo_error_me() {
        //if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
	
        $estado = $this->Session->read('cash.pagos.data');

        $this->set(compact('estado'));

        $this->layout = 'interna';

        switch ($estado['tipo']) {
            case 'm':
                $this->set('seccion_actual', 'cash.pagos_me.nomina.masiva');
                $this->set('titulo', 'Pagos Nomina Moneda Extranjera - Carga Masiva');
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.pagos_me.proveedores.masiva');
                $this->set('titulo', 'Pagos Proveedores Moneda Extranjera - Carga Masiva');
                break;
        }
    }

    // 06/10/2020
	
	/**
	 * Accion para descargar en PDF el comprobante del pago masivo 
	 */
	function descarga_comprobante_masivo(){
		if(!$this->Session->check('cash.pagos.descarga')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.descarga');
		$usuario = $this->Session->read('usuarios.datos');
		
		$this->set(compact('estado','usuario'));
		
		$this->layout='nada';
	}


/**
     * Accion para descargar en PDF el comprobante del pago masivo express
     */
    function descarga_comprobante_express(){
        if(!$this->Session->check('cash.pagos.descarga')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('cash.pagos.descarga');

        $usuario = $this->Session->read('usuarios.datos');
        
        $this->set(compact('estado','usuario'));
        
        $this->layout='nada';
    }


/**
     * Accion para descargar en PDF el comprobante del pago masivo errores express
     */

    function descarga_comprobante_errores_express(){
        if(!$this->Session->check('error_express.id_transaccion')) $this->redirect('/consultas/integral');

        
        $transaccion_id = $this->Session->read('error_express.id_transaccion');

        $dataRechazo  = $this->NexpLogsError->find(
                            'all', array(
                                'conditions' => array(
                                    'transaccion_id' => $transaccion_id
                                )
                            )
                        );
        $dataRechazoAux = $dataRechazo;       
        $usuario = $this->Session->read('usuarios.datos');
        $this->set(compact('dataRechazoAux','usuario'));
        $this->layout='nada';
    }    


	
	/**
	 * Acci?ara descargar en PDF los errores ocurridos en el pago masivo
	 */
	function descarga_error_masivo(){
		
		if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.data');
		
		$usuario = $this->Session->read('usuarios.datos');
		
		$this->set(compact('estado','usuario'));
		
		$this->layout = 'nada';
	}
	

    function descarga_error_masivo_express(){
        
        if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');

        $errores = $this->Session->read('cash.pagos.error'); 
        $estado = $this->Session->read('cash.pagos.data');
        
        $usuario = $this->Session->read('usuarios.datos');
        
        $this->set(compact('estado','usuario', 'errores'));
        
        $this->layout = 'nada';
    }


    function descarga_error_masivo_express_gestion(){
        
        if(!$this->Session->check('cash.pagos.data')) $this->redirect('/consultas/integral');

        $errores = $this->Session->read('cash.pagos.error'); 
        $estado = $this->Session->read('cash.pagos.data');



        
        $usuario = $this->Session->read('usuarios.datos');
        
        $this->set(compact('estado','usuario', 'errores'));
        
        $this->layout = 'nada';
    }



	/**
	 * Accion para descargar en PDF el comprobante del pago 
	 */
	function descarga_comprobante_pago(){
		if(!$this->Session->check('cash.pagos.descarga')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.pagos.descarga');
		$usuario = $this->Session->read('usuarios.datos');
		
		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('estado','usuario','bancos'));
		
		$this->layout='nada';
	}


	/**
	 * Accion para descargar en PDF el comprobante de la eliminacion	 */
	function descarga_comprobante_eliminacion(){
		if(!$this->Session->check('cash.data_usuario')) $this->redirect('manejo_usuarios/n');
		
		$this->data = $this->Session->read('cash.data_usuario');
		$usuario = $this->Session->read('usuarios.datos');
		
		$this->set('accion','ELIMINACION');
		
		// Busqueda del nombre del banco
		$bancos = $this->Activo->buscarBancos();
		$this->data['AfiliacionCash']['banco'] =  $bancos[substr($this->data['AfiliacionCash']['numero_cuenta'],0,4)];
		
		$this->set(compact('usuario'));
		
		$this->layout='nada';
		
		$this->render('descarga_comprobante_afiliacion');
	}
	
	
	/**
	 * Accion para descargar en PDF el comprobante de la eliminacion pagos N/a/Proveedores
	 */
	function descarga_comprobante_eliminacion_pagos(){
		if(!$this->Session->check('cash.pagos.descarga')) $this->redirect('/pagos/n');
		
		$pagos = $this->Session->read('cash.pagos.descarga');
		$usuario = $this->Session->read('usuarios.datos');

		$this->set('accion','ELIMINACION');
		
		$this->set(compact('usuario','pagos'));
		
		$this->layout='nada';
		
		$this->render('descarga_comprobante_eliminacion_pagos');
	}
	
 //js 06/10/2020
    /**
     * Accion que muestra el listado con los beneficiarios afiliados a los
     * procesos de pago de nomina o proveedores
     * @param string $tipo Indica si se trabaja sobre beneficiarios de tipo
     * proveedores: 'p' , nomina 'n'
     */
    function manejo_usuarios($tipo = null) {

        if (!empty($tipo)) {
            if (!in_array($tipo, array('n', 'p')))
                $this->redirect('/consultas/integral');

            if ($tipo == 'n' && !validarTransaccion(41)) {
                $this->redirect('/consultas/integral');
            } else if ($tipo == 'p' && !validarTransaccion(42)) {
                $this->redirect('/consultas/integral');
            }

            $this->Session->write(
                'cash.pagos', array(
                'tipo' => $tipo
                )
            );
        } else if ($this->Session->check('cash.pagos')) {
            $estado = $this->Session->read('cash.pagos');
            $tipo = $estado['tipo'];
        } else {
            $this->redirect('/consultas/integral');
        }

        $this->Session->delete('cash.data_usuario');

        $usuario = $this->Session->read('usuarios.datos');

        if ($tipo=='n') {
            // code...
            $tipo_afiliacion = array('m','n');
        }

        if ($tipo=='p') {
            // code...
            $tipo_afiliacion = array('x','p');

        }

        $condiciones = array(
            'tipo' => $tipo_afiliacion,
            'tipo_rif' => $usuario['tipo_rif'],
            'rif' => $usuario['rif']
        );

        

        $total_registros = $this->AfiliacionCash->find(
                'count', array('conditions' => $condiciones)
        );


        switch ($tipo) {
            case 'n':  
                $this->set('seccion_actual','cash.afiliaciones.nomina');
                $this->set('titulo', 'Afiliaciones - N/a');
                $seccion_actual = 'cash.afiliaciones.nomina';
                $titulo = 'Afiliaciones - Nomina';
                break; 
            case 'm':  
                $this->set('seccion_actual','cash.afiliaciones.nomina');
                $this->set('titulo', 'Afiliaciones - N/a');
                $seccion_actual = 'cash.afiliaciones.nomina';
                $titulo = 'Afiliaciones - Nomina';
                break;    

            case 'p':
                
                $this->set('seccion_actual', 'cash.afiliaciones.proveedores');
                $this->set('titulo', 'Afiliaciones - Proveedores');
                $seccion_actual = 'cash.afiliaciones.proveedores';
                $titulo = 'Afiliaciones - Proveedores';
                break; 

             case 'x':   
                $this->set('seccion_actual', 'cash.afiliaciones.proveedores');
                $this->set('titulo', 'Afiliaciones - Proveedores');
                $seccion_actual = 'cash.afiliaciones.proveedores';
                $titulo = 'Afiliaciones - Proveedores';
                break;

        }
        
         //Valida el factor de autenticacion TC
                $validacion_tdc = $this->Session->read('seguridad.validar_tdc');                                        
                
        if(         
            (
                            $validacion_tdc == null ||
                            !isset($validacion_tdc['validado']) ||
                            !$validacion_tdc['validado']
            )
        ){
            $this->Session->write(
                'seguridad.validar_tdc',
                array(
                    'url_aceptar'=>'/cash/manejo_usuarios',
                    'layout'=>'interna',
                    'titulo'=>$titulo,
                    'pasos'=> array(),
                    'paso_actual'=>0,
                    'seccion_actual'=>$seccion_actual
                )
            );

            $this->redirect('/seguridad/validar_tdc');
        }
                //Fin TC
         
        if (isset($this->data['accion'])) {
            switch ($this->data['accion']) {
                case 'crear_usuarios':
                    if ($total_registros < 10) {
                        $this->Session->write(
                            'cash.manejo_usuarios_modificar', array(
                            'motivo' => 'crear',
                            'tipo' => $tipo,
                            'url_volver' => 'manejo_usuarios/' . $tipo
                                )
                        );

                        $this->redirect('modificar_usuarios');
                    }
                    break;
                case 'modificar_usuarios':
                    $this->Session->write(
                            'cash.manejo_usuarios_modificar', array(
                        'motivo' => 'modificar',
                        'tipo' => $tipo,
                        'url_volver' => 'manejo_usuarios/' . $tipo
                            )
                    );

                    $afiliacion = $this->AfiliacionCash->findById($this->data['seleccion'][0]);

                    if (
                            $afiliacion &&
                            $afiliacion['AfiliacionCash']['tipo_rif'] == $usuario['tipo_rif'] &&
                            $afiliacion['AfiliacionCash']['rif'] == $usuario['rif']
                    ) {

                        $afiliacion['AfiliacionCash']['nombre'] = desencriptar($afiliacion['AfiliacionCash']['nombre']);
                        $afiliacion['AfiliacionCash']['cedula'] = desencriptar($afiliacion['AfiliacionCash']['cedula']);
                        $afiliacion['AfiliacionCash']['numero_cuenta'] = desencriptar($afiliacion['AfiliacionCash']['numero_cuenta']);
                        $afiliacion['AfiliacionCash']['email'] = desencriptar($afiliacion['AfiliacionCash']['email']);
                        $afiliacion['AfiliacionCash']['celular'] = desencriptar($afiliacion['AfiliacionCash']['celular']);

                        $this->Session->write(
                                'cash.data_usuario', $afiliacion
                        );

                        $this->redirect('modificar_usuarios');
                    }

                    break;
                case 'eliminar_usuarios':
                    $this->Session->write(
                            'cash.manejo_usuarios', array(
                        'tipo' => $tipo,
                        'url_volver' => 'manejo_usuarios/' . $tipo,
                        'seleccion' => $this->data['seleccion']
                            )
                    );

                    $this->redirect('eliminar_usuarios');
                    break;
            }
        }

        $offset = 0;

        if (
                isset($this->data['AfiliacionCash']['offset']) &&
                preg_match('/^\d+$/', $this->data['AfiliacionCash']['offset'])
        ) {
            $offset = intval($this->data['AfiliacionCash']['offset']);
        }
        $listado = $this->AfiliacionCash->find(
                'all', array(
            'conditions' => $condiciones
                )
        );

        // Ordenar arreglo

        usort($listado, 'ordernar_afiliacioncash_por_id');

        // Paginar arreglo

        $listado = array_slice($listado, $offset, Configure::read('Tamano.paginacion'));

        $bancos = $this->Activo->buscarBancos();

        $this->layout = 'interna';

        $this->set(compact('listado', 'bancos', 'tipo', 'total_registros'));
    }
	//js 06/10/2020
    /**
     * Accion que muestra el listado con los beneficiarios afiliados a los
     * procesos de pago de n/a o proveedores
     * @param string $tipo Indica si se trabaja sobre beneficiarios de tipo
     * proveedores moneda extranjera: 'x' , n?a moneda extranjera 'n'
     */
    function manejo_usuarios_me($tipo = null) {
	
        if (!empty($tipo)) {
            if (!in_array($tipo, array('m', 'x')))
                $this->redirect('/consultas/integral');

            if ($tipo == 'm' && !validarTransaccion(41)) {
                $this->redirect('/consultas/integral');
            } else if ($tipo == 'x' && !validarTransaccion(42)) {
                $this->redirect('/consultas/integral');
            }

            $this->Session->write(
                'cash.pagos', array(
                'tipo' => $tipo
                )
            );
        } else if ($this->Session->check('cash.pagos')) {
            $estado = $this->Session->read('cash.pagos');
            $tipo = $estado['tipo'];
        } else {
            $this->redirect('/consultas/integral');
        }

        $this->Session->delete('cash.data_usuario');

        $usuario = $this->Session->read('usuarios.datos');

        $condiciones = array(
            'tipo' => $tipo,
            'tipo_rif' => $usuario['tipo_rif'],
            'rif' => $usuario['rif']
        );

        $total_registros = $this->AfiliacionCash->find(
                'count', array('conditions' => $condiciones)
        );

        switch ($tipo) {
            case 'm':
                $this->set('seccion_actual', 'cash.afiliaciones.nomina');
                $this->set('titulo', 'Afiliaciones - N/a Moneda Extranjera');
                $seccion_actual = 'cash.afiliaciones.nomina';
                $titulo = 'Afiliaciones - N/a Moneda Extranjera';
                break;
            case 'x':
                $this->set('seccion_actual', 'cash.afiliaciones.proveedores');
                $this->set('titulo', 'Afiliaciones - Proveedores Moneda Extranjera');
                $seccion_actual = 'cash.afiliaciones.proveedores';
                $titulo = 'Afiliaciones - Proveedores Moneda Extranjera';
                break;
        }

            //Valida el factor de autenticacion TC
            $validacion_tdc = $this->Session->read('seguridad.validar_tdc');		                                
        
          if(
          (
          $validacion_tdc == null ||
          !isset($validacion_tdc['validado']) ||
          !$validacion_tdc['validado']
          )
          ){
          $this->Session->write(
          'seguridad.validar_tdc',
          array(
          'url_aceptar'=>'/cash/manejo_usuarios',
          'layout'=>'interna',
          'titulo'=>$titulo,
          'pasos'=> array(),
          'paso_actual'=>0,
          'seccion_actual'=>$seccion_actual
          )
          );

            $this->redirect('/seguridad/validar_tdc');
          }
          //Fin TC
        
        if (isset($this->data['accion'])) {
            switch ($this->data['accion']) {
                case 'crear_usuarios':
                    if ($total_registros < 10) {
                        $this->Session->write(
                                'cash.manejo_usuarios_modificar', array(
                            'motivo' => 'crear',
                            'tipo' => $tipo,
                            'url_volver' => 'manejo_usuarios/' . $tipo
                                )
                        );

                        $this->redirect('modificar_usuarios');
                    }
                    break;
                case 'modificar_usuarios':
                    $this->Session->write(
                            'cash.manejo_usuarios_modificar', array(
                        'motivo' => 'modificar',
                        'tipo' => $tipo,
                        'url_volver' => 'manejo_usuarios/' . $tipo
                            )
                    );

                    $afiliacion = $this->AfiliacionCash->findById($this->data['seleccion'][0]);

                    if (
                            $afiliacion &&
                            $afiliacion['AfiliacionCash']['tipo_rif'] == $usuario['tipo_rif'] &&
                            $afiliacion['AfiliacionCash']['rif'] == $usuario['rif']
                    ) {

                        $afiliacion['AfiliacionCash']['nombre'] = desencriptar($afiliacion['AfiliacionCash']['nombre']);
                        $afiliacion['AfiliacionCash']['cedula'] = desencriptar($afiliacion['AfiliacionCash']['cedula']);
                        $afiliacion['AfiliacionCash']['numero_cuenta'] = desencriptar($afiliacion['AfiliacionCash']['numero_cuenta']);
                        $afiliacion['AfiliacionCash']['email'] = desencriptar($afiliacion['AfiliacionCash']['email']);
                        $afiliacion['AfiliacionCash']['celular'] = desencriptar($afiliacion['AfiliacionCash']['celular']);

                        $this->Session->write(
                                'cash.data_usuario', $afiliacion
                        );

                        $this->redirect('modificar_usuarios');
                    }

                    break;
                case 'eliminar_usuarios':
                    $this->Session->write(
                            'cash.manejo_usuarios', array(
                        'tipo' => $tipo,
                        'url_volver' => 'manejo_usuarios/' . $tipo,
                        'seleccion' => $this->data['seleccion']
                            )
                    );

                    $this->redirect('eliminar_usuarios');
                    break;
            }
        }

        $offset = 0;

        if (
                isset($this->data['AfiliacionCash']['offset']) &&
                preg_match('/^\d+$/', $this->data['AfiliacionCash']['offset'])
        ) {
            $offset = intval($this->data['AfiliacionCash']['offset']);
        }
        $listado = $this->AfiliacionCash->find(
                'all', array(
            'conditions' => $condiciones
                )
        );

        // Ordenar arreglo

        usort($listado, 'ordernar_afiliacioncash_por_id');

        // Paginar arreglo

        $listado = array_slice($listado, $offset, Configure::read('Tamano.paginacion'));

        $bancos = $this->Activo->buscarBancos();

        $this->layout = 'interna';

        $this->set(compact('listado', 'bancos', 'tipo', 'total_registros'));
    }

    //js 06/10/2020
	
	/**
	 * Accion para eliminar manualmente beneficiarios de pagos de n/a y proveedores
	 */	
	function eliminar_usuarios(){
		if(!$this->Session->check('cash.manejo_usuarios')){
			$this->redirect('pagos/n');
		}
		
		$estado = $this->Session->read('cash.manejo_usuarios');
			
		if($estado['tipo']=='n' && !validarTransaccion(41)) {
			$this->redirect('/consultas/integral');
		} else if($estado['tipo']=='p' && !validarTransaccion(42)) {
			$this->redirect('/consultas/integral');
		}

		if(isset($this->data['PagoCash']['confirmado'])){
			$estado['confirmado']=true;
			$this->Session->write('cash.manejo_usuarios',$estado);
			$this->redirect('eliminar_usuarios_resultado');			
		}
		
		$usuario = $this->Session->read('usuarios.datos');
		
		$afiliaciones = $this->AfiliacionCash->find(
			'all',
			array(
				'conditions'=>array(
					'id'=>$estado['seleccion'],
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif']
				)
			)
		);
		
		$this->layout = 'interna';		

		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('afiliaciones','bancos','estado'));
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo','Eliminar Beneficiario N/a');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo','Eliminar Proveedores');
				break;
		}
	}

	/**
	 * Acci?ara indicar el resultado de la eliminaci?anual del
	 * beneficiario de pago de n?a o proveedor 
	 */
	function eliminar_usuarios_resultado(){
		if(!$this->Session->check('cash.manejo_usuarios')) {
			$this->redirect('manejo_usuarios');
		}
		
		$estado = $this->Session->read('cash.manejo_usuarios');
			
		if($estado['tipo']=='n' && !validarTransaccion(41)) {
			$this->redirect('/consultas/integral');
		} else if($estado['tipo']=='p' && !validarTransaccion(42)) {
			$this->redirect('/consultas/integral');
		}
		
		if(
			!isset($estado['confirmado'])
		){
			$this->redirect('manejo_usuarios');
		}
		
		$usuario = $this->Session->read('usuarios.datos');		
		
		$afiliaciones = $this->AfiliacionCash->find(
			'all',
			array(
				'conditions'=>array(
					'id'=>$estado['seleccion'],
					'tipo_rif'=>$usuario['tipo_rif'],
					'rif'=>$usuario['rif']
				)
			)
		);
		
		$data = $afiliaciones[0];
		
		$data['AfiliacionCash']['nombre'] = desencriptar($data['AfiliacionCash']['nombre']);
		$data['AfiliacionCash']['numero_cuenta'] = desencriptar($data['AfiliacionCash']['numero_cuenta']);
		$data['AfiliacionCash']['cedula'] = desencriptar($data['AfiliacionCash']['cedula']);
		$data['AfiliacionCash']['email'] = desencriptar($data['AfiliacionCash']['email']);
		$data['AfiliacionCash']['celular'] = desencriptar($data['AfiliacionCash']['celular']);
		
		$this->Session->write('cash.data_usuario',$data);
		
		$res = $this->AfiliacionCash->deleteAll(
			array(
				'id'=>$estado['seleccion'],
				'tipo_rif'=>$usuario['tipo_rif'],
				'rif'=>$usuario['rif']
			)
		);
		
		$res = true;
		if(!$res){
			$this->set('error_fatal',_m('error.fatal'));
		}
		
		$this->layout = 'interna';		

		$bancos = $this->Activo->buscarBancos();
		
		$this->set(compact('afiliaciones','bancos','estado'));		
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo','Eliminar Beneficiario N/a');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo','Eliminar Proveedores');
				break;
		}
		
		$this->Session->delete('cash.manejo_usuarios');	
	}
	
	/**
	 * Accion para crear o modificar beneficiarios de pagos de n/a o
	 * proveedores
	 * @param string $volver Indica si se estᠲegresando de la confirmacion	 */
	function modificar_usuarios($volver=null){
		if(!$this->Session->check('cash.manejo_usuarios_modificar')){
			$this->redirect('pagos/n');
		}
		
		$estado = $this->Session->read('cash.manejo_usuarios_modificar');
			
		if($estado['tipo']=='n' && !validarTransaccion(41)) {
			$this->redirect('/consultas/integral');
		} else if($estado['tipo']=='p' && !validarTransaccion(42)) {
			$this->redirect('/consultas/integral');
		}
			
		$usuario = $this->Session->read('usuarios.datos');
		
		if(isset($this->data['AfiliacionCash']['editando'])){
			// El usuario hizo POST del formulario
			
			if($this->Session->check('cash.data_usuario')){
				// Conservar id del registro a modificar
				// para verificar unicidad en validaciones del modelo
				$temp = $this->Session->read('cash.data_usuario');
				$this->data['AfiliacionCash']['id'] = $temp['AfiliacionCash']['id'];
			}
			
			// Colocar rif para validar en el modelo la unicidad
			$this->data['AfiliacionCash']['tipo_rif'] = $usuario['tipo_rif'];
			$this->data['AfiliacionCash']['rif'] = desencriptar($usuario['rif']);
			
			$this->AfiliacionCash->set($this->data);
			
			if($this->AfiliacionCash->validates(array(
				'fieldList'=>array(
					'numero_cuenta',
					'nombre',
					'tipo_cedula',
					'cedula',
					'email',
					'nroCelular',
					'codigoCelular',
					'nroCelular',
					'monto'
				)
			))){
				$this->data['AfiliacionCash']['tipo']=$estado['tipo'];
				$this->data['AfiliacionCash']['tipo_rif']=$usuario['tipo_rif'];
				$this->data['AfiliacionCash']['rif']=$usuario['rif'];
				$this->data['AfiliacionCash']['celular'] = $this->data['AfiliacionCash']['codigoCelular'].$this->data['AfiliacionCash']['nroCelular'];
				$this->data['motivo'] = $estado['motivo'];
				$this->Session->write('cash.data_usuario',$this->data);
				$this->redirect('modificar_usuarios_confirmar');
			}
		} else if(isset($volver) && $this->Session->check('cash.data_usuario')){
			// Regresando de la confirmacion			
            $this->data = $this->Session->read('cash.data_usuario');

		} else if($estado['motivo']=='modificar' && $this->Session->check('cash.data_usuario')){
			// Modificando  
			
			$this->data = $this->Session->read('cash.data_usuario');
			$this->data['AfiliacionCash']['codigoCelular'] = substr($this->data['AfiliacionCash']['celular'],0,4);
			$this->data['AfiliacionCash']['nroCelular'] = substr($this->data['AfiliacionCash']['celular'],-7);

		} else if($estado['motivo']=='crear'){
			$condiciones = array(
				'tipo'=>$estado['tipo'],
				'tipo_rif'=>$usuario['tipo_rif'],
				'rif'=>$usuario['rif']
			);		

			$total_registros = $this->AfiliacionCash->find(
				'count',
				array( 'conditions'=>$condiciones )
			);			
			
			if($total_registros >= 10){
				$this->Session->delete('cash.manejo_usuarios_modificar');
				$this->redirect('manejo_usuarios');
			}
			
			$this->data = array(
				'AfiliacionCash'=>array(
					'tipo'=>$estado['tipo']
				)
			);
		} else {
			// Usuario ingreso por url
			$this->redirect('manejo_usuarios/n');
		}
		
		if(
			isset($this->data['AfiliacionCash']['codigoCelular']) &&
			isset($this->data['AfiliacionCash']['nroCelular'])
		){
			$this->data['AfiliacionCash']['celular']=$this->data['AfiliacionCash']['codigoCelular'].$this->data['AfiliacionCash']['nroCelular'];
		}
		
		$bancos = $this->Activo->buscarBancos();

		$this->set(compact('bancos','estado'));
		$this->layout = 'interna';
		
		if($estado['motivo']=='modificar'){
			$titulo = 'Modificar';
			$titulo2 = 'A continuacion proceda a modificar los datos del beneficiario';
		} else {
			$titulo = 'Crear';
			$titulo2 = 'A continuacion proceda a crear los datos del beneficiario';
		}
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo',$titulo.' Beneficiario N/a');
				$this->set('titulo2',$titulo2);
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo',$titulo.' Proveedores');
				$this->set('titulo2',$titulo2);
				break;
		}		
	}
	
	/**
     * Acci?ara confirmar la modificaci? creaci?el beneficiario 
     */
    function modificar_usuarios_confirmar(){
        if(!$this->Session->check('cash.manejo_usuarios_modificar')) $this->redirect('/consultas/integral');
        
        $estado = $this->Session->read('cash.manejo_usuarios_modificar');
            
        if($estado['tipo']=='n' && !validarTransaccion(41)) {
            $this->redirect('/consultas/integral');
        } else if($estado['tipo']=='p' && !validarTransaccion(42)) {
            $this->redirect('/consultas/integral');
        }
        
        $bancos = $this->Activo->buscarBancos();
        $data =  $this->Session->read('cash.data_usuario');
        
        $data['AfiliacionCash']['banco'] =  $bancos[substr($data['AfiliacionCash']['numero_cuenta'],0,4)];
        
        if(isset($this->data['AfiliacionCash']['confirmado'])){
        
            $this->AfiliacionCash->id = $data['AfiliacionCash']['id'];
        
            // Verifica si ya existe 10 afiliados
            $usuario = $this->Session->read('usuarios.datos');
            $total_registros = $this->AfiliacionCash->find(
                'count',
                array( 'conditions'=>array(
                        'tipo'=>$estado['tipo'],
                        'tipo_rif'=>$usuario['tipo_rif'],
                        'rif'=>$usuario['rif']
                    )
                )
            );
            
            if($total_registros >= 10 && $estado['motivo']!='modificar'){
                $estado['confirmado']=true;
                $estado['cantidad_superada']=true;
                $this->Session->write('cash.manejo_usuarios_modificar',$estado);
                $this->redirect('modificar_usuarios_resultado');
            } else {

                //validamos el tipo de moneda para determinar el tipo de afiliacion
                $productoDvalidar = substr($data['AfiliacionCash']['numero_cuenta'], -10);
                $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar, 12, '0', STR_PAD_LEFT), 'NROPARAM' => 2);
                $datosD = $this->Activo->validarProducto($datosDproducto);
                $errorD = intval($datosD['NIBPF3010Return']['error']['ERNU01']);
                
               if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD' || $datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR' ) {

                switch($datosD['NIBPF3010Return']['NIBPF3010']['INTFIL01']) {
                    case  'CPND':
                    case  'CPJD':
                    case  'CPJE':
                    case  'CPNE':
                    case  'DCPN':
                    case  'DPJN':
                    case  'DPJD':
                                       
                           $numerror=1;
                    break;
                    
                }

                if($estado['tipo'] == 'n'){

                    
                    $data['AfiliacionCash']['tipo'] = 'm';
                }
                if($estado['tipo'] == 'p'){
                    $data['AfiliacionCash']['tipo'] = 'x';

                }
            }

            $moneda=$datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'];

        if(!isset($numerror)){
               

                //  fin validar cuenta
                $res = $this->AfiliacionCash->save(
                    array(
                        'AfiliacionCash'=>array(
                            'tipo_carga'=>'m',
                            'tipo' => $data['AfiliacionCash']['tipo'],
                            'tipo_rif' => $data['AfiliacionCash']['tipo_rif'],
                            'rif' => $data['AfiliacionCash']['rif'],
                            'nombre' => encriptar($data['AfiliacionCash']['nombre']),
                            'tipo_cedula' => $data['AfiliacionCash']['tipo_cedula'],
                            'cedula' => encriptar($data['AfiliacionCash']['cedula']),
                            'numero_cuenta' => encriptar($data['AfiliacionCash']['numero_cuenta']),
                            'email' => encriptar($data['AfiliacionCash']['email']),
                            'celular' => encriptar($data['AfiliacionCash']['celular']),
                            'moneda' =>$moneda
                        )
                    ),
                    false
                );

                if($res){

                    $usuario = $this->Session->read('usuarios.datos');
                    $transaccion = ($estado['tipo']=='n' ? 41 : 42);
                    $descripcion = 'Afiliacion';
                    $descripcion.= ($estado['tipo']=='n' ? 'nomina' : 'proveedor');
                                        
                    if($estado['tipo']=='n'){
                        // Enviar por sms la notificacion                                            
                        if( isset($usuario['celular']) ){                                    
                                $this->Sms->enviar(
                                        array(
                                                'numcel'=>desencriptar($usuario['celular']),
                                                'canal1'=>50
                                        )
                                );
                        }
                    }else{
                        // Enviar por sms la notificacion 
                        if( isset($usuario['celular']) ){                                    
                                $this->Sms->enviar(
                                        array(
                                                'numcel'=>desencriptar($usuario['celular']),
                                                'canal1'=>51
                                        )
                                );
                        }
                    }
                                        
                    $this->Bitacora->registraBitacora($usuario['id'], $transaccion , NULL, NULL, NULL, NULL, $descripcion);
                    $estado['confirmado']=true;
                    $this->Session->write('cash.manejo_usuarios_modificar',$estado);
                    $this->redirect('modificar_usuarios_resultado');        
                } else {
                    $this->set('error_fatal',_m('usuario.registrado'));
                }
            }else{$this->set('error_fatal',_m('afiliacion.nopermitida'));
        }
            }
        }
        
        $this->data = $data;
        
        $this->data['AfiliacionCash']['banco'] =  $bancos[substr($this->data['AfiliacionCash']['numero_cuenta'],0,4)];
        $this->data['AfiliacionCash']['celular'] = $data['AfiliacionCash']['codigoCelular'].$data['AfiliacionCash']['nroCelular'];
        
        $this->set(compact('bancos'));
        $this->layout = 'interna';
        
        if($estado['motivo']=='modificar'){
            $titulo = 'Modificar';
        } else {
            $titulo = 'Crear';
        }
        
        switch($estado['tipo']){
            case 'n':
                $this->set('seccion_actual','cash.afiliaciones.nomina');
                $this->set('titulo',$titulo.' Beneficiario N/a');
                break;
            case 'p':
                $this->set('seccion_actual','cash.afiliaciones.proveedores');
                $this->set('titulo',$titulo.' Proveedores');
                break;
        }   
    }
	
	/**
	 * Accion para indicar el resultado de la modificacion creacion el
	 * beneficiario 
	 */
	function modificar_usuarios_resultado(){
		if(!$this->Session->check('cash.manejo_usuarios_modificar')) $this->redirect('manejo_usuarios/n');
		
		$estado = $this->Session->read('cash.manejo_usuarios_modificar');
			
		if($estado['tipo']=='n' && !validarTransaccion(41)) {
			$this->redirect('/consultas/integral');
		} else if($estado['tipo']=='p' && !validarTransaccion(42)) {
			$this->redirect('/consultas/integral');
		}
		
		if(isset($estado['cantidad_superada'])){
			$this->set(compact('estado'));
		}
		else{
			$bancos = $this->Activo->buscarBancos();

			$data =  $this->Session->read('cash.data_usuario');
			$this->data = $data;

			$this->data['AfiliacionCash']['banco'] =  $bancos[substr($this->data['AfiliacionCash']['numero_cuenta'],0,4)];

			if(!isset($estado['confirmado'])){
				$this->redirect('pagos/n');
			}

			$this->set(compact('bancos'));
		}
		
		$this->layout = 'interna';

		
		if($estado['motivo']=='modificar'){
			$titulo = 'Modificar';
		} else {
			$titulo = 'Crear';
		}
		
		switch($estado['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina');
				$this->set('titulo',$titulo.' Beneficiario N?a');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores');
				$this->set('titulo',$titulo.' Proveedores');
				break;
		}
		
		$this->Session->delete('cash.manejo_usuarios_modificar');	
	}
	
	/**
	 * Accion para descargar en PDF el comprobante de la afiliacion	 */
	function descarga_comprobante_afiliacion(){
		if(!$this->Session->check('cash.data_usuario')) $this->redirect('manejo_usuarios/n');
		
		$this->data = $this->Session->read('cash.data_usuario');
		$usuario = $this->Session->read('usuarios.datos');
		
		if($this->data['motivo']=='modificar'){
			$this->set('accion','modificacion_limites');
		} else {
			$this->set('accion','AFILIACION');
		}
		
		// Busqueda del nombre del banco
		$bancos = $this->Activo->buscarBancos();
		$this->data['AfiliacionCash']['banco'] =  $bancos[substr($this->data['AfiliacionCash']['numero_cuenta'],0,4)];
		
		$this->set(compact('usuario'));
		
		$this->layout='nada';
		
	}
	
	/**
     * Accion para cargar masivamente beneficiarios de pago de nomina y
     * proveedores
     * @param string $tipo Indica si se estᠴrabajando sobre proveedores: 'p'
     * o n/a 'n'
     */
    function carga_masiva_beneficiarios($tipo = null) {

        if (!empty($tipo)) {
            if (!in_array($tipo, array('n', 'p')))
                $this->redirect('/consultas/integral');

            if ($tipo == 'n' && !validarTransaccion(41)) {
                $this->redirect('/consultas/integral');
            } else if ($tipo == 'p' && !validarTransaccion(42)) {
                $this->redirect('/consultas/integral');
            }

            $this->Session->write(
                'cash.pagos', array(
                    'tipo' => $tipo
                )
            );
        } else if ($this->Session->check('cash.pagos')) {
            $datos = $this->Session->read('cash.pagos');
            $tipo = $datos['tipo'];
        } else {
            $this->redirect('/consultas/integral');
        }

        switch ($tipo) {
            case 'n':
                $this->set('seccion_actual', 'cash.afiliaciones.nomina.masiva');
                $this->set('titulo', 'Afiliaciones - N/a');
                $seccion_actual = 'cash.afiliaciones.nomina.masiva';
                $titulo = 'Afiliaciones - N/a';
                break;
            case 'p':
                $this->set('seccion_actual', 'cash.afiliaciones.proveedores.masiva');
                $this->set('titulo', 'Afiliaciones - Proveedores');
                $seccion_actual = 'cash.afiliaciones.proveedores.masiva';
                $titulo = 'Afiliaciones - Proveedores';
                break;
        }
        
          //Valida el factor de autenticacion TC
          $validacion_tdc = $this->Session->read('seguridad.validar_tdc');
          
          if(
          (
            $validacion_tdc == null ||
            !isset($validacion_tdc['validado']) ||
            !$validacion_tdc['validado']
          )
          ){
            $this->Session->write(
                'seguridad.validar_tdc',
                     array(
                    'url_aceptar'=>'/cash/carga_masiva_beneficiarios',
                    'layout'=>'interna',
                    'titulo'=>$titulo,
                    'pasos'=> array(),
                    'paso_actual'=>0,
                    'seccion_actual'=>$seccion_actual
                )
            );
                $this->redirect('/seguridad/validar_tdc');
          }
          //Fin TC
         
        $data = $this->data['CargaMasivaBeneficiariosCash']['archivo']['tmp_name'];
        $nombreArchivo = $this->data['CargaMasivaBeneficiariosCash']['archivo']['name'];

        $usuario = $this->Session->read('usuarios.datos');

        if (!empty($data)) {
            $error_archivo = false;

            // Verifica la extension del archivo
            $this->AfiliacionCash->set($this->data['CargaMasivaBeneficiariosCash']);

            if ($this->AfiliacionCash->validates(array('fieldList' => array(
                            'archivo',
                )))) {
                $fp = fopen($data, "r");
                $errores = array();
                $registros = array();
                $kcuentas = array();
                $kcedulas = array();
                $linea = 0;

                while (!feof($fp)) {

                    // Actualiza el tiempo de la session en la base de datos
                    $this->UsuarioCanal->sessTime($usuario['id'], _p('tiempo_session'));

                    $linea++;
                    $contenido_linea = trim(fgets($fp));

                    if (!strlen($contenido_linea))
                        continue;

                    $data = explode(";", $contenido_linea);

                    if (count($data) != 7) {
                        $errores[] = array(
                            'registro' => $linea,
                            'identificacion' => '',
                            'nombre' => '',
                            'error' => 'Estructura del registro incompleto'
                        );

                        continue;
                    }

                    $tipoCedula = strtoupper(substr($data[0], 0, 1));
                    $cedula = trim(substr($data[0], 1));
                    $nombre = trim($data[1]);
                    $cuenta = $data[2];
                    $banco = trim($data[3]);
                    $email = $data[4];
                    $telefono = $data[5];
                    $accion = strtoupper(trim($data[6]));
                    $valido = true;

                    if (in_array($tipoCedula, array('V', 'P', 'E'))) {
                        $cedula = ltrim($cedula, 0);
                    }
                    //validamos el tipo de moneda para determinar el tipo de afiliacion
                    $productoDvalidar = substr($cuenta, -10);
                    $datosDproducto = array('NROCUENTA' => str_pad($productoDvalidar, 12, '0', STR_PAD_LEFT), 'NROPARAM' => 2);
                    $datosD = $this->Activo->validarProducto($datosDproducto);
                    $errorD = intval($datosD['NIBPF3010Return']['error']['ERNU01']);

                    if ($datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'USD' || $datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'] == 'EUR') {

                        
                    switch ($datosD['NIBPF3010Return']['NIBPF3010']['INTFIL01']) {
                    case  'CPND':
                    case  'CPJD':
                    case  'CPJE':
                    case  'CPNE':
                    case  'DCPN':
                    case  'DPJN':
                    case  'DPJD':


                                                            
                        $error=true;
                        $numerror=1;
                    break;


                }

                    if ($tipo == 'n') {
                            $tipo = 'n';
                        } 
                    if ($tipo == 'p') {
                            $tipo = 'p';
                        } 

                    }
                    //  fin validar cuenta 

                $moneda=$datosD['NIBPF3010Return']['NIBPF3010']['MONEDA'];
                

                    $data = array(
                        'AfiliacionCash' => array(
                            'tipo_rif' => $usuario['tipo_rif'],
                            'rif' => desencriptar($usuario['rif']),
                            'tipo_cedula' => $tipoCedula,
                            'cedula' => $cedula,
                            'nombre' => $nombre,
                            'numero_cuenta' => $cuenta,
                            'banco' => $banco,
                            'email' => $email,
                            'nroCelular' => substr($telefono, 4),
                            'codigoCelular' => substr($telefono, 0, 4),
                            'accion' => $accion,
                            'tipo' => $tipo,
                            'tipo_carga' => 'a',
                            'moneda'=> $moneda
                        )
                    );

                   
                    // buscar la afiliacion en bd

                    $afiliacion_existente = $this->AfiliacionCash->find(
                        'first', array(
                        'conditions' => array(
                            'tipo_rif' => $usuario['tipo_rif'],
                            'rif' => $usuario['rif'],
                            'tipo_cedula' => $data['AfiliacionCash']['tipo_cedula'],
                            'cedula' => encriptar($data['AfiliacionCash']['cedula']),
                            'tipo' => $tipo,
                        )
                            )
                    );



                    if (!$afiliacion_existente) {
                        // Si no encontro por cedula, buscar por nro de cuenta

                        $afiliacion_existente = $this->AfiliacionCash->find(
                                'first', array(
                            'conditions' => array(
                                'tipo_rif' => $usuario['tipo_rif'],
                                'rif' => $usuario['rif'],
                                'numero_cuenta' => encriptar($data['AfiliacionCash']['numero_cuenta']),
                                'tipo' => $tipo,
                            )
                                )
                        );
                    }

                    // preparar arreglo de error

                    $error = array(
                        'registro' => $linea,
                        'identificacion' => $tipoCedula . $cedula,
                        'nombre' => $nombre,
                    );

                    if ($afiliacion_existente) {
                        // colocar id para validaciones de unicidad
                        $data['AfiliacionCash']['id'] = $afiliacion_existente['AfiliacionCash']['id'];
                    }

                    $this->AfiliacionCash->set($data);

                     if(isset($numerror)){
                                 $error['error'] = 'Cuenta no Permitida - ' . $cuenta;
                                $errores[] = $error;

                            }

                    if (!$this->AfiliacionCash->validates()) {
                        // Validar cedula

                        if (isset($this->AfiliacionCash->validationErrors['tipo_cedula'])) {
                            $error['error'] = 'Tipo de Identificacion invalido - ' . $tipoCedula . $cedula;
                            $errores[] = $error;
                        } else if (isset($this->AfiliacionCash->validationErrors['cedula'])) {
                            switch ($this->AfiliacionCash->validationErrors['cedula']) {
                                case 'noBeneficiario':
                                    $error['error'] = _m('cuenta.no.beneficiario');
                                    break;
                                case 'unicidad':
                                    $error['error'] = _m('beneficiario.ya.afiliado');
                                    break;
                                default:
                                    $error['error'] = 'RIF o Cedula invalida - ' . $tipoCedula . $cedula;
                            }
                            $errores[] = $error;
                        }

                        if ($accion != 'D') {
                            // Si no es instruccion de eliminaci n, validar todos los datos

                            if (isset($this->AfiliacionCash->validationErrors['nombre'])) {
                                $error['error'] = 'Nombre o direccion del beneficiario en blanco/invalido - ' . $nombre;
                                $errores[] = $error;
                            }

                            if (isset($this->AfiliacionCash->validationErrors['numero_cuenta'])) {

                                switch ($this->AfiliacionCash->validationErrors['numero_cuenta']) {
                                    case 'unicidad':
                                        $error['error'] = _m('producto.nodisponible') . ' - ' . $cuenta;
                                        break;
                                    case 'cuentaControlada':
                                    case 'cuentaInactiva':
                                        $error['error'] = _m('cuenta.inactiva', 'la cuenta del beneficiario') . ' - ' . $cuenta;
                                        break;
                                    case 'productoPropio':
                                        $error['error'] = _m('producto.propio') . ' - ' . $cuenta;
                                        break;
                                    default:
                                        $error['error'] = 'Cuenta del Beneficiario/deudor invalida o no existe - ' . $cuenta;
                                }

                                $errores[] = $error;
                            }
                           

                            if (isset($this->AfiliacionCash->validationErrors['email'])) {
                                $error['error'] = 'Instruccion invalida - Email- ' . $email;
                                $errores[] = $error;
                            }

                            if (isset($this->AfiliacionCash->validationErrors['nroCelular'])) {
                                $error['error'] = 'Instruccion invalida - Celular - ' . $telefono;
                                $errores[] = $error;
                            }

                        }
                    }


                    if ($accion == 'D' && !$afiliacion_existente) {
                        $error['error'] = 'No se puede eliminar este beneficiario, ya que no existe';
                        $errores[] = $error;
                    } else if (array_key_exists($tipoCedula . $cedula, $kcedulas)) {
                        $error['error'] = 'Registro duplicado - ' . $tipoCedula . $cedula;
                        $errores[] = $error;
                    } else if (
                            array_key_exists($cuenta, $kcuentas) &&
                            !($tipo == 'P' && substr($cuenta, 0, 4) == '0171')
                    ) {
                        // Para pago de proveedores en cuentas de banco activo
                        // se pueden repetir los n meros de cuenta para distintos afiliados
                        // siempre y cuando sean cotitulares de dicha cuenta
                        // En cualquier otro caso, indicar el error de duplicidad
                        $error['error'] = 'Registro duplicado - ' . $cuenta;
                        $errores[] = $error;
                    } else if (empty($this->AfiliacionCash->validationErrors)) {
                        $data['AfiliacionCash']['cedula'] = encriptar($data['AfiliacionCash']['cedula']);
                        $data['AfiliacionCash']['nombre'] = encriptar($data['AfiliacionCash']['nombre']);
                        $data['AfiliacionCash']['numero_cuenta'] = encriptar($data['AfiliacionCash']['numero_cuenta']);
                        $data['AfiliacionCash']['banco'] = encriptar($data['AfiliacionCash']['banco']);
                        $data['AfiliacionCash']['email'] = encriptar($data['AfiliacionCash']['email']);
                        $data['AfiliacionCash']['nroCelular'] = encriptar($data['AfiliacionCash']['nroCelular']);
                        $data['AfiliacionCash']['codigoCelular'] = encriptar($data['AfiliacionCash']['codigoCelular']);
                        $registros[] = $data;
                    }

                    $kcuentas[$cuenta] = true;

                    $kcedulas[$tipoCedula . $cedula] = true;
                }

                if (!count($errores)) {
                    $estado['archivo'] = $this->data['CargaMasivaBeneficiariosCash']['archivo']['name'];
                    $estado['registros'] = $registros;
                    $this->Session->write('afiliacion.masiva', $estado);
                    $this->redirect('carga_masiva_beneficiarios_confirmacion');
                } else {

                    $manejoErrores['tipo'] = $tipo;
                    $manejoErrores['errores'] = $errores;
                    $manejoErrores['archivo'] = $this->data['CargaMasivaBeneficiariosCash']['archivo']['name'];
                    $this->Session->write('cash.afiliaciones_errores', $manejoErrores);
                    $this->redirect('carga_masiva_beneficiarios_error');
                }
            }

            if (isset($this->AfiliacionCash->validationErrors['archivo'])) {
                $error_archivo = true;
                $mensaje_error = $this->AfiliacionCash->validationErrors['archivo'];
            }
        }

        $this->set(compact('tipo', 'error_archivo', 'mensaje_error'));
        $this->layout = 'interna';
    }

    /**
     * Acci n para confirmaci n de la carga masiva de beneficiarios para
     * pagos de n mina y proveedores
     */
    function carga_masiva_beneficiarios_confirmacion() {
        if (!$this->Session->check('afiliacion.masiva'))
            $this->redirect('pagos');
        if (!$this->Session->check('cash.pagos'))
            $this->redirect('pagos');

        $estado = $this->Session->read('afiliacion.masiva');
        $usuario = $this->Session->read('usuarios.datos');
        $tipo = $this->Session->read('cash.pagos');

        $this->set(compact('tipo', 'estado'));
        $this->layout = 'interna';

        switch ($tipo['tipo']) {
            case 'n':
                $this->set('seccion_actual', 'cash.afiliaciones.nomina.masiva');
                $this->set('titulo', 'Afiliaciones - Nomina');
                break;
            case 'p':
                $this->set('seccion_actual', 'cash.afiliaciones.proveedores.masiva');
                $this->set('titulo', 'Afiliaciones - Proveedores');
                break;
        }
    }

    /**
     * Acci?ara indicar el resultado de la carga masiva de beneficiarios
     * para pagos de n?a y proveedores
     */
    function carga_masiva_beneficiarios_resultado() {
        if (!$this->Session->check('afiliacion.masiva'))
            $this->redirect('pagos');
        if (!$this->Session->check('cash.pagos'))
            $this->redirect('pagos');

        $data = $this->Session->read('afiliacion.masiva');
        $usuario = $this->Session->read('usuarios.datos');
        $tipo = $this->Session->read('cash.pagos');

        $band = true;
        $afiliados = array();
        $errores = array();
        $agregados = 0;
        $modificados = 0;
        $eliminados = 0;
        $registros = array();
        $bandera = true;
        $noEliminados = array();
        $noAgregados = array();
        $noModificados = array();

        $totalregistros = count($data);

        $ds = $this->AfiliacionCash->getDataSource();
        $i = 1;

        foreach ($data['registros'] as $datos) {
            if ($datos['AfiliacionCash']['accion'] == 'A') {
                if (isset($datos['AfiliacionCash']['id'])) {
                    $afiliados[] = $datos;
                    $modificados++;
                } else {
                    $afiliados[] = $datos;
                    $agregados++;
                }
            } else {
                $afiliados[] = $datos;
                $eliminados ++;
            }

            $i++;
        }

        //verifica si hay errores 
        if (count($errores)) {
            $manejoErrores['tipo'] = $tipo;
            $manejoErrores['errores'] = $errores;
            $manejoErrores['archivo'] = $data['archivo'];
            $this->Session->write('cash.afiliaciones_errores', $manejoErrores);
            $this->redirect('carga_masiva_beneficiarios_error');
        }

        foreach ($afiliados as &$datos) {
            if ($datos['AfiliacionCash']['accion'] == 'D') {
                $datos['AfiliacionCash']['descripcion_accion'] = 'Eliminar';
                if (!$this->AfiliacionCash->deleteAll(
                    array(
                        'tipo' => $tipo['tipo'],
                        'tipo_rif' => $usuario['tipo_rif'],
                        'rif' => $usuario['rif'],
                        'cedula' => $datos['AfiliacionCash']['cedula'],
                        'tipo_cedula' => $datos['AfiliacionCash']['tipo_cedula']
                        ), false
                )) {
                    $bandera = false;
                    $this->log('Error de bd al eliminar afiliado ' . $usuario['tipo_rif'] . desencriptar($usuario['rif']), LOG_ERROR);
                    $this->log($this->getDataSource()->error, LOG_ERROR);
                } else {
                    $bandera = true;
                }
            } elseif ($datos['AfiliacionCash']['accion'] == 'A') {
                $c = desencriptar($datos['AfiliacionCash']['codigoCelular']);
                $n = desencriptar($datos['AfiliacionCash']['nroCelular']);
                $celular = $c . $n;

                $this->AfiliacionCash->create();

                if (isset($datos['AfiliacionCash']['id'])) {
                    $datos['AfiliacionCash']['descripcion_accion'] = 'Modificar';
                    $this->AfiliacionCash->id = $datos['AfiliacionCash']['id'];
                } else {
                    $datos['AfiliacionCash']['descripcion_accion'] = 'Agregar';
                }

                $res = $this->AfiliacionCash->save(
                    array(
                    'AfiliacionCash' =>
                        array(
                            'tipo' => $datos['AfiliacionCash']['tipo'],
                            'nombre' => $datos['AfiliacionCash']['nombre'],
                            'tipo_cedula' => $datos['AfiliacionCash']['tipo_cedula'],
                            'cedula' => $datos['AfiliacionCash']['cedula'],
                            'numero_cuenta' => $datos['AfiliacionCash']['numero_cuenta'],
                            'email' => $datos['AfiliacionCash']['email'],
                            'celular' => encriptar($celular),
                            'tipo_carga' => $datos['AfiliacionCash']['tipo_carga'],
                            'tipo_rif' => $usuario['tipo_rif'],
                            'rif' => $usuario['rif'],
                            'moneda' =>$datos['AfiliacionCash']['moneda']
                    )
                        ), false
                );

                if ($res) {
                    $bandera = true;
                    $registros[] = $res;
                } else {
                    $bandera = false;
                    $this->log('Error de bd al guardar afiliado ' . $usuario['tipo_rif'] . desencriptar($usuario['rif']), LOG_ERROR);
                    $this->log($this->AfiliacionCash->getDataSource()->error, LOG_ERROR);
                }
            }
        }

        $resultado['registros'] = $afiliados;
        $resultado['agregados'] = $agregados;
        $resultado['eliminados'] = $eliminados;
        $resultado['modificados'] = $modificados;
        $resultado['tipo'] = $tipo['tipo'];
        $resultado['archivo'] = $data['archivo'];
        $resultado['cantidad_registros'] = count($data['registros']);

        $this->Session->write('descarga.masiva.pdf', $resultado);

        $cantidad = $this->AfiliacionCash->find(
                'count', array(
            'conditions' => array(
                'tipo' => $tipo['tipo'],
                'tipo_rif' => $usuario['tipo_rif'],
                'rif' => $usuario['rif']
            )
                )
        );

        if ($cantidad > 10) {
            // Si hay mas de 10 afiliados, se desactiva la opci n de pago manual
            // y por lo tanto hay que eliminar los pagos guardados

            $tipos = array(
                'n' => array('nxm', 'nxo', 'nxa'),
                'p' => array('vxm', 'vxo', 'vxa')
            );

            $res = $this->TransaccionPendiente->deleteAll(
                    array(
                'TransaccionPendiente.tipo_rif' => $usuario['tipo_rif'],
                'TransaccionPendiente.rif' => $usuario['rif'],
                'TransaccionPendiente.estatus' => 'g',
                'TransaccionPendiente.tipo' => $tipos[$tipo['tipo']]
                    ), false
            );

            if ($res) {
                $bandera = true;
            } else {
                $bandera = false;
            }
        }

        if ($bandera) {
            $transaccion = ($tipo == 'n' ? 41 : 42);
            $descripcion = 'Carga masiva afiliacion ';
            $descripcion.= ($tipo == 'n' ? 'nomina' : 'proveedor');
            $this->Bitacora->registraBitacora($usuario['id'], $transaccion, NULL, NULL, NULL, NULL, $descripcion);
            $ds->commit($this->AfiliacionCash);
        } else {
            $ds->rollback($this->AfiliacionCash);
        }

        $bancos = $this->Activo->buscarBancos();
        $this->set(compact('bandera', 'resultado', 'bancos', 'afiliados', 'agregados', 'eliminados', 'modificados', 'resultado', 'tipo', 'data'));
        $this->Session->delete('afiliacion.masiva');
        $this->layout = 'interna';

        switch ($tipo['tipo']) {
            case 'n':
                $this->set('seccion_actual', 'cash.afiliaciones.nomina.masiva');
                $this->set('titulo', 'Afiliaciones - Nomina');
                if ($bandera) {
                    // Enviar por sms la notificaci n
                    if (isset($usuario['celular'])) {
                        $this->Sms->enviar(
                                array(
                                    'numcel' => desencriptar($usuario['celular']),
                                    'canal1' => 50
                                )
                        );
                    }
                }
                break;
            case 'p':
                $this->set('seccion_actual', 'cash.afiliaciones.proveedores.masiva');
                $this->set('titulo', 'Afiliaciones - Proveedores');
                if ($bandera) {
                    // Enviar por sms la notificaci n
                    if (isset($usuario['celular'])) {
                        $this->Sms->enviar(
                                array(
                                    'numcel' => desencriptar($usuario['celular']),
                                    'canal1' => 51
                                )
                        );
                    }
                }
                break;
        }
    }
	
	/**
	 * Acci?ara descargar el resultado de la carga masiva de beneficiarios
	 * en PDF 
	 */
	function descarga_carga_masiva_beneficiarios(){
		if(!$this->Session->check('descarga.masiva.pdf')) $this->redirect('/consultas/integral');
		$bancos = $this->Activo->buscarBancos();
		$this->set(compact('bancos'));
		$cargaMasiva=$this->Session->read('descarga.masiva.pdf');
		$usuario = $this->Session->read('usuarios.datos');
		$this->set(compact('usuario','cargaMasiva'));
		$this->render('/cash/descargarCargaMasiva_pdf');
	}
	
	/**
	 * Acci?ara indicar los errores en la carga masiva de beneficiarios
	 * para pagos de n?a y proveedores
	 */
	function carga_masiva_beneficiarios_error(){
		if(!$this->Session->check('cash.pagos')) $this->redirect('/consultas/integral');
		if(!$this->Session->check('cash.afiliaciones_errores')) $this->redirect('/consultas/integral');

		$usuario = $this->Session->read('usuarios.datos');
		$tipo = $this->Session->read('cash.pagos');
		$manejoErrores = $this->Session->read('cash.afiliaciones_errores');
		$this->set(compact('tipo','manejoErrores'));
		$this->layout = 'interna';	
			
		switch($tipo['tipo']){
			case 'n':
				$this->set('seccion_actual','cash.afiliaciones.nomina.masiva');
				$this->set('titulo','Afiliaciones - N/a');
				break;
			case 'p':
				$this->set('seccion_actual','cash.afiliaciones.proveedores.masiva');
				$this->set('titulo','Afiliaciones - Proveedores');
				break;
		}
	}
	
	/**
	 * Acci?ara confirmaci?e los errores ocurridos en la carga masiva
	 * de beneficiarios de pagos de n?a y proveedores 
	 */
	function carga_masiva_beneficiarios_error_confir(){
		if(!$this->Session->check('afiliacion.masiva')) $this->redirect('/consultas/integral');
		if(!$this->Session->check('cash.pagos')) $this->redirect('/consultas/integral');
		if(!$this->Session->check('cash.afiliaciones_errores')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('afiliacion.masiva');
		$usuario = $this->Session->read('usuarios.datos');
		$tipo = $this->Session->read('cash.pagos');
		$errores =  $this->Session->read('cash.afiliaciones_errores');
		
		$this->set(compact('tipo','errores'));	
		$this->layout = 'interna';
		
		switch($tipo['tipo']){
				case 'n':
					$this->set('seccion_actual','cash.afiliaciones.nomina.masiva');
					$this->set('titulo','Afiliaciones - N/a');
					break;
				case 'p':
					$this->set('seccion_actual','cash.afiliaciones.proveedores.masiva');
					$this->set('titulo','Afiliaciones - Proveedores');
					break;
			}	
	}
	
	/**
	 * Acci?ara descargar en PDF los errores ocurridos en la carga
	 * masiva de pagos
	 */
	function descarga_carga_masiva_error(){
		if(!$this->Session->check('cash.afiliaciones_errores')) $this->redirect('/consultas/integral');
		
		$estado = $this->Session->read('cash.afiliaciones_errores');

		$usuario = $this->Session->read('usuarios.datos');
		
		$this->set(compact('estado','usuario'));
		
		$this->layout = 'nada';
	}
	
}

/**
 * Funci?ara ordenar por campo identificacion * 
 * @param array $a
 * @param array $b
 * @return int
 */
function ordernar_afiliacioncash_por_id($a,$b){
	if($a['AfiliacionCash']['tipo_cedula'] == $b['AfiliacionCash']['tipo_cedula']){
		// Si el tipo de identificaci?s el mismo
		// comparar el n?mero
		
		$id_a = desencriptar($a['AfiliacionCash']['cedula']);
		$id_b = desencriptar($b['AfiliacionCash']['cedula']);
		
		if($a['AfiliacionCash']['tipo_cedula']=='P'){
			// Si es pasaporte, puede tener letras, por lo tanto la comparaciones textual
			return strcmp($id_a,$id_b);
		} else {
			// Comparacion umca
			
			$id_a_i = intval(trim($id_a));
			$id_b_i = intval(trim($id_b));
			
			if($id_a_i==$id_b_i){
				return 0;
			} else if($id_a_i<$id_b_i){
				return -1;
			} else {
				return 1;
			}
		}
	} else {
		return strcmp($a['AfiliacionCash']['tipo_cedula'],$b['AfiliacionCash']['tipo_cedula']);
	}
}

/**
 * Funci?ara ordenar el listado de errores
 * 
 * @param array $a
 * @param array $b
 * @return int
 */
function ordernar_errores_por_linea($a, $b){
	return strcmp($a['registro'], $b['registro']);
}


function diaJuliano($dia, $mes, $anio){
  $bisiesto = date('L');
  if($bisiesto == 1){
        $diaFebrero = 29;
    }else{     
        $diaFebrero = 28;
    } 
    switch($mes){
    case 1:
       $dias = $dia;
       break;
    case 2: 
       $dias = 31 + $dia;
       break;
    case 3:
       $dias = 31 + $diaFebrero + $dia;
       break;          
    case 4:
       $dias = 62 + $diaFebrero + $dia;
       break;          
    case 5:
       $dias = 92 + $diaFebrero + $dia;
       break;          
    case 6:
       $dias = 123 + $diaFebrero + $dia;
       break; 
    case 7:
       $dias = 153 + $diaFebrero + $dia;
       break; 
    case 8:
       $dias = 184 + $diaFebrero + $dia;
       break; 
    case 9:
       $dias = 215 + $diaFebrero + $dia;
       break; 
    case 10:
       $dias = 245 + $diaFebrero + $dia;
       break; 
    case 11:
       $dias = 276 + $diaFebrero + $dia;
       break;        
    case 12:
       $dias = 306 + $diaFebrero + $dia;
       break;        
    }
    return $dias;
}




function completarDecimales($mto){
    if(strpos($mto,'.')){
        $arrgMto = explode(".",$mto);
        if(strlen($arrgMto)==1){ 
            $mto = $mto . "0";    
        }
    }else{
        $mto = $mto . ".00";
    }
    return $mto;
}

