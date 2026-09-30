<?php
    /**
     * Vista para mostrar el consolidado de productos
     */
     $nroTarjeta = substr($nroTarjeta,0,4) . " " . substr($nroTarjeta,4,4) . " " . substr($nroTarjeta,8,4) . " " . substr($nroTarjeta,12,4);

     $rutaImg = ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo_virtual.jpg';

?>

<style>

        .container-tarjeta {
            max-width: 40%;
            width: max-content;
            background-color: #bb9f60;
            border-radius: 10px;
            padding: 20px;
            margin: 10px;

        }


        .info-container p {
            font-size: 23px;
            text-shadow: 2px 2px 2px #000000db;
            color: #bb9f60;
            font-family: sans-serif;
        }

        p.fecha,
        p.clave {
            font-size: 17px;
        }

        .logo-content img {
            width: 50%;
            filter: brightness(1.1);
            mix-blend-mode: multiply;
        }

        @media only screen and (max-width : 991.5px) {
            .container-tarjeta {
                max-width: 100%;
                width: auto;
                background-color: #bb9f60;
                border-radius: 10px;
                padding: 20px;
                margin: 10px;
            }
        }
    </style>




<div>
<h2 class="user">Tarjeta Prepago CASH Virtual / Consulta de Saldo</h2>
<div class="line_title_orange"></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>Estatus</td>
<td>Saldo Total $</td>
<td>Saldo Diferido $.</td>
<td>Saldo Disponible $</td>
</tr>
</thead>
<tbody>
<tr>
<td class="texto_centro"><?php echo $statusCuenta; ?></td>
<td class="monto"><?php echo $saldoCuenta; ?></td>
<td class="monto"><?php echo $saldoDiferido; ?></td>
<td class="monto"><?php echo $saldoDisponible; ?></td>
</tr>

<tr><td class="texto_centro" colspan="4">
    <div class="container-tarjeta">
        <div class="row-tarjeta">
            <div class="logo-content">
                <!--<img src="<?php //echo $rutaImg;?>"></img>-->
                BANCO ACTIVO                

            </div>
            <br>
            <div class="info-container">
                <div class="numberAcount">
                    <p class="number"><?php echo $nroTarjeta; ?></p>
                </div>
                <div class="fechaVencimiento">
                    <p class="fecha">Fecha Vencimiento: <?php echo $fechaVencimiento; ?></p>
                </div>
                <div class="codVerificacion">
                    <p class="clave">Codigo de verificacion (CVV): <?php echo $cvv; ?></p>
                </div>
            </div>
        </div>
    </div>
</td>
</tr>    
</tbody>
</table>


<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
</thead>
<tbody>    
<tr>
    <td colspan="6" class="botonera" style="align:right">
        <?php 
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
</tbody>
</table>


