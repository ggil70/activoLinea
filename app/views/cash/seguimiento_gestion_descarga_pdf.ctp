<?php
/**
 * Generador de comprobante de carga masiva de afiliaciones en cash
 * management
**/

App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);

$i = 0 ; 
$tipo =  $dataPDF['0']['tipo'] ;
 
function enmascararNumeroCuenta($numeroCuenta) {
    // Verificar que el número de cuenta tenga 20 dígitos
    if (strlen($numeroCuenta) !== 20) {
        return "El número de cuenta debe tener 20 dígitos.";
    }

    // Enmascarar desde el carácter 4 hasta el carácter 16
    $parteVisible = substr($numeroCuenta, 0, 4); // Primeros 3 caracteres
    $parteEnmascarada = str_repeat('*', 13); // Reemplazar del 4 al 16 con asteriscos
    $parteFinal = substr($numeroCuenta, 16); // Últimos 3 caracteres

    // Combinar las partes
    return $parteVisible . $parteEnmascarada . $parteFinal;
}



foreach( $dataPDF as $x){ 
    $b = desencriptarCampos($x, array('nombre',  'cedula', 'numero_cuenta',  'mensaje_error'));
    $fecha =   $dataResumen['TransaccionPendiente']['fecha_hora_registro'] ; 
    $newDate =  date('d-m-Y', strtotime($fecha));
    $hora =   substr($fecha,10); 
    $codBanco = substr(desencriptar($x['numero_cuenta']),0,4);
    $referencia =   $dataPDF['referencia']['concepto']['transaccion_id'];
    $descripcion = desencriptar($dataResumen['TransaccionPendiente']['descripcion']); 
    $nombreBanco =  $codBanco; 
    $codigoBanco=  $bancos[$codBanco];  
    $origenNombre= $datos_origen['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
    $cuenta_origen = desencriptar($dataResumen['TransaccionPendiente']['cuenta_origen']); 
    $referencia=$dataPDF[$i]['id'];
    $motivo = $b['mensaje_error'];
 

$pdf->AddPage();
$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logoconrif-removebg-preview.jpg',20,10,70);

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Fecha:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,date('d/m/Y'));


$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->setFont('Arial','B',14);
// switch($estado['tipo']){
	// case 'n':
		// $pdf->cell(0,10,"PAGO NOMINA",'',0,'C');
		// break;
	// case 'p':
		// $pdf->cell(0,10,"PAGO PROVEEDOR",'',0,'C');
		// break;
// }

 
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setFillColor(240,240,240);
$pdf->setTextColor(0,51,102);
$pdf->SetLineWidth(0);
$pdf->SetDrawColor(229,229,229);
$pdf->cell(50,10,"Referencia:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10,  $referencia,1, 0, 'L', true);
$i++; 
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,utf8_decode("Fecha Real Transacción:"),1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10,  $newDate,1, 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,"Hora:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10,  $hora ,1, 0, 'L', true);


$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,"Monto:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, ( number_format( $b['monto'], 2) ),1, 0, 'L', true);
 
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(50,10,"Concepto:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, '',1, 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,utf8_decode("Descripción:"),1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, ($descripcion ),1, 0, 'L', true);
 

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(50,10,"Origen:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, '',1, 0, 'L', true); 

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10, utf8_decode("Cuenta Débito:"),1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, ( enmascararNumeroCuenta($cuenta_origen )  ),1, 0, 'L', true);


$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,utf8_decode("Nombre  o Razón Social") ,1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, $origenNombre,1, 0, 'L', true);


$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(50,10,"Beneficiario:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, '',1, 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,"Nombre Banco:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10,($codigoBanco ),1, 0, 'L', true);


$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,"Numero de Cuenta:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, enmascararNumeroCuenta($b['numero_cuenta']) ,1, 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,utf8_decode("Cédula o RIF:"),1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, ($b['tipo_cedula'].$b['cedula']),1, 0, 'L', true);


$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,"Nombre Beneficiario:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, ($b['nombre']),1, 0, 'L', true);


$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(50,10,"Detalle Pago:",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, '',1, 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,utf8_decode("Descripción"),1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10,  $descripcion,1, 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,"Estatus",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, $b['resultado'],1, 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','B',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(240,240,240);
$pdf->cell(50,10,"Motivo",1, 0, 'L', true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,51,102);
$pdf->setFillColor(255,255,255);
$pdf->cell(100,10, $b['motivo']  ,1, 0, 'L', true);
 
$pdf->ln();

$pdf->cell(0,20,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

}


$pdf->output(desencriptar($usuario['cedula']).'-'.date('Ymdhis').'.pdf','D');

exit;

?>