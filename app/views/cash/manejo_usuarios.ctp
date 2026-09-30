<?php
    /**
     *   Vista para mostrar el listado de usuarios Nómina/Proveedores carga manual
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style="width:790px;">
<?php

echo $form->create('ManejoUsuariosCash',
    array(
        'url'=> array(
            'controller'=>'cash',
            'action'=>'manejo_usuarios',
            $tipo
        ),
        'id' => 'main-form'  // Añadido ID para el formulario
    )
);

echo $form->hidden('id'); 

?>

<input type="hidden" name="data[accion]" id="form-accion"> <!-- Añadido ID -->

<p>En esta opci&oacute;n Ud. podr&aacute; crear, modificar y eliminar Beneficiarios</p>

<?php
 
 // Buscador con DataTables
    echo '  <div class="buscador-container">';
    echo '      <input type="text" id="buscador"  style="height: 30px; width:220px" class="buscador-input" placeholder="Buscar por Nombre de Beneficiario">';
    echo '      <span class="buscador-icon"><i class="fa fa-search"></i></span>';
    echo '  </div>'; 
     echo '  </br>'; 
?>


<label for="data[seleccion][]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros'); ?></label>

<!-- Añadido ID a la tabla para DataTable -->
<table id="tabla-beneficiarios" class="tabla" width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
    <thead>
    <tr>
        <th class="texto_centro ancholibre"><span class="requerido">*</span></th>
        <th style="width:160px;">Nombre Beneficiario</th>
        <th>Identificación Beneficiario</th>
        <th>Número Cuenta Beneficiario</th>
        <th>Banco Beneficiario</th>
        <th>E-mail Beneficiario</th>
        <th>Número Teléfono Celular</th>
    </tr>
    </thead>
    
    <tbody>
    <?php
    if(count($listado)){
        foreach($listado as $id=>$l) {?>
        <tr>
            <td class="texto_centro ancholibre">
                <input type="radio" name="data[seleccion][]" value="<?php echo $l['AfiliacionCash']['id'] ?>"
                    <?php if(
                            isset($this->data['seleccion']) &&
                            is_array($this->data['seleccion']) &&
                            in_array($id, $this->data['seleccion'])
                        ){
                        echo 'checked="checked"';
                    }
                    ?>
                />
            </td>
            <td ><?php echo desencriptar($l['AfiliacionCash']['nombre']) ?></td>
            <td><?php echo $l['AfiliacionCash']['tipo_cedula'].desencriptar($l['AfiliacionCash']['cedula']) ?></td>
            <td><?php echo desencriptar($l['AfiliacionCash']['numero_cuenta']) ?></a></td>
            <td style="width:35%"><?php echo @$bancos[substr(desencriptar($l['AfiliacionCash']['numero_cuenta']),0,4)] ?></td>
            <td><?php echo desencriptar($l['AfiliacionCash']['email']) ?></td>
            <td><?php echo desencriptar($l['AfiliacionCash']['celular']) ?></td>
        </tr>
        <?php } ?>
    <?php } else { ?>
    <tr><td colspan="7" class="texto_centro">No se encontraron resultados...</td></tr>
    <?php } ?>
    </tbody>
</table>

<!-- Elementos fuera de la tabla para DataTable -->
<div style="margin-top: 15px;">
    <span class="requerido">* </span><span class="requerido2">Campo obligatorio</span>
</div>

<div class="botonera" style="margin-top: 15px;">
    <?php
    if($total_registros < 10){
        echo $html->link(
            'Crear',
            'javascript:',
            array(
                'class'=>'button',
                'id'=>'crear_usuarios'
            )
        );
        
        echo '&nbsp;&nbsp;&nbsp;';
    }
    
    echo $html->link(
        'Modificar',
        'javascript:',
        array(
            'class'=>'button',
            'id'=>'modificar_usuarios'                      
        )
    );
    
    echo '&nbsp;&nbsp;&nbsp;';
    
    echo $html->link(
        'Eliminar',
        'javascript:',
        array(
            'class'=>'button',
            'id'=>'eliminar_usuarios'                       
        )
    );
    ?>
</div>

<?php
echo $form->end();
?>
</div>

<?php   
echo $form->create(
    'AfiliacionCash',
    array(
        'url'=> array(
            'controller'=>'cash',
            'action'=>$this->action
        ),
        'id' => 'paginacion'
    )
);

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
    background: #3C44D1 !important;
    color: #f1ecec !important;
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

 .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    color: white !important;
    border: 1px solid #111;
    background-color: #585858;
    background:  #3C44D1 !important;
     
} 

</style>

<!-- <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>   --> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">  
<script>

$(document).ready(function() {
    // Inicializar DataTable en la tabla con ID "tabla-beneficiarios"
   var table = $('#tabla-beneficiarios').DataTable({
        "paging": true,
        "pageLength": 10,
        "lengthChange": false,
        "searching": true,
        "info": true,
        "language": {
         //   "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json",
            "emptyTable": "No se encontraron resultados...",
            "zeroRecords": "No se encontraron coincidencias",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 de 0 registros",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }, 
        "dom": '<"top"i>rt<"bottom"lp><"clear">',
        "initComplete": function(settings, json) {
            // Ocultar el buscador nativo de DataTables
            $('.dataTables_filter').hide();
        },
        "columnDefs": [
            { "orderable": false, "targets": 0 } // Hacer la primera columna no ordenable
        ]
    });

    // Conectar el input de búsqueda personalizado al DataTable
  $('#buscador').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Botones de acción
    $('#crear_usuarios').click(function(){  
        $('#form-accion').val('crear_usuarios');
        $('#main-form').submit();        
    });

    $('#eliminar_usuarios').click(function(){
        if($('#main-form').valid()){     
            $('#form-accion').val('eliminar_usuarios');
            $('#main-form').submit();
        }
    });
    
    $('#modificar_usuarios').click(function(){
        if($('#main-form').valid()){     
            $('#form-accion').val('modificar_usuarios');
            $('#main-form').submit();
        }
    }); 
    
    // Validación del formulario
    $('#main-form').validate({
        onsubmit: false,
        onkeyup: false,
        onclick: false,
        onfocusout: false,
        rules: {
            'data[seleccion][]': {
                required: true
            }
        },
        messages: {
            'data[seleccion][]': {
                required: "<?php echo _m('uno.seleccion.requerida','de los registros'); ?>"
            }
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.closest('table').prev('.error.manual'));
        }
    });
});  
</script>

<script>
$(function(){
    $('#crear_usuarios').click(function(){  
        $('input[name="data[accion]"]').val('crear_usuarios');
        $('#ManejoUsuariosCashManejoUsuariosForm').submit();        
    });

    $('#eliminar_usuarios').click(function(){
        if($('#ManejoUsuariosCashManejoUsuariosForm').valid()){     
            $('input[name="data[accion]"]').val('eliminar_usuarios');
            $('#ManejoUsuariosCashManejoUsuariosForm').submit();
        }
    });
    
        $('#modificar_usuarios').click(function(){
        if($('#ManejoUsuariosCashManejoUsuariosForm').valid()){     
            $('input[name="data[accion]"]').val('modificar_usuarios');
            $('#ManejoUsuariosCashManejoUsuariosForm').submit();
           
        }
    });
    
    $('#ManejoUsuariosCashManejoUsuariosForm').validate({
        onsubmit:false,
        onkeyup:false,
        onclick:false,
        onfocusout:false,
        rules:{
            'data[seleccion][]':{
                required: true
            }
        }
    }); 
});
</script>