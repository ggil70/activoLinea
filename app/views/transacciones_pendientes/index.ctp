<?php
/**
 * Formulario de selección multiple y simple de las transacciones pendientes
**/
?>
<div>
<h2 class="user">Aprobaciones Pendientes</h2>
<div class="line_title_orange"></div>
</div>

<?php if(!empty($agrupadas)) { ?>
	<div class="paso_nro_actual">1</div>
	<div class="paso_desc_actual">Resumen de Aprobaciones</div>
	<div class="paso_nro">2</div>
	<div class="paso_desc">Confirmaci&oacute;n</div>
	<div class="paso_nro">3</div>
	<?php if($usuario['rol_juridico_id'] != 3 && !$activo->tdc_validada()) { ?>
	<div class="paso_desc">Tarjeta de Coordenadas</div>
	<div class="paso_nro">4</div>
	<?php } ?>
	<div class="paso_desc">Resultado de la Operaci&oacute;n</div>
<?php } ?>

<div class="espacio_vertical_20"></div>

<?php

echo $form->create('TransaccionPendiente',
	array(
		'url'=> array(
			'controller'=>'transacciones_pendientes',
			'action'=>'index'
		)
	)
);

?>

<input type="hidden" name="data[accion]">

<?php if(!empty($agrupadas)) { ?>

<a id="error"></a>
<label for="data[seleccion][]" id="error_sel" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros'); ?></label>
<label id="aprobaciones_trans_propias" class="error manual"><?php echo _m('aprobaciones.trans.propias'); ?></label>
<label id="modificacion_trans_terceros" class="error manual"><?php echo _m('modificacion.trans.terceros'); ?></label>

<table class="tabla" cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
<thead>
<tr>
<td><input type="checkbox" id="chkTodos"></td>
<td colspan="7">Todas las Aprobaciones Pendientes</td>
</tr>
</thead>

<?php
foreach($agrupadas as $tipo => $transacciones){
?>

<tr><td colspan="7" style="border:0">&nbsp;</td></tr>
<thead>
<tr>
<td>
<?php if($tipo != 'i'){ ?>
    <input
	type="checkbox"
	class="todas_por_tipo"
	tipotran="<?php echo $tipo?>"
    >
<?php } ?>
</td>
<td colspan="8">
<?php switch($tipo){
	case 't':
		echo 'Transferencias';
		break;
    case 'd':
		echo 'Transferencias Moneda Extranjera';
		break;    
	case 'p':
		echo 'Pagos de Tarjetas de Crédito';
		break;
	case 'n':
		echo 'Pagos de Nómina';
		break;
	case 'v':
		echo 'Pagos a Proveedores';
		break;
	case 'm':
		echo 'Pagos de Nómina Moneda Extranjera';
		break;    
	case 'x':
		echo 'Pagos a Proveedores Moneda Extranjera';
		break;           
    case 'i':
        echo 'Pagos de Impuestos Nacionales/Aduanales';
        break;
	case 'c':
		echo 'Suspender Cheques o Chequeras';
		break;
	case 's':
		echo 'Pago Servicios';
		break;
	case 'r':
		echo 'Recarga Servicios';
		break;
    case 'e':
		echo 'Mesa de Cambio';
		break;
    case 'f':
        echo 'Intervención Cambiaria';
        break;
} ?>
</td>
</tr>

<tr>
<td>&nbsp;</td>
<td>Fecha Registro</td>
<?php if($tipo=='t' || $tipo=='d' || $tipo=='p' || $tipo=='c' || $tipo=='s' || $tipo=='r' || $tipo=='i' || $tipo=='e' || $tipo=='f') { ?>
<td>Referencia</td>
<td <?php if($tipo=='t' || $tipo=='d' || $tipo=='p' || $tipo=='s' || $tipo=='r' || $tipo=='i' || $tipo=='e' || $tipo=='f') echo 'colspan="2"'; else if($tipo=='c') echo 'colspan="3"'; ?> >Resumen</td>
<?php } else {?>
<td>Fecha Valor</td>
<td>Lote</td>
<td>Resumen</td>
<?php }?>
<?php if($tipo!='c' && $tipo!='e' && $tipo!='f') { ?>
<td>Monto </td>
<?php }?>
<?php if($tipo=='e' || $tipo=='f') { ?>
<td>Monto Orden</td>
<?php }?>
<td>Usuario Registro</td>
<td>Estatus</td>
</tr>

</thead>

<tbody>
<?php foreach($transacciones as $i){ ?>

<tr>
<td style="text-align:center">
	<input
		class="tipo_tran_<?php echo $tipo ?> usuario_id_<?php echo $i['TransaccionPendiente']['usuario_id'] ?>"
		type="checkbox"
                tipotran="<?php echo $tipo?>"
		name="data[seleccion][]"
		value="<?php echo $i['TransaccionPendiente']['id'] ?>"
		<?php if(isset($i['permiso_aprobacion']) && !$i['permiso_aprobacion']){ ?>
		noaprobar="<?php echo $i['permiso_aprobacion_motivo'] ?>"
		<?php } if($i['TransaccionPendiente']['usuario_id']==$usuario['id'] && count($i['AprobacionesRealizadas'])>0){ ?>
		nooperar="yaaprobada"
		<?php } if($tipo == 'i'){ ?>
		nomultiple="nomultiple"
                <?php } ?>		
		<?php if(
                        isset($this->data['seleccion']) &&
                        is_array($this->data['seleccion']) &&
                        in_array($i['TransaccionPendiente']['id'], $this->data['seleccion'])
		){
			echo 'checked="checked"';
		}
		?>
	/>

	<input type="hidden" value="<?php echo $i['TransaccionPendiente']['usuario_id'] ?>">
</td>
<td style="text-align:center"><?php echo date('d/m/Y',strtotime($i['TransaccionPendiente']['fecha_hora_registro'])) ?></td>

<?php if($tipo=='t' || $tipo=='d' || $tipo=='p' || $tipo=='c' || $tipo=='s' || $tipo=='r' || $tipo=='i' || $tipo=='e' || $tipo=='f') {?>
<td style="text-align:center">
	<a href="javascript:verDetalle(<?php echo $i['TransaccionPendiente']['id'] ?>, '<?php echo $tipo ?>')">
		<?php echo $i['TransaccionPendiente']['id'] ?>
	</a>
</td>
<?php } else { ?>
<td class="texto_centro"><?php echo date('d/m/Y',strtotime($i['TransaccionPendiente']['fecha_hora_valor'])) ?></td>
<td style="text-align:center">
	<a href="javascript:verDetalle(<?php echo $i['TransaccionPendiente']['id'] ?>, '<?php echo $tipo ?>')">
		<?php echo $i['TransaccionPendiente']['lote'] ?>
	</a>
</td>
<?php } ?>
<td <?php if($tipo=='t' || $tipo=='d' || $tipo=='p' || $tipo=='s' || $tipo=='r' || $tipo=='i' || $tipo=='e' || $tipo=='f' ) echo 'colspan="2"'; else if($tipo=='c') echo 'colspan="3"'; ?> >

		Cuenta Débito: <?php echo enmascara(desencriptar($i['TransaccionPendiente']['cuenta_origen'])); ?><br />

	<?php switch($tipo){
		case 't':
		case 'p':
                case 'i':
                        if(isset($i['TransaccionPendiente']['nombre_beneficiario'])){
				echo 'Beneficiario: '.desencriptar($i['TransaccionPendiente']['nombre_beneficiario']).'<br>';
				echo TransaccionPendiente::$tipo_transaccion[$i['TransaccionPendiente']['tipo']];
			}
			break;
		case 'r':
		case 's':
			if(isset($i['TransaccionPendiente']['nombre_beneficiario'])){
                                echo 'Número Servicio: '.desencriptar($i['TransaccionPendiente']['cuenta_destino']).'<br>';
				echo 'Beneficiario: '.desencriptar($i['TransaccionPendiente']['nombre_beneficiario']).'<br>';
				echo TransaccionPendiente::$tipo_transaccion[$i['TransaccionPendiente']['tipo']];
			}
			break;
		case 'n':
		case 'v':
			if(isset($i['TransaccionPendiente']['descripcion'])){
				echo 'Descripción: '.desencriptar($i['TransaccionPendiente']['descripcion']).'<br>';
			}
			echo 'Número de Registros: '.count($i['Beneficiarios']).'<br>';
			break;
		
		case 'c':
			
			switch ($i['TransaccionPendiente']['tipo']){
				case 'csi':
				case 'csx':
					$cheque = ' ' . desencriptar($i['TransaccionPendiente']['cuenta_destino']);
					break;
				case 'csr':
					list($from, $to) = explode('|', desencriptar($i['TransaccionPendiente']['cuenta_destino']));
					$cheque = ' desde ' . $from . ' hasta ' . $to;
					break;
				default:
					$cheque = '';
					break;
			}
			
			echo TransaccionPendiente::$tipo_transaccion[$i['TransaccionPendiente']['tipo']] . $cheque;
			break;
                case 'e':
                case 'f':
			if(isset($i['TransaccionPendiente']['descripcion'])){
				echo 'Descripción: '.desencriptar($i['TransaccionPendiente']['descripcion']).' '.$i['TransaccionPendiente']['moneda'].' '.$i['TransaccionPendiente']['nombre_producto'].'<br>';
			}                       
			break;  

		case 'b':
			if(isset($i['TransaccionPendiente']['descripcion'])){
				echo 'Descripción: '.desencriptar($i['TransaccionPendiente']['descripcion']).'<br>';
			}
			echo 'Número de Registros: '.count($i['Beneficiarios']).'<br>';
			break;





	} ?>
</td>

<?php if($tipo!='c' && $tipo!='e' && $tipo!='f') { ?>
<td class="monto"><?php echo formatea_monto_fromWs($i['TransaccionPendiente']['monto']) ?></td>
<?php }
if($tipo=='e' || $tipo=='f') {
    $valores = explode('-',$i['TransaccionPendiente']['email_beneficiario']);
?>
<td class="monto"><?php echo $valores[1] ?></td>
<?php } ?>
<td style="text-align:center"><?php if(empty($i['UsuarioCreador']['login'])) {
	echo desencriptar($i['UsuarioCreador']['ibuser']);
} else {
	echo desencriptar($i['UsuarioCreador']['login']);
}	?></td>
<td><h5><?php

if($i['TransaccionPendiente']['estatus']=='ra'){
	echo 'Rechazada';
} else {
	$aprobaciones_pendientes = $i['TransaccionPendiente']['aprobaciones_requeridas'];

	if(isset($i['TransaccionPendiente']['aprobaciones'])){
		$aprobaciones_pendientes -= $i['TransaccionPendiente']['aprobaciones'];
	}

	foreach($i['AprobacionesRealizadas'] as $ap){
		echo 'Aprobada por '.desencriptar($ap['Usuario']['nombre']).'<br>';
	}

	if($aprobaciones_pendientes==1){
		echo 'Falta 1 aprobación';
	} else {
		echo 'Faltan '.$aprobaciones_pendientes.' aprobaciones';
	}
}

?></h5></td>
</tr>

<?php 
}
 ?>

</tbody>

<?php } ?>

<tr>
<td colspan="8" class="botonera">
<?php 

$usuario = $this->Session->read('usuarios.datos');

if(in_array($usuario['rol_juridico_id'],array(4,5))){
	// El aprobador y el operador/aprobador pueden aprobar o rechazar
	
	if(validarTransaccion(54)){
		echo $html->link(
			'Aprobar',
			'javascript:',
			array(
				'class'=>'button boton_accion',
				'id'=>'aprobar'
			)
		);

		echo '&nbsp;&nbsp;&nbsp;';
	}
	
	if(validarTransaccion(55)){
		echo $html->link(
			'Rechazar',
			'javascript:',
			array(
				'class'=>'button boton_accion',
				'id'=>'rechazar'						
			)
		);
	}
}

if(in_array($usuario['rol_juridico_id'],array(3,5))){
	// El operador y el operador/aprobador pueden eliminar

	if(validarTransaccion(57)){
		echo '&nbsp;&nbsp;&nbsp;';

		echo $html->link(
			'Eliminar',
			'javascript:',
			array(
				'class'=>'button boton_accion',
				'id'=>'eliminar'						
			)
		);
	}
}


?>
</td>
</tr>

</table>

<?php } else { ?>

Estimado Cliente, por ahora no hay transacciones pendientes.

<?php } ?>

