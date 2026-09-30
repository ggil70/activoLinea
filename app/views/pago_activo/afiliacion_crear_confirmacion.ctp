<?php
    /**
     * Vista para la confirmación afiliaciones Pago Activo
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
        
        <div id="error_contrato" style="display: none"><label class="error"><?php echo _m('aceptar.condiciones.contrato', true) ?></label></div>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
		<td class="etiqueta">Tipo de Afiliaci&oacute;n:</td>
		<td><?php echo $canal[trim($this->data['Afiliacion']['tipo_servicio'])] ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Tipo de Operaci&oacute;n:</td>
		<td><?php echo $subtipos[$this->data['Afiliacion']['tipo_afiliacion']][$this->data['Afiliacion']['tipo_operacion']] ?></td>
	</tr>               
        
	<tr>
		<td class="etiqueta">Nro. Celular Afiliado:</td>
		<td><?php echo $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['telefono'] ?></td>		
	</tr>
        	
	<tr>
		<td class="etiqueta">E-mail:</td>
		<td><?php echo $this->data['Afiliacion']['email'] ?></td>
	</tr>	
		
        <tr>
		<td class="etiqueta">Canal Seleccionado:</td>
		<td><?php echo $tipos[$this->data['Afiliacion']['tipo_afiliacion']] ?></td>
	</tr>
        
        <?php if(!empty($this->data['Afiliacion']['numero_cuenta'])) {?>
        <tr>
		<td class="etiqueta">Cuenta Corriente:</td>
		<td><?php echo enmascara($this->data['Afiliacion']['numero_cuenta']) ?></td>
	</tr>
        <?php } ?>
        
        <?php if(!empty($this->data['Afiliacion']['numero_cuenta_ahorro'])) {?>
        <tr>
		<td class="etiqueta">Cuenta Ahorro:</td>
		<td><?php echo enmascara($this->data['Afiliacion']['numero_cuenta_ahorro']) ?></td>
	</tr>
        <?php } ?>
        
        <?php if(isset($this->data['Afiliacion']['numero_cuenta_receptora']) && !empty($this->data['Afiliacion']['numero_cuenta_receptora'])) {?>
        <tr>
		<td class="etiqueta">Cuenta Receptora:</td>
		<td><?php echo enmascara($this->data['Afiliacion']['numero_cuenta_receptora']) ?></td>
	</tr>
        <?php } 
            if(empty($juridico)){
        ?>        
        <tr>
		<td class="etiqueta">Monto Máximo Diario Bs.:</td>
		<td><?php echo number_format($this->data['Afiliacion']['monto_maximo'],2,',','.') ?></td>
	</tr>
        <?php } ?>
        <tr>
		<td class="etiqueta">Contrato:</td>
		<td>
                    <?php
                        if(empty($juridico)){
                    ?>
                        <input type="checkbox" class="contrato_check" id="contrato_check">&nbsp;Aceptar&nbsp;&nbsp;&nbsp;&nbsp;<a href=<?php echo '"'.Router::url( array('controller'=>'pago_activo','action'=>'contrato_pago_activo'), true).'"'; ?>>Descargar</a>
                    <?php
                        }else{
                            if ($this->data['Afiliacion']['tipo_servicio'] == '7'){
                    ?>                    
                                <input type="checkbox" class="contrato_check" id="contrato_check">&nbsp;Aceptar&nbsp;&nbsp;&nbsp;&nbsp;<a href=<?php echo '"'.Router::url( array('controller'=>'pago_activo','action'=>'contrato_pago_activo_c2p'), true).'"'; ?>>Descargar</a>
                    <?php
                            }else{                                
                    ?>
                                <input type="checkbox" class="contrato_check" id="contrato_check">&nbsp;Aceptar&nbsp;&nbsp;&nbsp;&nbsp;<a href=<?php echo '"'.Router::url( array('controller'=>'pago_activo','action'=>'contrato_pago_activo_j'), true).'"'; ?>>Descargar</a>
                    <?php
                            }
                        }
                    ?>
                </td>
	</tr>
        
	<tr>
		<td colspan="2" class="botonera">

		<?php 
		echo $html->link(
			'Volver',
			'/pago_activo/afiliacion_crear/v',
			array(
				'class'=>'button',
				'id'=>'volver'
			)
		);
		
		echo '&nbsp;&nbsp;&nbsp;';			
		
		?>
        <a class="button" id="aceptar_btn" href="#">Aceptar</a>    
		</td>
        </tr>
	</table>                            
</div>
<script type="text/javascript">
    $( document ).ready(function() {
        sig = <?php echo '"'.Router::url( array('controller'=>'pago_activo','action'=>'afiliacion_crear_resultado'), true ).'"'; ?>;

        $("#aceptar_btn").click(function(e){
            if($(this).attr('href') === '#'){
                var aceptados = true;                                                 
                    if(!$("#contrato_check").is(':checked'))
                        aceptados = false;                        
                if(!aceptados){
                    $("#error_contrato").show();
                }else
                {
                    $(this).attr('href', sig);
                    $(this).click();
                }
            }
            else
                return true;
        });
    });
</script>


