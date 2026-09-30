<?php
/**
 * Controlador de Auditores Externos
 */
 

 /**
 * Controlador de Auditores Externos
 */
 class  AuditoresExternosController extends AppController{

	/**
	 *
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array('Usuario', 'Bitacora','AuditoresExternos');

	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session');

	/**
	 *
	 * @var array Ayudantes utilizados en las vistas de este controlador
	 */
	var $helpers = array('Html','Session','Form','Text');

	/*
	 * Acción para mostrar el listado de empresas participantes en el proceso de auditores externos
	 * @param string $volver Indica si está regresando de la confirmación
	 */
	function auditores_externos($volver=null){
	
		$this->layout = 'auditoria';
		$this->set('seccion_actual','auditores.externos');
		$res = $this->Activo->invoca(
					'NIB2090PFEMPRESAS',
					array(
						'req'=>array(
							'AUDOPC'=>'0003'
						)
					)
				);
								
				$arr = array();
				$empresas = array();
				
				if( isset($res) && !empty($res) ){
					$arr= $res['NIB2090PFEMPRESASReturn']['NIB2090PF']['NIB2090PF'];
					
					normaliza($arr);
				} else {
					$arr = array();
				}
		
				foreach($arr as $emp){
					$empresas[] = $emp;
				}

				$this->set(compact('empresas'));
				
				
			
		if(!empty($this->data['AuditoresExternos'])){
			$this->AuditoresExternos->set($this->data);
			
			if ($this->AuditoresExternos->validates(array('fieldList' => array('empresa_seleccion')))) {
				
				
				$empresa = $this->data['AuditoresExternos']['empresa_seleccion'];
				$longitud = strlen($empresa);				
				 $id_empresa = substr($empresa, 0, 3);
				 $nombre_empresa = substr($empresa, 4, $longitud);
				
				 $this->data['AuditoresExternos']['empresa_seleccionada'] = $nombre_empresa;
				 $this->data['AuditoresExternos']['id_empresa'] = $id_empresa;
				
				$this->Session->write('auditores.externos', $this->data);

				$this->redirect('auditores_externos_confirmacion');
				
			}
			
			
		}else if(isset($volver) && $this->Session->check('auditores.externos')){
			$this->data = $this->Session->read('auditores.externos');
		}	
	}
	
	
	/**
	 * Método para realizar la confirmación de la empresa seleccionada.
	 */
	function auditores_externos_confirmacion(){
		
		$this->layout = 'auditoria';

		$this->set('seccion_actual','auditores.externos');	

		if($this->Session->check('auditores.externos')){
			$this->data = $this->Session->read('auditores.externos');

			
		} else {
			$this->redirect('auditores_externos');
		}

	}

	/**
	 * Método para realizar el proceso de votación para seleccionar la empresa auditora.
	 */
	function auditores_externos_resultado() {

		$this->layout = 'auditoria';
		
		$this->set('seccion_actual','auitores.externos');
		if($this->Session->check('auditores.externos')){
			$this->data = $this->Session->read('auditores.externos');
			
			App::import('Component', 'Session');
			$session = new SessionComponent();
			
			$usuario = $session->read('usuarios.datos');	
			$datos_cliente = $this->Session->read('usuarios.nibpf3010');
			$id_cliente_IBS = intval($datos_cliente['NIBPF3010Return']['NIBPF3010']['NROCLIENTE']);

			
			/** Para identificar si es un usuario juridico **/
			if($usuario['tipo_rif']){
				$identificacion=$usuario['tipo_rif'].desencriptar($usuario['rif']);
				$nombreUsuario = desencriptar($usuario['nombre']);
				$loginUsuario = desencriptar($usuario['login']);
			} else {
				$identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
				$nombreUsuario = desencriptar($usuario['nombre']);
				$loginUsuario = desencriptar($usuario['login']);
			}
			/** Para identificar si es un usuario juridico **/
			
			
			$empresa = $this->data['AuditoresExternos']['empresa_seleccionada'];
			$id_empresa = $this->data['AuditoresExternos']['id_empresa'];
			
			$actualizar = $this->Activo->actualizarVoto($id_cliente_IBS, $identificacion, $loginUsuario,$nombreUsuario, $empresa, $id_empresa);

			$resultadoVotacion = -1;
			$descripcion = "Error inesperado, transacción no ejecutada.";

			$resultadoVotacion = $actualizar['NIB2090PFReturn']['NIB2090PF_2']['AUDCDE'];

			
			if($resultadoVotacion == '01'){
				$descripcion='Auditores Externos - ' . $identificacion ;
				$this->Bitacora->registraBitacora(
				$usuario['id'],
				100034, 
				null,
				null,
				null,
				null,
				$descripcion,
				0
				);			
			}else{
				$this->log('Auditores Externos - ' . $identificacion. ' - '.$descripcion, LOG_ERR);
				$this->Bitacora->registraBitacora(
					$usuario['id'],
					100034,
					null,
					null,
					null,
					NULL,
					$descripcion,
					1
				);
			}		
			
			$this->set(compact('resultadoVotacion'));
		}else {
			$this->redirect('auditores_externos');
		}		
	}


	
	


}
 
 ?>
