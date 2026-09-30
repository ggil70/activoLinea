<?php
    /**
     *  Vista para la confirmación al procesar un Pago de Nómina/Proveedores
     */
?>

    <div>
        <h2 class="user"><?php echo $titulo ?></h2>
        <div class="line_title_orange"></div>
    </div>

    <div class="paso_nro">1</div>
    <div class="paso_desc">Solicitud de Informaci&oacute;n</div>
    <div class="paso_nro_actual">2</div>
    <div class="paso_desc_actual">Confirmaci&oacute;n</div>
    <?php if($usuario['rol_juridico_id']==2){ ?>
    <div class="paso_nro">3</div>
    <div class="paso_desc">Tarjeta de Coordenadas</div>
    <div class="paso_nro">4</div>
    <?php } else { ?>
    <div class="paso_nro">3</div>
    <?php }  ?>
    <div class="paso_desc">Resultado Operaci&oacute;n</div>

    <div class="espacio_vertical_20"></div>

    <div class="contenedor_formulario" style="width:790px;">

	<?php

	echo $html->tag(
            'table',
            null,
            array(
                'class'=>'confirmacion',
                'cellpadding'=>'0',
                'cellspacing'=>'0',
                'border'=>'0',
                'align'=>'center'
            )
	);
	
	?>
	<thead>
            <tr><td class="resumen_paginacion" colspan="2">A continuaci&oacute;n proceda a confirmar los datos del pago que desea procesar.</td></tr>
	</thead>
            <tr>
                <td class="etiqueta">Tipo Operación:</td>
                <td><?php switch($estado['tipo']){
                    case 'm':
			echo 'Pago Nómina Moneda Extranjera';
                    break;
                    case 'x':
			echo 'Pago Proveedores Moneda Extranjera';
                    break;
		}?></td>
		</tr>
		<tr>
                    <td class="etiqueta">Usuario Registro:</td>
                    <td><?php echo desencriptar($usuario['nombre']) ?></td>
		</tr>
		<tr>
                    <td class="etiqueta">Cuenta Débito:</td>
                    <td><?php echo $activo->enmascara($this->data['PagoCash']['cuentaDebitar']) ?></td>
		</tr>
		<tr>
                    <td class="etiqueta">Fecha Valor:</td>
                    <td><?php echo $this->data['PagoCash']['fecha_valor']['day'].'/'.$this->data['PagoCash']['fecha_valor']['month'].'/'.$this->data['PagoCash']['fecha_valor']['year'] ?></td>
		</tr>
		<tr>
                    <td class="etiqueta">Descripción:</td>
                    <td><?php echo $this->data['PagoCash']['descripcion'] ?></td>
		</tr>
		<tr>
                    <td class="etiqueta">Número Lote:</td>
                    <td><?php echo $this->data['PagoCash']['lote'] ?></td>
		</tr>
		<tr>
                    <td class="etiqueta">Monto:</td>
                    <td><?php echo formatea_monto_fromWs($this->data['PagoCash']['monto_total']) ?></td>
		</tr>
	</table>
	
	<div class="espacio_vertical_20"></div>
	<div class="espacio_vertical_20"></div>
	

    <table class="tabla" cellpadding="0" cellspacing="0" border="0" align="center" style="width:100%;">
    <thead>
	<tr>
            <td>Identificación<br/> Beneficiario</td>
            <td style="width:160px;">Nombre <br/>Beneficiario</td>
            <td>Número Cuenta <br/>Beneficiario</td>
            <td>Banco <br/>Beneficiario</td>
            <td style="width:140px;">Monto.</td>
	</tr>
    </thead>
    <tbody>

    <?php
        foreach($this->data['Beneficiarios'] as $x){
            $b = desencriptarCampos($x, array('nombre','cedula','numero_cuenta','email','celular'));
    ?>
    <tr>
	<td><?php echo $b['tipo_cedula'].$b['cedula'] ?></td>
	<td><?php echo $b['nombre'] ?></td>
	<td class="texto_centro"><?php echo $b['numero_cuenta'] ?></td>
	<td><?php echo $bancos[substr($b['numero_cuenta'],0,4)] ?></td>
	<td class="monto"><?php echo formatea_monto_fromWs($b['monto']) ?></td>
    </tr>
    <?php }

    ?>
    <tr>
	<td class="botonera" colspan="5">	
            <?php 	
		
            echo $html->link(
                'Volver',
                'pagos_me/'.$estado['tipo'], 
		array(
                    'class'=>'button',
                    'id'=>'volver'
		)
            );
		
            echo '&nbsp;&nbsp;&nbsp;';
		
            echo $html->link(
		'Aceptar',
		'pago_procesar_resultado_me',
		array(
                    'class'=>'button'
		)
            );
		
            ?>

	</td>
    </tr>
    </tbody>
</table>
</div>
