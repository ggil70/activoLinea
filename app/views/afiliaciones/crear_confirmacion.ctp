<?php
    /**
     * Vista para la confirmación afiliaciones
     */

/*echo "Tipo Afil: " . $this->data['Afiliacion']['tipo_afiliacion'];
echo "<br>Tipo Oper: " . $this->data['Afiliacion']['tipo_operacion'];
die("<br>Banco: " . $this->data['Afiliacion']['banco']);
*/
 



?>

<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
		<td class="etiqueta">Tipo de Afiliaci&oacute;n:</td>
		<td><?php echo $tipos[$this->data['Afiliacion']['tipo_afiliacion']] ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Tipo de Operaci&oacute;n:</td>
		<td><?php echo $subtipos[$this->data['Afiliacion']['tipo_afiliacion']][$this->data['Afiliacion']['tipo_operacion']] ?></td>
	</tr>
        
    <?php
		if( $this->data['Afiliacion']['tipo_afiliacion'] == 'r' || $this->data['Afiliacion']['tipo_afiliacion'] == 's' ){

			if($this->data['Afiliacion']['tipo_servicio']!=''){
		?>
				<tr>
					<td class="etiqueta">Tipo de Servicio: </td>
					<td><?php echo $servicios[$this->data['Afiliacion']['tipo_afiliacion']][$this->data['Afiliacion']['tipo_servicio']] ?></td>
				</tr>
	<?php
			}
		}
	?>
        
	<tr>
		<td class="etiqueta">Alias:</td>
		<td><?php echo ($this->data['Afiliacion']['alias']!="") ? $this->data['Afiliacion']['alias'] : 'Alias no asignado' ?></td>
	</tr>
	<?php
    	$banco = trim($this->data['Afiliacion']['banco']);
		if($this->data['Afiliacion']['tipo_afiliacion'] == 't' && $banco != ''){
	?>
			<tr>
				<td class="etiqueta">Banco del Beneficiario:</td>
				<td><?php echo $banco; ?></td>
			</tr>
	<?php
		}
	?>

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

	<?php if($this->data['Afiliacion']['tipo_afiliacion'] == 'p'){ ?>
		<tr>
			<td class="etiqueta">N&uacute;mero de Tarjeta:</td>
			<td><?php echo $this->data['Afiliacion']['numero_cuenta']; ?></td>
		</tr>
	<?php }	?>

	
	<?php
	if($this->data['Afiliacion']['tipo_afiliacion'] != 'c' && $this->data['Afiliacion']['tipo_afiliacion'] != 'i' && $this->data['Afiliacion']['tipo_afiliacion'] != 'h' && $this->data['Afiliacion']['tipo_afiliacion'] != 'p' ){

	?>
        <tr>
		<td class="etiqueta">
			<?php 
            if($this->data['Afiliacion']['tipo_afiliacion']=='t'){
            	echo 'N&uacute;mero de Cuenta:';
            }else{ 
				if($this->data['Afiliacion']['tipo_afiliacion']=='s' || $this->data['Afiliacion']['tipo_afiliacion']=='r'){ 
					if($this->data['Afiliacion']['tipo_operacion']=='sd'){ 
						echo 'N&uacute;mero de Línea:'; 
                    }else if($this->data['Afiliacion']['tipo_operacion']=='sm' && $this->data['Afiliacion']['tipo_afiliacion']=='s'){ 
						echo 'N&uacute;mero de Contrato:';   
                    }else if($this->data['Afiliacion']['tipo_operacion']=='sm'){ 
						echo 'N&uacute;mero de Línea:';   

					}else if ($this->data['Afiliacion']['tipo_operacion']=='sv'){
						echo 'N&uacute;mero de Tarjeta de Acceso:'; 

					} else{  echo ( $this->data['Afiliacion']['tipo_afiliacion'] == 't' ) ? 'Cuenta' : 'Tarjeta 
						de Cr&eacute;dito' ?> del Beneficiario: <?php 
				    }
				}    
			}	
			?>
			    	
		</td>
		<td><?php
			if( $this->data['Afiliacion']['tipo_afiliacion'] == 't'){
				echo str_pad( $this->data['Afiliacion']['numero_cuenta'], 20, "010", STR_PAD_LEFT);
			} else if ($this->data['Afiliacion']['tipo_afiliacion'] == 't' && in_array($this->data['Afiliacion']['tipo_operacion'],array('ob','tob'))){
                if($this->data['Afiliacion']['destino']=='c'){
                    echo $this->data['Afiliacion']['numero_cuenta'];
                }else{
                    echo $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['telefono'];
                }
            }else if($this->data['Afiliacion']['tipo_afiliacion'] == 's' && $this->data['Afiliacion']['tipo_servicio'] == 'stm'){
            	echo $this->data['Afiliacion']['numero_cuenta'];
            } else if ($this->data['Afiliacion']['tipo_servicio'] == 'rtm' || $this->data['Afiliacion']['tipo_servicio'] == 'rtf' || $this->data['Afiliacion']['tipo_servicio'] == 'stm' || $this->data['Afiliacion']['tipo_servicio'] == 'stf' || $this->data['Afiliacion']['tipo_servicio'] == 'stv'){
                   echo $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['numero_cuenta'];
            } else{
				echo $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['numero_cuenta'];
			}
		?></td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td class="etiqueta">Nombre del Beneficiario:</td>
		<td><?php 
				echo $this->data['Afiliacion']['nombre_beneficiario'];  
			?>
		 </td>
		
	</tr>
	<?php
		if($this->data['Afiliacion']['tipo_afiliacion'] != 'c' && $this->data['Afiliacion']['tipo_afiliacion'] != 's' && $this->data['Afiliacion']['tipo_afiliacion'] != 'r'){?>
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
                    echo $this->data['Afiliacion']['tipo_cedula'] . "-" . $this->data['Afiliacion']['cedula']; 
                    ?>
                </td>
	</tr>
	<?php } ?>

	<?php
	if($this->data['Afiliacion']['tipo_afiliacion'] == 'h'){
	?>
        <tr>
		<td class="etiqueta">
			<?php 
			echo 'N&uacute;mero Tarjeta Prepago:'; 
			?>
			    	
		</td>
		<td><?php
            echo $this->data['Afiliacion']['numTarjeta'];
		?></td>
	</tr>
	<?php
	}
	?>




	
	<?php if($this->data['Afiliacion']['tipo_afiliacion'] != 'c' && $this->data['Afiliacion']['tipo_operacion'] != 'ob' && $this->data['Afiliacion']['tipo_afiliacion'] != 's' && $this->data['Afiliacion']['tipo_afiliacion'] != 'r'){ ?>
	<tr>
		<td class="etiqueta">E-mail del Beneficiario:</td>
		<td><?php echo $this->data['Afiliacion']['email'] ?></td>
	</tr>
	<?php } ?>
	
	<?php 
    if($this->data['Afiliacion']['tipo_afiliacion'] != 'h'){  	
		if(empty($juridico) && $this->data['Afiliacion']['tipo_afiliacion'] != 'i'){ ?>
	<tr>
		<td class="etiqueta">Monto m&aacute;ximo:</td>
		<td><?php
		if(empty($this->data['Afiliacion']['monto_maximo'])){
			echo 'Sin límite';
		} else {
			echo $this->data['Afiliacion']['monto_maximo'];
		}
		?></td>
	</tr>
	<?php }
	}
	?>
	<tr>
		<td colspan="2" class="botonera">

		<?php 
		echo $html->link(
			'Volver',
			'/afiliaciones/crear/v',
			array(
				'class'=>'button',
				'id'=>'volver'
			)
		);
		
		echo '&nbsp;&nbsp;&nbsp;';
		
		echo $html->link(
			'Aceptar',
			'/afiliaciones/crear_resultado',
			array(
				'class'=>'button bloqueo_repetir'
			)
		); 
		

		?>

		</td>
	</table>
</div>


