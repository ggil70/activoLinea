<?php
    /**
     * Vista para el resultado de la eliminación de las afiliaciones Pago Activo
     */
?>
<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
    <?php if( !$this->data['Error'] ){ ?>		
            <thead>
            <tr>
            <td colspan="2" class="resumen_paginacion exito">Operaci&oacute;n realizada satisfactoriamente</td>
            </tr>
            </thead>				
            <tr>
                    <td class="etiqueta">Tipo de Afiliaci&oacute;n:</td>
                    <td><?php echo $tipos[$this->data['Afiliacion']['tipo_afiliacion']] ?></td>
            </tr>
            <tr>
                    <td class="etiqueta">Tipo de Operaci&oacute;n:</td>
                    <td>Desafiliación</td>
            </tr>               

            <tr>
                    <td class="etiqueta">Nro. Celular Afiliado:</td>
                    <td><?php echo $this->data['Afiliacion']['codigo_telefono'].$this->data['Afiliacion']['telefono'] ?></td>		
            </tr>

            <tr>
                    <td class="etiqueta">E-mail:</td>
                    <td><?php echo $this->data['Afiliacion']['email'] ?></td>
            </tr>	

            <tr>
                    <td class="etiqueta">Canal Seleccionado:</td>
                    <td><?php echo $canal[trim($this->data['Afiliacion']['tipo_servicio'])] ?></td>
            </tr>

            <?php if(!empty($this->data['Afiliacion']['numero_cuenta'])) {?>
            <tr>
                    <td class="etiqueta">Cuenta Corriente:</td>
                    <td><?php echo enmascara($this->data['Afiliacion']['numero_cuenta']) ?></td>
            </tr>
            <?php } ?>

            <?php if(!empty($this->data['Afiliacion']['numero_cuenta_ahorro'])) {?>
            <tr>
                    <td class="etiqueta">Cuenta Ahorro:</td>
                    <td><?php echo enmascara($this->data['Afiliacion']['numero_cuenta_ahorro']) ?></td>
            </tr>
            <?php } ?>

            <?php if(isset($this->data['Afiliacion']['numero_cuenta_receptora']) && !empty($this->data['Afiliacion']['numero_cuenta_receptora'])) {?>
            <tr>
                    <td class="etiqueta">Cuenta Receptora:</td>
                    <td><?php echo enmascara($this->data['Afiliacion']['numero_cuenta_receptora'] ) ?></td>
            </tr>
            <?php } if(!empty($this->data['Afiliacion']['numero_contrato'])) { ?>
            <tr>
                <td class="etiqueta">Número de Afiliación:</td>
                <td><?php echo $this->data['Afiliacion']['numero_contrato'] ?></td>
            </tr>
            <?php } 
                if(empty($juridico)){
            ?>         
            <tr>
                <td class="etiqueta">Monto Máximo Diario Bs.:</td>
                <td><?php echo $this->data['Afiliacion']['monto_maximo'] ?></td>
            </tr>
            <?php } ?>
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
    </table>         
    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td colspan="2" class="botonera">
            <?php 		
                echo $html->link(
                    'Volver',
                    '/pago_activo/listado_pago_activo/v',
                    array(
                        'class'=>'button'
                    )
                );
            ?>
            </td>
        </tr>
    </table>
</div>




