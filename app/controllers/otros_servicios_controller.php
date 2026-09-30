<?php
/**
 * Controlador del alias y contrato 
 */
 
/**
 * Controlador del alias y contrato 
 */
class OtrosServiciosController extends AppController {
	
	/**
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array(
		'Usuario',
		'AliasProducto',
		'Bitacora'
	);

	/**
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Activo', 'Session');

	/**
	* @var array Ayudantes utilizados en las vistas de este controlador
	*/
	var $helpers = array('Activo','Html','Session','Form','Text');

	
	/**
	 * Acción para el contrato de aceptación del servicio Activo en Línea
	 */
	function contrato(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(23)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		$usuario = $this->Session->read('usuarios.datos');
		
		$this->Bitacora->registraBitacora($usuario['id'], 23);
	
		$this->layout = 'interna';

		$this->set('seccion_actual','consulta.contrato');
	}

	/**
	 * Acción para mostrar los alias de los productos
	 */
	function alias(){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(24)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		$this->layout = 'interna';

		$this->set('seccion_actual','asignacion.alias');
		
		$usuario = $this->Session->read('usuarios.datos');
		
		$userid = $usuario['id'];
		$arrData = array();
		
		// Busca los productos
		$res = $this->Session->read('usuarios.cuentas');
		if($res){
			$arrData = $res['SUMDSJVReturn']['SUMDSJV']['SUMDSJV'];
			normaliza($arrData);
		}
		
		$this->set(compact('userid', 'arrData'));
		
		if(isset($this->data['AliasProducto'])){
			$this->AliasProducto->set($this->data);

			if($this->AliasProducto->validates(array( 'fieldList'=>array('cuenta') ))){
				
				// verifica si ya esta registrado el alias en la BD
				$tempdata = $this->AliasProducto->buscaInfo($userid);
				if($tempdata){
					$this->data['AliasProducto'] = desencriptarCampos($tempdata['AliasProducto'], array('cuenta','alias'));
				}
				
				$this->Session->write('otros_servicios.alias',$this->data);
				$this->redirect('editar_alias');
			}
		}
	}

	/**
	 * Acción para editar los alias de los productos
	 */
	function editar_alias(){
		$this->layout = 'interna';

		$this->set('seccion_actual','asignacion.alias');
		
		$usuario = $this->Session->read('usuarios.datos');

		if(isset($this->data['AliasProducto'])){
			$this->AliasProducto->set($this->data);
			if($this->AliasProducto->validates()){
				if( $this->AliasProducto->checkAlias($usuario['id']) ){
					$this->AliasProducto->validationErrors['alias'] = 'alias_nodisponible';
				}
				else{
					$tempdata = $this->Session->read('otros_servicios.alias');
					$this->Session->write('otros_servicios.alias', array_merge($tempdata, $this->data));
					$this->redirect('alias_resultado');
				}
			}
		}
		else if($this->Session->check('otros_servicios.alias')){
			$this->data = $this->Session->read('otros_servicios.alias');
		}
		else{
			$this->redirect('alias');
		}
	}

	/**
	 * Acción para el resultado del cambio del alias de los productos
	 */
	function alias_resultado(){
		if($this->Session->check('otros_servicios.alias')){
			
			$usuario = $this->Session->read('usuarios.datos');
			
			$tempdata = $this->Session->read('otros_servicios.alias');
			
			$tempdata['AliasProducto']['usuario_id'] = $usuario['id'];
			
			$error = FALSE;
			
			// encripta los campos necesarios
			$this->data['AliasProducto'] = encriptarCampos($tempdata['AliasProducto'], array('alias', 'cuenta'));
			
			// si el alias viene vacio lo coloca como nulo
			if($this->data['AliasProducto']['alias']=='') $this->data['AliasProducto']['alias'] = NULL;
			
			$this->AliasProducto->set($this->data);
			
			if ( $this->AliasProducto->save(null,false) ){
				$this->Bitacora->registraBitacora($usuario['id'], 24, NULL, $tempdata['AliasProducto']['cuenta']);
			}
			else{
				$this->log('error al insertar/modificar el alias del producto' + $usuario['id'], LOG_ERR);
				$this->log($this->AliasProducto->getDataSource()->error);
				$error = TRUE;
			}
			
			$this->layout = 'interna';

			$this->set('seccion_actual','asignacion.alias');
			$this->set('error',$error);
			
			$this->Session->delete('otros_servicios.alias');
		}
		else {
			$this->redirect('alias');
		}
	}
        
	function aviso_nuevo_contrato(){
            $this->layout = 'auditoria';
	    
	    if(!empty($this->data['Usuario'])){
                $usuario = $this->Session->read('usuarios.datos');	
                
                if ($this->Usuario->actualizar_nuevo_contrato($usuario['id'])){

                    $this->Bitacora->registraBitacora(
                        $usuario['id'],
                        100040, 
                        null,
                        null,
                        null,
                        null,
                        'Aceptar Aviso Nuevo Contrato',
                        0
                    );

                    if(_p('proceso_auditoria')){

                         $usuario = $this->Session->read('usuarios.datos');
                         /** Para identificar si es un usuario juridico **/
                         if(isset($usuario['tipo_rif']) && isset($usuario['rif'])){
                                $identificacion=$usuario['tipo_rif'].desencriptar($usuario['rif']);
                                $loginUsuario = desencriptar($usuario['login']);
                                $idUsuario = $usuario['id'];
                         } else {
                                $identificacion = $usuario['tipo_cedula'].str_pad(desencriptar($usuario['cedula']),9,'0',STR_PAD_LEFT);
                                $loginUsuario = desencriptar($usuario['login']);
                                $idUsuario = $usuario['id'];
                         }
                         /** Para identificar si es un usuario juridico **/

                         $datos_cliente = $this->Session->read('usuarios.nibpf3010');
                         $id_cliente_IBS = intval($datos_cliente['NIBPF3010Return']['NIBPF3010']['NROCLIENTE']);

                         $cedula = desencriptar($usuario['cedula']);
                         $rif = desencriptar($usuario['rif']);

                         $verificarVoto = $this->Activo->verificarVoto($id_cliente_IBS, $identificacion, $loginUsuario);
                         $opcion = trim($verificarVoto['NIB2090PFReturn']['NIB2090PF_2']['AUDCDE']);

                         if($opcion == '03'){
                                 $this->redirect(array('controller'=>'auditores_externos','action'=>'auditores_externos'));
                         }else{
                                 $this->redirect(array('controller'=>'consultas','action'=>'integral'));
                         }
                     }else{
                         $this->redirect(array('controller'=>'consultas','action'=>'integral'));

                     }//Fin auditoría externa
                }else{
                    $this->redirect('/otros_servicios/aviso_nuevo_contrato');
                }
            }
     }
}

?>