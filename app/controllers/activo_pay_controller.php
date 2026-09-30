<?php

/**
 * Controlador para consulta de api key de Activo Pay.
 * 
 * @author Andrés S. Bco Activo
 */


class ActivoPayController extends AppController
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
    var $components = array('Activo', 'Session', 'Email', 'MesaCambio', 'ActivoPay', 'RequestHandler');

    /**
     *
     * @var array Ayudantes utilizados en las vistas de este controlador
     */
    var $helpers = array('Activo', 'Html', 'Session', 'Form', 'Paginator', 'Text', 'Javascript');

    function mostrar_key(){
    	 if (!validarTransaccion(102103)) $this->redirect('/consultas/integral');

    	 $usuario = $this->Session->read('usuarios.datos');      

    	 
    	
    	 	$usuario = $this->Session->read('usuarios.datos');    

            if($usuario['tipo_rif']){
            	$tipo_documento = $usuario['tipo_rif']; 
                $documento = desencriptar($usuario['rif']);
            }else{

            	$tipo_documento = $usuario['tipo_cedula']; 

                $documento = desencriptar($usuario['cedula']);


            }


            $this->Session->write('activo_pay.cedulaCliente',$documento);
            $this->Session->write('activo_pay.tipoDocumentoCliente',$tipo_documento);
	 

    	$res = $this->ActivoPay->activoPayApi($documento, $tipo_documento);

        $this->Bitacora->registraBitacora(
            $usuario['id'], 
            102103,
            NULL, 
            NULL, 
            NULL,
            NULL,
            'Generación de API KEY: ' . json_encode($res['error']),
            0
        );

    	$this->set('seccion_actual','activoPay.api'); 
        $this->layout = 'interna';
        $this->set(compact('documento', 'tipo_documento', 'res'));
        
    }

}