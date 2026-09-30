<?php
    /**
     * Vista para la confirmación afiliaciones pago activo
     */
?>

<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<?php 
	echo $form->create('Confirmacion',
		array(
			'url'=> '/afiliaciones/crear_confirmacion_pago_activo',
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
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
		<td class="etiqueta">Tipo de Afiliaci&oacute;n:</td>
		<td>Saldo Activo</td>
	</tr>
	<tr>
		<td class="etiqueta">Cuenta:</td>
		<td><?php echo $this->Activo->enmascara('01710000'.$this->data['Afiliacion']['numero_cuenta']) ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Tarjeta de Cr&eacute;dito:</td>
		<td><?php echo $this->Activo->enmascara($this->data['Afiliacion']['numero_tarjeta']) ?></td>
	</tr>
        <tr>
            <td class="etiqueta">T&eacute;rminos y Condiciones:</td>
            <td>
                <?php
                    if(strlen($this->data['Afiliacion']['numero_cuenta'])>10){
                        $nro_cta = substr($this->data['Afiliacion']['numero_cuenta'], 10);
                    }
                    else{
                        $nro_cta = $this->data['Afiliacion']['numero_cuenta'];
                    }
                
                    echo $html->link(
                            $nro_cta,
                            '/afiliaciones/contrato_pago_activo');
                ?>
            </td>
        </tr>
    </table>
    <br>
    <table style="margin: 0 auto; width: 550px;">
        <tr>
            <td>
                <?php 
                    echo $form->checkbox('acepto', 
                                array(
                                    'hiddenField' => false,
                                    'div'=>false,
                                    'id'=>'acepto'
                            )
                     );
                ?>
                <label for="acepto" style="cursor: pointer">
                    Acepto los T&eacute;rminos y Condiciones
                    <span class="requerido">* </span>
                </label>
                <br>
                <span class="requerido" style="font-size: 12px">* Campo obligatorio</span>
            </td>
        </tr>
        <tr>
            <td>                
            </td>
        </tr>
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
			'/afiliaciones/crear_pago_activo/v',
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
				required:'Estimado Cliente, por favor acepte los T&eacute;rminos y Condiciones.'
			}	
		},
                errorLabelContainer: "#messageBox"
	});	
	existeProducto = false;
	loadpage = true;
	
 });
</script>