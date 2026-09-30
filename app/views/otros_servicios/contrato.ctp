<?php
/**
 * Despliegue de contrato de uso de banco activo
**/
?>
<div>
<h2 class="user">Contrato</h2>
<div class="line_title_orange"></div>

<?php 
    if($this->Session->check('login.juridico')){
        echo $this->element('contrato_empresa');
    }else{
        echo $this->element('contrato');
    }
?>

<div class="botonera">
<?php

echo '&nbsp;&nbsp;';

if($this->Session->check('login.juridico')){
            echo $html->link(
                'Imprimir',
                '/files/CONTRATO_ACTIVO_EN_LINEA_EMPRESA.pdf',
                array(
                        'class'=>'button',
                        'target'=>'_blank'
                )
            );
}else{
            echo $html->link(
                'Imprimir',
                '/files/CONTRATO_ACTIVO_EN_LINEA.pdf',
                array(
                        'class'=>'button',
                        'target'=>'_blank'
                )
            );
}
?>
</div>

</div>