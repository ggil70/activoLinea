<?php
class generar_archivos_3
{
    // Declaración de una propiedad
    public $uses = array(
                'PreguntasDesafio',
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


    public function main()
     {
     try
        {
        App::import('Component', 'Activo');
        $activo = new ActivoComponent();
        mkdir("/tmp/".date('Ymd'));
       } 
     catch (Exception $e)
        {
        echo 'Excepción capturada: ',  $e->getMessage();
        }

     }

   //Textos
   public function Textos()
           {
           try
             {
             App::import('Component', 'Activo');
             $activo = new ActivoComponent();
             print 'pase 1';
          #   $user = new User();
            $this->loadModel('PreguntasDesafio');
             print 'pase 2';
             $busqueda = $this->PreguntasDesafio->buscarTodas();
             print 'pase 3';
             $archivo = fopen("/tmp/".date('Ymd')."/textos.txt", "w+");
             print 'pase 4';
             $tiposTransacciones = $user->find('all');
             print 'pase 5';
             foreach($tiposTransacciones as $i){
                    $contenido = $i['preguntas_desafio']['id'].";".$i['preguntas_desafio']['pregunta'];
                    fwrite($archivo, PHP_EOL . $contenido);
             }
             $contenido= "";
             print 'termine bien';
             fclose($archivo);
             }
          catch (\Exception $e)
             {
              print 'malo';
             echo $e->getMessage();
             }
           }


    //Tipos de Transacciones
    public function TipoTrans()
          {
          try
            {
#            App::import('Model', 'Transaccion');
print  ' ESTOY AQUI 1';
#           $user = new User();
            $archivo = fopen("/tmp/".date('Ymd')."/tipo_transacciones.txt", "w+");
 PRINT 'ESTOY AQUI 2';
            # $tiposTransacciones = $this->Transaccion->find('all');
            $tiposTransacciones = $user->find('all');
print  '3';
            foreach($tiposTransacciones as $i){
                  $contenido = $i['Transaccion']['id'].";".$i['Transaccion']['modulo_id'].";".$i['Transaccion']['nombre'];
                    fwrite($archivo, PHP_EOL . $contenido);
            }
            $contenido= "";
print  '3';
            fclose($archivo);
            }
          catch (Exception $e)
            {
            echo 'Excepción capturada: ',  $e->getMessage();
            }
          }

    //Modulos
    Public function Modulos()
          {
          try
            {
        App::import('Component', 'Activo');
        $activo = new ActivoComponent();
        mkdir("/tmp/".date('Ymd'));
            $archivo = fopen("/tmp/".date('Ymd')."/modulos.txt", "w+");
print 'paso0'; 
           $modulos = $this->Modulo->find('all');
 print 'paso1';
            foreach($modulos as $i){
                  $contenido = $i['Modulo']['id'].";".$i['Modulo']['nombre'].";".$i['Modulo']['canal_id'];
                  fwrite($archivo, PHP_EOL . $contenido);

            } 
 print 'paso2';
            $contenido= "";
            fclose($archivo);
            }
          catch (Exception $e)
            {
            echo 'Excepción capturada: ',  $e->getMessage();
            }
         }
           
   //Canales
   Public function Canales(){
           $archivo = fopen("/tmp/".date('Ymd')."/canales.txt", "w+");
           $canales = $this->Canal->find('all');
           foreach($canales as $i){
                  $contenido = $i['Canal']['codigo'].";".$i['Canal']['nombre'];
                  fwrite($archivo, PHP_EOL . $contenido);

           }
           $contenido= "";
           fclose($archivo);

           }

   //Acumulados
   Public function AcumTrans()
          {
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
          foreach($acumulados as $i)
              {
              $contenido = $i['AcumuladoTransaccion']['id'].";".trim($i['AcumuladoTransaccion']['fecha']).";".trim($i['AcumuladoTransaccion']['acumulado_internas']).";".$i['AcumuladoTransaccion']['acumulado_internas_terceros'].";".$i['AcumuladoTransaccion']['acumulado_externas'].";".$i['AcumuladoTransaccion']['acumulado_externas_terceros'].";".$i['AcumuladoTransaccion']['acumulado_internas_terceros_nomina'].";".$i['AcumuladoTransaccion']['acumulado_externas_nomina'].";".$i['AcumuladoTransaccion']['acumulado_internas_terceros_proveedores'].";".$i['AcumuladoTransaccion']['acumulado_externas_proveedores'];
           fwrite($archivo, PHP_EOL . $contenido);
           }
           $contenido= "";
           fclose($archivo);
         }



}

$ejecucion= new generar_archivos_3();
#$ejecucion->main();
#$ejecucion->Textos();
#$ejecucion->TipoTrans();
$ejecucion->Modulos();
#$ejecucion->Canales();
#$ejecucion->AcumTrans();
dispose($ejeucion);
?> 
