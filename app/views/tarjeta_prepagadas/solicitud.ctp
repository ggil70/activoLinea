<?php
/**
 * Formulario de TDD moneda extranjera
**/


?>
<div>
<h2 class="user">Solicitud de Tarjeta Prepago  </h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Validar Informaci&oacute;n Banco</div>

<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n Personal</div>

<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>


<div class="paso_nro">4</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>


<div class="paso_nro">5</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<br><br><br>
<?php 
            echo $form->create('Solicitud',
            array(
        'url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'solicitud'
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

echo '<tr><td colspan="2"><div class="paso_desc_actual">Ubicación Geografica.</div></td></tr>';


    $input_estados = array(
            'label'=>'<span class="requerido">* </span>Estado:',
            'type'=>'select',
            'id'=>'estados',
            'options'=>$estados,
            'empty'=>'Seleccione',
            'value'=>$idEstado
    );
    
    //if(count($estados) > 1) $input_estados['empty'] = 'Seleccione';
    
    echo $form->input(
            'estados',
            $input_estados
    );

    if ($form->isFieldError('estados')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('estados',
        array(
        )
            ).'</td></tr>'; 
    }



    $input_municipios = array(
            'label'=>'<span class="requerido">* </span>Municipio:',
            'type'=>'select',
            'id'=>'municipios',
            'empty'=>'Seleccione',
            'options'=>$municipios,
            'value'=>$idMunicipio
    );
    
    //if(count($estados) > 1) $input_estados['empty'] = 'Seleccione';
    
    echo $form->input(
            'municipios',
            $input_municipios
    );


    if ($form->isFieldError('municipios')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('municipios',
        array(
        )
            ).'</td></tr>'; 
    }


    $input_ciudades = array(
            'label'=>'<span class="requerido">* </span>Ciudad:',
            'type'=>'select',
            'id'=>'ciudades',
            'empty'=>'Seleccione',
            'options'=>$ciudades,
            'value'=>$idCiudad
    );
    
    //if(count($estados) > 1) $input_estados['empty'] = 'Seleccione';
    
    echo $form->input(
        'ciudades',
        $input_ciudades
    );


    if ($form->isFieldError('ciudades')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('ciudades',
        array(
        )
            ).'</td></tr>'; 
    }





    $input_zonaPostal = array(
            'label'=>'<span class="requerido">* </span>Zona Postal:',
            'type'=>'select',
            'id'=>'zonaPostal',
            'empty'=>'Seleccione',
            'options'=>$zonaPostal,
            'value'=>$idZonaPostal
    );
    
    //if(count($estados) > 1) $input_estados['empty'] = 'Seleccione';
    
    echo $form->input(
        'zonaPostal',
        $input_zonaPostal
    );

    if ($form->isFieldError('zonaPostal')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('zonaPostal',
        array(
        )
            ).'</td></tr>'; 
    }    


 echo $form->input(
        'direccion',
        array(
            'label'=>'<span class="requerido">* </span>Dirección Principal',
            'type'=>'textarea',
            'cols' => '100',
            'id'=>'direccion',
            'value'=>$idDire1
            )
    );

    if ($form->isFieldError('direccion')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('direccion',
        array(
        )
            ).'</td></tr>'; 
    } 

  echo $form->input(
        'direccion2',
        array(
            'label'=>'<span class="requerido">* </span>Dirección Secundaria',
            'type'=>'textarea',
            'rows' => '40', 
            'cols' => '20',
            'id'=>'direccion2',
            'value'=>$idDire2
            )
    );

    if ($form->isFieldError('direccion2')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('direccion2',
        array(
        )
            ).'</td></tr>'; 
    } 


 /*   $input_prefijo = array(
            'label'=>'<span class="requerido">* </span>Código Celular:',
            'type'=>'select',
            'id'=>'prefijoT',
            'options'=>$prefijos_telefonicos,
            'empty'=>'Seleccione Código',
            'style'=>'width:100px',
    );
    echo $form->input(
            'prefijoT',
            $input_prefijo
    );

*/


    echo '<tr><td class="etiqueta"><span class="requerido">* </span>Número Celular:</td>';  
        echo '<td class="campo">';                

                 echo $form->input(
                        'prefijoT',
                        array(
                                'before'=>false,
                                'between'=>false,
                                'after'=>false,
                                'label'=>false,                                
                                'class'=>'prefijoT',
                                'type'=>'select',
                                'id'=>'prefijoT',
                                'options'=>$prefijos_telefonicos,
                                'style'=>'width:80px',
                                'value'=>$idPrefijo                                
                        )
                );

                echo $form->input(
                        'telefono',
                        array(
                                'before'=>false,
                                'between'=>false,
                                'label'=>false,
                                'type'=>'text',
                                'style'=>'width:100px',
                                'class'=>'numerico',
                                'maxlength'=>7,
                                'minlength'=>7,
                                'id'=>'telefono',
                                'value'=>$idTelefono
                        )
                );  
                
        echo '</td>';
        echo '</tr>';
   

    if ($form->isFieldError('prefijoT')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('prefijoT',
        array(
        )
            ).'</td></tr>'; 
    }

        if ($form->isFieldError('telefono')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('celular',
                    array(
                        'requerido'=>_m('campo.requerido','el número de celular'),
                        'numero'=>_m('formato.invalido','el número de celular')                 
                    )
            ).'</td></tr>';
    }                    
   

  
    


    echo '<tr><td>&nbsp;</td><td class="submit">';

    echo $form->submit(
        'Solicitar',
        array(
            'class'=>'button'
        )
    );     




    echo '</td></tr>';

    echo '<tr><td colspan="2"><span class="requerido2">'._m('tcp.mensaje.comision.solicitud',$etiqComision).'</span></td></tr>';

    echo '</table>';
    echo $form->end();



?>
<script type="text/javascript">
 


function llenar_opcion(key, value){
    $('#municipios').append(
    $('<option>',{
        'value': key,
        'text': value
    })
    );
}    


function obt_municipios(estado){
    $("#municipios").html('<option value="">Seleccione</option>'); 
    switch(estado){
        case '1':
            <?php 
            foreach ($municipios_gen['1'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '2':
            <?php 
            foreach ($municipios_gen['2'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;


        case '3':
            <?php 
            foreach ($municipios_gen['3'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;

        case '4':
            <?php 
            foreach ($municipios_gen['4'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;

        case '5':
            <?php 
            foreach ($municipios_gen['5'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '6':
            <?php 
            foreach ($municipios_gen['6'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;

        case '7':
            <?php 
            foreach ($municipios_gen['7'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '8':
            <?php 
            foreach ($municipios_gen['8'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '9':
            <?php 
            foreach ($municipios_gen['9'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '10':
            <?php 
            foreach ($municipios_gen['10'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;        
        case '11':
            <?php 
            foreach ($municipios_gen['11'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '12':
            <?php 
            foreach ($municipios_gen['12'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '13':
            <?php 
            foreach ($municipios_gen['13'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '14':
            <?php 
            foreach ($municipios_gen['14'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '15':
            <?php 
            foreach ($municipios_gen['15'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '16':
            <?php 
            foreach ($municipios_gen['16'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '17':
            <?php 
            foreach ($municipios_gen['17'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '18':
            <?php 
            foreach ($municipios_gen['18'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '19':
            <?php 
            foreach ($municipios_gen['19'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '20':
            <?php 
            foreach ($municipios_gen['20'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '21':
            <?php 
            foreach ($municipios_gen['21'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '22':
            <?php 
            foreach ($municipios_gen['22'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '23':
            <?php 
            foreach ($municipios_gen['23'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '24':
            <?php 
            foreach ($municipios_gen['24'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '25':
            <?php 
            foreach ($municipios_gen['25'] as $key => $value) { ?>
                llenar_opcion('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
    }

}



//obtener ciudades por estado

function llenar_opcion_ciudad(key, value){
    $('#ciudades').append(
    $('<option>',{
        'value': key,
        'text': value
    })
    );
}  

function obt_ciudades(estado){
    $("#ciudades").html('<option value="">Seleccione</option>'); 
    switch(estado){
        case '1':
            <?php 
            foreach ($ciudades_gen['1'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '2':
            <?php 
            foreach ($ciudades_gen['2'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;


        case '3':
            <?php 
            foreach ($ciudades_gen['3'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;

        case '4':
            <?php 
            foreach ($ciudades_gen['4'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;

        case '5':
            <?php 
            foreach ($ciudades_gen['5'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '6':
            <?php 
            foreach ($ciudades_gen['6'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;

        case '7':
            <?php 
            foreach ($ciudades_gen['7'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '8':
            <?php 
            foreach ($ciudades_gen['8'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '9':
            <?php 
            foreach ($ciudades_gen['9'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '10':
            <?php 
            foreach ($ciudades_gen['10'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;        
        case '11':
            <?php 
            foreach ($ciudades_gen['11'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '12':
            <?php 
            foreach ($ciudades_gen['12'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '13':
            <?php 
            foreach ($ciudades_gen['13'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '14':
            <?php 
            foreach ($ciudades_gen['14'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '15':
            <?php 
            foreach ($ciudades_gen['15'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '16':
            <?php 
            foreach ($ciudades_gen['16'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '17':
            <?php 
            foreach ($ciudades_gen['17'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '18':
            <?php 
            foreach ($ciudades_gen['18'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '19':
            <?php 
            foreach ($ciudades_gen['19'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '20':
            <?php 
            foreach ($ciudades_gen['20'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '21':
            <?php 
            foreach ($ciudades_gen['21'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '22':
            <?php 
            foreach ($ciudades_gen['22'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '23':
            <?php 
            foreach ($ciudades_gen['23'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '24':
            <?php 
            foreach ($ciudades_gen['24'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '25':
            <?php 
            foreach ($ciudades_gen['25'] as $key => $value) { ?>
                llenar_opcion_ciudad('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
    }

}


//Obtener zona postal del estado

//obtener ciudades por estado


function llenar_opcion_zonaPostal(key, value){

    value = key + " " + value;
    $('#zonaPostal').append(
    $('<option>',{
        'value': key,
        'text': value
    })
    );
}  

function obt_zonaPostal(estado){
    $("#zonaPostal").html('<option value="">Seleccione</option>'); 
    switch(estado){
        case '1':
            <?php 
            foreach ($zonaPostal_gen['1'] as $key => $value) { ?>
                

                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>

            break;
        case '2':
            <?php 
            foreach ($zonaPostal_gen['2'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;


        case '3':
            <?php 
            foreach ($zonaPostal_gen['3'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;

        case '4':
            <?php 
            foreach ($zonaPostal_gen['4'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;

        case '5':
            <?php 
            foreach ($zonaPostal_gen['5'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '6':
            <?php 
            foreach ($zonaPostal_gen['6'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;

        case '7':
            <?php 
            foreach ($zonaPostal_gen['7'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '8':
            <?php 
            foreach ($zonaPostal_gen['8'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '9':
            <?php 
            foreach ($zonaPostal_gen['9'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '10':
            <?php 
            foreach ($zonaPostal_gen['10'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;        
        case '11':
            <?php 
            foreach ($zonaPostal_gen['11'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '12':
            <?php 
            foreach ($zonaPostal_gen['12'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '13':
            <?php 
            foreach ($zonaPostal_gen['13'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '14':
            <?php 
            foreach ($zonaPostal_gen['14'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '15':
            <?php 
            foreach ($zonaPostal_gen['15'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '16':
            <?php 
            foreach ($zonaPostal_gen['16'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '17':
            <?php 
            foreach ($zonaPostal_gen['17'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '18':
            <?php 
            foreach ($zonaPostal_gen['18'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '19':
            <?php 
            foreach ($zonaPostal_gen['19'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '20':
            <?php 
            foreach ($zonaPostal_gen['20'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '21':
            <?php 
            foreach ($zonaPostal_gen['21'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '22':
            <?php 
            foreach ($zonaPostal_gen['22'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '23':
            <?php 
            foreach ($zonaPostal_gen['23'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '24':
            <?php 
            foreach ($zonaPostal_gen['24'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
        case '25':
            <?php 
            foreach ($zonaPostal_gen['25'] as $key => $value) { ?>
                llenar_opcion_zonaPostal('<?php echo $key ?>','<?php echo $value ?>');
            <?php } ?>
            break;
    }

}

$(function(){

        $('#estados').change(function(){
            valorEstado = $('#estados').val();
            obt_municipios(valorEstado);
            obt_ciudades(valorEstado);
            obt_zonaPostal(valorEstado);
        });   


        $('#SolicitudSolicitudForm').validate({
                rules:{
                    'data[Solicitud][estados]':{
                        required:true
                    },

                    'data[Solicitud][municipios]':{
                        required:true
                    },

                    'data[Solicitud][ciudades]':{
                        required:true
                    },

                    'data[Solicitud][zonaPostal]':{
                        required:true
                    },

                    'data[Solicitud][direccion]':{
                        required:true
                    },

                    'data[Solicitud][direccion2]':{
                        required:true
                    },

                    'data[Solicitud][prefijoT]':{
                        required:true
                    },    

                    'data[Solicitud][telefono]':{
                        required:true,
                        digits:true,
                    }, 

                },
                messages:{
                    'data[Solicitud][estados]':{
                        required:<?php echo _m('seleccion.requerida',array('El Estado'), true) ?>
                    },
                    
                    'data[Solicitud][municipios]':{
                        required:<?php echo _m('seleccion.requerida',array('el Municipio'), true) ?>
                    },
                 
                    'data[Solicitud][ciudades]':{
                        required:<?php echo _m('seleccion.requerida',array('la Ciudad'), true) ?>
                    },

                    'data[Solicitud][zonaPostal]':{
                        required:<?php echo _m('seleccion.requerida',array('la Zona Postal'), true) ?>
                    },  

                    'data[Solicitud][direccion]':{
                        required:<?php echo _m('campo.requerido',array('la Dirección Principal'), true) ?>
                    },    

                    'data[Solicitud][direccion2]':{
                        required:<?php echo _m('campo.requerido',array('la Dirección Secundaria'), true) ?>
                    }, 

                    'data[Solicitud][prefijoT]':{
                        required:<?php echo _m('seleccion.requerida',array('el Código Celular'), true) ?>
                    },  

                    'data[Solicitud][telefono]':{
                        required:<?php echo _m('campo.requerido',array('el Número Celular (7 dígitos)'), true) ?>
                    },  

                },
        });






});   





/* al determinar el ajax activar esta funcion

function obt_municipios(valorEstado){
   ruta = '<?php //echo Router::url("/tarjeta_prepagada/obt_municipio/"); ?>' + valorEstado;
    alert(ruta);
        $.ajax({
            type: "GET",     
            url: ruta,
            dataType: "json", 
            cache:false,
            beforeSend: function() {
                ajaxPendiente = true;
                alert("inicio");

            },
                    
            success: function(source) {
                ajaxPendiente = false;
                alert("prueba");
            },
            error: function(source){
                    //$('#nombreP').html(<?php //echo _m('error.fatal',null,true) ?>);
            }

        }); 
}
*/



</script>
