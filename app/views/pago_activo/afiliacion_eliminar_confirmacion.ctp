<?php 
    /**
     * Vista para la confirmación de la eliminación de las afiliaciones Pago Activo
     */
?>
<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Resultado Operación</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n para desafiliar el servicio:</td>
	</tr>
	</thead>	
	<tr>
		<td class="etiqueta">Tipo de Afiliaci&oacute;n:</td>
		<td><?php echo $canal[trim($this->data['Afiliacion']['tipo_servicio'])] ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Tipo de Operaci&oacute;n:</td>
		<td>Desafiliación</td>
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
        <?php } if(!empty($this->data['Afiliacion']['numero_contrato'])) { ?>
            <tr>
                <td class="etiqueta">Número de Afiliación:</td>
                <td><?php echo $this->data['Afiliacion']['numero_contrato'] ?></td>
            </tr>
        <?php } 
            if(empty($juridico)){
        ?>         
        <tr>
		<td class="etiqueta">Monto Máximo Diario Bs.:</td>
		<td><?php echo $this->data['Afiliacion']['monto_maximo'] ?></td>
	</tr>
	<?php } ?>
   
  <thead>
  <tr id="nota">
        <td colspan="2" class="resumen_paginacion">                            
            </br><span class="requerido2"><?php echo _m('desafiliacion.c2p'); ?></span>
        </td>
    </tr>
    </thead>  

	<tr>
            <td colspan="2" class="botonera">
            <?php 
                echo $html->link(
                    'Volver',
                    '/pago_activo/listado_pago_activo/v',
                    array(
                            'class'=>'button',
                            'id'=>'volver'
                    )
                );

                echo '&nbsp;&nbsp;';

                echo $html->link(
                    'Aceptar',
                    '/pago_activo/afiliacion_eliminar_resultado',
                    array(
                        'class'=>'button'
                    )
                );
            ?>
            </td>
	</table>
</div>




