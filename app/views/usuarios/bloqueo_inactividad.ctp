<?php
/**
 * Vista que despliega mensaje de usuario bloqueado por inactividad
**/
?>

<div>
<h2 class="user"><?php echo $estado['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
	if($valida){
            if(!$this->Session->check('login.juridico')){
                $class = ($i==2) ? '_actual' : '';
            }else{
                $class = ($i==1) ? '_actual' : '';
            }
        }else{
            $class = '';
        }    
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p class="mensaje">
<?php 
if($valida){
    echo _m('acceso.actualizado'); 
}else{
    echo _m('usuario.bloqueo.inactividad');
}
?>
</p>

<div class="botonera">
<?php
if($valida){
    echo $html->link(
	'Aceptar',
	'/',
	array(
		'class'=>'button'
	)
    );
}else{
    if(!$this->Session->check('login.juridico')){
        echo $html->link(
            'Continuar',
            '/seguridad/validar_preguntas_autenticacion',
            array(
                    'class'=>'button'
            )
        );
    }else{
        echo $html->link(
            'Continuar',
            '/seguridad/validar_preguntas_desafio',
            array(
                    'class'=>'button'
            )
        );
    }
}

?>
</div>

</div>
