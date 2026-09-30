<?php

class GenerarArchivosShell extends Shell{
 var $uses = array(
                'Afiliacion',
                'AfiliacionCash',
                'Bitacora',
                'Modulo',
                'Canal',
                'Transaccion',
		'TransaccionPendiente',
		'TransaccionBeneficiario',
		'AcumuladoTransaccion',
		'AprobacionTransaccion'
	);

	function main(){
            
		App::import('Component', 'Activo');
		
		$activo = new ActivoComponent();                
                
                mkdir("/tmp/".date('Ymd'));
                
                //Acumulados         
                                                                    
                $archivo = fopen("/tmp/".date('Ymd')."/acumulado_transaccion.txt", "w+");

                $acumulados = $this->AcumuladoTransaccion->find(
                        'all',
                        array(
                            'conditions'=>array(
                                'fecha >='=> date('Y-m-d')
                            ),
                            'order'=>'AcumuladoTransaccion.id desc'
                        )
                );

                foreach($acumulados as $i){			
                    
                    $contenido = $i['AcumuladoTransaccion']['id'].";".trim($i['AcumuladoTransaccion']['fecha']).";".trim($i['AcumuladoTransaccion']['acumulado_internas']).";".$i['AcumuladoTransaccion']['acumulado_internas_terceros'].";".$i['AcumuladoTransaccion']['acumulado_externas'].";".$i['AcumuladoTransaccion']['acumulado_externas_terceros'].";".$i['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'].";".$i['AcumuladoTransaccion']['acumulado_externas_nomina'].";".$i['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'].";".$i['AcumuladoTransaccion']['acumulado_externas_proveedores'];
                    fwrite($archivo, PHP_EOL . $contenido);

                }
                $contenido= "";
                fclose($archivo);
                
                //Operaciones Diarias
                
                $archivo = fopen("/tmp/".date('Ymd')."/operaciones_diarias.txt", "w+");

                $logs = $this->Bitacora->find(
                        'all',
                        array(
                            'conditions'=>array(
                                'fecha_hora >='=> date('Y-m-d 00:00:00.000'),
                                'fecha_hora <='=> date('Y-m-d 23:59:59.000')
                            ),
                            'order'=>'Bitacora.id asc'
                        )
                );

                foreach($logs as $i){
                    
                    $contenido = $i['Bitacora']['id'].";".$i['Bitacora']['transaccion_id'].";".$i['Bitacora']['fecha_hora'].";".(($i['Bitacora']['cuenta_origen']!=null && !empty($i['Bitacora']['cuenta_origen'])) ? desencriptar($i['Bitacora']['cuenta_origen']) : $i['Bitacora']['cuenta_origen']).";".(($i['Bitacora']['cuenta_destino']!=null && !empty($i['Bitacora']['cuenta_destino'])) ? desencriptar($i['Bitacora']['cuenta_destino']) : $i['Bitacora']['cuenta_destino']).";".$i['Bitacora']['monto'].";".(($i['Bitacora']['referencia']!=null && !empty($i['Bitacora']['referencia'])) ? desencriptar($i['Bitacora']['referencia']) : $i['Bitacora']['referencia']).";".(($i['Bitacora']['descripcion']!=null && !empty($i['Bitacora']['descripcion'])) ? desencriptar($i['Bitacora']['descripcion']) : $i['Bitacora']['descripcion']).";".(($i['Bitacora']['ip']!=null && !empty($i['Bitacora']['ip'])) ? desencriptar($i['Bitacora']['ip']) : $i['Bitacora']['ip']).";".desencriptar($i['Bitacora']['user_agent']).";".$i['Bitacora']['canal'].";".$i['Bitacora']['error_operacion'].";".$i['Bitacora']['tipo_identificacion_b'].";".(($i['Bitacora']['identificacion_b']!=null && !empty($i['Bitacora']['identificacion_b'])) ? desencriptar($i['Bitacora']['identificacion_b']) : $i['Bitacora']['identificacion_b']).";".(($i['Bitacora']['nombre_b']!=null && !empty($i['Bitacora']['nombre_b'])) ? desencriptar($i['Bitacora']['nombre_b']) : $i['Bitacora']['nombre_b']);
                    fwrite($archivo, PHP_EOL . $contenido);

                }
                $contenido= "";
                fclose($archivo);
                
                
                //Modulos
                
                $archivo = fopen("/tmp/".date('Ymd')."/modulos.txt", "w+");

                $modulos = $this->Modulo->find(
                        'all'                        
                );

                foreach($modulos as $i){
                    
                    $contenido = $i['Modulo']['id'].";".$i['Modulo']['nombre'].";".$i['Modulo']['canal_id'];
                    fwrite($archivo, PHP_EOL . $contenido);

                }
                $contenido= "";
                fclose($archivo);
                
                
                //Canales
                
                $archivo = fopen("/tmp/".date('Ymd')."/canales.txt", "w+");

                $canales = $this->Canal->find(
                        'all'                        
                );

                foreach($canales as $i){
                    
                    $contenido = $i['Canal']['codigo'].";".$i['Canal']['nombre'];
                    fwrite($archivo, PHP_EOL . $contenido);

                }
                $contenido= "";
                fclose($archivo);
                
                
                //Tipos de Transacciones
                
                $archivo = fopen("/tmp/".date('Ymd')."/tipo_transacciones.txt", "w+");

                $tiposTransacciones = $this->Transaccion->find(
                        'all'                        
                );

                foreach($tiposTransacciones as $i){
                    
                    $contenido = $i['Transaccion']['id'].";".$i['Transaccion']['modulo_id'].";".$i['Transaccion']['nombre'];
                    fwrite($archivo, PHP_EOL . $contenido);

                }
                $contenido= "";
                fclose($archivo);
                
                
                //Transacciones Pendientes
                
                $archivo = fopen("/tmp/".date('Ymd')."/transacciones_pendientes.txt", "w+");

                $transaccionesPendientes = $this->TransaccionPendiente->find(
                        'all',
                        array(
                            'conditions'=>array(
                                'fecha_hora_registro >='=> date('Y-m-d 00:00:00.000'),
                                'fecha_hora_registro <='=> date('Y-m-d 23:59:59.000')
                            ),
                            'order'=>'TransaccionPendiente.id asc'
                        )                       
                );

                foreach($transaccionesPendientes as $i){
                    
                    $contenido = $i['TransaccionPendiente']['id'].";".trim($i['TransaccionPendiente']['tipo']).";".trim($i['TransaccionPendiente']['estatus']).";".trim($i['TransaccionPendiente']['lote']).";".$i['TransaccionPendiente']['fecha_hora_registro'].";".$i['TransaccionPendiente']['fecha_hora_valor'].";".$i['TransaccionPendiente']['monto'].";".$i['TransaccionPendiente']['aprobaciones_requeridas'].";".$i['TransaccionPendiente']['tipo_rif'].";".(($i['TransaccionPendiente']['rif']!=null && !empty($i['TransaccionPendiente']['rif'])) ? desencriptar($i['TransaccionPendiente']['rif']) : $i['TransaccionPendiente']['rif']).";".(($i['TransaccionPendiente']['cuenta_origen']!=null && !empty($i['TransaccionPendiente']['cuenta_origen'])) ? desencriptar($i['TransaccionPendiente']['cuenta_origen']) : $i['TransaccionPendiente']['cuenta_origen']).";".(($i['TransaccionPendiente']['cuenta_destino']!=null && !empty($i['TransaccionPendiente']['cuenta_destino'])) ? desencriptar($i['TransaccionPendiente']['cuenta_destino']) : $i['TransaccionPendiente']['cuenta_destino']).";".(($i['TransaccionPendiente']['descripcion']!=null && !empty($i['TransaccionPendiente']['descripcion'])) ? desencriptar($i['TransaccionPendiente']['descripcion']) : $i['TransaccionPendiente']['descripcion']).";".trim($i['TransaccionPendiente']['tipo_id_beneficiario']).";".(($i['TransaccionPendiente']['id_beneficiario']!=null && !empty($i['TransaccionPendiente']['id_beneficiario'])) ? desencriptar($i['TransaccionPendiente']['id_beneficiario']) : $i['TransaccionPendiente']['id_beneficiario']).";".(($i['TransaccionPendiente']['nombre_beneficiario']!=null && !empty($i['TransaccionPendiente']['nombre_beneficiario'])) ? desencriptar($i['TransaccionPendiente']['nombre_beneficiario']) : $i['TransaccionPendiente']['nombre_beneficiario']).";".(($i['TransaccionPendiente']['email_beneficiario']!=null && !empty($i['TransaccionPendiente']['email_beneficiario'])) ? desencriptar($i['TransaccionPendiente']['email_beneficiario']) : $i['TransaccionPendiente']['email_beneficiario']).";".trim($i['TransaccionPendiente']['tipo_carga']).";".(($i['TransaccionPendiente']['alias']!=null && !empty($i['TransaccionPendiente']['alias'])) ? desencriptar($i['TransaccionPendiente']['alias']) : $i['TransaccionPendiente']['alias']);
                    fwrite($archivo, PHP_EOL . $contenido);

                }
                $contenido= "";
                fclose($archivo);
                
                
                //Transacciones Beneficiarios
                
                $archivo = fopen("/tmp/".date('Ymd')."/transacciones_beneficiarios.txt", "w+");

                $transaccionesBeneficiarios = $this->TransaccionBeneficiario->find(
                        'all',
                        array(
                            'conditions'=>array(
                                'transaccion_id in (SELECT id FROM transacciones_pendientes WHERE fecha_hora_registro >= "'.date('Y-m-d 00:00:00.000').'" and fecha_hora_registro <= "'.date('Y-m-d 23:59:59.000').'")'                              
                            ),
                            'order'=>'TransaccionBeneficiario.id asc'
                        )                       
                );

                foreach($transaccionesBeneficiarios as $i){
                    
                    $contenido = $i['TransaccionBeneficiario']['id'].";".trim($i['TransaccionBeneficiario']['tipo_carga']).";".trim($i['TransaccionBeneficiario']['tipo']).";".$i['TransaccionBeneficiario']['tipo_rif'].";".(($i['TransaccionBeneficiario']['rif']!=null && !empty($i['TransaccionBeneficiario']['rif'])) ? desencriptar($i['TransaccionBeneficiario']['rif']) : $i['TransaccionBeneficiario']['rif']).";".(($i['TransaccionBeneficiario']['nombre']!=null && !empty($i['TransaccionBeneficiario']['nombre'])) ? desencriptar($i['TransaccionBeneficiario']['nombre']) : $i['TransaccionBeneficiario']['nombre']).";".$i['TransaccionBeneficiario']['tipo_cedula'].";".(($i['TransaccionBeneficiario']['cedula']!=null && !empty($i['TransaccionBeneficiario']['cedula'])) ? desencriptar($i['TransaccionBeneficiario']['cedula']) : $i['TransaccionBeneficiario']['cedula']).";".desencriptar($i['TransaccionBeneficiario']['numero_cuenta']).";".desencriptar($i['TransaccionBeneficiario']['email']).";".desencriptar($i['TransaccionBeneficiario']['celular']).";".$i['TransaccionBeneficiario']['monto'].";".(($i['TransaccionBeneficiario']['referencia']!=null && !empty($i['TransaccionBeneficiario']['referencia'])) ? desencriptar($i['TransaccionBeneficiario']['referencia']) : $i['TransaccionBeneficiario']['referencia']).";".(($i['TransaccionBeneficiario']['concepto']!=null && !empty($i['TransaccionBeneficiario']['concepto'])) ? desencriptar($i['TransaccionBeneficiario']['concepto']) : $i['TransaccionBeneficiario']['concepto']).";".$i['TransaccionBeneficiario']['transaccion_id'].";".$i['TransaccionBeneficiario']['reversada'];
                    fwrite($archivo, PHP_EOL . $contenido);

                }
                $contenido= "";
                fclose($archivo);
                
	}
}
?>
