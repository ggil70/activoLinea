<?php
/**
 * Formulario de listado operaciones credito inmediato
**/
?>
<div>
<h2 class="user">Operaciones realizadas por crédito inmediato</h2>
<div class="line_title_orange"></div>
</div>
<?php
echo $form->create('Transferencia',
	array(
            'url'=> array(
                'controller'=>'transferencias',
                'action'=>'listado_simf'
            )
	)
);
?>

<table class="tabla" cellpadding="0" cellspacing="0" style="margin: 0 auto" border="0" width="90%">
<thead>
<tr>    
    <td>Cta. Origen</td>
    <td>Cta. Destino</td>
    <td>Fecha</td>
    <td>Referencia</td>    
    <td>Moneda</td>
    <td>Monto</td>       
    <td>Estatus</td>
    <td>Motivo Rechazo</td>
</tr>
</thead>

<?php 
    foreach($listado as $l){
?>
<tr>
    <td class="texto_centro"><?php echo $this->Activo->enmascara($l['TransaccionSimf']['cuenta_origen'])?></td>
    <td class="texto_centro"><?php echo $this->Activo->enmascara($l['TransaccionSimf']['cuenta_destino'])?></td>
    <td class="texto_centro" width = "18%"><?php echo date('d/m/Y h:i:s a', strtotime($l['TransaccionSimf']['fecha_hora_registro']))?></td>
    <td class="texto_centro"><?php echo $l['TransaccionSimf']['referencia_banco'] ?></td>    
    <td class="texto_centro"><?php echo $l['TransaccionSimf']['moneda'] ?></td>
    <td class="texto_centro"><?php echo number_format($l['TransaccionSimf']['monto'],2,',','.') ?></td>      
    <td class="texto_centro"><?php echo TransaccionSimf::$estatus_transaccion[trim($l['TransaccionSimf']['estatus'])]; ?></td>
    <?php 
        if(trim($l['TransaccionSimf']['estatus'])=='AC'){
    ?>
        <td class="texto_centro"></td>
    <?php }else{ ?>
        <td class="texto_centro"><?php echo (TransaccionSimf::obtenerMotivo($l['TransaccionSimf']['codigo_respuesta'])!='') ? TransaccionSimf::obtenerMotivo($l['TransaccionSimf']['codigo_respuesta']) : $l['TransaccionSimf']['descripcion_respuesta']; ?></td>
    <?php } ?>
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
	'Transferencia',
	array(
            'url'=> array(
                'controller'=>'transferencias',
                'action'=>$this->action
            ),
            'id' => 'paginacion'
	)
);
if(!empty($this->data['Transferencia'])){
    foreach($this->data['Transferencia'] as $k => $v) {
        echo $form->hidden($k);
    }
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>