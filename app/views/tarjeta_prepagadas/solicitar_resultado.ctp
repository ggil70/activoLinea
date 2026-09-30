<?php
    /**
     * Vista para la confirmación  afiliaciones
     */
?>

<div>
<h2 class="user">Solicitud de Tarjeta Prepago CASH  </h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc_actual">Código de Autorización</div>
<div class="paso_nro_actual">4</div>
<?php } ?>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>




<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion"><p class="exito">
		
		Operación realizada satisfactoriamente
		
		</td>
	</tr>
	</thead>
	<tr>
		<td class="etiqueta">Nombre(s) Apellido(s):</td>
		<td><?php echo desencriptar($usuario['nombre']); ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Cédula:</td>
		<td><?php echo desencriptar($usuario['cedula']); ?></td>
	</tr>
 
	<tr>


		<td class="etiqueta">Télefono: </td>
		<td><?php echo $datos_basicos['NIBPF3010Return']['NIBPF3010']['NROCELULAR']; ?></td>
	</tr>
	
        
	<tr>
		<td class="etiqueta">Email</td>
		<td><?php echo $datos_basicos['NIBPF3010Return']['NIBPF3010']['DISPONIBLE3']; ?></td>
	</tr>
	
 <tr>
		<td class="etiqueta">Dirección:</td>
		<td><?php echo trim($datos_basicos['NIBPF3010Return']['NIBPF3010']['DIRECCION01'].$datos_basicos['NIBPF3010Return']['NIBPF3010']['DIRECCION02'].$datos_basicos['NIBPF3010Return']['NIBPF3010']['DIRECCION03'].$datos_basicos['NIBPF3010Return']['NIBPF3010']['DIRECCION04']);?> </td>
	</tr>

	
	<tr>
		<td class="etiqueta">Tipo Tarjeta:</td>
		<td>Física</td>
		
	</tr>

	



	</table>
</div>



