<?php 
    /**
     * Vista para la confirmación de la eliminación de las afiliaciones
     */

    
//echo "TA: " . $this->data['Afiliacion']['tipo_afiliacion'];
//echo "<br>TO = " . $this->data['Afiliacion']['tipo_operacion'];
//die("<br>BANCO = " . $this->data['Afiliacion']['banco']);

?>
<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Resultado Operación</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea eliminar:</td>
	</tr>
	</thead>	
	<tr>
		<td class="etiqueta">Tipo de Afiliaci&oacute;n:</td>
		<td><?php echo $tipos[$this->data['Afiliacion']['tipo_afiliacion']] ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Tipo de Operaci&oacute;n:</td>
		<td><?php echo $subtipos[$this->data['Afiliacion']['tipo_afiliacion']][$this->data['Afiliacion']['tipo_operacion']]; ?></td>
	</tr>
        <?php
	if( $this->data['Afiliacion']['tipo_afiliacion'] == 'r' || $this->data['Afiliacion']['tipo_afiliacion'] == 's' ){
	?>
	<tr>
		<td class="etiqueta">Tipo de Servicio:</td>
		<td><?php echo $servicios[$this->data['Afiliacion']['tipo_afiliacion']][$this->data['Afiliacion']['tipo_servicio']] ?></td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td class="etiqueta">Alias:</td>
		<td><?php echo ($this->data['Afiliacion']['alias']!="") ? $this->data['Afiliacion']['alias'] : 'Alias no asignado' ?></td>
	</tr>
	<?php if($this->data['Afiliacion']['tipo_afiliacion'] == 'c' && $this->data['Afiliacion']['tipo_operacion'] == 'cea'){ ?>
		<tr>
			<td class="etiqueta">N&uacute;mero de Contrato:</td>
			<td><?php echo $this->data['Afiliacion']['numero_contrato']; ?></td>
		</tr>
	<?php } elseif($this->data['Afiliacion']['tipo_afiliacion'] == 'c' && $this->data['Afiliacion']['tipo_operacion'] == 'ce'){	?>
		<tr>
			<td class="etiqueta">C&eacute;dula/RIF del Contrato:</td>
			<td><?php echo $this->data['Afiliacion']['tipo_cedula']. " ".$this->data['Afiliacion']['cedula']; ?></td>
		</tr>
	<?php }	?>
        <?php
	if( $this->data['Afiliacion']['tipo_afiliacion'] != 'c' && $this->data['Afiliacion']['tipo_afiliacion'] != 'i' && $this->data['Afiliacion']['tipo_afiliacion'] != 'h'){
	?>
	<tr>
		<td class="etiqueta">
			<?php if($this->data['Afiliacion']['tipo_afiliacion']=='s' || $this->data['Afiliacion']['tipo_afiliacion']=='r'){  
                   if($this->data['Afiliacion']['tipo_operacion'] == 'sd'){
					   echo 'Número de Línea:'; 
				   }else if($this->data['Afiliacion']['tipo_operacion'] == 'sv'){
                       echo 'Número de Tarjeta de Acceso:'; 

				   }else{	
				   	   echo 'Número de Servicio o Cuenta Contrato:'; 
				   }
				} else{ echo $this->data['Afiliacion']['tipo_afiliacion']=='t' ? 'Cuenta del Beneficiario:':'Tarjeta de Crédito del Beneficiario:'; 
			} ?>
		</td>

		<td><?php
                        if ($this->data['Afiliacion']['tipo_servicio'] == 'rtm' || $this->data['Afiliacion']['tipo_servicio'] == 'rtf' || $this->data['Afiliacion']['tipo_servicio'] == 'stm' || $this->data['Afiliacion']['tipo_servicio'] == 'stf' || $this->data['Afiliacion']['tipo_servicio'] == 'stv' ){
                            echo $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['numero_cuenta'];
                        }else{
                            echo $this->data['Afiliacion']['numero_cuenta'];
                        }                        
                    ?>
                </td>
	</tr>
        <?php
        }
        ?>
	<?php
	$banco = trim($this->data['Afiliacion']['banco']);
	if($this->data['Afiliacion']['tipo_afiliacion'] == 't' && $banco !='') {
        

	?>
	<tr>
		<td class="etiqueta">Banco del Beneficiario:</td>
		<td><?php echo $banco; ?></td>
	</tr>
	<?php
	}
	if($this->data['Afiliacion']['tipo_afiliacion'] != 'c'){
	?>	
	<tr>
		<td class="etiqueta">Nombre del Beneficiario:</td>
		<td><?php if(empty($this->data['Afiliacion']['nombre_beneficiario'])){
			echo "";
		}else{
			echo $this->data['Afiliacion']['nombre_beneficiario'];  
		} ?></td>
	</tr>
	<?php
	}
	if($this->data['Afiliacion']['tipo_afiliacion'] != 'c' && $this->data['Afiliacion']['tipo_afiliacion'] != 's' && $this->data['Afiliacion']['tipo_afiliacion'] != 'r' ){?>
	<tr>
                <?php
		if( $this->data['Afiliacion']['tipo_afiliacion'] == 'i'){
                ?> 
                <td class="etiqueta">RIF del Beneficiario:</td>
                <?php
                }else{
                ?>
		<td class="etiqueta">C&eacute;dula/RIF del Beneficiario:</td>
                 <?php
                }
                ?>
		<td>
                    <?php                             
                        //echo $this->data['Afiliacion']['tipo_cedula'] . "-" . $this->data['Afiliacion']['cedula']; 
                    	echo $this->data['Afiliacion']['cedula']; 
                    ?>
                </td>
	</tr>
	<?php if($this->data['Afiliacion']['tipo_afiliacion'] != 'c' && ($this->data['Afiliacion']['tipo_afiliacion'] == 'p' || $this->data['Afiliacion']['tipo_afiliacion'] == 't' || $this->data['Afiliacion']['tipo_afiliacion'] != 'r') && $this->data['Afiliacion']['tipo_operacion'] != 'ob'  ){ ?>
	<tr>
		<td class="etiqueta">E-mail del Beneficiario:</td>
		<td><?php echo $this->data['Afiliacion']['email'] ?></td>
	</tr>
	<?php } 
	}?>
	
	<?php if(empty($juridico) && $this->data['Afiliacion']['tipo_afiliacion'] != 'i' && $this->data['Afiliacion']['tipo_afiliacion'] != 'h'){ ?>
	<tr>
		<td class="etiqueta">Monto m&aacute;ximo:</td>
		<td>
                <?php
		if(empty($this->data['Afiliacion']['monto_maximo'])){
			echo 'Sin límite';
		} else {
			echo $this->data['Afiliacion']['monto_maximo'];
		}
		?>
                </td>
	</tr>
	<?php } ?>
	
	<tr>
		<td colspan="2" class="botonera">

		<?php 
		echo $html->link(
			'Volver',
			'/afiliaciones/listado/v',
			array(
				'class'=>'button'
			)
		);
		
		echo '&nbsp;&nbsp;';
		
		echo $html->link(
			'Aceptar',
			'/afiliaciones/eliminar_resultado',
			array(
				'class'=>'button'
			)
		);
		?>

		</td>
	</table>
</div>




