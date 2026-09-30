<?php
/**
 * Modelo para el manejo de los afiliados.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los afiliados.
 * 
 */
class Afiliacion extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'afiliaciones';
	
	/**
	 * Para listar las afiliaciones de un usuario de acuerdo al tipo de afiliación.
	 * 
	 * @param int $id_usuario
	 * @param string $tipo_afiliacion
	 * @return array
	 */
	function listar($id_usuario, $tipo_afiliacion=NULL){
		
		$conditions = array( 'Afiliacion.usuario_id'=>$id_usuario);
		
		if($tipo_afiliacion=='t' || $tipo_afiliacion=='p' || $tipo_afiliacion=='i'){
			$conditions= array_merge($conditions, array('Afiliacion.tipo_afiliacion'=>$tipo_afiliacion));
		}
		
		return $this->find(
			'all',
			array( 'conditions'=>$conditions )
		);
	}
	
	
	/**
	 * Para buscar la información de un afiliado en particular.
	 * 
	 * @param int $id_usuario
	 * @return array
	 */
	function buscar($id_usuario){
		App::import('Component', 'Session');
		
		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');	
		$usuario = $session->read('usuarios.datos');	
		
		$condiciones = array(
			'Afiliacion.id' => $this->data['Afiliacion']['id']
		);
		
		if($juridico){
			$condiciones['tipo_rif']=$usuario['tipo_rif'];
			$condiciones['rif']=$usuario['rif'];
		} else {
			$condiciones['usuario_id'] = $id_usuario;
		}
		
		$x = $this->find(
			'first',
			array(
				'conditions'=>$condiciones
			),
			false
		);
		
		return $x;
	}
	
	
	/**
	 * Para actualizar o guardar la información de una afiliación.
	 * 
	 * @param int $id_usuario
	 * @return boolean
	 */
	public function saveData($id_usuario){


		        //print_r($this->data['Afiliacion']);
		        //die("<br>Guardar data");
		
                //Si es pago activo encripto los campos adicionales
                if($this->data['Afiliacion']['tipo_afiliacion']=='m'){                    
                    $data['Afiliacion'] = encriptarCampos($this->data['Afiliacion'], array('numero_cuenta', 'numero_cuenta_receptora', 'numero_cuenta_ahorro', 'telefono', 'email', 'numero_contrato'));
                }else{
                    $data['Afiliacion'] = encriptarCampos($this->data['Afiliacion'], array('alias', 'numero_cuenta', 'nombre_beneficiario', 'cedula', 'email'));
                }				
                
		// formatea el monto
		$data['Afiliacion']['monto_maximo'] = formatea_monto_toWs($data['Afiliacion']['monto_maximo']);		                
                        
		// asigna el usuario
		$data['Afiliacion']['usuario_id'] = $id_usuario;
		
		// elimina los campos que no se encuentran en la BD
		unset($data['Afiliacion']['monto_limite']);
		unset($data['Afiliacion']['lim_t']);
		unset($data['Afiliacion']['lim_ob']);
		unset($data['Afiliacion']['lim_tob']);
		
		if( !$this->save($data,false)){
            $this->log($this->getDataSource()->error);
            return false;
		}
		
		return true;
	}
	
	
	/**
	 * Método para la validación de la longitud de la cédula.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function validaLongitud($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		return $this->checkMaxCI($this->data['Afiliacion']['tipo_cedula'], $value);
	}
	
	
	/**
	 * Retorna los tipos de afiliación que existen.
	 * 
	 * @return array
	 */
	function buscarTipos(){
		return array(
			't'=>'Transferencia',
			'd'=>'Transferencia Moneda Extranjera',
			'p'=>'Pago Tarjeta de Crédito',
			's'=>'Pago Servicios Postpago',
			'h'=>'Tarjeta Prepago Cash / Recargas',			
			'r'=>'Recarga Prepago',
            'i'=>'Pago Impuestos Nacionales/Aduanales',
            'a'=>'Saldo Activo',
            'm'=>'Pago Activo',
            'c'=>'Corpoelec',
		);
	}
	
	
	/**
	 * Retorna los subtipos de las afiliaciones.
	 * 
	 * @param varchar $key
	 * @return array
	 */
	function buscarSubTipos($key=NULL){
		$sub= array( 
			't'=>array(
				't'=>'Tercero en Activo',  
				'ob'=>'Mismo Titular Otro Banco', 
				'tob'=>'Tercero en Otro Banco'
			),
			'd'=>array(
                't'=>'Tercero en Activo'				
			),

			'p'=>array(
				't'=>'Tercero en Activo', 
				'ob'=>'Mismo Titular Otro Banco', 
				'tob'=>'Tercero en Otro Banco'
			),
			's'=>array(
				//'sc'=>'CANTV', 
				'sm'=>'Movilnet',                                
				//'scn'=>'Cantv.net'
                'st'=>'Movistar',
                'sd'=>'Digitel',
                'si'=>'Inter'
			),	
			'r'=>array(
				//'sc'=>'CANTV', 
				'sm'=>'Movilnet',
                'sd'=>'Digitel',
				//'scn'=>'Cantv.net'
                'st'=>'Movistar',
                'si'=>'Inter',
                'sv'=>'Simpletv'
			),
			'i'=>array(
				'p'=>'Mismo Titular', 
				't'=>'Terceros', 
            ),
            'a'=>array(
                    'mt'=>'Mismo Titular'
            ),
            'm'=>array(
                    'a'=>'Afiliación'					
            ),
			'h'=>array(
                'ht'=>'Tercero en Activo'				
			),
			'c'=>array(
                'ce'=>'Cuenta Energia',
				'cea'=>'Cuenta Energia + Aseo Relleno'
			),
		); 
		if(empty($key)){
			return $sub;
		}else{
			return $sub[$key];
		}
	}
	
	/**
	 * Retorna los tipos de servicios.
	 * s = Pospago r = Prepago, luego se coloca las iniciales del servicio
	 * @param varchar $key
	 * @return array
	 */
	function buscarTiposServicios($key=NULL){
		$sub= array( 			
			's'=>array(
				'stm'=>'Telefonía Móvil',
				'stf'=>'Telefonía Fija',
                'sim'=>'Internet Móvil',
                //'stv'=>'Servicio TV',
                'sfi'=>'Telefonía fija, TV e internet'
			),	
			'r'=>array(				
                'rtm'=>'Telefonía Móvil',
				'rtf'=>'Telefonía Fija',
                'rim'=>'Internet Móvil',
                //'rtv'=>'Servicio TV',
                'rfi'=>'Telefonía fija, TV e internet',
                'rtc'=>'Televisión por Cable'
			),
			'c'=>array(
				'ce'=>'Cuenta Energia',
				'cea'=>'Cuenta Energia + Aseo Relleno'
			)
		); 
		if(empty($key)){
			return $sub;
		}else{
			return $sub[$key];
		}
	}


	/**
	 * Retorna los tipos de servicios Simple TV.
	 * s = Pospago r = Prepago, luego se coloca las iniciales del servicio
	 * @param varchar $key
	 * @return array
	 */
	function buscarTiposServiciosST($key=NULL){
		$sub= array( 			
			'r'=>array(				
                'rtc'=>'Televisión por Cable',
			)
		); 
		if(empty($key)){
			return $sub;
		}else{
			return $sub[$key];
		}
	}
        
	/**
	 * Método para la validación del límite en el monto para una afiliacion.
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

		if($value!="" && $this->data['Afiliacion']['monto_limite']!=""){
		   $monto = formatea_monto_toWs($value);
		   $monto_maximo = formatea_monto_toWs($this->data['Afiliacion']['monto_limite']);		

		   return $monto <= $monto_maximo;
		}
		return true;

	}  
	
	/**
	 * Método para validar que un monto sea multiplo de un valor
	 * 
	 * @param array|string $check
         * @param string $valor (multiplo de)
	 * @return boolean
	 */
	function montoMultiplo($check,$valor){
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!=""){
		   $monto = formatea_monto_toWs($value);		   
                   if($monto % $valor == 0){
                       return true;
                   }else{
                       return false;
                   } 		   
		}		
	}
        
        /**
	 * Método para validar que la identificacion del cliente y destino sean distintas.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function comparaIdentificacion(){			            
            return $this->checkDestinatario($this->data['Afiliacion']['tipo_cedula'], $this->data['Afiliacion']['cedula']);
	}
        
	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'id' => array(
			'numero'=> array(
				'rule'=>'numeric'
			),		
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),

		'accion' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('crear','editar','eliminar'))
			),
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),		
		'numero_cuenta' => array(
			'numero'=> array(
				'rule'=>'nroCuenta'
			)
		),                
                'numero_cuenta_ahorro' => array(
			'numero'=> array(
				'rule'=>'nroCuenta'
			)
		),                                        
                'numero_tarjeta' => array(
                        'numero'=> array(
				'rule'=>'numeric'
			),
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		
		'nombre_beneficiario' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			),		
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'tipo_cedula' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('V','E','J','P','G'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'cedula' => array(
			'longitud'=>array(
				'rule'=>'validaLongitud'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
                        'destinatario'=>array(
				'rule'=>'comparaIdentificacion'
			)
		),
		
		'email' => array(
			'correo'=>array(
				'rule'=>'email'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
                       'longitud'=>array(
                            'rule'=>array('maxLength','50'),
                            'message' => 'El Email debe tener 50 caracteres como maximo'
                        )
		),
		
		'alias' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			)
		),
		
		'tipo_afiliacion' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'tipo_operacion' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
                
                'tipo_servicio' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
            
                'monto_maximo' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
		),

		'telefono' => array(
			'numero'=> array(
				'rule'=>'numeric'
			),		
			'requerido'=> array(
				'rule'=>'notEmpty'
			),
			'noPermitido'=> array(
				'rule'=>'checkAfiliacionServicioP2P'
			)
		),				
	);
	
	
	/**
	 * Obtiene los afiliados de un usuario de acuerdo a un tipo de afiliación y
	 * al tipo de operación.
	 * 
	 * @param int $id_usuario
	 * @param string $tipo_afiliacion
	 * @param string $tipo_operacion
	 * @return array
	 */
	public function obtenerListaAfiliados($id_usuario, $tipo_afiliacion, $tipo_operacion){
		App::import('Component', 'Session');
		
		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');	
		$usuario = $session->read('usuarios.datos');	
		
		$condiciones = array(
			'Afiliacion.tipo_afiliacion'=> $tipo_afiliacion,
			'Afiliacion.tipo_operacion'=> $tipo_operacion
		);
		
		if($juridico){
			$condiciones['tipo_rif']=$usuario['tipo_rif'];
			$condiciones['rif']=$usuario['rif'];
		} else {
			$condiciones['usuario_id'] = $id_usuario;
		}	
		
		return $this->find(
			'all',
			array(
				'conditions'=>$condiciones
			)
		);
	}
	
	
	/**
	 * Obtiene la información de un afiliado dado el número un producto.
	 * 
	 * @param int $id_usuario
	 * @param string $nro_producto
	 * @return array
	 */
	public function obtenerDataAfiliado($id_usuario, $nro_producto, $id=NULL){
		App::import('Component', 'Session');
		
		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');	
		$usuario = $session->read('usuarios.datos');	
		
		if(!empty($id)){
			$condiciones = array(
			'id' => $id
		);
		}else{
			$condiciones = array(
			'numero_cuenta' => encriptar($nro_producto)
		);
		}
		
		
		if($juridico){
			$condiciones['tipo_rif']=$usuario['tipo_rif'];
			$condiciones['rif']=$usuario['rif'];
		} else {
			$condiciones['usuario_id'] = $id_usuario;
		}	
		
		$result = $this->find(
			'first',
			array(
				'conditions'=>$condiciones
			),
			false
		);
		
		return $result['Afiliacion'];
	}	
	
	/**
	 * Busca y devuelve el nombre del beneficiario dado el id del usuario y el número
	 * de contrato
	 * @param int $id_usuario Identificador del usuario
	 * @param string $num_contrato Número de contrato afiliado
	 */
	public function obtenerNombreBeneficiario($id_usuario, $num_contrato){
		$this->NombreBeneficiarioServicio=array();
		 $this->NombreBeneficiarioServicio[] = array(
                    "DatosCantv"=>array(
                        'nombre'=>'Jescel',
                        'apellido'=>'Romero',
                        'email_beneficiario'=>'jorge@tumail.com'
                    )
                    
                );           
		
		 return $this->NombreBeneficiarioServicio;
	}

	
	/**
	 * Para realizar el registro de afiliados desde pagos/transferencias
	 * 
	 * @param int $id_usuario
	 * @param string $tipo_afiliacion
	 * @param string $tipo_operacion
	 * @param string $alias
	 * @param int $nro_producto
	 * @param string $nombre
	 * @param string $tipo_cedula
	 * @param string $nro_cedula
	 * @param string $email
	 * @param string $monto_maximo
	 * @param string $tipo_rif
	 * @param string $rif
	 * @return boolean
	 */
	public function afiliar($id_usuario, $tipo_afiliacion, $tipo_operacion, $alias=NULL, $nro_producto, $nombre, $tipo_cedula, $nro_cedula, $email, $monto_maximo,$tipo_rif=null,$rif=null,$id_afiliacion=null,$codigo_telefono=NULL,$telefono=NULL,$banco=NULL){
                                
                if(empty($id_afiliacion)){                    
                    $datos = array(
                        'usuario_id'=>$id_usuario,
                        'tipo_afiliacion'=>$tipo_afiliacion,
                        'tipo_operacion'=>$tipo_operacion,
                        'alias'=>encriptar($alias),
                        'numero_cuenta'=>encriptar($nro_producto),
                        'nombre_beneficiario'=> encriptar($nombre),
                        'tipo_cedula'=>$tipo_cedula,
                        'cedula'=>encriptar($nro_cedula),
                        'email'=>encriptar($email),
                        'monto_maximo'=>formatea_monto_toWs($monto_maximo),
                        'tipo_rif'=>$tipo_rif,
                        'rif'=>$rif,
                        'codigo_telefono'=>$codigo_telefono,
                        'telefono'=>$telefono,
                        'banco'=>$banco
                    );
                }
                else{
                    $datos = array(
                        'usuario_id'=>$id_usuario,
                        'tipo_afiliacion'=>$tipo_afiliacion,
                        'tipo_operacion'=>$tipo_operacion,
                        'alias'=>encriptar($alias),
                        'numero_cuenta'=>encriptar($nro_producto),
                        'nombre_beneficiario'=>encriptar($nombre),
                        'tipo_cedula'=>$tipo_cedula,
                        'cedula'=>encriptar($nro_cedula),
                        'email'=>encriptar($email),
                        'monto_maximo'=>formatea_monto_toWs($monto_maximo),
                        'tipo_rif'=>$tipo_rif,
                        'rif'=>$rif,
                        'id'=>$id_afiliacion,
                        'codigo_telefono'=>$codigo_telefono,
                        'telefono'=>$telefono,
                        'banco'=>$banco
                    );
                }
            
            
		if( !$this->save(
			array('Afiliacion'=>$datos
				
			),false
		)){
			$this->log($this->getDataSource()->error);
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Para verificar si ya esta afiliada una cuenta
	 * 
	 * @param int $id_usuario
	 * @param string $nro_producto
	 * @return boolean
	 */
	public function checkAfiliacion($id_usuario, $nro_producto){
		App::import('Component', 'Session');
		
		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');	
		$usuario = $session->read('usuarios.datos');	
		
		$condiciones = array(
			'numero_cuenta' => encriptar($nro_producto),

		);
		
		if($juridico){
			$condiciones['tipo_rif']=$usuario['tipo_rif'];
			$condiciones['rif']=$usuario['rif'];
		} else {
			$condiciones['usuario_id'] = $id_usuario;
		}
			
		$result = $this->find(
			'first',
			array(
				'fields' => array (
					'Afiliacion.id'
				),
				'conditions'=> $condiciones
			),
			false
		);
		
		return ( isset($result['Afiliacion']) );
	}
        
        /**
	 * Para verificar si ya esta afiliado un numero de telefono o servicio
	 * MGGY
	 * @param int $id_usuario
         * @param string $codigo
	 * @param string $nro_telefono
	 * @return boolean
	 */
	public function checkAfiliacionServicio($id_usuario,$nro_telefono,$codigo=null){
		App::import('Component', 'Session');
		

		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');	
		$usuario = $session->read('usuarios.datos');	
		

                if(!empty($codigo)){
                    $condiciones = array(
			'codigo_telefono' => $codigo,
                        'numero_cuenta' => encriptar($nro_telefono),
                    );


                }else{
                    $condiciones = array(			
                        'numero_cuenta' => encriptar($nro_telefono)
                    );
                }		
		
		if($juridico){
			$condiciones['tipo_rif']=$usuario['tipo_rif'];
			$condiciones['rif']=$usuario['rif'];
                  
		} else {
			$condiciones['usuario_id'] = $id_usuario;
		}
			
		$result = $this->find(
			'first',
			array(
				'fields' => array (
					'Afiliacion.id'
				),
				'conditions'=> $condiciones
			),
			false
		);
		
		return ( isset($result['Afiliacion']) );
	}
	
	      /**
	 * Para verificar si ya esta afiliado un numero de telefono o servicio
	 * MGGY
	 * @param int $id_usuario
         * @param string $codigo
	 * @param string $nro_telefono
	 * @return boolean
	 */
	public function checkAfiliacionServicioP2P($id_usuario,$nro_telefono,$codigo,$tipo_servicio){
		App::import('Component', 'Session');
	

		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');	
		$usuario = $session->read('usuarios.datos');

				
                if(!empty($codigo)){
                    $condiciones = array(
						'codigo_telefono' => $codigo,
                        'telefono' => encriptar($nro_telefono),
                        'tipo_afiliacion'=>'m',
                       // 'tipo_servicio'=>'7'
                    );


                }else{
                    $condiciones = array(			
                        'telefono' => encriptar($nro_telefono)
                    );
                }		
		
		if($juridico){
			$condiciones['tipo_rif']=$usuario['tipo_rif'];
			$condiciones['rif']=$usuario['rif'];
                  
		} else {
			$condiciones['usuario_id'] = $id_usuario;
		}

		if($tipo_servicio=='7'){
			$condiciones['tipo_servicio']=7;
		}else{
			$condiciones['tipo_servicio']=5;
		}

		
			
		$result = $this->find(
			'first',
			array(
				'fields' => array (
					'Afiliacion.id'
				),
				'conditions'=> $condiciones
			),
			false
		);
		
  	return ( isset($result['Afiliacion']) );
	}
	
	#MC
	/*
	 * Para verificar si ya esta afiliado un numero de telefono o servicio
	 * MGGY
	 * @param int $id_usuario
         * @param string $codigo
	 * @param string $nro_telefono
	 * @return boolean
	 */
	public function checkAfiliacionServicioC2P($id_usuario,$nro_telefono=null,$codigo=null){
		App::import('Component', 'Session');

	
		

		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');	
		$usuario = $session->read('usuarios.datos');

		

				
                if(empty($codigo)){
                    $condiciones = array(
						'tipo_afiliacion'=>'m',
                        'tipo_servicio'=>'7'
                    );


                }else{
                    $condiciones = array(			
                        'telefono' => encriptar($nro_telefono)
                    );
                }		
		
		if($juridico){
			$condiciones['tipo_rif']=$usuario['tipo_rif'];
			$condiciones['rif']=$usuario['rif'];
                  
		} else {
			$condiciones['usuario_id'] = $id_usuario;
		}
			
		$result = $this->find(
			'first',
			array(
				'fields' => array (
					'Afiliacion.id'
				),
				'conditions'=> $condiciones
			),
			false
		);
		
		return ( isset($result['Afiliacion']) );
	}
	
	
	/**
	 * Verifica el alias de un producto afiliado del usuario
	 * 
	 * @param integer $id_usuario
	 * @param string $alias
	 * @return boolean
	 */
	public function checkAlias($id_usuario, $alias){
		App::import('Component', 'Session');

		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');	
		$usuario = $session->read('usuarios.datos');	
		
		$condiciones = array(
			'alias' => encriptar($alias),
		);
		
		if($juridico){
			$condiciones['tipo_rif']=$usuario['tipo_rif'];
			$condiciones['rif']=$usuario['rif'];
		} else {
			$condiciones['usuario_id'] = $id_usuario;
		}
		
		$result = $this->find(
			'first',
			array(
				'fields' => array (
					'Afiliacion.id'
				),
				'conditions' => $condiciones
			),
			false
		);
		
		return ( isset($result['Afiliacion']) );
		
	}				
			
	/**
	 * Para validar el formato MOVILNET
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function movilnet($check) {
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}
		return preg_match('/^\d{7}(?:\d{7})?$/', $value);
	}

	function movilnet_postpago($check) {
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}
		return preg_match('/^\d{9}(?:\d{9})?$/', $value);
	}
	
	/**
	 * Para validar el formato CANTV
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function cantv($check) {
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		return preg_match('/^02\d{9}$/', $value) || preg_match('/^(?:050|080)|090\d{8}$/', $value);	
	}
        
        /**
	 * Para validar el formato DIGITEL
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function digitel($check) {            
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0]; 
		} else {
		   $value = $check;
		}           		

		return preg_match('/^\d{7}(?:\d{7})?$/', $value);
	}
        
        /**
	 * Para validar el formato MOVISTAR POSTPAGO
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function movistarPostpago($check) {            
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0]; 
		} else {
		   $value = $check;
		}           		

		return preg_match('/^\d{1,20}(?:\d{1,20})?$/', $value);
	}
        
        /**
	 * Para validar el formato MOVISTAR PREPAGO
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function movistarPrepago($check) {            
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0]; 
		} else {
		   $value = $check;
		}           		

		return preg_match('/^\d{1,20}(?:\d{1,20})?$/', $value);
	}
        
	/**
	 * Para la recarga mínima en servicios
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function recargaMinima($check){
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && !empty($this->data['Afiliacion']['monto_maximo'])){
		   $monto = floatval(formatea_monto_toWs($value));
		   $tipoOperacion = isset($this->data['Afiliacion']['tipo_operacion']) ? trim($this->data['Afiliacion']['tipo_operacion']) : '';
		   if($tipoOperacion == 'sd'){
                       $monto_minimo = _p('recarga_minima_digitel');
                   }elseif($tipoOperacion == 'st'){
                       $monto_minimo = _p('recarga_minima_movistar');
                   }elseif($tipoOperacion == 'si'){
                       $monto_minimo = _p('recarga_minima_inter_post');
                   }elseif($tipoOperacion == 'ri'){
                       $monto_minimo = _p('recarga_minima_inter_pre');
                   }elseif($tipoOperacion == 'sv'){
                       $monto_minimo = _p('recarga_minima_simple_tv');
                   }elseif($tipoOperacion == 'sm' || $tipoOperacion == 'sc' || $tipoOperacion == 'scn'){
                       $monto_minimo = _p('recarga_minima_movilnet');
                   }else{
                       $monto_minimo = _p('recarga_minima');
                   }                  
		   $monto_minimo = floatval(formatea_monto_toWs($monto_minimo));
		   return $monto >= $monto_minimo;
		}
		return true;

	}
        
	/**
	 * Para la recarga máxima en servicios
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function recargaMaxima($check){
		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && !empty($this->data['Afiliacion']['monto_maximo'])){
		   $monto = floatval(formatea_monto_toWs($value));
		   $tipoOperacion = isset($this->data['Afiliacion']['tipo_operacion']) ? trim($this->data['Afiliacion']['tipo_operacion']) : '';
                   if($tipoOperacion == 'sd'){
                       $monto_maximo = _p('recarga_maxima_digitel');
                   }elseif($tipoOperacion == 'st'){
                       $monto_maximo = _p('recarga_maxima_movistar');
                   }elseif($tipoOperacion == 'si'){
                       $monto_maximo = _p('recarga_maxima_inter_post');
                   }elseif($tipoOperacion == 'ri'){
                       $monto_maximo = _p('recarga_maxima_inter_pre');
                   }elseif($tipoOperacion == 'sv'){
                       $monto_maximo = _p('recarga_maxima_simple_tv');
                   }elseif($tipoOperacion == 'sm' || $tipoOperacion == 'sc' || $tipoOperacion == 'scn'){
                       $monto_maximo = _p('recarga_maxima_movilnet');
                   }else{
                       $monto_maximo = _p('recarga_maxima');
                   }                  
		   $monto_maximo = floatval(formatea_monto_toWs($monto_maximo));
		   return $monto <= $monto_maximo;
		}
		return true;

	} 
        
	/*
	 * Método para el registro de Saldo Activo
	 * 
	 * @param array|string $valores
	 * @return boolean
	 */        
        
        function afiliarSaldoActivo($valores){
                if(!$this->save($valores,false)){
			$this->log($this->getDataSource()->error);
			return false;
		}
		
		return true;            
        }
        
	/*
	 * Método para la eliminacion de Saldo Activo
	 * 
	 * @param array|string $cuenta
	 * @return boolean
	 */         
        function desafiliarSaldoActivo($cuenta){
                $condiciones = array(
                        "tipo_afiliacion" => "a",
                        "numero_cuenta" => $cuenta
                );

                if(!$this->deleteAll($condiciones,false)){
			$this->log($this->getDataSource()->error);
			return false;
		}
		
		return true;            
        }

	/*
	 * Método para la consulta de Saldo Activo
	 * 
	 * @param array|string $cuenta
	 * @return array
	 */        
        function consultarSaldoActivo($cuenta){
                $condiciones = array(
                        "tipo_afiliacion" => "a",
                        "numero_cuenta" => $cuenta
                );
                $parametros = array(
                    'conditions' => $condiciones
                );
                $res = $this->find('first', $parametros);
                return $res['Afiliacion'];
        }
        
	/*
	 * Método para la consultar las Afiliaciones de Saldo Activo
	 * 
	 * @param array|string $cuenta
	 * @return array
	 */        
        function buscarAfiliacionesSaldoActivo($usuario){
                $condiciones = array(
                        "tipo_afiliacion" => "a",
                        "usuario_id" => $usuario
                );
                $parametros = array(
                    'conditions' => $condiciones
                );
                $res = $this->find('all', $parametros);
                return $res;
        }
        
        /*
	 * Método para la consultar las Afiliaciones de Pago Activo (Cliente)
	 * 
	 * @param array|string $usuario
         * @param array|string $id
	 * @return array
	 */        
        function buscarAfiliacionesPagoActivo($id){
                         
            $condiciones = array(
                "id" => $id               
            );
                       
            $parametros = array(
                'conditions' => $condiciones
            );
            
            $res = $this->find('first', $parametros);
            return $res;
        }
        
        /*
	 * Método para consultar las Cuentas Pagadoras Afiliadas a Pago Activo
	 * 	 
         * @param array|string $id usuario
	 * @return array
	 */        
        function buscarCuentasAfiliadasPagoActivo($id){
                     
            $res = $this->find(
                'all',
                array(
                    'fields' => array (
                        'Afiliacion.numero_cuenta',
                        'Afiliacion.numero_cuenta_ahorro',
                        ),
                    'conditions'=>array(
                        'Afiliacion.usuario_id'=>$id,
                        'Afiliacion.tipo_afiliacion'=>'m'
                        )                            
                )
            );
            
            return $res;
        }
        
        /*
	 * Método para consultar las Cuentas Pagadoras y Receptoras Afiliadas a Pago Activo
	 * 	 
         * @param array|string $id usuario
	 * @return array
	 */        
        function buscarCuentasPRAfiliadasPagoActivo($id){
                     
            $res = $this->find(
                'all',
                array(
                    'fields' => array (
                        'Afiliacion.numero_cuenta',
                        'Afiliacion.numero_cuenta_ahorro',
                        'Afiliacion.numero_cuenta_receptora'
                        ),
                    'conditions'=>array(
                        'Afiliacion.usuario_id'=>$id,
                        'Afiliacion.tipo_afiliacion'=>'m'
                        )                            
                )
            );
            
            return $res;
        }
        
	function listarxTipoOperacion($id_usuario, $tipo_afiliacion, $tipo_operacion, $afiliacion=null){
		
		App::import('Component', 'Session');
		
		$session = new SessionComponent();
			
		$juridico = $session->check('login.juridico');	
		$usuario = $session->read('usuarios.datos');
                
		$condiciones = array(
			'Afiliacion.tipo_afiliacion' => $tipo_afiliacion,
                        'Afiliacion.tipo_operacion' => $tipo_operacion
		);
		if($juridico){
			$condiciones['tipo_rif']=$usuario['tipo_rif'];
			$condiciones['rif']=$usuario['rif'];
		} else {
			$condiciones['usuario_id'] = $id_usuario;
		}
                
                if($afiliacion!=null){
                    $condiciones['id'] = $afiliacion;
                }
                
                if($tipo_operacion == 'p' || $afiliacion!=null){
                    $cantidad = 'first';
                }else{
                    $cantidad = 'all';
                }
		                
	        $x = $this->find(
			$cantidad,
			array(
                            'conditions'=>$condiciones
			),
			false
		);
		                
		return $x;
	}     

	public function checkCedula($id_usuario, $cedula, $tipoAfiliacion){
		App::import('Component', 'Session');
		$session = new SessionComponent();
		$usuario = $session->read('usuarios.datos');	
		
		$condiciones = array(
			'cedula' => encriptar($cedula),
			'usuario_id'=>$id_usuario,
			'tipo_afiliacion'=>$tipoAfiliacion,
		);


		$result = $this->find(
			'first',
			array(
				'fields' => array (
					'Afiliacion.id'
				),
				'conditions' => $condiciones
			),
			false
		);
		return ( isset($result['Afiliacion']) );
	}				


	public function checkContratoST($id_usuario, $contrato, $tipoOperacion){
		App::import('Component', 'Session');
		$session = new SessionComponent();
		$usuario = $session->read('usuarios.datos');	
		
		$condiciones = array(
			'numero_cuenta' => encriptar($contrato),
			'usuario_id'=>$id_usuario,
			'tipo_operacion'=>$tipoOperacion,
		);

		$result = $this->find(
			'first',
			array(
				'fields' => array (
					'Afiliacion.id'
				),
				'conditions' => $condiciones
			),
			false
		);

    	return ( isset($result['Afiliacion']) );
	}

public function checkCedulaTarjeta($id_usuario, $cedula, $tipoAfiliacion,$nroTarjeta){
		App::import('Component', 'Session');
		$session = new SessionComponent();
		$usuario = $session->read('usuarios.datos');	


		$condiciones = array(
			'cedula' => encriptar($cedula),
			'usuario_id'=>$id_usuario,
			'tipo_afiliacion'=>$tipoAfiliacion,
			'numero_cuenta'=>encriptar($nroTarjeta)
		);


		$result = $this->find(
			'first',
			array(
				'fields' => array (
					'Afiliacion.id'
				),
				'conditions' => $condiciones
			),
			false
		);
		return ( isset($result['Afiliacion']) );
	}    

	                   
	    
}
?>