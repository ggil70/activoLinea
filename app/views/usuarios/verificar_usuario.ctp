<?php
/**
 * Muestra resultado de la verificación de la disponibilidad del nombre de usuario
 * en el registro 
**/

if(isset($valido)){
	echo  '<span class="exito">'.$mensaje.'</span>';;
} else {
	echo  '<span class="error-message">'.$mensaje.'</span>';
}
?>