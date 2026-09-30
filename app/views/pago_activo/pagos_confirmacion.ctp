<?php
/**
 * Confirmación de operación pago activo
**/
?>
<div>
<h2 class="user">Pago Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
	<td class="etiqueta">Cuenta de D&eacute;bito:</td>
	<td><?php  echo enmascara($this->data['PagoActivo']['cuentaDebitar']) ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Banco del Beneficiario:</td>
        <?php if(isset($this->data['PagoActivo']['cuentaCreditar'])){ ?>
        <td><?php echo $bancos[substr($this->data['PagoActivo']['cuentaCreditar'],0,4)] ?></td>
        <?php }else{ ?>
        <td><?php echo $bancos[$this->data['PagoActivo']['banco_receptor']] ?></td>
        <?php } ?>	
	</tr>
	<tr>
	<td class="etiqueta">Número Celular Beneficiario:</td>
        <?php if(isset($this->data['PagoActivo']['cuentaCreditar'])){ ?>
        <td><?php echo "0".substr($this->data['PagoActivo']['cuentaCreditar'],6,10) ?></td>
        <?php }else{ ?>
        <td><?php echo $this->data['PagoActivo']['codigo_telefono'].$this->data['PagoActivo']['telefono'] ?></td>
        <?php } ?>	
	</tr>	
	<tr>
	<td class="etiqueta">Cédula / RIF del Beneficiario:</td>
        <?php if(isset($this->data['PagoActivo']['cuentaCreditar'])){ ?>
        <td><?php echo substr($this->data['PagoActivo']['cuentaCreditar'],16,1)."-".substr($this->data['PagoActivo']['cuentaCreditar'],17) ?></td>
        <?php }else{ ?>
        <td><?php echo $this->data['PagoActivo']['tipoCedulaRif'].'-'.$this->data['PagoActivo']['nroCedulaRif']  ?></td>
        <?php } ?>	
	</tr>        
	<tr>
	<td class="etiqueta">Monto Bs.:</td>
	<td><?php echo $this->data['PagoActivo']['monto'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Descripción:</td>
	<td><?php echo $this->data['PagoActivo']['descripcion'] ?></td>
	</tr>
        <tr>
	<td colspan="2" class="botonera">

	<?php 
	if($this->data['PagoActivo']['tipoDestinatario'] == 'a'){
		echo $html->link(
			'Volver',
			'/pago_activo/pagos_afiliada',
			array(
				'class'=>'button bloqueo_repetir'
			)
		);
	} else {
		echo $html->link(
			'Volver',
			'/pago_activo/pagos_noafiliada',
			array(
				'class'=>'button bloqueo_repetir'
			)
		);
	}
	echo '&nbsp;&nbsp;';
               
        echo $html->link(
        'Aceptar',
        '/pago_activo/pagos_resultado',
        array(
                'class'=>'button bloqueo_repetir'
        )
        );        

	?>           
	</td>
        </tr> 
	</table>
     
    	<?php $longitud = strlen($this->data['PagoActivo']['tipoCedulaRif'].$this->data['PagoActivo']['nroCedulaRif'])?> 
		<div class="azul"><strong>Nota:
		<?php if($longitud<=9) {
			echo _m('pa.pago.comision',array(_pa('comision_pago_activo'),_pa('comision_pa_monto_hasta'),_pa('comision_pa_porcentaje')));
  		}else{
  			echo _m('pa.pago.comision',array(_pa('comision_pago_activo_p2c'),_pa('comision_pa_monto_hasta_p2c'),_pa('comision_pa_porcentaje_p2c')));
  		}?>
   		</strong></div>
	</div>

