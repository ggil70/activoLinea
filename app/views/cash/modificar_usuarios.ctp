<?php
    /**
     *  Vista para la modificación de usuarios Nómina/Proveedores carga manual
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
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

	echo $form->create('AfiliacionCash',
		array(
			'url'=> array(
				'controller'=>'cash',
				'action'=>'modificar_usuarios'
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
	
	echo $form->hidden('id');
	echo $form->hidden('editando');
	echo $form->hidden('tipo');

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
	
	?>
	<!-- La primera fila define la tabla con layout fixed -->
	<tr><td style="width:180px"></td><td style="width:250px"></td></tr>
	
	<tr><td colspan="2"><?php echo $titulo2 ?></td></tr>

	<?php
	
	echo $form->input(
		'nombre',
		array(
			'label'=>'<span class="requerido">* </span>Nombre Beneficiario:',
			'type'=>'text',
			'class'=>$estado['tipo']=='n' ? 'letrasEspacio' : 'alfanumericoEspacio',
			'maxlength'=>30
			
		)
	);

	if ($form->isFieldError('nombre')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('nombre',
			array(
				'requerido'=>_m('campo.requerido','el nombre'),
				'alfabetico'=>_m('alfabetico.invalido','el nombre'),
				'alfanumerico'=>_m('alfanumerico.invalido','el nombre')
			)
		).'</td></tr>';
	}
	
	switch($estado['tipo']){
		case 'n':
			$tipos_cedulas = array('V'=>'V','E'=>'E','P'=>'P');
			break;
		case 'p':
			$tipos_cedulas = array('V'=>'V','E'=>'E','P'=>'P','J'=>'J','G'=>'G');
			break;
	}
	
	echo $form->input(
		'tipo_cedula',
		array(
			'after'=>false,
			'label'=>'<span class="requerido">* </span>Identificación Beneficiario:',
			'type'=>'select',
			'class'=>$estado['tipo']=='n' ? 'tipoCedulaRif' : 'tipoRif',
			'options'=>$tipos_cedulas
		)
	);

	echo $form->input(
		'cedula',
		array(
			'before'=>false,
			'between'=>false,
			'label'=>false,
			'type'=>'text',
			'class'=>$estado['tipo']=='n' ? 'nroCedulaRif' : 'nroRif'
		)
	);

	if ($form->isFieldError('tipo_cedula')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('tipo_cedula',
			array(
				'requerido'=>_m('campo.requerido','el tipo de cédula/rif'),
				'valoresPermitidos'=>_m('seleccion.invalida','el tipo de cédula/rif')
			)
		).'</td></tr>';
	} else if ($form->isFieldError('cedula')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('cedula',
			array(
				'requerido'=>_m('campo.requerido','el campo cédula/rif'),
				'longitud'=>_m('valor.invalido','la cédula/rif'),
				'rif'=>_m('valor.invalido', 'la cédula/rif'),
				'noBeneficiario'=>_m('cuenta.no.beneficiario'),
				'unicidad'=>_m('beneficiario.ya.afiliado')
			)
		).'</td></tr>';
	}

	echo $form->input(
		'numero_cuenta',
		array(
			'label'=>'<span class="requerido">* </span>Número Cuenta Beneficiario:',
			'type'=>'text',
			'class'=>'numerico',
			'maxlength' => '20',
			'minlength' => '20'
		)
	);

	if ($form->isFieldError('numero_cuenta')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('numero_cuenta',
			array(
				'nroCuenta'=>_m('valor.invalido',array('el número de cuenta')),
				'requerido'=>_m('campo.requerido','el campo solicitado'),
				'numero'=>_m('formato.invalido','el campo solicitado'),
				'unicidad'=>_m('producto.nodisponible'),
				'cuentaControlada'=>_m('cuenta.controlada', 'con la cuenta del beneficiario'),
				'cuentaInactiva'=>_m('cuenta.inactiva', 'la cuenta del beneficiario'),
				'productoPropio'=>_m('producto.propio')
			)
		).'</td></tr>';
	}
	
	echo $form->input(
		'email',
		array(
			'label'=>'<span class="requerido">* </span>E-mail Beneficiario:',
			'type'=>'text',
			'maxlength'=> 50
		)
	);

	if ($form->isFieldError('email')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('email',
			array(
				'requerido'=>_m('campo.requerido','el email del beneficiario'),
				'correo'=>_m('correo.invalido','el email del beneficiario')
			)
		).'</td></tr>';
	}
	//
	
	echo $form->input(
		'codigoCelular',
			array(
				'after'=>false,
				'label'=>'<span class="requerido" width="100px">* </span>N&uacute;mero Teléfono Celular:',
				'type'=>'select',
				'class'=>'codigoCelular',
				'options'=>array('0414'=>'0414','0424'=>'0424','0416'=>'0416','0426'=>'0426','0412'=>'0412','0422'=>'0422')
			)
		);
		
		echo $form->input(
			'nroCelular',
			array(
				'before'=>false,
				'between'=>false,
				'label'=>false,
				'type'=>'text',
				'class'=>'nroCelular numerico',
				'maxlength'=>7,
				'minlength'=> 7
			)
		);	
		
		if ($this->Form->isFieldError('codigoCelular')){
			echo $form->error('codigoCelular',
				array(
					'requerido'	=> _m('campo.requerido',array('el código del celular')),
					'valoresPermitidos'	=> _m('seleccion.requerida',array('el código del celular'))
				)
			);
		}

		if ($form->isFieldError('nroCelular')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('nroCelular',
				array(
					'valor'=>_m('formato.invalido',array('el número celular')),
					'requerido'=>_m('campo.requerido',array('el número celular'))
				)
			).'</td></tr>';
		}
	
	//
	echo '<tr><td colspan="2"><span class="requerido">*</span>&nbsp;<span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td colspan="2" class="submit">';
	
	echo $html->link(
		'Volver',
		$estado['url_volver'],
		array(
			'class'=>'button',
			'id'=>'volver'
		)
	);
	
	echo '&nbsp;&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'javascript:void(0);',
		array(
			'class'=>'button boton_accion',
			'div' => false
		)
	);

	echo '</td></tr></table>';

	echo $form->end();

	?>
</div>

<script>
$(function(){

	var bancos = <?php echo json_encode(array_keys($bancos)) ?>;

	// para la validacion
	$('#AfiliacionCashModificarUsuariosForm').validate({
		rules:{
			'data[AfiliacionCash][numero_cuenta]':{
				required:true,
				digits:true,
				cualquierCuenta:true,
				inarray:{
					param:<?php echo json_encode(array_keys($bancos)) ?>,
					depends:function(e){
						return $(e).val().length==20;
					}
				}
			},
			'data[AfiliacionCash][nombre]':{
				required:true,
				<?php if($estado['tipo']=='n') { ?>
				alfabeticoEspacio:true
				<?php } else { ?>
				alfanumericoEspacio:true
				<?php } ?>
			},
			'data[AfiliacionCash][cedula]':{
				required:true, 
				digits:{
					depends: function(e){
						return $('#AfiliacionCashTipoCedula').val()=='V';
					}				
				},
				alphanumeric:{
					depends: function(e){
						return $('#AfiliacionCashCedula').val()=='P';
					}				
				}
			},
			'data[AfiliacionCash][email]':{
				required:true, email:true
			},
			'data[AfiliacionCash][nroCelular]':{
				required:true, celular:true
			}		
		},
		messages:{
			'data[AfiliacionCash][numero_cuenta]':{
				required:<?php echo _m('campo.requerido', 'el campo cuenta' , true) ?>,
				digits:<?php echo _m('formato.invalido','el campo cuenta',true)?>,
				cualquierCuenta:<?php echo _m('valor.invalido',array('el número de cuenta'),true) ?>,
				inarray:<?php echo _m('valor.invalido',array('el número de cuenta'),true) ?>,
				minlength:<?php echo _m('digitos.exactos',array('el número de cuenta',20),true) ?>,
				maxlength:<?php echo _m('digitos.exactos',array('el número de cuenta',20),true) ?>
			},
			'data[AfiliacionCash][nombre]':{
				required:<?php echo _m('campo.requerido','el campo nombre',true) ?>,
				alfabeticoEspacio:<?php echo _m('alfabetico.invalido','el nombre',true) ?>,
				alfanumericoEspacio:<?php echo _m('alfanumerico.invalido','el nombre',true) ?>
			},
			'data[AfiliacionCash][banco]':{
				required:<?php echo _m('campo.requerido','el banco',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','el banco',true) ?>
			},
			'data[AfiliacionCash][cedula]':{
				required:<?php echo _m('campo.requerido','el campo c&eacute;dula/rif',true) ?>,
				digits:<?php echo _m('formato.invalido','la c&eacute;dula/rif',true) ?>,
				alphanumeric:<?php echo _m('alfanumerico.invalido','la c&eacute;dula/rif',true) ?>
			},
			'data[AfiliacionCash][email]':{
				required:<?php echo _m('campo.requerido','el email del beneficiario',true) ?>,
				email:<?php echo _m('correo.invalido','el email del beneficiario',true) ?>
			},
			'data[AfiliacionCash][nroCelular]':{
				required:<?php echo _m('campo.requerido',array('el campo número de teléfono celular'),true) ?>,
				celular:<?php echo _m('telefono.invalido',array('el número de teléfono celular'),true) ?>,
				minlength:<?php echo _m('digitos.exactos',array('el número de teléfono celular',7),true) ?>,
				maxlength:<?php echo _m('digitos.exactos',array('el número de teléfono celular',7),true) ?>
			}
		}	
	});
	
});
</script>