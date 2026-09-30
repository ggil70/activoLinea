<?php
/**
 * Confirmación de mesa de cambio compra
**/
?>
<div>
<h2 class="user">Venta de Divisas</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

        <div id="error_contrato" style="display: none"><label class="error"><?php echo _m('aceptar.condiciones.mesa', true) ?></label></div>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos del cambio que desea realizar:</td>
	</tr>
	</thead>
	<tr>
	<td class="etiqueta">Cuenta Origen:</td>
	<td><?php echo enmascara($this->data['MesaCambio']['cuentaOrigen']) ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Cuenta Destino:</td>
	<td><?php echo enmascara($this->data['MesaCambio']['cuentaCreditar']) ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Monto de Orden:</td>
	<td><?php echo $this->data['MesaCambio']['monto_divisa'] ?></td>
	</tr>
    <tr>
    <td class="etiqueta">Comision:</td>
    <td><?php echo $this->data['MesaCambio']['comision'] ?></td>
    </tr>
        <tr>
	<td class="etiqueta">Tasa de Venta:</td>
	<td><?php echo $this->data['MesaCambio']['tasa'] ?></td>
	</tr>
        <tr>
	<td class="etiqueta">Total Bs.:</td>
	<td><?php echo $this->data['MesaCambio']['total'] ?></td>
	</tr>
	<tr>
        <tr>
        <td class="etiqueta">Declaración del Cliente:</td>
        <td>
            <?php
                if(empty($juridico)){
            ?>
                <input type="checkbox" class="contrato_check" id="contrato_check">&nbsp;Aceptar&nbsp;&nbsp;&nbsp;&nbsp;<a href=<?php echo '"'.Router::url( array('controller'=>'mesa_cambio','action'=>'condiciones_mesa_cambio_venta'), true).'"'; ?>>Descargar</a>
            <?php
                }else{
            ?>
                <input type="checkbox" class="contrato_check" id="contrato_check">&nbsp;Aceptar&nbsp;&nbsp;&nbsp;&nbsp;<a href=<?php echo '"'.Router::url( array('controller'=>'mesa_cambio','action'=>'condiciones_mesa_cambio_venta_j'), true).'"'; ?>>Descargar</a>
            <?php
                }
            ?>
        </td>
	</tr>    
	<td colspan="2" class="botonera">

	<?php 
	
	echo $html->link(
            'Volver',
            '/mesa_cambio/venta/v',
            array(
               'class'=>'button bloqueo_repetir'
            )
	);
	
	echo '&nbsp;&nbsp;';		
	?>
        <a class="button bloqueo_repetir" id="aceptar_btn" href="#">Aceptar</a>        
	</td>
        </tr>
	</table>        
</div>
<script type="text/javascript">
    $( document ).ready(function() {
        sig = <?php echo '"'.Router::url( array('controller'=>'mesa_cambio','action'=>'venta_resultado'), true ).'"'; ?>;

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




