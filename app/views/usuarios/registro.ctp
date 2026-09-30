<?php
/**
 * Formulario de aceptación de contrato de uso de Activo en línea
**/
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
<?php if($this->Session->check('login.juridico')){ ?>
	<div class="paso_nro">1</div>
	<div class="paso_desc">Autenticar Empresa</div>
	<div class="paso_nro_actual">2</div>
        <div class="paso_desc_actual">Aceptaci&oacute;n de Contrato</div>	
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Tarjeta de Coordenadas</div>
<?php }else{ ?>
        <div class="paso_nro">1</div>
        <div class="paso_desc">Autenticaci&oacute;n del Cliente</div>
	<div class="paso_nro_actual">2</div>
        <div class="paso_desc_actual">Aceptaci&oacute;n de Contrato</div>	
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Configuraci&oacute;n de L&iacute;mites</div>
<?php } ?>	
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style="width:100%">
	
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'usuarios',
				'action'=>'registro'
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
			'width'=>'100%'
		)
	);
	
	?>
	
	<tr>
		<td>
			<?php 
                        
                        if($this->Session->check('login.juridico')){
                            echo $this->element('contrato_empresa');
                        }else{
                            echo $this->element('contrato');
                        }
                            
                        ?>
		</td>
	</tr>
	
	<tr><td>&nbsp;</td></tr>
	
	<tr><td><b>Si esta de acuerdo escriba "ACEPTO" en el espacio de abajo, y presione el bot&oacute;n ACEPTO. En caso de no estar de acuerdo presione el bot&oacute;n CANCELAR</b></td></tr>
	
	<tr>
		<td style="text-align:center;">
			<?php echo $form->text('decision', array('maxlength'=>'6', 'size'=>'6') ) ?>
		</td>
	</tr>
	<?php
	
	if ($form->isFieldError('decision')){
			echo '<tr><td class="texto_centro">'.$form->error('decision',array(
				'decision'=>_m('acepto.contrato',null),
				'requerido'=>_m('acepto.contrato',null)
			)).'</td></tr>';
	}
	
	echo '<tr><td class="submit">';
	
	
	echo $html->link(
		'Acepto',
		'#',
		array(
			'class'=>'button envio_formulario',
			'div' => false
		)
	);	
	
	echo '&nbsp;&nbsp;&nbsp;';
        
	if($this->Session->check('login.juridico')){
            echo $html->link(
                'Imprimir',
                '/files/CONTRATO_ACTIVO_EN_LINEA_EMPRESA.pdf',
                array(
                        'class'=>'button',
                        'target'=>'_blank'
                )
            );
        }else{
            echo $html->link(
                'Imprimir',
                '/files/CONTRATO_ACTIVO_EN_LINEA.pdf',
                array(
                        'class'=>'button',
                        'target'=>'_blank'
                )
            );
        }
            
		
	echo '&nbsp;&nbsp;&nbsp;';
	
	echo $html->link(
		'Cancelar',
		'/',
		array(
			'class'=>'button'
		)
	);
	
	echo '</td></tr></table>';
	
	
	echo $form->end();
	
	?>
</div>

<script>
$(function(){
	
	// para la validacion
	$('#UsuarioRegistroForm').validate({
	
		rules:{
			'data[Usuario][decision]':{
				required:true,
				iguala:'ACEPTO'
			}
			
		},	
		messages:{
			'data[Usuario][decision]':{
				required:<?php echo _m('acepto.contrato',null,true) ?>,
				iguala:<?php echo _m('acepto.contrato',null,true) ?>
			}
			
		}
	});
	
});
</script>
