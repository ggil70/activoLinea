<?php

/**
 * Controlador para las operaciones de tarjeta divisa TDD.
 * 
 * @author Gheylert Gil Bco Activo
 */



/**
 * Operaciones de tarjeta divisa tdd.
 * 
 */
class TarjetaCreditoCredicardController extends AppController
{


    /**
     *
     * @var array Modelos que usa el controlador 
     */
    var $uses = array(
        'Usuario',
        'Parametro',
        'Bitacora',
        'Afiliacion'
    );

    /**
     *
     * @var array Componentes que utiliza este controlador
     */
    var $components = array('Activo', 'Session', 'Email', 'MesaCambio', 'Credicard', 'RequestHandler');

    /**
     *
     * @var array Ayudantes utilizados en las vistas de este controlador
     */
    var $helpers = array('Activo', 'Html', 'Session', 'Form', 'Paginator', 'Text', 'Javascript');



    /**
     * Metodo inicial para activar TDC
     * informacion de las cuentas.
     * 
     * @param string $volver Indica si esta regresando de la confirmacion
     */
    function activacion(
        $cvc = null,
        $fecha = null,
        $tarjeta = null,
        $respuesta = null,
        $cedula = null,
        $volver = null,
        $all_user_tdc_request = null
    ) {
        /* Para validar el acceso al modulo */
        if (!validarTransaccion(101112)) $this->redirect('/consultas/integral');

        if (!empty($this->data['Activacion'])) {

            $usuario = $this->Session->read('usuarios.datos');      
            if($usuario['tipo_rif']){
                $documento = desencriptar($usuario['rif']);
            }else{
                $documento = desencriptar($usuario['cedula']);
            }

            $cvc = $this->data['Activacion']['codSeguridad'];
            $fecha = $this->data['Activacion']['fechaVencimiento'];
            $tarjeta = $this->data['Activacion']['numTarjeta'];
            $respuesta = '00'; 

            $this->Session->write('activacion_TDC.cedulaCliente',$documento);
            $this->Session->write('activacion_TDC.fechaV',$fecha);
            $this->Session->write('activacion_TDC.tarjeta',$tarjeta);
            $this->Session->write('activacion_TDC.cvc',$cvc);
            $this->redirect('activacion_confirmacion');
            
        }

        $consultaSalsoTdc = $this->Credicard->consultaDeSaldoTdcSoap(1);
        $consultaSalsoTdc = $consultaSalsoTdc['ConsultSaldoenLineaTDCResponse'];

        $swTarjetaActivar = 0;
        $soapTarjeta = array();


        if(isset($consultaSalsoTdc['cedula'])){  
               //Verifica que la cuenta este normal
               if($consultaSalsoTdc['estatustrj'] == 10){
                    $swTarjetaActivar = 1;
                    $nroTarjeta = $consultaSalsoTdc['tarjeta'];
                    $soapTarjeta[$nroTarjeta] = enmascara($nroTarjeta);                    
               }     
        }else{
            //VArias tarjetas
            foreach ($consultaSalsoTdc as $key => $consultaTdc){  
                if($consultaTdc['estatustrj'] == 10){
                    $swTarjetaActivar = 1;
                    $nroTarjeta = $consultaTdc['tarjeta'];
                    $soapTarjeta[$nroTarjeta] = enmascara($nroTarjeta);                    
                }   
            } 
        } 

        if($swTarjetaActivar == 0){
            if($swActivo == 0){
                $swValidar = 1;
                $ruta = 'no_activacion/'.$swValidar;
                $this->redirect($ruta); 
            }            
        }  
           
        $this->set('seccion_actual','tarjetaTdc.activacion'); 
        $this->layout = 'interna';
        $this->set(compact('soapTarjeta'));
        
    }



    /**
     * Metodo para realizar la confirmacion de la compra de divisas.
     */
    function activacion_confirmacion(){
            /* Para validar el acceso al modulo */
            if(!validarTransaccion(101112)) $this->redirect('/consultas/integral');
            /* Para validar el acceso al modulo */
            $this->set('seccion_actual','tarjetaTdc.activacion'); 
            $this->layout = 'interna';

            if($this->Session->check('activacion_TDC.cedulaCliente')){
                $tarjeta = $this->Session->read('activacion_TDC.tarjeta',$tarjeta);
                $fechaVencimiento = $this->Session->read('activacion_TDC.fechaV');
                $codSeguridad = $this->Session->read('activacion_TDC.cvc');
                $this->set(compact('tarjeta','fechaVencimiento','codSeguridad')); 
            }else {
                $this->redirect('activacion');
            }   
    }



    /********************************************************************************
      function error_critico
      Permite desplegar la pantalla de errores ocurridos en cualquieras de los procesos
      tarjeta prepagada y cuando son errores de web services o Gateway As400
    **********************************************************************************/     
    function error_critico($swValidar, $proceso, $servicio, $idTransaccion){
        $this->layout = 'interna';
        $mensaje = _m('error.fatal');

        switch($swValidar){
        case 1:
            $descripcion = $mensaje . " [CC]";  
            break;  
        case 2:
            $descripcion = $mensaje . " [AS|400]";      
            break;  
  
        }

        //LLenar Log de canales con el error presentado
        $this->set(compact('descripcion'));
    }



