<?php
    /**
     *  Vista para mostrar el listado de cuentas que posee un cliente
     */
?>

<div>
<h2 class="user">Cuentas Bancarias Moneda Nacional</h2>
<div class="line_title_orange"></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>Alias</td>
<td>Tipo Moneda</td>
<td>N&uacute;mero</td>

</tr>
</thead>
<?php if(!empty($cuentas)) { ?>
<tbody>
<?php foreach($cuentas as $c){?>
<tr>
<td class="texto_centro"><?php echo $c['ALIAS']; ?></td>
<td class="texto_centro"><?php echo $c['SUMCCY']; ?></td>
<td class="texto_centro"><?php echo $c['SUMACN'] ?></td>
</tr>
<?php } ?>
</tbody>
<?php } ?>
</table>

<?php if(!empty($cuentas_moneda_extranjera)) { ?>
<div>
<h2 class="user">Cuentas Bancarias Moneda Extranjera</h2>
<div class="line_title_orange"></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>Alias</td>
<td>Tipo Moneda</td>
<td>N&uacute;mero</td>
</tr>
</thead>
<?php if(!empty($cuentas_moneda_extranjera)) { ?>
<tbody>
<?php foreach($cuentas_moneda_extranjera as $ce){?>
<tr>
<td class="texto_centro"><?php echo $ce['ALIAS']; ?></td>
<td class="texto_centro"><?php echo $ce['SUMCCY']; ?></td>
<td class="texto_centro"><?php echo $ce['SUMACN']; ?></td>
</tr>
<?php } ?>
</tbody>
<?php } ?>
</table>
<?php } ?>
