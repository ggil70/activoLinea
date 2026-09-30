<?php
/**
 * Componente que maneja la información de los estados de cuenta de las tarjetas
 * de crédito.
 * 
 * @author Gheylert Gil
 */



/**
 * Importación de la librería del manejo de los webservices.
 */
App::Import('Vendor', 'nusoap/nusoap');


/**
 * Maneja la información de los estados de cuenta de las tarjetas de crédito.
 * 
 */
class SimpletvComponent extends Object {
	
	/**
	 * Controlador
	 * 
	 * @var string 
	 */
	var $controller;
	
	/**
	 * Objeto de conexión del webservice
	 * 
	 * @var object
	 */
	var $client;
	
	/**
	 * Componentes del cake utilizados
	 * 
	 * @var array 
	 */
	var $components = array('Session','Activo');

	
	/**
	 * Método invocado inicialmente por el CakePHP
	 * 
	 * @param string $controller
	 * @param array $settings
	 */
	function initialize(&$controller, $settings = array()) {
		$this->controller = $controller;
	}	

	


//ws de consulta contrato
//----------------------------------------
//Permite consultar cuenta del cliente por nro de contrato
//----------------------------------------


    function invocaCurlStv($url, $parametros){

     	$url = Configure::read('Url_Simple_TV') ."/" . $url;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => json_encode($parametros),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
            ),
        ));

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($curl);
        curl_close($curl);

        
        return json_decode($response);
        
    }

public function as400Simple($data){

	$res = $this->Activo->invoca('IN800201',
            array('req'=>array(
            'IN8002ACC'=>$data['ctaDebitar'],
            'IN8002CCY'=>'VEB',
            'IN8002IDE'=>$data['nroContrato'],
            'IN8002MTO'=>$data['montoRec'],
            'IN8002FOP'=>'EFEC',
            'IN8002CST'=>'13',
            'IN8002CED'=>$data['documento_identificacion'],
            'IN8002TID'=>$data['tracerID'],
            'IN8002REF'=>$data['referencia'],
            'IN8002IRV'=>$data['accion'],
            'IN8002CAN'=>'04',
            'IN8002DIP'=>$data['ip'],
            'IN8002USR'=>$data['usuario'], 
            'IN8002OFC'=>0,
            'IN8002TER'=>0,
            'IN8002CKN'=>0,
            'IN8002ERR'=>0,
            'IN8002MSE'=>''
            )
        )
    );
    
    return $res;
}


   public  function mensajeErrorSTV_VTA($codeError){

        $mensajeCritico =  _m('error.fatal');
        $mensajeSimple = _m('stv.mensaje.error');
        $mensajeSimpleBasico = _m('stv.mensaje.error_basico');
        $mensajeSimpleIntente = _m('stv.mensaje.error_intente');
        
    
        switch($codeError){
            case 'SC-LV-0001':
                //Servicio no disponible
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;

            case 'SC-LV-0006':
                //Error url curl
                $mensaje = $mensajeCritico . ' [ BA - URL' . $codeError .' ]';
                break;


            case 'SC-LV-0002':
                //Servicio no disponible
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;


            case 'SC-STV-LV-CC-0000':
                //Servicio no disponible
                $mensaje = $mensajeSimpleBasico . ' [ BA - ' . $codeError .' ]';
                break;

            case 'SC-STV-TM-CC 9999':
                //time out 
                $mensaje = $mensajeSimpleBasico . ' [ BA  ' . $codeError . ' ]';
                break;

            case 'SC-LV-CC-9000':
                //error desconocido
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;


            case 'SC-STV-TM-GB-9999':
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;

            case 'SC-STV-LV-GB-0000':
                //Servicio no disponible
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;  

            case 'ERR-CID-LV-GB-8000':
                //error codificacion customer id
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;                

            case 'SC-LV-GB-9000':
                //error Desconocido
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;          

            case 'ERR-PD-8888':
                //error respuesta WEb SERVICE  PAID
                $mensaje = $mensajeCritico . ' [ SC STV PD 0001 ] ';
                break;


            case 'ME-0063':
                $mensaje = 'En estos momentos tu recarga no puede ser procesada por ajustes del monto máximo en la Billetera Simple.' . ' [ STV ' . $codeError .' ]';
                break;   

            case 'MI-0063':
                $mensaje = 'En estos momentos tu recarga no puede ser procesada por ajustes del monto máximo en la Billetera Simple.' . ' [ STV ' . $codeError .' ]';
                break;   

            case 'ME-0006':
                $mensaje = 'Ha ocurrido un error interno. Por favor valide los parámetros de llamada' . $mensajeSimpleBasico . ' [ BA - ' . $codeError .' ]';
                break;
            case 'MI-0080':
                $mensaje =$mensajeSimpleBasico  . ' [ STV ' . $codeError .' ]';
                break;

            case 'MI-0081':
                $mensaje = $mensajeSimpleBasico . ' [ STV ' . $codeError .' ]';
                break;

            case 'MI-0082':
                $mensaje = $mensajeSimpleBasico . ' [ BA ' . $codeError .' ]';
                break;

            case 'ME-0021':
                $mensaje = 'El Número de Tarjeta de Acceso que ha introducido no se encuentra asociado a ningún cliente' . ' [ BA ' . $codeError .' ]';
                break;

            case 'MI-0083':
                $mensaje = $mensajeSimpleBasico . ' [ BA ' . $codeError .' ]';
                break;                

            case 'ME-0014':
                $mensaje = 'Estimado cliente ha ocurrido un error en la consulta de la deuda. Intente de nuevo o ingrese a www.simple.com.ve' . ' [ ' . $codeError .' ]';
                break;                

            case 'MI-0021':
                $mensaje = 'El Número de Tarjeta de Acceso que ha introducido no se encuentra asociado a ningún cliente' . ' [ BA ' . $codeError .' ]';
                break;

            case 'MI-0061':
                $mensaje = 'El Número de Tarjeta de Acceso que ha introducido no se encuentra asociado a ningún cliente [ STV ' . $codeError . ' ]';
                break;

            case 'MI-0062':
                $mensaje = 'Su recarga no pudo ser procesada. El monto que desea recargar es mayor al permitido.' . ' [ ' . $codeError .' ]';
                break;

            case 'ME-0062':
                $mensaje = 'Disculpe para realizar este proceso debe comunicarse con el con el administrador de la cuenta.' . ' [ ' . $codeError .' ]';
                break;

            case 'ME-0063':
                $mensaje = 'En estos momentos tu recarga no puede ser procesada por ajustes del monto máximo en la Billetera Simple.' . ' [ ' . $codeError .' ]';
                break;                


            case 'MI-0097':
                $mensaje = $mensajeSimpleBasico . ' [ BA ' . $codeError .' ]';
                break;


            default:
                $mensaje = 'Ha ocurrido un error inesperado, intente mas tarde. [ BA ' . $codeError .' ]';
       }

       return $mensaje;

    }



