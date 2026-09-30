<?php
/**
 * Formulario de mesa de cambio comprass
**/
?>
<div>
<h2 class="user">Candado Virtual</h2>
<div class="line_title_orange"></div>
</div>


<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>
<div class="paso_nro_actual">4</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>


<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <br><br><br>

    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
    <thead>
        <tr>
        <td colspan="2" class="resumen_paginacion"><p class="exito">
        Operaci&oacute;n realizada satisfactoriamente</p>
        </td>
        </tr>
    </thead>
    <tr>
        <td class="etiqueta">N&uacute;mero de Tarjeta:</td>
        <td><?php echo enmascara($nroTarjeta);?></td>
    </tr>
    <tr>
        <td class="etiqueta">Estatus Actual del Candado:</td>
            <td><?php echo $desEdoNew?></td>    
     </tr>

    <tr>
        <td class="etiqueta">Fecha Operaci&oacuten:</td>
        <td><?php echo date('d/m/Y') ?></td>
    </tr>

        <tr>
        <td colspan="2" class="botonera">

        <?php
        echo $html->link(
                    'Imprimir',
                    'javascript:print();void(0);',
                    array(
                        'class'=>'button'
                    )
        );

        ?>
        </td>
        </tr>


    </table>
</div>

