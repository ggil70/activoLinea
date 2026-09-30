<?php
class prueba_cripta
{


public function main()
    {
     try
        {
#        App::import('Component', 'Activo');
#        $activo = new ActivoComponent();        
        }
     catch (Exception $e)
        {
        echo 'Excepción capturada: ',  $e->getMessage();
        }
    }

public function init()
    {
     try
        {
        $cifrado = MCRYPT_RIJNDAEL_128;
        $clave = "bcoactivo";
        $modo = MCRYPT_MODE_ECB;
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND);
        $cadena="Alejandro";
        $encriptado= base64_encode(mcrypt_encrypt($cifrado, $clave, trim($cadena), $modo, $iv));
        print $encriptado;
        }
     catch (Exception $e)
        {
        echo 'Excepción capturada: ',  $e->getMessage();
        }
    }

}

$ejecucion= new prueba_cripta();
$ejecucion->main();
$ejecucion->init();
dispose($ejeucion);


#echo  trim(mcrypt_decrypt($cifrado, $clave, base64_decode($cadena), $modo, $iv));

#$str = 'Alejandro';
#$str = base64_encode($str);
#echo $str;
#$str='Jz3t3iGuJIeVWoluzaYnupF8gxQpRKejW+QbEHLG5YI=';
#$str =base64_decode($str);
#echo $str;


#function encriptar($cadena, $clave = "bcoactivo") {
#        if(trim($cadena)se64_encode(mcrypt_encrypt($cifrado, $clave, trim($cadena), $modo, $iv));){
#                global $cifrado,$modo,$iv;
#                return base64_encode(mcrypt_encrypt($cifrado, $clave, trim($cadena), $modo, $iv));
#        }
#}
#
#function desencriptar($cadena, $clave = "bcoactivo") {
#        if(trim($cadena)){
#                global $cifrado,$modo,$iv;
#                return trim(mcrypt_decrypt($cifrado, $clave, base64_decode($cadena), $modo, $iv));
#        }
#}

#import_request_variables("gp", "p_");
?>
