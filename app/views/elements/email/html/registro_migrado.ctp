Estimado(a) Cliente: <?php echo desencriptar($master['Usuario']['nombre']); ?>,
<br/><br/>
BANCO ACTIVO le informa que se ha registrado en el sistema de Banca en Linea el 
siguiente Usuario:
<br/><br/>
<b>Rif:</b> <?php echo $estado['Usuario']['tipoRif'].'-'.$estado['Usuario']['nroRif']; ?><br/>
<b>Cedula:</b> <?php echo $estado['Usuario']['tipoCedulaRif'].'-'.$estado['Usuario']['nroCedulaRif']; ?><br/>
<b>Nombre:</b> <?php echo $estado['Usuario']['nombre']; ?><br/>
<b>Perfil:</b> <?php echo $perfil; ?><br/>
<b>Login:</b> <?php echo $estado['Usuario']['nomUsuario']; ?>
<br/><br/>
