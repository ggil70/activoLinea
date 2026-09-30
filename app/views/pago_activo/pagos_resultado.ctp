<?php
/**
 * Resultado del pago
**/
?>
<div>
<h2 class="user">Pago Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
<?php
    if($error=='0'){
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
	<td class="etiqueta">Cuenta de D&eacute;bito:</td>
	<td><?php echo enmascara($this->data['PagoActivo']['cuentaDebitar']) ?></td>
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
        <td><?php echo $this->data['PagoActivo']['tipoCedulaRif'].'-'.substr($this->data['PagoActivo']['nroCedulaRif'],1)  ?></td>
        <?php } ?>	
	</tr> 	
	<tr>
	<td class="etiqueta">Monto Bs.:</td>
	<td><?php echo $this->data['PagoActivo']['monto'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Descripci&oacute;n:</td>
	<td><?php echo $this->data['PagoActivo']['descripcion'] ?></td>
	</tr>	
	<tr>
	<td class="etiqueta">Fecha:</td>
	<td><?php echo date('d/m/Y') ?></td>
	</tr>	
        <tr>
        <td class="etiqueta">Referencia:</td>
        <td><?php echo $this->data['PagoActivo']['REFERENCIA'] ?></td>
        </tr>        
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
<?php 
}
else{
?>
    <thead>
    <tr>
    <td class="resumen_paginacion texto_centro"><div class="error-message"><?php if(isset($descripcion) && !empty($descripcion)){ echo $descripcion; } else { echo _m('operacion.fallida'); } ?></div></td>
    </tr>
    </thead>
<?php 
}

if($error != '0'){
?>
<tr>
    <td colspan="2" class="botonera">
    <form class="formulario_enlace" method="post" action="<?php echo Router::url('/pago_activo/pagos') ?>">
            <input type="submit" class="button" value="Volver" />
    </form>
    </td>
</tr>
<?php 
}
?>
</table>
</div>




