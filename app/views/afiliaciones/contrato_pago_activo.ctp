<?php
/**
 * Generador de planillas de afiliación de usuarios jurídicos
**/

App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

Class PlanillaPDF extends FPDF{

	function header(){		
				
		$this->ln();
		$this->ln();
		$this->ln();
		$this->ln();
		$this->ln();
	}
	
	function Footer(){
		
	}
	
	//Iniciación de variables
	var $B = 0;
	var $I = 0;
	var $U = 0;
	var $HREF = '';
        var $ALIGN='';

	function WriteHTML($html) {
		//Intérprete de HTML
		$html = str_replace("\n", ' ', $html);
		$a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);                                
        
		foreach ($a as $i => $e) {
			if ($i % 2 == 0) {
				//Text
				if($this->HREF)
					$this->PutLink($this->HREF,$e);
                                elseif($this->ALIGN == 'justify')
                                        $this->Multicell(0,5,$e,0,'J');
                                elseif($this->ALIGN == 'center')
                                        $this->Cell(0, 5, $e, 0, 1, 'C');
				else
					$this->Write(5,$e);          
			}
			else {
				//Etiqueta
				if ($e{0} == '/')
					$this->CloseTag(strtoupper(substr($e, 1)));
				else {
					//Extraer atributos
					$a2 = explode(' ', $e);
					$tag = strtoupper(array_shift($a2));
					$attr = array();
					foreach ($a2 as $v)
						if (ereg('^([^=]*)=["\']?([^"\']*)["\']?$', $v, $a3))
							$attr[strtoupper($a3[1])] = $a3[2];
					$this->OpenTag($tag, $attr);
				}
			}
		}
	}

	function OpenTag($tag, $attr) {
		//Etiqueta de apertura
                if ($tag == 'P')
                        $this->ALIGN = $attr['ALIGN'];
		if ($tag == 'B' or $tag == 'I' or $tag == 'U')
			$this->SetStyle($tag, true);
		if ($tag == 'A')
			$this->HREF = $attr['HREF'];
		if ($tag == 'BR')
			$this->Ln(2.5);                                
	}

	function CloseTag($tag) {
		//Etiqueta de cierre
                if ($tag == 'P')
                        $this->ALIGN='';
		if ($tag == 'B' or $tag == 'I' or $tag == 'U')
			$this->SetStyle($tag, false);
		if ($tag == 'A')
			$this->HREF = '';                
	}

	function SetStyle($tag, $enable) {
		//Modificar estilo y escoger la fuente correspondiente
		$this->$tag+=($enable ? 1 : -1);
		$style = '';
		foreach (array('B', 'I', 'U') as $s)
			if ($this->$s > 0)
				$style.=$s;
		$this->SetFont('', $style);
	}
	
	function PutLink($URL,$txt) {
		//Escribir un hiper-enlace
		$this->SetTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->Write(5,$txt,$URL);
		$this->SetStyle('U',false);
		$this->SetTextColor(0);
	}

}

$pdf = new PlanillaPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);

switch($datos['ESTADOCIVIL']){
    case 1: $edoCivil = 'soltero(a)';break;
    case 2: $edoCivil = 'casado(a)';break;
    case 3: $edoCivil = 'divorciado(a)';break;
    case 4: $edoCivil = 'viudo(a)';break;
    case 5: $edoCivil = 'concubinato(a)';break;
}



