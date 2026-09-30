<?php
/**
 * Resultado de transferencias a cuentas de terceros moneda extranjera en banco activo
**/
?>
<div>
<h2 class="user">Transferencia Tercero Moneda Extranjera en Activo</h2>
<div class="line_title_orange"></div>
</div>

<div>
    <?php for($i = 0; $i < count($this->data['pasos']); $i++) { 
    $class = ($i==$this->data['paso_final']) ? '_actual' : '';
    ?>
    <div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
    <div class="paso_desc<?php echo $class ?>"><?php echo $this->data['pasos'][$i] ?></div>
    <?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<thead>
		<?php

        if($error == 0 ){
        ?>
		<tr>
		<td colspan="2" class="resumen_paginacion"><p class="exito">
		<?php if($pendiente){ ?>
		Operación pendiente por ejecutar.</p>
		<?php } else { ?>
		Operación realizada satisfactoriamente</p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF
		<?php } ?>
		</td>
		</tr>
		</thead>
		<tr>
		<td class="etiqueta">Cuenta de Débito:</td>
		<td><?php echo enmascara($this->data['Transferencia']['cuentaDebitar']) ?></td>
		</tr>
		<tr>
		<td class="etiqueta">Número Cuenta Beneficiario:</td>
		<td><?php echo $this->data['Transferencia']['cuentaCreditar'] ?></td>
		</tr>
		<tr>
		<td class="etiqueta">Nombre Beneficiario:</td>
		<td><?php echo $this->data['Transferencia']['nombreBeneficiario'] ?></td>
		</tr>
		<tr>
		<td class="etiqueta">Cédula / RIF del beneficiario:</td>
		<td><?php echo $this->data['Transferencia']['tipoCedulaRif'].'-'.$this->data['Transferencia']['nroCedulaRif']  ?></td>
		</tr>
		<tr>
		<td class="etiqueta">E-mail del Beneficiario:</td>
		<td><?php echo $this->data['Transferencia']['emailBeneficiario'] ?></td>
		</tr>
		<tr>
		<td class="etiqueta">Monto:</td>
		<td><?php echo $this->data['Transferencia']['monto'] ?></td>
		</tr>
		<tr>
		<td class="etiqueta">Descripci&oacute;n:</td>
		<td><?php echo $this->data['Transferencia']['descripcion'] ?></td>
		</tr>
                <?php if($this->data['Transferencia']['afiliar'] == 's'){ ?>
                        <tr>
                        <td class="etiqueta">Alias:</td>
                        <td><?php echo ($this->data['Transferencia']['alias']!='') ? $this->data['Transferencia']['alias'] : 'Alias no asignado' ?></td>
                        </tr>
                        <?php if( !empty($this->data['Transferencia']['monto_maximo']) && empty($juridico) ){ ?>
                        <tr>
                        <td class="etiqueta">Monto m&aacute;ximo.:</td>
                        <td><?php echo $this->data['Transferencia']['monto_maximo'] ?></td>
                        </tr>
                <?php
                        }
                }?>
		<tr>
		<td class="etiqueta">Fecha:</td>
		<td><?php echo date('d/m/Y') ?></td>
		</tr>
		<?php if(!empty($referencia)){ ?>
			<tr>
				<td class="etiqueta">Referencia:</td>
				<td><?php echo $referencia ?></td>
			</tr>
		<?php } ?>
		<tr>
		<td colspan="2" class="botonera">

		<?php 

		echo $html->link(
			'Imprimir',
			'javascript:print();void(0);',
			array(
				'class'=>'button'
			)
		);

		if(!$pendiente){
			echo '&nbsp;&nbsp;';

			echo $html->link(
				'Descargar PDF',
				'/consultas/descargar_pdf',
				array(
					'class'=>'button'
				)
			);
		
		}

		?>

		</td>
		</tr>
        <?php if($error_afiliacion){ ?>
                <tr><td colspan="2" class="botonera" ><div class="error-message"><?php echo _m('afiliacion.fallida', 'de la cuenta'); ?></div></td></tr>
	<?php } ?>
<?php 
}else if($error== 182){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('producto.invalido', 'el número cuenta a acreditar'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error == 2149 ){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.erronea', 'el número cuenta a acreditar'); ?></div></td>
	
	</tr>
	</thead>
<?php 
}else if($error == 1616 ){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.sobregirada'); ?></div></td>
	
	</tr>
	</thead>
<?php 
}else if($error == 1049){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.producto'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error == 6029){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.simbolo'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error== 223 ){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.inactiva', 'la cuenta'); ?></div></td>
	</tr>
	</thead>
<?php 
}
else if($error == 1912){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('monto.excedido', 'de la transferencia'); ?></div></td>
	</tr>
	</thead>
<?php 
}
else{
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('operacion.fallida'); ?></div></td>
	</tr>
	</thead>
<?php 
}if($error != 0){
?>
<tr>
	<td colspan="2" class="botonera">
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/transferencia_me/terceros') ?>">
	<input type="submit" class="button" value="Volver" />
</form>
</td>
</tr>
<?php 
}
?>
</table>
</div>
