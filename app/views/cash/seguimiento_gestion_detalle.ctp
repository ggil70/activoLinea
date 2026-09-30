<?php
    /**
     *  Vista para mostrar el detalle del pago de nómina y/o proveedores seleccionado
     */
?>
<!--
    
-->

<div>
<h2 class="user">Seguimiento de Gesti&oacute;n <!--Pago < ?php echo $this->data['SeguimientoGestion']['tipoGestion']?--></h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>
<div class="descripcion">En esta opción podrá verificar el resultado de la gestión, estatus y detalle de sus lotes de pago. 
Los lotes de pagos con estatus "Pendiente por procesar" serán gestionados por el banco en la fecha valor indicado, los lotes con estatus "Procesados Parcialmente" no han culminado su gestión, los lotes con estado "Procesado" y "Procesado con Rechazo" culminaron su gestión en el banco. Utilice las opciones de búsqueda para ubicar el(los) beneficiario(s) que desea verificar.
</div>
<div class="espacio_vertical_20"></div>
<div class="contenedor_formulario" style="width:800px;">

<?php

echo $form->create('TransaccionPendiente',
	array(
            'url'=> '/cash/seguimiento_gestion_detalle',
            'id'=>'TransaccionPendienteSeguimientoGestionDetalleForm'
	)
);

echo $form->hidden('id');
echo $form->hidden('formato');
echo $form->hidden('beneficiarios');
?>

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
            <td class="resumen_paginacion user" colspan="2">Detalle del pago:</td>
	</tr>
	</thead>	
	<tbody>
                <tr>
			<td class="etiqueta">Número Lote:</td>
			<td><?php echo $this->data['TransaccionPendiente']['lote'] ?></td>
		</tr>		
		<tr>
			<td class="etiqueta">Fecha Valor:</td>
			<td><?php echo date('d/m/Y',strtotime($this->data['TransaccionPendiente']['fecha_hora_valor']))?></td>
		</tr>
				
		<tr>
			<td class="etiqueta">Descripción:</td>
			<td><?php if(!empty($this->data['TransaccionPendiente']['descripcion'])) {
				echo desencriptar($this->data['TransaccionPendiente']['descripcion']);
			}?></td>
		</tr>
                <tr>
			<td class="etiqueta">Estatus:</td>
			<td><?php echo $this->data['TransaccionPendiente']['descripcion_estatus'] ?></td>
		</tr>
	</tbody>
</table>
<br>    
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
		<td class="resumen_paginacion user" colspan="2">Detalle de registros:</td>
	</tr>
	</thead>	
	<tbody>  
                <tr>
			<td></td>
			<td align="center" class="etiqueta">Cantidad</td>
                        <td align="center" class="etiqueta">Monto</td>
		</tr>
		<tr>
			<td class="etiqueta">Total Registros Procesados:</td>
			<td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_aplicados'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_aplicados']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Total Registros Rechazados:</td>
			<td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_rechazados'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_rechazados']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Total Registros Pendientes:</td>
			<td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_pendientes'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_pendientes']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Registros Totales:</td>
			<td align="center"><?php echo count($this->data['Beneficiarios']) ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto']) ?></td>
		</tr>		
	</tbody>
</table>    
<br>
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>		
	</tr>
	</thead>	
	<tbody> 
                <tr>
                        <td></td>
                        <td colspan="2" align="center" class="etiqueta">En Banco Activo</td>
                        <td colspan="2" align="center" class="etiqueta">En Otros Bancos</td>
		</tr>
                <tr>
			<td></td>
			<td align="center" class="etiqueta">Cantidad</td>
                        <td align="center" class="etiqueta">Monto</td>
                        <td align="center" class="etiqueta">Cantidad</td>
                        <td align="center" class="etiqueta">Monto</td>
		</tr>
		<tr>
			<td class="etiqueta">Registros Procesados:</td>
			<td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_aplicados_p'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_aplicados_p']) ?></td>
                        <td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_aplicados_ob'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_aplicados_ob']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Registros Rechazados:</td>
			<td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_rechazados_p'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_rechazados_p']) ?></td>
                        <td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_rechazados_ob'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_rechazados_ob']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Registros Pendientes:</td>
			<td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_pendientes_p'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_pendientes_p']) ?></td>
                        <td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_pendientes_ob'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_pendientes_ob']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Registros Totales:</td>
			<td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_pt'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_pt']) ?></td>
                        <td align="center"><?php echo $this->data['TransaccionPendiente']['cantidad_obt'] ?></td>
                        <td align="right"><?php echo formatea_monto_fromWs($this->data['TransaccionPendiente']['monto_obt']) ?></td>
		</tr>		
	</tbody>
