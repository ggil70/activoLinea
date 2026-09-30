<?php
/**
 * Controlador de afiliaciones de pagos y transferenciass
 */
 

/**
 * Controlador de afiliaciones de pagos y transferencias
 */
class AfiliacionesController extends AppController {

	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array('Usuario','Afiliacion', 'Bitacora', 'PrefijosTelefonicos');

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
  var $components = array('Activo', 'Session', 'Email','Sms', 'Credicard','Simpletv');

	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */
	var $helpers = array('Activo','Html','Session','Form','Text');

	/**
	 * Acción que se encarga de mostrar el listado de afiliaciones del usuario
	 * @param string $volver Variable que indica si la página se carga
	 * debido a un regreso
	 * 
	 */ 

	  function limiteDigitelMovistar()
{
    /** PARA EL MONTO LIMITE EN LA TRANSACCION **/

    // NUEVOS PARAMETROS DIGITEL Y MOVISTAR

    // Declaración de arrays asociativos
    $montos_digitel = array();
    for ($i = 1; $i <= 8; $i++) {
        $monto = _p("monto_digitel_$i");

        // Validación y cast a float (o int si son enteros puros)
        if (empty($monto) && $monto !== '0') {
            $monto = 0; // Valor por defecto si está vacío o nulo
        }

        // Aplicar formato a dos decimales
        $monto_formateado = number_format($monto, 2, ',', ''); // Ej: 10 -> "10,00"

        // **IMPORTANTE: Usar el monto formateado como clave y valor**
        $montos_digitel[$monto_formateado] = $monto_formateado;
    }

    // Declaración de arrays asociativos
    $montos_movistar = array();
    for ($i = 9; $i <= 16; $i++) {
        $monto = _p("monto_movistar_$i");

        // Validación y cast a float
        if (empty($monto) && $monto !== '0') {
            $monto = 0;
        }
        // Aplicar formato a dos decimales
        $monto_formateado = number_format($monto, 2, ',', '');

        // **IMPORTANTE: Usar el monto formateado como clave y valor**
        $montos_movistar[$monto_formateado] = $monto_formateado;
    }

    // El resto de tu lógica para mínimo y máximo está bien
    $minimo_digitel_php = !empty($montos_digitel) ? reset($montos_digitel) : '0,00';
    $maximo_digitel_php = !empty($montos_digitel) ? end($montos_digitel) : '0,00';

    $minimo_movistar_php = !empty($montos_movistar) ? reset($montos_movistar) : '0,00';
    $maximo_movistar_php = !empty($montos_movistar) ? end($montos_movistar) : '0,00';

    return array(
        'montos_digitel' => $montos_digitel,
        'montos_movistar' => $montos_movistar,
        'minimo_digitel_php' => $minimo_digitel_php,
        'maximo_digitel_php' => $maximo_digitel_php,
        'minimo_movistar_php' => $minimo_movistar_php,
        'maximo_movistar_php' => $maximo_movistar_php,
    );
}



