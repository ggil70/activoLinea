<?php
/**
 * Generador de comprobantes de pagos y transferencias en PDF 
 */


App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

/**
 * Clase interna para manejar automaticamente el pie de página
 * 
 */
class TransaccionPendientePDF extends FPDF{
	
	function Footer(){
		$this->setXY(10,-30);
		$this->ln();
		$this->SetFont('Arial','',8);
		$this->Cell(0,5,'Página '.$this->PageNo().'/{nb}',0,0,'R');
	}
}

$pdf = new TransaccionPendientePDF('P','mm','Letter');
$pdf->AliasNbPages();
$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);

$pdf->ln();
$pdf->setXY(155,20);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Fecha:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,date('d/m/Y'));
$pdf->ln();

$pdf->setX(155);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Hora:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,date('h:i:s A'));
$pdf->ln();

$pdf->setTextColor(0,0,0);
$pdf->setFont('Arial','B',14);
$pdf->setY(50);

switch($estado['accion']){
	case 'eliminar':
		$pdf->cell(0,10,"Eliminación de Transacciones Pendientes",0,1,'C');
		break;
	case 'rechazar':
		$pdf->cell(0,10,"Rechazo de Transacciones Pendientes",0,1,'C');
		break;
	case 'aprobar':
		$pdf->cell(0,10,"Aprobación de Transacciones Pendientes",0,1,'C');
		break;
}

$temp_cantidad = 0;
$cuenta_destino = '';

