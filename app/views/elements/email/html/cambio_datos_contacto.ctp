Estimado(a) Cliente: <?php echo $nombre_destino; ?>,
<br/><br/>

Para <strong>BANCO ACTIVO</strong> es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado un cambio del <?php
switch($tipo_cambio){
	case 'ambos':
		echo 'e-mail y numero de telefono celular';
		break;
	case 'email':
		echo 'e-mail';
		break;
	case 'celular':
		echo 'numero de telefono celular';
		break;
} ?> donde te enviamos las notificaciones.