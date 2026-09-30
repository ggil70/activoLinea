<?php
/**
 * Confirmación de cambio PIN TPC
**/
?>
<div>
<h2 class="user">Generaci&oacute;n de Clave / Tarjeta Prepago CASH</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Cambiar Clave Tarjeta</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>


<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    
        <div id="error_contrato" style="display: none"><label class="error"><?php echo "Estimado Cliente,debe confirmar el Cambio de PIN de la Tarjeta Prepago"; ?></label></div>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos del cambio de PIN de la Tarjeta Prepago CASH:</td>
	</tr>
	</thead>


    <tr>
    <td class="etiqueta">Tarjeta Prepago CASH:</td>
    <td><?php echo enmascara($tarjeta) ?></td>
    </tr>

    <tr>
    <td class="etiqueta">Fecha Vencimiento:</td>
    <td><?php echo $fechaVencimiento ?></td>
    </tr>

    <tr>
    <td class="etiqueta">C&oacute;digo de Seguridad:</td>
    <td><?php echo $codSeguridad ?></td>
    </tr>

	<tr>
	<td colspan="2" class="botonera">
	<?php 
	
	echo $html->link(
            'Volver',
            '/tarjeta_prepagadas/cambiopin/',
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
        sig = <?php echo '"'.Router::url( array('controller'=>'tarjeta_prepagadas','action'=>'cambiopin_resultado'), true ).'"'; ?>;
        veces = 0;

        $("#aceptar_btn").click(function(e){
            if($(this).attr('href') === '#'){
                var aceptados = true;
                if(veces == 0){
                    veces = 1;
                    document.location.href=sig;
                }                            
            }
            else{
                return true;
            }    
        });
    });

</script>