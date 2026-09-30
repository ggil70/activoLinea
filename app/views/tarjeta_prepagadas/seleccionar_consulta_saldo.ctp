<?php
/**
 * Formulario de TDD moneda extranjera
**/

$checked1 = 'false';
$checked2 = 'false';
if($swFisico == 1){
   $checked1 = 'checked = "checked"';
}else{
   $checked2 = 'checked = "checked"';
}


?>
<div>
<h2 class="user">Consulta de Tarjeta Prepago CASH</h2>
<div class="line_title_orange"></div>
</div>


<br><br>
<?php 
    echo $form->create('SeleccionarConsultaSaldo',
            array('url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'seleccionar_consulta_saldo'
                    ),
                  'inputDefaults'=>array(
                    'div'=>false,
                    'label'=>'etiqueta',
                    'before'=>'<tr><td class="etiqueta">',
                    'between'=>'</td><td class="campo">',
                    'after'=>'</td></tr>',
                    'error'=>false
                  )
            )
    );
        
    echo $html->tag(
        'table',
        null,
        array(
                    'class'=>'formulario',
                    'cellpadding'=>'0',
                    'cellspacing'=>'0',
                    'border'=>'0',
                    'align'=>'center',
                    'style'=>'table-layout:fixed'
        )
    );

    // La primera fila define la tabla con layout fixed
    echo '<tr><td style="width:150px"></td><td style="width:400px"></td></tr>';
    
    echo '<tr><td colspan="2">A continuaci&oacute;n seleccione la Tarjeta que desea consultar:</td></tr>';
    ?> 


    <tr>
    <td colspan="2">

    <table cellpadding="0" cellspacing="0" border="0" class="tabla">
    <thead>
    <tr>
    <td class="ancholibre texto_centro"><span class="requerido">*</span></td>
    <td>N&uacute;mero Tarjeta</td>
    <td>Tipo Producto</td>
    </tr>
    </thead>
    <tbody>
        <?php
            if($swFisico==1){
        ?>

        <tr>
        <td class="texto_centro ancholibre">
                <input type="radio"
                    name="data[SeleccionarConsultaSaldo][tipo_tarjeta]"
                    value="f"
                    <?php echo $checked1;?>
                />
        </td>
        <td>
            <?php echo $desTarjetaF;?>
        </td>

        <td class="texto_centro ancholibre">
            Tarjeta F&iacute;sica 
        </td>
        </tr>    

        <?php
        }
        if($swVirtual == 1){
        ?>    
        <tr>
        <td class="texto_centro ancholibre">
                <input type="radio"
                    name="data[SeleccionarConsultaSaldo][tipo_tarjeta]"
                    value="v"
                    <?php echo $checked2;?>
                />
        </td>
        <td>
            <?php echo $desTarjetaV;?>
        </td>        
        <td class="texto_centro ancholibre">
            Tarjeta Virtual
        </td>
        </tr> 
        <?php
        }
        ?>
    </tbody>
    </table>    
    </td>
    </tr>


<?php
    echo '<tr><td>&nbsp;</td><td class="submit">';

    echo $form->submit(
        'Consultar',
        array(
            'class'=>'button'
        )
    );     

    echo '</td></tr>';

    echo '</table>';
    echo $form->end();



?>

 


