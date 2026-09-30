<?php
    /**
     * Vista para mostrar el listado del histórico de conexiones
     */
?>

<div>
<h2 class="user">Hist&oacute;rico de Conexiones</h2>
<div class="line_title_orange"></div>
</div>


<div class="contenedor_encabezado">

<?php

echo $form->create('HistoricoTransacciones',
	array(
		'url'=> array(
			'controller'=>'consultas',
			'action'=>'historico_conexiones'
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

echo '<tr><td colspan="2" class="mensaje ancholibre">Seleccione el mes que desea consultar</td></tr>';

echo $form->input(
	'mes',
	array(
		'label'=>'Mes:',
		'type'=>'select',
		'options'=>$meses,
		'empty'=>'Últimos 12 meses',
		'class'=>'cambio_producto'
	)
);

echo '<tr><td colspan="2" class="submit">';

echo '</td></tr></table>';

echo $form->end();

?>
	

<?php if(isset($resultados)) { ?>

<table class="tabla" cellpadding="0" cellspacing="0" align="center" >
<thead> 
	<tr>
		<td colspan="3" class="resumen_paginacion"><?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?></td>
	</tr>
</thead>
<?php if($total_registros>0) { ?>
<thead> 
<tr>
<td>Fecha/Hora</td>
<td>Canal</td>
<td>Acción</td>
</tr>
</thead>
<tbody>
<?php foreach($resultados as $i){ ?>
<tr>
<td class="texto_centro"><?php echo date('d/m/Y h:i:s a', strtotime($i['Bitacora']['fecha_hora'])) ?></td>
<td><?php echo _c($i['Bitacora']['canal']) ?></td>
<td class="descripcion"><?php  echo $i['Transaccion']['nombre'] ?></td>
</tr>
<?php } ?>
</tbody>
<tfoot>
	<tr>
		<td colspan="3" class="paginador">
			<?php	
			echo $form->create(
				'HistoricoTransacciones',
				array(
					'url'=> array(
						'controller'=>'consultas',
						'action'=>$this->action
					)
				)
			);

			if(!empty($this->data['HistoricoTransacciones'])){
				foreach($this->data['HistoricoTransacciones'] as $k => $v) {
					echo $form->hidden($k);
				}
			}

			echo $this->element('paginador',array('total'=>$total_registros));

			echo $form->end();
			?>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="botonera">
			<?php
			echo $html->link(
				'Volver', 
				'integral', 
				array('class' => 'button')
			);
			echo '&nbsp;&nbsp;';
			echo $html->link(
				'Imprimir', 
				'javascript:print();void(0);', 
				array('class' => 'button')
			);
			?>
		</td>
	</tr>
</tfoot>
<?php } ?>
</table>

<?php } ?>

</div>