<?php
class LiberarAcumuladosShell extends Shell{
	var $uses = array(
		'TransaccionPendiente',
		'TransaccionBeneficiario',
		'AcumuladoTransaccion',
		'AprobacionTransaccion'
	);

	function main(){
		App::import('Component', 'Activo');
		
		$activo = new ActivoComponent();

	
		$transacciones = $this->TransaccionPendiente->find(
			'all',
			array(
				'conditions'=>array(
					'fecha_hora_valor >='=> date('Y-m-d 00:00:00'),
					'fecha_hora_valor <='=> date('Y-m-d 23:59:59')
				)
			)
		);
		
		foreach($transacciones as $i){
			$this->out('lote '.$i['TransaccionPendiente']['lote'].' rif '.$i['TransaccionPendiente']['tipo_rif'].desencriptar($i['TransaccionPendiente']['rif']));
			
			$activo->seguimientoGestion($i);

			// Buscar el ultimo usuario que aprobó la transacción
			// a quién se debe descontar del acumulado
			
			$ultima_aprobacion = $this->AprobacionTransaccion->find(
				'first',
				array(
					'conditions'=>array(
						'transaccion_id'=>$i['TransaccionPendiente']['id']
					),
					'order'=>'AprobacionTransaccion.id desc'
				)
			);
			
			if($ultima_aprobacion){
				// Tomar el id del último aprobador
				$this->out('ultima aprobacion por '.desencriptar($ultima_aprobacion['Usuario']['nombre']));
				
				$usuario_id = $ultima_aprobacion['AprobacionTransaccion']['usuario_id'];
			} else {
				// Si no hay aprobación es porque es un master
				
				$this->out('montada por master '.desencriptar($i['UsuarioCreador']['nombre']));
				
				$usuario_id =  $i['TransaccionPendiente']['usuario_id'];
			}			
			

			foreach($i['Beneficiarios'] as $b){
				
				// Buscando el acumulado para el usuario correspondiente

				$ac = $this->AcumuladoTransaccion->find(
					'first',
					array(
						'conditions'=>array(
							'usuario_id'=>$usuario_id,
							'fecha'=>$i['TransaccionPendiente']['fecha_hora_valor']
						)
					)
				);
								
					
				if($ac){
					
					$this->out('acumulado id '.$ac['AcumuladoTransaccion']['id']);
					
					$this->out('beneficiario '.$b['tipo_cedula'].desencriptar($b['cedula']).' Rechazo '.isset($b['rechazado']));
					
					if(!$b['reversada'] && isset($b['rechazado'])){
						
						
						$this->out('se debe reversar del acumulado');
						
						$ds = $this->AcumuladoTransaccion->getDataSource();
						
						$ds->begin($this->AcumuladoTransaccion);
						
						$this->AcumuladoTransaccion->id = $ac['AcumuladoTransaccion']['id'];
						
						if(substr(desencriptar($b['numero_cuenta']),0,4)=='0171'){
							$this->out('reversando acumulado interno, monto '.formatea_monto_fromWs($b['monto']));
							
							$res1 = $this->AcumuladoTransaccion->saveField(
								'acumulado_internas_terceros',
								$ac['AcumuladoTransaccion']['acumulado_internas_terceros']-$b['monto']
							);
						} else {
							$this->out('reversando externo, monto '.formatea_monto_fromWs($b['monto']));

							$res1 = $this->AcumuladoTransaccion->saveField(
								'acumulado_externas',
								$ac['AcumuladoTransaccion']['acumulado_externas']-$b['monto']
							);				
						}
				
				
						$this->TransaccionBeneficiario->id = $b['id'];
						
						$res2 = $this->TransaccionBeneficiario->saveField('reversada',1);
						
						if($res1 && $res2){
							$ds->commit($this->AcumuladoTransaccion);
						} else {
							$this->out('ocurrio un error de bd. saliendo');
							$ds->rollback($this->AcumuladoTransaccion);
							exit;
						}
					
					}
				}
			}			
		}
	}
}
?>