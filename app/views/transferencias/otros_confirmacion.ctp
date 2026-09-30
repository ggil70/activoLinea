<?php
/**
 * Confirmación de transferencia propias de otros bancos
**/
?>
<div>
<h2 class="user">Transferencia Mismo Titular Otro Banco</h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($this->data['pasos']); $i++) { 
	$class = ($i==1) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $this->data['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
	<td class="etiqueta">Cuenta de Débito:</td>
	<td><?php echo enmascara($this->data['Transferencia']['cuentaDebitar']) ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Banco del Beneficiario:</td>
        <?php if($this->data['Transferencia']['simf'] == 's' && $this->data['Transferencia']['telefono'] != ''){ ?>
            <td><?php echo $bancos[$this->data['Transferencia']['banco']] ?></td>
        <?php }else{ ?>
            <td><?php echo $bancos[substr($this->data['Transferencia']['cuentaCreditar'],0,4)] ?></td>
        <?php } ?>
	</tr>
	<tr>
        <?php if($this->data['Transferencia']['simf'] == 's' && trim($this->data['Transferencia']['telefono']) != ''){ ?>    
            <td class="etiqueta">Número Teléfono Beneficiario:</td>
            <td><?php echo $this->data['Transferencia']['codigo_telefono'] .'-'. $this->data['Transferencia']['telefono'] ?></td>
        <?php }else{ ?>
            <td class="etiqueta">Número Cuenta Beneficiario:</td>
            <td><?php echo $this->data['Transferencia']['cuentaCreditar'] ?></td>
        <?php } ?>    
	</tr>
	<tr>
	<td class="etiqueta">Nombre Beneficiario:</td>
	<td><?php echo $this->data['Transferencia']['nombreBeneficiario'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Cédula / RIF del Beneficiario:</td>
	<td><?php  echo $this->data['Transferencia']['tipoCedulaRif'].'-'.$this->data['Transferencia']['nroCedulaRif'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Monto Bs:</td>
	<td><?php echo $this->data['Transferencia']['monto'] ?></td>
	</tr>
        <tr>	
	<td class="etiqueta">Tipo de Transferencia:</td>

	<?php if($this->data['Transferencia']['altoV']=='m'){ ?></td>
	<td>Alto Valor</td>
	<?php }else{
		?></td>
	<td>Crédito Inmediato</td>
	<?php }
		?></td>
	</tr>
	<tr>
	<td class="etiqueta">Descripción:</td>
	<td><?php echo $this->data['Transferencia']['descripcion'] ?></td>
	</tr>
        <?php if($this->data['Transferencia']['afiliar'] == 's'){ ?>
		<tr>
		<td class="etiqueta">Alias:</td>
		<td><?php echo ($this->data['Transferencia']['alias']!='') ? $this->data['Transferencia']['alias'] : 'Alias no asignado' ?></td>
		</tr>
		<?php if( !empty($this->data['Transferencia']['monto_maximo']) && empty($juridico['tipo_rif']) ){ ?>
		<tr>
		<td class="etiqueta">Monto m&aacute;ximo Bs:</td>
		<td><?php echo $this->data['Transferencia']['monto_maximo'] ?></td>
		</tr>
	<?php
		}
	}?>
	<tr>
	<td colspan="2" class="botonera">

	<?php 
	if($this->data['Transferencia']['tipoDestinatario'] == 'a'){
		echo $html->link(
			'Volver',
			'/transferencias/otros_afiliada',
			array(
				'class'=>'button bloqueo_repetir'
			)
		);
	} else {
		echo $html->link(
			'Volver',
			'/transferencias/otros_noafiliada',
			array(
				'class'=>'button bloqueo_repetir'
			)
		);
	}

	echo '&nbsp;&nbsp;';

        echo $html->link(
        'Aceptar',
        '/transferencias/otros_resultado',
        array(
                'class'=>'button bloqueo_repetir'
            )
        );	
	
	?>

	</td>
	</table>
        <div class="azul"><strong>Nota: Estimado Cliente, le informamos que esta operación tiene un costo de <?php echo number_format($this->data['Transferencia']['comision'],2,',','.'); ?> % del monto de la transacción.</strong></div>
</div>




