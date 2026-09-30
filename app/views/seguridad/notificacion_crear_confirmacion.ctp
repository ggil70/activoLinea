<?php
    /**
     * Vista para la confirmación Notificaciones de Viaje
     */
?>

<div>
<h2 class="user">Notificaci&oacute;n de Viaje/Ausencia</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Ubicaci&oacute;n de Seguridad</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
                
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
            <td class="etiqueta">Ubicaci&oacute;n de Seguridad:</td>
            <td><?php echo $paises[$this->data['NotificacionAusencia']['siglas']] ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Fecha desde:</td>
		<td><?php echo $this->data['NotificacionAusencia']['fecha_inicio']['day'] ."/". $this->data['NotificacionAusencia']['fecha_inicio']['month']."/". $this->data['NotificacionAusencia']['fecha_inicio']['year'] ?></td>
	</tr>               
        
	<tr>
		<td class="etiqueta">Fecha hasta:</td>
		<td><?php echo $this->data['NotificacionAusencia']['fecha_fin']['day'] ."/". $this->data['NotificacionAusencia']['fecha_fin']['month']."/". $this->data['NotificacionAusencia']['fecha_fin']['year'] ?></td>		
	</tr>        				               
	<tr>
		<td colspan="2" class="botonera">

		<?php 
		echo $html->link(
			'Volver',
			'/seguridad/notificacion_crear/v',
			array(
                            'class'=>'button',
                            'id'=>'volver'
			)
		);
		
		echo '&nbsp;&nbsp;&nbsp;';			
		
                echo $html->link(
                    'Aceptar',
                    '/seguridad/notificacion_crear_resultado',
                    array(
                        'class'=>'button bloqueo_repetir'
                    )
                ); 
		?>
                
		</td>
        </tr>
	</table>            
</div>


