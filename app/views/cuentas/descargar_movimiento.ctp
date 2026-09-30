<?php
/**
 * Archivo PDF para mostrar el detalle del movimiento de la cuenta seleccionado
 */


App::Import('Vendor', 'FPDF', array('file' => 'fpdf16/fpdf.php'));

$pdf = new FPDF('P', 'mm', 'Letter');

$pdf->setAutoPageBreak(true, 5);
$pdf->setMargins(10, 10, 10);
$pdf->AddPage();

$pdf->image(ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS . 'img' . DS . 'logo.jpg', 10, 10, 70);

$pdf->setTextColor(0, 0, 0);
$pdf->setXY(165, 10);
$pdf->setFont('Arial', 'B', 14);
$pdf->cell(20, 10, "RECIBO");

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial', 'B', 12);
$pdf->cell(25, 10, "Referencia:");
$pdf->setFont('Arial', '', 12);
$pdf->cell(35, 10, $movimiento['REFERENCIA']);

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial', 'B', 12);
$pdf->cell(25, 10, "Fecha:");
$pdf->setFont('Arial', '', 12);
$pdf->cell(35, 10, formatea_fecha($movimiento['TINID2'], $movimiento['TINID1'], $movimiento['TINID3']));

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial', 'B', 12);
$pdf->cell(25, 10, "Hora:");
$pdf->setFont('Arial', '', 12);
$pdf->cell(35, 10, date_format(new DateTime($detalleLogs['Bitacora']['fecha_hora']), 'h:i:s A'));

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial', 'B', 12);
$pdf->cell(25, 10, "Monto Bs.:");
$pdf->setFont('Arial', '', 12);
$pdf->cell(35, 10, formatea_monto_fromWs($movimiento['TINAMT']));