// TEXTO DEL CONTRATO
$contrato = '<P ALIGN="justify">Yo '.desencriptar($usuario['nombre']).', de nacionalidad '.$datos['NACIONALIDAD'];
$contrato.= ', mayor de edad, de estado civil '.$edoCivil.', ';
$contrato.= 'titular del Documento de Identidad Nro. '.$usuario['tipo_cedula'].'-'.desencriptar($usuario['cedula']).', ';
$contrato.= 'por medio de la presente autorizo expresa, amplia y suficientemente a BANCO ACTIVO, C.A. BANCO UNIVERSAL, ';
$contrato.= 'a los fines de que en todas las oportunidades en que la Cuenta '.$tipoCuentaCA.' Nro. '.$nro_cta.' de ';
$contrato.= 'la cual soy titular en BANCO ACTIVO, C.A. BANCO UNIVERSAL no disponga de fondos suficientes disponibles ';
$contrato.= 'para cubrir los pagos con algunos de los instrumentos de movilización que el banco habilite para tal fin  e igualmente con la ';
$contrato.= 'finalidad de generar dicha  disponibilidad y dejar elementos de referencia y conciliación en las mismas, de acuerdo con ';
$contrato.= 'las especificaciones de este producto "SALDO ACTIVO", realice  lo siguiente:';
$contrato.= '<br>';
$contrato.= '1.    Cargos a la Tarjeta de Crédito '.$tipoCuenta[trim($cuentas['Afiliacion']['numero_tarjeta'])].' distinguida con el Nro. '.$cuentas['Afiliacion']['numero_tarjeta'].' emitida por ';
$contrato.= 'BANCO ACTIVO, C.A. BANCO UNIVERSAL y de la cual soy titular, por el monto faltante para cubrir el pago';
$contrato.= ' y hasta por el saldo disponible en dicha  tarjeta de crédito e igualmente acepto, los cargos por concepto ';
$contrato.= 'de las respectivas comisiones que proceden por esta operación, los cuales me obligo a pagar. ';
$contrato.= '<br>';
$contrato.= '2.    Un abono a la Cuenta indicada en esta solicitud, correspondiente al monto cargado a la ';
$contrato.= 'Tarjeta de Crédito antes identificada, según lo señalado  en el numeral anterior.';
$contrato.= '<br>';
$contrato.= 'Expresamente convengo que cualquier reclamo que surja con motivo de los referidos cargos y abonos, ';
$contrato.= 'según sea el caso, al igual que la eventual revocatoria de esta autorización, lo tramitaré exclusivamente ante BANCO ACTIVO, C.A. BANCO UNIVERSAL.';
$contrato.= '<br>';
$contrato.= 'En este mismo sentido acepto, que BANCO ACTIVO, C.A. BANCO UNIVERSAL no será responsable por el incumplimiento';
$contrato.= ' en la prestación del Producto aquí descrito y no asumirá ningún tipo de responsabilidad ante los clientes; en consecuencia, no estará obligado ';
$contrato.= 'al pago de indemnización alguna por concepto de los daños y perjuicios que eventualmente se deriven de la no ejecución de los procesos cuando: ';
$contrato.= 'a) la Tarjeta de Crédito de la cual soy titular no disponga de saldo suficiente para efectuar el cargo, no esté activa, se encuentre bloqueada o en situación de atraso en su pago. ';
$contrato.= 'b) cuando exista retardo en el procesamiento, siempre que ello se deba a caso fortuito, fuerza mayor y/o, en general, a cualquier causa extraña no imputable a BANCO ACTIVO, C.A. BANCO UNIVERSAL, mientras tales circunstancias persistan.';
$contrato.= '<br>';
$contrato.= 'Finalmente declaro, que con anterioridad a la firma de la presente solicitud de Afiliación al Producto Saldo Activo, se me han suministrado las características y las condiciones de dicho producto y de su uso. ';
$contrato.= '</P>';

//**************************************************************** TERCERA HOJA
$pdf->AddPage();
$pdf->setMargins(20,20,20);
$pdf->setAutoPageBreak(true,20);

$pdf->setFont('Arial','B',11);
$pdf->cell(0,6,"SOLICITUD DE AFILIACION AL PRODUCTO SALDO ACTIVO",0,1,'C');
$pdf->cell(0,6,"PERSONA NATURAL",0,0,'C');

$pdf->ln();
$pdf->ln();

$pdf->setFont('Arial','',9);
$pdf->cell(0,6,$fecha,0,0,'R');

$pdf->ln();
$pdf->ln();

$pdf->cell(0,6,'Señores:',0,1,'L');
$pdf->cell(0,6,'BANCO ACTIVO, C.A. BANCO UNIVERSAL',0,1,'L');
$pdf->cell(0,6,'Presente.-',0,1,'L');

$pdf->ln();
$pdf->ln();

$pdf->setFont('Arial','',9);
$pdf->WriteHTML($contrato);
$pdf->ln();

$pdf->output(date('YmdHis').'.pdf','D');

exit;


?>