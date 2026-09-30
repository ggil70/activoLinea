<?php
/**
 * Formulario de configuración de montos límites para transferencias y pagos
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
	
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'usuarios',
				'action'=>'limite_montos'
			),
		)
	);
	
	echo $html->tag(
		'table',
		null,
		array(
			'class'=>'formulario',
			'cellpadding'=>'0',
			'cellspacing'=>'0',
			'border'=>'0'
		)
	);
	

	?>
	
	<tr><td>A continuaci&oacute;n Ud. debe establecer los l&iacute;mites de montos diario por transacci&oacute;n para Pagos, Transferencias, Pagos de Nomina y Proveedores</td></tr>
	
	<tr><td>&nbsp;</td></tr>
	
	<tr>
		<td style="padding:0; margin:0;">
			<table cellpadding="0" cellspacing="0" border="0" class="tabla" align="center">
				<thead>
					<td>Tipo de transferencia</td>
					<td>L&iacute;mites&nbsp;m&aacute;ximos&nbsp;permitidos<br/>por&nbsp;Banco&nbsp;Activo</td>
					<td width="250px">L&iacute;mites establecidos<br/>por el cliente</td>
				</thead>				
				<tr>
					<td>Internas a Terceros (Banco Activo)</td>
					<td class="texto_derecho">
						<?php if(empty($limites['max_it_trans'])) {
							echo 'Sin límite';
						} else {
							echo $limites['max_it_trans'];
						} ?>
					</td>
					<td><?php echo $form->text(
						'it_transaccion',
						array(
							'class'=>'it_trans inputs monto',
							'maxlength'=>23,
							'size'=>24,
						)
					);
				
					if ($form->isFieldError('it_transaccion')){
						echo $form->error('it_transaccion',
							array(
								'numerico'	=> _m('formato.invalido',array('el monto')),
								'monto_limite'	=> _m('monto.limite.config')
							)
						);
					} ?></td>
				</tr>
				
				<tr>
					<td>Otros Bancos</td>
					<td class="texto_derecho">
						<?php if(empty($limites['max_e_trans'])){
							echo 'Sin límite';
						} else {
							echo $limites['max_e_trans'];
						} ?>
					</td>
					<td><?php echo $form->text(
						'e_transaccion',
						array(
							'class'=>'e_trans inputs monto',
							'maxlength'=>23,
							'size'=>24,
						)
					);
				
					if ($form->isFieldError('e_transaccion')){
						echo $form->error('e_transaccion',
							array(
								'numerico'	=> _m('formato.invalido',array('el monto')),
								'monto_limite'	=> _m('monto.limite.config')
							)
						);
					} ?></td>
				</tr>
			</table>
                        <?php if($usuario['perfil_ib_id']=='2') { ?>
                        <br>
                        <table cellpadding="0" cellspacing="0" border="0" class="tabla" align="center">
				<thead>
					<td>Pago de Nómina</td>
					<td>L&iacute;mites&nbsp;m&aacute;ximos&nbsp;permitidos<br/>por&nbsp;Banco&nbsp;Activo</td>
					<td width="250px">L&iacute;mites establecidos<br/>por el cliente</td>
				</thead>				
				<tr>
					<td>Internas a Terceros (Banco Activo)</td>
					<td class="texto_derecho">
						<?php if(empty($limites['max_it_n'])) {
							echo 'Sin límite';
						} else {
							echo $limites['max_it_n'];
						} ?>
					</td>
					<td><?php echo $form->text(
						'it_transaccion_n',
						array(
							'class'=>'it_trans inputs monto',
							'maxlength'=>23,
							'size'=>24,
						)
					);
				
					if ($form->isFieldError('it_transaccion')){
						echo $form->error('it_transaccion_n',
							array(
								'numerico'	=> _m('formato.invalido',array('el monto')),
								'monto_limite'	=> _m('monto.limite.config')
							)
						);
					} ?></td>
				</tr>
				
				<tr>
					<td>Otros Bancos</td>
					<td class="texto_derecho">
						<?php if(empty($limites['max_e_n'])){
							echo 'Sin límite';
						} else {
							echo $limites['max_e_n'];
						} ?>
					</td>
					<td><?php echo $form->text(
						'e_transaccion_n',
						array(
							'class'=>'e_trans inputs monto',
							'maxlength'=>23,
							'size'=>24,
						)
					);
				
					if ($form->isFieldError('e_transaccion')){
						echo $form->error('e_transaccion_n',
							array(
								'numerico'	=> _m('formato.invalido',array('el monto')),
								'monto_limite'	=> _m('monto.limite.config')
							)
						);
					} ?></td>
				</tr>
			</table>
                        <br>
                        <table cellpadding="0" cellspacing="0" border="0" class="tabla" align="center">
				<thead>
					<td>Pago a Proveedores</td>
					<td>L&iacute;mites&nbsp;m&aacute;ximos&nbsp;permitidos<br/>por&nbsp;Banco&nbsp;Activo</td>
					<td width="250px">L&iacute;mites establecidos<br/>por el cliente</td>
				</thead>				
				<tr>
					<td>Internas a Terceros (Banco Activo)</td>
					<td class="texto_derecho">
						<?php if(empty($limites['max_it_p'])) {
							echo 'Sin límite';
						} else {
							echo $limites['max_it_p'];
						} ?>
					</td>
					<td><?php echo $form->text(
						'it_transaccion_p',
						array(
							'class'=>'it_trans inputs monto',
							'maxlength'=>23,
							'size'=>24,
						)
					);
				
					if ($form->isFieldError('it_transaccion_p')){
						echo $form->error('it_transaccion',
							array(
								'numerico'	=> _m('formato.invalido',array('el monto')),
								'monto_limite'	=> _m('monto.limite.config')
							)
						);
					} ?></td>
				</tr>
				
				<tr>
					<td>Otros Bancos</td>
					<td class="texto_derecho">
						<?php if(empty($limites['max_e_p'])){
							echo 'Sin límite';
						} else {
							echo $limites['max_e_p'];
						} ?>
					</td>
					<td><?php echo $form->text(
						'e_transaccion_p',
						array(
							'class'=>'e_trans inputs monto',
							'maxlength'=>23,
							'size'=>24,
						)
					);
				
					if ($form->isFieldError('e_transaccion_p')){
						echo $form->error('e_transaccion',
							array(
								'numerico'	=> _m('formato.invalido',array('el monto')),
								'monto_limite'	=> _m('monto.limite.config')
							)
						);
					} ?></td>
				</tr>
			</table>
                        <?php } ?>  
		</td>
	</tr>
	
	<tr><td><div id="error-todos">&nbsp;</div></td></tr>

	<tr>
		<td class="mensaje">
			<b>NOTA:</b><br/>
			Usted puede definir un monto distinto a los l&iacute;mites m&aacute;ximo permitido. En cuyo caso,
			el monto que coloque podr&aacute; estar por debajo y en ning&uacute;n caso por encima
			de los L&iacute;mites M&aacute;ximo Permitido por Banco Activo.
		</td>
	</tr>
	
	<tr><td>&nbsp;</td></tr>
	
	<tr>
		<td style="color:#CC0000;" class="mensaje">
			<b>Importante:</b><br/>
			Si usted se adhiere a cualquiera de los l&iacute;mites de montos establecidos por Banco Activo,
			y los l&iacute;mites sufren alg&uacute;n tipo de modificaci&oacute;n (hacia arriba o abajo), el sistema ajustar&aacute; los montos
			establecidos por usted en funci&oacute;n a dicho movimiento.<br/>
			Aquellos valores distintos a esos l&iacute;mites se mantendr&aacute;n siempre y cuando el monto se encuentre por debajo del l&iacute;mite
			m&aacute;ximo permitido.

		</td>
	</tr>
	
	
	<?php
	
	echo '<tr><td class="submit">';
	
	echo $form->submit(
		'Aceptar',
		array(
			'class'=>'button',
			'div' => false
		)
	);
	
	echo '</td></tr><tr><td>&nbsp;</td></tr></table>';
	
	echo $form->end();
	
	?>
</div>





<script>
$(function(){
	
	// para la validacion
	$('#UsuarioLimiteMontosForm').validate({
		rules:{
			'data[Usuario][it_transaccion]':{
				numberVE:true
				<?php if(!empty($limites['max_it_trans'])) { ?>
				,maxMontoLimite: '<?php echo $limites['max_it_trans'] ?>'
				<?php } ?>
			},
			'data[Usuario][e_transaccion]':{
				numberVE:true
				<?php if(!empty($limites['max_e_trans'])) { ?>
				,maxMontoLimite: '<?php echo $limites['max_e_trans'] ?>'
				<?php } ?>
			},
                        'data[Usuario][it_transaccion_n]':{
				numberVE:true
				<?php if(!empty($limites['max_it_n'])) { ?>
				,maxMontoLimite: '<?php echo $limites['max_it_n'] ?>'
				<?php } ?>
			},
			'data[Usuario][e_transaccion_n]':{
				numberVE:true
				<?php if(!empty($limites['max_e_n'])) { ?>
				,maxMontoLimite: '<?php echo $limites['max_e_n'] ?>'
				<?php } ?>
			},
                        'data[Usuario][it_transaccion_p]':{
				numberVE:true
				<?php if(!empty($limites['max_it_p'])) { ?>
				,maxMontoLimite: '<?php echo $limites['max_it_p'] ?>'
				<?php } ?>
			},
			'data[Usuario][e_transaccion_p]':{
				numberVE:true
				<?php if(!empty($limites['max_e_p'])) { ?>
				,maxMontoLimite: '<?php echo $limites['max_e_p'] ?>'
				<?php } ?>
			}
		},
		messages:{
			'data[Usuario][it_transaccion]':{
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				maxMontoLimite: <?php echo _m('monto.limite.config',null,true) ?>
			},
			'data[Usuario][e_transaccion]':{
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				maxMontoLimite: <?php echo _m('monto.limite.config',null,true) ?>
			},
                        'data[Usuario][it_transaccion_n]':{
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				maxMontoLimite: <?php echo _m('monto.limite.config',null,true) ?>
			},
			'data[Usuario][e_transaccion_n]':{
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				maxMontoLimite: <?php echo _m('monto.limite.config',null,true) ?>
			},
                        'data[Usuario][it_transaccion_p]':{
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				maxMontoLimite: <?php echo _m('monto.limite.config',null,true) ?>
			},
			'data[Usuario][e_transaccion_p]':{
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				maxMontoLimite: <?php echo _m('monto.limite.config',null,true) ?>
			}         
		},
		errorPlacement: function(error, element) {
			$('div.error-message').remove();
			$('#error-todos').html( error );
		}
	});
	
});
</script>
