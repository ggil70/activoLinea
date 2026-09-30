<?php
/**
 * Controlador de empresas para el manejo de usuarios
 */
 
/**
 * Controlador de empresas para el manejo de usuarios
 */
class EmpresaController extends AppController{
	
    
	/**
	 * @var array Modelos que usa el controlador 
	 */
	var $uses = array(
		'Empresa',
		'Usuario', 
		'TransaccionPerfil', 
		'RolJuridico', 
		'UsuarioCanal', 
		'TransaccionJuridico', 
		'TransaccionJuridicoDetalle', 
		'Bitacora',
        'LimitesVip'
	);
	
        
	/**
	 *
	 * @var array Componentes que utiliza este controlador
	 */
	var $components = array('Session','Activo','Idg', 'Email','MigracionJuridico','Sms');
	
	/**
	*
	* @var array Ayudantes utilizados en las vistas de este controlador
	*/
	var $helpers = array('Activo','Html','Session','Form','Text');
	
	
	/**
	 * Acción para el listado de usuarios de una empresa
	 */
	function index(){
		$this->redirect('listado');		
	}

	/**
	* Acción del Listado de usuarios de una empresa asociada al banco empresa  
	*/
	function listado(){
		$usuario= $this->Session->read('usuarios.datos');
	
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(37)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */

		// INICIO validacion tarjetas de coordenadas
		$validacion_tdc = $this->Session->read('seguridad.validar_tdc');
		if(
                    $validacion_tdc == null ||
                    !isset($validacion_tdc['validado']) ||
                    !$validacion_tdc['validado']
		){
                    $this->Session->write(
                        'seguridad.validar_tdc',
			array(
                            'url_aceptar'=>'/empresa/listado',
                            'layout'=>'interna',
                            'titulo'=>'Configuración Usuarios',
                            'pasos'=> array(),
                            'paso_actual'=>0,
                            'seccion_actual'=>'empresa.crear'
			)
                    );
			//$this->redirect('/seguridad/validar_tdc');
		}
		// FIN validacion tarjetas de coordenadas
		
		// registra los datos del usuario en bitacora
		$this->Bitacora->registraBitacora($usuario['id'], 37);
		
		$this->Session->delete('usuarios.descarga.pdf');
		$this->Session->delete('empresa.transacciones.generales');
		$accion = '';
                
		if(!empty($this->data['Usuario']) && !isset($this->data['Usuario']['paginando'] )){
                    switch($this->data['Usuario']['accion']){
                        case 'crear':{
                            
                            $this->Session->delete('empresa.usuarios');		
                            $this->Session->write('empresa.estado',
                                array(
                                    'operacion' => 'Agregar',
                                    'pasos' => array(
                                    1=>'Agregar Usuario',
                                    2=>'Configuración',
                                    3=>'Confirmación',
                                    4=>'Resultado Operación',
                                    )
                                )
                            );
					
                            $this->redirect('usuario');
                            break;
			            }
			case 'editar':{		
                            $this->Session->write('empresa.estado',
                                    array(
                                        'operacion' => 'Modificar',
                                        'pasos' => array(
                                        1=>'Modificar Usuario',
                                        2=>'Configuración',
                                        3=>'Confirmación',
                                        4=>'Resultado Operación',
                                        )
                                    )
                            );
					
                            if(!empty($this->data['Usuario']['id'])){
                                $usuarioEmpresa = $this->Empresa->buscarUsuario( $this->data['Usuario']['id'], $usuario['tipo_rif'], $usuario['rif']);
                                if($usuarioEmpresa){
                                    // busca las transacciones del usuario y redirige al formulario
                                    $usuarioEmpresa['Usuario']['ck'] = $this->TransaccionJuridicoDetalle->obtenerTransacciones($usuarioEmpresa['Usuario']['id'], Configure::read('transacciones_basicas.rol.default'));
                                    $this->Session->write('empresa.usuarios', $usuarioEmpresa);
                                    $this->redirect('usuario');
                                }
                            }else{
                                $accion = 'modificado';
                                $this->Usuario->validationErrors['id'] = _m('no.editar',$accion);
                            }
                            break;
			}				
				
			case 'ver':{		
                            $this->Session->write('empresa.estado',
				array(
                                    'operacion' => 'Modificar',
                                    'pasos' => array(
                                        3=>'Confirmación',
                                    )
				)
                            );
					
                            if(!empty($this->data['Usuario']['id'])){
                                $usuarioEmpresa = $this->Empresa->buscarUsuario( $this->data['Usuario']['id'], $usuario['tipo_rif'], $usuario['rif']);
                                if($usuarioEmpresa){
                                    // busca las transacciones del usuario y redirige al formulario
                                    $usuarioEmpresa['Usuario']['ck'] = $this->TransaccionJuridicoDetalle->obtenerTransacciones($usuarioEmpresa['Usuario']['id'], Configure::read('transacciones_basicas.rol.default'));
                                    $this->Session->write('empresa.usuarios', $usuarioEmpresa);
                                    $this->redirect('ver_usuario');
				}
                            }else{
                                    $accion = 'modificado';
                                    $this->Usuario->validationErrors['id'] = _m('no.editar',$accion);
				}
                            break;
			}
			case 'eliminar':{		
                            $this->Session->write('empresa.estado',
                                array(
                                    'operacion' => 'Eliminar',
                                    'pasos' => array(
                                        1=>'Confirmación',
                                        2=>'Resultado Operación',
                                    )
                                )
                            );
                            if(!empty($this->data['Usuario']['id'])){
                                $usuarioEmpresa = $this->Empresa->buscarUsuario($this->data['Usuario']['id'], $usuario['tipo_rif'], $usuario['rif']);
                                if($usuarioEmpresa){
                                    $this->Session->write('empresa.usuarios', $usuarioEmpresa);
                                    $this->redirect('confirmacion');
                                }
                            }else{
				$accion = 'eliminado';
                                    $this->Usuario->validationErrors['id'] = _m('no.editar', $accion);
				}
                            break;
			}
                        case 'cambio_estatus':{
                            if(!empty($this->data['Usuario']['id'])){
                            $usuarioEmpresa = $this->Empresa->buscarUsuario($this->data['Usuario']['id'], $usuario['tipo_rif'], $usuario['rif']);
                            if($usuarioEmpresa){
                                $this->Session->write('empresa.usuarios', $usuarioEmpresa);
				$this->redirect('cambio_form');
                            }
                            break;
			}else{
                            $accion = 'cambiado de estatus';
				$this->Usuario->validationErrors['id'] = _m('no.editar', $accion);
                            }
			}
                    }
		}
		
		$this->layout = 'interna';
		
		$this->set('seccion_actual','empresa.crear');

		$estatus = $this->Empresa->estatus;
		
		$total_registros = $this->Usuario->find('count', array(
                    'conditions'=>array(
                    'Usuario.tipo_rif'=> $usuario['tipo_rif'],
                    'Usuario.rif'=> $usuario['rif'],
                        'or'=>array(
                            'and'=>array(
                                'usuarioCanal.estatus_acceso is null',
                                'usuario.rol_juridico_id not in (1,2)' // para no mostrar master/masterunico eliminado
                            ),
                            'UsuarioCanal.estatus_acceso <>'=>'I1'
			)
                    ),
                    'joins' => array(
			array(
                            'table' => 'usuarios_canales',
                            'alias' => 'UsuarioCanal',
                            'type' => 'LEFT',
                            'conditions' => array(
                                'UsuarioCanal.usuario_id = Usuario.id'
                            )
			)
                    )
		));
		
		$offset = 0;

		if(
                    isset($this->data['Usuario']['offset']) &&
                    preg_match('/^\d+$/',$this->data['Usuario']['offset'])
		){
                    $offset = intval($this->data['Usuario']['offset']);			
		}
		
		$listado = array();
		
		if($total_registros){
                    $listado = $this->Empresa->listadoUsuariosJuridicos($usuario['tipo_rif'],$usuario['rif'],$offset);
		}
		foreach($listado as $l){
                    $rolJuridico = $l['rj']['id'];
		}
		
		//$rolJuridico = $usuario['RolJuridico']['id'];

		$this->set(compact('listado', 'estatus', 'total_registros','rolJuridico', 'accion'));
	}

