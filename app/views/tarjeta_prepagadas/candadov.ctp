<?php
/**
 * Formulario de candado tarjeta Prepago
**/

$nroTarjeta = enmascara($nroTarjeta);

?>
<div>
<h2 class="user">Tarjeta Prepago CASH / Candado Virtual</h2>
<div class="line_title_orange"></div>
</div>


<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>

<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <br><br><br>
	<?php

	echo $form->create('Candadov',
            array(
		'url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'mostrar_candadov'
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
                    'align'=>'center'
		)
	);
    ?>

   <?php 
    $input_tarjeta = array(
            'label'=>'<span class="requerido">* </span>N&uacute;mero de Tarjeta:',
            'type'=>'select',
            'id'=>'tarjeta',
            'empty'=>'Seleccione',
            'options'=>$tarjetas,
    );
    
    echo $form->input(
            'tarjeta',
            $input_tarjeta
    );

    if ($form->isFieldError('tarjeta')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tarjeta',
        array(
        )
            ).'</td></tr>'; 
    }


    echo '<tr><td>&nbsp;</td><td class="submit">';

    echo $form->submit(
        'Aceptar',
        array(
            'class'=>'button'
        )
    );     


    echo '</td></tr></table>';
    echo $form->end();


?>

</div>


<script type="text/javascript">
    $(function(){

        $('#CandadovCandadovForm').validate({
                rules:{

                    'data[Candadov][tarjeta]':{
                        required:true
                    },

                },
                messages:{

                    'data[Candadov][tarjeta]':{
                        required:<?php echo _m('tcp.seleccionar.tarjeta',array('el mes a consultar'), true) ?>
                    },

                },                    
                    
        });

    }); 





</script>

