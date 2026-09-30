<div>
<h2 class="user">ExtraCr&eacute;dito Efectivo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>
<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style="margin-left: 60px;">
       
           <!-- Informaci?n del cliente -->
           <div id="error_contrato" style="display: none"><label class="error"><?php echo _m('aceptar.condiciones.contrato', true) ?></label></div>
           <table class="tabla extra">
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
           
           <table class="tabla extra">
                <thead>
                    <tr>
                        <td>Alias</td>
                        <td>N&uacute;mero</td>
                        <td>Monto Solicitado</td>
                        <td>Plazo</td>
                        <td>Com. flat</td>
                        <td>* 1era cuota</td>
                        <td>Contrato</td>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($data_confirmacion['tarjetas'] as $t) {?>
                    <tr>
                        <td><?php echo $t['alias']; ?></td>
                        <td><?php echo $t['msk']; ?></td>
                        <td><?php echo number_format($t['monto_solicitado'],2,',','.'); ?></td>
                        <td><?php echo $t['plazo']; ?></td>
                        <td><?php echo number_format($t['comision_flat'],2,',','.'); ?></td>
                        <td><?php echo number_format($t['primer_pago'],2,',','.'); ?></td>
                        <td class="texto_centro"><input type="checkbox" class="contrato_check">&nbsp;Aceptar&nbsp; <br><a href=<?php echo '"'.Router::url( array('controller'=>$this->params['controller'],'action'=>'contrato_extra','?'=>array('tarj'=>$t['msk'])), true ).'"'; ?>>Descargar</a></td>
                    </tr>
                <?php } ?>
                </tbody>         
            </table>
           <div class="espacio_vertical_20"></div>
            
            <!-- Fin tarjetas con monto solicitado -->
           <table class="tabla extra">
               <tr>
                   <td class="resaltar">Total a Abonar</td>
                   <td><?php echo number_format($data_confirmacion['total_abono'],2,',','.'); ?></td>
               </tr>
               <tr>
                   <td class="resaltar">Total Comisi&oacute;n Flat</td>
                   <td><?php echo number_format($data_confirmacion['total_comision_flat'],2,',','.'); ?></td>
               </tr>
           </table>
            <div><b>NOTA:</b><br>El monto de la primera cuota es un c&aacute;lculo aproximado.</div>
            <div class="espacio_vertical_20"></div>
            <table style="width: 850px;"><!-- Se forzo el ancho para poder acomodar el boton a la derecha -->
                <tr>
                    <td class="submit">
                        <a class="button" id="volver_btn" href=<?php echo '"'.Router::url( array('controller'=>$this->params['controller'],'action'=>'avance_extra'), true ).'"'; ?>>Volver</a>&nbsp;<a class="button" id="aceptar_btn" href="#">Aceptar</a>
                    </td>
                </tr>
            </table>
        
        <script type="text/javascript">
            $( document ).ready(function() {
                sig = <?php echo '"'.Router::url( array('controller'=>$this->params['controller'],'action'=>'avance_extra_resultado'), true ).'"'; ?>;
              
                $("#aceptar_btn").click(function(e){
                    if($(this).attr('href') === '#'){
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
                    }
                    else
                        return true;
                });
            });
        </script>
</div>


