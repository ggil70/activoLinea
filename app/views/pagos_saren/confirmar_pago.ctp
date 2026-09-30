<?php
    /**
     * Vista para la confirmaciÃƒÂ³n afiliaciones pago activo
     */
?>



<div>
<h2 class="user">Pagos Saren</h2>
<div class="line_title_orange"></div>
</div>

<?php if ($error){ ?>
    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
    <thead>
        <tr>
            <td colspan="2" class="error-message"><b><?php echo $error; ?></b></td>
        </tr>
    </thead>
    </table>
        <!-- <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
            <tbody>
                <div class="error-message"></div>
            </tbody>
        </table>   -->  
<?php }else{ ?>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>
<div class="contenedor_formulario">
<?php 
    echo $form->create('Confirmar',
        array(
            'url'=> '/pago_saren/confirmar_pago',
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
?>

    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
        
    <thead>
        <tr>
            <td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
        </tr>
    </thead>
    
    <tr>
        <td class="etiqueta">Cuenta a D&eacute;bitar:</td>
        <td><?php echo enmascara($datos['cuentaDebitar']); ?></td>
    </tr>
    <tr>
        <td class="etiqueta">N&uacute;mero de Planilla:</td>
        <td><?php echo $datos['nro_planilla']; ?></td>
    </tr>
    <tr>
        <td class="etiqueta">Monto a Pagar:</td>
        <td><?php echo $datos['monto']; ?></td>
    </tr>
    <tr>
        <td class="etiqueta">Tipo de Tr&aacute;mite:</td>
        <td><?php echo $datos['acto']; ?></td>
    </tr>
    <tr>
        <td class="etiqueta">Oficina:</td>
        <td><?php echo $datos['oficina']; ?></td>
    </tr>
    <tr>
        <td class="etiqueta">Fecha de emisi&oacute;n: </td>
        <td><?php echo $fechaFormateada;?></td>
    </tr>
    
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
            '/pagos_saren/pago',
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
    <?php } ?>    
    <?php 
        echo $form->end(); 
    ?>
</div>
<script type="text/javascript">

    $( document ).ready(function() {
        sig = <?php echo '"'.Router::url( array('controller'=>'pagos_saren','action'=>'resultado_pago'), true ).'"'; ?>;

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