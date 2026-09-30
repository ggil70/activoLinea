<?php
/**
 * Confirmación de los montos límites
**/
?>
<div>
<h2 class="user"><?php echo $data_session['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($data_session['pasos']); $i++) { 
	$class = ($i==$data_session['paso_actual']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $data_session['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'usuarios',
				'action'=>'confirmar_limites'
			)
		)
	);

	?>
	<input type="hidden" name="data[Usuario][confirmo]" value="si">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los límites de montos diario por transacción para pagos y transferencias:</td>
	</tr>
	</thead>
	<tr>
	<td class="etiqueta">Internas a Terceros (Banco Activo)</td>
	<td><?php if(empty($this->data['Usuario']['it_transaccion'])){
		if(empty($limites['max_it_trans'])){
			echo 'Sin límite';
		} else {
			echo $limites['max_it_trans'];
		}
	} else {
		echo $this->data['Usuario']['it_transaccion'];
	} ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Externas (mismo titular otros Bancos)</td>
	<td><?php if(empty($this->data['Usuario']['e_transaccion'])){
		if(empty($limites['max_e_trans'])){
			echo 'Sin límite';
		} else {
			echo $limites['max_e_trans'];
		}
	} else {
		echo $this->data['Usuario']['e_transaccion'];
	} ?></td>
	</tr>
        
        
        
        
        <?php if($data_session['juridico']==true){ ?>
        <tr>
	<td class="etiqueta">Pago de Nómina Internas a Terceros (Banco Activo)</td>
	<td><?php if(empty($this->data['Usuario']['it_transaccion_n'])){
		if(empty($limites['max_it_n'])){
			echo 'Sin límite';
		} else {
			echo $limites['max_it_n'];
		}
	} else {
		echo $this->data['Usuario']['it_transaccion_n'];
	} ?></td>
	</tr>
        <tr>
	<td class="etiqueta">Pago de Nómina Externas (mismo titular otros Bancos)</td>
	<td><?php if(empty($this->data['Usuario']['e_transaccion_n'])){
		if(empty($limites['max_e_n'])){
			echo 'Sin límite';
		} else {
			echo $limites['max_e_n'];
		}
	} else {
		echo $this->data['Usuario']['e_transaccion_n'];
	} ?></td>
	</tr>
        
        
        
        
        
        <tr>
	<td class="etiqueta">Pago a Proveedores Internas a Terceros (Banco Activo)</td>
	<td><?php if(empty($this->data['Usuario']['it_transaccion_p'])){
		if(empty($limites['max_it_p'])){
			echo 'Sin límite';
		} else {
			echo $limites['max_it_p'];
		}
	} else {
		echo $this->data['Usuario']['it_transaccion_p'];
	} ?></td>
	</tr>
        <tr>
	<td class="etiqueta">Pago a Proveedores Externas (mismo titular otros Bancos)</td>
	<td><?php if(empty($this->data['Usuario']['e_transaccion_p'])){
		if(empty($limites['max_e_p'])){
			echo 'Sin límite';
		} else {
			echo $limites['max_e_p'];
		}
	} else {
		echo $this->data['Usuario']['e_transaccion_p'];
	} ?></td>
	</tr>
    <tr>
		<td class="etiqueta">Pago Nomina USD</td>
		<td><?php


		 if(empty($this->data['Usuario']['it_transaccion_n_usd'])){
			if(empty($limites['max_it_n_usd'])){
				echo 'Sin límite';
			} else {
				echo $limites['max_it_n_usd'];
			}
		} else {
			echo $this->data['Usuario']['it_transaccion_n_usd'];
		} ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Pago Nomina EUR</td>
		<td><?php


		 if(empty($this->data['Usuario']['it_transaccion_n_eur'])){
			if(empty($limites['max_it_n_eur'])){
				echo 'Sin límite';
			} else {
				echo $limites['max_it_n_eur'];
			}
		} else {
			echo $this->data['Usuario']['it_transaccion_n_eur'];
		} ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Pago Proveedores USD</td>
		<td><?php


		 if(empty($this->data['Usuario']['it_transaccion_p_usd'])){
			if(empty($limites['max_it_p_usd'])){
				echo 'Sin límite';
			} else {
				echo $limites['max_it_p_usd'];
			}
		} else {
			echo $this->data['Usuario']['it_transaccion_p_usd'];
		} ?></td>
	</tr>
	<td class="etiqueta">Pago Proveedores EUR</td>
		<td><?php


		 if(empty($this->data['Usuario']['it_transaccion_p_eur'])){
			if(empty($limites['max_it_p_eur'])){
				echo 'Sin límite';
			} else {
				echo $limites['max_it_p_eur'];
			}
		} else {
			echo $this->data['Usuario']['it_transaccion_p_eur'];
		} ?></td>
	</tr>

    <?php } ?>
           
	<tr>
	<td colspan="2" class="botonera">

	<?php 
		
	echo $html->link(
		'Volver',
		'limite_montos/v',
		array(
			'class'=>'button'
		)
	);
		
	echo '&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'#',
		array(
			'class'=>'envio_formulario button'
		)
	);

	?>

	</td>
	</tr>
	</table>
</form>
</div>

