<?php
/**
 * Resultado de solicitud de tarjeta de coordenadas
**/
?>
<div>
<h2 class="user"><?php echo $data_session['titulo'] ?></h2>
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

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" style="margin: 0px auto">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">
	<?php if($tdc_creada) { ?>
	<p class="exito">Operaci&oacute;n realizada satisfactoriamente</p>
	<p>La Tarjeta de Coordenadas es una herramienta con la cual Usted podr&aacute; 
		realizar todas operaciones con tranquilidad y seguridad</p>
	<p>A continuaci&oacute;n se ha generado su Tarjeta de Coordenadas:
	<?php } else { ?>
	<p class="error-message"><?php echo _m('error.fatal') ?></p>
	<?php } ?>
	</p>
	</td>
	</tr>
	
	<tr><td colspan="2" class="resumen_paginacion">
	
	
	<?php if(isset($tdc)){ ?>
	<table cellpadding="0" cellspacing="0" border="0" class="tdc">
	<?php
		$filas = Configure::read('filas.tdc');
		
		$clases = array('normal','alter');

		for($row=0;$row<=6;$row++){
			if($row > 0){
				echo "<tr class='".$clases[$row % count($clases)]."'>";
			} else {
				echo "<tr>";
			}
			
			for($col=0;$col<=9;$col++){
				if($row == 0){
					if($col==0){
						echo '<td class="punta">&nbsp;</td>';
					} else {
						echo "<td class='tope'>$col</td>";
					}
				} else {
					if($col==0){
						echo "<td>".$filas[$row]."</td>";
					} else {
						echo "<td>".$tdc[$row-1][$col-1]."</td>";
					}
				}
			}
			
			echo '</tr>';
		}
	?>
	<tr><td colspan="10" class="serial">Serial: <?php echo $serial ?></td>
	</table>
	<?php } ?>
	
	</td></tr>
	</thead>
	</table>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td colspan="2" class="botonera">
		<?php 
		if($tdc_creada){
			echo $html->link(
				'Imprimir',
				'javascript:print();void(0);',
				array(
					'class'=>'button'
				)
			);

			echo '&nbsp;&nbsp;';

			echo $html->link(
				'Descargar PDF',
				'descargar_tdc',
				array(
					'class'=>'button bloqueo_repetir'
				)
			);

			echo '&nbsp;&nbsp;';

			echo $html->link(
				'Continuar',
				$data_session['url_aceptar'],
				array(
					'class'=>'button'
				)
			);
		}
		?>
	</td>
	</table>
	
</div>
