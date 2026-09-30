<?php
  
  
/**
 * Formulario inicial de pago activo
**/
?>
<div>
<h2 class="user">Creación de QR</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario_400">
  <?php

  echo $form->create('qrForm',
            array(
                'url'=> array(
                  'controller'=>'pago_activo',
                  'action'=>'qr'  
                ),
                'id'=>'qrForm',
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
  
  echo '<p>En esta opción Ud. podrá crear código QR para el Servicio Pago Activo.</p>';


  $form->input(
        'qrimagen',
        array(
            'id'=>'qrimagen'
        )
    );


  echo $form->input(
    'codigo_qr',
    array(
      'label'=>'<span class="requerido">*</span> Código QR:',
      'type'=>'hidden',
      'maxlength' => '30',
      'class' => 'alfanumericoEspacio',
      'id'=>'txtqr',
      'Value'=>' '
    )
  );


  echo $form->end();
  ?>

<?php if(!empty($rutaImagen)){ ?>

<img src="<?php echo $rutaImagen?>" alt="QR Code"/>

<?php

echo '<tr><td>&nbsp;&nbsp;</td><td class="submit">'; 
echo $html->link(
              'Descargar',
              '/pago_activo/qr_pdf/',
              array(
                 'class'=>'button bloqueo_repetir'
              )
    );

    echo "&nbsp;&nbsp;";


    echo $html->link(
                    'Imprimir',
                    'javascript:print();void(0);',
                    array(
                        'class'=>'button'
                    )
    );

 } else { ?>



<?php
echo '<tr><td>&nbsp;&nbsp;</td><td class="submit">';            
        
  echo $form->submit(
    'Generar QR',
    array(
      'class'=>'button',
      'id'=>'txtBoton'
    )
  );
}
  ?>


<?php
    echo '</td></tr></table>';                
        
  echo $form->end();
?>
  </body>
</html>
        

<?php echo $html->script('libs\sdk.bundle.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js\libs\sdk.bundle.js'))?>


<script>
    const ident = "<?php echo $id ?>";
    var nombre = "<?php echo $name ?>";
    nombre = limpiarCadena(nombre);

    const name = nombre;
    const phone = "<?php echo $phone ?>";
    const GenerateQRInstance = new Suiche7b.GenerateQR();
    const qrForm = document.getElementById('qrForm');
    const qrCodeDiv = document.getElementById('qrCode');

    qrForm.addEventListener('submit', function(event) {
      event.preventDefault();

      // Obtiene el monto del input
      // const amount = document.getElementById('amount').value;

      // Datos de ejemplo


      const merchantId = '0102';
      const plaintextData = JSON.stringify({
        id: ident,
        name: name,
        phone: phone,
        bank: '0171',
      });

      // Usando el mÃ©todo generateQRCode
      GenerateQRInstance.generateQRCode(merchantId, plaintextData)
        .then((result) => {
          
          if (result) {
            
           // $('#qrimagen').val(result);
            $('#txtqr').val(result);

            generaQr();

            //qrCodeDiv.innerHTML = `<img src="${result}" alt="QR Code"/>`;
            //console.log(result);
          } else {

            console.log('Ha ocurrido un error al generar el código QR.');
          }
        })
        .catch((err) => {
          console.error('Error:', err);
        });
        
    });


  function generaQr(){
   let formulario = document.getElementById('qrForm');
    formulario.submit();
  }



  function limpiarCadena(cadena){
     nombre = nombre.replace('á', 'A');
     nombre = nombre.replace('Á', 'A');
     nombre = nombre.replace('é', 'E');
     nombre = nombre.replace('É', 'E');
     nombre = nombre.replace('í', 'I');
     nombre = nombre.replace('Í', 'I');
     nombre = nombre.replace('ó', 'O');
     nombre = nombre.replace('Ó', 'O');
     nombre = nombre.replace('ú', 'U');
     nombre = nombre.replace('Ú', 'U');
     nombre = nombre.replace('ñ', 'N');
     nombre = nombre.replace('Ñ', 'N');
     nombre = cadena.replace(/[^a-zA-Z0-9 ]/g, '');    
     nombre = nombre.replace('  ', ' ');
     nombre = nombre.replace('  ', ' ');
     return nombre; 
  }
  



  </script>
