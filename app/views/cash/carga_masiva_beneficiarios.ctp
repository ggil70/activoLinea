<?php
    /**
     *  Vista para la carga masiva de beneficiarios Nómina/Proveedores
     */
?>
<div>
<h2 class="user"><?php echo $titulo; ?></h2>
<div class="line_title_orange"></div>
</div>
<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<?php

	echo $form->create('CargaMasivaBeneficiariosCash',
		array(
			'url'=> array(
				'controller'=>'cash',
				'action'=>'carga_masiva_beneficiarios',
				
			),
			'enctype' => 'multipart/form-data',
			'inputDefaults'=>array(
				'div'=>false,
				'label'=>'etiqueta',
				'before'=>'<tr><td class="etiqueta">',
				'between'=>'</td><td class="campo">',
				'after'=>'</td></tr>',
				'error'=>false,
				
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
			'style'=>'width:400px'
		)
	);
?>	
<tr><td colspan="2">Por favor seleccione el archivo para la carga masiva de beneficiarios</td></tr>

<?php	
	echo $form->input(
		'archivo',
		array(
			'label'=>'<span class="requerido">* </span>Archivo',
			'type'=>'file',
			'size'=>40
		)
	);
	if (isset($error_archivo) && $error_archivo){
            if($mensaje_error=='extension'){
                echo '<tr><td>&nbsp;</td><td><label class="error">'. _m('seleccion.requerida',array('un archivo válido')) .'</label></td></tr>';
            }else{
                echo '<tr><td>&nbsp;</td><td><label class="error">'. $mensaje_error .'</label></td></tr>';
            }		                
	}

	echo '<tr><td colspan="2"><span class="requerido">*</span>&nbsp;<span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td colspan="2" class="botonera">';
	
	echo $html->link(
		'Aceptar',
		'javascript:',
		array(
			'id'=>'aceptar',
			'class'=>'button boton_accion masivoProcess'
		)
	);
	
	echo '</td></tr>';
?>


</table>

<div>
        <p align="left"><b>NOTA:</b><br/>
            Estimado Cliente, por favor verifique que el archivo txt no contenga caracteres especiales, acentos, ni espacios en blanco.
        </p>
</div>
</div>

<div class="basic-modal-content" id="procesando">
	<h3>Procesando...</h3>
        <p><?php echo(_m('procesando.transaccion','el archivo de los beneficiarios')); ?></p>
</div>


<script>
$(function(){
	
	$('.button').click(function(){
		if($('#CargaMasivaBeneficiariosCashCargaMasivaBeneficiariosForm').valid()){
			$('#procesando').modal({minHeight:120,minWidth:400,close:false});
			return false;
		}
	});
	
	$('#CargaMasivaBeneficiariosCashCargaMasivaBeneficiariosForm').validate({
		rules:{
			'data[CargaMasivaBeneficiariosCash][archivo]':{
				required:true
			}
		},
		messages:{
			'data[CargaMasivaBeneficiariosCash][archivo]':{
				required:<?php echo _m('seleccion.requerida',array('el archivo'), true) ?>
			}
		}
	});
});
</script>