<?php
    /**
     *  Vista que muestra un alerta de que el Pago masivo de Proveedores posee el mismo monto que el último lote cargado
     */
?>

<div>
<h2 class="user"><?php echo $titulo; ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Selecci&oacute;n Archivo</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->tdc_validada() && $usuario['rol_juridico_id']==2 ) { ?>
<div class="paso_desc">Tarjeta de Coordenadas</div>
<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">	
	
<thead>
	<tr>
		<td class="resumen_paginacion" colspan="2">
			<?php echo _m('masivo.proveerdor.alerta') ?>
		</td>
	</tr>
</thead>

<?php
echo '<tr><td class="botonera" colspan="2">';

echo $html->link(
	'Volver',
	'pago_masivo_me/m',
	array(
		'class'=>'button'
	)
);

echo '&nbsp;&nbsp;';

echo $html->link(
	'Continuar',
	'pago_masivo_confirmar_me',
	array(
		'class'=>'button'
	)
);

echo '</td></tr>';
?>

</table>

</div>