    /******************************************************************
    function no_activacion      
    Permite desplegar la pantalla de errores ocurridos durante 
    la activacion de las tarjetas
    ******************************************************************/
    function no_activacion($swValidar,$respuesta=null){
           //codigo de respuesta de la Activación     
           /*00 Transacción Exitosa
            $9 Tarjeta vencida
            45 Cedula invalida
            14 Tarjeta invalida
            92 Problemas con la conexión de la caja de seguridad
            $8 Emisor no existe
            57 Tarjeta ya Activa
            80 Fecha invalida
            54 Fecha Invalida
            N7 CVC inválido 
            $4 Tarjeta ya activada.
            */

        $this->layout = 'interna';   
        $mensaje = "";
        $proceso = 'ACTIVACION';
        $idTransaccion = 101112; 

        switch ($swValidar) {
          case 1:
              $mensaje = "Estimado cliente, Usted no posee Tarjeta de Cr&eacutedito para Activar";
              break;
          case 2:
              $mensaje = _m('tcp.activa');
              break;
          case 3:
              $mensaje = _m('tcp.activa.respuesta');
              $mensRespuesta = '';


              switch ($respuesta) {
                  case '$9':
                      $mensRespuesta = '[Tarjeta vencida]';
                      break;
                  case '45':
                      $mensRespuesta = '[Cedula invalida]';
                      break;                      
                  case '14':
                      $mensRespuesta = '[Tarjeta invalida]';
                      break;  
                  case '92':
                      $mensRespuesta = '[Problemas con la conexión de la caja de seguridad]';
                      break;  
                  case '$8':
                      $mensRespuesta = '[Emisor no existe]';
                      break;

                  case '$8':
                      $mensRespuesta = '[Tarjeta Vencida]';
                      break;

                  case '$4':
                     $mensaje = "Estimado Cliente, Verifique la información ingresada. ";
                     $mensRespuesta = '<br>[ Tarjeta ya activa ]';     
                     break;     

                   case '80':
                      $mensRespuesta = '[Fecha invalida]';
                      break; 

                  case '54':
                      $mensRespuesta = '[Fecha invalida]';
                      break; 
                  case '57':
                      $mensRespuesta = '[Tarjeta ya Activa]';
                      break;   

                  case 'N7':
                      $mensRespuesta = '[CVC invalido]';
                      break; 
                  case '04':
                     $mensRespuesta = '<br>[ Numero de Cedula o Codigo de Seguridad ]';     
                     break;
              }

              $mensaje = $mensaje  . " " . $mensRespuesta;
        }       
        $descripcion = $mensaje;


        $this->set(compact('descripcion'));
    }


    /**
     * Metodo extraer datos de la cuenta activa
     * informacion de las cuentas.
     * 
     * @param string $volver Indica si esta regresando de la confirmacion
     */


    function tdc_activacion_respuesta($res = null)
    {
        /* Para validar el acceso al modulo */
        if (!validarTransaccion(101112)) $this->redirect('/consultas/integral');
        /* Para validar el acceso al modulo */

        $this->set('seccion_actual');
        $this->layout = 'interna';

        $documento = $this->Session->read('activacion_TDC.cedulaCliente');
        $fechaVencimiento = $this->Session->read('activacion_TDC.fechaV');
        $tarjeta = $this->Session->read('activacion_TDC.tarjeta');
        $codSeguridad = $this->Session->read('activacion_TDC.cvc');
        $respuestaT = '00'; 

        $res = $this->Credicard->activacionTdcSoap($documento,$codSeguridad,$fechaVencimiento,$respuestaT,$tarjeta);


        if($res == 1){
            $swValidar = 1;
            $ruta = 'error_critico/'. $swValidar . '/ACTIVACION/WS-ACTIVACION/101112';
            $this->redirect($ruta);            
        }else{
            if($res ['ActivacionTarjetaResponse']['respuesta']){
                $respuesta = $res ['ActivacionTarjetaResponse']['respuesta'];    
            }else{
                $respuesta = $res['detail']['ExceptionFault']['detail']['detailCode']; 
            }



            if($respuesta == '00'){
                $ruta = 'activacion_resultado_vta';
                $this->redirect($ruta);
                //$this->set(compact('tarjeta','fechaVencimiento','codSeguridad'));
            }else{
                $swValidar = 3;
                $ruta = 'no_activacion/'.$swValidar.'/'.$respuesta;
                 $this->redirect($ruta); 
            }
        }    

    }

    function activacion_resultado_vta(){
        $this->layout = 'interna';
        $this->set('seccion_actual','tarjetaTdc.activacion'); 
        $fechaVencimiento = $this->Session->read('activacion_TDC.fechaV');
        $tarjeta = $this->Session->read('activacion_TDC.tarjeta');
        $codSeguridad = $this->Session->read('activacion_TDC.cvc');
        $this->set(compact('tarjeta','fechaVencimiento','codSeguridad'));
        //$this->set(compact('v_cedula','v_nombre','v_celular','v_agencia','v_cuentaBS'));    
    }



} //fin de la clase principal
