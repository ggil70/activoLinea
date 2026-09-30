<?php
    /**
     * Vista para la confirmaciÃ³n afiliaciones pago activo
     */
?>

<div>
<h2 class="user">Solicitud Cuenta Cash</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Información</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmación</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>
<div class="contenedor_formulario">
<?php 
	echo $form->create('Confirmacion',
		array(
			'url'=> '/solicitudes/solicitar_confirmacion_cuenta_cash',
			'id'=>'ConfirmarCrearForm',
			'inputDefaults'=>array(
				'div'=>false,
				'label'=>'etiqueta',
				'before'=>'',
				'between'=>'',
				'after'=>'',
				'error'=>false
			)
		)
	);
        
        echo $form->hidden('conf',array(
            'value'=>'1'
        ));
?>

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operación que desea realizar:</td>
	</tr>
	</thead>
	
	<tr>
		<td class="etiqueta">Nombre del Cliente:</td>
		<td><?php echo desencriptar($usuario[nombre]); ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Identificación del Cliente:</td>
		<td><?php 
				$tipo_cedula = $usuario[tipo_cedula];
				$cedula = sprintf('%09d',desencriptar($usuario[cedula]));
				echo $tipo_cedula . $cedula 
			?></td>
	</tr>

	<tr>
		<td class="etiqueta">Tipo de Cuenta</td>
		<td><?php echo "CUENTA CORRIENTE CASH USD" ?></td>
	</tr>
	<tr>
	    <td class="etiqueta">T&eacute;rminos y Condiciones:</td>
	    <td>
	        <?php
				echo $form->checkbox('acepto', 
	                        array(
	                            'hiddenField' => false,
	                            'div'=>false,
	                            'id'=>'acepto'
	                    )
	             );

	            echo $html->link(
	                    'Descargar',
	                    '/solicitudes/contrato_cuenta_cash');
	        ?>
	    </td>
	</tr>
    </table>
    <br>
    <table style="margin: 0 auto; width: 550px;">
        <tr>
        <tr>
            <td>
                <div id="messageBox"></div>
            </td>
        </tr>
        <tr>
		<td class="botonera">

		<?php 
		echo $html->link(
			'Volver',
			'/solicitudes/solicitar_cuenta_cash',
			array(
				'class'=>'button',
				'id'=>'volver'
			)
		);
		
		echo '&nbsp;&nbsp;&nbsp;';
		
		echo $form->submit('Aceptar',array(
				'class'=>'button',
				'id'=>'aceptar',
                                'div'=>false
			)); 
                
		?>
		</td>
        </tr>
	</table>
        
    <?php 
        echo $form->end(); 
    ?>
</div>
<script type="text/javascript">
var ajaxPendiente = false;
var servicioValido = true;
var existeProducto = true;
var loadpage = false;

$(function(){	
	
	// para la validacion
	$('#ConfirmarCrearForm').validate({
		submitHandler:function(form){
			$.each($('tr td div.error-message'),function(){
				if($(this).siblings().size()==0){
					$(this).parent().parent().remove();
				}
			});
			
			if(!ajaxPendiente){
				form.submit();
			}
		},
                rules:{
			'data[Confirmacion][acepto]':{
				required:true
			}
		},
		messages:{				
			'data[Confirmacion][acepto]':{
				required:'Estimado Cliente, por favor acepte los Términos y Condiciones.'
			}	
		},
                errorLabelContainer: "#messageBox"
	});	
	existeProducto = false;
	loadpage = true;
	
 });
</script>