function mensajeErrorSTV_STV($codeError){
      $mensajeCritico = _m('error.fatal');  
      $mensaje='';
      switch($codeError){
            case 'SC-LV-0001':
                //Servicio no disponible
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;

            case 'SC-LV-0002':
                //Servicio no disponible
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;

            case 'SC-LV-0006':
                //Error url curl
                $mensaje = $mensajeCritico . ' [ BA - URL' . $codeError .' ]';
                break;


           case 'SC-STV-LV-CC-0000':
                //Servicio no disponible
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;

            case 'SC-STV-TM-CC 9999':
                //time out 
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;

            case 'SC-LV-CC-9000':
                //error desconocido
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;


            case 'SC-STV-TM-GB-9999':
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;

            case 'SC-STV-LV-GB-0000':
                //Servicio no disponible
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;  

            case 'ERR-CID-LV-GB-8000':
                //error codificacion customer id
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;                

            case 'SC-LV-GB-9000':
                //error Desconocido
                $mensaje = $mensajeCritico . ' [ BA - ' . $codeError .' ]';
                break;  

        case 'ERR-PD-8888':
            //error respuesta webservice paid
            $mensaje = 'Ha ocurrido un error inesperado, intente mas tarde. [ S/C STV PD 0001 ]';
            break;     

        case 'ME-0014':
            $mensaje = 'Estimado cliente ha ocurrido un error en la consulta de la deuda. Intente de nuevo o ingrese a www.simple.com.ve' . ' [ ' . $codeError .' ]';
            break;   


        case 'ME-0063':
            $mensaje = 'En estos momentos tu recarga no puede ser procesada por ajustes del monto máximo en la Billetera Simple. [ ' . $codeError . ' ]';
            break;    
        case 'MI-0063':
            $mensaje = 'En estos momentos tu recarga no puede ser procesada por ajustes del monto máximo en la Billetera Simple. [ ' . $codeError . ' ]';
            break;         
        case 'ME-0006':
            $mensaje = 'Ha ocurrido un error interno. Por favor valide los parámetros de llamada al servicio. [ ' . $codeError . ' ]';
            break;
        case 'MI-0080':
            $mensaje = 'Ha ocurrido un error procesando la Recarga del cliente. Por favor intente nuevamente. [ ' . $codeError . ' ]';
            break;

        case 'MI-0081':
            $mensaje = 'Ha ocurrido un error interno de sistema. [ ' . $codeError . ' ]';
            break;

        case 'MI-0082':
            $mensaje = 'Ha ocurrido un error interno invocando plataforma SimpleTV. [ ' . $codeError . ' ]';
            break;

        case 'MI-0083':
            $mensaje = 'Ha ocurrido un error interno procesando la recarga del cliente. [ ' . $codeError . ' ]';
            break;

        case 'ME-0021':
            $mensaje = 'El número de tarjeta de acceso que ha introducido no se encuentra asociado a ningún cliente. [ ' . $codeError . ' ]';
            break;

        case 'MI-0021':
            $mensaje = 'El número de tarjeta de acceso que ha introducido no se encuentra asociado a ningún cliente. [ ' . $codeError . ' ]';
            break;            

        case 'MI-0061':
            $mensaje = 'El número de tarjeta de acceso es inválido. Por favor ingrese a nuestra página web www.simple.com.ve [ ' . $codeError . ' ]';
            break;

        case 'MI-0062':
            $mensaje = 'Su recarga no pudo ser procesada. El monto que desea recargar es mayor al permitido. [ ' . $codeError . ' ]';
            break;

        case 'ME-0062':
                $mensaje = 'Su recarga no pudo ser procesada. El monto que desea recargar es mayor al permitido.' . ' [ ' . $codeError .' ]';
                break;

        case 'ME-0063':
                $mensaje = 'En estos momentos tu recarga no puede ser procesada por ajustes del monto máximo en la Billetera Simple.' . ' [ ' . $codeError .' ]';
                break;             

        case 'MI-0097':
            $mensaje =  $mensajeSimpleBasico . ' [ ' . $codeError . ' ]';
            break;


        default:
            $mensaje = 'Ha ocurrido un error inesperado, intente mas tarde. [ S/D STV PD 0002 ]';
      }
      //$mensaje = $mensaje . " [STV] [" . ;
      return $mensaje;
}



}

?>

