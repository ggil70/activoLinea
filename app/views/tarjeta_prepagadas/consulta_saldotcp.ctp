<?php
    /**
     * Vista consulta Saldo TPC
     */
?>


<div>
<h2 class="user">Tarjeta Prepago CASH / Consulta de Saldo</h2>
<div class="line_title_orange"></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>N&uacute;mero</td>
<td>Estatus</td>
<td>Saldo Total $</td>
<td>Saldo Diferido $.</td>
<td>Saldo Disponible $</td>
</tr>
</thead>
<tbody>
<tr>
<td class="texto_centro"><?php echo $nroTarjeta; ?></td>
<td class="texto_centro"><?php echo $statusCuenta; ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($saldoCuenta); ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($saldoDiferido); ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($saldoDisponible); ?></td>


</tr>
<br>
<tr>
    <td colspan="6" class="botonera" style="align:right">
        <?php 
        echo $html->link(
                'Volver',
                '/tarjeta_prepagadas/seleccionar_consulta_saldo/',
                array(
                   'class'=>'button bloqueo_repetir'
                )
        );

        echo "&nbsp;&nbsp;";

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
</tbody>
</table>


