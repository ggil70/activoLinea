<div>
<h2 class="user">Avance de Efectivo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>
<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
       <div>
           <!-- Informaci?n del cliente -->
           <div id="error_contrato" style="display: none"><label class="error"><?php echo _m('aceptar.condiciones.contrato', true) ?></label></div>
           <table class="tabla">
               <tr>
                   <td class="resaltar">Nombre del Beneficiario:</td>
                   <td><?php echo $data_confirmacion['nombre_cliente']; ?></td>
               </tr>
               <tr>
                   <td class="resaltar">N&uacute;mero de Cuenta para Abono:</td>
                   <td><?php echo $data_confirmacion['cuenta_abono']['msk']; ?></td>
               </tr>
               <tr>
                   <td class="resaltar">Fecha:</td>
                   <td><?php echo date('d/m/Y'); ?></td>
               </tr>
           </table>
           <div class="espacio_vertical_20"></div>
           
           <!-------------- Por cada tarjeta con monto solicitado ------------->
           <div id="operaciones_tablas">
               
           </div>
            <!--------------- Fin tarjetas con monto solicitado --------------->
            
            <div id="total_resultado" style="display: none">
                <table class="tabla">
                    <tr>
                        <td class="resaltar">Total Abonado:</td>
                        <td id="total_abonado"></td>
                    </tr>
                    <tr>
                        <td class="resaltar">Total Comisi&oacute;n Flat:</td>
                        <td id="total_comision"></td>
                    </tr>
                </table>
                 <div class="espacio_vertical_20"></div>
                 <div class="submit pull-right">
                     <button class="button" id="imprimir_btn">Imprimir</button>&nbsp;<button class="button" id="pdf_btn">Descargar PDF</button>
                 </div>
            </div>
        </div>
        <div class="basic-modal-content" id="procesando">
            <h3>Procesando...</h3>
            <p><?php echo _m('procesando.transaccion','el avance de efectivo'); ?></p>
        </div>
        <!-- ------------- Formulario para solicitar el PDF -------------- --->
        <form id="pdf_form" method="post" action=<?php echo '"'.Router::url( array('controller'=>$this->params['controller'],'action'=>'genera_pdf_avance'), true ).'"'; ?>>
            
        </form>
        <script type="text/javascript">
            $(function(){
                $('#procesando').modal({minHeight:120,minWidth:400,close:false});
            });
        </script>
        <!-- ------------- Fin formulario para solicitar el PDF -------------- --->
        <script type="text/javascript">
            // Arreglo con la data de las tarjetas de todas las operaciones a realizar
            <?php
             $operaciones = array();
            foreach($data_confirmacion['tarjetas'] as $t){
                $op = "{'t_alias':'".$t['alias']."',".
                        "'t_numero':'".$t['numero']."',".
                        "'t_msk':'".$t['msk']."',".
                        "'t_saldo_disp':'". number_format($t['saldo_disponible'],2,'.','')."',".
                        "'t_monto_sol':'". number_format($t['monto_solicitado'],2,'.','')."',".
                        "'t_comision':'". number_format($t['comision_flat'],2,'.','')."',".
                        "'t_referencia':'',".
                        "'t_resultado':'',".
                        "'t_cod_respuesta':''}";
                $operaciones[] = $op;
            }
            ?>

            var operaciones = [<?php echo implode(',',$operaciones);?>];
            var cuenta_abonar = '<?php echo $data_confirmacion['cuenta_abono']['numero']; ?>';
            var cantidad_operaciones = 0;
            var monto_total = 0;
            var comision_total = 0;
            var cabecera_tabla = "<table class=\"tabla\"><thead><tr><td width=\"150\">N&uacute;mero</td><td>Monto Avance</td><td>Comision flat</td><td>Referencia</td><td>Resultado Operaci&oacute;n</td></tr></thead>";
            var fin_tabla = "</table><div class=\"espacio_vertical_20\"></div>";

            $(function() {
                $("#imprimir_btn").click(function(e){
                    window.print();
                });

                realizarAvances();
                getPdf();
            });
            
            // Funcion que genera el pdf
            function getPdf(){
                $("#pdf_btn").click(function(e){ 
                    $.each(operaciones, function(i){
                        if(operaciones[i].t_cod_respuesta === '00'){
                            var name = "data[operaciones]["+i+"][t_alias]";
                            var value = operaciones[i].t_alias;
                            $("#pdf_form").append("<input type=\"hidden\" name=\""+ name +"\" value=\""+ value +"\">");
                            name = "data[operaciones]["+i+"][t_numero]";
                            value = operaciones[i].t_numero;
                            $("#pdf_form").append("<input type=\"hidden\" name=\""+ name +"\" value=\""+ value +"\">");
                            name = "data[operaciones]["+i+"][t_msk]";
                            value = operaciones[i].t_msk;
                            $("#pdf_form").append("<input type=\"hidden\" name=\""+ name +"\" value=\""+ value +"\">");
                            name = "data[operaciones]["+i+"][t_saldo_disp]";
                            value = operaciones[i].t_saldo_disp;
                            $("#pdf_form").append("<input type=\"hidden\" name=\""+ name +"\" value=\""+ value +"\">");
                            name = "data[operaciones]["+i+"][t_monto_sol]";
                            value = operaciones[i].t_monto_sol;
                            $("#pdf_form").append("<input type=\"hidden\" name=\""+ name +"\" value=\""+ value +"\">");
                            name = "data[operaciones]["+i+"][t_comision]";
                            value = operaciones[i].t_comision;
                            $("#pdf_form").append("<input type=\"hidden\" name=\""+ name +"\" value=\""+ value +"\">");
                            name = "data[operaciones]["+i+"][t_referencia]";
                            value = operaciones[i].t_referencia;
                            $("#pdf_form").append("<input type=\"hidden\" name=\""+ name +"\" value=\""+ value +"\">");
                            name = "data[operaciones]["+i+"][t_resultado]";
                            value = operaciones[i].t_resultado;
                            $("#pdf_form").append("<input type=\"hidden\" name=\""+ name +"\" value=\""+ value +"\">");
                            name = "data[operaciones]["+i+"][t_cod_respuesta]";
                            value = operaciones[i].t_cod_respuesta;
                            $("#pdf_form").append("<input type=\"hidden\" name=\""+ name +"\" value=\""+ value +"\">");
                        }
                    });
                    $("#pdf_form").submit();  
                });
            }
            
            
            // Funcion que envia la peticion de avance por cada tarjeta asyncronamente
            function realizarAvances(){
                cantidad_operaciones = operaciones.length; //Cantidad de operaciones a realizar
                
                //Por cada tarjeta que se realiza avance
                $.each(operaciones,function(index, obj){
                        console.info("Enviando avance de efectivo tarjeta "+ obj.t_numero);
                        // Data formateada como urlencoded para que cake la reconozca como enviada desde un formulario
                        var postData = getAvanceData(obj);
                        var url = <?php echo '"'.Router::url( array('controller'=>$this->params['controller'],'action'=>'realiza_avance'), true ).'"'; ?>;
                        $.post(url, postData)
                            .done(function(data){
                                obj.t_referencia = data.t_referencia;
                                obj.t_resultado = data.t_resultado;
                                obj.t_cod_respuesta = data.t_cod_respuesta;
                                if(data.t_cod_respuesta === '00'){
                                    // La transaccion fue exitosa
                                    monto_total += parseFloat(data.t_monto_sol);
                                    comision_total += parseFloat(obj.t_comision);
                                }
                                cantidad_operaciones--;
                                mostrar_resultado();
                                  
                            })
                            .fail(function(data){
                                obj.t_referencia = "";
                                obj.t_cod_respuesta = data.t_cod_respuesta;
                                obj.t_resultado = "<?php echo _m('servicio.nocomunicacion', true) ?>";
                                cantidad_operaciones--;
                                mostrar_resultado();
                            });
                    }
                );
                mostrar_resultado();
            }
            
            // Funcion que muestra el resultado de todas las operaciones realizadas
            function mostrar_resultado(){
                if(cantidad_operaciones === 0){
                    // Si es 0 es porque ya todas se realizaron
                    var body = "<body>";
                    $.each(operaciones, function(obj){
                        var op = operaciones[obj];
                        var clase = op.t_cod_respuesta !== '00' ? 'class=\"error\"' : "";
                        body += "<tr><td>"+op.t_msk+"</td>";
                        body += "<td>"+parseFloat(op.t_monto_sol).toLocaleString("es-VE",{ minimumFractionDigits: 2 })+"</td>";
                        body += "<td>"+parseFloat(op.t_comision).toLocaleString("es-VE",{ minimumFractionDigits: 2 })+"</td>";
                        body += "<td>"+op.t_referencia+"</td>";
                        body += "<td class=\"texto_centro\"><label "+clase+">"+op.t_resultado+"</label></td></tr>";
                    });
                    
                    body += "</tbody>";
                    $("#total_abonado").text(monto_total.toLocaleString("es-VE",{ minimumFractionDigits: 2 }));
                    $("#total_comision").text(comision_total.toLocaleString("es-VE",{ minimumFractionDigits: 2 }));
                    $("#operaciones_tablas").append(cabecera_tabla + body + fin_tabla);
                    $.modal.close();
                    if(monto_total <= 0) $("#pdf_btn").remove();
                    $("#total_resultado").show("slow"); 
                }               
            }
            
            // Funcion que formatea la data como si fuese enviada desde un formulario, para que cake la pueda procesar
            function getAvanceData(op){
                var d = {'data[t_numero]':op.t_numero,
                     'data[t_monto_sol]':op.t_monto_sol,
                     'data[t_cuenta_abonar]':cuenta_abonar
                };
                
                return d;
            }
        </script>
</div>