<?php echo $form->end() ?>

<form id="detalle_form" action="<?php echo Router::url('/transacciones_pendientes/detalle') ?>" method="post">
	<input type="hidden" name="data[TransaccionPendiente][id]">
</form>


<div class="basic-modal-content" id="ap_deshabilitada_limite">
	<h3>Aprobación Deshabilitada</h3>
	<p><?php echo(_m('permiso.aprobacion.limite')); ?></p>
</div>

<div class="basic-modal-content" id="ap_deshabilitada_nopermiso">
	<h3>Aprobación Deshabilitada</h3>
	<p><?php echo(_m('permiso.aprobacion.nopermiso')); ?></p>
</div>

<div class="basic-modal-content" id="ap_deshabilitada_yaaprobo">
	<h3>Aprobación Deshabilitada</h3>
	<p><?php echo(_m('permiso.aprobacion.yaaprobada')); ?></p>
</div>

<div class="basic-modal-content" id="ayuda_nop_yaaprobada">
	<h3>Modificación/Eliminación Deshabilitada</h3>
	<p><?php echo _m('permiso.modif.yaaprobada') ?></p>
</div>

<div class="basic-modal-content" id="ap_deshabilitada_multiple">
	<h3>Aprobación Múltiple Deshabilitada</h3>
	<p><?php echo(_m('permiso.aprobacion.multiple')); ?></p>
