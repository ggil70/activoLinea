<?php
/**
 * Formulario de aceptación de contrato de uso de Activo en línea
**/
?>
<div>
<h2 class="user">Actualizaci&oacute;n de Contrato</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style="width:100%">
	
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'otros_servicios',
				'action'=>'aviso_nuevo_contrato'
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
        <p class="requerido1"  style='text-align:justify'>
            <b>Estimado Cliente, Banco Activo, siempre pensando en Ofrecer Nuevos Productos y Servicios, queremos informarle de la actualización de nuestro Contrato de Servicios, donde hemos incorporado la sección de Servicio de  Pago en  Línea. Para mayor información Consulte el Anexo
            <?php if($this->Session->check('login.juridico')){ ?> "C", <?php }else{ ?> "A" <?php } ?> 
            </b>
        <br>
        </p>
	
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
	$('#UsuarioAvisoNuevoContratoForm').validate({
	
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
