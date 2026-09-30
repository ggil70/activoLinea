<?php
/**
 * Formulario de listado operaciones mesa de cambio
**/
?>
<div>
<h2 class="user">Mis Solicitudes</h2>
<div class="line_title_orange"></div>
</div>
<?php
echo $form->create('MesaCambio',
	array(
            'url'=> array(
                'controller'=>'mesa_cambio',
                'action'=>'listado'
            )
	)
);
?>

<table class="tabla" cellpadding="0" cellspacing="0" style="margin: 0 auto" border="0" width="90%">
<thead>
<tr>    
    <td>Cta. Origen</td>
    <td>Fecha</td>
    <td>Referencia</td>
    <td>Tipo</td>    
    <td>Moneda</td>
    <td>Monto</td>
    <td>Tasa Efectiva</td>    
    <td>Estatus</td>
</tr>
</thead>

<?php 
    foreach($listado as $l){
?>
<tr>
    <td class="texto_centro"><?php echo $this->Activo->enmascara(desencriptar($l['TransaccionMesaCambio']['cuenta_origen']))?></td>
    <td class="texto_centro" width = "18%"><?php echo date('d/m/Y h:i:s a', strtotime($l['TransaccionMesaCambio']['fecha_hora_registro']))?></td>
    <td class="texto_centro"><?php echo $l['TransaccionMesaCambio']['referencia'] ?></td>
    <td class="texto_centro"><?php echo TransaccionMesaCambio::$tipo_transaccion[$l['TransaccionMesaCambio']['tipo']]; ?></td>
    <td class="texto_centro"><?php echo $l['TransaccionMesaCambio']['moneda'] ?></td>
    <?php if($l['TransaccionMesaCambio']['monto_adjudicado']==null){?>

         <td class="texto_centro"><?php echo number_format($l['TransaccionMesaCambio']['monto_divisa'],2,',','.') ?></td>

    <?php }else{ ?>
         <td class="texto_centro"><?php echo number_format($l['TransaccionMesaCambio']['monto_adjudicado'],2,',','.') ?></td>

    <?php } ?>
   
    <td class="texto_centro"><?php echo number_format($l['TransaccionMesaCambio']['tasa'],2,',','.') ?></td>    
    <td class="texto_centro"><?php echo TransaccionMesaCambio::$estatus_transaccion[$l['TransaccionMesaCambio']['estatus']]; ?></td>
</tr>		
<?php } ?>

<tr>
    <td colspan="7" class="paginador">
          <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros)); ?>
    </td>
</tr>
</table>
<?php	
echo $form->create(
	'MesaCambio',
	array(
            'url'=> array(
                'controller'=>'mesa_cambio',
                'action'=>$this->action
            ),
            'id' => 'paginacion'
	)
);
if(!empty($this->data['MesaCambio'])){
    foreach($this->data['MesaCambio'] as $k => $v) {
        echo $form->hidden($k);
    }
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>