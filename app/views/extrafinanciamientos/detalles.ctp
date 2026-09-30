<div>
    <h2 class="user">Consulta Extrafinanciamientos</h2>
    <div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">

    <?php if(isset($error)) { ?>
        <label class="error"><? echo $error; ?></label>
    <? }else{?>

    <!-- Tabla de consolidado de los extracreditos -->
    <table class="tabla">
        <thead>
        <tr>
            <td>Tipo de Producto</td>
            <td>Número</td>
            <td>N° Crédito</td>
            <td>Monto Solicitado</td>
            <td>Monto comisión Flat</td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="texto_centro"><? echo 'Tarjeta de Crédito '.$detalleTdc['marca']; ?></td>
            <td class="texto_centro"><? echo $detalleTdc['msk']; ?></td>
            <td class="texto_centro"><? echo $detalleCredito['numeroCredito']; ?></td>
            <td class="monto"><? echo number_format($detalleCredito['montoTotalCredito'],2,',','.'); ?></td>
            <td class="monto"><? echo number_format($detalleCredito['montoComision'],2,',','.'); ?></td>
        </tr>
        </tbody>
    </table>
    <table class="tabla">
        <thead>
        <tr>
            <td>Plazo</td>
            <td>Tasa Actual</td>
            <td>Saldo Pendiente</td>
            <td>Cuotas Pendientes</td>
            <td>Saldo Total a Pagar</td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="texto_centro"><? echo $detalleCredito['numeroCuotas'].' meses'; ?></td>
            <td class="texto_centro"><? echo number_format($tasaActual,2,',','.').'%'; ?></td>
            <td class="monto"><? echo number_format($detalleCredito['saldoPendiente'],2,',','.'); ?></td>
            <td class="texto_centro"><? echo $detalleCredito['cuotasPendientes']; ?></td>
            <td class="monto"><? echo number_format($totalPagar,2,',','.'); ?></td>
        </tr>
        </tbody>
    </table>
    <div class="pull-right">
        *Saldo total a pagar a la fecha de la consulta.
    </div>
    <!-- Fin tabla de consolidado de los extracreditos -->
    <div class="espacio_vertical_20"></div>

    <? if($totalMovimientos > 0) {?>
    <!-- Tabla de listado  de los extracreditos activos-->

    <div class="pull-right">
        <? echo sprintf("%d registros encontrados. Mostrando 1 al %d", $totalMovimientos, $totalMovimientos); ?>
    </div>
    <table class="tabla">
        <thead>
        <tr>
            <td>Fecha</td>
            <td>Concepto</td>
            <td>Monto</td>
            <td>Capital</td>
            <td>Interes</td>
            <td>Tasa</td>
            <td>Saldo Pendiente</td>
        </tr>
        </thead>
        <tbody>
        <? foreach($movimientos as $m) {?>
            <tr>
                <td class="texto_centro"><? echo substr($m['fechaMovimiento'],6).'/'.substr($m['fechaMovimiento'],4 , 2).'/'.substr($m['fechaMovimiento'],0,4); ?></td>
                <td class="texto_centro"><? echo $m['concepto']; ?></td>
                <td class="monto"><? echo number_format($m['monto'],2,',','.'); ?></td>
                <td class="monto"><? echo number_format(($m['monto'] - $m['interesesCancelados']),2,',','.'); ?></td>
                <td class="monto"><? echo number_format($m['interesesCancelados'],2,',','.'); ?></td>
                <td class="texto_centro"><? echo number_format($m['tasaMes'],2,',','.')."%"; ?></td>
                <td class="monto"><? echo number_format($m['nuevoSaldo'],2,',','.'); ?></td>
            </tr>
        <? } ?>
        </tbody>
    </table>
    <div  class="botonera">
        <a href="<? echo Router::url( array('controller'=>$this->params['controller'],'action'=>'index'), true ) ?>" class="button">Volver</a>
    </div>
    <? } ?><!-- Fin tabla de listado  de los extracreditos activos-->

    <? } ?><!-- Fin formulario sin errores -->
</div>