</table> 
<br>
<br>
<div class="box">
    <div class="box-header">
    <?php
    if($this->data['TransaccionPendiente']['alias']=='NEX'){
            echo '<h2 class="user">Detalle de Pagos Transmitidos a Procesamiento</h2>';
    }else{
            echo '<h2 class="user">Detalle de Beneficiarios</h2>';
    }    
    ?> 

    </div>
    <!-- /.box-header -->

    <!--  Se agrega a la tabla hidden el campo ID  de la transaccion pendiente echo $i['id'] #descargaraPDF  -->

    <div class="box-body">
        <table id="detalle" class="table table-bordered table-striped datatable" width="100%">
            <thead>
                <tr>
                    <th hidden class="etiqueta" >Id </th>
                    <th class="etiqueta">Identificación <br>Beneficiario</th>
                    <th class="etiqueta">Nombre <br>Beneficiario</th>                     
                    <th class="etiqueta">Número de Cuenta <br>Beneficiario</th>
                    <th class="etiqueta">Banco <br>Beneficiario</th>
                    <?php if($this->data['TransaccionPendiente']['tipo_carga']=='a') { ?> 
                        <th class="etiqueta">Referencia</th> 
                    <?php } ?>  
                    <th class="etiqueta">Monto</th>
                    <th class="etiqueta">Resultado</th>
                    <th class="etiqueta">Motivo <br>Rechazo</th>
                   <th class="etiqueta">Detalle <br>Unico</th>  
                    
                </tr>
            </thead>
            <tbody>                
                <?php  foreach($this->data['Beneficiarios'] as $i) {  //echo json_encode($this->data['Beneficiarios'] ) ;  ?>
                    
                    <tr>
                        <td hidden><?php echo $i['id'] ?></td>
                        <td><?php echo $i['tipo_cedula'].desencriptar($i['cedula']) ?></td>
                        <td><?php echo desencriptar($i['nombre']) ?></td>
                        <td><?php echo desencriptar($i['numero_cuenta']) ?></td>
                        <td><?php echo $bancos[substr(desencriptar($i['numero_cuenta']),0,4)] ?></td> 
                        <?php if($this->data['TransaccionPendiente']['tipo_carga']=='a') { ?> 
                            <td><?php echo ($i['referencia']!=null && !empty($i['referencia'])) ? str_pad(desencriptar($i['referencia']),9,'0',STR_PAD_LEFT) : '000000000'; ?></td>
                        <?php } ?>                         
                        <td class="monto"><?php echo formatea_monto_fromWs($i['monto']) ?></td>
                        <td><?php echo $i['resultado'] ?></td>
                        <td><?php echo $i['motivo_error'] ?></td>
                        <td style="text-align:center;"><input id="check_lista[]" name="check_lista[]" value=<?php echo $i['id'] ?> type="checkbox" /></td> 
                    </tr>
                <?php } 
           
             ?>           
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="9"></td>
                </tr>
            </tfoot>
        </table>                                
    </div>
    <!-- /.box-body -->
</div>



<!-- /.box -->  

<hr>
<table class="confirmacion" cellspacing="0" cellpadding="0" border="0" style="width:100%;">
<tr>
    <td class="botonera" colspan="2">
        
        <?php 
        
        echo '&nbsp;&nbsp;';

    echo $html->link(
        'Descargar Recibos',
        'javascript:',
        array(
            'class'=>'button',
            'id'=>'descarga_pdf'
        )
    );

        echo '&nbsp;&nbsp;';

        echo $html->link(
            'Descargar TXT',
            'javascript:',
            array(
                'class'=>'button',
                'id'=>'descarga_txt'
            )
        );

        echo '&nbsp;&nbsp;';

        echo $html->link(
            'Descargar EXCEL',
            'javascript:',
            array(
                'class'=>'button',
                'id'=>'descarga_xls'
            )
        ); 
        ?>
    </td>
</tr>
</table>
 
<hr> 