	/**
	 * Acción para crear/modificar la información de los usuarios
	 * @param type $volver
	 */
	function usuario($volver=''){
		
		/* Para validar el acceso al modulo */
		if(!validarTransaccion(37)) $this->redirect('/consultas/integral');
		/* Para validar el acceso al modulo */
		
		// Verifica si existe la sesion del usuario
		if(!$this->Session->check('usuarios.datos') || !$this->Session->check('empresa.estado')) $this->redirect('/consultas/integral');
		
		$usuario= $this->Session->read('usuarios.datos');
		
		$operacion = $this->Session->read('empresa.estado');
		
		if( empty($this->data['Usuario']) ){
                    if(isset($volver) && $this->Session->check('empresa.usuarios')){
                        $this->data = $this->Session->read('empresa.usuarios');
                        unset($this->data['Usuario']['ck']);
                    } elseif($operacion['operacion']=='Modificar' && $this->Session->check('empresa.usuarios')){
                        $this->data = $this->Session->read('empresa.usuarios');
                        }
		} else {
			
                    // Asigna la info del rif para la validacion del login
                    $this->data['Usuario']['tipoRif'] = $this->sanitize_input($usuario['tipo_rif']);
                    $this->data['Usuario']['nroRif'] = $this->sanitize_input(desencriptar($usuario['rif']));
			
                    $this->Usuario->set($this->data);
			
                    $validaciones = array();
			
                    switch($operacion){
                        case 'Agregar':
                            $validaciones = array(
				'nomUsuario',
				'tipoCedulaRif',
				'nroCedulaRif',
				'emailPri',
				'nroCelular',
				'codigoCelular',
				'cargo',
				'nombre',
                            );
					
                            break;
                        case 'Modificar':
                            $validaciones = array(
				'nomUsuario',
				'emailPri',
				'nroCelular',
				'codigoCelular',
				'cargo',
				'nombre',
                            );
					
			break;
			}
			
			// Validacion de los campos
			if ($this->Usuario->validates(array('fieldList' => $validaciones))) {
			
                            if(
                                isset($this->data['Usuario']['tipoCedulaRif']) &&
				isset($this->data['Usuario']['nroCedulaRif'])
                            ){
                            // Verifica si existe un usuario con la cedula
                            $usuario = $this->UsuarioCanal->find(
                                    'first',
                                    array(
                                        'conditions'=>array(
                                            'Usuario.tipo_rif'=>$this->sanitize_input($usuario['tipo_rif']),
                                            'Usuario.rif'=> $this->sanitize_input($usuario['rif']),
                                            'Usuario.tipo_cedula'=> $this->sanitize_input($this->data['Usuario']['tipoCedulaRif']),
                                            'Usuario.cedula'=>encriptar($this->sanitize_input($this->data['Usuario']['nroCedulaRif'])),
                                            'UsuarioCanal.canal_id'=>Configure::read('canal.ib'),
                                            'UsuarioCanal.estatus_acceso !=' => 'I1',
                                            'Usuario.login'=>encriptar($this->sanitize_input($this->data['Usuario']['nomUsuario'])),
                                        )
                                    )
                                );
					
                                if($usuario){
                                    $this->Usuario->validationErrors['nroCedulaRif'] = _m('juridico.ya.registrado');
                                }
                            }
				
                            // Combinacion de la data para mostrar en los siguientes pasos
                            if($operacion['operacion']=='Modificar'){
                                $temp = $this->Session->read('empresa.usuarios');
                                $this->data['Usuario'] = array_merge($temp['Usuario'],$this->data['Usuario']);
					
                                // Verificar si cambió el email y el usuario está en estatus pendiente
                                if(
                                    $temp['Usuario']['emailPri'] != $this->data['Usuario']['emailPri'] &&
                                    $temp['Usuario']['co_estado'] == 'P'
                                ){
                                    $this->data['Usuario']['enviar_mail_registro'] = true;
                                }
					
                                if(
                                   $this->data['Usuario']['emailPri'] != $temp['Usuario']['emailPri'] &&
                                    $this->data['Usuario']['codigoCelular'].$this->data['Usuario']['nroCelular'] != $temp['Usuario']['codigoCelular'].$temp['Usuario']['nroCelular']
                                ){
                                    $this->data['Usuario']['tipo_cambio'] = 'ambos';
                                    $this->data['Usuario']['canal_sms'] = '39';
                                    $this->data['Usuario']['email_anterior'] = $temp['Usuario']['emailPri'];
                                    $this->data['Usuario']['celular_anterior'] = $temp['Usuario']['codigoCelular'].$temp['Usuario']['nroCelular'];
                                } elseif($this->data['Usuario']['emailPri'] != $temp['Usuario']['emailPri']){
                                    $this->data['Usuario']['tipo_cambio'] = 'email';
                                    $this->data['Usuario']['canal_sms'] = '37';
                                    $this->data['Usuario']['email_anterior'] = $temp['Usuario']['emailPri'];
                                } elseif($this->data['Usuario']['codigoCelular'].$this->data['Usuario']['nroCelular'] != $temp['Usuario']['codigoCelular'].$temp['Usuario']['nroCelular']){
                                    $this->data['Usuario']['tipo_cambio'] = 'celular';
                                    $this->data['Usuario']['canal_sms'] = '38';
                                    $this->data['Usuario']['celular_anterior'] = $temp['Usuario']['codigoCelular'].$temp['Usuario']['nroCelular'];
                                    }
				}
				
				$this->data['Usuario']['nomUsuario']= strtolower($this->data['Usuario']['nomUsuario']);
				
				if(empty($this->Usuario->validationErrors)){
					$perfiles = $this->RolJuridico->listadoRoles();
					
					if(!array_key_exists($this->data['Usuario']['perfil'],$perfiles)){
						$this->redirect('/consultas/integral');
					}else{
	                    // guarda la info del usuario en sesion
	                    $this->Session->write('empresa.usuarios',$this->data);
	                    $this->redirect('configuracion');
					}
				}
                            }
                        }

        $this->layout = 'interna';

        $this->set('seccion_actual','empresa.crear');
        $perfiles = $this->RolJuridico->listadoRoles();

	    $this->set(compact('operacion','perfiles'));
	}
	
