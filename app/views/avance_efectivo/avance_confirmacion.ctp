<div>
<h2 class="user">Avance de Efectivo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>
<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
       <div>
           <!-- Información del cliente -->
           <div id="error_contrato" style="display: none"><label class="error"><?php echo _m('aceptar.condiciones.contrato', true) ?></label></div>
           <table class="tabla">
               <tr>
                   <td class="resaltar">Titular de la Tarjeta de Crédito</td>
                   <td><?php echo $data_confirmacion['nombre_cliente']; ?></td>
               </tr>
               <tr>
                   <td class="resaltar">N&uacute;mero de Cuenta para Abono</td>
                   <td><?php echo $data_confirmacion['cuenta_abono']['msk']; ?></td>
               </tr>
           </table>
           <div class="espacio_vertical_20"></div>
           <!-- Por cada tarjeta con monto solicitado -->
           
           <table class="tabla">
                <thead>
                    <tr>
                        <td>Alias</td>
                        <td>N&uacute;mero</td>
                        <td>Monto Solicitado</td>
                        <td>Comisi&oacute;n flat</td>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($data_confirmacion['tarjetas'] as $t) {?>
                    <tr>
                        <td><?php echo $t['alias']; ?></td>
                        <td><?php echo $t['msk']; ?></td>
                        <td><?php echo number_format($t['monto_solicitado'],2,',','.'); ?></td>
                        <td><?php echo number_format($t['comision_flat'],2,',','.'); ?></td>
                    </tr>
                <?php } ?>
                </tbody>         
            </table>
           <div class="espacio_vertical_20"></div>
            
            <!-- Fin tarjetas con monto solicitado -->
           <table class="tabla">
               <tr>
                   <td class="resaltar">Total a Abonar</td>
                   <td><?php echo number_format($data_confirmacion['total_abono'],2,',','.'); ?></td>
               </tr>
               <tr>
                   <td class="resaltar">Total Comisi&oacute;n Flat</td>
                   <td><?php echo number_format($data_confirmacion['total_comision_flat'],2,',','.'); ?></td>
               </tr>
           </table>
            <div class="espacio_vertical_20"></div>
            <table class="botonera"><!-- Se forzo el ancho para poder acomodar el boton a la derecha -->
                <tr>
                    <td class="submit">
                        <a class="button" id="volver_btn" href=<?php echo '"'.Router::url( array('controller'=>$this->params['controller'],'action'=>'avance'), true ).'"'; ?>>Volver</a>&nbsp;<a class="button" id="aceptar_btn" href="#">Aceptar</a>
                    </td>
                </tr>
            </table>
        </div>
        <script type="text/javascript">
		
			sig = <?php echo '"'.Router::url( array('controller'=>$this->params['controller'],'action'=>'avance_resultado'), true ).'"'; ?>;
			
            $( document ).ready(function() {
                $("#aceptar_btn").click(function(e){
                    var aceptados = true;
                    $.each($("input[type='checkbox']"),function(obj){
                        if(!$(this).is(':checked'))
                            aceptados = false;  
                    });
                    if(!aceptados){
                        $("#error_contrato").show();
                    }else
                    {
                        $(this).attr('href', sig);
                        $(this).click();
                    }
                });
                                
            });
        </script>
</div>


