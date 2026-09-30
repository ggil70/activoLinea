<?php
/**
 * Formulario de TDD moneda extranjera
**/

$checked1 = 'false';
$checked2 = 'false';
if($swTarjetaFaltante == 3){
   $checked1 = 'checked = "checked"';
}else{
   if($swTarjetaFaltante == 1){
       $checked1 = 'checked = "checked"';
   }else{
       $checked2 = 'checked = "checked"';
   }
}



?>
<div>
<h2 class="user">Solicitud de Tarjeta Prepago CASH </h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Seleccionar Tipo de Tarjeta</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Solicitud Informaci&oacute;n Banco</div>

<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">5</div>
<?php } ?>

<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<br><br><br>
<?php 
    echo $form->create('Presolicitud',
            array('url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'Presolicitud'
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
    
    echo '<tr><td colspan="2">A continuaci&oacute;n seleccione el Tipo de Producto que desea solicitar:</td></tr>';
    ?> 


    <tr>
    <td colspan="2">

    <table cellpadding="0" cellspacing="0" border="0" class="tabla">
    <thead>
    <tr>
    <td class="ancholibre texto_centro"><span class="requerido">*</span></td>
    <td>Producto</td>
    </tr>
    </thead>
    <tbody>

        <?php
            if($swTarjetaFaltante == 1 || $swTarjetaFaltante == 3){
        ?>

        <tr>
        <td class="texto_centro ancholibre">
                <input type="radio"
                    name="data[Presolicitud][tipo_tarjeta]"
                    value="f"
                    <?php echo $checked1;?>
                />
        </td>
        <td>
            Tarjeta F&iacute;sica 
        </td>
        </tr>    

        <?php
        }
        if($swTarjetaFaltante == 2 || $swTarjetaFaltante == 3){
        ?>    
        <tr>
        <td class="texto_centro ancholibre">
                <input type="radio"
                    name="data[Presolicitud][tipo_tarjeta]"
                    value="d"
                    <?php echo $checked2;?>
                />
        </td>
        <td>
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
        'Solicitar',
        array(
            'class'=>'button'
        )
    );     

    echo '</td></tr>';

    echo '</table>';
    echo $form->end();



?>

 


