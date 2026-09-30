<?php
/**
 * Formulario de búsqueda avanzada de movimientos de tarjetas de crédito
**/
?>
<div>
<h2 class="user">Búsqueda Avanzada</h2>
<div class="line_title_orange"></div>
</div>


<div class="contenedor_formulario">


<?php

echo $form->create('BusquedaCuenta',
	array(
		'url'=> array(
			'controller'=>'tarjetas',
			'action'=>'buscar'
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

echo $form->hidden('nro_tarjeta');
echo $form->hidden('tipo');
echo $form->hidden('nro_tarjeta_padre');

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

echo '<tr><td colspan="2">Seleccione la o las opciones para su búsqueda</td></tr>';

echo '<tr><td class="etiqueta"><span class="estiloTexto"/>Tarjeta:</td><td>'.enmascara($this->data['DetalleProducto']['nro_tarjeta']).'</td></tr>';


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
            'value'=>$anios
        )
    );
    ?>
       </td>
    </tr>

<?php


echo '<tr><td colspan="2" style="border:0;"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

echo '<tr><td colspan="2" class="submit">';

echo '<input type="button" class="button" value="Volver" id="volver">&nbsp;&nbsp;';

echo $form->submit(
	'Aceptar',
	array(
		'div'=>false,
		'class'=>'button'
	)
);

echo '</td></tr></table>';

echo '<input type="hidden" name="data[DetalleProducto][tipo]" value="'.$this->data['DetalleProducto']['tipo'].'">';
echo '<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="'.$this->data['DetalleProducto']['nro_tarjeta_padre'].'">';
echo $form->end();

?>

</div>

<form class="formulario_enlace" id="formulario_volver" method="post" action="<?php echo Router::url('/tarjetas/detalle') ?>">
<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta']; ?>" />
<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta_padre']?>" />
<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['DetalleProducto']['tipo']?>" />
</form>

<script type="text/javascript">

$(function(){
	
	$('#BusquedaCuentaPeriodo').change(acomodar);
	$('input[name="data[BusquedaCuenta][tipoBusq]"]').change(acomodar);
	
	$('#volver').click(function(){
		$('#formulario_volver').submit();	
		return false;
	});



	$('#BusquedaCuentaBuscarForm').validate({
            rules:{

                'data[BusquedaCuenta][meses]':{
                    required:true
                },
                'data[BusquedaCuenta][anio]':{
	                required:true,
    	            minlength:4, 
        	        maxlength:4,                    
                },                    
            },
            messages:{

                'data[BusquedaCuenta][meses]':{
                    required:<?php echo _m('seleccion.requerida',array('el mes a consultar'), true) ?>
                },
            	'data[BusquedaCuenta][anio]':{
                	required:<?php echo _m('campo.requerido',array('el Año a consultar'), true) ?>,
            },                    
                
            },
    });

}); 


</script>