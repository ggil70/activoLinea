<?php
/**
 * Formulario para la configuración de las transacciones a permitir
 */
?>
<div>
<h2 class="user">Configuraci&oacute;n Usuarios - <?php echo $operacion['operacion'] ?></h2>
<div class="line_title_orange"></div>
</div>

<?php 
for($i = 1; $i <= count($operacion['pasos']); $i++) { 
	$class = $i==2 ? '_actual' : '';
?>
<div class="paso_nro<?php echo $class ?>"><?php echo $i ?></div>
<div class="paso_desc<?php echo $class ?>"><?php echo $operacion['pasos'][$i] ?></div>
<?php } ?>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style=" width:90%">
<?php
if(!count($transacciones)){
	echo '<p>Error inesperado! Por favor intente más tarde</p>';
}
else{ /* INICIO FORMULARIO */ 
?>
	<p>A trav&eacute;s de esta opci&oacute;n usted podr&aacute; realizar la <?php
	if($operacion['operacion']=='Agregar'){
		echo 'inserción';
	} else {
		echo 'actualizaci&oacute;n';
	}
	?> de los datos que se indican a continuaci&oacute;n</p>
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'empresa',
				'action'=>'configuracion'
			),
			'inputDefaults'=>array(
				'div'=>false,
				'label'=>'etiqueta',
				'before'=>'<tr><td class="etiqueta">',
				'between'=>'</td><td class="campo" colspan="2">',
				'after'=>'</td></tr>',
				'error'=>false
			)
		)
	);
	
	echo $form->hidden('confirmado', array('value'=>true));
	
	?>
	
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" align="center">
		<thead>
			<tr>
				<td colspan="2" class="resumen_paginacion"><p>Configure las funcionalidades que ser&aacute;n asignadas al usuario</p></td>
			</tr>
		</thead>	
		<tr>
			<td class="etiqueta">Identificaci&oacute;n del Usuario (login):</td>
			<td><?php echo $this->data['Usuario']['nomUsuario'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">C&eacute;dula de Identidad:</td>
			<td><?php echo $this->data['Usuario']['tipoCedulaRif'].'-'.$this->data['Usuario']['nroCedulaRif'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Nombre y Apellido:</td>
			<td><?php echo $this->data['Usuario']['nombre'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cargo:</td>
			<td><?php echo $this->data['Usuario']['cargo'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Email:</td>
			<td><?php echo $this->data['Usuario']['emailPri']; ?></td>
		</tr>
		<tr>
			<td class="etiqueta">N&uacute;mero de Tel&eacute;fono Celular:</td>
			<td>
			<?php echo $this->data['Usuario']['codigoCelular'].$this->data['Usuario']['nroCelular']; ?>
			</td>
		</tr>
		<tr>
			<td class="etiqueta">Perfil:</td>
			<td><?php echo $perfiles[$this->data['Usuario']['perfil']] ?></td>
		</tr>
	</table>
	
	<?php
	
	echo '<p>Configuraci&oacute;n de funcionalidades</p>';
	
	echo $html->tag(
		'table',
		null,
		array(
			'class'=>'tabla',
			'cellpadding'=>'0',
			'cellspacing'=>'0',
			'border'=>'0',
			'align'=>'center',
			'width'=>'100%'
		)
	);

	echo "\n<thead>";
	echo "<tr>".
			'<td>Men&uacute;</td>'.
			'<td style="border-right:0px;" width="12px"></td>'.
			'<td style="border-left:0px;" width="180px">Asignar Funcionalidad</td>'.			
			'<td width="110px">Cuentas</td>'.			
			'<td width="100px">Nro. Aprobaciones</td>'.			
			'<td style="word-wrap:break-word; -ms-word-wrap:break-word;" width="170px">L&iacute;mites Bs.</td>'.	
		 "</tr>";
	echo "</thead>\n";
	$listo = false;
	// Recorre cada menu
	foreach ($transacciones as $menu => $m){
	
		// Verifica si esta lleno el arreglo
		if(!isset($m['transaccion'])) continue;

		$rowspan = (count($m['transaccion'])>1) ? 'rowspan="'.count($m['transaccion']).'"' : '' ;
		$mostrar_menu = true;

		// Recorre cada transaccion
		foreach ($m['transaccion'] as $transaccion => $t){
			echo '<tr>';

			// Verifica si debe mostrar el nombre del menu
			if($mostrar_menu) echo '<td '. $rowspan .'>'. $m['menu'] .'</td>';

			// Muestra el nombre de la transaccion
			$seleccionado = ( isset($this->data['Usuario']['ck'][$transaccion]['menu']) ) ? 'checked="checked"' : '';
			echo '<td class="texto_centro ancholibre" style="border-right:0px; padding:3px;">
					<input onclick="marcarPadre(this,'.$transaccion.');" id="A'. $transaccion .'" type="checkbox" value="'. $transaccion .'"  name="data[Usuario][ck]['.$transaccion.'][menu]" '. $seleccionado .' />
				</td>
				<td style="border-left:0px; padding-left:0px;">'. 
					'<input type="hidden" class="ckModulo'.$menu.'" name="data[Usuario][ck]['.$transaccion.'][aprobacion]" value=""/>'.
					$t['nombre'].
				'</td>'
			;
			
			// Productos			
			echo '<td>';
			if(isset($t['cuentas'])){
				foreach ($t['cuentas'] as $k => $v){

					$seleccionado = ( isset($this->data['Usuario']['ck'][$transaccion]['cuenta']) && in_array($k, $this->data['Usuario']['ck'][$transaccion]['cuenta']) ) ? 'checked="checked"' : '';
					echo '<input onclick="marcar(this,'.$transaccion.');" type="checkbox" value="'. $k .'" name="data[Usuario][ck]['.$transaccion.'][cuenta][]" '. $seleccionado .' />'.$v.'<br/>';
				}

			}

			echo '</td>';

			// Aprobaciones
			$nroRows = $rowspan;
			
			/** PARA LA OPCION DE CASH MANAGMENT **/
			if($menu==8 && $transaccion==39){
				$nroRows = 'rowspan="4"';
			}
			elseif($menu==8 && $transaccion==41 && !$listo){
				$nroRows = 'rowspan="3"';
				$mostrar_menu = true;
				$t['aprobacion'] = false;
				$listo = true;
			}
			elseif($menu==8 && $transaccion==43 && !$listo){
				$nroRows = 'rowspan="1"';
				$mostrar_menu = true;
				$t['aprobacion'] = false;
				$listo = true;
			}
			elseif($menu==8 && $transaccion==101108){
            	$nroRows = 'rowspan="1"';
            	$mostrar_menu = true;
			}elseif($menu==8 && $transaccion==101109){
            	$nroRows = 'rowspan="1"';
            	$mostrar_menu = true;
            }



			/** PARA LA OPCION DE CASH MANAGMENT **/
			
			if($mostrar_menu){
				echo '<td '. $nroRows .' style="text-align:center;">';
					if($t['aprobacion']){
						echo '<select class="aprobacion" id="aprob'.$menu.'">';
						for($i=1; $i<=2; $i++){
							$seleccionado = ( isset($this->data['Usuario']['ck'][$transaccion]['aprobacion']) && ($this->data['Usuario']['ck'][$transaccion]['aprobacion']==$i) ) ? 'selected="selected"' : '';
							echo '<option value="'. $i .'" '. $seleccionado .'>'. $i .'</option>';
						}
						echo '</select>';
					}
				echo '</td>';
			}

			
			// Limites
			echo '<td style="text-align:center;">';

            //para nomina proveedor express 
			/*if($transaccion==101108 || $transaccion==101109){
				echo '<input type="text" class="monto texto_derecho" name="data[Usuario][ck]['. $transaccion .'][limite]" value="'.$valor.'" maxlength="18" style="width:140px;"/>';
			}else{
			*/	
				if($t['limite']){
					$valor = ( isset($this->data['Usuario']['ck'][$transaccion]['menu']) && isset($this->data['Usuario']['ck'][$transaccion]['limite']) && !empty($this->data['Usuario']['ck'][$transaccion]['limite']) ) ? $this->data['Usuario']['ck'][$transaccion]['limite'] : '';
					echo '<input type="text" class="monto texto_derecho" name="data[Usuario][ck]['. $transaccion .'][limite]" value="'.$valor.'" maxlength="18" style="width:140px;"/>';
				}
		    //}



			echo '</td>';
			
			echo '</tr>';

			$mostrar_menu = false;
		}
	}

	echo '</table>';
	
	echo '<br/>';
	
	echo '<label id="error-ck" class="error" style="display:none; text-align:right;">'._m('una.seleccion.requerida','funcionalidad').'</label>';
	
    echo '<p align="left"><b>NOTA:</b><br/>
			Usted puede definir un monto distinto a los l&iacute;mites m&aacute;ximos permitidos. En cuyo caso,
			el monto que coloque podr&aacute; estar por debajo y en ning&uacute;n caso por encima
			de los L&iacute;mites M&aacute;ximos Permitidos por Banco Activo.<br><br>
			Estimado Cliente, debe tener en cuenta que si usted deja en blanco el monto para los casos de: Transferencias y Pagos, el banco entenderá que el monto de la funcionalidad adoptará el valor máximo permitido por el banco</p>';
			
			

	
	echo '<div class="botonera">';

		echo $html->link(
			'Volver',
			'/empresa/usuario',
			array(
				'class'=>'button',
				'id'=>'volver'
			)
		);

		echo '&nbsp;&nbsp;&nbsp;';

		echo $html->link(
			'Aceptar',
			'#',
			array(
				'class'=>'button envio_formulario'
			)		
		);

	echo '</div>';

	echo $form->end();

?>

<?php } /* FIN FORMULARIO */ ?>
</div>

<?php if(count($transacciones)){ ?>
<script type="text/javascript">
	
	function setearAprobacion(){
		var m = $(this)
			.attr('id')
			.replace( /aprob(\d+)$/, '$1');
			
		$('.ckModulo'+m).val($(this).val());
	}
	
	function marcar(c,id){
		if(c.checked){
			$('input[name="data[Usuario][ck]['+id+'][menu]"]').attr('checked', true);
		}else{
			var bool=false;
			$('input[name^="data[Usuario][ck]['+id+'][cuenta]"]').each(function(index) {
				bool = bool || $(this).is(':checked');
			});
			$('input[name="data[Usuario][ck]['+id+'][menu]"]').attr('checked', bool);
		}
	}

	function marcarPadre(c,id){	
		if(c.checked){									
			bool=false;
			$('input[name^="data[Usuario][ck]['+id+'][cuenta]"]').each(function(index) {
				bool = bool || $(this).is(':checked');
			});
			if(!bool){
				var i=0;
				$('input[name^="data[Usuario][ck]['+id+'][cuenta]"]').each(function(index) {
						$(this).attr('checked', true);						
				});
			}
		}else{
			$('input[name^="data[Usuario][ck]['+id+'][cuenta]"]').each(function(index) {
				$(this).attr('checked', false);
			});			
		}
	}

	$(function(){
		
		$('.aprobacion').each(setearAprobacion).change(setearAprobacion);
		
		$('input[name^="data[Usuario][ck]"]').click(function(){
			if($(this).is(':checked')){
				$('#error-ck').hide();
			}
		});

		// para la validacion
		$('#UsuarioConfiguracionForm').validate({
			submitHandler: function(form){		
				$.each($('tr td div.error-message'),function(){
					if($(this).siblings().size()==0){
						$(this).parent().parent().remove();
					}
				});
			
				if($('input[name^="data[Usuario][ck]"]').is(':checked')){
					$('#error-ck').hide();	
					form.submit();
				} else {
					$('#error-ck').show();
				}
			},
			rules:{
				<?php
				// Recorre cada menu
				foreach ($transacciones as $m){

					// Verifica si esta lleno el arreglo
					if(!isset($m['transaccion'])) continue;

					// Recorre cada transaccion
					foreach ($m['transaccion'] as $transaccion => $t){
						
						// Verifica si la transaccion maneja limites
						if($t['limite']){
							?>
							'data[Usuario][ck][<?php echo $transaccion; ?>][limite]':{
								numberVE: true
								<?php if($limite_internas_terceros && in_array($transaccion, array(100021,100022,100025,100026,100036,100037))) { ?>
								,maxMontoLimite: '<?php echo $limite_internas_terceros ?>'	
								<?php } else if($limite_externas && in_array($transaccion, array(100023,100024,100027,100028))) { ?>
								,maxMontoLimite: '<?php echo $limite_externas ?>'
                                                                <?php } else if($limite_internas_terceros_nomina && $transaccion == 39) { ?>
								,maxMontoLimite: '<?php echo $limite_internas_terceros_nomina ?>'
                                                                <?php } else if($limite_externas_nomina && $transaccion == 100016) { ?>
								,maxMontoLimite: '<?php echo $limite_externas_nomina ?>'                                                                
                                                                <?php } else if($limite_internas_terceros_proveedores && $transaccion == 40) { ?>
								,maxMontoLimite: '<?php echo $limite_internas_terceros_proveedores ?>'
                                                                <?php } else if($limite_externas_proveedores && $transaccion == 100017) { ?>
								,maxMontoLimite: '<?php echo $limite_externas_proveedores ?>'
								<?php } ?>
							},
							<?php 
						}
					}
				}
				?>
				'data[Usuario][perfil]':{
					required:true
				}
			},
			messages:{
				<?php
				
				// Recorre cada menu
				foreach ($transacciones as $m){

					// Verifica si esta lleno el arreglo
					if(!isset($m['transaccion'])) continue;

					// Recorre cada transaccion
					foreach ($m['transaccion'] as $transaccion => $t){

						// Verifica si la transaccion maneja limites
						if($t['limite']){
							?>
							'data[Usuario][ck][<?php echo $transaccion; ?>][limite]':{				
								numberVE: <?php echo _m('formato.invalido','el monto límite',true) ?>,
								maxMontoLimite: <?php echo _m('monto.limite.config',null,true) ?>
							},
							<?php 
						}
					}
				}
				?>
				'data[Usuario][perfil]':{
					required:<?php echo _m('seleccion.requerida',array('el perfil'),true) ?>
				}	
			}
		});	
	});
</script>
<?php } ?>