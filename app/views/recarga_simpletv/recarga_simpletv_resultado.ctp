<?php
/**
 * Formulario de recarga prepago resultado de la operacion
**/
?>
<div>
<h2 class="user">Recarga Simpletv Prepago</h2>
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
			<td><?php echo enmascara($this->data['RecargaSimpletv']['cuentaDebitar']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Tipo de Operación:</td>
			<td><?php echo $this->data['RecargaSimpletv']['operacion'] ?></td>
		</tr>
               <?php if(!empty($servicios['r'][$this->data['RecargaSimpletv']['tipoServicio']])){ ?>
        <tr>
		<td class="etiqueta">Tipo de Servicio:</td>

		<td><?php echo $servicios['r'][$this->data['RecargaSimpletv']['tipoServicio']] ?></td>
	</tr>
<?php } ?>
               
		<tr>
			<td class="etiqueta">Número de Tarjeta de Acceso:</td>
			<td><?php echo $this->data['RecargaSimpletv']['numeroContrato'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Nombre del Beneficiario:</td>
			<td><?php echo $this->data['RecargaSimpletv']['nombreBeneficiario'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Monto Bs.:</td>
			<td><?php echo $this->data['RecargaSimpletv']['montoFinal'] ?></td>
		</tr>

		<?php if(!$pendiente){ ?>
		?>	

		<tr>
			<td class="etiqueta">D&iacute;as disponibles de programaci&oacute;n:</td>
			<td><?php echo $this->data['RecargaSimpletv']['DIAPROGRAMACION'] ?></td>
		</tr>

		<tr>
			<td class="etiqueta">Fecha de Desconexi&oacute;n :</td>
			<td><?php echo $this->data['RecargaSimpletv']['FECHADECONEXION'] ?></td>
		</tr>
		<?php
		}
		?>

		<tr>
			<td class="etiqueta">Descripci&oacute;n:</td>
			<td><?php echo $this->data['RecargaSimpletv']['descripcion'] ?></td>
		</tr>		

		<tr>
			<td class="etiqueta">Fecha de Pago:</td>
			<td><?php echo date('d/m/Y') ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Referencia:</td>
			<td><?php echo $this->data['RecargaSimpletv']['REFERENCIA'] ?></td>
		</tr>
                <?php if(!$pendiente){ ?>
                <tr>
			<td class="etiqueta">Número Confirmación Operadora:</td>
			<td><?php echo $this->data['RecargaSimpletv']['REFERENCIA_OPERADOR'] ?></td>
		</tr>
                <?php
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
                                        '/recarga_simpletv/descargar_pdf_sevicios',
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
                                '/recarga_simpletv/recarga_simpletv',
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