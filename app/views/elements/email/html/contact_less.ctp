<?php
/** * Correo HTML ajustado segun requerimientos del cliente.
 **/
$nombre_usuario = isset($nombre_usuario) ? $nombre_usuario : 'Cliente';
$accion_verbo_correo = isset($accion_verbo_correo) ? $accion_verbo_correo : 'procesamiento de su solicitud';
?>

<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <p>Estimado(a) Cliente: <strong><?php echo $nombre_usuario; ?></strong>,</p>

    <p>
        Nos complace notificarte que procesamos de manera exitosa la 
        <strong><?php echo $accion_verbo_correo; ?></strong>. 
        Para BANCO ACTIVO es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro canal digital Activo en Linea.
    </p>

    <!-- <p>
        En caso de desconocer esta transaccion o requerir informacion adicional puedes comunicarte con nuestro Centro de Atencion al Cliente por el telefono 0500-ACTIVAT (0500-2284828).
    </p>

    <p>
        Atentamente,<br>
        <strong>BANCO ACTIVO</strong>
    </p>

    <hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">

    <p style="font-size: 11px; color: #777;">
        Por favor no responda o reenvie mensajes a esta cuenta de correo, dado que la misma no es monitoreada por el banco.
    </p>

    <p style="font-size: 10px; color: #999; text-align: justify;">
        <strong>NOTA CONFIDENCIAL:</strong><br>
        La informacion contenida en este E-mail es confidencial y solo puede ser utilizada por la persona o la compania a la cual está dirigido y/o por el emisor del mismo. Si no es el receptor autorizado, cualquier retencion, difusion, distribucion o copia de este mensaje esta prohibida y sera sancionada por la ley. Si por error recibe este mensaje, por favor agradecemos borrar inmediatamente el mensaje recibido y notifique inmediatamente a atcliente@bancoactivo.com
    </p> -->
     <!-- <hr style="border: none; border-top: 1px solid #ccc;"> -->

</div>