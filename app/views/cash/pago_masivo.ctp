<?php
    /**
     *  Vista para el Pago masivo de Nómina/Proveedores
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
<?php
echo $form->create('PagoCash',
	array(
		'url'=> array(
			'controller'=>'cash',
			'action'=>'pago_masivo',
			$tipo
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
		'align'=>'center'
	)
);

echo '<tr><td colspan="2">Por favor seleccione la cuenta y el archivo para la carga masiva del pago:</td></tr>';

echo $form->input(
	'cuentaDebitar',
	array(
		'label'=>'<span class="requerido">* </span>Cuenta Débito:',
		'type'=>'select',
		'options' => $cuentas,
		'empty'=>'Seleccione'
	)
);

if ($form->isFieldError('cuentaDebitar')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('cuentaDebitar',
		array(
			'requerido'=>_m('campo.requerido','la cuenta'),
			'numero'=>_m('formato.invalido','la cuenta')
		)
	).'</td></tr>';
}

echo $form->input(
	'archivo',
	array(
		'label'=>'<span class="requerido">* </span>Archivo TXT:',
		'type'=>'file',
		'size'=>30
	)
);
if ($form->isFieldError('archivo')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('archivo',
		array(
			'extension'=>_m('seleccion.requerida',array('un archivo válido'))
		)
	).'</td></tr>';
}

echo '<tr><td colspan="2" class="botonera">';

echo $html->link(
	'Aceptar',
	'javascript:;',
	array(
		'id'=>'aceptar',
		'class'=>'button boton_accion masivoProcess'
	)
);
echo '</td></tr></table>';

echo $form->end();

echo '<p align="left"><b>NOTA:</b><br/>
			Estimado Cliente, por favor verifique que el archivo txt no contenga caracteres especiales, acentos, ni espacios en blanco.</p>';
?>    
</div>


<div class="basic-modal-content" id="procesando">
	<h3>Procesando...</h3>
        <p><?php echo(_m('procesando.transaccion','el archivo del pago')); ?></p>
</div>

<script type="text/javascript">
$(function(){
	
	$('.boton_accion').click(function(){
		if($('#PagoCashPagoMasivoForm').valid()){
			$('#procesando').modal({minHeight:120,minWidth:400,close:false});
			return false;
		}
	});
	
	$('#PagoCashPagoMasivoForm').validate({
		rules:{
			'data[PagoCash][cuentaDebitar]':{
				required:true, digits:true
			},
			'data[PagoCash][archivo]':{
				required:true
			}
		},
		messages:{
			'data[PagoCash][cuentaDebitar]':{
				required:<?php echo _m('campo.requerido','la cuenta',true) ?>,
				digits:<?php echo _m('formato.invalido','la cuenta',true) ?>
			},
			'data[PagoCash][archivo]':{
				required:<?php echo _m('seleccion.requerida',array('el archivo TXT'), true) ?>
			}
		}
	});
});
</script>