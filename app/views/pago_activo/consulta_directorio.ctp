<?php

/**
     * Vista para mostrar el Directorio de Pago Activo
     */
?>
<div>
    <h2 class="user">Directorio</h2>
    <div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

    <p>En esta opci&oacute;n Ud. podr&aacute; crear y eliminar afiliaciones del Directorio Pago Activo</p>

<?php
echo $form->create('Directorio',
    array(
        'url'=> array(
            'controller'=>'pago_activo',
            'action'=>'consulta_directorio'
        ),
        'id'=>'DirectorioPagoActivoForm',
    )
);

?>
    <input type="hidden" name="data[accion]">

    <table class="formulario" cellpadding="0" cellspacing="0" border="0">	
        <tr>
            <td>
                <label for="data[Directorio][id]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros'); ?></label>
        <?php
                if ($form->isFieldError('id')){
                   echo $form->error('id', _m('uno.seleccion.requerida','de los registros'));
                }
        ?>
            </td>
        </tr>
    </table>
    <table class="tabla" width="100%" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <tr>
                <td colspan="6" class="resumen_paginacion">			
                    <div style="text-align:right; width:38%; float:right;">
                       <?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?>
                    </div>
                </td>
            </tr>
        </thead>
        <thead>
            <tr>
                <td class="texto_centro ancholibre"><span class="requerido">*</span></td>			
                <td>Banco Beneficiario</td>
                <td>Nombre Beneficiario</td>
                <td>Número Celular</td>	
            </tr>
        </thead>

        <tbody>
		<?php 
                if(isset($listado) && count($listado)>0){
                    foreach($resultados as $i){                
                ?>
        <td class="texto_centro ancholibre">
            <input
                type="radio"
                class="seleccion_afiliado"
                id="<?php echo 'sel'.$i['codBanco'].$i['telefono'] ?>"
                name="data[Directorio][id]"
                value="<?php echo $i['codBanco'].";".$i['telefono'].";".$i['nacionalidad'].$i['cedula'] ?>"
                        <?php if(
                                    isset($this->data['Directorio']['id'])
                                    && 0==strcmp($this->data['Directorio']['id'],$i['telefono'])
                                ){
                                echo 'checked="checked"';
                        }
                        ?>                
                />		
        </td>
        <td><?php echo $i['nombreBanco'] ?></td>
        <td><?php echo $i['nombre']?></td>
        <td><?php echo  "0".trim(substr($i['telefono'], 2, 3)).trim(substr($i['telefono'], 5)) ?></td>	
        </tr>
                <?php                
                    }
                ?>
        <tr>
            <td colspan="6" class="paginador">				
                        <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros,'tamano_paginacion'=>5)); ?>
            </td>
        </tr>

        <tr><td colspan="6" style="border: 0px;" ><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>

        <tr>
            <td colspan="6" class="botonera">

                            <?php 

                            echo $html->link(
                                    'Crear',
                                    'javascript:',
                                    array(
                                        'class'=>'button',
                                        'id'=>'crear'
                                    )
                            );

//                            echo '&nbsp;&nbsp;&nbsp;';
//
//                            echo $html->link(
//                                    'Modificar',
//                                    'javascript:',
//                                    array(
//                                        'id'=>'modificar',
//                                        'class'=>'button'
//                                    )
//                            );

                            echo '&nbsp;&nbsp;&nbsp;';

                            echo $html->link(
                                    'Eliminar',
                                    'javascript:',
                                    array(
                                        'class'=>'button',
                                        'id'=>'eliminar'						
                                    )
                            );
                            ?>

            </td>
        </tr>

		<?php
		}
		else{
		?>
        <tr><td colspan="5" class="texto_centro">No se encontraron resultados...</td></tr>
        <tr>
            <td colspan="5" class="botonera">
                                    <?php 
                                    echo $html->link(
                                            'Crear',
                                            'javascript:',
                                            array(
                                                'class'=>'button',
                                                'id'=>'crear'
                                            )
                                    );
                                    ?>
            </td>
        </tr>
		<?php
		}
		?>
        </tbody>
    </table>
<?php echo $form->end(); ?>
</div>

<?php	
echo $form->create(
	'Directorio',
	array(
		'url'=> array(
                    'controller'=>'pago_activo',
                    'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['Directorio'])){
	foreach($this->data['Directorio'] as $k => $v) {
            echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script type="text/javascript">

    $(function () {

        $('#crear').click(function () {
            $('input[name="data[accion]"]').val('crear');
            $('#DirectorioPagoActivoForm').submit();
        });

        $('#modificar').click(function () {
            if ($('#DirectorioPagoActivoForm').valid()) {
                $('input[name="data[accion]"]').val('editar');
                $('#DirectorioPagoActivoForm').submit();
            }
        });

        $('#eliminar').click(function () {
            if ($('#DirectorioPagoActivoForm').valid()) {
                $('input[name="data[accion]"]').val('eliminar');
                $('#DirectorioPagoActivoForm').submit();
            }
        });

        $('#DirectorioPagoActivoForm').validate({
            onsubmit: false,
            onkeyup: false,
            onclick: false,
            onfocusout: false,
            rules: {
                'data[Directorio][id]': {
                    required: true
                }
            },
            messages: {
                'data[Directorio][id]': {
                    required:<?php echo _m('seleccion.requerida',array('el beneficiario'),true) ?>
                }
            }
        });
    });
</script>
