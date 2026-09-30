<?php
/**
 * Resultado de operación eliminar registro en el directorio
**/
?>
<div>
<h2 class="user">Directorio</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
        <?php if($error==0){ ?>
            <thead>
                <tr>
                <td colspan="2" class="resumen_paginacion">Operaci&oacute;n realizada satisfactoriamente</td>
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
        <?php
	}
	else{
	?>
            <thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('operacion.fallida'); ?></div></td>
		</tr>
            </thead>
	<?php 
	}
	?>
            <tr>
                <td colspan="2" class="botonera">
                    <?php 		
                        echo $html->link(
                                'Volver',
                                '/pago_activo/consulta_directorio',
                                array(
                                    'class'=>'button',
                                    'id'=>'volver'
                                )
                        );
                    ?>
		</td>
            </tr>
	</table>    
</div>

