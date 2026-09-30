<?php
/**
 * Confirmación de operación crear registro del directorio
**/
?>
<div>
<h2 class="user">Directorio</h2>
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
	<td class="etiqueta">Nombre del Beneficiario:</td>
	<td><?php  echo $this->data['Directorio']['nombreBeneficiario'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Banco del Beneficiario:</td>       
        <td><?php echo $bancos[$this->data['Directorio']['banco_receptor']] ?></td>       
	</tr>
	<tr>
	<td class="etiqueta">Número Celular Beneficiario:</td>        
        <td><?php echo $this->data['Directorio']['codigo_telefono'].$this->data['Directorio']['telefono'] ?></td>        
	</tr>	
	<tr>
	<td class="etiqueta">Cédula / RIF del Beneficiario:</td>       
        <td><?php echo $this->data['Directorio']['tipoCedulaRif'].'-'.$this->data['Directorio']['nroCedulaRif']  ?></td>       	
	</tr>        
	<td colspan="2" class="botonera">

	<?php 
	
        echo $html->link(
                'Volver',
                '/pago_activo/directorio_crear',
                array(
                    'class'=>'button bloqueo_repetir'
                )
        );
	
	echo '&nbsp;&nbsp;';
               
        echo $html->link(
        'Aceptar',
        '/pago_activo/directorio_crear_resultado',
        array(
            'class'=>'button bloqueo_repetir'
        )
        );        

	?>
	</td>
	</table>    
</div>

