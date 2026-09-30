<?php
/**
 * Formulario de mesa de cambio comprass
**/

?>
<div>
<h2 class="user">>Tarjeta Prepago CASH / Candado Virtual</h2>
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

	echo $form->create('candadov',
            array(
		'url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'candadov'
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
    echo $form->input(
        'nroTarjeta',
        array(
            'label'=>'<span class="requerido">* </span>N&uacute;mero de Tarjeta:',
            'type'=>'text',
            'value'=>$nroTarjeta,
            'readonly'=>'readonly',
            'id'=>'nroTarjeta',
            )
    );  
    ?>  


    <tr>
     <td class="campo">
    
    <?php    
    echo $form->input(
        'desc_candado',
        array(
            'label'=>'<span class="requerido">* </span>Estado Candado:',
            'type'=>'text',
            'value'=>$desEstatusCandado,
            'readonly'=>'readonly',
            'id'=>'desc_candado',
            )
    );  
    ?>
       </td>
    </tr>

<?php
   	echo '<tr><td>&nbsp;</td><td class="submit">';
  
    if($estatusCandado == 0){
        echo $form->submit(
            'Abrir Candado',
            array(
               'class'=>'button'
            )
      	);
    }else{
        echo $form->submit(
            'Cerrar Candado',
            array(
               'class'=>'button'
            )
        );
    }   		
    echo '</td></tr></table>';
	echo $form->end();
?>
</div>

<script>
    
    $(function(){
         
	});

</script>