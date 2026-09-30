Estimado(a) Cliente: <?php echo $nombre_destino; ?>,
<br/><br/>

Para <strong>BANCO ACTIVO</strong> es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado el <?php
switch($tipo_cambio){
	case 'Imagen':
		echo 'Cambio de Imagen Antiphising';
		break;
	case 'Preguntas Desafio':
		echo 'Cambio Preguntas y Respuestas de Desafio';
		break;
        case 'Limite de Transferencia':
                echo 'Cambio de Limite de Transferencia';
		break;
} ?>.