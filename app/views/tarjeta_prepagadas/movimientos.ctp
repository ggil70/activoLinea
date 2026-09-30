<?php
    /**
     * Vista para mostrar el consolidado de productos
     */

$mes = date("m");;
$anio = date("Y");


?>


<div>
<h2 class="user">Tarjeta Prepago CASH / Movimientos</h2>
<div class="line_title_orange"></div>
</div>



<?php




    echo $form->create('Movimientos',
            array('url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'mostrar_movimiento',
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

    $input_tarjeta = array(
            'label'=>'<span class="requerido">* </span>Nro. Tarjeta Prepago:',
            'type'=>'select',
            'id'=>'tarjeta',
            'empty'=>'Seleccione',
            'options'=>$tarjetas,
            'value'=>''
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



    $input_meses = array(
            'label'=>'<span class="requerido">* </span>Mes:',
            'type'=>'select',
            'id'=>'mes',
            'empty'=>'Seleccione',
            'options'=>$arrayMeses,
            'value'=>$mes
    );
    
    echo $form->input(
            'meses',
            $input_meses
    );

    if ($form->isFieldError('$input_meses')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('$input_meses',
        array(
        )
            ).'</td></tr>'; 
    }
    
    ?>
    
    <tr>
       <td class="etiqueta"><span class="requerido">* </span>A&ntilde;o:</td>
     <td class="campo">
    
    <?php    echo $form->input(
        'anio',
        array(
            'before'=>false,
            'between'=>false,
            'label'=>false,
            'type'=>'text',
            'id'=>'anio',
            'class'=>'numerico',
            'maxlength'=>4,
            'value'=>$anio
        )
    );
    ?>
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


    echo '</td></tr></table>';
    echo $form->end();

?>


<script type="text/javascript">
    $(function(){
        $('#MovimientosMovimientosForm').validate({
                rules:{

                    'data[Movimientos][tarjeta]':{
                        required:true
                    },

                    'data[Movimientos][meses]':{
                        required:true
                    },
	                'data[Movimientos][anio]':{
    	                required:true,
        	            minlength:4, 
            	        maxlength:4,                    
	                },                    
                },
                messages:{

                    'data[Movimientos][tarjeta]':{
                        required:<?php echo _m('tcp.seleccionar.tarjeta',array('el mes a consultar'), true) ?>
                    },

                    'data[Movimientos][meses]':{
                        required:<?php echo _m('seleccion.requerida',array('el mes a consultar'), true) ?>
                    },
                	'data[Movimientos][anio]':{
                    	required:<?php echo _m('campo.requerido',array('el Año a consultar'), true) ?>,
                },                    
                    
                },
        });

    }); 





</script>