	 function listado($volver=null){
		
	    /* Para validar el acceso al modulo */
            if(!validarTransaccion(array(13,14,100010,100011,100035))) $this->redirect('/consultas/integral');
 	    /* Para validar el acceso al modulo */
		
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		$usuario = $this->Session->read('usuarios.datos');
		
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
                                    'url_aceptar'=>'/afiliaciones/listado',
                                    'layout'=>'interna',
                                    'titulo'=>'Afiliaciones',
                                    'pasos'=> array(),
                                    'paso_actual'=>0,
                                    'seccion_actual'=>'afiliaciones.afiliacion_tdc'
                            )
                    );

                    $this->redirect('/seguridad/validar_tdc');
		}
		
		if(!empty($this->data['Afiliacion']) && !isset($this->data['Afiliacion']['paginando'] ) ){
			$this->Afiliacion->set($this->data);
                        
			if($this->Afiliacion->validates(array('fieldList'=>array('accion','id')))){
				$this->Session->write('afiliaciones.listado',$this->data);

				switch($this->data['accion']){
					case 'crear':
						$this->Session->delete('afiliaciones.crear');
                    	$this->redirect('crear');
						break;
					
					case 'editar':
						$afiliacion = $this->Afiliacion->buscar($usuario['id']);
						$afiliacion['Afiliacion']['monto_maximo'] = empty($afiliacion['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($afiliacion['Afiliacion']['monto_maximo']);
						$afiliacion['Afiliacion'] = desencriptarCampos($afiliacion['Afiliacion'], array('numero_cuenta','email','cedula','nombre_beneficiario'));
						$afiliacion['Afiliacion']['alias'] = ($afiliacion['Afiliacion']['alias']!="") ? desencriptar($afiliacion['Afiliacion']['alias']) : '';
						$afiliacion['Afiliacion']['tipo_operacion'] = trim($afiliacion['Afiliacion']['tipo_operacion']);
                                                $afiliacion['Afiliacion']['codigo_telefono'] = trim($afiliacion['Afiliacion']['codigo_telefono']);
						$afiliacion['Afiliacion']['accion'] ='Modificación';
            			$this->Session->write('afiliaciones.numero_cuenta_mod',$afiliacion['Afiliacion']['numero_cuenta']);

						$this->Session->write('afiliaciones.crear',$afiliacion);
						$this->redirect('crear/e');
						break;
						
					case 'eliminar':
						$afiliacion = $this->Afiliacion->buscar($usuario['id']);
						$afiliacion['Afiliacion']['monto_maximo'] = empty($afiliacion['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($afiliacion['Afiliacion']['monto_maximo']);
						$afiliacion['Afiliacion'] = desencriptarCampos($afiliacion['Afiliacion'], array('numero_cuenta','email','cedula','nombre_beneficiario'));
						$afiliacion['Afiliacion']['alias'] = ($afiliacion['Afiliacion']['alias']!="") ? desencriptar($afiliacion['Afiliacion']['alias']) : '';
						$afiliacion['Afiliacion']['accion'] ='Eliminacion';
						$afiliacion['Afiliacion']['tipo_operacion'] = trim($afiliacion['Afiliacion']['tipo_operacion']);
						// Para la busqueda del banco
						$bancos = $this->Activo->buscarBancos();

						if($afiliacion['Afiliacion']['tipo_afiliacion']=='t'){
                                                    if($afiliacion['Afiliacion']['banco']!=''){
                                                        $afiliacion['Afiliacion']['bancoDesc'] = $bancos[$afiliacion['Afiliacion']['banco']];
                                                    }else{
                                                        $afiliacion['Afiliacion']['bancoDesc'] = $bancos[substr($afiliacion['Afiliacion']['numero_cuenta'], 0, 4)];
                                                    }
						}else{
							$tdc = str_pad($afiliacion['Afiliacion']['numero_cuenta'],20,'0',STR_PAD_RIGHT);
							$valorTarjeta = array('TNROTARJ' => $tdc);
							$datos = $this->Activo->validarBIN($valorTarjeta);
							if(!isset($datos['NIB090501Return']['error'])){
								$afiliacion['Afiliacion']['banco'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
							}
						}
						// Para la busqueda del banco
						
						$this->Session->write('afiliaciones.eliminar',$afiliacion);
						$this->redirect('eliminar_confirmacion');
						break;
				}
			}
		} else if(isset($volver) && $this->Session->check('afiliaciones.listado')){
			$this->data = $this->Session->read('afiliaciones.listado');
		}
		
		if($usuario['tipo_rif']){ // PARA JURIDICO
			$conditions = array(
                            'tipo_rif' => $usuario['tipo_rif'],
                            'rif' => $usuario['rif'],
			);
		}
		else{ // PARA NATURAL
			$conditions = array(
                            'Afiliacion.usuario_id'=>$usuario['id']
			);
		}
		
		if(!validarTransaccion(13)){ // Verifica transferencias
			$conditions['Afiliacion.tipo_afiliacion not like'] = 't';
		}
		if(!validarTransaccion(14)){ // Verifica pagos TDC
			$conditions['Afiliacion.tipo_afiliacion not like'] = 'p';
		}
		if(!validarTransaccion(100010)){ // Verifica servicios postpago
			$conditions['Afiliacion.tipo_afiliacion not like'] = 's';
		}
		if(!validarTransaccion(100011)){ // Verifica servicios reacarga
			$conditions['Afiliacion.tipo_afiliacion not like'] = 'r';
		}
                if(!validarTransaccion(100035)){ // Verifica impuestos nacionales/aduanales
			$conditions['Afiliacion.tipo_afiliacion not like'] = 'i';
		}
                if(!validarTransaccion(101071)){ // Verifica transferencias moneda extranjera
			$conditions['Afiliacion.tipo_afiliacion not like'] = 'd';
		}
                
                $conditions = array_merge($conditions, array('Afiliacion.tipo_afiliacion not like'=>'a','Afiliacion.tipo_afiliacion not like'=>'m'));                
                
		if(!empty($this->data['Afiliacion']['tipo_afiliacion'])){
                    $conditions = array_merge($conditions, array('Afiliacion.tipo_afiliacion'=>$this->data['Afiliacion']['tipo_afiliacion']));
		}

		$total_registros = $this->Afiliacion->find(
                    'count',
                    array( 'conditions'=>$conditions )
		);	

		$offset = 0;

		if(
                    isset($this->data['Afiliacion']['offset']) &&
                    preg_match('/^\d+$/',$this->data['Afiliacion']['offset'])
		){
                    $offset = intval($this->data['Afiliacion']['offset']);			
		}
		
		$listado = $this->Afiliacion->find(
                    'all',
                    array(
                        'conditions'=>$conditions,
                        'order'=>'Afiliacion.id desc',
                        'offset'=>$offset,
                        'limit'=>Configure::read('Tamano.paginacion')
                    )
		);


//****************************************
//Validar que en el listado de afiliados no muestra los registros 
//TV Movistar ni en recarga, ni postpago.
//****************************************		
    if($listado){
        if(count($listado) >0 ){
            //Validar que no se incluya el servicio de 
            //  REcarga /  movilstar TV
            /////////       

            $auxListado = $listado;
            $listado = array();
            
            $i = 0;
            foreach ($auxListado as $a){                     
                //Validación para excluir pago de movistart tv en recarga y postpago.

                if(trim($a['Afiliacion']['tipo_operacion'])=='st' && (trim($a['Afiliacion']['tipo_servicio'])=='rtv' || trim($a['Afiliacion']['tipo_servicio'])=='stv')){
                        $swTv = 1;
                }else{
                    $arreglo = array(
                       'Afiliacion'=>array(
                          'id'=> $a['Afiliacion']['id'],
                          'usuario_id'=> $a['Afiliacion']['usuario_id'], 
                          'tipo_afiliacion'=> $a['Afiliacion']['tipo_afiliacion'],
                          'tipo_operacion'=> $a['Afiliacion']['tipo_operacion'],
                          'alias'=> $a['Afiliacion']['alias'], 
                          'codigo_telefono'=> $a['Afiliacion']['codigo_telefono'],
                          'banco'=> $a['Afiliacion']['banco'], 
                          'numero_cuenta'=> $a['Afiliacion']['numero_cuenta'], 
                          'nombre_beneficiario'=> $a['Afiliacion']['nombre_beneficiario'], 
                          'tipo_cedula'=> $a['Afiliacion']['tipo_cedula'], 
                          'cedula'=> $a['Afiliacion']['cedula'], 
                          'email'=> $a['Afiliacion']['email'],                       
                          'monto_maximo'=> $a['Afiliacion']['monto_maximo'], 
                          'tipo_rif'=> $a['Afiliacion']['tipo_rif'],
                          'rif'=> $a['Afiliacion']['rif'],
                          'numero_contrato'=> $a['Afiliacion']['numero_contrato'],
                          'fecha_contrato'=> $a['Afiliacion']['fecha_contrato'],
                          'tipo_servicio'=> $a['Afiliacion']['tipo_servicio'],
                          'telefono'=> $a['Afiliacion']['telefono'],
                          'numero_cuenta_receptora'=> $a['Afiliacion']['numero_cuenta_receptora'],
                          'numero_cuenta_ahorro'=> $a['Afiliacion']['numero_cuenta_ahorro'],                      
                       )
                    );
                    array_push($listado, $arreglo);
                    $i=$i+1;
                }    
            }    
        }    
    } 

//****************************************
//Fin validacion del listado de afiliados no muestra los registros 
//TV Movistar ni en recarga, ni postpago.
//****************************************

		$tipos_listado = $tipos = $this->Afiliacion->buscarTipos();
                unset($tipos['m']);
                unset($tipos['a']);
		$subtipos = $this->Afiliacion->buscarSubTipos();
		
		if( !validarTransaccion(13) ){ // Verifica transferencias
			unset($tipos['t']);
		}
		if( !validarTransaccion(101071) ){ // Verifica transferencias moneda extranjera
			unset($tipos['d']);
		}
		if( !validarTransaccion(14) ){ // Verifica pagos TDC
			unset($tipos['p']);
		}
		if( !validarTransaccion(100010) ){ // Verifica pagos postpagos
			unset($tipos['s']);
		}
		if( !validarTransaccion(100011) ){ // Verifica recargas
			unset($tipos['r']);
		}
		
        if( !validarTransaccion(100035) || $usuario['tipo_cedula'] == 'P'){ // Verifica impuestos nacionales/aduanales
            unset($tipos['i']);
		}
		$this->layout = 'interna';
		$this->set('seccion_actual','afiliaciones.afiliacion_tdc');
		$this->set(compact('listado','tipos','subtipos','total_registros','tipos_listado'));
	}
        
        /**
	 * Acción que se encarga de mostrar el listado de afiliaciones de saldo activo que posee el cliente
	 * @param string $volver Variable que indica si la página se carga
	 * debido a un regreso
	 */
	function listado_saldo_activo($volver=null){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(array(100020))) $this->redirect('/consultas/integral');		

                /* Leer los Datos de Session del Usuario */
                $usuario = $this->Session->read('usuarios.datos');
                
                /* Verificar Datos de Tarjeta de Seguridad */
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
                                'url_aceptar'=>'/afiliaciones/listado_saldo_activo',
                                'layout'=>'interna',
                                'titulo'=>'Afiliaciones',
                                'pasos'=> array(),
                                'paso_actual'=>0,
                                'seccion_actual'=>'afiliaciones.afiliacion_saldo_activo'
                        )
                    );

                   $this->redirect('/seguridad/validar_tdc');
		}
                
                /* Verifica si no se esta paginando la Vista*/
		if(!isset($this->data['Afiliacion']['paginando']) ){
                    $this->Afiliacion->set($this->data); // Setea los Valores

                    if(isset($this->data['Afiliacion']['id'])){
                        // Datos para del servicio
                        $afiliacion['Afiliacion']['id'] = $this->data['Afiliacion']['id']; // id seleecionado por el usuario
                        $dat = explode('|', $this->data['Afiliacion']['id']);
                        $afiliacion['Afiliacion']['nrocliente'] = $dat[0];
                        $afiliacion['Afiliacion']['numero_cuenta'] = $dat[1];
                        $afiliacion['Afiliacion']['numero_tarjeta'] = $dat[2];
                        // Pasa a session las Variables del Formulario
                        $this->Session->write('afiliaciones.crear_saldo_activo',$this->data);
                    }
                    /* Verifica y redireccdiona segun la opcion */
                    switch($this->data['accion']){
                         case 'crear':
                                 $this->Session->delete('afiliaciones.crear_saldo_activo');
                                 $this->redirect('crear_saldo_activo');
                         break;

                         case 'editar':
                                 $afiliacion['Afiliacion']['accion'] ='Modificación';
                                 $this->Session->write('afiliaciones.crear_saldo_activo',$afiliacion);
                                 $this->redirect('crear_saldo_activo/e');
                         break;

                         case 'eliminar':
                                 $afiliacion['Afiliacion']['accion'] ='Modificación';
                                 $this->Session->write('afiliaciones.crear_saldo_activo',$afiliacion);
                                 $this->redirect('eliminar_confirmacion_saldo_activo');
                         break;
                     }                    
                }
                
                $tipo_documento = $usuario['tipo_cedula'];
                $documento= desencriptar($usuario['cedula']);
                
                /* Busca los Datos del Cliente*/
                $datos_basicos = $this->Session->read('usuarios.nibpf3010');
                
                $this->Session->write('NROCLIENTE',$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE']);
                $nrocliente = $datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'];

                /* Busca las Afiliaciones de Saldo Activo del Cliente*/
                $res = $this->Activo->invoca(
                    'ECC051001',
                    array(
                        'req'=>array(
                            'e01PATCUN'=>$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],
                            'h01OPECOD'=>'0005'											
                        )
                    )
                ); 
                /* Inicializa el listado de Afiliaciones */
                $arrData = array();
                if(!empty($res['ECC051001Return']['ECC051001']['ECC051001'])){
                    $arrData = $res['ECC051001Return']['ECC051001']['ECC051001'];
                    normaliza($arrData);
                }                                                

                $total_registros = count($arrData);

                $af_ebs = array();
                $fech_ebs = array();
                foreach($arrData as $l){
                    $af_ebs[] = $l['e01PATACC']; //afiliaciones en EBS
                    $fech_ebs[] =  $l['e01PATODT']; // fecha afiliaciones en EBS
                }
                
                $listAf = $this->Afiliacion->buscarAfiliacionesSaldoActivo($usuario['id']);
                
                $af_bbdd = array();
                foreach($listAf as $l){
                    $af_bbdd[] = desencriptar($l['Afiliacion']['numero_cuenta']); 
                }
                
            
                for($i=0;$i<count($af_ebs);$i++){
                    if(!in_array($af_ebs[$i], $af_bbdd)){
                        //Almacena Fecha en Base de Datos
                        $valores = array();
                        $valores['Afiliacion']['nombre_beneficiario'] = $usuario['nombre'];
                        $valores['Afiliacion']['tipo_afiliacion'] = 'a';
                        $valores['Afiliacion']['tipo_operacion'] = 'mt';
                        $valores['Afiliacion']['tipo_cedula'] = $usuario['tipo_cedula'];
                        $valores['Afiliacion']['cedula'] = $usuario['cedula'];
                        $valores['Afiliacion']['email'] = $usuario['email_principal'];
                        $valores['Afiliacion']['usuario_id'] = $usuario['id'];
                        $valores['Afiliacion']['numero_contrato'] = encriptar($af_ebs[$i]);
                        $valores['Afiliacion']['fecha_contrato'] = $fech_ebs[$i];
                        $valores['Afiliacion']['numero_cuenta'] = encriptar($af_ebs[$i]);

                        $this->Afiliacion->afiliarSaldoActivo($valores);
                    }
                }
                
                for($i=0;$i<count($af_bbdd);$i++){
                    if(!in_array($af_bbdd[$i], $af_ebs)){
                        $this->Afiliacion->desafiliarSaldoActivo(encriptar($af_bbdd[$i]));
                    }
                }
                
                // Empieza código para paginar		
		$offset = 0;

		if(
                    isset($this->data['Afiliacion']['offset']) &&
                    preg_match('/^\d+$/',$this->data['Afiliacion']['offset'])
		){
                    $offset = intval($this->data['Afiliacion']['offset']);			
		}
                
		$arrData = array_slice(
			$arrData,
			$offset,
			Configure::read('Tamano.paginacion'),
			true
		);                
		// Fin código para paginar
                                
                $this->set(compact('total_registros','nrocliente','arrData'));		                                
                $this->set('seccion_actual','afiliaciones.afiliacion_saldo_activo');
		$this->layout = 'interna'; // Indica Plantilla a Usar
	}
        
	/**
	 * Acción para crear o modificar una afiliación
	 * @param string Indica si se está regresando de la confirmación
	 */
	function crear($volver=null){
                
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(array(13,14,100010,100011,100035))) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
	
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');
		$usuario = $this->Session->read('usuarios.datos');

        $montoMinimo=0;
        $montoMaximo=0;                
        
        $limiteDigMov = $this->limiteDigitelMovistar();


        //Tarjeta de Coordenadas
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
					'url_aceptar'=>'/afiliaciones/listado',
					'layout'=>'interna',
					'titulo'=>'Afiliaciones',
					'pasos'=> array(),
					'paso_actual'=>0,
					'seccion_actual'=>'afiliaciones.afiliacion_tdc'
				)
			);
			$this->redirect('/seguridad/validar_tdc');
		}
		//fin tarjeta coordenadas
				
		$bancos = $this->Activo->buscarBancos();
                $prefijos_telefonicos = $this->PrefijosTelefonicos->listar_prefijos();
                
                $prefijos_telefonicos_movil = $this->PrefijosTelefonicos->listar_prefijos_movil();
                $prefijos = array();                 
                foreach ($prefijos_telefonicos_movil as $prefijo) {                    
                    $prefijos[$prefijo['PrefijosTelefonicos']['codigo_telefono']] = $prefijo['PrefijosTelefonicos']['codigo_telefono'];
                } 

                
        //Limites por transaccion
		$limite_total = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : _p('limite_internas_terceros');
		$limite=array('max_it_trans'=>$usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : _p('limite_internas_terceros'),'max_e_trans'=>$usuario['limite_externas'] ? $usuario['limite_externas'] : _p('limite_externas')
		);
		
		if($limite['max_it_trans']) $limite['max_it_trans'] = formatea_monto_fromWs($limite['max_it_trans']);
		if($limite['max_e_trans']) $limite['max_e_trans'] = formatea_monto_fromWs($limite['max_e_trans']);
				
		$limite['internas_terceros'] = $limite['max_it_trans'];
		$limite['externas'] = $limite['max_e_trans'];
		$this->set('limite', $limite);
		//Fin limites de transacciones.
		           

                
		if(empty($this->data['Afiliacion'])){ 
			if(isset($volver) && $this->Session->check('afiliaciones.crear')){
				$this->data = $this->Session->read('afiliaciones.crear');
        		$this->Session->write('afiliaciones.proceso','m');
			}else{
        		$this->Session->write('afiliaciones.proceso','i');
      		}
/*0000*/} else {

			$this->data['Afiliacion']['alias']= $this->sanitize_input($this->data['Afiliacion']['alias']);

			$this->Afiliacion->set($this->data);



/*1111*/	if($this->Afiliacion->validates(
				array(
					'fieldList'=>array(
						'tipo_afiliacion',
						'tipo_operacion'
					)
				)
			)){

		     


				$array_validacion = array();
                $this->Afiliacion->validate['alias'] = array(
                    'requerido'=> array('rule'=>'notEmpty')
                );        

                //Afiliación Tarejeta Prepagada Tercero
                //configuracion e valores para tarjeta prepagada cash
                //estos valores son modificado al validar la cedula del beneficiario
                //Es para evitar las validaciones de los campos 
                if($this->data['Afiliacion']['tipo_afiliacion']=='h'){ 
                    //$this->data['Afiliacion']['nombre_beneficiario'] = 'Cliente solicitante';
                    $this->data['Afiliacion']['monto_maximo'] = '1,00';
                    //$this->data['Afiliacion']['numero_cuenta'] = '1000000000';
                }
                //fin Afiliación Tarjeta Prepagada 


                //Completar decimales a 2 digitos 				
                if($this->data['Afiliacion']['tipo_afiliacion']=='r'){
                	$monto = $this->data['Afiliacion']['monto_maximo'];
    	            $arrayMonto = explode(',', $monto);
        	        $decimal = $arrayMonto[1];
        	        if(strlen($decimal)==1){
        	        	$this->data['Afiliacion']['monto_maximo'] = $this->data['Afiliacion']['monto_maximo'] . '0';
        	        }
                } 


/*2222*/		switch($this->data['Afiliacion']['tipo_afiliacion']){
					case 't': // Validaciones para transferencias
						// Validaciones para transferencias
						if($this->data['Afiliacion']['tipo_operacion']=='t'){
							$this->Afiliacion->validate['numero_cuenta']['numero'] = array(
								'rule'=>'/(?:^0171\d{16}$)|(?:^\d{10}$)/'
							);
						}else {
                            if($this->data['Afiliacion']['destino']=='c'){
                                $this->Afiliacion->validate['numero_cuenta'] = array(
								'numero'=>array(
									'rule'=>'nroCuenta'
								),
								'otro_banco'=>array(
									'rule'=>'otro_banco'
								),
								'requerido'=> array(
									'rule'=>'notEmpty'
								)
							    );
                            }else{
                                $this->data['Afiliacion']['codigo_telefono'] = $this->data['Afiliacion']['codigo_telefono_movil'];
                                $this->data['Afiliacion']['numero_cuenta'] = $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['telefono'];
                                $this->Afiliacion->validate['telefono'] = array(			
									'requerido'=> array(
									'rule'=>'notEmpty'
									)
								);
                            }											
						}										
						
						break;
					case 'p': // Validaciones para pagos de tarjetas
						$this->Afiliacion->validate['numero_cuenta']['numero'] = array(
							'rule'=>'numeric'
						);	
						break;
          			case 'h': // Validaciones para pagos de tarjetas
            			break;            
					case 's': // Validaciones para pagos de servicios

						if($this->data['Afiliacion']['tipo_operacion']!='si'){
                            $this->Afiliacion->validate['tipo_servicio'] = array(
									'requerido'=> array(
				  				    'rule'=>'notEmpty'
							    )
						    );
                              
                            switch($this->data['Afiliacion']['tipo_operacion']){
							case 'sc':
								$validacion_formato = 'cantv';
                                break;
							case 'sm':
								$validacion_formato = 'movilnet_postpago';
								//$montoMinimo=formatea_monto_fromWs(_p('recarga_minima_movilnet_postpago'));
	                            //$montoMaximo=formatea_monto_fromWs(_p('recarga_maxima_movilnet_postpago'));
								$montoMinimo="";
	                            $montoMaximo="";
                                $this->data['Afiliacion']['monto_limite'] = "";   
                                $this->data['Afiliacion']['monto_maximo'] = "";   
                                $this->data['Afiliacion']['lim_ob'] = "";   

                                



								break;
                            case 'st':
								$validacion_formato = 'movistarPostpago';
								break;    
							case 'sd':
								$validacion_formato='digitel';
								break;
							}

							$this->Afiliacion->validate['numero_cuenta'] = array(
									'requerido'=> array(
									'rule'=>'notEmpty'
									),
                                'formato_servicio'=> array(
								'rule'=>$validacion_formato
								)
							);


//                            if($this->data['Afiliacion']['tipo_operacion'] != 'sm' && $this->data['Afiliacion']['tipo_servicio'] != "stm"){

                            if($this->data['Afiliacion']['tipo_operacion'] != 'sm' && $this->data['Afiliacion']['tipo_servicio'] != "stm"){


								$validarNumero = $this->obtener_beneficiario($this->data['Afiliacion']['tipo_operacion'],$this->data['Afiliacion']['numero_cuenta'],2,$this->data['Afiliacion']['tipo_servicio']);
								if($validarNumero != ''){
									if ($validarNumero== 4656 || $validarNumero== 4668 || $validarNumero== 4684 || $validarNumero== 'V010'){
															$errorMovistar = _m('servicio.noexiste',null,true);
									}else if ($validarNumero== 4689 || $validarNumero== 4690 || $validarNumero== 4691 || $validarNumero== '0999' || $validarNumero== '0080'){
									
										$errorMovistar = _m('servicio.nocomunicacion',null,true);
									}else if ($validarNumero== 4614 || $validarNumero== 'V006' || $validarNumero== 'V007' || $validarNumero== 'V008'){

										$errorMovistar = _m('servicio.invalido',null,true);
									}else if ($validarNumero== 4613 || $validarNumero== 4603 || $validarNumero== 4615 || $validarNumero== 4630 || $validarNumero== 4655 || $validarNumero== 4658 || $validarNumero== '46N7'){

										$errorMovistar = _m('servicio.valor.invalido',null,true);
									}else if ($validarNumero== '46P4' || $validarNumero== '46P9'){

										$errorMovistar = _m('operacion.excedido',null,true);
									}else{

										$errorMovistar = _m('error.fatal',null,true);
									}
									$this->Afiliacion->validationErrors['numero_cuenta'] = $errorMovistar;
								}  
							} 
                        }else{
							$this->Afiliacion->validate['numero_cuenta'] = array(
							    'requerido'=> array(
								'rule'=>'notEmpty'
							    )
						    );
						    $this->Afiliacion->validate['tipo_servicio'] = array(
								'requerido'=> array(
								'rule'=>'notEmpty'
							    )
						    );
						    $montoMinimo=formatea_monto_fromWs(_p('recarga_minima_inter_post'));
                            $montoMaximo=formatea_monto_fromWs(_p('recarga_maxima_inter_post'));
						}
						break;
					case 'r': // Validaciones para recargas de servicios
						if($this->data['Afiliacion']['tipo_operacion']!='si'){														
	                        switch($this->data['Afiliacion']['tipo_operacion']){
	                            case 'sc':
	                                $validacion_formato = 'cantv';
	                                $montoMinimo=formatea_monto_fromWs(_p('recarga_minima'));
	                                $montoMaximo=formatea_monto_fromWs(_p('recarga_maxima'));
	                                break;
	                            case 'sm':
	                                $validacion_formato = 'movilnet';
	                                $montoMinimo=formatea_monto_fromWs(_p('recarga_maxima_movilnet'));
	                                $montoMaximo=formatea_monto_fromWs(_p('recarga_maxima_movilnet'));
	                                break;
	                            case 'sd':
									$montoMinimo=formatea_monto_fromWs($limiteDigMov['minimo_digitel_php']);
	                                $montoMaximo=formatea_monto_fromWs($limiteDigMov['maximo_digitel_php']);
									$validacion_formato='digitel';
	                                break;
	                            case 'st':
	                                $validacion_formato = 'movistarPrepago';
	                                $montoMinimo=formatea_monto_fromWs($limiteDigMov['minimo_movistar_php']);
	                                $montoMaximo=formatea_monto_fromWs($limiteDigMov['maximo_movistar_php']);
	                                break;  
	                            case 'sv':
	                                break; 
							}
				            if($this->data['Afiliacion']['tipo_operacion']!='sv'){ 
				                $this->Afiliacion->validate['numero_cuenta'] = array(
				                   'requerido'=> array(
				                   'rule'=>'notEmpty'
				                   ),
				                   'formato_servicio'=> array(
				                   'rule'=>$validacion_formato
				                  )
				                );  
				            }            
		
							// $this->Afiliacion->validate['monto_maximo'] = array(
							// 	'recargaMinima'=>array(
							// 		'rule'=>'recargaMinima'
							// 	),
							// 	'recargaMaxima'=>array(
							// 		'rule'=>'recargaMaxima'
							// 	),
							// );
					    }else{
							$montoMinimo=formatea_monto_fromWs(_p('recarga_minima_inter_pre'));
         					$montoMaximo=formatea_monto_fromWs(_p('recarga_maxima_inter_pre'));
							$this->Afiliacion->validate['numero_cuenta'] = array(
								'requerido'=> array(
								'rule'=>'notEmpty'
								)
							);
						    $this->Afiliacion->validate['tipo_servicio'] = array(
								'requerido'=> array(
								'rule'=>'notEmpty'
								)
							);
						}
						break;
					case 'i': // Validaciones para impuestos nacionales/aduanales
						$this->Afiliacion->validate['rif'] = array(
							'requerido'=> array(
							'rule'=>'notEmpty'
							)
						);					
						$this->Afiliacion->validate['nombre_beneficiario'] = array(
							'requerido'=> array(
							'rule'=>'notEmpty'
							)
						);					
						$this->Afiliacion->validate['email'] = array(
							'requerido'=> array(
							'rule'=>'notEmpty'
							)
						);					
						break;
                    case 'd': // Validaciones para transferencias moneda extranjera
						if($this->data['Afiliacion']['tipo_operacion']=='t'){
                            $this->Afiliacion->validate['numero_cuenta']['numero'] = array(
                                                            'rule'=>'/(?:^0171\d{16}$)|(?:^\d{10}$)/'
                            );
						}
						break;
					case 'c':
						if($this->data['Afiliacion']['tipo_afiliacion']=='c'){
							if($this->data['Afiliacion']['tipo_operacion']=='ce'){
	                            $this->Afiliacion->validate['cedula'] = array(
	                            	'requerido'=> array(
										'rule'=>'notEmpty'
									)
	                            );
	                            if(empty($this->Afiliacion->validate['cedula'])){
	                            	$this->Afiliacion->validationErrors['cedula'] = 'requerido';
	                            }
							}elseif($this->data['Afiliacion']['tipo_operacion']=='cea'){
								$this->Afiliacion->validate['numero_contrato'] = array(
	                            	'requerido'=> array(
										'rule'=>'notEmpty'
									)
	                            );
	                            if(empty($this->Afiliacion->validate['numero_contrato'])){
	                            	$this->Afiliacion->validationErrors['numero_contrato'] = 'requerido';
	                            }
							}
							if($this->Afiliacion->checkAlias($usuario['id'],$this->data['Afiliacion']['alias'])){
								$this->Afiliacion->validationErrors['alias'] = 'alias_nodisponible';
							}
							var_dump($this->data['Afiliacion']);
							die();


							if(empty($this->Afiliacion->validationErrors)){
								$this->Session->write('afiliaciones.crear', $this->data);
								$this->redirect('crear_confirmacion');
							}
						} 
					  	break;  
				} //FIN DEL SWITCH
        }   //FIN DEL IF LINEA 427



		
        if(!in_array($this->data['Afiliacion']['tipo_afiliacion'], array('s','r','i','c'))){

            switch($this->data['Afiliacion']['tipo_operacion']){
                case 't':
					$array_validacion = array('alias', 'numero_cuenta', 'email');
					break;
				case 'ob':
					if($this->data['Afiliacion']['destino']=='c'){
						$array_validacion = array('alias', 'numero_cuenta');
					}else{
						$array_validacion = array('alias');
					}                                            
					break;
				case 'tob':
					if($this->data['Afiliacion']['destino']=='c'){
						$array_validacion = array('alias', 'numero_cuenta', 'nombre_beneficiario', 'tipo_cedula', 'cedula', 'emailBeneficiario');
					}else{
						$array_validacion = array('alias', 'nombre_beneficiario', 'tipo_cedula', 'cedula', 'emailBeneficiario');
					}                                            
					break;         
            }
        }else if(in_array($this->data['Afiliacion']['tipo_afiliacion'], array('i'))){

            if($this->data['Afiliacion']['tipo_operacion']=='p'){
                $array_validacion = array('alias');
            }else{
                $array_validacion = array('alias', 'nombre_beneficiario', 'tipo_cedula', 'cedula', 'emailBeneficiario');
            }                            
        }else{
        	
    		if($this->data['Afiliacion']['tipo_afiliacion']=='s' && $this->data['Afiliacion']['tipo_operacion']=='sd' ){
    			$this->data['Afiliacion']['monto'] =$this->data['Afiliacion']['monto_limite'];
            	$array_validacion = array('numero_cuenta');
            }else{
            	if ($this->data['Afiliacion']['tipo_afiliacion']!='c') {
            		$array_validacion = array('numero_cuenta', 'monto_maximo');
            	}
            }	
        }

       
        if(isset($usuario['rif']) || ($this->data['Afiliacion']['tipo_afiliacion'] == 'i' || $this->data['Afiliacion']['tipo_afiliacion']== 'd')){
            $this->data['Afiliacion']['monto_maximo'] = NULL;
        } else {

            if( !empty($this->data['Afiliacion']['monto_maximo']) ){
                if($this->data['Afiliacion']['tipo_afiliacion'] == 'h'){
                     $array_validacion = array_merge($array_validacion, array('nombre'));
                }else{

                    if($this->data['Afiliacion']['tipo_operacion'] != 'sv'){
                       $array_validacion = array_merge($array_validacion, array('monto_maximo'));
                    }else{
                        $array_validacion = array_merge($array_validacion, array('nombre'));
                    }
                } 
            } else {
            	if($this->data['Afiliacion']['tipo_afiliacion'] != 'c'){
            		$this->data['Afiliacion']['monto_maximo'] = $this->data['Afiliacion']['monto_limite'];
            	}
             }
        }

        if(isset($usuario['rif']) && (in_array($this->data['Afiliacion']['tipo_afiliacion'], array('s','r')))){
            $this->data['Afiliacion']['monto_maximo'] = $this->data['Afiliacion']['monto_limite'];
        }

        $swSimple = 0;
        if($this->data['Afiliacion']['tipo_operacion'] == 'sv'){
              $swSimple = 1;
        }  

        

		if($this->Afiliacion->validates(array( 'fieldList'=>$array_validacion)) || $swSimple == 1 || $this->data['Afiliacion']['tipo_operacion']=='sm'){


			// Para verificar si es una cuenta diferente , proceso de modificacion
			if($swSimple == 1){
				$this->Afiliacion->validationErrors = array();
			}  

			if($this->data['Afiliacion']['tipo_operacion']=='sm'){
				$this->Afiliacion->validationErrors = array();
			}  

			$flagprod_modificado = true;
			$flagalias_modificado = true;

			if(!empty($this->data['Afiliacion']['id'])){
				$old_data = $this->Afiliacion->buscar($usuario['id']);
				$old_data['Afiliacion']['alias'] = ($old_data['Afiliacion']['alias']!="") ? desencriptar($old_data['Afiliacion']['alias']) : '';
				if($this->data['Afiliacion']['tipo_operacion']=='sd'){
					$flagprod_modificado = ($this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['numero_cuenta']!=$old_data['Afiliacion']['codigo_telefono'].desencriptar($old_data['Afiliacion']['numero_cuenta']));
				}else{
					if($this->data['Afiliacion']['tipo_afiliacion']=='t' && in_array($this->data['Afiliacion']['tipo_operacion'], array('ob','tob'))){
						if($this->data['Afiliacion']['destino']=='c'){
							$flagprod_modificado = ($this->data['Afiliacion']['numero_cuenta']!=desencriptar($old_data['Afiliacion']['numero_cuenta']));
						}else{
							$flagprod_modificado = ($this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['telefono']!=desencriptar($old_data['Afiliacion']['numero_cuenta']));
						}
					}else{
						$flagprod_modificado = ($this->data['Afiliacion']['numero_cuenta']!=desencriptar($old_data['Afiliacion']['numero_cuenta']));
					}                                            
				}						
				$flagalias_modificado = ($this->data['Afiliacion']['alias']!=$old_data['Afiliacion']['alias']);
			}	







			if($this->data['Afiliacion']['alias']!="" && $flagalias_modificado
				&& $this->Afiliacion->checkAlias($usuario['id'],$this->data['Afiliacion']['alias'])){
				$this->Afiliacion->validationErrors['alias'] = 'alias_nodisponible';
			} else if(isset($this->data['Afiliacion']['tipo_cedula']) && !$this->Activo->validarRIF($this->data['Afiliacion']['tipo_cedula'],$this->data['Afiliacion']['cedula'],$this->data['Afiliacion']['tipo_afiliacion'])){
				$this->Afiliacion->validationErrors['cedula'] = _m('valor.invalido', 'el número de identificación');
			} else if(isset($this->data['Afiliacion']['tipo_cedula']) && !$this->Activo->validarIdentificacion($this->data['Afiliacion']['tipo_cedula'],$this->data['Afiliacion']['cedula'],$this->data['Afiliacion']['tipo_operacion'])){
				$this->Afiliacion->validationErrors['cedula'] = _m('identificacion.igualcliente',array('la cédula/rif del beneficiario'));
			} else{
				$error=0;
				if($this->data['Afiliacion']['tipo_afiliacion'] == 't' && $this->data['Afiliacion']['tipo_operacion'] == 't'){
					$productoValidar = substr($this->data['Afiliacion']['numero_cuenta'],-10);
					$valorCuenta= array('NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 'NROPARAM' => 2);



					$datos = $this->Activo->validarProducto($valorCuenta);
					$error = intval($datos['NIBPF3010Return']['error']['ERNU01']);

					// Verifica si hubo un error en las validaciones anteriores
					if($error != 0){
						$this->Afiliacion->validationErrors['numero_cuenta'] = _m('valor.invalido', 'la cuenta a afiliar');
					} else {
						if(empty($usuario['tipo_cedula'])){
							$tipo_id = $usuario['tipo_cedula'];
							$nro_id = desencriptar($usuario['cedula']);
						} else {
							$tipo_id = $usuario['tipo_rif'];
							$nro_id = desencriptar($usuario['rif']);								
						}

						$this->data['Afiliacion']['moneda'] = trim($datos['NIBPF3010Return']['NIBPF3010']['MONEDA']);
						$this->data['Afiliacion']['tipo_producto'] = trim($datos['NIBPF3010Return']['NIBPF3010']['INTFIL01']);
						if(trim($datos['NIBPF3010Return']['NIBPF3010']['MONEDA'])!='VEB') {
							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('afiliacion.nopermitida');
						}

						else if(strtolower(trim($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']))==strtolower($tipo_id.str_pad($nro_id,9,'0',STR_PAD_LEFT))){
							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('producto.propio');
						} 
						else if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='A' && $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='T'){
							if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
								$this->Afiliacion->validationErrors['numero_cuenta'] = _m('cuenta.controlada', 'con la cuenta del beneficiario');
							}
							else{
								$this->Afiliacion->validationErrors['numero_cuenta'] = _m('cuenta.inactiva', 'la cuenta del beneficiario');
							}
						}

						// Completar el tamaño de la cuenta
						if(10==strlen($this->data['Afiliacion']['numero_cuenta'])){
							$res = $this->Activo->completaCuenta($this->data['Afiliacion']['numero_cuenta']);
							if($res){
								$this->data['Afiliacion']['numero_cuenta'] = str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT);
							} else {
								$this->Afiliacion->validationErrors['numero_cuenta'] = _m('valor.invalido', 'el número de cuenta');
							}
						}

						$cedula = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],1,9);								

						// Busca los datos del producto
						$this->data['Afiliacion']['tipo_cedula'] = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],0,1);

						if($this->data['Afiliacion']['tipo_cedula'] == 'G' || $this->data['Afiliacion']['tipo_cedula'] == 'J'){
							$this->data['Afiliacion']['nombre_beneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
						}else{
							$this->data['Afiliacion']['nombre_beneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'].' '.$datos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'];
						}

						$this->data['Afiliacion']['cedula'] = $cedula;							
					}
				} else if($this->data['Afiliacion']['tipo_afiliacion']=='t' && $this->data['Afiliacion']['tipo_operacion'] != 't'){
					if($this->data['Afiliacion']['banco']==''){
						$this->data['Afiliacion']['banco']=substr($this->data['Afiliacion']['numero_cuenta'], 0, 4);
						$this->data['Afiliacion']['bancoDesc'] = $bancos[substr($this->data['Afiliacion']['numero_cuenta'], 0, 4)]; 
					}else{                                               
						$this->data['Afiliacion']['bancoDesc'] = $bancos[$this->data['Afiliacion']['banco']]; 
					}
				} else if($this->data['Afiliacion']['tipo_afiliacion'] == 'p' && $this->data['Afiliacion']['tipo_operacion'] != 't'){


					$tdc = str_pad($this->data['Afiliacion']['numero_cuenta'],20,'0',STR_PAD_RIGHT);
					$valorTarjeta = array('TNROTARJ' => $tdc);
                    

					$datos = $this->Activo->validarBIN($valorTarjeta);
					$error = intval($datos['NIB090501Return']['error']['ERNU01']);

					if($error == 9986){
						$this->Afiliacion->validationErrors['numero_cuenta'] = _m('numero.bin');
					} else {
						if(trim(substr($datos['NIB090501Return']['NIB090501']['TBANCO'],3))==171){
							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('tdc.activo');
						} else {
							$this->data['Afiliacion']['banco'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
							$this->data['Afiliacion']['numeroBanco'] = trim(substr($datos['NIB090501Return']['NIB090501']['TBANCO'],3));
						}
					}
				} else if($this->data['Afiliacion']['tipo_afiliacion'] == 'p' && $this->data['Afiliacion']['tipo_operacion'] == 't'){

					$productoValidar = $this->data['Afiliacion']['numero_cuenta'];

					$valorTarjeta = array('NROTDC' => $this->data['Afiliacion']['numero_cuenta'], 'NROPARAM' => 4);
					//$datos = $this->Activo->validarProducto($valorTarjeta);
					//$error = $datos['NIBPF3010Return']['error']['ERNU01'];
                    $error = 0; 

					if($error != 0){
						$this->Afiliacion->validationErrors['numero_cuenta'] = _m('valor.invalido', 'el número de tarjeta');
					} else {

						$tipo_id = $this->data['Afiliacion']['tipo_cedula'];
						$nro_id = $this->data['Afiliacion']['cedula'];

						/*if(empty($usuario['tipo_cedula'])){
							$tipo_id = $usuario['tipo_cedula'];
							$nro_id = desencriptar($usuario['cedula']);
						} else {
							$tipo_id = $usuario['tipo_rif'];
							$nro_id = desencriptar($usuario['rif']);								
						}*/

						/*if(strtolower(trim($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']))==strtolower($tipo_id.str_pad($nro_id,9,'0',STR_PAD_LEFT))){
							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('producto.propio');
						} elseif(empty($usuario['rif']) && (trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCEP' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCEH' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCCP' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCCH')) {

							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('afiliacion.nopermitida');
						} elseif(!empty($usuario['rif']) && trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCEH') {

							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('afiliacion.nopermitida');
						} elseif($datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='01' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='02' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='03' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='06' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='08'){
							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('cuenta.inactiva', 'la tarjeta');
						} 
						*/

						$this->data['Afiliacion']['numeroBanco'] = strval('0171');
						$this->data['Afiliacion']['banco'] = $bancos[$this->data['Afiliacion']['numeroBanco']];


						//$cedula = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],1,9);

                        $cedula = $nro_id;
                        $tipo_cedula = $tipo_id;
                        
						// Busca los datos del producto
						/*$this->data['Afiliacion']['tipo_cedula'] = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],0,1);

						if($this->data['Afiliacion']['tipo_cedula'] == 'G' || $this->data['Afiliacion']['tipo_cedula'] == 'J'){
							$this->data['Afiliacion']['nombre_beneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
						}else{
							$this->data['Afiliacion']['nombre_beneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'].' '.$datos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'];
						}*/
						$this->data['Afiliacion']['cedula'] = $cedula;	
						$this->data['Afiliacion']['tipo_cedula'] = $tipo_id;

					}
				} else if($this->data['Afiliacion']['tipo_afiliacion']=='t' && $this->data['Afiliacion']['tipo_operacion'] != 't'){
						if(!isset($this->data['Afiliacion']['banco'])){
							$this->data['Afiliacion']['banco']=substr($this->data['Afiliacion']['numero_cuenta'], 0, 4);
							$this->data['Afiliacion']['bancoDesc'] = $bancos[substr($this->data['Afiliacion']['numero_cuenta'], 0, 4)]; 
						}else{                                               
							$this->data['Afiliacion']['bancoDesc'] = $bancos[$this->data['Afiliacion']['banco']]; 
						}

				}else if( $this->data['Afiliacion']['tipo_afiliacion'] == 'p' && $this->data['Afiliacion']['tipo_operacion'] != 't'){

                        //die("Diferente a T"); 


						$tdc = str_pad($this->data['Afiliacion']['numero_cuenta'],20,'0',STR_PAD_RIGHT);
						$valorTarjeta = array('TNROTARJ' => $tdc);
						$datos = $this->Activo->validarBIN($valorTarjeta);
						$error = intval($datos['NIB090501Return']['error']['ERNU01']);
						if($error == 9986){
							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('numero.bin');
						} else {
							if(trim(substr($datos['NIB090501Return']['NIB090501']['TBANCO'],3))==171){
								$this->Afiliacion->validationErrors['numero_cuenta'] = _m('tdc.activo');
							}else{
								$this->data['Afiliacion']['banco'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
								$this->data['Afiliacion']['numeroBanco'] = trim(substr($datos['NIB090501Return']['NIB090501']['TBANCO'],3));
							}								
						}
				} else if($this->data['Afiliacion']['tipo_afiliacion'] == 'p' && $this->data['Afiliacion']['tipo_operacion'] == 't'){

						$productoValidar = $this->data['Afiliacion']['numero_cuenta'];
						$valorTarjeta = array('NROTDC' => $this->data['Afiliacion']['numero_cuenta'], 'NROPARAM' => 4);
						$datos = $this->Activo->validarProducto($valorTarjeta);
						$error = $datos['NIBPF3010Return']['error']['ERNU01'];

						if($error != 0){
							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('valor.invalido', 'el número de tarjeta');
						} else {
							if(empty($usuario['tipo_cedula'])){
								$tipo_id = $usuario['tipo_cedula'];
								$nro_id = desencriptar($usuario['cedula']);
							} else {
								$tipo_id = $usuario['tipo_rif'];
								$nro_id = desencriptar($usuario['rif']);								
							}
							if(strtolower(trim($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']))==strtolower($tipo_id.str_pad($nro_id,9,'0',STR_PAD_LEFT))){
								$this->Afiliacion->validationErrors['numero_cuenta'] = _m('producto.propio');
							} elseif(empty($usuario['rif']) && (trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCEP' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCEH' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCCP' || trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCCH')) {
									$this->Afiliacion->validationErrors['numero_cuenta'] = _m('afiliacion.nopermitida');
							} elseif(!empty($usuario['rif']) && trim($datos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE1'])=='TCEH') {
								$this->Afiliacion->validationErrors['numero_cuenta'] = _m('afiliacion.nopermitida');
							} elseif($datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='01' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='02' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='03' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='06' && $datos['NIBPF3010Return']['NIBPF3010']['STSTDC']!='08'){
									$this->Afiliacion->validationErrors['numero_cuenta'] = _m('cuenta.inactiva', 'la tarjeta');
							} 

							$this->data['Afiliacion']['numeroBanco'] = strval('0171');
							$this->data['Afiliacion']['banco'] = $bancos[$this->data['Afiliacion']['numeroBanco']];
							$cedula = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],1,9);

							// Busca los datos del producto
							$this->data['Afiliacion']['tipo_cedula'] = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],0,1);

							if($this->data['Afiliacion']['tipo_cedula'] == 'G' || $this->data['Afiliacion']['tipo_cedula'] == 'J'){
								$this->data['Afiliacion']['nombre_beneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
							}else{
								$this->data['Afiliacion']['nombre_beneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'].' '.$datos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'];
							}
							$this->data['Afiliacion']['cedula'] = $cedula;							
						}


						//inicio validacion de afilicacion tarjeta prepagada cash
				} else if($this->data['Afiliacion']['tipo_afiliacion'] == 'h' && $this->data['Afiliacion']['tipo_operacion'] == 'ht'){
						$swValidarCedula = 0;
						$cedulaB = $this->data['Afiliacion']['cedula'];
						$tipo_documento =  $this->data['Afiliacion']['tipo_cedula'];

						$datosBas = $this->Activo->invoca(
							'NIBPF3010',
							array(
								'req'=>array(
									'NACIONALIDAD'=>$tipo_documento,
									'NROIDENTIFICACION'=>str_pad($cedulaB,12,'0',STR_PAD_LEFT),
									'NROPARAM'=>'1'
								)
							)
						);
						$documentoTitular= desencriptar($usuario['cedula']);

						if($this->Session->read('afiliaciones.proceso')== 'i'){
							if($cedulaB == $documentoTitular){
								$this->Afiliacion->validationErrors['cedula'] = _m('afi.cedula.titular');
								$swValidarCedula = 1;  
							}else{
								//se elimino la validación de existir el cliente creado en el core
								/*if(empty($datosBas['NIBPF3010Return']['NIBPF3010']['NROIDENTIFICACION'])){
									$this->Afiliacion->validationErrors['cedula'] = _m('afi.benef.noexistente');
									$swValidarCedula = 1;
								}else{       
								*/	
								$valTarjeta = $this->data['Afiliacion']['tarjetatpc'];	
								if($this->Afiliacion->checkCedulaTarjeta($usuario['id'],$cedulaB,'h',$valTarjeta)){
									$this->Afiliacion->validationErrors['cedula'] = _m('afi.tpc.existente');
									$swValidarCedula = 1;
								}    
								//}
							}
						}  

  
						if($swValidarCedula == 0){
							if($this->Session->read('afiliaciones.proceso')== 'i'){
								$cedulaBeneficiario = str_pad($cedulaB, 12, "0", STR_PAD_LEFT);
								//Validar tarjeta con credicard
								$codiEstatus = 0;
								$valTarjeta = $this->data['Afiliacion']['tarjetatpc'];
								$this->Session->write('afiliaciones.nroTarjetaTPC',$valTarjeta);
								$this->data['Afiliacion']['numero_cuenta'] = $valTarjeta;
								$this->data['Afiliacion']['numTarjeta'] = substr($valTarjeta,0,4) . "****" . substr($valTarjeta,16-4,4);
							}
	
							$apellido = trim($datosBas['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO']);
							if(empty($apellido)){
								$nombre = $this->data['Afiliacion']['nombre_beneficiario'];
							}else{	
								$nombre = trim($datosBas['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'])." " .trim($datosBas['NIBPF3010Return']['NIBPF3010']['SEGUNDOAPELLIDO'])." " .trim($datosBas['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'])." " .trim($datosBas['NIBPF3010Return']['NIBPF3010']['SEGUNDONOMBRE']);
							}

 

							$res = $this->Credicard->consultarCuenta($cedulaBeneficiario); 
							if($res == 1){
								$swValidar = 1;
								$valTarjeta = 1;
								$ruta = 'error_critico/'.$swValidar;
								$this->redirect($ruta);
							}


							if($this->Session->read('afiliaciones.proceso')== 'm'){
								$valTarjeta = $this->Session->read('afiliaciones.nroTarjetaMod');
								$this->data['Afiliacion']['numero_cuenta'] = $valTarjeta;
							}
							$this->data['Afiliacion']['numero_cuenta'] =$valTarjeta;
						}else{
							$this->data['Afiliacion']['numero_cuenta'] = $cedulaB;
							$nombre = $this->data['Afiliacion']['nombre_beneficiario'];
							$this->data['Afiliacion']['cedula'] = '';
						} 	//  fin if($this->Session->read('afiliaciones.proceso')== 'i'){

						$this->data['Afiliacion']['nombre_beneficiario'] = $nombre;
						$this->data['Afiliacion']['monto_maximo'] = NULL;
						//fin inicio validacion de afilicacion tarjeta prepagada cash  
				} else if($this->data['Afiliacion']['tipo_afiliacion'] == 'r' && $this->data['Afiliacion']['tipo_operacion']=='sv'){

					if(!empty($usuario['tipo_rif'])){  
						$this->data['Afiliacion']['monto_maximo'] = NULL;
					} 

					$swValidarContrato = 0;
					$nroContrato = $this->data['Afiliacion']['numero_cuenta'];

					if($this->Session->read('afiliaciones.proceso')== 'i'){
						if($this->Afiliacion->checkContratoST($usuario['id'],$nroContrato,'sv')){
							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('afi.tpc.existente');
							$swValidarContrato = 1;
						}
						if($swValidarContrato == 0){ 
							$userSTV = Configure::read('Simple_TV.user');
							$passwordSTV = Configure::read('Simple_TV.pwd');
							$urlCustomer = Configure::read('Simple_TV.urlCustomer');

							$arrayContrato = array(
								'nroContrato'=>$nroContrato,
								'userSTV'=>$userSTV,
								'passwordSTV'=>$passwordSTV,
								'urlCustomer'=>$urlCustomer
							); 

							//$arrayContrato = array('nroContrato'=>$nroContrato);
							$res = $this->Simpletv->invocaCurlStv('validateUser', $arrayContrato);

							if(!$res){
								$codeError = 'S/C LV-0001';
								$mensaje = $this->Simpletv->mensajeErrorSTV_VTA($codeError);
								//$mensaje = _m('error.fatal') . ' [ S/C LV-0001 ]';
								$this->Afiliacion->validationErrors['numero_cuenta'] = $mensaje;
							}else{
								if($res->error > 0){
									$mensaje = $this->Simpletv->mensajeErrorSTV_VTA($res->codeError); 
									//if($res->codeError == 'CC-ME-0021' || $res->codeError == 'CC-MI-0021'){
									//    $this->Afiliacion->validationErrors['numero_cuenta'] = _m('afi.stv.noEncontrado');                     
									//}else{
									//    $mensaje = _m('error.fatal') . ' [ ' . $res->codeError . ' ]';
									//   $this->Afiliacion->validationErrors['numero_cuenta'] = $mensaje;
									//}
									$this->Afiliacion->validationErrors['numero_cuenta'] = $mensaje;

								} 
							} 
						}  
					}//  fin if($this->Session->read('afiliaciones.proceso')== 'm')
				}              

				//FIN Inicio Simple tv

				else if(($this->data['Afiliacion']['tipo_afiliacion'] == 'r' || $this->data['Afiliacion']['tipo_afiliacion'] == 's') && ($this->data['Afiliacion']['tipo_operacion']=='st')){
					if(!isset($this->data['Afiliacion']['nombre_beneficiario']) || empty($this->data['Afiliacion']['nombre_beneficiario'])){
						$this->Afiliacion->validationErrors['nombre_beneficiario'] = 'requerido';
					}
				}//Recarga MOVILNET
				else if(
					($this->data['Afiliacion']['tipo_afiliacion'] == 'r' || 
					$this->data['Afiliacion']['tipo_afiliacion'] == 's')
					&& ($this->data['Afiliacion']['tipo_operacion']=='sm')){
						if(!isset($this->data['Afiliacion']['nombre_beneficiario']) || empty($this->data['Afiliacion']['nombre_beneficiario'])){
							$this->Afiliacion->validationErrors['nombre_beneficiario'] = 'requerido';
						}
				}//Recarga Digitel
				else if(
					($this->data['Afiliacion']['tipo_afiliacion'] == 'r'  || 
					$this->data['Afiliacion']['tipo_afiliacion'] == 's')
					&& ($this->data['Afiliacion']['tipo_operacion']=='sd')){
						$this->Afiliacion->validate['nombre_beneficiario'] = array(
							'nombre_beneficiario'=>array(
							'rule'=>'requerido'
						)
						);                                                            

						// if($this->data['Afiliacion']['tipo_afiliacion']=='r'){     
						// 	if(empty($usuario['tipo_rif'])&&!$this->Afiliacion->montoMultiplo($this->data['Afiliacion']['monto_maximo'],_p('multiplo.digitel'))){
						// 		$this->Afiliacion->validationErrors['monto_maximo'] = 'montoMultiplo';
						// 	}
						// }
				} else if($this->data['Afiliacion']['tipo_afiliacion'] == 'i'){
					if ($this->data['Afiliacion']['tipo_operacion'] == 'p'){
						if($usuario['tipo_rif']){
							$datos_basicos = $this->Session->read('usuarios.nibpf3010');
							$this->data['Afiliacion']['nombre_beneficiario'] = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
						}else{
							$this->data['Afiliacion']['nombre_beneficiario'] = desencriptar($usuario['nombre']);  
						}
						$this->data['Afiliacion']['email']= desencriptar($usuario['email_principal']);
					}                                               

					if(!isset($this->data['Afiliacion']['tipo_cedula']) || !isset($this->data['Afiliacion']['cedula']) ||
						empty($this->data['Afiliacion']['cedula'])){
						$this->Afiliacion->validationErrors['cedula'] = 'requerido';
					}
				else if(!isset($this->data['Afiliacion']['nombre_beneficiario']) || empty($this->data['Afiliacion']['nombre_beneficiario'])){
					$this->Afiliacion->validationErrors['nombre_beneficiario'] = 'requerido';
				}
				else if($this->data['Afiliacion']['tipo_afiliacion'] != 'c' && !isset($this->data['Afiliacion']['email']) || empty($this->data['Afiliacion']['email'])){
					$this->Afiliacion->validationErrors['email'] = 'requerido';
				}
				else if($this->data['Afiliacion']['tipo_afiliacion'] != 'c' && isset($this->data['Afiliacion']['tipo_cedula']) && !$this->Activo->validarRIF($this->data['Afiliacion']['tipo_cedula'],$this->data['Afiliacion']['cedula'],$this->data['Afiliacion']['tipo_afiliacion'])){
						$this->Afiliacion->validationErrors['cedula'] = _m('valor.invalido', 'el RIF');
					}
					$this->data['Afiliacion']['numero_cuenta'] = $this->data['Afiliacion']['tipo_cedula'].$this->data['Afiliacion']['cedula'];
				}else if(
					$this->data['Afiliacion']['tipo_afiliacion'] == 'd' &&
					$this->data['Afiliacion']['tipo_operacion'] == 't'){
						$productoValidar = substr($this->data['Afiliacion']['numero_cuenta'],-10);
						$valorCuenta= array('NROCUENTA' => str_pad($productoValidar,12,'0',STR_PAD_LEFT), 'NROPARAM' => 2);
						$datos = $this->Activo->validarProducto($valorCuenta);
						$error = intval($datos['NIBPF3010Return']['error']['ERNU01']);

						// Verifica si hubo un error en las validaciones anteriores
						if($error != 0){
							$this->Afiliacion->validationErrors['numero_cuenta'] = _m('valor.invalido', 'la cuenta a afiliar');
						} else {
							if(empty($usuario['tipo_cedula'])){
								$tipo_id = $usuario['tipo_cedula'];
								$nro_id = desencriptar($usuario['cedula']);
							} else {
								$tipo_id = $usuario['tipo_rif'];
								$nro_id = desencriptar($usuario['rif']);								
							}

							$this->data['Afiliacion']['moneda'] = trim($datos['NIBPF3010Return']['NIBPF3010']['MONEDA']);
							$this->data['Afiliacion']['tipo_producto'] = trim($datos['NIBPF3010Return']['NIBPF3010']['INTFIL01']);
							if(trim($datos['NIBPF3010Return']['NIBPF3010']['MONEDA'])=='VEB') {
								$this->Afiliacion->validationErrors['numero_cuenta'] = _m('afiliacion.nopermitida');
							}
							else if(strtolower(trim($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS']))==strtolower($tipo_id.str_pad($nro_id,9,'0',STR_PAD_LEFT))){
								$this->Afiliacion->validationErrors['numero_cuenta'] = _m('producto.propio');
							} 
							else if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='A' && $datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']!='T'){

								if($datos['NIBPF3010Return']['NIBPF3010']['STSCUENTA']=='O') {
									$this->Afiliacion->validationErrors['numero_cuenta'] = _m('cuenta.controlada', 'con la cuenta del beneficiario');
								}
								else{
									$this->Afiliacion->validationErrors['numero_cuenta'] = _m('cuenta.inactiva', 'la cuenta del beneficiario');
								}
							}
							// Completar el tamaño de la cuenta
							if(10==strlen($this->data['Afiliacion']['numero_cuenta'])){
								$res = $this->Activo->completaCuenta($this->data['Afiliacion']['numero_cuenta']);
								if($res){
									$this->data['Afiliacion']['numero_cuenta'] = str_pad($res['EDL890101Return']['EDL890101']['e01ACC020'],20,'0',STR_PAD_LEFT);
								} else {
									$this->Afiliacion->validationErrors['numero_cuenta'] = _m('valor.invalido', 'el número de cuenta');
								}
							}

							$cedula = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],1,9);								

							// Busca los datos del producto
							$this->data['Afiliacion']['tipo_cedula'] = substr($datos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],0,1);
							if($this->data['Afiliacion']['tipo_cedula'] == 'G' || $this->data['Afiliacion']['tipo_cedula'] == 'J'){
								$this->data['Afiliacion']['nombre_beneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
							}else{
								$this->data['Afiliacion']['nombre_beneficiario'] = $datos['NIBPF3010Return']['NIBPF3010']['PRIMERNOMBRE'].' '.$datos['NIBPF3010Return']['NIBPF3010']['PRIMERAPELLIDO'];
							}
							$this->data['Afiliacion']['cedula'] = $cedula;							
						}
				}

				if($this->data['Afiliacion']['tipo_operacion']=='ob'){
					/** Para identificar si es un usuario juridico **/
					if($usuario['tipo_rif']){
						$this->data['Afiliacion']['tipo_cedula']= $usuario['tipo_rif'];
						$this->data['Afiliacion']['cedula']= desencriptar($usuario['rif']);
						$datos_basicos = $this->Session->read('usuarios.nibpf3010');
						$this->data['Afiliacion']['nombre_beneficiario'] = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
					} else {
						$this->data['Afiliacion']['tipo_cedula']= $usuario['tipo_cedula'];
						$this->data['Afiliacion']['cedula']= desencriptar($usuario['cedula']);
						$this->data['Afiliacion']['nombre_beneficiario'] = desencriptar($usuario['nombre']);
					}

					/** Para identificar si es un usuario juridico **/
					$this->data['Afiliacion']['email']= desencriptar($usuario['email_principal']);

					if ($this->data['Afiliacion']['tipo_afiliacion'] != 'i'){
						if($this->data['Afiliacion']['tipo_afiliacion']=='t'){
							if($this->data['Afiliacion']['banco']==''){
								$this->data['Afiliacion']['banco']=substr($this->data['Afiliacion']['numero_cuenta'], 0, 4);
								$this->data['Afiliacion']['bancoDesc'] = $bancos[substr($this->data['Afiliacion']['numero_cuenta'], 0, 4)]; 
							}else{                                               
								$this->data['Afiliacion']['bancoDesc'] = $bancos[$this->data['Afiliacion']['banco']]; 
							}                                                        
						} else{
							$this->data['Afiliacion']['bancoDesc'] = $datos['NIB090501Return']['NIB090501']['TDESBANCO'];
							$this->data['Afiliacion']['numeroBanco'] = trim(substr($datos['NIB090501Return']['NIB090501']['TBANCO'],3));
						}
					}
				}

				// Verifica si ya tiene afiliada la cuenta o el servicio
				if($this->data['Afiliacion']['tipo_afiliacion'] != 'c' &&	$this->data['Afiliacion']['tipo_afiliacion'] != 's' && $this->data['Afiliacion']['tipo_afiliacion'] != 'r' && $this->data['Afiliacion']['tipo_afiliacion'] != 'h' && $flagprod_modificado==true && 
						$this->Afiliacion->checkAfiliacion($usuario['id'],$this->data['Afiliacion']['numero_cuenta'])){
						if($this->data['Afiliacion']['tipo_afiliacion'] != 'i'){
							if($this->data['Afiliacion']['tipo_afiliacion'] != 'h'){
								$this->Afiliacion->validationErrors['numero_cuenta'] = 'producto_nodisponible';
								if($this->Session->read('afiliaciones.proceso')!='m'){
									$this->Afiliacion->validationErrors['numero_cuenta'] = 'producto_nodisponible'; 
								}
							}else{
								$this->Afiliacion->validationErrors['cedula'] = 'producto_nodisponible';
							} 
						}else{
							$this->Afiliacion->validationErrors['cedula'] = 'no_disponible';
						}
				}elseif(//MGGY Digitel                                                       
					$this->data['Afiliacion']['tipo_operacion'] == 'sd' && $flagprod_modificado==true){
						if(isset($this->data['Afiliacion']['codigo_telefono'])){
							if($this->Afiliacion->checkAfiliacionServicio($usuario['id'],$this->data['Afiliacion']['numero_cuenta'],$this->data['Afiliacion']['codigo_telefono'])){
									$this->Afiliacion->validationErrors['numero_cuenta'] = 'producto_nodisponible';
							}
						}else{
							if($this->Afiliacion->checkAfiliacionServicio($usuario['id'],$this->data['Afiliacion']['numero_cuenta'])){
								$this->Afiliacion->validationErrors['numero_cuenta'] = 'producto_nodisponible';
							}
						}                                                                                            
				}elseif(//MGGY Digitel                                                       
					$this->data['Afiliacion']['tipo_operacion'] == 'si' && $flagprod_modificado==true){
					if(isset($this->data['Afiliacion']['codigo_telefono'])){
						if($this->Afiliacion->checkAfiliacionServicio($usuario['id'],$this->data['Afiliacion']['numero_cuenta'],$this->data['Afiliacion']['codigo_telefono'])){
							$this->Afiliacion->validationErrors['numero_cuenta'] = 'producto_nodisponible';
						}
					}else{
						if($this->Afiliacion->checkAfiliacionServicio($usuario['id'],$this->data['Afiliacion']['numero_cuenta'])){
							$this->Afiliacion->validationErrors['numero_cuenta'] = 'producto_nodisponible';
						}
					}                                                                                            
				}elseif(//Movistar
					$this->data['Afiliacion']['tipo_operacion'] == 'st' && $flagprod_modificado==true){
					if(isset($this->data['Afiliacion']['codigo_telefono'])){
						if($this->Afiliacion->checkAfiliacionServicio($usuario['id'],$this->data['Afiliacion']['numero_cuenta'],$this->data['Afiliacion']['codigo_telefono'])){
							$this->Afiliacion->validationErrors['numero_cuenta'] = 'producto_nodisponible';
						}
					}else{
						if($this->Afiliacion->checkAfiliacionServicio($usuario['id'],$this->data['Afiliacion']['numero_cuenta'])){
							$this->Afiliacion->validationErrors['numero_cuenta'] = 'producto_nodisponible';
						}
					}                                                                                            
				}                            

				if(empty($this->Afiliacion->validationErrors)){           
					$this->Session->write('afiliaciones.crear', $this->data);
					$this->redirect('crear_confirmacion');
				}
			}
		}

	}



	$tipos = $this->Afiliacion->buscarTipos();
	unset($tipos['a']);
	if( !validarTransaccion(13) ){ // Verifica transferencias
		unset($tipos['t']);
	}
	if( !validarTransaccion(101071) ){ // Verifica transferencias moneda extranjera
		unset($tipos['d']);
	}
	if( !validarTransaccion(14) ){ // Verifica pagos TDC
		unset($tipos['p']);
	}
	if( !validarTransaccion(100010) ){ // Verifica Servicios Postpago
		unset($tipos['s']);
	}
	if( !validarTransaccion(100011) ){ // Verifica Servicios Recarga
		unset($tipos['r']);
	}
	if( !validarTransaccion(100035) || $usuario['tipo_cedula'] == 'P'){ // Verifica impuestos nacionales/aduanales
		unset($tipos['i']);
	}
	unset($tipos['m']);

	$this->layout = 'interna';
	$this->set('seccion_actual','afiliaciones.afiliacion_tdc');
	$this->set('tipos', $tipos);

	if(!empty($this->data['Afiliacion']['tipo_afiliacion'])){
		$this->set('subtipos', $this->Afiliacion->buscarSubTipos($this->data['Afiliacion']['tipo_afiliacion']));
	}

	if(!isset($this->data['Afiliacion']['nombre_beneficiario'])){
		$this->data['Afiliacion']['nombre_beneficiario']='';
	}

	$this->set('bancos', $bancos);
	$this->set('prefijos_telefonicos', $prefijos_telefonicos);
	$this->set('prefijos', $prefijos);
	$this->set('montoMinimo', $montoMinimo);
	$this->set('montoMaximo', $montoMaximo);                
	$this->set('subtipos_general', $this->Afiliacion->buscarSubTipos());

	$this->set('num_tarjeta', $this->data['Afiliacion']['numero_cuenta']);

	if(isset($this->data['Afiliacion']['numero_cuenta'])){
		$this->Session->write('afiliaciones.nroTarjetaMod', $this->data['Afiliacion']['numero_cuenta']);
	}	

	//MGGY Recargas y Pago Servicios
	$this->set('tiposServicios', $this->Afiliacion->buscarTiposServicios());

	if($usuario['tipo_rif']){
		$datos_basicos = $this->Session->read('usuarios.nibpf3010');
		$this->set('nombreCliente', $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB']);
		$this->set('rifCliente', desencriptar($usuario['rif']));
	} else{
		if($usuario['tipo_cedula'] != 'P'){
			$this->set('nombreCliente', desencriptar($usuario['nombre']));
			$rifCliente = $this->Activo->generaRIF($usuario['tipo_cedula'],desencriptar($usuario['cedula']));
			$this->set('rifCliente', substr($rifCliente,1));
		} else{
			$this->set('nombreCliente', "");
			$this->set('rifCliente', "");
		}
	}
}
        
        /**
	 * Acción para crear o modificar una afiliación de saldo activo
	 * @param string Indica si se está regresando de la confirmación
	 */
	function crear_saldo_activo($volver=null){
	
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(array(100020))) $this->redirect('/consultas/integral');

                $usuario = $this->Session->read('usuarios.datos');						
                
                if(!empty($this->data['Afiliacion'])){ // Verifica si llegan datos de la Vista 
                    if($this->Afiliacion->validates(array('fieldList'=>array('numero_cuenta','numero_tarjeta')))){
                        $this->Afiliacion->set($this->data);
                    }
                    $array_validacion = array();
                    if(empty($this->Afiliacion->validationErrors)){
                        $this->Session->write('afiliaciones.crear_saldo_activo', $this->data);
                        $this->redirect('crear_confirmacion_saldo_activo');
                    }
				}else if(isset($volver)) { // Sin datos de la Vista
                        $this->data = $this->Session->read('afiliaciones.crear_saldo_activo');
                        $id_afiliacion = $this->data['Afiliacion']['id'];
                        if($id_afiliacion){
                            $cuentas = $this->data['Afiliacion']['numero_cuenta'];
                        }
                        else{
                            $cuentas = $this->Activo->buscarInstrumentosSaldoActivoU(array('SAV','DDA','NOW'.'MMK'));
                        }
                        $nro_tarjeta = $this->data['Afiliacion']['numero_tarjeta'];
                }else{
                        //Busca las cuentas Disponibles para el servicio				                
                        $cuentas = $this->Activo->buscarInstrumentosSaldoActivoU(array('SAV','DDA','NOW','MMK'));
                }
                
                //Busca las tarjetas Disponibles para el servicio
                $tarjetas = $this->Activo->buscarInstrumentosSaldoActivo(array('CC&','NOW','DDA'));
                $nroCliente = $this->Session->read('NROCLIENTE');
                
                /* Busca las Afiliaciones a Pago Activo del Cliente*/
                $res = $this->Activo->invoca(
                        'ECC051001',
                        array(
                                'req'=>array(
                                        'e01PATCUN'=>$nroCliente,
                                        'h01OPECOD'=>'0005'											
                                )
                        )
                ); 
                /* Inicializa el listado de Afiliaciones */
                $arrData = array();
                if(!empty($res['ECC051001Return']['ECC051001']['ECC051001'])){
                	
                    $arrData = $res['ECC051001Return']['ECC051001']['ECC051001'];
                    normaliza($arrData);
                }                
                foreach($arrData as $list){
                    $aux =  $this->Activo->completaCuenta($list['e01PATACC']);
                    $index = str_pad($aux['EDL890101Return']['EDL890101']['e01ACC020'],20,0,STR_PAD_LEFT);
                    if(isset($cuentas[$index])){
                        unset($cuentas[$index]);
                    }
                }
                $this->set('cuentas', $cuentas);
                $this->set('tarjetas', $tarjetas);
		$this->set('seccion_actual','afiliaciones.afiliacion_saldo_activo');									
		$this->layout = 'interna';
	}

	/**
	 * Acción de confirmación de creación/modificación de la afiliación 
	 */
	function crear_confirmacion(){
        $validacion_tdc = $this->Session->read('seguridad.validar_tdc');
		$usuario = $this->Session->read('usuarios.datos');
		if(
                    (
                        !isset($usuario['rol_juridico_id']) ||
                        !in_array($usuario['rol_juridico_id'],array(3,5)) // No solicitar tc para operadores
                    ) &&
                    (
                        $validacion_tdc == null ||
                        !isset($validacion_tdc['validado']) ||
                        !$validacion_tdc['validado']
                    )
		){
                    $this->Session->write(
                            'seguridad.validar_tdc',
                            array(
                                    'url_aceptar'=>'/afiliaciones/listado',
                                    'layout'=>'interna',
                                    'titulo'=>'Afiliaciones',
                                    'pasos'=> array(),
                                    'paso_actual'=>0,
                                    'seccion_actual'=>'afiliaciones.afiliacion_tdc'
                            )
                    );
 
                    $this->redirect('/seguridad/validar_tdc');
		}


		if($this->Session->check('afiliaciones.crear')){
                    $this->layout = 'interna';
                    $usuario = $this->Session->read('usuarios.datos');
                    $this->set('juridico',$usuario['tipo_rif']);
                    $this->set('seccion_actual','afiliaciones.afiliacion_tdc');
                    $this->set('tipos', $this->Afiliacion->buscarTipos());
                    $this->set('subtipos', $this->Afiliacion->buscarSubTipos());
                    $this->set('servicios', $this->Afiliacion->buscarTiposServicios());
                    $this->set('bancos', $this->Activo->buscarBancos());
                    $this->data = $this->Session->read('afiliaciones.crear');



		} else {
                    $this->redirect('listado');
		}               
	}
        
        /**
	 * Acción de confirmación de creación/modificación de la afiliación saldo activo 
	 */
	function crear_confirmacion_saldo_activo(){		
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(array(100020))) $this->redirect('/consultas/integral');
            
            // Datos del Usuario
            $usuario = $this->Session->read('usuarios.datos');



            
            if(!empty($this->data['Confirmacion'])){
                $this->redirect('/afiliaciones/crear_resultado_saldo_activo');
            }



            
            if($this->Session->check('afiliaciones.crear_saldo_activo')){					
                    $this->layout = 'interna';
                    $this->data = $this->Session->read('afiliaciones.crear_saldo_activo');
                    $this->set('seccion_actual','afiliaciones.afiliacion_saldo_activo');
            } else {
                    $this->redirect('listado_saldo_activo');
            }
	}
        
	/**
	 * Acción para el resultado de la creación/modificación de la afiliación 
	 */
	function crear_resultado(){
                
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');
		$usuario = $this->Session->read('usuarios.datos');

		if(
                    (
                        !isset($usuario['rol_juridico_id']) ||
                        !in_array($usuario['rol_juridico_id'],array(3,5)) // No solicitar tc para operadores
                    ) &&
                    (
                        $validacion_tdc == null ||
                        !isset($validacion_tdc['validado']) ||
                        !$validacion_tdc['validado']
                    )
		){
                    $this->Session->write(
                            'seguridad.validar_tdc',
                            array(
                                    'url_aceptar'=>'/afiliaciones/listado',
                                    'layout'=>'interna',
                                    'titulo'=>'Afiliaciones',
                                    'pasos'=> array(),
                                    'paso_actual'=>0,
                                    'seccion_actual'=>'afiliaciones.afiliacion_tdc'
                            )
                    );

                    $this->redirect('/seguridad/validar_tdc');
		}


		if($this->Session->check('afiliaciones.crear')){	
               
			

                    $this->layout = 'interna';

                    $tipos = $this->Afiliacion->buscarTipos();
                    $subtipos = $this->Afiliacion->buscarSubTipos();

                    $this->set('seccion_actual','afiliaciones.afiliacion_tdc');	
                    $this->set('tipos', $tipos);
                    $this->set('subtipos', $subtipos);
                    $this->set('bancos', $this->Activo->buscarBancos());
                    $this->set('servicios', $this->Afiliacion->buscarTiposServicios());
                    $this->data = $this->Session->read('afiliaciones.crear');
                    $usuario = $this->Session->read('usuarios.datos');
                    $this->Session->delete('afiliaciones.crear');

                    // Si es juridico
                    if($usuario['tipo_rif']){
                        $this->data['Afiliacion']['tipo_rif'] = $usuario['tipo_rif'];
                        $this->data['Afiliacion']['rif'] = $usuario['rif'];
                    }

                    $this->set('juridico',$usuario['tipo_rif']);

                    $transaccion = ($this->data['Afiliacion']['tipo_afiliacion']=='t') ? 13 : 14;

                    if($this->data['Afiliacion']['tipo_afiliacion']=='s') {
                            $transaccion=100010;
                    }elseif($this->data['Afiliacion']['tipo_afiliacion']=='r') {
                            $transaccion=100011;
                    }elseif($this->data['Afiliacion']['tipo_afiliacion']=='i') {
                            $transaccion=100035;
                    }elseif($this->data['Afiliacion']['tipo_afiliacion']=='d') {
                            $transaccion=101071;
                    }elseif($this->data['Afiliacion']['tipo_afiliacion']=='h') {
                            $transaccion=102099;
                    }elseif($this->data['Afiliacion']['tipo_afiliacion']=='c') {
                            $transaccion=101102;
                    } 
                    //var_dump($this->data['Afiliacion']);


                    if($this->data['Afiliacion']['tipo_afiliacion']=='c') {
                        if($this->data['Afiliacion']['tipo_operacion']=='ce'){
                        	$numero =  $this->data['Afiliacion']['cedula'];
                        }elseif($this->data['Afiliacion']['tipo_operacion']=='cea'){
                        	$numero =  $this->data['Afiliacion']['numero_contrato'];
                        }
                     

                        if(!isset($this->data['Afiliacion']['cedula'])){
                        	$this->data['Afiliacion']['cedula'] = "";
                        }

                        unset($this->data['Afiliacion']['recarga_maxima']);
                        unset($this->data['Afiliacion']['recarga_minima']);
                        unset($this->data['Afiliacion']['monto_limite']);
						unset($this->data['Afiliacion']['lim_t']);
						unset($this->data['Afiliacion']['lim_ob']);
						unset($this->data['Afiliacion']['lim_tob']);
						unset($this->data['Afiliacion']['tipo_servicio']);
						unset($this->data['Afiliacion']['destino']);
						unset($this->data['Afiliacion']['codigo_telefono_movil']);
						unset($this->data['Afiliacion']['banco']);
                    }
                    
                    if($this->data['Afiliacion']['tipo_afiliacion']=='h') {
                        $numero = $this->Session->read('afiliaciones.nroTarjetaTPC'); 	
                        $this->Session->delete('afiliaciones.nroTarjetaTPC');
                    }else{
                    	if($this->data['Afiliacion']['tipo_afiliacion']!='c'){
                    		$numero =  $this->data['Afiliacion']['numero_cuenta'];	
                    	}
                    }

                    $this->data['Afiliacion']['numero_cuenta'] = $numero;

                    if($this->data['Afiliacion']['tipo_afiliacion'] == 's' && $this->data['Afiliacion']['tipo_operacion'] == 'sm'){
                    	$this->data['Afiliacion']['codigo_telefono_movil'] = null;
                    	$this->data['Afiliacion']['codigo_telefono'] = null;
                    }


                    $this->Afiliacion->set($this->data);
                    
                    
                    $flag_nuevo = !isset($this->Afiliacion->id) || $this->Afiliacion->id=='';			

                    $this->data['Error'] = !$this->Afiliacion->saveData($usuario['id']);

                    $descripcion=$this->data['Afiliacion']['tipo_afiliacion'];  


                    if(!$this->data['Error']){

                        if($flag_nuevo) {
                            $this->data['Afiliacion']['accion']='Afiliacion';
                            $codigo_sms = '47';					
                        } else {
                            $this->data['Afiliacion']['accion']='Modificacion';
                            $codigo_sms = '42';
                        }


                        // Email Options
                        $this->Email->smtpOptions = Configure::read('email.smtp');
                        $this->Email->delivery	= 'smtp';
                        $this->Email->from = Configure::read('email.from');
                        $this->Email->to = desencriptar($usuario['email_principal']);
                        $this->Email->subject = 'Notificacion Afiliacion';
                        $this->Email->sendAs = 'html';

                        // Informacion del correo
                        $this->Email->template	= 'afiliacion_producto';
                        $tipo_afiliacion = $subtipos[trim($this->data['Afiliacion']['tipo_afiliacion'])][$this->data['Afiliacion']['tipo_operacion']];
                        $tipo_accion = $this->data['Afiliacion']['accion'] . ' '. $tipos[trim($this->data['Afiliacion']['tipo_afiliacion'])];
                        $nombreBeneficiario= $this->data['Afiliacion']['nombre_beneficiario'];				
                        $numero_servicio= isset($this->data['Afiliacion']['codigo_telefono']) ? $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['numero_cuenta'] : ''.$this->data['Afiliacion']['numero_cuenta'];				
                        $tipo_afiliacion_s= $this->data['Afiliacion']['tipo_afiliacion'];
                        $tipo_operacion = $this->data['Afiliacion']['tipo_operacion'];


                        $this->set(compact('usuario','tipo_afiliacion', 'tipo_accion','nombreBeneficiario','numero_servicio','tipo_afiliacion_s','tipo_operacion'));

                        // Envio del correo
                        $this->Email->send();

                        $descripcion=$tipo_accion . ' ' . $tipo_afiliacion;

                        // Enviar por sms la notificación
                        if( isset($usuario['celular']) ){                                    
                            $this->Sms->enviar(
                                array(
                                    'numcel'=>desencriptar($usuario['celular']),
                                    'canal1'=>$codigo_sms
                                )
                            );
                        }

                        // Registra en bitacora
                        $this->Bitacora->registraBitacora(
                            $usuario['id'], 
                            $transaccion,
                            NULL, 
                            $numero_servicio, 
                            $this->data['Afiliacion']['monto_maximo'],
                            NULL,
                            $descripcion,
                            0
                        );
                        
                        //Guardar en Directorio del APP                                        
                        //Servicio de token
                        $dataToken = array();
                        $dataToken["username"] = Configure::read('Api.user');
                        $dataToken["password"] = Configure::read('Api.password');                        
                        $tokenApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/busLogin',"POST",$dataToken);
                        $tokenApp = json_decode(json_encode($tokenApp), true);                        
                        $token = $tokenApp['token_type'] . ' ' . $tokenApp['access_token'];                        
                        //Consulto el user id
                        $dataUserId = array();
                        $dataUserId["login"] = desencriptar($usuario['login']);
                        $userIdApp = $this->Activo->invocaCurl(Configure::read('Api.url').'/service/session/userInfo',"POST",$dataUserId,$token);
                        $userIdApp = json_decode(json_encode($userIdApp), true);
                        $codigoBanco = '';
                        $optionTrans = 1;
                        $moneda = "VEB";
                        $tipoProducto = "";
                        $cedula = ltrim($this->data['Afiliacion']['cedula'],'0');
                        $tipoCedula = $this->data['Afiliacion']['tipo_cedula'];
                        $servicio = $this->data['Afiliacion']['numero_cuenta'];
                        
                        switch($this->data['Afiliacion']['tipo_afiliacion']){
                            case 't':
                                $codigoBanco = substr($this->data['Afiliacion']['numero_cuenta'], 0, 4);
                                $moneda = $this->data['Afiliacion']['moneda'];
                                $tipoProducto = $this->data['Afiliacion']['tipo_producto'];                                
                                if($this->data['Afiliacion']['tipo_operacion']=='t') {
                                    $optionTrans=27;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='tob') {
                                    $optionTrans=28;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='ob') {
                                    $optionTrans=2;
                                }  
                                break;
                            case 'd':
                                $codigoBanco = substr($this->data['Afiliacion']['numero_cuenta'], 0, 4);
                                $moneda = $this->data['Afiliacion']['moneda'];
                                $tipoProducto = $this->data['Afiliacion']['tipo_producto'];
                                if($this->data['Afiliacion']['tipo_operacion']=='t') {
                                    $optionTrans=14;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='p' || $this->data['Afiliacion']['tipo_operacion']=='mt') {
                                    $optionTrans=13;
                                } 
                                break;
                            case 'p':
                                if($this->data['Afiliacion']['tipo_operacion']=='t') {
                                    $optionTrans=29;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='tob') {
                                    $optionTrans=30;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='ob') {
                                    $optionTrans=4;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='p' || $this->data['Afiliacion']['tipo_operacion']=='mt') {
                                    $optionTrans=3;
                                }   
                                break; 
                            case 's':
                                $cedula = desencriptar($usuario['cedula']);
                                $tipoCedula = $usuario['tipo_cedula'];
                                $codigoTelf = (!empty($this->data['Afiliacion']['codigo_telefono'])) ? $this->data['Afiliacion']['codigo_telefono'] : "";
                                $servicio = $codigoTelf . $this->data['Afiliacion']['numero_cuenta'];
                                
                                if($this->data['Afiliacion']['tipo_operacion']=='st') {
                                    $optionTrans=10;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='si') {
                                    $optionTrans=58;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='sd') {
                                    $optionTrans=8;
                                }
                                break;
                            case 'r':
                                $cedula = desencriptar($usuario['cedula']);
                                $tipoCedula = $usuario['tipo_cedula'];
                                $codigoTelf = (!empty($this->data['Afiliacion']['codigo_telefono'])) ? $this->data['Afiliacion']['codigo_telefono'] : "";
                                $servicio = $codigoTelf . $this->data['Afiliacion']['numero_cuenta'];
                                
                                if($this->data['Afiliacion']['tipo_operacion']=='st') {
                                    $optionTrans=9;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='sd') {
                                    $optionTrans=7;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='si') {
                                    $optionTrans=56;
                                }  
                                break; 
                            case 'i':
                                $cedula = substr($cedula, 0, -1);
                                if($this->data['Afiliacion']['tipo_operacion']=='p') {
                                    $optionTrans=23;
                                }elseif($this->data['Afiliacion']['tipo_operacion']=='t') {
                                    $optionTrans=45;
                                }
                                break;                          
                            default:
                                break;
                        }


                        //Guardo en BD
                        $dataRest = array();
                        $dataRest["channel_id"] = 1;
                        $dataRest["user_id"] = $userIdApp['user_id'];
                        $dataRest["identification_type"] = $tipoCedula;
                        $dataRest["identification"] = $cedula;
                        $dataRest["name"] = $this->data['Afiliacion']['nombre_beneficiario'];
                        $dataRest["email"] = $this->data['Afiliacion']['email'];
                        $dataRest["option_transaction_id"] = $optionTrans;
                        $dataRest["alias"] = $this->data['Afiliacion']['alias'];
                        $dataRest["payment_information"] = $servicio;
                        $dataRest["cod_banco"] = $codigoBanco;
                        $dataRest["currency"] = $moneda;
                        $dataRest["product_cod"] = $tipoProducto;



                        
                        $this->Activo->invocaCurl(Configure::read('Api.url').'/service/db/directory/insertAeL',"POST",$dataRest,$token);
                    }else{
                        $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            $transaccion,
                            NULL,
                            $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['numero_cuenta'],
                            $this->data['Afiliacion']['monto_maximo'],
                            NULL,
                            $descripcion,
                            1
                        );
                    }
			
		} else {
			
                    $this->redirect('listado');
		}
	}
        
        /**
	 * Acción para el resultado de la creación/modificación de la afiliación saldo activo 
	 */
	function crear_resultado_saldo_activo(){
                /* Para validar el acceso al modulo */
                if(!validarTransaccion(array(100020))) $this->redirect('/consultas/integral');            
            
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		$usuario = $this->Session->read('usuarios.datos');

		if($this->Session->check('afiliaciones.crear_saldo_activo')){	
                        $transaccion=100020;
			$this->layout = 'interna';						
                        //Obtiene Datos de la Afiliacion de Saldo Activo
			$this->set('seccion_actual','afiliaciones.afiliacion_saldo_activo');				
			$this->data = $this->Session->read('afiliaciones.crear_saldo_activo');
                        //Inicializa Indicador de Error
                        $this->data['Error'] = false;
                        //Obtiene los datos del Usuario
			$usuario = $this->Session->read('usuarios.datos');
			//Inicializa los datos de la afiliacion
			$this->Afiliacion->set($this->data);
                        // verifica 
                        $flag_nuevo = !isset($this->Afiliacion->id) || $this->Afiliacion->id=='';															  
                        if($flag_nuevo){
                            $cod_op = '0001';
                            $nro_cliente = '';
                            $cuenta = substr($this->data['Afiliacion']['numero_cuenta'], 10);
                        }else{
                            //Separa la cadena ID para obtener el nro cliente			
                            $dat = explode('|',$this->Afiliacion->id);
                            $nro_cliente = $dat[0];
                            $cod_op = '0002';
                            $cuenta = $this->data['Afiliacion']['numero_cuenta'];
                        }
                        
                        if( strlen($cuenta) > 10 ){
                            $cuenta10 = substr ($cuenta, 10);
                            $cuenta = $cuenta10;
                        }
                        
                        if (substr($this->data['Afiliacion']['numero_tarjeta'],0,1) == '0'  ) {
                        	$tarjeta = substr($this->data['Afiliacion']['numero_tarjeta'],10,10);
                        }else{
                        	$tarjeta = substr($this->data['Afiliacion']['numero_tarjeta'],0,16);
                        }
                        
                        $parametros = array(
                            'req'=>array(
                                    'e01PATCUN'=>$nro_cliente,
                                    'h01OPECOD'=>$cod_op,
                                    'e01PATACC'=>$cuenta,
                                    'e01PATTDC'=>$tarjeta,
                                    'e01PATSTA'=>'4'
                            )
                        );

                        $res = $this->Activo->invoca(
                                'ECC051001',
                                $parametros
                        );
                        
                        $this->data['Error'] = false;

                        if($res['ECC051001Return']['error']['ERNU01']){
                             $this->data['Error'] = true;
                             $descripcion = $res['ECC051001Return']['error']['ERDS01'];
                        }
                         
			if(!$this->data['Error']){
			
				if($flag_nuevo){                                        
					$this->data['Afiliacion']['accion']='Afiliacion';
					
				} else {                                        
    					$this->data['Afiliacion']['accion']='Modificacion';
                                        $this->Afiliacion->set('id',null);
                                        $this->Afiliacion->desafiliarSaldoActivo(encriptar($this->data['Afiliacion']['numero_cuenta']));
				}

                                //Almacena Fecha en Base de Datos
                                $valores = array();
                                $valores['Afiliacion']['nombre_beneficiario'] = $usuario['nombre'];
                                $valores['Afiliacion']['tipo_afiliacion'] = 'a';
                                $valores['Afiliacion']['tipo_operacion'] = 'mt';
                                $valores['Afiliacion']['tipo_cedula'] = $usuario['tipo_cedula'];
                                $valores['Afiliacion']['cedula'] = $usuario['cedula'];
                                $valores['Afiliacion']['email'] = $usuario['email_principal'];
                                $valores['Afiliacion']['usuario_id'] = $usuario['id'];
                                $valores['Afiliacion']['numero_contrato'] = encriptar($cuenta);
                                $valores['Afiliacion']['fecha_contrato'] = date('Ymd');
                                $valores['Afiliacion']['numero_cuenta'] = encriptar($cuenta);
                                
                                $this->Afiliacion->afiliarSaldoActivo($valores);
                                
				// Email Options
				$this->Email->smtpOptions = Configure::read('email.smtp');
				$this->Email->delivery = 'smtp';
				$this->Email->from = Configure::read('email.from');
				$this->Email->to = desencriptar($usuario['email_principal']);
				$this->Email->subject = 'Notificacion Afiliacion Saldo Activo Plus';
				$this->Email->sendAs = 'html';

				// Informacion del correo
				$this->Email->template	= 'afiliacion_saldo_activo';				
				$tipo_accion = $this->data['Afiliacion']['accion'].' al servicio '.'Saldo Activo Plus';
				$cta_respaldar = enmascara($this->data['Afiliacion']['numero_cuenta']);
				$ins_respaldo = enmascara($this->data['Afiliacion']['numero_tarjeta']);

				$this->set(compact('usuario','tipo_accion','cta_respaldar','ins_respaldo'));
			
				// Envio del correo
				$this->Email->send();
			
				$descripcion= $tipo_accion;

				// Registra en bitacora
				$this->Bitacora->registraBitacora(
					$usuario['id'], 
					$transaccion,
					$this->data['Afiliacion']['numero_cuenta'], 
					$this->data['Afiliacion']['numero_tarjeta'], 
					NULL,
					NULL,
					$descripcion,
					0
				);
			}else{
                            
				$this->Bitacora->registraBitacora(
					$usuario['id'], 
					$transaccion,
					$this->data['Afiliacion']['numero_cuenta'], 
					$this->data['Afiliacion']['numero_tarjeta'], 
					NULL,
					NULL,
					$descripcion,
					1
				);
			}
			
		} else {
			$this->redirect('listado_saldo_activo');
		}
	}                

	/**
	 * Acción para confirmar la eliminación de una afiliación 
	 */
	function eliminar_confirmacion(){
            
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');
		$usuario = $this->Session->read('usuarios.datos');

		if(
			(
				!isset($usuario['rol_juridico_id']) ||
				!in_array($usuario['rol_juridico_id'],array(3,5)) // No solicitar tc para operadores
			) &&
			(
				$validacion_tdc == null ||
				!isset($validacion_tdc['validado']) ||
				!$validacion_tdc['validado']
			)
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/afiliaciones/listado',
					'layout'=>'interna',
					'titulo'=>'Afiliaciones',
					'pasos'=> array(),
					'paso_actual'=>0,
					'seccion_actual'=>'afiliaciones.afiliacion_tdc'
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}

		if($this->Session->check('afiliaciones.eliminar')){
			$this->data = $this->Session->read('afiliaciones.eliminar');
			$this->layout = 'interna';		
			$this->set('seccion_actual','afiliaciones.afiliacion_tdc');
			$usuario = $this->Session->read('usuarios.datos');

			$this->set('juridico',$usuario['tipo_rif']);
			$this->set('tipos', $this->Afiliacion->buscarTipos());
			$this->set('subtipos', $this->Afiliacion->buscarSubTipos());
                        $this->set('servicios', $this->Afiliacion->buscarTiposServicios());
		} else {
			$this->redirect('listado');
		}
	}

	/**
	 * Acción para el resultado de eliminar una afiliación 
	 */
	function eliminar_resultado(){
            
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');
		$usuario = $this->Session->read('usuarios.datos');
                $tipos = $this->Afiliacion->buscarTipos();
                $subtipos = $this->Afiliacion->buscarSubTipos();
                
		if(
			(
				!isset($usuario['rol_juridico_id']) ||
				!in_array($usuario['rol_juridico_id'],array(3,5)) // No solicitar tc para operadores
			) &&
			(
				$validacion_tdc == null ||
				!isset($validacion_tdc['validado']) ||
				!$validacion_tdc['validado']
			)
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/afiliaciones/listado',
					'layout'=>'interna',
					'titulo'=>'Afiliaciones',
					'pasos'=> array(),
					'paso_actual'=>0,
					'seccion_actual'=>'afiliaciones.afiliacion_tdc'
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}

		if($this->Session->check('afiliaciones.eliminar')){
			$this->data = $this->Session->read('afiliaciones.eliminar');
			$usuario = $this->Session->read('usuarios.datos');												
			
			$this->layout = 'interna';

			$this->Session->delete('afiliaciones.eliminar');
			
			
			$this->data['Error'] = !$this->Afiliacion->delete($this->data['Afiliacion']['id']);

			$transaccion = ($this->data['Afiliacion']['tipo_afiliacion']=='t') ? 13 : 14;
			
			if($this->data['Afiliacion']['tipo_afiliacion']=='s') {
				$transaccion=100010;
			}elseif($this->data['Afiliacion']['tipo_afiliacion']=='r') {
				$transaccion=100011;
			}elseif($this->data['Afiliacion']['tipo_afiliacion']=='i') {
				$transaccion=100035;
			}
			
			if(!$this->data['Error']){
			
				// Email Options
				$this->Email->smtpOptions = Configure::read('email.smtp');
				$this->Email->delivery = 'smtp';
				$this->Email->from = Configure::read('email.from');
				$this->Email->to = desencriptar($usuario['email_principal']);
				$this->Email->subject = 'Notificacion Eliminacion Afiliacion';
				$this->Email->sendAs = 'html';

				// Informacion del correo
				$this->Email->template = 'afiliacion_producto';
				$tipo_afiliacion = $subtipos[trim($this->data['Afiliacion']['tipo_afiliacion'])][$this->data['Afiliacion']['tipo_operacion']];
				$tipo_accion = $this->data['Afiliacion']['accion'] .' '. $tipos[trim($this->data['Afiliacion']['tipo_afiliacion'])];
				$nombreBeneficiario= $this->data['Afiliacion']['nombre_beneficiario'];
				$numero_servicio= $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['numero_cuenta'];
				$tipo_afiliacion_s= $this->data['Afiliacion']['tipo_afiliacion'];

        $tipo_operacion = $this->data['Afiliacion']['tipo_operacion'];

				$this->set(compact('usuario','tipo_afiliacion', 'tipo_accion','nombreBeneficiario','numero_servicio','tipo_afiliacion_s','tipo_operacion'));
			

				// Envio del correo
				$this->Email->send();				                                
				
                                $descripcion=$tipo_accion . ' ' . $tipo_afiliacion;			
				
				$this->Bitacora->registraBitacora(
				$usuario['id'],
				$transaccion,
				NULL, 
				$numero_servicio, 
				$this->data['Afiliacion']['monto_maximo'],
				NULL,
				$descripcion,
				0
			);
			}else{
				$this->Bitacora->registraBitacora(
					$usuario['id'],
					$transaccion,
					NULL, 
					$this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['numero_cuenta'], 
					$this->data['Afiliacion']['monto_maximo'],
					$descripcion,
					NULL,
					1
				);
			}
			
                        $this->set('juridico',$usuario['tipo_rif']);
			$this->set('seccion_actual','afiliaciones.afiliacion_tdc');
			$this->set('tipos', $this->Afiliacion->buscarTipos());
			$this->set('subtipos', $this->Afiliacion->buscarSubTipos());
                        $this->set('servicios', $this->Afiliacion->buscarTiposServicios());
			$this->set('bancos', $this->Activo->buscarBancos());
			
		} else {
			$this->redirect('listado');
		}
	}

	/**
	 * Acción para afiliar el envío digital del estado de cuenta a una
	 * dirección de correo electrónico 
	 */
	function envio() {
		$this->layout = 'interna';

		$this->set('seccion_actual','afiliaciones.envio_digital');

		if(!empty($this->data['Afiliacion'])){
			$this->Afiliacion->set($this->data);
			if($this->Afiliacion->validates(
				array(
					'fieldList'=>array(
						'numero'
					)
				)
			)){
				$this->Session->write('afiliaciones.eliminar', $this->data);
				$this->redirect('envio_terminos');
			}
		}
	}

	/**
	 * Acción que indica los términos y condiciones de la afiliación al
	 * envío digital del estado de cuenta 
	 */
	function envio_terminos() {
		$this->layout = 'interna';

		$this->set('seccion_actual','afiliaciones.envio_digital');

		if(empty($this->data['Afiliacion'])){
			if(!$this->Session->check('afiliaciones.eliminar')){
				$this->redirect('envio');
			}
		}
		else{
			$data = $this->Session->read('afiliaciones.eliminar');
			$this->Afiliacion->set($data);
			if($this->Afiliacion->validates(
				array(
					'fieldList'=>array(
						'numero'
					)
				)
			)){
				$this->redirect('envio_resultado');
			}
		}
	}

	/**
	 * Acción para el resultado de la afiliación de los estado de cuenta
	 * digitales 
	 */
	function envio_resultado(){
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');

		$usuario = $this->Session->read('usuarios.datos');

		if(
			(
				!isset($usuario['rol_juridico_id']) ||
				!in_array($usuario['rol_juridico_id'],array(3,5)) // No solicitar tc para operadores
			) &&
			(
				$validacion_tdc == null ||
				!isset($validacion_tdc['validado']) ||
				!$validacion_tdc['validado']
			)
		){
			$this->Session->write(
				'seguridad.validar_tdc',
				array(
					'url_aceptar'=>'/afiliaciones/listado',
					'layout'=>'interna',
					'titulo'=>'Afiliaciones',
					'pasos'=> array(),
					'paso_actual'=>0,
					'seccion_actual'=>'afiliaciones.afiliacion_tdc'
				)
			);

			$this->redirect('/seguridad/validar_tdc');
		}

		$this->layout = 'interna';

		$this->set('seccion_actual','afiliaciones.envio_digital');

		$this->Session->delete('afiliaciones.eliminar');
	}
	
	/**
	 * Acción invocada por AJAX para buscar los subtipos de una operación
	 * @param string $operacion Tipo de operación
	 * @return null 
	 */
	function obtener_operacion($operacion){
            $this->layout = 'ajax';
            $usuario = $this->Session->read('usuarios.datos');
            if(!empty($operacion) && !empty($usuario) && in_array($operacion, array('t','te','p','s','r'))){
                $this->set('subtipos', $this->Afiliacion->buscarSubTipos($operacion));
            } else{
                return null;
            }
	}
			
	/**
	 * Función utilitaria para obtener un beneficiario dado el número de contrato, la operadora
	 * y el tipo de servicio
	 * @param string $num_contrato Número de contrato
         * @param string $operador Operadora (Movistar, Digitel Etc)
	 * @param string $tipo_servicio Tipo de servicio (Movil, Fijo, Internet)
	 */
	function obtener_beneficiario($operador,$num_contrato,$prepos,$tipo_servicio){
            if(!$this->Session->check('usuarios.datos')) exit;
            
            $datos_basicos = $this->Session->read('usuarios.nibpf3010');
            
            $this->layout = 'ajax';
            
            if($prepos=='r'){
                $prepos = 1;
            }else{
                $prepos = 2;
            }                                                       
            
            if($operador=='st'){
                if($tipo_servicio == 'stm' || $tipo_servicio == 'sim' || $tipo_servicio == 'rtm' || $tipo_servicio == 'rim'){
                    $modalidad = 70;
                }elseif($tipo_servicio == 'stf' || $tipo_servicio == 'rtf'){
                    $modalidad = 71;                                                                                                                
                }elseif($tipo_servicio == 'stv' || $tipo_servicio == 'rtv'){
                    $modalidad = 72;                                                    
                }
                $this->set('datos', $this->verificarBeneficiarioMovistar($num_contrato,$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],$datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],$prepos,$modalidad));
                return $this->verificarBeneficiarioMovistar($num_contrato,$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],$datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],$prepos,$modalidad);
            }elseif($operador=='sd'){
                if($tipo_servicio == 'stm' || $tipo_servicio == 'rtm' ){
                    $modalidad = 40;
                }elseif($tipo_servicio == 'stf' || $tipo_servicio == 'rtm' ){
                    $modalidad = 41;                                                        
                }elseif($tipo_servicio == 'sim' ||  $tipo_servicio == 'rim'){
                    $modalidad = 42;                                                        
                }elseif($tipo_servicio == 'stv' || $tipo_servicio == 'rtv'){
                    $modalidad = 43;                                                      
                }
                $this->set('datos', $this->verificarBeneficiarioDIGITEL($num_contrato,$datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCLIENTE'],$datos_basicos['NIBPF3010Return']['NIBPF3010']['IDENTIFICACIONIBS'],$prepos,$modalidad));
            }
                        
	}
	
	/**
	 * Busca el beneficiario deacuerdo al tipo y numero de servicio en CANTV
	 * 
	 * @param string $tipo_servicio
	 * @param string $num_contrato
	 * @return string
	 */
	function verificarBeneficiarioCANTV($tipo_servicio, $num_contrato){
		$usuario = $this->Session->read('usuarios.datos');
		$modalidad = 0;
		$datos = "";
		
		/** Para identificar si es un usuario juridico **/
		if($usuario['tipo_rif']){
			$documento_identificacion = $usuario['tipo_rif'].desencriptar($usuario['rif']);
		}
		else{
			$documento_identificacion = desencriptar($usuario['cedula']);
		}
		/** Para identificar si es un usuario juridico **/
		
		if($tipo_servicio == 'sc'){
			$modalidad = 10;
		}elseif($tipo_servicio == 'scn'){
			$modalidad = 20;
		}else{
			$modalidad = 30;
		}		
		
		if(!empty($num_contrato) && !empty($tipo_servicio)){
			$res = '';
			if((substr($num_contrato,0,2)=='02' || substr($num_contrato,0,3)=='050' || substr($num_contrato,0,3)=='080' || substr($num_contrato,0,3)=='090' || substr($num_contrato,0,4)=='0416' || substr($num_contrato,0,4)=='0426')  && ($tipo_servicio=='sc' || $tipo_servicio=='sm')){
				$res = $this->Activo->invoca(
					'IN098401',
					array(
						'req'=>array(
							'IN984USR1'=>$documento_identificacion,
							'IN984BVDD'=>date('d'),
							'IN984BVDM'=>date('m'),
							'IN984BVDY'=>date('y'),
							'IN984SER'=>$modalidad,
							'IN984ARE'=>substr($num_contrato,0,4),
							'IN984TLF'=>substr($num_contrato,4),
							'IN984TYPE'=>10											
						)
					)
				);
			}else{
				$res = $this->Activo->invoca(
					'IN098401',
					array(
						'req'=>array(
							'IN984USR1'=>$documento_identificacion,
							'IN984BVDD'=>date('d'),
							'IN984BVDM'=>date('m'),
							'IN984BVDY'=>date('y'),
							'IN984SER'=>$modalidad,
							'IN984CON'=>str_pad($num_contrato,12,'0',STR_PAD_LEFT),
							'IN984TYPE'=>10											
						)
					)
				);
			}
			$nombre = trim($res['IN098401Return']['IN098401']['IN984DS1']);
			if(!empty($res['IN098401Return']['error'])){
				$error = intval($res['IN098401Return']['error']['ERRNUM']);
				$error = ($error == 0) ? $error : intval($res['IN098401Return']['error']['ERNU01']);
				$descripcion = $res['IN098401Return']['error']['ERNU01'] . ' - ' . $res['IN098401Return']['error']['ERDS01'];
				$datos = $error;
				$this->log('Obtener Beneficiarios Afiliacion de Servicios - ' . $descripcion, LOG_ERR);
			} elseif (!empty($nombre)) {
				$datos = $res['IN098401Return']['IN098401']['IN984DS1'];
			}elseif (empty($nombre)) {
				$datos = 'N&uacute;mero Privado';
			}elseif ($res['IN098401Return']['error']['ERNU01'] != 00) {
				$datos = 'N&uacute;mero Privado';
			} else {
				$datos = -1;
				$this->log('Obtener Beneficiarios Afiliacion de Servicios - Servicio IN098401 sin respuesta', LOG_ERR);
			}
			
		}				
		return $datos;
	}
        
        /**
	 * Valida el beneficiario para la afiliacion del servicio DIGITEL	 
	 * @param string $numero Numero que desea consultar
         * @param string $clienteIBS Numero cliente IBS
         * @param string $identificacion Cedula, Pasaporte, RIF
         * @param string $prepos Codigo Prepago o Postpago
         * @param string $tipoServicio Codigo Tipo de Servicio (Movil, Fijo, Internet, TV)         
	 * @return string
	 */
	function verificarBeneficiarioDIGITEL($numero,$clienteIBS,$identificacion,$prepos,$tipoServicio){
            
		$datos = "";				
		$ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                
		if(!empty($numero)){
			
                    $res = '';                           
                    
                    $res = $this->Activo->invoca(
                            'IN098801',
                            array(
                                    'req'=>array(
                                            'IN988NCLT'=>$clienteIBS,
                                            'IN988IDUS'=>$identificacion,
                                            'IN988CHAN'=>'4',
                                            'IN988TSER'=>$prepos,
                                            'IN988TYPE'=>10,
                                            'IN988CSER'=>$tipoServicio,
                                            'IN988CARE'=>substr($numero,0,4),
                                            'IN988NTLF'=>substr($numero,4),
                                            'IN988DS2'=>$numero,
                                            'IN988IPCLT'=>$ip
                                    )
                            )
                    );						

                    if(!empty($res['IN098801Return']['error'])){
                            $error = intval($res['IN098801Return']['error']['ERRNUM']);
                            $error = ($error == 0) ? $error : intval($res['IN098801Return']['error']['ERNU01']);
                            $descripcion = $res['IN098801Return']['error']['ERNU01'] . ' - ' . $res['IN098801Return']['error']['ERDS01'];
                            $datos = $error;
                            $this->log('Obtener Beneficiario Afiliacion de Servicios Digitel - ' . $descripcion, LOG_ERR);
                    }elseif (!empty($res['IN098801Return']['IN098801'])) {
                            $datos = $res['IN098801Return']['IN098801']['IN988DS1'];
                    } else {
                            $datos = -1;
                            $this->log('Obtener Beneficiario Afiliacion de Servicios Digitel - Servicio IN098801 sin respuesta', LOG_ERR);
                    }			
		}				
		return $datos;
	}
        
        /**
	 * Valida el beneficiario para la afiliacion del servicio Movistar	 
	 * @param string $numero Numero que desea consultar
         * @param string $clienteIBS Numero cliente IBS
         * @param string $identificacion Cedula, Pasaporte, RIF
         * @param string $prepos Codigo Prepago o Postpago
         * @param string $tipoServicio Codigo Tipo de Servicio (Movil, Fijo, Internet, TV)
	 * @return string
	 */
	function verificarBeneficiarioMovistar($numero,$clienteIBS,$identificacion,$prepos,$tipoServicio){
                                		
		$datos = '';
                $res = '';			                		
                $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                
		if(!empty($numero)){			                    
                                               
                    $res = $this->Activo->invoca(
                            'IN098801',
                            array(
                                    'req'=>array(                                               
                                        'IN988NCLT'=>$clienteIBS,
                                        'IN988IDUS'=>$identificacion,
                                        'IN988CHAN'=>'4',
                                        'IN988TSER'=>$prepos,
                                        'IN988TYPE'=>10,
                                        'IN988CSER'=>$tipoServicio,                                           
                                        'IN988DS2'=>str_pad($numero,40,'0',STR_PAD_LEFT),
                                        'IN988IPCLT'=>$ip
                                    )
                            )
                    );						

                    if(!empty($res['IN098801Return']['error'])){
                            $error = intval($res['IN098801Return']['error']['ERRNUM']);
                            $error = ($error == 0) ? $error : $res['IN098801Return']['error']['ERNU01'];
                            $descripcion = $res['IN098801Return']['error']['ERNU01'] . ' - ' . $res['IN098801Return']['error']['ERDS01'];
                            $datos = $error;
                            $this->log('Obtener Beneficiario Afiliacion de Servicios Movistar - ' . $descripcion, LOG_ERR);
                    }elseif (!empty($res['IN098801Return']['IN098801'])) {
                            $datos = "";
                    } else {
                            $datos = -1;
                            $this->log('Obtener Beneficiario Afiliacion de Servicios Movistar - Servicio IN098801 sin respuesta', LOG_ERR);
                    }			
		}
                
		return $datos;
	}
        
	/*
	 * Muestra Contrato de Aceptacion de Servicio de Saldo Activo
	 */
        function contrato_saldo_activo($contrato=NULL){
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(array(100020))) $this->redirect('/consultas/integral');
            
            $usuario = $this->Session->read('usuarios.datos');
            
            $tipo_documento = $usuario['tipo_cedula'];
            $documento= desencriptar($usuario['cedula']);
            $numero_cuenta = array();
            $tipoCuentaCA = '';
            $cuentas = array();
            
            // Datos del Usuario
            $datos_basicos = $this->Session->read('usuarios.nibpf3010');
            
            $datos = $datos_basicos['NIBPF3010Return']['NIBPF3010'];
            
            if($datos['TIPOID'] == 'CIV'){                
                $datos['NACIONALIDAD'] = 'Venezolana';
            }else{    
                $datos['NACIONALIDAD'] = 'Extranjera';
            }
                        
            if(!isset($contrato)){
                $cuentas = $this->Session->read('afiliaciones.crear_saldo_activo');              
            }
            else{
                /* Busca las Afiliaciones de Saldo Activo del Cliente*/
                $res = $this->Activo->invoca(
                        'ECC051001',
                        array(
                                'req'=>array(
                                        'e01PATACC'=>$contrato,
                                        'h01OPECOD'=>'0005'											
                                )
                        )
                ); 
                /* Inicializa el listado de Afiliaciones */
                $arrData = array();
                if(!empty($res['ECC051001Return']['ECC051001']['ECC051001'])){
                    $arrData = $res['ECC051001Return']['ECC051001']['ECC051001'];
                    normaliza($arrData);
                }                
                $cuentas['Afiliacion']['numero_cuenta'] = $arrData[0]['e01PATACC'];
                $cuentas['Afiliacion']['numero_tarjeta'] = $arrData[0]['e01PATTDC'];
                
            }                                    
            
            if(strlen($cuentas['Afiliacion']['numero_cuenta']) <=10 ){                
                $numero_cuenta = $this->Activo->completaCuenta($cuentas['Afiliacion']['numero_cuenta']);
                $nro_cta = '0'.$numero_cuenta['EDL890101Return']['EDL890101']['e01ACC020'];
            }
            else{
                $nro_cta = $cuentas['Afiliacion']['numero_cuenta'];
            }
            
            if(substr($nro_cta,10,1) == '6' || substr($nro_cta,11,1) == '4'){
                $tipoCuentaCA = 'Corriente';
            }else{
                $tipoCuentaCA = 'Ahorro';
            }
            
            $productos  = array($nro_cta,$cuentas['Afiliacion']['numero_tarjeta']);
            $tipoCuenta = $this->Activo->buscarTipoCuenta($datos['IDENTIFICACIONIBS'],$productos);
            $datosPago = $this->Afiliacion->consultarSaldoActivo(encriptar($cuentas['Afiliacion']['numero_cuenta']));
            $meses = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
            
            if(count($datosPago)){
                $fecha = substr($datosPago['fecha_contrato'], 8,2).' de '.$meses[substr($datosPago['fecha_contrato'], 5,2)-0].' de '.substr($datosPago['fecha_contrato'], 0,4);
            }
            else{
                $fecha = date('d').' de '.$meses[date('m')-0].' de '.date('Y');
            }                                    
            
            $this->set(compact('datos','usuario','cuentas','tipoCuenta','nro_cta','fecha','tipoCuentaCA'));
        }
        
        
        /*
         * Confirmación de Desafiliación de Saldo Activo
         */
        function eliminar_confirmacion_saldo_activo(){
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(array(100020))) $this->redirect('/consultas/integral');
            
            if($this->Session->check('afiliaciones.crear_saldo_activo')) { // Verifica datos de la Vista
                 $this->data = $this->Session->read('afiliaciones.crear_saldo_activo');
                 $id_afiliacion = $this->data['Afiliacion']['id'];
                 $cuentas = $this->data['Afiliacion']['numero_cuenta'];
                 $nro_tarjeta = $this->data['Afiliacion']['numero_tarjeta'];
            }
            $this->set('seccion_actual','afiliaciones.afiliacion_saldo_activo');
            $this->layout = 'interna';
        }

        /*
         * Resultado de Desafiliación de Saldo Activo
         */
        function eliminar_resultado_saldo_activo(){
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(array(100020))) $this->redirect('/consultas/integral');
            
            // Datos del Usuario
            $usuario = $this->Session->read('usuarios.datos');
            
            if($this->Session->check('afiliaciones.crear_saldo_activo')) { // Verifica datos de la Vista
                 $this->data = $this->Session->read('afiliaciones.crear_saldo_activo');
                 
                $parametros = array(
                    'req'=>array(
                            'h01OPECOD'=>'0004',
                            'e01PATACC'=>$this->data['Afiliacion']['numero_cuenta'],
                            'e01PATTDC'=>$this->data['Afiliacion']['numero_tarjeta']
                    )
                );

                $res = $this->Activo->invoca(
                        'ECC051001',
                        $parametros
                );                 
                 
                 $this->data['Error'] = false;

                 if($res['ECC051001Return']['error']['ERNU01']){
                     $this->data['Error'] = true;
                 }
                 
                 if(!$this->data['Error']){
                    $this->Afiliacion->desafiliarSaldoActivo(encriptar($this->data['Afiliacion']['numero_cuenta']));
                                                              
                    // Email Options
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery = 'smtp';
                    $this->Email->from = Configure::read('email.from');
                    $this->Email->to = desencriptar($usuario['email_principal']);
                    $this->Email->subject = 'Notificacion Eliminacion Saldo Activo Plus';
                    $this->Email->sendAs = 'html';

                    // Informacion del correo
                    $this->Email->template = 'afiliacion_saldo_activo';
                    $tipo_afiliacion = '';
                    $tipo_accion = 'Eliminacion al servicio Saldo Activo Plus';
                    $nombreBeneficiario= desencriptar($usuario['nombre']);
                    $numero_servicio= $this->data['Afiliacion']['numero_cuenta'];
                    $tipo_afiliacion_s= 'Afiliacion Pago Activo';
                    $this->set(compact('usuario','tipo_afiliacion', 'tipo_accion','nombreBeneficiario','numero_servicio','tipo_afiliacion_s'));


                    // Envio del correo
                    $this->Email->send();
                    
                    // Registro en Bitacora
                    $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            100020,
                            NULL, 
                            $this->data['Afiliacion']['numero_cuenta'], 
                            $this->data['Afiliacion']['numero_tarjeta'],
                            'Eliminación Afiliación Pago Activo',
                            NULL,
                            0
                    );                     
                }else{                    
                    // Registro en Bitacora
                    $this->Bitacora->registraBitacora(
                            $usuario['id'],
                            100020,
                            NULL, 
                            $this->data['Afiliacion']['numero_cuenta'], 
                            $this->data['Afiliacion']['numero_tarjeta'],
                            'Eliminación Afiliación Pago Activo',
                            NULL,
                            1
                    );                     
                }

            }
            $this->set('seccion_actual','afiliaciones.afiliacion_saldo_activo');
            $this->layout = 'interna';
        }


    //Inicio tarjeta prepago cash
    //inicio de valaTarjetaCash
    function valTarjetaCash($res){
      $swActivo = 0;
      $codProducto = _p('tcp_codigo_producto');
      $fechaActivo = '';
      $tarjeta = "0";
      $arreglo = $res['CuentaTarjetaClienteResponse'];

      //quitar al realizar las pruebas
      //$codProducto = 88;


      if(isset($arreglo['codAsociado'])){
            if($arreglo['codAsociado']==0){
              return 2;
            }
       
          if(trim($arreglo['codigoProducto']) == trim($codProducto)){
            $swActivo = 1;
        if($arreglo['codigoEstatus'] < 4){
            if($arreglo['codigoEstatusTrj'] != '10'){
                $swActivo = 1;
                $nroTarjeta = $arreglo['tarjeta'];
            }else{
                $swActivo = 3;
                  }    
              }else{
                  $swActivo = 3;
              }    
      }      
    }else{
        foreach ($arreglo as $valor) {
            $codProductoWS = $valor['codigoProducto'];
            if(trim($codProductoWS) == trim($codProducto)){
               $swActivo = 1;
               if($valor['codigoEstatus'] < 4){
                    if($valor['codigoEstatusTrj'] != '10'){
                        $swActivo = 1;
                        $nroTarjeta = $arreglo['tarjeta'];
                        break;
                    }else{
                        $swActivo = 3;
                    }                                    
                   
               }else{
                      $swActivo = 3;
                   }    
          } 
        }    
    } 




        if($swActivo == 1){
            return $nroTarjeta;
        }else{
          return $swActivo;
        }  
    }  //fin de valTarjetaCash


    function error_critico($swValidar){
        $this->layout = 'interna';
        $mensaje = _m('error.fatal');
        $descripcion = $mensaje . " [CC]";  
        $this->set(compact('descripcion'));
    }
    //Fin  tarjeta prepago cash


    function regBitacora($usuario_id, $nroCuenta, $nroTarjeta, $mensaje){
        $this->Bitacora->registraBitacora(
            $usuario_id,
            100020,
            $nroCuenta, 
            $nroTarjeta,
            null,
            null,
            $mensaje,
            NULL,
            1
      ); 
    }


      /*
   * Muestra Contrato de Aceptacion de Servicio de Saldo Activo Juridico
   */
        function contrato_saldo_activo_juridico($contrato=NULL){
            /* Para validar el acceso al modulo */


            if(!validarTransaccion(array(100020))) $this->redirect('/consultas/integral');

            


            
            $usuario = $this->Session->read('usuarios.datos');
            
            $tipo_documento = $usuario['tipo_cedula'];
            $documento= desencriptar($usuario['cedula']);
            $numero_cuenta = array();
            $tipoCuentaCA = '';
            $cuentas = array();


            // Datos del Usuario
            $datos_basicos = $this->Session->read('usuarios.nibpf3010');
            
            $datos = $datos_basicos['NIBPF3010Return']['NIBPF3010'];
            
            if($datos['TIPOID'] == 'CIV'){                
                $datos['NACIONALIDAD'] = 'Venezolana';
            }else{    
                $datos['NACIONALIDAD'] = 'Extranjera';
            }
                        
            if(!isset($contrato)){
                $cuentas = $this->Session->read('afiliaciones.crear_saldo_activo');              
            }
            else{
                /* Busca las Afiliaciones de Saldo Activo del Cliente*/

                $res = $this->Activo->invoca(
                        'ECC051001',
                        array(
                                'req'=>array(
                                        'e01PATACC'=>$contrato,
                                        'h01OPECOD'=>'0005'                     
                                )
                        )
                ); 


                /* Inicializa el listado de Afiliaciones */
                $arrData = array();
                if(!empty($res['ECC051001Return']['ECC051001']['ECC051001'])){
                    $arrData = $res['ECC051001Return']['ECC051001']['ECC051001'];
                    normaliza($arrData);
                }                
                $cuentas['Afiliacion']['numero_cuenta'] = $arrData[0]['e01PATACC'];
                $cuentas['Afiliacion']['numero_tarjeta'] = $arrData[0]['e01PATTDC'];

                $productos  = array($nro_cta,$cuentas['Afiliacion']['numero_tarjeta']);

                $ctaTarjeta = substr($productos[1], 0,10);
                $ctaTarjeta = $this->Activo->completaCuenta($ctaTarjeta);
                $cuentaT = "0" . trim($ctaTarjeta['EDL890101Return']['EDL890101']['e01ACC020']); 
                $cuentas['Afiliacion']['numero_tarjeta'] = $cuentaT;

                
            }                                    
            if(strlen($cuentas['Afiliacion']['numero_cuenta']) <=10 ){                
                $numero_cuenta = $this->Activo->completaCuenta($cuentas['Afiliacion']['numero_cuenta']);
                $nro_cta = '0'.$numero_cuenta['EDL890101Return']['EDL890101']['e01ACC020'];
            }
            else{
                $nro_cta = $cuentas['Afiliacion']['numero_cuenta'];
            }

            

            
            if(substr($nro_cta,10,1) == '6' || substr($nro_cta,11,1) == '4'){
                $tipoCuentaCA = 'Corriente';
            }else{
                $tipoCuentaCA = 'Ahorro';
            }
            




            $tipoCuenta = $this->Activo->buscarTipoCuenta($datos['IDENTIFICACIONIBS'],$productos);
            $datosPago = $this->Afiliacion->consultarSaldoActivo(encriptar($cuentas['Afiliacion']['numero_cuenta']));
            $meses = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
            
            if(count($datosPago)){
                $fecha = substr($datosPago['fecha_contrato'], 8,2).' de '.$meses[substr($datosPago['fecha_contrato'], 5,2)-0].' de '.substr($datosPago['fecha_contrato'], 0,4);
            }
            else{
                $fecha = date('d').' de '.$meses[date('m')-0].' de '.date('Y');
            } 




            $this->set(compact('datos','usuario','cuentas','tipoCuenta','nro_cta','fecha','tipoCuentaCA'));
        }

    function sanitize_input($data) {
	    return filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }
      
}
?>