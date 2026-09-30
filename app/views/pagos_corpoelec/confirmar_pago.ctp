<?php
/**
 * Resultado Activacion Tarjeta Prepagada
**/
?>
<div>
<h2 class="user">Pagos Corpoelec</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Información</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Solicitud de Factura</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Confirmación</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">5</div>
<div class="paso_desc">Resultado Operación</div>

<div class="espacio_vertical_20"></div>
<div class="contenedor_formulario">
<?php 
    echo $form->create('Confirmar',
        array(
            'url'=> '/pagos_corpoelec/confirmar_pago',
            'id'=>'ConfirmarCrearForm',
            'inputDefaults'=>array(
                'div'=>false,
                'label'=>'etiqueta',
                'before'=>'',
                'between'=>'',
                'after'=>'',
                'error'=>false
            )
        )
    );
        
        echo $form->hidden('conf',array(
            'value'=>'1'
        ));

    function FormatoMoneda($numero){
        $decimales = substr($numero, -2);
        $entero = substr($numero, 0, -2);
        $numeroFormateado = number_format($entero, 0, '', '.');
        $resultadoFinal = $numeroFormateado . '.' . $decimales;
        return $resultadoFinal;
    }
?>

    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
        
    <thead>
        <tr>
            <td colspan="2" class="resumen_paginacion">Confirme los datos de la operación que desea realizar:</td>
        </tr>
    </thead>
        <tr>
            <td class="etiqueta">Cuenta a Debitar:</td>
            <td><?php echo enmascara($datos['cuentaDebitar']); ?></td>
        </tr>
    <?php if($datos['servicio'] == "1"): ?>
        <tr>
            <td class="etiqueta">Cédula del beneficiario:</td>
            <td><?php echo $datos['tipoCedula'] . "-" . $datos['nroCedula']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Nombre del beneficiario:</td>
            <td><?php echo $datos['beneficiario']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Número de contrato:</td>
            <td><?php echo $datos['contrato']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Grupo:</td>
            <td><?php echo $datos['grupo']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Fecha de emisión: </td>
            <td><?php echo $datos['fecha']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Identificación de Factura: </td>
            <td><?php echo enmascara($datos['id']); ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Monto a Pagar: </td>
            <td><?php echo $datos['debts']; ?></td>
        </tr>
    <?php elseif($datos['servicio'] == "2"): ?>
        <tr>
            <td class="etiqueta">Número de contrato:</td>
            <td><?php echo $datos['nroContrato']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Fecha de emisión: </td>
            <td><?php echo $datos['fecha']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Monto a Pagar: </td>
            <td><?php echo FormatoMoneda($datos['debts']); ?></td>
        </tr>
    <?php endif; ?>
    </table>
    <br>
    <table style="margin: 0 auto; width: 550px;">
        <tr>
        <tr>
            <td>
                <div id="messageBox"></div>
            </td>
        </tr>
        <tr>
        <td class="botonera">

        <?php 
        echo $html->link(
            'Volver',
            '/pagos_corpoelec/pago',
            array(
                'class'=>'button',
                'id'=>'volver'
            )
        );
        
        echo '&nbsp;&nbsp;&nbsp;';
                
        ?>
        <a class="button bloqueo_repetir" id="aceptar_btn" href="#">Aceptar</a>
        </td>
        </tr>
    </table>
    
    <?php 
        echo $form->end(); 
    ?>
</div>
<script type="text/javascript">

    $( document ).ready(function() {
        sig = <?php echo '"'.Router::url( array('controller'=>'pagos_corpoelec','action'=>'resultado_pago'), true ).'"'; ?>;

        $("#aceptar_btn").click(function(e){
            if($(this).attr('href') === '#'){
                var aceptados = true;                           
                location.href=sig;
            }
            else{
                return true;
            }    
        });
    });  
    
</script>