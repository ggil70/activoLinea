<?php
/**
 * Formulario de pagos a cuentas afiliadas
**/
?>
<div>
<h2 class="user">Pago Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<?php

	echo $form->create('PagoActivo',
            array(
                    'url'=> '/pago_activo/pagos_afiliada',
                    'id'=>'PagoActivoPagoForm',
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
        
        echo $form->hidden('tipoPersona');
	echo $form->hidden('tipoDestinatario');
	echo $form->hidden('cuentaDebitar');
	echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$limite_pago_activo));		
	
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
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos del pago.</td></tr>';

	echo '
	<tr>
	<td class="etiqueta">Cuenta de Débito:</td>
	<td> ' . enmascara($this->data['PagoActivo']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2">Seleccione el beneficiario del pago y complete los datos de la operación:</td></tr>';

	echo '<tr><td colspan="2">'
		.'<label for="data[PagoActivo][cuentaCreditar]" class="error manual">'._m('seleccion.requerida',array('el beneficiario')).'</label>';
		if ($form->isFieldError('cuentaCreditar')){
                    echo $form->error('cuentaCreditar',
                        array(                            
                            'requerido'=>_m('campo.requerido',array('el beneficiario'))                            
                        )			
                    );
		}
	echo '</td></tr>';
	?>

	<tr>
	<td colspan="2">

	<input type="hidden" name="data[PagoActivo][cuentaCreditar]" value="">

	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tabla">
	<thead>
	<tr>
	<td class="ancholibre texto_centro"><span class="requerido">* </span></td>	
	<td>Banco Beneficiario</td>
	<td>Nombre Beneficiario</td>
	<td>Número Celular</td>
	</tr>
	</thead>
	<tbody>
	
	<?php 
	if(count($afiliados)>0){
            foreach($resultados as $i){                
	?>
	<td class="texto_centro ancholibre">
	<input
		type="radio"
		class="seleccion_afiliado"
		id="<?php echo 'sel'.$i['codbanco'].$i['telefono'] ?>"
		name="data[PagoActivo][cuentaCreditar]"
		value="<?php echo $i['codbanco'].$i['telefono'].$i['nacionalidad'].$i['cedula'] ?>"
		<?php if(
                            isset($this->data['PagoActivo']['cuentaCreditar'])
                            && 0==strcmp($this->data['PagoActivo']['cuentaCreditar'],$i['telefono'])
			){
			echo 'checked="checked"';
		}
		?>                
	/>		
	</td>
	<td><?php echo $i['nombrebanco'] ?></td>
	<td><?php echo $i['nombre']?></td>
	<td><?php echo  "0".trim(substr($i['telefono'], 2, 3)).trim(substr($i['telefono'], 5)) ?></td>	
	</tr>
	<?php                
            }
	?>
            <tr>
            <td colspan="5" class="paginador">
                <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros,'tamano_paginacion'=>5)); ?>
            </td>
            </tr>
	<?php
	}
	else{
	?>
	<tr>
            <td colspan="5" class="texto_centro">No se encontraron resultados...</td>
	</tr>
	<?php
	}
	?>
	</tbody>
	</table>

	</td>
	</tr>

	<?php

	echo '<tr><td colspan="2">Indique la información que a continuaci&oacute;n se solicita:</td></tr>';		      
        
	echo $form->input(
            'monto',
            array(
                'label'=>'<span class="requerido">* </span>Monto a pagar Bs.:',
                'type'=>'text',
                'class'=>'monto pago'
            )
	);

	if ($form->isFieldError('monto')){
            echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
                    array(
                        'numerico'=>_m('formato.invalido',array('el monto')),
                        'requerido'=>_m('campo.requerido',array('el monto')),
                        'montoMaximo'=> _m('monto.maximo', 'del pago'),
                        'montoLimiteDiario'=>_m('monto.excedido', 'del pago')                            
                    )			
            ).'</td></tr>';
	}

	echo $form->input(
            'descripcion',
            array(
                    'label'=>'<span class="requerido">* </span>Descripci&oacute;n:',
                    'type'=>'text',
                    'maxlength' => '30',
                    'class'=>'alfanumericoEspacio'
            )
	);

	if ($form->isFieldError('descripcion')){
            echo '<tr><td>&nbsp;</td><td>'.$form->error('descripcion',
                array(
                        'requerido'=>_m('campo.requerido',array('la descripción')),
                        'alfanumerico'=>_m('alfanumerico.invalido',array('la descripción'))
                )).'</td></tr>';
	}
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
        
        echo '<tr><td colspan="2"></td></tr>';               
        
	echo '<tr><td>&nbsp;</td><td class="submit">';

	echo $html->link(
            'Volver',
            'pagos/v',
            array(
                'class'=>'button'
            )
	);
	         
	if(count($afiliados)>0){            
	
            echo '&nbsp;&nbsp;';

            echo $html->link(
                'Aceptar',
                'javascript:',
                array(
                    'class'=>'button envio_formulario',
                    'id'=>'pagar'
                )
            );
        
        }
	echo '</td></tr></table>';                
        
	echo $form->end();

	?>
</div>


<?php	
echo $form->create(
	'PagoActivo',
	array(
            'url'=> array(
                    'controller'=>'pago_activo',
                    'action'=>$this->action
            ),
            'id' => 'paginacion'
	)
);
if(!empty($this->data['PagoActivo'])){
	foreach($this->data['PagoActivo'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script>
$(function(){    

       
   $('#PagoActivoMonto').blur(function (e) {
    	var monto = $('#PagoActivoMonto').val();
        //Si el monto solo posee un solo decimal, hay que completar los dos decimale
        var arreglo_monto = monto.split(',');
        var entero = arreglo_monto[0];
        var decimal = arreglo_monto[1];

        if (decimal.length == 1){
           monto = monto + '0';
           $('#PagoActivoMonto').val(monto);
        }
        
    }); 

    
	$('#PagoActivoPagoForm').validate({
		rules:{                                           
			'data[PagoActivo][cuentaCreditar]':{
				required:true
			},
			'data[PagoActivo][monto]':{
				required:true,
				numberVE:true,				
				montoMaximo2: '#monto_limite'
			},
			'data[PagoActivo][descripcion]':{
				required:true,alphaNumericSpace:true
			}
		},
		messages:{                                        
			'data[PagoActivo][cuentaCreditar]':{
				required:<?php echo _m('seleccion.requerida',array('el beneficiario'),true) ?>
			},
			'data[PagoActivo][monto]':{
				required:<?php echo _m('seleccion.requerida',array('el monto'),true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'),true) ?>,				
				montoMaximo2: <?php echo _m('monto.limite.config', 'del pago' ,true) ?>,
                                montoMaximo:<?php echo _m('monto.excedido', 'del pago' ,true) ?>
			},
			'data[PagoActivo][descripcion]':{
				required:<?php echo _m('campo.requerido','la descripci&oacute;n',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','la descripci&oacute;n',true) ?>
			}
		}	
	});
});



</script>
