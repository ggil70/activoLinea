<?php
/**
 * Formulario de transferencias a cuentas afiliadas de terceros en banco activo
**/
?>
<div>
<h2 class="user">Transferencia Tercero en Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <?php

    echo $form->create('Transferencia',
        array(
            'url'=> array(
                'controller'=>'transferencias',
                'action'=>'terceros_afiliados'
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

    echo $form->hidden('tipoDestinatario');
    echo $form->hidden('cuentaDebitar');
    echo $form->hidden('monto_maximo', array('id'=>'monto_maximo'));
    echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$monto_limite));
    echo $form->hidden('monto_limite_usuario', array('id'=>'monto_limite_usuario'));

    echo $html->tag(
        'table',
        null,
        array(
            'class'=>'formulario',
            'cellpadding'=>'0',
            'cellspacing'=>'0',
            'border'=>'0',
            'align'=>'center',
            'style'=>'table-layout:fixed'
        )
    );
    
    // La primera fila define la tabla con layout fixed
    echo '<tr><td style="width:150px"></td><td style="width:400px"></td></tr>';
    
    echo '<tr><td colspan="2">A continuación proceda a ingresar los datos de la transferencia.</td></tr>';
 
    echo '
    <tr>
    <td class="etiqueta">Cuenta de Débito:</td>
    <td> ' . enmascara($this->data['Transferencia']['cuentaDebitar']) . ' </td>
    </tr>
    ';

    echo '<tr><td colspan="2" >Seleccione el beneficiario de la transferencia y complete los datos de la operaci&oacute;n:</td></tr>';

    // Buscador con DataTables
    echo '<tr><td colspan="2">';
    echo '  <div class="buscador-container">';
    echo '      <input type="text" id="buscador"  style="height: 30px; width: 220px;" class="buscador-input" placeholder="Buscar por Nombre de Beneficiario">'; 
    echo '  </div>';
    echo '</td></tr>';
    
    echo '<tr><td colspan="2">'
        .'<label for="data[Transferencia][cuentaCreditar]" class="error manual">'._m('seleccion.requerida',array('la cuenta del beneficiario')).'</label>';
        if ($form->isFieldError('cuentaCreditar')){
            echo $form->error('cuentaCreditar',
                array(
                    'nroCuenta'=>_m('formato.invalido',array('la cuenta del beneficiario')),
                    'requerido'=>_m('campo.requerido',array('el beneficiario')),
                    'banco'=>_m('formato.invalido',array('el número de cuenta')),
                    'comparacion'=>_m('comparacion.cuentas',array('la cuenta destino'))
                )
            );
        }
    echo '</td></tr>'; 

    ?>

    <tr>
    <td colspan="2">

    <input type="hidden" name="data[Transferencia][cuentaCreditar]" value="">

    <table id="tabla-beneficiarios" cellpadding="0" cellspacing="0" border="0" class="tabla">
    <thead>
    <tr>
    <th class="ancholibre texto_centro"><span class="requerido">*</span></th>
    <th>Alias</th>
    <th>Nombre Beneficiario</th>
    <th>Numero Cuenta</th>
    </tr>
    </thead>
    <tbody>
    <?php 
    if($afiliados){
        $datos_afiliados = array(); 
        foreach($resultados as $key => $i){ 
    ?>
            <tr>
            <td class="texto_centro ancholibre">
            <input type="radio"
                   class="seleccion_afiliado"
                   id="<?php echo 'sel'.$i['Afiliacion']['id'] ?>"
                    name="data[Transferencia][cuentaCreditar]"
                    value="<?php echo desencriptar($i['Afiliacion']['numero_cuenta']);?>"
                    <?php if(
                            isset($this->data['Transferencia']['cuentaCreditar'])
                            && 0==strcmp($this->data['Transferencia']['cuentaCreditar'],desencriptar($i['Afiliacion']['numero_cuenta']))
                        ){
                        echo 'checked="checked"';
                    }
                    ?>
                    data-alias="<?php echo desencriptar($i['Afiliacion']['alias']) ?>"
                    data-id="<?php echo $i['Afiliacion']['id'] ?>"
            />
            <?php 
                echo $form->hidden('seleccion', array('id'=>'monto'.$i['Afiliacion']['id'], 'value'=>empty($i['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($i['Afiliacion']['monto_maximo']) ));

            ?>
            </td>
            <td><?php echo desencriptar($i['Afiliacion']['alias']) ?></td>
            <td><?php echo desencriptar($i['Afiliacion']['nombre_beneficiario']) ?></td>
            <td><?php echo desencriptar($i['Afiliacion']['numero_cuenta']) ?></td>
            </tr>
    <?php  
       
        }  
    } else {
        ?>
            <tr>
                <td colspan="4" class="texto_centro">No se encontraron resultados...</td>
            </tr>
        <?php
    }
    ?>
    </tbody>
    </table>
    
    <div id="sin-resultados" class="no-results" style="display: none;">
        No se encontraron coincidencias con su búsqueda
    </div>
    
    </td>
    </tr>
    
    <?php

    echo '<tr><td colspan="2">Indique la informaci&oacute;n que a continuaci&oacute;n se solicita:</td></tr>';

    echo $form->input(
        'alias',
        array(
            'label'=>'<span class="requerido">* </span>Alias:',
            'type'=>'text',
            'class'=>'alias alfanumericoEspacio',
            'maxlength' => '30',
            'id'=>'txtAlias',
            'readonly'=>'readonly'
        )
    );

    if ($form->isFieldError('monto')){
            echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
                array(
                    'requerido'=>_m('alias.requerido',array('el monto'))
                )
            ).'</td></tr>';
    }        
        
        
    echo $form->input(
        'monto',
        array(
            'label'=>'<span class="requerido">* </span>Monto Bs.:',
            'type'=>'text',
            'class'=>'monto pago'
        )
    );

    if ($form->isFieldError('monto')){
            echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
                array(
                    'requerido'=>_m('campo.requerido',array('el monto')),
                    'numerico'=>_m('formato.invalido',array('el monto')),
                    'montoMaximo'    => _m('monto.maximo', 'de la cuenta'),
                    'montoLimiteDiario'=>_m('monto.excedido', 'de la transferencia')
                )
            ).'</td></tr>';
    }

    echo $form->input(
        'descripcion',
        array(
            'label'=>'<span class="requerido">* </span>Descripci&oacute;n:',
            'type'=>'text',
            'maxlength' => '30',
            'class'=>'alfanumericoEspacio'
        )
    );

    if ($form->isFieldError('descripcion')){
            echo '<tr><td>&nbsp;</td><td>'.$form->error('descripcion',
                array(
                    'requerido'=>_m('campo.requerido',array('la descripción')),
                    'alfanumerico'=>_m('alfanumerico.invalido',array('la descripción'))
                )).'</td></tr>';
    }

    echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
    
    echo '<tr><td>&nbsp;</td><td class="submit">';
    
    echo $html->link(
        'Volver',
        'terceros/v',
        array(
            'class'=>'button'
        )
    );
    
    echo '&nbsp;&nbsp;';
    
    echo $html->link(
        'Aceptar',
        'javascript:',
        array(
            'class'=>'button envio_formulario',
            'id'=>'transferir'
        )
    );

    echo '</td></tr></table>';

    echo $form->hidden('offset');
    echo $form->end();

    ?>
</div>
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

<!-- Estilos para DataTables -->
<style>
/* Estilos para el buscador y tabla */
.buscador-container {
    position: relative;
    margin: 10px 0;
    width: 300px;
}

.buscador-input {
    width: 100%;
    padding: 8px 35px 8px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    height: 30px;
}

.buscador-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #777;
}

.no-results {
    padding: 15px;
    text-align: center;
    background-color: #f8f8f8;
    border: 1px solid #ddd;
    margin-top: 10px;
    display: none;
}

/* Estilos para DataTables */
.dataTables_wrapper {
    margin-top: 15px;
}

.dataTables_filter, .dataTables_length {
    display: none;
}

.dataTables_paginate {
    margin-top: 15px;
    text-align: center;
}

.dataTables_paginate .paginate_button {
    padding: 5px 10px;
    margin: 0 2px;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
}

.dataTables_paginate .paginate_button.current {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

/* Estilos para la tabla */
#tabla-beneficiarios {
    width: 100% !important;
    border-collapse: collapse;
}

#tabla-beneficiarios th {
    background-color: #f2f2f2;
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

#tabla-beneficiarios td {
    padding: 8px;
    border-bottom: 1px solid #eee;
}

#tabla-beneficiarios tr:hover {
    background-color: #f9f9f9;
}

