<?php
/**
 * Formulario de Candado Virtual TPC
**/
?>
<div>
<h2 class="user">Candado Virtual</h2>
<div class="line_title_orange"></div>
</div>


<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <br><br><br>

    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
    <thead>
    <tr>
    <td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
    </tr>
    </thead>
    <tr>
        <td class="etiqueta">N&uacute;mero de Tarjeta:</td>
        <td><?php echo enmascara($nroTarjeta)?></td>
    </tr>

    <tr>
        <td class="etiqueta">Estatus Nuevo del Candado:</td>
            <td><?php echo $desEdoNew?></td>    
     </tr>


    <tr>
        <td colspan="2" class="botonera">

        <?php 
        echo $html->link(
            'Volver',
            '/tarjeta_prepagadas/candadov',
            array(
                'class'=>'button',
                'id'=>'volver'
            )
        );
        
        echo '&nbsp;&nbsp;&nbsp;';
        
        echo $html->link(
            'Aceptar',
            '/tarjeta_prepagadas/candado_resultado',
            array(
                'class'=>'button bloqueo_repetir'
            )
        ); 
        

        ?>

        </td>
    </tr>

    </table>
</div>