foreach($transacciones as $transaccion){
	
        if(isset($estado['moneda'])){
            if($estado['moneda']=='USD'){
                $moneda = '$';
            }else{
                $moneda = '?';
            }
        }elseif($transaccion['TransaccionPendiente']['moneda']!=null && $transaccion['TransaccionPendiente']['moneda']!=''){
            if($transaccion['TransaccionPendiente']['moneda']=='USD'){
                $moneda = '$';
            }else{
                $moneda = '?';
            }            
        }else{
            $moneda = 'Bs.';
        }


//Simple tv         
$refOperadora = "";
$diaSimple = "";
$fechaSimple = "";
$swSimple = 0;

if(!empty($transaccion['TransaccionPendiente']['inf_simpletv'])){
	$cadenaSimple = $transaccion['TransaccionPendiente']['inf_simpletv'];
    $arraySimple = explode(';', $cadenaSimple);
	$refOperadora = $arraySimple[0];
	$diaSimple = $arraySimple[1];
	$fechaSimple = $arraySimple[2];
	$swSimple = 1;
}    

       
	if($transaccion['TransaccionPendiente']['tipo'][0]=='t' || $transaccion['TransaccionPendiente']['tipo'][0]=='d' || $transaccion['TransaccionPendiente']['tipo'][0]=='p' || $transaccion['TransaccionPendiente']['tipo'][0]=='s' || $transaccion['TransaccionPendiente']['tipo'][0]=='r' || $transaccion['TransaccionPendiente']['tipo'][0]=='s' || $transaccion['TransaccionPendiente']['tipo'][0]=='i' || $transaccion['TransaccionPendiente']['tipo'][0]=='e' || $transaccion['TransaccionPendiente']['tipo'][0]=='f') {
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Tipo Operación:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, TransaccionPendiente::$tipo_transaccion[$transaccion['TransaccionPendiente']['tipo']],"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Fecha Registro:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, date('d/m/Y',strtotime($transaccion['TransaccionPendiente']['fecha_hora_registro'])),"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Usuario Registro:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		if(empty($transaccion['UsuarioCreador']['login'])){
			$pdf->cell(85,10, desencriptar($transaccion['UsuarioCreador']['ibuser']),"LTRB",0,'L',true);
		} else {
			$pdf->cell(85,10, desencriptar($transaccion['UsuarioCreador']['login']),"LTRB",0,'L',true);
		}	
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Referencia:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, $transaccion['TransaccionPendiente']['id'],"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Cuenta Débito:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen'])),"LTRB",0,'L',true);
		$pdf->ln();
		
		if($transaccion['TransaccionPendiente']['tipo'][2]=='o'){
			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(65,10,"Banco del Beneficiario:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(85,10, $transaccion['TransaccionPendiente']['banco'],"LTRB",0,'L',true);
			$pdf->ln();
		}
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		switch($transaccion['TransaccionPendiente']['tipo'][0]) {
		case 'd':
                case 't':
                case 'e':
                case 'f':    
			$pdf->cell(65,10,"Cuenta Beneficiario:","LTRB",0,'L',true);
			$cuenta_destino = enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']));
			break;
		case 'p':
			$pdf->cell(65,10,"Número Tarjeta de Crédito:","LTRB",0,'L',true);
			$cuenta_destino = enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']));
			break;
		case 's':
			$pdf->cell(65,10,"Servicio:","LTRB",0,'L',true);
			$cuenta_destino = desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']);
			break;
		case 'r':
		    if($swSimple == 0){
				$pdf->cell(65,10,"Número Servicio o Cuenta Contrato:","LTRB",0,'L',true);
            }else{
            	$pdf->cell(65,10,"Número de Tarjeta de Acceso:","LTRB",0,'L',true);
            }
			$cuenta_destino = desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']);
                                            
			break;                                                                              
		case 'i':
			$pdf->cell(65,10,"RIF Beneficiario:","LTRB",0,'L',true);
			$cuenta_destino = desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']);
			break;
		}		
		$pdf->setFont('Arial','',10,"LTRB",0,'L',true);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, $cuenta_destino,"LTRB",0,'L',true);
		$pdf->ln();
		
		if(!in_array($transaccion['TransaccionPendiente']['tipo'],array('tpm','ppm','ip ','it','stm','scn','rcn'))) {
			
			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(65,10,"Nombre del Beneficiario:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			if(TransaccionPendiente::$tipo_transaccion[$transaccion['TransaccionPendiente']['tipo']] == "Servicio Pago Corpoelec"){
				$pdf->cell(85,10," Corpoelec ","LTRB",0,'L',true);
			}else{
				$pdf->cell(85,10, desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']),"LTRB",0,'L',true);
			}
			$pdf->ln();
			
			if(!empty($transaccion['TransaccionPendiente']['alias'])){
				$pdf->setX(30);
				$pdf->setFont('Arial','B',10);
				$pdf->setTextColor(255,255,255);
				$pdf->setFillColor(0,51,102);
				$pdf->cell(65,10,"Alias:","LTRB",0,'L',true);
				$pdf->setFont('Arial','',10);
				$pdf->setTextColor(0,0,0);
				$pdf->setFillColor(244,244,244);
				$pdf->cell(85,10, desencriptar($transaccion['TransaccionPendiente']['alias']),"LTRB",0,'L',true);
				$pdf->ln();
			}
			
			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(65,10,"Cédula / RIF:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(85,10, $transaccion['TransaccionPendiente']['tipo_id_beneficiario'].'-'.desencriptar($transaccion['TransaccionPendiente']['id_beneficiario']),"LTRB",0,'L',true);
			$pdf->ln();
		}
		
		if($transaccion['TransaccionPendiente']['tipo'][1] != 'p' && $transaccion['TransaccionPendiente']['tipo'][0] != 's' && $transaccion['TransaccionPendiente']['tipo'][0] != 'r' && $transaccion['TransaccionPendiente']['tipo'][0] != 'i' && $transaccion['TransaccionPendiente']['tipo'][0] != 'e' && $transaccion['TransaccionPendiente']['tipo'][0] != 'f') {
			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(65,10,"E-mail Beneficiario:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(85,10, desencriptar($transaccion['TransaccionPendiente']['email_beneficiario']),"LTRB",0,'L',true);
			$pdf->ln();
		
		}
 		
                if($transaccion['TransaccionPendiente']['tipo'][0]=='e' || $transaccion['TransaccionPendiente']['tipo'][0]=='f') {
                    $valores = explode('-',$transaccion['TransaccionPendiente']['email_beneficiario']);                    
                    $pdf->setX(30);
                    $pdf->setFont('Arial','B',10);
                    $pdf->setTextColor(255,255,255);
                    $pdf->setFillColor(0,51,102);
                    $pdf->cell(65,10,"Monto Orden " .$moneda.":","LTRB",0,'L',true);
                    $pdf->setFont('Arial','',10);
                    $pdf->setTextColor(0,0,0);
                    $pdf->setFillColor(244,244,244);
                    $pdf->cell(85,10, $valores[1],"LTRB",0,'L',true);
                    $pdf->ln();
                }else{
                    $pdf->setX(30);
                    $pdf->setFont('Arial','B',10);
                    $pdf->setTextColor(255,255,255);
                    $pdf->setFillColor(0,51,102);
                    $pdf->cell(65,10,"Monto " .$moneda.":","LTRB",0,'L',true);
                    $pdf->setFont('Arial','',10);
                    $pdf->setTextColor(0,0,0);
                    $pdf->setFillColor(244,244,244);
                    $pdf->cell(85,10, formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),"LTRB",0,'L',true);
                    $pdf->ln();


					if($swSimple == 1){
						$pdf->setX(30);
						$pdf->setFont('Arial','B',10);
						$pdf->setTextColor(255,255,255);
						$pdf->setFillColor(0,51,102);
						$pdf->cell(65,10,"Dias Disponibles de Programacion:","LTRB",0,'L',true);
						$pdf->setFont('Arial','',10);
						$pdf->setTextColor(0,0,0);
						$pdf->setFillColor(244,244,244);
						$pdf->cell(85,10, $diaSimple,"LTRB",0,'L',true);
						$pdf->ln();


						$pdf->setX(30);
						$pdf->setFont('Arial','B',10);
						$pdf->setTextColor(255,255,255);
						$pdf->setFillColor(0,51,102);
						$pdf->cell(65,10,"Fecha Desconexion:","LTRB",0,'L',true);
						$pdf->setFont('Arial','',10);
						$pdf->setTextColor(0,0,0);
						$pdf->setFillColor(244,244,244);
						$pdf->cell(85,10, $fechaSimple,"LTRB",0,'L',true);
						$pdf->ln();

						$pdf->setX(30);
						$pdf->setFont('Arial','B',10);
						$pdf->setTextColor(255,255,255);
						$pdf->setFillColor(0,51,102);
						$pdf->cell(65,10,"Numero Confirmacion Operadora:","LTRB",0,'L',true);
						$pdf->setFont('Arial','',10);
						$pdf->setTextColor(0,0,0);
						$pdf->setFillColor(244,244,244);
						$pdf->cell(85,10, $refOperadora,"LTRB",0,'L',true);
						$pdf->ln();			
					}


                }
		
               if($transaccion['TransaccionPendiente']['tipo'][0] == 'i'){
			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(65,10,"Forma:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(85,10, $transaccion['TransaccionPendiente']['forma'],"LTRB",0,'L',true);
			$pdf->ln();

			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(65,10,"N° Planilla:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(85,10, $transaccion['TransaccionPendiente']['planilla'],"LTRB",0,'L',true);
			$pdf->ln();

			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(65,10,"Período:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(85,10, $transaccion['TransaccionPendiente']['periodo'],"LTRB",0,'L',true);
			$pdf->ln();

			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(65,10,"Impuesto:","LTRB",0,'L',true);
			if (strlen(trim($transaccion['TransaccionPendiente']['impuesto']))<=50){
                            $pdf->setFont('Arial','',10);
                        }else{
                            $pdf->setFont('Arial','',8);   
                        }
                        $pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(85,10, $transaccion['TransaccionPendiente']['impuesto'],"LTRB",0,'L',true);
			$pdf->ln();
                    
			if($transaccion['TransaccionPendiente']['aduana'] != " - "){
                            $pdf->setX(30);
                            $pdf->setFont('Arial','B',10);
                            $pdf->setTextColor(255,255,255);
                            $pdf->setFillColor(0,51,102);
                            $pdf->cell(65,10,"Aduana:","LTRB",0,'L',true);
                            if (strlen(trim($transaccion['TransaccionPendiente']['aduana']))<=50){
                                $pdf->setFont('Arial','',10);
                            }else{
                                $pdf->setFont('Arial','',8);   
                            }

                            $pdf->setTextColor(0,0,0);
                            $pdf->setFillColor(244,244,244);
                            $pdf->cell(85,10, $transaccion['TransaccionPendiente']['aduana'],"LTRB",0,'L',true);
                            $pdf->ln();

                            $pdf->setX(30);
                            $pdf->setFont('Arial','B',10);
                            $pdf->setTextColor(255,255,255);
                            $pdf->setFillColor(0,51,102);
                            $pdf->cell(65,10,"Expediente:","LTRB",0,'L',true);
                            $pdf->setFont('Arial','',10);
                            $pdf->setTextColor(0,0,0);
                            $pdf->setFillColor(244,244,244);
                            $pdf->cell(85,10, $transaccion['TransaccionPendiente']['expediente'],"LTRB",0,'L',true);
                            $pdf->ln();
                        }

                }
		if($transaccion['TransaccionPendiente']['tipo'] != 'spc' && $transaccion['TransaccionPendiente']['tipo'] != 'tpm' && $transaccion['TransaccionPendiente']['tipo'][0] != 'i') {	
			$pdf->setX(30);	
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(65,10,"Descripción:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(85,10, desencriptar($transaccion['TransaccionPendiente']['descripcion']),"LTRB",0,'L',true);
			$pdf->ln();
		}
		
		
		if(isset($transaccion['descripcion_error'])){
			$wce=array(65,85);

			$pdf->setX(30);
			$pdf->setTextColor(255,0,0);
			$pdf->setFillColor(0,51,102);
			$pdf->setFont('Arial','B',10);
			$pdf->cell($wce[0],15,"Resultado:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setFillColor(244,244,244);

					$x = $pdf->getX();
					$y = $pdf->getY();
					$pdf->cell($wce[1],15,"",'LTBR',0,'L',true);
					$nx = $pdf->getX();
					$pdf->setXY($x,$y);
					$pdf->multicell($wce[1],5,$transaccion['descripcion_error'],0,'L');
					$pdf->setXY($nx,$y);

			$pdf->ln(10);
			$pdf->setTextColor(0,0,0);
		}
	
		$pdf->ln(5);
	} else if($transaccion['TransaccionPendiente']['tipo'][0]=='n' || $transaccion['TransaccionPendiente']['tipo'][0]=='v'){
	
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Tipo Operación:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, TransaccionPendiente::$tipo_transaccion[$transaccion['TransaccionPendiente']['tipo']],"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Fecha Registro:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, date('d/m/Y',strtotime($transaccion['TransaccionPendiente']['fecha_hora_registro'])),"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Usuario Registro:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		if(empty($transaccion['UsuarioCreador']['login'])){
			$pdf->cell(85,10, desencriptar($transaccion['UsuarioCreador']['ibuser']),"LTRB",0,'L',true);
		} else {
			$pdf->cell(85,10, desencriptar($transaccion['UsuarioCreador']['login']),"LTRB",0,'L',true);
		}	
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Referencia:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, $transaccion['TransaccionPendiente']['id'],"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Cuenta Débito:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen'])),"LTRB",0,'L',true);
		$pdf->ln();		
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Fecha Valor:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, date('d/m/Y',strtotime($transaccion['TransaccionPendiente']['fecha_hora_valor'])),"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Descripción:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, desencriptar($transaccion['TransaccionPendiente']['descripcion']),"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Número Lote:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, $transaccion['TransaccionPendiente']['lote'],"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(65,10,"Monto Bs.:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(85,10, formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']),"LTRB",0,'L',true);
		$pdf->ln();


		
		
		if(isset($transaccion['descripcion_error'])){
			$wce=array(65,85);

			$pdf->setX(30);
			$pdf->setTextColor(255,0,0);
			$pdf->setFillColor(0,51,102);
			$pdf->setFont('Arial','B',10);
			$pdf->cell($wce[0],15,"Resultado:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setFillColor(244,244,244);

					$x = $pdf->getX();
					$y = $pdf->getY();
					$pdf->cell($wce[1],15,"",'LTBR',0,'L',true);
					$nx = $pdf->getX();
					$pdf->setXY($x,$y);
					$pdf->multicell($wce[1],5,$transaccion['descripcion_error'],0,'L');
					$pdf->setXY($nx,$y);

			$pdf->ln(10);
			$pdf->setTextColor(0,0,0);
		}
	

		// si posee beneficiarios y la carga es manual
		if(count($transaccion['Beneficiarios'])>0 && $transaccion['TransaccionPendiente']['tipo_carga']=='m') {
			
			$pdf->AddPage('L');
			$pdf->SetFont('Arial','',8);

			$pdf->cell(20,5,"Listado de Beneficiarios:",0,0,'C');
			$pdf->ln();
			$pdf->ln();

			$pdf->line(5,$pdf->getY(),270,$pdf->getY());

			$height = 10;
			$width = array(
				'a' => 40,
				'b' => 65,
				'c' => 60,
				'd' => 65,
				'e' => 30,
			);

			$pdf->SetFont('Arial','B',8);
			$pdf->cell($width['a'],$height,'Identificación Beneficiario',0,0,'C');
			$pdf->cell($width['b'],$height,'Nombre Beneficiario',0,0,'C');
			$pdf->cell($width['c'],$height,'Número Cuenta Beneficiario',0,0,'C');
			$pdf->cell($width['d'],$height,'Banco Beneficiario',0,0,'C');
			$pdf->cell($width['e'],$height,'Monto Bs.',0,0,'C');
			$pdf->ln();

			$pdf->line(5,$pdf->getY(),270,$pdf->getY());

			$pdf->SetFont('Arial','',8);
			foreach ($transaccion['Beneficiarios'] as $x){	

				$b = desencriptarCampos($x, array('nombre','cedula','numero_cuenta','email','celular'));

				$pdf->cell($width['a'],$height,$b['tipo_cedula'].$b['cedula'],0,0,'L');

				$pdf->cell($width['b'],$height,$b['nombre'],0,0,'L');

				$pdf->cell($width['c'],$height,$b['numero_cuenta'],0,0,'C');

				$pdf->cell($width['d'],$height,trim($bancos[substr($b['numero_cuenta'],0,4)]),0,0,'L');

				$pdf->cell($width['e'],$height,formatea_monto_fromWs($x['monto']),0,0,'R');

				$pdf->ln();
			}
			
		} else {
			$pdf->ln(5);
		}
	}
	else if($transaccion['TransaccionPendiente']['tipo'][0]=='c'){
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(70,10,"Tipo Operación:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(90,10, TransaccionPendiente::$tipo_transaccion[$transaccion['TransaccionPendiente']['tipo']],"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(70,10,"Fecha Registro:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(90,10, date('d/m/Y',strtotime($transaccion['TransaccionPendiente']['fecha_hora_registro'])),"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(70,10,"Usuario Registro:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		if(empty($transaccion['UsuarioCreador']['login'])){
			$pdf->cell(90,10, desencriptar($transaccion['UsuarioCreador']['ibuser']),"LTRB",0,'L',true);
		} else {
			$pdf->cell(90,10, desencriptar($transaccion['UsuarioCreador']['login']),"LTRB",0,'L',true);
		}	
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(70,10,"Referencia:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(90,10, $transaccion['TransaccionPendiente']['id'],"LTRB",0,'L',true);
		$pdf->ln();
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(70,10,"Número de Cuenta:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(90,10, enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen'])),"LTRB",0,'L',true);
		$pdf->ln();
		
		if($transaccion['TransaccionPendiente']['tipo']=='csi'){
			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(70,10,"Últimos 6 Dígitos del Cheque:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(90,10, desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),"LTRB",0,'L',true);
			$pdf->ln();
			
		}elseif($transaccion['TransaccionPendiente']['tipo']=='csx'){
			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(70,10,"Últimos 6 Dígitos del Primer Cheque:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(90,10, desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']),"LTRB",0,'L',true);
			$pdf->ln();
		}
		else{
			list($primer,$segundo) = @explode('|', desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']));
			
			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(70,10,"Últimos 6 Dígitos del Primer Cheque:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(90,10, $primer,"LTRB",0,'L',true);
			$pdf->ln();
			
			$pdf->setX(30);
			$pdf->setFont('Arial','B',10);
			$pdf->setTextColor(255,255,255);
			$pdf->setFillColor(0,51,102);
			$pdf->cell(70,10,"Últimos 6 Dígitos del Último Cheque:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setTextColor(0,0,0);
			$pdf->setFillColor(244,244,244);
			$pdf->cell(90,10, $segundo,"LTRB",0,'L',true);
			$pdf->ln();
		}
		
		$pdf->setX(30);
		$pdf->setFont('Arial','B',10);
		$pdf->setTextColor(255,255,255);
		$pdf->setFillColor(0,51,102);
		$pdf->cell(70,10,"Motivo:","LTRB",0,'L',true);
		$pdf->setFont('Arial','',10);
		$pdf->setTextColor(0,0,0);
		$pdf->setFillColor(244,244,244);
		$pdf->cell(90,10, desencriptar($transaccion['TransaccionPendiente']['descripcion']),"LTRB",0,'L',true);
		$pdf->ln();
		
		
		if(isset($transaccion['descripcion_error'])){
			$wce=array(70,90);

			$pdf->setX(30);
			$pdf->setTextColor(255,0,0);
			$pdf->setFillColor(0,51,102);
			$pdf->setFont('Arial','B',10);
			$pdf->cell($wce[0],15,"Resultado:","LTRB",0,'L',true);
			$pdf->setFont('Arial','',10);
			$pdf->setFillColor(244,244,244);

					$x = $pdf->getX();
					$y = $pdf->getY();
					$pdf->cell($wce[1],15,"",'LTBR',0,'L',true);
					$nx = $pdf->getX();
					$pdf->setXY($x,$y);
					$pdf->multicell($wce[1],5,$transaccion['descripcion_error'],0,'L');
					$pdf->setXY($nx,$y);

			$pdf->ln(10);
			$pdf->setTextColor(0,0,0);
		}
	
	}
	
	if($transaccion['TransaccionPendiente']['tipo']=='rcd' || $transaccion['TransaccionPendiente']['tipo']=='scd'){
              $mensaje_footer = 'Para más información comuníquese al 121 desde su Digitel o al 04129121121';  
            $pdf->cell(0,10,$mensaje_footer,'',0,'C');
        }
                
	$pdf->ln(5);
	
	$temp_cantidad+=1;
	
	if(count($transacciones)>1 && $temp_cantidad<count($transacciones)){
		$pdf->AddPage();
	}
}

$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

$pdf->output($estado['accion'].date('Ymdhis').'.pdf','D');
exit;
		
?>