/* Estilos para el mensaje de sin resultados */
.dataTables_empty {
    text-align: center;
    padding: 15px;
    font-style: italic;
    color: #666;
}
    
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled,.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,.dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
    cursor: default;
    color: #333 !important;
    border: 1px solid transparent;
    background: transparent;
    box-shadow: none
}

</style>

<!-- Incluir jQuery y DataTables -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
 

<script>
$(document).ready(function() {
    // Datos originales para restaurar si es necesario
    const datosOriginales = <?php echo json_encode($datos_afiliados); ?>;
    
    // Inicializar DataTable
    var table = $('#tabla-beneficiarios').DataTable({
        "paging": true,
        "pageLength": 5,
        "lengthChange": false,
        "searching": true,
        "info": true,
       "language": {
           // "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json",
            "emptyTable": "No se encontraron resultados...",
            "zeroRecords": "No se encontraron coincidencias",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ ",
            "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
            "paginate": {
            "first": "Primero",
            "last": "Ultimo",
            "next": "Siguiente",
            "previous": "Anterior"
        }
        }, 
        "dom": '<"top"i>rt<"bottom"lp><"clear">',
        "initComplete": function(settings, json) {
            // Ocultar el buscador nativo de DataTables
            $('.dataTables_filter').hide();
        }
    });

    // Variable para almacenar el afiliado seleccionado
    var afiliadoSeleccionado = null;
    
    // Evento para el buscador personalizado
    $('#buscador').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        table.search(searchTerm).draw();
        
        // Mostrar mensaje si no hay resultados
        if (table.rows({ search: 'applied' }).count() === 0) {
            $('#sin-resultados').show();
        } else {
            $('#sin-resultados').hide();
        }
    });

    // Manejar selección de afiliado
    $('#tabla-beneficiarios').on('click', '.seleccion_afiliado', function() {
        afiliadoSeleccionado = $(this).data('id');
        $('#txtAlias').val($(this).data('alias'));
        
        if(!$(this).data('alias').length){
            $('#txtAlias').removeAttr('readonly');
        } else {
            $('#txtAlias').attr('readonly',true);
        }
    });

    // Mantener la selección al cambiar de página
    table.on('draw', function() {
        if (afiliadoSeleccionado) {
            $('.seleccion_afiliado[data-id="' + afiliadoSeleccionado + '"]').prop('checked', true);
        }
    });

    // Resto del código JavaScript existente
    $('#TransferenciaMonto').blur(function (e) {
        var monto = $('#TransferenciaMonto').val();
        // Si el monto solo posee un solo decimal, hay que completar los dos decimales
        var arreglo_monto = monto.split(',');
        var entero = arreglo_monto[0];
        var decimal = arreglo_monto[1];

        if (decimal && decimal.length == 1) {
            monto = monto + '0';
            $('#TransferenciaMonto').val(monto);
        }
    });   

    $('#transferir').click(function(){
        if(!$('#txtAlias').val().length) {
            $('#txtAlias').removeAttr('readonly');
        }
    });
    
    $('#TransferenciaTercerosAfiliadosForm').validate({
        rules:{
            'data[Transferencia][alias]':{
                required:true
            },                    
            'data[Transferencia][cuentaCreditar]':{
                required:true
            },
            'data[Transferencia][monto]':{
                required:true,
                numberVE:true,
                montoMaximo3: '#monto_limite_usuario',
                montoMaximo2: '#monto_limite',
                montoMaximo: '#monto_maximo'			
            },
            'data[Transferencia][descripcion]':{
                required:true,alphaNumericSpace:true
            }
        },	
        messages:{
            'data[Transferencia][alias]':{
                required:<?php echo _m('campo.requerido',array('el alias de la afiliación'), true) ?>
            },
            'data[Transferencia][cuentaCreditar]':{
                required:<?php echo _m('campo.requerido',array('la cuenta del beneficiario'), true) ?>
            },
            'data[Transferencia][monto]':{
                required:<?php echo _m('campo.requerido',array('el monto'), true) ?>,
                numberVE:<?php echo _m('formato.invalido',array('el monto'), true) ?>,
                montoMaximo:<?php echo _m('monto.maximo', 'de la cuenta' ,true) ?>,
                montoMaximo2: <?php echo _m('monto.excedido', 'de la transferencia' ,true) ?>,
                montoMaximo3: <?php echo _m('monto.limite.usuario', 'de la transferencia' ,true) ?>
            },
            'data[Transferencia][descripcion]':{
                required:<?php echo _m('campo.requerido',array('la descripcion'),true) ?>,
                alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('la descripcion'),true) ?>
            }
        }	
    });
});
</script>