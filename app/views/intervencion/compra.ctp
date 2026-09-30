
<?php
/**
 * Formulario de intervencion compra
**/
?>
<div>
<h2 class="user">Compra de Divisas en Intervención Cambiaria</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <?php
    if($error == 0){    
	echo $form->create('Intervencion',
            array(
                    'url'=> array(
                        'controller'=>'intervencion',
                        'action'=>'compra'
		),
                    'inputDefaults'=>array(
                        'div'=>false,
                        'label'=>'etiqueta',
                        'before'=>'<tr><td class="etiqueta">',
                        'between'=>'</td><td class="campo">',
                        'after'=>'</td></tr>',
                        'error'=>false
                    )
		)
	);
        
        echo $form->hidden('moneda');        
        echo $form->hidden('comisionPorc');
        echo $form->hidden('monto');
        echo $form->hidden('cuentaDestino');
        echo $form->hidden('producto');
        echo $form->hidden('tasa');
        echo $form->hidden('transactionSourceCod');
        echo $form->hidden('transactionTargetCod');
        echo $form->hidden('exchangeCod');
        echo $form->hidden('exchangeStart');
        echo $form->hidden('exchangeEnd');
        echo $form->hidden('exchangeTypeCod');
        echo $form->hidden('exchangeMarketId');
        echo $form->hidden('multiplo', array('id'=>'multiplo', 'value'=>_p('multiplo.intervencion')));
        
	echo $html->tag(
            'table',
            null,
            array(
                'class'=>'formulario',
                'cellpadding'=>'0',
                'cellspacing'=>'0',
                'border'=>'0',
                'align'=>'center'
            )
	);
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la compra.</td></tr>';

	$input_debitar = array(
            'label'=>'<span class="requerido">* </span>Cuenta Origen:',
            'type'=>'select',
            'options'=>$cuentas
	);
	
	if(count($cuentas) > 1) $input_debitar['empty'] = 'Seleccione';
	
	echo $form->input(
            'cuentaDebitar',
            $input_debitar
	);

	if ($form->isFieldError('cuentaDebitar')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar',
		array(
                    'requerido'=>_m('seleccion.requerida',array('la cuenta origen'))
		)
            ).'</td></tr>';
	}

        if($sw_cuentaDebitar == 1){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo"><span style="color:red">'._m('usuario.no.autorizado').'</span></td></tr>';
        }    

	$input_creditar = array(
            'label'=>'<span class="requerido">* </span>Cuenta Destino:',
            'type'=>'select',
            'options'=>$cta_acreditar,
	);
	
	if(count($cta_acreditar) >= 1) {
            $input_creditar['empty'] = 'Seleccione';
        }else{
            $input_creditar['empty'] = 'No posee cuentas que apliquen';
        }            
	
	echo $form->input(
            'cuentaCreditar',
            $input_creditar
	);

	if ($form->isFieldError('cuentaCreditar')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaCreditar',
		array(
                    'nroCuenta'=>_m('formato.invalido',array('la cuenta destino')),
                    'comparacion'=>_m('comparacion.cuentas',array('la cuenta destino'))
		)
            ).'</td></tr>'; 
	}
                
        $input_origen = array(
            'label'=>'<span class="requerido">* </span>Origen de Fondos:',
            'type'=>'select',
            'options'=>$origenFondos
	);
	
	if(count($origenFondos) > 1) $input_origen['empty'] = 'Seleccione';
	
	echo $form->input(
            'origenFondos',
            $input_origen
	);

	if ($form->isFieldError('origenFondos')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('origenFondos',
		array(
                    'requerido'=>_m('seleccion.requerida',array('el origen de fondos'))
		)
            ).'</td></tr>';
	}
     
        $input_motivo = array(
            'label'=>'<span class="requerido">* </span>Motivo:',
            'type'=>'select',
            'options'=>$motivos
	);
	
	if(count($motivos) > 1) $input_motivo['empty'] = 'Seleccione';
	
	echo $form->input(
            'motivos',
            $input_motivo
	);

	if ($form->isFieldError('motivos')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('motivos',
		array(
                    'requerido'=>_m('seleccion.requerida',array('el motivo'))
		)
            ).'</td></tr>';
	}
        
        echo $form->input(
            'monto_divisa',
            array(
		'label'=>'<span class="requerido">* </span>Total Divisas:',
		'type'=>'text',
		'class'=>'monto pago'
		)
	);

	if ($form->isFieldError('monto_divisa')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('monto_divisa',
		array(
                    'numerico'=>_m('formato.invalido',array('el monto en divisas')),
                    'requerido'=>_m('campo.requerido',array('el monto en divisas')),
		)
            ).'</td></tr>';
	}

        if($errorDivisa==1){
            echo '<tr id="mtoDivisa"><td class="etiqueta">&nbsp; </td><td class="campo"><label><span style="color:red;">'._m('sin.decimales') .'</span></label></td></tr>';
        }
       
	echo $form->input(
            'limite',
            array(                
		'label'=>'<span class="requerido">* </span>Límite Actual:',
		'type'=>'text',
		'class'=>'monto pago',
                'value'=>'0,00',
                'readonly'=>'readonly'
            )
	);
        
        echo $form->input(
            'tasaCompra',
            array(
		'label'=>'<span class="requerido">* </span>Tasa de Compra:',
		'type'=>'text',
		'class'=>'monto pago',
                'value'=>'0,00',
                'readonly'=>'readonly'
            )
	);
        
        echo $form->input(
            'comision',
            array(
		'label'=>'<span class="requerido">* </span>Comisión Bs.:',
		'type'=>'text',
		'class'=>'monto pago',
                'value'=>'0,00',
                'readonly'=>'readonly'
            )
	);
        
        echo $form->input(
            'total',
            array(
		'label'=>'<span class="requerido">* </span>Total Bs.:',
		'type'=>'text',
		'class'=>'monto pago',
                'value'=>'0,00',
                'readonly'=>'readonly'
            )
	);

	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td>&nbsp;</td><td class="submit">';

	echo $form->submit(
            'Aceptar',
            array(
                'class'=>'button'
		)
	);		

	echo '</td></tr></table>';

	echo $form->end();

