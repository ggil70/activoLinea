<?php
    /**
     *  Vista para mostrar el listado empresas participantes en el proceso de votación
     */
?>
<div class="espacio_vertical_20"></div>
<div>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Votaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="" width="850px">

<?php
	echo $form->create('AuditoresExternos',
		array(
			'url'=> array(
				'controller'=>'auditores_externos',
				'action'=>'auditores_externos'
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

echo '<tr><td style="text-align:center"><strong>Bienvenidos al proceso de Elección de Auditores Externos de Banco Activo, Banco Universal, C.A.</strong></td></tr>';

echo '<tr><td>';
?>

<p class="mensaje">Dando cumplimiento al art&iacute;culo 83 del Decreto con Rango, Valor y Fuerza de Ley de Reforma Parcial de la Ley de las Instituciones del Sector Bancario y la Resoluci&oacute;n N°065.11 publicada en Gaceta Oficial el 23-02-2011, se requiere la participaci&oacute;n de los usuarios y usuarias en la elecci&oacute;n de los auditores externos de los bancos en la que ellos han confiado sus operaciones financieras.
<br><br>
A los efectos de hacer cumplir este decreto, Banco Activo solicita tu participaci&oacute;n para la elecci&oacute;n de la Sociedad Auditora que se encargar&aacute; de la verificaci&oacute;n de los registros contables y operaciones financieras
<br><br>
Para elegir la Sociedad Auditora, s&oacute;lo tienes que seleccionar la opci&oacute;n de tu preferencia:</p>
</td></tr>

<tr>
	<td>
		<label for="data[AuditoresExternos][empresa_seleccion]" class="error manual"><?php echo _m('seleccion.requerida.AE','de las empresas'); ?></label>
		<?php
			if ($form->isFieldError('empresa_seleccion')){
				echo $form->error('empresa_seleccion', array(
															'requerido'=>_m('seleccion.requerida.AE','de las empresas')
				));
			}
		?>
	</td>
</tr>

<table class="" cellpadding="0" cellspacing="0" border="0" style="margin: 0px auto" width="290px">
<?php 
if(!empty($empresas)){
 //foreach($empresas as $e){
for($i = 0; ($i < count($empresas)); $i++){

 $clase = "empresa_seleccion_".trim($empresas[$i]['AUDEMP']);
 $valorEmpresa = trim($empresas[$i]['AUDEMP']).'-'.trim($empresas[$i]['AUDNEM']);

 ?>
		<tr>
			<td class="etiqueta">
				<input
					type="radio"
					name="data[AuditoresExternos][empresa_seleccion]"
					value="<?php echo $valorEmpresa;?>" 
					class="<?php echo $clase;?>"
					<?php echo isset($this->data['AuditoresExternos']['empresa_seleccion']) && $this->data['AuditoresExternos']['empresa_seleccion'] == $valorEmpresa ? 'checked="checked"' : '' ?>
				>
				<?php echo trim($empresas[$i]['AUDNEM']);?> <br/>
			</td>
		</tr>
<?php }
}

?>	

	<?php 
	echo '<tr><td class="texto_centro">';

	echo $form->submit(
		'Votar',
		array(
			'class'=>'button',
			'id'=>'votar'
		)
	);		

	echo '</td></tr></table>';


	echo $form->end();

	?>
	
</div>

<script>	
$(function(){
	
	
	
	$('#AuditoresExternosAuditoresExternosForm').validate({
		rules:{
			'data[AuditoresExternos][empresa_seleccion]':{
				required:true
			}
		},	
		messages:{
			'data[AuditoresExternos][empresa_seleccion]':{
				required:<?php echo _m('seleccion.requerida.AE','de las empresas',true) ?>
			}
		}
	});
	
	
	
	
});
</script>
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
