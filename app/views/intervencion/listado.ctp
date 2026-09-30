<?php
/**
 * Formulario de listado operaciones intervencion
**/
?>
<div>
<h2 class="user">Mis Solicitudes</h2>
<div class="line_title_orange"></div>
</div>
<?php
echo $form->create('Intervencion',
	array(
            'url'=> array(
                'controller'=>'intervencion',
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
    <td>Tasa Compra</td>
    <td>Estatus</td>
</tr>
</thead>

<?php 
    foreach($listado as $l){
?>
<tr>
    <td class="texto_centro"><?php echo $this->Activo->enmascara(desencriptar($l['TransaccionIntervencion']['cuenta_origen']))?></td>
    <td class="texto_centro" width = "18%"><?php echo date('d/m/Y h:i:s a', strtotime($l['TransaccionIntervencion']['fecha_hora_registro']))?></td>
    <td class="texto_centro"><?php echo $l['TransaccionIntervencion']['referencia'] ?></td>
    <td class="texto_centro"><?php echo TransaccionIntervencion::$tipo_transaccion[$l['TransaccionIntervencion']['tipo']]; ?></td>
    <td class="texto_centro"><?php echo $l['TransaccionIntervencion']['moneda'] ?></td>
    <td class="texto_centro"><?php echo number_format($l['TransaccionIntervencion']['monto_divisa'],2,',','.') ?></td>
    <td class="texto_centro"><?php echo number_format($l['TransaccionIntervencion']['monto']/$l['TransaccionIntervencion']['monto_divisa'],2,',','.') ?></td>    
    <td class="texto_centro"><?php echo TransaccionIntervencion::$estatus_transaccion[$l['TransaccionIntervencion']['estatus']]; ?></td>
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
	'Intervencion',
	array(
            'url'=> array(
                'controller'=>'intervencion',
                'action'=>$this->action
            ),
            'id' => 'paginacion'
	)
);
if(!empty($this->data['Intervencion'])){
    foreach($this->data['Intervencion'] as $k => $v) {
        echo $form->hidden($k);
    }
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>