$pdf->ln();
$pdf->setFont('Arial', 'B', 14);
switch ($this->data['DetalleProducto']['tipo']) {
    case 'WC':
    case 'AE':
        $pdf->cell(0, 10, "NOTA DE CRÉDITO", '', 0, 'C');
        break;
    case 'WD':
    case 'CW':
    case 'WN':


    case 'CI':
    case 'PS':
    case 'DS':


    case 'PI':
        $pdf->cell(0, 10, "NOTA DE DEBITO", '', 0, 'C');
        break;
    default:
        $pdf->cell(0, 10, "NOTA", '', 0, 'C');
        break;
}

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 12);
$pdf->cell(40, 10, "CONCEPTO:");

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(65, 10, "Descripción", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
if ($this->data['DetalleProducto']['tipo'] == 'PS' || $this->data['DetalleProducto']['tipo'] == 'DS') {
    $pdf->cell(100, 10, 'RECARGA SERVICIO', "LTRB", 0, 'L', true);
} else {

    $pdf->cell(100, 10, trim($movimiento['TINOBI1']), "LTRB", 0, 'L', true);
}

if ($this->data['DetalleProducto']['tipo'] != 'AE') {
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial', '', 12);
    $pdf->cell(40, 10, "ORIGEN:");

    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial', '', 10);
    $pdf->setTextColor(255, 255, 255);
    $pdf->setFillColor(0, 51, 102);
    $pdf->cell(65, 10, "Cuenta de Débito", "LTRB", 0, 'L', true);
    $pdf->setFont('Arial', '', 10);
    $pdf->setTextColor(0, 0, 0);
    $pdf->setFillColor(244, 244, 244);
    $pdf->cell(100, 10, enmascara($movimiento['CTA_ORIGEN']), "LTRB", 0, 'L', true);

    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial', '', 10);
    $pdf->setTextColor(255, 255, 255);
    $pdf->setFillColor(0, 51, 102);
    $pdf->cell(65, 10, "Nombre o Razón Social", "LTRB", 0, 'L', true);
    $pdf->setFont('Arial', '', 10);
    $pdf->setTextColor(0, 0, 0);
    $pdf->setFillColor(244, 244, 244);
    $pdf->cell(100, 10, $movimiento['NOMBRE_ORIGEN'], "LTRB", 0, 'L', true);
}

if ($this->data['DetalleProducto']['tipo'] != 'WN') {
    if ($this->data['DetalleProducto']['tipo'] == 'PS' || $this->data['DetalleProducto']['tipo'] == 'DS') {

        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 12);
        $pdf->cell(40, 10, "SERVICIO:");


        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);
        $pdf->cell(65, 10, "Nombre Empresa Servicio", "LTRB", 0, 'L', true);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);
        $pdf->cell(100, 10, $nombre_servicio, "LTRB", 0, 'L', true);


        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);
        $pdf->cell(65, 10, "Número de Servicio o Cuenta Contrato", "LTRB", 0, 'L', true);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);
        $pdf->cell(100, 10, $movimiento['TINOB1'], "LTRB", 0, 'L', true);

        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 12);
        $pdf->cell(40, 10, "DETALLE PAGO:");

        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);
        $pdf->cell(65, 10, "Número Confirmación Operadora", "LTRB", 0, 'L', true);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);
        $pdf->cell(100, 10, $movimiento['TINITM'], "LTRB", 0, 'L', true);


        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);
        $pdf->cell(65, 10, "Estatus", "LTRB", 0, 'L', true);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);
        $pdf->cell(100, 10, strlen(trim($movimiento['TINIMD'])) == 0 ? 'PROCESADO' : $movimiento['TINIMD'], "LTRB", 0,
            'L', true);
    } else {
        // COMENTADO A PETICION DE MARCEL 01/08/20013 //	if($this->data['DetalleProducto']['comision_cheque'] != 'I'){

        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 12);
        $pdf->cell(40, 10, "BENEFICIARIO:");

        if ($this->data['DetalleProducto']['tipo'] != 'PI' && $this->data['DetalleProducto']['tipo'] != 'AE') {
            $pdf->ln();
            $pdf->setX(30);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(255, 255, 255);
            $pdf->setFillColor(0, 51, 102);
            $pdf->cell(65, 10, "Nombre Banco", "LTRB", 0, 'L', true);

            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFillColor(244, 244, 244);
            $pdf->cell(100, 10, $movimiento['TINBB1'], "LTRB", 0, 'L', true);


        }


        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);

        switch ($this->data['DetalleProducto']['tipo']) {
            case 'WC':
            case 'AE':
            case 'WD':
            case 'CW':
                $pdf->cell(65, 10, "Número Cuenta", "LTRB", 0, 'L', true);
                break;
            case 'CI':
                $pdf->cell(65, 10, "Número Tarjeta", "LTRB", 0, 'L', true);
                break;
            case 'PI':
                $pdf->cell(65, 10, "RIF Beneficiario", "LTRB", 0, 'L', true);
                break;
        }


        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);

        if ($this->data['DetalleProducto']['tipo'] != 'PI') {
            $pdf->cell(100, 10, enmascara($movimiento['CTA_DESTINO']), "LTRB", 0, 'L', true);
        } else {
            $pdf->cell(100, 10, $movimiento['CTA_DESTINO'], "LTRB", 0, 'L', true);
        }

        if ($this->data['DetalleProducto']['tipo'] != 'PI') {
            $pdf->ln();
            $pdf->setX(30);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(255, 255, 255);
            $pdf->setFillColor(0, 51, 102);
            $pdf->cell(65, 10, "Cédula o RIF", "LTRB", 0, 'L', true);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFillColor(244, 244, 244);
            $pdf->cell(100, 10, (strlen(trim($movimiento['TINBC3'])) > 2) ? trim($movimiento['TINBC3']) : '', "LTRB", 0,
                'L', true);

            $pdf->ln();
            $pdf->setX(30);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(255, 255, 255);
            $pdf->setFillColor(0, 51, 102);
            $pdf->cell(65, 10, "Nombre Beneficiario", "LTRB", 0, 'L', true);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFillColor(244, 244, 244);
            $pdf->cell(100, 10, trim($movimiento['NOMBRE_DESTINO']), "LTRB", 0, 'L', true);
        }

        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 12);

        if ($this->data['DetalleProducto']['tipo'] == 'AE') {
            $pdf->cell(40, 10, "DETALLE DEL ABONO:");
        } else {
            $pdf->cell(40, 10, "DETALLE PAGO:");
        }

        if ($this->data['DetalleProducto']['tipo'] == 'PI') {
            $pdf->ln();
            $pdf->setX(30);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(255, 255, 255);
            $pdf->setFillColor(0, 51, 102);
            $pdf->cell(65, 10, "Descripción", "LTRB", 0, 'L', true);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFillColor(244, 244, 244);
            $pdf->cell(100, 10, $movimiento['DESCRIPCION'], "LTRB", 0, 'L', true);


            $pdf->ln();
            $pdf->setX(30);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(255, 255, 255);
            $pdf->setFillColor(0, 51, 102);
            $pdf->cell(65, 10, "Impuesto", "LTRB", 0, 'L', true);
            if (strlen(trim($movimiento['IMPUESTO']) . ' - ' . trim($movimiento['NOMBREIMPUESTO'])) <= 50) {
                $pdf->setFont('Arial', '', 10);
            } else {
                $pdf->setFont('Arial', '', 8);
            }
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFillColor(244, 244, 244);
            $pdf->cell(100, 10, trim($movimiento['IMPUESTO']) . ' - ' . $movimiento['NOMBREIMPUESTO'], "LTRB", 0, 'L',
                true);

            $pdf->ln();
            $pdf->setX(30);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(255, 255, 255);
            $pdf->setFillColor(0, 51, 102);
            $pdf->cell(65, 10, "Forma", "LTRB", 0, 'L', true);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFillColor(244, 244, 244);
            $pdf->cell(100, 10, $movimiento['FORMA'], "LTRB", 0, 'L', true);

            $pdf->ln();
            $pdf->setX(30);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(255, 255, 255);
            $pdf->setFillColor(0, 51, 102);
            $pdf->cell(65, 10, "N° Planilla", "LTRB", 0, 'L', true);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFillColor(244, 244, 244);
            $pdf->cell(100, 10, $movimiento['PLANILLA'], "LTRB", 0, 'L', true);

            if ($movimiento['ADUANA'] != '000000' && $movimiento['ADUANA'] != '') {

                $pdf->ln();
                $pdf->setX(30);
                $pdf->setFont('Arial', '', 10);
                $pdf->setTextColor(255, 255, 255);
                $pdf->setFillColor(0, 51, 102);
                $pdf->cell(65, 10, "Aduana", "LTRB", 0, 'L', true);
                if (strlen(trim($movimiento['ADUANA']) . ' - ' . trim($movimiento['NOMBREADUANA'])) <= 50) {
                    $pdf->setFont('Arial', '', 10);
                } else {
                    $pdf->setFont('Arial', '', 8);
                }
                $pdf->setTextColor(0, 0, 0);
                $pdf->setFillColor(244, 244, 244);
                $pdf->cell(100, 10, trim($movimiento['ADUANA']) . ' - ' . $movimiento['NOMBREADUANA'], "LTRB", 0, 'L',
                    true);

                $pdf->ln();
                $pdf->setX(30);
                $pdf->setFont('Arial', '', 10);
                $pdf->setTextColor(255, 255, 255);
                $pdf->setFillColor(0, 51, 102);
                $pdf->cell(65, 10, "Expediente", "LTRB", 0, 'L', true);
                $pdf->setFont('Arial', '', 10);
                $pdf->setTextColor(0, 0, 0);
                $pdf->setFillColor(244, 244, 244);
                $pdf->cell(100, 10, $movimiento['EXPEDIENTE'], "LTRB", 0, 'L', true);
            }

            $pdf->ln();
            $pdf->setX(30);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(255, 255, 255);
            $pdf->setFillColor(0, 51, 102);
            $pdf->cell(65, 10, "N° Autorización", "LTRB", 0, 'L', true);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFillColor(244, 244, 244);
            $pdf->cell(100, 10, $movimiento['AUTORIZACION'], "LTRB", 0, 'L', true);

        } else if ($this->data['DetalleProducto']['tipo'] != 'AE'){

            $pdf->ln();
            $pdf->setX(30);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(255, 255, 255);
            $pdf->setFillColor(0, 51, 102);
            $pdf->cell(65, 10, "Descripción", "LTRB", 0, 'L', true);
            $pdf->setFont('Arial', '', 10);
            $pdf->setTextColor(0, 0, 0);
            $pdf->setFillColor(244, 244, 244);
            $pdf->cell(100, 10, desencriptar($detalleLogs['Bitacora']['descripcion']), "LTRB", 0, 'L', true);

        }

        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);
        $pdf->cell(65, 10, "Estatus", "LTRB", 0, 'L', true);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);
        $pdf->cell(100, 10, strlen(trim($movimiento['TINIMD'])) == 0 ? 'PROCESADO' : $movimiento['TINIMD'], "LTRB", 0,
            'L', true);
    }
} else {
    foreach ($datosTransaccion as $transaccion) {
        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 12);
        $pdf->cell(40, 10, "DETALLE PAGO:");
        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);
        $pdf->cell(65, 10, "Número Lote", "LTRB", 0, 'L', true);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);
        $pdf->cell(100, 10, $transaccion['TransaccionPendiente']['lote'], "LTRB", 0, 'L', true);
        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);
        $pdf->cell(65, 10, "Concepto Lote", "LTRB", 0, 'L', true);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);
        $pdf->cell(100, 10, desencriptar($transaccion['TransaccionPendiente']['descripcion']), "LTRB", 0, 'L', true);

        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);
        $pdf->cell(65, 10, "Fecha Valor", "LTRB", 0, 'L', true);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);
        $pdf->cell(100, 10, date('d/m/Y', strtotime($transaccion['TransaccionPendiente']['fecha_hora_valor'])), "LTRB",
            0, 'L', true);


        $pdf->ln();
        $pdf->setX(30);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFillColor(0, 51, 102);
        $pdf->cell(65, 10, "Cantidad Pagos Realizados", "LTRB", 0, 'L', true);
        $pdf->setFont('Arial', '', 10);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFillColor(244, 244, 244);


        $pdf->cell(100, 10, count($transaccion['Beneficiarios']), "LTRB", 0, 'L', true);
        $pdf->ln();
    }
}

$pdf->ln();
$pdf->cell(0, 10, "BANCO ACTIVO, Se mueve Contigo !!!", '', 0, 'C');


$pdf->output(desencriptar($usuario['cedula']) . '-' . date('ymdhis') . '.pdf', 'D');
exit;

?>
