<?php
/**
 * Generador de comprobante de creación/modificación de usuario jurídico
**/


App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);

$pdf->setTextColor(0,0,0);
$pdf->setXY(165,10);
$pdf->setFont('Arial','B',14);
$pdf->cell(20,10,"");
$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Fecha:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,date('d/m/Y'));
$pdf->ln();


$pdf->ln();
$pdf->setFont('Arial','B',14);
$pdf->cell(0,10,$data['operacion'] . " Usuario",'',0,'C');

$pdf->ln();
$pdf->setX(30);


$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Identificación del Usuario (Login)","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['nomUsuario'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"RIF de la Empresa","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$usuario['tipo_rif'].desencriptar($usuario['rif']),"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Cédula de Identidad","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10, $data['Usuario']['tipoCedulaRif'].'-'.$data['Usuario']['nroCedulaRif'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Nombre","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['nombre'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Cargo","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['cargo'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Email","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['emailPri'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Celular","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['codigoCelular'].$data['Usuario']['nroCelular'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Perfil","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$perfiles[$data['Usuario']['perfil']],"LTRB",0,'L',true);
$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

if($data['operacion']!='Eliminar' && count($transacciones)>0){
	$pdf->AddPage();
	$pdf->SetFont('Arial','',8);
	
	$pdf->cell(55,5,"Identificación del Usuario (Login): " . $data['Usuario']['nomUsuario'],0,0,'C');
	$pdf->ln();
	
	$pdf->cell(20,5,"Funcionalidades:",0,0,'C');
	$pdf->ln();
		
	$height = 8;
	$width = array(
		'm' => 55,
		'f' => 55,
		'c' => 30,
		'a' => 30,
		'l' => 30,
	);
	
	$pdf->setTextColor(0,0,0);
	$pdf->setFillColor(244,244,244);
	
	$pdf->cell($width['m'],$height,'Menú',0,0,'L', TRUE);
	$pdf->cell($width['f'],$height,'Funcionalidad',0,0,'L', TRUE);
	$pdf->cell($width['c'],$height,'Cuentas',0,0,'C', TRUE);
	$pdf->cell($width['a'],$height,'Nro. Aprobaciones',0,0,'C', TRUE);
	$pdf->cell($width['l'],$height,'Límites Bs.',0,0,'C', TRUE);
	$pdf->ln();
	
	foreach ($transacciones as $menu => $m){
		$cantidad_transacciones = count(array_intersect_key($m['transaccion'], $data['Usuario']['ck']));

		// Verifica si esta lleno el arreglo
		if(!isset($m['transaccion'])) continue;
				
		// Recorre cada transaccion
		foreach ($m['transaccion'] as $transaccion => $t){
			// si la transaccion no se encuentra en los seleccionados continua con la siguiente
			if(!array_key_exists($transaccion, $data['Usuario']['ck'])) continue;
			
			$mostrar_info = true;
			
			$limite = '';
			
			if($t['limite']){
				if (!empty($data['Usuario']['ck'][$transaccion]['limite'])){
					$limite = $data['Usuario']['ck'][$transaccion]['limite'];
				} else if(in_array($transaccion, array(100021,100022,100025,100026,39,40,100036,100037,100014,100015))){
					if(empty($limite_internas_terceros)){
						$limite = 'Sin límite';
					} else {
						$limite = $limite_internas_terceros;
					}
				} else if(in_array($transaccion, array(100023,100024,100027,100028,100016,100017))){
					if(empty($limite_externas)){
						$limite = 'Sin límite';
					} else {
						$limite = $limite_externas;
					}
				}
			}			
			
			if(!isset($t['cuentas']) || empty($t['cuentas'])){
				
				$pdf->cell($width['m'],$height,$m['menu'],0,0,'L');

				$pdf->cell($width['f'],$height,$t['nombre'],0,0,'L');

				$pdf->cell($width['c'],$height,'',0,0,'C');

				$x = (isset($data['Usuario']['ck'][$transaccion]['aprobacion']) && !in_array($transaccion, array(41,42,43)) ) ? $data['Usuario']['ck'][$transaccion]['aprobacion'] : '';
				$pdf->cell($width['a'],$height,$x,0,0,'C');

				$pdf->cell($width['l'],$height,$limite,0,0,'R');

				$pdf->ln();
			} else {
				foreach ($t['cuentas'] as $k => $v){
					
					// Verifica si selecciono la cuenta
					if ( !isset($data['Usuario']['ck'][$transaccion]['cuenta']) || !in_array($k, $data['Usuario']['ck'][$transaccion]['cuenta']) ) continue;
					
					$x = $mostrar_info ? $m['menu'] : '';
					$pdf->cell($width['m'],$height,$x,0,0,'L');

					$x = $mostrar_info ? $t['nombre'] : '';
					$pdf->cell($width['f'],$height,$mostrar_info ? $x : '',0,0,'L');

					$pdf->cell($width['c'],$height,$v,0,0,'C');

					$x = (isset($data['Usuario']['ck'][$transaccion]['aprobacion']) && !in_array($transaccion, array(41,42,43)) ) ? $data['Usuario']['ck'][$transaccion]['aprobacion'] : '';
					$pdf->cell($width['a'],$height,$x,0,0,'C');

					$pdf->cell($width['l'],$height,$limite,0,0,'R');

					$pdf->ln();

					$mostrar_info = false;
				}
			}
			
			$x=10;
			$step=1;

			while($x<208+$step*2){
				$pdf->line($x,$pdf->getY(),$x+$step,$pdf->getY());
				$x+=$step*2;
			}
		}
	}
}

$pdf->output('CU'.$usuario['tipo_rif'].desencriptar($usuario['rif']).date('Ymdhis').'.pdf','D');

exit;
		
?>