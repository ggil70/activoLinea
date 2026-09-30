<?php
/**
 * Resultado de la configuración de usuarios
**/
?>
<div>
<h2 class="user">Configuraci&oacute;n Usuarios - <?php echo $operacion['operacion'] ?></h2>
<div class="line_title_orange"></div>
</div>

<?php 
for($i = 1; $i <= count($operacion['pasos']); $i++) { 
	$class = ($i==4 || ($operacion['operacion']=='Eliminar' && $i==1)) ? '_actual' : '';
?>
<div class="paso_nro<?php echo $class ?>"><?php echo $i ?></div>
<div class="paso_desc<?php echo $class ?>"><?php echo $operacion['pasos'][$i] ?></div>
<?php } ?>

<div class="espacio_vertical_20"></div>

<?php 
if (!$operacion_exitosa){ 
	echo'<div class="contenedor_formulario">';
	
	echo '<div class="error-message">'. _m('operacion.fallida') . '</div>';
	
	echo '<div class="botonera">';
	
		echo $html->link(
			'Volver',
			'/empresa/listado',
			array(
                            'class'=>'button'
			)
		);
		
	echo '</div>';
	
	echo '</div>';
}
else{
?>
<div class="contenedor_formulario">
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" align="center">
		<thead>
			<tr>
				<td colspan="2" class="resumen_paginacion"><p class="exito">Operación realizada satisfactoriamente</p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF </td>
			</tr>
		</thead>
		<tr>
			<td class="etiqueta">Identificaci&oacute;n del Usuario (login):</td>
			<td><?php echo $usuarioEmpresa['Usuario']['nomUsuario'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">RIF de la Empresa:</td>
			<td><?php echo $usuario['tipo_rif'].'-'.desencriptar($usuario['rif']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">C&eacute;dula de Identidad:</td>
			<td><?php echo $usuarioEmpresa['Usuario']['tipoCedulaRif'].'-'.$usuarioEmpresa['Usuario']['nroCedulaRif'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Nombre y Apellido:</td>
			<td><?php echo $usuarioEmpresa['Usuario']['nombre'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cargo:</td>
			<td><?php echo $usuarioEmpresa['Usuario']['cargo'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Email:</td>
			<td><?php echo $usuarioEmpresa['Usuario']['emailPri']; ?></td>
		</tr>
		<tr>
			<td class="etiqueta">N&uacute;mero de Tel&eacute;fono Celular:</td>
			<td>
			<?php echo $usuarioEmpresa['Usuario']['codigoCelular'].$usuarioEmpresa['Usuario']['nroCelular']; ?>
			</td>
		</tr>
		<tr>
			<td class="etiqueta">Perfil:</td>
			<td><?php echo $perfiles[$usuarioEmpresa['Usuario']['perfil']] ?></td>
		</tr>
	</table>
	<?php
	if($operacion['operacion']!='Eliminar'){
		echo '<p>Funcionalidades</p>';

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
				'<td style="width:180px;">Asignar Funcionalidad</td>'.			
				'<td>Cuentas</td>'.			
				'<td style="width:100px;">Nro. Aprobaciones</td>'.			
				'<td>L&iacute;mites Bs.</td>'.	
			 "</tr>";
		echo "</thead>\n";
		echo "<tbody>\n";

		$showrowspan = true;
		
		// Recorre cada menu
		foreach ($transacciones as $menu => $m){	
			
			// Verifica si esta lleno el arreglo
			if(!isset($m['transaccion'])) continue;
			
			$cantidad_transacciones = count(array_intersect_key($m['transaccion'], $usuarioEmpresa['Usuario']['ck']));
			$rowspan = ( count($m['transaccion'])>1) ? 'rowspan="'. $cantidad_transacciones .'"' : '' ;
			$mostrar_menu = true;
			$listo_bloque_cash = false;
			$listo_otras_cash = false;

			// Recorre cada transaccion
			foreach ($m['transaccion'] as $transaccion => $t){
				
				// si la transaccion no se encuentra en los seleccionados continua con la siguiente
				if(!array_key_exists($transaccion, $usuarioEmpresa['Usuario']['ck'])) continue;
				
				echo '<tr>';

				// Verifica si debe mostrar el nombre del menu
				if($mostrar_menu) echo '<td '. $rowspan .'>'. $m['menu'] .'</td>';

				// Muestra el nombre de la transaccion
				echo '<td>'. $t['nombre'] .'</td>';

				// Productos
				echo '<td>';
				if(isset($t['cuentas'])){
					foreach ($t['cuentas'] as $k => $v){
						if ( isset($usuarioEmpresa['Usuario']['ck'][$transaccion]['cuenta']) && in_array($k, $usuarioEmpresa['Usuario']['ck'][$transaccion]['cuenta']) ){
							echo $v.'<br/>';
						}
					}
				}
				echo '</td>';

				// Aprobaciones
				$nroRows = $rowspan;
				
				
				// Para la opción de cash hay que hacer un trato especial
				// ya que hay dos bloques de transacciones:
				// unas necesitan especificar aprobaciones y otras no
				// por lo tanto la discriminación
				if($menu==8){
					$bloque = array(39,40,100016,100017);
					
					if(!$listo_bloque_cash && in_array($transaccion,$bloque)){
						$cantidad = count(array_intersect($bloque, array_keys($usuarioEmpresa['Usuario']['ck'])));
						$nroRows = 'rowspan="'.$cantidad.'"';
						$mostrar_menu = true;
						$listo_bloque_cash = true;
					} else if(!$listo_otras_cash && !in_array($transaccion,$bloque)){
						$cantidad = count(array_diff(
							array_keys(
								array_intersect_key(
									$m['transaccion'],
									$usuarioEmpresa['Usuario']['ck'])
							),
							$bloque
						));
						
						$mostrar_menu = true;
						$listo_otras_cash = true;
						$nroRows = 'rowspan="'.$cantidad.'"';
						$usuarioEmpresa['Usuario']['ck'][$transaccion]['aprobacion']='';
					}
				}

				if($mostrar_menu){
					echo '<td '. $nroRows .' style="text-align:center;">';
						if($t['aprobacion'] && $usuarioEmpresa['Usuario']['ck'][$transaccion]['aprobacion']){
							echo $usuarioEmpresa['Usuario']['ck'][$transaccion]['aprobacion'];
						}
					echo '</td>';
				}


				// Limites
				echo '<td style="text-align:right;">';
				if($t['limite']){
					if (!empty($usuarioEmpresa['Usuario']['ck'][$transaccion]['limite'])){
						echo $usuarioEmpresa['Usuario']['ck'][$transaccion]['limite'];
					} else if(in_array($transaccion, array(100021,100022,100025,100026,100036,100037,100014,100015))){
						if(empty($limite_internas_terceros)){
							echo 'Sin límite';
						} else {
							echo $limite_internas_terceros;
						}
					} else if(in_array($transaccion, array(39,40))){
                                    	    if($transaccion==39){
                                                if(empty($limite_internas_terceros_nomina)){
							echo 'Sin límite';
						} else {
							echo $limite_internas_terceros_nomina;
						}   
                                            }else{
                                                if(empty($limite_internas_terceros_proveedores)){
							echo 'Sin límite';
						} else {
							echo $limite_internas_terceros_proveedores;
						}   
                                            }                                                                                      
                                        } else if(in_array($transaccion, array(100023,100024,100027,100028))){
						if(empty($limite_externas)){
							echo 'Sin límite';
						} else {
							echo $limite_externas;
						}
					} else if(in_array($transaccion, array(100016,100017))){
                                            if($transaccion==100016){
                                                if(empty($limite_externas_nomina)){
							echo 'Sin límite';
						} else {
							echo $limite_externas_nomina;
						}   
                                            }else{
                                                if(empty($limite_externas_proveedores)){
							echo 'Sin límite';
						} else {
							echo $limite_externas_proveedores;
						}   
                                            }                                             
					}
				}
				echo '</td>';

				echo '</tr>';

				$mostrar_menu = false;
			}
		}

		echo "</tbody>\n";
		echo '</table>';
	}
	?>
	
	<div class="botonera" <?php if($operacion['operacion']=='Eliminar') echo 'style="width: 550px; margin: 0 auto;"'?> >
		<?php
	echo $html->link(
		'Volver',
		'/empresa/listado',
		array(
			'class'=>'button',
			'id'=>'volver'
		)
	);	
echo '&nbsp;&nbsp;';
	echo $html->link(
			'Imprimir',
			'javascript:print();void(0)',
			array(
				'class'=>'button'
			)
		);

		echo '&nbsp;&nbsp;';

		echo $html->link(
			'Descargar PDF',
			'/empresa/descargar_pdf_juridico',
			array(
				'class'=>'button'
			)
		);
	
		?>
	</div>
</div>
<?php } ?>