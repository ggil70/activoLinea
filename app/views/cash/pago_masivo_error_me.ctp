<?php
    /**
     *  Vista para la mostrar el listado de errores encontrados en la carga masiva de Pago de Nómina/Proveedores
     */
?>

<div>
<h2 class="user"><?php echo $titulo; ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Selecci&oacute;n Archivo</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Listado Errores</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

    <div class="espacio_vertical_10"></div>

	<table class="tabla" cellpadding="0" cellspacing="0" border="0">
            <thead>
            <tr>
                <td colspan="3" class="resumen_paginacion">
                    A continuaci&oacute;n se presentan los errores detectados en el archivo: <b><?php echo $estado['archivo']['name']; ?>
                </td>
            </tr>
            <tr>
                <td>Registro</td>
                <td>Identificación</td>
                <td>Error</td>
                </thead>
                <tbody>
                    <?php foreach ($estado['errores'] as $e){ ?>
                    <tr>
			<td><?php echo $e['registro'] ?></td>
			<td><?php echo $e['identificacion'] ?></td>
			<td><?php echo $e['error'] ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3" class="botonera">
                        <?php

                            echo $html->link(
                                'Volver',
                                'pago_masivo_me/'.$estado['tipo'],
                                array(
                                    'class'=>'button'
                                )
                            );

                            echo '&nbsp;&nbsp;';
	
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
                                'descarga_error_masivo',
                                array(
                                    'class'=>'button'
                                )
                            );

                        ?>
                        </td>
                    <tr/>    
                </tbody>
            </table>
        
        <div>
            <p align="left"><b>NOTA:</b><br/>
                Estimado Cliente, por favor realice la corrección de los errores que presenta el archivo y vuelva a subir el mismo.
             </p>
        </div>
</div>
