<div>
    <h2 class="user">Consulta Extrafinanciamientos</h2>
    <div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">

    <?php if(isset($error)) { ?>
        <label class="error"><? echo $error; ?></label>
    <? }else{

    // Generamos el formulario
    $limiteExtra = (doubleval($tdcDetalle['limiteCredito']) * doubleval($params['avance.extra.porc.max']) / 100);
    $totalDetalles = count($consolidado['detalles']);
    // Datos del formulario
    $formulario['inicio'] = $form->create('ConsultaExtrafininanciamiento',
        array(
            'url'=> array(
                'controller'=>'extrafinanciamientos',
                'action'=>'index'
            ),
            'inputDefaults'=>array(
                'div'=>false,
                'error'=>array(
                    'class'=>'error'
                )
            )
        )
    );

    // Tarjetas para la consulta
    $formulario['tarjetas'] = $form->input(
        'tdc', array(
            'label'=>false,
            'required'=>'true',
            'type'=>'select',
            'options'=> $tarjetas
        )
    );

    // --------------------- Fin de creaci?n del formulario ------------------------
    echo $formulario['inicio'];

    ?>
    <!-- El formulario no contiene errores -->
    <table class="formulario">
        <tr>
            <td class="etiqueta">
                <label>
                    <span class="requerido">* </span>
                    Número Tarjeta:
                </label>
            </td>
            <td class="campo">
                <?php echo $formulario['tarjetas']; ?>
            </td>
        </tr>
    </table>
    <?php echo $form->end(); ?>
    <!-- Tabla de consolidado de los extracreditos -->
    <table class="tabla">
        <thead>
        <tr>
            <td>Tipo de Producto</td>
            <td>Límite Extrafinanciamiento</td>
            <td>Saldo Actual Bs.</td>
            <td>Crédito Disponible Bs.</td>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td class="texto_centro"><? echo 'Tarjeta de Crédito '.$tdcDetalle['marca']; ?></td>
                <td class="monto"><? echo number_format($limiteExtra, 2, ',', '.'); ?></td>
                <td class="monto"><? echo number_format($consolidado['saldo_actual'], 2, ',', '.'); ?></td>
                <td class="monto"><? echo number_format(($limiteExtra - doubleval($consolidado['saldo_actual'])), 2, ',', '.'); ?></td>
            </tr>
        </tbody>
    </table>
    <!-- Fin tabla de consolidado de los extracreditos -->
    <div class="espacio_vertical_20"></div>

    <? if($totalDetalles > 0) {?>
    <!-- Tabla de listado  de los extracreditos activos-->

    <div class="pull-right">
        <? echo sprintf("%d registros encontrados. Mostrando 1 al %d", $totalDetalles, $totalDetalles); ?>
    </div>
    <table class="tabla">
        <thead>
        <tr>
            <td>Fecha</td>
            <td>N° de Crédito</td>
            <td>Tipo de Extrafinanciamiento</td>
            <td>Monto Solicitado</td>
            <td>Plazo</td>
        </tr>
        </thead>
        <tbody>
        <? foreach($consolidado['detalles'] as $d) {?>
        <tr>
            <td class="texto_centro"><? echo substr($d['fechaAutorizacion'],6).'/'.substr($d['fechaAutorizacion'],4 , 2).'/'.substr($d['fechaAutorizacion'],0,4); ?></td>
            <td class="texto_centro"><a href="<? echo Router::url( array('controller'=>$this->params['controller'],'action'=>'detalles'), true ).'/'.$d['numeroCredito']; ?>" id="<? echo $d['numeroCredito']; ?> " class="extra_num"><? echo $d['numeroCredito']; ?></a></td>
            <td class="texto_centro"><? echo $d['tipo']; ?></td>
            <td class="monto"><? echo number_format($d['montoTotalCredito'],2,',','.'); ?></td>
            <td class="texto_centro"><? echo $d['numeroCuotas'].' meses'; ?></td>
        </tr>
        <? } ?>
        </tbody>
    </table>
    <? } ?><!-- Fin tabla de listado  de los extracreditos activos-->

    <? } ?><!-- Fin formulario sin errores -->
</div>
<script>
    $(document).ready(function(){
        $("#ConsultaExtrafininanciamientoTdc").change(function(){
            $("#ConsultaExtrafininanciamientoIndexForm").submit();
        })
    });
</script>
