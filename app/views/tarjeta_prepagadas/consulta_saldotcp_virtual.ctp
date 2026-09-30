<?php
    /**
     * Vista consulta Saldo TPC
     */
     $nroTarjeta = substr($nroTarjeta,0,4) . " " . substr($nroTarjeta,4,4) . " " . substr($nroTarjeta,8,4) . " " . substr($nroTarjeta,12,4);


?>


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
</tbody>
</table>

<br><br>


    <style>
:root {
    --main-bg-field-drab: #81661F;
    --main-bg-golden-brown: #896D23;
    --main-bg-dark-goldenrod: #A88C42;
    --main-bg-licorice: #211406;
    --main-bg-linen: #ddd289;
  }

.logo {
    width: 130px;
    height: 40px;
    object-fit: contain;
    padding: 0px 10px;
}

.logo-content {
    display: flex;
    flex-wrap: wrap;
    align-content: center;
    align-items: center;
    justify-content: flex-start;
}

img.mastercard-ico {
    width: 45px;
    height: 25px;
    object-fit: contain;
}

.tarjeta{
    width: 270px;
    background: linear-gradient(#67531d, #e2bf62);
    height: 160px;
    border-radius: 10px;
}

.tarjeta-down {
    height: 100px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    display: flex;
    align-content: flex-start;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
    flex-direction: column;
    
    padding: 0px 20px;
}

.tarjeta-up{
    height: 60px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    
}

.cod-container {
    display: flex;
    flex-wrap: wrap;
    align-content: center;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.numero-tarjeta-container span {
    color: var(--main-bg-linen);
    text-shadow: 2px 2px 3px black;
    font-size: 18px;
}

.info-fecha-contauner  span {
    color: var(--main-bg-linen);
    text-shadow: 2px 2px 3px black;
    font-size: 12px;
}

.cod-container span {
    color: var(--main-bg-linen);
    text-shadow: 2px 2px 3px black;
    font-size: 14px;
}

.info-fecha-contauner span:nth-of-type(1) {
    margin-right: 23px;
}

.info-nombre-container span{
    color: var(--main-bg-linen);
    text-shadow: 2px 2px 3px black;
    font-size: 14px;
}

.tarjeta-container{
    display: flex;
    flex-wrap: wrap;
    align-content: center;
    justify-content: center;
    align-items: center;
}



    </style>    

    <div class="tarjeta-container">
        <div class="content-row">
            <div class="tarjeta">
                <div class="tarjeta-up">
                    <div class="logo-content">
                        <?php
                        echo $html->image('activo_logo.png',array('class'=>'logo'));
                        ?>
                    </div>
                </div>
                <div class=tarjeta-down>
                    <div class="numero-tarjeta-container">
                        <span><?php echo $nroTarjeta;?></span>
                    </div>
                    <div class="info-fecha-contauner">
                        <span>Good thru: <?php echo $fechaVencimiento;?></span>

                    </div>
                <div class="info-nombre-container">
                        <span><?php echo $nombreCliente;?></span>
                </div>

                    <div class="cod-container">
                        <span>cvv: <?php echo $cvv;?></span>
                        <?php
                        echo $html->image('mascrdico.png',array('class'=>'mastercard-ico'));
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


<br>






<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
</thead>
<tbody>    
<tr>
    <td colspan="6" class="botonera" style="align:right">
        <?php 
        echo $html->link(
                'Volver',
                '/tarjeta_prepagadas/seleccionar_consulta_saldo/',
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

        ?>
    </td>
</tr>
</tbody>
</table>


