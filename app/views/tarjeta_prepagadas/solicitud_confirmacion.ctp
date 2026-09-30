<?php
    /**
     * Vista para la confirmación  afiliaciones
     */

?>


<div>
<h2 class="user">Solicitud de Tarjeta Prepago CASH </h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Seleccionar Tipo de Tarjeta</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Solicitud Informaci&oacute;n Banco</div>


<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>

<div class="paso_nro">4</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">5</div>
<?php } ?>

<div class="paso_desc">Resultado de Operaci&oacute;n</div>


<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0">
	<thead>
	<tr>
	<td colspan="2" style="border:0">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>

	<tr>
	<td colspan="2" class="resumen_paginacion"><h2 class="user">Información Cliente</h2></td>
	</tr>  

	<tr>
		<td class="etiqueta">Cédula:</td>
		<td><?php echo $v_cedula ?></td>
	</tr>


	<tr>
		<td class="etiqueta">Apellido(s) , Nombre(s):</td>
		<td><?php echo $v_nombre ?></td>
	</tr>

	<tr>
		<td class="etiqueta">Télefono Celular: </td>
		<td><?php echo $v_celular ?></td>
	</tr>

	<tr>
	<td colspan="2" class="resumen_paginacion"><h2 class="user">Información Banco</h2></td>
	</tr> 

	<tr>
		<td class="etiqueta">Cuenta Para Debitar Comisión</td>
		<td><?php echo $v_cuentaBS ?></td>
	</tr>

	<tr>
		<td class="etiqueta">Agencia de Retiro de Tarjeta Prepago CASH</td>
		<td><?php echo $v_agencia ?></td>
	</tr>

        <tr>
        <td class="etiqueta">Condiciones del Contrato:</td>
        <td>
          <input type="checkbox" class="contrato_check" id="contrato_check">&nbsp;Aceptar&nbsp;&nbsp;&nbsp;&nbsp;
          <a href=<?php echo '"'.Router::url( array('controller'=>'tarjeta_prepagadas','action'=>'contrato_tcp'), true).'"'; ?>>Descargar</a>
        </td>
	</tr>

	
	<tr>
        <td colspan="2" style="border:0">
        	<div id="error_contrato" style="display: none">
            	<label class="error"><?php echo _m('tcp.sol.contrato', true) ?></label> 
           </div> 	
		</td>
	</tr>	
    </div> 

	<tr>
		<td colspan="2" class="botonera">

		<?php 
		echo $html->link(
			'Volver',
			'/tarjeta_prepagadas/presolicitud_fisica/e',
			array(
				'class'=>'button',
				'id'=>'volver'
			)
		);
		
		echo '&nbsp;&nbsp;&nbsp;';
		
		echo '<a class="button bloqueo_repetir" id="aceptar_btn" href="#">Aceptar</a>';

		?>

    </td>
	</tr>
	<tr>
	<td colspan="2" class="azul" style="border:0">
		<?php echo _m("tcp.mensaje.emision.virtual") . " " .$etiqComisionTP;
		?>
	</td>
	</tr>	
	</table>

</div>



<script type="text/javascript">
    $( document ).ready(function() {
        sig = <?php echo '"'.Router::url( array('controller'=>'tarjeta_prepagadas','action'=>'solicitud_resultado'), true ).'"'; ?>;
        veces=0;

        $("#aceptar_btn").click(function(e){
        	$(this).attr('href').val = "#";
            if($(this).attr('href') === '#'){
                var aceptados = true;                                                 
                if(!$("#contrato_check").is(':checked'))
                        aceptados = false;
                if(!aceptados){
                    $("#error_contrato").show();
                    return false;
                }else
                {
                	if(veces == 0){
                		veces = 1;
                		document.location.href=sig;
                	}
                }
            }
            else
                return true;
        });
    });
</script>
