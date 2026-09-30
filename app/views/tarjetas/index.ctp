<?php

/**
 * Listado de las tarjetas de crédito
 **/
?>
<div>
    <h2 class="user">Tarjetas de Cr&eacute;ditos</h2>
    <div class="line_title_orange"></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
    <thead>
        <tr>
            <!-- TABLA PARA TARJETAS DE CREDITO -->
            <td>Alias</td>
            <td>N&uacute;mero</td>
            <td>Saldo Actual Bs.</td>
            <td>Saldo Actual UVC</td>
            <td>Cr&eacute;dito Disponible Bs.</td>
            <td>Pago M&iacute;nimo Bs.</td>
            <td>Fecha L&iacute;mite Pago</td>
        </tr>
    </thead>


    <?php 

        if (count($tarjetas) > 0) { ?>
         <tbody>
            <?php 
            foreach ($tarjetas as $t) {
            ?>
                <tr>
                <td class="texto_centro"><?php echo $t['ALIAS']; ?></td>
                <td class="texto_centro">
                    <?php if (trim($t['SUMTYP']) == 'TCC') { ?>
                            <form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/detalle_emp_corp') ?>">
                    <?php } else { ?>
                            <form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/detalle') ?>">
                            <?php } 
                            ?>
                                <input type="hidden" name="data[DetalleProducto][nro_tarjeta]" 
                                           value="<?php echo trim($t['nroTarjeta']) ?>" />
                                    <input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" 
                                           value="<?php echo trim($t['nroTarjeta']) ?>" />
                                    <input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo trim($t['SUMTYP']) ?>" />
                                    <input type="submit" class="boton_enlace" value="<?php echo enmascara($t['nroTarjeta']) ?>" />
                                </form>
                    </td>
                    
                            <td class="monto"><?php echo formatea_monto_fromWs($t['saldo_actual']); ?></td>
                            <td class="monto"><?php echo formatea_monto_fromWs($t['saldo_actual_uvc']); ?></td>
                            <td class="monto"><?php echo formatea_monto_fromWs($t['creditoBS']); ?></td>
                            <td class="monto"><?php echo formatea_monto_fromWs($t['pago_minimo']); ?></td>
                            <td class="texto_centro"><?php echo formatea_fecha($t['dia_pago'], $t['mes_pago'], $t['anio_pago']); ?></td>
                        </tr>
    <?php } ?>

    </tbody>
    <?php } ?>
    <tfoot>
        <tr>
            <td class="total" colspan="2">Total Bs.:</td>
            <td class="monto_total"><?php echo formatea_monto_fromWs($totales['saldo_actual']) ?> </td>
            <td class="monto_total"><?php echo formatea_monto_fromWs($totales['saldo_actualUVC']) ?> </td>
            <td class="monto_total"><?php echo formatea_monto_fromWs($totales['credito_disponible']) ?> </td>
            <td class="monto_total"><?php echo formatea_monto_fromWs($totales['pago_minimo']) ?> </td>
    </tfoot>
</table>