?>
<?php if(count($intervenciones) > 0) { ?>
<div class="box">
    <div class="box-header">
      <h2 class="user">Intervenciones Disponibles</h2>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="detalle" class="table table-bordered table-striped datatable" width="100%" align="center">
            <thead>
                <tr>
                    <th class="etiqueta">Disponible</th>
                    <th class="etiqueta">Tasa</th>                     
                    <th class="etiqueta">Comisión %</th>
                    <th class="etiqueta">Moneda</th>                    
                    <th class="etiqueta">Tipo</th>                   
                </tr>
            </thead>
            <tbody>                
                <?php  foreach($intervenciones as $i) { ?>
                    <tr>
                        <td><?php echo formatea_monto_fromWs($i['available']) ?></td>
                        <td><?php echo formatea_monto_fromWs($i['rate']) ?></td>
                        <td><?php echo number_format($i['fee_percentage'] * 100, 2, ",", ".") ?></td>
                        <td><?php echo $i['currency_cod'] ?></td>
                        <td><?php echo ($i['exchange_type_cod']=='IVE') ? 'Efectivo' : 'Electrónico' ?></td>                       
                    </tr>
                <?php } ?>           
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5"></td>
                </tr>
            </tfoot>
        </table>                                
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box --> 
<?php 
        }  
    } else {
?>
    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
            <td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('intervencion.nodata'); ?></div></td>
	</tr>
	</thead>
    </table>
<?php } ?>
</div>
<script>
    
    function formatear(valor) {
        var num = parseInt(parseFloat(valor) * 100) / 100;
        var x = Number(num).toFixed(2);
        x = x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "-").replace(/\./g, ',');
        return x.replace(/\-/g, '.');
    }
    
    function round(num, decimales = 2) {
        var signo = (num >= 0 ? 1 : -1);
        num = num * signo;
        if (decimales === 0) //con 0 decimales
            return signo * Math.round(num);
        // round(x * 10 ^ decimales)
        num = num.toString().split('e');
        num = Math.round(+(num[0] + 'e' + (num[1] ? (+num[1] + decimales) : decimales)));
        // x * 10 ^ (-decimales)
        num = num.toString().split('e');
        return signo * (num[0] + 'e' + (num[1] ? (+num[1] - decimales) : -decimales));
    }

    $(function(){
         
        var texto = $('select[name="data[Intervencion][cuentaCreditar]"] option:selected').text();
        
        if(texto!=='Seleccione' && texto!=='No posee cuentas que apliquen'){
            
            var valor = $('select[name="data[Intervencion][cuentaCreditar]"] option:selected').val();
            var result = valor.split('-');
            var moneda = result[4].trim();            
            var limite = result[3].trim();
            var sourceCod = result[6].trim();
            var targetCod = result[7].trim();
            var exchangeCod = result[8].trim();
            var exchangeStart = result[9].trim();
            var exchangeEnd = result[10].trim();
            var exchangeTypeCod = result[11].trim();
            var exchangeMarketId = result[12].trim();
            limite = limite.replace(/,/g, '.');
            
            $('#IntervencionLimite').val(formatear(limite));            
            
            var monto = $('#IntervencionMontoDivisa').val();
            if (monto === '') {
                monto = 0;
            } else {
                monto = monto.replace(/[\,\.]/g, '');
                monto = parseInt(monto) / 100;
            }

            var tasa = result[2].trim();
            var porcentaje = result[1].trim();
            tasa = tasa.replace(/,/g, '.');
            porcentaje = porcentaje.replace(/,/g, '.');             
            var total = monto * tasa;
            var comision = total * porcentaje;
                        
            //total = parseInt(total * 100) / 100;
            //comision = parseInt(comision * 100) / 100;
            
            if (monto === 0) {
                tasaEfectiva = 0;
            }else{
                tasaEfectiva = (total + comision) / monto;
            }            
            
            $('#IntervencionComisionPorc').val(porcentaje);
            $('#IntervencionTransactionSourceCod').val(sourceCod);
            $('#IntervencionTransactionTargetCod').val(targetCod);
            $('#IntervencionExchangeCod').val(exchangeCod);
            $('#IntervencionExchangeStart').val(exchangeStart);
            $('#IntervencionExchangeEnd').val(exchangeEnd);
            $('#IntervencionExchangeTypeCod').val(exchangeTypeCod);
            $('#IntervencionExchangeMarketId').val(exchangeMarketId);
            $('#IntervencionTasaCompra').val(formatear(tasa)); 
            $('#IntervencionTasa').val(formatear(round(tasaEfectiva))); 
            $("#IntervencionTotal").val(formatear(round(total + comision))); 
        }      
        
        $('#IntervencionCuentaCreditar').change(function(){
            
            var texto = $('select[name="data[Intervencion][cuentaCreditar]"] option:selected').text();
        
            if(texto!=='Seleccione' && texto!=='No posee cuentas que apliquen'){
                
                var valor = $('select[name="data[Intervencion][cuentaCreditar]"] option:selected').val();
                var result = valor.split('-');
                var moneda = result[4].trim();
                var producto = result[5].trim();
                var sourceCod = result[6].trim();
                var targetCod = result[7].trim();
                var exchangeCod = result[8].trim();
                var exchangeStart = result[9].trim();
                var exchangeEnd = result[10].trim();
                var exchangeTypeCod = result[11].trim();
                var exchangeMarketId = result[12].trim();
                var limite = result[3].trim();
                limite = limite.replace(/,/g, '.');
                var porcentaje = result[1].trim();                
                porcentaje = porcentaje.replace(/,/g, '.'); 
                var tasa = result[2].trim();                
                tasa = tasa.replace(/,/g, '.');    

                $('#IntervencionLimite').val(formatear(limite)); 
                $('#IntervencionCuentaDestino').val(result[0].trim());

                var monto = $('#IntervencionMontoDivisa').val();
                if (monto === '') {
                    monto = 0;
                } else {
                    monto = monto.replace(/[\,\.]/g, '');
                    monto = parseInt(monto) / 100;
                                                
                    var total = monto * tasa;
                    var comision = total * porcentaje;
                    var tasaEfectiva = (total + comision) / monto;
                    
                    $('#IntervencionTasa').val(formatear(round(tasaEfectiva)));
                    $('#IntervencionComision').val(formatear(round(comision)));                
                    $('#IntervencionMonto').val(formatear(round(total)));
                    $("#IntervencionTotal").val(formatear(round(total + comision)));
                }
                
                $('#IntervencionTasaCompra').val(formatear(tasa)); 
                $('#IntervencionComisionPorc').val(porcentaje);
                $('#IntervencionTransactionSourceCod').val(sourceCod);
                $('#IntervencionTransactionTargetCod').val(targetCod);
                $('#IntervencionExchangeCod').val(exchangeCod);
                $('#IntervencionExchangeStart').val(exchangeStart);
                $('#IntervencionExchangeEnd').val(exchangeEnd);
                $('#IntervencionExchangeTypeCod').val(exchangeTypeCod);
                $('#IntervencionExchangeMarketId').val(exchangeMarketId);
                $('#IntervencionMoneda').val(moneda);
                $('#IntervencionProducto').val(producto);
            }            
        });


        $('#IntervencionMontoDivisa').blur(function (e) {
            var valor = $('select[name="data[Intervencion][cuentaCreditar]"] option:selected').val();

            var result = valor.split('-');
            var monto = $('#IntervencionMontoDivisa').val();           

            //Si el monto solo posee un solo decimal, hay que completar los dos deimales ya que error en los provoca un calculos de las divisas
            var arreglo_monto = monto.split(',');
            var entero = arreglo_monto[0];
            var decimal = arreglo_monto[1];

            //validar que el monto sea mayor a 1
            
            $('#mtoDivisa').css('display','none');
            if (decimal.length == 1){
               monto = monto + '0';
               $('#IntervencionMontoDivisa').val(monto);

                if (monto === '') {
                   monto = 0;
                } else {                 
                    if(monto.indexOf(",")!=-1){
                        var resultMonto = monto.split(',');                    
                        if(resultMonto[1].length===2){
                            monto = monto.replace(/[\,\.]/g, '');
                            monto = parseInt(monto) / 100; 
                        }else if(resultMonto[1].length===1){
                            monto = monto.replace(/[\,\.]/g, '');
                            monto = parseInt(monto) / 10; 
                        }else{
                            monto = monto.replace(/[\,\.]/g, '');
                            monto = parseInt(monto); 
                        }                    
                    }else{
                       monto = monto.replace(/[\,\.]/g, '');
                       monto = parseInt(monto);
                    }                    
                }
        
                var tasa = result[2].trim();
                var porcentaje = result[1].trim();
                var sourceCod = result[6].trim();
                var targetCod = result[7].trim();
                var exchangeCod = result[8].trim();
                var exchangeStart = result[9].trim();
                var exchangeEnd = result[10].trim();
                var exchangeTypeCod = result[11].trim();
                var exchangeMarketId = result[12].trim();
                tasa = tasa.replace(/,/g, '.');
                porcentaje = porcentaje.replace(/,/g, '.');
                var total = monto * tasa;
                var comision = total * porcentaje;
                        
                //total = parseInt(total * 100) / 100;
                //comision = parseInt(comision * 100) / 100;
                if (monto === 0) {
                    tasaEfectiva = 0;
                }else{
                    tasaEfectiva = (total + comision) / monto;
                } 

                $('#IntervencionTasaCompra').val(formatear(tasa));
                $('#IntervencionTasa').val(formatear(round(tasaEfectiva)));
                $('#IntervencionComision').val(formatear(round(comision)));
                $('#IntervencionComisionPorc').val(porcentaje);
                $('#IntervencionTransactionSourceCod').val(sourceCod);
                $('#IntervencionTransactionTargetCod').val(targetCod);
                $('#IntervencionExchangeCod').val(exchangeCod);
                $('#IntervencionExchangeStart').val(exchangeStart);
                $('#IntervencionExchangeEnd').val(exchangeEnd);
                $('#IntervencionExchangeTypeCod').val(exchangeTypeCod);
                $('#IntervencionExchangeMarketId').val(exchangeMarketId);
                $('#IntervencionMonto').val(formatear(round(total)));
                $("#IntervencionTotal").val(formatear(round(total + comision)));         
            }
                //Fin completar decimales en las divisas
        });


        $('#IntervencionMontoDivisa').on("keyup", function (e) {
            $('#mtoDivisa').css('display','none'); 
            var valor = $('select[name="data[Intervencion][cuentaCreditar]"] option:selected').val();
            var result = valor.split('-');
            var monto = $('#IntervencionMontoDivisa').val();
            if (monto === '') {
                monto = 0;
            } else {                 
                if(monto.indexOf(",")!=-1){
                    var resultMonto = monto.split(',');                    
                    if(resultMonto[1].length===2){
                        monto = monto.replace(/[\,\.]/g, '');
                        monto = parseInt(monto) / 100; 
                    }else if(resultMonto[1].length===1){
                        monto = monto.replace(/[\,\.]/g, '');
                        monto = parseInt(monto) / 10; 
                    }else{
                        monto = monto.replace(/[\,\.]/g, '');
                        monto = parseInt(monto); 
                    }                    
                }else{
                    monto = monto.replace(/[\,\.]/g, '');
                    monto = parseInt(monto);
                }                    
            }
            
            var tasa = result[2].trim();
            var porcentaje = result[1].trim();
            var sourceCod = result[6].trim();
            var targetCod = result[7].trim();
            var exchangeCod = result[8].trim();
            var exchangeStart = result[9].trim();
            var exchangeEnd = result[10].trim();
            var exchangeTypeCod = result[11].trim();
            var exchangeMarketId = result[12].trim();
            tasa = tasa.replace(/,/g, '.');

            porcentaje = porcentaje.replace(/,/g, '.');
            var total = monto * tasa;
            var comision = total * porcentaje;
                        
            //total = parseInt(total * 100) / 100;
            //comision = parseInt(comision * 100) / 100;
            if (monto === 0) {
                tasaEfectiva = 0;
            }else{
                tasaEfectiva = (total + comision) / monto;
            } 
           
            $('#IntervencionTasaCompra').val(formatear(tasa)); 
            $('#IntervencionTasa').val(formatear(round(tasaEfectiva))); 
            $('#IntervencionComision').val(formatear(round(comision)));
            $('#IntervencionComisionPorc').val(porcentaje);
            $('#IntervencionTransactionSourceCod').val(sourceCod);
            $('#IntervencionTransactionTargetCod').val(targetCod);
            $('#IntervencionExchangeCod').val(exchangeCod);
            $('#IntervencionExchangeStart').val(exchangeStart);
            $('#IntervencionExchangeEnd').val(exchangeEnd);
            $('#IntervencionExchangeTypeCod').val(exchangeTypeCod);
            $('#IntervencionExchangeMarketId').val(exchangeMarketId);
            $('#IntervencionMonto').val(formatear(round(total)));
            $("#IntervencionTotal").val(formatear(round(total + comision)));            
	})            

	$('#IntervencionCompraForm').validate({
            rules:{
                'data[Intervencion][cuentaDebitar]':{
                        required:true
                },
                'data[Intervencion][cuentaCreditar]':{
                        required:true
                },
                'data[Intervencion][monto_divisa]':{
                        required:true, 
                        numberVE:true,
                        montoMultiplo: function(e){
                            if($('#IntervencionExchangeTypeCod').val()=='IVE'){
                                return '#multiplo';
                            }
                        },
                        montoMaximo2: '#IntervencionLimite'
                },
                'data[Intervencion][origenFondos]':{
                        required:true
                },
                'data[Intervencion][motivos]':{
                        required:true
                }
            },
            messages:{
                'data[Intervencion][cuentaDebitar]':{
                        required:<?php echo _m('seleccion.requerida',array('la cuenta origen'), true) ?>
                },
                'data[Intervencion][cuentaCreditar]':{
                        required:<?php echo _m('seleccion.requerida',array('la cuenta destino'), true) ?>
                },

                'data[Intervencion][monto_divisa]':{
                        required:<?php echo _m('campo.requerido',array('el monto en divisas'), true) ?>,
                        numberVE:<?php echo _m('formato.invalido',array('el monto en divisas'), true) ?>,
                        montoMultiplo: <?php echo _m('monto.multiplo', array(_p('multiplo.intervencion')) ,true) ?>,
                        montoMaximo2: <?php echo _m('monto.limite.compra', '' ,true) ?>,
                }
            }
	});
        
        $('#detalle').DataTable({
            "bFilter": false,
            "bLengthChange" : false,    
            language: {                
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningun dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Ultimo",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"

                },
            }
        });
});
</script>