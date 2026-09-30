<?php
/**
 * Detalle de la transacción pendiente seleccionada
**/
?>
<div>
<h2 class="user">Aprobaciones Pendientes</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Consulta de Aprobaciones</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php if($transaccion['TransaccionPendiente']['usuario_id'] != $usuario['id'] && !$activo->tdc_validada()) { ?>
<div class="paso_desc">Tarjeta de Coordenadas</div>
<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado de la Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario"
	 <?php if ($transaccion['TransaccionPendiente']['tipo'][0]=='n' || $transaccion['TransaccionPendiente']['tipo'][0]=='v' || $transaccion['TransaccionPendiente']['tipo'][0]=='m'){ ?>
		style="width:790px;"
	 <?php } else { ?>
		style="width:550px;"
	<?php } ?>
>

<?php

echo $form->create('TransaccionPendiente',
	array(
		'url'=>'/transacciones_pendientes/detalle'
	)
);


echo $form->hidden('id');

?>

<input type="hidden" name="data[accion]">

<?php echo $this->element('transaccion_detalle',compact('transaccion')) ?>

<div class="espacio_vertical_20"></div>

<div class="botonera">
<?php 

echo $html->link(
	'Volver',
	'index/v',
	array(
		'class'=>'button',
		'id'=>'volver'
	)
);

$usuario = $this->Session->read('usuarios.datos');

if(
	in_array($usuario['rol_juridico_id'],array(4,5)) &&
	$transaccion['TransaccionPendiente']['usuario_id'] != $usuario['id']
){
	// El aprobador/operador sólo puede aprobar o rechazar transacciones
	// no realizadas por el mismo

	if(validarTransaccion(54) && isset($permiso_aprobacion) && $permiso_aprobacion){
		echo '&nbsp;&nbsp;&nbsp;';

		echo $html->link(
			'Aprobar',
			'javascript:',
			array(
				'class'=>'button boton_accion',
				'id'=>'aprobar'
			)
		);
	}
	
	if(validarTransaccion(55) && isset($permiso_aprobacion) && $permiso_aprobacion){
		echo '&nbsp;&nbsp;&nbsp;';

		echo $html->link(
			'Rechazar',
			'javascript:',
			array(
				'class'=>'button boton_accion',
				'id'=>'rechazar'						
			)
		);
	}
}

if(
	in_array($usuario['rol_juridico_id'],array(3,5)) &&
	$transaccion['TransaccionPendiente']['usuario_id'] == $usuario['id'] &&
	count($transaccion['AprobacionesRealizadas'])==0
){
	// El aprobador/operador sólo puede eliminar o modificar transacciones realizadas por el mismo

	if(validarTransaccion(57)){
		echo '&nbsp;&nbsp;&nbsp;';

		echo $html->link(
			'Eliminar',
			'javascript:',
			array(
				'class'=>'button boton_accion',
				'id'=>'eliminar'						
			)
		);
	}
	
	// bandera para la cantidad valida de afiliados
	$cantidad_valida = $transaccion['TransaccionPendiente']['tipo'][0]=='c' ? false : true;
	
	// Verifica si es un pago nomina o proveedor y la cantidad de afiliados
	if( in_array($transaccion['TransaccionPendiente']['tipo'][0],array('n','v')) ){
		$tipo_trans = ($transaccion['TransaccionPendiente']['tipo'][0]=='n') ? 'n' : 'p';
		$cantidad_valida = !verificaAfiliados($tipo_trans);
	}
	//Jr 26072021
        // Verifica si es un pago nomina o proveedor y la cantidad de afiliados
	if( in_array($transaccion['TransaccionPendiente']['tipo'][0],array('m','x')) ){
		$tipo_trans = ($transaccion['TransaccionPendiente']['tipo'][0]=='m') ? 'm' : 'x';
		$cantidad_valida = !verificaAfiliados($tipo_trans);

	}
        
	// si es un pago masivo o no tiene permiso no se debe modificar
	// En caso de ser un pago manual y la cantidad de afiliados sea mayor a 10 no se debe permitir modificar
	if( $transaccion['TransaccionPendiente']['tipo_carga']!='a' && validarTransaccion(56) && $cantidad_valida &&
            $transaccion['TransaccionPendiente']['tipo'][0] != 'i' &&
            $transaccion['TransaccionPendiente']['tipo'][0] != 'e' &&
            $transaccion['TransaccionPendiente']['tipo'][0] != 'f'){
		echo '&nbsp;&nbsp;&nbsp;';

		echo $html->link(
			'Modificar',
			'javascript:',
			array(
				'class'=>'button boton_accion',
				'id'=>'modificar'						
			)
		);
	}
}

?>
</div>

<?php echo $form->end() ?>

</div>