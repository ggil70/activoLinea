<?php
/**
 * Confirmación de mesa de cambio compra
**/
?>
<div>
<h2 class="user">Tarjeta Prepago Cash / Recarga Tercero en Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitar Informaci&oacute;n Beneficiario</div>

<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>


<div class="paso_nro">3</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>

<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>


<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la recarga de tarjeta que desea realizar:</td>
	</tr>
	</thead>


    <tr>
    <td class="etiqueta">Cuenta de D&eacute;bito:</td>
    <td><?php echo enmascara($debito) ?></td>
    </tr>

    <tr>
    <td class="etiqueta">Tarjeta del Beneficiario:</td>
    <td><?php echo enmascara($tarjeta) ?></td>
    </tr>    


    <tr>
    <td class="etiqueta">Nombre del Beneficiario:</td>
    <td><?php echo $nombreB ?></td>
    </tr>


    <tr>
    <td class="etiqueta">C&eacute;dula del Beneficiario:</td>
    <td><?php echo $cedulaClienteB ?></td>
    </tr>


    <tr>
    <td class="etiqueta">Email del Beneficiario:</td>
    <td><?php echo $email ?></td>
    </tr>



    <tr>
    <td class="etiqueta">Monto Recarga:</td>
    <td><?php echo $montoRecarga ?></td>
    </tr>

    <tr>
    <td class="etiqueta">Comisión:</td>
    <td><?php echo $comision ?></td>
    </tr>


    <tr>
    <td class="etiqueta">Total a Debitar:</td>
    <td><?php echo $montoTotal ?></td>
    </tr>


	<tr>
	<td colspan="2" class="botonera">
	<?php 
	
	echo $html->link(
            'Volver',
            '/tarjeta_prepagadas/recargatercero/',
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

<table border = "0">
<?php

echo '<tr><td colspan="2"><span class="azul">'. _m('tcp.mensaje.comision.recarga'). " " . $etiqComision . ' % del monto de la transacción</span></td></tr>';
?>

</table> 
</div>

<script type="text/javascript">
    $( document ).ready(function() {
        sig = <?php echo '"'.Router::url( array('controller'=>'tarjeta_prepagadas','action'=>'recargatertpc_resultado'), true ).'"'; ?>;
        veces = 0;

        $("#aceptar_btn").click(function(e){
            if($(this).attr('href') === '#'){
                var aceptados = true;                         
                if(veces == 0){
                    veces = 1;
                    location.href=sig;
                }    
            }
            else
                return true;
        });

    });
</script>