</div>

<div class="basic-modal-content" id="cierre_seniat">
        <?php 
        $horaApertura = _p('apertura.seniat');
        $horaCierre = _p('cierre.seniat'); 
        $parametros = array(cambiarFormatoHoraAMPM($horaApertura),cambiarFormatoHoraAMPM($horaCierre));
        ?>
	<h3>Pago Deshabilitado</h3>
	<p><?php echo _m('cierre.seniat', $parametros); ?></p>
</div>

<script>

function verDetalle(id, tipo){
	
        if (tipo == 'i'){
            var horaServidor = '<?php echo date("G:i"); ?>';
            var horaApertura= '<?php echo _p('apertura.seniat'); ?>';
            var horaCierre = '<?php echo _p('cierre.seniat'); ?>';

            if(toTime(horaServidor, "G:i") < toTime(horaApertura, "G:i") || toTime(horaServidor, "G:i") > toTime(horaCierre, "G:i")){
                $('#cierre_seniat').modal({minHeight:120,minWidth:500,close:true});
            }else{
                $('#detalle_form input').val(id);
                $('#detalle_form').submit();
            }
        }else{
             $('#detalle_form input').val(id);
             $('#detalle_form').submit();
        }
}

$(function(){

	$('#chkTodos').change(function(){
		if($(this).is(':checked')){
			$('input[type="checkbox"]:not([noaprobar]):not([nooperar]):not([nomultiple])').attr('checked','checked');
		} else {
			$('input[type="checkbox"]').removeAttr('checked');
		}
	});
	
	$('.todas_por_tipo').change(function(){
		var t = $(this).attr('tipotran');
		
		if($(this).is(':checked')){
			
			$('input[type="checkbox"].tipo_tran_'+t+':not([noaprobar]):not([nooperar])').attr('checked','checked');
		} else {
			$('input[type="checkbox"].tipo_tran_'+t).removeAttr('checked');
		}
	
	});
	
	$('input[type="checkbox"][noaprobar]').click(function(){
		$( '#ap_deshabilitada_' + $(this).attr('noaprobar') ).modal({minHeight:100,minWidth:400});
		return false;
	}).change(function(){
		return false;
	});
	
	$('input[type="checkbox"][nooperar]').click(function(){
		$('#ayuda_nop_'+$(this).attr('nooperar')).modal({minHeight:200,minWidth:400});
		return false;
	}).change(function(){
		return false;
	});
        
        $('input[type="checkbox"][nomultiple]').click(function(){
		if($(this).is(':checked')){
                    
                    var horaServidor = '<?php echo date("G:i"); ?>';
                    var horaApertura= '<?php echo _p('apertura.seniat'); ?>';
                    var horaCierre = '<?php echo _p('cierre.seniat'); ?>';

                    if(toTime(horaServidor, "G:i") < toTime(horaApertura, "G:i") || toTime(horaServidor, "G:i") > toTime(horaCierre, "G:i")){
                        $('#cierre_seniat').modal({minHeight:120,minWidth:500,close:true});
                        return false;
                    }else{
                       var transacciones = $('input[type="checkbox"][name="data[seleccion][]"]:checked');
                        if (transacciones.size() > 1){
                            $('#ap_deshabilitada_multiple').modal({minHeight:200,minWidth:400});
                            return false;
                        }
                    }        
                }
	});
        
	
	$('input[type="checkbox"]').click(function(){
                var tipo = $(this).attr('tipotran');
                if(tipo != 'i'){
                    var transacciones = $('input[type="checkbox"]:[nomultiple]:checked');
                    if (transacciones.size() > 0){
                        $('#ap_deshabilitada_multiple').modal({minHeight:200,minWidth:400});
                        return false;
                    }
                }
		$('.error.manual').hide();
	});
	
	$('#TransaccionPendienteIndexForm').validate({
		submitHandler:function(form){
			$.each($('tr td div.error-message'),function(){
				if($(this).siblings().size()==0){
					$(this).parent().parent().remove();
				}
			});
			
			var accion = $('input[name="data[accion]"]').val();
			
			$('.error.manual').hide();
			
			var continuar = false;
			
			if(accion == 'aprobar' || accion=='rechazar'){
				var transacciones = $('input[type="checkbox"][name="data[seleccion][]"]:checked');
				
				if(transacciones.size() > 0){
					
					transacciones = $('input[type="checkbox"][name="data[seleccion][]"].usuario_id_<?php echo $usuario['id'] ?>:checked');

					if(transacciones.size() > 0){
						$('#aprobaciones_trans_propias').show();
						document.location='#error';
					} 
					else {
						continuar = true;
					}
				}
				
			} else if(accion == 'eliminar') {
				var transacciones = $('input[type="checkbox"][name="data[seleccion][]"]:not(.usuario_id_<?php echo $usuario['id'] ?>):checked');
				
				if(transacciones.size() > 0){
					$('#modificacion_trans_terceros').show();
					document.location='#error';
				} else {
					continuar = true;
				}
			} else {
				continuar = true;
			}
			
			if(continuar){
				$('#error_sel').hide();	
				form.submit();
			} else if(
				!$('#aprobaciones_trans_propias,#modificacion_trans_terceros').is(':visible')
			){
				$('#error_sel').show();
			}
		},
		rules:{
			'data[seleccion][]':{
				required: true
			}
		}
	});	
});



</script>