	/**
	 * Acción para consultar datos del usuario
	 **/
	function ver_usuario(){
		
            // Verifica si existe la sesion del usuario
            if(!$this->Session->check('usuarios.datos') || !$this->Session->check('empresa.usuarios')) $this->redirect('/empresa/listado');
		
            $usuario= $this->Session->read('usuarios.datos');
		
            $usuarioEmpresa = $this->Session->read('empresa.usuarios');
			
			
            $usuarioEmpresa['operacion']= "Detalle de usuario";
            $this->Session->write('usuarios.descarga.pdf',$usuarioEmpresa);
		
            $this->layout = 'interna_impresion';
		
            $this->set('seccion_actual','empresa.crear');
		
            $operacion = $this->Session->read('empresa.estado');
		
            $perfiles = $this->RolJuridico->listadoRoles();
		
            if($operacion['operacion']!='Eliminar'){
		$transacciones = $this->TransaccionPerfil->moduloTransacciones($usuario, $usuarioEmpresa['Usuario']['perfil']);
            }
				
            $limite_externas = $usuario['limite_externas'] ? $usuario['limite_externas'] : _p('limite_externas_J');
            $limite_internas_terceros = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : _p('limite_internas_terceros_J');
                
            $limite_externas_nomina = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
            $limite_internas_terceros_nomina = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                
            $limite_externas_proveedores = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
            $limite_internas_terceros_proveedores = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');                                
                
            if($limite_externas) $limite_externas = formatea_monto_fromWs($limite_externas);
            if($limite_internas_terceros) $limite_internas_terceros = formatea_monto_fromWs($limite_internas_terceros);						
            if($limite_externas_nomina) $limite_externas_nomina = formatea_monto_fromWs($limite_externas_nomina);
            if($limite_internas_terceros_nomina) $limite_internas_terceros_nomina = formatea_monto_fromWs($limite_internas_terceros_nomina);
            if($limite_externas_proveedores) $limite_externas_proveedores = formatea_monto_fromWs($limite_externas_proveedores);
            if($limite_internas_terceros_proveedores) $limite_internas_terceros_proveedores = formatea_monto_fromWs($limite_internas_terceros_proveedores);
		
            if(is_numeric($usuario['limite_vip'])) {
                $limites_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                $limite_externas_nomina = formatea_monto_fromWs($limites_vip['limite_externas_nomina']);
                $limite_internas_terceros_nomina = formatea_monto_fromWs($limites_vip['limite_internas_terceros_nomina']);						
                $limite_externas_proveedores = formatea_monto_fromWs($limites_vip['limite_externas_proveedores']);
                $limite_internas_terceros_proveedores = formatea_monto_fromWs($limites_vip['limite_internas_terceros_proveedores']);
            }
                    
		
            $this->set(compact('transacciones','operacion','perfiles','usuarioEmpresa','usuario','limite_externas','limite_internas_terceros','limite_externas_nomina','limite_internas_terceros_nomina','limite_externas_proveedores','limite_internas_terceros_proveedores'));
	}
	
	
	/**
	 * Acción para asignar las funcionalidades a los usuarios
	 * 
	 */
	function configuracion(){
		
            // Verifica si existe la sesion del usuario
            if(!$this->Session->check('usuarios.datos') || !$this->Session->check('empresa.usuarios')) $this->redirect('/empresa/listado');
		
            $usuario = $this->Session->read('usuarios.datos');
		
            $usuarioEmpresa = $this->Session->read('empresa.usuarios');
		
            $this->layout = 'interna';
		
            $this->set('seccion_actual','empresa.crear');
		
            $operacion = $this->Session->read('empresa.estado');
		
            $perfiles = $this->RolJuridico->listadoRoles();
		
		
            if( !empty($this->data['Usuario']['confirmado']) ){
			
		// elimina las transacciones no seleccionadas
		$arrayTemp = $this->data['Usuario']['ck'];
		foreach ($this->data['Usuario']['ck'] as $k => $v){
                    if(!isset($v['menu'])) unset($arrayTemp[$k]);
		}
		$this->data['Usuario']['ck'] = $arrayTemp;
		
		$dataTemp['Usuario'] = array_merge($usuarioEmpresa['Usuario'],$this->data['Usuario']);
			
		// guarda la info del usuario en sesion
                    $this->Session->write('empresa.usuarios', $dataTemp);
                    $this->redirect('confirmacion');
            }
		
            $this->data = $usuarioEmpresa;
                                                
            $limite_externas = $usuario['limite_externas'] ? $usuario['limite_externas'] : _p('limite_externas_J');
            $limite_internas_terceros = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : _p('limite_internas_terceros_J');
                
            $limite_externas_nomina = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
            $limite_internas_terceros_nomina = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
             
            $limite_internas_terceros_nomina_usd = $usuario['limite_internas_terceros_nomina_usd'] ? $usuario['limite_internas_terceros_nomina_usd'] : _p('limite_internas_terceros_nomina_usd');
            
            $limite_externas_proveedores = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
            $limite_internas_terceros_proveedores = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');
                
            $limite_vip = $usuario['limite_vip'] ? $usuario['limite_vip'] : '';
                
            if($limite_externas) $limite_externas = formatea_monto_fromWs($limite_externas);
            if($limite_internas_terceros) $limite_internas_terceros = formatea_monto_fromWs($limite_internas_terceros);						
            if($limite_externas_nomina) $limite_externas_nomina = formatea_monto_fromWs($limite_externas_nomina);
            if($limite_internas_terceros_nomina) $limite_internas_terceros_nomina = formatea_monto_fromWs($limite_internas_terceros_nomina);
            if($limite_internas_terceros_nomina_usd) $limite_internas_terceros_nomina_usd = formatea_monto_fromWs($limite_internas_terceros_nomina_usd);
            
            
            if($limite_externas_proveedores) $limite_externas_proveedores = formatea_monto_fromWs($limite_externas_proveedores);
            if($limite_internas_terceros_proveedores) $limite_internas_terceros_proveedores = formatea_monto_fromWs($limite_internas_terceros_proveedores);						
		 
            if(!empty($limite_vip)) {
                $limites_vip = $this->LimitesVip->buscarLimiteById($limite_vip);                    						
                $limite_externas_nomina = formatea_monto_fromWs($limites_vip['limite_externas_nomina']);
                $limite_internas_terceros_nomina = formatea_monto_fromWs($limites_vip['limite_internas_terceros_nomina']);
                $limite_internas_terceros_nomina_usd = formatea_monto_fromWs($limites_vip['$limite_internas_terceros_nomina_usd']);
                
                $limite_externas_proveedores = formatea_monto_fromWs($limites_vip['limite_externas_proveedores']);
                $limite_internas_terceros_proveedores = formatea_monto_fromWs($limites_vip['limite_internas_terceros_proveedores']);
            }
                
            $transacciones = $this->TransaccionPerfil->moduloTransacciones($usuario, $usuarioEmpresa['Usuario']['perfil']);
		
            $this->set(compact('transacciones','operacion','perfiles','limite_externas','limite_internas_terceros','limite_externas_nomina','limite_internas_terceros_nomina','limite_externas_proveedores','limite_internas_terceros_proveedores'));
	}

        
	/**
	 * Acción de confirmación de la creación/modificación de los usuarios
	 */
	function confirmacion(){
		
            // Verifica si existe la sesion del usuario
            if(!$this->Session->check('usuarios.datos') || !$this->Session->check('empresa.usuarios')) $this->redirect('/empresa/listado');
		
            $usuario= $this->Session->read('usuarios.datos');
		
            $usuarioEmpresa = $this->Session->read('empresa.usuarios');
		
            $this->layout = 'interna';
		
            $this->set('seccion_actual','empresa.crear');
		
            $operacion = $this->Session->read('empresa.estado');
		
            $perfiles = $this->RolJuridico->listadoRoles();
		
            if($operacion['operacion']!='Eliminar'){
		        $transacciones = $this->TransaccionPerfil->moduloTransacciones($usuario, $this->sanitize_input($usuarioEmpresa['Usuario']['perfil']));
            }
		
            $limite_externas = $usuario['limite_externas'] ? $usuario['limite_externas'] : _p('limite_externas_J');
            $limite_internas_terceros = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : _p('limite_internas_terceros_J');
                
            $limite_externas_nomina = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
            $limite_internas_terceros_nomina = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                
            $limite_externas_proveedores = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
            $limite_internas_terceros_proveedores = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');                                
                
            if($limite_externas) $limite_externas = formatea_monto_fromWs($limite_externas);
            if($limite_internas_terceros) $limite_internas_terceros = formatea_monto_fromWs($limite_internas_terceros);						
            if($limite_externas_nomina) $limite_externas_nomina = formatea_monto_fromWs($limite_externas_nomina);
            if($limite_internas_terceros_nomina) $limite_internas_terceros_nomina = formatea_monto_fromWs($limite_internas_terceros_nomina);						
            if($limite_externas_proveedores) $limite_externas_proveedores = formatea_monto_fromWs($limite_externas_proveedores);
            if($limite_internas_terceros_proveedores) $limite_internas_terceros_proveedores = formatea_monto_fromWs($limite_internas_terceros_proveedores);						
		  
            if(isset($usuario['limite_vip']) && is_numeric($usuario['limite_vip'])) {
                $limites_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                $limite_externas_nomina = formatea_monto_fromWs($limites_vip['limite_externas_nomina']);
                $limite_internas_terceros_nomina = formatea_monto_fromWs($limites_vip['limite_internas_terceros_nomina']);						
                $limite_externas_proveedores = formatea_monto_fromWs($limites_vip['limite_externas_proveedores']);
                $limite_internas_terceros_proveedores = formatea_monto_fromWs($limites_vip['limite_internas_terceros_proveedores']);
            }
                
            $this->set(compact('transacciones','operacion','perfiles','usuarioEmpresa','limite_externas','limite_internas_terceros','limite_externas_nomina','limite_internas_terceros_nomina','limite_externas_proveedores','limite_internas_terceros_proveedores'));
	}

	
	/**
	 * Acción de resultado de la creación/modificación de los usuarios
	 */
	function resultado(){
		
            // Verifica si existe la sesion del usuario
            if(!$this->Session->check('usuarios.datos') || !$this->Session->check('empresa.usuarios')) $this->redirect('/empresa/listado');
		
            $usuario= $this->Session->read('usuarios.datos');
		
            $usuarioEmpresa = $this->Session->read('empresa.usuarios');
		
            $this->layout = 'interna';
		
            $this->set('seccion_actual','empresa.crear');
		
            $operacion = $this->Session->read('empresa.estado');		

            $operacion_exitosa = false;
		
            switch ($operacion['operacion']){
			
                case 'Agregar':	
                    
                    // Info del usuario
                    $usuario_nuevo = array(
                        'Usuario'=>array(
                            'nombre'=>encriptar($this->sanitize_input($usuarioEmpresa['Usuario']['nombre'])),
                            'celular'=>encriptar($this->sanitize_input($usuarioEmpresa['Usuario']['codigoCelular'].$usuarioEmpresa['Usuario']['nroCelular'])),
                            'login'=>encriptar($this->sanitize_input($usuarioEmpresa['Usuario']['nomUsuario'])),
                            'email_principal'=>encriptar($this->sanitize_input($usuarioEmpresa['Usuario']['emailPri'])),
                            'tipo_rif'=> $this->sanitize_input($usuario['tipo_rif']),
                            'rif'=> $this->sanitize_input($usuario['rif']),
                            'tipo_cedula'=> $this->sanitize_input($usuarioEmpresa['Usuario']['tipoCedulaRif']),
                            'cedula'=>encriptar($this->sanitize_input($usuarioEmpresa['Usuario']['nroCedulaRif'])),
                            'sello_id'=>NULL,
                            'sello_1'=>NULL,
                            'sello_2'=>NULL,
                            'sello_3'=>NULL,
                            'fecha_hora_creacion'=>date('Y-m-d H:i:s'),
                            'perfil_ib_id'=> $this->sanitize_input($usuario['perfil_ib_id']),
                            'rol_juridico_id'=> $this->sanitize_input($usuarioEmpresa['Usuario']['perfil']),
                            'cargo'=>encriptar($this->sanitize_input($usuarioEmpresa['Usuario']['cargo']))
                        )
                    );
				
                    $usuario_canal = array(
                        'UsuarioCanal'=>array(
                            'canal_id'=>Configure::read('canal.ib'),
                            'estatus_registro'=>'I',
                            'estatus_acceso'=>'P',
                            'limite_internas_terceros'=>NULL,
                            'limite_externas'=>NULL,
                            'fecha_creacion'=>date('Y-m-d H:i:s'),
                            )
                    );
				
                    // Buscar si el usuario ya existía para reemplazarlo
				
                    $usuario_bd = $this->Usuario->find(
                        'first',
                        array(
                            'conditions'=>array(
                               'tipo_rif'=>$this->sanitize_input($usuario['tipo_rif']),
                               'rif'=> $this->sanitize_input($usuario['rif']),
                               'tipo_cedula'=> $this->sanitize_input($usuarioEmpresa['Usuario']['tipoCedulaRif']),
                               'cedula'=>encriptar($this->sanitize_input($usuarioEmpresa['Usuario']['nroCedulaRif'])),
                               'login'=>encriptar($this->sanitize_input($usuarioEmpresa['Usuario']['nomUsuario'])),
                        	)
                        )
                    );
				
                    if($usuario_bd){
			$usuario_nuevo['Usuario']['id'] = $usuario_bd['Usuario']['id'];
					
			$usuario_canal_bd = $this->UsuarioCanal->find(
                            'first',
				array(
                            	'conditions'=>array(
                                    'usuario_id'=>$usuario_bd['Usuario']['id'],
                                    'canal_id'=>Configure::read('canal.ib')
                                    )
				)
			);
					
					
			if($usuario_canal_bd){
                            $usuario_canal['UsuarioCanal']['id'] = $usuario_canal_bd['UsuarioCanal']['id'];
			}
                    }
				
                    $clave_acceso = $this->Empresa->generaClave();
								
                    $identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']) . $usuarioEmpresa['Usuario']['tipoCedulaRif'] . $usuarioEmpresa['Usuario']['nroCedulaRif'] . $usuarioEmpresa['Usuario']['nomUsuario'];

                    $ds = $this->Usuario->getDataSource();
				
                    $ds->begin($this->Usuario);
				
                    // Genera el registro del usuario
                    $res = $this->Usuario->save($usuario_nuevo,false);
				
                    if($res){
                        // creacion de usuario en IDG
			if($this->Idg->usuarioExiste($identificacion)){
                            $res_idg = true;
			} else {
                            $res_idg = $this->Idg->crearUsuario($identificacion);
			}
					
                        if($res_idg){
						
                            // Creacion de password
                            $res_pwd = $this->Idg->crearContrasena(
                                $identificacion,
                                $clave_acceso,
                                30
                            );
						
                            if( !$res_pwd && $this->Idg->codigo_error == 'OBJECT_EXIST' ){
                                $res_pwd = $this->Idg->inicializaContrasena(
                                    $identificacion,
                                    $clave_acceso,
                                    30
                                );
                            }
						
			if($res_pwd){
                            // Almacena la data del usuario
                            $ds->commit($this->Usuario);
							
                            if(!$usuario_bd){
				$usuario_canal['UsuarioCanal']['usuario_id']= $this->Usuario->id;
                            }
							
                            // Almacena el registro del usuario por canal
                            if( !$this->UsuarioCanal->save($usuario_canal,false) ){
                            // si falla borra el registro del usuario
                            $this->Usuario->delete($this->Usuario->id);
                            $this->log('error al crear la el usuario en idg ' . $identificacion,LOG_ERR);
                            } else {
                                if($usuario_bd){
                                        // Elimina las transacciones anteriores
                                        $this->TransaccionJuridico->deleteAll(
                                        array(
                                            'usuario_id' => $usuario_bd['Usuario']['id']
                                        ),
                                        false
                                    );
                                }
								                                                                
                                // Busca todas las tarjetas asociadas a las corporativas seleccionadas
                                $tarjetas_corpemp = $usuarioEmpresa['Usuario']['ck'];
                                $tarjetas_asociadas = array();            
                                foreach ($tarjetas_corpemp as $x){
                                if($x['menu']==4){
                                    foreach ($x['cuenta'] as $t){                                                    
                                            //Busca las tarjetas asociadas corporativas segun el tipo                                                                           
                                            $tarjetas_consulta = $this->Activo->buscarTarjetasEmpCorpAsociadas('C',$t);
                                            if(!empty($tarjetas_consulta) && !array_key_exists('', $tarjetas_consulta)){                                                                                
                                                $tarjetas_asociadas[$t] = $tarjetas_consulta;
                                                $tarjetas_asociadas[$t]= array_flip($tarjetas_asociadas[$t]);
                                            }                                                                                                      
                                        }                                                
                                    }
                                }                                    
                                // Asigna las transacciones
                                $this->TransaccionJuridicoDetalle->guardarTransacciones(Configure::read('transacciones_basicas.rol.default'), $usuarioEmpresa['Usuario']['ck'], $this->Usuario->id, $tarjetas_asociadas);
                                // Envio al correo del usuario el login y la clave
                                // Email Options
                                $this->Email->smtpOptions = Configure::read('email.smtp');
                                $this->Email->delivery	= 'smtp';
                                $this->Email->from = Configure::read('email.from');
                                $this->Email->to = $usuarioEmpresa['Usuario']['emailPri'];
                                $this->Email->subject = 'Notificacion de creacion de usuario';
                                $this->Email->sendAs = 'html';
                                // Informacion del correo
                                $this->Email->template	= 'usuario_creado';
                                $this->set(compact('usuario','usuarioEmpresa','clave_acceso'));

                                // Envio del correo
                                $this->Email->send();
                                                                
                                if ($usuarioEmpresa['Usuario']['perfil']=='3'){
                                $codigo_sms = 43;
                                }elseif($usuarioEmpresa['Usuario']['perfil']=='5'){
                                $codigo_sms = 49;
                                }else{
                                $codigo_sms = 44;
                                }
                                                                
                                // Enviar por sms la notificación
                                if( isset($usuario['celular']) ){                                    
                                    $this->Sms->enviar(
                                         array(
                                            'numcel'=>desencriptar($usuario['celular']),
                                            'canal1'=>$codigo_sms
                                        )
                                    );
                                }
								
                                $usuarioEmpresa['operacion']= $operacion['operacion'];
                                $this->Session->write('usuarios.descarga.pdf',$usuarioEmpresa);
                                $operacion_exitosa = true;
								
                                // registra los datos en bitacora
                                $this->Bitacora->registraBitacora($usuario['id'], 59, NULL, NULL, NULL, NULL, 'Creación de Usuario: ' . $usuarioEmpresa['Usuario']['nomUsuario']);
                            }
			} else {
				$ds->rollback($this->Usuario);
				$this->log('error al crear la contraseña del usuario en IDG ' . $identificacion,LOG_ERR);
                            }
                        } else {
				$ds->rollback($this->Usuario);
				$this->log('error al grabar el usuario en IDG '.$identificacion,LOG_ERR);
				$this->log($this->Idg->mensaje_error);
			}
			} else {
                                $ds->rollback($this->Usuario);
				$this->log('error al grabar el usuario '.$identificacion,LOG_ERR);
				$this->log($this->Usuario->getDataSource()->error);
                            }
				
                            break;
			case 'Eliminar':
                            if($this->Empresa->eliminaUsuario($usuarioEmpresa['Usuario']['id'], $usuario['tipo_rif'], $usuario['rif'])){
				$usuarioEmpresa['operacion']= $operacion['operacion'];
				$this->Session->write('usuarios.descarga.pdf',$usuarioEmpresa);
				$operacion_exitosa = true;
					
				// registra los datos en bitacora
				$this->Bitacora->registraBitacora($usuario['id'], 61, NULL, NULL, NULL, NULL, 'Eliminación de Usuario: ' . $usuarioEmpresa['Usuario']['nomUsuario']);
                            }
				
                            break;
			case 'Modificar':
                            // Info del usuario
                            $usuario_modificado = array(
				'Usuario'=>array(
                                    'nombre'=>encriptar($usuarioEmpresa['Usuario']['nombre']),
                                    'celular'=>encriptar($usuarioEmpresa['Usuario']['codigoCelular'].$usuarioEmpresa['Usuario']['nroCelular']),
                                    'email_principal'=>encriptar($usuarioEmpresa['Usuario']['emailPri']),
                                    'rol_juridico_id'=>$usuarioEmpresa['Usuario']['perfil'],
                                    'cargo'=>encriptar($usuarioEmpresa['Usuario']['cargo']),
                                    'fecha_hora_modificacion'=>date('Y-m-d H:i:s')
				)
                            );
				
                            $identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']) . $usuarioEmpresa['Usuario']['tipoCedulaRif'] . $usuarioEmpresa['Usuario']['nroCedulaRif'] . $usuarioEmpresa['Usuario']['nomUsuario'];

                            $this->Usuario->id = $usuarioEmpresa['Usuario']['id'];
				
                            // Actualiza el registro del usuario
                            $res = $this->Usuario->save($usuario_modificado,false);
                            if($res){
					
				// Elimina las transacciones anteriores
				$res = $this->TransaccionJuridico->deleteAll(
                                    array(
					'usuario_id' => $this->Usuario->id
                                    ),
                                    false
				);
					
                                // Busca todas las tarjetas asociadas a las corporativas seleccionadas
                                $tarjetas_corpemp = $usuarioEmpresa['Usuario']['ck'];
                                $tarjetas_asociadas = array();
                                        
                                    foreach ($tarjetas_corpemp as $x){
                                        if($x['menu']==4){
                                            foreach ($x['cuenta'] as $t){                                                    
                                            //Busca las tarjetas asociadas corporativas o empresariales segun el tipo
                                            $tarjetas_consulta = $this->Activo->buscarTarjetasEmpCorpAsociadas('C',$t);
                                                if(!empty($tarjetas_consulta) && !array_key_exists('', $tarjetas_consulta)){                                                        
                                                    $tarjetas_asociadas[$t] = $tarjetas_consulta;
                                                    $tarjetas_asociadas[$t]= array_flip($tarjetas_asociadas[$t]);
                                                    }                                                                                                        
                                                }                                                
                                            }
                                        }
                                        
					// Asigna las transacciones
					$this->TransaccionJuridicoDetalle->guardarTransacciones(Configure::read('transacciones_basicas.rol.default'), $usuarioEmpresa['Usuario']['ck'], $this->Usuario->id, $tarjetas_asociadas);
					
					if(isset($usuarioEmpresa['Usuario']['enviar_mail_registro'])){
						$res &= $this->enviar_nueva_clave($usuarioEmpresa);
					}
					
					if(isset($usuarioEmpresa['Usuario']['tipo_cambio'])){
                                            $tipo_cambio = $usuarioEmpresa['Usuario']['tipo_cambio'];
						
                                            $nombre_destino = $usuarioEmpresa['Usuario']['nombre'];
						
                                            $this->set(compact('nombre_destino', 'tipo_cambio'));
						
                                            // Enviar correo a dirección nueva

                                            $this->Email->smtpOptions = Configure::read('email.smtp');
                                            $this->Email->delivery	= 'smtp';
                                            $this->Email->from = Configure::read('email.from');
                                            $this->Email->to = $usuarioEmpresa['Usuario']['emailPri'];
                                            $this->Email->subject = 'Cambio de datos de contacto';
                                            $this->Email->sendAs = 'html';
                                            $this->Email->template = 'cambio_datos_contacto';
                                            $this->Email->send();
						
                                            $this->Sms->enviar(
						array(
                                                    'numcel'=>$usuarioEmpresa['Usuario']['codigoCelular'].$usuarioEmpresa['Usuario']['nroCelular'],
                                                    'canal1'=>$usuarioEmpresa['Usuario']['canal_sms']
						)
                                            );				
						
                                            if($tipo_cambio=='ambos' || $tipo_cambio=='email'){
						$this->Email->reset();
						$this->Email->smtpOptions = Configure::read('email.smtp');
						$this->Email->delivery	= 'smtp';
						$this->Email->from = Configure::read('email.from');
						$this->Email->to = $usuarioEmpresa['Usuario']['email_anterior'];
						$this->Email->subject = 'Cambio de datos de contacto';
						$this->Email->sendAs = 'html';
						$this->Email->template = 'cambio_datos_contacto';
						$this->Email->send();
                                            }
						
                                            if($tipo_cambio=='ambos' || $tipo_cambio=='celular'){
						$this->Sms->enviar(
                                                    array(
							'numcel'=>$usuarioEmpresa['Usuario']['celular_anterior'],
							'canal1'=>$usuarioEmpresa['Usuario']['canal_sms']
                                                    )
						);
                                            }
					}

					$usuarioEmpresa['operacion']= $operacion['operacion'];
					$this->Session->write('usuarios.descarga.pdf',$usuarioEmpresa);
					
					if($res){
                                            $operacion_exitosa = true;	
                                            // registra los datos en bitacora
                                            $this->Bitacora->registraBitacora($usuario['id'], 60, NULL, NULL, NULL, NULL, 'Modificación de Usuario: ' . $usuarioEmpresa['Usuario']['nomUsuario']);
					}
                                } else {
				   $this->log('error al actualizar el usuario '.$identificacion,LOG_ERR);
				   $this->log($this->Usuario->getDataSource()->error);
				}
			break;
		}
		
		$perfiles = $this->RolJuridico->listadoRoles();
		
		if($operacion['operacion']!='Eliminar'){
                    $transacciones = $this->TransaccionPerfil->moduloTransacciones($usuario, $usuarioEmpresa['Usuario']['perfil']);
		}
				
		$limite_externas = $usuario['limite_externas'] ? $usuario['limite_externas'] : _p('limite_externas_J');
		$limite_internas_terceros = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : _p('limite_internas_terceros_J');

		$limite_externas_nomina = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
		$limite_internas_terceros_nomina = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                
                $limite_externas_proveedores = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
		$limite_internas_terceros_proveedores = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');

		if($limite_externas) $limite_externas = formatea_monto_fromWs($limite_externas);
		if($limite_internas_terceros) $limite_internas_terceros = formatea_monto_fromWs($limite_internas_terceros);						
		if($limite_externas_nomina) $limite_externas_nomina = formatea_monto_fromWs($limite_externas_nomina);
		if($limite_internas_terceros_nomina) $limite_internas_terceros_nomina = formatea_monto_fromWs($limite_internas_terceros_nomina);						
		if($limite_externas_proveedores) $limite_externas_proveedores = formatea_monto_fromWs($limite_externas_proveedores);
		if($limite_internas_terceros_proveedores) $limite_internas_terceros_proveedores = formatea_monto_fromWs($limite_internas_terceros_proveedores);						
		
                if(isset($usuario['limite_vip']) && is_numeric($usuario['limite_vip'])) {
                    $limites_vip = $this->LimitesVip->buscarLimiteById($usuario['limite_vip']);                    						
                    $limite_externas_nomina = formatea_monto_fromWs($limites_vip['limite_externas_nomina']);
                    $limite_internas_terceros_nomina = formatea_monto_fromWs($limites_vip['limite_internas_terceros_nomina']);						
                    $limite_externas_proveedores = formatea_monto_fromWs($limites_vip['limite_externas_proveedores']);
                    $limite_internas_terceros_proveedores = formatea_monto_fromWs($limites_vip['limite_internas_terceros_proveedores']);
                }
                
            $this->set(compact('transacciones','operacion','perfiles','usuarioEmpresa','operacion_exitosa','usuario','limite_externas','limite_internas_terceros','limite_externas_nomina','limite_internas_terceros_nomina','limite_externas_proveedores','limite_internas_terceros_proveedores'));
		
            // Elimina la data de las sesiones
            $this->Session->delete('empresa.transacciones.generales');
            $this->Session->delete('empresa.estado');
            $this->Session->delete('empresa.usuarios');
	}
	
