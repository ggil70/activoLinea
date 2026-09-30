<?php

class GenerarArchivosShell {
  $uses = array(
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
 function aMemberFunc() { 
try
{

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

 
}
   catch (Exception $e)
     {
     echo 'Excepción capturada: ',  $e->getMessage();
     }
   finally
     {
     echo "Este código se ejecuta siempre.";
     }
}
}
