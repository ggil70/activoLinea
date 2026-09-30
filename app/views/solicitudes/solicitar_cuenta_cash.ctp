<?php
/**
 * Formulario de transferencias a cuentass afiliadas de terceros en otros bancos
**/
?>
<div>
<h2 class="user">Solicitar Cuenta Cash</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado de Operación</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<?php

	echo $form->create('SolicitudCuentaCash',
		array(
			'url'=> array(
				'controller'=>'solicitudes',
				'action'=>'solicitar_cuenta_cash'
			),
			'inputDefaults'=>array(
				'div'=>false,
				'label'=>'etiqueta',
				'before'=>'<tr><td class="etiqueta">',
				'between'=>'</td><td class="campo">',
				'after'=>'</td></tr>',
				'error'=>false
			)
		)
	);



	echo $html->tag(
		'table',
		null,
		array(
			'class'=>'formulario',
			'cellpadding'=>'0',
			'cellspacing'=>'0',
			'border'=>'0',
			'align'=>'center',
			'style'=>'table-layout:fixed'
		)
	);
	
	// La primera fila define la tabla con layout fixed
	echo '<tr><td style="width:150px"></td><td style="width:400px"></td></tr>';
	
	echo '<tr><td colspan="2">A continuación proceda a solicitar cuenta cash.</td></tr>';

	echo '
	<tr>
	<td class="etiqueta">Nombre del Cliente:</td>
	<td><b> ' . desencriptar($usuario[nombre]). ' </b></td>
	</tr>
	';
	$tipo_cedula = $usuario[tipo_cedula];
	$cedula = sprintf('%09d',desencriptar($usuario[cedula]));
	echo '
	<tr>
	<td class="etiqueta">Identificación del Cliente:</td>
	<td><b> ' . $tipo_cedula . $cedula . ' </b></td>
	</tr>
	';
	echo '
	<tr>
	<td class="etiqueta">Tipo de Cuenta:</td>
	<td><b> ' . 'CUENTA CORRIENTE CASH USD' . ' </b></td>
	</tr>
	';
		
	?>

	<?php
        	 
 	echo'<tr><td colspan="2"><span class="azul"><strong>Nota:Estimado Cliente, a continuación se realizara la creación de su cuenta cash USD.</strong></span></td></tr>';      
        echo '<tr><td colspan="2"></td></tr>';                
        
	echo '<tr><td>&nbsp;</td><td class="submit">';

		
	echo $html->link(
		'Aceptar',
		'/solicitudes/solicitar_confirmacion_cuenta_cash',
		array(
			'class'=>'button bloqueo_repetir'
		)
	);	               
        
	echo '</td></tr></table>';                
        
	echo $form->end();

	?>
</div>