	/**
	 * Acción para la selección del usuario al que se le cambiará el estatus de acceso
	 */
	function cambio_form(){
		
        /* Para validar el acceso al modulo */
        if(!validarTransaccion(37)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */
        $usuario = $this->Session->read('usuarios.datos');
        if( !$this->Session->check('empresa.usuarios') ) $this->redirect('/empresa/listado');
		
		if(!empty($this->data['Usuario'])){
			
            $this->Usuario->set($this->data);
			
            if( $this->Usuario->validates(array('fieldList'=>array('co_estado'))) ){
				
                $data = $this->Session->read('empresa.usuarios');
                $data['Usuario']['co_estado'] = $this->sanitize_input($this->data['Usuario']['co_estado']);
                if ($data['Usuario']['co_estado'] != 'P') {
                	$this->redirect('/consultas/integral');
                }else{
                	$this->Session->write('empresa.usuarios',$data);
                	$this->redirect('cambio_confirmacion');
                }
                
            }
		}
		
		$this->layout = 'interna';
		
		$this->set('seccion_actual','empresa.crear');
		
		$this->data= $this->Session->read('empresa.usuarios');
		
		$perfiles = $this->RolJuridico->listadoRoles();
		
		$array_estatus = $this->Empresa->estatus;
		
		$this->set(compact('perfiles','array_estatus','usuario'));
		
	}
	
	/**
	 * Acción para la confirmación del cambio de estatus de acceso
	 */
	function cambio_confirmacion(){
            if( !$this->Session->check('empresa.usuarios') ) $this->redirect('/empresa/listado');
            $perfiles = $this->RolJuridico->listadoRoles();
            $estatus = $this->Empresa->estatus;
            $this->layout = 'interna';		
            $this->set('seccion_actual','empresa.crear');
		
            $this->data = $this->Session->read('empresa.usuarios');		
            $this->set(compact('estatus', 'perfiles'));	

	}
	
	/**
	 * acción para el resultado del cambio de estatus de acceso
	 */
	function cambio_resultado(){
            if( !$this->Session->check('usuarios.datos') || !$this->Session->check('empresa.usuarios') ) $this->redirect('/empresa/listado');
		
            $this->layout = 'interna';
            $this->set('seccion_actual','empresa.crear');
            $usuario = $this->Session->read('usuarios.datos');
            $usuarioEmpresa = $this->Session->read('empresa.usuarios');
            $estatus = $this->Empresa->estatus;
            $perfiles = $this->RolJuridico->listadoRoles();
            $operacion_exitosa = false;
            $clave_acceso = '';
		
            // si lo va activar desbloquea el usuario del IDG
            if($usuarioEmpresa['Usuario']['co_estado']=='P'){
		$res = $this->enviar_nueva_clave($usuarioEmpresa);
            } else {
		$res = true;
            }
		
            // Cambia el estatus en la BD
            if($res){
		if($this->Empresa->cambioEstatusUsuario($usuarioEmpresa['Usuario']['id'],$usuarioEmpresa['Usuario']['co_estado'], $usuario['tipo_rif'], $usuario['rif'])){
                    $this->Session->write('usuarios.descarga.pdf',$usuarioEmpresa);
                    $operacion_exitosa = true;
				
                    // registra los datos en bitacora
                    $this->Bitacora->registraBitacora($usuario['id'], 58, NULL, NULL, NULL, NULL, 'Cambio Estatus Usuario: ' . $usuarioEmpresa['Usuario']['nomUsuario']);
				
		}
            }
		
            $this->set(compact('estatus', 'operacion_exitosa', 'usuarioEmpresa','perfiles','usuario'));
            $this->Session->delete('empresa.usuarios');
	
	}
	
	
	/**
	 * Acción para el envío de claves nuevas a los correos.
	 * 
	 * @param array $usuarioEmpresa
	 * @return boolean
	 */
	function enviar_nueva_clave($usuarioEmpresa){
            $usuario = $this->Session->read('usuarios.datos');
		
            $clave_acceso = $this->Empresa->generaClave();
		
            $usuario_canal = $this->UsuarioCanal->find(
		'first',
		array(
                    'conditions'=>array(
                        'usuario_id'=>$usuarioEmpresa['Usuario']['id']
                    )
		)
            );
	
            if($usuario_canal){
                // Si el usuariocanal ya existe, generar la clave en idg
			
		$identificacion = $usuario['tipo_rif'] . desencriptar($usuario['rif']) . $usuarioEmpresa['Usuario']['tipoCedulaRif'] . $usuarioEmpresa['Usuario']['nroCedulaRif'] . $usuarioEmpresa['Usuario']['nomUsuario'];
			
		$res = $this->Idg->inicializaContrasena($identificacion, $clave_acceso, 30);
		$res &= $this->Idg->desbloquearUsuario($identificacion);
		$res &= $this->Idg->desbloquearQA($identificacion);
		} else {
		// Generar la clave en ib actual
		$res = $this->MigracionJuridico->cambiarContrasena(
                    $usuario['tipo_rif'].desencriptar($usuario['rif']),
                    desencriptar($usuarioEmpresa['Usuario']['ibuser']),
                    $clave_acceso
		);		
		}
		if($res){
                    // Cambia el estatus de registro
                    $this->UsuarioCanal->cambioEstatus('registro', 'I', $usuarioEmpresa['Usuario']['id']);
			
                    // Envio al correo del usuario el login y la clave
                    // Email Options
                    $this->Email->smtpOptions = Configure::read('email.smtp');
                    $this->Email->delivery	= 'smtp';
                    $this->Email->from		= Configure::read('email.from');
                    $this->Email->to		= $usuarioEmpresa['Usuario']['emailPri'];
                    $this->Email->subject	= 'Notificacion de cambio estatus de usuario';
                    $this->Email->sendAs	= 'html';

                    // Informacion del correo
                    $this->Email->template	= 'usuario_creado';
                    $this->set(compact('usuario','usuarioEmpresa','clave_acceso'));

                    // Envio del correo
                    $this->Email->send();
			
                    return true;
		}
		
		return false;
	}
	
	
	/**
	 * Acción para la descarga del archivo pdf de los usuarios jurídicos
	 */
	function descargar_pdf_juridico(){
            if(!$this->Session->check('usuarios.descarga.pdf') || !$this->Session->check('usuarios.datos')){
		 $this->redirect('/consultas/integral');
             }

            $usuario = $this->Session->read('usuarios.datos');
		
            $data = $this->Session->read('usuarios.descarga.pdf');
		
            $perfiles = $this->RolJuridico->listadoRoles();
		
            if($data['operacion']!='Eliminar'){
		$transacciones = $this->TransaccionPerfil->moduloTransacciones($usuario, $data['Usuario']['perfil']);
            }
		
            $limite_externas = $usuario['limite_externas'] ? $usuario['limite_externas'] : _p('limite_externas_J');
            $limite_internas_terceros = $usuario['limite_internas_terceros'] ? $usuario['limite_internas_terceros'] : _p('limite_internas_terceros_J');

            $limite_externas_nomina = $usuario['limite_externas_nomina'] ? $usuario['limite_externas_nomina'] : _p('limite_externas_nomina');
            $limite_internas_terceros_nomina = $usuario['limite_internas_terceros_nomina'] ? $usuario['limite_internas_terceros_nomina'] : _p('limite_internas_terceros_nomina');
                
            $limite_externas_proveedores = $usuario['limite_externas_proveedores'] ? $usuario['limite_externas_proveedores'] : _p('limite_externas_proveedores');
            $limite_internas_terceros_proveedores = $usuario['limite_internas_terceros_proveedores'] ? $usuario['limite_internas_terceros_proveedores'] : _p('limite_internas_terceros_proveedores');

            if($limite_externas) $limite_externas = formatea_monto_fromWs($limite_externas);
            if($limite_internas_terceros) $limite_internas_terceros = formatea_monto_fromWs($limite_internas_terceros);						
            if($limite_externas_nomina) $limite_externas_nomina = formatea_monto_fromWs($limite_externas_nomina);
            if($limite_internas_terceros_nomina) $limite_internas_terceros_nomina = formatea_monto_fromWs($limite_internas_terceros_nomina);						
            if($limite_externas_proveedores) $limite_externas_proveedores = formatea_monto_fromWs($limite_externas_proveedores);
            if($limite_internas_terceros_proveedores) $limite_internas_terceros_proveedores = formatea_monto_fromWs($limite_internas_terceros_proveedores);						
		            
            $this->set(compact('usuario','data','perfiles','transacciones','limite_externas','limite_internas_terceros','limite_externas_nomina','limite_internas_terceros_nomina','limite_externas_proveedores','limite_internas_terceros_proveedores'));
		
            $this->render('/elements/descargar_pdf_juridico');
	}
	
        /**
         * Acción para la descarga del archivo pdf con la información del cambio de estatus de un usuario
         */
	function descargar_pdf_juridico_estatus(){
            if(!$this->Session->check('usuarios.descarga.pdf') || !$this->Session->check('usuarios.datos')){
		$this->redirect('/consultas/integral');
            }

            $usuario = $this->Session->read('usuarios.datos');
            $data = $this->Session->read('usuarios.descarga.pdf');
            $estatus = $this->Empresa->estatus;
            $perfiles = $this->RolJuridico->listadoRoles();
	
            $this->set(compact('data','estatus','perfiles','usuario'));
            $this->render('/elements/descargar_pdf_juridico_estatus');
	}

    function sanitize_input($data) {
        return filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }
}
?>