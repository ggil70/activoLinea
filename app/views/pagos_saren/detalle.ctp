<?php
/**
 * Formulario de seleccion de compromisos pago seniat
**/
?>
<div>
<h2 class="user">Pagos de Impuestos Nacionales/Aduanales <?php echo $descripcion; ?></h2>
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

<p>A continuaci&oacute;n proceda a seleccionar el beneficiario del pago.</p>

<?php
echo $form->create('Afiliacion',
	array(
		'url'=> array(
			'controller'=>'pagos_saren',
			'action'=>'detalle'
		)
	)
);
echo $form->hidden('nombre_beneficiario');

?>

        
<span>
    <label for="data[PagosSeniat][idAfiliacion]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros') ?></label>
      <?php
      if ($form->isFieldError('idAfiliacion')){
            echo $form->error('idAfiliacion', _m('uno.seleccion.requerida','de los registros'));
      }
      ?>
</span>
<table class="tabla" width="100%" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
		<td class="texto_centro ancholibre"><span class="requerido">*</span></td>
		<td>Alias</td>
		<td>Nombre Beneficiario</td>
		<td>RIF</td>
	</tr>
	</thead>
	
	<tbody>
		<?php 
		if($total_registros > 0){
			foreach($afiliaciones as $l) {
		?>
                    <tr id='<?php echo "afi_".$l['Afiliacion']['id'] ?>'>
                            <td class="texto_centro ancholibre">
                                    <input type="radio" name="data[PagosSeniat][idAfiliacion]" value="<?php echo $l['Afiliacion']['id'] ?>"
                                            <?php if(
                                                            isset($this->data['PagosSeniat']['idAfiliacion'])
                                                            && 0==strcmp($this->data['PagosSeniat']['idAfiliacion'],$l['Afiliacion']['id'])
                                                    ){
                                                    echo 'checked="checked"';
                                            }
                                            ?>
                                    />
                            </td>
                            <td><?php echo ($l['Afiliacion']['alias']!="") ? desencriptar($l['Afiliacion']['alias']) : '' ?></td>
                            <td> <?php 
                                    $nombre=desencriptar($l['Afiliacion']['nombre_beneficiario']);
                                    if (empty($nombre)){
                                       echo '';
                                    }else{
                                       echo desencriptar($l['Afiliacion']['nombre_beneficiario']);			
                                    }?>
                            </td>
                            <td><?php echo desencriptar($l['Afiliacion']['numero_cuenta']) ?></td>
                    </tr>
                    <?php } ?>
                    
                    <tr><td colspan="6" style="border: 0px;" ><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>

                    <tr id="notaSeniat">
                        <td colspan="6" style="border:0px; text-align:center;" class="requerido2">
                            <b>Recuerde que para realizar el Pago a Terceros en los Pagos de
                            Impuestos al SENIAT, debe estar registrado como usuario autorizado del
                            beneficiario en la Página Web del SENIAT: <u>http://www.seniat.gob.ve</u> en
                            Registro Autorizados.</b>
                        </td>
                    </tr>

                    <tr>
                            <td colspan="6" class="botonera">
                                    <?php 
                                     echo $html->link(
                                            'Aceptar',
                                            'javascript:',
                                            array(
                                                    'class'=>'button',
                                                    'id'=>'aceptar'
                                            )
                                          );
                                    ?>

                            </td>
                    </tr>
		<?php
		}
		else{
		?>
			<tr><td colspan="6" class="texto_centro">No se encontraron afiliaciones para Pago de Impuestos Nacionales/Aduanales <?php echo $descripcion ?></td></tr>
		<?php
		}
		?>
	</tbody>
</table>
<?php echo $form->end(); ?>
</div>

<div class="basic-modal-content" id="procesandoC">
	<h3>Procesando...</h3>
	<p><?php echo(_m('procesando.transaccion','la consulta solicitada')); ?></p>
</div>

<script type="text/javascript">

$(function(){

	$('#aceptar').click(function(){
                if($('#AfiliacionTercerosListadoForm').valid()){
                    id_afiliacion = $("input[name='data[PagosSeniat][idAfiliacion]']:checked").val();
                    $('input[name="data[Afiliacion][nombre_beneficiario]"]').val($('#afi_'+id_afiliacion).children('td')[2].innerHTML);
                    
                    $('#procesandoC').modal({minHeight:120,minWidth:400,close:false});
                    
            $('#AfiliacionTercerosListadoForm').submit();
		}
	});
	
	$('#AfiliacionTercerosListadoForm').validate({
		onsubmit:false,
		onkeyup:false,
		onclick:false,
		onfocusout:false,
                rules:{
			'data[PagosSeniat][idAfiliacion]':{
				required: true
			}
		}
	});
});
</script>
