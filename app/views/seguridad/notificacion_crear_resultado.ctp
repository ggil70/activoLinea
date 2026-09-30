<?php
/**
 *  Vista para el resultado de las notificaciones de viaje
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
<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">4</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<?php if(!$this->data['Error']){ ?>
		    						
            <thead>
		<tr>
		<td colspan="2" class="resumen_paginacion"><p class="exito">Operaci&oacute;n realizada satisfactoriamente</p></td>
		</tr>
            </thead>				
		<tr>
                    <td class="etiqueta">Ubicaci&oacute;n de Seguridad:</td>
                    <td><?php echo $paises[$this->data['NotificacionAusencia']['codigo_pais']] ?></td>
                </tr>
                <tr>
                    <td class="etiqueta">Fecha desde:</td>
                    <td><?php echo date("d-m-Y", strtotime($this->data['NotificacionAusencia']['fecha_inicio'])) ?></td>
                </tr>               

                <tr>
                    <td class="etiqueta">Fecha hasta:</td>
                    <td><?php echo date("d-m-Y", strtotime($this->data['NotificacionAusencia']['fecha_fin'])) ?></td>		
                </tr>        	               	
	<?php
	}
	else{
	?>
            <thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('pa.tipocuenta.errado'); ?></div></td>
		</tr>
            </thead>
	<?php 
	}
	?>
            <tr>
                <td colspan="2" class="botonera">
                    <?php 		
                        echo $html->link(
                                'Volver',
                                '/seguridad/listado_notificacion_viaje',
                                array(
                                        'class'=>'button',
                                        'id'=>'volver'
                                )
                        );
                    ?>
		</td>
            </tr>
	</table>
</div>




