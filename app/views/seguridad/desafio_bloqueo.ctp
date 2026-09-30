<?php
/**
 * Mensaje de bloqueo por respuesta de desafío inválida
**/
?>
<div>
<h2 class="user"><?php echo $data_session['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($data_session['pasos']); $i++) { 
	$class = ($i==$data_session['paso_actual']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $data_session['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p class="mensaje"><?php 
	if(isset($data_session['usuario'])){
		$usuario = $data_session['usuario'];
	} else {
		$usuario = $this->Session->read('usuarios.datos');
	}
	
	if($usuario['tipo_rif']){ // SI ES JURIDICO
		if($usuario['rol_juridico_id']>2){
			$mensaje = _m('bloqueo.juridico.creado');
		} else {
			$mensaje = _m('master.bloqueado');
		}
	} else { // SI ES NATURAL
		$mensaje = _m('autenticacion.b.2.22');
	}
	
	echo $mensaje;
?></p>

<div class="botonera">
<?php
echo $html->link(
	'Continuar',
	'/',
	array(
		'class'=>'button'
	)
);

?>
</div>

</div>

