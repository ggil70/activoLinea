<?php
/**
 * Formulario de recarga prepago resultado de la operacion
**/

?>
<div>
<h2 class="user">Recarga Servicios Prepago</h2>
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
<?php
if($error == 0){
?>
		<thead>
			<tr>
                            <td colspan="2" class="resumen_paginacion"><p class="exito">
                            <?php if($pendiente){ ?>
                                    Operación pendiente por ejecutar.</p>
                            <?php } else { ?>
                            Estimado Cliente, su recarga ha sido exitosa.</p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF
                            <?php } ?>
                            </td>
			</tr>
		</thead>

		
		<tr>
			<td class="etiqueta">Cuenta de D&eacute;bito:</td>
			<td><?php echo enmascara($this->data['RecargaServicios']['cuentaDebitar']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Tipo de Operación:</td>
			<td><?php echo $this->data['RecargaServicios']['operacion'] ?></td>
		</tr>
               <?php if(!empty($servicios['r'][$this->data['RecargaServicios']['tipoServicio']])){ ?>
        <tr>
		<td class="etiqueta">Tipo de Servicio:</td>

		<td><?php echo $servicios['r'][$this->data['RecargaServicios']['tipoServicio']] ?></td>
	</tr>
<?php } ?>
               
		<tr>
			<td class="etiqueta">Número Servicio o Cuenta Contrato:</td>
			<td><?php echo $this->data['RecargaServicios']['numeroContrato'] ?></td>
		</tr>
		<?php if(!empty($this->data['RecargaServicios']['nombreBeneficiario'])){ ?>
			<tr>
				<td class="etiqueta">Nombre Beneficiario:</td>
				<td><?php echo $this->data['RecargaServicios']['nombreBeneficiario'] ?></td>
			</tr>
		<?php } ?>
		<tr>
					<td class="etiqueta">Monto Bs.:</td>
					<td>
						<?php
						/*
						$monto_a_mostrar = ''; // Inicializamos la variable

						if (isset($this->data['RecargaServicios']['operacion'])) {
							$operacion = $this->data['RecargaServicios']['operacion'];

							if ($operacion == 'Movistar') {
								// Si la operación es Movistar, muestra montoFormMov
								$monto_a_mostrar = $this->data['RecargaServicios']['montoFormMov'];
							} elseif ($operacion == 'Digitel') {
								// Si la operación es Digitel, muestra montoFormDig
								$monto_a_mostrar = $this->data['RecargaServicios']['montoFormDig'];
							} else {
								// Para cualquier otra operación o si no coincide con las anteriores, muestra monto
								$monto_a_mostrar = $this->data['RecargaServicios']['monto'];
							}
						} else {
							// Si 'operacion' no está definido en el array, por defecto muestra 'monto'
							$monto_a_mostrar = $this->data['RecargaServicios']['monto'];
						}

						echo $monto_a_mostrar;
*/
						?>

						        <?php
        if ($this->data['RecargaServicios']['tipoOperacion'] === 'sd') {
            echo $this->data['RecargaServicios']['montoFormDig'];
        } elseif ($this->data['RecargaServicios']['tipoOperacion'] === 'st') {
            echo $this->data['RecargaServicios']['montoFormMov'];
        } elseif ($this->data['RecargaServicios']['tipoOperacion'] === 'sm') {
            echo $this->data['RecargaServicios']['montoFormMovt'];
        } else {
            // Puedes agregar un valor por defecto o manejar otros casos aquí
            echo $this->data['RecargaServicios']['monto']; // Por ejemplo, el monto original
        }
        ?>
					
					</td>
				</tr>
		<tr>
			<td class="etiqueta">Fecha:</td>
			<td><?php echo date('d/m/Y') ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Referencia:</td>
			<td><?php echo $referencia ?></td>
		</tr>
                <?php if(!$pendiente){ ?>
                <tr>
			<td class="etiqueta">Número Confirmación Operadora:</td>
			<td><?php echo $confirmacion ?></td>
		</tr>
                <?php if($tipoOperacion=='sd'){ ?>
                <thead>
                    <tr>
                        <td colspan="2" class="resumen_paginacion">
                            <p class="exito">Para más información comuníquese al 121 desde su Digitel o al 04129121121</p>
                        </td>
                    </tr>
                </thead>
                <?php }
                
                } ?>
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
                                        '/recarga_servicios/descargar_pdf_sevicios',
                                                        array(
                                                                'class'=>'button'
                                                        )
                                );
                        }
			?>

			</td>		
		</tr>                 
<?php 
}else{
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro">
            <div class="error-message">
                <?php 
                    if(isset($descripcion) && !empty($descripcion)) {
                        echo $descripcion;
                    }else{
                        echo _m('operacion.fallida');
                    }
                ?>
            </div>
        </td>        
	</tr>
	</thead>
<?php 
}

if($error != 0){
?>
	<thead>
	<tr>
            <td colspan="2" class="botonera"  align="right">
                <?php
                        echo $html->link(
                                'Volver',
                                '/recarga_servicios/recarga_servicios',
                                array(
                                        'class'=>'button bloqueo_repetir'
                                )
                        );
                ?>
            </td>
	</tr>
	</thead>
<?php }?>            
</table>
</div>