<?php
if(isset($this->data['TransaccionPendiente']['dataRechazo']) && $this->data['TransaccionPendiente']['alias']=='NEX'){
    if(count($this->data['TransaccionPendiente']['dataRechazo']) > 0){

?>	
<br>
<br>
<div class="box">
    <div class="box-header">
      <h2 class="user">Detalle de Pagos No Transmitidos a Procesamiento</h2>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="detalleRec" class="table table-bordered table-striped datatable" width="100%">
            <thead>
                <tr>
                    <th class="etiqueta">Cedula <br>Beneficiario</th>
                    <th class="etiqueta">Nombre <br>Beneficiario</th>
                    <th class="etiqueta">Motivo</th>


                </tr>
            </thead>
            <tbody>                
                <?php  
                    foreach($this->data['TransaccionPendiente']['dataRechazo'] as $t) { ?>
                    <tr>
                        <td><?php echo $t['NexpLogsError']['cedula'] ?></td>
                        <td><?php echo $t['NexpLogsError']['nombre'] ?></td>
                        <td><?php echo $t['NexpLogsError']['error_pago'] ?></td>
                    </tr>
                <?php } ?>           
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8"></td>
                </tr>
            </tfoot>
        </table>                                
    </div>
    <!-- /.box-body -->
</div>

<table class="confirmacion" cellspacing="0" cellpadding="0" border="0" style="width:100%;">
<tr>
    <td class="botonera" colspan="2">
        <?php 
        echo '&nbsp;&nbsp;';

        echo $html->link(
            'Descargar PDF',
            'descarga_comprobante_errores_express',
            array(
                'class'=>'button'
            )
        );

        ?>
    </td>
</tr>
</table>

    

<?php

}
}
?>

<hr>

<!-- /.box -->  
<table class="confirmacion" cellspacing="0" cellpadding="0" border="0" style="width:100%;">
<tr>
	<td class="botonera" colspan="2">
		<?php 

		echo $html->link(
			'Volver',
			'seguimiento_gestion',
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


		?>
	</td>
</tr>
</table>

<?php echo $form->end() ?>

</div>



<script>
    $(function(){

        /*  Se agrega boton de Descarga de PDF obtine los id de los elementos que se seleccionen con el checklist para enviarlos al controlador 23/10/24*/ 
        $('#descarga_pdf').click(function(){
                var check_lista=$("input[name='check_lista[]']");
                var valueCheck=[];
                if(check_lista.length > 0){
                    for (let i = 0; i < check_lista.length; i++) {
                        if(check_lista[i].checked){
                            valueCheck.push(check_lista[i].defaultValue)
                        }
                    } 
                    console.log(valueCheck);
                    if(valueCheck.length > 0){
                        $('input[name="data[TransaccionPendiente][formato]"]').val('pdf');
                        $('input[name="data[TransaccionPendiente][beneficiarios]"]').val(valueCheck);
                        $('#TransaccionPendienteSeguimientoGestionDetalleForm').submit();     
                    }else{
                        alert('Por favor, seleccione al menos un PDF para imprimir.');
                    }
                    return false;
                }else{
                    alert('Por favor, seleccione al menos un PDF para imprimir.');
                }
                
        });
 

        $('#descarga_txt').click(function(){
                $('input[name="data[TransaccionPendiente][formato]"]').val('txt');
                $('#TransaccionPendienteSeguimientoGestionDetalleForm').submit();
                return false;
        });

        $('#descarga_xls').click(function(){
                $('input[name="data[TransaccionPendiente][formato]"]').val('xls');
                $('#TransaccionPendienteSeguimientoGestionDetalleForm').submit();
                return false;

        });
        $('#detalle').DataTable({
                   
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

        }

    );

        $('#detalleRec').DataTable({
                   
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
        }
		);


    });
</script>
  

<script>
 /*    $(document).ready(function() {
     $('#TransaccionPendienteSeguimientoGestionDetalleForm').on('submit', function(event) {
                event.preventDefault(); // Prevenir el envío del formulario por defecto
                var selectedPdfs = $('input[name="check_lista[]"]:checked').map(function() {
                    console.log(this.value);
                    return this.value; // Obtener el valor de los checkboxes seleccionados
                  //  console.log(this.value);
                }).get();
                  //  console.log(selectedPdfs)
                if (selectedPdfs.length > 0) {
                    
                    // Aquí puedes realizar la lógica para imprimir o enviar los PDFs seleccionados
                    //  console.log(selectedPdfs); // Mostrar en consola los PDFs seleccionados
                    // Puedes enviar el formulario si es necesario
                    this.submit();
                } else {
                    alert('Por favor, seleccione al menos un PDF para imprimir.');
                }
            });
        });  */
    </script>
 
