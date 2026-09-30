<?php


/**
 * Class ExtrafinanciamientosController
 *
 * Controlador que contiene las funcionalidades para las consultas de los extrafinanciamientos.
 *
 * @author Miguel Mendez <mammendez@bancoactivo.com>
 */
class ExtrafinanciamientosController extends AppController{
    var $name = 'Extrafinanciamientos';

    /**
     * @var array Modelos que usa el controlador
     */
    var $uses = array(
        'Usuario',
        'Bitacora',
        'Parametro',
        'AvanceEfectivo',
    );

    /**
     *
     * @var array Componentes que utiliza este controlador
     */
    var $components = array('Activo', 'Session', 'Email', 'RequestHandler');

    /**
     * @var array Ayudantes utilizados en las vistas de este controlador
     */
    var $helpers = array('Activo','Html','Session','Form','Paginator','Text','Javascript');

    /**
     * Index muestra el listado de los extracréditos activos en la tarjeta de crédito seleccionada. por defecto selecciona
     * la primera tarjeta de viene del listado de as400
     *
     */
    public function index(){

        // verificamos si esta solicitando una tdc especifica
        $tdcSolicitada = isset($this->data['ConsultaExtrafininanciamiento']['tdc']) ? $this->data['ConsultaExtrafininanciamiento']['tdc'] : null;

        $this->layout = 'interna';
        $this->set('seccion_actual','consultas.extrafinanciamientos');

        // se leen los datos del usuario
        $usuario = $this->Session->read('usuarios.datos');

        // Buscamos en la data de sesion si ya tenemos lo que necesitamos
        $extraConsultaData = $this->Session->read('extra.consulta.data');

        if(!isset($extraConsultaData)){
            $instrumentos = $this->Activo->buscarInstrumentosAvance(true);

            // Buscamos los parametros del m?dulo
            $params = $this->AvanceEfectivo->getParametros();

            // Para ser guardado en sesion
            $extraConsultaData['instrumentos'] = $instrumentos;
            $extraConsultaData['params'] = $params;

        }else{
            $instrumentos = $extraConsultaData['instrumentos'];
            $params = $extraConsultaData['params'];
        }


        // El usuario posee tarjetas que califican para extracredito
        if(count($instrumentos['tarjetas']) > 0 ){
            $tarjetas = array();
            foreach($instrumentos['tarjetas'] as $t){
                foreach($t as $k => $v){
                    $tarjetas[$k] = $v;
                }

            }

            if(!is_null($tdcSolicitada)){
                if(array_key_exists($tdcSolicitada, $tarjetas)){
                    $n = $tdcSolicitada;
                }
            }else{
                $keys = array_keys($tarjetas);
				$n = $keys[0];
            }

            $extraConsultaData['tdcSeleccionada'] = $n;

            if(!isset($extraConsultaData[$n])){
                $tdcDetalle = $this->Activo->buscaDetalleTdc($n);
                $tdcDetalle['msk'] = $tarjetas[$n];
                try{
                    $consolidado = $this->Activo->consolidadoExtrafinanciamientos($n);
                }catch (Exception $ex){
                    $consolidado['saldo_actual'] = 0;
                    $consolidado['detalles'] = array();

                }

                // Para ser guardado en sesion
                $extraConsultaData[$n]['tdcDetalle'] = $tdcDetalle;
                $extraConsultaData[$n]['consolidado'] = $consolidado;

            }else{
                $tdcDetalle = $extraConsultaData[$n]['tdcDetalle'];
                $consolidado = $extraConsultaData[$n]['consolidado'];
            }

            // Registramos en Bitacora la consulta
            $this->Bitacora->registraBitacora(
                $usuario['id'], 100057, null, $n, 0, 0, "Consulta ExtraCreditos", 0
            );

            $this->Session->write('extra.consulta.data', $extraConsultaData);
            $this->set(compact('tdcDetalle','consolidado', 'params', 'tarjetas'));

        }else{
            $error = _m('productos.no.califican', true);
            $this->set(compact('error'));
        }

    }

    /**
     * Muestra el detalle de un extrafinanciemento específico
     *
     * @param $numero Número de ExtraCredito
     */
    public function detalles($numero){

        $this->layout = 'interna';
        $this->set('seccion_actual','consultas.extrafinanciamientos');

        // Buscamos en la data de sesion si ya tenemos lo que necesitamos
        $extraConsultaData = $this->Session->read('extra.consulta.data');

        // se leen los datos del usuario
        $usuario = $this->Session->read('usuarios.datos');

        if(isset($numero) && isset($extraConsultaData)){
            try{
                $movimientos = $this->Activo->detalleExtrafinanciamientos($extraConsultaData['tdcSeleccionada'], $numero);
            }catch (Exception $ex){
                $movimientos = array();
            }

            $tarjetaSeleccionada = $extraConsultaData['tdcSeleccionada'];
            $detalleTdc = $extraConsultaData[$tarjetaSeleccionada]['tdcDetalle'];


            foreach ($extraConsultaData[$tarjetaSeleccionada]['consolidado']['detalles'] as $d ){
                if($d['numeroCredito'] == $numero){
                    $detalleCredito = $d;
                    break;
                }

            };

            // Registramos en Bitacora la consulta
            $this->Bitacora->registraBitacora(
                $usuario['id'], 100057, null, $numero, 0, 0, "Consulta detalle ExtraCredito ", 0
            );


            $totalMovimientos = count($movimientos);
            $tasaActual = $movimientos[$totalMovimientos - 1]['tasaMes'];
            $fechaUltimoMovimiento = new DateTime($movimientos[$totalMovimientos - 1]['fechaMovimiento']);
            $hoy = new DateTime();
            $diasTranscurridos = $fechaUltimoMovimiento->diff($hoy)->days;
            $totalPagar = $detalleCredito['saldoPendiente'] + ($detalleCredito['saldoPendiente'] * ($tasaActual/100) * ($diasTranscurridos/360));

            $this->set(compact('detalleTdc', 'detalleCredito', 'movimientos', 'tasaActual', 'totalPagar', 'totalMovimientos'));
        }else{
            $this->redirect('/extrafinanciamientos/');
        }
    }
}