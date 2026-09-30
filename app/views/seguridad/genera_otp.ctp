<?php
/**
 * Formulario de generación de otp
**/

?>
<div>
<h2 class="user"><?php echo $estado['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
	$class = ($i==$estado['paso_actual']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<?php

echo $form->create('Otp',
	array(
		'url'=> array(
				'controller'=>'seguridad',
				'action'=>'genera_otp'
			)
	)
);
?>

<table cellpadding="0" cellspacing="0" border="0" class="formulario" width="650px" align="center">
<tr>
        <div class="espacio_vertical_20"></div>        
	<td class="mensaje">
	Estimado Cliente, por favor consulte en su celular y/o correo electrónico, el mensaje de Banco Activo indicando el Código de Autorización que debe ingresar para autorizar la operación.
	</td>                                
</tr>
<tr>
    <td class="mensaje">
            <strong>Recuerde el código enviado vencerá a los (3) tres minutos.</strong>
    </td>
</tr>
<tr><td>&nbsp;</td></tr>

<tr>
    <td class="submit" align="center">
    <?php                
        echo $html->link(
			'Volver',
			$estado['url_volver'],
			array(
				'class'=>'button'
			)
		);        

        echo '&nbsp;&nbsp;';
        
        echo $html->link(
			'Aceptar',
			'valida_otp',
			array(
				'class'=>'button'
			)
		);
    
    ?>
    </td>
</tr>

</table>

<?php	echo $form->end(); ?>
</div>

